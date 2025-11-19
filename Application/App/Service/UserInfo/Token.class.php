<?php

namespace App\Service\UserInfo;

class Token extends AbstractUserInfo
{
    public $data = [];
    public $token;

    public function start()
    {
        $result = false;
        $model  = new \App\Model\AppModel();
        $token  = $model->DB_get_one('user_app_token', '*', ['token' => $_SERVER['HTTP_AUTH'], 'status' => 1]);
        if (!empty($token)) {
            $this->token                = $token;
            $this->data                 = json_decode($token['session'], true);
            $this->data['app_platform'] = $_SERVER['HTTP_PLATFORM'];
            $result                     = true;
        }
        return $result;
    }

    public function set($key, $value, $params = [])
    {
        $this->data[$key] = $value;
        if (!empty($params['update'])) {
            $model = new \App\Model\AppModel();
            $model->updateData('user_app_token',
                ['session' => json_encode($this->data, JSON_UNESCAPED_UNICODE)], ['userid' => $this->token['userid']]);
        }
    }

    public function get($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return $default;
        }
    }

    public function getAll()
    {
        return $this->data;
    }

    public function logout()
    {
        $model = new \App\Model\AppModel();
        return $model->updateData('user_app_token', ['userid' => $this->token['userid']], ['status' => 0]);
    }
}