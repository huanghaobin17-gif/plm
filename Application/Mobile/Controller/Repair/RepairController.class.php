<?php

namespace Mobile\Controller\Repair;

use Admin\Controller\Tool\ToolController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\CommonModel;
use Admin\Model\ModuleModel;
use Admin\Model\UserModel;
use Common\Support\UrlGenerator;
use Common\Weixin\Weixin;
use Mobile\Model\RepairModel;
use Mobile\Controller\Login\IndexController;
use Mobile\Model\WxAccessTokenModel;

class RepairController extends IndexController
{
    private $MODULE = 'Repair';
    private $Controller = 'Repair';
    protected $index_url = 'Index/testindex.html';//首页地址
    protected $examine_url = 'Repair/examine.html';//验收设备列表地址

    //维修接单列表
    public function ordersLists()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'overhaulLists':
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->overhaulLists();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->ordersLists();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showRepairDetails':
                    $this->showRepairDetails();
                    break;
                //维修检列表页面
                case 'overhaulLists':
                    $this->showOverhaulLists();
                    break;
                default:
                    //维修接单列表页面
                    $this->showOrdersLists();
                    break;
            }
        }
    }

    //维修接单列表页面
    private function showOrdersLists()
    {
        $this->assign('ordersListsUrl', get_url());
        $this->display();
    }

    //维修检列表页面
    private function showOverhaulLists()
    {
        $jssdk = new WxAccessTokenModel();
        $signPackage = $jssdk->GetSignPackage();
        $this->signPackage = $signPackage;
        $this->assign('overhaulListsUrl', get_url());
        $this->display('overhaulLists');
    }

    //维修详情页面
    public function showRepairDetails()
    {
        $repid = I('GET.repid');
        if ($repid) {
            $RepairModel = new \Admin\Model\RepairModel();
            //维修信息
            $repArr = $RepairModel->getRepairBasic($repid);
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
            //获取故障问题
            $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
            //第三方厂家信息
            $companyData = $RepairModel->getAllCompanysBasic($repid);
            $company = $companyData['data'];
            $companyLast = $companyData['lastData'];
            //配件/服务
            $parts = $RepairModel->getAllPartsBasic($repid);
            //审核历史action:overhaulLists
            $approves = $RepairModel->getApproveBasic($repid);
            //维修跟进信息
            $follow = $RepairModel->getRepirFollowBasic($repid);
            //是否是否有权限查看上传文件
            $upload = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
            if ($upload) {
                //获取上传的文件
                $files = $RepairModel->getRepairFileBasic($repid);
                $this->assign('files', $files);
            }
            $doOfferMenu = get_menu($this->MODULE, $this->Controller, 'doOffer');
            //配件统一报价 1可操作报价 0不可操作
            $IS_OPEN_OFFER = C('DO_STATUS');
            //第三方统一报价 1可操作报价 0不可操作
            $IS_OPEN_OFFER_DO_OFFER = C('DO_STATUS');
            if (C('IS_OPEN_OFFER')) {
                if (!$doOfferMenu) {
                    $IS_OPEN_OFFER = C('NOT_DO_STATUS');
                    $IS_OPEN_OFFER_DO_OFFER = C('NOT_DO_STATUS');
                }
            } else {
                //第三方因为没有开启报价功能,也需要走报价让报价经理选，所以另开一个变量
                if (!$doOfferMenu) {
                    //报价关闭 无权限（可填价格 数量 建议厂家）
                    $IS_OPEN_OFFER_DO_OFFER = C('SHUT_STATUS_DO');
                }
            }
            //时间线
            $repairTimeRestult = $RepairModel->showRepairTimeLineData($repid);
            $repairTimeLine = $repairTimeRestult['repairTimeLineData'];
            foreach ($repairTimeLine as $k => $v) {
                if ($repairTimeLine[$k]['date'] == '-') {
                    unset($repairTimeLine[$k]);
                }
            }
            $repairTimeLine = array_unique($repairTimeLine, SORT_REGULAR);
            $repairTimeLine = $RepairModel->getUserNameAndPhone($repairTimeLine);
            $this->assign('repairTimeLine', array_reverse($repairTimeLine));
            $this->assign('asArr', $asArr);
            $this->assign('repArr', $repArr);
            $this->assign('approves', $approves);
            $this->assign('follow', $follow);
            $this->assign('parts', $parts);
            $this->assign('company', $company);
            $this->assign('companyLast', $companyLast);
            $this->assign('isOpenOffer', $IS_OPEN_OFFER);
            $this->assign('isOpenOffer_formOffer', $IS_OPEN_OFFER_DO_OFFER);
            $this->display('showRepairDetails');
        } else {
            $this->error('非法操作');
        }
    }

    //报修页面
    public function addRepair()
    {
        if (IS_POST) {
            $action = I('post.action');
            $RepairMod = new \Admin\Model\RepairModel();
            switch ($action) {
                case 'scanQRcode_baoxiu':
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->scanQRcode_baoxiu();
                    break;
                case 'upload':
                    //文件上传
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->uploadfile();
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //普通报修操作
                    $result = $RepairMod->doAddRepair();
                    break;
            }
            $this->ajaxReturn($result, 'json');
        } else {
            //普通报修
            $assnum = I('get.assnum');
            //查询设备信息
            $RepairMod = new \Admin\Model\RepairModel();
            $ainfo = $RepairMod->DB_get_one('assets_info', 'assid,status,departid', array('assnum' => $assnum,'is_delete'=>'0'));
            if (!$ainfo) {
                $ainfo = $RepairMod->DB_get_one('assets_info', 'assid,status,departid', array('assorignum' => $assnum,'is_delete'=>'0'));
            }
            if (!$ainfo) {
                $ainfo = $RepairMod->DB_get_one('assets_info', 'assid,status,departid', array('assorignum_spare' => $assnum,'is_delete'=>'0'));
            }
            if ($ainfo['status'] == C('ASSETS_STATUS_REPAIR')) {
                //状态 维修中
                $repWhere['assid'] = array('EQ', $ainfo['assid']);
                $repWhere['status'] = array('NEQ', C('REPAIR_ALREADY_ACCEPTED'));
                $repinfo = $RepairMod->DB_get_one('repair', 'repid', $repWhere, 'repid desc');
                redirect(U('M/Repair/showRepairDetails/repid/' . $repinfo['repid']));
                exit;
            }
            $departids = session('departid');
            if(!in_array($ainfo['departid'], explode(',', $departids))){
                //不在自己管理科室内
                $this->assign('tips', '对不起，'.$assnum.'设备不在您管理的科室范围内！');
                $this->assign('btn', '返回首页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $assid = $ainfo['assid'];
            $asArr = $RepairMod->getAssetsBasic($assid);
            //报修故障是否必填
            $problem = $RepairMod->DB_get_one('base_setting', 'value', array('set_item' => 'repair_required'));
            $problem = json_decode($problem['value'], true);
            //是否由系统生成字段
            $issystem = $RepairMod->DB_get_one('base_setting', 'value', array('set_item' => 'repair_system'));
            $issystem = json_decode($issystem['value'], true);
            $this->assign('problem', $problem['repair_detail']);
            $this->assign('issystem', $issystem);
            $this->assign('asArr', $asArr);
            $this->assign('addRepairUrl', ACTION_NAME);
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
            $jssdk = new WxAccessTokenModel();
            $signPackage = $jssdk->GetSignPackage();
            $this->signPackage = $signPackage;
            $this->display();
        }
    }

    //接单
    public function accept()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'scanQRCode_jianxiu':
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->scanQRcode_jianxiu();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'upload':
                    //文件上传
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->uploadfile();
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'accept':
                    //接单操作
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->accept();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'overhaul':
                    //检修操作
                    $RepairModel = new \Admin\Model\RepairModel();
                    $result = $RepairModel->doAccept();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'tmp_save':
                    //检修操作
                    $RepairModel = new \Admin\Model\RepairModel();
                    $result = $RepairModel->doAccept();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'sign_in'://签到
                    $repModel = new RepairModel();
                    $result = $repModel->scanQRCode_signin();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getengineer':
                    $this->getengineer();
                    break;
                case 'edit_engineer':
                    $RepairModel = new RepairModel();
                    //修改接单工程师
                    $repid = I('post.repid');
                    //查询维修单信息
                    $repInfo = $RepairModel->DB_get_one('repair','assets,assnum,departid,breakdown',['repid'=>$repid]);
                    if(!$repInfo){
                        $result['status']=-1;
                        $result['msg'] = '查找不到维修单信息';
                    }else{
                        $departname = [];
                        include APP_PATH . "Common/cache/department.cache.php";
                        $repInfo['department'] = $departname[$repInfo['departid']]['department'];
                        $edit_engineer = I('post.edit_engineer');
                        //查询被转单人电话
                        $user = $RepairModel->DB_get_one('user','telephone,openid',['username'=>$edit_engineer]);
                        $res = $RepairModel->updateData('repair', array('response'=>$edit_engineer,'response_tel'=>$user['telephone']), array('repid' => $repid));
                        if ($res) {
                            $moduleModel = new ModuleModel();
                            $wx_status = $moduleModel->decide_wx_login();
                            if ($wx_status) {
                                // 发送转单通知给被转单人
                                $redirectUrl = UrlGenerator::instance()->to('/Repair/accept?action=overhaul', ['repid' => $repid]);

                                if ($user['openid']) {
                                    Weixin::instance()->sendMessage($user['openid'], '设备维修通知', [
                                        'thing3'             => $repInfo['department'],// 科室
                                        'thing6'             => $repInfo['assets'],// 设备名称
                                        'character_string12' => $repInfo['assnum'],// 设备编码
                                        'character_string35' => $repInfo['repnum'],// 维修单号
                                        'const17'            => '已转单，请接单',// 工单状态
                                    ], $redirectUrl);
                                }
                            }
                            $result['status']=1;
                            $result['msg'] = '转单成功';
                        }else{
                            $result['status']=-1;
                            $result['msg'] = '转单失败';
                        }
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $this->ajaxReturn(['status' => -1, 'msg' => '未知参数'], 'json');
                    break;
            }

        } else {
            $action = I('get.action');
            $this->assign('now_date', date('Y-m-d'));
            switch ($action) {
                case 'getAllType':
                    //搜索所有故障类型
                    $RepairModel = new \Admin\Model\RepairModel();
                    $result = $RepairModel->getAllType();
                    $this->ajaxReturn($result, 'JSON');
                    break;
                case 'addTypeAndProblem':
                    $this->display('addTypeAndProblem');
                    break;
                case 'getRepairProblem':
                    //搜索对应故障问题
                    $parentid = I('get.parentid');
                    $RepairModel = new \Admin\Model\RepairModel();
                    $problem = $RepairModel->getRepairProblem($parentid);
                    if ($problem) {
                        $result['code'] = 0;
                        $result['msg'] = 'success';
                        $result['data'] = $problem;
                        $this->ajaxReturn($result, 'JSON');
                    }
                    break;
                case 'getRepairType':
                    //搜索对应故障类型
                    $RepairModel = new \Admin\Model\RepairModel();
                    $type = $RepairModel->getRepairType();
                    foreach ($type as $k => $v) {
                        $type[$k]['name'] = $v['title'];
                        $type[$k]['value'] = $v['id'];
                    }
                    $this->ajaxReturn($type, 'JSON');
                    break;
                case 'overhaul':
                    $this->showOverhaul();
                    break;
                default:
                    $this->showAccept();
                    break;
            }
        }
    }

    //获取拥有维修该科室权限的工程师
    private function getengineer()
    {
        $departid=I('POST.departid');
        $repid=I('POST.repid');
        $user = ToolController::getUser('accept', $departid, false);
        $RepairMod = new \Admin\Model\RepairModel();
        $data = $RepairMod->DB_get_one('repair', 'response', array('repid'=>$repid));
        $result['status'] = 1;
        $result['data'] = $user;
        $result['response'] = $data['response'];
        $this->ajaxReturn($result, 'JSON');
    }

    //维修接单页面
    private function showAccept()
    {
        $repid = I('GET.repid');
        if ($repid) {
            $RepairModel = new \Admin\Model\RepairModel();
            //维修信息
            $repArr = $RepairModel->getRepairBasic($repid);
            if (!isset($repArr['assid'])) {
                $this->assign('tips', '参数非法，请稍联系管理员！');
                $this->assign('btn', '返回首页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
            //故障类型 故障问题数据
            $repairType = $RepairModel->get_all_repair_type();
            //验证是否签到
            $sign_result = $RepairModel->check_sign_in($repid);
            //预计到场时间上限
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            if ($repArr['status'] != '1') {
                $this->assign('is_display', 0);
            } else {
                $this->assign('is_display', 1);
            }
            $uptime = $baseSetting['repair']['repair_uptime']['value'];
            $this->assign('asArr', $asArr);
            $this->assign('repArr', $repArr);
            $this->assign('repairType', $repairType);
            $this->assign('uptime', $uptime);
            $this->assign('sign_result', $sign_result);
            $this->assign('response', session('username'));
            $this->assign('response_date', getHandleMinute(time()));
            $this->assign('acceptUrl', get_url());
            $this->display();
        } else {
            $this->error('非法参数');
        }
    }

    //检修页面
    public function showOverhaul()
    {
        $repid = I('GET.repid');
        if ($repid) {
            $RepairModel = new \Admin\Model\RepairModel();
            //维修信息
            $repArr = $RepairModel->getRepairBasic($repid);
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($repArr['assid']);

            //故障类型 故障问题数据
            $repairType = $RepairModel->get_all_repair_type();
            foreach ($repairType as &$r_v){
                foreach ($r_v['parent'] as &$vv){
                    $vv['value'] = $r_v['id'].'-'.$vv['id'];
                }
            }
            //验证是否签到
            //$sign_result = $RepairModel->check_sign_in($repid);
            //预计到场时间上限
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $uptime = $baseSetting['repair']['repair_uptime']['value'];
            $doOfferMenu = get_menu($this->MODULE, $this->Controller, 'doOffer');
            //第三方统一报价 1可操作报价 0不可操作
            $IS_OPEN_OFFER_DO_OFFER = C('DO_STATUS');
            $show_parts = 'block';
            $not_scene = 'none';
            if (C('IS_OPEN_OFFER')) {
                if (!$doOfferMenu) {
                    $IS_OPEN_OFFER_DO_OFFER = C('NOT_DO_STATUS');
                }
            } else {
                //第三方因为没有开启报价功能,也需要走报价让报价经理选，所以另开一个变量
                if (!$doOfferMenu) {
                    //报价关闭 无权限（可填价格 数量 建议厂家）
                    $IS_OPEN_OFFER_DO_OFFER = C('SHUT_STATUS_DO');
                }
            }
            //配件 个人库
            $personalPartsInfo = $RepairModel->getPartsInfo();
            $this->assign('personalPartsInfo', json_encode($personalPartsInfo));

            $partsAll = $RepairModel->getPartsDic();
            //第三方公司选择
            $AssetsInfoModel = new AssetsInfoModel();
            $company = $AssetsInfoModel->getSuppliers('repair');
            foreach ($company as $k => $v) {
                $company[$k]['status'] = 1;
            }
            //查询是否有暂存数据
            if($repArr['status'] == C('REPAIR_RECEIPT') && $repArr['overhaul_time']){
                //已接单未检修，且暂存过检修信息
                $pros = $RepairModel->get_repair_fault($repid);
                $files = $RepairModel->get_repair_file($repid);
                foreach ($pros as $pro=>$prv){
                    foreach($repairType as $k=>$v){
                        if($prv['fault_type_id'] == $v['id']){
                            $repairType[$k]['selected'] = 'selected';
                            foreach ($repairType[$k]['parent'] as $pp=>$pv){
                                if($pv['id'] == $prv['fault_problem_id']){
                                    $repairType[$k]['parent'][$pp]['selected'] = 'selected';
                                }
                            }
                        }
                    }
                }
                foreach ($files as $k => $v){
                    $files[$k]['add_date'] = date('Y-m-d',strtotime($v['add_time']));
                }
                $this->assign('pros',$pros);
                $this->assign('files',$files);
                if($repArr['is_scene'] == 0){
                    $this->assign('is_scene','none');
                    $not_scene = 'block';
                }
                if($repArr['repair_type'] == C('REPAIR_TYPE_IS_STUDY')){
                    //自修
                    $parts = $RepairModel->get_repair_parts($repid);
                    foreach ($parts as $key => $value) {
                        foreach ($partsAll as $k => $v) {
                            if ($value['parts']==$v['parts']) {
                            $partsAll[$k]['selected'] = 'selected';
                        }
                        }
                    }
                    $this->assign('parts',$parts);
                    $this->assign('show_company','none');
                    $show_parts = 'block';
                }
                if($repArr['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')){
                    //第三方维修
                    $coms = $RepairModel->get_repair_companys($repid);
                    foreach ($coms as $v){
                        if($v['last_decisioin'] == 1){
                            $this->assign('decision_reasion',$v['decision_reasion']);
                        }
                        foreach ($company as $ck=>$cv){
                            if($v['offer_company'] == $cv['sup_name']){
                                unset($company[$ck]);
                            }
                        }
                    }

                    $this->assign('coms',$coms);
                    $this->assign('show_company','block');
                    $show_parts = 'none';
                }

            }
            if ($repArr['expect_time']) {
                $repArr['expect_time'] = date('Y/m/d',strtotime($repArr['expect_time']));
            }
            if ($repArr['status']!='2') {
                $this->assign('is_display',0);
            }else{
                $this->assign('is_display',1);
            }
            if (is_null($repArr['repair_type'])) {
                $repArr['repair_type'] = 0;
            }
            if($repArr['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')){
                    //第三方维修
                    $coms = $RepairModel->get_repair_companys($repid);
                    foreach ($coms as $v){
                        if($v['last_decisioin'] == 1){
                            $this->assign('decision_reasion',$v['decision_reasion']);
                        }
                        foreach ($company as $ck=>$cv){
                            if($v['offer_company'] == $cv['sup_name']){
                                unset($company[$ck]);
                            }
                        }
                    }
                    $this->assign('coms',$coms);
                    $this->assign('show_company','block');
                    $show_parts = 'none';
                }
            $this->assign('not_scene',$not_scene);
            $this->assign('show_parts',$show_parts);
            $this->assign('company', $company);
            //第三方公司联动选择
            $this->assign('companyInfo', json_encode($company));
            $this->assign('asArr', $asArr);
            $this->assign('partsAll', $partsAll);
            $this->assign('repArr', $repArr);
            $this->assign('repairType', $repairType);
            $this->assign('uptime', $uptime);
            //$this->assign('sign_result', $sign_result);
            $this->assign('isOpenOffer_formOffer', $IS_OPEN_OFFER_DO_OFFER);
            $this->assign('response', session('username'));
            $this->assign('overhaulUrl', get_url());
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            if($baseSetting['repair']['open_sweepCode_overhaul']['value']['open'] == C('OPEN_STATUS')){
                if($repArr['sign_in_time']){
                    $this->assign('wx_sign_in', 0);
                }else{
                    $this->assign('wx_sign_in', 1);
                }
            }else{
                $this->assign('wx_sign_in', 0);
            }
            $jssdk = new WxAccessTokenModel();
            $signPackage = $jssdk->GetSignPackage();
            $this->signPackage = $signPackage;
            $this->display('overhaul');
        } else {
            $this->error('非法参数');
        }
    }

    //维修进程列表
    public function progress()
    {
        if (IS_POST) {
            $RepairModel = new RepairModel();
            $result = $RepairModel->progress();
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showRepairDetails':
                    $this->showRepairDetails();
                    break;
                default:
                    $this->showProgress();
                    break;
            }
        }
    }

    //
    public function showProgress()
    {
        $repairStatus = array(
            '1' => '待结单',
            '2' => '带检修',
            '3' => '待验收',
            '4' => '待出库',
            '5' => '待审批',
            '6' => '继续维修',
            '7' => '已结束',
//                '8' => '待转单',
        );
        $this->assign('repairStatus', $repairStatus);
        $this->assign('progressUrl', get_url());
        $this->display();
    }

    //设备修复验收
    public function examine()
    {
        if (IS_POST) {
            $RepairModel = new RepairModel();
            $result = $RepairModel->examine();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->assign('examineUrl', get_url());
            $this->display();
        }
    }

    //验收设备
    public function checkRepair()
    {
        if (IS_POST) {
            $RepairModel = new \Admin\Model\RepairModel();
            $result = $RepairModel->checkRepair();
            $this->ajaxReturn($result, 'json');
        } else {
            $repid = I('get.repid');
            if (!$repid) {
                $this->assign('tips', '参数非法，请稍联系管理员！');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->examine_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $RepairModel = new \Admin\Model\RepairModel();
            //维修信息
            $repArr = $RepairModel->getRepairBasic($repid);
            if ($repArr['status'] == C('REPAIR_ALREADY_ACCEPTED')) {
                //已验收，跳转到详情页
                redirect(U('Mobile/Repair/Repair/showRepairDetails/repid/' . $repid));
                exit;
            }
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
            //获取故障问题
            $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
            //是否由系统生成字段
            $issystem = $RepairModel->DB_get_one('base_setting', 'value', array('set_item' => 'repair_system'));
            $issystem = json_decode($issystem['value'], true);
            $this->assign('repair_check', $issystem['repair_check']);
            $this->assign('user', session('username'));
            $this->assign('asArr', $asArr);
            $this->assign('repArr', $repArr);
            $this->assign('url', get_url());
            $this->display();
        }
    }

    //维修处理列表
    public function getRepairLists()
    {
        if (IS_POST) {
            $repairModel = new RepairModel();
            $result = $repairModel->get_repair_lists();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->assign('url', get_url());
            $this->display();
        }
    }

    //维修处理详细页
    public function startRepair()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'upload':
                    //文件上传
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->uploadfile();
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $RepairModel = new \Admin\Model\RepairModel();
                    $result = $RepairModel->doStartRepair();
                    $this->ajaxReturn($result, 'json');
            }
        } else {
            $repid = I('get.repid');
            if ($repid) {
                $RepairModel = new \Admin\Model\RepairModel();
                //维修信息
                $repArr = $RepairModel->getRepairBasic($repid);
                if ($repArr['status'] >= C('REPAIR_MAINTENANCE_COMPLETION')) {
                    //已维修完成
                    redirect(U('Mobile/Repair/Repair/showRepairDetails/repid/' . $repid));
                    exit;
                }
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
                //获取故障问题
                $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
                //第三方厂家信息
                $companyData = $RepairModel->getAllCompanysBasic($repid);
                $company = $companyData['data'];
                $companyLast = $companyData['lastData'];
                //配件/服务
                $parts = $RepairModel->getAllPartsBasic($repid);
                //审核历史
                $approves = $RepairModel->getApproveBasic($repid);
                //维修跟进信息
                $follow = $RepairModel->getRepirFollowBasic($repid);
                //是否是否有权限查看上传文件
                $upload = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
                if ($upload) {
                    //获取上传的文件
                    $files = $RepairModel->getRepairFileBasicAndDel($repid);
                    $newFiles = [];
                    $imgType = ['JPG', 'GIF', 'PNG', 'JPEG', 'jpg', 'gif', 'png', 'jpeg'];
                    foreach ($files as &$one) {
                        //过滤非图片的文件
                        if (in_array($one['file_type'], $imgType)) {
                            $newFiles[] = $one;
                        }
                    }
                    $this->assign('files', $newFiles);
                }
                $doOfferMenu = get_menu($this->MODULE, $this->Controller, 'doOffer');
                //第三方统一报价 1可操作报价 0不可操作
                $IS_OPEN_OFFER_DO_OFFER = C('DO_STATUS');
                if (C('IS_OPEN_OFFER')) {
                    if (!$doOfferMenu) {
                        $IS_OPEN_OFFER_DO_OFFER = C('NOT_DO_STATUS');
                    }
                } else {
                    //第三方因为没有开启报价功能,也需要走报价让报价经理选，所以另开一个变量
                    if (!$doOfferMenu) {
                        //报价关闭 无权限（可填价格 数量 建议厂家）
                        $IS_OPEN_OFFER_DO_OFFER = C('SHUT_STATUS_DO');
                    }
                }

                $partsAll = $RepairModel->getPartsDic();
                //辅助工程师
                $UserModel = new \Admin\Model\UserModel();
                $user = $UserModel->getUsers('accept', $asArr['departid']);
                //下一条跟进时间
                $maxtime = $RepairModel->DB_get_one('repair_follow', 'MAX(followdate) AS followdate', array('repid' => $repid));
                //是否由系统生成字段
                $issystem = $RepairModel->DB_get_one('base_setting', 'value', array('set_item' => 'repair_system'));
                $issystem = json_decode($issystem['value'], true);
                if ($repArr['status'] == C('REPAIR_MAINTENANCE')&&isset($issystem['service_working'])) {
                    //todo  维修工时计算：通知时间至当前时间;
                    $repArr['working_hours'] = timediff(strtotime($repArr['response_date']), time());
                }
                if (!isset($issystem['service_working'])) {
                    $repArr['engineer_time'] = date('Y/m/d',strtotime($repArr['engineer_time']));
                }
                //维修费用  expect_price(第三方报价,配件的费用)+other_price(其他费用)
                $repArr['actual_price'] = $repArr['expect_price'] + $repArr['other_price'];
                //配件 个人库
                $personalPartsInfo = $RepairModel->getPartsInfo();
                //删除协助工程师中已有的维修工程师
                foreach ($user as $key => $value) {
                    if ($value['username'] == $repArr['response']) {
                        unset($user[$key]);
                    }
                }
                if (!$repArr['engineer_time']||$repArr['engineer_time']=='1970/01/01') {
                    $repArr['engineer_time'] = date('Y/m/d',time());
                }
                $this->assign('personalPartsInfo', json_encode($personalPartsInfo));
                $this->assign('service_date', $issystem['service_date']);
                $this->assign('service_working', $issystem['service_working']);
                $this->assign('maxtime', getHandleTime($maxtime['followdate']));
                $this->assign('user', $user);
                $this->assign('userJson', json_encode($user));
                $this->assign('asArr', $asArr);
                $this->assign('partsAll', $partsAll);
                $this->assign('repArr', $repArr);
                $this->assign('approves', $approves);
                $this->assign('follow', $follow);
                $this->assign('parts', $parts);
                $this->assign('company', $company);
                $this->assign('companyLast', $companyLast);
                $this->assign('isOpenOffer_formOffer', $IS_OPEN_OFFER_DO_OFFER);
                $this->assign('startRepairUrl', get_url());
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    //维修审批
    public function addApprove()
    {
        $departid = session('departid');
        $departArr = explode(',', $departid);
        if (IS_POST) {
            $RepairModel = new \Admin\Model\RepairModel();
            $result = $RepairModel->doAddApprove();
            $this->ajaxReturn($result, 'json');
        } else {
            $repid = I('get.repid');
            if ($repid) {
                $RepairModel = new \Admin\Model\RepairModel();
                //维修信息
                $repArr = $RepairModel->getRepairBasic($repid);
                if (!isset($repArr['assid'])) {
                    $this->assign('tips', '参数非法，请稍联系管理员！');
                    $this->assign('btn', '返回首页');
                    $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                    $this->display('Pub/Notin/fail');
                    exit;
                }
                $current_approver = explode(',', $repArr['current_approver']);
                if ($repArr['approve_status'] != '0') {
                    $this->assign('is_display', 0);
                } else if (!in_array(session('username'), $current_approver)) {
                    $this->assign('is_display', 0);
                } else {
                    $this->assign('is_display', 1);
                }
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
                if (!in_array($asArr['departid'], $departArr)) {
                    $this->assign('tips', '对不起，您没有【' . $asArr['department'] . '】科室的管理权限，请联系管理员分配！');
                    $this->assign('btn', '返回首页');
                    $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                    $this->display('Pub/Notin/fail');
                    exit;
                }
                //获取故障问题
                $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
                //第三方厂家信息
                $companyData = $RepairModel->getAllCompanysBasic($repid);
                $company = $companyData['data'];
                $companyLast = $companyData['lastData'];
                //配件/服务
                $parts = $RepairModel->getAllPartsBasic($repid);
                //审核历史
                $approves = $RepairModel->getApproveBasic($repid);

                //******************************8审批流程显示 start***************************//
                $reModel = new RepairModel();
                $repArr = $reModel->get_approves_progress($repArr,'repid','repid');
                //**************************************审批流程显示 end*****************************//

                //维修跟进信息
                $follow = $RepairModel->getRepirFollowBasic($repid);
                //是否是否有权限查看上传文件
                $upload = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
                if ($upload) {
                    //获取上传的文件
                    $files = $RepairModel->getRepairFileBasic($repid);
                    $this->assign('files', $files);
                }
                //查询该用户是否有权限审批
                $canApprove = false;
                if ($repArr['approve_status'] == C('OUTSIDE_STATUS_APPROVE')) {
                    if ($repArr['current_approver']) {
                        $current_approver = explode(',', $repArr['current_approver']);
                        $current_approver_arr = [];
                        foreach ($current_approver as &$current_approver_value) {
                            $current_approver_arr[$current_approver_value] = true;
                        }
                        if ($current_approver_arr[session('username')]) {
                            $canApprove = true;
                        }
                    }
                }
                //统一报价 1可操作报价 0不可操作
                $IS_OPEN_OFFER = C('DO_STATUS');
                if (C('IS_OPEN_OFFER')) {
                    $doOfferMenu = get_menu($this->MODULE, $this->Controller, 'doOffer');
                    if (!$doOfferMenu) {
                        $IS_OPEN_OFFER = C('NOT_DO_STATUS');
                    }
                }
                $this->assign('canApprove', $canApprove);
                $this->assign('isOpenOffer', $IS_OPEN_OFFER);
                $this->assign('addApproveUrl', get_url());
                $this->assign('asArr', $asArr);
                $this->assign('repArr', $repArr);
                $this->assign('approves', $approves);
                $this->assign('follow', $follow);
                $this->assign('parts', $parts);
                $this->assign('company', $company);
                $this->assign('companyLast', $companyLast);
                $this->assign('approveUser', session('username'));
                $this->assign('approveDate', getHandleMinute(time()));

                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    //新增配件出库详细页
    public function partsOutWare()
    {
        if (IS_POST) {

        } else {
            $this->display();
        }
    }

    //新增配件入库详细页
    public function partsInWare()
    {
        if (IS_POST) {

        } else {
            $this->display();
        }
    }

    public function showApproveLists()
    {
        $this->assign('repairApproveListsUrl', get_url());
        $this->display();
    }


    public function checkApproveIsOpen($type, $hospital_id)
    {
        $RepairModel = new RepairModel();
        $where['approve_type'] = array('EQ', $type);
        $where['hospital_id'] = array('EQ', $hospital_id);
        $where['status'] = array('EQ', C('OPEN_STATUS'));
        $res = $RepairModel->DB_get_one('approve_type', '*', $where);
        return $res;
    }


}
