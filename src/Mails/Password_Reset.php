<?php


namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use WP_User;

class Password_Reset extends Mail_Generator {

	protected function getTrigger(): string {
		return 'password_reset';
	}

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		if ( has_term( $this->getTrigger(), Mail_Trigger_TAX::TAXONOMY_NAME, $cmb->object_id() ) ) {
			$cmb->remove_field( Mails_PT::POST_TYPE_NAME . '_recipients' );
		}

		return $cmb;
	}

	public function prepareSend( $message, string $key, $user_login, WP_User $user ): string {
		do_action( "juvo_mail_editor_send", $this->getTrigger(), [ "user" => $user, 'key' => $key ] );

		return '';
	}

	public function getSubject( string $subject ): string {

		if ( ! empty( $subject ) ) {
			return $subject;
		}

		return __( 'Password Reset', 'juvo-mail-editor' );
	}

	public function getMessage( string $message ): string {

		if ( ! empty( $message ) ) {
			return $message;
		}

		$message = __( 'Someone has requested a password reset for the following account:', 'default' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Site Name: %s', 'default' ), '{{ site.name }}' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'default' ), '{{ user.name }}' ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, ignore this email and nothing will happen.', 'default' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:', 'default' ) . "\r\n\r\n";
		$message .= '{{password_reset_link}}' . "\r\n\r\n";

		return $message;
	}

	public function getRecipients( array $recipients ): array {

		if ( ! empty( $recipients ) ) {
			return $recipients;
		}

		return [ '{{user.user_email}}' ];
	}

	protected function getName(): string {
		return 'Password Reset (User)';
	}

	/**
	 * @inheritDoc
	 */
	public function getPlaceholders( array $placeholders, ?array $context ): array {

		$placeholders = array_merge( $placeholders, array(
			'password_reset_link' => '',
		) );

		if ( empty( $context ) ) {
			return $placeholders;
		}

		if ( ! empty( $context['key'] ) ) {
			$placeholders['password_reset_link'] = '<a href="' . network_site_url( "wp-login.php?action=rp&key={$context['key']}&login=" . rawurlencode( $context['user']->user_login ), 'login' ) . '">' . network_site_url( "wp-login.php?action=rp&key={$context['key']}&login=" . rawurlencode( $context['user']->user_login ), 'login' ) . '</a>';
		}

		return $placeholders;
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	public function getLanguage( string $language, array $context ): string {

		if ( isset( $context['user'] ) && $context['user'] instanceof WP_User ) {
			return get_user_locale( $context['user']->ID );
		}

		return $language;
	}
}
