<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Update\Migration;

class Migration_0000_00_04_Set_Plugin_Status extends Migration {

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
		$this->storage->get_options()->enable_plugin();
	}
}
