<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_literasi' );

/** Database username */
define( 'DB_USER', 'wp_user' );

/** Database password */
define( 'DB_PASSWORD', 'aisyatur170706' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '~0?sblzyOcqg6Ssj(VQdg.zN@ME`8#V/!Tf:%.w(9r,nT-,kcMgB%xLt}=oZ}.Ji' );
define( 'SECURE_AUTH_KEY',  ';jSlMfCO8r.]<3|A$EUr?FomL| ;kp@R-1&.*9&3%:8BKD%oL/_=Tn)|j9bQ;&+Q' );
define( 'LOGGED_IN_KEY',    '{vxIqGHs@PtN54{fG*.Dte$0>+xl<N@ cb=^+HfjT!vF@uSGB;LpKsVQM)hIAb4*' );
define( 'NONCE_KEY',        '3-2,qyvp7r,v=&UT}QmXvZ]c!{bWAIsv3SgvZk?eLanKY{w]-?ihgEn^3a*>WYGz' );
define( 'AUTH_SALT',        '88@ek{@GMHyysB5t)CuJw 8^-e@%/tQ`oG9BQug2=7_I&(fewiRzu/Eg06h@fwSM' );
define( 'SECURE_AUTH_SALT', 'fZ8<HOd$r_iV:on7(s%[kWfah(>aN!Ni7|Pn!,ay03 bY!:{8uA!hP^t=h&=yW,{' );
define( 'LOGGED_IN_SALT',   '6_&!ye2;QB<S&`t?pJUEo[6h{cwNSqVL-)vv5}dWv.u@{bM%v/vTMqgskVVKRR^C' );
define( 'NONCE_SALT',       'EHG[.@Ft:Lp#F90F7?k}g2@+|ai]<pTv[zT`-[#P/nK$ZCdQ[rm)}?3`sP!K*J*!' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'rawpersonaa_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
