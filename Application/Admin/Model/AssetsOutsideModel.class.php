<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;

class AssetsOutsideModel extends CommonModel
{
    private $MODULE = 'Assets';
    private $Controller = 'Outside';
    protected $tablePrefix = 'sb_';
    protected $tableName = 'assets_outside';

    //外调申请列表数据
    public function getDepartAssetsList()
    {
        $model = I('POST.assetsModel');
        $assetsName = I('POST.assetsName');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order');
        $sort = I('POST.sort');
        $assnum = I('POST.assnum');
        $category = I('POST.category');
        $department = I('POST.department');
        $status = I('POST.status');
        $where['status'][0] = 'NOTIN';
        $where['status'][1][] = C('ASSETS_STATUS_SCRAP_ON');//报废中
        $where['status'][1][] = C('ASSETS_STATUS_SCRAP');//报废
        $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调

        $where['is_subsidiary'] = C('NO_STATUS');

        if (!$sort) {
            $sort = 'assid';
        }
        if (!$order) {
            $order = 'desc';
        }
        if ($assnum) {
            $where['assnum'] = array('LIKE', '%' . $assnum . '%');
        }
        if ($category) {
            //分类搜索
            $catwhere['category'] = array('like', '%' . $category . '%');
            $res = $this->DB_get_all('category', 'catid', $catwhere, '', 'catid asc', '');
            if ($res) {
                $catids = '';
                foreach ($res as $k => $v) {
                    $catids .= $v['catid'] . ',';
                }
                $catids = trim($catids, ',');
                $where['catid'] = array('in', $catids);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        }
        if ($department) {
            //科室搜索
            $department_where['department'] = array('like', '%' . $department . '%');
            $department_where['is_delete'] = 0;
            $res = $this->DB_get_all('department', 'departid', $department_where, '', 'departid asc', '');
            if ($res) {
                $departid = '';
                foreach ($res as $k => $v) {
                    $departid .= $v['departid'] . ',';
                }
                $departid = trim($departid, ',');
                $where['departid'] = array('in', $departid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        }
        if ($assnum) {
            $where['assnum'] = array('LIKE', '%' . $assnum . '%');
        }
        if ($status != "") {
            $where['status'] = $status;
        }

        if (session('isSuper') != C('YES_STATUS')) {
            //筛选科室 获取用户本身工作的科室
            if (!session('job_departid')) {
                $result['msg'] = '该用户未配置工作科室';
                $result['code'] = 400;
                return $result;
            }
            if($where['departid'][1] != null && $where['departid'][1] != session('job_departid')){
                $result['msg'] = '只能获取您工作科室所在的数据';
                $result['code'] = 400;
                return $result;
            }else{
                $where['departid'] = array('IN', session('job_departid'));
            }
        }
        if ($assetsName) {
            $where['assets'] = array('LIKE', '%' . $assetsName . '%');
        }
        $fileds = 'assid,catid,departid,hospital_id,assets,assnum,assets,brand,model,status,buy_price,opendate,guarantee_date,insuredsum,quality_in_plan,patrol_in_plan';
        $where['is_delete'] = C('NO_STATUS');
        $total = $this->DB_get_count('assets_info', $where);
        $data = $this->DB_get_all('assets_info', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $assid = [];
        $assnum = [];
        $assnumAssid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
            $assnum[] = $dataV['assnum'];
            $assnumAssid[$dataV['assnum']] = $dataV['assid'];

        }
        //获取参保信息
        $insuranceWhere['assid'] = array('IN', $assid);
        $insuranceWhere['status'] = array('EQ', C('INSURANCE_STATUS_USE'));
        $insurance = $this->DB_get_all('assets_insurance', 'status,assid', $insuranceWhere);
        $insuranceData = [];
        foreach ($insurance as &$insuranceV) {
            $insuranceData[$insuranceV['assid']] = $insuranceV['status'];
        }
        //查询借调表中等待重审或重审中的数据
        $outside = $this->DB_get_all('assets_outside', 'outid,assid,retrial_status,status', array('retrial_status' => array('in', '1,2')));
        $outsideids = $retrial_status = $outside_outid = [];
        foreach ($outside as $k => $v) {
            $outsideids[] = $v['assid'];
            $retrial_status[$v['assid']] = $v['retrial_status'];
            $outside_outid[$v['assid']] = $v['outid'];
            $outside_status[$v['assid']] = $v['status'];
        }

        //筛选计量计划中的设备
        $meteringWhere['status'] = array('EQ', C('YES_STATUS'));
        $meteringWhere['assid'] = array('IN', $assid);
        $metering = $this->DB_get_all('metering_plan', 'assid', $meteringWhere);
        $meteringAssid = [];
        if ($metering) {
            foreach ($metering as &$meteringV) {
                $meteringAssid[$meteringV['assid']] = true;
            }
        }
        //筛选外调中的设备
        $borrowWhere['status'] = array('IN', [C('BORROW_STATUS_APPROVE'), C('BORROW_STATUS_BORROW_IN'), C('BORROW_STATUS_GIVE_BACK')]);
        $borrowWhere['assid'] = array('IN', $assid);
        $borrow = $this->DB_get_all('assets_borrow', 'assid', $borrowWhere);
        $borrowAssid = [];
        if ($borrow) {
            foreach ($borrow as &$borrowV) {
                $borrowAssid[$borrowV['assid']] = true;
            }
        }
        $catname = array();
        $departname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $applyAssetOutSideMenu = get_menu($this->MODULE, $this->Controller, 'applyAssetOutSide');
        
        $disabled = $this->returnListLink('外调申请', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
        $repairing = $this->returnListLink(C('ASSETS_STATUS_REPAIR_NAME'), '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
        $outsiding = $this->returnListLink(C('ASSETS_STATUS_OUTSIDE_ON_NAME'), '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
        $scraping = $this->returnListLink(C('ASSETS_STATUS_SCRAP_ON_NAME'), '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
        $transfering = $this->returnListLink(C('ASSETS_STATUS_TRANSFER_ON_NAME'), '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
        $quailtying = $this->returnListLink('质控计划中', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
        $patroling = $this->returnListLink('巡查计划中', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
        foreach ($data as &$dataV) {
            if (!$showPrice) {
                $dataV['buy_price'] = '***';
            }
            $dataV['opendate'] = HandleEmptyNull($dataV['opendate']);
            $dataV['statusName'] = C('ASSETS_STATUS_USE_NAME');
            $dataV['department'] = $departname[$dataV['departid']]['department'];
            $dataV['category'] = $catname[$dataV['catid']]['category'];
            if (getHandleTime(time()) < HandleEmptyNull($dataV['guarantee_date'])) {
                $dataV['guarantee_status'] = '保修期内';
            } else {
                if ($insuranceData[$dataV['assid']] == C('INSURANCE_STATUS_USE')) {
                    $dataV['guarantee_status'] = '参保期内';
                } else {
                    $dataV['guarantee_status'] = '脱保';
                }
            }
            if ($dataV['status'] == C('ASSETS_STATUS_USE')) {
                //在用
                $dataV['statusName'] = C('ASSETS_STATUS_USE_NAME');
            }
            if ($dataV['status'] == C('ASSETS_STATUS_REPAIR')) {
                //维修中
                $dataV['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                $dataV['operation'] = $repairing;
                //当查找可用设备时，屏蔽该设备
                if ($status === 0) {
                    $dataV = array();
                }
                continue;
            }
            if ($dataV['status'] == C('ASSETS_STATUS_OUTSIDE_ON')) {
                //外调中
                $dataV['statusName'] = C('ASSETS_STATUS_OUTSIDE_ON_NAME');
                if (in_array($dataV['assid'], $outsideids)) {
                    //审批不通过，判断重审状态
                    if ($retrial_status[$dataV['assid']] == 1 && $outside_status[$dataV['assid']] == -1) {
                        $dataV['operation'] .= '<div class="layui-btn-group">';
                        $dataV['operation'] .= $this->returnListLink('申请重审', $applyAssetOutSideMenu['actionurl'] . '?outid=' . $outside_outid[$dataV['assid']], 'edit', C('BTN_CURRENCY') . ' layui-btn-normal');
                        $dataV['operation'] .= $this->returnListLink('结束进程', $applyAssetOutSideMenu['actionurl'], 'over', C('BTN_CURRENCY') . ' layui-btn-danger', '', 'data-id=' . $outside_outid[$dataV['assid']]);
                        $dataV['operation'] .= '</div>';
                    } elseif ($retrial_status[$dataV['assid']] == 2) {
                        $dataV['operation'] = $this->returnListLink('重审中', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                    } else {
                        $dataV['operation'] = $outsiding;
                    }
                }
                continue;
            }
            if ($dataV['status'] == C('ASSETS_STATUS_SCRAP_ON')) {
                //报废中
                $dataV['statusName'] = C('ASSETS_STATUS_SCRAP_ON_NAME');
                $dataV['operation'] = $scraping;
                continue;
            }
            if ($dataV['status'] == C('ASSETS_STATUS_TRANSFER_ON')) {
                //转科中
                $dataV['statusName'] = C('ASSETS_STATUS_TRANSFER_ON_NAME');
                $dataV['operation'] = $transfering;
                continue;
            }
            if ($dataV['quality_in_plan'] == C('YES_STATUS')) {
                //质控中
                $dataV['statusName'] = '质控中';
                $dataV['operation'] = $quailtying;
                continue;
            }
            if ($dataV['patrol_in_plan'] == C('YES_STATUS')) {
                //巡查计划中
                $dataV['statusName'] = '巡查中';
                $dataV['operation'] = $patroling;
                continue;
            }

            if ($applyAssetOutSideMenu && (!$meteringAssid[$dataV['assid']] && !$borrowAssid[$dataV['assid']])) {

                $dataV['operation'] = $this->returnListLink($applyAssetOutSideMenu['actionname'], $applyAssetOutSideMenu['actionurl'], 'applyAssetOutSide', C('BTN_CURRENCY'));
            } else {
                if ($meteringAssid[$dataV['assid']]) {
                    $dataV['statusName'] = '计量中';
                    $dataV['operation'] = $disabled;
                    continue;
                }
                if ($borrowAssid[$dataV['assid']]) {
                    $dataV['statusName'] = '借调中';
                    $dataV['operation'] = $disabled;
                    continue;
                }
            }
        }
        $result['data'] = $outside_status;
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //外调申请操作
    public function applyAssetOutSide()
    {
        $assid = I('POST.assid');
        $apply_type = I('POST.apply_type');
        $price = I('POST.price');
        $reason = I('POST.reason');
        $accept = I('POST.accept');
        $person = I('POST.person');
        $phone = I('POST.phone');
        $outside_date = I('POST.outside_date');
        $subsidiary_assid = trim(I('POST.subsidiary_assid'));
        if (!$apply_type) {
            die(json_encode(array('status' => -1, 'msg' => '请选择申请类型')));

        } elseif ($apply_type == 3) {
            if ($price <= 0) {
                die(json_encode(array('status' => -1, 'msg' => '请补充外售金额')));
            } else {
                $data['price'] = $price;
            }
        }
        if (!$reason) {
            die(json_encode(array('status' => -1, 'msg' => '请补充外调原因')));
        }

        if (!$accept) {
            die(json_encode(array('status' => -1, 'msg' => '请补充外调目的地')));
        }
        if ($phone) {
            if (!judgeMobile($phone) && !judgePhone($phone)) {
                die(json_encode(array('status' => -1, 'msg' => '请输入正常的号码')));
            }
        }
        //筛选计量计划中
        $meteringWhere['status'] = array('EQ', C('YES_STATUS'));
        $meteringWhere['assid'] = array('EQ', $assid);
        $metering = $this->DB_get_one('metering_plan', 'assid', $meteringWhere);
        if ($metering) {
            die(json_encode(array('status' => -1, 'msg' => '设备有启用中的计量计划,请停用或删除后再进行外调')));
        }
        $where['assid'] = array('EQ', $assid);
        $files = 'assid,hospital_id,departid,assets,assnum,status,quality_in_plan,patrol_in_plan,buy_price';
        $assets = $this->DB_get_one('assets_info', $files, $where);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assets['buy_price'] = '***';
        }
        if ($assets['status'] == C('ASSETS_STATUS_REPAIR')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在维修中，请等待结束后再申请外调')));
        }
        if ($assets['status'] == C('ASSETS_STATUS_OUTSIDE')) {
            die(json_encode(array('status' => -1, 'msg' => '设备已外调')));
        }
        if ($assets['status'] == C('ASSETS_STATUS_OUTSIDE_ON')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在外调申请，请勿重复申请')));
        }
        if ($assets['status'] == C('ASSETS_STATUS_SCRAP')) {
            die(json_encode(array('status' => -1, 'msg' => '设备已报废')));
        }
        if ($assets['status'] == C('ASSETS_STATUS_SCRAP_ON')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在报废申请，请等待结束后再申请外调')));
        }
        if ($assets['status'] == C('ASSETS_STATUS_TRANSFER_ON')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在转科申请，请等待结束后再申请外调')));
        }
        if ($assets['quality_in_plan'] == C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在质控执行中，请等待结束后再申请外调')));
        }
        if ($assets['patrol_in_plan'] == C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在巡查中，请等待结束后再申请外调')));
        }
        //筛选借调中
        $borrowWhere['status'] = array('IN', [C('BORROW_STATUS_APPROVE'), C('BORROW_STATUS_BORROW_IN'), C('BORROW_STATUS_GIVE_BACK')]);
        $borrowWhere['assid'] = array('EQ', $assid);
        $borrow = $this->DB_get_one('assets_borrow', 'assid', $borrowWhere);
        if ($borrow) {
            die(json_encode(array('status' => -1, 'msg' => '设备借调中，请等待结束后再申请外调')));
        }
        //查询是否已开启外调审批
        $isOpenApprove = $this->checkApproveIsOpen(C('OUTSIDE_APPROVE'), $assets['hospital_id']);

        $data['order_price'] = $assets['buy_price'];
        $all_subsidiaryWhere['main_assid'] = ['EQ', $assid];
        $all_subsidiaryWhere['status'][0] = 'NOTIN';
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_SCRAP');//报废
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        $subsidiary_assets = $this->DB_get_all('assets_info', 'buy_price,assid', $all_subsidiaryWhere);
        $all_subsidiary_assid = [];
        $assid_arr = explode(",", $subsidiary_assid);
        if ($subsidiary_assid) {
            //有选择辅助设备 金额汇总
            foreach ($subsidiary_assets as &$sub_asstes) {
                $all_subsidiary_assid[] = $sub_asstes['assid'];
                if (in_array($sub_asstes['assid'], $assid_arr)) {
                    $data['order_price'] += $sub_asstes['buy_price'];
                }
            }
        }
        $approve = [];
        if ($isOpenApprove) {
            //查询是否已设置审批流程
            $isSetProcess = $this->checkApproveIsSetProcess(C('OUTSIDE_APPROVE'), $assets['hospital_id']);
            if (!$isSetProcess) {
                die(json_encode(array('status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！')));
            }
            $data['status'] = C('OUTSIDE_STATUS_APPROVE');//默认为未审核
            //获取审批流程
            $approve_process_user = $this->get_approve_process($data['order_price'], C('OUTSIDE_APPROVE'), $assets['hospital_id']);
            //并且获取下次审批人
            $approve = $this->check_approve_process($assets['departid'], $approve_process_user, 1);
            if ($approve['all_approver'] == '') {
                //不在审核范围内 不需要审批
                $data['approve_status'] = C('STATUS_APPROE_UNWANTED');
                $data['status'] = C('OUTSIDE_STATUS_ACCEPTANCE_CHECK');
            } else {
                //默认为未审核
                $data['current_approver'] = $approve['current_approver'];
                $data['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                $data['all_approver'] = $approve['all_approver'];
                $data['approve_status'] = C('APPROVE_STATUS');
            }
        } else {
            //跳过审核直接到验收
            $data['status'] = C('OUTSIDE_STATUS_ACCEPTANCE_CHECK');
            $data['approve_status'] = C('STATUS_APPROE_UNWANTED');//不需审核
        }
//        exit();
        $data['assid'] = $assid;
        $data['apply_type'] = $apply_type;
        $data['reason'] = $reason;
        $data['accept'] = $accept;
        $data['person'] = $person;
        $data['phone'] = $phone;
        $data['outside_date'] = strtotime($outside_date);
        $data['apply_userid'] = session('userid');
        $data['apply_time'] = time();
        $add = $this->insertData('assets_outside', $data);
        $sql = M()->getLastSql();
        if ($add) {
            $this->addOutsideFile($add, C('OUTSIDE_FILE_TYPE_APPLY'));
            //记录辅助设备
            $update_assid_arr = [];
            if ($subsidiary_assid) {
                $addData = [];
                $assid_arr = explode(",", $subsidiary_assid);
                for ($i = 0; $i < count($assid_arr); $i++) {
                    $update_assid_arr[] = $assid_arr[$i];
                    $addData[$i]['outid'] = $add;
                    $addData[$i]['subsidiary_assid'] = $assid_arr[$i];
                }
                $this->insertDataALL('assets_outside_detail', $addData);
            }
            if ($data['status'] == C('APPROVE_STATUS')) {
                //设备状态变更为外调中
                $update_assid_arr[] = $assid;
                $assetaData['status'] = C('ASSETS_STATUS_OUTSIDE_ON');
                $this->updateData('assets_info', $assetaData, ['assid' => ['IN', $update_assid_arr]]);
                $this->updateAllAssetsStatus($update_assid_arr, $assetaData['status'], '设备外调申请');
            } else {
                //设备状态直接变更已外调
                $diff_assid = array_diff($all_subsidiary_assid, $update_assid_arr);
                if ($diff_assid) {
                    //将未选中的附属设备变成无主
                    $diffData['main_assid'] = 0;
                    $diffData['main_assets'] = '';
                    $diffWhere['assid'] = ['IN', $diff_assid];
                    $this->updateData('assets_info', $diffData, $diffWhere);
                }
                $update_assid_arr[] = $assid;
                $assetaData['status'] = C('ASSETS_STATUS_OUTSIDE');
                $this->updateData('assets_info', $assetaData, ['assid' => ['IN', $update_assid_arr]]);
                $this->updateAllAssetsStatus($update_assid_arr, $assetaData['status'], '设备外调');
            }
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $data['department'] = $departname[$assets['departid']]['department'];
            //通知审批人审批
            $where = [];
            $where['status'] = C('OPEN_STATUS');
            $where['is_delete'] = C('NO_STATUS');
            $where['username'] = $approve['this_current_approver'];
            $approve_user = $this->DB_get_one('user', 'openid,telephone', $where);
            if ($settingData && $approve['this_current_approver']) {
                //有开启短信 并且需要通知审批人
                $ToolMod = new ToolController();
                if ($settingData['doApprove']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                    $sms = $this->formatSmsContent($settingData['doApprove']['content'], $data);
                    $ToolMod->sendingSMS($approve_user['telephone'], $sms, $this->Controller, $add);
                }
            }
            //==========================================短信 END==========================================
            if(C('USE_FEISHU') === 1){
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**设备名称：**'.$assets['assets'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**设备编码：**'.$assets['assnum'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**调出科室：**'.$data['department'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '请尽快进行审批';
                $feishu_fields[] = $fd;

                $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag'] = 'div';
                $card_data['elements'][0]['fields'] = $feishu_fields;
                $card_data['header']['template'] = 'blue';
                $card_data['header']['title']['content'] = '设备外调审批申请';
                $card_data['header']['title']['tag'] = 'plain_text';

                if ($approve_user['openid']) {
                    $this->send_feishu_card_msg($approve_user['openid'],$card_data);
                }
                //==========================================飞书 END==========================================
            }else{
                //==================================微信通知外调审批 END====================================
                $moduleModel = new ModuleModel();
                $wx_status = $moduleModel->decide_wx_login();
                if ($wx_status && $approve_user['openid']) {
                    $typeName = '';

                    switch ($data['apply_type']) {
                        case C('OUTSIDE_CALL_OUT_TYPE'):
                            $typeName = C('OUTSIDE_CALL_OUT_TYPE_NAME');
                            break;
                        case C('OUTSIDE_DONATION_TYPE'):
                            $typeName = C('OUTSIDE_DONATION_TYPE_NAME');
                            break;
                        case C('OUTSIDE_OUTSIDE_SALE_TYPE'):
                            $typeName = C('OUTSIDE_OUTSIDE_SALE_TYPE_NAME');
                            break;
                    }

                    Weixin::instance()->sendMessage($approve_user['openid'], '设备调度申请审批通知', [
                        'thing11' => $data['department'],// 所属科室
                        'thing12' => $assets['assets'],// 设备名称
                        'const7'  => "外调（{$typeName}）",// 调度类型
                        'thing13' => $data['accept'],// 调度目的地
                        'const10' => '待审批',// 审批状态
                    ]);
                }
                //==================================微信通知借调审批 END====================================
            }

            //日志行为记录文字
            $log['assets'] = $assets['assets'];
            $log['accept'] = $accept;
            $text = getLogText('applyOutsideLogText', $log);
            $this->addLog('assets_outside', $sql, $text, $add);
            return array('status' => 1, 'msg' => '提交成功');
        } else {
            return array('status' => -1, 'msg' => '提交失败');
        }
    }

    //外调审批列表数据
    public function outSideApproveList()
    {

        $departid = I('POST.departid');
        $model = I('POST.assetsModel');
        $hospital_id = I('POST.hospital_id');
        $assetsName = I('POST.assetsName');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order');
        $sort = I('POST.sort');
        $where['approve_status'] = array('NEQ', C('STATUS_APPROE_UNWANTED'));
        $where['all_approver'] = array('LIKE', '%/' . session('username') . '/%');
        if (!$sort) {
            $sort = 'outid';
        }
        if (!$order) {
            $order = 'desc';
        }
        if ($departid or $model or $assetsName) {
            if ($departid) {
                $assetsWhere['departid'] = array('EQ', $departid);
            }
            if ($model) {
                $assetsWhere['model'] = array('LIKE', '%' . $model . '%');
            }
            if ($assetsName) {
                $assetsWhere['assets'] = array('LIKE', '%' . $assetsName . '%');
            }
        }
        if ($hospital_id) {
            $assetsWhere['hospital_id'] = $hospital_id;
        } else {
            $assetsWhere['hospital_id'] = session('current_hospitalid');
        }
        //  排除通过的设备
        //  $assetsWhere['status'] = array('EQ',C('ASSETS_STATUS_USE'));
        $assets = $this->DB_get_all('assets_info', 'assid', $assetsWhere);
        $assetsAssid = [];
        if ($assets) {
            foreach ($assets as &$assetsValue) {
                $assetsAssid[] = $assetsValue['assid'];
            }
            $where['assid'][] = array('IN', $assetsAssid);
        } else {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //获取审批列表信息
        $fileds = 'outid,assid,apply_type,reason,accept,status,approve_status,current_approver,complete_approver,not_complete_approver,all_approver';
        $total = $this->DB_get_count('assets_outside', $where);
        $data = $this->DB_get_all('assets_outside', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //获取设备基本信息
        $assid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
        }
        $assetsWhere = [];
        $assetsWhere['assid'] = array('IN', $assid);
        $fileds = 'departid,assets,assnum,brand,model,status,assid,opendate,buy_price,guarantee_date';
        $assets = $this->DB_get_all('assets_info', $fileds, $assetsWhere);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        $assetsData = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";

        foreach ($assets as &$assetsV) {
            $assetsData[$assetsV['assid']]['department'] = $departname[$assetsV['departid']]['department'];
            $assetsData[$assetsV['assid']]['opendate'] = HandleEmptyNull($assetsV['opendate']);
            $assetsData[$assetsV['assid']]['guarantee_date'] = HandleEmptyNull($assetsV['guarantee_date']);
            $assetsData[$assetsV['assid']]['buy_price'] = $assetsV['buy_price'];
            if (!$showPrice) {
                $assetsData[$assetsV['assid']]['buy_price'] = '***';
            }
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
                case C('ASSETS_STATUS_OUTSIDE'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_OUTSIDE_NAME');
                    break;
                case C('ASSETS_STATUS_OUTSIDE_ON'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_OUTSIDE_ON_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP_ON'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_SCRAP_ON_NAME');
                    break;
                case C('ASSETS_STATUS_TRANSFER_ON'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_TRANSFER_ON_NAME');
                    break;
            }
        }
        //获取参保信息
        $insuranceWhere['assid'] = array('IN', $assid);
        $insuranceWhere['status'] = array('EQ', C('INSURANCE_STATUS_USE'));
        $insurance = $this->DB_get_all('assets_insurance', 'status,assid', $insuranceWhere);
        $insuranceData = [];
        foreach ($insurance as &$insuranceV) {
            $insuranceData[$insuranceV['assid']] = $insuranceV['status'];
        }
        //获取维修信息
        $repairNum = [];
        $repairWhere['assid'] = array('IN', $assid);
        $repair = $this->DB_get_all('repair', 'assid', $repairWhere);
        if ($repair) {
            $repair = $this->returnRepeat($repair, 'assid');
            foreach ($repair as &$repairV) {
                $repairNum[$repairV['assid']]['sum'] = $repairV['sum'];
            }
        }
        //筛选计量计划中的设备
        $meteringWhere['status'] = array('EQ', C('YES_STATUS'));
        $meteringWhere['assid'] = array('IN', $assid);
        $metering = $this->DB_get_all('metering_plan', 'assid', $meteringWhere);
        $meteringAssid = [];
        if ($metering) {
            foreach ($metering as &$meteringV) {
                $meteringAssid[$meteringV['assid']] = true;
            }
        }
        $Apply = get_menu($this->MODULE, $this->Controller, 'assetOutSideApprove');
        foreach ($data as &$dataV) {
            $html = '<div class="layui-btn-group">';
            $html .= $this->returnButtonLink('设备详情', C('SHOWASSETS_ACTION_URL'), 'layui-btn layui-btn-xs layui-btn-primary', '', 'lay-event = showAssets');
            if (getHandleTime(time()) < $assetsData[$dataV['assid']]['guarantee_date']) {
                $dataV['guarantee_status'] = '保修期内';
            } else {
                if ($insuranceData[$dataV['assid']] == C('INSURANCE_STATUS_USE')) {
                    $dataV['guarantee_status'] = '参保期内';
                } else {
                    $dataV['guarantee_status'] = '脱保';
                }
            }
            switch ($dataV['apply_type']) {
                case C('OUTSIDE_CALL_OUT_TYPE'):
                    $dataV['apply_type_name'] = C('OUTSIDE_CALL_OUT_TYPE_NAME');
                    break;
                case C('OUTSIDE_DONATION_TYPE'):
                    $dataV['apply_type_name'] = C('OUTSIDE_DONATION_TYPE_NAME');
                    break;
                case C('OUTSIDE_OUTSIDE_SALE_TYPE'):
                    $dataV['apply_type_name'] = C('OUTSIDE_OUTSIDE_SALE_TYPE_NAME');
                    break;
            }
            $dataV['repairNum'] = $repairNum[$dataV['assid']]['sum'] ? $repairNum[$dataV['assid']]['sum'] : 0;
            $dataV['department'] = $assetsData[$dataV['assid']]['department'];

            $dataV['assets'] = $assetsData[$dataV['assid']]['assets'];
            $dataV['assnum'] = $assetsData[$dataV['assid']]['assnum'];
            $dataV['opendate'] = $assetsData[$dataV['assid']]['opendate'];
            $dataV['buy_price'] = $assetsData[$dataV['assid']]['buy_price'];
            $dataV['brand'] = $assetsData[$dataV['assid']]['brand'];
            $dataV['model'] = $assetsData[$dataV['assid']]['model'];
            $dataV['statusName'] = $assetsData[$dataV['assid']]['statusName'];
            //详情url
            $detailsUrl = get_url() . '?action=showApproveDetails&outid=' . $dataV['outid'] . '&assid=' . $dataV['assid'];
            if ($dataV['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                //审批完成
                $html .= $this->returnListLink('已通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY'));
            } elseif ($dataV['approve_status'] == C('STATUS_APPROE_FAIL')) {
                //审批失败
                $html .= $this->returnListLink('不通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-danger');
            } else {
                if ($Apply && $dataV['current_approver']) {
                    $current_approver = explode(',', $dataV['current_approver']);
                    $current_approver_arr = [];
                    foreach ($current_approver as &$current_approver_value) {
                        $current_approver_arr[$current_approver_value] = true;
                    }
                    if ($current_approver_arr[session('username')]) {
                        $html .= $this->returnListLink('审批', $Apply['actionurl'], 'assetOutsideApprove', C('BTN_CURRENCY') . ' layui-btn-normal');
                    } else {
                        $complete = explode(',', $dataV['complete_approver']);
                        $notcomplete = explode(',', $dataV['not_complete_approver']);
                        if (!in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                            //完全未审
                            $html .= $this->returnListLink('待审批', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                            //有已审，有未审
                            $html .= $this->returnListLink('待审批', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && !in_array(session('username'), $notcomplete)) {
                            //全部已审
                            $html .= $this->returnListLink('已审批', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-primary');
                        } else {
                            $html .= '';
                        }
                    }
                }
            }
            $html .= '</div>';
            $dataV['operation'] = $html;
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //外调审批操作
    public function assetOutSideApprove()
    {
        $outid = I('POST.outid');
        if (!$outid) {
            die(json_encode(array('status' => -1, 'msg' => '非法操作')));
        }
        //检查是否存在此条记录
        $where['outid'] = array('EQ', $outid);
        $outside = $this->DB_get_one('assets_outside', 'outid,assid,apply_type,accept,apply_userid,apply_time,approve_status,current_approver,complete_approver,all_approver,order_price,not_complete_approver', $where);
        if (!$outside) {
            die(json_encode(array('status' => -1, 'msg' => '无外调信息')));
        } else {
            switch ($outside['approve_status']) {
                case C('STATUS_APPROE_UNWANTED'):
                    die(json_encode(array('status' => -1, 'msg' => '不需审批,请按正常流程操作')));
                    break;
                case C('STATUS_APPROE_SUCCESS'):
                    die(json_encode(array('status' => -1, 'msg' => '审批已通过,请勿重复操作')));
                    break;
                case C('STATUS_APPROE_FAIL'):
                    die(json_encode(array('status' => -1, 'msg' => '审批已驳回,请勿重复操作')));
                    break;
            }
        }
        //获取申请人姓名
        $user = $this->DB_get_one('user', 'username,openid', array('userid' => $outside['apply_userid']));

        //查询设备信息
        $assInfo = $this->DB_get_one('assets_info', 'assid,hospital_id,assnum,assets,status,departid,quality_in_plan,patrol_in_plan', array('assid' => $outside['assid']));
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assInfo['department'] = $departname[$assInfo['departid']]['department'];
        $data['outid'] = $outid;
        $data['is_adopt'] = I('POST.is_adopt');
        $data['remark'] = trim(I('POST.remark'));
        $data['proposer'] = $user['username'];
        $data['proposer_time'] = $outside['apply_time'];
        $data['approver'] = session('username');
        $data['approve_time'] = time();
        $data['approve_class'] = 'outside';
        $data['process_node'] = C('OUTSIDE_APPROVE');
        $outside['assets'] = $assInfo['assets'];
        //判断是否是当前审批人
        if ($outside['current_approver']) {
            $current_approver = explode(',', $outside['current_approver']);
            $current_approver_arr = [];
            foreach ($current_approver as &$current_approver_value) {
                $current_approver_arr[$current_approver_value] = true;
            }
            if ($current_approver_arr[session('username')]) {
                $processWhere['outid'] = array('EQ', $outside['outid']);
                $processWhere['is_delete'] = array('NEQ', C('YES_STATUS'));
                $process = $this->DB_get_count('approve', $processWhere);
                $level = $process + 1;
                $data['process_node_level'] = $level;
                $res = $this->addApprove($outside, $data, $outside['order_price'], $assInfo['hospital_id'], $assInfo['departid'], C('OUTSIDE_APPROVE'), 'assets_outside', 'outid');
                if ($res['status'] == 1) {
                    $text = getLogText('approveOutSide', array('assnum' => $assInfo['assnum'], 'is_adopt' => I('POST.is_adopt')));
                    $this->addLog('assets_outside', $res['lastSql'], $text, $outside['outid'], '');
                }
                $not_complete_approver = explode(',', $outside['not_complete_approver']);
                $count = count($not_complete_approver);
                $moduleModel = new ModuleModel();
                $wx_status = $moduleModel->decide_wx_login();
                if ($data['is_adopt'] == 1 && $count == 2) {
                    //审批通过通知报废者
                    if(C('USE_FEISHU') === 1){
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**'.$assInfo['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**'.$assInfo['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**所属科室：**'.$assInfo['department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**审批意见：**通过';
                        $feishu_fields[] = $fd;

                        $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['header']['template'] = 'blue';
                        $card_data['header']['title']['content'] = '设备外调申请审批完成提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        if ($user['openid']) {
                            $this->send_feishu_card_msg($user['openid'],$card_data);
                        }
                        //==========================================飞书 END==========================================
                    }else{
                        if($wx_status){
                            Weixin::instance()->sendMessage($user['openid'], '设备外调审批结果通知', [
                                'thing1'            => $assInfo['department'],// 调出科室
                                'thing2'            => $assInfo['assets'],// 设备名称
                                'character_string3' => $assInfo['assnum'],// 设备编码
                                'thing4'            => $user['username'],// 申请人
                                'const5'            => '已通过',// 审批结果
                            ]);
                        }
                    }
                } else if ($data['is_adopt'] == 1) {
                    //审批通过通知下一位
                    $username = $this->DB_get_one('user', 'openid', array('username' => $not_complete_approver[2]));
                    if(C('USE_FEISHU') === 1){
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**'.$assInfo['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**'.$assInfo['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**所属科室：**'.$assInfo['department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**申请原因：**'.$data['remark'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '请尽快完成审批';
                        $feishu_fields[] = $fd;

                        $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['header']['template'] = 'blue';
                        $card_data['header']['title']['content'] = '设备外调审批申请提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        if ($username['openid']) {
                            $this->send_feishu_card_msg($username['openid'],$card_data);
                        }
                        //==========================================飞书 END==========================================
                    }else{
                        if($wx_status && $username['openid']) {
                            $typeName = '';

                            switch ($outside['apply_type']) {
                                case C('OUTSIDE_CALL_OUT_TYPE'):
                                    $typeName = C('OUTSIDE_CALL_OUT_TYPE_NAME');
                                    break;
                                case C('OUTSIDE_DONATION_TYPE'):
                                    $typeName = C('OUTSIDE_DONATION_TYPE_NAME');
                                    break;
                                case C('OUTSIDE_OUTSIDE_SALE_TYPE'):
                                    $typeName = C('OUTSIDE_OUTSIDE_SALE_TYPE_NAME');
                                    break;
                            }

                            Weixin::instance()->sendMessage($username['openid'], '设备调度申请审批通知', [
                                'thing11' => $assInfo['department'],// 所属科室
                                'thing12' => $assInfo['assets'],// 设备名称
                                'const7'  => "外调（{$typeName}）",// 调度类型
                                'thing13' => $outside['accept'],// 调度目的地
                                'const10' => '待审批',// 审批状态
                            ]);
                        }
                    }
                } else {
                    //审批不通过通知报废者
                    if(C('USE_FEISHU') === 1){
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**'.$assInfo['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**'.$assInfo['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**所属科室：**'.$assInfo['department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**审批意见：**不通过';
                        $feishu_fields[] = $fd;

                        $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['header']['template'] = 'blue';
                        $card_data['header']['title']['content'] = '设备外调申请审批完成提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        if ($user['openid']) {
                            $this->send_feishu_card_msg($user['openid'],$card_data);
                        }
                        //==========================================飞书 END==========================================
                    }else{
                        if($wx_status){
                            Weixin::instance()->sendMessage($user['openid'], '设备外调审批结果通知', [
                                'thing1'            => $assInfo['department'],// 调出科室
                                'thing2'            => $assInfo['assets'],// 设备名称
                                'character_string3' => $assInfo['assnum'],// 设备编码
                                'thing4'            => $user['username'],// 申请人
                                'const5'            => '未通过',// 审批结果
                            ]);
                        }
                    }
                }
                return $res;
            } else {
                return array('status' => -1, 'msg' => '请等待审批！');
            }
        } else {
            return array('status' => -1, 'msg' => '审核已结束！');
        }
    }

    //外调结果列表
    public function outSideResultList()
    {
        $departid = I('POST.departid');
        $model = I('POST.assetsModel');
        $assetsName = I('POST.assetsName');
        $apply_type = I('POST.apply_type');
        $hospital_id = I('POST.hospital_id');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'outid';
        if (!session('departid')) {
            $result['msg'] = '暂无分配科室';
            $result['code'] = 400;
            return $result;
        }
        $assetsWhere['departid'][] = array('IN', session('departid'));
        if ($departid or $model or $assetsName) {
            if ($departid) {
                $assetsWhere['departid'][] = array('EQ', $departid);
            }
            if ($model) {
                $assetsWhere['model'] = array('LIKE', '%' . $model . '%');
            }
            if ($assetsName) {
                $assetsWhere['assets'] = array('LIKE', '%' . $assetsName . '%');
            }
        }

        if ($hospital_id) {
            $assetsWhere['hospital_id'] = $hospital_id;
        } else {
            $assetsWhere['hospital_id'] = session('current_hospitalid');
        }

        if ($apply_type) {
            $where['apply_type'] = array('EQ', $apply_type);
        }

        $assets = $this->DB_get_all('assets_info', 'assid', $assetsWhere);
        $assetsAssid = [];
        if ($assets) {
            foreach ($assets as &$assetsValue) {
                $assetsAssid[] = $assetsValue['assid'];
            }
            $where['assid'][] = array('IN', $assetsAssid);
        } else {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        $where['status'][0] = 'IN';
        $where['status'][1][] = C('OUTSIDE_STATUS_ACCEPTANCE_CHECK');
        $where['status'][1][] = C('OUTSIDE_STATUS_COMPLETE');
        $where['status'][1][] = C('OUTSIDE_STATUS_FAIL');

        //获取审批列表信息
        $fileds = 'outid,assid,apply_type,reason,accept,status,approve_status';
        $total = $this->DB_get_count('assets_outside', $where);
        $data = $this->DB_get_all('assets_outside', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        $result['data'] = $data;
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //获取设备基本信息
        $assid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
        }
        $assetsWhere = [];
        $assetsWhere['assid'] = array('IN', $assid);
        $fileds = 'departid,assets,assnum,brand,model,status,assid,opendate,buy_price';
        $assets = $this->DB_get_all('assets_info', $fileds, $assetsWhere);
        $assetsData = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assets as &$assetsV) {
            $assetsData[$assetsV['assid']]['department'] = $departname[$assetsV['departid']]['department'];
            $assetsData[$assetsV['assid']]['opendate'] = HandleEmptyNull($assetsV['opendate']);
            $assetsData[$assetsV['assid']]['buy_price'] = $assetsV['buy_price'];
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
        $checkOutSiteAssetMenu = get_menu($this->MODULE, $this->Controller, 'checkOutSiteAsset');
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($data as &$dataV) {
            switch ($dataV['apply_type']) {
                case C('OUTSIDE_CALL_OUT_TYPE'):
                    $dataV['apply_type_name'] = C('OUTSIDE_CALL_OUT_TYPE_NAME');
                    break;
                case C('OUTSIDE_DONATION_TYPE'):
                    $dataV['apply_type_name'] = C('OUTSIDE_DONATION_TYPE_NAME');
                    break;
                case C('OUTSIDE_OUTSIDE_SALE_TYPE'):
                    $dataV['apply_type_name'] = C('OUTSIDE_OUTSIDE_SALE_TYPE_NAME');
                    break;
            }
            $dataV['department'] = $assetsData[$dataV['assid']]['department'];
            $dataV['assets'] = $assetsData[$dataV['assid']]['assets'];
            $dataV['assnum'] = $assetsData[$dataV['assid']]['assnum'];
            $dataV['opendate'] = $assetsData[$dataV['assid']]['opendate'];
            $dataV['buy_price'] = $assetsData[$dataV['assid']]['buy_price'];
            if (!$showPrice) {
                $dataV['buy_price'] = '***';
            }
            $dataV['brand'] = $assetsData[$dataV['assid']]['brand'];
            $dataV['model'] = $assetsData[$dataV['assid']]['model'];
            $dataV['statusName'] = $assetsData[$dataV['assid']]['statusName'];
            //详情url//生命进程页面
            $detailsUrl = C('ADMIN_NAME') . '/Lookup/assetsLifeList.html?action=showLife&assid=' . $dataV['assid'] . '&changeTab=14';

            if ($dataV['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                //审批通过
                $dataV['approve_status'] = '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
            } elseif ($dataV['approve_status'] == C('STATUS_APPROE_FAIL')) {
                //审批失败
                $dataV['approve_status'] = '<i class="layui-icon layui-icon-zzclose" style="color: red"></i>';
            } elseif ($dataV['approve_status'] == C('STATUS_APPROE_UNWANTED')) {
                //不需审批
                $dataV['approve_status'] = '不需审批';
            }

            $dataV['operation'] = '<div class="layui-btn-group">';
            if ($dataV['status'] == C('OUTSIDE_STATUS_ACCEPTANCE_CHECK') && $checkOutSiteAssetMenu) {
                $dataV['operation'] .= $this->returnListLink($checkOutSiteAssetMenu['actionname'], $checkOutSiteAssetMenu['actionurl'], 'checkOutSiteAsset', C('BTN_CURRENCY'));
            } else {
                $dataV['operation'] .= $this->returnListLink('查看详情', $detailsUrl, 'showLife', C('BTN_CURRENCY') . ' layui-btn-primary');
                if ($dataV['status'] == C('OUTSIDE_STATUS_COMPLETE')) {
                    $printReportUrl = get_url() . '?action=printReport&assid=' . $dataV['assid'] . '&outid=' . $dataV['outid'];
                    $uploadReportUrl = get_url() . '?action=uploadReport&outid=' . $dataV['outid'];
                    $dataV['operation'] .= $this->returnListLink('打印审批单', $printReportUrl, 'printReport', C('BTN_CURRENCY'));
                    $dataV['operation'] .= $this->returnListLink('上传/查看审批单', $uploadReportUrl, 'uploadReport', C('BTN_CURRENCY') . ' layui-btn-warm');
                }
            }
            $dataV['operation'] .= '</div>';
        }

        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //验收单录入操作
    public function checkOutSiteAsset()
    {
        $outid = I('POST.outid');
        $check_remark = I('POST.check_remark');
        $check_person = I('POST.check_person');
        $check_phone = I('POST.check_phone');
        $check_date = I('POST.check_date');
        if (!$outid) {
            die(json_encode(array('status' => -1, 'msg' => '非法操作')));
        }
        if (!$check_date) {
            die(json_encode(array('status' => -1, 'msg' => '请补充验收时间')));
        }
        if ($check_phone) {
            if (!judgeMobile($check_phone) && !judgePhone($check_phone)) {
                die(json_encode(array('status' => -1, 'msg' => '请输入正常的号码')));
            }
        }
        $data['check_person'] = $check_person;
        $data['check_phone'] = $check_phone;
        $data['check_date'] = strtotime($check_date);
        $data['check_remark'] = $check_remark;
        $data['status'] = C('OUTSIDE_STATUS_COMPLETE');
        $save = $this->updateData('assets_outside', $data, array('outid' => $outid));
        if ($save) {
            $this->addOutsideFile($outid, C('OUTSIDE_FILE_TYPE_CHECK'));
            return array('status' => 1, 'msg' => '提交成功');
        } else {
            return array('status' => -1, 'msg' => '提交失败');
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
        $files = 'assid,catid,assnum,assets,helpcatid,status,brand,model,unit,serialnum,assetsrespon,departid,address,buy_price,
        opendate,guarantee_date,insuredsum,is_metering,quality_in_plan,patrol_in_plan';
        $assets = $this->DB_get_one('assets_info', $files, $where);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assets['buy_price'] = '***';
        }
        $files = 'afid,factory,factory_user,factory_tel,supplier,supp_user,supp_tel,repair,repa_user,repa_tel';
        $factory = $this->DB_get_one('assets_factory', $files, $where);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assets['department'] = $departname[$assets['departid']]['department'];
        $assets['opendate'] = HandleEmptyNull($assets['opendate']);
        switch ($assets['status']) {
            case C('ASSETS_STATUS_USE'):
                $assets['statusName'] = C('ASSETS_STATUS_USE_NAME');
                break;
            case C('ASSETS_STATUS_REPAIR'):
                $assets['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                break;
            case C('ASSETS_STATUS_OUTSIDE'):
                $assets['statusName'] = C('ASSETS_STATUS_OUTSIDE_NAME');
                break;
        }

        //查询维保状态
        $insuranceWhere['assid'] = array('EQ', $assid);
        $insuranceWhere['status'] = array('EQ', C('INSURANCE_STATUS_USE'));
        $insurance = $this->DB_get_one('assets_insurance', 'status,assid', $insuranceWhere);
        $insuranceData = [];
        foreach ($insurance as &$insuranceV) {
            $insuranceData[$insuranceV['assid']] = $insuranceV['status'];
        }
        if (getHandleTime(time()) < $assets['guarantee_date']) {
            $assets['guarantee_status'] = '保修期内';
        } else {
            if ($insuranceData[$assets['assid']] == C('INSURANCE_STATUS_USE')) {
                $assets['guarantee_status'] = '参保期内';
            } else {
                $assets['guarantee_status'] = '脱保';
            }
        }

        if ($assets['patrol_in_plan'] == C('YES_STATUS')) {
            $assets['patrol_status'] = '计划中';
        } else {
            $assets['patrol_status'] = '不在计划中';
        }

        //计量状态 默认不在计划中
        if ($assets['is_metering'] == C('YES_STATUS')) {
            $assets['meterin_status'] = '不在计划中';
        } else {
            $assets['meterin_status'] = '非计量设备';
        }

        $meterinWhere['status'] = array('EQ', C('OPEN_STATUS'));
        $meterinWhere['assid'] = array('EQ', $assid);
        $meterin = $this->DB_get_one('metering_plan', 'mpid', $meterinWhere);
        if ($meterin) {
            $assets['meterin_status'] = '计划中';
        }
        //维修次数
        $repairWhere['assid'] = $assid;
        $repairNum = $this->DB_get_count('repair', $repairWhere);
        $assets['repairNum'] = $repairNum;
        if (empty($factory)){
            return $assets;
        }else{
            return array_merge($assets, $factory);
        }
    }


    /**
     * 获取外调基本信息
     * @param $outid int 外调id
     * @param $assid int 设备id
     * @return array
     * */
    public function getOutsideBasic($outid, $assid = '')
    {

        if ($assid) {
            $where['assid'] = array('EQ', $assid);
        } else {
            $where['outid'] = array('EQ', $outid);
        }
        $files = 'outid,assid,apply_userid,apply_type,price,reason,accept,person,phone,outside_date,apply_userid,apply_time,
        approve_status,status,approve_time,check_date,check_person,check_phone,check_remark,current_approver,all_approver';
        $outside = $this->DB_get_one('assets_outside', $files, $where, 'outid desc');
        if (!$outside) {
            $join = 'LEFT JOIN sb_assets_outside_detail AS B ON A.outid = B.outid';
            $outside = $this->DB_get_one_join('assets_outside', 'A', '', $join, array('subsidiary_assid' => $assid));
        }
        $outside['apply_time'] = getHandleMinute($outside['apply_time']);
        $outside['outside_date'] = getHandleTime($outside['outside_date']);
        $outside['check_date'] = getHandleTime($outside['check_date']);


        switch ($outside['apply_type']) {
            case C('OUTSIDE_CALL_OUT_TYPE'):
                $outside['apply_type_name'] = C('OUTSIDE_CALL_OUT_TYPE_NAME');
                break;
            case C('OUTSIDE_DONATION_TYPE'):
                $outside['apply_type_name'] = C('OUTSIDE_DONATION_TYPE_NAME');
                break;
            case C('OUTSIDE_OUTSIDE_SALE_TYPE'):
                $outside['apply_type_name'] = C('OUTSIDE_OUTSIDE_SALE_TYPE_NAME');
                break;
        }


        $userId[] = $outside['apply_userid'];

        if ($outside['apply_userid']) {
            $userId[] = $outside['apply_userid'];
        }


        $user = $this->DB_get_All('user', 'userid,username', array('userid' => array('IN', $userId)));


        foreach ($user as &$userV) {
            if ($userV['userid'] == $outside['apply_userid']) {
                $outside['apply_username'] = $userV['username'];
            }
        }

        return $outside;
    }

    /**
     * 获取外调审核基本信息
     * @param $outid int 外调id
     * @return array
     * */
    public function getOutsideApprovBasic($outid)
    {
        $where['outid'] = array('EQ', $outid);
        $where['approve_class'] = array('EQ', 'outside');
        $where['process_node'] = array('EQ', C('OUTSIDE_APPROVE'));
        $approve = $this->DB_get_all('approve', '', $where, '', 'process_node_level,approve_time asc');
        foreach ($approve as &$approveV) {
            $approveV['approve_time'] = getHandleMinute($approveV['approve_time']);
            switch ($approveV['is_adopt']) {
                case C('STATUS_APPROE_SUCCESS'):
                    $approveV['is_adoptName'] = '<span style = "color:green" >通过</span >';
                    $approveV['is_adoptNameNoColor'] = '通过';
                    break;
                case C('STATUS_APPROE_FAIL'):
                    $approveV['is_adoptName'] = '<span class="rquireCoin" >不通过</span >';
                    $approveV['is_adoptNameNoColor'] = '不通过';
                    break;
                default :
                    $approveV['is_adoptName'] = '未审核';
            }
        }
        return $approve;
    }


    /**
     * 获取上传文件
     * @param $type int 附件种类
     * @return  array
     * */
    public function getFileList($type, $outid)
    {
        $where['outid'] = array('EQ', $outid);
        $where['type'] = array('EQ', $type);
        $where['is_delete'] = array('EQ', C('NO_STATUS'));
        $file = $this->DB_get_all('assets_outside_file', 'fileid as file_id,file_name,save_name,file_type,file_url,add_user,add_time', $where);
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
            $dirName = C('UPLOAD_DIR_OUTSIDE_NAME');
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
        $add['type'] = C('OUTSIDE_FILE_TYPE_REPORT');
        $add['save_name'] = $file['title'];
        $add['file_type'] = $file['ext'];
        $add['file_size'] = $file['size'];
        $add['file_url'] = $file['path'];
        $add['add_user'] = session('username');
        $add['add_time'] = getHandleDate(time());
        $file_id = $this->insertData('assets_outside_file', $add);
        $file['file_id'] = $file_id;
        return $file;
    }

    //删除报告
    public function deleteFile()
    {
        $file_id = I('POST.file_id');
        if ($file_id) {
            $where['fileid'] = $file_id;
            $data = $this->DB_get_one('assets_outside_file', 'is_delete', $where);
            if ($data) {
                if ($data['is_delete'] != C('YES_STATUS')) {
                    $data['is_delete'] = C('YES_STATUS');
                    $save = $this->updateData('assets_outside_file', $data, $where);
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
     * 记录设备外调附属设备信息
     * @param $outid int 外调id
     * */
    public function setOutSideDetail($outid)
    {
        $subsidiary_assid = trim(I('POST.subsidiary_assid'));
        if ($subsidiary_assid) {
            $addData = [];
            $assid_arr = explode(",", $subsidiary_assid);
            for ($i = 0; $i < count($assid_arr); $i++) {
                $addData[$i]['outid'] = $outid;
                $addData[$i]['subsidiary_assid'] = $assid_arr[$i];
            }
            $this->insertDataALL('assets_borrow_detail', $addData);
        }
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
        //筛选计量计划中
        $meteringWhere['status'] = array('EQ', C('YES_STATUS'));
        $meteringWhere['assid'] = array('EQ', $assid);
        $metering = $this->DB_get_one('metering_plan', 'assid', $meteringWhere);
        if ($metering) {
            foreach ($metering as &$met) {
                $not_assid[] = $met['assid'];
            }
        }
        if ($not_assid != []) {
            $where['assid'] = ['NOTIN', $not_assid];
        }
        $where['main_assid'] = ['EQ', $assid];
        $where['quality_in_plan'] = ['EQ', C('NO_STATUS')];
        $where['patrol_in_plan'] = ['EQ', C('NO_STATUS')];
        $where['status'] = ['EQ', C('ASSETS_STATUS_USE')];
        $data = $this->DB_get_all('assets_info', 'assid,assets,assnum,model,unit,buy_price', $where);
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($data as $key => $value) {
            if (!$showPrice) {
                $data[$key]['buy_price'] = '***';
            }
        }
        return $data;
    }


    /**
     * 获取附属设备信息
     * @param $outid int 外调记录id
     * @return array
     * */
    public function getSubsidiaryBasic($outid)
    {
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price';
        $data = $this->DB_get_all_join('assets_outside_detail', 'D', $fields, $join, "outid=$outid", '', '', '');
        return $data;
    }

    /**
     * 记录上传外调文件
     * @param $outid int 外调id
     * @param $type int 附件种类
     */
    public function addOutsideFile($outid, $type)
    {
        $file_name = explode('|', rtrim(I('POST.file_name'), '|'));
        $save_name = explode('|', rtrim(I('POST.save_name'), '|'));
        $file_url = explode('|', rtrim(I('POST.file_url'), '|'));
        $file_type = explode('|', rtrim(I('POST.file_type'), '|'));
        $file_size = explode('|', rtrim(I('POST.file_size'), '|'));
        $addAll = [];
        $data = [];
        foreach ($file_name as $k => $v) {
            if (!$v) {
                continue;
            } else {
                $data[$k]['type'] = $type;
                $data[$k]['outid'] = $outid;
                $data[$k]['file_name'] = $v;
                $data[$k]['save_name'] = $save_name[$k];
                $data[$k]['file_type'] = $file_type[$k];
                $data[$k]['file_size'] = $file_size[$k];
                $data[$k]['file_url'] = $file_url[$k];
                $data[$k]['add_user'] = session('username');
                $data[$k]['add_time'] = getHandleDate(time());
                $addAll[] = $data[$k];
            }
        }
        if ($addAll) {
            $this->insertDataALL('assets_outside_file', $data);
        }
    }

    /**
     * 统计重复字段数量
     * @param array $list 需要统计的数组
     * @param string $keyName 需要统计的字段
     * @return  array
     */
    public function returnRepeat($list, $keyName)
    {
        $newArr = [];
        foreach ($list as $key => $value) {
            if ($newArr) {
                foreach ($newArr as $key2 => $value2) {
                    if ($value[$keyName] == $value2[$keyName]) {
                        $newArr[$key2]['sum']++;
                    } else {
                        //不存在 但是避免直接在末尾重复插入，做多一次循环，验证是否已存在
                        $Repeat = false;
                        foreach ($newArr as $key3 => $value3) {
                            if ($value[$keyName] == $value3[$keyName]) {
                                $Repeat = true;
                                break;
                            }
                        }
                        if (!$Repeat) {
                            $newArr[$key][$keyName] = $value[$keyName];
                            $newArr[$key]['sum'] = 1;
                        }
                    }
                }
            } else {
                $newArr[$key][$keyName] = $value[$keyName];
                $newArr[$key]['sum'] = 1;
            }
        }
        return $newArr;
    }

    //结束进程
    public function endOutside($outid = null)
    {
        $OutsideInfo = $this->DB_get_one('assets_outside', 'outid,assid', array('outid' => $outid));
        if (!$OutsideInfo) {
            return array('status' => -1, 'msg' => '查找不到该外调单！');
        }
        $this->updateData('assets_outside', array('retrial_status' => 3), array('outid' => $outid));
        $this->updateData('assets_info', array('status' => 0), array('assid' => $OutsideInfo['assid']));
        return array('status' => 1, 'msg' => '操作成功！');
    }

    //重审外调订单
    public function editOutside($outid = null)
    {
        $assid = I('POST.assid');
        $apply_type = I('POST.apply_type');
        $price = I('POST.price');
        $reason = I('POST.reason');
        $accept = I('POST.accept');
        $person = I('POST.person');
        $phone = I('POST.phone');
        $outside_date = I('POST.outside_date');
        $subsidiary_assid = trim(I('POST.subsidiary_assid'));
        $outside_data = $this->DB_get_one('assets_outside', 'approve_status,retrial_status,outid', array('outid' => $outid));
        if (!$apply_type) {
            die(json_encode(array('status' => -1, 'msg' => '请选择申请类型')));

        } elseif ($apply_type == 3) {
            if ($price <= 0) {
                die(json_encode(array('status' => -1, 'msg' => '请补充外售金额')));
            } else {
                $data['price'] = $price;
            }
        }
        if (!$reason) {
            die(json_encode(array('status' => -1, 'msg' => '请补充外调原因')));
        }

        if (!$accept) {
            die(json_encode(array('status' => -1, 'msg' => '请补充外调目的地')));
        }
        if ($phone) {
            if (!judgeMobile($phone) && !judgePhone($phone)) {
                die(json_encode(array('status' => -1, 'msg' => '请输入正常的号码')));
            }
        }
        //筛选计量计划中
        $meteringWhere['status'] = array('EQ', C('YES_STATUS'));
        $meteringWhere['assid'] = array('EQ', $assid);
        $metering = $this->DB_get_one('metering_plan', 'assid', $meteringWhere);
        if ($metering) {
            die(json_encode(array('status' => -1, 'msg' => '设备有启用中的计量计划,请停用或删除后再进行外调')));
        }
        $where['assid'] = array('EQ', $assid);
        $files = 'assid,hospital_id,departid,assets,status,quality_in_plan,patrol_in_plan,buy_price';
        $assets = $this->DB_get_one('assets_info', $files, $where);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assets['buy_price'] = '***';
        }
        if ($assets['status'] == C('ASSETS_STATUS_REPAIR')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在维修中，请等待结束后再申请外调')));
        }
        if ($assets['status'] == C('ASSETS_STATUS_OUTSIDE')) {
            die(json_encode(array('status' => -1, 'msg' => '设备已外调')));
        }
        if ($assets['status'] == C('ASSETS_STATUS_OUTSIDE_ON') && $outside_data['retrial_status'] != 1) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在外调申请，请勿重复申请')));
        }
        if ($assets['status'] == C('ASSETS_STATUS_SCRAP')) {
            die(json_encode(array('status' => -1, 'msg' => '设备已报废')));
        }
        if ($assets['status'] == C('ASSETS_STATUS_SCRAP_ON')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在报废申请，请等待结束后再申请外调')));
        }
        if ($assets['status'] == C('ASSETS_STATUS_TRANSFER_ON')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在转科申请，请等待结束后再申请外调')));
        }
        if ($assets['quality_in_plan'] == C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在质控执行中，请等待结束后再申请外调')));
        }
        if ($assets['patrol_in_plan'] == C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => '设备正在巡查中，请等待结束后再申请外调')));
        }
        //筛选借调中
        $borrowWhere['status'] = array('IN', [C('BORROW_STATUS_APPROVE'), C('BORROW_STATUS_BORROW_IN'), C('BORROW_STATUS_GIVE_BACK')]);
        $borrowWhere['assid'] = array('EQ', $assid);
        $borrow = $this->DB_get_one('assets_borrow', 'assid', $borrowWhere);
        if ($borrow) {
            die(json_encode(array('status' => -1, 'msg' => '设备借调中，请等待结束后再申请外调')));
        }
        //查询是否已开启外调审批
        $isOpenApprove = $this->checkApproveIsOpen(C('OUTSIDE_APPROVE'), $assets['hospital_id']);

        $data['order_price'] = $assets['buy_price'];
        $all_subsidiaryWhere['main_assid'] = ['EQ', $assid];
        $all_subsidiaryWhere['status'][0] = 'NOTIN';
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_SCRAP');//报废
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        $subsidiary_assets = $this->DB_get_all('assets_info', 'buy_price,assid', $all_subsidiaryWhere);
        $all_subsidiary_assid = [];
        $assid_arr = explode(",", $subsidiary_assid);
        if ($subsidiary_assid) {
            //有选择辅助设备 金额汇总
            foreach ($subsidiary_assets as &$sub_asstes) {
                $all_subsidiary_assid[] = $sub_asstes['assid'];
                if (in_array($sub_asstes['assid'], $assid_arr)) {
                    $data['order_price'] += $sub_asstes['buy_price'];
                }
            }
        }
        $approve = [];
        if ($isOpenApprove) {
            //查询是否已设置审批流程
            $isSetProcess = $this->checkApproveIsSetProcess(C('OUTSIDE_APPROVE'), $assets['hospital_id']);
            if (!$isSetProcess) {
                die(json_encode(array('status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！')));
            }
            $data['status'] = C('OUTSIDE_STATUS_APPROVE');//默认为未审核
            //获取审批流程
            $approve_process_user = $this->get_approve_process($data['order_price'], C('OUTSIDE_APPROVE'), $assets['hospital_id']);
            //并且获取下次审批人
            $approve = $this->check_approve_process($assets['departid'], $approve_process_user, 1);
            if ($approve['all_approver'] == '') {
                //不在审核范围内 不需要审批
                $data['approve_status'] = C('STATUS_APPROE_UNWANTED');
                $data['retrial_status'] = '3';
            } else {
                //默认为未审核
                $data['current_approver'] = $approve['current_approver'];
                $data['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                $data['all_approver'] = $approve['all_approver'];
                $data['approve_status'] = C('APPROVE_STATUS');
                $data['retrial_status'] = '2';
            }
        } else {
            //跳过审核直接到验收
            $data['status'] = C('OUTSIDE_STATUS_ACCEPTANCE_CHECK');
            $data['retrial_status'] = '3';
            $data['approve_status'] = C('STATUS_APPROE_UNWANTED');//不需审核
        }
        $data['assid'] = $assid;
        $data['apply_type'] = $apply_type;
        $data['reason'] = $reason;
        $data['accept'] = $accept;
        $data['person'] = $person;
        $data['phone'] = $phone;
        $data['outside_date'] = strtotime($outside_date);
        $data['apply_userid'] = session('userid');
        $data['apply_time'] = time();
        $add = $this->updateData('assets_outside', $data, array('outid' => $outside_data['outid']));;
        if ($add) {
            //删除审批记录
            $this->updateData('approve', array('is_delete' => 1), array('outid' => $outside_data['outid']));
            $this->addOutsideFile($outside_data['outid'], C('OUTSIDE_FILE_TYPE_APPLY'));
            //记录辅助设备
            $update_assid_arr = [];
            if ($subsidiary_assid) {
                $addData = [];
                $assid_arr = explode(",", $subsidiary_assid);
                for ($i = 0; $i < count($assid_arr); $i++) {
                    $update_assid_arr[] = $assid_arr[$i];
                    $addData[$i]['outid'] = $add;
                    $addData[$i]['subsidiary_assid'] = $assid_arr[$i];
                }
                $this->insertDataALL('assets_outside_detail', $addData);
            }
            if ($data['status'] == C('APPROVE_STATUS')) {
                //设备状态变更为外调中
                $update_assid_arr[] = $assid;
                $assetaData['status'] = C('ASSETS_STATUS_OUTSIDE_ON');
                $this->updateData('assets_info', $assetaData, ['assid' => ['IN', $update_assid_arr]]);
                $this->updateAllAssetsStatus($update_assid_arr, $assetaData['status'], '设备外调申请');
            } else {
                //设备状态直接变更已外调
                $diff_assid = array_diff($all_subsidiary_assid, $update_assid_arr);
                if ($diff_assid) {
                    //将未选中的附属设备变成无主
                    $diffData['main_assid'] = 0;
                    $diffData['main_assets'] = '';
                    $diffWhere['assid'] = ['IN', $diff_assid];
                    $this->updateData('assets_info', $diffData, $diffWhere);
                }
                $update_assid_arr[] = $assid;
                $assetaData['status'] = C('ASSETS_STATUS_OUTSIDE');
                $this->updateData('assets_info', $assetaData, ['assid' => ['IN', $update_assid_arr]]);
                $this->updateAllAssetsStatus($update_assid_arr, $assetaData['status'], '设备外调');
            }
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($settingData && $approve['this_current_approver']) {
                //有开启短信 并且需要通知审批人
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $data['department'] = $departname[$assets['departid']]['department'];
                $ToolMod = new ToolController();
                //通知审批人审批
                $where = [];
                $where['status'] = C('OPEN_STATUS');
                $where['is_delete'] = C('NO_STATUS');
                $where['username'] = $approve['this_current_approver'];
                $approve_user = $this->DB_get_one('user', 'telephone', $where);
                if ($settingData['doApprove']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                    $sms = $this->formatSmsContent($settingData['doApprove']['content'], $data);
                    $ToolMod->sendingSMS($approve_user['telephone'], $sms, $this->Controller, $add);
                }
            }
            //==========================================短信 END==========================================
            //==========================================微信 START==========================================
            if ($isOpenApprove) {
                //审批人
                $telephone = $this->DB_get_one('user', 'telephone,openid', ['username' => $approve['this_current_approver']]);
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $assets['department'] = $departname[$assets['departid']]['department'];
                //开启了审批 通知审批人
                if(C('USE_FEISHU') === 1){
                    //要显示的字段区域
                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备名称：**'.$assets['assets'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备编码：**'.$assets['assnum'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**所属科室：**'.$assets['department'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**申请原因：**'.$reason;
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '请尽快完成审批';
                    $feishu_fields[] = $fd;

                    $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                    $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                    $card_data['elements'][0]['tag'] = 'div';
                    $card_data['elements'][0]['fields'] = $feishu_fields;
                    $card_data['header']['template'] = 'blue';
                    $card_data['header']['title']['content'] = '设备外调审批申请提醒';
                    $card_data['header']['title']['tag'] = 'plain_text';

                    if ($telephone['openid']) {
                        $this->send_feishu_card_msg($telephone['openid'],$card_data);
                    }
                    //==========================================飞书 END==========================================
                }else{
                    $moduleModel = new ModuleModel();
                    $wx_status = $moduleModel->decide_wx_login();
                    if ($wx_status && $telephone['openid']) {
                        $typeName = '';

                        switch ($data['apply_type']) {
                            case C('OUTSIDE_CALL_OUT_TYPE'):
                                $typeName = C('OUTSIDE_CALL_OUT_TYPE_NAME');
                                break;
                            case C('OUTSIDE_DONATION_TYPE'):
                                $typeName = C('OUTSIDE_DONATION_TYPE_NAME');
                                break;
                            case C('OUTSIDE_OUTSIDE_SALE_TYPE'):
                                $typeName = C('OUTSIDE_OUTSIDE_SALE_TYPE_NAME');
                                break;
                        }

                        Weixin::instance()->sendMessage($telephone['openid'], '设备调度申请审批通知', [
                            'thing11' => $assets['department'],// 所属科室
                            'thing12' => $assets['assets'],// 设备名称
                            'const7'  => "外调（{$typeName}）",// 调度类型
                            'thing13' => $data['accept'],// 调度目的地
                            'const10' => '待审批',// 审批状态
                        ]);
                    }
                }
            }
            //==========================================微信 END==========================================
            //日志行为记录文字
            $log['assets'] = $assets['assets'];
            $log['accept'] = $accept;
            $text = getLogText('applyOutsideLogText', $log);
            $this->addLog('assets_outside', M()->getLastSql(), $text, $add);
            return array('status' => 1, 'msg' => '提交成功');
        } else {
            return array('status' => -1, 'msg' => '提交失败');
        }
    }

    //格式化短信内容
    public static function formatSmsContent($content, $data)
    {
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{department}", $data['department'], $content);
        $content = str_replace("{approve_status}", $data['approve_status'], $content);
        return $content;
    }
}
