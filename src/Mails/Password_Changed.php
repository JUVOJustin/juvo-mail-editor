<?php


namespace JUVO_MailEditor\Mails;


use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use JUVO_MailEditor\Relay;
use WP_User;

class Password_Changed extends Mail_Generator {

	public function getTrigger(): string {
		return "password_changed";
	}

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		if ( has_term( $this->getTrigger(), Mail_Trigger_TAX::TAXONOMY_NAME, $cmb->object_id() ) ) {
			$cmb->remove_field( Mails_PT::POST_TYPE_NAME . '_recipients' );
		}

		return $cmb;
	}

	function getSubject(): string {
		return sprintf( __( "%s Password Changed" ), "{{site.name}}" );
	}

	function getMessage(): string {
		$message = __(
			'Hi ###USERNAME###,

This notice confirms that your password was changed on ###SITENAME###.

If you did not change your password, please contact the Site Administrator at
###ADMIN_EMAIL###

This email has been sent to ###EMAIL###

Regards,
All at ###SITENAME###
###SITEURL###'
		);

		$message = str_replace( [
			'###USERNAME###',
			'###SITENAME###',
			'###ADMIN_EMAIL###',
			'###SITEURL###',
			'###EMAIL###'
		],
			[ '{{user.name}}', '{{site.name}}', '{{site.admin_email}}', '{{site.url}}', '{{user.user_email}}' ],
			$message
		);

		return $message;
	}

	function getRecipient(): string {
		return "{{user.user_email}}";
	}

	public function send( ...$params ) {
		list( $email, $user ) = $params;

		$placeholders = $this->getPlaceholderValues();

		$relay = new Relay( $this->getTrigger(), $placeholders, [ "user" => $user ] );
		$relay->sendMails();

		return [];
	}

	protected function getName(): string {
		return "Password Changed (User)";
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultPlaceholder(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	protected function getPlaceholderValues(): array {
		return [];
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	public function getLanguage( string $language, array $context ): string {

		if ( isset( $context["user"] ) && $context["user"] instanceof WP_User ) {
			return get_user_locale( $context["user"]->ID );
		}

		return $language;
	}
}
