<?php


namespace JUVO_MailEditor\Mails;

class Generic {

	private $subject = '';
	private $content = '';
	private $recipients;
	private $headers;
	private $attachments;

	/**
	 * Password_Reset constructor.
	 *
	 * @param string $subject
	 * @param string $content
	 * @param array $recipients
	 * @param array $headers
	 * @param array $attachments
	 */
	public function __construct( string $subject, string $content, array $recipients, array $headers, array $attachments ) {
		$this->subject     = $subject;
		$this->content     = $content;
		$this->recipients  = $recipients;
		$this->headers     = $headers;
		$this->attachments = $attachments;
	}

	/**
	 * Wrapper for wp_mail
	 */
	public function send() {
		return wp_mail( $this->recipients, $this->subject, $this->content, $this->headers, $this->attachments );
	}
}
