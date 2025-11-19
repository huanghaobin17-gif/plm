<?php

namespace Admin\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\AssetsScrapModel;
use Admin\Model\AssetsTransferModel;
use Admin\Model\AssetsInfoModel;
use Admin\Model\CommonModel;
use Admin\Model\ModuleModel;
use Admin\Model\UserModel;
use Common\Weixin\Weixin;

class TransferController extends CheckLoginController
{
    //转科申请列表
    public function getList()
    {
        if (IS_POST) {
            $transModel = new AssetsTransferModel();
            $result = $transModel->getAssetsLists();
            $this->ajaxReturn($result, 'json');
        } else {
            $asModel = new AssetsInfoModel();
            $users = $asModel->getUser();
            $this->assign('users', $users);
            $this->assign('getList', get_url());
            $this->display();
        }
    }

    //转科操作（加载模板保存数据）
    public function add()
    {
        //var_dump($_POST);die;
        $transModel = new AssetsTransferModel();
        if (IS_POST) {
            $atid = I('post.atid');
            if($atid){
                $type = I('post.type');
                if($type == 'end'){
                    //结束进程
                    $result = $transModel->endTransfer();
                }else{
                    //申请重审转科单
                    $result = $transModel->updateTransfer();
                }
            }else{
                $result = $transModel->addTransfer();
            }
            $this->ajaxReturn($result, 'JSON');
        } else {
            //echo 123;die;
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $atid = I('GET.atid');
            //echo $atid;die;
            if($atid){
                $transInfo = $transModel->DB_get_one('assets_transfer','atid,assid,transfernum,tranout_departid,tranout_departrespon,tranin_departid,tranin_departrespon,transfer_date,tran_docnum,tran_reason,address',array('atid'=>$atid));
                $transInfo['tranout_department'] = $departname[$transInfo['tranout_departid']]['department'];
                $transInfo['tranoin_department'] = $departname[$transInfo['tranin_departid']]['department'];
                $assids = $transInfo['assid'];
                $this->assign('rightdepart', '1');
                $this->assign('transInfo', $transInfo);
            }else{
                $this->assign('rightdepart', '0');
                $assids = I('GET.assid');
            }
            $assids = trim($assids, ',');

            if (!$assids) {
                $this->error('参数非法！');
            }
            $assids_arr=explode(',',$assids);
            $where = ' assid IN (' . $assids . ')';
            $asArr = $transModel->DB_get_all('assets_info', 'assid,hospital_id,assnum,assets,model,departid', $where, '', '', '');

            foreach ($asArr as $k => $v) {
                $asArr[$k]['department_name'] = $departname[$v['departid']]['department'];
                $asArr[$k]['departrespon'] = $departname[$v['departid']]['departrespon'];
            }
            $userid = session('userid');
            $uInfo = $transModel->DB_get_one('user', 'username', array('userid' => $userid));
            $this->assign('department_name', $asArr[0]['department_name']);
            $this->assign('departrespon', $asArr[0]['departrespon']);
            $this->assign('out_departid', $asArr[0]['departid']);
            $this->assign('username', $uInfo['username']);
            $this->assign('today', getHandleTime(time()));

            $subsidiary = $transModel->getAssetsSubsidiary($assids_arr);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('asArr', $asArr);
            $this->assign('assids', $assids);
            $this->display();
        }
    }

    //批量转科操作（加载模板保存数据）
    public function batchAdd()
    {

        if (IS_POST) {
            $transModel = new AssetsTransferModel();
            $result = $transModel->addTransfer();
            $this->ajaxReturn($result, 'JSON');
        } else {
            //var_dump($_GET);die;
            $assids = I('GET.assid');
            $assids = trim($assids, ',');
            if (!$assids) {
                echo '操作错误';
                exit;
            }
            $assids_arr=explode(',',$assids);
            $transModel = new AssetsTransferModel();
            $where['assid'] = ['in',$assids];
            $asArr = $transModel->DB_get_all('assets_info', 'assid,hospital_id,assnum,assets,model,departid', $where, '', '', '');
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            foreach ($asArr as $k => $v) {
                $asArr[$k]['department_name'] = $departname[$v['departid']]['department'];
                $asArr[$k]['departrespon'] = $departname[$v['departid']]['departrespon'];
            }
            $userid = session('userid');
            $uInfo = $transModel->DB_get_one('user', 'username', array('userid' => $userid), '');
            $this->assign('department_name', $asArr[0]['department_name']);
            $this->assign('departrespon', $asArr[0]['departrespon']);
            $this->assign('out_departid', $asArr[0]['departid']);
            $subsidiary = $transModel->getAssetsSubsidiary($assids_arr);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('username', $uInfo['username']);
            $this->assign('today', getHandleTime(time()));
            $this->assign('asArr', $asArr);
            $this->assign('assids', $assids);
            $this->display();
        }
    }

    //转科审核列表
    public function examine()
    {
        if (IS_POST) {
            $transModel = new AssetsTransferModel();
            $result = $transModel->getExamines();
            $this->ajaxReturn($result);
        } else {
            //查询转科审批是否已开启
            $isOpenApprove = $this->checkApproveIsOpen(C('TRANSFER_APPROVE'),session('job_hospitalid'));
            if (!$isOpenApprove) {
                $this->assign('errmsg', '转科审批未开启，如需开启，请联系管理员！');
                $this->display('Public/error');
            } else {
                $action = I('GET.action');
                switch ($action) {
                    //详情页面
                    case 'showDetails':
                        $this->showDetails();
                        break;
                    default:
                        //审批列表页面
                        $this->showexamineList();
                        break;
                }
            }
        }
    }

    //详情页面
    private function showDetails(){
        $transfernum = I('GET.transNum');
        $assid = I('GET.assid');
        if (!$transfernum || !$assid) {
            $this->error('参数非法！');
        }
        //查看申请转科设备信息
        $asModel = new AssetsTransferModel();
        $join[0] = ' LEFT JOIN __ASSETS_INFO__ ON A.assid = __ASSETS_INFO__.assid';
        $where['A.transfernum'] = $transfernum;
        $where['A.assid'] = $assid;
        $fields = 'A.*,sb_assets_info.assnum,sb_assets_info.assorignum,sb_assets_info.assets,sb_assets_info.model,sb_assets_info.catid';
        $zkInfo = $asModel->DB_get_one_join('assets_transfer', 'A', $fields, $join, $where);
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
        $zkInfo['tranout_departrespon'] = $departname[$zkInfo['tranout_departid']]['departrespon'];
        $zkInfo['tranin_depart_name'] = $departname[$zkInfo['tranin_departid']]['department'];
        $zkInfo['tranin_departrespon'] = $departname[$zkInfo['tranin_departid']]['departrespon'];
        //查询审批历史(申请重审已删除的)
        $old_apps = $asModel->DB_get_all('approve', '*', array('atid' => $zkInfo['atid'],'is_delete'=>1, 'approve_class' => 'transfer', 'process_node' => C('TRANSFER_APPROVE')), '', 'process_node_level,apprid asc');
        foreach($old_apps as $k => $v){
            $old_apps[$k]['approve_time'] = getHandleDate($v['approve_time']);
        }
        //查询审批历史
        $apps = $asModel->DB_get_all('approve', '*', array('atid' => $zkInfo['atid'],'is_delete'=>0, 'approve_class' => 'transfer', 'process_node' => C('TRANSFER_APPROVE')), '', 'process_node_level,apprid asc');
        foreach($apps as $k => $v){
            $apps[$k]['approve_time'] = getHandleDate($v['approve_time']);
        }
        //******************************审批流程显示 start***************************//
        $zkInfo = $asModel->get_approves_progress($zkInfo,'atid','atid');
        //**************************************审批流程显示 end*****************************//
        $subsidiary=$asModel->getSubsidiaryBasic($zkInfo['atid']);
        $this->assign('zkInfo', $zkInfo);
        $this->assign('old_apps', $old_apps);
        $this->assign('approves', $apps);
        $this->assign('subsidiary', $subsidiary);
        $this->display('showTransfer');
    }

    //审批列表页面
    private function showexamineList(){
        $asModel = new AssetsInfoModel();
        //查询是否开启了转科审批
        $isOpenApprove = $this->checkApproveIsOpen(C('TRANSFER_APPROVE'), session('job_hospitalid'));
        $this->assign('isOpenApprove', $isOpenApprove['status']);
        $users = $asModel->getUser();
        $this->assign('users', $users);
        $this->assign('examine', get_url());
        $this->display();
    }

    //转科审批操作
    public function approval()
    {
        //查询转科审批是否已开启
        $isOpenApprove = $this->checkApproveIsOpen(C('TRANSFER_APPROVE'),session('current_hospitalid'));
        if (!$isOpenApprove) {
            $this->error('转科审批已关闭！');
            exit;
        }
        if (IS_POST) {
            $apModel = new AssetsTransferModel();
            $result = $apModel->saveApprove();
            $this->ajaxReturn($result);
        } else {
            $transfernum = I('GET.transNum');
            $assid = I('GET.assid');
            if (!$transfernum || !$assid) {
                $this->error('参数非法！');
            }
            //查看申请转科设备信息
            $apModel = new AssetsTransferModel();
            $join[0] = ' LEFT JOIN __ASSETS_INFO__ ON A.assid = __ASSETS_INFO__.assid';
            $where['A.transfernum'] = $transfernum;
            $where['A.assid'] = $assid;
            $fields = 'A.*,sb_assets_info.assnum,sb_assets_info.assorignum,sb_assets_info.assets,sb_assets_info.model,sb_assets_info.catid';
            $zkInfo = $apModel->DB_get_one_join('assets_transfer', 'A', $fields, $join, $where);
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
            $zkInfo['tranout_departrespon'] = $departname[$zkInfo['tranout_departid']]['departrespon'];
            $zkInfo['tranin_depart_name'] = $departname[$zkInfo['tranin_departid']]['department'];
            $zkInfo['tranin_departrespon'] = $departname[$zkInfo['tranin_departid']]['departrespon'];
            //查询审批历史(申请重审已删除的)
            $old_apps = $apModel->DB_get_all('approve', '*', array('atid' => $zkInfo['atid'],'is_delete'=>1, 'approve_class' => 'transfer', 'process_node' => C('TRANSFER_APPROVE')), '', 'process_node_level,apprid asc');
            foreach($old_apps as $k => $v){
                $old_apps[$k]['approve_time'] = getHandleDate($v['approve_time']);
            }
            //查询审批历史
            $approves = $apModel->DB_get_all('approve', '*', array('atid' => $zkInfo['atid'],'is_delete'=>0, 'approve_class' => 'transfer', 'process_node' => C('TRANSFER_APPROVE')), '', 'process_node_level,apprid asc');
            foreach($approves as $k => $v){
                $approves[$k]['approve_time'] = getHandleDate($v['approve_time']);
            }
            $canApprove = false;
            if($zkInfo['approve_status']==C('OUTSIDE_STATUS_APPROVE')){
                if ($zkInfo['current_approver']) {
                    $current_approver=explode(',',$zkInfo['current_approver']);
                    $current_approver_arr=[];
                    foreach($current_approver as &$current_approver_value){
                        $current_approver_arr[$current_approver_value]=true;
                    }
                    if($current_approver_arr[session('username')]){
                        $canApprove = true;
                    }
                }
            }
            //******************************审批流程显示 start***************************//
            $zkInfo = $apModel->get_approves_progress($zkInfo,'atid','atid');
            //**************************************审批流程显示 end*****************************//
            $subsidiary=$apModel->getSubsidiaryBasic($zkInfo['atid']);
            $this->assign('zkInfo', $zkInfo);
            $this->assign('approves', $approves);
            $this->assign('old_apps', $old_apps);
            $this->assign('canApprove', $canApprove);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('username', session('username'));
            $this->assign('currenDate', getHandleTime(time()));
            $this->display();
        }
    }

    //转科验收列表
    public function checkLists()
    {
        if (IS_POST) {
            $transModel = new AssetsTransferModel();
            $action = I('post.action');
            switch ($action){
                case 'batchPrint':
                    $this->batchPrintReport();
                    break;
                default:
                    $result = $transModel->getcheckLists();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                //详情页面
                case 'showDetails':
                    $this->showDetails();
                    break;
                case 'printReport':
                    $this->printReport();
                    break;
                case 'uploadReport':
                    //上传、查看报告
                    $this->uploadReport();
                    break;
                default:
                    //转科验收列表页面
                    $this->showCheckList();
                    break;
            }
        }
    }

    //转科验收列表页面
    private function showCheckList(){
        $asModel = new AssetsInfoModel();
        $users = $asModel->getUser();
        $this->assign('users', $users);
        $this->assign('checkLists', get_url());
        $this->display();
    }

    /**
     * Notes: 打印报告
     */
    private function printReport()
    {
        $atid = I('get.atid');
        //echo $atid;die;
        $where['A.atid'] = $atid;
        //查询转科设备及单号信息
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.*,B.assid,B.assets,B.assnum,B.model,B.serialnum,B.brand,B.unit,B.catid,B.afid,B.guarantee_date,A.complete_approver,B.departid";
        $assModel = new AssetsInfoModel();
        $zkInfo = $assModel->DB_get_one_join('assets_transfer','A',$fields,$join,$where);
        //查询申请人电话
        $tel = $assModel->DB_get_one('user','telephone',array('username'=>$zkInfo['applicant_user']));
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        $zkInfo['telephone'] = $SM4->decrypt($tel['telephone']);
        //获取转出科室签名
        $tranout_autograph = $assModel->get_departid_autograph($zkInfo['tranout_departid']);
        //转入科室签名
        $tranin_autograph = $assModel->get_departid_autograph($zkInfo['tranin_departid']);
        //设备科签名
        $departautograph = $assModel->get_assets_autograph();

        $this->assign('tranout_autograph',$tranout_autograph);
        $this->assign('tranin_autograph',$tranin_autograph);
        $this->assign('departautograph',$departautograph);
        $this->assign('tranout_autograph_time',substr($zkInfo['applicant_time'],0,10));
        $this->assign('tranin_autograph_time',substr($zkInfo['check_time'],0,10));
        $this->assign('departautograph_time',substr($zkInfo['transfer_date'],0,10));
        //查询设备出厂厂家
        $fac = $assModel->DB_get_one('assets_factory','factory',array('afid'=>$zkInfo['afid']));
        $zkInfo['factory'] = $fac['factory'];
        $departname = $catname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
        $zkInfo['tranout_departrespon'] = $departname[$zkInfo['tranout_departid']]['departrespon'];
        $zkInfo['tranin_depart_name'] = $departname[$zkInfo['tranin_departid']]['department'];
        $zkInfo['tranin_departrespon'] = $departname[$zkInfo['tranin_departid']]['departrespon'];
        $zkInfo['category'] = $catname[$zkInfo['catid']]['category'];
        if($zkInfo['is_check'] != -1){
            //查询审批信息
            $approves = $assModel->DB_get_all('approve','apprid,approver,approve_time,is_adopt,remark',array('atid'=>$zkInfo['atid'],'is_delete'=>0),'','apprid asc');
            $this->assign('approves',$approves);
        }
        //查询随转附属设备
        $join_f = "LEFT JOIN sb_assets_info AS B ON A.subsidiary_assid = B.assid";
        $fields_f = "A.*,B.assets,B.assnum,B.model,B.brand,B.serialnum";
        $details = $assModel->DB_get_all_join('assets_transfer_detail','A',$fields_f,$join_f,array('A.atid'=>$atid),'','','');
        $title = $assModel->getprinttitle('assets','transfer_template');
        $this->assign('title', $title);
        $this->assign('zkInfo',$zkInfo);
        $this->assign('details',$details);
        $this->assign('date',date('Y-m-d'));
        $baseSetting = array();
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
        $this->display('printReport');
    }

    /**
     * Notes: 打印报告
     */
    private function batchPrintReport()
    {
        $atid = I('post.atid');
        $where['A.atid'] = array('in',$atid);
        //查询转科设备及单号信息
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid LEFT JOIN sb_user AS C ON C.username = A.applicant_user";
        $fields = "A.*,B.assid,B.assets,B.assnum,B.model,B.serialnum,B.brand,B.unit,B.catid,B.afid,B.guarantee_date,C.telephone,B.departid";
        $assModel = new AssetsInfoModel();
        $zkInfo = $assModel->DB_get_all_join('assets_transfer','A',$fields,$join,$where,'','','');
        $departname = $catname = array();
        $html = '';
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        $userModel = new UserModel();
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        //查询设备出厂厂家
        foreach($zkInfo as $k=>$v){
            //查询设备出厂厂家
            $fac = $assModel->DB_get_one('assets_factory','factory',array('afid'=>$v['afid']));
            $zkInfo[$k]['factory'] = $fac['factory'];
            $zkInfo[$k]['tranout_depart_name'] = $departname[$v['tranout_departid']]['department'];
            $zkInfo[$k]['tranout_departrespon'] = $departname[$v['tranout_departid']]['departrespon'];
            $zkInfo[$k]['tranin_depart_name'] = $departname[$v['tranin_departid']]['department'];
            $zkInfo[$k]['tranin_departrespon'] = $departname[$v['tranin_departid']]['departrespon'];
            $zkInfo[$k]['category'] = $catname[$v['catid']]['category'];
            $zkInfo[$k]['telephone'] = strlen($zkInfo[$k]['telephone'])>12?$SM4->decrypt($zkInfo[$k]['telephone']):$zkInfo[$k]['telephone'];
            if($v['is_check'] != -1){
                //查询审批信息
                $approves = $assModel->DB_get_all('approve','apprid,approver,approve_time,is_adopt,remark',array('atid'=>$v['atid'],'is_delete'=>0),'','apprid asc');
                $this->assign('approves',$approves);
            }
            //查询随转附属设备
            $join_f = "LEFT JOIN sb_assets_info AS B ON A.subsidiary_assid = B.assid";
            $fields_f = "A.*,B.assets,B.assnum,B.model,B.brand,B.serialnum";
            $details = $assModel->DB_get_all_join('assets_transfer_detail','A',$fields_f,$join_f,array('A.atid'=>$v['atid']),'','','');
            $title = $assModel->getprinttitle('repair','transfer_template');
            //获取转出科室签名
            $tranout_autograph = $assModel->get_departid_autograph($zkInfo[$k]['tranout_departid']);
            //转入科室签名
            $tranin_autograph = $assModel->get_departid_autograph($zkInfo[$k]['tranin_departid']);
            //设备科签名
            $departautograph = $assModel->get_assets_autograph();
            $this->assign('tranout_autograph',$tranout_autograph);
            $this->assign('tranin_autograph',$tranin_autograph);
            $this->assign('departautograph',$departautograph);
            $this->assign('tranout_autograph_time',substr($zkInfo[$k]['applicant_time'],0,10));
            $this->assign('tranin_autograph_time',substr($zkInfo[$k]['check_time'],0,10));
            $this->assign('departautograph_time',substr($zkInfo[$k]['transfer_date'],0,10));
            $this->assign('title', $title);
            $this->assign('zkInfo',$zkInfo[$k]);
            $this->assign('details',$details);
            $this->assign('date',date('Y-m-d'));
            $marget_top = ($k+2)%2 == 0 ? 0 : 10;
            $this->assign('marget_top',$marget_top);
            $baseSetting = array();
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
            $html .= $this->display('batch_print_report');
        }
        echo $html;exit;
    }

    /**
     * Notes: 上传、查看报告
     */
    private function uploadReport()
    {
        $atid = I('get.atid');
        //查询对应的转科报告
        $scrapModel = new AssetsTransferModel();
        $files = $scrapModel->DB_get_all('assets_transfer_report','*',array('atid'=>$atid,'is_delete'=>0));
        foreach ($files as $k => $v) {
            $files[$k]['suffix'] = substr(strrchr($v['file_url'], '.'), 1);
            switch ($files[$k]['suffix']) {
                //是图片类型
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'bmp':
                case 'gif':
                    $files[$k]['type'] = 1;
                    break;
                //pdf类型
                case 'pdf':
                    $files[$k]['type'] = 2;
                    break;
                //文档类型
                case 'doc':
                case 'docx':
                    $files[$k]['type'] = 3;
                    break;
            }
        }
        //生成二维码
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/uploadReport?id=' . $atid.'&i=atid&t=assets_transfer_report&username='.session('username');
        $codeUrl = $scrapModel->createCodePic($url);
        $codeUrl = trim($codeUrl,'.');
        $this->assign('codeUrl',$codeUrl);
        $this->assign('uploadinfo',$files);
        $this->assign('atid',$atid);
        $this->display('uploadReport');
    }

    //转科验收操作
    public function check()
    {
        //权限控制
        if (IS_GET) {
            $transfernum = I('GET.transNum');
            $assid = I('GET.assid');
            if (!$transfernum || !$assid) {
                echo '操作错误';
                die;
            }
            //查看申请转科设备信息
            $asModel = new AssetsTransferModel();
            $join[0] = ' LEFT JOIN __ASSETS_INFO__ ON __ASSETS_TRANSFER__.assid = __ASSETS_INFO__.assid';
            $where['sb_assets_transfer.transfernum'] = $transfernum;
            $where['sb_assets_transfer.assid'] = $assid;
            $fields = 'sb_assets_transfer.*,sb_assets_info.assnum,sb_assets_info.assorignum,sb_assets_info.assets,sb_assets_info.model,sb_assets_info.catid';
            $zkInfo = $asModel->DB_get_one_join('assets_transfer', 'sb_assets_transfer', $fields, $join, $where);
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
            $zkInfo['tranout_departrespon'] = $departname[$zkInfo['tranout_departid']]['departrespon'];
            $zkInfo['tranin_depart_name'] = $departname[$zkInfo['tranin_departid']]['department'];
            $zkInfo['tranin_departrespon'] = $departname[$zkInfo['tranin_departid']]['departrespon'];
            //查询是否开启了转科审批
            $isOpenApprove = $this->checkApproveIsOpen(C('TRANSFER_APPROVE'),session('job_hospitalid'));
            if($isOpenApprove){
                //查询审批历史
                $apps = $asModel->DB_get_all('approve', '*', array('atid' => $zkInfo['atid'], 'approve_class' => 'transfer', 'process_node' => C('TRANSFER_APPROVE')), '', 'process_node_level,apprid asc');
                $this->assign('approves', $apps);
            }
            $subsidiary=$asModel->getSubsidiaryBasic($zkInfo['atid']);
            $this->assign('isOpenApprove',$isOpenApprove);
            $this->assign('subsidiary',$subsidiary);
            $this->assign('zkInfo', $zkInfo);
            $this->assign('username', session('username'));
            $this->assign('currenDate', getHandleTime(time()));
            $this->display();
        } else {
            $asModel = new AssetsTransferModel();
            $ScrapModel = new AssetsScrapModel();
            $action = I('post.action');
            if($action == 'uploadReport'){
                $result = $ScrapModel->uploadReport(C('UPLOAD_DIR_REPORT_TRANSFER_NAME'));
                if($result['status'] == 1){
                    //保存数据库
                    $data['atid'] = I('post.atid');
                    $data['file_name'] = $result['formerly'];
                    $data['save_name'] = $result['title'];
                    $data['file_type'] = $result['file_type'];
                    $data['file_size'] = $result['file_size'];
                    $data['file_url'] = $result['file_url'];
                    $data['add_user'] = session('username');
                    $data['add_time'] = date('Y-m-d H:i:s');
                    $ScrapModel->insertData('assets_transfer_report',$data);
                }
                $this->ajaxReturn($result, 'json');
            }elseif($action == 'del_file'){
                $result = $ScrapModel->updateData('assets_transfer_report',array('is_delete'=>1),array('file_id'=>I('post.file_id')));
                if($result){
                    $this->ajaxReturn(array('status'=>1,'msg'=>'删除成功！'));
                }else{
                    $this->ajaxReturn(array('status'=>-1,'msg'=>'删除失败！'));
                }
            }else{
                //保存验收数据
                $transfernum = I('POST.transnum');
                $assid = I('POST.assid');
                $zkInfo = $asModel->DB_get_one('assets_transfer','atid,assid,transfernum,tranout_departid,tranout_departrespon,tranin_departid,address,is_check,applicant_user,applicant_time',array('transfernum'=>$transfernum,'assid'=>$assid));
                if(!$zkInfo){
                    $result['status'] = -1;
                    $result['msg'] = '查找不到转科设备信息';
                    $this->ajaxReturn($result, 'json');
                }
                if ($zkInfo['is_check'] == 1) {
                    $result['status'] = -1;
                    $result['msg'] = '该设备已验收';
                    $this->ajaxReturn($result, 'json');
                }
                $data['checkdate'] = getHandleDate(strtotime(I('POST.checkdate')));
                $data['check_user'] = session('username');
                $data['check_time'] = getHandleDate(time());
                $data['is_check'] = I('POST.res');
                $data['check'] = trim(I('POST.check'));
                if (!$transfernum || !$assid) {
                    $result['status'] = -1;
                    $result['msg'] = '操作错误';
                    $this->ajaxReturn($result, 'json');
                }
                if (I('POST.checkdate') < getHandleTime(time())) {
                    $res['status'] = -1;
                    $res['msg'] = '验收日期不能小于当前日期';
                    $this->ajaxReturn($res, 'JSON');
                }
                $assnum = $asModel->DB_get_one('assets_info','assnum',array('assid'=>$assid));
                $uprow = $asModel->updateData('assets_transfer', $data, array('transfernum' => $transfernum, 'assid' => $assid));
                if ($uprow) {
                    $change_assid=[];
                    $change_assid[]=$assid;
                    $subsidiary_assid=[];
                    $subsidiary=$asModel->DB_get_all('assets_transfer_detail','subsidiary_assid',['atid'=>['EQ',$zkInfo['atid']]]);
                    if($subsidiary){
                        foreach ($subsidiary as &$sub){
                            $change_assid[]=$sub['subsidiary_assid'];
                            $subsidiary_assid[]=$sub['subsidiary_assid'];
                        }
                    }
                    $departInfo = $asModel->DB_get_one('department','department',['departid'=>$zkInfo['tranout_departid']]);
                    if ($data['is_check'] == C('YES_STATUS')) {
                        //验收通过
                        $departname = array();
                        include APP_PATH . "Common/cache/department.cache.php";
                        $asModel->updateData('assets_info', array('status'=>C('ASSETS_STATUS_USE'),'departid' => $zkInfo['tranin_departid'], 'address' => $departname[$zkInfo['tranin_departid']]['address'], 'managedepart' => $departname[$zkInfo['tranin_departid']]['department']),['assid'=>['IN',$change_assid]]);
                        //记录设备变更信息
                        $remark = '设备转科：' . $departname[$zkInfo['tranout_departid']]['department'] . '===>' . $departname[$zkInfo['tranin_departid']]['department'];
                        $all_subsidiaryWhere['main_assid'] = ['EQ', $assid];
                        $all_subsidiaryWhere['status'][0] = 'NOTIN';
                        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_SCRAP');//报废
                        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
                        $all_subsidiaryData = $asModel->DB_get_all('assets_info', 'assid,buy_price', $all_subsidiaryWhere);
                        if($all_subsidiaryData){
                            $all_subsidiary_assid = [];
                            foreach ($all_subsidiaryData as $all_sub) {
                                $all_subsidiary_assid[] = $all_sub['assid'];
                            }
                            $all_subsidiary_assid = array_diff($all_subsidiary_assid, $subsidiary_assid);
                            if ($all_subsidiary_assid) {
                                //将未选中的附属设备变成无主
                                $diffData['main_assid'] = 0;
                                $diffData['main_assets'] = '';
                                $diffWhere['assid'] = ['IN', $all_subsidiary_assid];
                                $asModel->updateData('assets_info', $diffData, $diffWhere);
                            }
                        }
                        $smsData['check_status'] = '通过';
                    }else{
                        //验收不通过,更新设备最新状态为在用
                        $remark='验收不通过！';
                        $asModel->updateData('assets_info', array('status' => C('ASSETS_STATUS_USE')), ['assid'=>['IN',$change_assid]]);
                        $smsData['check_status'] = '不通过';
                    }
                    //==========================================短信 START==========================================
                    $transferModel = $transModel = new AssetsTransferModel();
                    $settingData = $transferModel->checkSmsIsOpen('Transfer');

                    $telephone = $transferModel->DB_get_one('user', 'telephone,openid', array('username' => $zkInfo['applicant_user']));
                    $asInfo = $transferModel->DB_get_one('assets_info','assnum,assets',['assid'=>$zkInfo['assid']]);
                    if ($settingData && $telephone['telephone']) {
                        //有开启短信
                        $departname = [];
                        include APP_PATH . "Common/cache/department.cache.php";
                        $smsData['tranout_department'] = $departname[$zkInfo['tranout_departid']]['department'];
                        $smsData['tranin_department'] = $departname[$zkInfo['tranin_departid']]['department'];
                        $smsData['assnum'] = $asInfo['assnum'];
                        $smsData['assets'] = $asInfo['assets'];
                        $smsData['transfer_num'] = $zkInfo['transfernum'];
                        $ToolMod = new ToolController();
                        $sms = $transferModel->formatSmsContent($settingData['checkTransferStatus']['content'], $smsData);
                        $ToolMod->sendingSMS($telephone['telephone'], $sms,'Transfer', $zkInfo['atid']);
                        //==========================================短信 END==========================================
                    }
                    if(C('USE_FEISHU') === 1){
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转科单号：**'.$zkInfo['transfernum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**'.$asInfo['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**'.$asInfo['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转出科室：**'.$smsData['tranout_department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转入科室：**'.$smsData['tranin_department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**验收结果：**'.$smsData['check_status'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**验收备注：**'.$data['check'];
                        $feishu_fields[] = $fd;

                        $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['header']['template'] = 'red';
                        $card_data['header']['title']['content'] = '设备转科验收结果通知';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        $asModel->send_feishu_card_msg($telephone['openid'],$card_data);
                        //==========================================飞书 END==========================================
                    }else{
                        //==================================微信通知验收结果 END====================================
                        if ((new ModuleModel())->decide_wx_login() && $telephone['openid']) {
                            // 发送验收结果给申请人
                            $isCheckText = $data['is_check'] === C('YES_STATUS') ? '已验收' : '验收不通过';

                            Weixin::instance()->sendMessage($telephone['openid'], '设备验收通知', [
                                'thing3'             => $smsData['tranout_department'],// 所属科室
                                'thing1'             => $asInfo['assets'],// 设备名称
                                'const13'            => '转科',// 设备来源
                                'character_string11' => $zkInfo['transfernum'],// 订单编号
                                'const7'             => $isCheckText,// 处理结果
                            ]);
                        }
                        //==================================微信通知验收结果 END====================================
                    }
                    $asModel->updateAllAssetsStatus($change_assid, C('ASSETS_STATUS_USE'), $remark);
                    $log['assnum']=$assnum['assnum'];
                    $asModel->DB_set_update('department',array('departnum'=>$zkInfo['tranout_departid'],'hospital_id'=>session('current_hospitalid')),'assetssum',1);
                    $asModel->DB_set_update('department',array('departnum'=>$zkInfo['tranout_departid'],'hospital_id'=>session('current_hospitalid')),'assetsprice',$all_subsidiaryData['buy_price']);
                    $text = getLogText('acceptanceTransferLogText',$log);
                    $asModel->addLog('assets_transfer',M()->getLastSql(),$text,$zkInfo['atid']);
                    $result['status'] = 1;
                    $result['msg'] = '验收成功';
                    $this->ajaxReturn($result, 'json');
                } else {
                    $result['status'] = -1;
                    $result['msg'] = '验收失败';
                    $this->ajaxReturn($result, 'json');
                }
            }
        }
    }

    //转科进度查询列表
    public function progress()
    {
        if (IS_POST) {
            //Carbon::now()->timestamp;
            $transModel = new AssetsTransferModel();
            $result = $transModel->getProgress();
            $this->ajaxReturn($result, 'json');
        } else {
            $asModel = new AssetsInfoModel();
            $users = $asModel->getUser();
            //查询是否开启了转科审批
            $isOpenApprove = $this->checkApproveIsOpen(C('TRANSFER_APPROVE'),session('job_hospitalid'));
            $this->assign('users', $users);
            $this->assign('isOpenApprove', $isOpenApprove);
            $this->assign('progress', get_url());
            $this->display();
        }
    }
}
