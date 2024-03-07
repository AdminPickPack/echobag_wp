<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pick_Pack
 * @subpackage Pick_Pack/admin
 * @author     Pick Pack <admin@pick-pack.ca>
 * @since      1.0.0
 */
if (!defined('ABSPATH')) exit;

class Pick_Pack_Admin
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
     * A shared variable. Multiple categories products
     *
     * @since  1.0.0
     * @access private
     * @var    string    $version    The current version of this plugin.
     */
    private $choosen_products;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     * @return void
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pick-pack-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_scripts()
    {
    }

    /**
     * Add Admin menu.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_admin_menu()
    {
        add_menu_page(
            __('Pick Pack', 'pick-pack'),
            __('Pick Pack', 'pick-pack'),
            'manage_options',
            'pickpack',
            array($this, 'display_dashboard'),
            PICK_PACK_BASEURL . 'assets/images/pick-pack-icon.png',
            57.9
        );

	add_submenu_page(
            'pickpack', 
	    __('Pick Pack Dashboard', 'pick-pack'),
	    __('Dashboard', 'pick-pack'),
	    'manage_options',
	    'pickpack',
	    array($this, 'display_dashboard'),
	);

        $orders_page = add_submenu_page(
            'pickpack',
	    __('Pick Pack Orders', 'pick-pack'),
	    __('Orders', 'pick-pack'),
	    'manage_options',
            'pickpack_orders',
	    array($this, 'display_orders'),
	);
	add_action("load-{$orders_page}", array($this, 'display_orders_screen_options'));

        add_submenu_page(
            'pickpack', 
	    __('Pick Pack Settings', 'pick-pack'),
	    __('Settings', 'pick-pack'),
	    'manage_options',
	    'pickpack_settings',
	    array($this, 'display_settings'),
	);
    }
    
    /**
     * Display pick pack admin dashboard page.
     *
     * @since 1.0.0
     * @return void
     */
    public function display_dashboard()
    {
	include_once plugin_dir_path(__FILE__) . 'partials/admin-header.inc.php';
	include_once plugin_dir_path(__FILE__) . 'partials/admin-dashboard.inc.php';
	include_once plugin_dir_path(__FILE__) . 'partials/admin-footer.inc.php';
    }

    
    /**
     * Display pick pack admin settings page.
     *
     * @since 1.0.0
     * @return void
     */
    public function display_settings()
    {
	include_once plugin_dir_path(__FILE__) . 'partials/admin-header.inc.php';
	include_once plugin_dir_path(__FILE__) . 'partials/admin-settings.inc.php';
	include_once plugin_dir_path(__FILE__) . 'partials/admin-footer.inc.php';
    }

    /**
     * Display pick pack orders page.
     *
     * @since 1.0.0
     * @return void
     */
    public function display_orders()
    {
	include_once plugin_dir_path(__FILE__) . 'partials/admin-header.inc.php';
	include_once plugin_dir_path(__FILE__) . 'partials/admin-orders.inc.php';
	include_once plugin_dir_path(__FILE__) . 'partials/admin-footer.inc.php';
    }
    
    public function display_orders_screen_options()
    {
	add_filter( 'set_screen_option_pickpack_orders_per_page', function($default, $option, $value) { return $value; }, 10, 3);
	add_screen_option('per_page', array( 'label' => __('Orders per page', 'pick-pack'),
					     'default' => 10,
					     'option' => 'pickpack_orders_per_page' ));
    }
    
    /**
     * Initialize pick pack in woocommerce.
     *
     * @since 1.0.0
     * @return void
     */
    public function init_woocommerce()
    {
	// Make sure that woocommerce is active (enabled)
	$wc_plugin = $this->get_wc_plugin_basename();
	$wc_enabled = !empty($wc_plugin) ? is_plugin_active($wc_plugin) : false;
	if (!$wc_enabled) {
	    add_action('admin_notices', function() {
		$wc_plugin = Pick_Pack_Admin::get_wc_plugin_basename();
		$activation_url = esc_url(wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $wc_plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $wc_plugin));
		$message = __("<strong>Pick Pack</strong> requires <strong>WooCommerce</strong> plugin to be active. Please activate it to continue.", 'pick-pack');
		
		$button_text = esc_html__('Activate WooCommerce', 'pick-pack');
		$button = "<p><a href='{$activation_url}' class='button-primary'>{$button_text}</a></p>";
		
		printf('<div class="notice notice-error"><p>%1$s</p>%2$s</div>', $message, $button);
		
		if (isset($_GET['activate'])) {
		    unset($_GET['activate']);
		}
		
		deactivate_plugins(PICK_PACK_BASENAME);
	    });
	    return ;
	}
	
	// Add pick pack options to categories
	$this->add_wc_category_options();
	
	// Add pick pack options to products
	$this->add_wc_product_options();
	
	// Create custom pick pack product
	$this->create_pick_pack_product();
    }
    
    /**
     * Get woocommerce plugin base name.
     *
     * @since 1.0.0
     * @return string The woocommerce plugin base name.
     */
    public function get_wc_plugin_basename() 
    {
	static $wc_basename = null;
	if (empty($wc_basename)) {
	    if (!function_exists('get_plugins')) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	    }
	    $plugins = get_plugins();
	    foreach ($plugins as $key => $data) {
		if ($data['TextDomain'] === "woocommerce") {
		    $wc_basename = $key;
		    break;
		}
	    }
	}
	return $wc_basename;
    }

    /**
     * Add pick pack options to woocommerce categories.
     *
     * @since 1.0.0
     * @return void
     */
    private function add_wc_category_options() 
    {
	// Pick Pack options in new category page
	add_action('product_cat_add_form_fields', function() { 
	    ?>
	    <div class="form-field">
	        <label for="pick_pack_product_type"><?php esc_html_e('Pick Pack Products Type', 'pick-pack'); ?></label>
	        <select name="pick_pack_product_type" id="pick_pack_product_type" class="postform">
	            <option value="default"><?php esc_html_e("Use default value", 'pick-pack'); ?></option>
	            <option value="enable"><?php esc_html_e("Pick Pack Enabled", 'pick-pack'); ?></option>
	            <option value="fragile"><?php esc_html_e("Fragile Products", 'pick-pack'); ?></option>
	            <option value="large"><?php esc_html_e("Large Products", 'pick-pack'); ?></option>
	            <option value="disable"><?php esc_html_e("Pick Pack Disabled", 'pick-pack'); ?></option>
	        </select>
                <p><?php esc_html_e("Pick Packs are compatible with non-fragile products only. In addition, certain products that are too large cannot be delivered in a Pick Pack. To improve your customers' experience, please add these tags to products not eligible for Pick Pack delivery: fragile or bulky.", 'pick-pack') ?></p>
	    </div>
	    <div class="form-field">
	        <label for="pick_pack_product_points"><?php esc_html_e('Pick Pack Products Points', 'your-textdomain'); ?></label>
        	<select name="pick_pack_product_points" id="pick_pack_product_points" class="postform" required>
	            <option value="default"><?php esc_html_e("Use default value", 'pick-pack'); ?></option>
	            <option value="1"><?php esc_html_e("1 point (small products)", 'pick-pack'); ?></option>
	            <option value="2"><?php esc_html_e("2 points", 'pick-pack'); ?></option>
	            <option value="3"><?php esc_html_e("3 points", 'pick-pack'); ?></option>
	            <option value="4"><?php esc_html_e("4 points", 'pick-pack'); ?></option>
	            <option value="5"><?php esc_html_e("5 points", 'pick-pack'); ?></option>
	            <option value="6"><?php esc_html_e("6 points", 'pick-pack'); ?></option>
	            <option value="7"><?php esc_html_e("7 points", 'pick-pack'); ?></option>
	            <option value="8"><?php esc_html_e("8 points", 'pick-pack'); ?></option>
	            <option value="9"><?php esc_html_e("9 points", 'pick-pack'); ?></option>
	            <option value="10"><?php esc_html_e("10 points", 'pick-pack'); ?></option>
	            <option value="11"><?php esc_html_e("11 points", 'pick-pack'); ?></option>
	            <option value="12"><?php esc_html_e("12 points", 'pick-pack'); ?></option>
	            <option value="13"><?php esc_html_e("13 points", 'pick-pack'); ?></option>
	            <option value="14"><?php esc_html_e("14 points", 'pick-pack'); ?></option>
	            <option value="15"><?php esc_html_e("15 points", 'pick-pack'); ?></option>
	            <option value="16"><?php esc_html_e("16 points", 'pick-pack'); ?></option>
	            <option value="17"><?php esc_html_e("17 points", 'pick-pack'); ?></option>
	            <option value="18"><?php esc_html_e("18 points", 'pick-pack'); ?></option>
	            <option value="19"><?php esc_html_e("19 points", 'pick-pack'); ?></option>
	            <option value="20"><?php esc_html_e("20 points (large products)", 'pick-pack'); ?></option>
        	</select>
                <p><?php esc_html_e("To make Pick Packs more compatible with your products, please add points to each product category so we can manage quantities and spaces correctly. Note that the large Pick Pack format has a capacity of 20 points. Thus, for every 21 points, we add a Pick Pack to the consumer's invoice.", 'pick-pack') ?></p>
	    </div>
	    <?php 
	}, 5, 2);

	add_action('edited_product_cat', function($term_id) {
	    $product_type   = isset($_POST['pick_pack_product_type']) ? $_POST['pick_pack_product_type'] : 'default';
	    update_term_meta($term_id, 'pick_pack_product_type', sanitize_text_field($product_type));
	    
	    $product_points = isset($_POST['pick_pack_product_points']) ? $_POST['pick_pack_product_points'] : 'default';
	    update_term_meta($term_id, 'pick_pack_product_points', sanitize_text_field($product_points));
	}, 10, 2);
	
	// Pick Pack options in edit category page
	add_action('product_cat_edit_form_fields', function($term) {
	    $product_type = get_term_meta($term->term_id, 'pick_pack_product_type', true);
	    $product_points = get_term_meta($term->term_id, 'pick_pack_product_points', true);
	    ?>
	    <tr class="form-field">
	        <th scope="row" valign="top"><label for="pick_pack_product_type"><?php esc_html_e('Pick Pack Products Type', 'pick-pack'); ?></label></th>
		<td>
	            <select name="pick_pack_product_type" id="pick_pack_product_type" class="postform">
	                <option value="default" <?php selected($product_type, 'default'); ?>><?php esc_html_e("Use default value", 'pick-pack'); ?></option>
	                <option value="enable" <?php selected($product_type, 'enable'); ?>><?php esc_html_e("Pick Pack Enabled", 'pick-pack'); ?></option>
	                <option value="fragile" <?php selected($product_type, 'fragile'); ?>><?php esc_html_e("Fragile Products", 'pick-pack'); ?></option>
	                <option value="large" <?php selected($product_type, 'large'); ?>><?php esc_html_e("Large Products", 'pick-pack'); ?></option>
	                <option value="disable" <?php selected($product_type, 'disable'); ?>><?php esc_html_e("Pick Pack Disabled", 'pick-pack'); ?></option>
	            </select>
                    <p><?php esc_html_e("Pick Packs are compatible with non-fragile products only. In addition, certain products that are too large cannot be delivered in a Pick Pack. To improve your customers' experience, please add these tags to products not eligible for Pick Pack delivery: fragile or bulky.", 'pick-pack') ?></p>
		</td>
	    </tr>
	    <tr class="form-field">
	        <th scope="row" valign="top"><label for="pick_pack_product_points"><?php esc_html_e('Pick Pack Products Points', 'your-textdomain'); ?></label></th>
                <td>
        	    <select name="pick_pack_product_points" id="pick_pack_product_points" class="postform" required>
	                <option value="default" <?php selected($product_points, 'default'); ?>><?php esc_html_e("Use default value", 'pick-pack'); ?></option>
        	        <option value="1" <?php selected($product_points, '1'); ?>><?php esc_html_e("1 point (small product)", 'pick-pack'); ?></option>
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
	                <option value="20" <?php selected($product_points, '20'); ?>><?php esc_html_e("20 points (large product)", 'pick-pack'); ?></option>
        	    </select>
                    <p><?php esc_html_e("To make Pick Packs more compatible with your products, please add points to each product category so we can manage quantities and spaces correctly. Note that the large Pick Pack format has a capacity of 20 points. Thus, for every 21 points, we add a Pick Pack to the consumer's invoice.", 'pick-pack') ?></p>
		</td>
	    </tr>
	    <?php
	}, 5, 2);

	add_action('create_product_cat', function($term_id) {
	    $product_type   = isset($_POST['pick_pack_product_type']) ? $_POST['pick_pack_product_type'] : 'default';
	    update_term_meta($term_id, 'pick_pack_product_type', sanitize_text_field($product_type));
	    
	    $product_points = isset($_POST['pick_pack_product_points']) ? $_POST['pick_pack_product_points'] : 'default';
	    update_term_meta($term_id, 'pick_pack_product_points', sanitize_text_field($product_points));
	}, 10, 2);

	// Display pick pack options in categories list
	add_filter('manage_edit-product_cat_columns', function($columns) {
	    $columns['pick_pack'] = __("Pick Pack", 'pick-pack');
	    return $columns;
	}, 10, 1);
	add_action('manage_product_cat_custom_column', array($this, 'show_custom_wc_category_column'), 10, 3);
    }
	
    /**
     * Show custom woocommerce category column for pickpack.
     *
     * @since 1.0.0
     * @return void
     */
    public function show_custom_wc_category_column($content, $column_name, $term_id) {
	if ($column_name == 'pick_pack') {
	    $product_options = $this->get_wc_category_pickpack_options($term_id);
	    $product_type = $product_options['product_type'];
	    $product_points = $product_options['product_points'];
	    
	    $content = '';
	    switch ($product_type) {
	     default:
		$content = __('Default', 'pick-pack');
		break;
	     case 'enable':
		$content = __('Enabled', 'pick-pack');
		break;
	     case 'disable':
		$content = __('Disabled', 'pick-pack');
		break;
	     case 'fragile':
		$content = __('Fragile Products', 'pick-pack');
		break;
	     case 'large':
		$content = __('Large Products', 'pick-pack');
		break;
	    }
	    
	    if ($product_points != 'default' && in_array($product_type, array('', 'default', 'enable'))) {
		$product_points = intval($product_points);
		$content .= sprintf( _n(' (%d point)', ' (%d points)', $product_points, 'pick-pack'), $product_points);
	    }
	}
	return $content;
    }

    /**
     * Add pick pack options to woocommerce products.
     *
     * @since 1.0.0
     * @return void
     */
    private function add_wc_product_options()
    {
	// Pick Pack tab in products
	add_filter('woocommerce_product_data_tabs', function($tabs) {
	    $tabs['pick_pack'] = array( 'label'  => __('Pick Pack', 'pick-pack'),
					'target' => 'pick_pack_product_data' );
	    return $tabs;
	}, 50, 1);

	add_action('admin_head', function() {
	    echo sprintf('<style>#woocommerce-product-data ul.wc-tabs li.pick_pack_options a::before {' .
			 ' display: inline-block; width: 13px; height: 13px; content: "";' .
			 ' background-image: url(%s); background-size: contain; background-position: center; background-repeat: no-repeat;' .
			 '}</style>', PICK_PACK_BASEURL . 'assets/images/pick-pack-icon.png');
	});
				 
	// Pick Pack product options
	add_action('woocommerce_product_data_panels', function() {
	    global $post;
	    
	    echo '<div id="pick_pack_product_data" class="panel woocommerce_options_panel hidden">';

	    $type_options = array( 'default' => __("Default (primary category value)", 'pick-pack'),
				   'enable'  => __("Pick Pack Enabled", 'pick-pack'),
				   'fragile' => __("Fragile Product", 'pick-pack'),
				   'large'   => __("Large Product", 'pick-pack'),
				   'disable' => __("Pick Pack Disabled", 'pick-pack') );
	    
	    woocommerce_wp_select(array( 'id'    => 'pick_pack_product_type',
					 'label' => __("Product Type", 'pick-pack'),
					 'options' => $type_options,
					 'desc_tip' => true,
					 'description' => __("Pick Packs are compatible with non-fragile products only. In addition, certain products that are too large cannot be delivered in a Pick Pack.", 'pick-pack') ));
	
	    $points_options = array( 'default' => __("Default (primary category value)", 'pick-pack'),
				     '1'       => __("1 point (small product)", 'pick-pack'),
				     '2'       => __("2 points", 'pick-pack'),
				     '3'       => __("3 points", 'pick-pack'),
				     '4'       => __("4 points", 'pick-pack'),
				     '5'       => __("5 points", 'pick-pack'),
				     '6'       => __("6 points", 'pick-pack'),
				     '7'       => __("7 points", 'pick-pack'),
				     '8'       => __("8 points", 'pick-pack'),
				     '9'       => __("9 points", 'pick-pack'),
				     '10'      => __("10 points", 'pick-pack'),
				     '11'      => __("11 points", 'pick-pack'),
				     '12'      => __("12 points", 'pick-pack'),
				     '13'      => __("13 points", 'pick-pack'),
				     '14'      => __("14 points", 'pick-pack'),
				     '15'      => __("15 points", 'pick-pack'),
				     '16'      => __("16 points", 'pick-pack'),
				     '17'      => __("17 points", 'pick-pack'),
				     '18'      => __("18 points", 'pick-pack'),
				     '19'      => __("19 points", 'pick-pack'),
				     '20'      => __("20 points (large product)", 'pick-pack') );
	    
	    woocommerce_wp_select(array( 'id'    => 'pick_pack_product_points',
					 'label' => __("Pick Pack Points", 'pick-pack'),
					 'options' => $points_options,
					 'desc_tip' => true,
					 'description' => __("Note that the large Pick Pack format has a capacity of 20 points. Thus, for every 21 points, we add a Pick Pack to the consumer's invoice.", 'pick-pack') ));
	    
	    echo '</div>';
	});
	
	add_action('woocommerce_process_product_meta', function($post_id) {
	    $product_type   = isset($_POST['pick_pack_product_type']) ? $_POST['pick_pack_product_type'] : 'default';
	    update_post_meta($post_id, 'pick_pack_product_type', sanitize_text_field($product_type));
	    
	    $product_points = isset($_POST['pick_pack_product_points']) ? $_POST['pick_pack_product_points'] : 'default';
	    update_post_meta($post_id, 'pick_pack_product_points', sanitize_text_field($product_points));
	});
	
	add_filter('manage_edit-product_columns', function($columns) {
	    $columns['pick_pack'] = __('Pick Pack', 'pick-pack');
	    return $columns;
	}, 10, 1);
	add_action('manage_product_posts_custom_column', array($this, 'show_custom_wc_product_column'), 10, 2);
    }

    /**
     * Show custom woocommerce product column for pickpack.
     *
     * @since 1.0.0
     * @return void
     */
    public function show_custom_wc_product_column($column, $post_id) {
	if ($column == 'pick_pack') {
	    $product_options = $this->get_wc_product_pickpack_options($post_id);
	    $product_type = $product_options['product_type'];
	    $product_points = $product_options['product_points'];
	    
	    $content = '';
	    switch ($product_type) {
	     default:
		$content = __('Default', 'pick-pack');
		break;
	     case 'enable':
		$content = __('Enabled', 'pick-pack');
		break;
	     case 'disable':
		$content = __('Disabled', 'pick-pack');
		break;
	     case 'fragile':
		$content = __('Fragile Product', 'pick-pack');
		break;
	     case 'large':
		$content = __('Large Product', 'pick-pack');
		break;
	    }
	    
	    if ($product_points != 'default' && in_array($product_type, array('', 'default', 'enable'))) {
		$product_points = intval($product_points);
		$content .= sprintf( _n(' (%d point)', ' (%d points)', $product_points, 'pick-pack'), $product_points);
	    }
	    echo $content;
	}
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
     * Create pick pack product in woocommerce.
     *
     * @since 1.0.0
     * @return void
     */
    private function create_pick_pack_product()
    {
	if (!function_exists('wc_get_product')) {
	    return ;
	}
	
	// Load pick pack options
        $product_id = get_option('pick_pack_product_id');
	$product_price = get_option('pick_pack_product_price', PICK_PACK_DEFAULT_PRODUCT_PRICE);
	$product_stock = intval(get_option('pick_pack_product_stock', 0));
	
	// If a product id is available, check if product still exist in woocommerce
	$product = !empty($product_id) ? wc_get_product($product_id) : null;
	
	// Create new product if doesn't exist in woocommerce
	if (!$product) {
	    $product = new WC_Product_Simple();
	    $product->set_name(esc_html__('Pick Pack', 'pick-pack'));
	    $product->set_sku('Pick-Pack');
	    $product->set_regular_price($product_price);
	    $product->set_status('publish');
	    $product->set_manage_stock(false);
	    $product->set_stock_status('instock');
	    $product->set_catalog_visibility('hidden');
	    
	    $product_id = $product->save();
            if (!empty($product_id)) {
		update_post_meta($product_id, 'pick_pack_product_type', 'disable');
                update_option('pick_pack_product_id', $product_id);
		$this->set_pick_pack_product_image($product_id);
		
		add_action('admin_notices', function() {
		    $message = __("<strong>Pick Pack</strong> product has been created.", 'pick-pack');
		    printf('<div class="notice notice-success"><p>%s</p></div>', $message);
		});
	    }
	}

	// Display warning if product is not published
	if ($product && $product->status != 'publish') {
	    $product->set_status('publish');
	    $product->save();
	    
	    add_action('admin_notices', function() {
		$message = __("You can't delete <strong>Pick Pack</strong> product.", 'pick-pack');
		printf('<div class="notice notice-warning"><p>%s</p></div>', $message);
	    });
	}
    }
    
    /**
     * Set pick pack product image.
     *
     * @since 1.0.0
     * @return void
     */
    private function set_pick_pack_product_image($product_id) 
    {
	// Make sure that product exist
	if (!function_exists('wc_get_product') || !wc_get_product($product_id)) {
	    return ;
	}

	// Load image data
	$image_path = PICK_PACK_PATH . 'assets/images/eco-bag-product.jpg';
	$image_name = basename($image_path);
	$image_data = file_get_contents($image_path);

	// Create image in upload directory
	$upload_dir = wp_upload_dir();
	$unique_file_name = wp_unique_filename($upload_dir['path'], $image_name);
	$filename = basename($unique_file_name);
	
	if (wp_mkdir_p($upload_dir['path'])) {
	    $file = $upload_dir['path'] . '/' . $filename;
	} else {
	    $file = $upload_dir['basedir'] . '/' . $filename;
	}
	file_put_contents($file, $image_data);
	
	// Add image to attachments
	$wp_filetype = wp_check_filetype($filename, null);
	
	$attachment = array( 'post_mime_type' => $wp_filetype['type'],
			     'post_title'     => sanitize_file_name($filename),
			     'post_content'   => '',
			     'post_status'    => 'inherit' );
	
	$attach_id = wp_insert_attachment($attachment, $file);
	
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	
	$attach_data = wp_generate_attachment_metadata($attach_id, $file);
	wp_update_attachment_metadata($attach_id, $attach_data);
	
	// Set product image
	update_post_meta($product_id, '_thumbnail_id', $attach_id);
    }

    /**
     * Register custom post type for pick pack orders.
     *
     * @since 1.0.0
     * @return void
     */
    public function custom_orders_post_type()
    {
	$labels = array( 'name' => __('Pick Pack Orders', 'pick-pack'),
			 'singular_name' => __('Pick Pack Order', 'pick-pack') );
	
	$capabilities = array( 'create_posts'       => 'do_not_allow',
			       'edit_posts'         => 'do_not_allow',
			       'edit_others_posts'  => 'do_not_allow',
			       'publish_posts'      => 'do_not_allow',
			       'read_private_posts' => 'do_not_allow',
			       
			       'edit_post'          => 'do_not_allow',
			       'read_post'          => 'do_not_allow',
			       'delete_post'        => 'do_not_allow' );
	
	$args = array( 'label'    => __('Pick Pack Order', 'pick-pack'),
		       'labels'   => $labels,
		       'supports' => array(),
		       'public'   => false,
		       'show_ui'  => true,
		       'show_in_menu' => 'edit.php?post_type=pickpack_orders',
		       'show_in_admin_bar' => false,
		       'show_in_nav_menus' => false,
		       'show_in_rest' => false,
		       'can_export' => false,
		       'has_archive' => false,
		       'exclude_from_search' => true,
		       'publicly_queryable' => false,
		       'capability_type' => 'post',
		       'map_meta_cap' => false );
	
        register_post_type('pickpack_orders', $args);
    }

    /**
     * Add custom columns in pick pack orders.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_custom_columns_orders_admin($columns)
    {
        return array_merge($columns, ['price' => __('Price', 'pick-pack'), 'pick_pack_bags_sold' => __('Pick Pack Bags Sold', 'pick-pack')]);
    }
    
    /**
     * Fill data in custom columns in pick pack orders.
     *
     * @since 1.0.0
     * @return void
     */
    public function fill_custom_columns_orders_admin($column_key, $post_id)
    {
        if ($column_key == 'price') {
            $price = get_post_meta($post_id, 'price', true);
            if (!$price || $price == '') {
                echo sprintf('<span style="color:red;">%s</span>', esc_html__('Not available', 'pick-pack'));
            } else {
                echo sprintf('<span style="color:green;">%s</span>', esc_html($price));
            }
        }

        if ($column_key == 'pick_pack_bags_sold') {
            $eco_bags_sold = get_post_meta($post_id, 'quantity', true);
            if (!$eco_bags_sold || $eco_bags_sold == '') {
                echo sprintf('<span style="color:red;">%s</span>', esc_html__('Not available', 'pick-pack'));
            } else {
                echo sprintf('<span style="color:green;">%s</span>', esc_html($eco_bags_sold));
            }
        }
    }
    
    /**
     * Update product data AJAX handler.
     *
     * @since 1.0.0
     * @return void
     **/
    public function update_product_handler()
    {
	// Check if the current user is an admin
	if (!current_user_can('manage_options')) {
	    wp_send_json_error('Unauthorized user.', 401);
	    exit;
	}

	// Verify nonce security
	if (!wp_verify_nonce($_POST['nonce'], 'my_ajax_nonce')) {
	    wp_send_json_error('Nonce verification failed!', 403);
	    exit;
	}

	// Woocommerce not enabled
	if (!function_exists('wc_get_product')) {
	    wp_send_json_error('Woocommerce not enabled!', 403);
	    exit;
	}
	
	// Load pick pack product
	$product_id = get_option('pick_pack_product_id');
	$product = !empty($product_id) ? wc_get_product($product_id) : null;
	
	// Do nothing if missing product
	if (!$product) {
	    wp_send_json_error('Missing pick pack product!', 404);
	    exit;
	}
	
	// Update product data
	$product_name_fr  = sanitize_post($_POST['name_fr']);
	$product_name_en  = sanitize_post($_POST['name_en']);
	$product_price    = sanitize_post($_POST['price']);
	$product_stock    = intval(sanitize_post($_POST['stock']));
	$default_points   = intval(sanitize_post($_POST['default_points']));
	$is_exclusive     = !empty(intval(sanitize_post($_POST['exclusive']))) ? true : false;
	
	update_option('pick_pack_product_name_fr', $product_name_fr);
	update_option('pick_pack_product_name_en', $product_name_en);
	update_option('pick_pack_product_price', $product_price);
	update_option('pick_pack_product_stock', $product_stock);
	update_option('pick_pack_product_default_points', $default_points);
	update_option('pick_pack_product_exclusive', $is_exclusive);
	
	$product->set_regular_price($product_price);
	$product->set_manage_stock(true);
	$product->set_stock_status($product_stock > 0 ? 'instock' : 'outofstock');
	$product->set_stock_quantity($product_stock);
	$product->save();
	
	// Success
	wp_send_json_success('Success.');
	exit;
    }

    /**
     * Update popup data in french AJAX handler.
     *
     * @since 1.0.0
     * @return void
     **/
    public function update_popup_fr_handler()
    {
	// Check if the current user is an admin
	if (!current_user_can('manage_options')) {
	    wp_send_json_error('Unauthorized user.', 401);
	    exit;
	}

	// Verify nonce security
	if (!wp_verify_nonce($_POST['nonce'], 'my_ajax_nonce')) {
	    wp_send_json_error('Nonce verification failed!', 403);
	    exit;
	}
	
	// Update data
	update_option('pick_pack_popup_header_fr', sanitize_post(@$_POST['header']));
	update_option('pick_pack_popup_content_fr', sanitize_post(@$_POST['content']));
	update_option('pick_pack_popup_howto_fr', sanitize_post(@$_POST['howto']));
	
	// Success
	wp_send_json_success('Success.');
	exit;
    }

    /**
     * Update popup data in english AJAX handler.
     *
     * @since 1.0.0
     * @return void
     **/
    public function update_popup_en_handler()
    {
	// Check if the current user is an admin
	if (!current_user_can('manage_options')) {
	    wp_send_json_error('Unauthorized user.', 401);
	    exit;
	}

	// Verify nonce security
	if (!wp_verify_nonce($_POST['nonce'], 'my_ajax_nonce')) {
	    wp_send_json_error('Nonce verification failed!', 403);
	    exit;
	}
	
	// Update data
	update_option('pick_pack_popup_header_en', sanitize_post(@$_POST['header']));
	update_option('pick_pack_popup_content_en', sanitize_post(@$_POST['content']));
	update_option('pick_pack_popup_howto_en', sanitize_post(@$_POST['howto']));
	
	// Success
	wp_send_json_success('Success.');
	exit;
    }

}
