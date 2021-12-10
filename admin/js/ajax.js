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

	loadPreview();

	$('#global_template').change(function (e) {
		loadPreview();
	});


	function loadPreview() {
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'juvo-mail-editor-template-preview',
				template: $('#global_template').val(),
			},
			success: function (data, textStatus, XMLHttpRequest) {
				updatePreview(data);
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				console.log(errorThrown);
			}
		});
	}

	function updatePreview(preview) {
		const iframeContainer = document.querySelector('#previewContainer');
		const iframe = document.createElement('iframe');

		iframeContainer.innerHTML = '';
		iframeContainer.appendChild(iframe);

		// provide height and width to it
		iframe.setAttribute("style", "height:400px;width:100%;");
		iframe.contentWindow.document.open();
		iframe.contentWindow.document.write(preview);
		iframe.contentWindow.document.close();
	}

	function delay(fn, ms) {
		let timer = 0
		return function (...args) {
			clearTimeout(timer)
			timer = setTimeout(fn.bind(this, ...args), ms || 0)
		}
	}

});
