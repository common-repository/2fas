<?php

namespace TwoFAS\TwoFAS\Listeners;

use TwoFAS\TwoFAS\Events\Totp_Code_Accepted;
use TwoFAS\TwoFAS\Storage\User_Storage;

class Enable_Totp extends Listener {

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

	/**
	 * @param Totp_Code_Accepted $event
	 */
	public function handle( Totp_Code_Accepted $event ) {
		$this->user_storage->enable_totp();
		$this->user_storage->enable_2fa();
	}
}
