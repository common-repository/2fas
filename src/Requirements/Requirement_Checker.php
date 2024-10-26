<?php

namespace TwoFAS\TwoFAS\Requirements;

class Requirement_Checker {

	/**
	 * @var Requirement[]
	 */
	private $requirements = [];

	/**
	 * @var array
	 */
	private $not_satisfied = [];

	/**
	 * @param Requirement $requirement
	 *
	 * @return $this
	 */
	public function add_requirement( Requirement $requirement ) {
		$this->requirements[] = $requirement;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function are_satisfied() {
		$this->check_requirements();

		return empty( $this->not_satisfied );
	}

	/**
	 * @return array
	 */
	public function get_not_satisfied() {
		return $this->not_satisfied;
	}

	private function check_requirements() {
		foreach ( $this->requirements as $requirement ) {
			if ( ! $requirement->is_satisfied() ) {
				$this->not_satisfied[] = $requirement->get_message();
			}
		}
	}
}
