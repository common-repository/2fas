<?php

namespace TwoFAS\TwoFAS\Factories;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\Api\Sdk as API;

class API_Factory extends SDK_Factory {

	/**
	 * @return API
	 *
	 * @throws TokenNotFoundException
	 */
	public function create() {
		$token   = $this->oauth_storage->retrieveToken( TokenType::WORDPRESS )->getAccessToken();
		$headers = $this->get_headers();
		$api     = new API( $token, $headers );

		$api->setBaseUrl( $this->config->get_api_url() );

		return $api;
	}
}
