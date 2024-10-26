<?php

namespace TwoFAS\TwoFAS\Http;

use TwoFAS\Core\Http\Cookie as Base_Cookie;

class Cookie extends Base_Cookie {

	const SEVERAL_DOZEN_YEARS_IN_SECONDS = 2147483647;

	/**
	 * @var array
	 */
	private $plugin_cookie_patterns = [
		'/^twofas_remember_me$/i',
		'/^twofas_trusted_device_[a-f0-9]{32}$/i',
	];

	/**
	 * @return array
	 */
	protected function get_plugin_cookies() {
		$all_cookies    = $this->get_cookies();
		$plugin_cookies = [];

		foreach ( $this->plugin_cookie_patterns as $pattern ) {
			$result         = preg_grep( $pattern, $all_cookies );
			$plugin_cookies = array_merge( $plugin_cookies, $result );
		}

		return $plugin_cookies;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param int    $lifespan
	 * @param bool   $http_only
	 */
	protected function write_cookie( $name, $value, $lifespan, $http_only = false ) {
		setcookie( $name, $value, time() + $lifespan, '/', '', false, $http_only );
	}
}
