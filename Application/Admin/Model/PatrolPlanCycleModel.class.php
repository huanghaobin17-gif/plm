<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;
use Think\Model;

class PatrolPlanCycleModel extends CommonModel
{
    protected $tableName = 'patrol_plan_cycle';


    public function releaseAndEidt()
    {

        $patrid = I('POST.patrid');
        $patrol = $this->getAllCyclePlans($patrid);
        if (!$patrid) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $patrol = $this->formatCyclePlanData($patrol);
        $result["code"] = 200;
        $result["rows"] = $patrol;
        return $result;
    }

    /*
     * 修改周期
     * @params1 $cycid int 周期 ID
     * @params2 $executeStatus int 实施状态
     * @params3 $result array 明细项数据
     * @params4 $asset_status int 设备状态
     * @params5 $assetnum string 设备编号
     */
    public function updateInExecution($cycid, $executeStatus, $result, $asset_status, $assetnum,$finish_time)
    {
        $fields = 'patrid,plan_assnum,cycle_status,implement_sum,abnormal_sum,repair_assnum,scrap_assnum,abnormal_assnum,not_operation_assnum,cycle_end_date';
        $data = $this->DB_get_one('patrol_plans_cycle', $fields, "cycid=$cycid");
        if ($executeStatus == C('MAINTAIN_COMPLETE')) {
            $abnormal_sum = 0;//异常设备数量
            $data['implement_sum']++;//已执行巡查设备数量
            if ($asset_status == C('ASSETS_STATUS_IN_MAINTENANCE')) {
                // $asset_status == 5 -- 该设备正在维修 将设备加入正在维修设备组中
                $abnormal_sum = 1;
                if ($data['repair_assnum']) {
                    $data['repair_assnum'] = json_decode($data['repair_assnum']);
                } else {
                    $data['repair_assnum'] = array();
                }
                array_push($data['repair_assnum'], $assetnum);
                $data['repair_assnum'] = json_encode($data['repair_assnum']);
            } elseif ($asset_status == C('ASSETS_STATUS_SCRAPPED')) {
                //$asset_status == 6 -- 该设备已报废 将设备加入已报废设备组中
                $abnormal_sum = 1;
                if ($data['scrap_assnum']) {
                    $data['scrap_assnum'] = json_decode($data['scrap_assnum']);
                } else {
                    $data['scrap_assnum'] = array();
                }
                array_push($data['scrap_assnum'], $assetnum);
                $data['scrap_assnum'] = json_encode($data['scrap_assnum']);
            } elseif ($asset_status == C('ASSETS_STATUS_NOT_OPERATION')) {
                // $asset_status == 7 -- 该设备不做保养 将设备加入不进行维修设备组中
                if ($data['not_operation_assnum'] != null) {
                    $data['not_operation_assnum'] = json_decode($data['not_operation_assnum']);
                } else {
                    $data['not_operation_assnum'] = array();
                }
                array_push($data['not_operation_assnum'], $assetnum);
                $data['not_operation_assnum'] = json_encode($data['not_operation_assnum']);
            } else {
                foreach ($result as &$one) {
                    if ($one != '合格') {
                        $abnormal_sum = 1;
                        break;
                    }
                }
            }
            if ($abnormal_sum == 1) {
                if ($data['abnormal_assnum']) {
                    $data['abnormal_assnum'] = json_decode($data['abnormal_assnum']);
                } else {
                    $data['abnormal_assnum'] = array();
                }
                array_push($data['abnormal_assnum'], $assetnum);
                $data['abnormal_assnum'] = json_encode($data['abnormal_assnum']);
                $data['abnormal_sum'] += $abnormal_sum;
            }
            if ($data['implement_sum'] == count(json_decode($data['plan_assnum']))) {
                //保养完成，判断是否逾期
                if (date('Y-m-d',strtotime($finish_time)) > $data['cycle_end_date']) {
                    $data['cycle_status'] = 3;//逾期完成
                } else {
                    $data['cycle_status'] = 2;//按期完成
                }
                $data['complete_time'] = $finish_time;//已完成
            } else {
                //未保养完成
                if (date('Y-m-d',strtotime($finish_time)) > $data['cycle_end_date']) {
                    $data['cycle_status'] = 4;//逾期未完成
                } else {
                    $data['cycle_status'] = 1;//执行中
                }
            }
        }
        $this->updateData('patrol_plans_cycle', $data, array('cycid' => $cycid));
        if ($executeStatus == C('MAINTAIN_COMPLETE')) {
            if (in_array($data['cycle_status'], [2, 3])) {
                //计划完成，生成计划编码
                $planInfo = $this->DB_get_one('patrol_plans', 'patrol_level', ['patrid' => $data['patrid']]);
                $this->create_patrol_num($planInfo['patrol_level'], $cycid);
                //计划完成，发送验收提醒
                $this->send_check_patrol_message($cycid);
            }
        }
    }

    /**
     * 生成计划编码
     * @param $patrol_level
     * @param $cycid
     */
    public function create_patrol_num($patrol_level, $cycid)
    {
        $ymd = date('Ymd');
        if ($patrol_level == '1') {
            $num_data = $this->DB_get_one('patrol_plans_cycle', "max(patrol_num) as patrol_num", array('patrol_num' => array('like', 'DC%')));
            if (!$num_data) {
                $patrol_num = 'DC' . $ymd . '-1';
            } else {
                $num = substr($num_data['patrol_num'], strpos($num_data['patrol_num'], "-") + 1) + 1;
                $patrol_num = 'DC' . $ymd . '-' . $num;
            }
            $this->updateData('patrol_plans_cycle', ['patrol_num' => $patrol_num], array('cycid' => $cycid));
        } elseif ($patrol_level == '2') {
            $num_data = $this->DB_get_one('patrol_plans_cycle', "max(patrol_num) as patrol_num", array('patrol_num' => array('like', 'RC%')));
            if (!$num_data) {
                $patrol_num = 'RC' . $ymd . '-1';
            } else {
                $num = substr($num_data['patrol_num'], strpos($num_data['patrol_num'], "-") + 1) + 1;
                $patrol_num = 'RC' . $ymd . '-' . $num;
            }
            $this->updateData('patrol_plans_cycle', ['patrol_num' => $patrol_num], array('cycid' => $cycid));
        }
    }

    /**
     * 保养计划完成，发送验收提醒
     * @param $cycid
     */
    public function send_check_patrol_message($cycid)
    {
        //==========================================短信 START==========================================
        $settingData = $this->checkSmsIsOpen('Patrol');
        $ToolMod = new ToolController();
        $moduleModel = new ModuleModel();
        $wx_status = $moduleModel->decide_wx_login();
        $patrolInfo = $this->DB_get_one_join('patrol_plans_cycle', 'A', 'B.patrid,B.patrol_name,A.patrol_num,A.cycle_start_date,A.cycle_end_date', 'LEFT JOIN sb_patrol_plans AS B ON B.patrid=A.patrid', array('A.cycid' => $cycid));
        if ($settingData['checkPatrolTask']['status'] == C('OPEN_STATUS')) {
//            $patrolnum = $patrolInfo['patrol_num'];
//            $sms_data['cyclenum'] = $patrolInfo['patrol_name'];
//            $sms = PatrolModel::formatSmsContent($settingData['checkPatrolTask']['content'], $sms_data);
//            $depart_data = $this->DB_get_one('assets_info', 'GROUP_CONCAT(departid) as departid', array('assnum' => array('IN', $data['patrol_assnums'])));
//            $menu_id = $this->menuid('examine', 'Patrol', 'Patrol');
//            $role_data = $this->DB_get_one_join('role_menu', 'A', 'GROUP_CONCAT(A.roleid) as roleid', 'LEFT JOIN sb_role AS B ON B.roleid=A.roleid', array('A.menuid' => $menu_id, 'B.hospital_id' => session('current_hospitalid'), 'B.is_delete' => '0'));
//            $join[0] = 'JOIN sb_user_role AS B ON B.userid=A.userid';
//            $join[1] = 'JOIN sb_user_department AS C ON C.userid=A.userid';
//            $data = $this->DB_get_all_join('user', 'A', 'A.,A.openid', $join, array('B.roleid' => array('IN', $role_data['roleid']), 'C.departid' => array('IN', $depart_data['departid'])), 'A.telephone', '', '');
//            if ($settingData['checkPatrolTask']['status'] == C('OPEN_STATUS') && $data) {
//                $phone = $this->formatPhone($data);
//                $ToolMod->sendingSMS($phone, $sms, $this->Controller, $res);
//            }
        }
        //==========================================短信 END============================================
        //获取该计划设备包括的所有科室的护士
        $join = ' LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $as_depart = $this->DB_get_all_join('patrol_plans_assets', 'A', 'distinct(B.departid)', $join, ['A.patrid' => $patrolInfo['patrid']]);
        $depart_ids = [];
        foreach ($as_depart as $v) {
            $depart_ids[] = $v['departid'];
        }
        $check_users = [];
        foreach ($depart_ids as $v) {
            $users = $this->getToUser(session('userid'), $v, 'Patrol', 'Patrol', 'examine');
            foreach ($users as $uv) {
                $check_users[] = $uv;
            }
        }
        if (C('USE_FEISHU') === 1) {
            //==========================================飞书 START========================================
            //要显示的字段区域
            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**计划名称：**' . $patrolInfo['patrol_name'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**计划编号：**' . $patrolInfo['patrol_num'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '请及时进行验收';
            $feishu_fields[] = $fd;

            $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
            $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
            $card_data['elements'][0]['tag'] = 'div';
            $card_data['elements'][0]['fields'] = $feishu_fields;
            $card_data['header']['template'] = 'blue';
            $card_data['header']['title']['content'] = '巡查保养计划验收提醒';
            $card_data['header']['title']['tag'] = 'plain_text';

            $openid = [];
            foreach ($check_users as $key => $value) {
                if (!in_array($value['openid'], $openid)) {
                    $openid[] = $value['openid'];
                    $this->send_feishu_card_msg($value['openid'], $card_data);
                }
            }
            //==========================================飞书 END==========================================
        } else {
            if ($wx_status && $check_users) {
                $openIds = array_column($check_users, 'openid');
                $openIds = array_filter($openIds);
                $openIds = array_unique($openIds);

                $messageData = [
                    'thing8'  => $patrolInfo['patrol_name'],// 巡检名称
                    'time9'   => $patrolInfo['cycle_start_date'],// 开始时间
                    'time10'  => $patrolInfo['cycle_end_date'],// 结束时间
                    'const14' => '已完成，请验收',// 巡检状态
                ];

                foreach ($openIds as $openId) {
                    Weixin::instance()->sendMessage($openId, '设备巡检工单处理通知', $messageData);
                }
            }
        }
    }

    /*
     * 获取所有周期计划
     * @params $patrid int 周期ID
     * return array
     */
    public function getAllCyclePlans($patrid)
    {
        $fields = "patrid,`patrol_level`,`is_release`,`release_time`,group_concat(plan_assnum) as plan_assnum,group_concat(executor) as executor,group_concat(scrap_assnum) as scrap_assnum,sum(abnormal_sum) as abnormal_sum,
                   sum(implement_sum) as implement_sum";
        //获取当前数据
        $patrol = $this->DB_get_all('patrol_plan_cycle', $fields, array('patrid' => $patrid), '', 'patrid asc');
        return $patrol;
    }

    /*
     * 格式化周期数据
     * @params1 $patrol array 要格式化的数据
     * return array
     */
    public function formatCyclePlanData($patrol)
    {
        //查询当前用户是否有权限进行发布
        $Release = get_menu('Patrol', 'Patrol', 'releasePatrol');
        //查询当前用户是否查看
        $addPrivi = get_menu('Patrol', 'Patrol', 'addPatrol');
        $html = '';
        foreach ($patrol as $k => $v) {
            $patrol[$k]['date'] = $v['startdate'] . ' 至 ' . $v['overdate'];
            $patrol[$k]['period_name'] = '第 ' . $v['period'] . ' 期';
            $v['plan_assnum'] = str_replace('[', '', $v['plan_assnum']);
            $v['plan_assnum'] = str_replace(']', '', $v['plan_assnum']);
            $v['plan_assnum'] = explode(',', $v['plan_assnum']);
            $v['scrap_assnum'] = str_replace('[', '', $v['scrap_assnum']);
            $v['scrap_assnum'] = str_replace(']', '', $v['scrap_assnum']);
            if (!$v['scrap_assnum']) {
                $scrapNum = 0;
            } else {
                $v['scrap_assnum'] = explode(',', $v['scrap_assnum']);
                $scrapNum = count($v['scrap_assnum']);
            }
            $totalNum = count($v['plan_assnum']);
            $patrol[$k]['implementation'] = $v['implement_sum'] . ' / ' . ($totalNum - $scrapNum) . ' / ' . $totalNum;
            if ($v['release_time'] == 0) {
                $patrol[$k]['release_time'] = '-';
            } else {
                $patrol[$k]['release_time'] = getHandleTime($v['release_time']);
            }
            //查询各周期完成状态
            $status = $this->DB_get_one('patrol_plan_cycle', 'min(status) as minStatus,max(status) as maxStatus', array('patrid' => $v['patrid'], 'patrol_level' => $v['patrol_level'], 'period' => $v['period']));
            $patrol[$k]['minStatus'] = $status['minStatus'];
            $patrol[$k]['maxStatus'] = $status['maxStatus'];
            if ($Release) {
                //前一周期未发布、已完成的或已逾期的，可以发布新的周期，其他情况不允许发布新周期计划
                if ($k == 0) {
                    if ($v['is_release'] == 0) {
                        //未发布的可以发布
                        $patrol[$k]['operation'] = $this->returnListLink($Release['actionname'], $Release['actionurl'], 'Release', C('BTN_CURRENCY') . ' Release', '', 'data-id="' . $v['patrid'] . '" data-period="' . $v['period'] . '"');
                    } else {
                        if ($addPrivi) {
                            $url = $addPrivi['actionurl'] . '?action=doTask&cyclenum=' . $v['cyclenum'];
                            $class = C('BTN_CURRENCY');
                            $layEvent = 'showDetail';
                        } else {
                            $url = '';
                            $class = C('BTN_CURRENCY') . ' layui-btn-danger';
                            $layEvent = '';
                        }
                        if ($patrol[$k]['maxStatus'] == 0) {
                            $name = '待执行';
                        } elseif ($patrol[$k]['maxStatus'] == 1) {
                            $name = '执行中';
                        } elseif ($patrol[$k]['maxStatus'] == 2 && $patrol[$k]['minStatus'] < 2) {
                            $name = '执行中';
                        } elseif ($patrol[$k]['minStatus'] == 2) {
                            $name = '已完成';
                        } elseif ($patrol[$k]['minStatus'] == 3) {
                            $class = C('BTN_CURRENCY') . ' layui-btn-warm';
                            $name = '已验收';
                        } else {
                            $name = '待执行';
                        }
                        $patrol[$k]['operation'] = $this->returnListLink($name, $url, $layEvent, $class);
                    }
                } else {
                    if ($patrol[$k - 1]['is_release'] == 1 && $patrol[$k - 1]['minStatus'] >= 2) {
                        //上一期已发布且状态为已完成或已结束
                        if ($v['is_release'] == 0) {
                            //本期未发布，则状态为可发布
                            $patrol[$k]['operation'] = $this->returnListLink($Release['actionname'], $Release['actionurl'], 'Release', C('BTN_CURRENCY') . ' Release', '', 'data-id="' . $v['patrid'] . '" data-period="' . $v['period'] . '"');
                        } else {
                            //本期已发布，则状态为已发布
                            $tips = $patrol[$k]['minStatus'] == 0 ? '待执行' : (($patrol[$k]['minStatus'] == 1 || $patrol[$k]['maxStatus'] == 1) ? '执行中' : ($patrol[$k]['minStatus'] == 2 ? '已完成' : ($patrol[$k]['minStatus'] == 3 ? '已验收' : '已结束')));
                            $url = $addPrivi['actionurl'] . '?action=doTask&cyclenum=' . $v['cyclenum'];
                            $class = C('BTN_CURRENCY') . ' layui-btn-danger';
                            $layEvent = 'showDetail';
                            $patrol[$k]['operation'] = $this->returnListLink($tips, $url, $layEvent, $class);
                        }
                    } else {
                        //上一期未发布或未完成，后面的均不能发布
                        $patrol[$k]['operation'] = $this->returnListLink($Release['actionname'], '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                    }
                }
            }
        }
        return $patrol;
    }

    /*
     * 根据设备包ID获取该设备包下面面的所有计划ID
     * @params1 $packid int 设备包ID
     * return array
     */
    public function getAllPatrIdByPackId($packid)
    {
        $fields = "A.*,sb_patrol_plan.patrid,sb_patrol_plan.patrol_level";
        $join[0] = " LEFT JOIN __PATROL_PLAN__ ON A.packid = sb_patrol_plan.packid";
        $plans = $this->DB_get_all_join('patrol_plan_assets_pack', 'A', $fields, $join, array('A.packid' => $packid), '', '', '');
        return $plans;
    }

    /*
     * 格式化成单元格合并表格数据
     * @params1 $data array 要格式化的数据
     * return array
     */
    public function formatToBeTableData($data)
    {
        $tmp = array();
        foreach ($data as $k => $v) {
            $data[$k]['hasRowspan'] = true;
            if (in_array($v['executor'], $tmp)) {
                $data[$k]['hasRowspan'] = false;
            } else {
                array_push($tmp, $v['executor']);
            }
        }
        foreach ($data as $k => $v) {
            if ($data[$k]['hasRowspan']) {
                $i = 0;
                foreach ($data as $k1 => $v1) {
                    if ($v1['executor'] == $v['executor']) {
                        $i += 1;
                    }
                }
                $data[$k]['rowspan'] = $i;
            }
        }
        return $data;
    }

    /**
     * Notes: 获取所有符合统计时间段内的计划
     * @param $startDate string 开始统计时间
     * @param $endDate string 结束统计时间
     * @return mixed
     */
    public function getAllPlanByDate($startDate, $endDate)
    {
        $where['complete_time'] = array(array('egt', $startDate), array('elt', $endDate), 'and');
        return $this->DB_get_all('patrol_plan_cycle', 'cycid,patrid,plan_assnum,abnormal_assnum,complete_time', $where);
    }

    public function handlePlanData($lists)
    {
        $res = [];
        $departments = [];
        $res['lists'] = $lists;
        //获取说明标签、处理数据
        foreach ($lists as $key => $v) {
            $departments[] = $v['department'] . '';
            $res['totalNum'][] = (int)$v['totalNum'];
            $res['planNum'][] = (int)$v['planNum'];
            $res['notPlanNum'][] = -(int)$v['notPlanNum'];
            $res['abnormalNum'][] = -(int)$v['abnormalNum'];
        }
        $res['departments'] = $departments;
        //要显示的数据
        $showName = array('planNum' => '保养设备数', 'abnormalNum' => '异常设备数', 'totalNum' => '设备总台数', 'notPlanNum' => '未保养设备数', 'planRate' => '保养覆盖率', 'abnormalRate' => '异常率');
        //要显示的数据（柱状）
        $barData = array('totalNum', 'planNum', 'notPlanNum', 'abnormalNum');
        //要显示的数据（折线）
        $lineData = array();
        //要显示的数据（单独Y轴标识）
        $bar2Data = array();
        //默认不显示数据
        $selected = array();
        $res['series'] = [];
        $i = 0;
        foreach ($showName as $key => $val) {
            if (in_array($key, $barData)) {
                $res['legend'][$i] = $val;
            }
            if (in_array($val, $selected)) {
                $res['selected'][$val] = false;
            }
            if (in_array($key, $barData)) {
                $res['series'][$i]['name'] = $val;
                $res['series'][$i]['type'] = 'bar';
                if (in_array($key, array('planNum', 'abnormalNum'))) {
                    $res['series'][$i]['stack'] = '总量';
                } else {
                    $res['series'][$i]['stack'] = '总量1';
                    //$res['series'][$i]['label']['normal']['position'] = 'left';
                }
                $res['series'][$i]['label']['normal']['show'] = true;
                $res['series'][$i]['data'] = $res[$key];
                $i++;
            }
        }
        return $res;
    }

    /**
     * Notes:获取报表搜索条件范围
     * @param $startDate string 时间始
     * @param $endDate string 时间末
     * @return string
     */
    public function getReportTips($startDate, $endDate)
    {
        $tips = '';
        //报表日期返回
        if ($startDate == '') {
            $tips = '报表日期：' . '2015-01-01' . ' 至 ' . $endDate;
        } else {
            $tips = '报表日期：' . $startDate . ' 至 ' . $endDate;
        }
        return $tips;
    }

    /**
     * Notes: 获取部门设备列表
     * @return mixed
     */
    public function getAllDepartmentAssets()
    {
        $departid = I('POST.id');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $total = $this->DB_get_count('assets_info', array('departid' => $departid));
        $lists = $this->DB_get_all('assets_info', 'assid,catid,departid,assets,assnum,assorignum,model', array('departid' => $departid), '', $sort . ' ' . $order, $offset . ',' . $limit);
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        foreach ($lists as &$val) {
            $val['category'] = $catname[$val['catid']]['category'];
        }
        $result['limit'] = $limit;
        $result['offset'] = $offset;
        $result['total'] = $total;
        $result['rows'] = $lists;
        $result['code'] = 200;
        return $result;
    }

    /**
     * Notes: 获取各设备计划次数
     * @param $lists array 设备列表
     * @return array
     */
    public function getPlansNum($lists)
    {
        if (!$lists) {
            return array();
        }
//        var_dump($lists);exit;
        $startDate = I('POST.startDate') . ' 00:00:01';
        $endDate = I('POST.endDate') . ' 23:59:59';
        $where['complete_time'] = array(array('egt', $startDate), array('elt', $endDate), 'and');
        $res = $this->DB_get_all('patrol_plan_cycle', 'cycid,patrid,patrol_level,group_concat(distinct plan_assnum) as plan_assnum,abnormal_assnum,complete_time', $where, 'patrol_level,patrid');
        foreach ($res as &$v) {
            $v['plan_assnum'] = str_replace('["', '', $v['plan_assnum']);
            $v['plan_assnum'] = str_replace('"]', '', $v['plan_assnum']);
            $v['plan_assnum'] = str_replace('",', ',', $v['plan_assnum']);
            $v['plan_assnum'] = str_replace(',"', ',', $v['plan_assnum']);
            $v['plan_assnum'] = explode(',', $v['plan_assnum']);
        }
        //var_dump($lists);exit;
        foreach ($lists as &$v) {
            $v['pmNum'] = 0;
            $v['xcNum'] = 0;
            $v['rcNum'] = 0;
            $v['abNum'] = 0;
            $v['abTermNum'] = 0;
        }
        //var_dump($res);exit;
        foreach ($lists as &$v) {
            $v['cid'] = '';
            foreach ($res as &$v1) {
                if ($v1['patrol_level'] == C('PATROL_LEVEL_PM')) {
                    if (in_array($v['assnum'], $v1['plan_assnum'])) {
                        $v['pmNum'] += 1;
                        $v['cid'] .= $v1['cycid'] . ',';
                    }
                } elseif ($v1['patrol_level'] == C('PATROL_LEVEL_RC')) {
                    if (in_array($v['assnum'], $v1['plan_assnum'])) {
                        $v['xcNum'] += 1;
                        $v['cid'] .= $v1['cycid'] . ',';
                    }
                } elseif ($v1['patrol_level'] == C('PATROL_LEVEL_DC')) {
                    if (in_array($v['assnum'], $v1['plan_assnum'])) {
                        $v['rcNum'] += 1;
                        $v['cid'] .= $v1['cycid'] . ',';
                    }
                }
            }
            $v['cid'] = trim($v['cid'], ',');
        }
//        var_dump($lists);exit;
        return $lists;
    }

    /**
     * Notes: 获取各设备异常出现次数
     * @param $lists array 设备列表
     * return array
     */
    public function getAbnormalNum($lists)
    {
        if (!$lists) {
            return array();
        }
        $startDate = I('POST.startDate') . ' 00:00:01';
        $endDate = I('POST.endDate') . ' 23:59:59';
        $where['complete_time'] = array(array('egt', $startDate), array('elt', $endDate), 'and');
        $res = $this->DB_get_all('patrol_plan_cycle', 'cycid,patrid,patrol_level,plan_assnum,abnormal_assnum,complete_time', $where);
        $cycids = array();
        $assnums = array();
        foreach ($lists as &$v) {
            foreach ($res as &$v1) {
                $abassnum = json_decode($v1['abnormal_assnum']);
                if (in_array($v['assnum'], $abassnum)) {
                    $v['abNum'] += 1;
                    $cycids[] = $v1['cycid'];
                    $assnums[] = $v['assnum'];
                }
            }
        }
        if ($cycids && $assnums) {
            $cycids = array_unique($cycids);
            $assnums = array_unique($assnums);
            $join[0] = " LEFT JOIN __PATROL_EXECUTE_ABNORMAL__ ON A.execid = __PATROL_EXECUTE_ABNORMAL__.execid";
            $execWhere['A.cycid'] = array('in', $cycids);
            $execWhere['A.assetnum'] = array('in', $assnums);
            $execWhere['sb_patrol_execute_abnormal.result'] = array('neq', '合格');
            $abDetails = $this->DB_get_all_join('patrol_execute', 'A', 'group_concat(distinct A.cycid) as cycids,A.assetnum,count(*) as num', $join, $execWhere, 'A.assetnum', '', '');
            //查询异常项总数
            foreach ($lists as &$v) {
                foreach ($abDetails as &$v1) {
                    if ($v['assnum'] == $v1['assetnum']) {
                        $v['abTermNum'] = (int)$v1['num'];
                        $v['cycids'] = $v1['cycids'];
                    }
                }
            }
        }
        return $lists;
    }

    /**
     * Notes: 根据周期ID组，获取计划明细
     * @param $cid array 周期ID组
     */
    public function getPlanDetail($cid)
    {
        $fields = "sb_patrol_plan.patrid,sb_patrol_plan.patrolname,sb_patrol_plan.patrol_level,sb_patrol_plan.patrolnum,A.cycid,A.abnormal_assnum,A.executor,A.complete_time";
        $join[0] = " LEFT JOIN __PATROL_PLAN__ ON A.patrid = __PATROL_PLAN__.patrid";
        return $this->DB_get_all_join('patrol_plan_cycle', 'A', $fields, $join, array('A.cycid' => array('in', $cid)), '', 'cycid asc', '');
    }

    /**
     * Notes: 根据assnum，cid获取设备异常明细
     * @param $cid array 周期ID组
     */
    public function getAbnormalDetail($assnum, $cid)
    {
        $fields = "A.execid,A.cycid,A.assetnum,sb_patrol_execute_abnormal.ppid,sb_patrol_execute_abnormal.result,sb_patrol_execute_abnormal.abnormal_remark";
        $join[0] = " LEFT JOIN __PATROL_EXECUTE_ABNORMAL__ ON A.execid = __PATROL_EXECUTE_ABNORMAL__.execid";
        $where['A.cycid'] = array('in', $cid);
        $where['A.assetnum'] = $assnum;
        $where['sb_patrol_execute_abnormal.result'] = array('neq', '合格');
        return $this->DB_get_all_join('patrol_execute', 'A', $fields, $join, $where, '', 'execid asc', '');
    }
}
