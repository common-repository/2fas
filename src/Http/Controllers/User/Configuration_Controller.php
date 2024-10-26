<?php

namespace TwoFAS\TwoFAS\Http\Controllers\User;

use TwoFAS\TwoFAS\Core\Legacy_Mode_Checker;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Controllers\Controller;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Integration\Integration_User;
use TwoFAS\TwoFAS\Storage\Storage;

abstract class Configuration_Controller extends Controller {

	/**
	 * @var Integration_User
	 */
	protected $integration_user;

	/**
	 * @var Legacy_Mode_Checker
	 */
	protected $legacy_mode_checker;

	/**
	 * @param Storage             $storage
	 * @param API_Wrapper         $api_wrapper
	 * @param Integration_User    $integration_user
	 * @param Flash               $flash
	 * @param Legacy_Mode_Checker $legacy_mode_checker
	 */
	public function __construct(
		Storage $storage,
		API_Wrapper $api_wrapper,
		Integration_User $integration_user,
		Flash $flash,
		Legacy_Mode_Checker $legacy_mode_checker
	) {
		parent::__construct( $storage, $api_wrapper, $flash );

		$this->integration_user    = $integration_user;
		$this->legacy_mode_checker = $legacy_mode_checker;
	}

	/**
	 * @return array
	 *
	 * @throws User_Not_Found_Exception
	 */
	protected function get_user_status_data() {
		$trusted_devices_storage = $this->storage->get_trusted_devices_storage();
		$user_storage            = $this->storage->get_user_storage();
		$options                 = $this->storage->get_options();

		return [
			'is_plan_premium'               => $options->is_plan_premium(),
			'is_2fa_enabled'                => $user_storage->is_2fa_enabled(),
			'is_2fa_enabled_in_legacy_mode' => $this->legacy_mode_checker->is_2fa_enabled_in_legacy_mode(),
			'is_totp_configured'            => $user_storage->is_totp_configured(),
			'is_totp_enabled'               => $user_storage->is_totp_enabled(),
			'is_sms_configured'             => $user_storage->is_sms_configured(),
			'is_sms_enabled'                => $user_storage->is_sms_enabled(),
			'are_offline_codes_configured'  => $user_storage->are_offline_codes_configured(),
			'are_offline_codes_enabled'     => $user_storage->are_offline_codes_enabled(),
			'trusted_devices_enabled'       => $options->are_trusted_devices_enabled(),
			'trusted_device_count'          => $trusted_devices_storage->get_trusted_device_count( $user_storage->get_user_id() ),
			'reload_modal_disabled'         => $user_storage->is_reload_modal_disabled(),
		];
	}
}
