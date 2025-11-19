<?php

namespace App\Service\UserInfo;

class Session extends AbstractUserInfo
{
    public $data = [];
    public $token;

    public function start()
    {
        return true;
    }

    public function set($key, $value)
    {
        return session($key, $value);
    }

    public function get($key, $default = null)
    {
        return session($key);
    }

    public function logout()
    {
        return session(null);
    }
}