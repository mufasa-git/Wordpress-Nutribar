<?php

/*
  Plugin Name: Custom Registration
*/

function registration_form()   {

echo '
 <form id = "custom_reg_form" method = "post" action = "' . site_url('/registration') . '">
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
  <div class="form-registration__item">
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
  </div>
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
</form>';
}

function complete_registration($username, $password, $email, $gender_select, $phone_number, $birth_date, $weight, $height)
{

  $userdata = array(
    'user_login' => $username,
    'user_email' => $email,
    'user_pass' => $password
  );

  $userMetaData = array(
    'gender-select' => $gender_select,
    'phone-number' => $phone_number,
    'birth-date' => $birth_date,
    'weight' => $weight,
    'height' => $height
  );

  $user = wp_insert_user($userdata);

  foreach ($userMetaData as $key => $value) {
    update_user_meta($user, $key, $value);
  }

  // wp_redirect(site_url('/login'));
  $user = wp_authenticate($email, $password);
  if(is_wp_error($user)){
    echo $user;
  }
  wp_set_auth_cookie($user->ID);
  do_action('wp_login', $email, $user);
  wp_redirect(home_url());

  exit;
}

function custom_registration_function()
{

  if (isset($_POST['sumbit'])) {
    // sanitize user form input

    $username = sanitize_user($_POST['username']);
    $password = esc_attr($_POST['password']);
    $email = sanitize_email($_POST['email']);
    $gender_select = sanitize_text_field($_POST['genderselect']);
    $phone_number = sanitize_text_field($_POST['phone-number']);
    $birth_date = sanitize_text_field($_POST['birth-date']);
    $weight = sanitize_text_field($_POST['weight']);
    $height = sanitize_text_field($_POST['height']);

    // call @function complete_registration to create the user
    // only when no WP_error is found
    complete_registration(
      $username,
      $password,
      $email,
      $gender_select,
      $phone_number,
      $birth_date,
      $weight,
      $height
    );
  }
}

// Register a new shortcode: [cr_custom_registration]
add_shortcode('cr_custom_registration', 'custom_registration_shortcode');

// The callback function that will replace [book]
function custom_registration_shortcode()
{
  ob_start();
  custom_registration_function();
  return ob_get_clean();
}
