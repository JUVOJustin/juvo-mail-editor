<?php

namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mails_PT;
use JUVO_MailEditor\Relay;
use JUVO_MailEditor\Trigger;
use WP_User;

class New_User extends Mail_Generator {

	private $placeholders = [
		"password_reset_link" => ""
	];

	/**
	 * Callback for wordpresss native user trigger
	 *
	 * @param array $email
	 * @param WP_User $user
	 *
	 */
	public function new_user_notification_email( array $email, WP_User $user ): array {

		$this->setPlaceholderValues( $user );

		$relay            = new Relay( $this->getTrigger(), $this->placeholders, $user );
		$email["to"]      = $relay->prepareRecipients();
		$email["subject"] = $relay->prepareSubject();
		$email["message"] = $relay->prepareContent();

		return $email;
	}

	protected function setPlaceholderValues( WP_User $user ): void {
		$adt_rp_key                                = get_password_reset_key( $user );
		$user_login                                = $user->user_login;
		$this->placeholders["password_reset_link"] = '<a href="' . network_site_url( "wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ), 'login' ) . '">' . network_site_url( "wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ), 'login' ) . '</a>';
	}

	/**
	 * @param WP_User $user
	 */
	public function rest_user_create( WP_User $user ): void {

		$user_id = $user->ID;
		wp_send_new_user_notifications( $user_id );

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

		$trigger = new Trigger( __( "New User (User)", 'juvo-mail-editor' ), $this->getTrigger() );
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

		$field = array(
			'name' => __( 'Trigger on rest', 'juvo-mail-editor' ),
			'desc' => __( 'Sends email if user is created via Rest API', 'juvo-mail-editor' ),
			'id'   => Mails_PT::POST_TYPE_NAME . '_rest',
			'type' => 'checkbox',
		);

		return $this->addFieldForTrigger( $field, $cmb );

	}

	public function getTrigger(): string {
		return "new_user";
	}
}
