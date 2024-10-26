<?php

namespace TwoFAS\ValidationRules;

class ValidationRules
{
    const STRING                                 = 'validation.string';
    const DIGITS                                 = 'validation.digits';
    const BOOL                                   = 'validation.boolean';
    const ARR                                    = 'validation.array';
    const ACCEPTED                               = 'validation.accepted';
    const CONFIRMED                              = 'validation.confirmed';
    const REQUIRED_WITH                          = 'validation.required_with';
    const REQUIRED_IF                            = 'validation.required_if';
    const REQUIRED                               = 'validation.required';
    const UNIQUE_PHONE_NUMBER                    = 'validation.unique_phone_number';
    const UNIQUE                                 = 'validation.unique';
    const EMAIL                                  = 'validation.email';
    const NULL_OR_FILLED                         = 'validation.null_or_filled';
    const NULL_OR_PRESENT                        = 'validation.null_or_present';
    const MAX                                    = 'validation.max';
    const MIN                                    = 'validation.min';
    const SIZE                                   = 'validation.size';
    const REGEX                                  = 'validation.regex';
    const TOTP_SECRET                            = 'validation.totp_secret';
    const AES_CIPHER                             = 'validation.aes_cipher';
    const TWOFAS_FORMATTABLE                     = 'validation.two_f_a_s_formattable';
    const BACKUP_CODE                            = 'validation.backup_code';
    const PUSHER_SOCKET_ID                       = 'validation.pusher_socket_id';
    const PUSHER_CHANNEL_NAME                    = 'validation.pusher_channel_name';
    const INTEGRATION_CHANNEL_NAME               = 'validation.private_integration_channel';
    const CHANNEL_ENABLING                       = 'validation.channel_enabling_rules';
    const CHANNEL_DISABLING                      = 'validation.channel_disabling_rules';
    const CLIENT_NO_INTEGRATION_WITH_PAID_METHOD = 'validation.client_has_no_integrations_with_paid_method_if_card_is_primary';
    const CLIENT_VALID_SOURCE                    = 'validation.valid_client_source';
    const CLIENT_PASSWORD_EQUALS                 = 'validation.client_password_equals';
    const KEY_VALID                              = 'validation.valid_key_type';
    const UNSUPPORTED                            = 'validation.unsupported';

    /**
     * @param string $rule
     *
     * @return string
     */
    public static function getContainingRule($rule)
    {
        $reflection = new \ReflectionClass(__CLASS__);

        foreach ($reflection->getConstants() as $constant => $value) {
            if (preg_match('/^' . str_replace('.', '\.', $value) . '(\.[a-zA-Z0-9_]+)*(?::{1}[a-zA-Z0-9,_]*)?$/', $rule)) {
                return $value;
            }
        }

        return ValidationRules::UNSUPPORTED;
    }
}
