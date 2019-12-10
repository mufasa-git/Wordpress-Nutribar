<?php

if (is_user_logged_in()) {
  wp_redirect(esc_url(site_url('/')));
  exit;
}

?>

<?php get_header('pages'); ?>

<main class="main-content">
  <div class="main-content__top-line">
    <div class="entry-bar">
      <a class="entry-bar__link lato-n-14-20-up" href="<?= site_url('/login') ?>">Login</a>
      <a class="entry-bar__link lato-n-14-20-up" href="<?php echo site_url('/registration') ?>">Registry</a>
    </div>
  </div>
  <div class="main-content__content">
    <div class="login-form-block">
      <div class="login-form-block__intro">
        <h1 class="intro-title">NUTRIBAR<sup class="intro-subtitle-sup">tm</sup></h1>
        <p class="intro-subtitle"> healty living</p>
      </div>
      <div class="login-form-block__form">

        <form name="loginform" id="loginform" method="post" action="<?=site_url('wp-login.php?action=resetpass')?>">
          <input type="hidden" id="user_login" name="rp_login" value="<?=esc_attr($_GET['login'])?>" autocomplete="off" />
          <input type="hidden" name="rp_key" value="<?=esc_attr($_GET['key'])?>" />
          <?php if(isset( $_REQUEST['errors'])): $error_codes = explode(',', $_REQUEST['errors']);?>
            <?php foreach($error_codes as $error_code):?>
              <div class="login-form-block__item">
                <p class="login-error"><?=get_error_message($error_code)?></p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
          <div class="login-form-block__item">
            <div class="form-simple-input">
              <input class="form-simple-input__input" type="password" name="pass1" placeholder="New password" id="pass1" autocomplete="off" value="" size="20" required>
              <label class="form-simple-input__label" for="undefined">New password</label>
            </div>
          </div>
          <div class="login-form-block__item">
            <div class="form-simple-input">
              <input class="form-simple-input__input" type="password" name="pass2" placeholder="Repeat new password" id="pass2" autocomplete="off" value="" size="20" required>
              <label class="form-simple-input__label" for="undefined">Repeat new password</label>
            </div>
          </div>
          <div class="login-form-block__item">
            <p><?=wp_get_password_hint()?></p>
          </div>
          <div class="login-form-block__item-submit">
            <input class="form-simple-submit" type="submit" name="wp-submit" value="Reset Password">
          </div>
        </form>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
