<?php


namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use WP_User;

class Password_Changed extends Mail_Generator {

	public function addCustomFields( CMB2 $cmb ): CMB2 {

		if ( $cmb->object_id() && has_term( $this->getTrigger(), Mail_Trigger_TAX::TAXONOMY_NAME, $cmb->object_id() ) ) {

            $recipients = $cmb->get_field( Mails_PT::POST_TYPE_NAME . '_recipients' );
            if (!empty( $recipients->value )) {
                update_post_meta( $cmb->object_id(), Mails_PT::POST_TYPE_NAME . '_recipients', [] );
            }
			$cmb->remove_field( Mails_PT::POST_TYPE_NAME . '_recipients' );

            $cc = $cmb->get_field( Mails_PT::POST_TYPE_NAME . '_cc' );
            if (!empty( $cc->value )) {
                update_post_meta( $cmb->object_id(), Mails_PT::POST_TYPE_NAME . '_cc', [] );
            }
            $cmb->remove_field( Mails_PT::POST_TYPE_NAME . '_cc' );

            $bcc = $cmb->get_field( Mails_PT::POST_TYPE_NAME . '_bcc' );
            if (!empty( $bcc->value )) {
                update_post_meta( $cmb->object_id(), Mails_PT::POST_TYPE_NAME . '_bcc', [] );
            }
            $cmb->remove_field( Mails_PT::POST_TYPE_NAME . '_bcc' );

		}

		return $cmb;
	}

	protected function getTrigger(): string {
		return 'password_changed';
	}

	public function getSubject( string $subject ): string {

		if ( ! empty( $subject ) ) {
			return $subject;
		}

		return sprintf( __( '%s Password Changed', 'default' ), '{{site.name}}' );
	}

	public function getMessage( string $message ): string {

		if ( ! empty( $message ) ) {
			return $message;
		}

		$message = __(
			'Hi ###USERNAME###,

This notice confirms that your password was changed on ###SITENAME###.

If you did not change your password, please contact the Site Administrator at
###ADMIN_EMAIL###

This email has been sent to ###EMAIL###

Regards,
All at ###SITENAME###
###SITEURL###',
			'default'
		);

		$message = str_replace(
			array(
				'###USERNAME###',
				'###SITENAME###',
				'###ADMIN_EMAIL###',
				'###SITEURL###',
				'###EMAIL###',
			),
			array( '{{user.name}}', '{{site.name}}', '{{site.admin_email}}', '{{site.url}}', '{{user.user_email}}' ),
			$message
		);

		return $message;
	}

	public function getRecipients( array $recipients ): array {

		if ( ! empty( $recipients ) ) {
			return $recipients;
		}

		return [ '{{user.user_email}}' ];
	}

	public function prepareSend( array $email, array $user ): array {
		do_action( "juvo_mail_editor_send", $this->getTrigger(), [ "user" => get_user_by( "ID", $user['ID'] ) ] );

		return $this->emptyMailArray( $email );
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

	protected function getName(): string {
		return 'Password Changed (User)';
	}
}
