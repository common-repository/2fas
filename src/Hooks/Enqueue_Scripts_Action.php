<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Account\Sdk as Account;
use TwoFAS\Api\Sdk as API;
use TwoFAS\Core\Environment_Interface;
use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\Core\Http\Request;
use TwoFAS\Core\Update\Update_Lock;
use TwoFAS\TwoFAS\Core\Plugin_Status;
use TwoFAS\TwoFAS\Helpers\Config;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Storage\Options_Storage;
use TwoFAS\TwoFAS\Templates\Twig;

class Enqueue_Scripts_Action implements Hook_Interface {

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var Environment_Interface
	 */
	private $environment;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Plugin_Status
	 */
	private $plugin_status;

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @var Update_Lock
	 */
	private $update_lock;

	/**
	 * @param Request               $request
	 * @param Config                $config
	 * @param Environment_Interface $environment
	 * @param Plugin_Status         $plugin_status
	 * @param Options_Storage       $options_storage
	 * @param Twig                  $twig
	 * @param Update_Lock           $update_lock
	 */
	public function __construct(
		Request $request,
		Config $config,
		Environment_Interface $environment,
		Plugin_Status $plugin_status,
		Options_Storage $options_storage,
		Twig $twig,
		Update_Lock $update_lock
	) {
		$this->request         = $request;
		$this->config          = $config;
		$this->environment     = $environment;
		$this->plugin_status   = $plugin_status;
		$this->options_storage = $options_storage;
		$this->twig            = $twig;
		$this->update_lock     = $update_lock;
	}

	public function register_hook() {
		add_action( 'login_enqueue_scripts', [ $this, 'enqueue_login' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin' ] );
	}

	public function enqueue_login() {
		$this->enqueue_common();

		wp_enqueue_script( 'twofas-login', TWOFAS_ASSETS_URL . 'js/login.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );

		if ( $this->plugin_status->client_completed_registration() ) {
			$this->enqueue_pusher();
		}
	}

	public function enqueue_admin() {
		$this->enqueue_common();

		if ( $this->is_plugin_page() ) {
			$this->enqueue_international_telephone_input();
			$this->enqueue_video_js();
			$this->enqueue_bootstrap();
			$this->enqueue_chart_js();
			$this->enqueue_dashboard();
		}

		$this->enqueue_deactivation_script();

		if ( $this->should_update_be_locked() ) {
			$this->enqueue_update_lock_scripts();
		}
	}

	private function enqueue_common() {
		$this->enqueue_sentry();
		wp_enqueue_style( 'google-fonts-roboto', 'https://fonts.googleapis.com/css?family=Roboto:400,500,700', [], null );
		wp_enqueue_style( 'twofas', TWOFAS_ASSETS_URL . 'css/twofas.min.css', [], TWOFAS_PLUGIN_VERSION );
		wp_enqueue_script( 'twofas-modal', TWOFAS_ASSETS_URL . 'js/modal.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
		wp_enqueue_script( 'twofas-totp-secret', TWOFAS_ASSETS_URL . 'js/totp-secret.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
		wp_enqueue_script( 'twofas-mobile-detect', TWOFAS_ASSETS_URL . 'js/mobile-detect.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
		wp_enqueue_script( 'twofas-device-type', TWOFAS_ASSETS_URL . 'js/device-type.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
	}

	private function enqueue_international_telephone_input() {
		wp_enqueue_style( 'international-telephone-input', TWOFAS_ASSETS_URL . 'css/intlTelInput.css', [], '9.2.0' );
		wp_enqueue_script( 'international-telephone-input-library', TWOFAS_ASSETS_URL . 'js/intlTelInput.min.js', [ 'jquery' ], '9.2.0', true );
	}

	private function enqueue_video_js() {
		wp_enqueue_style( 'video-js', TWOFAS_ASSETS_URL . 'css/video-js.min.css', [], '6.2.7' );
		wp_enqueue_script( 'video-js-library', TWOFAS_ASSETS_URL . 'js/video.min.js', [ 'jquery' ], '6.2.7', true );
	}

	private function enqueue_pusher() {
		wp_enqueue_script( 'pusher', 'https://js.pusher.com/5.1/pusher.min.js', [ 'jquery' ], '5.1.1', true );
		wp_enqueue_script( 'twofas-pusher-events', TWOFAS_ASSETS_URL . 'js/pusher-events.min.js', [
			'pusher',
			'jquery'
		], TWOFAS_PLUGIN_VERSION, true );

		$this->localize_script( 'twofas-pusher-events' );
	}

	private function enqueue_sentry() {
		wp_enqueue_script( 'sentry', 'https://browser.sentry-cdn.com/5.4.3/bundle.min.js', [ 'jquery' ], '5.4.3', true );
		wp_enqueue_script( 'twofas-sentry', TWOFAS_ASSETS_URL . 'js/sentry.min.js', [ 'sentry' ], TWOFAS_PLUGIN_VERSION, true );

		$data = [
			'sentryDsn'           => $this->config->get_sentry_dsn(),
			'whitelistUrls'       => TWOFAS_ASSETS_URL . 'js',
			'loggingAllowed'      => $this->options_storage->is_logging_allowed(),
			'release'             => TWOFAS_PLUGIN_VERSION,
			'wp_version'          => $this->environment->get_wordpress_version(),
			'api_sdk_version'     => API::VERSION,
			'account_sdk_version' => Account::VERSION,
			'loginPageUrl'        => wp_login_url(),
			'siteUrl'             => get_bloginfo( 'wpurl' ),
		];

		wp_localize_script( 'twofas-sentry', 'twofasSentry', $data );
	}

	private function enqueue_bootstrap() {
		wp_enqueue_style( 'twofas-bootstrap', TWOFAS_ASSETS_URL . 'css/bootstrap.min.css', [], '3.3.7' );
	}

	private function enqueue_chart_js() {
		wp_enqueue_script( 'twofas-chart-js', TWOFAS_ASSETS_URL . 'js/chart.min.js', [], TWOFAS_PLUGIN_VERSION, true );
	}

	private function enqueue_dashboard() {
		wp_enqueue_script( 'twofas-dashboard', TWOFAS_ASSETS_URL . 'js/dashboard.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
		wp_enqueue_script( 'twofas-user', TWOFAS_ASSETS_URL . 'js/user.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );

		$this->localize_script( 'twofas-dashboard' );
	}

	private function enqueue_deactivation_script() {
		wp_enqueue_script( 'twofas-deactivation', TWOFAS_ASSETS_URL . 'js/deactivation-form.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );

		$url = admin_url( 'admin.php?' . Action_Index::PAGE_KEY . '=' . Action_Index::SUBMENU_AJAX . '&' . Action_Index::TWOFAS_ACTION_KEY . '=' . Action_Index::ACTION_SEND_DEACTIVATION_REASON );

		$data = [
			'deactivationForm' => $this->twig->get_view( 'modals/deactivation-form.html.twig' ),
			'deactivationUrl'  => $url,
		];

		wp_localize_script( 'twofas-deactivation', 'twofasDeactivation', $data );
	}

	/**
	 * @return bool
	 */
	private function should_update_be_locked() {
		return ( $this->request->is_plugins_page() || $this->request->is_plugin_search_page() )
			&& $this->update_lock->is_locked();
	}

	private function enqueue_update_lock_scripts() {
		$wp_version = $this->environment->get_wordpress_version();

		if ( version_compare( $wp_version, '4.0', '<' ) ) {
			wp_enqueue_script( 'twofas-plugins', TWOFAS_ASSETS_URL . 'js/update-lock/plugins-38-to-44.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
			wp_enqueue_script( 'twofas-plugin-install', TWOFAS_ASSETS_URL . 'js/update-lock/plugin-install-38-to-39.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
		} elseif ( version_compare( $wp_version, '4.5', '<' ) ) {
			wp_enqueue_script( 'twofas-plugins', TWOFAS_ASSETS_URL . 'js/update-lock/plugins-38-to-44.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
			wp_enqueue_script( 'twofas-plugin-install', TWOFAS_ASSETS_URL . 'js/update-lock/plugin-install-40-to-51.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
		} elseif ( version_compare( $wp_version, '4.6', '<' ) ) {
			wp_enqueue_script( 'twofas-plugins', TWOFAS_ASSETS_URL . 'js/update-lock/plugins-45.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
			wp_enqueue_script( 'twofas-plugin-install', TWOFAS_ASSETS_URL . 'js/update-lock/plugin-install-40-to-51.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
		} else {
			wp_enqueue_script( 'twofas-plugins', TWOFAS_ASSETS_URL . 'js/update-lock/plugins-46-to-51.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
			wp_enqueue_script( 'twofas-plugin-install', TWOFAS_ASSETS_URL . 'js/update-lock/plugin-install-40-to-51.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
			wp_enqueue_script( 'twofas-ajax-search', TWOFAS_ASSETS_URL . 'js/update-lock/ajax-search.min.js', [ 'jquery' ], TWOFAS_PLUGIN_VERSION, true );
		}
	}

	/**
	 * @param string $handle
	 */
	private function localize_script( $handle ) {
		$pusher_key = $this->config->get_pusher_key();
		$page       = Action_Index::SUBMENU_AJAX;
		$action     = Action_Index::ACTION_AUTHENTICATE_CHANNEL;
		$auth_url   = admin_url( 'admin.php' );

		$auth_url = add_query_arg( [
			Action_Index::PAGE_KEY          => $page,
			Action_Index::TWOFAS_ACTION_KEY => $action,
		], $auth_url );

		$months = [
			__( 'January', '2fas' ),
			__( 'February', '2fas' ),
			__( 'March', '2fas' ),
			__( 'April', '2fas' ),
			__( 'May', '2fas' ),
			__( 'June', '2fas' ),
			__( 'July', '2fas' ),
			__( 'August', '2fas' ),
			__( 'September', '2fas' ),
			__( 'October', '2fas' ),
			__( 'November', '2fas' ),
			__( 'December', '2fas' )
		];

		$data = [
			'ajaxUrl'                   => admin_url( 'admin.php' ),
			'submenuDashboard'          => Action_Index::SUBMENU_DASHBOARD,
			'utilsUrl'                  => TWOFAS_ASSETS_URL . 'js/utils.js',
			'pusherKey'                 => $pusher_key,
			'authEndpoint'              => $auth_url,
			'months'                    => $months,
			'activeUsers'               => __( 'Active Users', '2fas' ),
			'inactiveUsers'             => __( 'Inactive Users', '2fas' ),
			'migratedUsers'             => __( 'Migrated users', '2fas' ),
			'migrationInProgress'       => __( 'Migration users in progress', '2fas'),
			'migrationCompleted'        => __( 'Migration users completed', '2fas'),
			'is_user_migration_allowed' => $this->options_storage->is_user_migration_allowed()
		];

		wp_localize_script( $handle, 'twofas', $data );
	}

	/**
	 * @return bool
	 */
	private function is_plugin_page() {
		$page = $this->request->get( Action_Index::PAGE_KEY );

		return $page === Action_Index::SUBMENU_DASHBOARD || $page === Action_Index::SUBMENU_CHANNEL;
	}
}
