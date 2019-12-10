<?php
session_start();
if (isset($_GET['day'])) {
  $day = esc_html($_GET['day']);
  $week = esc_html($_GET['week']);
  $course = esc_html($_GET['course']);
  $meal = esc_html($_GET['meal']);
}else{
  exit;
}
if(isset($_SESSION['menu_id'])){
  $product_menu_id = $_SESSION['menu_id'];
}else{
  wp_redirect(esc_url(site_url('/shop')));
  exit;
}
get_header('pages');
?>
<main class="main-content main-ch-breakfast">
  <?php
  // Get simple products from current menu product course
  $sub_prod_ids = get_post_meta($product_menu_id, 'menu_'.$day, true)['variation'][$meal];
  $sub_prod_selected = isset($_SESSION['menu_'.$day.'_'.$week.'_'.$meal]) ? $_SESSION['menu_'.$day.'_'.$week.'_'.$meal] : get_post_meta($product_menu_id, 'menu_'.$day, true)['default'][$meal][0];
  ?>
  <form>
  <?php foreach ($sub_prod_ids as $sub_prod_id):
    $sub_prod = wc_get_product($sub_prod_id);
  ?>
  <div class="dish-card dish-card-detailed">
    <div class="dish-card__main dish-card__main--pr1">
      <div class="dish-card-detailed__left-block">
        <img class="dish-card__pic" src="<?=get_the_post_thumbnail_url($sub_prod_id)?>" data-img-base="4.2rem 4.2rem"
        data-img-big="100% 100%">
        <div class="dish-card__dish-name roboto-n-12-14"><?=$sub_prod->get_title()?></div>
      </div>
      <div class="dish-card-detailed__right-block">
        <div class="black-checkbox">
          <input class="black-checkbox__input" type="radio" name="chosen_dish" <?=$sub_prod_id == $sub_prod_selected ? 'checked' : ''?> value="<?=$sub_prod_id?>" id="sub_prod_<?=$sub_prod_id?>">
          <label class="black-checkbox__label" for="sub_prod_<?=$sub_prod_id?>">
            <svg class="svg-sprite-icon icon-tick" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid meet">
              <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg') ?>#tick"></use>
            </svg>
          </label>
        </div>
      </div>
    </div>
    <div class="dish-card__dropdown dish-card-detailed__dropdown">
      <div class="dish-card-detailed__dish-name-drdwn"><?=$sub_prod->get_title()?></div>
      <div class="dish-card-detailed__descr"><span class="dish-card-detailed__top-descr-bold">Ingredients: </span><span class="dish-card-detailed__top-descr"><?=get_the_excerpt($sub_prod_id)?></span></div>
      <div class="dish-card-detailed__descr"><span class="dish-card-detailed__top-descr-bold">Category: </span><span class="dish-card-detailed__top-descr"><?=implode(", ",array_map(function($item){return $item->name;},get_the_terms($sub_prod_id, 'product_cat')))?></span></div>
      <table class="dish-card__table roboto-500-12-14">
        <tr>
          <td>Calories</td>
          <td><?=get_post_meta($sub_prod_id, '_product_param_calories', true)?> Kcal</td>
        </tr>
        <tr>
          <td>Carbs</td>
          <td><?=get_post_meta($sub_prod_id, '_product_param_carbs', true)?>g</td>
        </tr>
        <tr>
          <td>Protein</td>
          <td><?=get_post_meta($sub_prod_id, '_product_param_protein', true)?>g</td>
        </tr>
        <tr>
          <td>Fat</td>
          <td><?=get_post_meta($sub_prod_id, '_product_param_fat', true)?>g</td>
        </tr>
        <tr>
          <td>Fibre</td>
          <td><?=get_post_meta($sub_prod_id, '_product_param_fibre', true)?>g</td>
        </tr>
      </table>
    </div>
  </div>
  <?php endforeach; ?>
  <input type="hidden" name="day" value="<?=$day?>">
  <input type="hidden" name="week" value="<?=$week?>">
  <input type="hidden" name="course" value="<?=$course?>">
  <input type="hidden" name="meal" value="<?=$meal?>">
  <input type="hidden" name="action" value="save_chosen_subdish">
  </form>
  <div class="main-menu-week__controls">
    <button class="form-simple-submit save_chosen_dish">Continue</button>
  </div>
</main>
<script>
jQuery(function($){
  if(<?=($meal=='sides_dishes' ? 'true' : 'false')?>){
    $(".header-control__title").html('Side dish');
    $("title").html('Side dish - <?=get_bloginfo('name')?>');
  }
  if(<?=($meal=='dressing_salad' ? 'true' : 'false')?>){
    $(".header-control__title").html('Dressing for salad');
    $("title").html('Dressing for salad - <?=get_bloginfo('name')?>');
  }
  $('.save_chosen_dish').click(function(){
    $.ajax({
      url: '<?php echo admin_url("admin-ajax.php") ?>',
      type: 'POST',
      dataType: 'json',
      data: $("form").serialize(),
      success: function(data){
        window.history.back();
      }
    });
    return false;
  });
});
</script>
<?php get_footer(); ?>
