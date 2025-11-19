<?php

namespace App\Controller\Inventory;

use Admin\Model\AssetsInfoModel;
use Admin\Model\InventoryPlanModel;
use App\Controller\Login\IndexController;
use App\Service\UserInfo\UserInfo;


class InventoryPlanController extends IndexController
{
    private $inventoryPlanModel;
    private $assetsInfoModel;

    public function __construct()
    {
        parent::__construct();
        $this->inventoryPlanModel = new InventoryPlanModel();
        $this->assetsInfoModel = new AssetsInfoModel();
    }

    /**
     * 列表
     *
     * @return void
     */
    public function inventoryPlanList()
    {
        if (IS_POST) {
            $result = $this->inventoryPlanModel->getList();
            $result['status'] = 1;
            $this->ajaxReturn($result, 'json');
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
            $action = I('post.action');
            $assnum = I('post.assnum');
            switch ($action) {
                //扫码时获取设备编码详情
                case 'getAsset':
                    $asset = $this->inventoryPlanModel->DB_get_one('assets_info',
                        '*', [
                            'assnum' => $assnum,
                            'hospital_id' => UserInfo::getInstance()->get('current_hospitalid'),
                            'is_delete' => '0',
                        ]);
                    if (!$asset) {
                        $asset = $this->DB_get_one('assets_info', '',
                            [
                                'assorignum' => $assnum,
                                'hospital_id' => UserInfo::getInstance()->get('current_hospitalid'),
                                'is_delete' => '0',
                            ]);
                    }
                    if (!$asset) {
                        $asset = $this->DB_get_one('assets_info', '',
                            [
                                'assorignum_spare' => $assnum,
                                'hospital_id' => UserInfo::getInstance()->get('current_hospitalid'),
                                'is_delete' => '0',
                            ]);
                    }

                    if (empty($asset)) {
                        $data['status'] = -1;
                        $data['msg'] = '该设备不是医院的设备';
                        $this->ajaxReturn($data, 'json');
                    } else {
                        $asset['department'] = $this->inventoryPlanModel->DB_get_one('department', 'department',
                            ['id' => $asset['department_id']])['department'];
                        $data['status'] = 1;
                        $data['row'] = $asset;
                        $this->ajaxReturn($data, 'json');
                    }

                    break;
                default:
                    $data = $this->inventoryPlanModel->show();
                    $data['status'] = 1;
                    $this->ajaxReturn($data, 'json');
                    break;
            }

        }
    }

    /**
     * 暂存|结束 盘点
     *
     * @return void
     */
    public function saveOrEndInventoryPlan()
    {
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'getDicAssetsDetail':
                    //获取 对应字典数据详情
                    $result = $this->assetsInfoModel->getDicAssetsDetail();
                    $result['status'] = 1;
                    break;
                case 'getdepartDetail':
                    //获取科室对应的信息
                    $result = $this->assetsInfoModel->getdepartDetail();
                    $result['status'] = 1;
                    break;
                default:
//                    $result = $this->inventoryPlanModel->saveOrEnd();
                    $result = $this->inventoryPlanModel->AppSaveOrEnd();
            }
            $this->ajaxReturn($result);
        }
    }

    public function dealInventoryAsset()
    {
        $result = $this->inventoryPlanModel->updatePlanAssetsStatus();
        $this->ajaxReturn($result);
    }


    public function getAssetInfo()
    {
        $result = $this->inventoryPlanModel->getAssetInfo();
        $this->ajaxReturn($result);
    }
}