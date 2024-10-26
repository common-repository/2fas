<?php

namespace TwoFAS\Core\Helpers;

use TwoFAS\Core\Events\Event_Interface;

class Dispatcher {

	/**
	 * @param Event_Interface $event
	 */
	public static function dispatch( Event_Interface $event ) {
		do_action( get_class( $event ), $event );
	}
}
