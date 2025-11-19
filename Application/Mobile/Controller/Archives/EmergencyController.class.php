<?php

namespace Mobile\Controller\Archives;

use Mobile\Controller\Login\IndexController;
use Mobile\Model\ArchivesModel;
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
        $data = $ArchivesModel->get_emergency_list();
        $this->assign('data', $data);
        $this->assign('url', get_url());
        $this->display();
    }

    /**
     * 显示预案详情
     */
    public function showEmergencyPlan()
    {
        $ArchivesModel = new ArchivesModel();
        $data = $ArchivesModel->get_emergency();
        $file = $ArchivesModel->get_file();
        $this->assign('data', $data);
        $this->assign('file', $file);
        $this->assign('url', get_url());
        $this->display();
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