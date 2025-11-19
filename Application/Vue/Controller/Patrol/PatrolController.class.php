<?php

namespace Vue\Controller\Patrol;

use Admin\Controller\Tool\ToolController;
use Admin\Model\AssetsTemplateModel;
use Admin\Model\PatrolExecuteModel;
use Admin\Model\PatrolModel;
use Vue\Controller\Login\IndexController;
use Vue\Model\PatrolPlanModel;
use Vue\Model\UserModel;

class PatrolController extends IndexController
{
    private $planModel;

    /** 依赖注入model tips 懒得每次都new了
     * PatrolController constructor.
     */
    public function __construct()
    {
        $this->planModel = new PatrolPlanModel;
    }

    //巡查保养查询列表
    public function patrolList()
    {
        $result = $this->planModel->getPatrolLists();
        $this->ajaxReturn($result, 'json');
    }

    //巡查保养查询列表
    public function tasksList()
    {
        $result = $this->planModel->getTasksLists();
        $this->ajaxReturn($result, 'json');
    }

    //实施页面
    public function operation()
    {
        if(IS_POST){
            $operation = I('post.operation');
            //\Think\Log::write('$operation======'.$operation);
            //签到
            $result = $this->planModel->scanQRCode_signin();
            $this->ajaxReturn($result, 'json');
        }else{
            $operation = I('get.operation');
            \Think\Log::write('$operation======'.$operation);
            switch ($operation) {
                case 'showPlans':
                    $patrid = I('get.cycid');
                    //查询计划信息
                    $patrol_info = $this->planModel->get_plan_info($patrid);
                    if ($patrol_info) {
                        $status = 1;
                    }
                    //查询计划设备列表
                    $assets = $this->planModel->get_plan_assets($patrol_info);
                    //查询当前用户是否有执行权限
                    $doTask = get_menu('Patrol', 'Patrol', 'doTask');
                    $is_super = session('isSuper');
                    $cur_username = session('username');
                    $cycids = [];
                    //判断是否开启签到
                    $baseSetting = [];
                    include APP_PATH . "Common/cache/basesetting.cache.php";
                    if ($baseSetting['patrol']['patrol_wx_set_situation']['value'] == C('OPEN_STATUS')) {
                        $wx_sign_in = 1;
                    } else {
                        $wx_sign_in = 0;
                    }
                    foreach ($assets as $k => $v) {
                        $cycids[] = $v['cycid'];
                        if ($is_super) {
                            //超级管理员
                            $assets[$k]['operation'] = 'setSituation';
                            $assets[$k]['operation_name'] = '待保养';
                            $assets[$k]['actionurl'] = $doTask['actionurl'];
                        } else {
                            //不是超级管理员，判断是否是该设备的执行人
                            $assets[$k]['need_sign'] = false;
                            $assets[$k]['doTask'] = false;
                            if ($assets[$k]['executor'] == $cur_username) {
                                if ($doTask) {
                                    if ($wx_sign_in == 1) {
                                        //有开启的话 可以签到保养
                                        $assets[$k]['operation'] = 'setSituation';
                                        $assets[$k]['operation_name'] = '需要签到';
                                        $assets[$k]['actionurl'] = $doTask['actionurl'];
                                        $assets[$k]['need_sign'] = true;
                                        if ($v['sign_info']) {
                                            $arr = json_decode($v['sign_info'], true);
                                            if (isset($arr[$v['assnum']])) {
                                                $assets[$k]['operation_name'] = '待保养';
                                                $assets[$k]['bg_color'] = '#1989fa';
                                                $assets[$k]['need_sign'] = false;
                                            }
                                        }
                                    } else {
                                        $assets[$k]['operation'] = 'setSituation';
                                        $assets[$k]['bg_color'] = '#1989fa';
                                        $assets[$k]['operation_name'] = '待保养';
                                        $assets[$k]['actionurl'] = $doTask['actionurl'];
                                        $assets[$k]['doTask'] = true;
                                    }
                                } else {
                                    $assets[$k]['operation'] = 'setSituation';
                                    $assets[$k]['bg_color'] = '';
                                    $assets[$k]['operation_name'] = '待保养';
                                    $assets[$k]['actionurl'] = $doTask['actionurl'];
                                }
                            } else {
                                $assets[$k]['operation'] = 'setSituation';
                                $assets[$k]['operation_name'] = '待保养';
                                $assets[$k]['actionurl'] = $doTask['actionurl'];
                            }
                        }
                    }
                    if ($patrol_info['patrol_status'] >= 4) {
                        //待实施状态，查询保养完成情况
                        $fields = "execid,cycid,assetnum,asset_status_num,status";
                        $ab_where['cycid'] = array('in', $cycids);
                        $res = $this->planModel->DB_get_all('patrol_execute', $fields, $ab_where);
                        foreach ($assets as $ask => $asv) {
                            $assets[$ask]['assets_status_name'] = '<span style="color:#1989FA;">待执行</span>';
                            foreach ($res as $rk=>$rv){
                                if ($rv['cycid'] == $asv['cycid'] && $rv['assetnum'] == $asv['assnum']) {
                                    if($rv['status'] == 1){
                                        $assets[$ask]['assets_status_name'] = '<span style="color:#FF976A;">执行中</span>';
                                        if($is_super || $assets[$ask]['executor'] == $cur_username){
                                            $assets[$ask]['show_upload'] = true;
                                        }
                                    }
                                    if ($rv['status'] == 2) {
                                        //已完成
                                        $assets[$ask]['is_complete'] = 1;
                                        $assets[$ask]['assets_status_name'] = '<span style="color:#07C160;">已完成</span>';
                                        if($is_super || $assets[$ask]['executor'] == $cur_username){
                                            $assets[$ask]['show_upload'] = true;
                                        }

                                        if ($rv['asset_status_num'] == C('ASSETS_STATUS_NORMAL') || $rv['asset_status_num'] == C('ASSETS_STATUS_SMALL_PROBLEM')) {
                                            $assets[$ask]['operation'] = 'setSituation';
                                            $assets[$ask]['operation_name'] = '合格';
                                            $assets[$ask]['actionurl'] = $doTask['actionurl'];
                                        } elseif ($rv['asset_status_num'] == C('ASSETS_STATUS_NOT_OPERATION')) {
                                            $assets[$ask]['operation'] = 'setSituation';
                                            $assets[$ask]['operation_name'] = '不保养';
                                            $assets[$ask]['actionurl'] = $doTask['actionurl'];
                                        } else {
                                            $assets[$ask]['operation'] = 'setSituation';
                                            $assets[$ask]['operation_name'] = '异常';
                                            $assets[$ask]['actionurl'] = $doTask['actionurl'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $result = compact('patrol_info', 'status', 'approve', 'assets');
                    $this->ajaxReturn($result);
                    break;
                case 'get_batch_maintain_assnum':
                    //获取可以一键保养的设备assnum
                    $cycid = I('get.cycid');
                    $PatrolModel = new PatrolModel();
                    $cycleInfo = $PatrolModel->DB_get_one('patrol_plans_cycle','*',['cycid'=>$cycid]);
                    if(in_array($cycleInfo['cycle_status'],[2,3])){
                        $result['status'] = -1;
                        $result['msg'] = '该计划已完成保养';
                        $this->ajaxReturn($result);
                    }

                    $executed = $PatrolModel->DB_get_all('patrol_execute','assetnum',['cycid'=>$cycid,'status'=>2]);
                    $plan_assnum = json_decode($cycleInfo['plan_assnum']);
                    $exec_assnums = $return_assnum = [];
                    foreach ($executed as $ev){
                        $exec_assnums[] = $ev['assetnum'];
                    }
                    foreach ($plan_assnum as $k=>$v){
                        if(!in_array($v,$exec_assnums)){
                            $return_assnum[] = $v;
                        }
                    }
                    if(!$return_assnum){
                        $result['status'] = -1;
                        $result['msg'] = '该计划已完成保养';
                        $this->ajaxReturn($result);
                    }
                    $departids = session('departid');
                    $assInfo = $PatrolModel->DB_get_all('assets_info','assnum',['assnum'=>['in',implode(',',$return_assnum)],'departid'=>['in',$departids]]);
                    $res = [];
                    foreach ($assInfo as $v){
                        $res[] = $v['assnum'];
                    }
                    if(!$res){
                        $result['status'] = -1;
                        $result['msg'] = '该计划的设备不在你管理科室范围内或已全部保养完成';
                        $this->ajaxReturn($result);
                    }
                    $patrol_wx_set_situation = $PatrolModel->DB_get_one('base_setting', '*', array('module' => 'patrol', 'set_item' => 'patrol_wx_set_situation'));
                    if($patrol_wx_set_situation['value'] == 1){
                        //开启了签到才能保养功能
                        $sign_assnum = json_decode($cycleInfo['sign_info']);
                        if(!$sign_assnum){
                            $result['status'] = -1;
                            $result['msg'] = '请先签到再保养设备';
                            $this->ajaxReturn($result);
                        }
                        $sign = [];
                        foreach ($sign_assnum as $kv=>$vv){
                            $sign[] = $kv;
                        }
                        $not_sign_assnum = [];
                        foreach ($res as $k=>$v){
                            if(!in_array($v,$sign)){
                                $not_sign_assnum[] = $v;
                            }
                        }
                        if($not_sign_assnum){
                            $result['status'] = -1;
                            $result['msg'] = '请先签到再保养设备';
                            $this->ajaxReturn($result);
                        }
                    }
                    $cycleInfo['is_overdue'] = 0;//默认未逾期
                    if(date('Y-m-d') > $cycleInfo['cycle_end_date']){
                        $cycleInfo['is_overdue'] = 1;
                        $cycleInfo['now_date'] = date('Y-m-d H:i:s');
                        $cycleInfo['min_date'] = $cycleInfo['cycle_start_date'];
                        $cycleInfo['max_date'] = date('Y-m-d');
                        $cycleInfo['tips'] = '计划日期：'.$cycleInfo['cycle_start_date'].' 到 '.$cycleInfo['cycle_end_date'];
                    }
                    $result['status'] = 1;
                    $result['info'] = $res;
                    $result['cycleInfo'] = $cycleInfo;
                    $this->ajaxReturn($result);
                    break;
                case 'detail':
                    $cycid = I('get.cycid');
                    $PatrolModel = new PatrolModel();
                    $res = $PatrolModel->get_cycle_data($cycid);
                    $result['status'] = 1;
                    $result['info'] = $res;
                    $this->ajaxReturn($result);
                    break;
                case 'sign_in':
                    //签到
                    $result = $this->planModel->scanQRCode_signin();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        }
    }

    public function doTask()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'setSituation'://录入保养情况
                    $PatrolExecuteModel = new PatrolExecuteModel();
                    $result = $PatrolExecuteModel->setSituation();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'upload':
                    if ($_FILES['file']) {
                        $Tool = new ToolController();
                        $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx');
                        $dirName = C('UPLOAD_DIR_PATROL_DOTASK_SETSITUATION_PIC_NAME');
                        $info = $Tool->upFile($style, $dirName);
                        if ($info['status'] == C('YES_STATUS')) {
                            // 上传成功
                            $data['cycid'] = I('post.cycid');
                            $data['assnum'] = I('post.assnum');
                            $data['file_name'] = $info['formerly'];
                            $data['save_name'] = $info['title'];
                            $data['file_type'] = $info['ext'];
                            $data['file_size'] = round($info['size'] / 1000 / 1000, 2);
                            $data['file_url'] = $info['src'];
                            $data['add_user'] = session('username');
                            $data['add_time'] = date('Y-m-d H:i:s');
                            $res = $this->planModel->insertData('patrol_plans_cycle_file', $data);
                            if ($res) {
                                $result['status'] = 1;
                                $result['path'] = $data['file_url'];
                                $result['msg'] = '上传成功！';
                            }
                        } else {
                            // 上传错误提示错误信息
                            $result['status'] = -1;
                            $result['msg'] = '上传图片失败！';
                        }
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'batch_maintain':
                    //一键保养
                    $PatrolExecuteModel = new PatrolExecuteModel();
                    $result = $PatrolExecuteModel->batch_maintain();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $op = I('get.operation');
            switch ($op) {
                case 'setSituation':
                    //显示录入保养情况页面
                    $this->showSetSituation();
                    break;
            }
        }
    }

    //录入保养情况页面
    public function showSetSituation()
    {
        $cycid = I('GET.cycid');
        $assnum = I('GET.assnum');
        if (!$cycid && !$assnum) {
            $this->ajaxReturn(['status' => -1, 'msg' => '非法操作']);
        }
        $AssetsTemplateModel = new AssetsTemplateModel();
        $cycleInfo = $AssetsTemplateModel->DB_get_one('patrol_plans_cycle','cycle_start_date,cycle_end_date,cycle_status',['cycid'=>$cycid]);
        if (!$cycleInfo) {
            $this->ajaxReturn(['status' => -1, 'msg' => '找不到该任务']);
        }
        $cycleInfo['is_overdue'] = 0;//默认未逾期
        if(date('Y-m-d') > $cycleInfo['cycle_end_date'] && !in_array($cycleInfo['cycle_status'],[2,3])){
            $cycleInfo['is_overdue'] = 1;
            $cycleInfo['now_date'] = date('Y-m-d H:i:s');
            $cycleInfo['min_date'] = $cycleInfo['cycle_start_date'];
            $cycleInfo['max_date'] = date('Y-m-d');
            $cycleInfo['tips'] = '计划日期：'.$cycleInfo['cycle_start_date'].' 到 '.$cycleInfo['cycle_end_date'].' 当前已逾期';
        }
        $asArr = $AssetsTemplateModel->getAsArr($assnum, $cycid);
        if (!$asArr['arr_num']) {
            $this->error('流程错误,此设备模板未添加明细');
        }

        $PatrolExecuteModel = new PatrolExecuteModel();
        $executeData = $PatrolExecuteModel->getRecord($assnum, $cycid);
        $time = '';
        if ($executeData) {
            if ($executeData['status'] == C('MAINTAIN_COMPLETE')) {
                $time = $executeData['finish_time'];
                $abnormalArr = $PatrolExecuteModel->getAbnormal($executeData['execid']);
                $pointsArr = '';
                foreach ($abnormalArr as &$one) {
                    $pointsArr .= ',' . $one['ppid'];
                }
                $pointsArr = trim($pointsArr, ',');
                $cate = $AssetsTemplateModel->getIniPoints($pointsArr, $abnormalArr, 1);
                $asArr['count'] = substr_count($pointsArr, ',') + 1;
                if ($executeData['is_torepair'] == C('ASSETS_TO_REPAIR')) {
                    $COMPLETE = '<span style="color:green;font-size: 15px;">已转至报修</span>';
                } else {
                    if ($executeData['asset_status_num'] == C('ASSETS_STATUS_NOT_OPERATION')) {
                        $COMPLETE = '<span style="color:red;font-size: 15px;">该设备不做保养</span>';
                    } else {
                        $COMPLETE = ' <span style="color:green;font-size: 15px;">已完成该设备保养</span>';
                    }
                }
//                        $this->assign('COMPLETE', $COMPLETE);
            } else {
                $abnormalArr = $PatrolExecuteModel->getAbnormal($executeData['execid']);
                $cate = $AssetsTemplateModel->getIniPoints($asArr['arr_num'], $abnormalArr);
            }
        } else {
            $cate = $AssetsTemplateModel->getIniPoints($asArr['arr_num']);
        }
        $userModel = new \Admin\Model\UserModel();
        $user = $userModel->getUsers('addRepair', $asArr['departid'], true, false);
        $doTask = get_menu('Patrol', 'Patrol', 'doTask');
        foreach ($cate as &$one) {
            foreach ($one['detail'] as &$v) {
                if ($executeData['status'] == C('MAINTAIN_COMPLETE')) {
                    $v['passCheck'] = $v['pass'] = '';
                    $v['repairCheck'] = $v['repair'] = '';
                    $v['availCheck'] = $v['avail'] = '';
                    $v['repairedCheck'] = $v['repaired'] = '';
                    $v['passTitle'] = '合格';
                    $v['repairTitle'] = '修复';
                    $v['availTitle'] = '可用';
                    $v['repairedTitle'] = '待修';
                    if ($executeData['asset_status_num'] == C('ASSETS_STATUS_IN_MAINTENANCE') or $executeData['asset_status_num'] == C('ASSETS_STATUS_SCRAPPED')) {
                        //完成状态 类型为设备维修 报废等 则都不选中
                        $v['pass'] = 'disabled';
                        $v['repair'] = 'disabled';
                        $v['avail'] = 'disabled';
                        $v['repaired'] = 'disabled';
                    } else {
                        //完成状态 组合数据
                        switch ($v['result']) {
                            case '合格':
                                $v['passCheck'] = 'checked';
                                $v['passTitle'] = "<span class='green'>合格</span>";
                                $v['repair'] = 'disabled';
                                $v['avail'] = 'disabled';
                                $v['repaired'] = 'disabled';
                                break;
                            case '修复':
                                $v['repairCheck'] = 'checked';
                                $v['repairTitle'] = "<span class='rquireCoin'>修复</span>";
                                $v['pass'] = 'disabled';
                                $v['avail'] = 'disabled';
                                $v['repaired'] = 'disabled';
                                break;
                            case '可用':
                                $v['availCheck'] = 'checked';
                                $v['availTitle'] = "<span class='rquireCoin'>可用</span>";
                                $v['pass'] = 'disabled';
                                $v['repair'] = 'disabled';
                                $v['repaired'] = 'disabled';
                                break;
                            case '待修':
                                $v['repairedCheck'] = 'checked';
                                $v['repairedTitle'] = "<span class='rquireCoin'>待修</span>";
                                $v['pass'] = 'disabled';
                                $v['repair'] = 'disabled';
                                $v['avail'] = 'disabled';
                                break;
                        }
                    }
                } else {
                    $v['disabled'] = '';
                    //未完成保养
//                            $v['disabled'] = '';
                    $v['abnormal_remark'] = $v['remark'];
                    if (!$doTask) {
                        //巡查未完成 并且没有操作的权限只有查看的权限 则disabled选项
                        $v['disabled'] = 'disabled';
                    }
                }
            }
        }
        //获取图片
        $data = $AssetsTemplateModel->DB_get_all('patrol_plans_cycle_file', 'file_url', array('cycid' => $cycid, 'assnum' => $assnum, 'is_delete' => 0,'file_type'=>['not in',['pdf','docx','doc']]));
        $imageArr = [];
        if (count($data) > 0){
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            foreach ($data as $v) {

                $imageArr[] = "$protocol" . C('HTTP_HOST') . $v['file_url'];
            }
        }
        $result['path'] = $imageArr;
        $result['data'] = $cate;
        $result['doTask'] = $doTask;
        $result['time'] = $time;
        $result['asArr'] = $asArr;
        $result['action'] = 'setSituation';
        $result['executeData'] = $executeData;
        $result['cycid'] = $cycid;
        $result['user'] = $user;
        $result['cycleInfo'] = $cycleInfo;
        $result['status'] = 1;
        $this->ajaxReturn($result);
    }
}
