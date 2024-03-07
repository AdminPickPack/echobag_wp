<?php
/**
 * Admin dashboard for Pick Pack.
 *
 * @package    Pick_Pack
 * @subpackage Pick_Pack/admin
 * @author     Pick Pack <admin@pick-pack.ca>
 * @since      1.0.0
 */
if (!defined('ABSPATH')) exit;

$total_stats = array( 'pickpack_solds' => 0, 'pickpack_price' => 0 );
$month_stats = array( 'pickpack_solds' => 0, 'pickpack_price' => 0 );
$year_stats  = array( 'pickpack_solds' => 0, 'pickpack_price' => 0 );

$query = new WP_Query(array('post_type' => 'pickpack_orders', 'posts_per_page' => -1));
if ($query->have_posts()) {
    while ($query->have_posts()) {
	$query->the_post();
	$post_id    = get_the_ID();
	$post_date  = get_the_date('Y-m-d H:i:s');
	$order_id   = get_post_meta($post_id, 'order_id', true);
	$order_name = get_post_meta($post_id, 'order_name', true);
	$price      = floatval(get_post_meta($post_id, 'price', true));
	$quantity   = intval(get_post_meta($post_id, 'quantity', true));
	$total      = floatval($quantity * $price);
	
	$total_stats['pickpack_solds'] += $quantity;
	$total_stats['pickpack_price'] += $total;
	
	$date_ts    = strtotime($post_date);
	$date_day   = date('d', $date_ts);
	$date_month = date('m', $date_ts);
	$date_year  = date('Y', $date_ts);
	
	$current_day   = date('d');
	$current_month = date('m');
	$current_year  = date('Y');
	
	if ($date_year == $current_year) {
	    $year_stats['pickpack_solds'] += $quantity;
	    $year_stats['pickpack_price'] += $total;
	    
	    if ($date_month == $current_month) {
		$month_stats['pickpack_solds'] += $quantity;
		$month_stats['pickpack_price'] += $total;
		
		if ($date_day == $current_day) {
		    $today_stats['pickpack_solds'] += $quantity;
		    $today_stats['pickpack_price'] += $total;
		}
	    }
	}
    }
    wp_reset_postdata();
}
?>

<div id="pickpack-dashboard">
    <div class="dashboard-left">
        <h2 class="page-title"><?php esc_html_e("Here's what's happening in your store today", 'pick-pack'); ?></h2>
        <div class="dashboard-stats">
            <div class="dashboard-item">
                <div class="dashboard-item-title">
                    <?php esc_html_e("Today's sales", 'pick-pack') ?>
                </div>
                <div class="dashboard-item-value">
                    <?php echo sprintf("<strong>%.02f</strong> $", esc_html($today_stats['pickpack_price'])) ?>
                </div>
            </div>
            <div class="dashboard-item">
                <div class="dashboard-item-title">
                    <?php esc_html_e("PickPack solds today", 'pick-pack') ?>
                </div>
                <div class="dashboard-item-value">
                    <?php echo sprintf( _n('<strong>%d</strong> bag', '<strong>%d</strong> bags', $today_stats['pickpack_solds'], 'pick-pack'), $today_stats['pickpack_solds']); ?>
                </div>
            </div>
        </div>
        <div class="dashboard-welcome">
            <p class="dashboard-welcome-title">
                <?php esc_html_e('Welcome in the PickPack team !', 'pick-pack') ?>
            </p>
            <p class="dashboard-welcome-text">
                <?php esc_html_e('This is where the change begins for REAL! Thanks to this new application in your store, you will be able to see the PickPack effect on your consumers in real time. We congratulate you on this download and we wish you the GREATEST success in your e-com ecological transition.', 'pick-pack') ?>
            </p>
        </div>
    </div>
    <div class="dashboard-right">
        <div class="card">
            <div class="dashboard-item">
                <div class="dashboard-item-title">
                    <?php esc_html_e("Lifetime PickPack Sales", 'pick-pack') ?>
                </div>
                <div class="dashboard-item-value">
                    <?php echo sprintf("<strong>%.02f</strong> $", esc_html($total_stats['pickpack_price'])) ?>
                    <span><?php echo sprintf( _n('(%d bag)', '(%d bags)', $total_stats['pickpack_solds'], 'pick-pack'), $total_stats['pickpack_solds']); ?></span>
                </div>
            </div>
            <div class="dashboard-item">
                <div class="dashboard-item-title">
                    <?php esc_html_e("This month's PickPack sales", 'pick-pack') ?>
                </div>
                <div class="dashboard-item-value">
                    <?php echo sprintf("<strong>%.02f</strong> $", esc_html($month_stats['pickpack_price'])) ?>
                    <span><?php echo sprintf( _n('(%d bag)', '(%d bags)', $month_stats['pickpack_solds'], 'pick-pack'), $month_stats['pickpack_solds']); ?></span>
                </div>
            </div>
            <div class="dashboard-item">
                <div class="dashboard-item-title">
                    <?php esc_html_e("This year's PickPack sales", 'pick-pack') ?>
                </div>
                <div class="dashboard-item-value">
                    <?php echo sprintf("<strong>%.02f</strong> $", esc_html($year_stats['pickpack_price'])) ?>
                    <span><?php echo sprintf( _n('(%d bag)', '(%d bags)', $year_stats['pickpack_solds'], 'pick-pack'), $year_stats['pickpack_solds']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>




