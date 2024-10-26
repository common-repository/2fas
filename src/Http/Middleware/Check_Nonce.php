<?php

namespace TwoFAS\TwoFAS\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\Core\Http\Request;

class Check_Nonce extends Middleware {

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
		$action = $this->request->action();
		$nonce  = $this->request->get_nonce();

		if ( $this->request->is_post() && ! $this->check( $nonce, $action ) ) {
			if ( $this->request->is_ajax() ) {
				return $this->json( [
					'error' => 'Security token is invalid.',
				], 403 );
			}

			$this->flash->add_message( 'error', 'csrf' );

			return $this->redirect( new Action_URL( $this->request->page(), $this->request->referer_action() ) );
		}

		return $this->next->handle();
	}

	/**
	 * @param string $nonce
	 * @param string $action
	 *
	 * @return bool
	 */
	private function check( $nonce, $action ) {
		return false !== wp_verify_nonce( $nonce, $action );
	}
}
