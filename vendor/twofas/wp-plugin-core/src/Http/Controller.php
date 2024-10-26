<?php

namespace TwoFAS\Core\Http;

abstract class Controller {

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
	 * @param URL_Interface $url
	 *
	 * @return Redirect_Response
	 */
	protected function redirect( URL_Interface $url ) {
		return new Redirect_Response( $url );
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
}
