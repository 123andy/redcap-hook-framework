<?php
/**
 * Created by PhpStorm.
 * User: alvaro1
 * Date: 4/19/17
 * Time: 11:40 AM
 */



error_reporting(E_ALL);

$term = '@ENHANCED-SINGLE-COLUMN';

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



// Remove the excess td for question numbering if it isn't turned on
global $question_auto_numbering;
if ($question_auto_numbering == 0) {

    ?>
    <style>
        td.questionnum, td.questionnummatrix {
            display: none !important;
        }
    </style>
    <?php

}


// Fix the width of the enhancedchoice buttons
?>

    <script>
        $(document).ready(function () {
            $('div.enhancedchoice').removeClass('col-sm-6').addClass('col-sm-8 col-sm-offset-2');
        });
    </script>

