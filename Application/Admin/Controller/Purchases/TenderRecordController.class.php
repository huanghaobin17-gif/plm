<?php

/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/8/1
 * Time: 14:27
 */
namespace Admin\Controller\Purchases;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\DepartmentModel;
use Admin\Model\PurchasesModel;

/**
 * 采购计划
 * Class PurchasePlansController
 * @package Admin\Controller\Purchases
 */
class TenderRecordController extends CheckLoginController
{
    private $MODULE = 'Purchases';

    /**
     * 招标记录列表
     */
    public function tenderRecordList()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->getTenderLists();
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            switch ($action){
                case 'showTender':
                    $record_id = I('get.id');
                    //招标记录详情
                    $tenderInfo = $purModel->getTenderInfo($record_id);
                    //招标附件信息
                    $files = $purModel->getDepartApplyFiles($tenderInfo['apply_id']);
                    //招标明细信息
                    $details = $purModel->getTenderDetail($tenderInfo['record_id']);
                    $departname = array();
                    include APP_PATH . "Common/cache/department.cache.php";
                    $tenderInfo['department'] = $departname[$tenderInfo['apply_departid']]['department'];
                    $tenderInfo['apply_date'] = date('Y-m-d',strtotime($tenderInfo['apply_time']));
                    $tenderInfo['is_import_name'] = $tenderInfo['is_import'] == 1 ? '是' : '否';
                    $this->assign('showTender',get_url());
                    $this->assign('tenderInfo',$tenderInfo);
                    $this->assign('files',$files);
                    $this->assign('details',$details);
                    $this->display('showTender');
                    break;
                case 'showTenderDetail':
                    $detail_id = I('get.id');
                    $detailInfo = $purModel->getSupplierDetail('purchases_tender_detail',$detail_id);
                    $files = $purModel->getSupplierFiles('purchases_tender_detail_file',$detail_id);
                    $this->assign('detailInfo',$detailInfo);
                    $this->assign('files',$files);
                    $this->display('showTenderDetail');
                    break;
                default:
                    $hospital_id = session('current_hospitalid');
                    $departModel = new DepartmentModel();
                    $departments = $departModel->DB_get_all('department', 'departid,department', array('is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id));
                    $this->assign('departments', $departments);
                    $this->assign('hospital_id', $hospital_id);
                    $this->assign('tenderRecordList',get_url());
                    $this->display();
                    break;
            }
        }
    }

    /**
     * 处理标书
     */
    public function handleTender()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $action = I('post.action');
            $result = [];
            switch ($action){
                case 'uploadFile':
                    $result = $purModel->uploadApplySupplierFile(C('UPLOAD_DIR_PURCHASES_TENDER_SUPPLIER_FILE_NAME'));
                    break;
                case 'addFac':
                    $result = $purModel->saveDetail('purchases_tender_detail','purchases_tender_detail_file');
                    break;
                case 'delFac':
                    $result = $purModel->delDetail('purchases_tender_detail');
                    break;
                case 'finalSave':
                    $result = $purModel->saveFinalSupplierSelect();
                    break;
            }
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            switch ($action){
                case 'addFacDetail':
                    $record_id = I('get.id');
                    //招标记录详情
                    $tenderInfo = $purModel->getTenderInfo($record_id);
                    $this->assign('tenderInfo',$tenderInfo);
                    $this->assign('handleTender',get_url());
                    $this->display('addFacDetail');
                    break;
                default:
                    $record_id = I('get.id');
                    //招标记录详情
                    $tenderInfo = $purModel->getTenderInfo($record_id);
                    //招标附件信息
                    $files = $purModel->getDepartApplyFiles($tenderInfo['apply_id']);
                    //招标明细信息
                    $details = $purModel->getTenderDetail($tenderInfo['record_id']);
                    $departname = array();
                    include APP_PATH . "Common/cache/department.cache.php";
                    $tenderInfo['department'] = $departname[$tenderInfo['apply_departid']]['department'];
                    $tenderInfo['apply_date'] = date('Y-m-d',strtotime($tenderInfo['apply_time']));
                    $tenderInfo['is_import_name'] = $tenderInfo['is_import'] == 1 ? '是' : '否';
                    $this->assign('handleTender',get_url());
                    $this->assign('tenderInfo',$tenderInfo);
                    $this->assign('files',$files);
                    $this->assign('details',$details);
                    $this->display();
                    break;
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
}