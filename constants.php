<?php

$plugin_file_path = __DIR__ . '/twofas.php';
$plugin_path      = plugin_dir_path( $plugin_file_path );
$plugin_url       = plugin_dir_url( $plugin_file_path );
$plugin_basename  = plugin_basename( $plugin_file_path );
$assets_url       = $plugin_url . 'assets/';
$templates_path   = $plugin_path . 'templates/';
$admin_url        = get_admin_url();

define( 'TWOFAS_PLUGIN_PATH', $plugin_path );
define( 'TWOFAS_PLUGIN_URL', $plugin_url );
define( 'TWOFAS_PLUGIN_BASENAME', $plugin_basename );
define( 'TWOFAS_ASSETS_URL', $assets_url );
define( 'TWOFAS_TEMPLATES_PATH', $templates_path );
define( 'TWOFAS_WP_ADMIN_PATH', $admin_url );
define( 'TWOFAS_PLUGIN_VERSION', '3.2.0' );
define( 'TWOFAS_DEPRECATE_PHP_OLDER_THAN', '5.6' );
