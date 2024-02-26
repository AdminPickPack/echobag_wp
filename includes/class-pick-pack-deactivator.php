<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @package    Pick_Pack
 * @subpackage Pick_Pack/includes
 * @author     Pick Pack <admin@pick-pack.ca>
 * @since      1.0.0
 */
if (!defined('ABSPATH')) exit;

class Pick_Pack_Deactivator
{

    /**
     * Plugin deactivation tasks.
     *
     * @since  1.0.0
     * @return void
     */
    public static function deactivate()
    {
        wp_clear_scheduled_hook('monthly_charge_cronjob_action');
    }

}
