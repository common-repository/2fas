<?php

namespace TwoFAS\TwoFAS\Authentication;

use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Http\Session;
use TwoFAS\TwoFAS\Randomization\Hash;
use TwoFAS\TwoFAS\Storage\User_Storage;

class Step_Token {

	const STEP_TOKEN_KEY = 'step_token_key';

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @param Session      $session
	 * @param User_Storage $user_storage
	 */
	public function __construct( Session $session, User_Storage $user_storage ) {
		$this->session      = $session;
		$this->user_storage = $user_storage;
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function generate() {
		$token = Hash::get_step_token();

		$this->session->set( self::STEP_TOKEN_KEY, $token );
		$this->user_storage->set_step_token( $token );
	}

	/**
	 * @return null|string
	 */
	public function get() {
		return $this->session->get( self::STEP_TOKEN_KEY );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function reset() {
		$this->session->delete( self::STEP_TOKEN_KEY );
		$this->user_storage->delete_step_token();
	}
}
