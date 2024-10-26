<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Admin_Menu_Action as Base_Admin_Menu_Action;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Templates\Twig;
use TwoFAS\TwoFAS\User\Capabilities;

class Admin_Menu_Action extends Base_Admin_Menu_Action {

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @param Twig $twig
	 */
	public function __construct( Twig $twig ) {
		$this->twig = $twig;
	}

	public function add_menu() {
		add_menu_page(
			'2FAS &#8212; Dashboard',
			'2FAS Classic',
			Capabilities::ADMIN,
			Action_Index::SUBMENU_DASHBOARD,
			[ $this, 'render' ],
			$this->get_menu_icon()
		);

		add_submenu_page(
			Action_Index::SUBMENU_DASHBOARD,
			'2FAS &#8212; Dashboard',
			__( 'Dashboard', '2fas' ),
			Capabilities::ADMIN,
			Action_Index::SUBMENU_DASHBOARD,
			[ $this, 'render' ]
		);

		add_submenu_page(
			Action_Index::SUBMENU_DASHBOARD,
			'2FAS &#8212; Admin settings',
			__( 'Admin settings', '2fas' ),
			Capabilities::ADMIN,
			Action_Index::SUBMENU_SETTINGS,
			[ $this, 'render' ]
		);

		add_submenu_page(
			Action_Index::SUBMENU_DASHBOARD,
			'2FAS &#8212; Personal settings',
			__( 'Personal settings', '2fas' ),
			Capabilities::USER,
			Action_Index::SUBMENU_CHANNEL,
			[ $this, 'render' ]
		);
	}

	public function render() {
		echo $this->twig->get_view( $this->response->get_template(), $this->response->get_data() );
	}

	/**
	 * @return string
	 */
	private function get_menu_icon() {
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIyMHB4IiBoZWlnaHQ9IjM0cHgiIHZpZXdCb3g9IjAgMCA0Mi44NzUgNTEuNzYzIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAyMCAzNCIgZmlsbD0iI2EwYTVhYSIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PGc+PGRlZnM+PHJlY3QgaWQ9IlNWR0lEXzFfIiB4PSItMjIuODc1IiB5PSItMjIuNDc5IiB3aWR0aD0iMjM2LjYxNiIgaGVpZ2h0PSI5Ni44NTQiLz48L2RlZnM+PGNsaXBQYXRoIGlkPSJTVkdJRF8yXyI+PHVzZSB4bGluazpocmVmPSIjU1ZHSURfMV8iICBvdmVyZmxvdz0idmlzaWJsZSIvPjwvY2xpcFBhdGg+PHBhdGggY2xpcC1wYXRoPSJ1cmwoI1NWR0lEXzJfKSIgZD0iTTM4LjQ5NiwzLjk1MkwyNy4yODgsMC45NjNjLTMuODI4LTEuMDIxLTcuODU3LTEuMDIxLTExLjY4NiwwTDQuMzk0LDMuOTUyQzEuOTEyLDQuNjE0LDAuMTg1LDYuODYyLDAuMTg1LDkuNDN2MTkuNTkyYzAsNS43MTQsMi44NywxMS4wNDcsNy42MzgsMTQuMTk1bDEyLjI1Niw4LjA5YzAuOTU1LDAuNjMsMi4xOTYsMC42MjQsMy4xNDUtMC4wMTZsMTEuOTczLTguMDYyYzQuNjkzLTMuMTYsNy41MDgtOC40NDksNy41MDgtMTQuMTA3VjkuNDNDNDIuNzA1LDYuODYyLDQwLjk3OCw0LjYxNCwzOC40OTYsMy45NTIgTTExLjUyMywzNC40OWMtMS41NjYsMC0yLjgzNS0xLjI2OS0yLjgzNS0yLjgzNGMwLTEuNTY2LDEuMjY5LTIuODM1LDIuODM1LTIuODM1YzEuNTY1LDAsMi44MzUsMS4yNjksMi44MzUsMi44MzVDMTQuMzU4LDMzLjIyMiwxMy4wODgsMzQuNDksMTEuNTIzLDM0LjQ5IE0xOC42MDksMjUuOTg3aC03LjA4NmMtMS41NjYsMC0yLjgzNS0xLjI3LTIuODM1LTIuODM1YzAtMS41NjYsMS4yNjktMi44MzUsMi44MzUtMi44MzVoNy4wODZjMS41NjYsMCwyLjgzNSwxLjI2OSwyLjgzNSwyLjgzNUMyMS40NDUsMjQuNzE3LDIwLjE3NiwyNS45ODcsMTguNjA5LDI1Ljk4NyBNMzQuMjAxLDE0LjY0OGMwLDEuNTY1LTEuMjY5LDIuODM1LTIuODM1LDIuODM1SDExLjUyM2MtMS41NjYsMC0yLjgzNS0xLjI3LTIuODM1LTIuODM1YzAtMS41NjYsMS4yNjktMi44MzUsMi44MzUtMi44MzVoMTkuODQyQzMyLjkzMiwxMS44MTMsMzQuMjAxLDEzLjA4MywzNC4yMDEsMTQuNjQ4eiIvPjwvZz48L3N2Zz4=';
	}
}
