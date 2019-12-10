<?php
session_start();
if (isset($_GET['day'])) {
  $day = esc_html($_GET['day']);
  $week = esc_html($_GET['week']);
}else{
  exit;
}
if(isset($_GET['menu'])){
  $_SESSION['menu_id'] = intval($_GET['menu']);
}
if(isset($_SESSION['menu_id'])){
  $product_menu_id = $_SESSION['menu_id'];
}else{
  wp_redirect(esc_url(site_url('/shop')));
  exit;
}
if(isset($_GET['fillall'])){
  $_SESSION['full_month'] = $_GET['fillall'];
}
if(isset($_GET['add_saturday'])){
  $_SESSION['add_saturday'] = $_GET['add_saturday'];
}
?>

<?php get_header('pages'); ?>
<?php
global $week_days, $courses;
$start_date = new DateTime('next monday');
$weeks = 0;
if(has_term('week', 'product_cat', $product_menu_id)){
	$weeks = 1;
}else if(has_term('month', 'product_cat', $product_menu_id)){
  if(isset($_SESSION['full_month']) && $_SESSION['full_month'] == "1"){
    $weeks = 4;
  }else{
    $weeks = 1;
  }
}
?>
<div class="days-bar">
  <div class="days-bar__slide">
    <?php for($i=1;$i<=$weeks;$i++): ?>
      <?php foreach($week_days as $wday): ?>
        <?php if((!isset($_SESSION['add_saturday']) || $_SESSION['add_saturday'] == "0") && $wday=="saturday"){$start_date->modify("+2 day"); continue; } ?>
        <a href="<?=site_url('/menu-for-a-week?day='.$wday.'&week='.$i)?>" class="days-bar__btn <?=$day == $wday && $week == $i ? 'days-bar__btn--active' : ''?>">
          <span class="days-bar__day-name"><?=$wday?></span>
          <span class="days-bar__day-date"><?=$start_date->format('j F')?></span>
        </a>
        <?php if($wday=="saturday"){
          $start_date->modify("+2 day");
        }else{
          $start_date->modify("+1 day");
        } ?>
      <?php endforeach; ?>
    <?php endfor; ?>
  </div>
</div>
<main class="main-content main-menu-week">
  <div class="main-menu-week__list">
    <?php
      $nutrients = (object)[];
    ?>
    <?php foreach($courses as $course):
      if($course->type == 'sub_category') continue;
      if(count(get_post_meta($product_menu_id, 'menu_'.$day, true)['default'][$course->slug]) == 0) continue;
    ?>
      <div class="dish-card">
        <div class="dish-card__cathegory">&ndash;&nbsp;<span><?=$course->title?></span><span class="move_right"><?=mycred()->format_creds($course->nutripoints)?></span></div>

        <?php
        // Get first simple product from current menu product course
        $sub_prod_id = isset($_SESSION['menu_'.$day.'_'.$week.'_'.$course->slug]) ? $_SESSION['menu_'.$day.'_'.$week.'_'.$course->slug] : get_post_meta($product_menu_id, 'menu_'.$day, true)['default'][$course->slug][0];
        $sub_prod = wc_get_product($sub_prod_id);
        ?>
        <div class="dish-card__main">
          <img class="dish-card__pic" src="<?=get_the_post_thumbnail_url($sub_prod_id)?>">
          <div class="dish-card__dish-name roboto-n-12-14"><?=$sub_prod->get_title()?></div>
          <div class="dish-card__btn">
            <a class="little-gr-btn-dl" href="<?= site_url('/choose-meal?day='.$day.'&week='.$week.'&course='.$course->slug) ?>">choose</a>
          </div>
        </div>
      </div>
      <?php
        if(has_term('main-dish', 'product_cat', $sub_prod_id)){
          $param = $courses[array_search('sides_dishes', array_column($courses, 'slug'))];
        }
        if(has_term('salad', 'product_cat', $sub_prod_id)){
          $param = $courses[array_search('dressing_salad', array_column($courses, 'slug'))];
        }
        if(isset($param) && isset($_SESSION['menu_'.$week_day.'_'.$i.'_'.$param->slug])){
          $sub_prod_id =  $_SESSION['menu_'.$week_day.'_'.$i.'_'.$param->slug];
          $nutrients = add_nutrients($sub_prod_id,$nutrients);
        }
        $nutrients = add_nutrients($sub_prod_id,$nutrients);
      ?>
    <?php endforeach; ?>
    <div class="dish-card">
      <div class="dish-card__nutrients-title lato-b-12-20-up">Total Nutrients</div>
      <table class="dish-card__table roboto-500-12-14">
        <tr>
          <td>Calories</td>
          <td><?=$nutrients->calories?> Kcal</td>
        </tr>
        <tr>
          <td>Carbs</td>
          <td><?=$nutrients->carbs?>g</td>
        </tr>
        <tr>
          <td>Protein</td>
          <td><?=$nutrients->protein?>g</td>
        </tr>
        <tr>
          <td>Fat</td>
          <td><?=$nutrients->fat?>g</td>
        </tr>
        <tr>
          <td>Fibre</td>
          <td><?=$nutrients->fibre?>g</td>
        </tr>
      </table>
    </div>
  </div>


  <div class="main-menu-week__controls">
    <?php if(has_term('month', 'product_cat', $product_menu_id)):
      $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    ?>
    <a style="display:block;" href="<?=addToURL('fillall', ($_SESSION['full_month'] == "1" ? '0' : '1'), $actual_link)?>">
      <button class="form-simple-submit form-simple-submit--dark <?=($_SESSION['full_month'] !== "1" ? 'active' : '')?>">Autocomplete menu</button>
    </a>
    <?php endif; ?>
    <?php
    $current_index = array_search($day, $week_days);
    $current_week = $week;
    $sat_slice = ((!isset($_SESSION['add_saturday']) || $_SESSION['add_saturday'] == "0") ? 1 : 0);
    if($current_index + 1 >= count($week_days)-$sat_slice){
      $next_day = $week_days[0];
      if($current_week >= $weeks){
        $current_week = 1;
      }else{
        $current_week +=1;
      }
    }else{
      $next_day = $week_days[$current_index + 1];
    }
    ?>
    <a style="display:block;" href="<?=site_url('/menu-for-a-week?day='.$next_day.'&week='.$current_week)?>">
      <button class="form-simple-submit form-simple-submit--dark">Next day &gt;&gt;&gt;</button>
    </a>
    <button class="form-simple-submit push_menu_to_cart">Process</button>
  </div>
</main>
<script>
jQuery(function($){
  var add_saturday = 0;
  $('.push_menu_to_cart').click(function(){
    <?php
    $saturday_price = get_post_meta($product_menu_id, '_saturday_price', true);
    if(isset($_SESSION['add_saturday']) && $_SESSION['add_saturday'] == "1"): ?>
      $(document).trigger("push_menu_to_cart_confirmed");
    <?php elseif(empty($saturday_price)): ?>
      $(document).trigger("push_menu_to_cart_confirmed");
    <?php else: ?>
    if(confirm("Get your meals delivered on Saturday from only <?=$saturday_price?> euro!")){
      window.location.href="<?=site_url('/menu-for-a-week?day=monday&week=1&add_saturday=1')?>"
    }else{
      add_saturday = 0;
      $(document).trigger("push_menu_to_cart_confirmed");
    }
    <?php endif; ?>
    return false;
  });
  $(document).on("push_menu_to_cart_confirmed", function(){
    $.ajax({
      url: '<?=admin_url("admin-ajax.php")?>',
      type: 'POST',
      data: "action=push_menu_to_cart",
      success: function(data){
        window.location.href="<?=site_url("/shopping-cart")?>";
      }
    });
    return false;
  });
});
</script>
<?php get_footer() ?>
