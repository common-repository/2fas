<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\TwoFAS\Http\Action_Index;

class Action_Links_Filter implements Hook_Interface {

	public function register_hook() {
		add_filter( 'plugin_action_links_' . TWOFAS_PLUGIN_BASENAME, [ $this, 'add_settings_link' ] );
	}

	/**
	 * @param array $links
	 *
	 * @return array
	 */
	public function add_settings_link( array $links ) {
		return array_merge( [
			'settings' => $this->create_link()
		], $links );
	}

	/**
	 * @return string
	 */
	private function create_link() {
		$url = admin_url( 'admin.php?page=' . Action_Index::SUBMENU_DASHBOARD );

		return '<a href="' . $url . '">Settings</a>';
	}
}
