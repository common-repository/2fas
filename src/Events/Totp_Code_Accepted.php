<?php

namespace TwoFAS\TwoFAS\Events;

use TwoFAS\Api\Code\Code;
use TwoFAS\Core\Events\Event_Interface;

abstract class Totp_Code_Accepted implements Event_Interface {

	/**
	 * @var Code
	 */
	private $code;

	/**
	 * @param Code $code
	 */
	public function __construct( Code $code ) {
		$this->code = $code;
	}
}
