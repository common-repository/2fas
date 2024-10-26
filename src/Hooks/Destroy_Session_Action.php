<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\TwoFAS\Http\Session;

class Destroy_Session_Action implements Hook_Interface {

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @param Session $session
	 */
	public function __construct( Session $session ) {
		$this->session = $session;
	}

	public function register_hook() {
		add_action( 'wp_logout', [ $this->session, 'destroy' ] );
	}
}
