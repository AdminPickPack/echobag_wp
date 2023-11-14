<?php

/**
 * Fired during plugin deactivation
 *
 * @link       pick-pack.ca
 * @since      1.0.0
 *
 * @package    Pick_Pack
 * @subpackage Pick_Pack/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Pick_Pack
 * @subpackage Pick_Pack/includes
 * @author     Pick Pack <admin@pick-pack.ca>
 */
class Pick_Pack_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'monthly_charge_cronjob_action' );
	}

}
