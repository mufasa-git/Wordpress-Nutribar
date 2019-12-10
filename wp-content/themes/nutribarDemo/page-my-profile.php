<?php
if(isset($_POST['sumbit'])){
  wp_update_user(array(
    'ID' => get_current_user_id(),
    'user_email' => sanitize_email($_POST['email'])
  ));
  $userMetaData = array(
    'gender-select' => sanitize_text_field($_POST['genderselect']),
    'phone-number' => sanitize_text_field($_POST['phone-number']),
    'age' => sanitize_text_field($_POST['age']),
    'weight' => sanitize_text_field($_POST['weight']),
    'height' => sanitize_text_field($_POST['height'])
  );
  foreach ($userMetaData as $key => $value) {
    update_user_meta(get_current_user_id(), $key, $value);
  }
  update_user_meta(get_current_user_id(), 'daily_calories', get_calories_calculation());
}
get_header('pages');
$user_meta = array_map(function($a){return $a[0];}, get_user_meta(get_current_user_id()));
?>

<main class="main-content main-registration">
  <div class="main-content__content"><!-- FORM REGISTRATION BLOCK -->
    <div class="form-registration">

      <form id="custom_reg_form" method="post" action="/my-profile">
        <div class="form-registration__title lato-b-12-20-up">Estimated daily calories <input type="text" value="<?=$user_meta['daily_calories']?>" class="form-registration_calories" readonly></div>
       <p class="form-registration__title lato-b-12-20-up">My personal information</p>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="email" name="email" placeholder="Email address" id="email" value="<?=get_userdata(get_current_user_id())->user_email?>" required>
           <label class="form-simple-input__label" for="email">Email address</label>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-reg-select">
           <select id="gender-select" name="genderselect">
             <option value="" disabled selected id="blank-sel">Gender</option>
             <option value="male" <?=($user_meta['gender-select'] == 'male' ? 'selected': '')?>>Male</option>
             <option value="female" <?=($user_meta['gender-select'] == 'female' ? 'selected': '')?>>Female</option>
           </select>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="tel" name="phone-number" placeholder="Phone Number" id="phone-number" value="<?=$user_meta['phone-number']?>" required>
           <label class="form-simple-input__label" for="phone-number">Phone Number</label>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="text" name="age" placeholder="Age" id="age" value="<?=$user_meta['age']?>" required>
           <label class="form-simple-input__label" for="birth-date">Age</label>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="text" name="weight" placeholder="Weight (kg)" id="weight" value="<?=$user_meta['weight']?>" required>
           <label class="form-simple-input__label" for="weight">Weight (kg)</label>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="text" name="height" placeholder="Height (cm)" id="height" value="<?=$user_meta['height']?>" required>
           <label class="form-simple-input__label" for="height">Height (cm)</label>
         </div>
       </div>
       <input class="form-simple-submit" type="submit" name="sumbit" value="Save">
     </form>

    </div><!-- FORM REGISTRATION BLOCK END -->
  </div>
</main>

<?php get_footer(); ?>
