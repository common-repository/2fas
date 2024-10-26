<?php

namespace TwoFAS\TwoFAS\Authentication\Handler;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\ValidationException as API_Validation_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Login_Action;
use TwoFAS\TwoFAS\Core\Legacy_Mode_Checker;
use TwoFAS\TwoFAS\Events\Totp_Confirmation_Code_Accepted;
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
use WP_Error;
use WP_User;

/**
 * This class displays a configuration confirmation view and also handles a confirmation request.
 */
final class Configuration_Confirmation extends Login_Handler {

	/**
	 * @var Authentication_Storage
	 */
	private $authentication_storage;

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
	 * @var Legacy_Mode_Checker
	 */
	private $legacy_mode_checker;

	/**
	 * @var Integration_User
	 */
	private $integration_user;

	/**
	 * @param Storage             $storage
	 * @param Request             $request
	 * @param Session             $session
	 * @param Flash               $flash
	 * @param API_Wrapper         $api_wrapper
	 * @param Legacy_Mode_Checker $legacy_mode_checker
	 * @param Integration_User    $integration_user
	 */
	public function __construct(
		Storage $storage,
		Request $request,
		Session $session,
		Flash $flash,
		API_Wrapper $api_wrapper,
		Legacy_Mode_Checker $legacy_mode_checker,
		Integration_User $integration_user
	) {
		parent::__construct( $storage );
		$this->authentication_storage = $storage->get_authentication_storage();
		$this->request                = $request;
		$this->session                = $session;
		$this->flash                  = $flash;
		$this->api_wrapper            = $api_wrapper;
		$this->legacy_mode_checker    = $legacy_mode_checker;
		$this->integration_user       = $integration_user;
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

		return ( $this->legacy_mode_checker->totp_is_obligatory_or_legacy_mode_is_active()
				|| $this->session->exists( Authenticate_Filter::LOGIN_CONFIGURATION_IN_PROGRESS_KEY ) )
			&& ! $this->user_storage->is_totp_enabled()
			&& $this->user_storage->is_totp_configured();
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
		if ( $this->can_perform_action( Login_Action::TOTP_CONFIRMATION ) ) {
			return $this->confirm();
		} else {
			$this->session->set( Authenticate_Filter::LOGIN_CONFIGURATION_IN_PROGRESS_KEY, '1' );

			return $this->authenticate_via_totp();
		}

	}

	/**
	 * @param string $action
	 *
	 * @return bool
	 */
	private function can_perform_action( $action ) {
		return $this->request->is_login_action_equal_to( $action );
	}

	/**
	 * @return JSON_Response|View_Response
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function confirm() {
		try {
			if ( $this->authentication_storage->is_authentication_expired( $this->get_user_id() ) ) {
				throw new Authentication_Expired_Exception();
			}

			$code = $this->request->post( Authenticate_Filter::TWOFAS_CODE_KEY );

			if ( empty( $code ) ) {
				return $this->view_error( 'login/totp_confirmation.html.twig', Notification::get( 'code-required' ) );
			}

			$authentications = $this->authentication_storage->get_authentication_ids( $this->get_user_id() );
			$result          = $this->api_wrapper->check_code( $authentications, $code );

			if ( $result->accepted() ) {
				Dispatcher::dispatch( new Totp_Confirmation_Code_Accepted( $result ) );

				$this->session->delete( Authenticate_Filter::LOGIN_CONFIGURATION_IN_PROGRESS_KEY );
				$this->flash->add_message( 'success', 'totp-enabled' );

				return $this->json( [ 'user_id' => $this->get_user_id() ], 200 );
			}

			if ( ! $result->canRetry() ) {
				throw new Authentication_Limit_Reached_Exception();
			}

			return $this->view_error( 'login/totp_confirmation.html.twig', Notification::get( 'code-invalid' ) );
		} catch ( API_Validation_Exception $e ) {
			return $this->view_error( 'login/totp_confirmation.html.twig', Notification::get( 'code-validation' ) );
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
	 * @return JSON_Response|View_Response
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	private function authenticate_via_totp() {
		if ( ! $this->authentication_storage->has_open_authentication( $this->get_user_id() ) ) {

			if ( ! $this->integration_user->exists() ) {
				return $this->json_error( Notification::get( 'integration-user-not-found' ), 404 );
			}

			$authentication = $this->api_wrapper->request_auth_via_totp( $this->integration_user->get_totp_secret() );
			$this->authentication_storage->open_authentication( $this->get_user_id(), $authentication );
		}

		return $this->view( 'login/totp_confirmation.html.twig' );
	}
}
