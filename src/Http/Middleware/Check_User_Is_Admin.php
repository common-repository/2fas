<?php

namespace TwoFAS\TwoFAS\Http\Middleware;

use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Templates\Views;
use TwoFAS\TwoFAS\User\Capabilities;

class Check_User_Is_Admin extends Middleware {

	/**
	 * @return View_Response
	 */
	public function handle() {
		if ( ! current_user_can( Capabilities::ADMIN ) ) {
			return $this->view( Views::FORBIDDEN, [
					'description' => 'You do not have sufficient permissions to perform this action.'
				]
			);
		}

		return $this->next->handle();
	}
}
