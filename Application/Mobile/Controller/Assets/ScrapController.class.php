<?php

namespace Mobile\Controller\Assets;

use Admin\Controller\Tool\ToolController;
use Mobile\Controller\Login\IndexController;
use Mobile\Model\AssetsInfoModel;
use Mobile\Model\AssetsScrapModel;
use Mobile\Model\RepairModel;
use Mobile\Model\WxAccessTokenModel;

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
        $departids = session('departid');
        $asModel = new AssetsInfoModel();
        if (IS_POST) {
            $scrapModel = new AssetsScrapModel();
            $result = $scrapModel->get_Apply_List();
            $this->ajaxReturn($result, 'json');
        } else {
            //查询分类信息数据
            $cates = $asModel->get_all_category('scrap');
            $cates = getTree('parentid', 'catid', $cates, 0);

            //查询科室信息
            $departs = $asModel->get_all_department($departids,C('ASSETS_STATUS_USE').','.C('ASSETS_STATUS_SCRAP_ON'),C('NO_STATUS'));
            $departs = getTree('parentid', 'departid', $departs, 0);
            array_multisort(array_column($cates,'assetssum'),SORT_DESC,$cates);
            $this->assign('cates', $cates);
            $this->assign('departs', $departs);
            $this->assign('url', get_url());
            $jssdk = new WxAccessTokenModel();
            $signPackage = $jssdk->GetSignPackage();
            $this->signPackage = $signPackage;
            $this->display();
        }
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
            $scrid = I('get.scrid');
            $this->assign('show_form',1);
            if($scrid){
                $scrap_data = $scrapModel->DB_get_one('assets_scrap', '*', array('scrid' => $scrid));
                $assid = $scrap_data['assid'];
                $asInfo = $scrapModel->DB_get_one('assets_info','assid,assnum',array('assid'=>$assid,'is_delete'=>'0'));
                $assnum = $asInfo['assnum'];
                $this->assign('examine_is_display', 1);
                if($scrap_data['retrial_status'] == 3 || $scrap_data['retrial_status'] == 2){
                    //进程已结束
                    $this->assign('show_form',0);
                    $this->assign('zkInfo',$scrap_data);
                }else{
                    //审批进程记录
                    $examine = $scrapModel->DB_get_all('approve', 'scrapid,approver,is_adopt,approve_time,remark', array('scrapid' => $scrid), '', 'approve_time asc', '');
                    foreach ($examine as $k => $v) {
                        $examine[$k]['approve_time'] = getHandleDate($v['approve_time']);
                        $examine[$k]['approve_name'] = '报废审批';
                    }
                    $this->assign('approves', $examine);
                }
            }else{
                $assnum = I('get.assnum');
            }
            $action = I('get.action');
            if (!$assnum) {
                $this->assign('tips', '参数非法！');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->add_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            if ($action == 'brcode') {
//                //二维码扫码进来，验证是否可以申请转科
//                $this->scanQRcode_transfer();
            }
            //查询设备信息
            $assInfo = $scrapModel->get_assets_info($assnum);
            if (!$assInfo['assid']) {
                $this->assign('tips', '查找不到编码为 ' . $assnum . ' 的设备信息');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->add_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $departid = session('departid');
            $departid_arr = explode(',', $departid);
            if (!in_array($assInfo['departid'], $departid_arr)) {
                $this->assign('tips', '权限不足，无法报废其他科室的设备');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->add_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $assInfo['scrapnum'] = 'BF-' . $assInfo['assnum'];
            //报废表单信息
            $scrapinfo['scrapdate'] = getHandleTime(time());
            $scrapinfo['username'] = session('username');
            //如果是重审申请从数据库中获取原因
            if ($scrid) {
                $scrapinfo['scrap_reason'] = $scrap_data['scrap_reason'];
                $scrapinfo['scrid'] = $scrid;
                $scrapinfo['type'] = 'edit';
            }
            $this->assign('now', getHandleTime(time()));
            $this->assign('username', session('username'));
            $this->assign('asArr', $assInfo);
            $this->assign('scrapinfo', $scrapinfo);
            $this->assign('url', get_url());
            $this->display();
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
                $this->assign('tips', '报废审批已关闭');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->approve_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $scrid = I('get.scrid');
            //查询报废单信息
            $zkInfo = $ScrapModel->DB_get_one('assets_scrap', '*', array('scrid' => $scrid));
            if (!$zkInfo['scrid']) {
                $this->assign('tips', '查找不到该设备报废信息');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->approve_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $current_approver = explode(',', $zkInfo['current_approver']);
            if ($zkInfo['approve_status'] != '0') {
                $this->assign('is_display', 0);
            } else if (!in_array(session('username'), $current_approver)) {
                $this->assign('is_display', 0);
            } else {
                $this->assign('is_display', 1);
            }

            //查询申请人联系电话
            $tel = $ScrapModel->DB_get_one('user', 'telephone', array('username' => $zkInfo['add_user']));
            $zkInfo['telephone'] = $tel['telephone'];
            //查询审批历史
            $apps = $ScrapModel->DB_get_all('approve', '*', array('scrapid' => $scrid, 'is_delete' => 0, 'approve_class' => 'scrap', 'process_node' => C('SCRAP_APPROVE')), '', 'process_node_level,apprid asc');
            foreach ($apps as $k => $v) {
                $apps[$k]['approve_time'] = getHandleTime($v['approve_time']);
            }
            //查询重审前审批历史
            $not_apps = $ScrapModel->DB_get_all('approve', '*', array('scrapid' => $scrid, 'is_delete' => 1, 'approve_class' => 'scrap', 'process_node' => C('SCRAP_APPROVE')), '', 'process_node_level,apprid asc');
            foreach ($not_apps as $k => $v) {
                $not_apps[$k]['approve_time'] = getHandleTime($v['approve_time']);
            }
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            //查找设备信息
            $asModel = new \Admin\Model\AssetsInfoModel();
            $asArr = $asModel->getAssetsInfo($zkInfo['assid']);
            //******************************8审批流程显示 start***************************//
            $reModel = new RepairModel();
            $zkInfo = $reModel->get_approves_progress($zkInfo,'scrapid','scrid');
            //**************************************审批流程显示 end*****************************//
            //$asArr = $transferModel->DB_get_one('assets_info', 'assid,departid,assets,assnum,model,opendate,serialnum', array('assid' => $zkInfo['assid'],'is_delete'=>'0'));
            $asArr['department'] = $departname[$asArr['departid']]['department'];
            $this->assign('zkInfo', $zkInfo);
            $this->assign('approves', $apps);
            $this->assign('not_apps', $not_apps);
            $this->assign('assets', $asArr);
            $this->assign('scrid', $scrid);
            $this->assign('date', date('Y-m-d'));
            $this->assign('time', date('Y-m-d H:i'));
            $this->assign('username', session('username'));
            $this->assign('url', get_url());
            $this->display();
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
            if($action == 'detail'){

            }else{
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
            $asArr = $scrapModel->DB_get_one('assets_info', 'assid,assets,assnum,model,serialnum,opendate', array('assid' => $scrapInfo['assid'],'is_delete'=>'0'));
            $this->assign('asArr', $asArr);
            $this->assign('username', session('username'));
            $this->assign('date', date("Y-m-d",time()));
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
        if (IS_POST) {
            $result = $ScrapModel->get_scrap_lists();
            $this->ajaxReturn($result, 'json');
        }else{
            $action = I('GET.action');
            switch($action){
                case 'showScrap':
                    $this->showScrap();
                    $this->display('showScrap');
                    break;
                default:
                    $this->assign('getScrapList',get_url());
                    $this->display();
                    break;
            }
        }
    }

    /**
     * Notes: 报废明细
     */
    private function showScrap()
    {
        $ScrapModel = new AssetsScrapModel();
        $scrid = I('get.scrid');
        //查询该报废单是否存在
        $scrapInfo = $ScrapModel->DB_get_one('assets_scrap','*',array('scrid'=>$scrid));
        if(!$scrapInfo){
            $this->assign('tips', '查找不到该报废单信息');
            $this->assign('btn', '返回报废查询列表');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->getScrapList_url);
            $this->display('Pub/Notin/fail');
            exit;
        }else{
            $subsidiary = $ScrapModel->getSubsidiaryBasic($scrid);
            $this->assign('subsidiary',$subsidiary);
        }
        $assid = $scrapInfo['assid'];
        //设备基础信息
        $fields = 'A.assid,A.assnum,A.assets,A.catid,A.departid,A.model,A.opendate,A.residual_value,A.buy_price,A.guarantee_date,A.storage_date,B.factory';
        $join[0] = 'LEFT JOIN sb_assets_factory AS B ON A.afid = B.afid';
        $assetsinfo = $ScrapModel->DB_get_one_join('assets_info','A',$fields,$join,array('A.assid'=>$assid,'A.is_delete'=>'0'));
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assetsinfo['buy_price'] = '***';
        }
        $departname = array();
        $catname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $assetsinfo['opendate']=HandleEmptyNull($assetsinfo['opendate']);
        $assetsinfo['guarantee_date']=HandleEmptyNull($assetsinfo['guarantee_date']);
        $assetsinfo['storage_date']=HandleEmptyNull($assetsinfo['storage_date']);
        $assetsinfo['department'] = $departname[$assetsinfo['departid']]['department'];
        $assetsinfo['category'] = $catname[$assetsinfo['catid']]['category'];

        if (time() <= strtotime($assetsinfo['guarantee_date'])) {
            $assetsinfo['guaranteeStatus'] = '保修期内';
        } else {
            $assetsinfo['guaranteeStatus'] = '<span style="color: red;">已过保修期</span>';
        }
        //判断有无相关文件
        $files = $ScrapModel->DB_get_one('scrap_file','file_url',array('scrid'=>$scrid));
        if($files){
            $empty = 0;
        }else{
            $empty = 1;
        }
        //审批进程记录
        $examine = $ScrapModel->DB_get_all('approve','scrapid,approver,is_adopt,approve_time,remark',array('scrapid'=>$scrid),'','process_node_level,approve_time asc','');
        foreach ($examine as $k =>$v){
            $examine[$k]['approve_time'] = getHandleDate($v['approve_time']);
            $examine[$k]['approve_name'] = '报废审批';
        }

        $this->assign('scrid',$scrid);
        $this->assign('approves',$examine);
        $this->assign('empty',$empty);
        $this->assign('asArr',$assetsinfo);
        $this->assign('scrapinfo',$scrapInfo);

        $isOpenApprove = $this->checkApproveIsOpen(C('SCRAP_APPROVE'),session('current_hospitalid'));
        $this->assign('isOpenApprove',$isOpenApprove);
    }
}