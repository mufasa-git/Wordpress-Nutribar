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
<div class="wrapper">
  <header class="header-home">
    <div class="header-home__main-bar">
      <h1 class="header-home__title"><?php is_front_page() ? bloginfo('name') : wp_title('') ?></h1>
      <div class="move_right">
        <a href="<?php echo is_user_logged_in() ? site_url('/shop') : site_url('login') ?>" class="header-home__order-btn">Order</a>
        <!-- <span class="nutripoints_balance"><?=mycred_get_users_fcred(get_current_user_id())?></span> -->
      </div>
    </div>
  </header>
