<?php

namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use WP_User;

class New_User_Admin extends Mail_Generator {

	/**
	 * @return string
	 */
	protected function getTrigger(): string {
		return 'new_user_admin';
	}

	public function prepareSend( array $email, WP_User $user ): array {
		do_action( "juvo_mail_editor_send", $this->getTrigger(), [ "user" => $user ] );
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

	public function getAlwaysSent(): bool {
		return true;
	}
}
