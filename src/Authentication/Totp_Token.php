<?php

namespace TwoFAS\TwoFAS\Authentication;

class Totp_Token {

	const TOKEN_PARTS = 2;

	/**
	 * @var array|null
	 */
	private $token;

	/**
	 * @param string|null $token
	 */
	public function __construct( $token ) {
		$this->token = explode( '_', $token );
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		return is_array( $this->token ) && count( $this->token ) === self::TOKEN_PARTS;
	}

	/**
	 * @return string|null
	 */
	public function get_code() {
		if ( ! $this->is_valid() ) {
			return null;
		}

		return $this->token[0];
	}

	/**
	 * @return string|null
	 */
	public function get_salt() {
		if ( ! $this->is_valid() ) {
			return null;
		}

		return $this->token[1];
	}
}
