<?php


namespace JUVO_MailEditor;


use WP_User;

class Placeholder {

	private static $instance = null;
	private $context;

	private $globalPlaceholders = [];

	private function __construct( $context ) {
		$this->context = $context;
		$this->setGlobalPlaceholders();
	}

	private function setGlobalPlaceholders() {

		if ( $this->context instanceof WP_User ) {
			$this->globalPlaceholders["FIRST_NAME"]             = $this->context->first_name;
			$this->globalPlaceholders["LAST_NAME"]              = $this->context->last_name;
			$this->globalPlaceholders["USERNAME"]               = $this->context->nickname;
			$this->globalPlaceholders["USERID"]                 = $this->context->ID;
			$this->globalPlaceholders["FULLNAME_ELSE_USERNAME"] = empty( $this->context->first_name ) && empty( $user->last_name ) ? $this->context->nickname : $this->context->first_name . " " . $this->context->last_name;
			$this->globalPlaceholders["USER_EMAIL"]             = $this->context->user_email;
		}

		$this->globalPlaceholders["SITE_NAME"]        = get_bloginfo( "name" );
		$this->globalPlaceholders["SITE_DESCRIPTION"] = get_bloginfo( "description" );
		$this->globalPlaceholders["ADMIN_EMAIL"]      = get_bloginfo( "admin_email" );
		$this->globalPlaceholders["WPURL"]            = get_bloginfo( "wpurl" );
	}

	public static function replacePlaceholder( array $placeholder, string $text, $context = null ) {

		$globalPlaceHolder = self::getInstance( $context )->getGlobalPlaceholders();
		$placeholder       = $placeholder + $globalPlaceHolder;

		foreach ( $placeholder as $key => $value ) {

			// Check if is callback
			if ( is_callable( $value ) ) {
				$placeholder[ $key ] = call_user_func( $value );
			} else {
				// If not same the value in case it is not an array
				$placeholder[ $key ] = is_array( $value ) ? "" : $value;
			}

			$text = str_replace( '{{' . strtoupper( $key ) . '}}', $value, $text );
		}

		return $text;
	}

	public static function getDemoPlaceholder( string $context = null ) {

		if ($context == "user") {
			$context = wp_get_current_user();
		}

		$globalPlaceHolder = self::getInstance( $context )->getGlobalPlaceholders();

		return $globalPlaceHolder;
	}

	public function getGlobalPlaceholders(): array {
		return $this->globalPlaceholders;
	}

	private static function getInstance( $context = null ) {
		if ( self::$instance == null ) {
			self::$instance = new static( $context );
		}

		return self::$instance;
	}

}
