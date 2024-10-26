<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Update\Migration;
use TwoFAS\TwoFAS\Update\Rollback_Migration;

class Migration_0000_00_00_Create_Migrations_Table extends Migration implements Rollback_Migration {

	const TABLE_MIGRATIONS = 'migrations';

	/**
	 * @var array
	 */
	protected $tables = [
		self::TABLE_MIGRATIONS => '{prefix}twofas_migrations',
	];

	/**
	 * @return string
	 */
	protected function introduced() {
		return '2.4.0';
	}

	/**
	 * @throws Migration_Exception
	 */
	public function up() {
		$charset_collate = $this->db->get_charset_collate();

		$query = "CREATE TABLE IF NOT EXISTS {$this->tables[ self::TABLE_MIGRATIONS ]} (
migration VARCHAR(100),
PRIMARY KEY (migration)
) ENGINE = INNODB {$charset_collate}";

		$result = $this->db->query( $query );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}
	}

	/**
	 * @throws Migration_Exception
	 */
	public function down() {
		$result = $this->db->query( 'DROP TABLE IF EXISTS ' . $this->tables[ self::TABLE_MIGRATIONS ] );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}
	}
}
