<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/3/27
 * Time: 16:19
 */

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Think\Model;
use Think\Model\RelationModel;

class PurchasesContractModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'purchases_contract';
    private $MODULE = 'Purchases';
    private $Controller = 'Contract';

    //合同列表
    public function contractList()
    {
        $supplier_name = I('POST.supplier_name');
        $assets_name = I('POST.assets_name');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'record_id';
        $hospital_id = session('current_hospitalid');
        $where['B.hospital_id'] = $hospital_id;
        $where['A.is_delete'] = ['NEQ', C('YES_STATUS')];
        $where['A.final_select'] = ['EQ', C('YES_STATUS')];
        if ($assets_name) {
            $where['A.assets_name'] = ['LIKE', "%$assets_name%"];
        }
        if ($supplier_name) {
            $where['A.supplier_name'] = ['LIKE', "%$supplier_name%"];
        }
        //已生成的不需要获取
        $contractWhere['is_delete'] = ['NEQ', C('YES_STATUS')];
        $contractWhere['hospital_id'] = $hospital_id;
        $contract = $this->DB_get_one('purchases_contract', 'GROUP_CONCAT(record_id) AS record_id', $contractWhere);
        if ($contract['record_id'] != NULL) {
            $where['A.record_id'] = ['NOT IN', $contract['record_id']];
        }
        $join = "LEFT JOIN sb_purchases_tender_record AS B ON A.record_id = B.record_id";
        $fileds = 'A.record_id,A.supplier_id,A.supplier_name,A.factory_id,A.factory_name,A.assets_id,A.assets_name,A.model,A.brand,A.company_price,A.guarantee_year';
        $total = $this->DB_get_count_join('purchases_tender_detail','A',$join, $where);
        $data = $this->DB_get_all_join('purchases_tender_detail','A', $fileds,$join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $record_id = [];
        foreach ($data as &$value) {
            $record_id[] = $value['record_id'];
        }
        $record = $this->DB_get_all('purchases_tender_record', 'record_id,nums,brand',array('record_id'=>array('in',$record_id)));
        if (!$record) {
            $result['msg'] = '无招标记录';
            $result['code'] = 400;
            return $result;
        }

        $recordData = [];
        foreach ($record as $recordV) {
            $recordData[$recordV['record_id']]['nums'] = $recordV['nums'];
            $recordData[$recordV['record_id']]['brand'] = $recordV['brand'];
        }
        foreach ($data as &$value) {
            $value['nums'] = $recordData[$value['record_id']]['nums'];
            $value['brand'] = $recordData[$value['record_id']]['brand'];
            $value['total_price'] = $value['nums'] * $value['company_price'];
            switch ($value['contract_type']) {
                case C('CONTRACT_TYPE_SUPPLIER'):
                    $value['contract_type'] = C('CONTRACT_TYPE_SUPPLIER_NAME');
                    break;
                case C('CONTRACT_TYPE_REPAIR'):
                    $value['contract_type'] = C('CONTRACT_TYPE_REPAIR_NAME');
                    break;
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
    public function addContract()
    {
        $hospital_id =  session('current_hospitalid');
        //验证合同基础参数是否完整
        $result = $this->checkContractData();
        if ($result['status'] != C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => $result['msg'])));
        }
        $data = $result['data'];
        $data['hospital_id'] = $hospital_id;
        $add = $this->insertData('purchases_contract', $data);
        if ($add) {
            $log['sup_name'] = $data['sup_name'];
            $text = getLogText('addOfflineSuppliers', $log);
            $this->addLog('user', M()->getLastSql(), $text, $add);
            //采购计划设备明细更新
            $where['final_select'] = ['EQ', C('YES_STATUS')];
            $where['record_id'] = ['IN', $data['record_id']];
            $assetsData = $this->DB_get_all('purchases_tender_detail', 'brand,model,supplier_id,company_price,supplier_name,factory_id,factory_name,assets_id,guarantee_year', $where);
            foreach ($assetsData as &$assetsV) {
                $updateData = [];
                $updateData['supplier_id'] = $assetsV['supplier_id'];
                $updateData['factory_id'] = $assetsV['factory_id'];
                $updateData['factory'] = $assetsV['factory_name'];
                $updateData['supplier'] = $assetsV['supplier_name'];
                $updateData['actually_brand'] = $assetsV['brand'];
                $updateData['model'] = $assetsV['model'];
                $updateData['buy_price'] = $assetsV['company_price'];
                $updateData['contract_id'] = $add;
                $updateData['contract_time'] = date('Y-m-d H:i:s');
                $this->updateData('purchases_depart_apply_assets', $updateData, array('assets_id' => $assetsV['assets_id']));
            }
            //付款明细入库
            $this->addContractPay($add,$hospital_id);
            //合同文件入库
            $this->addContractFile($add);
            //付款明细入库
            return array('status' => 1, 'msg' => '添加成功');
        } else {
            return array('status' => -1, 'msg' => '提交失败');
        }
    }

    /**
     * 验证合同信息
     * */
    public function checkContractData()
    {
        $this->checkstatus(judgeEmpty(trim(I('POST.record_id'))), '非法操作');
        $data['record_id'] = trim(I('POST.record_id'));
        $this->checkstatus(judgeEmpty(trim(I('POST.contract_num'))), '请补充合同编号');
        $data['contract_num'] = trim(I('POST.contract_num'));
        $this->checkstatus(judgeEmpty(trim(I('POST.contract_name'))), '请补充合同名称');
        $data['contract_name'] = trim(I('POST.contract_name'));
        if (I('POST.contract_id')) {
            $data['contract_id'] = I('POST.contract_id');
            $where['contract_id'] = ['NEQ', $data['contract_id']];
            $data['edit_user'] = session('username');
            $data['edit_time'] = date('Y-m-d H:i:s');
        } else {
            $data['add_user'] = session('username');
            $data['add_time'] = date('Y-m-d H:i:s');
        }
        $where['contract_num'] = ['EQ', $data['contract_num']];
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $contract = $this->DB_get_one('purchases_contract', 'contract_id', $where);
        if ($contract) {
            return array('status' => -1, 'msg' => '合同编号 ' . $data['contract_num'] . ' 已存在，请勿重复添加');
        }
        $this->checkstatus(judgeEmpty(trim(I('POST.contract_type'))), '请选择合同类型');
        $data['contract_type'] = trim(I('POST.contract_type'));

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

        $where_a['archives_num'] = ['EQ', $data['archives_num']];
        $where_a['is_delete'] = ['NEQ', C('YES_STATUS')];
        $contract_a = $this->DB_get_one('purchases_contract', 'contract_id', $where_a);
        if ($contract_a) {
            return array('status' => -1, 'msg' => '档案编号 ' . $data['archives_num'] . ' 已存在，请勿重复添加');
        }

        $data['check_date'] = trim(I('POST.check_date'));
        $data['archives_manager'] = trim(I('POST.archives_manager'));
        $data['contract_content'] = trim(I('POST.contract_content'));

        $result['status'] = C('YES_STATUS');
        $result['data'] = $data;
        return $result;
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
            $this->insertDataALL('purchases_contract_file', $addAll);
        }
    }

    /**
     * 合同付款明细入库
     * @param $contract_id int 合同id
     * */
    public function addContractPay($contract_id,$hospital_id)
    {
        $pay_id = explode('|', rtrim(I('POST.pay_id'), '|'));
        $pay_period = explode('|', rtrim(I('POST.phase'), '|'));
        $estimate_pay_date = explode('|', rtrim(I('POST.estimate_pay_date'), '|'));
        $real_pay_date = explode('|', rtrim(I('POST.real_pay_date'), '|'));
        $pay_amount = explode('|', rtrim(I('POST.pay_amount'), '|'));
        $contract_num = trim(I('POST.contract_num'));
        $contract_name = trim(I('POST.contract_name'));
        $supplier_id = trim(I('POST.supplier_id'));
        $supplier_name = trim(I('POST.supplier_name'));
        $addAll = [];
        $data = [];
        foreach ($pay_period as $k => $v) {
            if (!$v) {
                continue;
            } else {
                $data[$k]['contract_id'] = $contract_id;
                $data[$k]['hospital_id'] = $hospital_id;
                $data[$k]['pay_period'] = $v;
                $data[$k]['estimate_pay_date'] = $estimate_pay_date[$k];
                $data[$k]['real_pay_date'] = $real_pay_date[$k];
                $data[$k]['pay_amount'] = $pay_amount[$k];
                $data[$k]['supplier_id']=$supplier_id;
                $data[$k]['supplier_name']=$supplier_name;
                $data[$k]['contract_num']=$contract_num;
                $data[$k]['contract_name']=$contract_name;
                if ($data[$k]['real_pay_date']) {
                    $data[$k]['pay_status'] = C('YES_STATUS');
                } else {
                    $data[$k]['pay_status'] = C('NO_STATUS');
                }
                if ($pay_id[$k]) {
                    $data[$k]['edit_user'] = session('username');
                    $data[$k]['edit_time'] = getHandleTime(time());
                    $this->updateData('purchases_contract_pay', $data[$k], array('pay_id' => $pay_id[$k]));
                } else {
                    $data[$k]['add_user'] = session('username');
                    $data[$k]['add_time'] = getHandleTime(time());
                    $addAll[] = $data[$k];
                }
            }
        }
        if ($addAll) {
            $this->insertDataALL('purchases_contract_pay', $addAll);
        }
    }

    //获取供应商
    public function getSupplierArr()
    {
        $where['is_supplier'] = ['EQ', C('YES_STATUS')];
        $data = $this->DB_get_all('offline_suppliers', 'olsid,sup_name', $where);
        return $data;
    }

    /**
     * 获取中标厂商信息
     * @param $record_id int 招标记录ID
     * @return array
     * */
    public function getRecordBasic($record_id)
    {
        $join[0] = 'LEFT JOIN sb_offline_suppliers AS S ON S.olsid=D.supplier_id';
        $join[1] = 'LEFT JOIN sb_purchases_tender_record AS R ON R.record_id=D.record_id';
        $where['D.record_id'] = $record_id;
        $where['D.final_select'] = 1;
        $record = $this->DB_get_one_join('purchases_tender_detail', 'D', 'olsid,sup_name,salesman_name,salesman_phone,hospital_id', $join, $where);
        return $record;
    }

    /*
     * 获取招标记录明细
     * */
    public function getRecordAssetsBasic($recordArr)
    {
        $join = 'LEFT JOIN sb_purchases_tender_record AS R ON R.record_id=D.record_id';
        $where['D.record_id'] = ['IN', $recordArr];
        $where['D.final_select'] = ['EQ', C('YES_STATUS')];
        $data = $this->DB_get_all_join('purchases_tender_detail', 'D', 'D.factory_name,D.assets_name,D.model,D.brand,D.company_price,R.nums', $join, $where);
        foreach ($data as &$value) {
            $value['sum'] = $value['company_price'] * $value['nums'];
        }
        return $data;
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
            if ($info['status']==C('YES_STATUS')) {
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

}
