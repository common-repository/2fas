<?php

namespace TwoFAS\TwoFAS\Authentication;

class Channel_Name {

	/**
	 * @var string|null
	 */
	private $name;

	/**
	 * @param string|null $name
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * @return string|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @param Totp_Token $totp_token
	 *
	 * @return bool
	 */
	public function is_valid( Totp_Token $totp_token ) {
		return substr( $this->name, - 12 ) === $totp_token->get_salt();
	}
}
