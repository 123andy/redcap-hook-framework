<?php
/**
 * Created by PhpStorm.
 * User: alvaro1
 * Date: 4/19/17
 * Time: 11:40 AM
 */


error_reporting(E_ALL);

$term = '@AUTOSCROLL';

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



?>


<style>
    #autoscroll         { background-color: #666; display:inline-block; color: #fff !important; }
    #autoscroll.enabled { background-color: #8C1515; }
</style>
<script>
    $(document).ready(function() {
        // Enable Radios
        $('#questiontable tr input[type="radio"]').bind('click',scrollToNextTr);

        // Enable Selects
        $('#questiontable tr select').bind('change',scrollToNextTr);

        // Add Button in corner to toggle feature
        var btn = $('<button class="btn btn-xs enabled" id="autoscroll">AutoScroll On</button>').bind('click',toggleAutoscroll);

        if ($('#changeFont').length) {
            // Survey
            $('#changeFont').prepend(btn).bind('click',toggleAutoscroll());
        } else if ($('#pdfExportDropdownTrigger').length) {
            // Data entry forms
            $('#pdfExportDropdownTrigger').after(btn).bind('click',toggleAutoscroll());
        }
        if (getCookie('autoscroll') == -1) toggleAutoscroll();

//        $('#changeFont').prepend('<button class="btn btn-xs enabled" id="autoscroll">AutoScroll On</button>').bind('click',toggleAutoscroll);
//        $('#formSaveTip');

    });

    // Scroll to the next sibling TR
    function scrollToNextTr() {
        if ( $('#autoscroll').hasClass('enabled') ) {

            // Skip Matrix Radios
            if ($(this).closest('td').hasClass('choicematrix')) return;

            // Get the current tr
            currentTr = $(this).parentsUntil('tr').parent();

            // Add a slight delay for branching logic to file and new TRs to be displayed...
            var timeoutId = window.setTimeout(function() {
//                console.log("Timeout",currentTr);
                if (nextTr = $(currentTr).nextAll('tr:visible').first()) {
                    $("html, body").animate({
                        scrollTop: $(nextTr).offset().top
                    }, 300);
                } else {
//                    console.log("None!");
                }
            },100,currentTr);
        }
//        return false;
    }

    // Turn on/off the autoscroll feature and cache the status in a cookie for a year
    function toggleAutoscroll() {
        var status = $('#autoscroll').hasClass('enabled');
        if (status) {
            $('#autoscroll').removeClass('enabled').text("Autoscroll Off");
            setCookie('autoscroll','-1',365);
        } else {
            $('#autoscroll').addClass('enabled').text("Autoscroll On");
            setCookie('autoscroll','1',365);
        }
    }
</script>