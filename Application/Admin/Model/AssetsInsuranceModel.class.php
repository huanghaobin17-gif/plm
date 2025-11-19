<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/25
 * Time: 9:44
 */

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Think\Model;
use Think\Model\RelationModel;

class AssetsInsuranceModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'assets_insurance';
    protected $MODULE = 'Assets';

    /**
     * 维保资产列表
     * @return array
     */
    public function getInsuranceList()
    {
        $departid = session('departid');
        $assets = I('POST.assName');
        $assetsNum = I('POST.assNum');
        $assetsDep = I('POST.department');
        $buyStartDate = I('POST.buyStartDate');
        $buyEndDate = I('POST.buyEndDate');
        $startDate = I('POST.startDate');
        $endDate = I('POST.endDate');
        $insurance = I('POST.insurance');
        $guarantee = I('POST.guarantee');

        $where['A.status'][0] = 'NOTIN';
        $where['A.status'][1][] = C('ASSETS_STATUS_SCRAP');
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        if (!session('isSuper')) {
            if (!$departid) {
                $departid = 0;
            }
            $where['A.departid'][] = array('IN', $departid);
        }

        $where['A.hospital_id'] = session('current_hospitalid');

        //设备名称搜索
        if ($assets) {
            $where['A.assets'] = array('LIKE', '%' . $assets . '%');
        }
        //资产编码搜索
        if ($assetsNum) {
            $where['A.assnum'] = array('LIKE', '%' . $assetsNum . '%');
        }
        //部门搜索
        if ($assetsDep) {
            $where['A.departid'][] = array('IN', $assetsDep);
        }
        //维保时间--开始
        if ($startDate) {
            $where['B.startdate'][] = array('EGT', strtotime($startDate) - 1);
        }
        //维保时间--结束
        if ($endDate) {
            $where['B.overdate'][] = array('ELT', strtotime($endDate) + 24 * 3600);
        }
        //设备购入时间--开始
        if ($buyStartDate) {
            $where['A.factorydate'][] = array('EGT', strtotime($buyStartDate) - 1);

        }
        //设备购入时间--结束
        if ($buyEndDate) {
            $where['A.factorydate'][] = array('ELT', strtotime($buyEndDate) + 24 * 3600);
        }

        $assetsWhere['guarantee_date'] = array('EGT', getHandleTime(time()));
        $assetsWhere['is_delete'] = C('NO_STATUS');
        $assetsData = $this->DB_get_all('assets_info', 'assid', $assetsWhere);
        $assid = [];
        foreach ($assetsData as &$one) {
            $assid [] = $one['assid'];
        }
        if ($guarantee == 1) {
            //搜索保修期内的
            if($assid){
                $where['A.assid'][] = array('IN', $assid);
            }else{
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        } else {
            if($assid){
                $where['A.assid'][] = array('NOT IN', $assid);
            }
        }
        $data = [];
        $where['A.is_delete'] = C('NO_STATUS');
        if ($insurance == '-1') {
            //全部
            $data = $this->getAllInsuranceList($where);
        } else {
            if ($insurance == '1') {
                //参保
                $data = $this->getUseInsuranceList($where);
            } elseif ($insurance == 0) {
                //脱保
                $data = $this->getDePaulInsuranceList($where);
            }
        }
        //var_dump($data);exit;
        if (!$data['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $doRenewalMenu = get_menu('Assets', 'Lookup', 'doRenewal');
        foreach ($data['rows'] as &$one) {
            $one['factorydate'] = HandleEmptyNull($one['factorydate']);
            $one['guarantee_date'] = HandleEmptyNull($one['guarantee_date']);
            $one['department_name'] = $departname[$one['departid']]['department'];
            if ($one['startdate'] && $one['overdate']) {
                $one['insuranceDate'] = getHandleTime($one['startdate']) . '至' . getHandleTime($one['overdate']);
            } else {
                $one['insuranceDate'] = '-';
            }

            if ($one['guarantee_date'] >= getHandleTime(time())) {
                $one['nature'] = C('FACTORY_WARRANTY_NAME');//原厂保修
                if(!$one['insurid']){
                    $one['is_canbao'] = '<span style="color:#FF5722;">未参保</span>';
                }else{
                    $one['is_canbao'] = '<span style="color:#009688;">已参保</span>';
                }
            } else {
                if ($one['status'] == C('INSURANCE_STATUS_USE')) {
                    $one['nature'] = $this->formatNature($one['nature']);
                    $one['is_canbao'] = '<span style="color:#009688;">已参保</span>';
                } else {
                    $one['nature'] = C('INSURANCE_STATUS_DE_PAUL_NAME');
                    $one['is_canbao'] = '<span style="color:#FF5722;">未参保</span>';
                }
            }
            $one['operation'] = '<div class="layui-btn-group">';
            $one['operation'] .= $this->returnListLink('设备详情', C('ADMIN_NAME').'/Lookup/showAssets', 'showAssets', C('BTN_CURRENCY') . ' layui-btn-primary') . '</div>';
            if ($doRenewalMenu) {
                $color = '';
                if ($one['nature'] == C('INSURANCE_STATUS_DE_PAUL_NAME')) {
                    //脱保显示脱保 并且标红
                    $linkName = C('INSURANCE_STATUS_DE_PAUL_NAME');
                    $color = '  layui-btn-danger';
                } else {
                    $linkName = $doRenewalMenu['actionname'];
                }
                $one['operation'] .= $this->returnListLink($linkName, $doRenewalMenu['actionurl'], 'doRenewal', C('BTN_CURRENCY') . $color);
            }
        }
        $result["total"] = $data['total'];
        $result["offset"] = $data['offset'];
        $result["limit"] = $data["limit"];
        $result["rows"] = $data['rows'];
        $result['code'] = 200;
        return $result;
    }

    /*
     * 获取第三方维保公司列表
     * */
    public function getRepairOffList()
    {
        $where['is_insurance'] = ['EQ', C('YES_STATUS')];
        $where['status'] = ['EQ', C('OPEN_STATUS')];
        $data = $this->DB_get_all('offline_suppliers', 'sup_name,salesman_name,salesman_phone,olsid', $where);
        if ($data) {
            return array('status' => 1, 'msg' => '获取成功', 'result' => $data);
        } else {
            return array('status' => -1, 'msg' => '无对应类型的厂商,请先补充');
        }
    }

    /**
     * 获取全部的维保信息
     * @param string $where 条件
     * @return array
     */
    public function getAllInsuranceList($where)
    {
        $limit = I('POST.limit') ? I('POST.limit') : 10;
        $page = I('POST.page') ? I('POST.page') : 1;
        $offset = ($page - 1) * $limit;
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        $join[0] = 'LEFT JOIN sb_assets_insurance AS B ON B.assid=A.assid AND B.overdate=(SELECT MAX(overdate) FROM sb_assets_insurance WHERE assid=A.assid )';
        $fileds = 'A.assid,A.assets,A.assnum,A.departid,A.factorydate,A.insuredsum,A.guarantee_date,B.startdate,B.overdate,B.company,
        B.contacts,B.telephone,B.content,B.insurid,B.nature,B.status';
        $total = $this->DB_get_count_join('assets_info', 'A', $join, $where);
        $asArr = $this->DB_get_all_join('assets_info', 'A', $fileds, $join, $where, 'A.assid', 'A.assid', $offset . "," . $limit);

        $DePaulId = '';//脱保的数据ID
        foreach ($asArr as &$one) {
            if (!$one['company']) {
                $DePaulId .= ',' . $one['assid'];
            }
        }
        $DePaulId = trim($DePaulId, ',');
        //只要是当前时间没有维保数据就直接当做脱保 不考虑未开始的维保数据
        $join['0'] = 'sb_assets_insurance AS B ON B.assid=A.assid AND B.overdate=(SELECT MAX(overdate) FROM sb_assets_insurance WHERE assid=A.assid AND status=' . C('INSURANCE_STATUS_DE_PAUL') . ')';
        if ($DePaulId) {
            $where['A.assid'][] = array('IN', $DePaulId);
        } else {
            $where = 1;
        }
        $DePaulArr = $this->DB_get_all_join('assets_info', 'A', $fileds, $join, $where, 'A.assid');
        foreach ($asArr as &$one) {
            foreach ($DePaulArr as &$two) {
                if ($two['assid'] == $one['assid']) {
                    $one = $two;
                }
            }
        }
        $result["total"] = $total;
        $result["rows"] = $asArr;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        return $result;
    }

    /**
     * 获取脱保的数据
     * @param string $where 条件
     * @return array
     */
    public function getDePaulInsuranceList($where)
    {
        $limit = I('POST.limit') ? I('POST.limit') : 10;
        $page = I('POST.page') ? I('POST.page') : 1;
        $offset = ($page - 1) * $limit;
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        //获取要排除的保内数据
        $useWhere['status'] = C('INSURANCE_STATUS_USE');
        $useData = $this->DB_get_all('assets_insurance', 'assid', $useWhere);
        if ($useData) {
            $assId = '';
            foreach ($useData as &$one) {
                $assId .= ',' . $one['assid'];
            }
            $assId = trim($assId, ',');
            $where['A.assid'][] = array('NOT IN', $assId);
        }
        $join[0] = 'LEFT JOIN sb_assets_insurance AS B ON B.assid=A.assid AND B.overdate=(SELECT MAX(overdate) FROM sb_assets_insurance WHERE assid=A.assid AND status!=' . C('INSURANCE_STATUS_USE') . ')';
        $fileds = 'A.assid,A.assets,A.assnum,A.departid,A.factorydate,A.insuredsum,A.guarantee_date,B.startdate,B.overdate,B.company,
        B.contacts,B.telephone,B.content,B.insurid,B.nature,B.status';
        $total = $this->DB_get_count_join('assets_info', 'A', $join, $where);
        $asArr = $this->DB_get_all_join('assets_info', 'A', $fileds, $join, $where, 'A.assid', 'A.assid asc', $offset . "," . $limit);
        $result["total"] = $total;
        $result["rows"] = $asArr;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        return $result;
    }


    /**
     * 获取保内维保信息
     * @param string $where 条件
     * @return array
     */
    public function getUseInsuranceList($where)
    {
        $where['B.status'] = array('EQ', C('INSURANCE_STATUS_USE'));
        $limit = I('POST.limit') ? I('POST.limit') : 10;
        $page = I('POST.page') ? I('POST.page') : 1;
        $offset = ($page - 1) * $limit;
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        $join[0] = 'LEFT JOIN sb_assets_insurance AS B ON B.assid=A.assid AND B.overdate=(SELECT MAX(overdate) FROM sb_assets_insurance WHERE assid=A.assid AND status=' . C('INSURANCE_STATUS_USE') . ')';
        $fileds = 'A.assid,A.assets,A.assnum,A.departid,A.factorydate,A.insuredsum,A.guarantee_date,B.startdate,B.overdate,B.company,
        B.contacts,B.telephone,B.content,B.insurid,B.nature,B.status';
        $total = $this->DB_get_count_join('assets_info', 'A', $join, $where);
        $asArr = $this->DB_get_all_join('assets_info', 'A', $fileds, $join, $where, 'A.assid', 'B.overdate desc', $offset . "," . $limit);
        $result["total"] = $total;
        $result["rows"] = $asArr;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        return $result;
    }

    /**
     * 获取设备基础信息
     * @params int $assid 设备ID
     * @return array
     * */
    public function getAssets($assid)
    {
        if (!$assid) {
            die(json_encode(array('status' => -999, 'msg' => '非法操作')));
        }
        $where = "A.assid=$assid";
        $join[0] = 'LEFT JOIN sb_assets_factory AS B ON B.afid=A.afid';
        $fileds = 'A.afid,A.assnum,A.model,A.assets,B.factory,A.departid,A.assid,A.factorydate,A.catid,A.assorignum,A.guarantee_date';
        //查询设备详情
        $asArr = $this->DB_get_one_join('assets_info', 'A', $fileds, $join, $where);
        $departname = [];
        $catname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        $asArr['category'] = $catname[$asArr['catid']]['category'];
        $asArr['guarantee_date'] = HandleEmptyNull($asArr['guarantee_date']);
        $asArr['department_name'] = $departname[$asArr['departid']]['department'];
        if (!$asArr['assorignum']) {
            $asArr['assorignum'] = '--';
        }
        return $asArr;
    }


    //获取设备维保列表
    public function getAssetsInsuranceList()
    {
        $assid = I('POST.assid');
        if (!$assid) {
            die(json_encode(array('status' => -999, 'msg' => '非法操作')));
        }
        $where = "assid=$assid";
        $fileds = 'adduser,adddate,edituser,editdate,content,remark,
        cost,insurid,fileid,nature,buydate,startdate,overdate,company,contacts,telephone,content';
        $insurance = $this->DB_get_all('assets_insurance', $fileds, $where, '', 'overdate asc');
        if (!$insurance) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $menuData = get_menu($this->MODULE, 'Lookup', 'doRenewal');
        foreach ($insurance as &$one) {
            $one['nature'] = $this->formatNature($one['nature']);
            $one['buydate'] = getHandleTime($one['buydate']);
            $one['adddate'] = getHandleTime($one['adddate']);
            $one['editdate'] = getHandleTime($one['editdate']);
            $one['term'] = getHandleTime($one['startdate']) . '至' . getHandleTime($one['overdate']);
            if ($one['overdate'] > time() && $menuData) {
                $one['operation'] = $this->returnListLink('修改联系人', '', 'save_insurance', C('BTN_CURRENCY'));
            } else {
                $one['operation'] = '--';
            }
            $file = $this->DB_get_all('assets_insurance_file', 'name,url,fileid', array('insurid' => $one['insurid']));
            $file_data = '';
            foreach ($file as &$fileValue) {
                $one['file_url'] = $fileValue['url'];
                $suffix = substr(strrchr($fileValue['name'], '.'), 1);
                $vue_file = [];
                $vue_file['file_url'] = $fileValue['url'];
                $vue_file['file_name'] = $fileValue['name'];
                $vue_file['file_type'] = $suffix;
                $one['file_list'][] = $vue_file;
                $supplement = 'data-path="' . $fileValue['url'] . '" data-name="' . $fileValue['name'] . '"';
                $string = '';
                if ($suffix == 'doc' or $suffix == 'docx') {
                    $string .= ' data-showFile=false';
                } else {
                    $string .= ' data-showFile=true';
                }
                $file_data .= $this->returnListLink($fileValue['name'], '', '', C('BTN_CURRENCY') . ' layui-btn-warm operationFile', '', $supplement . $string);
            }
            $one['file_data'] = trim($file_data, ',');
        }
        $result["rows"] = $insurance;
        $result['code'] = 200;
        return $result;
    }

    //设备维保信息统计
    public function MaintenanceSummary()
    {
        $tab = I('POST.tab');
        $notData['msg'] = '暂无相关数据';
        $notData['code'] = 400;
        switch ($tab) {
            case 'Supplier':
                $resulet = $this->MaintenanceSummarySupplier();
                break;
            case 'Term':
                $resulet = $this->MaintenanceSummaryTerm();
                break;
            case 'cost':
                $resulet = $this->MaintenanceSummaryCost();
                break;
            case 'depart':
                $resulet = $this->MaintenanceSummaryDepart();
                break;
            case 'assets':
                $resulet = $this->MaintenanceSummaryAssets();
                break;
            default :
                return $notData;
        }
        return $resulet;
    }


    //按照维保供应商统计维保报表
    public function MaintenanceSummarySupplier()
    {
        $where['startdate'] = array('ELT', time());
        $where['overdate'] = array('EGT', time());
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $total = $this->DB_get_count('assets_insurance', $where, 'company');
        $joinWhere['I.startdate'] = array('ELT', time());
        $joinWhere['I.overdate'] = array('EGT', time());
        $joinWhere['A.is_delete'] = C('NO_STATUS');
        $fields = 'I.company,GROUP_CONCAT(I.assid) AS assid,GROUP_CONCAT(A.insuredsum) AS insuredsum,GROUP_CONCAT(A.departid) AS departid';
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=I.assid';
        $data = $this->DB_get_all_join('assets_insurance', 'I', $fields, $join, $joinWhere, 'company', '', $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        foreach ($data as &$value) {
            $departArr = explode(",", $value['departid']);
            $departArr = array_unique($departArr);
            $value['departNum'] = count($departArr);
            $insuredsumArr = explode(",", $value['insuredsum']);
            $value['insuredsumNum'] = array_sum($insuredsumArr);
            $value['assidNum'] = substr_count($value['assid'], ',') + 1;
            $result['Bar']['depart'][] = $value['departNum'];
            $result['Bar']['insuredsum'][] = $value['insuredsumNum'];
            $result['Bar']['assid'][] = $value['assidNum'];
            $result['Bar']['company'][] = $value['company'];
        }
        $result["total"] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result["rows"] = $data;
        return $result;
    }

    //按维保期限统计维保报表
    public function MaintenanceSummaryTerm()
    {
        $hospital_id = session('current_hospitalid');
        $LongWhere['I.overdate'] = array(array('GT', strtotime("+1 month")), array('ELT', strtotime("+3 month")));
        $LongWhere['A.hospital_id'] = $hospital_id;
        $MetaphaseWhere['I.overdate'] = array(array('GT', strtotime("+15 day")), array('ELT', strtotime("+1 month")));
        $MetaphaseWhere['A.hospital_id'] = $hospital_id;
        $SoonWhere['I.overdate'] = array(array('GT', getHandleTime(time())), array('ELT', strtotime("+15 day")));
        $SoonWhere['A.hospital_id'] = $hospital_id;
        $fields = 'I.company,GROUP_CONCAT(I.assid) AS assid,GROUP_CONCAT(A.departid) AS departid';
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=I.assid';
        $SoonWhere['A.is_delete'] = C('NO_STATUS');
        $LongWhere['A.is_delete'] = C('NO_STATUS');
        $MetaphaseWhere['A.is_delete'] = C('NO_STATUS');
        $Long = $this->DB_get_all_join('assets_insurance', 'I', $fields, $join, $LongWhere, 'company');
        $Metaphase = $this->DB_get_all_join('assets_insurance', 'I', $fields, $join, $MetaphaseWhere, 'company');
        $Soon = $this->DB_get_all_join('assets_insurance', 'I', $fields, $join, $SoonWhere, 'company');

        $data[0]['LongAssidNum'] = 0;
        $data[0]['LongDepartNum'] = 0;
        $data[0]['LongCount'] = count($Long);
        $i = 0;
        foreach ($Long as &$value) {
            $departArr = explode(",", $value['departid']);
            $departArr = array_unique($departArr);
            $data[0]['LongAssidNum'] += substr_count($value['assid'], ',') + 1;
            $data[0]['LongDepartNum'] += count($departArr);
            $pie['data'][$i]['name'] = '一至三个月到期';
            $pie['data'][$i]['value'] = 3;
            $i++;
        }
        $data[0]['MetaphaseAssidNum'] = 0;
        $data[0]['MetaphaseDepartNum'] = 0;
        $data[0]['MetaphaseCount'] = count($Metaphase);
        foreach ($Metaphase as &$value) {
            $departArr = explode(",", $value['departid']);
            $departArr = array_unique($departArr);
            $data[0]['MetaphaseDepartNum'] += count($departArr);
            $data[0]['MetaphaseAssidNum'] += substr_count($value['assid'], ',') + 1;
        }
        $data[0]['SoonAssidNum'] = 0;
        $data[0]['SoonDepartNum'] = 0;
        $data[0]['SoonCount'] = count($Soon);
        foreach ($Soon as &$value) {
            $departArr = explode(",", $value['departid']);
            $departArr = array_unique($departArr);
            $data[0]['SoonDepartNum'] += count($departArr);
            $data[0]['SoonAssidNum'] += substr_count($value['assid'], ',') + 1;
        }

        $pie['series']['data'][0]['name'] = '相关设备数量';
        $pie['series']['data'][1]['name'] = '相关科室数量';
        $pie['series']['data'][2]['name'] = '相关供应商数量';
        $pie['series']['data'][0]['value'] = $data[0]['LongAssidNum'] + $data[0]['MetaphaseAssidNum'] + $data[0]['SoonAssidNum'];
        $pie['series']['data'][1]['value'] = $data[0]['LongDepartNum'] + $data[0]['MetaphaseDepartNum'] + $data[0]['SoonDepartNum'];
        $pie['series']['data'][2]['value'] = $data[0]['LongCount'] + $data[0]['MetaphaseCount'] + $data[0]['SoonCount'];

        $pie['data'][0]['name'] = '一至三个月到期';
        $pie['data'][0]['value'] = $data[0]['LongAssidNum'];
        $pie['data'][1]['name'] = '一个月内到15天到期';
        $pie['data'][1]['value'] = $data[0]['MetaphaseAssidNum'];
        $pie['data'][2]['name'] = '15天内到期';
        $pie['data'][2]['value'] = $data[0]['SoonAssidNum'];

        $pie['data'][3]['name'] = '一至三个月到期';
        $pie['data'][3]['value'] = $data[0]['LongDepartNum'];
        $pie['data'][4]['name'] = '一个月内到15天到期';
        $pie['data'][4]['value'] = $data[0]['MetaphaseDepartNum'];
        $pie['data'][5]['name'] = '15天内到期';
        $pie['data'][5]['value'] = $data[0]['SoonDepartNum'];

        $pie['data'][6]['name'] = '一至三个月到期';
        $pie['data'][6]['value'] = $data[0]['LongCount'];
        $pie['data'][7]['name'] = '一个月内到15天到期';
        $pie['data'][7]['value'] = $data[0]['MetaphaseCount'];
        $pie['data'][8]['name'] = '15天内到期';
        $pie['data'][8]['value'] = $data[0]['SoonCount'];

        $result["code"] = 200;
        $result["rows"] = $data;
        $result["Pie"] = $pie;
        return $result;

    }

    //按费用统计维保报表
    public function MaintenanceSummaryCost()
    {
        $typeCost = I('POST.typeCost');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $join = 'LEFT JOIN sb_assets_insurance AS I ON I.assid=A.assid';
        $fields = 'A.departid,A.assid,A.assnum,A.model,A.buy_price,A.assets,GROUP_CONCAT(I.cost) AS cost,GROUP_CONCAT(I.status) AS status';
        $where['A.hospital_id'] = session('current_hospitalid');
        $where['A.is_delete'] = C('NO_STATUS');
        $where['A.status'][0] = 'NOT IN';
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');
        //排除已删除科室
        $del_departids = $this->get_delete_departids();
        if($del_departids){
            $where['A.departid'] = array('not in',$del_departids);
        }
        $count = $this->DB_get_count_join('assets_info','A',$join, $where);
        $data = $this->DB_get_all_join('assets_info', 'A', $fields, $join, $where, 'A.assid', 'A.assid', $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $newArr = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $key => $value) {
            //统计科室设备总额
            if ($newArr) {
                foreach ($newArr as $key2 => $value2) {
                    if ($value['departid'] == $value2['departid']) {
                        $newArr[$key2]['sum'] += $value['buy_price'];
                    } else {
                        //不存在 但是避免直接在末尾重复插入，做多一次循环，验证是否已存在
                        $Repeat = false;
                        foreach ($newArr as $key3 => $value3) {
                            if ($value['departid'] == $value3['departid']) {
                                $Repeat = true;
                                break;
                            }
                        }
                        if (!$Repeat) {
                            $newArr[$key]['departid'] = $value['departid'];
                            $newArr[$key]['sum'] = $value['buy_price'];
                        }
                    }
                }
            } else {
                $newArr[$key]['departid'] = $value['departid'];
                $newArr[$key]['sum'] = $value['buy_price'];
            }
        }
        foreach ($data as &$value) {
            foreach ($newArr as &$newValue) {
                if ($value['departid'] == $newValue['departid']) {
                    $value['buy_price_sum'] = $newValue['sum'];
                }
            }
            $value['department'] = $departname[$value['departid']]['department'];
            $cost = explode(",", $value['cost']);
            $value['cost_num'] = array_sum($cost);
            if ($typeCost) {
                //不含历史参保费用
                $status = explode(",", $value['status']);
                $useKey = array_search(C('INSURANCE_STATUS_USE'), $status);
                if ($useKey !== false) {
                    $value['cost'] = $cost[$useKey];
                } else {
                    $value['cost'] = 0;
                }
            } else {
                $value['cost'] = $value['cost_num'];
            }
            $value['MP_ratio_AP'] = (number_format($value['cost'] / $value['buy_price'], 4, '.', '') * 100) . '%';
            $value['CP_ratio_AllCP'] = (number_format($value['cost'] / $value['cost_num'], 4, '.', '') * 100) . '%';
            $value['CP_ratio_AllAP'] = (number_format($value['cost'] / $value['buy_price_sum'], 4, '.', '') * 100) . '%';
        }
        $result["total"] = $count;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result["rows"] = $data;
        return $result;
    }

    //按科室分类统计维保报表
    public function MaintenanceSummaryDepart()
    {
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=I.assid';
        $joinAssets = $this->DB_get_all_join('assets_insurance', 'I', 'I.assid,A.departid', $join,array('A.is_delete'=> C('NO_STATUS')), 'I.assid');
        if (!$joinAssets) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['A.hospital_id'] = session('current_hospitalid');
        $where['A.is_delete'] = C('NO_STATUS');
        $where['A.status'][0] = 'NOT IN';
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');
        //排除已删除科室
        $del_departids = $this->get_delete_departids();
        if($del_departids){
            $where['A.departid'] = array('not in',$del_departids);
        }
        $fields = 'GROUP_CONCAT(A.assid) AS assid,A.departid,GROUP_CONCAT(I.assid) AS honaiAssid';
        $join = 'LEFT JOIN sb_assets_insurance AS I ON I.assid=A.assid AND I.status=' . C('INSURANCE_STATUS_USE');
        $data = $this->DB_get_all_join('assets_info', 'A', $fields, $join, $where, 'departid', '');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$value) {
            $value['department'] = $departname[$value['departid']]['department'];
            $result['depart'][] = $value['department'];
            if ($value['honaiAssid']) {
                $value['honaiNum'] = count(explode(',', $value['honaiAssid']));
            } else {
                $value['honaiNum'] = 0;
            }
            $value['assidNum'] = count(explode(',', $value['assid']));
            $value['joinNum'] = 0;
            foreach ($joinAssets as $JoinValue) {
                if ($value['departid'] == $JoinValue['departid']) {
                    $value['joinNum']++;
                }
            }
            $value['joinProportion'] = (sprintf("%.4f", $value['joinNum'] / $value['assidNum']) * 100) . '%';
            $dePaul = $value['assidNum'] - $value['joinNum'];


            if ($dePaul == 0) {
                $dePaul = $value['assidNum'];
            }
            $value['dePaulProportion'] = (sprintf("%.4f", $dePaul / $value['assidNum']) * 100) . '%';
            $result['assidNum'][] = $value['assidNum'];
            $result['joinNum'][] = $value['joinNum'];
            $result['honaiNum'][] = $value['honaiNum'];
            $result['joinProportion'][] = $value['joinProportion'];
            $result['dePaulProportion'][] = $value['dePaulProportion'];

        }
        $result["code"] = 200;
        $result["rows"] = $data;
        return $result;
    }

    //按费用统计维保报表
    public function MaintenanceSummaryAssets()
    {
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $where['A.hospital_id'] = session('current_hospitalid');
        $where['A.is_delete'] = C('NO_STATUS');
        $where['A.status'][0] = 'NOT IN';
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');
        //排除已删除科室
        $del_departids = $this->get_delete_departids();
        if($del_departids){
            $where['A.departid'] = array('not in',$del_departids);
        }
        $offset = ($page - 1) * $limit;
        $join[0] = 'LEFT JOIN sb_assets_insurance AS I ON I.assid=A.assid ';
        $join[1] = 'LEFT JOIN sb_repair AS R ON R.assid=I.assid AND I.status=' . C('INSURANCE_STATUS_USE') . ' 
        AND ( 
        (I.startdate<=R.applicant_time and I.overdate>=R.applicant_time) OR (I.startdate<=R.checkdate and I.overdate>=R.checkdate) 
        )';
        $fields = 'A.assnum,A.assid,A.assets,GROUP_CONCAT(I.insurid) AS insurid,GROUP_CONCAT(R.repid) AS repid';
        $count = $this->DB_get_count_join('assets_info','A',$join, $where);
        $data = $this->DB_get_all_join('assets_info', 'A', $fields, $join, $where, 'A.assid', 'A.assid', $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        foreach ($data as &$value) {
            if ($value['insurid']) {
                $insuridarr = explode(",", $value['insurid']);
                $insuridarr = array_unique($insuridarr);
                $value['insurSum'] = count($insuridarr);
                if ($value['repid']) {
                    $value['repSum'] = substr_count($value['repid'], ',') + 1;
                } else {
                    $value['repSum'] = 0;
                }
            } else {
                $value['insurSum'] = 0;
                $value['repSum'] = 0;
            }
        }
        $result["total"] = $count;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result["rows"] = $data;
        return $result;
    }

    /**
     * 修改维保信息
     * @params int $insurid 维保单ID
     * @return false or int;
     */
    public function saveInsurance()
    {
        $insurid = I('POST.insurid');
        if (!$insurid && $insurid < 1) {
            die(json_encode(array('status' => -999, 'msg' => '非法操作')));
        }
        $contacts = I('POST.contacts');
        $telephone = I('POST.telephone');
        if (!judgeMobile($telephone)) {
            die(json_encode(array('status' => -81, 'msg' => '联系人号码有误')));
        }
        if (!trim($contacts)) {
            die(json_encode(array('status' => -82, 'msg' => '请补充联系人')));
        }
        $data['edituser'] = session('username');
        $data['editdate'] = time();
        $data['contacts'] = $contacts;
        $data['telephone'] = $telephone;
        $where['insurid'] = array('EQ', $insurid);
        $save = $this->updateData('assets_insurance', $data, $where);
        if ($save) {
            $result['status'] = 1;
            $result['msg'] = '修改成功';
        } else {
            $result['status'] = -1;
            $result['msg'] = '修改失败';
        }
        return $result;
    }


    /**
     * 添加 维保厂家
     * @params int $assid 设备ID
     * @return false or ID
     */
    public function addInsurance()
    {
        $assid = I('POST.assid');
        if (!$assid) {
            die(json_encode(array('status' => -999, 'msg' => '非法操作')));
        }
        $buydate = strtotime(I('POST.buydate'));
        $startdate = strtotime(I('POST.startdate'));
        $overdate = strtotime(I('POST.overdate'));
        $company = trim(I('POST.company'));
        $company_id = trim(I('POST.company_id'));
        $nature = I('POST.nature');
        $cost = I('POST.cost');
        $contacts = I('POST.contacts');
        $telephone = I('POST.telephone');
        $content = I('POST.content');
        $remark = I('POST.remark');
        if (judgeMobile($telephone)) {
            $data['telephone'] = $telephone;
        } else {
            die(json_encode(array('status' => -81, 'msg' => '联系人号码有误')));
        }

        if (trim($contacts)) {
            $data['contacts'] = trim($contacts);
        } else {
            die(json_encode(array('status' => -82, 'msg' => '请输入联系人')));
        }

        if ($cost) {
            if (checkPrice($cost)) {
                $data['cost'] = $cost;
            } else {
                die(json_encode(array('status' => -83, 'msg' => '维保费用有误')));
            }
        }
//        if ($overdate < time()) {
//            die(json_encode(array('status' => -86, 'msg' => '时间设置有误')));
//        }

        if ($startdate < $overdate) {
            $data['startdate'] = $startdate;
            $data['overdate'] = $overdate;
        } else {
            die(json_encode(array('status' => -84, 'msg' => '维保开始时间不能大于结束时间')));
        }


        if ($company && $company_id > 0) {
            $data['company'] = trim($company);
            $data['company_id'] = trim($company_id);
        } else {
            die(json_encode(array('status' => -85, 'msg' => '请选择维保公司')));
        }


        if ($nature == C('INSURANCE_IS_GUARANTEE') or $nature == C('INSURANCE_THIRD_PARTY')) {
            $data['nature'] = $nature;
        } else {
            die(json_encode(array('status' => -999, 'msg' => '非法参数')));
        }

        if ($buydate) {
            $data['buydate'] = $buydate;
        } else {
            die(json_encode(array('status' => -85, 'msg' => '请选择购入日期')));
        }
        if ($startdate < time() && time() < $overdate) {
            $data['status'] = C('INSURANCE_STATUS_USE');
        } else {
            $data['status'] = C('INSURANCE_STATUS_NOT_RIGHT_NOW');
        }
        $data['adduser'] = session('username');
        $data['adddate'] = time();
        $data['content'] = $content;
        $data['remark'] = $remark;
        $data['assid'] = $assid;
        $add = $this->insertData('assets_insurance', $data);
        if ($add) {
            $fileName = I('POST.fileName');
            if ($fileName) {
                $this->addInsuranceFile($add);
            }
            $assnum = $this->DB_get_one('assets_info', 'assnum', array('assid' => $assid), '');
            //参保次数+1
            M("assets_info")->where("assid=$assid")->setInc('insuredsum');
            //日志行为记录文字
            $log['assnum'] = $assnum['assnum'];
            $text = getLogText('renewalAssetsLogText', $log);
            $this->addLog('assets_info', M()->getLastSql(), $text, $assid, '');
            $result['status'] = 1;
            $result['result'] = $add;
            $result['msg'] = '添加成功';
        } else {
            $result['status'] = -1;
            $result['msg'] = '添加失败';
        }
        return $result;
    }


    //上传维保文件
    public function uploadInsurance()
    {
        if ($_FILES['file']) {
            $Tool = new ToolController();
            $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx');
            $dirName = C('UPLOAD_DIR_INSURANCE_NAME');
            $info = $Tool->upFile($style, $dirName);
            if ($info['status'] == C('YES_STATUS')) {
                // 上传成功 获取上传文件信息
                $resule['status'] = 1;
                $resule['msg'] = '上传成功';
                $resule['path'] = $info['src'];
                $resule['name'] = $info['name'];
                $resule['formerly'] = $info['formerly'];
            } else {
                // 上传错误提示错误信息
                $resule['status'] = -1;
                $resule['msg'] = $info['msg'];
            }
            return $resule;
        }
    }


    /**
     * 记录上传的维保文件
     * @param int $insurid 维保ID
     */
    public function addInsuranceFile($insurid)
    {
        if (!$insurid) {
            die(json_encode(array('status' => -999, 'msg' => '非法操作')));
        }
        $fileName = I('POST.fileName');
        $formerly = I('POST.formerly');
        if ($fileName) {
            $fileName = explode('|', rtrim($fileName, '|'));
            $formerly = explode('|', rtrim($formerly, '|'));
            $data = [];
            foreach ($fileName as $key => $value) {
                $data[$key]['insurid'] = $insurid;
                $data[$key]['name'] = $formerly[$key];
                $data[$key]['url'] = $fileName[$key];
                $data[$key]['adddate'] = time();
            }
            $this->insertDataALL('assets_insurance_file', $data);
        }
    }

    /**
     * 返回保修期内的设备ID
     * @return string
     * */
    public function returnGuaranteeData()
    {
        $join = 'LEFT JOIN sb_assets_insurance AS B ON B.assid=A.assid';
        $where['B.status'] = array('eq', C('INSURANCE_STATUS_USE'));
        $where['_logic'] = 'or';
        $where['A.guarantee_date'] = array('EGT', getHandleTime(time()));
        $fields = 'GROUP_CONCAT(A.assid) AS assid ';
        $data = $this->DB_get_all_join('assets_info', 'A', $fields, $join, $where);
        if ($data[0]['assid'] == NULL) {
            return '-1';
        } else {
            return $data[0]['assid'];
        }
    }


    //更新Insurance维保表
    public function updateInsurance()
    {
        $data = 'UPDATE sb_assets_insurance SET status=CASE';
        $data .= ' WHEN startdate<' . time() . ' AND overdate>' . time() . ' THEN ' . C('INSURANCE_STATUS_USE');
        $data .= ' WHEN startdate>' . time() . ' THEN ' . C('INSURANCE_STATUS_NOT_RIGHT_NOW');
        $data .= ' WHEN overdate<' . time() . ' THEN ' . C('INSURANCE_STATUS_DE_PAUL') . ' END';
        $this->execute($data);
    }

    /**
     * 翻译维保性质
     * @params int $nature 维保性质编号
     * @return string
     * */
    public function formatNature($nature)
    {
        if ($nature !== null) {
            switch ($nature) {
                case C('INSURANCE_IS_GUARANTEE'):
                    $name = C('INSURANCE_IS_GUARANTEE_NAME');
                    break;
                case C('INSURANCE_THIRD_PARTY'):
                    $name = C('INSURANCE_THIRD_PARTY_NAME');
                    break;
                default:
                    $name = '未知参数';
            }
        } else {
            $name = '';
        }
        return $name;
    }
}