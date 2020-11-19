<?php


namespace JUVO_MailEditor\Generic_Mail;


class Editor {

	private const GROUPKEY = "group_5cbad3da05b4f";
	private array $fieldsets = [];

	function register_templates() {

		// Add filter to allow custom fields by third parties
		$this->fieldsets = apply_filters( "juvo_mail_templates", $this->fieldsets );

		do_action( "juvo_before_register_mail_template" );

		foreach ( $this->fieldsets as $fieldset ) {

			// Make Sure Array Item is a fieldset
			if ( ! $fieldset instanceof Fieldset ) {
				continue;
			}

			// Iterate fields of a fieldset
			foreach ( $fieldset->getFields() as $field ) {

				// Make Sure Array Item is a fieldset else skip complete fieldset
				if ( ! $field instanceof Field ) {
					continue 2;
				}

				switch ( $field->getType() ) {
					case "accordion":
						$this->register_accordion( $field );
						break;
					case "subject":
						$this->register_subject( $field );
						break;
					case "message":
						$this->register_message( $field );
						break;
					case "placeholder":
						$this->register_placeholder( $field );
						break;
				}

			}
		}

		do_action( "juvo_after_register_mail_template" );
	}

	private function register_accordion( Field $field ) {
		acf_add_local_field( array(
			'name'         => $field->getName() . ( $field->getArgs( "endpoint" ) ? 'true' : 'false' ),
			'label'        => ( $field->getLabel() ?: $field->getName() ) . ' (Generic)',
			'type'         => 'accordion',
			"open"         => $field->getArgs( "open" ),
			"multi_expand" => $field->getArgs( "multi_expand" ),
			"endpoint"     => $field->getArgs( "endpoint" ),
			'parent'       => self::GROUPKEY
		) );
	}

	private function register_subject( Field $field ) {
		acf_add_local_field( array(
			"label"        => ( $field->getLabel() ?: $field->getName() ),
			"name"         => $field->getName(),
			"type"         => "text",
			"instructions" => $field->getArgs( "instructions" ),
			"required"     => 1,
			"placeholder"  => $field->getArgs( "placeholder" ),
			"prepend"      => $field->getArgs( "prepend" ),
			"append"       => $field->getArgs( "append" ),
			"maxlength"    => $field->getArgs( "maxlength" ),
			'parent'       => self::GROUPKEY
		) );

	}

	private function register_message( Field $field ) {
		acf_add_local_field( array(
			"label"        => ( $field->getLabel() ?: $field->getName() ),
			"name"         => $field->getName(),
			"type"         => "wysiwyg",
			"instructions" => $field->getArgs( "instructions" ),
			"required"     => 1,
			"tabs"         => "all",
			"toolbar"      => "mail",
			"media_upload" => 0,
			"delay"        => 0,
			'parent'       => self::GROUPKEY
		) );
	}

	private function register_placeholder( Field $field ) {
		acf_add_local_field( array(
			"label"        => ( $field->getLabel() ?: $field->getName() ),
			"name"         => $field->getName(),
			"type"         => "textarea",
			"instructions" => $field->getArgs( "instructions" ),
			"required"     => 0,
			'readonly'     => $field->getArgs( "readonly" ),
			'disabled'     => $field->getArgs( "disabled" ),
			'new_lines'    => '',
			'parent'       => self::GROUPKEY
		) );
	}

}
