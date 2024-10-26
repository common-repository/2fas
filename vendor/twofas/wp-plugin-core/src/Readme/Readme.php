<?php

namespace TwoFAS\Core\Readme;

class Readme {

	/**
	 * @var array
	 */
	private $sections;

	/**
	 * @param array $sections
	 */
	public function __construct( array $sections ) {
		$this->sections = $sections;
	}

	/**
	 * @return array
	 */
	public function get_sections() {
		return $this->sections;
	}

	/**
	 * @param string $name
	 *
	 * @return null|string
	 */
	public function get_section( $name ) {
		if ( array_key_exists( $name, $this->sections ) ) {
			return $this->sections[ $name ];
		}

		return null;
	}
}
