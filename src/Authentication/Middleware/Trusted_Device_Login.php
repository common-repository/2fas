<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\Trusted_Devices_Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;
use WP_Error;
use WP_User;

/**
 * This class handles logging in on a trusted device.
 */
final class Trusted_Device_Login extends Middleware {

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Trusted_Devices_Storage
	 */
	private $trusted_devices_storage;

	/**
	 * @param Storage $storage
	 */
	public function __construct( Storage $storage ) {
		$this->user_storage            = $storage->get_user_storage();
		$this->trusted_devices_storage = $storage->get_trusted_devices_storage();
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null ) {
		if ( $this->is_wp_user( $user )
			&& $this->user_storage->is_totp_enabled()
			&& $this->trusted_devices_storage->is_device_trusted( $user->ID )
		) {
			$this->trusted_devices_storage->set_trusted_device_login_time( $user->ID );

			return $this->json( [ 'user_id' => $user->ID ], 200 );
		}

		return $this->run_next( $user, $response );
	}
}
