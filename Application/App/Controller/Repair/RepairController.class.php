<?php

namespace App\Controller\Repair;

use Admin\Controller\Tool\ToolController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\ModuleModel;
use Admin\Model\OfflineSuppliersModel;
use App\Controller\Login\IndexController;
use App\Model\RepairModel;
use App\Model\WxAccessTokenModel;
use Common\Support\UrlGenerator;
use Common\Weixin\Weixin;

class RepairController extends IndexController
{
    private $MODULE = 'Repair';
    private $Controller = 'Repair';
    protected $index_url = 'Index/testindex.html';//首页地址
    protected $examine_url = 'Repair/examine.html';//验收设备列表地址

    //维修接单列表
    public function ordersLists()
    {
        $action = I('get.action');
        switch ($action) {
            case 'overhaulLists':
                $RepairModel = new RepairModel();
                $result      = $RepairModel->overhaulLists();
                $this->ajaxReturn($result, 'json');
                break;
            default:
                $RepairModel = new RepairModel();
                $result      = $RepairModel->ordersLists();
                $this->ajaxReturn($result, 'json');
                break;
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
        $jssdk             = new WxAccessTokenModel();
        $signPackage       = $jssdk->GetSignPackage();
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
            $repArr            = $RepairModel->getRepairBasic($repid);
            $repArr['pic_url'] = json_decode($repArr['pic_url']);
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
            //获取故障问题
            $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
            //第三方厂家信息
            $companyData = $RepairModel->getAllCompanysBasic($repid);
            $company     = $companyData['data'];
            $companyLast = $companyData['lastData'];
            //配件/服务
            $parts = $RepairModel->getAllPartsBasic($repid);
            //审核历史action:overhaulLists
            $approves = $RepairModel->getApproveBasic($repid);
            foreach ($approves as $key => $value) {
                if ($value['is_adopt'] == 1) {
                    $approves[$key]['opinion'] = '<span style="color:green">通过</span>';
                } else {
                    $approves[$key]['opinion'] = '<span style="color:red">不通过</span>';
                }
            }
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
                    $IS_OPEN_OFFER          = C('NOT_DO_STATUS');
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
            $repairTimeLine    = $repairTimeRestult['repairTimeLineData'];
            foreach ($repairTimeLine as $k => $v) {
                if ($repairTimeLine[$k]['date'] == '-') {
                    unset($repairTimeLine[$k]);
                }
            }
            $repairTimeLine = array_unique($repairTimeLine, SORT_REGULAR);
            $repairTimeLine = $RepairModel->getUserNameAndPhone($repairTimeLine);
            /*$this->assign('repairTimeLine', array_reverse($repairTimeLine));
            $this->assign('approves', $approves);
            $this->assign('follow', $follow);
            $this->assign('parts', $parts);
            $this->assign('company', $company);
            $this->assign('companyLast', $companyLast);
            $this->assign('isOpenOffer', $IS_OPEN_OFFER);
            $this->assign('isOpenOffer_formOffer', $IS_OPEN_OFFER_DO_OFFER);*/
            $result['parts']    = $parts;
            $result['company']  = $company;
            $result['approves'] = $approves;
            $result['repArr']   = array_merge($asArr, $repArr);
            $result['status']   = 1;
            $result['life']     = array_reverse($repairTimeLine);
            $this->ajaxReturn($result, 'json');
        } else {
            $this->error('非法操作');
        }
    }

    //报修页面
    public function addRepair()
    {
        if (IS_POST) {
            $action    = I('post.action');
            $RepairMod = new \Admin\Model\RepairModel();
            switch ($action) {
                case 'upload':
                    //文件上传
                    $RepairModel        = new RepairModel();
                    $result             = $RepairModel->uploadfile();
                    $result['adduser']  = session('username');
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
            //验证
            $RepairModel = new RepairModel();
            $result      = $RepairModel->check_baoxiu();
            if ($result['status'] != 1) {
                //不可报修，返回错误
                $this->ajaxReturn($result, 'json');
            }
            $RepairMod = new \Admin\Model\RepairModel();
            $assid     = $result['assid'];
            $asArr     = $RepairMod->getAssetsBasic($assid);
            //组织表单第三部分厂商信息
            $OfflineSuppliersModel  = new OfflineSuppliersModel();
            $offlineSuppliers       = $OfflineSuppliersModel->DB_get_one('assets_factory',
                'ols_facid,ols_supid,ols_repid', ['assid' => $asArr['assid']]);
            $factoryInfo            = [];
            $supplierInfo           = [];
            $repairInfo             = [];
            $offlineSuppliersFields = 'olsid,sup_name,salesman_name,salesman_phone,artisan_name,artisan_phone';
            if ($offlineSuppliers['ols_facid']) {
                $factoryInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields,
                    ['olsid' => $offlineSuppliers['ols_facid']]);
            }
            if ($offlineSuppliers['ols_supid']) {
                $supplierInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields,
                    ['olsid' => $offlineSuppliers['ols_supid']]);
            }
            if ($offlineSuppliers['ols_repid']) {
                $repairInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields,
                    ['olsid' => $offlineSuppliers['ols_repid']]);
            }
            $asArr['factoryInfo']  = $factoryInfo;
            $asArr['supplierInfo'] = $supplierInfo;
            $asArr['repairInfo']   = $repairInfo;
            $result['status']      = 1;
            $result['asArr']       = $asArr;
            //是否由系统生成字段
            $issystem           = $RepairMod->DB_get_one('base_setting', 'value', ['set_item' => 'repair_system']);
            $issystem           = json_decode($issystem['value'], true);
            $result['issystem'] = $issystem;
            if ($issystem['repair_date'] != 1) {
                $result['maxDate']     = date('Y-m-d');
                $result['repair_date'] = date('Y-m-d H:i');
            }
            if ($issystem['repair_person'] != 1) {
                //$result['repair_person'] = session('username');
            }
            if ($issystem['repair_phone'] != 1) {
                //$result['repair_phone'] = session('telephone');
            }
            $this->ajaxReturn($result, 'json');
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
                    $result      = $RepairModel->scanQRcode_jianxiu();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'upload':
                    //文件上传
                    $RepairModel        = new RepairModel();
                    $result             = $RepairModel->uploadfile();
                    $result['adduser']  = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'accept':
                    //接单操作
                    $RepairModel = new RepairModel();
                    $result      = $RepairModel->accept();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'overhaul':
                    //检修操作
                    $RepairModel = new \Admin\Model\RepairModel();
                    $result      = $RepairModel->doAccept();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'tmp_save':
                    //检修操作
                    $RepairModel = new \Admin\Model\RepairModel();
                    $result      = $RepairModel->doAccept();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'sign_in'://签到
                    $repModel = new RepairModel();
                    $result   = $repModel->scanQRCode_signin();
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
                    $repInfo = $RepairModel->DB_get_one('repair', 'repnum,assets,assnum,departid,breakdown',
                        ['repid' => $repid]);
                    if (!$repInfo) {
                        $result['status'] = -1;
                        $result['msg']    = '查找不到维修单信息';
                    } else {
                        $departname = [];
                        include APP_PATH . "Common/cache/department.cache.php";
                        $repInfo['department'] = $departname[$repInfo['departid']]['department'];
                        $edit_engineer         = I('post.edit_engineer');
                        //查询被转单人电话
                        $user = $RepairModel->DB_get_one('user', 'telephone,openid', ['username' => $edit_engineer]);
                        $res  = $RepairModel->updateData('repair',
                            ['response' => $edit_engineer, 'response_tel' => $user['telephone']], ['repid' => $repid]);
                        if ($res) {
                            $moduleModel = new ModuleModel();
                            $wx_status   = $moduleModel->decide_wx_login();
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
                            $result['status'] = 1;
                            $result['msg']    = '转单成功';
                        } else {
                            $result['status'] = -1;
                            $result['msg']    = '转单失败';
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
                    $result      = $RepairModel->getAllType();
                    $this->ajaxReturn($result, 'JSON');
                    break;
                case 'addTypeAndProblem':
                    $this->display('addTypeAndProblem');
                    break;
                case 'getRepairProblem':
                    //搜索对应故障问题
                    $parentid    = I('get.parentid');
                    $RepairModel = new \Admin\Model\RepairModel();
                    $problem     = $RepairModel->getRepairProblem($parentid);
                    if ($problem) {
                        $result['code'] = 0;
                        $result['msg']  = 'success';
                        $result['data'] = $problem;
                        $this->ajaxReturn($result, 'JSON');
                    }
                    break;
                case 'getRepairType':
                    //搜索对应故障类型
                    $RepairModel = new \Admin\Model\RepairModel();
                    $type        = $RepairModel->getRepairType();
                    foreach ($type as $k => $v) {
                        $type[$k]['name']  = $v['title'];
                        $type[$k]['value'] = $v['id'];
                    }
                    $this->ajaxReturn($type, 'JSON');
                    break;
                case 'overhaul':
                    $this->showOverhaul();
                    break;
                default:
                    $result = $this->showAccept();
                    $this->ajaxReturn($result, 'JSON');
                    break;
            }
        }
    }

    //获取拥有维修该科室权限的工程师
    private function getengineer()
    {
        $departid           = I('POST.departid');
        $repid              = I('POST.repid');
        $user               = ToolController::getUser('accept', $departid, false);
        $RepairMod          = new \Admin\Model\RepairModel();
        $data               = $RepairMod->DB_get_one('repair', 'response', ['repid' => $repid]);
        $result['status']   = 1;
        $result['data']     = array_column($user, 'username');
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
                $result['msg']    = '参数非法，请稍联系管理员！';
                $result['status'] = -1;
                $this->ajaxReturn($result, 'JSON');
            }
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
            $asArr = $this->add_ols($asArr);
            //故障类型 故障问题数据
            //$repairType = $RepairModel->get_all_repair_type();
            //验证是否签到
            //$sign_result = $RepairModel->check_sign_in($repid);
            //预计到场时间上限
            $baseSetting = [];
            $is_display  = 0;
            include APP_PATH . "Common/cache/basesetting.cache.php";
            if ($repArr['status'] != '1') {
                $is_display = 0;
            } else {
                $is_display = 1;
            }
            $result['is_display'] = $is_display;
            //$uptime = $baseSetting['repair']['repair_uptime']['value'];
            $result['status']        = 1;
            $result['asArr']         = $asArr;
            $repArr['pic_url']       = json_decode($repArr['pic_url']);
            $result['repArr']        = $repArr;
            $result['response']      = session('username');
            $result['response_date'] = getHandleMinute(time());
            return $result;
        } else {
            $this->error('非法参数');
        }
    }

    //检修页面
    public function showOverhaul()
    {
        $repid  = I('GET.repid');
        $assnum = I('GET.assnum');
        if (!$repid) {
            //扫码进来
            if (!$assnum) {
                $result['status'] = 302;
                $msg['tips']      = '参数错误';
                $msg['url']       = '';
                $msg['btn']       = '';
                $result['msg']    = json_encode($msg, JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result, 'json');
            }
            $repairModel = new RepairModel();
            $result      = $repairModel->scanQRCode_jianxiu();
            if ($result['status'] !== 1) {
                $this->ajaxReturn($result, 'json');
            } else {
                $repid = $result['repid'];
            }
        }
        if ($repid) {
            $RepairModel = new \Admin\Model\RepairModel();
            //维修信息
            $repArr            = $RepairModel->getRepairBasic($repid);
            $repArr['pic_url'] = json_decode($repArr['pic_url']);
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($repArr['assid']);


            $asArr = $this->add_ols($asArr);
            //故障类型 故障问题数据
            $repairType = $RepairModel->get_all_repair_type();
            $errorType  = [];
            foreach ($repairType as $r_k => &$r_v) {
                $errorType[$r_k]['id']       = $r_v['id'];
                $errorType[$r_k]['badge']    = 0;//新增徽章显示
                $errorType[$r_k]['text']     = $r_v['title'];
                $errorType[$r_k]['children'] = [];
                foreach ($r_v['parent'] as $kk => &$vv) {
                    $vv['value']                              = $r_v['id'] . '-' . $vv['id'];
                    $errorType[$r_k]['children'][$kk]['text'] = $vv['title'];
                    $errorType[$r_k]['children'][$kk]['id']   = $r_v['id'] . '-' . $vv['id'];
                }

            }
            //验证是否签到
            //$sign_result = $RepairModel->check_sign_in($repid);
            //预计到场时间上限
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $uptime      = $baseSetting['repair']['repair_uptime']['value'];
            $doOfferMenu = get_menu($this->MODULE, $this->Controller, 'doOffer');
            //第三方统一报价 1可操作报价 0不可操作
            $IS_OPEN_OFFER_DO_OFFER = C('DO_STATUS');
            $show_parts             = 'block';
            $not_scene              = 'none';
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
            $company         = $AssetsInfoModel->getSuppliers('repair');
            foreach ($company as $k => $v) {
                $company[$k]['status']        = 1;
                $company[$k]['offer_company'] = htmlspecialchars_decode($v['offer_company']);
            }
            //查询是否有暂存数据
            if ($repArr['status'] == C('REPAIR_RECEIPT') && $repArr['overhaul_time']) {
                //已接单未检修，且暂存过检修信息
                $pros      = $RepairModel->get_repair_fault($repid);
                $files     = $RepairModel->get_repair_file($repid);
                $activeIds = [];
                $protext   = "";
                foreach ($pros as $pro => $prv) {
                    $activeIds[] = $prv['fault_type_id'] . '-' . $prv['fault_problem_id'];
                    foreach ($repairType as $r_k => $r_v) {
                        if ($prv['fault_type_id'] == $r_v['id']) {
                            foreach ($r_v['parent'] as $p_k => $p_v) {
                                if ($p_v['id'] == $prv['fault_problem_id']) {
                                    $protext = $p_v['title'];
                                }
                            }
                        }
                    }

                }
                foreach ($files as $k => $v) {
                    $files[$k]['add_date'] = date('Y-m-d', strtotime($v['add_time']));
                }
                $result['temporary']['activeIds'] = $activeIds;
                $result['temporary']['protext']   = $protext;
                $result['temporary']['files']     = $files;
                if ($repArr['repair_type'] == C('REPAIR_TYPE_IS_STUDY')) {
                    //自修
                    $parts                        = $RepairModel->get_repair_parts($repid);
                    $result['temporary']['parts'] = $parts;
                }
                if ($repArr['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')) {
                    //第三方维修
                    $coms = $RepairModel->get_repair_companys($repid);
                    foreach ($coms as $k => $v) {
                        $coms[$k]['offer_company'] = htmlspecialchars_decode($v['offer_company']);
                        if ($v['last_decisioin'] == 1) {
                            $this->assign('decision_reasion', $v['decision_reasion']);
                        }
                    }
                    $result['temporary']['company'] = $coms;
                    $this->assign('show_company', 'block');
                    $show_parts = 'none';
                }

            }
            $is_display = 1;
            if ($repArr['expect_time']) {
                $repArr['expect_time'] = date('Y/m/d', strtotime($repArr['expect_time']));
            }
            if ($repArr['status'] != '2') {
                $is_display = 0;
            } else {
                $is_display = 1;
            }
            if (is_null($repArr['repair_type'])) {
                $repArr['repair_type'] = 0;
            }
            if ($repArr['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')) {
                //第三方维修
                $coms = $RepairModel->get_repair_companys($repid);
            }
            $result['isOpenOffer_formOffer'] = $IS_OPEN_OFFER_DO_OFFER;
            $baseSetting                     = [];
            $wx_sign_in                      = 0;
            include APP_PATH . "Common/cache/basesetting.cache.php";
            if ($baseSetting['repair']['open_sweepCode_overhaul']['value']['open'] == C('OPEN_STATUS')) {
                if ($repArr['sign_in_time']) {
                    $wx_sign_in = 0;
                } else {
                    $wx_sign_in = 1;
                }
            } else {
                $wx_sign_in = 0;
            }
            $jssdk                   = new WxAccessTokenModel();
            $signPackage             = $jssdk->GetSignPackage();
            $this->signPackage       = $signPackage;
            $result['is_display']    = $is_display;
            $result['wx_sign_in']    = $wx_sign_in;
            $result['activeIds']     = $activeIds;
            $result['partsAll']      = $partsAll;
            $result['status']        = 1;
            $result['asArr']         = $asArr;
            $result['repairType']    = $repairType;
            $result['errorType']     = $errorType;
            $result['repArr']        = $repArr;
            $result['company']       = $company;
            $result['response']      = session('username');
            $result['response_date'] = getHandleMinute(time());
            $this->ajaxReturn($result, 'json');
        } else {
            $result['status'] = 302;
            $msg['tips']      = '参数错误';
            $msg['url']       = '';
            $msg['btn']       = '';
            $result['msg']    = json_encode($msg, JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result, 'json');
        }
    }

    //维修进程列表
    public function progress()
    {
        $RepairModel = new RepairModel();
        $result      = $RepairModel->progress();
        $this->ajaxReturn($result, 'json');
    }

    //
    public function showProgress()
    {
        $repairStatus = [
            '1' => '待结单',
            '2' => '带检修',
            '3' => '待验收',
            '4' => '待出库',
            '5' => '待审批',
            '6' => '继续维修',
            '7' => '已结束',
//                '8' => '待转单',
        ];
        $this->assign('repairStatus', $repairStatus);
        $this->assign('progressUrl', get_url());
        $this->display();
    }

    //设备修复验收
    public function examine()
    {
        $RepairModel = new RepairModel();
        $result      = $RepairModel->examine();
        $this->ajaxReturn($result, 'json');
    }

    //验收设备
    public function checkRepair()
    {
        if (IS_POST) {
            $RepairModel = new \Admin\Model\RepairModel();
            $result      = $RepairModel->checkRepair();
            $this->ajaxReturn($result, 'json');
        } else {
            $repid = I('get.repid');
            if (!$repid) {
                $result['status'] = 1;
                $result['msg']    = '参数非法，请稍联系管理员！';
                $this->ajaxReturn($result, 'json');
            }
            $RepairModel = new \Admin\Model\RepairModel();
            //维修信息
            $repArr            = $RepairModel->getRepairBasic($repid);
            $repArr['pic_url'] = json_decode($repArr['pic_url']);
            if ($repArr['status'] == C('REPAIR_ALREADY_ACCEPTED')) {
                //已验收，跳转到详情页
                $result['status']      = 302;
                $result['redirectUrl'] = C('VUE_NAME') . '/Repair/showRepairDetails?repid=' . $repid;
                $this->ajaxReturn($result, 'json');
            }
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
            //获取故障问题
            $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
            //是否由系统生成字段
            $issystem               = $RepairModel->DB_get_one('base_setting', 'value',
                ['set_item' => 'repair_system']);
            $issystem               = json_decode($issystem['value'], true);
            $result['repair_check'] = $issystem['repair_check'];
            $result['repArr']       = array_merge($asArr, $repArr);
            $result['status']       = 1;
            $this->ajaxReturn($result, 'json');

        }
    }

    //维修处理列表
    public function getRepairLists()
    {
        $repairModel = new RepairModel();
        $result      = $repairModel->get_repair_lists();
        $this->ajaxReturn($result, 'json');
    }

    //维修处理详细页
    public function startRepair()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'upload':
                    //文件上传
                    $RepairModel        = new RepairModel();
                    $result             = $RepairModel->uploadfile();
                    $result['adduser']  = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'del_file':
                    $RepairModel = new RepairModel();
                    $result      = $RepairModel->del_file();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $RepairModel = new \Admin\Model\RepairModel();
                    $result      = $RepairModel->doStartRepair();
                    $this->ajaxReturn($result, 'json');
            }
        } else {
            $repid = I('get.repid');
            if ($repid) {
                $RepairModel = new \Admin\Model\RepairModel();
                //维修信息
                $repArr            = $RepairModel->getRepairBasic($repid);
                $repArr['pic_url'] = json_decode($repArr['pic_url']);
                if ($repArr['status'] >= C('REPAIR_MAINTENANCE_COMPLETION')) {
                    //已维修完成
                    $result['status']      = 302;
                    $result['redirectUrl'] = C('VUE_NAME') . '/Repair/showRepairDetails?repid=' . $repid;
                    $this->ajaxReturn($result, 'json');

                }
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
                //获取故障问题
                $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
                //第三方厂家信息
                $companyData = $RepairModel->getAllCompanysBasic($repid);
                $company     = $companyData['data'];
                $companyLast = $companyData['lastData'];
                //配件/服务
                $parts = $RepairModel->getAllPartsBasic($repid);
                //审核历史
                $all_approves = $app_users = $userPic = [];
                $approves     = $RepairModel->getApproveBasic($repid);
                if ($repArr['all_approver']) {
                    //查询超级管理员
                    $is_super     = $RepairModel->DB_get_one('user', 'username,pic', ['is_super' => 1]);
                    $all_approver = str_replace(",/" . $is_super['username'] . "/", '', $repArr['all_approver']);
                    $all_approver = str_replace("/", '', $all_approver);
                    $all_approver = explode(',', $all_approver);
                    foreach ($all_approver as $k => $v) {
                        $upic                      = $RepairModel->DB_get_one('user', 'username,pic',
                            ['username' => $v]);
                        $app_users[$k]['username'] = $upic['username'];
                        $app_users[$k]['pic']      = $upic['pic'];
                    }
                    $userPic[$is_super['username']] = $is_super['pic'];
                    foreach ($app_users as $k => $v) {
                        $all_approves[$k]['is_adopt']     = 0;
                        $all_approves[$k]['approver']     = $v['username'];
                        $all_approves[$k]['user_pic']     = $v['pic'];
                        $all_approves[$k]['approve_time'] = '';
                        $all_approves[$k]['remark']       = '';
                        //用户头像
                        $userPic[$v['username']] = $v['pic'];
                    }
                }
                foreach ($approves as $key => &$value) {
                    $all_approves[$key]['is_adopt']     = (int)$value['is_adopt'];
                    $all_approves[$key]['approver']     = $value['approver'];
                    $all_approves[$key]['approve_time'] = date('Y-m-d H:i', strtotime($value['approve_time']));
                    $all_approves[$key]['remark']       = $value['remark'] ? $value['remark'] : '无备注';
                    $all_approves[$key]['user_pic']     = $userPic[$value['approver']];
                }
                //维修跟进信息
                $follow = $RepairModel->getRepirFollowBasic($repid);
                //是否是否有权限查看上传文件
                $upload = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
                if ($upload) {
                    //获取上传的文件
                    $files    = $RepairModel->getRepairFileBasicAndDel($repid);
                    $newFiles = [];
                    $imgType  = ['JPG', 'GIF', 'PNG', 'JPEG', 'jpg', 'gif', 'png', 'jpeg'];
                    foreach ($files as &$one) {
                        //过滤非图片的文件
                        if (in_array($one['file_type'], $imgType)) {
                            $newFiles[] = $one;
                        }
                    }
                    $result['files'] = $newFiles;
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
                $user      = $UserModel->getUsers('accept', $asArr['departid']);
                //下一条跟进时间
                $maxtime = $RepairModel->DB_get_one('repair_follow', 'MAX(followdate) AS followdate',
                    ['repid' => $repid]);
                //是否由系统生成字段
                $issystem = $RepairModel->DB_get_one('base_setting', 'value', ['set_item' => 'repair_system']);
                $issystem = json_decode($issystem['value'], true);
                if ($repArr['status'] == C('REPAIR_MAINTENANCE') && isset($issystem['service_working'])) {
                    //todo  维修工时计算：通知时间至当前时间;
                    $repArr['working_hours'] = timediff(strtotime($repArr['response_date']), time());
                } else {
                    $repArr['working_hours'] = timediff(strtotime($repArr['response_date']), time());
                }
                if (!isset($issystem['service_working'])) {
                    $repArr['engineer_time'] = date('Y-m-d H:i', strtotime($repArr['engineer_time']));
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
                    unset($user[$key]['telephone']);
                }
                if (!$repArr['engineer_time'] || $repArr['engineer_time'] == '1970/01/01') {
                    $repArr['engineer_time'] = date('Y-m-d H:i', time());
                }
                $result['follow']          = $follow;
                $result['parts']           = $parts;
                $result['service_working'] = $repArr['working_hours'];
                $result['service_date']    = $issystem['service_date'];
                $result['user']            = $user;
                $result['partsAll']        = $partsAll;
                $result['company']         = $company;
                $result['approves']        = $all_approves;
                $result['status']          = 1;
                $result['repArr']          = array_merge($asArr, $repArr);
                $this->ajaxReturn($result, 'json');
            } else {
                $this->error('非法操作');
            }
        }
    }

    //维修审批
    public function addApprove()
    {
        $departid  = session('departid');
        $departArr = explode(',', $departid);
        if (IS_POST) {
            $RepairModel = new \Admin\Model\RepairModel();
            $result      = $RepairModel->doAddApprove();
            $this->ajaxReturn($result, 'json');
        } else {
            $repid = I('get.repid');
            if ($repid) {
                $RepairModel = new \Admin\Model\RepairModel();
                //维修信息
                $repArr            = $RepairModel->getRepairBasic($repid);
                $repArr['pic_url'] = json_decode($repArr['pic_url']);
                if (!isset($repArr['assid'])) {
                    $result['msg']    = '参数非法，请稍联系管理员！';
                    $result['status'] = -1;
                    $this->ajaxReturn($result, 'json');
                }
                $is_display       = 0;
                $current_approver = explode(',', $repArr['current_approver']);
                if ($repArr['approve_status'] != '0') {
                    $is_display = 0;
                } elseif (!in_array(session('username'), $current_approver)) {
                    $is_display = 0;
                } else {
                    $is_display = 1;
                }
                //设备信息
                $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
                if (!in_array($asArr['departid'], $departArr)) {
                    $result['status'] = -1;
                    $result['msg']    = '对不起，您没有【' . $asArr['department'] . '】科室的管理权限，请联系管理员分配！';
                    $this->ajaxReturn($result, 'json');
                    exit;
                }
                //获取故障问题
                $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
                //第三方厂家信息
                $companyData = $RepairModel->getAllCompanysBasic($repid);
                $company     = $companyData['data'];
                $companyLast = $companyData['lastData'];
                //配件/服务
                $parts = $RepairModel->getAllPartsBasic($repid);
                //审核历史
                $approves     = $RepairModel->getApproveBasic($repid);
                $all_approves = $app_users = $userPic = [];
                if ($repArr['all_approver']) {
                    //查询超级管理员
                    $is_super     = $RepairModel->DB_get_one('user', 'username,pic', ['is_super' => 1]);
                    $all_approver = str_replace(",/" . $is_super['username'] . "/", '', $repArr['all_approver']);
                    $all_approver = str_replace("/", '', $all_approver);
                    $all_approver = explode(',', $all_approver);
                    foreach ($all_approver as $k => $v) {
                        $upic                      = $RepairModel->DB_get_one('user', 'username,pic',
                            ['username' => $v]);
                        $app_users[$k]['username'] = $upic['username'];
                        $app_users[$k]['pic']      = $upic['pic'];
                    }
                    $userPic[$is_super['username']] = $is_super['pic'];
                    foreach ($app_users as $k => $v) {
                        $all_approves[$k]['is_adopt']     = 0;
                        $all_approves[$k]['approver']     = $v['username'];
                        $all_approves[$k]['user_pic']     = $v['pic'];
                        $all_approves[$k]['approve_time'] = '';
                        $all_approves[$k]['remark']       = '';
                        //用户头像
                        $userPic[$v['username']] = $v['pic'];
                    }
                }
                foreach ($approves as $key => &$value) {
                    $all_approves[$key]['is_adopt']     = (int)$value['is_adopt'];
                    $all_approves[$key]['approver']     = $value['approver'];
                    $all_approves[$key]['approve_time'] = $value['approve_time'];
                    $all_approves[$key]['remark']       = $value['remark'] ? $value['remark'] : '无备注';
                    $all_approves[$key]['user_pic']     = $userPic[$value['approver']];
                }
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
                        $current_approver     = explode(',', $repArr['current_approver']);
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
                $result['parts']      = $parts;
                $result['follow']     = $follow;
                $result['is_display'] = $is_display;
                foreach ($approves as $key => $value) {
                    if ($value['is_adopt'] == 1) {
                        $approves[$key]['opinion'] = '<span style="color:green">通过</span>';
                    } else {
                        $approves[$key]['opinion'] = '<span style="color:red">不通过</span>';
                    }
                }
                $result['company']  = $company;
                $result['approves']    = $all_approves;
                $result['approveUser'] = session('username');
                $result['approveDate'] = getHandleMinute(time());
                $result['status']      = 1;
                $result['repArr']      = array_merge($asArr, $repArr);
                $this->ajaxReturn($result, 'json');
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
        $RepairModel           = new RepairModel();
        $where['approve_type'] = ['EQ', $type];
        $where['hospital_id']  = ['EQ', $hospital_id];
        $where['status']       = ['EQ', C('OPEN_STATUS')];
        $res                   = $RepairModel->DB_get_one('approve_type', '*', $where);
        return $res;
    }
}
