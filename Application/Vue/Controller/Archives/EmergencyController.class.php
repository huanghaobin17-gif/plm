<?php

namespace Vue\Controller\Archives;

use Vue\Controller\Login\IndexController;
use Vue\Model\ArchivesModel;
class EmergencyController extends IndexController
{
    private $MODULE = 'Emergency';
    private $Controller = 'Emergency';

    /**
     * 应急预案列表
     */
    public function emergencyPlanList()
    {
        $ArchivesModel = new ArchivesModel();
        $result = $ArchivesModel->get_emergency_list();
        $this->ajaxReturn($result,'json');
    }

    /**
     * 显示预案详情
     */
    public function showEmergencyPlan()
    {
        $ArchivesModel = new ArchivesModel();
        $data = $ArchivesModel->get_emergency();
        $file = $ArchivesModel->get_file();
        $result['status'] = 1;
        $result['data'] = $data;
        $result['file'] = $file;
        $this->ajaxReturn($result,'json');
    }
    /*
    预览功能 
     */
    public function showFile()
    {
        $path = I('get.path');
        $name = I('get.name');
        $this->assign('pdfsrc',$path);
        $this->assign('name',$name);
        $this->display();
    }
}