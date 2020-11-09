<?php


namespace JUVO_MailEditor\Mails;


use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class Password_Reset_Admin extends Mail_Generator {

	private array $placeholder = [];
	private string $text = "";
	private string $subject = "";

	/**
	 * Password_Reset_Admin constructor.
	 */
	public function __construct() {
		$this->text    = $this->getMessageCustomField();
		$this->subject = $this->getSubjectCustomField();
	}

	function password_reset_email_message(WP_User $user) {

		if (empty($this->text) || empty($this->subject)) {
			return;
		}

		$this->setContentType(true);

		$this->setPlaceholderValues($user, []);
		$this->subject = Placeholder::replacePlaceholder($user, $this->placeholder, $this->subject);
		$this->text = Placeholder::replacePlaceholder($user, $this->placeholder, $this->text);

		wp_mail(get_bloginfo("admin_email"), $this->subject , $this->text);
	}

	private function getSubjectCustomField(): string {
		return get_field("password_reset_subject_admin", "option") ?: "";
	}

	protected function getMessageCustomField(): string {
		return get_field("password_reset_message_admin", "option") ?: "";
	}

	protected function setPlaceholderValues(WP_User $user , array $options): void {
	}

}
