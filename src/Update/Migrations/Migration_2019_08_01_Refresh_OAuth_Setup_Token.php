<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Update\Migration;

class Migration_2019_08_01_Refresh_OAuth_Setup_Token extends Migration {

	/**
	 * @return string
	 */
	public function introduced() {
		return '2.5.0';
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
		/**
		 * Do nothing since Account SDK 4.1
		 */
	}

	public function down() {
	}
}
