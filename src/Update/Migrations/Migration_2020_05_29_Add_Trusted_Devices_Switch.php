<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Update\Migration;

class Migration_2020_05_29_Add_Trusted_Devices_Switch extends Migration {

	/**
	 * @return string
	 */
	protected function introduced() {
		return '3.0.0';
	}

	/**
	 * @inheritDoc
	 */
	public function up() {
		$options = $this->storage->get_options();
		$options->enable_trusted_devices();
	}
}
