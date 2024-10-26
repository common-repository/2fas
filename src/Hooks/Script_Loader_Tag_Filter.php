<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;

class Script_Loader_Tag_Filter implements Hook_Interface {

	public function register_hook() {
		add_filter( 'script_loader_tag', [ $this, 'add_crossorigin' ], 10, 3 );
	}

	/**
	 * @param string $tag
	 * @param string $handle
	 * @param string $src
	 *
	 * @return string
	 */
	public function add_crossorigin( $tag, $handle, $src ) {
		if ( 'sentry' === $handle ) {
			$tag = '<script type="text/javascript" src="' . esc_url( $src ) . '" crossorigin="anonymous"></script>';
		}

		return $tag;
	}
}
