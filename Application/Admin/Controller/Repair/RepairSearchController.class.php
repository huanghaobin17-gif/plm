<?php

namespace Admin\Controller\Repair;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\NotCheckLogin\PublicController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\RepairModel;

class RepairSearchController extends CheckLoginController
{
    private $MODULE = 'Repair';
    private $Controller = 'Repair';
    private $tablePrefix = 'sb_';

    /**
     * 判断是否开启了统一报价.
     */
    public function isOpenOffer()
    {
        return C('IS_OPEN_OFFER');
    }

    /**
     * 资产综合查询.
     */
    public function getRepairSearchList()
    {
        if (IS_POST) {
            $type = I('POST.type');
            if ($type == 'export') {
                $this->exportHistory();
            } else {
                $asModel = new AssetsInfoModel();
                $result = $asModel->getAssetsRepairRecord();
                $this->ajaxReturn($result, 'json');
            }
        } else {
            $asModel = new AssetsInfoModel();
            //报修人员
            $users = $asModel->getUser();
            //读取配置文件维修状态设置
            $repairStatus = [
                '9' => '派工',
                '1' => '接单',
                '2' => '检修',
                '4' => '维修审核',
                '5' => '维修中',
                '6' => '科室验收',
                '7' => '维修结束',
            ];
            //所属科室
            $notCheck = new PublicController();
            $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
            $this->assign('repairStatus', $repairStatus);
            $this->assign('users', $users);
            $baseSetting = [];
            include APP_PATH.'Common/cache/basesetting.cache.php';
            $this->assign('repair_category', $baseSetting['repair']['repair_category']['value']);
            $this->display();
        }
    }

    public function exportHistory()
    {
        $repModel = new RepairModel();
        $orderField = I('post.orderField');
        $orderType = I('post.orderType');
        if ($orderField && $orderType) {
            $order = $orderField.' '.$orderType;
        } else {
            $order = 'applicant_time desc';
        }
        $repid = I('POST.repid');
        $repid = trim($repid, ',');
        $repid = explode(',', $repid);
        $fields = I('POST.fields');
        $fields = trim($fields, ',');
        $fields = str_replace('0,', '', $fields);
        $fields = str_replace(',null', '', $fields);
        $fields = explode(',', $fields);
        if (!$repid || !$fields) {
            exit('参数错误！');
        }
        //获取要导出的数据
        //读取assets、factory数据库字段
        foreach ($fields as $key => $value) {
            if ($value == 'department') {
                $fields[$key] = 'departid';
            }
            if ($value == 'repairTypeName') {
                $fields[$key] = 'repair_type';
            }
        }
        $fields_1 = $this->getFields('repair', $fields, 'A');
        $fields_2 = '';
        if ($fields_1 && $fields_2) {
            $selFields = $fields_1.','.$fields_2;
        } elseif ($fields_1) {
            $selFields = $fields_1;
        } else {
            $selFields = $fields_2;
        }
        $join = ' LEFT JOIN sb_department as B on A.departid = B.departid ';
        $data = $repModel->DB_get_all_join('repair', 'A', $selFields, $join, ['A.repid' => ['in', $repid]], '', $order);
        $showName = ['xuhao' => '序号', 'department' => '所属科室'];
        $keyValue = $this->getDefaultShowFields();
        foreach ($keyValue as $k => $v) {
            if (in_array($k, $fields)) {
                $showName[$k] = $v;
            }
        }

        $departname = [];
        include APP_PATH.'Common/cache/department.cache.php';
        foreach ($data as $k => $v) {
            $data[$k]['xuhao'] = $k + 1;
            $data[$k]['applicant_time'] = date('Y-m-d H:i', $v['applicant_time']);
            $data[$k]['department'] = $departname[$v['departid']]['department'];
            $fault_problem_id = $repModel->DB_get_one('repair_fault', 'group_concat(fault_problem_id) as fault_problem_id', ['repid' => $v['repid']]);
            if ($fault_problem_id['fault_problem_id']) {
                $pros = $repModel->DB_get_one('repair_setting', 'group_concat(title) as title', ['id' => ['in', $fault_problem_id['fault_problem_id']]]);
                $data[$k]['fault_problem'] = $pros['title'];
            } else {
                $data[$k]['fault_problem'] = '';
            }
        }
        exportAssets(['设备维修记录表'], '设备维修记录表', $showName, $data);
    }

    /**
     * Notes: 返回要查询的字段.
     *
     * @param $table string 要获取字段的表名称
     * @param $fields array 全部要查字段
     * @param $alias string 虚拟表名
     *
     * @return string
     */
    public function getFields($table, $fields, $alias)
    {
        $tableName = $this->tablePrefix.$table;
        $sql = 'SHOW FULL COLUMNS FROM '.$tableName;
        $res = M($this->tableName)->query($sql);
        $fieldStr = '';
        foreach ($res as $k => $v) {
            if (in_array($v['Field'], $fields)) {
                $fieldStr .= $alias.'.'.$v['Field'].',';
            }
        }
        $fieldStr = trim($fieldStr, ',');

        return $fieldStr;
    }

    public function getDefaultShowFields()
    {
        $fields = [];
        $fields['repnum'] = '维修单编号';
        $fields['assets'] = '设备名称';
        $fields['assnum'] = '设备编码';
        $fields['model'] = '规格/型号';
        $fields['department'] = '使用科室';
        $fields['applicant'] = '报修人';
        $fields['engineer'] = '维修工程师';
        $fields['part_num'] = '配件数';
        $fields['applicant_time'] = '报修时间';
        $fields['breakdown'] = '故障描述';
        $fields['fault_problem'] = '故障问题';
        $fields['repair_remark'] = '检修备注';
        $fields['dispose_detail'] = '处理详情';
        $fields['repairTypeName'] = '维修性质';
        $fields['actual_price'] = '总维修费用';
        $fields['working_hours'] = '维修工时';

        return $fields;
    }

    /**
     * 显示维修详情.
     */
    public function showRepair()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'upload':
                    //本地上传
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->uploadfile();
                    $result = $RepairModel->insetRepairFile($result);
                    $result['add_user'] = session('username');
                    $result['add_time'] = getHandleDate(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'uploadStartRepairFile':
                    //本地上传
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->uploadfile();
                    $result = $RepairModel->uploadStartRepairFile($result);
                    $result['add_user'] = session('username');
                    $result['add_time'] = getHandleDate(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'uploadReport':
                    //本地上传
                    $RepairModel = new RepairModel();
                    $result = $RepairModel->uploadfile();
                    $result = $RepairModel->addCheckFile($result);
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $RepMod = new RepairModel();
            $repid = I('get.repid');
            $assid = I('get.assid');
            $asjoin[0] = 'LEFT JOIN sb_assets_factory AS B ON B.afid=A.afid';
            $asfileds = 'A.assnum,A.model,A.assets,A.guarantee_date,B.factory,B.repa_tel,B.repa_user,A.catid,A.departid';
            //查询主设备详情
            $assetsinfo = $RepMod->DB_get_one_join('assets_info', 'A', $asfileds, $asjoin, ['A.assid' => $assid], '');
            //读缓存
            $departname = [];
            $catname = [];
            include APP_PATH.'Common/cache/category.cache.php';
            include APP_PATH.'Common/cache/department.cache.php';
            //科室
            $assetsinfo['guarantee_date'] = HandleEmptyNull($assetsinfo['guarantee_date']);
            $assetsinfo['departname'] = $departname[$assetsinfo['departid']]['department'];
            //分类
            $assetsinfo['category'] = $catname[$assetsinfo['catid']]['category'];
            //保修期内判断
            if (getHandleTime(time()) <= $assetsinfo['guarantee_date']) {
                $assetsinfo['guaranteeStatus'] = '保修期内';
            } else {
                $assetsinfo['guaranteeStatus'] = '<span style="color: red;">已过保修期</span>';
            }
            //查询维修详情
            $repArr = $RepMod->showRepairData($repid);
            $repArr['fault_problem'] = $RepMod->getFaultProblem($repid);
            if ($repArr['actual_price'] == 0) {
                $repArr['actual_price'] = $repArr['expect_price'];
            }
            //维修跟进信息
            $follow = $RepMod->getRepirFollowBasic($repid);
            //审核历史
            $approves = $RepMod->getApproveBasic($repid);
            //配件/服务
            $parts = $RepMod->getAllPartsBasic($repid);
            //第三方厂家信息
            $companyData = $RepMod->getAllCompanysBasic($repid);
            $company = $companyData['data'];
            $companyLast = $companyData['lastData'];
            //查询当前用户是否具备上传文件权限(查看也是)
            $upload = get_menu($this->MODULE, 'Repair', 'uploadRepair');
            if ($upload) {
                $uploadRepair = 1;
            } else {
                $uploadRepair = 2;
            }

            $upload = get_menu($this->MODULE, $this->Controller, 'uploadRepair');
            if ($upload) {
                //获取上传的文件
                $files = $RepMod->getRepairFileBasic($repid);
                $this->assign('files', $files);
            }
            //******************************审批流程显示 start***************************//
            $repArr = $RepMod->get_approves_progress($repArr, 'repid', 'repid');
            //**************************************审批流程显示 end*****************************//
            //配件统一报价 1可操作报价 0不可操作
            $IS_OPEN_OFFER = C('DO_STATUS');
            //第三方统一报价 1可操作报价 0不可操作
            $doOfferMenu = get_menu($this->MODULE, $this->Controller, 'doOffer');
            $IS_OPEN_OFFER_DO_OFFER = C('DO_STATUS');
            if (C('IS_OPEN_OFFER')) {
                if (!$doOfferMenu) {
                    $IS_OPEN_OFFER = C('NOT_DO_STATUS');
                    $IS_OPEN_OFFER_DO_OFFER = C('NOT_DO_STATUS');
                }
            } else {
                //第三方因为没有开启报价功能,也需要走报价让报价经理选，所以另开一个变量
                if (!$doOfferMenu) {
                    //报价关闭 无权限（可填价格 数量 建议厂家）
                    $IS_OPEN_OFFER_DO_OFFER = C('SHUT_STATUS_DO');
                }
            }
            //时间线进度条
            $repairTimeRestult = $RepMod->showRepairTimeLineData($repid);
            $repairTimeLineProgress = $repairTimeRestult['repairTimeLine'];
            $repairTimeLine = $repairTimeRestult['repairTimeLineData'];
            //时间线
            foreach ($repairTimeLine as $k => $v) {
                if ($repairTimeLine[$k]['date'] == '-') {
                    unset($repairTimeLine[$k]);
                }
            }
            //第三方公司选择
            $repairTimeLine = $RepMod->getUserNameAndPhone($repairTimeLine);
            $repairTimeLine = array_unique($repairTimeLine, SORT_REGULAR);
            $this->assign('repairTimeLineProgress', $repairTimeLineProgress);
            $this->assign('repairTimeLine', array_reverse($repairTimeLine));
            $this->assign('approves', $approves);
            $this->assign('isOpenOffer', $IS_OPEN_OFFER);
            $this->assign('isOpenOffer_formOffer', $IS_OPEN_OFFER_DO_OFFER);
            $this->assign('uploadRepair', $uploadRepair);
            $this->assign('repair_type', $repArr['repair_type']);
            $this->assign('assetsinfo', $assetsinfo);
            $this->assign('repArr', $repArr);
            $this->assign('parts', $parts);
            $this->assign('company', $company);
            $this->assign('companyLast', $companyLast);
            $this->assign('follow', $follow);
            $this->assign('repid', $repid);
            $this->assign('showRepairUrl', get_url());
            $this->display();
        }
    }

    /**
     * 显示相关上传文件.
     */
    public function showUpload()
    {
        $RepMod = new repairModel();
        $repid = I('get.repid');
        $file_url = I('get.file_url');
        if ($file_url) {
            $uploadinfo = $RepMod->DB_get_all('repair_file', 'file_url', ['repid' => $repid, 'file_url' => $file_url]);
        } else {
            $uploadinfo = $RepMod->DB_get_all('repair_file', 'file_url', ['repid' => $repid]);
        }
        foreach ($uploadinfo as $k => $v) {
            $uploadinfo[$k]['suffix'] = substr(strrchr($v['file_url'], '.'), 1);
            switch ($uploadinfo[$k]['suffix']) {
                //是图片类型
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'bmp':
                case 'gif':
                    $uploadinfo[$k]['type'] = 1;
                    break;
                //pdf类型
                case 'pdf':
                    $uploadinfo[$k]['type'] = 2;
                    break;
                //文档类型
                case 'doc':
                case 'docx':
                    $uploadinfo[$k]['type'] = 3;
                    break;
            }
        }
        $this->assign('uploadinfo', $uploadinfo);
        $this->display();
    }

    /**
     * 显示维修详情.
     */
    public function cancelRepair()
    {
        $RepMod = new RepairModel();
        if (IS_POST) {
            $repid = I('post.repid');
            $cancle_remark = I('post.cancle_remark');
            $repairInfo = $RepMod->DB_get_one('repair', 'assid,status', ['repid' => $repid]);
            if ($repairInfo['status'] >= 7) {
                $this->ajaxReturn(['status' => -1, 'msg' => '已维修完成，无法撤单'], 'json');
            }
            $data['repid'] = $repid;
            $data['fainal_status'] = $repairInfo['status'];
            $data['cancle_remark'] = $cancle_remark;
            $data['cancle_user'] = session('username');
            $data['cancle_time'] = date('Y-m-d H:i:s');
            $exist = $RepMod->DB_get_one('repair_cancle', '*', ['repid' => $repid]);
            if ($exist) {
                $this->ajaxReturn(['status' => -1, 'msg' => '请勿重复提交'], 'json');
            }
            $RepMod->insertData('repair_cancle', $data);
            $RepMod->updateData('repair', ['status' => -1], ['repid' => $repid]);
            $res = $RepMod->updateData('assets_info', ['status' => 0], ['assid' => $repairInfo['assid']]);
            if ($res) {
                $this->ajaxReturn(['status' => 1, 'msg' => '撤单成功'], 'json');
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '撤单失败'], 'json');
            }
        } else {
            $repid = I('get.repid');
            $assid = I('get.assid');
            $asjoin[0] = 'LEFT JOIN sb_assets_factory AS B ON B.afid=A.afid';
            $asfileds = 'A.assnum,A.model,A.assets,A.guarantee_date,B.factory,B.repa_tel,B.repa_user,A.catid,A.departid';
            //查询主设备详情
            $assetsinfo = $RepMod->DB_get_one_join('assets_info', 'A', $asfileds, $asjoin, ['A.assid' => $assid], '');
            //读缓存
            $departname = [];
            $catname = [];
            include APP_PATH.'Common/cache/category.cache.php';
            include APP_PATH.'Common/cache/department.cache.php';
            //科室
            $assetsinfo['guarantee_date'] = HandleEmptyNull($assetsinfo['guarantee_date']);
            $assetsinfo['departname'] = $departname[$assetsinfo['departid']]['department'];
            //分类
            $assetsinfo['category'] = $catname[$assetsinfo['catid']]['category'];
            //保修期内判断
            if (getHandleTime(time()) <= $assetsinfo['guarantee_date']) {
                $assetsinfo['guaranteeStatus'] = '保修期内';
            } else {
                $assetsinfo['guaranteeStatus'] = '<span style="color: red;">已过保修期</span>';
            }
            //查询维修详情
            $repArr = $RepMod->showRepairData($repid);
            $repArr['fault_problem'] = $RepMod->getFaultProblem($repid);
            if ($repArr['actual_price'] == 0) {
                $repArr['actual_price'] = $repArr['expect_price'];
            }
            $this->assign('user', session('username'));
            $this->assign('now', date('Y-m-d H:i:s'));
            $this->assign('assetsinfo', $assetsinfo);
            $this->assign('repArr', $repArr);
            $this->assign('repid', $repid);
            $this->assign('cancelRepairUrl', get_url());
            $this->display();
        }
    }
}
