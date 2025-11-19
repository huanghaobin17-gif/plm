<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/3/6
 * Time: 10:24
 */

namespace Vue\Controller\Assets;

use Admin\Model\AdverseModel;
use Admin\Model\AssetsBorrowModel;
use Admin\Model\AssetsInsuranceModel;
use Admin\Model\OfflineSuppliersModel;
use Vue\Model\WxAccessTokenModel;
use Think\Controller;
use Vue\Controller\Login\IndexController;
use Vue\Model\AssetsInfoModel;

class LookupController extends IndexController
{
    protected $assets_list = 'Lookup/getAssetsList';//设备列表地址

    //主设备列表
    public function getAssetsList()
    {
        $asModel = new AssetsInfoModel();
        $result = $asModel->get_assets_lists();
        $this->ajaxReturn($result, 'json');
    }

    public function showAssets()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'doRenewal':
                    //获取参保记录
                    $AssetsInsuranceModel = new AssetsInsuranceModel();
                    $result = $AssetsInsuranceModel->getAssetsInsuranceList();
                    $result['status'] = 1;
                    $this->ajaxReturn($result, 'json');
                    break;

                default:
                    break;
            }
        }else{
            $asModel = new \Admin\Model\AssetsInfoModel();
            $assid = I('GET.assid');
            $assnum = I('GET.assnum');
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
                    $result['status'] = 302;
                    $msg['tips'] = '查找不到编码为 ' . $assnum . ' 的设备信息';
                    $msg['url'] = '';
                    $msg['btn'] = '';
                    $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
                    $this->ajaxReturn($result, 'json');
                }
            }
            //组织表单第一部分数据设备基础信息
            $assets = $asModel->getAssetsInfo($assid);
            if(!$assets){
                $result['status'] = 302;
                $msg['tips'] = '查找不到设备信息';
                $msg['url'] = '';
                $msg['btn'] = '';
                $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result, 'json');
            }
            $assnum = $assets['assnum'];
            if (!in_array($assets['departid'], $departids)) {
                $result['status'] = 302;
                $msg['tips'] = '编码为 ' . $assnum . ' 的设备不属于您管理的科室';
                $msg['url'] = '';
                $msg['btn'] = '';
                $result['msg'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result, 'json');
            }
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            if ($assets['pic_url']) {
                $assets['pic_url'] = explode(',', $assets['pic_url']);
                foreach ($assets['pic_url'] as &$pic) {
                    $pic = "$protocol" . C('HTTP_HOST') . '/Public/uploads/assets/' . $pic;
                }
            } else {
                $assets['pic_url'] = 0;
            }
            //判断有无查看原值的权限
            $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
            if (!$showPrice) {
                $assets['buy_price'] = '***';
                $assets['depreciable_quota_m'] = '***';
                $assets['depreciable_quota_count'] = '***';
                $assets['net_asset_value'] = '***';
                $assets['net_assets'] = '***';
            }
            //var_dump($assets);exit;
            //组织表单第三部分厂商信息
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $offlineSuppliers = $asModel->DB_get_one('assets_factory', 'ols_facid,ols_supid,ols_repid', array('assid' => $assets['assid']));
            $factoryInfo = [];
            $supplierInfo = [];
            $repairInfo = [];
            $factoryFile = [];
            $supplierFile = [];
            $repairFile = [];
            $offlineSuppliersFields = 'olsid,sup_name,salesman_name,salesman_phone,artisan_name,artisan_phone';
            if ($offlineSuppliers['ols_facid']) {
                $factoryInfo = $asModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_facid']]);
                $factoryFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_facid']);
                sort($factoryFile);
            }
            if ($offlineSuppliers['ols_supid']) {
                $supplierInfo = $asModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_supid']]);
                $supplierFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_supid']);
                sort($supplierFile);
            }
            if ($offlineSuppliers['ols_repid']) {
                $repairInfo = $asModel->DB_get_one('offline_suppliers', $offlineSuppliersFields, ['olsid' => $offlineSuppliers['ols_repid']]);
                $repairFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_repid']);
                sort($repairFile);
            }
            //生命历程
            $life = $asModel->getLifeInfo($assid);
            array_multisort(array_column($life, 'sort_time'), SORT_DESC, $life);
            $result['status'] = 1;
            $assets['factoryInfo'] = $factoryInfo;
            $assets['supplierInfo'] = $supplierInfo;
            $assets['repairInfo'] = $repairInfo;
            $assets['factoryFile'] = $factoryFile;
            $assets['supplierFile'] = $supplierFile;
            $assets['repairFile'] = $repairFile;

            // 技术资料
            //判断有无查看技术资料的权限
            $showTechnicalInformation = get_menu('Assets', 'Lookup', 'showTechnicalInformation');
            $techni_files = [];
            if ($showTechnicalInformation) {
                $techni_files = $asModel->DB_get_all('assets_technical_file', '*', array('assid' => $assid));
            }
            $assets['techni_files'] = $techni_files;

            //判断有无查看设备档案的权限
            $showAssetsfile = get_menu('Assets', 'Lookup', 'showAssetsfile');
            $archives_files = [];
            if ($showAssetsfile) {
                $archives_files = $asModel->DB_get_all('assets_archives_file', '*', array('assid' => $assid), '', 'arc_id asc');
            }
            $assets['archives_files'] = $archives_files;

            $mainAssets = [];
            $mainAssets_factory = [];
            $subsidiary = [];
            //所属主设备
            if ($assets['is_subsidiary'] == C('YES_STATUS')) {
                //获取所属主设备信息
                $mainAssets = $asModel->getAssetsInfo($assets['main_assid']);
                if (!$showPrice) {
                    $mainAssets['buy_price'] = '***';
                    $mainAssets['depreciable_quota_m'] = '***';
                    $mainAssets['depreciable_quota_count'] = '***';
                    $mainAssets['net_asset_value'] = '***';
                    $mainAssets['net_assets'] = '***';
                }
                //组织表单第三部分厂商信息
                $mainAssets_factory = $asModel->DB_get_one('assets_factory', '', array('assid' => $mainAssets['assid']));
            } else {
                //获取附属设备 信息
                $subsidiary = $asModel->getSubsidiaryList($assid);
            }
            $assets['mainAssets'] = $mainAssets;
            $assets['mainAssets_factory'] = $mainAssets_factory;
            $assets['subsidiary'] = $subsidiary;

            //获取档案资料文件信息
            foreach ($life as $key => $value) {
                switch ($value['type']) {
                    case '1'://转科生命线
                        $life[$key]['html'] = '<div class="timelineContentDiv">
                                            <label>转科单号：</label>
                                            <span>' . $value["transfernum"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>申请人：</label>
                                            <span>' . $value["applicant_user"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>申请时间：</label>
                                            <span>' . $value["applicant_time"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>转出科室：</label>
                                            <span>' . $value["tranout_depart_name"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>转出科室负责人：</label>
                                            <span>' . $value["tranout_departrespon"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>转入科室：</label>
                                            <span>' . $value["tranin_depart_name"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>转入科室负责人：</label>
                                            <span>' . $value["tranin_departrespon"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>转科日期：</label>
                                            <span>' . $value["transfer_date"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>验收人：</label>
                                            <span>' . $value["check_user"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>验收时间：</label>
                                            <span>' . $value["check_time"] . '</span>
                                        </div>';
                        break;
                    case '2'://维修生命线
                        $life[$key]['html'] = '<div class="timelineContentDiv">
                                            <label>维修单号：</label>
                                            <span>' . $value["repnum"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>报修人：</label>
                                            <span>' . $value["applicant"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>报修时间：</label>
                                            <span>' . $value["applicant_time"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>报修原因：</label>
                                            <span>' . $value["breakdown"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>维修状态：</label>
                                            <span>' . $value["statusName"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>接单人：</label>
                                            <span>' . $value["response"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>接单时间：</label>
                                            <span>' . $value["response_date"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>维修结束时间：</label>
                                            <span>' . $value["overdate"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>验收人：</label>
                                            <span>' . $value["checkperson"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>验收时间：</label>
                                            <span>' . $value["checkdate"] . '</span>
                                        </div>';
                        break;
                    case '3'://报废生命线
                        $life[$key]['html'] =
                                        '<div class="timelineContentDiv">
                                            <label>报废单号：</label>
                                            <span>' . $value["scrapnum"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>申请人：</label>
                                            <span>' . $value["apply_user"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>申请时间：</label>
                                            <span>' . $value["applicant_time"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>报废原因：</label>
                                            <span>' . $value["scrap_reason"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>处理经手人：</label>
                                            <span>' . $value["clear_cross_user"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>清理公司：</label>
                                            <span>' . $value["clear_company"] . '</span>
                                        </div>';
                        break;
                    case '4'://巡查保养生命线
                        $life[$key]['html'] = '<div class="timelineContentDiv">
                                            <label>计划名称：</label>
                                            <span>' . $value["clear_company"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>计划编号：</label>
                                            <span>' . $value["patrolnum"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>保养级别：</label>
                                            <span>' . $value["patrol_level_name"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>保养人：</label>
                                            <span>' . $value["executor"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>计划发布时间：</label>
                                            <span>' . $value["release_time"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>计划执行状态：</label>
                                            <span>' . $value["statusName"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>是否异常：</label>
                                            <span>' . $value["is_normal"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>异常点数：</label>
                                            <span>' . $value["abnormal"] . '</span>
                                        </div>';
                        break;
                    case '5'://不良事件生命线
                        $life[$key]['html'] = '<div class="timelineContentDiv">
                                            <label>报告人：</label>
                                            <span>' . $value["sign"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>报告人职称：</label>
                                            <span>' . $value["reporter"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>事件记录时间：</label>
                                            <span>' . $value["addtime"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>报告日期：</label>
                                            <span>' . $value["report_date"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>报告来源：</label>
                                            <span>' . $value["report_from"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>预期治疗治病：</label>
                                            <span>' . $value["expected"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>事件后果：</label>
                                            <span>' . $value["consequence"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>事件主要表现：</label>
                                            <span>' . $value["express"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>初步原因分析：</label>
                                            <span>' . $value["cause"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>初步处理情况：</label>
                                            <span>' . $value["situation"] . '</span>
                                        </div>';
                        break;
                    case '6'://质控计划生命线
                        $life[$key]['html'] = '<div class="timelineContentDiv">
                                            <label>计划名称：</label>
                                            <span>' . $value["plan_name"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>计划编号：</label>
                                            <span>' . $value["plan_num"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>是否周期执行：</label>
                                            <span>' . $value["is_cycle"] . '</span>
                                        </div>';
                        if ($value['is_cycle'] == '是') {
                            $life[$key]['html'] = $life[$key]['html'].'<div class="timelineContentDiv">
                                            <label>周期(月)：</label>
                                            <span>' . $value["cycle"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>期次：</label>
                                            <span>' . $value["period"] . '</span>
                                        </div>';
                            # code...
                        } else {
                            $life[$key]['html'] = $life[$key]['html'].'<div class="timelineContentDiv">
                                            <label>周期(月)：</label>
                                            <span>无</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>期次：</label>
                                            <span>无</span>
                                        </div>';
                        }
                        $life[$key]['html'] = $life[$key]['html'].'
                                        <div class="timelineContentDiv">
                                            <label>检测人：</label>
                                            <span>' . $value["username"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>预计执行日期：</label>
                                            <span>' . $value["do_date"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>';
                        if ($value['is_cycle'] == '是') {
                            $life[$key]['html'] = $life[$key]['html'].'预计';
                        }
                        $life[$key]['html'] = $life[$key]['html'].'结束日期：</label>
                                            <span>' . $value["end_date"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>启用日期：</label>
                                            <span>' . $value["start_date"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>计划执行状态：</label>
                                            <span>' . $value["is_start"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>检测结果：</label>
                                            <span>' . $value["result"] . '</span>
                                        </div>';
                        break;
                    case '7'://计量计划生命线
                        $life[$key]['html'] = '<div class="timelineContentDiv">
                                            <label>计划编号：</label>
                                            <span>' . $value["plan_num"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>计量分类：</label>
                                            <span>' . $value["mcategory"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>周期(月)：</label>
                                            <span>' . $value["cycle"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>计量负责人：</label>
                                            <span>' . $value["respo_user"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>启用状态：</label>
                                            <span>' . $value["plan_status_name"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>本次检定日期：</label>
                                            <span>' . $value["this_date"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>检定机构：</label>
                                            <span>' . $value["company"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>计量费用：</label>
                                            <span>' . $value["money"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>检定人：</label>
                                            <span>' . $value["test_person"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>检查状态：</label>
                                            <span>' . $value["result_status_name"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>检定结果：</label>
                                            <span>' . $value["result"] . '</span>
                                        </div>';
                        break;
                    case '8'://借调生命线
                        $life[$key]['html'] = '<div class="timelineContentDiv">
                                            <label>申请科室：</label>
                                            <span>' . $value["apply_department"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>申请人：</label>
                                            <span>' . $value["apply_username"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>申请时间：</label>
                                            <span>' . $value["apply_time"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>借用原因：</label>
                                            <span>' . $value["borrow_reason"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>借调状态：</label>
                                            <span>' . $value["statuName"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>结束时间：</label>
                                            <span>' . $value["over_date"] . '</span>
                                        </div>';
                        break;
                    case '9'://外调生命线
                        $life[$key]['html'] = '<div class="timelineContentDiv">
                                            <label>申请人：</label>
                                            <span>' . $value["apply_username"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>申请时间：</label>
                                            <span>' . $value["apply_time"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>申请类型：</label>
                                            <span>' . $value["apply_typeName"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>联系人：</label>
                                            <span>' . $value["person"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>联系电话：</label>
                                            <span>' . $value["phone"] . '</span>
                                        </div>';
                        if ($value['price']) {
                            $life[$key]['html'] += '<div class="timelineContentDiv">
                                                <label>金额：</label>
                                                <span>' . $value["price"] . '</span>
                                            </div>';
                        }
                        $life[$key]['html'] += '<div class="timelineContentDiv">
                                            <label>外调目的地：</label>
                                            <span>' . $value["accept"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>验收人：</label>
                                            <span>' . $value["check_person"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>验收人联系电话：</label>
                                            <span>' . $value["check_phone"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>验收日期：</label>
                                            <span>' . $value["check_date"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>是否通过审核：</label>
                                            <span>' . $value["examine_statusName"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>结束时间：</label>
                                            <span>' . $value["over_date"] . '</span>
                                        </div>';
                        break;
                    case '10'://附属设备分配生命线
                        $life[$key]['html'] = '<div class="timelineContentDiv">
                                            <label>申请人：</label>
                                            <span>' . $value["apply_user"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>申请日期：</label>
                                            <span>' . $value["apply_date"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>设备名称：</label>
                                            <span>' . $value["main_assets"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>设备科室：</label>
                                            <span>' . $value["department"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>管理科室：</label>
                                            <span>' . $value["main_managedepart"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>使用位置：</label>
                                            <span>' . $value["main_address"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>负责人：</label>
                                            <span>' . $value["main_assetsrespon"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>验收人：</label>
                                            <span>' . $value["check_user"] . '</span>
                                        </div>
                                        <div class="timelineContentDiv">
                                            <label>验收日期：</label>
                                            <span>' . $value["check_time"] . '</span>
                                        </div>';
                        break;
                    default:
                        # code...
                        break;
                }
            }
            $assets['life'] = $life;
            $result['data'] = $assets;
            $result['addAssets'] = get_menu('Assets','Lookup','addAssets');
            $this->ajaxReturn($result, 'json');
        }
    }

    public function cate()
    {
        $asModel = new AssetsInfoModel();
        $type = I('get.type');
        $cates = $asModel->get_all_category($type);
        $cates = getTree('parentid', 'catid', $cates, 0);
        foreach ($cates as $key => $value) {
            $cates[$key]['text'] = $value['category'] . '(' . $value['assetssum'] . ')';
        }
        usort($cates, function ($prev, $next) {
            return $prev['assetssum'] < $next['assetssum'];
        });
        $result['data'] = $cates;
        $result['status'] = 1;
        $this->ajaxReturn($result, 'json');
    }

    public function departs()
    {
        $asModel = new AssetsInfoModel();
        $type = I('get.type');
        if ($type=='borrow') {
            $departids = "";
            $departs = $asModel->get_all_department($departids, C('ASSETS_STATUS_USE') . ',' . C('ASSETS_STATUS_REPAIR') . ',' . C('ASSETS_STATUS_SCRAP') . ',' . C('ASSETS_STATUS_SCRAP_ON') . ',' . C('ASSETS_STATUS_TRANSFER_ON'));
        }else if ($type=='transfer') {
            $departids = session('departid');
            $departs = $asModel->get_all_department($departids, C('ASSETS_STATUS_USE') . ',' . C('ASSETS_STATUS_TRANSFER_ON'), C('NO_STATUS'));
        }else if ($type=='scrap') {
            $departids = session('departid');
            $departs = $asModel->get_all_department($departids,C('ASSETS_STATUS_USE').','.C('ASSETS_STATUS_SCRAP_ON'),C('NO_STATUS'));
        }else if ($type=='Print') {
            $departids = session('departid');
            $departs = $asModel->get_all_department($departids,C('ASSETS_STATUS_USE') . ',' . C('ASSETS_STATUS_REPAIR') . ',' . C('ASSETS_STATUS_SCRAP_ON') . ',' . C('ASSETS_STATUS_TRANSFER_ON').','.C('ASSETS_STATUS_OUTSIDE').','.C('ASSETS_STATUS_OUTSIDE_ON'),'',0);
        }
        else{
            $departids = session('departid');
            $departs = $asModel->get_all_department($departids, C('ASSETS_STATUS_USE') . ',' . C('ASSETS_STATUS_REPAIR') . ',' . C('ASSETS_STATUS_SCRAP') . ',' . C('ASSETS_STATUS_SCRAP_ON') . ',' . C('ASSETS_STATUS_TRANSFER_ON'));
        }
        $departs = getTree('parentid', 'departid', $departs, 0);
        $result['data'] = $departs;
        $result['status'] = 1;
        $this->ajaxReturn($result, 'json');
    }

    public function getAssetsLista()
    {
        $asModel = new AssetsInfoModel();
        $result = $asModel->get_assets_list();
        $this->ajaxReturn($result, 'json');

    }
}
