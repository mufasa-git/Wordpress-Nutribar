<?php

if (is_user_logged_in()) {
  wp_redirect(esc_url(site_url('/')));
  exit;
}

custom_registration_function();

?>

<?php get_header('pages'); ?>

<main class="main-content main-registration">
  <div class="main-content__top-line">
    <div class="entry-bar"><a class="entry-bar__link lato-n-14-20-up" href="<?= site_url('/login') ?>">Login</a><a class="entry-bar__link entry-bar__link--active lato-n-14-20-up" href="<?= site_url('/registration') ?>">Registry</a></div>
  </div>
  <div class="main-content__content"><!-- FORM REGISTRATION BLOCK -->
    <div class="form-registration">

      <form id="custom_reg_form" method="post" action="/registration">
       <p class="form-registration__title lato-b-12-20-up">My personal information</p>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="text" name="username" placeholder="Name" id="name" required>
           <label class="form-simple-input__label" for="name">Name</label>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="email" name="email" placeholder="Email address" id="email" required>
           <label class="form-simple-input__label" for="email">Email address</label>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-reg-select">
           <select id="gender-select" name="genderselect">
             <option value="" disabled selected id="blank-sel">Gender</option>
             <option value="male">Male</option>
             <option value="female">Female</option>
           </select>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="tel" name="phone-number" placeholder="Phone Number" id="phone-number" required>
           <label class="form-simple-input__label" for="phone-number">Phone Number</label>
         </div>
       </div>
       <!-- <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="text" name="birth-date" placeholder="Date of Birth" id="birth-date" required>
           <label class="form-simple-input__label" for="birth-date">Date of Birth</label>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="text" name="weight" placeholder="Weight (kg)" id="weight" required>
           <label class="form-simple-input__label" for="weight">Weight (kg)</label>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="text" name="height" placeholder="Height (cm)" id="height" required>
           <label class="form-simple-input__label" for="height">Height (cm)</label>
         </div>
       </div> -->
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="password" name="password" placeholder="Password" id="password" required>
           <label class="form-simple-input__label" for="password">Password</label>
         </div>
       </div>
       <div class="form-registration__item">
         <div class="form-simple-input">
           <input class="form-simple-input__input" type="password" name="re-password" placeholder="Re-enter Password" id="re-password" required>
           <label class="form-simple-input__label" for="re-password">Re-enter Password</label>
         </div>
       </div>
       <p class="form-registration__pwd-hint">Password must be at least 8 characters with 1 numeric digits.</p>
       <input class="form-simple-submit" type="submit" name="sumbit" value="Register">
     </form>

    </div><!-- FORM REGISTRATION BLOCK END -->
  </div>
</main>

<?php get_footer(); ?>
