<?php

namespace App\Controller;

use App\Service\UserInfo\Session;
use App\Service\UserInfo\Token;
use App\Service\UserInfo\UserInfo;
use Think\Controller;

class AppController extends Controller
{
    const JWT_KEY = 'example_key';// 这应该是一个安全的密钥
    const ALG     = 'HS256';

    public function __construct()
    {
        parent::__construct();
        header("Access-Control-Allow-Headers: Accept,DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization,Cookie,Set-Cookie,Auth,Platform");
        header("Access-Control-Expose-Headers: Auth,Platform");

        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
        header("Access-Control-Allow-Methods: 'GET, POST, OPTIONS, PUT, DELETE'");
        header("Access-Control-Allow-Credentials: true");
//        $cookie              = session_name() . '=' . session_id() . ';SameSite=None;secure;';
//        header('Set-Cookie: ' . $cookie);
//        header('Set-Cookie: PHPSESSID=' . session_id() . '; SameSite=None;');
//        setcookie('Set-Cookie', session_id(), ['samesite' => 'None', 'httponly' => true]);

    }

    public function userInfoBegin()
    {
        if (UserInfo::getPlatform() == C('VUE_APP_APP')) {
            $type = new Token();
        } else {
            $type = new Session();
        }
        return UserInfo::getInstance()->register($type);
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
