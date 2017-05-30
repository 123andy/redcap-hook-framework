<?php

/**
 *
 * This is a hook that allows you to override the default Y-M-D date format with something else.
 *   Should be active for both data_entry and survey_page
 *  if ($hook_event == "redcap_data_entry_form" || $hook_event == "redcap_survey_page") {

 **/

/**
 * Custom date formatting
 */


$term='@DATEFORMAT';

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
?>
<style>
    .customDateFormat { width: 100px;}
    .validation-alert { display:none; margin-left: 20px; padding:5px;}
    .invalid-alert .validation-alert { display: inline;}
    .invalid-alert input { border:3px solid red;}
</style>

<script type='text/javascript'>

    $(document).ready(function() {
        var date_fields = <?php print json_encode($startup_vars) ?>;

        $.each(date_fields, function(field,params) {
            var tr         	= $('tr[sq_id='+field+']');
            var format = params.params;

            console.log(params);

            var input       = $('input', tr);
            var note        = $('<span class="df">' + format + '</span><span class="btn btn-danger btn-xs validation-alert">This is not a valid date format!</span>');

            // We must cache the current value of input before we apply the datepicker options or else it appears to wipe them.
            var input_val_on_load = input.val();

            // Determine if the 'real' field is a date field or just a normal input
            var inputIsDate = $(input).hasClass('hasDatepicker');

            // Need to preserve tab ordering
            var input_index = $(input).attr('tabindex');

            // If we are using a real date field, then it must by validated as YMD
            var fv = $(input).attr('fv');
            if ( fv && inputIsDate && ( fv != 'date_ymd' ) ) {
                alert ("ACTION TAG CONFIGURATION ERROR:\n\nThe field " + field + " is using the action tag <?php echo $term ?> which requires that the 'real' input field either be UNVALIDATED or validated using the Y-M-D format. See the wiki for more details.  Currently the validation is " + fv + "\n\nPlease fix and try again.");
                $(input).addClass('alert');
                return;
            }

            // Step 1: Make a new input without a name for the custom datepickerer
            var dp_name = "cal-" + field;
            var dp = $('<input id="' + dp_name + '"/>').addClass('customDateFormat').addClass('x-form-text').attr('tabindex', input_index);


            // Step 2: Wrap the original input and its buttons / today stuff / etc... so they can be cleanly hidden
            var wrapper_div = $(input)
                .parent()
                .children("input,img.ui-datepicker-trigger,button,span.df")
                .wrapAll('<div class="wrapper">');

            // Step 3: remove any extra linefeeds from the parent node of the wrapper div (except on surveys) -- this addressed an odd formatting issue on data entry forms in some alignmnets
            if (page != 'surveys/index.php') {
                $(wrapper_div)
                    .parent()
                    .parent()
                    .contents()
                    .filter(function() {
                        //console.log(this, this.nodeType);
                        return this.nodeType == 3; //Node.TEXT_NODE
                    }).remove();
            }

            // Step 4: Hide the wrapper div and append the new input and input/validate notes
            $(wrapper_div)
                .hide()
                .parent()
                .after(note)
                .after(dp);


            // Step 5: Convert the new non-bound input into a datepicker
            dp.datepicker({
                constrainInput: true,
                altField: input,
                dateFormat: format,
                monthNamesShort: [ "JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC" ],
                showOn: "button",
                buttonImage: "/redcap_v7.3.3/Resources/images/date.png",
                buttonImageOnly: true
            });

            // Bind to the real input using YMD format if it is a datefield
            if (inputIsDate) dp.datepicker("option","altFormat", "yy-mm-dd");

            // Add a change event handler to work with manual entry in the input field
            dp.change(function(){

                // If empty, clear out the real input
                if (!$(this).val()) {
                    $(this).data('datepicker').settings['altField'].val('');
                } else {
                    // validate text-entered date
                    var value = $(this).val();
                    var format = $(this).datepicker('option', 'dateFormat');
                    var valueIsValid = false;
                    try {
                        $.datepicker.parseDate(format, value);
                        valueIsValid = true;

                        // Uppercase it if necessary
                        if (value.toUpperCase() != value) $(this).val(value.toUpperCase());

                        $(this).parent().removeClass('invalid-alert');
                        // VALID DATE
                    }
                    catch (e) {
                        // INVALID DATE
                        if ( ! $(this).parent().hasClass('invalid-alert') ) $(this).parent().addClass('invalid-alert');
                        $(this).data('datepicker').settings['altField'].val('');
                    }

                }
            });

            // Set the datepicker from the previously saved input
            if (input_val_on_load.length) {
                if (inputIsDate) {  //&& (input.val().length == 10)
                    var dateString = input_val_on_load.replace(/-/g, '\/').replace(/T.+/, '');
                    dp.datepicker("setDate", new Date( dateString )) ;   // input.val().replace(/-/g, '\/').replace(/T.+/, '') ) ); //moment($(this).prev().val()).toDate());
                } else {
                    dp.val(input_val_on_load)
                }
                dp.trigger('change');
            }
        });
    });
</script>
