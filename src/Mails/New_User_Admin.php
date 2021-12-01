<?php

namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Relay;
use WP_User;

class New_User_Admin extends Mail_Generator {

	protected WP_User $user;

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

	public function send( ...$params ) {
		list( $email, $user ) = $params;

		$this->user = $user;

		$placeholders = $this->getPlaceholderValues();

		$relay = new Relay( $this->getTrigger(), $placeholders, [ "user" => $user ] );
		$relay->sendMails();

		return [];
	}

	public function getSubject(): string {
		return sprintf( __( "%s New User Registration" ), "{{site.name}}" );
	}

	public function getMessage(): string {
		$message = sprintf( __( 'New user registration on your site %s:' ), "{{site.name}}" ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), "{{user.name}}" ) . "\r\n\r\n";
		$message .= sprintf( __( 'Email: %s' ), "{{user.user_email}}" ) . "\r\n";

		return $message;
	}

	public function getRecipient(): string {
		return "{{site.admin_email}}";
	}

	protected function getName(): string {
		return "New User (Admin)";
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultPlaceholder(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	protected function getPlaceholderValues(): array {
		return [];
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	public function getLanguage( string $language, array $context ): string {
		return $language;
	}
}
