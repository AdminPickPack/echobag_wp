<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       pick-pack.ca
 * @since      1.0.0
 *
 * @package    Pick_Pack
 * @subpackage Pick_Pack/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pick_Pack
 * @subpackage Pick_Pack/admin
 * @author     Pick Pack <admin@pick-pack.ca>
 */
class Pick_Pack_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * A shared variable. Multiple categories products
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $choosen_products;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pick_Pack_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pick_Pack_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pick-pack-admin.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . "-font-icon", 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . "-bootstrap-css", 'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		wp_enqueue_media();
		
		wp_enqueue_script($this->plugin_name . "-bootstrap-js-2", 'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pick-pack-admin.js', array('jquery'), $this->version, false);

		$taxonomy = 'product_cat';
		$categories_all = get_categories(array('taxonomy' => $taxonomy, 'hide_empty' => false));
		$plugin_status_array = $this->get_plugin_status();

		//Remove fragile and large from list
		foreach ($categories_all as $key => $category) {

			if ($category->name == "Large Product" || $category->name == "Fragile Product") {
				unset($categories_all[$key]);
			}
		}

		$split_payment = get_option('eco_bag_split_payment');

		$jsarray = array(
			'categories_all' => $categories_all,
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('_wpnonce'),
			'split_payment' => $split_payment,
			'plugin_status_array' => $plugin_status_array
		);

		wp_localize_script($this->plugin_name, 'php_vars', $jsarray);
	}

	/**
	 * Check If woocommerce plugin is active or not
	 */
	public function pick_pack_check_woocommerce_is_active()
	{

		if (!function_exists('get_plugins')) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// Get Graphina animation lite version basename
		$basename = '';
		$plugins = get_plugins();

		foreach ($plugins as $key => $data) {
			if ($data['TextDomain'] === "woocommerce") {
				$basename = $key;
			}
		}


		if (!is_plugin_active($basename)) {

			$plugin = $basename;

			$activation_url = esc_url(wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin));

			$message = '<strong>' . esc_html__(' Pick Pack ', 'pick-pack') . '</strong>' . esc_html__(' requires ', 'pick-pack') . '<strong>' . esc_html__(' WooCommerce') . '</strong>' . esc_html__(' plugin to be active. Please activate WooCommerce for Pick Pack to continue.', 'pick-pack');

			$button_text = esc_html__(' Activate WooCommerce ', 'pick-pack');

			$button = "<p><a href='{$activation_url}' class='button-primary'>{$button_text}</a></p>";

			printf('<div class="error"><p>%1$s</p>%2$s</div>', __($message), $button);

			if (isset($_GET['activate'])) unset($_GET['activate']);
			deactivate_plugins(PICK_PACK_ROOT);
		} else {

			$this->pick_pack_create_product();
		}
	}

	/**
	 * Create Pick Pack Product
	 */
	public function pick_pack_create_product()
	{

		$id = get_option('pick_pack_product');
		$split_payment = get_option('eco_bag_split_payment');
		$eco_bag_price = false;

		if ($split_payment) {
			$eco_bag_price = get_option('eco_bag_price');
		} else {
			$eco_bag_price = get_option('eco_bag_default_price');
		}

		if (empty($id)) {

			$post_args = array(
				'post_title' => esc_html__('Pick Pack', 'pick-pack'),
				'post_type' => 'product',
				'post_status' => 'publish'
			);

			$post_id = wp_insert_post($post_args);

			if (!empty($post_id)) {

				update_option("pick_pack_product", $post_id);

				if (empty($eco_bag_price)) {
					$eco_bag_price = 3;
				}

				if (function_exists('wc_get_product')) {
					$product = wc_get_product($post_id);
					$product->set_sku('pick-pack-' . $post_id);
					$product->set_regular_price($eco_bag_price);
					$product->save();
				}
			}
		}
	}

	/**
	 * Add Admin menu
	 */
	public function pick_pack_add_admin_menu()
	{
		add_menu_page(
			__('Pick Pack', 'textdomain'),
			__('Pick Pack', 'textdomain'),
			'manage_options',
			'pick-pack',
			array($this, 'pick_pack_package_admin_menu_function'),
			plugins_url('pick-pack/assets/images/pick-pack-logo.png')
		);

		add_submenu_page('pick-pack', 'Pick Pack Orders', 'Orders', 'manage_options', 'edit.php?post_type=pickpackorders');

	}

	public function get_plugin_status()
	{

		$eco_bag_token = get_option('eco_bag_token');
		$temp_eco_bag_token = get_option('temp_eco_bag_token');
		$split_payment = get_option('eco_bag_split_payment');
		$stock = (int) get_option('eco_bag_stock');

		$status_array = [
			'split_payment_complete' => false, 'split_payment_no_payment_method' => false,
			'split_payment_both_left' => false, 'default_payment_complete' => false,
			'default_payment_incomplete' => false, 'stock_empty' => false
		];

		if ($split_payment) {
			if ($eco_bag_token) {
				$status_array['split_payment_complete'] = true;
			} elseif ($temp_eco_bag_token) {
				$status_array['split_payment_no_payment_method'] = true;
			} else {
				$status_array['split_payment_both_left'] = true;
			}
		} elseif ($temp_eco_bag_token) {
			$status_array['default_payment_complete'] = true;
		} else {
			$status_array['default_payment_incomplete']  = true;
		}

		if ($stock === 0) {
			$status_array['stock_empty'] = true;
		}

		return $status_array;
	}

	public function pick_pack_package_admin_menu_function()
	{

		if (!function_exists('get_plugins')) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// This check for woocommerce is not needed, the pick plugin would already be deactivated and this hook won't be called
		$basename = '';
		$plugins = get_plugins();

		foreach ($plugins as $key => $data) {
			if ($data['TextDomain'] === "woocommerce") {
				$basename = $key;
			}
		}


		if (is_plugin_active($basename)) {

			$eco_bag_price = get_option('eco_bag_price', 3);
			$eco_bag_default_price = get_option('eco_bag_default_price', 3);
			$eco_bag_token = get_option("eco_bag_token", false);
			$pick_pack_token = get_option("temp_eco_bag_token", false);
			$taxonomy = 'product_cat';
			$categories = get_categories(array('taxonomy' => $taxonomy, 'hide_empty' => false));
			$popup_text = get_option('popup_text', '');
			$popup_header = get_option('popup_header', '');
			$stock_quantity = get_option('eco_bag_stock', -1);


			//Remove fragile and large from list
			foreach ($categories as $key => $category) {

				if ($category->name == "Large Product" || $category->name == "Fragile Product") {
					unset($categories[$key]);
				}
			}
			// Retrieve the option values from database
			$category_array = [];

			foreach ($categories as $category) {

				$category_array[] = array('category_id' => $category->term_id, 'category_value' => get_option('product_per_bag_' . $category->term_id), 'category_name' => $category->name);
			}

			$args = array(
				'category' => array('Large Product'),
				'orderby'  => 'name',
			);
			$products = wc_get_products($args);

			$args_2 = array(
				'category' => array('Fragile Product'),
				'orderby'  => 'name',
			);
			$products_2 = wc_get_products($args_2);

			$eco_bags_sold = get_option('eco_bags_sold', 0);
			$eco_bags_sold_display = '';
			$counter = 0;

			if (empty($eco_bags_sold)) {
				$eco_bags_sold_display = '<p>No ecobags orders currently</p>';
			} else {
				foreach ($eco_bags_sold as $index => $array) {

					$eco_bags_sold_display .= '<p>' . $index . '. Price: ' . $array['price'];

					$eco_bags_sold_display .= ' Quantity: ' . $array['quantity'] . '<p>';

					$counter++;
				}
			}

			$args = array(
				'limit' => -1,
				'orderby'  => 'name',
			);
			$products_3 = wc_get_products($args);
			$multiple_categories_products = array();
			$choosen_products = array();

			foreach ($products_3 as $product) {

				$product_name = $product->get_name();

				if ($product_name == "Pick Pack") {
					continue;
				}

				$category_choosen = get_post_meta($product->get_id(), 'category_selected', true);
				if ($category_choosen) {
					$choosen_products[] = ['id' => $product->get_id(), 'name' => $product->get_name()];
				}
				$terms = get_the_terms($product->get_id(), 'product_cat');

				if (count($terms) > 1) {
					$multiple_categories_products[$product->get_id()] = $product->get_name();

					foreach ($terms as $term) {

						if ($term->name == "Large Product" || $term->name == "Fragile Product") {

							unset($multiple_categories_products[$product->get_id()]);
							break;
						}
					}
				}
			}

			require_once(plugin_dir_path(__FILE__) . 'partials/pick-pack-admin-integration.php');
		} else {
			echo 'Please install WooCommerce to use Pick Pack Plugin';
		}
	}

	/**
	 * Add the fragile and large category
	 */
	public function add_two_categories()
	{

		if (!function_exists('get_plugins')) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// Get Graphina animation lite version basename
		$basename = '';
		$plugins = get_plugins();

		foreach ($plugins as $key => $data) {
			if ($data['TextDomain'] === "woocommerce") {
				$basename = $key;
			}
		}


		if (is_plugin_active($basename)) {

			$this->custom_post_type();

			//Get all product categories
			$taxonomy = 'product_cat';
			$categories = get_categories(array('taxonomy' => $taxonomy, 'hide_empty' => false));
			$count = 0;

			//Check if fragile and large category present
			foreach ($categories as $category) {

				if ($category->name == "Large Product" || $category->name == "Fragile Product") {
					$count++;
				}
			}

			//Create categories if they do not exist
			if ($count != 2) {
				$category_id =  wp_insert_term('Large Product', $taxonomy, array('description' => "A large product not available with eco bag"));
				$category_id_2 = wp_insert_term('Fragile Product', $taxonomy, array('description' => "A Fragile product not available with eco bag"));


				if (is_wp_error($category_id_2) || is_wp_error($category_id)) {
					printf('<div class="error"><p>Could not create the categories</p></div>');
				}
			}
		}
	}

	/**
	 * Post request from payment method registration and update request
	 */
	public function pick_pack_payment()
	{

		wp_verify_nonce($_POST['_wpnonce'], 'my-nonce');

		$token = get_option('temp_eco_bag_token');

		if ($token === false) {
			$location = add_query_arg('company_register', 'false', admin_url('/admin.php?page=pick-pack'));
			wp_redirect($location);
			exit;
		}

		$return_url = get_home_url();

		if ($_POST['token_update'] === 'true') {
			$URL = SERVER_URL . 'index.php?eco_bag_token=' . $token . '&return_url=' . urlencode($return_url) . '&action=update';
		} else {
			$URL = SERVER_URL . 'index.php?eco_bag_token=' . $token . '&return_url=' . urlencode($return_url);
		}


		header('Location: ' . $URL);
	}

	public function custom_post_type()
	{

		register_post_type(
			'pickpackorders',
			// CPT Options
			array(
				'labels' => array(
					'name' => __('Pick Pack Orders'),
					'singular_name' => __('Pick Pack Order')
				),
				'capability_type' => 'post',
				'supports' => array(
					''

				),
				'capabilities' => array(
					'create_posts' => 'do_not_allow', // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
				),
				'map_meta_cap' => true,
				'public' => true,
				'has_archive' => false,
				'rewrite' => array('slug' => 'pick-pack-orders'),
				'show_in_menu' => 'edit.php?post_type=pickpackorders'
			)
		);
	}

	public function add_custom_columns_orders_admin($columns)
	{
		return array_merge($columns, ['price' => __('Price', 'textdomain'), 'pick_pack_bags_sold' => __('Pick Pack Bags Sold', 'textdomain')]);
	}

	public function fill_custom_columns_orders_admin($column_key, $post_id)
	{

		if ($column_key == 'price') {

			$price = get_post_meta($post_id, 'price', true);

			if (!$price || $price == '') {

				echo '<span style="color:red;">';
				_e('Not available', 'textdomain');
				echo '</span>';
			} else {

				echo '<span style="color:green;">';
				_e($price, 'textdomain');
				echo '</span>';
			}
		}

		if ($column_key == 'pick_pack_bags_sold') {

			$eco_bags_sold = get_post_meta($post_id, 'quantity', true);

			if (!$eco_bags_sold || $eco_bags_sold == '') {

				echo '<span style="color:red;">';
				_e('Not available', 'textdomain');
				echo '</span>';
			} else {

				echo '<span style="color:green;">';
				_e($eco_bags_sold, 'textdomain');
				echo '</span>';
			}
		}
	}

	public function multiple_categories_products_callback()
	{

		if ($_SERVER["REQUEST_METHOD"] == "POST") {


			foreach ($_POST['category_selected'] as $key => $value) {

				update_post_meta($key, 'category_selected', (int) $value[0]);

				# code...
			}
		}



		require_once(plugin_dir_path(__FILE__) . 'partials/multiple-categories-products-page.php');
	}

	public function category_form_handler()
	{

		wp_verify_nonce($_POST['_wpnonce'], 'my-nonce');
		$term_ids_updated = [];


		foreach ($_POST['categories']['name'] as $key => $term_id) {
			if ($term_id != '' && $_POST['categories']['points'][$key] != '') {

				update_option('product_per_bag_' . $term_id, $_POST['categories']['points'][$key]);
				array_push($term_ids_updated, $term_id);
			}
		}

		$taxonomy = 'product_cat';
		$categories_all = get_categories(array('taxonomy' => $taxonomy, 'hide_empty' => false));

		foreach ($categories_all as $key => $category) {

			if ($category->name != "Large Product" && $category->name != "Fragile Product" && !in_array($category->term_id, $term_ids_updated)) {
				if (get_option('product_per_bag_' . $category->term_id) != false) {
					delete_option('product_per_bag_' . $category->term_id);
				}
			}
		}

		wp_redirect(esc_url(admin_url('/admin.php?page=pick-pack&categories-updated=1')));
		exit;
	}

	public function admin_notice_company_register_false()
	{

		global $pagenow;

		if ($pagenow == 'admin.php'  && isset($_GET['page']) && $_GET['page'] == 'pick-pack' && isset($_GET['company_register']) && $_GET['company_register'] == 'false') {
?>

			<div class="updated">
				<p><?php esc_html_e('Company is not registered with the pick pack system', 'pick-pack'); ?></p>
			</div>
<?php }
	}

	public function get_multiple_categories_product_terms_handler()
	{

		$nonce = check_ajax_referer('_wpnonce', 'security');

		$product_id = $_POST['product_id'];

		$status = ["status" => false, "terms" => "false"];

		if (!empty($product_id)) {

			$terms = get_the_terms($product_id, 'product_cat');

			if (!empty($terms)) {
				$status['status'] = true;
				$status['terms'] = $terms;
			}
		}

		echo json_encode($status, true);
		wp_die();
	}

	public function multiple_category_product_form_handler()
	{

		if ($_SERVER["REQUEST_METHOD"] == "POST") {

			wp_verify_nonce($_POST['_wpnonce'], 'my-nonce');
			$products_ids_updated = [];

			foreach ($_POST['choosen-category'] as $key => $value) {

				update_post_meta($key, 'category_selected', (int) $value);

				array_push($products_ids_updated, $key);
			}

			$multiple_categories_products_all = $this->get_products_multiple_categories();

			foreach ($multiple_categories_products_all as $product) {

				if (!in_array($product->get_id(), $products_ids_updated)) {
					if (get_post_meta($product->get_id(), 'category_selected', true)) {
						delete_post_meta($product->get_id(), 'category_selected');
					}
				}
			}



			wp_redirect(esc_url(admin_url('/admin.php?page=pick-pack&multiple-categories-products-updated=1')));
			exit;
		}
	}

	public function get_products_multiple_categories()
	{


		$args = array(
			'limit' => -1,
			'orderby'  => 'name',
		);
		$products = wc_get_products($args);
		$multiple_categories_products = array();

		foreach ($products as $product) {

			$product_name = $product->get_name();
			if ($product_name == "Pick Pack") {
				continue;
			}
			$terms = get_the_terms($product->get_id(), 'product_cat');

			if (count($terms) > 1) {
				$multiple_categories_products[$product->get_id()] = $product;

				foreach ($terms as $term) {

					if ($term->name == "Large Product" || $term->name == "Fragile Product") {

						unset($multiple_categories_products[$product->get_id()]);
						break;
					}
					# code...
				}
			}
		}

		return $multiple_categories_products;
	}

	public function change_split_payment_handler()
	{

		$nonce = check_ajax_referer('_wpnonce', 'security');

		$id = get_option('pick_pack_product');
		$split_payment = $_POST['split_payment'];
		$eco_bag_price = ($split_payment) ? get_option('eco_bag_price') : get_option('eco_bag_default_price');

		$status = ["status" => false, "split_payment" => false, 'plugin_status_array' => []];

		if ($split_payment == 'true') {

			update_option('eco_bag_split_payment', true);
			$status['split_payment'] = true;
			$status['status'] = true;
		} else {
			update_option('eco_bag_split_payment', false);
			$status['split_payment'] = false;
			$status['status'] = true;
		}

		if ($eco_bag_price !== false) {
			if (!empty($id)) {

				$product = wc_get_product($id);
				$product->set_regular_price($eco_bag_price);
				$product->save();
			}
		}

		$status['plugin_status_array'] = $this->get_plugin_status();



		echo json_encode($status, true);
		wp_die();
	}

	public function popup_text_form_handler()
	{
		wp_verify_nonce($_POST['_wpnonce'], 'my-nonce');

		$popup_text = $_POST['popup-text'];
		$popup_header = $_POST['popup-header'];

		if (isset($popup_text)) {
			update_option('popup_text', $popup_text);
		}

		if (isset($popup_text)) {
			update_option('popup_header', $popup_header);
		}

		wp_redirect(esc_url(admin_url('/admin.php?page=pick-pack&popup_text_updated=1')));
		exit;
	}

	public function stock_form_handler()
	{
		wp_verify_nonce($_POST['_wpnonce'], 'my-nonce');

		$stock_quantity = (int) $_POST['stock-quantity'];

		if (isset($stock_quantity) && $stock_quantity >= 0) {
			update_option('eco_bag_stock', $stock_quantity);
		}

		wp_redirect(esc_url(admin_url('/admin.php?page=pick-pack&popup_text_updated=1')));
		exit;
	}

	public function default_price_form_handler()
	{
		wp_verify_nonce($_POST['_wpnonce'], 'my-nonce');

		$default_price = $_POST['default-price'];
		$id = get_option('pick_pack_product');

		if (isset($default_price) && $default_price >= 0) {
			update_option('eco_bag_default_price', $default_price);

			if (!empty($id)) {

				$product = wc_get_product($id);
				$product->set_regular_price($default_price);
				$product->save();
			}
		}

		wp_redirect(esc_url(admin_url('/admin.php?page=pick-pack&price_updated=1')));
		exit;
	}
}
