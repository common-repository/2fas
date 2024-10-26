<?php

namespace TwoFAS\Core\Http;

class Direct_URL implements URL_Interface {

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @param string $url
	 */
	public function __construct( $url ) {
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function get() {
		return $this->url;
	}
}
