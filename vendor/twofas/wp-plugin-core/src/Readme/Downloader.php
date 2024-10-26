<?php

namespace TwoFAS\Core\Readme;

use TwoFAS\Core\Exceptions\Download_Exception;
use TwoFAS\Core\Exceptions\Parse_Exception;

class Downloader implements Downloader_Interface {

	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @param Parser $parser
	 */
	public function __construct( Parser $parser ) {
		$this->parser = $parser;
	}

	/**
	 * @param string $url
	 *
	 * @return Readme
	 *
	 * @throws Download_Exception
	 * @throws Parse_Exception
	 */
	public function download( $url ) {
		$response  = wp_safe_remote_get( $url );
		$http_code = wp_remote_retrieve_response_code( $response );
		$body      = wp_remote_retrieve_body( $response );

		if ( 200 === $http_code ) {
			return $this->parser->parse( $body );
		}

		throw new Download_Exception( 'Readme could not be downloaded.' );
	}
}
