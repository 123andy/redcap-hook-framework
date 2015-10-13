<?php
		
/**
	This file contains common functions used by the redcap_hooks and redcap hook functions
	
	The hook_log function is intended to help you debug your hooks and is a work-in-progress...
	
	TODO: Allow you to specify a default file to log to instead of the php error log
	TODO: Make sure it actually works :-)  I haven't had much time to play with this
	TODO: Maybe try wrapping hook code in try/catch so errors are easier to debug...
	
	Each project can have a debug level for its hooks:
		0 = only errors are logged (production),
		1 = error and info statements are logged
		2 = all statements are logged
		3 = all statements are logged to error file AND screen
	
	Andy Martin
	Stanford University

**/

// Set the base hook folder to be one level higher than this file
define('HOOK_PATH_ROOT', dirname(__DIR__).DS);
define('HOOK_PATH_FRAMEWORK', dirname(__FILE__).DS);
define('HOOK_PATH_SERVER', HOOK_PATH_ROOT . "server" . DS);

// In order to access these configuration parameters inside the hook function, they must be global
global $hook_debug_default, $hook_debug_projects;	//global $hook_functions, $hook_fields;
$hook_debug_default = 2;
$hook_debug_projects = array(
	//PID => HOOK DEBUG LEVEL (0-3)
	'17'  => 2,       //Primary Example Project
	'21'  => 2        //Hooks parsing tester
);

// Returns an array of paths to be included for the hook
function get_hook_include_files($function, $project_id = null) {
	$paths = array();
	
	// GLOBAL SINGLE HOOK FILE
	$script = HOOK_PATH_SERVER."global".DS."global_hooks.php";
	if (file_exists($script)) $paths[] = $script;
	
	// GLOBAL HOOKS PER-FILE
	$script = HOOK_PATH_SERVER."global".DS.$function.".php";
	if (file_exists($script)) $paths[] = $script;
	
	// PROJECT-SPECIFIED HOOKS IN ONE FILE
	$script = HOOK_PATH_SERVER."pid".$project_id.DS."custom_hooks.php";
	if (file_exists($script)) $paths[] = $script;
	
	// PROJECT-SPECIFIC HOOKS PER-FILE (PREVIOUS VERSION)
	$script = HOOK_PATH_SERVER."pid".$project_id.DS.$function.".php";
	if (file_exists($script)) $paths[] = $script;
	
	return $paths;
}


// Logging function for all hook activity
/*
	The message parameter can be an object/array/string
	Type can be: ERROR, INFO, DEBUG

	0 = only errors are logged (production),
	1 = error and info statements are logged
	2 = all statements (including DEBUG) are logged
	3 = all statements are logged to error file AND screen
*/
function hook_log($message, $type = 'INFO', $prefix = '') {
	global $hook_debug_default, $hook_debug_projects, $project_id;
	
	// Set the debug level
	$hook_debug_level = array_key_exists($project_id, $hook_debug_projects) ? $hook_debug_projects[$project_id] : $hook_debug_default;
	
	global $hook_debug_local;
	//echo "<br>hook_debug_local: $hook_debug_local  / hdl: $hook_debug_level";
	
	if ($type == 'ERROR' || ($hook_debug_level == 1 && $type == 'INFO') || $hook_debug_level > 1) {
		//echo "type: $type";
		
		// Get calling file using php backtrace to help label where the log entry is coming from
		$calling_file = debug_backtrace()[0]['file'];
		$calling_function = debug_backtrace()[3]['function'];
		
		// Convert arrays/objects into string for logging
		if (is_array($message)) {
			$msg = "(array): " . print_r($message,true);
		} elseif (is_object($message)) {
			$msg = "(object): " . json_encode($message);
		} elseif (is_string($message)) {
			$msg = $message;
		} else {
			$msg = "(unknown): " . print_r($message,true);
		}
		
		// Prepend prefix
		if ($prefix) $msg = "[$prefix] " . $msg; 
		
		// Output to error log
		error_log($project_id . "\t" . basename($calling_file, '.php') . "\t" . $calling_function . "\t" . $type . "\t" . $msg);

		// Output to screen
		if ($hook_debug_level == 3) {
			print "
<pre style='background: #eee; border: 1px solid #ccc; padding: 5px;'>
Type: $type
File: ".basename($calling_file, '.php')."
Func: $calling_function
Msg : $msg</pre>";
		}
	}
}

function hook_exec_time($hook_start_time) {
	return round((microtime(true) - $hook_start_time) * 1000,4) . " ms";
}