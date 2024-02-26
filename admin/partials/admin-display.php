<?php
/**
 * Provide an admin area view for the plugin.
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
<div class="pick-pack-admin-container container">
    <div class="row pt-5">
        <div class="col-sm-12 ">
            <h1 class="pick_pack_main_title"><?php esc_html_e('Pick Pack Integration', 'pick-pack'); ?></h1>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row">
            <div class="col-md-10 ml-auto col-xl-12 mr-auto">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs " role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#home" role="tab">
                            <i class="fa-solid fa-address-card pr-1"></i> Pick Pack Model
                        </a>
                    </li>
                </ul>
                <div class="tab-pane active" id="home" role="tabpanel">
                    <div class="card">
                        <div class="card-header">

                        </div>
                        <div class="card-body">
                            <div class="row pt-4" id="split-payment-price">
                                <div class="col-sm-12">
                                    <h5><?php esc_html_e('Eco Bag Price: ', 'pick-pack') ?><?php esc_html($eco_bag_price !== false ? $eco_bag_price : esc_html__('Not set', 'pick-pack')) ?></h5>
                                </div>
                            </div>

                            <div class="row pt-4 stock-quantity-container" id="default-payment-price">
                                <div class="col-sm-12">
                                    <form method='post' action="<?php esc_attr_e(esc_url(admin_url('admin-post.php'))); ?>">
                                        <input type="hidden" name="action" value="default_price_form_submission">
                                        <?php wp_nonce_field('my-nonce'); ?>
                                        <input value="<?php esc_attr_e($eco_bag_default_price) ?>" class="stock-quantity" name="default-price" type="number">
                                        <input class="category-header-items-submit stock-quantity-button" type="submit" value="<?php esc_attr_e('Update Price', 'pick-pack') ?>" />
                                    </form>
                                </div>
                            </div>

                            <div class="row pt-4">
                                <div class="col-sm-12">
                                    <h5><?php esc_html_e('Enable Split Payment', 'pick-pack') ?></h5>
                                </div>
                                <div class="col-sm-12">
                                    <label class="pick-pack-switch">
                                        <input id="split-payment-checkbox" type="checkbox">
                                        <span class="pick-pack-slider pick-pack-round"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="row pt-4">
                                <div class="col-sm-12">
                                    <div id="stock-warning" class="alert alert-warning" role="alert">
                                        <?php esc_html_e('No Stock', 'pick-pack') ?>
                                    </div>
                                    <div id="split-payment-no-payment-method-warning" class="alert alert-warning" role="alert">
                                        <?php esc_html_e('No payment method added', 'pick-pack') ?>
                                    </div>
                                    <div id="split-payment-both-left-warning" class="alert alert-warning" role="alert">
                                        <?php esc_html_e('Company not registered and Payment Method not set', 'pick-pack') ?>
                                    </div>
                                    <div id="default-payment-incomplete-warning" class="alert alert-warning" role="alert">
                                        <?php esc_html_e('Company not registered', 'pick-pack') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row pt-4 stock-quantity-container">
                                <div class="col-sm-12">
                                    <form method='post' action="<?php esc_attr_e(esc_url(admin_url('admin-post.php'))); ?>">
                                        <input type="hidden" name="action" value="stock_form_submission">
                                        <?php wp_nonce_field('my-nonce'); ?>
                                        <input value="<?php esc_attr_e($stock_quantity) ?>" class="stock-quantity" name="stock-quantity" type="number">
                                        <input class="category-header-items-submit stock-quantity-button" type="submit" value="<?php esc_attr_e('Update Stock', 'pick-pack') ?>" />
                                    </form>
                                </div>
                            </div>

                            <div id="popup-text-container" class="row pt-4 pick-pack-row ">
                                <div class="col-lg-4 col-sm-12 pick-pack-column ">
                                    <input id="popup-preview-button" class="category-header-items-submit" type="submit" value="<?php esc_attr_e('Change the text displayed in the Pick Pack Popup', 'pick-pack') ?>" />
                                </div>
                            </div>

                            <div class="admin-overlay" id="admin_overlay">
                                <h3 class="preview-text">Pick Pack Popup Preview</h3>
                                <span class="close toggle" id="preview-close" data-target="admin_overlay">close</span>
                                <div class="row pt-4 pick-pack-row ">
                                    <div class="col-lg-3 col-sm-12 pick-pack-column pick-pack-column-color">
                                        <form method='post' action="<?php esc_attr_e(esc_url(admin_url('admin-post.php'))); ?>" id="popup-text-form">
                                            <input type="hidden" name="action" value="popup_text_form_submission">
                                            <?php wp_nonce_field('my-nonce'); ?>

                                            <h5><?php esc_html_e('Heading Text', 'pick-pack') ?></h5>
                                            <textarea id="header-editing" maxlength="50" class="pick-pack-popup-text" name="popup-header"><?php esc_html_e($popup_header) ?></textarea>

                                            <h5><?php esc_html_e('Content Text', 'pick-pack') ?></h5>
                                            <textarea id="content-editing" class="pick-pack-popup-text" maxlength="50" name="popup-text"><?php esc_html_e($popup_text) ?></textarea>

                                            <input class="category-header-items-submit" type="submit" value="<?php esc_attr_e('Update', 'pick-pack') ?>" />
                                        </form>
                                    </div>
                                    <div class="col-lg-9 col-sm-12 pick-pack-column  ">
                                        <?php require_once plugin_dir_path(__FILE__) . 'admin-popup.php'; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row pt-4 pick-pack-row ">
                                <div class="col-lg-4 col-sm-12 pick-pack-column ">
                                    <h5><?php esc_html_e('Orders of Ecobags this month', 'pick-pack') ?></h5>
                                </div>
                                <div class="col-lg-6 col-sm-12 pick-pack-column pick-pack-column-color ">
                                    <div class="orders-container">
                                        <?php _e($eco_bags_sold_display) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row pt-4 pick-pack-row ">
                                <div class="col-lg-4 col-sm-12 pick-pack-column ">
                                    <h5><?php esc_html_e('The products that have been labeled as too large for an eco bag. To label a product as large assign to it the "Large" category', 'pick-pack'); ?></h5>
                                </div>
                                <div class="col-lg-6 col-sm-12 pick-pack-column pick-pack-column-color ">
                                    <div class="large-fragile-container">
                                       <?php foreach ($products as $product) : ?>
                                           <p class="excluded-products"><?php esc_html_e($product->name) ?></p>
                                       <?php endforeach ?>
                                    </div>

                                </div>
                            </div>

                            <div class="row pt-4 pick-pack-row ">
                                <div class="col-lg-4 col-sm-12 pick-pack-column ">
                                    <h5><?php esc_html_e('The products that have been labeled as fragile for an eco bag. To label a product as fragile assign to it the "fragile" category', 'pick-pack'); ?></h5>
                                </div>
                                <div class="col-lg-6 col-sm-12 pick-pack-column pick-pack-column-color ">
                                    <div class="large-fragile-container">
                                        <?php foreach ($products_2 as $product) : ?>
                                            <p class="excluded-products"><?php esc_html_e($product->name) ?></p>
                                        <?php endforeach ?>
                                    </div>

                                </div>
                            </div>

                            <div class="row pt-4 pick-pack-row ">
                                <div class="col-lg-4 col-sm-12 pick-pack-column ">
                                    <h5><?php esc_html_e('A single eco bag can contain products that have total points allocated less than 21. Products are allocated points as per the category they belong too. The default points associated to every category is four, you can change the default value by assigning custom points to the category', 'pick-pack'); ?></h5>
                                </div>
                                <div class="col-lg-6 col-sm-12 pick-pack-column pick-pack-column-color pick-pack-column-category">
                                    <div class="category-container">
                                        <div class="category-header">
                                            <p class="category-header-items" id="category-header-add">Add Category</p>
                                            <input class="category-header-items-submit" type="submit" form="category-form" value="Update" />
                                        </div>
                                        <div class="pick-pack-form-container">
                                            <form method='post' action="<?php esc_attr_e(esc_url(admin_url('admin-post.php'))); ?>" id="category-form">
                                                <input type="hidden" name="action" value="category_form_submission">
                                                <?php wp_nonce_field('my-nonce'); ?>
                                                <?php foreach ($category_array as $category) :
                                                    if ($category['category_value'] != false) : ?>
                                                        <div class="point-allocator-container">
                                                            <select class="col-lg-5 col-sm-12" name="categories[name][]">
                                                                <option value="" disabled>Select Category</option>
                                                                <?php foreach ($categories as $category_2) : ?>
                                                                    <option value="<?php esc_attr_e($category_2->term_id) ?>" <?php esc_attr_e($category_2->term_id == $category['category_id'] ? 'selected' : '') ?>>
                                                                      <?php esc_html_e($category_2->name) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <input class="col-lg-4 col-sm-12" type="number" name="categories[points][]" value="<?php esc_attr_e($category['category_value']) ?>">
                                                            <p class="pick-pack-close category-header-items category-remove-button"></p>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row pt-4 pick-pack-row ">
                                <div class="col-lg-4 col-sm-12 pick-pack-column ">
                                    <h5><?php esc_html_e('Products are assigned points from the category they belong too. In this section the products that have multiple categories assigned to them are listed. You can select the category from which they should be assigned points from', 'pick-pack'); ?></h5>
                                </div>
                                <div class="col-lg-6 col-sm-12 pick-pack-column pick-pack-column-color pick-pack-column-category-multiple">
                                    <div class="category-container-multiple">
                                        <div class="category-header">
                                            <input class="category-header-items-submit" type="submit" form="category-multiple-form" value="Update" />
                                        </div>
                                        <div class="category-header-multiple">
                                            <select class="col-lg-5 col-sm-12" name="multiple-category-product">
                                                <option value="" disabled selected>Select Product</option>
                                                <?php foreach ($multiple_categories_products as $key => $product_name) : ?>
                                                    <option value="<?php esc_attr_e($key) ?>"><?php esc_html_e($product_name) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                            <p class="category-header-items col-lg-5 col-sm-12" id="multiple-category-product-add">Select Product</p>
                                        </div>

                                        <form method='post' action="<?php esc_attr_e(esc_url(admin_url('admin-post.php'))); ?>" id="category-multiple-form">
                                            <input type="hidden" name="action" value="category_multiple_form_submission">
                                            <?php wp_nonce_field('my-nonce'); ?>

                                            <?php foreach ($choosen_products as $product) :
                                                $product_terms = get_the_terms($product['id'], 'product_cat');
                                                $category_choosen = get_post_meta($product['id'], 'category_selected', true); ?>
                                                <div class="selected-product-row">
                                                    <p class="col-lg-5 col-sm-12 pick-pack-selected-product"><?php esc_html_e($product['name']) ?></p>

                                                    <select class="col-lg-4 col-sm-12" name="choosen-category[<?php esc_attr_e($product['id']) ?>]">
                                                        <option value="" disabled>Select Category</option>
                                                        <?php foreach ($product_terms as $term) : ?>
                                                            <option <?php esc_attr_e($category_choosen == $term->term_id ? 'selected' : '') ?> value="<?php esc_attr_e($term->term_id) ?>"><?php esc_html_e($term->name) ?></option>
                                                        <?php endforeach ?>
                                                    </select>
                                                    <p class="pick-pack-close category-header-items multiple-category-product-remove"></p>

                                                </div>
                                            <?php endforeach ?>

                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="row pt-4 pick-pack-row ">
                                <div class="col-lg-4 col-sm-12 pick-pack-column ">
                                    <h5><?php _e('1. Register/Change your company details with our system.<br><br>2. Register/Change your payment details', 'pick-pack') ?></h5>
                                </div>

                                <div class="col-lg-6 col-sm-12 pick-pack-column pick-pack-column-color ">
                                    <div class="company-details-container">
                                        <?php if ($pick_pack_token === false): ?>
                                            <a class="customer-details-button" href='<?php esc_attr_e(PICK_PACK_SERVER . 'token.php?register=false&home-url=' . home_url()) ?>'>Register Company Details</a>
                                        <?php else: $payment = ($eco_bag_token === false) ? 'false' : 'true'; ?>
                                            <a class="customer-details-button" href='<?php esc_attr_e(PICK_PACK_SERVER . 'token.php?register=true&home-url=' . home_url() . '&token=' . $pick_pack_token . '&payment=' . $payment) ?>'>Change Company Details</a>
                                        <?php endif; ?>

                                        <?php if ($eco_bag_token === false): ?>
                                            <form method="POST" action="<?php esc_attr_e(esc_url(admin_url('admin-post.php'))) ?>">
                                                <?php wp_nonce_field('my-nonce'); ?>
                                                <input type="hidden" name="action" value="pick_pack_payment">
                                                <button type="submit" class="payment-method-button"><?php esc_html_e('Register Payment Method', 'pick-pack') ?></button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?php esc_attr_e(esc_url(admin_url('admin-post.php'))) ?>">
                                                <?php wp_nonce_field('my-nonce'); ?>
                                                <input type="hidden" name="token_update" value="true">
                                                <input type="hidden" name="action" value="pick_pack_payment">
                                                <button type="submit" class="payment-method-button"><?php esc_html_e('Change Payment Method', 'pick-pack') ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
