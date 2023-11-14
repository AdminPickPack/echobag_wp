<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              pick-pack.ca
 * @since             1.0.0
 * @package           Pick_Pack
 *
 * @wordpress-plugin
 * Plugin Name:       Pick Pack
 * Description:       The Plugin for integrating Pick Pack Bags to your Website.
 * Version:           1.0.0
 * Author:            Pick Pack
 * Author URI:        pick-pack.ca
 * License:           
 * License URI:       
 * Text Domain:       pick-pack
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PICK_PACK_VERSION', '1.0.0' );
define( 'SERVER_URL', 'https://www.ecobagapplication.pick-pack.ca/wp-plugin-server/');
//http://localhost/plugin_server/
if (!defined('PICK_PACK_ROOT'))
    define('PICK_PACK_ROOT', plugin_basename(__FILE__));

if (!defined('PICK_PACK_PATH'))	
    define('PICK_PACK_PATH', plugin_dir_path( dirname( __FILE__ ) ));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pick-pack-activator.php
 */
function activate_pick_pack() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pick-pack-activator.php';
	Pick_Pack_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pick-pack-deactivator.php
 */
function deactivate_pick_pack() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pick-pack-deactivator.php';
	Pick_Pack_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pick_pack' );
register_deactivation_hook( __FILE__, 'deactivate_pick_pack' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pick-pack.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pick_pack() {

	$plugin = new Pick_Pack();
	$plugin->run();

}
run_pick_pack();
