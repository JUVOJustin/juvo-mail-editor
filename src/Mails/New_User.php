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
	public function new_user_notification_email( array $email, WP_User $user ) {

		$this->setPlaceholderValues( $user );

		$relay = new Relay( $this->getTrigger(), $this->placeholders, $user );
		$relay->sendMails();
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

		$this->setPlaceholderValues( $user );

		$relay = new Relay( $this->getTrigger(), $this->placeholders, $user );
		$relay->sendMails();

	}

	/**
	 * @param array $triggers
	 *
	 * @return Trigger[]
	 */
	public function registerTrigger( array $triggers ): array {

		$message = __( 'New user registration on your site {{SITE_NAME}}:' ) . "\r\n\r\n";
		$message .= __( 'Username: {{USERNAME}}' ) . "\r\n\r\n";
		$message .= __( 'Email: {{USER_EMAIL}}' ) . "\r\n";

		$trigger = new Trigger( "New User (User)", $this->getTrigger() );
		$trigger
			->setSubject( "New User Registration" )
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
