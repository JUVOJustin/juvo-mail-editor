<?php


namespace JUVO_MailEditor;


use JUVO_MailEditor\Admin\Admin;
use JUVO_MailEditor\Generic_Mail\Editor;
use JUVO_MailEditor\Mails\New_User;
use JUVO_MailEditor\Mails\New_User_Admin;
use JUVO_MailEditor\Mails\Password_Changed;
use JUVO_MailEditor\Mails\Password_Reset;

class Mail_Editor {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected Loader $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected string $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected string $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'JUVO_MAIL_EDITOR_VERSION' ) ) {
			$this->version = JUVO_MAIL_EDITOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'juvo-mail-editor';

		$this->loader = new Loader();

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		/**
		 * ACF
		 */
		$acf = new ACF();
		$this->loader->add_filter( "acf/settings/save_json", $acf, "acf_json_save_point" );
		$this->loader->add_filter( "acf/settings/load_json", $acf, "acf_json_load_point", 9 );
		$this->loader->add_filter( "acf/fields/wysiwyg/toolbars", $acf, "acf_toolbars" );
		$this->loader->add_action( 'acf/init', $acf, "add_juvo_mail_editor_menu" );


		/**
		 * Mail Options
		 */
		$mail_options = new Mail_Options();
		$this->loader->add_action( "rest_insert_user", $mail_options, "rest_user_create", 12, 1 );
		remove_action( "register_new_user", "wp_send_new_user_notifications" );
		remove_action( "edit_user_created_user", "wp_send_new_user_notifications", 10 );
		$this->loader->add_action( "register_new_user", $mail_options, "new_user_notifications", 10, 2 );
		$this->loader->add_action( "edit_user_created_user", $mail_options, "new_user_notifications", 10, 2 );
		$this->loader->add_filter( "send_password_change_email", $mail_options, "password_changed_email", 10, 3 );
		$this->loader->add_filter( "retrieve_password_message", $mail_options, "password_reset_email", 11, 4 );


		/**
		 * Default Mail Overrides
		 */
		$this->loader->add_action( 'wp_new_user_notification_email', new New_User(), 'new_user_notification_email', 10, 2 );
		$this->loader->add_action( 'wp_new_user_notification_email_admin', new New_User_Admin(), 'new_user_notification_email_admin', 10, 2 );
		$this->loader->add_filter( 'retrieve_password_message', new Password_Reset(), 'password_reset_email_message', 10, 4 );
		$this->loader->add_filter( 'retrieve_password_title', new Password_Reset(), 'password_reset_email_subject', 10, 4 );
		$this->loader->add_action( 'password_change_email', new Password_Changed(), 'password_changed_email_message', 10, 3 );


		/**
		 * Generic Mail Action
		 */
		$editor = new Editor();
		$this->loader->add_action( 'acf/init', $editor, 'register_templates', 10, 0 );

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Loader Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

}
