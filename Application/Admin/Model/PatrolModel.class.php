<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;

class PatrolModel extends CommonModel
{
    private $MODULE = 'Patrol';
    private $Controller = 'Patrol';
    protected $tableName = 'patrol_plan';

    //新增巡查保养=====>查询到的设备列表
    public function getAddAssetslist()
    {
        $departid    = session('departid');
        $assName     = I('POST.assName');
        $assNum      = I('POST.assNum');
        $assetsCat   = I('POST.assCat');
        $dids        = I('POST.departids');
        $guarantee   = I('POST.guarantee');
        $expire      = I('POST.expire');
        $startPrice  = I('POST.startPrice');
        $endPrice    = I('POST.endPrice');
        $type        = I('POST.type');
        $removedata  = I('POST.removedata');
        $hospital_id = session('current_hospitalid');
        $limit       = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page        = I('post.page') ? I('post.page') : 1;
        $offset      = ($page - 1) * $limit;
        if (!$departid) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $patrol_xc_cycle        = I('post.patrol_xc_cycle');
        $patrol_pm_cycle        = I('post.patrol_pm_cycle');
        $quality_cycle          = I('post.quality_cycle');
        $metering_cycle         = I('post.metering_cycle');
        $where['A.departid'][]  = ['IN', $departid];
        $where['A.hospital_id'] = $hospital_id;
        $where['A.status'][0]   = 'IN';
        $where['A.status'][1][] = C('ASSETS_STATUS_USE');//0=在用
        $where['A.status'][1][] = C('ASSETS_STATUS_REPAIR');//2=维修中

        //$where['A.patrol_in_plan'] = C('NO_STATUS');//0=不在巡查计划中
        if ($removedata) {
            $where['A.assnum'][] = ['NOT IN', $removedata];
        }
        if (session('notAssnums')) {
            $where['A.assnum'][] = ['NOT IN', "'" . str_replace(",", "','", session('notAssnums')) . "'"];
        }
        //查询即将到期提醒时间
        if ($expire) {
            $PatrolPlanModel = new PatrolPlanModel();
            $assids          = $PatrolPlanModel->expiring();
            //即将到期 -- 是
            if ($assids == "") {
                $assids = 'A';
            }
            if ($expire == 1) {
                $where['A.assid'] = ['IN', $assids];
            }
            //即将到期 -- 否
            if ($expire == 2) {
                $where['A.assid'] = ['NOT IN', $assids];
            }
        }
        //保内
        if ($guarantee == 1) {
            $AssetsInsuranceModel = new AssetsInsuranceModel();
            $GuaranteeData        = $AssetsInsuranceModel->returnGuaranteeData();
            $where['A.assid']     = ['IN', $GuaranteeData];
        }
        //保外
        if ($guarantee == 2) {
            $AssetsInsuranceModel = new AssetsInsuranceModel();
            $GuaranteeData        = $AssetsInsuranceModel->returnGuaranteeData();
            $where['A.assid']     = ['NOT IN', $GuaranteeData];
        }
        //设备名称搜索
        if ($assName) {
            $where['A.assets'] = ['LIKE', '%' . $assName . '%'];
        }
        //设备编号搜索
        if ($assNum) {
            $where['A.assnum'] = $assNum;
        }
        //部门搜索
        if ($dids != '') {
            $where['A.departid'] = ['in', $dids];
        }
        //最小金额搜索
        if ($startPrice) {
            $this->checkstatus(judgeNum($startPrice), '请输入大于等于0的最小金额');
            $where['A.buy_price'][] = ['EGT', $startPrice];
        }
        //最大金额搜索
        if ($endPrice) {
            $this->checkstatus(judgeNum($endPrice) && $endPrice > 0, '请输入大于0的最大金额');
            $where['A.buy_price'][] = ['ELT', $endPrice];
        }
        if ($endPrice && $startPrice) {
            if ($startPrice > $endPrice) {
                die(json_encode(['status' => -1, 'msg' => '最大金额必须大于最小金额']));
            }
        }
        //分类搜索
        if ($assetsCat) {
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['like', "%$assetsCat%"];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['A.catid'][]   = ['IN', $catids];
        }
        $typ = explode(',', $type);
        foreach ($typ as &$typeValue) {
            if ($typeValue == 'is_firstaid') {
                $where['A.is_firstaid'] = ['eq', C('ASSETS_FIRST_CODE_YES')];
            }
            if ($typeValue == 'is_special') {
                $where['A.is_special'] = ['eq', C('ASSETS_SPEC_CODE_YES')];
            }
            if ($typeValue == 'is_metering') {
                $where['A.is_metering'] = ['eq', C('ASSETS_METER_CODE_YES')];
            }
            if ($typeValue == 'is_qualityAssets') {
                $where['A.is_qualityAssets'] = ['eq', C('ASSETS_QUALITY_CODE_YES')];
            }
            if ($typeValue == 'is_patrol') {
                $where['A.is_patrol'] = ['eq', 1];
            }
            if ($typeValue == 'is_benefit') {
                $where['A.is_benefit'] = ['eq', C('ASSETS_BENEFIT_CODE_YES')];
            }
        }
        if ($patrol_xc_cycle) {
            //巡查周期
            $where['A.patrol_xc_cycle'] = $patrol_xc_cycle;
        }
        if ($patrol_pm_cycle) {
            //保养周期
            $where['A.patrol_pm_cycle'] = $patrol_pm_cycle;
        }
        if ($quality_cycle) {
            //质控周期
            $where['A.quality_cycle'] = $quality_cycle;
        }
        if ($metering_cycle) {
            //计量周期
            $where['A.metering_cycle'] = $metering_cycle;
        }
        $join               = 'LEFT JOIN sb_assets_insurance as B ON A.assid=B.assid AND B.status=' . C('INSURANCE_STATUS_USE');
        $fileds             = 'A.assid,A.hospital_id,A.assnum,A.assets,A.brand,A.assorignum,A.serialnum,A.catid,A.model,A.departid,A.status,A.is_firstaid,A.is_special,A.is_metering,
        A.is_benefit,A.is_qualityAssets,A.buy_price,A.opendate,A.guarantee_date,A.pre_patrol_date,A.pre_maintain_date,A.patrol_xc_cycle,A.patrol_pm_cycle,B.status AS guarantee_status';
        $where['is_delete'] = '0';
        $total              = $this->DB_get_count_join('assets_info', 'A', $join, $where);
        $asArr              = $this->DB_get_all_join('assets_info', 'A', $fileds, $join, $where, '', 'A.assid DESC',
            $offset . "," . $limit);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$asArr) {
            $asArr = $this->DB_get_all_join('assets_info', 'A', $fileds, $join, $where, '', 'A.assid DESC',
                0 . "," . $limit);
        }
        if (!$asArr) {
            $result["total"] = 0;
            $result['msg']   = '暂无相关数据';
            $result['code']  = 400;
            return $result;
        }
        $departname = [];
        $catname    = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($asArr as &$one) {
            if (!$showPrice) {
                $one['buy_price'] = '***';
            }
            $one['guarantee_date'] = HandleEmptyNull($one['guarantee_date']);
            $one['opendate']       = HandleEmptyNull($one['opendate']);
            $one['department']     = $departname[$one['departid']]['department'];
            $one['category']       = $catname[$one['catid']]['category'];
            $one['operation']      = $this->returnListLink('纳入', '', 'add', C('BTN_CURRENCY'));
            if (getHandleTime(time()) < $one['guarantee_date']) {
                $one['guarantee_status'] = '<span style="color: #5FB878;">保修期内</span>';
            } else {
                if ($one['guarantee_status'] == C('INSURANCE_STATUS_USE')) {
                    $one['guarantee_status'] = '<span style="color: #5FB878;">参保期内</span>';
                } else {
                    $one['guarantee_status'] = '<span style="color: #FF5722;">脱保</span>';
                }
            }
            switch ($one['status']) {
                case C('ASSETS_STATUS_USE'):
                    $one['status_name'] = '<span style="color: #5FB878;">在用</span>';
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $one['status_name'] = '<span style="color: #FF5722;">维修中</span>';
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
            if ($one['is_benefit'] == C('ASSETS_BENEFIT_CODE_YES')) {
                $one['type_name'] .= ',' . C('ASSETS_BENEFIT_CODE_YES_NAME');
            }
            if ($one['is_patrol'] == C('ASSETS_PATROL_CODE_YES')) {
                $one['type_name'] .= ',' . C('ASSETS_PATROL_CODE_YES_NAME');
            }
            $one['type_name'] = ltrim($one['type_name'], ",");
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $asArr;
        return $result;
    }

    //新增巡查保养=====>已纳入计划设备列表
    public function getDelAssetslist()
    {
        $removedata = I('POST.removedata');
        if ($removedata) {
            $where['assnum'] = ['IN', $removedata];
        } elseif (session('notAssnums')) {
            $where['assnum'] = ['IN', session('notAssnums')];
        } else {
            $result["total"] = 0;
            $result['msg']   = '暂无相关数据';
            $result['code']  = 400;
            return $result;
        }
        $asModel = new AssetsInfoModel();
        $total   = $asModel->DB_get_count('assets_info', $where);
        $fileds  = 'brand,serialnum,assorignum,assid,assnum,assets,catid,model,departid,status,is_firstaid,is_special,buy_price,opendate,buy_price,
        is_benefit,is_metering,is_qualityAssets,guarantee_date,pre_patrol_date,pre_maintain_date,patrol_xc_cycle,patrol_pm_cycle';
        $asArr   = $asModel->DB_get_all('assets_info', $fileds, $where, '', 'assid DESC');
        //判断有无查看原值的权限
        $showPrice  = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        $assidstr   = '';
        $acRes      = [];
        $arrIDS     = [];
        $departname = [];
        $catname    = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($asArr as &$one) {
            if (!$showPrice) {
                $one['buy_price'] = '***';
            }
            $one['guarantee_date'] = HandleEmptyNull($one['guarantee_date']);
            $one['opendate']       = HandleEmptyNull($one['opendate']);
            $assidstr              .= $one['assid'] . ',';
            $one['department']     = $departname[$one['departid']]['department'];
            $one['category']       = $catname[$one['catid']]['category'];
            $one['operation']      = $this->returnListLink('移除', '', 'del', C('BTN_CURRENCY') . ' layui-btn-danger');
            if ($one['assid']) {
                $arrIDS[] = (int)$one['assid'];
            }
            switch ($one['status']) {
                case C('ASSETS_STATUS_USE');
                    $one['status_name'] = '<span style="color: #5FB878;">在用</span>';
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $one['status_name'] = '<span style="color: #FF5722;">维修中</span>';
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
            if ($one['is_benefit'] == C('ASSETS_BENEFIT_CODE_YES')) {
                $one['type_name'] .= ',' . C('ASSETS_BENEFIT_CODE_YES_NAME');
            }
            if ($one['is_patrol'] == C('ASSETS_PATROL_CODE_YES')) {
                $one['type_name'] .= ',' . C('ASSETS_PATROL_CODE_YES_NAME');
            }
            $one['type_name'] = ltrim($one['type_name'], ",");
        }
        if ($arrIDS) {
            $insuranceWhere['assid']  = ['IN', $arrIDS];
            $insuranceWhere['status'] = ['eq', C('INSURANCE_STATUS_USE')];
            $insurance                = $asModel->DB_get_all('assets_insurance', 'status,assid', $insuranceWhere, '',
                '', '');
            foreach ($insurance as &$insuranceValue) {
                $acRes[$insuranceValue['assid']]['guarantee_status'] = $insuranceValue['status'];
            }
        }
        foreach ($asArr as &$two) {
            if ($two['guarantee_date'] >= getHandleTime(time())) {
                $two['guarantee_status'] = '<span style="color: #5FB878;">保修期内</span>';
            } else {
                if ($acRes[$two['assid']]['guarantee_status'] == C('INSURANCE_STATUS_USE')) {
                    $two['guarantee_status'] = '<span style="color: #5FB878;">参保期内</span>';
                } else {
                    $two['guarantee_status'] = '<span style="color: #FF5722;">脱保</span>';
                }
            }
        }
        $result["total"] = $total;
        $result["rows"]  = $asArr;
        $result['code']  = 200;
        return $result;
    }


    //任务列表
    public function getTasksList()
    {
        $departids = session('departid');
        //排序加分页加搜索同意
        $limit         = I('POST.limit') ? I('POST.limit') : 10;
        $page          = I('POST.page') ? I('POST.page') : 1;
        $offset        = ($page - 1) * $limit;
        $order         = I('POST.order') ? I('POST.order') : 'ASC';
        $sort          = I('POST.sort') ? I('POST.sort') : 'patrol_status';
        $patrol_name   = I('POST.patrol_name');
        $patrol_status = I('POST.patrol_status');
        $patrol_level  = I('POST.patrol_level');
        $hospital_id   = session('current_hospitalid');
        $patrids       = [];

//        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
//        $all = $this->DB_get_all_join('patrol_plans_assets','A','A.assnum,A.patrid',$join,['B.departid'=>['in',$departids]],'');
//        if(!$all){
//            $result['msg'] = '暂无相关数据';
//            $result['code'] = 400;
//            return $result;
//        }
//        foreach ($all as $v){
//            if(!in_array($v['patrid'],$patrids)){
//                $patrids[] = $v['patrid'];
//            }
//        }

        $all_assets = $this->DB_get_all('assets_info', 'assid', ['departid' => ['in', $departids]]);
//        echo M()->getLastSql();exit;
        $all_assids = [];
//        foreach ($all_assets as $v){
//            $all_assids[] = $v['assid'];
//        }
        $all_assets && $all_assids = array_column($all_assets, 'assid');

        $all_patrass = $this->DB_get_all('patrol_plans_assets', 'patrid', ['assid' => ['in', $all_assids]]);
        if (!$all_patrass) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        } else {
            $patrids = array_column(array_unique($all_patrass, SORT_REGULAR), 'patrid');
        }
//        foreach ($all_patrass as $v){
//            $patrids[] = $v['patrid'];
//        }
        $where['patrid']      = ['in', $patrids];
        $where['is_delete']   = C('NO_STATUS');
        $where['hospital_id'] = $hospital_id;
        if ($patrol_name) {
            //计划名称搜索
            $where['patrol_name'] = ['like', "%$patrol_name%"];
        }
        //保养级别搜索
        if ($patrol_level) {
            $where['patrol_level'] = $patrol_level;
        }
        if ($patrol_status) {
            $where['patrol_status'] = $patrol_status;
        } else {
            $where['patrol_status'] = ['in', [3, 4]];
        }
        //需要的字段
        $fields = 'patrid,patrol_name,patrol_level,patrol_status,remark,patrol_start_date,patrol_end_date,is_cycle,cycle_unit,cycle_setting,total_cycle,current_cycle';
        //获取总条数
        $total = $this->DB_get_count('patrol_plans', $where);
        //获取数据
        $patrol = $this->DB_get_all('patrol_plans', $fields, $where, '', $sort . ' ' . $order . ',patrid desc',
            $offset . "," . $limit);
//        echo M()->getLastSql();exit;
        if (!$patrol) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        foreach ($patrol as $k => $v) {
            $patrol[$k]['patrol_date']   = $v['patrol_start_date'] . ' 至 ' . $v['patrol_end_date'];
            $patrol[$k]['cycle_name']    = $v['is_cycle'] ? '是' : '否';
            $patrol[$k]['total_cycle']   = $v['total_cycle'] ? $v['total_cycle'] : '-';
            $patrol[$k]['current_cycle'] = $v['current_cycle'] ? $v['current_cycle'] : '-';

            $patrol[$k]['patrol_status'] = (int)$v['patrol_status'];
            $html                        = '<div class="layui-btn-group">';
            if ($v['patrol_status'] == 3) {
                $html .= '<button type="button" class="layui-btn layui-btn-xs" lay-event="showPlans">执行</button>';
            }
            if ($v['patrol_status'] == 4) {
                $html .= '<button type="button" class="layui-btn layui-btn-xs layui-btn-warm" lay-event="showPlans">已结束</button>';
            }
            $html                    .= '</div>';
            $patrol[$k]['operation'] = $html;
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
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["rows"]   = $patrol;
        $result["code"]   = 200;
        return $result;
    }


    //科室验收列表公用方法
    public function commonExamineList($where)
    {
        $departids    = session('departid');
        $order        = I('POST.order');
        $sort         = I('POST.sort');
        $patrol_name  = I('POST.patrol_name');
        $patrol_level = I('POST.patrol_level');
        $hospital_id  = I('POST.hospital_id');
        $limit        = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page         = I('post.page') ? I('post.page') : 1;
        $offset       = ($page - 1) * $limit;
        $patrids      = [];

//        $p_join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
//        $all = $this->DB_get_all_join('patrol_plans_assets','A','A.assnum,A.patrid',$p_join,['B.departid'=>['in',$departids]],'A.patrid');
//        if(!$all){
//            $result['msg'] = '暂无相关数据';
//            $result['code'] = 400;
//            return $result;
//        }
//        foreach ($all as $v){
//            $patrids[] = $v['patrid'];
//        }

        $all_assets = $this->DB_get_all('assets_info', 'assid', ['departid' => ['in', $departids]]);
        $all_assids = [];
        foreach ($all_assets as $v) {
            $all_assids[] = $v['assid'];
        }
        $all_patrass = $this->DB_get_all('patrol_plans_assets', 'patrid', ['assid' => ['in', $all_assids]]);
        if (!$all_patrass) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        foreach ($all_patrass as $v) {
            $patrids[] = $v['patrid'];
        }

        $where['A.patrid']      = ['in', $patrids];
        $where['B.is_delete']   = C('NO_STATUS');
        $where['B.hospital_id'] = $hospital_id;

        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        //计划名称搜索
        if ($patrol_name) {
            $where['B.patrol_name'] = ['like', "%$patrol_name%"];
        }

        //保养级别搜索
        if ($patrol_level != '') {
            $where['B.patrol_level'] = $patrol_level;
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        $fileds = 'A.patrol_num,A.cycid,A.patrid,A.period,A.assets_nums,A.abnormal_sum,A.implement_sum,A.cycle_status,A.check_status,A.complete_time,A.not_operation_assnum,B.patrol_level,B.patrol_name,B.is_cycle';
        $m_join = ' LEFT JOIN sb_patrol_plans AS B ON A.patrid = B.patrid ';
        $count  = $this->DB_get_all_join('patrol_plans_cycle', 'A', 'A.patrid', $m_join, $where, 'A.patrid');
        $list   = $this->DB_get_all_join('patrol_plans_cycle', 'A', $fileds, $m_join, $where, 'A.patrid,A.period',
            $sort . ' ' . $order . ',A.cycid desc', $offset . "," . $limit);
        if (!$list) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        foreach ($list as $key => $value) {
            $list[$key]['cycle_name'] = $value['is_cycle'] ? '是' : '否';
            $not_patrol_nums          = 0;
            if ($value['not_operation_assnum']) {
                $not_patrol_nums = count(json_decode($value['not_operation_assnum']));
            }
            $list[$key]['complete_time'] = date('Y-m-d', strtotime($value['complete_time']));
            $list[$key]['numstatus']     = $value['implement_sum'] . ' / ' . ($value['implement_sum'] - $not_patrol_nums) . ' / ' . $value['assets_nums'];
            $class                       = 'examine';
            if (!$value['check_status']) {
                $list[$key]['operation'] = $this->returnListLink('待验收', '', $class, C('BTN_CURRENCY'));
            } else {
                $list[$key]['operation'] = $this->returnListLink('查看', '', $class,
                    C('BTN_CURRENCY') . ' layui-btn-normal');
            }
            $x_join       = "LEFT JOIN sb_patrol_execute_abnormal AS B ON A.execid = B.execid";
            $where        = ['cycid' => $value['cycid']];
            $execute_data = $this->DB_get_all_join('patrol_execute', 'A',
                'count(abnid) as num,result,GROUP_CONCAT(distinct execute_user) AS execute_user', $x_join, $where,
                'B.result', '', '');
            $error_num    = 0;
            $correct_num  = 0;
            $execute_user = [];
            foreach ($execute_data as $k => $v) {
                $execute_user = array_merge($execute_user, explode(',', $v['execute_user']));
                if ($v['result'] != '合格') {
                    $error_num += $v['num'];
                } else {
                    $correct_num += $v['num'];
                }
            }
            $list[$key]['execute_user'] = implode(',', array_unique($execute_user));
            $total_num                  = $correct_num + $error_num;
            $error_num                  = $error_num > 0 ? '<span style="color: red">' . $error_num . '</span>' : '0';
            $list[$key]['abnormal']     = $error_num . ' / ' . $total_num;
        }
        $result["total"]  = count($count);
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["rows"]   = $list;
        $result['code']   = 200;
        return $result;
    }


    /*
     * 获取周期信息
     * @params $cycId 周期ID
     * return array
     */
    public function getCycInfo($cyclenum)
    {
        $join[0] = " LEFT JOIN sb_patrol_plan AS B ON A.patrid = B.patrid";
        $fields  = 'group_concat(A.cycid) as cycid,A.cyclenum,A.assnum_tpid,A.is_release,A.status,B.patrolnum,B.patrolname,B.patrol_level';
        $where   = "A.cyclenum='$cyclenum'";
        //查询当前用户是否有权限创建计划，如果有，则显示该计划所有设备的执行情况
        $canAddPatrol = get_menu($this->MODULE, 'Patrol', 'addPatrol');
        //var_dump(session('isSuper'));exit;
        if (!$canAddPatrol) {
            //当前用户无权创建计划的，则查询对应用户的计划情况
            $fields .= ",A.executor,A.plan_assnum";
            $where  .= " AND executor='" . session('username') . "'";
        } else {
            $fields .= ",group_concat(A.executor) as executor,group_concat(A.plan_assnum) as plan_assnum,group_concat(A.status) as status";
        }
        $cycInfo = $this->DB_get_one_join('patrol_plan_cycle', 'A', $fields, $join, $where);
        if (!$cycInfo) {
            return false;
        }
        if ($canAddPatrol) {
            //获取当前用户的任务状态
            $executorArr = explode(',', $cycInfo['executor']);
            $key         = array_search(session('username'), $executorArr);
            if ($key !== false) {
                $status            = explode(',', $cycInfo['status']);
                $cycInfo['status'] = $status[$key];
            }
        }

        if ($cycInfo) {
            switch ($cycInfo['patrol_level']) {
                case C('PATROL_LEVEL_RC'):
                    $cycInfo['patrol_level_name'] = C('PATROL_LEVEL_NAME_RC');
                    break;
                case  C('PATROL_LEVEL_XC'):
                    $cycInfo['patrol_level_name'] = C('PATROL_LEVEL_NAME_XC');
                    break;
                case  C('PATROL_LEVEL_PM'):
                    $cycInfo['patrol_level_name'] = C('PATROL_LEVEL_NAME_PM');
                    break;
            }
        }
        return $cycInfo;
    }

    /*
    * 获取是否异常的周期
    * @params $error int 是否异常
    * return string
    */
    public function getAbnormalCycnum($error)
    {
        $where         = 'is_release=1';
        $AbnormalWhere = '';
        $cyc           = $this->DB_get_all('patrol_plan_cycle', ' cyclenum,GROUP_CONCAT(abnormal_sum) as abnormal_sum',
            $where, 'cyclenum');
        if ($cyc) {
            foreach ($cyc as &$cycValue) {
                $arr          = explode(',', $cycValue['abnormal_sum']);
                $abnormal_sum = array_sum($arr);
                if ($error == '1' && $abnormal_sum >= 1) {
                    $AbnormalWhere .= ",'" . $cycValue['cyclenum'] . "'";
                } elseif ($error == '0' && $abnormal_sum == 0) {
                    $AbnormalWhere .= ",'" . $cycValue['cyclenum'] . "'";
                }
            }
            if ($AbnormalWhere) {
                $AbnormalWhere = trim($AbnormalWhere, ',');
                $AbnormalWhere = " AND A.cyclenum IN($AbnormalWhere)";
            } else {
                $AbnormalWhere = ' AND 1=0';
            }
        } else {
            $AbnormalWhere = ' AND 1=0';
        }
        return $AbnormalWhere;
    }


    /*
     * 查询计划列表
     * @params $type int 是否不需验证执行人
     * return array
     */
    public function getAllPlans($type)
    {
//        $where = $patrid = [];
//        if (!session('isSuper')) {
//            $patrid = $this->getExecutorPatrid(session('username'), $type);
//        }
//        if (!$patrid) {
//            $patrid = [0];
//        }
//        $where['patrid'] = array('in', $patrid);
        $where['hospital_id'] = session('current_hospitalid');
        return $this->DB_get_all('patrol_plans', 'patrol_name', $where, '', 'patrid desc', '');
    }

    /*
     * 获取巡查人员对于的计划ID条件
     * @params1 $executor string 执行人
     * @params2 $type int 是否不需验证执行人
     * return string
     */
    public function getExecutorPatrid($executor, $type = 0)
    {
        $patridWhere = [0];
        if ($executor) {
            $where['B.approve_status'] = ['in', [-1, 1]];
            if (!$type) {
                $where['A.executor'] = $executor;
            }
            $join = "LEFT JOIN sb_patrol_plan AS B ON A.patrid = B.patrid";
            $cyc  = $this->DB_get_all_join('patrol_plan_cycle', 'A', 'A.patrid', $join, $where, 'A.patrid', '', '');
            if ($cyc) {
                foreach ($cyc as $cycValue) {
                    $patridWhere[] = $cycValue['patrid'];
                }
            } else {
                $patridWhere = 0;
            }
        }
        return $patridWhere;
    }

    /*
     * 通过执行人获取已发布的计划ID条件
     * @params1 $executor string 执行人
     * @params2 $alias string 条件前缀
     * return string
     */
    public function adoptExecutorPatrid($executor, $alias)
    {
        $patridWhere = '';
        $where       = "executor = '" . $executor . "'";
        $cyc         = $this->DB_get_all('patrol_plan_cycle', 'patrid', $where, 'patrid');
        if ($cyc) {
            foreach ($cyc as $cycValue) {
                $patridWhere .= "," . $cycValue['patrid'];
            }
            $patridWhere = trim($patridWhere, ',');
            if ($alias) {
                $patridWhere = " AND $alias.patrid IN($patridWhere)";
            } else {
                $patridWhere = " AND patrid IN($patridWhere)";
            }
        } else {
            $patridWhere = ' AND 1=0';
        }
        return $patridWhere;
    }


    /*
     * 通过状态获取对应的周期ID条件
     * @params1 $whereStatus int 周期计划状态
     * return string
     */
    public function getStatusCycnum($whereStatus)
    {
        $fileds = 'group_concat(executor) as executor,group_concat(status) as status,cyclenum,period,patrol_level';
        $data   = $this->DB_get_all('patrol_plan_cycle', $fileds, '', 'cyclenum');
        $where  = '';
        foreach ($data as &$value) {
            $peoplenum = substr_count($value['executor'], ',') + 1;
            $status    = $value['status'];
            switch ($whereStatus) {
                case C('PLAN_CYCLE_STANDBY'):
                    if (substr_count($status, C('PLAN_CYCLE_STANDBY')) == $peoplenum) {
                        $where .= ",'" . $value['cyclenum'] . "'";
                    }
                    break;
                case C('PLAN_CYCLE_EXECUTION'):
                    if (substr_count($status, C('PLAN_CYCLE_EXECUTION')) >= 1 or (substr_count($status,
                                C('PLAN_CYCLE_COMPLETE')) >= 1 && substr_count($status,
                                C('PLAN_CYCLE_COMPLETE')) != $peoplenum)) {
                        $where .= ",'" . $value['cyclenum'] . "'";
                    }
                    break;
                case C('PLAN_CYCLE_COMPLETE'):
                    if (substr_count($status, C('PLAN_CYCLE_COMPLETE')) == $peoplenum) {
                        $where .= ",'" . $value['cyclenum'] . "'";
                    }
                    break;
                case C('PLAN_CYCLE_CHECK') :
                    if (substr_count($status, C('PLAN_CYCLE_CHECK')) == $peoplenum) {
                        $where .= ",'" . $value['cyclenum'] . "'";
                    }
                    break;
            }

        }
        if ($where) {
            $where = trim($where, ',');
            return " AND A.cyclenum IN($where)";
        } else {
            return ' AND 1=0';
        }
    }

    /**
     * 格式化巡查类型
     *
     * @param int $level 级别
     *
     * @return string
     * */
    public function levelFormat($level)
    {
        switch ($level) {
            case C('PATROL_LEVEL_RC'):
                $name = C('PATROL_LEVEL_NAME_RC');
                break;
            case C('PATROL_LEVEL_XC'):
                $name = C('PATROL_LEVEL_NAME_XC');
                break;
            case C('PATROL_LEVEL_PM'):
                $name = C('PATROL_LEVEL_NAME_PM');
                break;
            default :
                $name = '异常参数';
        }
        return $name;
    }


    //格式化短信内容
    public static function formatSmsContent($content, $data)
    {
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{applicant}", $data['applicant'], $content);
        $content = str_replace("{department}", $data['department'], $content);
        $content = str_replace("{patrol_name}", $data['patrol_name'], $content);
        $content = str_replace("{patrol_num}", $data['patrol_num'], $content);
        $content = str_replace("{cyclenum}", $data['cyclenum'], $content);
        $content = str_replace("{period}", $data['period'], $content);
        $content = str_replace("{patrol_level}", $data['patrol_level_name'], $content);
        $content = str_replace("{examine_status}", $data['examine_status'], $content);
        $content = str_replace("{startdate}", $data['startdate'], $content);
        return $content;
    }

    //格式化保养级别
    public static function formatpatrolLevel($patrol_level)
    {
        switch ($patrol_level) {
            case C('PATROL_LEVEL_RC'):
                $levelName = C('PATROL_LEVEL_NAME_RC');
                break;
            case C('PATROL_LEVEL_XC'):
                $levelName = C('PATROL_LEVEL_NAME_XC');
                break;
            case C('PATROL_LEVEL_PM'):
                $levelName = C('PATROL_LEVEL_NAME_PM');
                break;
            default:
                $levelName = '-';
                break;
        }
        return $levelName;
    }

    /**新增模板操作
     *
     * @return mixed
     */
    public function addTemplateData()
    {
        $addData = [];
        //检查模板名称是否为空 以及 是否存在
        $name = trim(I('post.name'));
        $this->checkstatus(judgeEmpty($name), '模板名称不能为空');
        $existName = $this->DB_get_one('patrol_template', 'tpid', ['name' => $name]);
        if ($existName['tpid']) {
            die(json_encode(['status' => -1, 'msg' => '已存在该名称的模板']));
        } else {
            $addData['name'] = $name;
        }
        //判断是否勾选了保养明细项
        $this->checkstatus(judgeEmpty(I('post.num')), '请至少勾选一个保养明细');
        $addData['points_num'] = json_encode(explode(',', I('post.num')));
        $addData['remark']     = I('post.remark');
        $addData['add_user']   = session('username');
        $addData['add_time']   = getHandleDate(time());
        $result                = $this->insertData('patrol_template', $addData);
        $sql                   = M()->getLastSql();
        //日志行为记录文字
        $log['name'] = $name;
        $text        = getLogText('addTemplateLogText', $log);
        $this->addLog('patrol_template', $sql, $text, $result, 'addTemplate');
        return $result;
    }

    /**修改模板操作
     *
     * @return mixed
     */
    public function editTemplateData()
    {
        $editData = [];
        $tpid     = I('post.tpid');
        //检查模板名称是否为空 以及 是否存在
        $name = trim(I('post.name'));
        $this->checkstatus(judgeEmpty($name), '模板名称不能为空');
        $existName = $this->DB_get_one('patrol_template', 'tpid', ['name' => $name, 'tpid' => ['neq', $tpid]]);
        if ($existName['tpid']) {
            die(json_encode(['status' => -1, 'msg' => '已存在该名称的模板']));
        } else {
            $editData['name'] = $name;
        }
        //判断是否勾选了保养明细项
        $this->checkstatus(judgeEmpty(I('post.num')), '请至少勾选一个保养明细');
        $editData['points_num'] = json_encode(explode(',', I('post.num')));
        $editData['remark']     = I('post.remark');
        $editData['edit_user']  = session('username');
        $editData['edit_time']  = getHandleDate(time());
        $result                 = $this->updateData('patrol_template', $editData, ['tpid' => $tpid]);
        $sql                    = M()->getLastSql();
        //日志行为记录文字
        $log['name'] = $name;
        $text        = getLogText('editTemplateLogText', $log);
        $this->addLog('patrol_template', $sql, $text, $tpid, 'editTemplate');
        return $result;
    }

    public function batchSettingTemplateData()
    {
        $result       = [];
        $assidArr     = explode(',', trim(I('post.assid'), ','));
        $assnumArr    = explode(',', trim(I('post.assnum'), ','));
        $tpid         = I('post.tpid');
        $default_tpid = I('post.default_tpid');
        //判断模板是否为空
        if (!$tpid) {
            $result = ['status' => -1, 'msg' => '请至少选择一个模板'];
            return $result;
        }
        $batchadd = [];
        $i        = 0;
        foreach ($assidArr as $v) {
            $oldInfo = $this->DB_get_one('patrol_assets_template', 'tpid', ['assid' => $v]);
            if ($oldInfo['tpid']) {
                $update['tpid']         = implode($tpid, ',');
                $update['default_tpid'] = $default_tpid;
                $this->updateData('patrol_assets_template', $update, ['assid' => $v]);
            } else {
                foreach ($assnumArr as $v1) {
                    $batchadd[$i]['assnum'] = $v1;
                }
                $batchadd[$i]['assid']        = $v;
                $batchadd[$i]['tpid']         = implode($tpid, ',');
                $batchadd[$i]['default_tpid'] = $default_tpid;
                $i++;
            }
        }
        if ($batchadd) {
            $result = $this->insertDataALL('patrol_assets_template', $batchadd);
        } else {
            $result = ['status' => -1, 'msg' => '设定模板失败'];
            return $result;
        }
        //日志行为记录文字
        $log['assnum'] = trim(I('post.assnum'));
        $text          = getLogText('settingTemplateLogText', $log);
        $this->addLog('patrol_assets_template', '', $text, '', '');
        return $result;
    }

    /**
     * Notes: 获取巡查计划审核列表
     */
    public function get_approve_lists()
    {
        //排序加分页加搜索同意
        $limit                   = I('POST.limit') ? I('POST.limit') : 10;
        $page                    = I('POST.page') ? I('POST.page') : 1;
        $offset                  = ($page - 1) * $limit;
        $order                   = I('POST.order') ? I('POST.order') : 'ASC';
        $sort                    = I('POST.sort') ? I('POST.sort') : 'patrol_status';
        $hospital_id             = session('current_hospitalid');
        $patrol_level            = I('POST.patrol_level');
        $where['approve_status'] = ['neq', -1];
        $where['is_delete']      = C('NO_STATUS');
        $where['hospital_id']    = $hospital_id;

        if (!session('isSuper')) {
            $where['_string'] = "add_user='" . session('username') . "'or (all_approver like '%/" . session('username') . "/%')";
        }
        //保养级别搜索
        if ($patrol_level) {
            $where['patrol_level'] = $patrol_level;
        }
        //获取总条数
        $total = $this->DB_get_count('patrol_plans', $where);
        //获取数据
        $patrol = $this->DB_get_all('patrol_plans', '*', $where, '', $sort . ' ' . $order . ',patrid desc',
            $offset . "," . $limit);
        if (!$patrol) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //查询系统超级用户
        $super = $this->DB_get_all('user', 'username', ['is_super' => 1]);
        //查询计划执行人
        $ids_arr = [];
        foreach ($patrol as $k => $v) {
            $ids_arr[]         = $v['patrid'];
            $v['all_approver'] = str_replace('/', '', $v['all_approver']);
            foreach ($super as $user) {
                $search                     = ',' . $user['username'];
                $patrol[$k]['all_approver'] = str_replace($search, '', $v['all_approver']);
            }
        }

        //查询审批记录
        $apps = $this->DB_get_all('approve',
            'patrid,group_concat(is_adopt ORDER BY apprid ASC) as is_adopt,group_concat(approve_time ORDER BY apprid ASC) as approve_time',
            ['is_delete' => 0, 'patrid' => ['in', $ids_arr]], 'patrid');
        //处理数据
        //icon
        $yuan_icon = '&#xe63f;';//圆形
        $dui_icon  = '&#xe605;';//通过
        $cuo_icon  = '&#x1006;';//不通过

        //颜色
        $gray_color    = 'unexecutedColor';//灰色
        $pass_color    = 'executeddColor';//绿色
        $no_pass_color = 'endColor';//红色

        foreach ($patrol as $k => $v) {
            $all_approver = explode(',', $v['all_approver']);
            $html         = '';
            $html         = '<ul class="timeLineList">';
            foreach ($all_approver as $username) {
                $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $gray_color . '">' . $yuan_icon . '</i><div class="timeLine ' . $gray_color . 'Bg"></div><span class="timeLineTitle ' . $gray_color . '">' . $username . '</span><span class="timeLineDate ' . $gray_color . '">-</span></div></li>';
            }
            $html                          .= '</ul>';
            $patrol[$k]['app_user_status'] = $html;
            //审批状态
            foreach ($apps as $kp => $vp) {
                if ($vp['patrid'] == $v['patrid']) {
                    $is_adopt                      = explode(',', $vp['is_adopt']);
                    $approve_time                  = explode(',', $vp['approve_time']);
                    $patrol[$k]['app_user_status'] = '';
                    $html                          = '';
                    $html                          = '<ul class="timeLineList">';
                    foreach ($all_approver as $uk => $username) {
                        if ($is_adopt[$uk]) {
                            if ($is_adopt[$uk] == 1) {
                                $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $pass_color . '">' . $dui_icon . '</i><div class="timeLine ' . $pass_color . 'Bg"></div><span class="timeLineTitle ' . $pass_color . '">' . $username . '</span><span class="timeLineDate ' . $pass_color . '">' . date('Y-m-d H:i',
                                        $approve_time[$uk]) . '</span></div></li>';
                            } else {
                                $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $no_pass_color . '">' . $cuo_icon . '</i><div class="timeLine ' . $no_pass_color . 'Bg"></div><span class="timeLineTitle ' . $no_pass_color . '">' . $username . '</span><span class="timeLineDate ' . $no_pass_color . '">' . date('Y-m-d H:i',
                                        $approve_time[$uk]) . '</span></div></li>';
                            }
                        } else {
                            $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $gray_color . '">' . $yuan_icon . '</i><div class="timeLine ' . $gray_color . 'Bg"></div><span class="timeLineTitle ' . $gray_color . '">' . $username . '</span><span class="timeLineDate ' . $gray_color . '">-</span></div></li>';
                        }
                    }
                    $html                          .= '</ul>';
                    $patrol[$k]['app_user_status'] = $html;
                }
            }
        }

        foreach ($patrol as $k => $v) {
            $patrol[$k]['patrol_date'] = $v['patrol_start_date'] . ' 至 ' . $v['patrol_end_date'];
            $html                      = '<div class="layui-btn-group">';
            if ($v['patrol_status'] == 1) {
                $html .= '<button type="button" class="layui-btn layui-btn-xs" lay-event="showPlans" data-url="/index.php/Admin/Patrol/Patrol/showPlans">待审核</button>';
            } else {
                $html .= '<button type="button" class="layui-btn layui-btn-xs layui-btn-normal" lay-event="showPlans" data-url="/index.php/Admin/Patrol/Patrol/showPlans">查看</button>';
            }
            $html                    .= '</div>';
            $patrol[$k]['operation'] = $html;
            switch ($v['patrol_level']) {
                case C('PATROL_LEVEL_DC'):
                    $patrol[$k]['patrol_level_name'] = C('PATROL_LEVEL_NAME_DC');
                    break;
                case C('PATROL_LEVEL_RC'):
                    $patrol[$k]['patrol_level_name'] = C('PATROL_LEVEL_NAME_RC');
                    break;
                case C('PATROL_LEVEL_PM'):
                    $patrol[$k]['patrol_level_name'] = C('PATROL_LEVEL_NAME_PM');
                    break;
            }
            $patrol[$k]['assets_nums'] = $this->DB_get_count('patrol_plans_assets', ['patrid' => $v['patrid']]);
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["rows"]   = $patrol;
        $result["code"]   = 200;
        return $result;
    }

    /*
    删除巡查计划
     */
    public function del_patrol()
    {
        $patrid      = I('POST.patrid');
        $patrol_info = $this->DB_get_one('patrol_plan', '', ['patrid' => $patrid]);
        if (!$patrol_info) {
            return ['status' => -1, 'msg' => '查找不到巡查计划信息', 'data' => $patrid];
        } else {
            if ($patrol_info['add_user'] != session('username')) {
                return ['status' => -1, 'msg' => '该计划不是你制定的，你没有权限删除'];
            }
            $res = $this->updateData('patrol_plan', ['is_delete' => 1], ['patrid' => $patrid]);
            //应该在这里删除条件设备包
            if ($res) {
                $data = $this->DB_get_one('patrol_plan', '', ['packid' => $patrol_info['packid'], 'is_delete' => '0']);
                if (!$data) {
                    $this->updateData('assets_info', ['patrol_in_plan' => 0],
                        ['assnum' => ['IN', $patrol_info['patrol_assnums']]]);
                }
                return ['status' => 1, 'msg' => '删除成功'];
            }
        }
    }

    /**
     * Notes: 保存审核结果
     */
    public function save_approve()
    {
        $pid         = I('POST.pid');
        $patrol_info = $this->DB_get_one('patrol_plans', '', ['patrid' => $pid]);
        if (!$patrol_info) {
            return ['status' => -1, 'msg' => '查找不到巡查计划信息'];
        } else {
            if ($patrol_info['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                return ['status' => -1, 'msg' => '审批已通过，请勿重复提交！'];
            } elseif ($patrol_info['approve_status'] == C('STATUS_APPROE_FAIL')) {
                return ['status' => -1, 'msg' => '审批已否决，请勿重复提交！'];
            }
        }
        switch ($patrol_info['patrol_level']) {
            case C('PATROL_LEVEL_PM')://3预防性维护
                $patrol_info['patrol_level_name'] = C('PATROL_LEVEL_NAME_PM');
                break;
            case C('PATROL_LEVEL_RC')://2巡查保养
                $patrol_info['patrol_level_name'] = C('PATROL_LEVEL_NAME_RC');
                break;
            case C('PATROL_LEVEL_DC')://1日常保养
                $patrol_info['patrol_level_name'] = C('PATROL_LEVEL_NAME_DC');
                break;
        }
        $data['patrid']        = $patrol_info['patrid'];
        $data['is_adopt']      = I('POST.is_adopt');
        $data['remark']        = trim(I('POST.remark'));
        $data['proposer']      = $patrol_info['add_user'];
        $data['proposer_time'] = strtotime($patrol_info['add_time']);
        $data['approver']      = session('username');
        $data['approve_time']  = time();
        $data['approve_class'] = 'patrol';
        $data['process_node']  = C('PATROL_APPROVE');
        //判断是否是当前审批人
        if ($patrol_info['current_approver']) {
            $current_approver     = explode(',', $patrol_info['current_approver']);
            $current_approver_arr = [];
            foreach ($current_approver as &$current_approver_value) {
                $current_approver_arr[$current_approver_value] = true;
            }
            if ($current_approver_arr[session('username')]) {
                $processWhere['patrid']     = ['EQ', $patrol_info['patrid']];
                $processWhere['is_delete']  = ['NEQ', C('YES_STATUS')];
                $process                    = $this->DB_get_count('approve', $processWhere);
                $level                      = $process + 1;
                $data['process_node_level'] = $level;
                $res                        = $this->addApprove($patrol_info, $data, 0, $patrol_info['hospital_id'], 0,
                    C('PATROL_APPROVE'), 'patrol_plans', 'patrid');
                if ($res['status'] == 1) {
                    $is_adopt = I('POST.is_adopt') == 1 ? '已通过' : '已驳回';
                    $text     = getLogText('approvePatrolLogText',
                        ['patrolnum' => $patrol_info['patrol_name'], 'is_adopt' => $is_adopt]);
                    $this->addLog('patrol_plan', $res['lastSql'], $text, $patrol_info['patrid'], '');
                }
                //==========================================短信 START==========================================//
                $isOpenApprove = $this->checkApproveIsOpen(C('PATROL_APPROVE'), session('current_hospitalid'));
                $ToolMod       = new ToolController();
                $settingData   = $this->checkSmsIsOpen($this->Controller);
                $moduleModel   = new ModuleModel();
                $wx_status     = $moduleModel->decide_wx_login();
                if (I('POST.is_adopt') == 2) {
                    $user_data = $this->DB_get_one('user', 'telephone,openid',
                        ['username' => $patrol_info['add_user']]);
                    if ($settingData['borrowrApproveOver']['status'] == C('OPEN_STATUS') && $isOpenApprove) {
                        //拒绝短信
                        $sms_data['patrolname']     = $patrol_info['patrol_name'];
                        $sms_data['examine_status'] = '不通过。您可以修订后重新提交申请！';
                        $sms                        = $this->formatSmsContent($settingData['borrowrApproveOver']['content'],
                            $sms_data);
                        $Phone                      = $user_data['telephone'];
                        $ToolMod->sendingSMS($Phone, $sms, $this->Controller, $res);
                    }
                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**计划名称：**' . $patrol_info['patrol_name'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**审核人：**' . session('username');
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**审核意见：**不通过';
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**审核备注：**' . $data['remark'];
                        $feishu_fields[]       = $fd;

                        $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag']         = 'div';
                        $card_data['elements'][0]['fields']      = $feishu_fields;
                        $card_data['header']['template']         = 'red';
                        $card_data['header']['title']['content'] = '巡查保养计划审核完成通知';
                        $card_data['header']['title']['tag']     = 'plain_text';

                        $this->send_feishu_card_msg($user_data['openid'], $card_data);
                        //==========================================飞书 END==========================================
                    } else {
                        if ($user_data['openid'] && $wx_status) {
                            Weixin::instance()->sendMessage($user_data['openid'], '工单审核结果通知', [
                                'const1'            => '巡查保养计划',// 工单类型
                                'thing3'            => $patrol_info['patrol_name'],// 工单名称
                                'character_string2' => date('YmdHis', strtotime($patrol_info['add_time'])),// 工单号
                                'const4'            => '未通过',// 审核结果
                                'time6'             => getHandleMinute($data['approve_time']),// 审核时间
                            ]);
                        }
                    }
                } else {
                    if ($isOpenApprove) {
                        $approve_process_user = $this->get_approve_process('123', C('PATROL_APPROVE'),
                            session('current_hospitalid'));
                        $approve              = $this->check_approve_process('0', $approve_process_user, $level + 1);
                        $all_level            = explode(",", $approve['all_approver']);
                    }
                    $user_data = $this->DB_get_one('user', 'username,telephone,openid',
                        ['username' => $patrol_info['add_user']]);
                    if ($settingData['doApprove']['status'] == C('OPEN_STATUS') && $approve['this_current_approver'] && count($all_level) / 2 != $level) {
                        //通过审核但不是最后一次审核短信
                        $user_data              = $this->DB_get_one('user', 'telephone,openid',
                            ['username' => $approve['this_current_approver']]);
                        $sms_data['patrolname'] = $patrol_info['patrol_name'];
                        $sms_data['applicant']  = $patrol_info['add_user'];
                        $sms                    = $this->formatSmsContent($settingData['doApprove']['content'],
                            $sms_data);
                        $Phone                  = $user_data['telephone'];
                        $ToolMod->sendingSMS($Phone, $sms, $this->Controller, $res);
                    }
                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**计划名称：**' . $patrol_info['patrol_name'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**计划备注：**' . $patrol_info['remark'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '请尽快完成审核';
                        $feishu_fields[]       = $fd;

                        $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag']         = 'div';
                        $card_data['elements'][0]['fields']      = $feishu_fields;
                        $card_data['header']['template']         = 'blue';
                        $card_data['header']['title']['content'] = '巡查保养计划审核提醒';
                        $card_data['header']['title']['tag']     = 'plain_text';

                        $user_data = $this->DB_get_one('user', 'telephone,openid',
                            ['username' => $approve['this_current_approver']]);
                        $this->send_feishu_card_msg($user_data['openid'], $card_data);
                        //==========================================飞书 END==========================================
                    } else if ($wx_status) {
                        if (count($all_level) / 2 != $level) {
                            // 非最后一道审批
                            $currentApprover  = $this->DB_get_one('user', 'telephone,openid',
                                ['username' => $approve['this_current_approver']]);

                            if ($currentApprover['openid']) {
                                Weixin::instance()->sendMessage($currentApprover['openid'], '工单待审核提醒', [
                                    'const13'           => '设备巡查保养计划',// 工单类型
                                    'thing15'           => $patrol_info['patrol_name'],// 工单名称
                                    'character_string7' => date('YmdHis', strtotime($patrol_info['add_time'])),// 工单号
                                    'thing16'           => $patrol_info['add_user'],// 发起人
                                    'time3'             => getHandleMinute($data['approve_time']),// 创建时间
                                ]);
                            }
                        }
                    }
                    $user_data = $this->DB_get_one('user', 'telephone,openid',
                        ['username' => $patrol_info['add_user']]);
                    if ($settingData['doPatrolTask']['status'] == C('OPEN_STATUS') && count($all_level) / 2 == $level) {
                        //通过最后一次审核 短信
                        $sms_data['patrolname']     = $patrol_info['patrol_name'];
                        $sms_data['examine_status'] = '已通过。请尽快发布计划！';
                        $sms                        = $this->formatSmsContent($settingData['borrowrApproveOver']['content'],
                            $sms_data);
                        $Phone                      = $user_data['telephone'];
                        $ToolMod->sendingSMS($Phone, $sms, $this->Controller, $res);
                    }
                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**计划名称：**' . $patrol_info['patrol_name'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**审批人：**' . session('username');
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**审批意见：**通过';
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '**审批备注：**' . $data['remark'];
                        $feishu_fields[]       = $fd;

                        $fd['is_short']        = false;//是否并排布局
                        $fd['text']['tag']     = 'lark_md';
                        $fd['text']['content'] = '审核已通过，请尽快发布计划';
                        $feishu_fields[]       = $fd;

                        $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag']         = 'div';
                        $card_data['elements'][0]['fields']      = $feishu_fields;
                        $card_data['header']['template']         = 'red';
                        $card_data['header']['title']['content'] = '巡查保养计划审批完成通知';
                        $card_data['header']['title']['tag']     = 'plain_text';

                        $this->send_feishu_card_msg($user_data['openid'], $card_data);
                        //==========================================飞书 END==========================================
                    } else {
                        if ($user_data['openid'] && $wx_status && count($all_level) / 2 == $level) {
                            //通过最后一次审核 微信
                            Weixin::instance()->sendMessage($user_data['openid'], '工单审核结果通知', [
                                'const1'            => '巡查保养计划',// 工单类型
                                'thing3'            => $patrol_info['patrol_name'],// 工单名称
                                'character_string2' => date('YmdHis', strtotime($patrol_info['add_time'])),// 工单号
                                'const4'            => '已通过',// 审核结果
                                'time6'             => getHandleMinute($data['approve_time']),// 审核时间
                            ]);
                        }
                    }
                }
                return $res;
            } else {
                return ['status' => -1, 'msg' => '请等待审批！'];
            }
        } else {
            return ['status' => -1, 'msg' => '审核已结束！'];
        }
    }

    /**
     * Notes: 获取计划信息
     *
     * @param $patrid int 计划ID
     */
    public function get_plan_info($patrid)
    {
        $fields                    = "*";
        $where['is_delete']        = C('NO_STATUS');
        $where['patrid']           = $patrid;
        $where['hospital_id']      = session('current_hospitalid');
        $patrol_info               = $this->DB_get_one('patrol_plans', $fields, $where);
        $patrol_info['cycle_name'] = $patrol_info['is_cycle'] ? '是' : '否';
        if ($patrol_info['is_cycle']) {
            $patrol_info['total_progress'] = $patrol_info['current_cycle'] . ' / ' . $patrol_info['total_cycle'];
            switch ($patrol_info['cycle_unit']) {
                case 'day':
                    $patrol_info['cycle_setting_name'] = '每' . $patrol_info['cycle_setting'] . '天';
                    break;
                case 'week':
                    $patrol_info['cycle_setting_name'] = '每' . $patrol_info['cycle_setting'] . '周';
                    break;
                case 'month':
                    $patrol_info['cycle_setting_name'] = '每' . $patrol_info['cycle_setting'] . '月';
                    break;
                case 'quarter':
                    $patrol_info['cycle_setting_name'] = '每' . $patrol_info['cycle_setting'] . '季度';
                    break;
                case 'year':
                    $patrol_info['cycle_setting_name'] = '每' . $patrol_info['cycle_setting'] . '年';
                    break;
            }
        } else {
            $patrol_info['cycle_setting_name'] = '无';
        }
        switch ($patrol_info['patrol_status']) {
            case '1':
                $patrol_info['patrol_status_name'] = '待审批';
                break;
            case '2':
                $patrol_info['patrol_status_name'] = '待发布';
                break;
            case '3':
                $patrol_info['patrol_status_name'] = '待实施';
                break;
            case '4':
                $patrol_info['patrol_status_name'] = '已结束';
                break;
        }
        return $patrol_info;
    }

    /**
     * Notes: 获取计划设备列表
     *
     * @param $patrol_info array 计划信息集合
     */
    public function get_plan_assets($patrol_info)
    {
        //查询计划设备
        $patrol_assets = M("patrol_plans_assets");
        $assnum        = $patrol_assets->where(['patrid' => $patrol_info['patrid']])->getField('assnum', true);
        switch ($patrol_info['patrol_level']) {
            case C('PATROL_LEVEL_PM'):
                //上一次巡查日期
                $fields = 'assid,assets,assnum,assorignum,model,departid,pre_patrol_date as pre_date';
                break;
            default:
                //上一次保养日期
                $fields = 'assid,assets,assnum,assorignum,model,departid,pre_maintain_date as pre_date';
                break;
        }
        $asWhere['assnum'] = ['in', $assnum];
        $assInfo           = $this->DB_get_all('assets_info', $fields, $asWhere);
        //获取计划设备模板名称及明细项数目
        $assnum_tpid = $patrol_assets->where(['patrid' => $patrol_info['patrid']])->getField('assnum,assnum_tpid');
        $temModel    = M('patrol_template');
        $temps       = $temModel->where(['is_delete' => 0])->getField('tpid,name,points_num');
        $departname  = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assInfo as $k => $v) {
            $assInfo[$k]['patrol_level'] = $patrol_info['patrol_level'];
            switch ($patrol_info['plan_level']) {
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
//            $assInfo[$k]['cycid'] = $assnum_cycid[$v['assnum']];
//            $assInfo[$k]['executor'] = $assnum_name[$v['assnum']];
//            $assInfo[$k]['sign_info'] = $assnum_sign_info[$v['assnum']];//新增签到信息
            $assInfo[$k]['department']    = $departname[$v['departid']]['department'];
            $assInfo[$k]['details_num']   = count(json_decode($temps[$assnum_tpid[$v['assnum']]]['points_num'], true));
            $assInfo[$k]['template_name'] = '<span style="cursor: pointer;color: #01AAED;" data-id="' . $assnum_tpid[$v['assnum']] . '" class="show_template">' . $temps[$assnum_tpid[$v['assnum']]]['name'] . '</span>';
        }
        return $assInfo;
    }

    /**
     * 获取特定周期计划设备列表
     *
     * @param $cycid
     */
    public function get_cycle_plan_assets($cycid)
    {
        //查询计划设备
        $cycleInfo = $this->DB_get_one('patrol_plans_cycle', 'patrid,plan_assnum', ['cycid' => $cycid]);
        $assnum    = json_decode($cycleInfo['plan_assnum']);
        switch ($cycleInfo['patrol_level']) {
            case C('PATROL_LEVEL_PM'):
                //上一次巡查日期
                $fields = 'assid,assets,assnum,assorignum,model,departid,pre_patrol_date as pre_date';
                break;
            default:
                //上一次保养日期
                $fields = 'assid,assets,assnum,assorignum,model,departid,pre_maintain_date as pre_date';
                break;
        }
        $asWhere['assnum'] = ['in', $assnum];
        $assInfo           = $this->DB_get_all('assets_info', $fields, $asWhere);
        //获取计划设备模板名称及明细项数目
        $patrol_assets = M("patrol_plans_assets");
        $assnum_tpid   = $patrol_assets->where(['patrid' => $cycleInfo['patrid']])->getField('assnum,assnum_tpid');
        $temModel      = M('patrol_template');
        $temps         = $temModel->where(['is_delete' => 0])->getField('tpid,name,points_num');
        $departname    = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assInfo as $k => $v) {
            $assInfo[$k]['patrol_level'] = $cycleInfo['patrol_level'];
            switch ($cycleInfo['plan_level']) {
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
//            $assInfo[$k]['cycid'] = $assnum_cycid[$v['assnum']];
//            $assInfo[$k]['executor'] = $assnum_name[$v['assnum']];
//            $assInfo[$k]['sign_info'] = $assnum_sign_info[$v['assnum']];//新增签到信息
            $assInfo[$k]['department']    = $departname[$v['departid']]['department'];
            $assInfo[$k]['details_num']   = count(json_decode($temps[$assnum_tpid[$v['assnum']]]['points_num'], true));
            $assInfo[$k]['template_name'] = '<span style="cursor: pointer;color: #01AAED;" data-id="' . $assnum_tpid[$v['assnum']] . '" class="show_template">' . $temps[$assnum_tpid[$v['assnum']]]['name'] . '</span>';
        }
        return $assInfo;
    }

    /**
     * 获取计划的细分周期计划
     *
     * @param $patrid
     */
    public function get_plan_cycle($patrid)
    {
        $where['patrid'] = $patrid;
        $patrolInfo      = $this->DB_get_one('patrol_plans', 'is_cycle,total_cycle,patrol_name', $where);
        $tasks           = $this->DB_get_all('patrol_plans_cycle',
            'cycid,patrid,period,patrol_num,assets_nums,implement_sum,abnormal_sum,cycle_start_date,cycle_end_date,cycle_status,sign_info',
            $where, '', 'period desc');
        foreach ($tasks as $key => $value) {
            $tasks[$key]['patrol_name']   = $patrolInfo['patrol_name'];
            $tasks[$key]['cycle_name']    = $patrolInfo['is_cycle'] ? '是' : '否';
            $tasks[$key]['current_total'] = $value['period'] . ' / ' . $patrolInfo['total_cycle'];
            $tasks[$key]['patrol_date']   = $value['cycle_start_date'] . ' 至 ' . $value['cycle_end_date'];
            $not_patrol_nums              = 0;
            if ($value['not_operation_assnum']) {
                $not_operation_assnum = json_decode($value['not_operation_assnum']);
                $not_patrol_nums      = count($not_operation_assnum);
            }
            $tasks[$key]['abnormal_sum'] = $value['abnormal_sum'] > 0 ? '<span style="color: red;">' . $value['abnormal_sum'] . '</span>' : $value['abnormal_sum'];
            $tasks[$key]['numstatus']    = $value['implement_sum'] . ' / ' . ($value['implement_sum'] - $not_patrol_nums) . ' / ' . $value['assets_nums'];
            $linkname                    = '';
            $color                       = '';
            $class                       = 'showDetail';
            $url                         = C('ADMIN_NAME') . '/Patrol/showPlans?action=detail&cycid=' . $value['cycid'];
            switch ($value['cycle_status']) {
                case '0':
                    $linkname = '待执行';
                    break;
                case '1':
                    $linkname = '执行中';
                    break;
                case '2':
                    $color    = ' layui-btn-normal';
                    $linkname = '按期完成';
                    break;
                case '3':
                    $color    = ' layui-btn-warm';
                    $linkname = '逾期完成';
                    break;
                case '4':
                    $color    = ' layui-btn-danger';
                    $linkname = '逾期未完成';
                    break;
            }
            $tasks[$key]['operation'] = $this->returnListLink($linkname, $url, $class, C('BTN_CURRENCY') . $color);
        }
        return $tasks;
    }


    public function get_cycle_data($cycid)
    {
        $cycleInfo         = $this->DB_get_one('patrol_plans_cycle', '*', ['cycid' => $cycid]);
        $planInfo          = $this->DB_get_one('patrol_plans', 'patrol_level', ['patrid' => $cycleInfo['patrid']]);
        $patrol_level_name = '';
        switch ($planInfo['patrol_level']) {
            case 1:
                $patrol_level_name = '日常保养';
                break;
            case 2:
                $patrol_level_name = '巡查保养';
                break;
            case 3:
                $patrol_level_name = '预防性维护';
                break;
        }
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
        $assInfo = $this->DB_get_all('assets_info', 'assid,assets,assnum,assorignum,model,departid', $where);
        //获取模板信息
        $patrol_assets = M("patrol_plans_assets");
        $assnum_tpid   = $patrol_assets->where(['patrid' => $cycleInfo['patrid']])->getField('assnum,assnum_tpid');
        $temModel      = M('patrol_template');
        $temps         = $temModel->where(['is_delete' => 0])->getField('tpid,name,points_num');

        //查询当前用户是否有执行权限
        $doTask   = get_menu($this->MODULE, $this->Controller, 'doTask');
        $is_super = session('isSuper');
        //判断是否开启签到
        $patrol_wx_set_situation = $this->DB_get_one('base_setting', '*',
            ['module' => 'patrol', 'set_item' => 'patrol_wx_set_situation']);
        $wx_sign_in              = $patrol_wx_set_situation['value'];

        $sign_assnums = json_decode($cycleInfo['sign_info'], true);
        foreach ($assInfo as $k => $v) {
            switch ($v['status']) {
                case C('ASSETS_STATUS_USE'):
                    $assInfo[$k]['status_name'] = '<span style="color:#5FB878">' . C('ASSETS_STATUS_USE_NAME') . '</span>';
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    //设备维修中
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">' . C('ASSETS_STATUS_REPAIR_NAME') . '</span>';
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    //设备已报废
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">' . C('ASSETS_STATUS_SCRAP_NAME') . '</span>';
                    break;
                case C('ASSETS_STATUS_OUTSIDE'):
                    //设备已外调
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">' . C('ASSETS_STATUS_OUTSIDE_NAME') . '</span>';
                    break;
                case C('ASSETS_STATUS_OUTSIDE_ON'):
                    //设备外调中
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">' . C('ASSETS_STATUS_OUTSIDE_ON_NAME') . '</span>';
                    break;
                case C('ASSETS_STATUS_SCRAP_ON'):
                    //设备报废中
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">' . C('ASSETS_STATUS_SCRAP_ON_NAME') . '</span>';
                    break;
                case  C('ASSETS_STATUS_TRANSFER_ON'):
                    //设备转科中
                    $assInfo[$k]['status_name'] = '<span style="color:#FF5722">' . C('ASSETS_STATUS_TRANSFER_ON_NAME') . '</span>';
                    break;
                case '':
                    break;
            }
            $assInfo[$k]['is_complete']       = 0;
            $assInfo[$k]['cycid']             = $cycid;
            $assInfo[$k]['patrol_level_name'] = $patrol_level_name;
            $assInfo[$k]['department']        = $departname[$v['departid']]['department'];
            $assInfo[$k]['temp_name']         = $temps[$assnum_tpid[$v['assnum']]]['name'];
            $assInfo[$k]['points_num']        = count(json_decode($temps[$assnum_tpid[$v['assnum']]]['points_num']));
            $assInfo[$k]['need_sign']         = false;
            $assInfo[$k]['doTask']            = false;
            //查询设备是否已巡查
            $execute                           = $this->DB_get_one('patrol_execute', '*',
                ['cycid' => $cycid, 'assetnum' => $v['assnum'], 'status' => 2]);
            $assInfo[$k]['operation']          = '';
            $assInfo[$k]['assets_status_name'] = '<span style="color:#FF976A;">待执行</span>';
            $assInfo[$k]['operation_name']     = '待保养';
            if (!$execute) {
                //未保养
                if ($is_super) {
                    //超级管理员
                    $assInfo[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-sm layui-btn-normal layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">待保养</button>';
                    if ($wx_sign_in == 1) {
                        //有开启的话 需要签到保养
                        $assInfo[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="sign">需要签到</button>';
                        $assInfo[$k]['need_sign'] = true;
                        if (isset($sign_assnums[$v['assnum']])) {
                            $assInfo[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">待保养</button>';
                            $assInfo[$k]['need_sign'] = false;
                            $assInfo[$k]['doTask']    = true;
                            $assInfo[$k]['bg_color']  = '#1989fa';
                            $assInfo[$k]['actionurl'] = $doTask['actionurl'];
                        }
                    } else {
                        $assInfo[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">待保养</button>';
                        $assInfo[$k]['doTask']    = true;
                        $assInfo[$k]['bg_color']  = '#1989fa';
                        $assInfo[$k]['actionurl'] = $doTask['actionurl'];
                    }
                } else {
                    if ($doTask) {
                        if ($wx_sign_in == 1) {
                            //有开启的话 需要签到保养
                            $assInfo[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="sign">需要签到</button>';
                            $assInfo[$k]['need_sign'] = true;
                            if (isset($sign_assnums[$v['assnum']])) {
                                $assInfo[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">待保养</button>';
                                $assInfo[$k]['need_sign'] = false;
                                $assInfo[$k]['doTask']    = true;
                                $assInfo[$k]['bg_color']  = '#1989fa';
                                $assInfo[$k]['actionurl'] = $doTask['actionurl'];
                            }
                        } else {
                            $assInfo[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">待保养</button>';
                            $assInfo[$k]['doTask']    = true;
                            $assInfo[$k]['bg_color']  = '#1989fa';
                            $assInfo[$k]['actionurl'] = $doTask['actionurl'];
                        }
                    } else {
                        $assInfo[$k]['operation'] = '<button type="button" class="layui-btn layui-btn-disabled layui-btn-xs">待保养</button>';
                    }
                }
            } else {
                $assInfo[$k]['assets_status_name'] = '<span style="color:#07C160;">已完成</span>';
                $assInfo[$k]['show_upload']        = true;
                //已保养
                $assInfo[$k]['is_complete'] = 1;
                if ($execute['asset_status_num'] == C('ASSETS_STATUS_NORMAL') || $execute['asset_status_num'] == C('ASSETS_STATUS_SMALL_PROBLEM')) {
                    //asset_status_num 为1 、2 的合格
                    $assInfo[$k]['operation_name'] = '合格';
                    $assInfo[$k]['actionurl']      = $doTask['actionurl'];
                    $assInfo[$k]['operation']      = '<button type="button" class="layui-btn layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">合格</button>';
                } elseif ($v['asset_status_num'] == C('ASSETS_STATUS_NOT_OPERATION')) {
                    //asset_status_num 为7 的不做保养
                    $assInfo[$k]['operation_name'] = '不保养';
                    $assInfo[$k]['actionurl']      = $doTask['actionurl'];
                    $assInfo[$k]['operation']      = '<button type="button" class="layui-btn layui-btn-warm layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">不保养</button>';
                } else {
                    $assInfo[$k]['operation_name'] = '异常';
                    $assInfo[$k]['actionurl']      = $doTask['actionurl'];
                    $assInfo[$k]['operation']      = '<button type="button" class="layui-btn layui-btn-danger layui-btn-xs" lay-event="doTask" data-url="' . $doTask['actionurl'] . '">异常</button>';
                }
            }
        }
        $res['assInfo'] = $assInfo;

        //查询各科室设备完成情况
        $depart_assets = $this->DB_get_all('assets_info', 'assid,assets,assnum,assorignum,model,departid',
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
        foreach ($departInfo as $k => $v) {
            $exec_where['cycid']             = $cycid;
            $exec_where['assetnum']          = ['in', $v['assnum']];
            $exec_where['status']            = 2;//已巡查的
            $total_execute                   = $this->DB_get_count('patrol_execute', $exec_where);
            $departInfo[$k]['total_execute'] = $total_execute;
            $departInfo[$k]['not_execute']   = count($v['assnum']) - $total_execute;
            $departInfo[$k]['progress']      = $total_execute . ' / ' . $v['assnum_nums'];
            if (count($v['assnum']) == $total_execute) {
                $departInfo[$k]['bg'] = 'layui-bg-green';
            } else {
                $departInfo[$k]['bg'] = 'layui-bg-red';
            }
        }
        $res['departInfo']           = $departInfo;
        $cycleInfo['total_progress'] = $cycleInfo['implement_sum'] . ' / ' . $cycleInfo['assets_nums'];
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
        $cycleInfo['total_progress'] = $cycleInfo['implement_sum'] . ' / ' . $cycleInfo['assets_nums'];
        $cycleInfo['is_overdue']     = 0;
        $cycleInfo['now']            = date('Y-m-d H:i:s');
        if (date('Y-m-d') > $cycleInfo['cycle_end_date']) {
            //当前已逾期
            $cycleInfo['is_overdue'] = 1;
            $cycleInfo['min_date']   = $cycleInfo['cycle_start_date'];
            $cycleInfo['max_date']   = date('Y-m-d');
            $hint                    = '计划日期：' . $cycleInfo['cycle_start_date'] . ' 到 ' . $cycleInfo['cycle_end_date'] . '<span style="color: red;padding-left: 10px;">当前已逾期</span>';
            $cycleInfo['hint']       = $hint;
        }
        $res['cycleInfo'] = $cycleInfo;
        return $res;
    }

    /**
     * Notes:获取计划审批信息
     *
     * @param $patrid int 计划ID
     */
    public function get_approve_info($patrid)
    {
        $where['is_delete']     = C('NO_STATUS');
        $where['approve_class'] = 'patrol';
        $where['process_node']  = C('PATROL_APPROVE');
        $where['patrid']        = $patrid;
        $apps                   = $this->DB_get_all('approve', 'apprid,patrid,approver,approve_time,is_adopt,remark',
            $where, '', 'process_node_level,apprid asc');
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

    /**
     * Notes: 巡查验收记录
     *
     * @param $cycid int 周期计划ID
     */
    public function get_exam_res($cycid)
    {
        $res  = [];
        $data = $this->DB_get_all('patrol_examine_one', 'assnum', ['cycid' => $cycid, 'status' => 1]);
        foreach ($data as $v) {
            $res[] = $v['assnum'];
        }
        return $res;
    }

    //获取已保养设备数量
    public function getPatroled($hospital_id, $start, $end)
    {
        $start  = date("Y-m-d H:i:s", $start);
        $end    = date("Y-m-d H:i:s", $end);
        $where  .= "  B.hospital_id = " . $hospital_id;
        $where  .= " and A.complete_time >= '" . $start . "' and A.complete_time <= '" . $end . "'";
        $join   = " LEFT JOIN sb_patrol_plan AS B ON A.patrid = B.patrid";
        $fileds = 'sum(A.not_operation_assnum) as not_operation_assnum,sum(A.abnormal_sum) as abnormal_sum,sum(A.implement_sum) as implement_sum';
        $data   = $this->DB_get_all_join('patrol_plan_cycle', 'A', $fileds, $join, $where);

        $finishNum = 0;
        $passNum   = 0;
        foreach ($data as $k => $v) {
            if (empty($v['not_operation_assnum'])) {
                $v['not_operation_assnum'] = 0;
                $finishNum                 = $v['implement_sum'] - $v['not_operation_assnum'];
                $passNum                   = $v['implement_sum'] - $v['abnormal_sum'] - $v['not_operation_assnum'];
            }
        }
        $res = [
            'finishNum' => $finishNum,
            'passNum'   => $passNum,
        ];
        return $res;

    }
}
