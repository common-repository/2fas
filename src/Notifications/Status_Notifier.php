<?php

namespace TwoFAS\TwoFAS\Notifications;

use TwoFAS\Core\Update\Deprecation;
use TwoFAS\TwoFAS\Core\Legacy_Mode_Checker;
use TwoFAS\TwoFAS\Core\Plugin_Status;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Storage\Options_Storage;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;
use TwoFAS\TwoFAS\User\Capabilities;
use UnexpectedValueException;

class Status_Notifier {

	const REGISTRATION_COMPLETED = 'is_registration_completed';
	const PAGE_DASHBOARD         = 'is_user_on_dashboard_page';
	const PAGE_PERSONAL_SETTINGS = 'is_user_on_personal_settings_page';
	const ROLE_ADMIN             = 'is_current_user_admin';
	const PLUGIN_ENABLED         = 'is_plugin_enabled';
	const TOTP_OBLIGATORY        = 'is_totp_obligatory';
	const TOTP_CONFIGURED        = 'is_totp_configured';
	const TOTP_ENABLED           = 'is_totp_enabled';
	const LEGACY_MODE            = 'is_legacy_mode_active';
	const PHP_DEPRECATED         = 'is_php_deprecated';

	/**
	 * @var Plugin_Status
	 */
	private $plugin_status;

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var Legacy_Mode_Checker
	 */
	private $legacy_mode_checker;

	/**
	 * @var array
	 */
	private $requirements = [];

	/**
	 * @var array
	 */
	private $notifications = [];

	/**
	 * @var Deprecation
	 */
	private $deprecation;

	/**
	 * @param Plugin_Status       $plugin_status
	 * @param Storage             $storage
	 * @param Request             $request
	 * @param Flash               $flash
	 * @param Legacy_Mode_Checker $legacy_mode_checker
	 * @param Deprecation         $deprecation
	 */
	public function __construct(
		Plugin_Status $plugin_status,
		Storage $storage,
		Request $request,
		Flash $flash,
		Legacy_Mode_Checker $legacy_mode_checker,
		Deprecation $deprecation
	) {
		$this->plugin_status       = $plugin_status;
		$this->options_storage     = $storage->get_options();
		$this->user_storage        = $storage->get_user_storage();
		$this->request             = $request;
		$this->flash               = $flash;
		$this->legacy_mode_checker = $legacy_mode_checker;
		$this->deprecation         = $deprecation;
	}

	/**
	 * @throws User_Not_Found_Exception
	 * @throws UnexpectedValueException
	 */
	public function show() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$this->set_requirements();
		$this->set_notifications();

		foreach ( $this->notifications as $notification ) {
			$this->flash->add_message_now( 'error', $notification );
		}
	}

	/**
	 * @throws User_Not_Found_Exception
	 * @throws UnexpectedValueException
	 */
	private function set_requirements() {
		$this->requirements = [
			self::REGISTRATION_COMPLETED => $this->plugin_status->client_completed_registration(),
			self::PAGE_DASHBOARD         => $this->is_user_on_dashboard_page(),
			self::PAGE_PERSONAL_SETTINGS => $this->is_user_on_personal_settings_page(),
			self::ROLE_ADMIN             => $this->is_current_user_admin(),
			self::PLUGIN_ENABLED         => $this->plugin_status->is_plugin_enabled(),
			self::TOTP_OBLIGATORY        => $this->is_totp_obligatory(),
			self::TOTP_CONFIGURED        => $this->user_storage->is_totp_configured(),
			self::TOTP_ENABLED           => $this->user_storage->is_totp_enabled(),
			self::LEGACY_MODE            => $this->legacy_mode_checker->is_2fa_enabled_in_legacy_mode(),
			self::PHP_DEPRECATED         => $this->deprecation->is_php_deprecated(),
		];
	}

	private function set_notifications() {
		if ( $this->can_show_installation_not_completed() ) {
			$this->notifications[] = 'installation-not-completed';
		}

		if ( $this->can_show_plugin_disabled_by_admin() ) {
			$this->notifications[] = 'plugin-disabled-by-admin';
		}

		if ( $this->can_show_2fa_role_obligated() ) {
			$this->notifications[] = '2fa-role-obligated';
		}

		if ( $this->can_show_please_configure_2fa() ) {
			$this->notifications[] = 'please-configure-2fa';
		}

		if ( $this->can_show_please_configure_totp() ) {
			$this->notifications[] = 'please-configure-totp';
		}

		if ( $this->can_show_please_enable_totp() ) {
			$this->notifications[] = 'please-enable-totp';
		}

		if ( $this->can_show_please_configure_obligatory_totp() ) {
			$this->notifications[] = 'please-configure-obligatory-totp';
		}

		if ( $this->can_show_please_enable_obligatory_totp() ) {
			$this->notifications[] = 'please-enable-obligatory-totp';
		}

		if ( $this->can_show_deprecated_php() ) {
			$this->notifications[] = 'deprecated-php';
		}
	}

	/**
	 * @return bool
	 */
	private function can_show_installation_not_completed() {
		return ! $this->requirements[ self::REGISTRATION_COMPLETED ]
			&& ! $this->requirements[ self::PAGE_DASHBOARD ]
			&& $this->requirements[ self::ROLE_ADMIN ];
	}

	/**
	 * @return bool
	 */
	private function can_show_plugin_disabled_by_admin() {
		return $this->requirements[ self::REGISTRATION_COMPLETED ]
			&& $this->requirements[ self::PAGE_PERSONAL_SETTINGS ]
			&& ! $this->requirements[ self::PLUGIN_ENABLED ];
	}

	/**
	 * @return bool
	 */
	private function can_show_2fa_role_obligated() {
		return $this->requirements[ self::REGISTRATION_COMPLETED ]
			&& $this->requirements[ self::TOTP_OBLIGATORY ]
			&& $this->requirements[ self::TOTP_CONFIGURED ]
			&& ! $this->requirements[ self::TOTP_ENABLED ]
			&& ! $this->requirements[ self::LEGACY_MODE ];
	}

	/**
	 * @return bool
	 */
	private function can_show_please_configure_2fa() {
		return $this->requirements[ self::REGISTRATION_COMPLETED ]
			&& $this->requirements[ self::TOTP_OBLIGATORY ]
			&& ! $this->requirements[ self::TOTP_CONFIGURED ]
			&& ! $this->requirements[ self::LEGACY_MODE ];
	}

	/**
	 * @return bool
	 */
	private function can_show_please_configure_totp() {
		return $this->requirements[ self::REGISTRATION_COMPLETED ]
			&& $this->requirements[ self::LEGACY_MODE ]
			&& ! $this->requirements[ self::TOTP_OBLIGATORY ]
			&& ! $this->requirements[ self::TOTP_CONFIGURED ];
	}

	/**
	 * @return bool
	 */
	private function can_show_please_enable_totp() {
		return $this->requirements[ self::REGISTRATION_COMPLETED ]
			&& $this->requirements[ self::LEGACY_MODE ]
			&& ! $this->requirements[ self::TOTP_OBLIGATORY ]
			&& $this->requirements[ self::TOTP_CONFIGURED ];
	}

	/**
	 * @return bool
	 */
	private function can_show_please_configure_obligatory_totp() {
		return $this->requirements[ self::REGISTRATION_COMPLETED ]
			&& $this->requirements[ self::LEGACY_MODE ]
			&& $this->requirements[ self::TOTP_OBLIGATORY ]
			&& ! $this->requirements[ self::TOTP_CONFIGURED ];
	}

	/**
	 * @return bool
	 */
	private function can_show_please_enable_obligatory_totp() {
		return $this->requirements[ self::REGISTRATION_COMPLETED ]
			&& $this->requirements[ self::LEGACY_MODE ]
			&& $this->requirements[ self::TOTP_OBLIGATORY ]
			&& $this->requirements[ self::TOTP_CONFIGURED ]
			&& ! $this->requirements[ self::TOTP_ENABLED ];
	}

	/**
	 * @return bool
	 */
	private function can_show_deprecated_php() {
		return $this->requirements[ self::ROLE_ADMIN ]
			&& $this->requirements[ self::PHP_DEPRECATED ];
	}

	/**
	 * @return bool
	 */
	private function is_user_on_dashboard_page() {
		return $this->request->is_page_equal_to( Action_Index::SUBMENU_DASHBOARD );
	}

	/**
	 * @return bool
	 */
	private function is_user_on_personal_settings_page() {
		return $this->request->is_page_equal_to( Action_Index::SUBMENU_CHANNEL );
	}

	/**
	 * @return bool
	 */
	private function is_current_user_admin() {
		return current_user_can( Capabilities::ADMIN );
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 * @throws UnexpectedValueException
	 */
	private function is_totp_obligatory() {
		return $this->options_storage->has_twofas_role( $this->user_storage->get_roles() );
	}
}
