<?php


namespace JUVO_MailEditor;


use CMB2;
use CMB2_Field;
use JUVO_MailEditor\Mails\Mail;

abstract class Mail_Generator implements Mail {

	public function __construct() {
		add_filter( "juvo_mail_editor_post_metabox", [ $this, "addCustomFields" ] );

		add_filter( "juvo_mail_editor_{$this->getTrigger()}_always_sent", [ $this, "getAlwaysSent" ], 1, 0 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_subject", [ $this, "getSubject" ], 1, 0 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_message", [ $this, "getMessage" ], 1, 0 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_default_recipients", [ $this, "getRecipient" ], 1, 0 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_default_placeholder", [
			$this,
			"getDefaultPlaceholder"
		], 1, 0 );
		add_filter( "juvo_mail_editor_{$this->getTrigger()}_language", [ $this, "getLanguage" ], 1, 2 );
	}

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

	protected abstract function getName(): string;

	/**
	 * Add Custom Fields to metabox
	 *
	 * @param CMB2 $cmb
	 *
	 * @return CMB2
	 */
	abstract public function addCustomFields( CMB2 $cmb ): CMB2;

	public function postHasTrigger( CMB2_Field $field ): bool {
		return has_term( $this->getTrigger(), Mail_Trigger_TAX::TAXONOMY_NAME, $field->object_id() );
	}

	public abstract function getLanguage( string $language, array $context ): string;

	/**
	 * Fill the default placeholders with their real values.
	 * Params are most likely equal to the context.
	 * This function should be called inside the send() method.
	 *
	 * @return array
	 */
	protected abstract function getPlaceholderValues(): array;

	/**
	 * Utility function to auto add show_on_cb callback for trigger
	 *
	 * @param array $field
	 * @param CMB2 $cmb
	 *
	 * @return CMB2
	 */
	protected function addFieldForTrigger( array $field, CMB2 $cmb ): CMB2 {

		if ( ! isset( $field["show_on_cb"] ) ) {
			$field["show_on_cb"] = [ $this, "postHasTrigger" ];
		}

		$cmb->add_field( $field );

		return $cmb;
	}
}
