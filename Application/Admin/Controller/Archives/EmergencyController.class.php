<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/8/15
 * Time: 11:57
 */
namespace Admin\Controller\Archives;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\ArchivesModel;

class EmergencyController extends CheckLoginController
{
    private $MODULE = 'Archives';
    private $Controller = 'Emergency';

    /*
     * 应急预案列表
     */
    public function emergencyPlanList()
    {
        $archivesModel = new ArchivesModel();
        if(IS_POST){
            $result = $archivesModel->get_emergency_lists();
            $this->ajaxReturn($result);
        }else{
            $action = I('get.action');
            if($action == 'showEmer'){
                $arempid = I('get.arempid');
                $emerInfo = $archivesModel->get_emergency_info($arempid);
                $files = $archivesModel->get_emergency_files($arempid);
                $this->assign('emerInfo',$emerInfo);
                $this->assign('files',$files);
                $this->display('showEmer');
            }else{
                //查询分类名称
                $users = $archivesModel->DB_get_all('user','userid,username',array('status'=>1,'is_delete'=>0,'hospital_id'=>session('current_hospitalid')));
                $cates = $archivesModel->DB_get_all('archives_emergency_category','*',array('1'));
                $this->assign('cates',$cates);
                $this->assign('users',$users);
                $this->assign('emergencyPlanList', get_url());
                $this->display();
            }
        }
    }

    /**
     * Notes: 新增应急预案
     */
    public function addEmergency()
    {
        $archivesModel = new ArchivesModel();
        if(IS_POST){
            $action = I('post.action');
            switch ($action){
                case 'uploadFile':
                    //上传设备图片
                    $Tool = new ToolController();
                    //设置文件类型
                    $type = array('pdf','doc','docx','txt');
                    //维修文件名目录设置
                    $dirName = C('UPLOAD_DIR_ARCHIVES_EMERGENCY_NAME');
                    //上传文件
                    $upload = $Tool->upFile($type, $dirName);
                    $this->ajaxReturn($upload);
                    break;
                case 'addEmerCate':
                    $result = $archivesModel->add_category();
                    $this->ajaxReturn($result);
                    break;
                case 'getEmerCate':
                    $data = $archivesModel->DB_get_all('archives_emergency_category','id,name',array('1'));
                    $this->ajaxReturn(array('count'=>count($data),'data'=>$data));
                    break;
                case 'getcategory':
                    $result = $archivesModel->getcategory();
                    $this->ajaxReturn($result);
                    break;
                case 'savecategory':
                    $result = $archivesModel->savecategory();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $result = $archivesModel->save_emer();
                    $this->ajaxReturn($result);
            }

        }else{
            $action = I('get.action');
            $this->assign('addEmergency',get_url());
            if($action == 'addEmerCate'){
                $this->display('addEmerCate');
            }else{
                $cates = $archivesModel->DB_get_all('archives_emergency_category','*');
                $userInfo = [];
                $userInfo['username'] = session('username');
                $userInfo['userid'] = session('userid');
                $this->assign('now',date('Y-m-d'));
                $this->assign('userInfo',$userInfo);
                $this->assign('cates',$cates);
                $this->display();
            }
        }
    }

    /**
     * Notes: 修改应急预案
     */
    public function editEmergency()
    {
        $archivesModel = new ArchivesModel();
        if(IS_POST){
            $action = I('post.action');
            switch ($action){
                case 'uploadFile':
                    //上传设备图片
                    $Tool = new ToolController();
                    //设置文件类型
                    $type = array('pdf','doc','docx','txt');
                    //维修文件名目录设置
                    $dirName = C('UPLOAD_DIR_ARCHIVES_EMERGENCY_NAME');
                    //上传文件
                    $upload = $Tool->upFile($type, $dirName);
                    $this->ajaxReturn($upload);
                    break;
                default:
            $result = $archivesModel->edit_emer();
            $this->ajaxReturn($result);
        }
        }else{
            $arempid = I('get.arempid');
            $emerInfo = $archivesModel->get_emergency_info($arempid);
            $files = $archivesModel->get_emergency_files($arempid);
            $cates = $archivesModel->DB_get_all('archives_emergency_category','*',array('1'));
            foreach ($cates as $k=>$v){
                if($emerInfo['category'] == $v['id']){
                    $cates[$k]['selected'] = 'selected';
                }
            }
            $userInfo = [];
            $userInfo['username'] = session('username');
            $userInfo['userid'] = session('userid');
            $this->assign('addEmergency',get_url());
            $this->assign('emerInfo',$emerInfo);
            $this->assign('files',$files);
            $this->assign('emerInfo',$emerInfo);
            $this->assign('files',$files);
            $this->assign('cates',$cates);
            $this->display();
        }
    }

    /**
     * Notes: 删除应急预案
     */
    public function delEmergency()
    {
        $archivesModel = new ArchivesModel();
        $id = I('post.arempid');
        $info = $archivesModel->DB_get_one('archives_emergency_plan','arempid,is_delete',array('arempid'=>$id));
        if(!$info){
            $this->ajaxReturn(array('status'=>-1,'msg'=>'查找不到该预案信息！'));
        }
        if($info['is_delete'] == 1){
            $this->ajaxReturn(array('status'=>-1,'msg'=>'该预案已删除，请勿重复操作！'));
        }
        $res = $archivesModel->updateData('archives_emergency_plan',array('is_delete'=>1),array('arempid'=>$id));
        if($res){
            $this->ajaxReturn(array('status'=>1,'msg'=>'删除预案成功！'));
        }else{
            $this->ajaxReturn(array('status'=>-1,'msg'=>'删除预案失败！'));
        }
    }
}