<?php
/**
 * The multiple categories products page.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @package    Pick_Pack
 * @subpackage Pick_Pack/admin
 * @author     Pick Pack <admin@pick-pack.ca>
 * @since      1.0.0
 */
if (!defined('ABSPATH')) exit;
?>
<div class="pick_pack_container container">
    <div class="row pt-5 multiple-categories">
        <div class="col-sm-12 ">
            <h1 class="pick_pack_main_title"><?php esc_html_e('Multiple Categories Products:', 'pick-pack') ?></h1>
            <form method = "POST">
                <?php foreach ($multiple_categories_products as $product):
                    $category_choosen = get_post_meta($product->get_id(), 'category_selected', true);
                    ?>
                    <h5><?php echo sprintf(esc_html__("Select the category to be allocated points from for the product named %s:", 'pick-pack'), esc_html($product->get_name())) ?></h5>
                    <select name="category_selected[<?php esc_attr_e($product->get_id()) ?>][]">
                        <?php $product_terms = get_the_terms($product->get_id(), 'product_cat'); foreach ($product_terms as $term): ?>
                            <option <?php esc_attr_e($category_choosen == $term->term_id ? 'selected' : '') ?> value = "<?php esc_attr_e($term->term_id) ?>"><?php esc_attr_e($term->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endforeach; ?>
                <br>
                <button type="submit" class="pick_pack_buttons_hover">Save</button>
            </form>
        </div>
    </div>

</div>
