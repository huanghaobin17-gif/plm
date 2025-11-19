<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2018/10/19
 * Time: 10:50
 */

namespace Admin\Controller\Statistics;;


use Admin\Controller\Login\CheckLoginController;
use Admin\Model\DepartmentModel;
use Admin\Model\PurchasesStatisModel;

class StatisPurchasesController extends CheckLoginController
{
    private $MODULE = 'Statistics';

    /**
     * Notes: 采购费用统计
     */
    public function purFeeStatis()
    {
        $this->display();
    }

    /**
     * Notes: 采购费用分析
     */
    public function purAnalysis()
    {
        $this->display();
    }

}