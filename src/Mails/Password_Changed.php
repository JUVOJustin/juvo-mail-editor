<?php


namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use JUVO_MailEditor\Trigger_Registry;
use WP_User;

class Password_Changed extends Mail_Generator {

	protected function getTrigger(): string {
		return 'password_changed';
	}

	public function prepareSend( array $email, array $user ): array {

		Trigger_Registry::getInstance()->get( $this->getTrigger() )
		                ->setContext( [ "user" => get_user_by( "ID", $user['ID'] ) ] );

		return $email;
	}

	protected function getMailArrayHook(): string {
		return "wp_password_change_notification_email";
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	public function getLanguage( string $language, array $context ): string {

		if ( isset( $context['user'] ) && $context['user'] instanceof WP_User ) {
			return apply_filters( "juvo_mail_editor_user_language", '', $context['user'] );
		}

		return $language;
	}

	protected function getName(): string {
		return 'Password Changed (User)';
	}
}
