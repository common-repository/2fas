<?php

namespace TwoFAS\TwoFAS\Listeners;

use TwoFAS\Core\Events\View_Response_Created;
use TwoFAS\Core\Hooks\Admin_Menu_Action;

class Add_View_Response extends Listener {

	/**
	 * @var Admin_Menu_Action
	 */
	private $admin_menu_action;

	/**
	 * @param Admin_Menu_Action $admin_menu_action
	 */
	public function __construct( Admin_Menu_Action $admin_menu_action ) {
		$this->admin_menu_action = $admin_menu_action;
	}

	/**
	 * @param View_Response_Created $event
	 */
	public function handle( View_Response_Created $event ) {
		$this->admin_menu_action->set_response( $event->get_response() );
	}

}
