<?php

namespace TwoFAS\Encryption;

use TwoFAS\Encryption\Exceptions\AesException;
use TwoFAS\Encryption\Interfaces\IVGenerator;

/**
 * Class AESIVGenerator
 * @package TwoFAS\Encryption
 */
class AESIVGenerator implements IVGenerator
{
    /**
     * @var string
     */
    private $cipherMethod = "AES-256-CBC";

    /**
     * Generate IV to be used in AES-256-CBC algorithm.
     *
     * @return string
     *
     * @throws AesException
     */
    public function getIV()
    {
        $bytes = openssl_random_pseudo_bytes($this->getIVLength());

        if (false === $bytes) {
            throw new AesException((string) openssl_error_string());
        }

        return $bytes;
    }

    /**
     * Return length of generated IV.
     *
     * @return int
     *
     * @throws AesException
     */
    public function getIVLength()
    {
        $length = openssl_cipher_iv_length($this->cipherMethod);

        if (false === $length) {
            throw new AesException((string) openssl_error_string());
        }

        return $length;
    }

    /**
     * Return AES-256-CBC string.
     *
     * @return string
     */
    public function getCipherMethod()
    {
        return $this->cipherMethod;
    }
}
