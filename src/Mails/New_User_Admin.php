<?php

namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Trigger_Registry;
use WP_User;

class New_User_Admin extends Mail_Generator {

	/**
	 * @return string
	 */
	protected function getTrigger(): string {
		return 'new_user_admin';
	}

	public function prepareSend( array $email, WP_User $user ): array {
		Trigger_Registry::getInstance()->get( $this->getTrigger() )
		                ->setContext( [ "user" => $user ] );

		return $email;
	}

	protected function getMailArrayHook(): string {
		return "wp_new_user_notification_email_admin";
	}

	protected function getName(): string {
		return 'New User (Admin)';
	}

	public function getAlwaysSent(): bool {
		return true;
	}
}
