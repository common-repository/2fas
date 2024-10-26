<?php

namespace TwoFAS\Encryption\Random;

use OutOfRangeException;

/**
 * This is a value object that stores random string which can be converted to lower/upper chars or to base64.
 *
 * @package TwoFAS\Encryption\Random
 */
class Str
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct($value = '')
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function length()
    {
        return mb_strlen($this->value, 'UTF-8');
    }

    /**
     * @param int $index
     *
     * @return string
     *
     * @throws OutOfRangeException
     */
    public function pick($index)
    {
        if ($index >= $this->length()) {
            throw new OutOfRangeException('Index of char is out of range.');
        }

        return $this->value[$index];
    }

    /**
     * @param string $string
     *
     * @return Str
     */
    public function concat($string)
    {
        return new self($this->value . $string);
    }

    /**
     * @return Str
     */
    public function toUpper()
    {
        return new self(strtoupper($this->value));
    }

    /**
     * @return Str
     */
    public function toLower()
    {
        return new self(strtolower($this->value));
    }

    /**
     * @return Str
     */
    public function toBase64()
    {
        return new self(base64_encode($this->value));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}