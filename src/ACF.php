<?php


namespace JUVO_MailEditor;


class ACF {

	public function acf_json_save_point( string $path ): string {
		return JUVO_MAIL_EDITOR_PATH . '/acf-fields';
	}

	public function acf_json_load_point(array $paths): array {

		// remove original path (optional)
		unset($paths[0]);

		// append path
		$paths[] = JUVO_MAIL_EDITOR_PATH . '/acf-fields';

		// return
		return $paths;

	}

	public function add_juvo_mail_editor_menu(): void {

		// Check function exists.
		if( function_exists('acf_add_options_sub_page') ) {

			// Add sub page.
			$child = acf_add_options_sub_page(array(
				'page_title'  => __('Mail Editor'),
				'menu_title'  => __('Mail Editor'),
				'parent_slug' => "options-general.php",
				'capability' => 'edit_posts',
			));
		}
	}

	public function acf_read_only( array $field ): array {
		$field['readonly'] = 1;

		return $field;
	}


	public function acf_toolbars( $toolbars ) {

		// "Mail" Toolbar inherits from "Full" Toolbar
		$toolbars['Mail'] = $toolbars['Full' ];

		if( ($key = array_search('wp_more' , $toolbars['Mail'][1])) !== false ) {
			unset( $toolbars['Mail'][1][$key] );
		}

		return $toolbars;
	}



}
