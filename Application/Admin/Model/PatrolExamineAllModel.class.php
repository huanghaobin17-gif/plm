<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Think\Model;

class PatrolExamineAllModel extends CommonModel
{
    protected $MODULE = 'Patrol';
    protected $Controller = 'Patrol';
    protected $tableName = 'patrol_examine_all';
    protected $tableFields = 'exallid,patrid,patrolname,patrolnum,cycid,cyclenum,patrol_level,period,executor,repair_num,
    scrap_num,abnormal_num,abnormal_point_num,cycle_startdate,cycle_overdate,examine_username,examdate,examine_departid,
    status,remark,assnum_num,completiondate';

    /*
    * 获取验证表信息
    * @parmas1 string $exallid 实施ID
    * @parmas2 Booleans $skipCheck 是否需要验证科室
    * return array
    */
    public function getExamine($exallid, $skipCheck = false)
    {
        $where = "exallid=$exallid";
        if (!$skipCheck) {
            if (!session('isSuper')) {
                $where .= " AND examine_departid IN (" . session('departid') . ")";
            }
        }
        $fileds = 'exallid,cycid,patrol_level,examine_departid,cyclenum,abnormal_num,assnum_num,completiondate,
        cycle_startdate,cycle_overdate,abnormal_point_num,patrolname,status,examine_username,examdate,remark';
        $data = $this->DB_get_one('patrol_examine_all', $fileds, $where);
        if (!$data) {
            return false;
        }
        //查询计划信息
        $tpids = $this->DB_get_one('patrol_plan_cycle','assnum_tpid',array('cycid'=>$data['cycid']));
        $tpidsarr = json_decode($tpids['assnum_tpid'],true);
        $ids = [];
        foreach ($tpidsarr as $v){
            $ids[] = $v;
        }
        $data['point_num'] = 0;
        if($ids){
            $numarr = $this->DB_get_all('patrol_template','points_num',array('tpid'=>array('in',$ids)));
            foreach ($numarr as $v){
                $data['point_num'] += count(json_decode($v['points_num'],true));
            }
        }
        $data['cycleDate'] = $data['cycle_startdate'] . ' 至 ' . $data['cycle_overdate'];
        return $data;
    }


    //验收页面 返回设备列表
    public function doExamineList()
    {
        $assets = I('POST.assets');
        $order = I('POST.order');
        $sort = I('POST.sort');
        $limit = I('post.limit') ? I('post.limit') : C('PAGE_NUMS');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $exallid = I('POST.exallid');
        $where = "A.exallid=$exallid AND B.cycid=A.cycid";
        if ($assets) {
            $assetData = $this->DB_get_ALL('assets_info', 'assnum', "assets='$assets'");
            if ($assetData) {
                $where .= " AND A.assnum IN(";
                $assnumWhere = '';
                foreach ($assetData as &$one) {
                    $assnumWhere .= ",'" . $one['assnum'] . "'";
                }
                $assnumWhere = trim($assnumWhere, ',');
                $where = $where . $assnumWhere . ')';
            } else {
                $result["rows"] = [];
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        }
        $join[0] = 'LEFT JOIN sb_patrol_execute AS B ON B.assetnum=A.assnum';
        $join[1] = 'LEFT JOIN sb_assets_info AS C ON C.assnum=A.assnum';
        $join[2] = 'LEFT JOIN sb_patrol_plan_cycle AS D ON D.cycid=B.cycid';
        $fileds = 'A.exoneid,A.assnum,C.assets,C.model,C.catid,C.is_firstaid,C.is_special,C.is_metering,C.is_qualityAssets,
        C.is_benefit,D.executor,B.finish_time,B.asset_status,B.asset_status_num,B.remark,B.execid';
        $asArr = $this->DB_get_all_join('patrol_examine_one', 'A', $fileds, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asArr) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $execidstr = '';
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        foreach ($asArr as &$one) {
            if ($one['is_firstaid'] == C('YES_STATUS')) {
                $one['type_name'] = C('ASSETS_FIRST_CODE_YES_NAME');
            }
            if ($one['is_special'] == C('YES_STATUS')) {
                $one['type_name'] .= ',' . C('ASSETS_SPEC_CODE_YES_NAME');
            }
            if ($one['is_metering'] == C('YES_STATUS')) {
                $one['type_name'] = C('ASSETS_METER_CODE_YES_NAME');
            }
            if ($one['is_qualityAssets'] == C('YES_STATUS')) {
                $one['type_name'] .= ',' . C('ASSETS_QUALITY_CODE_YES_NAME');
            }
            if($one['is_benefit']==C('YES_STATUS')){
                $one['type_name'] .= ',' . C('ASSETS_BENEFIT_CODE_YES_NAME');
            }
            $one['type_name'] = ltrim($one['type_name'], ",");
            $one['finish_time'] = getHandleTime($one['finish_time']);
            $one['cat_name'] = $catname[$one['catid']]['category'];
            $execidstr .= ',' . $one['execid'];
        }
        $execidstr = trim($execidstr, ',');
        $menuData = get_menu($this->MODULE, 'Patrol', 'examine');
        $where = "execid IN ($execidstr)";
        $fields = 'abnid,execid,result,abnormal_remark';
        $abnormalarr = $this->DB_get_all('patrol_execute_abnormal', $fields, $where, '', 'result ASC');
        foreach ($asArr as &$value) {
            $value['abnormal_num'] = 0;
            $value['point'] = 0;
            $value['remark_title'] = '';
            $value['abnormal'] = [];
            foreach ($abnormalarr as &$abnormal) {
                if ($value['execid'] == $abnormal['execid']) {
                    $value['point']++;
                    if ($abnormal['result'] != '合格') {
                        $value['abnormal_num']++;
                        if ($value['remark_title']) {
                            $value['remark_title'] .= '<br>' . $value['abnormal_num'] . ':' . $abnormal['abnormal_remark'];
                        } else {
                            $value['remark_title'] .= $value['abnormal_num'] . ':' . $abnormal['abnormal_remark'];
                        }
                    }
                    array_push($value['abnormal'], $abnormal['result']);
                }
            }
            if (!$value['remark_title']) {
                $value['remark_title'] = '— —';
            }
            $value['abnormal'] = array_unique($value['abnormal']);
            $type = '';
            $name = '';
            $color = '';
            if (count($value['abnormal']) == 1) {
                switch ($value['asset_status_num']) {
                    case C('ASSETS_STATUS_IN_MAINTENANCE'):
                        $value['abnormal'] = C('ASSETS_STATUS_IN_MAINTENANCE_SNAME');
                        break;
                    case C('ASSETS_STATUS_SCRAPPED'):
                        $value['abnormal'] = C('ASSETS_STATUS_SCRAPPED_SNAME');
                        break;
                    default:
                        $value['abnormal'] = join(',', $value['abnormal']);
                        break;
                }
                if ($value['abnormal'] != '合格') {
                    $color = ' layui-btn-warm';
                }
                $name = $value['abnormal'];
            } else {
                $color = ' layui-btn-warm';
                foreach ($value['abnormal'] as &$one) {
                    if ($one != '合格') {
                        $name = $one . '...';
                    }
                }
                $value['abnormal'] = join(',', $value['abnormal']);
                $type = 'title="' . $value['abnormal'] . '"';
            }
            $value['count'] = "<span class='rquireCoin'>" . $value['abnormal_num'] . "</span>/" . $value['point'];
            if ($menuData) {
                $value['operation'] = $this->returnListLink($name, $this->full_open_url($this->MODULE, $this->Controller) . 'examine?action=examineOne&exoneid='.$value['exoneid'], 'examine', C('BTN_CURRENCY') . $color, '', $type);
            } else {
                $value['operation'] = $this->returnListLink($name, $this->full_open_url($this->MODULE, $this->Controller) . 'doTask?action=openExamineOne&exoneid=' . $value['exoneid'], 'examine', C('BTN_CURRENCY') . $color, '', $type);
            }
        }
        $result["rows"] = $asArr;
        $result['code'] = 200;
        return $result;
    }

    /*
     * 返回 验收单台设备-设备信息
     * @parmas1 int $exoneid 检查ID
     * return array
     */
    public function getPatrolExamineOne($exoneid)
    {
        return $this->DB_get_one('patrol_examine_one', 'exoneid,exallid,cycid,assnum,status,examdate,examine_username,remark', "exoneid=$exoneid");
    }


    /*
     * 返回设备信息
     * @parmas1 string $assnum 设备编号
     * return array
     */
    public function getAssets($assnum)
    {
        $fileds = 'assets,assid,assnum,catid,departid,status,is_firstaid,is_special,is_metering,is_qualityAssets,is_benefit,model';
        $where = "assnum='" . $assnum . "'";
        $asArr = $this->DB_get_one('assets_info', $fileds, $where);
        $departname = [];
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $asArr['department_name'] = $departname[$asArr['departid']]['department'];
        $asArr['cat_name'] = $catname[$asArr['catid']]['category'];
        switch ($asArr['status']) {
            case
            $asArr['status_name'] = C('ASSETS_STATUS_USE_NAME');
                break;
            case C('ASSETS_STATUS_REPAIR'):
                $asArr['status_name'] = C('ASSETS_STATUS_REPAIR_NAME');
                break;
            case C('ASSETS_STATUS_SCRAP'):
                $asArr['status_name'] = C('ASSETS_STATUS_SCRAP_NAME');
                break;
        }
        if ($asArr['is_firstaid'] == C('YES_STATUS')) {
            $asArr['type_name'] = C('ASSETS_FIRST_CODE_YES_NAME');
        }
        if ($asArr['is_special'] == C('YES_STATUS')) {
            $asArr['type_name'] .= ',' . C('ASSETS_SPEC_CODE_YES_NAME');
        }
        if ($asArr['is_metering'] == C('YES_STATUS')) {
            $asArr['type_name'] = C('ASSETS_METER_CODE_YES_NAME');
        }
        if ($asArr['is_qualityAssets'] == C('YES_STATUS')) {
            $asArr['type_name'] .= ',' . C('ASSETS_QUALITY_CODE_YES_NAME');
        }
        if($asArr['is_benefit']==C('YES_STATUS')){
            $asArr['type_name'] .= ',' . C('ASSETS_BENEFIT_CODE_YES_NAME');
        }
        $asArr['type_name'] = ltrim($asArr['type_name'], ",");
        return $asArr;
    }

    //验收单台设备操作
    public function examineOne()
    {
        $cycid = I('POST.cycid');
        $assnum = I('POST.assnum');
        $remark = trim(I('POST.remark'));
        if(!$cycid || !$assnum){
            return array('status' => -1, 'msg' => '参数错误！');
        }
        //查询该周期计划是否已验收
        $cyc_info = $this->DB_get_one('patrol_plans_cycle','*',array('cycid'=>$cycid));
        if(!$cyc_info){
            return array('status' => -1, 'msg' => '查询不到该周期信息！');
        }
        if($cyc_info['check_status'] == 1){
            return array('status' => -1, 'msg' => '该计划已验收！');
        }
        $repair_assnum = count(json_decode($cyc_info['repair_assnum'],true));
        $scrap_assnum = count(json_decode($cyc_info['scrap_assnum'],true));
        $total_assets = count(json_decode($cyc_info['plan_assnum'],true));
        //查询验收信息
        $where['cycid'] = $cycid;
        $where['assnum'] = $assnum;
        $data = $this->DB_get_one('patrol_examine_one', 'exoneid,status', $where);
        if ($data) {
            return array('status' => -98, 'msg' => '该设备已验收请勿重复操作');
        }
        $data['cycid'] = $cycid;
        $data['assnum'] = $assnum;
        $data['status'] = C('CYCLE_COMPLETE');
        $data['examdate'] = date('Y-m-d H:i:s');
        $data['examine_username'] = session('username');
        $data['remark'] = $remark;
        $save= $this->insertData('patrol_examine_one', $data);
        if (!$save) {
            return array('status' => -199, 'msg' => '验收失败');
        }

        //修改设备巡查计划状态为0
        $this->updateData('assets_info',array('patrol_in_plan'=>0),array('assnum'=>$assnum));
        //单个验收完成，查询周期计划的设备是否已全部验收完成，如已验收完成，则修改周期计划patrol_plans_cycle已验收1
        $total = $this->DB_get_count('patrol_examine_one',array('cycid'=>$cycid));
        if($total == $total_assets){
            //已验收设备数量等于周期计划设备数量，代表该周期计划已全部验收，修改周期计划patrol_plans_cycle已验收1
            $this->updateData('patrol_plans_cycle',array('check_status'=>1),array('cycid'=>$cycid));
            //如sb_patrol_examine_all 没有该计划验收信息，则写入一条信息
            $all_exam = $this->DB_get_one('patrol_examine_all','exallid',array('cycid'=>$cycid));
            if(!$all_exam){
                //未有记录，写入一条信息
                $plan_info = $this->DB_get_one('patrol_plans','patrol_level,patrol_name',array('patrid'=>$cyc_info['patrid']));
                //统计异常项明细数量
                $join = "LEFT JOIN sb_patrol_execute_abnormal AS B ON A.execid = B.execid";
                $ab_nums = $this->DB_get_count_join('patrol_execute','A',$join,array('A.cycid'=>$cycid,'B.result'=>array('neq','合格')));
                //获取执行人信息
                $execute_user = $this->DB_get_one('patrol_execute',"group_concat(distinct execute_user) as execute_user",['cycid'=>$cycid]);
                $all_data['cycid'] = $cycid;
                $all_data['patrid'] = $cyc_info['patrid'];
                $all_data['patrolname'] = $plan_info['patrol_name'];
                $all_data['patrolnum'] = $cyc_info['patrol_num'];
                $all_data['patrol_level'] = $plan_info['patrol_level'];
                $all_data['executor'] = $execute_user['execute_user'];
                $all_data['repair_num'] = $repair_assnum;
                $all_data['scrap_num'] = $scrap_assnum;
                $all_data['abnormal_num'] = $cyc_info['abnormal_sum'];
                $all_data['abnormal_point_num'] = $ab_nums;
                $all_data['exam_user'] = session('username');
                $all_data['exam_time'] = date('Y-m-d H:i:s');
                $all_data['examine_departid'] = '';
                $all_data['status'] = 1;
                $all_data['remark'] = '';
                $all_data['assnum_num'] = $total_assets;
                $all_data['completion_time'] = date('Y-m-d H:i:s');
                $exallid = $this->insertData('patrol_examine_all',$all_data);
                //更新sb_patrol_examine_one 中的字段exallid 为 $exallid
                $this->updateData('patrol_examine_one',array('exallid'=>$exallid),array('cycid'=>$cycid));
            }
            //查询该级别计划是否已全部完成
            $total_cycle = $this->DB_get_one('patrol_plans','total_cycle',array('patrid'=>$cyc_info['patrid']));
            $total_check = $this->DB_get_count('patrol_plans_cycle',['patrid'=>$cyc_info['patrid'],'check_status'=>1]);
            if($total_cycle['total_cycle'] == $total_check){
                //该级别计划已全部验收完成，修改patrol_plans 中patrol_status为已结束4
                $this->updateData('patrol_plans',array('patrol_status'=>4),array('patrid'=>$cyc_info['patrid']));
            }
        }
        return array('status' => 1, 'msg' => '验收成功');
    }

    /*
    * 科室批量验收操作
    * @parmas1 int $patrid 计划ID
    * @parmas1 string $remark 备注
    * return 1 or false
    */
    public function examineAll()
    {
        $cycid = I('POST.cycid');
        $patrid = I('POST.patrid');
        $remark = trim(I('POST.remark'));

        if(!$patrid){
            return array('status'=>-1,'msg'=>'参数错误！');
        }
        $planInfo = $this->DB_get_one('patrol_plans','patrid,patrol_name,patrol_level,patrol_status',array('patrid'=>$patrid));
        if(!$planInfo){
            return array('status'=>-1,'msg'=>'查询不到该计划信息！');
        }
        //查询该计划下的周期信息
        $cycle_where['patrid'] = $patrid;
        $cycle_where['cycid'] = $cycid;
        $cycleInfo = $this->DB_get_one('patrol_plans_cycle','*',$cycle_where);
        $i = $j = 0;
        $one_add = $all_add = [];
        $plan_assnum = json_decode($cycleInfo['plan_assnum'],true);
        //查询该计划设备是否已全部验收
        foreach ($plan_assnum as $one){
            $one_where['cycid'] = $cycid;
            $one_where['assnum'] = $one;
            $examone = $this->DB_get_one('patrol_examine_one','exoneid',$one_where);
            if(!$examone){
                //更新设备巡查计划状态为0
                $this->updateData('assets_info',array('patrol_in_plan'=>0),array('assnum'=>$one));
                //不存在单台验收记录
                $one_add[$i]['exallid']  = 0;
                $one_add[$i]['cycid'] = $cycid;
                $one_add[$i]['assnum'] = $one;
                $one_add[$i]['status'] = 1;
                $one_add[$i]['examdate'] = date('Y-m-d H:i:s');
                $one_add[$i]['examine_username'] = session('username');
                $one_add[$i]['remark'] = $remark;
                $i++;
            }
        }
        if($one_add){
            $this->insertDataALL('patrol_examine_one',$one_add);
        }

        //查询sb_patrol_examine_all 是否有对应的记录
        $all_where['cycid'] = $cycid;
        $all_where['patrid'] = $patrid;
        $examall = $this->DB_get_one('patrol_examine_all','exallid',$all_where);
        if(!$examall){
            //统计异常项明细数量
            $join = "LEFT JOIN sb_patrol_execute_abnormal AS B ON A.execid = B.execid";
            $ab_nums = $this->DB_get_count_join('patrol_execute','A',$join,array('A.cycid'=>$cycid,'B.result'=>array('neq','合格')));
            $total_assets = count(json_decode($cycleInfo['plan_assnum'],true));
            //获取执行人信息
            $execute_user = $this->DB_get_one('patrol_execute',"group_concat(distinct execute_user) as execute_user",['cycid'=>$cycid]);
            //不存在相应验收记录
            $all_add['cycid'] = $cycid;
            $all_add['patrid'] = $patrid;
            $all_add['patrolname'] = $planInfo['patrol_name'];
            $all_add['patrolnum'] = $cycleInfo['patrol_num'];
            $all_add['patrol_level'] = $planInfo['patrol_level'];
            $all_add['executor'] = $execute_user['execute_user'];
            $all_add['repair_num'] = count(json_decode($cycleInfo['repair_num'],true));
            $all_add['scrap_num'] = count(json_decode($cycleInfo['scrap_num'],true));
            $all_add['abnormal_num'] = $cycleInfo['abnormal_sum'];
            $all_add['abnormal_point_num'] = $ab_nums;
            $all_add['exam_user'] = session('username');
            $all_add['exam_time'] = date('Y-m-d H:i:s');
            $all_add['examine_departid'] = session('departid');
            $all_add['status'] = 1;
            $all_add['remark'] = $remark;
            $all_add['assnum_num'] = $total_assets;
            $all_add['completion_time'] = date('Y-m-d H:i:s');
            $newAllId = $this->insertData('patrol_examine_all',$all_add);
            $this->updateData('patrol_examine_one',array('exallid'=>$newAllId),array('cycid'=>$cycid));
            //周期计划状态变更为已验收
            $this->updateData('patrol_plans_cycle',array('check_status'=>1),array('cycid'=>$cycid));
        }
        //查询该级别计划是否已全部完成
        $total_cycle = $this->DB_get_one('patrol_plans','total_cycle',array('patrid'=>$cycleInfo['patrid']));
        $total_check = $this->DB_get_count('patrol_plans_cycle',['patrid'=>$cycleInfo['patrid'],'check_status'=>1]);
        if($total_cycle['total_cycle'] == $total_check){
            //该级别计划已全部验收完成，修改patrol_plans 中patrol_status为已结束4
            $this->updateData('patrol_plans',array('patrol_status'=>4),array('patrid'=>$cycleInfo['patrid']));
        }
        return array('status'=>1,'msg'=>'验收成功！');
    }

    /**
     * Notes: 获取保养记录信息
     * @param $execid int 保养记录ID
     */
    public function get_execute_info($execid)
    {
        return $this->DB_get_one('patrol_execute','*',array('execid'=>$execid));
    }

    /**
     * Notes: 获取单个设备的验收记录
     * @param $cycid int 周期ID
     * @param $assnum string 设备编码
     */
    public function get_one_examine_info($cycid,$assnum)
    {
        $data = $this->DB_get_one('patrol_examine_one','*',array('cycid'=>$cycid,'assnum'=>$assnum));
        if ($data) {
            if ($data['asset_status_num'] != C('ASSETS_STATUS_NORMAL') or $data['asset_status_num'] != C('ASSETS_STATUS_SMALL_PROBLEM')) {
                $data['asset_status'] = '<span class="rquireCoin">' . $data['asset_status'] . '</span>';
            } else {
                $data['asset_status'] = '<span class="grenn">' . $data['asset_status'] . '</span>';
            }
        }
        return $data;
    }

}
