<?php

namespace Admin\Controller\Benefit;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\NotCheckLogin\PublicController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\BenefitModel;
use Admin\Model\DepartmentModel;
use Admin\Model\RepairModel;

class BenefitController extends CheckLoginController
{

    public function index()
    {
        $this->display();
    }

    //设备收支录入
    public function assetsBenefitList()
    {
        $this->display();

    }

    //单机效益分析
    public function singleBenefitList()
    {
        $this->display();
    }


    //单机效益分析
    public function assetsBenefitData()
    {
        $this->display();
    }

    //科室效益分析列表
    public function departmentBenefitList()
    {
        $this->display();
    }

    //科室效益分析
    public function departmentBenefitData()
    {
        $this->display();
    }

    //批量录入收支明细
    public function batchAddBenefit()
    {
        $this->display();
    }


    //批量导出收支明细
    public function exportBenefit()
    {
        $BenefitModel = new BenefitModel();
        $BenefitModel->exportBenefit();
    }

}
