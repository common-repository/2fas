<?php

namespace TwoFAS\TwoFAS\Update;

use RuntimeException;
use TwoFAS\TwoFAS\Storage\OAuth_Storage;
use TwoFAS\TwoFAS\Storage\Options_Storage;

class Plugin_Version {

	/**
	 * @var Options_Storage
	 */
	private $options;

	/**
	 * @param Options_Storage $options
	 */
	public function __construct( Options_Storage $options ) {
		$this->options = $options;
	}

	/**
	 * @return string
	 *
	 * @throws RuntimeException
	 */
	public function get_db_version() {
		$version = $this->options->get_twofas_plugin_version();

		if ( $version ) {
			return $version;
		}

		if ( $this->is_fresh_installation() ) {
			return '0';
		}

		if ( $this->is_initial_release() ) {
			return '1.0.0';
		}

		throw new RuntimeException( 'inconsistent-data' );
	}

	/**
	 * @return string
	 */
	public function get_source_code_version() {
		return TWOFAS_PLUGIN_VERSION;
	}

	public function update_db_version() {
		$this->options->set_twofas_plugin_version( $this->get_source_code_version() );
	}

	/**
	 * @return bool
	 */
	private function is_fresh_installation() {
		$not_allowed_options = [
			Options_Storage::TWOFAS_EMAIL,
			Options_Storage::TWOFAS_ENCRYPTION_KEY,
			Options_Storage::TWOFAS_PLUGIN_STATUS,
			Options_Storage::TWOFAS_PLAN_KEY,
			Options_Storage::TWOFAS_PLUGIN_VERSION,
			OAuth_Storage::TWOFAS_OAUTH_TOKEN_SETUP_KEY,
			OAuth_Storage::TWOFAS_OAUTH_TOKEN_WORDPRESS_KEY,
		];

		foreach ( $not_allowed_options as $option ) {
			if ( false !== get_option( $option ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	private function is_initial_release() {
		$required_options = [
			Options_Storage::TWOFAS_EMAIL,
			Options_Storage::TWOFAS_PASSWORD,
			Options_Storage::TWOFAS_ENABLED,
		];

		foreach ( $required_options as $option ) {
			if ( false === get_option( $option ) ) {
				return false;
			}
		}

		return true;
	}
}
