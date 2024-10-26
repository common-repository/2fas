<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\TwoFAS\Authentication\Authenticator;
use TwoFAS\TwoFAS\Authentication\Step_Token;
use TwoFAS\TwoFAS\Http\Session;
use TwoFAS\TwoFAS\Storage\User_Storage;

class Clean_Login_Process_Action implements Hook_Interface {

	/**
	 * @var Step_Token
	 */
	private $step_token;

	/**
	 * @var Authenticator
	 */
	private $authenticator;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @param Step_Token    $step_token
	 * @param Authenticator $authenticator
	 * @param Session       $session
	 * @param User_Storage  $user_storage
	 */
	public function __construct( Step_Token $step_token, Authenticator $authenticator, Session $session, User_Storage $user_storage ) {
		$this->step_token    = $step_token;
		$this->authenticator = $authenticator;
		$this->session       = $session;
		$this->user_storage  = $user_storage;
	}

	public function register_hook() {
		add_action( 'wp_login', [ $this, 'clean' ], 5 );
	}

	public function clean() {
		if ( $this->user_storage->is_wp_user_set() ) {
			$this->step_token->reset();
			$this->session->destroy();
			$this->authenticator->close_authentication();
		}
	}
}
