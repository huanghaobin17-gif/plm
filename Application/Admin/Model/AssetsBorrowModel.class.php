<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;

class AssetsBorrowModel extends CommonModel
{
    private $MODULE = 'Assets';
    private $Controller = 'Borrow';
    protected $tablePrefix = 'sb_';
    protected $tableName = 'assets_borrow';

    //借调申请列表数据
    public function borrowAssetsList()
    {
        $departid = I('POST.departid');
        $hospital_id = session('current_hospitalid');
        $model = I('POST.assetsModel');
        $assetsName = I('POST.assetsName');
        $assnum = I('POST.assnum');
        $status = I('POST.status');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'assid';

        $where['hospital_id'] = $hospital_id;
        if (session('isSuper') != C('YES_STATUS')) {
            //筛选科室 获取除用户本身工作以外的科室
            if (!session('job_departid')) {
                $result['msg'] = '该用户未分配工作科室';
                $result['code'] = 400;
                return $result;
            }
            $where['departid'][] = array('NOTIN', session('job_departid'));
        }

        $where['status'][0] = 'NOTIN';
        $where['status'][1][] = C('ASSETS_STATUS_SCRAP');
        $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        //借调主设备
        $where['is_subsidiary'] = C('NO_STATUS');
        $where['quality_in_plan'] = C('NO_STATUS');//排除质控中
        $where['patrol_in_plan'] = C('NO_STATUS');//排除巡查中
        if ($departid) {
            $where['departid'][] = array('IN', $departid);
        }

        if ($model) {
            $where['model'] = array('LIKE', '%' . $model . '%');
        }
        if ($assnum) {
            $where['assnum'] = array('LIKE', '%' . $assnum . '%');
        }
        if ($status != "") {
            $where['status'] = $status;
        }
        if ($assetsName) {
            $where['assets'] = array('LIKE', '%' . $assetsName . '%');
        }
        $where['is_delete'] = C('NO_STATUS');
        $fileds = 'departid,assets,assnum,assets,brand,model,status,assid,quality_in_plan';
        $total = $this->DB_get_count('assets_info', $where);
        $data = $this->DB_get_all('assets_info', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $assid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
        }

        //筛选准备计量中的设备  不能是在提醒时间内的设备
        //计量筛选这里不做departid ID筛选,设备会出现转科 具体还是按设备表来筛选
        $meteringWhere['status'] = array('EQ', C('YES_STATUS'));
        $meteringWhere['assid'] = array('IN', $assid);
        $metering = $this->DB_get_all('metering_plan', 'assid,next_date,remind_day', $meteringWhere);
        $meteringAssid = [];
        if ($metering) {
            foreach ($metering as &$meteringV) {
                //如果当前日期在提醒时间之内则不能申请
                if (strtotime("+ " . $meteringV['remind_day'] . " day") > strtotime($meteringV['next_date'])) {
                    $meteringAssid[$meteringV['assid']] = true;
                }
            }
        }
        //筛选借调中的设备  不能是正在借调中的设备
        $borrowWhere['status'] = array('IN', [C('BORROW_STATUS_APPROVE'), C('BORROW_STATUS_BORROW_IN'), C('BORROW_STATUS_GIVE_BACK'), C('BORROW_STATUS_FAIL')]);
        $borrowWhere['assid'] = array('IN', $assid);
        $borrow = $this->DB_get_all('assets_borrow', 'assid,retrial_status,examine_status,borid', $borrowWhere);
        $borrowAssid = [];
        if ($borrow) {
            foreach ($borrow as &$borrowV) {
                if ($borrowV['retrial_status'] == 1 && $borrowV['examine_status'] == 2) {
                    $borrowAssid[$borrowV['assid']]['is_retrial'] = true;
                    $borrowAssid[$borrowV['assid']]['is_borrow'] = false;
                    $borrowAssid[$borrowV['assid']]['borid'] = $borrowV['borid'];
                } else {
                    $borrowAssid[$borrowV['assid']]['is_borrow'] = true;
                }
                if ($borrowV['retrial_status'] == 3 && $borrowV['examine_status'] == 2) {
                    $borrowAssid[$borrowV['assid']]['is_borrow'] = false;
                }
            }
        }
        $applyBorrowMenu = get_menu($this->MODULE, $this->Controller, 'applyBorrow');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $disabled = $this->returnListLink('申请借调', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
        foreach ($data as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            if ($one['quality_in_plan'] == C('YES_STATUS')) {
                $one['statusName'] = '质控中';
                $one['operation'] = $this->returnListLink('质控中', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                continue;
            }
            if ($one['status'] == C('ASSETS_STATUS_REPAIR')) {
                //维修中
                $one['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                $one['operation'] = $this->returnListLink(C('ASSETS_STATUS_REPAIR_NAME'), '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                continue;
            }
            if ($one['status'] == C('ASSETS_STATUS_OUTSIDE_ON')) {
                //外调中
                $one['statusName'] = C('ASSETS_STATUS_OUTSIDE_ON_NAME');
                $one['operation'] = $this->returnListLink(C('ASSETS_STATUS_OUTSIDE_ON_NAME'), '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                continue;
            }
            if ($one['status'] == C('ASSETS_STATUS_SCRAP_ON')) {
                //报废中
                $one['statusName'] = C('ASSETS_STATUS_OUTSIDE_ON_NAME');
                $one['operation'] = $this->returnListLink(C('ASSETS_STATUS_OUTSIDE_ON_NAME'), '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                continue;
            }
            if ($one['status'] == C('ASSETS_STATUS_TRANSFER_ON')) {
                //转科中
                $one['statusName'] = C('ASSETS_STATUS_TRANSFER_ON_NAME');
                $one['operation'] = $this->returnListLink(C('ASSETS_STATUS_TRANSFER_ON_NAME'), '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                continue;
            }
            if ($one['status'] == C('ASSETS_STATUS_USE')) {
                //在用
                $one['statusName'] = C('ASSETS_STATUS_USE_NAME');
            }
            if ($applyBorrowMenu && (!$meteringAssid[$one['assid']] && !$borrowAssid[$one['assid']]['is_borrow'] && !$borrowAssid[$one['assid']]['is_retrial'])) {
                $one['operation'] = $this->returnListLink($applyBorrowMenu['actionname'], $applyBorrowMenu['actionurl'], 'applyBorrow', C('BTN_CURRENCY'));
            } else {
                //维修中、计量中、的不可以申请，按钮加 layui-btn-disabled
                if ($borrowAssid[$one['assid']]['is_borrow']) {
                    $one['statusName'] = '借调中';
                    $one['operation'] = $this->returnListLink('借调中', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                } else if ($borrowAssid[$one['assid']]['is_retrial']) {
                    $html .= '<div class="layui-btn-group">';
                    $html .= $this->returnListLink('申请重审', $applyBorrowMenu['actionurl'] . '?borid=' . $borrowAssid[$one['assid']]['borid'] . '&type=edit', 'edit', C('BTN_CURRENCY') . ' layui-btn-warm');
                    $html .= $this->returnListLink('结束进程', $applyBorrowMenu['actionurl'], 'over', C('BTN_CURRENCY') . ' layui-btn-danger', '', 'data-id=' . $borrowAssid[$one['assid']]['borid']);
                    $html .= '</div>';
                    $one['operation'] = $html;
                }
                if ($meteringAssid[$one['assid']]) {
                    $one['statusName'] = '计量中';
                    $one['operation'] = $this->returnListLink('计量中', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                }
            }
        }
        $result['data'] = $borrowAssid;
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //借调申请操作
    public function applyBorrow()
    {
        $assid = I('POST.assid');
        //var_dump($_POST);die;
        $borrow_reason = I('POST.borrow_reason');
        $estimate_back = I('POST.estimate_back');
        if (!$assid) {
            die(json_encode(array('status' => -1, 'msg' => '非法操作')));
        }
        if (time() >= strtotime($estimate_back)) {
            die(json_encode(array('status' => -1, 'msg' => '请选择正确的归还时间')));
        }
        $assetsWher['assid'] = array('EQ', $assid);
        if (session('isSuper') == C('YES_STATUS')) {
            //如果是超级管理员 获取当前选中的医院
            $assetsWher['hospital_id'] = array('EQ', session('current_hospitalid'));
            $apply_departid = trim(I('POST.apply_departid'));
            $this->checkstatus(judgeNum($apply_departid), '请选中申请科室');
            $data['apply_departid'] = $apply_departid;
        } else {
            $assetsWher['hospital_id'] = array('EQ', session('job_hospitalid'));
            $data['apply_departid'] = session('job_departid');
        }
        $assets = $this->DB_get_one('assets_info', 'assets,assnum,assid,status,departid,quality_in_plan', $assetsWher);
        if (!$assets) {
            die(json_encode(array('status' => -1, 'msg' => '查找不到该设备信息！')));
        } else {
            if ($assets['status'] == C('ASSETS_STATUS_REPAIR')) {
                die(json_encode(array('status' => -1, 'msg' => '设备正在维修中，请等待结束后再申请借调！')));
            }
            if ($assets['status'] == C('ASSETS_STATUS_OUTSIDE_ON')) {
                die(json_encode(array('status' => -1, 'msg' => '设备正在外调申请！')));
            }
            if ($assets['status'] == C('ASSETS_STATUS_OUTSIDE')) {
                die(json_encode(array('status' => -1, 'msg' => '设备已外调！')));
            }
            if ($assets['status'] == C('ASSETS_STATUS_SCRAP_ON')) {
                die(json_encode(array('status' => -1, 'msg' => '设备正在报废申请！')));
            }
            if ($assets['status'] == C('ASSETS_STATUS_SCRAP')) {
                die(json_encode(array('status' => -1, 'msg' => '设备已报废！')));
            }
            if ($assets['status'] == C('ASSETS_STATUS_TRANSFER_ON')) {
                die(json_encode(array('status' => -1, 'msg' => '设备正在转科中，请等待转科结束后再申请借调！')));
            }
            if ($assets['quality_in_plan'] == C('YES_STATUS')) {
                die(json_encode(array('status' => -1, 'msg' => '设备正在质控执行中，请等待结束后再申请借调！')));
            }

        }
        //借调
        $borrowWhere['status'] = array('NOTIN', [C('BORROW_STATUS_COMPLETE'), C('BORROW_STATUS_NOT_APPLY'), C('BORROW_STATUS_FAIL')]);
        $borrowWhere['assid'] = array('EQ', $assid);
        $borrow = $this->DB_get_all('assets_borrow', 'assid', $borrowWhere);
        if ($borrow) {
            die(json_encode(array('status' => -1, 'msg' => '该设备正在借调中，请勿重复申请')));
        }
        $time = date('H:i', strtotime($estimate_back));
        $timeArr = explode(':', $time);
        $baseSetting = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $start = $baseSetting['assets']['apply_borrow_back_time']['value'][0];
        $end = $baseSetting['assets']['apply_borrow_back_time']['value'][1];
        $startArr = explode(':', $start);
        $endArr = explode(':', $end);
        if ($start && $end) {
            if ($startArr[0] <= $timeArr[0] && $timeArr[0] <= $endArr[0]) {
                //最后一个小时 分钟不能大于设置的分钟否则不合理
                if ($timeArr[0] == $endArr[0] && ($endArr[1] < $timeArr[1])) {
                    die(json_encode(array('status' => -1, 'msg' => '归还时间范围 ' . $start . ' 至 ' . $end)));
                }
                //第一个小时 分钟不能小于设置的分钟否则不合理
                if ($timeArr[0] == $startArr[0] && ($startArr[1] > $timeArr[1])) {
                    die(json_encode(array('status' => -1, 'msg' => '归还时间范围 ' . $start . ' 至 ' . $end)));
                }
            } else {
                die(json_encode(array('status' => -1, 'msg' => '归还时间范围 ' . $start . ' 至 ' . $end)));
            }
        }

        //归还时间不能在计量检查的时间内
        $meteringWhere['status'] = array('EQ', C('YES_STATUS'));
        $meteringWhere['assid'] = array('EQ', $assid);
        $metering = $this->DB_get_one('metering_plan', 'assid,next_date,remind_day', $meteringWhere);
        if ($metering) {
            //如果当前日期在提醒时间之内则不能申请
            $remind_date = strtotime($metering['next_date']) - ($metering['remind_day'] * (60 * 60 * 24));
            if (strtotime($estimate_back) > $remind_date) {
                die(json_encode(array('status' => -1, 'msg' => '需在计量执行(' . getHandleTime($remind_date) . ')前归还')));
            }
        }

        //归还时间不能在质控计划开始到结束的这个过程中
        $qualityWhere['is_start'] = array('EQ', C('YES_STATUS'));
        $qualityWhere['assid'] = array('EQ', $assid);
        $quality = $this->DB_get_one('quality_starts', 'do_date', $qualityWhere);
        if ($quality) {
            if (strtotime($estimate_back) > strtotime($quality['do_date'])) {
                die(json_encode(array('status' => -1, 'msg' => '需在质控执行(' . $quality['do_date'] . ')前归还')));
            }
        }

        $data['status'] = C('NO_STATUS');//默认为未审核
        //查看该科室是否有分配审核权限的用户
        $userModel = new UserModel();
        //部门审批
        $departUser = $userModel->getUsers('departApproveBorrow', $assets["departid"], true);
        if (!$departUser) {
            die(json_encode(array('status' => -1, 'msg' => '拥有【借出科室审批权限】的角色没有成员用户或该用户没有当前借出科室的管理权限！请联系系统管理人员设置！')));
        }
        //设备科审批
        $assetsUser = $userModel->getUsers('assetsApproveBorrow', $assets["departid"], true);
        if (!$assetsUser) {
            die(json_encode(array('status' => -1, 'msg' => '拥有【设备科审批权限】的角色没有成员用户或该用户没有当前科室的管理权限！请联系系统管理人员设置！')));
        }
        $departUserArr = [];
        foreach ($departUser as &$departUserValue) {
            $departUserArr[$departUserValue['username']]['username'] = $departUserValue['username'];
            $departUserArr[$departUserValue['username']]['telephone'] = $departUserValue['telephone'];
            $departUserArr[$departUserValue['username']]['openid'] = $departUserValue['openid'];
        }

        //判断departUser是否有当前设备的科室审批负责人
        $tranoutManager = $this->DB_get_one('department', 'manager', array('departid' => $assets["departid"]));

        if (!$tranoutManager['manager']) {
            die(json_encode(array('status' => -1, 'msg' => '借出科室 "' . $departname[$assets['departid']]['department'] . '" 未设置审批负责人,请联系管理员设置！')));
        }

        if (!$departUserArr[$tranoutManager['manager']]) {
            die(json_encode(array('status' => -1, 'msg' => '借出科室 "' . $departname[$assets['departid']]['department'] . '" 负责人 ' . $tranoutManager['manager'] . ' 无借调"借出科室审批权限 请联系管理员设置"')));
        }
        $data['assid'] = $assid;
        $data['borrow_num'] = $this->getFlowNumber($assid);
        $data['apply_userid'] = session('userid');
        $data['borrow_reason'] = $borrow_reason;
        $data['estimate_back'] = strtotime($estimate_back);
        $data['apply_time'] = time();
        $add = $this->insertData('assets_borrow', $data);
        if ($add) {
            $this->setBorrowDetail($add);
            $log['applyDepart'] = $departname[$data['apply_departid']]['department'];
            $log['backDepart'] = $departname[$assets['departid']]['department'];
            $log['assets'] = $assets['assets'];
            $text = getLogText('addBorrowLogText', $log);
            $this->addLog('user', M()->getLastSql(), $text, $add);
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($settingData) {
                //有开启短信
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $data['apply_department'] = $departname[$data['apply_departid']]['department'];
                $data['department'] = $departname[$assets['departid']]['department'];
                $data['assets'] = $assets['assets'];
                $data['estimate_back'] = $estimate_back;
                $ToolMod = new ToolController();
                if ($settingData['doApprove']['status'] == C('OPEN_STATUS') && $departUserArr[$tranoutManager['manager']]['telephone']) {
                    //通知报修用户验收 开启
                    $sms = $this->formatSmsContent($settingData['doApprove']['content'], $data);
                    $ToolMod->sendingSMS($departUserArr[$tranoutManager['manager']]['telephone'], $sms, $this->Controller, $add);
                }
            }
            //==========================================短信 END==========================================

            if (C('USE_FEISHU') === 1) {
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**审批方：**借出科室审批';
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**设备名称：**' . $assets['assets'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**设备编码：**' . $assets['assnum'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**借出科室：**' . $log['backDepart'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**借入科室：**' . $log['applyDepart'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**预计归还时间：**' . $estimate_back;
                $feishu_fields[] = $fd;

                //按钮区域
                $act['tag'] = 'button';
                $act['type'] = 'primary';
                // $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Borrow/departApproveBorrow?borid=' . $add;
                $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Borrow/approveBorrow?borid=' . $add;
                $act['text']['tag'] = 'plain_text';
                $act['text']['content'] = '审批';
                $feishu_actions[] = $act;

                $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag'] = 'div';
                $card_data['elements'][0]['fields'] = $feishu_fields;
                $card_data['elements'][1]['tag'] = 'hr';
                $card_data['elements'][2]['actions'] = $feishu_actions;
                $card_data['elements'][2]['layout'] = 'bisected';
                $card_data['elements'][2]['tag'] = 'action';
                $card_data['header']['template'] = 'blue';
                $card_data['header']['title']['content'] = '设备借调审批申请';
                $card_data['header']['title']['tag'] = 'plain_text';

                if ($departUserArr[$tranoutManager['manager']]['openid']) {
                    $this->send_feishu_card_msg($departUserArr[$tranoutManager['manager']]['openid'], $card_data);
                }
                //==========================================飞书 END==========================================
            } else {
                $moduleModel = new ModuleModel();
                $wx_status = $moduleModel->decide_wx_login();
                if ($wx_status) {
                    //==================================微信通知借调审批 END====================================
                    if ($departUserArr[$tranoutManager['manager']]['openid']) {
                        if (C('USE_VUE_WECHAT_VERSION')) {
                            // $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Borrow/departApproveBorrow?borid=' . $add;
                            $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Borrow/approveBorrow?borid=' . $add;
                        } else {
                            $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Borrow/departApproveBorrow.html?borid=' . $add;
                        }

                        Weixin::instance()->sendMessage($departUserArr[$tranoutManager['manager']]['openid'], '设备借调处理通知', [
                            'thing1'            => $log['applyDepart'],// 需求科室
                            'thing2'            => $assets['assets'],// 需求设备
                            'time5'             => $data['estimate_back'],// 需求结束时间
                            'character_string9' => $data['borrow_num'],// 借调流水号
                            'const10'           => '待审批',// 借调状态
                        ], $redecturl);
                    }
                    //==================================微信通知借调审批 END====================================
                }
            }
            return array('status' => 1, 'msg' => '提交成功,等待借出科室审批');
        } else {
            return array('status' => -1, 'msg' => '提交失败');
        }
    }

    //借调审批列表数据
    public function approveBorrowList()
    {
        $departid = I('POST.departid');
        $model = I('POST.assetsModel');
        $assetsName = I('POST.assetsName');
        $hospital_id = I('POST.hospital_id');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'DESC';
        $sort = I('POST.sort') ? I('POST.sort') : 'borid';
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $where['examine_status'] = array('NEQ', C('STATUS_APPROE_UNWANTED'));
        //借出部门审批
        $departApproveBorrowMenu = get_menu($this->MODULE, $this->Controller, 'departApproveBorrow');
        //设备科审批
        $assetsApproveBorrowMenu = get_menu($this->MODULE, $this->Controller, 'assetsApproveBorrow');
        //有审批权限的设备
        $backAssid = [];
        //负责人的可审批设备
        $managerApproveAssid = [];
        //设备科的可审批设备
        $assetsApproveAssid = [];

        if ($departApproveBorrowMenu) {
            //有借出部门审批权限
            $managerWhere['departid'] = array('in', session('departid'));
            $managerWhere['manager'] = array('EQ', session('username'));
            $managerWhere['hospital_id'] = session('current_hospitalid');
            $manager = $this->DB_get_all('department', 'departid,manager', $managerWhere);
            if ($manager) {
                //负责的科室
                $managerDepairtid = [];
                foreach ($manager as $managerV) {
                    $managerDepairtid[] = $managerV['departid'];
                }
                $assetsDepartWhere['departid'] = array('IN', $managerDepairtid);
                $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
                if ($assetsDepart) {
                    foreach ($assetsDepart as &$assetsDepartV) {
                        $backAssid[] = $assetsDepartV['assid'];
                        $managerApproveAssid[$assetsDepartV['assid']] = true;
                    }
                }
            }
        }

        if ($assetsApproveBorrowMenu) {
            //有设备科审批权限
            $assetsDepartWhere['departid'] = array('IN', session('departid'));
            $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
            if ($assetsDepart) {
                foreach ($assetsDepart as &$assetsDepartV) {
                    $backAssid[] = $assetsDepartV['assid'];
                    $assetsApproveAssid[$assetsDepartV['assid']] = true;
                }
            }
        }

        if (!$backAssid) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $backAssid = array_unique($backAssid);
        $assetsWhere['assid'][] = array('IN', $backAssid);
        if ($model) {
            $assetsWhere['model'] = array('LIKE', '%' . $model . '%');
        }
        if ($assetsName) {
            $assetsWhere['assets'] = array('LIKE', '%' . $assetsName . '%');
        }
        if ($hospital_id) {
            $assetsWhere['hospital_id'] = $hospital_id;
        } else {
            //管理员默认情况下的话只能看到自己工作的医院下的设备
            $assetsWhere['hospital_id'] = session('current_hospitalid');
        }
        $assets = $this->DB_get_all('assets_info', 'assid', $assetsWhere);
        if ($assets) {
            $assetsAssid = [];
            foreach ($assets as &$assetsAssidV) {
                $assetsAssid[] = $assetsAssidV['assid'];
            }
            $where['assid'][] = array('IN', $assetsAssid);
        } else {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if ($departid) {
            $where['apply_departid'][] = array('IN', $departid);
        }
        //获取审批列表信息
        $fileds = 'borid,assid,borrow_num,apply_userid,apply_departid,apply_time,borrow_reason,estimate_back,status,examine_status';
        $total = $this->DB_get_count('assets_borrow', $where);
        $data = $this->DB_get_all('assets_borrow', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);

        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //获取设备基本信息
        $assid = [];
        $userid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
            $userid[] = $dataV['apply_userid'];
            $borid[] = $dataV['borid'];
        }
        $assetsWhere = [];
        $assetsWhere['assid'] = array('IN', $assid);
        $fileds = 'departid,assets,assnum,brand,model,status,assid';
        $assets = $this->DB_get_all('assets_info', $fileds, $assetsWhere);
        $assetsData = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assets as &$assetsV) {
            $assetsData[$assetsV['assid']]['department'] = $departname[$assetsV['departid']]['department'];
            $assetsData[$assetsV['assid']]['assets'] = $assetsV['assets'];
            $assetsData[$assetsV['assid']]['assnum'] = $assetsV['assnum'];
            $assetsData[$assetsV['assid']]['brand'] = $assetsV['brand'];
            $assetsData[$assetsV['assid']]['model'] = $assetsV['model'];
            switch ($assetsV['status']) {
                case C('ASSETS_STATUS_USE'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_SCRAP_NAME');
                    break;
            }
        }
        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        $user = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }
        //获取审核记录
        $bor_approveWhere['borid'] = array('IN', $borid);
        $bor_approve = [];
        $bor_approve_data = $this->DB_get_all('assets_borrow_approve', 'level,approve_status,borid', $bor_approveWhere);
        foreach ($bor_approve_data as &$bor_approveV) {
            $bor_approve[$bor_approveV['borid']][$bor_approveV['level']] = $bor_approveV['approve_status'];
        }
        $success_i = '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
        $fail_i = '<i class="layui-icon layui-icon-zzclose" style="color: red"></i>';
        foreach ($data as &$dataV) {
            $dataV['department'] = $assetsData[$dataV['assid']]['department'];
            $dataV['assets'] = $assetsData[$dataV['assid']]['assets'];
            $dataV['assnum'] = $assetsData[$dataV['assid']]['assnum'];
            $dataV['brand'] = $assetsData[$dataV['assid']]['brand'];
            $dataV['model'] = $assetsData[$dataV['assid']]['model'];
            $dataV['statusName'] = $assetsData[$dataV['assid']]['statusName'];
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back'] = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time'] = getHandleTime($dataV['apply_time']);
            $dataV['apply_user'] = $userData[$dataV['apply_userid']]['username'];
            //详情url
            $detailsUrl = get_url() . '?action=showApproveDetails&borid=' . $dataV['borid'] . '&assid=' . $dataV['assid'];
            if ($dataV['examine_status'] == C('STATUS_APPROE_SUCCESS')) {
                //审批完成
                $dataV['operation'] = $this->returnListLink('已通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-normal');
                $dataV['deparment_approve'] = $success_i;
                $dataV['assets_approve'] = $success_i;
            } elseif ($dataV['examine_status'] == C('STATUS_APPROE_FAIL')) {
                //审批不通过
                $dataV['operation'] = $this->returnListLink('不通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-danger');
                if ($bor_approve[$dataV['borid']][1]['approve_status'] == 1) {
                    $dataV['deparment_approve'] = $success_i;
                } else if ($bor_approve[$dataV['borid']][1]['approve_status'] == 2) {
                    $dataV['deparment_approve'] = $fail_i;
                }
                if ($bor_approve[$dataV['borid']][2]['approve_status'] == 1) {
                    $dataV['assets_approve'] = $success_i;
                } else if ($bor_approve[$dataV['borid']][2]['approve_status'] == 2) {
                    $dataV['assets_approve'] = $fail_i;
                }
            } else {
                //查询审批历史
                $apps = $this->DB_get_all('assets_borrow_approve', '', array('borid' => $dataV['borid']), '', 'level,approve_time asc');
                if ((!$apps && $managerApproveAssid[$dataV['assid']]) or session('isSuper') == C('YES_STATUS')) {
                    //未审批,是第一个审批人
                    $dataV['operation'] = $this->returnListLink('审批', $departApproveBorrowMenu['actionurl'], 'approveBorrow', C('BTN_CURRENCY'));;
                    if ($bor_approve[$dataV['borid']][1]['approve_status'] == 1) {
                        $dataV['deparment_approve'] = $success_i;
                    } else if ($bor_approve[$dataV['borid']][1]['approve_status'] == 2) {
                        $dataV['deparment_approve'] = $fail_i;
                    }
                    if ($bor_approve[$dataV['borid']][2]['approve_status'] == 1) {
                        $dataV['assets_approve'] = $success_i;
                    } else if ($bor_approve[$dataV['borid']][2]['approve_status'] == 2) {
                        $dataV['assets_approve'] = $fail_i;
                    }
                    continue;
                }
                if (!$apps && !$managerApproveAssid[$dataV['assid']]) {
                    //未审批,不是第一个审批人
                    $dataV['operation'] = $this->returnListLink('待审批', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-warm');
                    if ($bor_approve[$dataV['borid']][1]['approve_status'] == 1) {
                        $dataV['deparment_approve'] = $success_i;
                    } else if ($bor_approve[$dataV['borid']][1]['approve_status'] == 2) {
                        $dataV['deparment_approve'] = $fail_i;
                    }
                    if ($bor_approve[$dataV['borid']][2]['approve_status'] == 1) {
                        $dataV['assets_approve'] = $success_i;
                    } else if ($bor_approve[$dataV['borid']][2]['approve_status'] == 2) {
                        $dataV['assets_approve'] = $fail_i;
                    }
                    continue;
                }
                //已审批
                if ($apps && $dataV['examine_status'] == C('APPROVE_STATUS')) {
                    //审批中
                    //设备科审批已通过
                    $dataV['deparment_approve'] = $success_i;
                    if ($assetsApproveAssid[$dataV['assid']] or session('isSuper') == C('YES_STATUS')) {
                        //有设备科审批权限
                        $dataV['operation'] = $this->returnListLink('审批', $assetsApproveBorrowMenu['actionurl'], 'approveBorrow', C('BTN_CURRENCY'));;
                        if ($bor_approve[$dataV['borid']][1]['approve_status'] == 1) {
                            $dataV['deparment_approve'] = $success_i;
                        } else if ($bor_approve[$dataV['borid']][1]['approve_status'] == 2) {
                            $dataV['deparment_approve'] = $fail_i;
                        }
                        if ($bor_approve[$dataV['borid']][2]['approve_status'] == 1) {
                            $dataV['assets_approve'] = $success_i;
                        } else if ($bor_approve[$dataV['borid']][2]['approve_status'] == 2) {
                            $dataV['assets_approve'] = $fail_i;
                        }
                        continue;
                    } else {
                        //没有设备科审批权限
                        $dataV['operation'] = $this->returnListLink('待审批', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-warm');
                        if ($bor_approve[$dataV['borid']][1]['approve_status'] == 1) {
                            $dataV['deparment_approve'] = $success_i;
                        } else if ($bor_approve[$dataV['borid']][1]['approve_status'] == 2) {
                            $dataV['deparment_approve'] = $fail_i;
                        }
                        if ($bor_approve[$dataV['borid']][2]['approve_status'] == 1) {
                            $dataV['assets_approve'] = $success_i;
                        } else if ($bor_approve[$dataV['borid']][2]['approve_status'] == 2) {
                            $dataV['assets_approve'] = $fail_i;
                        }
                        continue;
                    }
                }
                if ($dataV['examine_status'] == C('STATUS_APPROE_FAIL')) {
                    //审批不通过
                    $dataV['operation'] = $this->returnListLink('不通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-danger');
                    foreach ($apps as &$appsV) {
                        if ($appsV['level'] == 1) {
                            //第一个流程：借出科室审批
                            if ($appsV['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                                $dataV['deparment_approve'] = $success_i;
                            } else {
                                $dataV['deparment_approve'] = $fail_i;
                            }
                        }
                        if ($appsV['level'] == 2) {
                            //第二个流程：设备科审核失败
                            $dataV['assets_approve'] = $fail_i;
                        }
                    }
                }
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //借调审批操作
    public function approveBorrow()
    {
        $borid = I('POST.borid');
        if (!$borid) {
            die(json_encode(array('status' => -1, 'msg' => '非法操作')));
        }
        $data['borid'] = $borid;
        //检查是否存在此条记录
        $where['borid'] = array('EQ', $borid);
        $borrow = $this->DB_get_one('assets_borrow', 'borid,assid,borrow_num,apply_departid,estimate_back,apply_userid,apply_time,status', $where);
        if (!$borrow) {
            die(json_encode(array('status' => -1, 'msg' => '无借调信息')));
        }
        $assets = $this->DB_get_one('assets_info', 'departid,assets,assnum', array('assid' => $borrow['assid']));
        $apps = $this->DB_get_all('assets_borrow_approve', '', array('borid' => $borid, 'approve_status' => 1), '', 'level,approve_time asc');
        if (!$apps) {
            //借出部门审批
            $departApproveBorrowMenu = get_menu($this->MODULE, $this->Controller, 'departApproveBorrow');
            if (!$departApproveBorrowMenu) {
                die(json_encode(array('status' => -1, 'msg' => '无借出科室审批权限')));
            }
            $managerWhere['departid'] = array('EQ', $assets['departid']);
            $managerWhere['manager'] = array('EQ', session('username'));
            $managerWhere['hospital_id'] = array('EQ', session('job_hospitalid'));
            //负责人查询
            $manager = $this->DB_get_one('department', 'departid,manager', $managerWhere);
            if (!$manager && session('isSuper') != C('YES_STATUS')) {
                die(json_encode(array('status' => -1, 'msg' => '您没有当前设备科室的审批负责权限')));
            }
            return $this->addApproveBorrow(1, $borrow, 2, $assets);
        } elseif (count($apps) == 1 && $borrow['status'] == C('BORROW_STATUS_APPROVE')) {
            //设备科审批
            $assetsApproveBorrowMenu = get_menu($this->MODULE, $this->Controller, 'assetsApproveBorrow');
            if (!$assetsApproveBorrowMenu && session('isSuper') != C('YES_STATUS')) {
                die(json_encode(array('status' => -1, 'msg' => '请等待设备科审批')));
            }
            return $this->addApproveBorrow(2, $borrow, 2, $assets);
        } else {
            die(json_encode(array('status' => -1, 'msg' => '请勿重复提交')));
        }
    }

    //借入验收列表
    public function borrowInCheckList()
    {
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'DESC';
        $sort = I('POST.sort') ? I('POST.sort') : 'borid';
        $where['A.status'] = array('EQ', C('BORROW_STATUS_BORROW_IN'));
        if (session('isSuper') != C('YES_STATUS')) {
            if (!session('job_departid')) {
                $result['msg'] = '该用户未分配工作科室';
                $result['code'] = 400;
                return $result;
            }
            $where['A.apply_departid'] = array('IN', session('job_departid'));
        }
        $where['B.hospital_id'] = session('current_hospitalid');
        //获取审批列表信息
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fileds = 'A.borid,A.assid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,A.borrow_reason,A.estimate_back,A.status,A.examine_status,A.borrow_in_time,B.departid,B.assets,B.assnum,B.brand,B.model,B.status AS a_status';
        $total = $this->DB_get_count_join('assets_borrow', 'A', $join, $where);
        $data = $this->DB_get_all_join('assets_borrow', 'A', $fileds, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //获取设备基本信息
        $assid = [];
        $userid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $borid[] = $dataV['borid'];
            $assid[] = $dataV['assid'];
            $userid[] = $dataV['apply_userid'];
        }

        //获取附属设备明细
        $subsidiaryWhere['borid'] = ['IN', $borid];
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price,D.borid';
        $subsidiary = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join, $subsidiaryWhere);
        $subsidiaryData = [];
        if ($subsidiary) {
            foreach ($subsidiary as &$subV) {
                $subsidiaryData[$subV['borid']][] = $subV;
            }
        }
        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        $user = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }
        $success_i = '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
        $borrowInCheckMenu = get_menu($this->MODULE, $this->Controller, 'borrowInCheck');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$dataV) {
            $dataV['subsidiary'] = $subsidiaryData[$dataV['borid']];
            $dataV['department'] = $departname[$dataV['departid']]['department'];
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back'] = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time'] = getHandleTime($dataV['apply_time']);
            $dataV['apply_user'] = $userData[$dataV['apply_userid']]['username'];
            if ($dataV['examine_status'] == C('STATUS_APPROE_SUCCESS')) {
                $dataV['deparment_approve'] = $success_i;
                $dataV['assets_approve'] = $success_i;
            }
            if ($borrowInCheckMenu) {
                $dataV['borrow_in_time'] = '<div class="borrow_in_time">请点击确认录入时间</div><input type="hidden" name="borid" value="' . $dataV['borid'] . '">';
                $dataV['operation'] = '<div class="layui-btn-group">';
                $dataV['operation'] .= $this->returnListLink('确认设备完好无损并借入使用', $borrowInCheckMenu['actionurl'], 'borrowInCheck', C('BTN_CURRENCY'));
                $dataV['operation'] .= $this->returnListLink('不借入并结束流程', $borrowInCheckMenu['actionurl'], 'notBorrowInCheck', C('BTN_CURRENCY') . ' layui-btn-warm');
                $dataV['operation'] .= '</div>';
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //借入验收操作
    public function borrowInCheck()
    {
        //var_dump($_POST);die;
        $status = I('POST.status');
        $borrow_in_time = trim(I('POST.borrow_in_time'));
        $borid = I('POST.borid');
        $end_reason = trim(I('POST.end_reason'));
        $supplement = trim(I('POST.supplement'));
        $this->checkstatus(judgeNum($borid), '非法操作');
        $this->checkstatus(judgeNum($status), '非法操作');
        $data = $this->DB_get_one('assets_borrow', '', ['borid' => $borid]);
        if ($data) {
            if ($status == C('BORROW_STATUS_GIVE_BACK')) {
                if (!$borrow_in_time) {
                    die(json_encode(array('status' => -1, 'msg' => '请先补充确定借入时间')));
                }
                $data['borrow_in_time'] = strtotime($borrow_in_time);
                $data['supplement'] = $supplement;
                $msg = '验收信息已记录,请按时归还';
            } else {
                if (!$end_reason) {
                    die(json_encode(array('status' => -1, 'msg' => '请补充不借入的原因')));
                }
                $data['end_reason'] = $end_reason;
                $data['not_apply_time'] = time();
                $msg = '已取消本次借调申请';
            }
            $data['status'] = $status;
            $data['borrow_in_userid'] = session('userid');
            $save = $this->updateData('assets_borrow', $data, array('borid' => $borid));
            if ($save) {
                $log['borrow_num'] = $data['borrow_num'];
                $log['status'] = $data['status'] == C('BORROW_STATUS_GIVE_BACK') ? '借入验收通过' : '取消借入申请';
                $text = getLogText('borrowInCheckText', $log);
                $this->addLog('sb_assets_borrow', M()->getLastSql(), $text, $data['borid']);
                $departname = [];
                $assets = $this->DB_get_one('assets_info', 'assets,departid', ['assid' => $data['assid']]);
                include APP_PATH . "Common/cache/department.cache.php";
                $data['apply_department'] = $departname[$data['apply_departid']]['department'];
                $data['department'] = $departname[$assets['departid']]['department'];
                $data['estimate_back'] = getHandleMinute($data['estimate_back']);
                $data['examine_status'] = $data['examine_status'] == C('STATUS_APPROE_SUCCESS') ? '同意' : '不同意';
                $data['assets'] = $assets['assets'];
                $ToolMod = new ToolController();
                $UserData = $ToolMod->getUser('giveBackCheck', $data['departid']);
                //==========================================短信 START==========================================
                $settingData = $this->checkSmsIsOpen($this->Controller);
                if ($settingData) {
                    if ($status == C('BORROW_STATUS_GIVE_BACK')) {
                        //借入
                        if ($settingData['borrowInCheck']['status'] == C('OPEN_STATUS') && $UserData) {
                            //通知被借科室验收情况 开启
                            $phone = $this->formatPhone($UserData);
                            $sms = $this->formatSmsContent($settingData['borrowInCheck']['content'], $data);
                            $ToolMod->sendingSMS($phone, $sms, $this->Controller, $data['borid']);
                        }
                    } else {
                        //不借入
                        if ($settingData['borrowNotApply']['status'] == C('OPEN_STATUS') && $UserData) {
                            //通知被借科室暂时不需要借入 开启
                            $phone = $this->formatPhone($UserData);
                            $sms = $this->formatSmsContent($settingData['borrowNotApply']['content'], $data);
                            $ToolMod->sendingSMS($phone, $sms, $this->Controller, $data['borid']);
                        }
                    }
                }
                //==========================================短信 END============================================

                if (C('USE_FEISHU') === 1) {
                    //==========================================飞书 START========================================
                    //要显示的字段区域
                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**借调单号：**' . $data['borrow_num'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备名称：**' . $assets['assets'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备编码：**' . $assets['assnum'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**所属科室：**' . $data['department'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**借调科室：**' . $data['apply_department'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**验收人：**' . session('username');
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**预计归还时间：**' . $data['estimate_back'];
                    $feishu_fields[] = $fd;

                    $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                    $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                    $card_data['elements'][0]['tag'] = 'div';
                    $card_data['elements'][0]['fields'] = $feishu_fields;
                    $card_data['header']['template'] = 'blue';
                    $card_data['header']['title']['content'] = '设备借调验收结果提醒';
                    $card_data['header']['title']['tag'] = 'plain_text';

                    $toUser = $this->getToUser(session('userid'), $assets['departid'], 'Assets', 'Borrow', 'giveBackCheck');
                    foreach ($toUser as $k => $v) {
                        $this->send_feishu_card_msg($v['openid'], $card_data);
                    }
                    //==========================================飞书 END==========================================
                } else {
                    $moduleModel = new ModuleModel();
                    $wx_status = $moduleModel->decide_wx_login();
                    if ($wx_status) {
                        //=======================================微信提示 START==========================================
                        /** @var UserModel[] $users */
                        $users = $this->getToUser(session('userid'), $assets['departid'], 'Assets', 'Borrow', 'giveBackCheck');
                        $openIds = array_column($users, 'openid');
                        $openIds = array_filter($openIds);
                        $openIds = array_unique($openIds);

                        $messageData = [
                            'thing1'            => $data['apply_department'],// 需求科室
                            'thing2'            => $assets['assets'],// 需求设备
                            'time5'             => $data['estimate_back'],// 需求结束时间
                            'character_string9' => $data['borrow_num'],// 借调流水号
                            'const10'           => (int) $status === C('BORROW_STATUS_GIVE_BACK') ? '已验收，待归还' : '已拒绝',// 借调状态
                        ];

                        foreach ($openIds as $openId) {
                            Weixin::instance()->sendMessage($openId, '设备借调处理通知', $messageData);
                        }
                        //=======================================微信提示 END============================================
                    }
                }
                return array('status' => 1, 'msg' => $msg);
            } else {
                return array('status' => -1, 'msg' => '提交失败');
            }
        } else {
            die(json_encode(array('status' => -1, 'msg' => '非法操作')));
        }
    }

    //归还验收列表
    public function giveBackCheckList()
    {
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'borid';
        $where['status'] = array('EQ', C('BORROW_STATUS_GIVE_BACK'));
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $assetsDepartWhere['departid'] = array('IN', session('departid'));
        $assetsDepartWhere['hospital_id'] = session('current_hospitalid');
        $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
        if (!$assetsDepart) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $backAssid = [];
        foreach ($assetsDepart as &$assetsDepartV) {
            $backAssid[] = $assetsDepartV['assid'];
        }
        $where['assid'] = array('IN', $backAssid);
        //获取审批列表信息
        $fileds = 'borid,assid,borrow_num,apply_userid,apply_departid,apply_time,borrow_reason,estimate_back,status,examine_status,borrow_in_time,supplement';
        $total = $this->DB_get_count('assets_borrow', $where);
        $data = $this->DB_get_all('assets_borrow', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //获取设备基本信息
        $assid = [];
        $userid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
            $userid[] = $dataV['apply_userid'];
            $borid[] = $dataV['borid'];
        }
        $assetsWhere['assid'] = array('IN', $assid);
        $fileds = 'assets,assnum,status,assid';
        $assets = $this->DB_get_all('assets_info', $fileds, $assetsWhere);
        $assetsData = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assets as &$assetsV) {
            $assetsData[$assetsV['assid']]['assets'] = $assetsV['assets'];
            $assetsData[$assetsV['assid']]['assnum'] = $assetsV['assnum'];
            switch ($assetsV['status']) {
                case C('ASSETS_STATUS_USE'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_SCRAP_NAME');
                    break;
            }
        }
        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        $user = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }

        //获取附属设备明细
        $subsidiaryWhere['borid'] = ['IN', $borid];
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price,D.borid';
        $subsidiary = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join, $subsidiaryWhere);
        $subsidiaryData = [];
        if ($subsidiary) {
            foreach ($subsidiary as &$subV) {
                $subsidiaryData[$subV['borid']][] = $subV;
            }
        }
        $giveBackCheckMenu = get_menu($this->MODULE, $this->Controller, 'giveBackCheck');
        foreach ($data as &$dataV) {
            $dataV['subsidiary'] = $subsidiaryData[$dataV['borid']];
            $dataV['assets'] = $assetsData[$dataV['assid']]['assets'];
            $dataV['assnum'] = $assetsData[$dataV['assid']]['assnum'];
            $dataV['statusName'] = $assetsData[$dataV['assid']]['statusName'];
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back'] = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time'] = getHandleMinute($dataV['apply_time']);
            $dataV['borrow_in_time'] = getHandleMinute($dataV['borrow_in_time']);
            $dataV['apply_user'] = $userData[$dataV['apply_userid']]['username'];
            $dataV['give_back_time'] = '<div class="give_back_time">请点击录入归还时间</div><input type="hidden" name="borid" value="' . $dataV['borid'] . '">';
            if ($giveBackCheckMenu) {
                $dataV['operation'] = $this->returnListLink('确认设备完好无损并结束流程', $giveBackCheckMenu['actionurl'], 'borrowBackCheck', C('BTN_CURRENCY'));
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //归还验收操作
    public function giveBackCheck()
    {
        $give_back_time = I('POST.give_back_time');
        $borid = I('POST.borid');
        $this->checkstatus(judgeNum($borid), '非法操作');
        $data = $this->DB_get_one('assets_borrow', '', ['borid' => $borid]);
        if ($data) {
            if (!$give_back_time) {
                die(json_encode(array('status' => -1, 'msg' => '请先补充确定借入时间')));
            }
            $data['give_back_time'] = strtotime($give_back_time);
            $data['status'] = C('BORROW_STATUS_COMPLETE');
            $data['give_back_userid'] = session('userid');
            $save = $this->updateData('assets_borrow', $data, array('borid' => $borid));
            if ($save) {
                $log['borrow_num'] = $data['borrow_num'];
                $text = getLogText('giveBackCheckText', $log);
                $this->addLog('sb_assets_borrow', M()->getLastSql(), $text, $data['borid']);

                $departname = [];
                $assets = $this->DB_get_one('assets_info', 'assets,assnum,departid', ['assid' => $data['assid']]);
                include APP_PATH . "Common/cache/department.cache.php";
                $data['apply_department'] = $departname[$data['apply_departid']]['department'];
                $data['department'] = $departname[$assets['departid']]['department'];
                $data['estimate_back'] = getHandleMinute($data['estimate_back']);
                $data['giveBack_time'] = $give_back_time;
                $data['examine_status'] = $data['examine_status'] == C('STATUS_APPROE_SUCCESS') ? '同意' : '不同意';
                $data['assets'] = $assets['assets'];
                $ToolMod = new ToolController();
                $where = [];
                $where['status'] = C('OPEN_STATUS');
                $where['is_delete'] = C('NO_STATUS');
                $where['userid'] = $data['apply_userid'];
                $apply_user = $this->DB_get_one('user', 'openid,telephone', $where);
                //==========================================短信 START==========================================
                $settingData = $this->checkSmsIsOpen($this->Controller);
                if ($settingData) {
                    //有开启短信 通知借调申请人归还验收结果
                    if ($settingData['borrowGiveBack']['status'] == C('OPEN_STATUS') && $apply_user) {
                        //通知审批结果 开启
                        $sms = $this->formatSmsContent($settingData['borrowGiveBack']['content'], $data);
                        $ToolMod->sendingSMS($apply_user['telephone'], $sms, $this->Controller, $data['borid']);
                    }
                }
                //==========================================短信 END============================================

                if (C('USE_FEISHU') === 1) {
                    //==========================================飞书 START========================================
                    //要显示的字段区域
                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**借调单号：**' . $data['borrow_num'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备名称：**' . $assets['assets'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**所属部门：**' . $data['department'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**借调部门：**' . $data['apply_department'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '请检查确认设备是否完好无损';
                    $feishu_fields[] = $fd;

                    $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                    $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                    $card_data['elements'][0]['tag'] = 'div';
                    $card_data['elements'][0]['fields'] = $feishu_fields;
                    $card_data['header']['template'] = 'blue';
                    $card_data['header']['title']['content'] = '设备借调归还提醒';
                    $card_data['header']['title']['tag'] = 'plain_text';

                    if ($apply_user['openid']) {
                        $this->send_feishu_card_msg($apply_user['openid'], $card_data);
                    }
                    //==========================================飞书 END==========================================
                } else {
                    //==========================================微信 START============================================
                    $moduleModel = new ModuleModel();
                    $wx_status = $moduleModel->decide_wx_login();
                    if ($apply_user['openid'] && $wx_status) {
                        Weixin::instance()->sendMessage($apply_user['openid'], '设备归还成功通知', [
                            'thing3'            => $assets['assets'],// 设备名称
                            'character_string8' => $assets['assnum'],// 设备号
                            'character_string1' => $data['borrow_num'],// 借用单号
                            'thing4'            => session('username'),// 归还处理人
                        ]);
                    }
                    //=======================================微信提示 END============================================
                }
                return array('status' => 1, 'msg' => '提交成功,借调结束');
            } else {
                return array('status' => -1, 'msg' => '提交失败');
            }
        } else {
            die(json_encode(array('status' => -1, 'msg' => '非法操作')));
        }

    }

    //借调进程列表
    public function borrowLife()
    {
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'DESC';
        $sort = I('POST.sort') ? I('POST.sort') : 'borid';
        $startTime = strtotime(date("Ymd"));
        //echo date("Y-m-d",$startTime);die;
        $endTime = $startTime + 86399;
        //获取所管理科室下面的设备
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $assetsDepartWhere['hospital_id'] = session('current_hospitalid');
        $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
        if (!$assetsDepart) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $backAssid = [];
        foreach ($assetsDepart as &$assetsDepartV) {
            $backAssid[] = $assetsDepartV['assid'];
        }
        $where['assid'] = array('IN', $backAssid);

        $showStatus[] = C('BORROW_STATUS_APPROVE');
        $showStatus[] = C('BORROW_STATUS_BORROW_IN');
        $showStatus[] = C('BORROW_STATUS_GIVE_BACK');
//        //正常流程的设备
        $where[1][1]['status'] = array('IN', $showStatus);

        //或者当天结束的设备
        //1.完成验收
        $where[1][2][1]['status'] = array('EQ', C('BORROW_STATUS_COMPLETE'));
        $where[1][2][1][]['give_back_time'] = array('EGT', $startTime);
        $where[1][2][1][]['give_back_time'] = array('ELT', $endTime);

        //2.不借调
        $where[1][2][2]['status'] = array('EQ', C('BORROW_STATUS_NOT_APPLY'));
        $where[1][2][2][]['not_apply_time'] = array('EGT', $startTime);
        $where[1][2][2][]['not_apply_time'] = array('ELT', $endTime);

        //3.审批不通过
        $where[1][2][3]['status'] = array('EQ', C('BORROW_STATUS_FAIL'));
        $where[1][2][3][]['examine_time'] = array('EGT', $startTime);
        $where[1][2][3][]['examine_time'] = array('ELT', $endTime);

        $where[1][2]['_logic'] = 'or';
        $where[1]['_logic'] = 'or';
        //获取审批列表信息
        $fileds = 'borid,assid,borrow_num,apply_departid,estimate_back,apply_time,not_apply_time,borrow_in_time,
        borrow_in_userid,give_back_time,give_back_userid,examine_status,status,examine_time';
        $total = $this->DB_get_count('assets_borrow', $where);
        //echo $total;die;
        $data = $this->DB_get_all('assets_borrow', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //获取设备基本信息
        $assid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
            $borid[] = $dataV['borid'];
        }
        $assetsWhere['assid'] = array('IN', $assid);
        $fileds = 'departid,assets,assnum,assid';
        $assets = $this->DB_get_all('assets_info', $fileds, $assetsWhere);
        $assetsData = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assets as &$assetsV) {
            $assetsData[$assetsV['assid']]['department'] = $departname[$assetsV['departid']]['department'];
            $assetsData[$assetsV['assid']]['assets'] = $assetsV['assets'];
            $assetsData[$assetsV['assid']]['assnum'] = $assetsV['assnum'];
            $assetsData[$assetsV['assid']]['brand'] = $assetsV['brand'];
            $assetsData[$assetsV['assid']]['model'] = $assetsV['model'];
            switch ($assetsV['status']) {
                case C('ASSETS_STATUS_USE'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_SCRAP_NAME');
                    break;
            }
        }

        $approveWhere['borid'] = array('IN', $borid);
        $approve = $this->DB_get_all('assets_borrow_approve', 'approve_time,level,borid', $approveWhere, '', 'borid asc,level asc');

        $approveData = [];
        foreach ($approve as &$approveV) {
            $approveData[$approveV['borid']][$approveV['level']]['approve_time'] = getHandleMinute($approveV['approve_time']);
        }

        foreach ($data as &$dataV) {
            $dataV['department'] = $assetsData[$dataV['assid']]['department'];
            $dataV['assets'] = $assetsData[$dataV['assid']]['assets'];
            $dataV['assnum'] = $assetsData[$dataV['assid']]['assnum'];
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back'] = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time'] = getHandleMinute($dataV['apply_time']);
            $dataV['borrow_in_time'] = getHandleMinute($dataV['borrow_in_time']);
            $dataV['give_back_time'] = getHandleMinute($dataV['give_back_time']);
            $dataV['not_apply_time'] = getHandleMinute($dataV['not_apply_time']);
            //var_dump($approveData);die;
            $dataV['approve'] = $approveData[$dataV['borid']];
            //var_dump($dataV['approve']);die;
            $dataV['operation'] = $this->machiningProgress($dataV);
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //借调记录列表
    public function borrowRecordList()
    {
        //如果是生命历程页面
        $showlifeBorrow = I('post.showlifeBorrow');
        $showlifeAssid = I('post.assid');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'DESC';
        $sort = I('POST.sort') ? I('POST.sort') : 'borid';
        $departid = I('POST.departid');
        $model = I('POST.assetsModel');
        $assetsName = I('POST.assetsName');
        $hospital_id = I('POST.hospital_id');
        $apply_departid = I('POST.apply_departid');
        //获取所管理科室下面的设备
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        if ($showlifeBorrow) {
            $where['assid'] = $showlifeAssid;
        } else {
            if ($departid) {
                $assetsDepartWhere['departid'][] = array('IN', $departid);
            }

            if ($model) {
                $assetsDepartWhere['model'] = array('LIKE', '%' . $model . '%');
            }

            if ($assetsName) {
                $assetsDepartWhere['assets'] = array('LIKE', '%' . $assetsName . '%');
            }

            if ($hospital_id) {
                $assetsDepartWhere['hospital_id'] = $hospital_id;
            } else {
                $assetsDepartWhere['hospital_id'] = session('current_hospitalid');
            }

            if ($apply_departid) {
                $where['apply_departid'] = array('IN', $apply_departid);
            }
            $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
            //获取所管理科室下面的设备
            if (!$assetsDepart) {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
            $backAssid = [];
            foreach ($assetsDepart as &$assetsDepartV) {
                $backAssid[] = $assetsDepartV['assid'];
            }
            $where['assid'] = array('IN', $backAssid);
        }
        //获取审批列表信息
        $fileds = 'borid,assid,borrow_num,apply_userid,apply_departid,estimate_back,apply_time,not_apply_time,borrow_in_time,
        borrow_in_userid,give_back_time,give_back_userid,examine_status,status,examine_time';
        $total = $this->DB_get_count('assets_borrow', $where);
        $data = $this->DB_get_all('assets_borrow', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $join = 'LEFT JOIN sb_assets_borrow_detail AS B ON A.borid = B.borid';
            $data = $this->DB_get_all_join('assets_borrow', 'A', '', $join, array('subsidiary_assid' => $showlifeAssid));
            if (!$data) {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        }
        //获取设备基本信息
        $assid = [];
        $borid = [];
        $userid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
            $borid[] = $dataV['borid'];
            $userid[] = $dataV['apply_userid'];
        }

        $assetsWhere['assid'] = array('IN', $assid);
        $fileds = 'departid,assets,assnum,assid,brand,model';
        $assets = $this->DB_get_all('assets_info', $fileds, $assetsWhere);
        $assetsData = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assets as &$assetsV) {

            $assetsData[$assetsV['assid']]['department'] = $departname[$assetsV['departid']]['department'];
            $assetsData[$assetsV['assid']]['assets'] = $assetsV['assets'];
            $assetsData[$assetsV['assid']]['assnum'] = $assetsV['assnum'];
            $assetsData[$assetsV['assid']]['brand'] = $assetsV['brand'];
            $assetsData[$assetsV['assid']]['model'] = $assetsV['model'];

            switch ($assetsV['status']) {
                case C('ASSETS_STATUS_USE'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_SCRAP_NAME');
                    break;
            }
        }
        //var_dump($assetsData);die;
        //获取审核信息
        $approveWhere['borid'] = array('IN', $borid);
        $approve = $this->DB_get_all('assets_borrow_approve', 'approve_time,level,borid', $approveWhere, '', 'borid asc,level asc');

        $approveData = [];
        foreach ($approve as &$approveV) {
            $approveData[$approveV['borid']][$approveV['level']]['approve_time'] = getHandleMinute($approveV['approve_time']);
        }
        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        //var_dump($userWhere);die;
        $user = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }
        $borrowRecordList = get_menu($this->MODULE, $this->Controller, 'borrowRecordList');
        $success_i = '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
        $fail_i = '<i class="layui-icon layui-icon-zzclose" style="color: red"></i>';
        foreach ($data as &$dataV) {
            $dataV['department'] = $assetsData[$dataV['assid']]['department'];
            $dataV['assets'] = $assetsData[$dataV['assid']]['assets'];
            $dataV['assnum'] = $assetsData[$dataV['assid']]['assnum'];
            $dataV['brand'] = $assetsData[$dataV['assid']]['brand'];
            $dataV['model'] = $assetsData[$dataV['assid']]['model'];
            $dataV['apply_user'] = $userData[$dataV['apply_userid']]['username'];
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back'] = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time'] = getHandleMinute($dataV['apply_time']);
            $dataV['borrow_in_time'] = getHandleMinute($dataV['borrow_in_time']);
            $dataV['give_back_time'] = getHandleMinute($dataV['give_back_time']);
            $dataV['not_apply_time'] = getHandleMinute($dataV['not_apply_time']);
            $dataV['approve'] = $approveData[$dataV['borid']];


            $dataV['operation'] = '<div class="layui-btn-group">';

            if ($showlifeBorrow) {
                if ($borrowRecordList) {
                    $dataV['operation'] .= $this->returnListLink('详情', C('ADMIN_NAME') . '/Borrow/borrowRecordList?action=showBorrowRecordDetails&borid=' . $dataV['borid'] . '&assid=' . $dataV['assid'], 'showDetails', C('BTN_CURRENCY') . ' layui-btn-normal');
                }
            } else {
                $dataV['operation'] .= $this->returnListLink('详情', get_url() . '?action=showBorrowRecordDetails&borid=' . $dataV['borid'] . '&assid=' . $dataV['assid'], 'showDetails', C('BTN_CURRENCY') . ' layui-btn-normal');
            }
            if ($dataV['status'] == C('BORROW_STATUS_COMPLETE')) {
                $printReportUrl = get_url() . '?action=printReport&assid=' . $dataV['assid'] . '&borid=' . $dataV['borid'];
                $uploadReportUrl = get_url() . '?action=uploadReport&borid=' . $dataV['borid'];
                $dataV['operation'] .= $this->returnListLink('打印审批单', $printReportUrl, 'printReport', C('BTN_CURRENCY'));
                $dataV['operation'] .= $this->returnListLink('上传/查看审批单', $uploadReportUrl, 'uploadReport', C('BTN_CURRENCY') . ' layui-btn-warm');
            }

            $dataV['operation'] .= '</div>';

            //var_dump($dataV['approve']);die;
            if ($dataV['status'] == C('BORROW_STATUS_FAIL')) {
                if (!$dataV['approve'][2]) {
                    //借出科室审批不通过
                    $dataV['deparment_approve'] = $fail_i;
                } else {
                    //设备科审批不通过
                    $dataV['deparment_approve'] = $success_i;
                    $dataV['assets_approve'] = $fail_i;
                }
            } elseif ($dataV['status'] == C('BORROW_STATUS_APPROVE')) {
                //申请状态 借出科室审核数据存在 ：借出科室已审批通过
                if ($dataV['approve'][1]) {
                    $dataV['deparment_approve'] = $success_i;
                }
            } else {
                $dataV['deparment_approve'] = $success_i;
                $dataV['assets_approve'] = $success_i;
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    /**
     * 加工处理进度列表进度数据
     * @param $data array 需要加工的数据
     * @return string
     * */
    private function machiningProgress($data)
    {

        //默认icon
        $apply_icon = '&#xe63f;';
        $depart_porrove_icon = '&#xe63f;';
        $assets_porrove_icon = '&#xe63f;';
        $borrow_in_icon = '&#xe63f;';
        $give_back_icon = '&#xe63f;';

        //默认颜色

        $depart_porrove_color = 'unexecutedColor';
        $assets_porrove_color = 'unexecutedColor';
        $borrow_in_color = 'unexecutedColor';
        $give_back_color = 'unexecutedColor';


        //设备科审批未通过
        if ($data['status'] == C('BORROW_STATUS_FAIL')) {
            if (!$data['approve'][2]) {
                //借出科室审批不通过
                $depart_porrove_icon = '&#x1006;';
                $depart_porrove_color = 'endColor';
            } else {
                //设备科审批不通过
                $assets_porrove_icon = '&#x1006;';
                $depart_porrove_color = 'executeddColor';
                $assets_porrove_color = 'endColor';
            }
        }


        //申请状态 /包括借出科室审批(借调状态设备科审批通过后 或 者审批出现不通过 才会修改状态)
        if ($data['status'] == C('BORROW_STATUS_APPROVE')) {
            if ($data['approve'][1]) {
                //流程-> 借出科室审批通过 设备科未审批
                $depart_porrove_icon = '&#xe605;';
                $depart_porrove_color = 'executeddColor';
                $assets_porrove_color = 'haveColor';
            } else {
                //流程->申请
                $apply_icon = '&#xe605;';
                $depart_porrove_color = 'haveColor';
            }
        }

        //设备科审批通过 等待借入检查
        if ($data['status'] == C('BORROW_STATUS_BORROW_IN')) {
            $assets_porrove_icon = '&#xe605;';
            $depart_porrove_color = 'executeddColor';
            $assets_porrove_color = 'executeddColor';
            $borrow_in_color = 'haveColor';
        }


        //借入验收完成 待归还
        if ($data['status'] == C('BORROW_STATUS_GIVE_BACK')) {
            $borrow_in_icon = '&#xe605;';
            $depart_porrove_color = 'executeddColor';
            $assets_porrove_color = 'executeddColor';
            $borrow_in_color = 'executeddColor';
            $give_back_color = 'haveColor';
        }

        //完成借调
        if ($data['status'] == C('BORROW_STATUS_COMPLETE')) {
            $give_back_icon = '&#xe605;';
            $depart_porrove_color = 'executeddColor';
            $assets_porrove_color = 'executeddColor';
            $borrow_in_color = 'executeddColor';
            $give_back_color = 'endColor';
        }


        //设备不借入
        if ($data['status'] == C('BORROW_STATUS_NOT_APPLY')) {
            $borrow_in_icon = '&#xe605;';
            $depart_porrove_color = 'executeddColor';
            $assets_porrove_color = 'executeddColor';
            $borrow_in_color = 'endColor';
        }


        $html = '<ul class="timeLineList">';
        //借调申请
        $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis">' . $apply_icon . '</i><div class="timeLine timeLineStart"></div><span class="timeLineTitle timeLineStartTitle">借调申请</span><span class="timeLineDate timeLineStartTitle">' . $data['apply_time'] . '</span></div></li>';
        $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $depart_porrove_color . '">' . $depart_porrove_icon . '</i><div class="timeLine ' . $depart_porrove_color . 'Bg"></div><span class="timeLineTitle ' . $depart_porrove_color . '">科室审批</span><span class="timeLineDate ' . $depart_porrove_color . '">' . $data['approve'][1]['approve_time'] . '</span></div></li>';
        $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $assets_porrove_color . '">' . $assets_porrove_icon . '</i><div class="timeLine ' . $assets_porrove_color . 'Bg"></div><span class="timeLineTitle ' . $assets_porrove_color . '">设备科审核</span><span class="timeLineDate ' . $assets_porrove_color . '">' . $data['approve'][2]['approve_time'] . '</span></div></li>';

        if ($data['status'] == C('BORROW_STATUS_NOT_APPLY')) {
            $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $borrow_in_color . '">' . $borrow_in_icon . '</i><div class="timeLine ' . $borrow_in_color . 'Bg"></div><span class="timeLineTitle ' . $borrow_in_color . '">设备不借入</span><span class="timeLineDate ' . $borrow_in_color . '">' . $data['not_apply_time'] . '</span></div></li>';
        } else {
            $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $borrow_in_color . '">' . $borrow_in_icon . '</i><div class="timeLine ' . $borrow_in_color . 'Bg"></div><span class="timeLineTitle ' . $borrow_in_color . '">借入检查</span><span class="timeLineDate ' . $borrow_in_color . '">' . $data['borrow_in_time'] . '</span></div></li>';
        }
        $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $give_back_color . '">' . $give_back_icon . '</i><div class="timeLine ' . $give_back_color . 'Bg"></div><span class="timeLineTitle ' . $give_back_color . '">归还验收</span><span class="timeLineDate ' . $give_back_color . '">' . $data['give_back_time'] . '</span></div></li>';
        $html .= '</ul>';

        return $html;
    }

    /**
     * 记录设备借调附属设备信息
     * @param $borid int 借调id
     * */
    public function setBorrowDetail($borid)
    {
        $subsidiary_assid = trim(I('POST.subsidiary_assid'));
        if ($subsidiary_assid) {
            $addData = [];
            $assid_arr = explode(",", $subsidiary_assid);
            for ($i = 0; $i < count($assid_arr); $i++) {
                $addData[$i]['borid'] = $borid;
                $addData[$i]['subsidiary_assid'] = $assid_arr[$i];
            }
            $this->insertDataALL('assets_borrow_detail', $addData);
        }
    }

    /**
     * 获取设备基本信息
     * @param $assid int 设备id
     * @return array
     */
    public function getAssetsBasic($assid)
    {
        $where['assid'] = array('EQ', $assid);
        $files = 'assid,catid,assnum,assets,helpcatid,status,brand,model,unit,serialnum,assetsrespon,departid,address,buy_price,guarantee_date';
        $assets = $this->DB_get_one('assets_info', $files, $where);
        $files = 'afid,factory,factory_user,factory_tel,supplier,supp_user,supp_tel,repair,repa_user,repa_tel';
        $factory = $this->DB_get_one('assets_factory', $files, $where);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assets['department'] = $departname[$assets['departid']]['department'];
        switch ($assets['status']) {
            case C('ASSETS_STATUS_USE'):
                $assets['statusName'] = C('ASSETS_STATUS_USE_NAME');
                break;
            case C('ASSETS_STATUS_REPAIR'):
                $assets['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                break;
        }
        if (empty($factory)){
            return $assets;
        }else{
            return array_merge($assets, $factory);
        }
    }

    /**
     * 获取借调基本信息
     * @param $borid int 借调id
     * @return array
     * */
    public function getBorrowBasic($borid)
    {
        $where['borid'] = array('EQ', $borid);
        $files = 'assid,borid,borrow_num,apply_userid,apply_departid,borrow_reason,estimate_back,apply_time,status,examine_status,
        not_apply_time,end_reason,borrow_in_time,borrow_in_userid,give_back_time,give_back_userid,end_reason,score_value,
        score_remark,examine_status,status,examine_time,supplement';
        $borrow = $this->DB_get_one('assets_borrow', $files, $where);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $borrow['department'] = $departname[$borrow['apply_departid']]['department'];
        $borrow['estimate_back'] = getHandleMinute($borrow['estimate_back']);
        $borrow['apply_time'] = getHandleMinute($borrow['apply_time']);
        $borrow['not_apply_time'] = getHandleMinute($borrow['not_apply_time']);
        $borrow['borrow_in_time'] = getHandleMinute($borrow['borrow_in_time']);
        $borrow['give_back_time'] = getHandleMinute($borrow['give_back_time']);
        $borrow['examine_time'] = getHandleMinute($borrow['examine_time']);
        $userId[] = $borrow['apply_userid'];

        if ($borrow['borrow_in_userid']) {
            $userId[] = $borrow['borrow_in_userid'];
        }

        if ($borrow['give_back_userid']) {
            $userId[] = $borrow['give_back_userid'];
        }


        $user = $this->DB_get_All('user', 'userid,username', array('userid' => array('IN', $userId)));


        foreach ($user as &$userV) {
            if ($userV['userid'] == $borrow['apply_userid']) {
                $borrow['apply_username'] = $userV['username'];
            }
            if ($userV['userid'] == $borrow['borrow_in_userid']) {
                $borrow['borrow_in_username'] = $userV['username'];
            }

            if ($userV['userid'] == $borrow['give_back_userid']) {
                $borrow['give_back_username'] = $userV['username'];
            }
        }

        return $borrow;
    }

    /**
     * 获取借调审核基本信息
     * @param $borid int 借调id
     * @return array
     * */
    public function getBorrowApprovBasic($borid)
    {
        $where['borid'] = array('EQ', $borid);
        $approve = $this->DB_get_all('assets_borrow_approve', '', $where, '', 'level,approve_time asc');
        if ($approve) {
            $userId = [];
            foreach ($approve as &$approveV) {
                $userId[] = $approveV['approve_userid'];
                $approveV['approve_time'] = getHandleMinute($approveV['approve_time']);
                $approveV['approve_status'] = (int)$approveV['approve_status'];
                $approveV['is_adopt'] = $approveV['approve_status'];
            }

            $user = $this->DB_get_All('user', 'userid,username,pic', array('userid' => array('IN', $userId)));
            $userData = [];
            foreach ($user as &$userV) {
                $userData[$userV['userid']]['username'] = $userV['username'];
                $userData[$userV['userid']]['pic'] = $userV['pic'];
            }
            foreach ($approve as &$approveV) {
                $approveV['approver'] = $userData[$approveV['approve_userid']]['username'];
                $approveV['user_pic'] = $userData[$approveV['approve_userid']]['pic'];
            }
        }
        return $approve;
    }

    /**
     * 获取流水号
     * @param $assid int 设备id
     * @return string
     */
    public function getFlowNumber($assid)
    {
        $where['assid'] = array('EQ', $assid);
        $assets = $this->DB_get_one('assets_info', 'assnum', $where);
        $count = $this->DB_get_count('assets_borrow', $where);
        return 'B' . $assets['assnum'] . '-' . ($count + 1);
    }

    /**
     * 获取对应的附属设备
     * @param $assid int 主设备id
     * @return  array
     * */
    public function getAssetsSubsidiary($assid)
    {
        //筛选借调中
        $borrowWhere['status'] = array('IN', [C('BORROW_STATUS_APPROVE'), C('BORROW_STATUS_BORROW_IN'), C('BORROW_STATUS_GIVE_BACK')]);
        $borrow = $this->DB_get_one('assets_borrow', 'borid,assid', $borrowWhere);
        $not_assid = [];
        if ($borrow) {
            $borid = [];
            foreach ($borrow as &$bor) {
                $borid[] = $bor['borid'];
                $not_assid[] = $bor['assid'];
            }
            $borrow_detail = $this->DB_get_one('assets_borrow_detail', 'subsidiary_assid', ['assid' => ['IN', $borid]]);
            if ($borrow_detail) {
                foreach ($borrow_detail as &$bor_detail) {
                    $not_assid[] = $bor_detail['subsidiary_assid'];
                }
            }
        }
        if ($not_assid != []) {
            $where['assid'] = ['NOTIN', $not_assid];
        }
        $where['main_assid'] = ['EQ', $assid];
        $where['quality_in_plan'] = ['EQ', C('NO_STATUS')];
        $where['status'] = ['EQ', C('ASSETS_STATUS_USE')];
        $data = $this->DB_get_all('assets_info', 'assid,assets,assnum,model,unit,buy_price', $where);
        return $data;
    }

    /**
     * 获取附属设备信息
     * @param $borid int 借调记录id
     * @return array
     * */
    public function getSubsidiaryBasic($borid)
    {
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price';
        $data = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join, "borid=$borid");
        return $data;
    }

    /**
     * 保存审核结果
     * @param $level int 当前次序
     * @param $arr array 借调信息
     * @param $lastProcess int 总审核流程数
     * @param $assets array 设备信息
     * @return array
     */
    public function addApproveBorrow($level, $borrow, $lastProcess, $assets)
    {
        $data['borid'] = $borrow['borid'];
        $data['level'] = $level;
        $data['process_node'] = C('BORROW_APPROVE');
        $data['approve_userid'] = session('userid');
        $data['approve_time'] = time();
        $data['approve_status'] = I('POST.is_adopt');
        $data['remark'] = trim(I('POST.remark'));
        $data['borrow_num'] = $borrow['borrow_num'];
        //判断是否存在不同意的单，如果有，先把不同意的单改同意

        $approve_data = $this->DB_get_one('assets_borrow_approve', 'bor_app_id', array('approve_status' => 2, 'borid' => $data['borid']));
        if ($approve_data) {
            $newatid = $this->updateData('assets_borrow_approve', $data, array('bor_app_id' => $approve_data['bor_app_id']));
        } else {
            $newatid = $this->insertData('assets_borrow_approve', $data);
        }
        //判断当审批不同意时，借调是否重审状态回复1
        if ($data['approve_status'] == 2) {
            $this->updateData('assets_borrow', array('retrial_status' => 1), array('borid' => $borrow['borid']));
        }
        if ($newatid) {
            //添加日志
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $log['applyDepart'] = $departname[$borrow['apply_departid']]['department'];
            $log['backDepart'] = $departname[$assets['departid']]['department'];
            $log['borrow_num'] = $borrow['borrow_num'];
            $log['approve_status'] = $data['approve_status'] == C('STATUS_APPROE_SUCCESS') ? '同意' : '不同意';
            $text = getLogText('approveBorrowLogText', $log);
            $this->addLog('sb_assets_borrow', M()->getLastSql(), $text, $borrow['borid']);
            //判断是否是最后一道审批或者审批不通过
            $settingData = $this->checkSmsIsOpen($this->Controller);
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $data['apply_department'] = $departname[$borrow['apply_departid']]['department'];
            $data['department'] = $departname[$assets['departid']]['department'];
            $data['estimate_back'] = getHandleMinute($borrow['estimate_back']);
            $data['examine_status'] = $log['approve_status'];
            $data['assets'] = $assets['assets'];
            $applyData = $this->DB_get_one('user', 'username', ['userid' => $borrow['apply_userid']]);
            $ToolMod = new ToolController();
            if ($level == $lastProcess || $data['approve_status'] == C('STATUS_APPROE_FAIL')) {
                $where = [];
                $where['status'] = C('OPEN_STATUS');
                $where['is_delete'] = C('NO_STATUS');
                $where['userid'] = $borrow['apply_userid'];
                //获取申请人信息
                $apply_user = $this->DB_get_one('user', 'telephone,openid', $where);
                //更新转科表对应记录为最后审核状态
                if ($data['approve_status'] == C('STATUS_APPROE_FAIL')) {
                    //审批未通过
                    $borrowData['status'] = C('BORROW_STATUS_FAIL');
                    $borrowData['examine_status'] = C('STATUS_APPROE_FAIL');
                    //==========================================短信 START==========================================
                    if ($settingData) {
                        //有开启短信 通知借调申请人审批结果 未通过
                        if ($settingData['borrowrApproveOver']['status'] == C('OPEN_STATUS') && $apply_user) {
                            //通知审批结果 开启
                            $sms = $this->formatSmsContent($settingData['borrowrApproveOver']['content'], $data);
                            $ToolMod->sendingSMS($apply_user['telephone'], $sms, $this->Controller, $borrow['borid']);
                        }
                    }
                    //==========================================短信 END==========================================

                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**借调单号：**' . $borrow['borrow_num'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**' . $assets['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**' . $assets['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**借出科室：**' . $log['backDepart'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**借入科室：**' . $log['applyDepart'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**原因：**' . $data['remark'] ? $data['remark'] : '无';
                        $feishu_fields[] = $fd;

                        //按钮区域
                        $act['tag'] = 'button';
                        $act['type'] = 'primary';
                        $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Borrow/applyBorrow?borid=' . $borrow['borid'];
                        $act['text']['tag'] = 'plain_text';
                        $act['text']['content'] = '查看详情';
                        $feishu_actions[] = $act;

                        $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['elements'][1]['tag'] = 'hr';
                        $card_data['elements'][2]['actions'] = $feishu_actions;
                        $card_data['elements'][2]['layout'] = 'bisected';
                        $card_data['elements'][2]['tag'] = 'action';
                        $card_data['header']['template'] = 'red';
                        $card_data['header']['title']['content'] = '借调申请审批不通过提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        if ($apply_user['openid']) {
                            $this->send_feishu_card_msg($apply_user['openid'], $card_data);
                        }
                        //==========================================飞书 END==========================================
                    } else {
                        //==================================微信通知审批未通过 END====================================
                        //判断是否开启微信端
                        $moduleModel = new ModuleModel();
                        $wx_status = $moduleModel->decide_wx_login();
                        if ($wx_status && $apply_user['openid']) {
                            if (C('USE_VUE_WECHAT_VERSION')) {
                                $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Borrow/applyBorrow?borid=' . $borrow['borid'];
                            } else {
                                $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Borrow/applyBorrow.html?borid=' . $borrow['borid'];
                            }

                            Weixin::instance()->sendMessage($apply_user['openid'], '设备借调处理通知', [
                                'thing1'            => $log['applyDepart'],// 需求科室
                                'thing2'            => $assets['assets'],// 需求设备
                                'time5'             => getHandleMinute($borrow['estimate_back']),// 需求结束时间
                                'character_string9' => $borrow['borrow_num'],// 借调流水号
                                'const10'           => '审批未通过',// 借调状态
                            ], $redecturl);
                        }
                        //==================================微信通知审批未通过 END====================================
                    }
                } else {
                    //最后一次通过
                    $borrowData['status'] = C('BORROW_STATUS_BORROW_IN');
                    $borrowData['retrial_status'] = 1;
                    $borrowData['examine_status'] = C('STATUS_APPROE_SUCCESS');
                    $UserData = $ToolMod->getUser('borrowInCheck', $borrow['apply_departid']);
                    //=================================短信通知借调审批通过5?:t7j*%9SK! START==================================
                    if ($settingData) {
                        //有开启短信
                        if ($settingData['borrowrApproveOver']['status'] == C('OPEN_STATUS') && $UserData) {
                            //通知被借科室借调审批通过 开启
                            $phone = $this->formatPhone($UserData);
                            $sms = $this->formatSmsContent($settingData['borrowrApproveOver']['content'], $data);
                            $ToolMod->sendingSMS($phone, $sms, $this->Controller, $borrow['borid']);
                        }
                        if ($settingData['borrowrApproveOver']['status'] == C('OPEN_STATUS') && $apply_user) {
                            //通知申请人借调审批通过 开启
                            $sms = $this->formatSmsContent($settingData['borrowrApproveOver']['content'], $data);
                            $ToolMod->sendingSMS($apply_user['telephone'], $sms, $this->Controller, $borrow['borid']);
                        }
                    }
                    //==================================短信通知借调审批通过 END====================================

                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**借调单号：**' . $borrow['borrow_num'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**' . $assets['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**' . $assets['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**借出科室：**' . $log['backDepart'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**借入科室：**' . $log['applyDepart'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**预计归还时间：**' . $data['estimate_back'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '请及时进行借入验收，并按时归还';
                        $feishu_fields[] = $fd;

                        //按钮区域
                        $act['tag'] = 'button';
                        $act['type'] = 'primary';
                        $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Borrow/borrowInCheck?borid=' . $borrow['borid'];
                        $act['text']['tag'] = 'plain_text';
                        $act['text']['content'] = '查看详情并验收';
                        $feishu_actions[] = $act;

                        $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['elements'][1]['tag'] = 'hr';
                        $card_data['elements'][2]['actions'] = $feishu_actions;
                        $card_data['elements'][2]['layout'] = 'bisected';
                        $card_data['elements'][2]['tag'] = 'action';
                        $card_data['header']['template'] = 'blue';
                        $card_data['header']['title']['content'] = '设备借调申请审批完成提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        $alerady_send = [];
                        foreach ($UserData as $v) {
                            if ($v['openid'] && !in_array($v['openid'], $alerady_send)) {
                                $this->send_feishu_card_msg($v['openid'], $card_data);
                                $alerady_send[] = $v['openid'];
                            }
                        }
                        //==========================================飞书 END==========================================
                    } else {
                        $moduleModel = new ModuleModel();
                        $wx_status = $moduleModel->decide_wx_login();
                        if ($wx_status) {
                            //==================================微信通知借调审批通过 START==================================
                            if (C('USE_VUE_WECHAT_VERSION')) {
                                $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Borrow/borrowInCheck?borid=' . $borrow['borid'];
                            } else {
                                $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Borrow/borrowInCheck.html?borid=' . $borrow['borid'];
                            }

                            $openIds = array_column($UserData, 'openid');
                            $openIds = array_filter($openIds);
                            $openIds = array_unique($openIds);

                            $messageData = [
                                'thing1'            => $log['applyDepart'],// 需求科室
                                'thing2'            => $assets['assets'],// 需求设备
                                'time5'             => getHandleMinute($borrow['estimate_back']),// 需求结束时间
                                'character_string9' => $borrow['borrow_num'],// 借调流水号
                                'const10'           => '已审批通过，请验收',// 借调状态
                            ];

                            foreach ($openIds as $openId) {
                                Weixin::instance()->sendMessage($openId, '设备借调处理通知', $messageData, $redecturl);
                            }
                            //==================================微信通知借调审批通过 END====================================
                        }
                    }
                }
                $borrowData['examine_time'] = time();
                $this->updateData('assets_borrow', $borrowData, array('borid' => $borrow['borid']));
            } else {
                $approveUser = $ToolMod->getUser('assetsApproveBorrow', $assets['departid']);
                //==========================================短信 START==========================================
                if ($settingData) {
                    // 通知下个 设备科审批
                    if ($settingData['doApprove']['status'] == C('OPEN_STATUS') && $approveUser) {
                        //通知被借科室准备设备 开启
                        $phone = $this->formatPhone($approveUser);
                        $sms = $this->formatSmsContent($settingData['doApprove']['content'], $data);
                        $ToolMod->sendingSMS($phone, $sms, $this->Controller, $borrow['borid']);
                    }
                }
                //==========================================短信 END==========================================

                if (C('USE_FEISHU') === 1) {
                    //==========================================飞书 START========================================
                    //要显示的字段区域
                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**审批方：**设备科审批';
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备名称：**' . $assets['assets'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备编码：**' . $assets['assnum'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**借出科室：**' . $log['backDepart'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**借入科室：**' . $log['applyDepart'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**预计归还时间：**' . $data['estimate_back'];
                    $feishu_fields[] = $fd;

                    //按钮区域
                    $act['tag'] = 'button';
                    $act['type'] = 'primary';
                    // $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Borrow/assetsApproveBorrow?borid=' . $borrow['borid'];
                    $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Borrow/approveBorrow?borid=' . $borrow['borid'];
                    $act['text']['tag'] = 'plain_text';
                    $act['text']['content'] = '审批';
                    $feishu_actions[] = $act;


                    $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                    $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                    $card_data['elements'][0]['tag'] = 'div';
                    $card_data['elements'][0]['fields'] = $feishu_fields;
                    $card_data['elements'][1]['tag'] = 'hr';
                    $card_data['elements'][2]['actions'] = $feishu_actions;
                    $card_data['elements'][2]['layout'] = 'bisected';
                    $card_data['elements'][2]['tag'] = 'action';
                    $card_data['header']['template'] = 'blue';
                    $card_data['header']['title']['content'] = '设备借调审批申请';
                    $card_data['header']['title']['tag'] = 'plain_text';

                    $alerady_send = [];
                    foreach ($approveUser as $v) {
                        if ($v['openid'] && !in_array($v['openid'], $alerady_send)) {
                            $this->send_feishu_card_msg($v['openid'], $card_data);
                            $alerady_send[] = $v['openid'];
                        }
                    }
                    //==========================================飞书 END==========================================
                } else {
                    //==================================微信短信通知下一个人 END====================================
                    $moduleModel = new ModuleModel();
                    $wx_status = $moduleModel->decide_wx_login();
                    if ($wx_status) {
                        //开启微信
                        if (C('USE_VUE_WECHAT_VERSION')) {
                            // $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Borrow/assetsApproveBorrow?borid=' . $borrow['borid'];
                            $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Borrow/approveBorrow?borid=' . $borrow['borid'];
                        } else {
                            $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Borrow/assetsApproveBorrow.html?borid=' . $borrow['borid'];
                        }

                        $openIds = array_column($approveUser, 'openid');
                        $openIds = array_filter($openIds);
                        $openIds = array_unique($openIds);

                        $messageData = [
                            'thing1'            => $log['applyDepart'],// 需求科室
                            'thing2'            => $assets['assets'],// 需求设备
                            'time5'             => getHandleMinute($borrow['estimate_back']),// 需求结束时间
                            'character_string9' => $borrow['borrow_num'],// 借调流水号
                            'const10'           => '待审批',// 借调状态
                        ];

                        foreach ($openIds as $openId) {
                            Weixin::instance()->sendMessage($openId, '设备借调处理通知', $messageData, $redecturl);
                        }
                    }
                    //==================================微信短信通知下一个人 END====================================
                }
            }
            return array('status' => 1, 'msg' => '审批成功!');
        } else {
            return array('status' => -1, 'msg' => '审批失败,请稍后再试!');
        }
    }

    /**
     * 获取上传文件
     * @param $borid int 借调单id
     * @return  array
     * */
    public function getFileList($borid)
    {
        $where['borid'] = array('EQ', $borid);
        $where['is_delete'] = array('EQ', C('NO_STATUS'));
        $file = $this->DB_get_all('assets_borrow_file', 'file_id,file_name,save_name,file_type,file_url,add_user,add_time', $where);
        if ($file) {
            foreach ($file as &$fileValue) {
                $fileValue['operation'] = '<div class="layui-btn-group">';
                $supplement = 'data-path="' . $fileValue['file_url'] . '" data-name="' . $fileValue['file_name'] . '"';
                $fileValue['operation'] .= $this->returnListLink('下载', '', '', C('BTN_CURRENCY') . ' downFile', '', $supplement);
                if ($fileValue['file_type'] != 'doc' && $fileValue['file_type'] != 'docx') {
                    $fileValue['operation'] .= $this->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' layui-btn-normal showFile', '', $supplement);
                }
                $fileValue['operation'] .= '</div>';
            }
        }
        return $file;
    }

    /*
    * 上传文件
    * */
    public function uploadfile()
    {
        if ($_FILES['file']) {
            $Tool = new ToolController();
            $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx');
            $dirName = $this->Controller;
            $info = $Tool->upFile($style, $dirName);
            if ($info['status'] == C('YES_STATUS')) {
                // 上传成功 获取上传文件信息
                $resule['status'] = 1;
                $resule['msg'] = '上传成功';
                $resule['path'] = $info['src'];
                $resule['title'] = $info['title'];
                $resule['formerly'] = $info['formerly'];
                $resule['ext'] = $info['ext'];
                $resule['size'] = $info['size'];
            } else {
                // 上传错误提示错误信息
                $resule['status'] = -1;
                $resule['msg'] = $info['msg'];
            }
        } else {
            // 上传错误提示错误信息
            $resule['status'] = -1;
            $resule['msg'] = '未接收到文件';
        }
        return $resule;
    }

    /**
     * 保存报告
     * @param $file array 保存在服务器文件信息
     * @return array
     * */
    public function addReportFile($file)
    {
        $id = trim(I('POST.id'));
        $idName = trim(I('POST.idName'));
        $this->checkstatus(judgeNum($id), '非法操作');
        $this->checkstatus(judgeNum($id), '非法操作');
        if (!$id) {
            die(json_encode(array('status' => -1, 'msg' => '参数缺少,请按正常流程操作')));
        }
        $add['file_name'] = $file['formerly'];
        $add[$idName . 'id'] = $id;
        $add['save_name'] = $file['title'];
        $add['file_type'] = $file['ext'];
        $add['file_size'] = $file['size'];
        $add['file_url'] = $file['path'];
        $add['add_user'] = session('username');
        $add['add_time'] = getHandleDate(time());
        $file_id = $this->insertData('assets_borrow_file', $add);
        $file['file_id'] = $file_id;
        return $file;
    }

    //删除报告
    public function deleteFile()
    {
        $file_id = I('POST.file_id');
        if ($file_id) {
            $where['file_id'] = $file_id;
            $data = $this->DB_get_one('assets_borrow_file', 'is_delete', $where);
            if ($data) {
                if ($data['is_delete'] != C('YES_STATUS')) {
                    $data['is_delete'] = C('YES_STATUS');
                    $save = $this->updateData('assets_borrow_file', $data, $where);
                    if ($save) {
                        return array('status' => 1, 'msg' => '删除成功');
                    } else {
                        return array('status' => -1, 'msg' => '删除失败');
                    }
                } else {
                    die(json_encode(array('status' => -1, 'msg' => '文件已删除,请勿重复操作')));
                }
            } else {
                die(json_encode(array('status' => -1, 'msg' => '文件不存在,请刷新页面重新操作')));
            }
        } else {
            die(json_encode(array('status' => -1, 'msg' => '参数缺少,请按正常流程操作')));
        }
    }


    /**
     * 组合生命历程数据
     * @param $borrow array 借调记录
     * @return array
     */
    public function showBorrowTimeLineData($borrow)
    {
        $borrowTimeLine = array();
        $borrowTimeLine[0]['statusName'] = '借调申请';
        $borrowTimeLine[0]['number'] = '①';
        $borrowTimeLine[0]['date'] = $borrow['apply_time'];
        $borrowTimeLine[0]['user'] = $borrow['apply_username'];
        $borrowTimeLine[0]['text'] = ' 申请 "' . $borrow['lendDepartment'] . '" 下的 ' . $borrow['assets'] . ' 借调至 "' . $borrow['department'] . '"';

        $borrowTimeLine[1]['statusName'] = '借出科室审批';
        $borrowTimeLine[1]['number'] = '②';
        $borrowTimeLine[1]['date'] = $borrow['approve'][1]['approve_time'];
        $borrowTimeLine[1]['user'] = $borrow['approve'][1]['approver'];
        $borrowTimeLine[1]['text'] = ' 审批结果： ' . ($borrow['approve'][1]['approve_status'] == C('STATUS_APPROE_SUCCESS') ? '<span style="color: green;">通过</span>' : '<span style="color: red;">不通过</span>');

        $borrowTimeLine[2]['statusName'] = '设备科审批';
        $borrowTimeLine[2]['number'] = '③';
        $borrowTimeLine[2]['date'] = $borrow['approve'][2]['approve_time'];
        $borrowTimeLine[2]['user'] = $borrow['approve'][2]['approver'];
        $borrowTimeLine[2]['text'] = ' 审批结果： ' . ($borrow['approve'][2]['approve_status'] == C('STATUS_APPROE_SUCCESS') ? '<span style="color: green;">通过</span>' : '<span style="color: red;">不通过</span>');

        $borrowTimeLine[3]['statusName'] = '借入检查';
        $borrowTimeLine[3]['number'] = '④';
        $borrowTimeLine[3]['date'] = $borrow['borrow_in_time'];
        $borrowTimeLine[3]['user'] = $borrow['borrow_in_username'];
        $borrowTimeLine[3]['text'] = ' 确认设备 "' . $borrow['assets'] . '" 完好无损并借入使用';

        $borrowTimeLine[4]['statusName'] = '归还验收';
        $borrowTimeLine[4]['number'] = '⑤';
        $borrowTimeLine[4]['date'] = $borrow['give_back_time'];
        $borrowTimeLine[4]['user'] = $borrow['give_back_username'];
        $borrowTimeLine[4]['text'] = ' 确认设备 "' . $borrow['assets'] . '" 完好无损并结束流程';


        $borrowTimeLine[5]['statusName'] = '流程结束';
        $borrowTimeLine[5]['number'] = '⑥';

        //默认 灰色状态
        $borrowTimeLine[0]['class'] = 'nocompleteProgress';
        $borrowTimeLine[1]['class'] = 'nocompleteProgress';
        $borrowTimeLine[2]['class'] = 'nocompleteProgress';
        $borrowTimeLine[3]['class'] = 'nocompleteProgress';
        $borrowTimeLine[4]['class'] = 'nocompleteProgress';
        $borrowTimeLine[5]['class'] = 'nocompleteProgress';


        //设备科审批未通过
        if ($borrow['status'] == C('BORROW_STATUS_FAIL')) {
            if (!$borrow['approve'][2]) {
                //借出科室审批不通过
                $borrowTimeLine[0]['class'] = 'completeProgress';
                $borrowTimeLine[1]['class'] = 'completeProgress';
                $borrowTimeLine[5]['class'] = 'doingProgress';
            } else {
                //设备科审批不通过
                $borrowTimeLine[0]['class'] = 'completeProgress';
                $borrowTimeLine[1]['class'] = 'completeProgress';
                $borrowTimeLine[2]['class'] = 'completeProgress';
                $borrowTimeLine[5]['class'] = 'doingProgress';
            }
        }


        //申请状态 /包括借出科室审批(借调状态设备科审批通过后 或 者审批出现不通过 才会修改状态)
        if ($borrow['status'] == C('BORROW_STATUS_APPROVE')) {
            if ($borrow['approve'][1]) {
                //流程-> 借出科室审批通过 设备科未审批
                $borrowTimeLine[0]['class'] = 'completeProgress';
                $borrowTimeLine[1]['class'] = 'doingProgress';
            } else {
                //流程->申请
                $borrowTimeLine[0]['class'] = 'doingProgress';
            }
        }

        //设备科审批通过 等待借入检查
        if ($borrow['status'] == C('BORROW_STATUS_BORROW_IN')) {
            $borrowTimeLine[0]['class'] = 'completeProgress';
            $borrowTimeLine[1]['class'] = 'completeProgress';
            $borrowTimeLine[2]['class'] = 'doingProgress';
        }


        //借入验收完成 待归还
        if ($borrow['status'] == C('BORROW_STATUS_GIVE_BACK')) {
            $borrowTimeLine[0]['class'] = 'completeProgress';
            $borrowTimeLine[1]['class'] = 'completeProgress';
            $borrowTimeLine[2]['class'] = 'completeProgress';
            $borrowTimeLine[3]['class'] = 'doingProgress';
        }

        //完成借调
        if ($borrow['status'] == C('BORROW_STATUS_COMPLETE')) {
            $borrowTimeLine[0]['class'] = 'completeProgress';
            $borrowTimeLine[1]['class'] = 'completeProgress';
            $borrowTimeLine[2]['class'] = 'completeProgress';
            $borrowTimeLine[3]['class'] = 'completeProgress';
            $borrowTimeLine[4]['class'] = 'completeProgress';
            $borrowTimeLine[5]['class'] = 'doingProgress';
        }


        //设备不借入
        if ($borrow['status'] == C('BORROW_STATUS_NOT_APPLY')) {
            $borrowTimeLine[0]['class'] = 'completeProgress';
            $borrowTimeLine[1]['class'] = 'completeProgress';
            $borrowTimeLine[2]['class'] = 'completeProgress';
            $borrowTimeLine[3]['class'] = 'completeProgress';
            $borrowTimeLine[5]['class'] = 'doingProgress';

            $borrowTimeLine[3]['date'] = $borrow['not_apply_time'];
            $borrowTimeLine[3]['user'] = $borrow['borrow_in_username'];
            $borrowTimeLine[3]['text'] = ' 取消设备 "' . $borrow['assets'] . '" 的借调申请 不需要借调流程结束';

        }
        $RepairModel = new RepairModel();
        $borrowTimeLine = $RepairModel->getUserNameAndPhone($borrowTimeLine);
        return $borrowTimeLine;
    }

    //申请重审
    public function editBorrow($borid = null)
    {
        $borrowInfo = $this->DB_get_one('assets_borrow', 'borid,borrow_num,assid,retrial_status', array('borid' => $borid));
        if (!$borrowInfo) {
            return array('status' => -1, 'msg' => '查找不到该借调记录！');
        }
        if ($borrowInfo['retrial_status'] == 2) {
            return array('status' => -1, 'msg' => '已提交重审申请，请勿重复提交');
        }
        if ($borrowInfo['retrial_status'] == 3) {
            return array('status' => -1, 'msg' => '借调申请已结束！');
        }
        $assid = $borrowInfo['assid'];
        $borrow_reason = I('POST.borrow_reason');
        $estimate_back = I('POST.estimate_back');
        if (!$assid) {
            die(json_encode(array('status' => -1, 'msg' => '非法操作')));
        }
        if (time() >= strtotime($estimate_back)) {
            die(json_encode(array('status' => -1, 'msg' => '请选择正确的归还时间')));
        }
        $assetsWher['assid'] = array('EQ', $assid);
        if (session('isSuper') == C('YES_STATUS')) {
            //如果是超级管理员 获取当前选中的医院
            $assetsWher['hospital_id'] = array('EQ', session('current_hospitalid'));
            $apply_departid = trim(I('POST.apply_departid'));
            $this->checkstatus(judgeNum($apply_departid), '请选中申请科室');
            $data['apply_departid'] = $apply_departid;
        } else {
            $assetsWher['hospital_id'] = array('EQ', session('job_hospitalid'));
            $data['apply_departid'] = session('job_departid');
        }
        $assets = $this->DB_get_one('assets_info', 'assets,assnum,assid,status,departid,quality_in_plan', $assetsWher);
        if (!$assets) {
            die(json_encode(array('status' => -1, 'msg' => '查找不到该设备信息！')));
        } else {
            if ($assets['status'] == C('ASSETS_STATUS_REPAIR')) {
                die(json_encode(array('status' => -1, 'msg' => '设备正在维修中，请等待结束后再申请借调！')));
            }
            if ($assets['status'] == C('ASSETS_STATUS_OUTSIDE_ON')) {
                die(json_encode(array('status' => -1, 'msg' => '设备正在外调申请！')));
            }
            if ($assets['status'] == C('ASSETS_STATUS_OUTSIDE')) {
                die(json_encode(array('status' => -1, 'msg' => '设备已外调！')));
            }
            if ($assets['status'] == C('ASSETS_STATUS_SCRAP_ON')) {
                die(json_encode(array('status' => -1, 'msg' => '设备正在报废申请！')));
            }
            if ($assets['status'] == C('ASSETS_STATUS_SCRAP')) {
                die(json_encode(array('status' => -1, 'msg' => '设备已报废！')));
            }
            if ($assets['status'] == C('ASSETS_STATUS_TRANSFER_ON')) {
                die(json_encode(array('status' => -1, 'msg' => '设备正在转科中，请等待转科结束后再申请借调！')));
            }
            if ($assets['quality_in_plan'] == C('YES_STATUS')) {
                die(json_encode(array('status' => -1, 'msg' => '设备正在质控执行中，请等待结束后再申请借调！')));
            }

        }
        $time = date('H:i', strtotime($estimate_back));
        $timeArr = explode(':', $time);
        $baseSetting = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $start = $baseSetting['assets']['apply_borrow_back_time']['value'][0];
        $end = $baseSetting['assets']['apply_borrow_back_time']['value'][1];
        $startArr = explode(':', $start);
        $endArr = explode(':', $end);
        if ($start && $end) {
            if ($startArr[0] <= $timeArr[0] && $timeArr[0] <= $endArr[0]) {
                //最后一个小时 分钟不能大于设置的分钟否则不合理
                if ($timeArr[0] == $endArr[0] && ($endArr[1] < $timeArr[1])) {
                    die(json_encode(array('status' => -1, 'msg' => '归还时间范围 ' . $start . ' 至 ' . $end)));
                }
                //第一个小时 分钟不能小于设置的分钟否则不合理
                if ($timeArr[0] == $startArr[0] && ($startArr[1] > $timeArr[1])) {
                    die(json_encode(array('status' => -1, 'msg' => '归还时间范围 ' . $start . ' 至 ' . $end)));
                }
            } else {
                die(json_encode(array('status' => -1, 'msg' => '归还时间范围 ' . $start . ' 至 ' . $end)));
            }
        }

        //归还时间不能在计量检查的时间内
        $meteringWhere['status'] = array('EQ', C('YES_STATUS'));
        $meteringWhere['assid'] = array('EQ', $assid);
        $metering = $this->DB_get_one('metering_plan', 'assid,next_date,remind_day', $meteringWhere);
        if ($metering) {
            //如果当前日期在提醒时间之内则不能申请
            $remind_date = strtotime($metering['next_date']) - ($metering['remind_day'] * (60 * 60 * 24));
            if (strtotime($estimate_back) > $remind_date) {
                die(json_encode(array('status' => -1, 'msg' => '需在计量执行(' . getHandleTime($remind_date) . ')前归还')));
            }
        }

        //归还时间不能在质控计划开始到结束的这个过程中
        $qualityWhere['is_start'] = array('EQ', C('YES_STATUS'));
        $qualityWhere['assid'] = array('EQ', $assid);
        $quality = $this->DB_get_one('quality_starts', 'do_date', $qualityWhere);
        if ($quality) {
            if (strtotime($estimate_back) > strtotime($quality['do_date'])) {
                die(json_encode(array('status' => -1, 'msg' => '需在质控执行(' . $quality['do_date'] . ')前归还')));
            }
        }

        $data['status'] = C('NO_STATUS');//默认为未审核
        //查看该科室是否有分配审核权限的用户
        $userModel = new UserModel();
        //部门审批
        $departUser = $userModel->getUsers('departApproveBorrow', $assets["departid"], true);
        if (!$departUser) {
            die(json_encode(array('status' => -1, 'msg' => '拥有【借出科室审批权限】的角色没有成员用户或该用户没有当前借出科室的管理权限！请联系系统管理人员设置！')));
        }
        //设备科审批
        $assetsUser = $userModel->getUsers('assetsApproveBorrow', $assets["departid"], true);
        if (!$assetsUser) {
            die(json_encode(array('status' => -1, 'msg' => '拥有【设备科审批权限】的角色没有成员用户或该用户没有当前科室的管理权限！请联系系统管理人员设置！')));
        }
        $departUserArr = [];
        foreach ($departUser as &$departUserValue) {
            $departUserArr[$departUserValue['username']]['username'] = $departUserValue['username'];
            $departUserArr[$departUserValue['username']]['telephone'] = $departUserValue['telephone'];
            $departUserArr[$departUserValue['username']]['openid'] = $departUserValue['openid'];
        }

        //判断departUser是否有当前设备的科室审批负责人
        $tranoutManager = $this->DB_get_one('department', 'manager', array('departid' => $assets["departid"]));
        if (!$tranoutManager['manager']) {
            die(json_encode(array('status' => -1, 'msg' => '借出科室 "' . $departname[$assets['departid']]['department'] . '" 未设置审批负责人,请联系管理员设置！')));
        }

        if (!$departUserArr[$tranoutManager['manager']]) {
            die(json_encode(array('status' => -1, 'msg' => '借出科室 "' . $departname[$assets['departid']]['department'] . '" 负责人 ' . $tranoutManager['manager'] . ' 无借调"借出科室审批权限 请联系管理员设置"')));
        }
        $data['assid'] = $assid;
        $data['apply_userid'] = session('userid');
        $data['borrow_reason'] = $borrow_reason;
        $data['estimate_back'] = strtotime($estimate_back);
        $data['apply_time'] = time();
        $data['retrial_status'] = 2;//重审中
        $data['examine_status'] = 0;

        $add = $this->updateData('assets_borrow', $data, array('borid' => $borid));
        if ($add) {
            //删除已有审批记录
            $this->deleteData('assets_borrow_approve', array('borid' => $borid));
            $this->setBorrowDetail($add);
            $log['applyDepart'] = $departname[$data['apply_departid']]['department'];
            $log['backDepart'] = $departname[$assets['departid']]['department'];
            $log['assets'] = $assets['assets'];
            $text = getLogText('addBorrowLogText', $log);
            $this->addLog('user', M()->getLastSql(), $text, $add);
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($settingData) {
                //有开启短信
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $data['apply_department'] = $departname[$data['apply_departid']]['department'];
                $data['department'] = $departname[$assets['departid']]['department'];
                $data['assets'] = $assets['assets'];
                $data['estimate_back'] = $estimate_back;
                $ToolMod = new ToolController();
                if ($settingData['doApprove']['status'] == C('OPEN_STATUS') && $departUserArr[$tranoutManager['manager']]['telephone']) {
                    //通知报修用户验收 开启
                    $sms = $this->formatSmsContent($settingData['doApprove']['content'], $data);
                    $ToolMod->sendingSMS($departUserArr[$tranoutManager['manager']]['telephone'], $sms, $this->Controller, $add);
                }
            }
            //==========================================短信 END==========================================

            if (C('USE_FEISHU') === 1) {
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**审批方：**借出科室审批';
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**设备名称：**' . $assets['assets'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**设备编码：**' . $assets['assnum'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**借出科室：**' . $log['backDepart'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**借入科室：**' . $log['applyDepart'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**预计归还时间：**' . date('Y-m-d H:i', strtotime($estimate_back));
                $feishu_fields[] = $fd;

                //按钮区域
                $act['tag'] = 'button';
                $act['type'] = 'primary';
                // $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Borrow/departApproveBorrow?borid=' . $borid;
                $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Borrow/approveBorrow?borid=' . $borid;
                $act['text']['tag'] = 'plain_text';
                $act['text']['content'] = '审批';
                $feishu_actions[] = $act;


                $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag'] = 'div';
                $card_data['elements'][0]['fields'] = $feishu_fields;
                $card_data['elements'][1]['tag'] = 'hr';
                $card_data['elements'][2]['actions'] = $feishu_actions;
                $card_data['elements'][2]['layout'] = 'bisected';
                $card_data['elements'][2]['tag'] = 'action';
                $card_data['header']['template'] = 'blue';
                $card_data['header']['title']['content'] = '设备借调审批申请';
                $card_data['header']['title']['tag'] = 'plain_text';

                if ($departUserArr[$tranoutManager['manager']]['openid']) {
                    $this->send_feishu_card_msg($departUserArr[$tranoutManager['manager']]['openid'], $card_data);
                }
                //==========================================飞书 END==========================================
            } else {
                //==================================微信通知借调审批 END====================================
                //判断是否开启微信端
                $moduleModel = new ModuleModel();
                $wx_status = $moduleModel->decide_wx_login();
                if ($wx_status && $departUserArr[$tranoutManager['manager']]['openid']) {
                    if (C('USE_VUE_WECHAT_VERSION')) {
                        // $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Borrow/departApproveBorrow?borid=' . $borid;
                        $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Borrow/approveBorrow?borid=' . $borid;
                    } else {
                        $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Borrow/departApproveBorrow.html?borid=' . $borid;
                    }

                    Weixin::instance()->sendMessage($departUserArr[$tranoutManager['manager']]['openid'], '设备借调处理通知', [
                        'thing1'            => $log['applyDepart'],// 需求科室
                        'thing2'            => $assets['assets'],// 需求设备
                        'time5'             => getHandleMinute(strtotime($estimate_back)),// 需求结束时间
                        'character_string9' => $borrowInfo['borrow_num'],// 借调流水号
                        'const10'           => '待审批',// 借调状态
                    ], $redecturl);
                }
                //==================================微信通知借调审批 END====================================
            }
            return array('status' => 1, 'msg' => '提交成功,等待借出科室审批');
        } else {
            return array('status' => -1, 'msg' => '提交失败');
        }
    }


    //结束借调流程
    public function endBorrow($borid = null)
    {
        $borid = I('post.borid');
        $borrowInfo = $this->DB_get_one('assets_borrow', 'borid,assid', array('borid' => $borid));
        if (!$borrowInfo) {
            return array('status' => -1, 'msg' => '查找不到该借调记录！');
        }
        $this->updateData('assets_borrow', array('retrial_status' => 3), array('borid' => $borid));
        return array('status' => 1, 'msg' => '操作成功！');
    }

    //格式化短信内容
    public function formatSmsContent($content, $data)
    {
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{borrow_num}", $data['borrow_num'], $content);
        $content = str_replace("{apply_department}", $data['apply_department'], $content);
        $content = str_replace("{department}", $data['department'], $content);
        $content = str_replace("{examine_status}", $data['examine_status'], $content);
        $content = str_replace("{estimate_back}", $data['estimate_back'], $content);
        $content = str_replace("{giveBack_time}", $data['give_back_time'], $content);
        return $content;
    }
}
