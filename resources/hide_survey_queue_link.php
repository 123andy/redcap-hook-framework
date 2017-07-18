
<?php

/**
 *
 *
@HIDE-SURVEY-QUEUE-LINK

 **/

error_reporting(E_ALL);

$term = '@HIDE-SURVEY-QUEUE-LINK';

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




$startup_vars = $hook_functions[$term];


foreach ($hook_functions[$term] as $field => $params) {
    $startup_vars[$field] = $Proj->metadata[$field]['element_label'];
//    print "FIELD: $field " . print_r($params,true) . "<br>";
}

//error_log("Startup Vars in " . __FILE__);
//error_log(print_r($startup_vars,true));

?>
<style>

    /** CSS GOES HERE **/
    #changeFont {
        padding-top: 6px;
        padding-left: 50px;
        text-align: right;
        width: 75px;
        position: fixed;
        color: #666;
        font-family: tahoma;
        font-size: 11px;
    }
    #return_corner, #survey_queue_corner {
        white-space: nowrap;
        text-align: center;
        position: relative;
        top: 0px;
        left: 15px;
        padding: 9px 9px 4px 3px;
        border-left: 1px solid #bbb;
        border-bottom: 1px solid #bbb;
        display: none !important;

    }

</style>

