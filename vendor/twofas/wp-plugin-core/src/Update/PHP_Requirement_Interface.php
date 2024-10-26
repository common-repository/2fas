<?php

namespace TwoFAS\Core\Update;

use TwoFAS\Core\Exceptions\Download_Exception;

interface PHP_Requirement_Interface {

	/**
	 * @return string
	 *
	 * @throws Download_Exception
	 */
	public function get_required_php_version();
}
