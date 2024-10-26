<?php

namespace TwoFAS\TwoFAS\Http;

use DateTime;
use TwoFAS\TwoFAS\Storage\Session_Storage_Interface;
use WP_User_Meta_Session_Tokens;

class Session {

	const SESSION_COOKIE_NAME = 'twofas_session_id';
	const ONE_HOUR_IN_SECONDS = 3600;

	/**
	 * @var string|null
	 */
	private $id;

	/**
	 * @var DateTime|null
	 */
	private $expiry_date;

	/**
	 * @var Session_Storage_Interface
	 */
	private $storage;

	/**
	 * @var bool
	 */
	private $is_started = false;

	/**
	 * @param Session_Storage_Interface $storage
	 */
	public function __construct( Session_Storage_Interface $storage ) {
		$this->storage = $storage;
	}

	/**
	 * @return string
	 */
	public function get_id() {
		if ( $this->should_start() ) {
			$this->start();
		}

		return $this->id;
	}

	/**
	 * @return DateTime
	 */
	public function get_expiry_date() {
		if ( $this->should_start() ) {
			$this->start();
		}

		return $this->expiry_date;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function exists( $key ) {
		if ( $this->should_start() ) {
			$this->start();
		}

		return $this->storage->variable_exists( $key );
	}

	/**
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function get( $key ) {
		if ( $this->should_start() ) {
			$this->start();
		}

		return $this->storage->get_variable( $key );
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set( $key, $value ) {
		if ( ! $this->is_started ) {
			$this->start();
		}

		if ( $this->storage->variable_exists( $key ) ) {
			$this->storage->update_variable( $key, $value );
		} else {
			$this->storage->add_variable( $key, $value );
		}
	}

	/**
	 * @param string $key
	 */
	public function delete( $key ) {
		if ( $this->should_start() ) {
			$this->start();
		}

		$this->storage->delete_variable( $key );
	}

	public function destroy() {
		if ( $this->should_start() ) {
			$this->start();
		}

		$this->storage->delete_session();
		$this->id          = null;
		$this->expiry_date = null;
	}

	/**
	 * @param int $user_id
	 */
	public function log_out_on_other_devices( $user_id ) {
		if ( class_exists( 'WP_User_Meta_Session_Tokens' ) ) {
			$session_tokens = WP_User_Meta_Session_Tokens::get_instance( $user_id );
			$session_token  = wp_get_session_token();
			$session_tokens->destroy_others( $session_token );
		}
	}

	/**
	 * @return bool
	 */
	private function should_start() {
		$session_id = $this->storage->get_session_id();

		if ( ! $this->is_started && ! empty( $session_id ) ) {
			return true;
		}

		return false;
	}

	private function start() {
		if ( $this->storage->exists() ) {
			$this->storage->refresh();
		} else {
			$this->storage->add_session();
		}

		$session           = $this->storage->get_session();
		$this->id          = $this->storage->get_session_id();
		$this->expiry_date = $session['expiry_date'];
		$this->is_started  = true;
	}
}
