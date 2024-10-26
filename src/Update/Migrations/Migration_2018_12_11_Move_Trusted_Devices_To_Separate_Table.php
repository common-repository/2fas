<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Storage\Trusted_Devices_Storage;
use TwoFAS\TwoFAS\Update\Migration;

class Migration_2018_12_11_Move_Trusted_Devices_To_Separate_Table extends Migration {

	const TABLE_DEVICES = 'devices';

	/**
	 * @var array
	 */
	protected $tables = [
		self::TABLE_DEVICES => '{prefix}twofas_trusted_devices'
	];

	/**
	 * @param string $version
	 *
	 * @return bool
	 */
	public function supports( $version ) {
		return $this->do_not_run_on_fresh_install( $version );
	}

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
		$users = get_users( [
			'meta_key' => Trusted_Devices_Storage::TWOFAS_TRUSTED_DEVICES
		] );

		foreach ( $users as $user ) {
			$trusted_devices = get_user_meta( $user->ID, Trusted_Devices_Storage::TWOFAS_TRUSTED_DEVICES, true );

			if ( empty( $trusted_devices ) || ! is_array( $trusted_devices ) ) {
				delete_user_meta( $user->ID, Trusted_Devices_Storage::TWOFAS_TRUSTED_DEVICES );
				continue;
			}

			foreach ( $trusted_devices as $trusted_device ) {
				$this->db->insert(
					$this->tables[ self::TABLE_DEVICES ],
					[
						'user_id'        => $user->ID,
						'device_id'      => $trusted_device['cookie_name'],
						'cookie_value'   => $trusted_device['cookie_value'],
						'ip'             => $trusted_device['IP'],
						'created_at'     => $trusted_device['time'],
						'last_logged_in' => ( array_key_exists( 'last_logged_in', $trusted_device ) ? $trusted_device['last_logged_in'] : null ),
						'user_agent'     => $trusted_device['user_agent']
					],
					[ '%d', '%s', '%s', '%s', '%d', '%d', '%s' ]
				);
			}

			delete_user_meta( $user->ID, Trusted_Devices_Storage::TWOFAS_TRUSTED_DEVICES );
		}
	}
}
