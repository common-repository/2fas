<?php

namespace TwoFAS\TwoFAS\Authentication\Handler;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;
use WP_Error;
use WP_User;

abstract class Login_Handler {

	/**
	 * @var Login_Handler|null
	 */
	protected $successor;

	/**
	 * @var User_Storage
	 */
	protected $user_storage;

	/**
	 * @param Storage $storage
	 */
	public function __construct( Storage $storage ) {
		$this->user_storage = $storage->get_user_storage();
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	abstract public function supports( $user );

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool|JSON_Response|Redirect_Response|View_Response
	 */
	abstract protected function handle( $user );

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool|JSON_Response|Redirect_Response|View_Response If false is returned, it means that a login request could not be handled by any of the login handlers.
	 */
	public function authenticate( $user ) {
		return $this->supports( $user ) ? $this->handle( $user ) : $this->fallback( $user );
	}

	/**
	 * @param Login_Handler $successor
	 *
	 * @return Login_Handler
	 */
	public function then( Login_Handler $successor ) {
		return $this->successor = $successor;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool|JSON_Response|Redirect_Response|View_Response
	 */
	protected function fallback( $user ) {
		if ( $this->successor ) {
			return $this->successor->authenticate( $user );
		}

		return false;
	}

	/**
	 * @param array $body
	 * @param int   $status_code
	 *
	 * @return JSON_Response
	 */
	protected function json( array $body, $status_code ) {
		return new JSON_Response( $body, $status_code );
	}

	/**
	 * @param string $message
	 * @param int    $status_code
	 *
	 * @return JSON_Response
	 */
	protected function json_error( $message, $status_code ) {
		return $this->json( [ 'error' => $message ], $status_code );
	}

	/**
	 * @param string $template
	 * @param array  $data
	 *
	 * @return View_Response
	 */
	protected function view( $template, array $data = [] ) {
		return new View_Response( $template, $data );
	}

	/**
	 * @param string $template
	 * @param string $error
	 * @param array  $data
	 *
	 * @return View_Response
	 */
	protected function view_error( $template, $error, $data = [] ) {
		return $this->view( $template, array_merge( $data, [ 'error' => new WP_Error( 'twofas_login_error', $error ) ] ) );
	}

	/**
	 * @return bool
	 */
	protected function is_wp_user_set() {
		return $this->user_storage->is_wp_user_set();
	}

	/**
	 * @return WP_User
	 *
	 * @throws User_Not_Found_Exception
	 */
	protected function get_wp_user() {
		if ( $this->user_storage->is_wp_user_set() ) {
			return $this->user_storage->get_wp_user();
		}

		throw new User_Not_Found_Exception();
	}

	/**
	 * @return int
	 *
	 * @throws User_Not_Found_Exception
	 */
	protected function get_user_id() {
		return $this->get_wp_user()->ID;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	protected function is_wp_user( $user ) {
		return $user instanceof WP_User;
	}
}
