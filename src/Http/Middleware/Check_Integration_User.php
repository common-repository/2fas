<?php

namespace TwoFAS\TwoFAS\Http\Middleware;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Integration\Integration_User;

class Check_Integration_User extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Integration_User
	 */
	private $integration_user;

	/**
	 * @param Request          $request
	 * @param Integration_User $integration_user
	 */
	public function __construct( Request $request, Integration_User $integration_user ) {
		$this->request          = $request;
		$this->integration_user = $integration_user;
	}

	/**
	 * @return JSON_Response|Redirect_Response|View_Response
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function handle() {
		if ( ! $this->integration_user->exists() ) {
			if ( $this->request->is_ajax() ) {
				return $this->json( [
					'error' => 'Integration user not found.',
				], 403 );
			}

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
		}

		return $this->next->handle();
	}
}
