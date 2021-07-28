<?php


namespace JUVO_MailEditor\Mails;


use CMB2;
use JUVO_MailEditor\Mail_Generator;

class Generic extends Mail_Generator {

	private $subject = "";
	private $content = "";
	private $recipients;

	/**
	 * Password_Reset constructor.
	 *
	 * @param string $subject
	 * @param string $content
	 * @param array $placeholder
	 */
	public function __construct( string $subject, string $content, string $recipients ) {
		$this->subject    = $subject;
		$this->content    = $content;
		$this->recipients = $recipients;
	}

	/**
	 * @param string|string[] $recipients
	 */
	public function send() {

		$this->content = $this->setContentType( $this->content );

		wp_mail( $this->recipients, $this->subject, $this->content );
	}

	private function setContentType( string $message ): string {

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

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		// TODO: Implement addCustomFields() method.
	}

	public function registerTrigger( array $trigger ): array {
		// TODO: Implement registerTrigger() method.
	}
}
