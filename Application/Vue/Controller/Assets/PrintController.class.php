<?php

namespace Vue\Controller\Assets;

use Vue\Model\WxAccessTokenModel;
use Think\Controller;
use Vue\Controller\Login\IndexController;
use Vue\Model\AssetsInfoModel;
use Admin\Model\OfflineSuppliersModel;

class PrintController extends IndexController
{
    private $MODULE = 'Assets';
    protected $assets_list = 'Print/verify';//标签核实列表地址

    /**
     * 标签核实（列表）
     */
    public function verify()
    {
        $departids = session('departid');
        $asModel = new AssetsInfoModel();
        if (IS_POST) {
                $this->labelCheck();
        } else {
            $action = I('get.action');
            if($action == 'labelCheck'){
                $this->labelCheck();
            }else{
                $result = $asModel->get_verify_lists();
                $this->ajaxReturn($result, 'json');
            }
        }
    }

    /**
     * 标签核实
     */
    public function labelCheck()
    {
        if (IS_POST) {
            $asModel = new AssetsInfoModel();
            $result = $asModel->uploadReport();
            $this->ajaxReturn($result, 'json');
        } else {
            $from = I('get.from');
            if ($from == 'jumpButton') {
                $buttonName = '设备图片';
                $tips = '标签状态在上传成功后，改为已核实（无法贴标）！';
                $type = 2;
            } else {
                $buttonName = '已贴标签图片';
                $tips = '标签状态在上传成功后，改为已核实！';
                $type = 1;
            }
            $asModel = new \Admin\Model\AssetsInfoModel();
            $assid = I('GET.assid');
            $assnum = I('GET.assnum');
            $status = I('GET.status');
            $departids = explode(',', session('departid'));
            if (!$assid) {
                $asInfo = $asModel->DB_get_one('assets_info', 'assid', array('assnum' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
                if (!$asInfo) {
                    $asInfo = $asModel->DB_get_one('assets_info', 'assid', array('assorignum' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
                }
                if (!$asInfo) {
                    $asInfo = $asModel->DB_get_one('assets_info', 'assid', array('assorignum_spare' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
                }
                $assid = $asInfo['assid'];
//                $idarr = [];
//
//
//                if ($assnum) {
//                    //查询设备assid
//                    $idarr = $asModel->DB_get_one('assets_info', 'assid', array('assnum' => $assnum, 'is_delete' => '0'));
//                    $assid = $idarr['assid'];
//                }
//                if (!$idarr) {
//                    $idarr = $asModel->DB_get_one('assets_info', 'assid', array('assorignum' => $assnum, 'is_delete' => '0'));
//                    $assid = $idarr['assid'];
//                }
//                if (!$idarr) {
//                    $idarr = $asModel->DB_get_one('assets_info', 'assid', array('assorignum_spare' => $assnum, 'is_delete' => '0'));
//                    $assid = $idarr['assid'];
//                }
                if (!$asInfo) {
                    $result['status'] = 302;
                    $msg['tips'] = '查找不到编码为 ' . $assnum . ' 的设备信息';
                    $msg['url'] = '';
                    $msg['btn'] = '';
                    $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
                    $this->ajaxReturn($result, 'json');
                    exit;
                }
            }
            //组织表单第一部分数据设备基础信息
            $assets = $asModel->getAssetsInfo($assid);
            if (!in_array($assets['departid'], $departids)) {
                $result['status'] = 302;
                $msg['tips'] = '编码为 ' . $assnum . ' 的设备不属于您管理的科室';
                $msg['url'] = '';
                $msg['btn'] = '';
                $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result);
                exit;
            }
            /*if ($type == 1 && $assets['print_status'] == 2 && !$status) {
                $this->assign('tips', '当前设备为核实（无法贴标）状态，是否确定要更改为已核实状态');
                $this->assign('btn1', '确定');
                $this->assign('url1', get_url() . '?assnum=' . $assnum . '&status=1');
                $this->assign('btn2', '取消');
                $this->assign('url2', C('VUE_NAME').'/'.$this->assets_list);
                $this->display('Pub/Notin/judge');
                exit;
            } else if ($type == 2 && $assets['print_status'] == 1 && !$status) {
                $this->assign('tips', '当前设备为已核实状态，是否确定要更改为核实（无法贴标）状态');
                $this->assign('btn1', '确定');
                $this->assign('url1', get_url() . '?assid=' . $assid . '&status=1');
                $this->assign('btn2', '取消');
                $this->assign('url2', C('VUE_NAME').'/'.$this->assets_list);
                $this->display('Pub/Notin/judge');
                exit;
            }*/
            $file_data = explode(",", $assets['pic_url']);
            $jssdk = new WxAccessTokenModel();
            $signPackage = $jssdk->GetSignPackage();
            $this->signPackage = $signPackage;
            $this->assign('file_data', $file_data);
            $this->assign('buttonName', $buttonName);
            $this->assign('tips', $tips);
            $this->assign('type', $type);
            //组织表单第三部分厂商信息
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $offlineSuppliers = $OfflineSuppliersModel->DB_get_one('assets_factory', 'ols_facid,ols_supid,ols_repid', array('assid' => $assets['assid']));
            $factoryInfo = [];
            $supplierInfo = [];
            $repairInfo = [];
            $offlineSuppliersFields = 'olsid,sup_name,salesman_name,salesman_phone,artisan_name,artisan_phone';
            if ($offlineSuppliers['ols_facid']) {
                $factoryInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_facid']]);
                $factoryFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_facid']);
            }
            if ($offlineSuppliers['ols_supid']) {
                $supplierInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_supid']]);
                $supplierFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_supid']);
            }
            if ($offlineSuppliers['ols_repid']) {
                $repairInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_repid']]);
                $repairFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_repid']);
            }
            $assets['factoryInfo'] = $factoryInfo;
            $assets['supplierInfo'] = $supplierInfo;
            $assets['repairInfo'] = $repairInfo;
            $result['status'] = 1;
            $result['asArr'] = $assets;
            $result['type'] = $type;
            $result['tips'] = $tips;
            $this->ajaxReturn($result, 'json');
        }
    }
}