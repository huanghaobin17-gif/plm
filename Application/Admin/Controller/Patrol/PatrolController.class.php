<?php

namespace Admin\Controller\Patrol;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\AssetsTemplateModel;
use Admin\Model\PatrolAssetsPackModel;
use Admin\Model\PatrolExamineAllModel;
use Admin\Model\PatrolExecuteModel;
use Admin\Model\PatrolModel;
use Admin\Model\PatrolPlanModel;
use Admin\Model\PointModel;
use Admin\Model\UserModel;

class PatrolController extends CheckLoginController
{
    private $MODULE = 'Patrol';
    private $CONTROLLER = 'Patrol';

    public function index()
    {
        $this->display();
    }

    //巡查保养查询列表
    public function patrolList()
    {
        if (IS_POST) {
            //实例化模型
            $planModel = new PatrolPlanModel();
            $result    = $planModel->getPatrolLists();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->assign('patrolListUrl', get_url());
            $this->display();
        }
    }

    //新增巡查保养功能
    public function addPatrol()
    {
        $departid = session('departid');
        //重置notAssnums
        session('notAssnums', null);
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'addAssetslist':
                    $PatrolModel = new PatrolModel();
                    $result      = $PatrolModel->getAddAssetslist();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'delAssetslist':
                    $PatrolModel = new PatrolModel();
                    $result      = $PatrolModel->getDelAssetslist();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'removeAssets':
                    $AsstsPackMod = new PatrolAssetsPackModel();
                    $result       = $AsstsPackMod->removeAssets();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'doTaskList':
                    //获取实施列表
                    $PatrolPlanModel = new PatrolPlanModel();
                    $result          = $PatrolPlanModel->doTaskList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'is_Approval':
                    $AsstsPackMod = new PatrolAssetsPackModel();
                    $result       = $AsstsPackMod->is_Approval();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'create_next_plan':
                    $PatrolPlanModel = new PatrolPlanModel();
                    $patrid          = I('post.patrid');
                    $result          = $PatrolPlanModel->create_next_plan($patrid);
                    if ($result['status'] == 1) {
                        //创建成功，发送信息
                        $PatrolPlanModel->send_wechat_then_create_next_plans($result['depart_ids'], $patrid,
                            $result['patrol_name'], $result['cycle_start_date'], $result['cycle_end_date'],
                            session('current_hospitalid'));
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $AsstsPackMod = new PatrolAssetsPackModel();
                    $result       = $AsstsPackMod->addPatrol();
                    $this->ajaxReturn($result, 'json');
                    break;
            }

        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'doTask':
                    //查看计划执行情况
                    $this->showCheckDoTask();
                    break;
                case 'setSituation':
                    //查看单台设备保养情况
                    $this->showSetSituation();
                    break;
                default:
                    //判断巡查保养审核是否合格
                    $departids   = explode(',', $departid);
                    $departments = $this->getDepartname($departids);
                    $this->assign('level', I('get.level'));
                    $this->assign('departments', $departments);
                    $this->assign('url', C('ADMIN_NAME') . '/Patrol/' . ACTION_NAME);
                    $this->assign('nextUrl', C('ADMIN_NAME') . '/Patrol/allocationPlan');
                    $this->display();
                    break;
            }
        }
    }


    //查看计划执行情况
    private function showCheckDoTask()
    {
        $cyclenum = I('GET.cyclenum');
        $paModel  = new PatrolModel();
        //获取周期信息
        $cycInfo = $paModel->getCycInfo($cyclenum);
        if (!$cycInfo) {
            $this->error('您不是计划执行人！');
        }
        $cycInfo['plan_assnum'] = str_replace('[', '', $cycInfo['plan_assnum']);
        $cycInfo['plan_assnum'] = str_replace(']', '', $cycInfo['plan_assnum']);
        $cycInfo['plan_assnum'] = str_replace('"', '', $cycInfo['plan_assnum']);
        $assidsArr              = $cycInfo['plan_assnum'];
        $assnums                = explode(',', $cycInfo['plan_assnum']);
        $PatrolExecuteModel     = new PatrolPlanModel();
        $result                 = $PatrolExecuteModel->getDoTaskAsstes($assidsArr, $cycInfo, $cycInfo['cycid']);
        //总明细项数
        $pointsnum = 0;
        //总异常项数
        $ExceptionTerm = 0;
        //总异常设备数
        $abnormalCount = 0;
        $departids     = [];
        foreach ($result as &$one) {
            if ($one['abnormalAsset'] == true) {
                $abnormalCount++;
            }
            $ExceptionTerm += $one['abnormalPointNum'];
            $pointsnum     += count(json_decode($one['arr_num'], true));
            array_push($departids, $one['departid']);
        }
        $departments = $this->getDepartname($departids);
        $this->assign('cycInfo', $cycInfo);
        $this->assign('sum', count($assnums));
        $this->assign('pointsnum', $pointsnum);
        $this->assign('ExceptionTerm', $ExceptionTerm);
        $this->assign('abnormalCount', $abnormalCount);
        $this->assign('assidsArr', $assidsArr);
        $this->assign('action', 'doTask');
        $this->assign('url', C('ADMIN_NAME') . '/Patrol/addPatrol.html');
        $this->assign('doTaskUrl', C('ADMIN_NAME') . '/Patrol/doTask.html');
        $this->assign('departments', $departments);
        $this->display('doTask');
    }

    /**
     * Notes: 上传、查看报告
     */
    public function scanPic()
    {
        if (IS_POST) {
            $action  = I('post.action');
            $file_id = I('post.file_id');
            if ($action == 'del_file') {
                $paModel = new PatrolModel();
                $res     = $paModel->updateData('patrol_plans_cycle_file', ['is_delete' => 1], ['file_id' => $file_id]);
                if ($res) {
                    $this->ajaxReturn(['status' => 1, 'msg' => '删除成功'], 'json');
                } else {
                    $this->ajaxReturn(['status' => 1, 'msg' => '删除失败'], 'json');
                }
            }
        } else {
            $assnum = I('get.assnum');
            $cycid  = I('get.cycid');
            //查询对应的报告
            $paModel = new PatrolModel();
            $files   = $paModel->DB_get_all('patrol_plans_cycle_file', '*',
                ['cycid' => $cycid, 'assnum' => $assnum, 'is_delete' => 0]);
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
            $this->assign('uploadinfo', $files);
            $this->assign('assnum', $assnum);
            $this->assign('cycid', $cycid);
            $this->display('uploadReport');
        }
    }

    //确认计划
    public function allocationPlan()
    {
        if (IS_POST) {
            $type        = I('post.type');
            $patrolModel = new PatrolPlanModel();
            switch ($type) {
                case 'getAssets':
                    $res = $patrolModel->get_plan_assets();
                    break;
                case 'save_plan':
                    $res = $patrolModel->save_plan();
                    break;
            }
            $this->ajaxReturn($res, 'json');
        } else {
            $this->assign('level', I('get.level'));
            $this->display();
        }
    }

    /*
     * 发布计划
     */
    public function releasePatrol()
    {
        $patrolPlanModel = new PatrolPlanModel();
        if (IS_POST) {
            //修改周期计划为已发布
            if ($_POST['releasePatrol_code'] != session('releasePatrol_code')) {
                $this->ajaxReturn(['status' => -1, 'msg' => '请勿重复提交'], 'json');
            }
            $result = $patrolPlanModel->releasePatrol();
            $this->ajaxReturn($result, 'json');
        } else {
            $patrid      = I('get.id');
            $PatrolModel = new PatrolModel();
            //查询计划信息
            $patrol_info = $PatrolModel->get_plan_info($patrid);
            //查询计划设备列表
            $assets = $PatrolModel->get_plan_assets($patrol_info);
            if ($patrol_info['approve_status'] != -1) {
                //查询审批信息
                $apps = $PatrolModel->get_approve_info($patrid);
                $this->assign('apps', $apps);
            }
            switch ($patrol_info['patrol_level']) {
                case 3:
                    $this->assign('pre_date_name', '上一次巡查日期');
                    $this->assign('detail_name', '巡检明细(项)');
                    $this->assign('template_name', '巡查模板');
                    break;
                default:
                    $this->assign('pre_date_name', '上一次保养日期');
                    $this->assign('detail_name', '保养明细(项)');
                    $this->assign('template_name', '保养模板');
                    break;
            }
            //待发布
            if ($patrol_info['patrol_status'] == 2) {
                $this->assign('canRelease', 1);
                $this->assign('username', session('username'));
                $this->assign('min_date',
                    ($patrol_info['patrol_start_date'] > date('Y-m-d')) ? date('Y-m-d') : $patrol_info['patrol_start_date']);
                $this->assign('max_date',
                    ($patrol_info['patrol_end_date'] > date('Y-m-d')) ? date('Y-m-d') : $patrol_info['patrol_end_date']);
            }
            $this->assign('patrol_info', $patrol_info);
            $this->assign('assets', json_encode($assets));
            $this->assign('releasePatrol', get_url());
            $code = mt_rand(0, 1000000);
            session('releasePatrol_code', $code);
            $this->assign('releasePatrol_code', $code);
            $this->display();
        }
    }

    /*
     * 添加设备
     */
    public function addAssets()
    {
        $departid = session('departid');
        if (IS_POST) {
            $this->checkstatus(I('POST.name'), '请输入:计划设备列表（包）统一名称');
            $this->checkstatus(judgeEmpty(I('POST.removedata')), '设备包不能为空');
            $AsstsPackMod          = new PatrolAssetsPackModel();
            $packid                = I('POST.packid');
            $addData['arr_assnum'] = $_POST['removedata'];
            $addData['desc']       = I('POST.remark');
            $addData['packname']   = I('POST.name');
            if ($packid) {
                $AsstsPackMod->updateData('patrol_plan_assets_pack', $addData, ['packid' => $packid]);
                $addData['packid'] = $packid;
                $this->ajaxReturn(['status' => 1, 'msg' => '修改设备包成功', 'result' => $addData], 'json');
            } else {
                $this->ajaxReturn(['status' => -99, 'msg' => '非法操作'], 'json');
            }
        } else {
            $packid      = I('GET.packid');
            $patrolModel = new PatrolAssetsPackModel();
            $packInfo    = $patrolModel->DB_get_one('patrol_plan_assets_pack', '', ['packid' => $packid]);
            $departids   = explode(',', $departid);
            $departments = $this->getDepartname($departids);
            $this->assign('oldAssetsList', session('notAssnums'));
            $this->assign('packInfo', $packInfo);
            $this->assign('departments', $departments);
            $this->assign('url', C('ADMIN_NAME') . '/Patrol/' . ACTION_NAME);
            $this->assign('nextUrl', C('ADMIN_NAME') . '/Patrol/allocationPlan');
            $this->display();
        }
    }

    //任务列表
    public function tasksList()
    {
        if ($_POST) {
            $PatrolModel = new PatrolModel();
            $result      = $PatrolModel->getTasksList();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->assign('tasksListUrl', get_url());
            $this->display();
        }
    }

    //执行任务功能
    public function doTask()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'doTaskList':
                    //获取实施列表
                    $PatrolPlanModel = new PatrolPlanModel();
                    $result          = $PatrolPlanModel->doTaskList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'upload':
                    $PatrolModel = new PatrolModel();
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
                        $dirName = C('UPLOAD_DIR_PATROL_DOTASK_SETSITUATION_PIC_NAME');
                        $info    = $Tool->upFile($style, $dirName);
                        if ($info['status'] == C('YES_STATUS')) {
                            // 上传成功
                            $data['cycid']     = I('post.cycid');
                            $data['assnum']    = I('post.assnum');
                            $data['file_name'] = $info['formerly'];
                            $data['save_name'] = $info['title'];
                            $data['file_type'] = $info['ext'];
                            $data['file_size'] = round($info['size'] / 1000 / 1000, 2);
                            $data['file_url']  = $info['src'];
                            $data['add_user']  = session('username');
                            $data['add_time']  = date('Y-m-d H:i:s');
                            $res               = $PatrolModel->insertData('patrol_plans_cycle_file', $data);
                            if ($res) {
                                $result['status'] = 1;
                                $result['path']   = $data['file_url'];
                                $result['msg']    = '上传成功！';
                            }
                        } else {
                            // 上传错误提示错误信息
                            $result['status'] = -1;
                            $result['msg']    = '上传图片失败！';
                        }
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'setSituation':
                    //录入保养情况
                    $PatrolExecuteModel = new PatrolExecuteModel();
                    $result             = $PatrolExecuteModel->setSituation();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'batch_maintain':
                    //一键保养
                    $PatrolExecuteModel = new PatrolExecuteModel();
                    $result             = $PatrolExecuteModel->batch_maintain();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'checkTemporary':
                    //检查是否存在暂存的任务
                    $PatrolPlanModel = new PatrolPlanModel();
                    $result          = $PatrolPlanModel->checkTemporary();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addSetPoints':
                    //补充明细操作
                    $partolMod = new PointModel();
                    $result    = $partolMod->addSetPoints();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'openExamineList':
                    //实施人员: 获取当前周期验收情况列表
                    $where = 1;
                    if (!session('isSuper')) {
                        $where = "FIND_IN_SET('" . session('username') . "',executor)";
                    }
                    $PatrolModel = new PatrolModel();
                    $result      = $PatrolModel->commonExamineList($where);
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'openExamine':
                    //实施人员: 获取当前科室设备验收情况列表数据
                    $PatrolExamineAllModel = new PatrolExamineAllModel();
                    $result                = $PatrolExamineAllModel->doExamineList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'setAssetAbnormal':
                    //对正在维修和已报废的设备自动补充明细
                    $PatrolExecuteModel = new PatrolExecuteModel();
                    $result             = $PatrolExecuteModel->setAssetAbnormal();
                    $this->ajaxReturn($result, 'json');
                    break;
                default :
                    //实施完成操作
                    $PatrolExecuteModel = new PatrolExecuteModel();
                    $result             = $PatrolExecuteModel->doTask();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'getTaskList':
                    //获取对应json数据
                    $patrol = new \Vue\Controller\Patrol\PatrolController();
                    $patrol->showSetSituation();
                    break;
                case 'setSituation':
                    //显示录入保养情况页面
                    $this->showSetSituation();
                    break;
                case 'addSetPoints':
                    //补充保养明细页面
                    $this->showAddSetPoints();
                    break;
                case 'openExamineList':
                    //当前周期验收情况页面
                    $this->showOpenExamineList();
                    break;
                case 'openExamine':
                    //当前科室内的设备验收情况页面
                    $this->showOpenExamine();
                    break;
                case 'openExamineOne':
                    //当前设备验收情况页面
                    $this->showOpenExamineOne();
                    break;
                default :
                    //执行任务页面
                    $this->showDoTask();
                    break;
            }
        }
    }

    //执行任务页面
    private function showDoTask()
    {
        $cyclenum = I('GET.cyclenum');
        $paModel  = new PatrolModel();
        //获取周期信息
        $cycInfo = $paModel->getCycInfo($cyclenum);
        if (!$cycInfo) {
            $this->error('异常操作！');
        }
        $cycInfo['plan_assnum'] = str_replace('[', '', $cycInfo['plan_assnum']);
        $cycInfo['plan_assnum'] = str_replace(']', '', $cycInfo['plan_assnum']);
        $cycInfo['plan_assnum'] = str_replace('"', '', $cycInfo['plan_assnum']);
        $assidsArr              = $cycInfo['plan_assnum'];
        $assnums                = explode(',', $cycInfo['plan_assnum']);
        $PatrolExecuteModel     = new PatrolPlanModel();
        $result                 = $PatrolExecuteModel->getDoTaskAsstes($assidsArr, $cycInfo, $cycInfo['cycid']);
        //总明细项数
        $pointsnum = 0;
        //总异常项数
        $ExceptionTerm = 0;
        //总异常设备数
        $abnormalCount = 0;
        $departids     = [];
        foreach ($result as &$one) {
            if ($one['abnormalAsset'] == true) {
                $abnormalCount++;
            }
            $ExceptionTerm += $one['abnormalPointNum'];
            if ($one['pointsNum']) {
                $pointsnum += $one['pointsNum'];
            } else {
                $pointsnum += count(json_decode($one['arr_num']));
            }
            array_push($departids, $one['departid']);
        }
        $departments = $this->getDepartname($departids);
        $this->assign('cycInfo', $cycInfo);
        $this->assign('sum', count($assnums));
        $this->assign('pointsnum', $pointsnum);
        $this->assign('ExceptionTerm', $ExceptionTerm);
        $this->assign('abnormalCount', $abnormalCount);
        $this->assign('assidsArr', $assidsArr);
        $this->assign('action', 'doTask');
        $this->assign('url', C('ADMIN_NAME') . '/Patrol/doTask.html');
        $this->assign('doTaskUrl', C('ADMIN_NAME') . '/Patrol/doTask.html');
        $this->assign('departments', $departments);
        $this->display('doTask');
    }

    //录入保养情况页面
    private function showSetSituation()
    {
        $cycid  = I('GET.cycid');
        $assnum = I('GET.assnum');
        if (!$cycid || !$assnum) {
            $this->error('非法操作');
        }
        $AssetsTemplateModel = new AssetsTemplateModel();
        $asArr               = $AssetsTemplateModel->getAsArr($assnum, $cycid);
        if ($asArr['arr_num']) {
            $PatrolExecuteModel = new PatrolExecuteModel();
            $executeData        = $PatrolExecuteModel->getRecord($assnum, $cycid);
            $time               = '';
            if ($executeData) {
                if ($executeData['status'] == C('MAINTAIN_COMPLETE')) {
                    $time        = $executeData['finish_time'];
                    $abnormalArr = $PatrolExecuteModel->getAbnormal($executeData['execid']);
                    $pointsArr   = '';
                    foreach ($abnormalArr as &$one) {
                        $pointsArr .= ',' . $one['ppid'];
                    }
                    $pointsArr      = trim($pointsArr, ',');
                    $cate           = $AssetsTemplateModel->getIniPoints($pointsArr, $abnormalArr, 1);
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
                    $this->assign('COMPLETE', $COMPLETE);
                } else {
                    $abnormalArr = $PatrolExecuteModel->getAbnormal($executeData['execid']);
                    $cate        = $AssetsTemplateModel->getIniPoints($asArr['arr_num'], $abnormalArr);
                }
            } else {
                $cate = $AssetsTemplateModel->getIniPoints($asArr['arr_num']);
            }
            $userModel = new UserModel();
            $user      = $userModel->getUsers('addRepair', $asArr['departid'], true, false);
            $doTask    = get_menu($this->MODULE, $this->CONTROLLER, 'doTask');
            foreach ($cate as &$one) {
                foreach ($one['detail'] as &$v) {
                    if ($executeData['status'] == C('MAINTAIN_COMPLETE')) {
                        $v['passCheck']     = $v['pass'] = '';
                        $v['repairCheck']   = $v['repair'] = '';
                        $v['availCheck']    = $v['avail'] = '';
                        $v['repairedCheck'] = $v['repaired'] = '';
                        $v['passTitle']     = '合格';
                        $v['repairTitle']   = '修复';
                        $v['availTitle']    = '可用';
                        $v['repairedTitle'] = '待修';
                        if ($executeData['asset_status_num'] == C('ASSETS_STATUS_IN_MAINTENANCE') or $executeData['asset_status_num'] == C('ASSETS_STATUS_SCRAPPED')) {
                            //完成状态 类型为设备维修 报废等 则都不选中
                            $v['pass']     = 'disabled';
                            $v['repair']   = 'disabled';
                            $v['avail']    = 'disabled';
                            $v['repaired'] = 'disabled';
                        } else {
                            //完成状态 组合数据
                            switch ($v['result']) {
                                case '合格':
                                    $v['passCheck'] = 'checked';
                                    $v['passTitle'] = "<span class='green'>合格</span>";
                                    $v['repair']    = 'disabled';
                                    $v['avail']     = 'disabled';
                                    $v['repaired']  = 'disabled';
                                    break;
                                case '修复':
                                    $v['repairCheck'] = 'checked';
                                    $v['repairTitle'] = "<span class='rquireCoin'>修复</span>";
                                    $v['pass']        = 'disabled';
                                    $v['avail']       = 'disabled';
                                    $v['repaired']    = 'disabled';
                                    break;
                                case '可用':
                                    $v['availCheck'] = 'checked';
                                    $v['availTitle'] = "<span class='rquireCoin'>可用</span>";
                                    $v['pass']       = 'disabled';
                                    $v['repair']     = 'disabled';
                                    $v['repaired']   = 'disabled';
                                    break;
                                case '待修':
                                    $v['repairedCheck'] = 'checked';
                                    $v['repairedTitle'] = "<span class='rquireCoin'>待修</span>";
                                    $v['pass']          = 'disabled';
                                    $v['repair']        = 'disabled';
                                    $v['avail']         = 'disabled';
                                    break;
                            }
                        }
                    } else {
                        $v['disabled'] = '';
                        if (!$doTask) {
                            //巡查未完成 并且没有操作的权限只有查看的权限 则disabled选项
                            $v['disabled'] = 'disabled';
                        }
                    }
                }
            }
            //获取图片
            $data      = $AssetsTemplateModel->DB_get_one('patrol_plans_cycle_file', 'file_url',
                ['cycid' => $cycid, 'assnum' => $assnum, 'is_delete' => 0]);
            $cycleInfo = $PatrolExecuteModel->DB_get_one('patrol_plans_cycle', 'cycle_start_date,cycle_end_date',
                ['cycid' => $cycid]);
            $this->assign('execute_time', 0);
            if (date('Y-m-d') > $cycleInfo['cycle_end_date']) {
                $hint = '计划日期：' . $cycleInfo['cycle_start_date'] . ' 到 ' . $cycleInfo['cycle_end_date'] . '<span style="color: red;padding-left: 10px;">当前已逾期</span>';
                $this->assign('execute_time', 1);
                $this->assign('hint', $hint);
            }
            $this->assign('path', $data['file_url']);
            $this->assign('data', $cate);
            $this->assign('doTask', $doTask);
            $this->assign('time', $time);
            $this->assign('asArr', $asArr);
            $this->assign('action', 'setSituation');
            $this->assign('url', get_url());
            $this->assign('executeData', $executeData);
            //$this->assign('level', $patrol_level);
            $this->assign('cycid', $cycid);
            $this->assign('user', $user);
            $this->assign('now', date('Y-m-d H:i:s'));
            $this->assign('min', $cycleInfo['cycle_start_date']);
            $this->assign('max', date('Y-m-d'));
            $this->display('setSituation');
        } else {
            $this->error('流程错误,此设备模板未添加明细');
        }
    }

    //补充明显页面
    private function showAddSetPoints()
    {
        $tpid  = I('get.tpid');
        $level = I('get.level');
        if (!$tpid or !$level) {
            ('参数非法');
        }
        $AssetsTemplateModel = new AssetsTemplateModel();
        $fileds              = 'name,tpid';
        if ($level == C('PATROL_LEVEL_RC')) {
            $fileds .= ',arr_num_1 AS arr_num';
        }
        if ($level == C('PATROL_LEVEL_XC')) {
            $fileds .= ',arr_num_2 AS arr_num';
        }
        if ($level == C('PATROL_LEVEL_PM')) {
            $fileds .= ',arr_num_3 AS arr_num';
        }
        $tpInfo = $AssetsTemplateModel->DB_get_one('patrol_template', $fileds, ['tpid' => $tpid]);
        //获取对应存在的明细
        $arr_num = json_decode($tpInfo['arr_num']);
        //获取所有类别明细
        $pointCat = $AssetsTemplateModel->DB_get_all('patrol_points', 'ppid,num,name,parentid,result,require');
        $cate     = $AssetsTemplateModel->getFormatCate($pointCat, $arr_num);
        //获取模板
        $template = $AssetsTemplateModel->DB_get_all('patrol_template', 'name', ['tpid' => ['neq', $tpid]], '',
            'tpid asc');
        $tp       = json_encode($template);
        $this->assign('name', $tpInfo['name']);
        $this->assign('level', $level);
        $this->assign('tpid', $tpInfo['tpid']);
        $this->assign('level3', json_decode($tpInfo['arr_num']));
        $this->assign('action', 'addSetPoints');
        $this->assign('url', C('ADMIN_NAME') . '/Patrol/doTask.html');
        $this->assign('data', $cate);
        $this->assign('tp', $tp);
        $this->display('addSetPoints');
    }

    //当前周期验收情况页面-面向实施人员
    private function showOpenExamineList()
    {
        $cyclenum = I('GET.cyclenum');
        if ($cyclenum) {
            $this->assign('cyclenum', $cyclenum);
        }
        $this->assign('action', 'openExamineList');
        $this->assign('url', C('ADMIN_NAME') . '/Patrol/doTask.html');
        $this->display('openExamineList');
    }

    //验收单页面-面向实施人员
    private function showOpenExamine()
    {
        $exallid               = I('GET.exallid');
        $PatrolExamineAllModel = new PatrolExamineAllModel();
        //获取周期信息
        $examineData = $PatrolExamineAllModel->getExamine($exallid, true);
        if (!$examineData) {
            $this->error('您没有权限查看此科室');
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $examineData['department'] = $departname[$examineData['examine_departid']]['department'];
        if ($examineData['status'] == C('CYCLE_STANDBY')) {
            $examineData['status_name'] = '<span class="rquireCoin">' . C('CYCLE_EXECUTION_NAME') . '</span>';
        } else {
            $examineData['status_name'] = '<span style="color: green">' . C('CYCLE_COMPLETE_NAME') . '</span>';
        }
        $this->assign('action', 'openExamine');
        $this->assign('url', C('ADMIN_NAME') . '/Patrol/doTask.html');
        $this->assign('data', $examineData);
        $this->display('openExamine');
    }

    //验收单台设备验收情况-面向实施人员
    private function showOpenExamineOne()
    {
        $exoneid = I('GET.exoneid');
        if ($exoneid > 0) {
            $PatrolExamineAllModel = new PatrolExamineAllModel();
            $oneData               = $PatrolExamineAllModel->getPatrolExamineOne($exoneid);
            if (!$oneData) {
                $this->error('无数据,流程有误');
            }
            $asArr               = $PatrolExamineAllModel->getAssets($oneData['assnum']);
            $PatrolExecuteModel  = new PatrolExecuteModel();
            $executeData         = $PatrolExecuteModel->getRecord($oneData['assnum'], $oneData['cycid']);
            $AssetsTemplateModel = new AssetsTemplateModel();
            $abnormalArr         = $PatrolExecuteModel->getAbnormal($executeData['execid']);
            $pointsArr           = '';
            foreach ($abnormalArr as &$one) {
                $pointsArr .= ',' . $one['ppid'];
            }
            $cyc                     = $PatrolExecuteModel->DB_get_one('patrol_plan_cycle', 'executor',
                ['cycid' => $oneData['cycid']]);
            $executeData['executor'] = $cyc['executor'];
            $pointsArr               = trim($pointsArr, ',');
            $asArr['count']          = substr_count($pointsArr, ',') + 1;
            $cate                    = $AssetsTemplateModel->getIniPoints($pointsArr, $abnormalArr, 1);
            if ($oneData['status'] == C('CYCLE_STANDBY')) {
                $oneData['status_name'] = '<span class="rquireCoin">' . C('CYCLE_STANDBY_NAME') . '</span>';
            } else {
                $oneData['status_name'] = '<span style="color: green">' . C('CYCLE_COMPLETE_NAME') . '</span>';
            }
            $this->assign('data', $cate);
            $this->assign('oneData', $oneData);
            $this->assign('asArr', $asArr);
            $this->assign('action', 'openExamineOne');
            $this->assign('url', C('ADMIN_NAME') . '/Patrol/doTask.html');
            $this->assign('executeData', $executeData);
            $this->display('openExamineOne');
        } else {
            $this->error('非法操作');
        }
    }

    //科室验收列表-面向验收人员
    public function examineList()
    {
        if (IS_POST) {
            if (session('departid')) {
                $where['A.cycle_status'] = ['in', '2,3'];
                $PatrolModel             = new PatrolModel();
                $result                  = $PatrolModel->commonExamineList($where);
                $this->ajaxReturn($result, 'json');
            } else {
                $result['msg']  = '暂无相关数据';
                $result['code'] = 400;
                $this->ajaxReturn($result, 'json');
            }
        } else {
            $this->assign('examineListUrl', get_url());
            $this->display();
        }
    }

    //验收功能
    public function examine()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'doExamineList':
                    //验收页面 返回设备列表
                    $PatrolExamineAllModel = new PatrolExamineAllModel();
                    $result                = $PatrolExamineAllModel->doExamineList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'examineOne':
                    $PatrolExamineAllModel = new PatrolExamineAllModel();
                    $result                = $PatrolExamineAllModel->examineOne();
                    $this->ajaxReturn($result, 'json');
                    break;
                default :
                    //验收本周期
                    $PatrolExamineAllModel = new PatrolExamineAllModel();
                    $result                = $PatrolExamineAllModel->examineAll();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'examineOne':
                    //验收单台设备 页面
                    $this->showExamineOne();
                    break;
                default :
                    //验收本周期 页面
                    $this->showExamine();
                    break;
            }
        }
    }

    //验收本周期 页面
    private function showExamine()
    {
        $cycid       = I('get.id');
        $departids   = explode(',', session('departid'));
        $PatrolModel = new PatrolModel();
        $cycleInfo   = $PatrolModel->DB_get_one('patrol_plans_cycle', '*', ['cycid' => $cycid]);
        switch ($cycleInfo['cycle_status']) {
            case '0':
                $cycleInfo['cycle_status_name'] = '待执行';
                break;
            case '1':
                $cycleInfo['cycle_status_name'] = '执行中';
                break;
            case '2':
                $cycleInfo['cycle_status_name'] = '按期完成';
                break;
            case '3':
                $cycleInfo['cycle_status_name'] = '逾期完成';
                break;
            case '4':
                $cycleInfo['cycle_status_name'] = '逾期未完成';
                break;
        }
        $patrid = $cycleInfo['patrid'];
        //查询计划信息
        $patrol_info = $PatrolModel->get_plan_info($patrid);
        //查询周期计划设备列表
        $assets = $PatrolModel->get_cycle_plan_assets($cycid);

        $assnums    = json_decode($cycleInfo['plan_assnum']);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        //查询设备信息
        $where['assnum'] = ['in', $assnums];
        if (!session('isSuper')) {
            //非超级管理员只查询自己管理科室的设备
            $departid          = session('departid');
            $where['departid'] = ['in', $departid];
        }
        //查询各科室设备完成情况
        $depart_assets = $PatrolModel->DB_get_all('assets_info', 'assid,assets,assnum,assorignum,model,departid',
            ['assnum' => ['in', $assnums]]);
        $depart_ids    = $departInfo = [];
        $i             = 0;
        foreach ($depart_assets as $v) {
            if (!in_array($v['departid'], $depart_ids)) {
                $depart_ids[]                  = $v['departid'];
                $departInfo[$i]['departid']    = $v['departid'];
                $departInfo[$i]['department']  = $departname[$v['departid']]['department'];
                $departInfo[$i]['assnum_nums'] = 0;
                $i++;
            }
        }
        foreach ($depart_assets as $dv) {
            foreach ($departInfo as $k => $v) {
                if ($dv['departid'] == $v['departid']) {
                    $departInfo[$k]['assnum_nums'] += 1;
                    $departInfo[$k]['assnum'][]    = $dv['assnum'];
                }
            }
        }
        $all_total_exam = 0;
        foreach ($departInfo as $k => $v) {
            $exam_where['cycid']          = $cycid;
            $exam_where['assnum']         = ['in', $v['assnum']];
            $exam_where['status']         = 1;//已验收
            $total_exam                   = $PatrolModel->DB_get_count('patrol_examine_one', $exam_where);
            $all_total_exam               += $total_exam;
            $departInfo[$k]['total_exam'] = $total_exam;
            $departInfo[$k]['not_exam']   = count($v['assnum']) - $total_exam;
            $departInfo[$k]['progress']   = $total_exam . ' / ' . $v['assnum_nums'];
            if (count($v['assnum']) == $total_exam) {
                $departInfo[$k]['bg'] = 'layui-bg-green';
            } else {
                $departInfo[$k]['bg'] = 'layui-bg-red';
            }
        }
        $cycleInfo['total_progress'] = $all_total_exam . ' / ' . $cycleInfo['assets_nums'];
        $cycleInfo['actual_num']     = $cycleInfo['implement_sum'] - count(json_decode($cycleInfo['not_operation_assnum']));

        //查询当前用户是否有验收权限
        $examine              = get_menu($this->MODULE, $this->CONTROLLER, 'examine');
        $overdue_num          = 0;
        $show_all_examine_btn = false;
        if ($cycleInfo['cycle_status'] >= 2) {
            //查询验收结果
            $exam_res = $PatrolModel->get_exam_res($cycid);
            //待实施状态，查询保养完成情况
            $fields            = "execid,cycid,assetnum,asset_status_num,status,report_num,finish_time,execute_user";
            $ab_where['cycid'] = $cycid;
            $res               = $PatrolModel->DB_get_all('patrol_execute', $fields, $ab_where);
            $assnum_exec_data  = [];
            foreach ($res as $v) {
                $assnum_exec_data[$v['assetnum']]['execid']           = $v['execid'];
                $assnum_exec_data[$v['assetnum']]['cycid']            = $v['cycid'];
                $assnum_exec_data[$v['assetnum']]['assetnum']         = $v['assetnum'];
                $assnum_exec_data[$v['assetnum']]['asset_status_num'] = $v['asset_status_num'];
                $assnum_exec_data[$v['assetnum']]['status']           = $v['status'];
                $assnum_exec_data[$v['assetnum']]['report_num']       = $v['report_num'];
                $assnum_exec_data[$v['assetnum']]['finish_time']      = $v['finish_time'];
                $assnum_exec_data[$v['assetnum']]['execute_user']     = $v['execute_user'];
                if (date('Y-m-d', strtotime($v['finish_time'])) > $cycleInfo['cycle_end_date']) {
                    $overdue_num++;
                }
            }
            foreach ($assets as $k => $asv) {
                $assets[$k]['execid']       = $assnum_exec_data[$asv['assnum']]['execid'];
                $assets[$k]['finish_time']  = $assnum_exec_data[$asv['assnum']]['finish_time'];
                $assets[$k]['execute_user'] = $assnum_exec_data[$asv['assnum']]['execute_user'];
                //查询异常项数量
                $abnormal_num = $PatrolModel->DB_get_one('patrol_execute_abnormal', 'count(*) as abnormal_num',
                    ['execid' => $assnum_exec_data[$asv['assnum']]['execid'], 'result' => ['neq', '合格']]);
                if ($abnormal_num['abnormal_num'] > 0) {
                    $abnormal_num['abnormal_num'] = '<span style="color:red;">' . $abnormal_num['abnormal_num'] . '</span>';
                }
                $assets[$k]['abnormal_details_num'] = $abnormal_num['abnormal_num'] . ' / ' . $asv['details_num'];
                $assets[$k]['report_num']           = $assnum_exec_data[$asv['assnum']]['report_num'];
                if ($assnum_exec_data[$asv['assnum']]['status'] == 2) {
                    //结果
                    if ($assnum_exec_data[$asv['assnum']]['asset_status_num'] == C('ASSETS_STATUS_NORMAL') || $assnum_exec_data[$asv['assnum']]['asset_status_num'] == C('ASSETS_STATUS_SMALL_PROBLEM')) {
                        $assets[$k]['result'] = '<span style="color: #009688;">合格</span>';
                    } elseif ($assnum_exec_data[$asv['assnum']]['asset_status_num'] == C('ASSETS_STATUS_NOT_OPERATION')) {
                        $assets[$k]['result'] = '<span style="color: #FFB800;">不保养</span>';
                    } else {
                        $assets[$k]['result'] = '<span style="color: red;">异常</span>';
                    }
                    if (in_array($asv['assnum'], $exam_res)) {
                        $assets[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-xs layui-btn-warm" lay-event="examine" data-url="' . $examine['actionurl'] . '">已验收</button>';
                    } else {
                        //只验收自己管理科室的设备
                        if ($examine && in_array($asv['departid'], $departids)) {
                            $show_all_examine_btn    = true;
                            $assets[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-xs" lay-event="examine" data-url="' . $examine['actionurl'] . '">待验收</button>';
                        } else {
                            $assets[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-xs layui-btn-disabled">待验收</button>';
                        }
                    }
                }
            }
        }
        switch ($patrol_info['patrol_level']) {
            case C('PATROL_LEVEL_PM'):
                $this->assign('template_name', '巡查模板');
                $this->assign('result_name', '巡检结果');
                $this->assign('status_name', '巡检');
                break;
            default:
                $this->assign('template_name', '保养模板');
                $this->assign('result_name', '保养结果');
                $this->assign('status_name', '保养');
                break;
        }
        if ($cycleInfo['check_status'] != 1) {
            if ($show_all_examine_btn) {
                $this->assign('canExamine', 1);
            }
        } else {
            //获取验收信息
            $allexam_info = $PatrolModel->DB_get_one('patrol_examine_all', 'exam_user,exam_time,remark',
                ['cycid' => $cycid], 'exallid desc');
            $this->assign('allexam_info', $allexam_info);
        }
        $this->assign('url', C('ADMIN_NAME') . '/Patrol/examine.html');
        $this->assign('overdue_num', $overdue_num);
        $this->assign('plan_num', $cycleInfo['assets_nums']);
        $this->assign('cycleInfo', $cycleInfo);
        $this->assign('department', $departInfo);
        $this->assign('assets', json_encode($assets));
        $this->assign('showPlans', get_url());
        $this->display();
    }

    //验收单台设备
    private function showExamineOne()
    {
        $execid = I('get.execid');
        if (!$execid) {
            $this->error('参数错误');
            exit;
        }
        //查询报验记录信息
        $PatrolExamineAllModel = new PatrolExamineAllModel();
        $executeData           = $PatrolExamineAllModel->get_execute_info($execid);
        if ($executeData['status'] < 2) {
            //未保养完成，不允许验收
            $this->error('保养未完成');
            exit;
        }
        //查询验收信息
        $examineData         = $PatrolExamineAllModel->get_one_examine_info($executeData['cycid'],
            $executeData['assetnum']);
        $asArr               = $PatrolExamineAllModel->getAssets($executeData['assetnum']);
        $PatrolExecuteModel  = new PatrolExecuteModel();
        $AssetsTemplateModel = new AssetsTemplateModel();
        $abnormalArr         = $PatrolExecuteModel->getAbnormal($execid);
        $pointsArr           = '';
        foreach ($abnormalArr as &$one) {
            $pointsArr .= ',' . $one['ppid'];
        }
        $pointsArr      = trim($pointsArr, ',');
        $asArr['count'] = substr_count($pointsArr, ',') + 1;
        $cate           = $AssetsTemplateModel->getIniPoints($pointsArr, $abnormalArr, 1);
        $this->assign('data', $cate);
        $this->assign('asArr', $asArr);
        $this->assign('action', 'examineOne');
        $this->assign('url', C('ADMIN_NAME') . '/Patrol/examine.html');
        $this->assign('executeData', $executeData);
        $this->assign('examineData', $examineData);
        $this->display('examineOne');
    }


    public function test()
    {
        G('begin');
        $patrolPlan       = new PatrolPlanModel();
        $res              = $patrolPlan->getPlansByTest();
        $result["total"]  = $res['total'];
        $result["offset"] = $res['offset'];
        $result["limit"]  = $res['limit'];
        $result["rows"]   = $res['patrol'];
        //G('end');
        //echo G('begin','end').'s';exit;
        //$this->ajaxReturn($result, 'json');
        $this->display();
    }

    /**
     * Notes: 巡查计划审核列表
     */
    public function patrolApprove()
    {
        if (IS_POST) {
            $PatrolModel = new PatrolModel();
            $result      = $PatrolModel->get_approve_lists();
            $this->ajaxReturn($result);
        } else {
            //查询巡查审批是否已开启
            $isOpenApprove = $this->checkApproveIsOpen(C('PATROL_APPROVE'), session('current_hospitalid'));
            if (!$isOpenApprove) {
                $this->assign('errmsg', '巡查审批未开启，如需开启，请联系管理员！');
                $this->display('Public/error');
            } else {
                $this->assign('patrolApprove', get_url());
                $this->display();
            }
        }
    }

    /**
     * Notes: 审核
     */
    public function approve()
    {
        if (IS_POST) {
            $PatrolModel = new PatrolModel();
            $result      = $PatrolModel->save_approve();
            $this->ajaxReturn($result);
        }
    }

    /*
    删除巡查计划
     */
    public function deletePatrol()
    {
        if (IS_POST) {
            $PatrolModel = new PatrolModel();
            $result      = $PatrolModel->del_patrol();
            $this->ajaxReturn($result);
        }
    }

    /*
     * 查看
     */
    public function showPlans()
    {
        $PatrolModel = new PatrolModel();
        $action      = I('get.action');
        switch ($action) {
            case 'showTemplate':
                $tpid = I('get.id');
                if (!$tpid) {
                    exit('参数非法');
                }
                //实例化模型
                $patrolModel = new PatrolModel();
                //获取当前模板名称信息
                $tpInfo     = $patrolModel->DB_get_one('patrol_template', 'name,remark,points_num', ['tpid' => $tpid]);
                $points_num = json_decode($tpInfo['points_num']);
                if ($points_num) {
                    $points = $patrolModel->DB_get_all('patrol_points', '', ['num' => ['IN', $points_num]], '', '', '');
                } else {
                    exit('该模板存在错误');
                }

                $parentid = [];
                foreach ($points as $k => $v) {
                    if (!in_array($v['parentid'], $parentid)) {
                        array_push($parentid, $v['parentid']);
                    }
                }
                $pointCat = $patrolModel->DB_get_all('patrol_points', '',
                    ['parentid' => 0, 'ppid' => ['IN', $parentid]]);
                foreach ($pointCat as $k => $v) {
                    foreach ($points as $k1 => $v1) {
                        if ($v1['parentid'] == $v['ppid']) {
                            $pointCat[$k]['detail'][] = $v1;
                        }
                    }
                }
                $this->assign('tpInfo', $tpInfo);
                $this->assign('points_num', $points_num);
                $this->assign('data', $pointCat);
                $this->display('Patrol/PatrolSetting/showTemplate');
                break;
            case 'detail':
                $cycid = I('get.cycid');
                $res   = $PatrolModel->get_cycle_data($cycid);
                $this->assign('assets', json_encode($res['assInfo']));
                $this->assign('department', $res['departInfo']);
                $this->assign('cycleInfo', $res['cycleInfo']);
                $this->display('cyclePlansDetail');
                break;
            default:
                $patrid = I('get.id');
                //查询计划信息
                $patrol_info = $PatrolModel->get_plan_info($patrid);
                //查询计划设备列表
                $assets = $PatrolModel->get_plan_assets($patrol_info);
                if ($patrol_info['patrol_status'] >= 3) {
                    $plans = $PatrolModel->get_plan_cycle($patrid);
                    if ($patrol_info['is_cycle'] == 1 && ($patrol_info['current_cycle'] < $patrol_info['total_cycle']) && (date('Y-m-d') > $plans[0]['cycle_end_date'])) {
                        $this->assign('show_create_next_cycle', 1);
                    }
                    $this->assign('get_plan_cycle', 1);
                    $this->assign('plans', json_encode($plans));
                } else {
                    $this->assign('plans', json_encode([]));
                    $this->assign('get_plan_cycle', 0);
                }
                //查询当前用户是否有执行权限
                $doTask       = get_menu($this->MODULE, $this->CONTROLLER, 'doTask');
                $is_super     = session('isSuper');
                $cur_username = session('username');
                $cycids       = [];
                //判断是否开启签到
                $baseSetting = [];
                include APP_PATH . "Common/cache/basesetting.cache.php";
                if ($baseSetting['patrol']['patrol_wx_set_situation']['value'] == C('OPEN_STATUS')) {
                    $wx_sign_in = 1;
                } else {
                    $wx_sign_in = 0;
                }
                foreach ($assets as $k => $v) {
                    $cycids[]                = $v['cycid'];
                    $assets[$k]['operation'] = '';
                    if ($is_super) {
                        //超级管理员
                        $assets[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">待保养</button>';
                    } else {
                        $assets[$k]['need_sign'] = false;
                        //不是超级管理员，判断是否是该设备的执行人
                        if ($assets[$k]['executor'] == $cur_username) {
                            if ($doTask) {
                                if ($wx_sign_in == 1) {
                                    //有开启的话 可以签到保养
                                    $assets[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="sign">需要签到</button>';
                                    $assets[$k]['need_sign'] = true;
                                    if ($v['sign_info']) {
                                        $arr = json_decode($v['sign_info'], true);
                                        if (isset($arr[$v['assnum']])) {
                                            $assets[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">待保养</button>';
                                            $assets[$k]['need_sign'] = false;
                                        }
                                    }
                                } else {
                                    $assets[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">待保养</button>';
                                }
                            } else {
                                $assets[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-disabled layui-btn-xs">待保养</button>';
                            }
                        } else {
                            $assets[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-disabled layui-btn-xs">待保养</button>';
                        }
                    }
                }
                if ($patrol_info['patrol_status'] >= 4) {
                    //待实施状态，查询保养完成情况
                    $fields            = "execid,cycid,assetnum,asset_status_num,status";
                    $ab_where['cycid'] = ['in', $cycids];
                    $res               = $PatrolModel->DB_get_all('patrol_execute', $fields, $ab_where);
                    foreach ($res as $k => $v) {
                        foreach ($assets as $ask => $asv) {
                            if ($v['cycid'] == $asv['cycid'] && $v['assetnum'] == $asv['assnum']) {
                                if ($v['status'] == 2) {
                                    //已完成
                                    if ($v['asset_status_num'] == C('ASSETS_STATUS_NORMAL') || $v['asset_status_num'] == C('ASSETS_STATUS_SMALL_PROBLEM')) {
                                        $assets[$ask]['operation'] = '<button type="button" class="layui-btn layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">合格</button>';
                                    } elseif ($v['asset_status_num'] == C('ASSETS_STATUS_NOT_OPERATION')) {
                                        $assets[$ask]['operation'] = '<button type="button" class="layui-btn layui-btn-warm layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">不保养</button>';
                                    } else {
                                        $assets[$ask]['operation'] = '<button type="button" class="layui-btn layui-btn-danger layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">异常</button>';
                                    }
                                }
                            }
                        }
                    }
                }
                if ($patrol_info['approve_status'] != -1) {
                    //查询审批信息
                    $apps = $PatrolModel->get_approve_info($patrid);
                    $this->assign('apps', $apps);
                }
                switch ($patrol_info['patrol_level']) {
                    case 3:
                        $this->assign('pre_date_name', '上一次巡查日期');
                        $this->assign('detail_name', '巡检明细(项)');
                        $this->assign('template_name', '巡查模板');
                        break;
                    default:
                        $this->assign('pre_date_name', '上一次保养日期');
                        $this->assign('detail_name', '保养明细(项)');
                        $this->assign('template_name', '保养模板');
                        break;
                }
                switch ($patrol_info['patrol_status']) {
                    case '1'://待审核
                        $appprove   = get_menu($this->MODULE, $this->CONTROLLER, 'approve');
                        $canApprove = false;
                        if ($appprove) {
                            if ($patrol_info['approve_status'] == C('OUTSIDE_STATUS_APPROVE')) {
                                // 0 待审核状态
                                if ($patrol_info['current_approver']) {
                                    $current_approver     = explode(',', $patrol_info['current_approver']);
                                    $current_approver_arr = [];
                                    foreach ($current_approver as &$current_approver_value) {
                                        $current_approver_arr[$current_approver_value] = true;
                                    }
                                    if ($current_approver_arr[session('username')]) {
                                        $canApprove = true;
                                    }
                                }
                            }
                        }
                        $this->assign('canApprove', $canApprove);
                        $this->assign('username', session('username'));
                        $this->assign('date', date('Y-m-d'));
                        break;
                }
                $this->assign('patrol_info', $patrol_info);
                $this->assign('assets', json_encode($assets));
                $this->assign('showPlans', get_url());
                $this->display();
                break;
        }
    }

}
