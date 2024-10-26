<?php

namespace TwoFAS\TwoFAS\Factories;

use TwoFAS\Core\Storage\DB_Wrapper;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Storage\DB_Session_Storage;
use TwoFAS\TwoFAS\Storage\In_Memory_Session_Storage;
use TwoFAS\TwoFAS\Storage\Session_Storage_Interface;

class Session_Storage_Factory {

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param DB_Wrapper $db
	 * @param Request    $request
	 */
	public function __construct( DB_Wrapper $db, Request $request ) {
		$this->db      = $db;
		$this->request = $request;
	}

	/**
	 * @return Session_Storage_Interface
	 */
	public function create() {
		if ( $this->can_use_db_session_storage() ) {
			return new DB_Session_Storage( $this->request->cookie(), $this->db );
		}

		return new In_Memory_Session_Storage();
	}

	/**
	 * @return bool
	 */
	private function can_use_db_session_storage() {
		return $this->tables_exist();
	}

	/**
	 * @return bool
	 */
	private function tables_exist() {
		$table_sessions          = $this->get_table_full_name( DB_Session_Storage::TABLE_SESSIONS );
		$table_session_variables = $this->get_table_full_name( DB_Session_Storage::TABLE_SESSION_VARIABLES );

		$result1 = $this->db->get_var( "SHOW TABLES LIKE '{$table_sessions}'" );
		$result2 = $this->db->get_var( "SHOW TABLES LIKE '{$table_session_variables}'" );

		return ! is_null( $result1 ) && ! is_null( $result2 );
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
