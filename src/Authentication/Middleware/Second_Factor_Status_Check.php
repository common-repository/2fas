<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Storage\Options_Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;
use WP_Error;
use WP_User;

/**
 * This class checks whether user has 2FA enabled.
 */
final class Second_Factor_Status_Check extends Middleware {

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @param User_Storage    $user_storage
	 * @param Options_Storage $options_storage
	 */
	public function __construct( User_Storage $user_storage, Options_Storage $options_storage ) {
		$this->user_storage    = $user_storage;
		$this->options_storage = $options_storage;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null ) {
		if (
			$this->is_wp_user( $user )
			&& ! $this->user_storage->is_2fa_enabled()
			&& ! $this->options_storage->has_twofas_role( $user->roles )
		) {
			return $this->json( [ 'user_id' => $user->ID ], 200 );
		}

		return $this->run_next( $user, $response );
	}
}
