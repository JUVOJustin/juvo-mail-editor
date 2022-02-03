<?php


namespace JUVO_MailEditor;

use CMB2;
use CMB2_Field;
use JUVO_MailEditor\Mails\Mail;

abstract class Mail_Generator implements Mail {

	public function __construct() {
		add_filter( 'juvo_mail_editor_post_metabox', array( $this, 'addCustomFields' ) );
		add_filter( 'juvo_mail_editor_trigger', array( $this, 'registerTrigger' ) );

		add_filter( "juvo_mail_editor_{$this->getTrigger()}_always_sent", array( $this, 'getAlwaysSent' ), 10, 0 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_subject", array( $this, 'getSubject' ), 10, 1 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_message", array( $this, 'getMessage' ), 10, 1 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_recipients", array( $this, 'getRecipients' ), 10, 1 );
		add_filter(
			"juvo_mail_editor_{$this->getTrigger()}_placeholders",
			array(
				$this,
				'getPlaceholders',
			),
			1,
			2
		);
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_language", array( $this, 'getLanguage' ), 1, 2 );
	}

	/**
	 * Returns the trigger slug which should be unique and is used for all consecutive filters and actions
	 *
	 * @return string trigger slug
	 */
	abstract protected function getTrigger(): string;

	/**
	 * Returns the custom placeholders available for this trigger.
	 * They may not necessarily have a value.
	 *
	 * The function should always return all custom placeholder no matter if they have a value or not.
	 * This allows filters or other functions to fill or show the placeholder in the most dynamic way.
	 *
	 * @param array|null $context
	 *
	 * @return array Array key equals the accessor in twig
	 */
	public function getPlaceholders( array $placeholders, ?array $context ): array {
		return $placeholders;
	}

	/**
	 * @param Trigger[] $triggers
	 *
	 * @return Trigger[]
	 */
	public function registerTrigger( array $triggers ): array {
		$triggers[] = new Trigger( $this->getName(), $this->getTrigger() );

		return $triggers;
	}

	/**
	 * Returns the triggers nicename in a human-readable format
	 *
	 * @return string trigger nicename
	 */
	abstract protected function getName(): string; // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed

	/**
	 * Add Custom Fields to metabox
	 *
	 * @param CMB2 $cmb
	 *
	 * @return CMB2
	 */
	public function addCustomFields( CMB2 $cmb ): CMB2 {
		return $cmb;
	}

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

	/**
	 * Utility function that completely empties the often used mail array.
	 * This is most useful if hooking into native core function
	 *
	 * @param array $email
	 * @param null $val
	 *
	 * @return array
	 */
	protected function emptyMailArray( array $email, $val = null ): array {
		foreach ( $email as $key => $item ) {
			$email[ $key ] = $val;
		}

		return $email;
	}
}
