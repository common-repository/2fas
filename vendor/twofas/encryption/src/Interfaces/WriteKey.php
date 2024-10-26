<?php

namespace TwoFAS\Encryption\Interfaces;

/**
 * Interface WriteKey
 * @package TwoFAS\Encryption\Interfaces
 */
interface WriteKey
{
    /**
     * Store key in key storage so it can be retrieved for future use
     *
     * @param Key $key
     */
    public function store(Key $key);
}
