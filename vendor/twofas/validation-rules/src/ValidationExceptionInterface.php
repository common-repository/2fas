<?php

namespace TwoFAS\ValidationRules;

interface ValidationExceptionInterface
{
    /**
     * @return array
     */
    public function getErrors();

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasKey($key);

    /**
     * @param string $key
     * @param string $rule
     *
     * @return bool
     */
    public function hasError($key, $rule);

    /**
     * @param string $key
     *
     * @return array|null
     */
    public function getError($key);

    /**
     * @param string $key
     *
     * @return array|null
     */
    public function getBareError($key);
}