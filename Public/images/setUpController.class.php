<?php
namespace sbsys\Controller;
use Think\Controller;
class setUpController extends Controller{
    public function add()   
    {
        if(IS_POST)
        {
        $model = D('sbsys/setUp');
            if($model->create(I('post.'), 1))
        {
            if($id = $model->add())
            {
                $this->success('添加成功！', U('department_add?p='.I('get.p')));
                exit;
            }
            
        }
        $this->error($model->getError());
    }
    //发送科室编号到视图
    $departnumModel=D('departnum');
    $departnum=$departnumModel->select();
    $this->assign("departnum",$departnum);
    //发送科室管理到视图
    $departresponModel=D('departnum');
    $departrespon=$departresponModel->select();
    $this->assign("departrespon",$departrespon);
    //发送设备管理到视图
    $assetsresponModel=D('departnum');
    $assetsrespon=$assetsresponModel->select();
    $this->assign("assetsrespon",$assetsrespon);
    //发送电话到视图
    $departtelModel=D('departnum');
    $departtel=$departtelModel->select();
    $this->assign("departtel",$departtel);
    //发送设备数量到视图
    $assetssumModel=D('departnum');
    $assetssum=$assetssumModel->select();
    $this->assign("assetssum",$assetssum);
    //发送设备金额到视图
    $assetspriceModel=D('departnum');
    $assetsprice=$assetspriceModel->select();
    $this->assign("assetsprice",$assetsprice);
}
   
   public function delete()
{
    $model = D('/Admin');
    if($model->delete(I('get.departid', 0)) !== FALSE)
    {
        $this->success('删除成功！', U('department', array('p' => I('get.p', 1))));
        exit;
    }
    else
    {
        $this->error($model->getError());
    }
}
public function newfile(){
     
    $Data = M('department'); // 实例化Data数据模型

    $this->department = $Data->select();

    $this->display();
}
}