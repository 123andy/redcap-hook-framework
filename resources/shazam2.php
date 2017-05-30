<?php

/**
 *
 * This is a hook utility function that allows the rearrangement of input elements on a page, for example in a html table of a descriptive field.
 *
 * Version 2 differes from the predecessor in that it stores configurations in the log - the actual html and css is NOT in the data dictionary.
 *
 * Currently, each elemnt you wish to pipe a field into should be
 *
 * <span class='shazam'>field_name</span> will move the field_name input into this span
 * <span class='shazam'>field_name:label</span> will just copy the label for the field called field_name and not the actual inputs.
 *
 * If you want an element to mirror the visibility of another REDCap field, you can do:
 * <span shazam-mirror-visibility=field_name>This will appear or hide with field_name</span>
 *
 * Example:
 *
 * A descriptive field that contains the following HTML in the label and '@SHAZAM' in the field annotation box
 *
 *    <table style='width:100%; table-layout:fixed;border-collapse: collapse;'>
 *      <tr>
 *           <th style='width:16%;'></th
 *           <th style='width:16%;'><b>Dose</b></th>
 *           <th style='width:16%;'><b>Freq</b></th>
 *           <th style='width:16%;'><b>Route</b></th>
 *           <th style='width:32%;'><b>Totals</b></th>
 *       </tr>
 *       <tr shazam-mirror-visibility='dopa_dose' style='border-top: 1px dotted #ccc;'>
 *           <td><b>Dopamine</b></td>
 *           <td class='shazam'>dopa_dose</td>
 *           <td class='shazam'>dopa_freq</td>
 *           <td class='shazam'>dopa_route</td>
 *           <td>
 *               <table style='width:100%'>
 *                   <tr>
 *                       <td class='shazam'>dopa_total_daily</td>
 *                       <td><span class='note'>mg/day</span></td>
 *                   </tr>
 *                   <tr>
 *                       <td class='shazam'>dopa_total_weight</td>
 *                       <td><span class='note'>mg/kg/day</span></td>
 *                   </tr>
 *               </table>
 *           </td>
 *       </tr>
 *   </table>
 *
 * Andrew Martin
 * Stanford University
*/

$term = '@SHAZAM2';
hook_log("Starting $term for project $project_id", "DEBUG");

///////////////////////////////
//	Enable hook_functions and hook_fields for this plugin (if not already done)
if (!isset($hook_functions)) {
    $file = HOOK_PATH_FRAMEWORK . 'resources/init_hook_functions.php';
    if (file_exists($file)) {
        include_once $file;

        // Verify it has been loaded
        if (!isset($hook_functions)) { hook_log("ERROR: Unable to load required init_hook_functions."); return; }
    } else {
        hook_log ("ERROR: In Hooks - unable to include required file $file while in " . __FILE__);
    }
}

// See if the term defined in this hook is used on this page
if (!isset($hook_functions[$term])) {
    hook_log ("Skipping $term on $instrument of $project_id - not used.", "DEBUG");
    return;
}
//////////////////////////////


// SEARCH THE LOG FOR THE REQUIRED CONFIGURATIONS
include_once HOOK_PATH_FRAMEWORK . "../../redcap_connect_extensions.php";
include_once HOOK_PATH_FRAMEWORK . "../../plugins/shazam/common.php";
include_once HOOK_PATH_FRAMEWORK . "../../plugins/shazam/log_store_common.php";


hook_log("hook_functions: " . print_r($hook_functions,true), "DEBUG");


$shazamParams = array();

foreach($hook_functions[$term] as $field_name => $data) {

//    hook_log("Field: $field_name","DEBUG");//ShazamParams: " . print_r($shazamParams,true), "DEBUG");

    // Try to load the parameters
    $tag = "Shazam2 [" . $field_name . "]";
    $ls = new LogStore($project_id, $tag);
    $ls->load();
    $params = $ls->data;
    $shazamParams[] = array(
        'field_name' => $field_name,
        'html' => $params['html']
    );

    if (!empty($params['css'])) {
        print "<style type='text/css'>" . $params['css'] . "</style>";
    }
    if (!empty($params['javascript'])) {
        print "<script type='text/javascript'>" . $params['javascript'] . "</script>";
    }
}


?>

<script type='text/javascript'>
$(document).ready(function() {
    var shazamFields = <?php print json_encode($shazamParams); ?>;

    //console.log("Shazam Fields", shazamFields);

    // Loop through each field_name
    $(shazamFields).each(function(i, obj) {
        var field_name = obj.field_name;
        var html_content = obj.html;
        //console.log(obj);
        //console.log('i: ' + i);console.log(field_name);

        // Get parent tr for table
        var tr = $('tr[sq_id="' + field_name + '"]');

        // Add shazam2 class to tr
        $(tr).addClass('shazam-row');
        
        // Hide the input if it exists
        $('input[name="' + field_name + '"]', tr).hide();

        // Replace term from note
        var note = $('div.note', tr);
        $(note).text($(note).text().replace('<?php echo $term ?>', ''));

        // Get the last label td to inject the table
        var t = $('td.labelrc:last-child', tr);

        console.log(t, "t");

        // Set the html according to the parameters
        $(t).html( html_content );

        // Go through all elements for class .shazam for replacments
        $('.shazam', t).each(function() {
            var nodeValue = trim($(this).text());
            var matches = nodeValue.split(':');
            var field = matches[0];
            var option = matches[1];
            //console.log ('Field: ' + field);
            //console.log ('Option: ' + option);

            // Find the 'real tr' for the field to be relocated
            var real_tr = $("tr[sq_id='" + field + "']");
            // Make sure field is present on page
            if ($(real_tr).size()) {
                // Check for label option
                if (option == 'label') {
                    //Only copying the label
                    var real_label = $("td.labelrc:not(.quesnum):not(.questionnum) td:first", $(real_tr));

                    // COPY IT to the td cell
                    $(this).html($(real_label).clone()).addClass('shazam_label');
                } else {
                    // MOVE IT
                    var real_data = $("td.data", $(real_tr));
                    if (! $(real_data).size()) real_data = $("td:last", $(real_tr)); // changed to last to handle left alignment
                    //var trInputs = $(":input", $(real_tr)).parentsUntil('td.data');
                    if ($(real_data).size()) {
                        var trInputs = $("input[type!='hidden']", $(real_data)).each(function() {
                            var type = $(this).prop('type');
                            //console.log ('Type: ' + type);
                            //console.log (this);
                            //limit width of inputs
                            //console.log ($(this).css('width'));
                            if (type=='text' && $(this).css('width') != '0px') $(this).css({'width':''});//,'max-width':'90%'});
                            //limit width of inputs
                            if (type=='textarea') $(this).css('width','95%');
                            //console.log ($(this).css('width'));
                        });

                        // Move reset buttons to left
                        var r = $("a", $(real_data)).filter(function(index) { return $(this).text() === "reset"; }).parent().css('text-align','left');
                        //console.log (r);	 	//.css('text-align','left');

                        // Move it to the td cell
                        $(this).html($(real_data).children());

                        // Hide the source TRs. (two methods here)
                        //$(real_tr).css('display','none');
                        $(real_tr).css('position','absolute').css('left','-1000px');

                    }
                }
            }
        });

        // Look for shazam-mirror-visibility
        // This feature allows you to make a DOM element mirror the visibility of another element.
        $('td.labelrc *[shazam-mirror-visibility]', tr).each(function() {
            //console.log ('dependent element');
            //console.log(this);
            var mirrored_element = this;
            var field = $(this).attr('shazam-mirror-visibility');
            var real_tr = $("tr[sq_id='" + field + "']");

            // Make sure field is present on page
            if ($(real_tr).size()) {
                                // Do an initial sync of the visibilty
                if ($(real_tr).is(':visible')) {
                    $(mirrored_element).show();
                } else {
                    $(mirrored_element).hide();
                }

                // Create observer that maintains the sync going forward
                var observer = new MutationObserver(function(mutations) {
                    var target = mutations[0].target;
                    if ($(target).is(':visible')) {
                        //console.log('showing ' + field + '...');
                        $(mirrored_element).show();
                    } else {
                        //console.log('hiding ' + field + '...');
                        $(mirrored_element).hide();
                    }
                });

                // Attach observer to target
                var target = $(real_tr)[0];
                observer.observe(target,{
                    attributes:true
                });
            }
            //console.log ("shazam-mirror-visibility");
            //console.log(this);
        });

    });
});

function getFieldLabel(field_name) {
    // Search for a tr element with the id from the th cell
    var real_tr = $("tr[sq_id='" + field_name + "']");
    if ($(real_tr).size()) {
        // Get the label
        var real_label = $("td.label:not(.quesnum):not(.questionnum) td:first", $(real_tr));
        // Move the label into the table and add a 'label' class for rendering
        //$(th).html($(real_label.contents()));
    }

    if (real_label.length > 0) {
        return real_label;
    } else {
        return false;
    }
//	if (th_label.length > 0) {
//		$(th).addClass('label');
//	}
}

// Parse text for fields / options
// \[(?<field>[a-z\d_]+)(?:\:)?(?<option>[a-z\d_]+)?\]
function parseFieldOptions(obj) {
    var text = obj.nodeValue;

    var re = /\{([a-z\d_]+)(?:\:)?([a-z\d_]+)?\}/gm;
    //var re = /\[([a-z\d_]+)(?:\:)?([a-z\d_]+)?\]/gm;
    //var re = /\[(?<field>[a-z\d_]+)(?:\:)?(?<option>[a-z\d_]+)?\]/gm;
    while (match = re.exec(text)) {
        var field = match[1];
        var option = match[2];

        if (option == 'label') {
            // Get the field's label and place it into this spot
            //console.log ('looking for ' + field);
            if (label = getFieldLabel(field)) {
                //console.log ('Found label:');
                //console.log (label);
                // Substitue out the match for the label
                return label;
            }
        }
        //console.log ('asdf');
        //console.log('Match: ' + match.length + ' / ' + field + ' / ' + option);
        //console.log(match);
    }
    //return match;
}

</script>
<style type='text/css'>
    .neverDisplay {display:none;}
    .choicevert0 {width:0 !important}
</style>
