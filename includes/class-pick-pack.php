<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @category Common
 * @package  Pick_Pack
 * @author   Pick Pack <admin@pick-pack.ca>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://pick-pack.ca/
 * @since    1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}

class Pick_Pack
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var Pick_Pack_Loader    $loader    Maintains and registers all hooks for the plugin.
     * 
     * @since  1.0.0
     * @access protected
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     * 
     * @var string    $plugin_name    The string used to uniquely identify this plugin.
     *
     * @since  1.0.0
     * @access protected
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->plugin_name = 'pick-pack';
    
        if (defined('PICK_PACK_VERSION')) {
            $this->version = PICK_PACK_VERSION;
        } else {
            $this->version = 'UNKNOWN';
        }

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since  1.0.0
     * @access public
     * 
     * @return string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since  1.0.0
     * @access public
     * 
     * @return Pick_Pack_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since  1.0.0
     * @access public
     * 
     * @return string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
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
     * @since  1.0.0
     * @access private
     * @return void
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pick-pack-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pick-pack-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-pick-pack-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'public/class-pick-pack-public.php';

        $this->loader = new Pick_Pack_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Pick_Pack_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since  1.0.0
     * @access private
     * @return void
     */
    private function set_locale()
    {
        $plugin_i18n = new Pick_Pack_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     * @return void
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Pick_Pack_Admin($this->get_plugin_name(), $this->get_version());

        // Init functions
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'init_woocommerce');
    
        // AJAX handlers
        $this->loader->add_action('wp_ajax_update_product', $plugin_admin, 'update_product_handler');
        $this->loader->add_action('wp_ajax_update_popup_fr', $plugin_admin, 'update_popup_fr_handler');
        $this->loader->add_action('wp_ajax_update_popup_en', $plugin_admin, 'update_popup_en_handler');
    
        // Custom pick pack orders post type
        $this->loader->add_action('init', $plugin_admin, 'custom_orders_post_type');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     * @return void
     */
    private function define_public_hooks()
    {
        $plugin_public = new Pick_Pack_Public($this->get_plugin_name(), $this->get_version());

        // Enqueue styles and scripts
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    
        // Hide pickpack product in front-end
        $this->loader->add_action('pre_get_posts', $plugin_public, 'remove_bag_from_query', 20);
        $this->loader->add_filter('woocommerce_related_products', $plugin_public, 'remove_bag_from_related_products', 10, 3);

        // Woocommerce cart
        $this->loader->add_action('woocommerce_after_cart_table', $plugin_public, 'wc_cart_pick_pack_popup', 10);
        $this->loader->add_action('woocommerce_before_calculate_totals', $plugin_public, 'wc_cart_update_pick_pack_quantity', 20, 1);
        $this->loader->add_filter('woocommerce_cart_item_name', $plugin_public, 'wc_cart_pick_pack_product_name', 10, 3);
        $this->loader->add_filter('woocommerce_cart_item_quantity', $plugin_public, 'wc_cart_pick_pack_readonly_quantity', 10, 3);
        $this->loader->add_action('woocommerce_cart_item_removed', $plugin_public, 'wc_cart_remove_item_handler', 11, 2);
    
        // Woocommerce checkout
        $this->loader->add_action('woocommerce_after_checkout_form', $plugin_public, 'wc_checkout_pick_pack_popup', 20);
        $this->loader->add_action('woocommerce_checkout_update_order_review', $plugin_public, 'wc_checkout_country_update', 10);
        $this->loader->add_action('woocommerce_review_order_after_cart_contents', $plugin_public, 'wc_checkout_display_pick_pack_info', 20);
        $this->loader->add_action('woocommerce_checkout_order_processed', $plugin_public, 'wc_order_processed_handler');
        $this->loader->add_action('wp', $plugin_public, 'wc_straight_to_checkout_check');
        $this->loader->add_filter('woocommerce_update_order_review_fragments', $plugin_public, 'wc_straight_to_checkout_handler', 10);
    
        // AJAX handlers
        $this->loader->add_action('wp_ajax_pick_pack_add_to_cart', $plugin_public, 'pick_pack_add_to_cart_handler');
        $this->loader->add_action('wp_ajax_nopriv_pick_pack_add_to_cart', $plugin_public, 'pick_pack_add_to_cart_handler');
    }
}
