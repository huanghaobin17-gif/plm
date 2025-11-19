<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;

class OfflineSuppliersModel extends CommonModel
{
    private $MODULE = 'OfflineSuppliers';
    private $Controller = 'OfflineSuppliers';
    protected $tablePrefix = 'sb_';
    protected $tableName = 'offline_suppliers';

    //厂商列表
    public function offlineSuppliersList()
    {
        $auppliersListSupName = I('POST.auppliersListSupName');
        $suppliers_type = I('POST.suppliers_type');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'olsid';
        $where['is_delete'] = C('NO_STATUS');
        if ($auppliersListSupName) {
            $where['sup_name'] = ['LIKE', "%$auppliersListSupName%"];
        }
        if ($suppliers_type) {
            switch ($suppliers_type) {
                case 1:
                    //供应商
                    $where['is_supplier'] = ['EQ', C('YES_STATUS')];
                    break;
                case 2:
                    //生产商
                    $where['is_manufacturer'] = ['EQ', C('YES_STATUS')];
                    break;
                case 3:
                    //维修商
                    $where['is_repair'] = ['EQ', C('YES_STATUS')];
                    break;
                case 4:
                    //维保商
                    $where['is_insurance'] = ['EQ', C('YES_STATUS')];
                    break;
            }

        }
        $fileds = 'olsid,sup_name,sup_num,is_supplier,is_manufacturer,is_repair,is_insurance,salesman_name,salesman_phone';
        $total = $this->DB_get_count('offline_suppliers', $where);
        $data = $this->DB_get_all('offline_suppliers', $fileds, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        $olsid_arr = [];
        foreach ($data as &$val) {
            $olsid_arr[] = $val['olsid'];
        }
        $fileData = [];
        $file = $this->DB_get_all('offline_suppliers_file', 'name,ext,url,term_date,olsid,fileid',
            ['olsid' => ['IN', $olsid_arr]]);
        if ($file) {
            foreach ($file as &$file_v) {
                $fileData[$file_v['olsid']][$file_v['fileid']]['licence'] = $file_v['name'];
                $fileData[$file_v['olsid']][$file_v['fileid']]['ext'] = $file_v['ext'];
                $fileData[$file_v['olsid']][$file_v['fileid']]['term'] = getHandleTime($file_v['term_date']);
                $fileData[$file_v['olsid']][$file_v['fileid']]['olsid'] = $file_v['olsid'];
                $fileData[$file_v['olsid']][$file_v['fileid']]['situation'] = '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878">';
            }
        }
        $editMenu = get_menu($this->MODULE, $this->Controller, 'editOfflineSupplier');
        $delMenu = get_menu($this->MODULE, $this->Controller, 'delSupplier');
        foreach ($data as &$val) {
            $val['suppliers_type'] = '';
            if ($val['is_supplier'] == C('YES_STATUS')) {
                $val['suppliers_type'] = '供应商、';
            }
            if ($val['is_manufacturer'] == C('YES_STATUS')) {
                $val['suppliers_type'] .= '生产商、';
            }
            if ($val['is_repair'] == C('YES_STATUS')) {
                $val['suppliers_type'] .= '维修商、';
            }
            if ($val['is_insurance'] == C('YES_STATUS')) {
                $val['suppliers_type'] .= '维保商';
            }
            $val['suppliers_type'] = trim($val['suppliers_type'], '、');
            $detailsUrl = get_url() . '?action=showSuppliersDetails&olsid=' . $val['olsid'];
            $val['operation'] = '<div class="layui-btn-group">';
            $val['operation'] .= $this->returnListLink('查看', $detailsUrl, 'showDetails',
                C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($editMenu) {
                $val['operation'] .= $this->returnListLink('编辑', $editMenu['actionurl'], 'editSuppliers',
                    C('BTN_CURRENCY'));;
            } else {
                $val['operation'] .= $this->returnListLink('编辑', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
            }
            if ($delMenu) {
                $val['operation'] .= $this->returnListLink('删除', $delMenu['actionurl'], 'delSuppliers',
                    C('BTN_CURRENCY') . ' layui-btn-danger');;
            }
            $val['operation'] .= '</div>';
            $val['file'] = $fileData[$val['olsid']];

        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //添加厂商操作
    public function addOfflineSupplier()
    {
        $menuData = get_menu($this->MODULE, $this->Controller, 'addOfflineSupplier');
        if (!$menuData) {
            return array('status' => -1, 'msg' => '无权限新增厂家');
        }
        //验证数据
        $result = $this->checkSupplierData();
        if ($result['status'] != C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => $result['msg'])));
        }
        $data = $result['data'];
        $add = $this->insertData('offline_suppliers', $data);
        if ($add) {
            $log['sup_name'] = $data['sup_name'];
            $text = getLogText('addOfflineSuppliers', $log);
            $this->addLog('offline_suppliers', M()->getLastSql(), $text, $add);
            //授权文件
            $this->addAuthFile($add);
            //文件入库
            $this->addSupplierFile($add);
            return array(
                'status' => 1,
                'msg' => '添加成功',
                'result' => array('olsid' => $add, 'sup_name' => $data['sup_name'])
            );
        } else {
            return array('status' => -1, 'msg' => '提交失败');
        }
    }

    //编辑厂商操作
    public function editOfflineSupplier()
    {
        //验证数据
        $result = $this->checkSupplierData();
        if ($result['status'] != C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => $result['msg'])));
        }
        $data = $result['data'];
        $save = $this->updateData('offline_suppliers', $data, array('olsid' => $data['olsid']));
        if ($save) {
            $log['sup_num'] = $data['sup_num'];
            $text = getLogText('editOfflineSuppliers', $log);
            $this->addLog('offline_suppliers', M()->getLastSql(), $text, $data['olsid']);
            //授权文件
            $this->addAuthFile($data['olsid']);
            //文件入库
            $this->addSupplierFile($data['olsid']);
            return array(
                'status' => 1,
                'msg' => '编辑成功',
                'result' => array('olsid' => $data['olsid'], 'sup_name' => $data['sup_name'])
            );
        } else {
            return array('status' => -1, 'msg' => '提交失败');
        }
    }

    //合同列表
    public function olsContract()
    {
        $supplier_name = I('POST.supplier_name');
        $contract_name = I('POST.contract_name');
        $contract_type = I('POST.contract_type');
        $is_confirm = I('POST.is_confirm');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'olsid';
        $where['hospital_id'] = session('current_hospitalid');
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        if ($contract_type) {
            $where['contract_type'] = ['EQ', $contract_type];
        }
        if ($contract_name) {
            $where['contract_name'] = ['LIKE', "%$contract_name%"];
        }
        if ($supplier_name) {
            $where['supplier_name'] = ['LIKE', "%$supplier_name%"];
        }
        if ($is_confirm) {
            $where['is_confirm'] = ['EQ', $is_confirm];
        }
        $fileds = 'contract_id,contract_num,contract_name,contract_type,supplier_name,supplier_contacts,supplier_phone,sign_date,end_date,
        contract_content,is_confirm';
        $count = M('purchases_contract', C('DB_PREFIX'))
            ->field('COUNT(*) AS COUNT')
            ->union(array('field' => 'COUNT(*) AS COUNT', 'table' => 'sb_assets_record_contract', 'where' => $where),
                true)
            ->union(array('field' => 'COUNT(*) AS COUNT', 'table' => 'sb_assets_insurance_contract', 'where' => $where),
                true)
            ->union(array('field' => 'COUNT(*) AS COUNT', 'table' => 'sb_parts_contract', 'where' => $where), true)
            ->union(array('field' => 'COUNT(*) AS COUNT', 'table' => 'sb_repair_contract', 'where' => $where), true)
            ->where($where)
            ->select();

        $sql = M('purchases_contract', C('DB_PREFIX'))
            ->field($fileds)
            ->union(array('field' => $fileds, 'table' => 'sb_assets_record_contract', 'where' => $where), true)
            ->union(array('field' => $fileds, 'table' => 'sb_assets_insurance_contract', 'where' => $where), true)
            ->union(array('field' => $fileds, 'table' => 'sb_parts_contract', 'where' => $where), true)
            ->union(array('field' => $fileds, 'table' => 'sb_repair_contract', 'where' => $where), true)
            ->where($where)
            ->fetchSql(true)
            ->select();

        $sql = 'SELECT * FROM(' . $sql . ') AS a ORDER BY a.' . $sort . ' ' . $order . ' LIMIT ' . $offset . "," . $limit;
        $data = M()->query($sql);
        $total = 0;
        foreach ($count as &$countV) {
            $total += $countV['COUNT'];
        }
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $confirmMenu = get_menu($this->MODULE, $this->Controller, 'confirmOLSContract');
        foreach ($data as &$value) {
            $typeResult = $this->translation_Contract($value['contract_type']);
            $value['contract_type_name'] = $typeResult['type_name'];
            $detailsUrl = get_url() . '?action=showOLSContractDetails&contract_id=' . $value['contract_id'] . '&contract_type=' . $value['contract_type'];
            if ($value['is_confirm'] != C('YES_STATUS')) {
                if ($confirmMenu) {
                    $value['operation'] = $this->returnListLink($confirmMenu['actionname'], $confirmMenu['actionurl'],
                        'confirmContract', C('BTN_CURRENCY'));;
                } else {
                    $value['operation'] = $this->returnListLink('待确认', '', '',
                        C('BTN_CURRENCY') . ' layui-btn-disabled');
                }
            } else {
                $value['operation'] .= $this->returnListLink('查看', $detailsUrl, 'showDetails',
                    C('BTN_CURRENCY') . ' layui-btn-primary');
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //新增合同操作
    public function addOLSContract()
    {
        //验证合同基础参数是否完整
        $result = $this->checkContractData();
        if ($result['status'] != C('YES_STATUS')) {
            return array('status' => -1, 'msg' => $result['msg']);
        }
        $data = $result['data'];
        switch ($data['contract_type']) {
            case C('CONTRACT_TYPE_SUPPLIER'):
                //采购类型 todo 暂时未开启
                $result = $this->addAssetsContract($data);
                return $result;
                break;
            case C('CONTRACT_TYPE_REPAIR'):
                //维修类型
                $result = $this->addRepairContract($data);
                return $result;
                break;
            case C('CONTRACT_TYPE_INSURANCE'):
                //维保类型 todo 暂时未开启
                break;
            case C('CONTRACT_TYPE_RECORD_ASSETS'):
                //设备采购补录类型
                $result = $this->addRecordAssetsContract($data);
                return $result;
                break;
            default:
                return array('status' => -1, 'msg' => '未知类型');
                break;
        }
    }

    //编辑合同操作
    public function editOLSContract()
    {
        //验证合同基础参数是否完整
        $result = $this->checkContractData();
        if ($result['status'] != C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => $result['msg'])));
        }
        $data = $result['data'];
        $add = $this->updateData('purchases_contract', $data, array('contract_id' => $data['contract_id']));
        if ($add) {
            $log['sup_name'] = $data['sup_name'];
            $text = getLogText('addOfflineSuppliers', $log);
            $this->addLog('user', M()->getLastSql(), $text, $add);
            //付款明细入库
            $this->addContractPay($add);
            //合同文件入库
            $this->addContractFile($add);
            //设备明细入库
            $this->addContractAssets($add);
            return array('status' => 1, 'msg' => '添加成功');
        } else {
            return array('status' => -1, 'msg' => '提交失败');
        }
    }

    //确人并录入合同信息
    public function confirmOLSContract()
    {
        //验证合同基础参数是否完整
        $result = $this->checkContractData();
        if ($result['status'] != C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => $result['msg'])));
        }
        $data = $result['data'];

        $typeResult = $this->translation_Contract($data['contract_type']);
        $table = $typeResult['table'] . '_contract';

        $contractData = $this->DB_get_one($table, '', array('contract_id' => $data['contract_id']));
        if (!$contractData) {
            die(json_encode(array('status' => -1, 'msg' => '无此合同记录')));
        }

        if ($contractData['is_confirm'] == C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => '已确认过请勿重复操作')));
        }

        $data['confirmdate'] = getHandleDate(time());
        $data['confirm_user'] = session('username');
        $data['is_confirm'] = C('YES_STATUS');
        $confirm = $this->updateData($table, $data, array('contract_id' => $data['contract_id']));
        if ($confirm) {
            $log['contract_num'] = $data['contract_num'];
            $text = getLogText('confirmOLSContract', $log);
            $this->addLog($table, M()->getLastSql(), $text, $data['contract_id']);
            //付款明细入库
            $this->addContractPay($data['contract_id']);
            //合同文件入库
            $this->addContractFile($data['contract_id']);
            return array('status' => 1, 'msg' => '成功录入');
        } else {
            return array('status' => -1, 'msg' => '提交失败');
        }
    }

    //合同付款列表
    public function payOLSContractList()
    {
        $pay_status = I('POST.pay_status');
        $supplier_name = I('POST.supplier_name');
        $contract_num = I('POST.contract_num');
        $contract_name = I('POST.contract_name');
        $contract_type = I('POST.contract_type');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'ASC';
        $sort = I('POST.sort') ? I('POST.sort') : 'pay_id';
        $where['hospital_id'] = session('current_hospitalid');
        $where['is_delete'] = array('NEQ', C('YES_STATUS'));
        if (!session('job_departid')) {
            $result['msg'] = '该用户未分配工作科室';
            $result['code'] = 400;
            return $result;
        }

        if ($contract_name) {
            $where['contract_name'] = ['LIKE', "%$contract_name%"];
        }
        if ($contract_num) {
            $where['contract_num'] = ['LIKE', "%$contract_num%"];
        }
        if ($supplier_name) {
            $where['supplier_name'] = ['LIKE', "%$supplier_name%"];
        }
        if ($pay_status != '') {
            $where['pay_status'] = ['EQ', $pay_status];
        }
        if ($contract_type) {
            $where['contract_type'] = ['EQ', $contract_type];
        }

        $fileds = '';
        $count = M('purchases_contract_pay', C('DB_PREFIX'))
            ->field('COUNT(*) AS COUNT')
            ->union(array(
                'field' => 'COUNT(*) AS COUNT',
                'table' => 'sb_assets_record_contract_pay',
                'where' => $where
            ), true)
            ->union(array(
                'field' => 'COUNT(*) AS COUNT',
                'table' => 'sb_assets_insurance_contract_pay',
                'where' => $where
            ), true)
            ->union(array('field' => 'COUNT(*) AS COUNT', 'table' => 'sb_parts_contract_pay', 'where' => $where), true)
            ->union(array('field' => 'COUNT(*) AS COUNT', 'table' => 'sb_repair_contract_pay', 'where' => $where), true)
            ->where($where)
            ->select();

        $sql = M('purchases_contract_pay', C('DB_PREFIX'))
            ->field($fileds)
            ->union(array('field' => $fileds, 'table' => 'sb_assets_record_contract_pay', 'where' => $where), true)
            ->union(array('field' => $fileds, 'table' => 'sb_assets_insurance_contract_pay', 'where' => $where), true)
            ->union(array('field' => $fileds, 'table' => 'sb_parts_contract_pay', 'where' => $where), true)
            ->union(array('field' => $fileds, 'table' => 'sb_repair_contract_pay', 'where' => $where), true)
            ->where($where)
            ->fetchSql(true)
            ->select();

        $sql = 'SELECT * FROM(' . $sql . ') AS a ORDER BY a.' . 'contract_type ASC,' . $sort . ' ' . $order . ',pay_period ASC' . ' LIMIT ' . $offset . "," . $limit;
        $data = M()->query($sql);
        $total = 0;
        foreach ($count as &$countV) {
            $total += $countV['COUNT'];
        }
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $success_i = '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
        $fail_i = '<i class="layui-icon layui-icon-zzclose" style="color: red"></i>';
        $payMenu = get_menu($this->MODULE, $this->Controller, 'payOLSContract');
        foreach ($data as &$dataV) {
            $typeResult = $this->translation_Contract($dataV['contract_type']);
            $dataV['contract_type_name'] = $typeResult['type_name'];
            $dataV['estimate_pay_date'] = HandleEmptyNull($dataV['estimate_pay_date']);
            if ($dataV['pay_status'] == C('YES_STATUS')) {
                $dataV['real_pay_date'] = HandleEmptyNull($dataV['real_pay_date']);
                $dataV['pay_status_type'] = $success_i;
            } else {
                //清空 避免出现0000-0000-0000
                $dataV['real_pay_date'] = '';
                $dataV['pay_status_type'] = $fail_i;
                if ($payMenu) {
                    $dataV['real_pay_date'] = '<div class="real_pay_date_v not-show" data-value=""></div><div class="real_pay_date">请点击确认录入日期</div>';
                    $dataV['operation'] = '<div class="layui-btn-group">';
                    $dataV['operation'] .= $this->returnListLink('确认付款', $payMenu['actionurl'], 'payOLSContract',
                        C('BTN_CURRENCY'));
                    $dataV['operation'] .= '</div>';
                }
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //合同付款操作
    public function payOLSContract()
    {
        $pay_id = trim(I('POST.pay_id'));
        $contract_type = trim(I('POST.contract_type'));
        $real_pay_date = trim(I('POST.real_pay_date'));

        $this->checkstatus(judgeNum($pay_id), '非法操作');
        $this->checkstatus(judgeNum($contract_type), '非法操作');
        $this->checkstatus(judgeEmpty($real_pay_date), '请录入实际付款日期');

        $typeResult = $this->translation_Contract($contract_type);
        $table = $typeResult['table'] . '_contract_pay';

        $where['pay_id'] = ['EQ', $pay_id];
        $where['contract_type'] = ['EQ', $contract_type];
        $data = $this->DB_get_one($table, '', $where);
        if ($data) {
            if ($data['pay_status'] == C('YES_STATUS')) {
                $result['status'] = -99;
                $result['msg'] = '已记录付款信息请勿重复操作';
            } else {
                $data['pay_status'] = C('YES_STATUS');
                $data['real_pay_date'] = $real_pay_date;
                $data['hospital_id'] = session('current_hospitalid');
                $save = $this->updateData($table, $data, $where);
                if ($save) {
                    $log['contract_num'] = $data['contract_num'];
                    $log['real_pay_date'] = $data['real_pay_date'];
                    $log['pay_period'] = $data['pay_period'];
                    $log['contract_type'] = $typeResult['type_name'];
                    $text = getLogText('payContract', $log);
                    $this->addLog($table, M()->getLastSql(), $text, $data['contract_id']);
                    $result['status'] = 1;
                    $result['msg'] = '已确认';
                } else {
                    $result['status'] = -1;
                    $result['msg'] = '付款记录失败';
                }
            }
        } else {
            $result['status'] = -9;
            $result['msg'] = '数据不存在';
        }

        return $result;
    }

    //获取合同类型对应的乙方单位
    public function getSuppliers($contract_type = '')
    {
        if (!$contract_type) {
            $contract_type = I('POST.contract_type');
        }
        $where['status'] = C('OPEN_STATUS');
        $where['is_delete'] = ['EQ', C('NO_STATUS')];
        switch ($contract_type) {
            case C('CONTRACT_TYPE_SUPPLIER'):
            case C('CONTRACT_TYPE_RECORD_ASSETS'):
                $where[0]['is_supplier'] = C('YES_STATUS');
                $where[0]['is_manufacturer'] = C('YES_STATUS');
                $where[0]['_logic'] = 'OR';
                break;
            case C('CONTRACT_TYPE_REPAIR'):
                $where['is_repair'] = C('YES_STATUS');
                break;
            case C('CONTRACT_TYPE_INSURANCE'):
                $where['is_insurance'] = C('YES_STATUS');
                break;
        }
        $contract = $this->DB_get_all('offline_suppliers', 'olsid,sup_name', $where);
        if ($contract) {
            return array('status' => 1, 'msg' => '获取成功', 'result' => $contract);
        } else {
            return array('status' => -1, 'msg' => '无对应类型的厂商,请先补充');
        }
    }

    //获取供应商选择框
    public function getSupplierSelect()
    {
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $where['hospital_id'] = session('current_hospitalid');
        $supplierArr = $this->DB_get_one('parts_inware_record', 'group_concat(supplier_id) AS supplier_id', $where);
        if ($supplierArr['supplier_id']) {
            return $this->DB_get_all('offline_suppliers', 'olsid,sup_name',
                ['olsid' => ['IN', $supplierArr['supplier_id']]]);
        } else {
            return array();
        }
    }

    //获取乙方联系人信息
    public function getSalesman()
    {
        $olsid = I('POST.olsid');
        $where['status'] = ['NEQ', C('DELETE_STATUS')];
        $where['olsid'] = ['EQ', $olsid];
        $contract = $this->DB_get_one('offline_suppliers', 'salesman_name,salesman_phone', $where);
        if ($contract) {
            return array('status' => 1, '获取成功', 'result' => $contract);
        } else {
            return array('status' => -1, '无数据');
        }
    }

    //已纳入的维修明细列表
    public function joinedRepairList()
    {
        $joinedRepair = I('POST.joinedRepair');
        $where['repair_type'] = ['EQ', C('REPAIR_TYPE_THIRD_PARTY')];
        if ($joinedRepair) {
            $where['repid'][] = array('IN', $joinedRepair);
        } else {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $fileds = 'repid,repnum,assets,model,departid,applicant,applicant_time,response,response_date,breakdown';
        $total = $this->DB_get_count('repair', $where);
        $data = $this->DB_get_all('repair', $fileds, $where, '', 'repid DESC');
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$one) {
            $one['applicant_time'] = getHandleTime($one['applicant_time']);
            $one['response_date'] = getHandleTime($one['response_date']);
            $one['department'] = $departname[$one['departid']]['department'];
            $one['operation'] = $this->returnListLink('移除', '', 'del', C('BTN_CURRENCY') . ' layui-btn-danger');
        }
        $result["total"] = $total;
        $result["rows"] = $data;
        $result['code'] = 200;
        return $result;
    }

    //可纳入的维修明细列表
    public function canJoinRepairList()
    {
        $joinedRepair = I('POST.joinedRepair');
        $hospital_id = I('POST.hospital_id');
        $supplier_id = I('POST.supplier_id');
        $assets = I('POST.assets');
        $repnum = I('POST.repnum');
        $model = I('POST.model');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        if (!$hospital_id or !$supplier_id) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        if ($assets) {
            $assetsWhere['assets'] = array('LIKE', "%$assets%");
        }
        if ($model) {
            $assetsWhere['model'] = array('LIKE', "%$model%");
        }
        $assetsWhere['hospital_id'] = array('IN', $hospital_id);
        $assets = $this->DB_get_all('assets_info', 'assid', $assetsWhere);
        if (!$assets) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $assid = [];
        foreach ($assets as &$assetsV) {
            $assid[] = $assetsV['assid'];
        }

        $offerWhere['offer_company_id'] = ['EQ', $supplier_id];
        $offerWhere['last_decisioin'] = ['EQ', C('YES_STATUS')];
        $offer_company = $this->DB_get_all('repair_offer_company', 'repid', $offerWhere);
        if (!$offer_company) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $repid = [];
        foreach ($offer_company as &$offer_companyV) {
            $repid[] = $offer_companyV['repid'];
        }

        $where['repid'][] = ['IN', $repid];
        $where['assid'] = ['IN', $assid];
        if ($repnum) {
            $where['repnum'] = ['LIKE', "%$repnum%"];
        }

        //排除已录入合同的记录
        $contractWhere['is_delete'] = ['NEQ', C('YES_STATUS')];
        $contract = $this->DB_get_all('repair_contract', 'GROUP_CONCAT(repid) AS repid', $contractWhere);
        if ($contract['repid'] != null) {
            $where['repid'][] = ['NOT IN', $contract['repid']];
        }
        $where['repair_type'] = ['EQ', C('REPAIR_TYPE_THIRD_PARTY')];

        if ($joinedRepair) {
            $where['repid'][] = array('NOT IN', $joinedRepair);
        }

        $fileds = 'repid,repnum,assets,model,,applicant,applicant_time,response,response_date,breakdown';
        $total = $this->DB_get_count('repair', $where);
        $data = $this->DB_get_all('repair', $fileds, $where, '', 'repid DESC', $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        foreach ($data as &$one) {
            $one['operation'] = $this->returnListLink('纳入', '', 'add', C('BTN_CURRENCY'));
        }
        $result["total"] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result["rows"] = $data;
        return $result;
    }


    //已纳入的采购设备(补录)明细列表
    public function joinedRecordAssetsList()
    {
        $joinedRecordAssets = I('POST.joinedRecordAssets');
        $where['repair_type'] = ['EQ', C('REPAIR_TYPE_THIRD_PARTY')];
        if ($joinedRecordAssets) {
            $where['assid'][] = array('IN', $joinedRecordAssets);
        } else {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $total = $this->DB_get_count('assets_info', $where);
        $data = $this->DB_get_all('assets_info', '', $where, '', 'assid DESC');
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        foreach ($data as &$one) {
            $one['factorydate'] = HandleEmptyNull($one['factorydate']);
            $one['storage_date'] = HandleEmptyNull($one['storage_date']);
            $one['helpcatid'] = $baseSetting['assets']['assets_helpcat']['value'][$one['helpcatid']];
            $one['financeid'] = $baseSetting['assets']['assets_finance']['value'][$one['financeid']];
            $one['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$one['capitalfrom']];
            $one['assfromid'] = $baseSetting['assets']['assets_assfrom']['value'][$one['assfromid']];
            $one['operation'] = $this->returnListLink('移除', '', 'del', C('BTN_CURRENCY') . ' layui-btn-danger');
        }
        $result["total"] = $total;
        $result["rows"] = $data;
        $result['code'] = 200;
        return $result;
    }

    ///可纳入的采购设备明细(补录)列表
    public function canJoinRecordAssetsList()
    {
        $joinedRecordAssets = I('POST.joinedRecordAssets');
        $assets = I('POST.assets');
        $model = I('POST.model');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        if (!session('current_hospitalid')) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        $where['hospital_id'] = array('EQ', session('current_hospitalid'));
        if ($assets) {
            $where['assets'] = ['LIKE', "%$assets%"];
        }
        if ($model) {
            $where['model'] = ['LIKE', "%$model%"];
        }

        //排除已录入合同的记录
        $contractWhere['is_delete'] = ['NEQ', C('YES_STATUS')];
        $contract = $this->DB_get_all('assets_record_contract', 'GROUP_CONCAT(assid) AS assid', $contractWhere);
        if ($contract['assid'] != null) {
            $where['assid'][] = ['NOT IN', $contract['assid']];
        }
        $where['repair_type'] = ['EQ', C('REPAIR_TYPE_THIRD_PARTY')];

        if ($joinedRecordAssets) {
            $where['assid'][] = array('NOT IN', $joinedRecordAssets);
        }

        $total = $this->DB_get_count('assets_info', $where);
        $data = $this->DB_get_all('assets_info', '', $where, '', 'assid DESC', $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        foreach ($data as &$one) {
            $one['factorydate'] = HandleEmptyNull($one['factorydate']);
            $one['storage_date'] = HandleEmptyNull($one['storage_date']);
            $one['helpcatid'] = $baseSetting['assets']['assets_helpcat']['value'][$one['helpcatid']];
            $one['financeid'] = $baseSetting['assets']['assets_finance']['value'][$one['financeid']];
            $one['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$one['capitalfrom']];
            $one['assfromid'] = $baseSetting['assets']['assets_assfrom']['value'][$one['assfromid']];
            $one['operation'] = $this->returnListLink('纳入', '', 'add', C('BTN_CURRENCY'));
        }
        $result["total"] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result["rows"] = $data;
        return $result;

    }

    /**
     * 采购类型合同入库 todo
     * @param $data array 合同数据
     * @return array
     * */
    public function addAssetsContract($data)
    {
        $add = $this->insertData('purchases_contract', $data);
        if ($add) {
            $log['sup_name'] = $data['sup_name'];
            $text = getLogText('addOfflineSuppliers', $log);
            $this->addLog('user', M()->getLastSql(), $text, $add);
            //付款明细入库
            $this->addContractPay($add);
            //合同文件入库
            $this->addContractFile($add);
            //设备明细入库
            $this->addContractAssets($add);
            return array('status' => 1, 'msg' => '添加成功');
        } else {
            return array('status' => -1, 'msg' => '提交失败');
        }
    }


    /**
     * 维修类型合同入库
     * @param $data array 合同数据
     * @return  array
     * */
    public function addRepairContract($data)
    {
        $joinedRepair = I('POST.joinedRepair');
        if ($joinedRepair) {
            $data['repid'] = $joinedRepair;
            $add = $this->insertData('repair_contract', $data);
            if ($add) {
                $log['contract_num'] = $data['contract_num'];
                $log['contract_name'] = $data['contract_name'];
                $text = getLogText('addContract', $log);
                $this->addLog('repair_contract', M()->getLastSql(), $text, $add);
                //付款明细入库
                $this->addContractPay($add);
                //合同文件入库
                $this->addContractFile($add);
                //记录合同ID关联至维修表
                $this->setContractIdRepair($add, $joinedRepair);
                return array('status' => 1, 'msg' => '添加成功');
            } else {
                return array('status' => -1, 'msg' => '提交失败');
            }
        } else {
            return array('status' => -1, 'msg' => '维修明细单不能为空');
        }
    }


    /**
     * 设备补录类型合同入库
     * @param $data array 合同数据
     * @return  array
     * */
    public function addRecordAssetsContract($data)
    {
        $joinedRecordAssets = I('POST.joinedRecordAssets');
        if ($joinedRecordAssets) {
            $data['assid'] = $joinedRecordAssets;
            $add = $this->insertData('assets_record_contract', $data);
            if ($add) {
                $log['contract_num'] = $data['contract_num'];
                $log['contract_name'] = $data['contract_name'];
                $text = getLogText('addContract', $log);
                $this->addLog('assets_record_contract', M()->getLastSql(), $text, $add);
                //付款明细入库
                $this->addContractPay($add);
                //合同文件入库
                $this->addContractFile($add);
                //设备明细入库
                $this->addContractRecordAssets($add, $joinedRecordAssets);
                return array('status' => 1, 'msg' => '添加成功');
            } else {
                return array('status' => -1, 'msg' => '提交失败');
            }
        } else {
            return array('status' => -1, 'msg' => '设备明细单不能为空');
        }
    }

    /**
     * 验证厂商信息
     * */
    private function checkSupplierData()
    {
        $this->checkstatus(judgeEmpty(trim(I('POST.sup_name'))), '公司名称不能为空');
        $data['sup_name'] = trim(I('POST.sup_name'));
        if (I('POST.olsid')) {
            $data['olsid'] = I('POST.olsid');
            $data['edituser'] = session('username');
            $data['editdate'] = time();
            $where['sup_name'] = ['EQ', $data['sup_name']];
            $where['status'] = ['NEQ', C('DELETE_STATUS')];
            $where['olsid'] = ['NEQ', I('POST.olsid')];
            $where['is_delete'] = C('NO_STATUS');
            $suppliers = $this->DB_get_one('offline_suppliers', 'olsid', $where);
            if ($suppliers) {
                return array('status' => -1, 'msg' => $data['sup_name'] . '已存在');
            }
        } else {
            $data['sup_num'] = $this->getSupNum();
            $data['adduser'] = session('username');
            $data['adddate'] = time();
            $where['sup_name'] = ['EQ', $data['sup_name']];
            $where['status'] = ['NEQ', C('DELETE_STATUS')];
            $suppliers = $this->DB_get_one('offline_suppliers', 'olsid', $where);
            if ($suppliers) {
                return array('status' => -1, 'msg' => $data['sup_name'] . '已存在，请勿重复添加');
            }
        }
        $suppliers_type = I('POST.suppliers_type');
        $this->checkstatus(judgeEmpty($suppliers_type), '请选择公司类型');
        $suppliers_type = explode(',', $suppliers_type);

        $data['is_supplier'] = C('NO_STATUS');
        $data['is_manufacturer'] = C('NO_STATUS');
        $data['is_repair'] = C('NO_STATUS');
        $data['is_insurance'] = C('NO_STATUS');
        foreach ($suppliers_type as &$sup_type_value) {
            switch ($sup_type_value) {
                case 1:
                    $data['is_supplier'] = C('YES_STATUS');
                    break;
                case 2:
                    $data['is_manufacturer'] = C('YES_STATUS');
                    break;
                case 3:
                    $data['is_repair'] = C('YES_STATUS');
                    break;
                case 4:
                    $data['is_insurance'] = C('YES_STATUS');
                    break;
                default:
                    return array('status' => -1, 'msg' => '非法公司类型');
                    break;
            }
        }
        //技术人员号码 验证
        $artisan_phone = trim(I('POST.artisan_phone'));
        if ($artisan_phone) {
            $this->checkstatus(judgeMobileOrPhone($artisan_phone), ' 技术人员号码异常请核对');
        }
        $data['artisan_phone'] = $artisan_phone;

        //业务联系人号码 验证
        $salesman_phone = trim(I('POST.salesman_phone'));
//        if ($salesman_phone) {
//            $this->checkstatus(judgeMobileOrPhone($salesman_phone), '业务联系人号码异常请核对');
//        }
        $data['salesman_phone'] = $salesman_phone;

        //传真号码 验证
        $fax_number = I('POST.fax_number');
        if ($fax_number) {
            $this->checkstatus(judgePhone(trim(I('POST.fax_number'))), '传真号码异常请核对');
        }
        $data['fax_number'] = trim(I('POST.fax_number'));

        //邮箱验证
        $email = I('POST.email');
//        if ($email) {
//            $this->checkstatus(judgeEmail(trim(I('POST.email'))), '电子邮箱格式有误');
//        }
        if (I('POST.status')) {
            $data['status'] = trim(I('POST.status'));
        } else {
            $data['status'] = C('OPEN_STATUS');
        }
        $data['email'] = trim(I('POST.email'));
        $data['ECC_code'] = trim(I('POST.ECC_code'));
        $data['sup_abbr'] = trim(I('POST.sup_abbr'));
        $data['artisan_name'] = trim(I('POST.artisan_name'));
        $data['salesman_name'] = trim(I('POST.salesman_name'));
        $data['provinces'] = I('POST.provinces');
        $data['city'] = I('POST.city');
        $data['areas'] = I('POST.areas');
        $data['address'] = I('POST.address');
        $data['break'] = trim(I('POST.break'));
        $result['status'] = C('YES_STATUS');
        $result['data'] = $data;
        return $result;
    }


    /**
     * 验证合同信息
     * */
    public function checkContractData()
    {
        $this->checkstatus(judgeEmpty(trim(I('POST.contract_num'))), '请补充合同编号');
        $data['contract_num'] = trim(I('POST.contract_num'));
        $this->checkstatus(judgeEmpty(trim(I('POST.contract_name'))), '请补充合同名称');
        $data['contract_name'] = trim(I('POST.contract_name'));

        $this->checkstatus(judgeEmpty(trim(I('POST.contract_type'))), '请选择合同类型');
        $data['contract_type'] = trim(I('POST.contract_type'));

        if (I('POST.contract_id')) {
            $data['contract_id'] = I('POST.contract_id');
            $where['contract_id'] = ['NEQ', $data['contract_id']];
            $data['edit_user'] = session('username');
            $data['edit_time'] = getHandleTime(time());
        } else {
            $data['add_user'] = session('username');
            $data['add_time'] = getHandleTime(time());
        }
        $where['contract_num'] = ['EQ', $data['contract_num']];
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];

        $typeResult = $this->translation_Contract($data['contract_type']);
        $table = $typeResult['table'] . '_contract';
        $contract = $this->DB_get_one($table, 'contract_id', $where);
        if ($contract) {
            return array('status' => -1, 'msg' => '合同编号 ' . $data['contract_num'] . ' 已存在，请勿重复添加');
        }

        $this->checkstatus(judgeEmpty(trim(I('POST.supplier_id'))), '请选择乙方单位');
        $data['supplier_id'] = trim(I('POST.supplier_id'));
        $data['supplier_name'] = trim(I('POST.supplier_name'));

        $this->checkstatus(checkPrice(trim(I('POST.contract_amount'))), '请补充合同金额');
        $data['contract_amount'] = trim(I('POST.contract_amount'));

        $this->checkstatus(judgeEmpty(trim(I('POST.hospital_manager'))), '请选择院方联系人');
        $data['hospital_manager'] = trim(I('POST.hospital_manager'));

        $this->checkstatus(judgeEmpty(trim(I('POST.supplier_contacts'))), '请补充厂商联系人');
        $data['supplier_contacts'] = trim(I('POST.supplier_contacts'));

        $this->checkstatus(judgeEmpty(trim(I('POST.supplier_phone'))), '请补充厂商联系人');
        $data['supplier_phone'] = trim(I('POST.supplier_phone'));

        $this->checkstatus(judgeEmpty(trim(I('POST.sign_date'))), '请补充签订日期');
        $data['sign_date'] = trim(I('POST.sign_date'));

        $this->checkstatus(judgeEmpty(trim(I('POST.end_date'))), '合同截止日期');
        $data['end_date'] = trim(I('POST.end_date'));

        $this->checkstatus(judgeEmpty(trim(I('POST.archives_num'))), '请输入档案编号');
        $data['archives_num'] = trim(I('POST.archives_num'));

        $data['check_date'] = trim(I('POST.check_date'));
        $data['archives_manager'] = trim(I('POST.archives_manager'));
        $data['contract_content'] = trim(I('POST.contract_content'));

        $result['status'] = C('YES_STATUS');
        $result['data'] = $data;
        return $result;
    }

    /**
     * 获取厂商详细信息
     * @param $olsid int 厂商id
     * @return array
     * */
    public function getSuppliersBasic($olsid)
    {
        $where['olsid'] = array('EQ', $olsid);
        $files = 'olsid,sup_name,sup_num,ECC_code,sup_abbr,is_supplier,is_manufacturer,is_repair,is_insurance,salesman_name,
        salesman_phone,artisan_name,artisan_phone,fax_number,email,provinces,city,areas,address,break,adduser,adddate,status';
        $suppliers = $this->DB_get_one('offline_suppliers', $files, $where);
        $suppliers['adddate'] = getHandleTime($suppliers['adddate']);
        $suppliers['suppliers_type'] = '';
        if ($suppliers['is_supplier'] == C('YES_STATUS')) {
            $suppliers['suppliers_type'] = '供应商、';
        }
        if ($suppliers['is_manufacturer'] == C('YES_STATUS')) {
            $suppliers['suppliers_type'] .= '生产商、';
        }
        if ($suppliers['is_repair'] == C('YES_STATUS')) {
            $suppliers['suppliers_type'] .= '维修商、';
        }
        if ($suppliers['is_insurance'] == C('YES_STATUS')) {
            $suppliers['suppliers_type'] .= '维保商';
        }
        $suppliers['suppliers_type'] = trim($suppliers['suppliers_type'], '、');

        $suppliers['region'] = '';

        if ($suppliers['provinces']) {
            $provinces = $this->DB_get_one('base_provinces', 'province',
                array('provinceid' => $suppliers['provinces']));
            $suppliers['region'] = $provinces['province'];
            if ($suppliers['city']) {
                $city = $this->DB_get_one('base_city', 'city', array('cityid' => $suppliers['city']));
                $suppliers['region'] .= '/' . $city['city'];
                if ($suppliers['areas']) {
                    $area = $this->DB_get_one('base_areas', 'area', array('areaid' => $suppliers['areas']));
                    $suppliers['region'] .= '/' . $area['area'];
                }
            }
        }
        switch ($suppliers['status']) {
            case C('OPEN_STATUS'):
                $suppliers['statusName'] = '启用';
                break;
            case C('SHUT_STATUS'):
                $suppliers['statusName'] = '禁用';
                break;
        }
        return $suppliers;
    }

    /**
     * 获取合同详细信息
     * @param $contract_type int 合同类型
     * @param $contract_id int 合同id
     * @return array
     * */
    public function getContractBasic($contract_type, $contract_id)
    {
        $where['contract_id'] = array('EQ', $contract_id);
        $where['contract_type'] = array('EQ', $contract_type);
        $typeResult = $this->translation_Contract($contract_type);
        $table = $typeResult['table'] . '_contract';
        $contract = $this->DB_get_one($table, '', $where);
        $contract['guarantee_date'] = HandleEmptyNull($contract['guarantee_date']);
        $contract['sign_date'] = HandleEmptyNull($contract['sign_date']);
        $contract['end_date'] = HandleEmptyNull($contract['end_date']);
        $contract['check_date'] = HandleEmptyNull($contract['check_date']);
        $contract['contract_type_name'] = $typeResult['type_name'];
        return $contract;
    }


    /**
     * 获取付款明细
     * @param $contract_type int 合同类型
     * @param $contract_id int 合同id
     * @return array
     * */
    public function getContractPayBasic($contract_type, $contract_id)
    {
        $where['contract_id'] = array('EQ', $contract_id);
        $typeResult = $this->translation_Contract($contract_type);
        $table = $typeResult['table'] . '_contract_pay';
        $files = 'pay_period,contract_id,estimate_pay_date,real_pay_date,pay_amount,pay_status,pay_user,add_user,add_time';
        $pay = $this->DB_get_all($table, $files, $where);
        foreach ($pay as &$pay_val) {
            $pay_val['estimate_pay_date'] = HandleEmptyNull($pay_val['estimate_pay_date']);
            $pay_val['real_pay_date'] = HandleEmptyNull($pay_val['real_pay_date']);
            if ($pay_val['pay_status'] == C('YES_STATUS')) {
                $pay_val['pay_status_name'] = '已付款';
            } else {
                $pay_val['pay_status_name'] = '未付款';
            }
        }
        return $pay;
    }

    /**
     * 获取采购配件合同设备明细
     * @param $contract_id int 合同id
     * @return array
     * */
    public function getContractPayAssetsBasic($recordArr)
    {
        $join = 'LEFT JOIN sb_purchases_tender_record AS R ON R.record_id=D.record_id';
        $where['D.record_id'] = ['IN', $recordArr];
        $where['D.final_select'] = ['EQ', C('YES_STATUS')];
        $data = $this->DB_get_all_join('purchases_tender_detail', 'D',
            'D.factory_name,D.assets_name,D.model,D.brand,D.company_price,R.nums', $join, $where, '', '', '');
        foreach ($data as &$value) {
            $value['sum'] = $value['company_price'] * $value['nums'];
        }
        return $data;
    }

    /**
     * 获取采购补录合同设备
     * @param $contract_id string 设备id组
     * @return array
     * */
    public function getAssetsRecordList($assid)
    {

        $where['assid'] = ['IN', $assid];
        $fileds = '';
        $data = $this->DB_get_all('assets_info', $fileds, $where);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $baseSetting = [];
//        include APP_PATH . "Common/cache/category.cache.php";
//        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        foreach ($data as &$one) {
            $one['factorydate'] = HandleEmptyNull($one['factorydate']);
            $one['storage_date'] = HandleEmptyNull($one['storage_date']);
            $one['helpcatid'] = $baseSetting['assets']['assets_helpcat']['value'][$one['helpcatid']];
            $one['financeid'] = $baseSetting['assets']['assets_finance']['value'][$one['financeid']];
            $one['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$one['capitalfrom']];
            $one['assfromid'] = $baseSetting['assets']['assets_assfrom']['value'][$one['assfromid']];
        }
        return $data;
    }


    /**
     * 获取厂商上传文件
     * @param $outid int 厂商id
     * @return  array
     * */
    public function getSuppliersFileList($olsid)
    {
        $where['olsid'] = array('EQ', $olsid);
        $where['type'] = array('NEQ', 7);
        $file = $this->DB_get_all('offline_suppliers_file', 'fileid,name,url,adddate,ext,type,record_date,term_date',
            $where);
        $newFile = [];
        if ($file) {
            foreach ($file as &$fileV) {
                $newFile[$fileV['type']]['fileid'] = $fileV['fileid'];
                $newFile[$fileV['type']]['name'] = $fileV['name'];
                $newFile[$fileV['type']]['url'] = $fileV['url'];
                $newFile[$fileV['type']]['adddate'] = getHandleTime($fileV['adddate']);
                $newFile[$fileV['type']]['record_date'] = $fileV['record_date'] ? getHandleTime($fileV['record_date']) : '';
                $newFile[$fileV['type']]['term_date'] = $fileV['term_date'] ? getHandleTime($fileV['term_date']) : '';
                $newFile[$fileV['type']]['ext'] = $fileV['ext'];
                $newFile[$fileV['type']]['type'] = $fileV['type'];
                $newFile[$fileV['type']]['operation'] = '<div class="layui-btn-group">';
                $supplement = 'data-path="' . $fileV['url'] . '" data-name="' . $fileV['name'] . '.' . $fileV['ext'] . '"';
                $newFile[$fileV['type']]['operation'] .= $this->returnListLink('下载', '', '',
                    C('BTN_CURRENCY') . ' downFile', '', $supplement);
                $newFile[$fileV['type']]['operation'] .= $this->returnListLink('预览', '', '',
                    C('BTN_CURRENCY') . ' layui-btn-normal showFile', '', $supplement);
                $newFile[$fileV['type']]['operation'] .= '</div>';
            }
        }
        return $newFile;
    }

//    授权文件

    /**
     * 获取厂商上传文件
     * @param $outid int 厂商id
     * @return  array
     * */
    public function getAuthSuppliersFileList($olsid)
    {
        $file = $this->DB_get_all('offline_suppliers_file', '', ['olsid' => $olsid,['type'=>7]]);
        $newFile = [];
        if ($file) {
            foreach ($file as $k => $v) {
                $newFile[$k]['fileid'] = $v['fileid'];
                $newFile[$k]['name'] = $v['name'];
                $newFile[$k]['url'] = $v['url'];
                $newFile[$k]['ext'] = $v['ext'];
                $newFile[$k]['adddate'] = getHandleTime($v['adddate']);
                $newFile[$k]['record_date'] = $v['record_date'] ? getHandleTime($v['record_date']) : '';
                $newFile[$k]['term_date'] = $v['term_date'] ? getHandleTime($v['term_date']) : '';
                $newFile[$k]['operation'] = '<div class="layui-btn-group">';
                $supplement = 'data-path="' . $v['url'] . '" data-name="' . $v['name'].'" data-file-id="'.$v['fileid'].'"';
//                $newFile[$k]['operation'] .= $this->returnListLink('下载', '', '', C('BTN_CURRENCY') . ' downFile', '',
//                    $supplement);
                $newFile[$k]['operation'] .= $this->returnListLink('预览', '', '',
                    C('BTN_CURRENCY') . ' layui-btn-normal showAuthFile', '', $supplement);
                $newFile[$k]['operation'] .= $this->returnListLink('删除', '', '',
                    C('BTN_CURRENCY') . ' layui-btn-danger delFile', '', $supplement);
                $newFile[$k]['operation'] .= '</div>';
            }
        }
        return $newFile;
    }

    /**
     * 获取上合同传文件
     * @param $contract_type int 合同类型
     * @param contract_id int 合同id
     * @return  array
     * */
    public function getContractFileList($contract_type, $contract_id)
    {
        $where['contract_id'] = array('EQ', $contract_id);

        $typeResult = $this->translation_Contract($contract_type);
        $table = $typeResult['table'] . '_contract_file';

        $file = $this->DB_get_all($table, 'file_name,save_name,file_type,file_url,add_user,add_time', $where);
        if ($file) {
            foreach ($file as &$fileValue) {
                $fileValue['operation'] = '<div class="layui-btn-group">';
                $supplement = 'data-path="' . $fileValue['file_url'] . '" data-name="' . $fileValue['file_name'] . '"';
                $fileValue['operation'] .= $this->returnListLink('下载', '', '', C('BTN_CURRENCY') . ' downFile', '',
                    $supplement);
                if ($fileValue['file_type'] != 'doc' && $fileValue['file_type'] != 'docx') {
                    $fileValue['operation'] .= $this->returnListLink('预览', '', '',
                        C('BTN_CURRENCY') . ' layui-btn-normal showFile', '', $supplement);
                }
                $fileValue['operation'] .= '</div>';
            }
        }
        return $file;
    }


    /**
     * 上传文件
     * @param $dir string 保存的文件名
     * @param $style array 允许上传的格式
     * @return array
     * */
    public function uploadfile($dir = '', $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'jpg', 'png', 'jpeg', 'pdf'))
    {
        if ($_FILES['file']) {
            $Tool = new ToolController();
            if ($dir) {
                $dirName = $dir;
            } else {
                $dirName = $this->Controller;
            }
            $info = $Tool->upFile($style, $dirName);
            if ($info['status'] == C('YES_STATUS')) {
                // 上传成功 获取上传文件信息
                $resule['status'] = 1;
                $resule['msg'] = '上传成功';
                $resule['path'] = $info['src'];
                $resule['title'] = $info['title'];
                $resule['formerly'] = $info['formerly'];
                $resule['ext'] = $info['ext'];
                $resule['size'] = $info['size'];
            } else {
                // 上传错误提示错误信息
                $resule['status'] = -1;
                $resule['msg'] = $info['msg'];
            }
        } else {
            // 上传错误提示错误信息
            $resule['status'] = -1;
            $resule['msg'] = '未接收到文件';
        }
        return $resule;
    }

    public function addAuthFile($olsid)
    {
        $fileid = I('POST.fileid');
        $type = I('post.auth_type');
        $ext = I('post.auth_ext');
        $path = I('post.auth_path');
        $file_name = I('post.auth_file_name');
        $record_date = I('post.auth_record_date');
        $term_date = I('post.auth_term_date');
        $addAll = [];
        foreach ($path as $k => $v) {
            $data[$k]['olsid'] = $olsid;
            $data[$k]['type'] = $type[$k];
            $data[$k]['ext'] = $ext[$k];
            $data[$k]['name'] = $file_name[$k];
            $data[$k]['url'] = $path[$k];
            $data[$k]['record_date'] = strtotime($record_date[$k]);
            $data[$k]['term_date'] = strtotime($term_date[$k]);
            if ($fileid[$k]) {
                $data[$k]['edituser'] = session('username');
                $data[$k]['editdate'] = time();
                $this->updateData('offline_suppliers_file', $data[$k], array('fileid' => $fileid[$k]));
            } else {
                $data[$k]['adduser'] = session('username');
                $data[$k]['adddate'] = time();
                $addAll[] = $data[$k];
            }
        }
        $this->insertDataALL('offline_suppliers_file', $addAll);
    }

    /**
     * 厂商文件入库
     * @param $olsid int 厂家id
     * */
    public function addSupplierFile($olsid)
    {
        $fileid = explode('|', rtrim(I('POST.fileid'), '|'));
        $name = explode('|', rtrim(I('POST.name'), '|'));
        $path = explode('|', rtrim(I('POST.path'), '|'));
        $ext = explode('|', rtrim(I('POST.ext'), '|'));
        $fileType = explode('|', rtrim(I('POST.fileType'), '|'));
        $record_date = explode('|', rtrim(I('POST.startDate'), '|'));
        $term_date = explode('|', rtrim(I('POST.endDate'), '|'));
        $addAll = [];
        $data = [];
        foreach ($path as $k => $v) {
            if (!$v) {
                continue;
            } else {
                $data[$k]['olsid'] = $olsid;
                $data[$k]['name'] = $name[$k];
                $data[$k]['url'] = $v;
                $data[$k]['ext'] = $ext[$k];
                $data[$k]['name'] = $name[$k];
                $data[$k]['record_date'] = strtotime($record_date[$k]);
                $data[$k]['term_date'] = strtotime($term_date[$k]);
                $data[$k]['type'] = $fileType[$k];
                if ($fileid[$k]) {
                    $data[$k]['edituser'] = session('username');
                    $data[$k]['editdate'] = time();
                    $this->updateData('offline_suppliers_file', $data[$k], array('fileid' => $fileid[$k]));
                } else {
                    $data[$k]['adduser'] = session('username');
                    $data[$k]['adddate'] = time();
                    $addAll[] = $data[$k];
                }
            }
        }
        if ($addAll) {
            $this->insertDataALL('offline_suppliers_file', $addAll);
        }
    }

    /**
     * 合同文件入库
     * @param $contract_id int 合同id
     * */
    public function addContractFile($contract_id)
    {
        $file_name = explode('|', rtrim(I('POST.file_name'), '|'));
        $save_name = explode('|', rtrim(I('POST.save_name'), '|'));
        $file_url = explode('|', rtrim(I('POST.file_url'), '|'));
        $file_type = explode('|', rtrim(I('POST.file_type'), '|'));
        $file_size = explode('|', rtrim(I('POST.file_size'), '|'));
        $addAll = [];
        $data = [];
        foreach ($file_name as $k => $v) {
            if (!$v) {
                continue;
            } else {
                $data[$k]['contract_id'] = $contract_id;
                $data[$k]['file_name'] = $v;
                $data[$k]['save_name'] = $save_name[$k];
                $data[$k]['file_type'] = $file_type[$k];
                $data[$k]['file_size'] = $file_size[$k];
                $data[$k]['file_url'] = $file_url[$k];
                $data[$k]['add_user'] = session('username');
                $data[$k]['add_time'] = getHandleDate(time());
                $addAll[] = $data[$k];
            }
        }
        if ($addAll) {
            $result = $this->translation_Contract(trim(I('POST.contract_type')));
            $table = $result['table'] . '_contract_file';
            $this->insertDataALL($table, $addAll);
        }
    }

    /**
     * 合同付款明细入库
     * @param $contract_id int 合同id
     * */
    public function addContractPay($contract_id)
    {
        $pay_id = explode('|', rtrim(I('POST.pay_id'), '|'));
        $pay_period = explode('|', rtrim(I('POST.phase'), '|'));
        $estimate_pay_date = explode('|', rtrim(I('POST.estimate_pay_date'), '|'));
        $real_pay_date = explode('|', rtrim(I('POST.real_pay_date'), '|'));
        $pay_amount = explode('|', rtrim(I('POST.pay_amount'), '|'));
        $result = $this->translation_Contract(trim(I('POST.contract_type')));

        $supplier_id = trim(I('POST.supplier_id'));
        $supplier_name = trim(I('POST.supplier_name'));
        $contract_num = trim(I('POST.contract_num'));
        $contract_name = trim(I('POST.contract_name'));

        $table = $result['table'] . '_contract_pay';
        $addAll = [];
        $data = [];
        foreach ($pay_period as $k => $v) {
            if (!$v) {
                continue;
            } else {
                $data[$k]['contract_id'] = $contract_id;
                $data[$k]['pay_period'] = $v;
                $data[$k]['pay_amount'] = $pay_amount[$k];
                if ($real_pay_date[$k]) {
                    $data[$k]['real_pay_date'] = $real_pay_date[$k];
                } else {
                    $data[$k]['real_pay_date'] = null;
                }
                if ($estimate_pay_date[$k]) {
                    $data[$k]['estimate_pay_date'] = $estimate_pay_date[$k];
                } else {
                    $data[$k]['estimate_pay_date'] = null;
                }
                if ($data[$k]['real_pay_date']) {
                    $data[$k]['pay_status'] = C('YES_STATUS');
                } else {
                    $data[$k]['pay_status'] = C('NO_STATUS');
                }
                if ($pay_id[$k]) {
                    $data[$k]['edit_user'] = session('username');
                    $data[$k]['edit_time'] = getHandleDate(time());
                    $this->updateData($table, $data[$k], array('pay_id' => $pay_id[$k]));
                } else {
                    $data[$k]['supplier_id'] = $supplier_id;
                    $data[$k]['supplier_name'] = $supplier_name;
                    $data[$k]['contract_num'] = $contract_num;
                    $data[$k]['contract_name'] = $contract_name;
                    $data[$k]['add_user'] = session('username');
                    $data[$k]['add_time'] = getHandleDate(time());
                    $addAll[] = $data[$k];
                }
            }
        }
        if ($addAll) {
            $this->insertDataALL($table, $addAll);
        }
    }


    /**
     * 记录合同ID关联至维修表
     * @param $contract_id int 合同id
     * @param $joinedRepair string 维修明细单
     * */
    public function setContractIdRepair($contract_id, $joinedRepair)
    {
        $data['contract_id'] = $contract_id;
        $where['repid'] = ['IN', $joinedRepair];
        $this->updateData('repair', $data, $where);
    }

    /**
     * 记录合同ID关联至设备表
     * @param $contract_id int 合同id
     * @param $joinedRepair string 设备明细(补录)单
     * */
    public function addContractRecordAssets($contract_id, $joinedRecordAssets)
    {
        $data['acid'] = $contract_id;
        $where['assid'] = ['IN', $joinedRecordAssets];
        $this->updateData('assets_info', $data, $where);
    }

    /**
     * 合同采购设备明细入库
     * @param $contract_id int 合同id
     * */
    public function addContractAssets($contract_id)
    {

        $assets_name = explode('|', rtrim(I('POST.addAssets_assets'), '|'));
        if (!$assets_name) {
            //无新增结束 避免出现sql IN 空数据BUG
            die;
        }
        $assets_id = explode('|', rtrim(I('POST.assets_id'), '|'));
        $supplier_id = explode('|', rtrim(I('POST.addAssets_supplier_id'), '|'));
        $supplier = explode('|', rtrim(I('POST.addAssets_supplier'), '|'));
        $model = explode('|', rtrim(I('POST.addAssets_model'), '|'));
        $nums = explode('|', rtrim(I('POST.addAssets_num'), '|'));
        $market_price = explode('|', rtrim(I('POST.addAssets_price'), '|'));
        $pay_amount = explode('|', rtrim(I('POST.pay_amount'), '|'));
        $assets_name_arr = [];
        foreach ($assets_name as &$assets_name_v) {
            $assets_name_arr[] = $assets_name_v;
        }
        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        $where['assets'] = ['IN', $assets_name_arr];
        $where['status'] = ['EQ', C('OPEN_STATUS')];
        $assetsData = $this->DB_get_all('dic_assets', 'assets,catid,assets_category,unit', $where);
        $getAssetsData = [];
        if ($assetsData) {
            foreach ($assetsData as $assetsData_v) {
                $getAssetsData[$assetsData_v['assets']]['catid'] = $assetsData_v['catid'];
                $getAssetsData[$assetsData_v['assets']]['unit'] = $assetsData_v['unit'];
                $assets_category = explode(',', $assetsData_v['assets_category']);
                foreach ($assets_category as &$category_value) {
                    $getAssetsData[$assetsData_v['assets']]['is_firstaid'] = $category_value == 'is_firstaid' ? C('YES_STATUS') : C('NO_STATUS');
                    $getAssetsData[$assetsData_v['assets']]['is_special'] = $category_value == 'is_special' ? C('YES_STATUS') : C('NO_STATUS');
                    $getAssetsData[$assetsData_v['assets']]['is_metering'] = $category_value == 'is_metering' ? C('YES_STATUS') : C('NO_STATUS');
                    $getAssetsData[$assetsData_v['assets']]['is_qualityAssets'] = $category_value == 'is_qualityAssets' ? C('YES_STATUS') : C('NO_STATUS');
                    $getAssetsData[$assetsData_v['assets']]['is_benefit'] = $category_value == 'is_benefit' ? C('YES_STATUS') : C('NO_STATUS');
                    $getAssetsData[$assetsData_v['assets']]['is_lifesupport'] = $category_value == 'is_lifesupport' ? C('YES_STATUS') : C('NO_STATUS');
                }
            }
        }
        $addAll = [];
        $data = [];
        foreach ($assets_name as $k => $v) {
            if (!$v) {
                continue;
            } else {
                $data[$k]['contract_id'] = $contract_id;
                $data[$k]['assets_name'] = $v;
                $data[$k]['nums'] = $nums[$k];
                $data[$k]['model'] = $model[$k];
                $data[$k]['supplier_id'] = $supplier_id[$k];
                $data[$k]['supplier'] = $supplier[$k];
                $data[$k]['market_price'] = $market_price[$k];
                $data[$k]['pay_amount'] = $pay_amount[$k];
                $data[$k]['catid'] = $getAssetsData[$v]['catid'];
                $data[$k]['unit'] = $getAssetsData[$v]['unit'];
                $data[$k]['is_firstaid'] = $getAssetsData[$v]['is_firstaid'];
                $data[$k]['is_special'] = $getAssetsData[$v]['is_special'];
                $data[$k]['is_metering'] = $getAssetsData[$v]['is_metering'];
                $data[$k]['is_qualityAssets'] = $getAssetsData[$v]['is_qualityAssets'];
                $data[$k]['is_benefit'] = $getAssetsData[$v]['is_benefit'];
                $data[$k]['is_lifesupport'] = $getAssetsData[$v]['is_lifesupport'];
                $data[$k]['total_price'] = $nums[$k] * $market_price[$k];
                if ($data[$k]['real_pay_date']) {
                    $data[$k]['pay_status'] = C('YES_STATUS');
                } else {
                    $data[$k]['pay_status'] = C('NO_STATUS');
                }
                if ($assets_id[$k]) {
                    $data[$k]['edit_user'] = session('username');
                    $data[$k]['edit_time'] = getHandleTime(time());
                    $this->updateData('purchases_depart_apply_assets', $data[$k], array('assets_id' => $assets_id[$k]));
                } else {
                    $data[$k]['add_user'] = session('username');
                    $data[$k]['add_time'] = getHandleTime(time());
                    $addAll[] = $data[$k];
                }
            }
        }
        if ($addAll) {
            $this->insertDataALL('purchases_depart_apply_assets', $addAll);
        }
    }

    /**
     * 获取厂商编号
     * @return string
     * */
    public function getSupNum()
    {
        $count = $this->DB_get_count('offline_suppliers');
        $sup_num = 'SUP' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        return $sup_num;
    }

    /*
    修改厂商类型为全类型
     */
    public function savetype()
    {
        $suppliers_type = I('post.suppliers_type');
        if (strstr($suppliers_type, '1')) {
            $data['is_supplier'] = 1;
        } else {
            $data['is_supplier'] = 0;
        }
        if (strstr($suppliers_type, '2')) {
            $data['is_manufacturer'] = 1;
        } else {
            $data['is_manufacturer'] = 0;
        }
        if (strstr($suppliers_type, '3')) {
            $data['is_repair'] = 1;
        } else {
            $data['is_repair'] = 0;
        }
        if (strstr($suppliers_type, '4')) {
            $data['is_insurance'] = 1;
        } else {
            $data['is_insurance'] = 0;
        }
        $olsids = I('post.olsids');
        $res = $this->updateData('offline_suppliers', $data, array('olsid' => array('IN', $olsids)));
        if ($res) {
            return array('status' => 1, 'msg' => '修改成功');
        }
        return array('status' => -1, 'msg' => '修改失败');
    }

    /**
     * 转译合同类型
     * @param $type INT 类型
     * @return array
     * */
    public function translation_Contract($type)
    {
        $result = [];
        switch ($type) {
            case C('CONTRACT_TYPE_SUPPLIER'):
                //采购类型
                $result['table'] = 'purchases';
                $result['type_name'] = C('CONTRACT_TYPE_SUPPLIER_NAME');
                break;
            case C('CONTRACT_TYPE_REPAIR'):
                //维修类型
                $result['table'] = 'repair';
                $result['type_name'] = C('CONTRACT_TYPE_REPAIR_NAME');
                break;
            case C('CONTRACT_TYPE_INSURANCE'):
                //维保类型
                $result['type_name'] = C('CONTRACT_TYPE_INSURANCE_NAME');
                $result['table'] = 'assets_insurance';
                break;
            case C('CONTRACT_TYPE_RECORD_ASSETS'):
                //采购类型(补录)
                $result['type_name'] = C('CONTRACT_TYPE_RECORD_ASSETS_NAME');
                $result['table'] = 'assets_record';
                break;
            case C('CONTRACT_TYPE_PARTS'):
                //采购类型(补录)
                $result['type_name'] = C('CONTRACT_TYPE_PARTS_NAME');
                $result['table'] = 'parts';
                break;
            default:
                die(json_encode(array('status' => -1, 'msg' => '未知合同类型')));
                break;
        }
        return $result;
    }


}
