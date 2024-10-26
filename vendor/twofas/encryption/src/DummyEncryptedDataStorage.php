<?php

namespace TwoFAS\Encryption;

use TwoFAS\Encryption\Exceptions\AesException;
use TwoFAS\Encryption\Interfaces\Cipher;

/**
 * Sample class making use of Cipher object
 * Use it only as an example!
 * Class DummyEncryptedDataStorage
 * @package TwoFAS\Encryption
 */
class DummyEncryptedDataStorage
{
    /**
     * @var array
     */
    private $inMemoryStorage;

    /**
     * @var Cipher
     */
    private $cipher;

    /**
     * DummyEncryptedDataStorage constructor.
     *
     * @param Cipher $cipher
     */
    public function __construct(Cipher $cipher)
    {
        $this->inMemoryStorage = array();
        $this->cipher          = $cipher;
    }

    /**
     * Write data to storage in encrypted form.
     *
     * @param string  $data data to be saved in storage in encrypted form.
     * @param integer $dataId key under which data has to be stored.
     */
    public function write($data, $dataId)
    {
        $this->inMemoryStorage[$dataId] = $this->cipher->encrypt($data);
    }

    /**
     * Retrieve data under given key in decoded form.
     *
     * @param integer $dataId key under which data is stored.
     *
     * @return string data in decrypted form.
     *
     * @throws AesException
     */
    public function retrieveDecrypted($dataId)
    {
        return $this->cipher->decrypt($this->inMemoryStorage[$dataId]);
    }

    /**
     * Retrieve data under given key in encrypted form.
     *
     * @param integer $dataId key under which data is stored.
     *
     * @return string data in encrypted form.
     */
    public function retrieveEncrypted($dataId)
    {
        return $this->inMemoryStorage[$dataId];
    }
}
