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
 * This has been slightly modified (to read environment variables) for use in Docker.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// IMPORTANT: this file needs to stay in-sync with https://github.com/WordPress/WordPress/blob/master/wp-config-sample.php

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', getenv('DB_NAME') ?: 'wordpress' );

/** Database username */
define( 'DB_USER', getenv('DB_USER') ?: 'wp_user' );

/** Database password */
define( 'DB_PASSWORD', getenv('DB_PASSWORD') ?: 'wp_password' );

/** Database hostname */
define( 'DB_HOST', getenv('DB_HOST') ?: 'db' );

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
define( 'AUTH_KEY',         'f54358ef74b9262284abb71400c00cd2c6ddf44a' );
define( 'SECURE_AUTH_KEY',  'da6620cb17bed09b7f7de664508a4d7784e6edf8' );
define( 'LOGGED_IN_KEY',    '29d812ca9399182af59f7d1ff2d678c5ec0a6238' );
define( 'NONCE_KEY',        '929c77760ed4b0e4bc3bd6db900b7494fbaa9370' );
define( 'AUTH_SALT',        '579aa6a66e158552bfd023aa90630f42b552ea29' );
define( 'SECURE_AUTH_SALT', '562aa50d2604e9140e7612e317b5f21d4ee67e4f' );
define( 'LOGGED_IN_SALT',   'a0200c406059e6f531d78659fdbb67e00c1c701e' );
define( 'NONCE_SALT',       '78f809d1da1bdf3eca5861a28fff512ee6a8ba06' );

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
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

// If we're behind a proxy server and using HTTPS, we need to alert WordPress of that fact
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
        $_SERVER['HTTPS'] = 'on';
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
