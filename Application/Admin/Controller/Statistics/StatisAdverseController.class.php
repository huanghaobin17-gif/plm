<?php
namespace Admin\Controller\Statistics;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AdverseModel;


class StatisAdverseController extends CheckLoginController
{
    private $MODULE = 'Statistics';

    public function adverseAnalysis()
    {
        $this->display();
    }
}

