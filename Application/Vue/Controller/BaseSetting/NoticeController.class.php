<?php

namespace Vue\Controller\BaseSetting;

use Vue\Controller\Login\IndexController;
use Vue\Model\BaseSettingModel;

class NoticeController extends IndexController
{

    /**
     * 系统公告列表
     */
    public function getNoticeList()
    {
        $BasettingModel = new BaseSettingModel();
        $action = I('get.action');
        switch ($action){
            case 'showNotice':
                /**
                 * 显示公告详情
                 */
                $data = $BasettingModel->get_notice();
                if ($data['send_user_id'] != ''){
                    $judgeArr = explode(',',  $data['send_user_id']);
                    $userid = session('userid');
                    if (in_array($userid,$judgeArr) || session('isSuper') == 1){
                        $file = $BasettingModel->get_file();
                        $this->assign('file', $file);
                    }
                }else{
                    $file = $BasettingModel->get_file();
                    $this->assign('file', $file);
                }
                $result['file'] = $file;
                $result['status'] = 1;
                $result['data'] = $data;
                $this->assign('data', $data);
                $this->ajaxReturn($result,'json');
                break;
            default:
                $result = $BasettingModel->get_notice_list();
                $this->ajaxReturn($result,'json');
                break;
        }
    }
    /*
    预览功能
     */
    public function showFile()
    {
        $path = I('get.path') ? I('get.path') : '';
        if($path){
            $result['status'] = 302;
            $msg['tips'] = '文件地址错误';
            $msg['url'] = '';
            $msg['btn'] = '';
            $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
        }
        $name = I('get.name');
        $this->assign('pdfsrc',$path);
        $this->assign('name',$name);
        $this->display();
    }
}
