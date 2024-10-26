<?php

namespace TwoFAS\TwoFAS\Authentication;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\TwoFAS\Exceptions\Authentication_Expired_Exception;
use TwoFAS\TwoFAS\Exceptions\Authentication_Limit_Reached_Exception;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\Authentication_Storage;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;

class Authenticator {

	const OPEN_AUTHENTICATIONS_LIMIT = 6;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Authentication_Storage
	 */
	private $authentication_storage;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param API_Wrapper $api_wrapper
	 * @param Storage     $storage
	 * @param Request     $request
	 */
	public function __construct( API_Wrapper $api_wrapper, Storage $storage, Request $request ) {
		$this->api_wrapper            = $api_wrapper;
		$this->user_storage           = $storage->get_user_storage();
		$this->authentication_storage = $storage->get_authentication_storage();
		$this->request                = $request;
	}

	/**
	 * @param IntegrationUser $integration_user
	 * @param string          $manual_transition
	 *
	 * @throws TokenNotFoundException
	 * @throws Authentication_Expired_Exception
	 * @throws Authentication_Limit_Reached_Exception
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	public function open_authentication( IntegrationUser $integration_user, $manual_transition ) {
		if ( $this->can_open_totp_authentication( $manual_transition ) ) {
			$this->open_totp_authentication( $integration_user );
		} elseif ( $this->can_open_sms_authentication( $manual_transition ) ) {
			$this->open_sms_authentication( $integration_user );
		} elseif ( $this->can_open_call_authentication( $manual_transition ) ) {
			$this->open_call_authentication( $integration_user );
		}
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function close_authentication() {
		$this->authentication_storage->close_authentication( $this->user_storage->get_user_id() );
	}

	/**
	 * @param bool $manual_transition
	 *
	 * @return bool
	 *
	 * @throws Authentication_Expired_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function can_open_totp_authentication( $manual_transition ) {
		return $this->user_storage->is_totp_enabled() && $this->can_open_authentication( $manual_transition );
	}

	/**
	 * @param bool $manual_transition
	 *
	 * @return bool
	 *
	 * @throws Authentication_Expired_Exception
	 * @throws Authentication_Limit_Reached_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function can_open_sms_authentication( $manual_transition ) {
		return $this->can_open_phone_authentication( $manual_transition, $this->is_sms_action_sent() );
	}

	/**
	 * @param bool $manual_transition
	 *
	 * @return bool
	 *
	 * @throws Authentication_Expired_Exception
	 * @throws Authentication_Limit_Reached_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function can_open_call_authentication( $manual_transition ) {
		return $this->can_open_phone_authentication( $manual_transition, $this->is_call_action_sent() );
	}

	/**
	 * @param bool $manual_transition
	 * @param bool $is_action_sent
	 *
	 * @return bool
	 *
	 * @throws Authentication_Expired_Exception
	 * @throws Authentication_Limit_Reached_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function can_open_phone_authentication( $manual_transition, $is_action_sent ) {
		return $this->user_storage->is_sms_enabled() && $this->can_open_authentication( $manual_transition, $is_action_sent );
	}

	/**
	 * @param bool $manual_transition
	 * @param bool $reopen
	 *
	 * @return bool
	 *
	 * @throws Authentication_Expired_Exception
	 * @throws Authentication_Limit_Reached_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function can_open_authentication( $manual_transition, $reopen = false ) {
		if ( $this->authentication_storage->is_authentication_expired( $this->user_storage->get_user_id() ) ) {
			if ( ! $manual_transition ) {
				throw new Authentication_Expired_Exception();
			}

			$this->close_authentication();

			return true;
		}

		if ( $manual_transition || $reopen ) {
			if ( $this->is_authentications_limit_reached() ) {
				throw new Authentication_Limit_Reached_Exception( 'Open authentication limit is ' . self::OPEN_AUTHENTICATIONS_LIMIT );
			}

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function is_authentications_limit_reached() {
		$authentications = $this->authentication_storage->get_authentication_ids( $this->user_storage->get_user_id() );

		return count( $authentications ) >= self::OPEN_AUTHENTICATIONS_LIMIT;
	}

	/**
	 * @return bool
	 */
	private function is_sms_action_sent() {
		return $this->request->is_login_action_equal_to( Login_Action::OPEN_NEW_SMS_AUTHENTICATION );
	}

	/**
	 * @return bool
	 */
	private function is_call_action_sent() {
		return $this->request->is_login_action_equal_to( Login_Action::OPEN_NEW_CALL_AUTHENTICATION );
	}

	/**
	 * @param IntegrationUser $integration_user
	 *
	 * @throws API_Exception
	 *
	 * @throws TokenNotFoundException
	 * @throws User_Not_Found_Exception
	 */
	private function open_totp_authentication( IntegrationUser $integration_user ) {
		$authentication = $this->api_wrapper->request_auth_via_totp( $integration_user->getTotpSecret() );

		$this->authentication_storage->open_authentication( $this->user_storage->get_user_id(), $authentication );
	}

	/**
	 * @param IntegrationUser $integration_user
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function open_sms_authentication( IntegrationUser $integration_user ) {
		$authentication = $this->api_wrapper->request_auth_via_sms( $integration_user->getPhoneNumber()->phoneNumber() );
		$this->authentication_storage->open_authentication( $this->user_storage->get_user_id(), $authentication );
	}

	/**
	 * @param IntegrationUser $integration_user
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function open_call_authentication( IntegrationUser $integration_user ) {
		$authentication = $this->api_wrapper->request_auth_via_call( $integration_user->getPhoneNumber()->phoneNumber() );
		$this->authentication_storage->open_authentication( $this->user_storage->get_user_id(), $authentication );
	}
}
