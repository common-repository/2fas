<?php

namespace TwoFAS\Encryption;

use TwoFAS\Encryption\Exceptions\AesException;
use TwoFAS\Encryption\Interfaces\Cipher;
use TwoFAS\Encryption\Interfaces\ReadKey;

class Cryptographer
{
    /**
     * @var Cipher
     */
    private $cipher;

    /**
     * Cryptographer constructor.
     *
     * @param Cipher $cipher
     */
    public function __construct(Cipher $cipher)
    {
        $this->cipher = $cipher;
    }

    /**
     * @param ReadKey $keyStorage
     *
     * @return Cryptographer
     */
    public static function getInstance(ReadKey $keyStorage)
    {
        return new self(new AESCipher(new AESIVGenerator(), $keyStorage));
    }

    /**
     * Encrypt message and return it in encrypted form
     *
     * @param string $data Message to be encrypted
     *
     * @return string|null Encrypted message
     *
     * @throws AesException
     */
    public function encrypt($data)
    {
        if (is_null($data)) {
            return null;
        }
        return $this->cipher->encrypt($data);
    }

    /**
     * Decrypt message and return it in decrypted form
     *
     * @param string $data Message to be decrypted
     *
     * @return string|null Decrypted message
     *
     * @throws AesException
     */
    public function decrypt($data)
    {
        if (is_null($data)) {
            return null;
        }
        return $this->cipher->decrypt($data);
    }
}