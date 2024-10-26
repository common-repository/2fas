<?php

namespace TwoFAS\TwoFAS\Events;

use TwoFAS\Account\Integration;
use TwoFAS\Core\Events\Event_Interface;

class Integration_Was_Created implements Event_Interface {

	/**
	 * @var Integration
	 */
	private $integration;

	/**
	 * @param Integration $integration
	 */
	public function __construct( Integration $integration ) {
		$this->integration = $integration;
	}
}
