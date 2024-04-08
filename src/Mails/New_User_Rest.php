<?php

namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use WP_REST_Request;
use WP_User;

class New_User_Rest extends Mail_Generator {

	public function prepareSend( WP_User $user, WP_REST_Request $request, bool $creating ) {
        if (!$creating) {
            return;
        }
		do_action( "juvo_mail_editor_send", $this->getTrigger(), [ "user" => $user ] );
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

		$adt_rp_key                          = get_password_reset_key( $context['user'] );
		$user_login                          = $context['user']->user_login;
		$placeholders['password_reset_link'] = '<a href="' . network_site_url( "wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ), 'login' ) . '">' . network_site_url( "wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ), 'login' ) . '</a>';

		return $placeholders;

	}

	protected function getTrigger(): string {
		return 'new_user_rest';
	}

	public function getSubject( string $subject ): string {

		if ( ! empty( $subject ) ) {
			return $subject;
		}

		return sprintf( __( '%s Login Details', 'default' ), '{{site.name}}' );
	}

	public function getMessage( string $message ): string {

		if ( ! empty( $message ) ) {
			return $message;
		}

		$message = sprintf( __( 'Username: %s', 'default' ), '{{user.name}}' ) . "\r\n\r\n";
		$message .= __( 'To set your password, visit the following address:', 'default' ) . "\r\n\r\n";
		$message .= '{{password_reset_link}}' . "\r\n";

		return $message;
	}

	public function getRecipients( array $recipients ): array {

		if ( ! empty( $recipients ) ) {
			return $recipients;
		}

		return [ '{{user.user_email}}' ];
	}

	public function getAlwaysSent(): bool {
		return false;
	}

	public function getLanguage( string $language, array $context ): string {

		if ( isset( $context['user'] ) && $context['user'] instanceof WP_User ) {
			return apply_filters( "juvo_mail_editor_user_language", '', $context['user'] );
		}

		return $language;
	}

	protected function getName(): string {
		return 'New User Rest (User)';
	}
}
