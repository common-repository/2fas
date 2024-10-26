<?php

namespace TwoFAS\TwoFAS\Exceptions\Handler;

use Exception;
use Raven_Client;
use TwoFAS\Account\Sdk as Account;
use TwoFAS\Api\Sdk as API;
use TwoFAS\Core\Exceptions\Handler\Logger_Interface;
use TwoFAS\TwoFAS\Helpers\Config;
use TwoFAS\TwoFAS\Helpers\Environment;

class Sentry_Logger implements Logger_Interface {

	/**
	 * @var Raven_Client
	 */
	private $client;

	/**
	 * @param Config      $config
	 * @param Environment $environment
	 */
	public function __construct( Config $config, Environment $environment ) {
		$options = [
			'processors'    => [
				'Raven_Processor_RemoveCookiesProcessor',
			],
			'send_callback' => function ( &$event ) {
				if ( wp_login_url() === $event['request']['url'] ) {
					$site_url                = get_bloginfo( 'wpurl' );
					$event['request']['url'] = '[Filtered: ' . $site_url . ']';
				}
			}
		];

		$this->client = new Raven_Client( $config->get_sentry_dsn(), $options );
		$this->client->tags_context(
			[
				'php_version'         => phpversion(),
				'wp_version'          => $environment->get_wordpress_version(),
				'api_sdk_version'     => API::VERSION,
				'account_sdk_version' => Account::VERSION
			] );

		$this->client->setRelease( TWOFAS_PLUGIN_VERSION );
	}

	/**
	 * @param Exception $e
	 * @param array     $options
	 */
	public function capture_exception( Exception $e, array $options = [] ) {
		$this->client->captureException( $e, $options );
	}
}
