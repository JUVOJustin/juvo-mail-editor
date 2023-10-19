<?php


namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Trigger_Registry;
use WP_User;

class Password_Reset_Admin extends Mail_Generator {

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		return $cmb;
	}

	public function prepareSend( array $message, $key, $user_login, WP_User $user ): array {


		Trigger_Registry::getInstance()->get( $this->getTrigger() )
			->setContext( [ "user" => $user, 'key' => $key ] );

		// This is not a native wordpress mail. Therefore no mailhook is set and sending need to be added manually
		do_action( "juvo_mail_editor_send", $this->getTrigger(), [ "user" => $user ] );

		return $message;
	}

	public function getSubject( string $subject ): string {

		if ( ! empty( $subject ) ) {
			return $subject;
		}

		return __( 'Password Reset (Admin)', 'juvo-mail-editor' );
	}

	public function getMessage( string $message ): string {

		if ( ! empty( $message ) ) {
			return $message;
		}

		$message = __( 'Someone has requested a password reset for the following account:', 'default' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Site Name: %s', 'default' ), '{{ site.name}}' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'default' ), '{{ user.name }}' ) . "\r\n\r\n";

		return $message;
	}

	public function getRecipients( array $recipients ): array {

		if ( ! empty( $recipients ) ) {
			return $recipients;
		}

		return [ '{{site.admin_email}}' ];
	}

	protected function getTrigger(): string {
		return 'password_reset_admin';
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	protected function getName(): string {
		return 'Password Reset (Admin)';
	}
}
