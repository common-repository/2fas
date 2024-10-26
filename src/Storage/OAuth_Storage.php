<?php

namespace TwoFAS\TwoFAS\Storage;

use TwoFAS\Account\OAuth\Interfaces\TokenStorage;
use TwoFAS\Account\OAuth\Token;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Account\OAuth\TokenType;

class OAuth_Storage implements TokenStorage {

	const TWOFAS_OAUTH_TOKEN_BASE          = 'twofas_oauth_token_';
	const TWOFAS_OAUTH_TOKEN_SETUP_KEY     = 'twofas_oauth_token_setup';
	const TWOFAS_OAUTH_TOKEN_WORDPRESS_KEY = 'twofas_oauth_token_wordpress';
	const ACCESS_TOKEN_KEY                 = 'access_token';
	const INTEGRATION_ID_KEY               = 'integration_id';

	/**
	 * @inheritDoc
	 */
	public function retrieveToken( $type ) {
		$tokenArray = get_option( $this->get_meta_name( $type ) );

		if ( is_array( $tokenArray ) ) {
			return new Token( $type, $tokenArray[ self::ACCESS_TOKEN_KEY ], $tokenArray[ self::INTEGRATION_ID_KEY ] );
		}

		throw new TokenNotFoundException;
	}

	/**
	 * @inheritDoc
	 */
	public function storeToken( Token $token ) {
		update_option( $this->get_meta_name( $token->getType() ), [
			self::ACCESS_TOKEN_KEY   => $token->getAccessToken(),
			self::INTEGRATION_ID_KEY => $token->getIntegrationId(),
		] );
	}

	/**
	 * @return int
	 *
	 * @throws TokenNotFoundException
	 */
	public function get_integration_id() {
		$token = $this->retrieveToken( TokenType::WORDPRESS );

		return $token->getIntegrationId();
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	private function get_meta_name( $type ) {
		return self::TWOFAS_OAUTH_TOKEN_BASE . $type;
	}
}
