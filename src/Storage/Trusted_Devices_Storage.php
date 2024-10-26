<?php

namespace TwoFAS\TwoFAS\Storage;

use TwoFAS\Core\Http\Request;
use TwoFAS\Core\Storage\DB_Wrapper;
use TwoFAS\TwoFAS\Http\Cookie;
use TwoFAS\TwoFAS\Randomization\Hash;

class Trusted_Devices_Storage {

	const TRUSTED_DEVICE_COOKIE_NAME_PREFIX = 'twofas_trusted_device_';
	const TWOFAS_TRUSTED_DEVICES            = 'twofas_trusted_devices';

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Cookie
	 */
	private $cookie;

	/**
	 * @param DB_Wrapper $db
	 * @param Request    $request
	 */
	public function __construct( DB_Wrapper $db, Request $request ) {
		$this->db      = $db;
		$this->request = $request;
		$this->cookie  = $request->cookie();
	}

	/**
	 * @param int $user_id
	 *
	 * @return array
	 */
	public function get_trusted_devices( $user_id ) {
		$trusted_devices = $this->get_all( $user_id );

		if ( ! is_array( $trusted_devices ) ) {
			$trusted_devices = [];
		}

		return $trusted_devices;
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function is_device_trusted( $user_id ) {
		$trusted_device_id = $this->get_trusted_device_id( $user_id );

		return ! is_null( $trusted_device_id );
	}

	/**
	 * @param int $user_id
	 */
	public function add_trusted_device( $user_id ) {
		$device_id    = self::TRUSTED_DEVICE_COOKIE_NAME_PREFIX . Hash::get_trusted_device_id();
		$cookie_value = Hash::get_trusted_device_id();

		$this->cookie->set_cookie( $device_id, $cookie_value, Cookie::SEVERAL_DOZEN_YEARS_IN_SECONDS, true );

		$created_at = time();
		$ip         = $this->request->get_ip();
		$user_agent = $this->request->header( 'HTTP_USER_AGENT' );

		$this->add( $user_id, $device_id, $cookie_value, $ip, $created_at, $user_agent );
	}

	/**
	 * @param int $user_id
	 */
	public function set_trusted_device_login_time( $user_id ) {
		$device_id = $this->get_trusted_device_id( $user_id );
		$this->update( $user_id, $device_id, time() );
	}

	/**
	 * @param int    $user_id
	 * @param string $device_id
	 */
	public function remove_trusted_device( $user_id, $device_id ) {
		$this->cookie->delete_cookie( $device_id );
		$this->delete( $user_id, $device_id );
	}

	/**
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function get_trusted_device_count( $user_id ) {
		$trusted_devices = $this->get_trusted_devices( $user_id );

		return count( $trusted_devices );
	}

	/**
	 * @param int $user_id
	 */
	public function delete_trusted_devices( $user_id ) {
		$this->delete_cookies( $user_id );
		$this->delete_from_db( $user_id );
	}

	/**
	 * @param int $user_id
	 *
	 * @return null|string
	 */
	private function get_trusted_device_id( $user_id ) {
		$trusted_devices = $this->get_all( $user_id );

		if ( is_null( $trusted_devices ) ) {
			$trusted_devices = [];
		}

		foreach ( $trusted_devices as $trusted_device ) {
			$cookie_value = $this->cookie->get_cookie( $trusted_device['device_id'] );

			if ( $cookie_value === $trusted_device['cookie_value'] ) {
				return $trusted_device['device_id'];
			}
		}

		return null;
	}

	/**
	 * @param int $user_id
	 *
	 * @return null|array
	 */
	private function get_all( $user_id ) {
		$table = $this->get_table_full_name( self::TWOFAS_TRUSTED_DEVICES );
		$sql   = $this->db->prepare(
			"SELECT * FROM {$table} WHERE `user_id` = %d",
			[ $user_id ]
		);

		return $this->db->get_results( $sql, ARRAY_A );
	}

	/**
	 * @param int    $user_id
	 * @param string $device_id
	 * @param string $cookie_value
	 * @param string $ip
	 * @param int    $created_at
	 * @param string $user_agent
	 */
	private function add( $user_id, $device_id, $cookie_value, $ip, $created_at, $user_agent ) {
		$table = $this->get_table_full_name( self::TWOFAS_TRUSTED_DEVICES );

		$this->db->insert(
			$table,
			[
				'user_id'      => $user_id,
				'device_id'    => $device_id,
				'cookie_value' => $cookie_value,
				'ip'           => $ip,
				'created_at'   => $created_at,
				'user_agent'   => $user_agent
			],
			[ '%d', '%s', '%s', '%s', '%d', '%s' ]
		);
	}

	/**
	 * @param int    $user_id
	 * @param string $device_id
	 * @param int    $last_logged_in
	 */
	private function update( $user_id, $device_id, $last_logged_in ) {
		$table = $this->get_table_full_name( self::TWOFAS_TRUSTED_DEVICES );

		$this->db->update(
			$table,
			[
				'last_logged_in' => $last_logged_in,
			],
			[
				'user_id'   => $user_id,
				'device_id' => $device_id
			],
			[ '%d' ],
			[ '%d', '%s' ]
		);
	}

	/**
	 * @param int    $user_id
	 * @param string $device_id
	 */
	private function delete( $user_id, $device_id ) {
		$table = $this->get_table_full_name( self::TWOFAS_TRUSTED_DEVICES );

		$this->db->delete(
			$table,
			[
				'user_id'   => $user_id,
				'device_id' => $device_id
			],
			[ '%d', '%s' ]
		);
	}

	/**
	 * @param int $user_id
	 */
	private function delete_cookies( $user_id ) {
		$trusted_devices = $this->get_all( $user_id );

		foreach ( $trusted_devices as $trusted_device ) {
			$this->cookie->delete_cookie( $trusted_device['device_id'] );
		}
	}

	/**
	 * @param int $user_id
	 */
	private function delete_from_db( $user_id ) {
		$table = $this->get_table_full_name( self::TWOFAS_TRUSTED_DEVICES );

		$this->db->delete(
			$table,
			[
				'user_id' => $user_id
			],
			[ '%d' ]
		);
	}

	/**
	 * @param string $table_name
	 *
	 * @return string
	 */
	private function get_table_full_name( $table_name ) {
		return $this->db->get_prefix() . $table_name;
	}
}
