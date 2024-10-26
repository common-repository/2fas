<?php

namespace TwoFAS\Core\Exceptions;

use Exception;

class Not_Found_Http_Exception extends Http_Exception {

	/**
	 * @param Exception|null $previous
	 */
	public function __construct( Exception $previous = null ) {
		parent::__construct( 'Not Found', 404, $previous );
	}
}
