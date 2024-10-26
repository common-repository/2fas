<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\TwoFAS\Core\Legacy_Mode_Checker;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Storage\User_Storage;
use UnexpectedValueException;

class Logout_Not_Configured_Users_Action implements Hook_Interface {

	const TWO_WEEKS_IN_SECONDS = 1209600;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Legacy_Mode_Checker
	 */
	private $legacy_mode_checker;

	/**
	 * @param User_Storage        $user_storage
	 * @param Legacy_Mode_Checker $legacy_mode_checker
	 */
	public function __construct( User_Storage $user_storage, Legacy_Mode_Checker $legacy_mode_checker ) {
		$this->user_storage        = $user_storage;
		$this->legacy_mode_checker = $legacy_mode_checker;
	}

	public function register_hook() {
		try {
			if ( $this->is_user_logged_in_for( self::TWO_WEEKS_IN_SECONDS )
				&& $this->legacy_mode_checker->totp_is_obligatory_or_legacy_mode_is_active()
				&& ! $this->user_storage->is_totp_enabled() ) {
				add_action( 'wp_loaded', 'wp_logout' );
			}
		} catch ( User_Not_Found_Exception $e ) {
			// User is not logged in
		} catch ( UnexpectedValueException $e ) {
			// Do nothing
		}
	}

	/**
	 * @param int $period
	 *
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 */
	private function is_user_logged_in_for( $period ) {
		$last_logged_in = $this->user_storage->get_last_login_time();

		if ( empty( $last_logged_in ) ) {
			return false;
		}

		return ( time() - $last_logged_in ) > $period;
	}
}
