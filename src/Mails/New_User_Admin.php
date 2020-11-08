<?php

namespace JUVO_MailEditor\Mails;

use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Placeholder;
use WP_User;

class New_User_Admin extends Mail_Generator {

	private $placeholder = [];
	private $text = "";

	function new_user_notification_email_admin( array $email, WP_User $user ) {

		$this->text = $this->getCustomField();

		if ( empty( $this->text ) ) {
			return $email;
		}

		$this->setContentType( true );

		$this->setPlaceholderValues( $user, [] );
		$this->text = Placeholder::replacePlaceholder( $user, $this->placeholder, $this->text );

		$email['message'] = $this->text;

		return $email;
	}

	protected function getCustomField(): string {
		return get_field( "new_user_message_admin", "option" );
	}

	protected function setPlaceholderValues( WP_User $user, array $options ): void {
	}

}
