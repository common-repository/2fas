<?php

namespace TwoFAS\TwoFAS\Authentication\Handler;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\ValidationException as API_Validation_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Code_Check;
use TwoFAS\TwoFAS\Exceptions\Authentication_Expired_Exception;
use TwoFAS\TwoFAS\Exceptions\Authentication_Limit_Reached_Exception;
use TwoFAS\TwoFAS\Exceptions\Offline_Codes_Disabled_Exception;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Helpers\Second_Factor_Template_Picker;
use TwoFAS\TwoFAS\Hooks\Authenticate_Filter;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Integration\Integration_User;
use TwoFAS\TwoFAS\Notifications\Notification;
use TwoFAS\TwoFAS\Storage\Authentication_Storage;
use TwoFAS\TwoFAS\Storage\Storage;
use WP_Error;
use WP_User;

/**
 * This class handles logging in if user manually enters a 2FA code.
 */
final class Standard_Login extends Login_Handler {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Code_Check
	 */
	private $code_check;

	/**
	 * @var Integration_User
	 */
	private $integration_user;

	/**
	 * @var Second_Factor_Template_Picker
	 */
	private $template_picker;

	/**
	 * @var Authentication_Storage
	 */
	private $authentication_storage;

	/**
	 * @param Request                       $request
	 * @param Code_Check                    $code_check
	 * @param Storage                       $storage
	 * @param Integration_User              $integration_user
	 * @param Second_Factor_Template_Picker $template_picker
	 */
	public function __construct(
		Request $request,
		Code_Check $code_check,
		Storage $storage,
		Integration_User $integration_user,
		Second_Factor_Template_Picker $template_picker
	) {
		parent::__construct( $storage );
		$this->request                = $request;
		$this->code_check             = $code_check;
		$this->integration_user       = $integration_user;
		$this->template_picker        = $template_picker;
		$this->authentication_storage = $storage->get_authentication_storage();
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

		$totp_token = $this->request->post( 'totp_token' );
		$status_id  = $this->request->post( 'status_id' );

		return empty( $totp_token )
			&& empty( $status_id )
			&& ! is_null( $this->request->post( Authenticate_Filter::TWOFAS_CODE_KEY ) );
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool|JSON_Response|Redirect_Response|View_Response
	 *
	 * @throws TokenNotFoundException
	 * @throws Authentication_Expired_Exception
	 * @throws User_Not_Found_Exception
	 * @throws API_Exception
	 */
	protected function handle( $user ) {
		$user_id = $this->get_user_id();

		if ( ! $this->integration_user->exists() ) {
			return $this->json_error( Notification::get( 'integration-user-not-found' ), 404 );
		}

		$template = $this->template_picker->get_template( $this->request, $this->integration_user->get_user() );
		$code     = $this->request->post( Authenticate_Filter::TWOFAS_CODE_KEY );

		if ( empty( $code ) ) {
			return $this->view_error( $template, Notification::get( 'code-required' ) );
		}

		try {
			if ( $this->authentication_storage->is_authentication_expired( $this->get_user_id() ) ) {
				throw new Authentication_Expired_Exception();
			}

			$result = $this->code_check->check( $this->request, $this->integration_user->get_user(), $code );

			if ( $result->accepted() ) {
				return $this->json( [ 'user_id' => $user_id ], 200 );
			}

			if ( ! $result->canRetry() ) {
				throw new Authentication_Limit_Reached_Exception();
			}

			return $this->view_error( $template, Notification::get( 'code-invalid' ) );
		} catch ( API_Validation_Exception $e ) {
			return $this->view_error( $template, Notification::get( 'code-validation' ) );
		} catch ( Offline_Codes_Disabled_Exception $e ) {
			return $this->json_error( Notification::get( 'disabled-offline-codes' ), 403 );
		} catch ( Authentication_Expired_Exception $e ) {
			$this->authentication_storage->close_authentication( $this->get_user_id() );

			return $this->json_error( Notification::get( 'authentication-expired' ), 403 );
		} catch ( Authentication_Limit_Reached_Exception $e ) {
			$this->user_storage->block_user();
			$this->authentication_storage->close_authentication( $this->get_user_id() );

			return $this->json_error( Notification::get( 'authentication-limit' ), 403 );
		}
	}
}
