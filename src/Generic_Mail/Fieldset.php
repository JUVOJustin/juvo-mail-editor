<?php


namespace JUVO_MailEditor\Generic_Mail;


class Fieldset {

	private array $fields = [];
	private string $name;

	/**
	 * Fieldset constructor.
	 *
	 * @param string $name
	 */
	public function __construct( string $name ) {
		$this->name = $name;
	}

	/**
	 * @return array
	 */
	public function getFields(): array {
		return $this->fields;
	}


	/**
	 * @param string $type
	 * @param string $label
	 * @param array $args
	 */
	public function add_field( string $type, string $label = "", array $args = [] ): void {

		$field = new Field( $this->name . "_" . strtolower( $type ), strtolower( $type ), $label, $args );

		// If first field unconditionally add
		if ( empty( $this->fields ) || end( $this->fields )->getType() != "accordion" ) {
			$this->fields[] = $field;

			return;
		}

		$insertKey = array_key_last( $this->fields );

		// Iterate fields to find first that that is not an accordion
		while ( $insertKey >= 0 ) {
			if ( $this->fields[ $insertKey ]->getType() != "accordion" ) {
				array_splice( $this->fields, $insertKey+1, 0, [ $field ] );

				return;
			}
			$insertKey --;
		}

		// If this point is reached the fields array will only contain accordion fields
		array_splice( $this->fields, array_key_last( $this->fields ), 0, [ $field ] );
	}


	/**
	 * @param string $label
	 */
	public function add_accordion( string $label ) {

		// Add open accordion
		array_unshift( $this->fields, new Field( $this->name . "_accordion", "accordion", $label, [ "endpoint" => 0 ] ) );

		// Add end accordion
		$this->fields[] = new Field( $this->name . "_accordion", "accordion", $label, [ "endpoint" => 1 ] );
	}

	/**
	 * This function is used to store custom placeholders alongside the fieldset.
	 *
	 * @param array $placeholder
	 */
	public function register_placeholder(array $placeholder) {

		$this->add_field( "placeholder", "Placeholder", [
			"readonly" => 1,
		]);

		// Save Placeholders
		update_field($this->name . "_placeholder", json_encode( $placeholder ), "options");
	}


}
