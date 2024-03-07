<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
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

class Pick_Pack_Deactivator
{

    /**
     * Plugin deactivation tasks.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public static function deactivate()
    {
        wp_clear_scheduled_hook('monthly_charge_cronjob_action');
    }

}
