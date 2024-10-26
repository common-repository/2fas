<?php

namespace TwoFAS\TwoFAS\Update;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;

interface Rollback_Migration {

	/**
	 * @throws Migration_Exception
	 */
	public function down();
}
