<?php

namespace TwoFAS\TwoFAS\Requirements\Versions;

use TwoFAS\TwoFAS\Requirements\Requirement;

class PHP_Version extends Requirement {

	const PHP_MINIMUM_VERSION = '5.4';

	/**
	 * @return bool
	 */
	public function is_satisfied() {
		if ( ! is_null( $this->is_satisfied ) ) {
			return $this->is_satisfied;
		}

		return $this->is_satisfied = (bool) version_compare( PHP_VERSION, self::PHP_MINIMUM_VERSION, '>=' );
	}

	/**
	 * @return string
	 */
	public function get_message() {
		return sprintf(
		/* translators: %s: Minimum PHP version */
			__( '2FAS plugin does not support your PHP version. Minimum required version is %s.', '2fas' ),
			self::PHP_MINIMUM_VERSION );
	}
}
