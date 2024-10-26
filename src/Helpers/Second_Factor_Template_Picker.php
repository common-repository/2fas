<?php

namespace TwoFAS\TwoFAS\Helpers;

use TwoFAS\Api\IntegrationUser;
use TwoFAS\TwoFAS\Authentication\Login_Action;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Storage\User_Storage;
use TwoFAS\TwoFAS\Templates\Views;

class Second_Factor_Template_Picker {

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
	 * @param Request         $request
	 * @param IntegrationUser $integration_user
	 *
	 * @return null|string
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function get_template( Request $request, IntegrationUser $integration_user ) {
		if ( $this->can_display_totp_template( $request ) ) {
			return Views::TOTP_AUTHENTICATION_PAGE;
		}

		if ( $this->can_display_offline_codes_template( $request, $integration_user ) ) {
			return Views::BACKUP_AUTHENTICATION_PAGE;
		}

		if ( $this->can_display_sms_template( $request, $integration_user ) ) {
			return Views::SMS_AUTHENTICATION_PAGE;
		}

		if ( $this->can_display_call_template( $request, $integration_user ) ) {
			return Views::CALL_AUTHENTICATION_PAGE;
		}

		return null;
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function can_display_totp_template( Request $request ) {
		return $this->is_totp_action( $request ) && $this->user_storage->is_totp_enabled();
	}

	/**
	 * @param Request         $request
	 * @param IntegrationUser $integration_user
	 *
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function can_display_offline_codes_template( Request $request, IntegrationUser $integration_user ) {
		return $this->is_offline_codes_action( $request )
			&& $this->user_storage->are_offline_codes_enabled()
			&& $integration_user->getBackupCodesCount();
	}

	/**
	 * @param Request         $request
	 * @param IntegrationUser $integration_user
	 *
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function can_display_sms_template( Request $request, IntegrationUser $integration_user ) {
		return $this->is_sms_action( $request )
			&& $this->can_display_phone_template( $integration_user );
	}

	/**
	 * @param Request         $request
	 * @param IntegrationUser $integration_user
	 *
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function can_display_call_template( Request $request, IntegrationUser $integration_user ) {
		return $this->is_call_action( $request )
			&& $this->can_display_phone_template( $integration_user );
	}

	/**
	 * @param IntegrationUser $integration_user
	 *
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function can_display_phone_template( IntegrationUser $integration_user ) {
		$phone_number = $integration_user->getPhoneNumber()->phoneNumber();

		return $this->user_storage->is_sms_enabled()
			&& ! empty( $phone_number );
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function is_second_factor_action( Request $request ) {
		return $request->is_login_action_equal_to( null );
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function is_totp_action( Request $request ) {
		return $this->is_second_factor_action( $request )
			|| $request->is_login_action_equal_to( Login_Action::LOG_IN_WITH_TOTP_CODE )
			|| $request->is_login_action_equal_to( Login_Action::VERIFY_TOTP_CODE );
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function is_offline_codes_action( Request $request ) {
		return $request->is_login_action_equal_to( Login_Action::LOG_IN_WITH_BACKUP_CODE )
			|| $request->is_login_action_equal_to( Login_Action::VERIFY_BACKUP_CODE );
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function is_sms_action( Request $request ) {
		return $this->is_second_factor_action( $request )
			|| $request->is_login_action_equal_to( Login_Action::LOG_IN_WITH_SMS_CODE )
			|| $request->is_login_action_equal_to( Login_Action::VERIFY_SMS_CODE )
			|| $request->is_login_action_equal_to( Login_Action::OPEN_NEW_SMS_AUTHENTICATION );

	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function is_call_action( Request $request ) {
		return $request->is_login_action_equal_to( Login_Action::LOG_IN_WITH_CALL_CODE )
			|| $request->is_login_action_equal_to( Login_Action::VERIFY_CALL_CODE )
			|| $request->is_login_action_equal_to( Login_Action::OPEN_NEW_CALL_AUTHENTICATION );
	}
}
