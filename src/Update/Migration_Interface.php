<?php

namespace TwoFAS\TwoFAS\Update;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;

interface Migration_Interface {

	/**
	 * @param string $version
	 *
	 * @return bool
	 */
	public function supports( $version );

	/**
	 * @throws Migration_Exception
	 */
	public function up();
}
