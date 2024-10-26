<?php

namespace TwoFAS\TwoFAS\Requirements\Versions;

use TwoFAS\TwoFAS\Requirements\Requirement;

class WP_Version extends Requirement {

	const WP_MINIMUM_VERSION = '3.8';

	/**
	 * @return bool
	 */
	public function is_satisfied() {
		if ( ! is_null( $this->is_satisfied ) ) {
			return $this->is_satisfied;
		}

		return $this->is_satisfied = (bool) version_compare( get_bloginfo( 'version' ), self::WP_MINIMUM_VERSION, '>=' );
	}

	/**
	 * @return string
	 */
	public function get_message() {
		return sprintf(
		/* translators: %s: Minimum WordPress version */
			__( '2FAS plugin does not support your WordPress version. Minimum required version is %s.', '2fas' ),
			self::WP_MINIMUM_VERSION );
	}
}
