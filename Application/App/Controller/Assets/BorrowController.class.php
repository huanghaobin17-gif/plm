<?php

namespace App\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\UserModel;
use App\Controller\Login\IndexController;
use App\Model\AssetsBorrowModel;
use App\Model\AssetsInfoModel;
use App\Model\AssetsTransferModel;
use App\Model\WxAccessTokenModel;
use Admin\Model\OfflineSuppliersModel;
use Common\Weixin\Weixin;

class BorrowController extends IndexController
{
    protected $fail_url = 'Notin/fail.html';//失败跳转地址
    protected $succ_url = 'Notin/suc.html';//成功跳转地址
    protected $index_url = 'Index/testindex.html';//首页地址
    protected $borrow_list_url = 'Borrow/borrowAssetsList.html';//转科申请地址

    //借调申请设备列表
    public function borrowAssetsList()
    {
        $AssetsBorrowModel = new AssetsBorrowModel();
        $result = $AssetsBorrowModel->get_borrow_assets_list();
        $this->ajaxReturn($result, 'json');
    }

    //申请借调
    public function applyBorrow()
    {
        $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel();
        if (IS_POST) {
            $type = I('post.type');
            switch ($type) {
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
            $AssetsBorrowModel = new AssetsBorrowModel();
            $assid = I('GET.assid');
            $assnum = I('GET.assnum');
            if ($assnum) {
                //微信扫码进来
                $assid = $this->scanQRcode_borrow();
            }
            $show_form = 1;
            if (I('GET.borid')) {
                $assets_borrow_data = $AssetsBorrowModel->DB_get_one('assets_borrow', 'assid,retrial_status,apply_departid,borrow_num,borrow_reason,estimate_back,borid', array('borid' => I('GET.borid')));
                $assid = $assets_borrow_data['assid'];
                $assets_borrow_data['estimate_back'] = date('Y-m-d H:i', $assets_borrow_data['estimate_back']);
                $assets_borrow_data['type'] = 'edit';
                $result['data'] = $assets_borrow_data;
                $approve = $AssetsBorrowModel->getBorrowApprovBasic(I('GET.borid'));
                foreach ($approve as $k => $v) {
                    if ($v['is_adopt'] == 1) {
                        $examine[$k]['opinion'] = '<span style="color:green">通过</span>';
                    } else {
                        $examine[$k]['opinion'] = '<span style="color:red">不通过</span>';
                    }
                }
                $result['approve'] = $approve;
                if ($assets_borrow_data['retrial_status'] == 3) {
                    $show_form = 0;
                }
                $result['data'] = $assets_borrow_data;
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
                    if ($assets_borrow_data['apply_departid'] == $value['departid']) {
                        $result['depart'] = $value['department'];
                    }
                }
                sort($departname);
                $result['departmentColumns'] = array_column($departname, 'department');
                $result['departname'] = $departname;
            }
            $assets = $this->add_ols($assets);
            $result['show_form'] = $show_form;
            $result['username'] = session('username');
            $result['borrow_in_time'] = getHandleMinute(time());
            $result['subsidiary'] = $subsidiary;
            $result['flowNumber'] = $flowNumber;
            $result['apply_borrow_back_start_time'] = $baseSetting['assets']['apply_borrow_back_time']['value'][0];
            $result['apply_borrow_back_end_time'] = $baseSetting['assets']['apply_borrow_back_time']['value'][1];
            $result['status'] = 1;
            $result['asArr'] = $assets;
            $this->ajaxReturn($result, 'json');
            //$this->display();
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
        $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status,quality_in_plan,patrol_in_plan,is_subsidiary', array('assnum' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
        if (!$exists) {
            $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status,quality_in_plan,patrol_in_plan,is_subsidiary', array('assorignum' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
        }
        if (!$exists) {
            $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status,quality_in_plan,patrol_in_plan,is_subsidiary', array('assorignum_spare' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        }
        if (!$exists) {
            $result['status'] = 302;
            $msg['tips'] = '查找不到编码为 ' . $assnum . ' 的设备信息';
            $msg['url'] = '';
            $msg['btn'] = '';
            $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
            exit;
        }
        if ($exists['departid'] == session('job_departid')) {
            $result['status'] = 302;
            $msg['tips'] = '不能借调自己工作所在科室设备';
            $msg['url'] = '';
            $msg['btn'] = '';
            $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
            exit;
        }
        if ($exists['is_subsidiary'] == 1) {
            $result['status'] = 302;
            $msg['tips'] = '不能借调附属设备';
            $msg['url'] = '';
            $msg['btn'] = '';
            $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
            exit;
        }
        if ($exists['quality_in_plan'] == 1) {
            $result['status'] = 302;
            $msg['tips'] = '该设备正在进行质控计划';
            $msg['url'] = '';
            $msg['btn'] = '';
            $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
            exit;
        }
        if ($exists['patrol_in_plan'] == 1) {
            $result['status'] = 302;
            $msg['tips'] = '该设备正在进行巡查计划';
            $msg['url'] = '';
            $msg['btn'] = '';
            $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
            exit;
        }
        if ($exists['status'] != 0) {
            $result['status'] = 302;
            $msg['tips'] = '该设备暂不允许借调';
            $msg['url'] = '';
            $msg['btn'] = '';
            $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
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
                    $result['status'] = -1;
                    $result['msg'] = '查找不到该设备借入信息';
                    return $result;
                }
                if ($borrow['status'] != "1") {
                    $is_display = false;
                } else {
                    $is_display = true;
                }
                $asModel = new \Admin\Model\AssetsInfoModel();
                $assets = $asModel->getAssetsInfo($borrow['assid']);
                //判断有无查看原值的权限
                $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
                if (!$showPrice) {
                    $assets['buy_price'] = '***';
                }
                $assets = $this->add_ols($assets);
                $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
                $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
                $borrow['lendDepartment'] = $assets['department'];
                $borrow['assets'] = $assets['assets'];
                if ($approve) {
                    foreach ($approve as &$approveV) {
                        $borrow['approve'][$approveV['level']]['approver'] = $approveV['approver'];
                        $borrow['approve'][$approveV['level']]['approve_time'] = $approveV['approve_time'];
                        $borrow['approve'][$approveV['level']]['approve_status'] = $approveV['approve_status'];
                        if ($approveV['approve_status'] == 1) {
                            $approveV['opinion'] = '<span style="color:green">通过</span>';
                        } else {
                            $approveV['opinion'] = '<span style="color:red">不通过</span>';
                        }
                    }
                }
                $result['subsidiary'] = $subsidiary;
                $result['borrow'] = $borrow;
                $result['is_display'] = $is_display;
                $result['approve'] = $approve;
                $result['asArr'] = $assets;
                $result['status'] = 1;
                $this->ajaxReturn($result, 'json');

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
                    $result['status'] = -1;
                    $result['msg'] = '查找不到该设备借入信息';
                    return $result;
                }

                if ($borrow['status'] != "2") {
                    $is_display = false;
                } else {
                    $is_display = true;
                }
                $asModel = new \Admin\Model\AssetsInfoModel();
                $assets = $asModel->getAssetsInfo($borrow['assid']);
                //限制非该科室权限用户进入
                $departid = explode(',', session('departid'));
                if (!in_array($assets['departid'], $departid)) {
                    $result['msg'] = '该用户没有科室权限';
                    $result['status'] = '-1';
                    $this->ajaxReturn($result, 'json');
                    exit;
                }
                //判断有无查看原值的权限
                $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
                if (!$showPrice) {
                    $assets['buy_price'] = '***';
                }
                $assets = $this->add_ols($assets);

                $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
                $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
                $borrow['lendDepartment'] = $assets['department'];
                $borrow['assets'] = $assets['assets'];
                if ($approve) {
                    foreach ($approve as &$approveV) {
                        $borrow['approve'][$approveV['level']]['approver'] = $approveV['approver'];
                        $borrow['approve'][$approveV['level']]['approve_time'] = $approveV['approve_time'];
                        $borrow['approve'][$approveV['level']]['approve_status'] = $approveV['approve_status'];
                        if ($approveV['approve_status'] == 1) {
                            $approveV['opinion'] = '<span style="color:green">通过</span>';
                        } else {
                            $approveV['opinion'] = '<span style="color:red">不通过</span>';
                        }
                    }
                }
                $baseSetting = [];
                include APP_PATH . "Common/cache/basesetting.cache.php";
                $result['is_display'] = $is_display;
                $result['subsidiary'] = $subsidiary;
                $result['borrow'] = $borrow;
                $result['approve'] = $approve;
                $result['asArr'] = $assets;
                $result['apply_borrow_back_start_time'] = $baseSetting['assets']['apply_borrow_back_time']['value'][0];
                $result['apply_borrow_back_end_time'] = $baseSetting['assets']['apply_borrow_back_time']['value'][1];
                $result['status'] = 1;
                $this->ajaxReturn($result, 'json');
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
                        $approveV['opinion'] = $approveV['is_adoptName'];
                    }
                }
                //查询设备科管理
                $assetsApproveBorrowMenu = get_menu('Assets', 'Borrow', 'assetsApproveBorrow');
                if (!$borrow['assid']) {
                    $result['status'] = -1;
                    $result['msg'] = '查找不到该设备借入信息';
                    $this->ajaxReturn($result, 'json');
                    exit;
                }
                if ($borrow['status'] != "0") {
                    $is_display = false;
                } else if ($approve[0]['approve_status'] == 1 && !$assetsApproveBorrowMenu) {
                    $is_display = false;
                } else {
                    $is_display = true;
                }
                $assets = $this->add_ols($assets);
                $result['is_display'] = $is_display;
                $result['subsidiary'] = $subsidiary;
                $result['asArr'] = $assets;
                $result['status'] = 1;
                $result['approve'] = $approve;
                $result['borrow'] = $borrow;
                $result['approver'] = session('username');
                $result['approve_time'] = getHandleMinute(time());
                $this->ajaxReturn($result, 'json');
            } else {
                $this->error('非法操作');
            }
        }
    }

    //借入验收列表
    public function borrowInCheckList()
    {
        //获取借入验收列表数据
        $AssetsBorrowModel = new AssetsBorrowModel;
        $result = $AssetsBorrowModel->borrowInCheckList();
        $this->ajaxReturn($result, 'json');
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
        $action = I('get.action');
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
            $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME').'/#' . C('VUE_NAME') . '/Borrow/giveBackCheck?borid=' . $borid;
            if (strtotime($reminder_time) > time()) {
                $str = '预计';
            } else {
                $str = '实际';
            }

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
            $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME').'/#' . C('VUE_NAME') . '/Borrow/giveBackCheckList?action=showReminder&borid=' . $borid;
            $user = $AssetsBorrowModel->DB_get_one('user', 'openid', array('userid' => $borrow['apply_userid']));

            if ($user['openid']) {
                Weixin::instance()->sendMessage($user['openid'], '设备借调处理通知', [
                    'thing1'            => $borrow['department'],// 需求科室
                    'thing2'            => $assets['assets'],// 需求设备
                    'time5'             => $borrow['estimate_back'],// 需求结束时间
                    'character_string9' => $borrow['borrow_num'],// 借调流水号
                    'const10'           => '已逾期，请归还',// 借调状态
                ], $redecturl);
            }

            $result["status"] = 1;
            $result["msg"] = '发送成功，请耐心等待';
            $this->ajaxReturn($result, 'json');
        } else {
            $result["status"] = -1;
            $result["msg"] = '找不到这条记录';
            $this->ajaxReturn($result, 'json');
        }
    }

    //显示借调进程
    public function borrowLife()
    {

        $borid = I('GET.borid');
            if ($borid) {
                $AssetsBorrowModel = new \Admin\Model\AssetsBorrowModel;
                $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
                if (!$borrow['assid']) {
                    $result['status'] = -1;
                    $result['msg'] = '查找不到该设备借入信息';
                    return $result;
                }
                if ($borrow['status'] != "1") {
                    $is_display = false;
                } else {
                    $is_display = true;
                }
                $asModel = new \Admin\Model\AssetsInfoModel();
                $assets = $asModel->getAssetsInfo($borrow['assid']);
                //判断有无查看原值的权限
                $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
                if (!$showPrice) {
                    $assets['buy_price'] = '***';
                }
                $assets = $this->add_ols($assets);
                $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
                $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
                $borrow['lendDepartment'] = $assets['department'];
                $borrow['assets'] = $assets['assets'];
                if ($approve) {
                    foreach ($approve as &$approveV) {
                        $borrow['approve'][$approveV['level']]['approver'] = $approveV['approver'];
                        $borrow['approve'][$approveV['level']]['approve_time'] = $approveV['approve_time'];
                        $borrow['approve'][$approveV['level']]['approve_status'] = $approveV['approve_status'];
                        if ($approveV['approve_status'] == 1) {
                            $approveV['opinion'] = '<span style="color:green">通过</span>';
                        } else {
                            $approveV['opinion'] = '<span style="color:red">不通过</span>';
                        }
                    }
                }
                $borrowModel = new AssetsBorrowModel();
                $line= $borrowModel->get_progress_detail($borrow,$approve);
                $result['line'] = array_reverse($line);
                $result['borrow'] = $borrow;
                $result['approve'] = $approve;
                $result['asArr'] = $assets;
                $result['status'] = 1;
                $this->ajaxReturn($result, 'json');

            } else {
                $this->error('非法操作');
            }
        /*
        $this->ajaxReturn($result);*/
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
