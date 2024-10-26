<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use Exception;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Login_Action;
use TwoFAS\TwoFAS\Helpers\Login_Response;
use TwoFAS\TwoFAS\Helpers\Second_Factor_Template_Picker;
use TwoFAS\TwoFAS\Hooks\Authenticate_Filter;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Http\Session;
use TwoFAS\TwoFAS\Integration\Integration_User;
use TwoFAS\TwoFAS\Notifications\Notification;
use TwoFAS\TwoFAS\Storage\Storage;
use WP_Error;
use WP_User;

/**
 * This class sets a login template as a final response.
 */
final class Login_Template extends Middleware {

	/**
	 * @var Integration_User
	 */
	private $integration_user;

	/**
	 * @var Second_Factor_Template_Picker
	 */
	private $template_picker;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @var Login_Response
	 */
	private $login_response;

	/**
	 * @var Storage
	 */
	private $storage;

	/**
	 * @var Error_Handler_Interface
	 */
	private $error_handler;

	/**
	 * @var array
	 */
	private $login_actions = [
		'stop_login_process'      => Login_Action::STOP_LOGIN_PROCESS,
		'log_in_with_totp_code'   => Login_Action::LOG_IN_WITH_TOTP_CODE,
		'log_in_with_backup_code' => Login_Action::LOG_IN_WITH_BACKUP_CODE,
		'log_in_with_sms_code'    => Login_Action::LOG_IN_WITH_SMS_CODE,
		'log_in_with_call_code'   => Login_Action::LOG_IN_WITH_CALL_CODE,
		'verify_totp_code'        => Login_Action::VERIFY_TOTP_CODE,
		'verify_backup_code'      => Login_Action::VERIFY_BACKUP_CODE,
		'verify_sms_code'         => Login_Action::VERIFY_SMS_CODE,
		'verify_call_code'        => Login_Action::VERIFY_CALL_CODE,
		'open_sms_auth'           => Login_Action::OPEN_NEW_SMS_AUTHENTICATION,
		'open_call_auth'          => Login_Action::OPEN_NEW_CALL_AUTHENTICATION,
		'configure'               => Login_Action::CONFIGURE,
		'confirm_totp'            => Login_Action::TOTP_CONFIRMATION,
		'reset_totp'              => Login_Action::TOTP_RESET,
	];

	/**
	 * @param Integration_User              $integration_user
	 * @param Second_Factor_Template_Picker $template_picker
	 * @param Request                       $request
	 * @param Login_Response                $login_response
	 * @param Storage                       $storage
	 * @param Session                       $session
	 * @param Error_Handler_Interface       $error_handler
	 */
	public function __construct(
		Integration_User $integration_user,
		Second_Factor_Template_Picker $template_picker,
		Request $request,
		Login_Response $login_response,
		Storage $storage,
		Session $session,
		Error_Handler_Interface $error_handler
	) {
		$this->integration_user = $integration_user;
		$this->template_picker  = $template_picker;
		$this->request          = $request;
		$this->login_response   = $login_response;
		$this->storage          = $storage;
		$this->session          = $session;
		$this->error_handler    = $error_handler;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null ) {
		try {
			$user_storage = $this->storage->get_user_storage();

			if ( $response instanceof JSON_Response || ! $user_storage->is_wp_user_set() ) {
				return $this->run_next( $user, $response );
			}

			if ( ! $this->integration_user->exists() ) {
				$response = $this->json_error( Notification::get( 'integration-user-not-found' ), 404 );

				return $this->run_next( $user, $response );
			}

			if ( ! $response instanceof View_Response ) {
				$template = $this->template_picker->get_template( $this->request, $this->integration_user->get_user() );
				$response = new View_Response( $template );
			}

			$this->login_response->set_from_request( $this->request );
			$this->login_response->set_from_integration_user( $this->integration_user->get_user() );
			$this->login_response->set_from_storage( $this->storage );
			$this->login_response->set( 'actions', $this->login_actions );

			$data = $this->login_response->get_all();

			if ( array_key_exists( 'redirect_to', $data ) ) {
				$redirect_to = $data['redirect_to'];

				if ( force_ssl_admin() && false !== strpos( $redirect_to, 'wp-admin' ) ) {
					$redirect_to         = preg_replace( '|^http://|', 'https://', $redirect_to );
					$data['redirect_to'] = $redirect_to;
				}
			}

			$current_data = $response->get_data();

			foreach ( $data as $variable_name => $variable_value ) {
				$current_data[ $variable_name ] = $variable_value;
			}

			return new View_Response( $response->get_template(), $current_data );
		} catch ( Exception $e ) {
			$response = $this->error_handler->capture_exception( $e )->to_json( $e );
		}

		return $this->run_next( $user, $response );
	}
}
