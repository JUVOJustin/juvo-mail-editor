jQuery(document).ready(function ($) {

	$('#sync-triggers').click(function () {
		var data = {
			action: 'juvo-mail-editor-sync-triggers',
		};

		$.getJSON(ajaxurl, data, function (json) {
			if (json.success) {
				$(".sync-triggers-wrapper .message").replaceWith(
					"<div class='message notice notice-success'>" +
					"<b>" + json.data.title + "</b>" +
					"<p>" + json.data.message + "</p>" +
					"</div>");
			} else {
				let errors = "<b>Error</b><ul>";
				json.data.forEach(function (item) {
					errors += "<li>" + item.code + ": " + item.message + "</li>";
				});
				errors += "</ul>";
				$(".sync-triggers-wrapper .message").replaceWith("<code class='message notice notice-error'>" + errors + "</code>");
			}
		});
	});


	//Copy the variable from the badges
	$('.juvo_variable_badges').click(function (event) { 
		
		navigator.clipboard.writeText("");
		
		//Put the badge value in an input
		$("#badgesInput").val(event.target.text) ;

		 // Get the text field
		 var copyText = $("#badgesInput");

		 // Select the text field
		 copyText.select();
		 
	   
		 // Copy the text inside the text field
		 navigator.clipboard.writeText(copyText.val());
		 
		 // Alert the copied text
		 alert("You Copied the text : " + copyText.val());
	});


});
