<?php

namespace TwoFAS\TwoFAS\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Notifications\Notification;
use TwoFAS\TwoFAS\Storage\User_Storage;

class Check_Second_Factor_Enabled extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @param Request      $request
	 * @param Flash        $flash
	 * @param User_Storage $user_storage
	 */
	public function __construct( Request $request, Flash $flash, User_Storage $user_storage ) {
		$this->request      = $request;
		$this->flash        = $flash;
		$this->user_storage = $user_storage;
	}

	/**
	 * @return JSON_Response|Redirect_Response|View_Response
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function handle() {
		if ( ! $this->user_storage->is_2fa_enabled() ) {
			if ( $this->request->is_ajax() ) {
				return $this->json( [
					'error' => Notification::get( 'second-factor-status-disabled' ),
				], 403 );
			}

			$this->flash->add_message( 'error', 'second-factor-status-disabled' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
		}

		return $this->next->handle();
	}
}
