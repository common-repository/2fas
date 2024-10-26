<?php

namespace TwoFAS\Core\Http;

class JSON_Response {

	/**
	 * @var array
	 */
	private $body;

	/**
	 * @var int
	 */
	private $status_code;

	/**
	 * @param array $body
	 * @param int   $status_code
	 */
	public function __construct( array $body, $status_code ) {
		$this->body        = $body;
		$this->status_code = $status_code;
	}

	/**
	 * @return array
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * @return int
	 */
	public function get_status_code() {
		return $this->status_code;
	}

	public function send_json() {
		status_header( $this->status_code );
		wp_send_json( $this->body );
	}
}
