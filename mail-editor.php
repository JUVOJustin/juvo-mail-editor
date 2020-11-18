<?php
/**
 * Plugin Name:     Mail Editor
 * Plugin URI:      https://juvo-design.de
 * Description:     JUVO Mail Editor helps to configure the standard mail notification system of WordPress
 * Author:          JUVO Webdesign - Justin Vogt
 * Author URI:      https://juvo-design.de
 * Text Domain:     juvo-mail-editor
 * Domain Path:     /languages
 * Version:         1.0.2
 */

use juvo\WordPressAdminNotices\Manager;
use JUVO_MailEditor\Activator;
use JUVO_MailEditor\Deactivator;
use JUVO_MailEditor\Mail_Editor;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'JUVO_MAIL_EDITOR_VERSION', '1.0.0' );


/**
 * Plugin absolute path
 */
define( 'JUVO_MAIL_EDITOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'JUVO_MAIL_EDITOR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Use Composer PSR-4 Autoloading
 */
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_juvo_mail_editor() {
	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_juvo_mail_editor() {
	Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_juvo_mail_editor' );
register_deactivation_hook( __FILE__, 'deactivate_juvo_mail_editor' );


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_juvo_mail_editor() {

	if ( ! juvo_mail_editor_checkDependencies() ) {
		return;
	}

	$plugin = new Mail_Editor();
	$plugin->run();

}

run_juvo_mail_editor();


function juvo_mail_editor_checkDependencies(): bool {

	add_action( 'admin_init', function() {
		$notices = new Manager();
		$notices->notices();
	} );

	// Load ACF Pro if not loaded elsewhere
	if ( ! class_exists( 'acf_pro' ) && file_exists( JUVO_MAIL_EDITOR_PATH . 'includes/acf-pro/acf.php' ) ) {
		// Include the ACF plugin.
		include_once( JUVO_MAIL_EDITOR_PATH . 'includes/acf-pro/acf.php' );

		// Customize the url setting to fix incorrect asset URLs.
		add_filter( 'acf/settings/url', function( $url ) {
			return JUVO_MAIL_EDITOR_URL . 'includes/acf-pro/';
		} );
	}

	// Check if ACF is loaded
	if ( ! class_exists( 'acf_pro' ) ) {
		// Add a notice.
		Manager::add( "missing_plugin", __( "Required plugin missing", "juvo-mail-editor" ), __( "The advanced custom fields plugin is required for this plugin to work", "juvo-mail-editor" ), [ "type" => "error" ] );
		return false;
	} else {
		// Hide the ACF admin menu item.
		add_filter( 'acf/settings/show_admin', function( $show_admin ) {
			return true;
		} );
	}

	return true;

}


$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/JUVOJustin/juvo-mail-editor',
	__FILE__,
	'mail-editor'
);

//Optional: If you're using a private repository, specify the access token like this:
$myUpdateChecker->setAuthentication( '4fde03cac9ee6017f7d066e16497324378b42c8a' );
