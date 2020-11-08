<?php


namespace JUVO_MailEditor\Mails;


use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class Password_Reset extends Mail_Generator {

	private $placeholder = [
		"password_reset_link"    => "",
	];
	private $text = "";

	function password_reset_email_message(string $message, string $key, string $user_login, WP_User $user) {
		$this->text = $this->getCustomField();

		if (empty($this->text)) {
			return $message;
		}

		$this->setContentType(true);

		$this->setPlaceholderValues($user, ["key"=>$key]);
		$this->text = Placeholder::replacePlaceholder($user, $this->placeholder, $this->text);

		return $this->text;
	}

	protected function getCustomField(): string {
		return get_field("password_reset_message", "option");
	}

	protected function setPlaceholderValues(WP_User $user, array $options): void {
		$user_login = $user->user_login;
		$this->placeholder["password_reset_link"] = '<a href="' . network_site_url("wp-login.php?action=rp&key={$options["key"]}&login=" . rawurlencode($user_login), 'login') . '">' . network_site_url("wp-login.php?action=rp&key={$options["key"]}&login=" . rawurlencode($user_login), 'login') . '</a>';
	}

}
