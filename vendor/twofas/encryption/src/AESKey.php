<?php

namespace TwoFAS\Encryption;

use TwoFAS\Encryption\Interfaces\Key;

/**
 * Class AESKey implementing Key interface.
 * @package TwoFAS\Encryption
 */
class AESKey implements Key
{
    /**
     * String representing AESKey
     *
     * @var string
     */
    private $key;

    /**
     * AESKey constructor.
     *
     * @param string $bytes
     */
    public function __construct($bytes)
    {
        $this->key = $bytes;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->key;
    }
}