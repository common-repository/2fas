<?php

use TwoFAS\TwoFAS\Core\Plugin;
use TwoFAS\TwoFAS\Listeners\Listener_Provider;
use TwoFAS\TwoFAS\Requirements\Requirement_Checker;
use TwoFAS\TwoFAS\Update\Migrator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TWOFAS_PLUGIN_PATH . 'vendor/autoload.php';
require_once TWOFAS_PLUGIN_PATH . 'dependencies.php';

function twofas_prime_plugin_active() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( is_multisite() ) {
		$active_plugins = array_keys(get_site_option('active_sitewide_plugins'));
	} else {
		$active_plugins = get_option( 'active_plugins' );
	}
	$result = false;

	foreach ( $active_plugins as $data ) {
		$result |= ( preg_match( '/\/twofas_light\.php/', $data ) === 1 );
	}

	return $result;
}

/** @var Requirement_Checker $requirements */
$requirements = $twofas_container->get( Requirement_Checker::class );
$is_admin     = current_user_can( 'manage_options' );

if ( $requirements->are_satisfied() ) {
	/** @var Migrator $migrator */
	$migrator = $twofas_container->get( Migrator::class );

	if ( $is_admin && twofas_prime_plugin_active() && ! $migrator->is_migrated( 'Migration_2021_05_01_Migrate_Users' ) ) {
		add_action( 'admin_notices', function () {
			$message = __( 'Migrate all users from 2FAS Classic to 2FAS Prime plugin! You can do that in 2FAS Classic dashboard, by clicking green "Migrate" button.', '2fas' );
			echo '<div class="notice notice-info info twofas-info-notice"><p>' . $message . '</p></div>';
		} );
	}

	if ( $is_admin ) {
		add_action( 'admin_notices', function () {
			echo '
			<div class="notice notice-info info twofas-info-notice">
				<p>
					<strong>2FAS Classic plugin is obsolete!</strong><br/>
					On the 1st of December 2021, we will turn off push notifications but logging with 2FA tokens will work. <br />
					From this day on 2FAS Classic plugin will not be supported anymore. <br />
					We recommend you to download the 2FAS Prime plugin and then migrate all your settings and tokens from 2FAS Classic to the 2FAS Prime plugin.
				</p>
			</div>';
		} );
	}

	/** @var Listener_Provider $listener */
	$listener = $twofas_container->get( Listener_Provider::class );
	$listener->listen();

	/** @var Plugin $plugin */
	$plugin = $twofas_container->get( Plugin::class );
	$plugin->run();
} else {
	if ( ! $is_admin ) {
		return;
	}

	foreach ( $requirements->get_not_satisfied() as $message ) {
		add_action( 'admin_notices', function () use ( $message ) {
			echo '<div class="notice notice-error error"><p>' . $message . '</p></div>';
		} );
	}
}
