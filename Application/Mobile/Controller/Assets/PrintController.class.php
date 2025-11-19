<?php

namespace Mobile\Controller\Assets;

use Mobile\Model\WxAccessTokenModel;
use Think\Controller;
use Mobile\Controller\Login\IndexController;
use Mobile\Model\AssetsInfoModel;


class PrintController extends IndexController
{
    private $MODULE = 'Assets';
    protected $assets_list = 'Print/verify';//标签核实列表地址

    /**
     * 标签核实（列表）
     */
    public function verify()
    {
        $departids = session('departid');
        $asModel = new AssetsInfoModel();
        if (IS_POST) {
            $action = I('post.action');
            if($action == 'uploadReport'){
                $this->labelCheck();
            }else{
                $result = $asModel->get_verify_lists();
                $this->ajaxReturn($result, 'json');
            }
        } else {
            $action = I('get.action');
            if($action == 'labelCheck'){
                $this->labelCheck();
            }else{
                //查询分类信息数据
                $cates = $asModel->get_all_category('Print');
                $cates = getTree('parentid', 'catid', $cates, 0);
                //查询科室信息
                $departs = $asModel->get_all_department($departids,C('ASSETS_STATUS_USE') . ',' . C('ASSETS_STATUS_REPAIR') . ',' . C('ASSETS_STATUS_OUTSIDE_ON'). ',' . C('ASSETS_STATUS_OUTSIDE') . ',' . C('ASSETS_STATUS_SCRAP_ON') . ',' . C('ASSETS_STATUS_TRANSFER_ON'),'',0);
                $departs = getTree('parentid', 'departid', $departs, 0);
                array_multisort(array_column($cates, 'assetssum'), SORT_DESC, $cates);
                $this->assign('cates', $cates);
                $this->assign('departs', $departs);
                $this->assign('labelCheckListUrl', get_url());
                $jssdk = new WxAccessTokenModel();
                $signPackage = $jssdk->GetSignPackage();
                $this->signPackage = $signPackage;
                $this->display();
            }
        }
    }

    /**
     * 标签核实
     */
    public function labelCheck()
    {
        if (IS_POST) {
            $asModel = new AssetsInfoModel();
            $result = $asModel->uploadReport();
            $this->ajaxReturn($result, 'json');
        } else {
            $from = I('get.from');
            if ($from == 'jumpButton') {
                $buttonName = '设备图片';
                $tips = '标签状态在上传成功后，改为已核实（无法贴标）！';
                $type = 2;
            } else {
                $buttonName = '已贴标签图片';
                $tips = '标签状态在上传成功后，改为已核实！';
                $type = 1;
            }
            $asModel = new \Admin\Model\AssetsInfoModel();
            $assid = I('GET.assid');
            $assnum = I('GET.assnum');
            $status = I('GET.status');
            $departids = explode(',', session('departid'));
            if (!$assid) {
                $idarr = [];
                if ($assnum) {
                    //查询设备assid
                    $idarr = $asModel->DB_get_one('assets_info', 'assid', array('assnum' => $assnum, 'is_delete' => '0'));
                    $assid = $idarr['assid'];
                }
                if (!$idarr) {
                    $idarr = $asModel->DB_get_one('assets_info', 'assid', array('assorignum' => $assnum, 'is_delete' => '0'));
                    $assid = $idarr['assid'];
                }
                if (!$idarr) {
                    $idarr = $asModel->DB_get_one('assets_info', 'assid', array('assorignum_spare' => $assnum, 'is_delete' => '0'));
                    $assid = $idarr['assid'];
                }
                if (!$idarr) {
                    $this->assign('tips', '查找不到编码为 ' . $assnum . ' 的设备信息');
                    $this->assign('btn', '返回列表页');
                    $this->assign('url', C('MOBILE_NAME').'/'.$this->assets_list);
                    $this->display('Pub/Notin/fail');
                    exit;
                }
            }
            //组织表单第一部分数据设备基础信息
            $assets = $asModel->getAssetsInfo($assid);
            if (!in_array($assets['departid'], $departids)) {
                $this->assign('tips', '编码为 ' . $assnum . ' 的设备不属于您管理的科室');
                $this->assign('btn', '返回列表页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->assets_list);
                $this->display('Pub/Notin/fail');
                exit;
            }
            if ($type == 1 && $assets['print_status'] == 2 && !$status) {
                $this->assign('tips', '当前设备为核实（无法贴标）状态，是否确定要更改为已核实状态');
                $this->assign('btn1', '确定');
                $this->assign('url1', get_url() . '?assnum=' . $assnum . '&status=1');
                $this->assign('btn2', '取消');
                $this->assign('url2', C('MOBILE_NAME').'/'.$this->assets_list);
                $this->display('Pub/Notin/judge');
                exit;
            } else if ($type == 2 && $assets['print_status'] == 1 && !$status) {
                $this->assign('tips', '当前设备为已核实状态，是否确定要更改为核实（无法贴标）状态');
                $this->assign('btn1', '确定');
                $this->assign('url1', get_url() . '?assid=' . $assid . '&status=1');
                $this->assign('btn2', '取消');
                $this->assign('url2', C('MOBILE_NAME').'/'.$this->assets_list);
                $this->display('Pub/Notin/judge');
                exit;
            }
            $file_data = explode(",", $assets['pic_url']);
            $jssdk = new WxAccessTokenModel();
            $signPackage = $jssdk->GetSignPackage();
            $this->signPackage = $signPackage;
            $this->assign('assets', $assets);
            $this->assign('file_data', $file_data);
            $this->assign('buttonName', $buttonName);
            $this->assign('tips', $tips);
            $this->assign('type', $type);
            $this->display('labelCheck');
        }
    }
}