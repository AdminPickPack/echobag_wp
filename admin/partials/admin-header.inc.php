<?php
/**
 * Admin header for Pick Pack.
 *
 * @category Admin
 * @package  Pick_Pack
 * @author   Pick Pack <admin@pick-pack.ca>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://pick-pack.ca/
 * @since    1.0.0
 */
if (!defined('ABSPATH')) { 
    exit;
}
?>

<div id="pick-pack-admin" class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Pick Pack Packaging', 'pick-pack'); ?></h1>
    <nav class="navbar">
        <ul>
            <li class="brand">
                <img src="<?php echo PICK_PACK_BASEURL . 'assets/images/pick-pack-logo.png' ?>" alt="<?php esc_attr_e('Pick Pack', 'pick-pack') ?>">
            </li>
            <li>
                <a href="<?php esc_attr_e(admin_url('/admin.php?page=pickpack')) ?>"><?php esc_html_e('Dashboard', 'pick-pack'); ?></a>
            </li>
            <li>
                <a href="<?php esc_attr_e(admin_url('/admin.php?page=pickpack_orders')) ?>"><?php esc_html_e('Orders', 'pick-pack'); ?></a>
            </li>
            <li>
                <a href="<?php esc_attr_e(admin_url('/admin.php?page=pickpack_settings')) ?>"><?php esc_html_e('Settings', 'pick-pack'); ?></a>
            </li>
        </ul>
    </nav>
    <div class="container">
