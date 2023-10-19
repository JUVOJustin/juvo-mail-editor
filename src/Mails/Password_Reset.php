<?php


namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Trigger_Registry;
use WP_User;

class Password_Reset extends Mail_Generator {

	protected function getTrigger(): string {
		return 'password_reset';
	}

	public function prepareSend( array $args, string $key, $user_login, WP_User $user ): array {

		Trigger_Registry::getInstance()->get( $this->getTrigger() )
			->setContext( [ "user" => $user, 'key' => $key ] );

		return $args;
	}

	protected function getMailArrayHook(): string {
		return "retrieve_password_notification_email";
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
			$placeholders['password_reset_link'] = network_site_url( "wp-login.php?action=rp&key={$context['key']}&login=" . rawurlencode( $context['user']->user_login ), 'login' );
		}

		return $placeholders;
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	public function getLanguage( string $language, array $context ): string {

		if ( isset( $context['user'] ) && $context['user'] instanceof WP_User ) {
			return apply_filters( "juvo_mail_editor_user_language", '', $context['user'] );
		}

		return $language;
	}
}
