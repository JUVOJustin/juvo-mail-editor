<?php


namespace JUVO_MailEditor;

use WP_Block_Editor_Context;

class Mails_PT {

	public const POST_TYPE_NAME = 'juvo-mail';

	public function registerPostType() {
		$labels = array(
			'name'                  => _x( 'Mail', 'Post type general name', 'juvo-mail-editor' ),
			'singular_name'         => _x( 'Mail', 'Post type singular name', 'juvo-mail-editor' ),
			'menu_name'             => _x( 'Mails', 'Admin Menu text', 'juvo-mail-editor' ),
			'name_admin_bar'        => _x( 'Mail', 'Add New on Toolbar', 'juvo-mail-editor' ),
			'add_new'               => __( 'Add Mail', 'juvo-mail-editor' ),
			'add_new_item'          => __( 'Add New Mail', 'juvo-mail-editor' ),
			'new_item'              => __( 'New Mail', 'juvo-mail-editor' ),
			'edit_item'             => __( 'Edit Mail', 'juvo-mail-editor' ),
			'view_item'             => __( 'View Mail', 'juvo-mail-editor' ),
			'all_items'             => __( 'All Mails', 'juvo-mail-editor' ),
			'search_items'          => __( 'Search Mails', 'juvo-mail-editor' ),
			'not_found'             => __( 'No Mail found.', 'juvo-mail-editor' ),
			'not_found_in_trash'    => __( 'No mails found in Trash.', 'juvo-mail-editor' ),
			'insert_into_item'      => _x( 'Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'juvo-mail-editor' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'juvo-mail-editor' ),
			'filter_items_list'     => _x( 'Filter mail list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'juvo-mail-editor' ),
			'items_list_navigation' => _x( 'Mails list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'juvo-mail-editor' ),
			'items_list'            => _x( 'Mails list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'juvo-mail-editor' ),
		);

		$args = array(
			'labels'          => $labels,
			'public'          => false,
			'show_ui'         => true,
			'capability_type' => 'post',
			'has_archive'     => false,
			'hierarchical'    => false,
			'menu_position'   => null,
			'supports'        => array( 'title', 'editor', 'author', 'revisions' ),
			'show_in_rest'    => true,
			'menu_icon'       => 'dashicons-email',
		);

		register_post_type( self::POST_TYPE_NAME, $args );
	}

	/**
	 * @param bool|array $allowed_block_types
	 * @param WP_Block_Editor_Context $block_editor_context
	 *
	 * @return string[]
	 */
	public function limitBlocks( $allowed_block_types, WP_Block_Editor_Context $block_editor_context ) {

		if ( isset( $block_editor_context->post ) && self::POST_TYPE_NAME === $block_editor_context->post->post_type ) {
			return array(
				'core/image',
				'core/paragraph',
				'core/heading',
				'core/list',
				'core/table',
				'core/html',
				'core/freeform',
				'core/shortcode',
				'core/separator',
				'core/spacer'
			);
		}

		return $allowed_block_types;
	}

	public function addMetaboxes() {

		$cmb = new_cmb2_box(
			array(
				'id'           => self::POST_TYPE_NAME . '_metabox',
				'title'        => __( 'Mail Settings', 'juvo-mail-editor' ),
				'object_types' => array( self::POST_TYPE_NAME ), // Post type
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true, // Show field names on the left
			)
		);

		$cmb->add_field(
			array(
				'name'   => __( 'Subject', 'juvo-mail-editor' ),
				'desc'   => __( 'E-Mail subject', 'juvo-mail-editor' ),
				'id'     => self::POST_TYPE_NAME . '_subject',
				'type'   => 'text',
				'column' => true,
			)
		);

		// Named "recipients" for backwards compatibility reasons. Don´t rename!
		$to_group = $cmb->add_field( array(
			'id'          => self::POST_TYPE_NAME . '_recipients',
			'type'        => 'group',
			'description' => __( 'Comma seperated list of mail addresses', 'juvo-mail-editor' ),
			'options'     => array(
				'group_title'    => __( 'Recipient {#}', 'cmb2' ),
				'add_button'     => __( 'Add Another Entry', 'cmb2' ),
				'remove_button'  => __( 'Remove Entry', 'cmb2' ),
				'sortable'       => true,
				'closed'         => true,
				'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'cmb2' ),
			),
			'classes'     => "cmb-flex"
		) );

		$this->addRecipientGroupFields( $to_group, $cmb );

		$cc_group = $cmb->add_field( array(
			'id'          => self::POST_TYPE_NAME . '_cc',
			'type'        => 'group',
			'description' => __( 'Add recipients that should receive a carbon copy (CC). CC Recipients are visible in emails', 'juvo-mail-editor' ),
			'options'     => array(
				'group_title'    => __( 'CC Recipient {#}', 'cmb2' ),
				'add_button'     => __( 'Add Another Entry', 'cmb2' ),
				'remove_button'  => __( 'Remove Entry', 'cmb2' ),
				'sortable'       => true,
				'closed'         => true,
				'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'cmb2' ),
			),
			'classes'     => "cmb-flex"
		) );

		$this->addRecipientGroupFields( $cc_group, $cmb );

		$bcc_group = $cmb->add_field( array(
			'id'          => self::POST_TYPE_NAME . '_bcc',
			'type'        => 'group',
			'description' => __( 'Add recipients that should receive a “blind carbon copy.” Behaves like CC but without revealing the recipients in the email.', 'juvo-mail-editor' ),
			'options'     => array(
				'group_title'    => __( 'BCC Recipient {#}', 'cmb2' ),
				'add_button'     => __( 'Add Another Entry', 'cmb2' ),
				'remove_button'  => __( 'Remove Entry', 'cmb2' ),
				'sortable'       => true,
				'closed'         => true,
				'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'cmb2' ),
			),
			'classes'     => "cmb-flex"
		) );

		$this->addRecipientGroupFields( $bcc_group, $cmb );

		$cmb->add_field( array(
			'name' => __( 'Attachments', 'juvo-mail-editor' ),
			'desc' => '',
			'id'   => self::POST_TYPE_NAME . '_attachments',
			'type' => 'file_list',
		) );

		apply_filters( 'juvo_mail_editor_post_metabox', $cmb );
	}

	private function addRecipientGroupFields( string $group, $cmb ) {
		$cmb->add_group_field( $group, array(
			'name'    => __( 'Name', 'juvo-mail-editor' ),
			'id'      => 'name',
			'type'    => 'text',
			'classes' => "flex-col-2"
		) );

		$cmb->add_group_field( $group, array(
			'name'       => __( 'Mail', 'juvo-mail-editor' ),
			'id'         => 'mail',
			'type'       => 'text',
			'classes'    => "flex-col-2",
			'attributes' => array(
				'data-validation' => 'required',
			),
		) );
	}

}
