<?php

namespace TwoFAS\TwoFAS\Storage;

use TwoFAS\Core\Storage\DB_Wrapper;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use WP_User;

class User_Storage {

	const STEP_TOKEN                       = 'twofas_step_token';
	const USER_BLOCKED_UNTIL               = 'twofas_blocked_until';
	const USER_LAST_LOGIN_TIME             = 'twofas_last_login_time';
	const REMEMBER_ME                      = 'twofas_remember_me';
	const TOTP_STATUS                      = 'twofas_totp_status';
	const SMS_STATUS                       = 'twofas_sms_status';
	const CALL_STATUS                      = 'twofas_call_status';
	const METHOD_NOT_CONFIGURED            = 'NOT_CONFIGURED';
	const METHOD_CONFIGURED_DISABLED       = 'CONFIGURED_DISABLED';
	const METHOD_CONFIGURED_ENABLED        = 'CONFIGURED_ENABLED';
	const OFFLINE_CODES                    = 'twofas_offline_codes';
	const OFFLINE_CODES_STATUS             = 'twofas_offline_codes_status';
	const SECOND_FACTOR_STATUS             = 'twofas_is_2fa_enabled';
	const RELOAD_MODAL_STATUS              = 'twofas_reload_modal_disabled';
	const USER_LOGIN_BLOCK_TIME_IN_MINUTES = 5;
	const TWO_FACTOR_AUTH_ENABLED          = '1';
	const TWO_FACTOR_AUTH_DISABLED         = '0';

	/**
	 * @var array
	 */
	private $wp_user_meta = [
		self::TOTP_STATUS,
		self::SMS_STATUS,
		self::CALL_STATUS,
		self::STEP_TOKEN,
		self::REMEMBER_ME,
		self::USER_BLOCKED_UNTIL,
		self::USER_LAST_LOGIN_TIME,
		self::OFFLINE_CODES,
		self::OFFLINE_CODES_STATUS,
		self::SECOND_FACTOR_STATUS,
		self::RELOAD_MODAL_STATUS,
	];

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @var WP_User|null
	 */
	private $wp_user;

	/**
	 * @param DB_Wrapper $db
	 */
	public function __construct( DB_Wrapper $db ) {
		$this->db = $db;
	}

	/**
	 * @return array
	 */
	public function get_wp_user_meta() {
		return $this->wp_user_meta;
	}

	/**
	 * @param WP_User $user
	 */
	public function set_wp_user( WP_User $user ) {
		$this->wp_user = $user;
	}

	public function reset_wp_user() {
		$this->wp_user = null;
	}

	/**
	 * @return WP_User
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function get_wp_user() {
		if ( ! $this->is_wp_user_set() ) {
			throw new User_Not_Found_Exception();
		}

		return $this->wp_user;
	}

	/**
	 * @return bool
	 */
	public function is_wp_user_set() {
		return $this->wp_user instanceof WP_User;
	}

	/**
	 * @return int
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function get_user_id() {
		return $this->get_wp_user()->ID;
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function is_totp_enabled() {
		$method_status = $this->get_totp_status();

		return $this->is_auth_method_enabled( $method_status );
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function is_sms_enabled() {
		$sms_status = $this->get_sms_status();

		return $this->is_auth_method_enabled( $sms_status );
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function are_offline_codes_enabled() {
		$offline_codes_status = $this->get_offline_codes_status();

		return $this->is_auth_method_enabled( $offline_codes_status );
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function is_totp_configured() {
		$totp_status = $this->get_totp_status();

		return $this->is_auth_method_configured( $totp_status );
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function is_sms_configured() {
		$sms_status = $this->get_sms_status();

		return $this->is_auth_method_configured( $sms_status );
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function are_offline_codes_configured() {
		$offline_codes_status = $this->get_offline_codes_status();

		return $this->is_auth_method_configured( $offline_codes_status );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function enable_totp() {
		$this->set_auth_method_status( self::TOTP_STATUS, self::METHOD_CONFIGURED_ENABLED );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function disable_totp() {
		$this->set_auth_method_status( self::TOTP_STATUS, self::METHOD_CONFIGURED_DISABLED );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function remove_totp() {
		$this->set_auth_method_status( self::TOTP_STATUS, self::METHOD_NOT_CONFIGURED );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function enable_sms() {
		$this->set_auth_method_status( self::SMS_STATUS, self::METHOD_CONFIGURED_ENABLED );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function disable_sms() {
		$this->set_auth_method_status( self::SMS_STATUS, self::METHOD_CONFIGURED_DISABLED );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function remove_sms() {
		$this->set_auth_method_status( self::SMS_STATUS, self::METHOD_NOT_CONFIGURED );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function enable_offline_codes() {
		$this->set_auth_method_status( self::OFFLINE_CODES_STATUS, self::METHOD_CONFIGURED_ENABLED );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function disable_offline_codes() {
		$this->set_auth_method_status( self::OFFLINE_CODES_STATUS, self::METHOD_CONFIGURED_DISABLED );
	}

	/**
	 * @return string
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function get_email() {
		return $this->get_wp_user()->user_email;
	}

	/**
	 * @param string $token
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function set_step_token( $token ) {
		$this->set_user_meta( self::STEP_TOKEN, $token );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function delete_step_token() {
		$this->delete_user_meta( self::STEP_TOKEN );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function block_user() {
		$this->set_user_meta( self::USER_BLOCKED_UNTIL, time() + ( self::USER_LOGIN_BLOCK_TIME_IN_MINUTES * 60 ) );
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function is_user_blocked() {
		$blocked_until = $this->get_user_meta( self::USER_BLOCKED_UNTIL );

		return time() < ( (int) $blocked_until );
	}

	/**
	 * @return null|string
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function get_last_login_time() {
		return $this->get_user_meta( self::USER_LAST_LOGIN_TIME );
	}

	/**
	 * @param int $time
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function set_last_login_time( $time ) {
		$this->set_user_meta( self::USER_LAST_LOGIN_TIME, $time );
	}

	/**
	 * @param array $offline_codes
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function set_offline_codes( array $offline_codes ) {
		$this->set_user_meta( self::OFFLINE_CODES, $offline_codes );
	}

	/**
	 * @return array
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function get_offline_codes() {
		$offline_codes = $this->get_user_meta( self::OFFLINE_CODES );

		if ( ! is_array( $offline_codes ) ) {
			$offline_codes = [];
		}

		return $offline_codes;
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function enable_2fa() {
		$this->set_user_meta( self::SECOND_FACTOR_STATUS, self::TWO_FACTOR_AUTH_ENABLED );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function disable_2fa() {
		$this->set_user_meta( self::SECOND_FACTOR_STATUS, self::TWO_FACTOR_AUTH_DISABLED );
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function is_2fa_enabled() {
		$status = $this->get_user_meta( self::SECOND_FACTOR_STATUS );

		return self::TWO_FACTOR_AUTH_ENABLED === $status;
	}

	/**
	 * @return int
	 */
	public function get_user_count() {
		return count( get_users() );
	}

	/**
	 * @return int
	 */
	public function get_active_user_count() {
		return count( $this->get_active_users() );
	}

	/**
	 * @return int
	 */
	public function get_migrated_user_count() {
		return count( $this->get_migrated_users() );
	}

	/**
	 * @return int
	 */
	public function get_number_of_users_with_enabled_sms_backup() {
		return count( $this->get_users_with_enabled_sms_backup() );
	}

	public function disable_sms_backup_globally() {
		$this->db->update(
			$this->db->usermeta(),
			[
				'meta_value' => self::METHOD_CONFIGURED_DISABLED
			],
			[
				'meta_key'   => self::SMS_STATUS,
				'meta_value' => self::METHOD_CONFIGURED_ENABLED
			],
			[ '%s' ],
			[ '%s', '%s' ]
		);
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function delete_totp_configuration() {
		$this->delete_user_meta( self::TOTP_STATUS );
	}

	/**
	 * @return WP_User[]
	 */
	private function get_users_with_enabled_sms_backup() {
		return get_users( [
			'meta_key'   => self::SMS_STATUS,
			'meta_value' => self::METHOD_CONFIGURED_ENABLED,
		] );
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function is_reload_modal_disabled() {
		return '1' === $this->get_user_meta( self::RELOAD_MODAL_STATUS );
	}

	/**
	 * @throws User_Not_Found_Exception
	 */
	public function disable_reload_modal() {
		$this->set_user_meta( self::RELOAD_MODAL_STATUS, '1' );
	}

	/**
	 * @return array
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function get_roles() {
		$wp_user = $this->get_wp_user();

		return $wp_user->roles;
	}

	public function delete_wp_user_meta() {
		$users = get_users();

		foreach ( $users as $user ) {
			foreach ( $this->wp_user_meta as $user_meta ) {
				delete_user_meta( $user->ID, $user_meta );
			}
		}
	}

	/**
	 * @return string
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function get_totp_status() {
		return $this->get_auth_method_status( self::TOTP_STATUS );
	}

	/**
	 * @return string
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function get_sms_status() {
		return $this->get_auth_method_status( self::SMS_STATUS );
	}

	/**
	 * @return string
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function get_offline_codes_status() {
		return $this->get_auth_method_status( self::OFFLINE_CODES_STATUS );
	}

	/**
	 * @param string $status_key
	 *
	 * @return string
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function get_auth_method_status( $status_key ) {
		$method_status = $this->get_user_meta( $status_key );

		if ( ! is_string( $method_status ) || empty( $method_status ) ) {
			$method_status = self::METHOD_NOT_CONFIGURED;
		}

		return $method_status;
	}

	/**
	 * @param string $method_status
	 *
	 * @return bool
	 */
	private function is_auth_method_enabled( $method_status ) {
		return self::METHOD_CONFIGURED_ENABLED === $method_status;
	}

	/**
	 * @param string $method_status
	 *
	 * @return bool
	 */
	private function is_auth_method_configured( $method_status ) {
		return self::METHOD_CONFIGURED_ENABLED === $method_status
			|| self::METHOD_CONFIGURED_DISABLED === $method_status;
	}

	/**
	 * @param string $method_key
	 * @param string $status
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function set_auth_method_status( $method_key, $status ) {
		$this->set_user_meta( $method_key, $status );
	}

	/**
	 * @return WP_User[]
	 */
	private function get_active_users() {
		return get_users( [
			'meta_key'   => self::SECOND_FACTOR_STATUS,
			'meta_value' => '1',
		] );
	}

	private function get_migrated_users() {
		return get_users( [
			'meta_key'   => 'twofas_light_totp_secret',
			'meta_value' => '',
			'meta_compare' => 'EXISTS'
		] );
	}

	/**
	 * @param string $meta_key
	 *
	 * @return array|string|null
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function get_user_meta( $meta_key ) {
		$meta_value = get_user_meta( $this->get_user_id(), $meta_key, true );

		if ( empty( $meta_value ) ) {
			return null;
		}

		return $meta_value;
	}

	/**
	 * @param string           $meta_key
	 * @param array|int|string $meta_value
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function set_user_meta( $meta_key, $meta_value ) {
		update_user_meta( $this->get_user_id(), $meta_key, $meta_value );
	}

	/**
	 * @param string $meta_key
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function delete_user_meta( $meta_key ) {
		delete_user_meta( $this->get_user_id(), $meta_key );
	}
}
