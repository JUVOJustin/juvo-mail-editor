<?php

namespace JUVO_MailEditor\Integrations;

class BuddyBoss {

	public function useTemplate( array $args ): array {
		if ( function_exists( 'bp_is_active' ) ) {
			// BoddyBoss might have already applied the template
			if ( isset( $args['message'] ) && strpos( $args['message'], '<!DOCTYPE html>' ) === false ) {
				/** @phpstan-ignore-next-line */
				$args['message'] = bp_email_core_wp_get_template( $args['message'] );
			}
		}

		return $args;
	}

}
