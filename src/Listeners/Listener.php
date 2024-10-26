<?php

namespace TwoFAS\TwoFAS\Listeners;

use LogicException;

abstract class Listener {

	/**
	 * @param string $event
	 *
	 * @return $this
	 *
	 * @throws LogicException
	 */
	public function listen_for( $event ) {
		if ( ! class_exists( $event ) ) {
			throw new LogicException( 'Event ' . $event . ' does not exists' );
		}

		add_action( $event, [ $this, 'handle' ] );

		return $this;
	}
}
