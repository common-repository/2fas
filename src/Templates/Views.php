<?php

namespace TwoFAS\TwoFAS\Templates;

class Views {

	// Dashboard
	const ADMIN_MENU         = 'dashboard/admin/admin.html.twig';
	const ADMIN_SETTINGS     = 'dashboard/admin/settings.html.twig';
	const CREATE_ACCOUNT     = 'dashboard/admin/registration.html.twig';
	const LOGIN_FORM         = 'dashboard/admin/login.html.twig';
	const RESET_PASSWORD     = 'dashboard/admin/password-reset.html.twig';
	const CONFIGURE_TOTP     = 'dashboard/user/tokens.html.twig';
	const BACKUP_CODES       = 'dashboard/user/offline-backup.html.twig';
	const CONFIGURE_SMS      = 'dashboard/user/sms-backup.html.twig';
	const TRUSTED_DEVICES    = 'dashboard/user/trusted-devices.html.twig';
	const ERROR              = 'dashboard/error.html.twig';
	const FORBIDDEN          = 'dashboard/forbidden.html.twig';
	const NOT_ENABLED        = 'dashboard/user/plugin-not-configured.html.twig';
	const NOT_FOUND          = 'dashboard/not-found.html.twig';
	const NOT_ALLOWED        = 'dashboard/not-allowed.html.twig';
	const PRINT_BACKUP_CODES = 'offline-codes-preview.html.twig';
	const NOTICES            = 'dashboard/notices.html.twig';

	// Login page
	const TOTP_AUTHENTICATION_PAGE   = 'login/totp_authentication_page.html.twig';
	const BACKUP_AUTHENTICATION_PAGE = 'login/backup_authentication_page.html.twig';
	const SMS_AUTHENTICATION_PAGE    = 'login/sms_authentication_page.html.twig';
	const CALL_AUTHENTICATION_PAGE   = 'login/call_authentication_page.html.twig';
}
