<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Update\Migration;
use TwoFAS\TwoFAS\Update\Rollback_Migration;

class Migration_2019_12_22_Create_Authentications_Table extends Migration implements Rollback_Migration {

	const TABLE_AUTHENTICATIONS = 'authentications';

	/**
	 * @var array
	 */
	protected $tables = [
		self::TABLE_AUTHENTICATIONS => '{prefix}twofas_authentications',
	];

	/**
	 * @return string
	 */
	protected function introduced() {
		return '2.6.0';
	}

	/**
	 * @throws Migration_Exception
	 */
	public function up() {
		$charset_collate = $this->db->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$this->tables[ self::TABLE_AUTHENTICATIONS ]} (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`user_id` bigint(20) unsigned NOT NULL,
`authentication_id` varchar(255) NOT NULL,
`created_at` bigint(20) NOT NULL,
`valid_to` bigint(20) NOT NULL,
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
		$result = $this->db->query( 'DROP TABLE IF EXISTS ' . $this->tables[ self::TABLE_AUTHENTICATIONS ] );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}
	}
}
