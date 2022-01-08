<?php


namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use WP_User;

class Password_Changed extends Mail_Generator {

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		if ( has_term( $this->getTrigger(), Mail_Trigger_TAX::TAXONOMY_NAME, $cmb->object_id() ) ) {
			$cmb->remove_field( Mails_PT::POST_TYPE_NAME . '_recipients' );
		}

		return $cmb;
	}

	protected function getTrigger(): string {
		return 'password_changed';
	}

	public function getSubject(): string {
		return sprintf( __( '%s Password Changed', 'default' ), '{{site.name}}' );
	}

	public function getMessage(): string {
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

	public function getRecipient(): string {
		return '{{user.user_email}}';
	}

	public function prepareSend( array $email, WP_User $user ): array {
		$this->send( [ "user" => $user ] );

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
