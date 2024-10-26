<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use WP_User_Query;
use TwoFAS\Api\Exception\IntegrationUserNotFoundException;
use TwoFAS\TwoFAS\Storage\User_Storage;
use TwoFAS\TwoFAS\Update\Migration;

class Migration_2021_05_01_Migrate_Users extends Migration {

	/**
	 * @param string $version
	 *
	 * @return bool
	 */
	public function supports( $version ) {
		return $this->storage->get_options()->is_user_migration_allowed();
	}

	/**
	 * @inheritDoc
	 */
	protected function introduced() {
		return '3.0.5';
	}

	/**
	 * @inheritDoc
	 */
	public function up() {
		$args       = [
			'count_total' => true,
			'fields'      => 'all_with_meta',
			'meta_query'  => [
				'relation' => 'AND',
				[
					'key'     => User_Storage::TOTP_STATUS,
					'value'   => User_Storage::METHOD_NOT_CONFIGURED,
					'compare' => '!='
				],
				[
					'key'     => 'twofas_light_totp_secret',
					'value'   => '',
					'compare' => 'NOT EXISTS'
				],
			]
		];
		$user_query = new WP_User_Query( $args );
		foreach ( $user_query->get_results() as $user ) {
			try {
				$integration_user = $this->api_wrapper->get_integration_user_by_external_id( $user->ID );
				$totp_status = get_user_meta( $user->ID, 'twofas_totp_status', true ) === User_Storage::METHOD_CONFIGURED_ENABLED ? 'totp_enabled': 'totp_disabled';
				update_user_meta( $user->ID, 'twofas_light_totp_status', $totp_status );
				update_user_meta( $user->ID, 'twofas_light_totp_secret', $integration_user->getTotpSecret() );
				update_user_meta( $user->ID, 'twofas_light_totp_secret_update_date', time() );
			} catch (IntegrationUserNotFoundException $e) {}
		}
	}
}
