<?php
/**
 * Plugin Name:     JUVO Mail Editor
 * Description:     JUVO Mail Editor helps to modify the standard WordPress Mailings and allows adding dynamic mail triggers
 * Author:          JUVO Webdesign - Justin Vogt
 * Author URI:      https://juvo-design.de
 * License:         GPL v2 or later
 * Text Domain:     juvo-mail-editor
 * Domain Path:     /languages
 * Version:         3.0.14
 */

use JUVO_MailEditor\Activator;
use JUVO_MailEditor\Deactivator;
use JUVO_MailEditor\Mail_Editor;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin absolute path
 */
define( 'JUVO_MAIL_EDITOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'JUVO_MAIL_EDITOR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Use Composer PSR-4 Autoloading
 * Add file check to avoid autoloading if included as sub-package
 */
$juvo_mail_editor_plugin_dir = plugin_dir_path( __FILE__ );
if ( file_exists( $juvo_mail_editor_plugin_dir . 'vendor/autoload.php' ) ) {
	require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

/**
 * Load cmb2 manually and not by composer because file autoloading does not work
 */
if ( file_exists( $juvo_mail_editor_plugin_dir . 'vendor/cmb2/cmb2/init.php' ) ) {
	// Path for standalone plugin. Load from local vendor folder
	require plugin_dir_path( __FILE__ ) . 'vendor/cmb2/cmb2/init.php';
} else {
	// Lookup vendor folder when included as library
	preg_match( '/(.*)vendor/U', $juvo_mail_editor_plugin_dir, $matches );
	if ( file_exists( $matches[1] . 'vendor/cmb2/cmb2/init.php' ) ) {
		require $matches[1] . 'vendor/cmb2/cmb2/init.php';
	}
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function juvo_mail_editor_activate() {
	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function juvo_mail_editor_deactivate() {
	Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'juvo_mail_editor_activate' );
register_deactivation_hook( __FILE__, 'juvo_mail_editor_deactivate' );


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function juvo_mail_editor_run() {

	if ( ! defined( 'ABSPATH' ) ) {
		return;
	}

	// Make sure only loaded once
	if ( class_exists( '\WP' ) && ! defined( 'JUVO_MAIL_EDITOR_LOADED' ) ) {

		$version = "3.0.14";
		$plugin = new Mail_Editor($version);
		$plugin->run();

		define( 'JUVO_MAIL_EDITOR_LOADED', true );
	}

}

juvo_mail_editor_run();
