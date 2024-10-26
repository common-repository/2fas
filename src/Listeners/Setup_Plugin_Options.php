<?php

namespace TwoFAS\TwoFAS\Listeners;

use TwoFAS\TwoFAS\Events\Integration_Was_Created;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\Options_Storage;
use TwoFAS\Account\Exception\Exception as Account_Exception;

class Setup_Plugin_Options extends Listener {

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
	 * @param Integration_Was_Created $event
	 *
	 * @throws Account_Exception
	 */
	public function handle( Integration_Was_Created $event ) {
		$client = $this->api_wrapper->get_client();

		// Set encryption key
		$this->options_storage->save_aes_key();

		// Set 2FA globally for WP users
		$this->options_storage->set_twofas_email( $client->getEmail() );
		$this->options_storage->enable_plugin();
		$this->options_storage->set_basic_plan();

		//Accept privacy policy
		$this->options_storage->accept_privacy_policy();
	}
}
