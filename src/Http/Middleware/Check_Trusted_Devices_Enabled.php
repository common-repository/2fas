<?php

namespace TwoFAS\TwoFAS\Http\Middleware;

use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\TwoFAS\Notifications\Notification;
use TwoFAS\TwoFAS\Storage\Options_Storage;

class Check_Trusted_Devices_Enabled extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @param Request         $request
	 * @param Flash           $flash
	 * @param Options_Storage $options_storage
	 */
	public function __construct( Request $request, Flash $flash, Options_Storage $options_storage ) {
		$this->request         = $request;
		$this->flash           = $flash;
		$this->options_storage = $options_storage;
	}

	/**
	 * @inheritDoc
	 */
	public function handle() {
		if ( ! $this->options_storage->are_trusted_devices_enabled() ) {
			if ( $this->request->is_ajax() ) {
				return $this->json( [ 'error' => Notification::get( 'trusted-devices-disabled' ), ], 403 );
			}

			$this->flash->add_message( 'error', 'trusted-devices-disabled' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
		}

		return $this->next->handle();
	}
}
