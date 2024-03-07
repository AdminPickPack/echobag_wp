<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @category Common
 * @package  Pick_Pack
 * @author   Pick Pack <admin@pick-pack.ca>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://pick-pack.ca/
 * @since    1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}

class Pick_Pack_i18n
{

    /**
     * Load the plugin text domain for translation.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(
            'pick-pack',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

}
