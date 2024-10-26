<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Step_Token;
use TwoFAS\TwoFAS\Notifications\Notification;
use TwoFAS\TwoFAS\Storage\User_Storage;
use WP_Error;
use WP_User;

/**
 * This class checks whether user's account is blocked.
 */
final class Blocked_Account_Check extends Middleware {

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Step_Token
	 */
	private $step_token;

	/**
	 * @param User_Storage $user_storage
	 * @param Step_Token   $step_token
	 */
	public function __construct( User_Storage $user_storage, Step_Token $step_token ) {
		$this->user_storage = $user_storage;
		$this->step_token   = $step_token;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null ) {
		if ( $this->is_wp_user( $user ) && $this->user_storage->is_user_blocked() ) {
			$this->step_token->reset();

			return $this->json_error( Notification::get( 'authentication-limit' ), 403 );
		}

		return $this->run_next( $user, $response );
	}
}
