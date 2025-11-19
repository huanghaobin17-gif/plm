<?php

namespace Admin\Controller\Monitor;

use Admin\Common\CiAnTongAPI;
use Admin\Controller\Login\CheckLoginController;

class MonitorController extends CheckLoginController
{
    public function getMonitorList()
    {
        if (IS_POST) {
            // 排序整理
            $limit = I('post.limit') ? I('post.limit') : 100;
            $page = I('post.page') ? I('post.page') : 1;
            $offset = ($page - 1) * $limit;
            $order = I('post.order') ? I('post.order') : 'desc';
            $sort = I('post.sort') ? I('post.sort') : 'id';
            // 整理查询
            $where = [];
            $all_requests_data = I('post.');
            if (isset($all_requests_data['assnum'])) {
                $where['assnum'] = ['like', "%$all_requests_data[assnum]%"];
            }
            if (isset($all_requests_data['assets'])) {
                $where['assets'] = ['like', "%$all_requests_data[assets]%"];
            }
            $join = [];
            $join[] = 'LEFT JOIN sb_assets_info ON sb_assets_info.assid = sb_monitor.assid';
            // 一个拿总数
            $total = M('monitor')->field('sb_assets_info.assnum,sb_assets_info.assets,sb_monitor.*')->join($join)->where($where)->order($sort . ' ' . $order)->limit($offset . ',' . $limit)->count();
            // 一个负责查数据
            $data = M('monitor')->field('sb_assets_info.assnum,sb_assets_info.assets,sb_monitor.*')->join($join)->where($where)->order($sort . ' ' . $order)->limit($offset . ',' . $limit)->select();
            // 顺手把assid也全部查出来 到时候勾选的时候有用 不用再查一次
            $all_select = M('monitor')->field('sb_assets_info.assnum')->join($join)->where($where)->select();
            $all_assnum = array_column($all_select, 'assnum');
            $res = [
                'msg' => $total == 0 ? '暂无数据' : '获取成功',
                'total' => $total,
                'code' => $total == 0 ? 400 : 200,
                'rows' => $data,
                'all_assnum' => $all_assnum
            ];
            $this->ajaxReturn($res);
        } else {
            $this->assign('getMonitorList', get_url());
            $this->display();
        }
    }


    // 下载设备数据
    public function downloadAssetsData()
    {
        $request_data = I('post.');
        $all_assnums = explode(',', $request_data['assnum']);
        foreach ($all_assnums as $assnum) {
            $this->assetTagDailyStatistics($assnum, $request_data['startTime'], $request_data['endTime']);
            $this->assettagoperationstatusinformation($assnum);
        }

    }


    //资产标签每日统计数据(资产编码)
    private function assetTagDailyStatistics($assnum, $startTime, $endTime)
    {
        // 实例化
        $api = new CiAnTongAPI();
        // 端点
        $endpoint = "/api/device/open/energy/code";
        // 数据
        $data = array(
            'key' => $api->key,
            'code' => $assnum,
            'startTime' => $startTime,
            'endTime' => $endTime
        );
//                var_dump($data);exit();
        $response = $api->sendRequest($endpoint, $data);
        // 插日志记录
        $add_log_data = [
            'system' => '中科物联网',
            'interface' => '资产标签每日统计数据(资产编码)',
            'status' => json_decode($response, true)['code'],
            'response' => $response,
            'create_at' => getHandleDate(time())
        ];
        M('interface_log')->add($add_log_data);
        // 插入监控表
        $update_monitor_data = json_decode($response, true);
        $assets_info = M('assets_info')->where(['assnum'=>$assnum])->find();
        M('monitor')->where(['assid'=>$assets_info['assid']])->save($update_monitor_data['data']);
//        var_dump($response);exit();
//                if ($response) {
//                    echo $response;
//                } else {
//                    echo "请求失败";
//                }
//        $this->ajaxReturn($data);
    }

    //资产标签运行状态信息接口
    private function assettagoperationstatusinformation($assnum)
    {
        // 实例化
        $api = new CiAnTongAPI();
        // 端点
        $endpoint = "/api/device/open/assetStatus/detail";
        // 数据
        $data = array(
            'key' => $api->key,
            'code' => $assnum
//                    'label' => "322079905374",
        );
        $response = $api->sendRequest($endpoint, $data);
        // 插日志记录
        $add_log_data = [
            'system' => '中科物联网',
            'interface' => '资产标签运行状态信息接口(资产编码)',
            'status' => json_decode($response, true)['code'],
            'response' => $response,
            'create_at' => getHandleDate(time())
        ];
        M('interface_log')->add($add_log_data);
        // 插入监控表
        $update_monitor_data = json_decode($response, true);
        $assets_info = M('assets_info')->where(['assnum'=>$assnum])->find();
        M('monitor')->where(['assid'=>$assets_info['assid']])->save($update_monitor_data['data']);
//        var_dump($response);
//                if ($response) {
//                    echo $response;
//                } else {
//                    echo "请求失败";
//                }
//        $this->ajaxReturn($data);
    }

    //资产标签状态统计数量
    public function AssetTagStatusStatisticsQuantity()
    {
        // 对接接口
        $api = new CiAnTongAPI();
        $endpoint = "/api/device/open/assetCount/v2";
        $data = array(
            'key' => $api->key,
        );
        // 接口获取
        $response = $api->sendRequest($endpoint, $data);
        // 插日志记录
        $add_log_data = [
            'system' => '中科物联网',
            'interface' => '资产标签状态统计数量',
            'status' => json_decode($response, true)['code'],
            'response' => $response,
            'create_at' => getHandleDate(time())
        ];
        M('interface_log')->add($add_log_data);
        // 直接写进一个静态文件算了 表没必要建了
        $fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/Public/json/data.json', "w");
        if (!$fp) {
            echo '文件读写失败';
            return;
        }
        fwrite($fp, $response);
        fclose($fp);
        echo $response;
//        var_dump($data);

//                if ($response) {
//                    echo $response;
//                } else {
//                    echo "请求失败";
//                }
//                $this->ajaxReturn($data);
    }

}
