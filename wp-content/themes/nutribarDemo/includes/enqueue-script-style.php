<?php

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Enqueue scripts and styles.
 */
function nutribar_demo_scripts() {
  wp_enqueue_style( 'default', get_stylesheet_uri() );
  wp_enqueue_style( 'nutribar-demo-style', get_template_directory_uri() . '/assets/styles/main.min.css' );
  
  wp_enqueue_script( 'nutribar-demo-main', get_template_directory_uri() . '/assets/js/main.min.js', array(), '20151215', true );
  
  wp_enqueue_script( 'nutribar-demo-vendor', get_template_directory_uri() . '/assets/js/vendor.min.js', array(), '20151215', true );
  

}
add_action( 'wp_enqueue_scripts', 'nutribar_demo_scripts' );