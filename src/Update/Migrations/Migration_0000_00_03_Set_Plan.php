<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Update\Migration;

class Migration_0000_00_03_Set_Plan extends Migration {

	/**
	 * @return string
	 */
	protected function introduced() {
		return '2.0.0';
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
			$client  = $this->api_wrapper->get_client();
			$options = $this->storage->get_options();

			if ( $client->hasCard() ) {
				$options->set_premium_plan();
			} else {
				$options->set_basic_plan();
			}
		} catch ( Account_Exception $e ) {
		}
	}
}
