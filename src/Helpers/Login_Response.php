<?php

namespace TwoFAS\TwoFAS\Helpers;

use TwoFAS\Api\IntegrationUser;
use TwoFAS\TwoFAS\Authentication\Login_Action;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Storage\Storage;

class Login_Response {

	const WP_LOGIN_REDIRECT_TO         = 'redirect_to';
	const WP_LOGIN_REMEMBER_ME         = 'rememberme';
	const WP_LOGIN_TEST_COOKIE         = 'testcookie';
	const WP_LOGIN_INTERIM_LOGIN       = 'interim-login';
	const WP_LOGIN_CUSTOMIZE_LOGIN     = 'customize-login';
	const TWOFAS_LOGIN_REMEMBER_DEVICE = 'twofas_remember_device';

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * @param Request $request
	 */
	public function set_from_request( Request $request ) {
		$redirect_to = $request->request( self::WP_LOGIN_REDIRECT_TO );
		$remember_me = $request->post( self::WP_LOGIN_REMEMBER_ME );

		if ( is_null( $remember_me ) ) {
			$remember_me = $request->get( self::WP_LOGIN_REMEMBER_ME );
		}

		$test_cookie     = $request->post( self::WP_LOGIN_TEST_COOKIE );
		$interim_login   = $request->request( self::WP_LOGIN_INTERIM_LOGIN );
		$customize_login = $request->request( self::WP_LOGIN_CUSTOMIZE_LOGIN );
		$remember_device = $request->post( self::TWOFAS_LOGIN_REMEMBER_DEVICE );

		if ( ! is_null( $redirect_to ) ) {
			$this->set( 'redirect_to', $redirect_to );
		}

		if ( ! is_null( $remember_me ) ) {
			$this->set( 'remember_me', $remember_me );
		}

		if ( ! is_null( $test_cookie ) ) {
			$this->set( 'test_cookie', $test_cookie );
		}

		if ( ! is_null( $interim_login ) ) {
			$this->set( 'interim_login', $interim_login );
		}

		if ( ! is_null( $customize_login ) ) {
			$this->set( 'customize_login', $customize_login );
		}

		if ( ! is_null( $remember_device ) ) {
			$this->set( 'remember_device', $remember_device );
		}

		if ( $this->is_open_new_auth_request( $request ) ) {
			$this->set( 'open_new_auth_action', true );
		}
	}

	/**
	 * @param IntegrationUser $integration_user
	 */
	public function set_from_integration_user( IntegrationUser $integration_user ) {
		$phone_number = $integration_user->getPhoneNumber()->phoneNumber();

		$this->set( 'offline_codes_count', $integration_user->getBackupCodesCount() );

		if ( ! is_null( $phone_number ) ) {
			$this->set( 'phone_number_ending', substr( $phone_number, - 3 ) );
		}
	}

	/**
	 * @param Storage $storage
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function set_from_storage( Storage $storage ) {
		$user_storage    = $storage->get_user_storage();
		$options_storage = $storage->get_options();

		$this->set( 'is_sms_enabled', $user_storage->is_sms_enabled() );
		$this->set( 'are_offline_codes_enabled', $user_storage->are_offline_codes_enabled() );
		$this->set( 'is_totp_enabled', $user_storage->is_totp_enabled() );
		$this->set( 'trusted_devices_enabled', $options_storage->are_trusted_devices_enabled() );
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get( $key ) {
		if ( ! array_key_exists( $key, $this->data ) ) {
			return null;
		}

		return $this->data[ $key ];
	}

	/**
	 * @return array
	 */
	public function get_all() {
		return $this->data;
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function is_open_new_auth_request( Request $request ) {
		return $request->is_login_action_equal_to( Login_Action::OPEN_NEW_SMS_AUTHENTICATION )
			|| $request->is_login_action_equal_to( Login_Action::OPEN_NEW_CALL_AUTHENTICATION );
	}
}
