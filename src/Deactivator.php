<?php

namespace JUVO_MailEditor;

class Deactivator {

	public static function deactivate() {

		$timestamp = wp_next_scheduled( 'bl_cron_hook' );
		wp_unschedule_event( $timestamp, 'bl_cron_hook' );

	}

}
