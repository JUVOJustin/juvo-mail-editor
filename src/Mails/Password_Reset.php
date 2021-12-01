<?php


namespace JUVO_MailEditor\Mails;


use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use JUVO_MailEditor\Relay;
use WP_User;

class Password_Reset extends Mail_Generator {

	protected WP_User $user;
	protected string $key;

	public function getTrigger(): string {
		return "password_reset";
	}

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		if ( has_term( $this->getTrigger(), Mail_Trigger_TAX::TAXONOMY_NAME, $cmb->object_id() ) ) {
			$cmb->remove_field( Mails_PT::POST_TYPE_NAME . '_recipients' );
		}

		return $cmb;
	}

	public function send( ...$params ) {
		list( $message, $key, $user_login, $user ) = $params;

		$this->user = $user;
		$this->key  = $key;

		$placeholders = $this->getPlaceholderValues();

		$relay = new Relay( $this->getTrigger(), $placeholders, [ "user" => $user ] );
		$relay->sendMails();

		return "";
	}

	public function getSubject(): string {
		return __( "Password Reset", 'juvo-mail-editor' );
	}

	public function getMessage(): string {
		$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Site Name: %s' ), "{{ site.name }}" ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), "{{ user.name }}" ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= __( '{{PASSWORD_RESET_LINK}}' ) . "\r\n\r\n";

		return $message;
	}

	public function getRecipient(): string {
		return "{{ user.user_email }}";
	}

	protected function getName(): string {
		return "Password Reset (User)";
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultPlaceholder(): array {
		return [
			"password_reset_link" => "",
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getPlaceholderValues(): array {

		$placeholders = $this->getDefaultPlaceholder();

		if ( ! empty( $this->key ) ) {
			$placeholders["password_reset_link"] = '<a href="' . network_site_url( "wp-login.php?action=rp&key={$this->key}&login=" . rawurlencode( $this->user->user_login ), 'login' ) . '">' . network_site_url( "wp-login.php?action=rp&key={$this->key}&login=" . rawurlencode( $this->user->user_login ), 'login' ) . '</a>';
		}

		return $placeholders;
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	public function getLanguage( string $language, array $context ): string {

		if ( isset( $context["user"] ) && $context["user"] instanceof WP_User ) {
			return get_user_locale( $context["user"]->ID );
		}

		return $language;
	}
}
