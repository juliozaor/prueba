<?php
define('WP_AUTO_UPDATE_CORE', 'minor');
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

define('WP_CACHE', true);
define( 'WPCACHEHOME', '/home/tiendacristar/public_html/wp-content/plugins/wp-super-cache/' );
define( 'DB_NAME', 'tiendacr_wpsitio' );

/** MySQL database username */
define( 'DB_USER', 'tiendacr_wpuser' );

/** MySQL database password */
define( 'DB_PASSWORD', '9$O~QVnKpH9U' );

/** MySQL hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define( 'AUTH_KEY',         'fiaspmqriunsxz8t2qbwi30qibusxnffuytfabdb5uqg4v2mn11wjhj33cwywurp' );
define( 'SECURE_AUTH_KEY',  's7brblw3i2ktgqj82bme1ujve4znk3ddqdvbeh95j7ibdboyeaaxaqujdud7eofx' );
define( 'LOGGED_IN_KEY',    'azcrkpolherni5hwia5xpwbphejoooqhatp6la1ye95i5scgzlkkhda7dbdzokoq' );
define( 'NONCE_KEY',        'kxxhjxrv8k5altxpgbgftuyg2zxanqmqe9ome61zofrntwufu1b3ydijggo22l2k' );
define( 'AUTH_SALT',        'ndq1fd8eoswhurshoz5fab7ovswxivtis4lfspskotb96ek4ids4f0yyzeckpssv' );
define( 'SECURE_AUTH_SALT', 'se7bltgo7wlujcprln8o2ujbygdf2artejv0ymwrqzhuliad2qcfb2npbhvxttze' );
define( 'LOGGED_IN_SALT',   '2nrans0cf1qm9gttudsp0wmrowrq2ctu6hgrlhfcc4ageipv45ly7cpsffao2itg' );
define( 'NONCE_SALT',       'bt8eblz6cccmwdfhmw2drdfei00twlziir8hi6ckdsejtxhmg9kewdsgvtrdbhfe' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpli_';

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
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_DEBUG_LOG', true );
define('ALLOW_UNFILTERED_UPLOADS', true);
/* Multisite */
define('WP_ALLOW_MULTISITE', true );
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', 'tienda.cristar.com.co');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);
define ('WP_MEMORY_LIMIT', '256M' );

/* That's all, stop editing! Happy publishing. */
define( 'WPOSES_AWS_ACCESS_KEY_ID', 'AKIAR453K6UFAQ773B7J' );
define( 'WPOSES_AWS_SECRET_ACCESS_KEY','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' );
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
