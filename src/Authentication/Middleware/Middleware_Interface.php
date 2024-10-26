<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use WP_Error;
use WP_User;

interface Middleware_Interface {

	/**
	 * @param Middleware_Interface $next
	 */
	public function add_next( Middleware_Interface $next );

	/**
	 * @param WP_User|WP_Error                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null );
}
