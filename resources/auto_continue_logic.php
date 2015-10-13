<?php

/**

	This is a redcap_survey_page_top hook designed to enable conditional logic (like the survey queue, but with auto-continue instead)

	We have found that for large, complex projects the survey queue can be burdensom and auto-continue is much easier for end-users.  However, the inability to conditionally display an instrument (such as a pregnancy form) is a limitation.  This hook gets around that limitation.

	The logic for each instance can be specified on a per-form basis using an array called 
	$auto_continue_logic and should be specified before loading this hook, as in:
	
	$auto_continue_logic = array(
		//instrument name => //logic
		'pregnancy_form' => "[enrollment_arm_1][pregnancy] = '1' AND [enrollment_arm_1][gender] = '2'",
		'family_members_your_generation_siblings' => "[enrollment_arm_1][siblings_exist] = '1'",
		'family_members_children' => "[enrollment_arm_1][have_children] = '1'",
		'family_members_grandchildren' => "[enrollment_arm_1][family_members_grandchildren]='1'",
		'family_members_other_affected_relatives' =>  "[enrollment_arm_1][have_family_info]='1'"
	);

	Andrew Martin
	Stanford University

**/

global $end_survey_redirect_next_survey, $end_survey_redirect_url;

//hook_log($auto_continue_logic, "DEBUG", "Starting AutoContinueLogic!");

// Check if custom logic is applied to this instrument
if (isset($auto_continue_logic[$instrument])) {
	//hook_log("Applying auto-continue-logic to $instrument","DEBUG");
	// Get the logic and evaluate it
	$raw_logic = $auto_continue_logic[$instrument];
	$isValid = LogicTester::isValid($raw_logic);
	
	if (!$isValid) {
		print "<div class='red'><h3><center>Supplied survey auto-continue logic is invalid:<br>$raw_logic</center></h3></div>";
		hook_log("AutoContinue Logic is INVALID for $project_id / $instrument: $raw_logic","ERROR");
	}
	
	$logic_result = LogicTester::evaluateLogicSingleRecord($raw_logic, $record);
	
	if ($logic_result == false) {
		// This instrument should not be taken!
		hook_log("AutoContinue Logic is FALSE - skipping $instrument","DEBUG");
		
		// If autocontinue is enabled - then redirect to next instrument
		if($end_survey_redirect_next_survey) {
			// Try to get the next survey url
			$next_survey_url = Survey::getAutoContinueSurveyUrl($record, $instrument, $event_id);
			//print "Redirecting you to $next_survey_url";
			hook_log("Redirecting $record from $instrument to $next_survey_url","DEBUG");
			redirect($next_survey_url);
		} else {
			hook_log("AutoContinue Logic is FALSE for $record on $instrument but auto-continue is not enabled for this survey", "DEBUG");
			// If there is a normal end-of-survey url - go there
			if ($end_survey_redirect_url != "") {
				redirect($end_survey_redirect_url);
			} 
			// Display the normal end-of-survey message with an additional note
			else {
				$custom_text = "<div class='yellow'><h3><center>This survey form does not apply for this record.</center></h3></div>";
				hook_log("AutoContinue Logic is false but no other options so display 'does not apply' message","ERROR");
				exitSurvey($custom_text . $full_acknowledgement_text, false);
			}
		}
		//return false;
	} else {
		// administer the instrument
		hook_log("AutoContinue Logic is TRUE for $record from $instrument - starting survey", "DEBUG");
	}
}