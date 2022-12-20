import {Tooltip, Tab} from 'bootstrap'
import ClipboardJS from "clipboard";

const $ = jQuery;

export default class Placeholder {

	constructor() {
	}

	static bindEvents() {

		$(window).on('load', function () {
			var clipboard = new ClipboardJS('.juvo_variable_badges');

			//On Success Copy to Clipboard
			clipboard.on('success', function (e) {
				const tooltip = Tooltip.getInstance(e.trigger)

				// setContent example
				tooltip.setContent({ '.tooltip-inner': 'another title' })
				tooltip.show();
			});

			// Init tooltips for already active tab
			Placeholder.initTooltips();
		})

	}

	static initTooltips() {
		const tooltipTriggerList = document.querySelectorAll('.juvo_tooltip [data-bs-toggle="tooltip"]')
		const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl))

		// Reset tooltip after hidden to match title to dom if modified previously
		tooltipTriggerList.addEventListener('hidden.bs.tooltip', (e) => {
			var elem = e.target
			let tooltip = document.getElementById(elem);
			tooltip = new Tooltip(tooltip)
		})
	}

}
