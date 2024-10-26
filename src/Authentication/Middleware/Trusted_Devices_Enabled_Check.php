<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\Trusted_Devices_Storage;
use WP_Error;
use WP_User;

class Trusted_Devices_Enabled_Check extends Middleware {

	/**
	 * @var Storage
	 */
	private $storage;

	/**
	 * @param Storage $storage
	 */
	public function __construct( Storage $storage ) {
		$this->storage = $storage;
	}

	/**
	 * @inheritDoc
	 */
	public function handle( $user, $response = null ) {
		$user_storage            = $this->storage->get_user_storage();
		$options_storage         = $this->storage->get_options();
		$trusted_devices_storage = $this->storage->get_trusted_devices_storage();

		if ( $this->is_wp_user( $user ) && $user_storage->is_totp_enabled() && $trusted_devices_storage->is_device_trusted( $user->ID ) ) {
			if ( ! $options_storage->are_trusted_devices_enabled() ) {
				$trusted_devices_storage->delete_trusted_devices( $user->ID );
			}
		}

		return $this->run_next( $user, $response );
	}
}
