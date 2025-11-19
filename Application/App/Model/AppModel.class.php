<?php

namespace App\Model;

use App\Service\UserInfo\Token;
use App\Service\UserInfo\UserInfo;

class AppModel extends CommonModel
{
    const JWT_KEY = 'example_key';// 这应该是一个安全的密钥
    const ALG     = 'HS256';

    public function resolveToken()
    {
//        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
//        header("Access-Control-Expose-Headers: Auth");
//        if (empty($_SERVER['HTTPS']) && empty(session())) {
//            $sessionFilePath = session_save_path() . '/sess_' . $_SERVER['HTTP_AUTH'];
//            if (file_exists($sessionFilePath)) {
//                $sessionData = file_get_contents($sessionFilePath);
//                session_decode($sessionData);
//                unlink($sessionFilePath);
//            }
//            header("Auth:" . session_id());

        UserInfo::getInstance(new Token())->checkUser($_SERVER['HTTP_AUTH']);
//        }
    }

    public function createToken($user)
    {
        $token = $user['userid'];

        return $token;
    }

    public function checkToken($token, $tokenData)
    {
        $res = true;

        return $res;
    }

    public function encodeJWT($data)
    {
        require APP_PATH . "/../ThinkPHP/vendor/autoload.php";
        return \Firebase\JWT\JWT::encode($data, self::JWT_KEY, self::ALG);
    }

    public function decodeJWT($jwt)
    {
        $ret = false;
        require APP_PATH . "/../ThinkPHP/vendor/autoload.php";
        try {
            $ret = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key(self::JWT_KEY, self::ALG));
        } catch (\Firebase\JWT\ExpiredException $e) {
//            echo 'Caught exception: ', $e->getMessage(), "\n";
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
//            echo 'Caught exception: ', $e->getMessage(), "\n";
        } catch (\Firebase\JWT\BeforeValidException $e) {
//            echo 'Caught exception: ', $e->getMessage(), "\n";
        } catch (\UnexpectedValueException $e) {
//            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
        return $ret;
    }
}