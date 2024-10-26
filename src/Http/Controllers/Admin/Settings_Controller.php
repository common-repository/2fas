<?php

namespace TwoFAS\TwoFAS\Http\Controllers\Admin;

use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Exceptions\Validation_Exception;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\TwoFAS\Http\Controllers\Controller;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Templates\Views;
use UnexpectedValueException;
use WP_Roles;

class Settings_Controller extends Controller {

	/**
	 * @param Request $request
	 *
	 * @return View_Response
	 */
	public function show_settings_page( Request $request ) {
		$options = $this->storage->get_options();

		return $this->view( Views::ADMIN_SETTINGS, [
			'roles'                   => $this->get_role_settings(),
			'is_logging_allowed'      => $options->is_logging_allowed(),
			'trusted_devices_enabled' => $options->are_trusted_devices_enabled()
		] );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 *
	 * @throws UnexpectedValueException
	 */
	public function save_roles( Request $request ) {
		$roles = $request->post( 'roles' );

		if ( is_null( $roles ) ) {
			$roles = [];
		}

		if ( ! is_array( $roles ) ) {
			throw new UnexpectedValueException( 'Could not update configuration due to invalid data type.' );
		}

		if ( ! $this->validate_roles( $roles ) ) {
			throw new Validation_Exception( 'Invalid role has been sent.' );
		}

		$this->storage->get_options()->set_twofas_roles( $roles );
		$this->flash->add_message( 'success', 'roles-saved' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_SETTINGS, Action_Index::ACTION_DISPLAY_SETTINGS ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 */
	public function save_logging( Request $request ) {
		$options_storage    = $this->storage->get_options();
		$is_logging_allowed = (bool) $request->post( 'logging_enabled' );

		if ( $is_logging_allowed ) {
			$this->flash->add_message( 'success', 'logging-enabled' );
			$options_storage->enable_logging();
		} else {
			$this->flash->add_message( 'success', 'logging-disabled' );
			$options_storage->disable_logging();
		}

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_SETTINGS, Action_Index::ACTION_DISPLAY_SETTINGS ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 */
	public function save_trusted_devices( Request $request ) {
		$options_storage             = $this->storage->get_options();
		$are_trusted_devices_enabled = $request->post( 'trusted_devices_enabled' );

		if ( 'on' === $are_trusted_devices_enabled ) {
			$this->flash->add_message( 'success', 'trusted-devices-enabled' );
			$options_storage->enable_trusted_devices();
		} else {
			$this->flash->add_message( 'success', 'trusted-devices-disabled' );
			$options_storage->disable_trusted_devices();
		}

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_SETTINGS, Action_Index::ACTION_DISPLAY_SETTINGS ) );

	}

	/**
	 * @return array
	 *
	 * @throws UnexpectedValueException
	 */
	private function get_role_settings() {
		$wp_roles     = $this->get_wp_roles();
		$twofas_roles = $this->storage->get_options()->get_twofas_roles();
		$roles        = [];

		foreach ( $wp_roles as $role_key => $role_name ) {
			$roles[] = [
				'key'        => $role_key,
				'name'       => $role_name,
				'obligatory' => in_array( $role_key, $twofas_roles, true ),
			];
		}

		return $roles;
	}

	/**
	 * @return array
	 */
	private function get_wp_roles() {
		$wp_roles = new WP_Roles();

		return $wp_roles->role_names;
	}

	/**
	 * @param array $roles
	 *
	 * @return bool
	 */
	private function validate_roles( array $roles ) {
		$wp_roles = array_keys( $this->get_wp_roles() );
		$diff     = array_diff( $roles, $wp_roles );

		return empty( $diff );
	}
}
