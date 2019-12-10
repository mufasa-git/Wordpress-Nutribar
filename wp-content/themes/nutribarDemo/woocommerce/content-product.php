<?php
defined('ABSPATH') || exit;
global $product;

// Ensure visibility.
if(empty($product) || !$product->is_visible()){
	return;
}
if(has_term('week', 'product_cat')){
	$period = "Week";
}else if(has_term('month', 'product_cat')){
	$period = "Month";
}
?>

<li class="dish_item">
	<a href="<?=site_url('/menu-for-a-week?menu='.$product->get_id().'&day=monday&week=1&add_saturday=0&fillall=1')?>">
	<div class="dish_item_top">
		<div class="dish_item_left">
			<div class="dish_item_title"><?=$product->get_title()?></div>
			<div class="dish_item_price">
				<div class="dish_item_value"><?=$product->get_price()?></div>
				<div class="dish_item_measure"><?=get_woocommerce_currency()?><?=(empty($period) ? '' : "/".$period)?></div>
			</div>
		</div>
		<div class="dish_item_right">
			<img class="dish-dish_item_image" src="<?=get_the_post_thumbnail_url($product->get_id(),'thumbnail')?>">
		</div>
	</div>
	<div class="dish_item_bottom">
		<?=$product->get_description()?>
	</div>
	</a>
</li>
