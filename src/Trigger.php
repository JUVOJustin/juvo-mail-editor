<?php


namespace JUVO_MailEditor;

/**
 * Transportobject for mail trigger terms
 *
 * Class Trigger
 *
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
	public function getSlug(): string {
		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

}
