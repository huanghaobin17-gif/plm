<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;
use Think\Model;

class PatrolPlanModel extends CommonModel
{
    private $MODULE = 'Patrol';
    private $Controller = 'Patrol';
    private $CATE = 'Patrol';
    protected $tablePrefix = 'sb_';
    protected $tableName = 'patrol_plan';

    public function get_plan_assets()
    {
        $limit = I('POST.limit') ? I('POST.limit') : 10;
        $page = I('POST.page') ? I('POST.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'ASC';
        $sort = I('POST.sort') ? I('POST.sort') : 'assid';
        $arr_assnum = I('post.assnum');
        $arr_assnum = trim($arr_assnum, ',');

        //查询设备信息
        $fields = "A.assid,A.assets,A.assnum,A.model,A.departid,A.catid,A.buy_price,A.is_firstaid,A.is_special,A.assorignum,A.status,A.patrol_xc_cycle,A.patrol_pm_cycle,A.pre_patrol_date,A.pre_maintain_date,
        A.is_metering,A.is_qualityAssets,A.guarantee_date";
        $join[0] = " LEFT JOIN __ASSETS_CONTRACT__ ON A.acid = sb_assets_contract.acid";
        $total = $this->DB_get_count_join('assets_info', 'A', $join, array('A.assnum' => array('IN', $arr_assnum)));
        $assInfo = $this->DB_get_all_join('assets_info', 'A', $fields, $join, array('A.assnum' => array('IN', $arr_assnum)));
        $departname = [];
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');

        $assidArr = array();
        $departid = '';
        foreach ($assInfo as &$one) {
            $assidArr[] = $one['assid'];
            if (!$showPrice) {
                $one['buy_price'] = '***';
            }
            $one['guarantee_date'] = HandleEmptyNull($one['guarantee_date']);
            $one['department_name'] = $departname[$one['departid']]['department'];
            $one['cat_name'] = $catname[$one['catid']]['category'];
            if ($one['is_firstaid'] == C('ASSETS_FIRST_CODE_YES')) {
                $one['type_name'] = C('ASSETS_FIRST_CODE_YES_NAME');
            }
            if ($one['is_special'] == C('ASSETS_SPEC_CODE_YES')) {
                $one['type_name'] .= ',' . C('ASSETS_SPEC_CODE_YES_NAME');
            }
            if ($one['is_metering'] == C('ASSETS_METER_CODE_YES')) {
                $one['type_name'] = C('ASSETS_METER_CODE_YES_NAME');
            }
            if ($one['is_qualityAssets'] == C('ASSETS_QUALITY_CODE_YES')) {
                $one['type_name'] .= ',' . C('ASSETS_QUALITY_CODE_YES_NAME');
            }
            $departid = $one['departid'] . ',' . $departid;
            $one['type_name'] = ltrim($one['type_name'], ",");
            if (time() < strtotime($one['guarantee_date'])) {
                $one['guarantee_status'] = '保内';
            } else {
                $one['guarantee_status'] = '保外';
            }
            switch ($one['status']) {
                case '0':
                    $one['status'] = '在用';
                    break;
                case '1':
                    $one['status'] = '维修中';
                    break;
                case '2':
                    $one['status'] = '已报废';
                    break;
                case '3':
                    $one['status'] = '已外调';
                    break;
                case '4':
                    $one['status'] = '外调中';
                    break;
                case '5':
                    $one['status'] = '报废中';
                    break;
                case '6':
                    $one['status'] = '转科中';
                    break;
                default:
                    # code...
                    break;
            }
        }
        //获取设备对应的模板名称
        $AsstsPackMod = new PatrolAssetsPackModel();
        $asArr = $AsstsPackMod->getTemplateName($assInfo, $assidArr);
        $result["total"] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result["rows"] = $asArr;
        return $result;
    }

    public function save_plan()
    {
        $assnum = trim(I('post.assnum'), ',');
        $assnum = explode(',', $assnum);
        $data['patrol_name'] = I('post.patrol_name');
        $data['remark'] = I('post.remark');
        $data['patrol_level'] = I('post.level');
        $data['hospital_id'] = I('post.hospital_id');
        $data['patrol_start_date'] = I('post.startDate');
        $data['patrol_end_date'] = I('post.endDate');
        if($data['patrol_start_date'] > $data['patrol_end_date']){
            return array('status' => -1, 'msg' => '请设置合理的计划时间');
        }
        $data['is_cycle'] = I('post.is_cycle') ? 1 : 0;
        if (I('post.is_cycle')) {
            $data['cycle_unit'] = I('post.unit');
            $data['cycle_setting'] = I('post.cycle_setting');
            $data['total_cycle'] = 0;//总期数0 审批通过并且发布后才计算实际总期数
            $data['current_cycle'] = 0;//当前第0期
        } else {
            //非周期计划
            $data['total_cycle'] = 1;//总期数1
            $data['current_cycle'] = 1;//当前第1期
        }
        $data['patrol_status'] = 1;
        $data['add_user'] = session('username');
        $data['add_time'] = date('Y-m-d H:i:s');
        $data = $this->approve($data);
        if ($data == null) {
            return array('status' => -1, 'msg' => '未设置审批流程，请先设置审批流程');
        }
        //记录计划信息
        $this->startTrans();
        $new_plan_id = $this->insertData('patrol_plans', $data);
        if (!$new_plan_id) {
            $this->rollback();
            return array('status' => -1, 'msg' => '记录计划信息失败');
        }
        $assetsModel = M("assets_info"); // 实例化User对象
        $assnum_assid = $assetsModel->where(['assnum' => array('in', $assnum)])->getField('assnum,assid');
        foreach ($assnum as $k => $v) {
            $plan_assets_data[$k]['patrid'] = $new_plan_id;
            $plan_assets_data[$k]['assid'] = $assnum_assid[$v];
            $plan_assets_data[$k]['assnum'] = $v;
            $plan_assets_data[$k]['assnum_tpid'] = $_POST[$v];
            $plan_assets_data[$k]['enable_status'] = 1;
            $plan_assets_data[$k]['add_user'] = session('username');
            $plan_assets_data[$k]['add_time'] = date('Y-m-d H:i:s');
        }
        //记录计划设备信息
        $as_res = $this->insertDataALL('patrol_plans_assets', $plan_assets_data);
        if (!$as_res) {
            $this->rollback();
            return array('status' => -1, 'msg' => '记录计划设备信息失败');
        }
        $this->commit();

        if ($data['current_approver'] && (new ModuleModel())->decide_wx_login()) {
            // 发送待审批提醒给审批人
            $addTime = strtotime($data['add_time']);

            $users = UserModel::getUsersByUsernames(explode(',', $data['current_approver']), ['openid']);
            $openIds = array_column($users, 'openid');
            $openIds = array_filter($openIds);
            $openIds = array_unique($openIds);

            $messageData = [
                'const13'           => '设备巡查保养计划',// 工单类型
                'thing15'           => $data['patrol_name'],// 工单名称
                'character_string7' => date('YmdHis', $addTime),// 工单号
                'thing16'           => $data['add_user'],// 发起人
                'time3'             => getHandleMinute($addTime),// 创建时间
            ];

            foreach ($openIds as $openId) {
                Weixin::instance()->sendMessage($openId, '工单待审核提醒', $messageData);
            }
        }

        return array('status' => 1, 'msg' => '创建计划成功');
    }

    public function get_total_cycle($start_date,$end_date,$cycle_unit,$cycle_setting)
    {
        switch ($cycle_unit) {
            case 'day':
                $res = day_between_two_dates($start_date, $end_date);
                $total = ceil($res / $cycle_setting);
                break;
            case 'week':
                $res = week_between_two_dates($start_date, $end_date);
                $total = ceil($res / $cycle_setting);
                break;
            case 'month':
                $res = month_between_two_dates($start_date, $end_date);
                $total = ceil(count($res) / $cycle_setting);
                break;
            case 'quarter':
                $res = month_between_two_dates($start_date, $end_date);
                $total = ceil(count($res) / ($cycle_setting * 3));
                break;
            case 'year':
                $res = month_between_two_dates($start_date, $end_date);
                $total = ceil(count($res) / ($cycle_setting * 12));
                break;
        }
        return $total;
    }

    public function create_cycle_plan($patrid,$rel_date)
    {
        $planInfo = $this->DB_get_one('patrol_plans', '*', ['patrid' => $patrid]);
        if (!$planInfo['is_release']) {
            //计划未发布
            return ['status' => -1, 'msg' => '计划未发布'];
        }
        $join = ' LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $as_depart = $this->DB_get_all_join('patrol_plans_assets','A','distinct(B.departid)',$join,['A.patrid'=>$patrid,'A.enable_status'=>1]);
        $depart_ids = [];
        foreach ($as_depart as $v){
            $depart_ids[] = $v['departid'];
        }
        $departids_string = implode(',',$depart_ids);
        $this->updateData('patrol_plans',['assets_departid'=>$departids_string],['patrid'=>$patrid]);
        //查询计划设备信息
        $patrol_assets = M("patrol_plans_assets"); // 实例化User对象
        $assnum = $patrol_assets->where(['patrid'=>$planInfo['patrid'],'enable_status'=>1])->getField('assnum',true);
        if ($planInfo['is_cycle']) {
            //是周期计划
            if($planInfo['patrol_start_date'] >= $rel_date){
                $cycle_start_date = $planInfo['patrol_start_date'];
                $cycle_end_date = '';
                switch ($planInfo['cycle_unit']){
                    case 'day':
                        $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." day",strtotime($planInfo['patrol_start_date'])-86400));
                        break;
                    case 'week':
                        $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." week",strtotime($planInfo['patrol_start_date'])-86400));
                        break;
                    case 'month':
                        $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." month",strtotime($planInfo['patrol_start_date'])-86400));
                        break;
                    case 'quarter':
                        $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']*3) ." month",strtotime($planInfo['patrol_start_date'])-86400));
                        break;
                    case 'year':
                        $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." year",strtotime($planInfo['patrol_start_date'])-86400));
                        break;
                }
                if($cycle_end_date > $planInfo['patrol_end_date']){
                    $cycle_end_date = $planInfo['patrol_end_date'];
                }
                $insert_data['patrid'] = $planInfo['patrid'];
                $insert_data['period'] = $planInfo['current_cycle'] + 1;
                $insert_data['assets_nums'] = count($assnum);
                $insert_data['assets_departid'] = $departids_string;
                $insert_data['plan_assnum'] = json_encode($assnum);
                $insert_data['cycle_start_date'] = $cycle_start_date;
                $insert_data['cycle_end_date'] = $cycle_end_date;
                $insert_data['cycle_status'] = 0;
                $insert_data['create_time'] = $rel_date.' '.date('H:i:s');
                if($planInfo['patrol_level'] == 3){
                    $patrolnum = $this->DB_get_one('patrol_plans_cycle', 'max(patrol_num) as patrol_num', array('patrol_num' => array('LIKE', 'PM%')));
                    if (!$patrolnum) {
                        $insert_data['patrol_num'] = 'PM001';
                    } else {
                        $insert_data['patrol_num'] = "PM" . str_pad(substr($patrolnum['patrol_num'], 2) + 1, 3, "0", STR_PAD_LEFT);
                    }
                }
                $res = $this->insertData('patrol_plans_cycle',$insert_data);
                if($res){
                    $this->updateData('patrol_plans',['current_cycle'=>$insert_data['period']],['patrid'=>$planInfo['patrid']]);
                    return ['status' => 1, 'msg' => '创建周期计划成功','depart_ids'=>$depart_ids];
                }else{
                    return ['status' => -1, 'msg' => '创建周期计划失败'];
                }
            }else{
                //发布时间大于计划开始时间
                $total_cycle = $this->get_total_cycle($planInfo['patrol_start_date'],date('Y-m-d'),$planInfo['cycle_unit'],$planInfo['cycle_setting']);
                $cycle_start_date = $planInfo['patrol_start_date'];
                $cycle_end_date = '';
                $insert_data = [];
                for($i = 1;$i <= $total_cycle;$i++){
                    switch ($planInfo['cycle_unit']){
                        case 'day':
                            $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." day",strtotime($cycle_start_date)-86400));
                            break;
                        case 'week':
                            $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." week",strtotime($cycle_start_date)-86400));
                            break;
                        case 'month':
                            $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." month",strtotime($cycle_start_date)-86400));
                            break;
                        case 'quarter':
                            $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']*3) ." month",strtotime($cycle_start_date)-86400));
                            break;
                        case 'year':
                            $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." year",strtotime($cycle_start_date)-86400));
                            break;
                    }
                    if($cycle_end_date >= $planInfo['patrol_end_date']){
                        $cycle_end_date = $planInfo['patrol_end_date'];
                    }
                    $insert_data[$i-1]['patrid'] = $planInfo['patrid'];
                    $insert_data[$i-1]['period'] = $i;
                    $insert_data[$i-1]['assets_nums'] = count($assnum);
                    $insert_data[$i-1]['assets_departid'] = $departids_string;
                    $insert_data[$i-1]['plan_assnum'] = json_encode($assnum);
                    $insert_data[$i-1]['cycle_start_date'] = $cycle_start_date;
                    $insert_data[$i-1]['cycle_end_date'] = $cycle_end_date;
                    $insert_data[$i-1]['cycle_status'] = 0;
                    $insert_data[$i-1]['create_time'] = $rel_date.' '.date('H:i:s');
                    if($cycle_end_date < date('Y-m-d')){
                        $insert_data[$i-1]['cycle_status'] = 4;
                    }
                    if($cycle_end_date < $planInfo['patrol_end_date']){
                        $cycle_start_date = date('Y-m-d',strtotime($cycle_end_date)+86400);
                    }
                    if($planInfo['patrol_level'] == 3){
                        $patrolnum = $this->DB_get_one('patrol_plans_cycle', 'max(patrol_num) as patrol_num', array('patrol_num' => array('LIKE', 'PM%')));
                        if (!$patrolnum) {
                            $insert_data[$i-1]['patrol_num'] = 'PM00'.$i;
                        } else {
                            $insert_data[$i-1]['patrol_num'] = "PM" . str_pad(substr($patrolnum['patrol_num'], 2) + ($i+1), 3, "0", STR_PAD_LEFT);
                        }
                    }
                }
                if($insert_data){
                    $res = $this->insertDataALL('patrol_plans_cycle',$insert_data);
                    if($res){
                        $this->updateData('patrol_plans',['current_cycle'=>$total_cycle],['patrid'=>$planInfo['patrid']]);
                        return ['status' => 1, 'msg' => '创建周期计划成功','depart_ids'=>$depart_ids];
                    }else{
                        return ['status' => -1, 'msg' => '创建周期计划失败'];
                    }
                }
            }

        } else {
            //非周期计划
            $insert_data['patrid'] = $planInfo['patrid'];
            $insert_data['period'] = 1;
            $insert_data['assets_nums'] = count($assnum);
            $insert_data['assets_departid'] = $departids_string;
            $insert_data['plan_assnum'] = json_encode($assnum);
            $insert_data['cycle_start_date'] = $planInfo['patrol_start_date'];
            $insert_data['cycle_end_date'] = $planInfo['patrol_end_date'];
            $insert_data['cycle_status'] = 0;
            $insert_data['create_time'] = $rel_date.' '.date('H:i:s');
            if($planInfo['patrol_level'] == 3){
                $patrolnum = $this->DB_get_one('patrol_plans_cycle', 'max(patrol_num) as patrol_num', array('patrol_num' => array('LIKE', 'PM%')));
                if (!$patrolnum) {
                    $insert_data['patrol_num'] = 'PM001';
                } else {
                    $insert_data['patrol_num'] = "PM" . str_pad(substr($patrolnum['patrol_num'], 2) + 1, 3, "0", STR_PAD_LEFT);
                }
            }
            $res = $this->insertData('patrol_plans_cycle',$insert_data);
            if($res){
                return ['status' => 1, 'msg' => '创建计划成功','depart_ids'=>$depart_ids];
            }else{
                return ['status' => -1, 'msg' => '创建计划失败'];
            }
        }
    }

    public function create_next_plan($patrid)
    {
        $planInfo = $this->DB_get_one('patrol_plans','*',['patrid'=>$patrid]);
        if($planInfo['patrol_status'] == 4){
            return array('status' => -1, 'msg' => '计划已结束');
        }
        if(!$planInfo['is_cycle']){
            return array('status' => -1, 'msg' => '该计划不是周期计划');
        }
        //查询计划设备信息
        $patrol_assets = M("patrol_plans_assets");
        $assnum = $patrol_assets->where(['patrid'=>$patrid,'enable_status'=>1])->getField('assnum',true);

        $join = ' LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $as_depart = $this->DB_get_all_join('patrol_plans_assets','A','distinct(B.departid)',$join,['A.patrid'=>$patrid,'A.enable_status'=>1]);
        $depart_ids = [];
        foreach ($as_depart as $v){
            $depart_ids[] = $v['departid'];
        }
        //原计划包含科室
        $old_departids = explode(',',$planInfo['assets_departid']);
        $plan_new_departids = array_merge($old_departids,array_diff($depart_ids, $old_departids));

        $departids_string = implode(',',$depart_ids);
        $this->updateData('patrol_plans',['assets_departid'=>implode(',',$plan_new_departids)],['patrid'=>$patrid]);
        $plans = $this->DB_get_all('patrol_plans_cycle','cycid,patrid,cycle_start_date,cycle_end_date',['patrid'=>$patrid],'','cycid desc');
        if($planInfo['total_cycle'] > count($plans) && (date('Y-m-d') > $plans[0]['cycle_end_date'])){
            //可以生成下一周期计划
            //获取下一周期开始结束时间
            $cycle_start_date = date('Y-m-d',strtotime($plans[0]['cycle_end_date'])+86400);
            $cycle_end_date = '';
            switch ($planInfo['cycle_unit']){
                case 'day':
                    $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." day",strtotime($cycle_start_date)-86400));
                    break;
                case 'week':
                    $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." week",strtotime($cycle_start_date)-86400));
                    break;
                case 'month':
                    $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." month",strtotime($cycle_start_date)-86400));
                    break;
                case 'quarter':
                    $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']*3) ." month",strtotime($cycle_start_date)-86400));
                    break;
                case 'year':
                    $cycle_end_date = date("Y-m-d",strtotime("+".($planInfo['cycle_setting']) ." year",strtotime($cycle_start_date)-86400));
                    break;
            }
            if($cycle_end_date > $planInfo['patrol_end_date']){
                $cycle_end_date = $planInfo['patrol_end_date'];
            }
            $insert_data['patrid'] = $patrid;
            $insert_data['period'] = count($plans) + 1;
            $insert_data['assets_nums'] = count($assnum);
            $insert_data['assets_departid'] = $departids_string;
            $insert_data['plan_assnum'] = json_encode($assnum);
            $insert_data['cycle_start_date'] = $cycle_start_date;
            $insert_data['cycle_end_date'] = $cycle_end_date;
            if(date('Y-m-d') > $cycle_end_date){
                $insert_data['cycle_status'] = 4;//逾期未完成
            }else{
                $insert_data['cycle_status'] = 0;
            }
            $insert_data['create_time'] = date('Y-m-d H:i:s');
            if($planInfo['patrol_level'] == 3){
                $patrolnum = $this->DB_get_one('patrol_plans_cycle', 'max(patrol_num) as patrol_num', array('patrol_num' => array('LIKE', 'PM%')));
                if (!$patrolnum) {
                    $insert_data['patrol_num'] = 'PM001';
                } else {
                    $insert_data['patrol_num'] = "PM" . str_pad(substr($patrolnum['patrol_num'], 2) + 1, 3, "0", STR_PAD_LEFT);
                }
            }
            $res = $this->insertData('patrol_plans_cycle',$insert_data);
            if($res){
                $this->updateData('patrol_plans',['current_cycle'=>$insert_data['period']],['patrid'=>$planInfo['patrid']]);
                return ['status' => 1, 'msg' => '创建下一周期计划成功','depart_ids'=>$depart_ids,'patrol_name'=>$planInfo['patrol_name'],'cycle_start_date'=>$cycle_start_date,'cycle_end_date'=>$cycle_end_date];
            }else{
                return ['status' => -1, 'msg' => '创建下一周期计划失败'];
            }
        }else{
            return ['status' => -1, 'msg' => '咱不能创建下一周期计划'];
        }
    }

    public function send_wechat_then_create_next_plans($depart_ids,$patrid,$patrol_name,$cycle_start_date,$cycle_end_date,$hospital_id)
    {
        if(!$depart_ids){
            return false;
        }
        $engineers = $this->get_department_engineers($depart_ids,$hospital_id);
        if ($engineers) {
            //==========================================短信 END============================================
            if (C('USE_FEISHU') === 1) {
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**计划名称：**' . $patrol_name;
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**执行日期：**' . $cycle_start_date.' 至 '.$cycle_end_date;
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '请及时进行巡检安排';
                $feishu_fields[] = $fd;

                $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag'] = 'div';
                $card_data['elements'][0]['fields'] = $feishu_fields;
                $card_data['header']['template'] = 'blue';
                $card_data['header']['title']['content'] = '巡检计划执行提醒';
                $card_data['header']['title']['tag'] = 'plain_text';

                foreach ($engineers as $key => $value) {
                    if ($value['openid']) {
                        $this->send_feishu_card_msg($value['openid'], $card_data);
                    }
                }
                //==========================================飞书 END==========================================
            } else {
                //==========================================微信 START==========================================
                $moduleModel = new ModuleModel();
                $wx_status = $moduleModel->decide_wx_login();
                if ($wx_status) {
                    $openIds = array_column($engineers, 'openid');
                    $openIds = array_filter($openIds);
                    $openIds = array_unique($openIds);

                    $messageData = [
                        'thing8'  => $patrol_name,// 巡检名称
                        'time9'   => $cycle_start_date,// 开始时间
                        'time10'  => $cycle_end_date,// 结束时间
                        'const14' => '待执行',// 巡检状态
                    ];

                    foreach ($openIds as $openId) {
                        Weixin::instance()->sendMessage($openId, '设备巡检工单处理通知', $messageData);
                    }
                }
                //==========================================微信 END============================================
            }
        }
    }

    //格式化短信信息
    public function formatSmsContent($content, $data)
    {
        $content = str_replace("{patrolname}", $data['patrolname'], $content);
        $content = str_replace("{patrolnum}", $data['patrolnum'], $content);
        $content = str_replace("{cyclenum}", $data['cyclenum'], $content);
        $content = str_replace("{patrol_level}", $data['patrol_level'], $content);
        $content = str_replace("{startdate}", $data['startdate'], $content);
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{department}", $data['department'], $content);
        $content = str_replace("{applicant}", $data['applicant'], $content);
        return $content;
    }

    /*
    获取审批的相关数据
     */
    public function approve($data)
    {
        $where['hospital_id'] = $data['hospital_id'];
        $where['approve_type'] = 'patrol_approve';//巡查审批类型
        $fields = "A.status as status,C.approve_user,C.listorder,C.approve_user_aux";
        $join[0] = "LEFT JOIN sb_approve_process as B ON A.typeid = B.typeid";
        $join[1] = "LEFT JOIN sb_approve_process_user as C ON B.processid = C.processid";
        $approve = $this->DB_get_all_join('approve_type', 'A', $fields, $join, $where, '', 'C.listorder asc');
        foreach ($approve as $key => $value) {
            if ($value['status'] == 0) {
                $data['approve_status'] = '-1';
                $data['patrol_status'] = '2';//直接可以为2待发布状态
                return $data;
            }
            if ($value['approve_user'] == "") {
                return null;
            }
            if ($value['listorder'] == 1) {
                $data['current_approver'] = $value['approve_user'] . ',' . $value['approve_user_aux'];
            }
            $data['approve_status'] = '0';
            $data['patrol_status'] = '1';
            $data['not_complete_approver'] .= $value['approve_user'] . ',' . $value['approve_user_aux'] . ',';
            $data['all_approver'] .= '/' . $value['approve_user'] . '/,/' . $value['approve_user_aux'] . '/,';

        }
        $data['not_complete_approver'] = substr($data['not_complete_approver'], 0, -1);
        $data['all_approver'] = substr($data['all_approver'], 0, -1);
        return $data;
    }

    /*
     * 修改周期计划
     * @params1 $patrid int 计划ID
     * @params2 $patrol_level int 级别
     * @params3 $executor array()
     * return boolen
     */
    public function updateCyclePlan($patrid, $patrol_level, $executor, $planInfo)
    {
        $ass_name = json_decode($_POST['assnum_name'], true);
        $ass_tpid = json_decode($_POST['assnum_tpid'], true);
        $assnum_tpid = [];
        foreach ($ass_name as $k => $v) {
            $assnum_tpid[$v] = $ass_tpid[$k];
        }
        $assnum_tpid = json_encode($assnum_tpid);
        foreach ($executor as $k => $v) {
            $updata['plan_assnum'] = json_encode(explode(',', $v));
            //查找当前执行人是否存在在该周期计划
            $cycid = $this->DB_get_one('patrol_plan_cycle', 'cycid', array('patrid' => $patrid, 'patrol_level' => $patrol_level, 'executor' => $k));
            $updata['assnum_tpid'] = $assnum_tpid;
            if ($cycid) {
                //存在，更新未发布的周期计划设备组
                $this->updateData('patrol_plan_cycle', $updata, array('patrid' => $patrid, 'patrol_level' => $patrol_level, 'executor' => $k, 'is_release' => 0));
            } else {
                //不存在，新增该执行人的周期计划
                //查询当前正在执行的期数
                $nowday = date('Y-m-d');
                $period = $this->DB_get_one('patrol_plan_cycle', 'period,is_release,startdate,overdate', array('patrid' => $patrid, 'patrol_level' => $patrol_level, 'startdate' => array('ELT', $nowday), 'overdate' => array('EGT', $nowday)));
                if (!$period) {
                    return false;
                }
                $startPeriod = $period['period'];
                if ($startPeriod <= $planInfo['cycletimes']) {
                    //未超期数，生成新的周期计划
                    //计划保存成功，生成周期计划
                    if ($planInfo['unit'] == '月') {
                        $unit = 'month';
                    } elseif ($planInfo['unit'] == '周') {
                        $unit = 'week';
                    } elseif ($planInfo['unit'] == '天') {
                        $unit = 'day';
                    } else {
                        $unit = 'month';
                    }
                    $cycData = array();
                    $startDate = $period['startdate'];
                    $j = 0;
                    for ($i = $startPeriod; $i <= $planInfo['cycletimes']; $i++) {
                        if ($i == 0) {
                            $cycData[$j]['startdate'] = $startDate;
                        } else {
                            //$startDate = date("Y-m-d", strtotime("+1 day", strtotime($startDate)));
                            $cycData[$j]['startdate'] = $startDate;
                        }
                        $cycData[$j]['patrid'] = $patrid;
                        $cycData[$j]['executor'] = $k;
                        $cycData[$j]['assnum_tpid'] = $assnum_tpid;
                        $cycData[$j]['plan_assnum'] = $updata['plan_assnum'];
                        $cycData[$j]['cyclenum'] = $planInfo['patrolnum'] . '-' . date('Ymd', strtotime($startDate));
                        $startDate = date("Y-m-d", strtotime("+1 $unit", strtotime($startDate)));
                        $startDate = date("Y-m-d", strtotime("-1 day", strtotime($startDate)));
                        $cycData[$j]['patrol_level'] = $planInfo['patrol_level'];
                        $cycData[$j]['period'] = $i;
                        $cycData[$j]['overdate'] = $startDate;
                        if ($j == 0) {
                            $cycData[$j]['is_release'] = $period['is_release'];
                            $cycData[$j]['release_time'] = $period['is_release'] == 1 ? time() : 0;
                        } else {
                            $cycData[$j]['is_release'] = 0;
                            $cycData[$j]['release_time'] = 0;
                        }
                        $cycData[$j]['status'] = 0;
                        $startDate = date("Y-m-d", strtotime("+1 day", strtotime($startDate)));
                        $j++;
                    }
                    $this->insertDataALL('patrol_plan_cycle', $cycData);
                } else {
                    return false;
                }
            }
        }
        return true;
    }


    //获取实施列表
    public function doTaskList()
    {
        $doTaskMenu = get_menu($this->MODULE, 'Patrol', 'doTask');
        $addPatrolMenu = get_menu($this->MODULE, 'Patrol', 'addPatrol');
        if (!$doTaskMenu && !$addPatrolMenu) {
            $result['msg'] = '无权限';
            $result['code'] = 400;
            return $result;
        }
        $patrol_level = I('POST.patrol_level');
        $assidsArr = I('POST.assidsArr');
        $departid = I('POST.departid');
        $assets = I('POST.assets');
        $cycId = I('POST.cycid');

        if ($departid) {
            $where['departid'] = $departid;
        }
        if ($assets) {
            $where['assets'] = array('like', '%' . $assets . '%');
        }
        //获取周期信息
        $cycInfo = $this->DB_get_one('patrol_plan_cycle', '*', array('cycid' => $cycId));
        $patrol_model = M('patrol_template');
        $all_tps = $patrol_model->where(array('is_delete' => 0))->getField('tpid,points_num');
        $assnum_tpid = json_decode($cycInfo['assnum_tpid'], true);
        $assidsArr = explode(',', $assidsArr);
        $where['assnum'] = array('in', $assidsArr);
        $where['is_delete'] = '0';
        $total = $this->DB_get_count('assets_info', $where);
        $asArr = $this->DB_get_all('assets_info', 'assets,assnum,departid,catid,departid,status,is_firstaid,is_metering,is_qualityAssets,is_special', $where);
        foreach ($asArr as $k => $v) {
            $asArr[$k]['arr_num'] = $all_tps[$assnum_tpid[$v['assnum']]];
        }
        //查询设备所对应的cycid
        if (!session('isSuper')) {
            $nwhere['cycid'] = array('in', $cycId);
            //$nwhere['executor'] = session('username');
        } else {
            $nwhere['cycid'] = array('in', $cycId);
        }
        $check = $this->DB_get_all('patrol_plan_cycle', 'cycid,executor,plan_assnum', $nwhere);
        foreach ($asArr as $k => $v) {
            foreach ($check as $k1 => $v1) {
                if (in_array($v['assnum'], json_decode($v1['plan_assnum']))) {
                    $asArr[$k]['cycid'] = (int)$v1['cycid'];
                    $asArr[$k]['executor'] = $v1['executor'];
                }
            }
        }
        $asArr = $this->checkAbnormal($asArr, $assidsArr, $cycId);
        if (!$asArr) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($asArr as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            $one['category'] = $catname[$one['catid']]['category'];
            switch ($one['status']) {
                case C('ASSETS_STATUS_USE'):
                    $one['status_name'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $one['status_name'] = C('ASSETS_STATUS_REPAIR_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $one['status_name'] = C('ASSETS_STATUS_SCRAP_NAME');
                    break;
            }
            if ($one['is_firstaid'] == C('ASSETS_FIRST_CODE_YES')) {
                $one['type_name'] = C('ASSETS_FIRST_CODE_YES_NAME');
            }
            if ($one['is_special'] == C('ASSETS_SPEC_CODE_YES')) {
                $one['type_name'] .= ',' . C('ASSETS_SPEC_CODE_YES_NAME');
            }
            if ($one['is_metering'] == C('ASSETS_METER_CODE_YES')) {
                $one['type_name'] = C('ASSETS_METER_CODE_YES_NAME');
            }
            if ($one['is_qualityAssets'] == C('ASSETS_QUALITY_CODE_YES')) {
                $one['type_name'] .= ',' . C('ASSETS_QUALITY_CODE_YES_NAME');
            }
            $one['type_name'] = ltrim($one['type_name'], ",");

            if (!$one['pointsNum']) {
                $one['pointsNum'] = count(json_decode($one['arr_num']));
            }
            if (!$one['abnormalPointNum']) {
                $one['abnormalPointNum'] = 0;
            }
            $one['count'] = '<span class="rquireCoin">' . $one['abnormalPointNum'] . '</span>/' . $one['pointsNum'];
            if (($one['status'] == C('ASSETS_STATUS_REPAIR') or $one['status'] == C('ASSETS_STATUS_SCRAP')) && $one['executeStatus'] != C('MAINTAIN_COMPLETE')) {
                $layRver = 'result';
            } else {
                $layRver = 'maintain';
            }
            if (!$doTaskMenu) {
                //不是实施人 直接进入此设备的巡查详情页
                $layRver = 'maintain';
                $url = $addPatrolMenu['actionurl'];
            } else {
                $url = $doTaskMenu['actionurl'];
            }
            $linkcolor = '';
            if ($one['executeStatus'] == C('MAINTAIN_COMPLETE')) {
                if ($one['asset_status_num'] == 1 or $one['asset_status_num'] == 2) {
                    $linkName = '合格';
                    $linkcolor = ' layui-btn-normal';
                } else {
                    $linkName = '异常';
                    $linkcolor = ' layui-btn-danger';
                }
            } elseif ($one['executeStatus'] == C('MAINTAIN_PATROL')) {
                $linkName = '暂存中';
            } else {
                $linkName = '待保养';
            }
            $one['operation'] = $this->returnListLink($linkName, $url, $layRver, C('BTN_CURRENCY') . $linkcolor);
        }
        $result["total"] = $total;
        $result["rows"] = $asArr;
        $result['code'] = 200;
        return $result;
    }

    //发布计划操作
    public function releasePatrol()
    {
        $patrid = trim(I('POST.pid'));
        $planData = $this->DB_get_one('patrol_plans', 'patrid,patrol_name,patrol_level,patrol_start_date,patrol_end_date,is_cycle,cycle_unit,cycle_setting', array('patrid' => $patrid));
        if (!$planData) {
            return array('status' => -1, 'msg' => '查找不到该计划信息！');
        }
        $rel_date = I('POST.rel_date') ? I('POST.rel_date') : date('Y-m-d');
        if($rel_date > $planData['patrol_end_date']){
            return array('status' => -1, 'msg' => '发布日期不能晚于计划结束日期【'.$planData['patrol_end_date'].'】');
        }
        $remark = trim(I('POST.remark'));
        $data['patrol_status'] = 3;//待实施
        $data['is_release'] = C('YES_STATUS');
        $data['release_user'] = session('username');
        $data['release_time'] = $rel_date.' '.date('H:i:s');
        $data['release_remark'] = $remark;
        $this->startTrans();
        $res = $this->updateData('patrol_plans', $data, array('patrid' => $patrid));
        if(!$res){
            $this->rollback();
            return array('status' => 0, 'msg' => '发布计划失败');
        }
        //日志行为记录文字
        $text = getLogText('releasePatrolLogText', $planData);
        $this->addLog('patrol_plans', M()->getLastSql(), $text);
        //发布计划成功，生成周期计划
        if ($planData['is_cycle']) {
            //周期计划，获取总周期数
            $total_cycle = $this->get_total_cycle($planData['patrol_start_date'],$planData['patrol_end_date'],$planData['cycle_unit'],$planData['cycle_setting']);
            $this->updateData('patrol_plans', ['total_cycle' => $total_cycle], array('patrid' => $patrid));
        }
        $create_res = $this->create_cycle_plan($planData['patrid'],$rel_date);
        if($create_res['status'] != 1){
            $this->rollback();
        }else{
            $this->commit();
        }
        //获取该计划设备包括的所有科室的工程师
        $depart_ids = $create_res['depart_ids'];
        $engineers = [];
        foreach ($depart_ids as $v){
            $users = $this->getToUser(session('userid'),$v,'Repair','Repair','accept');
            foreach($users as $uv){
                $engineers[] = $uv;
            }
        }
        //==========================================短信 START==========================================
        $settingData = $this->checkSmsIsOpen($this->Controller);
        if ($settingData && $engineers) {
            //有开启短信
            $planData['patrol_level_name'] = PatrolModel::formatpatrolLevel($planData['patrol_level']);
            $ToolMod = new ToolController();
            if ($settingData['doPatrolTask']['status'] == C('OPEN_STATUS')) {
                //通知被借科室准备设备 开启
                $phone = $this->formatPhone($engineers);
                $sms = PatrolModel::formatSmsContent($settingData['doPatrolTask']['content'], $planData);
                $ToolMod->sendingSMS($phone, $sms, $this->Controller, $patrid);
            }
        }
        //==========================================短信 END============================================
        if (C('USE_FEISHU') === 1) {
            //==========================================飞书 START========================================
            //要显示的字段区域
            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**计划名称：**' . $planData['patrol_name'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**执行日期：**' . $planData['patrol_start_date'].' 至 '.$planData['patrol_end_date'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '请及时进行巡检安排';
            $feishu_fields[] = $fd;

            $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
            $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
            $card_data['elements'][0]['tag'] = 'div';
            $card_data['elements'][0]['fields'] = $feishu_fields;
            $card_data['header']['template'] = 'blue';
            $card_data['header']['title']['content'] = '巡检计划执行提醒';
            $card_data['header']['title']['tag'] = 'plain_text';

            foreach ($engineers as $key => $value) {
                if ($value['openid']) {
                    $this->send_feishu_card_msg($value['openid'], $card_data);
                }
            }
            //==========================================飞书 END==========================================
        } else {
            //==========================================微信 START==========================================
            $moduleModel = new ModuleModel();
            $wx_status = $moduleModel->decide_wx_login();
            if ($wx_status) {
                $openIds = array_column($engineers, 'openid');
                $openIds = array_filter($openIds);
                $openIds = array_unique($openIds);

                $messageData = [
                    'thing8'  => $planData['patrol_name'],// 巡检名称
                    'time9'   => $planData['patrol_start_date'],// 开始时间
                    'time10'  => $planData['patrol_end_date'],// 结束时间
                    'const14' => '待执行',// 巡检状态
                ];

                foreach ($openIds as $openId) {
                    Weixin::instance()->sendMessage($openId, '设备巡检工单处理通知', $messageData);
                }
            }
            //==========================================微信 END============================================
        }
        return array('status' => 1, 'msg' => '发布计划成功');
    }

    public function getDoTaskAsstes($assidsArr, $cycInfo, $cycId)
    {
        //查询所有模板明细
        $patrol_model = M('patrol_template');
        $all_tps = $patrol_model->where(array('is_delete' => 0))->getField('tpid,points_num');
        $assnum_tpid = json_decode($cycInfo['assnum_tpid'], true);
        $assidsArr = explode(',', $assidsArr);
        $asArr = $this->DB_get_all('assets_info', 'assets,assnum,departid', array('assnum' => array('in', $assidsArr)));
        foreach ($asArr as $k => $v) {
            $asArr[$k]['arr_num'] = $all_tps[$assnum_tpid[$v['assnum']]];
        }
        $asArr = $this->checkAbnormal($asArr, $assidsArr, $cycId);
        return $asArr;
    }

    public function checkAbnormal($asArr, $assidsArr, $cycId)
    {
        $executeFileds = 'cycid,asset_status_num,assetnum,status AS executeStatus,execid';
        //$executeWhere = "assetnum IN ($assidsArr) AND cycid in(" . $cycId . ")";
        $executeWhere['assetnum'] = array('in', $assidsArr);
        $executeWhere['cycid'] = array('in', $cycId);
        //$executeWhere = "assetnum IN ($assidsArr) AND cycid =$cycId";
        $execute = $this->DB_get_all('patrol_execute', $executeFileds, $executeWhere);
        if ($execute) {
            $execidSet = '';
            foreach ($execute as &$executeValue) {
                $execidSet .= ',' . $executeValue['execid'];
            }
            $execidSet = trim($execidSet, ',');
            $abnormalFileds = 'execid,group_concat(result) AS result';
            $abnormalWhere = "execid IN ($execidSet) and result!='合格'";
            $abnormal = $this->DB_get_all('patrol_execute_abnormal', $abnormalFileds, $abnormalWhere, 'execid');
            foreach ($execute as &$executeValue) {
                if ($abnormal) {
                    foreach ($abnormal as $abnormalValue) {
                        if ($abnormalValue['execid'] == $executeValue['execid']) {
                            if ($executeValue['asset_status_num'] != C('ASSETS_STATUS_IN_MAINTENANCE') && $executeValue['asset_status_num'] != C('ASSETS_STATUS_SCRAPPED')) {
                                $executeValue['abnormalPointNum'] = substr_count($abnormalValue['result'], ',') + 1;
                                $executeValue['abnormalAsset'] = true;
                            } else {
                                $executeValue['abnormalAsset'] = true;
                                $executeValue['abnormalPointNum'] = 0;
                            }
                            break;
                        } else {
                            if ($executeValue['asset_status_num'] == C('ASSETS_STATUS_IN_MAINTENANCE') or $executeValue['asset_status_num'] == C('ASSETS_STATUS_SCRAPPED')) {
                                $executeValue['abnormalAsset'] = true;
                                $executeValue['abnormalPointNum'] = 0;
                            }
                        }
                    }
                } else {
                    if ($executeValue['asset_status_num'] == C('ASSETS_STATUS_IN_MAINTENANCE') or $executeValue['asset_status_num'] == C('ASSETS_STATUS_SCRAPPED')) {
                        $executeValue['abnormalAsset'] = true;
                    }
                    $executeValue['abnormalPointNum'] = 0;
                }
            }
            $join[1] = 'LEFT JOIN sb_patrol_points AS C ON C.ppid=A.ppid';
            $fields = 'GROUP_CONCAT(C.num) as num,A.execid';
            $abnormalarr = $this->DB_get_all_join('patrol_execute_abnormal', 'A', $fields, $join, "A.execid IN ($execidSet)", 'A.execid');
            foreach ($execute as &$one) {
                foreach ($abnormalarr as &$two) {
                    if ($one['execid'] == $two['execid'] && $one['executeStatus'] == C('MAINTAIN_COMPLETE')) {
                        $one['arr_num'] = '["' . str_replace(',', '","', $two['num']) . '"]';
                    }
                }
            }
        }
        foreach ($asArr as &$arArrValue) {
            foreach ($execute as &$executeValue) {
                if ($executeValue['assetnum'] == $arArrValue['assnum']) {
                    if ($executeValue['arr_num']) {
                        $arArrValue['arr_num'] = $executeValue['arr_num'];
                    }
                    $arArrValue['executeStatus'] = $executeValue['executeStatus'];
                    $arArrValue['asset_status_num'] = $executeValue['asset_status_num'];
                    $arArrValue['abnormalPointNum'] = $executeValue['abnormalPointNum'];
                    $arArrValue['abnormalAsset'] = $executeValue['abnormalAsset'];
                }
            }
        }
        return $asArr;
    }


    /*
     * 查询巡查保养计划
     */
    public function getPatrolLists()
    {
        //排序加分页加搜索同意
        $limit = I('POST.limit') ? I('POST.limit') : 10;
        $page = I('POST.page') ? I('POST.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'ASC';
        $sort = I('POST.sort') ? I('POST.sort') : 'patrol_status';
        $patrol_name = I('POST.patrol_name');
        $patrol_status = I('POST.patrol_status');
        $patrol_level = I('POST.patrol_level');
        $hospital_id = session('current_hospitalid');
        $where['is_delete'] = C('NO_STATUS');
        $where['hospital_id'] = $hospital_id;
        if ($patrol_name) {
            //计划名称搜索
            $where['patrol_name'] = array('like', "%$patrol_name%");
        }
        //保养级别搜索
        if ($patrol_level) {
            $where['patrol_level'] = $patrol_level;
        }
        if ($patrol_status) {
            $where['patrol_status'] = $patrol_status;
        }
        //需要的字段
        $fields = 'patrid,patrol_name,patrol_level,patrol_status,remark,patrol_start_date,patrol_end_date,is_cycle,cycle_unit,cycle_setting,total_cycle,current_cycle';
        //获取总条数
        $total = $this->DB_get_count('patrol_plans', $where);
        //获取数据
        $patrol = $this->DB_get_all('patrol_plans', $fields, $where, '', $sort . ' ' . $order.',patrid desc', $offset . "," . $limit);
        if (!$patrol) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //查询用户发布权限
        $release = get_menu('Patrol', 'Patrol', 'releasePatrol');
        $approve = get_menu('Patrol', 'Patrol', 'approve');
        foreach ($patrol as $k => $v) {
            $patrol[$k]['patrol_date'] = $v['patrol_start_date'] . ' 至 ' . $v['patrol_end_date'];
            $patrol[$k]['cycle_name'] = $v['is_cycle'] ? '是' : '否';
            $patrol[$k]['total_cycle'] = $v['total_cycle'] ? $v['total_cycle'] : '-';
            $patrol[$k]['current_cycle'] = $v['current_cycle'] ? $v['current_cycle'] : '-';

            $patrol[$k]['patrol_status'] = (int)$v['patrol_status'];
            $html = '<div class="layui-btn-group">';
            if (in_array($v['patrol_status'], [3, 4])) {
                $html .= '<button type="button" class="layui-btn layui-btn-xs layui-btn-normal" lay-event="showPlans">查看</button>';
            }
            if ($v['patrol_status'] == 1) {
                if ($approve) {
                    $html .= '<button type="button" class="layui-btn layui-btn-xs" lay-event="showPlans">待审核</button>';
                } else {
                    $html .= '<button type="button" class="layui-btn layui-btn-xs layui-btn-disabled">待审核</button>';
                }

            }
            if ($v['patrol_status'] == 2) {
                if ($release) {
                    $html .= '<button type="button" class="layui-btn layui-btn-xs" lay-event="release" data-url="' . $release['actionurl'] . '">发布</button>';
                } else {
                    $html .= '<button type="button" class="layui-btn layui-btn-xs layui-btn-disabled">发布</button>';
                }

            }
            $html .= '</div>';
            $patrol[$k]['operation'] = $html;
            switch ($v['patrol_level']) {
                case 1:
                    $patrol[$k]['patrol_level_name'] = '日常保养(DC)';
                    break;
                case 2:
                    $patrol[$k]['patrol_level_name'] = '巡查保养(RC)';
                    break;
                case 3:
                    $patrol[$k]['patrol_level_name'] = '预防性维护(PM)';
                    break;
            }
            switch ($v['patrol_status']) {
                case 1:
                    $patrol[$k]['patrol_status_name'] = '待审核';
                    break;
                case 2:
                    $patrol[$k]['patrol_status_name'] = '待发布';
                    break;
                case 3:
                    $patrol[$k]['patrol_status_name'] = '实施中';
                    break;
                case 4:
                    $patrol[$k]['patrol_status_name'] = '已结束';
                    break;
            }
        }
        $result["total"] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["rows"] = $patrol;
        $result["code"] = 200;
        return $result;
    }

    //检查是否存在暂存的任务
    public function checkTemporary()
    {
        $idstr = I('POST.cycid');
        $where['cycid'] = array('in', $idstr);
        if (!session('isSuper')) {
            $where['executor'] = session('username');
        }
        $curExecutor = $this->DB_get_one('patrol_plan_cycle', 'cycid,plan_assnum', $where);
        if (!$curExecutor['cycid']) {
            $reslt['status'] = -1;
            $reslt['msg'] = '您不是计划执行人,请按流程操作！';
            return $reslt;
        }
        $cycid = I('POST.cycid');
        $where['cycid'] = array('EQ', $cycid);
        $where['status'] = array('EQ', C('MAINTAIN_PATROL'));
        $check = $this->DB_get_count('patrol_execute', $where);
        if ($check) {
            $reslt['status'] = -1;
            $reslt['msg'] = '请先完成暂存设备的巡查';
        } else {
            $assnum = json_decode($curExecutor['plan_assnum']);
            $where['assetnum'] = array('in', $assnum);
            $where['cycid'] = array('EQ', $cycid);
            $where['status'] = array('EQ', C('MAINTAIN_COMPLETE'));
            $count = $this->DB_get_count('patrol_execute', $where);
            if ($count != count($assnum)) {
                $reslt['status'] = 1;
            } else {
                $reslt['status'] = 2;
            }
        }
        return $reslt;
    }

    /*
     * 查询巡查保养计划
     */
    public function getPlansByTest()
    {
        //排序加分页加搜索同意
        $limit = I('POST.limit');
        $offset = I('POST.offset');
        $order = I('POST.order');
        $sort = I('POST.sort');
        $patrolname = I('POST.patrolname');
        $executor = I('POST.executor');
        $adduser = I('POST.adduser');
        $partrol_level = I('POST.partrol_level');
        $where = 1;
        if (!$sort) {
            $sort = 'patrid ';
        }
        if (!$order) {
            $order = 'asc';
        }
        if ($sort == 'patrolname') {
            $sort = 'patrolname';
        }
        if ($sort == 'addtime') {
            $sort = 'addtime';
        }
        if ($patrolname) {
            //计划名称搜索
            $where .= " and patrolname like '%" . $patrolname . "%'";
        }
        if ($executor) {
            //执行人搜索
            $patrids = $this->DB_get_all('patrol_plan_cycle', 'patrid', array('executor' => $executor), 'patrid', 'patrid desc', '200');
            $idstr = '';
            foreach ($patrids as $k => $v) {
                $idstr .= $v['patrid'] . ',';
            }
            $idstr = trim($idstr, ',');
            if ($idstr) {
                $where .= " and patrid in (" . $idstr . ")";
            }
        }
        //保养级别搜索
        if ($partrol_level == 1) {
            $where .= " and patrol_level = 1";
        } elseif ($partrol_level == 2) {
            $where .= " and patrol_level = 2";
        } elseif ($partrol_level == 3) {
            $where .= " and patrol_level = 3";
        }
        if ($adduser) {
            //计划执行人
            $where .= " and adduser like '%" . $adduser . "%'";
        }
        //需要的字段
        $fields = 'patrid,patrolnum,patrolname,patrol_level,cycletimes,unit,executedate,remark,adduser,addtime';
        //获取总条数
        $total = $this->DB_get_count('patrol_plan', $where);
        //获取数据
        $sql = "call get_protrol_plans($where)";
        $patrol = $this->query($sql);
        //查询发布次数
        foreach ($patrol as $k => $v) {
            $patrol[$k]['releaseNum'] = 0;
            $isre = $this->DB_get_all('patrol_plan_cycle', 'is_release', array('patrid' => $v['patrid'], 'patrol_leve' => $v['patrol_leve']), 'period');
            foreach ($isre as $k1 => $v1) {
                $patrol[$k]['releaseNum'] += $v1['is_release'];
            }
            //查询异常、执行、计划总台数
            //$countNum = $this->DB_get_one('patrol_plan_cycle','sum(abnormal_sum) as abnormal_sum,sum(implement_sum) as implement_sum,group_concat(plan_assnum) as plan_assnum',array('patrid'=>$v['patrid']));
            $v['plan_assnum'] = str_replace('[', '', $v['plan_assnum']);
            $v['plan_assnum'] = str_replace(']', '', $v['plan_assnum']);
            $v['plan_assnum'] = explode(',', $v['plan_assnum']);
            $patrol[$k]['planAssetsNum'] = count($v['plan_assnum']);
            $patrol[$k]['showNum'] = $v['implement_sum'] . '/' . $v['implement_sum'] . '/' . $patrol[$k]['planAssetsNum'];
        }
        //查询当前用户是否有权限进行发布&修订
        $Release = get_menu('Patrol', 'Patrol', 'releaseAndEidt');
        $html = '';
        foreach ($patrol as $k => $v) {
            $patrol[$k]['addtime'] = date('Y-m-d', $v['addtime']);
            $patrol[$k]['cyclenum'] = $v['cycletimes'] . ' (' . $v['unit'] . ')';
            if ($v['patrol_level'] == 1) {
                $patrol[$k]['patrol_level'] = '日常保养';
            } elseif ($v['patrol_level'] == 2) {
                $patrol[$k]['patrol_level'] = '巡查保养';
            } elseif ($v['patrol_level'] == 3) {
                $patrol[$k]['patrol_level'] = '预防性维护';
            }
            if ($v['unit'] == '月') {
                $unit = 'month';
            } elseif ($v['unit'] == '周') {
                $unit = 'week';
            } elseif ($v['unit'] == '天') {
                $unit = 'day';
            } else {
                $unit = 'month';
            }
            $num = $v['cycletimes'];
            $endDate = date("Y-m-d", strtotime("+$num $unit", strtotime($v['executedate'])));
            $endDate = date("Y-m-d", strtotime("-1 day", strtotime($endDate)));
            $patrol[$k]['frequency'] = $v['releaseNum'] . '/' . $v['cycletimes'];
            $patrol[$k]['date'] = $v['executedate'] . ' 至 ' . $endDate;
            if ($Release) {
                $html = '<a class="Release" style="color: #00a6c8;" title="发布&修订" href="javascript:void(0)" data-url="' . $Release['actionurl'] . '">' . $Release['actionname'] . '</a>';
            }
            $patrol[$k]['operation'] = $html;
        }
        $res['total'] = $total;
        $res['offset'] = $offset;
        $res['limit'] = $limit;
        $res['patrol'] = $patrol;
        return $res;
    }

    /*
     * 查询所有对应权限人
     * @params1 $controller string 模块名称
     * @params2 $action string 方法名称
     * @params3 $includeAdmin int 是否包含超级管理员
     * return array()
     */
    public function getAllUserByPrivilege($controller, $action, $includeAdmin = 1)
    {
        $menuid = $this->DB_get_one('menu', 'menuid', array('controller' => $controller, 'action' => $action));
        $fields = "sb_user.username,sb_user.telephone";
        $join[0] = " LEFT JOIN __USER_ROLE__ ON A.roleid = sb_user_role.roleid ";
        $join[1] = " LEFT JOIN __USER__ ON sb_user_role.userid = sb_user.userid ";
        $res = $this->DB_get_all_join('role_menu', 'A', $fields, $join, array('A.menuid' => $menuid['menuid']), 'username', '', '');
        if ($includeAdmin) {
            $admin = $this->DB_get_all('user', 'username,telephone', array('is_super' => 1));
            foreach ($admin as $k => $v) {
                $res[] = $v;
            }
        }
        return $res;
    }

    /*
     * 获取计划详情
     * @parmas $patrid int 计划ID
     * return $res array
     */

    public function getPlanInfo($patrid)
    {
        return $this->DB_get_one('patrol_plan', '*', array('patrid' => $patrid));
    }

    /*
     * 根据计划ID获取设备包信息
     * @params $patrid int 计划ID
     * return array()
     */
    public function getPackInfoByPatrid($patrid)
    {
        $tableName2 = C('DB_PREFIX') . "patrol_plan_assets_pack";
        $fields = "$tableName2.*";
        $join[0] = " LEFT JOIN __PATROL_PLAN_ASSETS_PACK__ ON A.packid = $tableName2.packid";
        $packinfo = $this->DB_get_one_join('patrol_plan', 'A', $fields, $join, array('A.patrid' => $patrid));
        return $packinfo;
    }

    /*
     * 获取周期计划记录
     * @params $patrid int 计划ID
     * return array();
     */
    public function getPlanCycleInfo($patrid)
    {
        $res = $this->DB_get_all('patrol_plan_cycle', '*', array('patrid' => $patrid), '', 'cycid asc', '');
        return $res;
    }


    //列表页链接拼接
    function returnALink($name, $url, $class, $color = '#428bca', $string = '')
    {
        return '<a style="color:' . $color . ';" class="titlecolor ' . $class . '" href="javascript:void(0)" ' . $string . ' data-url="' . $url . '">' . $name . '</a>';
    }


    public function getAllAssets($patrid, $period)
    {
        return $this->DB_get_all('patrol_plan_cycle', 'executor,plan_assnum', array('patrid' => $patrid, 'period' => $period));
    }

    public function getAllCycleAssets($assnums)
    {
        $join = 'LEFT JOIN sb_assets_contract as B ON A.acid=B.acid';
        $fileds = 'A.assid,A.assnum,A.assets,A.brand,A.assorignum,A.serialnum,A.catid,A.model,A.departid,A.status,A.is_firstaid,A.is_special,A.is_metering,
            A.is_qualityAssets,A.buy_price,A.opendate,B.buy_date,A.guarantee_date';
        $asArr = $this->DB_get_all_join('assets_info', 'A', $fileds, $join, array('A.assnum' => array('IN', $assnums)), '', 'A.assid asc');
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($asArr as &$one) {
            if (!$showPrice) {
                $one['buy_price'] = '***';
            }
            $one['guarantee_date'] = HandleEmptyNull($one['guarantee_date']);
            $one['opendate'] = HandleEmptyNull($one['opendate']);
        }


        return $asArr;
    }

    /*
     * 获取周期计划中特定期次的信息
     * @parmas1 int $partid 计划ID
     * @params2 int $period 期次编号
     * return array()
     */
    public function getPlanCycleInfoByPatridAndPeriod($patrid, $period)
    {
        return $this->DB_get_one('patrol_plan_cycle', 'startdate,overdate,period,cyclenum', array('patrid' => $patrid, 'period' => $period));
    }

    /*
     * 获取计划执行开始时间
     * @params1 $patrid int 计划ID
     * return array
     */
    public function getPlanStartDay($patrid)
    {
        return $this->DB_get_one('patrol_plan', 'executedate', array('patrid' => $patrid));
    }

    /*
     * 查询当前计划是否已完成
     * @patrid int 计划ID
     * return boolen
     */
    public function checkPlanIsComplete($patrid)
    {
        $res = $this->DB_get_one('patrol_plan_cycle', 'cycid', array('patrid' => $patrid, 'status' => array('lt', 2)));
        if (!$res) {
            //已完成
            return true;
        }
        return false;
    }

    /*
    查询到期提醒时间
     */
    public function plan_remind_time()
    {
        $data = $this->DB_get_one('base_setting', 'value', array('set_item' => 'patrol_soon_expire_day'));
        return $data['value'];
    }

    /*
    获取即将到期的设备id
     */
    public function expiring()
    {
        $where['(UNIX_TIMESTAMP(pre_maintain_date)+patrol_pm_cycle*86400-UNIX_TIMESTAMP(NOW()))/86400'] = array('ELT', $this->plan_remind_time());
        $where['(UNIX_TIMESTAMP(pre_patrol_date)+patrol_xc_cycle*86400-UNIX_TIMESTAMP(NOW()))/86400'] = array('ELT', $this->plan_remind_time());
        $where['_logic'] = 'OR';
        $data = $this->DB_get_one('assets_info', 'GROUP_CONCAT(assid) as assids', $where);
        return $data['assids'];
    }

    /**
     * 设备保养记录查询
     */
    public function getRecordSearchListData()
    {
        $departids = session('departid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'DESC';
        $sort = I('post.sort') ? I('post.sort') : 'assid';
        $where = [];
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['departid'] = ['in', $departids];
        //搜索设备名称
        $assets = I('post.assets');
        if ($assets) {
            $where['assets'] = ['like', '%' . $assets . '%'];
        }
        //搜索设备编号
        $assnum = I('post.assnum');
        if ($assnum) {
            $where['assnum'] = $assnum;
        }
        //搜索设备原编码
        $assorignum = I('post.assorignum');
        if ($assorignum) {
            $where['assorignum'] = $assorignum;
        }
        //搜索科室id
        $departid = I('post.departid');
        if ($departid) {
            $where['departid'] = ['IN', $departid];
        }
        //搜索执行人userid
        $executor = I('post.executor');
        //搜索保养等级
        $partrol_level = I('post.level');
        //搜索巡查周期
        $patrolCycle = I('post.patrolCycle');
        if ($patrolCycle) {
            $where['patrol_xc_cycle'] = $patrolCycle;
        }
        //搜索保养周期
        $maintainCycle = I('post.maintainCycle');
        if ($maintainCycle) {
            $where['patrol_pm_cycle'] = $maintainCycle;
        }
        //搜索 即将到期  1是  2否  0全部
        $exceptOverDate = I('post.exceptOverDate');
        if ($exceptOverDate) {
            $assids = $this->expiring();
            if ($assids == "") {
                $assids = 'A';
            }
            if ($exceptOverDate == 1) {
                $where['assid'] = array('IN', $assids);
            } else {
                $where['assid'] = array('NOT IN', $assids);
            }
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";

        //临时读取数据
        $fields = 'assid,assets,assnum,assorignum,model,departid,patrol_xc_cycle,patrol_pm_cycle,patrol_nums,maintain_nums,patrol_dates,maintain_dates,pre_patrol_executor,pre_patrol_result,pre_maintain_result,pre_maintain_date';
        $where['is_delete'] = '0';
        $total = $this->DB_get_count('assets_info', $where);
        $patrolRecordData = $this->DB_get_all('assets_info', $fields, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($patrolRecordData as $k => $v) {
            $patrolRecordData[$k]['department'] = $departname[$v['departid']]['department'];
            $patrolRecordData[$k]['operation'] = $this->returnListLink('查看', C('ADMIN_NAME') . '/PatrolRecords/getPatrolRecords', 'showPatrolRecord', C('BTN_CURRENCY') . ' layui-btn-xs layui-btn-normal');
            //即将到期天数=上次时间+周期-当前时间>提醒时间
            if (!isset($patrolRecordData[$k]['pre_maintain_date'])) {
                $patrolRecordData[$k]['cday'] = "";
            } else {
                $patrolRecordData[$k]['cday'] = (strtotime($patrolRecordData[$k]['pre_maintain_date']) + $patrolRecordData[$k]['patrol_pm_cycle'] * 86400 - strtotime(date('Y-m-d', time()))) / 86400;
            }
            if ($v['patrol_dates']) {
                $patrolRecordData[$k]['patrol_dates_all'] = implode("&#10;", json_decode($v['patrol_dates'], true));
                $patrolRecordData[$k]['patrol_dates'] = json_decode($v['patrol_dates'], true)[0];
            }
            if ($v['maintain_dates']) {
                $patrolRecordData[$k]['maintain_dates_all'] = implode("&#10;", json_decode($v['maintain_dates'], true));
                $patrolRecordData[$k]['maintain_dates'] = json_decode($v['maintain_dates'], true)[0];
            }

        }
        if (!$patrolRecordData) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $result['time'] = $this->plan_remind_time();
        $result["total"] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["rows"] = $patrolRecordData;
        $result["code"] = 200;
        return $result;
    }

    /*
    测试报告数据
     */
    public function getRecordData($assnum, $cycid)
    {
        if (!$cycid || !$assnum) {
            return [];
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assetsInfo = $this->DB_get_one('assets_info', '*', ['assnum' => $assnum]);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assetsInfo['buy_price'] = '***';
        }
        $assetsInfo['department'] = $departname[$assetsInfo['departid']]['department'];
        $assetsInfo['expiring'] = $assetsInfo['patrol_pm_cycle'] - (strtotime(date('y-m-d', time())) - strtotime($assetsInfo['pre_maintain_date'])) / 86400;
        $assetsInfo['expiring'] = $assetsInfo['expiring'] > 0 ? $assetsInfo['expiring'] : "";
        $execute_data = $this->DB_get_one('patrol_execute', 'execid,report_num,cycid', array('assetnum' => $assnum, 'cycid' => $cycid));

        $join[0] = 'LEFT JOIN sb_patrol_execute_abnormal AS B ON A.execid = B.execid';
        $join[1] = 'LEFT JOIN sb_patrol_points AS C ON C.ppid = B.ppid';
        $join[2] = 'LEFT JOIN sb_patrol_plans_cycle AS D ON A.cycid = D.cycid';
        $join[3] = 'LEFT JOIN sb_patrol_plans AS E ON D.patrid = E.patrid';
        $data = $this->DB_get_all_join('patrol_execute', 'A', 'C.name,B.result,A.finish_time,A.execute_user,B.abnormal_remark,A.asset_status_num,A.reason,E.patrid as max_patrid', $join, array('A.assetnum' => $assnum, 'A.cycid' => $cycid), 'B.ppid');

        $join = array();
        $join[0] = 'LEFT JOIN sb_patrol_plans_cycle AS B ON A.patrid = B.patrid';
        $join[1] = 'LEFT JOIN sb_patrol_examine_all AS C ON A.patrid = C.patrid';
        $plan_data = $this->DB_get_one_join('patrol_plans', 'A', 'B.patrol_num,A.patrol_name,A.add_user,A.is_cycle,A.total_cycle,B.period,B.cycle_start_date,B.cycle_end_date,A.remark,A.patrid,A.patrid as max_patrid,B.cycid,C.exam_user,C.exam_time,C.status', $join, array('B.cycid' => $cycid), 'A.patrid');
        $plan_data['execute_user'] = $data[0]['execute_user'];
        $plan_data['finish_time'] = $data[0]['finish_time'];
        $plan_data['cycle_name'] = $plan_data['is_cycle'] ? '是' : '否';
        if ($plan_data['status'] == '0') {
            $plan_data['exam_status'] = '未验收';
        } else if ($plan_data['status'] == '1') {
            $plan_data['exam_status'] = '已验收';
        }
        foreach ($data as $key => $value) {
            if ($value['asset_status_num'] == '7') {
                $plan_data['reason'] = $value['reason'];
            }
            $data[$key]['key'] = $key + 1;
        }
        $assetsInfo['report_num'] = $execute_data['report_num'];
        $assetsInfo['data'] = $data;
        $assetsInfo['plan_data'] = $plan_data;
        return $assetsInfo;
    }

    /*
    根据计划id获取数据
     */
    public function getRecordDatas($assnum, $cycids)
    {
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assetsInfo = $this->DB_get_one('assets_info', '*', ['assnum' => $assnum]);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assetsInfo['buy_price'] = '***';
        }
        $assetsInfo['department'] = $departname[$assetsInfo['departid']]['department'];
        $assetsInfo['expiring'] = $assetsInfo['patrol_pm_cycle'] - (strtotime(date('y-m-d', time())) - strtotime($assetsInfo['pre_maintain_date'])) / 86400;
        $assetsInfo['expiring'] = $assetsInfo['expiring'] > 0 ? $assetsInfo['expiring'] : "";
        $execute_data = $this->DB_get_one('patrol_execute', 'max(report_num) as report_num,max(cycid) as cycid', array('assetnum' => $assnum, 'cycid' => array('IN', $cycids)));
        $join[0] = 'LEFT JOIN sb_patrol_execute_abnormal AS B ON A.execid = B.execid';
        $join[1] = 'LEFT JOIN sb_patrol_points AS C ON C.ppid = B.ppid';
        $data = $this->DB_get_all_join('patrol_execute', 'A', 'C.name,B.result,B.abnormal_remark,A.asset_status_num,A.reason,A.execute_user,A.finish_time', $join, array('A.assetnum' => $assnum, 'A.cycid' => $execute_data['cycid']), 'B.ppid');

        $join = 'LEFT JOIN sb_patrol_plans_cycle AS B ON A.patrid = B.patrid';
        $plan_data = $this->DB_get_one_join('patrol_plans', 'A', 'A.patrid,A.is_cycle,B.patrol_num,A.patrol_name,A.add_user,A.total_cycle,B.period,B.cycle_start_date,B.cycle_end_date,A.remark,B.cycid', $join, array('B.cycid' => $execute_data['cycid']), '');
        $plan_data['execute_user'] = $data[0]['execute_user'];
        $plan_data['finish_time'] = $data[0]['finish_time'];
        $plan_data['cycle_name'] = $plan_data['is_cycle'] ? '是' : '否';
        //查询验收信息
        $check_data = $this->DB_get_one('patrol_examine_all', 'exam_user,exam_time,remark', array('patrid' => $plan_data['patrid'], 'cycid' => $plan_data['cycid']));
        $plan_data['exam_status'] = '未验收';
        if ($check_data) {
            $plan_data['exam_status'] = '已验收';
            $plan_data['exam_user'] = $check_data['exam_user'];
            $plan_data['exam_time'] = $check_data['exam_time'];
            $plan_data['exam_remark'] = $check_data['remark'];
        }
        foreach ($data as $key => $value) {
            if ($value['asset_status_num'] == '7') {
                $plan_data['reason'] = $value['reason'];
            }
            $data[$key]['key'] = $key + 1;
        }
        $assetsInfo['report_num'] = $execute_data['report_num'];
        $assetsInfo['data'] = $data;
        $assetsInfo['plan_data'] = $plan_data;
        return $assetsInfo;
    }

    /**
     * 设备保养记录查询 详情
     */

    public function showPatrolRecordData()
    {
        $result = [];
        //先组织设备的信息
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assid = I('get.assid');
        $assetsInfo = $this->DB_get_one('assets_info', 'assnum,assorignum,model,departid,patrol_xc_cycle,patrol_pm_cycle', ['assid' => $assid]);
        $assetsInfo['department'] = $departname[$assetsInfo['departid']]['department'];
        $result['assetsInfo'] = $assetsInfo;
        //根据设备号查询巡查记录
        return $result;
    }

//    /**
//     * 设备保养记录查询 详情
//     */
//
//    public function get_patrol_records($assnums)
//    {
//        $result = [];
//        //先组织设备的信息
//        $departname = [];
//        include APP_PATH . "Common/cache/department.cache.php";
//        $where['hospital_id'] = session('current_hospitalid');
//        $where['is_delete'] = C('NO_STATUS');
//        $where['assnum'] = array('in', $assnums);
//        $data = $this->DB_get_all('assets_info', 'assnum,assorignum,model,departid,patrol_xc_cycle,patrol_pm_cycle', $where);
//        foreach ($data as &$v) {
//            $v['department'] = $departname[$v['departid']]['department'];
//        }
//        foreach ($data as &$v) {
//            //组织巡查信息
//            $fields = 'A.patrolnum,A.patrolname,B.executor,C.finish_time';
//            $join[0] = 'LEFT JOIN sb_patrol_plan_cycle AS B ON A.patrid = B.patrid';
//            $join[1] = 'LEFT JOIN sb_patrol_execute AS C ON B.cycid = C.cycid';
//            $patrolInfo = $this->DB_get_all_join('patrol_plan', 'A', $fields, $join, 'FIND_IN_SET(' . $v['assnum'] . ',A.patrol_assnums) and B.status = 2');
//            $result['assetsInfo'] = $assetsInfo;
//            $result['patrolInfo'] = $patrolInfo;
//        }
//        return $result;
//    }
//
    //获取计划保养设备数量
    public function getPlanNum($hospital_id, $start, $end)
    {
        $where['is_delete'] = C('NO_STATUS');
        $where['hospital_id'] = $hospital_id;
        $where['executedate'] = array(array('egt', date("Y-m-d", $start)), array('elt', date("Y-m-d", $end)), 'and');
        //需要的字段
        $fields = 'patrid';
        //获取数据
        $patrol = $this->DB_get_all('patrol_plan', $fields, $where);
        $assetsNum = 0;
        foreach ($patrol as $k => $v) {
            //查询计划总台数
            $countNum = $this->DB_get_one('patrol_plan_cycle', 'group_concat(plan_assnum) as plan_assnum', array('patrid' => $v['patrid']));
            $countNum['plan_assnum'] = str_replace('[', '', $countNum['plan_assnum']);
            $countNum['plan_assnum'] = str_replace(']', '', $countNum['plan_assnum']);
            $countNum['plan_assnum'] = explode(',', $countNum['plan_assnum']);
            $patrol[$k]['planAssetsNum'] = count($countNum['plan_assnum']);
            $assetsNum += $patrol[$k]['planAssetsNum'];
        }

        return $assetsNum;
    }
}
