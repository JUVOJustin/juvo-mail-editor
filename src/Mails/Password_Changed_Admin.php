<?php


namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use WP_User;

class Password_Changed_Admin extends Mail_Generator {

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		if ( has_term( $this->getTrigger(), Mail_Trigger_TAX::TAXONOMY_NAME, $cmb->object_id() ) ) {
			$cmb->remove_field( Mails_PT::POST_TYPE_NAME . '_recipients' );
		}

		return $cmb;
	}

	protected function getTrigger(): string {
		return 'password_changed_admin';
	}

	public function getSubject(): string {
		return sprintf( __( '%s Password Changed', 'default' ), '{{site.name}}' );
	}

	public function getMessage(): string {
		return sprintf( __( 'Password changed for user: %s', 'default' ), '{{user.name}}' ) . "\r\n";
	}

	public function getRecipient(): string {
		return '{{site.admin_email}}';
	}

	public function prepareSend( array $email, WP_User $user ): array {
		$this->send( [ "user" => $user ] );

		return $this->emptyMailArray( $email );
	}

	public function getAlwaysSent(): bool {
		return true;
	}

	protected function getName(): string {
		return 'Password Changed (Admin)';
	}
}
