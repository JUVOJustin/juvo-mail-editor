<?php


namespace JUVO_MailEditor;


class Mail_Trigger_TAX {

	public const TAXONOMY_NAME = "juvo-mail-trigger";

	public function registerTaxonomy() {

		$labels = array(
			'name'              => _x( 'Subjects', 'taxonomy general name' ),
			'singular_name'     => _x( 'Subject', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Subjects' ),
			'all_items'         => __( 'All Subjects' ),
			'parent_item'       => __( 'Parent Subject' ),
			'parent_item_colon' => __( 'Parent Subject:' ),
			'edit_item'         => __( 'Edit Subject' ),
			'update_item'       => __( 'Update Subject' ),
			'add_new_item'      => __( 'Add New Subject' ),
			'new_item_name'     => __( 'New Subject Name' ),
			'menu_name'         => __( 'Subjects' ),
		);

		register_taxonomy( self::TAXONOMY_NAME, Mails_PT::POST_TYPE_NAME, array(
			'public'            => false,
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true
		) );

	}

	public function addMetaboxes() {

		$cmb = new_cmb2_box( array(
			'id'           => self::TAXONOMY_NAME . '_metabox',
			'title'        => __( 'Mail Settings', 'juvo-mail-editor' ),
			'object_types' => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'   => array( self::TAXONOMY_NAME ),
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );

		$cmb->add_field( array(
			'name' => __( 'Default Recipients', 'juvo-mail-editor' ),
			'desc' => __( 'Default recipients of the mail', 'juvo-mail-editor' ),
			'id'   => self::TAXONOMY_NAME . '_default_recipients',
			'type' => 'text'
		) );

		$cmb->add_field( array(
			'name' => __( 'Default Subject', 'juvo-mail-editor' ),
			'desc' => __( 'Default subject of the mail', 'juvo-mail-editor' ),
			'id'   => self::TAXONOMY_NAME . '_default_subject',
			'type' => 'text'
		) );

		$cmb->add_field( array(
			'name' => __( 'Default Content', 'juvo-mail-editor' ),
			'desc' => __( 'Default content of the mail', 'juvo-mail-editor' ),
			'id'   => self::TAXONOMY_NAME . '_default_content',
			'type' => 'textarea'
		) );

		$cmb->add_field( array(
			'name' => __( 'Additional Placeholders', 'juvo-mail-editor' ),
			'id'   => self::TAXONOMY_NAME . '_placeholders',
			'type' => 'textarea'
		) );
	}

	public function registerTrigger() {

		$triggers = [];

		$triggers = apply_filters( "juvo_mail_editor_trigger", $triggers );

		foreach ( $triggers as $trigger ) {

			if ( ! $trigger instanceof Trigger ) {
				error_log( "[juvo_mail_editor]: Provided trigger is no instance of JUVO_MailEditor\Trigger and therefore skipped" );
				continue;
			}

			$term = get_term_by( 'slug', $trigger->getSlug(), self::TAXONOMY_NAME );

			$term_id = null;
			if ( ! $term ) {
				$term_id = wp_insert_term( $trigger->getName(), self::TAXONOMY_NAME, [ "slug" => $trigger->getSlug() ] )["term_id"];
			} else {
				$term_id = $term->term_id;
			}

			$term_id = wp_update_term( $term_id, self::TAXONOMY_NAME, array(
				'name' => $trigger->getName(),
				'slug' => $trigger->getSlug()
			) )["term_id"];

			update_term_meta( $term_id, self::TAXONOMY_NAME . '_default_recipients', $trigger->getRecipients() );
			update_term_meta( $term_id, self::TAXONOMY_NAME . "_default_subject", $trigger->getSubject() );
			update_term_meta( $term_id, self::TAXONOMY_NAME . "_default_content", $trigger->getContent() );
			update_term_meta( $term_id, self::TAXONOMY_NAME . "_placeholders", $trigger->getPlaceholders() );

		}

	}

}
