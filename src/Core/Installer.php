<?php

namespace TwoFAS\TwoFAS\Core;

use TwoFAS\Account\Exception\AuthorizationException as Account_Authorization_Exception;
use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\ValidationException as Account_Validation_Exception;
use TwoFAS\TwoFAS\Events\Integration_Was_Created;
use TwoFAS\Core\Helpers\Dispatcher;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\Options_Storage;

class Installer {

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @param API_Wrapper     $api_wrapper
	 * @param Options_Storage $options_storage
	 */
	public function __construct( API_Wrapper $api_wrapper, Options_Storage $options_storage ) {
		$this->api_wrapper     = $api_wrapper;
		$this->options_storage = $options_storage;
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param string $password_confirmation
	 *
	 * @throws Account_Validation_Exception
	 * @throws Account_Authorization_Exception
	 * @throws Account_Exception
	 */
	public function create_account( $email, $password, $password_confirmation ) {
		$this->api_wrapper->create_client( $email, $password, $password_confirmation );

		$this->create_integration( $email, $password );
	}

	/**
	 * @param string $email
	 * @param string $password
	 *
	 * @throws Account_Validation_Exception
	 * @throws Account_Authorization_Exception
	 * @throws Account_Exception
	 */
	public function create_integration( $email, $password ) {
		$this->api_wrapper->generate_oauth_setup_token( $email, $password );
		$integration = $this->api_wrapper->create_integration();
		$this->api_wrapper->generate_integration_specific_token( $email, $password, $integration->getId() );

		Dispatcher::dispatch( new Integration_Was_Created( $integration ) );
	}
}
