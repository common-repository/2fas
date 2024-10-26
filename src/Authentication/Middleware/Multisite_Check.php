<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use WP_Error;
use WP_User;

/**
 * This class provides a compatibility with a multisite mode.
 * It prevents from displaying a second factor template
 * when a logged in user goes to a login page.
 */
final class Multisite_Check extends Middleware {

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null ) {
		if ( $this->is_wp_user( $user ) && is_multisite() && is_user_logged_in() ) {
			return $this->json( [ 'user_id' => $user->ID ], 200 );
		}

		return $this->run_next( $user, $response );
	}
}
