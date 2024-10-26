<?php

namespace TwoFAS\TwoFAS\Authentication\Handler;

class Handler_Builder {

	/**
	 * @var array
	 */
	private $handlers = [];

	/**
	 * @param Login_Handler $handler
	 *
	 * @return Handler_Builder
	 */
	public function add_handler( Login_Handler $handler ) {
		$this->handlers[] = $handler;

		return $this;
	}

	/**
	 * @return Login_Handler
	 */
	public function build() {
		$first = array_shift( $this->handlers );

		array_reduce( $this->handlers, function ( Login_Handler $current, Login_Handler $next ) {
			$current->then( $next );

			return $next;
		}, $first );

		return $first;
	}
}
