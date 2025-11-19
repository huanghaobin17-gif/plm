<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/3/6
 * Time: 10:24
 */

namespace Mobile\Controller\Assets;

use Admin\Model\AdverseModel;
use Admin\Model\AssetsBorrowModel;
use Admin\Model\AssetsInsuranceModel;
use Admin\Model\OfflineSuppliersModel;
use Mobile\Model\WxAccessTokenModel;
use Think\Controller;
use Mobile\Controller\Login\IndexController;
use Mobile\Model\AssetsInfoModel;

class LookupController extends IndexController
{
    protected $assets_list = 'Lookup/getAssetsList';//设备列表地址

    //主设备列表
    public function getAssetsList()
    {
        $departids = session('departid');
        $asModel = new AssetsInfoModel();
        if (IS_POST) {
            $result = $asModel->get_assets_lists();
            $this->ajaxReturn($result, 'json');
        } else {
            //查询分类信息数据
            $cates = $asModel->get_all_category();
            $cates = getTree('parentid', 'catid', $cates, 0);
            //查询科室信息
            $departs = $asModel->get_all_department($departids,C('ASSETS_STATUS_USE') . ',' . C('ASSETS_STATUS_REPAIR') . ',' . C('ASSETS_STATUS_SCRAP') . ',' . C('ASSETS_STATUS_SCRAP_ON') . ',' . C('ASSETS_STATUS_TRANSFER_ON'));
            $departs = getTree('parentid', 'departid', $departs, 0);
            array_multisort(array_column($cates, 'assetssum'), SORT_DESC, $cates);
            $this->assign('cates', $cates);
            $this->assign('departs', $departs);
            $this->assign('assetsListUrl', get_url());
            $jssdk = new WxAccessTokenModel();
            $signPackage = $jssdk->GetSignPackage();
            $this->signPackage = $signPackage;
            $this->display();
        }
    }

    public function showAssets()
    {
        $asModel = new \Admin\Model\AssetsInfoModel();
        $assid = I('GET.assid');
        $assnum = I('GET.assnum');
        $departids = explode(',', session('departid'));
        if (!$assid) {
            $idarr = [];
            if ($assnum) {
                //查询设备assid
                $idarr = $asModel->DB_get_one('assets_info', 'assid', array('assnum' => $assnum, 'is_delete' => '0'));
                $assid = $idarr['assid'];
            }
            if (!$idarr) {
                $idarr = $asModel->DB_get_one('assets_info', 'assid', array('assorignum' => $assnum, 'is_delete' => '0'));
                $assid = $idarr['assid'];
            }
            if (!$idarr) {
                $idarr = $asModel->DB_get_one('assets_info', 'assid', array('assorignum_spare' => $assnum, 'is_delete' => '0'));
                $assid = $idarr['assid'];
            }
            if (!$idarr) {
                $this->assign('tips', '查找不到编码为 ' . $assnum . ' 的设备信息');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->assets_list);
                $this->display('Pub/Notin/fail');
                exit;
            }
        }
        //组织表单第一部分数据设备基础信息
        $assets = $asModel->getAssetsInfo($assid);
        $assnum = $assets['assnum'];
        if (!in_array($assets['departid'], $departids)) {
            $this->assign('tips', '编码为 ' . $assnum . ' 的设备不属于您管理的科室');
            $this->assign('btn', '返回列表页');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->assets_list);
            $this->display('Pub/Notin/fail');
            exit;
        }
        if ($assets['pic_url']) {
            $assets['pic_url'] = explode(',', $assets['pic_url']);
            foreach ($assets['pic_url'] as &$pic) {
                $pic = '/Public/uploads/assets/' . $pic;
            }
            $assets['imgCount'] = count($assets['pic_url']);
            $assets['pic_url'] = json_encode($assets['pic_url']);
        } else {
            $assets['pic_url'] = 0;
        }
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assets['buy_price'] = '***';
        }
        //var_dump($assets);exit;
        //组织表单第三部分厂商信息
        $OfflineSuppliersModel = new OfflineSuppliersModel();
        $offlineSuppliers = $asModel->DB_get_one('assets_factory', 'ols_facid,ols_supid,ols_repid', array('assid' => $assets['assid']));
        $factoryInfo = [];
        $supplierInfo = [];
        $repairInfo = [];
        $offlineSuppliersFields = 'olsid,sup_name,salesman_name,salesman_phone,artisan_name,artisan_phone';
        if ($offlineSuppliers['ols_facid']) {
            $factoryInfo = $asModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_facid']]);
            $factoryFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_facid']);
            $this->assign('factoryData', $factoryFile);
        }
        if ($offlineSuppliers['ols_supid']) {
            $supplierInfo = $asModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_supid']]);
            $supplierFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_supid']);
            $this->assign('supplierData', $supplierFile);
        }
        if ($offlineSuppliers['ols_repid']) {
            $repairInfo = $asModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_repid']]);
            $repairFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_repid']);
            $this->assign('repairData', $repairFile);
        }
        //生命历程
        $life = $asModel->getLifeInfo($assid);
        array_multisort(array_column($life, 'sort_time'), SORT_DESC, $life);
        $this->assign('assets', $assets);
        $this->assign('life', $life);
        $this->assign('len', count($life));
        $this->assign('factoryInfo', $factoryInfo);
        $this->assign('supplierInfo', $supplierInfo);
        $this->assign('repairInfo', $repairInfo);
        $this->assign('uppic_url', C('ADMIN_NAME').'/Public/uploadReport?id='.$assid.'&i=assid&t=assets_info&username='.session('username'));
        $this->display();
    }
}