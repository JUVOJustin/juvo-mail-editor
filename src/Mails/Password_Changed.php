<?php


namespace JUVO_MailEditor\Mails;


use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class Password_Changed extends Mail_Generator {

	private $placeholder = [];
	private $text = "";

	function password_changed_email_message(array $email, array $user, array $userdata) {
		$this->text = $this->getCustomField();
		$user = get_user_by('id', $userdata["ID"]);

		if (empty($this->text)) {
			return $email;
		}

		$this->setContentType(true);

		$this->setPlaceholderValues($user, []);
		$this->text = Placeholder::replacePlaceholder($user, $this->placeholder, $this->text);

		$email['message'] = $this->text;
		return $email;
	}

	protected function getCustomField(): string {
		return get_field("password_changed_message", "option");
	}

	protected function setPlaceholderValues(WP_User $user , array $options): void {
	}

}
