<?php

namespace TwoFAS\Core\Http;

class Redirect_Response {

	/**
	 * @var URL_Interface
	 */
	protected $url;

	/**
	 * @param URL_Interface $url
	 */
	public function __construct( URL_Interface $url ) {
		$this->url = $url;
	}

	public function redirect() {
		header( 'Location: ' . $this->url->get() );
		exit;
	}
}
