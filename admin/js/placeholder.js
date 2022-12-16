var clipboard = new ClipboardJS('.juvo_variable_badges');

//On Success Copy to Clipboard
clipboard.on('success', function (e) {

	//Show Tooltip
	e.trigger.nextElementSibling.style.visibility = 'visible';
	setTimeout(function () {
		e.trigger.nextElementSibling.style.visibility = 'hidden';
	}, 1500);
	
});