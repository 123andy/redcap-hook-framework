<?php
	
/**

This is a hook that allows you to prevent multiple selections of checkbox values when one is marked as a 'none of the above' option

So, if you have:

1, Apples
2, Bananas
3, Cherries
98, None of the Above

You can define a hook as @NONEOFTHEABOVE=98 and it will prevent the selection of 98 with any other values.

**/
	
$term = '@NONEOFTHEABOVE';

hook_log("Starting $term for project $project_id", "DEBUG");

///////////////////////////////
//	Enable hook_functions and hook_fields for this plugin (if not already done)
if (!isset($hook_functions)) {
	$file = HOOK_PATH_ROOT . 'resources/init_hook_functions.php';
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

$startup_vars = $hook_functions[$term];
?>
<script type='text/javascript'>
	$(document).ready(function() {
		var notaFields = <?php print json_encode($startup_vars) ?>;
		// Go through each field with a NOTA option
		$.each(notaFields, function(field,params) {
			var tr = $('tr[sq_id='+field+']');
			var notaValue = params.params;
			var inputs = $('input:checkbox',tr);
			
			// Replace term from note if defined there
			var note = $('div.note', tr);
			$(note).text($(note).text().replace('<?php echo $term ?>=' + notaValue, ''));
			
			// Add event handler to click events
			$(inputs).on("change", {'field': field, 'code': notaValue},notaCheck);
		});
	});
	
	// This changes checkbox selections based on user response from confirmation dialog
	function notaUpdate(field, notaValue, erase) {
		var tr = $('tr[sq_id='+field+']');
		if (erase) {
			// Clear all non-nota checkboxes
			var otherCheckedItems = $('input:checkbox:checked[code!="'+notaValue+'"]', tr);
			$(otherCheckedItems).parents().click();
		} else {
			// Undo the nota checkbox (this is all messy due to way REDCap handles checkboxes - very odd...)
			var notaOption = $('input:checkbox[code="'+notaValue+'"]', tr);
			$(notaOption).parents().click();
			$(notaOption).prop('checked', false);
			calculate();doBranching();
		}
	};
	
	// This is called when a checkbox is modified in a monitored field
	function notaCheck(event) {
		// Ignore uncheck events
		if (!$(this).prop('checked')) {
			//console.log ($(this).prop('name') + ': Ignoring uncheck call');
			return true;
		}
		
		// Get the field name and tr elements
		var field = event.data.field;
		var notaValue = event.data.code;
		var tr = $('tr[sq_id='+field+']');
		var notaOption = $('input:checkbox[code="'+notaValue+'"]', tr);
		var notaText = $(notaOption).parent().text().trim();
		
		// Ignore if the NOTA is not checked
		if (!$(notaOption).prop('checked')) {
			//console.log ('NOTA not checked...');
			return true;
		}
		
		var otherCheckedItems = $('input:checkbox:checked[code!="'+notaValue+'"]', tr);
		if (otherCheckedItems.length) {
			// Prepare a modal dialog to confirm action
			var labels = [];
			$(otherCheckedItems).each(function(){
				labels.push($(this).parent().text().trim());
			});
			var content = "The option, <b>" + notaText + "</b>, can only be selected by itself.<br><br>Press <b>OK</b> to uncheck the other selected option(s) listed below:<div style='padding:5px 20px;'>" + labels.join(',<br>') + "</div>Or, press <b>MORE THAN 1</b> to allow for multiple selections.";
			var undo_js = "notaUpdate('" + field + "','"+notaValue+"', false)";
			var accept_js = "notaUpdate('" + field + "','"+notaValue+"', true)";
			simpleDialog(content, "Incompatible Checkbox Selection", null, 400, accept_js, "OK", undo_js, "MORE THAN 1");
		}
	}
</script>
