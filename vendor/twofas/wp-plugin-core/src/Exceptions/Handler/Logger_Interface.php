<?php

namespace TwoFAS\Core\Exceptions\Handler;

use Exception;

interface Logger_Interface {

	/**
	 * @param Exception $e
	 * @param array     $options
	 */
	public function capture_exception( Exception $e, array $options = array() );
}
