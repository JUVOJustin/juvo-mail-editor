<?php

namespace JUVO_MailEditor;

use Timber\Timber;

class Template {

	public function maybeAddGlobalTemplate( array $args ): array {

		$args["message"] = $this->render( $args["message"] );

		return $args;
	}

	public function previewTemplateAjax() {

		if ( ! isset( $_POST["template"] ) ) {
			wp_send_json( "No preview content to render", 500 );
		}

		$message = "<h1>Lorem Ipsum</h1><p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam...</p>";
		$preview = $this->render( $message, stripslashes( $_POST["template"] ) );

		wp_send_json( $preview, 200 );
	}

	private function render( string $message, string $preview = null ): string {

		if ( $preview ) {
			return Timber::compile_string( $preview, [ "message" => $message ] );
		}

		$settings = get_option( 'mail-editor-template' );

		$template = apply_filters( "juvo_mail_editor_global_template", $settings['global_template'] ?? "" );

		if ( empty( $template ) ) {
			return $message;
		}

		return Timber::compile_string( $template, [ "message" => $message ] );
	}

}
