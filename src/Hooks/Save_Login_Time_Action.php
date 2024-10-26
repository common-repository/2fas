<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Storage\User_Storage;

class Save_Login_Time_Action implements Hook_Interface {

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @param User_Storage $user_storage
	 */
	public function __construct( User_Storage $user_storage ) {
		$this->user_storage = $user_storage;
	}

	public function register_hook() {
		add_action( 'wp_login', [ $this, 'save' ] );
	}

	public function save() {
		try {
			$this->user_storage->set_last_login_time( time() );
		} catch ( User_Not_Found_Exception $e ) {
		}
	}
}
