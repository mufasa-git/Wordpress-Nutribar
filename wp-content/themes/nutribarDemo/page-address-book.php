<?php
if (isset($_POST['delivery_time'])) {
  update_user_meta(get_current_user_id(), "delivery_time", esc_html($_POST['delivery_time']) );
  wp_redirect(esc_url(site_url('/shop')));
}
$d_time = get_user_meta(get_current_user_id(),'delivery_time',true);

$user_meta = array_map(function($a){return $a[0];}, get_user_meta(get_current_user_id()));

$address = $user_meta['billing_city'] .
            ($user_meta['billing_postcode'] ? (', ' . $user_meta['billing_postcode']) : '') .
            ($user_meta['address'] ? (', ' . $user_meta['address']) : '') .
            ($user_meta['building_house_number'] ? (', ' . $user_meta['building_house_number']) : '') .
            ($user_meta['flat_number'] ? (', ' . $user_meta['flat_number']) : '') .
            ($user_meta['floor_or_level'] ? (', ' . $user_meta['floor_or_level']) : '') .
            ($user_meta['area'] ? (', ' . $user_meta['area']) : '');
?>

<?php get_header('pages'); ?>

  <main class="main-content main-addr-book">
    <div class="main-addr-book__top-block">
      <div class="main-addr-book__hint">To proceed, please confirm your delivery details.</div>
      <div class="main-addr-book__item">Delivery address</div>
      <p class="main-addr-book__address"><?php echo $address ?></p>
      <div class="main-addr-book__del-addr">
        <div class="main-addr-book__item">Delivery time</div>
      </div>
    </div>
    <form class="main-addr-book__time-chooser" action="" method="post">
      <div class="time-chooser">
        <div class="time-chooser__info-block">
          <div class="time-chooser__info-block-day">Day time
            <div class="time-chooser__info-btn" id="ib-day"><span>i</span>
              <svg class="svg-sprite-icon icon-delete">
                <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg') ?>#delete"></use>
              </svg>
            </div>
          </div>
          <div class="time-chooser__info-block-evening">Evening time
            <div class="time-chooser__info-btn" id="ib-evening"><span>i</span>
              <svg class="svg-sprite-icon icon-delete">
                <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg') ?>#delete"></use>
              </svg>
            </div>
          </div>
        </div>
        <div class="time-chooser__switcher">
          <div class="time-chooser__glider <?php if ($d_time == '19:00 - 22:00') echo 'time-chooser__glider--rpos'?>"></div>
          <div class="time-chooser__periods">
            <div class="time-chooser__period-day <?= $d_time == '11:00 - 14:00' ? 'time-chooser__period-day--active' : ''?>">11:00 - 14:00</div>
            <div class="time-chooser__period-evening <?= $d_time == '19:00 - 22:00' ? 'time-chooser__period-evening--active' : ''?>">19:00 - 22:00</div>
          </div>
        </div>
      </div>
    </form>
    <div class="main-addr-book__bottom-block">
      <div class="main-addr-book__text-btn"><a class="underlined-btn" href="<?= site_url('/delivery-address') ?>">Change OR EDIt your address</a>
      </div>
      <div class="main-addr-book__proceed-btn">
        <form action="" method="post">
          <input id="delivery_time" name="delivery_time" type="hidden" value="<?= $d_time ? $d_time : '11:00 - 14:00' ?>">
          <input class="form-simple-submit" type="submit" name="sumbit" value="Proceed to order">
        </form>
      </div>
    </div>
  </main>

<?php get_footer();
