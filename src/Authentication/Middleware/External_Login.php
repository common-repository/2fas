<?php

namespace TwoFAS\TwoFAS\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Core\Plugin_Compatibility;
use TwoFAS\Core\Http\Request;
use WP_Error;
use WP_User;

/**
 * This class redirects to a default WordPress login page
 * if a login is done from an external site (for example, WooCommerce login form).
 */
final class External_Login extends Middleware {

	/**
	 * @var Plugin_Compatibility
	 */
	private $plugin_compatibility;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param Plugin_Compatibility $plugin_compatibility
	 * @param Request              $request
	 */
	public function __construct( Plugin_Compatibility $plugin_compatibility, Request $request ) {
		$this->plugin_compatibility = $plugin_compatibility;
		$this->request              = $request;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|Redirect_Response|null
	 */
	public function handle( $user, $response = null ) {
		if ( $this->is_wp_user( $user ) && $this->plugin_compatibility->is_external_login( $this->request ) ) {
			return $this->redirect( $this->plugin_compatibility->get_redirect_to_url( $this->request ) );
		}

		return $this->run_next( $user, $response );
	}
}
