<?php

/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/8/1
 * Time: 14:27
 */
namespace Admin\Controller\Purchases;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\DepartmentModel;
use Admin\Model\PurchasesModel;

/**
 * 采购进程
 * Class PurchaseLifeController
 * @package Admin\Controller\Purchases
 */
class PurchaseLifeController extends CheckLoginController
{
    private $MODULE = 'Purchases';

    /**
     * 招标记录列表
     */
    public function purchaseLifeList()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->getLifeList();
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            $this->assign('purchaseLifeList',get_url());
            if($action == 'showLife'){
                $assets_id = I('get.id');
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                //查询设备信息
                $assInfo = $purModel->getAssetsInfo($assets_id);
                $assInfo['department'] = $departname[$assInfo['departid']]['department'];
                $assInfo['is_import_name'] = $assInfo['is_import'] == 0 ? '否' : '是';
                //查询对应申请单信息
                $applyInfo = $purModel->getDepartApplyInfo($assInfo['apply_id']);
                //查询申请单附件
                $files = $purModel->getDepartApplyFiles($assInfo['apply_id']);
                //查询设备招标明细
                $suppliers = $purModel->getTenderDetailByAssetsId($assets_id);
                //查询采购进程明细
                $tips = $purModel->getPurchasesProgressDetail($assets_id,$assInfo['apply_id'],$assInfo['contract_id'],$assInfo['contract_time'],$assInfo['is_check'],$assInfo['check_time']);
                $progress = $this->getProgress($applyInfo['expert_review'],$tips);
                $tips = array_reverse($tips);
                $this->assign('assInfo',$assInfo);
                $this->assign('applyInfo',$applyInfo);
                $this->assign('files',$files);
                $this->assign('suppliers',$suppliers);
                $this->assign('tips',$tips);
                $this->assign('progress',$progress);
                $this->display('showLife');
            }elseif($action == 'showTenderDetail'){
                $detail_id = I('get.id');
                $detailInfo = $purModel->getSupplierDetail('purchases_tender_detail',$detail_id);
                //获取供应商附件
                $sup_files = $purModel->getSupplierFiles('purchases_tender_detail_file',$detail_id);
                $this->assign('detailInfo',$detailInfo);
                $this->assign('sup_files',$sup_files);
                $this->display('showTenderDetail');
            }else{
                $hospital_id = session('current_hospitalid');
                $departModel = new DepartmentModel();
                $departments = $departModel->DB_get_all('department', 'departid,department', array('is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id));
                $this->assign('departments', $departments);
                $this->assign('hospital_id', $hospital_id);
                $this->display();
            }
        }
    }

    private function getProgress($expert_review,$tips)
    {
        $progress = [];
        if($expert_review == 0){
            $progress[0]['class'] = 'nocompleteProgress';
            $progress[0]['type_name'] = '科室申请';
            $progress[1]['class'] = 'nocompleteProgress';
            $progress[1]['type_name'] = '采购审批';
            $progress[2]['class'] = 'nocompleteProgress';
            $progress[2]['type_name'] = '招标记录';
            $progress[3]['class'] = 'nocompleteProgress';
            $progress[3]['type_name'] = '合同录入';
            $progress[4]['class'] = 'nocompleteProgress';
            $progress[4]['type_name'] = '设备验收';
        }else{
            $progress[0]['class'] = 'nocompleteProgress';
            $progress[0]['type_name'] = '科室申请';
            $progress[1]['class'] = 'nocompleteProgress';
            $progress[1]['type_name'] = '采购审批';
            $progress[2]['class'] = 'nocompleteProgress';
            $progress[2]['type_name'] = '招标论证';
            $progress[3]['class'] = 'nocompleteProgress';
            $progress[3]['type_name'] = '招标记录';
            $progress[4]['class'] = 'nocompleteProgress';
            $progress[4]['type_name'] = '合同录入';
            $progress[5]['class'] = 'nocompleteProgress';
            $progress[5]['type_name'] = '设备验收';
        }
        foreach ($progress as $k=>$v){
            foreach ($tips as $k1=>$v1){
                if($v1['type_name'] == $v['type_name']){
                    if(isset($v1['new'])){
                        $progress[$k]['class'] = 'doingProgress';
                    }else{
                        $progress[$k]['class'] = 'completeProgress';
                    }
                }
            }
        }
        return $progress;
    }

    /**
     * 处理标书
     */
    public function handleTender()
    {
        if(IS_POST){

        }else{
            $action = I('get.action');
            if($action == 'addFacDetail'){
                $this->display('addFacDetail');
            }else{
                $this->assign('handleTender',get_url());
                $this->display();
            }
        }
    }

    /**
     * 处理标书
     */
    public function showTender()
    {
        if(IS_POST){

        }else{
            $action = I('get.action');
            if($action == 'showTenderDetail'){
                $this->display('showTenderDetail');
            }else{
                $this->assign('showTender',get_url());
                $this->display();
            }
        }
    }

    /**
     * 采购计划审批
     */
    public function purchasePlanApprove()
    {
        $this->display();
    }

    /**
     * 查看采购计划详情
     */
    public function showPurchasePlan()
    {
        $this->display();
    }

}