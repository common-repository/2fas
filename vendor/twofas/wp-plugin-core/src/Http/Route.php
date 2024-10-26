<?php

namespace TwoFAS\Core\Http;

use TwoFAS\Core\Exceptions\Method_Not_Allowed_Http_Exception;
use TwoFAS\Core\Exceptions\Not_Found_Http_Exception;

class Route {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var array
	 */
	private $routes;

	/**
	 * @param Request $request
	 * @param array   $routes
	 */
	public function __construct( Request $request, array $routes ) {
		$this->request = $request;
		$this->routes  = $routes;
	}

	/**
	 * @return array
	 */
	public function get_routes() {
		return $this->routes;
	}

	/**
	 * @return array
	 *
	 * @throws Not_Found_Http_Exception
	 * @throws Method_Not_Allowed_Http_Exception
	 */
	public function create_route() {
		$page   = $this->request->page();
		$action = $this->request->action();
		$method = $this->request->http_method();

		if ( ! $this->can_process_route( $page, $action ) ) {
			throw new Not_Found_Http_Exception();
		}

		if ( ! $this->method_allowed( $page, $action, $method ) ) {
			throw new Method_Not_Allowed_Http_Exception();
		}

		return $this->match( $page, $action );
	}

	/**
	 * @param string $page
	 * @param string $action
	 *
	 * @return bool
	 */
	private function can_process_route( $page, $action ) {
		if ( empty( $page ) ) {
			return false;
		}

		return array_key_exists( $page, $this->routes )
			&& array_key_exists( $action, $this->routes[ $page ] );
	}

	/**
	 * @param string $page
	 * @param string $action
	 * @param string $method
	 *
	 * @return bool
	 */
	private function method_allowed( $page, $action, $method ) {
		return in_array( $method, $this->routes[ $page ][ $action ]['method'], true );
	}

	/**
	 * @param string $page
	 * @param string $action
	 *
	 * @return array
	 */
	private function match( $page, $action ) {
		return array(
			'controller' => $this->routes[ $page ][ $action ]['controller'],
			'action'     => $this->routes[ $page ][ $action ]['action'],
			'middleware' => $this->get_middleware( $page, $action ),
		);
	}

	/**
	 * @param string $page
	 * @param string $action
	 *
	 * @return array
	 */
	private function get_middleware( $page, $action ) {
		$middleware = array();

		if ( array_key_exists( 'middleware', $this->routes[ $page ][ $action ] ) ) {
			$middleware = $this->routes[ $page ][ $action ]['middleware'];
		}

		return $middleware;
	}
}
