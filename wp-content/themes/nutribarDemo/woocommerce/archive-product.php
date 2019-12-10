<?php
defined('ABSPATH') || exit;
if (!is_user_logged_in()) {
  wp_redirect(esc_url(site_url('/login')));
  exit;
}
$d_time = get_user_meta(get_current_user_id(),'delivery_time',true);

$user_meta = array_map(function($a){return $a[0];}, get_user_meta(get_current_user_id()));
if(empty($user_meta['billing_city']) || empty($user_meta['address']) || empty($user_meta['building_house_number'])){
  wp_redirect(esc_url(site_url('/delivery-address')));
  exit;
}

$address = $user_meta['billing_city'] .
            ($user_meta['billing_postcode'] ? (', ' . $user_meta['billing_postcode']) : '') .
            ($user_meta['address'] ? (', ' . $user_meta['address']) : '') .
            ($user_meta['building_house_number'] ? (', ' . $user_meta['building_house_number']) : '') .
            ($user_meta['flat_number'] ? (', ' . $user_meta['flat_number']) : '') .
            ($user_meta['floor_or_level'] ? (', ' . $user_meta['floor_or_level']) : '') .
            ($user_meta['area'] ? (', ' . $user_meta['area']) : '');
session_start();
WC()->cart->empty_cart();
$_SESSION = array();
get_header('pages');
?>
<div class="order-details">
	<div class="order-details__main">
		<div class="order-details__deliv-addr">
			<h3 class="order-details__title">Delivery address</h3>
			<p class="order-details__text"><?=$address?></p>
		</div>
		<div class="order-details__est-time">
			<h3 class="order-details__title">Delivery time</h3>
			<p class="order-details__text">Mon - Fri <?=$d_time?></p>
		</div>
	</div>
	<div class="address_edit_wrapper">
		<a href="/address-book" class="little-gr-btn-dl">edit</a>
	</div>
</div>
<main class="main-content main-menu-week">
<?php
if(woocommerce_product_loop()){
	while(have_posts()){
		the_post();
		wc_get_template_part('content', 'product');
	}
}else{
	do_action('woocommerce_no_products_found');
}
?>
</main>
<?php
get_footer();
