<?php

namespace TwoFAS\TwoFAS\Authentication\Handler;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\QrCodeGenerator;
use TwoFAS\Api\TotpSecretGenerator;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Login_Action;
use TwoFAS\TwoFAS\Codes\QR_Code_Message;
use TwoFAS\TwoFAS\Core\Legacy_Mode_Checker;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Hooks\Authenticate_Filter;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Http\Session;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Integration\Integration_User;
use TwoFAS\TwoFAS\Storage\Authentication_Storage;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\Trusted_Devices_Storage;
use WP_Error;
use WP_User;

/**
 * This class is responsible for resetting TOTP configuration.
 */
final class Configuration_Reset extends Login_Handler {

	/**
	 * @var Authentication_Storage
	 */
	private $authentication_storage;

	/**
	 * @var Trusted_Devices_Storage
	 */
	private $trusted_devices_storage;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var QR_Code_Message
	 */
	private $qr_code_message;

	/**
	 * @var QrCodeGenerator
	 */
	private $qr_code_generator;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @var Legacy_Mode_Checker
	 */
	private $legacy_mode_checker;

	/**
	 * @var Integration_User
	 */
	private $integration_user;

	/**
	 * @param Storage             $storage
	 * @param API_Wrapper         $api_wrapper
	 * @param QR_Code_Message     $qr_code_message
	 * @param QrCodeGenerator     $qr_code_generator
	 * @param Request             $request
	 * @param Session             $session
	 * @param Legacy_Mode_Checker $legacy_mode_checker
	 * @param Integration_User    $integration_user
	 */
	public function __construct(
		Storage $storage,
		API_Wrapper $api_wrapper,
		QR_Code_Message $qr_code_message,
		QrCodeGenerator $qr_code_generator,
		Request $request,
		Session $session,
		Legacy_Mode_Checker $legacy_mode_checker,
		Integration_User $integration_user
	) {
		parent::__construct( $storage );
		$this->authentication_storage  = $storage->get_authentication_storage();
		$this->trusted_devices_storage = $storage->get_trusted_devices_storage();
		$this->api_wrapper             = $api_wrapper;
		$this->qr_code_message         = $qr_code_message;
		$this->qr_code_generator       = $qr_code_generator;
		$this->request                 = $request;
		$this->session                 = $session;
		$this->legacy_mode_checker     = $legacy_mode_checker;
		$this->integration_user        = $integration_user;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	public function supports( $user ) {
		if ( ! $this->is_wp_user_set() ) {
			return false;
		}

		if ( ! $this->legacy_mode_checker->totp_is_obligatory_or_legacy_mode_is_active()
			&& ! $this->session->exists( Authenticate_Filter::LOGIN_CONFIGURATION_IN_PROGRESS_KEY ) ) {
			return false;
		}

		if ( ! $this->is_wp_user( $user ) && $this->request->is_login_action_equal_to( Login_Action::TOTP_RESET ) ) {
			return ! $this->user_storage->is_totp_enabled()
				&& $this->user_storage->is_totp_configured();
		}

		return ! $this->user_storage->is_totp_configured();
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool|JSON_Response|Redirect_Response|View_Response
	 *
	 * @throws TokenNotFoundException
	 * @throws User_Not_Found_Exception
	 * @throws API_EXception
	 */
	protected function handle( $user ) {
		$totp_secret = TotpSecretGenerator::generate();

		$user_id = $this->get_user_id();

		if ( ! $this->integration_user->exists() ) {
			$integration_user = $this->api_wrapper->create_integration_user( $user_id );
			$this->integration_user->set_user( $integration_user );
		} else {
			$this->integration_user->set_totp_secret( null );
			$this->api_wrapper->update_integration_user( $this->integration_user->get_user() );
		}

		$this->user_storage->delete_totp_configuration();
		$this->trusted_devices_storage->delete_trusted_devices( $user_id );
		$this->session->set( Authenticate_Filter::LOGIN_CONFIGURATION_IN_PROGRESS_KEY, '1' );
		$message = $this->qr_code_message->create( $totp_secret );
		$qr_code = $this->qr_code_generator->generateBase64( $message );

		$this->authentication_storage->close_authentication( $this->get_user_id() );
		$authentication = $this->api_wrapper->request_auth_via_totp( $totp_secret );
		$this->authentication_storage->open_authentication( $this->get_user_id(), $authentication );

		return $this->view( 'login/configuration.html.twig', [
			'qr_code'         => $qr_code,
			'qr_code_message' => $message,
			'totp_secret'     => $totp_secret,
		] );
	}
}
