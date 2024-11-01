<?php

namespace TwoFAS\TwoFAS\Storage;

use DateInterval;
use DateTime;
use Exception;
use TwoFAS\Core\Http\Cookie;
use TwoFAS\Core\Storage\DB_Wrapper;
use TwoFAS\TwoFAS\Http\Session;
use TwoFAS\TwoFAS\Randomization\Hash;
use wpdb;

class DB_Session_Storage implements Session_Storage_Interface {

	const TABLE_SESSIONS          = 'twofas_sessions';
	const TABLE_SESSION_VARIABLES = 'twofas_session_variables';

	/**
	 * @var string
	 */
	private $session_id;

	/**
	 * @var Cookie
	 */
	private $cookie;

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * @var array
	 */
	private $cached_variables = [];

	/**
	 * @param Cookie     $cookie
	 * @param DB_Wrapper $db
	 */
	public function __construct( Cookie $cookie, DB_Wrapper $db ) {
		$this->cookie     = $cookie;
		$this->db         = $db;
		$this->session_id = $this->cookie->get_cookie( Session::SESSION_COOKIE_NAME );
	}

	/**
	 * @return string
	 */
	public function get_session_id() {
		return $this->session_id;
	}

	/**
	 * @return bool
	 */
	public function exists() {
		if ( empty( $this->session_id ) ) {
			return false;
		}

		$table = $this->get_table_full_name( self::TABLE_SESSIONS );
		$sql   = $this->db->prepare(
			"SELECT COUNT(1) FROM {$table} WHERE session_id = %s",
			[ $this->get_hash( $this->session_id ) ]
		);

		return (bool) $this->db->get_var( $sql );
	}

	/**
	 * @throws Exception
	 */
	public function refresh() {
		if ( empty( $this->session_id ) ) {
			return;
		}

		$table       = $this->get_table_full_name( self::TABLE_SESSIONS );
		$expiry_date = $this->get_expiry_date();

		$this->cookie->set_cookie(
			Session::SESSION_COOKIE_NAME,
			$this->session_id,
			Session::ONE_HOUR_IN_SECONDS,
			true
		);

		$this->db->update(
			$table,
			[
				'expiry_date' => $expiry_date->getTimestamp(),
			],
			[
				'session_id' => $this->get_hash( $this->session_id ),
			],
			[ '%s' ],
			[ '%s' ]
		);
	}

	/**
	 * @return array|null
	 *
	 * @throws Exception
	 */
	public function get_session() {
		if ( empty( $this->session_id ) ) {
			return null;
		}

		$table  = $this->get_table_full_name( self::TABLE_SESSIONS );
		$sql    = "SELECT session_id, expiry_date FROM {$table} WHERE session_id = %s";
		$sql    = $this->db->prepare( $sql, [ $this->get_hash( $this->session_id ) ] );
		$result = $this->db->get_row( $sql, ARRAY_A );

		if ( is_null( $result ) ) {
			return null;
		}

		return [
			'session_id'  => $result['session_id'],
			'expiry_date' => new DateTime( '@' . intval( $result['expiry_date'] ) ),
		];
	}

	/**
	 * @throws Exception
	 */
	public function add_session() {
		$table            = $this->get_table_full_name( self::TABLE_SESSIONS );
		$this->session_id = Hash::get_session_id();
		$expiry_date      = $this->get_expiry_date();

		$this->cookie->set_cookie(
			Session::SESSION_COOKIE_NAME,
			$this->session_id,
			Session::ONE_HOUR_IN_SECONDS,
			true
		);

		$this->db->insert(
			$table,
			[
				'session_id'  => $this->get_hash( $this->session_id ),
				'expiry_date' => $expiry_date->getTimestamp(),
			],
			[ '%s', '%d' ]
		);
	}

	public function delete_session() {
		if ( empty( $this->session_id ) ) {
			return;
		}

		$table = $this->get_table_full_name( self::TABLE_SESSIONS );

		$this->db->delete(
			$table,
			[ 'session_id' => $this->get_hash( $this->session_id ) ],
			[ '%s' ]
		);

		$this->cookie->delete_cookie( Session::SESSION_COOKIE_NAME );
	}

	/**
	 * @throws Exception
	 */
	public function delete_expired_sessions() {
		$now   = new DateTime();
		$table = $this->get_table_full_name( self::TABLE_SESSIONS );
		$sql   = $this->db->prepare( "DELETE FROM {$table} WHERE expiry_date < %d", [ $now->getTimestamp() ] );
		$this->db->query( $sql );
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function variable_exists( $key ) {
		if ( empty( $this->session_id ) ) {
			return false;
		}

		if ( array_key_exists( $key, $this->cached_variables ) ) {
			return true;
		}

		$table = $this->get_table_full_name( self::TABLE_SESSION_VARIABLES );
		$sql   = $this->db->prepare(
			"SELECT COUNT(1) FROM {$table} WHERE session_id = %s AND session_key = %s",
			[ $this->get_hash( $this->session_id ), $key ]
		);

		return (bool) $this->db->get_var( $sql );
	}

	/**
	 * @param string $key
	 *
	 * @return null|string
	 */
	public function get_variable( $key ) {
		if ( empty( $this->session_id ) ) {
			return false;
		}

		if ( array_key_exists( $key, $this->cached_variables ) ) {
			return $this->cached_variables[ $key ];
		}

		$table = $this->get_table_full_name( self::TABLE_SESSION_VARIABLES );
		$sql   = $this->db->prepare(
			"SELECT session_value FROM {$table} WHERE session_id = %s AND session_key = %s",
			[ $this->get_hash( $this->session_id ), $key ]
		);

		$variable = $this->db->get_var( $sql );

		if ( is_null( $variable ) ) {
			return null;
		}

		$this->cached_variables[ $key ] = $variable;

		return $variable;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function add_variable( $key, $value ) {
		if ( empty( $this->session_id ) ) {
			return;
		}

		$table = $this->get_table_full_name( self::TABLE_SESSION_VARIABLES );

		$this->db->insert(
			$table,
			[
				'session_id'    => $this->get_hash( $this->session_id ),
				'session_key'   => $key,
				'session_value' => $value,
			],
			[ '%s', '%s', '%s' ]
		);

		$this->cached_variables[ $key ] = $value;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function update_variable( $key, $value ) {
		if ( empty( $this->session_id ) ) {
			return;
		}

		$table = $this->get_table_full_name( self::TABLE_SESSION_VARIABLES );

		$this->db->update(
			$table,
			[
				'session_value' => $value,
			],
			[
				'session_id'  => $this->get_hash( $this->session_id ),
				'session_key' => $key,
			],
			[ '%s' ],
			[ '%s', '%s' ]
		);

		$this->cached_variables[ $key ] = $value;
	}

	/**
	 * @param string $key
	 */
	public function delete_variable( $key ) {
		if ( empty( $this->session_id ) ) {
			return;
		}

		$table = $this->get_table_full_name( self::TABLE_SESSION_VARIABLES );

		$this->db->delete(
			$table,
			[
				'session_id'  => $this->get_hash( $this->session_id ),
				'session_key' => $key,
			],
			[ '%s', '%s' ]
		);

		unset( $this->cached_variables[ $key ] );
	}

	/**
	 * @param string $table_name
	 *
	 * @return string
	 */
	private function get_table_full_name( $table_name ) {
		return $this->db->get_prefix() . $table_name;
	}

	/**
	 * @return DateTime
	 *
	 * @throws Exception
	 */
	private function get_expiry_date() {
		$now = new DateTime();

		return $now->add( new DateInterval( 'PT' . Session::ONE_HOUR_IN_SECONDS . 'S' ) );
	}

	/**
	 * @param string $session_id
	 *
	 * @return string
	 */
	private function get_hash( $session_id ) {
		return md5( $session_id );
	}
}
