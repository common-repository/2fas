<?php

use TwoFAS\Core\Storage\DB_Wrapper;
use TwoFAS\TwoFAS\Core\Uninstaller;
use TwoFAS\TwoFAS\Exceptions\Handler\Error_Handler;
use TwoFAS\TwoFAS\Exceptions\Handler\Sentry_Logger;
use TwoFAS\TwoFAS\Factories\Account_Factory;
use TwoFAS\TwoFAS\Factories\API_Factory;
use TwoFAS\TwoFAS\Helpers\Config;
use TwoFAS\TwoFAS\Helpers\Environment;
use TwoFAS\TwoFAS\Http\Cookie;
use TwoFAS\TwoFAS\Http\Request;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Integration\Integration_Name;
use TwoFAS\TwoFAS\Storage\Authentication_Storage;
use TwoFAS\TwoFAS\Storage\In_Memory_Session_Storage;
use TwoFAS\TwoFAS\Storage\OAuth_Storage;
use TwoFAS\TwoFAS\Storage\Options_Storage;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\Trusted_Devices_Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;
use TwoFAS\TwoFAS\Update\Migrator;
use TwoFAS\TwoFAS\Update\Plugin_Version;
use WhichBrowser\Parser;

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once 'vendor/autoload.php';
require_once 'constants.php';

/**
 * Should be done this way because we cannot use PHPDI on uninstall when another plugin use it (uninstallation fails)
 */
global $wpdb;
$db_wrapper       = new DB_Wrapper( $wpdb );
$options_storage  = new Options_Storage();
$user_storage     = new User_Storage( $db_wrapper );
$oauth_storage    = new OAuth_Storage();
$cookie_storage   = new Cookie( $_COOKIE );
$request          = new Request( [], [], [], $cookie_storage );
$storage          = new Storage(
	$cookie_storage,
	$options_storage,
	$user_storage,
	$oauth_storage,
	new In_Memory_Session_Storage(),
	new Authentication_Storage( $db_wrapper ),
	new Trusted_Devices_Storage( $db_wrapper, $request ) );
$plugin_version   = new Plugin_Version( $options_storage );
$environment      = new Environment();
$config           = new Config( TWOFAS_PLUGIN_PATH );
$browser          = new Parser();
$account_factory  = new Account_Factory( $environment, $config, $oauth_storage, $browser );
$api_factory      = new API_Factory( $environment, $config, $oauth_storage, $browser );
$integration_name = new Integration_Name( $environment->get_wordpress_app_url() );
$api_wrapper      = new API_Wrapper( $account_factory, $api_factory, $options_storage, $integration_name );
$migrator         = new Migrator( $db_wrapper, $plugin_version, $api_wrapper, $storage );
$logger           = new Sentry_Logger( $config, $environment );
$error_handler    = new Error_Handler( $logger, false );

$uninstaller = new Uninstaller( $migrator, $storage, $cookie_storage, $api_wrapper, $error_handler );
$uninstaller->uninstall();
