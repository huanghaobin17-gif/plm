<?php
namespace Admin\Controller\Statistics;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AdverseModel;
use Admin\Model\CommonModel;
use Admin\Model\QualityModel;
use Admin\Model\RepairModel;


class StatisRepairController extends CheckLoginController
{
    private $MODULE = 'Statistics';

    /**
     * Notes: 维修费用统计
     */
    public function repairFeeStatis()
    {
        $this->display();
    }

    /**
     * Notes: 维修费用分析
     */
    public function repairAnalysis()
    {
        $this->display();
    }

    /**
     * Notes: 工程师工作量对比
     */
    public function engineerCompar()
    {
        $this->display();
    }

    /**
     * Notes: 工程师评价对比
     */
    public function engineerEva()
    {
        $this->display();
    }

    /**
     * Notes: 科室维修费用趋势分析
     */
    public function repairFeeTrend()
    {   

        $this->display();
    }

    private function formatYearData($year_data,$count_type,$show_type)
    {
        $option = [];
        return $option;
    }
}

