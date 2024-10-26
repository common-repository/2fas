<?php

namespace TwoFAS\Encryption;

use InvalidArgumentException;
use TwoFAS\Encryption\Exceptions\AesException;
use TwoFAS\Encryption\Interfaces\Cipher;
use TwoFAS\Encryption\Interfaces\IVGenerator;
use TwoFAS\Encryption\Interfaces\ReadKey;

class AESCipher implements Cipher
{
    /**
     * @var string String describing encryption algorithm used by this class.
     */
    private $cipherMethod = 'AES-256-CBC';

    /**
     * @var IVGenerator IVGenerator interface implementation.
     */
    private $ivGenerator;

    /**
     * @var ReadKey ReadKey interface implementation.
     */
    private $keyStorage;

    /**
     * AESCipher constructor.
     *
     * @param IVGenerator $ivGenerator IVGenerator interface implementation
     * @param ReadKey     $keyStorage ReadKey interface implementation.
     */
    public function __construct(IVGenerator $ivGenerator, ReadKey $keyStorage)
    {
        $this->ivGenerator = $ivGenerator;
        $this->keyStorage  = $keyStorage;
    }

    /**
     * @inheritdoc
     */
    public function getCipherMethod()
    {
        return $this->cipherMethod;
    }

    /**
     * @inheritdoc
     */
    public function encrypt($data)
    {
        // Get initialization vector and key value
        $iv  = $this->ivGenerator->getIV();
        $key = $this->keyStorage->retrieve();

        // Encrypt
        $encryptedData = openssl_encrypt($data, $this->cipherMethod, $key->getValue(), 0, $iv);

        if (false === $encryptedData) {
            throw new AesException((string) openssl_error_string());
        }

        // Encode
        $encryptedData = base64_encode($encryptedData);

        return $encryptedData . ':' . base64_encode($iv);
    }

    /**
     * @inheritdoc
     */
    public function decrypt($data)
    {
        // Retrieve key
        $key = $this->keyStorage->retrieve();

        // Obtain encrypted message and initialization vector
        $parts = explode(':', $data);

        // This cipher implementation requires that data variable holds encrypted message and initialization vector
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('
                Unable to obtain encrypted message, initialization vector pair from passed argument.
            ');
        }

        // Decrypt and encode data
        $encryptedData = base64_decode($parts[0]);
        $iv            = base64_decode($parts[1]);

        $decrypted = openssl_decrypt($encryptedData, $this->cipherMethod, $key->getValue(), 0, $iv);

        if (false === $decrypted) {
            throw new AesException((string) openssl_error_string());
        }

        return $decrypted;
    }
}