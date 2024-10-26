<?php

namespace TwoFAS\TwoFAS\Storage;

interface Session_Storage_Interface {

	/**
	 * @return string
	 */
	public function get_session_id();

	/**
	 * @return bool
	 */
	public function exists();

	public function refresh();

	/**
	 * @return array|null
	 */
	public function get_session();

	public function add_session();

	public function delete_session();

	public function delete_expired_sessions();

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function variable_exists( $key );

	/**
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function get_variable( $key );

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function add_variable( $key, $value );

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function update_variable( $key, $value );

	/**
	 * @param string $key
	 */
	public function delete_variable( $key );
}
