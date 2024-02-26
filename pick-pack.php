<?php
/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @package Pick_Pack
 * @author  Pick Pack <admin@pick-pack.ca>
 * @since   1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Pick Pack
 * Description:       Integrate Pick Pack into your WooCommerce shop and give to your customers the option to order an ecobag on elligible products.
 * Version:           1.0.0
 * Stable tag:        1.0.0
 * Tested up:         6.4
 * Author:            Pick Pack
 * Author URI:        http://pick-pack.ca/
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pick-pack
 * Domain Path:       /languages/
 */

// If this file is called directly, abort.
if (! defined('WPINC') ) {
    die;
}

/**
 * Current plugin version. Use SemVer - https://semver.org.
 */
define('PICK_PACK_VERSION', '1.0.0');

/**
 * Pick pack API server URL.
 */
define('PICK_PACK_SERVER', 'https://www.ecobagapplication.pick-pack.ca/wp-plugin-server/');

/**
 * Pick pack plugin base name (root script).
 */
if (!defined('PICK_PACK_BASENAME')) {
    define('PICK_PACK_BASENAME', plugin_basename(__FILE__));
}

/**
 * Pick pack plugin folder name
 */
if (!defined('PICK_PACK_FOLDER')) {
    define('PICK_PACK_FOLDER', dirname(PICK_PACK_BASENAME));
}

/**
 * Pick pack plugin path.
 */
if (!defined('PICK_PACK_PATH')) {
    define('PICK_PACK_PATH', realpath(plugin_dir_path(dirname(__FILE__)) . '/' . PICK_PACK_FOLDER) . '/');
}

/**
 * Pick pack plugin base URL.
 */
if (!defined('PICK_PACK_BASEURL')) {
    define('PICK_PACK_BASEURL', plugins_url(PICK_PACK_FOLDER) . '/');
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pick-pack-activator.php
 *
 * @since 1.0.0
 * @return void
 */
function pick_pack_activate()
{
    include_once plugin_dir_path(__FILE__) . 'includes/class-pick-pack-activator.php';
    Pick_Pack_Activator::activate();
}
register_activation_hook(__FILE__, 'pick_pack_activate');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pick-pack-deactivator.php
 *
 * @since 1.0.0
 * @return void
 */
function pick_pack_deactivate()
{
    include_once plugin_dir_path(__FILE__) . 'includes/class-pick-pack-deactivator.php';
    Pick_Pack_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'pick_pack_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-pick-pack.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 * @return void
 */
function pick_pack_run()
{
    $plugin = new Pick_Pack();
    $plugin->run();
}
pick_pack_run();
