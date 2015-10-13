<?php
	
/**
	This is a hook utility function that does a directory-based lookup (like ldap) from a web service and uses the results to populate a form
	
@DIRECTORY_LOOKUP={"fullName":"owner_name","lastName":"last_name","firstName":"first_name","email":"owner_email","phone":"owner_phone","department":"department","org_id":"org_id"}
	
	(Currently no spaces are permitted with the hooks parser)
	
	For each key-value pair in the json object, 
		the key is the name of the lookup attribute (returned from your web service)
		the value is the name of the field where that result should be placed.
	
	Andrew Martin
	Stanford University
**/


$term = '@DIRECTORY_LOOKUP';
hook_log("Starting $term for project $project_id", "DEBUG");

$directory_url = 'http://redcap.localhost.com/plugins/example_directory_webservice.php';

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

$startup_vars = array();
foreach($hook_functions[$term] as $field => $details) {
	$params = $details['params'];
	try {
		$params = json_decode($params);
	} catch (Exception $e) {
		hook_log('Caught exception decoding params in $term for project $project_id: ' . $e->getMessage(), "ERROR");
	}
	$i = new stdClass();
	$i->fieldName = $field;
	$i->params = json_decode($details['params']);
	$startup_vars[] = $i;
}

// A progress field turned on/off by javascript
print RCView::div(array('id'=>'directory_lookup_progress', 'style'=>'text-align:right;display:none;color:#777;font-size:12px;'),
	RCView::img(array('src'=>'progress_circle.gif', 'class'=>'imgfix2')) .
	RCView::span(array('style'=>'color:#800000;font-size:12px;padding-right:5px;font-style:italic'),
		"Performing Lookup "
	)
);

// A sample linked field placed next to all destination fields
print RCView::span(array('id'=>'directory_lookup_link', 'title'=>'This field can be auto-populated by SUNET ID lookup','style'=>'display:none;'),
	RCView::img(array('src'=>'link.png', 'class'=>'imgfix2'))
);

// A sample linked field placed next to all source fields
print RCView::span(array('id'=>'directory_lookup_source', 'title'=>'This SUNET field queries the Stanford Who database to find additional user information.','style'=>'display:none;'),
	RCView::img(array('src'=>'find.png', 'class'=>'imgfix2'))
);

// Build some unique keys for additional security...
$key1 = $_SERVER['REMOTE_ADDR'];
$key1 = encrypt(htmlspecialchars($key1));
$key2 = date('Y:m:d H:i:s');
$key2 = encrypt(htmlspecialchars($key2));
?>

<script type='text/javascript'>
$(document).ready(function() {
	var lookupFields = <?php print json_encode($startup_vars); ?>;
	//console.log("LookupFields:");console.log(lookupFields);
	
	function directory_lookup(event) {
		//console.log ('directory_lookup'); console.log(this);
		var field_name = $(this).attr('name');
		var uid = $(this).val();
		
		// Quit if the field is empty
		if (uid.length == 0) return;
		
		var params = event.data.params;
		
		// Get position of query input for progress box
		var pos = $(this).position();
		var posw = $(this).outerWidth();
		$('#directory_lookup_progress').css({
			position: "absolute",
			top: pos.top + "px",
			width: posw + "px",
			left: (pos.left) + "px"
		}).show();
		
		// Do the actual lookup (currently doing it async which allows the data validation to fire first, preventing a loopback issue...)
		$.ajax({
			url: "<?php echo $directory_url ?>",
			dataType: "json",
			async: true,
			type: "POST",
			data: { 
				uid: uid,
				pid: pid,
				key1: "<?php echo $key1 ?>",
				key2: "<?php echo $key2 ?>"
			}
		})
		.done(function( msg ) {
			applyLookupResults(msg, params);
			
			// Turn off the progress spinner
			$('#directory_lookup_progress').fadeOut();
		});
	}

	function applyLookupResults(data, params) {
		//console.log('Params:');console.log(params);
		if (data.status == 'found') {
			// Loop through params which contain the mapping of ldap results to form fieldnames
			for (var resultType in params) {
				if (params.hasOwnProperty(resultType)) {
					// Get the destination field name from the params specified in the notes field
					var destFieldName = params[resultType];
					// Does destination field exist on the current form
					var destField = $('input[name="' + destFieldName + '"]');
					if ($(destField).length) {
						// Was the resultType returned from lookup?
						if (resultType in data) {
							var d = $('<textarea />').html(data[resultType]).text();
							var existingValue = $(destField).val();
							if (existingValue.length && existingValue != d) {
								// We have a new value, let's confirm overwriting the old value
								if (confirm("Do you want to replace " + destFieldName + "'s current value of '" + existingValue + "' with the lookup result '" + d + "'?")) {
									$(destField).val(d);
								}
							} else {
								$(destField).val(d);
							}
						} else {
							// resultType was not returned from lookup
						}
					} else {
						// DestField does not exist on this page
					}
				}
			}
		} else {
			// The directory result was not found
			// simpleDialog(content,title,id,width,onCloseJs,closeBtnTxt,okBtnJs,okBtnTxt) {
			var isValidationError = $('#redcapValidationErrorPopup').css('display') == 'block';
			//console.log ("Validation Error: " + isValidationError);
			
			// We dont want to bring up a second popup if there already is a validation error being displayed since it tended to cause an endless loop of validation errors!
			if (!isValidationError) {
				//console.log("Showing Dialog");
				simpleDialog (data.errorMsg, "SUNET Lookup: " + data.status.toUpperCase());
			}
			//return false;
		}
	}
	
	// Put a link over destination fields to show they are linked to a master lookup field
	function markLookupDestinationFields(source_field, params) {
		for (var resultType in params) {
			if (params.hasOwnProperty(resultType)) {
				var field_name = params[resultType];
				var dest = $('input[name="' + field_name + '"]');
				$(dest).attr('directory_source',source_field);
				var c = $('#directory_lookup_link').clone().show().attr('id', field_name + "_directory_lookup_link").on("click",function() {
					$('input[name="'+source_field+'"]').show('pulsate', 75);
				});
				$(dest).after(c).show();
			}
		}
		
		var s = $('#directory_lookup_source').clone().show().attr('id', source_field + "_directory_lookup_source").on("click",function() {
			//console.log('Yo!');
			$('input[directory_source="'+source_field+'"]').show('pulsate', 75);
		});
		//console.log("S: ");console.log(s);
		$('input[name="' + source_field + '"]').after(s);
	}
	
	// Loop through each lookup field, clean up the notes, and add an event handler
	$(lookupFields).each(function(i, obj) {
		var field_name = obj.fieldName;
		var params = obj.params;
		//console.log('i: ' + i);console.log(field_name);console.log(params);
		
		// Get parent tr for the question
		var tr = $('tr[sq_id="' + field_name + '"]');
		//console.log('tr');console.log(tr);
		
		// Replace term from note if present there
		var note = $('div.note', tr);
		var newNote = $(note).text().replace(/<?php echo $term ?>(={[^}]*})?/,'').trim();
		$(note).text(newNote);
		
		// Add event handler to blur event
		var input = $('input[name="' + field_name + '"]', tr);
		$(input).on("blur", {params: params}, directory_lookup);
		$(input).attr('maxlength', 8);
		
		// Query if it has information (maybe from an upload)
		// This would cause it to re-check each time the page is refreshed
		//if ($(input).val()) $(input).trigger("blur");
		
		// Add indicator to destination fields
		markLookupDestinationFields(field_name, params);
	});
});
</script>
