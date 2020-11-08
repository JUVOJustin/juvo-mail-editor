<?php

namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class New_User extends Mail_Generator {

	private $placeholder = [
		"password_reset_link"    => ""
	];
	private $text = "";

	function new_user_notification_email(array $email, WP_User $user) {
		$this->text = $this->getCustomField();

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
		return get_field("new_user_message", "option");
	}

	protected function setPlaceholderValues(WP_User $user, array $options): void {
		$adt_rp_key = get_password_reset_key($user);
		$user_login = $user->user_login;
		$this->placeholder["password_reset_link"] = '<a href="' . network_site_url("wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login), 'login') . '">' . network_site_url("wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login), 'login') . '</a>';
	}

}
