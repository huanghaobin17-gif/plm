<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2018/1/2
 * Time: 17:01
 */

namespace Admin\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AssetsInsuranceModel;
use Admin\Model\DepartmentModel;
use Admin\Model\RepairModel;
use Admin\Model\UserModel;

class AssetsStatisController extends CheckLoginController
{
    private $MODULE = 'Assets';

    /**
     * Notes:全部资产概况统计
     */
    public function assetsSurvey()
    {
        if (IS_POST) {
            $departmentModel = new DepartmentModel();
            $data_start = date('Y') . '-01-01';
            $data_end = date('Y-m-d');
            //获取设备概况
            $startTime = I('POST.assetsSurveyStartDate') ? strtotime(I('POST.assetsSurveyStartDate')) : strtotime($data_start);
            $endTime = I('POST.assetsSurveyEndDate') ? strtotime(I('POST.assetsSurveyEndDate')) : time();
            $priceMin = I('POST.assetsSurveyPriceMin') ? I('POST.assetsSurveyPriceMin') : 0;
            $priceMax = I('POST.assetsSurveyPriceMax');
            $hospital_id = session('current_hospitalid');
            $lists = $departmentModel->getReportData($startTime, $endTime, $priceMin, $priceMax, $hospital_id);
            if (!$lists) {
                $this->ajaxReturn($lists, 'json');
            }
            //组织数据
            $res = $departmentModel->handleAssetsSurvey($lists);
            $hosName = $this->get_hospital_name($hospital_id);
            $hospitalName = $hosName['hospital_name'];
            //获取报表标题
            $res['reportTitle'] = $hospitalName . '资产概况表';
            //获取报表搜索条件范围
            $res['reportTips'] = $departmentModel->getReportTips($startTime, $endTime, $priceMin, $priceMax);
            $this->ajaxReturn($res, 'json');
        } else {
            $data_start = date('Y') . '-01-01';
            $data_end = date('Y-m-d');
            $this->assign('data_start', $data_start);
            $this->assign('data_end', $data_end);
            $this->display();
        }
    }

    /**
     * Notes:导出资产概况数据到excel表格
     */
    public function exportAssetsSurvey()
    {
        if ($_POST) {
            $startTime = I('POST.startDate') ? strtotime(I('POST.startDate')) : 0;
            $endTime = I('POST.endDate') ? strtotime(I('POST.endDate')) : time();
            $priceMin = I('POST.priceMin') ? I('POST.priceMin') : 0;
            $priceMax = I('POST.priceMax');
            $hospital_id = session('current_hospitalid');

            //根据搜索条件获取数据
            $departmentModel = new DepartmentModel();
            $data = $departmentModel->getReportData($startTime, $endTime, $priceMin, $priceMax, $hospital_id, true);
            //要显示的数据
            $showName = array('department' => '科室名称', 'totalNum' => '登记台数', 'newAddNum' => '新增台数', 'working' => '在用台数', 'repairing' => '维修中台数', 'scraped' => '报废台数', 'totalPrice' => '总金额（元）', 'newAddPrice' => '新增总金额（元）');
            //接收base64图片编码并转化为图片保存
            $base64Data = I('POST.base64Data');
            //匹配出图片的格式
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Data, $result)) {
                //图片格式
                $type = $result[2];
                if (!in_array($type, array('png', 'jpeg'))) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '图片格式错误！'));
                }
                $filePath = "./Public/uploads/report/";
                if (!file_exists($filePath)) {
                    //检查是否有该文件夹，如果没有就创建，并给予最高权限
                    mkdir($filePath, 0777, true);
                }
                $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
                $file = $filePath . $fileName;
                if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $base64Data)))) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '图表保存出错，请重试！'));
                }
                $image = new \Think\Image();
                $image->open($file);
                $imageInfo['width'] = $image->width(); // 返回图片的宽度
                $imageInfo['height'] = $image->height(); // 返回图片的高度
                $imageInfo['type'] = $image->type(); // 返回图片的类型
                $imageInfo['url'] = $file; // 服务器图片地址
                $showLastTotalRow = true; //是否显示最好一行合计行
                $hosName = $this->get_hospital_name($hospital_id);
                $hospitalName = $hosName['hospital_name'];
                $tableHeader = $hospitalName . '资产概况统计';
                $tips = $departmentModel->getReportTips($startTime, $endTime, $priceMin, $priceMax);
                $otherInfo['titleFontSize'] = 28;
                $otherInfo['titleRowHeight'] = 60;
                $otherInfo['imagePosition'] = 'bottom';
                $otherInfo['imageWidth'] = $image->width();//图片缩放比例
                $otherInfo['imageHeight'] = $image->height();//图片缩放比例
                exportExcelStatistics($sheetTitle = array('资产概况'), $tableHeader, $showName, $data, $imageInfo, $showLastTotalRow, $tableHeader, $tips, $otherInfo);
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '生成图片错误！'));
            }
        }
    }

    /**
     * Notes:在用资产概况统计
     */
    public function onUseAssetsSurvey()
    {
        $departmentModel = new DepartmentModel();
        if (IS_POST) {
            //获取数据
            $startTime = I('POST.onUseAssetsSurveyStartDate') ? strtotime(I('POST.onUseAssetsSurveyStartDate')) : 0;
            $endTime = I('POST.onUseAssetsSurveyEndDate') ? strtotime(I('POST.onUseAssetsSurveyEndDate')) : time();
            $priceMin = I('POST.onUseAssetsSurveyPriceMin') ? I('POST.onUseAssetsSurveyPriceMin') : 0;
            $priceMax = I('POST.onUseAssetsSurveyPriceMax');
            $hospital_id = session('current_hospitalid');
            $lists = $departmentModel->getOnUseReportData($startTime, $endTime, $priceMin, $priceMax, $hospital_id);
            if (!$lists) {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $this->ajaxReturn($result, 'json');
            }
            //组织数据
            $res = $departmentModel->handleOnUseAssetsSurvey($lists);
            $hosName = $this->get_hospital_name($hospital_id);
            $hospitalName = $hosName['hospital_name'];
            //获取报表标题
            $res['reportTitle'] = $hospitalName . '在用资产统计';
            //获取报表搜索条件范围
            $res['reportTips'] = $departmentModel->getReportTips($startTime, $endTime, $priceMin, $priceMax);
            $date=$departmentModel->DB_get_one('assets_info','min(adddate) as startTime',array('is_delete'=>C('NO_STATUS'),'adddate'=>array('gt','0')));
            $res['startTime']=getHandleTime($date['startTime']);
            $res['endTime']=getHandleTime($endTime);
            $res['code']='200';
            $this->ajaxReturn($res, 'json');
        } else {
            $date=$departmentModel->DB_get_one('assets_info','min(adddate) as startTime',array('is_delete'=>C('NO_STATUS'),'adddate'=>array('gt','0')));
            $this->assign('startTime',getHandleTime($date['startTime']));
            $this->assign('endTime',getHandleTime(time()));
            $this->display();
        }
    }

    /**
     * Notes:导出在用资产概况数据到excel表格
     */
    public function exportUsingAssetsSurvey()
    {
        if ($_POST) {
            $startTime = I('POST.startDate') ? strtotime(I('POST.startDate')) : 0;
            $endTime = I('POST.endDate') ? strtotime(I('POST.endDate')) : time();
            $priceMin = I('POST.priceMin') ? I('POST.priceMin') : 0;
            $priceMax = I('POST.priceMax');
            $hospital_id = session('current_hospitalid');
            //根据搜索条件获取数据
            $departmentModel = new DepartmentModel();
            $data = $departmentModel->getOnUseReportData($startTime, $endTime, $priceMin, $priceMax, $hospital_id, true);
            //要显示的数据
            $showName = array('department' => '科室名称', 'totalNum' => '设备总数量', 'onUse' => '正常在用数量','other' => '其他数量','totalPrice' => '设备总金额（元）');
            //接收base64图片编码并转化为图片保存
            $base64Data = I('POST.base64Data');
            //匹配出图片的格式
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Data, $result)) {
                //图片格式
                $type = $result[2];
                if (!in_array($type, array('png', 'jpeg'))) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '图片格式错误！'));
                }
                $filePath = "./Public/uploads/report/";
                if (!file_exists($filePath)) {
                    //检查是否有该文件夹，如果没有就创建，并给予最高权限
                    mkdir($filePath, 0777, true);
                }
                $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
                $file = $filePath . $fileName;
                if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $base64Data)))) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '图表保存出错，请重试！'));
                }
                $image = new \Think\Image();
                $image->open($file);
                $imageInfo['url'] = $file; // 服务器图片地址
                $showLastTotalRow = true; //是否显示最好一行合计行
                $hosName = $this->get_hospital_name($hospital_id);
                $tableHeader = $hosName['hospital_name'] . '在用资产概况统计';
                $tips = $departmentModel->getReportTips($startTime, $endTime, $priceMin, $priceMax);
                $otherInfo['titleFontSize'] = 14;
                $otherInfo['titleRowHeight'] = 40;
                $otherInfo['imagePosition'] = 'right';
                $otherInfo['imageWidth'] = $image->width() * 0.6;//图片缩放比例
                $otherInfo['imageHeight'] = $image->height() * 0.6;//图片缩放比例
                exportExcelStatistics($sheetTitle = array('在用资产概况'), $tableHeader, $showName, $data, $imageInfo, $showLastTotalRow, $tableHeader, $tips, $otherInfo);
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '生成图片错误！'));
            }
        }
    }

    /**
     * Notes:所有科室资产电子账单汇总表统计
     */
    public function assetsSummary()
    {
        if (IS_POST) {
            $type = I('POST.type');
            $departmentModel = new DepartmentModel();
            switch ($type) {
                case 'departmentSummary':
                    //科室账单详情列表
                    $res = $departmentModel->departmentSummary();
                    $this->ajaxReturn($res, 'json');
                    break;
                case 'RepairRecord':
                    //获取维修记录
                    $repairModel = new RepairModel();
                    $res = $repairModel->RepairRecord();
                    //var_dump($res);exit;
                    $this->ajaxReturn($res, 'json');
                    break;
                default:
                    //获取设备汇总列表数据
                    $res = $departmentModel->assetsSummary();
                    array_multisort(array_column($res['lists'], 'totalNum'), SORT_DESC, $res['lists']);
                    $result = $this->format_assetsSummary($res);
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $type = I('GET.type');
            switch ($type) {
                case 'departmentSummary':
                    $data_start = date('Y') . '-01-01';
                    $data_end = date('Y-m-d');
                    $this->assign('data_start', $data_start);
                    $this->assign('data_end', $data_end);
                    $this->assign('departid', I('GET.id'));
                    $this->assign('assetsSummary', get_url());
                    $this->display('departmentSummary');
                    break;
                case 'showRepairRecord':
                    //显示设备维修记录情况
                    $assid = I('GET.assid');
                    //查询维修记录情况
                    $repairModel = new RepairModel();
                    $lists = $repairModel->getRepairRecordByAssid($assid);
                    $year = array();
                    foreach ($lists as $k => $v) {
                        if (!in_array($v['year'], $year)) {
                            $year[] = $v['year'];
                        }
                    }
                    $this->assign('assid', $assid);
                    //输出画布高度
                    $this->assign('height', (count($year) * 200 + 100) . 'px');
                    $this->display('showRepairRecord');
                    break;
                case 'showRepairParts':
                    //根据assid获取该设备所有维修历史产生的配件信息
                    $assid = I('GET.assid');
                    if (!$assid || $assid < 0) {
                        $this->error('参数非法');
                    }
                    $repairModel = new RepairModel();
                    //查询设备信息
                    $assetsInfo = $repairModel->DB_get_one('assets_info', 'assets,assnum', array('assid' => $assid));
                    if (!$assetsInfo) {
                        $this->error('查询不到该设备信息！');
                    }
                    //获取所有维修单的repid
                    $repids = $repairModel->DB_get_one('repair', 'group_concat(repid) as repids', array('assid' => $assid));
                    if (!$repids) {
                        $this->error('查询不到该设备的维修记录！');
                    }
                    $repidArr = explode(',', $repids['repids']);
                    $res = $repairModel->getAllPartsLists($repidArr);
                    $this->assign('reportTitle', '设备历史维修配件信息记录');
                    $this->assign('reportConditions', '设备名称：' . $assetsInfo['assets'] . ' / ' . '设备编码：' . $assetsInfo['assnum']);
                    $this->assign('data', $res);
                    $this->display('showRepairParts');
                    break;
                case 'showPatrolPlan':
                    //根据assnum获取设备巡查计划信息
                    $assnum = I('GET.assnum');
                    $startTime = I('GET.startTime');
                    $endTime = I('GET.endTime');
                    $startTime = $startTime ? strtotime($startTime) : strtotime('1970-01-02');
                    $endTime = $endTime ? strtotime($endTime) : time();
                    $departModel = new DepartmentModel();
                    //查询该设备信息
                    $assetsInfo = $departModel->DB_get_one('assets_info', 'assets,assnum', array('assnum' => $assnum));
                    //查询巡查保养记录
                    $res = $departModel->getPatrolPlan($startTime, $endTime, $assnum);
                    $this->assign('reportTitle', '设备巡查保养记录');
                    $this->assign('reportConditions', '设备名称：' . $assetsInfo['assets'] . ' / ' . '设备编码：' . $assetsInfo['assnum'] . ' / ' . '统计区间：' . getHandleTime($startTime) . ' 至 ' . getHandleTime($endTime));
                    $this->assign('data', $res);
                    $this->display('showPatrolPlan');
                    break;
                default:
                    $data_start = date('Y') . '-01-01';
                    $data_end = date('Y-m-d');
                    $departments = $this->getSelectDepartments();
                    $this->assign('departments', $departments);
                    $this->assign('data_start', $data_start);
                    $this->assign('data_end', $data_end);
                    $this->assign('assetsSummary', get_url());
                    $this->display();
                    break;
            }
        }
    }

    private function format_assetsSummary($res)
    {
        $result['reportTitle'] = $res['reportTitle'];
        $result['reportTips'] = $res['reportTips'];
        $result['limit'] = 200;
        $result['offset'] = 0;
        $result['total'] = count($res['lists']);
        $result['rows'] = $res['lists'];
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $legend['num'] = ['设备总数','维保期内设备数','报修次数','修复次数','维修配件数','保养计划次数','保养执行次数'];
        $legend['price'] = ['设备总金额(元)','维修总费用(元)'];
        $yAxis['data'] = $series = $totalPrice = $actualPrice = $totalNum = $partNum = $repairNum = $overRepairNum = $guaranteeNum = $patrolPlanNum = $implementNum = [];
        foreach ($res['lists'] as $v){
            $yAxis['data'][] = $v['department'];
            $totalNum[] = $v['totalNum'];
            $partNum[] = $v['partNum'];
            $repairNum[] = $v['repairNum'];
            $overRepairNum[] = $v['overRepairNum'];
            $guaranteeNum[] = $v['guaranteeNum'];
            $patrolPlanNum[] = $v['patrolPlanNum'];
            $implementNum[] = $v['implementNum'];
            $totalPrice[] = $v['totalPrice'];
            $actualPrice[] = $v['actualPrice'];
        }
        foreach ($legend['num'] as $k=>$v){
            $series['num'][$k]['name'] = $v;
            $series['num'][$k]['type'] = 'bar';
            $series['num'][$k]['stack'] = '总量';
            $series['num'][$k]['label']['normal']['show'] = true;
            $series['num'][$k]['label']['normal']['position'] = 'insideRight';
            switch ($v){
                case '设备总数':
                    $series['num'][$k]['data'] = $totalNum;
                    break;
                case '维保期内设备数':
                    $series['num'][$k]['data'] = $guaranteeNum;
                    break;
                case '报修次数':
                    $series['num'][$k]['data'] = $repairNum;
                    break;
                case '修复次数':
                    $series['num'][$k]['data'] = $overRepairNum;
                    break;
                case '维修配件数':
                    $series['num'][$k]['data'] = $partNum;
                    break;
                case '保养计划次数':
                    $series['num'][$k]['data'] = $patrolPlanNum;
                    break;
                case '保养执行次数':
                    $series['num'][$k]['data'] = $implementNum;
                    break;
            }

        }
        foreach ($legend['price'] as $k=>$v){
            $series['price'][$k]['name'] = $v;
            $series['price'][$k]['type'] = 'bar';
            $series['price'][$k]['stack'] = '总量';
            $series['price'][$k]['label']['normal']['show'] = true;
            $series['price'][$k]['label']['normal']['position'] = 'insideRight';
            switch ($v){
                case '设备总金额(元)':
                    $series['price'][$k]['data'] = $totalPrice;
                    break;
                case '维修总费用(元)':
                    $series['price'][$k]['data'] = $actualPrice;
                    break;
            }

        }
        $result['series'] = $series;
        $result['legend'] = $legend;
        $result['yAxis']  = $yAxis;
        return $result;
    }

    /**
     * Notes:导出资产汇总统计概况数据到excel表格
     */
    public function exportAssetsSummary()
    {
        if ($_POST) {
            $startTime = I('POST.startDate') ? strtotime(I('POST.startDate')) : 0;
            $endTime = I('POST.endDate') ? strtotime(I('POST.endDate')) : time();
            $priceMin = I('POST.priceMin') ? I('POST.priceMin') : 0;
            $priceMax = I('POST.priceMax');
            $departids = I('POST.departids');
//            $hospital_id = I('POST.hospital_id');
            $hospital_id = session('current_hospitalid');
            //根据搜索条件获取数据
            $departmentModel = new DepartmentModel();
            $data = $departmentModel->getSummaryData($startTime, $endTime, $priceMin, $priceMax, $departids, $hospital_id, true);
            //要显示的数据
            $showName = array('department' => '科室名称', 'totalNum' => '设备台数', 'guaranteeNum' => '维保期内台数', 'repairNum' => '报修次数', 'overRepairNum' => '修复次数', 'partNum' => '维修配件数', 'patrolPlanNum' => '保养计划次数', 'implementNum' => '保养执行次数', 'transferNum' => '转科次数', 'actualPrice' => '维修总费用（元）', 'totalPrice' => '总金额（元）');
            //接收base64图片编码并转化为图片保存
            $base64Data = I('POST.base64Data');
            //匹配出图片的格式
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Data, $result)) {
                //图片格式
                $type = $result[2];
                if (!in_array($type, array('png', 'jpeg'))) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '图片格式错误！'));
                }
                $filePath = "./Public/uploads/report/";
                if (!file_exists($filePath)) {
                    //检查是否有该文件夹，如果没有就创建，并给予最高权限
                    mkdir($filePath, 0777, true);
                }
                $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
                $file = $filePath . $fileName;
                if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $base64Data)))) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '图表保存出错，请重试！'));
                }
                $image = new \Think\Image();
                $image->open($file);
                $imageInfo['width'] = $image->width(); // 返回图片的宽度
                $imageInfo['height'] = $image->height(); // 返回图片的高度
                $imageInfo['type'] = $image->type(); // 返回图片的类型
                $imageInfo['url'] = $file; // 服务器图片地址
                $showLastTotalRow = true; //是否显示最好一行合计行
                $hosName = $this->get_hospital_name($hospital_id);
                $tableHeader = $hosName['hospital_name'] . '资产汇总概况统计';
                $tips = $departmentModel->getReportTips($startTime, $endTime, $priceMin, $priceMax);
                $otherInfo['titleFontSize'] = 28;
                $otherInfo['titleRowHeight'] = 60;
                $otherInfo['imagePosition'] = 'bottom';
                $otherInfo['imageWidth'] = $image->width();//图片缩放比例
                $otherInfo['imageHeight'] = $image->height();//图片缩放比例
                exportExcelStatistics($sheetTitle = array('资产汇总概况'), $tableHeader, $showName, $data, $imageInfo, $showLastTotalRow, $tableHeader, $tips, $otherInfo);
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '生成图片错误！'));
            }
        }
    }


    /**
     * Notes:导出单个部门电子资产账单详情
     */
    public function exportDepartmentSummary()
    {
        $departmentModel = new DepartmentModel();
        $startTime = I('POST.startDate') ? strtotime(I('POST.startDate')) : 0;
        $endTime = I('POST.endDate') ? strtotime(I('POST.endDate')) : time();
        $priceMin = I('POST.priceMin') ? I('POST.priceMin') : 0;
        $priceMax = I('POST.priceMax');
        $departids = I('POST.departid');
        //获取符合条件数据
        $data = $departmentModel->getDepartmentAssetsSummaryData($startTime, $endTime, $priceMin, $priceMax, $departids, true);
        //要显示的数据
        $showName = array('assnum' => '设备编码', 'assets' => '设备名称', 'isGuarantee' => '保修期内', 'totalPrice' => '设备金额（元）', 'repairNum' => '报修次数', 'overRepairNum' => '修复次数', 'partNum' => '维修配件数', 'actualPrice' => '维修费用（元）', 'totalHours' => '维修工时', 'patrolPlanNum' => '保养计划次数', 'implementNum' => '保养执行次数');
        //接收base64图片编码并转化为图片保存
        $base64Data = I('POST.base64Data');
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Data, $result)) {
            //图片格式
            $type = $result[2];
            if (!in_array($type, array('png', 'jpeg'))) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '图片格式错误！'));
            }
            $filePath = "./Public/uploads/report/";
            if (!file_exists($filePath)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($filePath, 0777, true);
            }
            $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
            $file = $filePath . $fileName;
            if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $base64Data)))) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '图表保存出错，请重试！'));
            }
            $image = new \Think\Image();
            $image->open($file);
            $imageInfo['width'] = $image->width(); // 返回图片的宽度
            $imageInfo['height'] = $image->height(); // 返回图片的高度
            $imageInfo['type'] = $image->type(); // 返回图片的类型
            $imageInfo['url'] = $file; // 服务器图片地址
            $showLastTotalRow = true; //是否显示最好一行合计行
            $hosName = $this->get_hospital_name($data[0]['hospital_id']);
            $hospitalName = $hosName['hospital_name'];
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $tableHeader = $hospitalName . '（' . $departname[$departids]['department'] . '）资产汇总概况统计';
            $tips = $departmentModel->getReportTips($startTime, $endTime, $priceMin, $priceMax);
            $otherInfo['titleFontSize'] = 28;
            $otherInfo['titleRowHeight'] = 60;
            $otherInfo['imagePosition'] = 'bottom';
            $otherInfo['imageWidth'] = $image->width();//图片缩放比例
            $otherInfo['imageHeight'] = $image->height();//图片缩放比例
            exportExcelStatistics($sheetTitle = array('资产汇总概况'), $tableHeader, $showName, $data, $imageInfo, $showLastTotalRow, $tableHeader, $tips, $otherInfo);
        } else {
            $this->ajaxReturn(array('status' => -1, 'msg' => '生成图片错误！'));
        }
    }


    /**
     * Notes: 导出设备维修记录情况
     */
    public function exportRepairRecord()
    {
        $assid = I('POST.assid');
        if (!$assid) {
            $this->error('参数非法！');
        }
        $repairModel = new RepairModel();
        //查询设备基本信息
        $assetsInfo = $repairModel->DB_get_one('assets_info', 'assid,assets,assnum', array('assid' => $assid));
        if (!$assetsInfo) {
            $this->error('查询不到设备基本信息！');
        }
        //查询维修记录情况
        $data = $repairModel->getRepairRecordByAssid($assid, true);
        //要显示的数据
        $showName = array('repnum' => '维修单编号', 'applicant' => '报修人', 'applicant_time' => '报修日期', 'repairEngineer' => '维修工程师', 'partNum' => '配件数量', 'actualPrice' => '维修费用', 'totalHours' => '维修工时', 'isComplete' => '是否修复', 'overdate' => '修复日期');
        //接收base64图片编码并转化为图片保存
        $base64Data = I('POST.base64Data');
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Data, $result)) {
            //图片格式
            $type = $result[2];
            if (!in_array($type, array('png', 'jpeg'))) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '图片格式错误！'));
            }
            $filePath = "./Public/uploads/report/";
            if (!file_exists($filePath)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($filePath, 0777, true);
            }
            $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
            $file = $filePath . $fileName;
            if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $base64Data)))) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '图表保存出错，请重试！'));
            }
            $image = new \Think\Image();
            $image->open($file);
            $imageInfo['width'] = $image->width(); // 返回图片的宽度
            $imageInfo['height'] = $image->height(); // 返回图片的高度
            $imageInfo['type'] = $image->type(); // 返回图片的类型
            $imageInfo['url'] = $file; // 服务器图片地址
            $showLastTotalRow = true; //是否显示最好一行合计行
            $tableHeader = '设备维修记录';
            $tips = '设备名称：' . $assetsInfo['assets'] . '  /  ' . '设备编码：' . $assetsInfo['assnum'];
            $otherInfo['titleFontSize'] = 28;
            $otherInfo['titleRowHeight'] = 60;
            $otherInfo['imagePosition'] = 'bottom';
            $otherInfo['imageWidth'] = $image->width() * 0.8;//图片缩放比例
            $otherInfo['imageHeight'] = $image->height() * 0.8;//图片缩放比例
            exportExcelStatistics($sheetTitle = array('设备维修记录'), $tableHeader, $showName, $data, $imageInfo, $showLastTotalRow, $tableHeader, $tips, $otherInfo);
        } else {
            $this->ajaxReturn(array('status' => -1, 'msg' => '生成图片错误！'));
        }
    }


    //维保信息统计
    public function MaintenanceSummary()
    {
        if (IS_POST) {
            $AssetsInsuranceModel = new AssetsInsuranceModel();
            $result = $AssetsInsuranceModel->MaintenanceSummary();
            $this->ajaxReturn($result, 'json');
        } else {
            $AssetsInsuranceModel = new AssetsInsuranceModel();
            $AssetsInsuranceModel->updateInsurance();
            $this->display();
        }

    }

}