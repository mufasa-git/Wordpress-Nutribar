<?php
if (!is_user_logged_in()) {
  wp_redirect(esc_url(site_url('/login')));
  exit;
}

if(isset($_POST['fitness_program'])){
  update_user_meta(get_current_user_id(), 'fitness_program', $_POST['fitness_program']);
}elseif(isset($_POST['age'])){
  update_user_meta(get_current_user_id(), 'age', $_POST['age']);
}elseif(isset($_POST['height'])){
  update_user_meta(get_current_user_id(), 'height', $_POST['height']);
}elseif(isset($_POST['weight'])){
  update_user_meta(get_current_user_id(), 'weight', $_POST['weight']);
}elseif(isset($_POST['physical_activity'])){
  update_user_meta(get_current_user_id(), 'physical_activity', $_POST['physical_activity']);
}elseif(isset($_POST['daily_calories'])){
  update_user_meta(get_current_user_id(), 'daily_calories', $_POST['daily_calories']);
}

$user_meta = array_map(function($a){return $a[0];}, get_user_meta(get_current_user_id()));
if(!empty($user_meta['fitness_program']) && !empty($user_meta['age']) && !empty($user_meta['height']) && !empty($user_meta['weight']) && !empty($user_meta['physical_activity']) && !empty($user_meta['daily_calories'])){
  wp_redirect(esc_url(site_url('/')));
  exit;
}
?>

<?php get_header('pages'); ?>

<main class="main-content main-choose-program">
  <?php if(empty(get_user_meta(get_current_user_id(),'fitness_program',true))): ?>
  <form action="" method="post">
    <div class="fitness_program_item" style="background-image: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.8) 100%), url('<?=get_theme_file_uri('/assets/img/fitness_2.jpg')?>');">
      <div class="fitness_program_item_title">LOSE WEIGHT</div>
      <div class="fitness_program_item_description">Learn how to eat properly</div>
      <button type="submit" class="fitness_program_item_btn" value="lose weight" name="fitness_program">Choose</button>
    </div>
    <div class="fitness_program_item" style="background-image: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.8) 100%), url('<?=get_theme_file_uri('/assets/img/fitness_3.jpg')?>');">
      <div class="fitness_program_item_title">FITNESS</div>
      <div class="fitness_program_item_description">Gain muscle mass</div>
      <button type="submit" class="fitness_program_item_btn" value="fitness" name="fitness_program">Choose</button>
    </div>
    <div class="fitness_program_item" style="background-image: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.8) 100%), url('<?=get_theme_file_uri('/assets/img/fitness_1.jpg')?>');">
      <div class="fitness_program_item_title">HEALTHY EATING</div>
      <div class="fitness_program_item_description">Healthy and Convenient</div>
      <button type="submit" class="fitness_program_item_btn" value="healthy eating" name="fitness_program">Choose</button>
    </div>
  </form>
  <?php elseif(empty(get_user_meta(get_current_user_id(),'age',true))): ?>
  <div class="main-choose-program_bg" style="background-image:url('<?=get_theme_file_uri('/assets/img/calc_cal_age.jpg')?>')"></div>
  <form action="" method="post" class="calculate_calories">
    <?php set_title('calculate calories');?>
    <?php show_calculate_calories_steps(1); ?>
    <div class="calculate_calories_question">
      <div class="calculate_calories_question_title">How old are you?</div>
      <input class="calculate_calories_question_value" name="age" placeholder="age" type="number" min="5" max="150" step="1">
    </div>
    <div class="calculate_calories_btn">
      <button type="submit" class="form-simple-submit form-simple-submit--dark">Next &gt;&gt;&gt;</button>
    </div>
  </form>
  <?php elseif(empty(get_user_meta(get_current_user_id(),'height',true))): ?>
  <div class="main-choose-program_bg" style="background-image:url('<?=get_theme_file_uri('/assets/img/calc_cal_height.jpg')?>')"></div>
  <form action="" method="post" class="calculate_calories">
    <?php set_title('calculate calories');?>
    <?php show_calculate_calories_steps(2); ?>
    <div class="calculate_calories_question">
      <div class="calculate_calories_question_title">What is your height?</div>
      <input class="calculate_calories_question_value" name="height" placeholder="height, cm" type="number" min="0" max="300" step="1">
    </div>
    <div class="calculate_calories_btn">
      <button type="submit" class="form-simple-submit form-simple-submit--dark">Next &gt;&gt;&gt;</button>
    </div>
  </form>
  <?php elseif(empty(get_user_meta(get_current_user_id(),'weight',true))): ?>
  <div class="main-choose-program_bg" style="background-image:url('<?=get_theme_file_uri('/assets/img/fitness_2.jpg')?>')"></div>
  <form action="" method="post" class="calculate_calories">
    <?php set_title('calculate calories');?>
    <?php show_calculate_calories_steps(3); ?>
    <div class="calculate_calories_question">
      <div class="calculate_calories_question_title">What is your weight?</div>
      <input class="calculate_calories_question_value" name="weight" placeholder="weight, kg" type="number" min="0" max="500" step="1">
    </div>
    <div class="calculate_calories_btn">
      <button type="submit" class="form-simple-submit form-simple-submit--dark">Next &gt;&gt;&gt;</button>
    </div>
  </form>
<?php elseif(empty(get_user_meta(get_current_user_id(),'physical_activity',true))): ?>
  <form action="" method="post">
    <?php set_title('physical activity');?>
    <div class="physical_activity_item_question_title">What is your daily physical activity?</div>
    <div class="physical_activity_item green">
      <div class="physical_activity_item_title">High</div>
      <img class="physical_activity_item_logo logo_1" src="<?=get_theme_file_uri('/assets/img/physical_activity_1.png')?>">
      <img class="physical_activity_item_logo logo_2" src="<?=get_theme_file_uri('/assets/img/physical_activity_2.png')?>">
      <img class="physical_activity_item_logo logo_3" src="<?=get_theme_file_uri('/assets/img/physical_activity_3.png')?>">
      <button type="submit" class="fitness_program_item_btn" value="high" name="physical_activity">Choose</button>
    </div>
    <div class="physical_activity_item yellow">
      <div class="physical_activity_item_title">Average</div>
      <img class="physical_activity_item_logo logo_1" src="<?=get_theme_file_uri('/assets/img/physical_activity_4.png')?>">
      <img class="physical_activity_item_logo logo_2" src="<?=get_theme_file_uri('/assets/img/physical_activity_5.png')?>">
      <img class="physical_activity_item_logo logo_3" src="<?=get_theme_file_uri('/assets/img/physical_activity_6.png')?>">
      <button type="submit" class="fitness_program_item_btn" value="average" name="physical_activity">Choose</button>
    </div>
    <div class="physical_activity_item red">
      <div class="physical_activity_item_title">Low</div>
      <img class="physical_activity_item_logo logo_1" src="<?=get_theme_file_uri('/assets/img/physical_activity_7.png')?>">
      <img class="physical_activity_item_logo logo_2" src="<?=get_theme_file_uri('/assets/img/physical_activity_8.png')?>">
      <img class="physical_activity_item_logo logo_3" src="<?=get_theme_file_uri('/assets/img/physical_activity_9.png')?>">
      <button type="submit" class="fitness_program_item_btn" value="low" name="physical_activity">Choose</button>
    </div>
  </form>
<?php elseif(empty(get_user_meta(get_current_user_id(),'daily_calories',true))): ?>
  <div class="main-choose-program_bg" style="background-image:url('<?=get_theme_file_uri('/assets/img/calc_cal_res.jpg')?>')"></div>
  <form action="" method="post" class="calculate_calories">
    <?php set_title('calculate calories');?>
    <?php show_calculate_calories_steps(); ?>
    <div class="calculate_calories_question">
      <?php
      switch(get_user_meta(get_current_user_id(),'fitness_program',true)){
        case 'lose weight':
          $res_phrase = "You need <strong>".get_calories_calculation()."</strong> in order to lose 1 kg per week";
          break;
        case 'fitness':
          $res_phrase = "You need <strong>".get_calories_calculation()."</strong> in order to gain 0.5 kg muscle mass per week";
          break;
        case 'healthy eating':
          $res_phrase = "You need <strong>".get_calories_calculation()."</strong> to stay stable at your weight and eat healthy and balanced";
          break;
      }
      ?>
      <div class="calculate_calories_question_title"><?=$res_phrase?></div>
      <input class="calculate_calories_question_value" value="<?=get_calories_calculation()?>" name="daily_calories" type="hidden">
    </div>
    <div class="calculate_calories_btn">
      <button type="submit" class="form-simple-submit form-simple-submit--dark">done</button>
    </div>
  </form>
  <?php endif; ?>
</main>

<?php
  function show_calculate_calories_steps($active=null){
    ?>
    <div class="calculate_calories_steps">
      <div class="calculate_calories_steps_item">
        <div class="calculate_calories_steps_item_title">Age</div>
        <div class="calculate_calories_steps_item_sign <?=(!empty(get_user_meta(get_current_user_id(),'age',true)) ? 'done' : '')?> <?=($active == 1 ? 'active' : '')?>"></div>
      </div>
      <div class="calculate_calories_steps_item">
        <div class="calculate_calories_steps_item_title">Height</div>
        <div class="calculate_calories_steps_item_sign <?=(!empty(get_user_meta(get_current_user_id(),'height',true)) ? 'done' : '')?> <?=($active == 2 ? 'active' : '')?>"></div>
      </div>
      <div class="calculate_calories_steps_item">
        <div class="calculate_calories_steps_item_title">Weight</div>
        <div class="calculate_calories_steps_item_sign <?=(!empty(get_user_meta(get_current_user_id(),'weight',true)) ? 'done' : '')?> <?=($active == 3 ? 'active' : '')?>"></div>
      </div>
    </div>
    <?php
  }
  function set_title($title){
    echo "<script>jQuery('.header-control__title').html('".$title."');</script>";
  }

?>
<?php get_footer(); ?>
