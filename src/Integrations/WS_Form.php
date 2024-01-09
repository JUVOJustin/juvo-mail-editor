<?php

namespace JUVO_MailEditor\Integrations;

class WS_Form {

	public function header_filter_callback(array $headers, $form, $submit, array $action) {

		$actions = $form->meta->action->groups[0]->rows;

		foreach ($actions as $action) {

		}


		return $headers;

	}

}
