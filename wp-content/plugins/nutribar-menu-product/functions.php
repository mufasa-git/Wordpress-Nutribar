<?php
global $week_days, $courses;
$week_days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
$courses = [
 (object) ['slug'=>'breakfast','title'=>'Breakfast','type'=>'category','nutripoints'=>10],
 (object) ['slug'=>'1_snack','title'=>'1st Snack','type'=>'category','nutripoints'=>10],
 (object) ['slug'=>'lunch','title'=>'Lunch','type'=>'category','nutripoints'=>20],
 (object) ['slug'=>'2_snack','title'=>'2nd Snack','type'=>'category','nutripoints'=>10],
 // (object) ['slug'=>'fruit','title'=>'Fruit','type'=>'category','nutripoints'=>10],
 (object) ['slug'=>'dinner','title'=>'Dinner','type'=>'category','nutripoints'=>30],
 (object) ['slug'=>'dressing_salad','title'=>'Dressing for salad','type'=>'sub_category'],
 (object) ['slug'=>'sides_dishes','title'=>'Sides dishes','type'=>'sub_category']
];
// show multiple product select with searching
function woocommerce_wp_select_multiple($field){
  global $thepostid, $post;
  ?>
  <p class="form-field">
    <label for="<?=esc_attr($field['id'])?>"><?=wp_kses_post($field['label'])?></label>
    <select class="wc-product-search" multiple="multiple" style="width: 90%;" id="<?=esc_attr($field['id'])?>" name="<?=esc_attr($field['id'])?>[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?=intval($post->ID)?>">
      <?php
      $product_ids = $field['options'];

      foreach($product_ids as $product_id){
        $product = wc_get_product($product_id);
        if (is_object($product)){
          echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
        }
      }
      ?>
    </select> <?=wc_help_tip(wp_kses_post($field['description']))?>
  </p>
  <?php
}
add_action('wp_ajax_save_chosen_dish', 'save_chosen_dish_action');
function save_chosen_dish_action(){
  session_start();
  $_SESSION['menu_'.$_POST['day'].'_'.$_POST['week'].'_'.$_POST['course']] = $_POST['chosen_dish'];
  die;
}
add_action('wp_ajax_save_chosen_subdish', 'save_chosen_subdish_action');
function save_chosen_subdish_action(){
  session_start();
  $_SESSION['menu_'.$_POST['day'].'_'.$_POST['week'].'_'.$_POST['meal']] = $_POST['chosen_dish'];
  die;
}
add_action('wp_ajax_push_menu_to_cart', 'push_menu_to_cart_action');
function push_menu_to_cart_action(){
  session_start();
  WC()->cart->empty_cart();
  WC()->cart->add_to_cart($_SESSION['menu_id']);
  die;
}
add_action('wp_ajax_repeat_order', 'repeat_order_action');
function repeat_order_action(){
  session_start();
  global $week_days, $courses;
  $order_id = $_POST['order'];
  for($i=1;$i<=4;$i++){
    foreach($week_days as $week_day){
      if(empty(get_post_meta($order_id, 'menu_'.$week_day.'_'.$i, true))) continue;
      foreach($courses as $course){
        $_SESSION['menu_'.$week_day.'_'.$i.'_'.$course->slug] = get_post_meta($order_id, 'menu_'.$week_day.'_'.$i, true)[$course->slug];
      }
    }
  }
  die;
}
function add_nutrients($sub_prod_id,$nutrients){
  $nutrients->calories += floatval(get_post_meta($sub_prod_id, '_product_param_calories', true));
  $nutrients->carbs += floatval(get_post_meta($sub_prod_id, '_product_param_carbs', true));
  $nutrients->protein += floatval(get_post_meta($sub_prod_id, '_product_param_protein', true));
  $nutrients->fat += floatval(get_post_meta($sub_prod_id, '_product_param_fat', true));
  $nutrients->fibre += floatval(get_post_meta($sub_prod_id, '_product_param_fibre', true));
  return $nutrients;
}
