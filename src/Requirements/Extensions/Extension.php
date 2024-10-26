<?php

namespace TwoFAS\TwoFAS\Requirements\Extensions;

use TwoFAS\TwoFAS\Requirements\Requirement;

abstract class Extension extends Requirement {

	/**
	 * @param string $extension
	 *
	 * @return string
	 */
	protected function get_php_extension_message( $extension ) {
		return sprintf(
		/* translators: %s: Required extension */
			__( '2FAS plugin requires %s extension to work properly.', '2fas' ),
			$extension
		);
	}
}
