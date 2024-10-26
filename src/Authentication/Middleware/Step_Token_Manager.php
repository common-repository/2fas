<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Authentication\Step_Token;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Notifications\Notification;
use WP_Error;
use WP_User;

/**
 * This class is responsible for generating a new step token.
 */
final class Step_Token_Manager extends Middleware {

	/**
	 * @var Step_Token
	 */
	private $step_token;

	/**
	 * @param Step_Token $step_token
	 */
	public function __construct( Step_Token $step_token ) {
		$this->step_token = $step_token;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null ) {
		try {
			if ( $this->is_wp_user( $user ) ) {
				$this->step_token->generate();
			}

			return $this->run_next( $user, $response );
		} catch ( User_Not_Found_Exception $e ) {
			return $this->json_error( Notification::get( 'user-not-found' ), 404 );
		}
	}
}
