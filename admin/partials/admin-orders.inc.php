<?php
/**
 * Pick Pack orders list.
 *
 * @category Admin
 * @package  Pick_Pack
 * @author   Pick Pack <admin@pick-pack.ca>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://pick-pack.ca/
 * @since    1.0.0
 */
if (!defined('ABSPATH')) { 
    exit;
}

if (!class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class PickPack_Orders_List_Table extends WP_List_Table
{
    private $table_data = array();
    
    public function get_columns()
    {
        $columns = array( 'date'     => __('Date', 'pick-pack'),
        'order'    => __('Order', 'pick-pack'),
        'quantity' => __('Pick Pack Solds', 'pick-pack'),
        'price'    => __('Price per Bag', 'pick-pack'),
        'total'    => __('Total Paid', 'pick-pack') );
        return $columns;
    }
    
    public function get_sortable_columns() 
    {
        $sortable_columns = array( 'date'     => array('date', false),
        'order'    => array('order', false),
        'quantity' => array('quantity', false),
        'price'    => array('price', false),
        'total'    => array('total', false) );
        return $sortable_columns;
    }
    
    public function prepare_items() 
    {    
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->table_data = $this->get_pickpack_orders();
        if (empty($this->table_data)) { $this->table_data = array(); 
        }
    
        $search_query = isset($_REQUEST['s']) ? trim($_REQUEST['s']) : null;
        if (!empty($search_query)) {
            $this->table_data = array_filter(
                $this->table_data, function ($item) use ($search_query) {
                    return stripos($item['date'], $search_query) !== false || stripos((string) $item['order_desc'], $search_query) !== false;
                }
            );
        }
    
        usort($this->table_data, array($this, 'usort_items'));
    
        $per_page = $this->get_items_per_page('pickpack_orders_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->set_pagination_args(
            array( 'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page) )
        );
    
        $this->items = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);
    }

    private function get_pickpack_orders() 
    {
        $items = array();
    
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
        
                $items[] = array( 'id'         => $post_id,
                'date'       => $post_date,
                'order_id'   => $order_id,
                'order_name' => $order_name,
                'order_desc' => trim(sprintf("#%s %s", $order_id, $order_name)),
                'price'      => $price,
                'quantity'   => $quantity,
                'total'      => $total );
            }
            wp_reset_postdata();
        }
        return $items;
    }
    
    public function column_order($item)
    {
        $order_id = $item['order_id'];
        $order_desc = $item['order_desc'];
        if (!empty($order_id)) {
            return sprintf('<a href="%s" title="%s">%s</a>', admin_url("/post.php?post={$order_id}&action=edit"), __('View Order', 'pick-pack'), $order_desc);
        } else {
            return __('N/A', 'pick-pack');
        }
    }
    
    public function column_price($item)
    {
        $price = $item['price'];
        if (!empty($price)) {
            return sprintf('%.02f $', $price);
        } else {
            return __('N/A', 'pick-pack');
        }
    }

    public function column_total($item)
    {
        $price = $item['price'];
        if (!empty($price)) {
            return sprintf('%.02f $', $price);
        } else {
            return __('N/A', 'pick-pack');
        }
    }

    public function column_default($item, $column_name) 
    {
        return $item[$column_name];
    }
    
    public function usort_items($a, $b) 
    {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'date';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';
    
        if (is_numeric($a[$orderby]) && is_numeric($b[$orderby])) {
            $result = (float)$a[$orderby] - (float)$b[$orderby];
        } else {
            $result = strnatcmp($a[$orderby], $b[$orderby]);
        }
        return ($order === 'asc') ? $result : -$result;
    }
}
?>

<!-- Page title -->
<h2 class="page-title"><?php esc_html_e('Orders', 'pick-pack'); ?></h2>

<!-- Orders List -->
<form method="post">
<?php
$list_table = new PickPack_Orders_List_Table();
$list_table->prepare_items();
$list_table->search_box('search', 'search_id');
$list_table->display();
?>
</form>
