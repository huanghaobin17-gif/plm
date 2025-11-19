<?php

/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/5/10
 * Time: 14:50
 */
namespace Admin\Controller\Adverse;
use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\AdverseModel;
use Admin\Model\UserModel;
class AdverseController extends CheckLoginController
{

    /**
     *器械不良事件报告列表
     */
    public function getAdverseList()
    {
        switch(I('get.type')){
            case '';
                if(IS_POST){
                    $adverseModel = new AdverseModel();
                    $result = $adverseModel->getAdverseData();
                    $this->ajaxReturn($result, 'json');
                }else{
                    $userModel = new UserModel();
                    $hospital_id = session('current_hospitalid');
                    $departments = $userModel->getAllDepartments(array('hospital_id' => $hospital_id,'is_delete'=>C('NO_STATUS')));
                    $this->assign('department', $departments);
                    $this->assign('getAdverseList',get_url());
                    $this->display();
                }
                break;
            case 'showAdverse';
                $adverseModel = new AdverseModel();
                $id = I('get.id');
                $adverseInfo = $adverseModel->showAdverseData($id);
                $this->assign('info',$adverseInfo);
                $this->assign('id',$id);
                $this->display('showAdverse');
                break;
            case 'showUpload';
                $adverseModel = new AdverseModel();
                $id =I('get.id');
                $res = $adverseModel->DB_get_one('adverse_info', 'report', array('id' => $id));
                $fileexists = file_exists('.'.$res['report']);
                $this->assign('file_exists',$fileexists);
                $typearr = explode('/',$res['report']);
                $filename = $typearr[count($typearr)-1];
                $img_type = explode('.',$filename);
                $type = $img_type[1];
                $this->assign('type',$type);
                $this->assign('src',$res['report']);
                $this->assign('title',$filename);
                $this->display('./Public/showFile');
                break;
            case 'downFile';
                $path = I('GET.path');//获取文件的路径
                $title = I('GET.name');//
                if ($path && $title) {
                    Header("Content-type:  application/octet-stream ");
                    Header("Accept-Ranges:  bytes ");
                    Header("Accept-Length: " . filesize($path));
                    header("Content-Disposition:  attachment;  filename= $title");//生成的文件名(带后缀的)
                    echo file_get_contents('http://' . C('HTTP_HOST') . $path );//用绝对路径
                    readfile($path);
                }
                break;
        }
    }

    /**
     * 新增
     */
    public function addAdverse()
    {
        switch(I('get.type')){
            case '';
                $adverseModel = new AdverseModel();
                if(IS_POST){
                    $result = $adverseModel->addAdverse();
                    $this->ajaxReturn($result, 'json');
                }else{
                    $userid = session('userid');
                    $uInfo = $adverseModel->DB_get_one('user', 'username,autograph', array('userid' => $userid));
                    if ($uInfo['autograph']=="") {
                        $uInfo['display']='1';
                    }else{
                        $uInfo['display']='2';
                    }
                    $this->assign('uInfo',$uInfo);
                    $this->assign('now_date',date('Y-m-d'));
                    $this->display();
                }
                break;
            case 'uploadFile';
                $Tool = new ToolController();
                //设置文件类型
                $type = array('jpg','pdf','png','bmp','jpeg','gif','doc','docx');
                //文件名目录设置
                $dirName = C('UPLOAD_DIR_ADVERSE_NAME');
                //上传文件
                $upload = $Tool->upFile($type,$dirName);
                if ($upload['status']==C('YES_STATUS')) {
                    // 上传成功 获取上传文件信息
                    $this->ajaxReturn(array('status' => 1, 'path' => $upload['src'],'name'=>$upload['formerly']));
                } else {
                    // 上传错误提示错误信息
                    $this->ajaxReturn(array('status' => -1,'msg'=>$upload['msg']));
                }
                break;
            case 'getAllAssetsSearch';
                $adverseModel = new AdverseModel();
                $arr = $adverseModel->getAssetsData();
                $this->ajaxReturn($arr, 'JSON');
                break;
        }

    }

    /**
     * 编辑
     */
    public function editAdverse()
    {
        switch(I('get.type')){
            case '';
                if(IS_POST){
                    $adverseModel = new AdverseModel();
                    $result = $adverseModel->editAdverse();
                    $this->ajaxReturn($result, 'json');
                }else{
                    $adverseModel = new AdverseModel();
                    $id = I('get.id');
                    $adverseInfo = $adverseModel->showAdverseData($id);
                    $this->assign('now_date',date('Y-m-d'));
                    $this->assign('info',$adverseInfo);
                    $this->assign('id',$id);
                    $this->display();
                }
                break;
            case 'uploadFile';
                $Tool = new ToolController();
                //设置文件类型
                $type = array('jpg','pdf','png','bmp','jpeg','gif','doc','docx');
                //文件名目录设置
                $dirName = C('UPLOAD_DIR_ADVERSE_NAME');
                //上传文件
                $upload = $Tool->upFile($type,$dirName);
                if ($upload['status']==C('YES_STATUS')) {
                    // 上传成功 获取上传文件信息
                    $this->ajaxReturn(array('status' => 1, 'path' => $upload['src'],'name'=>$upload['formerly']));
                } else {
                    // 上传错误提示错误信息
                    $this->ajaxReturn(array('status' => -1,'msg'=>$upload['msg']));
                }
                break;
            case 'getAllAssetsSearch';
                $adverseModel = new AdverseModel();
                $arr = $adverseModel->getAssetsData();
                $this->ajaxReturn($arr, 'JSON');
                break;
        }
    }
}