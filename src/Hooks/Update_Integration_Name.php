<?php

namespace TwoFAS\TwoFAS\Hooks;

use Exception;
use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;
use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\TwoFAS\Helpers\Scheduler;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Integration\Integration_Name;
use TwoFAS\TwoFAS\Storage\OAuth_Storage;

class Update_Integration_Name implements Hook_Interface {

	/**
	 * @var OAuth_Storage
	 */
	private $oauth_storage;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Integration_Name
	 */
	private $integration_name;

	/**
	 * @var Error_Handler_Interface
	 */
	private $error_handler;

	/**
	 * @var Scheduler
	 */
	private $scheduler;

	/**
	 * @param OAuth_Storage           $oauth_storage
	 * @param API_Wrapper             $api_wrapper
	 * @param Integration_Name        $integration_name
	 * @param Error_Handler_Interface $error_handler
	 * @param Scheduler               $scheduler
	 */
	public function __construct(
		OAuth_Storage $oauth_storage,
		API_Wrapper $api_wrapper,
		Integration_Name $integration_name,
		Error_Handler_Interface $error_handler,
		Scheduler $scheduler
	) {
		$this->oauth_storage    = $oauth_storage;
		$this->api_wrapper      = $api_wrapper;
		$this->integration_name = $integration_name;
		$this->error_handler    = $error_handler;
		$this->scheduler        = $scheduler;
	}

	public function register_hook() {
		$hook = 'twofas_update_integration_name';
		$this->scheduler->weekly( $hook );
		add_action( $hook, [ $this, 'update' ] );
	}

	public function update() {
		try {
			$token          = $this->oauth_storage->retrieveToken( TokenType::WORDPRESS );
			$integration_id = $token->getIntegrationId();
			$integration    = $this->api_wrapper->get_integration( $integration_id );
			$expected_name  = $this->integration_name->get_name();
			$current_name   = $integration->getName();

			if ( $current_name !== $expected_name ) {
				$integration->setName( $expected_name );
				$this->api_wrapper->update_integration( $integration );
			}
		} catch ( Exception $e ) {
			$this->error_handler->capture_exception( $e );
		}
	}
}
