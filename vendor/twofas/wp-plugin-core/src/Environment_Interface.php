<?php

namespace TwoFAS\Core;

interface Environment_Interface {

	/**
	 * @return string
	 */
	public function get_php_version();

	/**
	 * @return string
	 */
	public function get_wordpress_version();
}
