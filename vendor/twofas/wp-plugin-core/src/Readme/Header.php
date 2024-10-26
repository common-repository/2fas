<?php

namespace TwoFAS\Core\Readme;

use TwoFAS\Core\Exceptions\Download_Exception;
use TwoFAS\Core\Update\PHP_Requirement_Interface;

class Header implements PHP_Requirement_Interface {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @param Container $readme_container
	 */
	public function __construct( Container $readme_container ) {
		$this->container = $readme_container;
	}

	/**
	 * @return string
	 *
	 * @throws Download_Exception
	 */
	public function get_required_php_version() {
		$header = $this->get_header_section();
		preg_match( '/^Requires PHP: .+$/m', $header, $matches );

		return substr( $matches[0], strlen( 'Requires PHP: ' ) );
	}

	/**
	 * @return string
	 *
	 * @throws Download_Exception
	 */
	private function get_header_section() {
		$readme   = $this->container->get();
		$sections = $readme->get_sections();

		return array_shift( $sections );
	}
}
