<?php if(!defined('ABSPATH')) exit; ?>

<?php
$d_time = get_user_meta(get_current_user_id(),'delivery_time',true);
$w_days_text = !isset($_SESSION['add_saturday']) || $_SESSION['add_saturday'] == "0" ? 'Mon - Fri' : 'Mon - Sat';
?>
<main class="main-content">
  <div class="acknowledgment">
		<div class="acknowledgment__main-image">
      <img src="<?=get_theme_file_uri('assets/img/thank_you.jpg')?>">
    </div>
    <div class="acknowledgment__main-text">
      <div class="acknowledgment__main-text-top">Thank you</div>
      <div class="acknowledgment__main-text-bottom">For you order</div>
    </div>
    <div class="acknowledgment__time-info">
      <div class="acknowledgment__time-info-top">Est. delivery date and time</div>
      <div class="acknowledgment__time-info-bottom"><?=$w_days_text?> <?=$d_time?></div>
    </div>
  </div>
</main>
