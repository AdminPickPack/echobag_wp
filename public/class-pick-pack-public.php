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
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pick-pack-public.js', array('jquery'), $this->version, false);
	
        $js_vars = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('_wpnonce')
        );
        wp_localize_script($this->plugin_name, 'pickpack_vars', $js_vars);
    }

    /**
     * Check if pick-pack plugin is setup or not.
     *
     * @since 1.0.0
     * @return bool Return true if pick-pack plugin is setup.
     */
    public function plugin_is_setup()
    {
	$product_id = get_option('pick_pack_product_id');
	$product_price = get_option('pick_pack_product_price');
	$product_stock = get_option('pick_pack_product_stock');
	
	return (empty($product_id) || empty($product_price) || empty($product_stock)) ? false : true;
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
	    $pickpack_product_id = get_option('pick_pack_product_id');
            if (!empty($pickpack_product_id)) {
                $query->set('post__not_in', array( $pickpack_product_id ));
            }
        }
    }
    
    /**
     * Remove ecobag product from related products.
     *
     * @since 1.0.0
     * @return array The related products result
     */
    public function remove_bag_from_related_products($related_posts, $product_id, $args)
    {
        $pickpack_product_id = get_option('pick_pack_product_id');
        $exclude_ids = !empty($pickpack_product_id) ? array( $pickpack_product_id ) : array();
        return array_diff($related_posts, $exclude_ids);
    }
    
    /**
     * Get default pick pack options.
     *
     * @since 1.0.0
     * @return array The pick pack options.
     */
    private function get_default_pickpack_options() {
	static $options;
	if (!empty($options)) {
	    return $options;
	}
	
	$is_exclusive = get_option('pick_pack_product_exclusive', false);
	
	$product_type = $is_exclusive ? 'disable': 'enable';
	$product_points = get_option('pick_pack_product_default_points', PICK_PACK_DEFAULT_PRODUCT_POINTS);
	
	$options = array( 'product_type' => $product_type,
			  'product_points' => $product_points );
	return $options;
    }
    
    /**
     * Get category pick pack options.
     *
     * @since 1.0.0
     * @return array The pick pack options.
     */
    private function get_wc_category_pickpack_options($category_id) {
	static $options = array();
	if (!empty($options[$category_id])) {
	    return $options[$category_id];
	}
	
	$default_options = $this->get_default_pickpack_options();
	
	$product_type = get_term_meta($category_id, 'pick_pack_product_type', true);
	if (empty($product_type) || $product_type == 'default') {
	    $product_type = $default_options['product_type'];
	}
	
	$product_points = get_term_meta($category_id, 'pick_pack_product_points', true);
	if (empty($product_points) || $product_points == 'default') {
	    $product_points = $default_options['product_points'];
	}
	
	$options[$category_id] = array( 'product_type' => $product_type,
					'product_points' => $product_points );
	return $options[$category_id];
    }

    /**
     * Get product pick pack options.
     *
     * @since 1.0.0
     * @return array The pick pack options.
     */
    private function get_wc_product_pickpack_options($product_id) {
	static $options = array();
	if (!empty($options[$product_id])) {
	    return $options[$product_id];
	}
	
	$product_type = get_post_meta($product_id, 'pick_pack_product_type', true);
	if (empty($product_type)) { $product_type = 'default'; }

	$product_points = get_post_meta($product_id, 'pick_pack_product_points', true);
	if (empty($product_points)) { $product_points = 'default'; }

	if ($product_type == 'default' || $product_points == 'default') {
	    $categories = get_the_terms($product_id, 'product_cat');
	    if (!empty($categories) && !is_wp_error($categories)) {
		foreach ($categories as $term) {
		    $category_id = $term->term_id;
		    $category_options = $this->get_wc_category_pickpack_options($category_id);
		    if ($product_type == 'default') {
			$product_type = $category_options['product_type'];
		    }
		    if ($product_points == 'default') {
			$product_points = $category_options['product_points'];
		    }
		}
	    }
	}

	if ($product_type == 'default' || $product_points == 'default') {
	    $default_options = $this->get_default_pickpack_options();
	    if ($product_type == 'default') {
		$product_type = $default_options['product_type'];
	    }
	    if ($product_points == 'default') {
		$product_points = $default_options['product_points'];
	    }
	}
	
	$options[$product_id] = array( 'product_type' => $product_type,
				       'product_points' => $product_points );
	return $options[$product_id];
    }
    
    /**
     * Count total points for products in woocommerce cart.
     *
     * @since 1.0.0
     * @return int The total number of points.
     */
    private function count_wc_cart_pick_pack_points()
    {
        $pickpack_product_id = get_option('pick_pack_product_id');
	
	$total_points = 0;
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id  = $cart_item['product_id'];
	    $product_qty = $cart_item['quantity'];
	    
	    if ($product_id == $pickpack_product_id) {
		continue;
	    }
	    
	    $pickpack_options = $this->get_wc_product_pickpack_options($product_id);
	    if ($pickpack_options['product_type'] != 'enable' || !$pickpack_options['product_points']) {
		continue;
	    }
	    
	    $pickpack_points = $pickpack_options['product_points'];
	    $product_points = intval($product_qty) * intval($pickpack_points);
	    $total_points += $product_points;
        }

	return $total_points;
    }

    /**
     * Count number of pick pack bags for products in woocommerce cart.
     *
     * @since 1.0.0
     * @return int The total quantity of pickpack bags.
     */
    private function count_pick_pack_quantity()
    {
	$pickpack_points   = $this->count_wc_cart_pick_pack_points();
	$pickpack_quantity = ceil($pickpack_points / 20);
	return $pickpack_quantity;
    }
    
    /**
     * Check pick-pack eco bag product in cart or not.
     *
     * @since 1.0.0
     * @return bool Return true if pick-pack eco bag is in cart.
     */
    private function pick_pack_product_in_cart()
    {
	$pickpack_product_id = get_option('pick_pack_product_id');
	if (empty($pickpack_product_id)) {
	    return false;
	}
	
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($pickpack_product_id == $cart_item['product_id']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check pick-pack eco bag product in cart and return item key.
     *
     * @since 1.0.0
     * @return mixed Return the pick pack product key in woocommerce cart.
     */
    private function pick_pack_wc_cart_product_key()
    {
	$pickpack_product_id = get_option('pick_pack_product_id');
	if (empty($pickpack_product_id)) {
	    return false;
	}
	
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($pickpack_product_id == $cart_item['product_id']) {
                return $cart_item_key;
            }
        }
        return null;
    }
    
    /**
     * Remove pick-pack eco bag product from cart.
     *
     * @since 1.0.0
     * @return bool Return true if pick-pack eco bag is in cart.
     */
    private function remove_pick_pack_from_cart() {
	$pickpack_product_id = get_option('pick_pack_product_id');
	if (empty($pickpack_product_id)) {
	    return false;
	}
	
	foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
	    if ($cart_item['product_id'] == $pickpack_product_id) {
		WC()->cart->remove_cart_item($cart_item_key);
		return true;
	    }
	}
	return false;
    }
    

    /**
     * Add pickpack product into cart. Handles the ajax request from the pop up modal.
     *
     * @since 1.0.0
     * @return void
     */
    public function pick_pack_add_to_cart_handler()
    {
        if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
            session_start();
        }
	
        $nonce = check_ajax_referer('_wpnonce', 'security');

        $pickpack_product_id = get_option('pick_pack_product_id');
	if (empty($pickpack_product_id)) {
	    wp_send_json_error('Missing pickpack product!', 404);
	    exit;
	}

	$pickpack_quantity = $this->count_pick_pack_quantity();
	if (empty($pickpack_quantity)) {
	    wp_send_json_error('No pickpack quantity for this order.', 403);
	    exit;
	}

        $pickpack_stock = intval(get_option('pick_pack_product_stock'));
	if (empty($pickpack_stock) || $pickpack_quantity > $pickpack_stock) {
	    wp_send_json_error('Not enough pickpack available.', 403);
	    exit;
	}
	
	$add = WC()->cart->add_to_cart($pickpack_product_id, $pickpack_quantity);
	if ($add) {
	    $_SESSION["pick_pack_product_added"] = $pickpack_product_id;
	    wp_send_json_success('Product added.');
	    exit;
	} 
	
	wp_send_json_error('Error.', 403);
	exit;
    }

    /**
     * Add pick-pack popup to woocommerce cart. Remove pick pack from products if no eligible products.
     *
     * @since 1.0.0
     * @return void
     */
    public function wc_cart_pick_pack_popup()
    {
        if (!$this->plugin_is_setup()) {
            return;
        }

	$pickpack_quantity = $this->count_pick_pack_quantity();
        $pickpack_stock = intval(get_option('pick_pack_product_stock'));
	
	if (!$pickpack_quantity || !$pickpack_stock || $pickpack_quantity > $pickpack_stock) {
	    $this->remove_pick_pack_from_cart();
	    return ;
	}
	
        $pickpack_product_id  = get_option('pick_pack_product_id');
	$pickpack_product_key = $this->pick_pack_wc_cart_product_key();
	
	include_once plugin_dir_path(__FILE__) . 'partials/cart.inc.php';
    }
    
    /**
     * Make pickpack product quantity in woocommerce cart readonly.
     *
     * @since 1.0.0
     * @return string The product quantity field.
     */
    public function wc_cart_pick_pack_readonly_quantity($product_quantity, $cart_item_key, $cart_item) 
    {
        $pickpack_product_id = get_option('pick_pack_product_id');
	if (!empty($pickpack_product_id) && $cart_item['product_id'] == $pickpack_product_id) {
	    $product_quantity = sprintf('<div class="pickpack-quantity-readonly">%s</div>', $cart_item['quantity']);
	}
	return $product_quantity;
    }
    
    /**
     * Custom product name for pickpack in woocommerce cart.
     *
     * @since 1.0.0
     * @return string The product name.
     */
    public function wc_cart_pick_pack_product_name($name, $cart_item, $cart_item_key) {
        $pickpack_product_id = get_option('pick_pack_product_id');
	if (!empty($pickpack_product_id) && $cart_item['product_id'] == $pickpack_product_id) {
	    $current_lang = '';
	    if (defined('ICL_LANGUAGE_CODE')) {
		$current_lang = ICL_LANGUAGE_CODE;
	    } elseif (function_exists('pll_current_language')) {
		$current_lang = pll_current_language();
	    }
	
	    if ($current_lang == 'en') {
		$name = get_option('pick_pack_product_name_en', PICK_PACK_DEFAULT_PRODUCT_NAME_EN);
	    } else {
		$name = get_option('pick_pack_product_name_fr', PICK_PACK_DEFAULT_PRODUCT_NAME_FR);
	    }
	    $name = esc_html($name);
	}
	return $name;
    }

    /**
     * Change cart item quantities handler. Update number of pickpack ecobags.
     *
     * @since 1.0.0
     * @return void
     */
    public function wc_cart_update_pick_pack_quantity($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return ;
        }
	
        if (did_action('woocommerce_before_calculate_totals') >= 2) {
            return ;
        }
	
        $pickpack_product_id = get_option('pick_pack_product_id');
	if (empty($pickpack_product_id)) {
	    return false;
	}

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = $cart_item['data']->get_id();
            if ($product_id == $pickpack_product_id) {
                $pickpack_quantity = $this->count_pick_pack_quantity();
                if (!empty($pickpack_quantity)) {
                    $cart->set_quantity($cart_item_key, $pickpack_quantity);
		} else {
                    WC()->cart->remove_cart_item($cart_item_key);
                    if (!empty($_SESSION["pick_pack_product_added"]) && $_SESSION["pick_pack_product_added"] == $pickpack_product_id) {
                        unset($_SESSION['pick_pack_product_added']);
                    }
                }
            }
        }
    }
    
    /**
     * Actions when an item is removed from cart.
     *
     * @since 1.0.0
     * @return void
     */
    public function wc_cart_remove_item_handler($cart_item_key, $cart)
    {
        if (is_checkout()) {
            return ;
        }
	
        if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
            session_start();
        }
	
        $pickpack_product_id = get_option('pick_pack_product_id');
	if (empty($pickpack_product_id)) {
	    return ;
	}
	
        $removed_item = $cart->removed_cart_contents[$cart_item_key];
        $removed_product_id = $removed_item['product_id'];
	
        if ($removed_product_id == $pickpack_product_id) {
            if (!empty($_SESSION["pick_pack_product_added"]) && $_SESSION["pick_pack_product_added"] == $pickpack_product_id) {
                unset($_SESSION['pick_pack_product_added']);
            }
        }
    }

    /**
     * Add pick-pack popup to woocommerce checkout. Remove pick pack from products if no eligible products.
     *
     * @since 1.0.0
     * @return void
     **/
    public function wc_checkout_pick_pack_popup()
    {
        if (!$this->plugin_is_setup()) {
            return;
        }
	
	$pickpack_quantity = $this->count_pick_pack_quantity();
        $pickpack_stock = intval(get_option('pick_pack_product_stock'));
	
	if (!$pickpack_quantity || !$pickpack_stock || $pickpack_quantity > $pickpack_stock) {
	    $this->remove_pick_pack_from_cart();
	    return ;
	}
	
        $pickpack_product_id  = get_option('pick_pack_product_id');
	$pickpack_product_key = $this->pick_pack_wc_cart_product_key();
	
        include_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/checkout.inc.php';
    }

    /**
     * Display pick page info in woocommerce checkout.
     *
     * @since 1.0.0
     * @return void
     */
    public function wc_checkout_display_pick_pack_info()
    {
	if (is_ajax() || !$this->plugin_is_setup()) {
	    return;
	}

	if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
	    session_start();
	}
	
	$text = esc_html__('Pick Pack option is only available in Canada and depending on the products being bought.', 'pick-pack');
	echo '<div class="pick-pack-info"><p>' . $text . '</p></div>';
    }
    
    /**
     * Country option in woocommerce checkout page.
     *
     * @since 1.0.0
     * @return void
     */
    public function wc_checkout_country_update()
    {
        if (!$this->plugin_is_setup()) {
            return ;
        }
	
        parse_str($_POST['post_data'], $post_data);
	
        if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
            session_start();
        }
	
        $pickpack_product_id = get_option('pick_pack_product_id');
	
        if ($post_data['billing_country'] === "CA") {
            if (!$this->pick_pack_product_in_cart() && !empty($_SESSION["pick_pack_product_added"]) && $_SESSION["pick_pack_product_added"] === $pickpack_product_id) {
		$pickpack_quantity = $this->count_pick_pack_quantity();
		if (!empty($pickpack_quantity)) {
		    WC()->cart->add_to_cart($pickpack_product_id, $pickpack_quantity);
		}
            }
        } elseif ($this->pick_pack_product_in_cart()) {
	    $this->remove_pick_pack_from_cart();
        }
    }

    /**
     * Actions on order processed.
     *
     * @since 1.0.0
     * @return bool Return true if successfull, false on errors.
     */
    public function wc_order_processed_handler($order_id)
    {
	$pickpack_product_id = get_option('pick_pack_product_id');
	if (empty($pickpack_product_id)) {
	    return ;
	}
	
        $order = wc_get_order($order_id);
        foreach ($order->get_items() as $item_id => $item) {
            $item_data = $item->get_data();
	    if ($item_data['product_id'] == $pickpack_product_id) {
		// Current order
		$pickpack_quantity = intval($item_data['quantity']);
		$pickpack_total = floatval($item_data['total']);
		$pickpack_price = floatval($pickpack_total / $pickpack_quantity);
		
		// Reduce pickpack stock
		$pickpack_stock = intval(get_option('pick_pack_product_stock'));
		$new_stock_quantity = intval($pickpack_stock - $pickpack_quantity);
		if ($new_stock_quantity < 0) { $new_stock_quantity = 0; }
		update_option('pick_pack_product_stock', $new_stock_quantity);

		// Store pickpack order
		$order_buyer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
		
		$order_data = array( 'order_id'   => $order_id,
				     'order_name' => $order_buyer_name,
				     'price'      => $pickpack_price,
				     'quantity'   => $pickpack_quantity );
		
		$post_data = array( 'post_type' => 'pickpack_orders',
				    'post_status' => 'publish',
				    'post_title' => sprintf(__('Order #%d', 'pick-pack'), $order_id),
				    'meta_input' => $order_data );
		
		$post_id = wp_insert_post($post_data);
            }
        }
    }
    
    /**
     * Straight to checkout check.
     *
     * @since 1.0.0
     * @return void
     */
    public function wc_straight_to_checkout_check()
    {
	if (!$this->plugin_is_setup()) {
	    return ;
	}
	
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

    /**
     * Straight to checkout handler.
     *
     * @since 1.0.0
     * @return array The woocommerce fragments.
     */
    public function wc_straight_to_checkout_handler($fragments)
    {
        if (!$this->plugin_is_setup()) {
            return $fragments;
        }
	
        if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
            session_start();
        }
	
        if (!empty($_SESSION['straight_to_checkout'])) {
            parse_str($_POST['post_data'], $post_data);
            if ($post_data['billing_country'] === "CA") {
		$pickpack_product_id = get_option('pick_pack_product_id');
		$pickpack_quantity = $this->count_pick_pack_quantity();
                if (!$this->pick_pack_product_in_cart() && !empty($pickpack_quantity)) {
                    unset($_SESSION['straight_to_checkout']);
                    $fragments['straight_to_checkout_popup'] = true;
                }
            }
        }
	
        return $fragments;
    }    
}
