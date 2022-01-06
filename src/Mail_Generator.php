<?php


namespace JUVO_MailEditor;

use CMB2;
use CMB2_Field;
use JUVO_MailEditor\Mails\Mail;

abstract class Mail_Generator implements Mail {

	public function __construct() {
		add_filter( 'juvo_mail_editor_post_metabox', array( $this, 'addCustomFields' ) );
		add_filter( 'juvo_mail_editor_trigger', array( $this, 'registerTrigger' ) );

		add_filter( "juvo_mail_editor_{$this->getTrigger()}_always_sent", array( $this, 'getAlwaysSent' ), 1, 0 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_subject", array( $this, 'getSubject' ), 1, 0 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_message", array( $this, 'getMessage' ), 1, 0 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_default_recipients", array( $this, 'getRecipient' ), 1, 0 );
		add_filter(
			"juvo_mail_editor_{$this->getTrigger()}_default_placeholder",
			array(
				$this,
				'getDefaultPlaceholder',
			),
			1,
			0
		);
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_language", array( $this, 'getLanguage' ), 1, 2 );
	}

	abstract protected function getTrigger(): string;

	abstract public function send( ...$params );

	/**
	 * @param Trigger[] $triggers
	 *
	 * @return Trigger[]
	 */
	public function registerTrigger( array $triggers ): array {
		$triggers[] = new Trigger( $this->getName(), $this->getTrigger() );

		return $triggers;
	}

	abstract protected function getName(): string;

	/**
	 * Add Custom Fields to metabox
	 *
	 * @param CMB2 $cmb
	 *
	 * @return CMB2
	 */
	public function addCustomFields( CMB2 $cmb ): CMB2 {
		return $cmb;
	} // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed

	public function postHasTrigger( CMB2_Field $field ): bool {
		return has_term( $this->getTrigger(), Mail_Trigger_TAX::TAXONOMY_NAME, $field->object_id() );
	}

	/**
	 * @param string $language
	 * @param array $context
	 *
	 * @return string
	 *
	 * @phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	 */
	public function getLanguage( string $language, array $context ): string {
		return $language;
	}

	/**
	 * Fill the default placeholders with their real values.
	 * Params are most likely equal to the context.
	 * This function should be called inside the send() method.
	 *
	 * @return array
	 */
	abstract protected function getPlaceholderValues(): array;

	/**
	 * Utility function to auto add show_on_cb callback for trigger
	 *
	 * @param array $field
	 * @param CMB2 $cmb
	 *
	 * @return CMB2
	 */
	protected function addFieldForTrigger( array $field, CMB2 $cmb ): CMB2 {

		if ( ! isset( $field['show_on_cb'] ) ) {
			$field['show_on_cb'] = array( $this, 'postHasTrigger' );
		}

		$cmb->add_field( $field );

		return $cmb;
	}

	protected function emptyMailArray( array $email, $val = null ): array {
		foreach ( $email as $key => $item ) {
			$email[ $key ] = $val;
		}

		return $email;
	}
}
