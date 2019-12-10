<?php
session_start();
WC()->cart->calculate_totals();
if(!isset($_SESSION['add_saturday'])){
	wp_redirect(esc_url(site_url('/shop')));
  exit;
}
get_header('pages') ?>

<main class="main-content rev-and-confirm">
  <?=do_shortcode("[woocommerce_checkout]")?>
</main>

<?php get_footer() ?>
