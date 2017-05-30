<?php
	
/**
	This is a hook utility function that randomizes options in radio and checkbox fields
	
	Currently dropdowns randomization doesn't work...
	
	@RANDOMORDER=99 would randomize the list but keep 99 at the bottom

	Andrew Martin
	Stanford University
**/

$term = '@RANDOMORDER';
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

?>

<script type='text/javascript'>

// Randomize enhancement for jquery
$.fn.randomize = function(selector){
	//console.log("Randomize:");console.log(this);
	(selector ? this.find(selector) : this).parent().each(function(){
		$(this).children(selector).sort(function(){
			return Math.round(Math.random()) - 0.5;
		}).detach().appendTo(this);
	});
	//console.log("Randomize Done:");console.log(this);
	return this;
};

$(document).ready(function() {
	var fields = <?php print json_encode($startup_vars) ?>;
	//console.log(fields);

    matrices = {}; // Object();

	// Loop through each field_name
	$.each(fields, function(field_name,params) {
		// Get parent tr for table
		var tr = $('tr[sq_id="' + field_name + '"]');
		
		// Leave this value at the bottom
		var bottomValue = params.params;
		
		// Replace term from note (if used)
		var note = $('div.note', tr);
		$(note).text($(note).text().replace('<?php echo $term ?>', ''));
		
		// For radios and checkboxes, the options are stored in these classes
		var inputs = $('.frmrd,.frmrdh,.choicevert,.choicehoriz',tr);

        // Check if this is part of an input matrix - if so, create an array where key is matrix group and values are fields to randomize
        if ( $(tr).is('[mtxgrp]') ) {

            var mtxgrp = $(tr).attr('mtxgrp');
            var matrix_trs = $('tr[mtxgrp="' + mtxgrp + '"][sq_id]');
            var previous_tr = $(matrix_trs[0]).prev();

            // Don't re-randomize a matrix group
            if ( $(previous_tr).hasClass("randomized") ) return true;

            // Wrap the elements in a div to prevent randomizing other siblings
            $(matrix_trs).sort(
                function(){
                    return Math.round(Math.random()) - 0.5;
                }
            ).detach().insertAfter(previous_tr);

            // Log randomization
            $(previous_tr).addClass("randomized");
        }

		if ( $(inputs).length) {
			
			// Find if last option is other - if so remove it from randomization
			var lastInput = $(inputs).last();
			if ( $('input[value="' + bottomValue + '"]', lastInput).length || $('input[code="' + bottomValue + '"]', lastInput).length ) {
				// Remove from DOM
				$(lastInput).detach();
			} else {
				lastInput = false;
			}
			
			// Wrap the elements in a div to prevent randomizing other siblings
			$(inputs).wrapAll("<div/>");
			$(inputs).randomize();
		}
		
		if ( lastInput !== false ) {
			//console.log("lastInput");
			//console.log(lastInput);
			$(inputs).parent().append(lastInput);
		}
		
		// If it is a select/dropdown
		var select = $('select',tr);
		if ( $(select).length ) {
			// Strip off the first (blank) option to keep it at the top
			var opt1 = $('option:first', select);
			$(opt1).detach();
			
			// If the last option is "Other" then detach and move to end
			var optLast = $('option:last', select);
			//console.log(optOther[0]['label']);  //console.log(optOther[0]['value']);
			if (optLast[0]['value'] == bottomValue) {
				$(optLast).detach();
			}
			
			// Get the remaining options and randomize
			var options = $('option', select);
			//$(options).wrapAll("<div/>");
			$(options).randomize();
						
			// Reinsert the first (blank) option at the top of the list
			$(select).prepend(opt1);
			
			// Reattach other if it was detatched
			if (optLast[0]['value'] == bottomValue) {
				$(select).append(optLast);
			}
		}
	});
});
</script>
