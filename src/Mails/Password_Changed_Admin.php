<?php


namespace JUVO_MailEditor\Mails;


use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use JUVO_MailEditor\Relay;
use JUVO_MailEditor\Trigger;
use WP_User;

class Password_Changed_Admin extends Mail_Generator {

	private $placeholders = [
	];

	/**
	 *
	 * @param array $email
	 * @param WP_User $user
	 *
	 * @return array
	 */
	public function password_changed_admin_email( array $email, WP_User $user ): array {

		$this->setPlaceholderValues( $user );

		$relay = new Relay( $this->getTrigger(), $this->placeholders, [ "user" => $user ] );
		$relay->sendMails();

		return [];
	}

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

	/**
	 * @param array $triggers
	 *
	 * @return Trigger[]
	 */
	public function registerTrigger( array $triggers ): array {

		$message = sprintf( __( 'Password changed for user: %s' ), '{{user.name}}' ) . "\r\n";

		$trigger = new Trigger( __( "Password Changed (Admin)", 'juvo-mail-editor' ), $this->getTrigger() );
		$trigger
			->setAlwaysSent( true )
			->setSubject( sprintf( __( "%s Password Changed" ), "{{site.name}}" ) )
			->setContent( $message )
			->setRecipients( "{{site.admin_email}}" )
			->setPlaceholders( $this->placeholders );

		$triggers[] = $trigger;

		return $triggers;

	}
}
