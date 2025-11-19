<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/25
 * Time: 9:44
 */

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use App\Service\UserInfo\UserInfo;
use Common\Support\UrlGenerator;
use Common\Weixin\Weixin;

class RepairModel extends CommonModel
{
    protected $len = 100;
    protected $tablePrefix = 'sb_';
    protected $tableName = 'repair';
    protected $MODULE = 'Repair';
    protected $Controller = 'Repair';

    // 获取维修设备列表
    public function getAssetsLists()
    {
        $assets       = I('POST.assetsName');
        $serialnum    = I('post.serialnum');
        $assetsNum    = I('POST.assetsNum');
        $assetsCat    = I('POST.assetsCat');
        $assetsDep    = I('POST.assetsDep');
        $startDate    = I('POST.startDate');
        $endDate      = I('POST.endDate');
        $repairStatus = I('POST.repairStatus');
        $hospital_id  = I('POST.hospital_id');

        $order                = I('POST.order') ? I('POST.order') : 'DESC';
        $sort                 = I('POST.sort') ? I('POST.sort') : 'status';
        $limit                = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page                 = I('post.page') ? I('post.page') : 1;
        $offset               = ($page - 1) * $limit;
        $where['status'][0]   = 'NOT IN';
        $where['status'][1][] = C('ASSETS_STATUS_SCRAP');//已报废
        $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');//外调中
        $where['status'][1][] = C('ASSETS_STATUS_SCRAP_ON');//报废中
        $where['status'][1][] = C('ASSETS_STATUS_TRANSFER_ON');//转科中
        if (session('departid')) {
            $where['departid'][] = ['IN', session('departid')];
        } else {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        if ($hospital_id) {
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }
        //设备名称搜索
        if ($assets) {
            $where['assets'] = ['LIKE', '%' . $assets . '%'];
        }
        if ($serialnum) {
            //出厂编号搜索
            $where['serialnum'] = ['like', '%' . $serialnum . '%'];
        }
        //资产编码搜索
        if ($assetsNum) {
            $where['assnum'] = ['LIKE', "%$assetsNum%"];
        }
        //部门搜索
        if ($assetsDep != '') {
            $where['departid'][] = ['EQ', $assetsDep];
        }
        //启用时间--开始
        if ($startDate) {
            $where['opendate'][] = ['GT', getHandleTime(strtotime($startDate) - 1)];
        }
        //启用时间--结束
        if ($endDate) {
            $where['opendate'][] = ['LT', getHandleTime(strtotime($endDate) + 24 * 3600)];
        }
        //分类搜索
        if ($assetsCat) {
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['LIKE', "%$assetsCat%"];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['catid']       = ['IN', $catids];
        }
        if ($repairStatus == 1) {
            //未报修
            $where['status'] = ['EQ', C('ASSETS_STATUS_USE')];
        } elseif ($repairStatus == 2) {
            //已报修
            $where['status'] = ['EQ', C('ASSETS_STATUS_REPAIR')];
        }
        //搜索正在维修的设备
        $repair_where['status'][] = ['EGT', C('REPAIR_HAVE_REPAIRED')];
        $repair_where['status'][] = ['LT', C('REPAIR_ALREADY_ACCEPTED')];
        $repair                   = $this->DB_get_all('repair', 'assid,repid,status', $repair_where);
        $repair_assid_arr         = [];
        if ($repair) {
            foreach ($repair as &$repairValue) {
                $repair_assid_arr[$repairValue['assid']]['repid']  = $repairValue['repid'];
                $repair_assid_arr[$repairValue['assid']]['status'] = $repairValue['status'];
            }
        }
        $where['is_delete'] = C('NO_STATUS');
        $total              = $this->DB_get_count('assets_info', $where);
        $fileds             = 'assid,assets,catid,assnum,assorignum,model,departid,opendate,buy_price,lastrepairtime,guarantee_date,status';
        $asArr              = $this->DB_get_all('assets_info', $fileds, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$asArr) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        $catname    = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $assidstr = '';
        foreach ($asArr as &$one) {
            $assidstr              .= $one['assid'] . ',';
            $one['opendate']       = HandleEmptyNull($one['opendate']);
            $one['guarantee_date'] = HandleEmptyNull($one['guarantee_date']);
            $one['department']     = $departname[$one['departid']]['department'];
            $one['category']       = $catname[$one['catid']]['category'];
            $one['buy_price']      = (float)$one['buy_price'];
            $one['lastrepairtime'] = getHandleTime($one['lastrepairtime']);
            if (!$showPrice) {
                $one['buy_price'] = '***';
            }
        }
        //查询当前用户是否有报修权限
        $menuData = get_menu($this->MODULE, $this->Controller, 'addRepair');
        //var_dump($menuData);exit;
        //查询当前用户是否有验收权限
        $menucheck = get_menu($this->MODULE, $this->Controller, 'checkRepair');
        //按钮默认样式-小按钮
        //var_dump($asArr);exit;
        foreach ($asArr as &$value) {
            $value['operation'] = '<div class="layui-btn-group">';
            $value['operation'] .= $this->returnListLink('设备详情', C('SHOWASSETS_ACTION_URL'), 'showAssets',
                C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($value['status'] == C('ASSETS_STATUS_USE')) {
                //在用 可报修
                if ($menuData) {
                    $value['operation'] .= $this->returnListLink($menuData['actionname'], $menuData['actionurl'],
                        'addRepair', C('BTN_CURRENCY'));
                } else {
                    $value['operation'] .= $this->returnListLink('报修', '', '',
                        C('BTN_CURRENCY') . ' layui-btn-disabled');
                }
            } else {
                //报修中
                $value['repid'] = $repair_assid_arr[$value['assid']]['repid'];

                $detailsUrl = get_url() . '?action=showRepairDetails&assid=' . $value['assid'] . '&repid=' . $value['repid'];
                if ($repair_assid_arr[$value['assid']]['status'] == C('REPAIR_MAINTENANCE_COMPLETION')) {
                    //待验收
                    if ($menuData && in_array($value['departid'], explode(',', session('departid')))) {
                        //有权限并且 是所属管理科室科室
                        $value['operation'] .= $this->returnListLink($menucheck['actionname'], $menucheck['actionurl'],
                            'checkRepair', C('BTN_CURRENCY') . ' layui-btn-warm');
                    } else {
                        $value['operation'] .= $this->returnListLink('待验收', $detailsUrl, 'showDetails',
                            C('BTN_CURRENCY') . ' layui-btn-primary');
                    }
                } else {
                    $value['operation'] .= $this->returnListLink('已报修', $detailsUrl, 'showDetails',
                        C('BTN_CURRENCY') . ' layui-btn-normal');
                }
            }
            $value['operation'] .= '</div>';
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $asArr;
        return $result;
    }

    // 设备报修操作
    public function doAddRepair()
    {
        $breakdown = I('post.breakdown');
        $assid     = I('post.assid');
        $info      = $this->DB_get_one('assets_info', 'assid,assnum,status,departid', ['assid' => $assid]);
        $departids = session('departid');
        if (!in_array($info['departid'], explode(',', $departids))) {
            //不是自己管理科室的设备
            $result['status'] = -1;
            $result['msg']    = '对不起，' . $info['assnum'] . '设备不在您管理的科室范围内';
            return $result;
        }
        if ($info['status'] == C('ASSETS_STATUS_REPAIR')) {
            //设备维修中
            $result['status'] = -99;
            $result['msg']    = '设备维修中，请勿重复提交!';
            return $result;
        }
        $repWhere['assid']  = ['EQ', $assid];
        $repWhere['status'] = ['NEQ', C('REPAIR_ALREADY_ACCEPTED')];
        $data               = $this->DB_get_one('repair', '', $repWhere, 'repid desc');
        if ($data['approve_status'] == 2 && $data['status'] == 5) {
            $data = [];
        }
        //已撤单的可重新报修
        if ($data['status'] == -1) {
            $data = [];
        }
        $data['breakdown'] = $breakdown;
        if ('POST.filename') {
            $data['pic_url'] = trim(I('post.filename'), ',');
        }
        $data['assets']           = I('post.assets');
        $data['wxTapeAmr']        = I('post.wxTapeAmr');
        $data['assid']            = $assid;
        $data['assnum']           = I('post.assnum');
        $data['assprice']         = I('post.assprice');
        $data['factorynum']       = I('post.factorynum');
        $data['factory']          = I('post.factory');
        $data['model']            = I('post.model');
        $data['applicant_remark'] = I('post.remark');
        $data['repair_category']  = I('post.repair_category');
        $data['archives_num']     = trim(I('post.archives_num')) ? trim(I('post.archives_num')) : '';
        if (I('post.repair_person')) {
            $data['applicant'] = I('post.repair_person');
        } else {
            $data['applicant'] = session('username');
        }
        if (I('post.repair_phone')) {
            Vendor('SM4.SM4');
            $SM4 = new \SM4();
            //$data['applicant_tel'] = $SM4->encrypt(I('post.repair_phone'));
            $data['applicant_tel'] = I('post.repair_phone');
        } else {
            $data['applicant_tel'] = session('telephone');
        }
        if (I('post.repair_date')) {
            $data['applicant_time'] = strtotime(I('post.repair_date'));
        } else {
            $data['applicant_time'] = time();
        }
        $data['adddate']  = time();
        $data['editdate'] = time();
        $data['departid'] = I('post.departid');
        $data['status']   = C('REPAIR_HAVE_REPAIRED');
        $data['opendate'] = strtotime(I('POST.opendate'));
        //维修单来源 pc端 1 微信端0
        $data['from'] = I('post.from') ? I('post.from') : 0;
        //获取自动派工信息
        $engineer = $this->getEngineer($assid);
        if ($engineer) {
            $data['assign_time']     = time();
            $data['assign_engineer'] = $engineer['username'];
            $data['is_assign']       = C('YES_STATUS');
        }
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $wx = $baseSetting['repair']['repair_encoding_rules']['value'];
        if ($wx['prefix']) {
            $data['repnum'] = $wx['prefix'] . $data['repnum'];
        }
        //暂时未需要配置默认下划线 如果需要配置 这里读配置 todo
        $cut = '_';
        if ($cut) {
            $data['repnum'] .= $cut;
        }
        $data['repnum'] = $this->getOrgNumber('repair', 'repnum', $wx['prefix'], $cut);
        $add            = $this->insertData('repair', $data);
        if ($add) {
            $log['assnum'] = $data['assnum'];
            $text          = getLogText('addRepairLogText', $log);
            $this->addLog('repair', M()->getLastSql(), $text, $add);
            $departname = [];
            $catname    = [];
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($settingData) {
                //有开启短信
                $data['department'] = $departname[$data['departid']]['department'];
                $ToolMod            = new ToolController();
                if ($engineer) {
                    //该设备配置了自动派工
                    $acceptPhone = $engineer['telephone'];
                } else {
                    //无自动派工 通知派工人员派单
                    $UserData = $ToolMod->getUser('assigned', $data['departid']);
                    if ($settingData['assigned']['status'] == C('OPEN_STATUS') && $UserData) {
                        //通知派工员分配维修单 开启
                        $phone = $this->formatPhone($UserData);
                        $sms   = $this->formatSmsContent($settingData['assigned']['content'], $data);
                        $ToolMod->sendingSMS($phone, $sms, $this->Controller, $add);
                    }
                    //通知接单权限工程师接单
                    $UserData    = $ToolMod->getUser('accept', $data['departid']);
                    $acceptPhone = $this->formatPhone($UserData);
                }
                if ($settingData['applyRepair']['status'] == C('OPEN_STATUS') && $acceptPhone) {
                    //通知工程师接单短信 开启
                    $sms = $this->formatSmsContent($settingData['applyRepair']['content'], $data);
                    $ToolMod->sendingSMS($acceptPhone, $sms, $this->Controller, $add);
                }
            }
            //==========================================短信 END==========================================

            //查询报修单信息
            $info               = $this->DB_get_one_join('repair', 'A',
                'A.repnum,A.applicant_tel,B.assets,B.assnum,B.model,B.catid,B.departid',
                'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid', ['A.repid' => $add]);
            $info['department'] = $departname[$info['departid']]['department'];
            $info['category']   = $catname[$info['catid']]['category'];

            $moduleName     = 'Repair';
            $controllerName = 'Repair';
            $actionName     = 'accept';

            if (C('USE_FEISHU') === 1) {
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**报修人：**' . session('username');
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**维修单号：**' . $info['repnum'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备名称：**' . $info['assets'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备编码：**' . $info['assnum'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备型号：**' . $info['model'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**使用科室：**' . $info['department'];
                $feishu_fields[]       = $fd;

                //按钮区域
                $act['tag']  = 'button';
                $act['type'] = 'primary';
                $act['url']  = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Repair/accept?repid=' . $add;;//指定跳转地址
                $act['text']['tag']     = 'plain_text';
                $act['text']['content'] = '接单';
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
                $card_data['header']['title']['content'] = '你有一个新的报修待接单';
                $card_data['header']['title']['tag']     = 'plain_text';

                $toUser = $this->getToUser(session('userid'), $info['departid'], $moduleName, $controllerName,
                    $actionName);
                foreach ($toUser as $k => $v) {
                    $this->send_feishu_card_msg($v['openid'], $card_data);
                }
                //==========================================飞书 END==========================================
            } else {
                //==========================================微信 START==========================================
                $moduleModel = new ModuleModel();
                $wx_status   = $moduleModel->decide_wx_login();
                if ($wx_status) {
                    //发送微信消息给工程师（模板：报障通知）
                    if (C('USE_VUE_WECHAT_VERSION')) {
                        $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Repair/accept?repid=' . $add;
                    } else {
                        $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Repair/accept.html?repid=' . $add;
                    }

                    /** @var UserModel[] $users */
                    $users = $this->getToUser(session('userid'), $info['departid'], $moduleName, $controllerName, $actionName);
                    $openIds = array_column($users, 'openid');
                    $openIds = array_filter($openIds);
                    $openIds = array_unique($openIds);

                    $messageData = [
                        'thing3'             => $info['department'],// 科室
                        'thing6'             => $info['assets'],// 设备名称
                        'character_string12' => $info['assnum'],// 设备编码
                        'character_string35' => $info['repnum'],// 维修单号
                        'const17'            => '待接单',// 工单状态
                    ];

                    foreach ($openIds as $openId) {
                        Weixin::instance()->sendMessage($openId, '设备维修通知', $messageData, $redecturl);
                    }
                }
                //==========================================微信 END==========================================
            }
            $infodata['status'] = C('ASSETS_STATUS_REPAIR');
            $asModel            = new AssetsInfoModel();
            $asModel->updateData('assets_info', $infodata, ['assid' => $assid]);
            $result['status'] = 1;
            //记录报修信息到状态变更表
            $asModel->updateAssetsStatus($assid, $result['status'], $remark = '设备报修');
            $result['msg']   = '报修成功';
            $result['repid'] = $add;

            //推送一条推送消息到大屏幕
            $push_messages[] = [
                'type_action' => 'add',
                'type_name'   => C('SCREEN_REPAIR'),
                'assets'      => $data['assets'],
                'assnum'      => $data['assnum'],
                'department'  => $departname[$data['departid']]['department'],
                'remark'      => $data['breakdown'],
                'status'      => $data['status'],
                'status_name' => '已报修',
                'time'        => date('Y-m-d H:i'),
                'username'    => session('username') . '(' . session('telephone') . ')',
            ];
            push_messages($push_messages);
        } else {
            $result['status'] = -1;
            $result['msg']    = '报修失败';
        }
        return $result;
    }

    //获取确认转至报修设备列表
    public function confirmAddRepairList()
    {
        $limit       = I('POST.limit');
        $offset      = I('POST.offset') ? I('POST.offset') : 0;
        $order       = I('POST.order');
        $sort        = I('POST.sort');
        $patrolname  = I('POST.confirmAddRepairListPatrolname');
        $hospital_id = I('POST.hospital_id');
        if (I('POST.patrol_level')) {
            $patrol_level = I('POST.patrol_level');
        } else {
            $patrol_level = -1;
        }
        $assets     = I('POST.confirmAddRepairListAssets');
        $department = I('POST.confirmAddRepairListDepartment');
        $patroluser = I('POST.confirmAddRepairListPatroluser');
        $departname = [];
        $where      = '1';
        include APP_PATH . "Common/cache/department.cache.php";
        if ($department) {
            foreach ($departname as $key => $value) {
                if ($value['department'] == $department) {
                    $where .= ' AND C.departid=' . $key;
                    break;
                }
            }
        }
        if (!session('isSuper')) {
            $where .= " AND A.applicant='" . session('username') . "'";
        }

        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$order) {
            $order = 'desc';
        }
        if ($patroluser) {
            $where .= " AND A.patroluser = '$patroluser'";
        }
        if ($patrolname) {
            $where .= " AND D.patrolname='$patrolname'";
        }
        if ($assets) {
            $where .= " AND C.assets='$assets'";
        }
        if ($patrol_level != -1) {
            $where .= " AND B.patrol_level = " . $patrol_level;
        }

        if ($hospital_id) {
            $where .= ' AND C.hospital_id = ' . $hospital_id;
        } else {
            $where .= ' AND C.hospital_id IN( ' . session('manager_hospitalid') . ')';
        }
        $join[0]         = 'LEFT JOIN sb_patrol_plan_cycle AS B ON B.cycid=A.cycid';
        $join[1]         = 'LEFT JOIN sb_assets_info AS C ON C.assnum=A.assnum';
        $join[2]         = 'LEFT JOIN sb_patrol_plan AS D ON D.patrid=B.patrid';
        $fields          = 'C.departid,C.assets,A.confirmId,A.assnum,A.patroluser,A.abnormalText,A.cycid,A.status,
            B.patrol_level,D.patrolname,C.model';
        $total           = $this->DB_get_count_join('confirm_add_repair', 'A', $join, $where);
        $list            = $this->DB_get_all_join('confirm_add_repair', 'A', $fields, $join, $where, '',
            'A.' . $sort . ' ' . $order, $offset . "," . $limit);
        $menuData        = get_menu($this->MODULE, 'Repair', 'addRepair');
        $UnconfirmedHtml = $this->returnButtonLink('确认报修', C('ADMIN_NAME') . '/Repair/addRepair.html',
            'layui-btn layui-btn-xs', '', 'lay-event = confirmed');
        $confirmedHtml   = $this->returnButtonLink('已确认', C('ADMIN_NAME') . '/Repair/addRepair.html',
            'layui-btn layui-btn-xs layui-btn-primary', '', 'lay-event = confirmed');
        if (!$list) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        foreach ($list as &$one) {
            $one['department_name'] = $departname[$one['departid']]['department'];
            switch ($one['patrol_level']) {
                case C('PATROL_LEVEL_RC'):
                    $one['patrol_level'] = C('PATROL_LEVEL_NAME_RC');
                    break;
                case C('PATROL_LEVEL_DC'):
                    $one['patrol_level'] = C('PATROL_LEVEL_NAME_DC');
                    break;
                case C('PATROL_LEVEL_PM'):
                    $one['patrol_level'] = C('PATROL_LEVEL_NAME_PM');
                    break;
                default :
                    $one['patrol_level'] = '异常参数';
            }
            if ($menuData) {
                if ($one['status'] == C('SWITCH_REPAIR_UNCONFIRMED')) {
                    $one['operation'] = $UnconfirmedHtml;
                } else {
                    $one['operation'] = $confirmedHtml;
                }
            }
        }
        $result['total']  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result['rows']   = $list;
        return $result;
    }

    //确认转至报修操作
    public function confirmAddRepair()
    {
        $confirmId = I('POST.confirmId');
        $remark    = I('POST.remark');
        if ($confirmId) {
            $join = 'LEFT JOIN sb_assets_info AS B ON B.assnum=A.assnum';
            $data = $this->DB_get_one_join('confirm_add_repair', 'A',
                'A.applicant,A.status,A.assnum,A.abnormalText,B.status AS assets_status', $join,
                "confirmId=$confirmId");
            if ($data['assets_status'] != C('ASSETS_STATUS_USE')) {
                return ['status' => -90, 'msg' => '该设备已报修/报废'];
            }
            if ($data['status'] != 0) {
                return ['status' => -90, 'msg' => '该设备已确认过,请勿重复操作'];
            }
            if (!session('isSuper')) {
                if ($data['applicant'] != session('username')) {
                    return ['status' => -91, 'msg' => '您不是被使用名义的科室管理员'];
                }
            }
            $confirmAddData['status']      = 1;
            $confirmAddData['comfirmDate'] = time();
            $save                          = $this->updateData('confirm_add_repair', $confirmAddData,
                ['assnum' => $data['assnum']]);
            if ($save !== false) {
                $assetsData['status'] = 1;
                $this->updateData('assets_info', $assetsData, ['assnum' => $data['assnum']]);
                $this->addPatrolRepair($data['assnum'], $data['abnormalText'], $data['applicant'],
                    $confirmAddData['comfirmDate'], $remark);
                return ['status' => 1, 'msg' => '该设备成功转至报修'];
            } else {
                return ['status' => -99, 'msg' => '确认失败'];
            }
        } else {
            return ['status' => -99, 'msg' => '非法操作'];
        }
    }

    //获取派单列表
    public function dispatchingLists()
    {
        $order             = I('POST.order');
        $sort              = I('POST.sort');
        $assets            = I('POST.dispatchingListsAssetsName');
        $assetsDep         = I('POST.assetsDep');
        $assetsCat         = I('POST.assetsCat');
        $applicant         = I('POST.applicant');
        $startDate         = I('POST.startDate');
        $endDate           = I('POST.endDate');
        $res_startDate     = I('POST.response_startDate');
        $res_endDate       = I('POST.response_endDate');
        $repair_category   = I('POST.repair_category');
        $hospital_id       = I('POST.hospital_id');
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $where['A.status'] = ['EQ', C('REPAIR_HAVE_REPAIRED')];
        if (!session('departid')) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['A.departid'][] = ['IN', session('departid')];
        if (!$order) {
            $order = 'desc';
        }
        if ($assets) {
            $where['A.assets'] = ['LIKE', "%$assets%"];
        }
        if ($hospital_id) {
            $where['C.hospital_id'] = $hospital_id;
        } else {
            $where['C.hospital_id'] = session('current_hospitalid');
        }
        if ($assetsDep) {
            $where['C.departid'][] = ['EQ', $assetsDep];
        }
        if ($assetsCat) {
            //分类搜索
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['LIKE', "%$assetsCat%"];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['catid']       = ['IN', $catids];
        }
        if ($applicant) {
            $where['A.applicant'] = ['EQ', $applicant];
        }
        if ($repair_category) {
            $where['A.repair_category'] = $repair_category;
        }
        if ($startDate) {
            $where['applicant_time'][] = ['GT', strtotime($startDate) - 1];
        }
        if ($endDate) {
            $where['applicant_time'][] = ['LT', strtotime($endDate) + 24 * 3600];
        }
        if ($res_startDate) {
            $where['response_date'][] = ['GT', strtotime($res_startDate) - 1];
        }
        if ($res_endDate) {
            $where['response_date'][] = ['LT', strtotime($res_endDate) + 24 * 3600];
        }
        switch ($sort) {
            case 'repnum':
                $sort = 'A.repnum';
                break;
            case 'status':
                $sort = 'A.status';
                break;
            case 'applicant_time':
                $sort = 'A.applicant_time';
                break;
            case 'assets':
                $sort = 'convert(A.assets USING gbk) COLLATE gbk_chinese_ci';
                break;
            case 'model':
                $sort = 'A.model';
                break;
            default:
                $sort = 'A.repid ';
                break;
        }
        $join   = 'LEFT JOIN sb_assets_info AS C ON A.assid=C.assid';
        $fields = 'A.archives_num,A.assign_engineer,A.assign_time,A.assign,A.repid,A.model,A.applicant,A.applicant_time,A.breakdown,
        A.repnum,A.from,A.applicant_remark AS remark,C.departid,C.assets,C.assnum,C.assid,A.status,A.assign_time,A.response_date,A.is_assign';
        $total  = $this->DB_get_count_join('repair', 'A', $join, $where);
        $list   = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        if (!$list) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $menuData   = get_menu($this->MODULE, $this->Controller, 'assigned');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($list as &$one) {
            $one['department']     = $departname[$one['departid']]['department'];
            $one['responder']      = ($one['assign'] != 'system') ? $one['assign'] : '系统派单';
            $one['applicant_time'] = getHandleTime($one['applicant_time']);
            $one['assign_time']    = getHandleDate($one['assign_time']);
            $one['response_date']  = getHandleDate($one['response_date']);
            $one['operation']      = '<div class="layui-btn-group">';
            $one['operation']      .= $this->returnListLink('设备详情', C('SHOWASSETS_ACTION_URL'), 'showAssets',
                C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($menuData) {
                if ($one['from'] == 1) {
                    if ($one['is_assign'] == C('NO_STATUS')) {
                        $one['operation'] .= $this->returnListLink($menuData['actionname'], $menuData['actionurl'],
                            'assigned', C('BTN_CURRENCY'));
                    } else {
                        $one['operation'] .= $this->returnListLink('重新指派', $menuData['actionurl'], 'assigned',
                            C('BTN_CURRENCY'));
                    }
                } else {
                    $one['operation'] .= $this->returnListLink('指派', '', '',
                        C('BTN_CURRENCY') . ' layui-btn-disabled');
                }
            } else {
                $one['operation'] .= $this->returnListLink('指派', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
            }
            $one['operation'] .= '</div>';
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $list;
        return $result;
    }

    //派单操作
    public function doAssigned()
    {
        $repid    = I('post.repid');
        $engineer = I('post.engineer');
        $remark   = I('post.remark');
        $data     = $this->DB_get_one('repair',
            'assid,repnum,assets,assnum,departid,breakdown,applicant,applicant_time,applicant_tel',
            ['repid' => $repid]);
        $asArr    = $this->getAssetsBasic($data['assid']);
        if ($data) {
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $data['department'] = $departname[$data['departid']]['department'];
            if ($engineer) {
                $data['repid']           = $repid;
                $data['assign']          = session('username');
                $data['assign_tel']      = session('telephone');
                $data['assign_time']     = time();
                $data['editdate']        = time();
                $data['assign_engineer'] = $engineer;
                $data['is_assign']       = C('HAVE_STATUS');
                $data['assign_remark']   = $remark;
                $save                    = $this->updateData('repair', $data, ['repid' => $repid]);
                //日志行为记录文字
                $log['repnum']   = $data['repnum'];
                $log['engineer'] = $engineer;
                $text            = getLogText('assignRepairLogText', $log);
                $this->addLog('repair', M()->getLastSql(), $text, $save, '');
                if ($save !== false) {
                    //==========================================短信 START==========================================
                    $settingData = $this->checkSmsIsOpen($this->Controller);
                    //查询工程师电话openid
                    $enginfo = $this->DB_get_one('user', 'telephone,openid', ['username' => $engineer]);
                    if ($settingData) {
                        //有开启短信
                        $data['department'] = $asArr['department'];
                        $ToolMod            = new ToolController();
                        if ($settingData['applyRepair']['status'] == C('OPEN_STATUS') && $enginfo['telephone']) {
                            //通知工程师接单短信 开启
                            $sms = $this->formatSmsContent($settingData['applyRepair']['content'], $data);
                            $ToolMod->sendingSMS($enginfo['telephone'], $sms, $this->Controller, $repid);
                        }
                    }
                    //==========================================短信 END==========================================
                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**指派人：**' . session('username');
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**' . $data['assets'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**' . $data['assnum'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**使用科室：**' . $data['department'];
                        $feishu_fields[]       = $fd;

                        //按钮区域
                        $act['tag']             = 'button';
                        $act['type']            = 'orange';
                        $act['url']             = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Repair/accept?repid=' . $repid;
                        $act['text']['tag']     = 'plain_text';
                        $act['text']['content'] = '查看详情并处理';
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
                        $card_data['header']['title']['content'] = '你有一个新的维修派单等待处理';
                        $card_data['header']['title']['tag']     = 'plain_text';

                        $this->send_feishu_card_msg($enginfo['openid'], $card_data);
                        //==========================================飞书 END==========================================
                    } else {
                        $moduleModel = new ModuleModel();
                        $wx_status   = $moduleModel->decide_wx_login();
                        if ($wx_status) {
                            //发送微信消息给派单工程师
                            if (C('USE_VUE_WECHAT_VERSION')) {
                                $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Repair/accept?repid=' . $repid;
                            } else {
                                $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Repair/accept.html?repid=' . $repid;
                            }

                            if ($enginfo['openid']) {
                                Weixin::instance()->sendMessage($enginfo['openid'], '设备维修通知', [
                                    'thing3'             => $data['department'],// 科室
                                    'thing6'             => $data['assets'],// 设备名称
                                    'character_string12' => $data['assnum'],// 设备编码
                                    'character_string35' => $data['repnum'],// 维修单号
                                    'const17'            => '已派单，请接单',// 工单状态
                                ], $redecturl);
                            }
                        }
                    }
                    $result['status'] = 1;
                    $result['msg']    = '指派成功!';
                } else {
                    $result['status'] = -1;
                    $result['msg']    = '指派失败!';
                }
            } else {
                $result['status'] = -80;
                $result['msg']    = '请选择指派工程师!';
            }
        } else {
            $result['status'] = -999;
            $result['msg']    = '非法参数!';
        }
        return $result;
    }

    //设备接单列表
    public function ordersLists()
    {
        $order           = I('post.order');
        $sort            = I('post.sort');
        $assets          = I('post.ordersListsAssetsName');
        $meetStatus      = I('post.meetStatus');
        $assetsDep       = I('post.assetsDep');
        $assetsCat       = I('post.assetsCat');
        $applicant       = I('post.applicant');
        $repair_category = I('post.repair_category');
        $hospital_id     = I('post.hospital_id');
        $startDate       = I('post.startDate');
        $endDate         = I('post.endDate');
        $guarantee       = I('post.guarantee');
        $username        = session('username');
        $limit           = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page            = I('post.page') ? I('post.page') : 1;
        $offset          = ($page - 1) * $limit;
        if (!session('departid')) {
            $result['msg']  = '暂未分配管理科室！';
            $result['code'] = 400;
            return $result;
        }
        if ($hospital_id) {
            $where['C.hospital_id'] = $hospital_id;
        } else {
            $where['C.hospital_id'] = session('current_hospitalid');
        }
        $where['C.departid'][] = ['IN', session('departid')];

        //如果不是超级管理员 只显示自己接的单和未接单的和指派了自己的
        if (!session('isSuper')) {
            $where['_string'] .= "(is_assign=" . C('NOTHING_STATUS') . " OR ISNULL(assign_engineer) OR assign_engineer='$username') AND (ISNULL(response) OR response='$username')";
        }
        switch ($sort) {
            case 'repnum':
                $sort = 'A.repnum';
                break;
            case 'status':
                $sort = 'A.status';
                break;
            case 'applicant_time':
                $sort = 'A.applicant_time';
                break;
            case 'assets':
                $sort = 'convert(A.assets USING gbk) COLLATE gbk_chinese_ci';
                break;
            case 'model':
                $sort = 'A.model';
                break;
            default:
                $sort = 'A.repid ';
                break;
        }
        if (!$order) {
            $order = 'desc';
        }
        if ($assets) {
            $where['A.assets'] = ['LIKE', '%' . $assets . '%'];
        }
        if ($assetsDep) {
            $where['A.departid'] = $assetsDep;
        }
        //分类搜索
        if ($assetsCat) {
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['like', "%$assetsCat%"];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['C.catid']     = ['IN', $catids];
        }
        if ($applicant) {
            $where['A.applicant'] = ['EQ', $applicant];
        }
        //启用时间--开始
        if ($startDate) {
            $where['A.applicant_time'][] = ['GT', strtotime($startDate) - 1];
        }
        //启用时间--结束
        if ($endDate) {
            $where['A.applicant_time'][] = ['LT', strtotime($endDate) + 24 * 3600];
        }
        if ($repair_category) {
            $where['A.repair_category'] = $repair_category;
        }
        if ($meetStatus == 1) {
            $where['A.status'] = ['EQ', $meetStatus];
        } elseif ($meetStatus == 2) {
            $where['A.status'] = ['BETWEEN', [C('REPAIR_HAVE_REPAIRED'), C('REPAIR_MAINTENANCE_COMPLETION')]];
        } else {
            $where['A.status'] = ['NEQ', C('REPAIR_ALREADY_ACCEPTED')];
        }
        if ($guarantee == 1) {
            $AssetsInsuranceModel = new AssetsInsuranceModel();
            $GuaranteeData        = $AssetsInsuranceModel->returnGuaranteeData();
            $where['A.assid']     = ['IN', $GuaranteeData];
        } elseif ($guarantee == 2) {
            $AssetsInsuranceModel = new AssetsInsuranceModel();
            $GuaranteeData        = $AssetsInsuranceModel->returnGuaranteeData();
            $where['A.assid']     = ['NOT IN', $GuaranteeData];
        }
        $join   = 'LEFT JOIN sb_assets_info AS C ON A.assid=C.assid';
        $fields = 'A.archives_num,A.status,A.approve_status,A.expect_price,A.assign_time,A.assign,A.repid,
        A.assnum,A.model,A.applicant,A.applicant_time,A.response,A.repnum,A.breakdown,A.applicant_remark AS remark,
        A.response_date,A.expect_arrive,C.departid,C.assets,C.assid';
        $total  = $this->DB_get_count_join('repair', 'A', $join, $where);
        $list   = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '',
            'A.status asc,' . $sort . ' ' . $order, $offset . "," . $limit);
        if (!$list) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $list       = $this->getAcceptHtml($list);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($list as &$one) {
            $one['department']     = $departname[$one['departid']]['department'];
            $one['responder']      = ($one['assign'] != 'system') ? $one['assign'] : '系统派单';
            $one['expect_arrive']  = getHandleDate($one['response_date'] + ($one['expect_arrive'] * 60));
            $one['applicant_time'] = getHandleTime($one['applicant_time']);
            $one['response_date']  = getHandleDate($one['response_date']);
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $list;
        return $result;
    }

    //接单操作
    public function doAccept()
    {
        $repid  = I('post.repid');
        $assnum = I('post.assnum');
        // var_dump($repid,$assnum);exit;
        $datafield = 'assid,repnum,response,response_date,status,assign,department,assnum,response_date,departid,
        applicant_tel,applicant,applicant_time,approve_status,assign_tel,assign_engineer';
        if ($repid) {
            $data = $this->DB_get_one('repair', $datafield, ['repid' => $repid]);
        }else{
            $asInfo   = $this->DB_get_one('assets_info', '',
                [
                    'assnum'      => $assnum,
                    'hospital_id' => UserInfo::getInstance()->get('current_hospitalid'),
                    'is_delete'   => '0',
                ]);
            if (!$asInfo) {
                $asInfo = $this->DB_get_one('assets_info', '',
                    [
                        'assorignum'  => $assnum,
                        'hospital_id' => UserInfo::getInstance()->get('current_hospitalid'),
                        'is_delete'   => '0',
                    ]);
            }
            if (!$asInfo) {
                $asInfo = $this->DB_get_one('assets_info', '',
                    [
                        'assorignum_spare' => $assnum,
                        'hospital_id'      => UserInfo::getInstance()->get('current_hospitalid'),
                        'is_delete'        => '0',
                    ]);
            }
            $data      = $this->DB_get_one('repair', $datafield, ['assid' => $asInfo['assid']]);
        }

        if (!$data) {
            if ($assnum) {
                $datafield = 'assid,repnum,response,response_date,status,assign,department,assnum,response_date,departid,
                applicant_tel,applicant,applicant_time,approve_status,assign_tel,assign_engineer,repid';
                $data      = $this->DB_get_one('repair', $datafield, ['assnum' => $assnum, 'status' => 2]);
                if (!$data) {
                    $result['status'] = -1;
                    $result['msg']    = '维修单不存在';
                    return $result;
                } else {
                    $repid = $data['repid'];
                }
            } else {
                $result['status'] = -1;
                $result['msg']    = '维修单不存在';
                return $result;
            }
        }
        if ($data['status'] >= C('REPAIR_HAVE_OVERHAULED')) {
            //已检修/配件待出库
            $result['status'] = -1;
            $result['msg']    = '维修单已检修，请勿重复操作';
            return $result;
        }
        //查询设备所属医院
        $assInfo             = $this->DB_get_one('assets_info', 'hospital_id', ['assid' => $data['assid']]);
        $data['hospital_id'] = $assInfo['hospital_id'];
        if ($data['status'] == C('REPAIR_HAVE_REPAIRED')) {
            //接单操作
            if (session('isSuper') != 1 && $data['assign_engineer'] != null && $data['assign_engineer'] != session('username')) {
                return ['status' => -1, 'msg' => '这台设备已经被指派给另一个工程师'];
            }

            $result = $this->accept($data, $repid);
        } elseif ($data['status'] == C('REPAIR_RECEIPT')) {
            //检修操作
            $tmp_save = I('post.tmp_save');
            if ($tmp_save) {
                //暂时保存
                $result = $this->tmp_save_overhaul($data, $repid);
            } else {
                //确定检修
                //把暂存时候的保存的数据清除掉
                $this->deleteData('repair_fault', ['repid' => $repid]);
                $this->deleteData('repair_parts', ['repid' => $repid]);
                $this->deleteData('repair_file', ['repid' => $repid]);
                $this->deleteData('repair_offer_company', ['repid' => $repid]);
                $is_scene = I('POST.is_scene');
                if ($is_scene == C('YES_STATUS')) {
                    //现场解决
                    $result = $this->sceneEnd($data, $repid);
                } else {
                    //现场不能解决
                    $result = $this->notSceneEnd($data, $repid);
                }
            }
        } else {
            $result['status'] = -1;
            $result['msg']    = '已被接单';
            return $result;
        }
        return $result;
    }

    //检修 现场解决操作
    public function sceneEnd($data, $repid)
    {
        //查询设备所属医院
        $assInfo    = $this->DB_get_one('assets_info', 'assets,assnum,model,departid,hospital_id',
            ['assid' => $data['assid']]);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assInfo['department'] = $departname[$assInfo['departid']]['department'];

        $this->checkstatus(judgeEmpty(I('POST.dispose_detail')), '请输入处理详情');
        $data['dispose_detail'] = I('POST.dispose_detail');
        $data['is_scene']       = C('YES_STATUS');
        $data['status']         = C('REPAIR_MAINTENANCE_COMPLETION');
        $data['repair_type']    = C('REPAIR_TYPE_IS_SCENE');//3现场解决
        $data['editdate']       = time();
        $data['overdate']       = time();
        $data['engineer_time']  = $data['response_date'];
        $data['overhauldate']   = time();
        $data['fault_type']     = I('post.type');
        $problem                = I('post.problem');
        $parts_result           = $this->get_insert_parts($repid);
        $this->checkstatus(judgeEmpty($problem), '请至少选择一个故障问题');
        $problem     = explode('|', $problem);
        $problemData = [];
        $ftypes      = [];
        foreach ($problem as $key => $one) {
            $problemData[$key]['repid']            = $repid;
            $fault                                 = explode('-', $one);
            $problemData[$key]['fault_type_id']    = $fault[0];
            $problemData[$key]['fault_problem_id'] = $fault[1];
            if (!in_array($fault[0], $ftypes)) {
                $ftypes[] = $fault[0];
            }
        }
        $data['fault_type'] = implode(',', $ftypes);
        $data['engineer']   = session('username');
        $data['editdate']   = time();
        if (I('post.expect_time')) {
            $data['expect_time'] = strtotime(I('post.expect_time'));
        }
        $data['engineer_time'] = time();
        $data['working_hours'] = timediff($data['response_date'], time());
        $save                  = $this->updateData('repair', $data, ['repid' => $repid]);
        if ($save !== false) {
            $this->insertDataALL('repair_fault', $problemData);
            $this->addLog('repair', M()->getLastSql(), '维修编号为' . $data['repnum'] . '的设备已现场维修完成', $repid,
                'overhaul');
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($parts_result['data']) {
                //配件申请入库
                $this->add_parts($parts_result, $repid, $data['assid']);
                //==========================================短信 START==========================================
                if ($settingData) {
                    //有开启短信
                    $departname = [];
                    include APP_PATH . "Common/cache/department.cache.php";
                    $data['department'] = $departname[$data['departid']]['department'];
                    $ToolMod            = new ToolController();
                    //获取有出库权限的用户
                    $UserData = $ToolMod->getUser('partsOutWare', $data['departid']);
                    if ($settingData['repairPartsOutApply']['status'] == C('OPEN_STATUS') && $UserData) {
                        //通知库管出库 开启
                        $phone = $this->formatPhone($UserData);
                        $sms   = $this->formatSmsContent($settingData['repairPartsOutApply']['content'], $data);
                        $ToolMod->sendingSMS($phone, $sms, $this->Controller, $repid);
                    }
                }
                //==========================================短信 END==========================================
                //配件出库
                $moduleName     = 'Repair';
                $controllerName = 'RepairParts';
                $actionName     = 'partsOutWare';
                if (C('USE_FEISHU') === 1) {
                    //==========================================飞书 START========================================
                    //要显示的字段区域
                    $fd['is_short']        = false;//是否并排布局
                    $fd['text']['tag']     = 'lark_md';
                    $fd['text']['content'] = '**申请人：**' . session('username');
                    $feishu_fields[]       = $fd;

                    $fd['is_short']        = false;//是否并排布局
                    $fd['text']['tag']     = 'lark_md';
                    $fd['text']['content'] = '**设备名称：**' . $assInfo['assets'];
                    $feishu_fields[]       = $fd;

                    $fd['is_short']        = false;//是否并排布局
                    $fd['text']['tag']     = 'lark_md';
                    $fd['text']['content'] = '**设备编码：**' . $assInfo['assnum'];
                    $feishu_fields[]       = $fd;

                    $fd['is_short']        = false;//是否并排布局
                    $fd['text']['tag']     = 'lark_md';
                    $fd['text']['content'] = '**设备型号：**' . $assInfo['model'];
                    $feishu_fields[]       = $fd;

                    $fd['is_short']        = false;//是否并排布局
                    $fd['text']['tag']     = 'lark_md';
                    $fd['text']['content'] = '**使用科室：**' . $assInfo['department'];
                    $feishu_fields[]       = $fd;

                    //按钮区域
                    $act['tag']  = 'button';
                    $act['type'] = 'primary';
                    $act['url']  = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/RepairParts/partsOutWare?repid=' . $repid;;
                    $act['text']['tag']     = 'plain_text';
                    $act['text']['content'] = '查看详情';
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
                    $card_data['header']['title']['content'] = '收到一个维修配件出库申请';
                    $card_data['header']['title']['tag']     = 'plain_text';

                    $toUser = $this->getToUser(session('userid'), $assInfo['departid'], $moduleName, $controllerName,
                        $actionName);
                    foreach ($toUser as $k => $v) {
                        $this->send_feishu_card_msg($v['openid'], $card_data);
                    }
                    //==========================================飞书 END==========================================
                } else {
                    //发送微信消息给配件出库人进行配件出库操作
                    //==========================================微信 START==========================================
                    $moduleModel = new ModuleModel();
                    $wx_status   = $moduleModel->decide_wx_login();
                    if ($wx_status) {
                        if (C('USE_VUE_WECHAT_VERSION')) {
                            $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/RepairParts/partsOutWare?repid=' . $repid;
                        } else {
                            $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/RepairParts/partsOutWare.html?repid=' . $repid;
                        }
                        /** @var UserModel[] $users */
                        $users = $this->getToUser(session('userid'), $assInfo['departid'], $moduleName, $controllerName, $actionName);
                        $openIds = array_column($users, 'openid');
                        $openIds = array_filter($openIds);
                        $openIds = array_unique($openIds);

                        $messageData = [
                            'thing6'            => $assInfo['department'],// 设备科室
                            'thing7'            => $assInfo['assets'],// 设备名称
                            'character_string8' => $assInfo['assnum'],// 设备编码
                            'character_string4' => $data['repnum'],// 维修单号
                            'const5'            => '待出库',// 审批状态
                        ];

                        foreach ($openIds as $openId) {
                            Weixin::instance()->sendMessage($openId, '配件出库审批通知', $messageData, $redecturl);
                        }
                    }
                    //==========================================微信 END==========================================
                }
            }
            //==========================================短信 START==========================================
            if ($settingData) {
                //有开启短信
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $data['department'] = $departname[$data['departid']]['department'];
                $ToolMod            = new ToolController();
                $UserData           = $ToolMod->getUser('checkRepair', $data['departid']);
                if ($settingData['checkRepair']['status'] == C('OPEN_STATUS') && $UserData) {
                    //通知报修用户验收 开启
                    $phone = $this->formatPhone($UserData);
                    $sms   = $this->formatSmsContent($settingData['checkRepair']['content'], $data);
                    $ToolMod->sendingSMS($phone, $sms, $this->Controller, $repid);
                }
            }
            //==========================================短信 END==========================================
            //查询报修人员openid
            $applicant = $this->DB_get_one('user', 'telephone,openid', ['username' => $data['applicant']]);
            if (C('USE_FEISHU') === 1) {
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**维修单号：**' . $data['repnum'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备名称：**' . $assInfo['assets'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备编码：**' . $assInfo['assnum'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**使用科室：**' . $assInfo['department'];
                $feishu_fields[]       = $fd;

                //按钮区域
                $act['tag']             = 'button';
                $act['type']            = 'orange';
                $act['url']             = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Repair/checkRepair?repid=' . $repid;
                $act['text']['tag']     = 'plain_text';
                $act['text']['content'] = '查看详情并验收';
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
                $card_data['header']['title']['content'] = '设备修复验收通知';
                $card_data['header']['title']['tag']     = 'plain_text';

                $this->send_feishu_card_msg($applicant['openid'], $card_data);
                //==========================================飞书 END==========================================
            } else {
                //==========================================微信 START==========================================
                $moduleModel = new ModuleModel();
                $wx_status   = $moduleModel->decide_wx_login();
                if ($wx_status) {
                    //发送微信消息给报修人进行验收操作
                    if (C('USE_VUE_WECHAT_VERSION')) {
                        $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Repair/checkRepair?repid=' . $repid;
                    } else {
                        $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Repair/checkRepair.html?repid=' . $repid;
                    }

                    if ($applicant['openid']) {
                        Weixin::instance()->sendMessage($applicant['openid'], '设备维修通知', [
                            'thing3'             => $assInfo['department'],// 科室
                            'thing6'             => $assInfo['assets'],// 设备名称
                            'character_string12' => $assInfo['assnum'],// 设备编码
                            'character_string35' => $data['repnum'],// 维修单号
                            'const17'            => '已修复，请验收',// 工单状态
                        ], $redecturl);
                    }
                }
                //==========================================微信 END==========================================
            }
            $menuData = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
            if ($menuData) {
                //拥有上传文件的权限
                $this->addRepairFile($repid, ACTION_NAME);
            }
            $result['status'] = 1;
            $result['msg']    = '提交成功';

            //推送一条推送消息到大屏幕
            $push_messages[] = [
                'type_action' => 'edit',
                'type_name'   => C('SCREEN_REPAIR'),
                'assets'      => $assInfo['assets'],
                'assnum'      => $assInfo['assnum'],
                'department'  => $departname[$assInfo['departid']]['department'],
                'remark'      => $data['dispose_detail'],
                'status'      => $data['status'],
                'status_name' => '待验收',
                'time'        => date('Y-m-d H:i'),
                'username'    => session('username') . '(' . session('telephone') . ')',
            ];
            push_messages($push_messages);
        } else {
            $result['status'] = -1;
            $result['msg']    = '提交失败!';
        }
        return $result;
    }

    //检修 现场不能解决
    public function notSceneEnd($data, $repid)
    {
        //查询设备所属医院
        $assInfo    = $this->DB_get_one('assets_info', 'assets,assnum,model,departid,hospital_id',
            ['assid' => $data['assid']]);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assInfo['department'] = $departname[$assInfo['departid']]['department'];

        $data['repair_type']   = I('post.repair_type');
        $data['fault_type']    = I('post.type');
        $data['overhauldate']  = time();
        $data['editdate']      = time();
        $data['repair_remark'] = I('post.repair_remark');
        $data['is_scene']      = C('NO_STATUS');
        $problem               = I('post.problem');
        $this->checkstatus(judgeEmpty($problem), '请至少选择一个故障问题');
        $problem     = explode('|', $problem);
        $problemData = [];
        $ftypes      = [];
        foreach ($problem as $key => $one) {
            $problemData[$key]['repid']            = $repid;
            $fault                                 = explode('-', $one);
            $problemData[$key]['fault_type_id']    = $fault[0];
            $problemData[$key]['fault_problem_id'] = $fault[1];
            if (!in_array($fault[0], $ftypes)) {
                $ftypes[] = $fault[0];
            }
        }
        $data['fault_type'] = implode(',', $ftypes);
        if (I('post.expect_time')) {
            $data['expect_time'] = strtotime(I('post.expect_time'));
        }
        $IS_OPEN_OFFER = C('DO_STATUS');//可操作
        $doOfferMenu   = get_menu($this->MODULE, $this->Controller, 'doOffer');
        if (C('IS_OPEN_OFFER')) {
            //开启了统一报价
            if (!$doOfferMenu) {
                $IS_OPEN_OFFER = C('NOT_DO_STATUS');
            }
        }
        //审审批状态
        $isOpenApprove = $this->checkApproveIsOpen(C('REPAIR_APPROVE'), $data['hospital_id']);
        //维保厂家
        if ($data['repair_type'] == C('REPAIR_TYPE_IS_GUARANTEE')) {
            $data['salesman_name']  = trim(I('POST.salesman_name'));
            $data['salesman_phone'] = trim(I('POST.salesman_phone'));
            $data['guarantee_id']   = trim(I('POST.guarantee_id'));
            $data['guarantee_name'] = trim(I('POST.guarantee_name'));
            $data['status']         = C('REPAIR_MAINTENANCE');//维修中
        }
        //自修
        $parts_result = [];
        if ($data['repair_type'] == C('REPAIR_TYPE_IS_STUDY')) {
            //记录配件信息
            $parts_result = $this->get_insert_parts($repid);
            if ($parts_result['data']) {
                //有新增配件 状态 已检修(待出库)
                $data['status'] = C('REPAIR_HAVE_OVERHAULED');
            } else {
                //无新增配件
                $data['status'] = C('REPAIR_MAINTENANCE');//维修中
            }
        }
        //第三方
        $company_result = [];
        $repairContract = [];
        $approve        = [];
        if ($data['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')) {
            if (!$doOfferMenu) {
                $IS_OPEN_OFFER = C('NOT_DO_STATUS');
            }
            //记录报价公司信息
            $company_result = $this->insertOfferCompany($repid);
            if ($company_result['data']) {
                foreach ($company_result['data'] as $company_val) {
                    if ($company_val['last_decisioin'] == C('YES_STATUS')) {
                        $repairContract['hospital_id']       = $data['hospital_id'];
                        $repairContract['repid']             = $repid;
                        $repairContract['supplier_id']       = $company_val['offer_company_id'];
                        $repairContract['supplier_name']     = $company_val['offer_company'];
                        $repairContract['supplier_contacts'] = $company_val['offer_contacts'];
                        $repairContract['supplier_phone']    = $company_val['telphone'];
                        $repairContract['contract_amount']   = $company_val['total_price'];
                        $repairContract['add_user']          = $company_val['add_user'];
                        $repairContract['add_time']          = getHandleDate(time());
                        $count                               = $this->DB_get_count('repair_contract');
                        $repairContract['contract_num']      = 'WXHT' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
                        $company_result['totalPrice']        = $company_val['total_price'];
                    }
                }
                if ($IS_OPEN_OFFER == C('DO_STATUS')) {
                    if ($isOpenApprove) {
                        //查询是否已设置审批流程
                        $isSetProcess = $this->checkApproveIsSetProcess(C('REPAIR_APPROVE'), $data['hospital_id']);
                        if (!$isSetProcess) {
                            die(json_encode(['status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！']));
                        }
                        //需要审批，获取审批人
                        $approve_process_user = $this->get_approve_process($company_result['totalPrice'],
                            C('REPAIR_APPROVE'), $data['hospital_id']);
                        //并且获取下次审批人
                        $approve = $this->check_approve_process($data['departid'], $approve_process_user, 1);
                        if ($approve['all_approver'] == '') {
                            //不在审核范围内 不需要审批
                            $data['approve_status'] = C('STATUS_APPROE_UNWANTED');
                            $data['status']         = C('REPAIR_MAINTENANCE');//维修中
                        } else {
                            //默认为未审核
                            $repairContract                = [];
                            $data['current_approver']      = $approve['current_approver'];
                            $data['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                            $data['all_approver']          = $approve['all_approver'];
                            $data['approve_status']        = C('APPROVE_STATUS');
                            $data['status']                = C('REPAIR_AUDIT');//审核中
                        }
                    } else {
                        //未开启维修审批，不需要审批
                        $data['status'] = C('REPAIR_MAINTENANCE');//维修中
                    }
                } else {
                    $data['status'] = C('REPAIR_QUOTATION');//报价中
                }
            } else {
                $data['status'] = C('REPAIR_QUOTATION');//报价中
            }
        }
        $save = $this->updateData('repair', $data, ['repid' => $repid]);
        if ($save !== false) {
            $log['repnum'] = $data['repnum'];
            $text          = getLogText('overhaulRepairText', $log);
            $this->addLog('repair', M()->getLastSql(), $text, $repid, 'overhaul');
            //记录故障问题
            $this->insertDataALL('repair_fault', $problemData);
            $settingData = $this->checkSmsIsOpen($this->Controller);
            //自修
            if ($data['repair_type'] == C('REPAIR_TYPE_IS_STUDY')) {
                if ($parts_result['data']) {
                    //配件申请入库
                    $this->add_parts($parts_result, $repid, $data['assid']);
                    //==========================================短信 START==========================================
                    if ($settingData) {
                        //有开启短信
                        $departname = [];
                        include APP_PATH . "Common/cache/department.cache.php";
                        $data['department'] = $departname[$data['departid']]['department'];
                        $ToolMod            = new ToolController();
                        //获取有出库权限的用户
                        $UserData = $ToolMod->getUser('partsOutWare', $data['departid']);
                        if ($settingData['repairPartsOutApply']['status'] == C('OPEN_STATUS') && $UserData) {
                            //通知库管出库 开启
                            $phone = $this->formatPhone($UserData);
                            $sms   = $this->formatSmsContent($settingData['repairPartsOutApply']['content'], $data);
                            $ToolMod->sendingSMS($phone, $sms, $this->Controller, $repid);
                        }
                    }
                    //==========================================短信 END==========================================
                    //配件出库
                    $moduleName     = 'Repair';
                    $controllerName = 'RepairParts';
                    $actionName     = 'partsOutWare';
                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**申请人：**' . session('username');
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**' . $assInfo['assets'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**' . $assInfo['assnum'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备型号：**' . $assInfo['model'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**使用科室：**' . $assInfo['department'];
                        $feishu_fields[]       = $fd;

                        //按钮区域
                        $act['tag']  = 'button';
                        $act['type'] = 'primary';
                        $act['url']  = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/RepairParts/partsOutWare?repid=' . $repid;;
                        $act['text']['tag']     = 'plain_text';
                        $act['text']['content'] = '查看详情';
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
                        $card_data['header']['title']['content'] = '收到一个维修配件出库申请';
                        $card_data['header']['title']['tag']     = 'plain_text';

                        $toUser = $this->getToUser(session('userid'), $assInfo['departid'], $moduleName,
                            $controllerName, $actionName);
                        foreach ($toUser as $k => $v) {
                            $this->send_feishu_card_msg($v['openid'], $card_data);
                        }
                        //==========================================飞书 END==========================================
                    } else {
                        //发送微信消息给配件出库人进行配件出库操作
                        //==========================================微信 START==========================================
                        $moduleModel = new ModuleModel();
                        $wx_status   = $moduleModel->decide_wx_login();
                        if ($wx_status) {
                            if (C('USE_VUE_WECHAT_VERSION')) {
                                $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/RepairParts/partsOutWare?repid=' . $repid;
                            } else {
                                $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/RepairParts/partsOutWare.html?repid=' . $repid;
                            }
                            /** @var UserModel[] $users */
                            $users = $this->getToUser(session('userid'), $assInfo['departid'], $moduleName, $controllerName, $actionName);
                            $openIds = array_column($users, 'openid');
                            $openIds = array_filter($openIds);
                            $openIds = array_unique($openIds);

                            $messageData = [
                                'thing6'            => $assInfo['department'],// 设备科室
                                'thing7'            => $assInfo['assets'],// 设备名称
                                'character_string8' => $assInfo['assnum'],// 设备编码
                                'character_string4' => $data['repnum'],// 维修单号
                                'const5'            => '待出库',// 审批状态
                            ];

                            foreach ($openIds as $openId) {
                                Weixin::instance()->sendMessage($openId, '配件出库审批通知', $messageData, $redecturl);
                            }
                        }
                        //==========================================微信 END==========================================
                    }
                    //推送一条推送消息到大屏幕
                    $push_messages[] = [
                        'type_action' => 'edit',
                        'type_name'   => C('SCREEN_REPAIR'),
                        'assets'      => $assInfo['assets'],
                        'assnum'      => $assInfo['assnum'],
                        'department'  => $departname[$assInfo['departid']]['department'],
                        'remark'      => $data['repair_remark'],
                        'status'      => $data['status'],
                        'status_name' => '已检修',
                        'time'        => date('Y-m-d H:i'),
                        'username'    => session('username') . '(' . session('telephone') . ')',
                    ];
                    push_messages($push_messages);
                }
            }
            //第三方
            if ($data['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')) {
                if ($company_result['data']) {
                    //第三方入库
                    $this->add_company($company_result, $repid);
                }
                if ($repairContract) {
                    //有权限选择最终厂家 并且不需要审批  则生产一条待确认的合同
                    $this->insertData('repair_contract', $repairContract);
                }
                //==========================================短信 START==========================================
                if ($settingData) {
                    //有开启短信
                    $departname = [];
                    include APP_PATH . "Common/cache/department.cache.php";
                    $data['department'] = $departname[$data['departid']]['department'];
                    $ToolMod            = new ToolController();
                    switch ($data['status']) {
                        case C('REPAIR_QUOTATION'):
                            //报价中 通知报价员选择最终
                            //获取有报价权限的用户
                            $UserData = $ToolMod->getUser('doOffer', $data['departid']);
                            if ($settingData['repairOffer']['status'] == C('OPEN_STATUS') && $UserData) {
                                //通知库管出库 开启
                                $phone = $this->formatPhone($UserData);
                                $sms   = $this->formatSmsContent($settingData['repairOffer']['content'], $data);
                                $ToolMod->sendingSMS($phone, $sms, $this->Controller, $repid);
                            }
                            break;
                        case C('REPAIR_AUDIT'):
                            //审批中
                            if ($approve['this_current_approver']) {
                                //通知审批人审批
                                $where              = [];
                                $where['status']    = C('OPEN_STATUS');
                                $where['is_delete'] = C('NO_STATUS');
                                $where['username']  = $approve['this_current_approver'];
                                $approve_user       = $this->DB_get_one('user', 'telephone', $where);
                                if ($settingData['doApprove']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                                    $sms = $this->formatSmsContent($settingData['doApprove']['content'], $data);
                                    $ToolMod->sendingSMS($approve_user['telephone'], $sms, $this->Controller, $repid);
                                }
                            }
                            //推送一条推送消息到大屏幕
                            $push_messages[] = [
                                'type_action' => 'edit',
                                'type_name'   => C('SCREEN_REPAIR'),
                                'assets'      => $assInfo['assets'],
                                'assnum'      => $assInfo['assnum'],
                                'department'  => $departname[$assInfo['departid']]['department'],
                                'remark'      => $data['repair_remark'],
                                'status'      => $data['status'],
                                'status_name' => '待审批',
                                'time'        => date('Y-m-d H:i'),
                                'username'    => session('username') . '(' . session('telephone') . ')',
                            ];
                            push_messages($push_messages);
                            break;
                    }
                }
                //==========================================短信 END==========================================

                if ((new ModuleModel())->decide_wx_login()) {
                    switch ($data['status']) {
                        case C('REPAIR_QUOTATION'):
                            // 报价中
                            // 发送待报价提醒给报价权限者
                            /** @var UserModel[] $users */
                            $users = $this->getToUser(session('userid'), $assInfo['departid'], 'Repair', 'Repair', 'doOffer');

                            $openIds = array_column($users, 'openid');
                            $openIds = array_filter($openIds);
                            $openIds = array_unique($openIds);

                            $messageData = [
                                'thing3'             => $departname[$assInfo['departid']]['department'],// 科室
                                'thing6'             => $assInfo['assets'],// 设备名称
                                'character_string12' => $assInfo['assnum'],// 设备编码
                                'character_string35' => $data['repnum'],// 维修单号
                                'const17'            => '已检修，请报价',// 工单状态
                            ];

                            foreach ($openIds as $openId) {
                                Weixin::instance()->sendMessage($openId, '设备维修通知', $messageData);
                            }
                            break;
                        case C('REPAIR_AUDIT'):
                            // 审核中
                            // 发送待审批提醒给审批人
                            $user = UserModel::getUserByUsername($approve['this_current_approver'], ['openid']);
                            $redirectUrl = UrlGenerator::instance()->to('/Repair/addApprove', ['repid' => $repid]);

                            Weixin::instance()->sendMessage($user['openid'], '设备维修通知', [
                                'thing3'             => $departname[$assInfo['departid']]['department'],// 科室
                                'thing6'             => $assInfo['assets'],// 设备名称
                                'character_string12' => $assInfo['assnum'],// 设备编码
                                'character_string35' => $data['repnum'],// 维修单号
                                'const17'            => '待审批',// 工单状态
                            ], $redirectUrl);
                            break;
                    }
                }
            }
            $menuData = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
            if ($menuData) {
                //拥有上传文件的权限
                $this->addRepairFile($repid, ACTION_NAME);
            }
            $result['status']        = 1;
            $result['repair_status'] = $data['status'];
            $result['msg']           = '提交成功';
        } else {
            $result['status'] = -1;
            $result['msg']    = '提交失败!';
        }
        return $result;
    }

    //检修暂存
    public function tmp_save_overhaul($data, $repid)
    {
        //查询设备所属医院
        $assInfo    = $this->DB_get_one('assets_info', 'assets,assnum,model,departid,hospital_id',
            ['assid' => $data['assid']]);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assInfo['department'] = $departname[$assInfo['departid']]['department'];
        $data['repair_type']   = I('post.repair_type');
        $data['fault_type']    = I('post.type');
        $data['editdate']      = time();
        $data['is_scene']      = C('NO_STATUS');
        $data['overhauldate']  = time();
        $data['repair_remark'] = I('post.repair_remark');
        $problem               = I('post.problem');
        $problem               = explode('|', $problem);
        $problemData           = [];
        $ftypes                = [];
        foreach ($problem as $key => $one) {
            $problemData[$key]['repid']            = $repid;
            $fault                                 = explode('-', $one);
            $problemData[$key]['fault_type_id']    = $fault[0];
            $problemData[$key]['fault_problem_id'] = $fault[1];
            if (!in_array($fault[0], $ftypes)) {
                $ftypes[] = $fault[0];
            }
        }
        $data['fault_type'] = implode(',', $ftypes);
        if (I('post.expect_time')) {
            $data['expect_time'] = strtotime(I('post.expect_time'));
        }
        //维保厂家
        if ($data['repair_type'] == C('REPAIR_TYPE_IS_GUARANTEE')) {
            $data['salesman_name']  = trim(I('POST.salesman_name'));
            $data['salesman_phone'] = trim(I('POST.salesman_phone'));
            $data['guarantee_id']   = trim(I('POST.guarantee_id'));
            $data['guarantee_name'] = trim(I('POST.guarantee_name'));
        }
        if ($problemData[0]['fault_type_id']) {
            //记录故障问题
            $this->deleteData('repair_fault', ['repid' => $repid]);
            $this->insertDataALL('repair_fault', $problemData);
        }
        //自修
        if ($data['repair_type'] == C('REPAIR_TYPE_IS_STUDY')) {
            //记录配件信息
            $parts_result = $this->get_insert_parts($repid);
            if ($parts_result['data']) {
                //有配件，删除原来的配件记录，保存新的配件
                $this->deleteData('repair_parts', ['repid' => $repid]);
                $this->insertDataALL('repair_parts', $parts_result['data']);
            }
        }
        $menuData = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
        if ($menuData) {
            //拥有上传文件的权限
            //清除上一次临时保存的数据
            $this->deleteData('repair_file', ['repid' => $repid]);
            $this->addRepairFile($repid, ACTION_NAME);
        }
        //第三方
        if ($data['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')) {
            //记录报价公司信息
            $this->save_tmp_company($repid);
        }
        $save = $this->updateData('repair', $data, ['repid' => $repid]);
        if ($save) {
            $result['status'] = 1;
            $result['msg']    = '暂存成功';
        } else {
            $result['status'] = -1;
            $result['msg']    = '暂存失败!';
        }
        return $result;
    }

    //统一报价列表
    public function unifiedOffer()
    {
        $assets      = I('POST.assets');
        $is_offer    = I('POST.is_offer');
        $department  = I('POST.department');
        $assetsCat   = I('POST.assetsCat');
        $applicant   = I('POST.applicant');
        $startDate   = I('POST.startDate');
        $endDate     = I('POST.endDate');
        $guarantee   = I('POST.guarantee');
        $hospital_id = I('POST.hospital_id');
        $limit       = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page        = I('post.page') ? I('post.page') : 1;
        $offset      = ($page - 1) * $limit;
        $order       = I('POST.order') ? I('POST.order') : 'DESC';
        $sort        = I('POST.sort');
        if ($is_offer) {
            if ($is_offer == 1) {
                $where['A.is_offer'] = C('YES_STATUS');
            } else {
                $whereOR['A.status'] = C('REPAIR_QUOTATION');
                $whereOR['_logic']   = 'OR';
                $where['_complex']   = $whereOR;
            }
        } else {
            $whereOR['A.status']   = C('REPAIR_QUOTATION');
            $whereOR['A.is_offer'] = C('YES_STATUS');
            $whereOR['_logic']     = 'OR';
            $where['_complex']     = $whereOR;
        }
        switch ($sort) {
            case 'repnum':
                $sort = 'A.repnum';
                break;
            case 'status':
                $sort = 'A.status';
                break;
            case 'applicant_time':
                $sort = 'A.applicant_time';
                break;
            case 'assets':
                $sort = 'convert(A.assets USING gbk) COLLATE gbk_chinese_ci';
                break;
            case 'model':
                $sort = 'A.model';
                break;
            default:
                $sort = 'A.repid ';
                break;
        }
        if ($assets) {
            $where['A.assets'] = ['LIKE', $assets];
        }
        if ($department) {
            $where['C.departid'] = $department;
        }
        if ($assetsCat) {
            //分类搜索
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['LIKE', "%$assetsCat%"];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['C.catid']     = ['IN', $catids];
        }
        if ($applicant) {
            $where['A.applicant'] = $applicant;
        }
        if ($startDate) {
            $where['applicant_time'][] = ['gt', (strtotime($startDate) - 1)];
        }
        if ($endDate) {
            $where['applicant_time'][] = ['lt', (strtotime($endDate) + 24 * 3600)];
        }
        if ($guarantee) {
            if ($guarantee == 1) {
                $AssetsInsuranceModel = new AssetsInsuranceModel();
                $GuaranteeData        = $AssetsInsuranceModel->returnGuaranteeData();
                $where['A.assid']     = ['IN', $GuaranteeData];
            } elseif ($guarantee == 2) {
                $AssetsInsuranceModel = new AssetsInsuranceModel();
                $GuaranteeData        = $AssetsInsuranceModel->returnGuaranteeData();
                $where['A.assid']     = ['NOT IN', $GuaranteeData];
            }
        }
        if ($hospital_id) {
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }
        $join   = 'LEFT JOIN sb_assets_info AS C ON A.assid=C.assid';
        $fields = 'A.assid,A.expect_price,A.assign,A.repid,A.assets,A.assnum,A.model,A.status,A.applicant,
        A.applicant_time,A.repnum,A.breakdown,A.applicant_remark AS remark,A.is_offer,C.departid';
        $total  = $this->DB_get_count_join('repair', 'A', $join, $where);
        $list   = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        if (!$list) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $menuDoOffer = get_menu($this->MODULE, $this->Controller, 'doOffer');
        foreach ($list as &$one) {
            $one['department']     = $departname[$one['departid']]['department'];
            $one['applicant_time'] = getHandleTime($one['applicant_time']);
            $one['operation']      = '<div class="layui-btn-group">';
            $one['operation']      .= $this->returnListLink('设备详情', C('SHOWASSETS_ACTION_URL'), 'showAssets',
                C('BTN_CURRENCY') . ' layui-btn-primary');
            $detailsUrl            = get_url() . '?action=showRepairDetails&assid=' . $one['assid'] . '&repid=' . $one['repid'];
            if ($one['status'] == C('REPAIR_QUOTATION')) {
                //报价中
                if ($menuDoOffer) {
                    $one['operation'] .= $this->returnListLink($menuDoOffer['actionname'], $menuDoOffer['actionurl'],
                        'doOffer', C('BTN_CURRENCY'));
                } else {
                    $one['operation'] .= $this->returnListLink('待报价', $detailsUrl, 'showDetails',
                        C('BTN_CURRENCY') . ' layui-btn-primary');
                }
            } else {
                $one['operation'] .= $this->returnListLink('已报价', $detailsUrl, 'showDetails',
                    C('BTN_CURRENCY') . ' layui-btn-warm');
            }
            $one['operation'] .= '</div>';
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $list;
        return $result;
    }

    //报价操作
    public function doOffer()
    {
        $repid = I('post.repid');
        if (!$repid) {
            die(json_encode(['status' => -999, 'msg' => '非法参数！']));
        }
        $data = $this->DB_get_one('repair', 'assid,repnum,repair_type,status,departid,assets,assnum,response',
            ['repid' => $repid]);
        if (!$data) {
            die(json_encode(['status' => -2, 'msg' => '维修单不存在！']));
        }
        //查询设备所属医院
        $assInfo = $this->DB_get_one('assets_info', 'hospital_id', ['assid' => $data['assid']]);
        if (!$assInfo) {
            die(json_encode(['status' => -2, 'msg' => '异常参数，设备无所属医院！']));
        }

        //审审批状态
        $isOpenApprove = $this->checkApproveIsOpen(C('REPAIR_APPROVE'), $assInfo['hospital_id']);
        //第三方报价
        $company_result = [];
        $repairContract = [];
        $approve        = [];
        if ($data['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')) {
            $company_result = $this->insertOfferCompany($repid);

            if ($company_result['data']) {
                foreach ($company_result['data'] as $company_val) {
                    if ($company_val['last_decisioin'] == C('YES_STATUS')) {
                        $repairContract['hospital_id']       = $assInfo['hospital_id'];
                        $repairContract['repid']             = $repid;
                        $repairContract['supplier_id']       = $company_val['offer_company_id'];
                        $repairContract['supplier_name']     = $company_val['offer_company'];
                        $repairContract['supplier_contacts'] = $company_val['offer_contacts'];
                        $repairContract['supplier_phone']    = $company_val['telphone'];
                        $repairContract['contract_amount']   = $company_val['total_price'];
                        $repairContract['add_user']          = $company_val['add_user'];
                        $repairContract['add_time']          = getHandleDate(time());
                        $count                               = $this->DB_get_count('repair_contract');
                        $repairContract['contract_num']      = 'WXHT' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
                        $company_result['totalPrice']        = $company_val['total_price'];
                    }
                }
                if ($isOpenApprove) {
                    //查询是否已设置审批流程
                    $isSetProcess = $this->checkApproveIsSetProcess(C('REPAIR_APPROVE'), $assInfo['hospital_id']);
                    if (!$isSetProcess) {
                        die(json_encode(['status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！']));
                    }
                    //需要审批，获取审批人
                    $approve_process_user = $this->get_approve_process($company_result['totalPrice'],
                        C('REPAIR_APPROVE'), $assInfo['hospital_id']);
                    //并且获取下次审批人
                    $approve = $this->check_approve_process($data['departid'], $approve_process_user, 1);
                    if ($approve['all_approver'] == '') {
                        //不在审核范围内 不需要审批
                        $data['approve_status'] = C('STATUS_APPROE_UNWANTED');
                        $data['status']         = C('REPAIR_MAINTENANCE');//维修中
                    } else {
                        //默认为未审核
                        $repairContract                = [];
                        $data['current_approver']      = $approve['current_approver'];
                        $data['complete_approver']     = '';
                        $data['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                        $data['all_approver']          = $approve['all_approver'];
                        $data['approve_status']        = C('APPROVE_STATUS');
                        $data['status']                = C('REPAIR_AUDIT');//审核中
                    }
                } else {
                    //未开启维修审批，不需要审批
                    $data['status'] = C('REPAIR_MAINTENANCE');//维修中
                }
            }
        }
        $data['offer_time'] = time();
        $data['editdate']   = time();
        $data['is_offer']   = C('YES_STATUS');
        $data['offer_user'] = session('username');
        $save               = $this->updateData('repair', $data, ['repid' => $repid]);
        if ($save !== false) {
            //日志行为记录文字
            $log['repnum'] = $data['repnum'];
            $text          = getLogText('doOfferLogText', $log);
            $this->addLog('repair', M()->getLastSql(), $text, $repid);
            if ($data['status'] == C('REPAIR_MAINTENANCE')) {
                $result['status'] = 1;
                $result['msg']    = '报价成功并通知维修工程师' . $data['response'];
            } else {
                $result['status'] = 1;
                $result['msg']    = '报价成功!';
            }


            $delOffid = I('POST.delOffid');
            if ($delOffid) {
                //删除被移除的公司 包括 配料
                $this->deleteData('repair_offer_company', "offid IN(" . implode(',', $delOffid) . ")");
            }
            if ($company_result['data']) {
                //第三方入库
                $this->add_company($company_result, $repid);
            }
            if ($repairContract) {
                //有权限选择最终厂家 并且不需要审批  则生产一条待确认的合同
                $this->insertData('repair_contract', $repairContract);
            }
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $data['department'] = $departname[$data['departid']]['department'];
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($settingData) {
                //有开启短信
                $ToolMod = new ToolController();
                switch ($data['status']) {
                    case C('REPAIR_MAINTENANCE'):
                        //继续维修 通知工程师继续维修
                        $where              = [];
                        $where['status']    = C('OPEN_STATUS');
                        $where['is_delete'] = C('NO_STATUS');
                        $where['username']  = $data['response'];
                        $approve_user       = $this->DB_get_one('user', 'telephone', $where);
                        if ($settingData['repairOfferOver']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                            $sms = $this->formatSmsContent($settingData['repairOfferOver']['content'], $data);
                            $ToolMod->sendingSMS($approve_user['telephone'], $sms, $this->Controller, $repid);
                        }
                        break;
                    case C('REPAIR_AUDIT'):
                        //审批中
                        if ($approve['this_current_approver']) {
                            //通知审批人审批
                            $where              = [];
                            $where['status']    = C('OPEN_STATUS');
                            $where['is_delete'] = C('NO_STATUS');
                            $where['username']  = $approve['this_current_approver'];
                            $approve_user       = $this->DB_get_one('user', 'telephone', $where);
                            if ($settingData['doApprove']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                                $sms = $this->formatSmsContent($settingData['doApprove']['content'], $data);
                                $ToolMod->sendingSMS($approve_user['telephone'], $sms, $this->Controller, $repid);
                            }
                        }
                        break;
                }
            }
            //==========================================短信 END==========================================
            $moduleModel = new ModuleModel();
            $wx_status   = $moduleModel->decide_wx_login();
            switch ($data['status']) {
                case C('REPAIR_MAINTENANCE'):
                    //继续维修 通知工程师继续维修
                    $where              = [];
                    $where['status']    = C('OPEN_STATUS');
                    $where['is_delete'] = C('NO_STATUS');
                    $where['username']  = $data['response'];
                    $approve_user       = $this->DB_get_one('user', 'openid,telephone', $where);
                    if (!$approve_user['openid'] || !$wx_status) {
                        break;
                    }
                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**维修单号：**' . $data['repnum'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**' . $assInfo['assets'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**' . $assInfo['assnum'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**使用科室：**' . $assInfo['department'];
                        $feishu_fields[]       = $fd;

                        //按钮区域
                        $act['tag']             = 'button';
                        $act['type']            = 'primary';
                        $act['url']             = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Repair/startRepair?repid=' . $repid;
                        $act['text']['tag']     = 'plain_text';
                        $act['text']['content'] = '继续维修';
                        $feishu_actions[]       = $act;

                        $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag']         = 'div';
                        $card_data['elements'][0]['fields']      = $feishu_fields;
                        $card_data['elements'][1]['tag']         = 'hr';
                        $card_data['elements'][2]['actions']     = $feishu_actions;
                        $card_data['elements'][2]['layout']      = 'bisected';
                        $card_data['elements'][2]['tag']         = 'action';
                        $card_data['header']['template']         = 'orange';
                        $card_data['header']['title']['content'] = '设备继续维修提醒';
                        $card_data['header']['title']['tag']     = 'plain_text';

                        $this->send_feishu_card_msg($approve_user['openid'], $card_data);
                        //==========================================飞书 END==========================================
                    } else {
                        //继续维修，发送微信消息给维修工程师（维修处理通知）
                        if (C('USE_VUE_WECHAT_VERSION')) {
                            $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Repair/startRepair?repid=' . $repid;
                        } else {
                            $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Repair/startRepair.html?repid=' . $repid;
                        }

                        if ($approve_user['openid']) {
                            Weixin::instance()->sendMessage($approve_user['openid'], '设备维修通知', [
                                'thing3'             => $data['department'],// 科室
                                'thing6'             => $data['assets'],// 设备名称
                                'character_string12' => $data['assnum'],// 设备编码
                                'character_string35' => $data['repnum'],// 维修单号
                                'const17'            => '已审批通过，请继续维修',// 工单状态
                            ], $redecturl);
                        }
                    }
                    break;
                case C('REPAIR_AUDIT'):
                    //审批中
                    if (!$approve['this_current_approver']) {
                        break;
                    }
                    //通知审批人审批
                    $where              = [];
                    $where['status']    = C('OPEN_STATUS');
                    $where['is_delete'] = C('NO_STATUS');
                    $where['username']  = $approve['this_current_approver'];
                    $approve_user       = $this->DB_get_one('user', 'openid,telephone', $where);
                    if (!$approve_user['openid'] || !$wx_status) {
                        break;
                    }
                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**维修单号：**' . $data['repnum'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**' . $assInfo['assets'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**' . $assInfo['assnum'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**使用科室：**' . $assInfo['department'];
                        $feishu_fields[]       = $fd;

                        //按钮区域
                        $act['tag']             = 'button';
                        $act['type']            = 'primary';
                        $act['url']             = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Repair/addApprove?repid=' . $repid;
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
                        $card_data['header']['title']['content'] = '维修审批提醒';
                        $card_data['header']['title']['tag']     = 'plain_text';

                        $this->send_feishu_card_msg($approve_user['openid'], $card_data);
                        //==========================================飞书 END==========================================
                    } else {
                        if (C('USE_VUE_WECHAT_VERSION')) {
                            $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Repair/addApprove?repid=' . $repid;
                        } else {
                            $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Repair/addApprove.html?repid=' . $repid;
                        }

                        Weixin::instance()->sendMessage($approve_user['openid'], '设备维修通知', [
                            'thing3'             => $data['department'],// 科室
                            'thing6'             => $data['assets'],// 设备名称
                            'character_string12' => $data['assnum'],// 设备编码
                            'character_string35' => $data['repnum'],// 维修单号
                            'const17'            => '已报价，请审批',// 工单状态
                        ], $redecturl);
                    }
                    break;
            }

            //推送一条推送消息到大屏幕
            switch ($data['status']) {
                case C('REPAIR_MAINTENANCE'):
                    //继续维修 通知工程师继续维修
                    $push_messages[] = [
                        'type_action' => 'edit',
                        'type_name'   => C('SCREEN_REPAIR'),
                        'assets'      => $data['assets'],
                        'assnum'      => $data['assnum'],
                        'department'  => $data['department'],
                        'remark'      => $data['remark'],
                        'status'      => $data['status'],
                        'status_name' => '维修中',
                        'time'        => date('Y-m-d H:i'),
                        'username'    => session('username') . '(' . session('telephone') . ')',
                    ];
                    push_messages($push_messages);
                    break;
                case C('REPAIR_AUDIT'):
                    //审批中
                    $umodel          = M('user');
                    $uinfo           = $umodel->where(['is_delete' => 0])->getField('username,telephone');
                    $super           = $umodel->where(['is_delete' => 0, 'is_super' => 1])->getField('username', 1);
                    $apper           = str_replace($super, '', $approve['current_approver']);
                    $apper           = trim($apper, ',');
                    $apptel          = $uinfo[$apper];
                    $username        = $apper . '(' . $apptel . ')';
                    $push_messages[] = [
                        'type_action' => 'edit',
                        'type_name'   => C('SCREEN_REPAIR'),
                        'assets'      => $data['assets'],
                        'assnum'      => $data['assnum'],
                        'department'  => $data['department'],
                        'remark'      => '',
                        'status'      => $data['status'],
                        'status_name' => '审批中',
                        'time'        => '',
                        'username'    => $username,
                    ];
                    push_messages($push_messages);
                    break;
            }
        } else {
            $result['status'] = -1;
            $result['msg']    = '报价失败!';
        }
        return $result;
    }

    //维修审批列表
    public function repairApproveLists()
    {
        $assets          = trim(I('POST.assetsName'));
        $assetsNum       = trim(I('POST.assetsNum'));
        $assetsCat       = trim(I('POST.assetsCat'));
        $assetsDep       = I('POST.assetsDep');
        $startDate       = I('POST.startDate');
        $endDate         = I('POST.endDate');
        $startResDate    = I('POST.startResDate');
        $endResDate      = I('POST.endResDate');
        $priceMin        = I('POST.priceMin');
        $priceMax        = I('POST.priceMax');
        $appUser         = I('POST.appUser');
        $repair_category = I('POST.repair_category');
        $hospital_id     = session('current_hospitalid');
        $limit           = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page            = I('post.page') ? I('post.page') : 1;
        $offset          = ($page - 1) * $limit;
        $order           = I('POST.order') ? I('POST.order') : 'DESC';
        $sort            = I('POST.sort');
        $approveStatus   = I('POST.approveStatus');
        if ($assetsNum && !judgeNum($assetsNum)) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if ($startDate && $endDate && $startDate > $endDate) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if ($startResDate && $endResDate && $startResDate > $endResDate) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if ($priceMin && $priceMax && $priceMin > $priceMax) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if (!session('departid')) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['B.departid']     = ['IN', session('departid')];
        $where['A.all_approver'] = ['LIKE', '%/' . session('username') . '/%'];
        if ($priceMin) {
            //预计维修费用--开始
            $where['A.expect_price'] = ['EGT', $priceMin];
        }
        if ($priceMax) {
            //预计维修费用--结束
            $where['A.expect_price'] = ['ELT', $priceMax];
        }
        if ($appUser) {
            //经手人
            $where['A.examine_user'] = ['EQ', $appUser];
        }
        if ($assets) {
            //设备名称搜索
            $where['B.assets'] = ['LIKE', "%$assets%"];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['B.assnum'] = ['LIKE', "%$assetsNum%"];
        }
        if ($assetsCat) {
            //分类搜索
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['LIKE', "%$assetsCat%"];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['B.catid']     = ['IN', $catids];
        }
        if ($assetsDep) {
            //部门搜索
            $where['A.departid'] = ['IN', $assetsDep];
        }
        if ($repair_category) {
            $where['A.repair_category'] = ['EQ', $repair_category];
        }
        if ($approveStatus == '3') {
            //已审核搜索
            $where['A.approve_status'] = ['GT', C('APPROVE_STATUS')];
        } elseif ($approveStatus >= 0 && $approveStatus != 3 && $approveStatus != null) {
            $where['A.approve_status'] = ['EQ', $approveStatus];
        }
        if ($startDate) {
            //报修时间--开始
            $where['A.applicant_time'] = ['EGT', (strtotime($startDate) - 1)];
        }
        if ($endDate) {
            //报修时间--结束
            $where['A.applicant_time'] = ['ELT', (strtotime($endDate) + 24 * 3600)];
        }
        if ($startResDate) {
            //接单时间--开始
            $where['A.response_date'] = ['EGT', (strtotime($startResDate) - 1)];
        }
        if ($endResDate) {
            //接单时间--结束
            $where['A.response_date'] = ['ELT', (strtotime($endResDate) + 24 * 3600)];
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = ['EQ', $hospital_id];
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }

        switch ($sort) {
            case 'appTime':
                $sort = 'A.applicant_time';
                break;
            case 'part_num':
                $sort = 'A.part_num';
                break;
            case 'pPrice':
                $sort = 'A.part_total_price';
                break;
            case 'buy_price':
                $sort = 'B.buy_price';
                break;
            case 'ePrice':
                $sort = 'A.expect_price';
                break;
            case 'aPrice':
                $sort = 'A.actual_price';
                break;
            default:
                $sort = 'A.repid ';
                break;
        }

        $join[0] = ' LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $total   = $this->DB_get_count_join('repair', 'A', $join, $where);
        //G('begin');
        $fields = 'A.archives_num,A.repid,A.applicant as appUser,A.applicant_time as appTime,A.repnum,A.status,A.breakdown,A.repair_type as rType,
        A.expect_price as ePrice,A.actual_price as aPrice,A.part_num,A.part_total_price as pPrice,A.assprice,A.overdate,A.fault_type as fauleType,
        A.fault_problem as faultProblem,A.response,A.engineer,A.engineer_time as engineerTime,A.repair_remark as rRemark,A.part_num as pNum,
        A.working_hours as wHours,A.approve_status as approveStatus,A.applicant_remark as appRemark,A.current_approver,A.complete_approver,A.not_complete_approver,A.all_approver,B.assid,B.acid,B.assets,B.catid,B.assnum,
        B.assorignum,B.model,B.departid,B.buy_price';
        $asArr  = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        if (!$asArr) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $catname    = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        $Apply = get_menu($this->MODULE, 'Repair', 'addApprove');
        foreach ($asArr as $k => $v) {
            $asArr[$k]['cat_name']     = $catname[$v['catid']]['category'];
            $asArr[$k]['department']   = $departname[$v['departid']]['department'];
            $asArr[$k]['appTime']      = getHandleTime($v['appTime']);
            $asArr[$k]['overdate']     = getHandleTime($v['overdate']);
            $asArr[$k]['engineerTime'] = getHandleTime($v['engineerTime']);
        }
        foreach ($asArr as $k => $v) {
            $detailsUrl = get_url() . '?action=showRepairDetails&repid=' . $v['repid'] . '&assid=' . $v['assid'];
            $html       = '<div class="layui-btn-group">';
            $html       .= $this->returnListLink('设备详情', C('SHOWASSETS_ACTION_URL'), 'showAssets',
                C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($asArr[$k]['approveStatus'] == C('APPROVE_STATUS')) {
                //审批中
                if ($v['current_approver']) {
                    $current_approver     = explode(',', $v['current_approver']);
                    $current_approver_arr = [];
                    foreach ($current_approver as &$current_approver_value) {
                        $current_approver_arr[$current_approver_value] = true;
                    }
                    if ($current_approver_arr[session('username')]) {
                        $html .= $this->returnListLink($Apply['actionname'], $Apply['actionurl'], 'approve',
                            C('BTN_CURRENCY'));
                    } else {
                        $complete    = explode(',', $v['complete_approver']);
                        $notcomplete = explode(',', $v['not_complete_approver']);
                        if (!in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                            //完全未审
                            $html .= $this->returnListLink('待审批', $detailsUrl, 'showDetails',
                                C('BTN_CURRENCY') . ' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && in_array(session('username'),
                                $notcomplete)) {
                            //有已审，有未审
                            $html .= $this->returnListLink('待审批', $detailsUrl, 'showDetails',
                                C('BTN_CURRENCY') . ' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && !in_array(session('username'),
                                $notcomplete)) {
                            //全部已审
                            $html .= $this->returnListLink('已审批', $detailsUrl, 'showDetails',
                                C('BTN_CURRENCY') . ' layui-btn-primary');
                        } else {
                            $html .= '';
                        }
                    }
                }
            } elseif ($asArr[$k]['approveStatus'] == C('STATUS_APPROE_SUCCESS')) {
                $html .= $this->returnListLink('已通过', $detailsUrl, 'showDetails',
                    C('BTN_CURRENCY') . ' layui-btn-normal');
            } elseif ($asArr[$k]['approveStatus'] == 2) {
                $html .= $this->returnListLink('不通过', $detailsUrl, 'showDetails',
                    C('BTN_CURRENCY') . ' layui-btn-danger');
            }
            $html                   .= '</div>';
            $asArr[$k]['operation'] = $html;
        }

        //G('end');
        //echo G('begin','end').'s';exit;
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["rows"]   = $asArr;
        $result['code']   = 200;
        return $result;
    }

    //审核操作
    public function doAddApprove()
    {
        $data['repid'] = I('POST.repid');
        if (!$data['repid']) {
            $result['status'] = -1;
            $result['msg']    = '参数错误';
            return $result;
        }
        //查询当前维修单的信息
        $repInfo = $this->DB_get_one('repair', '*', ['repid' => $data['repid']]);
        if (!$repInfo['expect_price']) {
            $result['status'] = -1;
            $result['msg']    = '此单不存在';
            return $result;
        }
        $isOpenApprove = $this->checkApproveIsOpen(C('REPAIR_APPROVE'), session('job_hospitalid'));
        if (!$isOpenApprove) {
            $result['status'] = -1;
            $result['msg']    = '审核功能未开启';
            return $result;
        }
        //查询设备信息
        $assInfo               = $this->DB_get_one('assets_info',
            'assid,hospital_id,assnum,assets,status,departid,quality_in_plan,patrol_in_plan,buy_price',
            ['assid' => $repInfo['assid']]);
        $data['repid']         = $repInfo['repid'];
        $data['is_adopt']      = I('POST.is_adopt');
        $data['remark']        = I('POST.remark');
        $data['proposer']      = $repInfo['response'];
        $data['proposer_time'] = $repInfo['response_date'];
        $data['approver']      = session('username');
        $data['approve_time']  = time();
        $data['approve_class'] = 'repair';
        $data['process_node']  = C('REPAIR_APPROVE');

        $repInfo['is_adopt'] = $data['is_adopt'];
        $repInfo['proposer'] = $data['proposer'];
        //判断是否是当前审批人
        if ($repInfo['current_approver']) {
            $current_approver     = explode(',', $repInfo['current_approver']);
            $current_approver_arr = [];
            foreach ($current_approver as &$current_approver_value) {
                $current_approver_arr[$current_approver_value] = true;
            }
            if ($current_approver_arr[session('username')]) {
                $processWhere['repid']      = ['EQ', $repInfo['repid']];
                $processWhere['is_delete']  = ['NEQ', C('YES_STATUS')];
                $process                    = $this->DB_get_count('approve', $processWhere);
                $level                      = $process + 1;
                $data['process_node_level'] = $level;
                $res                        = $this->addApprove($repInfo, $data, $repInfo['expect_price'],
                    $assInfo['hospital_id'], $assInfo['departid'], C('REPAIR_APPROVE'), 'repair', 'repid');
                if ($res['status'] == 1) {
                    //添加日志
                    $log['repnum']   = $repInfo['repnum'];
                    $log['is_adopt'] = $data['is_adopt'] == C('STATUS_APPROE_SUCCESS') ? '同意' : '不同意';
                    $text            = getLogText('approveRepairLogText', $log);
                    $this->addLog('sb_repair', M()->getLastSql(), $text, $repInfo['repid']);

                    //审批成功，查询维修单当前状态信息
                    $repairModel = new RepairModel();
                    $repInfo     = $repairModel->DB_get_one('repair',
                        'repid,repnum,status,assid,departid,assets,assnum,response,engineer,approve_status,current_approver',
                        ['repid' => $data['repid']]);
                    $departname  = [];
                    include APP_PATH . "Common/cache/department.cache.php";
                    $repInfo['department'] = $departname[$repInfo['departid']]['department'];
                    $moduleModel           = new ModuleModel();
                    $wx_status             = $moduleModel->decide_wx_login();
                    if ($repInfo['status'] == C('REPAIR_AUDIT') && $data['is_adopt'] == C('STATUS_APPROE_SUCCESS')) {
                        if (C('USE_FEISHU') === 1) {
                            //==========================================飞书 START========================================
                            //要显示的字段区域
                            $fd['is_short']        = false;//是否并排布局
                            $fd['text']['tag']     = 'lark_md';
                            $fd['text']['content'] = '**维修单号：**' . $repInfo['repnum'];
                            $feishu_fields[]       = $fd;

                            $fd['is_short']        = false;//是否并排布局
                            $fd['text']['tag']     = 'lark_md';
                            $fd['text']['content'] = '**设备名称：**' . $repInfo['assets'];
                            $feishu_fields[]       = $fd;

                            $fd['is_short']        = false;//是否并排布局
                            $fd['text']['tag']     = 'lark_md';
                            $fd['text']['content'] = '**设备编码：**' . $repInfo['assnum'];
                            $feishu_fields[]       = $fd;

                            $fd['is_short']        = false;//是否并排布局
                            $fd['text']['tag']     = 'lark_md';
                            $fd['text']['content'] = '**使用科室：**' . $repInfo['department'];
                            $feishu_fields[]       = $fd;

                            //按钮区域
                            $act['tag']             = 'button';
                            $act['type']            = 'primary';
                            $act['url']             = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Repair/addApprove?repid=' . $repInfo['repid'];
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
                            $card_data['header']['title']['content'] = '维修审批提醒';
                            $card_data['header']['title']['tag']     = 'plain_text';

                            //查询审核人员openid
                            $applicant    = $repairModel->DB_get_all('user', 'telephone,openid',
                                ['username' => ['in', $repInfo['current_approver']]]);
                            $already_send = [];
                            foreach ($applicant as $k => $v) {
                                if ($v['openid'] && !in_array($v['openid'], $already_send)) {
                                    $this->send_feishu_card_msg($v['openid'], $card_data);
                                    $already_send[] = $v['openid'];
                                }
                            }
                            //==========================================飞书 END==========================================
                        } else {
                            if ($wx_status) {
                                //审核中，并且通过了的，发送微信消息给下一审核人（维修处理通知）
                                if (C('USE_VUE_WECHAT_VERSION')) {
                                    $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Repair/addApprove?repid=' . $repInfo['repid'];
                                } else {
                                    $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Repair/addApprove.html?repid=' . $repInfo['repid'];
                                }
                                //查询审核人员openid
                                $applicant    = $repairModel->DB_get_all('user', 'telephone,openid',
                                    ['username' => ['in', $repInfo['current_approver']]]);

                                $openIds = array_column($applicant, 'openid');
                                $openIds = array_filter($openIds);
                                $openIds = array_unique($openIds);

                                $messageData = [
                                    'thing3'             => $repInfo['department'],// 科室
                                    'thing6'             => $repInfo['assets'],// 设备名称
                                    'character_string12' => $repInfo['assnum'],// 设备编码
                                    'character_string35' => $repInfo['repnum'],// 维修单号
                                    'const17'            => '待审批',// 工单状态
                                ];

                                foreach ($openIds as $openId) {
                                    Weixin::instance()->sendMessage($openId, '设备维修通知', $messageData, $redecturl);
                                }
                            }
                        }
                    }
                    if ($repInfo['status'] == C('REPAIR_MAINTENANCE')) {
                        if (C('USE_FEISHU') === 1) {
                            //==========================================飞书 START========================================
                            //要显示的字段区域
                            $fd['is_short']        = false;//是否并排布局
                            $fd['text']['tag']     = 'lark_md';
                            $fd['text']['content'] = '**维修单号：**' . $repInfo['repnum'];
                            $feishu_fields[]       = $fd;

                            $fd['is_short']        = false;//是否并排布局
                            $fd['text']['tag']     = 'lark_md';
                            $fd['text']['content'] = '**设备名称：**' . $repInfo['assets'];
                            $feishu_fields[]       = $fd;

                            $fd['is_short']        = false;//是否并排布局
                            $fd['text']['tag']     = 'lark_md';
                            $fd['text']['content'] = '**设备编码：**' . $repInfo['assnum'];
                            $feishu_fields[]       = $fd;

                            $fd['is_short']        = false;//是否并排布局
                            $fd['text']['tag']     = 'lark_md';
                            $fd['text']['content'] = '**使用科室：**' . $repInfo['department'];
                            $feishu_fields[]       = $fd;

                            //按钮区域
                            $act['tag']             = 'button';
                            $act['type']            = 'primary';
                            $act['url']             = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/Repair/startRepair?repid=' . $repInfo['repid'];
                            $act['text']['tag']     = 'plain_text';
                            $act['text']['content'] = '继续维修';
                            $feishu_actions[]       = $act;

                            $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
                            $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                            $card_data['elements'][0]['tag']         = 'div';
                            $card_data['elements'][0]['fields']      = $feishu_fields;
                            $card_data['elements'][1]['tag']         = 'hr';
                            $card_data['elements'][2]['actions']     = $feishu_actions;
                            $card_data['elements'][2]['layout']      = 'bisected';
                            $card_data['elements'][2]['tag']         = 'action';
                            $card_data['header']['template']         = 'orange';
                            $card_data['header']['title']['content'] = '设备继续维修提醒';
                            $card_data['header']['title']['tag']     = 'plain_text';

                            //查询接单人员openid
                            $applicant = $repairModel->DB_get_one('user', 'telephone,openid',
                                ['username' => $repInfo['response']]);
                            if ($applicant['openid']) {
                                $this->send_feishu_card_msg($applicant['openid'], $card_data);
                            }
                            //==========================================飞书 END==========================================
                        } else {
                            if ($wx_status) {
                                //继续维修，发送微信消息给维修工程师（维修处理通知）
                                if (C('USE_VUE_WECHAT_VERSION')) {
                                    $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Repair/startRepair?repid=' . $repInfo['repid'];
                                } else {
                                    $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Repair/startRepair.html?repid=' . $repInfo['repid'];
                                }
                                //查询接单人员openid
                                $applicant = $repairModel->DB_get_one('user', 'telephone,openid',
                                    ['username' => $repInfo['response']]);

                                if ($applicant['openid']) {
                                    Weixin::instance()->sendMessage($applicant['openid'], '设备维修通知', [
                                        'thing3'             => $repInfo['department'],// 科室
                                        'thing6'             => $repInfo['assets'],// 设备名称
                                        'character_string12' => $repInfo['assnum'],// 设备编码
                                        'character_string35' => $repInfo['repnum'],// 维修单号
                                        'const17'            => '已审批通过，请继续维修',// 工单状态
                                    ], $redecturl);
                                }
                            }
                        }
                    }

                    if ($repInfo['status'] == C('REPAIR_AUDIT') && $data['is_adopt'] == C('STATUS_APPROE_SUCCESS')) {
                        //审批中 推送消息到大屏幕
                        $umodel          = M('user');
                        $uinfo           = $umodel->where(['is_delete' => 0])->getField('username,telephone');
                        $super           = $umodel->where(['is_delete' => 0, 'is_super' => 1])->getField('username', 1);
                        $apper           = str_replace($super, '', $repInfo['current_approver']);
                        $apper           = trim($apper, ',');
                        $apptel          = $uinfo[$apper];
                        $username        = $apper . '(' . $apptel . ')';
                        $push_messages[] = [
                            'type_action' => 'edit',
                            'type_name'   => C('SCREEN_REPAIR'),
                            'assets'      => $assInfo['assets'],
                            'assnum'      => $assInfo['assnum'],
                            'department'  => $repInfo['department'],
                            'remark'      => $data['remark'],
                            'status'      => $repInfo['status'],
                            'status_name' => '审批中',
                            'time'        => date('Y-m-d H:i'),
                            'username'    => $username,
                        ];
                        push_messages($push_messages);
                    }
                    if ($repInfo['status'] == C('REPAIR_MAINTENANCE')) {
                        //继续维修 推送消息到大屏幕
                        $push_messages[] = [
                            'type_action' => 'edit',
                            'type_name'   => C('SCREEN_REPAIR'),
                            'assets'      => $assInfo['assets'],
                            'assnum'      => $assInfo['assnum'],
                            'department'  => $repInfo['department'],
                            'remark'      => '',
                            'status'      => $repInfo['status'],
                            'status_name' => '维修中',
                            'time'        => '',
                            'username'    => '',
                        ];
                        push_messages($push_messages);
                    }
                }
                return $res;
            } else {
                return ['status' => -1, 'msg' => '请等待审批！'];
            }
        } else {
            return ['status' => -1, 'msg' => '审核已结束！'];
        }
    }

    //设备维修列表
    public function getRepairLists()
    {
        $order             = I('POST.order');
        $sort              = I('POST.sort');
        $assets            = I('POST.assetsName');
        $assetsCat         = I('POST.assetsCat');
        $guarantee         = I('POST.guarantee');
        $parts             = I('POST.parts');
        $assetsDep         = I('POST.assetsDep');
        $startDate         = I('POST.startDate');
        $endDate           = I('POST.endDate');
        $repairType        = I('POST.repairType');
        $repair_category   = I('POST.repair_category');
        $hospital_id       = I('POST.hospital_id');
        $status            = I('POST.status');
        $engineerStartDate = I('POST.engineerStartDate');
        $engineerEndDate   = I('POST.engineerEndDate');
        $applicant         = I('POST.applicant');
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        if (!session('departid')) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where = "C.departid IN (" . session('departid') . ") AND A.status>=2";
        if ($hospital_id) {
            $where .= " and C.hospital_id = " . $hospital_id;
        } else {
            $where .= " and C.hospital_id = " . session('current_hospitalid');
        }
        if (!session('isSuper')) {
            $where .= " and response='" . session('username') . "'";
        }
        switch ($sort) {
            case 'applicant_time':
                $sort = 'A.applicant_time';
                break;
            default:
                $sort = 'A.response_date ';
                break;
        }
        if (!$order) {
            $order = 'DESC';
        }
        if ($assets) {//设备名称搜索
            $where .= " and C.assets like '%" . $assets . "%'";
        }
        if ($assetsDep != '') {//部门搜索
            $where .= " and C.departid = " . $assetsDep;
        }
        if ($applicant != '') {//报修人搜索
            $where .= " and A.applicant = '" . $applicant . "'";
        }
        if ($repairType != '') {
            //维修性质搜索
            $where .= " and A.repair_type = " . $repairType;
        }
        if ($repair_category) {
            $where .= " and A.repair_category = '" . $repair_category . "'";
        }
        if ($status) {
            //维修状态搜索
            $where .= " and A.status = " . $status;
        } else {
            $where .= " and A.status < " . C('REPAIR_ALREADY_ACCEPTED');
        }
        if ($guarantee) {//是否保修搜索
            if ($guarantee == 1) {
                $AssetsInsuranceModel = new AssetsInsuranceModel();
                $GuaranteeData        = $AssetsInsuranceModel->returnGuaranteeData();
                $where                .= " and A.assid IN (" . $GuaranteeData . ")";
            } elseif ($guarantee == 2) {
                $AssetsInsuranceModel = new AssetsInsuranceModel();
                $GuaranteeData        = $AssetsInsuranceModel->returnGuaranteeData();
                $where                .= " and A.assid NOT IN (" . $GuaranteeData . ")";
            }
        }
        if ($assetsCat) {
            //分类搜索
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['like', "%$assetsCat%"];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where                .= " and C.catid in (" . $catids . ")";
        }

        if ($parts != '' && $parts != '-1') {
            //是否消耗配件搜索
            if ($parts == '1') {
                $where .= " and A.part_num > 0";
            } elseif ($parts == '0') {
                $where .= " and A.part_num = 0";
            }
        }
        if ($startDate) {//报修时间--开始
            $where .= " and A.applicant_time >" . (strtotime($startDate) - 1);
        }
        if ($endDate) {//报修时间--结束
            $where .= " and A.applicant_time <" . (strtotime($endDate) + 24 * 3600);
        }
        if ($engineerStartDate) {//维修时间--开始
            $where .= " and A.engineer_time >" . (strtotime($engineerStartDate) - 1);
        }
        if ($engineerEndDate) {//维修时间--结束
            $where .= " and A.engineer_time <" . (strtotime($engineerEndDate) + 24 * 3600);
        }
        $join  = 'LEFT JOIN sb_assets_info AS C ON A.assid=C.assid';
        $where .= ' and C.is_delete=0';
        $total = $this->DB_get_count_join('repair', 'A', $join, $where);
        //G('begin');
        $fields = 'A.archives_num,A.assid,A.approve_status,A.expect_price,A.fault_problem,A.repid,A.applicant,A.applicant_time,A.repnum,
        A.status,A.breakdown,A.repair_type,C.assets,C.catid,C.departid,C.assnum,C.model';
        $asArr  = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '',
            'A.status asc,' . $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asArr) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        $catname    = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $asArr = $this->getAcceptHtml($asArr);
        foreach ($asArr as &$one) {
            $one['department']     = $departname[$one['departid']]['department'];
            $one['fault_problem']  = htmlspecialchars_decode($one['fault_problem']);
            $one['category']       = $catname[$one['catid']]['category'];
            $one['applicant_time'] = getHandleTime($one['applicant_time']);
            $one['repTypeName']    = $this->getRepairTypeName($one['repair_type']);
            $one['fault_problem']  = $this->getFaultProblem($one['repid']);
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["rows"]   = $asArr;
        $result["code"]   = 200;
        return $result;
    }

    //设备维修操作
    public function doStartRepair()
    {
        $repid = I('POST.repid');
        if (!$repid) {
            return ['status' => -1, 'msg' => '参数非法！'];
        }
        $data = $this->DB_get_one('repair', '*', ['repid' => $repid]);
        if (!$data) {
            $result['status'] = -4;
            $result['msg']    = '查无此维修单记录';
            return $result;
        }
        if ($data['status'] != C('REPAIR_MAINTENANCE')) {
            $result['status'] = -3;
            $result['msg']    = '设备当前不是维修中状态，请按正常流程操作!';
            return $result;
        } //判断设备是不是已经是结束状态

        $this->checkstatus(judgeNum(I('post.working_hours')), '维修工时有误');
        $this->checkstatus(judgeMobile(I('post.username_tel')), '维修程师手机号码格式有误');
        if (I('post.assist_engineer') == -1) {
            $addData['assist_engineer']     = '';
            $addData['assist_engineer_tel'] = '';
        } else {
            $this->checkstatus(judgeMobile(I('post.assist_engineer_tel')), '协助工程师手机号码格式有误');
            $addData['assist_engineer']     = I('post.assist_engineer');
            $addData['assist_engineer_tel'] = I('post.assist_engineer_tel');
        }
        if (I('post.other_price')) {
            $this->checkstatus(checkPrice(I('post.other_price')), '其他费用：请输入正确费用');
            $addData['other_price']  = I('post.other_price');
            $addData['actual_price'] = $data['expect_price'] + $addData['other_price'];
        }
        if (I('post.overEngineer') == C('YES_STATUS')) {
            $addData['status']   = C('REPAIR_MAINTENANCE_COMPLETION');
            $addData['overdate'] = time();
            $statusText          = '维修结束';
        } else {
            $statusText = '保存当前维修进度';
        }
        if (!$data['engineer']) {
            $addData['engineer'] = session('username');
        }
        //查询设备所属医院
        $assInfo             = $this->DB_get_one('assets_info', 'hospital_id,assets,assnum,departid,model',
            ['assid' => $data['assid']]);
        $data['hospital_id'] = $assInfo['hospital_id'];
        $addData['repid']    = I('post.repid');
        $addData['editdate'] = time();
        if (I('post.service_date')) {
            $addData['engineer_time'] = strtotime(I('post.service_date'));
        } else {
            $addData['engineer_time'] = time();
        }
        $addData['repid']          = I('post.repid');
        $addData['working_hours']  = I('post.working_hours');
        $addData['engineer_tel']   = I('post.username_tel');
        $addData['dispose_detail'] = I('post.dispose_detail');
        $this->addFollow($repid);
        $parts_result = $this->get_insert_parts($repid);
        //维修中产生配件
        if ($parts_result['data']) {
            $addData['status'] = C('REPAIR_HAVE_OVERHAULED');
        }
        $save = $this->updateData('repair', $addData, ['repid' => $repid]);
        if ($save !== false) {
            $log['repnum']     = $data['repnum'];
            $log['statusText'] = $statusText;
            $text              = getLogText('startRepairLogText', $log);
            $this->addLog('repair', M()->getLastSql(), $text, $repid);
            $menuData = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
            if ($menuData) {
                //拥有上传文件的权限
                $this->addRepairFile($repid, ACTION_NAME);
            }
            $settingData = $this->checkSmsIsOpen($this->Controller);
            $departname  = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $assInfo['department'] = $departname[$assInfo['departid']]['department'];
            $data['department']    = $departname[$data['departid']]['department'];

            $ToolMod = new ToolController();
            switch ($addData['status']) {
                case C('REPAIR_HAVE_OVERHAULED'):
                    //产生配件 通知出库
                    $this->add_parts($parts_result, $repid, $data['assid']);
                    //==========================================短信 START==========================================
                    if ($settingData) {
                        //有开启短信
                        //获取有出库权限的用户
                        $UserData = $ToolMod->getUser('partsOutWare', $data['departid']);
                        if ($settingData['repairPartsOutApply']['status'] == C('OPEN_STATUS') && $UserData) {
                            //通知库管出库 开启
                            $phone = $this->formatPhone($UserData);
                            $sms   = $this->formatSmsContent($settingData['repairPartsOutApply']['content'], $data);
                            $ToolMod->sendingSMS($phone, $sms, $this->Controller, $repid);
                        }
                    }
                    //==========================================短信 END==========================================
                    //配件出库
                    $moduleName     = 'Repair';
                    $controllerName = 'RepairParts';
                    $actionName     = 'partsOutWare';
                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**申请人：**' . session('username');
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**' . $assInfo['assets'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**' . $assInfo['assnum'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备型号：**' . $assInfo['model'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**使用科室：**' . $assInfo['department'];
                        $feishu_fields[]       = $fd;

                        //按钮区域
                        $act['tag']  = 'button';
                        $act['type'] = 'primary';
                        $act['url']  = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME') . '/RepairParts/partsOutWare?repid=' . $repid;;
                        $act['text']['tag']     = 'plain_text';
                        $act['text']['content'] = '查看详情';
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
                        $card_data['header']['title']['content'] = '收到一个维修配件出库申请';
                        $card_data['header']['title']['tag']     = 'plain_text';

                        $toUser = $this->getToUser(session('userid'), $assInfo['departid'], $moduleName,
                            $controllerName, $actionName);
                        foreach ($toUser as $k => $v) {
                            $this->send_feishu_card_msg($v['openid'], $card_data);
                        }
                        //==========================================飞书 END==========================================
                    } else {
                        //发送微信消息给配件出库人进行配件出库操作
                        //==========================================维修 START==========================================
                        $moduleModel = new ModuleModel();
                        $wx_status   = $moduleModel->decide_wx_login();
                        if ($wx_status) {
                            if (C('USE_VUE_WECHAT_VERSION')) {
                                $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/RepairParts/partsOutWare?repid=' . $repid;
                            } else {
                                $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/RepairParts/partsOutWare.html?repid=' . $repid;
                            }

                            /** @var UserModel[] $users */
                            $users = $this->getToUser(session('userid'), $assInfo['departid'], $moduleName, $controllerName, $actionName);
                            $openIds = array_column($users, 'openid');
                            $openIds = array_filter($openIds);
                            $openIds = array_unique($openIds);

                            $messageData = [
                                'thing6'            => $assInfo['department'],// 设备科室
                                'thing7'            => $assInfo['assets'],// 设备名称
                                'character_string8' => $assInfo['assnum'],// 设备编码
                                'character_string4' => $data['repnum'],// 维修单号
                                'const5'            => '待出库',// 审批状态
                            ];

                            foreach ($openIds as $openId) {
                                Weixin::instance()->sendMessage($openId, '配件出库审批通知', $messageData, $redecturl);
                            }
                        }
                        //==========================================微信 END==========================================
                    }
                    $result['msg'] = '产生新配件，请等待出库！';
                    break;
                case C('REPAIR_MAINTENANCE_COMPLETION'):
                    //结束维修 通知验收
                    //==========================================短信 START==========================================
                    if ($settingData) {
                        //有开启短信
                        $UserData = $ToolMod->getUser('checkRepair', $data['departid']);
                        if ($settingData['checkRepair']['status'] == C('OPEN_STATUS') && $UserData) {
                            //通知报修用户验收 开启
                            $phone = $this->formatPhone($UserData);
                            $sms   = $this->formatSmsContent($settingData['checkRepair']['content'], $data);
                            $ToolMod->sendingSMS($phone, $sms, $this->Controller, $repid);
                        }
                    }
                    //==========================================短信 END==========================================
                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**维修单号：**' . $data['repnum'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**' . $assInfo['assets'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**' . $assInfo['assnum'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**使用科室：**' . $assInfo['department'];
                        $feishu_fields[]       = $fd;

                        //按钮区域
                        $act['tag']             = 'button';
                        $act['type']            = 'primary';
                        $act['url']             = C('APP_NAME') . C('FS_FOLDER_NAME') . '/#' . C('FS_NAME ') . '/Repair/checkRepair?repid=' . $repid;
                        $act['text']['tag']     = 'plain_text';
                        $act['text']['content'] = '验收';
                        $feishu_actions[]       = $act;

                        $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag']         = 'div';
                        $card_data['elements'][0]['fields']      = $feishu_fields;
                        $card_data['elements'][1]['tag']         = 'hr';
                        $card_data['elements'][2]['actions']     = $feishu_actions;
                        $card_data['elements'][2]['layout']      = 'bisected';
                        $card_data['elements'][2]['tag']         = 'action';
                        $card_data['header']['template']         = 'indigo';
                        $card_data['header']['title']['content'] = '维修设备验收提醒';
                        $card_data['header']['title']['tag']     = 'plain_text';

                        //查询报修人员openid
                        $applicant = $this->DB_get_one('user', 'telephone,openid', ['username' => $data['applicant']]);
                        if ($applicant['openid']) {
                            $this->send_feishu_card_msg($applicant['openid'], $card_data);
                        }
                        //==========================================飞书 END==========================================
                    } else {
                        //发送微信消息给报修人进行验收操作
                        $moduleModel = new ModuleModel();
                        $wx_status   = $moduleModel->decide_wx_login();
                        if ($wx_status) {
                            if (C('USE_VUE_WECHAT_VERSION')) {
                                $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME ') . '/Repair/checkRepair?repid=' . $repid;
                            } else {
                                $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Repair/checkRepair.html?repid=' . $repid;
                            }
                            //查询报修人员openid
                            $applicant = $this->DB_get_one('user', 'telephone,openid',
                                ['username' => $data['applicant']]);

                            if ($applicant['openid']) {
                                Weixin::instance()->sendMessage($applicant['openid'], '设备维修通知', [
                                    'thing3'             => $assInfo['department'],// 科室
                                    'thing6'             => $assInfo['assets'],// 设备名称
                                    'character_string12' => $assInfo['assnum'],// 设备编码
                                    'character_string35' => $data['repnum'],// 维修单号
                                    'const17'            => '已修复，请验收',// 工单状态
                                ], $redecturl);
                            }
                        }
                    }

                    $result['msg'] = '维修结束，等待验收';

                    //结束维修  推送消息到大屏幕
                    $push_messages[] = [
                        'type_action' => 'edit',
                        'type_name'   => C('SCREEN_REPAIR'),
                        'assets'      => $assInfo['assets'],
                        'assnum'      => $assInfo['assnum'],
                        'department'  => $assInfo['department'],
                        'remark'      => $data['remark'],
                        'status'      => $addData['status'],
                        'status_name' => '待验收',
                        'time'        => date('Y-m-d H:i'),
                        'username'    => session('username') . '(' . session('telephone') . ')',
                    ];
                    push_messages($push_messages);
                    break;

                default:
                    $result['msg'] = '提交成功';
                    break;
            }
            $result['status'] = 1;
        } else {
            $result['status'] = -2;
            $result['msg']    = '提交失败!';
        }
        return $result;
    }

    //设备验收列表
    public function examine()
    {
        $order           = I('POST.order');
        $sort            = I('POST.sort');
        $assets          = I('POST.assetsName');
        $engineer        = I('POST.engineer');
        $assetsNum       = I('POST.assetsNum');
        $assetsCat       = I('POST.assetsCat');
        $assetsDep       = I('POST.assetsDep');
        $startDate       = I('POST.startDate');
        $endDate         = I('POST.endDate');
        $examineStatus   = I('POST.examineStatus');
        $repair_category = I('POST.repair_category');
        $hospital_id     = I('POST.hospital_id');
        $limit           = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page            = I('post.page') ? I('post.page') : 1;
        $offset          = ($page - 1) * $limit;
        if (!session('departid')) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['B.departid'][] = ['IN', session('departid')];
        switch ($sort) {
            case 'appTime':
                $sort = 'A.applicant_time';
                break;
            case 'engineerTime':
                $sort = 'A.engineer_time';
                break;
            case 'overdate':
                $sort = 'A.overdate';
                break;
            case 'wHours':
                $sort = 'A.working_hours';
                break;
            case 'pNum':
                $sort = 'A.part_num';
                break;
            case 'ePrice':
                $sort = 'A.expect_price';
                break;
            case 'aPrice':
                $sort = 'A.actual_price';
                break;
            case 'pPrice':
                $sort = 'A.part_total_price';
                break;
            default:
                $sort = 'A.status';
                break;
        }
        if (!$order) {
            $order = 'ASC';
        }
        if ($assets) {
            //设备名称搜索
            $where['A.assets'] = ['LIKE', '%' . $assets . '%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['A.assnum'] = ['LIKE', '%' . $assetsNum . '%'];

        }
        if ($repair_category) {
            $where['A.repair_category'] = $repair_category;
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
            $where['B.departid'][] = ['EQ', $assetsDep];
        }
        if ($examineStatus) {
            $where['A.status'] = ['EQ', $examineStatus];
        } else {
            $where['A.status'] = ['EGT', 7];
        }
        if ($startDate) {
            //报修时间--开始
            $where['A.applicant_time'][] = ['GT', (strtotime($startDate) - 1)];
        }
        if ($endDate) {
            //报修时间--结束
            $where['A.applicant_time'][] = ['LT', (strtotime($endDate) + 24 * 3600)];
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($engineer) {
            $where['A.engineer'] = $engineer;
        }
        $join[0] = ' LEFT JOIN __ASSETS_INFO__ AS B ON A.assid = B.assid';
        $total   = $this->DB_get_count_join('repair', 'A', $join, $where);
        $fields  = 'A.archives_num,A.repid,A.applicant as appUser,A.applicant_time as appTime,A.repnum,A.status,A.breakdown,A.repair_type as rType,
            A.expect_price as ePrice,A.actual_price as aPrice,A.part_total_price as pPrice,A.overdate,A.fault_type as fauleType,
            A.fault_problem as faultProblem,A.engineer,A.engineer_time as engineerTime,A.repair_remark as rRemark,A.part_num as pNum,
            A.working_hours as wHours,B.assid,B.acid,B.assets,B.catid,B.assnum,A.over_status,
            B.assorignum,B.model,B.departid,A.part_total_price as pathPrice';
        $asArr   = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        if (!$asArr) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $catname    = [];
        $departname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $menuData = get_menu($this->MODULE, 'Repair', 'checkRepair');
        //当前用户可验收科室
        foreach ($asArr as $k => $v) {
            $asArr[$k]['category']     = $catname[$v['catid']]['category'];
            $asArr[$k]['department']   = $departname[$v['departid']]['department'];
            $asArr[$k]['appTime']      = getHandleTime($v['appTime']);
            $asArr[$k]['overdate']     = getHandleTime($v['overdate']);
            $asArr[$k]['engineerTime'] = getHandleTime($v['engineerTime']);
            $asArr[$k]['operation']    = '<div class="layui-btn-group">';
            $asArr[$k]['operation']    .= $this->returnListLink('设备详情', C('SHOWASSETS_ACTION_URL'), 'showAssets',
                C('BTN_CURRENCY') . ' layui-btn-primary');
            $detailsUrl                = get_url() . '?action=showRepairDetails&assid=' . $v['assid'] . '&repid=' . $v['repid'];
            if ($asArr[$k]['status'] == C('REPAIR_MAINTENANCE_COMPLETION')) {
                if ($menuData) {
                    $asArr[$k]['operation'] .= $this->returnListLink('待验收', $menuData['actionurl'], 'check',
                        C('BTN_CURRENCY'));
                } else {
                    $asArr[$k]['operation'] .= $this->returnListLink('待验收', $detailsUrl, 'showDetails',
                        C('BTN_CURRENCY') . ' layui-btn-warm');
                }
            } else {
                $printReportUrl  = get_url() . '?action=printReport&assid=' . $v['assid'] . '&repid=' . $v['repid'];
                $uploadReportUrl = get_url() . '?action=uploadReport&repid=' . $v['repid'];

                if ($v['over_status'] == C('SUCCESS_STATUS')) {
                    $asArr[$k]['operation'] .= $this->returnListLink('已修复', $detailsUrl, 'showDetails',
                        C('BTN_CURRENCY') . ' layui-btn-normal');
                } else {
                    $asArr[$k]['operation'] .= $this->returnListLink('未修复', $detailsUrl, 'showDetails',
                        C('BTN_CURRENCY') . ' layui-btn-danger');
                }
                $asArr[$k]['operation'] .= $this->returnListLink('打印报告', $printReportUrl, 'printReport',
                    C('BTN_CURRENCY'));
                $file                   = $this->DB_get_one('repair_file', 'file_id',
                    ['repid' => $v['repid'], 'is_delete' => 0]);
                if ($file) {
                    $asArr[$k]['operation'] .= $this->returnListLink('查看报告', $uploadReportUrl, 'uploadReport',
                        C('BTN_CURRENCY') . ' layui-btn-normal');
                } else {
                    $asArr[$k]['operation'] .= $this->returnListLink('上传报告', $uploadReportUrl, 'uploadReport',
                        C('BTN_CURRENCY') . ' layui-btn-warm');
                }
            }
            $asArr[$k]['operation'] .= '</div>';
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["rows"]   = $asArr;
        $result["code"]   = 200;
        return $result;
    }

    //设备验收操作
    public function checkRepair()
    {
        $repid = I('POST.repid');
        $this->checkstatus(judgeNum($repid), '非法参数');
        $refileds = 'repnum,assnum,assets,assid,repid,status,over_status,applicant,departid,response,response_tel,applicant_tel,approve_status,engineer';
        $data     = $this->DB_get_one('repair', $refileds, ['repid' => $repid]);
        //判断是否已验收
        if ($data['status'] == C('REPAIR_ALREADY_ACCEPTED')) {
            die(json_encode(['status' => -400, 'msg' => '请勿重复操作']));
        }
        $res_status_name             = I('POST.repaired') == 1 ? '已修复' : '未修复';
        $data['service_attitude']    = I('POST.attitude');
        $data['technical_level']     = I('POST.technical');
        $data['response_efficiency'] = I('POST.efficiency');
        $data['over_status']         = I('POST.repaired');
        $data['check_remark']        = I('POST.checkRemark');
        $data['status']              = C('REPAIR_ALREADY_ACCEPTED');
        $data['editdate']            = time();
        if (I('POST.repair_check')) {
            $data['checkdate'] = strtotime(I('POST.repair_check'));
        } else {
            $data['checkdate'] = time();
        }
        $data['checkperson'] = session('username');
        $save                = $this->updateData('repair', $data, ['repid' => $repid]);
        if ($save !== false) {
            //日志行为记录文字
            $log['repnum'] = $data['repnum'];
            $text          = getLogText('checkRepairLogText', $log);
            $this->addLog('repair', M()->getLastSql(), $text, $repid);
            $infodata['status']         = C('ASSETS_STATUS_USE');
            $infodata['lastrepairtime'] = time();
            $this->updateData('assets_info', $infodata, ['assid' => $data['assid']]);
            //记录报修信息到状态变更表
            $asModel = new AssetsInfoModel();
            $asModel->updateAssetsStatus($data['assid'], $infodata['status'], $remark = '修复验收');
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($settingData) {
                //有开启短信
                $data['over_status']    = $data['over_status'] == C('REPAIR_OVER_STATUS_SUCCESSFUL') ? '已修复' : '未修复';
                $data['approve_status'] = $data['is_adopt'] == C('STATUS_APPROE_FAIL') ? '未通过' : '通过';
                $departname             = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $data['department'] = $departname[$data['departid']]['department'];
                $ToolMod            = new ToolController();
                if ($settingData['checkRepairStatus']['status'] == C('OPEN_STATUS') && $data['response_tel']) {
                    //通知已有工程师接单 开启
                    $sms = $this->formatSmsContent($settingData['checkRepairStatus']['content'], $data);
                    $ToolMod->sendingSMS($data['response_tel'], $sms, $this->Controller, $repid);
                }
            }
            //==========================================短信 END==========================================
            if (C('USE_FEISHU') === 1) {
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**维修单号：**' . $data['repnum'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备名称：**' . $data['assets'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备编码：**' . $data['assnum'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**验收人：**' . session('username');
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**验收结果：**' . $res_status_name;
                $feishu_fields[]       = $fd;

                $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag']         = 'div';
                $card_data['elements'][0]['fields']      = $feishu_fields;
                $card_data['header']['template']         = 'indigo';
                $card_data['header']['title']['content'] = '维修设备验收结果通知';
                $card_data['header']['title']['tag']     = 'plain_text';

                //查询工程师人员openid
                $applicant = $this->DB_get_one('user', 'telephone,openid', ['username' => $data['engineer']]);
                if ($applicant['openid']) {
                    $this->send_feishu_card_msg($applicant['openid'], $card_data);
                }
                //==========================================飞书 END==========================================
            } else {
                $moduleModel = new ModuleModel();
                $wx_status   = $moduleModel->decide_wx_login();
                if ($wx_status) {
                    //发送微信消息给工程师，告知验收结果（验收结果通知）
                    //查询工程师人员openid
                    $engineer = $this->DB_get_one('user', 'telephone,openid', ['username' => $data['engineer']]);

                    if ($engineer['openid']) {
                        Weixin::instance()->sendMessage($engineer['openid'], '设备维修通知', [
                            'thing3'             => $data['department'],// 科室
                            'thing6'             => $data['assets'],// 设备名称
                            'character_string12' => $data['assnum'],// 设备编码
                            'character_string35' => $data['repnum'],// 维修单号
                            'const17'            => '已验收',// 工单状态
                        ]);
                    }
                }
            }
            return ['status' => 1, 'msg' => '验收成功'];
        } else {
            return ['status' => 1, 'msg' => '验收失败'];
        }
    }

    //设备维修进程
    public function progress()
    {
        $limit           = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page            = I('post.page') ? I('post.page') : 1;
        $offset          = ($page - 1) * $limit;
        $order           = I('POST.order');
        $sort            = I('POST.sort');
        $assets          = I('POST.progressAssName');
        $assetsNum       = I('POST.progressAssNum');
        $assetsDep       = I('POST.progressAssetsDep');
        $startDate       = I('POST.progressStartDate');
        $endDate         = I('POST.progressEndDate');
        $repairStatus    = I('POST.progressRepairStatus');
        $engineer        = I('POST.engineer');
        $applicant       = I('POST.progressApplicant');
        $repair_category = I('POST.repair_category');
        $hospital_id     = I('POST.hospital_id');
        $departids       = session('departid');
        if (!session('departid')) {
            $result['msg']  = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $where['C.departid'] = ['in', session('departid')];
        if (empty($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$sort) {
            $sort = 'A.repid ';
        }
        if (!$order) {
            $order = 'desc';
        }
        if ($sort == 'applicant_time') {
            $sort = 'A.applicant_time';
        }
        if ($hospital_id) {
            $where['C.hospital_id'] = $hospital_id;
        } else {
            $where['C.hospital_id'] = session('current_hospitalid');
        }
        if ($assets) {
            //设备名称搜索
            $where['C.assets'] = ['like', '%' . $assets . '%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['C.assnum'] = ['like', '%' . $assetsNum . '%'];
        }
        if ($assetsDep) {
            //部门搜索
            $where['C.departid'] = ['like', '%' . $assetsDep . '%'];
        }
        if ($applicant) {
            //报修人搜索
            $where['A.applicant'] = $applicant;
        }
        if ($repair_category) {
            $where['A.repair_category'] = $repair_category;
        }
        if ($engineer) {
            //工程师搜索
            $where['A.engineer'] = $engineer;
        }
        if ($repairStatus) {
            switch ($repairStatus) {
                case 1:
                    if (session('isSuper')) {
                        $where['A.status']   = 1;
                        $where['is_assign']  = 1;
                        $whereOR['A.status'] = 1;
                        $whereOR['_logic']   = 'or';
                        $where['_complex']   = $whereOR;
                    } else {
                        $where['A.status']        = 1;
                        $where['is_assign']       = 1;
                        $where['assign_engineer'] = session('username');
                        $whereOR['A.status']      = 1;
                        $whereOR['_logic']        = 'or';
                        $where['_complex']        = $whereOR;
                    }
                    break;
                case 2:
                    $whereOR[0]['A.status'] = 2;
                    $whereOR[1]['A.status'] = 4;
                    $whereOR['_logic']      = 'or';
                    $where['_complex']      = $whereOR;
                    break;
                case 4:
                    $where['A.status'] = 5;
                    break;
                case 5:
                    $where['A.status'] = 6;
                    break;
                case 6:
                    $where['A.status'] = 7;
                    break;
                case  9:
                    $where['A.status']  = 1;
                    $where['is_assign'] = 0;
                    break;
            }
        } else {
            $where['A.status'] = ['BETWEEN', [C('REPAIR_HAVE_REPAIRED'), C('REPAIR_MAINTENANCE_COMPLETION')]];
        }
        if ($startDate) {
            //报修时间--开始
            $where['A.applicant_time'][] = ['gt', (strtotime($startDate) - 1)];
        }
        if ($endDate) {
            //报修时间--结束
            $where['A.applicant_time'][] = ['lt', (strtotime($endDate) + 24 * 3600)];
        }
        $join   = 'LEFT JOIN sb_assets_info AS C ON A.assid = C.assid';
        $total  = $this->DB_get_count_join('repair', 'A', $join, $where);
        $fields = 'C.assid,C.acid,C.assets,C.catid,C.assnum,C.assorignum,C.model,C.departid,C.opendate,A.repid,
            A.notice_time,A.approve_status,A.expect_price,A.is_scene,A.repid,A.applicant,A.applicant_time,A.assign,
            A.assign_time,A.response,A.response_date,A.engineer,A.engineer_time,A.checkperson,A.checkdate,A.repnum,
            A.status,A.overhauldate,A.examine_user,A.examine_time,A.is_assign,A.assign_engineer,A.current_approver,A.from';
        $asArr  = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        //查询当前用户是否有派单权限
        $assignedMenu = get_menu($this->MODULE, $this->Controller, 'assigned');
        //查询当前用户是否有接单权限
        $acceptMenu = get_menu($this->MODULE, $this->Controller, 'accept');
        //查询当前用户是否有报价权限
        $offerMenu = get_menu($this->MODULE, $this->Controller, 'doOffer');
        //查询当前用户是否有审核权限
        $approveMenu = get_menu($this->MODULE, $this->Controller, 'addApprove');
        //查询当前用户是否有维修权限
        $startRepairMenu = get_menu($this->MODULE, $this->Controller, 'startRepair');
        //查询当前用户是否有验收权限
        $checkMenu  = get_menu($this->MODULE, $this->Controller, 'checkRepair');
        $username   = session('username');
        $isSuper    = session('isSuper');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($asArr as &$one) {
            if ($one['notice_time']) {
                $one['notice'] = $one['engineer'];
            } else {
                $one['notice'] = '';
            }
            $repidhref = '?repid=' . $one['repid'] . '&assid=' . $one['assid'];
            if ($one['response'] == null) {
                $one['response'] = '';
            }
            $one['assign']         = $one['assign'] == 'system' ? '系统派单' : $one['assign'];
            $one['department']     = $departname[$one['departid']]['department'];
            $one['opendate']       = HandleEmptyNull($one['opendate']);
            $one['applicant_time'] = getHandleTime($one['applicant_time']);
            $one['assign_time']    = getHandleTime($one['assign_time']);
            $one['response_date']  = getHandleTime($one['response_date']);
            $one['notice_time']    = getHandleTime($one['notice_time']);
            $one['engineer_time']  = getHandleTime($one['engineer_time']);
            $one['examine_time']   = getHandleTime($one['examine_time']);
            $one['checkdate']      = getHandleTime($one['checkdate']);
            $one['overhauldate']   = getHandleTime($one['overhauldate']);
            $one['engineer']       = $one['engineer'] ? $one['engineer'] : '-';
            $one['checkperson']    = $one['checkperson'] ? $one['checkperson'] : '-';
            $one['examine_user']   = $one['examine_user'] ? $one['examine_user'] : '-';
            if ($one['status'] > 2) {
                $one['overhaulUser'] = $one['response'];
            } else {
                $one['overhaulUser'] = '-';
            }
            switch ($one['status']) {
                case 1:
                    if ($one['is_assign'] == 1) {
                        $one['status'] = 2;
                        if ($acceptMenu) {
                            if ((($one['is_assign'] == 0 or $one['assign_engineer'] == '' or $one['assign_engineer'] == $username) and ($one['response'] == '' or $one['response'] = $username)) or $isSuper) {
                                $one['href'] = $this->returnListLink('接单', $acceptMenu['actionurl'] . $repidhref,
                                    'progressData', 'layui-btn layui-btn-xs layui-btn-normal', '', '');
                            } else {
                                $one['href'] = '接单';
                            }
                        } else {
                            $one['href'] = '接单';
                        }
                    } else {
                        $one['status'] = 1;
                        if ($assignedMenu && $one['from']) {
                            $one['href'] = $this->returnListLink('派工', $assignedMenu['actionurl'] . $repidhref,
                                'progressData', 'layui-btn layui-btn-xs layui-btn-normal', '', '');
                        } else {
                            $one['href'] .= $this->returnListLink('指派', '', '',
                                C('BTN_CURRENCY') . ' layui-btn-disabled', '', 'title="微信端报修不能人工指派"');
                        }
                        if ($acceptMenu) {
                            if ((($one['is_assign'] == 0 or $one['assign_engineer'] == '' or $one['assign_engineer'] == $username) and ($one['response'] == '' or $one['response'] = $username)) or $isSuper) {
                                $one['href2'] = $this->returnListLink('接单', $acceptMenu['actionurl'] . $repidhref,
                                    'progressData', 'layui-btn layui-btn-xs layui-btn-normal', '', '');
                            } else {
                                $one['href2'] = '接单';
                            }
                        } else {
                            $one['href2'] = '接单';
                        }
                    }
                    break;
                case 2:
                    $one['status'] = 3;
                    if ($acceptMenu) {
                        if ($one['response'] == $username or $isSuper) {
//
                            $one['href'] = $this->returnListLink('检修', $acceptMenu['actionurl'] . $repidhref,
                                'progressData', 'layui-btn layui-btn-xs layui-btn-normal', '', '');
                        } else {
                            $one['href'] = '检修';
                        }
                    } else {
                        $one['href'] = '检修';
                    }
                    break;
                case 3:
                    $one['href'] = '配件待出库';
                    break;
                case 4:
                    if ($offerMenu) {
                        $one['href'] = $this->returnListLink('报价', $offerMenu['actionurl'] . $repidhref,
                            'progressData', 'layui-btn layui-btn-xs layui-btn-normal', '', '');
                    } else {
                        $one['href'] = '检修';
                    }
                    break;
                case 5:
                    if ($approveMenu && $one['current_approver']) {
                        $current_approver     = explode(',', $one['current_approver']);
                        $current_approver_arr = [];
                        foreach ($current_approver as &$current_approver_value) {
                            $current_approver_arr[$current_approver_value] = true;
                        }
                        if ($current_approver_arr[session('username')] && $one['approve_status'] != 2) {
                            $one['href'] = $this->returnListLink('审核', $approveMenu['actionurl'] . $repidhref,
                                'progressData', 'layui-btn layui-btn-xs layui-btn-normal', '', '');
                        } else {
                            $one['href'] = '维修审核';
                        }
                    } else {
                        $one['href'] = '维修审核';
                    }
                    break;
                case 6:
                    if ($startRepairMenu) {
                        if ($one['response'] == $username or $isSuper) {
                            $one['href'] = $this->returnListLink('继续维修', $startRepairMenu['actionurl'] . $repidhref,
                                'progressData', 'layui-btn layui-btn-xs layui-btn-normal', '', '');
                        } else {
                            $one['href'] = '维修中';
                        }
                    } else {
                        $one['href'] = '维修中';
                    }
                    break;
                case 7:
                    if ($checkMenu) {
                        if ($one['applicant'] == $username or $isSuper) {
                            $one['href'] = $this->returnListLink('验收', $checkMenu['actionurl'] . $repidhref,
                                'progressData', 'layui-btn layui-btn-xs layui-btn-normal', '', '');
                        } else {
                            $one['href'] = '科室验收';
                        }
                    } else {
                        $one['href'] = '科室验收';
                    }
                    break;
            }
            if ($one['status'] >= 4) {
                $one['status']--;
            }
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["rows"]   = $asArr;
        $result["code"]   = 200;
        return $result;
    }

    //检修过程中补充故障问题故障描述
    public function addTypeAndProblem()
    {
        //判断故障类型
        $this->checkstatus(judgeEmpty(I('POST.type')), '请输入故障类型');
        $addType['title'] = trim(I('post.type'));
        //判断故障问题
        $this->checkstatus(judgeEmpty(I('POST.problem')), '请输入故障问题');
        $addProblem['title'] = trim(I('post.problem'));
        //判断解决方法
        $this->checkstatus(judgeEmpty(I('POST.solve')), '请输入解决办法');
        $addProblem['solve'] = I('post.solve');
        //是否启用
        $addType['status']    = I('post.status');
        $addProblem['status'] = I('post.status');
        //添加人和添加时间
        $addType['adduser']    = session('username');
        $addProblem['adduser'] = session('username');
        $addType['addtime']    = time();
        $addProblem['addtime'] = time();
        //故障类型备注
        $addType['remark'] = I('post.typeRemark');
        //故障问题备注
        $addProblem['remark'] = I('post.problemRemark');
        //查有没有叫这个的故障类型
        $type = $this->DB_get_one('repair_setting', 'id', ['title' => $addType['title']], '');
        //如果有这个故障类型就添加问题，没有就是新加一个故障类型和一个故障问题
        if ($type['id']) {
            $addProblem['parentid'] = $type['id'];
            $add                    = $this->insertData('repair_setting', $addProblem);
            if ($add) {
                return ['status' => 1, 'msg' => '添加成功'];
            } else {
                return ['status' => -99, 'msg' => '添加失败'];
            }

        } else {
            $addType['parentid']    = 0;
            $newid                  = $this->insertData('repair_setting', $addType);
            $addProblem['parentid'] = $newid;
            $add                    = $this->insertData('repair_setting', $addProblem);
            if ($add) {
                return ['status' => 1, 'msg' => '添加成功'];
            } else {
                return ['status' => -99, 'msg' => '添加失败'];
            }
        }
    }

    //查询维修记录情况
    public function RepairRecord()
    {
        $assid = I('POST.assid');
        if (!$assid) {
            die(json_encode(['status' => -1, 'msg' => '非法操作']));

        }
        $lists = $this->getRepairRecordByAssid($assid);
        if (!$lists) {
            return $lists;
        }
        //获取设备名称和assnum
        $assetsInfo = $this->DB_get_one('assets_info', 'assid,assets,assnum',
            ['assid' => $assid, 'is_delete' => C('NO_STATUS')]);
        //组织数据
        $res = $this->handleRepairRecordData($lists);
        //获取报表标题
        $res['reportTitle'] = '设备维修记录';
        //获取报表搜索条件范围
        $res['reportTips'] = '设备名称：' . $assetsInfo['assets'] . ' /  设备编码：' . $assetsInfo['assnum'];
        return $res;
    }

    //批量导入
    public function addRepair()
    {
        $insertData     = [];
        $assets         = trim(I('POST.assets'), ',');
        $assnum         = trim(I('POST.assnum'), ',');
        $assorignum     = trim(I('POST.assorignum'), ',');
        $model          = trim(I('POST.model'), ',');
        $applicant      = trim(I('POST.applicant'), ',');
        $applicant_day  = trim(I('POST.applicant_day'), ',');
        $applicant_time = trim(I('POST.applicant_time'), ',');
        $applicant_tel  = trim(I('POST.applicant_tel'), ',');
        $faultProblem   = trim(I('POST.faultProblem'), ',');

        $breakdown           = trim(I('POST.breakdown'), ',');
        $response            = trim(I('POST.response'), ',');
        $response_date       = trim(I('POST.response_date'), ',');
        $response_tel        = trim(I('POST.response_tel'), ',');
        $engineer            = trim(I('POST.engineer'), ',');
        $engineer_tel        = trim(I('POST.engineer_tel'), ',');
        $assist_engineer     = trim(I('POST.assist_engineer'), ',');
        $assist_engineer_tel = trim(I('POST.assist_engineer_tel'), ',');
        $engineer_time       = trim(I('POST.engineer_time'), ',');
        $overdate_day        = trim(I('POST.overdate_day'), ',');
        $overdate_time       = trim(I('POST.overdate_time'), ',');
        $actual_price        = trim(I('POST.actual_price'), ',');
        $dispose_detail      = trim(I('POST.dispose_detail'), ',');
        $checkperson         = trim(I('POST.checkperson'), ',');
        $checkdate           = trim(I('POST.checkdate'), ',');
        $check_remark        = trim(I('POST.check_remark'), ',');
        $assetsParts         = I('POST.assetsParts');
        $assets              = explode(',', $assets);
        $assnum              = explode(',', $assnum);
        $assorignum          = explode(',', $assorignum);
        foreach ($assorignum as $k => $v) {
            if ($v == '/') {
                unset($assorignum[$k]);
            }
        }
        $model               = explode(',', $model);
        $applicant           = explode(',', $applicant);
        $applicant_day       = explode(',', $applicant_day);
        $applicant_time      = explode(',', $applicant_time);
        $applicant_tel       = explode(',', $applicant_tel);
        $faultProblem        = explode(',', $faultProblem);
        $breakdown           = explode(',', $breakdown);
        $response            = explode(',', $response);
        $response_date       = explode(',', $response_date);
        $response_tel        = explode(',', $response_tel);
        $engineer            = explode(',', $engineer);
        $engineer_tel        = explode(',', $engineer_tel);
        $assist_engineer     = explode(',', $assist_engineer);
        $assist_engineer_tel = explode(',', $assist_engineer_tel);
        $engineer_time       = explode(',', $engineer_time);
        $overdate_day        = explode(',', $overdate_day);
        $overdate_time       = explode(',', $overdate_time);
        $actual_price        = explode(',', $actual_price);
        $dispose_detail      = explode(',', $dispose_detail);
        $checkperson         = explode(',', $checkperson);
        $checkdate           = explode(',', $checkdate);
        $check_remark        = explode(',', $check_remark);
        $allAssnum           = $this->DB_get_all('assets_info', 'assnum,assorignum', ['1'], '', '');
        $assArr              = [];
        foreach ($allAssnum as $k => $v) {
            $assArr[] = $v['assnum'];
        }
        $allAssorignum = [];
        foreach ($allAssnum as $k => $v) {
            $allAssorignum[] = $v['assorignum'];

        }
        foreach ($assnum as $k => $v) {
            if ($v == '' || $v == '--') {
                return ['status' => -1, 'msg' => '资产编号不能为空'];
                break;
            }
            if (!in_array($v, $assArr) and !in_array($assorignum[$k], $allAssorignum)) {
                return ['status' => -1, 'msg' => '不存在编号为' . $v . '的设备'];
            }
            if ($applicant[$k] == '' || $applicant[$k] == '--') {
                return ['status' => -1, 'msg' => '申报人不能为空'];
                break;
            }
            if ($applicant_day[$k] == '' || $applicant_day[$k] == '--') {
                return ['status' => -1, 'msg' => '申报日期不能为空'];
                break;
            }
            if ($applicant_time[$k] == '' || $applicant_time[$k] == '--') {
                return ['status' => -1, 'msg' => '申报时间不能为空'];
                break;
            }
            if ($faultProblem[$k] == '' || $faultProblem[$k] == '--') {
                return ['status' => -1, 'msg' => '故障问题不能为空'];
                break;
            }
            if ($breakdown[$k] == '' || $breakdown[$k] == '--') {
                return ['status' => -1, 'msg' => '故障描述不能为空'];
                break;
            }
            if ($response[$k] == '' || $response[$k] == '--') {
                return ['status' => -1, 'msg' => '接单人不能为空'];
                break;
            }
            if ($response_date[$k] == '' || $response_date[$k] == '--') {
                return ['status' => -1, 'msg' => '接单时间不能为空'];
                break;
            }
            if ($engineer[$k] == '' || $engineer[$k] == '--') {
                return ['status' => -1, 'msg' => '维修工程师不能为空'];
                break;
            }
            if ($engineer_time[$k] == '' || $engineer_time[$k] == '--') {
                return ['status' => -1, 'msg' => '维修开始时间不能为空'];
                break;
            }
            if ($overdate_day[$k] == '' || $overdate_day[$k] == '--') {
                return ['status' => -1, 'msg' => '维修结束日期不能为空'];
                break;
            }
            if ($overdate_time[$k] == '' || $overdate_time[$k] == '--') {
                return ['status' => -1, 'msg' => '维修结束时间不能为空'];
                break;
            }
            if ($dispose_detail[$k] == '' || $dispose_detail[$k] == '--') {
                return ['status' => -1, 'msg' => '处理详情不能为空'];
                break;
            }
            if ($checkperson[$k] == '' || $checkperson[$k] == '--') {
                return ['status' => -1, 'msg' => '验收人不能为空'];
                break;
            }
            if ($checkdate[$k] == '' || $checkdate[$k] == '--') {
                return ['status' => -1, 'msg' => '验收时间不能为空'];
                break;
            }
            $assets[$k]              = ($assets[$k] == '--') ? '' : $assets[$k];
            $model[$k]               = ($model[$k] == '--') ? '' : $model[$k];
            $applicant_tel[$k]       = ($applicant_tel[$k] == '--') ? '' : $applicant_tel[$k];
            $response_tel[$k]        = ($response_tel[$k] == '--') ? '' : $response_tel[$k];
            $engineer_tel[$k]        = ($engineer_tel[$k] == '--') ? '' : $engineer_tel[$k];
            $assist_engineer[$k]     = ($assist_engineer[$k] == '--') ? '' : $assist_engineer[$k];
            $assist_engineer_tel[$k] = ($assist_engineer_tel[$k] == '--') ? '' : $assist_engineer_tel[$k];
            $actual_price[$k]        = ($actual_price[$k] == '--') ? '0' : $actual_price[$k];
            $check_remark[$k]        = ($check_remark[$k] == '--') ? '' : $check_remark[$k];
            $actual_price[$k]        = ($actual_price[$k] == '--') ? '' : $actual_price[$k];
        }
        $i        = 0;
        $Model    = M('Repair');
        $repModel = M('RepairParts');
        $this->startTrans();
        $departname  = [];
        $baseSetting = [];
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $realAssnum = [];
        foreach ($assnum as $k => $v) {
            if (!$v) {
                return ['status' => -1, 'msg' => '设备编码不能为空'];
            }
            if (in_array($v, $assArr)) {
                $realAssnum[] = $v;
            } else {
                return ['status' => -1, 'msg' => '系统不存在设备编码为【' . $v . '】的设备'];
            }
        }
        foreach ($realAssnum as $k => $v) {
            //查询对应设备编码的设备信息
            $assInfo         = [];
            $assInfo         = $this->DB_get_one('assets_info', 'assid,assets,model,departid', ['assnum' => $v]);
            $departID        = $assInfo['departid'];
            $department_name = $departname[$assInfo['departid']]['department'];
            $wx              = $baseSetting['repair']['repair_encoding_rules']['value'];
            if ($wx['prefix']) {
                $insertData['repnum'] = $wx['prefix'] . $insertData['repnum'];
            }
            //暂时未需要配置默认下划线 如果需要配置 这里读配置 todo
            $cut = '_';
            if ($cut) {
                $insertData['repnum'] .= $cut;
            }
            $insertData['repnum']              = $this->getOrgNumber('repair', 'repnum', $wx['prefix'], $cut,
                strtotime($applicant_day[$k]));
            $insertData['assid']               = $assInfo['assid'];
            $insertData['assets']              = $assInfo['assets'];
            $insertData['model']               = $assInfo['model'];
            $insertData['departid']            = $departID;
            $insertData['department']          = $department_name;
            $insertData['assnum']              = $v;
            $insertData['status']              = 8;
            $insertData['repair_type']         = 0;
            $insertData['is_scene']            = 1;
            $insertData['overhauldate']        = strtotime($applicant_day[$k] . ' ' . $applicant_time[$k] . ':00') + 600;
            $insertData['response_date']       = strtotime($applicant_day[$k] . ' ' . $applicant_time[$k] . ':00');
            $insertData['expect_arrive']       = 10;
            $insertData['applicant']           = $applicant[$k];
            $insertData['applicant_time']      = strtotime($applicant_day[$k] . ' ' . $applicant_time[$k] . ':00');
            $insertData['applicant_tel']       = $applicant_tel[$k];
            $insertData['fault_problem']       = htmlspecialchars_decode($faultProblem[$k]);
            $insertData['working_hours']       = timediff(strtotime($overdate_day[$k] . ' ' . $overdate_time[$k] . ':00'),
                strtotime($applicant_day[$k] . ' ' . $applicant_time[$k] . ':00'));
            $insertData['breakdown']           = $breakdown[$k];
            $insertData['response']            = $response[$k];
            $insertData['response_tel']        = $response_tel[$k];
            $insertData['actual_price']        = $actual_price[$k];
            $insertData['engineer']            = $engineer[$k];
            $insertData['engineer_time']       = strtotime($applicant_time[$k] . ' ' . $engineer_time[$k] . ':00');
            $insertData['engineer_tel']        = $engineer_tel[$k];
            $insertData['assist_engineer']     = $assist_engineer[$k];
            $insertData['assist_engineer_tel'] = $assist_engineer_tel[$k];
            $insertData['dispose_detail']      = $dispose_detail[$k];
            $insertData['overdate']            = strtotime($overdate_day[$k] . ' ' . $overdate_time[$k] . ':00');
            $insertData['checkperson']         = $checkperson[$k];
            $insertData['checkdate']           = strtotime($overdate_day[$k] . ' ' . $overdate_time[$k] . ':00');
            $insertData['over_status']         = 1;
            $insertData['service_attitude']    = 0;
            $insertData['technical_level']     = 0;
            $insertData['response_efficiency'] = 0;
            $insertData['check_remark']        = $check_remark[$k];
            $insertData['adddate']             = time();
            $insertId                          = $Model->add($insertData);
            if ($insertId) {
                /*新加匹配故障问题*/
                $thisProblem           = $this->DB_get_one('repair', 'fault_problem', ['repid' => $insertId]);
                $waitFindProblem       = explode('&', $thisProblem['fault_problem']);
                $searchWhere           = [];
                $searchWhere['_logic'] = 'or';
                foreach ($waitFindProblem as $k1 => $v1) {
                    $searchWhere[$k1]['title'] = $v1;
                }
                $faultWhere['_complex'] = $searchWhere;
                $faultTypeId            = $this->DB_get_one('repair_setting',
                    'group_concat(parentId) AS typeId,group_concat(id) AS problemId', $faultWhere);
                if ($faultTypeId['problemId']) {
                    $update['fault_type']    = $faultTypeId['typeId'];
                    $update['fault_problem'] = '';
                    $this->updateData('repair', $update, ['repid' => $insertId]);
                    $addData      = [];
                    $addTypeId    = explode(',', $faultTypeId['typeId']);
                    $addProblemId = explode(',', $faultTypeId['problemId']);
                    for ($i = 0; $i < count($addTypeId); $i++) {
                        $addData[$i]['repid']            = $insertId;
                        $addData[$i]['fault_type_id']    = $addTypeId[$i];
                        $addData[$i]['fault_problem_id'] = $addProblemId[$i];
                    }
                    $this->insertDataALL('repair_fault', $addData);
                }
                /**/
                $parts  = trim($assetsParts[$v][$k]['parts'], ',');
                $parts  = explode(',', $parts);
                $partsM = trim($assetsParts[$v][$k]['part_model'], ',');
                $partsM = explode(',', $partsM);
                $partsP = trim($assetsParts[$v][$k]['part_price'], ',');
                $partsP = explode(',', $partsP);
                $partsN = trim($assetsParts[$v][$k]['part_num'], ',');
                $partsN = explode(',', $partsN);
                $j      = 0;
                $ptotal = 0;
                $ntotal = 0;
                $parr   = [];
                foreach ($parts as $k1 => $v1) {
                    if ($v1) {
                        $parr[$j]['repid']      = $insertId;
                        $parr[$j]['parts']      = $v1;
                        $parr[$j]['part_model'] = $partsM[$k1] == '--' ? '' : $partsM[$k1];
                        $parr[$j]['part_num']   = $partsN[$k1];
                        $ptotal                 += $partsN[$k1];
                        $parr[$j]['part_price'] = $partsP[$k1];
                        $parr[$j]['price_sum']  = ($partsN[$k1] * $partsP[$k1]);
                        $ntotal                 += $parr[$j]['price_sum'];
                        $parr[$j]['status']     = 1;
                        $parr[$j]['adduser']    = session('username');
                        $parr[$j]['adddate']    = time();
                        $j++;
                    }
                }
                if ($parr) {
                    $pid = $repModel->addAll($parr);
                    if (!$pid) {
                        $this->rollback();
                        return ['status' => -1, 'msg' => '保存数据失败'];
                    }
                    $Model->where('repid=' . $insertId)->save(['part_num' => $ptotal, 'part_total_price' => $ntotal]);
                }
            } else {
                $this->rollback();
                return ['status' => -1, 'msg' => '保存数据失败'];
            }
        }
        $this->commit();
        //继续返回下一批数据
        $repairData = F('repairData');
        $parts      = F('repairParts');
        $arr        = [];
        $i          = 0;
        foreach ($repairData as $k => $v) {
            if ($i < $this->len && $v) {
                $arr[] = $v;
                $i++;
                unset($repairData[$k]);
            }
        }
        F('repairData', $repairData);
        //只返回对应的配件数据
        $parts = $this->returnLimitPatarts($arr, $parts);
        return ['status' => 1, 'msg' => '保存成功', 'data' => $arr, 'parts' => $parts];
    }

//功能：计算两个时间戳之间相差的日时分秒
//$begin_time 开始时间戳
//$end_time 结束时间戳
    function timediff($begin_time, $end_time)
    {
        if ($begin_time < $end_time) {
            $starttime = $begin_time;
            $endtime   = $end_time;
        } else {
            $starttime = $end_time;
            $endtime   = $begin_time;
        }

//计算天数
        $timediff = $endtime - $starttime;
        $days     = intval($timediff / 86400);
//计算小时数
        $remain = $timediff % 86400;
        $hours  = intval($remain / 3600);
//计算分钟数
        $remain = $remain % 3600;
        $mins   = intval($remain / 60);
//计算秒数
        $secs = $remain % 60;
        $res  = ["day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs];
        return $res;
    }

    /*
    * 转至报修
    * @params1 $assetnum string 设备编号
    * @params2 $breakdown string 故障描述
    * @params3 $applicant string 报修人
    * @params4 $applicant_time int 报修时间
    * @params5 $remark string 报修备注
    */
    public function addPatrolRepair($assetnum, $breakdown, $applicant, $applicant_time, $remark)
    {
        $where   = "A.assnum='$assetnum'";
        $join[0] = 'LEFT JOIN sb_assets_factory AS B ON B.afid=A.afid';
        $join[2] = 'LEFT JOIN sb_assets_contract AS C ON C.acid=A.acid';
        $fileds  = 'A.assnum,A.model,A.assets,B.factory,A.catid,A.departid,C.buy_date,A.guarantee_date,A.factorynum,
            A.assid,A.opendate,A.buy_price';
        //查询设备详情
        $asArr              = $this->DB_get_one_join('assets_info', 'A', $fileds, $join, $where);
        $asArr['guarantee'] = HandleEmptyNull($asArr['guarantee_date']);
        $asArr['opendate']  = HandleEmptyNull($asArr['opendate']);
        $asArr['buy']       = getHandleTime($asArr['buy_date']);
        if (time() <= $asArr['guarantee_date']) {
            $data['is_guarantee'] = C('YES_STATUS');
        } else {
            $data['is_guarantee'] = C('NO_STATUS');
        }
        $data['breakdown']        = $breakdown;
        $data['applicant_remark'] = $remark;
        $data['assets']           = $asArr['assets'];
        $data['assid']            = $asArr['assid'];
        $data['assnum']           = $assetnum;
        $data['assprice']         = $asArr['buy_price'];
        $data['factorynum']       = $asArr['factorynum'];
        $data['factory']          = $asArr['factory'];
        $data['model']            = $asArr['model'];
        $data['applicant']        = $applicant;
        $user                     = $this->DB_get_one('user', 'telephone', "username='$applicant'");
        $data['applicant_tel']    = $user['telephone'];
        $data['applicant_time']   = $applicant_time;
        $data['adddate']          = time();
        $data['departid']         = $asArr['departid'];
        $data['assign_time']      = time();
        $data['status']           = C('REPAIR_HAVE_REPAIRED');
        $data['opendate']         = $asArr['opendate'];
        //维修单来源 pc端 1 微信端0
        $data['from'] = I('post.from') ? I('post.from') : 0;
        $engineer     = $this->getEngineer($asArr['assid']);
        if ($engineer) {
            $data['assign_time']     = time();
            $data['assign_engineer'] = $engineer['username'];
            $data['is_assign']       = C('YES_STATUS');
        }
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $wx = $baseSetting['repair']['repair_encoding_rules']['value'];
        if ($wx['prefix']) {
            $data['repnum'] = $wx['prefix'] . $data['repnum'];
        }
        //暂时未需要配置默认下划线 如果需要配置 这里读配置 todo
        $cut = '_';
        if ($cut) {
            $data['repnum'] .= $cut;
        }
        $data['repnum'] = $this->getOrgNumber('repair', 'repnum', $wx['prefix'], $cut);
        $add            = $this->insertData('repair', $data);
        //==========================================短信 START==========================================
        $settingData = $this->checkSmsIsOpen($this->Controller);
        if ($settingData) {
            //有开启短信
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $data['department'] = $departname[$data['departid']]['department'];
            $ToolMod            = new ToolController();
            if ($engineer) {
                //有自动派工 记录被指派工程师电话
                $acceptPhone = $engineer['telephone'];
            } else {
                //无自动派工 通知派工人员派单
                $UserData = $ToolMod->getUser('assigned', $data['departid']);
                if ($settingData['assigned']['status'] == C('OPEN_STATUS') && $UserData) {
                    //通知派工员分配维修单 开启
                    $phone = $this->formatPhone($UserData);
                    $sms   = $this->formatSmsContent($settingData['assigned']['content'], $data);
                    $ToolMod->sendingSMS($phone, $sms, $this->Controller, $add);
                }
                //通知接单权限工程师接单
                $UserData    = $ToolMod->getUser('accept', $data['departid']);
                $acceptPhone = $this->formatPhone($UserData);
            }
            if ($settingData['applyRepair']['status'] == C('OPEN_STATUS') && $acceptPhone) {
                //通知工程师接单短信 开启
                $sms = $this->formatSmsContent($settingData['applyRepair']['content'], $data);
                $ToolMod->sendingSMS($acceptPhone, $sms, $this->Controller, $add);
            }
        }
        //==========================================短信 END==========================================
        $infodata['status'] = C('ASSETS_STATUS_REPAIR');
        $this->updateData('assets_info', $infodata, "assid=$asArr[assid]");
    }


    public function returnLimitDate($data)
    {
        F('repairData', $data);
        $repairData = F('repairData');
        $arr        = [];
        $i          = 0;
        foreach ($repairData as $k => $v) {
            if ($i < $this->len && $v) {
                $arr[] = $v;
                $i++;
                unset($repairData[$k]);
            }
        }
        F('repairData', $repairData);
        return $arr;
    }

    public function returnLimitPatarts($repairs, $parts)
    {
        F('repairParts', $parts);
        $partsData = F('repairParts');
        $arr       = [];
        foreach ($repairs as $k => $v) {
            foreach ($partsData as $k1 => $v1) {
                if ($v['assnum'] == $v1['assnum']) {
                    $arr[] = $v1;
                    unset($partsData[$k1]);
                }
            }
        }
        F('repairParts', $partsData);
        return $arr;
    }

    //接单详细操作
    public function accept($data, $repid)
    {
        //查询设备所属医院
        $assInfo    = $this->DB_get_one('assets_info', 'assets,assnum,model,departid,hospital_id',
            ['assid' => $data['assid']]);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assInfo['department'] = $departname[$assInfo['departid']]['department'];

        $expect_arrive = I('POST.expect_arrive');
        $this->checkstatus(judgeNum($expect_arrive) && $expect_arrive > 0, '到场时间必须为大于0');
        $data['response']       = session('username');
        $data['response_tel']   = session('telephone');
        $data['response_date']  = time();
        $data['notice_time']    = time();
        $data['editdate']       = time();
        $data['expect_arrive']  = $expect_arrive;
        $data['status']         = C('REPAIR_RECEIPT');
        $data['reponse_remark'] = I('post.reponse_remark');
        $save                   = $this->updateData('repair', $data, ['repid' => $repid]);
        if ($save !== false) {
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($settingData) {
                //有开启短信
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $data['department'] = $departname[$data['departid']]['department'];
                $ToolMod            = new ToolController();
                if ($settingData['acceptOrder']['status'] == C('OPEN_STATUS') && $data['applicant_tel']) {
                    //通知已有工程师接单 开启
                    $sms = $this->formatSmsContent($settingData['acceptOrder']['content'], $data);
                    $ToolMod->sendingSMS($data['applicant_tel'], $sms, $this->Controller, $repid);
                }
            }
            //==========================================短信 END==========================================
            if (C('USE_FEISHU') === 1) {
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**维修单号：**' . $data['repnum'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备名称：**' . $assInfo['assets'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**设备编码：**' . $assInfo['assnum'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**使用科室：**' . $assInfo['department'];
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**接单工程师：**' . session('username');
                $feishu_fields[]       = $fd;


                $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag']         = 'div';
                $card_data['elements'][0]['fields']      = $feishu_fields;

                $card_data['header']['template']         = 'indigo';
                $card_data['header']['title']['content'] = '维修设备已接单提醒';
                $card_data['header']['title']['tag']     = 'plain_text';

                //查询报修人员openid
                $applicant = $this->DB_get_one('user', 'telephone,openid', ['username' => $data['applicant']]);
                if ($applicant['openid']) {
                    $this->send_feishu_card_msg($applicant['openid'], $card_data);
                }
                //==========================================飞书 END==========================================
            } else {
                //==========================================微信 start==========================================
                //发送微信消息给报修人（维修处理通知）
                //查询报修人员openid
                $applicant   = $this->DB_get_one('user', 'telephone,openid', ['username' => $data['applicant']]);
                $moduleModel = new ModuleModel();
                $wx_status   = $moduleModel->decide_wx_login();

                if ($wx_status && $applicant['openid']) {
                    Weixin::instance()->sendMessage($applicant['openid'], '设备维修通知', [
                        'thing3'             => $assInfo['department'],// 科室
                        'thing6'             => $assInfo['assets'],// 设备名称
                        'character_string12' => $assInfo['assnum'],// 设备编码
                        'character_string35' => $data['repnum'],// 维修单号
                        'const17'            => '已接单',// 工单状态
                    ]);
                }
                //==========================================微信 end==========================================
            }
            $this->addLog('repair', M()->getLastSql(), '接单一台维修编号为' . $data['repnum'] . '的设备', $repid, '');
            $result['status'] = 1;
            $result['msg']    = '接单成功';

            //推送一条推送消息到大屏幕
            $push_messages[] = [
                'type_action' => 'edit',
                'type_name'   => C('SCREEN_REPAIR'),
                'assets'      => $assInfo['assets'],
                'assnum'      => $assInfo['assnum'],
                'department'  => $departname[$assInfo['departid']]['department'],
                'remark'      => $data['reponse_remark'],
                'status'      => $data['status'],
                'status_name' => '已接单',
                'time'        => date('Y-m-d H:i'),
                'username'    => session('username') . '(' . session('telephone') . ')',
            ];
            push_messages($push_messages);
        } else {
            $result['status'] = -1;
            $result['msg']    = '接单失败!';
        }
        return $result;
    }


    //添加修改跟进
    public function addFollow($repid)
    {
        $followdate = I('post.followdate');
        $detail     = I('post.remark');
        $nextdate   = I('post.nextdate');
        foreach ($followdate as $val) {
            if (!trim($val)) {
                return ['status' => -1, 'msg' => '请填写跟进时间！'];
            }
        }
        foreach ($detail as $val) {
            if (!trim($val)) {
                return ['status' => -1, 'msg' => '请填写处理详情！'];
            }
        }
        foreach ($nextdate as $val) {
            if (!trim($val)) {
                return ['status' => -1, 'msg' => '请填写预计下一步跟进时间！'];
            }
        }
        $this->deleteData('repair_follow', ['repid' => $repid]);
        //组织数据
        $addFollow = [];
        foreach ($followdate as $k => $v) {
            $addFollow['repid']      = $repid;
            $addFollow['followdate'] = strtotime($followdate[$k]);
            $addFollow['detail']     = $detail[$k];
            $addFollow['nextdate']   = strtotime($nextdate[$k]);
            //新增跟进
            $this->insertData('repair_follow', $addFollow);
        }
    }

    /**
     * 记录维修配件信息
     * @params1 $repid int 维修单ID
     *
     * @return array 配件信息
     */
    public function get_insert_parts($repid)
    {
        //获取接单人
        $repinfo  = $this->DB_get_one('repair', 'response', ['repid' => $repid]);
        $partname = rtrim(I('POST.partname'), '|');
        $model    = rtrim(I('POST.model'), '|');
        $num      = rtrim(I('POST.num'), '|');
//        $partsStatus = explode('|', rtrim(I('POST.partsStatus'), '|'));
        $partname = explode('|', $partname);
        $model    = explode('|', $model);
        $num      = explode('|', $num);
        //是否出库
        $status = [];
//        foreach ($partsStatus as $k => $v) {
//            if ($v == '是') {
//                $status[$k] = 1;
//            } else {
//                $status[$k] = 0;
//            }
//        }
//        $delPartid = I('POST.delPartid');
        $totalNum = 0;
        $data     = [];
//        if ($delPartid) {
//            //排除被移除的配料
//            $parts_where['partid'] = array('NOT IN', $delPartid);
//        }
//        $parts_where['repid'] = array('EQ', $repid);
//        //获取已添加的配件信息
//        $parts = $this->DB_get_all('repair_parts', 'partid,repid,parts,part_model', array("repid" => $repid));
//        $parts_arr = [];
//        if ($parts) {
//            foreach ($parts as &$partsValue) {
//                $parts_arr[$partsValue['parts']][$partsValue['part_model']] = $partsValue['partid'];
//            }
//        }
        foreach ($partname as $k => $v) {
            if (!$v || $v == '--') {
                continue;
            } else {
                $data[$k]['repid']      = $repid;
                $data[$k]['parts']      = $v;
                $model[$k]              = ($model[$k] == '--') ? '' : $model[$k];
                $num[$k]                = ($num[$k] == '--') ? 0 : $num[$k];
                $data[$k]['part_model'] = $model[$k];
                $data[$k]['part_num']   = $num[$k];
//                $data[$k]['is_out'] = $status[$k];
                $totalNum += $data[$k]['part_num'];
//                if ($parts_arr[$v][$model[$k]]) {
//                    $data[$k]['edituser'] = session('username');
//                    $data[$k]['editdate'] = time();
//                    $data[$k]['partid'] = $parts_arr[$v][$model[$k]];
//
//                } else {
                $data[$k]['adduser'] = $repinfo['response'];
                $data[$k]['adddate'] = time();
//                }
            }
        }
        $result['data']     = $data;
        $result['totalNum'] = $totalNum;
        return $result;
    }

    /**
     * @Notes 配件入库
     * @params $partsArr array 配件信息数组
     * @params $repid int 维修单id
     * @params $assid int 设备id
     * */
    public function add_parts($partsArr, $repid, $assid)
    {
        $repinfo = $this->DB_get_one('repair', 'response', ['repid' => $repid]);
        //总库存数据 未出库的数据
        $allPartsInfo = $this->getPartsInfo();

        //组合后的库存数据
        $allpartsData = [];
        foreach ($allPartsInfo as &$v) {
            $allpartsData[$v['parts'] . $v['parts_model']]['parts']       = $v['parts'];
            $allpartsData[$v['parts'] . $v['parts_model']]['parts_model'] = $v['parts_model'];
            $allpartsData[$v['parts'] . $v['parts_model']]['total']       = $v['total'];
            $allpartsData[$v['parts'] . $v['parts_model']]['detailid']    = $v['detailid'];
        }
        //入库申请单详情
        $applydetail = [];
        //出库单详情
        $outdetail = [];
        //需要登记的配件id(申请的配件 仓库有)
        $outDetailid = [];

        $applynum   = 0;
        $outnum     = 0;
        $partsModel = new PartsModel();
        foreach ($partsArr['data'] as &$stort) {
            //生成出库单
            $apply                             = $allpartsData[$stort['parts'] . $stort['part_model']]['total'] - $stort['part_num'];
            $outdetail[$outnum]['parts']       = $stort['parts'];
            $outdetail[$outnum]['parts_model'] = $stort['part_model'];
            $outdetail[$outnum]['part_num']    = $stort['part_num'];
            $outnum++;
            $detailid = explode(',', $allpartsData[$stort['parts'] . $stort['part_model']]['detailid']);
            for ($i = 0; $i < $stort['part_num']; $i++) {
                if ($detailid[$i]) {
                    //登记库存内需要分配的设备
                    $outDetailid[] = $detailid[$i];
                }
            }
            if ($apply < 0) {
                //申请数量大于库存,生成采购单
                $applydetail[$applynum]['parts']       = $stort['parts'];
                $applydetail[$applynum]['parts_model'] = $stort['part_model'];
                $applydetail[$applynum]['part_num']    = abs($apply);
                $applynum++;
            }
        }


        if ($outdetail) {
            //添加一条出库记录
            $outRecord                = [];
            $outRecord['repid']       = $repid;
            $outRecord['leader']      = $repinfo['response'];
            $outRecord['hospital_id'] = session('current_hospitalid');
            $outRecord['assid']       = $assid;
            $outRecord['outware_num'] = $partsModel->getOutwareNum();
            $outwareid                = $this->insertData('parts_outware_record', $outRecord);
            $num                      = 0;
            $outAddAll                = [];
            foreach ($outdetail as &$value) {
                for ($i = 0; $i < $value['part_num']; $i++) {
                    $outAddAll[$num]['parts']       = $value['parts'];
                    $outAddAll[$num]['parts_model'] = $value['parts_model'];
                    $outAddAll[$num]['outwareid']   = $outwareid;
                    $num++;
                }
            }
            //添加出库申请记录
            $this->insertDataALL('parts_outware_record_apply', $outAddAll);
            if ($applydetail) {
                //添加一条入库记录
                $applyRecord                = [];
                $applyRecord['repid']       = $repid;
                $applyRecord['hospital_id'] = session('current_hospitalid');
                $applyRecord['assid']       = $assid;
                $applyRecord['inware_num']  = $partsModel->getInwareNum();
                $inwareid                   = $this->insertData('parts_inware_record', $applyRecord);
                $num                        = 0;
                $applyAddAll                = [];
                foreach ($applydetail as &$value) {
                    for ($i = 0; $i < $value['part_num']; $i++) {
                        $applyAddAll[$num]['parts']       = $value['parts'];
                        $applyAddAll[$num]['inwareid']    = $inwareid;
                        $applyAddAll[$num]['parts_model'] = $value['parts_model'];
                        $num++;
                    }
                }
                //添加入库申请记录
                $this->insertDataALL('parts_inware_record_apply', $applyAddAll);
            }
        }


        if ($outDetailid) {
            //配件库有所申请的配件 登记绑定维修编号
            $data['repid']     = $repid;
            $data['leader']    = $repinfo['response'];
            $where['detailid'] = ['IN', $outDetailid];
            $this->updateData('parts_inware_record_detail', $data, $where);
        }

        $addAll = [];
        foreach ($partsArr['data'] as &$addRepairParts) {
            if ($addRepairParts['partid']) {
                $this->updateData('repair_parts', $addRepairParts, ['partid' => $addRepairParts['partid']]);
            } else {
                $addAll[] = $addRepairParts;
            }
        }

        if ($addAll) {
            $this->insertDataALL('repair_parts', $addAll);
        }
    }


    /**
     * @Notes记录维修报价公司信息
     * @params1 $repid int 维修单ID
     * @retrun $total_price int 总价
     */
    public function insertOfferCompany($repid)
    {
        $companyName = rtrim(I('POST.companyName'), '|');
        $olsid       = rtrim(I('POST.olsid'), '|');
//        $offid_arr = rtrim(I('POST.offid'), '|');
        $contracts        = rtrim(I('POST.contracts'), '|');
        $telphone         = rtrim(I('POST.telphone'), '|');
        $invoice          = rtrim(I('POST.invoice'), '|');
        $cycle            = rtrim(I('POST.cycle'), '|');
        $proposal_info    = rtrim(I('POST.proposal_info'), '|');
        $remark           = rtrim(I('POST.remark'), '|');
        $sumPrice         = rtrim(I('POST.totalPrice'), '|');
        $companyName      = explode('|', $companyName);
        $olsid            = explode('|', $olsid);
        $contracts        = explode('|', $contracts);
        $telphone         = explode('|', $telphone);
        $invoice          = explode('|', $invoice);
        $cycle            = explode('|', $cycle);
        $remark           = explode('|', $remark);
        $sumPrice         = explode('|', $sumPrice);
        $proposal_info    = explode('|', $proposal_info);
        $proposal         = I('POST.proposal');
        $last_decisioin   = trim(I('POST.last_decisioin'));
        $decision_reasion = trim(I('POST.decision_reasion'));
        $delRepopid       = I('POST.delRepopid');
        $delOffid         = I('POST.delOffid');
        $IS_OPEN_OFFER    = C('DO_STATUS');
        $doOfferMenu      = get_menu($this->MODULE, $this->Controller, 'doOffer');
        if (!$doOfferMenu) {
            if (C('IS_OPEN_OFFER')) {
                //数量 建议
                $IS_OPEN_OFFER = C('NOT_DO_STATUS');
            } else {
                //价格 数量 建议
                $IS_OPEN_OFFER = C('SHUT_STATUS_DO');
            }
        }
        if ($IS_OPEN_OFFER == C('DO_STATUS')) {
            //价格 数量 最终
            if (!$last_decisioin) {
                die(json_encode(['status' => -198, 'msg' => '请选择最终厂家']));
            }
        }
        if ($delOffid) {
            //排除被移除的公司 包括 配料
            $company_where['offid'] = ['NOT IN', $delOffid];
            $parts_where['offid'][] = ['NOT IN', $delOffid];
        }
        if ($delRepopid) {
            //排除被移除的配料
            $parts_where['repopid'][] = ['NOT IN', $delRepopid];
        }
        //获取已添加的第三方  排除需要删除的
        $company_where['repid'] = ['EQ', $repid];
        $company                = $this->DB_get_all('repair_offer_company',
            'offid,repid,offer_company,proposal,proposal_info', $company_where);
        $proposal               = [];
        $proposal_info          = [];
        $company_arr            = [];
        $parts_arr              = [];
        if ($company) {
            $offid_arr = [];
            foreach ($company as &$companyValue) {
                $company_arr[$companyValue['offer_company']] = $companyValue['offid'];
                $offid_arr[]                                 = $companyValue['offid'];
                $proposal[]                                  = $companyValue['proposal'];
                $proposal_info[]                             = $companyValue['proposal_info'];
            }
            //获取已添加的第三方配件/服务信息
            $parts_where['offid'] = ['IN', $offid_arr];
            $parts                = $this->DB_get_all('repair_offer_parts_detail',
                'repopid,offid,parts_name,parts_model', $parts_where);
            if ($parts) {
                foreach ($parts as &$partsValue) {
                    $parts_arr[$partsValue['offid']][$partsValue['parts_name']][$partsValue['parts_model']] = $partsValue['repopid'];
                }
            }
        }
        $data = [];
        foreach ($companyName as $k => $v) {
            if (!$v || $v == '--') {
                continue;
            } else {
                //记录报价公司信息
                $data[$k]['offer_company']    = trim($v);
                $data[$k]['offer_company_id'] = trim($olsid[$k]);;
                $data[$k]['offer_contacts'] = trim($contracts[$k]);
                $data[$k]['telphone']       = trim($telphone[$k]);
                $data[$k]['invoice']        = trim($invoice[$k]);
                $data[$k]['cycle']          = trim($cycle[$k]);
                $data[$k]['remark']         = trim($remark[$k]);
                $data[$k]['total_price']    = trim($sumPrice[$k]) ?: '0';
                if ($IS_OPEN_OFFER != C('NOT_DO_STATUS')) {
                    //有写入价格权限(1,2)
                    $companyPartsPrice = I('POST.companyPartsPrice');
                }
                if ($IS_OPEN_OFFER == C('DO_STATUS')) {
                    //有报价权限 (1)
                    if ($v == $last_decisioin) {
                        $data[$k]['last_decisioin']   = C('YES_STATUS');
                        $data[$k]['decision_reasion'] = $decision_reasion;
                        $data[$k]['decision_user']    = session('username');
                        $data[$k]['decision_adddate'] = time();
                    }
                } else {
                    //没有报价权限 写 建议厂家 (0,2)
                    $data[$k]['proposal']      = ($proposal == $v) ? C('YES_STATUS') : C('NO_STATUS');
                    $data[$k]['proposal_info'] = $proposal_info[$k];
                }
                if ($company_arr[$v]) {
                    //已添加 修改厂家
                    $data[$k]['offid']    = $company_arr[$v];
                    $data[$k]['edituser'] = session('username');
                    $data[$k]['editdate'] = time();
//                    $this->updateData('repair_offer_company', $data, "offer_company='$v' AND repid=$repid AND offid=$offid_arr[$k]");
                } else {
                    //新增的厂家
                    $data[$k]['adduser'] = session('username');
                    $data[$k]['adddate'] = time();
//                    $offid = $this->insertData('repair_offer_company', $data);
                }
            }
        }
        foreach ($data as $k => $v) {
            $data[$k]['proposal']      = $proposal[$k];
            $data[$k]['proposal_info'] = $proposal_info[$k];
        }
        $result['data'] = $data;
        return $result;
    }

    /**
     * Notes: 暂存维修公司
     */
    public function save_tmp_company($repid)
    {
        $companyName      = rtrim(I('POST.companyName'), '|');
        $olsid            = rtrim(I('POST.olsid'), '|');
        $contracts        = rtrim(I('POST.contracts'), '|');
        $telphone         = rtrim(I('POST.telphone'), '|');
        $invoice          = rtrim(I('POST.invoice'), '|');
        $cycle            = rtrim(I('POST.cycle'), '|');
        $proposal_info    = rtrim(I('POST.proposal_info'), '|');
        $remark           = rtrim(I('POST.remark'), '|');
        $sumPrice         = rtrim(I('POST.totalPrice'), '|');
        $companyName      = explode('|', $companyName);
        $olsid            = explode('|', $olsid);
        $contracts        = explode('|', $contracts);
        $telphone         = explode('|', $telphone);
        $invoice          = explode('|', $invoice);
        $cycle            = explode('|', $cycle);
        $remark           = explode('|', $remark);
        $sumPrice         = explode('|', $sumPrice);
        $proposal_info    = explode('|', $proposal_info);
        $proposal         = I('POST.proposal');
        $last_decisioin   = trim(I('POST.last_decisioin'));
        $decision_reasion = trim(I('POST.decision_reasion'));
        $data             = [];
        foreach ($companyName as $k => $v) {
            if (!$v || $v == '--') {
                continue;
            } else {
                //记录报价公司信息
                $data[$k]['repid']            = $repid;
                $data[$k]['offer_company']    = trim($v);
                $data[$k]['offer_company_id'] = trim($olsid[$k]);;
                $data[$k]['offer_contacts'] = trim($contracts[$k]);
                $data[$k]['telphone']       = trim($telphone[$k]);
                $data[$k]['invoice']        = trim($invoice[$k]);
                $data[$k]['cycle']          = trim($cycle[$k]);
                $data[$k]['remark']         = trim($remark[$k]);
                $data[$k]['total_price']    = trim($sumPrice[$k]);
                if (trim($v) == $last_decisioin) {
                    $data[$k]['last_decisioin']   = C('YES_STATUS');
                    $data[$k]['decision_reasion'] = $decision_reasion;
                } else {
                    $data[$k]['last_decisioin']   = C('NO_STATUS');
                    $data[$k]['decision_reasion'] = '';
                }
                $data[$k]['proposal']      = ($proposal == $v) ? C('YES_STATUS') : C('NO_STATUS');
                $data[$k]['proposal_info'] = $proposal_info[$k];
            }
        }
        //删除原来的临时记录
        $this->deleteData('repair_offer_company', ['repid' => $repid]);
        if ($data) {
            //记录新的临时记录
            $this->insertDataALL('repair_offer_company', $data);
        }
    }

    /**
     * @Notes 第三方维修公司入库
     * @params $companyArr array 配件信息数组
     * @params $repid int 维修单id
     */
    public function add_company($companyArr, $repid)
    {
        foreach ($companyArr['data'] as &$value) {
            if ($value['offid']) {
                $saveData                     = [];
                $saveData['repid']            = $repid;
                $saveData['offer_company_id'] = $value['offer_company_id'];
                $saveData['offer_company']    = $value['offer_company'];
                $saveData['offer_contacts']   = $value['offer_contacts'];
                $saveData['telphone']         = $value['telphone'];
                $saveData['invoice']          = $value['invoice'];
                $saveData['cycle']            = $value['cycle'];
                $saveData['remark']           = $value['remark'];
                $saveData['total_price']      = $value['total_price'];
                $saveData['editdate']         = $value['editdate'];
                $saveData['edituser']         = $value['edituser'];
                $saveData['proposal']         = $value['proposal'];
                $saveData['proposal_info']    = $value['proposal_info'];
                $saveData['last_decisioin']   = $value['last_decisioin'];
                $saveData['decision_reasion'] = $value['decision_reasion'];
                $saveData['decision_user']    = $value['decision_user'];
                $saveData['decision_adddate'] = $value['decision_adddate'];
                $offid                        = $value['offid'];
                $this->updateData('repair_offer_company', $saveData, ['offid' => $offid]);
            } else {
                $addData                     = [];
                $addData['repid']            = $repid;
                $addData['offer_company_id'] = $value['offer_company_id'];
                $addData['offer_company']    = $value['offer_company'];
                $addData['offer_contacts']   = $value['offer_contacts'];
                $addData['telphone']         = $value['telphone'];
                $addData['invoice']          = $value['invoice'];
                $addData['cycle']            = $value['cycle'];
                $addData['remark']           = $value['remark'];
                $addData['total_price']      = $value['total_price'];
                $addData['adduser']          = $value['adduser'];
                $addData['adddate']          = $value['adddate'];
                $addData['proposal']         = $value['proposal'];
                $addData['proposal_info']    = $value['proposal_info'];
                $addData['last_decisioin']   = $value['last_decisioin'];
                $addData['decision_reasion'] = $value['decision_reasion'];
                $addData['decision_user']    = $value['decision_user'];
                $addData['decision_adddate'] = $value['decision_adddate'];
                $this->insertData('repair_offer_company', $addData);
            }
        }
        if ($companyArr['totalPrice'] > 0) {
            //有选择最终厂家的权限 获取到总价 更新 维修表
            $repairData                        = $this->DB_get_one('repair', 'company_total_price,expect_price',
                ['repid' => $repid]);
            $expect_price                      = $repairData['expect_price'] - $repairData['company_total_price'] + $companyArr['totalPrice'];
            $saveRepair['expect_price']        = $expect_price;
            $saveRepair['company_total_price'] = $companyArr['totalPrice'];
            $this->updateData('repair', $saveRepair, ['repid' => $repid]);
        }
    }

    /**
     * @Notes: 获取所有的报价公司及其配件
     * @params $repid int 维修单ID
     * @return array
     */
    public function getAllCompanysBasic($repid)
    {
        $company     = $this->DB_get_all('repair_offer_company', '', ['repid' => $repid], '', 'last_decisioin DESC');
        $companyData = [];
        if ($company) {
            foreach ($company as &$value) {
                $file = $this->DB_get_all('offline_suppliers_file', '', ['olsid' => $value['offer_company_id']]);
                if ($file) {
                    if (count($file) == 5) {
                        //暂定 没有全部上传就是不齐全 todo
                        $value['aptitude'] = '<span class="green">齐全</span><a class="gray">点击查看</a>';
                    } else {
                        $value['aptitude'] = '<span class="red">不齐全</span><a class="gray">点击查看</a>';
                    }
                    $fileData = [];
                    foreach ($file as &$pic) {
                        $fileData[] = $pic['url'];
                        if ($pic['term_date'] > 0 && $pic['term_date'] <= time()) {
                            $value['aptitude'] = '<span class="red">已过期</span>';
                        } else {
                            $value['aptitude'] .= '<input type="hidden" class="imageUrl" value="' . $pic['url'] . '">';
                        }
                    }
                    $value['pic_url'] = json_encode($fileData);
                } else {
                    $value['aptitude'] = '<span class="red">无</span>';
                }
                if ($value['last_decisioin'] == C('YES_STATUS')) {
                    $value['decision_adddate'] = getHandleTime($value['decision_adddate']);
                    $companyData['lastData']   = $value;
                }
                $value['pic_url'] = json_decode($value['pic_url']);
            }
            $companyData['data'] = $company;
        }
        return $companyData;
    }

    /**
     * @Notes: 获取配件/服务
     * @params $repid int 维修单ID
     * @return array
     */
    public function getAllPartsBasic($repid)
    {
        $fields = 'partid,repid,order,parts,part_model,part_num,part_price,price_sum,status,adduser,adddate,edituser,editdate';
        $parts  = $this->DB_get_all('repair_parts', $fields, ['repid' => $repid]);
        return $parts;
    }

    /**
     * @Notes 获取审核历史
     * @params $repid int 维修单ID
     * @return array
     * */
    public function getApproveBasic($repid)
    {
        $approves = $this->DB_get_all('approve', '',
            ['repid' => $repid, 'process_node' => 'repair_approve', 'is_delete' => C('NO_STATUS')], '', 'apprid asc');
        foreach ($approves as $k => $v) {
            $approves[$k]['approve_time'] = getHandleMinute($v['approve_time']);
        }
        return $approves;
    }

    /**
     * @Notes  获取维修上传的文件
     * @params $repid int 维修单ID
     * @params $type string 文件上传节点(类型)
     * @return array
     * */
    public function getRepairFileBasic($repid, $type = null)
    {
        $where['repid']     = ['EQ', $repid];
        $where['is_delete'] = ['EQ', C('NO_STATUS')];

        if ($type) {
            $where['type'] = ['EQ', $type];
        }
        $file = $this->DB_get_all('repair_file', 'file_name,save_name,file_type,file_url,add_user,add_time,file_id',
            $where);
        if ($file) {
            foreach ($file as &$fileValue) {
                $fileValue['file_url']  = urldecode(urlencode($fileValue['file_url']));
                $fileValue['operation'] = '<div class="layui-btn-group">';
                $supplement             = 'data-path="' . $fileValue['file_url'] . '" data-name="' . $fileValue['file_name'] . '"';
                $fileValue['operation'] .= $this->returnListLink('下载', '', '', C('BTN_CURRENCY') . ' downFile', '',
                    $supplement);
                if ($fileValue['file_type'] != 'doc' && $fileValue['file_type'] != 'docx') {
                    $fileValue['operation'] .= $this->returnListLink('预览', '', '',
                        C('BTN_CURRENCY') . ' layui-btn-normal showFile', '', $supplement);
                }
                $fileValue['operation'] .= '</div>';
            }
        }
        return $file;
    }

    /**
     * @Notes  获取维修上传的文件 允许删除
     * @params $repid int 维修单ID
     * @params $type string 文件上传节点(类型)
     * @return array
     * */
    public function getRepairFileBasicAndDel($repid, $type = null)
    {
        $where['repid'] = ['EQ', $repid];
        if ($type) {
            $where['type'] = ['EQ', $type];
        }
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $data               = $this->DB_get_all('repair_file', '', $where);
        if ($data) {
            foreach ($data as &$dataV) {
                $dataV['file_size']       = round($dataV['file_size'] / 1024 / 1024, 2) . 'M';
                $dataV['operation']       = '<div class="layui-btn-group">';
                $dataV['operation']       .= '<input type="hidden" name="file_url" value="' . $dataV['file_url'] . '">';
                $dataV['operation']       .= '<input type="hidden" name="file_id" value="' . $dataV['file_id'] . '">';
                $dataV['operation']       .= '<input type="hidden" name="file_name" value="' . $dataV['file_name'] . '">';
                $dataV['operation']       .= '<input type="hidden" name="file_type" value="' . $dataV['file_type'] . '">';
                $dataV['mobileOperation'] = $dataV['operation'] . '<button class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</button></div>';
                $dataV['operation']       .= $this->returnListLink('下载', '', '', C('BTN_CURRENCY') . ' downFile', '');
                if ($dataV['file_type'] != 'doc' && $dataV['file_type'] != 'docx') {
                    $dataV['operation'] .= $this->returnListLink('预览', '', '',
                        C('BTN_CURRENCY') . ' layui-btn-normal showFile', '');
                }
                $dataV['operation'] .= '<button class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</button>';
                $dataV['operation'] .= '</div>';
            }
        }
        return $data;
    }


    /**
     * @Notes  获取维修跟进信息
     * @params $repid int 维修单ID
     * @return array
     * */
    public function getRepirFollowBasic($repid)
    {
        $follow = $this->DB_get_all('repair_follow', '', ['repid' => $repid], '', 'followdate desc', '');
        foreach ($follow as $k => $v) {
            $follow[$k]['followdate']   = getHandleTime($v['followdate']);
            $follow[$k]['nextdate']     = getHandleTime($v['nextdate']);
            $follow[$k]['followdate_m'] = date('Y/m/d', $v['followdate']);;
            $follow[$k]['nextdate_m'] = date('Y/m/d', $v['nextdate']);;
        }
        return $follow;
    }

    /**
     * @Notes: 根据设备assid获取设备维修记录情况
     *
     * @param $assid int 设备assid
     *
     * @return mixed
     */
    public function getRepairRecordByAssid($assid, $export = false)
    {
        //获取设备名称和assnum
        //$assetsInfo = $this->DB_get_one('assets_info','assid,assets,assnum',array('assid'=>$assid));
        $fields = "repid,assid,status,repnum,part_num as partNum,applicant,applicant_time,engineer,assist_engineer,overdate,actual_price as actualPrice,working_hours as totalHours";
        $lists  = $this->DB_get_all('repair', $fields, ['assid' => $assid], '', 'applicant_time asc');
        foreach ($lists as $key => $v) {
            $lists[$key]['year']           = date('Y', $v['applicant_time']);
            $lists[$key]['applicant_time'] = getHandleTime($v['applicant_time']);
            $lists[$key]['overdate']       = getHandleTime($v['overdate']);
            if ($v['status'] >= C('REPAIR_MAINTENANCE_COMPLETION')) {
                $lists[$key]['isComplete'] = '是';
            } else {
                $lists[$key]['isComplete'] = '否';
            }
            if ($v['assist_engineer']) {
                $lists[$key]['repairEngineer'] = $v['engineer'] . '、' . $v['assist_engineer'];
            } else {
                $lists[$key]['repairEngineer'] = $v['engineer'];
            }
        }
        foreach ($lists as $key => $val) {
            $lists[$key]['partNumUrl'] = '<a class="show" onclick="showPartInfo(this)" style="text-decoration: none;color: #00a6c8;" href="javascript:void(0)" data-id="' . $val['repid'] . '">' . $val['partNum'] . '</a>';
        }
        if ($export) {
            //导出出excel，行末加统计数据
            $sum            = [];
            $sum['partNum'] = $sum['actualPrice'] = $sum['totalHours'] = 0;
            foreach ($lists as $k => $v) {
                foreach ($sum as $k1 => $v1) {
                    if ($k1 == 'totalHours' || $k1 == 'actualPrice') {
                        $sum[$k1] += (float)$v[$k1];
                    } else {
                        $sum[$k1] += (int)$v[$k1];
                    }
                }
            }
            $count                           = count($lists);
            $lists[$count]['repid']          = 0;
            $lists[$count]['assid']          = 0;
            $lists[$count]['status']         = 0;
            $lists[$count]['repnum']         = '合计：';
            $lists[$count]['applicant']      = '--';
            $lists[$count]['applicant_time'] = '--';
            $lists[$count]['repairEngineer'] = '--';
            $lists[$count]['isComplete']     = '--';
            $lists[$count]['overdate']       = '--';
            foreach ($sum as $k => $v) {
                $lists[$count][$k] = $v;
            }
        }
        return $lists;
    }

    /**
     * Notes:组织数据
     * @params1 $lists array 要组织的数据
     *
     * @return array
     */
    public function handleRepairRecordData($lists)
    {
        $res           = [];
        $assets        = [];
        $res['lists']  = $lists;
        $year          = [];
        $calendarStyle = [];
        $seriesStyle   = [];
        $date          = [];
        $seriesTmpData = [];
        $seriesData    = [];
        foreach ($res['lists'] as $k => $v) {
            if (!in_array($v['year'], $year)) {
                $year[] = $v['year'];
            }
            if (in_array($v['applicant_time'], $date)) {
                $seriesTmpData[$v['applicant_time']] += 1;
            } else {
                $date[]                              = $v['applicant_time'];
                $seriesTmpData[$v['applicant_time']] = 1;
            }
        }
        //对年份进行高到低排序
        rsort($year);
        foreach ($seriesTmpData as $k => $v) {
            $seriesData[] = [$k, $v];
        }
        $len = count($year);
        //组织calendar的js数据格式
        for ($i = 0; $i < $len; $i++) {
            $calendarStyle[$i]['top']                                = $i * 200 + 100;
            $calendarStyle[$i]['left']                               = 'center';
            $calendarStyle[$i]['range'][0]                           = $year[$i] . '-01-01';
            $calendarStyle[$i]['range'][1]                           = $year[$i] . '-12-31';
            $calendarStyle[$i]['splitLine']['show']                  = true;
            $calendarStyle[$i]['splitLine']['lineStyle']['color']    = true;
            $calendarStyle[$i]['splitLine']['lineStyle']['width']    = 4;
            $calendarStyle[$i]['splitLine']['lineStyle']['type']     = 'solid';
            $calendarStyle[$i]['yearLabel']['formatter']             = $year[$i] . ' 年 ';
            $calendarStyle[$i]['yearLabel']['textStyle']['color']    = '#fff';
            $calendarStyle[$i]['monthLabel']['textStyle']['color']   = '#B5B3B3';
            $calendarStyle[$i]['monthLabel']['nameMap']              = 'cn';
            $calendarStyle[$i]['dayLabel']['textStyle']['color']     = '#B5B3B3';
            $calendarStyle[$i]['dayLabel']['nameMap']                = 'cn';
            $calendarStyle[$i]['itemStyle']['normal']['color']       = '#323c48';
            $calendarStyle[$i]['itemStyle']['normal']['borderWidth'] = 1;
            $calendarStyle[$i]['itemStyle']['normal']['borderColor'] = '#111';
        }
        //组织series的js数据格式
        for ($i = 0; $i < $len; $i++) {
            $seriesStyle[$i]['name']                         = '维修次数';
            $seriesStyle[$i]['type']                         = 'scatter';
            $seriesStyle[$i]['coordinateSystem']             = 'calendar';
            $seriesStyle[$i]['calendarIndex']                = $i;
            $seriesStyle[$i]['data']                         = $seriesData;
            $seriesStyle[$i]['itemStyle']['normal']['color'] = '#ddb926';
        }
        for ($i = 0; $i < $len; $i++) {
            $seriesStyle[$len + $i]['name']                               = 'Top 12';
            $seriesStyle[$len + $i]['type']                               = 'effectScatter';
            $seriesStyle[$len + $i]['coordinateSystem']                   = 'calendar';
            $seriesStyle[$len + $i]['calendarIndex']                      = $i;
            $seriesStyle[$len + $i]['showEffectOn']                       = 'render';
            $seriesStyle[$len + $i]['rippleEffect']['brushType']          = 'stroke';
            $seriesStyle[$len + $i]['hoverAnimation']                     = true;
            $seriesStyle[$len + $i]['itemStyle']['normal']['color']       = '#f4e925';
            $seriesStyle[$len + $i]['itemStyle']['normal']['shadowBlur']  = 10;
            $seriesStyle[$len + $i]['itemStyle']['normal']['shadowColor'] = '#333';
            $seriesStyle[$len + $i]['itemStyle']['normal']['zlevel']      = 1;
        }
        $res['assets']        = $assets;
        $res['calendarStyle'] = $calendarStyle;
        $res['seriesStyle']   = $seriesStyle;
        $res['seriesData']    = $seriesData;
        return $res;
    }

    /**
     * 获取设备故障类型统计
     *
     * @return array
     * */
    public function getFaultSummary()
    {
        $year         = date('Y');
        $default_time = strtotime($year . '-01-01');
        $startTime    = I('POST.startDate') ? strtotime(I('POST.startDate')) : $default_time;
        $endTime      = I('POST.endDate') ? strtotime(I('POST.endDate')) + 23 * 59 * 59 : time();
        $type         = I('POST.type') ? I('POST.type') : 1;
        $hospital_id  = I('POST.hospital_id');
        if (!$hospital_id) {
            $hospital_id = session('current_hospitalid');
        }
        switch ($type) {
            case 1:
                //按故障类型统计
                $data = $this->getRepairSummaryFromFault($startTime, $endTime, $hospital_id);
                break;
            case 2:
                //按设备分类统计
                $data = $this->getRepairSummaryFromCat($startTime, $endTime, $hospital_id);
                break;
            case 3:
                //按资产类型统计（急救、特种、计量、质控、效益分析、生命支持类、普通）
                $data = $this->getRepairSummaryFromStyle($startTime, $endTime, $hospital_id);
                break;
            case 4:
                //按维修性质统计（现场解决、自修、维保厂家、第三方维修）
                $data = $this->getRepairSummaryFromNature($startTime, $endTime, $hospital_id);
                break;
            case 5:
                //按资产价值区间统计
                $data = $this->getRepairSummaryFromPrice($startTime, $endTime, $hospital_id);
                break;
        }
        return $data;
    }


    /**
     * 获取设备故障数量 按故障类型统计
     *
     * @param int    $startTime   开始时间
     * @param int    $endTime     结束时间
     * @param string $hospital_id 医院id
     *
     * @return array
     * */
    public function getRepairSummaryFromFault($startTime, $endTime, $hospital_id)
    {

        $where = ' WHERE F.fault_problem_id=B.fault_problem_id AND E.applicant_time>=' . $startTime . ' AND E.applicant_time<=' . $endTime . ' AND G.hospital_id IN(' . $hospital_id . ')';
        //查询故障类型
        $parent = $this->DB_get_all('repair_setting', 'id,title', 'parentid=0');
        //问题的ID,名称,故障的数量
        $fields        = 'A.id,A.title AS name,(SELECT COUNT(*) from sb_repair_fault AS F LEFT JOIN sb_repair AS E ON E.repid=F.repid LEFT JOIN sb_assets_info AS G ON G.assid=E.assid' . $where . ') as totalNum';
        $JOIN[0]       = 'LEFT JOIN sb_repair_fault AS B ON A.id=B.fault_problem_id';
        $JOIN[1]       = 'LEFT JOIN sb_repair AS C ON C.repid=B.repid';
        $maxI['value'] = 0;
        $maxI['key']   = 0;
        $totalNumArr   = [];
        foreach ($parent as $key => $v) {
            $new['lists'][$key]['title'] = $parent[$key]['title'];
            $new['lists'][$key]['find']  = $this->DB_get_all_join('repair_setting', 'A', $fields, $JOIN,
                "A.parentid=" . $parent[$key]['id'], 'id', '', '');
            $findTotalNumArr             = [];
            foreach ($new['lists'][$key]['find'] as &$one) {
                $findTotalNumArr[] = $one['totalNum'];
            }
            $findTotalNumArr = $this->calculationDutyRatio($findTotalNumArr);
            foreach ($findTotalNumArr as $FindKey => $FindV) {
                $new['lists'][$key]['find'][$FindKey]['Ratio'] = $findTotalNumArr[$FindKey];
            }
            $count = 0;
            foreach ($new['lists'][$key]['find'] as &$findV) {
                $count += $findV['totalNum'];
            }
            $new['lists'][$key]['totalNum']       = $count;
            $totalNumArr[]                        = $count;
            $new['legend'][$key]                  = $parent[$key]['title'];
            $new['series']['data'][$key]['name']  = $parent[$key]['title'];
            $new['series']['data'][$key]['value'] = $count;

            if ($count == 0) {
                $new['selected'][$parent[$key]['title']] = false;
            } else {
                $new['selected'][$parent[$key]['title']] = true;
            }


            if ($new['series']['data'][$key]['value'] > $maxI['value']) {
                $maxI['key']   = $key;
                $maxI['value'] = $new['series']['data'][$key]['value'];
            }
        }

        $new['max']  = true;
        $new['maxI'] = $maxI['key'];
        $totalNumArr = $this->calculationDutyRatio($totalNumArr);
        foreach ($totalNumArr as $key => $v) {
            $new['lists'][$key]['Ratio'] = $totalNumArr[$key];
        }
        $new['series']['name']   = '设备故障统计';
        $new['series']['type']   = 'pie';
        $new['series']['radius'] = '40%';
        $new['series']['center'] = ['34%', '45%'];
        return $new;
    }


    /**
     * 获取设备故障数量 按设备分类统计
     *
     * @param int $startTime 开始时间
     * @param int $endTime   结束时间
     *
     * @return array
     * */
    public function getRepairSummaryFromCat($startTime, $endTime, $hospital_id)
    {
        //获取父类数据
        $parentCat = $this->DB_get_all('category', 'catid,arrchildid,category',
            ['parentid' => 0, 'hospital_id' => ['in', $hospital_id]]);
        $JOIN[0]   = 'LEFT JOIN sb_assets_info as B ON B.assid=A.assid';
        $JOIN[1]   = 'LEFT JOIN sb_category as C ON C.catid=B.catid';
        foreach ($parentCat as $key => $v) {
            if ($parentCat[$key]['arrchildid']) {
                $arrCatId = $parentCat[$key]['catid'] . ',' . trim(str_replace('","', ",",
                        $parentCat[$key]['arrchildid']), '[""]');
            } else {
                $arrCatId = $parentCat[$key]['catid'];
            }
            $where                               = "C.catid IN($arrCatId) AND applicant_time>=" . $startTime . " AND applicant_time<=" . $endTime;
            $total                               = $this->DB_get_count_join('repair', 'A', $JOIN, $where);
            $parentCat[$key]['title']            = $parentCat[$key]['category'];
            $parentCat[$key]['totalNum']         = $total;
            $totalArr[]                          = $total;
            $new['legend'][$key]                 = $parentCat[$key]['category'];
            $new['series']['data'][$key]['name'] = $parentCat[$key]['category'];
            if ($total == 0) {
                $new['selected'][$parentCat[$key]['category']] = false;
            } else {
                $new['selected'][$parentCat[$key]['category']] = true;
            }
            $new['series']['data'][$key]['value'] = $total;
        }
        $parentRatio = $this->calculationDutyRatio($totalArr);
        foreach ($parentCat as $key => $v) {
            $parentCat[$key]['Ratio'] = $parentRatio[$key];
        }
        $new['series']['name']   = '设备故障统计';
        $new['series']['type']   = 'pie';
        $new['series']['radius'] = '30%';
        $new['series']['center'] = ['45%', '50%'];
        $new['lists']            = $parentCat;
        return $new;
    }


    /**
     * 获取设备故障数量 按资产类型统计（急救、特种、计量、质控、效益分析、生命支持类、普通）
     *
     * @param int $startTime 开始时间
     * @param int $endTime   结束时间
     *
     * @return array
     * */
    public function getRepairSummaryFromStyle($startTime, $endTime, $hospital_id)
    {
        $where  = " A.applicant_time>=" . $startTime . " AND A.applicant_time<=" . $endTime . " AND B.hospital_id in(" . $hospital_id . ")";
        $JOIN   = 'LEFT JOIN sb_assets_info as B ON A.assid=B.assid';
        $assets = $this->DB_get_all_join('repair', 'A',
            'is_special,is_metering,is_qualityAssets,is_benefit,is_lifesupport,is_firstaid', $JOIN, $where, '', '', '');


        $lists[C('ASSETS_FIRST_CODE_YES_NAME')]        = 0;
        $lists[C('ASSETS_SPEC_CODE_YES_NAME')]         = 0;
        $lists[C('ASSETS_METER_CODE_YES_NAME')]        = 0;
        $lists[C('ASSETS_QUALITY_CODE_YES_NAME')]      = 0;
        $lists[C('ASSETS_BENEFIT_CODE_YES_NAME')]      = 0;
        $lists[C('ASSETS_LIFE_SUPPORT_CODE_YES_NAME')] = 0;
        $lists['普通设备']                             = 0;


        foreach ($assets as &$one) {
            if ($one['is_firstaid'] == C('YES_STATUS')) {
                $lists[C('ASSETS_FIRST_CODE_YES_NAME')]++;
            }
            if ($one['is_special'] == C('YES_STATUS')) {
                $lists[C('ASSETS_SPEC_CODE_YES_NAME')]++;
            }
            if ($one['is_metering'] == C('YES_STATUS')) {
                $lists[C('ASSETS_METER_CODE_YES_NAME')]++;
            }
            if ($one['is_qualityAssets'] == C('YES_STATUS')) {
                $lists[C('ASSETS_QUALITY_CODE_YES_NAME')]++;
            }
            if ($one['is_benefit'] == C('YES_STATUS')) {
                $lists[C('ASSETS_BENEFIT_CODE_YES_NAME')]++;
            }
            if ($one['is_lifesupport'] == C('YES_STATUS')) {
                $lists[C('ASSETS_LIFE_SUPPORT_CODE_YES_NAME')]++;
            }
            if ($one['is_firstaid'] != C('YES_STATUS') && $one['is_special'] != C('YES_STATUS') && $one['is_metering'] != C('YES_STATUS') && $one['is_qualityAssets'] != C('YES_STATUS') && $one['is_benefit'] != C('YES_STATUS') && $one['is_lifesupport'] != C('YES_STATUS')) {
                $lists['普通设备']++;
            }
        }

        $i = 0;
        foreach ($lists as $key => $v) {
            $totalNumArr[]                      = $v;
            $new['lists'][$i]['title']          = $key;
            $new['lists'][$i]['totalNum']       = $v;
            $new['legend'][$i]                  = $key;
            $new['series']['data'][$i]['name']  = $key;
            $new['series']['data'][$i]['value'] = $v;
            $i++;
        }
        $totalNumArr = $this->calculationDutyRatio($totalNumArr);
        foreach ($totalNumArr as $key => $v) {
            $new['lists'][$key]['Ratio'] = $totalNumArr[$key];
        }
        $new['series']['name']   = '设备故障统计';
        $new['series']['type']   = 'pie';
        $new['series']['radius'] = '40%';
        $new['series']['center'] = ['45%', '45%'];
        return $new;
    }

    /**
     * 获取设备故障数量 按维修性质统计（现场解决、自修、第三方维修）
     *
     * @param int    $startTime   开始时间
     * @param int    $endTime     结束时间
     * @param string $hospital_id 医院id
     *
     * @return array
     * */
    public function getRepairSummaryFromNature($startTime, $endTime, $hospital_id)
    {
        $where                       = " AND applicant_time>=" . $startTime . " AND applicant_time<=" . $endTime;
        $assets_where['hospital_id'] = ['IN', $hospital_id];
        $assets                      = $this->DB_get_all('assets_info', 'assid', $assets_where);
        $assidArr                    = '';
        if ($assets) {
            foreach ($assets as $assetsV) {
                $assidArr .= ',' . $assetsV['assid'];
            }
            $assidArr                            = trim($assidArr, ',');
            $where                               .= ' AND assid IN(' . $assidArr . ')';
            $list[C('REPAIR_TYPE_IS_STUDY')]     = $this->DB_get_count('repair',
                'repair_type=' . C('REPAIR_TYPE_IS_STUDY') . $where);
            $list[C('REPAIR_TYPE_IS_GUARANTEE')] = $this->DB_get_count('repair',
                'repair_type=' . C('REPAIR_TYPE_IS_GUARANTEE') . $where);
            $list[C('REPAIR_TYPE_THIRD_PARTY')]  = $this->DB_get_count('repair',
                'repair_type=' . C('REPAIR_TYPE_THIRD_PARTY') . $where);
            $list[C('REPAIR_TYPE_IS_SCENE')]     = $this->DB_get_count('repair',
                'repair_type=' . C('REPAIR_TYPE_IS_SCENE') . $where);
            $i                                   = 0;
            foreach ($list as $key => $value) {
                $totalNumArr[] = $value;
                switch ($key) {
                    case C('REPAIR_TYPE_IS_STUDY'):
                        $name = C('REPAIR_TYPE_IS_STUDY_NAME');
                        break;
                    case C('REPAIR_TYPE_IS_GUARANTEE'):
                        $name = C('REPAIR_TYPE_IS_GUARANTEE_NAME');
                        break;
                    case C('REPAIR_TYPE_THIRD_PARTY'):
                        $name = C('REPAIR_TYPE_THIRD_PARTY_NAME');
                        break;
                    case C('REPAIR_TYPE_IS_SCENE'):
                        $name = C('REPAIR_TYPE_IS_SCENE_NAME');
                        break;
                }
                $new['series']['data'][$i]['name']  = $name;
                $new['lists'][$i]['title']          = $name;
                $new['lists'][$i]['totalNum']       = $value;
                $new['legend'][$i]                  = $name;
                $new['series']['data'][$i]['value'] = $value;
                $i++;
            }
            $totalNumArr = $this->calculationDutyRatio($totalNumArr);
            foreach ($totalNumArr as $key => $v) {
                $new['lists'][$key]['Ratio'] = $totalNumArr[$key];
            }
            $new['series']['name']   = '设备故障统计';
            $new['series']['type']   = 'pie';
            $new['series']['radius'] = '40%';
            $new['series']['center'] = ['45%', '45%'];
            return $new;
        } else {
            return [];
        }
    }


    /**
     * 获取设备故障数量 按资产价值区间统计
     *
     * @param int    $startTime   开始时间
     * @param int    $endTime     结束时间
     * @param string $hospital_id 医院id
     *
     * @return array
     * */
    public function getRepairSummaryFromPrice($startTime, $endTime, $hospital_id)
    {
        $price[] = ['min' => 0, 'max' => 1000];
        $price[] = ['min' => 1000, 'max' => 5000];
        $price[] = ['min' => 5000, 'max' => 10000];
        $price[] = ['min' => 10000, 'max' => 30000];
        $price[] = ['min' => 30000, 'max' => -1];
        $fields  = '';
        $where   = "A.applicant_time>=" . $startTime . " AND A.applicant_time<=" . $endTime . ' AND B.hospital_id IN(' . $hospital_id . ')';
        foreach ($price as &$one) {
            $fields .= "(SELECT COUNT(*) FROM sb_repair AS A LEFT JOIN sb_assets_info as B ON A.assid=B.assid  WHERE " . $where . " AND B.buy_price>=$one[min]";
            if ($one['max'] == -1) {
                $fields .= " ) AS '$one[min]~∞',";
            } else {
                $fields .= " AND B.buy_price<$one[max]) AS '$one[min]~$one[max]',";
            }

        }
        $fields = rtrim($fields, ',');
        $lists  = $this->DB_get_one('assets_info', $fields);
        $i      = 0;
        foreach ($lists as $key => $v) {
            $totalNumArr[]                      = $v;
            $new['lists'][$i]['title']          = $key;
            $new['lists'][$i]['totalNum']       = $v;
            $new['legend'][$i]                  = $key;
            $new['series']['data'][$i]['name']  = $key;
            $new['series']['data'][$i]['value'] = $v;
            $i++;
        }
        $totalNumArr = $this->calculationDutyRatio($totalNumArr);
        foreach ($totalNumArr as $key => $v) {
            $new['lists'][$key]['Ratio'] = $totalNumArr[$key];
        }
        $new['series']['name']   = '设备故障统计';
        $new['series']['type']   = 'pie';
        $new['series']['radius'] = '40%';
        $new['series']['center'] = ['45%', '45%'];
        return $new;
    }

    /**
     * Notes:获取报表搜索条件范围
     *
     * @param $startTime int 添加时间始
     * @param $endTime   int 添加时间末
     * @param $priceMin  float 总价区间始
     * @param $priceMax  float 总价区间末
     *
     * @return array
     */
    public function getReportTips()
    {
        $year         = date('Y');
        $default_time = strtotime($year . '-01-01');
        $startTime    = I('POST.startDate') ? strtotime(I('POST.startDate')) : $default_time;
        $endTime      = I('POST.endDate') ? strtotime(I('POST.endDate')) : time();
        $type         = I('POST.type') ? I('POST.type') : 1;
        //报表日期返回
        if ($startTime == 0) {
            $tips = '报表日期：' . '1970-01-01' . ' 至 ' . getHandleTime($endTime) . ' / ';
        } else {
            $tips = '报表日期：' . getHandleTime($startTime) . ' 至 ' . getHandleTime($endTime) . ' / ';
        }
        $tips    .= ' 统计类型：';
        $tableTh = '';
        switch ($type) {
            case 1:
                $tips    .= '按故障类型统计';
                $tableTh = '故障类型';
                break;
            case 2:
                $tips    .= '按设备分类统计';
                $tableTh = '设备分类';
                break;
            case 3:
                $tips    .= '按资产类型统计（急救、特种、计量、质控、效益分析、生命支持类、普通）';
                $tableTh = '资产类型';
                break;
            case 4:
                $tips    .= '按维修性质统计(现场解决、自修、维保厂家、第三方维修)';
                $tableTh = '维修性质';
                break;
            case 5:
                $tips    .= '按资产价值区间统计';
                $tableTh = '资产价值区间';
                break;
        }
        $data['tips']    = $tips;
        $data['tableTh'] = $tableTh;
        return $data;
    }


    /**
     * Notes:工程师可操作列表页连接处理
     *
     * @param $assetsList array 设备数据
     *
     * @return array
     */
    public function getAcceptHtml($assetsList)
    {
        //按钮默认样式-小按钮
        $acceptMenu = get_menu($this->MODULE, $this->Controller, 'accept');
        $startMenu  = get_menu($this->MODULE, $this->Controller, 'startRepair');
        foreach ($assetsList as &$one) {
            $html       = '<div class="layui-btn-group">';
            $detailsUrl = get_url() . '?action=showRepairDetails&assid=' . $one['assid'] . '&repid=' . $one['repid'];
            $html       .= $this->returnListLink('设备详情', C('SHOWASSETS_ACTION_URL'), 'showAssets',
                C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($acceptMenu) {
                switch ($one['status']) {
                    case C('REPAIR_HAVE_REPAIRED'):
                        $html .= $this->returnListLink($acceptMenu['actionname'], $acceptMenu['actionurl'], 'accept',
                            C('BTN_CURRENCY'));
                        break;
                    case C('REPAIR_RECEIPT'):
                        $html .= $this->returnListLink('检修', $acceptMenu['actionurl'], 'accept',
                            C('BTN_CURRENCY') . ' layui-btn-normal');
                        $html .= $this->returnListLink('转单', '', 'modify', C('BTN_CURRENCY') . ' layui-btn-warm');
                        break;
                    case C('REPAIR_HAVE_OVERHAULED'):
                        $html .= $this->returnListLink('配件待出库', $detailsUrl, 'showDetails',
                            C('BTN_CURRENCY') . ' layui-btn-warm');
                        break;
                    case C('REPAIR_QUOTATION'):
                        $html .= $this->returnListLink('已检修', $detailsUrl, 'showDetails',
                            C('BTN_CURRENCY') . ' layui-btn-warm');
                        break;
                    case C('REPAIR_AUDIT'):
                        switch ($one['approve_status']) {
                            case C('REPAIR_IS_NOTCHECK'):
                                $html .= $this->returnListLink('审批中', $detailsUrl, 'showDetails',
                                    C('BTN_CURRENCY') . ' layui-btn-warm');
                                break;
                            case C('REPAIR_IS_CHECK_NOT_THROUGH'):
                                $html .= $this->returnListLink('未通过', $detailsUrl, 'showDetails',
                                    C('BTN_CURRENCY') . ' layui-btn-danger');
                                break;
                            default:
                                $html = '未知参数';
                                break;
                        }
                        break;
                    case C('REPAIR_MAINTENANCE'):
                        $html .= $this->returnListLink('继续维修', $startMenu['actionurl'], 'accept',
                            C('BTN_CURRENCY'));
                        break;
                    case C('REPAIR_MAINTENANCE_COMPLETION'):
                        $html .= $this->returnListLink('待验收', $detailsUrl, 'showDetails',
                            C('BTN_CURRENCY') . ' layui-btn-warm');
                        break;
                    case -1:
                        $html .= $this->returnListLink('已撤单', $detailsUrl, 'showDetails',
                            C('BTN_CURRENCY') . ' layui-btn-warm');
                        break;
                    default:
                        $html .= $this->returnListLink('已完成', $detailsUrl, 'showDetails',
                            C('BTN_CURRENCY') . ' layui-btn-normal');
                        break;
                }
                $one['operation'] .= $html . '</div>';
            }
        }
        return $assetsList;
    }

    //搜索所有故障类型
    public function getAllType()
    {
        $category = $this->DB_get_all('repair_setting', 'id,title', ['parentid' => 0], '', 'id asc');
        $res      = [];
        $i        = 0;
        foreach ($category as $k => $v) {
            $res[$i]['id']    = $k;
            $res[$i]['title'] = $v['title'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        return $arr;
    }


    /**
     * 审批列表页 操作栏连接
     *
     * @param array $asArr     列表数据
     * @param array int 键值
     * @param array $listorder 审核排序
     * @param array $menuData  权限数据
     *
     * @return array
     */
    function format($asArr, $k, $listorder, $menuData, $process)
    {
        $menuHtml   = $this->returnListLink($menuData['actionname'], $menuData['actionurl'], 'approve',
            C('BTN_CURRENCY'));
        $Inoperable = $this->returnListLink('等待审批', $menuData['actionurl'], 'approve',
            C('BTN_CURRENCY') . ' layui-btn-primary');
        $notHtml    = $this->returnListLink('未通过', $menuData['actionurl'], 'approve',
            C('BTN_CURRENCY') . ' layui-btn-danger');
        $tgHtml     = $this->returnListLink('已通过', $menuData['actionurl'], 'approve',
            C('BTN_CURRENCY') . ' layui-btn-danger');
        $checkHtml  = $this->returnListLink('查看', $menuData['actionurl'], 'approve',
            C('BTN_CURRENCY') . ' layui-btn-normal');
        //判断自己是否已审
        //查询审批历史
        $apps       = $this->DB_get_all('approve', '*',
            ['repid' => $asArr[$k]['repid'], 'approve_class' => 'repair', 'process_node' => C('REPAIR_APPROVE')], '',
            'process_node_level,approve_time asc');
        $tmpprocess = $process;
        if (!$apps && $process[0]['listorder'] == 1) {
            //未有审批历史，且第一次序审批审批排序为1
            $asArr[$k]['operation'] = $menuHtml;
        } elseif (!$apps && $process[0]['listorder'] > 1) {
            //未有审核历史，但自己不是第一审批人，需等待上一审批人先审批
            $asArr[$k]['operation'] = $Inoperable;
        } else {
            //有审核历史
            foreach ($apps as $k1 => $v1) {
                foreach ($tmpprocess as $k2 => $v2) {
                    if ($v2['listorder'] == $v1['process_node_level'] && $v1['approver'] == session('username')) {
                        array_splice($tmpprocess, $k2, 1);
                    }
                }
            }
            $totalHistory = count($apps);
            $lastOrder    = $apps[$totalHistory - 1]['process_node_level'];
            $is_adopt     = $apps[$totalHistory - 1]['is_adopt'];
            if (!$tmpprocess) {
                //自己的流程已审批完毕
                $asArr[$k]['operation'] = $checkHtml;
            } else {
                $totalHistory = count($apps);
                $lastOrder    = $apps[$totalHistory - 1]['process_node_level'];
                $is_adopt     = $apps[$totalHistory - 1]['is_adopt'];
                if (($lastOrder + 1) == $tmpprocess[0]['listorder']) {
                    //可审批
                    if ($is_adopt == 1) {
                        $asArr[$k]['operation'] = $menuHtml;
                    } else {
                        //不通过
                        $asArr[$k]['operation'] = $notHtml;
                    }
                } elseif (($lastOrder + 1) < $tmpprocess[0]['listorder']) {
                    //需等待
                    $asArr[$k]['operation'] = $Inoperable;
                } else {
                    //自己的流程都已经审核完毕
                    $asArr[$k]['operation'] = $checkHtml;
                }
            }
        }
        return $asArr[$k];
    }

    /**
     * 获取设备自动派工信息
     *
     * @param int $assid 设备ID
     *
     * @return array
     * */
    public function getEngineer($assid)
    {
        $assetsWhere['assid']       = $assid;
        $assetsData                 = $this->DB_get_one('assets_info', 'catid,departid,helpcatid,assid,hospital_id',
            $assetsWhere);
        $assingJoin                 = 'LEFT JOIN sb_user AS U ON U.userid=A.userid';
        $assingWhere['hospital_id'] = ['EQ', $assetsData['hospital_id']];
        $assingData                 = $this->DB_get_all_join('repair_assign', 'A',
            'A.style,A.userid,A.valuedata,U.username,U.telephone', $assingJoin, $assingWhere, '', 'A.style desc', '');
        if ($assingData) {
            foreach ($assingData as &$assingDataValue) {
                $assingDataValue['dataArr'] = json_decode($assingDataValue['valuedata']);
                if ($assingDataValue['style'] == C('REPAIR_ASSIGN_STYLE_ASSETS')) {
                    if (in_array($assetsData['assid'], $assingDataValue['dataArr'])) {
                        return $assingDataValue;
                    }

                }
                if ($assingDataValue['style'] == C('REPAIR_ASSIGN_STYLE_AUXILIARY')) {
                    if (in_array($assetsData['helpcatid'], $assingDataValue['dataArr'])) {
                        return $assingDataValue;
                    }

                }
                if ($assingDataValue['style'] == C('REPAIR_ASSIGN_STYLE_DEPARTMENT')) {
                    if (in_array($assetsData['departid'], $assingDataValue['dataArr'])) {
                        return $assingDataValue;
                    }

                }
                if ($assingDataValue['style'] == C('REPAIR_ASSIGN_STYLE_CATEGORY')) {
                    if (in_array($assetsData['catid'], $assingDataValue['dataArr'])) {
                        return $assingDataValue;
                    }
                }
            }
        }
        return [];
    }

    /**
     * 查询设备基本信息
     *
     * @param array $where 条件
     *
     * @return array
     * */
    public function getRepair($where)
    {
        $fileds = 'assid,assign_engineer,repid,repnum,status,applicant,applicant_time,applicant_tel,breakdown,applicant_remark,
        from,is_scene,expect_arrive,repair_remark,overhauldate,response,response_date,response_tel,reponse_remark,
        expect_time,repair_type,response_efficiency,checkdate,check_remark,over_status,checkperson,technical_level,
        service_attitude,engineer,engineer_time AS engTime,assist_engineer,assist_engineer_tel,is_offer,
        expect_price,engineer_time,working_hours,overdate,dispose_detail,actual_price AS aPrice';
        //查询设备详情
        $repArr = $this->DB_get_one('repair', $fileds, $where);
        return $repArr;
    }

    /**
     * 获取报修信息
     *
     * @param int $repid 维修id
     *
     * @return array
     * */
    public function getRepairBasic($repid)
    {
        $where['repid'] = ['EQ', $repid];
        $fileds         = 'assid,repid,departid,repnum,status,applicant,applicant_time,applicant_tel,breakdown,applicant_remark,
        from,is_scene,expect_arrive,repair_remark,overhauldate,response,response_date,response_tel,reponse_remark,
        expect_time,repair_type,response_efficiency,checkdate,check_remark,over_status,checkperson,technical_level,
        service_attitude,engineer,assist_engineer,assist_engineer_tel,is_offer,approve_status,other_price,sign_in_time,
        expect_price,engineer_time,working_hours,overdate,dispose_detail,actual_price,current_approver,all_approver,pic_url,wxTapeAmr';
        //查询设备详情
        $repArr = $this->DB_get_one('repair', $fileds, $where);
        Vendor('SM4.SM4');
        $SM4                     = new \SM4();
        $repArr['applicant_tel'] = strlen($repArr['applicant_tel']) > 12 ? $SM4->decrypt($repArr['applicant_tel']) : $repArr['applicant_tel'];
        if ($repArr['wxTapeAmr']) {
            //查询微信录音时长
            $sec               = $this->DB_get_one('repair_record', 'seconds', ['record_url' => $repArr['wxTapeAmr']]);
            $repArr['seconds'] = ceil($sec['seconds']);
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $repArr['department']     = $departname[$repArr['departid']]['department'];
        $repArr['overhaul_time']  = $repArr['overhauldate'] ? $repArr['overhauldate'] : '';
        $repArr['overhauldate']   = getHandleMinute($repArr['overhauldate']);
        $repArr['response_date']  = getHandleDate($repArr['response_date']);
        $repArr['applicant_time'] = date('Y-m-d H:i:s', $repArr['applicant_time']);
        $repArr['expect_time']    = getHandleDate($repArr['expect_time']);
        if ($repArr['engineer_time']) {
            $repArr['engineer_time'] = date('Y-m-d H:i:s', $repArr['engineer_time']);
        } else {
            $repArr['engineer_time'] = '';
        }
        $repArr['checkdate'] = getHandleDate($repArr['checkdate']);
        if ($repArr['status'] >= C('REPAIR_HAVE_OVERHAULED')) {
            $repArr['repTypeName'] = $this->getRepairTypeName($repArr['repair_type']);
        }
        $repArr['dispose_detail'] = html_entity_decode($repArr['dispose_detail']);

        if ($repArr['pic_url']) {
            $repArr['pic_url'] = explode(',', $repArr['pic_url']);
            foreach ($repArr['pic_url'] as &$pic) {
                $pic = str_replace('/Public/uploads/' . C('UPLOAD_DIR_REPAIR_NAME') . '/', '', $pic);
                $pic = '/Public/uploads/' . C('UPLOAD_DIR_REPAIR_NAME') . '/' . $pic;
            }
            $repArr['imgCount']          = count($repArr['pic_url']);
            $repArr['addRepair_pic_url'] = $repArr['pic_url'];
            $repArr['pic_url']           = json_encode($repArr['pic_url']);
        }
        $repArr['asset_info'] = $this->DB_get_one('assets_info', '*', ['assid' => $repArr['assid']]);
//        if ($repArr['pic_url']) {
//            //$repArr['pic_url'] = explode(',', '/Public/uploads/' . C('UPLOAD_DIR_REPAIR_NAME') . '/' . $repArr['pic_url']);
//            $repArr['pic_url'] = explode(',', $repArr['pic_url']);
//            $repArr['imgCount'] = count($repArr['pic_url']);
//            $repArr['addRepair_pic_url'] = $repArr['pic_url'];
//            $repArr['pic_url'] = json_encode($repArr['pic_url']);
//        }
        return $repArr;
    }


    /**
     * 查询设备基本信息
     *
     * @param int $assid 设备id
     *
     * @return array
     * */
    public function getAssetsBasic($assid)
    {
        $where['A.assid'] = ['EQ', $assid];
        $join[0]          = 'LEFT JOIN sb_assets_factory AS B ON B.assid=A.assid';
        $join[1]          = 'LEFT JOIN sb_assets_insurance AS I ON I.assid=A.assid AND I.status=' . C('INSURANCE_STATUS_USE');
        $fileds           = 'A.*,B.factory,B.repair,B.ols_repid,B.repa_user,B.repa_tel,I.status AS guarantee_status,
        I.company,I.company_id,I.contacts,I.telephone';
        $asArr            = $this->DB_get_one_join('assets_info', 'A', $fileds, $join, $where);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $asArr['buy_price'] = '***';
        }
        $departname  = [];
        $catname     = [];
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $asArr['department']  = $departname[$asArr['departid']]['department'];
        $asArr['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$asArr['capitalfrom']];
        $asArr['address']     = $departname[$asArr['departid']]['address'];
        /*  新增跟查询设备一样的设备信息 S*/
        $asArr['cate_name'] = $catname[$asArr['catid']]['category'];
        if ($asArr['is_subsidiary'] == C('YES_STATUS')) {
            //附属设备 获取附属设备辅助分类
            $asArr['helpcat'] = $baseSetting['assets']['acin_category']['value'][$asArr['subsidiary_helpcatid']];
        } else {
            //主设备   获取主设备辅助分类
            $asArr['helpcat'] = $baseSetting['assets']['assets_helpcat']['value'][$asArr['helpcatid']];
        }
        if ($asArr['is_firstaid'] == C('YES_STATUS')) {
            $asArr['type'] = C('ASSETS_FIRST_CODE_YES_NAME');
        }
        if ($asArr['is_special'] == C('YES_STATUS')) {
            $asArr['type'] .= '、' . C('ASSETS_SPEC_CODE_YES_NAME');
        }
        if ($asArr['is_metering'] == C('YES_STATUS')) {
            $asArr['type'] .= '、' . C('ASSETS_METER_CODE_YES_NAME');
        }
        if ($asArr['is_qualityAssets'] == C('YES_STATUS')) {
            $asArr['type'] .= '、' . C('ASSETS_QUALITY_CODE_YES_NAME');
        }
        if ($asArr['is_patrol'] == C('YES_STATUS')) {
            $asArr['type'] .= '、' . C('ASSETS_PATROL_CODE_YES_NAME');
        }
        if ($asArr['is_benefit'] == C('YES_STATUS')) {
            $asArr['type'] .= '、' . C('ASSETS_BENEFIT_CODE_YES_NAME');
        }
        if ($asArr['is_lifesupport'] == C('YES_STATUS')) {
            $asArr['type'] .= '、' . C('ASSETS_LIFE_SUPPORT_NAME');
        }
        $asArr['type']    = trim($asArr['type'], '、');
        $asArr['finance'] = $baseSetting['assets']['assets_finance']['value'][$asArr['financeid']];
        $asArr['assfrom'] = $baseSetting['assets']['assets_assfrom']['value'][$asArr['assfromid']];
        switch ($asArr['depreciation_method']) {
            case 1:
                $asArr['depreciation_method_name'] = '平均折旧法';
                break;
            case 2:
                $asArr['depreciation_method_name'] = '工作量法';
                break;
            case 3:
                $asArr['depreciation_method_name'] = '加速折旧法';
                break;
            default:
                $asArr['depreciation_method_name'] = '';
                break;
        }
        /*  新增跟查询设备一样的设备信息 E*/
        $asArr['opendate']       = HandleEmptyNull($asArr['opendate']);
        $asArr['guarantee_date'] = HandleEmptyNull($asArr['guarantee_date']);
        if (getHandleTime(time()) < $asArr['guarantee_date']) {
            $asArr['guaranteeStatus'] = '保修期内';
            $asArr['is_guarantee']    = C('YES_STATUS');
            //维保的读保修厂家
            $asArr['salesman_name']  = $asArr['repa_user'];
            $asArr['salesman_phone'] = $asArr['repa_tel'];
            $asArr['guarantee_id']   = $asArr['ols_repid'];
            $asArr['guarantee_name'] = $asArr['repair'];
        } else {
            if ($asArr['guarantee_status'] == C('INSURANCE_STATUS_USE')) {
                $asArr['guaranteeStatus'] = '参保期内';
                $asArr['is_guarantee']    = C('YES_STATUS');
                //读维保表的维保信息
                $asArr['salesman_name']  = $asArr['contacts'];
                $asArr['salesman_phone'] = $asArr['telephone'];
                $asArr['guarantee_id']   = $asArr['company_id'];
                $asArr['guarantee_name'] = $asArr['repair'];
            } else {
                $asArr['guaranteeStatus']        = '<span style="color: red;">脱保</span>';
                $asArr['guaranteeStatusNoColor'] = '脱保';
            }
        }
        if ($asArr['pic_url']) {
            $asArr['pic_url'] = explode(',', $asArr['pic_url']);
            foreach ($asArr['pic_url'] as &$pic) {
                $pic = '/Public/uploads/assets/' . $pic;
            }
            $asArr['imgCount'] = count($asArr['pic_url']);
            $asArr['pic_url']  = json_encode($asArr['pic_url']);
        }
        return $asArr;
    }

    /**
     * 格式化维修数据
     *
     * @param array $asArr
     *
     * @return array
     * */
    public function formatRepair($asArr)
    {
        $departname = [];
        $catname    = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $asArr['department']     = $departname[$asArr['departid']]['department'];
        $asArr['category']       = $catname[$asArr['catid']]['category'];
        $asArr['overhauldate']   = getHandleMinute($asArr['overhauldate']);
        $asArr['response_date']  = getHandleMinute($asArr['response_date']);
        $asArr['applicant_time'] = getHandleTime($asArr['applicant_time']);
        $asArr['expect_time']    = getHandleTime($asArr['expect_time']);
        $asArr['engineer_time']  = getHandleTime($asArr['engineer_time']);
        if (getHandleTime(time()) < $asArr['guarantee_date']) {
            $asArr['guaranteeStatus'] = '保修期内';
        } else {
            if ($asArr['guarantee_status'] == C('INSURANCE_STATUS_USE')) {
                $asArr['guaranteeStatus'] = '参保期内';
            } else {
                $asArr['guaranteeStatus'] = '<span style="color: red;">脱保</span>';
            }
        }
        $asArr['repTypeName'] = $this->getRepairTypeName($asArr['repair_type']);
        return $asArr;
    }

    /**
     * 获取该维修单的具体故障类型和问题
     *
     * @param int $repid 维修单id
     *
     * @return string
     * */
    public function getFaultProblem($repid)
    {
        $fields        = 'B.title AS fault,C.title AS problem';
        $JOIN[0]       = 'LEFT JOIN sb_repair_setting AS B ON B.id=A.fault_type_id';
        $JOIN[1]       = 'LEFT JOIN sb_repair_setting AS C ON C.id=A.fault_problem_id';
        $fault         = $this->DB_get_all_join('repair_fault', 'A', $fields, $JOIN, ['repid' => $repid], '', '', '');
        $fault_problem = '<ul>';
        foreach ($fault as $k => $v) {
            $fault_problem .= '<li class="fault_css">' . $v['fault'] . '-' . $v['problem'] . '</li>';
        }
        $fault_problem .= '</ul>';
        return $fault_problem;
    }

    /**
     * 格式化维修类型
     *
     * @param int $repair_type 类型ID
     *
     * @return string
     * */
    private function getRepairTypeName($repair_type)
    {
        switch ($repair_type) {
            case C('REPAIR_TYPE_IS_STUDY'):
                $repTypeName = '自修';
                break;
            case C('REPAIR_TYPE_IS_GUARANTEE'):
                $repTypeName = '维保厂家';
                break;
            case C('REPAIR_TYPE_THIRD_PARTY'):
                $repTypeName = '第三方维修';
                break;
            case C('REPAIR_TYPE_IS_SCENE'):
                $repTypeName = '现场解决';
                break;
            default:
                $repTypeName = '未知参数';
                break;
        }
        return $repTypeName;
    }

    /**
     * 维修模块验证是否需要报价
     *
     * @param int $totalPrice 价格
     *
     * @return boolean
     * */
    private function checkRepairApprove($totalPrice)
    {
        $isOpenApprove = $this->checkApproveIsOpen(C('REPAIR_APPROVE'), session('job_hospitalid'));
        if ($isOpenApprove) {
            //开启了审批
            return true;
        } else {
            return false;
        }
    }

    /**
     * @Notes: 根据维修单ID组获取该设备所有维修历史的配件信息
     *
     * @param $repidArr array 维修单ID组
     *
     * @return array
     */
    public function getAllPartsLists($repidArr)
    {
        $join[] = " LEFT JOIN sb_repair AS A ON B.repid=A.repid ";
        $fields = "A.repnum,A.repid,B.partid,B.parts,B.part_model,B.part_num,B.part_price,B.price_sum";
        $lists  = $this->DB_get_all_join('repair_parts', 'B', $fields, $join, ['A.repid' => ['in', $repidArr]], '', '',
            '');
        return $lists;
    }

    /**
     * 显示维修详情 （后台数据）
     */
    public function showRepairData($repid)
    {
        $repairinfo = $this->DB_get_one('repair', '', ['repid' => $repid]);
        //报修日期
        $repairinfo['applicant_time'] = getHandleTime($repairinfo['applicant_time']);
        //响应时间
        $repairinfo['response_date'] = getHandleDate($repairinfo['response_date']);
        //预计修复日期
        $repairinfo['expect_time'] = getHandleTime($repairinfo['expect_time']);
        //处理详情进行转义
        $repairinfo['dispose_detail'] = html_entity_decode($repairinfo['dispose_detail']);
        //维修日期
        $repairinfo['engineer_time'] = getHandleTime($repairinfo['engineer_time']);
        //验收时间
        $repairinfo['checkdate'] = getHandleTime($repairinfo['checkdate']);
        //维修性质
        if ($repairinfo['repair_type'] == C('REPAIR_TYPE_IS_STUDY')) {
            $repairinfo['repairTypeName'] = '自修';
        } elseif ($repairinfo['repair_type'] == C('REPAIR_TYPE_IS_GUARANTEE')) {
            $repairinfo['repairTypeName'] = '维保厂家';
        } elseif ($repairinfo['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')) {
            $repairinfo['repairTypeName'] = '第三方维修';
        } elseif ($repairinfo['repair_type'] == C('REPAIR_TYPE_IS_SCENE')) {
            $repairinfo['repairTypeName'] = '现场解决';
        } else {
            $repairinfo['repairTypeName'] = '';
        }
        if ($repairinfo['wxTapeAmr']) {
            //查询微信录音时长
            $sec                   = $this->DB_get_one('repair_record', 'seconds',
                ['record_url' => $repairinfo['wxTapeAmr']]);
            $repairinfo['seconds'] = ceil($sec['seconds']);
        }
        if ($repairinfo['pic_url']) {
            $repairinfo['pic_url'] = explode(',', $repairinfo['pic_url']);
            foreach ($repairinfo['pic_url'] as &$pic) {
                $pic = str_replace('/Public/uploads/' . C('UPLOAD_DIR_REPAIR_NAME') . '/', '', $pic);
                $pic = '/Public/uploads/' . C('UPLOAD_DIR_REPAIR_NAME') . '/' . $pic;
            }
            $repairinfo['imgCount']          = count($repairinfo['pic_url']);
            $repairinfo['addRepair_pic_url'] = $repairinfo['pic_url'];
            $repairinfo['pic_url']           = json_encode($repairinfo['pic_url']);
        }
        return $repairinfo;
    }

    /**
     * 显示维修记录 进度条 时间线 （后台数据）
     */

    public function showRepairTimeLineData($repid)
    {
        $repairInfo = $this->DB_get_one('repair', '', ['repid' => $repid]);

        $join    = 'LEFT JOIN sb_parts_outware_record_detail AS R ON R.outwareid=O.outwareid';
        $outware = $this->DB_get_all_join('parts_outware_record', 'O', 'O.outdate,R.adduser,O.addtime', $join,
            ['O.repid' => $repid, 'O.status' => C('REPAIR_IS_CHECK_ADOPT')], 'O.outwareid', '', '');


        $approve = $this->DB_get_all('approve', 'approver,approve_time,is_adopt', ['repid' => $repid]);

        $numberArr = ['①', '②', '③', '④', '⑤', '⑥', '⑦', '⑧', '⑨', '⑩'];

        $repairTimeLine = [];

//        $re

        $addRepair['statusName'] = '科室报修';
        $addRepair['date']       = getHandleDate($repairInfo['applicant_time']);
        $addRepair['user']       = $repairInfo['applicant'];
        if ($repairInfo['from'] == 1) {
            $addRepair['text'] = '通过<span style="color:#FF5722;">PC端</span>报修';
        } else {
            $addRepair['text'] = '通过<span style="color:#FF5722;">微信端</span>报修';
        }
        $repairTimeLine['addRepair'] = $addRepair;


        $addRepair['statusName']    = '派工响应';
        $addRepair['date']          = getHandleDate($repairInfo['assign_time']);
        $addRepair['user']          = $repairInfo['assign_engineer'];
        $addRepair['text']          = '收到派单提醒';
        $repairTimeLine['assigned'] = $addRepair;


        $addRepair['statusName']  = '接单响应';
        $addRepair['date']        = getHandleDate($repairInfo['response_date']);
        $addRepair['user']        = $repairInfo['response'];
        $addRepair['text']        = ' 进行接单操作，预计到场时间为 ' . $repairInfo['expect_arrive'] . ' 分钟';
        $repairTimeLine['accept'] = $addRepair;


        $addRepair['statusName'] = '签到检修';
        $addRepair['date']       = getHandleDate($repairInfo['overhauldate']);
        $addRepair['user']       = $repairInfo['response'];
        if ($repairInfo['is_scene'] == 1) {
            $addRepair['text'] = ' 对设备进行了初检咨询，并现场解决了相关故障！';
        } else {
            $addRepair['text'] = ' 对设备进行了初检咨询，预计修复日期：' . getHandleTime($repairInfo['expect_time']);
        }
        $repairTimeLine['overhaul'] = $addRepair;

        //将详细的分开记录
        $repairTimeLineData = $repairTimeLine;


        if ($repairInfo['status'] >= C('REPAIR_MAINTENANCE_COMPLETION')) {
            $addRepair['statusName'] = '维修结束';
            $addRepair['text']       = ' 维修设备结束';
        } else {
            $addRepair['statusName'] = '继续维修';
            $addRepair['text']       = ' 继续进行维修';
        }
        $addRepair['date']             = getHandleDate($repairInfo['overdate']);
        $addRepair['user']             = $repairInfo['engineer'];
        $repairTimeLine['startRepair'] = $repairTimeLineData['startRepair'] = $addRepair;


        $addRepair['statusName']       = '科室验收';
        $addRepair['date']             = getHandleDate($repairInfo['checkdate']);
        $addRepair['user']             = $repairInfo['checkperson'];
        $addRepair['text']             = ' 对维修单进行验收，维修状态为：' . ($repairInfo['over_status'] == 0 ? '<span style="color:red;">未修复</span>' : '<span style="color:green;">已修复</span>');
        $repairTimeLine['checkRepair'] = $repairTimeLineData['checkRepair'] = $addRepair;


        $resetArr = [];
        foreach ($repairTimeLineData as &$one) {
            //将配件出库 与维修审批节点按照时间插入
            $resetArr[] = $one;
        }
        $repairTimeLineData = $resetArr;

        if ($outware) {
            foreach ($outware as &$one) {
                $addRepair['statusName'] = '配件出库';
                $addRepair['date']       = $one['addtime'];
                $addRepair['user']       = $one['adduser'];
                $addRepair['text']       = ' 同意配件于' . $one['outdate'] . '出库';
                $repairTimeLineData      = $this->insertArrToDate($repairTimeLineData, $addRepair);
            }
        }


        if ($approve) {
            foreach ($approve as &$one) {
                $addRepair['statusName'] = '维修审核';
                $addRepair['date']       = getHandleDate($one['approve_time']);
                $addRepair['user']       = $one['approver'];
                $one['is_adopt']         = $one['is_adopt'] == C('REPAIR_IS_CHECK_ADOPT') ? '<span style="color: green;">通过</span>' : '<span style="color:#FF5722;">未通过</span>';
                $addRepair['text']       = ' 对设备进行审核 审批结果：' . $one['is_adopt'];
                $repairTimeLineData      = $this->insertArrToDate($repairTimeLineData, $addRepair);
            }
        }

        $number = 0;
        foreach ($repairTimeLine as &$one) {
            $one['number'] = $numberArr[$number];
            $number++;
        }

        //报修
        $repairTimeLine['addRepair']['class'] = 'completeProgress';
        //派单
        if ($repairInfo['status'] == C('REPAIR_HAVE_REPAIRED')) {
            if ($repairInfo['is_assign'] == C('YES_STATUS')) {
                //已派单
                $repairTimeLine['assigned']['class'] = 'completeProgress';
                $repairTimeLine['accept']['class']   = 'doingProgress';
                $next                                = true;
            } else {
                //未派单
                $repairTimeLine['assigned']['class'] = 'doingProgress';
                $next                                = false;
            }
        } else {
            //已经接单
            $repairTimeLine['assigned']['class'] = 'completeProgress';
            $next                                = true;
        }

        //接单
        if ($next == true) {
            if ($repairInfo['status'] >= C('REPAIR_RECEIPT')) {
                //已接单
                $repairTimeLine['accept']['class'] = 'completeProgress';
                $next                              = true;
            } else {
                //派单and未接单
                $repairTimeLine['accept']['class'] = 'doingProgress';
                $next                              = false;
            }
        } else {
            //未接单and未派单
            $repairTimeLine['accept']['class'] = 'nocompleteProgress';
            $next                              = false;
        }

        //检修
        if ($next == true) {
            if ($repairInfo['status'] >= C('REPAIR_HAVE_OVERHAULED')) {
                //已检修
                $repairTimeLine['overhaul']['class'] = 'completeProgress';
                $next                                = true;
            } else {
                //未检修
                $repairTimeLine['overhaul']['class'] = 'doingProgress';
                $next                                = false;
            }
        } else {
            //未接单
            $repairTimeLine['overhaul']['class'] = 'nocompleteProgress';
            $next                                = false;
        }

        //继续维修
        if ($next == true) {
            if ($repairInfo['status'] > C('REPAIR_MAINTENANCE')) {
                //维修结束
                $repairTimeLine['startRepair']['class'] = 'completeProgress';
                $next                                   = true;
            } else {
                //维修中
                $repairTimeLine['startRepair']['class'] = 'doingProgress';
                $next                                   = false;
            }
        } else {
            //未检修
            $repairTimeLine['startRepair']['class'] = 'nocompleteProgress';
            $next                                   = false;
        }

        //验收
        if ($next == true) {
            if ($repairInfo['status'] == C('REPAIR_MAINTENANCE_COMPLETION')) {
                //未验收
                $repairTimeLine['checkRepair']['class'] = 'doingProgress';
            } else {
                //已验收
                $repairTimeLine['checkRepair']['class'] = 'completeProgress';
            }
        } else {
            //未结束维修
            $repairTimeLine['checkRepair']['class'] = 'nocompleteProgress';
        }

        $result['repairTimeLine']     = $repairTimeLine;
        $result['repairTimeLineData'] = $repairTimeLineData;
        return $result;
    }


    private function insertArrToDate($Arr, $inserArr)
    {
        foreach ($Arr as $key => $value) {
            if ($value['date'] > $inserArr['date']) {
                $new[] = $inserArr;
                array_splice($Arr, $key, 0, $new);

            } elseif ($key == count($Arr) - 1) {
                $Arr[] = $inserArr;
            }
        }
        return $Arr;
    }

    //旧版
    public function showRepairTimeLineData2($repid)
    {
        $repairInfo                      = $this->DB_get_one('repair', '', ['repid' => $repid]);
        $repairTimeLine                  = [];
        $repairTimeLine[0]['statusName'] = '科室报修';
        $repairTimeLine[0]['date']       = getHandleDate($repairInfo['applicant_time']);
        $repairTimeLine[0]['number']     = '①';
        $repairTimeLine[0]['user']       = $repairInfo['applicant'];
        $repairTimeLine[0]['text']       = ' 通过PC端报修';
        $repairTimeLine[1]['statusName'] = '派工响应';
        $repairTimeLine[1]['date']       = getHandleDate($repairInfo['assign_time']);
        $repairTimeLine[1]['number']     = '②';
        $repairTimeLine[1]['user']       = $repairInfo['assign_engineer'];
        $repairTimeLine[1]['text']       = ' 收到派单提醒';
        $repairTimeLine[2]['statusName'] = '接单响应';
        $repairTimeLine[2]['date']       = getHandleDate($repairInfo['response_date']);
        $repairTimeLine[2]['number']     = '③';
        $repairTimeLine[2]['user']       = $repairInfo['response'];
        $repairTimeLine[2]['text']       = ' 进行接单操作，预计到场时间为 ' . $repairInfo['expect_arrive'] . ' 分钟';
        $repairTimeLine[3]['statusName'] = '签到检修';
        $repairTimeLine[3]['number']     = '④';
        $repairTimeLine[3]['user']       = $repairInfo['response'];
        $repairTimeLine[3]['date']       = getHandleDate($repairInfo['overhauldate']);
        $repairTimeLine[3]['text']       = ' 对设备进行了初检咨询，预计修复日期：' . getHandleTime($repairInfo['expect_time']);
        $repairTimeLine[4]['statusName'] = '维修审核';
        $repairTimeLine[4]['number']     = '⑤';
        $repairTimeLine[4]['date']       = getHandleDate($repairInfo['examine_time']);
        $repairTimeLine[4]['user']       = $repairInfo['examine_user'];
        $repairTimeLine[4]['text']       = ' 对设备进行审核';
        $repairTimeLine[5]['statusName'] = '维修结束';
        $repairTimeLine[5]['number']     = '⑤';
        $repairTimeLine[5]['date']       = getHandleDate($repairInfo['overdate']);
        $repairTimeLine[5]['user']       = $repairInfo['engineer'];
        $repairTimeLine[5]['text']       = ' 维修设备结束';
        $repairTimeLine[6]['statusName'] = '科室验收';
        $repairTimeLine[6]['number']     = '⑥';
        $repairTimeLine[6]['date']       = getHandleDate($repairInfo['checkdate']);
        $repairTimeLine[6]['user']       = $repairInfo['checkperson'];
        $repairTimeLine[6]['text']       = ' 对维修单进行验收，维修状态为：' . ($repairInfo['over_status'] == 0 ? '<span style="color:red;">未修复</span>' : '<span style="color:green;">已修复</span>');


        //$complete = 1 未操作 2 已操作 3 正在操作
        //报修
        if ($repairInfo['status'] == C('REPAIR_HAVE_REPAIRED')) {
            $repairTimeLine[0]['class'] = 'doingProgress';
            $complete                   = 3;
        } else {
            $repairTimeLine[0]['class'] = 'completeProgress';
            $complete                   = 1;
        }
        //派工
        if ($complete == 3) {
            if ($repairInfo['is_assign'] == 1) {
                $repairTimeLine[0]['class'] = 'completeProgress';
                $repairTimeLine[1]['class'] = 'doingProgress';
                $complete                   = 3;
            } else {
                $repairTimeLine[1]['class'] = 'nocompleteProgress';
                $complete                   = 2;
            }
        } else {
            if ($repairInfo['status'] > C('REPAIR_HAVE_REPAIRED')) {
                $repairTimeLine[1]['class'] = 'completeProgress';
                $complete                   = 2;
            }
        }
        //接单
        if ($complete != 3) {
            if ($repairInfo['status'] > C('REPAIR_HAVE_REPAIRED')) {
                if ($repairInfo['status'] == C('REPAIR_RECEIPT')) {
                    $repairTimeLine[2]['class'] = 'doingProgress';
                    $complete                   = 3;
                } else {
                    $repairTimeLine[2]['class'] = 'completeProgress';
                    $complete                   = 2;
                }
            } else {
                $repairTimeLine[2]['class'] = 'nocompleteProgress';
            }
        } else {
            $repairTimeLine[2]['class'] = 'nocompleteProgress';
        }
        //检修
        if ($complete != 3) {
            if ($repairInfo['status'] > C('REPAIR_RECEIPT')) {
                if ($repairInfo['status'] == C('REPAIR_QUOTATION')) {
                    $repairTimeLine[3]['class'] = 'doingProgress';
                    $complete                   = 3;
                } else {
                    $repairTimeLine[3]['class'] = 'completeProgress';
                    $complete                   = 2;
                }
            } else {
                $repairTimeLine[3]['class'] = 'nocompleteProgress';
            }
        } else {
            $repairTimeLine[3]['class'] = 'nocompleteProgress';
        }
        //审核
        if ($complete != 3) {
            if ($repairInfo['status'] > C('REPAIR_QUOTATION')) {
                if ($repairInfo['status'] == C('REPAIR_AUDIT')) {
                    $repairTimeLine[4]['class'] = 'doingProgress';
                    $complete                   = 3;
                } else {
                    $repairTimeLine[4]['class'] = 'completeProgress';
                    $complete                   = 2;
                }
            } else {
                $repairTimeLine[4]['class'] = 'nocompleteProgress';
            }
        } else {
            $repairTimeLine[4]['class'] = 'nocompleteProgress';
        }
        //继续维修
        if ($complete != 3) {
            if ($repairInfo['status'] > C('REPAIR_AUDIT')) {
                if ($repairInfo['status'] == C('REPAIR_MAINTENANCE')) {
                    $repairTimeLine[4]['class'] = 'doingProgress';
                    $complete                   = 3;
                } else {
                    $repairTimeLine[5]['class'] = 'completeProgress';
                    $complete                   = 2;
                }
            } else {
                $repairTimeLine[5]['class'] = 'nocompleteProgress';
            }
        } else {
            $repairTimeLine[5]['class'] = 'nocompleteProgress';
        }
        //已验收
        if ($complete != 3) {
            if ($repairInfo['status'] > C('REPAIR_MAINTENANCE')) {
                if ($repairInfo['status'] == C('REPAIR_MAINTENANCE_COMPLETION')) {
                    $repairTimeLine[4]['class'] = 'doingProgress';
                    $repairTimeLine[6]['class'] = 'nocompleteProgress';
                }
                if ($repairInfo['status'] == C('REPAIR_ALREADY_ACCEPTED')) {
                    $repairTimeLine[6]['class'] = 'doingProgress';
                }
            } else {
                $repairTimeLine[6]['class'] = 'nocompleteProgress';
            }
        } else {
            $repairTimeLine[6]['class'] = 'nocompleteProgress';
        }
        return $repairTimeLine;
    }

    /**
     * 获取用户号码
     */
    public function getUserNameAndPhone($data)
    {
        Vendor('SM4.SM4');
        $SM4     = new \SM4();
        $userArr = [];
        if ($data) {
            foreach ($data as &$value) {
                $userArr[] = $value['user'];
            }
            $where['is_delete'] = ['EQ', C('NO_STATUS')];
            $where['username']  = ['IN', $userArr];
            $fields             = 'username,telephone';
            $role               = $this->DB_get_all('user', $fields, $where);
            foreach ($data as $k => $v) {
                foreach ($role as $k1 => $v1) {
                    if ($v['user'] == $v1['username']) {
                        $data[$k]['telephone'] = $SM4->decrypt($v1['telephone']);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @Notes: 获取故障问题
     *
     * @param $repidArr string 父级id
     *
     * @return array
     */
    public function getRepairProblem($parentid)
    {
        $type    = $this->DB_get_all('repair_setting', 'id,title', ['status' => 1, 'parentid' => 0]);
        $problem = $this->DB_get_all('repair_setting', 'id,title,parentid',
            ['status' => 1, 'parentid' => ['IN', $parentid]]);
        $data    = [];
        foreach ($problem as $k => $v) {
            foreach ($type as $k1 => $v1) {
                if ($v['parentid'] == $v1['id']) {
                    $data[$k]['name']  = $v1['title'] . ' : ' . $v['title'];
                    $data[$k]['value'] = $v1['id'] . '-' . $v['id'];
                }
            }
        }
        return $data;
    }


    /**
     * @Notes: 获取转至报修的设备详情
     *
     * @param $confirmId int 转至报修记录id
     *
     * @return array
     */
    public function getConfirmAddRepairArr($confirmId)
    {
        $where      = "confirmId=$confirmId";
        $join[0]    = 'LEFT JOIN sb_patrol_plan_cycle AS B ON B.cycid=A.cycid';
        $join[1]    = 'LEFT JOIN sb_assets_info AS C ON C.assnum=A.assnum';
        $join[2]    = 'LEFT JOIN sb_patrol_plan AS D ON D.patrid=B.patrid';
        $fields     = 'C.departid,C.assets,C.model,C.catid,
                A.confirmId,A.assnum,A.patroluser,A.abnormalText,A.cycid,A.status,A.applicant,A.comfirmDate,
                C.is_firstaid,C.is_special,C.is_metering,C.is_qualityAssets,B.patrol_level,D.patrolname';
        $catname    = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        $asArr                    = $this->DB_get_one_join('confirm_add_repair', 'A', $fields, $join, $where);
        $asArr['cat_name']        = $catname[$asArr['catid']]['category'];
        $asArr['department_name'] = $departname[$asArr['departid']]['department'];
        $asArr['comfirmDate']     = getHandleTime($asArr['comfirmDate']);
        switch ($asArr['status']) {
            case C('ASSETS_STATUS_USE'):
                $asArr['status_name'] = C('ASSETS_STATUS_USE_NAME');
                break;
            case C('ASSETS_STATUS_REPAIR'):
                $asArr['status_name'] = C('ASSETS_STATUS_REPAIR_NAME');
                break;
            case C('ASSETS_STATUS_SCRAP'):
                $asArr['status_name'] = C('ASSETS_STATUS_SCRAP_NAME');
                break;
        }
        if ($asArr['is_firstaid'] == C('ASSETS_FIRST_CODE_YES')) {
            $asArr['type_name'] = C('ASSETS_FIRST_CODE_YES_NAME');
        }
        if ($asArr['is_special'] == C('ASSETS_SPEC_CODE_YES')) {
            $asArr['type_name'] .= ',' . C('ASSETS_SPEC_CODE_YES_NAME');
        }
        if ($asArr['is_metering'] == C('ASSETS_METER_CODE_YES')) {
            $asArr['type_name'] = C('ASSETS_METER_CODE_YES_NAME');
        }
        if ($asArr['is_qualityAssets'] == C('ASSETS_QUALITY_CODE_YES')) {
            $asArr['type_name'] .= ',' . C('ASSETS_QUALITY_CODE_YES_NAME');
        }
        $asArr['type_name'] = ltrim($asArr['type_name'], ",");
        switch ($asArr['patrol_level']) {
            case C('PATROL_LEVEL_RC'):
                $asArr['patrol_level'] = C('PATROL_LEVEL_NAME_RC');
                break;
            case C('PATROL_LEVEL_XC'):
                $asArr['patrol_level'] = C('PATROL_LEVEL_NAME_XC');
                break;
            case C('PATROL_LEVEL_PM'):
                $asArr['patrol_level'] = C('PATROL_LEVEL_NAME_PM');
                break;
            default :
                $asArr['patrol_level'] = '异常参数';
        }
        if ($asArr['status'] == C('SWITCH_REPAIR_CONFIRM')) {
            $where                     = 'status!=' . C('REPAIR_ALREADY_ACCEPTED') . " AND assnum='$asArr[assnum]' AND applicant='$asArr[applicant]'";
            $repair                    = $this->DB_get_one('repair', 'applicant_remark', $where);
            $asArr['applicant_remark'] = $repair['applicant_remark'];
        }
        $executeJoin   = 'LEFT JOIN sb_patrol_execute_abnormal AS B ON B.execid=A.execid';
        $executeWhere  = "A.cycid=$asArr[cycid] AND A.assetnum='$asArr[assnum]'";
        $point         = $this->DB_get_all_join('patrol_execute', 'A', 'result,asset_status', $executeJoin,
            $executeWhere, '', '', '');
        $abnormalCount = 0;
        foreach ($point as &$one) {
            if ($one['result'] != '合格') {
                $abnormalCount++;
            }
        }
        $asArr['asset_status']  = $point[0]['asset_status'];
        $asArr['count']         = count($point);
        $asArr['abnormalCount'] = $abnormalCount;
        return $asArr;
    }


    /**
     * @Notes: 验证是否签到
     *
     * @param $repid int 维修单id
     *
     * @return boolean
     */
    public function check_sign_in($repid)
    {
        $result      = false;
        $moduleModel = new ModuleModel();
        $wx_status   = $moduleModel->decide_wx_login();
        if ($wx_status) {
            //开启微信
            $logchek = $this->DB_get_one('log', 'logid',
                ['module' => 'repair', 'action' => 'sign', 'actionid' => $repid, 'username' => session('username')]);
            if (!$logchek) {
                $result = true;
            }
        } else {
            $result = true;
        }
        return $result;
    }

    /**
     * @Notes: 获取故障类型 故障问题数据
     * @return array
     */
    public function get_all_repair_type()
    {
        $type       = $this->DB_get_all('repair_setting', 'id,title,parentid', ['status' => 1]);
        $repairType = [];
        if ($type) {
            foreach ($type as $k => $v) {
                if ($v['parentid'] == 0) {
                    $repairType[$k] = $type[$k];
                    foreach ($type as &$two) {
                        if ($type[$k]['id'] == $two['parentid']) {
                            $repairType[$k]['parent'][] = $two;
                        }
                    }
                }
            }
        }
        return $repairType;
    }

    /**
     * Notes: 获取维修费用统计搜索条件初始数据
     */
    public function getStatisSearchOption()
    {
        //获取当前所在医院科室
        $hospital_id = session('current_hospitalid');
        //获取用户可管理医院的部门
        //获取所有医院的名称
        $hospitalName    = $this->DB_get_all('hospital', 'hospital_id,hospital_name', ['is_delete' => 0]);
        $hospitalNameArr = $departments = [];
        foreach ($hospitalName as $v) {
            $hospitalNameArr[$v['hospital_id']] = $v['hospital_name'];
        }
        $departs = $this->DB_get_all('department', 'departid,department,hospital_id',
            ['hospital_id' => $hospital_id, 'is_delete' => 0]);
        foreach ($departs as $v) {
            $departments[$v['hospital_id']]['hospital_name'] = $hospitalNameArr[$v['hospital_id']];
            $departments[$v['hospital_id']]['list'][]        = $v;
        }
        $result['departments'] = $departments;
        //维修类型
        $result['repair_type'][C('REPAIR_TYPE_IS_STUDY')]['id']       = C('REPAIR_TYPE_IS_STUDY');
        $result['repair_type'][C('REPAIR_TYPE_IS_STUDY')]['name']     = C('REPAIR_TYPE_IS_STUDY_NAME');
        $result['repair_type'][C('REPAIR_TYPE_IS_GUARANTEE')]['id']   = C('REPAIR_TYPE_IS_GUARANTEE');
        $result['repair_type'][C('REPAIR_TYPE_IS_GUARANTEE')]['name'] = C('REPAIR_TYPE_IS_GUARANTEE_NAME');
        $result['repair_type'][C('REPAIR_TYPE_THIRD_PARTY')]['id']    = C('REPAIR_TYPE_THIRD_PARTY');
        $result['repair_type'][C('REPAIR_TYPE_THIRD_PARTY')]['name']  = C('REPAIR_TYPE_THIRD_PARTY_NAME');
        $result['repair_type'][C('REPAIR_TYPE_IS_SCENE')]['id']       = C('REPAIR_TYPE_IS_SCENE');
        $result['repair_type'][C('REPAIR_TYPE_IS_SCENE')]['name']     = C('REPAIR_TYPE_IS_SCENE_NAME');

        //故障类型
        $result['fault_type'] = $this->DB_get_all('repair_setting', 'id,title', ['parentid' => 0, 'status' => 1]);

        $userModel       = new UserModel();
        $result['users'] = $userModel->getUsers('accept', '', false, false);
        return $result;
    }

    /**
     * Notes: 维修费用统计
     *
     * @return mixed
     */
    public function getAllRepairRecord()
    {
        $end_date          = date('Y-m-d');
        $start_date        = date('Y-m-d', strtotime("-6 month"));
        $order             = I('POST.order') ? I('POST.order') : 'DESC';
        $sort              = I('POST.sort') ? I('POST.sort') : 'overdate';
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $engineer          = I('POST.engineer');
        $departid          = I('POST.departid');
        $repair_type       = I('POST.repair_type');
        $catid             = I('POST.catid');
        $fault_type        = I('POST.fault_type');
        $startDate         = I('POST.startDate');
        $endDate           = I('POST.endDate');
        $hospital_id       = I('POST.hospital_id');
        $where['A.status'] = ['egt', 7];
        $departids         = session('departid');
        if (!$departids) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['B.departid'] = ['in', $departids];
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($catid) {
            $where['B.catid'] = $catid;
        }
        if ($engineer) {
            $where['A.engineer'] = $engineer;
        }
        if ($departid) {
            $where['B.departid'] = $departid;
        }
        if ($repair_type != '') {
            $where['A.repair_type'] = $repair_type;
        }
        if ($fault_type) {
            $where['A.fault_type'] = ['like', "%$fault_type%"];
        }
        if ($startDate && !$endDate) {
            $where['A.overdate'] = ['egt', strtotime($startDate)];
        }
        if ($endDate && !$startDate) {
            $where['A.overdate'] = ['elt', strtotime($endDate) + 24 * 3600 - 1];
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return ['status' => -1, 'msg' => '请选择合理的日期区间！'];
            }
            $where['A.overdate'] = [['egt', strtotime($startDate)], ['elt', strtotime($endDate) + 24 * 3600 - 1]];
        }
        if (!$startDate && !$endDate) {
            $where['A.overdate'] = [['egt', strtotime($start_date)], ['elt', strtotime($end_date) + 24 * 3600 - 1]];
        }
        $join   = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.repid,A.status,A.repnum,A.assid,A.assnum,A.assets,A.part_num,A.part_total_price,A.repair_type,A.fault_type,A.departid as applicant_departid,A.actual_price,A.engineer,A.overdate,A.over_status,B.hospital_id,B.catid,B.departid";
        $total  = $this->DB_get_count_join('repair', 'A', $join, $where);
        $data   = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        //查询所有故障类型
        $ftypes    = $this->DB_get_all('repair_setting', 'id,title', ['parentid' => 0, 'status' => 1]);
        $ftypesarr = [];
        foreach ($ftypes as $k => $v) {
            $ftypesarr[$v['id']] = $v['title'];
        }
        $catname    = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        foreach ($data as $k => $v) {
            $data[$k]['part_num']             = (int)$v['part_num'];
            $data[$k]['part_total_price']     = (float)$v['part_total_price'];
            $data[$k]['actual_price']         = (float)$v['actual_price'];
            $data[$k]['department']           = $departname[$v['departid']]['department'];
            $data[$k]['applicant_department'] = $departname[$v['applicant_departid']]['department'];
            $data[$k]['category']             = $catname[$v['catid']]['category'];
            $data[$k]['overdate']             = date('Y-m-d', $v['overdate']);
            $data[$k]['over_status_name']     = $v['over_status'] == 1 ? '已修复' : '<div style="color:#FF5722;">未修复</div>';
            switch ($v['repair_type']) {
                case C('REPAIR_TYPE_IS_STUDY'):
                    $data[$k]['repair_type_name'] = C('REPAIR_TYPE_IS_STUDY_NAME');
                    break;
                case C('REPAIR_TYPE_IS_GUARANTEE'):
                    $data[$k]['repair_type_name'] = C('REPAIR_TYPE_IS_GUARANTEE_NAME');
                    break;
                case C('REPAIR_TYPE_THIRD_PARTY'):
                    $data[$k]['repair_type_name'] = C('REPAIR_TYPE_THIRD_PARTY_NAME');
                    break;
                case C('REPAIR_TYPE_IS_SCENE'):
                    $data[$k]['repair_type_name'] = C('REPAIR_TYPE_IS_SCENE_NAME');
                    break;
            }
            $data[$k]['fault_type_name'] = '';
            foreach (explode(',', $v['fault_type']) as $k1 => $v1) {
                $data[$k]['fault_type_name'] .= $ftypesarr[$v1] . ',';
            }
            $data[$k]['fault_type_name'] = trim($data[$k]['fault_type_name'], ',');
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $data;
        return $result;
    }

    /**
     * Notes: 获取维修费用分析数据
     *
     * @param $type string 统计类型
     */
    public function getDataLists($type)
    {
        $result = [];
        switch ($type) {
            case 'department':
                $result = $this->getListByDepartment();
                break;
            case 'fault_type':
                $result = $this->getListByFaultType();
                break;
            case 'category':
                $result = $this->getListByCate();
                break;
        }
        return $result;
    }

    /**
     * Notes: 科室维修数据统计
     *
     * @return mixed
     */
    public function getListByDepartment()
    {
        $end_date          = date('Y-m-d');
        $start_date        = date('Y-m-d', strtotime("-6 month"));
        $order             = I('POST.order') ? I('POST.order') : 'DESC';
        $sort              = I('POST.sort') ? I('POST.sort') : 'repair_num';
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $startDate         = I('POST.startDate');
        $endDate           = I('POST.endDate');
        $hospital_id       = I('POST.hospital_id');
        $where['A.status'] = ['egt', 7];
        $departids         = session('departid');
        if (!$departids) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['B.departid'] = ['in', $departids];
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['A.overdate'] = ['egt', strtotime($startDate)];
        }
        if ($endDate && !$startDate) {
            $where['A.overdate'] = ['elt', strtotime($endDate) + 24 * 3600 - 1];
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return ['status' => -1, 'msg' => '请选择合理的日期区间！'];
            }
            $where['A.overdate'] = [['egt', strtotime($startDate)], ['elt', strtotime($endDate) + 24 * 3600 - 1]];
        }
        if (!$startDate && !$endDate) {
            $where['A.overdate'] = [['egt', strtotime($start_date)], ['elt', strtotime($end_date) + 24 * 3600 - 1]];
        }
        $join       = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields     = "count(*) as repair_num,A.fault_type,sum(part_num) as part_num,sum(part_total_price) as part_total_price,sum(actual_price) as repair_fee,B.departid";
        $count      = $this->DB_get_one_join('repair', 'A', 'count(*) total,sum(actual_price) as total_price', $join,
            $where);
        $data       = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, 'B.departid', $sort . ' ' . $order,
            '');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $chartData = [];
        foreach ($data as $k => $v) {
            $data[$k]['repair_num']       = (int)$v['repair_num'];
            $data[$k]['part_num']         = (int)$v['part_num'];
            $data[$k]['repair_fee']       = (float)$v['repair_fee'];
            $data[$k]['part_total_price'] = (float)$v['part_total_price'];
            $data[$k]['department']       = $departname[$v['departid']]['department'];
            $data[$k]['num_ratio']        = number_format($v['repair_num'] / $count['total'] * 100, 2) . '%';
            $data[$k]['fee_ratio']        = number_format($v['repair_fee'] / $count['total_price'] * 100, 2) . '%';
        }
        //组织图表数据
        foreach ($data as $k => $v) {
            $chartData['legend_data'][$k]          = $v['department'];
            $chartData['series_data'][$k]['value'] = $v['repair_fee'];
            $chartData['series_data'][$k]['name']  = $v['department'];
        }
//        $result['total'] = $count['total'];
//        $result["offset"] = $offset;
//        $result["limit"] = $limit;
        $result["code"]     = 200;
        $result['rows']     = $data;
        $result['charData'] = $chartData;
        if (!$result['rows']) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 故障类型维修数据统计
     *
     * @return mixed
     */
    public function getListByFaultType()
    {
        $end_date          = date('Y-m-d');
        $start_date        = date('Y-m-d', strtotime("-6 month"));
        $order             = I('POST.order') ? I('POST.order') : 'DESC';
        $sort              = I('POST.sort') ? I('POST.sort') : 'repair_num';
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $startDate         = I('POST.startDate');
        $endDate           = I('POST.endDate');
        $hospital_id       = I('POST.hospital_id');
        $where['A.status'] = ['egt', 7];
        $departids         = session('departid');
        if (!$departids) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['B.departid'] = ['in', $departids];
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['A.overdate'] = ['egt', strtotime($startDate)];
        }
        if ($endDate && !$startDate) {
            $where['A.overdate'] = ['elt', strtotime($endDate) + 24 * 3600 - 1];
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return ['status' => -1, 'msg' => '请选择合理的日期区间！'];
            }
            $where['A.overdate'] = [['egt', strtotime($startDate)], ['elt', strtotime($endDate) + 24 * 3600 - 1]];
        }
        if (!$startDate && !$endDate) {
            $where['A.overdate'] = [['egt', strtotime($start_date)], ['elt', strtotime($end_date) + 24 * 3600 - 1]];
        }
        $join   = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.repid,A.departid,B.catid,A.fault_type,part_num,part_total_price,actual_price as repair_fee";
        $data   = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, 'A.repid', '', '');
        //查询所有故障类型
        $ftypes       = $this->DB_get_all('repair_setting', 'id,title', ['parentid' => 0, 'status' => 1]);
        $ftypesidsarr = [];
        $ftypesarr    = [];
        foreach ($ftypes as $k => $v) {
            $ftypesidsarr[]      = $v['id'];
            $ftypesarr[$v['id']] = $v['title'];
        }
        $res       = $chartData = $result = [];
        $total_num = count($data);
        $total_fee = $num = 0;
        foreach ($data as $k => $v) {
            $total_fee += $v['repair_fee'];
            $faulttype = explode(',', $v['fault_type']);
            foreach ($faulttype as $key => $val) {
                if (!in_array($ftypesarr[$val], $chartData['legend_data'])) {
                    $chartData['legend_data'][$val] = $ftypesarr[$val];
                    $res[$val]['fault_id']          = $val;
                    $res[$val]['fault_type_name']   = $ftypesarr[$val];
                    if (!isset($res[$val]['repair_num'])) {
                        $res[$val]['repair_num'] = 0;
                    }
                    if (!isset($res[$val]['repair_fee'])) {
                        $res[$val]['repair_fee'] = 0;
                    }
                    if (!isset($chartData['series_data'][$val]['value'])) {
                        $chartData['series_data'][$val]['value'] = 0;
                    }
                    $res[$val]['repair_num']                 += 1;
                    $res[$val]['repair_fee']                 += $v['repair_fee'];
                    $chartData['series_data'][$val]['value'] = $res[$val]['repair_fee'];
                    $chartData['series_data'][$val]['name']  = $ftypesarr[$val];
                } else {
                    $res[$val]['repair_num']                 += 1;
                    $res[$val]['repair_fee']                 += $v['repair_fee'];
                    $chartData['series_data'][$val]['value'] = $res[$val]['repair_fee'];
                }
            }
        }
        $res = array_values($res);
        foreach ($res as $k => $v) {
            $res[$k]['num_ratio'] = number_format($v['repair_num'] / $total_num * 100, 2) . '%';
            $res[$k]['fee_ratio'] = number_format($v['repair_fee'] / $total_fee * 100, 2) . '%';
        }
        $char['legend_data'] = array_values($chartData['legend_data']);
        $char['series_data'] = array_values($chartData['series_data']);
        $result['total']     = count($res);
        $result["offset"]    = $offset;
        $result["limit"]     = $limit;
        $result["code"]      = 200;
        $result['rows']      = $res;
        $result['charData']  = $res ? $char : [];
        if (!$result['rows']) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 设备分类维修数据统计
     *
     * @return mixed
     */
    public function getListByCate()
    {
        $end_date          = date('Y-m-d');
        $start_date        = date('Y-m-d', strtotime("-6 month"));
        $order             = I('POST.order') ? I('POST.order') : 'DESC';
        $sort              = I('POST.sort') ? I('POST.sort') : 'repair_num';
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $startDate         = I('POST.startDate');
        $endDate           = I('POST.endDate');
        $hospital_id       = I('POST.hospital_id');
        $where['A.status'] = ['egt', 7];
        $departids         = session('departid');
        if (!$departids) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['B.departid'] = ['in', $departids];
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['A.overdate'] = ['egt', strtotime($startDate)];
        }
        if ($endDate && !$startDate) {
            $where['A.overdate'] = ['elt', strtotime($endDate) + 24 * 3600 - 1];
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return ['status' => -1, 'msg' => '请选择合理的日期区间！'];
            }
            $where['A.overdate'] = [['egt', strtotime($startDate)], ['elt', strtotime($endDate) + 24 * 3600 - 1]];
        }
        if (!$startDate && !$endDate) {
            $where['A.overdate'] = [['egt', strtotime($start_date)], ['elt', strtotime($end_date) + 24 * 3600 - 1]];
        }
        $join    = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields  = "count(*) as repair_num,A.departid,B.catid,A.fault_type,sum(part_num) as part_num,sum(part_total_price) as part_total_price,sum(actual_price) as repair_fee";
        $count   = $this->DB_get_one_join('repair', 'A', 'count(*) total,sum(actual_price) as total_price', $join,
            $where);
        $data    = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, 'B.catid', $sort . ' ' . $order, '');
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        $chartData = [];
        foreach ($data as $k => $v) {
            $data[$k]['repair_num']       = (int)$v['repair_num'];
            $data[$k]['part_num']         = (int)$v['part_num'];
            $data[$k]['repair_fee']       = (float)$v['repair_fee'];
            $data[$k]['part_total_price'] = (float)$v['part_total_price'];
            $data[$k]['category']         = $catname[$v['catid']]['category'];
            $data[$k]['num_ratio']        = number_format($v['repair_num'] / $count['total'] * 100, 2) . '%';
            $data[$k]['fee_ratio']        = number_format($v['repair_fee'] / $count['total_price'] * 100, 2) . '%';
        }
        //组织图表数据
        foreach ($data as $k => $v) {
            $chartData['legend_data'][$k]          = $v['category'];
            $chartData['series_data'][$k]['value'] = $v['repair_fee'];
            $chartData['series_data'][$k]['name']  = $v['category'];
        }
//        $result['total'] = $count['total'];
//        $result["offset"] = $offset;
//        $result["limit"] = $limit;
        $result["code"]     = 200;
        $result['rows']     = $data;
        $result['charData'] = $chartData;
        if (!$result['rows']) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取维修工程师工作量数据
     */
    public function getEngineerJobRecords()
    {
        $end_date          = date('Y-m-d');
        $start_date        = date('Y-m-d', strtotime("-6 month"));
        $order             = I('POST.order') ? I('POST.order') : 'DESC';
        $sort              = I('POST.sort') ? I('POST.sort') : 'repair_num';
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $startDate         = I('POST.startDate');
        $endDate           = I('POST.endDate');
        $hospital_id       = I('POST.hospital_id');
        $where['A.status'] = ['egt', 7];
        $departids         = session('departid');
        if (!$departids) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['B.departid'] = ['in', $departids];
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['A.overdate'] = ['egt', strtotime($startDate)];
        }
        if ($endDate && !$startDate) {
            $where['A.overdate'] = ['elt', strtotime($endDate) + 24 * 3600 - 1];
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return ['status' => -1, 'msg' => '请选择合理的日期区间！'];
            }
            $where['A.overdate'] = [['egt', strtotime($startDate)], ['elt', strtotime($endDate) + 24 * 3600 - 1]];
        }
        if (!$startDate && !$endDate) {
            $where['A.overdate'] = [['egt', strtotime($start_date)], ['elt', strtotime($end_date) + 24 * 3600 - 1]];
        }
        $join   = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.engineer,count(*) as repair_num,sum(working_hours) as repair_time";
        $data   = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, 'A.engineer', $sort . ' ' . $order,
            $offset . "," . $limit);
        //查询已验收、修复数据
        $where['A.checkdate'] = ['EXP', 'IS NOT NULL'];
        $checkdata            = $this->DB_get_all_join('repair', 'A',
            'A.engineer,count(*) as check_num,sum(over_status) as over_status', $join, $where, 'A.engineer');
        $engineerdata         = [];
        $total_over_num       = $all_check_num = 0;
        foreach ($checkdata as $k => $v) {
            $engineerdata[$v['engineer']]['check_num']   = $v['check_num'];
            $engineerdata[$v['engineer']]['over_status'] = $v['over_status'];
            $total_over_num                              += $v['over_status'];
        }
        $chartData = [];
        foreach ($data as $k => $v) {
            $data[$k]['repair_num']          = (int)$v['repair_num'];
            $data[$k]['repair_time']         = (float)$v['repair_time'];
            $data[$k]['avg_time']            = round((float)($data[$k]['repair_time'] / $data[$k]['repair_num']), 2);
            $data[$k]['over_status_num']     = 0;
            $data[$k]['not_over_status_num'] = 0;
            $data[$k]['not_check_num']       = 0;
            if ($engineerdata[$v['engineer']]) {
                $data[$k]['over_status_num']     = $engineerdata[$v['engineer']]['over_status'];
                $data[$k]['not_over_status_num'] = $engineerdata[$v['engineer']]['check_num'] - $engineerdata[$v['engineer']]['over_status'];
                $data[$k]['not_check_num']       = $v['repair_num'] - $engineerdata[$v['engineer']]['check_num'];
            }
            $data[$k]['repair_rate'] = number_format($data[$k]['over_status_num'] / $total_over_num * 100, 2) . '%';
        }
        //组织图表数据
        foreach ($data as $k => $v) {
            $chartData['repair_num']['title']                    = '维修次数';
            $chartData['repair_num']['legend_data'][$k]          = $v['engineer'];
            $chartData['repair_num']['series_data'][$k]['value'] = $v['repair_num'];
            $chartData['repair_num']['series_data'][$k]['name']  = $v['engineer'];

            $chartData['repair_time']['title']                    = '维修时长';
            $chartData['repair_time']['legend_data'][$k]          = $v['engineer'];
            $chartData['repair_time']['series_data'][$k]['value'] = $v['repair_time'];
            $chartData['repair_time']['series_data'][$k]['name']  = $v['engineer'];

            $chartData['repair_rate']['title']                    = '修复占比';
            $chartData['repair_rate']['legend_data'][$k]          = $v['engineer'];
            $chartData['repair_rate']['series_data'][$k]['value'] = $v['over_status_num'];
            $chartData['repair_rate']['series_data'][$k]['name']  = $v['engineer'];
        }
//        $result['total'] = count($data);
//        $result["offset"] = $offset;
//        $result["limit"] = $limit;
        $result["code"]     = 200;
        $result['rows']     = $data;
        $result['charData'] = $chartData;
        if (!$result['rows']) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取维修工程师评价数据
     */
    public function getEngineerEvaRecords()
    {
        $end_date          = date('Y-m-d');
        $start_date        = date('Y-m-d', strtotime("-6 month"));
        $order             = I('POST.order') ? I('POST.order') : 'DESC';
        $sort              = I('POST.sort') ? I('POST.sort') : 'checkdate';
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $startDate         = I('POST.startDate');
        $endDate           = I('POST.endDate');
        $hospital_id       = I('POST.hospital_id');
        $where['A.status'] = C('REPAIR_ALREADY_ACCEPTED');
        $departids         = session('departid');
        if (!$departids) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['B.departid'] = ['in', $departids];
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['A.checkdate'] = ['egt', strtotime($startDate)];
        }
        if ($endDate && !$startDate) {
            $where['A.checkdate'] = ['elt', strtotime($endDate) + 24 * 3600 - 1];
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return ['status' => -1, 'msg' => '请选择合理的日期区间！'];
            }
            $where['A.checkdate'] = [['egt', strtotime($startDate)], ['elt', strtotime($endDate) + 24 * 3600 - 1]];
        }
        if (!$startDate && !$endDate) {
            $where['A.checkdate'] = [['egt', strtotime($start_date)], ['elt', strtotime($end_date) + 24 * 3600 - 1]];
        }
        $join      = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields    = "A.repid,A.engineer,A.checkdate,group_concat(service_attitude) as service_attitude,group_concat(technical_level) as technical_level,group_concat(response_efficiency) as response_efficiency";
        $count     = $this->DB_get_one_join('repair', 'A', 'count(DISTINCT engineer) as total', $join, $where, '');
        $data      = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, 'A.engineer', $sort . ' ' . $order,
            $offset . "," . $limit);
        $chartData = $res = [];
        //初始化数据
        foreach ($data as $k => $v) {
            $times                 = explode(',', $v['service_attitude']);
            $res[$k]['engineer']   = $v['engineer'];
            $res[$k]['repair_num'] = count($times);

            $res[$k]['technical_level_1'] = 0;
            $res[$k]['technical_level_2'] = 0;
            $res[$k]['technical_level_3'] = 0;

            $res[$k]['response_efficiency_1'] = 0;
            $res[$k]['response_efficiency_2'] = 0;
            $res[$k]['response_efficiency_3'] = 0;

            $res[$k]['service_attitude_1'] = 0;
            $res[$k]['service_attitude_2'] = 0;
            $res[$k]['service_attitude_3'] = 0;

            $res[$k]['score_1'] = 0;
            $res[$k]['score_2'] = 0;
            $res[$k]['score_3'] = 0;
            $res[$k]['score_4'] = 0;
        }
        //统计各评价点数量
        foreach ($data as $k => $v) {
            $technicaldata = explode(',', $v['technical_level']);
            foreach ($technicaldata as $key => $val) {
                switch ($val) {
                    case 0:
                        $res[$k]['technical_level_1'] += 1;
                        break;
                    case 1:
                        $res[$k]['technical_level_2'] += 1;
                        break;
                    case 2:
                        $res[$k]['technical_level_3'] += 1;
                        break;
                }
            }
            $efficiencydata = explode(',', $v['response_efficiency']);
            foreach ($efficiencydata as $key => $val) {
                switch ($val) {
                    case 0:
                        $res[$k]['response_efficiency_1'] += 1;
                        break;
                    case 1:
                        $res[$k]['response_efficiency_2'] += 1;
                        break;
                    case 2:
                        $res[$k]['response_efficiency_3'] += 1;
                        break;
                }
            }
            $attitudedata = explode(',', $v['service_attitude']);
            foreach ($attitudedata as $key => $val) {
                switch ($val) {
                    case 0:
                        $res[$k]['service_attitude_1'] += 1;
                        break;
                    case 1:
                        $res[$k]['service_attitude_2'] += 1;
                        break;
                    case 2:
                        $res[$k]['service_attitude_3'] += 1;
                        break;
                }
            }
        }
        //统计各项得分
        foreach ($res as $k => $v) {
            //技术得分
            $tech_score         = ($v['technical_level_1'] * 5) + ($v['technical_level_2'] * 3) + ($v['technical_level_3'] * 2);
            $tech_score         = number_format(number_format($tech_score / $v['repair_num'], 2) * 2, 2);
            $res[$k]['score_1'] = $tech_score;

            //时效得分
            $efficiency_score   = ($v['response_efficiency_1'] * 5) + ($v['response_efficiency_2'] * 3) + ($v['response_efficiency_3'] * 2);
            $efficiency_score   = number_format(number_format($efficiency_score / $v['repair_num'], 2) * 2, 2);
            $res[$k]['score_2'] = $efficiency_score;

            //态度得分
            $attitude_score     = ($v['service_attitude_1'] * 5) + ($v['service_attitude_2'] * 3) + ($v['service_attitude_3'] * 2);
            $attitude_score     = number_format(number_format($attitude_score / $v['repair_num'], 2) * 2, 2);
            $res[$k]['score_3'] = $attitude_score;

            $res[$k]['score_4'] = number_format(($tech_score * 0.5 + $efficiency_score * 0.3 + $attitude_score * 0.2),
                2);

            $chartData['legend_data'][$k]          = $v['engineer'];
            $chartData['series_data'][$k]['value'] = $res[$k]['score_4'];
            $chartData['series_data'][$k]['name']  = $v['engineer'];
        }
        //组织图表数据
//        foreach ($data as $k=>$v){
//            $chartData['repair_num']['legend_data'][$k] = $v['engineer'];
//            $chartData['repair_num']['series_data'][$k]['value'] = $v['repair_num'];
//            $chartData['repair_num']['series_data'][$k]['name'] = $v['engineer'];
//
//            $chartData['repair_time']['legend_data'][$k] = $v['engineer'];
//            $chartData['repair_time']['series_data'][$k]['value'] = $v['repair_time'];
//            $chartData['repair_time']['series_data'][$k]['name'] = $v['engineer'];
//
//            $chartData['repair_rate']['legend_data'][$k] = $v['engineer'];
//            $chartData['repair_rate']['series_data'][$k]['value'] = $v['over_status_num'];
//            $chartData['repair_rate']['series_data'][$k]['name'] = $v['engineer'];
//        }
        $result['total']    = $count['$count'];
        $result["offset"]   = $offset;
        $result["limit"]    = $limit;
        $result["code"]     = 200;
        $result['rows']     = $res;
        $result['charData'] = $chartData;
        if (!$result['rows']) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /*
    * 上传文件
    * */
    public function uploadfile()
    {
        if ($_FILES['file']) {
            $Tool    = new ToolController();
            $style   = [
                'JPG',
                'PNG',
                'JPEG',
                'PDF',
                'BMP',
                'DOC',
                'DOCX',
                'jpg',
                'png',
                'jpeg',
                'pdf',
                'bmp',
                'doc',
                'docx',
            ];
            $dirName = C('UPLOAD_DIR_REPAIR_NAME');
            $info    = $Tool->upFile($style, $dirName);
            if ($info['status'] == C('YES_STATUS')) {
                // 上传成功 获取上传文件信息
                $resule['status']   = 1;
                $resule['msg']      = '上传成功';
                $resule['path']     = $info['src'];
                $resule['title']    = $info['title'];
                $resule['formerly'] = $info['formerly'];
                $resule['ext']      = $info['ext'];
                $resule['size']     = $info['size'];
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

    /**
     * 保存报告
     *
     * @param $file array 保存在服务器文件信息
     *
     * @return array
     * */
    public function addCheckFile($file)
    {
        $id     = trim(I('POST.id'));
        $idName = trim(I('POST.idName'));
        $this->checkstatus(judgeNum($id), '非法操作');
        $this->checkstatus(judgeNum($id), '非法操作');
        if (!$id) {
            die(json_encode(['status' => -1, 'msg' => '参数缺少,请按正常流程操作']));
        }
        $add['file_name']    = $file['formerly'];
        $add[$idName . 'id'] = $id;
        $add['type']         = 'report';
        $add['save_name']    = $file['title'];
        $add['file_type']    = $file['ext'];
        $add['file_size']    = $file['size'];
        $add['file_url']     = $file['path'];
        $add['add_user']     = session('username');
        $add['add_time']     = getHandleDate(time());

        $file_id         = $this->insertData('repair_file', $add);
        $file['file_id'] = $file_id;
        return $file;
    }

    /*
     * */
    public function insetRepairFile($file)
    {
        $repid = trim(I('POST.repid'));
        $this->checkstatus(judgeNum($repid), '非法操作');
        $add['file_name'] = $file['formerly'];
        $add['repid']     = $repid;
        $add['type']      = ACTION_NAME;
        $add['save_name'] = $file['title'];
        $add['file_type'] = $file['ext'];
        $add['file_size'] = $file['size'];
        $add['file_url']  = $file['path'];
        $add['add_user']  = session('username');
        $add['add_time']  = getHandleDate(time());
        $file_id          = $this->insertData('repair_file', $add);
        $file['file_id']  = $file_id;
        return $file;
    }

    public function uploadStartRepairFile($file)
    {
        $repid = trim(I('POST.id'));
        $this->checkstatus(judgeNum($repid), '非法操作');
        $add['file_name'] = $file['formerly'];
        $add['repid']     = $repid;
        $add['type']      = 'startRepair';
        $add['save_name'] = $file['title'];
        $add['file_type'] = $file['ext'];
        $add['file_size'] = $file['size'];
        $add['file_url']  = $file['path'];
        $add['add_user']  = session('username');
        $add['add_time']  = getHandleDate(time());
        $file_id          = $this->insertData('repair_file', $add);
        $file['file_id']  = $file_id;
        return $file;
    }

    //删除报告
    public function deleteFile()
    {
        $file_id = I('POST.file_id');
        if ($file_id) {
            $where['file_id'] = $file_id;
            $data             = $this->DB_get_one('repair_file', 'is_delete', $where);
            if ($data) {
                if ($data['is_delete'] != C('YES_STATUS')) {
                    $data['is_delete'] = C('YES_STATUS');
                    $save              = $this->updateData('repair_file', $data, $where);
                    if ($save) {
                        return ['status' => 1, 'msg' => '删除成功'];
                    } else {
                        return ['status' => -1, 'msg' => '删除失败'];
                    }
                } else {
                    die(json_encode(['status' => -1, 'msg' => '文件已删除,请勿重复操作']));
                }
            } else {
                die(json_encode(['status' => -1, 'msg' => '文件不存在,请刷新页面重新操作']));
            }
        } else {
            die(json_encode(['status' => -1, 'msg' => '参数缺少,请按正常流程操作']));
        }
    }

    /**
     * 记录上传外调文件
     *
     * @param $outid int 外调id
     * @param $type  string 上传节点
     */
    public function addRepairFile($repid, $type)
    {
        $delfileid = trim(I('POST.delfileid'));
        if ($delfileid) {
            $where['repid']   = ['EQ', $repid];
            $where['file_id'] = ['IN', $delfileid];
            $this->updateData('repair_file', ['is_delete' => C('YES_STATUS')], $where);
        }
        $file_name = explode('|', rtrim(I('POST.file_name'), '|'));
        $save_name = explode('|', rtrim(I('POST.save_name'), '|'));
        $file_url  = explode('|', rtrim(I('POST.file_url'), '|'));
        $file_type = explode('|', rtrim(I('POST.file_type'), '|'));
        $file_size = explode('|', rtrim(I('POST.file_size'), '|'));
        $addAll    = [];
        $data      = [];
        foreach ($file_name as $k => $v) {
            if (!$v) {
                continue;
            } else {
                $data[$k]['type']      = $type;
                $data[$k]['repid']     = $repid;
                $data[$k]['file_name'] = $v;
                $data[$k]['save_name'] = $save_name[$k];
                $data[$k]['file_type'] = $file_type[$k];
                $data[$k]['file_size'] = $file_size[$k];
                $data[$k]['file_url']  = $file_url[$k];
                $data[$k]['add_user']  = session('username');
                $data[$k]['add_time']  = getHandleDate(time());
                $addAll[]              = $data[$k];
            }
        }
        if ($addAll) {
            $this->insertDataALL('repair_file', $data);
        }
    }


    /** 获取配件库 信息
     *
     * @param $type string 获取的类型 personal个人库 all总库
     *
     * @return array
     */
    public function getPartsInfo()
    {
        $fields               = 'COUNT(*) AS total,GROUP_CONCAT(detailid) AS detailid,parts,parts_model';
        $group                = 'parts,parts_model';
        $where['is_use']      = C('NO_STATUS');
        $where['status']      = ['EQ', C('NO_STATUS')];
        $where['repid']       = ['EQ', 0];
        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        return $data = $this->DB_get_all('parts_inware_record_detail', $fields, $where, $group);
    }

    //获取配件字典
    public function getPartsDic()
    {
        $where['status']    = ['EQ', C('OPEN_STATUS')];
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $data               = $this->DB_get_all('dic_parts', 'parts,parts_model', ['status' => 1], 'parts,parts_model',
            'parts');
        return $data;
    }

    /** 获取维修故障类型
     *
     * @param $type string 获取的类型
     *
     * @return array
     */
    public function getRepairType()
    {
        $type = $this->DB_get_all('repair_setting', 'id,title', ['status' => 1, 'parentid' => 0]);
        return $type;
    }

    /**
     * Notes: 获取医院近三年来维修设备概况
     *
     * @param $hospital_id int 医院ID
     */
    public function getThreeYearsRepairData($hospital_id)
    {

    }

    /**
     * Notes: 获取年度每月各科室维修数据
     *
     * @param $hospital_id int 医院ID
     * @param $year        int year 要获取的年份
     * @param $departids   string 要搜索的科室ID
     */
    public function getDepartFreeEachMonths($hospital_id, $year, $departids = '')
    {
        $year                   = $year ? $year : date('Y');
        $departids              = $departids ? $departids : '';
        $time_start             = strtotime($year . '-01-01 00:00:01');
        $time_end               = strtotime($year . '-12-31 23:59:59');
        $where['B.hospital_id'] = $hospital_id;
        $where['A.status']      = ['egt', 7];
        if ($departids) {
            $where['B.departid'] = ['in', $departids];
        }
        $where['A.overdate'] = [['egt', $time_start], ['elt', $time_end], 'and'];
        $join                = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields              = "B.hospital_id,B.departid,group_concat(FROM_UNIXTIME(overdate, '%m')) as months,group_concat(actual_price) as actual_price,
group_concat(working_hours) as total_hours";
        $data                = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, 'B.departid');
        $departname          = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k => $v) {
            $data[$k]['department'] = $departname[$v['departid']]['department'];
            $data[$k]['year']       = $year;
            for ($i = 1; $i <= 12; $i++) {
                $data[$k]['repair_num_' . $i]   = 0;
                $data[$k]['repair_hours_' . $i] = 0;
                $data[$k]['repair_price_' . $i] = 0;
            }
        }
        foreach ($data as $k => $v) {
            $months       = explode(',', $v['months']);
            $actual_price = explode(',', $v['actual_price']);
            $total_hours  = explode(',', $v['total_hours']);
            foreach ($months as $k1 => $v1) {
                $data[$k]['repair_num_' . (int)$v1]   += 1;
                $data[$k]['repair_hours_' . (int)$v1] += $total_hours[$k1];
                $data[$k]['repair_price_' . (int)$v1] += $actual_price[$k1];
            }
        }
        foreach ($data as $k => $v) {
            $total_num = $total_hours = $total_price = 0;
            for ($i = 1; $i <= 12; $i++) {
                $data[$k]['repair_price_' . $i] = round($data[$k]['repair_price_' . $i], 2);
                $total_num                      += $data[$k]['repair_num_' . $i];
                $total_hours                    += $data[$k]['repair_hours_' . $i];
                $total_price                    += $data[$k]['repair_price_' . $i];
            }
            $data[$k]['year_total_num']   = $total_num;
            $data[$k]['year_total_hours'] = $total_hours;
            $data[$k]['year_total_price'] = round($total_price, 2);
        }
        return $data;
    }


    //获取各科室设备维修费用及次数
    public function getDepartFreeNum($hospital_id, $start, $end)
    {
        //已验收
        $where['B.hospital_id']    = $hospital_id;
        $where['A.status']         = ['in', ['1', '8']];
        $where['A.applicant_time'] = [['egt', $start], ['elt', $end], 'and'];
        $join                      = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields                    = "B.departid, A.actual_price";
        $data                      = $this->DB_get_all_join('repair', 'A', $fields, $join, $where);
        $departNum                 = 0;
        $chart_data                = [];
        //空时，获取初始科室并赋值
        if (empty($data)) {
            $departmentWhere['hospital_id'] = $hospital_id;
            $departmentWhere['is_delete']   = 0;
            $depart                         = M('department')
                ->field('departid')
                ->where($departmentWhere)
                ->select();
            foreach ($depart as $k => $v) {
                $chart_data[$k]['num']          = 0;
                $chart_data[$k]['actual_price'] = 0;
                $chart_data[$k]['departid']     = $v['departid'];
                $departNum                      += 1;
            }
        } else {
            $departIdArr = M('repair')
                ->alias('A')
                ->join('sb_assets_info AS B ON A.assid = B.assid', "LEFT")
                ->field('A.departid')
                ->where($where)
                ->group("A.departid")
                ->select();
            foreach ($departIdArr as $k => $v) {
                foreach ($data as $kk => $vv) {
                    if ($v['departid'] == $vv['departid']) {
                        $chart_data[$k]['num']          += 1;
                        $chart_data[$k]['actual_price'] += round($vv['actual_price'] / 1000, 2);
                        $chart_data[$k]['departid']     = $vv['departid'];
                    }
                }
            }
            $departNum = count($chart_data);
        }

        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($chart_data as $k => $v) {
            $chart_data[$k]['department'] = $departname[$v['departid']]['department'];

        }
        $title = '科室报修汇总TOP' . $departNum;
        foreach ($chart_data as $k => $v) {
            $res['depart'][]         = $v['department'];
            $res['repair']['data'][] = $v['num'];
            $res['free']['data'][]   = $v['actual_price'];
        }

        //默认柱状图，折线图y轴最大值
        $free_max        = 10;
        $free_interval   = 1;
        $free_check      = 0;
        $repair_max      = 10;
        $repair_interval = 1;
        $repair_check    = 0;
        //取最大值，次数
        foreach ($res['repair']['data'] as $k => $v) {
            if ($v > $repair_check) {
                $repair_check = $v;
            }
        }
        switch (strlen($repair_check)) {
            case 2:
                if ($repair_check == $repair_max) {
                    $repair_max = $repair_check;
                } else {
                    $head_num        = substr($repair_check, 0, 1);
                    $repair_max      = ($head_num + 1) * 10;
                    $repair_interval = $repair_max / 10;
                }
                break;
            case 3:
                if ($repair_check == $repair_max) {
                    $repair_max = $repair_check;
                } else {
                    $head_num        = substr($repair_check, 0, 1);
                    $repair_max      = ($head_num + 1) * 100;
                    $repair_interval = $repair_max / 100;
                }
                break;
            default:
                # code...
                break;
        }
        //取最大值，费用
        foreach ($res['free']['data'] as $k => $v) {
            if ($v > $free_check) {
                $free_check = $v;
            }
        }
        switch (strlen(round($free_check))) {
            case 2:
                if ($repair_check == $free_max) {
                    $free_max = $repair_check;
                } else {
                    $head_num      = substr($free_interval, 0, 1);
                    $free_max      = ($head_num + 1) * 10;
                    $free_interval = $free_max / 10;
                }
                break;
            case 3:
                if ($free_interval == $free_max) {
                    $free_max = $free_interval;
                } else {
                    $head_num      = substr($free_interval, 0, 1);
                    $free_max      = ($head_num + 1) * 100;
                    $free_interval = $free_max / 100;
                }
                break;
            default:
                # code...
                break;
        }
        $res['repair']['interval'] = $repair_interval;
        $res['repair']['max']      = $repair_max;
        $res['free']['interval']   = $free_interval;
        $res['free']['max']        = $free_max;
        $res['title']              = $title;
        return $res;
    }


    //格式化短信内容
    public static function formatSmsContent($content, $data)
    {
        $content = str_replace("{repnum}", $data['repnum'], $content);
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{assnum}", $data['assnum'], $content);
        $content = str_replace("{department}", $data['department'], $content);
        $content = str_replace("{applicant}", $data['applicant'], $content);
        $content = str_replace("{applicant_tel}", $data['applicant_tel'], $content);
        $content = str_replace("{response}", $data['response'], $content);
        $content = str_replace("{response_tel}", $data['response_tel'], $content);
        $content = str_replace("{approve_status}", $data['approve_status'], $content);
        $content = str_replace("{over_status}", $data['over_status'], $content);
        return $content;
    }

    /**
     * Notes: 获取维修配件
     *
     * @param $repid int 维修ID
     */
    public function get_repair_parts($repid)
    {
        return $this->DB_get_all('repair_parts', 'partid,parts,part_model,part_num,adduser', ['repid' => $repid]);
    }

    /**
     * Notes: 获取维修单故障问题
     *
     * @param $repid int 维修ID
     */
    public function get_repair_fault($repid)
    {
        return $this->DB_get_all('repair_fault', 'fault_type_id,fault_problem_id', ['repid' => $repid]);
    }

    /**
     * Notes: 获取维修单文件
     *
     * @param $repid int 维修ID
     */
    public function get_repair_file($repid)
    {
        return $this->DB_get_all('repair_file', '*', ['repid' => $repid]);
    }

    /**
     * Notes: 获取维修单大三方公司
     *
     * @param $repid int 维修ID
     */
    public function get_repair_companys($repid)
    {
        return $this->DB_get_all('repair_offer_company', '*', ['repid' => $repid]);
    }

    /**
     * Notes: 获取对应医院信息
     *
     * @param $hosidarr array 医院ID组
     */
    public function getHosScreenInfo($hosidarr)
    {
        //获取所有已报修但未维修完的维修单信息
        $data = $this->getAllNotOverRepairInfo($hosidarr);
        return $data;
    }

    //获取所有已报修但未维修完的维修单信息
    private function getAllNotOverRepairInfo($hosidarr)
    {
        $umodel     = M('user');
        $uinfo      = $umodel->where(['is_delete' => 0])->getField('username,telephone');
        $super      = $umodel->where(['is_delete' => 0, 'is_super' => 1])->getField('username', 1);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $where['B.hospital_id'] = ['in', $hosidarr];
        $where['B.is_delete']   = C('NO_STATUS');
        $where['A.status']      = ['neq', C('REPAIR_ALREADY_ACCEPTED')];//所有未验收的
        $fields                 = 'A.repid,A.repnum,A.status,A.applicant,A.applicant_time,A.applicant_tel,A.breakdown,A.response,A.response_date,
        A.response_tel,A.reponse_remark,A.overhauldate,A.repair_remark,A.engineer,A.engineer_time,A.engineer_tel,A.current_approver,A.overdate,A.editdate,B.assets,B.departid,B.assnum';
        $join                   = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $data                   = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '',
            'A.editdate desc,A.repid desc');
        $result_data            = [];
        foreach ($data as $k => $v) {
            $result_data[$k]['type_name']  = '设备维修';
            $result_data[$k]['assets']     = $v['assets'];
            $result_data[$k]['assnum']     = $v['assnum'];
            $result_data[$k]['department'] = $departname[$v['departid']]['department'];
            $result_data[$k]['remark']     = '';
            $result_data[$k]['status']     = $v['status'];
            switch ($v['status']) {
                case C('REPAIR_HAVE_REPAIRED')://status=1 已报修
                    $result_data[$k]['username'] = $v['applicant'] . '(' . $v['applicant_tel'] . ')';
                    $result_data[$k]['time']     = date('Y-m-d H:i', $v['applicant_time']);
                    $result_data[$k]['remark']   = $v['breakdown'] ? $v['breakdown'] : '';
                    $img                         = '';
                    if ((time() - $v['applicant_time'] <= 3600 * 8)) {
                        $img = " <img src='/Public/images/new_6.gif' style='width: 30px;'/>";
                    }
                    $result_data[$k]['status_name'] = urlencode(C('REPAIR_HAVE_REPAIRED_NAME') . $img);
                    break;
                case C('REPAIR_RECEIPT')://status=2 已接单
                    $result_data[$k]['username'] = $v['response'] . '(' . $v['response_tel'] . ')';
                    $result_data[$k]['time']     = date('Y-m-d H:i', $v['response_date']);
                    $result_data[$k]['remark']   = $v['reponse_remark'] ? $v['reponse_remark'] : '';
                    $img                         = '';
                    if ((time() - $v['response_date'] <= 3600 * 8)) {
                        $img = " <img src='/Public/images/new_6.gif' style='width: 30px;'/>";
                    }
                    $result_data[$k]['status_name'] = urlencode(C('REPAIR_RECEIPT_NAME') . $img);
                    break;
                case C('REPAIR_HAVE_OVERHAULED')://status=3 //已检修/配件待出库
                    $result_data[$k]['username'] = $v['response'] . '(' . $v['response_tel'] . ')';
                    $result_data[$k]['time']     = date('Y-m-d H:i', $v['overhauldate']);
                    $result_data[$k]['remark']   = $v['repair_remark'] ? $v['repair_remark'] : '';
                    $img                         = '';
                    if ((time() - $v['overhauldate'] <= 3600 * 8)) {
                        $img = " <img src='/Public/images/new_6.gif' style='width: 30px;'/>";
                    }
                    $result_data[$k]['status_name'] = urlencode(C('REPAIR_HAVE_OVERHAULED_NAME') . $img);
                    break;
                case C('REPAIR_AUDIT')://status=5 审核中
                    $apper                       = str_replace($super, '', $v['current_approver']);
                    $apper                       = trim($apper, ',');
                    $apptel                      = $uinfo[$apper];
                    $result_data[$k]['username'] = $apper . '(' . $apptel . ')';
                    $result_data[$k]['time']     = date('Y-m-d H:i', $v['editdate']);
                    $result_data[$k]['remark']   = '设备已出库，正在等待审批';
                    $img                         = '';
                    if ((time() - $v['editdate'] <= 3600 * 8)) {
                        $img = " <img src='/Public/images/new_6.gif' style='width: 30px;'/>";
                    }
                    $result_data[$k]['status_name'] = urlencode(C('REPAIR_AUDIT_NAME') . $img);
                    break;
                case C('REPAIR_MAINTENANCE')://status=6 维修中
                    $result_data[$k]['username'] = $v['response'] . '(' . $v['response_tel'] . ')';
                    $result_data[$k]['time']     = date('Y-m-d H:i', $v['overhauldate']);
                    $result_data[$k]['remark']   = $v['repair_remark'] ? $v['repair_remark'] : '';
                    $img                         = '';
                    if ((time() - $v['overhauldate'] <= 3600 * 8)) {
                        $img = " <img src='/Public/images/new_6.gif' style='width: 30px;'/>";
                    }
                    $result_data[$k]['status_name'] = urlencode(C('REPAIR_MAINTENANCE_NAME') . $img);
                    break;
                case C('REPAIR_MAINTENANCE_COMPLETION')://status=7 待验收
                    $result_data[$k]['username'] = $v['engineer'] . '(' . $v['engineer_tel'] . ')';
                    $result_data[$k]['time']     = date('Y-m-d H:i', $v['overdate']);
                    $result_data[$k]['remark']   = '';
                    $img                         = '';
                    if ((time() - $v['overdate'] <= 3600 * 8)) {
                        $img = " <img src='/Public/images/new_6.gif' style='width: 30px;'/>";
                    }
                    $result_data[$k]['status_name'] = urlencode(C('REPAIR_MAINTENANCE_COMPLETION_NAME') . $img);
                    break;
                default:
                    $result_data[$k]['username'] = $v['response'] . '(' . $v['response_tel'] . ')';
                    $result_data[$k]['time']     = date('Y-m-d H:i', $v['overhauldate']);
                    $result_data[$k]['remark']   = $v['repair_remark'] ? $v['repair_remark'] : '';
                    $img                         = '';
                    if ((time() - $v['overhauldate'] <= 3600 * 8)) {
                        $img = " <img src='/Public/images/new_6.gif' style='width: 30px;'/>";
                    }
                    $result_data[$k]['status_name'] = urlencode('未知' . $img);
                    break;
            }
        }
        return $result_data;
    }

    public function addRepairForm()
    {
        $addData  = [];
        $postData = I('post.');
        foreach ($postData as $k => $v) {
            switch ($k) {
                case 'assnum':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '设备编号不能为空'];
                    } else {
                        $addData['assnum'] = $v;
                    }
                    break;
                case 'archives_num':
                    //档案编号
                    $addData['archives_num'] = $v;
                    break;
                case 'repair_category':
                    //项目内 项目外
                    $addData['repair_category'] = $v;
                    break;
                case 'applicant_time':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '报修时间不能为空'];
                    } else {
                        $addData['applicant_time'] = strtotime($v);
                    }
                    break;
                case 'applicant':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '报修人不能为空'];
                    } else {
                        $addData['applicant'] = $v;
                    }
                    break;
                case 'applicant_tel':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '报修人电话不能为空'];
                    } else {
                        $addData['applicant_tel'] = $v;
                    }
                    break;
                case 'breakdown':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '故障描述不能为空'];
                    } else {
                        $addData['breakdown'] = $v;
                    }
                    break;
                case 'applicant_remark':
                    //报修备注
                    $addData['applicant_remark'] = $v;
                    break;
                case 'response':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '接单人不能为空'];
                    } else {
                        $addData['response'] = $v;
                    }
                    break;
                case 'response_tel':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '接单人电话不能为空'];
                    } else {
                        $addData['response_tel'] = $v;
                    }
                    break;
                case 'response_date':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '接单时间不能为空'];
                    } else {
                        $addData['response_date'] = strtotime($v);
                    }
                    break;
                case 'expect_arrive':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '预计到场时间不能为空'];
                    } else {
                        $addData['expect_arrive'] = $v;
                    }
                    break;
                case 'reponse_remark':
//                    接单备注
                    $addData['reponse_remark'] = $v;
                    break;
                case 'is_scene':
                    //是否现场解决
                    $addData['is_scene'] = $v;
                    break;
                case 'repair_type':
                    //维修性质【0自修1厂家2第三方3现场解决】
                    $addData['repair_type'] = $v;
                    break;
                case 'expect_time':
                    //预计修复时间
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '预计修复时间不能为空'];
                    } else {
                        $addData['expect_time'] = strtotime($v);
                    }
                    break;
                case 'repair_remark':
                    //检修备注
                    $addData['repair_remark'] = $v;
                    break;
                case 'engineer':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '维修工程师不能为空'];
                    } else {
                        $addData['engineer'] = $v;
                    }
                    break;
                case 'engineer_tel':
                    //维修工程师联系方式
                    $addData['engineer_tel'] = $v;
                    break;
                case 'assist_engineer':
                    //协助工程师
                    $addData['assist_engineer'] = $v;
                    break;
                case 'assist_engineer_tel':
                    //协助工程师联系方式
                    $addData['assist_engineer_tel'] = $v;
                    break;
                case 'other_price':
                    //其他费用
                    $addData['other_price'] = $v;
                    break;
                case 'actual_price':
                    //总维修费用
                    $addData['actual_price'] = $v;
                    break;
                case 'engineer_time':
                    //维修开始时间
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '维修开始日期不能为空'];
                    } else {
                        $addData['engineer_time'] = strtotime($v);
                    }
                    break;
                case 'overhauldate':
                    //检修时间
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '检修时间不能为空'];
                    } else {
                        $addData['overhauldate'] = strtotime($v);
                    }
                    break;
                case 'overdate':
                    //维修结束
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '维修结束日期不能为空'];
                    } else {
                        $addData['overdate'] = strtotime($v);
                    }
                    break;
//                case 'working_hours':
//                    //维修工时
//                    if (empty(trim($v))) {
//                        return ['status' => -1, 'msg' => '维修工时不能为空'];
//                    } else {
//                        $addData['working_hours'] = $v;
//                    }
//                    break;
                case 'checkperson':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '验收人不能为空'];
                    } else {
                        $addData['checkperson'] = $v;
                    }
                    break;
                case 'checkdate':
                    if (empty(trim($v))) {
                        return ['status' => -1, 'msg' => '验收时间不能为空'];
                    } else {
                        $addData['checkdate'] = strtotime($v);
                    }
                    break;
                case 'check_remark':
                    //验收备注
                    $addData['check_remark'] = $v;
                    break;
            }
        }
        //先判断该设备是否存在于系统
        $assInfo = $this->DB_get_one('assets_info', '', ['assnum' => $addData['assnum']]);
        if (empty($assInfo)) {
            return ['status' => -1, 'msg' => '系统查不到该台设备编号所对应的设备信息'];
        }
        //筛选现场解决数据还是非现场数据
        if ($addData['is_scene'] == 0) {
            //非现场解决
            switch ($addData['repair_type']) {
                case 0:
                    //自修
                    //配件
                    $addParts['partsData'] = I('post.partsData');
                    break;
                case 1:
                    //维保厂家
                    $addData['guarantee_id']   = I('post.guarantee_id');
                    $addData['guarantee_name'] = I('post.guarantee_name');
                    $addData['salesman_name']  = I('post.salesman_name');
                    $addData['salesman_phone'] = I('post.salesman_phone');
                    break;
                case 2:
                    //第三方
                    $addCompany['offer_company_id'] = I('post.offer_company_id');
                    $addCompany['offer_company']    = I('post.offer_company');
                    $addCompany['offer_contacts']   = I('post.offer_contacts');
                    $addCompany['telphone']         = I('post.telphone');
                    $addCompany['total_price']      = I('post.total_price');
                    $addCompany['invoice']          = I('post.invoice');
                    $addCompany['cycle']            = I('post.cycle');
                    $addCompany['decision_user']    = I('post.decision_user');
                    $addCompany['decision_adddate'] = I('post.decision_adddate');
                    $addCompany['decision_reasion'] = I('post.decision_reasion');
                    $addCompany['remark']           = I('post.company_remark');
                    break;
                default:
                    return ['status' => -1, 'msg' => '非法参数'];
                    break;
            }
        } elseif ($addData['is_scene'] == 1) {
            //现场解决分支
            //处理详情
            if (empty(trim(I('post.dispose_detail')))) {
                return ['status' => -1, 'msg' => '处理详情不能为空'];
            } else {
                $addData['dispose_detail'] = I('post.dispose_detail');
            }
            //配件
            $addParts['partsData'] = I('post.partsData');
        } else {
            return ['status' => -1, 'msg' => '非法参数'];
        }
        //缓存信息补充设备信息
        $departname  = [];
        $baseSetting = [];
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        //查询对应设备编码的设备信息
        $departID        = $assInfo['departid'];
        $department_name = $departname[$assInfo['departid']]['department'];
        $wx              = $baseSetting['repair']['repair_encoding_rules']['value'];
        if ($wx['prefix']) {
            $addData['repnum'] = $wx['prefix'] . $addData['repnum'];
        }
        //暂时未需要配置默认下划线 如果需要配置 这里读配置 todo
        $cut = '_';
        if ($cut) {
            $addData['repnum'] .= $cut;
        }
        $addData['repnum']              = $this->getOrgNumber('repair', 'repnum', $wx['prefix'], $cut,
            strtotime($addData['applicant_time']));
        $addData['assid']               = $assInfo['assid'];
        $addData['assets']              = $assInfo['assets'];
        $addData['model']               = $assInfo['model'];
        $addData['departid']            = $departID;
        $addData['department']          = $department_name;
        $addData['status']              = 8;
        $addData['working_hours']       = timediff($addData['overdate'], $addData['applicant_time']);
        $addData['over_status']         = 1;
        $addData['service_attitude']    = 0;
        $addData['technical_level']     = 0;
        $addData['response_efficiency'] = 0;
        $addData['adddate']             = time();
        //开启事务
        $this->startTrans();
        $insertId = $this->add($addData);
        if ($insertId) {
            //故障问题入库
            if (empty(trim(I('post.problem')))) {
                //回滚
                $this->rollback();
                return ['status' => -1, 'msg' => '处理详情不能为空'];
            } else {
                //故障问题入库处理
                $postProblem = explode(',', I('post.problem'));
                foreach ($postProblem as $k => $v) {
                    $addType[$k]['repid']            = $insertId;
                    $addType[$k]['fault_type_id']    = explode('-', $v)[0];
                    $addType[$k]['fault_problem_id'] = explode('-', $v)[1];
                }
                $addProblemResult = $this->insertDataALL('repair_fault', $addType);
                if (!$addProblemResult) {
                    $this->rollback();
                    return ['status' => -1, 'msg' => '故障问题录入失败！'];
                }
            }
            //配件
            if (!empty($addParts)) {
                $parr = [];
                foreach ($addParts['partsData'] as $k => $v) {
                    if ($v) {
                        $parr[$k]['repid']      = $insertId;
                        $parr[$k]['parts']      = $v['parts'];
                        $parr[$k]['part_model'] = $v['part_model'];
                        $parr[$k]['part_num']   = $v['num'];
                        $parr[$k]['part_price'] = $v['part_price'];
                        $parr[$k]['price_sum']  = ($v['num'] * $v['part_price']);
                        $parr[$k]['status']     = 1;
                        $parr[$k]['adduser']    = $v['username'];
                        $parr[$k]['adddate']    = time();
                    }
                }
                $addPartResult = $this->insertDataALL('repair_parts', $parr);
                if (!$addPartResult) {
                    $this->rollback();
                    return ['status' => -1, 'msg' => '配件录入失败！'];
                }
            }
            //第三方维修公司
            if (!empty($addCompany)) {
                $addCompany['repid']          = $insertId;
                $addCompany['last_decisioin'] = 1;
                $addCompany[$k]['adduser']    = session('username');
                $addCompany[$k]['adddate']    = time();
                $addCompanyResult             = $this->insertData('repair_offer_company', $addCompany);
                if (!$addCompanyResult) {
                    $this->rollback();
                    return ['status' => -1, 'msg' => '第三方维修公司录入失败！'];
                }
            }
            //维修跟进信息
            $followData = I('post.followData');
            if (!empty($followData)) {
                foreach ($followData as $k => $v) {
                    $addFollowData[$k]['repid']      = $insertId;
                    $addFollowData[$k]['followdate'] = strtotime($v['follow_date']);
                    $addFollowData[$k]['nextdate']   = strtotime($v['next_date']);
                    $addFollowData[$k]['detail']     = $v['follow_detail'];
                }
                $addfollowResult = $this->insertDataALL('repair_follow', $addFollowData);
                if (!$addfollowResult) {
                    ;
                    $this->rollback();
                    return ['status' => -1, 'msg' => '维修跟进信息录入失败！'];
                }
            }
            $this->commit();
            return ['status' => 1, 'msg' => '录入成功'];
        }
    }

    //获取自修单数量
    public function getSelfStudyNum($hospital_id, $start, $end)
    {
        $where['A.repair_type']    = ['EQ', 0];
        $where['A.applicant_time'] = [['egt', $start], ['elt', $end], 'and'];
        $where['B.hospital_id']    = $hospital_id;
        $where['B.is_delete']      = 0;
        $join                      = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $total                     = $this->DB_get_count_join('repair', 'A', $join, $where);
        return $total;
    }

    //获取本月维修中设备数量
    public function getRepairAssets($hospital_id, $start, $end)
    {
        $where['A.status']        = 2;
        $where['A.response_date'] = array(array('egt', $start), array('elt', $end), 'and');
        $where['B.hospital_id']   = $hospital_id;
        $where['B.is_delete']     = 0;
        $join                     = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $total                    = $this->DB_get_count_join('repair', 'A', $join, $where);
        return $total;
    }


}
