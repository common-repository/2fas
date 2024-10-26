<?php

namespace TwoFAS\TwoFAS\Http\Controllers\User;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Http\Controllers\Controller;
use TwoFAS\Core\Http\Request;

class Modal_Controller extends Controller {

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function disable_reload( Request $request ) {
		$this->storage->get_user_storage()->disable_reload_modal();

		return $this->json( [], 204 );
	}
}
