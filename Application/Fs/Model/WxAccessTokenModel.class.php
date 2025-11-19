<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/3/13
 * Time: 15:24
 */
namespace Fs\Model;

use Think\Model;

class WxAccessTokenModel extends Model
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'wx_access_token';

    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        //$url = "$protocol".C('HTTP_HOST')."$_SERVER[REQUEST_URI]";
        $url = "$protocol".C('HTTP_HOST').C('FS_FOLDER_NAME')."/";
        //$timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        //$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        //$signature = sha1($string);
        $signPackage = array(
            "appId"     => C('WX_APPID'),
            "nonceStr"  => $nonceStr,
            //"timestamp" => $timestamp,
            "url"       => $url,
            //"signature" => $signature,
            //"rawString" => $string
            "jsapiTicket" => $jsapiTicket
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $wx = M('wx_access_token');
        $map['item'] = 'jsapi_ticket';
        $data = $wx->where($map)->find();
        if(!$data){
            $token = $this->getAccessToken('tenant_access_token');
            $url = "https://open.feishu.cn/open-apis/jssdk/ticket/get";
            $header[] = 'Authorization:Bearer '.$token;
            $header[] = 'Content-Type:application/json; charset=utf-8';
            $res = dcurl($url,[],$header);
            $res = json_decode($res,true);
            if ($res['code'] != 0) {
                return false;
            }
            $ticket = $res['data']['ticket'];
            $expires_in = $res['data']['expire_in'];
            if ($ticket) {
                $tkData['expire_time']  = time() + $expires_in;
                $tkData['item']         = 'jsapi_ticket';
                $tkData['value']        = $ticket;
                $wx->data($tkData)->add();
            }
        }else{
            if (time() > $data['expire_time']) {
                $token = $this->getAccessToken('tenant_access_token');
                $url = "https://open.feishu.cn/open-apis/jssdk/ticket/get";
                $header[] = 'Authorization:Bearer '.$token;
                $header[] = 'Content-Type:application/json; charset=utf-8';
                $res = dcurl($url,[],$header);
                $res = json_decode($res,true);
                if ($res['code'] != 0) {
                    return false;
                }
                $ticket = $res['data']['ticket'];
                $expires_in = $res['data']['expire_in'];
                if ($ticket) {
                    $wxData['expire_time']   = time() + $expires_in;
                    $wxData['value']   = $ticket;
                    $wxWhere['item'] = 'jsapi_ticket';
                    $this->where($wxWhere)->save($wxData);
                }
            } else {
                $ticket = $data['value'];
            }
        }
        return $ticket;
    }

    public function getAccessToken($token_type) {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        //查找数据库token信息
        $wx = M('wx_access_token');
        $map['item'] = $token_type;
        $data = $wx->where($map)->find();
        if(!$data){
            $param['app_id'] = C('WX_APPID'); //AppID
            $param['app_secret'] = C('WX_SECRET'); //AppSecret
            $header[] = 'Content-Type:application/json; charset=utf-8';
            $url = 'https://open.feishu.cn/open-apis/auth/v3/'.$token_type.'/internal';
            $res = dcurl($url,json_encode($param),$header);
            $res = json_decode($res,true);
            if ($res['code'] != 0) {
                return false;
            }
            $token = $res[$token_type];
            $expires_in = $res['expire'];
            if ($token) {
                $acData['expire_time'] = time() + $expires_in;
                $acData['value'] = $token;
                $acData['item']  = $token_type;
                $wx->data($acData)->add();
            }
        }else{
            if (time() > $data['expire_time']) {
                //token过期
                $param['app_id'] = C('WX_APPID'); //AppID
                $param['app_secret'] = C('WX_SECRET'); //AppSecret
                $header[] = 'Content-Type:application/json; charset=utf-8';
                $url = 'https://open.feishu.cn/open-apis/auth/v3/'.$token_type.'/internal';
                $res = dcurl($url,json_encode($param),$header);
                $res = json_decode($res,true);
                if ($res['code'] != 0) {
                    return false;
                }
                $token = $res[$token_type];
                $expires_in = $res['expire'];
                if ($token) {
                    $wxData['expire_time']   = time() + $expires_in;
                    $wxData['value']   = $token;
                    $wxWhere['item'] = $token_type;
                    $this->where($wxWhere)->save($wxData);
                }
            } else {
                $token = $data['value'];
            }
        }
        return $token;
    }

    public function httpGet($url,$header=[]) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 设置请求头
        if($header){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($curl, CURLOPT_CAINFO,dirname(__FILE__).'\cacert.pem');
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    private function get_php_file($filename) {
        return trim(substr(file_get_contents($filename), 15));
    }
    private function set_php_file($filename, $content) {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }

    //获取飞书用户其他信息
    public function get_feishu_userinfo($user_access_token)
    {
        $url = 'https://open.feishu.cn/open-apis/authen/v1/user_info';
        $header[] = 'Authorization:Bearer '.$user_access_token;
        $res = json_decode($this->httpGet($url,$header),true);
        if ($res['code'] != 0) {
            return false;
        }
        return $res['data'];
    }

    public function get_wxuser_info1($openid,$access_token)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $res = json_decode($this->httpGet($url),true);
        return $res;
    }
}
