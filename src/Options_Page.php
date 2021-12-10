<?php

namespace JUVO_MailEditor;

use CMB2_Boxes;
use CMB2_Options_Hookup;

class Options_Page {

	public function yourprefix_register_options_submenu_for_page_post_type() {

		/**
		 * Registers options page menu item and form.
		 */
		$main = new_cmb2_box( array(
			'id'           => 'juvo-mail-editor-settings',
			'title'        => "Mail Editor",
			'object_types' => array( 'options-page' ),
			'option_key'   => 'mail-editor-settings',
			'tab_group'    => 'mail-editor-settings',
			'tab_title'    => __( 'Settings', 'juvo-mail-editor' ),
			'parent_slug'  => 'edit.php?post_type=' . Mails_PT::POST_TYPE_NAME,
			//'capability'      => 'post', // Cap required to view options-page.
			'display_cb'   => [ $this, 'juvoMailEditorOptionsPageOutput' ],
		) );

		$main->add_field( array(
			'name'     => __( 'Mute Trigger', 'juvo-mail-editor' ),
			'desc'     => __( 'Select the triggers you want to completely mute. Using this function disables sending mails even if the ', 'juvo-mail-editor' ),
			'id'       => 'trigger_mute',
			'taxonomy' => Mail_Trigger_TAX::TAXONOMY_NAME,
			'type'     => 'taxonomy_multicheck',
			'classes'  => 'trigger-mute',
			'text'     => array(
				'no_terms_text' => __( 'No triggers found', 'juvo-mail-editor' )
			),
		) );

		$templateOptions = new_cmb2_box( array(
			'id'           => 'juvo-mail-editor-template',
			'title'        => "Mail Editor",
			'object_types' => array( 'options-page' ),
			'option_key'   => 'mail-editor-template',
			'tab_group'    => 'mail-editor-settings',
			'tab_title'    => __( 'Template', 'juvo-mail-editor' ),
			'parent_slug'  => 'edit.php?post_type=' . Mails_PT::POST_TYPE_NAME,
			'display_cb'   => [ $this, 'juvoMailEditorOptionsPageOutput' ],
		) );

		$templateOptions->add_field( array(
			'name'    => __( 'Global Template', 'juvo-mail-editor' ),
			'desc'    => __( 'Global e-mail template. Has to include <code>{{message}}</code> to render the content correctly. Has effect over all mails sent with wordpress', 'juvo-mail-editor' ),
			'default' => '',
			'id'      => 'global_template',
			'type'    => 'textarea_code'
		) );

	}

	/**
	 * Gets navigation tabs array for CMB2 options pages which share the given
	 * display_cb param.
	 *
	 * @param CMB2_Options_Hookup $cmb_options The CMB2_Options_Hookup object.
	 *
	 * @return array Array of tab information.
	 */
	function juvoMailEditorOptionsPageTabs( $cmb_options ) {
		$tab_group = $cmb_options->cmb->prop( 'tab_group' );
		$tabs      = array();

		foreach ( CMB2_Boxes::get_all() as $cmb_id => $cmb ) {
			if ( $tab_group === $cmb->prop( 'tab_group' ) ) {
				$tabs[ $cmb->options_page_keys()[0] ] = $cmb->prop( 'tab_title' )
					? $cmb->prop( 'tab_title' )
					: $cmb->prop( 'title' );
			}
		}

		return $tabs;
	}

	function juvoMailEditorOptionsPageOutput( $cmb_options ) {

		$tabs = $this->juvoMailEditorOptionsPageTabs( $cmb_options );

		?>
		<div class="wrap juvo-mail-editor cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
			<?php if ( get_admin_page_title() ) : ?>
				<h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
			<?php endif; ?>
			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $option_key => $tab_title ) : ?>
					<a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>"
					   href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
				<?php endforeach; ?>
			</h2>
			<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST"
				  id="<?php echo $cmb_options->cmb->cmb_id; ?>" enctype="multipart/form-data"
				  encoding="multipart/form-data">
				<input type="hidden" name="action" value="<?php echo esc_attr( $cmb_options->option_key ); ?>">
				<?php $cmb_options->options_page_metabox(); ?>
				<?php submit_button( esc_attr( $cmb_options->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
			</form>
			<?php if ( $cmb_options->option_key === "mail-editor-settings" ): ?>
				<div class="sync-triggers-wrapper">
					<div class="button-wrapper">
						<button id="sync-triggers"
								class="button"><?php _e( 'Sync triggers', 'juvo-mail-editor' ) ?></button>
						<small><?php _e( 'Adds and updates all triggers that are added with the <code>juvo_mail_editor_trigger</code> filter.', 'juvo-mail-editor' ) ?></small>
					</div>
					<div class="message"></div>
				</div>
			<?php endif; ?>
			<div id="previewContainer">

			</div>
		</div>
		<?php
	}

	public function ajax_sync_triggers() {
		$success = ( new Mail_Trigger_TAX() )->registerTrigger();
		if ( is_wp_error( $success ) && $success->has_errors() ) {
			wp_send_json_error( $success );
		}

		wp_send_json_success( [
			"title"   => __( 'Success', 'juvo-mail-editor' ),
			"message" => __( 'All triggers were synced successfully', 'juvo-mail-editor' )
		] );
	}

}
