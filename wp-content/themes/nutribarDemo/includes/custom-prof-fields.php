<?php
/*
 * Code for additional fields in Wordpress user menu
 */

add_action('show_user_profile', 'my_profile_new_fields_add');
add_action('edit_user_profile', 'my_profile_new_fields_add');

function my_profile_new_fields_add($user){
  $accaunt = get_user_meta($user->ID);

//  echo var_dump($accaunt);

  ?>
  <h3>Additional data</h3>
  <table class="form-table">
    <tr>
      <th><label for="user_fb_txt">Gender</label></th>
      <td>
        <input type="text" name="gender-select" value="<?php echo $accaunt['gender-select'][0] ?>"><br>
      </td>
    </tr>
    <tr>
      <th><label for="user_fb_txt">Phone Number</label></th>
      <td>
        <input type="text" name="phone-number" value="<?php echo $accaunt['phone-number'][0] ?>"><br>
      </td>
    </tr>
    <tr>
      <th><label for="user_fb_txt">Fitness program</label></th>
      <td>
        <input type="text" name="fitness_program" value="<?php echo $accaunt['fitness_program'][0] ?>"><br>
      </td>
    </tr>
    <tr>
      <th><label for="user_fb_txt">Age</label></th>
      <td>
        <input type="text" name="age" value="<?php echo $accaunt['age'][0] ?>"><br>
      </td>
    </tr>
    <tr>
      <th><label for="user_fb_txt">Weight (kg)</label></th>
      <td>
        <input type="text" name="weight" value="<?php echo $accaunt['weight'][0] ?>"><br>
      </td>
    </tr>
    <tr>
      <th><label for="user_fb_txt">Height (cm)</label></th>
      <td>
        <input type="text" name="height" value="<?php echo $accaunt['height'][0] ?>"><br>
      </td>
    </tr>
    <tr>
      <th><label for="user_fb_txt">Physical activity</label></th>
      <td>
        <input type="text" name="physical_activity" value="<?php echo $accaunt['physical_activity'][0] ?>"><br>
      </td>
    </tr>
    <tr>
      <th><label for="user_fb_txt">Daily calories</label></th>
      <td>
        <input type="text" name="daily_calories" value="<?php echo $accaunt['daily_calories'][0] ?>"><br>
      </td>
    </tr>
  </table>

  <h3>Delivery Address</h3>
  <table class="form-table">
    <tr>
      <th><label for="user_fb_txt">Billing Sity</label></th>
      <td>
        <input type="text" name="billing_city" value="<?php echo $accaunt['billing_city'][0] ?>"><br>
      </td>
    </tr>

    <tr>
      <th><label for="user_fb_txt">Building House Number</label></th>
      <td>
        <input type="text" name="building_house_number" value="<?php echo $accaunt['building_house_number'][0] ?>"><br>
      </td>
    </tr>

    <tr>
      <th><label for="user_fb_txt">Special Request</label></th>
      <td>
        <input type="text" name="special_request" value="<?php echo $accaunt['special_request'][0] ?>"><br>
      </td>
    </tr>
  </table>
  <?php
}
add_action( 'personal_options_update', 'save_my_profile_new_fields_add' );
add_action( 'edit_user_profile_update', 'save_my_profile_new_fields_add' );

function save_my_profile_new_fields_add($user_id){
    if (!current_user_can( 'edit_user', $user_id)){
        return false;
    }
    update_user_meta($user_id, 'gender-select', $_POST['gender-select']);
    update_user_meta($user_id, 'phone-number', $_POST['phone-number']);
    update_user_meta($user_id, 'age', $_POST['age']);
    update_user_meta($user_id, 'fitness_program', $_POST['fitness_program']);
    update_user_meta($user_id, 'physical_activity', $_POST['physical_activity']);
    update_user_meta($user_id, 'daily_calories', $_POST['daily_calories']);
    update_user_meta($user_id, 'weight', $_POST['weight']);
    update_user_meta($user_id, 'height', $_POST['height']);
    update_user_meta($user_id, 'billing_city', $_POST['billing_city']);
    update_user_meta($user_id, 'building_house_number', $_POST['building_house_number']);
    update_user_meta($user_id, 'special_request', $_POST['special_request']);
}
