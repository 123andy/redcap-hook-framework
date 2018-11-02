<?php
	
/**
	This is a hook utility function that makes HTML MATRIX tables based on label configuration

	I am pretty sure we can replace the inputmatrix with this version but I would like to test before doing it and don't have tim et o do that right now...

	Andrew Martin
	Stanford University
**/

$term = '@INPUTMATRIX';
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


# Step 1 - Create array of fields to hide and inject
$startup_vars = array();
foreach($hook_functions[$term] as $field => $details) {
	$startup_vars[] = $field;
}
?>

<script type='text/javascript'>
$(document).ready(function() {
	var matrixFields = <?php print json_encode($startup_vars); ?>;
	//console.log(matrixFields);
	
	// Loop through each field_name
	$(matrixFields).each(function(i, field_name) {
		//console.log('i: ' + i);console.log(field_name);
		
		// Get parent tr for table
		var tr = $('tr[sq_id="' + field_name + '"]');
		//console.log('tr');console.log(tr);
		
		// Hide the input
		$('input[name="' + field_name + '"]', tr).hide();
		
		// Replace term from note if present
		var note = $('div.note', tr);
		$(note).text($(note).text().replace('<?php echo $term ?>', ''));
		
		// Get table in label
		var t = $('td.labelrc table.inputmatrix', tr);
		
		// Remove the br's that REDCap inserts before the table
		$(t).siblings('br').remove();
		
		// Iterate through each 'th' in the table
		$('th', t).each(function(j, th) {
			// Get the contents of the th cell
			var th_label = $(th).text();  // This is the text in the TH element
			//console.log('j:' + j);console.log(th);console.log(th_label);
			
			// Search for a tr element with the id from the th cell
			var real_tr = $("tr[sq_id='" + th_label + "']");
			if ($(real_tr).length) {
				// Get the label
				var real_label = $("td.labelrc:not(.quesnum):not(.questionnum)", $(real_tr));
				// Move the label into the table and add a 'label' class for rendering
				$(th).html($(real_label.contents()));
			}
			
			if (th_label.length > 0) {
				$(th).addClass('labelrc');
			}
		});
	
		// Iterate through each 'td' in the table
		$('td', t).each(function(j, td) {
			// Get the contents of the td cell
			var td_label = $(td).text();  // This is the text in the TD element
			//console.log('j:' + j);console.log(td);console.log(td_label);
		
			// Search for a tr element with the id from the td cell
			var real_tr = $("tr[sq_id='" + td_label + "']");
			if ($(real_tr).length) {
				// Get the input
				//console.log('Found ' + td_label);
				var trInputs = $(":input", $(real_tr));
				if ($(trInputs).length) {
					//console.log(trInputs);
					var type = $(trInputs).prop('type');
					
					//limit width of inputs
					//if (type=='text') $(trInputs).css('width',50);
					
					if (type=='text') $(trInputs).removeAttr('size');
					//console.log(type);
					//console.log(trInputs);
					
					//limit width of inputs
					if (type=='textarea') $(trInputs).css('width','95%');
					
					// Move it to the td cell
					$(td).html($(trInputs));
				
					// Hide the TRs.
					$(real_tr).css('display','none');
				} else {
					//console.log ("Unable to find input in " . td_label);
				}
			}
		});
	});
});
</script>
