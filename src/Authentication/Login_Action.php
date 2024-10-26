<?php

namespace TwoFAS\TwoFAS\Authentication;

final class Login_Action {

	const STOP_LOGIN_PROCESS           = 'stop-login-process';
	const LOG_IN_WITH_TOTP_CODE        = 'log-in-with-totp-code';
	const LOG_IN_WITH_BACKUP_CODE      = 'log-in-with-backup-code';
	const LOG_IN_WITH_SMS_CODE         = 'log-in-with-sms-code';
	const LOG_IN_WITH_CALL_CODE        = 'log-in-with-call-code';
	const VERIFY_TOTP_CODE             = 'verify-totp-code';
	const VERIFY_BACKUP_CODE           = 'verify-backup-code';
	const VERIFY_SMS_CODE              = 'verify-sms-code';
	const VERIFY_CALL_CODE             = 'verify-call-code';
	const OPEN_NEW_SMS_AUTHENTICATION  = 'open-sms-authentication';
	const OPEN_NEW_CALL_AUTHENTICATION = 'open-call-authentication';
	const CONFIGURE                    = 'configure';
	const TOTP_CONFIRMATION            = 'confirm-totp';
	const TOTP_RESET                   = 'reset-totp';
}
