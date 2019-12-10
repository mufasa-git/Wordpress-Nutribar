<?php
/**
 * Plugin Name: Nutribar menu product for WooCommerce
 * Description: Pluging for Nutribar menu
 * Version: 1.0
 * Author: Maksym Holovchenko
 * WC requires at least: 3.0
 * WC tested up to: 3.6.5
 */
global $plugin_slug;
$plugin_slug = 'n_menu_product';

require __DIR__.'/functions.php';
require __DIR__.'/reports_page.php';

function admin_style() {
  global $plugin_slug;
  wp_enqueue_style($plugin_slug.'_admin-styles', plugin_dir_url(__FILE__).'css/admin.css');
  wp_enqueue_script($plugin_slug.'_admin-script', plugin_dir_url(__FILE__).'js/admin.js', array('jquery'));
}
add_action('admin_enqueue_scripts', 'admin_style');

// Register new Menu product type
add_action('init', 'register_menu_product_type');
function register_menu_product_type(){
 class WC_Product_Menu extends WC_Product{
   public function __construct($product){
     $this->product_type = 'menu';
     parent::__construct($product);
   }
   public function add_to_cart_url(){
    $url = $this->is_purchasable() && $this->is_in_stock() ? remove_query_arg('added-to-cart', add_query_arg('add-to-cart', $this->id)) : get_permalink($this->id);
    return apply_filters('woocommerce_product_add_to_cart_url', $url, $this);
  }
 }
}
// Add Menu product type to product selector
add_filter('product_type_selector', 'add_menu_product_type');
function add_menu_product_type($types){
  global $plugin_slug;
  $types['menu'] = __('Menu product', $plugin_slug);
  return $types;
}
// Add tab for Menu product type
add_filter('woocommerce_product_data_tabs', 'menu_product_tab');
function menu_product_tab($tabs){
  global $plugin_slug;
  $tabs['menu'] = array(
    'label'	 => __('Menu list', $plugin_slug),
    'target' => 'menu_product_options',
    'class'  => array('show_if_menu_product','hide_if_simple','hide_if_grouped','hide_if_external','hide_if_variable'),
   );
   array_push($tabs['general']['class'],'show_if_menu_product');
  return $tabs;
}
// Add fields to the tab
add_action('woocommerce_product_data_panels', 'menu_product_tab_product_tab_content');
function menu_product_tab_product_tab_content(){
  global $plugin_slug;
  include 'templates/menu_product_tab_content.php';
}
// Add Saturday price field
add_action('woocommerce_product_options_general_product_data', 'add_saturday_price_field');
function add_saturday_price_field(){
  global $woocommerce, $post;
  $_product = wc_get_product($post->ID);
  if($_product->is_type('menu')){
    echo '<div class="product_custom_field">';
    woocommerce_wp_text_input(
      array(
        'id' => '_saturday_price',
        'label' => __('Saturday price', 'woocommerce'),
        'desc_tip' => 'true',
        'data_type' => 'price'
      )
    );
    echo '</div>';
  }
}
// Save Menu product data
add_action('woocommerce_process_product_meta', 'save_menu_product_settings');
function save_menu_product_settings($post_id){
  $_product = wc_get_product($post_id);
  if($_product->is_type('menu')){
    global $week_days, $courses;
    foreach($week_days as $week_day){
      $meta_array = [];
      foreach($courses as $course){
        if(!empty($_POST['menu_'.$week_day.'_'.$course->slug.'_variation'])){
          $meta_array['variation'][$course->slug] = $_POST['menu_'.$week_day.'_'.$course->slug.'_variation'];
        }else{
          $meta_array['variation'][$course->slug] = [];
        }
        if(!empty($_POST['menu_'.$week_day.'_'.$course->slug.'_default'])){
          $meta_array['default'][$course->slug] = $_POST['menu_'.$week_day.'_'.$course->slug.'_default'];
        }else{
          $meta_array['default'][$course->slug] = [];
        }
      }
      update_post_meta($post_id, 'menu_'.$week_day, $meta_array);
    }
    update_post_meta($post_id, '_saturday_price', $_POST['_saturday_price']);
  }
}

// Change cart subtotal - add Saturday price
add_action('woocommerce_before_calculate_totals', 'add_custom_price', 10, 1);
function add_custom_price($cart_object){
  if (is_admin() && ! defined('DOING_AJAX')) return;
  if (did_action('woocommerce_before_calculate_totals') >= 2) return;
  if (session_status() == PHP_SESSION_NONE){
    session_start();
  }
  foreach ($cart_object->get_cart() as $hash => $value){
    if($value['data']->is_type('menu')){
      if (isset($_SESSION['add_saturday']) && $_SESSION['add_saturday'] == "1"){
        $new_price = floatval($value['data']->get_price()) + floatval(get_post_meta($value['product_id'], '_saturday_price', true));
		    $value['data']->set_price($new_price);
      }
    }
	}
}

// Add meta data to order
add_action('woocommerce_checkout_update_order_meta', 'before_checkout_create_order', 20, 2);
function before_checkout_create_order($order_id, $data){
  $order = wc_get_order($order_id);
  foreach ($order->get_items() as $item_id => $item){
    $product_id = $item->get_product_id();
    $_product = wc_get_product($product_id);
    if($_product->is_type('menu')){
      if (session_status() == PHP_SESSION_NONE){
        session_start();
      }
      global $week_days, $courses;
      $stat_items = array();
      $weeks = 0;
      global $stat_times;
    	if(has_term('week', 'product_cat', $product_id)){
    		$weeks = 1;
    	}else if(has_term('month', 'product_cat', $product_id)){
    	  if(isset($_SESSION['full_month']) && $_SESSION['full_month'] == "1"){
    	    $weeks = 4;
          $stat_times = 1;
    	  }else{
    	    $weeks = 1;
          $stat_times = 4;
    	  }
    	}
      for($i=1;$i<=$weeks;$i++){
        foreach($week_days as $week_day){
          if((!isset($_SESSION['add_saturday']) || $_SESSION['add_saturday'] == "0") && $week_day=="saturday") continue;
          $meta_array = [];
          foreach($courses as $course){
            if($course->type == 'sub_category') continue;
            $sub_prod_id = isset($_SESSION['menu_'.$week_day.'_'.$i.'_'.$course->slug]) ? $_SESSION['menu_'.$week_day.'_'.$i.'_'.$course->slug] : get_post_meta($product_id, 'menu_'.$week_day, true)['default'][$course->slug][0];

            $meta_array[$course->slug] = $sub_prod_id;
            // add data to statistic
            if(!empty($sub_prod_id)){
              if(array_key_exists($sub_prod_id,$stat_items)){
                $stat_items[$sub_prod_id] = $stat_items[$sub_prod_id]+1;
              }else{
                $stat_items[$sub_prod_id] = 1;
              }
            }
            // Adding subcourse
            if(has_term('main-dish', 'product_cat', $sub_prod_id)){
    					$param = $courses[array_search('sides_dishes', array_column($courses, 'slug'))];
    				}
    				if(has_term('salad', 'product_cat', $sub_prod_id)){
    					$param = $courses[array_search('dressing_salad', array_column($courses, 'slug'))];
    				}
    				if(isset($param)){
              $sub_prod_id = isset($_SESSION['menu_'.$week_day.'_'.$i.'_'.$param->slug]) ? $_SESSION['menu_'.$week_day.'_'.$i.'_'.$param->slug] : get_post_meta($product_id, 'menu_'.$week_day, true)['default'][$param->slug][0];

              $meta_array[$param->slug] = $sub_prod_id;
              // add data to statistic
              if(!empty($sub_prod_id)){
                if(array_key_exists($sub_prod_id,$stat_items)){
                  $stat_items[$sub_prod_id] = $stat_items[$sub_prod_id]+1;
                }else{
                  $stat_items[$sub_prod_id] = 1;
                }
              }
            }
          }
          // $order->update_meta_data('menu_'.$week_day, $meta_array);
          update_post_meta($order_id, 'menu_'.$week_day.'_'.$i, $meta_array);
        }
      }
      if(has_term('month', 'product_cat', $product_id)){
        $stat_items = array_map(function($a){global $stat_times;return $a*$stat_times;}, $stat_items);
      }
      save_order_items_stat($stat_items,$order_id);

      mycred_subtract('menu_order', get_current_user_id(), $_SESSION['total_nutripoints']*-1, 'Nutripoints for ordering menu');
      update_post_meta($order_id, 'subtotal_nutripoints', $_SESSION['subtotal_nutripoints']);
      update_post_meta($order_id, 'total_nutripoints', $_SESSION['total_nutripoints']);

      // add start date and finish date menu properties
      $interval_date = new DateTime('next monday');
      update_post_meta($order_id, 'menu_start', $interval_date->format('Y-m-d'));
      $days = 7;
      if(has_term('month', 'product_cat', $product_id)) $days *= 4;
      $days -=1;
      $interval_date->modify("+".$days." days");
      update_post_meta($order_id, 'menu_finish', $interval_date->format('Y-m-d'));
    }
  }
}

function save_order_items_stat($data,$order_id){
  foreach ($data as $product_id => $item_qty){
    $product = wc_get_product($product_id);
    $order_item_id = wc_add_order_item($order_id, array(
      'order_item_name' => $product->get_title(),
      'order_item_type' => 'line_item'
    ));
    wc_add_order_item_meta($order_item_id, '_qty', $item_qty, true);
    wc_add_order_item_meta($order_item_id, '_product_id', $product_id, true);
    wc_add_order_item_meta($order_item_id, '_line_subtotal', 0, true);
    wc_add_order_item_meta($order_item_id, '_line_total', 0, true);
    wc_add_order_item_meta($order_item_id, '_hidden', 1, true);
  }
  $order = wc_get_order($order_id);
  $order->calculate_totals();
}

// Hide item on admin order page
add_filter('woocommerce_admin_html_order_item_class', 'hide_admin_order_subproduct_items', 10, 3);
function hide_admin_order_subproduct_items($class, $item, $order){
  if(wc_get_order_item_meta($item->get_id(), '_hidden') == "1"){
    $class .= " hidden";
  }
  return $class;
}

// Admin order item meta list hook
add_action('woocommerce_before_order_itemmeta', 'add_item_meta_data_to_order_page', 10, 3);
function add_item_meta_data_to_order_page($item_id, $item, $_product){
  global $post, $week_days, $courses;
  $customer_id = get_post_meta($post->ID, '_customer_user', true);
  if(isset($_product) && get_class($_product) == "WC_Product_Menu"):?>
    <div class=""><?=(empty(get_post_meta($post->ID, 'menu_saturday_1', true)) ? 'Mon - Fri' : 'Mon - Sat')?> <?=get_user_meta($customer_id,'delivery_time',true)?></div>
    <div class="order_page_subproduct_list">
    	<?php for($i=1;$i<=4;$i++): ?>
        <?php foreach($week_days as $week_day):
          if(empty(get_post_meta($post->ID, 'menu_'.$week_day.'_'.$i, true))) continue;
        ?>
          <div class="order_page_subproduct_item">
            <div class="order_page_subproduct_item_title"><strong><?=ucfirst($week_day)?></strong></div>
          </div>
          <?php foreach($courses as $course):
            if(!array_key_exists($course->slug,get_post_meta($post->ID, 'menu_'.$week_day.'_'.$i, true))) continue;
            $sub_prod_id = get_post_meta($post->ID, 'menu_'.$week_day.'_'.$i, true)[$course->slug];
            if(empty($sub_prod_id)) continue;
            $sub_prod = wc_get_product($sub_prod_id);
            if($sub_prod):
          ?>
            <div class="order_page_subproduct_item">
              <img src="<?=get_the_post_thumbnail_url($sub_prod_id,'thumbnail')?>">
              <div class="order_page_subproduct_item_title">
                <strong><?=$course->title?>:</strong> <a href="<?=admin_url('post.php?post='.$sub_prod_id.'&action=edit')?>" target="_blank"><?=$sub_prod->get_title()?></a>
              </div>
            </div>
          <?php else: ?>
            <div class="order_page_subproduct_item">
              Product #<?=$sub_prod_id?> is not found
            </div>
          <?php endif; ?>
          <?php endforeach; ?>
        <?php endforeach; ?>
      <?php endfor;?>
    </div>
  <?php endif;
}

// Show price fields
function nutribar_menu_admin_custom_js(){
  if(get_post_type() != 'product') return;
  ?>
  <script type='text/javascript'>
    jQuery(document).ready(function () {
      //for Price tab
      jQuery('.product_data_tabs .general_tab').addClass('show_if_menu_product').show();
      jQuery('#general_product_data .pricing').addClass('show_if_menu_product').show();
    });
  </script>
  <?php
}
add_action('admin_footer', 'nutribar_menu_admin_custom_js');
