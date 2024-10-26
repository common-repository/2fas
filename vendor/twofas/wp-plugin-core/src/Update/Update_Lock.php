<?php

namespace TwoFAS\Core\Update;

use TwoFAS\Core\Environment_Interface;
use TwoFAS\Core\Exceptions\Download_Exception;

class Update_Lock {

	const WP_VERSION_WITH_NATIVE_UPDATE_LOCKING = '5.2';

	/**
	 * @var Environment_Interface
	 */
	private $environment;

	/**
	 * @var PHP_Requirement_Interface
	 */
	private $php_requirement;

	/**
	 * @param Environment_Interface     $environment
	 * @param PHP_Requirement_Interface $requirement
	 */
	public function __construct( Environment_Interface $environment, PHP_Requirement_Interface $requirement ) {
		$this->environment     = $environment;
		$this->php_requirement = $requirement;
	}

	/**
	 * @return bool
	 */
	public function is_locked() {
		try {
			$required_php_version = $this->php_requirement->get_required_php_version();
		} catch ( Download_Exception $e ) {
			return false;
		}

		return version_compare( $this->environment->get_php_version(), $required_php_version, '<' )
			&& version_compare( $this->environment->get_wordpress_version(), self::WP_VERSION_WITH_NATIVE_UPDATE_LOCKING, '<' );
	}
}
