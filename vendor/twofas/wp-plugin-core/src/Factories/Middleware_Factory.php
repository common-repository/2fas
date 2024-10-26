<?php

namespace TwoFAS\Core\Factories;

use LogicException;
use TwoFAS\Core\Http\Middleware\Create_Response;
use TwoFAS\Core\Http\Middleware\Middleware_Bag;
use TwoFAS\Core\Http\Middleware\Middleware_Interface;

class Middleware_Factory {

	/**
	 * @var Middleware_Bag
	 */
	private $middleware_bag;

	/**
	 * @param Middleware_Bag $middleware_bag
	 */
	public function __construct( Middleware_Bag $middleware_bag ) {
		$this->middleware_bag = $middleware_bag;
	}

	/**
	 * @param array           $middleware_keys
	 * @param Create_Response $create_response
	 *
	 * @return Middleware_Interface
	 */
	public function create( array $middleware_keys, Create_Response $create_response ) {
		$ordered_middleware   = $this->select_middleware( $middleware_keys );
		$ordered_middleware[] = $create_response;

		return $this->create_chain( $ordered_middleware );
	}

	/**
	 * @param array $middleware_keys
	 *
	 * @return array
	 */
	private function select_middleware( array $middleware_keys ) {
		$all_middleware = $this->middleware_bag->get_middleware();

		return array_map( function ( $middleware_name ) use ( $all_middleware ) {
			if ( array_key_exists( $middleware_name, $all_middleware ) ) {
				return $all_middleware[ $middleware_name ];
			}

			throw new LogicException( 'Some middleware does not exist.' );
		}, $middleware_keys );
	}

	/**
	 * @param array $ordered_middleware
	 *
	 * @return Middleware_Interface
	 */
	private function create_chain( array $ordered_middleware ) {
		$first = array_shift( $ordered_middleware );

		array_reduce( $ordered_middleware, function ( Middleware_Interface $current, Middleware_Interface $next ) {
			$current->add_next( $next );

			return $next;
		}, $first );

		return $first;
	}
}
