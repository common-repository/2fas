<?php

namespace TwoFAS\TwoFAS\Notifications;

use TwoFAS\TwoFAS\Helpers\URL;
use TwoFAS\TwoFAS\Http\Action_Index;

class Notification {

	/**
	 * @var array
	 */
	private static $notifications = [];

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	public static function get( $key ) {
		if ( empty( self::$notifications ) ) {
			self::load_notifications();
		}

		if ( ! isset( self::$notifications[ $key ] ) ) {
			return self::$notifications['default'];
		}

		return self::$notifications[ $key ];
	}

	private static function load_notifications() {
		$reset_password = self::generate_link_to_action( Action_Index::ACTION_RESET_PASSWORD, __( 'here', '2fas' ) );
		$create_account = self::generate_link_to_action( Action_Index::ACTION_CREATE_ACCOUNT, __( 'here', '2fas' ) );

		self::$notifications = [
			// Plugin overall notifications
			'installation-not-completed'       => sprintf(
			/* translators: %1$s: Link to create account page %2$s Html tag 3%$s Html tag*/
				__( 'Please click %1$s to go to the %2$s2FAS Admin%3$s', '2fas' ),
				$create_account,
				'<strong>',
				'</strong>'
			),
			'logged-in'                        => __( 'You have been logged in to your 2FAS account.', '2fas' ),
			'logged-out'                       => __( 'You have been logged out from 2FAS account.', '2fas' ),
			'plugin-enabled'                   => __( '2FAS Classic plugin has been enabled.', '2fas' ),
			'plugin-disabled'                  => __( '2FAS Classic plugin has been disabled.', '2fas' ),
			'csrf'                             => __( 'CSRF token is invalid.', '2fas' ),
			'ajax'                             => __( 'Invalid AJAX request.', '2fas' ),
			'plan-updated'                     => __( 'Plan has been changed.', '2fas' ),
			'credit-card-required'             => __( 'Credit card required to do this action.', '2fas' ),
			'plan-downgraded-automatically'    => __( 'Plan has been automatically downgraded because there is no credit card.', '2fas' ),
			'plugin-disabled-by-admin'         => __( 'Plugin has been disabled by administrator. Configuration can be changed but it will be applied when administrator enables plugin.', '2fas' ),
			'account-exists'                   => __( 'Account already exists.', '2fas' ),
			'premium-only'                     => __( 'This action is available only in premium plan.', '2fas' ),
			'inconsistent-data'                => __( 'Plugin data is inconsistent.', '2fas' ),
			'session-tables'                   => __( 'Session tables does not exists.', '2fas' ),
			'roles-saved'                      => __( 'Users with the selected roles have been obligated to use 2FA.', '2fas' ),
			'logging-enabled'                  => __( 'Error logging has been enabled.', '2fas' ),
			'logging-disabled'                 => __( 'Error logging has been disabled.', '2fas' ),
			'second-factor-status-disabled'    => __( 'Cannot perform this action because second factor is disabled.', '2fas' ),
			'deprecated-php'                   => sprintf(
			/* translators: %1$s: Html tag %2$s Html tag*/
				__( 'Starting from next major version (2.6.0) the 2FAS plugin will not work with your version of PHP. %1$sClick here to learn more about updating PHP%2$s.', '2fas' ),
				'<a href="https://wordpress.org/support/update-php/" target="_blank">',
				'</a>'
			),
			'account-required'                 => __( 'Before starting to use 2FAS plugin, you have to create 2FAS account or log in to the existing one.', '2fas' ),
			'email-sent'                       => __( 'If you entered a valid e-mail, you will receive the instructions to reset your password. Please check your inbox.', '2fas' ),

			// Validation
			'email-required'                   => __( 'Please enter your e-mail.', '2fas' ),
			'email-invalid'                    => __( 'E-mail is invalid.', '2fas' ),
			'email-unique'                     => sprintf(
			/* translators: %1$s Html tag %2$s: Link to reset password page %3$s Html tag*/
				__( 'E-mail already exists, click %1$s%2$s%3$s to reset your password.', '2fas' ),
				'<strong>',
				$reset_password,
				'</strong>'
			),
			'password-required'                => __( 'Please enter your password.', '2fas' ),
			'password-confirmed'               => __( 'Password confirmation does not match password.', '2fas' ),
			'password-min'                     => __( 'Password should have at least 6 characters.', '2fas' ),
			'invalid-credentials'              => __( 'Invalid credentials entered.', '2fas' ),
			'token-validation'                 => __( 'Wrong token format, please check entered token.', '2fas' ),
			'token-empty'                      => __( 'Token cannot be empty.', '2fas' ),
			'totp-secret-empty'                => __( 'TOTP secret is empty.', '2fas' ),
			'totp-secret-validation'           => __( 'Invalid TOTP secret format.', '2fas' ),
			'token-invalid'                    => __( 'Wrong token entered, please enter the token again.', '2fas' ),
			'code-invalid'                     => __( 'Wrong code entered, please try again.', '2fas' ),
			'code-required'                    => __( 'Code cannot be empty.', '2fas' ),
			'code-validation'                  => __( 'Code is not in a valid format or there is no valid authentication.', '2fas' ),
			'code-invalid-cannot-retry'        => 'Wrong code has been entered. ' .
				'Please take note that token has limited lifetime. You can enter your phone number again.',
			'authentication-required'          => __( 'Please provide your phone number before you enter the code.', '2fas' ),
			'privacy-policy-required'          => __( 'Privacy policy is required to create an account.' ),

			// Trusted devices
			'trusted-device-added'             => __( 'Your browser has been added to the trusted devices list.', '2fas' ),
			'trusted-device-already-added'     => __( 'Your browser is already in the trusted devices list.', '2fas' ),
			'trusted-device-removed'           => __( 'Trusted device has been removed.', '2fas' ),
			'trusted-devices-enabled'          => __( 'Trusted devices has been enabled.', '2fas' ),
			'trusted-devices-disabled'         => __( 'Trusted devices has been disabled.', '2fas' ),

			// User enables and disables authentication method
			'totp-enabled'                     => __( 'Two-factor authentication has been enabled.', '2fas' ),
			'totp-disabled'                    => __( 'Two-factor authentication has been disabled.', '2fas' ),
			'cannot-enable-totp'               => __( 'Two-factor authentication cannot be enabled because it is not configured.', '2fas' ),
			'sms-enabled'                      => __( 'Backup codes - SMS/VMS have been enabled.', '2fas' ),
			'sms-disabled'                     => __( 'Backup codes - SMS/VMS have been disabled.', '2fas' ),
			'legacy-mode-sms-disabled'         => __( 'Two-factor authentication via SMS/VMS has been disabled. In order to use two-factor authentication 2FAS Tokens method must be enabled.', '2fas' ),
			'offline-codes-enabled'            => __( 'Backup codes - offline have been enabled.', '2fas' ),
			'offline-codes-disabled'           => __( 'Backup codes - offline have been disabled.', '2fas' ),
			'configuration-removed'            => __( 'Configuration has been removed successfully.', '2fas' ),
			'configuration-remove-error'       => __( 'Error occurred during configuration removing.', '2fas' ),
			'totp-configured'                  => __( 'Two-factor authentication has been configured and enabled.', '2fas' ),
			'sms-configured'                   => __( 'Backup codes - SMS/VMS have been configured and enabled.', '2fas' ),
			'2fa-role'                         => __( 'You cannot disable two-factor authentication because 2FA is obligatory.', '2fas' ),
			'2fa-role-remove'                  => __( 'You cannot remove configuration because 2FA is obligatory.', '2fas' ),
			'2fa-role-obligated'               => __( 'Please enable two-factor authentication because it is obligatory.', '2fas' ),
			'please-enable-totp'               => __( 'Please enable 2FAS tokens in order to disable legacy mode.', '2fas' ),
			'please-enable-obligatory-totp'    => __( 'Please enable 2FAS tokens because they are obligatory.', '2fas' ),
			'please-configure-2fa'             => __( 'Please configure two-factor authentication because it is obligatory.', '2fas' ),
			'please-configure-totp'            => __( 'Please configure 2FAS tokens in order to disable legacy mode.', '2fas' ),
			'please-configure-obligatory-totp' => __( 'Please configure 2FAS tokens because they are obligatory.', '2fas' ),

			// General errors
			'default'                          => __( 'Something went wrong. Please try again.', '2fas' ),
			'db-error'                         => __( 'Something went wrong with database.', '2fas' ),
			'client-error'                     => __( 'Could not get client data.', '2fas' ),
			'integration-error'                => __( 'Could not get integration data.', '2fas' ),
			'empty-private-key'                => __( 'Private key cannot be empty.', '2fas' ),
			'user-not-found'                   => __( 'User has not been found.', '2fas' ),
			'integration-user-not-found'       => __( 'Could not get integration user.', '2fas' ),
			'oauth-token-not-found'            => __( 'OAuth token not found.', '2fas' ),
			'entity-not-found'                 => __( 'Could not get data.', '2fas' ),
			'template-not-found'               => __( '2FAS plugin could not find a template.', '2fas' ),
			'template-compilation'             => __( 'Error occurred in 2FAS plugin during template compilation.', '2fas' ),
			'template-rendering'               => __( 'Error occurred in 2FAS plugin during template rendering.', '2fas' ),
			'authentication-expired'           => __( 'Your authentication session has expired. Please log in again.', '2fas' ),
			'authentication-limit'             => __( 'Attempt limit exceeded. Your account has been blocked for 5 minutes.', '2fas' ),
			'disabled-offline-codes'           => __( "You cannot log in with offline code because this method is disabled or you don't have any codes.", '2fas' )
		];
	}

	/**
	 * @param string $action
	 * @param string $text
	 *
	 * @return string
	 */
	private static function generate_link_to_action( $action, $text ) {
		$url = Url::create( Action_Index::SUBMENU_DASHBOARD, $action );

		return '<a href=' . $url . '>' . $text . '</a>';
	}
}
