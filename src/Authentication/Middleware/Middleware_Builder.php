<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

class Middleware_Builder {

	/**
	 * @var array
	 */
	private $middleware = [];

	/**
	 * @param Middleware_Interface $middleware
	 *
	 * @return Middleware_Builder
	 */
	public function add_middleware( Middleware_Interface $middleware ) {
		$this->middleware[] = $middleware;

		return $this;
	}

	/**
	 * @return Middleware_Interface
	 */
	public function build() {
		$first = array_shift( $this->middleware );

		array_reduce( $this->middleware, function ( Middleware_Interface $current, Middleware_Interface $next ) {
			$current->add_next( $next );

			return $next;
		}, $first );

		return $first;
	}
}
