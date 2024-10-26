<?php

namespace TwoFAS\TwoFAS\Update;

use TwoFAS\Core\Storage\DB_Wrapper;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\Storage;

abstract class Migration implements Migration_Interface {

	/**
	 * @var DB_Wrapper
	 */
	protected $db;

	/**
	 * @var API_Wrapper
	 */
	protected $api_wrapper;

	/**
	 * @var Storage
	 */
	protected $storage;

	/**
	 * @var array
	 */
	protected $tables = [];

	/**
	 * @param DB_Wrapper  $db
	 * @param Storage     $storage
	 * @param API_Wrapper $api_wrapper
	 */
	public function __construct( DB_Wrapper $db, Storage $storage, API_Wrapper $api_wrapper ) {
		$this->db          = $db;
		$this->storage     = $storage;
		$this->api_wrapper = $api_wrapper;

		$this->set_table_full_names();
	}

	/**
	 * @param string $version
	 *
	 * @return bool
	 */
	public function supports( $version ) {
		return $this->run_always();
	}

	/**
	 * @return string
	 */
	abstract protected function introduced();

	/**
	 * @return bool
	 */
	protected function run_always() {
		return true;
	}

	/**
	 * @param string $version
	 *
	 * @return bool
	 */
	protected function do_not_run_on_fresh_install( $version ) {
		return version_compare( $version, '0', '>' )
			&& version_compare( $version, $this->introduced(), '<' );
	}

	/**
	 * @param string $table_name
	 *
	 * @return string
	 */
	protected function get_table_full_name( $table_name ) {
		return str_replace( '{prefix}', $this->db->get_prefix(), $table_name );
	}

	protected function set_table_full_names() {
		foreach ( $this->tables as $table_key => $table_name ) {
			$this->tables[ $table_key ] = $this->get_table_full_name( $table_name );
		}
	}
}
