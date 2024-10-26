<?php

namespace TwoFAS\TwoFAS\Core;

use Exception;
use TwoFAS\Core\Factories\Response_Factory;
use TwoFAS\Core\Hooks\Hook_Handler;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\Request;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Notifications\Status_Notifier;
use TwoFAS\TwoFAS\Update\Updater;

class Plugin {

	/**
	 * @var Response_Factory
	 */
	private $response_factory;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Hook_Handler
	 */
	private $hook_handler;

	/**
	 * @var Updater
	 */
	private $updater;

	/**
	 * @var Status_Notifier
	 */
	private $notifier;

	/**
	 * @param Response_Factory $response_factory
	 * @param Request          $request
	 * @param Hook_Handler     $hook_handler
	 * @param Updater          $updater
	 * @param Status_Notifier  $notifier
	 */
	public function __construct(
		Response_Factory $response_factory,
		Request $request,
		Hook_Handler $hook_handler,
		Updater $updater,
		Status_Notifier $notifier
	) {
		$this->response_factory = $response_factory;
		$this->request          = $request;
		$this->hook_handler     = $hook_handler;
		$this->updater          = $updater;
		$this->notifier         = $notifier;
	}

	public function run() {
		try {
			if ( $this->updater->should_plugin_be_updated() ) {
				$this->updater->update_plugin();
			}

			$this->notifier->show();

			$response = $this->response_factory->create_response( $this->request );
		} catch ( Exception $e ) {
			$response = $this->response_factory->create_error_response( $e );
		}

		if ( $response instanceof JSON_Response ) {
			$response->send_json();
		}

		if ( $response instanceof Redirect_Response ) {
			$response->redirect();
		}

		if ( $response instanceof View_Response ) {
			$this->hook_handler->register_hooks();
		}
	}
}
