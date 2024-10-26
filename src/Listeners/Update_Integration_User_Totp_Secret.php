<?php

namespace TwoFAS\TwoFAS\Listeners;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\Exception as Api_Exception;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Events\Totp_Configuration_Code_Accepted;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Integration\Integration_User;

class Update_Integration_User_Totp_Secret extends Listener {

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Integration_User
	 */
	private $integration_user;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param API_Wrapper      $api_wrapper
	 * @param Integration_User $integration_user
	 * @param Request          $request
	 */
	public function __construct( API_Wrapper $api_wrapper, Integration_User $integration_user, Request $request ) {
		$this->api_wrapper      = $api_wrapper;
		$this->integration_user = $integration_user;
		$this->request          = $request;
	}

	/**
	 * @param Totp_Configuration_Code_Accepted $event
	 *
	 * @throws TokenNotFoundException
	 * @throws AuthorizationException
	 * @throws ValidationException
	 * @throws Api_Exception
	 */
	public function handle( Totp_Configuration_Code_Accepted $event ) {
		$this->api_wrapper->update_integration_user(
			$this->integration_user->set_totp_secret( $this->request->post( 'totp_secret' ) )->get_user()
		);
	}
}
