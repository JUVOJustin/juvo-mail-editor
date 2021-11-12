<?php


namespace JUVO_MailEditor\Mails;


use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use JUVO_MailEditor\Relay;
use JUVO_MailEditor\Trigger;
use WP_User;

class Password_Changed extends Mail_Generator {

	private $placeholders = [
	];

	/**
	 *
	 * @param array $email
	 * @param array $user
	 *
	 * @return array
	 */
	public function password_changed_email( array $email, array $user ): array {

		$user = get_user_by( 'id', $user['ID'] );

		$this->setPlaceholderValues( $user );

		$relay = new Relay( $this->getTrigger(), $this->placeholders, [ "user" => $user ] );
		$relay->sendMails();

		return [];
	}

	public function getTrigger(): string {
		return "password_changed";
	}

	protected function setPlaceholderValues( WP_User $user, array $options = [] ): void {
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

		$trigger = new Trigger( __( "Password Changed (User)", 'juvo-mail-editor' ), $this->getTrigger() );
		$trigger
			->setAlwaysSent( true )
			->setSubject( sprintf( __( "%s Password Changed" ), "{{site.name}}" ) )
			->setContent( $message )
			->setRecipients( "{{user.user_email}}" )
			->setPlaceholders( $this->placeholders );

		$triggers[] = $trigger;

		return $triggers;

	}
}
