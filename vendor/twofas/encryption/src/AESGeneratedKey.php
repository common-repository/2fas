<?php

namespace TwoFAS\Encryption;

use TwoFAS\Encryption\Exceptions\AesException;
use TwoFAS\Encryption\Interfaces\Key;

/**
 * Class AESGeneratedKey implementing Key interface.
 *
 * @package TwoFAS\Encryption
 */
class AESGeneratedKey implements Key
{
    /**
     * String representing AESKey
     *
     * @var string
     */
    private $key;

    /**
     * Key length
     *
     * @var int
     */
    private $keyLength = 128;

    /**
     * AESGeneratedKey constructor.
     *
     * @throws AesException
     */
    public function __construct()
    {
        $this->key = openssl_random_pseudo_bytes($this->keyLength);

        if (false === $this->key) {
            throw new AesException((string) openssl_error_string());
        }
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->key;
    }
}