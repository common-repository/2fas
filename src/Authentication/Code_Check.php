<?php

namespace TwoFAS\TwoFAS\Authentication;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Code\Code;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\TwoFAS\Exceptions\Offline_Codes_Disabled_Exception;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\Authentication_Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;

class Code_Check {

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Authentication_Storage
	 */
	private $authentication_storage;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @param API_Wrapper            $api_wrapper
	 * @param Authentication_Storage $authentication_storage
	 * @param User_Storage           $user_storage
	 */
	public function __construct( API_Wrapper $api_wrapper, Authentication_Storage $authentication_storage, User_Storage $user_storage ) {
		$this->api_wrapper            = $api_wrapper;
		$this->authentication_storage = $authentication_storage;
		$this->user_storage           = $user_storage;
	}

	/**
	 * @param Request         $request
	 * @param IntegrationUser $integration_user
	 * @param string          $code
	 *
	 * @return Code
	 *
	 * @throws TokenNotFoundException
	 * @throws AuthorizationException
	 * @throws ValidationException
	 * @throws Offline_Codes_Disabled_Exception
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	public function check( Request $request, IntegrationUser $integration_user, $code ) {
		$authentications = $this->authentication_storage->get_authentication_ids( $this->user_storage->get_user_id() );

		if ( $this->is_backup_code_request( $request ) ) {
			if ( $this->user_storage->are_offline_codes_enabled() && $integration_user->getBackupCodesCount() ) {
				return $this->api_wrapper->check_backup_code( $integration_user, $authentications, $code );
			}

			throw new Offline_Codes_Disabled_Exception();
		}

		return $this->api_wrapper->check_code( $authentications, $code );
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function is_backup_code_request( Request $request ) {
		return $request->is_login_action_equal_to( Login_Action::VERIFY_BACKUP_CODE );
	}
}
