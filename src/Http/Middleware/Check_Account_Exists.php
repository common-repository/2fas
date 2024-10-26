<?php

namespace TwoFAS\TwoFAS\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Core\Plugin_Status;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Templates\Views;
use TwoFAS\TwoFAS\User\Capabilities;

class Check_Account_Exists extends Middleware {

	/**
	 * @var Plugin_Status
	 */
	private $plugin_status;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param Plugin_Status $plugin_status
	 * @param Flash         $flash
	 * @param Request       $request
	 */
	public function __construct( Plugin_Status $plugin_status, Flash $flash, Request $request ) {
		$this->plugin_status = $plugin_status;
		$this->flash         = $flash;
		$this->request       = $request;
	}

	/**
	 * @return null|View_Response|Redirect_Response|JSON_Response
	 */
	public function handle() {
		if ( ! $this->plugin_status->client_completed_registration() ) {
			if ( current_user_can( Capabilities::ADMIN ) ) {
				return $this->get_admin_response();
			}

			return $this->get_user_response();
		}

		return $this->next->handle();
	}

	/**
	 * @return JSON_Response|Redirect_Response
	 */
	private function get_admin_response() {
		if ( $this->request->is_ajax() ) {
			return $this->json( [
				'error' => "2FAS account doesn't exist",
			], 403 );
		}

		$this->flash->add_message( 'error', 'account-required' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD, Action_Index::ACTION_CREATE_ACCOUNT ) );
	}

	/**
	 * @return JSON_Response|View_Response
	 */
	private function get_user_response() {
		if ( $this->request->is_ajax() ) {
			return $this->json( [
				'error' => "2FAS account doesn't exist",
			], 403 );
		}

		return $this->view( Views::NOT_ENABLED );
	}
}
