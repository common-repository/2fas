<?php

namespace TwoFAS\TwoFAS\Hooks;

use Exception;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;
use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Login_Process;
use TwoFAS\TwoFAS\Core\Plugin_Status;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Factories\User_Factory;
use TwoFAS\TwoFAS\Storage\User_Storage;
use TwoFAS\TwoFAS\Templates\Twig;
use WP_Error;
use WP_User;

class Authenticate_Filter implements Hook_Interface {

	const TWOFAS_CODE_KEY                     = 'twofas_code';
	const TWOFAS_REMEMBER_DEVICE_KEY          = 'twofas_remember_device';
	const LOGIN_ACTION_KEY                    = 'twofas_action';
	const PUSHER_SESSION_ID_KEY               = 'pusher_session_id';
	const LOGIN_CONFIGURATION_IN_PROGRESS_KEY = 'login_configuration_in_progress';
	const SECRET_FIELD                        = 'totp_secret';

	/**
	 * @var Login_Process
	 */
	private $login_process;

	/**
	 * @var Plugin_Status
	 */
	private $plugin_status;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var User_Factory
	 */
	private $user_factory;

	/**
	 * @var Error_Handler_Interface
	 */
	private $error_handler;

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @param Plugin_Status           $plugin_status
	 * @param Login_Process           $login_process
	 * @param User_Storage            $user_storage
	 * @param User_Factory            $user_factory
	 * @param Error_Handler_Interface $error_handler
	 * @param Twig                    $twig
	 */
	public function __construct(
		Plugin_Status $plugin_status,
		Login_Process $login_process,
		User_Storage $user_storage,
		User_Factory $user_factory,
		Error_Handler_Interface $error_handler,
		Twig $twig
	) {
		$this->login_process = $login_process;
		$this->plugin_status = $plugin_status;
		$this->user_storage  = $user_storage;
		$this->user_factory  = $user_factory;
		$this->error_handler = $error_handler;
		$this->twig          = $twig;
	}

	public function register_hook() {
		if ( $this->plugin_status->client_completed_registration() && $this->plugin_status->is_plugin_enabled() ) {
			add_filter( 'authenticate', [ $this, 'authenticate' ], 100 );
		}
	}

	/**
	 * @param null|WP_User|WP_Error $user
	 *
	 * @return null|bool|WP_User|WP_Error
	 */
	public function authenticate( $user ) {
		if ( is_null( $user ) ) {
			return null;
		}

		$this->set_wp_user( $user );

		$response = $this->login_process->authenticate( $user );

		if ( $response === false ) {
			return $user;
		}

		if ( $response instanceof JSON_Response ) {
			$status_code = $response->get_status_code();
			$body        = $response->get_body();

			if ( 200 === $status_code ) {
				return new WP_User( $body['user_id'] );
			}

			return $this->error( $body['error'] );
		}

		if ( $response instanceof Redirect_Response ) {
			$response->redirect();
		}

		if ( $response instanceof View_Response ) {
			try {
				echo $this->twig->try_render( $response->get_template(), $response->get_data() );
				exit;
			} catch ( Exception $e ) {
				return $this->error_handler->capture_exception( $e )->to_wp_error( $e );
			}
		}

		return $response;
	}

	/**
	 * @param WP_Error|WP_User $user
	 */
	private function set_wp_user( $user ) {
		try {
			if ( $this->user_storage->is_wp_user_set() ) {
				$this->user_storage->reset_wp_user();
			}

			if ( $this->is_wp_user( $user ) ) {
				$this->user_storage->set_wp_user( $user );
			} else {
				$this->user_storage->set_wp_user( $this->user_factory->create() );
			}
		} catch ( User_Not_Found_Exception $e ) {
		}
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	private function is_wp_user( $user ) {
		return $user instanceof WP_User;
	}

	/**
	 * @param string $message
	 *
	 * @return WP_Error
	 */
	private function error( $message ) {
		return new WP_Error( 'twofas_login_error', $message );
	}
}
