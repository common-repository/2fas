<?php

namespace TwoFAS\TwoFAS\Requirements\Extensions;

class Gd extends Extension {

	/**
	 * @return bool
	 */
	public function is_satisfied() {
		if ( ! is_null( $this->is_satisfied ) ) {
			return $this->is_satisfied;
		}

		return $this->is_satisfied = extension_loaded( 'gd' );
	}

	/**
	 * @return string
	 */
	public function get_message() {
		return $this->get_php_extension_message( 'GD' );
	}
}
