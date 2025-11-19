<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/3/14
 * Time: 10:43
 */

namespace App\Controller\Assets;


use Admin\Controller\Tool\ToolController;
use Admin\Model\ModuleModel;
use Admin\Model\OfflineSuppliersModel;
use App\Controller\Login\IndexController;
use App\Model\AssetsTransferModel;
use App\Model\RepairModel;
use App\Service\UserInfo\UserInfo;
use Common\Weixin\Weixin;

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
        $tansferModel = new AssetsTransferModel();
        $result       = $tansferModel->get_transfer_lists();
        $this->ajaxReturn($result, 'json');
    }

    /*
     * 转科申请
     */
    public function add()
    {
        $transModel  = new AssetsTransferModel();
        $hospital_id = session('current_hospitalid');
        if (IS_POST) {
            $tmodel = new \Admin\Model\AssetsTransferModel();
            switch (I('post.type')) {
                case 'end':
                    # 结束进程
                    $result = $tmodel->endTransfer();
                    break;
                case 'edit':
                    #重审
                    $result = $tmodel->updateTransfer();
                    break;
                default:
                    $result = $tmodel->addTransfer();
                    break;
            }
            $this->ajaxReturn($result, 'JSON');
        } else {
            $assnum = I('get.assnum');
            if (!$assnum) {
                $result['status'] = 302;
                $msg['tips']      = '参数非法';
                $msg['url']       = '';
                $msg['btn']       = '';
                $result['msg']    = json_encode($msg, JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result);
            }
            $action = I('get.action');
            if ($action == 'brcode') {
                //二维码扫码进来，验证是否可以申请转科
                $this->scanQRcode_transfer();
            }
            //查询设备信息
            $asInfo = $transModel->DB_get_one('assets_info', 'assid,assnum,status',
                [
                    'assnum'      => $assnum,
                    'is_delete'   => '0',
                    'hospital_id' => UserInfo::getInstance()->get('current_hospitalid'),
                ]);
            if (!$asInfo) {
                $asInfo = $transModel->DB_get_one('assets_info', '',
                    [
                        'assorignum'  => $assnum,
                        'hospital_id' => UserInfo::getInstance()->get('current_hospitalid'),
                        'is_delete'   => '0',
                    ]);
            }
            if (!$asInfo) {
                $asInfo = $transModel->DB_get_one('assets_info', '',
                    [
                        'assorignum_spare' => $assnum,
                        'hospital_id'      => UserInfo::getInstance()->get('current_hospitalid'),
                        'is_delete'        => '0',
                    ]);
            }
            if (!$asInfo) {
                $result['status'] = 302;
                $msg['tips']      = '查找不到设备编码为【' . $assnum . '】的信息';
                $msg['url']       = '';
                $msg['btn']       = '';
                $result['msg']    = json_encode($msg, JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result);
            }
            if ($asInfo['status'] != C('ASSETS_STATUS_USE')) {
                //不是在用状态
                if ($asInfo['status'] == C('ASSETS_STATUS_TRANSFER_ON')) {
                    //该设备在转科中，查询转科信息
                    $zkInfo = $transModel->DB_get_one('assets_transfer', '*', ['assid' => $asInfo['assid']],
                        'atid desc');
                    $atid   = $zkInfo['atid'];
                    if ($atid) {
                        $show_form = 1;
                        //查询申请人联系电话
                        $tel                 = $transModel->DB_get_one('user', 'telephone',
                            ['username' => $zkInfo['applicant_user']]);
                        $zkInfo['telephone'] = $tel['telephone'];
                        $departname          = [];
                        include APP_PATH . "Common/cache/department.cache.php";
                        $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
                        $zkInfo['tranin_depart_name']  = $departname[$zkInfo['tranin_departid']]['department'];
                        $zkInfo['departtel']           = $departname[$zkInfo['tranin_departid']]['departtel'];
                        if ($zkInfo['retrial_status'] == 3 || $zkInfo['retrial_status'] == 2) {
                            //进程已结束
                            $show_form = 0;
                        } else {
                            //审批进程记录
                            $examine = $transModel->DB_get_all('approve', 'atid,approver,is_adopt,approve_time,remark',
                                ['atid' => $atid, 'is_delete' => 0], '', 'approve_time asc', '');
                            foreach ($examine as $k => $v) {
                                $tmpuser                     = $transModel->DB_get_one('user', 'pic',
                                    ['username' => $v['approver']]);
                                $examine[$k]['user_pic']     = $tmpuser['pic'];
                                $examine[$k]['approve_time'] = getHandleMinute($v['approve_time']);
                                $examine[$k]['approve_name'] = '转科审批';
                            }
                        }
                        $result['transinfo'] = $zkInfo;
                    }
                } else {
                    $result['status'] = 302;
                    $msg['tips']      = '该设备暂不允许转科';
                    $msg['url']       = '';
                    $msg['btn']       = '';
                    $result['msg']    = json_encode($msg, JSON_UNESCAPED_UNICODE);
                    $this->ajaxReturn($result);
                }
            }
            //查询设备信息
            $assInfo = $transModel->get_assets_info($assnum);
            $assInfo = $this->add_ols($assInfo);
            //获取所有科室
            $departments = $transModel->DB_get_all('department', 'departid as value,department as title',
                ['hospital_id' => $hospital_id, 'is_delete' => 0, 'departid' => ['neq', $assInfo['departid']]]);
            if ($examine) {
                $assInfo['approves'] = $examine;
            }
            $result['show_form']         = $show_form;
            $result['departments']       = $departments;
            $result['departmentColumns'] = array_column($departments, 'title');
            $result['asArr']             = $assInfo;
            $result['username']          = session('username');
            $result['transferdate']      = getHandleTime(time());
            $result['status']            = 1;
            $this->ajaxReturn($result, 'JSON');
        }
    }

    /*
     * 检查是否可以申请转科该设备
     */
    public function scanQRcode_transfer()
    {
        $transfer = new AssetsTransferModel();
        $departid = session('departid');
        $assnum   = I('get.assnum');
        //微信扫码转科进入，查询
        $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status',
            ['assnum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')]);
        if (!$exists) {
            $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status',
                ['assorignum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')]);
        }
        if (!$exists) {
            $exists = $transfer->DB_get_one('assets_info', 'assid,departid,status',
                ['assorignum_spare' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')]);
        }
        if (!$exists) {
            $result['status'] = 302;
            $msg['tips']      = '查找不到设备编码为【' . $assnum . '】的信息';
            $msg['url']       = '';
            $msg['btn']       = '';
            $result['msg']    = json_encode($msg, JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
        }
        if (!in_array($exists['departid'], explode(',', $departid))) {
            $result['status'] = 302;
            $msg['tips']      = '您无权操作该部门设备';
            $msg['url']       = '';
            $msg['btn']       = '';
            $result['msg']    = json_encode($msg, JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
        }
        return $exists;
    }

    /*
     *  转科进程
     */
    public function progress()
    {
        $transferModel = new AssetsTransferModel();
        $action        = I('get.action');
        if ($action == 'detail') {
            $assnum = I('get.assnum');
            if ($assnum) {
                $idarr = [];
                if ($assnum) {
                    //查询设备assid
                    $idarr = $transferModel->DB_get_one('assets_info', 'assid',
                        ['assnum' => $assnum, 'is_delete' => '0']);
                    $assid = $idarr['assid'];
                }
                if (!$idarr) {
                    $idarr = $transferModel->DB_get_one('assets_info', 'assid',
                        ['assorignum' => $assnum, 'is_delete' => '0']);
                    $assid = $idarr['assid'];
                }
                if (!$idarr) {
                    $idarr = $transferModel->DB_get_one('assets_info', 'assid',
                        ['assorignum_spare' => $assnum, 'is_delete' => '0']);
                    $assid = $idarr['assid'];
                }
                if (!$idarr) {
                    $result['msg']    = '查找不到编码为 ' . $assnum . ' 的设备信息';
                    $result['status'] = -1;
                    $this->ajaxReturn($result, 'json');
                }
                $transferInfo = $transferModel->DB_get_one('assets_transfer', '*', ['assid' => $assid], 'atid DESC');
            } else {
                $atid = I('get.atid');
                //查询设备信息
                $transferInfo = $transferModel->DB_get_one('assets_transfer', '*', ['atid' => $atid]);
            }
            if (!$transferInfo) {
                $result['msg']    = '查找不到该设备转科信息';
                $result['status'] = -1;
                $this->ajaxReturn($result, 'json');
            }
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $transferInfo['tranout_depart_name'] = $departname[$transferInfo['tranout_departid']]['department'];
            $transferInfo['tranin_depart_name']  = $departname[$transferInfo['tranin_departid']]['department'];
            //设备信息
            $asArr                  = $transferModel->DB_get_one('assets_info',
                'assid,assets,assnum,model,serialnum,opendate',
                ['assid' => $transferInfo['assid'], 'is_delete' => '0']);
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
            //查询审批明细
            $approves     = $transferModel->DB_get_all('approve', 'apprid,approver,approve_time,is_adopt,remark',
                ['atid' => $transferInfo['atid'], 'is_delete' => 0], '', 'approve_time asc');
            $all_approves = $app_users = $userPic = [];
            if ($transferInfo['all_approver']) {
                //查询超级管理员
                $is_super     = $transferModel->DB_get_one('user', 'username,pic', ['is_super' => 1]);
                $all_approver = str_replace(",/" . $is_super['username'] . "/", '', $transferInfo['all_approver']);
                $all_approver = str_replace("/", '', $all_approver);
                $all_approver = explode(',', $all_approver);
                foreach ($all_approver as $k => $v) {
                    $upic                      = $transferModel->DB_get_one('user', 'username,pic', ['username' => $v]);
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
                $all_approves[$key]['approve_time'] = date('Y-m-d H:i', $value['approve_time']);
                $all_approves[$key]['remark']       = $value['remark'] ? $value['remark'] : '无备注';
                $all_approves[$key]['user_pic']     = $userPic[$value['approver']];
            }
            //转科详细流程
            $line                = $transferModel->get_progress_detail($transferInfo, $approves);
            $result['transInfo'] = $transferInfo;
            $result['asArr']     = $asArr;
            $result['line']      = array_reverse($line);
            $result['approves']  = $all_approves;
            $result['status']    = 1;
            $this->ajaxReturn($result, 'json');
        } else {
            $result = $transferModel->get_transfer_progress();
            $this->ajaxReturn($result, 'json');
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
            $result  = $apModel->saveApprove();
            $this->ajaxReturn($result);
        } else {
            //查询转科审批是否已开启
            $isOpenApprove = $this->checkApproveIsOpen(C('TRANSFER_APPROVE'), session('current_hospitalid'));
            if (!$isOpenApprove) {
                $result['msg']    = '转科审批已关闭';
                $result['status'] = -1;
                $this->ajaxReturn($result, 'json');
                exit;
            }
            $atid       = I('get.atid');
            $is_display = 1;
            //查询转科单信息
            $zkInfo           = $transferModel->DB_get_one('assets_transfer', '*', ['atid' => $atid]);
            $current_approver = explode(',', $zkInfo['current_approver']);
            if ($zkInfo['approve_status'] != '0') {
                $is_display = 0;
            } elseif (!in_array(session('username'), $current_approver)) {
                $is_display = 0;
            } else {
                $is_display = 1;
            }
            if (!$zkInfo['atid']) {
                $result['msg']    = '查找不到该设备转科信息';
                $result['status'] = -1;
                $this->ajaxReturn($result, 'json');
                exit;
            }
            //查询申请人联系电话
            $tel                      = $transferModel->DB_get_one('user', 'telephone',
                ['username' => $zkInfo['applicant_user']]);
            $zkInfo['applicant_time'] = date('Y-m-d H:i', strtotime($zkInfo['applicant_time']));
            $zkInfo['telephone']      = $tel['telephone'];
            //查询审批历史
            $approves     = $transferModel->DB_get_all('approve', '*', [
                'atid'          => $atid,
                'is_delete'     => 0,
                'approve_class' => 'transfer',
                'process_node'  => C('TRANSFER_APPROVE'),
            ], '', 'process_node_level,apprid asc');
            $all_approves = $app_users = $userPic = [];
            if ($zkInfo['all_approver']) {
                //查询超级管理员
                $is_super     = $transferModel->DB_get_one('user', 'username,pic', ['is_super' => 1]);
                $all_approver = str_replace(",/" . $is_super['username'] . "/", '', $zkInfo['all_approver']);
                $all_approver = str_replace("/", '', $all_approver);
                $all_approver = explode(',', $all_approver);
                foreach ($all_approver as $k => $v) {
                    $upic                      = $transferModel->DB_get_one('user', 'username,pic', ['username' => $v]);
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
                $all_approves[$key]['approve_time'] = date('Y-m-d H:i', $value['approve_time']);
                $all_approves[$key]['remark']       = $value['remark'] ? $value['remark'] : '无备注';
                $all_approves[$key]['user_pic']     = $userPic[$value['approver']];
            }

            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
            $zkInfo['tranin_depart_name']  = $departname[$zkInfo['tranin_departid']]['department'];
            $zkInfo['departtel']           = $departname[$zkInfo['tranin_departid']]['departtel'];
            //查找设备信息
            $asModel = new \Admin\Model\AssetsInfoModel();
            $asArr   = $asModel->getAssetsInfo($zkInfo['assid']);
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
                $factoryFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_facid']);
            }
            if ($offlineSuppliers['ols_supid']) {
                $supplierInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields,
                    ['olsid' => $offlineSuppliers['ols_supid']]);
                $supplierFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_supid']);
            }
            if ($offlineSuppliers['ols_repid']) {
                $repairInfo = $OfflineSuppliersModel->DB_get_one('offline_suppliers', $offlineSuppliersFields,
                    ['olsid' => $offlineSuppliers['ols_repid']]);
                $repairFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_repid']);
            }
            $asArr['factoryInfo']  = $factoryInfo;
            $asArr['supplierInfo'] = $supplierInfo;
            $asArr['repairInfo']   = $repairInfo;
            //******************************8审批流程显示 start***************************//
            $reModel = new RepairModel();
            $zkInfo  = $reModel->get_approves_progress($zkInfo, 'atid', 'atid');
            //**************************************审批流程显示 end*****************************//
            //$asArr = $transferModel->DB_get_one('assets_info', 'assid,departid,assets,assnum,model,opendate,serialnum', array('assid' => $zkInfo['assid']));
            $asArr['department']   = $departname[$asArr['departid']]['department'];
            $result['status']      = 1;
            $result['is_display']  = $is_display;
            $result['asArr']       = $asArr;
            $result['approveDate'] = date('Y-m-d');
            $result['approveUser'] = session('username');
            $result['approves']    = $all_approves;
            $result['transfer']    = $zkInfo;
            $this->ajaxReturn($result, 'json');
        }
    }

    public function checkLists()
    {
        $transferModel = new AssetsTransferModel();
        $result        = $transferModel->get_transfer_checklist();
        $this->ajaxReturn($result);

    }

    /*
     * 转科验收操作
     */
    public function check()
    {
        $transferModel = new AssetsTransferModel();
        if (IS_POST) {
            //保存验收数据
            $atid   = I('POST.atid');
            $zkInfo = $transferModel->DB_get_one('assets_transfer',
                'atid,assid,transfernum,tranout_departid,tranout_departrespon,tranin_departid,address,is_check,applicant_user,applicant_time',
                ['atid' => $atid]);
            if (!$zkInfo) {
                $result['status'] = -1;
                $result['msg']    = '查找不到转科设备信息';
                $this->ajaxReturn($result, 'json');
            }
            if ($zkInfo['is_check'] == 1) {
                $result['status'] = -1;
                $result['msg']    = '该设备已验收';
                $this->ajaxReturn($result, 'json');
            }
            $data['checkdate']  = getHandleDate(strtotime(I('POST.checkdate')));
            $data['check_user'] = session('username');
            $data['check_time'] = getHandleDate(time());
            $data['is_check']   = I('POST.res');
            $data['check']      = trim(I('POST.check'));

            if (I('POST.checkdate') < getHandleTime(time())) {
                $res['status'] = -1;
                $res['msg']    = '验收日期不能小于当前日期';
                $this->ajaxReturn($res, 'JSON');
            }
            $assnum = $transferModel->DB_get_one('assets_info', 'assnum',
                ['assid' => $zkInfo['assid'], 'is_delete' => '0']);
            $uprow  = $transferModel->updateData('assets_transfer', $data, ['atid' => $atid]);
            if ($uprow) {
                $change_assid     = [];
                $change_assid[]   = $zkInfo['assid'];
                $subsidiary_assid = [];
                $subsidiary       = $transferModel->DB_get_all('assets_transfer_detail', 'subsidiary_assid',
                    ['atid' => ['EQ', $zkInfo['atid']]]);
                if ($subsidiary) {
                    foreach ($subsidiary as &$sub) {
                        $change_assid[]     = $sub['subsidiary_assid'];
                        $subsidiary_assid[] = $sub['subsidiary_assid'];
                    }
                }
                $departInfo = $transferModel->DB_get_one('department', 'department',
                    ['departid' => $zkInfo['tranout_departid']]);
                if ($data['is_check'] == C('YES_STATUS')) {
                    //验收通过
                    $departname = [];
                    include APP_PATH . "Common/cache/department.cache.php";
                    $transferModel->updateData('assets_info', [
                        'status'       => C('ASSETS_STATUS_USE'),
                        'departid'     => $zkInfo['tranin_departid'],
                        'address'      => $departname[$zkInfo['tranin_departid']]['address'],
                        'managedepart' => $departname[$zkInfo['tranin_departid']]['department'],
                    ], ['assid' => ['IN', $change_assid]]);
                    //记录设备变更信息
                    $remark                             = '设备转科：' . $departname[$zkInfo['tranout_departid']]['department'] . '===>' . $departname[$zkInfo['tranin_departid']]['department'];
                    $all_subsidiaryWhere['main_assid']  = ['EQ', $zkInfo['assid']];
                    $all_subsidiaryWhere['status'][0]   = 'NOTIN';
                    $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_SCRAP');//报废
                    $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
                    $all_subsidiaryWhere['is_delete']   = '0';
                    $all_subsidiaryData                 = $transferModel->DB_get_all('assets_info', 'assid',
                        $all_subsidiaryWhere);
                    if ($all_subsidiaryData) {
                        $all_subsidiary_assid = [];
                        foreach ($all_subsidiaryData as $all_sub) {
                            $all_subsidiary_assid[] = $all_sub['assid'];
                        }
                        $all_subsidiary_assid = array_diff($all_subsidiary_assid, $subsidiary_assid);
                        if ($all_subsidiary_assid) {
                            //将未选中的附属设备变成无主
                            $diffData['main_assid']  = 0;
                            $diffData['main_assets'] = '';
                            $diffWhere['assid']      = ['IN', $all_subsidiary_assid];
                            $transferModel->updateData('assets_info', $diffData, $diffWhere);
                        }
                    }
                    $smsData['check_status'] = '通过';
                } else {
                    //验收不通过,更新设备最新状态为在用
                    $remark = '验收不通过！';
                    $transferModel->updateData('assets_info', ['status' => C('ASSETS_STATUS_USE')],
                        ['assid' => ['IN', $change_assid]]);
                    $smsData['check_status'] = '不通过';
                }

                $transferModel = $transModel = new AssetsTransferModel();
                $asInfo        = $transferModel->DB_get_one('assets_info', 'assnum,assets',
                    ['assid' => $zkInfo['assid'], 'is_delete' => '0']);
                $telephone     = $transferModel->DB_get_one('user', 'telephone,openid',
                    ['username' => $zkInfo['applicant_user']]);
                //==========================================短信 START==========================================
                $settingData = $transferModel->checkSmsIsOpen('Transfer');
                $departname  = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $smsData['tranout_department'] = $departname[$zkInfo['tranout_departid']]['department'];
                $smsData['tranin_department']  = $departname[$zkInfo['tranin_departid']]['department'];
                if ($settingData) {
                    //有开启短信
                    $smsData['assnum']       = $asInfo['assnum'];
                    $smsData['assets']       = $asInfo['assets'];
                    $smsData['transfer_num'] = $zkInfo['transfernum'];
                    $ToolMod                 = new ToolController();
                    $sms                     = $transferModel->formatSmsContent($settingData['checkTransferStatus']['content'],
                        $smsData);
                    $ToolMod->sendingSMS($telephone['telephone'], $sms, 'Transfer', $zkInfo['atid']);
                }
                //==========================================短信 END==========================================

                //==================================微信通知验收结果 END====================================
                //判断是否开启微信端
                $moduleModel = new ModuleModel();
                $wx_status   = $moduleModel->decide_wx_login();
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
                $text          = getLogText('acceptanceTransferLogText', $log);
                $transferModel->addLog('assets_transfer', M()->getLastSql(), $text, $zkInfo['atid']);
                $result['status'] = 1;
                $result['msg']    = '验收成功';
                $this->ajaxReturn($result, 'json');
            } else {
                $result['status'] = -1;
                $result['msg']    = '验收失败';
                $this->ajaxReturn($result, 'json');
            }
        } else {
            $atid = I('get.atid');
            //查询转科单信息
            $zkInfo = $transferModel->DB_get_one('assets_transfer', '*', ['atid' => $atid]);
            if (!$zkInfo['atid']) {
                $result['status'] = -1;
                $result['msg']    = '查找不到该设备转科信息';
                $this->ajaxReturn($result);
            }
            $departids     = session('departid');
            $departids_arr = explode(',', $departids);
            if (!in_array($zkInfo['tranin_departid'], $departids_arr)) {
                $result['status'] = -1;
                $result['msg']    = '对不起，您没有该设备转入科室的管理权限！';
                $this->ajaxReturn($result);
            }
            $departname = [];
            $is_display = 1;
            include APP_PATH . "Common/cache/department.cache.php";
            $zkInfo['applicant_time']      = date('Y-m-d H:i', strtotime($zkInfo['applicant_time']));
            $zkInfo['tranout_depart_name'] = $departname[$zkInfo['tranout_departid']]['department'];
            $zkInfo['tranin_depart_name']  = $departname[$zkInfo['tranin_departid']]['department'];
            $zkInfo['departtel']           = $departname[$zkInfo['tranin_departid']]['departtel'];
            if ($zkInfo['is_check'] != '0') {
                $is_display = 0;
            } else {
                $is_display = 1;
            }
            //查找设备信息
            $asModel               = new \Admin\Model\AssetsInfoModel();
            $asArr                 = $asModel->getAssetsInfo($zkInfo['assid']);
            $asArr['department']   = $departname[$asArr['departid']]['department'];
            $result['zkInfo']      = $zkInfo;
            $result['date']        = date('Y-m-d H:i');
            $result['username']    = session('username');
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $offlineSuppliers      = $OfflineSuppliersModel->DB_get_one('assets_factory',
                'ols_facid,ols_supid,ols_repid', ['assid' => $asArr['assid']]);
            $asArr                 = $this->add_ols($asArr);
            //查询审批明细
            $approves = $transferModel->DB_get_all('approve', 'apprid,approver,approve_time,is_adopt,remark',
                ['atid' => $zkInfo['atid'], 'is_delete' => 0], '', 'approve_time asc');
            foreach ($approves as $key => &$value) {
                if ($value['is_adopt'] == 1) {
                    $approves[$key]['opinion'] = '<span style="color:green">通过</span>';
                } else {
                    $approves[$key]['opinion'] = '<span style="color:red">不通过</span>';
                }
            }
            $result['is_display'] = $is_display;
            $result['asArr']      = $asArr;
            $result['approves']   = $approves;
            $result['status']     = 1;
            $this->ajaxReturn($result);
        }
    }
}
