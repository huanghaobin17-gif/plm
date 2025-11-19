<?php

namespace Mobile\Controller\BaseSetting;

use Mobile\Controller\Login\IndexController;
use Mobile\Model\BaseSettingModel;

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
                $this->assign('data', $data);
                $this->assign('url', get_url());
                $this->display('showNotice');
                break;
            default:
                $data = $BasettingModel->get_notice_list();
                $this->assign('data', $data);
                $this->assign('url', get_url());
                $this->display();
                break;
        }
    }
    /*
    预览功能
     */
    public function showFile()
    {
        $path = I('get.path') ? I('get.path') : '';
        if($path == ''){
            $this->assign('tips', '文件地址错误！');
            $this->assign('btn', '返回首页');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        $name = I('get.name');
        $this->assign('pdfsrc',$path);
        $this->assign('name',$name);
        $this->display();
    }
}
