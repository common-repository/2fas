<?php

namespace TwoFAS\TwoFAS\Storage;

use DateTime;
use Exception;
use TwoFAS\Api\Authentication;
use TwoFAS\Core\Storage\DB_Wrapper;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;

class Authentication_Storage {

	const TABLE_AUTHENTICATIONS = 'twofas_authentications';

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @param DB_Wrapper $db
	 */
	public function __construct( DB_Wrapper $db ) {
		$this->db = $db;
	}

	/**
	 * @param string $user_id
	 *
	 * @return array
	 */
	public function get_authentication_ids( $user_id ) {
		$authentications = $this->get_authentications( $user_id );

		return array_map( function ( $authentication ) {
			return $authentication['authentication_id'];
		}, $authentications );
	}

	/**
	 * @param int $user_id
	 *
	 * @return array
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function get_authentications( $user_id ) {
		$authentications = $this->get_usable( $user_id );

		if ( ! is_array( $authentications ) ) {
			$authentications = [];
		}

		return $authentications;
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function is_authentication_expired( $user_id ) {
		$authentications = $this->get_usable( $user_id );

		return empty( $authentications );
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function has_open_authentication( $user_id ) {
		return ! $this->is_authentication_expired( $user_id );
	}

	/**
	 * @param int            $user_id
	 * @param Authentication $authentication
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function open_authentication( $user_id, Authentication $authentication ) {
		$this->add(
			$user_id,
			$authentication->id(),
			$authentication->createdAt()->getTimestamp(),
			$authentication->validTo()->getTimestamp()
		);
	}

	/**
	 * @param int $user_id
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function close_authentication( $user_id ) {
		$this->delete( $user_id );
	}

	/**
	 * @throws Exception
	 */
	public function delete_expired_authentications() {
		$now   = new DateTime();
		$table = $this->get_table_full_name( self::TABLE_AUTHENTICATIONS );
		$sql   = $this->db->prepare( "DELETE FROM {$table} WHERE valid_to < %d", [ $now->getTimestamp() ] );
		$this->db->query( $sql );
	}

	/**
	 * @param int $user_id
	 *
	 * @return null|array
	 */
	private function get_usable( $user_id ) {
		$table = $this->get_table_full_name( self::TABLE_AUTHENTICATIONS );
		$sql   = $this->db->prepare(
			"SELECT * FROM {$table} WHERE `user_id` = %d AND `valid_to` >= UNIX_TIMESTAMP()",
			[ $user_id ]
		);

		return $this->db->get_results( $sql, ARRAY_A );
	}

	/**
	 * @param int    $user_id
	 * @param string $authentication_id
	 * @param int    $created_at
	 * @param int    $valid_to
	 */
	private function add( $user_id, $authentication_id, $created_at, $valid_to ) {
		$table = $this->get_table_full_name( self::TABLE_AUTHENTICATIONS );

		$this->db->insert(
			$table,
			[
				'user_id'           => $user_id,
				'authentication_id' => $authentication_id,
				'created_at'        => $created_at,
				'valid_to'          => $valid_to,
			],
			[ '%d', '%s', '%d', '%d' ]
		);
	}

	/**
	 * @param int $user_id
	 */
	private function delete( $user_id ) {
		$table = $this->get_table_full_name( self::TABLE_AUTHENTICATIONS );

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
