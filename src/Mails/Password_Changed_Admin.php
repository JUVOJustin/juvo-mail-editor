<?php


namespace JUVO_MailEditor\Mails;


use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use JUVO_MailEditor\Relay;
use WP_User;

class Password_Changed_Admin extends Mail_Generator {

	protected function setPlaceholderValues( WP_User $user, array $options = [] ): void {
	}

	public function getTrigger(): string {
		return "password_changed_admin";
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
		return sprintf( __( 'Password changed for user: %s' ), '{{user.name}}' ) . "\r\n";
	}

	function getRecipient(): string {
		return "{{site.admin_email}}";
	}

	public function send( ...$params ) {
		list( $email, $user ) = $params;

		$placeholders = $this->getPlaceholderValues();

		$relay = new Relay( $this->getTrigger(), $placeholders, [ "user" => $user ] );
		$relay->sendMails();

		return $this->emptyMailArray( $email );
	}

	protected function getName(): string {
		return "Password Changed (Admin)";
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
		return $language;
	}
}
