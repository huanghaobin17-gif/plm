<?php

namespace App\Service\UserInfo;

abstract class AbstractUserInfo
{
    abstract public function start();

    abstract public function set($key, $value);

    abstract public function get($key, $default = null);

    abstract public function logout();

}