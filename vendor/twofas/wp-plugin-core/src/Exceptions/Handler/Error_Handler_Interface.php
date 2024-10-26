<?php

namespace TwoFAS\Core\Exceptions\Handler;

use Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use WP_Error;

interface Error_Handler_Interface {

	/**
	 * @param Exception $e
	 * @param array     $options
	 *
	 * @return Error_Handler_Interface
	 */
	public function capture_exception( Exception $e, array $options = array() );

	/**
	 * @param Exception $e
	 *
	 * @return JSON_Response
	 */
	public function to_json( Exception $e );

	/**
	 * @param Exception $e
	 *
	 * @return View_Response
	 */
	public function to_view( Exception $e );

	/**
	 * @param Exception $e
	 *
	 * @return WP_Error
	 */
	public function to_wp_error( Exception $e );

	/**
	 * @param Exception $e
	 * @param string    $css_class
	 *
	 * @return string
	 */
	public function to_notification( Exception $e, $css_class );
}
