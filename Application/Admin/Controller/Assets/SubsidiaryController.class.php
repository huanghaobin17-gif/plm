<?php

namespace Admin\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\SubsidiaryModel;

class SubsidiaryController extends CheckLoginController
{
    //附属设备分配管理列表
    public function subsidiaryAllotList(){
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取附属设备分配管理列表数据
                    $SubsidiaryModel = new SubsidiaryModel();
                    $result = $SubsidiaryModel->subsidiaryAllotList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                default:
                    //附属设备分配管理列表页面
                    $this->showSubsidiaryAllotList();
                    break;
            }
        }
    }

    //附属设备分配管理列表页面
    private function showSubsidiaryAllotList(){
        $departments = $this->getSelectDepartments();
        $this->assign('subsidiaryAllotListUrl', get_url());
        $this->assign('department', $departments);
        $this->display();
    }

    //附属设备分配申请
    public function subsidiaryAllot(){
        if (IS_POST) {
            $action=I('POST.action');
            switch ($action){
                case 'getAssetsDetail':
                    //获取科室对应的信息
                    $SubsidiaryModel = new SubsidiaryModel();
                    $result = $SubsidiaryModel->getAssetsDetail();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $SubsidiaryModel = new SubsidiaryModel();
                    $result = $SubsidiaryModel->subsidiaryAllot();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $assid = I('GET.assid');
            if (!$assid) {
                $this->error('非法操作');
            }
            $SubsidiaryModel = new SubsidiaryModel();
            $assets = $SubsidiaryModel->getAssetsBasic($assid);
            $departments = $this->getSelectDepartments();
            $this->assign('assets', $assets);
            $this->assign('apply_user',session('username'));
            $this->assign('apply_time',getHandleTime(time()));
            $this->assign('departname', $departments);
            $this->assign('subsidiaryAllotUrl', get_url());
            $this->display();
        }
    }

    //附属设备分配审批列表
    public function subsidiaryApproveList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取附属设备分配审批列表数据
                    $SubsidiaryModel = new SubsidiaryModel();
                    $result = $SubsidiaryModel->subsidiaryApproveList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showAllotDetails':
                    //详情页面
                    $this->showAllotDetails();
                    break;
                default:
                    //附属设备分配审批列表页面
                    $this->showSubsidiaryApproveList();
                    break;
            }
        }
    }

    //附属设备分配审批列表页面
    private function showSubsidiaryApproveList()
    {
        $isOpenApprove = $this->checkApproveIsOpen(C('SUBSIDIARY_APPROVE'),session('job_hospitalid'));
        if (!$isOpenApprove) {
            $this->assign('errmsg', '附属设备分配审批未开启，如需开启，请联系管理员！');
            $this->display('Public/error');
        } else {
            $departid = explode(',', session('departid'));
            $department = $this->getDepartname($departid);
            $this->assign('subsidiaryApproveListUrl', get_url());
            $this->assign('department', $department);
            $this->display();
        }
    }

    //附属设备分配审批详情页
    private function showAllotDetails()
    {
        $assid = I('GET.assid');
        $allotid = I('GET.allotid');
        if ($assid && $allotid) {
            $SubsidiaryModel = new SubsidiaryModel();
            $assets = $SubsidiaryModel->getAssetsBasic($assid);
            $allot = $SubsidiaryModel->getAllotBasic($allotid);
            $approve = $SubsidiaryModel->getSubsidiaryApprovBasic($allotid);
            $this->assign('assets', $assets);
            $this->assign('allot', $allot);
            $this->assign('approve', $approve);
            $this->display('showAllotDetails');
        } else {
            $this->error('非法操作');
        }
    }

    //附属设备分配审批
    public function subsidiaryApprove()
    {
        if (IS_POST) {
            $SubsidiaryModel = new SubsidiaryModel();
            $result = $SubsidiaryModel->subsidiaryApprove();
            $this->ajaxReturn($result, 'json');
        } else {
            $assid = I('GET.assid');
            $allotid = I('GET.allotid');
            if ($assid && $allotid) {
                $SubsidiaryModel = new SubsidiaryModel();
                $assets = $SubsidiaryModel->getAssetsBasic($assid);
                $allot = $SubsidiaryModel->getAllotBasic($allotid);
                $approve = $SubsidiaryModel->getSubsidiaryApprovBasic($allotid);
                $this->assign('approve_time', getHandleTime(time()));
                $this->assign('approver', session('username'));
                $this->assign('assets', $assets);
                $this->assign('allot', $allot);
                $this->assign('approve', $approve);
                $this->assign('subsidiaryApproveUrl', get_url());
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    //附属设备验收列表
    public function subsidiaryCheckList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取附属设备分配审批列表数据
                    $SubsidiaryModel = new SubsidiaryModel();
                    $result = $SubsidiaryModel->subsidiaryCheckList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showAllotDetails':
                    //详情页面
                    $this->showAllotDetails();
                    break;
                default:
                    //附属设备分配审批列表页面
                    $this->showSubsidiaryCheckList();
                    break;
            }
        }
    }


    //附属设备分配审批列表页面
    private function showSubsidiaryCheckList()
    {
        $departid = explode(',', session('departid'));
        $department = $this->getDepartname($departid);
        $this->assign('subsidiaryCheckListUrl', get_url());
        $this->assign('department', $department);
        $this->display();
    }

    //附属设备分配验收
    public function subsidiaryCheck()
    {
        if (IS_POST) {
            $SubsidiaryModel = new SubsidiaryModel();
            $result = $SubsidiaryModel->subsidiaryCheck();
            $this->ajaxReturn($result, 'json');
        } else {
            $assid = I('GET.assid');
            $allotid = I('GET.allotid');
            if ($assid && $allotid) {
                $SubsidiaryModel = new SubsidiaryModel();
                $assets = $SubsidiaryModel->getAssetsBasic($assid);
                $allot = $SubsidiaryModel->getAllotBasic($allotid);
                $approve = $SubsidiaryModel->getSubsidiaryApprovBasic($allotid);
                $this->assign('approve_time', getHandleTime(time()));
                $this->assign('approver', session('username'));
                $this->assign('assets', $assets);
                $this->assign('allot', $allot);
                $this->assign('approve', $approve);
                $this->assign('subsidiaryCheckUrl', get_url());
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }
}