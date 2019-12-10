<?php

if (!is_user_logged_in()) {
  wp_redirect(esc_url(site_url('/login')));
  exit;
}
?>

<?php get_header('pages'); ?>

  <main class="main-content main-menu">
    <nav class="main-menu__nav">
      <ul class="main-menu__list">
        <li class="main-menu__item">
          <div class="menu-item">
            <svg class="svg-sprite-icon icon-small-clock">
              <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg');?>#small-clock"></use>
            </svg><a class="menu-item__link" href="<?=site_url('/order-history')?>">Orders History</a>
          </div>
        </li>
        <li class="main-menu__item">
          <div class="menu-item">
            <svg class="svg-sprite-icon icon-person">
              <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg');?>#person"></use>
            </svg><a class="menu-item__link" href="<?=site_url('/my-profile')?>">My profile</a>
          </div>
        </li>
        <li class="main-menu__item">
          <div class="menu-item">
            <svg class="svg-sprite-icon icon-addr-mark">
              <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg');?>#addr-mark"></use>
            </svg><a class="menu-item__link" href="<?=site_url('/address-book')?>">Address</a>
          </div>
        </li>
        <li class="main-menu__item">
          <div class="menu-item">
            <svg class="svg-sprite-icon icon-scoop">
              <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg');?>#scoop"></use>
            </svg><a class="menu-item__link" href="#">About</a>
          </div>
        </li>
        <li class="main-menu__item">
          <div class="menu-item">
            <svg class="svg-sprite-icon icon-cart">
              <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg');?>#cart"></use>
            </svg><a class="menu-item__link" href="<?=site_url('/shop')?>">Shop</a>
          </div>
        </li>
        <li class="main-menu__item">
          <div class="menu-item">
            <svg class="svg-sprite-icon icon-ask">
              <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg');?>#ask"></use>
            </svg><a class="menu-item__link" href="#">FAQ</a>
          </div>
        </li>
        <li class="main-menu__item">
          <div class="menu-item">
            <svg class="svg-sprite-icon icon-logout">
              <use xlink:href="<?= get_theme_file_uri('assets/img/sprites/symbol/sprite.svg');?>#logout"></use>
            </svg><a class="menu-item__link" href="<?php echo wp_logout_url(site_url('/')); ?>">Logout</a>
          </div>
        </li>
      </ul>
    </nav>
  </main>

<?php get_footer(); ?>
