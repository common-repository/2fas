<?php

namespace TwoFAS\TwoFAS\Factories;

use TwoFAS\TwoFAS\Helpers\Config;
use TwoFAS\TwoFAS\Helpers\Environment;
use TwoFAS\TwoFAS\Storage\OAuth_Storage;
use WhichBrowser\Parser;

abstract class SDK_Factory {

	/**
	 * @var Environment
	 */
	protected $environment;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var OAuth_Storage
	 */
	protected $oauth_storage;

	/**
	 * @var Parser
	 */
	protected $browser;

	/**
	 * @param Environment   $environment
	 * @param Config        $config
	 * @param OAuth_Storage $oauth_storage
	 * @param Parser        $browser
	 */
	public function __construct( Environment $environment, Config $config, OAuth_Storage $oauth_storage, Parser $browser ) {
		$this->environment   = $environment;
		$this->config        = $config;
		$this->oauth_storage = $oauth_storage;
		$this->browser       = $browser;
	}

	/**
	 * @return array
	 */
	protected function get_headers() {
		return [
			'Plugin-Version'  => TWOFAS_PLUGIN_VERSION,
			'App-Version'     => $this->environment->get_wordpress_version(),
			'App-Name'        => $this->environment->get_wordpress_app_name(),
			'App-Url'         => $this->environment->get_wordpress_app_url(),
			'Browser-Version' => $this->browser->toString(),
			'Php-Version'     => PHP_VERSION,
		];
	}
}
