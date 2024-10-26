<?php

namespace TwoFAS\Core\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\URL_Interface;
use TwoFAS\Core\Http\View_Response;

abstract class Middleware implements Middleware_Interface {

	/**
	 * @var Middleware_Interface
	 */
	protected $next;

	/**
	 * @param Middleware_Interface $next
	 */
	public function add_next( Middleware_Interface $next ) {
		$this->next = $next;
	}

	/**
	 * @param string $template_name
	 * @param array  $data
	 *
	 * @return View_Response
	 */
	protected function view( $template_name, array $data = array() ) {
		return new View_Response( $template_name, $data );
	}

	/**
	 * @param array $body
	 * @param int   $status_code
	 *
	 * @return JSON_Response
	 */
	protected function json( array $body, $status_code = 200 ) {
		return new JSON_Response( $body, $status_code );
	}

	/**
	 * @param URL_Interface $url
	 *
	 * @return Redirect_Response
	 */
	protected function redirect( URL_Interface $url ) {
		return new Redirect_Response( $url );
	}
}
