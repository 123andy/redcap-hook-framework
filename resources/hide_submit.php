<?php
	
/**
	This is a hook script that prevents the 'submit' button from being displayed on surveys.
	Specify the unique redcap instrumentn names in the filter array to limit to selected surveys.
	If there are multi-page surveys, it will allow the next button but not the submit button.

	This should only be applied on a per project basis.  Copy this template into the /pidxxx/survey_page folder
	(where xxx is the number of the project) and customize the filter array to apply

	Andrew Martin
	Stanford University
**/

//error_reporting(E_ALL);

// Specify the instrument filer - otherwise submit will be hidden on all surveys in the project
// The name of the instrument is the 'unique' name that can be found in column B from a data dictionary export
// For example: $filter = array('participantreported_musical_background');
$filter = array();

global $form_name;
if ($filter) {
	if (!in_array($form_name, $filter)) exit();
}

// Only hide submit if this is single-page or the last of multi-pages
global $totalPages, $question_by_section;	
if (!$question_by_section || $_GET['__page__'] == $totalPages) {
	//error_log("DEBUG: Hiding submit on $form_name");
	echo "
	<script type='text/javascript'>
		$(document).ready(function() {
			$('button[name=\"submit-btn-saverecord\"]').hide();
		});
	</script>";	
}

?>