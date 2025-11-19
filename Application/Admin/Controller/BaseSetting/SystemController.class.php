<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/17
 * Time: 10:38
 */

namespace Admin\Controller\BaseSetting;

use Admin\Controller\Tasks\TasksController;
use Admin\Model\MenuModel;
use Admin\Model\UserModel;
use think\Controller;

class SystemController extends Controller
{
    private $MODULE = 'BaseSetting';

    public function themes()
    {
        $this->display();
    }

    public function about()
    {
        $this->display();
    }

    public function more()
    {
        $this->display();
    }

    public function get()
    {
        $this->display();
    }

    public function message()
    {
        $TasksController=new TasksController();
        $TasksController->getTask();
        $this->assign('task', session('taskResult'));
        $this->display();
    }

    public function updataTask(){
        $TasksController = new TasksController();
        $TasksController->getTask();
    }

    public function changeHospital()
    {
        $userModel = new UserModel();
        if(IS_POST){
            $hosid = I('post.hsid');
            //查询医院信息
            $hosInfo = $userModel->DB_get_one('hospital','*',array('hospital_id'=>$hosid,'is_delete'=>C('NO_STATUS')));
            if(!$hosInfo){
                $this->ajaxReturn(array('status'=>-1,'msg'=>'该医院不存在或已被删除'));
            }
            //查询该用户是否有该医院管理权限
            $userInfo = $userModel->DB_get_one('user','manager_hospitalid',array('userid'=>session('userid')));
            if(!in_array($hosid,explode(',',$userInfo['manager_hospitalid']))){
                $this->ajaxReturn(array('status'=>-1,'msg'=>'您没有该医院管理权限！'));
            }
            if(session('isSuper')){
                //超级管理员
                $depart = $userModel->DB_get_one('department','group_concat(departid) as departids',array('hospital_id'=>$hosid,'is_delete'=>0));
            }else{
                $join = "LEFT JOIN sb_department AS B ON A.departid = B.departid";
                $field = "group_concat(A.departid) departids";
                //查询该用户在该医院的可管理科室
                $depart = $userModel->DB_get_one_join('user_department','A',$field,$join,array('B.hospital_id'=>$hosid,'B.is_delete'=>0,'A.userid'=>session('userid')));
            }

            //获取对应分院权限
            if (session('isSuper') == C('YES_STATUS')) {
                //超级管理员
                $menuidarr = $userModel->DB_get_all('menu', '*', array('status' => 1));
            } else {
                //获取用户roleid和menu
                $fields = "group_concat(A.roleid) as roleids";
                $join = "LEFT JOIN sb_role AS B ON A.roleid = B.roleid";
                $roleidarr = $userModel->DB_get_one_join('user_role','A', $fields,$join, array('A.userid' => session('userid'),'B.hospital_id'=>$hosid));
                if (!$roleidarr['roleids']) {
                    //没有分配角色
                    $this->ajaxReturn(array('status' => -1, 'msg' => '您在该院暂未分配角色，请联系管理员分配！'));
                }
                $menuidarr = $userModel->DB_get_all('role_menu', 'menuid', array('roleid' => array('in', $roleidarr['roleids'])), 'menuid');
                if (!$menuidarr) {
                    //没有分配权限
                    $this->ajaxReturn(array('status' => -1, 'msg' => '您在该院的角色未分配权限，暂不允许登录系统，请联系管理员进行权限分配！'));
                }
                $menuidstr = [];
                foreach ($menuidarr as $k => $v) {
                    $menuidstr[] = $v['menuid'];
                }
                if ($menuidstr) {
                    $menuidarr = $userModel->DB_get_all('menu', '*', array('menuid' => array('in', $menuidstr), 'status' => 1), '', 'orderID asc');
                }
            }
            $leftShowMenu = $sessionmid = $leftMenu = [];
            foreach ($menuidarr as $k => $v) {
                $sessionmid[] = $v['menuid'];
                $leftShowMenu[] = $v;
            }
            if (session('isSuper') != C('YES_STATUS') && $leftShowMenu) {
                //不是超级管理员要补全menu
                $leftShowMenu = $this->getParentMenu($leftShowMenu);
            }
            session('leftShowMenu', $leftShowMenu);
            if($leftShowMenu){
                $menuModel = new MenuModel();
                $leftMenu = $menuModel->formatMenu($leftShowMenu);
            }
            session('leftMenu', $leftMenu);
            session('sessionmid', $sessionmid);

            if($depart['departids']){
                session('departid', $depart['departids']);
            }else{
                session('departid', '-1');
            }
            session('current_hospitalid',$hosid);
            session('current_hospitalname',$hosInfo['hospital_name']);
            $TasksController = new TasksController();
            $TasksController->getTask();
            $this->ajaxReturn(array('status'=>1,'msg'=>'切换医院成功！'));
        }else{
            $current_id = session('current_hospitalid');
            $userid = session('userid');
            //查询该用户可管理的所有医院
            $myHospitals = $userModel->get_all_user_can_manager_hospital($userid);
            $this->assign('current_id',$current_id);
            $this->assign('myHospitals',$myHospitals);
            $this->display();
        }
    }

    //补全menu
    public function getParentMenu($leftShowMenu)
    {
        $parentid = array();
        foreach ($leftShowMenu as &$one) {
            $parentid[] = $one['parentid'];
        }
        $parentid = array_unique($parentid);
        $menuModel = new MenuModel();
        //不是超级管理员 要补全menu
        $parentMenuidarr = $menuModel->DB_get_all('menu', '*', array('menuid' => array('in', $parentid), 'status' => 1), '', 'orderID asc');
        $newParentid = false;
        foreach ($parentMenuidarr as &$one) {
            if ($one['parentid'] != 0) {
                $newParentid = true;
                break;
            }
        }
        if ($newParentid) {
            $parentMenuidarr = $this->getParentMenu($parentMenuidarr);
        }

        return array_merge($parentMenuidarr, $leftShowMenu);
    }
}