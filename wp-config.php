<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'db_nutribar' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ']-)zp~=duHVVYheF21&=,g#rKHCq6yO+`Bc7*:#6tH9K1M*[|ZxLXWeq>nU?2zP;' );
define( 'SECURE_AUTH_KEY',  '-OZ&H&4w?t@1FyUStwH^^ivd}]NYc%R09%8)Zg(/VfN{tD@/[!~yz@f)5nK?8Xof' );
define( 'LOGGED_IN_KEY',    'AnQy0A0h)lb}x_$A`Q/g-aEy73O=o|DN$G.ThXw2+udO]6;OA<(dz3U3}x4[rtaC' );
define( 'NONCE_KEY',        '5$8(8Lkf1tj[xfWu)#K1>(D`<|ppTa[iE$iJvZ+`Si~M:9@L^9Yg6D4}Wi0WSBYo' );
define( 'AUTH_SALT',        'wEo5W>|tvk3j?HWT1=UORA Zg;PpImRok&!Wx&aq<F S]P{onFm^g,(Vx6Ge5#>6' );
define( 'SECURE_AUTH_SALT', 'c=@3q/Og) tQl7<gqJd#)hS0j#(,]nPpLY_JhBST~C+0TrE}GzNoK:BQ>>Vj.m4R' );
define( 'LOGGED_IN_SALT',   '%+^0e,hp ~lwCwx3bSL?z_ctu!UIxv@WSKa!l[Mz y?@mQ9ln9_-g*NP12T`~]p_' );
define( 'NONCE_SALT',       '&_m_{9`D GyKF)t:6ml9S8wqM7!M(}{OFoMy$^&2A<?M~*Mft7@2$;Zj`uFej+nE' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );

@ini_set( 'upload_max_filesize' , '128M' );
@ini_set( 'post_max_size', '128M');
@ini_set( 'memory_limit', '256M' );
@ini_set( 'max_execution_time', '300' );
@ini_set( 'max_input_time', '300' );