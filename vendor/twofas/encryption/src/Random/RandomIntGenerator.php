<?php

namespace TwoFAS\Encryption\Random;

use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;

interface RandomIntGenerator
{
    /**
     * @param int $min
     * @param int $max
     *
     * @return int
     *
     * @throws RandomBytesGenerateException
     */
    public function generate($min, $max);
}