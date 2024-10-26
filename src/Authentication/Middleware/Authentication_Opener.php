<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use Exception;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Authenticator;
use TwoFAS\TwoFAS\Exceptions\Authentication_Limit_Reached_Exception;
use TwoFAS\TwoFAS\Integration\Integration_User;
use TwoFAS\TwoFAS\Notifications\Notification;
use TwoFAS\TwoFAS\Storage\Authentication_Storage;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;
use WP_Error;
use WP_User;

class Authentication_Opener extends Middleware {

	/**
	 * @var Authenticator
	 */
	private $authenticator;

	/**
	 * @var Authentication_Storage
	 */
	private $authentication_storage;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Integration_User
	 */
	private $integration_user;

	/**
	 * @var Error_Handler_Interface
	 */
	private $error_handler;

	/**
	 * @param Authenticator           $authenticator
	 * @param Storage                 $storage
	 * @param Integration_User        $integration_user
	 * @param Error_Handler_Interface $error_handler
	 */
	public function __construct(
		Authenticator $authenticator,
		Storage $storage,
		Integration_User $integration_user,
		Error_Handler_Interface $error_handler
	) {
		$this->authenticator          = $authenticator;
		$this->authentication_storage = $storage->get_authentication_storage();
		$this->user_storage           = $storage->get_user_storage();
		$this->integration_user       = $integration_user;
		$this->error_handler          = $error_handler;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null ) {
		try {
			if ( $response instanceof JSON_Response || ! $this->user_storage->is_wp_user_set() ) {
				return $this->run_next( $user, $response );
			}

			if ( ! $this->integration_user->exists() ) {
				$response = $this->json_error( Notification::get( 'integration-user-not-found' ), 404 );

				return $this->run_next( $user, $response );
			}

			if ( ! $response instanceof View_Response ) {
				$this->authenticator->open_authentication( $this->integration_user->get_user(), $this->is_wp_user( $user ) );
			}

		} catch ( Authentication_Limit_Reached_Exception $e ) {
			$this->user_storage->block_user();
			$this->authentication_storage->close_authentication( $this->user_storage->get_user_id() );

			$response = $this->json_error( Notification::get( 'authentication-limit' ), 403 );
		} catch ( Exception $e ) {
			$response = $this->error_handler->capture_exception( $e )->to_json( $e );
		}

		return $this->run_next( $user, $response );
	}
}
