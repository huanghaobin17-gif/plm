<?php

namespace Admin\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AssetsOutsideModel;
use Admin\Model\UserModel;

class OutsideController extends CheckLoginController
{
    //外调设备列表
    public function getDepartAssetsList()
    {
        if (IS_POST) {
            $AssetsOutsideModel = new AssetsOutsideModel();
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取外调申请列表数据
                    $result = $AssetsOutsideModel->getDepartAssetsList();
                    break;
            }
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('GET.action');
            switch ($action) {
                default:
                    //设备外调列表页面
                    $this->showGetDepartAssetsList();
                    break;
            }
        }
    }

    //设备外调列表页面
    private function showGetDepartAssetsList()
    {
        $this->assign('getDepartAssetsListUrl', get_url());
        $this->display();
    }

    //外调申请
    public function applyAssetOutSide()
    {
        if (IS_POST) {
            //var_dump($_POST);die;
            $action = I('POST.action');
            $AssetsOutsideModel = new AssetsOutsideModel();
            switch ($action) {
                case 'upload':
                    //文件上传
                    $result = $AssetsOutsideModel->uploadfile();
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'end':
                    //结束进程
                    $outid = I('POST.outid');
                    $result = $AssetsOutsideModel->endOutside($outid);
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'edit':
                    //重审
                    $outid = I('POST.outid'); 
                    $result = $AssetsOutsideModel->editOutside($outid);
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //外调申请提交
                    $result = $AssetsOutsideModel->applyAssetOutSide();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
   
            $assid = I('GET.assid');
            $outid = I('GET.outid');
      
            $AssetsOutsideModel = new AssetsOutsideModel();
            if ($outid) {
                 $outside_data = $AssetsOutsideModel->DB_get_one('assets_outside','assid,outid,apply_type,reason,accept,person,phone,outside_date,price',array('outid'=>$outid));
                 $assid = $outside_data['assid'];
                 if ($outside_data['outside_date']!=0) {
                    $outside_data['outside_date'] = getHandleTime($outside_data['outside_date']);
                 }else{
                    $outside_data['outside_date'] = null;
                 }
                 $approve = $AssetsOutsideModel->getOutsideApprovBasic($outid);
                 $file = $AssetsOutsideModel->getFileList(C('OUTSIDE_FILE_TYPE_APPLY'),$outid);
                 $this->assign('fileData', $file);
                 $this->assign('approve', $approve);
                 $this->assign('outside_data', $outside_data);
                 $this->assign('action', 'edit');
            }
            if (!$assid) {
                $this->error('非法操作');
            }
            $assets = $AssetsOutsideModel->getAssetsBasic($assid);
            $subsidiary = $AssetsOutsideModel->getAssetsSubsidiary($assid);
            $this->assign('assets', $assets);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('thisTime', getHandleDate(time()));
            $this->assign('apply_user', session('username'));
            $this->assign('applyAssetOutSideUrl', get_url());
            $this->display();
        }
    }

    //外调审批列表
    public function outSideApproveList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取外调审批列表数据
                    $AssetsOutsideModel = new AssetsOutsideModel();
                    $result = $AssetsOutsideModel->outSideApproveList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showApproveDetails':
                    //审批详情页面
                    $this->showApproveDetails();
                    break;
                default:
                    //外调审批列表页面
                    $this->showOutSideApproveList();
                    break;
            }
        }
    }

    //外调审批列表页面
    private function showOutSideApproveList()
    {
        $isOpenApprove = $this->checkApproveIsOpen(C('OUTSIDE_APPROVE'),session('job_hospitalid'));
        if (!$isOpenApprove) {
            $this->assign('errmsg', '外调审批未开启，如需开启，请联系管理员！');
            $this->display('Public/error');
        } else {
            $departid = explode(',', session('departid'));
            $department = $this->getDepartname($departid);
            $this->assign('outSideApproveListUrl', get_url());
            $this->assign('department', $department);
            $this->display();
        }
    }

    //外调审批详情页
    private function showApproveDetails()
    {
        $assid = I('GET.assid');
        $outid = I('GET.outid');
        if ($assid && $outid) {
            $AssetsOutsideModel = new AssetsOutsideModel();
            $assets = $AssetsOutsideModel->getAssetsBasic($assid);
            $outside = $AssetsOutsideModel->getOutsideBasic($outid);
            $approve = $AssetsOutsideModel->getOutsideApprovBasic($outid);
            $subsidiary=$AssetsOutsideModel->getSubsidiaryBasic($outid);
            $file = $AssetsOutsideModel->getFileList(C('OUTSIDE_FILE_TYPE_APPLY'),$outid);
            //******************************审批流程显示 start***************************//
            $outside = $AssetsOutsideModel->get_approves_progress($outside,'outid','outid');
            //**************************************审批流程显示 end*****************************//
            $this->assign('assets', $assets);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('outside', $outside);
            $this->assign('approve', $approve);
            $this->assign('fileData', $file);
            $this->display('showApproveDetails');
        } else {
            $this->error('非法操作');
        }
    }

    //外调审批
    public function assetOutSideApprove()
    {
        if (IS_POST) {
            $AssetsOutsideModel = new AssetsOutsideModel();
            $result = $AssetsOutsideModel->assetOutSideApprove();
            $this->ajaxReturn($result, 'json');
        } else {
            $assid = I('GET.assid');
            $outid = I('GET.outid');
            if ($assid && $outid) {
                $AssetsOutsideModel = new AssetsOutsideModel();
                $assets = $AssetsOutsideModel->getAssetsBasic($assid);
                $outside = $AssetsOutsideModel->getOutsideBasic($outid);
                $approve = $AssetsOutsideModel->getOutsideApprovBasic($outid);
                $subsidiary=$AssetsOutsideModel->getSubsidiaryBasic($outid);
                $file = $AssetsOutsideModel->getFileList(C('OUTSIDE_FILE_TYPE_APPLY'),$outid);
                //******************************审批流程显示 start***************************//
                $outside = $AssetsOutsideModel->get_approves_progress($outside,'outid','outid');
                //**************************************审批流程显示 end*****************************//
                $this->assign('approve_time', getHandleTime(time()));
                $this->assign('approver', session('username'));
                $this->assign('assets', $assets);
                $this->assign('outside', $outside);
                $this->assign('subsidiary', $subsidiary);
                $this->assign('approve', $approve);
                $this->assign('fileData', $file);
                $this->assign('assetOutSideApproveUrl', get_url());
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    //外调结果列表
    public function outSideResultList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'uploadReport':
                    //本地上传
                    $AssetsOutsideModel = new AssetsOutsideModel();
                    $result = $AssetsOutsideModel->uploadfile();
                    $result = $AssetsOutsideModel->addReportFile($result);
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'deleteFile':
                    //移除附件
                    $AssetsOutsideModel = new AssetsOutsideModel();
                    $result = $AssetsOutsideModel->deleteFile();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'batchPrint':
                    //批量打印模板
                    $this->batchPrintReport();
                    break;
                default:
                    //获取外结果列表数据
                    $AssetsOutsideModel = new AssetsOutsideModel();
                    $result = $AssetsOutsideModel->outSideResultList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showResultDetails':
                    //外调结果详情
                    $this->showOutSideResultDetails();
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
                    //外调结果列表页面
                    $this->showOutSideResultList();
                    break;
            }
        }
    }

    //批量打印报告
    private function batchPrintReport()
    {
        $AssetsOutsideModel = new AssetsOutsideModel();
        $userModel = new UserModel();
        $outid = trim(I('post.outid'));
        $outidArr = explode(',',$outid);
        $html = '';
        foreach ($outidArr as $k=>$v){
            $outid = $v;
            $outside = $AssetsOutsideModel->getOutsideBasic($outid);
            $assets = $AssetsOutsideModel->getAssetsBasic($outside['assid']);
            $approve = $AssetsOutsideModel->getOutsideApprovBasic($outid);
            $subsidiary=$AssetsOutsideModel->getSubsidiaryBasic($outid);
            //申请人员签名
            $apply_autograph = $AssetsOutsideModel->get_autograph_id($outside['apply_userid']);
            $this->assign('apply_autograph', $apply_autograph);
            $this->assign('apply_autograph_time', substr($outside['apply_time'],0,10));
            //设备科签名

            $departautograph = $AssetsOutsideModel->get_assets_autograph();
            $this->assign('departautograph', $departautograph);
            $this->assign('departautograph_time',substr($approve[count($approve)-1]['approve_time'],0,10));
            $this->assign('assets', $assets);
            $this->assign('date', getHandleTime(time()));
            $this->assign('subsidiary', $subsidiary);
            $this->assign('outside', $outside);
            $this->assign('approve', $approve);
            $marget_top = ($k+3)%3 == 0 ? 0 : 10;
            $this->assign('marget_top',$marget_top);
            $title = $AssetsOutsideModel->getprinttitle('assets', 'outside_template');
            $this->assign('title', $title);
            $baseSetting = array();
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
            $html .= $this->display('batch_print_report');
        }
        echo $html;exit;
    }


    //上传、查看报告
    private function uploadReport()
    {
        $outid = I('GET.outid');
        //查询对应的转科报告
        $AssetsOutsideModel = new AssetsOutsideModel();
        $files = $AssetsOutsideModel->getFileList(C('OUTSIDE_FILE_TYPE_REPORT'),$outid);
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
        $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/uploadReport?id=' . $outid . '&i=outid&t=assets_outside_file&username=' . session('username');
        $codeUrl = $AssetsOutsideModel->createCodePic($url);
        $codeUrl = trim($codeUrl, '.');
        $this->assign('codeUrl', $codeUrl);
        $this->assign('uploadinfo', $files);
        $this->assign('id', $outid);
        $this->assign('idName', 'out');
        $this->assign('uploadAction', get_url());
        $this->display('Public/uploadReport');
    }

    //打印模板页面
    private function showPrintReport()
    {
        $assid = I('GET.assid');
        $outid = I('GET.outid');
        if ($assid && $outid) {
            $AssetsOutsideModel = new AssetsOutsideModel();
            $assets = $AssetsOutsideModel->getAssetsBasic($assid);
            $outside = $AssetsOutsideModel->getOutsideBasic($outid);
            //申请人签名
            $apply_autograph = $AssetsOutsideModel->get_autograph_id($outside['apply_userid']);
            $approve = $AssetsOutsideModel->getOutsideApprovBasic($outid);
            $subsidiary=$AssetsOutsideModel->getSubsidiaryBasic($outid);
            $title = $AssetsOutsideModel->getprinttitle('assets','outside_template');
            $this->assign('title', $title);
            $this->assign('assets', $assets);
            $this->assign('apply_autograph', $apply_autograph);
            $this->assign('apply_autograph_time', substr($outside['apply_time'],0,10));
            //设备科签名
            $departautograph = $AssetsOutsideModel->get_assets_autograph();
            $this->assign('departautograph', $departautograph);
            $this->assign('departautograph_time',substr($approve[count($approve)-1]['approve_time'],0,10));
            $this->assign('date', getHandleTime(time()));
            $this->assign('subsidiary', $subsidiary);
            $this->assign('outside', $outside);
            $this->assign('approve', $approve);
            $baseSetting = array();
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
            $this->display('reportTemplate');
        } else {
            $this->error('非法操作');
        }
    }

    //外调结果列表页面
    private function showOutSideResultList()
    {
        $departid = explode(',', session('departid'));
        $department = $this->getDepartname($departid);
        $this->assign('outSideResultListUrl', get_url());
        $this->assign('department', $department);
        $this->display();
    }

    //外调结果详情页面
    private function showOutSideResultDetails()
    {
        $assid = I('GET.assid');
        $outid = I('GET.outid');
        if ($assid && $outid) {
            $AssetsOutsideModel = new AssetsOutsideModel();
            $assets = $AssetsOutsideModel->getAssetsBasic($assid);
            $outside = $AssetsOutsideModel->getOutsideBasic($outid);
            $approve = $AssetsOutsideModel->getOutsideApprovBasic($outid);
            $subsidiary=$AssetsOutsideModel->getSubsidiaryBasic($outid);
            $file = $AssetsOutsideModel->getFileList(C('OUTSIDE_FILE_TYPE_APPLY'),$outid);
            $this->assign('assets', $assets);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('outside', $outside);
            $this->assign('approve', $approve);
            $this->assign('fileData', $file);
            $this->display('showOutSideResultDetails');
        } else {
            $this->error('非法操作');
        }
    }

    //验收单录入
    public function checkOutSiteAsset()
    {
        if (IS_POST) {
            $action=I('POST.action');
            switch ($action){
                case 'upload':
                    //文件上传
                    $AssetsOutsideModel = new AssetsOutsideModel();
                    $result = $AssetsOutsideModel->uploadfile();
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $AssetsOutsideModel = new AssetsOutsideModel();
                    $result = $AssetsOutsideModel->checkOutSiteAsset();
                    $this->ajaxReturn($result, 'json');
                    break;
            }

        } else {
            $assid = I('GET.assid');
            $outid = I('GET.outid');
            if ($assid && $outid) {
                $AssetsOutsideModel = new AssetsOutsideModel();
                $assets = $AssetsOutsideModel->getAssetsBasic($assid);
                $outside = $AssetsOutsideModel->getOutsideBasic($outid);
                $approve = $AssetsOutsideModel->getOutsideApprovBasic($outid);
                $subsidiary=$AssetsOutsideModel->getSubsidiaryBasic($outid);
                $file = $AssetsOutsideModel->getFileList(C('OUTSIDE_FILE_TYPE_APPLY'),$outid);
                $this->assign('approve_time', getHandleTime(time()));
                $this->assign('approver', session('username'));
                $this->assign('subsidiary', $subsidiary);
                $this->assign('assets', $assets);
                $this->assign('outside', $outside);
                $this->assign('approve', $approve);
                $this->assign('fileData', $file);
                $this->assign('checkOutSiteAssetUrl', get_url());
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }


    /**
     * 结果查询
     */
    public function showOutSideResult()
    {
        $this->display();
    }
}