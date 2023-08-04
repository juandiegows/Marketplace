<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'marketplace' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '12345' );

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
define( 'AUTH_KEY',         '~Wg`U3JzjoGL%gOd{>Un ?/)YO}>bg#&)v4OX4eDHQWYk*Qg1|pC%.o|lN|~KvMw' );
define( 'SECURE_AUTH_KEY',  'A!9:_vg=Z{F6=BO+ ;tQkA7(L 9ZO$=/AOZ)*(.hmj#??)JS_r/tuO^/<z8BD;4W' );
define( 'LOGGED_IN_KEY',    '8<Qp>G~XmeX72-_Ag^lDt.Qdd`R%5YR2BmkeG,TYqkfMEV,^gQqcy|rKEVy/g#eS' );
define( 'NONCE_KEY',        '|<lF;#xxe;DV4bU5<6UkIHP[?-%MNj&]6KSbnk.3)Ix56y3qSBrA9xc7hk~,]]hd' );
define( 'AUTH_SALT',        'cuYFii.a+*vvaFD8.b]o^vlRx^`=&1qgF{33@cQXG@ue2uy@;7gRL[eMj^kYe[c%' );
define( 'SECURE_AUTH_SALT', 'F5I( *!/T6eGT?6 m9:30CZ$d <:l$`#ESeD<wlc8bk>U<M8AM/1l7XU04.t7E{%' );
define( 'LOGGED_IN_SALT',   '<(K(*j_91?)r1,B(%ghO6p.gx6wNv;~;ij][RPYLwofan;k3P) k45BLn+J%UV|H' );
define( 'NONCE_SALT',       '^5|S7TI`h[;m?5mEH<.}jcSBc2qF=4|]Tm=vq)jh=y6gzGq&?][ETsl]wAZu/`bo' );

/**#@-*/

/**
 * WordPress database table prefix.
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
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
