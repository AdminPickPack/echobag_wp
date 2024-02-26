<?php
/**
 * Display pick-pack popup preview in admin.
 *
 * @package    Pick_Pack
 * @subpackage Pick_Pack/admin
 * @author     Pick Pack <admin@pick-pack.ca>
 * @since      1.0.0
 */
if (!defined('ABSPATH')) exit;
?>
<div id="pick_pack_popup" class="pick-pack-container">
    <div class="popup-header">
        <span class="close toggle"><?php esc_html_e("close", 'pick-pack') ?></span>
    </div>
    <div class="popup-body">
        <h3><?php esc_html_e("Do you want a PickPack reusable package?", 'pick-pack') ?></h3>

        <h4 id="header-text-preview">
            <?php echo nl2br($popup_header) ?>
        </h4>
           
        <div class="popup_flex">
            <img class="pick-pack-logo" src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/ecobag-product.jpg' ?>" alt="">
            <div class="popup_col">
                <div class="cnt">
                    <p id="content-text-preview"><?php echo nl2br($popup_text) ?></p>
                </div>
            </div>

            <label class="form-control-checkbox">
              <input type="checkbox" name="checkbox-pickpack"  id="checkbox-pickpack"/>
              <?php esc_html_e("My order will be delivered in Canada", 'pick-pack') ?>
            </label>

            <div class="popup-footer">
                <div class="footer-flex">
                    <button class="pick_pack_button pick_pack_add_checkout" data-target="pick_pack_popup"><?php esc_html_e("Yes, I want to make a difference.", 'pick-pack') ?></button>
                    <button type='button' class="toggle cancel-button"><?php esc_html_e("No thank you", 'pick-pack') ?></button>
                </div>
                <div class="centered">
                    <p class="inline-text"><?php esc_html_e("Initiative powered by PickPack", 'pick-pack') ?> </p>
                    <img class="inline-image" src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/pick-pack-logo.png' ?>" alt="">
                </div>
                    
                <p class="conditions"><?php 
                    $span_text = '<span>CONDITIONS : </span>';
                    $string = esc_html__("CONDITIONS : You must choose the PickPack option AND return your packaging as agreed.", 'pick-pack');
                    $string = str_replace("CONDITIONS : ", "", $string);
                    echo $span_text . $string;
                ?>
                </p>
            </div>
        </div>
    </div>
        
</div>
