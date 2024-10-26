<?php

namespace TwoFAS\Core\Hooks;

class Hook_Handler {

	/**
	 * @var Hook_Interface[]
	 */
	private $hooks = array();

	/**
	 * @param Hook_Interface $hook
	 *
	 * @return Hook_Handler
	 */
	public function add_hook( Hook_Interface $hook ) {
		$this->hooks[] = $hook;

		return $this;
	}

	public function register_hooks() {
		foreach ( $this->hooks as $hook ) {
			$hook->register_hook();
		}
	}
}
