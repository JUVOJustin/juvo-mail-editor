<?php


namespace JUVO_MailEditor;

use Timber\Timber;
use WP_Post;
use WP_Term;
use WP_User;

class Placeholder {

	public static function replacePlaceholder( array $placeholder, string $text, array $context = array() ) {

		foreach ( $placeholder as $key => $value ) {

			// Check if is callback
			if ( is_callable( $value ) ) {
				$placeholder[ $key ] = call_user_func( $value );
			} else {
				// If not same the value in case it is not an array
				$placeholder[ $key ] = is_array( $value ) ? '' : $value;
			}

			$text = str_replace( '{{' . $key . '}}', $value, $text ); // Without space before brackets
			$text = str_replace( '{{ ' . $key . ' }}', $value, $text );
		}

		// Parse context for timber
		$renderContext = Timber::context();
		apply_filters( 'juvo_mail_editor_timber_context', $renderContext );

		foreach ( $context as $key => $item ) {
			if ( $item instanceof WP_User ) {
				$renderContext[ $key ] = Timber::get_user($item);
			} elseif ( $item instanceof WP_Post ) {
				$renderContext[ $key ] = Timber::get_post( $item->ID );
			} elseif ( $item instanceof WP_Term ) {
				$renderContext[ $key ] = Timber::get_term($item->term_id);
			} else {
				$renderContext[ $key ] = $item;
			}
		}

		// Parse text with timber/twig to add logic and advanced placeholder support
		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $compiled = Timber::compile_string( $text, $renderContext ) ) {
			return $compiled;
		}

		return $text;
	}

	/**
	 * Remove some of the default context variables timber sets
	 *
	 * @param array $context
	 *
	 * @return array
	 */
	public function filterTimberContext( array $context ): array {

		unset( $context['body_class'] );
		unset( $context['request'] );
		unset( $context['wp_head'] );
		unset( $context['wp_footer'] );
		unset( $context['posts'] );

		return $context;

	}
}
