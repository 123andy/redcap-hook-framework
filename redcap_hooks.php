<?php
/**

	Custom REDCap Hooks File

	It is possible to configure your hooks in many ways.  In playing around with a few I've adopted this convention:

	Inside your base redcap directory you should create a folder called hooks (or any other name you like).
	So, your directory might look like this:
	- redcap_vx.y.z
	- temp
	- webtools2
	- plugins
	- hooks

	Inside the hooks directory you need two folders:
	- framework: This is intended to be this repository.  It is the 'source code' and houses the generic hook functions and utilities.
	- server: This is the server-specific configuration of your hooks.  This can be in a separate repository on a per-server basis and is NOT checked into the public repo.

	Inside /hooks/framework/ lies the redcap_hooks.php file.  This is the hook configuration file and must be linked from inside the REDCap
	application.  In the Control Center under general settings, you have to define the location of this file, something like:
		/var/html/www/redcap/hooks/framework/redcap_hooks.php)

	The purpose of this configuration is to permit you to have a single hook function call from REDCap call both
	global and project-specific hooks. Because I like things complicated, there are two methods to include either
	a global or project-specific hook:

		Method 1: A 'master' hooks file exists for global and each project.  Each file can 'catch' all hook function calls and use
			simple if-then syntax to decide which events to process.  These files should be located at
			hooks/server/global/global_hooks.php
			hooks/server/pidxxx/custom_hooks.php

		Method 2: A custom hook file for each hook function (named the same as the hook function itself).  Examples include
			hooks/server/global/redcap_custom_verify_username.php
			hooks/server/pidxxx/redcap_data_entry_form.php

	Any combination of methods 1 and 2 can be used.  Exercise caution in defining new functions that could be included
	more than once for a given hook event (or use custom namespaces).

	Also inside this hook framework directory I have the following:
	- hooks_common.php (a file with utility functions and constants you might want to adjust)
	- pidxxx (a template folder that can be copied to /hooks/server/pidyyy to start custom hooks for a new project

	Andrew Martin
	Stanford University

**/

// Turn on error reporting
error_reporting(E_ALL);

// Include the common functions
require_once('hooks_common.php');

### FOR EACH HOOK METHOD IN REDCAP, A FUNCTION OF MATCHING NAME SHOULD BE ENTERED BELOW

// redcap_add_edit_records_page (REDCap >= 6.8.0)
function redcap_add_edit_records_page ($project_id, $instrument, $event_id) {
	// Example use of hook_log to track time to execute a hook:
	//$hook_start_time = microtime(true);
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
	//hook_log(" - $hook_event took " . hook_exec_time($hook_start_time), 'DEBUG');
}


// redcap_add_edit_records_page (REDCap >= 5.11.0)
function redcap_control_center() {
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event) as $script) include $script;
}


// redcap_add_edit_records_page (REDCap >= 5.8.0)
function redcap_custom_verify_username($username) {
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event) as $script) include $script;
}


// redcap_data_entry_form (REDCap >= 5.11.0)
function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
}


// redcap_data_entry_form_top (REDCap >= 6.8.0)
function redcap_data_entry_form_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
}


// redcap_every_page_top (REDCap >= 6.14.0)
function redcap_every_page_top($project_id)
{
        $hook_event = __FUNCTION__;
        foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
}


// redcap_every_page_before_render (REDCap >= 6.14.0)
function redcap_every_page_before_render($project_id = null)
{
        $hook_event = __FUNCTION__;
        foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
}


// redcap_project_home_page (REDCap >= 6.9.0)
function redcap_project_home_page($project_id) {
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
}


// redcap_save_record (REDCap >= 5.11.0)
function redcap_save_record($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
}


// redcap_survey_complete (REDCap >= 5.11.0)
function redcap_survey_complete($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
}


// redcap_survey_page (REDCap >= 5.11.0)
function redcap_survey_page($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
}


// redcap_survey_page_top (REDCap >= 6.8.0)
function redcap_survey_page_top($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
}


// redcap_user_rights (REDCap >= 5.11.0)
function redcap_user_rights($project_id) {
	$hook_event = __FUNCTION__;
	foreach (get_hook_include_files($hook_event, $project_id) as $script) include $script;
}

// INSERT ADDITONAL HOOKS HERE AS THEY ARE DEVELOPED HERE



/////////////////////////////////////

hook_log("------------ redcap_hooks loaded ------------", "DEBUG");
