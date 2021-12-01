<?php

namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Relay;
use WP_User;

class New_User_Rest extends Mail_Generator {

	protected WP_User $user;

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

	public function send( ...$params ) {
		list( $user ) = $params;

		$this->user = $user;

		$placeholders = $this->getPlaceholderValues();

		$relay = new Relay( $this->getTrigger(), $placeholders, [ "user" => $user ] );
		$relay->sendMails();
	}

	/**
	 * @inheritDoc
	 */
	protected function getPlaceholderValues(): array {

		$placeholders = $this->getDefaultPlaceholder();

		$adt_rp_key                          = get_password_reset_key( $this->user );
		$user_login                          = $this->user->user_login;
		$placeholders["password_reset_link"] = '<a href="' . network_site_url( "wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ), 'login' ) . '">' . network_site_url( "wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ), 'login' ) . '</a>';

		return $placeholders;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultPlaceholder(): array {
		return [
			"password_reset_link" => ""
		];
	}

	public function getTrigger(): string {
		return "new_user_rest";
	}

	public function getSubject(): string {
		return sprintf( __( '%s Login Details' ), "{{site.name}}" );
	}

	public function getMessage(): string {
		$message = sprintf( __( 'Username: %s' ), "{{user.name}}" ) . "\r\n\r\n";
		$message .= __( 'To set your password, visit the following address:' ) . "\r\n\r\n";
		$message .= "{{password_reset_link}}" . "\r\n";

		return $message;
	}

	public function getRecipient(): string {
		return "{{user.user_email}}";
	}

	public function getAlwaysSent(): bool {
		return false;
	}

	public function getLanguage( string $language, array $context ): string {

		if ( isset( $context["user"] ) && $context["user"] instanceof WP_User ) {
			return get_user_locale( $context["user"]->ID );
		}

		return $language;
	}

	protected function getName(): string {
		return "New User Rest (User)";
	}
}
