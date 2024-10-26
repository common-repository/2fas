<?php

namespace TwoFAS\TwoFAS\Helpers;

use TwoFAS\Core\Environment_Interface;

class Environment implements Environment_Interface {

	/**
	 * @return string
	 */
	public function get_wordpress_version() {
		return get_bloginfo( 'version' );
	}

	/**
	 * @return string
	 */
	public function get_wordpress_app_name() {
		$name = get_bloginfo( 'name' );
		$name = empty( $name ) ? 'WordPress' : $name;
		$name = html_entity_decode( $name, ENT_QUOTES, 'UTF-8' );

		return substr( $name, 0, 255 );
	}

	/**
	 * @return string
	 */
	public function get_wordpress_app_url() {
		return get_site_url();
	}

	/**
	 * @return string
	 */
	public function get_php_version() {
		return PHP_VERSION;
	}
}
