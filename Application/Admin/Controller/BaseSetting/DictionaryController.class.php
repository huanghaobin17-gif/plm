<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2018/8/28
 * Time: 10:50
 */

namespace Admin\Controller\BaseSetting;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\DictionaryModel;
use Admin\Model\PeopleModel;
use Admin\Model\OfflineSuppliersModel;
use Admin\Model\UserModel;

class DictionaryController extends CheckLoginController
{
    private $MODULE = 'BaseSetting';

    /**
     * Notes: 设备字典
     */
    public function assetsDic()
    {
        $dicModel = new DictionaryModel();
        if(IS_POST){
            $res = $dicModel->getAllAssetsDic();
            $this->ajaxReturn($res);
        }else{
            $this->assign('assetsDic',get_url());
            $this->display();
        }
    }

    public function testDic()
    {
        
        $dicModel = new PeopleModel();

        if(IS_POST){
            //echo 123;die;
            $res = $dicModel->getAllDic();
            $this->ajaxReturn($res);
        }else{
            $this->assign('testDic',get_url());
            $this->display();
        }
    }

    /**
     * Notes: 新增设备字典
     */
    public function addAssetsDic()
    {
        $dicModel = new DictionaryModel();
        if(IS_POST){
            $res = $dicModel->addDic();
            $this->ajaxReturn($res);
        }else{
            $this->assign('hospital_id',session('job_hospitalid'));
            $this->display();
        }
    }
    public function addTestDic()
    {

        $dicModel = new PeopleModel();
        if(IS_POST){
            $res = $dicModel->addDic();
            $this->ajaxReturn($res);
        }else{

            $this->display();
        }
    }

    /**
     * Notes: 修改测试字典
     */
    public function editTestDic()
    {   
   
        $dicModel = new PeopleModel();
        if(IS_POST){
            $res = $dicModel->editDic();
            $this->ajaxReturn($res);
        }else{
            $id = I('get.id');
            $dicModel = new PeopleModel();
            //查当前信息所对应的基本信息
            $dicInfo = $dicModel->DB_get_one('people', '', array('id' => $id), '');
            if (!$dicInfo) {
                $this->error('该字典不存在！');
            }
      
            $this->assign('dicInfo',$dicInfo);
            $this->assign('like',explode(',',$dicInfo['like']));
            $this->display();
        }
    }

    /**
     * Notes: 修改设备字典
     */
    public function editAssetsDic()
    {
        $dicModel = new DictionaryModel();
        if(IS_POST){
            $res = $dicModel->editDic();
            $this->ajaxReturn($res);
        }else{
            $dic_assid = I('get.id');
            $dicModel = new DictionaryModel();
            //查当前信息所对应的基本信息
            $dicInfo = $dicModel->DB_get_one('dic_assets', '', array('dic_assid' => $dic_assid), '');
            if (!$dicInfo) {
                $this->error('该设备字典不存在！');
            }
            //查设备分类
            $category = $dicModel->DB_get_all('category','catid,catenum,category,parentid',array('status'=>1,'is_delete'=>0,'hospital_id'=>$dicInfo['hospital_id']),'','catid asc','');
            $category = getTree('parentid','catid',$category,0,0,' ➣ ');
            $this->assign('category',$category);
            $this->assign('dicInfo',$dicInfo);
            $this->assign('assets_category',explode(',',$dicInfo['assets_category']));
            $this->display();
        }
    }

    /**
     * Notes: 删除设备字典
     */
    public function delAssetsDic()
    {
        if(IS_POST){
            $dicModel = new DictionaryModel();
            $dic_assid = I('post.id');
            //查当前信息所对应的基本信息
            $dicInfo = $dicModel->DB_get_one('dic_assets', '', array('dic_assid' => $dic_assid), '');
            if (!$dicInfo) {
                $this->error('该设备字典不存在！');
            }
            $data['status']=C('SHUT_STATUS');
            $res=$dicModel->deleteData('dic_assets',array('dic_assid'=>$dic_assid));
//            $res = $dicModel->deleteData('dic_assets',array('dic_assid'=>$dic_assid));
            if($res){
                $this->ajaxReturn(array('status'=>1,'msg'=>'删除设备字典成功！'));
            }else{
                $this->ajaxReturn(array('status'=>-1,'msg'=>'删除设备字典失败！'));
            }
        }
    }

    /**
     * Notes: 删除测试字典
     */
    public function delTestDic()
    {

        if(IS_POST){
            $dicModel = new PeopleModel();
            $id = I('post.id');
            //查当前信息所对应的基本信息
            $dicInfo = $dicModel->DB_get_one('people', '', array('id' => $id), '');
            if (!$dicInfo) {
                $this->error('该字典不存在！');
            }
            //$data['status']=C('SHUT_STATUS');
            $res=$dicModel->deleteData('people',array('id'=>$id));
            if($res){
                $this->ajaxReturn(array('status'=>1,'msg'=>'删除字典成功！'));
            }else{
                $this->ajaxReturn(array('status'=>-1,'msg'=>'删除字典失败！'));
            }
        }
    }

    /**
     * Notes: 品牌字典
     */
    public function brandDic()
    {
        $dicModel = new DictionaryModel();
        if(IS_POST){
            $res = $dicModel->getAllBrandDic();
            $this->ajaxReturn($res);
        }else{
            $this->assign('brandDic',get_url());
            $this->display();
        }
    }

    /**
     * Notes: 新增品牌字典
     */
    public function addBrandDic()
    {
        $dicModel = new DictionaryModel();
        if(IS_POST){
            $res = $dicModel->addBrandDic();
            $this->ajaxReturn($res);
        }else{
            $otherPage = I('get.otherPage');
            if ($otherPage) {
                $this->assign('otherPage', $otherPage);
            }
            $this->display();
        }
    }

    /**
     * Notes: 修改品牌字典
     */
    public function editBrandDic()
    {
        $dicModel = new DictionaryModel();
        if(IS_POST){
            $res = $dicModel->editBrandDic();
            $this->ajaxReturn($res);
        }else{
            $brand_id = I('get.id');
            //查当前信息所对应的基本信息
            $dicInfo = $dicModel->DB_get_one('dic_brand', '', array('brand_id' => $brand_id), '');
            if (!$dicInfo) {
                $this->error('该品牌字典不存在！');
            }
            $this->assign('dicInfo',$dicInfo);
            $this->display();
        }
    }

    /**
     * Notes: 删除品牌字典
     */
    public function delBrandDic()
    {
        if(IS_POST){
            $dicModel = new DictionaryModel();
            $brand_id = I('post.id');
            //查当前信息所对应的基本信息
            $dicInfo = $dicModel->DB_get_one('dic_brand', '', array('brand_id' => $brand_id), '');
            if (!$dicInfo) {
                $this->error('该品牌字典不存在！');
            }
            $data['is_delete'] = 1;
            $res=$dicModel->deleteData('dic_brand',array('brand_id'=>$brand_id));
            if($res){
                $this->ajaxReturn(array('status'=>1,'msg'=>'删除品牌字典成功！'));
            }else{
                $this->ajaxReturn(array('status'=>-1,'msg'=>'删除品牌字典失败！'));
            }
        }
    }

    //配件字典列表
    public function partsDic(){
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取合同列表数据
                    $dicModel = new DictionaryModel();
                    $result = $dicModel->partsDic();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $this->assign('partsDicUrl',get_url());
            $this->display();
        }
    }

    //新增配件字典
    public function addPartsDic(){
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'getCity':
                    //获取城市
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getCity();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getAreas':
                    //获取区/城镇
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getAreas();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addSuppliers':
                    //补充厂家
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->addOfflineSupplier();
                    if ($result['status'] == C('SUCCESS_STATUS')) {
                        $result['result']['sup_num'] = $OfflineSuppliersModel->getSupNum();
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //新增操作
                    $dicModel = new DictionaryModel();
                    $result = $dicModel->addPartsDic();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $provinces = $OfflineSuppliersModel->getProvinces();
            $sup_num = $OfflineSuppliersModel->getSupNum();
            $where['is_delete'] = ['EQ', C('NO_STATUS')];
            $where['is_manufacturer'] = C('YES_STATUS');
            $supplierList = $OfflineSuppliersModel->DB_get_all('offline_suppliers','olsid,sup_name',$where);
            $this->assign('provinces', $provinces);
            $this->assign('sup_num', $sup_num);
            $this->assign('supplierList', $supplierList);
            $this->assign('addPartsDicUrl', get_url());
            $this->display();
        }
    }

    //编辑配件字典
    public function editPartsDic()
    {
        if(IS_POST){
            $action=I('POST.action');
            switch ($action){
                case 'getCity':
                    //获取城市
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getCity();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getAreas':
                    //获取区/城镇
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getAreas();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addSuppliers':
                    //补充厂家
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->addOfflineSupplier();
                    if ($result['status'] == C('SUCCESS_STATUS')) {
                        $result['result']['sup_num'] = $OfflineSuppliersModel->getSupNum();
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $dicModel = new DictionaryModel();
                    $result = $dicModel->editPartsDic();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        }else{
            $dic_partsid = I('GET.dic_partsid');
            $where['dic_partsid']=['EQ',$dic_partsid];
            $where['status']=['NEQ',C('DELETE_STATUS')];
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $parts = $OfflineSuppliersModel->DB_get_one('dic_parts', '', $where);
            if (!$parts) {
                $this->error('该配件字典不存在！');
            }
            $provinces = $OfflineSuppliersModel->getProvinces();
            $sup_num = $OfflineSuppliersModel->getSupNum();
            $where['status'] = ['NEQ', C('DELETE_STATUS')];
            $where['is_manufacturer'] = C('YES_STATUS');
            $supplierList = $OfflineSuppliersModel->DB_get_all('offline_suppliers','olsid,sup_name',$where);
            $this->assign('sup_num', $sup_num);
            $this->assign('parts',$parts);
            $this->assign('provinces', $provinces);
            $this->assign('supplierList', $supplierList);
            $this->assign('editPartsDicUrl',get_url());
            $this->display();
        }
    }

    //删除配件字典
    public function delPartsDic()
    {
        if(IS_POST){
            $dicModel = new DictionaryModel();
            $result=$dicModel->delPartsDic();
            $this->ajaxReturn($result, 'json');
        }
    }

    /*
     * 批量添加分类
     */
    public function batchAddAssetsDic()
    {
        $dicModel = new DictionaryModel();
        if (IS_POST) {
            $type = I('POST.type');
            switch($type){
                case 'save':
                    $result = $dicModel->batchAddDic();
                    if($result['status'] == 1){
                        //日志行为记录文字
                        $text = getLogText('batchAddCategoryLogText');
                        $dicModel->addLog('category','',$text,'','');
                    }
                    $this->ajaxReturn($result);
                    break;
                case 'getData':
                    $result = $dicModel->getTmpDic();
                    $this->ajaxReturn($result);
                    break;
                case 'updateData':
                    //更新临时表数据库
                    $result = $dicModel->updateTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'delTmpData':
                    //删除临时表数据库
                    $result = $dicModel->delTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'upload':
                    //接收上传文件数据
                    $result = $dicModel->uploadData();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $this->ajaxReturn(array('status'=>-1,'msg'=>'空操作！'));
                    break;
            }
        } else {
            $type = I('GET.type');
            if ($type == 'exploreCatesModel') {
                //导出模板
                $xlsName = "设备字典";
                $xlsCell = array('设备名称', '设备分类','字典类别','单位','设备类型(多个请用“|”分隔：急救设备|特种设备|计量设备|质控设备|效益分析|生命支持)');
                //单元格宽度设置
                $width = array(
                    '设备名称' => '20',//字符数长度
                    '设备分类' => '20',//字符数长度
                    '字典类别' => '20',//字符数长度
                    '单位' => '20',//字符数长度
                    '设备类型(多个请用“|”分隔：急救设备|特种设备|计量设备|质控设备|效益分析|生命支持)' => '90',//字符数长度
                );
                //单元格颜色设置（例如必填行单元格字体颜色为红色）
                $color = array(
                    '设备名称' => 'FF0000',//颜色代码
                    '设备分类' => 'FF0000',//颜色代码
                );
                Excel('设备字典批量导入模板', $xlsName, $xlsCell, $width, $color);
            }
            $hospital_id = session('current_hospitalid');
            //查设备分类
            $category = $dicModel->DB_get_all('category', 'catid,parentid,catenum,category', array('is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id), '', 'catid asc', '');
            $category = getTree('parentid', 'catid', $category, 0, 0, '----');
            //查询数据库已有设备字典信息
            $assets = $dicModel->DB_get_all('dic_assets', 'assets', array('hospital_id' => session('current_hospitalid')), '', '');
            $this->assign('assets',json_encode($assets,JSON_UNESCAPED_UNICODE));
            $this->assign('batchAddAssetsDic',get_url());
            $this->assign('category', $category);
            $this->display();
        }
    }

}