<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Update\Migration;

class Migration_2019_12_23_Move_Authentications_To_Separate_Table extends Migration {

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
	 * @param string $version
	 *
	 * @return bool
	 */
	public function supports( $version ) {
		return $this->do_not_run_on_fresh_install( $version );
	}

	/**
	 * @throws Migration_Exception
	 */
	public function up() {
		$users = get_users( [
			'meta_key' => 'twofas_authentication_id'
		] );

		foreach ( $users as $user ) {
			$authentications = get_user_meta( $user->ID, 'twofas_authentication_id', true );

			if ( empty( $authentications ) || ! is_array( $authentications ) ) {
				delete_user_meta( $user->ID, 'twofas_authentication_id' );
				delete_user_meta( $user->ID, 'twofas_authentication_valid_until' );
				delete_user_meta( $user->ID, 'twofas_is_authentication_open' );
				continue;
			}

			$valid_to = get_user_meta( $user->ID, 'twofas_authentication_valid_until', true );

			foreach ( $authentications as $authentication ) {
				$this->db->insert(
					$this->tables[ self::TABLE_AUTHENTICATIONS ],
					[
						'user_id'           => $user->ID,
						'authentication_id' => $authentication,
						'created_at'        => 0,
						'valid_to'          => $valid_to,
					],
					[ '%d', '%s', '%d', '%d' ]
				);
			}

			delete_user_meta( $user->ID, 'twofas_authentication_id' );
			delete_user_meta( $user->ID, 'twofas_authentication_valid_until' );
			delete_user_meta( $user->ID, 'twofas_is_authentication_open' );
		}
	}
}
