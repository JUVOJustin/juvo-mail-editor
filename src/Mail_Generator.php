<?php


namespace JUVO_MailEditor;


use WP_User;

abstract class Mail_Generator {

	protected function setContentType(bool $html): string {

		$type = "";
		if ($html) {
			$type = 'text/html';
		} else {
			$type = 'text/plain';
		}

		add_filter('wp_mail_content_type', function($content_type) use ($type) {
			return $type;
		});

		return $type;
	}

	abstract protected function getMessageCustomField(): string;

	abstract protected function setPlaceholderValues(WP_User $user, array $options): void;

}
