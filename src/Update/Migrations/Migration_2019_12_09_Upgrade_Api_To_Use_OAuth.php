<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Notifications\Notification;
use TwoFAS\TwoFAS\Update\Migration;

class Migration_2019_12_09_Upgrade_Api_To_Use_OAuth extends Migration {

	const MIGRATION_ERROR = 'Cannot update integration. Please check on dashboard.2fas.com that you have only one Integration Key and try again.';

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
		try {
			$oauth_storage  = $this->storage->get_oauth();
			$token          = $oauth_storage->retrieveToken( TokenType::WORDPRESS );
			$integration_id = $token->getIntegrationId();

			if ( ! $this->api_wrapper->can_integration_upgrade( $integration_id ) ) {
				throw new Migration_Exception( self::MIGRATION_ERROR );
			}

			$this->api_wrapper->upgrade_integration( $integration_id );
			delete_option( 'twofas_login' );
			delete_option( 'twofas_key' );

		} catch ( TokenNotFoundException $e ) {
			throw new Migration_Exception( Notification::get( 'oauth-token-not-found' ) );
		} catch ( Account_Exception $e ) {
			throw new Migration_Exception( self::MIGRATION_ERROR );
		}
	}
}
