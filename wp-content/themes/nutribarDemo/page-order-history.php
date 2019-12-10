<?php
get_header('pages'); ?>
<?php
$customer_orders = wc_get_orders(array(
  'customer' => get_current_user_id()
));
?>
<main class="main-content main-order-history-items">
  <div class="main-shopping-cart-items__items">
  <?php foreach ($customer_orders as $customer_order):
    $order      = wc_get_order($customer_order);
    $item_count = $order->get_item_count();
  ?>
    <div class="sh-cart-card week_card" data-id="dropdown_order_<?=$order->get_id()?>">
      <div class="sh-cart-card_data_wrapper">
        <h2>Order <span class="green">#<?=$order->get_order_number()?></span></h2>
        <p class="order_date"><?=esc_html(wc_format_datetime($order->get_date_created()))?></p>
      </div>
      <div class="order_price"><?=$order->get_formatted_order_total()?></div>
    </div>
    <div class="sh-cart-card__dropdown" id="dropdown_order_<?=$order->get_id()?>">
    <?php foreach ($order->get_items() as $item_id => $item):
      if(wc_get_order_item_meta($item_id, '_hidden') == "1") continue;
      $product_id = $item->get_product_id();
      $_product = wc_get_product($product_id);
    ?>
      <?php if($_product->is_type('menu')): ?>
    	<div class="sh-cart-card order_item week_card" data-id="dropdown_<?=$item_id?>">
    		<div class="sh-cart-card__pic sh-cart-card__pic--calendar">
    			<svg class="svg-sprite-icon icon-calendar">
    				<use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg') ?>#calendar"></use>
    			</svg>
    		</div>
    		<div class="sh-cart-card__dish-name sh-cart-card__dish-name--week"><?=$item['name']?></div>
    		<div class="sh-cart-card__price"><?=wc_price($item->get_total())?></div>
        <div class="dish-card__btn">
          <button class="little-gr-btn-dl action_repeat_order" data-order="<?=$order->get_id()?>" data-prod="<?=$product_id?>">repeat</button>
        </div>
    	</div>
      <div class="sh-cart-card__dropdown" id="dropdown_<?=$item_id?>">
      <?php for($i=1;$i<=4;$i++): ?>
      	<?php foreach($week_days as $week_day):
          if(empty(get_post_meta($order->get_id(), 'menu_'.$week_day.'_'.$i, true))) continue;?>
      		<div class="sh-cart-card subproduct week_card" data-id="dropdown_<?=$item_id?>_<?=$week_day?>_<?=$i?>">
      			<h2><?=ucfirst($week_day)?></h2>
      		</div>
      		<div class="sh-cart-card__dropdown" id="dropdown_<?=$item_id?>_<?=$week_day?>_<?=$i?>">
      		<?php foreach($courses as $course):
            if(!array_key_exists($course->slug,get_post_meta($order->get_id(), 'menu_'.$week_day.'_'.$i, true))) continue;
            $sub_prod_id = get_post_meta($order->get_id(), 'menu_'.$week_day.'_'.$i, true)[$course->slug];
            if(empty($sub_prod_id)) continue;
            $sub_prod = wc_get_product($sub_prod_id);
            if($sub_prod):
      		?>
      			<div class="sh-cart-card subproduct">
      				<img class="sh-cart-card__pic" src="<?=get_the_post_thumbnail_url($sub_prod_id,'thumbnail')?>" srcset="<?=get_the_post_thumbnail_url($sub_prod_id,'thumbnail')?> 2x">
      				<div class="sh-cart-card__dish-name"><strong><?=$course->title?>:</strong> <?=$sub_prod->get_title()?></div>
      			</div>
          <?php else: ?>
            <div class="sh-cart-card subproduct">
              Product #<?=$sub_prod_id?> is not found
            </div>
          <?php endif; ?>
      		<?php endforeach; ?>
      		</div>
      	<?php endforeach;?>
      <?php endfor;?>
      </div>
    	<?php else: ?>
    		<div class="sh-cart-card order_item">
    			<img class="sh-cart-card__pic" src="<?=get_the_post_thumbnail_url($_product->get_id(),'thumbnail')?>" srcset="<?=get_the_post_thumbnail_url($_product->get_id(),'thumbnail')?> 2x">
    			<div class="sh-cart-card__dish-name sh-cart-card__dish-name--week"><?=$_product->get_title()?></div>
    			<div class="sh-cart-card__price"><?=WC()->cart->get_product_price($_product)?></div>
    		</div>
    	<?php endif;?>
    <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  </div>
</main>
<script>
jQuery(document).ready(function($){
  $(".action_repeat_order").on("click",function(e){
    e.stopPropagation();
    var order = $(this).attr("data-order");
    var prod = $(this).attr("data-prod");
    $.ajax({
      url: '<?=admin_url("admin-ajax.php")?>',
      type: 'POST',
      data: "action=repeat_order&order="+order,
      success: function(data){
        window.location.href="/menu-for-a-week/?menu="+prod+"&day=monday&week=1&add_saturday=0";
      }
    });
  });
});
</script>
<?php get_footer(); ?>
