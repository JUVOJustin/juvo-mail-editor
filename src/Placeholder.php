<?php


namespace JUVO_MailEditor;


use WP_User;

class Placeholder {

	private static ?Placeholder $instance = null;
	private WP_User $user;

	private $globalPlaceholders = [
		"first_name"             => "",
		"last_name"              => "",
		"username"               => "",
		"userid"                 => "",
		"fullname_else_username" => "",
		"user_email"             => "",
		"site_name"              => "",
		"site_description"       => "",
		"admin_email"            => "",
		"wpurl"                  => ""
	];

	private function __construct( WP_User $user ) {
		$this->user = $user;
		$this->setGlobalPlaceholders();
	}

	private function setGlobalPlaceholders() {

		$this->globalPlaceholders["first_name"]             = $this->user->first_name;
		$this->globalPlaceholders["last_name"]              = $this->user->last_name;
		$this->globalPlaceholders["username"]               = $this->user->nickname;
		$this->globalPlaceholders["userid"]                 = $this->user->ID;
		$this->globalPlaceholders["fullname_else_username"] = empty( $this->user->first_name ) && empty( $user->last_name ) ? $this->user->nickname : $this->user->first_name . " " . $this->user->last_name;
		$this->globalPlaceholders["user_email"]              = $this->user->user_email;
		$this->globalPlaceholders["site_name"]              = get_bloginfo( "name" );
		$this->globalPlaceholders["site_description"]       = get_bloginfo( "description" );
		$this->globalPlaceholders["admin_email"]            = get_bloginfo( "admin_email" );
		$this->globalPlaceholders["wpurl"]                  = get_bloginfo( "wpurl" );
	}

	public static function replacePlaceholder( WP_User $user, array $placeholder, string $text ) {

		$globalPlaceHolder = self::getInstance( $user )->getGlobalPlaceholders();
		$placeholder       = array_merge( $placeholder, $globalPlaceHolder );

		foreach ( $placeholder as $key => $value ) {
			$text = str_replace( '{{' . strtoupper( $key ) . '}}', $value, $text );
		}

		return $text;
	}

	private function getGlobalPlaceholders() {
		return $this->globalPlaceholders;
	}

	private static function getInstance( WP_User $user ) {
		if ( self::$instance == null ) {
			self::$instance = new static( $user );
		}

		return self::$instance;
	}

}
