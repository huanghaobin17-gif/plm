<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Think\Model;
use Admin\Model\ModuleModel;

class PatrolExecuteModel extends CommonModel
{
    protected $tableName = 'patrol_execute';
    protected $tableFields = 'execid,cycid,assetnum,asset_status,finish_time,remark,is_torepair,torepair_time,applicant,userid';
    protected $Controller = 'Patrol';


    //确认本期实施完成
    public function doTask()
    {
        $idstr = I('POST.cycid');
        $patrol_level = I('POST.patrol_level');
        $where['cycid'] = array('in', $idstr);
        if (!session('isSuper')) {
            $where['executor'] = session('username');
        }
        //查询当前执行人所属的cycid
        $curExecutor = $this->DB_get_all('patrol_plan_cycle', 'cycid,cyclenum,plan_assnum', $where);
        if ($curExecutor) {
            foreach ($curExecutor as &$v) {
                $assnum = json_decode($v['plan_assnum']);
                $this->checkexecutor($v['cycid']);
                $this->notPatrolNum($assnum, $v['cycid'], $patrol_level);
                $this->completeCycle($v['cycid']);
                $this->addCheckData($v['cycid']);
                //日志行为记录文字
                $log['cyclenum'] = $v['cyclenum'];
                $text = getLogText('doTaskPatrolLogText', $log);
                $this->addLog('patrol_plan_cycle', M()->getLastSql(), $text, $v['cycid']);
            }
            return array('status' => 1, 'msg' => '已完成并且保存成功');
        } else {
            return array('status' => -99, 'msg' => '您不是计划执行人！');
        }
    }

    //录入保养情况
    public function setSituation()
    {
        $ppid = I('post.ppid');
        $userid = I('POST.userid');
        $applicant = I('POST.applicant');
        $result = I('post.result');
        $abnormal_remark = I('post.abnormal_remark');
        $asset_status = I('POST.asset_status');
        $remark = I('POST.remark');
        $reason = I('POST.reason');
        $assetnum = I('POST.assetnum');
        $cycid = I('POST.cycid');
        $executeStatus = I('POST.executeStatus');
        $complete_time = I('POST.complete_time');
        $this->checkstatus(judgeEmpty($ppid), '非法操作');
        $this->checkstatus(judgeEmpty($cycid), '非法操作');
        $this->checkstatus(judgeEmpty($assetnum), '非法操作');
        $this->checkstatus(judgeEmpty($result), '非法操作');
        $this->checkstatus(judgeEmpty($abnormal_remark), '非法操作');
        if ($executeStatus == C('MAINTAIN_COMPLETE')) {
            $this->checkstatus(judgeNum($asset_status) && $asset_status > 0 && $asset_status <= C('ASSETS_STATUS_NOT_OPERATION'), '非法操作');
            if ($asset_status == C('ASSETS_STATUS_NOT_OPERATION')) {
                if ($reason == '') {
                    return array('status' => -91, 'msg' => '请输入该设备不进行保养的原因');
                }
            }
        }
        //新增检测数据的类型
        $ppid = json_decode($ppid) == null ? $ppid : json_decode($ppid);
        $result = json_decode($result) == null ? $result : json_decode($result);
        $abnormal_remark = json_decode($abnormal_remark) == null ? $abnormal_remark : json_decode($abnormal_remark);
        $ppid = explode(',', $ppid);
        $result = explode(',', $result);
        $abnormal_remark = explode(',', $abnormal_remark);
        $abnormalData['result'] = $result;
        $abnormalData['abnormal_remark'] = $abnormal_remark;
        $abnormalData['ppid'] = $ppid;
        $executeData['cycid'] = $cycid;
        $executeData['assetnum'] = $assetnum;
        $executeData['asset_status'] = $asset_status;
        $executeData['remark'] = $remark;
        $executeData['reason'] = $reason;
        $executeData['applicant'] = $applicant;
        $executeData['userid'] = $userid;
        $executeData['executeStatus'] = $executeStatus;//1暂存 2完成
        if($complete_time){
            //逾期做保养的，可以选择完成时间
            $executeData['finish_time'] = $complete_time;
        }else{
            $executeData['finish_time'] = date('Y-m-d H:i:s');
        }
        $do = $this->addexecute($executeData, $abnormalData);
        if ($executeStatus == C('MAINTAIN_PATROL')) {
            $text = '暂存成功';
            $textError = '暂存失败';
        } else {
            switch ($asset_status) {
                case C('ASSETS_STATUS_NORMAL')://1
                case C('ASSETS_STATUS_SMALL_PROBLEM')://2
                case C('ASSETS_STATUS_IN_MAINTENANCE')://5
                case C('ASSETS_STATUS_SCRAPPED')://6
                    $text = '保存成功，请继续下一台设备';
                    $textError = '保存失败';
                    break;
                case C('ASSETS_STATUS_FAULT'):
                case C('ASSETS_STATUS_ABNORMAL'):
                    $text = "当前故障设备已通知科室设备管理员:$applicant,将由其确认并报修!";
                    $textError = '设备报修失败';
                    break;
                case C('ASSETS_STATUS_NOT_OPERATION'):
                    $text = '该设备已经跳过，请继续下一台设备';
                    $textError = '结束失败';
                    break;
                default:
                    $text = '异常参数';
                    $textError = '异常参数';
                    break;
            }
        }
        if ($do) {
            $PatrolPlanCycleModel = new PatrolPlanCycleModel();
            $PatrolPlanCycleModel->updateInExecution($cycid, $executeStatus, $result, $asset_status, $assetnum,$executeData['finish_time']);
            return array('status' => 1, 'msg' => $text);
        } else {
            return array('status' => -99, 'msg' => $textError);
        }
    }

    //一键保养
    public function batch_maintain()
    {
        $cycid = I('post.cycid');
        $finish_time = I('post.complete_time');//逾期的任务可以选择完成时间，不逾期的不存在此参数
        $assnum = json_decode($_POST['assnum']);
        if(!$cycid || !$assnum){
            return array('status' => -1, 'msg' => '参数有误！');
        }
        if(!$finish_time){
            $finish_time = date('Y-m-d H:i:s');
        }
        //查询patrid
        $cycleInfo = $this->DB_get_one('patrol_plans_cycle','patrid,sign_info,cycle_status',['cycid'=>$cycid]);
        if(in_array($cycleInfo['cycle_status'],[2,3])){
            return array('status' => -1, 'msg' => '该计划已完成，请勿重复提交');
        }
        //判断是否开启签到
        $patrol_wx_set_situation = $this->DB_get_one('base_setting', '*', array('module' => 'patrol', 'set_item' => 'patrol_wx_set_situation'));
        $wx_sign_in = $patrol_wx_set_situation['value'];
        if($wx_sign_in){
            //开启了签到保养功能
            $sign_assnums = json_decode($cycleInfo['sign_info'], true);
            //判断是否均已签到
            foreach ($assnum as $k=>$v){
                if (!isset($sign_assnums[$v])) {
                    return array('status' => -1, 'msg' => '开启了签到保养，请先签到再保养');
                }
            }
        }

        $plan_data = $this->DB_get_one('patrol_plans','patrid,patrol_level',['patrid'=>$cycleInfo['patrid']]);
        $Cycle = M("patrol_plans_cycle");
        foreach ($assnum as $v){
            $tpid = $this->DB_get_one('patrol_plans_assets','assnum_tpid',['patrid'=>$cycleInfo['patrid'],'assnum'=>$v]);
            $points_num = $this->DB_get_one('patrol_template','points_num',['tpid'=>$tpid['assnum_tpid']]);
            $ppid = $this->DB_get_all('patrol_points','ppid',['num'=>['in',json_decode($points_num['points_num'])]]);
            //查询保养信息
            $execInfo = $this->DB_get_one('patrol_execute','execid',['cycid'=>$cycid,'assetnum'=>$v]);

            //保存保养信息
            $exec_data['asset_status_num'] = 1;//工作正常
            $exec_data['asset_status'] = '工作正常';//工作正常
            $exec_data['finish_time'] = $finish_time;
            $exec_data['execute_user'] = session('username');
            $exec_data['status'] = 2;//已巡查
            $exec_data['remark'] = '一键保养设备，暂无改善建议';
            $exec_data['report_num'] = $this->get_assnum_patrol_report_num($plan_data,$v);
            if($execInfo){
                //原来暂存的，修改为完成
                $this->updateData('patrol_execute',$exec_data,['execid'=>$execInfo['execid']]);
                //更新保养项明细
                foreach ($ppid as $pv){
                    $abnormal_data['result'] = '合格';
                    $abnormal_data['abnormal_remark'] = '';
                    $this->updateData('patrol_execute_abnormal',$abnormal_data,['execid'=>$execInfo['execid'],'ppid'=>$pv['ppid']]);
                }
            }else{
                //没有保养信息，新增一条
                $exec_data['cycid'] = $cycid;
                $exec_data['assetnum'] = $v;
                $execid = $this->insertData('patrol_execute',$exec_data);
                //保存保养项明细
                foreach ($ppid as $pv){
                    $abnormal_data['execid'] = $execid;
                    $abnormal_data['ppid'] = $pv['ppid'];
                    $abnormal_data['result'] = '合格';
                    $abnormal_data['abnormal_remark'] = '';
                    $this->insertData('patrol_execute_abnormal',$abnormal_data);
                }
            }
        }
        //查询已完成保养的设备数量
        $over_num = $this->DB_get_count('patrol_execute',['cycid'=>$cycid,'status'=>2]);
        //更新已完成数量
        $newUpdate['implement_sum'] = $over_num;

        $newCycle = $this->DB_get_one('patrol_plans_cycle','*',['cycid'=>$cycid]);
        if($newCycle['assets_nums'] == $over_num){
            //已完成
            $newUpdate['complete_time'] = $finish_time;
            if(date('Y-m-d',strtotime($finish_time)) > $newCycle['cycle_end_date']){
                //逾期完成
                $newUpdate['cycle_status'] = 3;
            }else{
                //按期完成
                $newUpdate['cycle_status'] = 2;
            }
        }else{
            if(date('Y-m-d',strtotime($finish_time)) > $newCycle['cycle_end_date']){
                //逾期未完成
                $newUpdate['cycle_status'] = 4;
            }else{
                //执行中
                $newUpdate['cycle_status'] = 1;
            }
        }
        $this->updateData('patrol_plans_cycle',$newUpdate,['cycid'=>$cycid]);

        if(in_array($newUpdate['cycle_status'],[2,3])){
            $cycleModel = new PatrolPlanCycleModel();
            //已完成的计划生成计划编码 如DC RC 20190813-2
            $cycleModel->create_patrol_num($plan_data['patrol_level'],$cycid);
            //计划完成，发送验收提醒
            $cycleModel->send_check_patrol_message($cycid);
        }
        return array('status' => 1, 'msg' => '一键保养成功！');
    }

    public function get_assnum_patrol_report_num($plan_data,$assnum)
    {
        $assinfo = $this->DB_get_one('assets_info', 'patrol_nums,maintain_nums,patrol_dates,maintain_dates', array('assnum' => $assnum));
        if ($plan_data['patrol_level'] == '1' || $plan_data['patrol_level'] == '2') {
            $update_assets_data['pre_maintain_date'] = date('Y-m-d');//上一次保养日期
            $update_assets_data['pre_maintain_executor'] = session('username');//上一次保养执行人
            $update_assets_data['pre_maintain_result'] = '正常';//上一次保养结果
            $maintain_dates = json_decode($assinfo['maintain_dates'], true);
            $maintain_dates[] = date('Y-m-d');
            $maintain_dates = array_unique($maintain_dates);
            $update_assets_data['maintain_dates'] = json_encode($maintain_dates);//历史保养日期
            $update_assets_data['maintain_nums'] = $assinfo['maintain_nums'] + 1;//总保养次数
        } else {
            //三级的属于巡查次数
            $update_assets_data['pre_patrol_date'] = date('Y-m-d');//上一次巡查日期
            $update_assets_data['pre_patrol_executor'] = session('username');//上一次巡查执行人
            $update_assets_data['pre_patrol_result'] = '正常';//上一次巡查结果
            $patrol_dates = json_decode($assinfo['patrol_dates'], true);
            $patrol_dates[] = date('Y-m-d');
            //去重
            $patrol_dates = array_unique($patrol_dates);
            $update_assets_data['patrol_dates'] = json_encode($patrol_dates);//历史巡查日期
            $update_assets_data['patrol_nums'] = $assinfo['patrol_nums'] + 1;//总巡查次数
        }
        if ($plan_data['patrol_level'] == '1') {
            $patrol_level = 'DC';
        } else if ($plan_data['patrol_level'] == '2') {
            $patrol_level = 'RC';
        } else {
            $patrol_level = 'PM';
        }
        $max_data = $this->DB_get_one('patrol_execute', 'max(report_num) as max', array('report_num' => array('like', $patrol_level . date('Y') . '%')));
        if (!$max_data) {
            $report_num = $patrol_level . date('Ymd') . '-0001';
        } else {
            $report_num = $patrol_level . date('Ymd') . '-' . sprintf('%04s', substr($max_data['max'], strpos($max_data['max'], "-") + 1) + 1);
        }
        if ($update_assets_data) {
            //更新设备信息
            $this->updateData('assets_info', $update_assets_data, array('assnum' => $assnum));
        }
        return $report_num;
    }

    //对正在维修和已报废的设备自动补充明细
    public function setAssetAbnormal()
    {
        $cycid = I('POST.cycid');
        $assetnum = I('POST.assnum');
        $assetStatus = I('POST.status');
        $arr_num = $_POST['arr_num'];
        if ($cycid > 0 && $assetnum && $arr_num) {
            $this->checkexecutor($cycid);
            switch ($assetStatus) {
                case C('ASSETS_STATUS_REPAIR'):
                    $asset_status = C('ASSETS_STATUS_IN_MAINTENANCE');
                    $text = '该设备正在维修';
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $asset_status = C('ASSETS_STATUS_SCRAPPED');
                    $text = '该设备已报废';
                    break;
                default:
                    $text = '异常参数';
            }
            $arr_num = str_replace(array('"'), "", $arr_num);
            $arr_num = trim($arr_num, ']');
            $arr_num = trim($arr_num, '[');
            $points = $this->getPointsArr($arr_num);
            $result = $abnormal_remark = $ppid = [];
            foreach ($points as &$pointValue) {
                $result[] = '合格';
                $ppid[] = $pointValue['ppid'];
                $abnormal_remark[] = '';
            }
            $abnormalData['result'] = $result;
            $abnormalData['abnormal_remark'] = $abnormal_remark;
            $abnormalData['ppid'] = $ppid;
            $executeData['cycid'] = $cycid;
            $executeData['assetnum'] = $assetnum;
            $executeData['asset_status'] = $asset_status;
            $executeData['remark'] = '';
            $executeData['applicant'] = '';
            $executeData['userid'] = '';
            $executeData['executeStatus'] = C('MAINTAIN_COMPLETE');
            $do = $this->addexecute($executeData, $abnormalData);
            if ($do) {
                $PatrolPlanCycleModel = new PatrolPlanCycleModel();
                $PatrolPlanCycleModel->updateInExecution($cycid, $executeData['executeStatus'], $result, $asset_status, $assetnum);
                return array('status' => 1, 'msg' => $text);
            } else {
                return array('status' => -99, 'msg' => '失败');
            }
        } else {
            return array('status' => -999, 'msg' => '参数错误');
        }
    }

    /*
     * 保存设备巡查结果与明细
     * @params1 $executeData array 实施详情
     * @params2 $abnormalData array 巡查项详情
     * return ID or false
     */
    public function addexecute($executeData, $abnormalData)
    {
        $cycid = $executeData['cycid'];
        $assetnum = $executeData['assetnum'];
        $asset_status = $executeData['asset_status'];
        $remark = $executeData['remark'];
        $applicant = $executeData['applicant'];
        $userid = $executeData['userid'];
        $executeStatus = $executeData['executeStatus'];
        $finish_time = $executeData['finish_time'];
        $result = $abnormalData['result'];
        $reason = $executeData['reason'];
        $abnormal_remark = $abnormalData['abnormal_remark'];
        $ppid = $abnormalData['ppid'];
        $data = $this->DB_get_one('patrol_execute', "execid,status", "cycid=$cycid AND assetnum='$assetnum'");
        $update_assets_data = [];//保存需要更新sb_assets_info 的字段
        $result_name = '';//保养结果文字描述
        //查询保养级别
        $patrid = $this->DB_get_one('patrol_plans_cycle', 'patrid', ['cycid'=>$cycid]);
        $plan_data = $this->DB_get_one('patrol_plans', 'patrol_level', ['patrid'=>$patrid['patrid']]);
        if (!$data) {
            //没有提交过 添加操作
            if ($executeStatus == C('MAINTAIN_COMPLETE')) {
                //完成状态
                //$asset_status!=7 加报告编号  报告编码PM20190606-0001  过一年从0001重新开始
                switch ($asset_status) {
                    //可使用
                    case C('ASSETS_STATUS_NORMAL')://1--工作正常
                        $addExecute['asset_status'] = C('ASSETS_STATUS_NORMAL_NAME');
                        $result_name = '正常';
                        break;
                    case C('ASSETS_STATUS_SMALL_PROBLEM')://2--有小问题，但不影响使用
                        $addExecute['asset_status'] = C('ASSETS_STATUS_SMALL_PROBLEM_NAME');
                        $result_name = '正常';
                        break;
                    //转报修
                    case C('ASSETS_STATUS_FAULT')://3--有故障，需要进一步维修
                    case C('ASSETS_STATUS_ABNORMAL')://4--无法正常使用
                        if ($asset_status == C('ASSETS_STATUS_FAULT')) {
                            $addExecute['asset_status'] = C('ASSETS_STATUS_FAULT_NAME');
                        } else {
                            $addExecute['asset_status'] = C('ASSETS_STATUS_ABNORMAL_NAME');
                        }
                        if (!is_numeric($userid) or $userid <= 0) {
                            die(json_encode(array('status' => -99, 'msg' => '请选择以谁的名义报修')));
                        }
                        if (!$applicant) {
                            die(json_encode(array('status' => -98, 'msg' => '请选择以谁的名义报修')));
                        }
                        $addExecute['userid'] = $userid;
                        $addExecute['applicant'] = $applicant;
                        $addExecute['is_torepair'] = C('ASSETS_TO_REPAIR');
                        $addExecute['torepair_time'] = time();
                        $result_name = '异常';
                        break;
                    //设备已报修
                    case C('ASSETS_STATUS_IN_MAINTENANCE')://5--该设备正在维修
                        $addExecute['asset_status'] = C('ASSETS_STATUS_IN_MAINTENANCE_NAME');
                        $result_name = '异常';
                        break;
                    //设备已报废
                    case C('ASSETS_STATUS_SCRAPPED')://6--该设备已报废
                        $addExecute['asset_status'] = C('ASSETS_STATUS_SCRAPPED_NAME');
                        $result_name = '异常';
                        break;
                    //该设备不做保养
                    case C('ASSETS_STATUS_NOT_OPERATION')://7--该设备不做保养
                        $addExecute['asset_status'] = C('ASSETS_STATUS_NOT_OPERATION_NAME');
                        $addExecute['reason'] = $reason;
                        $result_name = '不做保养';
                        break;
                }
                $addExecute['report_num'] = $this->get_assnum_patrol_report_num($plan_data,$assetnum);
                $addExecute['asset_status_num'] = $asset_status;
            }
            $addExecute['status'] = $executeStatus;
            $addExecute['finish_time'] = $finish_time;
            $addExecute['execute_user'] = session('username');
            $addExecute['cycid'] = $cycid;
            $addExecute['assetnum'] = $assetnum;
            $addExecute['remark'] = $remark;
            $execid = $this->insertData('patrol_execute', $addExecute);
            if ($update_assets_data) {
                $this->updateData('assets_info', $update_assets_data, array('assnum' => $assetnum));
            }
            //更新设备表巡查字段信息
            if ($execid) {
                $addData = array();
                $breakdown = '';
                $num = 1;
                foreach ($result as $key => $value) {
                    if ($asset_status == C('ASSETS_STATUS_IN_MAINTENANCE') or $asset_status == C('ASSETS_STATUS_SCRAPPED') or $asset_status == C('ASSETS_STATUS_NOT_OPERATION')) {
                        $addData[$key]['result'] = '合格';
                        $abnormal_remark[$key] = '';
                    } else {
                        $addData[$key]['result'] = $result[$key];
                        if ($abnormal_remark[$key] == '#') {
                            $abnormal_remark[$key] = '';
                        }
                    }
                    if ($abnormal_remark[$key]) {
                        if ($breakdown) {
                            $breakdown .= "\r\n" . $num . "." . $abnormal_remark[$key];
                        } else {
                            $breakdown = $num . "." . $abnormal_remark[$key];
                        }
                        $num++;
                    }
                    $addData[$key]['ppid'] = $ppid[$key];
                    $addData[$key]['execid'] = $execid;
                    $addData[$key]['abnormal_remark'] = $abnormal_remark[$key];
                    $addData[$key]['addtime'] = time();
                }
                if ($asset_status == C('ASSETS_STATUS_FAULT') or $asset_status == C('ASSETS_STATUS_ABNORMAL')) {
                    $ConfirmRepairData['applicant'] = $applicant;
                    $ConfirmRepairData['assnum'] = $assetnum;
                    $ConfirmRepairData['patroluser'] = session('username');
                    $ConfirmRepairData['abnormalText'] = $breakdown;
                    $ConfirmRepairData['cycid'] = $cycid;
                    $ConfirmRepairData['execid'] = $execid;
                    $ConfirmRepairData['asset_status'] = $addExecute['asset_status'];
                    $this->insertData('confirm_add_repair', $ConfirmRepairData);
                }
                return $this->insertDataALL('patrol_execute_abnormal', $addData);
            } else {
                return false;
            }
        } else {
            //提交过
            if ($data['status'] == C('MAINTAIN_COMPLETE')) {
                //状态完成时
                die(json_encode(array('status' => -99, 'msg' => '已完成,请勿重复提交')));
            } else {
                if ($executeStatus == C('MAINTAIN_COMPLETE')) {
                    switch ($asset_status) {
                        //可使用
                        case C('ASSETS_STATUS_NORMAL'):
                            $saveExecute['asset_status'] = C('ASSETS_STATUS_NORMAL_NAME');
                            $result_name = '正常';
                            break;
                        case C('ASSETS_STATUS_SMALL_PROBLEM'):
                            $saveExecute['asset_status'] = C('ASSETS_STATUS_SMALL_PROBLEM_NAME');
                            $result_name = '正常';
                            break;
                        //转报修
                        case C('ASSETS_STATUS_FAULT'):
                        case C('ASSETS_STATUS_ABNORMAL'):
                            if ($asset_status == C('ASSETS_STATUS_FAULT')) {
                                $saveExecute['asset_status'] = C('ASSETS_STATUS_FAULT_NAME');
                            } else {
                                $saveExecute['asset_status'] = C('ASSETS_STATUS_ABNORMAL_NAME');
                            }
                            if (!is_numeric($userid) or $userid <= 0) {
                                die(json_encode(array('status' => -99, 'msg' => '请选择以谁的名义报修')));
                            }
                            if (!$applicant) {
                                die(json_encode(array('status' => -98, 'msg' => '请选择以谁的名义报修')));
                            }
                            $saveExecute['userid'] = $userid;
                            $saveExecute['applicant'] = $applicant;
                            $saveExecute['is_torepair'] = C('ASSETS_TO_REPAIR');
                            $saveExecute['torepair_time'] = time();
                            $result_name = '异常';
                            break;
                        //设备已报修
                        case C('ASSETS_STATUS_IN_MAINTENANCE'):
                            $saveExecute['asset_status'] = C('ASSETS_STATUS_IN_MAINTENANCE_NAME');
                            $result_name = '异常';
                            break;
                        //设备已报废
                        case C('ASSETS_STATUS_SCRAPPED'):
                            $saveExecute['asset_status'] = C('ASSETS_STATUS_SCRAPPED_NAME');
                            $result_name = '异常';
                            break;
                        //该设备不做保养
                        case C('ASSETS_STATUS_NOT_OPERATION'):
                            $saveExecute['asset_status'] = C('ASSETS_STATUS_NOT_OPERATION_NAME');
                            $saveExecute['reason'] = $reason;
                            $result_name = '不做保养';
                            break;
                    }
                    $saveExecute['report_num'] = $this->get_assnum_patrol_report_num($plan_data,$assetnum);
                    $saveExecute['asset_status_num'] = $asset_status;
                }
                switch ($asset_status) {
                    //可使用
                    case C('ASSETS_STATUS_NORMAL'):
                        $saveExecute['asset_status'] = C('ASSETS_STATUS_NORMAL_NAME');
                        break;
                    case C('ASSETS_STATUS_SMALL_PROBLEM'):
                        $saveExecute['asset_status'] = C('ASSETS_STATUS_SMALL_PROBLEM_NAME');
                        break;
                    //转报修
                    case C('ASSETS_STATUS_FAULT'):
                    case C('ASSETS_STATUS_ABNORMAL'):
                        if ($asset_status == C('ASSETS_STATUS_FAULT')) {
                            $saveExecute['asset_status'] = C('ASSETS_STATUS_FAULT_NAME');
                        } else {
                            $saveExecute['asset_status'] = C('ASSETS_STATUS_ABNORMAL_NAME');
                        }
                        if (!is_numeric($userid) or $userid <= 0) {
                            die(json_encode(array('status' => -99, 'msg' => '请选择以谁的名义报修')));
                        }
                        if (!$applicant) {
                            die(json_encode(array('status' => -98, 'msg' => '请选择以谁的名义报修')));
                        }
                        $saveExecute['userid'] = $userid;
                        $saveExecute['applicant'] = $applicant;
                        $saveExecute['is_torepair'] = C('ASSETS_TO_REPAIR');
                        $saveExecute['torepair_time'] = time();
                        break;
                    //设备已报修
                    case C('ASSETS_STATUS_IN_MAINTENANCE'):
                        $saveExecute['asset_status'] = C('ASSETS_STATUS_IN_MAINTENANCE_NAME');
                        break;
                    //设备已报废
                    case C('ASSETS_STATUS_SCRAPPED'):
                        $saveExecute['asset_status'] = C('ASSETS_STATUS_SCRAPPED_NAME');
                        break;
                    //该设备不做保养
                    case C('ASSETS_STATUS_NOT_OPERATION'):
                        $saveExecute['asset_status'] = C('ASSETS_STATUS_NOT_OPERATION_NAME');
                        $saveExecute['reason'] = $reason;
                        break;
                }
                $saveExecute['asset_status_num'] = $asset_status;
                $saveExecute['status'] = $executeStatus;
                $saveExecute['finish_time'] = $finish_time;
                $saveExecute['execute_user'] = session('username');
                $saveExecute['remark'] = $remark;
                $save = $this->updateData('patrol_execute', $saveExecute, "cycid=$cycid AND assetnum='$assetnum'");
                if ($update_assets_data) {
                    $this->updateData('assets_info', $update_assets_data, array('assnum' => $assetnum));
                }
                if ($save) {
                    $saveData = array();
                    $breakdown = '';
                    $num = 1;
                    foreach ($result as $key => $value) {
                        if ($asset_status == C('ASSETS_STATUS_IN_MAINTENANCE') or $asset_status == C('ASSETS_STATUS_SCRAPPED') or $asset_status == C('ASSETS_STATUS_NOT_OPERATION')) {
                            $saveData[$key]['result'] = '合格';
                            $abnormal_remark[$key] = '';
                        } else {
                            $saveData[$key]['result'] = $result[$key];
                            if ($abnormal_remark[$key] == '#') {
                                $abnormal_remark[$key] = '';
                            }
                        }
                        $saveData[$key]['ppid'] = $ppid[$key];
                        $saveData[$key]['execid'] = $data['execid'];
                        $saveData[$key]['abnormal_remark'] = $abnormal_remark[$key];
                        if ($abnormal_remark[$key]) {
                            if ($breakdown) {
                                $breakdown .= "\r\n" . $num . "." . $abnormal_remark[$key];
                            } else {
                                $breakdown = $num . "." . $abnormal_remark[$key];
                            }
                            $num++;
                        }
                        $saveData[$key]['addtime'] = time();
                        $saveabnormal = $this->updateData('patrol_execute_abnormal', $saveData[$key], "ppid=$ppid[$key] AND execid=$data[execid]");

                        if (!$saveabnormal) {
                            $this->deleteData('patrol_execute_abnormal',array('execid'=>$data['execid'],'ppid'=>$ppid[$key]));
                            $this->insertData('patrol_execute_abnormal', $saveData[$key]);
                        }
                    }
                    if ($asset_status == C('ASSETS_STATUS_FAULT') or $asset_status == C('ASSETS_STATUS_ABNORMAL')) {
                        $executeData = $this->DB_get_one('patrol_execute', 'execid,asset_status', "cycid=$cycid AND assetnum='$assetnum'");
                        $execid = $executeData['execid'];
                        $ConfirmRepairData['applicant'] = $applicant;
                        $ConfirmRepairData['assnum'] = $assetnum;
                        $ConfirmRepairData['patroluser'] = session('username');
                        $ConfirmRepairData['abnormalText'] = $breakdown;
                        $ConfirmRepairData['cycid'] = $cycid;
                        $ConfirmRepairData['execid'] = $execid;
                        $ConfirmRepairData['asset_status'] = $executeData['asset_status'];
                        $this->insertData('confirm_add_repair', $ConfirmRepairData);
                    }
                    return $save;
                } else {
                    return false;
                }
            }
        }
    }


    /*
     * 批量添加设备巡查明细结果
     * @params1 $result array 明细选项数组
     * @params2 $abnormal_remark array 明细详情数组
     * @params3 $ppid array 明细ID数组
     * @params4 $execid int 实施ID
     * return ID or false
     */
    public function addAbnormalALL($result, $abnormal_remark, $ppid, $execid)
    {
        $addData = array();
        foreach ($result as $key => $value) {
            $addData[$key]['result'] = $result[$key];
            $addData[$key]['ppid'] = $ppid[$key];
            $addData[$key]['execid'] = $execid;
            if ($abnormal_remark[$key] == '#') {
                $abnormal_remark[$key] = '';
            }
            $addData[$key]['abnormal_remark'] = $abnormal_remark[$key];
            $addData[$key]['addtime'] = time();
        }
        return $this->insertDataALL('patrol_execute_abnormal', $addData);
    }

    /*
     * 获取设备巡查数据
     * @params1 $assetnum string 设备编号
     * @params2 $cycid int 周期ID
     * return array
     */
    public function getRecord($assetnum, $cycid)
    {
        $where = "assetnum='$assetnum' and cycid=$cycid";
        $fields = 'execid,status,asset_status,asset_status_num,finish_time,remark,is_torepair,applicant,reason';
        $data = $this->DB_get_one('patrol_execute', $fields, $where);
        if ($data) {
            if ($data['asset_status_num'] != C('ASSETS_STATUS_NORMAL') or $data['asset_status_num'] != C('ASSETS_STATUS_SMALL_PROBLEM')) {
                $data['asset_status'] = '<span class="rquireCoin">' . $data['asset_status'] . '</span>';
            } else {
                $data['asset_status'] = '<span class="grenn">' . $data['asset_status'] . '</span>';
            }
        }
        return $data;
    }

    /*
     * 获取设备对应明细结果
     * @params1 $execid ID 实施ID
     * return array
     */
    public function getAbnormal($execid)
    {
        $data = $this->DB_get_all('patrol_execute_abnormal', 'ppid,result,abnormal_remark', "execid=$execid");
        return $data;
    }

    /*
     * 将未执行的任务完成
     * @params1 $assnum array 设备编号
     * @params2 $cycId ID 周期ID
     */
    public function notPatrolNum($assnum, $cycId, $patrol_level)
    {
        $where['assetnum'] = array('in', $assnum);
        $where['cycid'] = array('EQ', $cycId);
        $where['status'] = array('EQ', C('MAINTAIN_COMPLETE'));
        $execute = $this->DB_get_all('patrol_execute', 'assetnum', $where);
        $executeArr = [];
        foreach ($execute as &$one) {
            $executeArr[] = $one['assetnum'];
        }
        //获取保养的设备
        $unexecuted = array_diff($assnum, $executeArr);
        if ($unexecuted) {
            $cycInfo = $this->DB_get_one('patrol_plan_cycle', '*', array('cycid' => $cycId));

//            $cycInfo['plan_assnum'] = str_replace('[', '', $cycInfo['plan_assnum']);
//            $cycInfo['plan_assnum'] = str_replace(']', '', $cycInfo['plan_assnum']);
//            $cycInfo['plan_assnum'] = str_replace('"', '', $cycInfo['plan_assnum']);
//            $assidsArr = $cycInfo['plan_assnum'];
            //查询所有模板明细
            $patrol_model = M('patrol_template');
            $all_tps = $patrol_model->where(array('is_delete' => 0))->getField('tpid,points_num');
            $assnum_tpid = json_decode($cycInfo['assnum_tpid'], true);
            $assidsArr = implode($unexecuted, ',');
            $assidsArr = explode(',', $assidsArr);
            $fileds = 'assets,assid,assnum,catid,departid,status,is_firstaid,is_metering,is_qualityAssets,is_special,is_benefit';
            $asArr = $this->DB_get_all('assets_info', $fileds, array('assnum' => array('in', $assidsArr)));
            foreach ($asArr as $k => $v) {
                $asArr[$k]['arr_num'] = $all_tps[$assnum_tpid[$v['assnum']]];
            }
            $asArrCount = count($asArr);
            $abnormal_sum = 0;
            $fields = 'status,implement_sum,abnormal_sum,repair_assnum,scrap_assnum,abnormal_assnum';
            $data = $this->DB_get_one('patrol_plan_cycle', $fields, "cycid=$cycId");
            foreach ($asArr as &$one) {
                $PatrolExecuteMod = new PatrolExecuteModel();
                switch ($one['status']) {
                    case C('ASSETS_STATUS_REPAIR'):
                        //维修中的设备
                        $asset_status = C('ASSETS_STATUS_IN_MAINTENANCE');
                        $abnormal_sum = 1;
                        if ($data['repair_assnum']) {
                            $data['repair_assnum'] = json_decode($data['repair_assnum']);
                        } else {
                            $data['repair_assnum'] = array();
                        }
                        array_push($data['repair_assnum'], $one['assnum']);
                        $data['repair_assnum'] = json_encode($data['repair_assnum']);
                        break;
                    case C('ASSETS_STATUS_SCRAP'):
                        //异常的设备
                        $asset_status = C('ASSETS_STATUS_SCRAPPED');
                        $abnormal_sum = 1;
                        if ($data['scrap_assnum']) {
                            $data['scrap_assnum'] = json_decode($data['scrap_assnum']);
                        } else {
                            $data['scrap_assnum'] = array();
                        }
                        array_push($data['scrap_assnum'], $one['assnum']);
                        $data['scrap_assnum'] = json_encode($data['scrap_assnum']);
                        break;
                    case C('ASSETS_STATUS_USE'):
                        $asset_status = C('ASSETS_STATUS_NORMAL');
                        break;
                    default:
                        $asset_status = '异常参数';
                }

                if ($abnormal_sum == 1) {
                    if ($data['abnormal_assnum']) {
                        $data['abnormal_assnum'] = json_decode($data['abnormal_assnum']);
                    } else {
                        $data['abnormal_assnum'] = array();
                    }
                    array_push($data['abnormal_assnum'], $one['assnum']);
                    $data['abnormal_assnum'] = json_encode($data['abnormal_assnum']);
                    $data['abnormal_sum'] += $abnormal_sum;
                }
                $arr_num = str_replace(array('"'), "", $one['arr_num']);
                $arr_num = trim($arr_num, ']');
                $arr_num = trim($arr_num, '[');
                $points = $PatrolExecuteMod->getPointsArr($arr_num);
                $result = $abnormal_remark = $ppid = [];
                foreach ($points as &$pointValue) {
                    $result[] = '合格';
                    $ppid[] = $pointValue['ppid'];
                    $abnormal_remark[] = '';
                }
                $abnormalData['result'] = $result;
                $abnormalData['abnormal_remark'] = $abnormal_remark;
                $abnormalData['ppid'] = $ppid;
                $executeData['cycid'] = $cycId;
                $executeData['assetnum'] = $one['assnum'];
                $executeData['asset_status'] = $asset_status;
                $executeData['remark'] = '';
                $executeData['applicant'] = '';
                $executeData['userid'] = '';
                $executeData['executeStatus'] = C('MAINTAIN_COMPLETE');
                $PatrolExecuteMod->addexecute($executeData, $abnormalData);
            }
            $data['implement_sum'] += $asArrCount;
            $this->updateData('patrol_plan_cycle', $data, array('cycid' => $cycId));
        }
    }


    /*
     * 完成该次巡查任务
     * @params1 $cycId ID 周期ID
     * return 1 or false
     */
    public function completeCycle($cycId)
    {
        $data = $this->DB_get_count('patrol_plan_cycle', "cycid=$cycId and status>=" . C('PLAN_CYCLE_COMPLETE'));
        if ($data) {
            die(json_encode(array('status' => -98, 'msg' => '该任务已完成请勿重复操作')));
        } else {
            $saveData['status'] = C('PLAN_CYCLE_COMPLETE');
            return $this->updateData('patrol_plan_cycle', $saveData, "cycid=$cycId");
        }
    }

    /*
     * 周期最后一个完成的时候添加验收数据
     * @params1 $cycId ID 周期ID
     */
    public function addCheckData($cycid)
    {
        //验证是否是这个周期全部是完成状态
        $join[0] = "LEFT JOIN sb_patrol_plan_cycle AS B ON B.cyclenum=A.cyclenum";
        $cycData = $this->DB_get_all_join('patrol_plan_cycle', "A", 'A.cyclenum,B.status', $join, "A.cycid=$cycid", '', '', '');
        $COMPLETE = C('YES_STATUS');
        foreach ($cycData as &$one) {
            if ($one['status'] != C('PLAN_CYCLE_COMPLETE')) {
                $COMPLETE = C('NO_STATUS');
                break;
            }
        }
        if ($COMPLETE) {
            //最后1次,执行生成验收表数据
            $where = "A.cyclenum='" . $cycData[0]['cyclenum'] . "'";
            $join[0] = "LEFT JOIN sb_patrol_plan AS B ON A.patrid = B.patrid";
            $fileds = 'group_concat(executor) as executor,group_concat(plan_assnum) as plan_assnum,
            group_concat(abnormal_assnum) as abnormal_assnum,group_concat(implement_sum) as implement_sum,
            group_concat(repair_assnum) as repair_assnum,group_concat(scrap_assnum) as scrap_assnum,
            group_concat(not_operation_assnum) as not_operation_assnum,group_concat(cycid) as cycid,A.cyclenum,A.patrid,
            A.period,A.startdate,A.overdate,A.patrol_level,B.patrolnum,B.patrolname,B.cycletimes';
            $tasks = $this->DB_get_one_join('patrol_plan_cycle', 'A', $fileds, $join, $where, 'cyclenum');
            $plan_assnum = str_replace('"', "'", str_replace(array("[", "]"), "", $tasks['plan_assnum']));
            //异常的数组
            $abnormal_arr = json_decode(trim(str_replace("],[", ",", $tasks['abnormal_assnum']), ','));
            //正在维修的数组
            $repair_arr = json_decode(trim(str_replace("],[", ",", $tasks['repair_assnum']), ','));
            //报废的数组
            $scrap_arr = json_decode(trim(str_replace("],[", ",", $tasks['scrap_assnum']), ','));
            //报废的数组
            $not_operation_arr = json_decode(trim(str_replace("],[", ",", $tasks['not_operation_assnum']), ','));

            $assData = $this->DB_get_all('assets_info', 'group_concat(assnum) AS assnum,departid', "assnum IN ($plan_assnum)", 'departid');
            $cycidAssnumData = $this->DB_get_all('patrol_plan_cycle', 'cycid,plan_assnum AS assnum', "cycid IN ($tasks[cycid])");
            $settingData = $this->checkSmsIsOpen($this->Controller);
            //==========================================短信 START==========================================
            if ($settingData) {
                $tasks['patrol_level_name'] = PatrolModel::formatpatrolLevel($tasks['patrol_level']);
                $ToolMod = new ToolController();
            }
            //==========================================短信 END============================================
            $departid_arr = [];
            foreach ($assData as &$one) {
                $exallid = $this->addPatrolExamine($tasks, $one['departid'], $one['assnum'], $abnormal_arr, $repair_arr, $scrap_arr, $not_operation_arr);
                $this->addExamineAll($one['assnum'], $cycidAssnumData, $exallid);
                $departid_arr[] = $one['departid'];
            }
            if ($settingData && $departid_arr != []) {
                $UserData = $ToolMod->getUser('examine', $departid_arr);
                if ($settingData['checkPatrolTask']['status'] == C('OPEN_STATUS') && $UserData) {
                    //通知被借科室准备设备 开启
                    $phone = $this->formatPhone($UserData);
                    $sms = PatrolModel::formatSmsContent($settingData['checkPatrolTask']['content'], $tasks);
                    $ToolMod->sendingSMS($phone, $sms, $this->Controller, $cycid);
                }
            }
        }
    }

    /*
     * 生成验收表数据
     * @params1 $tasks array 验收数据
     * @params2 $departid int 科室ID
     * @params3 $assnum string 设备编号
     * @params4 $abnormal_arr array 异常数据
     * @params5 $repair_arr array 正在报修设备
     * @params6 $scrap_arr array 已报废设备
     * @params7 $not_operation_arr array 不进行巡查数据
     * return ID or false
     */
    public function addPatrolExamine($tasks, $departid, $assnum, $abnormal_arr, $repair_arr, $scrap_arr, $not_operation_arr)
    {
        $assnum_arr = explode(',', $assnum);
        $data['assnum_num'] = count($assnum_arr);
        $data['patrid'] = $tasks['patrid'];
        $data['patrolnum'] = $tasks['patrolnum'];
        $data['cycid'] = $tasks['cycid'];
        $data['cyclenum'] = $tasks['cyclenum'];
        $data['patrol_level'] = $tasks['patrol_level'];
        $data['period'] = $tasks['period'];
        $data['executor'] = $tasks['executor'];
        $data['cycle_startdate'] = $tasks['startdate'];
        $data['cycle_overdate'] = $tasks['overdate'];
        $data['patrolname'] = $tasks['patrolname'];
        $data['completiondate'] = time();
        $data['repair_num'] = count(array_intersect($repair_arr, $assnum_arr));
        $data['abnormal_num'] = count(array_intersect($abnormal_arr, $assnum_arr));
        $data['scrap_num'] = count(array_intersect($scrap_arr, $assnum_arr));
        $data['not_operation_num'] = count(array_intersect($not_operation_arr, $assnum_arr));
        $data['examine_departid'] = $departid;
        $assnum = "'" . str_replace(",", "','", $assnum) . "'";
        $join = 'LEFT JOIN sb_patrol_execute_abnormal AS B ON A.execid=B.execid';
        $where = "A.cycid IN ($tasks[cycid]) AND A.assetnum IN ($assnum) AND B.result!='合格' AND 
        A.asset_status_num!=" . C('ASSETS_STATUS_IN_MAINTENANCE') . ' AND A.asset_status_num!=' . C('ASSETS_STATUS_SCRAPPED');
        $data['abnormal_point_num'] = $this->DB_get_count_join('patrol_execute', 'A', $join, $where);
        return $this->insertData('patrol_examine_all', $data);
    }

    /*
     * 批量生成验收副本数据
     * @params1 $assnum string 设备编号
     * @params2 $cycidAssnumData array 周期对应下的设备数组
     * @params3 $exallid ID 验收all表ID
     */
    public function addExamineAll($assnum, $cycidAssnumData, $exallid)
    {

        $assnum_arr = explode(',', $assnum);
        $data = [];
        foreach ($assnum_arr as $key => $value) {
            $data[$key]['exallid'] = $exallid;
            $data[$key]['assnum'] = $value;
            foreach ($cycidAssnumData as &$one) {
                $arr = explode(',', $value);
                if (array_intersect($arr, json_decode($one['assnum']))) {
                    $data[$key]['cycid'] = $one['cycid'];
                    break;
                }
            }
        }
        $this->insertDataALL('patrol_examine_one', $data);
    }

    /*
     * 获取对应的明细
     * @params1 $arr_num string 明细ID
     * return array()
     */
    public function getPointsArr($arr_num)
    {
        return $this->DB_get_all('patrol_points', 'ppid', "num IN($arr_num)");
    }

    /*
    获取巡查验收menu_id
     */
    public function menuid($function, $controller, $model)
    {
        $join[0] = 'LEFT JOIN sb_menu AS B ON B.menuid=A.parentid';
        $join[1] = 'LEFT JOIN sb_menu AS C ON C.menuid=B.parentid';
        $menuid = $this->DB_get_one_join('menu', 'A', 'A.menuid', $join, array('A.name' => $function, 'B.name' => $controller, 'C.name' => $model));
        return $menuid['menuid'];
    }


    /*
     * 检查是不是对应执行人
     * @params1 $cycid ID 周期ID
     */
    public function checkexecutor($cycid)
    {
        $where['cycid'] = $cycid;
        if (!session('isSuper')) {
            $where['executor'] = session('username');
        }
        $data = $this->DB_get_count('patrol_plan_cycle', $where);
        if (!$data) {
            die(json_encode(array('status' => -999, 'msg' => '您不是该计划的执行人,请按流程操作')));
        }
    }

}
