<?php

if (is_user_logged_in()) {
  wp_redirect(esc_url(site_url('/')));
  exit;
}

?>

<?php get_header('pages'); ?>

<main class="main-content">
  <div class="main-content__top-line">
    <div class="entry-bar"><a class="entry-bar__link entry-bar__link--active lato-n-14-20-up" href="<?= site_url('/login') ?>">Login</a><a class="entry-bar__link lato-n-14-20-up" href="<?php echo site_url('/registration') ?>">Registry</a></div>
  </div>
  <div class="main-content__content">
    <div class="login-form-block">
      <div class="login-form-block__intro">
        <h1 class="intro-title">NUTRIBAR<sup class="intro-subtitle-sup">tm</sup></h1>
        <p class="intro-subtitle"> healty living</p>
      </div>
      <div class="login-form-block__form">

        <form name="loginform" id="loginform" method="post" action="<?php echo site_url() . '/wp-login.php' ?>">
          <?php if(isset( $_REQUEST['checkemail'] ) && $_REQUEST['checkemail'] == 'confirm'): ?>
          <div class="login-form-block__item">
            <p class="login-info">Check your email for a link to reset your password.</p>
          </div>
          <?php endif; ?>
          <?php if(isset( $_REQUEST['password'] ) && $_REQUEST['password'] == 'changed'): ?>
          <div class="login-form-block__item">
            <p class="login-info">Your password has been changed. You can sign in now.</p>
          </div>
          <?php endif; ?>
          <div class="login-form-block__item">
            <div class="form-simple-input">
              <input class="form-simple-input__input" type="email" name="log" placeholder="Email" id="user_login" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( $_POST['email'] ) : ''; ?>" size="20" required>
              <label class="form-simple-input__label" for="undefined">Email</label>
            </div>
          </div>
          <div class="login-form-block__item">
            <div class="form_password form_password--pwd-restore">
              <input class="form-simple-input__input form_password__pwd" type="password" name="pwd" placeholder="Password" id="user_pass" size="20">
              <label class="form-simple-input__label" for="undefined">Password</label>
              <a class="form_password__pwd-restore" href="<?=site_url('forgot-passwd')?>">Forgot Password?</a>
            </div>
          </div>
          <div class="login-form-block__item-submit">
            <input class="form-simple-submit" type="submit" name="wp-submit" value="Login">
          </div>
        </form>
        <?=do_shortcode('[miniorange_social_login]')?>
        <p class="facebook_description">*you need to be logged in on facebook for login with facebook</p>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
