<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/3/13
 * Time: 15:24
 */
namespace Vue\Model;

use Think\Model;

class WxAccessTokenModel extends Model
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'wx_access_token';

    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        //是否开启反向代理
        if(!C('OPEN_AGENT')){
            $url = "$protocol".C('HTTP_HOST')."/wx/";
        }else{
            $url = C('AGENT_URL')."$_SERVER[REQUEST_URI]";
        }
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => C('WX_APPID'),
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
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
            $accessToken = $this->getAccessToken();
            //是否开启反向代理
            if(!C('OPEN_AGENT')){
                $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
                $res = json_decode($this->httpGet($url));
            }else{
                $gz_tz = dcurl(C('GET_JS_TICKET_URL').'?token='.$accessToken);
                $res = json_decode($gz_tz);
            }
            $ticket = $res->ticket;
            $expires_in = $res->expires_in;
            if ($ticket) {
                $tkData['expire_time']  = time() + $expires_in;
                $tkData['item']         = 'jsapi_ticket';
                $tkData['value']        = $ticket;
                $wx->data($tkData)->add();
            }
        }else{
            if (time() > $data['expire_time']) {
                $accessToken = $this->getAccessToken();
                // 如果是企业号用以下 URL 获取 ticket
                // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
                //是否开启反向代理
                if(!C('OPEN_AGENT')){
                    $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
                    $res = json_decode($this->httpGet($url));
                }else{
                    $gz_tz = dcurl(C('GET_JS_TICKET_URL').'?token='.$accessToken);
                    $res = json_decode($gz_tz);
                }
                $ticket = $res->ticket;
                $expires_in = $res->expires_in;
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

    public function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        //查找数据库token信息
        $wx = M('wx_access_token');
        $map['item'] = 'access_token';
        $data = $wx->where($map)->find();
        if(!$data){
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            //是否开启反向代理
            if(!C('OPEN_AGENT')){
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".C('WX_APPID')."&secret=".C('WX_SECRET');
                $res = json_decode($this->httpGet($url));
            }else{
                $gz_tz = dcurl(C('GET_ASSESS_TOKEN_URL'));
                $res = json_decode($gz_tz);
            }
            if($res->errcode){
               //记录日志
                write_error('./error.html', $res->errmsg);
            }
            $access_token = $res->access_token;
            $expires_in = $res->expires_in;
            if ($access_token) {
                $acData['expire_time'] = time() + $expires_in;
                $acData['value'] = $access_token;
                $acData['item']  = 'access_token';
                $wx->data($acData)->add();
            }
        }else{
            if (time() > $data['expire_time']) {
                //token过期
                // 如果是企业号用以下URL获取access_token
                // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
                //是否开启反向代理
                if(!C('OPEN_AGENT')){
                    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".C('WX_APPID')."&secret=".C('WX_SECRET');
                    $res = json_decode($this->httpGet($url));
                }else{
                    $gz_tz = dcurl(C('GET_ASSESS_TOKEN_URL'));
                    $res = json_decode($gz_tz);
                }
                if($res->errcode){
                    //记录日志
                    write_error('./error.html', $res->msg);
                }
                $access_token = $res->access_token;
                $expires_in = $res->expires_in;
                if ($access_token) {
                    $wxData['expire_time']   = time() + $expires_in;
                    $wxData['value']   = $access_token;
                    $wxWhere['item'] = 'access_token';
                    $this->where($wxWhere)->save($wxData);
                }
            } else {
                $access_token = $data['value'];
            }
        }
        return $access_token;
    }

    public function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
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

    public function get_wxuser_info($openid)
    {
        $access_token = $this->getAccessToken();
        //是否开启反向代理
        if(!C('OPEN_AGENT')){
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
            $res = json_decode($this->httpGet($url),true);
        }else{
            $gz_tz = dcurl(C('GET_WX_USER_INFO_URL').'?openid='.$openid.'&access_token='.$access_token);
            $res =  json_decode($gz_tz,true);
        }
        return $res;
    }

    public function get_wxuser_info1($openid,$access_token)
    {
        //是否开启反向代理
        if(!C('OPEN_AGENT')){
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
            $res = json_decode($this->httpGet($url),true);
        }else{
            $gz_tz = dcurl(C('GET_WX_USER_INFO_URL').'?openid='.$openid.'&access_token='.$access_token);
            $res =  json_decode($gz_tz,true);
        }
        return $res;
    }
}
