<?php

namespace TwoFAS\TwoFAS\Http\Controllers\User;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\InvalidDateException;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\TotpSecretGenerator;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Codes\QR_Code_Message;
use TwoFAS\TwoFAS\Core\Legacy_Mode_Checker;
use TwoFAS\TwoFAS\Events\Totp_Configuration_Code_Accepted;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Factories\QR_Code_Generator_Factory;
use TwoFAS\Core\Helpers\Dispatcher;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Integration\Integration_User;
use TwoFAS\TwoFAS\Notifications\Notification;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Templates\Views;
use TwoFAS\ValidationRules\ValidationRules;

class TOTP_Configuration_Controller extends Configuration_Controller {

	/**
	 * @var QR_Code_Message
	 */
	private $qr_code_message;

	/**
	 * @param Storage             $storage
	 * @param API_Wrapper         $api_wrapper
	 * @param Integration_User    $integration_user
	 * @param Flash               $flash
	 * @param QR_Code_Message     $qr_code_message
	 * @param Legacy_Mode_Checker $legacy_mode_checker
	 */
	public function __construct(
		Storage $storage,
		API_Wrapper $api_wrapper,
		Integration_User $integration_user,
		Flash $flash,
		QR_Code_Message $qr_code_message,
		Legacy_Mode_Checker $legacy_mode_checker
	) {
		parent::__construct( $storage, $api_wrapper, $integration_user, $flash, $legacy_mode_checker );

		$this->qr_code_message = $qr_code_message;
	}

	/**
	 * @param Request $request
	 *
	 * @return View_Response|Redirect_Response
	 *
	 * @throws AuthorizationException
	 * @throws InvalidDateException
	 * @throws Account_Exception
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	public function configure( Request $request ) {
		if ( ! $request->is_post() ) {
			return $this->show_configuration_page();
		}

		$code        = $request->post( 'code' );
		$totp_secret = $request->post( 'totp_secret' );

		try {
			if ( empty( $totp_secret ) ) {
				throw new ValidationException( [ 'totp_secret' => [ ValidationRules::REQUIRED ] ] );
			}

			if ( empty( $code ) ) {
				throw new ValidationException( [ 'code' => [ ValidationRules::REQUIRED ] ] );
			}

			$authentication = $this->api_wrapper->request_auth_via_totp( $totp_secret );
			$result         = $this->api_wrapper->check_code( [ $authentication->id() ], $code );
		} catch ( ValidationException $e ) {
			$this->flash->add_message_now( 'error', $this->get_validation_error( $e ) );

			return $this->show_configuration_page( $totp_secret, true );
		}

		if ( $result->accepted() ) {
			Dispatcher::dispatch( new Totp_Configuration_Code_Accepted( $result ) );

			$this->flash->add_message( 'success', 'totp-configured' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_TOTP ) );
		}

		$this->flash->add_message_now( 'error', 'token-invalid' );

		return $this->show_configuration_page( $totp_secret, true );
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

		if ( $user_storage->is_totp_configured() ) {
			$user_storage->enable_totp();
			$user_storage->enable_2fa();
			$this->flash->add_message( 'success', 'totp-enabled' );
		} else {
			$this->flash->add_message( 'error', 'cannot-enable-totp' );
		}

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 */
	public function disable( Request $request ) {
		$trusted_devices_storage = $this->storage->get_trusted_devices_storage();
		$options_storage         = $this->storage->get_options();
		$user_storage            = $this->storage->get_user_storage();

		try {
			if ( $options_storage->has_twofas_role( $user_storage->get_roles() ) ) {
				$this->flash->add_message( 'error', '2fa-role' );

				return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
			}

			$user_storage->disable_totp();
			$user_storage->disable_2fa();
			$trusted_devices_storage->delete_trusted_devices( $user_storage->get_user_id() );

			$this->flash->add_message( 'success', 'totp-disabled' );
		} catch ( User_Not_Found_Exception $e ) {
			$this->flash->add_message( 'error', 'default' );
		}

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
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
			$trusted_devices_storage = $this->storage->get_trusted_devices_storage();
			$user_storage            = $this->storage->get_user_storage();

			if ( $this->legacy_mode_checker->totp_is_obligatory_and_legacy_mode_is_not_active() ) {
				$this->flash->add_message( 'error', '2fa-role-remove' );

				return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
			}

			$user = $this->get_integration_user();
			$this->api_wrapper->update_integration_user( $user->setTotpSecret( null ) );
			$user_storage->remove_totp();

			if ( $this->legacy_mode_checker->is_2fa_enabled_in_legacy_mode() ) {
				$this->flash->add_message( 'success', 'configuration-removed' );

				return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
			}

			$user_storage->disable_2fa();
			$trusted_devices_storage->delete_trusted_devices( $user_storage->get_user_id() );

			$this->flash->add_message( 'success', 'configuration-removed' );

		} catch ( API_Exception $e ) {
			$this->flash->add_message( 'error', 'configuration-remove-error' );
		} catch ( User_Not_Found_Exception $e ) {
			$this->flash->add_message( 'error', 'user-not-found' );
		}

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 */
	public function reload( Request $request ) {
		try {
			$user_storage = $this->storage->get_user_storage();

			if ( $user_storage->is_totp_configured() ) {
				return $this->json( [ 'error' => 'QR code cannot be reloaded if TOTP is configured.' ], 403 );
			}

			$totp_secret               = TotpSecretGenerator::generate();
			$message                   = $this->get_qr_code_message( $totp_secret );
			$response                  = [];
			$response['qrCode']        = $this->get_qr_code( $message );
			$response['qrCodeMessage'] = $message;
			$response['totpSecret']    = $totp_secret;
		} catch ( User_Not_Found_Exception $e ) {
			return $this->json( [ 'error' => 'User has not been found.' ], 404 );
		}

		return $this->json( $response, 200 );
	}

	/**
	 * @param string|null $totp_secret
	 * @param bool        $validation_error
	 *
	 * @return View_Response
	 *
	 * @throws Account_Exception
	 * @throws API_Exception
	 */
	private function show_configuration_page( $totp_secret = null, $validation_error = false ) {
		try {
			$client = $this->api_wrapper->get_client();
			$user   = $this->get_integration_user();

			if ( empty( $totp_secret ) ) {
				$totp_secret = $this->get_totp_secret( $user );
			}

			$qr_code_message = $this->get_qr_code_message( $totp_secret );
			$qr_code         = $this->get_qr_code( $qr_code_message );
			$status_data     = $this->get_user_status_data();

			$data = [
				'has_client_card'     => $client->hasCard(),
				'qr_code_message'     => $qr_code_message,
				'qr_code'             => $qr_code,
				'totp_secret'         => $totp_secret,
				'offline_codes_count' => $user->getBackupCodesCount(),
				'active_tab'          => 'tokens',
				'validation_error'    => $validation_error,
			];

			return $this->view( Views::CONFIGURE_TOTP, array_merge( $data, $status_data ) );
		} catch ( User_Not_Found_Exception $e ) {
			return $this->error( Notification::get( 'user-not-found' ) );
		}
	}

	/**
	 * @return IntegrationUser|null
	 *
	 * @throws TokenNotFoundException
	 * @throws ValidationException
	 * @throws AuthorizationException
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function get_integration_user() {
		if ( ! $this->integration_user->exists() ) {
			$user_id          = $this->storage->get_user_storage()->get_user_id();
			$integration_user = $this->api_wrapper->create_integration_user( $user_id );
			$this->integration_user->set_user( $integration_user );
		}

		return $this->integration_user->get_user();
	}

	/**
	 * @param IntegrationUser $user
	 *
	 * @return string
	 */
	private function get_totp_secret( IntegrationUser $user ) {
		$totp_secret = $user->getTotpSecret();

		if ( empty( $totp_secret ) ) {
			$totp_secret = TotpSecretGenerator::generate();
		}

		return $totp_secret;
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function get_qr_code( $message ) {
		$generator = QR_Code_Generator_Factory::create();

		return $generator->generateBase64( $message );
	}

	/**
	 * @param string $totp_secret
	 *
	 * @return string
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function get_qr_code_message( $totp_secret ) {
		return $this->qr_code_message->create( $totp_secret );
	}
}
