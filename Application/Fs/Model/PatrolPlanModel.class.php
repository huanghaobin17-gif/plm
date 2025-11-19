<?php


namespace Fs\Model;


class PatrolPlanModel extends CommonModel
{
    /*
     * 查询巡查保养计划
     */
    public function getPatrolLists()
    {
        $departids = session('departid');
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('get.order') ? I('get.order') : 'asc';
        $sort = I('get.sort') ? I('get.sort') : 'patrol_status';
        $search = I('get.search');
        $hospital_id = I('get.hospital_id');
        $patrids = [];

        $all_assets = $this->DB_get_all('assets_info','assid',['departid'=>array('in',$departids)]);
        $all_assids = [];
        foreach ($all_assets as $v){
            $all_assids[] = $v['assid'];
        }
        $all_patrass = $this->DB_get_all('patrol_plans_assets','patrid',['assid'=>array('in',$all_assids)]);
        if(!$all_patrass){
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
            $result['total'] = 0;
            return $result;
        }
        foreach ($all_patrass as $v){
            $patrids[] = $v['patrid'];
        }

        $where['patrid'] = ['in',$patrids];
        //['patrol_status'] = ['in',[3,4]];
        $where['patrol_status'] = 3;
        $where['is_delete'] = C('NO_STATUS');
        $where['hospital_id'] = $hospital_id;
        //计划名称搜索
        if ($search) {
            $where['patrol_name'] = ['like', "%$search%"];
        }
        if ($hospital_id) {
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }
        //需要的字段
        $fields = 'patrid,patrol_name,patrol_level,patrol_status,remark,patrol_start_date,patrol_end_date,is_cycle,cycle_unit,cycle_setting,total_cycle,current_cycle';
        //获取总条数
        $total = $this->DB_get_count('patrol_plans',$where);
        //获取数据
        $patrol = $this->DB_get_all('patrol_plans', $fields,$where, '', $sort . ' ' . $order.',patrid desc', $offset . "," . $limit);
        if (!$patrol) {
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
            $result['total'] = 0;
            return $result;
        }
        foreach ($patrol as $k => $v) {
            //查询该计划的设备
            $patrol_assnums = $this->DB_get_one('patrol_plans_assets','group_concat(assnum) as patrol_assnums',['patrid'=>$v['patrid']]);
            $patrol[$k]['patrol_assnums'] = $patrol_assnums['patrol_assnums'];
            $patrol[$k]['percent'] = sprintf("%.2f", $v['current_cycle'] / $v['total_cycle'] * 100);
            $patrol[$k]['patrol_date'] = $v['patrol_start_date'] . ' 至 ' . $v['patrol_end_date'];
            $patrol[$k]['cycle_name'] = $v['is_cycle'] ? '是' : '否';
            $patrol[$k]['total_cycle'] = $v['total_cycle'] ? $v['total_cycle'] : '-';
            $patrol[$k]['current_cycle'] = $v['current_cycle'] ? $v['current_cycle'] : '-';

            if($v['is_cycle']){
                switch ($v['cycle_unit']) {
                    case 'day':
                        $patrol[$k]['cycle_setting_name'] = '每'.$v['cycle_setting'].'天';
                        break;
                    case 'week':
                        $patrol[$k]['cycle_setting_name'] = '每'.$v['cycle_setting'].'周';
                        break;
                    case 'month':
                        $patrol[$k]['cycle_setting_name'] = '每'.$v['cycle_setting'].'月';
                        break;
                }
            }else{
                $patrol[$k]['cycle_setting_name'] = '无';
            }

            $patrol[$k]['patrol_status'] = (int)$v['patrol_status'];
            if ($v['patrol_status'] == 4) {
                //逾期
                $patrol[$k]['operation'] = 'showPlans';
                $patrol[$k]['operation_name'] = '已结束';
                $patrol[$k]['bg_color'] = '#7232dd';
            } else {
                //执行中
                $patrol[$k]['operation'] = 'showPlans';
                $patrol[$k]['operation_name'] = C('PLAN_CYCLE_EXECUTION_NAME');
                $patrol[$k]['bg_color'] = '#BDB76B';
            }
            switch ($v['patrol_level']) {
                case 1:
                    $patrol[$k]['patrol_level_name'] = '日常保养';
                    break;
                case 2:
                    $patrol[$k]['patrol_level_name'] = '巡查保养';
                    break;
                case 3:
                    $patrol[$k]['patrol_level_name'] = '预防性维护';
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
        $result['page'] = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result['rows'] = $patrol;
        $result['status'] = 1;
        return $result;
    }


    /**
     * Notes: 获取计划信息
     * @param $patrid int 计划ID
     */
    public function get_plan_info($patrid)
    {
        $fields = "*";
        $where['is_delete'] = C('NO_STATUS');
        $where['patrid'] = $patrid;
        $where['hospital_id'] = session('current_hospitalid');
        $patrol_info = $this->DB_get_one('patrol_plan', $fields, $where);
        //获取计划执行人
        $executors = $this->DB_get_one('patrol_plan_cycle', 'group_concat(distinct executor) as executors,sum(abnormal_sum) as abnormal_sum', array('patrid' => $patrol_info['patrid'], 'patrol_level' => $patrol_info['patrol_level']));
        $patrol_info['abnormal_sum'] = $executors['abnormal_sum'];
        $patrol_info['executors'] = $executors['executors'];
        switch ($patrol_info['patrol_status']) {
            case '1':
                $patrol_info['patrol_status_name'] = '待审批';
                break;
            case '2':
                $patrol_info['patrol_status_name'] = '待修订';
                break;
            case '3':
                $patrol_info['patrol_status_name'] = '待发布';
                break;
            case '4':
                $patrol_info['patrol_status_name'] = '待实施';
                break;
            case '5':
                $patrol_info['patrol_status_name'] = '待验收';
                break;
            case '6':
                $patrol_info['patrol_status_name'] = '已结束';
                break;
            case '7':
                $patrol_info['patrol_status_name'] = '已逾期';
                break;
        }
        return $patrol_info;
    }

    /**
     * Notes: 获取计划设备列表
     * @param $patrol_info array 计划信息集合
     */
    public function get_plan_assets($patrol_info)
    {
        $departids = session('departid');
        switch ($patrol_info['patrol_level']) {
            case C('PATROL_LEVEL_PM'):
                //上一次巡查日期
                $fields = 'assid,assets,status,assnum,assorignum,model,departid,pre_patrol_date as pre_date';
                break;
            default:
                //上一次保养日期
                $fields = 'assid,assets,status,assnum,assorignum,model,departid,pre_maintain_date as pre_date';
                break;
        }
        $assnum = explode(',', $patrol_info['patrol_assnums']);
        $asWhere['assnum'] = array('in', $assnum);
        //$asWhere['departid'] = array('in', $departids);
        $assInfo = $this->DB_get_all('assets_info', $fields, $asWhere);
        //获取计划设备模板名称及明细项数目
        $where['patrid'] = $patrol_info['patrid'];
        $where['patrol_level'] = $patrol_info['patrol_level'];
        $cycle_info = $this->DB_get_all('patrol_plan_cycle', 'cycid,executor,assnum_tpid,plan_assnum,sign_info', $where);
        $assnum_tpid = [];
        $assnum_name = [];
        $assnum_cycid = [];
        $assnum_sign_info = [];
        $cycids = [];
        foreach ($cycle_info as $k => $v) {
            $tpids = json_decode($v['assnum_tpid'], true);
            foreach ($tpids as $kk => $vv) {
                $assnum_tpid[$kk] = $vv;
                $assnum_name[$kk] = $v['executor'];
                $assnum_cycid[$kk] = $v['cycid'];
                $assnum_sign_info[$kk] = $v['sign_info'];//新增签到
                $cycids[] = $v['cycid'];
            }
        }
        //查询模板明细及明细项数
        $temModel = M('patrol_template');
        $temps = $temModel->where(array('is_delete' => 0))->getField('tpid,name,points_num');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assInfo as $k => $v) {
            $assInfo[$k]['patrol_level'] = $patrol_info['patrol_level'];
            switch ($patrol_info['patrol_level']) {
                case C('PATROL_LEVEL_DC'):
                    $assInfo[$k]['patrol_level_name'] = C('PATROL_LEVEL_NAME_DC');
                    break;
                case C('PATROL_LEVEL_RC'):
                    $assInfo[$k]['patrol_level_name'] = C('PATROL_LEVEL_NAME_RC');
                    break;
                case C('PATROL_LEVEL_PM'):
                    $assInfo[$k]['patrol_level_name'] = C('PATROL_LEVEL_NAME_PM');
                    break;
            }
            switch ($v['status']) {
                case C('ASSETS_STATUS_USE'):
                    $assInfo[$k]['status_name'] = '<span style="color:#5FB878">'.C('ASSETS_STATUS_USE_NAME').'</span>';
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    //设备维修中
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">'.C('ASSETS_STATUS_REPAIR_NAME').'</span>';
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    //设备已报废
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">'.C('ASSETS_STATUS_SCRAP_NAME').'</span>';
                    break;
                case C('ASSETS_STATUS_OUTSIDE'):
                    //设备已外调
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">'.C('ASSETS_STATUS_OUTSIDE_NAME').'</span>';
                    break;
                case C('ASSETS_STATUS_OUTSIDE_ON'):
                    //设备外调中
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">'.C('ASSETS_STATUS_OUTSIDE_ON_NAME').'</span>';
                    break;
                case C('ASSETS_STATUS_SCRAP_ON'):
                    //设备报废中
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">'.C('ASSETS_STATUS_SCRAP_ON_NAME').'</span>';
                    break;
                case  C('ASSETS_STATUS_TRANSFER_ON'):
                    //设备转科中
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">'.C('ASSETS_STATUS_TRANSFER_ON_NAME').'</span>';
                    break;
                case '':
                    break;
            }
            $assInfo[$k]['cycid'] = $assnum_cycid[$v['assnum']];
            $assInfo[$k]['sign_info'] = $assnum_sign_info[$v['assnum']];//新增签到信息
            $assInfo[$k]['executor'] = $assnum_name[$v['assnum']];
            $assInfo[$k]['department'] = $departname[$v['departid']]['department'];
            $assInfo[$k]['details_num'] = count(json_decode($temps[$assnum_tpid[$v['assnum']]]['points_num'], true));
            $assInfo[$k]['template_name'] = '<span style="cursor: pointer;color: #01AAED;" data-id="' . $assnum_tpid[$v['assnum']] . '" class="show_template">' . $temps[$assnum_tpid[$v['assnum']]]['name'] . '</span>';
        }
        $tmpdata =  array_column($assInfo, 'executor'); //取出数组中executor的一列，返回一维数组
        array_multisort($tmpdata, SORT_DESC, $assInfo);//排序，根据$status 排序
        return $assInfo;
    }

    /**
     * Notes:获取计划审批信息
     * @param $patrid int 计划ID
     */
    public function get_approve_info($patrid)
    {
        $where['is_delete'] = C('NO_STATUS');
        $where['approve_class'] = 'patrol';
        $where['process_node'] = C('PATROL_APPROVE');
        $where['patrid'] = $patrid;
        $apps = $this->DB_get_all('approve', 'apprid,patrid,approver,approve_time,is_adopt,remark', $where, '', 'process_node_level,apprid asc');
        foreach ($apps as $k => $v) {
            $apps[$k]['approve_time'] = date('Y-m-d H:i', $v['approve_time']);
            if ($v['is_adopt'] == 1) {
                $apps[$k]['approve_status'] = '通过';
            } else {
                $apps[$k]['approve_status'] = '驳回';
            }
        }
        return $apps;
    }


    //任务列表
    public function getTasksLists()
    {
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('get.order') ? I('get.order') : 'asc';
        $sort = I('get.sort') ? I('get.sort') : 'B.patrol_status';
        $search = I('get.search');
        $patrid = I('get.patrid');
        $hospital_id = I('get.hospital_id');

        $where['B.is_delete'] = C('NO_STATUS');
        $where['B.hospital_id'] = $hospital_id;
        $where['A.patrid'] = $patrid;
        $where['B.patrol_status'] = ['in',[3,4]];
        //计划名称搜索
        if ($search) {
            $where['B.patrol_name'] = ['like', "%$search%"];
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        //判断是否开启签到
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        if ($baseSetting['patrol']['patrol_wx_set_situation']['value'] == C('OPEN_STATUS')) {
            $wx_sign_in = 1;
        } else {
            $wx_sign_in = 0;
        }
        $join = " LEFT JOIN sb_patrol_plans AS B ON A.patrid = B.patrid";
        $fileds = 'A.patrol_num,A.period,B.patrol_name,A.cycle_start_date,A.cycle_end_date,B.patrol_level,B.remark,B.is_cycle,A.not_operation_assnum,A.patrid,A.cycid,A.plan_assnum,A.cycle_status,B.patrol_status,A.abnormal_sum,A.implement_sum';
        $total = $this->DB_get_count_join('patrol_plans_cycle', 'A', $join, $where);
        $tasks = $this->DB_get_all_join('patrol_plans_cycle', 'A', $fileds, $join, $where, '', 'B.patrol_status asc,' . $sort . ' ' . $order, $offset . "," . $limit);
        if (!$tasks) {
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
            $result['total'] = 0;
            return $result;
        }
        foreach ($tasks as $key => $value) {
            $not_patrol_nums = 0;
            if ($value['not_operation_assnum']) {
                $not_patrol_nums = count(json_decode($value['not_operation_assnum']));
            }
            $tasks[$key]['abnormal_sum'] = $value['abnormal_sum'] > 0 ? '<span style="color: red;">异常数 ' . $value['abnormal_sum'] . '</span>' : '异常数 '.$value['abnormal_sum'];
            $tasks[$key]['numstatus'] = $value['implement_sum'] . ' / ' . count(json_decode($value['plan_assnum']));
            $tasks[$key]['percent'] = sprintf("%.2f", $value['implement_sum'] / (count(json_decode($value['plan_assnum']))) * 100);
            $tasks[$key]['personal_percent'] = $tasks[$key]['percent'];

            /*重新梳理巡查状态 S*/
            switch ($value['cycle_status']){
                case '0':
                    $tasks[$key]['operation'] = 'detail';
                    $tasks[$key]['operation_name'] = '待执行';
                    $tasks[$key]['bg_color'] = '#1989fa';
                    $tasks[$key]['yijian_show'] = true;
                    break;
                case '1':
                    $tasks[$key]['operation'] = 'detail';
                    $tasks[$key]['operation_name'] = '执行中';
                    $tasks[$key]['bg_color'] = '#1989fa';
                    $tasks[$key]['yijian_show'] = true;
                    break;
                case '2':
                    $tasks[$key]['operation'] = 'detail';
                    $tasks[$key]['operation_name'] = '按期完成';
                    $tasks[$key]['bg_color'] = '#07c160';
                    $tasks[$key]['yijian_show'] = false;
                    break;
                case '3':
                    $tasks[$key]['operation'] = 'detail';
                    $tasks[$key]['operation_name'] = '逾期完成';
                    $tasks[$key]['bg_color'] = '#ff976a';
                    $tasks[$key]['yijian_show'] = false;
                    break;
                case '4':
                    $tasks[$key]['operation'] = 'detail';
                    $tasks[$key]['operation_name'] = '逾期未完成';
                    $tasks[$key]['bg_color'] = '#ee0a24';
                    $tasks[$key]['yijian_show'] = true;
                    break;
            }
            switch ($value['patrol_level']) {
                case C('PATROL_LEVEL_DC'):
                    $tasks[$key]['patrol_level_name'] = C('PATROL_LEVEL_NAME_DC');
                    break;
                case C('PATROL_LEVEL_RC'):
                    $tasks[$key]['patrol_level_name'] = C('PATROL_LEVEL_NAME_RC');
                    break;
                case C('PATROL_LEVEL_PM'):
                    $tasks[$key]['patrol_level_name'] = C('PATROL_LEVEL_NAME_PM');
                    break;
            }
        }
        //查询当前用户是否有执行权限
        $result['page'] = (int)$page;
        $result['pages'] = ceil((int)$total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result['rows'] = $tasks;
        $result['need_sign'] = $wx_sign_in;//签到功能是否开启
        $result['status'] = 1;
        return $result;
    }

    /*
   * 通过执行人获取已发布的计划ID条件
   * @params1 $executor string 执行人
   * @params2 $alias string 条件前缀
   * return string
   */
    public function adoptExecutorPatrid($executor)
    {
        $where['executor'] = $executor;
        $cyc = $this->DB_get_all('patrol_plan_cycle', 'patrid', $where, 'patrid');
        if ($cyc) {
            $str = '';
            foreach ($cyc as $cycValue) {
                $str .= "," . $cycValue['patrid'];
            }
            $patridWhere = ['IN', trim($str, ',')];
        } else {
            $patridWhere = ['IN', [-1]];
        }
        return $patridWhere;
    }

    /**
     * Notes: 扫码签到
     * @return array
     */
    public function scanQRCode_signin()
    {
        $departid = session('departid');
        $assnum = I('post.assnum');
        $cycid = I('post.cycid');
        $url = '/Patrol/operation?patrid=50&operation=showPlans';
        if (!$assnum) {
            return array(
                'status' => -1,
                'msg' => '扫码错误！',
                'url' => $this->fail_url . '?url=' . $url . '&tips=扫码错误，请重新扫码&btn=本计划设备列表'
            );
        }
        //微信扫码签到进入
        $exists = $this->DB_get_one('assets_info', 'assid,assnum,departid', array('assnum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        if (!$exists) {//先判断原设备码
            $exists = $this->DB_get_one('assets_info', 'assid,assnum,departid', array('assorignum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        }
        if (!$exists) {
            //设备原编码备用
            $exists = $this->DB_get_one('assets_info', 'assid,assnum,departid', array('assorignum_spare' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        }
        if (!$exists) {
            return array(
                'status' => -1,
                'msg' => '查找不到编码为【' . $assnum . '】的设备信息'
            );
        }
        if (!in_array($exists['departid'], explode(',', $departid))) {
            return array(
                'status' => -1,
                'msg' => '您无权操作该部门设备!'
            );
        }
        //查询该设备签到信息
        $signInfo = $this->DB_get_one('patrol_plan_cycle', 'sign_info', ['cycid' => $cycid]);
        if ($signInfo['sign_info']) {
            $repeatInfo = json_decode($signInfo['sign_info'], true);
            if (isset($repeatInfo[$exists['assnum']])) {
                return ['status' => -1, 'msg' => '您已签到，请勿重复签到！'];
//                if ($repeatInfo[$exists['assnum']]['executor'] == session('username')) {
//                    return ['status' => -1, 'msg' => '您已签到，请勿重复签到！'];
//                } else {
//                    return ['status' => -1, 'msg' => '该设备已有签到相关信息，签到人： ' . $repeatInfo[$exists['assnum']]['executor']];
//                }
            } else {
                $repeatInfo[$exists['assnum']] = [
                    'executor' => session('username'),
                    'sign_in_time' => getHandleDate(time()),
                    'latitude' => I('post.latitude'),
                    'longitude' => I('post.longitude'),
                ];
                $res = $this->updateData('patrol_plan_cycle', json_encode($repeatInfo), ['cycid' => $cycid]);
            }
        } else {
            $updata[$exists['assnum']] = [
                'executor' => session('username'),
                'sign_in_time' => getHandleDate(time()),
                'latitude' => I('post.latitude'),
                'longitude' => I('post.longitude'),
            ];
            //保存签到数据
            $res = $this->updateData('patrol_plan_cycle', json_encode($updata), ['cycid' => $cycid]);
        }
//        \Think\Log::write('repid================>'.$repinfo['repid']);
//        \Think\Log::write('sign_in_time================>'.$updata['sign_in_time']);
//        \Think\Log::write('latitude================>'.$updata['latitude']);
//        \Think\Log::write('longitude==================>'.$updata['longitude']);
        if ($res) {
            return array(
                'status' => 1,
                'msg' => '签到成功！'
            );
        } else {
            return array(
                'status' => -1,
                'msg' => '保存签到信息失败' . $res
            );
        }
    }
}
