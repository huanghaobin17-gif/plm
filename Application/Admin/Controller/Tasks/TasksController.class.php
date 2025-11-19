<?php

/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/4/10
 * Time: 15:56
 */

namespace Admin\Controller\Tasks;

use Admin\Model\AssetsInfoModel;
use Admin\Model\AssetsInsuranceModel;
use Admin\Model\ModuleModel;
use Admin\Model\OfflineSuppliersModel;
use Admin\Model\PatrolPlanModel;
use Admin\Model\QualityModel;
use Admin\Model\RepairModel;
use Admin\Model\UserModel;
use Admin\Model\PatrolExecuteModel;
use Mobile\Model\WxAccessTokenModel;
use Pop3;

class TasksController
{
    //每天凌晨调用此接口生成新的周期任务
    public function create_today_patrol_cycle_plans()
    {
        $patrolPlanModel = new PatrolPlanModel();
        $where['is_cycle'] = 1;
        $where['patrol_status'] = ['in', '3,4'];
        $where['is_delete'] = 0;
        $planInfo = $patrolPlanModel->DB_get_all('patrol_plans', '*', $where, '', 'patrid desc');
        foreach ($planInfo as $v) {
            $patrolPlanModel->create_next_plan($v['patrid']);
        }
        return true;
    }

    //每天早上8:30发送微信消息提醒工程师去完成巡查任务
    public function send_wechat_todo_patrol_cycle_plans()
    {
        $patrolPlanModel = new PatrolPlanModel();
        $start_time = date('Y-m-d') . ' 00:00:01';
        $end_time = date('Y-m-d') . ' 23:59:59';
        $where['A.cycle_status'] = ['in', '0,1,4'];
        $where['A.create_time'] = array(array('EGT', $start_time), array('ELT', $end_time), 'and');//只提醒当天创建的任务
        $field = 'A.patrid,A.cycid,A.period,A.assets_departid,A.cycle_start_date,A.cycle_end_date,A.cycle_status,A.create_time,B.patrol_name,B.patrol_level,B.hospital_id';
        $join = 'LEFT JOIN sb_patrol_plans AS B ON A.patrid = B.patrid';
        $cycleInfo = $patrolPlanModel->DB_get_all_join('patrol_plans_cycle', 'A', $field, $join, $where, '', 'A.cycid desc');
        foreach ($cycleInfo as $k => $v) {
            $depart_ids = explode(',', $v['assets_departid']);
            $patrolPlanModel->send_wechat_then_create_next_plans($depart_ids, $v['patrid'], $v['patrol_name'], $v['cycle_start_date'], $v['cycle_end_date'], $v['hospital_id']);
        }
    }

    public function changeNumberRules()
    {
        //维修模块
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        //维修 START===========================================
        $wx = $baseSetting['repair']['repair_encoding_rules']['value'];
        //暂时未需要配置默认下划线 如果需要配置 这里读配置 todo
        $wx_cut = '_';
        $this->doChangeNumber('repair', 'adddate', 'repnum', $wx['prefix'], $wx_cut);
        //维修 END=============================================
//        $this->doChangeNumber('repair','adddate','repnum',$wx['prefix'],$wx_cut);
    }

    /**
     * @param string $table 表名
     * @param string $field 记录时间的字段
     * @param string $saveField 需要修改的编号字段名
     * @param string $prefix 前缀
     * @param string $cut 分割符
     * */
    private function doChangeNumber($table, $field, $saveField, $prefix = '', $cut = '')
    {
        $RepairModel = new RepairModel();
        $data = $RepairModel->DB_get_all($table, $field . ',' . $saveField, '', '', $field . ' ASC');
        $num = 1;
        $year = 0;
        foreach ($data as &$one) {
            if ($year == 0) {
                //第一条
                $save[$saveField] = $prefix . date('Ymd', $one[$field]) . $cut . sprintf("%04d", $num);
                $year = date('Y', $one[$field]);
            } else {
                if (date('Y', $one[$field]) != $year) {
                    //不是同一年份的 重新统计
                    $year = date('Y', $one[$field]);
                    $num = 1;
                    $save[$saveField] = $prefix . date('Ymd', $one[$field]) . $cut . sprintf("%04d", $num);
                } else {
                    $save[$saveField] = $prefix . date('Ymd', $one[$field]) . $cut . sprintf("%04d", ++$num);
                }
            }
            $RepairModel->updateData($table, $save, [$saveField => $one[$saveField]]);
        }
    }


    /**
     * 监控设备质控计划完成情况并生成新的周期质控计划
     */
    public function addNewQualityPlan()
    {
        $qualityModel = new QualityModel();
        $result = $qualityModel->addNewPlanForTask();
        echo json_encode($result);
    }

    //质控提醒逾期/反馈今日质控情况
    public function feedbackQualityPlan()
    {
        $qualityModel = new QualityModel();
        $qualityModel->feedbackQualityPlan();
    }

    /*
     * 维保模块 设备维保状态更新
     * */
    public function changeRenewalStatus()
    {
        $InsuranceModel = new AssetsInsuranceModel();
        $sql = "UPDATE `sb_assets_insurance` SET `status` = CASE";
        $sql .= ' WHEN `startdate` < ' . time() . ' AND ' . time() . ' < `overdate` THEN ' . C('INSURANCE_STATUS_USE');//再用
        $sql .= ' WHEN `overdate` < ' . time() . ' THEN ' . C('INSURANCE_STATUS_DE_PAUL');//脱保
        $sql .= ' WHEN ' . time() . '< `startdate`  THEN ' . C('INSURANCE_STATUS_NOT_RIGHT_NOW');//未到维保日期
        $sql .= ' END';
        $InsuranceModel->execute($sql);
    }


    /*
     * 获取任务
     * */
    public function getTask()
    {
        $task = [];
        $indexResult = [];
        //管理科室 包括 管理/工作科室(工作医院管理医院)
        $departid = session('departid');
        //工作科室
        $job_departid = session('job_departid');
        //工作医院
        $job_hospitalid = session('job_hospitalid');
        //管理科室数组
        $manager_departid_arr = explode(',', $departid);
        //用户名
        $username = session('username');
        $num = 0;
        $indexResultNum = 0;
        $taskCount = 0;

        //查询所在医院下的任务
        $RepairModel = new RepairModel();
        if (!session('job_hospitalid')) {
            return false;
        }

        if (!session('current_hospitalid')) {
            return false;
        }

        $departid && $assetsWhere['departid'] = array('IN', $departid);
        $assetsWhere['hospital_id'] = array('IN', session('current_hospitalid'));
        $assets = $RepairModel->DB_get_all('assets_info', 'assid,departid,hospital_id', $assetsWhere);
        if (!$assets) {
            return false;
        } else {
            $all_assids = [];
            $all_assids = array_column($assets, 'assid');

            $all_patrass = $RepairModel->DB_get_all('patrol_plans_assets', 'patrid', ['assid' => array('in', $all_assids)]);
            $all_patrass && $patrids = array_column(array_unique($all_patrass, SORT_REGULAR), 'patrid');
        }

        //所工作的科室下的全部设备
        $job_assid = [];
        //所工作医院下的全部设备
        $job_hospital_assid = [];
        //所管理下的医院全部设备
        $manager_assid = [];
        //工作医院下所管理的科室的设备
        $job_hospital_manager_assid = [];

        foreach ($assets as &$assetsValue) {
            if ($job_departid == $assetsValue['departid']) {
                $job_assid[] = $assetsValue['assid'];
            }
            if ($job_hospitalid == $assetsValue['hospital_id']) {
                $job_hospital_assid[] = $assetsValue['assid'];
                if (in_array($assetsValue['departid'], $manager_departid_arr)) {
                    $job_hospital_manager_assid[] = $assetsValue['assid'];
                }
            }
            if (in_array($assetsValue['departid'], $manager_departid_arr)) {
                $manager_assid[] = $assetsValue['assid'];
            }
        }
        if (!$manager_assid) {
            return false;
        }
        //维修接单
        $acceptMenu = get_menu('Repair', 'Repair', 'accept');
        $RepairModel = new RepairModel();
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        if ($acceptMenu && $manager_assid) {
            $where[1]['status'] = array('EQ', C('REPAIR_HAVE_REPAIRED'));
            $where[1]['assid'] = array('IN', $manager_assid);
            if (!session('isSuper')) {
//                $where .= " AND (is_assign=0 OR ISNULL(assign_engineer) OR assign_engineer='$username') AND (ISNULL(response) OR response='$username')";
                $where[2][1][]['is_assign'] = array('EQ', C('NO_STATUS'));
                $where[2][1][]['assign_engineer'][] = array('EXP', 'IS NULL');
                $where[2][1][]['assign_engineer'][] = array('EQ', $username);
                $where[2][1]['_logic'] = 'OR';

                $where[3][1][]['response'][] = array('EXP', 'IS NULL');
                $where[3][1][]['response'][] = array('EQ', $username);
                $where[3][1]['_logic'] = 'OR';
            }
            $count = $RepairModel->DB_get_count('repair', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('维修接单', ' ', '/Repair/ordersLists', '', C('TASK_REPAIR_COLOR'));
                $val['num'] = $count;
                $task['维修管理'][] = $val;
                if ($this->checkIndexShow('repairAccept', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '维修待接单';
                    $indexResult[$indexResultNum]['aLink'] = '/Repair/ordersLists';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }


        //未接单派工
        $assignedMenu = get_menu('Repair', 'Repair', 'assigned');
        if ($assignedMenu && $manager_assid) {
            $where = [];
            $where['status'] = array('EQ', C('REPAIR_HAVE_REPAIRED'));
            $where['assid'] = array('IN', $manager_assid);
            $count = $RepairModel->DB_get_count('repair', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('未接单派工', ' ', '/Repair/dispatchingLists', '', C('TASK_REPAIR_COLOR'));
                $val['num'] = $count;
                $task['维修管理'][] = $val;
                if ($this->checkIndexShow('repairAssigned', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '维修待派工';
                    $indexResult[$indexResultNum]['aLink'] = '/Repair/dispatchingLists';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //维修处理检修
        if ($acceptMenu && $manager_assid) {
            $where = [];
            $where['status'] = array('EQ', C('REPAIR_RECEIPT'));
            if (!session('isSuper')) {
                $where['response'] = array('EQ', $username);
            }
            $where['assid'] = array('IN', $manager_assid);
            $count = $RepairModel->DB_get_count('repair', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('维修处理(检修)', ' ', 'Repair/getRepairLists', '', C('TASK_REPAIR_COLOR'));
                $val['num'] = $count;
                $task['维修管理'][] = $val;
                if ($this->checkIndexShow('repairOverhaul', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '维修待检修';
                    $indexResult[$indexResultNum]['aLink'] = '/Repair/getRepairLists';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }


        //维修处理
        $startRepairMenu = get_menu('Repair', 'Repair', 'startRepair');
        if ($startRepairMenu && $manager_assid) {
            $where = [];
            $where['status'] = array('EQ', C('REPAIR_MAINTENANCE'));
            if (!session('isSuper')) {
                $where['response'] = array('EQ', $username);
            }
            $where['assid'] = array('IN', $manager_assid);
            $count = $RepairModel->DB_get_count('repair', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('维修处理', ' ', 'Repair/getRepairLists', '', C('TASK_REPAIR_COLOR'));
                $val['num'] = $count;
                $task['维修管理'][] = $val;
                if ($this->checkIndexShow('startRepair', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '维修待处理';
                    $indexResult[$indexResultNum]['aLink'] = '/Repair/getRepairLists';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //报价
        $doOfferMenu = get_menu('Repair', 'Repair', 'doOffer');
        if ($doOfferMenu && $manager_assid) {
            $where = [];
            $where[1]['status'] = array('EQ', C('REPAIR_QUOTATION'));
            $where[1]['_logic'] = 'OR';
            $where[2]['assid'] = array('IN', $manager_assid);
            $count = $RepairModel->DB_get_count('repair', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('报价', ' ', '/Repair/unifiedOffer', '', C('TASK_REPAIR_COLOR'));
                $val['num'] = $count;
                $task['维修管理'][] = $val;
                if ($this->checkIndexShow('repairDoOffer', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '维修待报价';
                    $indexResult[$indexResultNum]['aLink'] = '/Repair/unifiedOffer';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //维修审批
        $approveMenu = get_menu('Repair', 'Repair', 'addApprove');
        if ($approveMenu && $manager_assid) {
            $where = [];
            $where['assid'] = array('IN', $manager_assid);


            $where['all_approver'] = array('LIKE', '%/' . session('username') . '/%');


            $where['approve_status'] = array('EQ', C('APPROVE_STATUS'));
            $data = $RepairModel->DB_get_all('repair', 'current_approver', $where);
            if ($data) {
                $count = 0;
                foreach ($data as &$repair_approve_v) {
                    $current_approver = explode(',', $repair_approve_v['current_approver']);
                    foreach ($current_approver as &$repair_approver_value) {
                        if ($repair_approver_value == session('username')) {
                            $count++;
                        }
                    }
                }
                if ($count >= 1) {
                    $taskCount += $count;
                    $val['title'] = $RepairModel->returnTaskALink('维修审批', ' ', '/Repair/repairApproveLists', '', C('TASK_REPAIR_COLOR'));
                    $val['num'] = $count;
                    $task['维修管理'][] = $val;
                    if ($this->checkIndexShow('addRepairApprove', $baseSetting)) {
                        $indexResult[$indexResultNum]['name'] = '维修待审批';
                        $indexResult[$indexResultNum]['aLink'] = '/Repair/repairApproveLists';
                        $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                        $indexResult[$indexResultNum]['num'] = $count;
                        $indexResultNum++;
                    }
                    $num++;
                }
            }
        }
        //附属设备分配审批
        $subsidiaryMunu = get_menu('Assets', 'Subsidiary', 'subsidiaryApproveList');
        if ($subsidiaryMunu && $manager_assid) {
            $where = [];
            $where['assid'] = array('IN', $manager_assid);


            $where['all_approver'] = array('LIKE', '%/' . session('username') . '/%');


            $where['approve_status'] = array('EQ', C('APPROVE_STATUS'));
            $data = $RepairModel->DB_get_all('subsidiary_allot', 'current_approver', $where);
            if ($data) {
                $count = 0;
                foreach ($data as &$subsidiary_approve_v) {
                    $current_approver = explode(',', $subsidiary_approve_v['current_approver']);
                    foreach ($current_approver as &$subsidiary_approver_value) {
                        if ($subsidiary_approver_value == session('username')) {
                            $count++;
                        }
                    }
                }
                if ($count >= 1) {
                    $taskCount += $count;
                    $val['title'] = $RepairModel->returnTaskALink('附属设备分配审批', ' ', '/Subsidiary/subsidiaryApproveList', '', C('TASK_ASSETS_COLOR'));
                    $val['num'] = $count;
                    $task['设备管理'][] = $val;
                    if ($this->checkIndexShow('addRepairApprove', $baseSetting)) {
                        $indexResult[$indexResultNum]['name'] = '分配待审批';
                        $indexResult[$indexResultNum]['aLink'] = '/Subsidiary/subsidiaryApproveList';
                        $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                        $indexResult[$indexResultNum]['num'] = $count;
                        $indexResultNum++;
                    }
                    $num++;
                }
            }
        }

        //附属设备待验收  标记
        $s_CheckListMunu = get_menu('Assets', 'Subsidiary', 'subsidiaryCheckList');
        if ($s_CheckListMunu && $manager_assid) {
            $where = [];
            $where['status'] = array('EQ', '1');
            $where['main_departid'] = array('IN', $departid);
            $count = $RepairModel->DB_get_count('subsidiary_allot', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('附属设备分配待验收', ' ', '/Subsidiary/subsidiaryCheckList', '', C('TASK_ASSETS_COLOR'));
                $val['num'] = $count;
                $task['设备管理'][] = $val;
                if ($this->checkIndexShow('subsidiaryCheckList', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '分配待验收';
                    $indexResult[$indexResultNum]['aLink'] = '/Subsidiary/subsidiaryCheckList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }
        //待验收
        $checkMenu = get_menu('Repair', 'Repair', 'examine');
        if (($startRepairMenu or $checkMenu) && $manager_assid) {
            $where = [];
            $where['status'] = array('EQ', C('REPAIR_MAINTENANCE_COMPLETION'));
            if (!session('isSuper')) {
                if ($startRepairMenu) {
                    $where['response'] = array('EQ', $username);
                }
            }
            $where['assid'] = array('IN', $manager_assid);
            $count = $RepairModel->DB_get_count('repair', $where);
            if ($count >= 1) {
                $taskCount += $count;
                if ($startRepairMenu) {
                    //已修复待验收-面向维修人员
                    if ($this->checkIndexShow('repairEngineerCheck', $baseSetting)) {
                        $indexResult[$indexResultNum]['name'] = '维修结束待验收';
                        $indexResult[$indexResultNum]['aLink'] = '/Repair/examine';
                        $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                        $indexResult[$indexResultNum]['num'] = $count;
                        $indexResultNum++;
                    }
                    $val['title'] = $RepairModel->returnTaskALink('修复待验收', ' ', '/Repair/examine', '', C('TASK_REPAIR_COLOR'));
                } elseif ($checkMenu) {
                    //已修复待验收-面向报修验收人员
                    if ($this->checkIndexShow('repairCheck', $baseSetting)) {
                        $indexResult[$indexResultNum]['name'] = '维修待验收';
                        $indexResult[$indexResultNum]['aLink'] = '/Repair/examine';
                        $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_ORANGE');
                        $indexResult[$indexResultNum]['num'] = $count;
                        $indexResultNum++;
                    }
                    $val['title'] = $RepairModel->returnTaskALink('待验收', ' ', '/Repair/examine', '', C('TASK_REPAIR_COLOR'));
                }
                $val['num'] = $count;
                $val['indexShow'] = true;
                $task['维修管理'][] = $val;
                $num++;
            }
        }

        //确认报修（巡查过来的报修）
        $addRepairMenu = get_menu('Repair', 'Repair', 'addRepair');
        if ($addRepairMenu) {
            $planWhere['P.hospital_id'] = array('IN', session('current_hospitalid'));
            $planJoin = 'LEFT JOIN sb_patrol_plans AS P ON P.patrid=C.patrid';
            $cycidData = $RepairModel->DB_get_all_join('patrol_plans_cycle', 'C', 'C.cycid', $planJoin, $planWhere);
            if ($cycidData) {
                $where = [];
                $cycid = [];
                foreach ($cycidData as &$cycidValue) {
                    $cycid[] = $cycidValue['cycid'];
                }

                $where['status'] = array('EQ', C('NOTHING_STATUS'));
                if (!session('isSuper')) {
                    $where['applicant'] = array('EQ', $username);
                }
                $where['cycid'] = array('IN', $cycid);
                $count = $RepairModel->DB_get_count('confirm_add_repair', $where);
                if ($count >= 1) {
                    $taskCount += $count;
                    $val['title'] = $RepairModel->returnTaskALink('确认报修', ' ', '/Repair/getAssetsLists?action=confirmAddRepairList', '', C('TASK_REPAIR_COLOR'));
                    $val['num'] = $count;
                    $task['维修管理'][] = $val;
                    if ($this->checkIndexShow('addRepair', $baseSetting)) {
                        $indexResult[$indexResultNum]['name'] = '维修待确认报修';
                        $indexResult[$indexResultNum]['aLink'] = '/Repair/getAssetsLists?action=confirmAddRepairList';
                        $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                        $indexResult[$indexResultNum]['num'] = $count;
                        $indexResultNum++;
                    }
                    $num++;
                }
            }
        }
        //巡查计划审批
        $approvePatrolMenu = get_menu('Patrol', 'Patrol', 'approve');
        if ($approvePatrolMenu) {
            $planWhere = [];
            $planWhere['hospital_id'] = array('IN', session('current_hospitalid'));
            if (!session('isSuper')) {
                $planWhere['current_approver'] = array('like', session('username') . ',%');
            }
            $planWhere['patrol_status'] = 1;
            $data = $RepairModel->DB_get_all('patrol_plans', 'patrid', $planWhere);
            $count = count($data);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('巡查计划审批', ' ', '/Patrol/patrolApprove', '', C('TASK_PATROL_COLOR'));
                $val['num'] = $count;
                $task['巡查保养管理'][] = $val;
                if ($this->checkIndexShow('releasePatrol', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '有计划等待您的审批';
                    $indexResult[$indexResultNum]['aLink'] = '/Patrol/patrolApprove';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_ORANGE');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //下一次计划发布将到
        $releasePatrolMenu = get_menu('Patrol', 'Patrol', 'releasePatrol');
        if ($releasePatrolMenu) {
            $planWhere = [];
            $planWhere['hospital_id'] = session('current_hospitalid');
            $planWhere['patrol_status'] = '2';
            $day = $baseSetting['patrol']['patrol_reminding_day']['value'];
            $patrol_reminding_day = strtotime("+ $day day");
            $planWhere['patrol_start_date'] = array('ELT', getHandleTime($patrol_reminding_day));
            $plan = $RepairModel->DB_get_all('patrol_plans', 'patrid', $planWhere);
            if ($plan && $day) {
                $patrid = [];
                foreach ($plan as &$planValue) {
                    $patrid[] = $planValue['patrid'];
                }

                $where = [];
                $where['patrid'] = array('IN', $patrid);
                $data = $RepairModel->DB_get_all('patrol_plans_cycle', 'cycid', $where);
                $count = count($data);
                if ($count >= 1) {
                    $taskCount += $count;
                    $val['title'] = $RepairModel->returnTaskALink('计划发布将到', ' ', '/Patrol/patrolList', '', C('TASK_PATROL_COLOR'));
                    $val['num'] = $count;
                    $task['巡查保养管理'][] = $val;
                    if ($this->checkIndexShow('releasePatrol', $baseSetting)) {
                        $indexResult[$indexResultNum]['name'] = '计划发布将到';
                        $indexResult[$indexResultNum]['aLink'] = '/Patrol/patrolList';
                        $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_ORANGE');
                        $indexResult[$indexResultNum]['num'] = $count;
                        $indexResultNum++;
                    }
                    $num++;
                }
            }
        }

        //巡查任务
        $doTaskMenu = get_menu('Patrol', 'Patrol', 'doTask');
        if ($doTaskMenu) {
            //待执行保养任务
            $where = [];
            $where['A.cycle_status'] = ['in', '0,1,4'];//待执行、执行中、逾期未完成
            $where['B.hospital_id'] = session('current_hospitalid');
            $where['B.is_delete'] = '0';
            if ($patrids) {
                $where['B.patrid'] = ['in', $patrids];
            }

            $join = "LEFT JOIN sb_patrol_plans AS B ON B.patrid=A.patrid";
            $data = $RepairModel->DB_get_all_join('patrol_plans_cycle', 'A', 'B.patrol_end_date', $join, $where);
            $count = count($data);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('待执行保养任务', ' ', '/Patrol/tasksList', '', C('TASK_PATROL_COLOR'));
                $val['num'] = $count;
                $task['巡查保养管理'][] = $val;
                if ($this->checkIndexShow('patrolDoTask', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '待执行保养任务';
                    $indexResult[$indexResultNum]['aLink'] = '/Patrol/tasksList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
            $day = $baseSetting['patrol']['patrol_soon_expire_day']['value'];
            $patrol_soon_expire_day = 0;//提醒日期范围
            if ($day) {
                $patrol_soon_expire_day = getHandleTime(strtotime("+ $day day"));
            }
            $soon_expire_count = 0;//快到期数量
            $overdue_count = 0;//逾期数量
            $Today = getHandleTime(time());
            foreach ($data as &$one) {
                if ($one['patrol_end_date'] <= $patrol_soon_expire_day) {
                    $soon_expire_count++;
                }
                if ($one['patrol_end_date'] < $Today) {
                    $overdue_count++;
                }
            }
            //任务即将到期
            if ($soon_expire_count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('任务即将到期', ' ', '/Patrol/tasksList', '', C('TASK_PATROL_COLOR'));
                $val['num'] = $soon_expire_count;
                $task['巡查保养管理'][] = $val;
                if ($this->checkIndexShow('patrolTaskSoonExpire', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '任务即将到期';
                    $indexResult[$indexResultNum]['aLink'] = '/Patrol/tasksList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_ORANGE');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
            //任务逾期
            if ($overdue_count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('任务逾期', ' ', '/Patrol/tasksList', '', C('TASK_PATROL_COLOR'));
                $val['num'] = $overdue_count;
                $task['巡查保养管理'][] = $val;
                if ($this->checkIndexShow('patrolTaskExpire', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '任务逾期';
                    $indexResult[$indexResultNum]['aLink'] = '/Patrol/tasksList';
                    $indexResult[$indexResultNum]['color'] = 'red';
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }

        }

        //保养完成待验收
        $examineMenu = get_menu('Patrol', 'Patrol', 'examine');
        if ($examineMenu && $departid) {
            $where = [];
            $where['cycle_status'] = ['IN', '2,3'];
            $where['check_status'] = 0;
            if (session('isSuper')) {
                $count = $RepairModel->DB_get_count('patrol_plans_cycle', $where);
            } else {
                $username = session('username');
                $pemodel = new PatrolExecuteModel();
                $menuid = $pemodel->menuid('examine', 'Patrol', 'Patrol');
                $sql = "SELECT E.assnum from sb_user as A LEFT JOIN sb_user_department B ON A.userid=B.userid LEFT JOIN sb_user_role C ON A.userid=C.userid LEFT JOIN sb_role_menu D ON D.roleid=C.roleid LEFT JOIN sb_assets_info E ON E.departid=B.departid  where D.menuid='" . $menuid . "' and A.username='$username' and E.assnum!='' GROUP BY E.assnum";
                $data = $RepairModel->query($sql);
                $data = array_column($data, 'assnum');
                if ($patrids){
                    $where['B.patrid'] = ['in', $patrids];
                }

                $join = "LEFT JOIN sb_patrol_plans AS B ON B.patrid=A.patrid";
                $plan_data = $RepairModel->DB_get_all_join('patrol_plans_cycle', 'A', 'A.plan_assnum,A.patrid', $join, $where);
//                $plan_data = $RepairModel->DB_get_all('patrol_plans_cycle', 'plan_assnum,patrid', $where);
//                var_dump(M()->getLastSql());exit;
                $assnum = array();
                foreach ($plan_data as $key => $value) {
                    $patrol_assnums = json_decode($value['plan_assnum']);
                    foreach ($patrol_assnums as $k => $v) {
                        $assnum[$v] = $value['patrid'];
                    }
                }
//                var_dump($assnum);exit;
                $count_data = array();
                foreach ($assnum as $key => $value) {
                    if (in_array($key, $data)) {
                        $count_data[$value] = $key;
                    }
                }
                $count = count($count_data);
            }
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('保养完成待验收', ' ', '/Patrol/examineList', '', C('TASK_PATROL_COLOR'));
                $val['num'] = $count;
                $task['巡查保养管理'][] = $val;
                if ($this->checkIndexShow('patrolExamine', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '保养完成待验收';
                    $indexResult[$indexResultNum]['aLink'] = '/Patrol/examineList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //转科审批
        $approveMenu = get_menu('Assets', 'Transfer', 'approval');
        if ($approveMenu && $manager_assid) {
            $where = [];
            $where['assid'] = array('IN', $manager_assid);
            $where['all_approver'] = array('LIKE', '%/' . session('username') . '/%');
            $where['approve_status'] = array('EQ', C('APPROVE_STATUS'));
            $data = $RepairModel->DB_get_all('assets_transfer', 'current_approver', $where);
            if ($data) {
                $count = 0;
                foreach ($data as &$repair_approve_v) {
                    $current_approver = explode(',', $repair_approve_v['current_approver']);
                    foreach ($current_approver as &$repair_approver_value) {
                        if ($repair_approver_value == session('username')) {
                            $count++;
                        }
                    }
                }
                if ($count >= 1) {
                    $taskCount += $count;
                    $val['title'] = $RepairModel->returnTaskALink('转科审批', ' ', '/Transfer/examine', '', C('TASK_ASSETS_COLOR'));
                    $val['num'] = $count;
                    $task['设备管理'][] = $val;
                    if ($this->checkIndexShow('approvalTransfer', $baseSetting)) {
                        $indexResult[$indexResultNum]['name'] = '转科待审批';
                        $indexResult[$indexResultNum]['aLink'] = '/Transfer/examine';
                        $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                        $indexResult[$indexResultNum]['num'] = $count;
                        $indexResultNum++;
                    }
                    $num++;
                }
            }
        }

        //转科验收
        $examineMenu = get_menu('Assets', 'Transfer', 'examine');
        if ($examineMenu && $departid) {
            $where = [];
            $where['is_check'] = array('EQ', C('TRANSFER_IS_NOTCHECK'));
            $where['approve_status'][0] = 'IN';
            $where['approve_status'][1][] = C('STATUS_APPROE_UNWANTED');
            $where['approve_status'][1][] = C('STATUS_APPROE_SUCCESS');
            $where['tranin_departid'] = array('IN', $departid);
            $count = $RepairModel->DB_get_count('assets_transfer', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('转科验收', ' ', '/Transfer/checkLists', '', C('TASK_ASSETS_COLOR'));
                $val['num'] = $count;
                $task['设备管理'][] = $val;
                if ($this->checkIndexShow('AssetsTransferExamine', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '转科验收';
                    $indexResult[$indexResultNum]['aLink'] = '/Transfer/checkLists';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //报废设备审批
        $approveMenu = get_menu('Assets', 'Scrap', 'examine');
        if ($approveMenu && $manager_assid) {
            $where = [];
            $where['assid'] = array('IN', $manager_assid);
            $where['all_approver'] = array('LIKE', '%/' . session('username') . '/%');
            $where['approve_status'] = array('EQ', C('APPROVE_STATUS'));
            $data = $RepairModel->DB_get_all('assets_scrap', 'current_approver', $where);
            if ($data) {
                $count = 0;
                foreach ($data as &$repair_approve_v) {
                    $current_approver = explode(',', $repair_approve_v['current_approver']);
                    foreach ($current_approver as &$repair_approver_value) {
                        if ($repair_approver_value == session('username')) {
                            $count++;
                        }
                    }
                }
                if ($count >= 1) {
                    $taskCount += $count;
                    $val['title'] = $RepairModel->returnTaskALink('报废审批', ' ', '/Scrap/getExamineList', '', C('TASK_ASSETS_COLOR'));
                    $val['num'] = $count;
                    $task['设备管理'][] = $val;
                    if ($this->checkIndexShow('approvalScrap', $baseSetting)) {
                        $indexResult[$indexResultNum]['name'] = '报废待审批';
                        $indexResult[$indexResultNum]['aLink'] = '/Scrap/getExamineList';
                        $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                        $indexResult[$indexResultNum]['num'] = $count;
                        $indexResultNum++;
                    }
                    $num++;
                }
            }
        }

        //报废设备处置
        $resultMenu = get_menu('Assets', 'Scrap', 'result');
        if ($resultMenu && $manager_assid) {
            $where = [];
            $where['clear_cross_user'] = array('EXP', 'IS NULL');
            $where['approve_status'] = array('EQ', C('SCRAP_IS_CHECK_ADOPT'));
            $where['assid'] = array('IN', $manager_assid);
            $count = $RepairModel->DB_get_count('assets_scrap', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('报废设备处置', ' ', '/Scrap/getResultList', '', C('TASK_ASSETS_COLOR'));
                $val['num'] = $count;
                $task['设备管理'][] = $val;
                if ($this->checkIndexShow('assetsScrapResult', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '报废设备处置';
                    $indexResult[$indexResultNum]['aLink'] = '/Scrap/getResultList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //报废设备提醒
//        $scrapMenu = get_menu('Assets', 'Scrap', 'getApplyList');
//        if ($scrapMenu && $manager_assid) {
//            $where = [];
//
//            $where['assid'] = array('IN', $manager_assid);
//            $count = $RepairModel->DB_get_count('assets_scrap', $where);
//            if ($count >= 1) {
//            $taskCount = 20;
//            $val['title'] = $RepairModel->returnTaskALink('报废即将到期台数', ' ', 'Assets/Scrap/getScrapList', '', C('TASK_ASSETS_COLOR'));
//            $val['num'] = 20;
//            $task['设备管理'][] = $val;
//                if ($this->checkIndexShow('assetsScrapResult', $baseSetting)) {
//                    $indexResult[$indexResultNum]['name'] = '报废设备处置';
//                    $indexResult[$indexResultNum]['aLink'] = 'Assets/Scrap/getResultList';
//                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
//                    $indexResult[$indexResultNum]['num'] = $count;
//                    $indexResultNum++;
//                }
//                $num++;
//            }
//        }

        //借出部门审批
        $departApproveBorrowMenu = get_menu('Assets', 'Borrow', 'departApproveBorrow');
        //设备科审批
        $assetsApproveBorrowMenu = get_menu('Assets', 'Borrow', 'assetsApproveBorrow');
        //借调审批
        if ($departApproveBorrowMenu or $assetsApproveBorrowMenu) {
            $where = [];
            $where['status'] = array('EQ', C('BORROW_STATUS_APPROVE'));
            $borrow = $RepairModel->DB_get_all('assets_borrow', 'borid,assid,status,examine_status', $where);
            if ($borrow) {
                //负责人的可审批设备
                $managerApproveAssid = [];
                //设备科的可审批设备
                $assetsApproveAssid = [];
                $count = 0;
                if ($departApproveBorrowMenu && session('departid')) {
                    //有借出部门审批权限
                    $managerWhere['departid'] = array('in', session('departid'));
                    $managerWhere['manager'] = array('EQ', session('username'));
                    $managerWhere['hospital_id'] = array('EQ', session('job_hospitalid'));
                    $manager = $RepairModel->DB_get_all('department', 'departid,manager', $managerWhere);
                    if ($manager) {
                        //负责的科室
                        $managerDepairtid = [];
                        foreach ($manager as $managerV) {
                            $managerDepairtid[] = $managerV['departid'];
                        }
                        $assetsDepartWhere['departid'] = array('IN', $managerDepairtid);
                        $assetsDepart = $RepairModel->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
                        if ($assetsDepart) {
                            foreach ($assetsDepart as &$assetsDepartV) {
                                $managerApproveAssid[$assetsDepartV['assid']] = true;
                            }
                        }
                    }
                }
                if ($assetsApproveBorrowMenu && session('departid')) {
                    //有设备科审批权限
                    $assetsDepartWhere['departid'] = array('IN', session('departid'));
                    $assetsDepart = $RepairModel->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
                    if ($assetsDepart) {
                        foreach ($assetsDepart as &$assetsDepartV) {
                            $assetsApproveAssid[$assetsDepartV['assid']] = true;
                        }
                    }
                }
                foreach ($borrow as &$dataV) {
                    if ($dataV['examine_status'] != C('STATUS_APPROE_SUCCESS')) {
                        //查询审批历史
                        $apps = $RepairModel->DB_get_all('assets_borrow_approve', '', array('borid' => $dataV['borid']), '', 'level,approve_time asc');
                        if (!$apps && $managerApproveAssid[$dataV['assid']]) {
                            //未审批,是第一个审批人
                            $count++;
                            continue;
                        }
                        //已审批
                        if ($apps && $dataV['examine_status'] == C('APPROVE_STATUS')) {
                            //审批中
                            //设备科审批已通过
                            if ($assetsApproveAssid[$dataV['assid']]) {
                                //有设备科审批权限
                                $count++;
                                continue;
                            }
                        }
                    }
                }
                if ($count >= 1) {
                    $taskCount += $count;
                    $val['title'] = $RepairModel->returnTaskALink('借调审批', ' ', '/Borrow/approveBorrowList', '', C('TASK_ASSETS_COLOR'));
                    $val['num'] = $count;
                    $task['设备管理'][] = $val;
                    if ($this->checkIndexShow('approveBorrow', $baseSetting)) {
                        $indexResult[$indexResultNum]['name'] = '借调审批';
                        $indexResult[$indexResultNum]['aLink'] = '/Borrow/approveBorrowList';
                        $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                        $indexResult[$indexResultNum]['num'] = $count;
                        $indexResultNum++;
                    }
                    $num++;
                }
            }
        }

        //借调 借入检查
        $borrowInCheckMenu = get_menu('Assets', 'Borrow', 'borrowInCheck');
        if ($borrowInCheckMenu && $job_departid) {
            $where = [];
            $where['status'] = array('EQ', C('BORROW_STATUS_BORROW_IN'));
            $where['apply_departid'] = array('IN', $job_departid);
            $count = $RepairModel->DB_get_count('assets_borrow', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('借入检查', ' ', '/Borrow/borrowInCheckList', '', C('TASK_ASSETS_COLOR'));
                $val['num'] = $count;
                $task['设备借调'][] = $val;
                if ($this->checkIndexShow('borrowInCheck', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '借入检查';
                    $indexResult[$indexResultNum]['aLink'] = '/Borrow/borrowInCheckList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //借调 归还验收
        $borrowInCheckMenu = get_menu('Assets', 'Borrow', 'giveBackCheck');
        if ($borrowInCheckMenu && $job_hospital_manager_assid) {
            $where = [];
            $where['status'] = array('EQ', C('BORROW_STATUS_GIVE_BACK'));
            $where['assid'] = array('IN', $job_hospital_manager_assid);
            $count = $RepairModel->DB_get_count('assets_borrow', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('归还验收', ' ', '/Borrow/giveBackCheckList', '', C('TASK_ASSETS_COLOR'));
                $val['num'] = $count;
                $task['设备管理'][] = $val;
                if ($this->checkIndexShow('giveBackCheck', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '归还验收';
                    $indexResult[$indexResultNum]['aLink'] = '/Borrow/giveBackCheckList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //外调审批
        $approveMenu = get_menu('Assets', 'Outside', 'assetOutSideApprove');
        if ($approveMenu && $manager_assid) {
            $where = [];
            $where['assid'] = array('IN', $manager_assid);
            $where['all_approver'] = array('LIKE', '%/' . session('username') . '/%');
            $where['approve_status'] = array('EQ', C('APPROVE_STATUS'));
            $data = $RepairModel->DB_get_all('assets_outside', 'current_approver', $where);
            if ($data) {
                $count = 0;
                foreach ($data as &$repair_approve_v) {
                    $current_approver = explode(',', $repair_approve_v['current_approver']);
                    foreach ($current_approver as &$repair_approver_value) {
                        if ($repair_approver_value == session('username')) {
                            $count++;
                        }
                    }
                }
                if ($count >= 1) {
                    $taskCount += $count;
                    $val['title'] = $RepairModel->returnTaskALink('外调审批', ' ', '/Outside/outSideApproveList', '', C('TASK_ASSETS_COLOR'));
                    $val['num'] = $count;
                    $task['设备管理'][] = $val;
                    if ($this->checkIndexShow('approvalOutside', $baseSetting)) {
                        $indexResult[$indexResultNum]['name'] = '外调待审批';
                        $indexResult[$indexResultNum]['aLink'] = '/Outside/outSideApproveList';
                        $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                        $indexResult[$indexResultNum]['num'] = $count;
                        $indexResultNum++;
                    }
                    $num++;
                }
            }
        }

        //外调 外调单录入
        $checkOutSiteAssetMenu = get_menu('Assets', 'Outside', 'checkOutSiteAsset');
        if ($checkOutSiteAssetMenu && $manager_assid) {
            $where = [];
            $where['status'] = array('EQ', C('OUTSIDE_STATUS_ACCEPTANCE_CHECK'));
            $where['assid'] = array('IN', $manager_assid);
            $count = $RepairModel->DB_get_count('assets_outside', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('外调单录入', ' ', '/Outside/outSideResultList', '', C('TASK_ASSETS_COLOR'));
                $val['num'] = $count;
                $task['设备外调'][] = $val;
                if ($this->checkIndexShow('checkOutSiteAsset', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '外调单录入';
                    $indexResult[$indexResultNum]['aLink'] = '/Outside/outSideResultList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //质控 质控明细录入
        $setQualityDetailMenu = get_menu('Qualities', 'Quality', 'setQualityDetail');
        if ($setQualityDetailMenu && $manager_assid) {
            $where = [];
            $where['is_start'] = array('EQ', C('YES_STATUS'));
            if (session('is_supplier') == C('YES_STATUS')) {
                if (session('olsid') > 0) {
                    $assets_f = $RepairModel->DB_get_one('assets_factory', 'GROUP_CONCAT(assid) AS assid', ['ols_supid' => session('olsid')]);
                    if ($assets_f['assid']) {
                        $where['assid'] = ['IN', $assets_f['assid']];
                    } else {
                        //不检索
                        $where['assid'] = ['EQ', 0];
                    }
                }
            } else {
                $where['assid'] = array('IN', $manager_assid);
                if ($manager_assid) {
                    if (!session('isSuper')) {
                        $where['userid'] = array('EQ', session('userid'));
                    }
                }
            }
            $count = $RepairModel->DB_get_count('quality_starts', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('明细录入', ' ', '/Quality/qualityDetailList', '', C('TASK_ASSETS_COLOR'));
                $val['num'] = $count;
                $task['设备质控'][] = $val;
                if ($this->checkIndexShow('setQualityDetail', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '质控明细录入';
                    $indexResult[$indexResultNum]['aLink'] = '/Quality/qualityDetailList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //有新设备入库 todo 暂时是有录入权限的就可以看到提示
        $releasePatrolMenu = get_menu('Patrol', 'Patrol', 'releasePatrol');
        $editPatrolMenu = get_menu('Patrol', 'Patrol', 'editPatrol');
        $addPatrolMenu = get_menu('Patrol', 'Patrol', 'addPatrol');
        $getAssetsListMenu = get_menu('Assets', 'Lookup', 'getAssetsList');
        if (($releasePatrolMenu or $editPatrolMenu or $addPatrolMenu) && $getAssetsListMenu) {
            $day = $baseSetting['assets']['assets_add_remind_day']['value'];
            if ($day && $departid) {
                $assets_add_remind_day = strtotime("- $day day");
                $where = [];
                $where['departid'] = array('IN', $departid);
                $where['adddate'] = array('GT', $assets_add_remind_day);
                $count = $RepairModel->DB_get_count('assets_info', $where);
                if ($count >= 1) {
                    $assidArr = $RepairModel->DB_get_one('assets_info', 'group_concat(assid) AS assid', $where);
                    $val['title'] = $RepairModel->returnTaskALink('有新设备入库', ' ', '/Lookup/getAssetsList?assid=' . $assidArr['assid'] . '', '', 'sandybrown');
                    $val['num'] = $count;
                    $task['设备管理'][] = $val;
                    $num++;
                }


//                $assetsData = $RepairModel->DB_get_all('assets_info','assets,assnum' ,$where);
//                if ($assetsData) {
//                    foreach ($assetsData as &$assetsV){
//                        $val['title'] = $RepairModel->returnTaskALink('新增设备 "'. $assetsV['assets'].'" 编号 "' .$assetsV['assnum']. '" 入库', ' ', 'Repair/RepairParts/partsInWareList', '', 'sandybrown');
//                        $val['num'] = 1;
//                        $task['设备管理'][] = $val;
//                        $num++;
//                    }
//                }
            }
        }
        //计量检测将至
        $setMeteringMenu = get_menu('Metering', 'Metering', 'setMeteringResult');
        if ($setMeteringMenu) {
            $PlanWhere = [];
            $PlanWhere['status'] = array('EQ', C('YES_STATUS'));
            $Plan = $RepairModel->DB_get_all('metering_plan', 'mpid,remind_day,next_date', $PlanWhere);
            $count = 0;
            foreach ($Plan as &$PlanValue) {
                if (getHandleTime(strtotime('+' . $PlanValue['remind_day'] . ' day')) >= $PlanValue['next_date']) {
                    $count++;
                }
            }
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('计量检测将至', ' ', '/Metering/getMeteringResult', '', C('TASK_ASSETS_COLOR'));
                $val['num'] = $count;
                $task['计量管理'][] = $val;
                if ($this->checkIndexShow('setMeteringResult', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '计量检测将至';
                    $indexResult[$indexResultNum]['aLink'] = '/Metering/getMeteringResult';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }


        //维修配件采购申请待处理
        $inwarePartsMenu = get_menu('Repair', 'RepairParts', 'partsInWare');
        if ($inwarePartsMenu) {
            $where = [];
            $where['status'] = array('EQ', C('NO_STATUS'));
            $count = $RepairModel->DB_get_count('parts_inware_record', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('配件采购申请', ' ', '/RepairParts/partsInWareList', '', C('TASK_REPAIR_COLOR'));
                $val['num'] = $count;
                $task['配件管理'][] = $val;
                if ($this->checkIndexShow('inwareParts', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '配件采购申请';
                    $indexResult[$indexResultNum]['aLink'] = '/RepairParts/partsInWareList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //维修配件采购申请待处理
        $outwarePartsMenu = get_menu('Repair', 'RepairParts', 'partsOutWare');

        if ($outwarePartsMenu) {
            $where = [];
            $where['status'] = array('EQ', C('NO_STATUS'));
            $count = $RepairModel->DB_get_count('parts_outware_record', $where);
            if ($count >= 1) {
                $taskCount += $count;
                $val['title'] = $RepairModel->returnTaskALink('配件出库申请', ' ', '/RepairParts/partsOutWareList', '', C('TASK_REPAIR_COLOR'));
                $val['num'] = $count;
                $task['配件管理'][] = $val;
                if ($this->checkIndexShow('inwareParts', $baseSetting)) {
                    $indexResult[$indexResultNum]['name'] = '配件出库申请';
                    $indexResult[$indexResultNum]['aLink'] = '/RepairParts/partsOutWareList';
                    $indexResult[$indexResultNum]['color'] = C('HTML_A_LINK_COLOR_GREEN');
                    $indexResult[$indexResultNum]['num'] = $count;
                    $indexResultNum++;
                }
                $num++;
            }
        }

        //配件库存不足
        if ($inwarePartsMenu) {
            $stockWhere = [];
            $stockWhere['status'] = array('EQ', C('NO_STATUS'));
            $stockWhere['leader'] = array('EQ', '');
            $stockWhere['repid'] = array('EQ', 0);
            $group = 'parts,parts_model';
            $fields = 'COUNT(*) AS max_sum,leader,parts,supplier_name,price,unit,parts_model,brand';
            $stock = $RepairModel->DB_get_all('parts_inware_record_detail', $fields, $stockWhere, $group);
            $parts_warning = $baseSetting['repair']['parts_warning']['value'];
            foreach ($stock as &$stockValue) {
                if ($stockValue['max_sum'] < $parts_warning) {
                    $val['title'] = $RepairModel->returnTaskALink('"' . $stockValue['parts'] . '" 型号 "' . $stockValue['parts_model'] . '" 的配件库存不足', '', 'RepairParts/partsInWareList', '', 'sandybrown');
                    $val['num'] = 1;
                    $task['配件管理'][] = $val;
                    $num++;
                }
            }
        }
        foreach ($task as $k => $v) {
            $total = 0;
            foreach ($v as $k1 => $v1) {
                $total += $v1['num'];
                $task[$k]['total'] = $total;
            }
        }
        session('taskResult', $task);
        session('indexResult', $indexResult);
        session('taskCount', $taskCount);
    }


    public function checkIndexShow($menu, $setting)
    {
        return true;
    }

    //将设备厂商表没有绑定厂商表的厂商创建后和设备厂商表匹配id关联起来(已存在的直接匹配)
    public function set_offline_suppliers()
    {
        $OfflineSuppliersModel = new OfflineSuppliersModel();
        //获取所有的非空'/'的数据;
        $where[0]['ols_facid'] = ['EQ', 0];
        $where[0][]['factory'] = ['NEQ', '/'];
        $where[0][]['factory'] = ['NEQ', ''];

        $where[1]['ols_supid'] = ['EQ', 0];
        $where[1][]['supplier'] = ['NEQ', '/'];
        $where[1][]['supplier'] = ['NEQ', ''];

        $where[2]['ols_repid'] = ['EQ', 0];
        $where[2][]['repair'] = ['NEQ', '/'];
        $where[2][]['repair'] = ['NEQ', ''];

        $where[2]['ols_insurid'] = ['EQ', 0];
        $where[2][]['insurance'] = ['NEQ', '/'];
        $where[2][]['insurance'] = ['NEQ', ''];

        $where['_logic'] = 'OR';
        $factory = $OfflineSuppliersModel->DB_get_all('assets_factory', 'afid,factory,supplier,repair', $where);
        if ($factory) {
            $grounData = [];
            $allFactory = [];
            foreach ($factory as &$one) {
                if ($one['factory'] != '' && $one['factory'] != '/') {
                    if (!$grounData[$one['factory']]['is_manufacturer']) {
                        $allFactory[] = $one['factory'];
                        $grounData[$one['factory']]['sup_name'] = $one['factory'];
                        $grounData[$one['factory']]['is_manufacturer'] = true;
                    }
                }
                if ($one['supplier'] != '' && $one['supplier'] != '/') {
                    if (!$grounData[$one['supplier']]['is_supplier']) {
                        $allFactory[] = $one['supplier'];
                        $grounData[$one['supplier']]['sup_name'] = $one['supplier'];
                        $grounData[$one['supplier']]['is_supplier'] = true;
                    }
                }
                if ($one['repair'] != '' && $one['repair'] != '/') {
                    if (!$grounData[$one['repair']]['is_repair']) {
                        $allFactory[] = $one['repair'];
                        $grounData[$one['repair']]['sup_name'] = $one['repair'];
                        $grounData[$one['repair']]['is_repair'] = true;
                    }
                }

                if ($one['insurance'] != '' && $one['insurance'] != '/') {
                    if (!$grounData[$one['insurance']]['is_insurance']) {
                        $allFactory[] = $one['insurance'];
                        $grounData[$one['insurance']]['sup_name'] = $one['insurance'];
                        $grounData[$one['insurance']]['is_insurance'] = true;
                    }
                }
            }

            sort($grounData);

            $allFactory = array_unique($allFactory);
            sort($allFactory);
            $offlineData = [];
            $offline = $OfflineSuppliersModel->DB_get_all('offline_suppliers', 'olsid,sup_name,is_manufacturer,is_supplier,is_repair,is_insurance', ['sup_name' => ['IN', $allFactory]]);

            foreach ($offline as &$offvul) {
                $offlineData[$offvul['sup_name']]['olsid'] = $offvul['olsid'];
                $offlineData[$offvul['sup_name']]['sup_name'] = $offvul['sup_name'];
                $offlineData[$offvul['sup_name']]['is_manufacturer'] = $offvul['is_manufacturer'];
                $offlineData[$offvul['sup_name']]['is_supplier'] = $offvul['is_supplier'];
                $offlineData[$offvul['sup_name']]['is_repair'] = $offvul['is_repair'];
                $offlineData[$offvul['sup_name']]['is_insurance'] = $offvul['is_insurance'];
            }


            $addAllcount = count($grounData);
            $count = $OfflineSuppliersModel->DB_get_count('offline_suppliers');
            $insertData = array();
            $num = 0;
            $len = 50;
            $saveolsid = [];
            foreach ($grounData as $k => $v) {
                if ($num < $len) {
                    if ($offlineData[$v['sup_name']]) {
                        //修改操作
                        $save['edituser'] = session('username');
                        $save['editdate'] = time();

                        if ($v['is_manufacturer']) {
                            $save['is_manufacturer'] = C('YES_STATUS');
                        }

                        if ($v['is_supplier']) {
                            $save['is_supplier'] = C('YES_STATUS');
                        }

                        if ($v['is_repair']) {
                            $save['is_repair'] = C('YES_STATUS');
                        }

                        if ($v['is_insurance']) {
                            $save['is_insurance'] = C('YES_STATUS');
                        }
                        $saveolsid[] = $offlineData[$v['sup_name']]['olsid'];

                        $addAllcount--;

                        $OfflineSuppliersModel->updateData('offline_suppliers', $save, ['olsid' => ['EQ', $offlineData[$v['sup_name']]['olsid']]]);

                    } else {
                        //新增操作
                        $sup_num = 'SUP' . str_pad($count + $num + 1, 4, '0', STR_PAD_LEFT);
                        $insertData[$num]['sup_num'] = $sup_num;
                        $insertData[$num]['adduser'] = session('username');
                        $insertData[$num]['adddate'] = time();
                        $insertData[$num]['status'] = C('OPEN_STATUS');
                        $insertData[$num]['sup_name'] = $v['sup_name'];

                        if ($v['is_manufacturer']) {
                            $insertData[$num]['is_manufacturer'] = C('YES_STATUS');
                        } else {
                            $insertData[$num]['is_manufacturer'] = C('NO_STATUS');
                        }

                        if ($v['is_supplier']) {
                            $insertData[$num]['is_supplier'] = C('YES_STATUS');
                        } else {
                            $insertData[$num]['is_supplier'] = C('NO_STATUS');
                        }

                        if ($v['is_repair']) {
                            $insertData[$num]['is_repair'] = C('YES_STATUS');
                        } else {
                            $insertData[$num]['is_repair'] = C('NO_STATUS');
                        }

                        if ($v['is_insurance']) {
                            $insertData[$num]['is_insurance'] = C('YES_STATUS');
                        } else {
                            $insertData[$num]['is_insurance'] = C('NO_STATUS');
                        }
                        $num++;
                    }
                }
                if ($num == $len) {
                    //插入数据
                    $OfflineSuppliersModel->insertDataALL('offline_suppliers', $insertData);
                    //重置数据
                    $num = 0;
                    $insertData = array();
                }
            }

            if ($insertData) {
                $OfflineSuppliersModel->insertDataALL('offline_suppliers', $insertData);
            }

            //插入完后将 刚刚插入的数据获取回来
            $addOffline = $OfflineSuppliersModel->DB_get_all('offline_suppliers', 'olsid,sup_name', '', '', '', "$count,$addAllcount");
            if ($saveolsid) {
                $saveOffline = $OfflineSuppliersModel->DB_get_all('offline_suppliers', 'olsid,sup_name', ['olsid' => ['IN', $saveolsid]]);
                $addOffline = array_merge_recursive($addOffline, $saveOffline);
            }

            $offlineData = [];
            foreach ($addOffline as &$offvul) {
                $offlineData[$offvul['sup_name']] = $offvul['olsid'];
            }
            foreach ($factory as &$one) {
                if ($offlineData[$one['factory']]) {
                    $one['ols_facid'] = $offlineData[$one['factory']];
                }
                if ($offlineData[$one['supplier']]) {
                    $one['ols_supid'] = $offlineData[$one['supplier']];
                }
                if ($offlineData[$one['repair']]) {
                    $one['ols_repid'] = $offlineData[$one['repair']];
                }
                if ($offlineData[$one['insurance']]) {
                    $one['ols_insurid'] = $offlineData[$one['insurance']];
                }
                $OfflineSuppliersModel->updateData('assets_factory', $one, ['afid' => ['EQ', $one['afid']]]);
            }
//            print_r('更新成功');
        } else {
//            print_r('没有需要更新数据');
        }

    }

    public function tr()
    {
        $userModel = new UserModel();
        //删除前先备份原来的数据
        //$this->beifen();
        $userModel->tr();
    }

    public function beifen()
    {
//        //无论客户端是否关闭浏览器，下面的代码都将得到执行。
//        ignore_user_abort(true);
//        set_time_limit(0);
//        //function write_txt(){
//        ini_set("max_execution_time", "180");//避免数据量过大，导出不全的情况出现。

        $host = C('DB_HOST');//数据库地址
        $dbname = C('DB_NAME');//这里配置数据库名
        $username = C('DB_USER');//用户名
        $passw = C('DB_PWD');//这里配置密码

        $filename = date("Y-m-d_H-i-s") . "-" . $dbname . ".sql";
//        header("Content-disposition:filename=" . $filename);//所保存的文件名
//        header("Content-type:application/octetstream");
//        header("Pragma:no-cache");
//        header("Expires:0");
        //备份数据
        $i = 0;
        $crlf = "\r\n";
        global $dbconn;
        $dbconn = mysql_connect($host, $username, $passw);//数据库主机，用户名，密码
        $db = mysql_select_db($dbname, $dbconn);
        mysql_query("SET NAMES 'utf8'");
        $tables = mysql_list_tables($dbname, $dbconn);
        $num_tables = @mysql_numrows($tables);
        $sql_str = "-- filename=" . $filename;
        while ($i < $num_tables) {
            $table = mysql_tablename($tables, $i);
            $sql_str .= $crlf;
            $sql_str .= $this->get_table_structure($dbname, $table, $crlf) . ";$crlf$crlf";
            //echo   get_table_def($dbname,   $table,   $crlf).";$crlf$crlf";
            $sql_str .= $this->get_table_content($dbname, $table, $crlf);
            $i++;
        }
        mkdir('./Public/bak_sql/', 0777);
        chmod('./Public/bak_sql/' . $filename, 0777);
        $myfile = fopen('./Public/bak_sql/' . $filename, "w") or die("Unable to open file!");
        fwrite($myfile, $sql_str);
        fwrite($myfile, $sql_str);
        fclose($myfile);
    }

    public function get_table_structure($db, $table, $crlf)
    {
        global $drop;
        $schema_create = "";
        if (!empty($drop)) {
            $schema_create .= "DROP TABLE IF EXISTS `$table`;$crlf";
        }
        $result = mysql_db_query($db, "SHOW CREATE TABLE $table");
        $row = mysql_fetch_array($result);
        $schema_create .= $crlf . "-- " . $row[0] . $crlf;
        $schema_create .= $row[1] . $crlf;
        return $schema_create;
    }


    //获得表内容
    public function get_table_content($db, $table, $crlf)
    {
        $schema_create = "";
        $temp = "";
        $result = mysql_db_query($db, "SELECT * FROM $table");
        $i = 0;
        while ($row = mysql_fetch_row($result)) {
            $schema_insert = "INSERT INTO `$table` VALUES   (";
            for ($j = 0; $j < mysql_num_fields($result); $j++) {
                if (!isset($row[$j]))
                    $schema_insert .= " NULL,";
                elseif ($row[$j] != "")
                    $schema_insert .= " '" . addslashes($row[$j]) . "',";
                else
                    $schema_insert .= " '',";
            }
            $schema_insert = ereg_replace(",$", "", $schema_insert);
            $schema_insert .= ");$crlf";
            $temp = $temp . $schema_insert;
            $i++;
        }
        return $temp;
    }

    /**
     * Notes: 生成登录用的密钥
     */
    public function get_key()
    {
        chdir('Public/key');
        $shell = "openssl genrsa -out rsa_1024_priv.pem 1024";
        system($shell, $status);
        $shell = "openssl rsa -pubout -in rsa_1024_priv.pem -out rsa_1024_pub.pem";
        system($shell, $status);
    }

    /**
     * Notes: 发送提醒消息提醒审批及验收等
     */
    public function send_wechat()
    {
        $comModel = new AssetsInfoModel();
        //查询所有医院
        $hosids = $comModel->DB_get_all('hospital', 'hospital_id', array('is_delete' => 0));
        if (C('USE_FEISHU') === 1) {
            foreach ($hosids as $hosid) {
                //获取待审批数据
                $user = $comModel->get_user_approves($hosid['hospital_id']);
                //获取待验收数据
                $user = $comModel->get_user_check($user, $hosid['hospital_id']);
                $name = [];
                foreach ($user as $k => $v) {
                    $name[] = $k;
                }
                if ($name) {
                    $User = M("user"); // 实例化User对象
                    $where['username'] = array('in', $name);
                    $where['is_delete'] = 0;
                    $where['is_status'] = 1;
                    $nameopenid = $User->where($where)->getField('username,openid');
                    foreach ($nameopenid as $username => $openid) {
                        if (!$openid) {
                            continue;
                        }
                        foreach ($user as $uname => $uv) {
                            $str = '';
                            if ($uname == $username) {
                                foreach ($uv as $nk => $nv) {
                                    switch ($nk) {
                                        case 'outside_approve_nums':
                                            $str .= '外调审批(' . $nv . ')、';
                                            break;
                                        case 'scrap_approve_nums':
                                            $str .= '报废审批(' . $nv . ')、';
                                            break;
                                        case 'transfer_approve_nums':
                                            $str .= '转科审批(' . $nv . ')、';
                                            break;
                                        case 'patrol_approve_nums':
                                            $str .= '巡查审批(' . $nv . ')、';
                                            break;
                                        case 'purch_approve_nums':
                                            $str .= '采购审批(' . $nv . ')、';
                                            break;
                                        case 'repair_approve_nums':
                                            $str .= '维修审批(' . $nv . ')、';
                                            break;
                                        case 'repair_check_nums':
                                            $str .= '维修验收(' . $nv . ')、';
                                            break;
                                    }
                                }
                                $str = trim($str, '、');
                                //==========================================飞书 START========================================
                                //要显示的字段区域
                                $fd['is_short'] = false;//是否并排布局
                                $fd['text']['tag'] = 'lark_md';
                                $fd['text']['content'] = '**待处理事项：**\n' . $str;
                                $feishu_fields[] = $fd;

                                $fd['is_short'] = false;//是否并排布局
                                $fd['text']['tag'] = 'lark_md';
                                $fd['text']['content'] = '请及时登录系统进行处理';
                                $feishu_fields[] = $fd;

                                //按钮区域
                                $act['tag'] = 'button';
                                $act['type'] = 'primary';
                                $act['url'] = C('APP_NAME') . C('FS_NAME') . '/Notin/getUserOpenId';
                                $act['text']['tag'] = 'plain_text';
                                $act['text']['content'] = '登录系统';
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
                                $card_data['header']['title']['content'] = '待处理任务提醒';
                                $card_data['header']['title']['tag'] = 'plain_text';

                                $this->send_feishu_card_msg($openid, $card_data);
                                //==========================================飞书 END==========================================
                            }
                        }
                    }
                }
            }
        } else {
            //判断是否开启微信端
            $moduleModel = new ModuleModel();
            $wx_status = $moduleModel->decide_wx_login();
            if (!$wx_status) {
                return false;
            }
            foreach ($hosids as $hosid) {
                //获取待审批数据
                $user = $comModel->get_user_approves($hosid['hospital_id']);
                //获取待验收数据
                $user = $comModel->get_user_check($user, $hosid['hospital_id']);
                $name = [];
                foreach ($user as $k => $v) {
                    $name[] = $k;
                }
                if ($name) {
                    $User = M("user"); // 实例化User对象
                    $where['username'] = array('in', $name);
                    $where['is_delete'] = 0;
                    $where['is_status'] = 1;
                    $nameopenid = $User->where($where)->getField('username,openid');
                    $templateId = C('WX_TEMPLATES')['GZJDTZ'];//工作进度通知
                    if (C('USE_VUE_WECHAT_VERSION')) {
                        $redecturl = C('APP_NAME') . C('VUE_NAME') . '/Notin/getUserOpenId';
                    } else {
                        $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Index/testindex.html';
                    }
                    foreach ($nameopenid as $username => $openid) {
                        if (!$openid) {
                            continue;
                        }
                        foreach ($user as $uname => $uv) {
                            $str = '';
                            if ($uname == $username) {
                                foreach ($uv as $nk => $nv) {
                                    switch ($nk) {
                                        case 'outside_approve_nums':
                                            $str .= '外调审批(' . $nv . ')、';
                                            break;
                                        case 'scrap_approve_nums':
                                            $str .= '报废审批(' . $nv . ')、';
                                            break;
                                        case 'transfer_approve_nums':
                                            $str .= '转科审批(' . $nv . ')、';
                                            break;
                                        case 'patrol_approve_nums':
                                            $str .= '巡查审批(' . $nv . ')、';
                                            break;
                                        case 'purch_approve_nums':
                                            $str .= '采购审批(' . $nv . ')、';
                                            break;
                                        case 'repair_approve_nums':
                                            $str .= '维修审批(' . $nv . ')、';
                                            break;
                                        case 'repair_check_nums':
                                            $str .= '维修验收(' . $nv . ')、';
                                            break;
                                    }
                                }
                                $str = trim($str, '、');
                                //发送微信消息
                                $wxdata = array(
                                    'first' => array('value' => urlencode($username . "，您好，你有一个待处理任务提醒！"), 'color' => "#FF0000"),
                                    'keyword1' => array('value' => urlencode($str)),
                                    'keyword2' => array('value' => urlencode('待处理')),
                                    'remark' => array('value' => urlencode('请及时登录系统进行处理！')),
                                );
                                $comModel->sendMsgToOnUserByWechat($openid, $templateId, $redecturl, $wxdata);
                            }
                        }
                    }
                }
            }
        }


        /******************删除Public/uploads/loginimg文件夹下当前时间以前一天产出的图片*******************/
        /**********该文件夹下的图片由PC端选择扫码登录时产生的图片，登录后已没其他作用，故可删除节约系统空间********/
        $dir = './Public/uploads/loginimg';
        //scandir将以数组的形式返回该目录下所有的文件
        $files = scandir($dir);
        foreach ($files as $filename) {
            $thisfile = $dir . '/' . $filename;
            if ($thisfile != '.' && $thisfile != '..' && (time() - filemtime($thisfile)) > 3600 * 24) {
                unlink($thisfile);//删除此次遍历到的文件
            }
        }
    }

    /**
     * 获取邮件内容并发送微信消息
     */
    public function get_email()
    {
        //此处查看链接状态
        header("Content-type:text/html;charset=utf-8");
        if (!fsockopen('tls://pop.163.com', 995, $error, $errorstr, 8)) {
            echo 'cennect 163 fail!';
        }
        $host = "tls://pop.163.com"; //‘tls：//’为ssl协议加密，端口走加密端口
        $user = "wxdiallon@163.com"; //邮箱登录密码：GbK2312__
        $pass = "RSFYKJLUJNPDPWFF"; //密码icexjxkjqdkvbgii

        Vendor('email.Pop3');
        //参数1：为链接地址，参数2：为端口号，参数3为过载时间
        $rec = new \Pop3($host, 995, 2);
        //打开
        if (!$rec->open()) {
            die($rec->err_str);
        }
        //登录
        if (!$rec->login($user, $pass)) {
            die($rec->err_str);
        }
        //读取
        if (!$rec->stat()) {
            die($rec->err_str);
        }
        //邮件数量
        if ($rec->messages > 0) {
            if (!$rec->listmail()) {
                die($rec->err_str);
            }
            //定义邮件头内容--邮件主体内容数组
            $head_data = $body_data = $mail_head = $mail_content = [];
            //读取10封邮件
            $n = $m = 0;
            for ($j = $rec->messages; $j > $rec->messages - 10; $j--) {
                $have_mail = $rec->getmail($j);
                if ($have_mail) {
                    $head_data[] = $rec->head;
                    $body_data[] = $rec->body;
                    $rec->head = null;
                    $rec->body = null;
                }
            }
            //邮件主题列表
            foreach ($head_data as $key => $value) {
                foreach ($value as $k => $v) {
                    //邮件发送时间
                    if (strpos($v, '+0800 (CST)') !== false) {
                        $time = str_replace('+0800 (CST)', '', $v);
                        $time = trim($time);
                        $time = explode(',', $time);
                        $time = trim($time[1]);
                        $time = explode(' ', $time);
                        switch ($time[1]) {
                            case 'Jan':
                                $time[1] = '01';
                                break;
                            case 'Feb':
                                $time[1] = '02';
                                break;
                            case 'Mar':
                                $time[1] = '03';
                                break;
                            case 'Apr':
                                $time[1] = '04';
                                break;
                            case 'May':
                                $time[1] = '05';
                                break;
                            case 'Jun':
                                $time[1] = '06';
                                break;
                            case 'Jul':
                                $time[1] = '07';
                                break;
                            case 'Aug':
                                $time[1] = '08';
                                break;
                            case 'Sep':
                                $time[1] = '09';
                                break;
                            case 'Oct':
                                $time[1] = '10';
                                break;
                            case 'Nov':
                                $time[1] = '11';
                                break;
                            case 'Dec':
                                $time[1] = '12';
                                break;
                        }
                        $time[0] = $time[0] < 10 ? '0' . $time[0] : $time[0];
                        $mail_head[$n]['send_time'] = $time[2] . '-' . $time[1] . '-' . $time[0] . ' ' . $time[3];
                    }
                    //邮件发送人
                    if (strpos($v, 'From: ') !== false) {
                        $from = str_replace('From: ', '', $v);
                        //$from = $rec->decode_mime($from);
                        $from = trim($from, '>');
                        $from_arr = explode('<', $from);
                        if ($from_arr) {
                            $mail_head[$n]['from'] = $from_arr[1];
                        } else {
                            $mail_head[$n]['from'] = $from;
                        }
                    }
                    //邮件主题
                    if (strpos($v, "Subject: ") !== false) {
                        $subjec = '';
                        $subjec = str_replace('Subject: ', '', $v);
                        $subjec = $rec->decode_mime($subjec);
                        if ($value[$k + 1] && strpos($value[$k + 1], ": ") === false && strpos($value[$k + 1], '=?')) {
                            $subjec1 = trim($value[$k + 1]);
                            $subjec1 = $rec->decode_mime($subjec1);
                            $subjec .= $subjec1;
                        }
                        if ($value[$k + 2] && strpos($value[$k + 2], ": ") === false && strpos($value[$k + 2], '=?')) {
                            $subjec2 = trim($value[$k + 2]);
                            $subjec2 = $rec->decode_mime($subjec2);
                            $subjec .= $subjec2;
                        }
                        if ($value[$k + 3] && strpos($value[$k + 3], ": ") === false && strpos($value[$k + 3], '=?')) {
                            $subjec3 = trim($value[$k + 3]);
                            $subjec3 = $rec->decode_mime($subjec3);
                            $subjec .= $subjec3;
                        }
                        if ($value[$k + 4] && strpos($value[$k + 4], ": ") === false && strpos($value[$k + 4], '=?')) {
                            $subjec4 = trim($value[$k + 4]);
                            $subjec4 = $rec->decode_mime($subjec4);
                            $subjec .= $subjec4;
                        }
                        if ($value[$k + 5] && strpos($value[$k + 5], ": ") === false && strpos($value[$k + 5], '=?')) {
                            $subjec5 = trim($value[$k + 5]);
                            $subjec5 = $rec->decode_mime($subjec5);
                            $subjec .= $subjec5;
                        }
                        if ($value[$k + 6] && strpos($value[$k + 6], ": ") === false && strpos($value[$k + 6], '=?')) {
                            $subjec6 = trim($value[$k + 6]);
                            $subjec6 = $rec->decode_mime($subjec6);
                            $subjec .= $subjec6;
                        }
                        $mail_head[$n]['subject'] = $subjec;
                    }
                }
                $n++;
            }
            //邮件内容列表
            foreach ($body_data as $key => $value) {
                $start = $end = 0;
                foreach ($value as $k => $v) {
                    if (strpos($v, 'Content-Transfer-Encoding: base64') !== false) {
                        $start = $k + 1;
                    }
                    if (strpos($v, '------=') !== false && $start > 0) {
                        $end = $k;
                        break;
                    }
                }
                $body_str = '';
                for ($i = $start; $i < $end; $i++) {
                    $body_str .= $value[$i];
                }
                if ($body_str) {
                    $content = base64_decode($body_str);
                    $content = iconv('gb18030', "utf-8", $content);
                    $mail_content[$m]['content'] = $content;
                } else {
                    $mail_content[$m]['content'] = '';
                }
                $m++;
            }

            //发送客服消息调用地址
            $wxModel = new WxAccessTokenModel();
            $access_token = $wxModel->getAccessToken();
            $send_url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
            $userModel = new UserModel();
            $moduleModel = new ModuleModel();
            //查询主医院，默认只为主医院发送消息
            $hosinfo = $userModel->DB_get_one('hospital', 'hospital_id', ['is_general_hospital' => 1, 'is_delete' => 0]);
            $hosid = $hosinfo['hospital_id'];
            if (!$hosid) {
                die('未设置医院');
            }
            //查询是否为新邮件
            foreach ($mail_head as $hk => $hv) {
                //忽略非报修的邮件
                //主题没‘报修’字眼、内容没‘报修人’的，排除
                if (strpos($hv['subject'], '报修') === false || strpos($mail_content[$hk]['content'], '报修人') === false) {
                    unset($mail_head[$hk]);
                    unset($mail_content[$hk]);
                    continue;
                }
                $where['receive_time'] = $hv['send_time'];
                $where['from'] = $hv['from'];
                //查询该邮件是否已记录在数据库中
                $exists = $userModel->DB_get_one('email_content', '*', $where);
                if (!$exists) {
                    //不存在，保存到数据库sb_email_content
                    $inserData['receive_time'] = $hv['send_time'];
                    $inserData['from'] = $hv['from'];
                    $inserData['subject'] = $hv['subject'];
                    $inserData['content'] = $mail_content[$hk]['content'];
                    $emid = $userModel->insertData('email_content', $inserData);
                    if ($emid) {
                        //获取报修科室
                        $department = $this->get_department($mail_content[$hk]['content']);
                        echo $department;
                        //获取工单号
                        $ordernum = $this->get_ordernum($mail_content[$hk]['content']);
                        echo $ordernum;
                        //查询该科室下的工程师
                        $dep = $userModel->DB_get_one('department', 'departid', ['department' => $department]);
                        $moduleName = 'Repair';
                        $controllerName = 'Repair';
                        $actionName = 'accept';
                        $users = $userModel->getToUser(0, $dep['departid'], $moduleName, $controllerName, $actionName, $hosid);
                        $openids = [];
                        foreach ($users as $uv) {
                            $openids[$uv['userid']] = $uv['openid'];
                        }
                        //openid 去重
                        $openids = array_unique($openids);
                        //发送微信消息
                        $wx_status = $moduleModel->decide_wx_login();
                        $update_data['openid'] = json_encode($openids);
                        if ($wx_status) {
                            $this->send_kefu_msg($send_url, $openids, $ordernum, $emid);
                            //更新发送时间
                            $update_data['send_time'] = date('Y-m-d H:i:s');
                            $update_data['msg_status'] = 1;
                        }
                        $userModel->updateData('email_content', $update_data, ['emid' => $emid]);
                    }
                }
            }
//            if($mail_head && $mail_content){
//                echo '最近七天接收到关于报修的邮件主题及内容如下：<br/>';
//                echo '<hr/>';
//                echo '<pre>';
//                echo '邮件主题：<br/>';
//                print_r($mail_head);
//                echo '<hr/>';
//                echo '邮件内容：<br/>';
//                print_r($mail_content);
//            }else{
//                echo '最近七天没有接收到邮件';
//            }
        } else {
            $rec->close();
        }
    }

    // 每个月更新一次折旧额
    public function renew_depreciation()
    {
        $comModel = new AssetsInfoModel();
        $comModel->renew_depreciation();
        return true;
    }

    private function get_department($content)
    {
        $start = strpos($content, '报修科室');
        $end = strpos($content, '报修时间');
        $department = substr($content, $start, ($end - $start));
        $department = str_replace('报修科室:', '', $department);
        $department = str_replace('报修科室：', '', $department);
        //截取科室后的；和空格
        $s = strpos($department, ';');
        $rep = substr($department, $s);
        //替换$rep为空
        return str_replace($rep, '', $department);
    }

    private function get_ordernum($content)
    {
        $start = strpos($content, '工单号');
        $end = strpos($content, '报修人');
        $num = substr($content, $start, ($end - $start));
        $num = str_replace('工单号:', '', $num);
        $num = str_replace('工单号：', '', $num);
        //把数字全部提取出来
        $str_num = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $len = strlen($num);
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            if (in_array($num[$i], $str_num)) {
                $str .= $num[$i];
            }
        }
        return $str;
    }

    /**
     * Notes: 发送客服消息
     * @param $send_url string 客服消息地址
     * @param $openids array 要发送的用户openid
     * @param $ordernum string 工单号
     * @param $emid int 邮件记录
     */
    private function send_kefu_msg($send_url, $openids, $ordernum, $emid)
    {
        $templateId = C('WX_TEMPLATES')['GDCLTZ'];
        $wechat_data = array(
            'first' => array('value' => urlencode("您好,收到一条邮件报修通知"), 'color' => "#FF0000"),
            'keyword1' => array('value' => urlencode($ordernum)),
            'keyword2' => array('value' => urlencode('详情点击链接查看')),
            'remark' => array('value' => urlencode('请尽快处理！')),
        );
        $n = 0;
        if (C('USE_VUE_WECHAT_VERSION')) {
            $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME') . '/Tasks/show_email?emid=' . $emid;
        } else {
            $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Tasks/show_email.html?emid=' . $emid;
        }
        foreach ($openids as $v) {
            $template = array(
                'touser' => $v,
                'template_id' => $templateId,
                'url' => $redecturl,
                'topcolor' => "#7B68EE",
                'data' => $wechat_data
            );
            $template = json_encode($template);
            $template = urldecode($template);
            echo $redecturl . 'n=' . $n;
            if ($n == 0) {
                $res = dcurl($send_url, $template);
                $res = json_decode($res);
                if ($res->errcode != 0) {
                    $wx = M('wx_access_token');
                    $map['item'] = 'access_token';
                    $wx->where($map)->delete();
                    $urls = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . C('WX_APPID') . "&secret=" . C('WX_SECRET');
                    $res = json_decode($this->httpGet($urls));
                    $access_token = $res->access_token;
                    $expires_in = $res->expires_in;
                    if ($access_token) {
                        $acData['expire_time'] = time() + $expires_in;
                        $acData['value'] = $access_token;
                        $acData['item'] = 'access_token';
                        $wx->data($acData)->add();
                        $wxurl = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
                        fsock($wxurl, $template);
                    }
                }
                $n++;
            } else {
                fsock($send_url, $template);
            }
        }
    }

    public function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($curl, CURLOPT_CAINFO,dirname(__FILE__).'\cacert.pem');
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }
}
