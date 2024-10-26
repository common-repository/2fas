<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\Direct_URL;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use WP_Error;
use WP_User;

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
	 * @param WP_User|WP_Error                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	protected function run_next( $user, $response ) {
		if ( is_null( $this->next ) ) {
			return $response;
		}

		return $this->next->handle( $user, $response );
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
	 * @param string $message
	 * @param int    $status_code
	 *
	 * @return JSON_Response
	 */
	protected function json_error( $message, $status_code ) {
		return $this->json( [ 'error' => $message ], $status_code );
	}

	/**
	 * @param string $url
	 *
	 * @return Redirect_Response
	 */
	protected function redirect( $url ) {
		return new Redirect_Response( new Direct_URL( $url ) );
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	protected function is_wp_user( $user ) {
		return $user instanceof WP_User;
	}
}
