<?php

use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Controllers\Admin\Dashboard_Controller;
use TwoFAS\TwoFAS\Http\Controllers\Admin\Account_Controller;
use TwoFAS\TwoFAS\Http\Controllers\Admin\Settings_Controller;
use TwoFAS\TwoFAS\Http\Controllers\User\TOTP_Configuration_Controller;
use TwoFAS\TwoFAS\Http\Controllers\User\SMS_Configuration_Controller;
use TwoFAS\TwoFAS\Http\Controllers\User\Offline_Codes_Configuration_Controller;
use TwoFAS\TwoFAS\Http\Controllers\User\Trusted_Devices_Controller;
use TwoFAS\TwoFAS\Http\Controllers\User\Modal_Controller;
use TwoFAS\TwoFAS\Http\Controllers\Ajax\Deactivation_Controller;

return [
	'routes' => [
		Action_Index::SUBMENU_DASHBOARD => [
			Action_Index::ACTION_DISPLAY_ADMIN_MENU => [
				'controller' => Dashboard_Controller::class,
				'action'     => 'show_dashboard_page',
				'method'     => [ 'GET' ],
				'middleware' => [ 'admin', 'account_exists' ]
			],
			Action_Index::ACTION_CREATE_ACCOUNT     => [
				'controller' => Account_Controller::class,
				'action'     => 'create_account',
				'method'     => [ 'GET', 'POST' ],
				'middleware' => [ 'admin', 'account_not_exists', 'nonce' ]
			],
			Action_Index::ACTION_RESET_PASSWORD     => [
				'controller' => Account_Controller::class,
				'action'     => 'reset_password',
				'method'     => [ 'GET', 'POST' ],
				'middleware' => [ 'admin', 'account_not_exists', 'nonce' ]
			],
			Action_Index::ACTION_LOGIN              => [
				'controller' => Account_Controller::class,
				'action'     => 'login',
				'method'     => [ 'GET', 'POST' ],
				'middleware' => [ 'admin', 'account_not_exists', 'nonce' ]
			],
			Action_Index::ACTION_LOGOUT             => [
				'controller' => Account_Controller::class,
				'action'     => 'logout',
				'method'     => [ 'POST' ],
				'middleware' => [ 'admin', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_ENABLE_PLUGIN      => [
				'controller' => Dashboard_Controller::class,
				'action'     => 'enable_plugin',
				'method'     => [ 'POST' ],
				'middleware' => [ 'admin', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_DISABLE_PLUGIN     => [
				'controller' => Dashboard_Controller::class,
				'action'     => 'disable_plugin',
				'method'     => [ 'POST' ],
				'middleware' => [ 'admin', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_UPGRADE_PLAN       => [
				'controller' => Dashboard_Controller::class,
				'action'     => 'upgrade_to_premium',
				'method'     => [ 'POST' ],
				'middleware' => [ 'admin', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_DOWNGRADE_PLAN     => [
				'controller' => Dashboard_Controller::class,
				'action'     => 'downgrade_to_basic',
				'method'     => [ 'POST' ],
				'middleware' => [ 'admin', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_MIGRATE_USERS => [
				'controller' => Dashboard_Controller::class,
				'action'     => 'migrate_users',
				'method'     => [ 'POST' ],
				'middleware' => [ 'admin', 'account_exists', 'nonce', 'ajax' ]
			],
			Action_Index::ACTION_MIGRATION_USER_STATUS => [
				'controller' => Dashboard_Controller::class,
				'action'     => 'get_migration_status',
				'method'     => [ 'GET' ],
				'middleware' => [ 'admin', 'account_exists', 'ajax' ]
			],
			Action_Index::ACTION_DEFAULT            => [
				'controller' => Dashboard_Controller::class,
				'action'     => 'show_dashboard_page',
				'method'     => [ 'GET' ],
				'middleware' => [ 'admin', 'account_exists' ]
			],
		],
		Action_Index::SUBMENU_SETTINGS  => [
			Action_Index::ACTION_DISPLAY_SETTINGS => [
				'controller' => Settings_Controller::class,
				'action'     => 'show_settings_page',
				'method'     => [ 'GET' ],
				'middleware' => [ 'admin', 'account_exists' ]
			],
			Action_Index::ACTION_SAVE_ROLES       => [
				'controller' => Settings_Controller::class,
				'action'     => 'save_roles',
				'method'     => [ 'POST' ],
				'middleware' => [ 'admin', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_SAVE_LOGGING     => [
				'controller' => Settings_Controller::class,
				'action'     => 'save_logging',
				'method'     => [ 'POST' ],
				'middleware' => [ 'admin', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_SAVE_TRUSTED_DEVICES => [
				'controller' => Settings_Controller::class,
				'action'     => 'save_trusted_devices',
				'method'     => [ 'POST' ],
				'middleware' => [ 'admin', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_DEFAULT          => [
				'controller' => Settings_Controller::class,
				'action'     => 'show_settings_page',
				'method'     => [ 'GET' ],
				'middleware' => [ 'admin', 'account_exists' ]
			]
		],
		Action_Index::SUBMENU_CHANNEL   => [
			Action_Index::ACTION_CONFIGURE_TOTP            => [
				'controller' => Totp_Configuration_Controller::class,
				'action'     => 'configure',
				'method'     => [ 'GET', 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_ENABLE_TOTP               => [
				'controller' => Totp_Configuration_Controller::class,
				'action'     => 'enable',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_DISABLE_TOTP              => [
				'controller' => Totp_Configuration_Controller::class,
				'action'     => 'disable',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_REMOVE_TOTP_CONFIGURATION => [
				'controller' => Totp_Configuration_Controller::class,
				'action'     => 'remove_configuration',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'integration_user', 'nonce' ]
			],
			Action_Index::ACTION_RELOAD_QR_CODE            => [
				'controller' => Totp_Configuration_Controller::class,
				'action'     => 'reload',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'integration_user', 'nonce' ]
			],
			Action_Index::ACTION_REQUEST_AUTH_VIA_SMS      => [
				'controller' => Sms_Configuration_Controller::class,
				'action'     => 'request_auth_via_sms',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'premium_plan', 'second_factor_enabled', 'nonce' ]
			],
			Action_Index::ACTION_CONFIGURE_SMS             => [
				'controller' => Sms_Configuration_Controller::class,
				'action'     => 'configure',
				'method'     => [ 'GET', 'POST' ],
				'middleware' => [
					'user',
					'account_exists',
					'integration_user',
					'premium_plan',
					'second_factor_enabled',
					'nonce'
				]
			],
			Action_Index::ACTION_ENABLE_SMS                => [
				'controller' => Sms_Configuration_Controller::class,
				'action'     => 'enable',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'premium_plan', 'second_factor_enabled', 'nonce' ]
			],
			Action_Index::ACTION_DISABLE_SMS               => [
				'controller' => Sms_Configuration_Controller::class,
				'action'     => 'disable',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_REMOVE_SMS_CONFIGURATION  => [
				'controller' => Sms_Configuration_Controller::class,
				'action'     => 'remove_configuration',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'integration_user', 'nonce' ]
			],
			Action_Index::ACTION_CONFIGURE_OFFLINE_CODES   => [
				'controller' => Offline_Codes_Configuration_Controller::class,
				'action'     => 'show_offline_codes',
				'method'     => [ 'GET' ],
				'middleware' => [ 'user', 'account_exists', 'integration_user', 'second_factor_enabled' ]
			],
			Action_Index::ACTION_GENERATE_OFFLINE_CODES    => [
				'controller' => Offline_Codes_Configuration_Controller::class,
				'action'     => 'generate',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'integration_user', 'second_factor_enabled', 'nonce' ]
			],
			Action_Index::ACTION_ENABLE_OFFLINE_CODES      => [
				'controller' => Offline_Codes_Configuration_Controller::class,
				'action'     => 'enable',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'second_factor_enabled', 'nonce' ]
			],
			Action_Index::ACTION_DISABLE_OFFLINE_CODES     => [
				'controller' => Offline_Codes_Configuration_Controller::class,
				'action'     => 'disable',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'nonce' ]
			],
			Action_Index::ACTION_PRINT_OFFLINE_CODES       => [
				'controller' => Offline_Codes_Configuration_Controller::class,
				'action'     => 'print_codes',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'second_factor_enabled', 'nonce' ]
			],
			Action_Index::ACTION_DISPLAY_TRUSTED_DEVICES   => [
				'controller' => Trusted_Devices_Controller::class,
				'action'     => 'show_trusted_devices',
				'method'     => [ 'GET' ],
				'middleware' => [ 'user', 'account_exists', 'integration_user', 'second_factor_enabled', 'trusted_devices_enabled' ]
			],
			Action_Index::ACTION_ADD_TRUSTED_DEVICE        => [
				'controller' => Trusted_Devices_Controller::class,
				'action'     => 'add_trusted_device',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'second_factor_enabled', 'nonce', 'trusted_devices_enabled' ]
			],
			Action_Index::ACTION_REMOVE_TRUSTED_DEVICE     => [
				'controller' => Trusted_Devices_Controller::class,
				'action'     => 'remove_trusted_device',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'nonce', 'trusted_devices_enabled' ]
			],
			Action_Index::ACTION_DISABLE_RELOAD_MODAL      => [
				'controller' => Modal_Controller::class,
				'action'     => 'disable_reload',
				'method'     => [ 'POST' ],
				'middleware' => [ 'user', 'account_exists', 'nonce', 'ajax' ]
			],
			Action_Index::ACTION_DEFAULT                   => [
				'controller' => Totp_Configuration_Controller::class,
				'action'     => 'configure',
				'method'     => [ 'GET' ],
				'middleware' => [ 'user', 'account_exists' ]
			],
		],
		Action_Index::SUBMENU_AJAX      => [
			Action_Index::ACTION_SEND_DEACTIVATION_REASON => [
				'controller' => Deactivation_Controller::class,
				'action'     => 'send_deactivation_reason',
				'method'     => [ 'POST' ],
				'middleware' => [ 'admin', 'nonce' ],
			],
		]
	]
];
