<?php


namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Trigger_Registry;
use WP_User;

/**
 * Class Password_Changed_Admin
 * 
 * Triggered after the user changed his password. By default only triggered when using the password reset form, not on the profile page
 */
class Password_Changed_Admin extends Mail_Generator {

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		return $cmb;
	}

	protected function getTrigger(): string {
		return 'password_changed_admin';
	}

	public function prepareSend( array $email, WP_User $user ): array {

		Trigger_Registry::getInstance()->get( $this->getTrigger() )
		                ->setContext( [ "user" => $user ] );

		return $email;
	}

	protected function getMailArrayHook(): string {
		return "wp_password_change_notification_email";
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	protected function getName(): string {
		return 'Password Changed (Admin)';
	}
}
