<?php


namespace JUVO_MailEditor\Mails;


use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class Generic extends Mail_Generator {

	private string $name = "";
	private array $placeholder = [];
	private string $text = "";
	private string $subject = "";
	private ?WP_User $user;

	/**
	 * Password_Reset constructor.
	 *
	 * @param string $name
	 * @param WP_User|null $user
	 * @param array $placeholder
	 */
	public function __construct( string $name, WP_User $user = null, array $placeholder = [] ) {
		$this->name    = $name;
		$this->user    = $user;
		$this->placeholder = $placeholder;
		$this->text    = $this->getMessageCustomField();
		$this->subject = $this->getSubjectCustomField();

	}

	protected function getMessageCustomField(): string {
		return get_field( $this->name . "_message", "option" ) ?: "";
	}

	protected function getSubjectCustomField(): string {
		return get_field( $this->name . "_subject", "option" );
	}

	public function send( string $recipient ) {

		if ( empty( $this->text ) || empty( $this->subject ) || empty( $recipient ) ) {
			return;
		}

		$this->setPlaceholderValues( $this->user, []);

		$this->subject = Placeholder::replacePlaceholder( $this->user, $this->placeholder, $this->subject );
		$this->text    = Placeholder::replacePlaceholder( $this->user, $this->placeholder, $this->text );
		$this->text    = $this->setContentType( $this->text );

		wp_mail( $recipient, $this->subject, $this->text );
	}

	protected function setPlaceholderValues( WP_User $user, array $options ): void {
		$placeholder = get_field( $this->name . "_placeholder", "option" );
		$placeholder = get_object_vars(json_decode( $placeholder ));

		// Merge late passed placeholders values with registered placeholders
		$placeholder = array_intersect_key($this->placeholder, $placeholder) + $placeholder;

		foreach($placeholder as $key => $v) {

			// Check if is callback
			if (is_callable( $v)) {
				$placeholder[$key] = call_user_func($v);
			} else {
				// If not same the value in case it is not an array
				$placeholder[$key] = is_array($v) ? "" : $v;
			}
		}

		$this->placeholder = $placeholder;
	}

}
