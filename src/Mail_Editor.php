<?php


namespace JUVO_MailEditor;

use JUVO_MailEditor\Admin\Admin;
use JUVO_MailEditor\Integrations\WS_Form;
use JUVO_MailEditor\Mails\New_User;
use JUVO_MailEditor\Mails\New_User_Admin;
use JUVO_MailEditor\Mails\New_User_Admin_Rest;
use JUVO_MailEditor\Mails\New_User_Rest;
use JUVO_MailEditor\Mails\Password_Changed;
use JUVO_MailEditor\Mails\Password_Changed_Admin;
use JUVO_MailEditor\Mails\Password_Reset;
use JUVO_MailEditor\Mails\Password_Reset_Admin;
use Timber\Timber;

class Mail_Editor {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

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

		$this->plugin_name = 'juvo-mail-editor';

		$this->loader = new Loader();

        Timber::init();

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

		$plugin_i18n = new I18N();

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

		$plugin_admin = new Admin( $this->get_plugin_name() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Init Trigger Registry
		Trigger_Registry::getInstance();

		/**
		 * Options
		 */
		$options = new Options_Page();
		$this->loader->add_action( 'cmb2_admin_init', $options, 'registerOptionsPage' );
		$this->loader->add_action( 'wp_ajax_juvo-mail-editor-sync-triggers', $options, 'ajax_sync_triggers' );

		/**
		 * Post Type
		 */
		$pt = new Mails_PT();
		$this->loader->add_action( 'init', $pt, 'registerPostType' );
		$this->loader->add_filter( 'allowed_block_types_all', $pt, 'limitBlocks', 10, 2 );
		$this->loader->add_action( 'cmb2_admin_init', $pt, 'addMetaboxes' );

		/**
		 * Taxonomy
		 */
		$tax = new Mail_Trigger_TAX();
		$this->loader->add_action( 'init', $tax, 'registerTaxonomy' );
		$this->loader->add_action( 'juvo_mail_editor_trigger_init', $tax, 'registerTrigger' ); // Called by trigger
		$this->loader->add_action( 'cmb2_admin_init', $tax, 'addMetaboxes' );

		/**
		 * Handle Sending
		 */
		$relay = new Relay();
		$this->loader->add_action( "juvo_mail_editor_send", $relay, 'send_mails_action_callback', 10, 2 );
		$this->loader->add_filter( "wp_mail", $relay, 'wpmail_filter_callback', 10, 1 );

		/**
		 * Placeholders
		 */
		$this->loader->add_filter( 'juvo_mail_editor_timber_context', new Placeholder(), 'filterTimberContext' );

		/**
		 * User Locale
		 */
		$this->loader->add_filter( 'juvo_mail_editor_user_language', new Integrations\Language(), 'getUserLocale', 10, 2 );
		$this->loader->add_filter( 'juvo_mail_editor_user_language', new Integrations\WPML(), 'getUserLocale', 20, 2 );

		/**
		 * New User Notification for enduser
		 */
		$this->loader->add_action( 'wp_new_user_notification_email', new New_User(), 'prepareSend', 10, 2 );
		$this->loader->add_action( 'rest_insert_user', new New_User_Rest(), 'prepareSend', 12, 3 ); // Rest
		$this->loader->add_action( 'wp_new_user_notification_email_admin', new New_User_Admin(), 'prepareSend', 10, 2 );
		$this->loader->add_action( 'rest_insert_user', new New_User_Admin_Rest(), 'prepareSend', 12, 3 ); // Rest

		/**
		 * Password Reset
		 */
		$this->loader->add_filter( 'retrieve_password_notification_email', new Password_Reset(), 'prepareSend', 10, 4 );
		$this->loader->add_filter( 'retrieve_password_notification_email', new Password_Reset_Admin(), 'prepareSend', 10, 4 );

		/**
		 * Password Changed
		 */
		$this->loader->add_filter( 'password_change_email', new Password_Changed(), 'prepareSend', 10, 2 );
		$this->loader->add_filter( 'wp_password_change_notification_email', new Password_Changed_Admin(), 'prepareSend', 10, 2 );

		/**
		 * Integrations
		 */
		$this->loader->add_filter( 'wp_mail', new Integrations\BuddyBoss(), 'useTemplate', 99, 1 );
		$this->loader->add_filter( 'user_activation_notification_message', new Integrations\Formidable_Forms\Confirm_User(), 'prepareSend', 99, 3 );
		$wsf = new WS_Form();
		$this->loader->add_filter( 'wsf_action_email_headers', $wsf, 'header_filter_callback', 10, 4 );

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
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {}

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
