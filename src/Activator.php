<?php

namespace JUVO_MailEditor;

class Activator {

	public static function activate() {

		if ( ! wp_next_scheduled( 'juvo_mail_editor_trigger_init' ) ) {
			wp_schedule_event( time(), 'hourly', 'juvo_mail_editor_trigger_init' );
		} else {
			$timestamp = wp_next_scheduled( 'juvo_mail_editor_trigger_init' );
			wp_unschedule_event( $timestamp, 'juvo_mail_editor_trigger_init' );
			wp_schedule_event( time(), 'hourly', 'juvo_mail_editor_trigger_init' );
		}

	}

}
