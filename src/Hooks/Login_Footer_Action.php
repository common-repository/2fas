<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Templates\Twig;

class Login_Footer_Action implements Hook_Interface {

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param Twig    $twig
	 * @param Request $request
	 */
	public function __construct( Twig $twig, Request $request ) {
		$this->twig    = $twig;
		$this->request = $request;
	}

	public function register_hook() {
		$interim_login = $this->request->request( 'interim-login' );

		if ( is_null( $interim_login ) ) {
			add_action( 'login_footer', [ $this, 'add_footer' ] );
		}
	}

	public function add_footer() {
		echo $this->twig->get_view( 'login/login-footer.html.twig' );
	}
}
