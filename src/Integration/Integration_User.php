<?php

namespace TwoFAS\TwoFAS\Integration;

use InvalidArgumentException;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\FormattedNumber;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Storage\User_Storage;

class Integration_User {

	/**
	 * @var IntegrationUser
	 */
	private $integration_user;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var bool
	 */
	private $is_initialized = false;

	/**
	 * @param API_Wrapper  $api_wrapper
	 * @param User_Storage $user_storage
	 */
	public function __construct( API_Wrapper $api_wrapper, User_Storage $user_storage ) {
		$this->integration_user = new IntegrationUser();
		$this->api_wrapper      = $api_wrapper;
		$this->user_storage     = $user_storage;
	}

	/**
	 * @return bool
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function exists() {
		$this->initialize();

		if ( is_null( $this->integration_user->getId() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @return string|null
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function get_id() {
		$this->initialize();

		return $this->integration_user->getId();
	}

	/**
	 * @param string $id
	 *
	 * @return Integration_User
	 *
	 * @throws TokenNotFoundException
	 * @throws InvalidArgumentException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function set_id( $id ) {
		$this->initialize();

		$this->integration_user->setId( $id );

		return $this;
	}

	/**
	 * @return null|string
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function get_external_id() {
		$this->initialize();

		return $this->integration_user->getExternalId();
	}

	/**
	 * @param null|string $external_id
	 *
	 * @return Integration_User
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function set_external_id( $external_id ) {
		$this->initialize();

		$this->integration_user->setExternalId( $external_id );

		return $this;
	}

	/**
	 * @return FormattedNumber
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function get_phone_number() {
		$this->initialize();

		return $this->integration_user->getPhoneNumber();
	}

	/**
	 * @param null|string $phone_number
	 *
	 * @return Integration_User
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function set_phone_number( $phone_number ) {
		$this->initialize();

		$this->integration_user->setPhoneNumber( $phone_number );

		return $this;
	}

	/**
	 * @return null|string
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function get_email() {
		$this->initialize();

		return $this->integration_user->getEmail();
	}

	/**
	 * @param null|string $email
	 *
	 * @return Integration_User
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function set_email( $email ) {
		$this->initialize();

		$this->integration_user->setEmail( $email );

		return $this;
	}

	/**
	 * @return null|string
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function get_totp_secret() {
		$this->initialize();

		return $this->integration_user->getTotpSecret();
	}

	/**
	 * @param null|string $totp_secret
	 *
	 * @return Integration_User
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function set_totp_secret( $totp_secret ) {
		$this->initialize();

		$this->integration_user->setTotpSecret( $totp_secret );

		return $this;
	}

	/**
	 * @return int
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function get_backup_codes_count() {
		$this->initialize();

		return $this->integration_user->getBackupCodesCount();
	}

	/**
	 * @param int $backup_codes_count
	 *
	 * @return Integration_User
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function set_backup_codes_count( $backup_codes_count ) {
		$this->initialize();

		$this->integration_user->setBackupCodesCount( $backup_codes_count );

		return $this;
	}

	/**
	 * @return IntegrationUser
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function get_user() {
		$this->initialize();

		return $this->integration_user;
	}

	/**
	 * @param IntegrationUser $integration_user
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	public function set_user( IntegrationUser $integration_user ) {
		$this->initialize();

		$this
			->set_id( $integration_user->getId() )
			->set_external_id( $integration_user->getExternalId() )
			->set_totp_secret( $integration_user->getTotpSecret() )
			->set_email( $integration_user->getEmail() )
			->set_phone_number( $integration_user->getPhoneNumber()->phoneNumber() )
			->set_backup_codes_count( ! is_null( $integration_user->getBackupCodesCount() ) ? $integration_user->getBackupCodesCount() : 0 );
	}

	/**
	 * @throws TokenNotFoundException
	 * @throws API_Exception
	 * @throws AuthorizationException
	 */
	private function initialize() {
		try {
			if ( ! $this->is_initialized ) {
				$integration_user = $this->api_wrapper->get_integration_user_by_external_id( $this->user_storage->get_user_id() );

				if ( ! is_null( $integration_user ) ) {
					$this->integration_user = $integration_user;
				}
			}
		} catch ( User_Not_Found_Exception $e ) {
		}

		$this->is_initialized = true;
	}
}
