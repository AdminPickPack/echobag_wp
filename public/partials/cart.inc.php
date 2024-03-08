<?php
/**
 * The woocommerce cart code for pick-pack.
 *
 * @category Public
 * @package  Pick_Pack
 * @author   Pick Pack <admin@pick-pack.ca>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://pick-pack.ca/
 * @since    1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}

// Popup
require_once plugin_dir_path(__FILE__) . 'popup.inc.php';
?>

<?php if (!$this->pick_pack_product_in_cart() && !empty($pickpack_quantity)) : ?>
<button id="open-pickpack-button" class="button open-pickpack-button"><?php esc_html_e('Add Eco Bag', 'pick-pack') ?></button>
<?php endif; ?>
