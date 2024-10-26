<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Update\Migration;

class Migration_2021_04_30_Add_User_Migration_Status extends Migration {

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
		$options = $this->storage->get_options();
		$options->add_user_migration_status();
	}
}
