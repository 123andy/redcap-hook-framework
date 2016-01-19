<?php
	
/**
	This is a hook utility function that works as an action tag inserting
        the current user's username (or "[survey respondent]") into fields
        tagged with @USERNAME in the Field Annotation.
 
        It works the same as other action tags in that the username value is 
        inserted only into an empty field - it does not update fields that 
        already contain a value.

        Luke Stevens, Murdoch Childrens Research Institute, to work with hooks framework by 
        Andy Martin
        Stanford University
        https://github.com/123andy/redcap-hook-framework


        To use actiontag_username as a global hook include the following block 
        in server/global/global_hooks.php

        To use actiontag_username as a project-level hook for project X include 
        the following block in server/pidX/custom_hooks.php

if ($hook_event == 'redcap_data_entry_form' || $hook_event == 'redcap_survey_page') {

        // INCLUDE other hook function scripts here
        // ...
       
	// INCULDE @USERNAME action tag
	$file = HOOK_PATH_FRAMEWORK . "resources/actiontag_username.php";
	if (file_exists($file)) {
		include_once $file;
	} else {
		hook_log ("Unable to include $file for project $project_id while in " . __FILE__);
	}
}

**/

$term = '@USERNAME';
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


# Step 1 - Create array of fields to inject
$startup_vars = array();
foreach($hook_functions[$term] as $field => $details) {
	$startup_vars[] = $field;
}
?>

<script type='text/javascript'>
$(document).ready(function() {
	var usernameFields = <?php print json_encode($startup_vars); ?>;
	//console.log(matrixFields);
	
	// Loop through each field_name
	$(usernameFields).each(function(i, field_name) {
		//console.log('i: ' + i);console.log(field_name);
                
		// Get the field's text box input (will be ignored if it's not a text box)
		var usernameInput = $('input:text[name="' + field_name + '"]');
		
		// Insert username if current value is blank
                if ($(usernameInput).val() === '') {
                    $(usernameInput).val('<?php print USERID; ?>'); // USERID is "[survey respondent]" on surveys
                }
	});
});
</script>
