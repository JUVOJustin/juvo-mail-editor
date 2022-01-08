<?php


namespace JUVO_MailEditor;

use JUVO_MailEditor\Admin\Admin;
use JUVO_MailEditor\Integrations\BuddyBoss;
use JUVO_MailEditor\Mails\New_User;
use JUVO_MailEditor\Mails\New_User_Admin;
use JUVO_MailEditor\Mails\New_User_Admin_Rest;
use JUVO_MailEditor\Mails\New_User_Rest;
use JUVO_MailEditor\Mails\Password_Changed;
use JUVO_MailEditor\Mails\Password_Changed_Admin;
use JUVO_MailEditor\Mails\Password_Reset;
use JUVO_MailEditor\Mails\Password_Reset_Admin;

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
		 * Placeholders
		 */
		$this->loader->add_filter( 'juvo_mail_editor_timber_context', new Placeholder(), 'filterTimberContext' );

		/**
		 * New User Notification for enduser
		 */
		$this->loader->add_action( 'wp_new_user_notification_email', new New_User(), 'prepareSend', 10, 2 );
		$this->loader->add_action( 'rest_insert_user', new New_User_Rest(), 'prepareSend', 12, 1 ); // Rest

		/**
		 * New User Notification Admin
		 */
		$this->loader->add_action( 'wp_new_user_notification_email_admin', new New_User_Admin(), 'prepareSend', 10, 2 );
		$this->loader->add_action( 'rest_insert_user', new New_User_Admin_Rest(), 'prepareSend', 12, 1 ); // Rest

		/**
		 * Password Reset
		 */
		$this->loader->add_filter( 'retrieve_password_message', new Password_Reset(), 'prepareSend', 10, 4 );

		/**
		 * Password Reset Admin
		 */
		$this->loader->add_filter( 'retrieve_password_message', new Password_Reset_Admin(), 'prepareSend', 99, 4 );

		/**
		 * Password Changed
		 */
		$this->loader->add_filter( 'password_change_email', new Password_Changed(), 'prepareSend', 10, 2 );

		/**
		 * Password Changed Admin
		 */
		$this->loader->add_filter( 'wp_password_change_notification_email', new Password_Changed_Admin(), 'prepareSend', 10, 2 );

		/**
		 * Integrations
		 */
		$this->loader->add_filter( 'wp_mail', new BuddyBoss(), 'useTemplate', 99, 1 );
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
