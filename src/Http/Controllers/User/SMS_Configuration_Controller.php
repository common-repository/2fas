<?php

namespace TwoFAS\TwoFAS\Http\Controllers\User;

use DateTime;
use Exception;
use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Authentication;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\AuthorizationException as API_Authorization_Exception;
use TwoFAS\Api\Exception\CountryIsBlockedException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\InvalidDateException;
use TwoFAS\Api\Exception\InvalidNumberException;
use TwoFAS\Api\Exception\PaymentException;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\Exception\ValidationException as API_Validation_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Templates\Views;
use TwoFAS\ValidationRules\ValidationRules;

class SMS_Configuration_Controller extends Configuration_Controller {

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 *
	 * @throws TokenNotFoundException
	 * @throws CountryIsBlockedException
	 * @throws InvalidDateException
	 * @throws PaymentException
	 * @throws API_Authorization_Exception
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	public function request_auth_via_sms( Request $request ) {
		$response     = [];
		$phone_number = $request->post( 'phone_number' );

		if ( ! empty( $phone_number ) ) {
			try {
				$authentication               = $this->api_wrapper->request_auth_via_sms( $phone_number );
				$status_code                  = 200;
				$response['authenticationId'] = $authentication->id();
			} catch ( InvalidNumberException $e ) {
				$status_code       = 400;
				$response['error'] = 'Phone number is invalid.';
			}
		} else {
			$status_code       = 400;
			$response['error'] = 'Phone number is empty.';
		}

		return $this->json( $response, $status_code );
	}

	/**
	 * @param Request $request
	 *
	 * @return View_Response|Redirect_Response
	 *
	 * @throws Exception
	 * @throws AuthorizationException
	 * @throws Account_Exception
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	public function configure( Request $request ) {
		if ( ! $request->is_post() ) {
			return $this->show_configuration_page( $request );
		}

		$authentication_id = $request->get_twofas_param( 'authentication_id' );
		$code              = $request->get_twofas_param( 'token' );
		$phone_number      = $request->get_twofas_param( 'phone_number' );

		if ( empty( $authentication_id ) || empty( $phone_number ) ) {
			$this->flash->add_message_now( 'error', 'authentication-required' );

			return $this->show_configuration_page( $request, $authentication_id, $phone_number );
		}

		$date_time      = new DateTime();
		$authentication = new Authentication( $authentication_id, $date_time, $date_time );

		try {
			if ( empty( $code ) ) {
				throw new ValidationException( [ 'code' => [ ValidationRules::REQUIRED ] ] );
			}

			$code_state = $this->api_wrapper->check_code( [ $authentication->id() ], $code );

			if ( $code_state->accepted() ) {
				$this->api_wrapper->update_integration_user( $this->integration_user->set_phone_number( $phone_number )->get_user() );
				$user_storage = $this->storage->get_user_storage();
				$user_storage->enable_sms();
				$this->flash->add_message( 'success', 'sms-configured' );
			} elseif ( $code_state->canRetry() ) {
				$this->flash->add_message_now( 'error', 'code-invalid' );

				return $this->show_configuration_page( $request, $authentication_id, $phone_number );
			} else {
				$this->flash->add_message( 'error', 'code-invalid-cannot-retry' );
			}

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_SMS ) );
		} catch ( ValidationException $e ) {
			$this->flash->add_message_now( 'error', $this->get_validation_error( $e ) );

			return $this->show_configuration_page( $request, $authentication_id, $phone_number );
		}
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function enable( Request $request ) {
		$user_storage = $this->storage->get_user_storage();
		$user_storage->enable_sms();
		$this->flash->add_message( 'success', 'sms-enabled' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_SMS ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function disable( Request $request ) {
		$user_storage    = $this->storage->get_user_storage();
		$options_storage = $this->storage->get_options();

		if ( ! $this->legacy_mode_checker->is_2fa_enabled_in_legacy_mode() ) {
			$user_storage->disable_sms();
			$this->flash->add_message( 'success', 'sms-disabled' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_SMS ) );
		}

		try {
			$roles = $user_storage->get_roles();
		} catch ( User_Not_Found_Exception $e ) {
			$this->flash->add_message( 'error', 'user-not-found' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_SMS ) );
		}

		if ( $options_storage->has_twofas_role( $roles ) ) {
			$this->flash->add_message( 'error', '2fa-role' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_SMS ) );
		}

		$user_storage->disable_sms();
		$user_storage->disable_2fa();
		$this->flash->add_message( 'success', 'legacy-mode-sms-disabled' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_TOTP ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 *
	 * @throws TokenNotFoundException
	 */
	public function remove_configuration( Request $request ) {
		try {
			$user_storage = $this->storage->get_user_storage();

			if ( $this->legacy_mode_checker->totp_is_obligatory_and_legacy_mode_is_active() ) {
				$this->flash->add_message( 'error', '2fa-role-remove' );

				return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_SMS ) );
			}

			$this->api_wrapper->update_integration_user( $this->integration_user->set_phone_number( null )->get_user() );
			$user_storage->remove_sms();

			if ( $this->legacy_mode_checker->is_2fa_enabled_in_legacy_mode() ) {
				$user_storage->disable_2fa();
			}

			$this->flash->add_message( 'success', 'configuration-removed' );
		} catch ( API_Exception $e ) {
			$this->flash->add_message( 'error', 'configuration-remove-error' );
		} catch ( User_Not_Found_Exception $e ) {
			$this->flash->add_message( 'error', 'user-not-found' );
		}

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_SMS ) );
	}

	/**
	 * @param Request     $request
	 * @param string|null $authentication_id
	 * @param string|null $phone_number
	 *
	 * @return View_Response|Redirect_Response
	 *
	 * @throws Account_Exception
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function show_configuration_page( Request $request, $authentication_id = null, $phone_number = null ) {
		$client = $this->api_wrapper->get_client();

		if ( ! $client->hasCard() ) {
			return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
		}

		$data                               = [];
		$data['authentication_id']          = $authentication_id;
		$data['configuration_phone_number'] = $phone_number;
		$data['has_client_card']            = $client->hasCard();
		$data['offline_codes_count']        = $this->integration_user->get_backup_codes_count();
		$data['active_tab']                 = 'sms';
		$data['phone_number']               = $this->integration_user->get_phone_number()->phoneNumber();
		$status_data                        = $this->get_user_status_data();
		$data                               = array_merge( $data, $status_data );

		return $this->view( Views::CONFIGURE_SMS, $data );
	}
}
