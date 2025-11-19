<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2020/3/13
 * Time: 15:38
 */

namespace Vue\Controller\Archives;

use Vue\Controller\Login\IndexController;

class BoxController extends IndexController
{
    public function boxList()
    {
        $action = I('get.action');
        $archivesModel = new \Admin\Model\ArchivesModel();
        if($action == 'show_box'){
            if (I('get.box_id')) {
                $box_id = I('get.box_id');
            }else{
            $box_num = I('get.box_num');
            //查询boxID
            $box = $archivesModel->DB_get_one('archives_box','box_id',array('box_num'=>$box_num));
            if(!$box){
                $result['status'] = 302;
                $msg['tips'] = '找不到档案编号为【'.$box_num.'】的信息';
                $msg['url'] = '';
                $msg['btn'] = '';
                $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result);
                exit;
            }
            $box_id = $box['box_id'];
           }
            $boxInfo = $archivesModel->get_box_info($box_id);
            $filesInfo = $archivesModel->get_box_files($boxInfo['box_num']);
            $result['status'] = 1;
            $result['boxInfo'] = $boxInfo;
            $result['filesInfo'] = $filesInfo;
            $this->ajaxReturn($result);
        }else{
            $result = $archivesModel->get_m_box_lists();
            $this->ajaxReturn($result);
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