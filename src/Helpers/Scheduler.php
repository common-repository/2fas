<?php

namespace TwoFAS\TwoFAS\Helpers;

class Scheduler {

	/**
	 * @param string $hook
	 */
	public function weekly( $hook ) {
		$timestamp = wp_next_scheduled( $hook );

		if ( false === $timestamp ) {
			wp_schedule_event( time(), 'weekly', $hook );
		}
	}
}
