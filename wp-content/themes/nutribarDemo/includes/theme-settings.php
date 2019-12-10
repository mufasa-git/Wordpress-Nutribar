<?php

if (!defined('ABSPATH')) {
  exit;
}

if ( ! function_exists( 'nutribar_demo_setup' ) ) {

  add_action( 'after_setup_theme', 'nutribar_demo_setup' );
  function nutribar_demo_setup() {
    
    add_theme_support( 'automatic-feed-links' );
    
    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support( 'title-tag' );
    
    /*
     * Enable support for Post Thumbnails on posts and pages.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support( 'post-thumbnails' );
    
    
    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support( 'html5', array(
      'search-form',
      'comment-form',
      'comment-list',
      'gallery',
      'caption',
    ) );
    
  }
}

