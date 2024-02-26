<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    Pick_Pack
 * @subpackage Pick_Pack/public
 * @author     Pick Pack <admin@pick-pack.ca>
 * @since      1.0.0
 */
if (!defined('ABSPATH')) exit;

class Pick_Pack_Public
{
    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pick-pack-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_scripts()
    {
        $state_name_array = $this->get_state_name_array();

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pick-pack-public.js', array('jquery'), $this->version, false);
        $jsarray = array(
        'class_name' => 'single_add_to_cart_button',
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('_wpnonce'),
        'state_name_array' => $state_name_array

        );
        wp_localize_script($this->plugin_name, 'php_vars', $jsarray);
    }

    /**
     * Check if pick-pack plugin is setup or not.
     *
     * @since 1.0.0
     * @return bool Return true if pick-pack plugin is setup.
     */
    public function plugin_is_setup()
    {
        $eco_bag_token = get_option('pick_pack_ecobag_token');
        $temp_eco_bag_token = get_option('pick_pack_temp_ecobag_token');
        $split_payment = get_option('pick_pack_split_payment');
        $stock = (int) get_option('pick_pack_ecobag_stock');

        if (!$temp_eco_bag_token || ($split_payment && !$eco_bag_token) || $stock === 0) {
            return false;
        }

        return true;
    }

    /**
     * Add pick-pack model to woocommerce cart. Checks eco bag is eligible,if its in the cart and not eligible;remove it from cart. If its not in the cart and eligible show the popup or add eco button.
     *
     * @since 1.0.0
     * @return void
     */
    public function pick_pack_add_model()
    {
        if (!$this->plugin_is_setup()) {
            return;
        }
        
        $product_id = get_option('pick_pack_product');

        $fragile = [];
        $large = [];
	
        // Find out if there is a fragile or large product
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id_2 = $cart_item['product_id'];

            $terms = get_the_terms($product_id_2, 'product_cat');
            if ($terms != false && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    if ($term->name == "Fragile Product") {
                        $fragile[] = sprintf(esc_attr__('%s is a fragile product', 'pick-pack'), $cart_item['data']->get_name());
                    }

                    if ($term->name == "Large Product") {
                        $large[] = sprintf(esc_attr__('%s is a large product', 'pick-pack'), $cart_item['data']->get_name());
                    }
                }
            }
        }

        $remove_eco_bag = false;
        $cart_count = count(WC()->cart->get_cart());
        $fragile_count = count($fragile);
        $large_count = count($large);
        $pick_pack_count = 0;
        $points_allocated_less = false;
        $split_payment = get_option('pick_pack_split_payment');
        $popup_text = get_option('pick_pack_popup_text', '');
        $popup_header = get_option('pick_pack_popup_header', '');

        if ($this->pick_pack_woo_in_cart($product_id)) {
            $pick_pack_count++;
        }

        // If the only remaining product is eco bag
        if ($cart_count == 1) {
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                if ($cart_item['product_id'] == $product_id) {
                    $remove_eco_bag = true;
                }
            }
        }

        // If only large or fragile products are present
        if ($cart_count == ($fragile_count + $large_count + $pick_pack_count)) {
            $remove_eco_bag = true;
        }

	// Count points
        $points = $this->get_eco_bag_quantity(WC()->cart, false);
	
        // Points less than 5
        if (($fragile_count > 0 || $large_count > 0) && $points < 5) {
            $remove_eco_bag = true;
            $points_allocated_less = true;
        }
        // Points less than 3 without fragile and large items
        if ($fragile_count == 0 && $large_count == 0 && $points < 3) {
            $remove_eco_bag = true;
            $points_allocated_less = true;
        }

        // Get the eco bag key
        $eco_bag_key = '';
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            // code...
            if ($cart_item['product_id'] == $product_id) {
                $eco_bag_key = $cart_item_key;
            }
        }

        if ($remove_eco_bag) {
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                if ($cart_item['product_id'] == $product_id) {
                    WC()->cart->remove_cart_item($cart_item_key);
		    
                    if (!empty($_SESSION["pick_pack_product_added"]) && $_SESSION["pick_pack_product_added"] == $product_id) {
                        unset($_SESSION['pick_pack_product_added']);
                    }

                    $cart_count--;
                }
            }
        }

        include_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/cart-popup.php';
    }

    /**
     * Add product into cart. Handles the ajax request from the pop up modal.
     *
     * @since 1.0.0
     * @return void
     */
    public function pick_pack_add_to_cart_product_callback()
    {
        if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
            session_start();
        }
	
        $nonce = check_ajax_referer('_wpnonce', 'security');

        global $woocommerce;

        $product_id = get_option('pick_pack_product');
        $status = ["status" => "false", "product_add" => "false"];

        if (!empty($product_id)) {
            $add = $woocommerce->cart->add_to_cart($product_id, 1);
            if ($add) {
                $status["status"] = true;
                $status["product_add"] = true;
                $_SESSION["pick_pack_product_added"] = $product_id;
            }
        }

        echo wp_json_encode($status);
        wp_die();
    }

    /**
     * Check pick-pack eco bag product in cart or not.
     *
     * @since 1.0.0
     * @return bool Return true if pick-pack eco bag is in cart.
     */
    public function pick_pack_woo_in_cart($product_id)
    {
        global $woocommerce;
        foreach ($woocommerce->cart->get_cart() as $key => $val) {
            $_product = $val['data'];
            if ($product_id == $_product->get_id()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Actions when an item is removed from cart.
     *
     * @since 1.0.0
     * @return void
     */
    public function pick_pack_remove_item_from_cart($cart_item_key, $cart)
    {
        if (is_checkout()) {
            return;
        }
	
        if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
            session_start();
        }
	
        $product_id = get_option('pick_pack_product');

        $line_item = $cart->removed_cart_contents[$cart_item_key];
        $product_id_temp = $line_item['product_id'];

        if ($product_id == $product_id_temp) {
            if (!empty($_SESSION["pick_pack_product_added"]) && $_SESSION["pick_pack_product_added"] == $product_id) {
                unset($_SESSION['pick_pack_product_added']);
            }
        }
    }

    /**
     * Remove ecobag product from posts query.
     *
     * @since 1.0.0
     * @return void
     */
    public function remove_bag_from_query($query)
    {
        if (!is_admin() && $query->get('post_type') == "product") {
            $product = get_page_by_title('Pick Pack', OBJECT, array('product'));
            if (!is_null($product)) {
                $query->set('post__not_in', array($product->ID));
            }
        }
    }

    /**
     * Filter ecobag product from related products.
     *
     * @since 1.0.0
     * @return array The related products result
     */
    public function filter_bag_from_related_products($related_posts, $product_id, $args)
    {
        $product = get_page_by_title('Pick Pack', OBJECT, array('product'));

        $exclude_ids = [];
        if (!is_null($product)) {
            $exclude_ids = array($product->ID);
        }
        return array_diff($related_posts, $exclude_ids);
    }
    
    /**
     * Actions on order payment complete.
     *
     * @since 1.0.0
     * @return bool Return true if successfull, false on errors.
     */
    public function order_payment_complete($order_id)
    {
        $order = wc_get_order($order_id);
        foreach ($order->get_items() as $item_id => $item) {
            $item_data = $item->get_data();

            if ($item_data['name'] == "Pick Pack") {

                $split_payment = get_option('pick_pack_split_payment');
                $eco_bag_quantity = $item_data['quantity'];
                $eco_bag_price = $item_data['total'] / $item_data['quantity'];
                $stock_quantity = get_option('pick_pack_ecobag_stock', -1);

                if ($split_payment) {
                    $eco_bags_sold_array = get_option('pick_pack_ecobags_sold', array());
                    $eco_bags_sold_array[] = array('price' => $eco_bag_price, 'quantity' => $eco_bag_quantity);
                    $option = update_option('pick_pack_ecobags_sold', $eco_bags_sold_array);
                }

                // Also add the products sold and whether split payment activated
                $post_id = wp_insert_post(
                    array(
                    'post_type' => 'pickpackorders',
                    'post_status' => 'publish',
                    'post_title' => 'Order with a Pick Pack Bag',
                    'meta_input' => array(
                    'price' => $eco_bag_price,
                    'quantity' => $eco_bag_quantity
                    )
                    )
                );

                if ($stock_quantity > 0) {
		    $new_stock_quantity = $stock_quantity - $eco_bag_quantity;

                    if ($new_stock_quantity < 0) {
                        $new_stock_quantity = 0;
                    }

		    update_option('pick_pack_ecobag_stock', $new_stock_quantity);
                }

                // Eco_bag_token wont be present if split payment not there, cater for that
                $eco_bag_token = '';
                if ($split_payment) {
                    $eco_bag_token = get_option('pick_pack_temp_ecobag_token');
                } else {
                    $eco_bag_token = get_option('pick_pack_ecobag_token');
                }

                $request = new WP_Http();

                // Add split payment info
                $body = array('eco_bags_sold' => $eco_bag_quantity, 'eco_bag_price' => $eco_bag_price, 'order_id' => $post_id, 'timestamp' => date('Y/m/d h:i:s', time()), 'eco_bag_token' => $eco_bag_token, 'url' => get_site_url());

                $url = PICK_PACK_SERVER . 'dashboard/order_webhook.php';
                $response = $request->get($url, array('body' => $body));

                if (isset($response->errors)) {
                    return false;
                }

                if ($response['response']['code'] === 200) {
                    return true;
                }
            }
        }
    }

    /**
     * Change cart item quantities.
     *
     * @since 1.0.0
     * @return void
     */
    public function change_cart_item_quantities($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (did_action('woocommerce_before_calculate_totals') >= 2) {
            return;
        }

        // HERE below define your specific products IDs
        $specific_ids = array(get_option('pick_pack_product'));

        // Checking cart items
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = $cart_item['data']->get_id();
	    
            // Check for specific product IDs and change quantity
            if (in_array($product_id, $specific_ids)) {
                $new_qty = $this->get_eco_bag_quantity($cart);
                if ($new_qty == 0) {
                    WC()->cart->remove_cart_item($cart_item_key);

                    if (!empty($_SESSION["pick_pack_product_added"]) && $_SESSION["pick_pack_product_added"] == $product_id) {

                        unset($_SESSION['pick_pack_product_added']);
                    }
                    // New quantity
                } else {
                    $cart->set_quantity($cart_item_key, $new_qty);
                }
                
            }
        }
    }
    
    /**
     * Calculate eco bag quantities.
     *
     * @since 1.0.0
     * @return int The number of eco bags.
     */
    public function get_eco_bag_quantity($cart, $bags = true)
    {
        $item_bags = 0;
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $skip = false;

            $product_id = $cart_item['data']->get_id();
            if (get_option('pick_pack_product') == $product_id) {
                continue;
            }

            $taxonomy = 'product_cat';
            $categories = get_the_terms($product_id, 'product_cat');

            foreach ($categories as $category) {
                if ($category->name == "Large Product" || $category->name == "Fragile Product") {
                    $skip = true;
                }
            }

            if (count($categories) > 1 && !$skip) {
                $category_selected = get_post_meta($product_id, 'category_selected', true);
                if ($category_selected) {
                    $categories[0] = get_term($category_selected);
		}
            }
	    
            if (!$skip) {
                $product_per_bag = get_option('pick_pack_product_per_bag_' . $categories[0]->term_id, 3);
                $item_bags += $cart_item['quantity'] * $product_per_bag;
            }
        }

        $wholesome_bags = ceil($item_bags / 20);

        if ($bags) {
            return $wholesome_bags;
        } else {
            return $item_bags;
        }
    }

    /**
     * Return (redirect) from payment method.
     *
     * @since 1.0.0
     * @return void
     */
    public function return_from_payment_method()
    {
        if (isset($_GET['token']) && isset($_GET['status'])) {
            if (get_option('pick_pack_temp_ecobag_token') == $_GET['token'] && $_GET['status'] == 'success') {
                update_option('pick_pack_ecobag_token', $_GET['token']);
                wp_redirect(get_dashboard_url() . 'admin.php?page=pick-pack&status=success');
		exit;
            } else {
                wp_redirect(get_dashboard_url() . 'admin.php?page=pick-pack&status=failure');
		exit;
            }
        }
    }
    
    /**
     * Country option in checkout page.
     *
     * @since 1.0.0
     * @return void
     */
    public function country_option_checkout_page()
    {
        if (!$this->plugin_is_setup()) {
            return;
        }
	
        $product_id = get_option('pick_pack_product');
        $split_payment = get_option('pick_pack_split_payment');

        parse_str($_POST['post_data'], $post_data);

        if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($post_data['billing_country'] !== 'CA') {
            if ($this->pick_pack_woo_in_cart($product_id)) {
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    if ($cart_item['data']->get_id() == $product_id) {
                        
                        WC()->cart->remove_cart_item($cart_item_key);
                    }
                }
            }
        } else {
            if (!$this->pick_pack_woo_in_cart($product_id) && !empty($_SESSION["pick_pack_product_added"]) && $_SESSION["pick_pack_product_added"] === $product_id) {
                if (!$this->pick_pack_woo_in_cart($product_id)) {
                    $add = WC()->cart->add_to_cart($product_id, 1);
                    if ($add) {
                        $new_qty = $this->get_eco_bag_quantity(WC()->cart); // New quantity
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			    // Check for specific product IDs and change quantity
                            if ($cart_item['product_id'] == $product_id) {
                                WC()->cart->set_quantity($cart_item_key, $new_qty); // Change quantity
                            }
                        }
                    }
                }
            }
        }

    }

    /**
     * Get Canada states for tax rates.
     *
     * @since 1.0.0
     * @return array The canada states for tax rartes.
     */
    public function get_state_name_array()
    {
        $state_name_array = ['AB' => esc_attr__("Tax rate in Alberta is 5%", "pick-pack"), 'BC' => esc_attr__("Tax rate in British Columbia is 12%", "pick-pack"), 'MB' => esc_attr__("Tax rate in Manitoba is 13%", "pick-pack"), 'NB' => esc_attr__("Tax rate in New Brunswick is 13%", "pick-pack"), 'NL' => esc_attr__("Tax rate in Newfoundland and Labrador is 13%", "pick-pack"), 'NT' => esc_attr__("Tax rate in Northwest Territories is 5%", "pick-pack"), 'NS' => esc_attr__("Tax rate in Nova Scotia is 15%", "pick-pack"), 'NU' => esc_attr__("Tax rate in Nunavut is 5%", "pick-pack"), 'ON' => esc_attr__("Tax rate in Ontario is 13%", "pick-pack"), 'PE' => esc_attr__("Tax rate in Prince Edward Island is 14%", "pick-pack"), 'QC' => esc_attr__("Tax rate in Quebec is 14.975%", "pick-pack"), 'SK' => esc_attr__("Tax rate in Saskatchewan is 10%", "pick-pack"), 'YT' => esc_attr__("Tax rate in Yukon Territory is 5%", "pick-pack")];
        return $state_name_array;
    }

    /**
     * Get current state tax rates
     *
     * @since 1.0.0
     * @return array The states with current tax rates.
     */
    public function get_state_tax($state)
    {
        switch ($state) {
        case 'AB':
            $tax = 1.05;
            break;
        case 'BC':
            $tax = 1.12;
            break;
        case 'MB':
            $tax = 1.13;
            break;
        case 'NB':
            $tax = 1.13;
            break;
        case 'NL':
            $tax = 1.13;
            break;
        case 'NT':
            $tax = 1.05;
            break;
        case 'NS':
            $tax = 1.15;
            break;
        case 'NU':
            $tax = 1.05;
            break;
        case 'ON':
            $tax = 1.13;
            break;
        case 'PE':
            $tax = 1.14;
            break;
        case 'QC':
            $tax = 1.14975;
            break;
        case 'SK':
            $tax = 1.10;
            break;
        case 'YT':
            $tax = 1.05;
            break;
        default:
            $tax = 1.00;
            break;
        }

        return $tax;
    }
    
    /**
     * Add taxes to eco bag in checkout page.
     *
     * @since 1.0.0
     * @return void
     */
    public function checkout_page_pick_pack_tax()
    {
        if (!$this->plugin_is_setup()) {
            return;
        }
        $product_id = get_option('pick_pack_product');
        $state_name_array = $this->get_state_name_array();

        parse_str($_POST['post_data'], $post_data);

        if ($post_data['billing_country'] === 'CA' && array_key_exists('billing_state', $post_data) && array_key_exists($post_data['billing_state'], $state_name_array)) {

            if ($this->pick_pack_woo_in_cart($product_id)) {
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    if ($cart_item['data']->get_id() == $product_id) {
                        $eco_bag_price = $cart_item['data']->get_price();
                        $tax = $this->get_state_tax($post_data['billing_state']);
                        $cart_item['data']->set_price(number_format((float)$eco_bag_price * $tax, 2, '.', ''));
                    }
                }
            }
        }
    }
    
    /**
     * Add taxes to eco bag in order.
     *
     * @since 1.0.0
     * @return void
     */
    public function checkout_pick_pack_add_tax_order($order_id, $posted_data, $order)
    {
        $billing_state = $order->get_billing_state();
        foreach ($order->get_items() as $item_id => $item) {
            $item_data = $item->get_data();
            if ($item_data['name'] == "Pick Pack") {
                $tax = $this->get_state_tax($billing_state);
                $product        = $item->get_product();
                $eco_bag_price = $product->get_price();

                $item->set_total(number_format((float)$eco_bag_price * $tax * $item_data['quantity'], 2, '.', ''));
            }
        }

        $order->calculate_totals();
        $order->save();
    }
    
    /**
     * Display pick page info.
     *
     * @since 1.0.0
     * @return void
     */
    public function display_pick_pack_info()
    {
        if (!is_ajax()) {
            if (!$this->plugin_is_setup()) {
                return;
            }

            if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $text = esc_html__('Pick Pack bag option only available in Canada and depending on the products being bought', 'pick-pack');
            echo '<div class="pick-pack-info"><p>' . $text . '</p></div>';

            if (isset($_SESSION['pick_pack_product_added'])) {
                echo '<div id="state-tax-info-container"><p id="state-tax-info"></p></div>';
            }
        }
    }

    /**
     * Display checkout popup.
     *
     * @since 1.0.0
     * @return void
     **/
    public function checkout_popup()
    {
        if (!$this->plugin_is_setup()) {
            return;
        }

        $split_payment = get_option('pick_pack_split_payment');
        $popup_text = get_option('pick_pack_popup_text', '');
        $popup_header = get_option('pick_pack_popup_header', '');

        include_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/checkout-popup.php';
    }
    
    /**
     * Straight to checkout check.
     *
     * @since 1.0.0
     * @return void
     */
    public function straight_to_checkout_check()
    {
	if (!$this->plugin_is_setup()) {
	    return;
	}
	
        if (!is_admin()) {
            if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (is_cart()) {
                if (isset($_SESSION['straight_to_checkout'])) {
                    unset($_SESSION['straight_to_checkout']);
                }
            } else {
                if (!is_checkout()) {
                    if (!isset($_SESSION['straight_to_checkout'])) {
                        $_SESSION['straight_to_checkout'] = true;
                    }
                }
            }
        }
    }

    /**
     * Eco bag checkout popup script.
     *
     * @since 1.0.0
     * @return array Fragments.
     */
    public function eco_bag_checkout_popup_script($fragments)
    {
        if (!$this->plugin_is_setup()) {
            return $fragments;
        }

        if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['straight_to_checkout'])) {
            parse_str($_POST['post_data'], $post_data);
            $product_id = get_option('pick_pack_product');

            if ($post_data['billing_country'] === 'CA') {
                if (!$this->pick_pack_woo_in_cart($product_id) && $this->eco_bag_eligible()) {
                    unset($_SESSION['straight_to_checkout']);
                    $fragments['straight_to_checkout_popup'] = true;
                }
            }
        }

        return $fragments;
    }

    /**
     * Check if a product is eco bag eligible.
     *
     * @since 1.0.0
     * @return bool Return true if eligible.
     */
    public function eco_bag_eligible()
    {
        $fragile_large_array = $this->get_fragile_large_products();
        $fragile_large_count = count($fragile_large_array['fragile']) + count($fragile_large_array['large']);

        $eligible = true;
        $product_id = get_option('pick_pack_product');

        if ($this->pick_pack_woo_in_cart($product_id)) {
            $pick_pack = 1;
        } else {
            $pick_pack = 0;
        }

        $cart_count = count(WC()->cart->get_cart());

        if ($pick_pack && $cart_count == 1) {
            return false;
        }

        // If only large or fragile products are present
        if ($cart_count == ($fragile_large_count + $pick_pack)) {
            return false;
        }

	// Calculate points
        $points = $this->get_eco_bag_quantity(WC()->cart, false);
	
        // Points less than 5
        if ($fragile_large_count > 0 && $points < 5) {
            return false;
        }
	
        // Points less than 3 without fragile and large items
        if ($fragile_large_count == 0 && $points < 3) {
            return false;
        }

	// Is elligible
        return true;
    }

    /**
     * Get fragile and large products.
     *
     * @since 1.0.0
     * @return array The list of fragile and large products.
     */
    public function get_fragile_large_products()
    {
        $fragile = [];
        $large = [];
	
        //Find out if there is a fragile or large product
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id_2 = $cart_item['product_id'];
	    
            $terms = get_the_terms($product_id_2, 'product_cat');
            if ($terms != false && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    if ($term->name == "Fragile Product") {
                        $fragile[] = sprintf(esc_attr__('%s is a fragile product', 'pick-pack'), $cart_item['data']->get_name());
                    }
                    if ($term->name == "Large Product") {
                        $large[] = sprintf(esc_attr__('%s is a large product', 'pick-pack'), $cart_item['data']->get_name());
                    }
                }
            }
        }

        return ['fragile' => $fragile, 'large' => $large];
    }

    /**
     * Return from admin register company details form.
     *
     * @since 1.0.0
     * @return void
     */
    public function return_from_company_details()
    {
        if (isset($_GET['company_details'])) {
            if (isset($_GET['token'])) {
                update_option('pick_pack_temp_ecobag_token', $_GET['token']);
            }

            wp_redirect(get_dashboard_url() . 'admin.php?page=pick-pack&company_details=true');
	    exit;
        }
    }
    
    /**
     * CURL webhook receive.
     *
     * @since 1.0.0
     * @return void
     */
    public function curl_webhook_receive()
    {
        if (isset($_GET['request']) && $_GET['request'] === 'curl' && PICK_PACK_SERVER . 'dashboard/update.php' === $_SERVER['HTTP_REFERER']) {
            $split_payment = get_option('pick_pack_split_payment');
            $_product = wc_get_product(get_option('pick_pack_product'));

            if ($_product !== null && $_product !== false && $split_payment) {
                update_option('pick_pack_ecobag_price', $_GET['price']);
                $_product->set_regular_price($_GET['price']);
                $_product->save();
                $new_price = $_product->get_regular_price();
                echo wp_json_encode(['message' => "success", "new_price" => $new_price]);
            } else {
                echo wp_json_encode(['message' => "failure", "new_price" => '']);
            }
            wp_die();
        }
    }

    /**
     * CURL eco bag orders.
     *
     * @since 1.0.0
     * @return void
     */
    public function curl_eco_bag_orders()
    {
        if (isset($_GET['request']) && $_GET['request'] === 'curl' && $_GET['type'] === 'orders' && PICK_PACK_SERVER . 'dashboard/cronjob/cronjob_realtime.php' === $_SERVER['HTTP_REFERER']) {
            echo 'OK';
            wp_die();
        }
    }
    
}
