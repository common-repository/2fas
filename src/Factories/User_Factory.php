<?php

namespace TwoFAS\TwoFAS\Factories;

use TwoFAS\TwoFAS\Authentication\Step_Token;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Storage\User_Storage;
use WP_User;

class User_Factory {

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
	 * @return WP_User
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function create() {
		$step_token = $this->step_token->get();

		if ( empty( $step_token ) ) {
			throw new User_Not_Found_Exception();
		}

		$wp_user = $this->find_wp_user( $step_token );

		if ( is_null( $wp_user ) ) {
			throw new User_Not_Found_Exception();
		}

		return $wp_user;
	}

	/**
	 * @param string $step_token
	 *
	 * @return null|WP_User
	 */
	private function find_wp_user( $step_token ) {
		$users = get_users( [
			'meta_key'   => User_Storage::STEP_TOKEN,
			'meta_value' => $step_token,
		] );

		if ( empty( $users ) ) {
			return null;
		}

		return $users[0];
	}
}
