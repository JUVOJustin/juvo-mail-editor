<?php


namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Relay;

class Password_Reset_Admin extends Mail_Generator {

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		return $cmb;
	}

	public function getSubject(): string {
		return __( 'Password Reset (Admin)', 'juvo-mail-editor' );
	}

	public function getMessage(): string {
		$message = __( 'Someone has requested a password reset for the following account:', 'default' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Site Name: %s', 'default' ), '{{ site.name}}' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'default' ), '{{ user.name }}' ) . "\r\n\r\n";

		return $message;
	}

	public function getRecipient(): string {
		return '{{ site.admin_email}}';
	}

	public function send( ...$params ) {
		list( $message, $key, $user_login, $user ) = $params;

		$placeholders = $this->getPlaceholderValues();

		$relay = new Relay( $this->getTrigger(), $placeholders, array( 'user' => $user ) );
		$relay->sendMails();

		return '';
	}

	/**
	 * @inheritDoc
	 */
	protected function getPlaceholderValues(): array {
		return array();
	}

	public function getTrigger(): string {
		return 'password_reset_admin';
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultPlaceholder(): array {
		return array();
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	public function getLanguage( string $language, array $context ): string {
		return get_bloginfo( 'language' );
	}

	protected function getName(): string {
		return 'Password Reset (Admin)';
	}
}
