<div id='menu_product_options' class='panel woocommerce_options_panel'>
  <div class='options_group'>
    <?php
    global $week_days, $courses;
    foreach($week_days as $week_day):?>
    <div class='options_group'>
      <h3><?=ucfirst($week_day)?></h3>
      <?php foreach($courses as $course):
        woocommerce_wp_select_multiple(
          array(
            'id' => 'menu_'.$week_day.'_'.$course->slug.'_variation',
            'label' => __($course->title.' variation', $plugin_slug),
            'description' => __('Enter Menu products for '.$course->title.'.', $plugin_slug),
            'options' => get_post_meta(get_the_ID(), 'menu_'.$week_day, true)['variation'][$course->slug]
          )
        );
        woocommerce_wp_select_multiple(
          array(
            'id' => 'menu_'.$week_day.'_'.$course->slug.'_default',
            'label' => __($course->title.' default', $plugin_slug),
            'description' => __('Enter Menu default product for '.$course->title.'.', $plugin_slug),
            'options' => get_post_meta(get_the_ID(), 'menu_'.$week_day, true)['default'][$course->slug]
          )
        );
      endforeach;?>
      </div>
    <?php endforeach;
    ?>
  </div>
</div>
