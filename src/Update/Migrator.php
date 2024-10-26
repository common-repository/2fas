<?php

namespace TwoFAS\TwoFAS\Update;

use DirectoryIterator;
use LogicException;
use TwoFAS\Core\Storage\DB_Wrapper;
use TwoFAS\TwoFAS\Exceptions\DB_Exception;
use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\Storage;

class Migrator {

	const TABLE_MIGRATION = 'twofas_migrations';

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @var Plugin_Version
	 */
	private $plugin_version;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Storage
	 */
	private $storage;

	/**
	 * @var string
	 */
	private $migrations_path;

	/**
	 * @param DB_Wrapper     $db
	 * @param Plugin_Version $plugin_version
	 * @param API_Wrapper    $api_wrapper
	 * @param Storage        $storage
	 */
	public function __construct( DB_Wrapper $db, Plugin_Version $plugin_version, API_Wrapper $api_wrapper, Storage $storage ) {
		$this->db              = $db;
		$this->plugin_version  = $plugin_version;
		$this->api_wrapper     = $api_wrapper;
		$this->storage         = $storage;
		$this->migrations_path = __DIR__ . '/Migrations';
	}

	/**
	 * @param string $migrations_path
	 */
	public function set_migrations_path( $migrations_path ) {
		$this->migrations_path = $migrations_path;
	}

	/**
	 * @throws Migration_Exception
	 * @throws DB_Exception
	 * @throws LogicException
	 */
	public function migrate() {
		$db_plugin_version = $this->plugin_version->get_db_version();
		$migrations        = array_diff( $this->get_migrations(), $this->get_executed_migrations() );

		sort( $migrations );

		foreach ( $migrations as $name ) {
			$migration_name = $this->get_fully_qualified_name( $name );

			/** @var Migration_Interface $migration */
			$migration = new $migration_name( $this->db, $this->storage, $this->api_wrapper );

			if ( ! ( $migration instanceof Migration_Interface ) ) {
				throw new LogicException( 'Migration should be instance of Migration_Interface' );
			}

			if ( $migration->supports( $db_plugin_version ) ) {
				$migration->up();
				$this->add_migration( $name );
			}

		}
	}

	/**
	 * @throws Migration_Exception
	 */
	public function rollback_all() {
		$migrations = $this->get_executed_migrations();

		rsort( $migrations );

		foreach ( $migrations as $name ) {
			$migration_name = $this->get_fully_qualified_name( $name );

			/** @var Migration_Interface $migration */
			$migration = new $migration_name( $this->db, $this->storage, $this->api_wrapper );

			if ( $migration instanceof Rollback_Migration ) {
				$migration->down();
			}
		}
	}

	/**
	 * @param string $migration_name
	 *
	 * @return bool
	 */
	public function is_migrated( $migration_name ) {
		return in_array($migration_name, $this->get_executed_migrations(), true);
	}

	/**
	 * @return bool
	 */
	private function check_migration_table() {
		$table_exist = $this->db->get_var( "SHOW TABLES LIKE '" . $this->get_migration_table_name() . "' " );

		return ! is_null( $table_exist );
	}

	/**
	 * @return array
	 */
	private function get_migrations() {
		$migrations = [];

		foreach ( new DirectoryIterator( $this->migrations_path ) as $migration ) {
			if ( $migration->isDot() ) {
				continue;
			}

			$filename = $migration->getFilename();

			if ( ! preg_match( '/^Migration_\d{4}_\d{2}_\d{2}(_[a-zA-Z]+)+\.php$/', $filename ) ) {
				continue;
			}

			$migrations[] = str_replace( '.php', '', $filename );
		}

		return $migrations;
	}

	/**
	 * @return array
	 */
	private function get_executed_migrations() {
		if ( ! $this->check_migration_table() ) {
			return [];
		}

		return $this->db->get_col( "SELECT migration FROM " . $this->get_migration_table_name() . " " );
	}

	/**
	 * @param string $migration_name
	 *
	 * @throws DB_Exception
	 */
	private function add_migration( $migration_name ) {
		$result = $this->db->insert( $this->get_migration_table_name(), [
			'migration' => $migration_name
		] );

		if ( $result === false ) {
			throw new DB_Exception( $this->db->get_last_error() );
		}
	}

	/**
	 * @param string $migration_name
	 *
	 * @return string
	 */
	private function get_fully_qualified_name( $migration_name ) {
		return 'TwoFAS\\TwoFAS\\Update\\Migrations\\' . $migration_name;
	}

	/**
	 * @return string
	 */
	private function get_migration_table_name() {
		return $this->db->get_prefix() . self::TABLE_MIGRATION;
	}
}
