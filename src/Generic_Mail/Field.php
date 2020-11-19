<?php


namespace JUVO_MailEditor\Generic_Mail;


class Field extends Fieldset {

	private string $name;
	private string $type;
	private string $label;
	private array $args;

	/**
	 * Field constructor.
	 *
	 * @param string $name
	 * @param string $type
	 * @param string $label
	 * @param array $args
	 */
	protected function __construct( string $name, string $type, string $label = "", array $args = [] ) {

		$this->name = $name;
		$this->type  = $type;
		$this->label = $label;
		$this->args  = $args;
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
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return $this->label;
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	public function getArgs(string $key = "") {

		if ($key === "") {
			return $this->args;
		}

		return isset($this->args[$key]) ? $this->args[$key] : null;

	}


}
