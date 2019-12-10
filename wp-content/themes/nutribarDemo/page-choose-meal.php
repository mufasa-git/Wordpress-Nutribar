<?php
session_start();
if (isset($_GET['day'])) {
  $day = esc_html($_GET['day']);
  $week = esc_html($_GET['week']);
  $course = esc_html($_GET['course']);
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
  $sub_prod_ids = get_post_meta($product_menu_id, 'menu_'.$day, true)['variation'][$course];
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
          <?php
            $param = '';
            if(has_term('main-dish', 'product_cat', $sub_prod_id)){
              $param .= 'sides_dishes';
            }
            if(has_term('salad', 'product_cat', $sub_prod_id)){
              $param .= 'dressing_salad';
            }
            $default_selected = 0;
            if(empty($param)){
              $default_selected = get_post_meta($product_menu_id, 'menu_'.$day, true)['default'][$course][0];
            }
            $sub_prod_selected = isset($_SESSION['menu_'.$day.'_'.$week.'_'.$course]) ? $_SESSION['menu_'.$day.'_'.$week.'_'.$course] : $default_selected;
          ?>
          <input class="black-checkbox__input <?=(empty($param) ? '' : 'choose_submeal')?>" data-meal="<?=$param?>" type="radio" name="chosen_dish" <?=$sub_prod_id == $sub_prod_selected ? 'checked' : ''?> value="<?=$sub_prod_id?>" id="sub_prod_<?=$sub_prod_id?>">
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
  <input type="hidden" name="action" value="save_chosen_dish">
  </form>
  <div class="main-menu-week__controls">
    <button class="form-simple-submit save_chosen_dish">Continue</button>
  </div>
</main>
<script>
jQuery(function($){
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
  $('.choose_submeal').change(function(){
    var meal_type = $(this).attr("data-meal");
    $.ajax({
      url: '<?php echo admin_url("admin-ajax.php") ?>',
      type: 'POST',
      dataType: 'json',
      data: $("form").serialize(),
      success: function(data){
        window.location.href = '<?=site_url('/choose-submeal?day='.$day.'&week='.$week.'&course='.$course.'&meal=')?>'+meal_type;
      }
    });
    return false;
  });
});
</script>
<?php get_footer(); ?>
