<?php

namespace Admin\Controller\Inventory;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\InventoryPlanModel;
use Admin\Model\UserModel;

class InventoryPlanController extends CheckLoginController
{
    private $MODULE = 'Inventory';
    private $Controller = 'InventoryPlan';

    private $inventoryPlanModel;
    private $assetsInfoModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->inventoryPlanModel = new InventoryPlanModel();
        $this->assetsInfoModel    = new AssetsInfoModel();
        $this->userModel          = new UserModel();

    }

    /**
     * 列表
     *
     * @return void
     */
    public function inventoryPlanList()
    {
        if (IS_POST) {
            //实例化模型
            $result = $this->inventoryPlanModel->getList();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->assign('inventoryPlanList', get_url());
            $this->display();
        }
    }


    /**
     * 批量删除
     *
     * @return void
     */
    public function batchDelInventoryPlan()
    {
        if (IS_POST) {
            $inventory_plan_ids = rtrim(I('post.inventory_plan_ids'), ',');
            if (!$inventory_plan_ids) {
                $this->ajaxReturn(
                    [
                        'status' => -1,
                        'msg'    => '请选择需要删除的盘点计划',
                    ]
                );
            }
            $result = $this->inventoryPlanModel->batchDel($inventory_plan_ids);
            $this->ajaxReturn($result);
        }
    }


    /**
     * 删除
     *
     * @return void
     */
    public function delInventoryPlan()
    {
        if (IS_POST) {
            $where['hospital_id']       = session('current_hospitalid');
            $where['is_delete']         = 0;
            $where['inventory_plan_id'] = I('post.inventory_plan_id');

            $inventory_plan                    = $this->inventoryPlanModel->where($where)->find();
            $inventory_plan['inventory_users'] = json_decode($inventory_plan['inventory_users'], true);
            $result                            = $this->inventoryPlanModel->del($inventory_plan);
            $this->ajaxReturn($result);
        }
    }

    /**
     * 新增
     *
     * @return void
     */
    public function addInventoryPlan()
    {
        $departid = session('departid');
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'addAssetsList':
                    $result = $this->inventoryPlanModel->addAssetsList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'delAssetsList':
                    $result = $this->inventoryPlanModel->delAssetsList();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $result = $this->inventoryPlanModel->addPlan();
                    $this->ajaxReturn($result);
            }
        } else {
            $departids   = explode(',', $departid);
            $departments = $this->getDepartname($departids);
            //财务分类
            $assets_finance = $this->assetsInfoModel->getBaseSettingAssets('assets_finance');

            $inventoryPlanUserList = $this->userModel->getUsers('saveOrEndInventoryPlan', session('departid'));
            $this->assign('inventoryPlanUserList', $inventoryPlanUserList);
            $this->assign('addInventoryPlanUrl', get_url());
            $this->assign('assetsFinance', $assets_finance);
            $this->assign('departments', $departments);
            $this->display();
        }
    }

    /**
     * 编辑
     *
     * @return void
     */
    public function editInventoryPlan()
    {
        $departid = session('departid');
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'addAssetslist':
                    $result = $this->inventoryPlanModel->addAssetsList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'delAssetslist':
                    $result = $this->inventoryPlanModel->delAssetsList();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $result = $this->inventoryPlanModel->editPlan();
                    $this->ajaxReturn($result);
            }
        } else {
            $departids   = explode(',', $departid);
            $departments = $this->getDepartname($departids);
            //财务分类
            $assets_finance = $this->assetsInfoModel->getBaseSettingAssets('assets_finance');

            $inventoryPlanUserList = $this->userModel->getUsers('saveOrEndInventoryPlan', session('departid'));
            $this->assign('inventoryPlanUserList', $inventoryPlanUserList);
            $this->assign('assetsFinance', $assets_finance);
            $this->assign('departments', $departments);
            $this->assign('editInventoryPlanUrl', get_url());
            $this->inventoryPlanDetailAssign();
            $this->display();
        }
    }

    /**
     * 详情
     *
     * @return void
     */
    public function showInventoryPlan()
    {
        if (IS_POST) {
            $data = $this->inventoryPlanModel->show();
            $this->ajaxReturn($data, 'json');
        } else {
            $hospital_id = session('current_hospitalid');
            $this->assign('showInventoryPlan', get_url());
            $this->dicAssign($hospital_id);
            $this->inventoryPlanDetailAssign();
            $this->display();
        }
    }


    /**
     * 批量发布计划
     *
     * @return void
     */
    public function batchReleaseInventoryPlan()
    {
        if (IS_POST) {
            $inventory_plan_ids = rtrim(I('post.inventory_plan_ids'), ',');
            if (!$inventory_plan_ids) {
                $this->ajaxReturn(
                    [
                        'status' => -1,
                        'msg'    => '请选择需要发布的盘点计划',
                    ]
                );
            }
            $result = $this->inventoryPlanModel->batchRelease($inventory_plan_ids);
            $this->ajaxReturn($result);
        } else {
            $this->display();
        }
    }

    /**
     * 暂存|结束 盘点
     *
     * @return void
     */
    public function saveOrEndInventoryPlan()
    {
        $hospital_id = session('current_hospitalid');
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'getDicAssetsDetail':
                    //获取 对应字典数据详情
                    $result = $this->assetsInfoModel->getDicAssetsDetail();
                    break;
                case 'getdepartDetail':
                    //获取科室对应的信息
                    $result = $this->assetsInfoModel->getdepartDetail();
                    break;
                default:
                    $result = $this->inventoryPlanModel->saveOrEnd();
            }
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'addAssetAll':
                    $result = $this->inventoryPlanModel->addAssetAll();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $this->assign('saveOrEndInventoryPlanUrl', get_url());
                    $this->dicAssign($hospital_id);
                    $this->inventoryPlanDetailAssign();
                    $this->display();
            }
        }
    }


    /**
     * 审核列表
     *
     * @return void
     */
    public function inventoryPlanApproveList()
    {
        if (IS_POST) {
            //实例化模型
            $result = $this->inventoryPlanModel->getApproveList();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->assign('inventoryPlanApproveListUrl', get_url());
            $this->display();
        }
    }


    /**
     * 审核
     *
     * @return void
     */
    public function auditInventoryPlanApprove()
    {
        $hospital_id = session('current_hospitalid');
        if (IS_POST) {
            $result = $this->inventoryPlanModel->audit();
            $this->ajaxReturn($result);
        } else {

            $this->assign('auditInventoryPlanApproveUrl', get_url());
            $this->assign('username', session('username'));
            $this->assign('date', date('Y-m-d'));
            $this->dicAssign($hospital_id);
            $this->inventoryPlanDetailAssign();
            $this->display();
        }
    }


    protected function inventoryPlanDetailAssign()
    {
        $result = $this->inventoryPlanModel->show();
        $this->assign('inventoryPlanData', str_json_encode($result));
    }
}