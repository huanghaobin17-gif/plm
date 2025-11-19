<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/25
 * Time: 9:44
 */

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;

class AssetsScrapModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'assets_scrap';
    private $MODULE = 'Assets';
    private $Controller = 'Scrap';


    //获取报废申请列表
    public function getAssetsLists()
    {
        $departids            = session('departid');
        $limit                = I('post.limit') ? I('post.limit') : 10;
        $page                 = I('post.page') ? I('post.page') : 1;
        $offset               = ($page - 1) * $limit;
        $order                = I('POST.order');
        $sort                 = I('POST.sort');
        $assets               = I('POST.getApplyListAssets');
        $assetsNum            = I('POST.getApplyListAssetsNum');
        $assetsCat            = I('POST.getApplyListCategory');
        $did                  = I('POST.departid');
        $assetsStatus         = I('POST.status');
        $hospital_id          = I('POST.hospital_id');
        $where['status'][0]   = 'NOTIN';
        $where['status'][1][] = C('ASSETS_STATUS_SCRAP');
        $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$departids) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if ($did) {
            $where['departid'] = ['IN', $did];
        } else {
            $where['departid'] = ['IN', $departids];
        }
        if ($hospital_id) {
            $where['hospital_id'] = $hospital_id;
        } else {
            $hospital_id          = session('current_hospitalid');
            $where['hospital_id'] = $hospital_id;
        }
        if ($assets) {
            //设备名称搜索
            $where['assets'] = ['LIKE', '%' . $assets . '%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['assnum'] = ['LIKE', '%' . $assetsNum . '%'];

        }
        if ($assetsCat) {
            //分类搜索
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['LIKE', '%' . $assetsCat . '%'];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['catid']       = ['IN', $catids];
        }
        if ($assetsStatus == 2) {
            $where['status'] = C('ASSETS_STATUS_SCRAP');
        } elseif ($assetsStatus == 1) {
            $where['status'] = ['NEQ', C('ASSETS_STATUS_SCRAP')];
        }
        //设备原值区间（小）
        if (I('post.buy_priceMin')) {
            $where['buy_price'][] = ['EGT', I('post.buy_priceMin')];
        }
        //设备原值区间（大）
        if (I('post.buy_priceMax')) {
            $where['buy_price'][] = ['ELT', I('post.buy_priceMax')];
        }
//        //报废设备是否到期判断
//        if(I('post.license')){
//            switch(I('post.license')){
//                case 1:
//                    $where[] = ['DATE_ADD(opendate, INTERVAL expected_life YEAR) < NOW() AND opendate IS NOT NULL'];
//                    break;
//                case 2:
//                    $where[] = ['DATE_ADD(opendate, INTERVAL expected_life YEAR) >= NOW() AND opendate IS NOT NULL'];
//                    break;
//            }
//        }
        $catname     = [];
        $departname  = [];
        $baseSetting = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        //查询当前用户是否有权限进行申请报废
        $Apply              = get_menu($this->MODULE, $this->Controller, 'applyScrap');
        $applyList          = get_menu($this->MODULE, $this->Controller, 'getApplyList');
        $where['is_delete'] = C('NO_STATUS');
        $join               = "LEFT JOIN sb_assets_scrap AS B ON A.assid = B.assid";
        $fields             = 'A.assid,A.acid,A.afid,A.assets,A.catid,A.assnum,A.assorignum,A.status,A.model,A.departid,A.factorydate,A.opendate,A.storage_date,A.expected_life,A.buy_price,A.adduser,A.adddate,A.unit,B.scrid,B.approve_status,B.retrial_status';
        $total              = $this->DB_get_count('assets_info', $where);
        $asinfo             = $this->DB_get_all_join('assets_info', 'A', $fields, $join, $where, '',
            $sort . ' ' . $order, $offset . "," . $limit);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($asinfo as $k => $v) {
            if ($v['retrial_status'] == 3 && $v['status'] != 0) {
                //unset($asinfo[$k]);
                $asinfo[$k] = [];
                continue;
            }
            if ($v['assnum'] == $asinfo[$k - 1]['assnum'] || $v['assnum'] == $assnum) {
                $asinfo[$k] = [];
                $assnum     = $v['assnum'];
                continue;
            }
            if ((float)$v['buy_price'] >= (float)$baseSetting['assets']['assets_scrap_overPrice']['value']) {
                $asinfo[$k]['buy_price'] = '<span style="color: #FF5722;">' . (float)$v['buy_price'] . '</span>';
            } else {
                $asinfo[$k]['buy_price'] = (float)$v['buy_price'];
            }
            if (!$showPrice) {
                $asinfo[$k]['buy_price'] = '***';
            }
            $asinfo[$k]['factorydate']   = HandleEmptyNull($v['factorydate']);
            $asinfo[$k]['opendate']      = HandleEmptyNull($v['opendate']);
            $asinfo[$k]['storage_date']  = HandleEmptyNull($v['storage_date']);
            $asinfo[$k]['address']       = $departname[$v['departid']]['address'];
            $asinfo[$k]['department']    = $departname[$v['departid']]['department'];
            $asinfo[$k]['category']      = $catname[$v['catid']]['category'];
            $asinfo[$k]['expected_life'] = (int)$v['expected_life'];
            if ($v['unit'] == '') {
                $asinfo[$k]['unit'] = '-';
            }
            $html = '<div class="layui-btn-group">';
            $html .= $this->returnListLink('设备详情', C('SHOWASSETS_ACTION_URL'), 'showAssets',
                C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($v['status'] == C('ASSETS_STATUS_USE')) {
                //在用
                if ($Apply) {
                    $html .= $this->returnListLink('申请报废', $Apply['actionurl'], 'apply',
                        C('BTN_CURRENCY') . ' layui-btn-normal');
                }
            }
            if ($v['status'] == C('ASSETS_STATUS_REPAIR')) {
                //维修中
                $html .= $this->returnListLink(C('ASSETS_STATUS_REPAIR_NAME'), '', '',
                    C('BTN_CURRENCY') . ' layui-btn-disabled');
            }
            if ($v['status'] == C('ASSETS_STATUS_OUTSIDE_ON')) {
                //外调中
                $html .= $this->returnListLink(C('ASSETS_STATUS_OUTSIDE_ON_NAME'), '', '',
                    C('BTN_CURRENCY') . ' layui-btn-disabled');
            }
            if ($v['status'] == C('ASSETS_STATUS_SCRAP_ON')) {
                //报废中
                if ($v['approve_status'] == '2' && $v['retrial_status'] == '1') {
                    $html .= '<div class="layui-btn-group">';
                    $html .= $this->returnListLink('申请重审', $Apply['actionurl'] . '?scrid=' . $v['scrid'], 'add',
                        C('BTN_CURRENCY'));
                    $html .= $this->returnListLink('结束进程', $Apply['actionurl'], 'over',
                        C('BTN_CURRENCY') . ' layui-btn-danger', '', 'data-id=' . $v['scrid']);
                    $html .= '</div>';
                } else {
                    $one['statusName'] = C('ASSETS_STATUS_OUTSIDE_ON_NAME');
                    $html              .= $this->returnListLink('等待审核', $applyList['actionurl'], 'showScrap',
                        C('BTN_CURRENCY') . ' layui-btn-warm');
                }
            }
            if ($v['status'] == C('ASSETS_STATUS_TRANSFER_ON')) {
                //转科中
                $html .= $this->returnListLink(C('ASSETS_STATUS_TRANSFER_ON_NAME'), '', '',
                    C('BTN_CURRENCY') . ' layui-btn-disabled');
            }
            $html                    .= '</div>';
            $asinfo[$k]['operation'] = $html;
        }

        $result["code"]   = 200;
        $result['total']  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["rows"]   = $asinfo;
        if (!$result['rows']) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    //结束报废流程
    public function endScrap($scrid = null)
    {
        $scrid     = I('post.scrid');
        $scrapInfo = $this->DB_get_one('assets_scrap', 'scrid,assid', ['scrid' => $scrid]);
        if (!$scrapInfo) {
            return ['status' => -1, 'msg' => '查找不到该报废记录！'];
        }
        $this->updateData('assets_scrap', ['retrial_status' => 3], ['scrid' => $scrid]);
        $this->updateData('assets_info', ['status' => 0], ['assid' => $scrapInfo['assid']]);
        return ['status' => 1, 'msg' => '操作成功！'];
    }

    //重审报废流程
    public function editScrap()
    {

        $scrid            = I('post.scrid');
        $subsidiary_assid = trim(I('POST.subsidiary_assid'));
        $scrapInfo        = $this->DB_get_one('assets_scrap', 'scrid,assid,scrapnum', ['scrid' => $scrid]);
        if (!$scrapInfo) {
            return ['status' => -1, 'msg' => '查找不到该报废记录！'];
        }
        $assets = $this->DB_get_one('assets_info', 'assets,assnum,assid,model,departid,hospital_id,buy_price',
            ['assid' => $scrapInfo['assid']]);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assets['buy_price'] = '***';
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assets['department']               = $departname[$assets['departid']]['department'];
        $add['order_price']                 = $assets['buy_price'];
        $all_subsidiaryWhere['main_assid']  = ['EQ', $add['assid']];
        $all_subsidiaryWhere['status'][0]   = 'NOTIN';
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_SCRAP');//报废
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        $subsidiary_assets                  = $this->DB_get_all('assets_info', 'buy_price,assid', $all_subsidiaryWhere);
        $all_subsidiary_assid               = [];
        $assid_arr                          = explode(",", $subsidiary_assid);
        $add['order_price']                 = $assets['buy_price'];
        if ($subsidiary_assid) {
            //有选择辅助设备 金额汇总
            foreach ($subsidiary_assets as &$sub_asstes) {
                $all_subsidiary_assid[] = $sub_asstes['assid'];
                if (in_array($sub_asstes['assid'], $assid_arr)) {
                    $add['order_price'] += $sub_asstes['buy_price'];
                }
            }
        }
        //查询报废审核是否已开启
        $isOpenApprove = $this->checkApproveIsOpen(C('SCRAP_APPROVE'), $assets['hospital_id']);
        $smsApprover   = $UserData = [];
        if ($isOpenApprove) {
            //查询是否已设置审批流程
            $isSetProcess = $this->checkApproveIsSetProcess(C('SCRAP_APPROVE'), $assets['hospital_id']);
            if (!$isSetProcess) {
                die(json_encode(['status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！']));
            }
            //已开启报废审批
            //获取审批流程
            $approve_process_user = $this->get_approve_process($add['order_price'], C('SCRAP_APPROVE'),
                $assets['hospital_id']);
            //并且获取下次审批人
            $approve                 = $this->check_approve_process($assets['departid'], $approve_process_user, 1);
            $smsApprover['username'] = $approve['this_current_approver'];
            if ($approve['all_approver'] == '') {
                //不在审核范围内 不需要审批
                $add['approve_status'] = C('STATUS_APPROE_UNWANTED');
                $add['retrial_status'] = 3;//结束
                $status                = C('ASSETS_STATUS_SCRAP');
                $remark                = '设备申请报废，无需审批，已直接报废！';
            } else {
                //默认为未审核
                $add['current_approver']      = $approve['current_approver'];
                $add['complete_approver']     = '';
                $add['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                $add['all_approver']          = $approve['all_approver'];
                $add['approve_status']        = C('APPROVE_STATUS');
                $add['retrial_status']        = 2;//重审中
                $remark                       = '设备申请报废，正在等待审批！';
                $status                       = C('ASSETS_STATUS_SCRAP_ON');
            }
        } else {
            //未开启报废审批,不需审核
            $add['approve_status'] = C('STATUS_APPROE_UNWANTED');
            $add['retrial_status'] = 3;//结束
            $status                = C('ASSETS_STATUS_SCRAP');
            $remark                = '设备申请报废，无需审批，已直接报废！';
        }
        $add['scrap_reason'] = I('post.scrap_reason');
        $add['update_time']  = getHandleDate(time());
        $add['update_user']  = session('username');
        $newScrid            = $this->updateData('assets_scrap', $add, ['scrid' => $scrid]);
        if (!$newScrid) {
            return ['status' => 1, 'msg' => '提交重审申请失败!'];
        }
        //删除审批记录
        $this->updateData('approve', ['is_delete' => 1], ['scrapid' => $scrid]);

        $update_assid_arr = [];
        if ($subsidiary_assid) {
            $addData   = [];
            $assid_arr = explode(",", $subsidiary_assid);
            for ($i = 0; $i < count($assid_arr); $i++) {
                $update_assid_arr[]              = $assid_arr[$i];
                $addData[$i]['scrid']            = $newScrid;
                $addData[$i]['subsidiary_assid'] = $assid_arr[$i];
            }
        }
        if ($status == C('ASSETS_STATUS_SCRAP')) {
            //不需审核，设备状态直接变更已报废
            $diff_assid = array_diff($all_subsidiary_assid, $update_assid_arr);
            if ($diff_assid) {
                //将未选中的附属设备变成无主,已选中的不用操作 当做记录
                $diffData['main_assid']  = 0;
                $diffData['main_assets'] = '';
                $diffWhere['assid']      = ['IN', $diff_assid];
                $this->updateData('assets_info', $diffData, $diffWhere);
            }
            $update_assid_arr[]   = $assets['assid'];
            $assetaData['status'] = $status;
            $this->updateData('assets_info', $assetaData, ['assid' => ['IN', $update_assid_arr]]);
            $this->updateAllAssetsStatus($update_assid_arr, $status, '设备报废');
        }
        //日志行为记录文字
        $log['assnum'] = $assets['assnum'];
        $text          = getLogText('applyScrapLogText2', $log);
        $this->addLog('assets_scrap', M()->getLastSql(), $text, $newScrid, '');

        //==========================================短信 START==========================================
        $settingData = $this->checkSmsIsOpen($this->Controller);
        if ($settingData) {
            //有开启短信
            $asInfo            = $this->DB_get_one('assets_info', 'assnum,assets', ['assid' => $add['assid']]);
            $smsData['assnum'] = $asInfo['assnum'];
            $smsData['assets'] = $asInfo['assets'];
            $ToolMod           = new ToolController();
            if ($isOpenApprove) {
                //开启了审批 通知审批人
                $telephone = $this->DB_get_one('user', 'telephone', ['username' => $smsApprover['username']]);
                $sms       = $this->formatSmsContent($settingData['approveScrap']['content'], $smsData);
                $ToolMod->sendingSMS($telephone['telephone'], $sms, $this->Controller, $newScrid);
            }
        }
        //==========================================短信 END==========================================

        if (C('USE_FEISHU') === 1) {
            //==========================================飞书 START========================================
            //要显示的字段区域
            $fd['is_short']        = false;//是否并排布局
            $fd['text']['tag']     = 'lark_md';
            $fd['text']['content'] = '**申请人：**' . session('username');
            $feishu_fields[]       = $fd;

            $fd['is_short']        = false;//是否并排布局
            $fd['text']['tag']     = 'lark_md';
            $fd['text']['content'] = '**报废单号：**' . $scrapInfo['scrapnum'];
            $feishu_fields[]       = $fd;

            $fd['is_short']        = false;//是否并排布局
            $fd['text']['tag']     = 'lark_md';
            $fd['text']['content'] = '**设备名称：**' . $assets['assets'];
            $feishu_fields[]       = $fd;

            $fd['is_short']        = false;//是否并排布局
            $fd['text']['tag']     = 'lark_md';
            $fd['text']['content'] = '**设备编码：**' . $assets['assnum'];
            $feishu_fields[]       = $fd;

            $fd['is_short']        = false;//是否并排布局
            $fd['text']['tag']     = 'lark_md';
            $fd['text']['content'] = '**所属科室：**' . $assets['department'];
            $feishu_fields[]       = $fd;

            $fd['is_short']        = false;//是否并排布局
            $fd['text']['tag']     = 'lark_md';
            $fd['text']['content'] = '**报废原因：**' . $add['scrap_reason'];
            $feishu_fields[]       = $fd;

            //按钮区域
            $act['tag']             = 'button';
            $act['type']            = 'primary';
            $act['url']             = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Scrap/examine?scrid=' . $scrid;
            $act['text']['tag']     = 'plain_text';
            $act['text']['content'] = '审批';
            $feishu_actions[]       = $act;

            $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
            $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
            $card_data['elements'][0]['tag']         = 'div';
            $card_data['elements'][0]['fields']      = $feishu_fields;
            $card_data['elements'][1]['tag']         = 'hr';
            $card_data['elements'][2]['actions']     = $feishu_actions;
            $card_data['elements'][2]['layout']      = 'bisected';
            $card_data['elements'][2]['tag']         = 'action';
            $card_data['header']['template']         = 'blue';
            $card_data['header']['title']['content'] = '设备报废审批申请提醒(重审申请)';
            $card_data['header']['title']['tag']     = 'plain_text';

            if ($isOpenApprove) {
                //审批人
                $telephone = $this->DB_get_one('user', 'telephone,openid', ['username' => $smsApprover['username']]);
                //开启了审批 通知审批人
                if ($telephone['openid']) {
                    $this->send_feishu_card_msg($telephone['openid'], $card_data);
                }
            }
            //==========================================飞书 END==========================================
        } else {
            //==========================================微信通知 START=====================================
            //判断是否开启微信端
            $moduleModel = new ModuleModel();
            $wx_status   = $moduleModel->decide_wx_login();
            if ($wx_status && $isOpenApprove) {
                //审批人
                $telephone = $this->DB_get_one('user', 'telephone,openid', ['username' => $smsApprover['username']]);
                //开启了审批 通知审批人
                if ($telephone['openid']) {
                    if (C('USE_VUE_WECHAT_VERSION')) {
                        $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Scrap/examine?scrid=' . $scrid;
                    } else {
                        $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Scrap/examine.html?scrid=' . $scrid;
                    }

                    Weixin::instance()->sendMessage($telephone['openid'], '设备报废待审批提醒', [
                        'thing8'            => $assets['department'],// 所属科室
                        'thing1'            => $assets['assets'],// 设备名称
                        'character_string9' => $assets['assnum'],// 设备编码
                        'character_string7' => $scrapInfo['scrapnum'],// 报废单号
                        'thing2'            => session('username'),// 申请人
                    ], $redecturl);
                }
            }
            //==========================================微信通知 END=======================================
        }
        return ['status' => 1, 'msg' => $remark];
    }

    /**
     * 添加报废申请
     *
     * todo 如果更改了校验逻辑，同样需要更改盘点的校验方法，checkScrap
     *
     * @return array|void
     */
    public function addScrap()
    {
        $add['assid']     = I('post.assid');
        $subsidiary_assid = trim(I('POST.subsidiary_assid'));
        //报废原因
        if (!trim(I('post.scrap_reason'))) {
            return ['status' => -1, 'msg' => '报废原因不能为空', 'data' => I('post.scrap_reason')];
        } else {
            $add['scrap_reason'] = I('post.scrap_reason');
        }
        if (!$add['assid']) {
            die(json_encode(['status' => -1, 'msg' => '非法操作']));
        }
        if (!session('departid')) {
            die(json_encode(['status' => -1, 'msg' => '无分配管理科室']));
        }
        //筛选计量计划中
        $meteringWhere['status'] = ['EQ', C('YES_STATUS')];
        $meteringWhere['assid']  = ['EQ', $add['assid']];
        $metering                = $this->DB_get_one('metering_plan', 'assid', $meteringWhere);
        if ($metering) {
            die(json_encode(['status' => -1, 'msg' => '设备有启用中的计量计划,请停用或删除后再进行报废']));
        }
        $where['assid']    = ['EQ', $add['assid']];
        $where['departid'] = ['IN', session('departid')];
        $files             = 'assid,hospital_id,assnum,assets,status,departid,quality_in_plan,patrol_in_plan,buy_price';
        $assets            = $this->DB_get_one('assets_info', $files, $where);
        if (!$assets) {
            die(json_encode(['status' => -1, 'msg' => '无该设备所属科室管理权限']));
        } else {
            if ($assets['status'] == C('ASSETS_STATUS_REPAIR')) {
                die(json_encode(['status' => -1, 'msg' => '设备正在维修中，请等待维修结果']));
            }
            if ($assets['status'] == C('ASSETS_STATUS_OUTSIDE_ON')) {
                die(json_encode(['status' => -1, 'msg' => '设备正在外调申请']));
            }
            if ($assets['status'] == C('ASSETS_STATUS_OUTSIDE')) {
                die(json_encode(['status' => -1, 'msg' => '设备已外调']));
            }
            if ($assets['status'] == C('ASSETS_STATUS_SCRAP_ON')) {
                die(json_encode(['status' => -1, 'msg' => '设备正在报废申请']));
            }
            if ($assets['status'] == C('ASSETS_STATUS_SCRAP')) {
                die(json_encode(['status' => -1, 'msg' => '设备已报废']));
            }
            if ($assets['status'] == C('ASSETS_STATUS_TRANSFER_ON')) {
                die(json_encode(['status' => -1, 'msg' => '设备正在转科中，请等待转科结束后再申请报废']));
            }
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assets['department'] = $departname[$assets['departid']]['department'];

        $all_subsidiaryWhere['main_assid']  = ['EQ', $add['assid']];
        $all_subsidiaryWhere['status'][0]   = 'NOTIN';
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_SCRAP');//报废
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        $subsidiary_assets                  = $this->DB_get_all('assets_info', 'buy_price,assid', $all_subsidiaryWhere);
        $all_subsidiary_assid               = [];
        $assid_arr                          = explode(",", $subsidiary_assid);
        $add['order_price']                 = $assets['buy_price'];
        if ($subsidiary_assid) {
            //有选择辅助设备 金额汇总
            foreach ($subsidiary_assets as &$sub_asstes) {
                $all_subsidiary_assid[] = $sub_asstes['assid'];
                if (in_array($sub_asstes['assid'], $assid_arr)) {
                    $add['order_price'] += $sub_asstes['buy_price'];
                }
            }
        }
        //查询报废审核是否已开启
        $isOpenApprove = $this->checkApproveIsOpen(C('SCRAP_APPROVE'), $assets['hospital_id']);
        $smsApprover   = [];
        if ($isOpenApprove) {
            //查询是否已设置审批流程
            $isSetProcess = $this->checkApproveIsSetProcess(C('SCRAP_APPROVE'), $assets['hospital_id']);
            if (!$isSetProcess) {
                die(json_encode(['status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！']));
            }
            //已开启报废审批
            //获取审批流程
            $approve_process_user = $this->get_approve_process($add['order_price'], C('SCRAP_APPROVE'),
                $assets['hospital_id']);
            //并且获取下次审批人
            $approve                 = $this->check_approve_process($assets['departid'], $approve_process_user, 1);
            $smsApprover['username'] = $approve['this_current_approver'];
            if ($approve['all_approver'] == '') {
                //不在审核范围内 不需要审批
                $add['approve_status'] = C('STATUS_APPROE_UNWANTED');
                $status                = C('ASSETS_STATUS_SCRAP');
                $remark                = '申请成功，无需审批，已直接报废！';
            } else {
                //默认为未审核
                $add['current_approver']      = $approve['current_approver'];
                $add['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                $add['all_approver']          = $approve['all_approver'];
                $add['approve_status']        = C('APPROVE_STATUS');
                $remark                       = '申请成功，等待报废审批！';
                $status                       = C('ASSETS_STATUS_SCRAP_ON');
            }
        } else {
            //未开启报废审批,不需审核
            $add['approve_status'] = C('STATUS_APPROE_UNWANTED');
            $status                = C('ASSETS_STATUS_SCRAP');
            $remark                = '申请成功，无需审批，已直接报废！';
        }
        $add['apply_user'] = session('username');
        $add['scrapnum']   = I('post.scrapnum');
        $add['scrapdate']  = getHandleTime(time());
        $add['add_time']   = getHandleDate(time());
        $add['add_user']   = session('username');
        $newScrid          = $this->insertData('assets_scrap', $add);
        if ($newScrid) {
            $update_assid_arr = [];
            if ($subsidiary_assid) {
                $addData   = [];
                $assid_arr = explode(",", $subsidiary_assid);
                for ($i = 0; $i < count($assid_arr); $i++) {
                    $update_assid_arr[]              = $assid_arr[$i];
                    $addData[$i]['scrid']            = $newScrid;
                    $addData[$i]['subsidiary_assid'] = $assid_arr[$i];
                }
                $this->insertDataALL('assets_scrap_detail', $addData);
            }
            if ($status == C('ASSETS_STATUS_SCRAP_ON')) {
                //设备状态变更为报废中
                $update_assid_arr[]   = $assets['assid'];
                $assetaData['status'] = $status;
                $this->updateData('assets_info', $assetaData, ['assid' => ['IN', $update_assid_arr]]);
                $this->updateAllAssetsStatus($update_assid_arr, $status, '设备报废申请');
            } else {
                //设备状态直接变更已报废
                $diff_assid = array_diff($all_subsidiary_assid, $update_assid_arr);
                if ($diff_assid) {
                    //将未选中的附属设备变成无主,已选中的不用操作 当做记录
                    $diffData['main_assid']  = 0;
                    $diffData['main_assets'] = '';
                    $diffWhere['assid']      = ['IN', $diff_assid];
                    $this->updateData('assets_info', $diffData, $diffWhere);
                }
                $update_assid_arr[]   = $assets['assid'];
                $assetaData['status'] = $status;
                $this->updateData('assets_info', $assetaData, ['assid' => ['IN', $update_assid_arr]]);
                $this->updateAllAssetsStatus($update_assid_arr, $status, '设备报废申请');
            }
            //日志行为记录文字
            $log['assnum'] = $assets['assnum'];
            $text          = getLogText('applyScrapLogText', $log);
            $this->addLog('assets_scrap', M()->getLastSql(), $text, $newScrid, '');

            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($settingData) {
                //有开启短信
                $asInfo            = $this->DB_get_one('assets_info', 'assnum,assets', ['assid' => $add['assid']]);
                $smsData['assnum'] = $asInfo['assnum'];
                $smsData['assets'] = $asInfo['assets'];
                $ToolMod           = new ToolController();
                if ($isOpenApprove) {
                    //开启了审批 通知审批人
                    $telephone = $this->DB_get_one('user', 'telephone', ['username' => $smsApprover['username']]);
                    $sms       = $this->formatSmsContent($settingData['approveScrap']['content'], $smsData);
                    $ToolMod->sendingSMS($telephone['telephone'], $sms, $this->Controller, $newScrid);
                }
            }
            //==========================================短信 END==========================================

            if (C('USE_FEISHU') === 1) {
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**申请人：**' . session('username');
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**报废单号：**' . $add['scrapnum'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备名称：**' . $assets['assets'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备编码：**' . $assets['assnum'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**所属科室：**' . $assets['department'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**报废原因：**' . $add['scrap_reason'];
                $feishu_fields[]       = $fd;

                //按钮区域
                $act['tag']             = 'button';
                $act['type']            = 'primary';
                $act['url']             = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Scrap/examine?scrid=' . $newScrid;
                $act['text']['tag']     = 'plain_text';
                $act['text']['content'] = '审批';
                $feishu_actions[]       = $act;

                $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag']         = 'div';
                $card_data['elements'][0]['fields']      = $feishu_fields;
                $card_data['elements'][1]['tag']         = 'hr';
                $card_data['elements'][2]['actions']     = $feishu_actions;
                $card_data['elements'][2]['layout']      = 'bisected';
                $card_data['elements'][2]['tag']         = 'action';
                $card_data['header']['template']         = 'blue';
                $card_data['header']['title']['content'] = '设备报废审批申请提醒';
                $card_data['header']['title']['tag']     = 'plain_text';

                if ($isOpenApprove) {
                    //审批人
                    $telephone = $this->DB_get_one('user', 'telephone,openid',
                        ['username' => $smsApprover['username']]);
                    //开启了审批 通知审批人
                    if ($telephone['openid']) {
                        $this->send_feishu_card_msg($telephone['openid'], $card_data);
                    }
                }
                //==========================================飞书 END==========================================
            } else {
                //==========================================微信通知 START=====================================
                //判断是否开启微信端
                $moduleModel = new ModuleModel();
                $wx_status   = $moduleModel->decide_wx_login();
                if ($wx_status && $isOpenApprove) {
                    //审批人
                    $telephone = $this->DB_get_one('user', 'telephone,openid',
                        ['username' => $smsApprover['username']]);
                    //开启了审批 通知审批人
                    if ($telephone['openid']) {
                        if (C('USE_VUE_WECHAT_VERSION')) {
                            $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Scrap/examine?scrid=' . $newScrid;
                        } else {
                            $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Scrap/examine.html?scrid=' . $newScrid;
                        }

                        Weixin::instance()->sendMessage($telephone['openid'], '设备报废待审批提醒', [
                            'thing8'            => $assets['department'],// 所属科室
                            'thing1'            => $assets['assets'],// 设备名称
                            'character_string9' => $assets['assnum'],// 设备编码
                            'character_string7' => $add['scrapnum'],// 报废单号
                            'thing2'            => session('username'),// 申请人
                        ], $redecturl);
                    }
                }
                //==========================================微信通知 END=======================================
            }
            return ['status' => 1, 'msg' => $remark];
        } else {
            return ['status' => -1, 'msg' => '申请报废失败！'];
        }
    }

    //获取审核列表
    public function getApproveLists()
    {
        $limit         = I('post.limit') ? I('post.limit') : 10;
        $page          = I('post.page') ? I('post.page') : 1;
        $offset        = ($page - 1) * $limit;
        $sort          = I('post.sort') ? I('post.sort') : 'scrid';
        $order         = I('post.order') ? I('post.order') : 'desc';
        $assets        = I('POST.getExamineListAssets');
        $assetsNum     = I('POST.getExamineListAssetsNum');
        $assetsCat     = I('POST.getExamineListCategory');
        $examineStatus = I('POST.examineStatus');
        $depid         = I('POST.departid');
        $hostpital_id  = session('job_hospitalid');
        if (!session('departid')) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if ($depid) {
            $where['B.departid'][] = ['IN', $depid];
        } else {
            $where['B.departid'][] = ['IN', session('departid')];
        }
        if ($examineStatus != null) {
            //审核状态搜索
            $where['A.approve_status'] = ['EQ', $examineStatus];
        } else {
            $where['A.approve_status'] = ['NEQ', C('STATUS_APPROE_UNWANTED')];
        }
        $where['A.all_approver'] = ['LIKE', '%/' . session('username') . '/%'];
        if ($hostpital_id) {
            $where['B.hospital_id'] = ['EQ', $hostpital_id];
        }
        if ($assets) {
            //设备名称搜索
            $where['B.assets'] = ['LIKE', '%' . $assets . '%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['B.assnum'] = ['LIKE', '%' . $assetsNum . '%'];
        }
        if ($assetsCat) {
            //分类搜索
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['like', '%' . $assetsCat . '%'];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['B.catid']     = ['IN', $catids];
        }
        $departname = [];
        $catname    = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        //查询当前用户是否有权限进行报废审批
        $Apply   = get_menu($this->MODULE, $this->Controller, 'examine');
        $fields  = 'A.scrid,A.scrapnum,A.scrapdate,A.apply_user,A.scrap_reason,A.approve_status,A.current_approver,A.complete_approver,A.not_complete_approver,A.all_approver,B.assets,B.catid,B.assid,B.assnum,B.departid,B.opendate,B.buy_price,B.storage_date,B.expected_life';
        $join    = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $total   = $this->DB_get_count_join('assets_scrap', 'A', $join, $where, '');
        $examine = $this->DB_get_all_join('assets_scrap', 'A', $fields, $join, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        if (!$examine) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        foreach ($examine as $k => $v) {

            $html                         = '<div class="layui-btn-group">';
            $html                         .= $this->returnButtonLink('设备详情', C('SHOWASSETS_ACTION_URL'),
                'layui-btn layui-btn-xs layui-btn-primary', '', 'lay-event = showAssets');
            $examine[$k]['opendate']      = HandleEmptyNull($v['opendate']);
            $examine[$k]['storage_date']  = HandleEmptyNull($v['storage_date']);
            $examine[$k]['department']    = $departname[$v['departid']]['department'];
            $examine[$k]['category']      = $catname[$v['catid']]['category'];
            $examine[$k]['buy_price']     = (float)$v['buy_price'];
            $examine[$k]['expected_life'] = (int)$v['expected_life'];
            if ($v['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                $html .= $this->returnListLink('已通过', $Apply['actionurl'], 'approval',
                    C('BTN_CURRENCY') . ' layui-btn');
            } elseif ($v['approve_status'] == C('STATUS_APPROE_FAIL')) {
                $html .= $this->returnListLink('不通过', $Apply['actionurl'], 'approval',
                    C('BTN_CURRENCY') . ' layui-btn-danger');
            } else {
                if ($Apply && $v['current_approver']) {
                    $current_approver     = explode(',', $v['current_approver']);
                    $current_approver_arr = [];
                    foreach ($current_approver as &$current_approver_value) {
                        $current_approver_arr[$current_approver_value] = true;
                    }
                    if ($current_approver_arr[session('username')]) {
                        $html .= $this->returnListLink('审核', $Apply['actionurl'], 'approval',
                            C('BTN_CURRENCY') . ' layui-btn-normal');
                    } else {
                        $complete = explode(',', $v['complete_approver']);

                        $notcomplete = explode(',', $v['not_complete_approver']);
                        if (!in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                            //完全未审
                            $html .= $this->returnListLink('待审核', $Apply['actionurl'], 'approval',
                                C('BTN_CURRENCY') . ' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && in_array(session('username'),
                                $notcomplete)) {
                            //有已审，有未审
                            $html .= $this->returnListLink('待审核', $Apply['actionurl'], 'approval',
                                C('BTN_CURRENCY') . ' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && !in_array(session('username'),
                                $notcomplete)) {
                            //全部已审
                            $html .= $this->returnListLink('已审核', $Apply['actionurl'], 'approval',
                                C('BTN_CURRENCY') . ' layui-btn-primary');
                        } else {
                            $html .= '';
                        }
                    }
                }
            }
            $html                     .= '</div>';
            $asinfo[$k]['operation']  = $html;
            $examine[$k]['operation'] = $html;
        }
        // exit;
        $result["code"]   = 200;
        $result['total']  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["rows"]   = $examine;
        if (!$result['rows']) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    //审批操作
    public function saveExamine()
    {
        if (!trim(I('post.remark'))) {
            return ['status' => -1, 'msg' => '审核意见不能为空！'];
        }
        if (I('post.scrid')) {
            $data['scrapid'] = I('post.scrid');
        } else {
            return ['status' => -1, 'msg' => '参数错误！'];
        }
        //检查是否存在此条记录
        $scrapinfo_where['scrid']          = ['EQ', $data['scrapid']];
        $scrapinfo_where['approve_status'] = ['NEQ', C('STATUS_APPROE_UNWANTED')];
        $scrapinfo                         = $this->DB_get_one('assets_scrap',
            'scrid,assid,order_price,scrapnum,apply_user,add_time,approve_status,current_approver,complete_approver,all_approver,not_complete_approver,add_user',
            $scrapinfo_where);
        if (!$scrapinfo['scrid']) {
            return ['status' => -1, 'msg' => '查找不到报废信息！'];
        } else {
            if ($scrapinfo['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                return ['status' => -1, 'msg' => '审批已通过，请勿重复提交！'];
            } elseif ($scrapinfo['approve_status'] == C('STATUS_APPROE_FAIL')) {
                return ['status' => -1, 'msg' => '审批已否决，请勿重复提交！'];
            }
        }
        //查询设备信息
        $assInfo    = $this->DB_get_one('assets_info',
            'assid,hospital_id,assnum,assets,status,departid,quality_in_plan,patrol_in_plan,buy_price',
            ['assid' => $scrapinfo['assid']]);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assInfo['department'] = $departname[$assInfo['departid']]['department'];
        $data['scrapid']       = $scrapinfo['scrid'];
        $data['is_adopt']      = I('POST.is_adopt');
        $data['remark']        = trim(I('POST.remark'));
        $data['proposer']      = $scrapinfo['apply_user'];
        $data['proposer_time'] = strtotime($scrapinfo['add_time']);
        $data['approver']      = session('username');
        $data['approve_time']  = time();
        $data['approve_class'] = 'scrap';
        $data['process_node']  = C('SCRAP_APPROVE');
        //判断是否是当前审批人
        if (!$scrapinfo['current_approver']) {
            return ['status' => -1, 'msg' => '审核已结束！'];
        }
        $current_approver     = explode(',', $scrapinfo['current_approver']);
        $current_approver_arr = [];
        foreach ($current_approver as &$current_approver_value) {
            $current_approver_arr[$current_approver_value] = true;
        }
        if (!$current_approver_arr[session('username')]) {
            return ['status' => -1, 'msg' => '请等待审批！'];
        }
        $processWhere['scrapid']    = ['EQ', $scrapinfo['scrid']];
        $processWhere['is_delete']  = ['NEQ', C('YES_STATUS')];
        $process                    = $this->DB_get_count('approve', $processWhere);
        $level                      = $process + 1;
        $data['process_node_level'] = $level;
        $res                        = $this->addApprove($scrapinfo, $data, $scrapinfo['order_price'],
            $assInfo['hospital_id'], $assInfo['departid'], C('SCRAP_APPROVE'), 'assets_scrap', 'scrid');
        if ($res['status'] == 1) {
            $text = getLogText('applyApproverLogText',
                ['scrapnum' => $scrapinfo['scrapnum'], 'is_adopt' => I('POST.is_adopt')]);
            $this->addLog('assets_scrap', $res['lastSql'], $text, $scrapinfo['scrid'], '');
        }
        return $res;
    }

    //获取报废查询列表
    public function getScrapLists()
    {
        $departids = session('departid');
        $limit     = I('post.limit') ? I('post.limit') : 10;
        $page      = I('post.page') ? I('post.page') : 1;
        $offset    = ($page - 1) * $limit;
        //设备编号
        $assetsNum = I('POST.getScrapListAssetsNum');
        //设备名称
        $assets = I('POST.getScrapListAssets');
        //科室
        $depid = I('POST.departid');
        //分类
        $assetsCat   = I('POST.getScrapListCategory');
        $hospital_id = I('POST.hospital_id');
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$departids) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if ($depid) {
            $where['B.departid'] = ['IN', $depid];
        } else {
            $where['B.departid'] = ['IN', $departids];
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($assets) {
            //设备名称搜索
            $where['B.assets'] = ['LIKE', '%' . $assets . '%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['B.assnum'] = ['LIKE', '%' . $assetsNum . '%'];
        }
        if ($assetsCat) {
            //分类搜索
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['LIKE', '%' . $assetsCat . '%'];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['B.catid']     = ['IN', $catids];
        }
        if ($assetsDep) {
            //部门搜索
            $deModel               = new DepartmentModel();
            $dewhere['department'] = ['LIKE', '%' . $assetsDep . '%'];;
            $res = $deModel->DB_get_all('department', 'departid', $dewhere, '', 'departid asc', '');
            if ($res) {
                $departids = '';
                foreach ($res as $k => $v) {
                    $departids .= $v['departid'] . ',';
                }
                $departids           = trim($departids, ',');
                $where['B.departid'] = ['IN', $departids];
            } else {
                $where['B.departid'] = ['IN', '-1'];
            }
        }
        $catname    = [];
        $departname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $ScrapModel   = new AssetsScrapModel();
        $fields       = 'A.scrapnum,A.scrapdate,A.apply_user,A.scrap_reason,A.clear_cross_user,A.cleardate,A.clear_company,A.clear_contacter,A.scrid,A.clear_telephone,A.approve_status,B.assid,B.acid,B.afid,B.assets,B.catid,B.assid,B.assnum,B.departid,B.opendate,B.buy_price,B.expected_life';
        $join         = 'INNER JOIN sb_assets_info AS B ON A.assid = B.assid';
        $total        = $ScrapModel->DB_get_count_join('assets_scrap', 'A', $join, $where, '');
        $scrapinfo    = $ScrapModel->DB_get_all_join('assets_scrap', 'A', $fields, $join, $where, '', 'A.scrid',
            $offset . "," . $limit);
        $getScrapList = get_menu($this->MODULE, $this->Controller, 'getScrapList');
        $showPrice    = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($scrapinfo as $k => $v) {
            $scrapinfo[$k]['opendate']   = HandleEmptyNull($scrapinfo[$k]['opendate']);
            $scrapinfo[$k]['department'] = $departname[$v['departid']]['department'];
            $scrapinfo[$k]['category']   = $catname[$v['catid']]['category'];
            $scrapinfo[$k]['buy_price']  = (float)$v['buy_price'];
            if (!$showPrice) {
                $scrapinfo[$k]['buy_price'] = '***';
            }
            $scrapinfo[$k]['expected_life'] = (int)$v['expected_life'];
            $html                           = '<div class="layui-btn-group">';
            $html                           .= $this->returnButtonLink('设备详情', C('SHOWASSETS_ACTION_URL'),
                'layui-btn layui-btn-xs layui-btn-primary', '', 'lay-event = showAssets');
            $html                           .= $this->returnButtonLink('报废明细', $getScrapList['actionurl'],
                'layui-btn layui-btn-xs layui-btn-normal', '', 'lay-event = showScrap');
            $html                           .= '</div>';
            $scrapinfo[$k]['operation']     = $html;
        }
        $result['total']  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $scrapinfo;
        if (!$result['rows']) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    //报废结果查询
    public function getResultLists()
    {
        $departids   = session('departid');
        $limit       = I('post.limit') ? I('post.limit') : 10;
        $page        = I('post.page') ? I('post.page') : 1;
        $offset      = ($page - 1) * $limit;
        $assets      = I('POST.getResultListAssets');
        $assetsNum   = I('POST.getResultListAssetsNum');
        $assetsCat   = I('POST.getResultListCategory');
        $depid       = I('POST.departid');
        $hospital_id = I('POST.hospital_id');
        if (!$departids) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if ($depid) {
            $where['B.departid'] = ['IN', $depid];
        } else {
            $where['B.departid'] = ['IN', $departids];
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if ($assets) {
            //设备名称搜索
            $where['B.assets'] = ['LIKE', '%' . $assets . '%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['B.assnum'] = ['LIKE', '%' . $assetsNum . '%'];
        }
        if ($assetsCat) {
            //分类搜索
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['like', '%' . $assetsCat . '%'];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['B.catid']     = ['IN', $catids];
        }
        if ($assetsDep) {
            //部门搜索
            $deModel               = new DepartmentModel();
            $dewhere['department'] = ['like', '%' . $assetsDep . '%'];
            $res                   = $deModel->DB_get_all('department', 'departid', $dewhere, '', 'departid asc', '');
            if ($res) {
                $departids = '';
                foreach ($res as $k => $v) {
                    $departids .= $v['departid'] . ',';
                }
                $departids           = trim($departids, ',');
                $where['B.departid'] = ['IN', $departids];
            } else {
                $where['B.departid'] = ['IN', '-1'];
            }
        }
        $catname    = [];
        $departname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $ScrapModel = new AssetsScrapModel();
        $fields     = 'A.scrid,A.scrapnum,A.scrapdate,A.apply_user,A.scrap_reason,A.clear_cross_user,A.cleardate,A.clear_company,A.clear_contacter,A.scrid,A.clear_telephone,A.approve_status,B.assid,B.acid,B.afid,B.assets,B.catid,B.assid,B.assnum,B.departid,B.opendate,B.buy_price,B.expected_life,B.hospital_id';
        $join       = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $resultInfo = $ScrapModel->DB_get_all_join('assets_scrap', 'A', $fields, $join, $where, '', 'A.scrid',
            $offset . "," . $limit);
        $total      = $ScrapModel->DB_get_count_join('assets_scrap', 'A', $join, $where, '');
        //查询当前用户是否有权限进行报废处置
        $solve         = get_menu($this->MODULE, $this->Controller, 'result');
        $getResultList = get_menu($this->MODULE, $this->Controller, 'getResultList');
        $showPrice     = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($resultInfo as $k => $v) {
            $printReportUrl               = $getResultList['actionurl'] . '?action=printReport&scrid=' . $v['scrid'];
            $uploadReportUrl              = $solve['actionurl'] . '?action=uploadReport&scrid=' . $v['scrid'];
            $resultInfo[$k]['opendate']   = HandleEmptyNull($v['opendate']);
            $resultInfo[$k]['department'] = $departname[$v['departid']]['department'];
            $resultInfo[$k]['category']   = $catname[$v['catid']]['category'];
            $resultInfo[$k]['buy_price']  = (float)$v['buy_price'];
            if (!$showPrice) {
                $resultInfo[$k]['buy_price'] = '***';
            }
            $resultInfo[$k]['expected_life'] = (int)$v['expected_life'];
            $html                            = '<div class="layui-btn-group">';
            if ($v['clear_cross_user']) {
                //已处置的
                $html .= $this->returnButtonLink('处置结果', $getResultList['actionurl'],
                    'layui-btn layui-btn-xs layui-btn-normal', '', 'lay-event = showResult');
                $html .= $this->returnButtonLink('打印审批单', $printReportUrl, 'layui-btn layui-btn-xs', '',
                    'lay-event = printReport');
                if ($solve) {
                    $html .= $this->returnButtonLink('上传/查看审批单', $uploadReportUrl,
                        'layui-btn layui-btn-xs layui-btn-warm', '', 'lay-event = uploadReport');
                }
            } else {
                //未处置的
                if ($solve) {
                    if ($v['approve_status'] == 1) {
                        $html .= $this->returnButtonLink('报废处置', $solve['actionurl'], 'layui-btn layui-btn-xs', '',
                            'lay-event = result');
                    } elseif ($v['approve_status'] == 2) {
                        $html .= $this->returnButtonLink('审批不通过', $getResultList['actionurl'],
                            'layui-btn layui-btn-xs layui-btn-danger', '', 'lay-event = showScrap');
                    } elseif ($v['approve_status'] == -1) {
                        $html .= $this->returnButtonLink('报废处置', $solve['actionurl'], 'layui-btn layui-btn-xs', '',
                            'lay-event = result');
                    } else {
                        $html .= $this->returnButtonLink('待审核', $getResultList['actionurl'],
                            'layui-btn layui-btn-xs layui-btn-warm', '', 'lay-event = showScrap');
                    }
                }
            }
            $html                        .= '</div>';
            $resultInfo[$k]['operation'] = $html;
        }
        $result["code"]   = 200;
        $result['total']  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["rows"]   = $resultInfo;
        if (!$result['rows']) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    //处置操作
    public function saveResult()
    {
        //清理公司
        if (trim(I('post.clear_company'))) {
            $edit['clear_company'] = I('post.clear_company');
        }
        //清理公司联系人
        if (trim(I('post.clear_contacter'))) {
            $edit['clear_contacter'] = I('post.clear_contacter');
        }
        //清理备注
        $edit['clear_remark'] = I('post.clear_remark');
        //清理公司联系电话
        if (trim(I('post.clear_telephone'))) {
            $edit['clear_telephone'] = I('post.clear_telephone');
        }
        //处置日期
        if (trim(I('post.cleardate'))) {
            $edit['cleardate'] = I('post.cleardate');
        }
        //清理公司联系人id
        $edit['clear_cross_user'] = session('username');
        $edit['clear_time']       = getHandleDate(time());
        $scrid                    = I('post.scrid');
        if ($scrid) {
            $scrapNum = $this->DB_get_one('assets_scrap', 'scrapnum', ['scrid' => $scrid], '');
            $this->updateData('assets_scrap', $edit, ['scrid' => $scrid]);
            //日志行为记录文字
            $log['scrapnum'] = $scrapNum['scrapnum'];
            $text            = getLogText('resultScrapLogText', $log);
            $this->addLog('assets_scrap', M()->getLastSql(), $text, $scrid, '');
            return ['status' => 1, 'msg' => '处置成功'];
        } else {
            return ['status' => -1, 'msg' => '处置失败'];
        }
    }

    //添加报废文件
    public function addPath($scrid)
    {
        if (I('post.uploadFiles')) {
            //上传文件的名字
            $addFiles['file_url'] = I('post.uploadFiles');
            $addFiles['scrid']    = $scrid;
            $this->insertData('scrap_file', $addFiles);
        }
    }

    /*
   * 上传文件
   * */
    public function uploadfile()
    {
        if (I('post.zm' == 'canvas')) {
            $fin                    = I('post.filename');
            $_FILES['file']['name'] = $fin;
            $ty                     = explode('.', $fin);
            $_FILES['file']['ext']  = $ty[1];
        }
        //上传设备图片
        $Tool = new ToolController();
        //设置文件类型
        $type = ['jpg', 'png', 'bmp', 'jpeg', 'gif'];
        //报告保存地址
        $dirName = $this->Controller;
        //上传文件
        $base64 = I('POST.base64');
        if ($base64) {
            $upload = $Tool->base64imgsave($base64, $dirName);
        } else {
            $upload = $Tool->upFile($type, $dirName);
        }
        if ($upload['status'] == C('YES_STATUS')) {
            $result['status']       = 1;
            $result['file_url']     = $upload['src'];
            $result['file_name']    = $upload['formerly'];
            $result['file_type']    = $upload['ext'];
            $result['save_name']    = $upload['title'];
            $result['thisDateTime'] = date('Ymd', time());
            $result['msg']          = '上传成功';
            $size                   = round($upload['size'] / 1024 / 1024, 2);
            $result['file_size']    = $size;
        } else {
            $result['status'] = -1;
            $result['msg']    = '上传失败';
        }
        return $result;
    }

    public function getScrapFile()
    {
        $scrid = I('get.scrid');
        $files = $this->DB_get_all('scrap_file', 'file_url', ['scrid' => $scrid]);
        foreach ($files as $k => $v) {
            $files[$k]['suffix'] = substr(strrchr($v['file_url'], '.'), 1);
            switch ($files[$k]['suffix']) {
                //是图片类型
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'bmp':
                case 'gif':
                    $files[$k]['type'] = 1;
                    break;
                //pdf类型
                case 'pdf':
                    $files[$k]['type'] = 2;
                    break;
                //文档类型
                case 'doc':
                case 'docx':
                    $files[$k]['type'] = 3;
                    break;
            }
        }
        return $files;
    }

    /**
     * 获取对应的附属设备
     *
     * @param $assid int 主设备id
     *
     * @return  array
     * */
    public function getAssetsSubsidiary($assid)
    {
        $where['main_assid'] = ['EQ', $assid];
        $where['status']     = ['EQ', C('ASSETS_STATUS_USE')];
        $data                = $this->DB_get_all('assets_info', 'assid,assets,assnum,model,unit,buy_price', $where);
        $showPrice           = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($data as $key => $value) {
            if (!$showPrice) {
                $data[$key]['buy_price'] = '***';
            }
        }
        return $data;
    }


    /**
     * 获取附属设备信息
     *
     * @param $borid int 借调记录id
     *
     * @return array
     * */
    public function getSubsidiaryBasic($scrid)
    {
        $join   = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price';
        $data   = $this->DB_get_all_join('assets_scrap_detail', 'D', $fields, $join, "scrid=$scrid");
        return $data;
    }

    /**
     * 上传文件
     *
     * @param $dir   string 保存的文件名
     * @param $style array 允许上传的格式
     *
     * @return array
     * */
    public function uploadReport(
        $dir = '',
        $style = ['JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx']
    ) {
        if ($_FILES['file']) {
            $Tool = new ToolController();
            if ($dir) {
                $dirName = $dir;
            } else {
                $dirName = $this->Controller;
            }
            $info = $Tool->upFile($style, $dirName);
            if ($info['status'] == C('YES_STATUS')) {
                // 上传成功 获取上传文件信息
                $resule['status']    = 1;
                $resule['msg']       = '上传成功';
                $resule['file_url']  = $info['src'];
                $resule['title']     = $info['title'];
                $resule['formerly']  = $info['formerly'];
                $resule['file_type'] = $info['ext'];
                $resule['file_size'] = $info['size'];
            } else {
                // 上传错误提示错误信息
                $resule['status'] = -1;
                $resule['msg']    = $info['msg'];
            }
        } else {
            // 上传错误提示错误信息
            $resule['status'] = -1;
            $resule['msg']    = '未接收到文件';
        }
        return $resule;
    }

    public function formatSmsContent($content, $data)
    {
        $content = str_replace("{scrap_num}", $data['scrapnum'], $content);
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{assnum}", $data['assnum'], $content);
        $content = str_replace("{approve_status}", $data['approve_status'], $content);
        return $content;
    }
}
