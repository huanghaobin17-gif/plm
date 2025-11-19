<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/6/1
 * Time: 14:52
 */

namespace Admin\Controller\BaseSetting;

use Admin\Model\AssetsInfoModel;
use Admin\Model\CategoryModel;
use Admin\Model\IntegratedSettingModel;
use Admin\Model\MenuModel;
use Admin\Model\DepartmentModel;
use Admin\Model\BaseSettingModel;
use Admin\Model\UserModel;
use think\Controller;

class CacheController extends Controller
{
    public function index()
    {
        if (IS_POST) {
            $path = APP_PATH.'/Common/test.txt';
            if (!file_exists($path)) {
                //以读写方式打写指定文件，如果文件不存则创建
                if( ($TxtRes=fopen ($path,"w+")) === FALSE){
                    fclose ($TxtRes); //关闭指针
                    $this->ajaxReturn(array('status'=>-1,'msg'=>'更新缓存失败，请检查相关文件权限'));
                }
                $StrConents = "Test whether it is writable";//要 写进文件的内容
                if(!fwrite ($TxtRes,$StrConents)){ //将信息写入文件
                    fclose($TxtRes);
                    $this->ajaxReturn(array('status'=>-1,'msg'=>'更新缓存失败，请检查相关文件权限'));
                }
            }else{
                $TxtRes = fopen($path, "w+");
                $StrConents = "Test whether it is writable";//要 写进文件的内容
                if(!fwrite ($TxtRes,$StrConents)){ //将信息写入文件
                    fclose($TxtRes);
                    $this->ajaxReturn(array('status'=>-1,'msg'=>'更新缓存失败，请检查相关文件权限'));
                }
            }
            //缓存menu
            $menuModel = new MenuModel();
            $categoryModel = new IntegratedSettingModel();
            $menuarr = $menuModel->DB_get_all('menu', '', array('status'=>1), '', '', '');
            $menuData = $modules = array();
            $menuarr = getLeftMenu($menuarr,0);
            //组织menu数据
            foreach ($menuarr as $k=>$v){
                foreach ($v['list'] as $k1=>$v1){
//                    var_dump($v1);
//                    $i = 0;
                    foreach ($v1['list'] as $k2=>$v2){

                        $modules[$v['name']][$v1['name']][$k2]['menuid'] = $v2['menuid'];
                        $modules[$v['name']][$v1['name']][$k2]['name'] = $v2['name'];
                        $modules[$v['name']][$v1['name']][$k2]['title'] = $v2['title'];
//                        var_dump($v1['name']);
//                        $i++;
                    }
                }
            }

//            var_dump($modules);
            $menuData['menu'] = ArrayToString($modules, true);

            made_web_array(APP_PATH . '/Common/cache/menu.cache.php', $menuData);
            //缓存分类
            $categoryModel->updateCategory();
            //缓存部门
            $categoryModel->updateDepartment();
            //缓存模块设置
            $baseModel = new BaseSettingModel();
            $basearr = $baseModel->DB_get_all('base_setting','', '', '', '','');
            $baseData = array();
            foreach ($basearr as $k => $v) {
                $baseData[$v['module']][$v['set_item']]['value'] = json_decode($v['value'], true);
            }
            $bedata['baseSetting'] = ArrayToString($baseData);
            made_web_array(APP_PATH . '/Common/cache/basesetting.cache.php', $bedata);
            $userModel = new UserModel();
            $data = $userModel->DB_get_all('user','userid,telephone',['_string'=>'LENGTH(telephone)<12']);
            Vendor('SM4.SM4');
            $SM4 = new \SM4();
            $sql = '';
            foreach ($data as &$value) {
                $sql = $sql."UPDATE sb_user SET telephone = '".$SM4->encrypt($value['telephone'])."' WHERE userid = ".$value['userid'].";";
            }
            if ($sql != '') {
                $userModel->execute($sql);
            }
            //更新部门表和分类表中设备数量、总价等信息
            $this->updateAssetsNumAndTotalPrice();
            header('Content-Type: application/json; charset=utf-8');
            $this->ajaxReturn(array('status' => 1, 'msg' => '更新缓存成功！'));
            exit;
        }
    }

    //公共更新统计部门和分类方法
    private function updateAssetsNumAndTotalPrice()
    {
        $asModel = new AssetsInfoModel();
        $deModel = new DepartmentModel();
        $cateModel = new CategoryModel();

        //排除 已外调 已报废的设备
        $where['is_delete'] = C('NO_STATUS');
        $where['status'][0] = 'NOT IN';
        $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');

        $departCount = $asModel->DB_get_all('assets_info','departid,sum(buy_price) as totalPrice,count(departid) as assetsNum', $where, 'departid', '');
        $exist_departids = [];
        foreach ($departCount as $k => $v) {
            $exist_departids[] = $v['departid'];
            $departData['assetssum'] = $v['assetsNum'];
            $departData['assetsprice'] = $v['totalPrice'];
            $departWhere['departid'] = $v['departid'];
            $deModel->updateData('department',$departData, $departWhere);
        }
        $deModel->updateData('department',[
            'assetssum'=>0,
            'assetsprice'=>0
        ],['departid'=>array('NOT IN',$exist_departids)]);
        //更新分类表中每个分类的设备数量和总价
        $cateCount = $asModel->DB_get_all('assets_info','catid,sum(buy_price) as totalPrice,count(catid) as assetsNum',$where, 'catid', '');
        foreach ($cateCount as $k => $v) {
            $cateData['assetssum'] = $v['assetsNum'];
            $cateData['assetsprice'] = $v['totalPrice'];
            $cateWhere['catid'] = $v['catid'];
            $cateModel->updateData('category',$cateData, $cateWhere);
        }
        //获取父分类信息
        $cateParent['ids'] = '';
        if($cateParent['ids']){
            $parentcount = $asModel->DB_get_all('category','parentid,group_concat(catid order by catid asc) as ids,count(catid) as childNum,sum(assetssum) as assetsNum,sum(assetsprice) as totalPrice', array('parentid' => array('IN', $cateParent['ids'])), 'parentid', '');
            foreach ($parentcount as $k => $v) {
                $parentData['assetssum'] = $v['assetsNum'];
                $parentData['assetsprice'] = $v['totalPrice'];
                $parentData['child'] = $v['childNum'];
                $parentData['arrchildid'] = json_encode(explode(',', $v['ids']));
                $parentWhere['catid'] = $v['parentid'];
                $cateModel->updateData('category',$parentData, $parentWhere);
            }
        }
        $cateParent = $asModel->DB_get_one('category','group_concat(catid) as ids', array('parentid' => 0));
        if($cateParent['ids']){
            $parentcount = $asModel->DB_get_all('category','parentid,group_concat(catid order by catid asc) as ids,count(catid) as childNum,sum(assetssum) as assetsNum,sum(assetsprice) as totalPrice', array('parentid' => array('IN', $cateParent['ids'])), 'parentid', '');
            foreach ($parentcount as $k => $v) {
                $parentData['assetssum'] = $v['assetsNum'];
                $parentData['assetsprice'] = $v['totalPrice'];
                $parentData['child'] = $v['childNum'];
                $parentData['arrchildid'] = json_encode(explode(',', $v['ids']));
                $parentWhere['catid'] = $v['parentid'];
                $cateModel->updateData('category',$parentData, $parentWhere);
            }
        }
    }
}