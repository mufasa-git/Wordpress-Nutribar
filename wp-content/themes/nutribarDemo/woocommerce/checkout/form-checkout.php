<?php
if(!defined('ABSPATH')) exit;
?>
<style>
	.woocommerce-notices-wrapper,
	.woocommerce-message {display: none;}
</style>
<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" target="_parent">
<?php
global $week_days, $courses;
$d_time = get_user_meta(get_current_user_id(),'delivery_time',true);

$user_meta = array_map(function($a){return $a[0];}, get_user_meta(get_current_user_id()));

$address = $user_meta['billing_city'] .
            ($user_meta['billing_postcode'] ? (', ' . $user_meta['billing_postcode']) : '') .
            ($user_meta['address'] ? (', ' . $user_meta['address']) : '') .
            ($user_meta['building_house_number'] ? (', ' . $user_meta['building_house_number']) : '') .
            ($user_meta['flat_number'] ? (', ' . $user_meta['flat_number']) : '') .
            ($user_meta['floor_or_level'] ? (', ' . $user_meta['floor_or_level']) : '') .
            ($user_meta['area'] ? (', ' . $user_meta['area']) : '');
$w_days_text = !isset($_SESSION['add_saturday']) || $_SESSION['add_saturday'] == "0" ? 'Mon - Fri' : 'Mon - Sat';
?>
<input type="hidden" name="billing_first_name" value="<?=wp_get_current_user()->display_name?>">
<input type="hidden" name="billing_last_name" value="Doe">
<input type="hidden" name="billing_country" value="CY">
<input type="hidden" name="billing_address_1" value="<?=$user_meta['address'].', '.$user_meta['building_house_number']?>">
<input type="hidden" name="billing_address_2" value="<?=$user_meta['flat_number']?>">
<input type="hidden" name="billing_city" value="<?=$user_meta['billing_city']?>">
<input type="hidden" name="billing_state" value="none">
<input type="hidden" name="billing_postcode" value="<?=$user_meta['billing_postcode']?>">
<input type="hidden" name="billing_phone" value="<?=$user_meta['phone-number']?>">
<input type="hidden" name="billing_email" value="<?=wp_get_current_user()->user_email?>">
<div class="summary">
	<p class="summary__title">Summary</p>
	<table class="summary__order-info">
		<tr>
			<td class="summary__left-columns">Subtotal</td>
			<td class="summary__right-columns"><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>
		<tr>
			<?php
			$package = WC()->shipping()->get_packages()[0];
			$method = array_shift($package['rates']);
			?>
			<td class="summary__left-columns">Delivery charge</td>
			<td class="summary__right-columns">
				<input type="hidden" name="shipping_method[0]" data-index="0" id="shipping_method_0_<?=esc_attr(sanitize_title($method->id))?>" value="<?=esc_attr($method->id)?>" class="shipping_method" />
				<?=wc_cart_totals_shipping_method_label($method)?>
			</td>
		</tr>
		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<tr>
						<td class="summary__left-columns">Includes VAT of</td>
						<td class="summary__right-columns"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>
		<?php
			$delivery_nutripoints = 20;
			$total_nutripoints = $_SESSION['subtotal_nutripoints']+$delivery_nutripoints;
			$_SESSION['total_nutripoints'] = $total_nutripoints;
		?>
		<tr class="summary__total">
			<td class="summary__left-columns">Total nutripoints</td>
			<td class="summary__right-columns"><?=mycred()->format_creds($total_nutripoints)?></td>
		</tr>
		<tr class="summary__total">
			<td class="summary__left-columns">Total</td>
			<td class="summary__right-columns"><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>
	</table>
</div>
<div class="order-details">
	<div class="order-details__main">
		<div class="order-details__deliv-addr">
			<h3 class="order-details__title">Delivery address</h3>
			<p class="order-details__text"><?=$address?></p>
		</div>
		<div class="order-details__est-time">
			<h3 class="order-details__title">Delivery time</h3>
			<p class="order-details__text"><?=$w_days_text?> <?=$d_time?></p>
		</div>
	</div>
</div>
<div class="rev-and-confirm__items">

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
	}?>
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

</div>
<?php
// show standart form of payment methods
// $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
// if(!empty($available_gateways)){
// 	foreach($available_gateways as $gateway){
// 		wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
// 	}
// }
?>
<div class="rev-and-confirm__btn">
	<input type="hidden" name="payment_method" value="mypos_virtual">
	<?php //if(mycred_get_users_cred(get_current_user_id()) >= $total_nutripoints):?>
		<input class="form-simple-submit" type="submit" name="woocommerce_checkout_place_order" id="place_order" value="Confirm" data-value="Confirm">
		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	<?php //endif; ?>
</div>
</form>
