<?php


namespace JUVO_MailEditor;


use WP_Term;

/**
 * Transportobject for mail trigger terms
 *
 * Class Trigger
 * @package JUVO_MailEditor
 */
class Trigger {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var bool
	 */
	private $alwaysSent = false;

	/**
	 * @var string
	 */
	private $recipients = "";

	/**
	 * @var string
	 */
	private $content = "";

	/**
	 * @var string
	 */
	private $subject = "";

	/**
	 * @var array
	 */
	private $placeholders = [];

	/**
	 * Trigger constructor.
	 *
	 * @param string $name
	 * @param string $slug
	 */
	public function __construct( string $name, string $slug ) {
		$this->name = $name;
		$this->slug = $slug;
	}

	/**
	 * @return string
	 */
	public function getRecipients(): string {
		return $this->recipients;
	}

	/**
	 * @param string $recipients
	 *
	 * @return Trigger
	 */
	public function setRecipients( string $recipients ): Trigger {
		$this->recipients = $recipients;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isAlwaysSent(): bool {
		return $this->alwaysSent;
	}

	/**
	 * @param bool $alwaysSent
	 *
	 * @return Trigger
	 */
	public function setAlwaysSent( bool $alwaysSent ): Trigger {
		$this->alwaysSent = $alwaysSent;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getPlaceholders(): array {
		return $this->placeholders;
	}

	/**
	 * @param array $placeholders
	 *
	 * @return Trigger
	 */
	public function setPlaceholders( array $placeholders ): Trigger {
		$this->placeholders = $placeholders;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSlug(): string {
		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function getSubject(): string {
		return $this->subject;
	}

	/**
	 * @param string $subject
	 *
	 * @return Trigger
	 */
	public function setSubject( string $subject ): Trigger {
		$this->subject = $subject;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * @param string $content
	 *
	 * @return Trigger
	 */
	public function setContent( string $content ): Trigger {
		$this->content = $content;

		return $this;
	}


}
