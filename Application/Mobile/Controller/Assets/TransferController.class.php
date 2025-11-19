<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/3/14
 * Time: 10:43
 */

namespace Mobile\Controller\Assets;


use Admin\Controller\Tool\ToolController;
use Admin\Model\CommonModel;
use Admin\Model\ModuleModel;
use Common\Weixin\Weixin;
use Mobile\Controller\Login\IndexController;
use Mobile\Model\AssetsInfoModel;
use Mobile\Model\AssetsTransferModel;
use Mobile\Model\RepairModel;
use Mobile\Model\WxAccessTokenModel;

class TransferController extends IndexController
{
    protected $fail_url = 'Notin/fail.html';//失败跳转地址
    protected $succ_url = 'Notin/suc.html';//成功跳转地址
    protected $index_url = 'Index/testindex.html';//首页地址
    protected $add_url = 'Transfer/getList.html';//转科申请地址
    protected $progress_url = 'Transfer/progress.html';//转科进程地址
    protected $approve_url = 'Transfer/approve.html';//转科审核地址
    protected $checkLists_url = 'Transfer/checkLists.html';//转科验收地址

    public function getList()
    {
        $departids = session('departid');
        $asModel = new AssetsInfoModel();
        if (IS_POST) {
            $tansferModel = new AssetsTransferModel();
            $result = $tansferModel->get_transfer_lists();
            $this->ajaxReturn($result, 'json');
        } else {
            //查询分类信息数据
            $cates = $asModel->get_all_category('transfer');
            $cates = getTree('parentid', 'catid', $cates, 0);
            //查询科室信息
            $departs = $asModel->get_all_department($departids, C('ASSETS_STATUS_USE') . ',' . C('ASSETS_STATUS_TRANSFER_ON'), C('NO_STATUS'));
            $departs = getTree('parentid', 'departid', $departs, 0);
            array_multisort(array_column($cates, 'assetssum'), SORT_DESC, $cates);
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
     * 转科申请
     */
    public function add()
    {
        $transModel = new AssetsTransferModel();
        $hospital_id = session('current_hospitalid');
        if (IS_POST) {
            $tmodel = new \Admin\Model\AssetsTransferModel();
            switch (I('post.type')) {
                case 'over':
                    # 结束进程
                    $result = $tmodel->endTransfer();
                    break;
                case 'save':
                    #重审
                    $result = $tmodel->updateTransfer();
                    break;
                default:
                    $result = $tmodel->addTransfer();
                    break;
            }
            $this->ajaxReturn($result, 'JSON');
        } else {
            $atid = I('get.atid');
            $this->assign('show_form', 1);
            if ($atid) {
                //查询转科单信息
                $zkInfo = $transModel->DB_get_one('assets_transfer', '*', array('atid' => $atid));
                //查询申请人联系电话
                $tel = $transModel->DB_get_one('user', 'telephone', array('username' => $zkInfo['applicant_user']));
                $zkInfo['telephone'] = $tel['telephone'];
                $assid = $zkInfo['assid'];
                $asInfo = $transModel->DB_get_one('assets_info', 'assid,assnum', array('assid' => $assid, 'is_delete' => '0'));
                $assnum = $asInfo['assnum'];
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
                $zkInfo['tranin_depart_name'] = $departname[$zkInfo['tranin_departid']]['department'];
                $zkInfo['departtel'] = $departname[$zkInfo['tranin_departid']]['departtel'];
                if ($zkInfo['retrial_status'] == 3 || $zkInfo['retrial_status'] == 2) {
                    //进程已结束
                    $this->assign('show_form', 0);
                } else {
                    //审批进程记录
                    $examine = $transModel->DB_get_all('approve', 'atid,approver,is_adopt,approve_time,remark', array('atid' => $atid), '', 'approve_time asc', '');
                    foreach ($examine as $k => $v) {
                        $examine[$k]['approve_time'] = getHandleDate($v['approve_time']);
                        $examine[$k]['approve_name'] = '转科审批';
                    }
                    $this->assign('approves', $examine);
                }
                $this->assign('transinfo', $zkInfo);
                $this->assign('type', 'save');
            } else {
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
                //二维码扫码进来，验证是否可以申请转科
                $this->scanQRcode_transfer();
            }
            //查询设备信息
            $assInfo = $transModel->get_assets_info($assnum);
            if (!$assInfo['assid']) {
                $this->assign('tips', '查找不到编码为 ' . $assnum . ' 的设备信息');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->add_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            //获取所有科室
            $departments = $transModel->DB_get_all('department', 'departid as value,department as title', array('hospital_id' => $hospital_id, 'is_delete' => 0, 'departid' => array('neq', $assInfo['departid'])));
            $this->assign('departments', json_encode($departments));
            $this->assign('date', date('Y-m-d'));
            $this->assign('time', date('Y-m-d H:i'));
            $this->assign('username', session('username'));
            $this->assign('asArr', $assInfo);
            $this->assign('url', get_url());
            $this->display();
        }
    }

    /*
     * 检查是否可以申请转科该设备
     */
    public function scanQRcode_transfer()
    {
        $transfer = new AssetsTransferModel();
        $departid = session('departid');
        $assnum = I('get.assnum');
        //微信扫码转科进入，查询
        $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status', array('assnum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        if (!$exists) {
            $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status', array('assorignum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        }
        if (!$exists) {
            $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status', array('assorignum_spare' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        }
        if (!$exists) {
            $this->assign('tips', '查找不到编码为 ' . $assnum . ' 的设备信息');
            $this->assign('btn', '返回列表页');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->add_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        if (!in_array($exists['departid'], explode(',', $departid))) {
            $this->assign('tips', '您无权操作该部门设备');
            $this->assign('btn', '返回列表页');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->add_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        if ($exists['status'] != C('ASSETS_STATUS_USE')) {
            //不是在用状态
            if($exists['status'] == C('ASSETS_STATUS_TRANSFER_ON')){
                //该设备在转科中，查询转科信息
                $atinfo = $transfer->DB_get_one('assets_transfer','atid',array('assid'=>$exists['assid']),'atid desc');
                if($atinfo['atid']){
                    $this->get_trans_info($atinfo['atid']);
                }
            }else{
                $this->assign('tips', '该设备暂不允许转科');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->add_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
        }
        return $exists;
    }

    private function get_trans_info($atid)
    {
        $transModel = new AssetsTransferModel();
        //查询转科单信息
        $zkInfo = $transModel->DB_get_one('assets_transfer', '*', array('atid' => $atid));
        //查询申请人联系电话
        $tel = $transModel->DB_get_one('user', 'telephone', array('username' => $zkInfo['applicant_user']));
        $zkInfo['telephone'] = $tel['telephone'];
        $assid = $zkInfo['assid'];
        $asInfo = $transModel->DB_get_one('assets_info', 'assid,assnum', array('assid' => $assid, 'is_delete' => '0'));
        $assnum = $asInfo['assnum'];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
        $zkInfo['tranin_depart_name'] = $departname[$zkInfo['tranin_departid']]['department'];
        $zkInfo['departtel'] = $departname[$zkInfo['tranin_departid']]['departtel'];
        if ($zkInfo['retrial_status'] == 3 || $zkInfo['retrial_status'] == 2) {
            //进程已结束
            $this->assign('show_form', 0);
        } else {
            //审批进程记录
            $examine = $transModel->DB_get_all('approve', 'atid,approver,is_adopt,approve_time,remark', array('atid' => $atid), '', 'approve_time asc', '');
            foreach ($examine as $k => $v) {
                $examine[$k]['approve_time'] = getHandleDate($v['approve_time']);
                $examine[$k]['approve_name'] = '转科审批';
            }
            $this->assign('approves', $examine);
        }
        $this->assign('transinfo', $zkInfo);
        $this->assign('type', 'save');
    }

    /*
     *  转科进程
     */
    public function progress()
    {
        $transferModel = new AssetsTransferModel();
        if (IS_POST) {
            $result = $transferModel->get_transfer_progress();
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('get.action');
            if ($action == 'detail') {
                $assnum = I('get.assnum');
                if ($assnum) {
                   $idarr = [];
                   if($assnum){
                    //查询设备assid
                    $idarr = $transferModel->DB_get_one('assets_info','assid',array('assnum'=>$assnum,'is_delete'=>'0'));
                    $assid = $idarr['assid'];
                    }
                   if (!$idarr) {
                    $idarr = $transferModel->DB_get_one('assets_info','assid',array('assorignum'=>$assnum,'is_delete'=>'0'));
                    $assid = $idarr['assid'];
                   }
                   if (!$idarr) {
                    $idarr = $transferModel->DB_get_one('assets_info','assid',array('assorignum_spare'=>$assnum,'is_delete'=>'0'));
                    $assid = $idarr['assid'];
                   }
                if (!$idarr) {
                   $this->assign('tips','查找不到编码为 ' . $assnum . ' 的设备信息');
                   $this->assign('btn','返回列表页');
                   $this->assign('url',$this->assets_list);
                   $this->display('Pub/Notin/fail');
                   exit;
                 }
                 $transferInfo = $transferModel->DB_get_one('assets_transfer', '*', array('assid' => $assid),'atid DESC');
                }else{
                $atid = I('get.atid');
                //查询设备信息
                $transferInfo = $transferModel->DB_get_one('assets_transfer', '*', array('atid' => $atid));
                }
                if (!$transferInfo) {
                    $this->assign('tips', '查找不到该设备转科信息');
                    $this->assign('btn', '返回列表页');
                    $this->assign('url', C('MOBILE_NAME').'/'.$this->progress_url);
                    $this->display('Pub/Notin/fail');
                    exit;
                }
                $asArr = $transferModel->DB_get_one('assets_info', 'assid,assets,assnum,model,serialnum,opendate', array('assid' => $transferInfo['assid'], 'is_delete' => '0'));
                //查询审批明细
                $approves = $transferModel->DB_get_all('approve', 'apprid,approver,approve_time,is_adopt,remark', ['atid' => $transferInfo['atid'],'is_delete'=>0], '', 'approve_time asc');
                //转科详细流程
                $line = $transferModel->get_progress_detail($transferInfo, $approves);
                $this->assign('line', array_reverse($line));
                $this->assign('asArr', $asArr);
                $this->assign('approves', $approves);
                $this->display('progress_detail');
            } else {
                $jssdk = new WxAccessTokenModel();
                $signPackage = $jssdk->GetSignPackage();
                $this->signPackage = $signPackage;
                $this->display();
            }
        }
    }

    /*
     *  转科审批
     */
    public function approval()
    {
        $transferModel = new AssetsTransferModel();
        if (IS_POST) {
            $apModel = new \Admin\Model\AssetsTransferModel();
            $result = $apModel->saveApprove();
            $this->ajaxReturn($result);
        } else {
            //查询转科审批是否已开启
            $isOpenApprove = $this->checkApproveIsOpen(C('TRANSFER_APPROVE'), session('current_hospitalid'));
            if (!$isOpenApprove) {
                $this->assign('tips', '转科审批已关闭');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->approve_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $atid = I('get.atid');
            //查询转科单信息
            $zkInfo = $transferModel->DB_get_one('assets_transfer', '*', array('atid' => $atid));
            $current_approver = explode(',', $zkInfo['current_approver']);
            if ($zkInfo['approve_status'] != '0') {
                $this->assign('is_display', 0);
            } else if (!in_array(session('username'), $current_approver)) {
                $this->assign('is_display', 0);
            } else {
                $this->assign('is_display', 1);
            }
            if (!$zkInfo['atid']) {
                $this->assign('tips', '查找不到该设备转科信息');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->approve_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            //查询申请人联系电话
            $tel = $transferModel->DB_get_one('user', 'telephone', array('username' => $zkInfo['applicant_user']));
            $zkInfo['applicant_time'] = date('Y-m-d H:i',strtotime($zkInfo['applicant_time']));
            $zkInfo['telephone'] = $tel['telephone'];
            //查询审批历史
            $apps = $transferModel->DB_get_all('approve', '*', array('atid' => $atid, 'is_delete' => 0, 'approve_class' => 'transfer', 'process_node' => C('TRANSFER_APPROVE')), '', 'process_node_level,apprid asc');
            foreach ($apps as $k => $v) {
                $apps[$k]['approve_time'] = getHandleMinute($v['approve_time']);
            }
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
            $zkInfo['tranin_depart_name'] = $departname[$zkInfo['tranin_departid']]['department'];
            $zkInfo['departtel'] = $departname[$zkInfo['tranin_departid']]['departtel'];
            //查找设备信息
            $asModel = new \Admin\Model\AssetsInfoModel();
            $asArr = $asModel->getAssetsInfo($zkInfo['assid']);
            //******************************8审批流程显示 start***************************//
            $reModel = new RepairModel();
            $zkInfo = $reModel->get_approves_progress($zkInfo,'atid','atid');
            //**************************************审批流程显示 end*****************************//
            //$asArr = $transferModel->DB_get_one('assets_info', 'assid,departid,assets,assnum,model,opendate,serialnum', array('assid' => $zkInfo['assid']));
            $asArr['department'] = $departname[$asArr['departid']]['department'];
            $this->assign('zkInfo', $zkInfo);
            $this->assign('approves', $apps);
            $this->assign('assets', $asArr);
            $this->assign('date', date('Y-m-d'));
            $this->assign('time', date('Y-m-d H:i'));
            $this->assign('username', session('username'));
            $this->assign('url', get_url());
            $this->display();
        }
    }

    public function checkLists()
    {
        $transferModel = new AssetsTransferModel();
        if (IS_POST) {
            $result = $transferModel->get_transfer_checklist();
            $this->ajaxReturn($result);
        } else {
            $this->display();
        }
    }

    /*
     * 转科验收操作
     */
    public function check()
    {
        $transferModel = new AssetsTransferModel();
        if (IS_POST) {
            //保存验收数据
            $atid = I('POST.atid');
            $zkInfo = $transferModel->DB_get_one('assets_transfer', 'atid,assid,transfernum,tranout_departid,tranout_departrespon,tranin_departid,address,is_check,applicant_user,applicant_time', array('atid' => $atid));
            if (!$zkInfo) {
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

            if (I('POST.checkdate') < getHandleTime(time())) {
                $res['status'] = -1;
                $res['msg'] = '验收日期不能小于当前日期';
                $this->ajaxReturn($res, 'JSON');
            }
            $assnum = $transferModel->DB_get_one('assets_info', 'assnum', array('assid' => $zkInfo['assid'], 'is_delete' => '0'));
            $uprow = $transferModel->updateData('assets_transfer', $data, array('atid' => $atid));
            if ($uprow) {
                $change_assid = [];
                $change_assid[] = $zkInfo['assid'];
                $subsidiary_assid = [];
                $subsidiary = $transferModel->DB_get_all('assets_transfer_detail', 'subsidiary_assid', ['atid' => ['EQ', $zkInfo['atid']]]);
                if ($subsidiary) {
                    foreach ($subsidiary as &$sub) {
                        $change_assid[] = $sub['subsidiary_assid'];
                        $subsidiary_assid[] = $sub['subsidiary_assid'];
                    }
                }
                $departInfo = $transferModel->DB_get_one('department', 'department', ['departid' => $zkInfo['tranout_departid']]);
                if ($data['is_check'] == C('YES_STATUS')) {
                    //验收通过
                    $departname = array();
                    include APP_PATH . "Common/cache/department.cache.php";
                    $transferModel->updateData('assets_info', array('status' => C('ASSETS_STATUS_USE'), 'departid' => $zkInfo['tranin_departid'], 'address' => $departname[$zkInfo['tranin_departid']]['address'], 'managedepart' => $departname[$zkInfo['tranin_departid']]['department']), ['assid' => ['IN', $change_assid]]);
                    //记录设备变更信息
                    $remark = '设备转科：' . $departname[$zkInfo['tranout_departid']]['department'] . '===>' . $departname[$zkInfo['tranin_departid']]['department'];
                    $all_subsidiaryWhere['main_assid'] = ['EQ', $zkInfo['assid']];
                    $all_subsidiaryWhere['status'][0] = 'NOTIN';
                    $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_SCRAP');//报废
                    $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
                    $all_subsidiaryWhere['is_delete'] = '0';
                    $all_subsidiaryData = $transferModel->DB_get_all('assets_info', 'assid', $all_subsidiaryWhere);
                    if ($all_subsidiaryData) {
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
                            $transferModel->updateData('assets_info', $diffData, $diffWhere);
                        }
                    }
                    $smsData['check_status'] = '通过';
                } else {
                    //验收不通过,更新设备最新状态为在用
                    $remark = '验收不通过！';
                    $transferModel->updateData('assets_info', array('status' => C('ASSETS_STATUS_USE')), ['assid' => ['IN', $change_assid]]);
                    $smsData['check_status'] = '不通过';
                }

                $asInfo = $transferModel->DB_get_one('assets_info', 'assnum,assets', ['assid' => $zkInfo['assid'], 'is_delete' => '0']);
                $telephone = $transferModel->DB_get_one('user', 'telephone,openid', array('username' => $zkInfo['applicant_user']));
                //==========================================短信 START==========================================
                $settingData = $transferModel->checkSmsIsOpen('Transfer');
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $smsData['tranout_department'] = $departname[$zkInfo['tranout_departid']]['department'];
                $smsData['tranin_department'] = $departname[$zkInfo['tranin_departid']]['department'];
                if ($settingData) {
                    //有开启短信
                    $smsData['assnum'] = $asInfo['assnum'];
                    $smsData['assets'] = $asInfo['assets'];
                    $smsData['transfer_num'] = $zkInfo['transfernum'];
                    $ToolMod = new ToolController();
                    $sms = $transferModel->formatSmsContent($settingData['checkTransferStatus']['content'], $smsData);
                    $ToolMod->sendingSMS($telephone['telephone'], $sms, 'Transfer', $zkInfo['atid']);
                }
                //==========================================短信 END==========================================

                //==================================微信通知验收结果 END====================================
                //判断是否开启微信端
                $moduleModel = new ModuleModel();
                $wx_status = $moduleModel->decide_wx_login();
                if ($wx_status && $telephone['openid']) {
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

                $transferModel->updateAllAssetsStatus($change_assid, C('ASSETS_STATUS_USE'), $remark);
                $log['assnum'] = $assnum['assnum'];
                $text = getLogText('acceptanceTransferLogText', $log);
                $transferModel->addLog('assets_transfer', M()->getLastSql(), $text, $zkInfo['atid']);
                $result['status'] = 1;
                $result['msg'] = '验收成功';
                $this->ajaxReturn($result, 'json');
            } else {
                $result['status'] = -1;
                $result['msg'] = '验收失败';
                $this->ajaxReturn($result, 'json');
            }
        } else {
            $atid = I('get.atid');
            //查询转科单信息
            $zkInfo = $transferModel->DB_get_one('assets_transfer', '*', array('atid' => $atid));
            if (!$zkInfo['atid']) {
                $this->assign('tips', '查找不到该设备转科信息');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->checkLists_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $departids = session('departid');
            $departids_arr = explode(',', $departids);
            if (!in_array($zkInfo['tranin_departid'], $departids_arr)) {
                $this->assign('tips', '对不起，您没有该设备转入科室的管理权限！');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->checkLists_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $zkInfo['applicant_time'] = date('Y-m-d H:i',strtotime($zkInfo['applicant_time']));
            $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
            $zkInfo['tranin_depart_name'] = $departname[$zkInfo['tranin_departid']]['department'];
            $zkInfo['departtel'] = $departname[$zkInfo['tranin_departid']]['departtel'];
            if ($zkInfo['is_check'] != '0') {
                $this->assign('is_display', 0);
            } else {
                $this->assign('is_display', 1);
            }
            //查找设备信息
            $asModel = new \Admin\Model\AssetsInfoModel();
            $asArr = $asModel->getAssetsInfo($zkInfo['assid']);
            $asArr['department'] = $departname[$asArr['departid']]['department'];
            $this->assign('zkInfo', $zkInfo);
            $this->assign('assets', $asArr);
            $this->assign('username', session('username'));
            $this->assign('time', date('Y-m-d H:i'));
            $this->assign('date', date('Y-m-d'));
            $this->assign('url', get_url());
            $this->display();
        }
    }
}
