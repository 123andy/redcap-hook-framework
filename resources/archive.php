<?php

/**
 * This is a hook utility function that prevents new records from selecting optoins that have been marked
 * as retired inside the actiontag
 *

	@ARCHIVE=1,2,3 (comma-separated list of options)

	Andrew Martin
	Stanford University
 *
 *  Example project: https://redcap.stanford.edu/redcap_v6.17.1/ProjectSetup/index.php?pid=9420
**/

error_reporting(E_ALL);

$term = '@ARCHIVE';

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


# Step 1 - Create array of fields to hide and inject
//$startup_vars = array();
//foreach($hook_functions[$term] as $field => $details) {
//	$startup_vars[] = $field;
//}

$startup_vars = $hook_functions[$term];

//error_log("Startup Vars in " . __FILE__);
//error_log(print_r($startup_vars,true));

?>
<style>
    div.archived {color: #999;}
    div.archived:after {content: " *Archived"}
</style>

<script type='text/javascript'>

$(document).ready(function() {
	var fields = <?php print json_encode($startup_vars) ?>;
//	console.log("Fields:",fields);
	
	// Loop through each field_name
	$.each(fields, function(field_name,params) {
		// Get parent tr for table
		var tr = $('tr[sq_id="' + field_name + '"]');

		// Replace term from note (if used)
		var note = $('div.note', tr);
		$(note).text($(note).text().replace('<?php echo $term ?>', ''));

        var csvOptions =   params.params.split(",");

        // CHECK FOR RADIO
        if ($('input[type="radio"]',tr).length) {
            var currentValue = $("input[name='" + field_name + "']").val();
            $.each(csvOptions, function(index, optionKey) {
                if (optionKey == currentValue) {
                    // make it grey
                    var label = $('input[type="radio"][value="' + optionKey + '"]', tr).parent().addClass("archived");
                } else {
                    // hide the optionKey
                    $('input[type="radio"][value="' + optionKey + '"]', tr).parent().hide();
                }
            });
        }
        // CHECK FOR DROPDOWNS
        else if ($('select',tr).length) {
            // Check for dropdown options
//            console.log("Checking a dropdown..." + field_name);
            var select = $('select[name="' + field_name + '"]', tr);
            var currentValue = select.val();

            var x = []; // Build array of valid remaining options for auto-complete

            $('option', tr).each(function (index, element) {
                var optVal = $(element).val();
                //console.log("Element",element);
                if (csvOptions.indexOf(optVal) != -1) {
                    if (optVal == currentValue) {
                        $(element).text(element.innerHTML + " *Archived");

                        x.push({value: element.innerHTML, code: optVal})

                        // Gray it out (not really an option here...
                    } else {
                        // Hide the option
//                        console.log("Hide Option", optVal);
                        $(element).detach();
                    }
                } else {
                    x.push({value: element.innerHTML, code: optVal})
                }
            });

            if (select.hasClass('rc-autocomplete-enabled')) {
                // Update the auto-complete source as well to reflect the removed options
                $('#rc-ac-input_' + field_name).autocomplete("option", { source: x });
            }
        }
        // CHECK FOR CHECKBOX
        else if ($('input[type="checkbox"]').length) {
            // Loop through checkbox options
            $('input[type="checkbox"][name="__chkn__' + field_name + '"]').each(function(index,element) {
                var code = $(element).attr('code');
                if (csvOptions.indexOf(code) != -1) {
                    if (element.checked) {
                        // Grey it out
//                        console.log (code + " is checked!");
                        var label = $(element).parent().addClass("archived");
                    } else {
                        // Remove retired/unchecked option
                        $(element).parent().detach();
                    }
                }
            });
        }
	});

});
</script>
