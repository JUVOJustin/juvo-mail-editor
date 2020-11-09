<?php


namespace JUVO_MailEditor;


use JUVO_MailEditor\Mails\Password_Changed_Admin;
use JUVO_MailEditor\Mails\Password_Reset_Admin;
use WP_User;

class Mail_Options {

	public function rest_user_create( WP_User $user ): void {

		$rest_notification = get_field( "new_user_recipient_rest", "option" ) ?: 'both';

		$user_id = $user->ID;
		wp_send_new_user_notifications( $user_id, $rest_notification );

	}

	public function new_user_notifications( int $user_id, string $notify ) {

		$admin_notification = get_field( "new_user_toggle_admin", "option" );

		if ( ! $admin_notification ) {
			switch ( $notify ) {
				case "admin":
					$notify = "none";
					break;
				case "both":
					$notify = "user";
					break;
			}
		}

		wp_new_user_notification( $user_id, null, $notify );
	}

	public function password_changed_email( bool $send, array $user, array $userdata ): bool {
		$password_changed_notification = get_field( "password_changed_recipient", "option" );

		$pw_changed_admin = new Password_Changed_Admin();

		switch ( $password_changed_notification ) {
			case "admin":
				$pw_changed_admin->send_password_changed_email_message( $user, $userdata );

				return false;
			case "both":
				$pw_changed_admin->send_password_changed_email_message( $user, $userdata );

				return true;
			case "none":
				return false;
			default:
				return true;
		}
	}

	public function password_reset_email( string $message, string $key, string $user_login, WP_User $user ) {
		$password_changed_notification = get_field( "password_reset_recipient", "option" );

		$pw_changed_admin = new Password_Reset_Admin();

		switch ( $password_changed_notification ) {
			case "both":
				$pw_changed_admin->password_reset_email_message( $user );
				break;
			default:
		}

		return $message;
	}

}
