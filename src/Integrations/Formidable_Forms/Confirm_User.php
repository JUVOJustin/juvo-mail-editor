<?php

namespace JUVO_MailEditor\Integrations\Formidable_Forms;


use JUVO_MailEditor\Mail_Generator;
use WP_User;

class Confirm_User extends Mail_Generator
{

	/**
	 * @param string $message
	 * @param string $activation_url
	 * @param int $user_id
	 * @return string Return empty string to avoid wp_mail from sending the mail
	 *
	 * @link https://formidableforms.com/knowledgebase/user-registration/#kb-customize-confirmation-email
	 */
	public function prepareSend(string $message, string $activation_url, int $user_id) {

		$user = get_user_by('ID', $user_id);
		do_action( "juvo_mail_editor_send", $this->getTrigger(), [ "user" => $user, "activation_url" => $activation_url ] );
		return "";
	}


	public function getSubject(string $subject): string {
		if ( ! empty( $subject ) ) {
			return $subject;
		}

		return sprintf( __( '[%s] Activate Your Account', 'frmreg' ), '{{site.name}}' );
	}

	public function getMessage(string $message): string {
		if ( ! empty( $message ) ) {
			return $message;
		}

		$message = sprintf( __( 'Thanks for registering at %s! To complete the activation of your account please click the following link: ', 'frmreg' ), '{{user.name}}' ) . "\r\n\r\n";
		$message .= "{{activation_url}}\r\n";

		return $message;
	}

	public function getRecipients(array $recipients): array {
		if ( ! empty( $recipients ) ) {
			return $recipients;
		}

		return [ '{{user.user_email}}' ];
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	protected function getTrigger(): string {
		return "formidable_confirm_user";
	}

	protected function getName(): string {
		return 'Confirm User';
	}

	public function getPlaceholders( array $placeholders, ?array $context ): array {

		$placeholders = array_merge( $placeholders, array(
			'activation_url' => '',
		) );

		if ( empty( $context['activation_url'] ) ) {
			return $placeholders;
		}

		$placeholders['activation_url'] = $context['activation_url'];

		return $placeholders;

	}

	public function getLanguage( string $language, array $context ): string {

		if ( isset( $context['user'] ) && $context['user'] instanceof WP_User ) {
			return get_user_locale( $context['user']->ID );
		}

		return $language;
	}
}
