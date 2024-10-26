<?php

namespace TwoFAS\TwoFAS\Integration;

class Integration_Name {

	/**
	 * @var string
	 */
	private $app_url;

	/**
	 * @param string $app_url
	 */
	public function __construct( $app_url ) {
		$this->app_url = $app_url;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return preg_replace( '/^https?:\/\//', '', $this->app_url );
	}
}
