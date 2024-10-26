<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Update\Migration;

class Migration_2020_06_19_Add_Privacy_Policy extends Migration {

	/**
	 * @inheritDoc
	 */
	protected function introduced() {
		return '3.0.0';
	}

	/**
	 * @inheritDoc
	 */
	public function up() {
		$options = $this->storage->get_options();
		$options->add_privacy_policy();
	}
}
