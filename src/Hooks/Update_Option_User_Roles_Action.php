<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\Core\Storage\DB_Wrapper;
use TwoFAS\TwoFAS\Storage\Options_Storage;
use UnexpectedValueException;

class Update_Option_User_Roles_Action implements Hook_Interface {

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @param DB_Wrapper      $db
	 * @param Options_Storage $options_storage
	 */
	public function __construct( DB_Wrapper $db, Options_Storage $options_storage ) {
		$this->db              = $db;
		$this->options_storage = $options_storage;
	}

	public function register_hook() {
		if ( count( $this->options_storage->get_twofas_roles() ) ) {
			$update_option_user_roles_hook_name = 'update_option_' . $this->db->get_prefix() . 'user_roles';
			add_action( $update_option_user_roles_hook_name, [ $this, 'update' ], 10, 2 );
		}
	}

	/**
	 * @param mixed $old_value
	 * @param mixed $new_value
	 */
	public function update( $old_value, $new_value ) {
		if ( ! is_array( $old_value ) || ! is_array( $new_value ) ) {
			return;
		}

		$wp_roles = array_keys( $new_value );

		try {
			$this->update_twofas_roles( $wp_roles );
		} catch ( UnexpectedValueException $e ) {
			return;
		}
	}

	/**
	 * @param array $wp_roles
	 *
	 * @throws UnexpectedValueException
	 */
	private function update_twofas_roles( array $wp_roles ) {
		$twofas_roles   = $this->options_storage->get_twofas_roles();
		$outdated_roles = $this->get_outdated_roles( $twofas_roles, $wp_roles );

		if ( ! empty( $outdated_roles ) ) {
			$this->delete_outdated_roles( $twofas_roles, $outdated_roles );
		}
	}

	/**
	 * @param array $twofas_roles
	 * @param array $wp_roles
	 *
	 * @return array
	 */
	private function get_outdated_roles( array $twofas_roles, array $wp_roles ) {
		return array_diff( $twofas_roles, $wp_roles );
	}

	/**
	 * @param array $twofas_roles
	 * @param array $outdated_roles
	 */
	private function delete_outdated_roles( array $twofas_roles, array $outdated_roles ) {
		$valid_twofas_roles = array_diff( $twofas_roles, $outdated_roles );
		$this->options_storage->set_twofas_roles( array_values( $valid_twofas_roles ) );
	}
}
