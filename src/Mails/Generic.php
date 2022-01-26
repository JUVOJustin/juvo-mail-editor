<?php


namespace JUVO_MailEditor\Mails;

class Generic {

	private $subject = '';
	private $content = '';
	private $recipients;
	private $headers;

	/**
	 * Password_Reset constructor.
	 *
	 * @param string $subject
	 * @param string $content
	 * @param string $recipients
	 */
	public function __construct( string $subject, string $content, string $recipients, array $headers ) {
		$this->subject    = $subject;
		$this->content    = $content;
		$this->recipients = $recipients;
		$this->headers    = $headers;
	}

	/**
	 * Wrapper for wp_mail
	 */
	public function send() {
		return wp_mail( $this->recipients, $this->subject, $this->content, $this->headers );
	}
}
