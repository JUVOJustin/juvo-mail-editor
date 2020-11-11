<?php


namespace JUVO_MailEditor;


use WP_User;

abstract class Mail_Generator {

	/**
	 * Checks if a message contains html and sets the mail content type.
	 * If html is detected paragraphs will be added for linebreaks
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	protected function setContentType( string $message ): string {

		$type = 'text/plain';

		if ( $message != strip_tags( $message ) ) {
			$type    = "text/html";
			$message = wpautop( $message );
		}

		add_filter( 'wp_mail_content_type', function( $content_type ) use ( $type ) {
			return $type;
		} );

		return $message;
	}

	abstract protected function getMessageCustomField(): string;

	abstract protected function getSubjectCustomField(): string;

	abstract protected function setPlaceholderValues( WP_User $user, array $options ): void;

}
