<?php
global $nutri_main_page, $nutri_chef_page, $nutri_delivery_page;
$nutri_main_page = 'nutribar-reports.php';
$nutri_chef_page = 'nutribar-chef-reports.php';
$nutri_delivery_page = 'nutribar-delivery-reports.php';

add_action('admin_menu', 'nutribar_report_menu');
function nutribar_report_menu() {
  global $nutri_main_page, $nutri_chef_page, $nutri_delivery_page;
  add_menu_page('Nutribar reports', 'Nutribar reports', 'manage_options', $nutri_main_page, 'nutribar_report_chef_admin_page', 'dashicons-clipboard', 59);
  add_submenu_page($nutri_main_page, 'Report for Chef', 'Report for Chef', 'manage_options', $nutri_chef_page, 'nutribar_report_chef_admin_page');
  add_submenu_page($nutri_main_page, 'Food Delivery', 'Food Delivery', 'manage_options', $nutri_delivery_page, 'nutribar_report_delivery_admin_page');
  remove_submenu_page($nutri_main_page,$nutri_main_page);
}
function nutribar_report_chef_admin_page(){
  global $nutri_chef_page, $courses;
	?>
	<div class="wrap">
		<h1>Nutribar report for chef</h1>
    <hr>
    <form action="<?php menu_page_url($nutri_chef_page) ?>" method="get">
      <input type="date" name="date_from" value="<?=$_GET['date_from']?>">
      <input type="date" name="date_to" value="<?=$_GET['date_to']?>">
      <input type="submit" class="button" name="generate" value="Generate">
      <input type="hidden" name="page" value="<?=$nutri_chef_page?>">
    </form>
    <hr>
    <?php
    $begin = new DateTime($_GET['date_from']);
    $end = new DateTime($_GET['date_to']);
    $end = $end->modify( '+1 day' );
    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($begin, $interval ,$end);

    foreach($daterange as $date):
      $orders = new WP_Query(
        array(
          'post_type' => 'shop_order',
          'posts_per_page' => '-1',
          'post_status' => array('wc-processing', /*'wc-on-hold', 'wc-pending', 'wc-completed'*/),
          'meta_query' => array(
            'relation' => 'AND',
            array(
              'key' => 'menu_start',
              'value' => $date->format("Y-m-d"),
              'compare' => '<=',
              'type' => 'DATE'
            ),
            array(
              'key' => 'menu_finish',
              'value' => $date->format("Y-m-d"),
              'compare' => '>=',
              'type' => 'DATE'
            )
          )
        )
      );?>
      <?php if($orders->have_posts()):?>
        <div class="nutribar_report_date"><?=$date->format("jS \of F")?></div>
        <table class="wp-list-table widefat fixed striped">
          <thead>
            <tr>
              <th style="width:40%">Food name</th>
              <th>Meal type</th>
              <th style="width:40%">Ingredients</th>
              <th style="width:8%">Total</th>
            </tr>
          </thead>
          <tbody>
        <?php
        $meals = [];
        while($orders->have_posts()){
          $orders->the_post();
          $order = wc_get_order(get_the_ID());

          $menu_start = new DateTime(get_post_meta($order->get_id(), 'menu_start', true));
          $week_day = strtolower($date->format("l"));
          $days = date_diff($menu_start,$date)->format('%a')+1;
          $week = ceil($days / 7);
          foreach($courses as $course){
            if(!array_key_exists($course->slug,get_post_meta($order->get_id(), 'menu_'.$week_day.'_'.$week, true))) continue;
            $sub_prod_id = get_post_meta($order->get_id(), 'menu_'.$week_day.'_'.$week, true)[$course->slug];
            if(empty($sub_prod_id)) continue;
            $sub_prod = wc_get_product($sub_prod_id);
            if($sub_prod){
              if(array_key_exists($sub_prod_id,$meals)){
                $meals[$sub_prod_id]->total = $meals[$sub_prod_id]->total+1;
              }else{
                $meals[$sub_prod_id]->total = 1;
              }
              $meals[$sub_prod_id]->title = $sub_prod->get_title();
              $meals[$sub_prod_id]->course = $course->title;
            }
          }
        }?>
        <?php foreach($meals as $meal): ?>
          <tr>
            <td><?=$meal->title?></td>
            <td><?=$meal->course?></td>
            <td></td>
            <td><?=$meal->total?></td>
          </tr>
        <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif;?>
    <?php endforeach;?>
	</div>
	<?php
}

function nutribar_report_delivery_admin_page(){
  global $nutri_delivery_page, $courses;
	?>
	<div class="wrap">
		<h1>Nutribar report for delivery</h1>
    <hr>
    <form action="<?php menu_page_url($nutri_delivery_page) ?>" method="get">
      <input type="date" name="date_from" value="<?=$_GET['date_from']?>">
      <input type="date" name="date_to" value="<?=$_GET['date_to']?>">
      <input type="submit" class="button" name="generate" value="Generate">
      <input type="hidden" name="page" value="<?=$nutri_delivery_page?>">
    </form>
    <hr>
    <?php
    $begin = new DateTime($_GET['date_from']);
    $end = new DateTime($_GET['date_to']);
    $end = $end->modify( '+1 day' );
    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($begin, $interval ,$end);

    foreach($daterange as $date):
      $orders = new WP_Query(
        array(
          'post_type' => 'shop_order',
          'posts_per_page' => '-1',
          'post_status' => array('wc-processing', /*'wc-on-hold', 'wc-pending', 'wc-completed'*/),
          'meta_query' => array(
            'relation' => 'AND',
            array(
              'key' => 'menu_start',
              'value' => $date->format("Y-m-d"),
              'compare' => '<=',
              'type' => 'DATE'
            ),
            array(
              'key' => 'menu_finish',
              'value' => $date->format("Y-m-d"),
              'compare' => '>=',
              'type' => 'DATE'
            )
          )
        )
      );?>
      <?php if($orders->have_posts()):?>
        <div class="nutribar_report_date"><?=$date->format("jS \of F")?></div>
        <table class="wp-list-table widefat fixed striped">
          <thead>
            <tr>
              <th>Customer full name</th>
              <th>Phone</th>
              <th>Delivery address</th>
              <?php foreach($courses as $course): ?>
              <th><?=$course->title?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
        <?php
        $customers = [];
        while($orders->have_posts()){
          $orders->the_post();
          $order = wc_get_order(get_the_ID());
          $customer_id = get_post_meta($order->get_id(), '_customer_user', true);
          $d_time = get_user_meta($customer_id,'delivery_time',true);

          $user_meta = array_map(function($a){return $a[0];}, get_user_meta($customer_id));

          $address = $user_meta['billing_city'] .
                      ($user_meta['billing_postcode'] ? (', ' . $user_meta['billing_postcode']) : '') .
                      ($user_meta['address'] ? (', ' . $user_meta['address']) : '') .
                      ($user_meta['building_house_number'] ? (', ' . $user_meta['building_house_number']) : '') .
                      ($user_meta['flat_number'] ? (', ' . $user_meta['flat_number']) : '') .
                      ($user_meta['floor_or_level'] ? (', ' . $user_meta['floor_or_level']) : '') .
                      ($user_meta['area'] ? (', ' . $user_meta['area']) : '');
          $customer_data = null;
          $customer_data->name = $user_meta['billing_first_name'];
          $customer_data->phone = $user_meta['phone-number'];
          $customer_data->address = $d_time.' '.$address;

          $menu_start = new DateTime(get_post_meta($order->get_id(), 'menu_start', true));
          $week_day = strtolower($date->format("l"));
          $days = date_diff($menu_start,$date)->format('%a')+1;
          $week = ceil($days / 7);
          foreach($courses as $course){
            if(!array_key_exists($course->slug,get_post_meta($order->get_id(), 'menu_'.$week_day.'_'.$week, true))) continue;
            $sub_prod_id = get_post_meta($order->get_id(), 'menu_'.$week_day.'_'.$week, true)[$course->slug];
            if(empty($sub_prod_id)) continue;
            $sub_prod = wc_get_product($sub_prod_id);
            if($sub_prod){
              $customer_data->products[$course->slug] = $sub_prod->get_title();
            }
          }
          array_push($customers, $customer_data);
        }?>
        <?php foreach($customers as $customer): if(!$customer->products) continue;?>
          <tr>
            <td><?=$customer->name?></td>
            <td><?=$customer->phone?></td>
            <td><?=$customer->address?></td>
            <?php foreach($courses as $course): ?>
            <td><?=$customer->products[$course->slug]?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif;?>
    <?php endforeach;?>
	</div>
	<?php
}


?>
