<?php
/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/8/1
 * Time: 14:59
 */

namespace Admin\Controller\Purchases;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\DepartmentModel;
use Admin\Model\PurchasesModel;

/**
 * 科室采购申请
 * Class PurchaseApplyController
 * @package Admin\Controller\Purchases
 */
class PurchaseApplyController extends CheckLoginController
{

    protected $MODULE='Purchases';
    /**
     * 科室采购申请列表
     */
    public function purchaseApplyList()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->getDepartApplyLists();
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            if($action == 'showApply'){
                $apply_id = I('get.id');
                $applyInfo = $purModel->getDepartApplyInfo($apply_id);
                $assetsInfo = $purModel->getDepartApplyAssets($apply_id);
                $fileInfo = $purModel->getDepartApplyFiles($apply_id);
                //审批信息
                $approveInfo = $purModel->getApplyApproveInfo($apply_id);
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                $applyInfo['department'] = $departname[$applyInfo['apply_departid']]['department'];
                $applyInfo['apply_date'] = date('Y-m-d',strtotime($applyInfo['apply_time']));
                $applyInfo['apply_type_name'] = $applyInfo['apply_type'] == 1 ? '计划内' : '计划外';
                $applyInfo['approve_status_name'] = $applyInfo['approve_status'] == 0 ? '未审核' : ($applyInfo['approve_status'] == 1 ? '已通过' : ($applyInfo['approve_status'] == 2 ? '不通过' : '不用审核'));
                $this->assign('applyInfo',$applyInfo);
                $this->assign('assetsInfo',$assetsInfo);
                $this->assign('fileInfo',$fileInfo);
                $this->assign('approveInfo',$approveInfo);
                $this->display('showApply');
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
                $this->assign('purchaseApplyList',get_url());
                $this->display();
            }
        }
    }

    /**
     * 科室新增采购申请
     */
    public function addPurchaseApply()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $action = I('post.action');
            if ($action == 'uploadFile') {
                $result = $purModel->uploadApplySupplierFile(C('UPLOAD_DIR_PURCHASES_APPLY_ASSETS_FILE_NAME'));
            }elseif($action == 'addPlnasAssets'){
                $result['assets'] = $purModel->addPlnasAssets(I('post.plans_id'));
                $result['files'] = $purModel->addPlnasFiles(I('post.plans_id'));
            }else{
                $result = $purModel->addDepartApply();
            }
            $this->ajaxReturn($result);
        } else {
            $this->assign('addPurchaseApply', get_url());
            $action = I('get.action');
            if($action == 'addAssets'){
                $this->display('addAssets');
            }else{
                //查询品牌字典
                $brands = $purModel->DB_get_all('dic_brand','*',array('is_delete'=>0));
                $hospital_id = session('current_hospitalid');
                $this->assign('add_user', session('username'));
                $this->assign('add_date', date('Y-m-d'));
                $this->assign('hospital_id', $hospital_id);
                $this->assign('brands', $brands);
                $this->display();
            }
        }
    }

    /**
     * 科室采购申请审批
     */
    public function approveApply()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->saveApplyApprove();
            //查询是否已通过审批，如通过，则生成招标信息
            if($result['status'] == 1){
                $apply_id = I('post.apply_id');
                $applyInfo = $purModel->getDepartApplyInfo($apply_id);
                if($applyInfo['approve_status'] == C('STATUS_APPROE_SUCCESS')){
                    //审批通过
                    if($applyInfo['expert_review'] == 0){
                        //不需专家评审，直接生成招标记录
                        $purModel->createTenderRecord($applyInfo);
                    }else{
                        //需要专家评审，生成专家评审信息
                        $purModel->insertData('purchases_expert_review',array('apply_id'=>$applyInfo['apply_id']));
                        //==========================================短信 START==========================================
                        $settingData = $purModel->checkSmsIsOpen($this->MODULE);
                        if ($settingData) {
                            //有开启短信 通知专家评审
                            $departname = [];
                            include APP_PATH . "Common/cache/department.cache.php";
                            $applyInfo['department'] = $departname[$applyInfo['apply_departid']]['department'];
                            $ToolMod = new ToolController();
                            $userData=$ToolMod->getUser('expertReview',$applyInfo['apply_departid']);
                            if ($settingData['expertReview']['status'] == C('OPEN_STATUS') && $userData) {
                                $phone=$purModel->formatPhone($userData);
                                $sms = $purModel->formatSmsContent($settingData['expertReview']['content'], $applyInfo);
                                $ToolMod->sendingSMS($phone, $sms, $this->MODULE, $apply_id);
                            }
                        }
                        //==========================================短信 END==========================================
                    }
                }
            }
            $this->ajaxReturn($result);
        }else{
            $apply_id = I('get.id');
            $applyInfo = $purModel->getDepartApplyInfo($apply_id);
            $assetsInfo = $purModel->getDepartApplyAssets($apply_id);
            $fileInfo = $purModel->getDepartApplyFiles($apply_id);
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $applyInfo['department'] = $departname[$applyInfo['apply_departid']]['department'];
            $applyInfo['apply_date'] = date('Y-m-d',strtotime($applyInfo['apply_time']));
            $applyInfo['apply_type_name'] = $applyInfo['apply_type'] == 1 ? '计划内' : '计划外';
            $applyInfo['approve_status_name'] = $applyInfo['approve_status'] == 0 ? '未审核' : ($applyInfo['approve_status'] == 1 ? '已通过' : ($applyInfo['approve_status'] == 2 ? '不通过' : '不用审核'));
            //审批信息
            $approveInfo = $purModel->getApplyApproveInfo($apply_id);
            $canApprove = false;
            if ($applyInfo['approve_status'] == C('OUTSIDE_STATUS_APPROVE')) {
                if ($applyInfo['current_approver']) {
                    $current_approver = explode(',', $applyInfo['current_approver']);
                    $current_approver_arr = [];
                    foreach ($current_approver as &$current_approver_value) {
                        $current_approver_arr[$current_approver_value] = true;
                    }
                    if ($current_approver_arr[session('username')]) {
                        $canApprove = true;
                    }
                }
            }
            $this->assign('approveInfo', $approveInfo);
            $this->assign('canApprove', $canApprove);
            $this->assign('username', session('username'));
            $this->assign('now', date('Y-m-d'));
            $this->assign('applyInfo',$applyInfo);
            $this->assign('assetsInfo',$assetsInfo);
            $this->assign('fileInfo',$fileInfo);
            $this->display();
        }
    }

}