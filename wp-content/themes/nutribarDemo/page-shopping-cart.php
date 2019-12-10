<?php
session_start();
WC()->cart->calculate_totals();
if(!isset($_SESSION['add_saturday'])){
	wp_redirect(esc_url(site_url('/shop')));
  exit;
}
get_header('pages'); ?>

<main class="main-content main-shopping-cart-items">
  <?=do_shortcode("[woocommerce_cart]")?>
</main>

<?php get_footer(); ?>
