<?php

namespace TwoFAS\Core\Update;

use TwoFAS\Core\Environment_Interface;

class Deprecation {

	/**
	 * @var Environment_Interface
	 */
	private $environment;

	/**
	 * @var string
	 */
	private $deprecated_below;

	/**
	 * @param Environment_Interface $environment
	 */
	public function __construct( Environment_Interface $environment ) {
		$this->environment = $environment;
	}

	/**
	 * @param $php_version
	 */
	public function deprecate_php_older_than( $php_version ) {
		$this->deprecated_below = $php_version;
	}

	/**
	 * @return bool
	 */
	public function is_php_deprecated() {
		return version_compare( $this->environment->get_php_version(), $this->deprecated_below, '<' );
	}
}
