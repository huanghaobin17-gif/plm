<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/17
 * Time: 10:38
 */

namespace Admin\Controller\BaseSetting;
use Admin\Controller\Login\CheckLoginController;
use Admin\Model\MenuModel;

class MenuController extends CheckLoginController
{
    private $MODULE = 'BaseSetting';

    /*
     * 或取用户列表getList
     */
    public function getMenuLists()
    {
      
        $menuModel = new MenuModel();
        if (IS_POST) {
            $action = I('POST.action');
            if($action == 'updateMenu'){
                $this->updateMenu();
            }elseif($action == 'getDetail'){
                $this->getDetail();
            }else{
                $result = $menuModel->getMenuLists1();
                $this ->ajaxReturn($result,'json');
            }
        }else{
            //查询第一个二级菜单
            $menuid = $menuModel->DB_get_one('menu','menuid',array('status'=>1,'parentid'=>array('neq',0)),'parentid,orderID asc');
            $this->assign('menuid',$menuid['menuid']);
            $this->assign('getMenuLists',get_url());
            $this->display();
        }
    }

    /*
     * 或取菜单明细
     */
    private function getDetail()
    {
        if (IS_POST) {
            //根据搜索条件获取用户列表
            $menuModel = new MenuModel();
            $result = $menuModel->getDetail();
            $this ->ajaxReturn($result,'json');
        }else{
            $this->display('getMenuLists');
        }
    }

    private function updateMenu()
    {
        $menuid = I('POST.menuid');
        $menuname = $_POST['title'];
        $orderID  = $_POST['orderID'];
        $status  = $_POST['status'];
        $data = array();
        if(isset($menuname)){
            $data['title'] = trim($menuname);
            if(!$data['title']){
                $this->ajaxReturn(array('status'=>-1,'msg'=>'菜单名称不能为空！'));
            }
        }
        if(isset($orderID)){
            $data['orderID'] = trim($orderID);
            if(!$data['orderID']){
                $this->ajaxReturn(array('status'=>-1,'msg'=>'排序不能为空！'));
            }
            if($data['orderID'] <= 0){
                $this->ajaxReturn(array('status'=>-1,'msg'=>'请输入大于0的排序号！'));
            }
        }
        if(isset($status)){
            $data['status'] = $status;
        }
        if(!$data){
            $this->ajaxReturn(array('status'=>-1,'msg'=>'没有可更新的数据！'));
        }
        $menuModel = new MenuModel();
        $menuModel->updateData('menu',$data,array('menuid'=>$menuid));
        $this->ajaxReturn(array('status'=>1,'msg'=>'更新成功！'));
    }
}