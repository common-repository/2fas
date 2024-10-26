<?php

namespace TwoFAS\TwoFAS\Codes;

use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Helpers\Environment;
use TwoFAS\TwoFAS\Integration\Integration_Name;
use TwoFAS\TwoFAS\Storage\User_Storage;

class QR_Code_Message {

	const MAXIMUM_ISSUER_LENGTH      = 18;
	const MAXIMUM_DESCRIPTION_LENGTH = 19;

	/**
	 * @var Environment
	 */
	private $environment;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Integration_Name
	 */
	private $integration_name;

	/**
	 * @param Environment  $environment
	 * @param User_Storage $user_storage
	 */
	public function __construct( Environment $environment, User_Storage $user_storage ) {
		$this->environment      = $environment;
		$this->user_storage     = $user_storage;
		$this->integration_name = new Integration_Name( $this->environment->get_wordpress_app_url() );
	}

	/**
	 * @param string $totp_secret
	 *
	 * @return string
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function create( $totp_secret ) {
		$query       = $this->build_query( $totp_secret );
		$email       = $this->user_storage->get_email();
		$description = $this->get_description();

		return "otpauth://totp/{$description}:{$email}?{$query}";
	}

	/**
	 * @param string $totp_secret
	 *
	 * @return string
	 */
	private function build_query( $totp_secret ) {
		return build_query( [
			'secret'        => $totp_secret,
			'issuer'        => $this->get_issuer()
		] );
	}

	/**
	 * @return string
	 */
	private function get_issuer() {
		$site_name = substr( $this->environment->get_wordpress_app_name(), 0, self::MAXIMUM_ISSUER_LENGTH );

		return rawurlencode( trim( $site_name ) );
	}

	/**
	 * @return string
	 */
	private function get_description() {
		$integration_name = $this->integration_name->get_name();
		$description      = $this->validate_description( $integration_name ) ? $integration_name : 'WordPress Account';

		return rawurlencode( $description );
	}

	/**
	 * @param string $description
	 *
	 * @return bool
	 */
	private function validate_description( $description ) {
		return strlen( $description ) <= self::MAXIMUM_DESCRIPTION_LENGTH;
	}
}
