<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\TwoFAS\Storage\Authentication_Storage;

class Delete_Authentications_Action implements Hook_Interface {

	/**
	 * @var Authentication_Storage
	 */
	private $authentication_storage;

	/**
	 * @param Authentication_Storage $authentication_storage
	 */
	public function __construct( Authentication_Storage $authentication_storage ) {
		$this->authentication_storage = $authentication_storage;
	}

	public function register_hook() {
		add_action( 'deleted_user', [ $this, 'delete_authentications' ] );
	}

	/**
	 * @param int $user_id
	 */
	public function delete_authentications( $user_id ) {
		$this->authentication_storage->close_authentication( $user_id );
	}
}
