<?php

namespace TwoFAS\TwoFAS\Core;

use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Storage\Options_Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;
use WP_User;

/**
 * @deprecated
 */
class Legacy_Mode_Checker {

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @param User_Storage    $user_storage
	 * @param Options_Storage $options_storage
	 */
	public function __construct( User_Storage $user_storage, Options_Storage $options_storage ) {
		$this->user_storage    = $user_storage;
		$this->options_storage = $options_storage;
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 * @deprecated
	 *
	 */
	public function totp_is_obligatory_or_legacy_mode_is_active() {
		return $this->options_storage->has_twofas_role( $this->user_storage->get_roles() ) || $this->is_2fa_enabled_in_legacy_mode();
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 * @deprecated
	 *
	 */
	public function totp_is_obligatory_and_legacy_mode_is_active() {
		return $this->options_storage->has_twofas_role( $this->user_storage->get_roles() ) && $this->is_2fa_enabled_in_legacy_mode();
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 * @deprecated
	 *
	 */
	public function totp_is_obligatory_and_legacy_mode_is_not_active() {
		return $this->options_storage->has_twofas_role( $this->user_storage->get_roles() ) && ! $this->is_2fa_enabled_in_legacy_mode();
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 * @deprecated
	 *
	 */
	public function is_2fa_enabled_in_legacy_mode() {
		return ! $this->user_storage->is_totp_enabled() && $this->user_storage->is_2fa_enabled();
	}

	/**
	 * @deprecated
	 */
	public function disable_legacy_2fa() {
		$users = $this->get_users_in_legacy_mode();

		foreach ( $users as $user ) {
			$this->set_user_meta( $user->ID, User_Storage::SECOND_FACTOR_STATUS, User_Storage::TWO_FACTOR_AUTH_DISABLED );

		}
	}

	/**
	 * @return WP_User[]
	 */
	private function get_users_in_legacy_mode() {
		$users                = $this->get_users_with_enabled_two_factor_authentication();
		$users_in_legacy_mode = [];

		foreach ( $users as $user ) {
			$totp_status = get_user_meta( $user->ID, User_Storage::TOTP_STATUS, true );

			if ( User_Storage::METHOD_CONFIGURED_ENABLED !== $totp_status ) {
				$users_in_legacy_mode[] = $user;
			}
		}

		return $users_in_legacy_mode;
	}

	/**
	 * @return WP_User[]
	 */
	private function get_users_with_enabled_two_factor_authentication() {
		return get_users( [
			'meta_key'   => User_Storage::SECOND_FACTOR_STATUS,
			'meta_value' => '1',
		] );
	}

	/**
	 * @param int              $user_id
	 * @param string           $meta_key
	 * @param array|int|string $meta_value
	 */
	private function set_user_meta( $user_id, $meta_key, $meta_value ) {
		update_user_meta( $user_id, $meta_key, $meta_value );
	}
}
