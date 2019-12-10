<?php

session_start();
if(isset($_SESSION['screen_width']) AND isset($_SESSION['screen_height'])){
    if(intval($_SESSION['screen_width']) > 1024 && empty($_REQUEST['iframe'])){
      echo '<script type="text/javascript">
      function is_iframe(){try{return window.self !== window.top;}catch(e){ return true;}} if(is_iframe()){window.location = "' . $_SERVER['PHP_SELF'] . '?iframe=1";}</script>';
      get_template_part('desktop','index');
      exit;
    }
} else if(isset($_REQUEST['width']) AND isset($_REQUEST['height'])) {
    $_SESSION['screen_width'] = $_REQUEST['width'];
    $_SESSION['screen_height'] = $_REQUEST['height'];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
} else {
    echo '<script type="text/javascript">window.location = "' . $_SERVER['PHP_SELF'] . '?'.$_SERVER['QUERY_STRING'].'&width="+screen.width+"&height="+screen.height;</script>';
    exit;
}

if(is_user_logged_in()){
  $user_meta = array_map(function($a){return $a[0];}, get_user_meta(get_current_user_id()));
  if(empty($user_meta['fitness_program']) || empty($user_meta['age']) || empty($user_meta['height']) || empty($user_meta['weight']) || empty($user_meta['physical_activity']) || empty($user_meta['daily_calories'])){
    wp_redirect(esc_url(site_url('/choose-program')));
    exit;
  }
}


get_header();
?>

  <main class="main-content"><!-- MAIN HOME -->
    <div class="main-home">
      <picture>
        <source media="(min-width: 576px)" srcset="<?php echo get_theme_file_uri('/assets/img/home-pic_x2.jpg') ?> 2x">
        <img class="main-home__big-pic" src="<?php echo get_theme_file_uri('/assets/img/home-pic.jpg') ?>" srcset="<?php echo get_theme_file_uri('/assets/img/home-pic_x2.jpg') ?> 2x">
      </picture>
      <div class="main-home__bottom-line">
        <a href="<?php echo is_user_logged_in() ? site_url('/shop') : site_url('login') ?>" class="main-home__btn main-home__btn-left">
          <svg class="svg-sprite-icon icon-calendar">
            <use xlink:href="<?php echo get_theme_file_uri('/assets/img/sprites/symbol/sprite.svg')  ?>#calendar"></use>
          </svg>
          <span class="main-home__text">Build your <br/>plan</span>
        </a>
        <a target="_blank" href="https://foody.com.cy/gr/menu/nutribar" class="main-home__btn main-home__btn-right">
          <svg class="svg-sprite-icon icon-clock">
            <use xlink:href="<?php echo get_theme_file_uri('/assets/img/sprites/symbol/sprite.svg')  ?>#clock"></use>
          </svg>
          <span class="main-home__text">Order now</span><!--<span class="main-home__subtext">Comming soon</span>-->
        </a>
      </div>
    </div><!-- MAIN HOME END -->
  </main>
<?php
get_footer();
