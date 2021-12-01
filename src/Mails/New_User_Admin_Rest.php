<?php

namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Relay;

class New_User_Admin_Rest extends Mail_Generator {

	/**
	 * @param CMB2 $cmb
	 *
	 * @return CMB2
	 */
	public function addCustomFields( CMB2 $cmb ): CMB2 {
		return $cmb;
	}

	public function send( ...$params ) {
		list( $user ) = $params;

		$placeholders = $this->getPlaceholderValues();

		$relay = new Relay( $this->getTrigger(), $placeholders, [ "user" => $user ] );
		$relay->sendMails();
	}

	/**
	 * @inheritDoc
	 */
	protected function getPlaceholderValues(): array {
		return [];
	}

	/**
	 * @return string
	 */
	public function getTrigger(): string {
		return "new_user_admin_rest";
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
		return "New User Rest (Admin)";
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultPlaceholder(): array {
		return [];
	}

	public function getAlwaysSent(): bool {
		return false;
	}

	public function getLanguage( string $language, array $context ): string {
		return $language;
	}
}
