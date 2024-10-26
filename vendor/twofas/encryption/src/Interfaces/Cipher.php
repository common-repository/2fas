<?php

namespace TwoFAS\Encryption\Interfaces;
use TwoFAS\Encryption\Exceptions\AesException;

/**
 * Interface Cipher
 * @package TwoFAS\Encryption\Interfaces
 */
interface Cipher
{
    /**
     * Return string representing encryption algorithm used in Cipher.
     *
     * @return string
     */
    public function getCipherMethod();

    /**
     * Encrypt message and return it in encrypted form
     *
     * @param string $data Message to be encrypted
     *
     * @return string Encrypted message
     *
     * @throws AesException
     */
    public function encrypt($data);

    /**
     * Decrypt message and return it in decrypted form
     *
     * @param string $data Message to be decrypted
     *
     * @return string Decrypted message
     *
     * @throws AesException
     */
    public function decrypt($data);
}
