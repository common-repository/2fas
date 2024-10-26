<?php

namespace TwoFAS\Core\Http\Middleware;

class Middleware_Bag {

	/**
	 * @var Middleware_Interface[]
	 */
	private $middleware = array();

	/**
	 * @param string               $key
	 * @param Middleware_Interface $middleware
	 */
	public function add_middleware( $key, Middleware_Interface $middleware ) {
		$this->middleware[ $key ] = $middleware;
	}

	/**
	 * @return Middleware_Interface[]
	 */
	public function get_middleware() {
		return $this->middleware;
	}
}
