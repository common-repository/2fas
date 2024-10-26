<?php

namespace TwoFAS\Core\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;

interface Middleware_Interface {

	/**
	 * @param Middleware_Interface $next
	 */
	public function add_next( Middleware_Interface $next );

	/**
	 * @return View_Response|Redirect_Response|JSON_Response
	 */
	public function handle();
}
