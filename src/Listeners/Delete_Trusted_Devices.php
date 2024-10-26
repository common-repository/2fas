<?php

namespace TwoFAS\TwoFAS\Listeners;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\Exception as Api_Exception;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Events\Totp_Code_Accepted;
use TwoFAS\TwoFAS\Http\Session;
use TwoFAS\TwoFAS\Integration\Integration_User;
use TwoFAS\TwoFAS\Storage\Trusted_Devices_Storage;

class Delete_Trusted_Devices extends Listener {

	/**
	 * @var Integration_User
	 */
	private $integration_user;

	/**
	 * @var Trusted_Devices_Storage
	 */
	private $trusted_devices_storage;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param Integration_User        $integration_user
	 * @param Trusted_Devices_Storage $trusted_devices_storage
	 * @param Session                 $session
	 * @param Request                 $request
	 */
	public function __construct(
		Integration_User $integration_user,
		Trusted_Devices_Storage $trusted_devices_storage,
		Session $session,
		Request $request
	) {
		$this->integration_user        = $integration_user;
		$this->trusted_devices_storage = $trusted_devices_storage;
		$this->session                 = $session;
		$this->request                 = $request;
	}

	/**
	 * @param Totp_Code_Accepted $event
	 *
	 * @throws TokenNotFoundException
	 * @throws AuthorizationException
	 * @throws Api_Exception
	 */
	public function handle( Totp_Code_Accepted $event ) {
		$old_secret = $this->integration_user->get_totp_secret();

		if ( $old_secret !== $this->request->post( 'totp_secret' ) ) {
			$user_id = $this->integration_user->get_external_id();
			$this->session->log_out_on_other_devices( $user_id );
			$this->trusted_devices_storage->delete_trusted_devices( $user_id );
		}
	}
}
