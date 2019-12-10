<?php
/*
 * Have tested additional fields for base product, remove if unnesessary
 */

add_action( 'woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields' );

add_action( 'woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save' );

function woocommerce_product_custom_fields() {
  global $woocommerce, $post;
  $_product = wc_get_product($post->ID);
  if($_product->is_type('simple')){
    echo '<div class="product_custom_field">';
    woocommerce_wp_text_input(
      array(
        'id' => '_product_param_calories',
        'placeholder' => 'Calories',
        'label' => __('Calories', 'woocommerce'),
        'desc_tip' => 'true'
      )
    );
    woocommerce_wp_text_input(
      array(
        'id' => '_product_param_carbs',
        'placeholder' => 'Carbs',
        'label' => __('Carbs', 'woocommerce'),
        'desc_tip' => 'true'
      )
    );
    woocommerce_wp_text_input(
      array(
        'id' => '_product_param_protein',
        'placeholder' => 'Protein',
        'label' => __('Protein', 'woocommerce'),
        'desc_tip' => 'true'
      )
    );
    woocommerce_wp_text_input(
      array(
        'id' => '_product_param_fat',
        'placeholder' => 'Fat	',
        'label' => __('Fat', 'woocommerce'),
        'desc_tip' => 'true'
      )
    );
    woocommerce_wp_text_input(
      array(
        'id' => '_product_param_fibre',
        'placeholder' => 'Fibre',
        'label' => __('Fibre', 'woocommerce'),
        'desc_tip' => 'true'
      )
    );
    echo '</div>';
  }
}

function woocommerce_product_custom_fields_save($post_id){
  $_product = wc_get_product($post_id);
  if($_product->is_type('simple')){
    if(!empty($_POST['_product_param_calories'])){
      update_post_meta($post_id, '_product_param_calories', esc_attr($_POST['_product_param_calories']));
    }
    if(!empty($_POST['_product_param_carbs'])){
      update_post_meta($post_id, '_product_param_carbs', esc_attr($_POST['_product_param_carbs']));
    }
    if(!empty($_POST['_product_param_protein'])){
      update_post_meta($post_id, '_product_param_protein', esc_attr($_POST['_product_param_protein']));
    }
    if(!empty($_POST['_product_param_fat'])){
      update_post_meta($post_id, '_product_param_fat', esc_attr($_POST['_product_param_fat']));
    }
    if(!empty($_POST['_product_param_fibre'])){
      update_post_meta($post_id, '_product_param_fibre', esc_attr($_POST['_product_param_fibre']));
    }
  }
}

/*
 * <?php while (have_posts()) : the_post(); ?>
<?php wc_get_template_part('content', 'single-product'); ?>
<?php
// Display the value of custom product text field
    echo get_post_meta($post->ID, '_custom_product_text_field', true);
// Display the value of custom product number field
    echo get_post_meta(get_the_ID(), '_custom_product_number_field', true);
// Display the value of custom product text area
    echo get_post_meta(get_the_ID(), '_custom_product_textarea', true);
    ?>
<?php endwhile; // end of the loop. ?>
 */
