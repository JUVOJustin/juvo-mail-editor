<?php

namespace JUVO_MailEditor\Integrations;

class BuddyBoss {

	public function useTemplate( string $content, string $trigger, $context ) {
		if ( function_exists( 'bp_is_active' ) ) {
			return bp_email_core_wp_get_template( $content, $context );
		}

		return $content;
	}

}
