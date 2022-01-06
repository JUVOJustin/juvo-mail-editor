<?php

namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Relay;
use WP_User;

class New_User_Admin extends Mail_Generator {

	protected WP_User $user;

	/**
	 * @return string
	 */
	protected function getTrigger(): string {
		return 'new_user_admin';
	}

	public function send( ...$params ) {
		list( $email, $user ) = $params;

		$this->user = $user;

		$placeholders = $this->getPlaceholderValues();

		$relay = new Relay( $this->getTrigger(), $placeholders, array( 'user' => $user ) );
		$relay->sendMails();

		return $this->emptyMailArray( $email );
	}

	public function getSubject(): string {
		return sprintf( __( '[%s] New User Registration', 'default' ), '{{site.name}}' );
	}

	public function getMessage(): string {
		$message = sprintf( __( 'New user registration on your site %s:', 'default' ), '{{site.name}}' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'default' ), '{{user.name}}' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Email: %s', 'default' ), '{{user.user_email}}' ) . "\r\n";

		return $message;
	}

	public function getRecipient(): string {
		return '{{site.admin_email}}';
	}

	protected function getName(): string {
		return 'New User (Admin)';
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultPlaceholder(): array {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	protected function getPlaceholderValues(): array {
		return array();
	}

	public function getAlwaysSent(): bool {
		return true;
	}
}
