<?php

namespace TwoFAS\TwoFAS\Authentication\Handler;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\ValidationException as API_Validation_Exception;
use TwoFAS\Api\QrCodeGenerator;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Login_Action;
use TwoFAS\TwoFAS\Core\Legacy_Mode_Checker;
use TwoFAS\TwoFAS\Events\Totp_Configuration_Code_Accepted;
use TwoFAS\TwoFAS\Exceptions\Authentication_Expired_Exception;
use TwoFAS\TwoFAS\Exceptions\Authentication_Limit_Reached_Exception;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\Core\Helpers\Dispatcher;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Hooks\Authenticate_Filter;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Http\Session;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Integration\Integration_User;
use TwoFAS\TwoFAS\Notifications\Notification;
use TwoFAS\TwoFAS\Storage\Authentication_Storage;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\Trusted_Devices_Storage;
use WP_Error;
use WP_User;

/**
 * This class handles TOTP configuration request during login process.
 */
final class Login_Configuration extends Login_Handler {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var QrCodeGenerator
	 */
	private $qr_code_generator;

	/**
	 * @var Authentication_Storage
	 */
	private $authentication_storage;

	/**
	 * @var Trusted_Devices_Storage
	 */
	private $trusted_devices_storage;

	/**
	 * @var Legacy_Mode_Checker
	 */
	private $legacy_mode_checker;

	/**
	 * @var Integration_User
	 */
	private $integration_user;

	/**
	 * @param Request             $request
	 * @param Session             $session
	 * @param Flash               $flash
	 * @param API_Wrapper         $api_wrapper
	 * @param Storage             $storage
	 * @param QrCodeGenerator     $qr_code_generator ,
	 * @param Legacy_Mode_Checker $legacy_mode_checker
	 * @param Integration_User    $integration_user
	 */
	public function __construct(
		Request $request,
		Session $session,
		Flash $flash,
		API_Wrapper $api_wrapper,
		Storage $storage,
		QrCodeGenerator $qr_code_generator,
		Legacy_Mode_Checker $legacy_mode_checker,
		Integration_User $integration_user
	) {
		parent::__construct( $storage );
		$this->request                 = $request;
		$this->session                 = $session;
		$this->flash                   = $flash;
		$this->api_wrapper             = $api_wrapper;
		$this->qr_code_generator       = $qr_code_generator;
		$this->authentication_storage  = $storage->get_authentication_storage();
		$this->trusted_devices_storage = $storage->get_trusted_devices_storage();
		$this->legacy_mode_checker     = $legacy_mode_checker;
		$this->integration_user        = $integration_user;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	public function supports( $user ) {
		if ( $this->is_wp_user( $user ) ) {
			return false;
		}

		if ( ! $this->is_wp_user_set() ) {
			return false;
		}

		return ( $this->legacy_mode_checker->totp_is_obligatory_or_legacy_mode_is_active()
				|| $this->session->exists( Authenticate_Filter::LOGIN_CONFIGURATION_IN_PROGRESS_KEY ) )
			&& $this->request->is_login_action_equal_to( Login_Action::CONFIGURE );
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool|JSON_Response|Redirect_Response|View_Response
	 *
	 * @throws TokenNotFoundException
	 * @throws User_Not_Found_Exception
	 * @throws API_Exception
	 */
	protected function handle( $user ) {
		$code   = $this->request->post( Authenticate_Filter::TWOFAS_CODE_KEY );
		$secret = $this->request->post( Authenticate_Filter::SECRET_FIELD );

		if ( empty( $code ) ) {
			return $this->view_error( 'login/configuration.html.twig', Notification::get( 'code-required' ), $this->get_error_data() );
		}

		if ( empty( $secret ) ) {
			return $this->view_error( 'login/configuration.html.twig', Notification::get( 'empty-private-key' ), $this->get_error_data() );
		}

		try {
			$user_id = $this->get_user_id();

			if ( ! $this->integration_user->exists() ) {
				return $this->json_error( Notification::get( 'integration-user-not-found' ), 404 );
			}

			if ( $this->authentication_storage->is_authentication_expired( $this->get_user_id() ) ) {
				throw new Authentication_Expired_Exception();
			}

			$result = $this->api_wrapper->check_code( $this->authentication_storage->get_authentication_ids( $this->get_user_id() ), $code );

			if ( $result->accepted() ) {
				Dispatcher::dispatch( new Totp_Configuration_Code_Accepted( $result ) );
				$this->session->delete( Authenticate_Filter::LOGIN_CONFIGURATION_IN_PROGRESS_KEY );
				$this->flash->add_message( 'success', 'totp-enabled' );

				return $this->json( [ 'user_id' => $user_id ], 200 );
			}

			if ( ! $result->canRetry() ) {
				throw new Authentication_Limit_Reached_Exception();
			}

			return $this->view_error( 'login/configuration.html.twig', Notification::get( 'code-invalid' ), $this->get_error_data() );

		} catch ( API_Validation_Exception $e ) {
			return $this->view_error( 'login/configuration.html.twig', Notification::get( 'code-validation' ), $this->get_error_data() );

		} catch ( Authentication_Expired_Exception $e ) {
			$this->authentication_storage->close_authentication( $this->get_user_id() );

			return $this->json_error( Notification::get( 'authentication-expired' ), 403 );
		} catch ( Authentication_Limit_Reached_Exception $e ) {
			$this->user_storage->block_user();
			$this->authentication_storage->close_authentication( $this->get_user_id() );

			return $this->json_error( Notification::get( 'authentication-limit' ), 403 );
		}
	}

	/**
	 * @return array
	 */
	private function get_error_data() {
		$qr_code_message = $this->request->post( 'qr_code_message' );
		$qr_code         = $this->qr_code_generator->generateBase64( $qr_code_message );
		$secret          = $this->request->post( Authenticate_Filter::SECRET_FIELD );

		return [
			'qr_code'         => $qr_code,
			'qr_code_message' => $qr_code_message,
			'totp_secret'     => $secret
		];
	}
}
