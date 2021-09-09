<?php

namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Relay;
use JUVO_MailEditor\Trigger;
use WP_User;

class New_User_Admin extends Mail_Generator {

	private $placeholders = [];

	function new_user_notification_email_admin( array $email, WP_User $user ): array {

		$this->setPlaceholderValues( $user );

		$relay = new Relay( $this->getTrigger(), $this->placeholders, $user );
		$relay->sendMails();

		return [];
	}

	protected function setPlaceholderValues( WP_User $user ): void {
	}

	/**
	 * @return string
	 */
	public function getTrigger(): string {
		return "new_user_admin";
	}

	/**
	 * @param CMB2 $cmb
	 *
	 * @return CMB2
	 */
	public function addCustomFields( CMB2 $cmb ): CMB2 {
		return $cmb;
	}

	/**
	 * @param array $triggers
	 *
	 * @return Trigger[]
	 */
	public function registerTrigger( array $triggers ): array {

		$message = sprintf( __( 'New user registration on your site %s:' ), "{{SITE_NAME}}" ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), "{{USERNAME}}" ) . "\r\n\r\n";
		$message .= sprintf( __( 'Email: %s' ), "{{USER_EMAIL}}" ) . "\r\n";

		$trigger = new Trigger( __( "New User (Admin)", 'juvo-mail-editor' ), $this->getTrigger() );
		$trigger
			->setAlwaysSent( true )
			->setSubject( sprintf( __( "%s New User Registration" ), "{{SITE_NAME}}" ) )
			->setContent( $message )
			->setRecipients( "{{ADMIN_EMAIL}}" )
			->setPlaceholders( $this->placeholders );

		$triggers[] = $trigger;

		return $triggers;
	}
}
