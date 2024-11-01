<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Update\Migration;
use TwoFAS\TwoFAS\Update\Rollback_Migration;

class Migration_2018_11_30_Create_Trusted_Devices_Table extends Migration implements Rollback_Migration {

	const TABLE_DEVICES = 'devices';

	/**
	 * @var array
	 */
	protected $tables = [
		self::TABLE_DEVICES => '{prefix}twofas_trusted_devices',
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

		$sql = "CREATE TABLE IF NOT EXISTS {$this->tables[ self::TABLE_DEVICES ]} (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`user_id` bigint(20) unsigned NOT NULL,
`device_id` varchar(255) NOT NULL,
`cookie_value` varchar(255) NOT NULL,
`ip` varchar(50) NOT NULL,
`created_at` bigint(20) NOT NULL,
`last_logged_in` bigint(20) unsigned DEFAULT NULL, 
`user_agent` text NOT NULL,
PRIMARY KEY (id)
) ENGINE = INNODB {$charset_collate}";

		$result = $this->db->query( $sql );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}
	}

	/**
	 * @throws Migration_Exception
	 */
	public function down() {
		$result = $this->db->query( 'DROP TABLE IF EXISTS ' . $this->tables[ self::TABLE_DEVICES ] );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}
	}
}
