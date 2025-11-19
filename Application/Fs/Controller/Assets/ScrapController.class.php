<?php

namespace Fs\Controller\Assets;

use Admin\Controller\Tool\ToolController;
use Fs\Controller\Login\IndexController;
use Fs\Model\AssetsInfoModel;
use Fs\Model\AssetsScrapModel;
use Fs\Model\RepairModel;
use Fs\Model\WxAccessTokenModel;
use Admin\Model\OfflineSuppliersModel;

class ScrapController extends IndexController
{
    protected $fail_url = 'Notin/fail.html';//失败跳转地址
    protected $succ_url = 'Notin/suc.html';//成功跳转地址
    protected $index_url = 'Index/testindex.html';//首页地址
    protected $add_url = 'Scrap/getApplyList.html';//报废申请列表地址
    protected $approve_url = 'Notin/approve.html';
    protected $getScrapList_url = 'Scrap/getScrapList.html';//报废查询列表页地址

    /**
     * 报废申请列表
     */
    public function getApplyList()
    {
        $scrapModel = new AssetsScrapModel();
        $result = $scrapModel->get_Apply_List();
        $this->ajaxReturn($result, 'json');
    }

    /*
     * 报废申请
     */
    public function applyScrap()
    {
        if (IS_POST) {
            $scrapModel = new \Admin\Model\AssetsScrapModel();
            switch (I('post.type')) {
                case 'end':
                    $scrid = I('post.scrid');
                    $result = $scrapModel->endScrap($scrid);
                    $this->ajaxReturn($result);
                    break;
                case 'edit':
                    $result = $scrapModel->editScrap();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $result = $scrapModel->addScrap();
                    $this->ajaxReturn($result);
            }
            $this->ajaxReturn($result, 'JSON');
        } else {
            $scrapModel = new AssetsScrapModel();
            $assnum = I('get.assnum');
            if (!$assnum) {
                $result['status'] = 302;
                $msg['tips'] = '参数非法';
                $msg['url'] = '';
                $msg['btn'] = '';
                $result['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result);
            }
            $action = I('get.action');
            if ($action == 'brcode') {
                //二维码扫码进来，验证是否可以申请报废
                $result = $scrapModel->scanQRcode_addSscrap();
                if ($result['status'] !== 1) {
                    $this->ajaxReturn($result);
                }
            }
            //查询设备信息
            $asInfo = $scrapModel->DB_get_one('assets_info', 'assid,assnum,status', array('assnum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
            if (!$asInfo) {
                $result['status'] = 302;
                $msg['tips'] = '查找不到设备编码为【' . $assnum . '】的信息';
                $msg['url'] = '';
                $msg['btn'] = '';
                $result['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result);
            }
            if ($asInfo['status'] != C('ASSETS_STATUS_USE')) {
                //不是在用状态
                if ($asInfo['status'] == C('ASSETS_STATUS_SCRAP_ON')) {
                    //该设备在报废中，查询报废信息
                    $scrInfo = $scrapModel->DB_get_one('assets_scrap', '*', array('assid' => $asInfo['assid']), 'scrid desc');
                    if ($scrInfo['approve_status'] == 0) {
                        $result['status'] = 302;
                        $msg['tips'] = '报废审核中';
                        $msg['url'] = '';
                        $msg['btn'] = '';
                        $result['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
                        $this->ajaxReturn($result);
                    }
                    if ($scrInfo['approve_status'] == 2) {
                        $scrid = $scrInfo['scrid'];
                        if ($scrid) {
                            $show_form = 1;
                            //查询申请人联系电话
                            $tel = $scrapModel->DB_get_one('user', 'telephone', array('username' => $scrInfo['apply_user']));
                            $scrInfo['telephone'] = $tel['telephone'];
                            if ($scrInfo['retrial_status'] == 3 || $scrInfo['retrial_status'] == 2) {
                                //进程已结束
                                $show_form = 0;
                            } else {
                                //审批进程记录
                                $examine = $scrapModel->DB_get_all('approve', 'scrapid,approver,is_adopt,approve_time,remark', array('scrapid' => $scrid, 'is_delete' => 0), '', 'approve_time asc', '');
                                foreach ($examine as $k => $v) {
                                    $tmpuser = $scrapModel->DB_get_one('user', 'pic', ['username' => $v['approver']]);
                                    $examine[$k]['user_pic'] = $tmpuser['pic'];
                                    $examine[$k]['approve_time'] = getHandleMinute($v['approve_time']);
                                    $examine[$k]['approve_name'] = '报废审批';
                                }
                            }
                            $result['scrapinfo'] = $scrInfo;
                        }
                    }
                } else {
                    $result['status'] = 302;
                    $msg['tips'] = '该设备暂不允许报废';
                    $msg['url'] = '';
                    $msg['btn'] = '';
                    $result['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
                    $this->ajaxReturn($result);
                }
            }
            //查询设备信息
            $assInfo = $scrapModel->get_assets_info($assnum);

            $departid = session('departid');
            $departid_arr = explode(',', $departid);
            if (!in_array($assInfo['departid'], $departid_arr)) {
                $result['status'] = 302;
                $msg['tips'] = '您无权操作该部门设备';
                $msg['url'] = '';
                $msg['btn'] = '';
                $result['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result);
            }
            $assInfo['scrapnum'] = 'BF-' . $assInfo['assnum'];
            //报废表单信息
            $scrapinfo['scrapdate'] = getHandleTime(time());
            $scrapinfo['username'] = session('username');

            //组织表单第三部分厂商信息
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $offlineSuppliers = $OfflineSuppliersModel->DB_get_one('assets_factory', 'ols_facid,ols_supid,ols_repid', array('assid' => $assets['assid']));
            $factoryInfo = [];
            $supplierInfo = [];
            $repairInfo = [];
            $offlineSuppliersFields = 'olsid,sup_name,salesman_name,salesman_phone,artisan_name,artisan_phone';
            if ($offlineSuppliers['ols_facid']) {
                $factoryInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_facid']]);
            }
            if ($offlineSuppliers['ols_supid']) {
                $supplierInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_supid']]);
            }
            if ($offlineSuppliers['ols_repid']) {
                $repairInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_repid']]);
            }
            if ($examine) {
                $assInfo['approves'] = $examine;
            }
            $assInfo['factoryInfo'] = $factoryInfo;
            $assInfo['supplierInfo'] = $supplierInfo;
            $assInfo['repairInfo'] = $repairInfo;
            $result['status'] = 1;
            $result['show_form'] = $show_form;
            $result['asArr'] = $assInfo;
            $result['now'] = getHandleTime(time());
            $result['username'] = session('username');
            $this->ajaxReturn($result, 'json');
        }
    }

    /*
     *  报废审批
     */
    public function examine()
    {
        $ScrapModel = new AssetsScrapModel();
        if (IS_POST) {
            $apModel = new \Admin\Model\AssetsScrapModel();
            $result = $apModel->saveExamine();
            $this->ajaxReturn($result);
        } else {
            //查询报废审批是否已开启
            $isOpenApprove = $this->checkApproveIsOpen(C('SCRAP_APPROVE'), session('current_hospitalid'));
            if (!$isOpenApprove) {
                $result['msg'] = "报废审批已关闭";
                $result['status'] = -1;
                $this->ajaxReturn($result, 'JSON');
                exit;
            }
            $scrid = I('get.scrid');
            //查询报废单信息
            $zkInfo = $ScrapModel->DB_get_one('assets_scrap', '*', array('scrid' => $scrid));
            if (!$zkInfo['scrid']) {
                $result['msg'] = "查找不到该设备报废信息";
                $result['status'] = -1;
                $this->ajaxReturn($result, 'JSON');
                exit;
            }
            $is_display = 0;
            $current_approver = explode(',', $zkInfo['current_approver']);
            if ($zkInfo['approve_status'] != '0') {
                $is_display = 0;
            } else if (!in_array(session('username'), $current_approver)) {
                $is_display = 0;
            } else {
                $is_display = 1;
            }

            //查询申请人联系电话
            $tel = $ScrapModel->DB_get_one('user', 'telephone', array('username' => $zkInfo['add_user']));
            $zkInfo['telephone'] = $tel['telephone'];
            //查询审批历史
            $approves = $ScrapModel->DB_get_all('approve', '*', array('scrapid' => $scrid, 'is_delete' => 0, 'approve_class' => 'scrap', 'process_node' => C('SCRAP_APPROVE')), '', 'process_node_level,apprid asc');
            $all_approves = $app_users = $userPic = [];
            if($zkInfo['all_approver']){
                //查询超级管理员
                $is_super = $ScrapModel->DB_get_one('user','username,pic',['is_super'=>1]);
                $all_approver = str_replace(",/".$is_super['username']."/",'',$zkInfo['all_approver']);
                $all_approver = str_replace("/",'',$all_approver);
                $all_approver = explode(',',$all_approver);
                foreach ($all_approver as $k=>$v){
                    $upic = $ScrapModel->DB_get_one('user','username,pic',array('username'=>$v));
                    $app_users[$k]['username'] = $upic['username'];
                    $app_users[$k]['pic'] = $upic['pic'];
                }
                $userPic[$is_super['username']] = $is_super['pic'];
                foreach ($app_users as $k=>$v){
                    $all_approves[$k]['is_adopt'] = 0;
                    $all_approves[$k]['approver'] = $v['username'];
                    $all_approves[$k]['user_pic'] = $v['pic'];
                    $all_approves[$k]['approve_time'] = '';
                    $all_approves[$k]['remark'] = '';
                    //用户头像
                    $userPic[$v['username']] = $v['pic'];
                }
            }
            foreach ($approves as $key => &$value) {
                $all_approves[$key]['is_adopt'] = (int)$value['is_adopt'];
                $all_approves[$key]['approver'] = $value['approver'];
                $all_approves[$key]['approve_time'] = date('Y-m-d H:i',$value['approve_time']);
                $all_approves[$key]['remark'] = $value['remark'] ? $value['remark'] : '无备注';
                $all_approves[$key]['user_pic'] = $userPic[$value['approver']];
            }
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            //查找设备信息
            $asModel = new \Admin\Model\AssetsInfoModel();
            $asArr = $asModel->getAssetsInfo($zkInfo['assid']);
            $asArr = $this->add_ols($asArr);
            //******************************8审批流程显示 start***************************//
            $reModel = new RepairModel();
            $zkInfo = $reModel->get_approves_progress($zkInfo, 'scrapid', 'scrid');
            //**************************************审批流程显示 end*****************************//
            //$asArr = $transferModel->DB_get_one('assets_info', 'assid,departid,assets,assnum,model,opendate,serialnum', array('assid' => $zkInfo['assid'],'is_delete'=>'0'));
            $asArr['department'] = $departname[$asArr['departid']]['department'];
            $result['status'] = 1;
            $result['is_display'] = $is_display;
            $result['asArr'] = $asArr;
            $result['approveDate'] = date('Y-m-d H:i');
            $result['approveUser'] = session('username');
            $result['approves'] = $all_approves;
            $result['scrap'] = $zkInfo;
            $this->ajaxReturn($result, 'json');
        }
    }


    /**
     * 报废结果列表
     */
    public function getResultList()
    {
        $departids = session('departid');
        $asModel = new AssetsInfoModel();
        if (IS_POST) {
            $scrapModel = new AssetsScrapModel();
            $result = $scrapModel->get_result_lists();
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('get.action');
            if ($action == 'detail') {
            } else {
                $this->assign('url', get_url());
                $this->display();
            }
        }
    }

    /*
    报废处置页面
     */
    public function result()
    {
        if (IS_POST) {
            $action = I('post.action');
            $ScrapModel = new \Admin\Model\AssetsScrapModel();
            switch ($action) {
                case 'upload':
                    $result = $ScrapModel->uploadReport(C('UPLOAD_DIR_REPORT_SCRAP_NAME'));
                    if ($result['status'] == 1) {
                        //保存数据库
                        $data['scrid'] = I('post.scrid');
                        $data['file_name'] = $result['formerly'];
                        $data['save_name'] = $result['title'];
                        $data['file_type'] = $result['file_type'];
                        $data['file_size'] = $result['file_size'];
                        $data['file_url'] = $result['file_url'];
                        $data['add_user'] = session('username');
                        $data['add_time'] = date('Y-m-d H:i:s');
                        $ScrapModel->insertData('assets_scrap_report', $data);
                    }
                    break;
                case 'del_file':
                    $result = $ScrapModel->updateData('assets_scrap_report', array('is_delete' => 1), array('file_id' => I('post.file_id')));
                    if ($result) {
                        $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功！'));
                    } else {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '删除失败！'));
                    }
                    return;
                case 'uploadFile':
                    $result = $ScrapModel->uploadfile();
                    break;
                default:
                    $result = $ScrapModel->saveResult();
                    break;
            }
            $this->ajaxReturn($result, 'json');
        } else {
            $scrid = I('get.scrid');
            $scrapModel = new AssetsScrapModel();
            $scrapInfo = $scrapModel->DB_get_one('assets_scrap', '*', array('scrid' => $scrid));
            if (!$scrapInfo) {
                $this->assign('tips', '查找不到该设备报废信息');
                $this->assign('btn', '返回列表页');
                $this->assign('url', $this->progress_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $asArr = $scrapModel->DB_get_one('assets_info', 'assid,assets,assnum,model,serialnum,opendate', array('assid' => $scrapInfo['assid'], 'is_delete' => '0'));
            $this->assign('asArr', $asArr);
            $this->assign('username', session('username'));
            $this->assign('date', date("Y-m-d", time()));
            $this->assign('scrapinfo', $scrapInfo);
            $this->assign('url', get_url());
            $this->display();
        }
    }

    /**
     * 报废查询列表
     */
    public function getScrapList()
    {
        $ScrapModel = new AssetsScrapModel();
        $action = I('GET.action');
        switch ($action) {
            case 'showScrap':
                $result = $this->showScrap();
                break;
            default:
                $result = $ScrapModel->get_scrap_lists();
                break;
        }
        $this->ajaxReturn($result, 'json');
    }

    /**
     * Notes: 报废明细
     */
    private function showScrap()
    {
        $ScrapModel = new AssetsScrapModel();
        $scrid = I('get.scrid');
        //查询该报废单是否存在
        $scrapInfo = $ScrapModel->DB_get_one('assets_scrap', '*', array('scrid' => $scrid));
        if (!$scrapInfo) {
            $result['status'] = -1;
            $result['msg'] = '查找不到该报废单信息';
            $this->ajaxReturn($result, 'json');
        } else {
            $subsidiary = $ScrapModel->getSubsidiaryBasic($scrid);
        }
        $assid = $scrapInfo['assid'];
        //设备基础信息
        $asModel = new \Admin\Model\AssetsInfoModel();
        $assetsinfo = $asModel->getAssetsInfo($assid);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assetsinfo['buy_price'] = '***';
        }
        $departname = array();
        $catname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $assetsinfo['opendate'] = HandleEmptyNull($assetsinfo['opendate']);
        $assetsinfo['guarantee_date'] = HandleEmptyNull($assetsinfo['guarantee_date']);
        $assetsinfo['storage_date'] = HandleEmptyNull($assetsinfo['storage_date']);
        $assetsinfo['department'] = $departname[$assetsinfo['departid']]['department'];
        $assetsinfo['category'] = $catname[$assetsinfo['catid']]['category'];

        if (time() <= strtotime($assetsinfo['guarantee_date'])) {
            $assetsinfo['guaranteeStatus'] = '保修期内';
        } else {
            $assetsinfo['guaranteeStatus'] = '<span style="color: red;">已过保修期</span>';
        }
        //判断有无相关文件
        $files = $ScrapModel->DB_get_one('scrap_file', 'file_url', array('scrid' => $scrid));
        if ($files) {
            $empty = 0;
        } else {
            $empty = 1;
        }
        //审批进程记录
        $approves = $ScrapModel->DB_get_all('approve', 'scrapid,approver,is_adopt,approve_time,remark', array('scrapid' => $scrid), '', 'process_node_level,approve_time asc', '');
        $all_approves = $app_users = $userPic = [];
        if ($scrapInfo['all_approver']) {
            //查询超级管理员
            $is_super = $ScrapModel->DB_get_one('user', 'username,pic', ['is_super' => 1]);
            $all_approver = str_replace(",/" . $is_super['username'] . "/", '', $scrapInfo['all_approver']);
            $all_approver = str_replace("/", '', $all_approver);
            $all_approver = explode(',', $all_approver);
            foreach ($all_approver as $k => $v) {
                $upic = $ScrapModel->DB_get_one('user', 'username,pic', array('username' => $v));
                $app_users[$k]['username'] = $upic['username'];
                $app_users[$k]['pic'] = $upic['pic'];
            }
            $userPic[$is_super['username']] = $is_super['pic'];
            foreach ($app_users as $k => $v) {
                $all_approves[$k]['is_adopt'] = 0;
                $all_approves[$k]['approver'] = $v['username'];
                $all_approves[$k]['user_pic'] = $v['pic'];
                $all_approves[$k]['approve_time'] = '';
                $all_approves[$k]['remark'] = '';
                //用户头像
                $userPic[$v['username']] = $v['pic'];
            }
        }
        foreach ($approves as $key => &$value) {
            $all_approves[$key]['is_adopt'] = (int)$value['is_adopt'];
            $all_approves[$key]['approver'] = $value['approver'];
            $all_approves[$key]['approve_time'] = date('Y-m-d H:i', $value['approve_time']);
            $all_approves[$key]['remark'] = $value['remark'] ? $value['remark'] : '无备注';
            $all_approves[$key]['user_pic'] = $userPic[$value['approver']];
        }

        //组织表单第三部分厂商信息
        $OfflineSuppliersModel = new OfflineSuppliersModel();
        $offlineSuppliers = $ScrapModel->DB_get_one('assets_factory', 'ols_facid,ols_supid,ols_repid', array('assid' => $assetsinfo['assid']));
        $factoryInfo = [];
        $supplierInfo = [];
        $repairInfo = [];
        $offlineSuppliersFields = 'olsid,sup_name,salesman_name,salesman_phone,artisan_name,artisan_phone';
        if ($offlineSuppliers['ols_facid']) {
            $factoryInfo = $ScrapModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_facid']]);
            $factoryFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_facid']);

        }
        if ($offlineSuppliers['ols_supid']) {
            $supplierInfo = $ScrapModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_supid']]);
            $supplierFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_supid']);
        }
        if ($offlineSuppliers['ols_repid']) {
            $repairInfo = $ScrapModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_repid']]);
            $repairFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_repid']);

        }
        $assetsinfo['factoryInfo'] = $factoryInfo;
        $assetsinfo['supplierInfo'] = $supplierInfo;
        $assetsinfo['repairInfo'] = $repairInfo;
        $scrapInfo['asArr'] = $assetsinfo;
        $isOpenApprove = $this->checkApproveIsOpen(C('SCRAP_APPROVE'), session('current_hospitalid'));
        $scrapInfo['approves'] = $all_approves;
        $scrapInfo['status'] = 1;
        return $scrapInfo;
    }
}
