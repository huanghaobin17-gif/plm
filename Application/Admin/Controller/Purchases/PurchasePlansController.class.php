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
class PurchasePlansController extends CheckLoginController
{
    public function index()
    {
        $this->display();
    }

    /**
     * 采购计划列表
     */
    public function purchasePlansList()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $result = $purModel->getPlansList();
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            if ($action == 'showPlans') {
                $plans_id = I('get.id');
                //查询计划信息
                $plansInfo = $purModel->getPlansInfo($plans_id);
                //查询设备信息
                $plansAssetsInfo = $purModel->getPlansAssetsInfo($plans_id);
                //查询设备附件
                $plansFilesInfo = $purModel->getPlansFilesInfo($plans_id);
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                $plansInfo['department'] = $departname[$plansInfo['departid']]['department'];
                $plansInfo['plans_status_name'] = $plansInfo['plans_status'] == 0 ? '未启用' : '启用';
                $this->assign('plansInfo', $plansInfo);
                $this->assign('plansAssetsInfo', $plansAssetsInfo);
                $this->assign('plansFilesInfo', $plansFilesInfo);
                $this->assign('now', date('Y-m-d'));
                $this->assign('apply_id', $plansAssetsInfo[0]['apply_id'] ? $plansAssetsInfo[0]['apply_id'] : 0);
                $this->display('showPlans');
            } else {
                $departModel = new DepartmentModel();
                $hospital_id = session('current_hospitalid');
                $where['is_delete'] = C('NO_STATUS');
                $where['hospital_id'] = $hospital_id;
                $where['departid'] = array('in',session('departid'));
                $departments = $departModel->DB_get_all('department', 'departid,department', $where);
                $this->assign('departments', $departments);
                $this->assign('purchasePlansList', get_url());
                $this->display();
            }
        }
    }

    /**
     * 新增采购计划
     */
    public function addPlans()
    {
        if (IS_POST) {
            $purModel = new PurchasesModel();
            $action = I('post.action');
            if ($action == 'getHospitals') {
                $hospital_id = I('post.hospital_id');
                $departments = $this->getSelectDepartments(array('is_delete' => 0, 'hospital_id' => $hospital_id));
                if ($departments) {
                    $result = array('status' => 1, 'departments' => $departments);
                } else {
                    $result = array('status' => -1, 'departments' => []);
                }
            } else {
                $result = $purModel->addPlans();
            }
            $this->ajaxReturn($result);
        } else {
            $departModel = new DepartmentModel();
            $departments = $departModel->DB_get_all('department', 'departid,department', array('departid'=>array('in',session('departid')),'is_delete' => C('NO_STATUS'), 'hospital_id' => session('current_hospitalid')));
            $this->assign('add_user', session('username'));
            $this->assign('add_date', date('Y-m-d'));
            $this->assign('departments', $departments);
            $this->display();
        }
    }

    /**
     * 科室采购计划上报
     */
    public function departReport()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'addAssets':
                    $result = $purModel->addAssets();
                    break;
                case 'editAssets':
                    $result = $purModel->editAssets();
                    break;
                case 'delAssets':
                    $result = $purModel->delAssets();
                    break;
                case 'uploadFile':
                    $result = $purModel->uploadFile();
                    break;
                case 'delFile':
                    $result = $purModel->delFile();
                    break;
                default:
                    $result = $purModel->finalSave();
                    break;
            }
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            $this->assign('departReport', get_url());
            if ($action == 'addAssets') {
                $plans_id = I('get.id');
                $hosInfo = $purModel->DB_get_one('purchases_plans','hospital_id',array('plans_id'=>$plans_id));
                //获取品牌
                $brands = $purModel->DB_get_all('dic_brand','brand_id,brand_name',array('is_delete'=>0));
                $this->assign('plans_id', $plans_id);
                $this->assign('brands', $brands);
                $this->assign('hospital_id', $hosInfo['hospital_id']);
                $this->display('addAssets');
            } elseif ($action == 'editAssets') {
                $assets_id = I('get.assets_id');
                //查询设备信息
                $assetsInfo = $purModel->DB_get_one('purchases_plans_assets', 'assets_id,assets_name,unit,nums,market_price,is_import,buy_type,brand', array('assets_id' => $assets_id, 'is_delete' => C('NO_STATUS')));
                //获取品牌
                $brands = $purModel->DB_get_all('dic_brand','brand_id,brand_name',array('is_delete'=>0));
                $this->assign('brands', $brands);
                $this->assign('old_brand', json_encode(explode(',', $assetsInfo['brand'])));
                $this->assign('assetsInfo', $assetsInfo);
                $this->assign('assets_id', $assets_id);
                $this->display('editAssets');
            } else {
                $plans_id = I('get.id');
                //查询计划信息
                $plansInfo = $purModel->getPlansInfo($plans_id);
                //查询设备信息
                $plansAssetsInfo = $purModel->getPlansAssetsInfo($plans_id);
                //查询设备附件
                $plansFilesInfo = $purModel->getPlansFilesInfo($plans_id);
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                $plansInfo['department'] = $departname[$plansInfo['departid']]['department'];
                $plansInfo['plans_status_name'] = $plansInfo['plans_status'] == 0 ? '未启用' : '启用';
                $this->assign('plansInfo', $plansInfo);
                $this->assign('plansAssetsInfo', $plansAssetsInfo);
                $this->assign('plansFilesInfo', $plansFilesInfo);
                $this->assign('now', date('Y-m-d'));
                $this->display();
            }
        }
    }

    /**
     * Notes: 科室采购计划申请列表
     */
    public function purPlansAppLists()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $result = $purModel->getApprovePlansList();
            $this->ajaxReturn($result);
        } else {
            $departModel = new DepartmentModel();
            $hospital_id = session('current_hospitalid');
            $where['is_delete'] = C('NO_STATUS');
            $where['hospital_id'] = $hospital_id;
            $where['departid'] = array('in',session('departid'));
            $departments = $departModel->DB_get_all('department', 'departid,department', $where);
            $this->assign('departments', $departments);
            $this->assign('purPlansAppLists', get_url());
            $this->display();
        }
    }

    /**
     * 新增科室采购计划
     */
    public function addPurchaseApply()
    {
        if (IS_POST) {
            $purModel = new PurchasesModel();
            $action = I('post.action');
            if ($action == 'getHospitals') {
                $hospital_id = I('post.hospital_id');
                $departments = $this->getSelectDepartments(array('is_delete' => 0, 'hospital_id' => $hospital_id));
                if ($departments) {
                    $result = array('status' => 1, 'departments' => $departments);
                } else {
                    $result = array('status' => -1, 'departments' => []);
                }
            } else {
                $result = $purModel->addPlans();
            }
            $this->ajaxReturn($result);
        } else {
            //查是否管理员 以及是否开启分院功能
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
            $this->assign('add_user', session('username'));
            $this->assign('add_date', date('Y-m-d'));
            $this->assign('departments', $departments);
            $this->display();
        }
    }


    /**
     * 采购计划审批
     */
    public function purchasePlanApprove()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $result = $purModel->saveApprove();
            $this->ajaxReturn($result);
        } else {
            $plans_id = I('get.id');
            //计划信息
            $plansInfo = $purModel->getPlansInfo($plans_id);
            //设备信息
            $plansAssetsInfo = $purModel->getPlansAssetsInfo($plans_id);
            //附件信息
            $plansFilesInfo = $purModel->getPlansFilesInfo($plans_id);
            //审批信息
            $approveInfo = $purModel->getPlansApproveInfo($plans_id);
            $canApprove = false;
            if ($plansInfo['approve_status'] == C('OUTSIDE_STATUS_APPROVE')) {
                if ($plansInfo['current_approver']) {
                    $current_approver = explode(',', $plansInfo['current_approver']);
                    $current_approver_arr = [];
                    foreach ($current_approver as &$current_approver_value) {
                        $current_approver_arr[$current_approver_value] = true;
                    }
                    if ($current_approver_arr[session('username')]) {
                        $canApprove = true;
                    }
                }
            }
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $plansInfo['department'] = $departname[$plansInfo['departid']]['department'];
            $plansInfo['plans_status_name'] = $plansInfo['plans_status'] == 0 ? '未启用' : '启用';
            $this->assign('plansInfo', $plansInfo);
            $this->assign('plansAssetsInfo', $plansAssetsInfo);
            $this->assign('plansFilesInfo', $plansFilesInfo);
            $this->assign('approveInfo', $approveInfo);
            $this->assign('canApprove', $canApprove);
            $this->assign('username', session('username'));
            $this->assign('now', date('Y-m-d'));
            $this->display();
        }
    }

    /**
     * 查看采购计划详情
     */
    public function showPurchasePlan()
    {
        $this->display();
    }

}