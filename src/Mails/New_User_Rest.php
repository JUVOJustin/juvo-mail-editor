<?php

namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Relay;
use JUVO_MailEditor\Trigger;
use WP_User;

class New_User_Rest extends Mail_Generator {

	private $placeholders = [
		"password_reset_link" => ""
	];

	/**
	 * Callback for wordpresss native user trigger
	 *
	 * @param WP_User $user
	 *
	 * @return void
	 */
	public function new_user_notification_email( WP_User $user ) {

		$this->setPlaceholderValues( $user );

		$relay = new Relay( $this->getTrigger(), $this->placeholders, $user );
		$relay->sendMails();
	}

	protected function setPlaceholderValues( WP_User $user ): void {
		$adt_rp_key                                = get_password_reset_key( $user );
		$user_login                                = $user->user_login;
		$this->placeholders["password_reset_link"] = '<a href="' . network_site_url( "wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ), 'login' ) . '">' . network_site_url( "wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ), 'login' ) . '</a>';
	}

	public function getTrigger(): string {
		return "new_user_rest";
	}

	/**
	 * @param array $triggers
	 *
	 * @return Trigger[]
	 */
	public function registerTrigger( array $triggers ): array {

		$message = sprintf( __( 'Username: %s' ), "{{USERNAME}}" ) . "\r\n\r\n";
		$message .= __( 'To set your password, visit the following address:' ) . "\r\n\r\n";
		$message .= "{{password_reset_link}}" . "\r\n";

		$trigger = new Trigger( __( "New User Rest (User)", 'juvo-mail-editor' ), $this->getTrigger() );
		$trigger
			->setAlwaysSent( true )
			->setSubject( sprintf( __( '%s Login Details' ), "{{SITE_NAME}}" ) )
			->setContent( $message )
			->setRecipients( "{{CONTEXT}}" )
			->setPlaceholders( $this->placeholders );

		$triggers[] = $trigger;

		return $triggers;

	}

	/**
	 * Add Custom Fields to metabox
	 *
	 * @param CMB2 $cmb
	 *
	 * @return CMB2
	 */
	public function addCustomFields( CMB2 $cmb ): CMB2 {
		return $cmb;
	}
}
