<?php
/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/8/2
 * Time: 11:36
 */

namespace Admin\Controller\Purchases;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\PurchasesModel;
use Admin\Model\AssetsInfoModel;
use Admin\Model\PurchaseCheckModel;
use Admin\Model\PurchasesContractModel;

/**
 * 安装调试验收管理
 * Class PurchaseCheckController
 * @package Admin\Controller\Purchases
 */
class PurchaseCheckController extends CheckLoginController
{
    private $MODULE = 'Purchases';

    //验收列表
    public function checkAssetsLists()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'uploadEnclosure':
                    //资料上传
                    $PurchaseCheckModel = new PurchaseCheckModel();
                    $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'jpg', 'png', 'jpeg', 'pdf');
                    $result = $PurchaseCheckModel->uploadfile('purchases', $style);
                    $result = $PurchaseCheckModel->addCheckFile($result);
                    $result['add_user'] = session('username');
                    $result['add_time'] = getHandleDate(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'upload':
                    //附件上传
                    $PurchaseCheckModel = new PurchaseCheckModel();
                    $result = $PurchaseCheckModel->uploadfile('purchases');
                    $result = $PurchaseCheckModel->addCheckFile($result);
                    $result['add_user'] = session('username');
                    $result['add_time'] = getHandleDate(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'deleteFile':
                    //移除附件
                    $PurchaseCheckModel = new PurchaseCheckModel();
                    $result=$PurchaseCheckModel->deleteFile();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //获取合同列表数据
                    $PurchaseCheckModel = new PurchaseCheckModel();
                    $result = $PurchaseCheckModel->checkAssetsLists();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case'showCheckAssetsDetails':
                    //验收详情页面
                    $this->showCheckAssetsDetails();
                    break;
                default:
                    //显示验收列表页
                    $this->assign('now_date',date('Y-m-d'));
                    $this->assign('checkAssetsListsUrl', get_url());
                    $this->display();
                    break;
            }
        }
    }

    //验收列表页
    private function showcheckAssetsLists()
    {
        $PurchasesContractModel = new PurchasesContractModel();
        $supplier = $PurchasesContractModel->getSupplierArr();
        $assetsWhere['is_delete'] = ['NEQ', C('YES_STATUS')];
        $assets = $PurchasesContractModel->DB_get_all('purchases_tender_detail', 'assets_name', $assetsWhere, 'assets_name');
        $this->assign('supplier', $supplier);
        $this->assign('assets', $assets);
        $this->assign('checkAssetsListsUrl', get_url());
        $this->display();
    }


    private function showCheckAssetsDetails(){
        $assets_id = I('GET.assets_id');
        if ($assets_id) {
            $PurchaseCheckModel = new PurchaseCheckModel();
            //获取设备明细基本信息
            $assetsinfo = $PurchaseCheckModel->getCheckassetsBasic($assets_id);
            //获取申购信息
            $apply=$PurchaseCheckModel->getAssetsApplyBasic($assetsinfo['apply_id']);
            //获取合同信息
            $contract=$PurchaseCheckModel->getAssetsContractBasic($assetsinfo['contract_id']);
            //获取合同付款信息
            $contractPay=$PurchaseCheckModel->getAssetsContractPay($assetsinfo['contract_id']);
            //获取已上传的资料
            $fileData = $PurchaseCheckModel->getCheckAssetsFile($assets_id);
            //获取招标信息


            $this->assign('assets', $assetsinfo);
            $this->assign('apply', $apply);
            $this->assign('contract', $contract);
            $this->assign('contractPay', $contractPay);
            $this->assign('file', $fileData['file']);
            $this->assign('enclosure', $fileData['enclosure']);
            $this->assign('checkAssetsDetailsUrl', get_url());
            $this->assign('check_user', session('username'));
            $this->assign('assets_id', $assets_id);
            $this->display('showCheckAssetsDetails');
        } else {
            $this->error('非法操作');
        }
    }


    //验收设备
    public function checkAssets()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'uploadEnclosure':
                    //资料上传
                    $PurchaseCheckModel = new PurchaseCheckModel();
                    $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'jpg', 'png', 'jpeg', 'pdf');
                    $result = $PurchaseCheckModel->uploadfile('purchases', $style);
                    $result = $PurchaseCheckModel->addCheckFile($result);
                    $result['add_user'] = session('username');
                    $result['add_time'] = getHandleDate(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'upload':
                    //附件上传
                    $PurchaseCheckModel = new PurchaseCheckModel();
                    $result = $PurchaseCheckModel->uploadfile('purchases');
                    $result = $PurchaseCheckModel->addCheckFile($result);
                    $result['add_user'] = session('username');
                    $result['add_time'] = getHandleDate(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'deleteFile':
                    //移除附件
                    $PurchaseCheckModel = new PurchaseCheckModel();
                    $result=$PurchaseCheckModel->deleteFile();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //新增操作
                    $PurchaseCheckModel = new PurchaseCheckModel();
                    $result = $PurchaseCheckModel->checkAssets();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $assets_id = I('GET.assets_id');
            if ($assets_id) {
                $PurchaseCheckModel = new PurchaseCheckModel();
                //获取设备明细基本信息
                $assetsinfo = $PurchaseCheckModel->getCheckassetsBasic($assets_id);
                //获取申购信息
                $apply=$PurchaseCheckModel->getAssetsApplyBasic($assetsinfo['apply_id']);
                //获取合同信息
                $contract=$PurchaseCheckModel->getAssetsContractBasic($assetsinfo['contract_id']);
                //获取合同付款信息
                $contractPay=$PurchaseCheckModel->getAssetsContractPay($assetsinfo['contract_id']);
                //获取字典信息
                $dic = $PurchaseCheckModel->getAssetsDicBasic($assetsinfo['assets_name'], $assetsinfo['hospital_id']);
                $baseSetting = [];
                include APP_PATH . "Common/cache/basesetting.cache.php";
                //辅助分类
                $assets_helpcat = [];
                //财务分类
                $assets_finance = [];
                //附属设备分类
                $acin_category = [];
                //资金来源
                $assets_capitalfrom = [];
                //资产来源
                $assets_assfrom = [];
                foreach ($baseSetting['assets'] as $k => $v) {
                    if ($k == 'assets_helpcat') {
                        $assets_helpcat = $v['value'];
                    }
                    if ($k == 'assets_finance') {
                        $assets_finance = $v['value'];
                    }
                    if ($k == 'acin_category') {
                        $acin_category = $v['value'];
                    }
                    if ($k == 'assets_capitalfrom') {
                        $assets_capitalfrom = $v['value'];
                    }
                    if ($k == 'assets_assfrom') {
                        $assets_assfrom = $v['value'];
                    }
                }
                //查设备分类
                $category = $PurchaseCheckModel->DB_get_all('category', 'catid,catenum,category,parentid', array('status' => 1, 'is_delete' => 0, 'hospital_id' => $assetsinfo['hospital_id']), '', 'catid asc', '');
                $category = getTree('parentid', 'catid', $category, 0, 0, ' ➣ ');
                $department = $PurchaseCheckModel->DB_get_one('department', 'departid,department,address,assetsrespon', array('departid' => $assetsinfo['departid']));
                //获取已上传的资料
                $fileData = $PurchaseCheckModel->getCheckAssetsFile($assets_id);
                //生成二维码
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/uploadPic?id=' . $assets_id.'&username='.session('username');
                $codeUrl = $PurchaseCheckModel->createCodePic($url);
                $codeUrl = trim($codeUrl,'.');
                $asModel = new AssetsInfoModel();
                $assetsLevel = $asModel->getAssetsLevel();
                $this->assign('assetsLevel', $assetsLevel);
                $this->assign('codeUrl', $codeUrl);
                $this->assign('assets', $assetsinfo);
                $this->assign('apply', $apply);
                $this->assign('contract', $contract);
                $this->assign('contractPay', $contractPay);
                $this->assign('assets_finance', $assets_finance);
                $this->assign('assets_helpcat', $assets_helpcat);
                $this->assign('assets_capitalfrom', $assets_capitalfrom);
                $this->assign('acin_category', $acin_category);
                $this->assign('category', $category);
                $this->assign('assets_assfrom', $assets_assfrom);
                $this->assign('department', $department);
                $this->assign('dic', $dic);
                $this->assign('file', $fileData['file']);
                $this->assign('enclosure', $fileData['enclosure']);
                $this->assign('checkAssetsUrl', get_url());
                $this->assign('check_user', session('username'));
                $this->assign('now_date',date('Y-m-d'));
                $this->assign('assets_id', $assets_id);
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    public function showCheck()
    {
        $this->display();
    }

    /**
     * Notes: 设备入库列表
     */
    public function assetsWareLists()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->getWareLists();
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            if ($action == 'showWare') {
                $in_id = I('get.id');
                $inWareInfo = $purModel->getInWareInfo($in_id);
                $assets = $purModel->getInWareAssets($in_id);
                $this->assign('username',session('username'));
                $this->assign('nowday',date('Y-m-d'));
                $this->assign('inWareInfo',$inWareInfo);
                $this->assign('assets',$assets);
                $this->display('showWare');
            } else {
                $this->assign('assetsWareLists', get_url());
                $this->display();
            }
        }
    }

    /**
     * Notes: 添加入库单
     */
    public function addWare()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $action = I('POST.action');
            switch ($action){
                case 'getLists':
                    $result = $purModel->getCheckedLists();
                    $this->ajaxReturn($result);
                    break;
                case 'saveWare':
                    $result = $purModel->saveWare();
                    $this->ajaxReturn($result);
                    break;
            }
        }else{
            $this->assign('username',session('username'));
            $this->assign('nowday',date('Y-m-d'));
            $this->assign('addWare',get_url());
            $this->display();
        }
    }

    /**
     * Notes: 入库审核
     */
    public function approveWare()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->saveInWareApprove();
            $this->ajaxReturn($result);
        } else {
            $in_id = I('get.id');
            $inWareInfo = $purModel->getInWareInfo($in_id);
            $assets = $purModel->getInWareAssets($in_id);
            $this->assign('username',session('username'));
            $this->assign('nowday',date('Y-m-d'));
            $this->assign('inWareInfo',$inWareInfo);
            $this->assign('assets',$assets);
            $this->display();
        }
    }

    /**
     * Notes: 设备出库列表
     */
    public function assetsOutLists()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->getOutLists();
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            if ($action == 'showOut') {
                $out_id = I('get.id');
                $outWareInfo = $purModel->getOutWareInfo($out_id);
                $assets = $purModel->getOutWareAssets($out_id);
                $debugInfo = $purModel->getDebugInfo($out_id);
                $departname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                $outWareInfo['department'] = $departname[$outWareInfo['departid']]['department'];
                $this->assign('outWareInfo',$outWareInfo);
                $this->assign('debugInfo',$debugInfo);
                $this->assign('assets',$assets);
                $this->display('showOut');
            } else {
                $this->assign('assetsOutLists', get_url());
                $this->display();
            }
        }
    }

    /**
     * Notes: 添加出库单
     */
    public function addOut()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $action = I('POST.action');
            switch ($action){
                case 'getLists':
                    $result = $purModel->getCanOutLists();
                    $this->ajaxReturn($result);
                    break;
                case 'saveOut':
                    $result = $purModel->saveOut();
                    $this->ajaxReturn($result);
                    break;
            }
        }else{
            $users = $purModel->DB_get_all('user','userid,username',array('is_delete'=>0,'status'=>1,'job_hospitalid'=>session('current_hospitalid'),'is_super'=>0));
            $this->assign('users',$users);
            $this->assign('username',session('username'));
            $this->assign('nowday',date('Y-m-d'));
            $this->assign('addOut',get_url());
            $this->display();
        }
    }

    /**
     * Notes: 出库审核
     */
    public function approveOut()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $result = $purModel->saveOutApprove();
            $this->ajaxReturn($result);
        } else {
            $out_id = I('get.id');
            $outWareInfo = $purModel->getOutWareInfo($out_id);
            $assets = $purModel->getOutWareAssets($out_id);
            $debugInfo = $purModel->getDebugInfo($out_id);
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $outWareInfo['department'] = $departname[$outWareInfo['departid']]['department'];
            $this->assign('username',session('username'));
            $this->assign('nowday',date('Y-m-d'));
            $this->assign('outWareInfo',$outWareInfo);
            $this->assign('debugInfo',$debugInfo);
            $this->assign('assets',$assets);
            $this->display();
        }
    }

    /**
     * 设备安装调试报告列表
     */
    public function installDebugList()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $result = $purModel->getDebugList();
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            if($action == 'showDebug'){
                $ware_assets_id = I('get.id');
                $asInfo = $purModel->outAssetsInfo($ware_assets_id);
                $debugInfo = $purModel->debugInfo($asInfo['out_id']);
                $debug_files = $purModel->outReportInfo($ware_assets_id);
                $departname = $catname = array();
                include APP_PATH . "Common/cache/department.cache.php";
                include APP_PATH . "Common/cache/category.cache.php";
                $asInfo['department'] = $departname[$asInfo['departid']]['department'];
                $asInfo['category'] = $catname[$asInfo['catid']]['category'];
                $this->assign('asInfo',$asInfo);
                $this->assign('debugInfo',$debugInfo);
                $this->assign('debug_files',$debug_files);
                $this->display('showDebug');
            }else{
                $this->assign('installDebugList', get_url());
                $this->assign('hospital_id', session('current_hospitalid'));
                $this->display();
            }
        }
    }

    /**
     * 设备安装调试报告
     */
    public function debugReport()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $action = I('post.action');
            switch ($action){
                case 'uploadFile':
                    $result = $purModel->uploadReport(C('UPLOAD_DIR_DEBUG_FILE_NAME'));
                    if($result['status'] == 1){
                        $wid = I('post.wid');
                        $oid = I('post.oid');
                        $data['ware_assets_id'] = $wid;
                        $data['out_id'] = $oid;
                        $data['file_name'] = $result['file_name'];
                        $data['save_name'] = $result['save_name'];
                        $data['file_type'] = $result['file_type'];
                        $data['file_size'] = $result['file_size'];
                        $data['file_url'] = $result['file_url'];
                        $data['add_user'] = session('username');
                        $data['add_time'] = date('Y-m-d H:i:s');
                        $purModel->insertData('purchases_assets_install_debug_report',$data);
                        $purModel->updateData('purchases_out_warehouse_assets',array('debug_status'=>1),array('ware_assets_id'=>$wid));
                    }
                    $this->ajaxReturn($result);
                    break;
                default:
                    $wid = I('post.wid');
                    $res = $purModel->updateData('purchases_out_warehouse_assets',array('debug_status'=>2),array('ware_assets_id'=>$wid));
                    if($res){
                        $this->ajaxReturn(array('status'=>1,'msg'=>'保存成功！'));
                    }else{
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'保存失败！'));
                    }
                    break;
            }
        }else{
            $ware_assets_id = I('get.id');
            $asInfo = $purModel->outAssetsInfo($ware_assets_id);
            $debugInfo = $purModel->getDebugInfo($asInfo['out_id']);
            $files = $purModel->outReportInfo($ware_assets_id);
            $departname = $catname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            include APP_PATH . "Common/cache/category.cache.php";
            $asInfo['department'] = $departname[$asInfo['departid']]['department'];
            $asInfo['category'] = $catname[$asInfo['catid']]['category'];
            //生成二维码
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/uploadReport?id=' . $asInfo['out_id'].'&i=out_id&id2='.$ware_assets_id.'&i2=ware_assets_id&t=purchases_assets_install_debug_report&username='.session('username');
            $codeUrl = $purModel->createCodePic($url);
            $codeUrl = trim($codeUrl,'.');
            $this->assign('codeUrl',$codeUrl);
            $this->assign('debugInfo',$debugInfo);
            $this->assign('asInfo',$asInfo);
            $this->assign('files',$files);
            $this->display();
        }
    }

    /**
     * 临床培训报告列表
     */
    public function departTrainList()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $result = $purModel->getTrainList();
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            switch ($action){
                case 'showTrain':
                    $ware_assets_id = I('get.id');
                    $asInfo = $purModel->outAssetsInfo($ware_assets_id);
                    $departname = $catname = array();
                    include APP_PATH . "Common/cache/department.cache.php";
                    include APP_PATH . "Common/cache/category.cache.php";
                    $asInfo['department'] = $departname[$asInfo['departid']]['department'];
                    $asInfo['category'] = $catname[$asInfo['catid']]['category'];
                    //查询调试信息
                    $debugInfo = $purModel->debugInfo($asInfo['out_id']);
                    //查询调试报告
                    $debug_files = $purModel->outReportInfo($ware_assets_id);
                    //查询培训计划信息
                    $trainInfo = $purModel->getTrainInfo($asInfo['train_id']);
                    //查询培训计划报告
                    $files = $purModel->getTrainReports($asInfo['train_id']);
                    $this->assign('asInfo',$asInfo);
                    $this->assign('debugInfo',$debugInfo);
                    $this->assign('debug_files',$debug_files);
                    $this->assign('trainInfo',$trainInfo);
                    $this->assign('files',$files);
                    $this->display('showTrain');
                    break;
                case 'uploadReport':
                    $ware_assets_id = I('get.id');
                    $asInfo = $purModel->outAssetsInfo($ware_assets_id);
                    $departname = $catname = array();
                    include APP_PATH . "Common/cache/department.cache.php";
                    include APP_PATH . "Common/cache/category.cache.php";
                    $asInfo['department'] = $departname[$asInfo['departid']]['department'];
                    $asInfo['category'] = $catname[$asInfo['catid']]['category'];
                    //查询培训计划信息
                    $trainInfo = $purModel->getTrainInfo($asInfo['train_id']);
                    //查询培训计划报告
                    $files = $purModel->getTrainReports($asInfo['train_id']);
                    //生成二维码
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                    $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/uploadReport?id=' . $asInfo['train_id'].'&i=train_id&t=purchases_assets_train_report&username='.session('username');
                    $codeUrl = $purModel->createCodePic($url);
                    $codeUrl = trim($codeUrl,'.');
                    $this->assign('codeUrl',$codeUrl);
                    $this->assign('asInfo',$asInfo);
                    $this->assign('trainInfo',$trainInfo);
                    $this->assign('files',$files);
                    $this->display('trainPlansReport');
                    break;
                default:
                    $this->assign('departTrainList', get_url());
                    $this->assign('hospital_id',session('current_hospitalid'));
                    $this->display();
                    break;
            }
        }
    }

    /**
     * 临床培训报告
     */
    public function trainPlans()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $action = I('post.action');
            switch ($action){
                case 'uploadFile':
                    $result = $purModel->uploadReport(C('UPLOAD_DIR_TRAIN_FILE_NAME'));
                    if($result['status'] == 1){
                        $train_id = I('post.train_id');
                        $data['train_id'] = $train_id;
                        $data['file_name'] = $result['file_name'];
                        $data['save_name'] = $result['save_name'];
                        $data['file_type'] = $result['file_type'];
                        $data['file_size'] = $result['file_size'];
                        $data['file_url'] = $result['file_url'];
                        $data['add_user'] = session('username');
                        $data['add_time'] = date('Y-m-d H:i:s');
                        $purModel->insertData('purchases_assets_train_report',$data);
                        $purModel->updateData('purchases_out_warehouse_assets',array('train_status'=>1),array('train_id'=>$train_id));
                    }
                    $this->ajaxReturn($result);
                    break;
                case 'finalSave':
                    $train_id = I('post.train_id');
                    //查询是否已上传报告
                    $file = $purModel->DB_get_one('purchases_assets_train_report','file_id',array('train_id'=>$train_id,'is_delete'=>C('NO_STATUS')));
                    if(!$file){
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'请先上传报告！'));
                    }
                    $res = $purModel->updateData('purchases_out_warehouse_assets',array('train_status'=>2),array('train_id'=>$train_id));
                    if($res){
                        $this->ajaxReturn(array('status'=>1,'msg'=>'保存成功！'));
                    }else{
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'保存失败！'));
                    }
                    break;
                default:
                    $result = $purModel->saveTrainPlans();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
            $ids = I('get.id');
            $ware_ids = explode(',',$ids);
            $asInfo = $purModel->outAssetsInfo($ware_ids[0]);
            $departname = $catname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            include APP_PATH . "Common/cache/category.cache.php";
            $asInfo['department'] = $departname[$asInfo['departid']]['department'];
            $asInfo['category'] = $catname[$asInfo['catid']]['category'];
            $users = $purModel->DB_get_all('user','userid,username',array('is_delete'=>0,'status'=>1,'job_hospitalid'=>session('current_hospitalid'),'is_super'=>0));
            $this->assign('users',$users);
            $this->assign('username',session('username'));
            $this->assign('nowday',date('Y-m-d'));
            $this->assign('addOut',get_url());
            $this->assign('asInfo',$asInfo);
            $this->assign('ware_ids',$ids);
            $this->assign('hospital_id',session('current_hospitalid'));
            $this->display();
        }
    }

    /**
     * 培训考核报告列表
     */
    public function trainExamineList()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $result = $purModel->getTrainExamineList();
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            switch ($action){
                case 'showAssess':
                    $ware_assets_id = I('get.id');
                    $asInfo = $purModel->outAssetsInfo($ware_assets_id);
                    $departname = $catname = array();
                    include APP_PATH . "Common/cache/department.cache.php";
                    include APP_PATH . "Common/cache/category.cache.php";
                    $asInfo['department'] = $departname[$asInfo['departid']]['department'];
                    $asInfo['category'] = $catname[$asInfo['catid']]['category'];
                    //查询调试信息
                    $debugInfo = $purModel->debugInfo($asInfo['out_id']);
                    //查询调试报告
                    $debug_files = $purModel->outReportInfo($ware_assets_id);
                    //查询培训计划信息
                    $trainInfo = $purModel->getTrainInfo($asInfo['train_id']);
                    //查询培训计划报告
                    $files = $purModel->getTrainReports($asInfo['train_id']);
                    //查询培训考核报告
                    $assessReports = $purModel->getTrainAssessReports($asInfo['train_id']);
                    $this->assign('asInfo',$asInfo);
                    $this->assign('debugInfo',$debugInfo);
                    $this->assign('debug_files',$debug_files);
                    $this->assign('trainInfo',$trainInfo);
                    $this->assign('files',$files);
                    $this->assign('assessReports',$assessReports);
                    $this->display('showAssess');
                    break;
                default:
                    $this->assign('hospital_id',session('current_hospitalid'));
                    $this->assign('trainExamineList', get_url());
                    $this->display();
            }
        }
    }

    /**
     * 培训考核报告
     */
    public function assessReport()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $action = I('post.action');
            switch ($action){
                case 'uploadFile':
                    $result = $purModel->uploadReport(C('UPLOAD_DIR_ASSESS_FILE_NAME'));
                    if($result['status'] == 1){
                        $train_id = I('post.train_id');
                        $data['train_id'] = $train_id;
                        $data['file_name'] = $result['file_name'];
                        $data['save_name'] = $result['save_name'];
                        $data['file_type'] = $result['file_type'];
                        $data['file_size'] = $result['file_size'];
                        $data['file_url'] = $result['file_url'];
                        $data['add_user'] = session('username');
                        $data['add_time'] = date('Y-m-d H:i:s');
                        $purModel->insertData('purchases_assets_train_assessment_report',$data);
                    }
                    $this->ajaxReturn($result);
                    break;
                case 'finalSave':
                    $train_id = I('post.train_id');
                    //查询是否已上传报告
                    $file = $purModel->DB_get_one('purchases_assets_train_assessment_report','file_id',array('train_id'=>$train_id,'is_delete'=>C('NO_STATUS')));
                    if(!$file){
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'请先上传报告！'));
                    }
                    $res = $purModel->updateData('purchases_out_warehouse_assets',array('assessment_status'=>1),array('train_id'=>$train_id));
                    if($res){
                        $this->ajaxReturn(array('status'=>1,'msg'=>'保存成功！'));
                    }else{
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'保存失败！'));
                    }
                    break;
            }
        }else{
            $ware_ids = I('get.id');
            $ware_assets_id = explode(',',$ware_ids);
            $asInfo = $purModel->outAssetsInfo($ware_assets_id[0]);
            $departname = $catname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            include APP_PATH . "Common/cache/category.cache.php";
            $asInfo['department'] = $departname[$asInfo['departid']]['department'];
            $asInfo['category'] = $catname[$asInfo['catid']]['category'];
            //查询培训计划信息
            $trainInfo = $purModel->getTrainInfo($asInfo['train_id']);
            //查询培训计划报告
            $files = $purModel->getTrainReports($asInfo['train_id']);
            //查询培训考核报告
            $assessReports = $purModel->getTrainAssessReports($asInfo['train_id']);
            //生成二维码
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/uploadReport?id=' . $asInfo['train_id'].'&i=train_id&t=purchases_assets_train_assessment_report&username='.session('username');
            $codeUrl = $purModel->createCodePic($url);
            $codeUrl = trim($codeUrl,'.');
            $this->assign('codeUrl',$codeUrl);
            $this->assign('asInfo',$asInfo);
            $this->assign('trainInfo',$trainInfo);
            $this->assign('files',$files);
            $this->assign('assessReports',$assessReports);
            $this->display();
        }
    }

    /**
     * 培训考核报告列表
     */
    public function testReportList()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $result = $purModel->getTestReportList();
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            switch ($action){
                case 'showTest':
                    $ware_assets_id = I('get.id');
                    $asInfo = $purModel->outAssetsInfo($ware_assets_id);
                    $departname = $catname = array();
                    include APP_PATH . "Common/cache/department.cache.php";
                    include APP_PATH . "Common/cache/category.cache.php";
                    $asInfo['department'] = $departname[$asInfo['departid']]['department'];
                    $asInfo['category'] = $catname[$asInfo['catid']]['category'];
                    //查询调试信息
                    $debugInfo = $purModel->debugInfo($asInfo['out_id']);
                    //查询调试报告
                    $debug_files = $purModel->outReportInfo($ware_assets_id);
                    //查询培训计划信息
                    $trainInfo = $purModel->getTrainInfo($asInfo['train_id']);
                    //查询培训计划报告
                    $files = $purModel->getTrainReports($asInfo['train_id']);
                    //查询培训考核报告
                    $assessReports = $purModel->getTrainAssessReports($asInfo['train_id']);
                    //查询测试运行报告
                    $testReports = $purModel->getTestReports($ware_assets_id,$asInfo['out_id']);
                    $this->assign('asInfo',$asInfo);
                    $this->assign('debugInfo',$debugInfo);
                    $this->assign('debug_files',$debug_files);
                    $this->assign('trainInfo',$trainInfo);
                    $this->assign('files',$files);
                    $this->assign('assessReports',$assessReports);
                    $this->assign('testReports',$testReports);
                    $this->display('showTest');
                    break;
                default:
                    $this->assign('hospital_id',session('current_hospitalid'));
                    $this->assign('testReportList', get_url());
                    $this->display();
            }
        }
    }

    /**
     * 培训考核报告
     */
    public function testReport()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $action = I('post.action');
            switch ($action){
                case 'uploadFile':
                    $result = $purModel->uploadReport(C('UPLOAD_DIR_TEST_FILE_NAME'));
                    if($result['status'] == 1){
                        $wid = I('post.wid');
                        $oid = I('post.oid');
                        $data['ware_assets_id'] = $wid;
                        $data['out_id'] = $oid;
                        $data['file_name'] = $result['file_name'];
                        $data['save_name'] = $result['save_name'];
                        $data['file_type'] = $result['file_type'];
                        $data['file_size'] = $result['file_size'];
                        $data['file_url'] = $result['file_url'];
                        $data['add_user'] = session('username');
                        $data['add_time'] = date('Y-m-d H:i:s');
                        $purModel->insertData('purchases_assets_test_report',$data);
                    }
                    $this->ajaxReturn($result);
                    break;
                case 'finalSave':
                    $wid = I('post.wid');
                    if(empty($wid)){
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'请上传报告文件'));
                    }else{
                        $res = $purModel->updateData('purchases_out_warehouse_assets',array('test_status'=>1),array('ware_assets_id'=>$wid));
                        if($res){
                            $this->ajaxReturn(array('status'=>1,'msg'=>'保存成功！'));
                        }else{
                            $this->ajaxReturn(array('status'=>-1,'msg'=>'保存失败！'));
                        }
                    }

                    break;
            }
        }else{
            $ware_assets_id = I('get.id');
            $asInfo = $purModel->outAssetsInfo($ware_assets_id);
            $departname = $catname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            include APP_PATH . "Common/cache/category.cache.php";
            $asInfo['department'] = $departname[$asInfo['departid']]['department'];
            $asInfo['category'] = $catname[$asInfo['catid']]['category'];
            //查询培训计划信息
            $trainInfo = $purModel->getTrainInfo($asInfo['train_id']);
            //查询培训计划报告
            $files = $purModel->getTrainReports($asInfo['train_id']);
            //查询培训考核报告
            $assessReports = $purModel->getTrainAssessReports($asInfo['train_id']);
            //查询测试运行报告
            $testReports = $purModel->getTestReports($ware_assets_id,$asInfo['out_id']);
            //生成二维码
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol".C('HTTP_HOST'). C('ADMIN_NAME').'/Public/uploadReport?id=' . $asInfo['out_id'].'&i=out_id&id2='.$ware_assets_id.'&i2=ware_assets_id&t=purchases_assets_test_report&username='.session('username');
            $codeUrl = $purModel->createCodePic($url);
            $codeUrl = trim($codeUrl,'.');
            $this->assign('codeUrl',$codeUrl);
            $this->assign('asInfo',$asInfo);
            $this->assign('trainInfo',$trainInfo);
            $this->assign('files',$files);
            $this->assign('assessReports',$assessReports);
            $this->assign('testReports',$testReports);
            $this->display();
        }
    }

    /**
     * 设备质量验收报告列表（首次计量）
     */
    public function firstMeteringList()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $result = $purModel->getFirstMeteringList();
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            switch ($action){
                case 'showMetering':
                    $ware_assets_id = I('get.id');
                    $asInfo = $purModel->outAssetsInfo($ware_assets_id);
                    $departname = $catname = array();
                    include APP_PATH . "Common/cache/department.cache.php";
                    include APP_PATH . "Common/cache/category.cache.php";
                    $asInfo['department'] = $departname[$asInfo['departid']]['department'];
                    $asInfo['category'] = $catname[$asInfo['catid']]['category'];
                    //查询调试信息
                    $debugInfo = $purModel->debugInfo($asInfo['out_id']);
                    //查询调试报告
                    $debug_files = $purModel->outReportInfo($ware_assets_id);
                    //查询培训计划信息
                    $trainInfo = $purModel->getTrainInfo($asInfo['train_id']);
                    //查询培训计划报告
                    $files = $purModel->getTrainReports($asInfo['train_id']);
                    //查询培训考核报告
                    $assessReports = $purModel->getTrainAssessReports($asInfo['train_id']);
                    //查询测试运行报告
                    $testReports = $purModel->getTestReports($ware_assets_id,$asInfo['out_id']);
                    //查询首次计量报告
                    $meteringReports = $purModel->getMeteringReports($ware_assets_id,$asInfo['out_id']);
                    $this->assign('asInfo',$asInfo);
                    $this->assign('debugInfo',$debugInfo);
                    $this->assign('debug_files',$debug_files);
                    $this->assign('trainInfo',$trainInfo);
                    $this->assign('files',$files);
                    $this->assign('assessReports',$assessReports);
                    $this->assign('testReports',$testReports);
                    $this->assign('meteringReports',$meteringReports);
                    $this->display('showMetering');
                    break;
                default:
                    $this->assign('hospital_id',session('current_hospitalid'));
                    $this->assign('firstMeteringList', get_url());
                    $this->display();
            }
        }
    }

    /**
     * 设备质量验收报告
     */
    public function firstMetering()
    {
        $purModel = new PurchasesModel();
        if(IS_POST){
            $action = I('post.action');
            switch ($action){
                case 'uploadFile':
                    $result = $purModel->uploadReport(C('UPLOAD_DIR_METERING_FILE_NAME'));
                    if($result['status'] == 1){
                        $wid = I('post.wid');
                        $oid = I('post.oid');
                        $data['ware_assets_id'] = $wid;
                        $data['out_id'] = $oid;
                        $data['file_name'] = $result['file_name'];
                        $data['save_name'] = $result['save_name'];
                        $data['file_type'] = $result['file_type'];
                        $data['file_size'] = $result['file_size'];
                        $data['file_url'] = $result['file_url'];
                        $data['add_user'] = session('username');
                        $data['add_time'] = date('Y-m-d H:i:s');
                        $purModel->insertData('purchases_assets_metering_report',$data);
                    }
                    $this->ajaxReturn($result);
                    break;
                case 'finalSave':
                    $wid = I('post.wid');
                    $res = $purModel->updateData('purchases_out_warehouse_assets',array('firstMetering_status'=>1),array('ware_assets_id'=>$wid));
                    if($res){
                        $this->ajaxReturn(array('status'=>1,'msg'=>'保存成功！'));
                    }else{
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'保存失败！'));
                    }
                    break;
            }
        }else{
            $ware_assets_id = I('get.id');
            $asInfo = $purModel->outAssetsInfo($ware_assets_id);
            $departname = $catname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            include APP_PATH . "Common/cache/category.cache.php";
            $asInfo['department'] = $departname[$asInfo['departid']]['department'];
            $asInfo['category'] = $catname[$asInfo['catid']]['category'];
            //查询培训计划信息
            $trainInfo = $purModel->getTrainInfo($asInfo['train_id']);
            //查询培训计划报告
            $files = $purModel->getTrainReports($asInfo['train_id']);
            //查询培训考核报告
            $assessReports = $purModel->getTrainAssessReports($asInfo['train_id']);
            //查询测试运行报告
            $testReports = $purModel->getTestReports($ware_assets_id,$asInfo['out_id']);
            //查询首次计量报告
            $meteringReports = $purModel->getMeteringReports($ware_assets_id,$asInfo['out_id']);
            //生成二维码
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/uploadReport?id=' . $asInfo['out_id'].'&i=out_id&id2='.$ware_assets_id.'&i2=ware_assets_id&t=purchases_assets_metering_report&username='.session('username');
            $codeUrl = $purModel->createCodePic($url);
            $codeUrl = trim($codeUrl,'.');
            $this->assign('codeUrl',$codeUrl);
            $this->assign('asInfo',$asInfo);
            $this->assign('trainInfo',$trainInfo);
            $this->assign('files',$files);
            $this->assign('assessReports',$assessReports);
            $this->assign('testReports',$testReports);
            $this->assign('meteringReports',$meteringReports);
            $this->display();
        }
    }

    /**
     * 设备质量验收报告列表
     */
    public function qualityReportLists()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $result = $purModel->getQualityReportList();
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            switch ($action){
                case 'showQuality':
                    $ware_assets_id = I('get.id');
                    $asInfo = $purModel->outAssetsInfo($ware_assets_id);
                    $departname = $catname = array();
                    include APP_PATH . "Common/cache/department.cache.php";
                    include APP_PATH . "Common/cache/category.cache.php";
                    $asInfo['department'] = $departname[$asInfo['departid']]['department'];
                    $asInfo['category'] = $catname[$asInfo['catid']]['category'];
                    //查询调试信息
                    $debugInfo = $purModel->debugInfo($asInfo['out_id']);
                    //查询调试报告
                    $debug_files = $purModel->outReportInfo($ware_assets_id);
                    //查询培训计划信息
                    $trainInfo = $purModel->getTrainInfo($asInfo['train_id']);
                    //查询培训计划报告
                    $files = $purModel->getTrainReports($asInfo['train_id']);
                    //查询培训考核报告
                    $assessReports = $purModel->getTrainAssessReports($asInfo['train_id']);
                    //查询测试运行报告
                    $testReports = $purModel->getTestReports($ware_assets_id,$asInfo['out_id']);
                    //查询首次计量报告
                    $meteringReports = $purModel->getMeteringReports($ware_assets_id,$asInfo['out_id']);
                    //查询验收资料
                    $reports = $purModel->getQualityReports($ware_assets_id,$asInfo['out_id']);
                    $y = $m = $h = $c = [];
                    foreach ($reports as $k=>$v){
                        if($v['type_code'] == 'Y'){
                            $y =$v;
                        }
                        if($v['type_code'] == 'M'){
                            $m =$v;
                        }
                        if($v['type_code'] == 'H'){
                            $h =$v;
                        }
                        if($v['type_code'] == 'C'){
                            $c =$v;
                        }
                    }
                    $this->assign('y',$y);
                    $this->assign('m',$m);
                    $this->assign('h',$h);
                    $this->assign('c',$c);
                    $this->assign('asInfo',$asInfo);
                    $this->assign('debugInfo',$debugInfo);
                    $this->assign('debug_files',$debug_files);
                    $this->assign('trainInfo',$trainInfo);
                    $this->assign('files',$files);
                    $this->assign('assessReports',$assessReports);
                    $this->assign('testReports',$testReports);
                    $this->assign('meteringReports',$meteringReports);
                    $this->display('showQuality');
                    break;
                default:
                    $this->assign('hospital_id',session('current_hospitalid'));
                    $this->assign('qualityReportLists', get_url());
                    $this->display();
            }
        }
    }

    /**
     * 设备质量验收报告
     */
    public function qualityReport()
    {
        $purModel = new PurchasesModel();
        if (IS_POST) {
            $action = I('post.action');
            switch ($action){
                case 'uploadFile':
                    $result = $purModel->uploadReport(C('UPLOAD_DIR_QUALITY_FILE_NAME'));
                    if($result['status'] == 1){
                        $wid = I('post.wid');
                        $oid = I('post.oid');
                        $data['ware_assets_id'] = $wid;
                        $data['out_id'] = $oid;
                        $data['type_code'] = I('post.type_code');
                        $data['file_name'] = $result['file_name'];
                        $data['save_name'] = $result['save_name'];
                        $data['file_type'] = $result['file_type'];
                        $data['file_size'] = $result['file_size'];
                        $data['file_url'] = $result['file_url'];
                        $data['add_user'] = session('username');
                        $data['add_time'] = date('Y-m-d H:i:s');
                        $purModel->insertData('purchases_assets_quality_report',$data);
                    }
                    $this->ajaxReturn($result);
                    break;
                case 'finalSave':
                    $wid = I('post.wid');
                    $wareInfo = $purModel->DB_get_one('purchases_out_warehouse_assets','ware_assets_id,out_id,assets_id,factorynum,serialnum,invoicenum,in_assetsinfo',array('ware_assets_id'=>$wid));
                    if($wareInfo['in_assetsinfo'] == 1){
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'该设备已入库，请勿重复操作！'));
                    }
                    //查询报告是否上传完毕
                    $files = $purModel->DB_get_count('purchases_assets_quality_report',array('ware_assets_id'=>$wid,'out_id'=>$wareInfo['out_id'],'is_delete'=>C('NO_STATUS')));
                    if($files['total'] != 4){
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'请先上传完毕相关文件再保存！'));
                    }
                    $res = $purModel->updateData('purchases_out_warehouse_assets',array('quality_status'=>1),array('ware_assets_id'=>$wid));
                    if($res){
                        //质量验收完成，把信息写入设备库
                        $purModel->insertNewAssets($wareInfo);
                        $purModel->updateData('purchases_out_warehouse_assets',array('in_assetsinfo'=>1),array('ware_assets_id'=>$wid));
                        $this->ajaxReturn(array('status'=>1,'msg'=>'设备已正式交付科室使用，可在设备管理列表中查询相关信息！'));
                    }else{
                        $this->ajaxReturn(array('status'=>-1,'msg'=>'保存失败！'));
                    }
                    break;
            }
        } else {
            $ware_assets_id = I('get.id');
            $asInfo = $purModel->DB_get_one('purchases_out_warehouse_assets','*',array('ware_assets_id'=>$ware_assets_id));
            $reports = $purModel->getQualityReports($ware_assets_id,$asInfo['out_id']);
            $y = $m = $h = $c = [];
            foreach ($reports as $k=>$v){
                if($v['type_code'] == 'Y'){
                    $y =$v;
                }
                if($v['type_code'] == 'M'){
                    $m =$v;
                }
                if($v['type_code'] == 'H'){
                    $h =$v;
                }
                if($v['type_code'] == 'C'){
                    $c =$v;
                }
            }
            $this->assign('asInfo',$asInfo);
            $this->assign('y',$y);
            $this->assign('m',$m);
            $this->assign('h',$h);
            $this->assign('c',$c);
            $this->display();
        }
    }
}