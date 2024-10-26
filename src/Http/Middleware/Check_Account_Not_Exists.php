<?php

namespace TwoFAS\TwoFAS\Http\Middleware;

use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\TwoFAS\Core\Plugin_Status;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;

class Check_Account_Not_Exists extends Middleware {

	/**
	 * @var Plugin_Status
	 */
	private $plugin_status;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @param Plugin_Status $plugin_status
	 * @param Flash         $flash
	 */
	public function __construct( Plugin_Status $plugin_status, Flash $flash ) {
		$this->plugin_status = $plugin_status;
		$this->flash         = $flash;
	}

	/**
	 * @return null|Redirect_Response
	 */
	public function handle() {
		if ( $this->plugin_status->client_completed_registration() ) {
			$this->flash->add_message( 'error', 'account-exists' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD ) );
		}

		return $this->next->handle();
	}
}
