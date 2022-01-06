<?php

namespace JUVO_MailEditor;

class Options_Page {

	public function registerOptionsPage() {

		/**
		 * Registers options page menu item and form.
		 */
		$cmb = new_cmb2_box(
			array(
				'id'           => 'juvo-mail-editor-settings',
				'title'        => __( 'Settings', 'juvo-mail-editor' ),
				'object_types' => array( 'options-page' ),
				'option_key'   => 'settings',
				'parent_slug'  => 'edit.php?post_type=' . Mails_PT::POST_TYPE_NAME,
				'capability'   => 'edit_pages', // Cap required to view options-page.
				'display_cb'   => array( $this, 'mailEditorOptionsPageRender' ),
			)
		);

		$cmb->add_field(
			array(
				'name'     => __( 'Mute Trigger', 'juvo-mail-editor' ),
				'desc'     => __( 'Select the triggers you want to completely mute. Using this function disables sending mails even if the ', 'juvo-mail-editor' ),
				'id'       => 'trigger_mute',
				'taxonomy' => Mail_Trigger_TAX::TAXONOMY_NAME,
				'type'     => 'taxonomy_multicheck',
				'classes'  => 'trigger-mute',
				'text'     => array(
					'no_terms_text' => __( 'No triggers found', 'juvo-mail-editor' ),
				),
			)
		);

	}

	public function mailEditorOptionsPageRender( $hookup ) {
		// Output custom markup for the options-page.
		?>
		<div class="wrap juvo-mail-editor cmb2-options-page option-<?php echo esc_attr( $hookup->option_key ); ?>">
			<?php if ( $hookup->cmb->prop( 'title' ) ) : ?>
				<h2><?php echo wp_kses_post( $hookup->cmb->prop( 'title' ) ); ?></h2>
			<?php endif; ?>
			<?php if ( $hookup->cmb->prop( 'description' ) ) : ?>
				<h2><?php echo wp_kses_post( $hookup->cmb->prop( 'description' ) ); ?></h2>
			<?php endif; ?>
			<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST"
				  id="<?php echo esc_attr( $hookup->cmb->cmb_id ); ?>" enctype="multipart/form-data"
				  encoding="multipart/form-data">
				<input type="hidden" name="action" value="<?php echo esc_attr( $hookup->option_key ); ?>">
				<?php $hookup->options_page_metabox(); ?>
				<?php submit_button( esc_attr( $hookup->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
			</form>
			<div class="sync-triggers-wrapper">
				<div class="button-wrapper">
					<button id="sync-triggers"
							class="button"><?php esc_html_e( 'Sync triggers', 'juvo-mail-editor' ); ?></button>
					<small><?php esc_html_e( 'Adds and updates all triggers that are added with the <code>juvo_mail_editor_trigger</code> filter.', 'juvo-mail-editor' ); ?></small>
				</div>
				<div class="message"></div>
			</div>

		</div>
		<?php
	}

	public function ajax_sync_triggers() {
		$success = ( new Mail_Trigger_TAX() )->registerTrigger();
		if ( is_wp_error( $success ) && $success->has_errors() ) {
			wp_send_json_error( $success );
		}

		wp_send_json_success(
			array(
				'title'   => __( 'Success', 'juvo-mail-editor' ),
				'message' => __( 'All triggers were synced successfully', 'juvo-mail-editor' ),
			)
		);
	}

}
