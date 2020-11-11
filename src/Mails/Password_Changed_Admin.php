<?php


namespace JUVO_MailEditor\Mails;


use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class Password_Changed_Admin extends Mail_Generator {

	private array $placeholder = [];
	private string $text = "";
	private string $subject = "";

	/**
	 * Password_Changed_Admin constructor.
	 */
	public function __construct() {
		$this->text    = $this->getMessageCustomField();
		$this->subject = $this->getSubjectCustomField();
	}

	function send_password_changed_email_message( array $user, array $userdata) {

		$user = get_user_by('id', $userdata["ID"]);

		if (empty($this->text) || empty($this->subject)) {
			return;
		}

		$this->setPlaceholderValues($user, []);
		$this->subject = Placeholder::replacePlaceholder($user, $this->placeholder, $this->subject);
		$this->text = Placeholder::replacePlaceholder($user, $this->placeholder, $this->text);
		$this->text = $this->setContentType($this->text);

		wp_mail(get_bloginfo("admin_email"), $this->subject , $this->text);
	}

	protected function getSubjectCustomField(): string {
		return get_field("password_changed_subject_admin", "option") ?: "";
	}

	protected function getMessageCustomField(): string {
		return get_field("password_changed_message", "option") ?: "";
	}

	protected function setPlaceholderValues(WP_User $user , array $options): void {
	}

}
