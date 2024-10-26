<?php

namespace TwoFAS\TwoFAS\Http\Controllers;

use TwoFAS\Core\Http\Controller as Base_Controller;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Templates\Views;
use TwoFAS\ValidationRules\ValidationExceptionInterface;
use TwoFAS\ValidationRules\ValidationRules;

abstract class Controller extends Base_Controller {

	/**
	 * @var Storage
	 */
	protected $storage;

	/**
	 * @var API_Wrapper
	 */
	protected $api_wrapper;

	/**
	 * @var Flash
	 */
	protected $flash;

	/**
	 * @var array
	 */
	private $validation_rules = [
		'email'       => [
			ValidationRules::REQUIRED => 'email-required',
			ValidationRules::EMAIL    => 'email-invalid',
			ValidationRules::UNIQUE   => 'email-unique',
		],
		'password'    => [
			ValidationRules::REQUIRED  => 'password-required',
			ValidationRules::CONFIRMED => 'password-confirmed',
			ValidationRules::MIN       => 'password-min',
		],
		'code'        => [
			ValidationRules::REQUIRED => 'token-empty',
			ValidationRules::STRING   => 'token-validation',
			ValidationRules::DIGITS   => 'token-validation'
		],
		'totp_secret' => [
			ValidationRules::REQUIRED    => 'totp-secret-empty',
			ValidationRules::TOTP_SECRET => 'totp-secret-validation'
		],
		'privacy_policy' => [
			ValidationRules::REQUIRED => 'privacy-policy-required'
		]
	];

	/**
	 * @param Storage     $storage
	 * @param API_Wrapper $api_wrapper
	 * @param Flash       $flash
	 */
	public function __construct( Storage $storage, API_Wrapper $api_wrapper, Flash $flash ) {
		$this->storage     = $storage;
		$this->api_wrapper = $api_wrapper;
		$this->flash       = $flash;
	}

	/**
	 * @param string $error
	 *
	 * @return View_Response
	 */
	protected function error( $error ) {
		return $this->view( Views::ERROR, [
			'description' => $error,
		] );
	}

	/**
	 * @param ValidationExceptionInterface $exception
	 *
	 * @return string
	 */
	protected function get_validation_error( ValidationExceptionInterface $exception ) {
		foreach ( $exception->getErrors() as $key => $errors ) {
			if ( array_key_exists( $key, $this->validation_rules ) ) {
				return $this->validation_rules[ $key ][ $errors[0] ];
			}
		}

		return 'default';
	}
}
