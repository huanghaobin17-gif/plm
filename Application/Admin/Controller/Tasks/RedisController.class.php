<?php

/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/4/10
 * Time: 15:56
 */

namespace Admin\Controller\Tasks;

use Admin\Model\AssetsInfoModel;
use Mobile\Model\WxAccessTokenModel;

class RedisController
{

    public function redisSendWechat()
    {
        $addid = 4;
        $templateId = C('WX_TEMPLATES')['XTYXJB'];
        if(C('USE_VUE_WECHAT_VERSION')){
            $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME').'/#'. C('VUE_NAME') . '/Notice/showNotice?id=' . $addid;
        }else{
            $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Notice/showNotice.html?id=' . $addid;
        }
        $wechat_data = [
            'first' => ['value' => urlencode("您好,收到一条系统公告"), 'color' => "#FF0000"],
            'keyword1' => ['value' => urlencode(session('current_hospitalname'))],
            'keyword2' => ['value' => urlencode('test')],
            'keyword3' => ['value' => urlencode(date('Y-m-d H:i:s'))],
            'remark' => ['value' => urlencode('详情请点击查看')],
        ];
        $assModel = new AssetsInfoModel();
        $toUser = $assModel->DB_get_all('user', 'openid', ['is_delete' => 0, 'status' => 1, 'job_hospitalid' => session('current_hospitalid')], '', 'userid asc');
        $alerady_send = [];
        foreach ($toUser as $v) {
            if ($v['openid'] && !in_array($v['openid'], $alerady_send)) {
                $alerady_send[] = $v['openid'];
                $this->pushData($v['openid'], $templateId, $wechat_data, $redecturl);
            }
        }
    }

    public function pushData($openid, $templateId, $wechat_data, $redecturl)
    {
//        $redis = new \Redis();
//        $res = $redis->connect('127.0.0.1','6379');
//        $i = 0;
//        $data = [];
//        while ($i < 5){
//            $data[$i]['name'] = 'zhangsan_'.($i+1);
//            $data[$i]['age'] = 'age_'.($i+1);
//            $data[$i]['adress'] = 'address_'.($i+1);
//            $i++;
//        }
//        $data = serialize($data);
//        $redis->lPush('tecev_nb_test_data',$data);
//        $res = $redis->lrange('tecev_nb_test_data',0,-1); //返回全部数据，数组形式
//        echo '<pre>';
//        var_dump($res);
//        exit;

        $redis = new \Redis();
        $res = $redis->connect('127.0.0.1', '6379');
        if(!$res){
            exit('无法启动redis');
        }
        //入队
        $data = [
            'openid' => $openid,
            'templateId' => $templateId,
            'wechat_data' => $wechat_data,
            'redecturl' => $redecturl,
        ];
        $data = json_encode($data);
        $redis->lpush(C('DB_NAME'), $data);
    }

    public function getLists()
    {
        $assModel = new AssetsInfoModel();
        $redis = new \Redis();
        $res = $redis->connect('127.0.0.1', '6379');
        if(!$res){
            exit('无法启动redis');
        }
        $n = 0;
        $wxModel = new WxAccessTokenModel();
        $wxurl = '';
        while (true) {
            $res = $redis->rpop(C('DB_NAME'));
            if ($res) {
                $value = json_decode($res, true);
                if ($n == 0) {
                    //第一次curl发送，预防token过期
                    $assModel->sendMsgToOnUserByWechat('oSYrdsu68E4GetWsrQ76dmuGo2M0', $value['templateId'], $value['redecturl'], $value['wechat_data']);
                    $n++;
                    $access_token = $wxModel->getAccessToken();
                    $wxurl = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
                } else {
                    //如果还有其他的直接发送
                    $template = array(
                        'touser' => 'oSYrdsu68E4GetWsrQ76dmuGo2M0',
                        'template_id' => $value['templateId'],
                        'url' => $value['redecturl'],
                        'topcolor' => "#7B68EE",
                        'data' => $value['wechat_data']
                    );
                    $template = json_encode($template);
                    $template = urldecode($template);
                    fsock($wxurl, $template);
                }
            } else {
                break;
            }
        }

    }
}
