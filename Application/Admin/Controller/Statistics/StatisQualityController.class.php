<?php
namespace Admin\Controller\Statistics;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AdverseModel;
use Admin\Model\QualityModel;


class StatisQualityController extends CheckLoginController
{
    private $MODULE = 'Statistics';

    public function qualityAnalysis()
    {
        $this->display();
    }

    public function resultAnalysis()
    {
        $this->display();
    }
}

