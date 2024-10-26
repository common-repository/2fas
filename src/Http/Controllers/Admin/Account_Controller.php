<?php

namespace TwoFAS\TwoFAS\Http\Controllers\Admin;

use TwoFAS\Account\Exception\AuthorizationException;
use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\Exception\PasswordResetAttemptsRemainingIsReachedException;
use TwoFAS\Account\Exception\ValidationException;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Core\Installer;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\TwoFAS\Http\Controllers\Controller;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Templates\Views;
use TwoFAS\ValidationRules\ValidationRules;

class Account_Controller extends Controller {

	/**
	 * @var Installer
	 */
	private $installer;

	/**
	 * @param Storage     $storage
	 * @param API_Wrapper $api_wrapper
	 * @param Flash       $flash
	 * @param Installer   $installer
	 */
	public function __construct( Storage $storage, API_Wrapper $api_wrapper, Flash $flash, Installer $installer ) {
		parent::__construct( $storage, $api_wrapper, $flash );

		$this->installer = $installer;
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response|View_Response
	 *
	 * @throws Account_Exception
	 */
	public function create_account( Request $request ) {
		if ( ! $request->is_post() ) {
			return $this->render_account_view( Views::CREATE_ACCOUNT );
		}

		$email                 = $request->get_twofas_param( 'email' );
		$password              = $request->get_twofas_param( 'password' );
		$password_confirmation = $request->get_twofas_param( 'password-confirmation' );
		$privacy_policy        = $request->get_twofas_param( 'privacy-policy' );

		try {
			if ( is_null( $privacy_policy ) ) {
				throw new ValidationException( [ 'privacy_policy' => [ ValidationRules::REQUIRED ] ] );
			}

			$this->installer->create_account( $email, $password, $password_confirmation );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD ) );
		} catch ( ValidationException $e ) {
			$this->flash->add_message_now( 'error', $this->get_validation_error( $e ) );

			return $this->view( Views::CREATE_ACCOUNT, [ 'email' => $email ] );
		}
	}

	/**
	 * @param Request $request
	 *
	 * @return View_Response|Redirect_Response
	 *
	 * @throws Account_Exception
	 */
	public function login( Request $request ) {
		if ( ! $request->is_post() ) {
			return $this->render_account_view( Views::LOGIN_FORM );
		}

		$email         = $request->get_twofas_param( 'email' );
		$password      = $request->get_twofas_param( 'password' );
		$template_name = Views::LOGIN_FORM;
		$template_data = [ 'email' => $email ];

		try {
			$this->installer->create_integration( $email, $password );
			$this->flash->add_message( 'success', 'logged-in' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD ) );
		} catch ( ValidationException $e ) {
			$this->flash->add_message_now( 'error', $this->get_validation_error( $e ) );
		} catch ( AuthorizationException $e ) {
			$this->flash->add_message_now( 'error', 'invalid-credentials' );
		}

		return $this->view( $template_name, $template_data );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 */
	public function logout( Request $request ) {
		$this->storage->get_options()->delete_wp_options_except( 'twofas_plugin_version' );
		$this->storage->get_user_storage()->delete_wp_user_meta();
		$request->cookie()->delete_plugin_cookies();

		$this->flash->add_message( 'success', 'logged-out' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD, Action_Index::ACTION_CREATE_ACCOUNT ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return View_Response|Redirect_Response
	 *
	 * @throws PasswordResetAttemptsRemainingIsReachedException
	 * @throws Account_Exception
	 */
	public function reset_password( Request $request ) {
		if ( ! $request->is_post() ) {
			return $this->render_account_view( Views::RESET_PASSWORD );
		}

		$email = $request->get_twofas_param( 'email' );

		try {
			$this->api_wrapper->reset_password( $email );
			$this->flash->add_message( 'success', 'email-sent' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD, Action_Index::ACTION_LOGIN ) );
		} catch ( NotFoundException $e ) {
			$this->flash->add_message( 'success', 'email-sent' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD, Action_Index::ACTION_LOGIN ) );
		} catch ( ValidationException $e ) {
			$this->flash->add_message_now( 'error', $this->get_validation_error( $e ) );

			return $this->view( Views::RESET_PASSWORD, [ 'email' => $email ] );
		}
	}

	/**
	 * @param string $template_name
	 *
	 * @return View_Response
	 */
	private function render_account_view( $template_name ) {
		try {
			$this->render_prime_info();
			$email = $this->storage->get_user_storage()->get_email();

			return $this->view( $template_name, [
				'email' => $email,
			] );

		} catch ( User_Not_Found_Exception $e ) {
			return $this->view( Views::ERROR, [
				'description' => $e->getMessage(),
			] );
		}
	}

	private function render_prime_info() {
		$message = "<div class='notice notice-info info'>
            <p>If you want to go beyond the basic plugin. Go for our upgraded plugin 2FAS Prime. <br />
            Advantages of 2FAS Prime plugin:
            <ul>
                <li>- No registration required</li>
                <li>- Easy to set up</li>
                <li>- Simple to use</li>
                <li>- Free</li>
			</ul>
			</p>
        </div>";

		add_action( 'admin_notices', function () use ($message) {
			echo $message;
		}, 20 );
	}
}
