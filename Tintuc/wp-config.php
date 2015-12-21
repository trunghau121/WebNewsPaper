<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'tintuc');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '12345');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Rxxw/HeMb<HT}C6 p8SH>^VxmzN]eV oL]m|Bs3.>|ZJ3!T0+KE%]|k~{k^^Ia} ');
define('SECURE_AUTH_KEY',  '-ylk+Gs/XT[@yA~DI~T(7*$xQ+H$W~#!9(er]0[F8dKb7d`CJD?F1#Vc&Xf+@/:m');
define('LOGGED_IN_KEY',    '/`_Z~[<Ng>-UdWjWx$&]`5-*~m&EOjZrWLM_v-wXpyN;,0h{D~TX.u4(U|.W]iQ:');
define('NONCE_KEY',        'qO7KS[ymgY]|t{iWwm@OzQ%FC--Zc -RB7WH< !%&rix:HB?uW=5_!Ly%3Z#4):x');
define('AUTH_SALT',        '1H=A,U(+E81 >qC~Dxs&^[vcn-GJ;81kG.:6N8$-n|&<D2f-51awf+2x.;6cqI@U');
define('SECURE_AUTH_SALT', '@X?MF@Gmz@*T&SiOP!6snYB*pz+!w$?=sdgqSz5m|`}w*E1PNV}g<mB>3M5$4Wpe');
define('LOGGED_IN_SALT',   'oT2ZkyI|/*J&>OPIA% x(%@Jb}-D.O+UfR&GCQ[Bl}Fy{FM_4IGc!><F>l7{-~D_');
define('NONCE_SALT',       'CQ)  HEU_zuxWANq~jf&d8ym+xPyc430?<J3LVr?e-VtW17>E*7K-39^Kf+Cg5[x');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
