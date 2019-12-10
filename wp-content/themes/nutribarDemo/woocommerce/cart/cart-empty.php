<?php
defined( 'ABSPATH' ) || exit;
?>

<div class="nutribar_empty_cart_wrapper">
	<img class="nutribar_empty_cart_image" src="<?=get_theme_file_uri('assets/img/open-box.png') ?>">
	<div class="nutribar_empty_cart_text">
		Your shopping cart is empty</br>
		Add items to continue shopping.
	</div>
</div>
<div class="rev-and-confirm__btn">
  <a style="display: block;" class="form-simple-submit" href="<?=esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop')))?>">Continue shopping</a>
</div>
