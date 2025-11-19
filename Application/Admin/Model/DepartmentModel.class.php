<?php

namespace Admin\Model;

use Think\Model;
use Think\Model\RelationModel;

class DepartmentModel extends CommonModel
{
    protected $len = 500;
    protected $tablePrefix = 'sb_';
    protected $tableName = 'department';


    //获取设备汇总列表数据
    public function assetsSummary()
    {
        $data_start = date('Y') . '-01-01';
        //$data_end = date('Y-m-d');
        $startTime = I('POST.assetsSummaryStartDate') ? strtotime(I('POST.assetsSummaryStartDate')) : strtotime($data_start);
        $endTime = I('POST.assetsSummaryEndDate') ? strtotime(I('POST.assetsSummaryEndDate')) : time();
        $priceMin = I('POST.assetsSummaryPriceMin') ? I('POST.assetsSummaryPriceMin') : 0;
        $priceMax = I('POST.assetsSummaryPriceMax');
        $departids = I('POST.departids');
        $hospital_id = session('current_hospitalid');
        $lists = $this->getSummaryData($startTime, $endTime, $priceMin, $priceMax, $departids, $hospital_id);
        if (!$lists) {
            return $lists;
        }
        $res = $this->handleAssetsSummaryData($lists);
        $hospitalName = $this->get_hospital_name($hospital_id);
        //获取报表标题
        $res['reportTitle'] = $hospitalName['hospital_name'] . '资产汇总表';
        //获取报表搜索条件范围
        $res['reportTips'] = $this->getReportTips($startTime, $endTime, $priceMin, $priceMax);
        return $res;
    }


//科室账单详情列表
    public function departmentSummary()
    {
        $data_start = date('Y') . '-01-01';
        $data_end = date('Y-m-d');
        $startDate = I('POST.startDate') ? I('POST.startDate') : $data_start;
        $endDate = I('POST.endDate') ? I('POST.endDate') : $data_end;
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate.' 23:59:59');
        if($startTime > $endTime){
            die(json_encode(array('status' => -1, 'msg' => '开始日期不能大于结束日期')));
        }
        $priceMin = I('POST.priceMin') ? I('POST.priceMin') : 0;
        $priceMax = I('POST.priceMax');
        $departids = I('POST.departid');
        //获取符合条件数据
        $lists = $this->getDepartmentAssetsSummaryData($startTime,$endTime,$priceMin,$priceMax,$departids);
        if(!$lists){
            return $lists;
        }
        $result['limit'] = 200;
        $result['offset'] = 0;
        $result['total'] = count($lists);
        $result['rows'] = $lists;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $legend['assets_guarantee'] = ['在保设备','过保设备'];
        $legend['assets_price'] = ['0~10000','10001~50000','50001~100000','100000以上'];
        $price_1 = $price_2 = $price_3 = $price_4 = $zaibao = $nozaibao = 0;
        foreach ($lists as $k=>$v){
            if($v['totalPrice'] >= 0 && $v['totalPrice'] <= 10000){
                $price_1 += 1;
            }
            if($v['totalPrice'] >= 10001 && $v['totalPrice'] <= 50000){
                $price_2 += 1;
            }
            if($v['totalPrice'] >= 50001 && $v['totalPrice'] <= 100000){
                $price_3 += 1;
            }
            if($v['totalPrice'] >= 100001){
                $price_4 += 1;
            }
            if($v['isGuarantee'] == '否'){
                $nozaibao += 1;
            }else{
                $zaibao += 1;
            }
        }
        $series['assets_price'] = $series['assets_guarantee'] = [];
        foreach ($legend['assets_guarantee'] as $k=>$v){
            switch ($v){
                case '在保设备':
                    $series['assets_guarantee'][$k]['name'] = $v;
                    $series['assets_guarantee'][$k]['value'] = $zaibao;
                    break;
                case '过保设备':
                    $series['assets_guarantee'][$k]['name'] = $v;
                    $series['assets_guarantee'][$k]['value'] = $nozaibao;
                    break;
            }
        }
        foreach ($legend['assets_price'] as $k=>$v){
            switch ($v){
                case '0~10000':
                    $series['assets_price'][$k]['name']  = $v;
                    $series['assets_price'][$k]['value'] = $price_1;
                    break;
                case '10001~50000':
                    $series['assets_price'][$k]['name']  = $v;
                    $series['assets_price'][$k]['value'] = $price_2;
                    break;
                case '50001~100000':
                    $series['assets_price'][$k]['name']  = $v;
                    $series['assets_price'][$k]['value'] = $price_3;
                    break;
                case '100000以上':
                    $series['assets_price'][$k]['name']  = $v;
                    $series['assets_price'][$k]['value'] = $price_4;
                    break;
            }
        }
        $result['condition']['priceStart'] = $priceMin;
        $result['condition']['priceEnd'] = $priceMax;
        $result['condition']['startTime'] = $startTime;
        $result['condition']['endTime'] = $endTime;
        $hosName = $this->get_hospital_name($result['rows'][0]['hospital_id']);
        $hospitalName = $hosName['hospital_name'];
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        //获取报表标题
        $result['reportTitle'] = $hospitalName.'（'.$departname[$departids]['department'].'）资产电子账单';
        //获取报表搜索条件范围
        $result['reportTips'] = $this->getReportTips($startTime,$endTime,$priceMin,$priceMax);
        $result['legend'] = $legend;
        $result['series'] = $series;
        return $result;
    }


    /**
     * Notes: 获取部门列表
     * @return mixed
     */
    public function getDepartmentLists()
    {
        //接收数据
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $departid = I('post.departid');
        $departmentname = I('post.department');
        $address = I('post.address');
        $hospital_id = I('POST.hospital_id');
        $departrespon = I('post.departrespon');
        $where['is_delete'] = 0;

        //模糊查询
        if ($departmentname) {
            //科室名称搜索
            $where['department'] = array('like', "%$departmentname%");
        }
        if ($address) {
            //所在位置搜索
            $where['address'] = array('like', "%$address%");
        }
        if ($departrespon) {
            //科室负责人搜索
            $where['departrespon'] = array('like', "%$departrespon%");
        }
        if ($hospital_id) {
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = array('in', session('current_hospitalid'));
        }
        if ($departid) {
            //科室id查询
            $where['departid'] = ['in', $departid];
        }
        //查询当前用户是否有权限进行修改科室
        $addDepartment = get_menu('BaseSetting', 'IntegratedSetting', 'addDepartment');
        //查询当前用户是否有权限进行修改科室
        $editDepartment = get_menu('BaseSetting', 'IntegratedSetting', 'editDepartment');
        //查询当前用户是否有权限进行删除科室
        $deleteDepartment = get_menu('BaseSetting', 'IntegratedSetting', 'deleteDepartment');
        //查询当前用户是否有添加用户权限
        $adduser = get_menu('BaseSetting', 'User', 'addUser');
        //查询当前用户是否有添加审批流程权限
        $process = get_menu('BaseSetting', 'ApproveSetting', 'addProcess');
        $total = $this->DB_get_count('department', $where);
        //查询数据库
        $department = $this->DB_get_all('department', '', $where, '', 'assetssum desc');

        //获取子科室的数据
        $parent = [];
        $depart = [];
        $join = 'LEFT JOIN sb_department as B ON B.departid = A.departid';
        $depart_data = $this->DB_get_all_join('assets_info', 'A', 'count(*) as num,sum(A.buy_price) as price,A.departid,B.parentid', $join, array('A.is_delete' => 0, 'A.hospital_id' => session('current_hospitalid'), 'status' => array('neq', '2')), 'A.departid');

        foreach ($depart_data as $key => $value) {
            if ($value['parentid'] != '0') {
                $parent[$value['parentid']]['num'] += $value['num'];
                $parent[$value['parentid']]['price'] += $value['price'];
            }
            $depart[$value['departid']]['num'] = $value['num'];
            $depart[$value['departid']]['price'] = $value['price'];

        }

        //查询所有设备已占用的科室
        $use_depart = $this->DB_get_one('assets_info', 'group_concat(distinct departid) as use_departid', array('is_delete' => 0, 'hospital_id' => session('current_hospitalid')));

        $use_departid = explode(',', $use_depart['use_departid']);
        //查询各科室中报废的价格和数量
        $scrap_data = $this->DB_get_all('assets_info', 'count(assid) as num,sum(buy_price) as price,departid', array('is_delete' => 0, 'hospital_id' => session('current_hospitalid'), 'status' => array('eq', '2')), 'departid');

        $scrap = [];
        foreach ($scrap_data as $key => $value) {
            $scrap[$value['departid']]['num'] = $value['num'];
            $scrap[$value['departid']]['price'] = $value['price'];
        }

        foreach ($department as $k => $v) {

            // if (isset($parent[$v['departid']])) {
            //     $department[$k]['assetssum'] = $department[$k]['assetssum']+$parent[$v['departid']]['num'];
            //     $department[$k]['assetsprice'] = $department[$k]['assetsprice']+$parent[$v['departid']]['price'];
            // }
            // if (isset($scrap[$v['departid']])) {


            //     $department[$k]['assetssum'] = $department[$k]['assetssum']-$scrap[$v['departid']]['num'];
            //     $department[$k]['assetsprice'] = $department[$k]['assetsprice']-$scrap[$v['departid']]['price'];
            // }

            if (isset($depart[$v['departid']])) {
                $department[$k]['assetssum'] = $depart[$v['departid']]['num'];
                $department[$k]['assetsprice'] = $depart[$v['departid']]['price'];
            }
            $html = '<div class="layui-btn-group">';
            if ($editDepartment) {
                $html .= $this->returnButtonLink('修改', $editDepartment['actionurl'], 'layui-btn layui-btn-xs layui-btn-warm', '', 'lay-event = editDepartment');
            }
            if ($adduser || $process) {
                $html .= $this->returnButtonLink('审批人', '', 'layui-btn layui-btn-xs layui-btn-normal', '', 'lay-event = manager');
            }
            //if ($v['parentid'] == 0) {
                if ($addDepartment) {
                    $html .= $this->returnButtonLink('子科室', $addDepartment['actionurl'], 'layui-btn layui-btn-xs', '', 'lay-event = addDepartment');
                }
            //}
            if ($deleteDepartment) {
                if (in_array($v['departid'], $use_departid) || $department[$k]['assetssum'] > 0) {
                    $html .= $this->returnButtonLink('删除', $deleteDepartment['actionurl'], 'layui-btn layui-btn-xs layui-btn-disabled', '', '');
                } else {
                    $html .= $this->returnButtonLink('删除', $deleteDepartment['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'lay-event = deleteDepartment');
                }
            }
            $html .= '</div>';
            $department[$k]['depart_operation'] = $html;
        }
        $result['total'] = $total;
        $result['a'] = $depart_data;
        $result["code"] = 200;
        $result["limit"] = (int)$limit;
        $result['rows'] = $department;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 返回特定条数数据
     * @param array $data 要返回的数据
     * @return array
     */
    public function returnLimitDate($data)
    {
        F('departData', $data);
        $departData = F('departData');
        $arr = array();
        $i = 0;
        foreach ($departData as $k => $v) {
            if ($i < $this->len && $v) {
                $arr[] = $v;
                $i++;
                unset($departData[$k]);
            }
        }
        F('departData', $departData);
        return $arr;
    }

    /**
     * Notes: 批量添加科室方法（excel批量导入科室时用）
     * @param $departnum string 科室编码
     * @param $department string 科室名称
     * @param $address string 科室地址
     * @param $departrespon string 科室负责人
     * @param $assetsrespon string 科室设备负责人
     * @param $departtel string 科室电话
     * @return array
     */
    public function addDepartment($departnum, $department, $address, $departrespon, $assetsrespon, $departtel)
    {
        $res = $this->DB_get_all('department', 'departnum,department', array('1'), '', '');
        $dment = $dnum = array();
        foreach ($res as $k => $v) {
            $dment[] = $v['department'];
            $dnum[] = $v['departnum'];
        }
        $departnum = explode(',', $departnum);
        $department = explode(',', $department);
        $address = explode(',', $address);
        $departrespon = explode(',', $departrespon);
        $assetsrespon = explode(',', $assetsrespon);
        $departtel = explode(',', $departtel);
        foreach ($departnum as $k => $v) {
            if ($v == '') {
                return array('status' => -1, 'msg' => '科室编号不能为空！');
                break;
            }
            if (in_array($v, $dnum)) {
                return array('status' => -1, 'msg' => $v . '科室编号已存在！', 'key' => 'departnum', 'val' => $v, 'res' => $res);
                break;
            }
            if ($department[$k] == '') {
                return array('status' => -1, 'msg' => '科室名称不能为空！');
                break;
            }
            if (in_array($department[$k], $dment)) {
                return array('status' => -1, 'msg' => $department[$k] . '科室名称已存在！', 'key' => 'department', 'val' => $department[$k], 'res' => $res);
                break;
            }
            if ($address[$k] == '') {
                return array('status' => -1, 'msg' => '所在位置不能为空');
                break;
            }
            if ($departrespon[$k] == '') {
                return array('status' => -1, 'msg' => '科室负责人不能为空');
                break;
            }
            if ($assetsrespon[$k] == '') {
                return array('status' => -1, 'msg' => '设备负责人不能为空');
                break;
            }
            if ($departtel[$k] == '') {
                return array('status' => -1, 'msg' => '科室电话不能为空');
                break;
            }
        }
        $this->startTrans();
        $values = '';
        $i = 1;
        $Model = M('department');
        foreach ($departnum as $k => $v) {
            $values .= "('" . $v . "','" . $department[$k] . "','" . $address[$k] . "','" . $departrespon[$k] . "','" . $assetsrespon[$k] . "','" . $departtel[$k] . "'),";
            if ($i % ($this->len) == 0) {
                $values = trim($values, ',');
                $sql = "insert into " . $this->tablePrefix . $this->tableName . "(departnum,department,address,departrespon,assetsrespon,departtel) values";
                $sql .= $values;
                $result = $Model->execute($sql);
                if (false === $result) {
                    // 发生错误自动回滚事务
                    $this->rollback();
                    return array('status' => -1, 'msg' => '保存数据失败');
                }
                // 提交事务
                $this->commit();
                $values = '';
            }
            $i++;
        }
        if ($values) {
            $values = trim($values, ',');
            $sql = "insert into " . $this->tablePrefix . $this->tableName . "(departnum,department,address,departrespon,assetsrespon,departtel) values";
            $sql .= $values;
            $result = $Model->execute($sql);
            if (false === $result) {
                // 发生错误自动回滚事务
                $this->rollback();
                return array('status' => -1, 'msg' => '保存数据失败');
            }
            // 提交事务
            $this->commit();
        }
        //继续返回下一批数据
        $departData = F('departData');
        $arr = array();
        $i = 0;
        foreach ($departData as $k => $v) {
            if ($i < $this->len && $v) {
                $arr[] = $v;
                $i++;
                unset($departData[$k]);
            }
        }
        F('departData', $departData);
        return array('status' => 1, 'msg' => '保存成功', 'data' => $arr);
    }

    /**
     * Notes:组织数据
     * @params1 $lists 要组织的数据
     * return array
     */
    public function handleAssetsSurvey($lists)
    {
        $res = [];
        $departments = [];
        $res['lists'] = $lists;
        //处理数据
        foreach ($lists as $key => $v) {
            $departments[] = $v['department'] . '';
            $res['totalNum'][] = (int)$v['totalNum'];
            $res['totalPrice'][] = $v['totalPrice'];
            $res['newAddNum'][] = (int)$v['newAddNum'];
            $res['newAddPrice'][] = $v['newAddPrice'];
            $res['working'][] = (int)$v['working'];
            $res['repairing'][] = (int)$v['repairing'];
            $res['scraped'][] = (int)$v['scraped'];
        }
        $res['departments'] = $departments;
        //要显示的数据
        $showName = array('totalNum' => '登记台数', 'newAddNum' => '新增台数', 'working' => '在用台数', 'repairing' => '维修中台数', 'scraped' => '报废台数', 'totalPrice' => '总金额', 'newAddPrice' => '新增总金额');
        //要显示的数据（柱状）
        $barData = array('totalNum', 'newAddNum', 'working', 'repairing', 'scraped');
        //要显示的数据（折线）
        $lineData = array('totalPrice', 'newAddPrice');
        //饼图说明
        $descData = array('working', 'repairing', 'scraped');
        //默认不显示数据
        $selected = array('报废台数');
        $res['series'] = [];
        $i = 0;
        foreach ($showName as $key => $val) {
            $res['legend'][$i] = $val;
            if (in_array($val, $selected)) {
                $res['selected'][$val] = false;
            }
            if (in_array($key, $barData)) {
                $res['series'][$i]['name'] = $val;
                $res['series'][$i]['type'] = 'bar';
                $res['series'][$i]['data'] = $res[$key];
            } elseif (in_array($key, $lineData)) {
                $res['series'][$i]['name'] = $val;
                $res['series'][$i]['type'] = 'line';
                $res['series'][$i]['yAxisIndex'] = 1;
                $res['series'][$i]['data'] = $res[$key];
            }
            $i++;
        }
        $res['series'][$i]['name'] = '总览';
        $res['series'][$i]['type'] = 'pie';
        $res['series'][$i]['z'] = '1000';
        $res['series'][$i]['radius'] = '15%';
        $res['series'][$i]['center'] = array('78%', '28%');
        foreach ($descData as $k => $v) {
            $num = 0;
            foreach ($res[$v] as $k1 => $v1) {
                $num += $v1;
            }
            $res['series'][$i]['data'][$k]['name'] = $showName[$v] . '(' . $num . ')';
            $res['series'][$i]['data'][$k]['value'] = $num;
        }
        return $res;
    }

    /**
     * Notes:获取报表搜索条件范围
     * @param $startTime int 添加时间始
     * @param $endTime int 添加时间末
     * @param $priceMin float 总价区间始
     * @param $priceMax float 总价区间末
     * @return string
     */
    public function getReportTips($startTime, $endTime, $priceMin, $priceMax)
    {
        $tips = '';
        //报表日期返回
        if ($startTime == 0) {
            //获取设备表中添加时间最小值
            $date = $this->DB_get_one('assets_info', 'min(adddate) as startTime', array('is_delete' => C('NO_STATUS'), 'adddate' => array('gt', '0')));
            $tips = '报表日期：' . getHandleTime($date['startTime']) . ' 至 ' . getHandleTime($endTime) . ' / ';
        } else {
            $tips = '报表日期：' . getHandleTime($startTime) . ' 至 ' . getHandleTime($endTime) . ' / ';
        }
        //报表金额区间返回
        if ($priceMax) {
            $tips .= '  总金额区间（元）：' . $priceMin . ' 至 ' . $priceMax;
        } else {
            $tips .= '  总金额区间（元）：≥ ' . $priceMin;
        }
        return $tips;
    }

    /**
     * Notes: 根据搜索条件获取资产概况
     * @param $startTime int 添加时间始
     * @param $endTime int 添加时间末
     * @param $priceMin float 总价区间始
     * @param $priceMax float 总价区间末
     * @return array
     */
    public function getReportData($startTime, $endTime, $priceMin, $priceMax, $hospital_id, $export = false)
    {
        $where = ' where a.hospital_id =' . $hospital_id;
        $where .= ' AND a.is_delete =' . C('NO_STATUS');
        $where .= ' AND b.is_delete =' . C('NO_STATUS');
        $where .= ' AND b.status not in(' . C('ASSETS_STATUS_OUTSIDE') . ',' . C('ASSETS_STATUS_OUTSIDE_ON') . ')';
        $sql = "select a.departid,a.department,count(assid) as totalNum,IFNULL(sum(b.buy_price),0) as totalPrice,
	            (select count(buy_price) from sb_assets_info where adddate >='{$startTime}' and adddate <='{$endTime}' and departid = b.departid) as newAddNum,
	            IFNULL((select sum(buy_price) from sb_assets_info where adddate >='{$startTime}' and adddate <='{$endTime}' and departid = b.departid),0) as newAddPrice,
	            (select count(assid) from sb_assets_info where status = 0 and departid = b.departid) as working,
	            (select count(assid) from sb_assets_info where status = 1 and departid = b.departid) as repairing,
	            (select count(assid) from sb_assets_info where status = 2 and departid = b.departid) as scraped
	            from sb_department as a left join sb_assets_info as b on a.departid = b.departid $where group by a.departid";
        $model = new \Think\Model();
        $resLists = $model->query($sql);
        if (!$resLists) {
            return array();
        }
        $lists = [];
        foreach ($resLists as $k => $v) {
            if ($priceMax) {
                if ($v['totalPrice'] >= $priceMin && $v['totalPrice'] <= $priceMax) {
                    $lists[] = $v;
                }
            } else {
                if ($v['totalPrice'] >= $priceMin) {
                    $lists[] = $v;
                }
            }
        }
        if ($export) {
            //出excel，行末加统计数据
            $sum = [];
            $sum['totalNum'] = $sum['totalPrice'] = $sum['working'] = $sum['repairing'] = $sum['scraped'] = $sum['newAddNum'] = $sum['newAddPrice'] = 0;
            foreach ($lists as $k => $v) {
                foreach ($sum as $k1 => $v1) {
                    if ($k1 == 'totalPrice' || $k1 == 'newAddPrice') {
                        $sum[$k1] += (float)$v[$k1];
                    } else {
                        $sum[$k1] += (int)$v[$k1];
                    }
                }
            }
            $count = count($lists);
            $lists[$count]['departid'] = 0;
            $lists[$count]['department'] = '合计：';
            foreach ($sum as $k => $v) {
                $lists[$count][$k] = $v;
            }
        }
        return $lists;
    }

    /**
     * Notes: 根据搜索条件获取资产概况
     * @param $startTime int 添加时间始
     * @param $endTime int 添加时间末
     * @param $priceMin float 总价区间始
     * @param $priceMax float 总价区间末
     * @return array
     */
    public function getOnUseReportData($startTime, $endTime, $priceMin, $priceMax, $hospital_id, $export = false)
    {
        $where = ' where status = 0 and is_delete = 0 and hospital_id = ' . $hospital_id;
        $where .= ' and adddate >= ' . $startTime . ' and adddate <= ' . $endTime;
        //排除已删除的科室
        $del_ids = $this->DB_get_one('department', 'group_concat(departid) as departids', array('is_delete' => 1, 'hospital_id' => $hospital_id));
        if ($del_ids) {
            $del_ids['departids'] = 0;
            $where .= ' and departid not in(' . $del_ids['departids'] . ')';
        }
        $having = 'totalPrice >= ' . $priceMin;
        if ($priceMax) {
            $having .= ' and totalPrice <= ' . $priceMax;
        }
        $sql = "select departid,count(assid) as totalNum,SUM(IF(status = 0,1,0)) as onUse,IFNULL(sum(buy_price),0) as totalPrice from sb_assets_info " .
            $where .= " group by departid  having " . $having . ' order by departid asc';
        $model = new \Think\Model();
        $lists = $model->query($sql);
        if (!$lists) {
            return array();
        }
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($lists as $k => $v) {
            $lists[$k]['department'] = $departname[$v['departid']]['department'];
            $lists[$k]['other'] = (int)$v['totalNum'] - (int)$v['onUse'];
        }
        if ($export) {
            //出excel，行末加统计数据
            $sum = [];
            $sum['totalNum'] = $sum['onUse'] = $sum['other'] = $sum['totalPrice'] = 0;
            foreach ($lists as $k => $v) {
                foreach ($sum as $k1 => $v1) {
                    if ($k1 == 'totalPrice') {
                        $sum[$k1] += (float)$v[$k1];
                    } else {
                        $sum[$k1] += (int)$v[$k1];
                    }
                }
            }
            $count = count($lists);
            $lists[$count]['departid'] = 0;
            $lists[$count]['department'] = '合计：';
            foreach ($sum as $k => $v) {
                $lists[$count][$k] = $v;
            }
        }
        return $lists;
    }

    /**
     * Notes: 根据搜索条件获取资产概况
     * @param $startTime int 添加时间始
     * @param $endTime int 添加时间末
     * @param $priceMin float 总价区间始
     * @param $priceMax float 总价区间末
     * @return array
     */
    public function getSummaryData($startTime,$endTime,$priceMin,$priceMax,$departids,$hospital_id,$export=false)
    {
        $not_in_status = ' b.status not in('.C('ASSETS_STATUS_OUTSIDE').','.C('ASSETS_STATUS_OUTSIDE_ON').')';
        $where = ' where b.is_delete = 0 and a.is_delete = 0 and a.hospital_id ='.$hospital_id.' and '.$not_in_status;
        $and = ' and c.standard_date >= '.$startTime.' and c.standard_date <= '.$endTime;
        if ($departids) {
            $where .= ' and a.departid in('.$departids.')';
        }
        $having = 'totalPrice >= '.$priceMin;
        if($priceMax){
            $having .= ' and totalPrice <= '.$priceMax;
        }
        $model = new \Think\Model();
        //统计在验收合格时间段内和符合条件总金额内的设备数量和总金额
        $sql = "select a.departid,a.department,count(b.assid) as totalNum,IFNULL(sum(buy_price),0) as totalPrice 
                from sb_department as a left join sb_assets_info as b on a.departid = b.departid left join sb_assets_contract as c on b.acid = c.acid ".$and
            .$where." group by a.departid having ".$having.' order by a.hospital_id asc';
        $lists = $model->query($sql);
        if(!$lists){
            return array();
        }
        foreach($lists as &$val){
            //查询科室设备assnum assid
            $tmpinfo = $this->DB_get_all('assets_info','assid,assnum',array('departid'=>$val['departid']));
            $val['assids'] = '';
            foreach ($tmpinfo as $k=>$v){
                $val['assids'] .= $v['assid'].',';
            }
            $val['assids'] = trim($val['assids'],',');
            $url = C('ADMIN_NAME')."/AssetsStatis/assetsSummary.html?id=".$val['departid'].'&type=departmentSummary';
            $val['departmentSummary'] = $val['department'];
            $val['operation'] = $this->returnListLink('科室详情账单',$url,'depart_detail','layui-btn layui-btn-xs layui-btn-normal statement','','');;
        }
        $reWhere = " and applicant_time >= ".$startTime." and applicant_time <= ".$endTime;
        foreach ($lists as $k=>$v){
            $lists[$k]['partNum'] = 0;//维修配件数
            $lists[$k]['actualPrice'] = 0;//维修总费用
            $lists[$k]['repairNum'] = 0;//报修数
            $lists[$k]['overRepairNum'] = 0;//修复次数
            if($v['assids']){
                $sql1 = "select repid,status,count(*) as num,sum(part_num) as partNum,sum(actual_price) as actualPrice from sb_repair where assid in(".$v['assids'].")".$reWhere." group by status";
                $repaired = $model->query($sql1);
                foreach($repaired as $k1=>$v1){
                    $lists[$k]['partNum'] += $v1['partNum'];
                    $lists[$k]['actualPrice'] += $v1['actualPrice'];
                    $lists[$k]['repairNum'] += $v1['num'];
                    if($v1['status'] == C('REPAIR_ALREADY_ACCEPTED')){
                        $lists[$k]['overRepairNum'] += $v1['num'];
                    }
                }
            }
        }
        //统计维保期内设备数
        $nowTime = time();
        foreach ($lists as $k=>$v){
            if($v['assids']){
                $sql2 = "select count(a.assid) as guaranteeNum from sb_assets_info as a 
                         left join sb_assets_contract as b on a.acid = b.acid 
                         left join sb_assets_insurance as c on a.assid = c.assid 
                         where a.assid in (".$v['assids'].") and ( b.guarantee_date >= ".$nowTime." or c.overdate >= ".$nowTime.")";
                $guaranteeNum = $model->query($sql2);
                $lists[$k]['guaranteeNum'] = $guaranteeNum[0]['guaranteeNum'];
            }else{
                $lists[$k]['guaranteeNum'] = 0;
            }
        }
        //查询所有符合条件的巡查保养件计划
        $patrolSQL = "select cycid,patrid,plan_assnum,complete_time as endDate from sb_patrol_plans_cycle 
                      having endDate >= '".date('Y-m-d',$startTime)." 00:00:01' and endDate <= '".date('Y-m-d',$endTime)." 23:59:59'";
        $patrols = $model->query($patrolSQL);
        $patrolArr = array();
        $patrids = array();
        foreach ($patrols as $k=>$v){
            if(!in_array($v['patrid'],$patrids)){
                $patrids[] = $v['patrid'];
                $patrolArr[$v['patrid']] = json_decode($v['plan_assnum']);
            }else{
                $patrolArr[$v['patrid']] = array_keys(array_flip($patrolArr[$v['patrid']])+array_flip(json_decode($v['plan_assnum'])));
            }
        }
        //统计计划保养次数
        foreach ($lists as $k=>$v){
            $lists[$k]['patrolPlanNum'] = 0;
            foreach ($patrolArr as $k1=>$v1){
                $same = array_intersect(explode(',',$v['assnums']),$v1);
                $lists[$k]['patrolPlanNum'] += count($same);
            }
        }
        //查询所有保养执行次数
        foreach ($lists as $k=>$v){
            $imWhere = array();
            $imWhere['assetnum'] = array('in',explode(',',$v['assnums']));
            $imWhere['finish_time']  = array(array('EGT',$startTime),array('ELT',$endTime),'and');
            $imRes = $this->DB_get_one('patrol_execute','count(*) as implementNum',$imWhere);
            $lists[$k]['implementNum'] = $imRes['implementNum'];
        }
        if($export){
            //出excel，行末加统计数据
            $sum = [];
            $sum['totalNum'] = $sum['totalPrice'] = $sum['repairNum'] = $sum['overRepairNum'] = $sum['partNum'] = $sum['actualPrice'] = $sum['guaranteeNum'] = $sum['patrolPlanNum'] = $sum['implementNum'] = $sum['transferNum'] = 0;
            foreach ($lists as $k=>$v){
                foreach ($sum as $k1=>$v1){
                    if($k1 == 'totalPrice' || $k1 == 'actualPrice'){
                        $sum[$k1] += (float)$v[$k1];
                    }else{
                        $sum[$k1] += (int)$v[$k1];
                    }
                }
            }
            $count = count($lists);
            $lists[$count]['departid'] = 0;
            $lists[$count]['department'] = '合计：';
            foreach ($sum as $k=>$v){
                $lists[$count][$k] = $v;
            }
        }
        return $lists;
    }

    /**
     * Notes:组织数据
     * @params1 $lists 要组织的数据
     * return array
     */
    public function handleOnUseAssetsSurvey($lists)
    {
        $type = I('POST.type') ? I('POST.type') : 'pie';
        $res = [];
        $departments = [];
        $res['lists'] = $lists;
        //处理数据
        foreach ($lists as $key => $v) {
            $departments[] = $v['department'] . '';
            $res['onUse'][] = (int)$v['onUse'];
            $res['totalPrice'][] = $v['totalPrice'];
        }
        $res['departments'] = $departments;
        $res['series'] = [];
        $res['series']['name'] = '在用资产数量';
        $res['series']['type'] = $type;
        if ($type == 'pie') {
            $res['series']['radius'] = '50%';
            $res['series']['center'] = array('40%', '60%');
        }
        $i = 0;
        foreach ($lists as $key => $val) {
            $res['legend'][$i] = $val['department'];
            if ($type == 'pie') {
                $res['series']['data'][$i]['name'] = $val['department'];
                $res['series']['data'][$i]['value'] = $val['onUse'];
            } elseif ($type == 'bar') {
                $res['series']['data'][$i] = $val['onUse'];
            }
            $i++;
        }
        return $res;
    }

    /**
     * Notes:组织数据
     * @params1 $lists 要组织的数据
     * return array
     */
    public function handleAssetsSummaryData($lists)
    {
        $res = [];
        $departments = [];
        $res['lists'] = $lists;
        //获取说明标签、处理数据
        foreach ($lists as $key => $v) {
            $departments[] = $v['department'] . '';
            $res['totalNum'][] = (int)$v['totalNum'];
            $res['guaranteeNum'][] = (int)$v['guaranteeNum'];
            $res['totalPrice'][] = (float)$v['totalPrice'];
            $res['repairNum'][] = (int)$v['repairNum'];
            $res['overRepairNum'][] = (int)$v['overRepairNum'];
            $res['partNum'][] = (int)$v['partNum'];
            $res['actualPrice'][] = (float)$v['actualPrice'];
            $res['patrolPlanNum'][] = (int)$v['patrolPlanNum'];
            $res['implementNum'][] = (int)$v['implementNum'];
        }
        $res['departments'] = $departments;
        //要显示的数据
        $showName = array('totalNum' => '设备台数', 'guaranteeNum' => '维保期内台数', 'repairNum' => '报修次数', 'overRepairNum' => '修复次数', 'partNum' => '维修配件数', 'patrolPlanNum' => '保养计划次数', 'implementNum' => '保养执行次数', 'actualPrice' => '维修总费用（元）', 'totalPrice' => '总金额（元）');
        //要显示的数据（柱状）
        $barData = array('totalNum', 'guaranteeNum', 'repairNum', 'overRepairNum', 'partNum', 'patrolPlanNum', 'implementNum');
        //要显示的数据（折线）
        $lineData = array('totalPrice');
        //要显示的数据（单独Y轴标识）
        $bar2Data = array('actualPrice');
        //默认不显示数据
        $selected = array();
        $res['series'] = [];
        $i = 0;
        foreach ($showName as $key => $val) {
            $res['legend'][$i] = $val;
            if (in_array($val, $selected)) {
                $res['selected'][$val] = false;
            }
            if (in_array($key, $barData)) {
                $res['series'][$i]['name'] = $val;
                $res['series'][$i]['type'] = 'bar';
                $res['series'][$i]['data'] = $res[$key];
            } elseif (in_array($key, $lineData)) {
                $res['series'][$i]['name'] = $val;
                $res['series'][$i]['type'] = 'line';
                $res['series'][$i]['yAxisIndex'] = 1;
                $res['series'][$i]['data'] = $res[$key];
            } elseif (in_array($key, $bar2Data)) {
                $res['series'][$i]['name'] = $val;
                $res['series'][$i]['type'] = 'bar';
                $res['series'][$i]['yAxisIndex'] = 2;
                $res['series'][$i]['data'] = $res[$key];
            }
            $i++;
        }
        return $res;
    }

    /**
     * Notes:组织数据
     * @params1 $lists array 要组织的数据
     * @return array
     */
    public function handleDepartmentSummaryData($lists)
    {
        $res = [];
        $assets = [];
        $res['lists'] = $lists;
        //获取说明标签、处理数据
        foreach ($lists as $key => $v) {
            $assets[] = $v['assets'] . '';
            $res['totalNum'][] = (int)$v['totalNum'];
            $res['guaranteeNum'][] = (int)$v['guaranteeNum'];
            $res['totalPrice'][] = (float)$v['totalPrice'];
            $res['repairNum'][] = (int)$v['repairNum'];
            $res['overRepairNum'][] = (int)$v['overRepairNum'];
            $res['partNum'][] = (int)$v['partNum'];
            $res['actualPrice'][] = (float)$v['actualPrice'];
            $res['totalHours'][] = (float)$v['totalHours'];
            $res['patrolPlanNum'][] = (int)$v['patrolPlanNum'];
            $res['implementNum'][] = (int)$v['implementNum'];
        }
        $res['assets'] = $assets;
        //要显示的数据
        $showName = array('repairNum' => '报修次数', 'overRepairNum' => '修复次数', 'partNum' => '维修配件数', 'patrolPlanNum' => '保养计划次数', 'implementNum' => '保养执行次数', 'actualPrice' => '维修费用（元）', 'totalPrice' => '设备金额（元）');
        //要显示的数据（柱状）
        $barData = array('repairNum', 'overRepairNum', 'partNum', 'patrolPlanNum', 'implementNum');
        //要显示的数据（折线）
        $lineData = array('totalPrice');
        //要显示的数据（单独Y轴标识）
        $bar2Data = array('actualPrice');
        //默认不显示数据
        $selected = array();
        $res['series'] = [];
        $i = 0;
        foreach ($showName as $key => $val) {
            $res['legend'][$i] = $val;
            if (in_array($val, $selected)) {
                $res['selected'][$val] = false;
            }
            if (in_array($key, $barData)) {
                $res['series'][$i]['name'] = $val;
                $res['series'][$i]['type'] = 'bar';
                $res['series'][$i]['data'] = $res[$key];
            } elseif (in_array($key, $lineData)) {
                $res['series'][$i]['name'] = $val;
                $res['series'][$i]['type'] = 'line';
                $res['series'][$i]['yAxisIndex'] = 1;
                $res['series'][$i]['data'] = $res[$key];
            } elseif (in_array($key, $bar2Data)) {
                $res['series'][$i]['name'] = $val;
                $res['series'][$i]['type'] = 'bar';
                $res['series'][$i]['yAxisIndex'] = 2;
                $res['series'][$i]['data'] = $res[$key];
            }
            $i++;
        }
        return $res;
    }

    /**
     * Notes: 根据搜索条件获取资产概况
     * @param $startTime int 添加时间始
     * @param $endTime int 添加时间末
     * @param $priceMin float 总价区间始
     * @param $priceMax float 总价区间末
     * @return array
     */
    public function getDepartmentAssetsSummaryData($startTime,$endTime,$priceMin,$priceMax,$departids,$export=false)
    {
        $not_in_status = ' a.status not in('.C('ASSETS_STATUS_OUTSIDE').','.C('ASSETS_STATUS_OUTSIDE_ON').')';
        $where = ' where a.is_delete = 0 and a.departid = '.$departids.' and '.$not_in_status;
        $where .= ' and a.buy_price >= '.$priceMin;
        if($priceMax){
            $where .= ' and a.buy_price <= '.$priceMax;
        }
        $contAnd = ' and b.standard_date >= '.$startTime.' and b.standard_date <= '.$endTime;
        $model = new \Think\Model();
        //统计在验收合格时间段内和符合条件总金额内的设备数量和总金额
        $sql = "select a.assid,a.hospital_id,a.hospital_id,a.departid,a.assnum,a.assets,a.buy_price as totalPrice from sb_assets_info as a left join sb_assets_contract as b on a.acid = b.acid ".$contAnd.$where;
        $lists = $model->query($sql);
        if(!$lists){
            return array();
        }
        $assids = '';
        foreach($lists as $key=>$val){
            $lists[$key]['assnumUrl'] = $val['assnum'];
            $lists[$key]['assetsUrl'] = $val['assets'];
            $assids .= $val['assid'].',';
        }
        $assids = trim($assids,',');
        $reWhere = " and applicant_time >= ".$startTime." and applicant_time <= ".$endTime;
        $sql1 = "select repid,assid,status,count(*) as num,sum(part_num) as partNum,sum(actual_price) as actualPrice,sum(working_hours) as totalHours from sb_repair where assid in(".$assids.")".$reWhere." group by status,assid";
        $repaired = $model->query($sql1);
        foreach ($lists as $k=>$v){
            $lists[$k]['partNum'] = 0;//维修配件数
            $lists[$k]['actualPrice'] = 0;//维修总费用
            $lists[$k]['repairNum'] = 0;//报修数
            $lists[$k]['overRepairNum'] = 0;//修复次数
            $lists[$k]['totalHours'] = 0;//维修工时
            foreach($repaired as $k1=>$v1){
                if($v1['assid'] == $v['assid']){
                    $lists[$k]['partNum'] += $v1['partNum'];
                    $lists[$k]['actualPrice'] += $v1['actualPrice'];
                    $lists[$k]['repairNum'] += $v1['num'];
                    $lists[$k]['totalHours'] += $v1['totalHours'];
                    if($v1['status'] == C('REPAIR_ALREADY_ACCEPTED')){
                        $lists[$k]['overRepairNum'] += $v1['num'];
                    }
                }
            }
        }
        //查询设备是否在维保期内
        $nowTime = time();
        foreach ($lists as $k=>$v){
            $repairSql = "select a.assid from sb_assets_info as a left join sb_assets_contract as b on a.acid = b.acid 
                     left join sb_assets_insurance as c on a.assid = c.assid where a.assid = ".$v['assid']." and ( b.guarantee_date >= ".$nowTime." or c.overdate >= ".$nowTime.")";
            $repairRes = $model->query($repairSql);
            $lists[$k]['isGuarantee'] = '否';
            if($repairRes[0]['assid']){
                $lists[$k]['isGuarantee'] = '是';
            }
        }
        //查询所有符合条件的巡查保养件计划
        //查询所有符合条件的巡查保养件计划
        $patrolSQL = "select cycid,patrid,plan_assnum,complete_time as endDate from sb_patrol_plans_cycle 
                      having endDate >= '".date('Y-m-d',$startTime)." 00:00:01' and endDate <= '".date('Y-m-d',$endTime)." 23:59:59'";
        $patrols = $model->query($patrolSQL);
        //统计计划保养次数
        foreach ($lists as $k=>$v){
            $lists[$k]['patrolPlanNum'] = 0;
            foreach ($patrols as $vv){
                $assnums = json_decode($vv['plan_assnum']);
                if(in_array($v['assnum'],$assnums)){
                    $lists[$k]['patrolPlanNum'] += 1;
                }
            }
        }
        //查询所有保养执行次数
        foreach ($lists as $k=>$v){
            $imWhere = array();
            $imWhere['assetnum'] = $v['assnum'];
            $imWhere['finish_time']  = array(array('EGT',date('Y-m-d H:i:s',$startTime)),array('ELT',date('Y-m-d H:i:s',$endTime)),'and');
            $imRes = $this->DB_get_one('patrol_execute','count(*) as implementNum',$imWhere);
            $lists[$k]['implementNum'] = $imRes['implementNum'];
        }
        //添加链接
        $lists = $this->addUrl($lists);
        if($export){
            //出excel，行末加统计数据
            $sum = [];
            $sum['totalNum'] = $sum['totalPrice'] = $sum['repairNum'] = $sum['overRepairNum'] = $sum['totalHours'] = $sum['partNum'] = $sum['actualPrice'] = $sum['guaranteeNum'] = $sum['patrolPlanNum'] = $sum['implementNum'] = $sum['transferNum'] = 0;
            foreach ($lists as $k=>$v){
                foreach ($sum as $k1=>$v1){
                    if($k1 == 'totalPrice' || $k1 == 'actualPrice'){
                        $sum[$k1] += (float)$v[$k1];
                    }else{
                        $sum[$k1] += (int)$v[$k1];
                    }
                }
            }
            $count = count($lists);
            $lists[$count]['departid'] = 0;
            $lists[$count]['isGuarantee'] = '合计：';
            foreach ($sum as $k=>$v){
                $lists[$count][$k] = $v;
            }
        }
        return $lists;
    }

    /**
     * Notes: 添加跳转链接
     * @param $lists
     * @return mixed
     */
    public function addUrl($lists)
    {
        foreach ($lists as $key => $val) {
            $html = '<div class="layui-btn-group">';
            $html .= $this->returnListLink('设备详情', '', 'assets_detail', 'layui-btn layui-btn-xs layui-btn-normal', '', 'data-id="' . $val['assid'] . '"');
            if ($val['repairNum'] > 0 or $val['overRepairNum'] > 0) {
                $html .= $this->returnListLink('维修记录', C('ADMIN_NAME') . '/AssetsStatis/assetsSummary.html', 'showRepairRecord', 'layui-btn layui-btn-xs layui-btn-normal', '', 'data-id="' . $val['assid'] . '"');
            } else {
                $html .= $this->returnListLink('维修记录', '', '', 'layui-btn layui-btn-xs layui-btn-normal layui-btn-disabled', '', '');
            }
            $lists[$key]['repairNumUrl'] = $val['repairNum'];
            $lists[$key]['overRepairNumUrl'] = $val['overRepairNum'];
            $lists[$key]['partNumUrl'] = $val['partNum'];
            $lists[$key]['patrolPlanNumUrl'] = $val['patrolPlanNum'];
            $lists[$key]['implementNumUrl'] = $val['implementNum'];
            if ($val['partNum'] > 0) {
                $html .= $this->returnListLink('配件记录', C('ADMIN_NAME') . '/AssetsStatis/assetsSummary.html', 'showRepairParts', 'layui-btn layui-btn-xs layui-btn-normal', '', 'data-id="' . $val['assid'] . '"');
            } else {
                $html .= $this->returnListLink('配件记录', '', '', 'layui-btn layui-btn-xs layui-btn-normal layui-btn-disabled', '', '');
            }
            if ($val['patrolPlanNum'] > 0 or $val['implementNum'] > 0) {
                $html .= $this->returnListLink('保养记录', C('ADMIN_NAME') . '/AssetsStatis/assetsSummary.html', 'showPatrolPlan', 'layui-btn layui-btn-xs layui-btn-normal', '', 'data-assnum="' . $val['assnum'] . '"');
            } else {
                $html .= $this->returnListLink('保养记录', '', '', 'layui-btn layui-btn-xs layui-btn-normal layui-btn-disabled', '', '');
            }
            $html .= '</div>';
            $lists[$key]['operation'] = $html;
        }
        return $lists;
    }

    public function getPatrolPlan($startTime,$endTime,$assnum)
    {
        $model = new \Think\Model();
        //查询所有符合条件的巡查保养件计划
        $patrolSQL = "select cycid,patrid,plan_assnum,complete_time as endDate from sb_patrol_plans_cycle 
                      having endDate >= '".date('Y-m-d',$startTime)." 00:00:01' and endDate <= '".date('Y-m-d',$endTime)." 23:59:59'";
        $patrols = $model->query($patrolSQL);
        $patrolArr = array();
        $patrids = array();
        foreach ($patrols as $k=>$v){
            if(!in_array($v['patrid'],$patrids)){
                $patrids[] = $v['patrid'];
                $patrolArr[$v['patrid']] = json_decode($v['plan_assnum']);
            }else{
                $patrolArr[$v['patrid']] = array_keys(array_flip($patrolArr[$v['patrid']])+array_flip(json_decode($v['plan_assnum'])));
            }
        }
        $ids = array();
        //统计计划保养次数
        foreach ($patrolArr as $k=>$v){
            if(in_array($assnum,$v)){
                $ids[] = $k;
            }
        }
        if(!$ids){
            return array();
        }
        //根据partid查询计划信息
        $lists = $this->DB_get_all('patrol_plans','patrid,patrol_name,patrol_level,total_cycle,current_cycle',array('patrid'=>array('in',$ids)));
        //查询计划执行情况
        $fields = "A.execid,B.cycid,B.patrid,B.period,A.assetnum,A.finish_time,A.status,A.execute_user,B.patrid,B.patrol_num,B.cycle_start_date,B.cycle_end_date";
        $join[0] = " LEFT JOIN sb_patrol_plans_cycle as B on A.cycid = B.cycid ";
        $where['A.assetnum'] = $assnum;
        $where['A.status'] = 2;
        $where['B.patrid'] = array('in',$ids);
        $exec = $this->DB_get_all_join('patrol_execute','A',$fields,$join,$where,'','B.patrid asc,B.period asc','');
        foreach ($exec as $k=>$v){
            switch ($v['status']) {
                case C('MAINTAIN_EXECUTION'):
                    $statusName = C('MAINTAIN_EXECUTION_NAME');
                    break;
                case C('MAINTAIN_PATROL'):
                    $statusName = C('MAINTAIN_PATROL_NAME');
                    break;
                case C('MAINTAIN_COMPLETE'):
                    $statusName = C('MAINTAIN_COMPLETE_NAME');
                    break;
                default:
                    $statusName = C('MAINTAIN_EXECUTION_NAME');
                    break;
            }
            $exec[$k]['status'] = (int)$v['status'];
            $exec[$k]['overDate'] = date('Y-m-d H:i',strtotime($v['finish_time']));
            $exec[$k]['statusName'] = $statusName;
            foreach ($lists as $kk=>$vv){
                if($v['patrid'] == $vv['patrid']){
                    $exec[$k]['patrol_name'] = $vv['patrol_name'];
                    $exec[$k]['total_cycle'] = $vv['total_cycle'];
                    $exec[$k]['patrol_level'] = $vv['patrol_level'];
                }
            }
        }
        return $exec;
    }

    /*
     * 更新部门缓存
     */
    public function updateDepartmentCache()
    {
        //更新部门缓存信息
        $departarr = $this->DB_get_all('department', '', '', '', '', '');
        $departname = array();
        if (is_array($departarr) && $departarr) {
            foreach ($departarr as $v) {
                $departname[$v['departid']]['department'] = $v['department'];
                $departname[$v['departid']]['address'] = $v['address'];
                $departname[$v['departid']]['departrespon'] = $v['departrespon'];
                $departname[$v['departid']]['assetsrespon'] = $v['assetsrespon'];
                $departname[$v['departid']]['departtel'] = $v['departtel'];
                $departname[$v['departid']]['assetsprice'] = $v['assetsprice'];
            }
        }
        $dedata['departname'] = ArrayToString($departname);
        made_web_array(APP_PATH . '/Common/cache/department.cache.php', $dedata);
    }

    /**
     * Notes: 根据搜索条件获取资产概况
     * @param $departids array 要搜索的部门数据，如为空，则搜索全部部门
     * @param $endTime int 添加时间末
     * @param $priceMin float 总价区间始
     * @param $priceMax float 总价区间末
     * @return array
     */
    public function getDepartmentAssetsNum($departids, $hospital_id)
    {
        $where['A.is_delete'] = C('NO_STATUS');
        $where['A.hospital_id'] = array('IN', $hospital_id);
        if ($departids) {
            $where['A.departid'] = array('IN', $departids);
            $fields = "A.departid,A.department,count(B.assid) as totalNum,group_concat(B.assnum) as assnums,group_concat(B.assid) as assids";
            $join = " LEFT JOIN sb_assets_info AS B ON A.departid = B.departid";
            $lists = $this->DB_get_all_join('department', 'A', $fields, $join, $where, 'departid', 'departid asc', '');
        } else {
            $fields = "A.departid,A.department,count(sb_assets_info.assid) as totalNum,group_concat(sb_assets_info.assnum) as assnums,group_concat(sb_assets_info.assid) as assids";
            $join[0] = " LEFT JOIN __ASSETS_INFO__ ON A.departid = __ASSETS_INFO__.departid";
            $lists = $this->DB_get_all_join('department', 'A', $fields, $join, $where, 'departid', 'departid asc', '');
        }
        return $lists;
    }

    /**
     * Notes: 获取待入库临时表科室
     * @return mixed
     */
    public function getWatingUploadDeparts()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'asc';
        $sort = I('post.sort') ? I('post.sort') : 'hospital_code';
        $where['adduser'] = session('username');
        $where['is_save'] = 0;//获取未上传的数据
        $hospital_id = session('current_hospitalid');
        //查询当前用户所在的医院代码
        $code = $this->DB_get_one('hospital', 'hospital_code', array('hospital_id' => $hospital_id, 'is_delete' => 0));
        $where['hospital_code'] = $code['hospital_code'];
        $hospital_code = $code['hospital_code'];
        $departmentModel = new DepartmentModel();
        $total = $this->DB_get_count('department_upload_temp', $where);
        //查询上次未完成保存的数据
        $departs = $this->DB_get_all('department_upload_temp', '*', $where, '', $sort . ' ' . $order, $offset . ',' . $limit);
        if ($departs) {
            //查询数据库已有诊所信息
            $alldepar = $this->DB_get_all('department', 'departnum,department', array('hospital_id' => $hospital_id, 'is_delete' => 0), '', '');
            $dnum = $dment = array();
            //诊所
            foreach ($alldepar as $k => $v) {
                $dnum[] = $v['departnum'];
                $dment[] = $v['department'];
            }
            //判断待上传数据是否合法
            foreach ($departs as $k => $v) {
                if ($v['hospital_code'] != $hospital_code) {
                    //医院代码不存在或该用户没有权限上传该医院的诊所数据
                    $departs[$k]['hospital_code'] = '<span style="color:red;">' . $v['hospital_code'] . '</span>';
                }
                if (in_array($v['departnum'], $dnum)) {
                    //诊所编码已存在
                    $departs[$k]['departnum'] = '<span style="color:red;">' . $v['departnum'] . '</span>';
                }
                //判断诊所编码规则是否符合系统设置要求
                $checknumres = $departmentModel->checkDepartNum($v['departnum']);
                if ($checknumres['status'] == -1) {
                    $departs[$k]['departnum'] = '<span style="color:red;">' . $v['departnum'] . '</span>';
                }
                if (in_array($v['department'], $dment)) {
                    //诊所名称已存在
                    $departs[$k]['department'] = '<span style="color:red;">' . $v['department'] . '</span>';
                }
                $departs[$k]['operation'] = '<button style="color:;" class="layui-btn layui-btn-xs layui-btn-danger" lay-event="delTmpDeparts" data-url="/A/IntegratedSetting/batchAddDepartment.html" data-id="' . $v['tempid'] . '" data-type="delTmpDeparts">删除</button>';
            }
        }
        $departs = getNoLimitTree($departs, 'departnum', '', 'parent_departnum');
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $departs;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 更新科室临时表数据
     */
    public function updateTempData()
    {
        $tempid = I('POST.tempid');
        unset($_POST['tempid']);
        unset($_POST['type']);
        $newdata = '';
        $departmentModel = new DepartmentModel();
        foreach ($_POST as $k => $v) {
            $data[$k] = htmlspecialchars(addslashes(trim($v)));
            if ($k == 'hospital_code') {
                //查询当前用户所在的医院代码
                $code = $this->DB_get_one('hospital', 'hospital_code', array('hospital_id' => session('current_hospitalid'), 'is_delete' => 0));
                $existsCode = explode(',', $code['hospital_code']);
                if (!in_array($data[$k], $existsCode)) {
                    return array('status' => -1, 'msg' => '系统中不存在医院代码为 ' . $data[$k] . ' 的医院或你无权为该医院添加科室！');
                }
                $newdata = $data[$k];
            } elseif ($k == 'departnum') {
                //查询是否已存在重复的科室编码
                $find = $this->DB_get_one('department', 'departnum', array('departnum' => $data[$k]));
                if ($find['departnum']) {
                    return array('status' => -1, 'msg' => '系统中已存在编码为 ' . $data[$k] . ' 的科室！');
                }
                //判断部门编码规则是否符合系统设置要求
                $checknumres = $departmentModel->checkDepartNum($data[$k]);
                if ($checknumres['status'] == -1) {
                    return array('status' => -1, 'msg' => '编码规则不符合系统设置要求！');
                }
                $newdata = $data[$k];
            } elseif ($k == 'department') {
                //查询是否已存在重复的科室名称
                $find = $this->DB_get_one('department', 'department', array('department' => $data[$k]));
                if ($find['department']) {
                    return array('status' => -1, 'msg' => '系统中已存在名称为 ' . $data[$k] . ' 的科室！');
                }
            } else {
                $newdata = $data[$k];
            }
        }
        $data['edituser'] = session('username');
        $data['editdate'] = getHandleDate(time());
        $res = $this->updateData('department_upload_temp', $data, array('tempid' => $tempid));
        if ($res) {
            return array('status' => 1, 'msg' => '修改成功！', 'newdata' => $newdata);
        } else {
            return array('status' => -1, 'msg' => '修改失败！');
        }
    }

    /**
     * Notes: 删除科室临时表数据
     */
    public function delTempData()
    {
        $tempid = trim(I('POST.tempid'), ',');
        $tempArr = explode(',', $tempid);
        $res = $this->deleteData('department_upload_temp', array('tempid' => array('in', $tempArr)));
        if ($res) {
            return array('status' => 1, 'msg' => '删除成功！');
        } else {
            return array('status' => -1, 'msg' => '删除失败！');
        }
    }

    /**
     * Notes: 上传科室数据
     */
    public function uploadData()
    {
        if (empty($_FILES)) {
            return array('status' => -1, 'msg' => '请上传文件');
        }
        $uploadConfig = array(
            'maxSize' => 3145728,
            'rootPath' => './Public/',
            'savePath' => 'uploads/',
            'saveName' => array('uniqid', ''),
            'exts' => array('xlsx', 'xls', 'xlsm'),
            'autoSub' => true,
            'subName' => array('date', 'Ymd'),
        );
        $upload = new \Think\Upload($uploadConfig);
        $info = $upload->upload();
        if (!$info) {
            return array('status' => -1, 'msg' => '上传出错，请检查相关文件夹权限');
        }
        vendor("PHPExcel.PHPExcel");
        $filePath = $upload->rootPath . $info['file']['savepath'] . $info['file']['savename'];
        if (empty($filePath) or !file_exists($filePath)) {
            die('file not exists');
        }
        $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                return array('status' => -1, 'msg' => '文件格式错误');
            }
        }
        $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
        $currentSheet = $PHPExcel->getSheet(0);  //**读取excel文件中的指定工作表*/
        $allColumn = $currentSheet->getHighestColumn(); //**取得最大的列号*/
        $allRow = $currentSheet->getHighestRow();       //**取得一共有多少行*/
        $data = array();
        $cellname = array('A' => 'hospital_code', 'B' => 'departnum', 'C' => 'parent_departnum', 'D' => 'department', 'E' => 'address', 'F' => 'departrespon', 'G' => 'assetsrespon', 'H' => 'departtel');
        for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for ($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if ($cell instanceof \PHPExcel_RichText) { //富文本转换字符串
                    $cell = $cell->__toString();
                }
                $data[$rowIndex - 2][$cellname[$colIndex]] = $cell ? $cell : '';
            }
        }
        if (!$data) {
            return array('status' => -1, 'msg' => '导入数据失败');
        }
        //查询当前用户所在的医院代码
        $code = $this->DB_get_one('hospital', 'hospital_code', array('hospital_id' => session('current_hospitalid'), 'is_delete' => 0));
        //对数据进行重复性验证
        foreach ($data as $k => $v) {
            foreach ($v as $k1 => $v1) {
                if ($k1 == 'hospital_code') {
                    if ($data[$k][$k1] != $code['hospital_code']) {
                        unset($data[$k]);
                        break;
                    }
                } elseif ($k1 == 'departnum') {
                    $res = $this->DB_get_one('department_upload_temp', 'tempid', array('departnum' => $v1, 'is_delete' => 0, 'hospital_code' => array('eq', $code['hospital_code'])));
                    if ($res) {
                        unset($data[$k]);
                        break;
                    }
                } elseif ($k1 == 'department') {
                    $res = $this->DB_get_one('department_upload_temp', 'tempid', array('department' => $v1, 'is_delete' => 0, 'hospital_code' => array('eq', $code['hospital_code'])));
                    if ($res) {
                        unset($data[$k]);
                        break;
                    }
                }
            }
        }
        if (!$data) {
            //上传的文件数据和临时表中已存在数据重复
            return array('status' => -1, 'msg' => '没有新数据被上传！请检查文件数据是否已上传过，或是否符合要求！');
        }
        //保存数据到临时表
        $insertData = array();
        $num = 0;
        foreach ($data as $k => $v) {
            if ($num < $this->len) {
                //$this->len条存一次数据到数据库
                $tempid = getRandomId();
                $insertData[$num]['tempid'] = $tempid;
                $insertData[$num]['adduser'] = session('username');
                $insertData[$num]['adddtime'] = getHandleDate(time());
                $insertData[$num]['is_save'] = 0;
                foreach ($v as $k1 => $v1) {
                    $insertData[$num][$k1] = $v1;
                }
                $num++;
            }
            if ($num == $this->len) {
                //插入数据
                $this->insertDataALL('department_upload_temp', $insertData);
                //重置数据
                $num = 0;
                $insertData = array();
            }
        }
        if ($insertData) {
            $this->insertDataALL('department_upload_temp', $insertData);
        }
        return array('status' => 1, 'msg' => '上传数据成功，请核准后再保存！');
    }

    /*
     * 批量新增科室
     * return array
     */
    public function batchAddDeparts()
    {
        $tempid = trim(I('POST.tempid'), ',');
        $tempArr = explode(',', $tempid);
        $hospital_id = session('current_hospitalid');
        //查询当前用户所在的医院代码
        $code = $this->DB_get_one('hospital', 'hospital_code', array('hospital_id' => $hospital_id, 'is_delete' => 0));
        $hospital_code = $code['hospital_code'];
        //查询所有现有的科室编码和名称
        $department = $this->DB_get_all('department', 'departid,departnum,department', array('hospital_id' => $hospital_id, 'is_delete' => 0), '', 'departid asc', '');
        $departnum = $departname = array();
        foreach ($department as $k => $v) {
            $departnum[] = $v['departnum'];
            $departname[] = $v['department'];
        }
        $num = 0;
        $saveTempidArr = array();
        foreach ($tempArr as $k => $v) {
            //按每次最多不超过$this->len条的数据获取临时表数据进行保存操作
            if ($num < $this->len) {
                $saveTempidArr[] = $v;
                $num++;
            }
            if ($num == $this->len) {
                //进行一次设备入库操作
                $res = $this->departStorage($saveTempidArr, $departnum, $departname, $hospital_id, $hospital_code);
                //重置
                $num = 0;
                $saveTempidArr = array();
            }
        }
        if ($saveTempidArr) {
            $res = $this->departStorage($saveTempidArr, $departnum, $departname, $hospital_id, $hospital_code);
        }
        $msg = $res ? '保存数据成功！' : '暂无数据保存！';
        return array('status' => 1, 'msg' => $msg);
    }

    /**
     * Notes: 科室批量入库方法
     * @param $saveTempidArr array 要保存的临时表设备ID
     * @param $departnum array 已有科室的departnum
     * @param $departname array 已有科室的department
     * @return array
     */
    public function departStorage($saveTempidArr, $departnum, $departname, $hospital_id, $hospital_code)
    {
        $data = $this->DB_get_all('department_upload_temp', '*', array('tempid' => array('in', $saveTempidArr), 'is_save' => 0));

        //过滤掉必填字段为空的数据
        $need = array('departnum', 'department', 'address', 'departrespon', 'assetsrespon', 'departtel');
        foreach ($data as $k => $v) {
            foreach ($v as $k1 => $v1) {
                if (in_array($k1, $need) && is_null($v1)) {
                    //过滤掉必填字段没填写的数据
                    unset($data[$k]);
                }
            }
        }
        $departmentModel = new DepartmentModel();
        foreach ($data as $k => $v) {
            if ($v['hospital_code'] != $hospital_code) {
                //过滤掉不存在的医院代码数据或用户没权限添加的数据
                unset($data[$k]);
            }
            if (in_array($v['departnum'], $departnum)) {
                //过滤掉与系统诊所编码重复的数据
                unset($data[$k]);
            }
            //判断诊所编码规则是否符合系统设置要求
            $checknumres = $departmentModel->checkDepartNum($v['departnum']);
            if ($checknumres['status'] == -1) {
                unset($data[$k]);
            }
            if (in_array($v['department'], $departname)) {
                //过滤掉与系统设诊所名称重复的数据
                unset($data[$k]);
            }
        }

        $new_id = 0;
        $insertData = array();
        //组织数据插入正式表
        $data = getNoLimitTree($data, 'departnum', '', 'parent_departnum');
        $son = [];
        foreach ($data as $k => $v) {
            // 原来重名不能导进去
//            if (in_array($v['departnum'], $departnum) || in_array($v['department'], $departname)) {
            // 改重名也能导进去 防止一级二级科室重名的情况
            if (in_array($v['departnum'], $departnum) || (in_array($v['department'], $departname) && $v['parent_departnum'] == 0)) {
                continue;
            } else {
                if ($v['parent_departnum'] != 0) {
                    $son[] = $v;
                    continue;
                }
                $insertData['hospital_id'] = $hospital_id;
                $insertData['departnum'] = $v['departnum'];
                $insertData['department'] = $v['department'];
                $insertData['address'] = $v['address'];
                $insertData['departrespon'] = $v['departrespon'];
                $insertData['assetsrespon'] = $v['assetsrespon'];
                $insertData['departtel'] = $v['departtel'];
                $insertData['adduser'] = session('username');
                $insertData['addtime'] = time();
                $new_id = $this->insertData('department', $insertData);
                if ($new_id) {
                    $departnum[] = $v['departnum'];
                    $departname[] = $v['department'];
                    //修改临时表状态未已上传
                    $this->updateData('department_upload_temp', array('is_save' => 1, 'edituser' => session('username'), 'editdate' => getHandleDate(time())), array('tempid' => $v['tempid']));
                }
            }
        }
        foreach ($son as $k => $v) {
            $insertData['hospital_id'] = $hospital_id;
            $insertData['departnum'] = $v['departnum'];
            $insertData['department'] = $v['department'];
            $insertData['address'] = $v['address'];
            $insertData['departrespon'] = $v['departrespon'];
            $insertData['assetsrespon'] = $v['assetsrespon'];
            $insertData['departtel'] = $v['departtel'];
            $insertData['adduser'] = session('username');
            $insertData['addtime'] = time();
            //有上级编码的，先查询数据库获取父级ID
            $par = $this->DB_get_one('department', 'departid', ['departnum' => $v['parent_departnum']]);
            if ($par['departid']) {
                $insertData['parentid'] = $par['departid'];
            } else {
                //没有则退出
                continue;
            }
            $new_id = $this->insertData('department', $insertData);
            if ($new_id) {
                $departnum[] = $v['departnum'];
                $departname[] = $v['department'];
                //修改临时表状态未已上传
                $this->updateData('department_upload_temp', array('is_save' => 1, 'edituser' => session('username'), 'editdate' => getHandleDate(time())), array('tempid' => $v['tempid']));
            }
        }
        return $new_id;
    }

    /**
     * 获取所有的科室
     */
    public function getAllDepartments()
    {
        return $this->DB_get_all('department', '*', array('1'));
    }

    /**
     * Notes: 获取科室负责人
     */
    public function getDepartmentsRespon($where)
    {
        return $this->DB_get_all('department', '*', $where, 'departrespon');
    }

    public function getChildDepartment()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        //搜索名称
        $department = I('post.departName');
        $departid = I('post.departid');
        if (!$departid) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $whereOR['departid'] = $departid;
        $whereOR['parentid'] = $departid;
        $whereOR['_logic'] = 'or';
        $where['_complex'] = $whereOR;
        $where['is_delete'] = C('NO_STATUS');
        if ($department) {
            //分类名称搜索
            $where['department'] = array('like', '%' . $department . '%');
        }
        $order = I('POST.order');
        $sort = I('POST.sort');
        if (!$sort) {
            $sort = 'departnum';
        }
        if (!$order) {
            $order = 'asc';
        }
        //查询当前用户是否有权限进行修改科室
        $editDepartment = get_menu('BaseSetting', 'IntegratedSetting', 'editDepartment');
        //查询当前用户是否有权限进行删除科室
        $deleteDepartment = get_menu('BaseSetting', 'IntegratedSetting', 'deleteDepartment');
        //查询当前用户是否有添加用户权限
        $adduser = get_menu('BaseSetting', 'User', 'addUser');
        //查询当前用户是否有添加审批流程权限
        $process = get_menu('BaseSetting', 'ApproveSetting', 'addProcess');
        $total = $this->DB_get_count('department', $where);
        //查出子类
        $department = $this->DB_get_all('department', '', $where, '', $sort . ' ' . $order);
        foreach ($department as $k => $v) {
            $html = '<div class="layui-btn-group">';
            if ($editDepartment) {
                $html .= $this->returnButtonLink($editDepartment['actionname'], $editDepartment['actionurl'], 'layui-btn layui-btn-xs layui-btn-warm', '', 'lay-event = editChildDepartment');
            }
            if ($deleteDepartment) {
                $html .= $this->returnButtonLink($deleteDepartment['actionname'], $deleteDepartment['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'lay-event = deleteChildDepartment');
            }
            if ($adduser || $process) {
                $html .= $this->returnButtonLink('设置审批人', C('ADMIN_NAME') . '/IntegratedSetting/department', 'layui-btn layui-btn-xs layui-btn-normal', '', 'lay-event = manager');
            }
            $html .= '</div>';
            $department[$k]['operation'] = $html;
            if ($v['parentid'] == 0) {
                $department[$k]['parentDepartment'] = $v['department'];
                unset($department[$k]['department']);
                unset($department[$k]['operation']);
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $department;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 生成二维码图片
     * @return string
     */
    public function createLabelCode($file_name, $code_str)
    {
        Vendor('phpqrcode.phpqrcode');
        $QRcode = new \QRcode ();
        $value = $code_str;//二维码内容
        //二维码文件保存地址
        $savePath = './Public/uploads/department/qrcode/';
        if (!file_exists($savePath)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($savePath, 0777, true);
        }
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 5;//生成图片大小
        //文件名
        $filename = $file_name . '.png';
        //生成二维码,第二个参数为二维码保存路径
        $QRcode::png($value, $savePath . $filename, $errorCorrectionLevel, $matrixPointSize, 2, true);
        if (file_exists($savePath . $filename)) {
            return $savePath . $filename;
        } else {
            return false;
        }
    }
}
