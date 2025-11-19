<?php

namespace Admin\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AssetsBorrowModel;
use Admin\Model\DepartmentModel;

class BorrowController extends CheckLoginController
{
    //借调申请设备列表
    public function borrowAssetsList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取借调申请列表数据
                    $AssetsBorrowModel = new AssetsBorrowModel;
                    $result = $AssetsBorrowModel->borrowAssetsList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                default:
                    //借调设备列表页面
                    $this->showBorrowAssetsList();
                    break;
            }
        }
    }

    //借调设备列表页面
    private function showBorrowAssetsList()
    {   
        //echo 123;die; 
        $where['is_delete'] = 0;
        $where['departid'] = array('neq', session('job_departid'));
        $where['hospital_id'] = session('current_hospitalid');
        $departModel = new DepartmentModel();
        $departments = $departModel->DB_get_all('department', 'departid,department', $where);
        $this->assign('borrowAssetsListUrl', get_url());
        $this->assign('department', $departments);
        $this->display();
    }

    //申请借调
    public function applyBorrow()
    {
        if (IS_POST) {
            $type = I('POST.type');
            $AssetsBorrowModel = new AssetsBorrowModel();
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
                    //借调申请
                    $result = $AssetsBorrowModel->applyBorrow();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $assid = I('GET.assid');
            $AssetsBorrowModel = new AssetsBorrowModel;
            //echo 133;die;
            if (I('GET.borid')) {
                $assets_borrow_data = $AssetsBorrowModel->DB_get_one('assets_borrow', 'assid,apply_departid,borrow_num,borrow_reason,estimate_back,borid', array('borid' => I('GET.borid')));
                $assid = $assets_borrow_data['assid'];
                $assets_borrow_data['estimate_back'] = date('Y-m-d H:i', $assets_borrow_data['estimate_back']);
                $assets_borrow_data['type'] = 'edit';
                $this->assign('assets_borrow_data', $assets_borrow_data);
                $approve = $AssetsBorrowModel->getBorrowApprovBasic(I('GET.borid'));
                $this->assign('approve', $approve);
            }
            if (!$assid) {
                $this->error('非法操作');
            }
            $assets = $AssetsBorrowModel->getAssetsBasic($assid);
            if ($assets_borrow_data) {
                $flowNumber = $assets_borrow_data['borrow_num'];
            } else {
                $flowNumber = $AssetsBorrowModel->getFlowNumber($assid);
                //echo $flowNumber;die;
            }
            $subsidiary = $AssetsBorrowModel->getAssetsSubsidiary($assid);
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            if (session('isSuper') == C('YES_STATUS')) {
                $departname = $this->getDepartname();
                foreach ($departname as $key => $value) {
                    if ($assets['departid'] == $value['departid']) {
                        unset($departname[$key]);
                    }
                }
                sort($departname);
                $this->assign('departname', $departname);
            } 
            $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
            if (!$showPrice) {
                $assets['buy_price'] = '***';
            }
            $this->assign('assets', $assets);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('flowNumber', $flowNumber);
            $this->assign('borrow_in_time', getHandleDate(time()));
            $this->assign('apply_user', session('username'));
            $this->assign('applyBorrowUrl', get_url());
            $this->assign('apply_borrow_back_start_time', $baseSetting['assets']['apply_borrow_back_time']['value'][0]);
            $this->assign('apply_borrow_back_end_time', $baseSetting['assets']['apply_borrow_back_time']['value'][1]);
            $this->display();
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
       // var_dump($departid);die;
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
            //var_dump($assets);die;
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
            $AssetsBorrowModel = new AssetsBorrowModel;
            $result = $AssetsBorrowModel->approveBorrow();
            $this->ajaxReturn($result, 'json');
        } else {
            $assid = I('GET.assid');
            $borid = I('GET.borid');
            if ($assid && $borid) {
                $AssetsBorrowModel = new AssetsBorrowModel;
                $assets = $AssetsBorrowModel->getAssetsBasic($assid);
                $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
                $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
                $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
                $this->assign('approve_time', getHandleTime(time()));
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

    //借入验收操作
    public function borrowInCheck()
    {
        if (IS_POST) {
            $AssetsBorrowModel = new AssetsBorrowModel;
            $result = $AssetsBorrowModel->borrowInCheck();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->error('非法操作');
        }
    }


    //归还验收列表
    public function giveBackCheckList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
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
                default:
                    //归还验收列表页面
                    $this->showGiveBackCheckList();
                    break;
            }
        }
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

    //归还验收操作
    public function giveBackCheck()
    {
        if (IS_POST) {
            $AssetsBorrowModel = new AssetsBorrowModel;
            $result = $AssetsBorrowModel->giveBackCheck();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->error('非法操作');
        }
    }

    //借调进程
    public function borrowLife()
    {
        if (IS_POST) {
            $AssetsBorrowModel = new AssetsBorrowModel;
            $result = $AssetsBorrowModel->borrowLife();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->assign('borrowLifeUrl', get_url());
            $this->display();
        }
    }


    //借调记录列表
    public function borrowRecordList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'uploadReport':
                    //本地上传
                    $AssetsBorrowModel = new AssetsBorrowModel;
                    $result = $AssetsBorrowModel->uploadfile();
                    $result = $AssetsBorrowModel->addReportFile($result);
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'deleteFile':
                    //移除附件
                    $AssetsBorrowModel = new AssetsBorrowModel;
                    $result = $AssetsBorrowModel->deleteFile();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'batchPrint':
                    //批量打印模板
                    $this->batchPrintReport();
                    break;
                default:
                    //获取借调申请列表数据
                    $AssetsBorrowModel = new AssetsBorrowModel;
                    $result = $AssetsBorrowModel->borrowRecordList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                //借调记录详情页
                case 'showBorrowRecordDetails':
                    $this->showBorrowRecordDetails();
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
                    //借调记录列表页
                    $this->showBorrowRecordList();
                    break;
            }
        }
    }

    //打印模板页面
    private function showPrintReport()
    {
        $assid = I('GET.assid');
        $borid = I('GET.borid');
        if ($assid && $borid) {
            $AssetsBorrowModel = new AssetsBorrowModel;
            $assets = $AssetsBorrowModel->getAssetsBasic($assid);
            $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
            $borrow_in_autograph = $AssetsBorrowModel->get_autograph_id($borrow['borrow_in_userid']);
            $data = $AssetsBorrowModel->DB_get_one('assets_borrow_approve','approve_userid',array('level'=>2,'borid'=>$borrow['borid']));
            $departautograph = $AssetsBorrowModel->get_assets_autograph();
            $give_back_autograph = $AssetsBorrowModel->get_autograph_id($borrow['give_back_userid']);
            $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
            $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
            $borrow['lendDepartment'] = $assets['department'];
            $borrow['assets'] = $assets['assets'];
            if ($borrow['status'] == C('BORROW_STATUS_NOT_APPLY')) {
                $this->assign('end_reason', true);
            }
            if ($borrow['status'] == C('BORROW_STATUS_GIVE_BACK') or $borrow['status'] == C('BORROW_STATUS_COMPLETE')) {
                $this->assign('borrow_in', true);
                if ($borrow['status'] == C('BORROW_STATUS_COMPLETE')) {
                    $this->assign('give_back', true);
                }
            }
            if ($approve) {
                foreach ($approve as &$approveV) {
                    $borrow['approve'][$approveV['level']]['approver'] = $approveV['approver'];
                    $borrow['approve'][$approveV['level']]['approve_time'] = $approveV['approve_time'];
                    $borrow['approve'][$approveV['level']]['approve_status'] = $approveV['approve_status'];
                }
            }
            $title = $AssetsBorrowModel->getprinttitle('assets','borrow_template');
            $this->assign('title', $title);
            $this->assign('date', getHandleTime(time()));
            $this->assign('subsidiary', $subsidiary);
            $this->assign('assets', $assets);
            $this->assign('give_back_autograph', $give_back_autograph);
            $this->assign('departautograph', $departautograph);
            $this->assign('borrow_in_autograph', $borrow_in_autograph);
            $this->assign('give_back_autograph_time', substr($borrow['give_back_time'],0,10));
            $this->assign('departautograph_time', substr($approve[count($approve)-1]['approve_time'],0,10));
            $this->assign('borrow_in_autograph_time', substr($borrow['borrow_in_time'],0,10));
            $this->assign('borrow', $borrow);
            $this->assign('approve', $approve);
            $baseSetting = array();
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
            $this->display('reportTemplate');
        } else {
            $this->error('非法操作');
        }
    }

    //批量打印报告
    private function batchPrintReport()
    {
        $AssetsBorrowModel = new AssetsBorrowModel;
        $borid = trim(I('post.borid'));
        $boridArr = explode(',', $borid);
        $html = '';
        foreach ($boridArr as $k => $v) {
            $borid = $v;
            $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
            $assets = $AssetsBorrowModel->getAssetsBasic($borrow['assid']);
            $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
            $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
            $borrow['lendDepartment'] = $assets['department'];
            $borrow['assets'] = $assets['assets'];
            if ($borrow['status'] == C('BORROW_STATUS_NOT_APPLY')) {
                $this->assign('end_reason', true);
            }
            if ($borrow['status'] == C('BORROW_STATUS_GIVE_BACK') or $borrow['status'] == C('BORROW_STATUS_COMPLETE')) {
                $this->assign('borrow_in', true);
                if ($borrow['status'] == C('BORROW_STATUS_COMPLETE')) {
                    $this->assign('give_back', true);
                }
            }
            if ($approve) {
                foreach ($approve as &$approveV) {
                    $borrow['approve'][$approveV['level']]['approver'] = $approveV['approver'];
                    $borrow['approve'][$approveV['level']]['approve_time'] = $approveV['approve_time'];
                    $borrow['approve'][$approveV['level']]['approve_status'] = $approveV['approve_status'];
                }
            }
            $borrow_in_autograph = $AssetsBorrowModel->get_autograph_id($borrow['borrow_in_userid']);
            $data = $AssetsBorrowModel->DB_get_one('assets_borrow_approve','approve_userid',array('level'=>2,'borid'=>$borrow['borid']));
            $departautograph = $AssetsBorrowModel->get_assets_autograph();
            $give_back_autograph = $AssetsBorrowModel->get_autograph_id($borrow['give_back_userid']);
            $title = $AssetsBorrowModel->getprinttitle('assets','borrow_template');
            $this->assign('title', $title);
            $this->assign('date', getHandleTime(time()));
            $this->assign('subsidiary', $subsidiary);
            $this->assign('give_back_autograph', $give_back_autograph);
            $this->assign('departautograph', $departautograph);
            $this->assign('borrow_in_autograph', $borrow_in_autograph);
            $this->assign('give_back_autograph_time', substr($borrow['give_back_time'],0,10));
            $this->assign('departautograph_time', substr($approve[count($approve)-1]['approve_time'],0,10));
            $this->assign('borrow_in_autograph_time', substr($borrow['borrow_in_time'],0,10));
            $this->assign('assets', $assets);
            $this->assign('borrow', $borrow);
            $this->assign('approve', $approve);
            $marget_top = ($k + 2) % 2 == 0 ? 0 : 10;
            $this->assign('marget_top', $marget_top);
            $baseSetting = array();
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
            $html .= $this->display('batch_print_report');
        }
        echo $html;
        exit;
    }


    //上传、查看报告
    private function uploadReport()
    {
        $borid = I('GET.borid');
        //查询对应的转科报告
        $AssetsBorrowModel = new AssetsBorrowModel();
        $files = $AssetsBorrowModel->getFileList($borid);
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
        $url = "$protocol".C('HTTP_HOST'). C('ADMIN_NAME').'/Public/uploadReport?id=' . $borid . '&i=borid&t=assets_borrow_file&username=' . session('username');
        $codeUrl = $AssetsBorrowModel->createCodePic($url);
        $codeUrl = trim($codeUrl, '.');
        $this->assign('codeUrl', $codeUrl);
        $this->assign('uploadinfo', $files);
        $this->assign('id', $borid);
        $this->assign('idName', 'bor');
        $this->assign('uploadAction', get_url());
        $this->display('Public/uploadReport');
    }

    //借调记录列表页
    private function showBorrowRecordList()
    {
        $department = $this->getDepartname();
        $this->assign('borrowRecordListUrl', get_url());
        $this->assign('department', $department);
        $this->display();
    }

    //借调记录详情页
    private function showBorrowRecordDetails()
    {
//        如果是生命历程页面进入
        $showLifeBorrow = I('get.showLifeBorrow');
        $assid = I('GET.assid');
        $borid = I('GET.borid');
        if ($assid && $borid) {
            $AssetsBorrowModel = new AssetsBorrowModel;
            $assets = $AssetsBorrowModel->getAssetsBasic($assid);
            $borrow = $AssetsBorrowModel->getBorrowBasic($borid);
            $approve = $AssetsBorrowModel->getBorrowApprovBasic($borid);
            $subsidiary = $AssetsBorrowModel->getSubsidiaryBasic($borid);
            $borrow['lendDepartment'] = $assets['department'];
            $borrow['assets'] = $assets['assets'];
            if ($borrow['status'] == C('BORROW_STATUS_NOT_APPLY')) {
                $this->assign('end_reason', true);
            }
            if ($borrow['status'] == C('BORROW_STATUS_GIVE_BACK') or $borrow['status'] == C('BORROW_STATUS_COMPLETE')) {
                $this->assign('borrow_in', true);
                if ($borrow['status'] == C('BORROW_STATUS_COMPLETE')) {
                    $this->assign('give_back', true);
                }
            }
            if ($approve) {
                foreach ($approve as &$approveV) {
                    $borrow['approve'][$approveV['level']]['approver'] = $approveV['approver'];
                    $borrow['approve'][$approveV['level']]['approve_time'] = $approveV['approve_time'];
                    $borrow['approve'][$approveV['level']]['approve_status'] = $approveV['approve_status'];
                }
            }
            //时间线进度条
            $borrowTimeLineProgress = $borrowTimeLine = $AssetsBorrowModel->showBorrowTimeLineData($borrow);
            //去除空项
            foreach ($borrowTimeLine as $k => $v) {
                if ($borrowTimeLine[$k]['date'] == '-' or !$borrowTimeLine[$k]['date']) {
                    unset($borrowTimeLine[$k]);
                }
            }
            $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
            if (!$showPrice) {
                $assets['buy_price'] = '***';
            }
            $this->assign('subsidiary', $subsidiary);
            $this->assign('assets', $assets);
            $this->assign('borrow', $borrow);
            $this->assign('approve', $approve);
            $this->assign('borrowTimeLineProgress', $borrowTimeLineProgress);
            $this->assign('borrowTimeLine', array_reverse($borrowTimeLine));
            $this->assign('showLifeBorrow', $showLifeBorrow);
            $this->display('showBorrowRecordDetails');
        } else {
            $this->error('非法操作');
        }
    }


}