<?php
/**
 * The pick-pack popup.
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

// Current language
$current_lang = '';
if (defined('ICL_LANGUAGE_CODE')) {
    $current_lang = ICL_LANGUAGE_CODE;
} elseif (function_exists('pll_current_language')) {
    $current_lang = pll_current_language();
}

// Popup in French
$popup_header  = get_option('pick_pack_popup_header_fr', PICK_PACK_DEFAULT_POPUP_HEADER_FR);
$popup_content = get_option('pick_pack_popup_content_fr', PICK_PACK_DEFAULT_POPUP_CONTENT_FR);
$popup_howto   = get_option('pick_pack_popup_howto_fr', PICK_PACK_DEFAULT_POPUP_HOWTO_FR);
$help_url      = defined('PICK_PACK_HELP_URL_FR') ? PICK_PACK_HELP_URL_FR : null;

// Popup in English
if ($current_lang == 'en') {
    $popup_header_en  = get_option('pick_pack_popup_header_en', PICK_PACK_DEFAULT_POPUP_HEADER_EN);
    $popup_content_en = get_option('pick_pack_popup_content_en', PICK_PACK_DEFAULT_POPUP_CONTENT_EN);
    $popup_howto_en   = get_option('pick_pack_popup_howto_en', PICK_PACK_DEFAULT_POPUP_HOWTO_EN);
    $help_url         = defined('PICK_PACK_HELP_URL_EN') ? PICK_PACK_HELP_URL_EN : null;
}
?>
<div id="pickpack-popup" class="pickpack-container" style="display: none">
    <div class="pickpack-overlay"></div>
    <div class="pickpack-wrap">
        <div class="pickpack-container">
            <div class="pickpack-img"><img src="<?php echo PICK_PACK_BASEURL . 'assets/images/eco-bag-product.png' ?>" alt="<?php esc_attr_e('Pick Pack Eco Bag', 'pick-pack') ?>"></div>
            <div class="pickpack-header">
                <span class="pickpack-close" data-target="pick_pack_popup"><?php esc_html_e("Close", 'pick-pack') ?></span>
            </div>
            <div class="pickpack-body">
                <div class="pickpack-header-text"><?php _e($popup_header) ?></div>
                <div class="pickpack-content-text"><?php _e($popup_content) ?></div>
                <div class="pickpack-warn-text"><?php esc_html_e('* Some products may not yet be eligible for PickPack delivery.', 'pick-pack') ?></div>
                <div class="pickpack-checkbox-wrap">
                    <input type="checkbox" id="pickpack-checkbox" name="pickpack-checkbox" />
                    <label for="pickpack-checkbox"><?php esc_html_e("My order will be delivered in Canada", 'pick-pack') ?></label>
                </div>
                <div class="pickpack-buttons">
                    <button class="pickpack-button pickpack-add" data-target="pick_pack_popup"><?php esc_html_e("Yes, I want to make a difference", 'pick-pack') ?></button>
                    <button class="pickpack-button pickpack-cancel"><?php esc_html_e("No thank you", 'pick-pack') ?></button>
                </div>
                <div class="pickpack-howto">
                   <div class="pickpack-howto-toggle"><?php esc_html_e('How it works', 'pick-pack') ?></div>
                   <div class="pickpack-howto-content">
                       <?php _e($popup_howto) ?>
                       <?php if (!empty($help_url)) : ?>
                       <div class="pickpack-howto-help"><a href="<?php esc_attr_e($help_url) ?>" target="_blank"><?php esc_html_e('See the return procedure (collaboration with Canada Post)', 'pick-pack') ?></a></div>
                       <?php endif; ?>
                    </div>
                </div>
                <div class="pickpack-footer">
                    <div class="pickpack-footer-text"><?php esc_html_e('* You must choose the option AND return your packaging as agreed.', 'pick-pack') ?></div>
                   <div class="pickpack-footer-logo">
                        <a href="https://pick-pack.ca/" target="_blank">
                           <img src="<?php echo PICK_PACK_BASEURL . 'assets/images/footer-logo.png' ?>" alt="<?php esc_attr_e('Pick Pack', 'pick-pack') ?>">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
jQuery(document).ready(function($) {
    $("#pickpack-popup").appendTo('body');
    $("#pickpack-popup").<?php echo (!$this->pick_pack_product_in_cart($product_id) && !empty($pickpack_quantity)) ? 'show' : 'hide' ?>();
});
</script>
