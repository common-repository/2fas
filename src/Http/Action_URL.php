<?php

namespace TwoFAS\TwoFAS\Http;

use TwoFAS\Core\Http\URL_Interface;

class Action_URL implements URL_Interface {

	/**
	 * @var string
	 */
	private $page;

	/**
	 * @var string
	 */
	private $action;

	/**
	 * @param string $page
	 * @param string $action
	 */
	public function __construct( $page, $action = '' ) {
		$this->page   = $page;
		$this->action = $action;
	}

	/**
	 * @return string
	 */
	public function get() {
		return $this->create_url();
	}

	/**
	 * @return string
	 */
	private function create_url() {
		$url           = get_admin_url();
		$url           .= 'admin.php?';
		$query         = [];
		$query['page'] = $this->page;

		if ( ! empty( $this->action ) ) {
			$query['twofas-action'] = $this->action;
		}

		$url .= http_build_query( $query );

		return $url;
	}
}
