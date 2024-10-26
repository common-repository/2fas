<?php

namespace TwoFAS\Encryption\Random;

use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;

class RandomStringGenerator
{
    const LETTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const DIGITS  = '0123456789';
    const SYMBOLS = '!#$%&()*+,-./:;<=>?@[]^_`{|}~';

    /**
     * @var RandomIntGenerator
     */
    private $intGenerator;

    /**
     * @param RandomIntGenerator $intGenerator
     */
    public function __construct(RandomIntGenerator $intGenerator)
    {
        $this->intGenerator = $intGenerator;
    }

    /**
     * @param int $size
     *
     * @return Str
     *
     * @throws RandomBytesGenerateException
     */
    public function string($size)
    {
        return $this->getRandom(new Str(self::LETTERS . self::DIGITS . self::SYMBOLS), $size);
    }

    /**
     * @param int $size
     *
     * @return Str
     *
     * @throws RandomBytesGenerateException
     */
    public function alphaNum($size)
    {
        return $this->getRandom(new Str(self::LETTERS . self::DIGITS), $size);
    }

    /**
     * @param int $size
     *
     * @return Str
     *
     * @throws RandomBytesGenerateException
     */
    public function symbols($size)
    {
        return $this->getRandom(new Str(self::SYMBOLS), $size);
    }

    /**
     * @param Str $alphabet
     * @param int $size
     *
     * @return Str
     *
     * @throws RandomBytesGenerateException
     */
    private function getRandom(Str $alphabet, $size)
    {
        $string = new Str();

        if ($size < 1) {
            return $string;
        }

        while ($string->length() < $size) {
            $index  = $this->intGenerator->generate(0, $alphabet->length() - 1);
            $string = $string->concat($alphabet->pick($index));
        }

        return $string;
    }
}