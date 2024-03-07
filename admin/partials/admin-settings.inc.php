<?php
/**
 * Admin settings for Pick Pack.
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

// TinyMCE Editor settings
$default_editor_settings = array( 
    'media_buttons' => false,
    'default_editor' => 'tinymce', 
    'tinymce' => array(
        'elementpath' => false,
        'toolbar1' => 'bold,italic,underline,strikethrough,removeformat,forecolor,|,alignleft,aligncenter,alignright,|,undo,redo',
        'toolbar2' => '',
        'toolbar3' => '',
        'toolbar4' => '',
    ),
    'quicktags' => false,
);

$popup_content_editor_settings = $default_editor_settings;
$popup_content_editor_settings['tinymce']['height'] = 100;

$popup_howto_editor_settings = $default_editor_settings;
$popup_howto_editor_settings['tinymce']['height'] = 150;

// EcoBag Product
$product_name_fr   = get_option('pick_pack_product_name_fr', PICK_PACK_DEFAULT_PRODUCT_NAME_FR);
$product_name_en   = get_option('pick_pack_product_name_en', PICK_PACK_DEFAULT_PRODUCT_NAME_EN);
$product_price     = get_option('pick_pack_product_price', PICK_PACK_DEFAULT_PRODUCT_PRICE);
$product_stock_qty = get_option('pick_pack_product_stock', 0);
$product_points    = get_option('pick_pack_product_default_points', PICK_PACK_DEFAULT_PRODUCT_POINTS);
$product_exclusive = get_option('pick_pack_product_exclusive') ? true : false;

// Popup in French
$popup_header_fr  = get_option('pick_pack_popup_header_fr', PICK_PACK_DEFAULT_POPUP_HEADER_FR);
$popup_content_fr = get_option('pick_pack_popup_content_fr', PICK_PACK_DEFAULT_POPUP_CONTENT_FR);
$popup_howto_fr   = get_option('pick_pack_popup_howto_fr', PICK_PACK_DEFAULT_POPUP_HOWTO_FR);

// Popup in English
$popup_header_en = get_option('pick_pack_popup_header_en', PICK_PACK_DEFAULT_POPUP_HEADER_EN);
$popup_content_en = get_option('pick_pack_popup_content_en', PICK_PACK_DEFAULT_POPUP_CONTENT_EN);
$popup_howto_en = get_option('pick_pack_popup_howto_en', PICK_PACK_DEFAULT_POPUP_HOWTO_EN);
?>

<!-- Page title -->
<h2 class="page-title"><?php esc_html_e('Settings', 'pick-pack'); ?></h2>

<!-- Product Options -->
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <?php esc_html_e('Product Options', 'pick-pack'); ?>
                <p><?php esc_html_e('Manage Pick Pack product.', 'pick-pack') ?></p>
            </th>
            <td>
                <div class="card">
                    <div class="form-field">
                        <label><?php esc_html_e('Product Name in French', 'pick-pack'); ?></label><br/>
                        <input value="<?php esc_attr_e($product_name_fr) ?>" id="pick_pack_product_name_fr" name="pick_pack_product_name_fr" type="text">
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e('Product Name in English', 'pick-pack'); ?></label><br/>
                        <input value="<?php esc_attr_e($product_name_en) ?>" id="pick_pack_product_name_en" name="pick_pack_product_name_en" type="text">
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e('Price per Bag', 'pick-pack'); ?></label><br/>
                        <input value="<?php esc_attr_e($product_price) ?>" id="pick_pack_product_price" name="pick_pack_product_price" type="number" min="0" step="0.01"> $
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e('Stock Quantity', 'pick-pack'); ?></label><br/>
                        <input value="<?php esc_attr_e($product_stock_qty) ?>" id="pick_pack_product_stock" name="pick_pack_product_stock" type="number" min="0" step="1">
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e('Default Points for Products', 'pick-pack'); ?></label><br/>
                        <select id="pick_pack_product_points" name="pick_pack_product_points">
                            <option value="1" <?php selected($product_points, '1'); ?>><?php esc_html_e("1 point (small products)", 'pick-pack'); ?></option>
                            <option value="2" <?php selected($product_points, '2'); ?>><?php esc_html_e("2 points", 'pick-pack'); ?></option>
                            <option value="3" <?php selected($product_points, '3'); ?>><?php esc_html_e("3 points", 'pick-pack'); ?></option>
                            <option value="4" <?php selected($product_points, '4'); ?>><?php esc_html_e("4 points", 'pick-pack'); ?></option>
                            <option value="5" <?php selected($product_points, '5'); ?>><?php esc_html_e("5 points", 'pick-pack'); ?></option>
                            <option value="6" <?php selected($product_points, '6'); ?>><?php esc_html_e("6 points", 'pick-pack'); ?></option>
                            <option value="7" <?php selected($product_points, '7'); ?>><?php esc_html_e("7 points", 'pick-pack'); ?></option>
                            <option value="8" <?php selected($product_points, '8'); ?>><?php esc_html_e("8 points", 'pick-pack'); ?></option>
                            <option value="9" <?php selected($product_points, '9'); ?>><?php esc_html_e("9 points", 'pick-pack'); ?></option>
                            <option value="10" <?php selected($product_points, '10'); ?>><?php esc_html_e("10 points", 'pick-pack'); ?></option>
                            <option value="11" <?php selected($product_points, '11'); ?>><?php esc_html_e("11 points", 'pick-pack'); ?></option>
                            <option value="12" <?php selected($product_points, '12'); ?>><?php esc_html_e("12 points", 'pick-pack'); ?></option>
                            <option value="13" <?php selected($product_points, '13'); ?>><?php esc_html_e("13 points", 'pick-pack'); ?></option>
                            <option value="14" <?php selected($product_points, '14'); ?>><?php esc_html_e("14 points", 'pick-pack'); ?></option>
                            <option value="15" <?php selected($product_points, '15'); ?>><?php esc_html_e("15 points", 'pick-pack'); ?></option>
                            <option value="16" <?php selected($product_points, '16'); ?>><?php esc_html_e("16 points", 'pick-pack'); ?></option>
                            <option value="17" <?php selected($product_points, '17'); ?>><?php esc_html_e("17 points", 'pick-pack'); ?></option>
                            <option value="18" <?php selected($product_points, '18'); ?>><?php esc_html_e("18 points", 'pick-pack'); ?></option>
                            <option value="19" <?php selected($product_points, '19'); ?>><?php esc_html_e("19 points", 'pick-pack'); ?></option>
                            <option value="20" <?php selected($product_points, '20'); ?>><?php esc_html_e("20 points (large products)", 'pick-pack'); ?></option>
                        </select>
                        <p><?php esc_html_e("To make Pick Packs more compatible with your products, please add points to each product category so we can manage quantities and spaces correctly. You can also set points directly in each product. Note that the large Pick Pack format has a capacity of 20 points. Thus, for every 21 points, we add a Pick Pack to the consumer's invoice.", 'pick-pack') ?></p>
                    </div>
                    <div class="form-field">
                        <input type="checkbox" id="pick_pack_product_exclusive" name="pick_pack_product_exclusive" <?php checked($product_exclusive) ?>>
                        <label for="pick_pack_product_exclusive"><?php esc_html_e('Exclusive PickPack products', 'pick-pack') ?></label>
                        <p><?php esc_html_e('When this option is enabled, it allows you to attach the PickPack tag to the products you want delivered in PickPack. ONLY marked products can be delivered in a PickPack.', 'pick-pack') ?></p>
                    </div>
                    <div class="form-controls">
                        <div id="ajax_status_product" class="ajax-status"></div>
                        <div class="form-buttons">
                            <input id="update_product" type="button" value="<?php esc_attr_e('Save Changes', 'pick-pack') ?>" class="btn btn-primary">
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<hr>

<!-- Popup in French -->
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <?php esc_html_e('Popup content in French', 'pick-pack'); ?>
                <p><?php esc_html_e('You can customize the content of the popup.', 'pick-pack') ?></p>
            </th>
            <td>
                <div class="card">
                    <div class="form-field">
                        <label><?php esc_html_e('Header', 'pick-pack'); ?></label><br/>
                        <textarea id="pick_pack_popup_header_fr" name="pick_pack_popup_header_fr" rows="2"><?php esc_html_e($popup_header_fr) ?></textarea>
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e('Content', 'pick-pack'); ?></label>
                        <?php wp_editor($popup_content_fr, 'pick_pack_popup_content_fr', $popup_content_editor_settings); ?>
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e('How it works', 'pick-pack'); ?></label>
                        <?php wp_editor($popup_howto_fr, 'pick_pack_popup_howto_fr', $popup_howto_editor_settings); ?>
                    </div>
                    <div class="form-controls">
                        <div id="ajax_status_popup_fr" class="ajax-status"></div>
                        <div class="form-buttons">
                            <input id="update_popup_fr" type="button" value="<?php esc_attr_e('Save Changes', 'pick-pack') ?>" class="btn btn-primary">
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<hr>

<!-- Popup in English -->
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <?php esc_html_e('Popup content in English', 'pick-pack'); ?>
                <p><?php esc_html_e('You can customize the content of the popup.', 'pick-pack') ?></p>
            </th>
            <td>
                <div class="card">
                    <div class="form-field">
                        <label><?php esc_html_e('Header', 'pick-pack'); ?></label>
                        <textarea id="pick_pack_popup_header_en" name="pick_pack_popup_header_en" rows="2"><?php esc_html_e($popup_header_en) ?></textarea>
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e('Content', 'pick-pack'); ?></label>
                        <?php wp_editor($popup_content_en, 'pick_pack_popup_content_en', $popup_content_editor_settings); ?>
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e('How it works', 'pick-pack'); ?></label>
                        <?php wp_editor($popup_howto_en, 'pick_pack_popup_howto_en', $popup_howto_editor_settings); ?>
                    </div>
                    <div class="form-controls">
                        <div id="ajax_status_popup_en" class="ajax-status"></div>
                        <div class="form-buttons">
                            <input id="update_popup_en" type="button" value="<?php esc_attr_e('Save Changes', 'pick-pack') ?>" class="btn btn-primary">
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>

<!-- Javascript -->
<script>
jQuery(document).ready(function($) {
    // Update Product
    $('#update_product').click(function(event) {
        event.preventDefault();

        const name_fr  = $('#pick_pack_product_name_fr').val();
        const name_en  = $('#pick_pack_product_name_en').val();
        const price    = $('#pick_pack_product_price').val().replace(/,/g, '.');
        const stock_quantity = parseInt($('#pick_pack_product_stock').val());
        const default_points = parseInt($('#pick_pack_product_points').val());
        const is_exclusive = $('#pick_pack_product_exclusive').is(':checked');

        $('#ajax_status_product').html('<?php esc_attr_e("Please wait ...", "pick-pack") ?>');
        $('#update_product').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '<?php esc_attr_e(admin_url('admin-ajax.php')) ?>',
            data: {
                action: 'update_product',
                nonce: '<?php esc_attr_e(wp_create_nonce('my_ajax_nonce')) ?>',
                name_fr: name_fr,
                name_en: name_en,
                price: price,
                stock: stock_quantity,
                default_points: default_points,
                exclusive: is_exclusive ? 1 : 0
            },
            success: function(response) {
                $('#ajax_status_product').html('<span class="success"><?php esc_attr_e("Updated.", "pick-pack") ?></span>');
                $('#update_product').prop('disabled', false);
            },
            error: function() {
                $('#ajax_status_product').html('<span class="error"><?php esc_attr_e("Error, please retry!", "pick-pack") ?></span>');
                $('#update_product').prop('disabled', false);
            }
        });
    });

    // Update Popup in French
    $('#update_popup_fr').click(function(event) {
        event.preventDefault();

        const header  = $('#pick_pack_popup_header_fr').val();
        const content = tinyMCE.get('pick_pack_popup_content_fr') ? tinyMCE.get('pick_pack_popup_content_fr').getContent() : null;
        const howto   = tinyMCE.get('pick_pack_popup_howto_fr') ? tinyMCE.get('pick_pack_popup_howto_fr').getContent() : null;

        $('#ajax_status_popup_fr').html('<?php esc_attr_e("Please wait ...", "pick-pack") ?>');
        $('#update_popup_fr').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '<?php esc_attr_e(admin_url('admin-ajax.php')) ?>',
            data: {
                action: 'update_popup_fr',
                nonce: '<?php esc_attr_e(wp_create_nonce('my_ajax_nonce')) ?>',
                header: header,
                content: content,
                howto: howto
            },
            success: function(response) {
                $('#ajax_status_popup_fr').html('<span class="success"><?php esc_attr_e("Updated.", "pick-pack") ?></span>');
                $('#update_popup_fr').prop('disabled', false);
            },
            error: function() {
                $('#ajax_status_popup_fr').html('<span class="error"><?php esc_attr_e("Error, please retry!", "pick-pack") ?></span>');
                $('#update_popup_fr').prop('disabled', false);
            }
        });
    });

    // Update Popup in English
    $('#update_popup_en').click(function(event) {
        event.preventDefault();

        const header  = $('#pick_pack_popup_header_en').val();
        const content = tinyMCE.get('pick_pack_popup_content_en') ? tinyMCE.get('pick_pack_popup_content_en').getContent() : null;
        const howto   = tinyMCE.get('pick_pack_popup_howto_en') ? tinyMCE.get('pick_pack_popup_howto_en').getContent() : null;

        $('#ajax_status_popup_en').html('<?php esc_attr_e("Please wait ...", "pick-pack") ?>');
        $('#update_popup_en').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '<?php esc_attr_e(admin_url('admin-ajax.php')) ?>',
            data: {
                action: 'update_popup_en',
                nonce: '<?php esc_attr_e(wp_create_nonce('my_ajax_nonce')) ?>',
                header: header,
                content: content,
                howto: howto
            },
            success: function(response) {
                $('#ajax_status_popup_en').html('<span class="success"><?php esc_attr_e("Updated.", "pick-pack") ?></span>');
                $('#update_popup_en').prop('disabled', false);
            },
            error: function() {
                $('#ajax_status_popup_en').html('<span class="error"><?php esc_attr_e("Error, please retry!", "pick-pack") ?></span>');
                $('#update_popup_en').prop('disabled', false);
            }
        });
    });
});
</script>
