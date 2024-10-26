<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Storage\User_Storage;
use WP_Error;
use WP_User;

/**
 * This class calls a WordPress function which
 * determines whether the administration panel
 * should be viewed over SSL.
 */
final class SSL extends Middleware {

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @param User_Storage $user_storage
	 */
	public function __construct( User_Storage $user_storage ) {
		$this->user_storage = $user_storage;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null ) {
		if ( ! $this->is_wp_user( $user ) && $this->user_storage->is_wp_user_set() && ! force_ssl_admin() ) {
			if ( get_user_option( 'use_ssl', $this->user_storage->get_user_id() ) ) {
				force_ssl_admin( true );
			}
		}

		return $this->run_next( $user, $response );
	}
}
