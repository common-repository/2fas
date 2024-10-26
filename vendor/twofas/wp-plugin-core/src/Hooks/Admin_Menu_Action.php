<?php

namespace TwoFAS\Core\Hooks;

use TwoFAS\Core\Http\View_Response;

abstract class Admin_Menu_Action implements Hook_Interface {

	/**
	 * @var View_Response
	 */
	protected $response;

	public function register_hook() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	/**
	 * @param View_Response $response
	 */
	public function set_response( View_Response $response ) {
		$this->response = $response;
	}

	abstract public function add_menu();
}
