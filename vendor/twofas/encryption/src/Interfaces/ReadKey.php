<?php

namespace TwoFAS\Encryption\Interfaces;

/**
 * Interface ReadKey
 * @package TwoFAS\Encryption\Interfaces
 */
interface ReadKey
{
    /**
     * Retrieve stored Key object.
     *
     * @return Key
     */
    public function retrieve();
}
