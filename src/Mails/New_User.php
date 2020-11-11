<?php

namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class New_User extends Mail_Generator {

	private array $placeholder = [
		"password_reset_link" => ""
	];
	private string $text = "";
	private string $subject = "";

	/**
	 * New_User constructor.
	 */
	public function __construct() {
		$this->text    = $this->getMessageCustomField();
		$this->subject = $this->getSubjectCustomField();
	}

	function new_user_notification_email(array $email, WP_User $user) {

		if (empty($this->text)) {
			$this->text = $email['message'];
		}

		if (empty($this->subject)) {
			$this->subject = $email['subject'];
		}

		$this->setPlaceholderValues($user, []);
		$this->subject = Placeholder::replacePlaceholder($user, $this->placeholder, $this->subject);
		$this->text = Placeholder::replacePlaceholder($user, $this->placeholder, $this->text);
		$this->text = $this->setContentType($this->text);

		$email['message'] = $this->text;
		$email['subject'] = $this->subject;
		return $email;
	}

	protected function getSubjectCustomField(): string {
		return get_field("new_user_subject", "option") ?: "";
	}

	protected function getMessageCustomField(): string {
		return get_field("new_user_message", "option") ?: "";
	}

	protected function setPlaceholderValues(WP_User $user, array $options): void {
		$adt_rp_key = get_password_reset_key($user);
		$user_login = $user->user_login;
		$this->placeholder["password_reset_link"] = '<a href="' . network_site_url("wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login), 'login') . '">' . network_site_url("wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login), 'login') . '</a>';
	}

}
