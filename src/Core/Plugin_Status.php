<?php

namespace TwoFAS\TwoFAS\Core;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\TwoFAS\Storage\OAuth_Storage;
use TwoFAS\TwoFAS\Storage\Options_Storage;

class Plugin_Status {

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @var OAuth_Storage
	 */
	private $oauth_storage;

	/**
	 * @param Options_Storage $options_storage
	 * @param OAuth_Storage   $oauth_storage
	 */
	public function __construct( Options_Storage $options_storage, OAuth_Storage $oauth_storage ) {
		$this->options_storage = $options_storage;
		$this->oauth_storage   = $oauth_storage;
	}

	/**
	 * @return bool
	 */
	public function is_plugin_enabled() {
		return $this->options_storage->is_plugin_enabled();
	}

	/**
	 * @return bool
	 */
	public function client_completed_registration() {
		try {
			$this->oauth_storage->retrieveToken( TokenType::WORDPRESS );

			return $this->options_storage->get_twofas_email()
				&& $this->options_storage->get_twofas_encryption_key();
		} catch ( TokenNotFoundException $e ) {
			return false;
		}
	}
}
