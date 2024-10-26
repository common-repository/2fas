<?php

namespace TwoFAS\TwoFAS\Factories;

use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\Account\Sdk as Account;

class Account_Factory extends SDK_Factory {

	/**
	 * @return Account
	 */
	public function create() {
		$headers       = $this->get_headers();
		$account       = new Account( $this->oauth_storage, TokenType::wordpress(), $headers );

		$account->setBaseUrl( $this->config->get_account_url() );

		return $account;
	}
}
