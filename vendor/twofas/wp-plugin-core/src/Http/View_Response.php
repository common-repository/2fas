<?php

namespace TwoFAS\Core\Http;

class View_Response {

	/**
	 * @var string
	 */
	private $template;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @param string $template
	 * @param array  $data
	 */
	public function __construct( $template, array $data = array() ) {
		$this->template = $template;
		$this->data     = $data;
	}

	/**
	 * @return string
	 */
	public function get_template() {
		return $this->template;
	}

	/**
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}
}
