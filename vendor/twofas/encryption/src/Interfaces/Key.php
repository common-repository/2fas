<?php

namespace TwoFAS\Encryption\Interfaces;

/**
 * Interface Key
 * @package TwoFAS\Encryption\Interfaces
 */
interface Key
{
    /**
     * Return symmetric key as string
     *
     * @return string
     */
    public function getValue();
}
