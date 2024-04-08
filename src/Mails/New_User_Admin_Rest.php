<?php

namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use WP_REST_Request;
use WP_User;

class New_User_Admin_Rest extends Mail_Generator {

	public function prepareSend( WP_User $user, WP_REST_Request $request, bool $creating ): void {
        if (!$creating) {
            return;
        }
		do_action( "juvo_mail_editor_send", $this->getTrigger(), [ "user" => $user ] );
	}

	/**
	 * @return string
	 */
	protected function getTrigger(): string {
		return 'new_user_admin_rest';
	}

	public function getSubject( string $subject ): string {

		if ( ! empty( $subject ) ) {
			return $subject;
		}

		return sprintf( __( '[%s] New User Registration', 'default' ), '{{site.name}}' );
	}

	public function getMessage( string $message ): string {

		if ( ! empty( $message ) ) {
			return $message;
		}

		$message = sprintf( __( 'New user registration on your site %s:', 'default' ), '{{site.name}}' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'default' ), '{{user.name}}' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Email: %s', 'default' ), '{{user.user_email}}' ) . "\r\n";

		return $message;
	}

	public function getRecipients( array $recipients ): array {

		if ( ! empty( $recipients ) ) {
			return $recipients;
		}

		return [ '{{site.admin_email}}' ];
	}

	protected function getName(): string {
		return 'New User Rest (Admin)';
	}

	public function getAlwaysSent(): bool {
		return false;
	}
}
