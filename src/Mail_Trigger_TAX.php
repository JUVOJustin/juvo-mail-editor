<?php


namespace JUVO_MailEditor;

use WP_Error;

class Mail_Trigger_TAX {

	public const TAXONOMY_NAME = 'juvo-mail-trigger';

	public function registerTaxonomy() {

		$labels = array(
			'name'              => _x( 'Triggers', 'taxonomy general name', 'juvo-mail-editor' ),
			'singular_name'     => _x( 'Trigger', 'taxonomy singular name', 'juvo-mail-editor' ),
			'search_items'      => __( 'Search Triggers', 'juvo-mail-editor' ),
			'all_items'         => __( 'All Triggers', 'juvo-mail-editor' ),
			'parent_item'       => __( 'Parent Trigger', 'juvo-mail-editor' ),
			'parent_item_colon' => __( 'Parent Trigger:', 'juvo-mail-editor' ),
			'edit_item'         => __( 'Edit Trigger', 'juvo-mail-editor' ),
			'update_item'       => __( 'Update Trigger', 'juvo-mail-editor' ),
			'add_new_item'      => __( 'Add New Trigger', 'juvo-mail-editor' ),
			'new_item_name'     => __( 'New Trigger Name', 'juvo-mail-editor' ),
			'menu_name'         => __( 'Triggers', 'juvo-mail-editor' ),
		);

		register_taxonomy(
			self::TAXONOMY_NAME,
			Mails_PT::POST_TYPE_NAME,
			array(
				'public'            => false,
				'hierarchical'      => false,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'capabilities'      => array(
					'manage_terms' => false,
					'edit_terms'   => false,
					'delete_terms' => false,
				),
			)
		);

	}

	public function addMetaboxes() {

		$cmb = new_cmb2_box(
			array(
				'id'           => self::TAXONOMY_NAME . '_metabox',
				'title'        => __( 'Mail Settings', 'juvo-mail-editor' ),
				'object_types' => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
				'taxonomies'   => array( self::TAXONOMY_NAME ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true, // Show field names on the left
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Always use trigger', 'juvo-mail-editor' ),
				'desc' => __( 'Use trigger even if no post is associated with it', 'juvo-mail-editor' ),
				'id'   => self::TAXONOMY_NAME . '_always_send',
				'type' => 'checkbox',
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Default Recipients', 'juvo-mail-editor' ),
				'desc' => __( 'Default recipients of the mail', 'juvo-mail-editor' ),
				'id'   => self::TAXONOMY_NAME . '_default_recipients',
				'type' => 'text',
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Additional Placeholders', 'juvo-mail-editor' ),
				'id'   => self::TAXONOMY_NAME . '_placeholders',
				'type' => 'textarea',
			)
		);
	}

	/**
	 * @return void|WP_Error
	 */
	public function registerTrigger() {

		$errors = new WP_Error();

		$triggers = Trigger_Registry::getInstance()->getAll();

		foreach ( $triggers as $trigger ) {

			if ( ! $trigger instanceof Trigger ) {
				$errors->add( 'juvo_mail_editor_invalid_trigger', 'Provided trigger is no instance of JUVO_MailEditor\Trigger and therefore skipped' );
				continue;
			}

			// Create or Update Term
			$term = get_term_by( 'slug', $trigger->getSlug(), self::TAXONOMY_NAME );
			if ( ! $term ) {
				$term = wp_insert_term(
					$trigger->getName(),
					self::TAXONOMY_NAME,
					array( 'slug' => $trigger->getSlug() )
				);
			} else {
				$term = wp_update_term(
					$term->term_id,
					self::TAXONOMY_NAME,
					array(
						'name' => $trigger->getName(),
						'slug' => $trigger->getSlug(),
					)
				);
			}

			if ( is_wp_error( $term ) ) {
				$errors->add( 'juvo_mail_editor_term_error', $term->get_error_message() );
				continue;
			}

		}

		foreach ( $errors->get_error_messages() as $error ) {
			error_log( "[juvo_mail_editor]: {$error}" );
		}

		return $errors;

	}

}
