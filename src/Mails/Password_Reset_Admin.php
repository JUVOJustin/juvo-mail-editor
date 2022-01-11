<?php


namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use WP_User;

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

	public function prepareSend( $message, $key, $user_login, WP_User $user ): string {
		do_action( "juvo_mail_editor_send", $this->getTrigger(), [ "user" => $user, 'key' => $key ] );
		return '';
	}

	protected function getTrigger(): string {
		return 'password_reset_admin';
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	protected function getName(): string {
		return 'Password Reset (Admin)';
	}
}
