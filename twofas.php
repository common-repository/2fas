<?php
/**
 * Plugin Name: 2FAS Classic — Two Factor Authentication
 * Plugin URI:  https://wordpress.org/plugins/2fas/
 * Description: 2FAS strengthens WordPress admin security by requiring an additional verification code on untrusted devices.
 * Version:     3.2.0
 * Author:      Two Factor Authentication Service Inc.
 * Author URI:  https://2fas.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: 2fas
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) or die();

function twofas_start() {
	require_once 'constants.php';
	require_once 'start.php';
}

function twofas_delete_cron_jobs() {
	wp_clear_scheduled_hook( 'twofas_delete_expired_sessions' );
}

function twofas_load_plugin_textdomain() {
	load_plugin_textdomain( '2fas', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

register_deactivation_hook( __FILE__, 'twofas_delete_cron_jobs' );

add_action( 'init', 'twofas_start' );
add_action( 'plugins_loaded', 'twofas_load_plugin_textdomain' );
