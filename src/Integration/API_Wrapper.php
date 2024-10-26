<?php

namespace TwoFAS\TwoFAS\Integration;

use InvalidArgumentException;
use TwoFAS\Account\Card;
use TwoFAS\Account\Client;
use TwoFAS\Account\Exception\AuthorizationException as Account_Authorization_Exception;
use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\Exception\PasswordResetAttemptsRemainingIsReachedException;
use TwoFAS\Account\Exception\ValidationException as Account_Validation_Exception;
use TwoFAS\Account\Integration;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Account\Sdk as Account;
use TwoFAS\Api\Authentication;
use TwoFAS\Api\BackupCodesCollection;
use TwoFAS\Api\Code\Code;
use TwoFAS\Api\Exception\AuthorizationException as API_Authorization_Exception;
use TwoFAS\Api\Exception\CountryIsBlockedException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\IntegrationUserNotFoundException;
use TwoFAS\Api\Exception\InvalidDateException;
use TwoFAS\Api\Exception\InvalidNumberException;
use TwoFAS\Api\Exception\PaymentException;
use TwoFAS\Api\Exception\ValidationException as API_Validation_Exception;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\Sdk as API;
use TwoFAS\TwoFAS\Factories\Account_Factory;
use TwoFAS\TwoFAS\Factories\API_Factory;
use TwoFAS\TwoFAS\Storage\Options_Storage;

class API_Wrapper {

	/**
	 * @var Account_Factory
	 */
	private $account_factory;

	/**
	 * @var API_Factory
	 */
	private $api_factory;

	/**
	 * @var Account
	 */
	private $account;

	/**
	 * @var API
	 */
	private $api;

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @var Integration_Name
	 */
	private $integration_name;

	/**
	 * @param Account_Factory  $account_factory
	 * @param API_Factory      $api_factory
	 * @param Options_Storage  $options_storage
	 * @param Integration_Name $integration_name
	 */
	public function __construct(
		Account_Factory $account_factory,
		API_Factory $api_factory,
		Options_Storage $options_storage,
		Integration_Name $integration_name
	) {
		$this->account_factory  = $account_factory;
		$this->api_factory      = $api_factory;
		$this->options_storage  = $options_storage;
		$this->integration_name = $integration_name;
	}

	/**
	 * @return API
	 *
	 * @throws TokenNotFoundException
	 */
	private function api() {
		if ( ! $this->api ) {
			$this->api = $this->api_factory->create();
		}

		return $this->api;
	}

	/**
	 * @return Account
	 */
	private function account() {
		if ( ! $this->account ) {
			$this->account = $this->account_factory->create();
		}

		return $this->account;
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param string $password_confirmation
	 *
	 * @return Client
	 *
	 * @throws Account_Validation_Exception
	 * @throws Account_Exception
	 */
	public function create_client( $email, $password, $password_confirmation ) {
		return $this->account()->createClient( $email, $password, $password_confirmation, 'wordpress' );
	}

	/**
	 * @return Integration
	 *
	 * @throws Account_Validation_Exception
	 * @throws Account_Exception
	 */
	public function create_integration() {
		return $this->account()->createIntegration( $this->integration_name->get_name() );
	}

	/**
	 * @param $email
	 *
	 * @throws Account_Exception
	 * @throws PasswordResetAttemptsRemainingIsReachedException
	 */
	public function reset_password( $email ) {
		$this->account()->resetPassword( $email );
	}

	/**
	 * @return Client
	 *
	 * @throws Account_Exception
	 */
	public function get_client() {
		return $this->account()->getClient();
	}

	/**
	 * @param int $integration_id
	 *
	 * @return Integration
	 *
	 * @throws NotFoundException
	 * @throws Account_Exception
	 */
	public function get_integration( $integration_id ) {
		return $this->account()->getIntegration( $integration_id );
	}

	/**
	 * @param Integration $integration
	 *
	 * @throws Account_Validation_Exception
	 * @throws Account_Exception
	 */
	public function update_integration( Integration $integration ) {
		$this->account()->updateIntegration( $integration );
	}

	/**
	 * @param int $integration_id
	 *
	 * @throws Account_Exception
	 * @throws NotFoundException
	 */
	public function delete_integration( $integration_id ) {
		$this->account()->deleteIntegration( $this->get_integration( $integration_id ) );
	}

	/**
	 * @param string $integration_id
	 *
	 * @return bool
	 *
	 * @throws Account_Exception
	 * @throws NotFoundException
	 */
	public function can_integration_upgrade( $integration_id ) {
		return $this->account()->canIntegrationUpgrade( $integration_id );
	}

	/**
	 * @param string $integration_id
	 *
	 * @throws Account_Exception
	 * @throws NotFoundException
	 */
	public function upgrade_integration( $integration_id ) {
		$this->account()->upgradeIntegration( $integration_id );
	}

	/**
	 * @param Client $client
	 *
	 * @return Card
	 *
	 * @throws Account_Exception
	 * @throws NotFoundException
	 */
	public function get_primary_card( Client $client ) {
		return $this->account()->getPrimaryCard( $client );
	}

	/**
	 * @param int $user_id
	 *
	 * @return IntegrationUser
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Validation_Exception
	 * @throws API_Authorization_Exception
	 * @throws API_Exception
	 */
	public function create_integration_user( $user_id ) {
		$integration_user = new IntegrationUser();
		$integration_user->setExternalId( (string) $user_id );

		return $this->api()->addIntegrationUser( $this->options_storage, $integration_user );
	}

	/**
	 * @param int $user_id
	 *
	 * @return IntegrationUser|null
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Authorization_Exception
	 * @throws API_Exception
	 */
	public function get_integration_user_by_external_id( $user_id ) {
		try {
			return $this->api()->getIntegrationUserByExternalId( $this->options_storage, (string) $user_id );
		} catch ( IntegrationUserNotFoundException $e ) {
			return null;
		}
	}

	/**
	 * @param IntegrationUser $integration_user
	 *
	 * @return IntegrationUser
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Validation_Exception
	 * @throws API_Authorization_Exception
	 * @throws API_Exception
	 */
	public function update_integration_user( IntegrationUser $integration_user ) {
		return $this->api()->updateIntegrationUser( $this->options_storage, $integration_user );
	}

	/**
	 * @param string $totp_secret
	 *
	 * @return Authentication
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Authorization_Exception
	 * @throws InvalidDateException
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	public function request_auth_via_totp( $totp_secret ) {
		return $this->api()->requestAuthViaTotp( $totp_secret );
	}

	/**
	 * @param string $phone_number
	 *
	 * @return Authentication
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Authorization_Exception
	 * @throws CountryIsBlockedException
	 * @throws InvalidDateException
	 * @throws InvalidNumberException
	 * @throws PaymentException
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	public function request_auth_via_sms( $phone_number ) {
		return $this->api()->requestAuthViaSms( $phone_number );
	}

	/**
	 * @param string $phone_number
	 *
	 * @return Authentication
	 *
	 * @throws TokenNotFoundException
	 * @throws CountryIsBlockedException
	 * @throws InvalidDateException
	 * @throws InvalidNumberException
	 * @throws PaymentException
	 * @throws API_Authorization_Exception
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	public function request_auth_via_call( $phone_number ) {
		return $this->api()->requestAuthViaCall( $phone_number );
	}

	/**
	 * @param array  $authentications
	 * @param string $code
	 *
	 * @return Code
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Authorization_Exception
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	public function check_code( array $authentications, $code ) {
		return $this->api()->checkCode( $authentications, $code );
	}

	/**
	 * @param IntegrationUser $integration_user
	 * @param array           $authentications
	 * @param string          $code
	 *
	 * @return Code
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	public function check_backup_code( IntegrationUser $integration_user, array $authentications, $code ) {
		return $this->api()->checkBackupCode( $integration_user, $authentications, $code );
	}

	/**
	 * @param IntegrationUser $integration_user
	 *
	 * @return BackupCodesCollection
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Authorization_Exception
	 * @throws API_Exception
	 */
	public function regenerate_backup_codes( IntegrationUser $integration_user ) {
		return $this->api()->regenerateBackupCodes( $integration_user );
	}

	/**
	 * @param string $email
	 * @param string $password
	 *
	 * @throws Account_Authorization_Exception
	 * @throws Account_Exception
	 * @throws Account_Validation_Exception
	 */
	public function generate_oauth_setup_token( $email, $password ) {
		$this->account()->generateOAuthSetupToken( $email, $password );
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param int    $integration_id
	 *
	 * @throws Account_Authorization_Exception
	 * @throws Account_Exception
	 * @throws Account_Validation_Exception
	 */
	public function generate_integration_specific_token( $email, $password, $integration_id ) {
		$this->account()->generateIntegrationSpecificToken( $email, $password, $integration_id );
	}
}
