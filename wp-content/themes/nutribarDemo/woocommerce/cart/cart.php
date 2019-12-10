<?php
defined( 'ABSPATH' ) || exit;
global $week_days, $courses;
?>

<?php $cart_count = WC()->cart->get_cart_contents_count();?>
<div class="main-shopping-cart-items__subtotal subtotal"><span class="subtotal__main">Subtotal (<?=$cart_count?> <?=$cart_count == 1 ? 'item' : 'items' ?>): </span><span class="subtotal__bold"><?php wc_cart_totals_subtotal_html(); ?></span></div>
<div class="main-shopping-cart-items__items">
	<?php foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item):
		$_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
		$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
	?>
	<?php if($_product->is_type('menu')): ?>
	<div class="sh-cart-card">
		<div class="sh-cart-card__pic sh-cart-card__pic--calendar">
			<svg class="svg-sprite-icon icon-calendar">
				<use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg') ?>#calendar"></use>
			</svg>
		</div>
		<div class="sh-cart-card__dish-name sh-cart-card__dish-name--week"><?=$_product->get_title()?></div>
		<div class="sh-cart-card__price"><?=WC()->cart->get_product_price($_product)?></div>
		<div class="sh-cart-card__remove">
			<a href="<?=wc_get_cart_remove_url($cart_item_key)?>">
				<svg class="svg-sprite-icon icon-close">
					<use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg') ?>#close"></use>
				</svg>
			</a>
		</div>
	</div>
	<?php $weeks = 0;
	if(has_term('week', 'product_cat', $product_id)){
		$weeks = 1;
	}else if(has_term('month', 'product_cat', $product_id)){
	  if(isset($_SESSION['full_month']) && $_SESSION['full_month'] == "1"){
	    $weeks = 4;
	  }else{
	    $weeks = 1;
	  }
	}
	$subtotal_nutripoints = 0;
	?>
	<?php for($i=1;$i<=$weeks;$i++): ?>
		<?php foreach($week_days as $week_day): if($_SESSION['add_saturday'] == "0" && $week_day=="saturday") continue;?>
			<div class="sh-cart-card subproduct week_card" data-id="dropdown_<?=$week_day?>_<?=$i?>">
				<h2><?=ucfirst($week_day)?></h2>
			</div>
			<div class="sh-cart-card__dropdown" id="dropdown_<?=$week_day?>_<?=$i?>">
			<?php foreach($courses as $course):
				if($course->type == 'sub_category'){
					continue;
				}else{
					if(count(get_post_meta($_product->get_id(), 'menu_'.$week_day, true)['default'][$course->slug]) == 0) continue;
				}
				$subtotal_nutripoints += $course->nutripoints;
				$sub_prod_id = isset($_SESSION['menu_'.$week_day.'_'.$i.'_'.$course->slug]) ? $_SESSION['menu_'.$week_day.'_'.$i.'_'.$course->slug] : get_post_meta($_product->get_id(), 'menu_'.$week_day, true)['default'][$course->slug][0];
				$sub_prod = wc_get_product($sub_prod_id);
			?>
				<div class="sh-cart-card subproduct">
					<img class="sh-cart-card__pic" src="<?=get_the_post_thumbnail_url($sub_prod_id,'thumbnail')?>" srcset="<?=get_the_post_thumbnail_url($sub_prod_id,'thumbnail')?> 2x">
					<div class="sh-cart-card__dish-name"><strong><?=$course->title?>:</strong> <?=$sub_prod->get_title()?></div>
				</div>
				<?php
				if(has_term('main-dish', 'product_cat', $sub_prod_id)){
					$param = $courses[array_search('sides_dishes', array_column($courses, 'slug'))];
				}
				if(has_term('salad', 'product_cat', $sub_prod_id)){
					$param = $courses[array_search('dressing_salad', array_column($courses, 'slug'))];
				}
				if(isset($param)):
					$sub_prod_id = isset($_SESSION['menu_'.$week_day.'_'.$i.'_'.$param->slug]) ? $_SESSION['menu_'.$week_day.'_'.$i.'_'.$param->slug] : get_post_meta($_product->get_id(), 'menu_'.$week_day, true)['default'][$param->slug][0];
					$sub_prod = wc_get_product($sub_prod_id);
				?>
				<div class="sh-cart-card subproduct">
					<img class="sh-cart-card__pic" src="<?=get_the_post_thumbnail_url($sub_prod_id,'thumbnail')?>" srcset="<?=get_the_post_thumbnail_url($sub_prod_id,'thumbnail')?> 2x">
					<div class="sh-cart-card__dish-name"><strong><?=$param->title?>:</strong> <?=$sub_prod->get_title()?></div>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
			</div>
		<?php endforeach;?>
	<?php endfor;?>
	<?php else: ?>
		<div class="sh-cart-card">
			<img class="sh-cart-card__pic" src="<?=get_the_post_thumbnail_url($_product->get_id(),'thumbnail')?>" srcset="<?=get_the_post_thumbnail_url($_product->get_id(),'thumbnail')?> 2x">
			<div class="sh-cart-card__dish-name sh-cart-card__dish-name--week"><?=$_product->get_title()?></div>
			<div class="sh-cart-card__price"><?=WC()->cart->get_product_price($_product)?></div>
			<div class="sh-cart-card__remove">
				<a href="<?=wc_get_cart_remove_url($cart_item_key)?>">
					<svg class="svg-sprite-icon icon-close">
						<use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg') ?>#close"></use>
					</svg>
				</a>
			</div>
		</div>
	<?php endif;?>
	<?php endforeach; ?>
	<?php
		$_SESSION['subtotal_nutripoints'] = $subtotal_nutripoints;
	?>
</div>
<div class="main-shopping-cart-items__controls"><a class="underlined-btn" href="<?=site_url("/shop")?>">Continue shopping</a>
	<?php if(wc_coupons_enabled()): ?>
		<form action="<?=esc_url(wc_get_cart_url())?>" method="post">
		<div class="main-shopping-cart-items__code">
			<input class="main-shopping-cart-items__input" type="text" name="coupon_code" placeholder="Voucher code">
			<div class="main-shopping-cart-items__apply">
				<button type="submit" name="apply_coupon" value="Apply coupon" class="form-simple-submit form-simple-submit--dark">Apply</button>
			</div>
		</div>
		</form>
	<?php endif; ?>
	<a style="display:block;margin-top:auto;" class="form-simple-submit" href="<?=esc_url(wc_get_checkout_url())?>">Check Out</a>
</div>
