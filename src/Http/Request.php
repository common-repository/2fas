<?php

namespace TwoFAS\TwoFAS\Http;

use TwoFAS\Core\Http\Request as Base_Request;
use TwoFAS\TwoFAS\Hooks\Authenticate_Filter;

class Request extends Base_Request {

	const TWOFAS_ARRAY_KEY = 'twofas';

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has_twofas_param( $key ) {
		return $this->are_twofas_params_sent()
			&& array_key_exists( $key, $this->post[ self::TWOFAS_ARRAY_KEY ] );
	}

	/**
	 * @param array $params
	 *
	 * @return bool
	 */
	public function has_twofas_params( array $params ) {
		if ( ! $this->are_twofas_params_sent() ) {
			return false;
		}

		$keys = array_keys( $this->post[ self::TWOFAS_ARRAY_KEY ] );
		$diff = array_diff( $params, $keys );

		return empty( $diff );
	}

	/**
	 * @param string $key
	 *
	 * @return null|string
	 */
	public function get_twofas_param( $key ) {
		if ( ! $this->has_twofas_param( $key ) ) {
			return null;
		}

		$params = $this->post( self::TWOFAS_ARRAY_KEY );

		return $params[ $key ];
	}

	/**
	 * @param mixed $action
	 *
	 * @return bool
	 */
	public function is_login_action_equal_to( $action ) {
		return $this->post( Authenticate_Filter::LOGIN_ACTION_KEY ) === $action;
	}

	/**
	 * @return string
	 */
	public function action() {
		$action = $this->get( Action_Index::TWOFAS_ACTION_KEY );

		if ( is_string( $action ) ) {
			return $action;
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function referer_action() {
		parse_str( $this->header( 'HTTP_REFERER' ), $query );

		if ( array_key_exists( Action_Index::TWOFAS_ACTION_KEY, $query ) ) {
			return $query[ Action_Index::TWOFAS_ACTION_KEY ];
		}

		return '';
	}

	/**
	 * @param string $page
	 *
	 * @return bool
	 */
	public function is_page_equal_to( $page ) {
		return $this->get( 'page' ) === $page;
	}

	/**
	 * @param string $action
	 *
	 * @return bool
	 */
	public function is_action_equal_to( $action ) {
		return $this->get( 'twofas-action' ) === $action;
	}

	/**
	 * @return string
	 */
	public function get_ip() {
		$ip = $this->header( 'X-Forwarded-For' );

		if ( $this->validate_ip( $ip ) ) {
			return $ip;
		}

		$ip = $this->header( 'HTTP_X_FORWARDED_FOR' );

		if ( $this->validate_ip( $ip ) ) {
			return $ip;
		}

		return $this->header( 'REMOTE_ADDR' );
	}

	/**
	 * @param string $ip
	 *
	 * @return bool
	 */
	private function validate_ip( $ip ) {
		return null !== $ip && filter_var( $ip, FILTER_VALIDATE_IP );
	}

	/**
	 * @return array|null|string
	 */
	public function get_nonce() {
		if ( $this->is_post() ) {
			return $this->post( '_wpnonce' );
		}

		return $this->get( '_wpnonce' );
	}

	/**
	 * @return bool
	 */
	private function are_twofas_params_sent() {
		return array_key_exists( self::TWOFAS_ARRAY_KEY, $this->post )
			&& is_array( $this->post( self::TWOFAS_ARRAY_KEY ) );
	}
}
