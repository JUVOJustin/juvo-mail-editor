<?php

namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class New_User_Admin extends Mail_Generator {

	private array $placeholder = [];
	private string $text = "";
	private string $subject = "";

	/**
	 * New_User_Admin constructor.
	 */
	public function __construct() {
		$this->text    = $this->getMessageCustomField();
		$this->subject = $this->getSubjectCustomField();
	}

	function new_user_notification_email_admin( array $email, WP_User $user ) {

		if (empty($this->text)) {
			$this->text = $email['message'];
		}

		if (empty($this->subject)) {
			$this->subject = $email['subject'];
		}

		$this->setPlaceholderValues( $user, [] );
		$this->subject = Placeholder::replacePlaceholder($user, $this->placeholder, $this->subject);
		$this->text = Placeholder::replacePlaceholder($user, $this->placeholder, $this->text);
		$this->text = $this->setContentType($this->text);

		$email['message'] = $this->text;
		$email['subject'] = $this->subject;
		return $email;
	}

	protected function getSubjectCustomField(): string {
		return get_field("new_user_subject_admin", "option") ?: "";
	}

	protected function getMessageCustomField(): string {
		return get_field("new_user_message_admin", "option") ?: "";
	}

	protected function setPlaceholderValues( WP_User $user, array $options ): void {
	}

}
