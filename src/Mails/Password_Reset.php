<?php


namespace JUVO_MailEditor\Mails;


use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class Password_Reset extends Mail_Generator {

	private array $placeholder = [
		"password_reset_link" => "",
	];
	private string $text = "";
	private string $subject = "";

	/**
	 * Password_Reset constructor.
	 */
	public function __construct() {
		$this->text    = $this->getMessageCustomField();
		$this->subject = $this->getSubjectCustomField();
	}

	function password_reset_email_message(string $message, string $key, string $user_login, WP_User $user) {
		$this->text = $this->getMessageCustomField();

		if (empty($this->text)) {
			return $message;
		}

		$this->setContentType(true);

		$this->setPlaceholderValues($user, ["key"=>$key]);
		$this->text = Placeholder::replacePlaceholder($user, $this->placeholder, $this->text);

		return $this->text;
	}

	function password_reset_email_subject(string $title, string $user_login, WP_User $user) {

		if (empty($this->subject)) {
			return $title;
		}

		$this->setPlaceholderValues($user, ["key"=>"none"]);
		$this->subject = Placeholder::replacePlaceholder($user, $this->placeholder, $this->subject);

		return $this->subject;
	}

	protected function getMessageCustomField(): string {
		return get_field("password_reset_message", "option") ?: "";
	}

	private function getSubjectCustomField(): string {
		return get_field("password_reset_subject", "option") ?: "";
	}

	protected function setPlaceholderValues(WP_User $user, array $options): void {
		$user_login = $user->user_login;
		$this->placeholder["password_reset_link"] = '<a href="' . network_site_url("wp-login.php?action=rp&key={$options["key"]}&login=" . rawurlencode($user_login), 'login') . '">' . network_site_url("wp-login.php?action=rp&key={$options["key"]}&login=" . rawurlencode($user_login), 'login') . '</a>';
	}

}
