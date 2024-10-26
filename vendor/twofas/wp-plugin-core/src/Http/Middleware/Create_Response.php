<?php

namespace TwoFAS\Core\Http\Middleware;

use TwoFAS\Core\Http\Controller;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\Request;
use TwoFAS\Core\Http\View_Response;

class Create_Response extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Controller
	 */
	private $controller;

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @param Request    $request
	 * @param Controller $controller
	 * @param string     $method
	 */
	public function __construct( Request $request, Controller $controller, $method ) {
		$this->request    = $request;
		$this->controller = $controller;
		$this->method     = $method;
	}

	/**
	 * @return JSON_Response|Redirect_Response|View_Response
	 */
	public function handle() {
		return $this->controller->{$this->method}( $this->request );
	}
}
