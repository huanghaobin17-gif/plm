<?php

namespace Fs\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Fs\Controller\Login\IndexController;
use Fs\Model\AssetsBorrowModel;
use Fs\Model\AssetsInfoModel;
use Fs\Model\AssetsTransferModel;
use Fs\Model\WxAccessTokenModel;
use Admin\Model\OfflineSuppliersModel;

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
            if (strtotime($reminder_time) > time()) {
                $str = '预计';
            } else {
                $str = '实际';
            }
            //==========================================飞书 START========================================
            //要显示的字段区域
            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**设备名称：**'.$assets['assets'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**设备编码：**'.$assets['assnum'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**所属科室：**'.$assets['department'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**设备数量：**'.($borrow['subsidiary_num'] + 1);
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**归还人：**'.session('username');
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**归还时间：**'.$reminder_time . '(' . $str . ')';
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '请及时登录系统进行处理';
            $feishu_fields[] = $fd;

            //按钮区域
            $act['tag'] = 'button';
            $act['type'] = 'primary';
            $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME').'/#' . C('FS_NAME') . '/Borrow/giveBackCheck?borid=' . $borid;
            $act['text']['tag'] = 'plain_text';
            $act['text']['content'] = '登录系统';
            $feishu_actions[] = $act;

            $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
            $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
            $card_data['elements'][0]['tag'] = 'div';
            $card_data['elements'][0]['fields'] = $feishu_fields;
            $card_data['elements'][1]['tag'] = 'hr';
            $card_data['elements'][2]['actions'] = $feishu_actions;
            $card_data['elements'][2]['layout'] = 'bisected';
            $card_data['elements'][2]['tag'] = 'action';
            $card_data['header']['template'] = 'blue';
            $card_data['header']['title']['content'] = '设备归还通知';
            $card_data['header']['title']['tag'] = 'plain_text';

            $toUser = $this->getToUser(session('userid'), $assets['departid'], $moduleName, $controllerName, $actionName);
            foreach ($toUser as $k => $v) {
                $this->send_feishu_card_msg($v['openid'],$card_data);
            }
            //==========================================飞书 END==========================================
        } else {
            $result["code"] = 100;
            $result["msg"] = '找不到这条记录';
            $this->ajaxReturn($result, 'json');
            return;
        }
        $result["code"] = 200;
        $result["data"] = $card_data;
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
            //==========================================飞书 START========================================
            //要显示的字段区域
            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**设备名称：**'.$assets['assets'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**设备编号：**'.$assets['assnum'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**催还科室：**'.$assets['department'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**预计归还时间：**'.$borrow['estimate_back'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '**已逾期：**'.$borrow['overdue'];
            $feishu_fields[] = $fd;

            $fd['is_short'] = false;//是否并排布局
            $fd['text']['tag'] = 'lark_md';
            $fd['text']['content'] = '您借调的设备已经逾期，请及时归还';
            $feishu_fields[] = $fd;

            //按钮区域
            $act['tag'] = 'button';
            $act['type'] = 'primary';
            $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME').'/#' . C('FS_NAME') . '/Borrow/giveBackCheckList?action=showReminder&borid=' . $borid;
            $act['text']['tag'] = 'plain_text';
            $act['text']['content'] = '查看详情';
            $feishu_actions[] = $act;

            $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
            $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
            $card_data['elements'][0]['tag'] = 'div';
            $card_data['elements'][0]['fields'] = $feishu_fields;
            $card_data['elements'][1]['tag'] = 'hr';
            $card_data['elements'][2]['actions'] = $feishu_actions;
            $card_data['elements'][2]['layout'] = 'bisected';
            $card_data['elements'][2]['tag'] = 'action';
            $card_data['header']['template'] = 'red';
            $card_data['header']['title']['content'] = '设备借调超时提醒';
            $card_data['header']['title']['tag'] = 'plain_text';

            $user = $AssetsBorrowModel->DB_get_one('user', 'openid', array('userid' => $borrow['apply_userid']));
            $asModel->send_feishu_card_msg($user['openid'],$card_data);
            //==========================================飞书 END==========================================

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
