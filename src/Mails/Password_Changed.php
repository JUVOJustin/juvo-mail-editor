<?php


namespace JUVO_MailEditor\Mails;


use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class Password_Changed extends Mail_Generator {

	private array $placeholder = [];
	private string $text = "";
	private string $subject = "";

	/**
	 * Password_Changed constructor.
	 */
	public function __construct() {
		$this->text    = $this->getMessageCustomField();
		$this->subject = $this->getSubjectCustomField();
	}

	function password_changed_email_message(array $email, array $user, array $userdata) {

		$user = get_user_by('id', $userdata["ID"]);

		if (empty($this->text)) {
			$this->text = $email['message'];
		}

		if (empty($this->subject)) {
			$this->subject = $email['subject'];
		}

		$this->setContentType(true);

		$this->setPlaceholderValues($user, []);
		$this->subject = Placeholder::replacePlaceholder($user, $this->placeholder, $this->subject);
		$this->text = Placeholder::replacePlaceholder($user, $this->placeholder, $this->text);

		$email['message'] = $this->text;
		$email['subject'] = $this->subject;
		return $email;
	}

	private function getSubjectCustomField(): string {
		return get_field("password_changed_subject", "option") ?: "";
	}

	protected function getMessageCustomField(): string {
		return get_field("password_changed_message", "option") ?: "";
	}

	protected function setPlaceholderValues(WP_User $user , array $options): void {
	}

}
