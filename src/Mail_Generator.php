<?php


namespace JUVO_MailEditor;


use CMB2;
use CMB2_Field;

abstract class Mail_Generator {

	/**
	 * Add Custom Fields to metabox
	 *
	 * @param CMB2 $cmb
	 *
	 * @return CMB2
	 */
	abstract public function addCustomFields( CMB2 $cmb ): CMB2;

	/**
	 * Register Trigger
	 *
	 * @param array $triggers
	 *
	 * @return Trigger[]
	 */
	abstract public function registerTrigger( array $triggers ): array;

	public function postHasTrigger( CMB2_Field $field ): bool {
		return has_term( $this->getTrigger(), Mail_Trigger_TAX::TAXONOMY_NAME, $field->object_id() );
	}

	//abstract protected function setPlaceholderValues( WP_User $user, array $options ): void;

	abstract public function getTrigger(): string;

	abstract public function demoContext(): string;

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
