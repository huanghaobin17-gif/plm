<?php

namespace Admin\Controller\Repair;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\ApproveProcessModel;
use Admin\Model\AssetsTemplateModel;
use Admin\Model\ModuleModel;
use Admin\Model\ModuleSettingModel;
use Admin\Model\PatrolExecuteModel;
use Admin\Model\UserModel;
use Admin\Model\AssetsInfoModel;
use Admin\Model\RepairModel;
use Common\Support\Department;
use Common\Support\UrlGenerator;
use Common\Weixin\Weixin;

class RepairController extends CheckLoginController
{
    private $MODULE = 'Repair';
    private $Controller = 'Repair';

    // 模块首页
    public function index()
    {
        $this->display();
    }

    // 获取维修设备列表
    public function getAssetsLists()
    {
        //echo I('POST.action');die;
        if (IS_POST) {
            $action = I('POST.action');
            $RepairModel = new RepairModel();
            switch ($action) {
                case 'confirmAddRepairList':
                    $result = $RepairModel->confirmAddRepairList();
                    $this->ajaxReturn($result, 'json');
                    break;
                default :
                //echo 123;die;
                    $result = $RepairModel->getAssetsLists();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'showRepairDetails':
                    $this->showRepairDetails();
                    break;
                //巡查异常设备 转至报修 确认列表页
                case 'confirmAddRepairList':
                    $this->showConfirmAddRepairList();
                    break;
                default:
                    //设备报修申请列表页面
                    $this->showGetAssetsLists();
                    break;
            }
        }
    }

    //设备报修申请列表页面
    private function showGetAssetsLists()
    {
        $departids = explode(',', session('departid'));
        $departments = $this->getDepartname($departids);
        $this->assign('departments', $departments);
        $this->assign('getAssetsListsUrl', get_url());
        $this->display();
    }

    //巡查异常设备 转至报修 确认列表页
    private function showConfirmAddRepairList()
    {
        $departids = explode(',', session('departid'));
        $departments = $this->getDepartname($departids);
        $this->assign('departments', $departments);
        $this->assign('confirmAddRepairListUrl', get_url());
        $this->display('confirmAddRepairList');
    }

    //维修详情页面
    private function showRepairDetails()
    {
        $assid = I('get.assid');
        $repid = I('get.repid');
        if ($assid && $repid) {
            $RepairModel = new RepairModel();
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($assid);
            //维修信息
            $repArr = $RepairModel->getRepairBasic($repid);
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
                $files = $RepairModel->getRepairFileBasic($repid);
                $this->assign('files', $files);
            }
            //******************************审批流程显示 start***************************//
            $repArr = $RepairModel->get_approves_progress($repArr, 'repid', 'repid');
            //**************************************审批流程显示 end*****************************//

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
            //第三方公司选择
            $asModel = new AssetsInfoModel();
            $companyA = $asModel->getSuppliers('repair');
            foreach ($companyA as $k => $v) {
                $companyA[$k]['status'] = 1;
                if (isset($asArr['guarantee_id'])) {
                    if ($asArr['guarantee_id'] === $v['olsid']) {
                        $asArr['sup_name'] = $v['sup_name'];
                        $asArr['salesman_name'] = $v['salesman_name'];
                        $asArr['salesman_phone'] = $v['salesman_phone'];
                    }
                }
            }
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

    //获取拥有维修该科室权限的工程师
    public function getengineer()
    {
        $departid=I('POST.departid');
        $repid=I('POST.repid');
        $user = ToolController::getUser('accept', $departid, false);
        $RepairMod = new RepairModel();
        $data = $RepairMod->DB_get_one('repair', 'response', array('repid'=>$repid));
        $result['status'] = 1;
        $result['data'] = $user;
        $result['response'] = $data['response'];
        $this->ajaxReturn($result, 'JSON');
    }

    //设备报修
    public function addRepair()
    {
        if (IS_POST) {
            $action = I('post.action');
            $RepairMod = new RepairModel();
            //判断该设备是否处于报修状态，如果是提示用户不需要重复操作
            $where['status'] = C('ASSETS_STATUS_REPAIR');
            $where['assid'] = I('post.assid');
            $is_repair = $RepairMod->DB_get_one('assets_info', 'assid', $where);
            if ($is_repair) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '该设备已报修，请勿重复操作！'));
            }
            if ($action == 'confirmAddRepair') {
                //确认转至报修操作
                $result = $RepairMod->confirmAddRepair();
            } else {
                //普通报修操作
                $result = $RepairMod->doAddRepair();
            }
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('get.action');
            if ($action == 'confirmAddRepair') {
                //确认转至报修
                $RepairMod = new RepairModel();
                $confirmId = I('get.confirmId');
                if ($confirmId) {
                    $asArr = $RepairMod->getConfirmAddRepairArr($confirmId);
                    $PatrolExecuteModel = new PatrolExecuteModel();
                    $executeData = $PatrolExecuteModel->getRecord($asArr['assnum'], $asArr['cycid']);
                    $AssetsTemplateModel = new AssetsTemplateModel();
                    if ($executeData) {
                        $abnormalArr = $PatrolExecuteModel->DB_get_all('patrol_execute_abnormal', 'ppid,result,abnormal_remark', array('execid' => $executeData['execid'], 'result' => array('neq', '合格')));
                        $pointsArr = '';
                        foreach ($abnormalArr as &$one) {
                            $pointsArr .= ',' . $one['ppid'];
                        }
                        $pointsArr = trim($pointsArr, ',');

                        $cate = $AssetsTemplateModel->getIniPoints($pointsArr, $abnormalArr, 1);
                        $this->assign('data', $cate);
                        $this->assign('executeData', $executeData);
                    }
                    $this->assign('asArr', $asArr);
                    $this->assign('url', ACTION_NAME);
                    $this->display('confirmAddRepair');
                } else {
                    $this->error('非法操作');
                }
            } else {
                //普通报修
                $assid = I('get.assid');
                $RepairMod = new RepairModel();
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
                $this->assign('max', date('Y-m-d',strtotime('+1 day')));
                $this->assign('current_time', date('Y-m-d H:i:s'));
                $this->assign('addRepairUrl', ACTION_NAME);
                $baseSetting = [];
                include APP_PATH . "Common/cache/basesetting.cache.php";
                $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
                $this->display();
            }
        }
    }

    //维修派工响应列表
    public function dispatchingLists()
    {
        if (IS_POST) {
            $repairMod = new RepairModel();
            $result = $repairMod->dispatchingLists();
            $this->ajaxReturn($result, 'json');
        } else {
            $departids = explode(',', session('departid'));
            $userModel = new UserModel();
            $user = $userModel->getUsers('addRepair');
            $departments = $this->getDepartname($departids);
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('user', $user);
            $this->assign('departments', $departments);
            $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
            $this->assign('dispatchingListsUrl', get_url());
            $this->display();
        }
    }

    //维修指派
    public function assigned()
    {
        if (IS_POST) {
            $RepairModel = new RepairModel();
            $result = $RepairModel->doAssigned();
            $this->ajaxReturn($result, 'json');
        } else {
            $repid = I('get.repid');
            $where['repid'] = array('EQ', $repid);
            $where['status'] = array('EQ', C('REPAIR_HAVE_REPAIRED'));
            $RepairModel = new RepairModel();
            $repair = $RepairModel->getRepair($where);
            if ($repair == null) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '该设备已经有工程师接单'));
            } else if (I('get.type') == 'responder') {
                $this->ajaxReturn(array('status' => 0, 'msg' => '可以指派'));
            }
            $assets = $RepairModel->getAssetsBasic($repair['assid']);
            $asArr = array_merge($assets, $repair);
            if ($asArr['from'] != 1) {
                exit('微信端报修不能人工指派');
            } else {
                $asArr = $RepairModel->formatRepair($asArr);
                $user = ToolController::getUser('accept', $asArr['departid'], false);
                $this->assign('user', $user);
                $this->assign('asArr', $asArr);
                $this->assign('url', ACTION_NAME);
                $this->display();
            }
        }
    }

    //维修接单列表
    public function ordersLists()
    {
        if (IS_POST) {
            $action = I('post.action');
            $RepairModel = new RepairModel();
            switch ($action) {
                case 'edit_engineer':
                //修改接单工程师
                    $repid = I('post.repid');
                    $edit_engineer = I('post.edit_engineer');
                    $res = $RepairModel->updateData('repair', array('response'=>$edit_engineer), array('repid' => $repid));
                    if ($res) {
                        if ((new ModuleModel())->decide_wx_login()) {
                            // 发送转单通知给被转单人
                            $repair = (new RepairModel())->where(['repid' => $repid])->field(['departid', 'assets', 'assnum', 'repnum'])->find();
                            $repair['department'] = Department::instance()->getName($repair['departid']);

                            $user = UserModel::getUserByUsername($edit_engineer, ['openid']);
                            $redirectUrl = UrlGenerator::instance()->to('/Repair/accept?action=overhaul', ['repid' => $repid]);

                            if ($user['openid']) {
                                Weixin::instance()->sendMessage($user['openid'], '设备维修通知', [
                                    'thing3'             => $repair['department'],// 科室
                                    'thing6'             => $repair['assets'],// 设备名称
                                    'character_string12' => $repair['assnum'],// 设备编码
                                    'character_string35' => $repair['repnum'],// 维修单号
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
                    break;
                case 'getengineer':
                    $this->getengineer();
                    break;
                default:
                    $result = $RepairModel->ordersLists();
                    break;
            }
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'showRepairDetails':
                    $this->showRepairDetails();
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
        $userModel = new UserModel();
        $user = $userModel->getUsers('addRepair');
        $departids = explode(',', session('departid'));
        $departments = $this->getDepartname($departids);
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
        $this->assign('user', $user);
        $this->assign('departments', $departments);
        $this->assign('ordersListsUrl', get_url());
        $this->display();
    }

    //接单
    public function accept()
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
                case 'addTypeAndProblem':
                    //检修过程中补充故障问题故障描述
                    $RepMod = new RepairModel();
                    $result = $RepMod->addTypeAndProblem();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->doAccept();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('get.action');
            $this->assign('now_date', date('Y-m-d'));
            switch ($action) {
                case 'getAllType':
                    //搜索所有故障类型
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->getAllType();
                    $this->ajaxReturn($result, 'JSON');
                    break;
                case 'addTypeAndProblem':
                    $this->display('addTypeAndProblem');
                    break;
                case 'getRepairProblem':
                    //搜索对应故障问题
                    $parentid = I('get.parentid');
                    $repid = I('get.repid');
                    $RepairModel = new RepairModel();
                    $problem = $RepairModel->getRepairProblem($parentid);
                    //获取暂存检修时保存的故障信息
                    $old_problem = $RepairModel->get_repair_fault($repid);
                    $old_problem_id = [];
                    foreach ($old_problem as $k => $v) {
                        $old_problem_id[] = $v['fault_type_id'] . '-' . $v['fault_problem_id'];
                    }
                    foreach ($problem as $k => $v) {
                        if (in_array($v['value'], $old_problem_id)) {
                            $problem[$k]['selected'] = 'selected';
                        }
                    }
                    if ($problem) {
                        $result['code'] = 0;
                        $result['msg'] = 'success';
                        $result['data'] = $problem;
                        $this->ajaxReturn($result, 'JSON');
                    }
                    break;
                case 'getRepairType':
                    //搜索对应故障类型
                    $RepairModel = new RepairModel();
                    $type = $RepairModel->getRepairType();
                    foreach ($type as $k => $v) {
                        $type[$k]['name'] = $v['title'];
                        $type[$k]['value'] = $v['id'];
                    }
                    $this->ajaxReturn($type, 'JSON');
                    break;
                default:
                    $this->showAccept();
                    break;
            }
        }
    }

    //维修接单页面
    private function showAccept()
    {
        $repid = I('get.repid');
        $assid = I('get.assid');
        if ($assid && $repid) {
            $RepairModel = new RepairModel();
            $asModel = new AssetsInfoModel();
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($assid);
            //维修信息
            $repArr = $RepairModel->getRepairBasic($repid);
            //故障类型 故障问题数据
            $repairType = $RepairModel->get_all_repair_type();
            foreach ($repairType as &$r_v) {
                foreach ($r_v['parent'] as &$vv) {
                    $vv['value'] = $r_v['id'] . '-' . $vv['id'];
                }
            }
            //验证是否签到
            $sign_result = $RepairModel->check_sign_in($repid);
            //预计到场时间上限
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $uptime = $baseSetting['repair']['repair_uptime']['value'];
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
            //配件 个人库
            $personalPartsInfo = $RepairModel->getPartsInfo();
            $this->assign('personalPartsInfo', json_encode($personalPartsInfo));
            //第三方公司选择
            $company = $asModel->getSuppliers('repair');
            foreach ($company as $k => $v) {
                $company[$k]['status'] = 1;
                if (isset($asArr['guarantee_id'])) {
                    if ($asArr['guarantee_id'] === $v['olsid']) {
                        $asArr['salesman_name'] = $v['salesman_name'];
                        $asArr['salesman_phone'] = $v['salesman_phone'];
                    }
                }
            }
            //第三方公司联动选择
            $this->assign('companyInfo', json_encode($company));
            if ($repArr['assign_engineer'] == '' or $repArr['assign_engineer'] == session('username') or session('isSuper') == 1) {
                $showAccpet = 1;
            } else {
                $showAccpet = 0;
            }
            $show_parts = 'block';
            $this->assign('is_scene', 'block');
            $this->assign('not_scene', 'none');
            //查询是否有暂存数据
            if ($repArr['status'] == C('REPAIR_RECEIPT') && $repArr['overhaul_time']) {
                //已接单未检修，且暂存过检修信息
                $pros = $RepairModel->get_repair_fault($repid);
                $files = $RepairModel->get_repair_file($repid);
                foreach ($pros as $pro => $prv) {
                    foreach ($repairType as $k => $v) {
                        if ($prv['fault_type_id'] == $v['id']) {
                            $repairType[$k]['selected'] = 'selected';
                            foreach ($repairType[$k]['parent'] as $pp => $pv) {
                                if ($pv['id'] == $prv['fault_problem_id']) {
                                    $repairType[$k]['parent'][$pp]['selected'] = 'selected';
                                }
                            }
                        }
                    }
                }
                foreach ($files as &$v) {
                    $v['add_date'] = date('Y-m-d', strtotime($v['add_time']));
                }
                $this->assign('pros', $pros);
                $this->assign('files', $files);
                if ($repArr['is_scene'] == 0) {
                    $this->assign('is_scene', 'none');
                    $this->assign('not_scene', 'block');
                }
                if ($repArr['repair_type'] == C('REPAIR_TYPE_IS_STUDY')) {
                    //自修
                    $parts = $RepairModel->get_repair_parts($repid);
                    $this->assign('parts', $parts);
                    $this->assign('show_company', 'none');
                    $show_parts = 'block';
                }
                if ($repArr['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')) {
                    //第三方维修
                    $coms = $RepairModel->get_repair_companys($repid);
                    foreach ($coms as $v) {
                        if ($v['last_decisioin'] == 1) {
                            $this->assign('decision_reasion', $v['decision_reasion']);
                        }
                        foreach ($company as $ck => $cv) {
                            if ($v['offer_company'] == $cv['sup_name']) {
                                unset($company[$ck]);
                            }
                        }
                    }
                    $this->assign('coms', $coms);
                    $this->assign('show_company', 'block');
                    $show_parts = 'none';
                }
            }
            $this->assign('show_parts', $show_parts);
            $this->assign('company', $company);
            $this->assign('showAccpet', $showAccpet);
            $this->assign('asArr', $asArr);
            $this->assign('repArr', $repArr);
            $this->assign('repairType', $repairType);
            $this->assign('uptime', $uptime);
            $this->assign('sign_result', $sign_result);
            $this->assign('isOpenOffer_formOffer', $IS_OPEN_OFFER_DO_OFFER);
            $this->assign('acceptUrl', get_url());
            $this->display();
        } else {
            $this->error('非法参数');
        }
    }

    //统一报价列表
    public function unifiedOffer()
    {
        if (IS_POST) {
            $RepairModel = new RepairModel();
            $result = $RepairModel->unifiedOffer();
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'showRepairDetails':
                    $this->showRepairDetails();
                    break;
                default:
                    //统一报价列表页面
                    $this->showUnifiedOffer();
                    break;
            }
        }
    }

    //统一报价列表页面
    private function showUnifiedOffer()
    {
        $departids = explode(',', session('departid'));
        $departments = $this->getDepartname($departids);
        $asModel = new AssetsInfoModel();
        $user = $asModel->getUser();
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
        $this->assign('user', $user);
        $this->assign('departments', $departments);
        $this->assign('unifiedOfferUrl', get_url());
        $this->display();
    }

    //报价
    public function doOffer()
    {
        if (IS_POST) {
            $RepairModel = new RepairModel();
            $result = $RepairModel->doOffer();
            $this->ajaxReturn($result, 'json');
        } else {
            $assid = I('get.assid');
            $repid = I('get.repid');
            if ($assid && $repid) {
                $RepairModel = new RepairModel();
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($assid);
                //维修信息
                $repArr = $RepairModel->getRepairBasic($repid);
                //获取故障问题
                $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
                //第三方厂家信息
                $companyData = $RepairModel->getAllCompanysBasic($repid);
                $companyOld = $companyData['data'];
                $companyLast = $companyData['lastData'];
                //审核历史
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
                //第三方公司选择
                $asModel = new AssetsInfoModel();
                $company = $asModel->getSuppliers('repair');
                //排查已存在的公司名称
                $existOlsid = [];
                foreach ($companyOld as $v) {
                    $existOlsid[$v['offer_company_id']] = 1;
                }
                foreach ($company as $k => $v) {
                    $company[$k]['status'] = 1;
                    if ($existOlsid[$v['olsid']]) {
                        unset($company[$k]);
                    }
                }
                $this->assign('company', $company);
                //第三方公司联动选择
                $this->assign('companyInfo', json_encode($company));
                $this->assign('url', ACTION_NAME);
                $this->assign('asArr', $asArr);
                $this->assign('repArr', $repArr);
                $this->assign('approves', $approves);
                $this->assign('follow', $follow);
                $this->assign('companyOld', $companyOld);
                $this->assign('companyLast', $companyLast);
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    //维修审批列表
    public function repairApproveLists()
    {
        if (IS_POST) {
            $RepairModel = new RepairModel();
            $result = $RepairModel->repairApproveLists();
            $this->ajaxReturn($result, 'json');
        } else {
            $isOpenApprove = $this->checkApproveIsOpen(C('REPAIR_APPROVE'), session('job_hospitalid'));
            if (!$isOpenApprove) {
                $this->assign('errmsg', '维修审批未开启，如需开启，请联系管理员！');
                $this->display('Public/error');
            } else {
                $action = I('get.action');
                switch ($action) {
                    case 'showRepairDetails':
                        $this->showRepairDetails();
                        break;
                    default:
                        //审批列表页面
                        $this->showApproveLists();
                        break;
                }
            }
        }
    }

    //审批列表页面
    public function showApproveLists()
    {
        //获取部门
        $departids = explode(',', session('departid'));
        $departments = $this->getDepartname($departids);
        //查询有权进行维修审批的人
        $userModel = new UserModel();
        $users = $userModel->getUsers('addApprove', '', true, true);
        $this->assign('departments', $departments);
        $this->assign('users', $users);
        $this->assign('repairApproveListsUrl', get_url());
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
        $this->display();
    }

    //维修审批
    public function addApprove()
    {
        if (IS_POST) {
            $RepairModel = new RepairModel();
            $result = $RepairModel->doAddApprove();
            $this->ajaxReturn($result, 'json');
        } else {
            $repid = I('get.repid');
            $assid = I('get.assid');
            if ($assid && $repid) {
                $RepairModel = new RepairModel();
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($assid);
                //维修信息
                $repArr = $RepairModel->getRepairBasic($repid);
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
                //******************************审批流程显示 start***************************//
                $repArr = $RepairModel->get_approves_progress($repArr, 'repid', 'repid');
                //**************************************审批流程显示 end*****************************//

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
                $this->assign('url', ACTION_NAME);
                $this->assign('asArr', $asArr);
                $this->assign('repArr', $repArr);
                $this->assign('approves', $approves);
                $this->assign('follow', $follow);
                $this->assign('parts', $parts);
                $this->assign('company', $company);
                $this->assign('companyLast', $companyLast);
                $this->assign('username', session('username'));

                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    //设备维修列表
    public function getRepairLists()
    {
        if (IS_POST) {
            $RepairModel = new RepairModel();
            $result = $RepairModel->getRepairLists();
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'showRepairDetails':
                    $this->showRepairDetails();
                    break;
                default:
                    //设备维修列表页面
                    $this->showGetRepairLists();
                    break;
            }
        }
    }

    //设备维修列表页面
    private function showGetRepairLists()
    {
        $departids = explode(',', session('departid'));
        $departments = $this->getDepartname($departids);
        //获取所属部门人员信息
        $userModel = new UserModel();
        $user = $userModel->getUsers('addRepair');
        $this->assign('departments', $departments);
        $this->assign('users', $user);
        $this->assign('getRepairListsUrl', get_url());
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
        $this->display();
    }

    //开始维修
    public function startRepair()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'upload':
                    //附件上传
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->uploadfile();
                    $result['file_url'] = $result['path'];
                    $result['file_name'] = $result['formerly'];
                    $result['file_type'] = $result['ext'];
                    $result['add_user'] = session('username');
                    $result['add_time'] = getHandleDate(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->doStartRepair();
                    $this->ajaxReturn($result, 'json');
            }
        } else {
            $repid = I('get.repid');
            $assid = I('get.assid');
            if ($repid && $assid) {
                $RepairModel = new RepairModel();
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($assid);
                //维修信息
                $repArr = $RepairModel->getRepairBasic($repid);
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
                    $this->assign('files', $files);
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
                //辅助工程师
                $UserModel = new UserModel();
                $user = $UserModel->getUsers('accept', $asArr['departid']);
                //下一条跟进时间
                $maxtime = $RepairModel->DB_get_one('repair_follow', 'MAX(followdate) AS followdate', array('repid' => $repid));
                //是否由系统生成字段
                $issystem = $RepairModel->DB_get_one('base_setting', 'value', array('set_item' => 'repair_system'));
                $issystem = json_decode($issystem['value'], true);
                if ($repArr['status'] == C('REPAIR_MAINTENANCE') && isset($issystem['service_working'])) {
                    //todo  维修工时计算：通知时间至当前时间;
                    $repArr['working_hours'] = timediff(strtotime($repArr['response_date']), time());
                } else {
                    $repArr['working_hours'] = timediff(strtotime($repArr['response_date']), time());
                }
                if (!isset($issystem['service_working'])) {
                    if ($repArr['engineer_time']) {
                        $repArr['engineer_time'] = getHandleDate(strtotime($repArr['engineer_time']));
                    } else {
                        $repArr['engineer_time'] = getHandleDate(time());
                    }
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

                //第三方公司选择
                $asModel = new AssetsInfoModel();
                $companyA = $asModel->getSuppliers('repair');
                foreach ($companyA as $k => $v) {
                    $companyA[$k]['status'] = 1;
                    if (isset($asArr['guarantee_id'])) {
                        if ($asArr['guarantee_id'] === $v['olsid']) {
                            $asArr['sup_name'] = $v['sup_name'];
                            $asArr['salesman_name'] = $v['salesman_name'];
                            $asArr['salesman_phone'] = $v['salesman_phone'];
                        }
                    }
                }
                $this->assign('personalPartsInfo', json_encode($personalPartsInfo));
                $this->assign('service_date', $issystem['service_date']);
                $this->assign('service_working', $issystem['service_working']);
                $this->assign('maxtime', getHandleTime($maxtime['followdate']));
                $this->assign('user', $user);
                $this->assign('userJson', json_encode($user));
                $this->assign('asArr', $asArr);
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

    //获取修复、验收设备列表
    public function examine()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'uploadReport':
                    //本地上传
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->uploadfile();
                    $result = $RepairModel->addCheckFile($result);
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'deleteFile':
                    //移除附件
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->deleteFile();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'batchPrint':
                    //批量打印模板
                    $this->batchPrintReport();
                    break;
                default:
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->examine();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'showRepairDetails':
                    $this->showRepairDetails();
                    break;
                case 'printReport':
                    //打印模板
                    $this->showPrintReport();
                    break;
                case 'uploadReport':
                    //上传、查看报告
                    $this->uploadReport();
                    break;
                default:
                    //设备验收页面
                    $this->showExamine();
                    break;
            }
        }
    }

    //上传、查看报告
    private function uploadReport()
    {
        $repid = I('GET.repid');
        //查询对应的转科报告
        $RepairModel = new RepairModel();
        $files = $RepairModel->getRepairFileBasic($repid, 'report');

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
        $url = "$protocol" . C('HTTP_HOST') . C('ADMIN_NAME') . '/Public/uploadReport?id=' . $repid . '&i=repid&t=repair_file&username=' . session('username');
        $codeUrl = $RepairModel->createCodePic($url);
        $codeUrl = trim($codeUrl, '.');
        $this->assign('codeUrl', $codeUrl);
        $this->assign('uploadinfo', $files);
        $this->assign('id', $repid);
        $this->assign('idName', 'rep');
        $this->assign('uploadAction', get_url());
        $this->display('Public/uploadReport');
    }


    //打印模板页面
    private function showPrintReport()
    {
        //对于维修配置 仅预览
        $onlyShow = I('get.onlyShow');
        $assid = I('get.assid');
        $repid = I('get.repid');
        $baseSetting = array();
        include APP_PATH . "Common/cache/basesetting.cache.php";
        if ($assid && $repid) {
            $RepairModel = new RepairModel();
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($assid);
            //维修信息
            $repArr = $RepairModel->getRepairBasic($repid);
            //获取维修人员的签名
            $repautograph = $RepairModel->get_autograph($repArr['response']);
            //获取设备科长的签名
            $userModel = new UserModel();
            $departautograph = $userModel->get_assets_autograph();
            //获取验收人的签名
            $checkautograph = $RepairModel->get_autograph($repArr['checkperson']);
            //获取故障问题
            $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
            //第三方厂家信息
            $companyData = $RepairModel->getAllCompanysBasic($repid);
            $companyLast = $companyData['lastData'];
            //配件/服务
            $parts = $RepairModel->getAllPartsBasic($repid);
            //审核历史
            $approves = $RepairModel->getApproveBasic($repid);
            //维修跟进信息
            $follow = $RepairModel->getRepirFollowBasic($repid);
            //是否有权限查看上传文件
            $upload = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
            if ($upload) {
                //获取上传的文件
                $files = $RepairModel->getRepairFileBasic($repid);
                $this->assign('files', $files);
            }
            $title = $RepairModel->getprinttitle('repair', 'repair_template');
            $this->assign('title', $title);
            $this->assign('asArr', $asArr);
            $this->assign('repautograph', $repautograph);
            $this->assign('departautograph', $departautograph);
            $this->assign('checkautograph', $checkautograph);
            $this->assign('repautograph_time', substr($repArr['checkdate'], 0, 10));
            $this->assign('departautograph_time', substr($repArr['engineer_time'], 0, 10));
            $this->assign('checkautograph_time', substr($repArr['engineer_time'], 0, 10));
            $this->assign('repArr', $repArr);
            $this->assign('approves', $approves);
            $this->assign('date', getHandleTime(time()));
            $this->assign('follow', $follow);
            $this->assign('parts', $parts);
            $this->assign('companyLast', $companyLast);
            //根据系统设置选择模板
            $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
            switch ($baseSetting['repair']['repair_template']['value']['version']) {
                case 1:
                    $this->display('Repair/ReportTemplate/checkTemplate');
                    break;
                case 2:
                    $this->display('Repair/ReportTemplate/checkTemplate1');
                    break;
            }
        } elseif ($onlyShow) {
            $this->assign('onlyShow', $onlyShow);
            $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
            switch ($onlyShow) {
                case 1:
                    $this->display('Repair/ReportTemplate/checkTemplate');
                    break;
                case 2:
                    $this->display('Repair/ReportTemplate/checkTemplate1');
                    break;
            }
        } else {
            $this->error('非法操作');
        }
    }

    //批量打印报告
    private function batchPrintReport()
    {
        $repairModel = new RepairModel();
        $userModel = new UserModel();
        $repidStr = I('post.repid');
        $repidStr = trim($repidStr, ',');
        $repidArr = explode(',', $repidStr);
        $html = '';
        foreach ($repidArr as $k => $v) {
            $repid = $v;
            //维修信息
            $repArr = $repairModel->getRepairBasic($repid);
            $assid = $repArr['assid'];
            //设备信息
            $asArr = $repairModel->getAssetsBasic($assid);
            //获取维修人员的签名
            $repautograph = $repairModel->get_autograph($repArr['response']);
            //获取设备科长的签名
            $departautograph = $repairModel->get_assets_autograph();
            //获取验收人的签名
            $checkautograph = $repairModel->get_autograph($repArr['checkperson']);
            //获取故障问题
            $repArr['fault_problem'] = $repairModel->getFaultProblem($repid);
            //第三方厂家信息
            $companyData = $repairModel->getAllCompanysBasic($repid);
            $companyLast = $companyData['lastData'];
            //配件/服务
            $parts = $repairModel->getAllPartsBasic($repid);
            //审核历史
            $approves = $repairModel->getApproveBasic($repid);
            if ($companyLast && count($parts) >= 2) {
                $this->assign('pages', 2);//需要两页的打印空间
            } elseif ($companyLast && count($approves) >= 2) {
                $this->assign('pages', 2);//需要两页的打印空间
            } elseif ($companyLast && count(explode('<br>', $repArr['fault_problem'])) >= 2) {
                $this->assign('pages', 2);//需要两页的打印空间
            } elseif (!$companyLast && (count(explode('<br>', $repArr['fault_problem'])) + count($parts) + count($approves)) >= 8) {
                $this->assign('pages', 2);//需要两页的打印空间
            } else {
                $this->assign('pages', 1);
            }
            //维修跟进信息
            $follow = $repairModel->getRepirFollowBasic($repid);
            //是否是否有权限查看上传文件
            $upload = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
            if ($upload) {
                //获取上传的文件
                $files = $repairModel->getRepairFileBasic($repid);
                $this->assign('files', $files);
            }
            $this->assign('repautograph', $repautograph);
            $this->assign('departautograph', $departautograph);
            $this->assign('checkautograph', $checkautograph);
            $this->assign('repautograph_time', substr($repArr['checkdate'], 0, 10));
            $this->assign('departautograph_time', substr($repArr['engineer_time'], 0, 10));
            $this->assign('checkautograph_time', substr($repArr['engineer_time'], 0, 10));
            $this->assign('asArr', $asArr);
            $this->assign('repArr', $repArr);
            $this->assign('approves', $approves);
            $this->assign('date', getHandleTime(time()));
            $this->assign('follow', $follow);
            $this->assign('parts', $parts);
            $this->assign('companyLast', $companyLast);
            $marget_top = ($k + 2) % 2 == 0 ? 0 : 10;
            $this->assign('marget_top', $marget_top);
            $baseSetting = array();
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
            $title = $repairModel->getprinttitle('repair', 'repair_template');
            $this->assign('title', $title);
            switch ($baseSetting['repair']['repair_template']['value']['version']) {
                case 1:
                    $html .= $this->display('Repair/ReportTemplate/batch_print_report');
                    break;
                case 2:
                    $html .= $this->display('Repair/ReportTemplate/batch_print_report1');
                    break;
            }

        }
        echo $html;
        exit;
    }

    //设备验收页面
    private function showExamine()
    {
        $userModel = new UserModel();
        $user = $userModel->getUsers('accept','',true);
        $this->assign('user', $user);
        $departids = explode(',', session('departid'));
        $departments = $this->getDepartname($departids);
        $this->assign('departments', $departments);
        $this->assign('examineUrl', get_url());
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
        $this->display();

    }


    //验收设备
    public function checkRepair()
    {
        if (IS_POST) {
            $RepairModel = new RepairModel();
            $result = $RepairModel->checkRepair();
            $this->ajaxReturn($result, 'json');
        } else {
            $repid = I('get.repid');
            $assid = I('get.assid');
            if ($assid && $repid) {
                $RepairModel = new RepairModel();
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($assid);
                //维修信息
                $repArr = $RepairModel->getRepairBasic($repid);
                $repArr['over_time'] = date('Y-m-d H:i:s', $repArr['overdate']);
                //是否由系统生成字段
                $issystem = $RepairModel->DB_get_one('base_setting', 'value', array('set_item' => 'repair_system'));
                $issystem = json_decode($issystem['value'], true);
                $this->assign('repair_check', $issystem['repair_check']);
                $this->assign('user', session('username'));
                $this->assign('asArr', $asArr);
                $this->assign('repArr', $repArr);
                $this->assign('url', ACTION_NAME);
                $this->display();

            } else {
                $this->error('非法操作');
            }
        }
    }

    //维修进程
    public function progress()
    {
        if (IS_POST) {
            $RepairModel = new RepairModel();
            $result = $RepairModel->progress();
            $this->ajaxReturn($result, 'json');
        } else {
            //获取所属部门信息
            $departids = explode(',', session('departid'));
            $departments = $this->getDepartname($departids);
            $userModel = new UserModel();
            $user = $userModel->getUsers('addRepair', '', true, true);
            //读取配置文件维修状态设置
            $repairStatus = array(
                '9' => '派工',
                '1' => '接单',
                '2' => '检修',
                '4' => '维修审核',
                '5' => '维修中',
                '6' => '科室验收',
            );
            $this->assign('users', $user);
            $this->assign('departments', $departments);
            $this->assign('repairStatus', $repairStatus);
            $this->assign('url', get_url());
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
            $this->display();
        }
    }

    function checkIsApprove($alreadyAppUser, $app)
    {
        $apModel = new ApproveProcessModel();
        //查询当前用户在该审批流程中的审批排序
        $myorder = $apModel->DB_get_one('approve_detail', 'listorder', array('processid' => $app[0]['processid'], 'approve_user' => session('username')));
        //查询当前用户及当前用户前一个审批的人
        $myandpre = $apModel->DB_get_all('approve_detail', 'detailid,approve_user,listorder', array('processid' => $app[0]['processid'], 'listorder' => array('ELT', $myorder['listorder'])), '', 'listorder desc,detailid desc', '2');
        if ($myandpre[1]['approve_user']) {
            if (!in_array($myandpre[1]['approve_user'], $alreadyAppUser)) {
                //当前用户的前一审批人还没审批
                return false;
            } elseif (!in_array($myandpre[0]['approve_user'], $alreadyAppUser)) {
                //当前用户的前一审批人已审批
                return true;
            } else {
                return false;
            }
        } else {
            if (!in_array($myandpre[0]['approve_user'], $alreadyAppUser)) {
                //未审批
                return true;
            } else {
                return false;
            }
        }
    }

    public function importRepair()
    {
        $type = I('get.type');
        if ($type == 'exportRepairModel') {
            //导出模板
            $xlsName = "repair";
            $xlsCell = array('资产名称', '资产编号', '规格型号', '申报人', '申报时间', '申报人电话', '故障描述', '接单人', '接单时间', '接单人电话',
                '维修工程师', '维修工程师电话', '协助工程师', '协助工程师电话', '开始维修时间', '结束维修时间', '实际维修费用', '处理详情', '验收人', '验收时间', '验收意见及建议');
            //单元格宽度设置
            $width = array(
                '资产名称' => '30',//字符数长度
                '资产编号' => '20',//字符数长度
                '规格型号' => '20',
                '申报人' => '20',
                '申报时间' => '20',
                '申报人电话' => '25',
                '故障描述' => '50',
                '接单人' => '20',
                '接单时间' => '20',
                '接单人电话' => '20',
                '维修工程师' => '20',
                '维修工程师电话' => '20',
                '协助工程师' => '20',
                '协助工程师电话' => '20',
                '开始维修时间' => '20',
                '结束维修时间' => '20',
                '实际维修费用' => '20',
                '处理详情' => '50',
                '验收人' => '20',
                '验收时间' => '20',
                '验收意见及建议' => '50',
            );
            //单元格颜色设置（例如必填行单元格字体颜色为红色）
            $color = array(
                '资产编号' => 'FF0000',//颜色代码
                '申报人' => 'FF0000',
                '申报时间' => 'FF0000',
                '申报人电话' => 'FF0000',
                '故障描述' => 'FF0000',
                '接单人' => 'FF0000',
                '接单时间' => 'FF0000',
                '接单人电话' => 'FF0000',
                '维修工程师' => 'FF0000',
                '维修工程师电话' => 'FF0000',
                '开始维修时间' => 'FF0000',
                '结束维修时间' => 'FF0000',
                '处理详情' => 'FF0000',
                '验收人' => 'FF0000',
                '验收时间' => 'FF0000',
            );
            Excel('维修单导入模板', $xlsName, $xlsCell, $width, $color, array(), array(), array(), array(), array('repair' => array('parts' => 1)));
        }
        $this->display();
    }


    /*
     * 批量导入修单
     */
    public function batchAddRepair()
    {
        if (IS_POST) {
            $type = I('POST.type');
            if ($type == 'save') {
                $reModel = new RepairModel();
                $res = $reModel->addRepair();
                $this->ajaxReturn($res);
            } else {
                $reModel = new RepairModel();

                if (!empty($_FILES)) {
                    $uploadConfig = array(
                        'maxSize' => 3145728,
                        'rootPath' => './Public/',
                        'savePath' => 'uploads/',
                        'saveName' => array('uniqid', ''),
                        'exts' => array('xlsx', 'xls', 'xlsm'),
                        'autoSub' => true,
                        'subName' => array('date', 'Ymd'),
                    );
                    $upload = new \Think\Upload($uploadConfig);
                    //var_dump($upload);exit;
                    $info = $upload->upload();
                   // var_dump($info);exit;
                    if (!$info) {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '导入数据出错'));
                    }
                    vendor("PHPExcel.PHPExcel");
                    $filePath = $upload->rootPath . $info['file']['savepath'] . $info['file']['savename'];
                    if (empty($filePath) or !file_exists($filePath)) {
                        die('file not exists');
                    }
                    $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
                    if (!$PHPReader->canRead($filePath)) {
                        $PHPReader = new \PHPExcel_Reader_Excel5();
                        if (!$PHPReader->canRead($filePath)) {
                            $this->ajaxReturn(array('status' => -1, 'msg' => '文件格式错误'));
                        }
                    }

                    $excelDate = new \PHPExcel_Shared_Date();
                    $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
                    $currentSheet = $PHPExcel->getSheet(0);        //**读取excel文件中的指定工作表*/
                    $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/

                    ++$allColumn;
                    $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
                    //echo $allRow;die;
                    $data = array();
                    $cellname = array(
                        'A' => 'assets',
                        'B' => 'assnum',
                        'C' => 'applicant',
                        'D' => 'applicant_day',
                        'E' => 'applicant_time',
                        'F' => 'applicant_tel',
                        'G' => 'faultProblem',
                        'H' => 'breakdown',
                        'I' => 'response',
                        'J' => 'response_date',
                        'K' => 'response_tel',
                        'L' => 'engineer',
                        'M' => 'engineer_tel',
                        'N' => 'assist_engineer',
                        'O' => 'assist_engineer_tel',
                        'P' => 'engineer_time',
                        'Q' => 'overdate_day',
                        'R' => 'overdate_time',
                        'S' => 'actual_price',
                        'T' => 'dispose_detail',
                        'U' => 'checkperson',
                        'V' => 'checkdate',
                    );
                    //需要进行日期处理的保存在一个数组
                    $toDate = array(
                        'applicant_day',

                        'overdate_day',
                        'checkdate'
                    );
                    //需要进行时间处理的保存在一个数组
                    $toDateTime = array(
                        'response_date',
                        'applicant_time',
                        'engineer_time',
                        'overdate_time'
                    );
                    for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
                        for ($colIndex = 'A'; $colIndex != $allColumn; $colIndex++) {
                            $addr = $colIndex . $rowIndex;
                            //echo $addr;die;
                            if (in_array($cellname[$colIndex], $toDate)) {
                                if (!$currentSheet->getCell($addr)->getValue()) {
                                    $cell = '';
                                } else {
                                    $cell = gmdate("Y-m-d", $excelDate::ExcelToPHP($currentSheet->getCell($addr)->getValue()));
                                }
                            } else {
                                $cell = $currentSheet->getCell($addr)->getValue();
                            }
                            if ($cell instanceof \PHPExcel_RichText) { //富文本转换字符串
                                $cell = $cell->__toString();
                            }
                            if ($cellname[$colIndex] == 'assnum') {
                                if (!$cell) {
                                    continue;
                                }
                            }
                            if (in_array($cellname[$colIndex], $toDateTime)) {
                                if (!$currentSheet->getCell($addr)->getValue()) {
                                    $cell = '';
                                } else {
                                    $cell = gmdate("H:i", $excelDate::ExcelToPHP($currentSheet->getCell($addr)->getValue()));
                                }
                            }
                            $data[$rowIndex - 2][$cellname[$colIndex]] = $cell ? $cell : '';
                        }
                    }

                    if (!$data) {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '导入数据失败'));
                    }
                    //只返回特定条数数据
                    $returnLimitDate = $reModel->returnLimitDate($data);
                    //读取配件信息
                    $currentSheet = $PHPExcel->getSheet(1);        //**读取excel文件中的指定工作表*/

                    $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
                    //echo $allColumn;die;
                    ++$allColumn;
                    $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
                    $parts = array();
                    $cellname = array('A' => 'assnum', 'B' => 'bindung', 'C' => 'parts', 'D' => 'part_model', 'E' => 'part_price', 'F' => 'part_num');
                    for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
                        for ($colIndex = 'A'; $colIndex != $allColumn; $colIndex++) {
                            $addr = $colIndex . $rowIndex;
                            if (in_array($cellname[$colIndex], $toDate)) {
                                if (!$currentSheet->getCell($addr)->getValue()) {
                                    $cell = '';
                                } else {
                                    $cell = gmdate("Y-m-d", $excelDate::ExcelToPHP($currentSheet->getCell($addr)->getValue()));
                                }
                            } else {
                                $cell = $currentSheet->getCell($addr)->getValue();
                            }
                            if ($cell instanceof \PHPExcel_RichText) { //富文本转换字符串
                                $cell = $cell->__toString();
                            }
                            if ($cellname[$colIndex] == 'assnum') {
                                if (!$cell) {
                                    continue;
                                }
                            }
                            $parts[$rowIndex - 2][$cellname[$colIndex]] = $cell ? $cell : '';
                        }
                    }
                    unlink($filePath);
                    $return['status'] = "1";
                    $return['msg'] = '导入数据成功';
                    $return['data'] = $returnLimitDate;
                    //只返回对应的配件数据
                    $parts = $reModel->returnLimitPatarts($returnLimitDate, $parts);
                    //var_dump($parts);exit;
                    $return['parts'] = $parts;
                    $return = json_encode($return, JSON_UNESCAPED_UNICODE);
                    header('Content-Length: ' . strlen($return));
                    header('Content-Type: text/html; charset=utf-8');
                    echo $return;
                    exit;
                } else {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '请上传文件'));
                }
            }
        } else {
            $type = I('get.type');
            if ($type == 'exportRepairModel') {
                //导出模板
                $xlsName = "repair";
                $xlsCell = array('设备名称', '设备编号', '申报人', '申报日期', '申报时间', '申报人电话', '故障问题（多个问题用&符号分隔）', '故障描述', '接单人', '接单时间', '接单人电话',
                    '维修工程师', '维修工程师电话', '协助工程师', '协助工程师电话', '开始维修时间', '维修结束日期', '维修结束时间', '实际维修费用', '处理详情', '验收人', '验收日期');
                //单元格宽度设置
                $width = array(
                    '设备名称' => '30',//字符数长度
                    '设备编号' => '20',//字符数长度
                    '申报人' => '20',
                    '申报日期' => '20',
                    '申报时间' => '20',
                    '申报人电话' => '25',
                    '故障问题（多个问题用&符号分隔）' => '50',
                    '故障描述' => '50',
                    '接单人' => '20',
                    '接单时间' => '20',
                    '接单人电话' => '20',
                    '维修工程师' => '20',
                    '维修工程师电话' => '20',
                    '协助工程师' => '20',
                    '协助工程师电话' => '20',
                    '开始维修时间' => '20',
                    '维修结束日期' => '20',
                    '维修结束时间' => '20',
                    '实际维修费用' => '20',
                    '处理详情' => '50',
                    '验收人' => '20',
                    '验收日期' => '20',
                );
                //单元格颜色设置（例如必填行单元格字体颜色为红色）
                $color = array(
                    '设备名称' => 'FF0000',//颜色代码
                    '设备编号' => 'FF0000',
                    '申报人' => 'FF0000',
                    '申报日期' => 'FF0000',
                    '申报时间' => 'FF0000',
                    '故障问题（多个问题用&符号分隔）' => 'FF0000',
                    '故障描述' => 'FF0000',
                    '接单人' => 'FF0000',
                    '接单时间' => 'FF0000',
                    '维修工程师' => 'FF0000',
                    '开始维修时间' => 'FF0000',
                    '维修结束日期' => 'FF0000',
                    '维修结束时间' => 'FF0000',
                    '处理详情' => 'FF0000',
                    '验收人' => 'FF0000',
                    '验收日期' => 'FF0000',
                );
                Excel('维修单导入模板', $xlsName, $xlsCell, $width, $color, array(), array(), array(), array(), array('repair' => array('parts' => 1)));
            }
            $reModel = new RepairModel();
            //查询数据库已有设备信息
            $assnum = $reModel->DB_get_all('assets_info', 'assnum', '', '', '');
            $this->assign('assnum', json_encode($assnum, JSON_UNESCAPED_UNICODE));
            $this->display();
        }
    }

    public function repairForm()
    {
        $repairModel = new RepairModel();
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                default:
                    $this->ajaxReturn($repairModel->addRepairForm());
                    break;
            }
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'getAssetsInfo':
                    //获取单台设备信息
                    $assid = I('get.assid');
                    if (isset($assid) && !empty($assid)) {
                        $this->ajaxReturn($repairModel->getAssetsBasic($assid));
                    }
                    break;
                case 'getRepairProblem':
                    $parentid = I('get.parentid');
                    //对应故障问题
                    if (isset($parentid) && !empty($parentid)) {
                        $this->ajaxReturn(['code' => 0, 'msg' => 'success', 'data' => $repairModel->getRepairProblem($parentid)]);
                    }
                    break;
                default:
                    //维修类别
                    $baseSetting = [];
                    include APP_PATH . "Common/cache/basesetting.cache.php";
                    $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
                    //故障类型 故障问题数据
                    $repairType = $repairModel->get_all_repair_type();
                    foreach ($repairType as &$r_v) {
                        foreach ($r_v['parent'] as &$vv) {
                            $vv['value'] = $r_v['id'] . '-' . $vv['id'];
                        }
                    }
                    $this->assign('repairType', $repairType);
                    //第三方公司
                    $asModel = new AssetsInfoModel();
                    $company = $asModel->getSuppliers('repair');
                    $this->assign('company', $company);
                    $this->display();
                    break;
            }
        }
    }
}
