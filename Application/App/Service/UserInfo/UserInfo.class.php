<?php

namespace App\Service\UserInfo;

class UserInfo
{
    private static $instance;
    private $userInfo = null;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register($userInfo)
    {
        $this->userInfo = $userInfo;
        return self::$instance;
    }

    public function judgeStart()
    {
        if ($this->userInfo == null) {
            return false;
        } else {
            return true;
        }
    }

    static public function getPlatform()
    {
        return $_SERVER['HTTP_PLATFORM'];
    }

    public function start()
    {
        return $this->userInfo->start();
    }

    public function set($key, $value, $params = [])
    {
        return $this->userInfo->set($key, $value, $params);
    }

    public function get($key, $default = null)
    {
        return $this->userInfo->get($key, $default);
    }

    public function logout()
    {
        return $this->userInfo->logout();
    }

    function __call($method, $params)
    {
        return $this->userInfo->{$method}($params);
    }
}