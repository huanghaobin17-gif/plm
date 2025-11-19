<?php

namespace Admin\Controller\Repair;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\DictionaryModel;
use Admin\Model\OfflineSuppliersModel;
use Admin\Model\PartsModel;
use Admin\Model\RepairModel;
use Admin\Model\UserModel;

class RepairPartsController extends CheckLoginController
{
    //配件入库列表
    public function partsInWareList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取配件入库列表数据
                    $PartsModel = new PartsModel();
                    $result = $PartsModel->partsInWareList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showPartsInwareDetails':
                    //显示入库单详情页
                    $this->showPartsInwareDetails();
                    break;
                default:
                    //显示入库单列表页
                    $this->showPartsInWareList();
                    break;
            }
        }
    }

    //入库单详情页
    private function showPartsInwareDetails()
    {
        $inwareid = I('GET.inwareid');
        if ($inwareid) {
            $PartsModel=new PartsModel();
            $inware_record=$PartsModel->getInwareRecordBasic($inwareid);
            if($inware_record['repid']){
                //有维修单号，获取维修单信息
                $RepairModel = new RepairModel();
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($inware_record['assid']);
                //维修信息
                $repArr = $RepairModel->getRepairBasic($inware_record['repid']);
                //配件信息
                $parts = $RepairModel->get_repair_parts($inware_record['repid']);
                $this->assign('asArr', $asArr);
                $this->assign('repArr', $repArr);
                $this->assign('parts', $parts);
                $out = $RepairModel->DB_get_one('parts_outware_record','status',['repid'=>$inware_record['repid']]);
                if($out && $out['status'] == 0){
                    //未出库，可修改
                    $this->assign('can_edit', 1);
                }
            }else{
                //与维修单没关联的  查询是否已出库
                $out_num = $PartsModel->DB_get_count('parts_inware_record_detail',['status'=>1,'inwareid'=>$inware_record['inwareid']]);
                if($out_num == 0){
                    //未出库，可删除
                    $this->assign('can_del', 1);
                }
            }
            $inware_detail=$PartsModel->getInwareRecordDetail($inwareid);
            $this->assign('inware', $inware_record);
            $this->assign('inware_detail', $inware_detail);
            $this->display('showPartsInwareDetails');
        } else {
            $this->error('非法操作');
        }
    }

    //入库单列表页
    private function showPartsInWareList()
    {
        $OfflineSuppliersModel = new OfflineSuppliersModel();
        $supplierResult = $OfflineSuppliersModel->getSuppliers(C('CONTRACT_TYPE_SUPPLIER'));
        $this->assign('partsInWareListUrl', get_url());
        $this->assign('supplier', $supplierResult['result']);
        $supplierInfo = $OfflineSuppliersModel->getSupplierSelect();
        $this->assign('supplierInfo', $supplierInfo);
        $this->display();
    }

    //新增入库
    public function partsInWare()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'getCity':
                    //获取城市
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getCity();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getAreas':
                    //获取区/城镇
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getAreas();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addSuppliers':
                    //补充厂家
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->addOfflineSupplier();
                    if ($result['status'] == C('SUCCESS_STATUS')) {
                        $result['result']['sup_num'] = $OfflineSuppliersModel->getSupNum();
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'canJoinPartsList':
                    //获取可采购的配件字典数据
                    $PartsModel=new PartsModel();
                    $result = $PartsModel->canJoinPartsList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'partsInWareApply':
                    //采购申请入库记录操作
                    $PartsModel=new PartsModel();
                    $result = $PartsModel->partsInWareApply();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'backup':
                    //退回到入库前状态
                    $PartsModel=new PartsModel();
                    $result = $PartsModel->backup();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'delInware':
                    $PartsModel=new PartsModel();
                    $result = $PartsModel->delInware();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //新增操作
                    $PartsModel=new PartsModel();
                    $result = $PartsModel->partsInWare();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'partsInWareApply':
                    //显示入库申请单页面
                    $this->showPartsInWareApply();
                    break;
                default :
                    //显示新增入库单页面
                    $this->showPartsInWare();
                    break;
            }
        }
    }

    //入库申请单页面
    private function showPartsInWareApply()
    {
        $inwareid=I('GET.inwareid');
        if($inwareid){
            $PartsModel=new PartsModel();
            $inware_record=$PartsModel->getInwareRecordBasic($inwareid);
            if($inware_record){
                //入库申请单信息
                $apply=$PartsModel->getInwareApply($inwareid);
                $RepairModel = new RepairModel();
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($inware_record['assid']);
                //维修信息
                $repArr = $RepairModel->getRepairBasic($inware_record['repid']);
                //获取故障问题
                $repArr['fault_problem'] = $RepairModel->getFaultProblem($inware_record['repid']);
                //获取 供应/生产 商
                $supplierResult=$PartsModel->getSuppliers();
                $manufacturerList=[];
                $supplierList=[];
                foreach ($supplierResult as &$svalue){
                    $manufacturer['olsid']=$svalue['olsid'];
                    $manufacturer['sup_name']=$svalue['sup_name'];
                    if($svalue['is_manufacturer']==C('YES_STATUS')){
                        $manufacturerList[]=$manufacturer;
                        $supplierList[]=$manufacturer;
                        continue;
                    }
                    if($svalue['is_supplier']==C('YES_STATUS')){
                        $supplierList[]=$manufacturer;
                    }
                }
                //获取品牌列表
                $brandList=$PartsModel->getBannerList();
                $OfflineSuppliersModel = new OfflineSuppliersModel();
                $provinces = $OfflineSuppliersModel->getProvinces();
                $sup_num = $OfflineSuppliersModel->getSupNum();
                $this->assign('now_date', date('Y-m-d'));
                $this->assign('inware', $inware_record);
                $this->assign('provinces', $provinces);
                $this->assign('sup_num', $sup_num);
                $this->assign('asArr',$asArr);
                $this->assign('apply',$apply);
                $this->assign('repArr',$repArr);
                $this->assign('brandList',$brandList);
                $this->assign('supplierList',$supplierList);
                $this->assign('manufacturerList',$manufacturerList);
                $this->assign('partsInWareApplyUrl', get_url());
                $this->display('partsInWareApply');
            }else{
                $this->error('无申请单记录');
            }
        }else{
            $this->error('非法操作');
        }
    }

    //新增入库单页面
    private function showPartsInWare(){
        $OfflineSuppliersModel = new OfflineSuppliersModel();
        $provinces = $OfflineSuppliersModel->getProvinces();
        $sup_num = $OfflineSuppliersModel->getSupNum();
        //获取 供应/生产 商
        $supplierResult=$OfflineSuppliersModel->getSuppliers(C('CONTRACT_TYPE_SUPPLIER'));
        $this->assign('provinces', $provinces);
        $this->assign('supplierList', $supplierResult['result']);
        $this->assign('sup_num', $sup_num);
        $this->assign('partsInWareUrl', get_url());
        $this->display();
    }


    //配件出库列表
    public function partsOutWareList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取配件出库列表数据
                    $PartsModel = new PartsModel();
                    $result = $PartsModel->partsOutWareList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showPartsOutwareDetails':
                    //显示出库单详情页
                    $this->showPartsOutwareDetails();
                    break;
                default:
                    //显示出库单列表页
                    $this->showPartsOutWareList();
                    break;
            }
        }
    }

    //出库单详情页
    private function showPartsOutwareDetails()
    {
        $outwareid = I('GET.outwareid');
        if ($outwareid) {
            $PartsModel=new PartsModel();
            $outware_record=$PartsModel->getOutwareRecordBasic($outwareid);
            $outware_detail=$PartsModel->getOutwareRecordDetail($outwareid);

            //审核历史
            $RepairModel=new RepairModel();
            if($outware_record['repid']){
                $approves = $RepairModel->getApproveBasic($outware_record['repid']);
                $this->assign('approves', $approves);
            }

            $this->assign('outware', $outware_record);
            $this->assign('outware_detail', $outware_detail);
            $this->display('showPartsOutwareDetails');
        } else {
            $this->error('非法操作');
        }
    }

    //出库单列表页
    private function showPartsOutWareList()
    {
        $this->assign('partsOutWareListUrl', get_url());
        $userData=ToolController::getUser('accept');
        $this->assign('userData', $userData);
        $this->display();
    }

    //新增出库
    public function partsOutWare()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'canJoinOutWareList':
                    //获取可采购的配件字典数据
                    $PartsModel=new PartsModel();
                    $result = $PartsModel->canJoinOutWareList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'partsOutWareApply':
                    //采购申请出库记录操作
                    $PartsModel=new PartsModel();
                    $result = $PartsModel->partsOutWareApply();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //新增操作
                    $PartsModel=new PartsModel();
                    $result = $PartsModel->partsOutWare();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'partsOutWareApply':
                    //显示出库申请单页面
                    $this->showPartsOutWareApply();
                    break;
                default :
                    //显示新增出库单页面
                    $this->showPartsOutWare();
                    break;
            }
        }
    }

    //出库申请单页面
    private function showPartsOutWareApply()
    {
        $outwareid=I('GET.outwareid');
        if($outwareid){
            $PartsModel=new PartsModel();
            $outware_record=$PartsModel->getOutwareRecordBasic($outwareid);
            if($outware_record){
                //出库申请单信息
                $apply=$PartsModel->getOutwareApply($outwareid);
                $RepairModel = new RepairModel();
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($outware_record['assid']);
                //维修信息
                $repArr = $RepairModel->getRepairBasic($outware_record['repid']);
                //获取故障问题
                $repArr['fault_problem'] = $RepairModel->getFaultProblem($outware_record['repid']);
                //获取用户
                $UserModel=new UserModel();
                $userList=$UserModel->getUser();

                $this->assign('now_date', getHandleDate(time()));
                $this->assign('outware', $outware_record);
                $this->assign('userList', $userList);
                $this->assign('asArr',$asArr);
                $this->assign('apply',$apply);
                $this->assign('repArr',$repArr);
                $this->assign('partsOutWareApplyUrl', get_url());
                $this->display('partsOutWareApply');
            }else{
                $this->error('无申请单记录');
            }
        }else{
            $this->error('非法操作');
        }
    }

    //新增入库单页面
    private function showPartsOutWare(){
        $OfflineSuppliersModel = new OfflineSuppliersModel();
        $provinces = $OfflineSuppliersModel->getProvinces();
        //获取 供应/生产 商
        $supplierResult=$OfflineSuppliersModel->getSuppliers(C('CONTRACT_TYPE_SUPPLIER'));
        $userData=ToolController::getUser('accept');
        $this->assign('provinces', $provinces);
        $this->assign('userList', $userData);
        $this->assign('supplierList', $supplierResult['result']);
        $this->assign('partsOutWareUrl', get_url());
        $this->display();
    }

    public function partStockList(){
        if(IS_POST){
            //获取配件出库列表数据
            $PartsModel = new PartsModel();
            $result = $PartsModel->partStockList();
            $this->ajaxReturn($result, 'json');
        }else{
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $supplierResult = $OfflineSuppliersModel->getSuppliers(C('CONTRACT_TYPE_SUPPLIER'));
            $UserModel=new UserModel();
            $userList=$UserModel->getUser();
            array_unshift($userList,array('username'=>'配件库'));
            $this->assign('partStockListUrl', get_url());
            $this->assign('supplier', $supplierResult['result']);
            $this->assign('userList', $userList);
            $this->display();
        }
    }

}
