<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2018/1/2
 * Time: 17:01
 */

namespace Admin\Controller\Repair;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\CategoryModel;
use Admin\Model\DepartmentModel;
use Admin\Model\RepairModel;

class RepairStatisController extends CheckLoginController
{
    private $MODULE = 'Repair';

    //设备故障统计
    public function faultSummary()
    {
        $departid = session('departid');
        if (IS_POST) {
       
            $tab = I('POST.tab');
            if ($tab) {
                //设备故障明细统计表格
                $limit = I('POST.limit');
                $page = I('POST.page');
                $order = I('POST.order');
                $sort = I('POST.sort');
                $assetsDep = I('POST.departid');
                $year = date('Y');
                $default_time = strtotime($year.'-01-01');
                $startTime = I('POST.tab_startDate') ? strtotime(I('POST.tab_startDate')) : $default_time;
                $endTime = I('POST.tab_endDate') ? strtotime(I('POST.tab_endDate'))+23*59*59 : time();
                $sortAsc = I('POST.sortAsc');
                $hospital_id = I('POST.hospital_id');
                if ($sortAsc == 1) {
                    $sort_order = SORT_ASC;
                } else {
                    $sort_order = SORT_DESC;
                }
                $where = [];
                if (!$departid) {
                    $result['msg'] = '暂无相关数据';
                    $result['code'] = 400;
                    $this->ajaxReturn($result, 'json');
                }
                $where['I.departid'] = ['in', $departid];
                if (!isset($limit)) {
                    $limit = 10;
                }
                if (!isset($page)) {
                    $page = 0;
                } else {
                    $page = ($page - 1) * $limit;
                }
                if (!$order) {
                    $order = 'desc';
                }
                if ($assetsDep) {
                    $where['I.departid'] = $assetsDep;
                }
                if ($hospital_id) {
                    $where['I.hospital_id'] = $hospital_id;
                } else {
                    $where['I.hospital_id'] = session('current_hospitalid');
                }
                $where['A.applicant_time'][] = ['EGT', $startTime];
                $where['A.applicant_time'][] = ['ELT', $endTime];
                if (!$sort) {
                    $sort = 'A.repid ';
                }
                $Repair = new RepairModel();
                $JOIN[0] = 'LEFT JOIN sb_assets_info AS I ON I.assid=A.assid';
                $JOIN[1] = 'LEFT JOIN sb_department AS D ON D.departid=I.departid';
                $fields = 'A.repid,A.repnum,applicant_time,A.applicant,A.engineer_time,A.breakdown,A.engineer,A.status,
                D.department,I.assnum,I.assets,I.opendate';
                $total = $Repair->DB_get_count_join('repair', 'A', $JOIN, $where);
                $list = $Repair->DB_get_all_join('repair', 'A', $fields, $JOIN, $where, '', $sort . ' ' . $order,'');
                if (!$list) {
                    $result['msg'] = '暂无相关数据';
                    $result['code'] = 400;
                    $this->ajaxReturn($result, 'json');
                }
                foreach ($list as &$one) {
                    $one['applicant_time'] = getHandleTime($one['applicant_time']);
                    $one['opendate'] = HandleEmptyNull($one['opendate']);
                    $one['engineer_time'] = getHandleTime($one['engineer_time']);
                    switch ($one['status']) {
                        case C('REPAIR_HAVE_REPAIRED'):
                            $one['status'] = C('REPAIR_HAVE_REPAIRED_NAME');
                            break;
                        case C('REPAIR_RECEIPT'):
                            $one['status'] = C('REPAIR_RECEIPT_NAME');
                            break;
                        case C('REPAIR_HAVE_OVERHAULED'):
                            $one['status'] = C('REPAIR_HAVE_OVERHAULED_NAME');
                            break;
                        case C('REPAIR_QUOTATION'):
                            $one['status'] = C('REPAIR_QUOTATION_NAME');
                            break;
                        case C('REPAIR_AUDIT'):
                            $one['status'] = C('REPAIR_HAVE_OVERHAULED_NAME');
                            break;
                        case C('REPAIR_AUDIT_NAME'):
                            $one['status'] = C('REPAIR_HAVE_OVERHAULED_NAME');
                            break;
                        case C('REPAIR_MAINTENANCE'):
                            $one['status'] = C('REPAIR_MAINTENANCE_NAME');
                            break;
                        case C('REPAIR_MAINTENANCE_COMPLETION'):
                            $one['status'] = C('REPAIR_MAINTENANCE_COMPLETION_NAME');
                            break;
                        case C('REPAIR_ALREADY_ACCEPTED'):
                            $one['status'] = C('REPAIR_ALREADY_ACCEPTED_NAME');
                            break;
                    }
                }
                $newList = [];
                //分组操作
                $list = $this->my_array_multisort($list, $sort_order);
                //分页操作
                for ($i = $page; $i < $page + $limit; $i++) {
                    if ($list[$i] != []) {
                        $newList[] = $list[$i];
                    } else {
                        break;
                    }
                }
                $result["total"] = $total;
                $result["offset"] = 10;
                $result["limit"] = 10;
                $result["rows"] = $newList;
                $result['repeat'] = $this->returnRepeat($newList);
                $result["code"] = 200;
                $this->ajaxReturn($result, 'json');
            } else {
                //报表
                $RepairModel = new RepairModel();
                $lists = $RepairModel->getFaultSummary();
                if (!$lists) {
                    $this->ajaxReturn($lists, 'json');
                }
                //获取报表标题
                $data = $RepairModel->getReportTips();
                $lists['reportTips'] = $data['tips'];
                $lists['tableTh'] = $data['tableTh'];
                $this->ajaxReturn($lists, 'json');
            }
        } else {
            $year = date('Y');
            $start_date = $year.'-01-01';
            $departids = explode(',', $departid);
            $departments = $this->getDepartname($departids);
            $this->assign('start_date', $start_date);
            $this->assign('end_date', date('Y-m-d'));
            $this->assign('departments', $departments);
            $this->display();
        }
    }


    //导出设备维修记录
    public function exportFaultDetailed()
    {
        $order = I('POST.order') ? I('POST.order') : 'DESC';
        $sort = I('POST.sort') ? I('POST.sort') : 'A.repid ';
        $assetsDep = I('POST.departid');
        $startTime = I('POST.tab_startDate') ? strtotime(I('POST.tab_startDate')) : 0;
        $endTime = I('POST.tab_endDate') ? strtotime(I('POST.tab_endDate')) : time();
        $sort_order = I('POST.sortAsc') == 1 ? SORT_ASC : SORT_DESC;
        $hospital_id = session('current_hospitalid');

        $Repair = new RepairModel();
        $hospital = $Repair->DB_get_all('hospital', 'hospital_name,hospital_id', 'is_delete=' . C('NO_STATUS'));

        $hospital_arr=[];
        if ($hospital) {
            foreach ($hospital as $hospital_value) {
                $hospital_arr[$hospital_value['hospital_id']]=$hospital_value['hospital_name'];
            }
        }
        $hospital_name='';
        if ($hospital_id) {
            $where['I.hospital_id'] = $hospital_id;
            $hospital_name=$hospital_arr[$hospital_id];
        } else {

            $where['I.hospital_id'] = ['IN', session('manager_hospitalid')];
            $hospital_id=explode(',',session('manager_hospitalid'));
            foreach ($hospital_id as $hospital_id_value){
                $hospital_name.='、'.$hospital_arr[$hospital_id_value];
            }
            $hospital_name=trim($hospital_name,'、');
        }
        if (!session('isSuper')) {
            $where['I.departid'][] = ['IN', session('departid')];
        }
        $where['A.applicant_time'][] = ['EGT', $startTime];
        $where['A.applicant_time'][] = ['ELT', $endTime];
        if ($startTime == 0) {
            $tips = '统计日期：' . '1970-01-01' . ' 至 ' . getHandleTime($endTime);
        } else {
            $tips = '统计日期：' . getHandleTime($startTime) . ' 至 ' . getHandleTime($endTime);
        }
        if ($assetsDep) {
            $where['I.departid'][] = ['EQ', $assetsDep];
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $tips .= ' / 科室：' . $departname[$assetsDep]['department'];
        }
        $Repair = new RepairModel();
        $JOIN[0] = 'LEFT JOIN sb_assets_info AS I ON I.assid=A.assid';
        $JOIN[1] = 'LEFT JOIN sb_department AS D ON D.departid=I.departid';
        $fields = 'A.repnum,applicant_time,A.applicant,A.engineer_time,A.breakdown,A.engineer,A.status,D.department,I.assnum,I.assets,I.opendate,I.hospital_id';
        $lists = $Repair->DB_get_all_join('repair', 'A', $fields, $JOIN, $where, '', $sort . ' ' . $order,'');
        foreach ($lists as &$one) {
            $one['applicant_time'] = getHandleTime($one['applicant_time']);
            $one['opendate'] = HandleEmptyNull($one['opendate']);
            $one['engineer_time'] = getHandleTime($one['engineer_time']);
            $one['hospital_name']=$hospital_arr[$one['hospital_id']];
            switch ($one['status']) {
                case C('REPAIR_HAVE_REPAIRED'):
                    $one['status'] = C('REPAIR_HAVE_REPAIRED_NAME');
                    break;
                case C('REPAIR_RECEIPT'):
                    $one['status'] = C('REPAIR_RECEIPT_NAME');
                    break;
                case C('REPAIR_HAVE_OVERHAULED'):
                    $one['status'] = C('REPAIR_HAVE_OVERHAULED_NAME');
                    break;
                case C('REPAIR_QUOTATION'):
                    $one['status'] = C('REPAIR_QUOTATION_NAME');
                    break;
                case C('REPAIR_AUDIT'):
                    $one['status'] = C('REPAIR_HAVE_OVERHAULED_NAME');
                    break;
                case C('REPAIR_AUDIT_NAME'):
                    $one['status'] = C('REPAIR_HAVE_OVERHAULED_NAME');
                    break;
                case C('REPAIR_MAINTENANCE'):
                    $one['status'] = C('REPAIR_MAINTENANCE_NAME');
                    break;
                case C('REPAIR_MAINTENANCE_COMPLETION'):
                    $one['status'] = C('REPAIR_MAINTENANCE_COMPLETION_NAME');
                    break;
                case C('REPAIR_ALREADY_ACCEPTED'):
                    $one['status'] = C('REPAIR_ALREADY_ACCEPTED_NAME');
                    break;
            }
        }


        //分组操作
        $lists = $this->my_array_multisort($lists, $sort_order);
        $repeat = $this->returnRepeat($lists);
        foreach ($lists as &$listsV) {
            foreach ($repeat as &$repeatV) {
                if ($listsV['department'] == $repeatV['department']) {
                    $listsV['sum'] = $repeatV['sum'];
                    $repeatV['sum'] = 0;
                    break;
                }
            }
        }

        //搜索条件
        $reportTips = $tips;
        //列表标题
        $showName = array(
            'department' => '科室',
            'hospital_name' => '所属医院',
            'assnum' => '设备编号',
            'assets' => '设备名称',
            'repnum' => '维修单号',
            'opendate' => '启用日期',
            'applicant_time' => '报修日期',
            'applicant' => '报修人',
            'engineer_time' => '维修日期',
            'breakdown' => '故障原因',
            'engineer' => '维修工程师',
            'status' => '维修情况'
        );

        $tableHeader = $hospital_name.'设备故障明细统计';
        $otherInfo['titleFontSize'] = 20;
        $otherInfo['titleRowHeight'] = 50;
        exportExcelData($sheetTitle = array('设备故障明细统计'), $tableHeader, $showName, $lists, $tableHeader, $reportTips, $otherInfo);
    }

    function my_array_multisort($data, $sort_order = SORT_DESC, $sort_type = SORT_NUMERIC)
    {
        $departmentData = $this->returnRepeat($data);
        foreach ($departmentData as $val) {
            $key_arrays[] = $val['sum'];
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $departmentData);
        foreach ($departmentData as &$value) {
            foreach ($data as &$value2) {
                if ($value['department'] == $value2['department']) {
                    $list[] = $value2;
                }
            }
        }
        return $list;
    }

    //统计重复科室数量
    public function returnRepeat($list)
    {
        $newArr = [];
        foreach ($list as $key => $value) {
            if ($newArr) {
                foreach ($newArr as $key2 => $value2) {
                    if ($value['department'] == $value2['department']) {
                        $newArr[$key2]['sum']++;
                    } else {
                        //不存在 但是避免直接在末尾重复插入，做多一次循环，验证是否已存在
                        $Repeat = false;
                        foreach ($newArr as $key3 => $value3) {
                            if ($value['department'] == $value3['department']) {
                                $Repeat = true;
                                break;
                            }
                        }
                        if (!$Repeat) {
                            $newArr[$key]['department'] = $value['department'];
                            $newArr[$key]['sum'] = 1;
                        }
                    }
                }
            } else {
                $newArr[$key]['department'] = $value['department'];
                $newArr[$key]['sum'] = 1;
            }
        }
        return $newArr;
    }

    //导出设备故障excel表格
    public function exportFaultSummary()
    {
        if ($_POST) {
            //根据搜索条件获取数据
            $RepairModel = new RepairModel();
            $lists = $RepairModel->getFaultSummary();
            $tips = $RepairModel->getReportTips();
            $reportTips = $tips['tips'];
            $tableTh = $tips['tableTh'];
            $i = 0;
            $sumTotalNum = 0;
            $sumRatio = 0;
            $showName = array('title' => $tableTh, 'totalNum' => '维修次数', 'Ratio' => '百分比（%）');
            foreach ($lists['lists'] as &$one) {
                $data[$i]['title'] = $one['title'];
                $data[$i]['totalNum'] = $one['totalNum'];
                $data[$i]['Ratio'] = $one['Ratio'];
                $sumTotalNum += $one['totalNum'];
                $sumRatio += $one['Ratio'];
                $i++;
            }
            $data[$i]['title'] = '合计';
            $data[$i]['totalNum'] = $sumTotalNum;
            $data[$i]['Ratio'] = $sumRatio . '%';
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
                $showLastTotalRow = true; //是否显示最后一行合计行
                $tableHeader = session('current_hospitalname') . '设备故障统计';
                $otherInfo['titleFontSize'] = 28;
                $otherInfo['titleRowHeight'] = 60;
                $otherInfo['imagePosition'] = 'bottom';
                $otherInfo['imageWidth'] = $image->width();//图片缩放比例
                $otherInfo['imageHeight'] = $image->height();//图片缩放比例
                exportExcelStatistics($sheetTitle = array('设备故障统计'), $tableHeader, $showName, $data, $imageInfo, $showLastTotalRow, $tableHeader, $reportTips, $otherInfo);
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '生成图片错误！'));
            }
        }
    }
}