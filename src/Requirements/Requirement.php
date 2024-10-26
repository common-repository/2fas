<?php

namespace TwoFAS\TwoFAS\Requirements;

abstract class Requirement {

	/**
	 * @var bool
	 */
	protected $is_satisfied;

	/**
	 * @return bool
	 */
	abstract public function is_satisfied();

	/**
	 * @return string
	 */
	abstract public function get_message();
}
