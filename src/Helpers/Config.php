<?php

namespace TwoFAS\TwoFAS\Helpers;

use RuntimeException;

class Config {

	const FILENAME = 'config.php';

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @param string $path
	 *
	 * @throws RuntimeException
	 */
	public function __construct( $path ) {
		$path = $this->create_full_path( $path );

		if ( ! file_exists( $path ) ) {
			throw new RuntimeException( 'Configuration file has not been found.' );
		}

		$this->config = include $path;
	}

	/**
	 * @return string
	 */
	public function get_api_url() {
		return $this->config['api_url'];
	}

	/**
	 * @return string
	 */
	public function get_account_url() {
		return $this->config['account_url'];
	}

	/**
	 * @return string
	 */
	public function get_dashboard_url() {
		return $this->config['dashboard_url'];
	}

	/**
	 * @return string
	 */
	public function get_pusher_key() {
		return $this->config['pusher_key'];
	}

	/**
	 * @return string
	 */
	public function get_readme_url() {
		return $this->config['readme_url'];
	}

	/**
	 * @return string
	 */
	public function get_sentry_dsn() {
		return $this->config['sentry_dsn'];
	}

	/**
	 * @return array
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	private function create_full_path( $path ) {
		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '/\/$/', '', $path );
		$path .= '/';
		$path .= self::FILENAME;

		return $path;
	}
}
