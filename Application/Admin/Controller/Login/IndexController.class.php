<?php

namespace Admin\Controller\Login;

use Admin\Model\AssetsInfoModel;
use Admin\Model\MenuModel;
use Admin\Model\NoticeModel;
use Admin\Model\RepairModel;
use Admin\Model\StatisticsModel;
use Admin\Model\PatrolPlanModel;
use Admin\Model\PatrolModel;

class IndexController extends CheckLoginController
{
    protected function __initialize(){
        parent::_initialize();
    }

    public function topmenu(){
        $this->assign('num',session('taskCount'));
        $this->display();
    }
    public function index()
    {
      
        $this->display();
    }
    public function ajaxMenu()
    {
        $menuid = I('POST.memuid');
        $menu = M('menu');
        $condition['status'] = 0;
        $all= $menu->where($condition)->order('orderid ASC')->select();
        $res = array();
        $i = 0;
        foreach($all as $k=>$v){
            if($v['parentid'] == $menuid){
                $res[$i]['menuid'] = $v['menuid'];
                $res[$i]['parentid'] = $v['parentid'];
                $res[$i]['name'] = $v['name'];
                $i++;
            }
        }
        foreach ($res as $k=>$v){
            foreach ($all as $k1=>$v1){
                if($v1['parentid'] == $v['menuid']){
                    $res[$k]['sondata'][] = $v1;
                }
            }
        }
        foreach ($res as $k=>$v){
            $res[$k]['sonNum'] = count($v['sondata']);
        }
        $this->assign('res',$res);
        $this->display();
    }

    public function frameset(){
        $this->display();
    }
    public function leftmenu(){
        $this->display();
    }
    public function main(){
        $this->display("Baobiao/index");
    }
    public function side(){
        $this->display();
    }
    public function foot(){
        $this->display();
    }

    public function taskList(){
        $this->display();
    }

    public function payItem(){
        $this->display();
    }

    public function layout()
    {
        if(C('IS_OPEN_BRANCH')){
            $idstr = session('manager_hospitalid');
            $hosids = explode(',',$idstr);
            if(count($hosids) > 1){
                $this->assign('showChangHospitalOption',true);
            }else{
                $this->assign('showChangHospitalOption',false);
            }
        }else{
            $this->assign('showChangHospitalOption',false);
        }
        $this->assign('sessionUserid', session('userid'));
        $this->display();
    }
    public function target()
    {
        $hospital_id = session('current_hospitalid');
        $departids = session('departid');
        if(IS_POST){
            $staModel = new StatisticsModel();
            $action = I('post.action');
            switch ($action){
                case 'survey_setting':
                    $chart_type = $_POST['chart_type'];
                    unset($_POST['action']);
                    unset($_POST['chart_type']);
                    $ids = [];
                    $ids[] = 'repair_scrap_assets';
                    foreach($_POST as $k=>$v){
                        $ids[] = $k;
                    }
                    if(count($ids) > 5){
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'最多只能设置五个显示项！'));
                    }else{
                        $result = $staModel->save_show_setting($ids,$hospital_id,'survey');
                        //更改显示类型
                        $staModel->updateData('target_statistic_setting',array('chart_type'=>$chart_type),array('user_id'=>session('userid'),'set_hospital_id'=>$hospital_id,'set_type'=>'survey'));
                        $this->ajaxReturn($result);
                    }
                    break;
                case 'show_setting':
                    unset($_POST['action']);
                    $ids = [];
                    foreach($_POST as $k=>$v){
                        $ids[] = $k;
                    }
                    $result = $staModel->save_show_setting($ids,$hospital_id,'detail');
                    $this->ajaxReturn($result);
                    break;
                case 'getAssetsData':
                    $res = $staModel->get_assets_data();
                    $this->ajaxReturn($res);
                    break;
                case 'target_chart_assets_repair':
                    //设备维修情况
                    $year = I('post.year');//查询期限
                    $count_type = I('post.count_type');//查询类型
                    $show_type = I('post.show_type');
                    $current_year = date('Y');
                    $year_data = [];
                    if($year == 3){
                        //最近三年数据
                        for ($i = 0;$i < 3;$i++){
                            $year_data[$i] = $staModel->getDepartFreeEachMonths($hospital_id,$current_year - $i,$departids);
                        }
                        //组织近三年数据
                        $result = $this->formatYearData($year_data,$count_type,$show_type);
                        $this->ajaxReturn($result);
                        break;
                    }else{
                        $result = $staModel->get_assets_repair_data($year,$count_type,$hospital_id,$departids);
                        $this->ajaxReturn($result);
                        break;
                    }
                    break;
                case 'target_chart_depart_repair':
                    //科室维修费用分析
                    $year = I('post.year');//查询期限
                    $data = $staModel->get_department_repair_free($hospital_id,$departids,$year);
                    $result = $staModel->format_department_repair_free($data);
                    $this->ajaxReturn($result);
                    break;
                case 'target_chart_assets_add':
                    //全院设备增加情况
                    $year = I('post.year');//查询期限
                    $count_type = I('post.count_type');//统计类型
                    if($count_type == 'trend'){
                        $data = $staModel->get_assets_add($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_data_months_trend($data);
                        $this->ajaxReturn($result);
                    }elseif($count_type == 'depart'){
                        $data = $staModel->get_assets_department_add($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_data_department_analysis($data);
                        $this->ajaxReturn($result);
                    }
                    break;
                case 'target_chart_assets_scrap':
                    //全院设备报废情况
                    $year = I('post.year');//查询期限
                    $count_type = I('post.count_type');//统计类型
                    if($count_type == 'trend'){
                        $data = $staModel->get_assets_scrap($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_data_months_trend($data);
                        $this->ajaxReturn($result);
                    }elseif($count_type == 'depart'){
                        $data = $staModel->get_assets_department_scrap($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_data_department_analysis($data);
                        $this->ajaxReturn($result);
                    }
                    break;
                case 'target_chart_assets_adverse':
                    //设备不良事件情况
                    $year = I('post.year');//查询期限
                    $count_type = I('post.count_type');//统计类型12
                    if($count_type == 'trend'){
                        $data = $staModel->get_assets_adverse($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_data_months_trend($data);
                        $this->ajaxReturn($result);
                    }elseif($count_type == 'depart'){
                        $data = $staModel->get_assets_department_adverse($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_data_department_analysis($data);
                        $this->ajaxReturn($result);
                    }
                    break;
                case 'target_chart_assets_purchases':
                    //设备采购费用情况
                    $year = I('post.year');//查询期限
                    $count_type = I('post.count_type');//统计类型
                    if($count_type == 'trend'){
                        $data = $staModel->get_assets_purchases($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_purchases($data);
                        $this->ajaxReturn($result);
                    }elseif($count_type == 'free'){
                        $data = $staModel->get_assets_department_free_purchases($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_data_department_analysis($data);
                        $this->ajaxReturn($result);
                    }elseif($count_type == 'nums'){
                        $data = $staModel->get_assets_department_nums_purchases($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_data_department_analysis($data);
                        $this->ajaxReturn($result);
                    }
                    break;
                case 'target_chart_assets_benefit':
                    //设备效益情况
                    $count_type = I('post.count_type');
                    $year = I('post.year');//查询期限
                    if($count_type == 'trend'){
                        //收支趋势
                        $data = $staModel->get_assets_benefit($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_benefit($data);
                        $this->ajaxReturn($result);
                    }elseif($count_type == 'income'){
                        //收入分析
                        $data = $staModel->get_assets_depart_benefit($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_depart_benefit($data);
                        $this->ajaxReturn($result);
                    }elseif($count_type == 'expenditure'){
                        //支出分析
                        $data = $staModel->get_assets_benefit_expenditure($hospital_id,$year,$departids);
                        $result = $staModel->format_assets_benefit_expenditure($data);
                        $this->ajaxReturn($result);
                    }
                    break;
                case 'target_chart_assets_move':
                    //设备不良事件情况
                    $year = I('post.year');//查询期限
                    //$count_type = I('post.count_type');//统计类型12
                    $transfer_data = $staModel->get_assets_transfer($hospital_id,$year,$departids);
                    $outside_data = $staModel->get_assets_outside($hospital_id,$year,$departids);
                    $borrow_in_data = $staModel->get_assets_borrow_in($hospital_id,$year,$departids);
                    $borrow_out_data = $staModel->get_assets_borrow_out($hospital_id,$year,$departids);
                    $result['transfer_data'] = $transfer_data;
                    $result['outside_data'] = $outside_data;
                    $result['borrow_in_data'] = $borrow_in_data;
                    $result['borrow_out_data'] = $borrow_out_data;
                    $this->ajaxReturn($result);
                    break;
                case 'target_chart_assets_patrol':
                    //设备保养情况
                    $year = I('post.year');//查询期限
                    $count_type = I('post.count_type');//统计类型
                    if($count_type == 'trend'){
                        $data = $staModel->get_assets_patrol_times($year);
                        $result = $staModel->format_assets_data_months_trend($data);
                    }else{
                        $result = $staModel->get_assets_patrol_abnormal($year);
                    }
                    $this->ajaxReturn($result);
                    break;
            }
        }else{
            $action = I('get.action');
            $staModel = new StatisticsModel();
            //查询系统设置的统计显示设置
            $sys_showids = $staModel->get_target_setting();
            if(session('isSuper')){
                //超级管理员
                $role_setting['detail'] = $sys_showids;
                $role_setting['survey'] = array('insurance_assets', 'special_assets', 'lifesupport_assets', 'big_assets', 'firstaid_assets', 'quality_assets', 'metering_assets', 'Inspection_assets', 'maintain_assets');
            }else{
                //查询角色配置的统计显示设置
                $role_setting = $staModel->get_role_target_setting();
                //排序掉系统设置为不显示的图表
                foreach ($role_setting['detail'] as $k=>$v){
                    if(!in_array($v,$sys_showids)){
                        unset($role_setting['detail'][$k]);
                    }
                }
            }
            //查询当前用户已有的统计显示设置
            $user_id = session('userid');
            $chartids = $staModel->get_show_setting($user_id,$hospital_id,'detail');
            $show_ids = $show_survey_ids = [];
            foreach ($chartids as $v){
                $show_ids[] = $v['chart_id'];
            }
            //排序掉系统设置为不显示的、没权限查看的图表
            foreach ($show_ids as $k=>$v){
                if(!in_array($v,$role_setting['detail'])){
                    unset($show_ids[$k]);
                }
            }
            $surveyids = $staModel->get_show_setting($user_id,$hospital_id,'survey');
            foreach ($surveyids as $k=>$v){
                //排除掉没权限显示的图表
                if(in_array($v['chart_id'],$role_setting['survey'])){
                    $show_survey_ids[] = $v['chart_id'];
                }
            }
            $this->assign('sys_showids',$role_setting['detail']);
            $this->assign('survey_showids',$role_setting['survey']);
            $this->assign('show_ids',$show_ids);
            $this->assign('show_survey_ids',$show_survey_ids);
            if($action == 'survey_setting'){
                $this->assign('chart_type',$surveyids[0]['chart_type']);
                $this->display('survey_setting');
            }elseif($action == 'show_setting'){
                $this->display('show_setting');
            }else{
                $noticeModel = new NoticeModel();
                $noticeinfo = $noticeModel->DB_get_all('notice','notid,title,adddate,top',array('hospital_id'=>$hospital_id),'','top desc,notid desc',2);
                foreach($noticeinfo as $k => $v){
                    $noticeinfo[$k]['date'] = getHandleTime(strtotime($v['adddate']));
                }
                $this->assign('noticeinfo',$noticeinfo);
                //获取最近一周最新报修设备数量
                $now = time();
                $result = [];
                for($i=6;$i>=0;$i--){
                    $result[] = date('Y-m-d',strtotime('-'.$i.' day', $now));
                }
                $xAxisData = $seriesDdata = $seriesDdata_patrol = array();
                //日期数据
                foreach ($result as $k=>$v){
                    $xAxisData[] = date('m-d',strtotime($v));
                }
                $repairModel = new RepairModel();
                //维修数据
                $seriesDdata[0]['name'] = '报修';
                $seriesDdata[0]['type'] = 'line';
                $seriesDdata[0]['smooth'] = true;
                $seriesDdata[0]['itemStyle']['normal']['areaStyle']['type'] = 'default';
                $seriesDdata[1]['name'] = '修复';
                $seriesDdata[1]['type'] = 'line';
                $seriesDdata[1]['smooth'] = true;
                $seriesDdata[1]['itemStyle']['normal']['areaStyle']['type'] = 'default';
                foreach ($result as $k=>$v){
                    $start = $v.' 00:00:00';
                    $end   = $v.' 23:59:59';
                    $s = strtotime($start);
                    $e = strtotime($end);
                    //最近一周报修设备数量
                    $where['A.applicant_time'] = array(array('EGT',$s),array('ELT',$e));
                    $where['B.departid'] = array('in',$departids);
                    $where['B.hospital_id'] = $hospital_id;
                    $where['B.is_delete'] =  C('NO_STATUS');
                    $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
                    $seriesDdata[0]['data'][] = $repairModel->DB_get_count_join('repair','A',$join,$where);
                    //最近一周修复设备数量
                    $where_1['A.overdate'] = array(array('EGT',$s),array('ELT',$e));
                    $where_1['A.status'] = 8;
                    $where_1['A.over_status'] = 1;
                    $where_1['B.departid'] = array('in',$departids);
                    $where_1['B.hospital_id'] = $hospital_id;
                    $where_1['B.is_delete'] =  C('NO_STATUS');
                    $join_1 = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
                    $seriesDdata[1]['data'][] = $repairModel->DB_get_count_join('repair','A',$join_1,$where_1);

                    $patrol_join = "LEFT JOIN sb_patrol_plans_cycle as B ON A.cycid = B.cycid LEFT JOIN sb_patrol_plans as C ON C.patrid = B.patrid";
                    $where_patrol['A.finish_time'] = array(array('EGT',$start),array('ELT',$end));
                    $where_patrol['C.hospital_id'] = $hospital_id;
                    //$join_1 = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
                    $seriesDdata_patrol[] = $repairModel->DB_get_count_join('patrol_execute','A',$patrol_join,$where_patrol);
                }
                //统计当月设备修复率计环比数据
                $monthStart = date('Y-m');
                $monthStart = strtotime($monthStart);
                $now = time();
                //当月报修数量
                $whereMonth['A.applicant_time'] = array(array('EGT',$monthStart),array('ELT',$now));
                $whereMonth['B.hospital_id'] = $hospital_id;
                $whereMonth['B.is_delete'] =  C('NO_STATUS');
                $joinmonth = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
                $applicantNum = $repairModel->DB_get_count_join('repair','A',$joinmonth,$whereMonth);


                //当月报修且修复数量
                $whereMonth['A.status'] = 8;
                $whereMonth['A.over_status'] = 1;
                $repairedNum = $repairModel->DB_get_count_join('repair','A',$joinmonth,$whereMonth);
                $rate = round(($repairedNum/$applicantNum)*100);
                //上月数据
                $preMonthStart = date('Y-m-01', strtotime('-1 month'));
                $preMonthStart = strtotime($preMonthStart);
                $preMonthEnd = date('Y-m-t', strtotime('-1 month'));
                $preMonthEnd = strtotime($preMonthEnd)+3600*24-1;
                $wherePreMonth['A.applicant_time'] = array(array('EGT',$preMonthStart),array('ELT',$preMonthEnd));
                $wherePreMonth['B.hospital_id'] = $hospital_id;
                $wherePreMonth['B.is_delete'] =  C('NO_STATUS');
                $joinPREmonth = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
                $applicantPreNum = $repairModel->DB_get_count_join('repair','A',$joinPREmonth,$wherePreMonth);
                //当月报修且修复数量
                $wherePreMonth['A.status'] = 8;
                $wherePreMonth['A.over_status'] = 1;
                $repairedPreNum = $repairModel->DB_get_count_join('repair','A',$joinPREmonth,$wherePreMonth);
                if ($applicantPreNum==0) {
                    $ratePre = 0;
                }else{
                    $ratePre = round(($repairedPreNum/$applicantPreNum)*100);
                }

                if($rate >= $ratePre){
                    $arrow = 'top';
                }else{
                    $arrow = 'bottom';
                }
                //查询设备最新状态
                //最新报修
                $rwhere['B.is_delete'] =  C('NO_STATUS');
                $rjoin = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
                $rwhere['A.status'] = array('lt',7);
                $rwhere['B.hospital_id'] = $hospital_id;
                $newAddRepair = $repairModel->DB_get_one_join('repair','A','A.repid,A.assid,B.assnum,B.assets,B.departid',$rjoin,$rwhere,'','A.repid desc');
                $ojoin = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
                $owhere['A.status'] = array('gt',6);
                $owhere['B.hospital_id'] = $hospital_id;
                $owhere['B.is_delete'] =  C('NO_STATUS');
                $newOverRepair = $repairModel->DB_get_one_join('repair','A','A.repid,A.assid,B.assnum,B.assets,B.departid',$ojoin,$owhere,'','A.repid desc');
                $newAddAssets = $repairModel->DB_get_one('assets_info','assid,assnum,assets,departid',array('hospital_id'=>$hospital_id,'is_delete'=>0),'assid desc');
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                if($newAddRepair){
                    $newAddRepair['department'] = $departname[$newAddRepair['departid']]['department'];
                }
                if($newOverRepair) {
                    $newOverRepair['department'] = $departname[$newOverRepair['departid']]['department'];
                }
                if($newAddAssets) {
                    $newAddAssets['department'] = $departname[$newAddAssets['departid']]['department'];
                }
                $this->assign('xAxisData',json_encode($xAxisData));
                $this->assign('seriesDdata',json_encode($seriesDdata));
                $this->assign('seriesDdata_patrol',json_encode($seriesDdata_patrol));
                $this->assign('rate',$rate);
                $this->assign('ratePre',$ratePre);
                $this->assign('arrow',$arrow);
                $this->assign('newAddRepair',$newAddRepair);
                $this->assign('newOverRepair',$newOverRepair);
                $this->assign('newAddAssets',$newAddAssets);
                //最近五年
                $years = [];
                $current_year = date('Y');
                for ($i = 0;$i < 30;$i++){
                    $years[] = $current_year-$i;
                }
                $this->assign('years',$years);
                $this->assign('current_year',$current_year);
                $this->display();
            }
        }
    }

    private function formatYearData($year_data,$count_type,$show_type)
    {
        $current_year = date('Y');
        $option = [];
        if(!$count_type){
            return false;
        }
        for ($i = 0;$i < 3;$i++){
            $option['legend'][] = (string)($current_year - $i);
            if($show_type == 'bar'){
                $option['tooltip']['axisPointer']['type'] = 'shadow';
            }else{
                $option['tooltip']['axisPointer']['type'] = 'line';
            }
            $option['series'][$i]['name'] = $current_year - $i;
            $option['series'][$i]['type'] = $show_type;
            //$option['series'][$i]['stack'] = '总量';
            for ($k = 0;$k < 12;$k++){
                $option['xAxis']['data'][$k] = ($k+1).' 月';
                $option['series'][$i]['data'][$k] = 0;
                $option['series'][$i]['smooth'][$k] = true;//圆滑曲线
            }
        }
        switch ($count_type){
            case 'times':
                //$option['title'] = '维修次数';
                $option['yAxis']['name'] = '单位：次';
                foreach ($year_data as $k=>$value){
                    foreach ($value as $k1=>$v1){
                        for($i = 0;$i < 12;$i++){
                            $option['series'][$k]['data'][$i] += $v1['repair_num_'.($i+1)];
                        }
                    }
                }
                break;
            case 'hours':
                //$option['title'] = '维修工时';
                $option['yAxis']['name'] = '单位：时';
                foreach ($year_data as $k=>$value){
                    foreach ($value as $k1=>$v1){
                        for($i = 0;$i < 12;$i++){
                            $option['series'][$k]['data'][$i] += $v1['repair_hours_'.($i+1)];
                        }
                    }
                }
                break;
            case 'free':
                //$option['title'] = '维修费用';
                $option['yAxis']['name'] = '单位：元';
                foreach ($year_data as $k=>$value){
                    foreach ($value as $k1=>$v1){
                        for($i = 0;$i < 12;$i++){
                            $option['series'][$k]['data'][$i] += $v1['repair_price_'.($i+1)];
                        }
                    }
                }
                break;
        }
        return $option;
    }

    private function formatYearData_1($year_data,$count_type,$show_type)
    {
        $current_year = date('Y');
        $option = [];
        $option['legend'][] = $count_type == 'free' ? '维修费用' : '维修次数';
        if($show_type == 'bar'){
            $option['tooltip']['axisPointer']['type'] = 'shadow';
        }else{
            $option['tooltip']['axisPointer']['type'] = 'line';
        }
        $option['series'][$i]['name'] = $current_year - $i;
        $option['series'][$i]['type'] = $show_type;
        //$option['series'][$i]['stack'] = '总量';
        for ($k = 0;$k < 12;$k++){
            $option['xAxis']['data'][$k] = ($k+1).' 月';
            $option['series'][$i]['data'][$k] = 0;
            $option['series'][$i]['smooth'][$k] = true;//圆滑曲线
        }
        switch ($count_type){
            case 'times':
                $option['title'] = '维修次数';
                $option['yAxis']['name'] = '单位：次';
                foreach ($year_data as $k=>$value){
                    foreach ($value as $k1=>$v1){
                        for($i = 0;$i < 12;$i++){
                            $option['series'][$k]['data'][$i] += $v1['repair_num_'.($i+1)];
                        }
                    }
                }
                break;
            case 'hours':
                $option['title'] = '维修工时';
                $option['yAxis']['name'] = '单位：时';
                foreach ($year_data as $k=>$value){
                    foreach ($value as $k1=>$v1){
                        for($i = 0;$i < 12;$i++){
                            $option['series'][$k]['data'][$i] += $v1['repair_hours_'.($i+1)];
                        }
                    }
                }
                break;
            case 'free':
                $option['title'] = '维修费用';
                $option['yAxis']['name'] = '单位：元';
                foreach ($year_data as $k=>$value){
                    foreach ($value as $k1=>$v1){
                        for($i = 0;$i < 12;$i++){
                            $option['series'][$k]['data'][$i] += $v1['repair_price_'.($i+1)];
                        }
                    }
                }
                break;
        }
        return $option;
    }
}