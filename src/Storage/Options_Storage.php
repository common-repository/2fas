<?php

namespace TwoFAS\TwoFAS\Storage;

use TwoFAS\Encryption\AESGeneratedKey;
use TwoFAS\Encryption\AESKey;
use TwoFAS\Encryption\Interfaces\Key;
use TwoFAS\Encryption\Interfaces\ReadKey;
use TwoFAS\Encryption\Interfaces\WriteKey;
use UnexpectedValueException;

class Options_Storage implements ReadKey, WriteKey {

	const TWOFAS_ENABLED                 = 'twofas_enabled';
	const TWOFAS_PLUGIN_STATUS           = 'twofas_is_plugin_enabled';
	const TWOFAS_ENCRYPTION_KEY          = 'twofas_encryption_key';
	const TWOFAS_EMAIL                   = 'twofas_email';
	const TWOFAS_PASSWORD                = 'twofas_password';
	const TWOFAS_PLUGIN_VERSION          = 'twofas_plugin_version';
	const TWOFAS_PLAN_KEY                = 'twofas_plan';
	const TWOFAS_ROLES                   = 'twofas_roles';
	const TWOFAS_LOGGING_ALLOWED         = 'twofas_is_logging_allowed';
	const TWOFAS_TRUSTED_DEVICES_ENABLED = 'twofas_trusted_devices_enabled';
	const TWOFAS_PRIVACY_POLICY_ACCEPTED = 'twofas_privacy_policy_accepted';
	const TWOFAS_USER_MIGRATION_ALLOWED  = 'twofas_user_migration_allowed';
	const PLAN_BASIC                     = 'basic';
	const PLAN_PREMIUM                   = 'premium';

	/**
	 * @var array
	 */
	private $wp_options = [
		'twofas_login',
		'twofas_key',
		'twofas_encryption_key',
		'twofas_email',
		'twofas_password',
		'twofas_enabled',
		'twofas_plugin_version',
		'twofas_migrations',
		'twofas_integration_id',
		'twofas_oauth_token_setup',
		'twofas_oauth_token_wordpress',
		'twofas_is_plugin_enabled',
		'twofas_is_logging_allowed',
		'twofas_trusted_devices_enabled',
		'twofas_plan',
		'twofas_roles',
		'twofas_privacy_policy_accepted',
		'twofas_user_migration_allowed'
	];

	/**
	 * @return string|null
	 */
	public function get_twofas_email() {
		return get_option( self::TWOFAS_EMAIL, null );
	}

	/**
	 * @param string $email
	 */
	public function set_twofas_email( $email ) {
		update_option( self::TWOFAS_EMAIL, $email );
	}

	public function delete_twofas_password() {
		delete_option( self::TWOFAS_PASSWORD );
	}

	/**
	 * @return string|false
	 */
	public function get_twofas_encryption_key() {
		return get_option( self::TWOFAS_ENCRYPTION_KEY );
	}

	/**
	 * @param string $key
	 */
	public function set_twofas_encryption_key( $key ) {
		update_option( self::TWOFAS_ENCRYPTION_KEY, $key );
	}

	/**
	 * @return string|false
	 */
	public function get_twofas_plugin_version() {
		return get_option( self::TWOFAS_PLUGIN_VERSION );
	}

	/**
	 * @param string $version
	 */
	public function set_twofas_plugin_version( $version ) {
		update_option( self::TWOFAS_PLUGIN_VERSION, $version );
	}

	/**
	 * @param Key $key
	 */
	public function store( Key $key ) {
		$this->set_twofas_encryption_key( base64_encode( $key->getValue() ) );
	}

	public function save_aes_key() {
		$this->store( new AESGeneratedKey() );
	}

	/**
	 * @return Key
	 */
	public function retrieve() {
		$key = base64_decode( $this->get_twofas_encryption_key() );

		return new AESKey( $key );
	}

	/**
	 * @return bool
	 */
	public function is_plugin_enabled() {
		$status = get_option( self::TWOFAS_PLUGIN_STATUS );

		return '1' === $status;
	}

	public function enable_plugin() {
		update_option( self::TWOFAS_PLUGIN_STATUS, '1' );
	}

	public function disable_plugin() {
		update_option( self::TWOFAS_PLUGIN_STATUS, '0' );
	}

	/**
	 * @param string $plan
	 */
	public function set_plan( $plan ) {
		update_option( self::TWOFAS_PLAN_KEY, $plan );
	}

	public function set_premium_plan() {
		$this->set_plan( self::PLAN_PREMIUM );
	}

	public function set_basic_plan() {
		$this->set_plan( self::PLAN_BASIC );
	}

	/**
	 * @return bool
	 */
	public function is_plan_premium() {
		$plan = get_option( self::TWOFAS_PLAN_KEY );

		return self::PLAN_PREMIUM === $plan;
	}

	/**
	 * @return bool
	 */
	public function is_plan_basic() {
		return ! $this->is_plan_premium();
	}

	public function enable_trusted_devices() {
		update_option( self::TWOFAS_TRUSTED_DEVICES_ENABLED, '1' );
	}

	public function disable_trusted_devices() {
		update_option( self::TWOFAS_TRUSTED_DEVICES_ENABLED, '0' );
	}

	public function add_privacy_policy() {
		update_option( self::TWOFAS_PRIVACY_POLICY_ACCEPTED, '0' );
	}

	public function accept_privacy_policy() {
		update_option( self::TWOFAS_PRIVACY_POLICY_ACCEPTED, '1' );
	}

	public function add_user_migration_status() {
		update_option( self::TWOFAS_USER_MIGRATION_ALLOWED, '0' );
	}

	public function is_user_migration_allowed() {
		return (bool) get_option( self::TWOFAS_USER_MIGRATION_ALLOWED, false );
	}

	public function allow_user_migration() {
		update_option( self::TWOFAS_USER_MIGRATION_ALLOWED, '1' );
	}

	public function get_plugins() {
		return get_option( 'active_plugins', [] );
	}

	/**
	 * @return bool
	 */
	public function are_trusted_devices_enabled() {
		return (bool) get_option( self::TWOFAS_TRUSTED_DEVICES_ENABLED, false );
	}

	/**
	 * @return array
	 *
	 * @throws UnexpectedValueException
	 */
	public function get_twofas_roles() {
		$roles = get_option( self::TWOFAS_ROLES, [] );

		if ( is_array( $roles ) ) {
			return $roles;
		}

		throw new UnexpectedValueException( 'Option ' . self::TWOFAS_ROLES . ' should be an array.' );
	}

	/**
	 * @param array $roles
	 */
	public function set_twofas_roles( array $roles ) {
		update_option( self::TWOFAS_ROLES, $roles );
	}

	/**
	 * @param array $user_roles
	 *
	 * @return bool
	 *
	 * @throws UnexpectedValueException
	 */
	public function has_twofas_role( array $user_roles ) {
		$roles        = $this->get_twofas_roles();
		$intersection = array_intersect( $user_roles, $roles );

		return ! empty( $intersection );
	}

	public function enable_logging() {
		update_option( self::TWOFAS_LOGGING_ALLOWED, '1' );
	}

	public function disable_logging() {
		update_option( self::TWOFAS_LOGGING_ALLOWED, '0' );
	}

	/**
	 * @return bool
	 */
	public function is_logging_allowed() {
		return (bool) get_option( self::TWOFAS_LOGGING_ALLOWED, false );
	}

	public function delete_wp_options() {
		foreach ( $this->wp_options as $option_name ) {
			delete_option( $option_name );
		}
	}

	/**
	 * @param string $except
	 */
	public function delete_wp_options_except( $except ) {
		foreach ( $this->wp_options as $option_name ) {
			if ( $option_name !== $except ) {
				delete_option( $option_name );
			}
		}
	}
}
