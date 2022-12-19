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

});
