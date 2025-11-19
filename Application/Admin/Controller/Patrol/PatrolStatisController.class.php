<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2018/2/8
 * Time: 9:32
 */

namespace Admin\Controller\Patrol;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\CommonModel;
use Admin\Model\DepartmentModel;
use Admin\Model\PatrolPlanCycleModel;
use Admin\Model\UserModel;


class PatrolStatisController extends CheckLoginController
{
    private $MODULE = 'Patrol';
    private $CONTROLLER = 'PatrolStatis';

    /**
     * Notes: 巡查计划概况
     */
    public function patrolPlanSurvey()
    {
        if (IS_POST) {
            $year = I('post.year') ? I('post.year') : date('Y',strtotime('-1 year'));
            $result = $this->get_plans_by_year($year);
            $this->ajaxReturn($result);
        } else {
            $this->assign('year', date('Y',strtotime('-1 year')));
            $this->display();
        }
//        if (IS_POST) {
//            $departmentModel = new DepartmentModel();
//            $action = I('POST.action');
//            if ($action == 'departPlanDetail') {
//                $this->departPlanDetail();
//            } else {
//                //获取数据
//                $startDate = I('POST.patrolPlanSurveyStartDate') ? I('POST.patrolPlanSurveyStartDate') . ' 00:00:01' : date('Y') . '-01-01 00:00:01';
//                $endDate = I('POST.patrolPlanSurveyEndDate') ? I('POST.patrolPlanSurveyEndDate') . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
//                $departids = I('POST.departids');
//                $hospital_id = I('POST.hospital_id');
//                if ($departids) {
//                    $departids = explode(',', I('POST.departids'));
//                } else {
//                    $departids = array();
//                }
//                if (!$hospital_id) {
//                    $hospital_id = session('current_hospitalid');
//                }
//                //获取部门设备数据
//                $lists = $departmentModel->getDepartmentAssetsNum($departids, $hospital_id);
//                if (!$lists) {
//                    $this->ajaxReturn($lists, 'json');
//                }
//                //根据搜索日期，查询保养计划
//                $planModel = new PatrolPlanCycleModel();
//                $plans = $planModel->getAllPlanByDate($startDate, $endDate);
//                //筛选出已做计划的assnum和异常的assnum
//                $arr = array();
//                $abnormalArr = array();
//                foreach ($plans as $k => $v) {
//                    $planassnum = json_decode($v['plan_assnum']);
//                    $arr = array_merge($arr, $planassnum);
//                    $arr = array_unique($arr);
//                    if ($v['abnormal_assnum']) {
//                        $abnormal = json_decode($v['abnormal_assnum']);
//                        $abnormalArr = array_merge($abnormalArr, $abnormal);
//                        $abnormalArr = array_unique($abnormalArr);
//                    }
//                }
//                //统计各部门已做计划设备数量及保养覆盖率
//                foreach ($lists as $k => $v) {
//                    if ($v['assnums']) {
//                        $url = C('ADMIN_NAME')."/".$this->CONTROLLER."/patrolPlanSurvey/action/departPlanDetail/id/" . $v['departid'] . '/s/' . strtotime($startDate) . '/e/' . strtotime($endDate);
//                        $lists[$k]['departPlanDetail'] = $v['department'];
//                        $allAssnum = explode(',', $v['assnums']);
//                        //部门所有设备与所有已做计划设备合并找出相同部分即为该部门已做计划设备数量
//                        $planArr = array_intersect($arr, $allAssnum);
//                        $lists[$k]['planNum'] = count($planArr);
//                        $lists[$k]['notPlanNum'] = $v['totalNum'] - count($planArr);
//                        $lists[$k]['planRate'] = (sprintf("%.3f", $lists[$k]['planNum'] / $lists[$k]['totalNum']) * 100) . '%';
//                        //部门所有设备与所有异常设备合并找出相同部分即为该部门异常设备数量
//                        $notNormalArr = array_intersect($abnormalArr, $allAssnum);
//                        $lists[$k]['abnormalNum'] = count($notNormalArr);
//                        $lists[$k]['abnormalRate'] = (sprintf("%.3f", $lists[$k]['abnormalNum'] / $lists[$k]['totalNum']) * 100) . '%';
//                        $lists[$k]['operation'] = '<button data-url="' . $url . '" class="layui-btn layui-btn-xs layui-btn-normal departmentPatrol" >科室巡查详情</button>';
//                    } else {
//                        $lists[$k]['planNum'] = 0;
//                        $lists[$k]['notPlanNum'] = 0;
//                        $lists[$k]['abnormalNum'] = 0;
//                        $lists[$k]['planRate'] = '0%';
//                        $lists[$k]['abnormalRate'] = '0%';
//                        $lists[$k]['operation'] = '<button class="layui-btn layui-btn-xs layui-btn-disabled" >科室巡查详情</button>';
//                    }
//                }
//                $res = $planModel->handlePlanData($lists);
//                $hosName = $this->get_hospital_name($hospital_id);
//                $hospitalName = $hosName['hospital_name'];
//                //获取报表标题
//                $res['reportTitle'] = $hospitalName . '各部门巡查计划概况';
//                //获取报表搜索条件范围
//                $res['reportTips'] = $planModel->getReportTips($startDate, $endDate);
//                $this->ajaxReturn($res, 'json');
//            }
//        } else {
//            $action = I('GET.action');
//            if ($action == 'departPlanDetail') {
//                $this->departPlanDetail();
//            } elseif ($action == 'view') {
//                $this->view();
//            } else {
//                $departments = $this->getSelectDepartments();
//                $this->assign('departments', $departments);
//                $this->assign('height', count($departments) * 50);
//                $this->assign('start_date', date('Y') . '-01-01');
//                $this->assign('end_date', date('Y-m-d'));
//                $this->display();
//            }
//        }
    }

    public function get_plans_by_year($year)
    {
        if(!$year){
            return ['status'=>-1,'msg'=>'请输入年份搜索'];
        }
        $startDate = $year.'-01-01 00:00:01';
        $endDate = $year.'-12-31 23:59:59';
        $where['A.create_time'] = [['egt',$startDate],['elt',$endDate]];//array(array('egt', $startDate), array('elt', $endDate));
        $where['B.is_delete'] = 0;
        $field = "A.cycid,A.create_time,A.cycle_status";
        $join = "LEFT JOIN sb_patrol_plans AS B ON A.patrid = B.patrid";
        $cycleModel = new PatrolPlanCycleModel();
        $plans = $cycleModel->DB_get_all_join('patrol_plans_cycle','A',$field,$join,$where,'','A.create_time asc');
        $all_plans_data = [];
        $complete_plans_data = [];
        $xAxis_data = [];
        for($i=1;$i<=12;$i++){
            $xAxis_data[] = $i < 10 ? $year.'-0'.$i : $year.'-'.$i;
        }
        $year_all = 0;
        $year_complete = 0;
        foreach ($xAxis_data as $xk=>$xv){
            $all = 0;
            $complete = 0;
            foreach ($plans as $k=>$v){
                if(!$v['create_time']){
                    continue;
                }
                if(strpos($v['create_time'],$xv) !== false){
                    $year_all += 1;
                    $all += 1;
                    if(in_array($v['cycle_status'],[2,3])){
                        //已完成的
                        $year_complete += 1;
                        $complete += 1;
                    }
                }
            }
            $all_plans_data[$xk] = $all;
            $complete_plans_data[$xk] = $complete;
        }
        $result['res']['xAxis_data'] = $xAxis_data;
        $result['res']['all_plans_data'] = $all_plans_data;
        $result['res']['complete_plans_data'] = $complete_plans_data;
        $result['res']['year_all'] = $year_all;
        $result['res']['year_complete'] = $year_complete;
        $result['status'] = 1;
        return $result;
    }

    /*
     * 导出巡查计划概况
     */
    public function exportPlanSummary()
    {
        if ($_POST) {
            $departmentModel = new DepartmentModel();
            //获取数据
            $startDate = I('POST.startDate') ? I('POST.startDate') . ' 00:0001' : date('Y') . '-01-01 00:00:01';
            $endDate = I('POST.endDate') ? I('POST.endDate') . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
            $departids = I('POST.departids');
            $hospital_id = I('POST.hospital_id');
            if ($departids) {
                $departids = explode(',', I('POST.departids'));
            } else {
                $departids = array();
            }
            if (!$hospital_id) {
                $hospital_id = session('manager_hospitalid');
            }
            //获取部门设备数据
            $lists = $departmentModel->getDepartmentAssetsNum($departids, $hospital_id);
            if (!$lists) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '获取不到部门设备数据！'));
            }
            //根据搜索日期，查询保养计划
            $planModel = new PatrolPlanCycleModel();
            $plans = $planModel->getAllPlanByDate($startDate, $endDate);
            //筛选出已做计划的assnum和异常的assnum
            $arr = array();
            $abnormalArr = array();
            foreach ($plans as $k => $v) {
                $planassnum = json_decode($v['plan_assnum']);
                $arr = array_merge($arr, $planassnum);
                $arr = array_unique($arr);
                if ($v['abnormal_assnum']) {
                    $abnormal = json_decode($v['abnormal_assnum']);
                    $abnormalArr = array_merge($abnormalArr, $abnormal);
                    $abnormalArr = array_unique($abnormalArr);
                }
            }
            //统计各部门已做计划设备数量及保养覆盖率
            foreach ($lists as $k => $v) {
                if ($v['assnums']) {
                    $url = "./departPlanDetail/id/" . $v['departid'] . '/s/' . strtotime($startDate) . '/e/' . strtotime($endDate);
                    $lists[$k]['departPlanDetail'] = '<a href="' . $url . '" style="color: #0099FF;text-decoration: none;">' . $v['department'] . '</a>';
                    $allAssnum = explode(',', $v['assnums']);
                    //部门所有设备与所有已做计划设备合并找出相同部分即为该部门已做计划设备数量
                    $planArr = array_intersect($arr, $allAssnum);
                    $lists[$k]['planNum'] = count($planArr);
                    $lists[$k]['notPlanNum'] = $v['totalNum'] - count($planArr);
                    $lists[$k]['planRate'] = (sprintf("%.3f", $lists[$k]['planNum'] / $lists[$k]['totalNum']) * 100) . '%';
                    //部门所有设备与所有异常设备合并找出相同部分即为该部门异常设备数量
                    $notNormalArr = array_intersect($abnormalArr, $allAssnum);
                    $lists[$k]['abnormalNum'] = count($notNormalArr);
                    $lists[$k]['abnormalRate'] = (sprintf("%.3f", $lists[$k]['abnormalNum'] / $lists[$k]['totalNum']) * 100) . '%';
                } else {
                    $lists[$k]['planNum'] = 0;
                    $lists[$k]['notPlanNum'] = 0;
                    $lists[$k]['abnormalNum'] = 0;
                    $lists[$k]['planRate'] = '0%';
                    $lists[$k]['abnormalRate'] = '0%';
                }
            }
            $data = $planModel->handlePlanData($lists);
            //要显示的数据
            $showName = array('department' => '科室名称', 'totalNum' => '设备总台数', 'planNum' => '保养设备数', 'planRate' => '保养覆盖率', 'abnormalNum' => '异常设备数', 'abnormalRate' => '异常率');
            //接收base64图片编码并转化为图片保存
            $base64Data = I('POST.base64Data');
            //匹配出图片的格式
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Data, $result)) {
                //图片格式
                $type = $result[2];
                if (!in_array($type, array('png', 'jpeg'))) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '图片格式错误！'));
                }
                $filePath = "./Public/uploads/report/";
                if (!file_exists($filePath)) {
                    //检查是否有该文件夹，如果没有就创建，并给予最高权限
                    mkdir($filePath, 0777, true);
                }
                $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
                $file = $filePath . $fileName;
                if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $base64Data)))) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '图表保存出错，请重试！'));
                }
                $image = new \Think\Image();
                $image->open($file);
                $imageInfo['width'] = $image->width(); // 返回图片的宽度
                $imageInfo['height'] = $image->height(); // 返回图片的高度
                $imageInfo['type'] = $image->type(); // 返回图片的类型
                $imageInfo['url'] = $file; // 服务器图片地址
                $showLastTotalRow = false; //是否显示最好一行合计行
                $tableHeader = '巡查计划概况';
                $tips = '报表日期：' . $startDate . ' 至 ' . $endDate;
                $otherInfo['titleFontSize'] = 28;
                $otherInfo['titleRowHeight'] = 60;
                $otherInfo['imagePosition'] = 'bottom';
                $otherInfo['imageWidth'] = $image->width() * 0.8;//图片缩放比例
                $otherInfo['imageHeight'] = $image->height() * 0.8;//图片缩放比例
                exportExcelStatistics($sheetTitle = array('资产概况'), $tableHeader, $showName, $data['lists'], $imageInfo, $showLastTotalRow, $tableHeader, $tips, $otherInfo);
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '生成图片错误！'));
            }
        }
    }

    /*
     * notes: 部门各设备巡查计划概况统计
     */
    private function departPlanDetail()
    {
        if (IS_POST) {
            //获取对应部门下的所有设备
            $patrolModel = new PatrolPlanCycleModel();
            $result = $patrolModel->getAllDepartmentAssets();
            //统计各设备计划次数
            $result['rows'] = $patrolModel->getPlansNum($result['rows']);
            //统计各设备出现异常次数和异常项总数
            $result['rows'] = $patrolModel->getAbnormalNum($result['rows']);
            $this->ajaxReturn($result, 'json');
        } else {
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $departid = I('GET.id');
            $startDate = getHandleTime(I('GET.s'));
            $endDate = getHandleTime(I('GET.e'));
            $this->assign('departid', $departid);
            $this->assign('startDate', $startDate);
            $this->assign('endDate', $endDate);
            $reportTitle = $departname[$departid]['department'] . "设备巡查计划概况列表";
            $reportConditions = "统计日期：" . $startDate . ' 至 ' . $endDate;
            $this->assign('reportTitle', $reportTitle);
            $this->assign('reportConditions', $reportConditions);
            $this->display('departPlanDetail');
        }
    }

    /**
     * Notes: 查看科室设备
     */
    private function view()
    {
        $assnum = I('GET.assnum');
        $cid = I('GET.cid');
        $startDate = I('GET.startDate');
        $endDate = I('GET.endDate');
        if (!$cid) {
            $this->error('该设备没有巡查计划记录！');
        }
        $cid = explode(',', $cid);
        //获取设备计划明细
        $patrolModel = new PatrolPlanCycleModel();
        $lists = $patrolModel->getPlanDetail($cid);
        foreach ($lists as $k => $v) {
            $lists[$k]['isNormal'] = '否';
            $lists[$k]['abnormalDetail'] = '保养结果无异常';
            if ($v['abnormal_assnum']) {
                if (in_array($assnum, json_decode($v['abnormal_assnum']))) {
                    $lists[$k]['isNormal'] = '是';
                }
            }
        }
        //查询异常项详情
        $abnormal = $patrolModel->getAbnormalDetail($assnum, $cid);
        if ($abnormal) {
            //查询所有的保养项目类别和明细
            $points = $patrolModel->DB_get_all('patrol_points', 'ppid,name,parentid', array('1'));
            $pfather = array();
            $pson = array();
            foreach ($points as $k => $v) {
                if ($v['parentid'] == 0) {
                    $pfather[$v['ppid']]['name'] = $v['name'];
                } else {
                    $pson[$v['ppid']]['name'] = $v['name'];
                    $pson[$v['ppid']]['parentid'] = $v['parentid'];
                }
            }
            foreach ($pson as $k => $v) {
                $pson[$k]['cateName'] = $pfather[$v['parentid']]['name'];
            }
            foreach ($lists as $k => $v) {
                if ($v['isNormal'] == '是') {
                    $lists[$k]['abnormalDetail'] = '';
                }
                foreach ($abnormal as $k1 => $v1) {
                    if ($v['cycid'] == $v1['cycid']) {
                        $lists[$k]['abnormalDetail'] .= ' <span style="color:#8BB9FF;">' . $pson[$v1['ppid']]['cateName'] . '</span>' . '->' . '<span style="color:#8BB9FF;">' . $pson[$v1['ppid']]['name'] . '</span>' . ' ' . ' 保养结果为：' . '<span style="color:red;">' . $v1['result'] . '</span>' . '(' . $v1['abnormal_remark'] . ')<br/>';
                    }
                }
                $lists[$k]['abnormalDetail'] = trim($lists[$k]['abnormalDetail'], '<br/>');
            }
        }
        $asInfo = $patrolModel->DB_get_one('assets_info', 'assets', array('assnum' => $assnum));
        $reportTitle = "设备巡查计划记录";
        $reportConditions = "设备名称：" . $asInfo['assets'] . "(" . $assnum . ") / 统计日期：" . $startDate . ' 至 ' . $endDate;
        $this->assign('reportTitle', $reportTitle);
        $this->assign('reportConditions', $reportConditions);
        $this->assign('assnum', $assnum);
        $this->assign('cid', implode(',', $cid));
        $this->assign('startDate', $startDate);
        $this->assign('endDate', $endDate);
        $this->assign('lists', $lists);
        $this->display('view');
    }

    /**
     * Notes: 导出设备计划列表
     */
    public function exportPlanLists()
    {
        if (IS_POST) {
            $assnum = I('POST.assnum');
            $cid = I('POST.cid');
            $startDate = I('POST.startDate');
            $endDate = I('POST.endDate');
            if (!$cid) {
                $this->error('该设备没有巡查计划记录！');
            }
            $cid = explode(',', $cid);
            //获取设备计划明细
            $patrolModel = new PatrolPlanCycleModel();
            $data = $patrolModel->getPlanDetail($cid);
            foreach ($data as $k => $v) {
                $data[$k]['isNormal'] = '否';
                $data[$k]['abnormalDetail'] = '保养结果无异常';
                if ($v['abnormal_assnum']) {
                    if (in_array($assnum, json_decode($v['abnormal_assnum']))) {
                        $data[$k]['isNormal'] = '是';
                    }
                }
            }
            //查询异常项详情
            $abnormal = $patrolModel->getAbnormalDetail($assnum, $cid);
            if ($abnormal) {
                //查询所有的保养项目类别和明细
                $points = $patrolModel->DB_get_all('patrol_points', 'ppid,name,parentid', array('1'));
                $pfather = array();
                $pson = array();
                foreach ($points as $k => $v) {
                    if ($v['parentid'] == 0) {
                        $pfather[$v['ppid']]['name'] = $v['name'];
                    } else {
                        $pson[$v['ppid']]['name'] = $v['name'];
                        $pson[$v['ppid']]['parentid'] = $v['parentid'];
                    }
                }
                foreach ($pson as $k => $v) {
                    $pson[$k]['cateName'] = $pfather[$v['parentid']]['name'];
                }
                foreach ($data as $k => $v) {
                    if ($v['isNormal'] == '是') {
                        $data[$k]['abnormalDetail'] = '';
                    }
                    foreach ($abnormal as $k1 => $v1) {
                        if ($v['cycid'] == $v1['cycid']) {
                            $data[$k]['abnormalDetail'] .= $pson[$v1['ppid']]['cateName'] . '->' . $pson[$v1['ppid']]['name'] . ' ' . ' 保养结果为：' . $v1['result'] . '(' . $v1['abnormal_remark'] . ')';
                        }
                    }
                    $data[$k]['abnormalDetail'] = trim($data[$k]['abnormalDetail'], '<br/>');
                }
            }
            //要显示的数据
            $showName = array('patrolnum' => '计划编号', 'patrolname' => '计划名称', 'executor' => '执行人', 'complete_time' => '完成时间', 'isNormal' => '异常', 'abnormalDetail' => '异常情况详情');
            $asInfo = $patrolModel->DB_get_one('assets_info', 'assets', array('assnum' => $assnum));
            $tableHeader = '设备计划记录';
            $tips = "设备名称：" . $asInfo['assets'] . "(" . $assnum . ") / 统计日期：" . $startDate . ' 至 ' . $endDate;
            $otherInfo['titleFontSize'] = 22;
            $otherInfo['titleRowHeight'] = 40;
            exportExcelPlanLists($sheetTitle = array('设备计划记录'), $tableHeader, $showName, $data, $tableHeader, $tips, $otherInfo);
        }
    }
}