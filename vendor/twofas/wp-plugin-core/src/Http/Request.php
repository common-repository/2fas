<?php

namespace TwoFAS\Core\Http;

abstract class Request {

	/**
	 * @var array
	 */
	protected $get;

	/**
	 * @var array
	 */
	protected $post;

	/**
	 * @var array
	 */
	protected $server;

	/**
	 * @var Cookie
	 */
	protected $cookie;

	/**
	 * @param array  $get
	 * @param array  $post
	 * @param array  $server
	 * @param Cookie $cookie
	 */
	public function __construct( array $get, array $post, array $server, Cookie $cookie ) {
		$this->get    = $get;
		$this->post   = $post;
		$this->server = $server;
		$this->cookie = $cookie;
	}

	/**
	 * @return string
	 */
	abstract public function action();

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has( $name ) {
		if ( array_key_exists( $name, $this->get ) ) {
			return true;
		} elseif ( array_key_exists( $name, $this->post ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $name
	 *
	 * @return array|string|null
	 */
	public function get( $name ) {
		if ( isset( $this->get[ $name ] ) ) {
			return $this->get[ $name ];
		}

		return null;
	}

	/**
	 * @param string $name
	 *
	 * @return array|string|null
	 */
	public function post( $name ) {
		if ( isset( $this->post[ $name ] ) ) {
			return $this->post[ $name ];
		}

		return null;
	}

	/**
	 * @param string $name
	 *
	 * @return string|null
	 */
	public function header( $name ) {
		if ( isset( $this->server[ $name ] ) ) {
			return $this->server[ $name ];
		}

		return null;
	}

	/**
	 * @return Cookie
	 */
	public function cookie() {
		return $this->cookie;
	}

	/**
	 * @param string $name
	 *
	 * @return array|string|null
	 */
	public function request( $name ) {
		$request = array_merge( $this->get, $this->post, $this->cookie->get_cookies() );

		if ( isset( $request[ $name ] ) ) {
			return $request[ $name ];
		}

		return null;
	}

	/**
	 * @return string
	 */
	public function http_method() {
		return strtoupper( $this->header( 'REQUEST_METHOD' ) );
	}

	/**
	 * @return bool
	 */
	public function is_ajax() {
		return 'xmlhttprequest' === strtolower( $this->header( 'HTTP_X_REQUESTED_WITH' ) );
	}

	/**
	 * @return string
	 */
	public function page() {
		$page = $this->get( 'page' );

		if ( is_string( $page ) ) {
			return $page;
		}

		return '';
	}

	/**
	 * @return bool
	 */
	public function is_post() {
		return 'POST' === $this->http_method();
	}

	/**
	 * @return bool
	 */
	public function is_plugins_page() {
		$request_uri = $this->header( 'REQUEST_URI' );

		return false !== strpos( $request_uri, 'plugins.php' );
	}

	/**
	 * @return bool
	 */
	public function is_plugin_search_page() {
		$request_uri = $this->header( 'REQUEST_URI' );

		return false !== strpos( $request_uri, 'plugin-install.php' );
	}
}
