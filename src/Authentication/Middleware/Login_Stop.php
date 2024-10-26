<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Login_Action;
use TwoFAS\TwoFAS\Authentication\Step_Token;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Storage\User_Storage;
use WP_Error;
use WP_User;

/**
 * This class handles a login stop request.
 */
final class Login_Stop extends Middleware {

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Step_Token
	 */
	private $step_token;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param User_Storage $user_storage
	 * @param Step_Token   $step_token
	 * @param Request      $request
	 */
	public function __construct( User_Storage $user_storage, Step_Token $step_token, Request $request ) {
		$this->user_storage = $user_storage;
		$this->step_token   = $step_token;
		$this->request      = $request;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|Redirect_Response|View_Response|null
	 */
	public function handle( $user, $response = null ) {
		if ( ! $this->is_wp_user( $user )
			&& $this->user_storage->is_wp_user_set()
			&& $this->request->is_login_action_equal_to( Login_Action::STOP_LOGIN_PROCESS ) ) {

			$this->step_token->reset();

			$login_url     = wp_login_url();
			$interim_login = $this->request->request( 'interim-login' );

			if ( $interim_login ) {
				$login_url = add_query_arg( 'interim-login', '1', $login_url );
			}

			return $this->redirect( $login_url );
		}

		return $this->run_next( $user, $response );
	}
}
