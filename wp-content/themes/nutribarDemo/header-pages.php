<?php

$prev_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="theme-color" content="#fff">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <link rel="shortcut icon" href="<?=get_template_directory_uri()?>/assets/img/favicons/favicon.ico" type="image/x-icon">
  <link rel="icon" sizes="16x16" href="<?=get_template_directory_uri()?>/assets/img/favicons/favicon-16x16.png" type="image/png">
  <link rel="icon" sizes="32x32" href="<?=get_template_directory_uri()?>/assets/img/favicons/favicon-32x32.png" type="image/png">
  <link rel="apple-touch-icon-precomposed" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-precomposed.png">
  <link rel="apple-touch-icon" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon.png">
  <link rel="apple-touch-icon" sizes="57x57" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="167x167" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-167x167.png">
  <link rel="apple-touch-icon" sizes="180x180" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-180x180.png">
  <link rel="apple-touch-icon" sizes="1024x1024" href="<?=get_template_directory_uri()?>/assets/img/favicons/apple-touch-icon-1024x1024.png">
  <?php wp_head(); ?>
</head>
<body>
<div class="wrapper"><!-- HEADER -->
  <header class="main-header">
    <!-- <a class="header-control" href="<?= $prev_url; ?>"> -->
      <?php
      if(is_page('choose-meal') || is_page('choose-submeal')){
        $save_dish = true;
      }else{
        $back_link = true;
      }
      ?>
      <a class="header-control <?=($save_dish ? 'save_chosen_dish' : '')?>" <?=($back_link ? 'onclick="return goBack()"' : '')?> href="#">
        <div class="header-control__arrow"></div>
        <span class="lato-b-16-19-up header-control__title"><?php wp_title('') ?></span>
      </a>
      <div class="move_right">
        <?php if(is_page('address-book')): ?>
          <a class="header-control__btn" href="<?= site_url('/delivery-address') ?>">Edit</a>
        <?php elseif (is_page('delivery-address')): ?>
          <a class="header-control__btn" href="">Save</a>
        <?php elseif (is_page('shopping-cart')): ?>
          <a class="header-control__btn" href="<?=esc_url(wc_get_checkout_url())?>">check out</a>
        <?php elseif (is_shop() || is_page('menu-for-a-week') || is_page('choose-meal') || is_page('choose-submeal')): ?>
          <a class="header-control__bag" href="<?= site_url('/shopping-cart') ?>">
            <svg class="svg-sprite-icon icon-bag">
              <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg') ?>#bag"></use>
            </svg><span class="header-control__count"><?=WC()->cart->get_cart_contents_count()?></span>
          </a>
        <?php endif; ?>
        <!-- <span class="nutripoints_balance"><?=mycred_get_users_fcred(get_current_user_id())?></span> -->
      </div>
  </header><!-- HEADER END -->
