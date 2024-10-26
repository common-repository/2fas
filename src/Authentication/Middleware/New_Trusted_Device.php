<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Login_Action;
use TwoFAS\TwoFAS\Hooks\Authenticate_Filter;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Storage\Trusted_Devices_Storage;
use WP_Error;
use WP_User;

/**
 * This class adds a new trusted device if a user checked a checkbox on a login page.
 */
final class New_Trusted_Device extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Trusted_Devices_Storage
	 */
	private $trusted_devices_storage;

	/**
	 * @param Request                 $request
	 * @param Trusted_Devices_Storage $trusted_devices_storage
	 */
	public function __construct( Request $request, Trusted_Devices_Storage $trusted_devices_storage ) {
		$this->request                 = $request;
		$this->trusted_devices_storage = $trusted_devices_storage;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null ) {
		$remember_device = $this->request->post( Authenticate_Filter::TWOFAS_REMEMBER_DEVICE_KEY );

		if ( ! $this->is_wp_user( $user )
			&& $response instanceof JSON_Response
			&& 200 === $response->get_status_code()
			&& ! empty( $remember_device )
			&& $this->request->is_login_action_equal_to( Login_Action::VERIFY_TOTP_CODE ) ) {
			$body    = $response->get_body();
			$user_id = $body['user_id'];

			$this->trusted_devices_storage->add_trusted_device( $user_id );
		}

		return $this->run_next( $user, $response );
	}
}
