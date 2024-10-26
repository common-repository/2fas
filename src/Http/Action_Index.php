<?php

namespace TwoFAS\TwoFAS\Http;

class Action_Index {

	const PAGE_KEY          = 'page';
	const TWOFAS_ACTION_KEY = 'twofas-action';

	// Pages
	const SUBMENU_DASHBOARD = 'twofas-submenu-dashboard';
	const SUBMENU_SETTINGS  = 'twofas-submenu-settings';
	const SUBMENU_CHANNEL   = 'twofas-submenu-channel';
	const SUBMENU_AJAX      = 'twofas-submenu-ajax';

	// Admin actions
	const ACTION_DISPLAY_ADMIN_MENU    = 'display-admin-menu';
	const ACTION_CREATE_ACCOUNT        = 'create-account';
	const ACTION_RESET_PASSWORD        = 'reset-password';
	const ACTION_LOGIN                 = 'login';
	const ACTION_LOGOUT                = 'logout';
	const ACTION_ENABLE_PLUGIN         = 'enable-plugin';
	const ACTION_DISABLE_PLUGIN        = 'disable-plugin';
	const ACTION_UPGRADE_PLAN          = 'upgrade-plan';
	const ACTION_DOWNGRADE_PLAN        = 'downgrade-plan';
	const ACTION_DISPLAY_SETTINGS      = 'display-settings';
	const ACTION_SAVE_ROLES            = 'save-roles';
	const ACTION_SAVE_LOGGING          = 'save-logging';
	const ACTION_SAVE_TRUSTED_DEVICES  = 'save-trusted-devices';
	const ACTION_MIGRATE_USERS         = 'migrate-users';
	const ACTION_MIGRATION_USER_STATUS = 'migration-users-status';

	// User actions
	const ACTION_CONFIGURE_TOTP            = 'configure-totp';
	const ACTION_ENABLE_TOTP               = 'enable-totp';
	const ACTION_DISABLE_TOTP              = 'disable-totp';
	const ACTION_REMOVE_TOTP_CONFIGURATION = 'remove-totp-configuration';
	const ACTION_CONFIGURE_SMS             = 'configure-sms';
	const ACTION_ENABLE_SMS                = 'enable-sms';
	const ACTION_DISABLE_SMS               = 'disable-sms';
	const ACTION_REMOVE_SMS_CONFIGURATION  = 'remove-sms-configuration';
	const ACTION_CONFIGURE_OFFLINE_CODES   = 'configure-offline-codes';
	const ACTION_ENABLE_OFFLINE_CODES      = 'enable-offline-codes';
	const ACTION_DISABLE_OFFLINE_CODES     = 'disable-offline-codes';
	const ACTION_DISPLAY_TRUSTED_DEVICES   = 'display-trusted-devices';
	const ACTION_ADD_TRUSTED_DEVICE        = 'add-trusted-device';
	const ACTION_REMOVE_TRUSTED_DEVICE     = 'remove-trusted-device';
	const ACTION_PRINT_OFFLINE_CODES       = 'print-offline-codes';

	// Ajax Actions
	const ACTION_AUTHENTICATE_CHANNEL     = 'authenticate-channel';
	const ACTION_GENERATE_OFFLINE_CODES   = 'generate-offline-codes';
	const ACTION_RELOAD_QR_CODE           = 'reload-qr-code';
	const ACTION_DISABLE_RELOAD_MODAL     = 'disable-reload-modal';
	const ACTION_REQUEST_AUTH_VIA_SMS     = 'request-auth-via-sms';
	const ACTION_SEND_DEACTIVATION_REASON = 'send-deactivation-reason';

	// Default action
	const ACTION_DEFAULT = '';
}
