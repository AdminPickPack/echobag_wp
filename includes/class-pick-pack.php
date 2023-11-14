<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       pick-pack.ca
 * @since      1.0.0
 *
 * @package    Pick_Pack
 * @subpackage Pick_Pack/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Pick_Pack
 * @subpackage Pick_Pack/includes
 * @author     Pick Pack <admin@pick-pack.ca>
 */
class Pick_Pack
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Pick_Pack_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('PICK_PACK_VERSION')) {
			$this->version = PICK_PACK_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'pick-pack';

		$this->load_dependencies();
		$this->set_locale();
		$this->cronjob();
		$this->cronjob_schedules();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		/*wp_clear_scheduled_hook( 'monthly_charge_cronjob_action' );*/
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Pick_Pack_Loader. Orchestrates the hooks of the plugin.
	 * - Pick_Pack_i18n. Defines internationalization functionality.
	 * - Pick_Pack_Admin. Defines all hooks for the admin area.
	 * - Pick_Pack_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pick-pack-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pick-pack-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-pick-pack-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-pick-pack-public.php';

		$this->loader = new Pick_Pack_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Pick_Pack_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Pick_Pack_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Set up the cronjob
	 * @since    1.0.0
	 * @access   private
	 */
	private function cronjob()
	{

		$this->loader->add_action('init', $this, 'register_monthly_charge');
		$this->loader->add_action('monthly_charge_cronjob_action', $this, 'cronjob_function');
	}

	/**
	 * Set up monthly cronjob schedule
	 * @since    1.0.0
	 * @access   private
	 */
	private function cronjob_schedules()
	{

		$this->loader->add_filter('cron_schedules', $this, 'cron_add_monthly');
	}

	function cron_add_monthly($schedules)
	{
		// Adds once weekly to the existing schedules.
		$schedules['Monthly'] = array(
			'interval' => MONTH_IN_SECONDS,
			'display' => __('Once Monthly')
		);
		return $schedules;
	}


	/**
	 * Set up the cronjob
	 * @since    1.0.0
	 * @access   private
	 */
	public function register_monthly_charge()
	{

		if (!wp_next_scheduled('monthly_charge_cronjob_action') && !is_bool(get_option('eco_bag_token'))) {
			wp_schedule_event(time() + 60, 'Monthly', 'monthly_charge_cronjob_action');
		}
	}

	/**
	 * Function that runs on cronjob
	 * @since    1.0.0
	 * @access   private
	 */
	public function cronjob_function()
	{

		$eco_bags_sold_array = get_option('eco_bags_sold', array());
		$eco_bag_token = get_option('eco_bag_token');

		if (!empty($eco_bags_sold_array) && !is_bool($eco_bag_token)) {

			$request = new WP_Http();
			$eco_bags_sold = 0;

			foreach ($eco_bags_sold_array as $order) {
				$order['price'] = $order['price'] + $order['price'] * 0.05 + $order['price'] * 0.09975;
				$eco_bags_sold += $order['price'] * $order['quantity'];
			}
			// eco_bags_sold is the total amount to be charged
			$body = array('eco_bags_sold' => $eco_bags_sold, 'eco_bag_token' => $eco_bag_token);
			$url = SERVER_URL . 'cronjob.php';
			$response = $request->get($url, array('body' => $body));

			if (isset($response->errors)) {

				return false;
			}

			if ($response['response']['code'] === 200) {
				$response_body = $response['body'];
				if ($response_body == 'true') {
					update_option('eco_bags_sold', array());
				}
			}
		}
	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Pick_Pack_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_init', $plugin_admin, 'pick_pack_check_woocommerce_is_active');
		$this->loader->add_action('admin_init', $plugin_admin, 'add_two_categories');
		$this->loader->add_action('admin_menu', $plugin_admin, 'pick_pack_add_admin_menu');
		$this->loader->add_action('admin_post_pick_pack_payment', $plugin_admin, 'pick_pack_payment');
		$this->loader->add_action('init', $plugin_admin, 'custom_post_type');
		$this->loader->add_filter('manage_pickpackorders_posts_columns', $plugin_admin, 'add_custom_columns_orders_admin');
		$this->loader->add_action('manage_pickpackorders_posts_custom_column', $plugin_admin, 'fill_custom_columns_orders_admin', 10, 2);
		$this->loader->add_action('admin_post_category_form_submission', $plugin_admin, 'category_form_handler');
		$this->loader->add_action('admin_notices', $plugin_admin, 'admin_notice_company_register_false');
		$this->loader->add_action('wp_ajax_get_multiple_categories_product_terms', $plugin_admin, 'get_multiple_categories_product_terms_handler');
		$this->loader->add_action('wp_ajax_nopriv_wp_ajax_get_multiple_categories_product_terms', $plugin_admin, 'get_multiple_categories_product_terms_handler');
		$this->loader->add_action('admin_post_category_multiple_form_submission', $plugin_admin, 'multiple_category_product_form_handler');
		$this->loader->add_action('wp_ajax_change_split_payment', $plugin_admin, 'change_split_payment_handler');
		$this->loader->add_action('wp_ajax_nopriv_wp_ajax_change_split_payment', $plugin_admin, 'change_split_payment_handler');
		$this->loader->add_action('admin_post_popup_text_form_submission', $plugin_admin, 'popup_text_form_handler');
		$this->loader->add_action('admin_post_stock_form_submission', $plugin_admin, 'stock_form_handler');
		$this->loader->add_action('admin_post_default_price_form_submission', $plugin_admin, 'default_price_form_handler');
		/*$this->loader->add_action('admin_post_nopriv_pick_pack_payment', $plugin_admin, 'pick_pack_payment');*/
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Pick_Pack_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('pre_get_posts', $plugin_public, 'remove_bag_from_query', 20);
		$this->loader->add_filter('woocommerce_related_products', $plugin_public, 'filter_bag_from_related_products', 10, 3);
		$this->loader->add_filter('woocommerce_update_order_review_fragments', $plugin_public, 'eco_bag_checkout_popup_script', 10);
		$this->loader->add_action('woocommerce_checkout_order_processed', $plugin_public, 'checkout_pick_pack_add_tax_order', 10, 3);
		$this->loader->add_action('woocommerce_checkout_order_processed', $plugin_public, 'order_payment_complete');
		$this->loader->add_action('woocommerce_before_calculate_totals', $plugin_public, 'change_cart_item_quantities', 20, 1);
		$this->loader->add_action('woocommerce_before_cart_table', $plugin_public, 'pick_pack_add_model', 10);
		$this->loader->add_action('woocommerce_cart_item_removed', $plugin_public, 'pick_pack_remove_item_from_cart', 11, 2);
		$this->loader->add_action('wp_ajax_pick_pack_add_to_cart_product', $plugin_public, 'pick_pack_add_to_cart_product_callback');
		$this->loader->add_action('wp_ajax_nopriv_pick_pack_add_to_cart_product', $plugin_public, 'pick_pack_add_to_cart_product_callback');
		$this->loader->add_action('template_redirect', $plugin_public, 'return_from_payment_method', 20);
		$this->loader->add_action('init', $plugin_public, 'curl_webhook_receive', 20);
		$this->loader->add_action('init', $plugin_public, 'curl_eco_bag_orders', 20);
		$this->loader->add_action('wp', $plugin_public, 'straight_to_checkout_check');
		$this->loader->add_action('woocommerce_checkout_update_order_review', $plugin_public, 'checkout_page_pick_pack_tax', 20);
		$this->loader->add_action('woocommerce_checkout_update_order_review', $plugin_public, 'country_option_checkout_page', 10);
		/*$this->loader->add_action( 'woocommerce_checkout_update_order_review', $plugin_public, 'change_pick_pack_info', 30 );*/
		$this->loader->add_action('woocommerce_review_order_after_cart_contents', $plugin_public, 'display_pick_pack_info', 20);
		$this->loader->add_action('template_redirect', $plugin_public, 'return_from_company_details', 20);
		$this->loader->add_action('woocommerce_before_checkout_form', $plugin_public, 'checkout_popup', 20);
		//combine the update order action functions handler into one


	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Pick_Pack_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
