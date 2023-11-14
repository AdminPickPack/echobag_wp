<?php

/**
 * The multiple categories products page
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://pick-pack.ca/
 * @since      1.0.0
 *
 * @package    Pick_pack_package
 * @subpackage Pick_pack_package/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="pick_pack_container container">
    <div class="row pt-5 multiple-categories">
        <div class="col-sm-12 ">
            <h1 class="pick_pack_main_title">Multiple Categories Products:</h1>
            <form method = "POST">
                <?php foreach ($multiple_categories_products as $product):
                    $category_choosen = get_post_meta($product->get_id(), 'category_selected', true);
                 ?>
                    <h5>Select the category to be allocated points from for the product named <?php echo $product->get_name() ?>:</h5>
                    <select name="category_selected[<?php echo $product->get_id() ?>][]">
                        <?php $product_terms = get_the_terms( $product->get_id(), 'product_cat' );
                        foreach ($product_terms as $term):?>
                        <option <?php echo ($category_choosen == $term->term_id) ? 'selected' : '' ?> value = "<?php echo $term->term_id ?>"><?php echo $term->name ?></option>
                    <?php endforeach; ?>
                    </select>
                <?php endforeach; ?>
                <br>
                <button type="submit" class="pick_pack_buttons_hover">Save</button>

            </form>
        </div>
    </div>

</div>