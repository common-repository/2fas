<?php

namespace TwoFAS\TwoFAS\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\Core\Http\Request;

class Check_Ajax extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @param Request $request
	 * @param Flash   $flash
	 */
	public function __construct( Request $request, Flash $flash ) {
		$this->request = $request;
		$this->flash   = $flash;
	}

	/**
	 * @return JSON_Response|Redirect_Response|View_Response
	 */
	public function handle() {
		if ( ! $this->check() ) {
			$this->flash->add_message( 'error', 'ajax' );

			return $this->redirect( new Action_URL( $this->request->page() ) );
		}

		return $this->next->handle();
	}

	/**
	 * @return bool
	 */
	private function check() {
		return $this->request->is_ajax();
	}
}
