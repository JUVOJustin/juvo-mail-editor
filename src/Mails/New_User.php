<?php

namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Trigger_Registry;
use WP_User;

class New_User extends Mail_Generator {

	protected function getTrigger(): string {
		return 'new_user';
	}

	public function prepareSend( array $email, WP_User $user ): array {

		Trigger_Registry::getInstance()->get( $this->getTrigger() )
		                ->setContext( [ "user" => $user ] );

		return $email;
	}

	protected function getMailArrayHook(): string {
		return "wp_new_user_notification_email";
	}

	/**
	 * @param array|null $context
	 *
	 * @return string[]
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

	protected function getName(): string {
		return 'New User (User)';
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
