<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\Integration;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Update\Migration;

class Migration_0000_00_05_Reset_Integration_Encryption_Keys extends Migration {

	/**
	 * @return string
	 */
	protected function introduced() {
		return '2.1.1';
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
		} catch ( TokenNotFoundException $e ) {

		} catch ( NotFoundException $e ) {

		} catch ( Account_Exception $e ) {

		}
	}

	/**
	 * @return Integration
	 *
	 * @throws TokenNotFoundException
	 * @throws NotFoundException
	 * @throws Account_Exception
	 */
	private function get_integration() {
		return $this->api_wrapper->get_integration( $this->storage->get_oauth()->get_integration_id() );
	}
}
