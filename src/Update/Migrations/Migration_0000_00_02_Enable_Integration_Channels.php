<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Update\Migration;

class Migration_0000_00_02_Enable_Integration_Channels extends Migration {

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
		return false;
	}

	public function up() {

	}
}
