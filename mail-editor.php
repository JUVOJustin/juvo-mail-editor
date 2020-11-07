<?php
/**
 * Plugin Name:     Mail Editor
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          JUVO Webdesign - Justin Vogt
 * Author URI:      https://juvo-design.de
 * Text Domain:     juvo-mail-editor
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 */

// If this file is called directly, abort.

use MediaFetcher\Activator;
use MediaFetcher\Deactivator;

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
function run_media_fetcher() {

	$plugin = new Mail_Editor();
	$plugin->run();

}

run_media_fetcher();
