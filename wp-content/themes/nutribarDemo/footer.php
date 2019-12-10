<footer class="main-footer">
  <div class="main-footer__item"><a class="main-footer__link" href="<?= site_url('/?iframe=1') ?>"><!-- active class: footer-item--active -->
      <div class="footer-item <?php if (is_home()) echo 'footer-item--active' ?>">
        <div class="footer-item__icon">
          <svg class="svg-sprite-icon icon-house">
            <use xlink:href="<?php echo get_theme_file_uri('/assets/img/sprites/symbol/sprite.svg') ?>#house"></use>
          </svg>
        </div>
        <span class="lato-n-12-14 footer-item__title">Home</span>
      </div>
    </a></div>
  <div class="main-footer__item"><a class="main-footer__link" href="<?= site_url('/shop') ?>"><!-- active class: footer-item--active -->
      <div class="footer-item <?php if (is_shop()) echo 'footer-item--active' ?>">
        <div class="footer-item__icon">
          <svg class="svg-sprite-icon icon-booklet">
            <use xlink:href="<?php echo get_theme_file_uri('/assets/img/sprites/symbol/sprite.svg') ?>#booklet"></use>
          </svg>
        </div>
        <span class="lato-n-12-14 footer-item__title">Menu</span>
      </div>
    </a></div>
  <div class="main-footer__item"><a class="main-footer__link" href="<?= site_url('/menu') ?>"><!-- active class: footer-item--active -->
      <div class="footer-item <?php if (is_page('menu')) echo 'footer-item--active' ?>">
        <div class="footer-item__icon">
          <svg class="svg-sprite-icon icon-burger">
            <use xlink:href="<?php echo get_theme_file_uri('/assets/img/sprites/symbol/sprite.svg') ?>#burger"></use>
          </svg>
        </div>
        <span class="lato-n-12-14 footer-item__title">More</span>
      </div>
    </a></div>
</footer><!-- FOOTER END -->
</div>
<?php wp_footer(); ?>
</body>
</html>
