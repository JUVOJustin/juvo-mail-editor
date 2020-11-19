jQuery(document).ready(function ($) {

	const pageContainer = $(".settings_page_acf-options-mail-editor");
	const formContainer = $("#acf-group_5cbad3da05b4f");

	if (!formContainer || !pageContainer) {
		return;
	}

	// Craeate Preview Div
	$("#submitdiv", pageContainer).after("<div id='mail-editor-preview'></div>");

	// Get all wysiwyg fields from acf
	var fields = acf.getFields ({
		type: 'wysiwyg'
	});

	// Iterate Fields and add event listener
	$.each(fields, function( index, field ) {
		field.on("change", function(e) {

			//ToDo Add Preview Logic via Ajax Call

		})

	});

});

