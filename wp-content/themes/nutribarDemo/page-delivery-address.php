<?php
if (!is_user_logged_in()) {
  wp_redirect(esc_url(site_url('/login')));
  exit;
}

if (isset($_POST['city-select'])) {
  $adressData = array(
    'billing_city' => esc_html($_POST['city-select']),
    'billing_postcode' => esc_html($_POST['postcode']),
    'address' => esc_html($_POST['address']),
    'building_house_number' => esc_html($_POST['building-house-number']),
    'flat_number' => esc_html($_POST['flat-number']),
    'floor_or_level' => esc_html($_POST['floor-or-level']),
    'area' => esc_html($_POST['Area']),
    'special_request' => esc_html($_POST['special-request'])
  );

  save_delivery_address($adressData);

  wp_redirect('/address-book');
} else {
  $adressData = '';
}

$user = get_current_user_id();
?>

<?php get_header('pages'); ?>

<main class="main-content main-delivery-address">
  <div class="main-content__content"><!-- DELIVERY ADDRESS FORM -->
    <div class="form-delivery-addr">
      <form id="delivery_address_form" action="" method="post">
        <p class="form-delivery-addr__hint"><sup>* </sup><span>Required fields</span></p>
        <div class="form-delivery-addr__form-fields">
          <div class="form-delivery-addr__form-field">
            <label class="form-delivery-addr__label" for="city-select">City/District:<sup>*</sup></label>
            <div class="form-delivery-addr__select-wrapper">
              <select class="form-delivery-addr__select" id="city-select" name="city-select" required>
                <option value="Limassol">Limassol</option>
                <option value="kikjerbo">kikjerbo</option>
                <option value="Fumilon">Fumilon</option>
              </select>
            </div>
          </div>
          <div class="form-delivery-addr__form-field">
            <label class="form-delivery-addr__label" for="postcode">Postcode:<sup>*</sup></label>
            <input class="form-delivery-addr__input-text" name="postcode" id="postcode" value="<?= get_user_meta($user, 'billing_postcode', true) ?>" required>
          </div>
          <div class="form-delivery-addr__form-field">
            <label class="form-delivery-addr__label" for="address">Address:<sup>*</sup></label>
            <input class="form-delivery-addr__input-text" name="address" id="address" value="<?= get_user_meta($user, 'address', true) ?>" required>
          </div>
          <div class="form-delivery-addr__form-field">
            <label class="form-delivery-addr__label" for="building-house-number">Buildung/House Number:<sup>*</sup></label>
            <input class="form-delivery-addr__input-text" name="building-house-number" id="building-house-number" value="<?= get_user_meta($user, 'building_house_number', true) ?>" required>
          </div>
          <div class="form-delivery-addr__form-field">
            <label class="form-delivery-addr__label" for="flat-number">Flat Number:</label>
            <input class="form-delivery-addr__input-text" name="flat-number" id="flat-number" value="<?= get_user_meta($user, 'flat_number', true) ?>">
          </div>
          <div class="form-delivery-addr__form-field">
            <label class="form-delivery-addr__label" for="floor-or-level">Floor/Level:</label>
            <input class="form-delivery-addr__input-text" name="floor-or-level" id="floor-or-level" value="<?= get_user_meta($user, 'floor_or_level', true) ?>">
          </div>
          <div class="form-delivery-addr__form-field">
            <label class="form-delivery-addr__label" for="Area">Area:</label>
            <input class="form-delivery-addr__input-text" name="Area" id="Area" value="<?= get_user_meta($user, 'area', true) ?>">
          </div>
          <div class="form-delivery-addr__form-field">
            <label class="form-delivery-addr__label" for="special-request">Special reqest for delivery:</label>
            <input class="form-delivery-addr__input-text" name="special-request" id="special-request" value="<?= get_user_meta($user, 'special_request', true) ?>">
          </div>
          <div class="form-delivery-addr__form-field-submit">
            <input class="form-delivery-addr__form-submit form-simple-submit" type="submit" name="delivery_address" value="Save">
          </div>
        </div>
      </form>
    </div>
  </div>
</main>
<!-- Forgot to add this in markup -->
<script>
  document.querySelector('.header-control__btn').addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelector('#delivery_address_form').submit();

  })
</script>

<?php get_footer(); ?>
