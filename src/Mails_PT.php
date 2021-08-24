<?php


namespace JUVO_MailEditor;


use WP_Block_Editor_Context;

class Mails_PT {

	public const POST_TYPE_NAME = "juvo-mail";

	public function registerPostType() {
		$labels = [
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
		];

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
			'menu_icon'       => 'dashicons-email'
		);

		register_post_type( self::POST_TYPE_NAME, $args );
	}

	/**
	 * @param bool|array $allowed_block_types
	 * @param WP_Block_Editor_Context $block_editor_context
	 *
	 * @return string[]
	 */
	public function limitBlocks( $allowed_block_types, WP_Block_Editor_Context $block_editor_context ): array {

		return [
			'core/image',
			'core/paragraph',
			'core/heading',
			'core/list',
			'core/table'
		];

	}

	public function addMetaboxes() {

		$cmb = new_cmb2_box( array(
			'id'           => self::POST_TYPE_NAME . '_metabox',
			'title'        => __( 'Mail Settings', 'juvo-mail-editor' ),
			'object_types' => array( self::POST_TYPE_NAME, ), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );

		$cmb->add_field( array(
			'name'   => __( 'Recipients', 'juvo-mail-editor' ),
			'desc'   => __( 'Comma seperated list of mail addresses<br><code>{{CONTEXT}}</code><code>{{ADMIN_EMAIL}}</code>', 'juvo-mail-editor' ),
			'id'     => self::POST_TYPE_NAME . '_recipients',
			'type'   => 'text',
			'column' => true,
		) );

		$cmb->add_field( array(
			'name'   => __( 'Subject', 'juvo-mail-editor' ),
			'desc'   => __( 'E-Mail subject', 'juvo-mail-editor' ),
			'id'     => self::POST_TYPE_NAME . '_subject',
			'type'   => 'text',
			'column' => true,
		) );

		apply_filters( "juvo_mail_editor_post_metabox", $cmb );
	}

}
