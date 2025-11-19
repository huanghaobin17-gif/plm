<?php

namespace Mobile\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\CommonModel;
use Admin\Model\UserModel;
use Common\Weixin\Weixin;
use Mobile\Controller\Login\IndexController;
use Mobile\Model\AssetsBorrowModel;
use Mobile\Model\AssetsInfoModel;
use Mobile\Model\AssetsTransferModel;
use Mobile\Model\WxAccessTokenModel;

class BorrowController extends IndexController
{
    protected $fail_url = 'Notin/fail.html';//失败跳转地址
    protected $succ_url = 'Notin/suc.html';//成功跳转地址
    protected $index_url = 'Index/testindex.html';//首页地址
    protected $borrow_list_url = 'Borrow/borrowAssetsList.html';//转科申请地址

    //借调申请设备列表
    public function borrowAssetsList()
    {
        if (IS_POST) {
            //获取借调申请列表数据
            $AssetsBorrowModel = new AssetsBorrowModel();
            $result = $AssetsBorrowModel->get_borrow_assets_list();
            $this->ajaxReturn($result, 'json');
        } else {
            $asModel = new AssetsInfoModel();
            //查询分类信息数据
            $cates = $asModel->get_all_category('borrow');
            $cates = getTree('parentid', 'catid', $cates, 0);
            //查询科室信息
            $where['is_delete'] = 0;
            $where['hospital_id'] = session('current_hospitalid');
            if (session('isSuper') != C('YES_STATUS')) {
                $where['departid'] = array('NOT IN', session('job_departid'));
            }
            $departs = $asModel->DB_get_all('department', 'departid,department,parentid,assetssum', $where);
            $where['status'] = C('ASSETS_STATUS_USE');//只能借调在用的设备
            $where['is_subsidiary'] = C('NO_STATUS');//只借调主设备
            $where['quality_in_plan'] = C('NO_STATUS');//排除质控中
            $where['patrol_in_plan'] = C('NO_STATUS');//排除巡查中
            $notid = [];
            $notwhere['status'] = array('not in',[3,4]);
            $notarr = $asModel->DB_get_all('assets_borrow','assid',$notwhere);
            foreach ($notarr as $k=>$v){
               $notid[] = $v['assid'];
             }
            if($notid){
               $where['assid'] = array('not in',$notid);
            }
            $as_data = $asModel->DB_get_all('assets_info','count(assid) as num,departid',$where,'departid');
            $assetssum = [];
            foreach ($as_data as $key => $value) {
               $assetssum[$value['departid']] = $value['num'];
             }
            foreach ($departs as $key => $value) {
               $departs[$key]['assetssum'] = $assetssum[$value['departid']]?$assetssum[$value['departid']]:0;
            }
            $departs = getTree('parentid', 'departid', $departs, 0);
            array_multisort(array_column($cates,'assetssum'),SORT_DESC,$cates);
            $this->assign('cates', $cates);
            $this->assign('departs', $departs);
            $this->assign('borrowAssetsListUrl', get_url());
            $jssdk = new WxAccessTokenModel();
            $signPackage = $jssdk->GetSignPackage();
            $this->signPackage = $signPackage;
            $this->display();
        }
    }

    //申请借调
    public function applyBorrow()
    {
        $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel();
        if (IS_POST) {
            $type = I('post.type');
            switch ($type){
                //结束进程
                case 'end':
                    $borid = I('POST.borid');
                    $result = $AssetsBorrowModel->endBorrow($borid);
                    $this->ajaxReturn($result, 'json');
                    break;
                //申请重审
                case 'edit':
                    $borid = I('POST.borid');
                    $result = $AssetsBorrowModel->editBorrow($borid);
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $result = $AssetsBorrowModel->applyBorrow();
                    $this->ajaxReturn($result, 'json');
            }
        } else {
            $assid = I('GET.assid');
            $assnum = I('get.assnum');
            if ($assnum) {
                //微信扫码进来
                $assid = $this->scanQRcode_borrow();
            }
            $this->assign('is_display', 1);
            if (I('GET.borid')) {
                $assets_borrow_data = $AssetsBorrowModel->DB_get_one('assets_borrow', 'assid,retrial_status,apply_departid,borrow_num,borrow_reason,estimate_back,borid', array('borid' => I('GET.borid')));
                $assid = $assets_borrow_data['assid'];
                $assets_borrow_data['estimate_back'] = date('Y-m-d H:i', $assets_borrow_data['estimate_back']);
                $assets_borrow_data['type'] = 'edit';
                $this->assign('assets_borrow_data', $assets_borrow_data);
                $approve = $AssetsBorrowModel->getBorrowApprovBasic(I('GET.borid'));
                $this->assign('approve', $approve);
                if($assets_borrow_data['retrial_status'] == 3){
                    $this->assign('is_display', 0);
                }
            }
            if (!$assid) {
                $this->error('非法操作');
            }
            $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel();
            $asModel = new \Admin\Model\AssetsInfoModel();
            $assets = $asModel->getAssetsInfo($assid);
            if ($assets_borrow_data) {
                $flowNumber = $assets_borrow_data['borrow_num'];
            } else {
                $flowNumber = $AssetsBorrowModel->getFlowNumber($assid);
            }
            //判断有无查看原值的权限
            $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
            if (!$showPrice) {
                $assets['buy_price'] = '***';
            }
            $subsidiary = $AssetsBorrowModel->getAssetsSubsidiary($assid);
            $baseSetting = $applyDepartname = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            if (session('isSuper') == C('YES_STATUS')) {
                $CheckLoginController = new CheckLoginController();
                $departname = $CheckLoginController->getDepartname();
                foreach ($departname as $key => $value) {
                    if ($assets['departid'] == $value['departid']) {
                        unset($departname[$key]);
                    }
                }
                sort($departname);
                $this->assign('departname', $departname);
            }
            $this->assign('assets', $assets);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('flowNumber', $flowNumber);
            $this->assign('borrow_in_time', getHandleMinute(time()));
            $this->assign('apply_user', session('username'));
            $this->assign('apply_borrow_back_start_time', $baseSetting['assets']['apply_borrow_back_time']['value'][0]);
            $this->assign('apply_borrow_back_end_time', $baseSetting['assets']['apply_borrow_back_time']['value'][1]);
            $this->assign('applyBorrowUrl', get_url());
            $this->display();
        }
    }

    /*
     * 检查是否可以扫码借调设备
     */
    public function scanQRcode_borrow()
    {
        $transfer = new AssetsTransferModel();
        $departid = session('departid');
        $assnum = I('get.assnum');
        //微信扫码转科进入，查询
        $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status,quality_in_plan,patrol_in_plan,is_subsidiary', array('assnum' => $assnum,'hospital_id' => session('current_hospitalid'),'is_delete'=>'0'));
        if (!$exists) {
            $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status,quality_in_plan,patrol_in_plan,is_subsidiary', array('assorignum' => $assnum, 'hospital_id' => session('current_hospitalid'),'is_delete'=>'0'));
        }
        if (!$exists) {
            $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status,quality_in_plan,patrol_in_plan,is_subsidiary', array('assorignum_spare' => $assnum,'is_delete'=>'0', 'hospital_id' => session('current_hospitalid')));
        }
        if (!$exists) {
            $this->assign('tips', '查找不到编码为 ' . $assnum . ' 的设备信息');
            $this->assign('btn', '返回列表页');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->borrow_list_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        if ($exists['departid'] == session('job_departid')) {
            $this->assign('tips', '不能借调自己工作所在科室设备');
            $this->assign('btn', '返回列表页');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->borrow_list_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        if ($exists['is_subsidiary'] == 1) {
            $this->assign('tips', '不能借调附属设备');
            $this->assign('btn', '返回列表页');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->borrow_list_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        if ($exists['quality_in_plan'] == 1) {
            $this->assign('tips', '该设备正在进行质控计划');
            $this->assign('btn', '返回列表页');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->borrow_list_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        if ($exists['patrol_in_plan'] == 1) {
            $this->assign('tips', '该设备正在进行巡查计划');
            $this->assign('btn', '返回列表页');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->borrow_list_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        if ($exists['status'] != 0) {
            $this->assign('tips', '该设备暂不允许借调');
            $this->assign('btn', '返回列表页');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->borrow_list_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        return $exists['assid'];
    }

    //借入验收操作
    public function borrowInCheck()
    {
        if (IS_POST) {
            $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel();
            $result = $AssetsBorrowModel->borrowInCheck();
            $this->ajaxReturn($result, 'json');
        } else {
            $borid = I('GET.borid');
            if ($borid) {
                $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel;
                $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
                if (!$borrow['assid']) {
                $this->assign('tips', '查找不到该设备借入信息');
                $this->assign('btn', '返回首页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                $this->display('Pub/Notin/fail');
                exit;
                }
                if ($borrow['status'] != "1") {
                    $this->assign('is_display', 0);
                } else {
                    $this->assign('is_display', 1);
                }
                $asModel = new \Admin\Model\AssetsInfoModel();
                $assets = $asModel->getAssetsInfo($borrow['assid']);
                //判断有无查看原值的权限
                $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
                if (!$showPrice) {
                    $assets['buy_price'] = '***';
                }
                $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
                $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
                $borrow['lendDepartment'] = $assets['department'];
                $borrow['assets'] = $assets['assets'];
                if ($approve) {
                    foreach ($approve as &$approveV) {
                        $borrow['approve'][$approveV['level']]['approver'] = $approveV['approver'];
                        $borrow['approve'][$approveV['level']]['approve_time'] = $approveV['approve_time'];
                        $borrow['approve'][$approveV['level']]['approve_status'] = $approveV['approve_status'];
                    }
                }
                $this->assign('subsidiary', $subsidiary);
                $this->assign('assets', $assets);
                $this->assign('borrow', $borrow);
                $this->assign('approve', $approve);
                $this->assign('borrowInCheckUrl', get_url());
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    //归还验收操作
    public function giveBackCheck()
    {
        if (IS_POST) {
            $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel();
            $result = $AssetsBorrowModel->giveBackCheck();
            $this->ajaxReturn($result, 'json');
        } else {
            $borid = I('GET.borid');
            if ($borid) {
                $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel;
                $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
                if (!$borrow['assid']) {
                $this->assign('tips', '查找不到该设备借入信息');
                $this->assign('btn', '返回首页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                $this->display('Pub/Notin/fail');
                exit;
                }

                if ($borrow['status'] != "2") {
                    $this->assign('is_display', 0);
                } else {
                    $this->assign('is_display', 1);
                }
                $asModel = new \Admin\Model\AssetsInfoModel();
                $assets = $asModel->getAssetsInfo($borrow['assid']);
                //限制非该科室权限用户进入
                $departid = explode(',',session('departid'));
                if (!in_array($assets['departid'], $departid)) {
                    $this->assign('tips', '该用户没有科室权限');
                    $this->assign('btn', '返回首页');
                    $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                    $this->display('Pub/Notin/fail');
                    exit;
                }
                //判断有无查看原值的权限
                $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
                if (!$showPrice) {
                    $assets['buy_price'] = '***';
                }
                $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
                $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
                $borrow['lendDepartment'] = $assets['department'];
                $borrow['assets'] = $assets['assets'];
                if ($approve) {
                    foreach ($approve as &$approveV) {
                        $borrow['approve'][$approveV['level']]['approver'] = $approveV['approver'];
                        $borrow['approve'][$approveV['level']]['approve_time'] = $approveV['approve_time'];
                        $borrow['approve'][$approveV['level']]['approve_status'] = $approveV['approve_status'];
                    }
                }
                $this->assign('subsidiary', $subsidiary);
                $this->assign('assets', $assets);
                $this->assign('borrow', $borrow);
                $this->assign('approve', $approve);
                $baseSetting = [];
                include APP_PATH . "Common/cache/basesetting.cache.php";
                $this->assign('apply_borrow_back_start_time', $baseSetting['assets']['apply_borrow_back_time']['value'][0]);
                $this->assign('apply_borrow_back_end_time', $baseSetting['assets']['apply_borrow_back_time']['value'][1]);
                $this->assign('giveBackCheckUrl', get_url());
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    //借调审批列表ps.该列表受用户的科室限定
    public function approveBorrowList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取借调审批列表数据
                    $AssetsBorrowModel = new AssetsBorrowModel;
                    $result = $AssetsBorrowModel->approveBorrowList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                //借调详情页面
                case 'showApproveDetails':
                    $this->showApproveDetails();
                    break;
                default:
                    //借调审批列表页面
                    $this->showApproveBorrowList();
                    break;
            }
        }
    }

    //借调审批列表页面
    private function showApproveBorrowList()
    {
        $departid = explode(',', session('departid'));
        $department = $this->getDepartname($departid);
        $this->assign('approveBorrowListUrl', get_url());
        $this->assign('department', $department);
        $this->display();
    }

    //借调审批详情页
    private function showApproveDetails()
    {
        $assid = I('GET.assid');
        $borid = I('GET.borid');
        if ($assid && $borid) {
            $AssetsBorrowModel = new AssetsBorrowModel;
            $assets = $AssetsBorrowModel->getAssetsBasic($assid);
            $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
            $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
            $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
            $this->assign('assets', $assets);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('borrow', $borrow);
            $this->assign('approve', $approve);
            $this->display('showApproveDetails');
        } else {
            $this->error('非法操作');
        }
    }

    //借调审批 借出科室审批
    public function departApproveBorrow()
    {
        $this->approveBorrow();
    }


    //借调审批  设备科审批
    public function assetsApproveBorrow()
    {
        $this->approveBorrow();
    }

    //借调审批
    public function approveBorrow()
    {
        if (IS_POST) {
            $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel();
            $result = $AssetsBorrowModel->approveBorrow();
            $this->ajaxReturn($result, 'json');
        } else {
            $borid = I('GET.borid');
            if ($borid) {
                $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel;
                $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
                $asModel = new \Admin\Model\AssetsInfoModel();
                $assets = $asModel->getAssetsInfo($borrow['assid']);
                //判断有无查看原值的权限
                $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
                if (!$showPrice) {
                    $assets['buy_price'] = '***';
                }
                $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
                $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
                $borrow['lendDepartment'] = $assets['department'];
                $borrow['assets'] = $assets['assets'];
                if ($approve) {
                    foreach ($approve as &$approveV) {
                        $borrow['approve'][$approveV['level']]['approver'] = $approveV['approver'];
                        $borrow['approve'][$approveV['level']]['approve_time'] = $approveV['approve_time'];
                        $borrow['approve'][$approveV['level']]['approve_status'] = $approveV['approve_status'];
                    }
                }
                //查询设备科管理
                $assetsApproveBorrowMenu = get_menu('Assets', 'Borrow', 'assetsApproveBorrow');
                if (!$borrow['assid']) {
                $this->assign('tips', '查找不到该设备借入信息');
                $this->assign('btn', '返回首页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                $this->display('Pub/Notin/fail');
                exit;
                }
                if ($borrow['status'] != "0") {
                    $this->assign('is_display', 0);
                } else if ($approve[0]['approve_status'] == 1 && !$assetsApproveBorrowMenu) {
                    $this->assign('is_display', 0);
                } else {
                    $this->assign('is_display', 1);
                }
                $this->assign('approve_time', getHandleMinute(time()));
                $this->assign('approver', session('username'));
                $this->assign('assets', $assets);
                $this->assign('subsidiary', $subsidiary);
                $this->assign('borrow', $borrow);
                $this->assign('approve', $approve);
                $this->assign('approveBorrowUrl', get_url());
                $this->display('approveBorrow');
            } else {
                $this->error('非法操作');
            }
        }
    }

    //借入验收列表
    public function borrowInCheckList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取借入验收列表数据
                    $AssetsBorrowModel = new AssetsBorrowModel;
                    $result = $AssetsBorrowModel->borrowInCheckList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                default:
                    //借入验收列表页面
                    $this->showBorrowInCheckList();
                    break;
            }
        }
    }

    //借入验收列表页面
    private function showBorrowInCheckList()
    {
        $this->assign('borrowInCheckListUrl', get_url());
        $this->display();
    }


    //归还验收列表
    public function giveBackCheckList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'getReminderList':
                    //获取逾期催还列表
                    $AssetsBorrowModel = new AssetsBorrowModel;
                    $result = $AssetsBorrowModel->getReminderList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'sendOutReminder':
                    $this->sendOutReminder();
                    break;
                case 'Reminder_acceptance':
                    $this->Reminder_acceptance();
                    break;
                default:
                    //获取归还验收列表数据
                    $AssetsBorrowModel = new AssetsBorrowModel;
                    $result = $AssetsBorrowModel->giveBackCheckList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                //借调逾期
                case 'showReminderList':
                    $this->showReminderList();
                    break;
                //借调逾期详细内容
                case 'showReminder':
                    $this->showReminder();
                    break;
                default:
                    //归还验收列表页面
                    $this->showGiveBackCheckList();
                    break;
            }
        }
    }

    //提醒用户准备验收
    public function Reminder_acceptance()
    {
        $borid = I('POST.borid');
        $reminder_time = I('POST.reminder_time');
        if ($borid && $reminder_time) {
            $AssetsBorrowModel = new AssetsBorrowModel();
            $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
            $asModel = new \Admin\Model\AssetsInfoModel();
            $assets = $asModel->getAssetsInfo($borrow['assid']);
            $moduleName = 'Assets';
            $controllerName = 'Borrow';
            $actionName = 'giveBackCheckList';
            $redecturl = C('HTTP_HOST') .C('MOBILE_NAME'). '/Borrow/giveBackCheck.html?borid=' . $borid;

            /** @var UserModel[] $users */
            $users = $AssetsBorrowModel->getToUser(session('userid'), $assets['departid'], $moduleName, $controllerName, $actionName);
            $openIds = array_column($users, 'openid');
            $openIds = array_filter($openIds);
            $openIds = array_unique($openIds);

            $messageData = [
                'thing1'            => $borrow['department'],// 需求科室
                'thing2'            => $assets['assets'],// 需求设备
                'time5'             => $borrow['estimate_back'],// 需求结束时间
                'character_string9' => $borrow['borrow_num'],// 借调流水号
                'const10'           => '已归还，待验收',// 借调状态
            ];

            foreach ($openIds as $openId) {
                Weixin::instance()->sendMessage($openId, '设备借调处理通知', $messageData, $redecturl);
            }
        } else {
            $result["code"] = 100;
            $result["msg"] = '找不到这条记录';
            $this->ajaxReturn($result, 'json');
            return;
        }
        $result["code"] = 200;
        $result["msg"] = '发送成功，请耐心等待';
        $this->ajaxReturn($result, 'json');
        return;
    }

    //逾期单的详细内容
    public function showReminder()
    {
        $borid = I('GET.borid');
        if ($borid) {
            $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel;
            $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
            $asModel = new \Admin\Model\AssetsInfoModel();
            $assets = $asModel->getAssetsInfo($borrow['assid']);
            //判断有无查看原值的权限
            $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
            if (!$showPrice) {
                $assets['buy_price'] = '***';
            }
            $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
            $borrow['lendDepartment'] = $assets['department'];
            $borrow['assets'] = $assets['assets'];
            if ($approve) {
                foreach ($approve as &$approveV) {
                    $borrow['approve'][$approveV['level']]['approver'] = $approveV['approver'];
                    $borrow['approve'][$approveV['level']]['approve_time'] = $approveV['approve_time'];
                    $borrow['approve'][$approveV['level']]['approve_status'] = $approveV['approve_status'];
                }
            }
            $this->assign('assets', $assets);
            $this->assign('borrow', $borrow);
            $this->assign('approve', $approve);
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('ReminderCheckUrl', get_url());
            $this->display('showReminder');
        } else {
            $this->error('非法操作');
        }
    }

    //发送超时提醒
    private function sendOutReminder()
    {
        $borid = I('POST.borid');
        if ($borid) {
            $AssetsBorrowModel = new AssetsBorrowModel();
            $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
            $asModel = new \Admin\Model\AssetsInfoModel();
            $assets = $asModel->getAssetsInfo($borrow['assid']);
            $redecturl = C('HTTP_HOST') . C('MOBILE_NAME').'/Borrow/giveBackCheckList.html?action=showReminder&borid=' . $borid;
            $user = $AssetsBorrowModel->DB_get_one('user', 'openid', array('userid' => $borrow['apply_userid']));

            if ($user['openid']) {
                Weixin::instance()->sendMessage($user['openid'], '设备借调处理通知', [
                    'thing1'            => $assets['department'],// 需求科室
                    'thing2'            => $assets['assets'],// 需求设备
                    'time5'             => $borrow['estimate_back'],// 需求结束时间
                    'character_string9' => $borrow['borrow_num'],// 借调流水号
                    'const10'           => '已逾期，请归还',// 借调状态
                ], $redecturl);
            }

        } else {
            $result["code"] = 100;
            $result["msg"] = '找不到这条记录';
            $this->ajaxReturn($result, 'json');
            return;
        }
        $result["code"] = 200;
        $result["msg"] = '发送成功，请耐心等待';
        $this->ajaxReturn($result, 'json');
        return;
    }

    //显示逾期催还列表
    private function showReminderList()
    {
        $this->assign('borid', I('get.borid'));
        $this->assign('reminderListUrl', get_url());
        $this->display('reminderList');
    }

    private function showGiveBackCheckList()
    {
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $this->assign('giveBackCheckListUrl', get_url());
        $this->assign('apply_borrow_back_start_time', $baseSetting['assets']['apply_borrow_back_time']['value'][0]);
        $this->assign('apply_borrow_back_end_time', $baseSetting['assets']['apply_borrow_back_time']['value'][1]);
        $this->display();
    }

}
