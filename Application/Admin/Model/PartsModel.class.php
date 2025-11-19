<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;
use Think\Model;
use Think\Model\RelationModel;

class PartsModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'parts_inware_record_detail';
    protected $MODULE = 'Repair';
    protected $Controller = 'RepairParts';


    //配件入库列表
    public function partsInWareList()
    {
        $inware_num = I('POST.inware_num');
//        $supplier_name = I('POST.supplier_name');
        $supplierId = I('POST.supplierId');
        $startDate = I('POST.startDate');
        $endDate = I('POST.endDate');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'DESC';
        $sort = I('POST.sort') ? I('POST.sort') : 'inwareid';
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $where['hospital_id'] = session('current_hospitalid');
        if ($inware_num) {
            $where['inware_num'] = ['LIKE', "%$inware_num%"];
        }
        if ($supplierId) {
            $where['supplier_id'] = $supplierId;
        }
        if ($startDate) {
            $where['buydate'][] = ['EGT', $startDate];
        }
        if ($endDate) {
            $where['buydate'][] = ['ELT', $endDate];
        }
        $total = $this->DB_get_count('parts_inware_record', $where);
        $data = $this->DB_get_all('parts_inware_record', '', $where, '', 'status asc,'.$sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $inwareid_arr = [];
        foreach ($data as &$val) {
            $inwareid_arr[] = $val['inwareid'];
        }
        $group = 'inwareid,parts,parts_model';
        $fields = 'inwareid,parts,parts_model,price,supplier_name,COUNT(*) AS sum';

        $inwareDetail = $this->DB_get_all('parts_inware_record_detail', $fields,['inwareid'=>['IN',$inwareid_arr]],$group);
        $num=0;
        $inwareData = [];
        foreach ($inwareDetail as &$detail) {
            $inwareData[$detail['inwareid']][$num]['supplier_name'] = htmlspecialchars_decode($detail['supplier_name']);
            $inwareData[$detail['inwareid']][$num]['parts'] = $detail['parts'];
            $inwareData[$detail['inwareid']][$num]['price'] = number_format($detail['price'],2, '.', '');
            $inwareData[$detail['inwareid']][$num]['parts_model'] = $detail['parts_model'];
            $inwareData[$detail['inwareid']][$num]['sum'] = $detail['sum'];
            $inwareData[$detail['inwareid']][$num]['total_price'] = number_format($detail['sum']*$detail['price'],2, '.', '');
            $num++;
        }

        $applyWhere['inwareid']=['IN',$inwareid_arr];
        $applyWhere['status']=['EQ',C('NO_STATUS')];
        $fields = 'inwareid,parts,parts_model,COUNT(*) AS sum';
        $inwareApply = $this->DB_get_all('parts_inware_record_apply', $fields,$applyWhere,$group);
        $num=0;
        $inwareApplyData = [];
        foreach ($inwareApply as &$apply) {
            $inwareApplyData[$apply['inwareid']][$num]['parts'] = $apply['parts'];
            $inwareApplyData[$apply['inwareid']][$num]['parts_model'] = $apply['parts_model'];
            $inwareApplyData[$apply['inwareid']][$num]['sum'] = $apply['sum'];
            $num++;
        }
        $addMenu = get_menu($this->MODULE, $this->Controller, 'partsInWare');
        foreach ($data as &$val) {
            $val['buydate'] = HandleEmptyNull($val['buydate']);
            $detailsUrl = get_url() . '?action=showPartsInwareDetails&inwareid=' . $val['inwareid'];
            $val['operation'] = $this->returnListLink('查看', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($val['status'] == C('YES_STATUS')) {
                //查询是否已出库
                if($val['repid']){
                    $out = $this->DB_get_one('parts_outware_record','status',['repid'=>$val['repid']]);
                    if($out['status'] == 0){
                        //未出库，可修改
                        $val['operation'] = $this->returnListLink('修改', $detailsUrl, 'editInware', C('BTN_CURRENCY') . ' layui-btn-normal');
                    }else{
                        $val['operation'] = $this->returnListLink('查看', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-primary');
                    }
                }else{
                    $out_num = $this->DB_get_count('parts_inware_record_detail',['status'=>1,'inwareid'=>$val['inwareid']]);
                    if($out_num == 0){
                        //未出库，可修改
                        $val['operation'] = $this->returnListLink('修改', $detailsUrl, 'editInware', C('BTN_CURRENCY') . ' layui-btn-normal');
                    }else{
                        $val['operation'] = $this->returnListLink('查看', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-primary');
                    }
                }
                $val['inwareDetail'] = $inwareData[$val['inwareid']];
            } else {
                $val['inwareDetail'] = $inwareApplyData[$val['inwareid']];
                if ($addMenu) {
                    $val['operation'] = $this->returnListLink('入库', $addMenu['actionurl'], 'partsInWare', C('BTN_CURRENCY'));;
                } else {
                    $val['operation'] = $this->returnListLink('待入库', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                }
            }
            //print_r($val['inwareDetail']);
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //获取可纳入的配件字典列表
    public function canJoinPartsList()
    {
        $joinedParts = I('POST.joinedParts');
        $parts = I('POST.parts');
        $supplier_id = I('POST.supplier_id');
        $dic_category = I('POST.dic_category');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        if (!session('current_hospitalid')) {
            $result['msg'] = '未选中医院';
            $result['code'] = 400;
            return $result;
        }
        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        $where['status'] = ['EQ', C('OPEN_STATUS')];
        if ($parts) {
            $where['parts'] = array('LIKE', "%$parts%");
        }
        if ($dic_category) {
            $where['dic_category'] = array('LIKE', "%$dic_category%");
        }
        if ($supplier_id) {
            $where['supplier_id'] = ['EQ', $supplier_id];
        }
        if ($joinedParts) {
            $where['dic_partsid'][] = array('NOT IN', $joinedParts);
        }
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $total = $this->DB_get_count('dic_parts', $where);
        $data = $this->DB_get_all('dic_parts', '', $where, '', 'dic_partsid DESC', $offset . "," . $limit);
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

    //配件入库操作
    public function partsInWare()
    {
        $addtime = trim(I('POST.addtime'));
        $remark = trim(I('POST.remark'));
        $supplier_id = trim(I('POST.supplier_id'));
        $this->checkstatus(judgeEmpty($supplier_id), '请选择供应商');
        $this->checkstatus(judgeEmpty($addtime), '请补充入库日期');
        $partsResult = $this->getInWarePartsData();
        if ($partsResult['status'] == C('SUCCESS_STATUS')) {
            $data['hospital_id'] = session('current_hospitalid');
            $data['inware_num'] = $this->getInwareNum();
            $name = $this->DB_get_one('offline_suppliers', 'sup_name', ['olsid' => $supplier_id]);
            $data['supplier_name'] = $name['sup_name'];
            $data['supplier_id'] = $supplier_id;
            $data['buydate'] = $addtime;
            $data['total_price'] = $partsResult['totalPrice'];
            $data['sum'] = $partsResult['totalNum'];
            $data['remark'] = $remark;
            $data['status'] = C('YES_STATUS');
            $add = $this->insertData('parts_inware_record', $data);
            if ($add) {
                $log['inware_num'] = $data['inware_num'];
                $text = getLogText('addPartsInware', $log);
                $this->addLog('sb_parts_inware_record', M()->getLastSql(), $text, $add);
                //创建暂存采购合同记录
                $this->setInwareContract($data, $add);
                //配件明细入库
                $this->add_inware_record_detail($partsResult['data'], $add);
                //查看是否有采购申请单 有这一批入库的同配件型号的 修改下申请单数据
                $this->changeInwareApply($add);
                return array('status' => 1, 'msg' => '入库记录成功');
            } else {
                return array('status' => -1, 'msg' => '入库记录失败');
            }
        } else {
            return array('status' => $partsResult['status'], 'msg' => $partsResult['msg']);
        }
    }

    //入库采购单申请确认操作
    public function partsInWareApply()
    {
        $addtime = trim(I('POST.addtime'));
        $supplier_id = trim(I('POST.supplier_id'));
        $inwareid = trim(I('POST.inwareid'));
        $remark = trim(I('POST.remark'));
        $supplier_name = trim(I('POST.supplier_name'));
        $leader = trim(I('POST.leader'));
        $this->checkstatus(judgeNum($inwareid), '非法操作');
        $data = $this->DB_get_one('parts_inware_record', '', array('inwareid' => $inwareid));
        if (!$data) {
            die(json_encode(array('status' => -1, 'msg' => '无此申请单记录')));
        }
        if ($data['status'] == C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => '已录入请勿重复操作')));
        }
        //查询领用人
        $this->checkstatus(judgeEmpty($supplier_id), '请选择供应商');
        $this->checkstatus(judgeEmpty($addtime), '请补充入库日期');
        $partsResult = $this->getInWarePartsData();
        if ($partsResult['status'] == C('SUCCESS_STATUS')) {
            $data['buydate'] = $addtime;
            $data['total_price'] = $partsResult['totalPrice'];
            $data['sum'] = $partsResult['totalNum'];
            $data['supplier_name'] = $supplier_name;
            $data['supplier_id'] = $supplier_id;
            $data['remark'] = $remark;
            $data['status'] = C('YES_STATUS');
            $save = $this->updateData('parts_inware_record', $data, array('inwareid' => $inwareid));
            if ($save) {
                $log['inware_num'] = $data['inware_num'];
                $text = getLogText('addPartsInware', $log);
                $this->addLog('sb_parts_inware_record', M()->getLastSql(), $text, $inwareid);
                //创建暂存采购合同记录
                $this->setInwareContract($data, $inwareid);
                //修改申请单状态
                $this->updateData('parts_inware_record_apply', array('status' => C('YES_STATUS')), array('inwareid' => $inwareid));
                //配件明细入库
                $this->add_inware_record_detail($partsResult['data'], $inwareid, $leader, $data['repid']);
                //记录/更新 配件字典信息
                $this->addDicParts($partsResult['data']);
                //查看是否有采购申请单 有这一批入库的同配件型号的 修改下申请单数据
                $this->changeInwareApply($inwareid);
                return array('status' => 1, 'msg' => '入库记录成功');
            } else {
                return array('status' => -1, 'msg' => '入库记录失败');
            }
        } else {
            return array('status' => $partsResult['status'], 'msg' => $partsResult['msg']);
        }
    }

    //退回到入库前状态
    public function backup()
    {
        $inwareid = trim(I('POST.inwareid'));
        $inwareInfo = $this->DB_get_one('parts_inware_record','repid,status',['inwareid'=>$inwareid]);
        if($inwareInfo['status'] == 0){
            return array('status' => -1, 'msg' => '尚未进行过入库操作');
        }
        //查询是否已出库
        $out = $this->DB_get_one('parts_outware_record','status',['repid'=>$inwareInfo['repid']]);
        if($out['status'] == 1){
            return array('status' => -1, 'msg' => '该入库单已有配件出库，不允许进行回退操作');
        }
        //修改状态为未入库
        $res = $this->updateData('parts_inware_record',['status'=>0,'buydate'=>null,'sum'=>0,'total_price'=>0,'supplier_name'=>'','supplier_id'=>0],['inwareid'=>$inwareid]);
        if($res){
            if($inwareInfo['repid']){
                //有维修单的
                $this->updateData('parts_inware_record_apply',['status'=>0],['inwareid'=>$inwareid]);
                //删除parts_inware_record_detail的记录
                $this->deleteData('parts_inware_record_detail',['inwareid'=>$inwareid]);
            }
            return array('status' => 1, 'msg' => '回退成功');
        }else{
            return array('status' => -1, 'msg' => '回退状态失败');
        }
    }

    //删除入库单
    public function delInware()
    {
        $inwareid = trim(I('POST.inwareid'));
        $inwareInfo = $this->DB_get_one('parts_inware_record','repid,status',['inwareid'=>$inwareid]);
        if($inwareInfo['status'] == 0){
            return array('status' => -1, 'msg' => '尚未进行过入库操作');
        }
        if(!$inwareInfo['repid']){
            //删除入库单
            $this->updateData('parts_inware_record',['is_delete'=>1],['inwareid'=>$inwareid]);
            $this->deleteData('parts_inware_record_detail',['inwareid'=>$inwareid]);
        }
        return array('status' => 1, 'msg' => '删除入库单成功');
    }

    //微信端入库采购单申请确认操作
    public function wx_partsInWareApply()
    {
        $addtime = trim(I('POST.addtime'));
        $supplier_id = trim(I('POST.supplier_id'));
        $inwareid = trim(I('POST.inwareid'));
        $remark = trim(I('POST.remark'));
        $supplier_name = trim(I('POST.supplier_name'));
        $leader = trim(I('POST.leader'));
        $this->checkstatus(judgeNum($inwareid), '非法操作');
        $data = $this->DB_get_one('parts_inware_record', '', array('inwareid' => $inwareid));
        if (!$data) {
            die(json_encode(array('status' => -1, 'msg' => '无此申请单记录')));
        }
        if ($data['status'] == C('YES_STATUS')) {
            $is_existence = $this->DB_get_one('parts_inware_record_apply','applyid',array('inwareid'=>$inwareid,'status'=>'0'));
            if (!$is_existence) {
                die(json_encode(array('status' => -1, 'msg' => '已录入请勿重复操作')));
            }
        }
        //查询领用人
        $this->checkstatus(judgeEmpty($supplier_id), '请选择供应商');
        $this->checkstatus(judgeEmpty($addtime), '请补充入库日期');
        $partsResult = $this->getInWarePartsData();
        if ($partsResult['status'] == C('SUCCESS_STATUS')) {
            $data['buydate'] = $addtime;
            $data['total_price'] = $partsResult['totalPrice'];
            $data['sum'] = $data['sum']+$partsResult['totalNum'];
            $data['supplier_name'] = $supplier_name;
            $data['supplier_id'] = $supplier_id;
            $data['remark'] = $remark;
            $data['status'] = C('YES_STATUS');
            $save = $this->updateData('parts_inware_record', $data, array('inwareid' => $inwareid));
            if ($save) {
                $log['inware_num'] = $data['inware_num'];
                $text = getLogText('addPartsInware', $log);
                $this->addLog('sb_parts_inware_record', M()->getLastSql(), $text, $inwareid);
                //创建暂存采购合同记录
                $this->setInwareContract($data, $inwareid);
                //修改申请单状态
                $parts = "";
                foreach ($partsResult['data'] as $key => $value) {
                    $parts.=$value['parts'];
                }
                $parts = rtrim($parts,',');
                $this->updateData('parts_inware_record_apply', array('status' => C('YES_STATUS')), array('inwareid' => $inwareid,'parts'=>array('IN',$parts)));
                //配件明细入库
                $this->add_inware_record_detail($partsResult['data'], $inwareid, $leader, $data['repid']);
                //记录/更新 配件字典信息
                $this->addDicParts($partsResult['data']);
                //查看是否有采购申请单 有这一批入库的同配件型号的 修改下申请单数据
                $this->changeInwareApply($inwareid);
                return array('status' => 1, 'msg' => '入库记录成功');
            } else {
                return array('status' => -1, 'msg' => '入库记录失败','data'=>M()->getLastSql());
            }
        } else {
            return array('status' => $partsResult['status'], 'msg' => $partsResult['msg']);
        }
    }
    //配件出库列表
    public function partsOutWareList()
    {
        $outware_num = I('POST.outware_num');
        $leader = I('POST.leader');
        $startDate = I('POST.startDate');
        $endDate = I('POST.endDate');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'outwareid';
        $where['A.status'] = ['NEQ', C('DELETE_STATUS')];
        $where['A.hospital_id'] = session('current_hospitalid');
        if ($outware_num) {
            $where['A.outware_num'] = ['LIKE', "%$outware_num%"];
        }
        if ($leader) {
            $where['A.leader'] = ['LIKE', "%$leader%"];
        }
        if ($startDate) {
            $where['A.outdate'][] = ['EGT', $startDate];
        }
        if ($endDate) {
            $where['A.outdate'][] = ['ELT', $endDate];
        }
        //$total = $this->DB_get_count('parts_outware_record', $where);
        //$data = $this->DB_get_all('parts_outware_record', '', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        $where['C.is_delete'] = '0';
        $join[] = 'LEFT JOIN sb_repair AS B ON A.repid=B.repid';
        $join[] = 'LEFT JOIN sb_assets_info AS C ON A.assid=C.assid';
        $total = $this->DB_get_count_join('parts_outware_record', 'A', $join, $where);
        $data = $this->DB_get_all_join('parts_outware_record', 'A', 'A.*', $join, $where, '','outdate,'.$sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }


        $outwareid_arr = [];
        foreach ($data as &$val) {
            $outwareid_arr[] = $val['outwareid'];
        }
        $group = 'parts,parts_model';
        $fields = 'parts,parts_model,COUNT(*) AS max_sum';
        $inwareDetail = $this->DB_get_all('parts_inware_record_detail', $fields,array('hospital_id'=>session('current_hospitalid'),'status'=>C('NO_STATUS')),$group);
        $inwareData = [];
        foreach ($inwareDetail as &$value) {
            $inwareData[$value['parts']][$value['parts_model']]['max_sum']=$value['max_sum'];
        }

        $group = 'outwareid,parts,parts_model';
        $fields = 'outwareid,parts,parts_model,COUNT(*) AS sum';

        $outwareDetail = $this->DB_get_all('parts_outware_record_detail', $fields,['outwareid'=>['IN',$outwareid_arr]],$group);
        $num=0;
        $outwareData = [];
        foreach ($outwareDetail as &$detail) {
            $outwareData[$detail['outwareid']][$num]['parts'] = $detail['parts'];
            $outwareData[$detail['outwareid']][$num]['parts_model'] = $detail['parts_model'];
            $outwareData[$detail['outwareid']][$num]['sum'] = $detail['sum'];
            $num++;
        }

        $applyWhere['outwareid']=['IN',$outwareid_arr];
        $applyWhere['status']=['EQ',C('NO_STATUS')];
        $outwareApply = $this->DB_get_all('parts_outware_record_apply', $fields,$applyWhere,$group);
        $num=0;
        $outwareApplyData = [];
        $excess_data = [];
        foreach ($outwareApply as &$apply) {
            $outwareApplyData[$apply['outwareid']][$num]['parts'] = $apply['parts'];
            $outwareApplyData[$apply['outwareid']][$num]['parts_model'] = $apply['parts_model'];
            $outwareApplyData[$apply['outwareid']][$num]['sum'] = $apply['sum'];
            $max_sum = $inwareData[$apply['parts']][$apply['parts_model']]?$inwareData[$apply['parts']][$apply['parts_model']]:0;
            if ($apply['sum']>$max_sum) {
                $excess_data[$apply['outwareid']] = 1;
            }
            $num++;
        }
        $outMenu = get_menu($this->MODULE, $this->Controller, 'partsOutWare');
        foreach ($data as &$val) {
            $val['outdate'] = HandleEmptyNull($val['outdate']);
            $detailsUrl = get_url() . '?action=showPartsOutwareDetails&outwareid=' . $val['outwareid'];
            $val['operation'] = $this->returnListLink('查看', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-primary');
            switch ($val['status']) {
                case C('YES_STATUS'):
                    $val['outwareDetail'] = $outwareData[$val['outwareid']];
                    $val['operation'] = $this->returnListLink('查看', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-primary');
                    break;
                case C('STATUS_APPROE_FAIL'):
                    $val['outwareDetail'] = $outwareData[$val['outwareid']];
                    $val['operation'] = $this->returnListLink('审批未通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-danger');
                    break;
                case C('NO_STATUS'):
                    $val['outwareDetail'] = $outwareApplyData[$val['outwareid']];
                    if ($outMenu) {
                        if ($excess_data[$val['outwareid']]) {
                            $val['operation'] = $this->returnListLink('库存不足', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                        }else{
                            $val['operation'] = $this->returnListLink('出库', $outMenu['actionurl'], 'partsOutWare', C('BTN_CURRENCY'));
                        }
                    } else {
                        $val['operation'] = $this->returnListLink('待出库', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                    }
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

    //获取可纳入的库存配件
    public function canJoinOutWareList()
    {
        $joinedDetailid = I('POST.joinedDetailid');
        $parts = I('POST.parts');
        $supplier_id = I('POST.supplier_id');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        if (!session('current_hospitalid')) {
            $result['msg'] = '未选中医院';
            $result['code'] = 400;
            return $result;
        }
        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        $where['status'] = ['EQ', C('NO_STATUS')];
        $where['leader'] = ['EQ', ''];
        if ($parts) {
            $where['parts'] = array('LIKE', "%$parts%");
        }
        if ($supplier_id) {
            $where['supplier_id'] = ['EQ', $supplier_id];
        }
        if ($joinedDetailid) {
            $joinedDetailid = str_replace('|', ',', $joinedDetailid);
            $where['detailid'][] = array('NOT IN', $joinedDetailid);
        }
        $group = 'parts,parts_model,price,supplier_id';
        $fields = 'COUNT(*) AS max_sum,GROUP_CONCAT(detailid) AS detailid,parts,supplier_name,supplier_id,price,unit,parts_model';
        $total = $this->DB_get_all('parts_inware_record_detail', 'COUNT(*) ', $where, $group);
        $data = $this->DB_get_all('parts_inware_record_detail', $fields, $where, $group, '', $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        foreach ($data as &$one) {
            $one['operation'] = $this->returnListLink('纳入', '', 'add', C('BTN_CURRENCY'));
        }
        $result["total"] = count($total);
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result["rows"] = $data;
        return $result;
    }

    //配件出库操作
    public function partsOutWare()
    {
        $outdate = trim(I('POST.addtime'));
        $leader = trim(I('POST.leader'));
        $remark = trim(I('POST.remark'));
        $this->checkstatus(judgeEmpty($leader), '请选中领用人');
        $this->checkstatus(judgeEmpty($outdate), '请补充出库日期');
        $partsResult = $this->checkOutWareData();
        if ($partsResult['status'] == C('SUCCESS_STATUS')) {
            $data['hospital_id'] = session('current_hospitalid');
            $data['outware_num'] = $this->getOutwareNum();
            $data['outdate'] = $outdate;
            $data['total_price'] = $partsResult['totalPrice'];
            $data['sum'] = $partsResult['totalNum'];
            $data['leader'] = $leader;
            $data['remark'] = $remark;
            $data['status'] = C('YES_STATUS');
            $data['addtime']=getHandleDate(time());
            $add = $this->insertData('parts_outware_record', $data);
            if ($add) {
                $log['outware_num'] = $data['outware_num'];
                $text = getLogText('addPartsOutware', $log);
                $this->addLog('sb_parts_outware_record', M()->getLastSql(), $text, $add);
                //配件明细入库
                $this->add_outware_record_detail($partsResult, $add, $leader);
                //查看是否有出库申请单 有这一批入库的同配件型号的 修改下出库申请单数据 todo
                return array('status' => 1, 'msg' => '入库记录成功');
            } else {
                return array('status' => -1, 'msg' => '入库记录失败');
            }
        } else {
            return array('status' => $partsResult['status'], 'msg' => $partsResult['msg']);
        }
    }

    //出库单申请确认操作
    public function partsOutWareApply()
    {
        $outdate = trim(I('POST.addtime'));
        $outwareid = trim(I('POST.outwareid'));
        $remark = trim(I('POST.remark'));
        $leader = trim(I('POST.leader'));
        $this->checkstatus(judgeNum($outwareid), '非法操作');
        $data = $this->DB_get_one('parts_outware_record', '', array('outwareid' => $outwareid));
        if (!$data) {
            die(json_encode(array('status' => -1, 'msg' => '无此申请单记录')));
        }
        if ($data['status'] == C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => '已录入请勿重复操作')));
        }
        $this->checkstatus(judgeEmpty($leader), '请选择领用人');
        $this->checkstatus(judgeEmpty($outdate), '请补充入库日期');
        $partsResult = $this->checkOutWareApplyData($data['repid']);
        if ($partsResult['status'] == C('SUCCESS_STATUS')) {
            $data['outdate'] = $outdate;
            $data['total_price'] = $partsResult['totalPrice'];
            $data['sum'] = $partsResult['totalNum'];
            $data['leader'] = $leader;
            $data['remark'] = $remark;
            $data['status'] = C('YES_STATUS');
            $data['addtime'] = getHandleDate(time());
            //获取审批信息
            $approveData=$this->getApproveData($data['repid'],$data['assid'],$data['total_price'],$data['sum']);
            $save = $this->updateData('parts_outware_record', $data, array('outwareid' => $outwareid));
            if ($save) {
                $log['outware_num'] = $data['outware_num'];
                $text = getLogText('addPartsOutware', $log);
                $this->addLog('sb_parts_outware_record', M()->getLastSql(), $text, $outwareid);
                //配件明细入库 判断解决方式 决定是否跳过审核
                $repairModel = new RepairModel();
                $repInfo = $repairModel->DB_get_one('repair','is_scene',array('repid'=>$data['repid']));
                if ($repInfo['is_scene']==1) {
                    $type = 'no';
                    $approveData = array();
                }else{
                    $type = 'yes';
                }
                $this->add_outware_record_detail($partsResult, $outwareid, $leader, $data['repid'], $approveData,$type);
                //修改申请单状态
                $this->updateData('parts_outware_record_apply', array('status' => C('YES_STATUS')), array('outwareid' => $outwareid));
                //出库记录成功，查询维修单当前状态信息
                $repInfo = $repairModel->DB_get_one('repair','repid,repnum,status,assid,departid,assets,assnum,response,engineer,approve_status,current_approver',array('repid'=>$data['repid']));
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $repInfo['department'] = $departname[$repInfo['departid']]['department'];
                $moduleModel = new ModuleModel();
                $wx_status = $moduleModel->decide_wx_login();
                if($repInfo['status'] == C('REPAIR_AUDIT')){
                    if(C('USE_FEISHU') === 1){
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**维修单号：**'.$repInfo['repnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**'.$repInfo['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**'.$repInfo['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**所属科室：**'.$repInfo['department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '维修配件已出库，请审批';
                        $feishu_fields[] = $fd;

                        //按钮区域
                        $act['tag'] = 'button';
                        $act['type'] = 'primary';
                        $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME').'/#'.C('FS_NAME').'/Repair/addApprove?repid='.$repInfo['repid'];
                        $act['text']['tag'] = 'plain_text';
                        $act['text']['content'] = '审批';
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
                        $card_data['header']['title']['content'] = '维修配件出库审批提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        //查询接单人员openid
                        $applicant = $repairModel->DB_get_all('user','telephone,openid',array('username'=>array('in',$repInfo['current_approver'])));
                        $already_send = [];
                        foreach ($applicant as $k=>$v){
                            if($v['openid'] && !in_array($v['openid'],$already_send)){
                                $this->send_feishu_card_msg($v['openid'],$card_data);
                                $already_send[] = $v['openid'];
                            }
                        }
                    }else{
                        if($wx_status){
                            //审核中，发送微信消息给审核人（维修处理通知）
                            if(C('USE_VUE_WECHAT_VERSION')){
                                $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME').'/#'.C('VUE_NAME').'/Repair/addApprove?repid='.$repInfo['repid'];
                            }else{
                                $redecturl = C('HTTP_HOST').C('MOBILE_NAME').'/Repair/addApprove.html?repid='.$repInfo['repid'];
                            }
                            //查询接单人员openid
                            $applicant = $repairModel->DB_get_all('user','telephone,openid',array('username'=>array('in',$repInfo['current_approver'])));

                            $openIds = array_column($applicant, 'openid');
                            $openIds = array_filter($openIds);
                            $openIds = array_unique($openIds);

                            $messageData = [
                                'thing3'             => $repInfo['department'],// 科室
                                'thing6'             => $repInfo['assets'],// 设备名称
                                'character_string12' => $repInfo['assnum'],// 设备编码
                                'character_string35' => $repInfo['repnum'],// 维修单号
                                'const17'            => '维修配件已出库，请审批',// 工单状态
                            ];

                            foreach ($openIds as $openId) {
                                Weixin::instance()->sendMessage($openId, '设备维修通知', $messageData, $redecturl);
                            }
                        }
                    }
                    //推送一条推送消息到大屏幕
                    $push_messages[] = ['type_action' => 'edit', 'type_name' => C('SCREEN_REPAIR'), 'assets' => $repInfo['assets'], 'assnum' => $repInfo['assnum'], 'department' => $repInfo['department'], 'remark' => '设备已出库，正在等待审批', 'status' => $repInfo['status'], 'status_name' => '审核中', 'time' => date('Y-m-d H:i'), 'username' => session('username') . '(' . session('telephone') . ')'];
                    push_messages($push_messages);
                }
                if($repInfo['status'] == C('REPAIR_MAINTENANCE')){
                    if(C('USE_FEISHU') === 1){
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**维修单号：**'.$repInfo['repnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**'.$repInfo['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**'.$repInfo['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**所属科室：**'.$repInfo['department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '维修配件已出库，请继续维修';
                        $feishu_fields[] = $fd;

                        //按钮区域
                        $act['tag'] = 'button';
                        $act['type'] = 'primary';
                        $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME').'/#'.C('FS_NAME').'/Repair/startRepair?repid='.$repInfo['repid'];
                        $act['text']['tag'] = 'plain_text';
                        $act['text']['content'] = '继续维修';
                        $feishu_actions[] = $act;

                        $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['elements'][1]['tag'] = 'hr';
                        $card_data['elements'][2]['actions'] = $feishu_actions;
                        $card_data['elements'][2]['layout'] = 'bisected';
                        $card_data['elements'][2]['tag'] = 'action';
                        $card_data['header']['template'] = 'orange';
                        $card_data['header']['title']['content'] = '继续维修提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        //查询接单人员openid
                        $applicant = $repairModel->DB_get_one('user','telephone,openid',array('username'=>$repInfo['response']));
                        $this->send_feishu_card_msg($applicant['openid'],$card_data);
                    }else{
                        if($wx_status){
                            //继续维修，发送微信消息给维修工程师（维修处理通知）
                            if(C('USE_VUE_WECHAT_VERSION')){
                                $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME').'/#'.C('VUE_NAME').'/Repair/startRepair?repid='.$repInfo['repid'];
                            }else{
                                $redecturl = C('HTTP_HOST').C('MOBILE_NAME').'/Repair/startRepair.html?repid='.$repInfo['repid'];
                            }
                            //查询接单人员openid
                            $applicant = $repairModel->DB_get_one('user','telephone,openid',array('username'=>$repInfo['response']));

                            if ($applicant['openid']) {
                                Weixin::instance()->sendMessage($applicant['openid'], '设备维修通知', [
                                    'thing3'             => $repInfo['department'],// 科室
                                    'thing6'             => $repInfo['assets'],// 设备名称
                                    'character_string12' => $repInfo['assnum'],// 设备编码
                                    'character_string35' => $repInfo['repnum'],// 维修单号
                                    'const17'            => '维修配件已出库，请继续维修',// 工单状态
                                ], $redecturl);
                            }
                        }
                    }
                    //推送一条推送消息到大屏幕
                    $push_messages[] = ['type_action' => 'edit', 'type_name' => C('SCREEN_REPAIR'), 'assets' => $repInfo['assets'], 'assnum' => $repInfo['assnum'], 'department' => $repInfo['department'], 'remark' => $data['remark'], 'status' => $repInfo['status'], 'status_name' => '维修中', 'time' => date('Y-m-d H:i'), 'username' => session('username') . '(' . session('telephone') . ')'];
                    push_messages($push_messages);
                }
                return array('status' => 1, 'msg' => '出库记录成功','repid'=>$data['repid']);
            } else {
                return array('status' => -1, 'msg' => '出库记录失败');
            }
        } else {
            return array('status' => $partsResult['status'], 'msg' => $partsResult['msg']);
        }
    }

    //配件库存列表
    public function partStockList()
    {
        $parts = I('POST.parts');
        $leader = I('POST.leader');
        $startDate = I('POST.startDate');
        $endDate = I('POST.endDate');
        $supplier_name = I('POST.supplier_name');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        if (!session('current_hospitalid')) {
            $result['msg'] = '未选中医院';
            $result['code'] = 400;
            return $result;
        }
        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        $where['is_use'] = ['EQ', C('NO_STATUS')];
        $where['status'] = ['EQ', C('NO_STATUS')];
        if ($parts) {
            $where['parts'] = array('LIKE', "%$parts%");
        }
        if ($supplier_name) {
            $where['supplier_name'] = ['like', $supplier_name];
        }
        if ($startDate) {
            $where['addtime'][] = ['EGT', $startDate];
        }
        if ($endDate) {
            $where['addtime'][] = ['ELT', $endDate];
        }
        if ($leader) {
            if ($leader == '配件库') {
                $where['leader'] = ['EQ', ''];
            } else {
                $where['leader'] = ['EQ', $leader];
            }
        }
//        var_dump($where);exit;
        $group = 'parts,parts_model,price,supplier_id,leader';
        $fields = 'COUNT(*) AS max_sum,leader,parts,supplier_name,price,unit,parts_model,brand';
        $total = $this->DB_get_all('parts_inware_record_detail', 'COUNT(*) ', $where, $group);
        $data = $this->DB_get_all('parts_inware_record_detail', $fields, $where, $group, '', $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        foreach ($data as &$val) {
            if ($val['leader'] == '') {
                $val['leader'] = '配件库';
            }
            $val['supplier_name'] = htmlspecialchars_decode($val['supplier_name']);
            $val['total_price'] = number_format($val['max_sum'] * $val['price'], 2, '.', '');
        }
        $result['total'] = count($total);
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //获取入库配件记录信息
    public function getInWarePartsData()
    {
        $parts = rtrim(I('POST.parts'), '|');
        if (!$parts) {
            $result['status'] = 997;
            $result['msg'] = '请至少记录一个配件采购明细';
            return $result;
        }
        $parts_model = rtrim(I('POST.parts_model'), '|');
        $supplier_id = rtrim(I('POST.supplier_id'), '|');
        $supplier_name = rtrim(I('POST.supplier_name'), '|');
        $manufacturer_name = rtrim(I('POST.manufacturer_name'), '|');
        $manufacturer_id = rtrim(I('POST.manufacturer_id'), '|');
        $brand = rtrim(I('POST.brand'), '|');
        $unit = rtrim(I('POST.unit'), '|');
        $sum = rtrim(I('POST.sum'), '|');
        $price = rtrim(I('POST.price'), '|');
        $parts = explode('|', $parts);
        $parts_model = explode('|', $parts_model);
        $supplier_id = explode('|', $supplier_id);
        $supplier_name = explode('|', $supplier_name);
        $manufacturer_name = explode('|', $manufacturer_name);
        $manufacturer_id = explode('|', $manufacturer_id);
        $brand = explode('|', $brand);
        $unit = explode('|', $unit);
        $sum = explode('|', $sum);
        $price = explode('|', $price);
        $apply_sum = [];
        if (rtrim(I('POST.apply_sum'))) {
            $apply_sum = rtrim(I('POST.apply_sum'), '|');
            $apply_sum = explode('|', $apply_sum);
        }
        $totalPrice = 0;
        $totalNum = 0;
        $data = array();
        $result['status'] = 1;
        foreach ($parts as $k => $v) {
            if (!$v || $v == '--') {
                continue;
            } else {
                $data[$k]['parts'] = $v;
                $data[$k]['parts_model'] = $parts_model[$k];
                $data[$k]['supplier_id'] = $supplier_id[$k];
                $data[$k]['supplier_name'] = $supplier_name[$k];
                $data[$k]['manufacturer_name'] = $manufacturer_name[$k];
                $data[$k]['manufacturer_id'] = $manufacturer_id[$k];
                $data[$k]['brand'] = $brand[$k];
                $data[$k]['unit'] = $unit[$k];
                if ($sum[$k] <= 0) {
                    $result['status'] = -999;
                    $result['msg'] = '配件：' . $v . ' 请输入合理的数量';
                    return $result;
                }
                if (!checkPrice($price[$k])) {
                    $result['status'] = -998;
                    $result['msg'] = '配件：' . $v . ' 请输入合理的单价';
                    return $result;
                }
                if ($apply_sum[$k]) {
                    if ($sum[$k] < $apply_sum[$k]) {
                        $result['status'] = -997;
                        $result['msg'] = '采购配件：' . $v . ' 数量不能少于申请的数量' . $apply_sum[$k] . ' ' . $unit[$k];;
                        return $result;
                    } else {
                        $data[$k]['apply_sum'] = $apply_sum[$k];
                    }
                }
                $data[$k]['sum'] = $sum[$k];
                $data[$k]['price'] = number_format($price[$k],2, '.', '');
                $totalPrice += ($data[$k]['price'] * $data[$k]['sum']);
                $totalNum += $data[$k]['sum'];
            }
        }
        $result['data'] = $data;
        $result['totalPrice'] = $totalPrice;
        $result['totalNum'] = $totalNum;
        return $result;

    }

    //获取验证后的配件入库信息
    public function checkOutWareData()
    {
        $parts = rtrim(I('POST.parts'), '|');
        if (!$parts) {
            $result['status'] = 997;
            $result['msg'] = '请至少记录一个配件出库明细';
            return $result;
        }
        $price = rtrim(I('POST.price'), '|');
        $sum = rtrim(I('POST.sum'), '|');
        $parts_model = rtrim(I('POST.parts_model'), '|');
        $supplier_name = rtrim(I('POST.supplier_name'), '|');
        $supplier_id = rtrim(I('POST.supplier_id'), '|');


        $parts = explode('|', $parts);
        $price = explode('|', $price);
        $sum = explode('|', $sum);
        $parts_model = explode('|', $parts_model);
        $supplier_name = explode('|', $supplier_name);
        $supplier_id = explode('|', $supplier_id);


        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        $where['status'] = ['EQ', C('NO_STATUS')];
        $where['parts'] = ['IN', $parts];
        $where[1][]['leader'] = ['EQ', trim(I('POST.leader'))];
        $where[1][]['leader'] = ['EQ', ''];
        $where[1]['_logic'] = 'OR';
        $group = 'parts,parts_model,price,supplier_id';
        $fields = 'COUNT(*) AS max_sum,GROUP_CONCAT(detailid) AS detailid,GROUP_CONCAT(unit) AS unit,
        parts,parts_model,price,supplier_name,supplier_id';
        $stock = $this->DB_get_all('parts_inware_record_detail', $fields, $where, $group);
        if (!$stock) {
            $result['status'] = -997;
            $result['msg'] = '仓库无申请的配件请先补充';
            return $result;
        }

        $totalPrice = 0;
        $totalNum = 0;
        $data = [];
        $stockData = [];
        foreach ($stock as &$sval) {
            $stockData[$sval['parts'] . $sval['parts_model'] . $sval['price'] . $sval['supplier_id']]['max_sum'] = $sval['max_sum'];
            $stockData[$sval['parts'] . $sval['parts_model'] . $sval['price'] . $sval['supplier_id']]['detailid'] = $sval['detailid'];
            $stockData[$sval['parts'] . $sval['parts_model'] . $sval['price'] . $sval['supplier_id']]['unit'] = $sval['unit'];
        }

        $result['status'] = 1;
        foreach ($parts as $k => $v) {
            if (!$v || $v == '--') {
                continue;
            } else {
                $data[$k]['parts'] = $v;
                $data[$k]['parts_model'] = $parts_model[$k];
                if ($sum[$k] <= 0) {
                    $result['status'] = -999;
                    $result['msg'] = '配件：' . $v . ' 请输入合理的数量';
                    return $result;
                }

                //验证库存是否足够
                if ($stockData[$v . $parts_model[$k] . $price[$k] . $supplier_id[$k]]['max_sum'] < $sum[$k]) {
                    $result['status'] = -998;
                    $result['msg'] = '配件库 配件：' . $v . ' 数量不足够出库 ' . $sum[$k] . ' 个请先做入库操作';
                    return $result;
                }
                $data[$k]['sum'] = $sum[$k];
                $data[$k]['supplier_name'] = $supplier_name[$k];
                $data[$k]['supplier_id'] = $supplier_id[$k];
                $data[$k]['price'] = $price[$k];
                $data[$k]['detailid'] = $stockData[$v . $parts_model[$k] . $price[$k] . $supplier_id[$k]]['detailid'];
                $data[$k]['unit'] = $stockData[$v . $parts_model[$k] . $price[$k] . $supplier_id[$k]]['unit'];
                $totalNum += $data[$k]['sum'];
            }
        }
        $addAll = [];
        $num = 0;
        foreach ($data as &$value) {
            $detailid = explode(',', $value['detailid']);
            $unit = explode(',', $value['unit']);
            for ($i = 0; $i < $value['sum']; $i++) {
                $addAll[$num]['inware_partsid'] = $detailid[$i];
                $addAll[$num]['parts'] = $value['parts'];
                $addAll[$num]['parts_model'] = $value['parts_model'];
                $addAll[$num]['supplier_id'] = $value['supplier_id'];
                $addAll[$num]['supplier_name'] = $value['supplier_name'];
                $addAll[$num]['price'] = $value['price'];
                $addAll[$num]['unit'] = $unit[$i];
                $totalPrice += $value['price'];
                $num++;
            }
        }
        $result['data'] = $addAll;
        $result['totalPrice'] = $totalPrice;
        $result['totalNum'] = $totalNum;
        return $result;
    }

    //获取验证后的配件入库信息
    public function checkOutWareApplyData($repid)
    {
        $parts = rtrim(I('POST.parts'), '|');
        if (!$parts) {
            $result['status'] = 997;
            $result['msg'] = '请至少记录一个配件出库明细';
            return $result;
        }
        $parts_model = rtrim(I('POST.parts_model'), '|');
        $sum = rtrim(I('POST.sum'), '|');

        $parts = explode('|', $parts);
        $parts_model = explode('|', $parts_model);
        $sum = explode('|', $sum);

        $apply_sum = [];
        if (rtrim(I('POST.apply_sum'))) {
            $apply_sum = rtrim(I('POST.apply_sum'), '|');
            $apply_sum = explode('|', $apply_sum);
        }

        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        $where['status'] = ['EQ', C('NO_STATUS')];
        $where['parts'] = ['IN', $parts];
        $where[1][1][]['leader'] = ['EQ', trim(I('POST.leader'))];
        $where[1][1][]['repid'] = ['EQ', $repid];
        $where[1][]['leader'] = ['EQ', ''];
        $where[1]['_logic'] = 'OR';
        $group = 'parts,parts_model';
        $fields = 'COUNT(*) AS max_sum,GROUP_CONCAT(detailid) AS detailid,GROUP_CONCAT(price) AS price,GROUP_CONCAT(unit) AS unit,
        GROUP_CONCAT(supplier_name) AS supplier_name,GROUP_CONCAT(supplier_id) AS supplier_id,parts,parts_model';
        $stock = $this->DB_get_all('parts_inware_record_detail', $fields, $where, $group);
        if (!$stock) {
            $result['status'] = -997;
            $result['msg'] = '仓库无申请的配件请先补充';
            return $result;
        }
        $totalPrice = 0;
        $totalNum = 0;
        $data = [];
        $stockData = [];
        foreach ($stock as &$sval) {
            $stockData[$sval['parts'] . $sval['parts_model']]['max_sum'] = $sval['max_sum'];
            $stockData[$sval['parts'] . $sval['parts_model']]['detailid'] = $sval['detailid'];
            $stockData[$sval['parts'] . $sval['parts_model']]['supplier_id'] = $sval['supplier_id'];
            $stockData[$sval['parts'] . $sval['parts_model']]['supplier_name'] = $sval['supplier_name'];
            $stockData[$sval['parts'] . $sval['parts_model']]['price'] = $sval['price'];
            $stockData[$sval['parts'] . $sval['parts_model']]['unit'] = $sval['unit'];
        }

        $result['status'] = 1;
        foreach ($parts as $k => $v) {
            if (!$v || $v == '--') {
                continue;
            } else {
                $data[$k]['parts'] = $v;
                $data[$k]['parts_model'] = $parts_model[$k];
                if ($sum[$k] <= 0) {
                    $result['status'] = -999;
                    $result['msg'] = '配件：' . $v . ' 请输入合理的数量';
                    return $result;
                }

                if ($sum[$k] < $apply_sum[$k]) {
                    $result['status'] = -998;
                    $result['msg'] = '出库配件：' . $v . ' 数量不能少于申请的数量' . $apply_sum[$k];
                    return $result;
                }
                //验证库存是否足够
                if ($stockData[$v . $parts_model[$k]]['max_sum'] < $sum[$k]) {
                    $result['status'] = -998;
                    $result['msg'] = '配件库 配件：' . $v . ' 数量不足够出库 ' . $sum[$k] . ' 个请先做入库操作';
                    return $result;
                }

                $data[$k]['sum'] = $sum[$k];
                $data[$k]['detailid'] = $stockData[$v . $parts_model[$k]]['detailid'];
                $data[$k]['supplier_name'] = $stockData[$v . $parts_model[$k]]['supplier_name'];
                $data[$k]['supplier_id'] = $stockData[$v . $parts_model[$k]]['supplier_id'];
                $data[$k]['price'] = $stockData[$v . $parts_model[$k]]['price'];
                $data[$k]['unit'] = $stockData[$v . $parts_model[$k]]['unit'];
                $totalNum += $data[$k]['sum'];
            }
        }


        $addAll = [];
        $num = 0;
        $outDetailid = [];
        $parts_pirceData=[];
        foreach ($data as &$value) {
            $detailid = explode(',', $value['detailid']);
            $supplier_name = explode(',', $value['supplier_name']);
            $supplier_id = explode(',', $value['supplier_id']);
            $price = explode(',', $value['price']);
            $unit = explode(',', $value['unit']);
            $parts_pirceData[$value['parts'].'//'.$value['parts_model']]['price_sum']=0;
            for ($i = 0; $i < $value['sum']; $i++) {
                $parts_pirceData[$value['parts'].'//'.$value['parts_model']]['price_sum']+=$price[$i];
                $outDetailid[] = $detailid[$i];
                $addAll[$num]['inware_partsid'] = $detailid[$i];
                $addAll[$num]['parts'] = $value['parts'];
                $addAll[$num]['parts_model'] = $value['parts_model'];
                $addAll[$num]['supplier_id'] = $supplier_id[$i];
                $addAll[$num]['supplier_name'] = $supplier_name[$i];
                $addAll[$num]['price'] = $price[$i];
                $addAll[$num]['unit'] = $unit[$i];
                $totalPrice += $price[$i];
                $num++;
            }
        }

        $result['totalNum'] = $totalNum;
        $result['totalPrice'] = $totalPrice;
        $result['outDetailid'] = $outDetailid;
        $result['parts_pirceData'] = $parts_pirceData;
        $result['data'] = $addAll;
        return $result;
    }

    /**
     * @Notes 配件入库明细入库操作
     * @params $data array 配件信息数组
     * @params $inwareid int 入库单id
     * @params $leader string 领用人
     * @params $repid int 维修单id
     * */
    public function add_inware_record_detail($data, $inwareid, $leader = '', $repid = 0)
    {

        $addtime = trim(I('POST.addtime'));
        $addAll = [];
        $num = 0;
        foreach ($data as &$value) {
            for ($i = 1; $i <= $value['sum']; $i++) {
                $addAll[$num]['inwareid'] = $inwareid;
                $addAll[$num]['parts'] = $value['parts'];
                $addAll[$num]['parts_model'] = $value['parts_model'];
                $addAll[$num]['manufacturer_name'] = $value['manufacturer_name'];
                $addAll[$num]['manufacturer_id'] = $value['manufacturer_id'];
                $addAll[$num]['supplier_id'] = $value['supplier_id'];
                $addAll[$num]['supplier_name'] = $value['supplier_name'];
                $addAll[$num]['brand'] = $value['brand'];
                $addAll[$num]['unit'] = $value['unit'];
                $addAll[$num]['price'] = $value['price'];
                $addAll[$num]['adduser'] = $value['adduser'];
                $addAll[$num]['addtime'] = $value['addtime'];
                $addAll[$num]['hospital_id'] = session('current_hospitalid');
                $addAll[$num]['adduser'] = session('username');
                $addAll[$num]['addtime'] = $addtime;
                $addAll[$num]['leader'] = '';
                $addAll[$num]['repid'] = 0;
                if ($leader) {
                    if ($i <= $value['apply_sum']) {
                        $addAll[$num]['leader'] = $leader;
                        $addAll[$num]['repid'] = $repid;
                    }
                }
                $num++;
            }
        }
        if ($addAll) {
            $this->insertDataALL('parts_inware_record_detail', $addAll);
        }
    }

    /**
     * @Notes 配件出库明细入库操作
     * @params $data array 配件信息数组
     * @params $outwareid int 出库单id
     * @params $leader string 领用人
     * @params $repid int 维修单id
     * @params $approveData array 设备审批所需信息数组
     * @params $type 解决方式 yes 非现场  no 现场
     * */
    public function add_outware_record_detail($data, $outwareid, $leader, $repid = 0, $approveData = array(),$type = 'yes')
    {
        $addtime = trim(I('POST.addtime'));
        $addAll = [];
        $detailid_arr = [];
        $num = 0;
        foreach ($data['data'] as &$value) {
            $detailid_arr[] = $value['inware_partsid'];
            $addAll[$num]['inware_partsid'] = $value['inware_partsid'];
            $addAll[$num]['outwareid'] = $outwareid;
            $addAll[$num]['parts'] = $value['parts'];
            $addAll[$num]['parts_model'] = $value['parts_model'];
            $addAll[$num]['supplier_name'] = $value['supplier_name'];
            $addAll[$num]['supplier_id'] = $value['supplier_id'];
            $addAll[$num]['unit'] = $value['unit'];
            $addAll[$num]['price'] = $value['price'];
            $addAll[$num]['hospital_id'] = session('current_hospitalid');
            $addAll[$num]['adduser'] = session('username');
            $addAll[$num]['addtime'] = $addtime;
            $addAll[$num]['leader'] = $leader;
            $num++;
        }
        $settingData = $this->checkSmsIsOpen($this->MODULE);
        $departname = [];
        $repInfo=$this->DB_get_one('repair','',['repid'=>$repid]);
        include APP_PATH . "Common/cache/department.cache.php";
        $repInfo['department'] = $departname[$repInfo['departid']]['department'];
        $ToolMod = new ToolController();
        $RepairModel = new RepairModel();
        if ($addAll) {
            if ($repid > 0) {
                //是维修申请的
                $approve=$approveData['approve'];
                if ($approveData['isOpenApprove']&&$type=='yes') {
                    if ($approve['all_approver'] == '') {
                        //不在审核范围内 不需要审批
                        $updata['is_use'] = C('YES_STATUS');
                        $repairData['approve_status'] = C('STATUS_APPROE_UNWANTED');
                        $repairData['status'] = C('REPAIR_MAINTENANCE');//维修中
                    } else {
                        //默认为未审核
                        $repairData['current_approver'] = $approve['current_approver'];
                        $repairData['complete_approver'] = '';
                        $repairData['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                        $repairData['all_approver'] = $approve['all_approver'];
                        $repairData['approve_status'] = C('APPROVE_STATUS');
                        $repairData['status'] = C('REPAIR_AUDIT');//审核中

                        //审批记录删除
                        $approveWhere['repid'] = ['EQ', $repid];
                        $approveWhere['is_delete'] = ['EQ', C('NO_STATUS')];
                        $approveWhere['approve_class'] = ['EQ', 'repair'];
                        $approveWhere['process_node'] = ['EQ', C('REPAIR_APPROVE')];
                        $updateApprove['is_delete'] = C('YES_STATUS');
                        $approveList = $this->DB_get_all('approve', 'apprid', $approveWhere);
                        if ($approveList) {
                            $this->updateData('approve', $updateApprove, $approveWhere);
                        }
                    }
                } else {
                    //未开启维修审批，不需要审批
                    $updata['is_use'] = C('YES_STATUS');
                    $repairData['approve_status'] = C('STATUS_APPROE_UNWANTED');
                    if ($type == 'no'&&$repInfo['status']==C('REPAIR_MAINTENANCE_COMPLETION')) {
                        $repairData['status'] = C('REPAIR_MAINTENANCE_COMPLETION');//待验收
                    }else if ($type == 'yes') {
                        $repairData['status'] = C('REPAIR_MAINTENANCE');//维修中
                    }
                }
                if($repairData['status']==C('STATUS_APPROE_UNWANTED')||$type == 'no'){
                    //不需审批 出库单配件状态不需要审批
                    foreach ($addAll as &$addAllV){
                        $addAllV['status']=C('STATUS_APPROE_UNWANTED');
                    }
                    //
                }
                $repairData['part_total_price'] = $approveData['total_price'];
                $repairData['part_num'] = $approveData['part_num'];
                //预计维修费用 获取配件与第三方报价(可能是第三方类型 在继续维修的时候产生配件)
                $repairData['expect_price'] = $repairData['part_total_price']+$approveData['company_total_price'];
                // 维修总费用 预计维修费用+其他费用
                $repairData['actual_price'] =  $repairData['expect_price']+$approveData['other_price'];
                $repairData['editdate'] = time();
                //修改维修单状态
                $this->updateData('repair', $repairData, array('repid' => $repid));
                foreach ($data['parts_pirceData'] as $key=>$value){
                    $partsArr=explode('//',$key);
                    $partsDataWhere['parts']=$partsArr[0];
                    $partsDataWhere['part_model']=$partsArr[1];
                    $partsDataWhere['status']=C('NO_STATUS');
                    $partsDataWhere['repid']=$repid;
                    $partsData['status']=C('YES_STATUS');
                    $partsData['price_sum']=$value['price_sum'];
                    //修改维修配件单状态
                    $this->updateData('repair_parts', $partsData, $partsDataWhere);
                }
                //==========================================短信 START==========================================
                if ($settingData) {
                    //有开启短信
                    if($repairData['status'] == C('REPAIR_MAINTENANCE')){
                        //继续维修
                        //通知工程师领取配件继续维修
                        $where=[];
                        $where['status']=C('OPEN_STATUS');
                        $where['is_delete']=C('NO_STATUS');
                        $where['username']=$leader;
                        $leader_user=$this->DB_get_one('user','telephone',$where);
                        if($settingData['repairPartsOut']['status'] == C('OPEN_STATUS') && $leader_user['telephone']){
                            $sms = $RepairModel->formatSmsContent($settingData['repairPartsOut']['content'], $repInfo);
                            $ToolMod->sendingSMS($leader_user['telephone'], $sms, $this->MODULE, $repid);
                        }
                    }elseif($repairData['status'] == C('REPAIR_AUDIT')){
                        //审批中
                        if($approve['this_current_approver']){
                            //通知审批人审批
                            $where=[];
                            $where['status']=C('OPEN_STATUS');
                            $where['is_delete']=C('NO_STATUS');
                            $where['username']=$approve['this_current_approver'];
                            $approve_user=$this->DB_get_one('user','telephone',$where);
                            if($settingData['doApprove']['status'] == C('OPEN_STATUS') && $approve_user['telephone']){
                                $sms = $RepairModel->formatSmsContent($settingData['doApprove']['content'], $repInfo);
                                $ToolMod->sendingSMS($approve_user['telephone'], $sms, $this->MODULE, $repid);
                            }
                        }
                    }
                }
                //==========================================短信 END==========================================
            }else{
                //直接分配 出库单配件状态：不需要审批
                foreach ($addAll as &$addAllV){
                    $addAllV['status']=C('STATUS_APPROE_UNWANTED');
                }
            }
            $this->insertDataALL('parts_outware_record_detail', $addAll);
            $updata['status'] = C('YES_STATUS');
            $updata['repid'] = $repid;
            $updata['leader'] = $leader;
            $updataWhere['detailid'] = ['IN', $detailid_arr];
            $this->updateData('parts_inware_record_detail', $updata, $updataWhere);
        }
    }

    /**
     * 获取并验证审批信息
     * @param $repid int 维修单id
     * @param $assid int 设备id
     * @param $totalPrice float 出库单总价
     * @param $totalNum int 出库单配件数量
     * @return array
     * */
    public function getApproveData($repid,$assid,$totalPrice,$totalNum){
        $approve=[];
        $part_num = $totalNum;
        $total_price = $totalPrice;
        $assInfo = $this->DB_get_one('assets_info', 'hospital_id,departid', array('assid' => $assid));
        //验证是否需要审批
        $isOpenApprove = $this->checkApproveIsOpen(C('REPAIR_APPROVE'), $assInfo['hospital_id']);
        $repInfo=$this->DB_get_one('repair', 'company_total_price,other_price', array('repid' => $repid));
        if ($isOpenApprove) {
            //查询是否已设置审批流程
            $isSetProcess = $this->checkApproveIsSetProcess(C('REPAIR_APPROVE'), $assInfo['hospital_id']);
            if (!$isSetProcess) {
                die(json_encode(array('status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！')));
            }
            $where['status'] = ['EQ', C('YES_STATUS')];
            $where['repid'] = ['EQ', $repid];
            $outware = $this->DB_get_all('parts_outware_record', 'total_price,sum', $where);
            if ($outware) {
                //过往出库单历史 配件价格数量追加
                foreach ($outware as &$outVal) {
                    $total_price += $outVal['total_price'];
                    $part_num += $outVal['sum'];
                }
            }

            //需要审批，获取审批人
            $approve_process_user = $this->get_approve_process($total_price+$repInfo['company_total_price']+$repInfo['other_price'], C('REPAIR_APPROVE'), $assInfo['hospital_id']);
            //并且获取下次审批人
            $approve = $this->check_approve_process($assInfo['departid'], $approve_process_user, 1);
        }
        $assInfo['isOpenApprove']=$isOpenApprove;
        $assInfo['approve']=$approve;
        $assInfo['total_price']=$total_price;
        $assInfo['company_total_price']=$repInfo['company_total_price'];
        $assInfo['other_price']=$repInfo['other_price'];
        $assInfo['part_num']=$part_num;



        return $assInfo;
    }

    /**
     * 当然入库设备匹配 配件入库申请明细 相同的改变申请状态
     * @param $inwareid int 入库单id
     * */
    public function changeInwareApply($inwareid)
    {
        $num = 0;
        $formatData = [];
        $stockWhere['inwareid'] = ['EQ', $inwareid];
        $stockWhere['repid'] = ['EQ', 0];
        $stockWhere['leader'] = ['EQ', ''];
        $stockWhere['status'] = ['EQ', C('NO_STATUS')];
        $group = 'parts,parts_model';
        $fields = 'COUNT(*) AS sum,GROUP_CONCAT(detailid) AS detailid,parts,parts_model';
        //获取当前加入的入库单 未绑定 未分配的配件
        $stock = $this->DB_get_all('parts_inware_record_detail', $fields, $stockWhere, $group);
        if(!$stock){
            return;
        }
        foreach ($stock as $dataV) {
            $where[1][$num]['parts'] = ['EQ', $dataV['parts']];
            $where[1][$num]['parts_model'] = ['EQ', $dataV['parts_model']];
            $num++;
            $formatData[$dataV['parts'] . $dataV['parts_model']]['parts'] = $dataV['parts'];
            $formatData[$dataV['parts'] . $dataV['parts_model']]['parts_model'] = $dataV['parts_model'];
            $formatData[$dataV['parts'] . $dataV['parts_model']]['detailid'] = $dataV['detailid'];
            $formatData[$dataV['parts'] . $dataV['parts_model']]['sum'] = $dataV['sum'];
        }

        $where[1]['_logic'] = 'OR';
        $where['status'] = ['EQ', C('NO_STATUS')];
        $group = 'parts,parts_model';
        $fields = 'COUNT(*) AS sum,GROUP_CONCAT(inwareid) AS inwareid,GROUP_CONCAT(applyid) AS applyid,parts,parts_model';
        //获取申请单明细 相匹配的配件
        $apply = $this->DB_get_all('parts_inware_record_apply', $fields, $where, $group);
        if ($apply) {
            //匹配到有对应的配件 组合数据
            $num = 0;
            $applyidArr = [];
            $detailidArr = [];
            foreach ($apply as &$applyVal) {
                $applyid = explode(',', $applyVal['applyid']);
                $inwareidArr = explode(',', $applyVal['inwareid']);
                $detailid = explode(',', $formatData[$applyVal['parts'] . $applyVal['parts_model']]['detailid']);
                for ($i = 0; $i < $applyVal['sum']; $i++) {
                    if ($formatData[$applyVal['parts'] . $applyVal['parts_model']]['sum'] > 0) {
                        $applyidArr[] = $applyid[$i];
                        $detailidArr[$inwareidArr[$i]][] = $detailid[$i];
                        $detailidArr[$inwareidArr[$i]]['inwareid'] = $inwareidArr[$i];

                        //如果绑定了 则记录数量 用于判断申请单是否全部匹配
                        if ($detailidArr[$inwareidArr[$i]]['sum'] > 0) {
                            $detailidArr[$inwareidArr[$i]]['sum']++;
                        } else {
                            $detailidArr[$inwareidArr[$i]]['sum'] = 1;
                        }

                        $formatData[$applyVal['parts'] . $applyVal['parts_model']]['sum']--;
                        $num++;
                    }
                }
            }


            $inwareApplyInwareid=[];
            foreach ($detailidArr as $key => $value) {
                $repairWhere[]['I.inwareid'] = $key;
                $inwareApplyInwareid[1][]['inwareid'] = $key;

            }
            $repairWhere['_logic'] = 'OR';
            $inwareApplyInwareid[1]['_logic'] = 'OR';

            $join = 'LEFT JOIN sb_repair AS R ON R.repid=I.repid';
            $fields = 'I.inwareid,R.repid,R.response';
            //获取 申请单对应的维修单号
            $repair = $this->DB_get_all_join('parts_inware_record', 'I', $fields, $join, $repairWhere,'','','');
            if ($repair) {
                $repairData = [];
                foreach ($repair as &$repairV) {
                    $repairData[$repairV['inwareid']]['repid'] = $repairV['repid'];
                    $repairData[$repairV['inwareid']]['response'] = $repairV['response'];
                }
                foreach ($detailidArr as $key => $value) {
                    $changeWhere['detailid'] = ['IN', $value];
                    $changeData['leader'] = $repairData[$key]['response'];
                    $changeData['repid'] = $repairData[$key]['repid'];
                    //将已加入的配件绑定申请单对应的维修id和工程师
                    $this->updateData('parts_inware_record_detail',$changeData,$changeWhere);
                }
            }


            $group = 'inwareid';
            $inwareApplyInwareid['status']=C('NO_STATUS');
            //获取 有匹配的申请单 总申请的数量
            $applyInwareData = $this->DB_get_all('parts_inware_record_apply', 'COUNT(*) AS sum,inwareid', $inwareApplyInwareid, $group);
            $delInwareid=[];
            foreach ($applyInwareData as &$value){
                if($value['sum']==$detailidArr[$value['inwareid']]['sum']){
                    //申请的数量等于匹配的数量
                    $delInwareid[]=$value['inwareid'];
                }
            }
            if($delInwareid){
                //将配件已经全部采购的申请单删除
                $delInwareData['is_delete']=C('YES_STATUS');
                $this->updateData('parts_inware_record',$delInwareData,['inwareid'=>['IN',$delInwareid]]);

            }
            if ($applyidArr) {
                $applyData['status'] = C('YES_STATUS');
                $applyData['inwareid'] = $inwareid;
                //将申请单重新绑定
                $this->updateData('parts_inware_record_apply',$applyData,['applyid'=>['IN',$applyidArr]]);
            }
        }

    }

    //创建暂存采购合同记录
    public function setInwareContract($data, $inwareid)
    {
        $add['hospital_id'] = session('current_hospitalid');
        $add['inwareid'] = $inwareid;
        $add['contract_num'] = $this->getInwareContractNum();
        $add['supplier_id'] = $data['supplier_id'];
        $add['supplier_name'] = $data['supplier_name'];
        $add['contract_type'] = C('CONTRACT_TYPE_PARTS');
        //获取厂商联系人
        $supplier = $this->DB_get_one('offline_suppliers', 'salesman_name,salesman_phone', array('olsid' => $data['supplier_id']));
        $add['supplier_contacts'] = $supplier['salesman_name'];
        $add['supplier_phone'] = $supplier['salesman_phone'];
        $add['contract_amount'] = $data['total_price'];
        $add['hospital_manager'] = session('username');
        $add['add_user'] = session('username');
        $add['add_time'] = getHandleDate(time());
        $this->insertData('parts_contract', $add);
    }


    //获取入合同单号
    public function getInwareContractNum()
    {
        $count = $this->DB_get_count('parts_contract');
        return 'P-INW-CN' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    //获取入库单号
    public function getInwareNum()
    {
        $count = $this->DB_get_one('parts_inware_record','inware_num',[],'inwareid desc');
        $lastNum = substr($count['inware_num'], 3);
        $num = sprintf("%04d", ++$lastNum);
        return 'INW' . $num;
    }

    //获取出库单号
    public function getOutwareNum()
    {
        $count = $this->DB_get_count('parts_outware_record');
        return 'OUTW' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    //获取厂商
    public function getSuppliers()
    {
        $where['status'] = ['EQ', C('OPEN_STATUS')];
        $data = $this->DB_get_all('offline_suppliers', 'olsid,sup_name,is_supplier,is_manufacturer', $where);
        return $data;
    }

    //获取品牌
    public function getBannerList()
    {
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $data = $this->DB_get_all('dic_brand', 'brand_name', $where);
        return $data;
    }

    /**
     * 获取入库单信息
     * @param $inwareid int 入库单id
     * @return array
     */
    public function getInwareRecordBasic($inwareid)
    {
        $data = $this->DB_get_one('parts_inware_record', '', array('inwareid' => $inwareid));
        $data['buydate'] = HandleEmptyNull($data['buydate']);
        return $data;
    }

    /**
     * 获取入库单明细信息
     * @param $inwareid int 入库单id
     * @return  array
     * */
    public function getInwareRecordDetail($inwareid)
    {
        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        $where['inwareid'] = ['EQ', $inwareid];
        // 注：同一个单号 供应商是一样的 生产商可能不同
        $group = 'parts,parts_model,price,brand,manufacturer_id';
        $fields = 'COUNT(*) AS max_sum,price,unit,supplier_name,supplier_id,parts,parts_model,manufacturer_name,brand';
        $data = $this->DB_get_all('parts_inware_record_detail', $fields, $where, $group);
        foreach ($data as &$value) {
            $value['total_price'] = number_format($value['max_sum'] * $value['price'], 2, '.', '');
        }
        return $data;
    }

    /**
     * 获取入库申请单信息
     * @param $inwareid int 入库单id
     * @return array
     * */
    public function getInwareApply($inwareid)
    {
        $data = $this->DB_get_all('parts_inware_record_apply', 'count(*) AS sum,parts,parts_model', array('inwareid' => $inwareid), 'parts,parts_model');
        return $data;
    }

    /**
     * 获取出库单信息
     * @param $outwareid int 出库单id
     * @return array
     */
    public function getOutwareRecordBasic($outwareid)
    {
        $data = $this->DB_get_one('parts_outware_record', '', array('outwareid' => $outwareid));
        $data['outdate'] = HandleEmptyNull($data['outdate']);
        return $data;
    }

    /**
     * 获取入库单明细信息
     * @param $outwareid int 出库单id
     * @return  array
     * */
    public function getOutwareRecordDetail($outwareid)
    {
        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        $where['outwareid'] = ['EQ', $outwareid];
        $group = 'parts,parts_model,price,supplier_id';
        $fields = 'COUNT(*) AS max_sum,price,unit,supplier_name,parts,parts_model';
        $data = $this->DB_get_all('parts_outware_record_detail', $fields, $where, $group);
        foreach ($data as &$value) {
            $value['total_price'] = number_format($value['max_sum'] * $value['price'], 2, '.', '');
        }
        return $data;
    }

    /**
     * 获取出库申请单信息
     * @param $outwareid int 出库单id
     * @return array
     * */
    public function getOutwareApply($outwareid)
    {
        $data = $this->DB_get_all('parts_outware_record_apply', 'count(*) AS sum,parts,parts_model', array('outwareid' => $outwareid), 'parts,parts_model');
        return $data;
    }




    /**
     * 将新配件更新至配件字典
     * @param $addPartsData array 入库的配件信息
     * */
    public function addDicParts($addPartsData){
        $addData=[];
        foreach ($addPartsData as &$partsV){
            $key=$partsV['parts'].$partsV['parts_model'].$partsV['manufacturer_id'].$partsV['brand'];
            $addData[$key]['parts']=$partsV['parts'];
            $addData[$key]['hospital_id']=session('current_hospitalid');
            $addData[$key]['parts_model']=$partsV['parts_model']==null?'':$partsV['parts_model'];
            $addData[$key]['unit']=$partsV['unit']==null?'':$partsV['unit'];
            $addData[$key]['price']=$partsV['price']==null?'':$partsV['price'];
            $addData[$key]['brand']=$partsV['brand']==null?'':$partsV['brand'];
            $addData[$key]['supplier_name']=$partsV['manufacturer_name']==null?'':$partsV['manufacturer_name'];
            $addData[$key]['supplier_id']=$partsV['manufacturer_id']==null?0:$partsV['manufacturer_id'];
        }

        $where['is_delete']=['EQ',C('NO_STATUS')];
        $where['status']=['EQ',C('OPEN_STATUS')];
        $where['hospital_id'] = ['EQ',session('current_hospitalid')];
        $fields='dic_partsid,parts,parts_model,unit,price,brand,supplier_name,supplier_id';
        $dic=$this->DB_get_all('dic_parts',$fields,$where);

        if($dic){
            $dicData=[];
            foreach ($dic as &$dicV){
                $key=$dicV['parts'].$dicV['parts_model'].$dicV['supplier_id'].$dicV['brand'];
                $dicData[$key]['dic_partsid']=$dicV['dic_partsid'];
                $dicData[$key]['parts']=$dicV['parts'];
                $dicData[$key]['hospital_id']=session('current_hospitalid');
                $dicData[$key]['parts_model']=$dicV['parts_model'];
                $dicData[$key]['unit']=$dicV['unit'];
                $dicData[$key]['price']=$dicV['price'];
                $dicData[$key]['brand']=$dicV['brand'];
                $dicData[$key]['supplier_name']=$dicV['supplier_name'];
                $dicData[$key]['supplier_id']=$dicV['supplier_id'];
            }

            //交集 update
            $saveData=array_intersect_key($addData,$dicData);
            if($saveData){
                foreach ($saveData as $key=>$value){
                    if($dicData[$key]['price']!=$addData[$key]['price'] or $dicData[$key]['unit']!=$addData[$key]['unit']){
                        $value['edituser']=session('username');
                        $value['edittime']=getHandleDate(time());
                        $this->updateData('dic_parts',$value,['dic_partsid'=>$dicData[$key]['dic_partsid']]);
                    }
                }
            }
            //差异 add
            $addData=array_diff_key($addData,$dicData);
        }

        if($addData){
            $addDicData=[];
            foreach ($addData as $addV){
                $addV['adduser']=session('username');
                $addV['addtime']=getHandleDate(time());
                $addDicData[]=$addV;
            }
            $this->insertDataALL('dic_parts',$addDicData);
        }
    }
}
