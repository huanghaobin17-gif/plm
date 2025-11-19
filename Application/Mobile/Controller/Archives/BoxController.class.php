<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2020/3/13
 * Time: 15:38
 */

namespace Mobile\Controller\Archives;

use Mobile\Controller\Login\IndexController;

class BoxController extends IndexController
{
    public function boxList()
    {
        $action = I('get.action');
        if($action == 'show_box'){
            $archivesModel = new \Admin\Model\ArchivesModel();
            $box_num = I('get.num');
            //查询boxID
            $box = $archivesModel->DB_get_one('archives_box','box_id',array('box_num'=>$box_num));
            if(!$box){
                $this->assign('tips', '找不到档案编号为【'.$box_num.'】的信息');
                $this->assign('btn', '');
                $this->assign('url', '');
                $this->display('Pub/Notin/fail');
                exit;
            }
            $box_id = $box['box_id'];
            $boxInfo = $archivesModel->get_box_info($box_id);
            $filesInfo = $archivesModel->get_box_files($boxInfo['box_num']);
            $this->assign('boxInfo',$boxInfo);
            $this->assign('filesInfo',$filesInfo);
            $this->assign('empty','<span>暂无相关数据</span>');
            $this->display('showBox');
        }
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