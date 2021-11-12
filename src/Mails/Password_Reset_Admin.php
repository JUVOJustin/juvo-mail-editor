<?php


namespace JUVO_MailEditor\Mails;


use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Relay;
use JUVO_MailEditor\Trigger;
use WP_User;

class Password_Reset_Admin extends Mail_Generator {

	private $placeholders = [];

	/**
	 * Sends mailing to admins. Abuses the "retrieve_password_message" hook. Therefore it needs to return $message
	 *
	 * @param string $message
	 * @param string $key
	 * @param string $user_login
	 * @param WP_User $user
	 *
	 * @return string
	 */
	function password_reset_email_admin( string $message, string $key, string $user_login, WP_User $user ): string {

		$this->setPlaceholderValues( $user );

		$relay = new Relay( $this->getTrigger(), $this->placeholders, [ "user" => $user ] );
		$relay->sendMails();

		return "";
	}

	protected function setPlaceholderValues( WP_User $user ): void {
	}

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		return $cmb;
	}

	public function registerTrigger( array $triggers ): array {

		$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Site Name: %s' ), "{{ site.name}}" ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), "{{ user.name }}" ) . "\r\n\r\n";

		$trigger = new Trigger( __( "Password Reset (Admin)", 'juvo-mail-editor' ), $this->getTrigger() );
		$trigger
			->setAlwaysSent( true )
			->setSubject( sprintf( __( 'Password Reset for %s', 'juvo-mail-editor' ), "{{ user.name }}" ) )
			->setContent( $message )
			->setRecipients( "{{ site.admin_email}}" )
			->setPlaceholders( $this->placeholders );

		$triggers[] = $trigger;

		return $triggers;
	}

	public function getTrigger(): string {
		return "password_reset_admin";
	}
}
