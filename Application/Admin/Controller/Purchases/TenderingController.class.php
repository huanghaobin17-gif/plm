<?php
/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/8/1
 * Time: 15:36
 */

namespace Admin\Controller\Purchases;
use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\DepartmentModel;
use Admin\Model\PurchasesModel;

/**
 * 招标论证
 * Class TenderingController
 * @package Admin\Controller\Purchases
 */
class TenderingController extends CheckLoginController
{
    private $MODULE = 'Purchases';

    /**
     * 专家评审列表
     */
    public function expertReviewList()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->getExpertReviewList();
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            if($action == 'showExpertReview'){
                $review_id = I('get.id');
                //查询专家评审信息
                $reviewInfo = $purModel->getExpertReviewInfo($review_id);
                $applyInfo = $purModel->getDepartApplyInfo($reviewInfo['apply_id']);
                $assets = $purModel->getDepartApplyAssets($reviewInfo['apply_id']);
                $files = $purModel->getDepartApplyFiles($reviewInfo['apply_id']);
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                $applyInfo['department'] = $departname[$applyInfo['apply_departid']]['department'];
                $applyInfo['apply_type_name'] = $applyInfo['apply_type'] == 1 ? '计划内' : '计划外';
                $this->assign('reviewInfo',$reviewInfo);
                $this->assign('applyInfo',$applyInfo);
                $this->assign('assets',$assets);
                $this->assign('files',$files);
                $this->display('showExpertReview');
            }else{
                if (session('isSuper') == C('YES_STATUS') and C('IS_OPEN_BRANCH') == true) {
                    //显示分院
                    $asModel = new AssetsInfoModel();
                    $hospital = $asModel->get_all_hospital();
                    $this->assign('showHospital', 1);
                    $this->assign('hospital', $hospital);
                    $hospital_id = session('current_hospitalid');
                } else {
                    $this->assign('showHospital', 0);
                    $hospital_id = session('job_hospitalid');
                }
                $departModel = new DepartmentModel();
                $departments = $departModel->DB_get_all('department', 'departid,department', array('is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id));
                $this->assign('departments', $departments);
                $this->assign('hospital_id', $hospital_id);
                $this->assign('expertReviewList',get_url());
                $this->display();
            }
        }
    }

    /**
     * 专家评审
     */
    public function expertReview()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->saveExpertReview();
            $this->ajaxReturn($result);
        }else{
            $review_id = I('get.id');
            //查询专家评审信息
            $reviewInfo = $purModel->getExpertReviewInfo($review_id);
            $applyInfo = $purModel->getDepartApplyInfo($reviewInfo['apply_id']);
            $assets = $purModel->getDepartApplyAssets($reviewInfo['apply_id']);
            $files = $purModel->getDepartApplyFiles($reviewInfo['apply_id']);
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $applyInfo['department'] = $departname[$applyInfo['apply_departid']]['department'];
            $applyInfo['apply_type_name'] = $applyInfo['apply_type'] == 1 ? '计划内' : '计划外';
            $this->assign('reviewInfo',$reviewInfo);
            $this->assign('applyInfo',$applyInfo);
            $this->assign('assets',$assets);
            $this->assign('files',$files);
            $this->assign('expertReview',get_url());
            $this->display();
        }
    }

    /**
     * 询价记录列表
     */
    public function inquiryPricesList()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->getInquiryPricesList();
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            if($action == 'showInquiryPrices'){
                $record_id = I('get.id');
                //获取设备信息
                $recordInfo = $purModel->getInquiryRecordInfo($record_id);
                //获取询价记录
                $details = $purModel->getInquiryDetail($record_id);
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                $recordInfo['apply_date'] = date('Y-m-d',strtotime($recordInfo['apply_time']));
                $recordInfo['department'] = $departname[$recordInfo['apply_departid']]['department'];
                $recordInfo['apply_type_name'] = $recordInfo['apply_type'] == 1 ? '计划内' : '计划外';
                $recordInfo['is_import_name'] = $recordInfo['is_import'] == 1 ? '是' : '否';
                $this->assign('recordInfo',$recordInfo);
                $this->assign('details',$details);
                $this->assign('inquiryPricesList',get_url());
                $this->display('showInquiryPrices');
            }elseif($action == 'showDetail'){
                $detail_id = I('get.id');
                $detailInfo = $purModel->getSupplierDetail('purchases_inquiry_record_detail',$detail_id);
                $files = $purModel->getSupplierFiles('purchases_inquiry_record_detail_file',$detail_id);
                $this->assign('detailInfo',$detailInfo);
                $this->assign('files',$files);
                $this->display('showDetail');
            }else{
                if (session('isSuper') == C('YES_STATUS') and C('IS_OPEN_BRANCH') == true) {
                    //显示分院
                    $asModel = new AssetsInfoModel();
                    $hospital = $asModel->get_all_hospital();
                    $this->assign('showHospital', 1);
                    $this->assign('hospital', $hospital);
                    $hospital_id = session('current_hospitalid');
                } else {
                    $this->assign('showHospital', 0);
                    $hospital_id = session('job_hospitalid');
                }
                $departModel = new DepartmentModel();
                $departments = $departModel->DB_get_all('department', 'departid,department', array('is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id));
                $this->assign('departments', $departments);
                $this->assign('inquiryPricesList',get_url());
                $this->display();
            }
        }
    }

    /**
     * 询价记录登记
     */
    public function inquiryPrices()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $action = I('post.action');
            $result = [];
            switch ($action){
                case 'uploadFile':
                    $result = $purModel->uploadApplySupplierFile(C('UPLOAD_DIR_PURCHASES_TENDER_SUPPLIER_FILE_NAME'));
                    break;
                case 'addPrice':
                    $result = $purModel->saveDetail('purchases_inquiry_record_detail','purchases_inquiry_record_detail_file');
                    break;
                case 'delPrice':
                    $result = $purModel->delDetail('purchases_inquiry_record_detail');
                    break;
                case 'finalSave':
                    $result = $purModel->saveInquiry();
                    break;
            }
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            if($action == 'addPrice'){
                $record_id = I('get.id');
                //招标记录详情
                $recordInfo = $purModel->getInquiryRecordInfo($record_id);
                $this->assign('recordInfo',$recordInfo);
                $this->display('addPrice');
            }else{
                $record_id = I('get.id');
                //获取设备信息
                $recordInfo = $purModel->getInquiryRecordInfo($record_id);
                //获取询价记录
                $details = $purModel->getInquiryDetail($record_id);
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                $recordInfo['apply_date'] = date('Y-m-d',strtotime($recordInfo['apply_time']));
                $recordInfo['department'] = $departname[$recordInfo['apply_departid']]['department'];
                $recordInfo['apply_type_name'] = $recordInfo['apply_type'] == 1 ? '计划内' : '计划外';
                $recordInfo['is_import_name'] = $recordInfo['is_import'] == 1 ? '是' : '否';
                $this->assign('recordInfo',$recordInfo);
                $this->assign('details',$details);
                $this->assign('inquiryPrices',get_url());
                $this->display();
            }
        }
    }


    /**
     * 制定标书列表
     */
    public function tenderingBookList()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->getTenderingBookList();
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            if($action == 'showTenderingBook'){
                $record_id = I('get.id');
                //获取设备信息
                $recordInfo = $purModel->getInquiryRecordInfo($record_id);
                $re = $purModel->DB_get_one('purchases_depart_apply','apply_reason',array('apply_id'=>$recordInfo['apply_id']));
                $recordInfo['apply_reason'] = $re['apply_reason'];
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                $recordInfo['apply_date'] = date('Y-m-d',strtotime($recordInfo['apply_time']));
                $recordInfo['department'] = $departname[$recordInfo['apply_departid']]['department'];
                $recordInfo['apply_type_name'] = $recordInfo['apply_type'] == 1 ? '计划内' : '计划外';
                $recordInfo['is_import_name'] = $recordInfo['is_import'] == 1 ? '是' : '否';
                $recordInfo['buy_type_name'] = $recordInfo['buy_type'] == 1 ? '报废更新' : ($recordInfo['buy_type'] == 2 ? '添置' : '新增');
                //查询标书记录
                $files = $purModel->getReviewFile($record_id);
                $files_1 = $files_2 = array();
                foreach ($files as $k=>$v){
                    if($v['is_pass'] == 2){
                        $files_1[] = $v;
                    }else{
                        $files_2[] = $v;
                    }
                }
                $this->assign('files_1',$files_1);
                $this->assign('files_2',$files_2);
                $this->assign('recordInfo',$recordInfo);
                $this->assign('tenderingBookList',get_url());
                $this->display('showTenderingBook');
            }else{
                if (session('isSuper') == C('YES_STATUS') and C('IS_OPEN_BRANCH') == true) {
                    //显示分院
                    $asModel = new AssetsInfoModel();
                    $hospital = $asModel->get_all_hospital();
                    $this->assign('showHospital', 1);
                    $this->assign('hospital', $hospital);
                    $hospital_id = session('current_hospitalid');
                } else {
                    $this->assign('showHospital', 0);
                    $hospital_id = session('job_hospitalid');
                }
                $departModel = new DepartmentModel();
                $departments = $departModel->DB_get_all('department', 'departid,department', array('is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id));
                $this->assign('departments', $departments);
                $this->assign('tenderingBookList',get_url());
                $this->display();
            }
        }
    }

    /**
     * 制定标书
     */
    public function addTenderingBook()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $action = I('post.action');
            $result = [];
            switch ($action){
                case 'uploadFile':
                    $result = $purModel->uploadApplySupplierFile(C('UPLOAD_DIR_PURCHASES_TENDER_SUPPLIER_FILE_NAME'));
                    break;
                case 'finalSave':
                    $result = $purModel->saveBookFile();
                    break;
            }
            $this->ajaxReturn($result);
        }else{
            $record_id = I('get.id');
            //获取设备信息
            $recordInfo = $purModel->getInquiryRecordInfo($record_id);
            $re = $purModel->DB_get_one('purchases_depart_apply','apply_reason',array('apply_id'=>$recordInfo['apply_id']));
            $recordInfo['apply_reason'] = $re['apply_reason'];
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $recordInfo['apply_date'] = date('Y-m-d',strtotime($recordInfo['apply_time']));
            $recordInfo['department'] = $departname[$recordInfo['apply_departid']]['department'];
            $recordInfo['apply_type_name'] = $recordInfo['apply_type'] == 1 ? '计划内' : '计划外';
            $recordInfo['is_import_name'] = $recordInfo['is_import'] == 1 ? '是' : '否';
            $recordInfo['buy_type_name'] = $recordInfo['buy_type'] == 1 ? '报废更新' : ($recordInfo['buy_type'] == 2 ? '添置' : '新增');
            //查询标书记录
            $files = $purModel->getReviewFile($record_id);
            $files_1 = $files_2 = array();
            foreach ($files as $k=>$v){
                if($v['is_pass'] == 2){
                    $files_1[] = $v;
                }else{
                    $files_2[] = $v;
                }
            }
            $this->assign('files_1',$files_1);
            $this->assign('files_2',$files_2);
            $this->assign('recordInfo',$recordInfo);
            $this->assign('addTenderingBook',get_url());
            $this->display();
        }
    }

    /**
     * 标书审批列表
     */
    public function tbApproveList()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->getTbApproveList();
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            if($action == 'showTbApprove'){
                $record_id = I('get.id');
                //获取设备信息
                $recordInfo = $purModel->getInquiryRecordInfo($record_id);
                $re = $purModel->DB_get_one('purchases_depart_apply','apply_reason',array('apply_id'=>$recordInfo['apply_id']));
                $recordInfo['apply_reason'] = $re['apply_reason'];
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                $recordInfo['apply_date'] = date('Y-m-d',strtotime($recordInfo['apply_time']));
                $recordInfo['department'] = $departname[$recordInfo['apply_departid']]['department'];
                $recordInfo['apply_type_name'] = $recordInfo['apply_type'] == 1 ? '计划内' : '计划外';
                $recordInfo['is_import_name'] = $recordInfo['is_import'] == 1 ? '是' : '否';
                $recordInfo['buy_type_name'] = $recordInfo['buy_type'] == 1 ? '报废更新' : ($recordInfo['buy_type'] == 2 ? '添置' : '新增');
                //查询标书记录
                $files = $purModel->getReviewFile($record_id);
                $files_1 = $files_2 = array();
                foreach ($files as $k=>$v){
                    if($v['is_pass'] == 2){
                        $files_1[] = $v;
                    }else{
                        $files_2[] = $v;
                    }
                }
                $this->assign('files_1',$files_1);
                $this->assign('files_2',$files_2);
                $this->assign('recordInfo',$recordInfo);
                $this->display('showTbApprove');
            }else{
                if (session('isSuper') == C('YES_STATUS') and C('IS_OPEN_BRANCH') == true) {
                    //显示分院
                    $asModel = new AssetsInfoModel();
                    $hospital = $asModel->get_all_hospital();
                    $this->assign('showHospital', 1);
                    $this->assign('hospital', $hospital);
                    $hospital_id = session('current_hospitalid');
                } else {
                    $this->assign('showHospital', 0);
                    $hospital_id = session('job_hospitalid');
                }
                $departModel = new DepartmentModel();
                $departments = $departModel->DB_get_all('department', 'departid,department', array('is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id));
                $this->assign('departments', $departments);
                $this->assign('tbApproveList',get_url());
                $this->display();
            }
        }
    }

    /**
     * 标书审批
     */
    public function tbApprove()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->saveReviewApprove();
            $this->ajaxReturn($result);
        }else{
            $rev_id = I('get.id');
            $revInfo = $purModel->DB_get_one('purchases_tender_review','record_id',array('rev_id'=>$rev_id));
            //获取设备信息
            $recordInfo = $purModel->getInquiryRecordInfo($revInfo['record_id']);
            $re = $purModel->DB_get_one('purchases_depart_apply','apply_reason',array('apply_id'=>$recordInfo['apply_id']));
            $recordInfo['apply_reason'] = $re['apply_reason'];
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $recordInfo['apply_date'] = date('Y-m-d',strtotime($recordInfo['apply_time']));
            $recordInfo['department'] = $departname[$recordInfo['apply_departid']]['department'];
            $recordInfo['apply_type_name'] = $recordInfo['apply_type'] == 1 ? '计划内' : '计划外';
            $recordInfo['is_import_name'] = $recordInfo['is_import'] == 1 ? '是' : '否';
            $recordInfo['buy_type_name'] = $recordInfo['buy_type'] == 1 ? '报废更新' : ($recordInfo['buy_type'] == 2 ? '添置' : '新增');
            //查询标书记录
            $files = $purModel->getReviewFile($revInfo['record_id']);
            $files_1 = $files_2 = array();
            foreach ($files as $k=>$v){
                if($v['is_pass'] == 2){
                    $files_1[] = $v;
                }else{
                    $files_2[] = $v;
                }
            }
            $this->assign('files_1',$files_1);
            $this->assign('files_2',$files_2);
            $this->assign('recordInfo',$recordInfo);
            $this->assign('rev_id',$rev_id);
            $this->assign('record_id',$revInfo['record_id']);
            $this->assign('addTenderingBook',get_url());
            $this->assign('username',session('username'));
            $this->assign('nowday',date('Y-m-d'));
            $this->display();
        }
    }

    /**
     * 标书提交列表
     */
    public function tbSubmitList()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->getTbApproveList();
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            if($action == 'showTbSubmit'){
                $rev_id = I('get.id');
                $revInfo = $purModel->DB_get_one('purchases_tender_review','*',array('rev_id'=>$rev_id));
                //获取设备信息
                $recordInfo = $purModel->getInquiryRecordInfo($revInfo['record_id']);
                $re = $purModel->DB_get_one('purchases_depart_apply','apply_reason',array('apply_id'=>$recordInfo['apply_id']));
                $recordInfo['apply_reason'] = $re['apply_reason'];
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                $recordInfo['apply_date'] = date('Y-m-d',strtotime($recordInfo['apply_time']));
                $recordInfo['department'] = $departname[$recordInfo['apply_departid']]['department'];
                $recordInfo['apply_type_name'] = $recordInfo['apply_type'] == 1 ? '计划内' : '计划外';
                $recordInfo['is_import_name'] = $recordInfo['is_import'] == 1 ? '是' : '否';
                $recordInfo['buy_type_name'] = $recordInfo['buy_type'] == 1 ? '报废更新' : ($recordInfo['buy_type'] == 2 ? '添置' : '新增');
                //查询标书记录
                $files = $purModel->getReviewFile($revInfo['record_id']);
                $files_1 = $files_2 = array();
                foreach ($files as $k=>$v){
                    if($v['is_pass'] == 2){
                        $files_1[] = $v;
                    }else{
                        $files_2[] = $v;
                    }
                }
                //获取评审记录
                $approves = $purModel->getReviewApproves($revInfo['record_id']);
                $this->assign('approves',$approves);
                $this->assign('files_1',$files_1);
                $this->assign('files_2',$files_2);
                $this->assign('recordInfo',$recordInfo);
                $this->assign('revInfo',$revInfo);
                $this->assign('tbSubmitList',get_url());
                $this->display('showTbSubmit');
            }else{
                if (session('isSuper') == C('YES_STATUS') and C('IS_OPEN_BRANCH') == true) {
                    //显示分院
                    $asModel = new AssetsInfoModel();
                    $hospital = $asModel->get_all_hospital();
                    $this->assign('showHospital', 1);
                    $this->assign('hospital', $hospital);
                    $hospital_id = session('current_hospitalid');
                } else {
                    $this->assign('showHospital', 0);
                    $hospital_id = session('job_hospitalid');
                }
                $departModel = new DepartmentModel();
                $departments = $departModel->DB_get_all('department', 'departid,department', array('is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id));
                $this->assign('departments', $departments);
                $this->assign('tbSubmitList',get_url());
                $this->display();
            }
        }
    }

    /**
     * 标书提交
     */
    public function tbSubmit()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->saveReviewSubmit();
            $this->ajaxReturn($result);
        }else{
            $rev_id = I('get.id');
            $revInfo = $purModel->DB_get_one('purchases_tender_review','record_id',array('rev_id'=>$rev_id));
            //获取设备信息
            $recordInfo = $purModel->getInquiryRecordInfo($revInfo['record_id']);
            $re = $purModel->DB_get_one('purchases_depart_apply','apply_reason',array('apply_id'=>$recordInfo['apply_id']));
            $recordInfo['apply_reason'] = $re['apply_reason'];
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $recordInfo['apply_date'] = date('Y-m-d',strtotime($recordInfo['apply_time']));
            $recordInfo['department'] = $departname[$recordInfo['apply_departid']]['department'];
            $recordInfo['apply_type_name'] = $recordInfo['apply_type'] == 1 ? '计划内' : '计划外';
            $recordInfo['is_import_name'] = $recordInfo['is_import'] == 1 ? '是' : '否';
            $recordInfo['buy_type_name'] = $recordInfo['buy_type'] == 1 ? '报废更新' : ($recordInfo['buy_type'] == 2 ? '添置' : '新增');
            //查询标书记录
            $files = $purModel->getReviewFile($revInfo['record_id']);
            $files_1 = $files_2 = array();
            foreach ($files as $k=>$v){
                if($v['is_pass'] == 2){
                    $files_1[] = $v;
                }else{
                    $files_2[] = $v;
                }
            }
            //获取评审记录
            $approves = $purModel->getReviewApproves($revInfo['record_id']);
            $this->assign('approves',$approves);
            $this->assign('files_1',$files_1);
            $this->assign('files_2',$files_2);
            $this->assign('recordInfo',$recordInfo);
            $this->assign('approves',$approves);
            $this->assign('rev_id',$rev_id);
            $this->assign('record_id',$revInfo['record_id']);
            $this->assign('addTenderingBook',get_url());
            $this->assign('username',session('username'));
            $this->assign('nowday',date('Y-m-d'));
            $this->display();
        }
    }

    /**
     * 项目结果列表
     */
    public function tenderingResultList()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->resultLists();
            $this->ajaxReturn($result);
        }else{
            if (session('isSuper') == C('YES_STATUS') and C('IS_OPEN_BRANCH') == true) {
                //显示分院
                $asModel = new AssetsInfoModel();
                $hospital = $asModel->get_all_hospital();
                $this->assign('showHospital', 1);
                $this->assign('hospital', $hospital);
                $hospital_id = session('current_hospitalid');
            } else {
                $this->assign('showHospital', 0);
                $hospital_id = session('job_hospitalid');
            }
            $departModel = new DepartmentModel();
            $departments = $departModel->DB_get_all('department', 'departid,department', array('is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id));
            $this->assign('departments', $departments);
            $this->assign('hospital_id', $hospital_id);
            $this->assign('tenderingResultList',get_url());
            $this->display();
        }
    }

    /**
     * 项目结果
     */
    public function tenderingResult()
    {
        $this->display();
    }
}