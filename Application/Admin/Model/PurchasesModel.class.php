<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2018/09/19
 * Time: 13:50
 */

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Think\Model;
use Think\Model\RelationModel;

class PurchasesModel extends CommonModel
{
    private $MODULE = 'Purchases';
    private $Controller = 'PurchasePlans';
    protected $tablePrefix = 'sb_';
    protected $tableName = 'purchases_plans';

    /**
     * Notes: 获取采购计划列表
     */
    public function getPlansList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'plans_id';
        $plans_year = I('post.plans_year');
        $plans_status = I('post.plans_status');
        $apply_status = I('post.apply_status');
        $project_name = I('post.project_name');
        $departids = I('post.departids');
        $hospital_id = I('post.hospital_id');
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $where['departid'] = array('in', session('departid'));
        $where['is_delete'] = array('eq', C('NO_STATUS'));
        if ($departids) {
            //计划科室搜索
            $where['departid'] = array('in', $departids);
        }
        if ($plans_year) {
            //计划年份搜索
            $where['plans_year'] = $plans_year;
        }
        if ($plans_status != '') {
            //计划状态搜索
            $where['plans_status'] = $plans_status;
        }
        if ($apply_status != '') {
            //上报状态搜索
            $where['apply_status'] = $apply_status;
        }
        if ($project_name) {
            //项目名称搜索
            $where['project_name'] = array('like', "%$project_name%");
        }
        if ($hospital_id) {
            //医院搜索
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }
        //查询当前用户是否有权限进行上报计划
        $departReport = get_menu($this->MODULE, 'PurchasePlans', 'departReport');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $total = $this->DB_get_count('purchases_plans', $where);
        $plans = $this->DB_get_all('purchases_plans', 'plans_id,hospital_id,project_name,plans_num,plans_year,plans_start,plans_end,departid,plans_status,apply_status,apply_date,assets_nums,assets_amount,add_user', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['plans_status_name'] = $v['plans_status'] == 0 ? '未启用' : '已启用';
            $plans[$k]['approve_status_name'] = $v['approve_status'] == 0 ? '未审核' : ($v['approve_status'] == 1 ? '已通过' : ($v['approve_status'] == 2 ? '不通过' : '不需审核'));
            $plans[$k]['apply_status_icon'] = $v['apply_status'] == 0 ? '<i class="layui-icon layui-icon-zzban" style="color: #FF5722"></i>' : '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
            $html = '<div class="layui-btn-group">';
            if ($v['apply_status'] == 0) {
                if ($departReport) {
                    $html .= $this->returnListLink('上报', $departReport['actionurl'], 'departReport', C('BTN_CURRENCY') . '');
                }
            } else {
                $html .= $this->returnListLink('查看', get_url() . '?action=showPlans&id=' . $v['plans_id'], 'showPlans', C('BTN_CURRENCY') . ' layui-btn-primary');
            }
            $html .= '</div>';
            $plans[$k]['plans_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 添加科室采购计划
     */
    public function addPlans()
    {
        $data = [];
        $project_name = I('post.project_name');
        $plans_year = I('post.plans_year');
        $plans_start = I('post.plans_start');
        $plans_end = I('post.plans_end');
        $plans_status = I('post.plans_status');
        $plans_desc = I('post.plans_desc');
        $departid = I('post.departid');
        $hospital_id = session('current_hospitalid');
        if (!trim($project_name)) {
            return array('status' => -1, 'msg' => '项目名称不能为空！');
        }
        if (!trim($plans_year)) {
            return array('status' => -1, 'msg' => '计划年份不能为空！');
        }
        if (!trim($plans_start)) {
            return array('status' => -1, 'msg' => '开始时间不能为空！');
        }
        if (!trim($plans_end)) {
            return array('status' => -1, 'msg' => '结束时间不能为空！');
        }
        if (!trim($departid)) {
            return array('status' => -1, 'msg' => '计划科室不能为空！');
        }
        //查询项目名称是否已存在
        $pro = $this->DB_get_one('purchases_plans', 'plans_id', array('project_name' => $project_name, 'is_delete' => C('NO_STATUS')));
        if ($pro) {
            return array('status' => -1, 'msg' => '项目名称已存在！');
        }
        $departids = explode(',', $departid);
        $code = rand(10000, 99999);
        foreach ($departids as $k => $v) {
            $data[$k]['plans_num'] = 'JH-SQ' . date('ym') . $code;
            $data[$k]['hospital_id'] = $hospital_id;
            $data[$k]['plans_year'] = $plans_year;
            $data[$k]['project_name'] = $project_name;
            $data[$k]['plans_start'] = $plans_start;
            $data[$k]['plans_end'] = $plans_end;
            $data[$k]['plans_status'] = $plans_status;
            $data[$k]['plans_desc'] = $plans_desc;
            $data[$k]['departid'] = $v;
            $data[$k]['add_user'] = session('username');
            $data[$k]['add_time'] = date('Y-m-d H:i:s');
        }
        $res = $this->insertDataALL('purchases_plans', $data);
        if ($res) {
            $log['project_name'] = $project_name;
            $text = getLogText('addPurchasesPlansLogText', $log);
            $this->addLog('purchases_plans', M()->getLastSql(), $text, $res);
            return array('status' => 1, 'msg' => '创建计划成功！');
        } else {
            return array('status' => -1, 'msg' => '创建计划失败！');
        }
    }

    /**
     * Notes: 获取计划信息
     * @param $plans_id int 计划ID
     */
    public function getPlansInfo($plans_id)
    {
        return $this->DB_get_one('purchases_plans', '*', array('plans_id' => $plans_id, 'is_delete' => C('NO_STATUS')));
    }

    /**
     * Notes: 获取计划设备信息
     * @param $plans_id int 计划ID
     */
    public function getPlansAssetsInfo($plans_id)
    {
        //查询设备列表
        return $this->DB_get_all('purchases_plans_assets', '*', array('plans_id' => $plans_id, 'is_delete' => C('NO_STATUS')));
    }

    /**
     * Notes: 获取计划设备信息
     * @param $plans_id int 计划ID
     */
    public function getPlansFilesInfo($plans_id)
    {
        //查询附件列表
        return $this->DB_get_all('purchases_plans_file', '*', array('plans_id' => $plans_id, 'is_delete' => C('NO_STATUS')));
    }


    /**
     * Notes: 新增设备
     */
    public function addAssets()
    {
        //查询是否已同名设备
        $plans_id = I('post.plans_id');
        $assInfo = $this->DB_get_one('purchases_plans_assets', 'assets_id', array('plans_id' => $plans_id, 'assets_name' => I('post.assets_name')));
        if ($assInfo) {
            return array('status' => -1, 'msg' => '已有同名设备！');
        }
        $data = [];
        $data['plans_id'] = $plans_id;
        $data['assets_name'] = I('post.assets_name');
        $data['unit'] = I('post.unit');
        $data['nums'] = I('post.nums');
        $data['market_price'] = I('post.market_price');
        $data['total_price'] = $data['nums'] * $data['market_price'];
        $data['is_import'] = I('post.is_import');
        $data['buy_type'] = I('post.buy_type');
        $data['brand'] = I('post.brand');
        $assets_id = $this->insertData('purchases_plans_assets', $data);
        if ($assets_id) {
            return array('status' => 1, 'msg' => '添加设备成功！');
        } else {
            return array('status' => -1, 'msg' => '添加设备失败！');
        }
    }

    /**
     * Notes: 修改设备信息
     */
    public function editAssets()
    {
        $data = [];
        $assets_id = I('post.assets_id');
        $data['assets_name'] = I('post.assets_name');
        $data['unit'] = I('post.unit');
        $data['nums'] = I('post.nums');
        $data['market_price'] = I('post.market_price');
        $data['total_price'] = $data['nums'] * $data['market_price'];
        $data['is_import'] = I('post.is_import');
        $data['buy_type'] = I('post.buy_type');
        $data['brand'] = I('post.brand');
        $res = $this->updateData('purchases_plans_assets', $data, array('assets_id' => $assets_id));
        if ($res) {
            return array('status' => 1, 'msg' => '修改设备成功！');
        } else {
            return array('status' => -1, 'msg' => '修改设备失败或没有要修改的内容！');
        }
    }

    /**
     * Notes: 删除设备信息
     */
    public function delAssets()
    {
        $data = [];
        $assets_id = I('post.assets_id');
        $data['is_delete'] = C('YES_STATUS');//1
        $res = $this->updateData('purchases_plans_assets', $data, array('assets_id' => $assets_id));
        if ($res) {
            return array('status' => 1, 'msg' => '删除设备成功！');
        } else {
            return array('status' => -1, 'msg' => '删除设备失败或没有要修改的内容！');
        }
    }

    /**
     * Notes: 上传附件信息
     */
    public function uploadFile()
    {
        $plans_id = I('post.plans_id');
        $size = round($_FILES['file']['size'] / 1024 / 1024, 2);
        $Tool = new ToolController();
        $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx', 'zip');
        $dirName = C('UPLOAD_DIR_PURCHASES_APPLY_ASSETS_FILE_NAME');
        $info = $Tool->upFile($style, $dirName);
        if ($info['status'] == C('YES_STATUS')) {
            $type = explode('.', $info['formerly']);
            $name = explode('/', $info['name']);
            $data['plans_id'] = $plans_id;
            $data['file_name'] = $info['formerly'];
            $data['save_name'] = $name[1];
            $data['file_type'] = $type[1];
            $data['file_size'] = $size . 'M';
            $data['file_url'] = $info['src'];
            $data['add_user'] = session('username');
            $data['add_time'] = date('Y-m-d H:i:s');
            $res = $this->insertData('purchases_plans_file', $data);
            if ($res) {
                $resule['status'] = 1;
                $resule['msg'] = '上传成功';
            } else {
                $resule['status'] = -1;
                $resule['msg'] = '上传失败';
            }
        } else {
            // 上传错误提示错误信息
            $resule['status'] = -1;
            $resule['msg'] = $info['msg'];
        }
        return $resule;
    }

    /**
     * Notes: 删除附件信息
     */
    public function delFile()
    {
        $data = [];
        $file_id = I('post.file_id');
        $data['is_delete'] = C('YES_STATUS');//1
        $res = $this->updateData('purchases_plans_file', $data, array('file_id' => $file_id));
        if ($res) {
            return array('status' => 1, 'msg' => '删除附件成功！');
        } else {
            return array('status' => -1, 'msg' => '删除附件失败！');
        }
    }

    /**
     * Notes: 保存申请单最终信息
     */
    public function finalSave()
    {
        $remark = '';
        $plans_id = I('post.plans_id');
        $plansInfo = $this->DB_get_one('purchases_plans', 'plans_id,hospital_id,project_name,departid', array('plans_id' => $plans_id, 'is_delete' => C('NO_STATUS')));
        $assInfo = $this->DB_get_one('purchases_plans_assets', 'SUM(nums) as nums,SUM(total_price) as total_price', array('plans_id' => $plans_id, 'is_delete' => C('NO_STATUS')));
        if (!$assInfo['nums']) {
            return array('status' => -1, 'msg' => '请先添加设备明细和附件！');
        }
        //查询采购计划审核是否已开启
        $isOpenApprove = $this->checkApproveIsOpen(C('PURCHASES_PLANS_APPROVE'), $plansInfo['hospital_id']);
        $approve=[];
        if ($isOpenApprove) {
            //查询是否已设置审批流程
            $isSetProcess = $this->checkApproveIsSetProcess(C('PURCHASES_PLANS_APPROVE'), $plansInfo['hospital_id']);
            if (!$isSetProcess) {
                die(json_encode(array('status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！')));
            }
            //已开采购计划审核
            //获取审批流程
            $approve_process_user = $this->get_approve_process($assInfo['total_price'], C('PURCHASES_PLANS_APPROVE'), $plansInfo['hospital_id']);
            //并且获取下次审批人
            $approve = $this->check_approve_process($plansInfo['departid'], $approve_process_user, 1);
            if ($approve['all_approver'] == '') {
                //不在审核范围内 不需要审批
                $plan_data['approve_status'] = C('STATUS_APPROE_UNWANTED');//-1不需审批
                $remark = ' 无需审批！';
            } else {
                //默认为未审核
                $plan_data['current_approver'] = $approve['current_approver'];
                $plan_data['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                $plan_data['all_approver'] = $approve['all_approver'];
                $plan_data['approve_status'] = C('APPROVE_STATUS');//-1不需审批
                $remark = ' 正在等待审批！';
            }
        } else {
            $plan_data['approve_status'] = C('STATUS_APPROE_UNWANTED');//-1不需审批
        }
        $plan_data['apply_user'] = session('username');
        $plan_data['apply_time'] = date('Y-m-d H:i:s');
        $plan_data['apply_reason'] = I('post.apply_reason');
        $plan_data['apply_status'] = C('YES_STATUS');
        $plan_data['apply_date'] = date('Y-m-d');
        $plan_data['assets_nums'] = $assInfo['nums'];
        $plan_data['can_apply_nums'] = $assInfo['nums'];
        $plan_data['assets_amount'] = $assInfo['total_price'];
        $res = $this->updateData('purchases_plans', $plan_data, array('plans_id' => $plans_id));
        if ($res) {
            $departname = array();
            include APP_PATH . "Common/cache/department.cache.php";
            $log['project_name'] = $plansInfo['project_name'];
            $log['department'] = $departname[$plansInfo['departid']]['department'];
            $text = getLogText('departReportLogText', $log);
            $this->addLog('purchases_depart_apply', M()->getLastSql(), $text . $remark, $res);
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->MODULE);
            if ($settingData) {
                //有开启短信
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $data = $this->getPlansInfo($plans_id);
                $data['department'] = $departname[$plansInfo['departid']]['department'];
                $ToolMod = new ToolController();
                if ($approve['this_current_approver']) {
                    //通知审批人审批
                    $where = [];
                    $where['status'] = C('OPEN_STATUS');
                    $where['is_delete'] = C('NO_STATUS');
                    $where['username'] = $approve['this_current_approver'];
                    $approve_user = $this->DB_get_one('user', 'telephone', $where);
                    if ($settingData['purchasePlanApprove']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                        $sms = $this->formatSmsContent($settingData['purchasePlanApprove']['content'], $data);
                        $ToolMod->sendingSMS($approve_user['telephone'], $sms, $this->MODULE, $plans_id);
                    }
                }
            }
            //==========================================短信 END==========================================
            return array('status' => 1, 'msg' => '上报计划成功！');
        } else {
            return array('status' => -1, 'msg' => '上报计划成功！');
        }
    }

    /**
     * Notes: 获取申请单信息
     */
    public function getApplyInfo($plans_id)
    {
        return $this->DB_get_one('purchases_depart_apply', 'apply_user,apply_time,apply_reason', array('plans_id' => $plans_id));
    }

    /**
     * Notes: 获取采购计划审批列表
     */
    public function getApprovePlansList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'plans_id';
        $project_name = I('post.project_name');
        $departids = I('post.departids');
        $hospital_id = I('post.hospital_id');
        $approve_status = I('post.approve_status');
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $where['departid'] = array('in', session('departid'));
        $where['is_delete'] = array('eq', C('NO_STATUS'));
        $where['apply_status'] = array('eq', C('YES_STATUS'));
        $where['approve_status'] = array('neq', -1);
        $where['all_approver'] = array('LIKE', '%/' . session('username') . '/%');
        if ($departids) {
            //计划科室搜索
            $where['departid'] = array('in', $departids);
        }
        if ($project_name) {
            //项目名称搜索
            $where['project_name'] = array('like', "%$project_name%");
        }
        if ($hospital_id) {
            //医院搜索
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }
        if ($approve_status != '') {
            $where['approve_status'] = $approve_status;
        }
        //查询当前用户是否有权限进行采购计划审批
        $Apply = get_menu($this->MODULE, $this->Controller, 'purchasePlanApprove');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $total = $this->DB_get_count('purchases_plans', $where);
        $plans = $this->DB_get_all('purchases_plans', '*', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['plans_status_name'] = $v['plans_status'] == 0 ? '未启用' : '已启用';
            $plans[$k]['apply_status_icon'] = $v['apply_status'] == 0 ? '<i class="layui-icon layui-icon-zzban" style="color: #FF5722"></i>' : '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
            $plans[$k]['approve_status_name'] = $v['approve_status'] == 0 ? '未审核' : ($v['approve_status'] == 1 ? '已通过' : ($v['approve_status'] == 2 ? '<span style="color:red;">不通过</span>' : '不需审核'));
            $html = '<div class="layui-btn-group">';
            if ($v['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                $html .= $this->returnListLink('已通过', $Apply['actionurl'], 'approval', C('BTN_CURRENCY') . ' layui-btn-normal');
            } elseif ($v['approve_status'] == C('STATUS_APPROE_FAIL')) {
                $html .= $this->returnListLink('不通过', $Apply['actionurl'], 'approval', C('BTN_CURRENCY') . ' layui-btn-danger');
            } else {
                if ($Apply && $v['current_approver']) {
                    $current_approver = explode(',', $v['current_approver']);
                    $current_approver_arr = [];
                    foreach ($current_approver as &$current_approver_value) {
                        $current_approver_arr[$current_approver_value] = true;
                    }
                    if ($current_approver_arr[session('username')]) {
                        $html .= $this->returnListLink('审核', $Apply['actionurl'], 'approval', C('BTN_CURRENCY') . ' layui-btn');
                    } else {
                        $complete = explode(',', $v['complete_approver']);
                        $notcomplete = explode(',', $v['not_complete_approver']);
                        if (!in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                            //完全未审
                            $html .= $this->returnListLink('待审核', $Apply['actionurl'], 'approval', C('BTN_CURRENCY') . ' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                            //有已审，有未审
                            $html .= $this->returnListLink('待审核', $Apply['actionurl'], 'approval', C('BTN_CURRENCY') . ' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && !in_array(session('username'), $notcomplete)) {
                            //全部已审
                            $html .= $this->returnListLink('已审核', $Apply['actionurl'], 'approval', C('BTN_CURRENCY') . ' layui-btn-warm');
                        } else {
                            $html .= '';
                        }
                    }
                }
            }
            $html .= '</div>';
            $plans[$k]['app_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取采购计划审批情况
     * @param $plans_id int 计划ID
     */
    public function getPlansApproveInfo($plans_id)
    {
        $examine = $this->DB_get_all('approve', 'purchases_plans_id,approver,is_adopt,approve_time,remark', array('purchases_plans_id' => $plans_id), '', 'approve_time asc', '');
        foreach ($examine as $k => $v) {
            $examine[$k]['is_adopt'] = $v['is_adopt'] == 1 ? '通过' : '不通过';
            $examine[$k]['approve_time'] = getHandleDate($v['approve_time']);
            $examine[$k]['approve_name'] = '年度采购计划审批';
        }
        return $examine;
    }

    /**
     * Notes: 保存采购计划审批记录
     * @return array
     */
    public function saveApprove()
    {
        if (!trim(I('post.remark'))) {
            return array('status' => -1, 'msg' => '审核意见不能为空！');
        }
        if (I('post.plans_id')) {
            $data['purchases_plans_id'] = I('post.plans_id');
        } else {
            return array('status' => -1, 'msg' => '参数错误！');
        }
        //检查是否存在此条记录
        $where['plans_id'] = array('EQ', $data['purchases_plans_id']);
        $where['approve_status'] = array('NEQ', C('STATUS_APPROE_UNWANTED'));//不需审核
        $plansInfo = $this->DB_get_one('purchases_plans', '*', $where);
        if (!$plansInfo['plans_id']) {
            return array('status' => -1, 'msg' => '查找不到采购计划信息！');
        } else {
            if ($plansInfo['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                return array('status' => -1, 'msg' => '审批已通过，请勿重复提交！');
            } elseif ($plansInfo['approve_status'] == C('STATUS_APPROE_FAIL')) {
                return array('status' => -1, 'msg' => '审批已否决，请勿重复提交！');
            }
        }
        $data['purchases_plans_id'] = $plansInfo['plans_id'];
        $data['is_adopt'] = I('POST.is_adopt');
        $data['remark'] = trim(I('POST.remark'));
        $data['proposer'] = $plansInfo['apply_user'];
        $data['proposer_time'] = strtotime($plansInfo['apply_time']);
        $data['approver'] = session('username');
        $data['approve_time'] = time();
        $data['approve_class'] = 'purchases_plans';
        $data['process_node'] = C('PURCHASES_PLANS_APPROVE');
        //判断是否是当前审批人
        if ($plansInfo['current_approver']) {
            $current_approver = explode(',', $plansInfo['current_approver']);
            $current_approver_arr = [];
            foreach ($current_approver as &$current_approver_value) {
                $current_approver_arr[$current_approver_value] = true;
            }
            if ($current_approver_arr[session('username')]) {
                $processWhere['purchases_plans_id'] = array('EQ', $plansInfo['plans_id']);
                $processWhere['is_delete'] = array('NEQ', C('YES_STATUS'));
                $process = $this->DB_get_count('approve', $processWhere);
                $level = $process + 1;
                $data['process_node_level'] = $level;
                $res = $this->addApprove($plansInfo, $data, $plansInfo['assets_amount'], $plansInfo['hospital_id'], $plansInfo['departid'], C('PURCHASES_PLANS_APPROVE'), 'purchases_plans', 'plans_id');
                if ($res['status'] == 1) {
                    $text = getLogText('purchasesApproverLogText', array('project_name' => $plansInfo['project_name'], 'is_adopt' => I('POST.is_adopt')));
                    $this->addLog('purchases_plans', $res['lastSql'], $text, $plansInfo['plans_id'], '');
                }
                return $res;
            } else {
                return array('status' => -1, 'msg' => '请等待审批！');
            }
        } else {
            return array('status' => -1, 'msg' => '审核已结束！');
        }
    }

    /**
     * Notes: 获取采购计划的设备
     * @param $plans_id int 采购计划ID
     */
    public function addPlnasAssets($plans_id)
    {
        $assets = $this->DB_get_all('purchases_plans_assets', '*', array('plans_id' => $plans_id, 'is_delete' => C('NO_STATUS')));
        foreach ($assets as $k => $v) {
            $assets[$k]['buy_type_name'] = ($v['buy_type'] == 1) ? '报废更新' : ($v['buy_type'] == 2 ? '添置' : '新增');
            $assets[$k]['is_import_name'] = ($v['is_import'] == 1) ? '是' : '否';
        }
        return $assets;
    }

    /**
     * Notes: 获取采购计划的文件
     * @param $plans_id int 采购计划ID
     */
    public function addPlnasFiles($plans_id)
    {
        return $this->DB_get_all('purchases_plans_file', '*', array('plans_id' => $plans_id, 'is_delete' => C('NO_STATUS')));
    }

    /**
     * Notes: 添加科室采购申请
     */
    public function addDepartApply()
    {
        $data = $ass_data = $file_data = [];
        $data['apply_type'] = I('post.apply_type');
        if ($data['apply_type'] == 1) {
            //计划内
            $data['plans_id'] = I('post.plans_id');
            //统计设备总金额
            $sum = $this->DB_get_one('purchases_plans_assets', 'SUM(total_price) AS total_price,SUM(nums) as nums', array('plans_id' => $data['plans_id'], 'is_delete' => C('NO_STATUS')));
            $total_price = (float)$sum['total_price'];
            $nums = $sum['nums'];
        } else {
            $assets_name = explode(',', trim(I('post.assets_name'), ','));
            $unit = explode(',', trim(I('post.unit'), ','));
            $is_import = explode(',', trim(I('post.is_import'), ','));
            $buy_type = explode(',', trim(I('post.buy_type'), ','));
            $brand = explode('|', trim(I('post.brand'), '|'));
            //统计设备总金额
            $nums = explode(',', trim(I('post.nums'), ','));
            $market_price = explode(',', trim(I('post.market_price'), ','));
            $total_price = 0;
            foreach ($nums as $k => $v) {
                $total_price += $v * $market_price[$k];
            }
            $total_price = round($total_price, 2);
            foreach ($assets_name as $k => $v) {
                $ass_data[$k]['assets_name'] = $v;
                $ass_data[$k]['unit'] = $unit[$k];
                $ass_data[$k]['nums'] = $nums[$k];
                $ass_data[$k]['market_price'] = $market_price[$k];
                $ass_data[$k]['total_price'] = $nums[$k] * $market_price[$k];
                $ass_data[$k]['is_import'] = $is_import[$k] == '否' ? 0 : 1;
                $ass_data[$k]['buy_type'] = $buy_type[$k] == '报废更新' ? '1' : ($buy_type[$k] == '添置' ? '2' : '3');
                $ass_data[$k]['brand'] = $brand[$k];
            }
        }
        $data['hospital_id'] = I('post.hospital_id') ? I('post.hospital_id') : session('current_hospitalid');
        //查询医院年度采购下限
        $amount = $this->DB_get_one('hospital', 'amount_limit', array('hospital_id' => $data['hospital_id']));
        if ($total_price > (float)$amount['amount_limit']) {
            //大于年度下限，需要走专家评审
            $data['expert_review'] = 1;
        } else {
            $data['expert_review'] = 0;
        }
        $code = rand(10000, 99999);
        $data['apply_num'] = 'DP-SQ' . date('ym') . $code;
        $data['apply_departid'] = I('post.departid');
        $data['apply_user'] = session('username');
        $data['apply_time'] = date('Y-m-d H:i:s');
        $data['apply_reason'] = I('post.apply_reason');
        $data['project_name'] = I('post.project_name');
        if (!$data['apply_departid']) {
            return array('status' => -1, 'msg' => '请选择申报科室！');
        }
        if (!trim($data['apply_reason'])) {
            return array('status' => -1, 'msg' => '申请理由不能为空！');
        }
        //是否开启科室计划审批
        $approve=[];
        $isOpenApprove = $this->checkApproveIsOpen(C('DEPART_APPLY_APPROVE'), $data['hospital_id']);
        if ($isOpenApprove) {
            //查询是否已设置审批流程
            $isSetProcess = $this->checkApproveIsSetProcess(C('DEPART_APPLY_APPROVE'), $data['hospital_id']);
            if (!$isSetProcess) {
                die(json_encode(array('status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！')));
            }
            //已开启科室计划审批
            $approve_process_user = $this->get_approve_process($total_price, C('DEPART_APPLY_APPROVE'), $data['hospital_id']);
            //并且获取下次审批人
            $approve = $this->check_approve_process($data['apply_departid'], $approve_process_user, 1);
            if ($approve['all_approver'] == '') {
                //不在审核范围内 不需要审批
                $data['approve_status'] = C('STATUS_APPROE_UNWANTED');//-1不需审批
                $remark = ' 无需审批！';
            } else {
                //默认为未审核
                $data['current_approver'] = $approve['current_approver'];
                $data['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                $data['all_approver'] = $approve['all_approver'];
                $data['approve_status'] = C('APPROVE_STATUS');//-1不需审批
                $remark = ' 正在等待审批！';
            }
        } else {
            $data['approve_status'] = C('STATUS_APPROE_UNWANTED');//-1不需审批
        }
        $res = $this->insertData('purchases_depart_apply', $data);
        if ($res) {
            //记录日志
            $log['project_name'] = $data['project_name'];
            $text = getLogText('addDepartPlansLogText', $log);
            $this->addLog('purchases_depart_apply', M()->getLastSql(), $text, $res);
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->MODULE);
            if ($settingData) {
                //有开启短信
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $data['department'] = $departname[$data['apply_departid']]['department'];
                $ToolMod = new ToolController();
                if ($approve['this_current_approver']) {
                    //通知审批人审批
                    $where = [];
                    $where['status'] = C('OPEN_STATUS');
                    $where['is_delete'] = C('NO_STATUS');
                    $where['username'] = $approve['this_current_approver'];
                    $approve_user = $this->DB_get_one('user', 'telephone', $where);
                    if ($settingData['approveApply']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                        $sms = $this->formatSmsContent($settingData['approveApply']['content'], $data);
                        $ToolMod->sendingSMS($approve_user['telephone'], $sms, $this->MODULE, $res);
                    }
                }
            }
            //==========================================短信 END==========================================
            //写入设备信息
            if ($data['apply_type'] == 1) {
                //计划内，查询计划内设备信息
                $assets = $this->DB_get_all('purchases_plans_assets', '*', array('plans_id' => $data['plans_id'], 'is_delete' => C('NO_STATUS')));
                $as = [];
                foreach ($assets as $k => $v) {
                    $as[$k]['hospital_id'] = $data['hospital_id'];
                    $as[$k]['apply_id'] = $res;
                    $as[$k]['apply_user'] = session('username');
                    $as[$k]['apply_date'] = date('Y-m-d');
                    $as[$k]['project_name'] = I('post.project_name');
                    $as[$k]['assets_name'] = $v['assets_name'];
                    $as[$k]['unit'] = $v['unit'];
                    $as[$k]['nums'] = $v['nums'];
                    $as[$k]['market_price'] = $v['market_price'];
                    $as[$k]['total_price'] = $v['total_price'];
                    $as[$k]['is_import'] = $v['is_import'];
                    $as[$k]['buy_type'] = $v['buy_type'];
                    $as[$k]['brand'] = $v['brand'];
                    $as[$k]['departid'] = $data['apply_departid'];
                }
                $this->insertDataALL('purchases_depart_apply_assets', $as);
                //更新设备总数和总金额
                $this->updateData('purchases_depart_apply', array('assets_nums' => $nums, 'assets_amount' => $total_price), array('apply_id' => $res));
                //写入附件信息
                $files = $this->DB_get_all('purchases_plans_file', '*', array('plans_id' => $data['plans_id'], 'is_delete' => C('NO_STATUS')));
                foreach ($files as $k => $v) {
                    $file_data[$k]['apply_id'] = $res;
                    $file_data[$k]['file_name'] = $v['file_name'];
                    $file_data[$k]['save_name'] = $v['save_name'];
                    $file_data[$k]['file_type'] = $v['file_type'];
                    $file_data[$k]['file_size'] = $v['file_size'];
                    $file_data[$k]['file_url'] = $v['file_url'];
                    $file_data[$k]['add_user'] = session('username');
                    $file_data[$k]['add_time'] = date('Y-m-d H:i:s');
                }
                $this->insertDataALL('purchases_depart_apply_file', $file_data);
                //更新计划内可申请采购设备数
                $this->updateData('purchases_plans', array('can_apply_nums' => 0), array('plans_id' => $data['plans_id']));
            } else {
                //计划外
                foreach ($ass_data as $k => $v) {
                    $ass_data[$k]['apply_id'] = $res;
                    $ass_data[$k]['apply_user'] = session('username');
                    $ass_data[$k]['apply_date'] = date('Y-m-d');
                    $ass_data[$k]['project_name'] = I('post.project_name');
                    $ass_data[$k]['departid'] = $data['apply_departid'];
                    $ass_data[$k]['hospital_id'] = $data['hospital_id'];
                }
                $this->insertDataALL('purchases_depart_apply_assets', $ass_data);
                //更新设备总数和总金额
                $this->updateData('purchases_depart_apply', array('assets_nums' => count($ass_data), 'assets_amount' => $total_price), array('apply_id' => $res));
                //写入附件信息
                $file_name = explode(',', trim(I('post.file_name'), ','));
                $file_size = explode(',', trim(I('post.file_size'), ','));
                $file_type = explode(',', trim(I('post.file_type'), ','));
                $file_url = explode(',', trim(I('post.file_url'), ','));
                $save_name = explode(',', trim(I('post.save_name'), ','));
                foreach ($file_name as $k => $v) {
                    $file_data[$k]['apply_id'] = $res;
                    $file_data[$k]['file_name'] = $v;
                    $file_data[$k]['file_size'] = $file_size[$k];
                    $file_data[$k]['file_type'] = $file_type[$k];
                    $file_data[$k]['file_url'] = $file_url[$k];
                    $file_data[$k]['save_name'] = $save_name[$k];
                    $file_data[$k]['add_user'] = session('username');
                    $file_data[$k]['add_time'] = date('Y-m-d H:i:s');
                }
                $this->insertDataALL('purchases_depart_apply_file', $file_data);
            }

            $applyInfo = $this->DB_get_one('purchases_depart_apply','*',['apply_num' => $data['apply_num']]);
            if ($applyInfo['approve_status'] == C('STATUS_APPROE_SUCCESS') || $applyInfo['approve_status'] == -1) {
                //审批通过
                if ($applyInfo['expert_review'] == 0) {
                    //不需专家评审，直接生成招标记录
                    $this->createTenderRecord($applyInfo);
                } else {
                    //需要专家评审，生成专家评审信息
                    $this->insertData('purchases_expert_review', array('apply_id' => $applyInfo['apply_id']));
                }
            }

            return array('status' => 1, 'msg' => '保存成功！');
        } else {
            return array('status' => -1, 'msg' => '保存失败！');
        }
    }

    /**
     * Notes: 获取科室申请列表
     */
    public function getDepartApplyLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'apply_id';
        $departids = I('post.departids');
        $apply_type = I('post.apply_type');
        $project_name = I('post.project_name');

        $buy_type = I('post.buy_type');
        $apply_date_start = I('post.apply_date_start');
        $apply_date_end = I('post.apply_date_end');
        $approve_status = I('post.approve_status');

        $hospital_id = I('post.hospital_id');

        $where['is_delete'] = array('eq', C('NO_STATUS'));
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        if ($departids) {
            //计划科室搜索
            $where['apply_departid'] = array(array('in', $departids), array('in', session('departid')), 'and');
        } else {
            $where['apply_departid'] = array('in', session('departid'));
        }
        if ($project_name) {
            //项目名称搜索
            $where['project_name'] = array('like', "%$project_name%");
        }
        if ($apply_type != '') {
            //申请方式搜索
            $where['apply_type'] = $apply_type;
        }
        if ($buy_type != '') {
            //购置类型搜索
            $where['buy_type'] = $buy_type;
        }

        if ($approve_status != '') {
            //审核状态
            $where['approve_status'] = $approve_status;
        }
        if ($apply_date_start && !$apply_date_end) {
            $where['apply_time'] = array('egt', $apply_date_start . ' 00:00:01');
        }
        if ($apply_date_end && !$apply_date_start) {
            $where['apply_time'] = array('elt', $apply_date_end . ' 23:59:59');
        }
        if ($apply_date_start && $apply_date_end) {
            if ($apply_date_start > $apply_date_end) {
                return array('status' => -1, 'msg' => '请输入合理的搜索日期！');
            }
            $where['apply_time'] = array(array('egt', $apply_date_start . ' 00:00:01'), array('elt', $apply_date_end . ' 23:59:59'), 'and');
        }
        if ($hospital_id) {
            //医院搜索
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }

        //查询当前用户是否有权限进行科室申请审批
        $Apply = get_menu($this->MODULE, 'PurchaseApply', 'approveApply');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $total = $this->DB_get_count('purchases_depart_apply', $where);
        $plans = $this->DB_get_all('purchases_depart_apply', '*', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['apply_departid']]['department'];
            $plans[$k]['apply_date'] = date('Y-m-d', strtotime($v['apply_time']));
            $plans[$k]['buy_type_name'] = $v['buy_type'] == 1 ? '报废更新' : ($v['buy_type'] == 2 ? '添置' : '新增');
            $plans[$k]['apply_type_name'] = $v['apply_type'] == 1 ? '计划内' : '计划外';
            $plans[$k]['approve_status_name'] = $v['approve_status'] == 0 ? '未审核' : ($v['approve_status'] == 1 ? '已通过' : ($v['approve_status'] == 2 ? '<span style="color:red;">不通过</span>' : '不用审核'));
            $html = '<div class="layui-btn-group">';
            if ($v['approve_status'] != 0) {
                $html .= $this->returnListLink('查看', get_url() . '?action=showApply&id=' . $v['apply_id'], 'showApply', C('BTN_CURRENCY') . ' layui-btn-primary');
            } else {
                if ($Apply && $v['current_approver']) {
                    $current_approver = explode(',', $v['current_approver']);
                    $current_approver_arr = [];
                    foreach ($current_approver as &$current_approver_value) {
                        $current_approver_arr[$current_approver_value] = true;
                    }
                    if ($current_approver_arr[session('username')]) {
                        $html .= $this->returnListLink('审核', $Apply['actionurl'], 'approve', C('BTN_CURRENCY') . ' layui-btn');
                    } else {
                        $complete = explode(',', $v['complete_approver']);
                        $notcomplete = explode(',', $v['not_complete_approver']);
                        if (!in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                            //完全未审
                            $html .= $this->returnListLink('待审核', $Apply['actionurl'], 'approve', C('BTN_CURRENCY') . ' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                            //有已审，有未审
                            $html .= $this->returnListLink('待审核', $Apply['actionurl'], 'approve', C('BTN_CURRENCY') . ' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && !in_array(session('username'), $notcomplete)) {
                            //全部已审
                            $html .= $this->returnListLink('已审核', $Apply['actionurl'], 'approve', C('BTN_CURRENCY') . ' layui-btn-primary');
                        } else {
                            $html .= $this->returnListLink('查看', get_url() . '?action=showApply&id=' . $v['apply_id'], 'showApply', C('BTN_CURRENCY') . ' layui-btn-primary');
                        }
                    }
                } else {
                    $html .= $this->returnListLink('查看', get_url() . '?action=showApply&id=' . $v['apply_id'], 'showApply', C('BTN_CURRENCY') . ' layui-btn-primary');
                }
            }
            $html .= '</div>';
            $plans[$k]['depart_apply_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取科室申请单信息
     * @param $apply_id int 申请单ID
     * @return mixed
     */
    public function getDepartApplyInfo($apply_id)
    {
        return $this->DB_get_one('purchases_depart_apply', '*', array('apply_id' => $apply_id));
    }

    /**
     * Notes: 获取科室申请单设备信息
     * @param $apply_id int 申请单ID
     * @return mixed
     */
    public function getDepartApplyAssets($apply_id)
    {
        return $this->DB_get_all('purchases_depart_apply_assets', 'assets_id,assets_name,unit,nums,market_price,total_price,is_import,buy_type,brand', array('apply_id' => $apply_id, 'is_delete' => C('NO_STATUS')));
    }

    /**
     * Notes: 获取招标明细信息
     * @param $record_id int 招标记录ID
     */
    public function getTenderDetail($record_id)
    {
        return $this->DB_get_all('purchases_tender_detail', '*', array('record_id' => $record_id, 'is_delete' => C('NO_STATUS')));
    }

    /**
     * Notes: 获取科室申请单文件信息
     * @param $apply_id int 申请单ID
     * @return mixed
     */
    public function getDepartApplyFiles($apply_id)
    {
        return $this->DB_get_all('purchases_depart_apply_file', '*', array('apply_id' => $apply_id, 'is_delete' => C('NO_STATUS')));
    }

    /**
     * Notes: 上传附件信息
     */
    public function uploadApplySupplierFile($dir)
    {
        $size = round($_FILES['file']['size'] / 1024 / 1024, 2);
        $Tool = new ToolController();
        $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx', 'zip');
        $dirName = $dir;
        $info = $Tool->upFile($style, $dirName);
        if ($info['status'] == C('YES_STATUS')) {
            $type = explode('.', $info['formerly']);
            $name = explode('/', $info['name']);
            $resule['file_name'] = $info['formerly'];
            $resule['save_name'] = $name[1];
            $resule['file_type'] = $type[1];
            $resule['file_size'] = $size . 'M';
            $resule['file_url'] = $info['src'];
            $resule['add_user'] = session('username');
            $resule['add_time'] = date('Y-m-d H:i:s');
            $resule['status'] = 1;
            $resule['msg'] = '上传成功';
        } else {
            // 上传错误提示错误信息
            $resule['status'] = -1;
            $resule['msg'] = $info['msg'];
        }
        return $resule;
    }

    /**
     * Notes: 获取科室申请审批情况
     * @param $apply_id int 申请ID
     */
    public function getApplyApproveInfo($apply_id)
    {
        $examine = $this->DB_get_all('approve', 'depart_apply_id,approver,is_adopt,approve_time,remark', array('depart_apply_id' => $apply_id), '', 'approve_time asc', '');
        foreach ($examine as $k => $v) {
            $examine[$k]['is_adopt'] = $v['is_adopt'] == 1 ? '通过' : '不通过';
            $examine[$k]['approve_time'] = getHandleDate($v['approve_time']);
            $examine[$k]['approve_name'] = '科室申请审批';
        }
        return $examine;
    }

    /**
     * Notes: 保存科室申请审批记录
     * @return array
     */
    public function saveApplyApprove()
    {
        if (!trim(I('post.remark'))) {
            return array('status' => -1, 'msg' => '审核意见不能为空！');
        }
        if (I('post.apply_id')) {
            $data['depart_apply_id'] = I('post.apply_id');
        } else {
            return array('status' => -1, 'msg' => '参数错误！');
        }
        //检查是否存在此条记录
        $where['apply_id'] = array('EQ', $data['depart_apply_id']);
        $where['approve_status'] = array('NEQ', C('STATUS_APPROE_UNWANTED'));//不需审核
        $plansInfo = $this->DB_get_one('purchases_depart_apply', '*', $where);
        if (!$plansInfo['apply_id']) {
            return array('status' => -1, 'msg' => '查找不到科室申请信息！');
        } else {
            if ($plansInfo['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                return array('status' => -1, 'msg' => '审批已通过，请勿重复提交！');
            } elseif ($plansInfo['approve_status'] == C('STATUS_APPROE_FAIL')) {
                return array('status' => -1, 'msg' => '审批已否决，请勿重复提交！');
            }
        }
        $data['depart_apply_id'] = $plansInfo['apply_id'];
        $data['is_adopt'] = I('POST.is_adopt');
        $data['remark'] = trim(I('POST.remark'));
        $data['proposer'] = $plansInfo['apply_user'];
        $data['proposer_time'] = strtotime($plansInfo['apply_time']);
        $data['approver'] = session('username');
        $data['approve_time'] = time();
        $data['approve_class'] = 'depart_apply';
        $data['process_node'] = C('DEPART_APPLY_APPROVE');
        //判断是否是当前审批人
        if ($plansInfo['current_approver']) {
            $current_approver = explode(',', $plansInfo['current_approver']);
            $current_approver_arr = [];
            foreach ($current_approver as &$current_approver_value) {
                $current_approver_arr[$current_approver_value] = true;
            }
            if ($current_approver_arr[session('username')]) {
                $processWhere['depart_apply_id'] = array('EQ', $plansInfo['apply_id']);
                $processWhere['is_delete'] = array('NEQ', C('YES_STATUS'));
                $process = $this->DB_get_count('approve', $processWhere);
                $level = $process + 1;
                $data['process_node_level'] = $level;
                $res = $this->addApprove($plansInfo, $data, $plansInfo['assets_amount'], $plansInfo['hospital_id'], $plansInfo['apply_departid'], C('DEPART_APPLY_APPROVE'), 'purchases_depart_apply', 'apply_id');
                if ($res['status'] == 1) {
                    $text = getLogText('departApplyApproverLogText', array('project_name' => $plansInfo['project_name'], 'is_adopt' => I('POST.is_adopt')));
                    $this->addLog('purchases_depart_apply', $res['lastSql'], $text, $plansInfo['apply_id'], '');
                }
                return $res;
            } else {
                return array('status' => -1, 'msg' => '请等待审批！');
            }
        } else {
            return array('status' => -1, 'msg' => '审核已结束！');
        }
    }

    /**
     * Notes: 生成招标记录
     * @param $applyInfo array 科室申请单信息
     * @return array
     */
    public function createTenderRecord($applyInfo)
    {
        $assets = $this->getDepartApplyAssets($applyInfo['apply_id']);
        $data = [];
        foreach ($assets as $k => $v) {
            $data[$k]['apply_id'] = $applyInfo['apply_id'];
            $data[$k]['hospital_id'] = $applyInfo['hospital_id'];
            $data[$k]['apply_num'] = $applyInfo['apply_num'];
            $data[$k]['apply_type'] = $applyInfo['apply_type'];
            $data[$k]['apply_departid'] = $applyInfo['apply_departid'];
            $data[$k]['apply_user'] = $applyInfo['apply_user'];
            $data[$k]['apply_time'] = $applyInfo['apply_time'];
            $data[$k]['project_name'] = $applyInfo['project_name'];
            $data[$k]['assets_id'] = $v['assets_id'];
            $data[$k]['assets_name'] = $v['assets_name'];
            $data[$k]['nums'] = $v['nums'];
            $data[$k]['unit'] = $v['unit'];
            $data[$k]['brand'] = $v['brand'];
            $data[$k]['market_price'] = $v['market_price'];
            $data[$k]['total_budget'] = $v['total_price'];
            $data[$k]['is_import'] = $v['is_import'];
            $data[$k]['buy_type'] = $v['buy_type'];
            $data[$k]['add_user'] = session('username');
            $data[$k]['add_time'] = date('Y-m-d H:i:s');
            $data[$k]['record_from'] = 0;
        }
        $this->insertDataALL('purchases_tender_record', $data);
    }

    /**
     * Notes: 获取采购计划中的项目名称
     */
    public function getPurPlansProjects()
    {
        return $this->DB_get_all('purchases_plans', 'project_name', array('hospital_id' => session('current_hospitalid'), 'is_delete' => C('NO_STATUS')), 'project_name');
    }

    /**
     * Notes: 获取科室申请的项目名称
     */
    public function getAllDepartProjects()
    {
        return $this->DB_get_all('purchases_depart_apply', 'project_name', array('hospital_id' => session('current_hospitalid'), 'is_delete' => C('NO_STATUS')), 'project_name');
    }

    /**
     * Notes: 获取招标记录列表
     */
    public function getTenderLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'record_id';
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $departids = I('post.departids');
        $buy_type = I('post.buy_type');
        $project_name = I('post.project_name');

        $apply_date_start = I('post.apply_date_start');
        $apply_date_end = I('post.apply_date_end');
        $tender_status = I('post.tender_status');

        $hospital_id = I('post.hospital_id');

        $where['is_delete'] = array('eq', C('NO_STATUS'));

        if ($departids) {
            //计划科室搜索
            $where['apply_departid'] = array(array('in', $departids), array('in', session('departid')), 'and');
        } else {
            $where['apply_departid'] = array('in', session('departid'));
        }
        if ($project_name) {
            //项目名称搜索
            $where['project_name'] = array('like', "%$project_name%");
        }
        if ($buy_type != '') {
            //购置类型搜索
            $where['buy_type'] = $buy_type;
        }

        if ($tender_status != '') {
            //招标处理状态
            $where['tender_status'] = $tender_status;
        }
        if ($apply_date_start && !$apply_date_end) {
            $where['apply_time'] = array('egt', $apply_date_start . ' 00:00:01');
        }
        if ($apply_date_end && !$apply_date_start) {
            $where['apply_time'] = array('elt', $apply_date_end . ' 23:59:59');
        }
        if ($apply_date_start && $apply_date_end) {
            if ($apply_date_start > $apply_date_end) {
                return array('status' => -1, 'msg' => '请输入合理的搜索日期！');
            }
            $where['apply_time'] = array(array('egt', $apply_date_start . ' 00:00:01'), array('elt', $apply_date_end . ' 23:59:59'), 'and');
        }
        if ($hospital_id) {
            //医院搜索
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }

        //查询当前用户是否有权限进行招标处理
        $handleTender = get_menu($this->MODULE, 'TenderRecord', 'handleTender');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $total = $this->DB_get_count('purchases_tender_record', $where);
        $plans = $this->DB_get_all('purchases_tender_record', '*', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['apply_departid']]['department'];
            $plans[$k]['apply_date'] = date('Y-m-d', strtotime($v['apply_time']));
            $plans[$k]['buy_type_name'] = $v['buy_type'] == 1 ? '报废更新' : ($v['buy_type'] == 2 ? '添置' : '新增');
            $plans[$k]['apply_type_name'] = $v['apply_type'] == 1 ? '计划内' : '计划外';
            $plans[$k]['is_import_name'] = $v['is_import'] == 1 ? '是' : '否';
            $plans[$k]['tender_status_name'] = $v['tender_status'] == 0 ? '未招标' : ($v['tender_status'] == 1 ? '已招标' : '已确定');
            $html = '<div class="layui-btn-group">';
            if ($v['tender_status'] == 0 && $handleTender) {
                $html .= $this->returnListLink('处理', $handleTender['actionurl'], 'handleTender', C('BTN_CURRENCY') . ' layui-btn');
            } else {
                $html .= $this->returnListLink('查看', get_url() . '?action=showTender&id=' . $v['record_id'], 'showTender', C('BTN_CURRENCY') . ' layui-btn-primary');
            }
            $html .= '</div>';
            $plans[$k]['handle_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取招标记录详情
     * @param $record_id int 记录ID
     */
    public function getTenderInfo($record_id)
    {
        return $this->DB_get_one('purchases_tender_record', '*', array('record_id' => $record_id));
    }

    /**
     * Notes: 保存招标明细
     */
    public function saveDetail($table_detail, $table_detail_file)
    {
        $data['record_id'] = I('post.record_id');
        $data['assets_id'] = I('post.assets_id');
        $data['supplier_id'] = I('post.supplier_id');
        $data['factory_id'] = I('post.factory_id');
        $data['supplier_name'] = I('post.supplier_name');
        $data['factory_name'] = I('post.factory_name');
        $data['brand'] = I('post.brand');
        $data['model'] = I('post.model');
        $data['market_price'] = I('post.market_price');
        $data['company_price'] = I('post.company_price');
        $data['assets_name'] = I('post.assets_name');
        $data['guarantee_year'] = I('post.guarantee_year');
        $data['desc'] = I('post.desc');
        $data['add_user'] = session('username');
        $data['add_time'] = date('Y-m-d H:i:s');
        $res = $this->insertData($table_detail, $data);
        if ($res) {
            if ($table_detail == 'purchases_inquiry_record_detail') {
                $this->updateData('purchases_inquiry_record', array('have_inquiry_record' => 1), array('record_id' => $data['record_id']));
            }
            $file_name = explode(',', trim(I('post.file_name'), ','));
            $file_type = explode(',', trim(I('post.file_type'), ','));
            $save_name = explode(',', trim(I('post.save_name'), ','));
            $file_size = explode(',', trim(I('post.file_size'), ','));
            $file_url = explode(',', trim(I('post.file_url'), ','));
            $file_data = [];
            foreach ($file_name as $k => $v) {
                $file_data[$k]['detail_id'] = $res;
                $file_data[$k]['file_name'] = $v;
                $file_data[$k]['file_type'] = $file_type[$k];
                $file_data[$k]['save_name'] = $save_name[$k];
                $file_data[$k]['file_size'] = $file_size[$k];
                $file_data[$k]['file_url'] = $file_url[$k];
                $file_data[$k]['add_user'] = session('username');
                $file_data[$k]['add_time'] = date('Y-m-d H:i:s');
            }
            $this->insertDataALL($table_detail_file, $file_data);
        } else {
            return array('status' => -1, 'msg' => '添加明细失败！');
        }
        return array('status' => 1, 'msg' => '添加明细成功！');
    }

    /**
     * Notes: 删除招标明细
     */
    public function delDetail($table_detail)
    {
        $detail_id = I('post.detail_id');
        $res = $this->updateData($table_detail, array('is_delete' => C('YES_STATUS')), array('detail_id' => $detail_id));
        if ($res) {
            return array('status' => 1, 'msg' => '删除成功！');
        } else {
            return array('status' => -1, 'msg' => '删除失败！');
        }
    }

    /**
     * Notes: 保存最终选择的供应商
     */
    public function saveFinalSupplierSelect()
    {
        $record_id = I('post.record_id');
        $final_select = I('post.final_select');
        $recordInfo = $this->DB_get_one('purchases_tender_record', 'record_id', array('record_id' => $record_id, 'is_delete' => C('NO_STATUS')));
        if (!$recordInfo) {
            return array('status' => -1, 'msg' => '该招标记录不存在或已被删除！');
        }
        //查询中标供应商提供的设备品牌
        $brand = $this->DB_get_one('purchases_tender_detail', 'brand', array('detail_id' => $final_select));
        $record_update['have_final_supplier'] = 1;
        $record_update['brand'] = $brand['brand'];
        $record_update['tender_status'] = 1;
        $record_update['handle_user'] = session('username');
        $record_update['handle_time'] = date('Y-m-d H:i:s');
        $this->updateData('purchases_tender_record', $record_update, array('record_id' => $record_id));
        $this->updateData('purchases_tender_detail', array('final_select' => 0), array('record_id' => $record_id));
        $res = $this->updateData('purchases_tender_detail', array('final_select' => 1), array('detail_id' => $final_select));
        if ($res) {
            return array('status' => 1, 'msg' => '提交成功！');
        } else {
            return array('status' => -1, 'msg' => '提交失败！');
        }
    }

    /**
     * Notes: 保存询价记录
     */
    public function saveInquiry()
    {
        $record_id = I('post.record_id');
        $final_select = I('post.final_select');
        $recordInfo = $this->DB_get_one('purchases_inquiry_record', 'record_id', array('record_id' => $record_id, 'is_delete' => C('NO_STATUS')));
        if (!$recordInfo) {
            return array('status' => -1, 'msg' => '该招标记录不存在或已被删除！');
        }
        $detailInfo = $this->DB_get_one('purchases_inquiry_record_detail', 'detail_id,supplier_name,factory_name', array('detail_id' => $final_select, 'is_delete' => C('NO_STATUS')));
        if (!$detailInfo) {
            return array('status' => -1, 'msg' => '该询价记录不存在或已被删除！');
        }
        $record_update['have_final_supplier'] = 1;
        $record_update['supplier'] = $detailInfo['supplier_name'];
        $record_update['factory'] = $detailInfo['factory_name'];
        $record_update['handle_user'] = session('username');
        $record_update['handle_time'] = date('Y-m-d H:i:s');
        $this->updateData('purchases_inquiry_record', $record_update, array('record_id' => $record_id));
        $res = $this->updateData('purchases_inquiry_record_detail', array('final_select' => 1), array('detail_id' => $final_select));
        if ($res) {
            return array('status' => 1, 'msg' => '提交成功！');
        } else {
            return array('status' => -1, 'msg' => '提交失败！');
        }
    }

    /**
     * Notes: 获取供应商明细
     * @param $detail_id int 明细ID
     */
    public function getSupplierDetail($table, $detail_id)
    {
        return $this->DB_get_one($table, '*', array('detail_id' => $detail_id));
    }

    /**
     * Notes: 获取供应商附近信息
     * @param $detail_id int 明细ID
     */
    public function getSupplierFiles($table, $detail_id)
    {
        return $this->DB_get_all($table, '*', array('detail_id' => $detail_id, 'is_delete' => C('NO_STATUS')));
    }

    /**
     * Notes: 获取专家评审列表
     * @return mixed
     */
    public function getExpertReviewList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'review_id';
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $departids = I('post.departids');
        $apply_type = I('post.apply_type');
        $project_name = I('post.project_name');

        $apply_date_start = I('post.reviewStartDate');
        $apply_date_end = I('post.reviewEendDate');
        $review_status = I('post.review_status');

        $hospital_id = I('post.hospital_id');

        $where['B.is_delete'] = array('eq', C('NO_STATUS'));

        if ($departids) {
            //计划科室搜索
            $where['B.apply_departid'] = array(array('in', $departids), array('in', session('departid')), 'and');
        } else {
            $where['B.apply_departid'] = array('in', session('departid'));
        }
        if ($project_name) {
            //项目名称搜索
            $where['B.project_name'] = array('like', "%$project_name%");
        }
        if ($apply_type != '') {
            //申请方式搜索
            $where['B.apply_type'] = $apply_type;
        }

        if ($review_status != '') {
            //招标处理状态
            $where['A.review_status'] = $review_status;
        }
        if ($apply_date_start && !$apply_date_end) {
            $where['B.apply_time'] = array('egt', $apply_date_start . ' 00:00:01');
        }
        if ($apply_date_end && !$apply_date_start) {
            $where['B.apply_time'] = array('elt', $apply_date_end . ' 23:59:59');
        }
        if ($apply_date_start && $apply_date_end) {
            if ($apply_date_start > $apply_date_end) {
                return array('status' => -1, 'msg' => '请输入合理的搜索日期！');
            }
            $where['B.apply_time'] = array(array('egt', $apply_date_start . ' 00:00:01'), array('elt', $apply_date_end . ' 23:59:59'), 'and');
        }
        if ($hospital_id) {
            //医院搜索
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }

        //查询当前用户是否有权限进行专家评审
        $expertReview = get_menu($this->MODULE, 'Tendering', 'expertReview');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $join = "LEFT JOIN sb_purchases_depart_apply AS B ON A.apply_id = B.apply_id";
        $fields = "A.review_id,A.review_status,B.*";
        $total = $this->DB_get_count_join('purchases_expert_review', 'A', $join, $where);
        $plans = $this->DB_get_all_join('purchases_expert_review', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['apply_departid']]['department'];
            $plans[$k]['apply_date'] = date('Y-m-d', strtotime($v['apply_time']));
            $plans[$k]['buy_type_name'] = $v['buy_type'] == 1 ? '报废更新' : ($v['buy_type'] == 2 ? '添置' : '新增');
            $plans[$k]['apply_type_name'] = $v['apply_type'] == 1 ? '计划内' : '计划外';
            $plans[$k]['is_import_name'] = $v['is_import'] == 1 ? '是' : '否';
            $plans[$k]['review_status_name'] = $v['review_status'] == 0 ? '未评审' : ($v['review_status'] == 1 ? '已评审' : '');
            $html = '<div class="layui-btn-group">';
            if ($v['review_status'] == 0) {
                $html .= $this->returnListLink('评审', $expertReview['actionurl'], 'expertReview', C('BTN_CURRENCY') . ' layui-btn');
            } else {
                $html .= $this->returnListLink('查看', get_url() . '?action=showExpertReview&id=' . $v['record_id'], 'showExpertReview', C('BTN_CURRENCY') . ' layui-btn-primary');
            }
            $html .= '</div>';
            $plans[$k]['review_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 保存专家评审结果
     */
    public function saveExpertReview()
    {
        $review_id = I('post.review_id');
        $reviewInfo = $this->getExpertReviewInfo($review_id);
        if (!$reviewInfo) {
            return array('status' => -1, 'msg' => '查找不到评审信息！');
        }
        if ($reviewInfo['review_status'] == C('YES_STATUS')) {
            return array('status' => -1, 'msg' => '请勿重复评审！');
        }
        $items = ['installation', 'business', 'rationality', 'technical', 'benefit', 'necessity', 'repair', 'safety', 'matching', 'reliability'];
        foreach ($items as $k => $v) {
            if (!$_POST[$v]) {
                return array('status' => -1, 'msg' => '请对所有项目进行评审！');
            }
        }
        $data = [];
        $data['expert_name'] = session('username');
        $data['review_time'] = date('Y-m-d H:i:s');
        $data['score'] = 0;
        $data['review_status'] = C('YES_STATUS');
        $data['project_desc'] = $_POST['project_desc'];
        $data['technical_desc'] = $_POST['technical_desc'];
        unset($_POST['project_desc']);
        unset($_POST['technical_desc']);
        unset($_POST['review_id']);
        foreach ($_POST as $k => $v) {
            $data[$k] = $v;
            $data['score'] += $v * 2.5;
        }
        $res = $this->updateData('purchases_expert_review', $data, array('review_id' => $review_id));
        if ($res) {
            //获取申请单信息，生成询价记录
            $applyInfo = $this->getDepartApplyInfo($reviewInfo['apply_id']);
            $assets = $this->getDepartApplyAssets($reviewInfo['apply_id']);
            $inqu_data = [];
            foreach ($assets as $k => $v) {
                $inqu_data[$k]['apply_id'] = $applyInfo['apply_id'];
                $inqu_data[$k]['hospital_id'] = $applyInfo['hospital_id'];
                $inqu_data[$k]['apply_num'] = $applyInfo['apply_num'];
                $inqu_data[$k]['project_name'] = $applyInfo['project_name'];
                $inqu_data[$k]['apply_type'] = $applyInfo['apply_type'];
                $inqu_data[$k]['apply_departid'] = $applyInfo['apply_departid'];
                $inqu_data[$k]['apply_user'] = $applyInfo['apply_user'];
                $inqu_data[$k]['apply_time'] = $applyInfo['apply_time'];
                $inqu_data[$k]['assets_id'] = $v['assets_id'];
                $inqu_data[$k]['assets_name'] = $v['assets_name'];
                $inqu_data[$k]['unit'] = $v['unit'];
                $inqu_data[$k]['nums'] = $v['nums'];
                $inqu_data[$k]['market_price'] = $v['market_price'];
                $inqu_data[$k]['total_price'] = $v['total_price'];
                $inqu_data[$k]['brand'] = $v['brand'];
                $inqu_data[$k]['buy_type'] = $v['buy_type'];
                $inqu_data[$k]['is_import'] = $v['is_import'];
                $inqu_data[$k]['add_user'] = session('username');
                $inqu_data[$k]['add_time'] = date('Y-m-d H:i:s');
            }
            if ($inqu_data) {
                $this->insertDataALL('purchases_inquiry_record', $inqu_data);
            }
            return array('status' => 1, 'msg' => '评审成功！');
        } else {
            return array('status' => -1, 'msg' => '评审结果保存失败！');
        }
    }

    /**
     * Notes: 获取专家评审信息
     */
    public function getExpertReviewInfo($review_info)
    {
        return $this->DB_get_one('purchases_expert_review', '*', array('review_id' => $review_info));
    }

    /**
     * Notes: 获取询价记录列表
     * @return mixed
     */
    public function getInquiryPricesList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'record_id';
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $departids = I('post.departids');
        $assets_name = I('post.assets_name');
        $have_inquiry_record = I('post.inquiry_record');
        $have_final_supplier = I('post.final_supplier');
        $supplier = I('post.supplier');
        $factory = I('post.factory');

        $hospital_id = I('post.hospital_id');

        $where['is_delete'] = array('eq', C('NO_STATUS'));

        if ($departids) {
            //计划科室搜索
            $where['apply_departid'] = array(array('in', $departids), array('in', session('departid')), 'and');
        } else {
            $where['apply_departid'] = array('in', session('departid'));
        }
        if ($assets_name) {
            //设备名称搜索
            $where['assets_name'] = array('like', "%$assets_name%");
        }
        if ($supplier) {
            //供应商
            $where['supplier'] = array('like', "%$supplier%");
        }
        if ($factory) {
            //生产厂家
            $where['factory'] = array('like', "%$factory%");
        }
        if ($have_inquiry_record != '') {
            //是否有询价记录
            $where['have_inquiry_record'] = $have_inquiry_record;
        }
        if ($have_final_supplier != '') {
            //是否有初步确认供货
            $where['have_final_supplier'] = $have_final_supplier;
        }
        if ($hospital_id) {
            //医院搜索
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }

        //查询当前用户是否有权限添加询价记录
        $inquiryPrices = get_menu($this->MODULE, 'Tendering', 'inquiryPrices');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $total = $this->DB_get_count('purchases_inquiry_record', $where);
        $plans = $this->DB_get_all('purchases_inquiry_record', '', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['apply_departid']]['department'];
            $plans[$k]['apply_date'] = date('Y-m-d', strtotime($v['apply_time']));
            $plans[$k]['buy_type_name'] = $v['buy_type'] == 1 ? '报废更新' : ($v['buy_type'] == 2 ? '添置' : '新增');
            $plans[$k]['apply_type_name'] = $v['apply_type'] == 1 ? '计划内' : '计划外';
            $plans[$k]['is_import_name'] = $v['is_import'] == 1 ? '是' : '否';
            $plans[$k]['have_inquiry_record_name'] = $v['have_inquiry_record'] == 1 ? '有' : '无';
            $plans[$k]['have_final_supplier_name'] = $v['have_final_supplier'] == 1 ? '是' : '否';
            $html = '<div class="layui-btn-group">';
            if ($v['have_final_supplier'] == 0) {
                $html .= $this->returnListLink('询价', $inquiryPrices['actionurl'], 'inquiryPrices', C('BTN_CURRENCY') . ' layui-btn');
            } else {
                $html .= $this->returnListLink('查看', get_url() . '?action=showInquiryPrices&id=' . $v['record_id'], 'showInquiryPrices', C('BTN_CURRENCY') . ' layui-btn-primary');
            }
            $html .= '</div>';
            $plans[$k]['inquiry_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取询价记录详情
     * @param $record_id int 询价记录ID
     */
    public function getInquiryRecordInfo($record_id)
    {
        return $this->DB_get_one('purchases_inquiry_record', '*', array('record_id' => $record_id));
    }

    /**
     * Notes: 获取询价记录明细
     * @param $record_id int 记录ID
     */
    public function getInquiryDetail($record_id)
    {
        return $this->DB_get_all('purchases_inquiry_record_detail', '*', array('record_id' => $record_id, 'is_delete' => C('NO_STATUS')));
    }

    /**
     * Notes: 获取制定标书列表
     * @return mixed
     */
    public function getTenderingBookList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'record_id';
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $departids = I('post.departids');
        $assets_name = I('post.assets_name');
        $buy_type = I('post.buy_type');
        $tender_status = I('post.tender_status');
        $apply_date_start = I('post.apply_date_start');
        $apply_date_end = I('post.apply_date_end');
        $hospital_id = I('post.hospital_id');

        $where['is_delete'] = array('eq', C('NO_STATUS'));
        $where['have_final_supplier'] = 1;

        if ($departids) {
            //计划科室搜索
            $where['apply_departid'] = array(array('in', $departids), array('in', session('departid')), 'and');
        } else {
            $where['apply_departid'] = array('in', session('departid'));
        }
        if ($assets_name) {
            //设备名称搜索
            $where['assets_name'] = array('like', "%$assets_name%");
        }
        if ($buy_type != '') {
            //购置类型
            $where['buy_type'] = $buy_type;
        }
        if ($tender_status != '') {
            //标书状态
            $where['tender_status'] = $tender_status;
        }
        if ($hospital_id) {
            //医院搜索
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }
        if ($apply_date_start && !$apply_date_end) {
            $where['apply_time'] = array('egt', $apply_date_start . ' 00:00:01');
        }
        if ($apply_date_end && !$apply_date_start) {
            $where['apply_time'] = array('elt', $apply_date_end . ' 23:59:59');
        }
        if ($apply_date_start && $apply_date_end) {
            if ($apply_date_start > $apply_date_end) {
                return array('status' => -1, 'msg' => '请输入合理的搜索日期！');
            }
            $where['apply_time'] = array(array('egt', $apply_date_start . ' 00:00:01'), array('elt', $apply_date_end . ' 23:59:59'), 'and');
        }

        //查询当前用户是否有权限制定标书
        $addTenderingBook = get_menu($this->MODULE, 'Tendering', 'addTenderingBook');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $total = $this->DB_get_count('purchases_inquiry_record', $where);
        $plans = $this->DB_get_all('purchases_inquiry_record', '', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['apply_departid']]['department'];
            $plans[$k]['apply_date'] = date('Y-m-d', strtotime($v['apply_time']));
            $plans[$k]['buy_type_name'] = $v['buy_type'] == 1 ? '报废更新' : ($v['buy_type'] == 2 ? '添置' : '新增');
            $plans[$k]['apply_type_name'] = $v['apply_type'] == 1 ? '计划内' : '计划外';
            $plans[$k]['is_import_name'] = $v['is_import'] == 1 ? '是' : '否';
            $plans[$k]['tender_status_name'] = $v['tender_status'] == 1 ? '已提交' : ($v['tender_status'] == 2 ? '已通过' : ($v['tender_status'] == 3 ? '已退回' : '未提交'));
            $html = '<div class="layui-btn-group">';
            if ($v['tender_status'] == 0 || $v['tender_status'] == 3) {
                $html .= $this->returnListLink('处理', $addTenderingBook['actionurl'], 'addTenderingBook', C('BTN_CURRENCY') . ' layui-btn');
            } else {
                $html .= $this->returnListLink('查看', get_url() . '?action=showTenderingBook&id=' . $v['record_id'], 'showTenderingBook', C('BTN_CURRENCY') . ' layui-btn-primary');
            }
            $html .= '</div>';
            $plans[$k]['book_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 查询标书记录
     * @param $record_id
     */
    public function getReviewFile($record_id)
    {
        return $this->DB_get_all('purchases_tender_review_file', '*', array('record_id' => $record_id));
    }

    /**
     * Notes: 保存标书文件
     */
    public function saveBookFile()
    {
        $record_id = I('post.record_id');
        $username = session('username');
        $now = date('Y-m-d H:i:s');
        //查询是否已生成标书评审记录
        $exists = $this->DB_get_one('purchases_tender_review', 'rev_id', array('record_id' => $record_id));
        if ($exists['rev_id']) {
            //修改评审状态为未评审
            $this->updateData('purchases_tender_review', array('review_status' => 0), array('record_id' => $record_id));
        } else {
            $this->insertData('purchases_tender_review', array('record_id' => $record_id, 'add_user' => $username, 'add_time' => $now));
        }
        $data['record_id'] = $record_id;
        $data['file_name'] = I('post.file_name');
        $data['file_type'] = I('post.file_type');
        $data['save_name'] = I('post.save_name');
        $data['file_size'] = trim(I('post.file_size'), 'M');
        $data['file_url'] = I('post.file_url');
        $data['is_pass'] = 0;
        $data['add_user'] = $username;
        $data['add_time'] = $now;
        $res = $this->insertData('purchases_tender_review_file', $data);
        if ($res) {
            //修改提交状态
            $this->updateData('purchases_inquiry_record', array('tender_status' => 1), array('record_id' => $record_id));
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->MODULE);
            if ($settingData) {
                //有开启短信
                $recordInfo = $this->getInquiryRecordInfo($record_id);
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $recordInfo['department'] = $departname[$recordInfo['apply_departid']]['department'];
                $recordInfo['assets'] =$recordInfo['assets_name'];
                $ToolMod = new ToolController();
                //通知审批人审批
                $userData=$ToolMod->getUser('tbApprove',$recordInfo['apply_departid']);
                if ($settingData['tbApprove']['status'] == C('OPEN_STATUS') && $userData) {
                    $sms = $this->formatSmsContent($settingData['tbApprove']['content'], $recordInfo);
                    $phone=$this->formatPhone($userData);
                    $ToolMod->sendingSMS($phone, $sms, $this->MODULE, $record_id);
                }
            }
            //==========================================短信 END==========================================

            return array('status' => 1, 'msg' => '提交成功！');
        } else {
            return array('status' => -1, 'msg' => '提交失败！');
        }
    }

    /**
     * Notes: 获取标书审批、提交列表
     * @return mixed
     */
    public function getTbApproveList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'rev_id';
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $departids = I('post.departids');
        $assets_name = I('post.assets_name');
        $buy_type = I('post.buy_type');
        $review_status = I('post.review_status');
        $submit_status = I('post.submit_status');
        $apply_date_start = I('post.apply_date_start');
        $apply_date_end = I('post.apply_date_end');
        $hospital_id = I('post.hospital_id');
        $list_type = I('post.list_type');
        if ($list_type == 'tbSubmit') {
            $where['A.review_status'] = 1;
        }
        $where['B.is_delete'] = array('eq', C('NO_STATUS'));

        if ($departids) {
            //计划科室搜索
            $where['B.apply_departid'] = array(array('in', $departids), array('in', session('departid')), 'and');
        } else {
            $where['B.apply_departid'] = array('in', session('departid'));
        }
        if ($assets_name) {
            //设备名称搜索
            $where['B.assets_name'] = array('like', "%$assets_name%");
        }
        if ($buy_type != '') {
            //购置类型
            $where['B.buy_type'] = $buy_type;
        }
        if ($review_status != '') {
            //评审状态
            $where['A.review_status'] = $review_status;
        }
        if ($submit_status != '') {
            //提交状态
            $where['A.submit_status'] = $submit_status;
        }
        if ($hospital_id) {
            //医院搜索
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($apply_date_start && !$apply_date_end) {
            $where['B.apply_time'] = array('egt', $apply_date_start . ' 00:00:01');
        }
        if ($apply_date_end && !$apply_date_start) {
            $where['B.apply_time'] = array('elt', $apply_date_end . ' 23:59:59');
        }
        if ($apply_date_start && $apply_date_end) {
            if ($apply_date_start > $apply_date_end) {
                return array('status' => -1, 'msg' => '请输入合理的搜索日期！');
            }
            $where['B.apply_time'] = array(array('egt', $apply_date_start . ' 00:00:01'), array('elt', $apply_date_end . ' 23:59:59'), 'and');
        }
        $field = "A.*,B.*";
        $join = "LEFT JOIN sb_purchases_inquiry_record as B ON A.record_id = B.record_id";
        //查询当前用户是否有权限评审标书
        $tbApprove = get_menu($this->MODULE, 'Tendering', 'tbApprove');
        $tbSubmit = get_menu($this->MODULE, 'Tendering', 'tbSubmit');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $total = $this->DB_get_count_join('purchases_tender_review', 'A', $join, $where);
        $plans = $this->DB_get_all_join('purchases_tender_review', 'A', $field, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['apply_departid']]['department'];
            $plans[$k]['apply_date'] = date('Y-m-d', strtotime($v['apply_time']));
            $plans[$k]['buy_type_name'] = $v['buy_type'] == 1 ? '报废更新' : ($v['buy_type'] == 2 ? '添置' : '新增');
            $plans[$k]['apply_type_name'] = $v['apply_type'] == 1 ? '计划内' : '计划外';
            $plans[$k]['is_import_name'] = $v['is_import'] == 1 ? '是' : '否';
            $plans[$k]['review_status_name'] = $v['review_status'] == 1 ? '已通过' : ($v['review_status'] == 2 ? '已退回' : '未评审');
            $plans[$k]['submit_status_name'] = $v['submit_status'] == 1 ? '已提交' : '未提交';
            if ($list_type == 'tbSubmit') {
                $html = '<div class="layui-btn-group">';
                if ($v['submit_status'] == 0) {
                    $html .= $this->returnListLink('提交', $tbSubmit['actionurl'], 'tbSubmit', C('BTN_CURRENCY') . ' layui-btn');
                } else {
                    $html .= $this->returnListLink('查看', get_url() . '?action=showTbSubmit&id=' . $v['rev_id'], 'showTbSubmit', C('BTN_CURRENCY') . ' layui-btn-primary');
                }
                $html .= '</div>';
                $plans[$k]['sub_operation'] = $html;
            } else {
                $html = '<div class="layui-btn-group">';
                if ($v['review_status'] == 0) {
                    $html .= $this->returnListLink('评审', $tbApprove['actionurl'], 'tbApprove', C('BTN_CURRENCY') . ' layui-btn');
                } else {
                    $html .= $this->returnListLink('查看', get_url() . '?action=showTbApprove&id=' . $v['record_id'], 'showTbApprove', C('BTN_CURRENCY') . ' layui-btn-primary');
                }
                $html .= '</div>';
                $plans[$k]['tb_operation'] = $html;
            }
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 保存标书评审结果
     */
    public function saveReviewApprove()
    {
        $rev_id = I('post.rev_id');
        $record_id = I('post.record_id');
        $rev_data['review_user'] = session('username');
        $rev_data['review_time'] = date('Y-m-d H:i:s');
        $rev_data['review_status'] = I('post.review_status');
        $res = $this->updateData('purchases_tender_review', $rev_data, array('rev_id' => $rev_id));
        if ($res) {
            $render_status = $rev_data['review_status'] == 1 ? 2 : 3;
            $this->updateData('purchases_inquiry_record', array('tender_status' => $render_status), array('record_id' => $record_id));
            $this->updateData('purchases_tender_review_file', array('is_pass' => $rev_data['review_status'], 'desc' => I('post.desc')), array('record_id' => $record_id, 'is_pass' => 0));
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->MODULE);
            if ($settingData) {
                //有开启短信
                $recordInfo = $this->getInquiryRecordInfo($record_id);
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $recordInfo['department'] = $departname[$recordInfo['apply_departid']]['department'];
                $recordInfo['assets'] =$recordInfo['assets_name'];
                $recordInfo['review_status'] =$rev_data['review_status']==C('SUCCESS_STATUS')?'已通过':'驳回';
                $ToolMod = new ToolController();
                if($rev_data['review_status']==C('SUCCESS_STATUS')){
                    //通知提交标书
                    $userData=$ToolMod->getUser('tbSubmit',$recordInfo['apply_departid']);
                    if ($settingData['tbSubmit']['status'] == C('OPEN_STATUS') && $userData) {
                        $sms = $this->formatSmsContent($settingData['tbSubmit']['content'], $recordInfo);
                        $phone=$this->formatPhone($userData);
                        $ToolMod->sendingSMS($phone, $sms, $this->MODULE, $record_id);
                    }
                }else{
                    //通知标书被驳回
                    $userData=$ToolMod->getUser('addTenderingBook',$recordInfo['apply_departid']);
                    if ($settingData['tbApproveOver']['status'] == C('OPEN_STATUS') && $userData) {
                        $sms = $this->formatSmsContent($settingData['tbApproveOver']['content'], $recordInfo);
                        $phone=$this->formatPhone($userData);
                        $ToolMod->sendingSMS($phone, $sms, $this->MODULE, $record_id);
                    }
                }
            }
            //==========================================短信 END==========================================
            return array('status' => 1, 'msg' => '提交成功！');
        } else {
            return array('status' => -1, 'msg' => '提交失败！');
        }
    }

    /**
     * Notes: 保存标书提交信息
     */
    public function saveReviewSubmit()
    {
        $rev_id = I('post.rev_id');
        $revInfo = $this->DB_get_one('purchases_tender_review', 'record_id', array('rev_id' => $rev_id));
        if (!$revInfo) {
            return array('status' => -1, 'msg' => '查找不到标书信息！');
        }
        $recordInfo = $this->DB_get_one('purchases_inquiry_record', '*', array('record_id' => $revInfo['record_id']));
        $data['submit_user'] = session('username');
        $data['submit_time'] = date('Y-m-d H:i:s');
        $data['submit_status'] = 1;
        $res = $this->updateData('purchases_tender_review', $data, array('rev_id' => $rev_id));
        if ($res) {
            //写入招标记录
            $tender_data['hospital_id'] = $recordInfo['hospital_id'];
            $tender_data['apply_id'] = $recordInfo['apply_id'];
            $tender_data['apply_num'] = $recordInfo['apply_num'];
            $tender_data['apply_type'] = $recordInfo['apply_type'];
            $tender_data['apply_departid'] = $recordInfo['apply_departid'];
            $tender_data['apply_user'] = $recordInfo['apply_user'];
            $tender_data['apply_time'] = $recordInfo['apply_time'];
            $tender_data['project_name'] = $recordInfo['project_name'];
            $tender_data['assets_id'] = $recordInfo['assets_id'];
            $tender_data['assets_name'] = $recordInfo['assets_name'];
            $tender_data['nums'] = $recordInfo['nums'];
            $tender_data['unit'] = $recordInfo['unit'];
            $tender_data['brand'] = $recordInfo['brand'];
            $tender_data['market_price'] = $recordInfo['market_price'];
            $tender_data['total_budget'] = $recordInfo['total_price'];
            $tender_data['is_import'] = $recordInfo['is_import'];
            $tender_data['buy_type'] = $recordInfo['buy_type'];
            $tender_data['tender_status'] = 0;
            $tender_data['add_user'] = session('username');
            $tender_data['add_time'] = date('Y-m-d H:i:s');
            $tender_data['record_from'] = 1;
            $new_id = $this->insertData('purchases_tender_record', $tender_data);
            //写入明细和文件信息
            $details = $this->DB_get_all('purchases_inquiry_record_detail', '*', array('record_id' => $recordInfo['record_id'], 'is_delete' => C('NO_STATUS')));
            foreach ($details as $k => $v) {
                $detail_data['record_id'] = $new_id;
                $detail_data['supplier_id'] = $v['supplier_id'];
                $detail_data['supplier_name'] = $v['supplier_name'];
                $detail_data['factory_id'] = $v['factory_id'];
                $detail_data['factory_name'] = $v['factory_name'];
                $detail_data['assets_id'] = $v['assets_id'];
                $detail_data['assets_name'] = $v['assets_name'];
                $detail_data['model'] = $v['model'];
                $detail_data['brand'] = $v['brand'];
                $detail_data['market_price'] = $v['market_price'];
                $detail_data['company_price'] = $v['company_price'];
                $detail_data['guarantee_year'] = $v['guarantee_year'];
                $detail_data['desc'] = $v['desc'];
                $detail_data['final_select'] = $v['final_select'];
                $detail_data['add_user'] = $v['add_user'];
                $detail_data['add_time'] = $v['add_time'];
                $detail_id = $this->insertData('purchases_tender_detail', $detail_data);
                //查询原附件
                $files = $this->DB_get_all('purchases_inquiry_record_detail_file', '*', array('detail_id' => $v['detail_id'], 'is_delete' => C('NO_STATUS')));
                foreach ($files as $k1 => $v1) {
                    $file_data['detail_id'] = $detail_id;
                    $file_data['file_name'] = $v1['file_name'];
                    $file_data['save_name'] = $v1['save_name'];
                    $file_data['file_type'] = $v1['file_type'];
                    $file_data['file_size'] = $v1['file_size'];
                    $file_data['file_url'] = $v1['file_url'];
                    $file_data['add_user'] = $v1['add_user'];
                    $file_data['add_time'] = $v1['add_time'];
                    $this->insertData('purchases_tender_detail_file', $file_data);
                }
            }
            return array('status' => 1, 'msg' => '提交成功！');
        } else {
            return array('status' => -1, 'msg' => '提交失败！');
        }
    }

    /**
     * Notes: 获取标书评审记录
     * @param $record_id
     * @return mixed
     */
    public function getReviewApproves($record_id)
    {
        return $this->DB_get_all('purchases_tender_review_file', '*', array('record_id' => $record_id), '', 'file_id asc');
    }

    /**
     * Notes: 获取项目结果
     */
    public function resultLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'review_id';
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $departids = I('post.departids');
        $project_name = I('post.project_name');
        $apply_type = I('post.apply_type');
        $review_status = I('post.review_status');

        $apply_date_start = I('post.apply_date_start');
        $apply_date_end = I('post.apply_date_end');
        $hospital_id = I('post.hospital_id');

        if ($departids) {
            //计划科室搜索
            $where['B.apply_departid'] = array(array('in', $departids), array('in', session('departid')), 'and');
        } else {
            $where['B.apply_departid'] = array('in', session('departid'));
        }
        if ($project_name) {
            //项目名称搜索
            $where['B.project_name'] = array('like', "%$project_name%");
        }
        if ($apply_type != '') {
            //申请方式
            $where['B.apply_type'] = $apply_type;
        }
        if ($review_status != '') {
            //评审状态
            $where['A.review_status'] = $review_status;
        }
        if ($hospital_id) {
            //医院搜索
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($apply_date_start && !$apply_date_end) {
            $where['B.apply_time'] = array('egt', $apply_date_start . ' 00:00:01');
        }
        if ($apply_date_end && !$apply_date_start) {
            $where['B.apply_time'] = array('elt', $apply_date_end . ' 23:59:59');
        }
        if ($apply_date_start && $apply_date_end) {
            if ($apply_date_start > $apply_date_end) {
                return array('status' => -1, 'msg' => '请输入合理的搜索日期！');
            }
            $where['B.apply_time'] = array(array('egt', $apply_date_start . ' 00:00:01'), array('elt', $apply_date_end . ' 23:59:59'), 'and');
        }
        $field = "A.*,B.*";
        $join = "LEFT JOIN sb_purchases_depart_apply as B ON A.apply_id = B.apply_id";
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $total = $this->DB_get_count_join('purchases_expert_review', 'A', $join, $where);
        $plans = $this->DB_get_all_join('purchases_expert_review', 'A', $field, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['apply_departid']]['department'];
            $plans[$k]['apply_date'] = date('Y-m-d', strtotime($v['apply_time']));
            $plans[$k]['buy_type_name'] = $v['buy_type'] == 1 ? '报废更新' : ($v['buy_type'] == 2 ? '添置' : '新增');
            $plans[$k]['apply_type_name'] = $v['apply_type'] == 1 ? '计划内' : '计划外';
            $plans[$k]['is_import_name'] = $v['is_import'] == 1 ? '是' : '否';
            $plans[$k]['review_status_name'] = $v['review_status'] == 1 ? '已评审' : '未评审';
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取可入库设备列表
     */
    public function getCheckedLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'check_date';
        $sup_id = I('post.sup_id');
        $where['hospital_id'] = session('current_hospitalid');
        $where['is_check'] = C('YES_STATUS');
        $where['is_ware'] = array('in', "0,3");
        if (!$sup_id) {
            $result['total'] = 0;
            $result["offset"] = 0;
            $result["limit"] = 10;
            $result["code"] = 200;
            $result['rows'] = [];
            if (!$result['rows']) {
                $result['msg'] = '请先选择供应商';
                $result['code'] = 400;
            }
            return $result;
        }
        $where['supplier_id'] = $sup_id;
        $catname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        $total = $this->DB_get_count('purchases_depart_apply_assets', $where);
        $plans = $this->DB_get_all('purchases_depart_apply_assets', 'assets_id,assets_name,model,factory,factorynum,catid,check_date,unit,nums,market_price,buy_price,total_price', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['category'] = $catname[$v['catid']]['category'];
            $plans[$k]['real_total'] = $v['nums'] * $v['buy_price'];
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 保存入库设备
     */
    public function saveWare()
    {
        $assets_ids = I('post.assets_ids');
        $in_data['in_desc'] = I('post.in_desc');
        $in_data['in_date'] = I('post.in_date');
        $assets = $this->DB_get_all('purchases_depart_apply_assets', '*', array('assets_id' => array('in', $assets_ids)));
        //生成入库单
        $in_data['hospital_id'] = $assets[0]['hospital_id'];
        $code = rand(10000, 99999);
        $in_data['in_num'] = 'SW-RK' . date('ym') . $code;
        $in_data['in_user'] = session('username');
        $in_data['add_user'] = session('username');
        $in_data['supplier_id'] = $assets[0]['supplier_id'];
        $in_data['supplier'] = $assets[0]['supplier'];
        $in_data['add_time'] = date('Y-m-d H:i:s');
        $in_id = $this->insertData('purchases_in_warehouse', $in_data);
        $total_nums = $total_price = 0;
        foreach ($assets as $k => $v) {
            $total_nums += $v['nums'];
            $total_price += (float)$v['buy_price'] * $v['nums'];
            $as_data = [];
            for ($i = 0; $i < $v['nums']; $i++) {
                $code = rand(10000, 99999);
                $as_data[$i]['in_id'] = $in_id;
                $as_data[$i]['hospital_id'] = $v['hospital_id'];
                $as_data[$i]['contract_id'] = $v['contract_id'];
                $as_data[$i]['apply_id'] = $v['apply_id'];
                $as_data[$i]['assets_id'] = $v['assets_id'];
                $as_data[$i]['assets_num'] = 'RK-AS' . date('ym' . $code);
                $as_data[$i]['assets_name'] = $v['assets_name'];
                $as_data[$i]['model'] = $v['model'];
                $as_data[$i]['unit'] = $v['unit'];
                $as_data[$i]['is_import'] = $v['is_import'];
                $as_data[$i]['buy_type'] = $v['buy_type'];
                $as_data[$i]['supplier_id'] = $v['supplier_id'];
                $as_data[$i]['supplier'] = $v['supplier'];
                $as_data[$i]['factory_id'] = $v['factory_id'];
                $as_data[$i]['factory'] = $v['factory'];
                $facnum = explode(',', $v['factorynum']);
                if (!is_array($facnum[0])) {
                    $facnum = explode(';', $v['factorynum']);
                }
                $sernum = explode(',', $v['serialnum']);
                if (!is_array($sernum[0])) {
                    $sernum = explode(';', $v['serialnum']);
                }
                $invonum = explode(',', $v['invoicenum']);
                if (!is_array($invonum[0])) {
                    $invonum = explode(';', $v['invoicenum']);
                }
                $as_data[$i]['factorynum'] = $facnum[$i];
                $as_data[$i]['serialnum'] = $sernum[$i];
                $as_data[$i]['invoicenum'] = $invonum[$i];
                $as_data[$i]['departid'] = $v['departid'];
                $as_data[$i]['catid'] = $v['catid'];
                $as_data[$i]['check_date'] = $v['check_date'];
                $as_data[$i]['buy_price'] = $v['buy_price'];
                $as_data[$i]['is_out'] = 0;
            }
            $this->insertDataALL('purchases_in_warehouse_assets', $as_data);
        }
        $res = $this->updateData('purchases_in_warehouse', array('nums' => $total_nums, 'total_price' => $total_price), array('in_id' => $in_id));
        if ($res) {
            $this->updateData('purchases_depart_apply_assets', array('is_ware' => 1), array('assets_id' => array('in', $assets_ids)));
            return array('status' => 1, 'msg' => '提交成功！');
        } else {
            return array('status' => -1, 'msg' => '提交失败！');
        }
    }

    /**
     * Notes: 获取入库列表
     */
    public function getWareLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'in_id';
        $in_num = I('post.in_num');
        $supplier = I('post.supplier');
        $approve_status = I('post.approve_status');
        $in_date_start = I('post.in_date_start');
        $in_date_end = I('post.in_date_end');
        $where['hospital_id'] = session('current_hospitalid');
        if ($in_num) {
            $where['in_num'] = array('like', "%$in_num%");
        }
        if ($supplier) {
            $where['supplier'] = array('like', "%$supplier%");
        }
        if ($approve_status != '') {
            $where['approve_status'] = $approve_status;
        }
        if ($in_date_start && !$in_date_end) {
            $where['in_date'] = array('egt', $in_date_start);
        }
        if ($in_date_end && !$in_date_start) {
            $where['in_date'] = array('elt', $in_date_end);
        }
        if ($in_date_start && $in_date_end) {
            if ($in_date_start > $in_date_end) {
                return array('status' => -1, 'msg' => '请输入合理的搜索日期！');
            }
            $where['in_date'] = array(array('egt', $in_date_start), array('elt', $in_date_end), 'and');
        }
        //查询当前用户是否有权限审核
        $approveWare = get_menu($this->MODULE, 'PurchaseCheck', 'approveWare');
        $total = $this->DB_get_count('purchases_in_warehouse', $where);
        $plans = $this->DB_get_all('purchases_in_warehouse', '*', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['approve_status_name'] = $v['approve_status'] == 1 ? '已通过' : ($v['approve_status'] == 2 ? '不通过' : '未审核');
            $html = '<div class="layui-btn-group">';
            if ($approveWare && $v['approve_status'] == 0) {
                $html .= '<button class="layui-btn layui-btn-xs" lay-event="approveWare" href="javascript:void(0)" data-url="' . $approveWare['actionurl'] . '">' . $approveWare['actionname'] . '</button>';
            } else {
                $html .= '<button class="layui-btn layui-btn-xs layui-btn-primary" lay-event="showWare" href="javascript:void(0)" data-url="' . get_url() . '?action=showWare&id=' . $v['in_id'] . '">查看</button>';
            }
            $html .= '</div>';
            $plans[$k]['in_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取入库单信息
     */
    public function getInWareInfo($in_id)
    {
        return $this->DB_get_one('purchases_in_warehouse', '*', array('in_id' => $in_id));
    }

    /**
     * Notes: 获取入库设备列表
     * @param $in_id
     */
    public function getInWareAssets($in_id)
    {
        return $this->DB_get_all('purchases_in_warehouse_assets', 'assets_id,assets_name,unit,count(*) as nums,buy_price,sum(buy_price) as total_price,is_import,buy_type', array('in_id' => $in_id), 'assets_id');
    }

    /**
     * Notes: 保存入库审批结果
     */
    public function saveInWareApprove()
    {
        $in_id = I('post.in_id');
        $data['approve_user'] = session('username');
        $data['approve_time'] = date('Y-m-d H:i:s');
        $data['approve_status'] = I('post.approve_status');
        $data['approve_desc'] = I('post.approve_desc');
        $res = $this->updateData('purchases_in_warehouse', $data, array('in_id' => $in_id));
        if ($res) {
            $is_ware = ($data['approve_status'] == 2) ? 3 : 2;
            //修改入库状态为
            $ids = $this->DB_get_one('purchases_in_warehouse_assets', 'group_concat(distinct assets_id) as assetsids', array('in_id' => $in_id));
            $this->updateData('purchases_depart_apply_assets', array('is_ware' => $is_ware), array('assets_id' => array('in', $ids['assetsids'])));
            //审核通过后，修改设备为可用状态
            if ($is_ware == 2) {
                $this->updateData('purchases_in_warehouse_assets', array('can_use' => 1), array('in_id' => $in_id));
            }
            return array('status' => 1, 'msg' => '审批成功！');
        } else {
            return array('status' => -1, 'msg' => '审批失败！');
        }
    }

    /**
     * Notes: 获取可出库设备列表
     * @return mixed
     */
    public function getCanOutLists()
    {
        $departid = I('post.departid');
        $where['hospital_id'] = session('current_hospitalid');
        $where['is_out'] = C('NO_STATUS');//未出库
        $where['can_use'] = C('YES_STATUS');//可用
        if (!$departid) {
            $result["code"] = 200;
            $result['rows'] = [];
            if (!$result['rows']) {
                $result['msg'] = '请先选择领用科室';
                $result['code'] = 400;
            }
            return $result;
        }
        $where['departid'] = $departid;
        $catname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        $plans = $this->DB_get_all('purchases_in_warehouse_assets', '*', $where, '', 'ware_assets_id asc');
        foreach ($plans as $k => $v) {
            $plans[$k]['category'] = $catname[$v['catid']]['category'];
            $plans[$k]['real_total'] = $v['nums'] * $v['buy_price'];
        }
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 保存出库单信息
     */
    public function saveOut()
    {
        $code = rand(10000, 99999);
        $ware_assets_id = I('post.assets_ids');
        $data['hospital_id'] = session('current_hospitalid');
        $data['out_num'] = 'SW-CK' . date('ym') . $code;
        $data['departid'] = I('post.departid');
        $data['out_user'] = session('username');
        $data['out_date'] = I('post.out_date');
        $data['add_user'] = session('username');
        $data['add_time'] = date('Y-m-d H:i:s');
        $out_id = $this->insertData('purchases_out_warehouse', $data);
        if (!$out_id) {
            return array('status' => -1, 'msg' => '提交失败！');
        }
        //记录调试信息
        $debug_data['out_id'] = $out_id;
        $debug_data['debug_desc'] = I('post.debug_desc');
        $debug_data['debug_user'] = I('post.debug_user');
        $debug_data['debug_start_date'] = I('post.installStartDate');
        $debug_data['debug_end_date'] = I('post.installEendDate');
        $debug_data['attendants_user'] = I('post.presence');
        $debug_data['debug_area'] = I('post.debug_area');
        $debug_data['add_user'] = session('username');
        $debug_data['add_time'] = date('Y-m-d H:i:s');
        $this->insertData('purchases_assets_install_debug', $debug_data);

        $assets = $this->DB_get_all('purchases_in_warehouse_assets', '*', array('ware_assets_id' => array('in', $ware_assets_id)));
        $total_nums = $total_price = 0;
        foreach ($assets as $k => $v) {
            $total_nums += 1;
            $total_price += (float)$v['buy_price'];
            $as_data = [];
            $as_data['out_id'] = $out_id;
            $as_data['hospital_id'] = $data['hospital_id'];
            $as_data['contract_id'] = $v['contract_id'];
            $as_data['apply_id'] = $v['apply_id'];
            $as_data['assets_id'] = $v['assets_id'];
            $as_data['assets_num'] = $v['assets_num'];
            $as_data['assets_name'] = $v['assets_name'];
            $as_data['model'] = $v['model'];
            $as_data['unit'] = $v['unit'];
            $as_data['is_import'] = $v['is_import'];
            $as_data['buy_type'] = $v['buy_type'];
            $as_data['supplier_id'] = $v['supplier_id'];
            $as_data['supplier'] = $v['supplier'];
            $as_data['factory_id'] = $v['factory_id'];
            $as_data['factory'] = $v['factory'];
            $as_data['factorynum'] = $v['factorynum'];
            $as_data['serialnum'] = $v['serialnum'];
            $as_data['invoicenum'] = $v['invoicenum'];
            $as_data['departid'] = $v['departid'];
            $as_data['catid'] = $v['catid'];
            $as_data['check_date'] = $v['check_date'];
            $as_data['buy_price'] = $v['buy_price'];
            $as_data['can_use'] = 0;
            $as_data['in_assetsinfo'] = 0;
            $this->insertData('purchases_out_warehouse_assets', $as_data);
            //修改出库状态为审核中
            $this->updateData('purchases_in_warehouse_assets', array('is_out' => 1), array('ware_assets_id' => $v['ware_assets_id']));
        }
        $res = $this->updateData('purchases_out_warehouse', array('nums' => $total_nums, 'total_price' => $total_price), array('out_id' => $out_id));
        if ($res) {
            return array('status' => 1, 'msg' => '提交成功！');
        } else {
            return array('status' => -1, 'msg' => '提交失败！');
        }
    }

    /**
     * Notes: 获取出库设备列表
     */
    public function getOutLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'in_id';
        $out_num = I('post.out_num');
        $departid = I('post.departid');
        $approve_status = I('post.approve_status');
        $out_date_start = I('post.out_date_start');
        $out_date_end = I('post.out_date_end');
        $where['hospital_id'] = session('current_hospitalid');
        if ($out_num) {
            $where['out_num'] = array('like', "%$out_num%");
        }
        if ($departid) {
            $where['departid'] = $departid;
        }
        if ($approve_status != '') {
            $where['approve_status'] = $approve_status;
        }
        if ($out_date_start && !$out_date_end) {
            $where['out_date'] = array('egt', $out_date_start);
        }
        if ($out_date_end && !$out_date_start) {
            $where['out_date'] = array('elt', $out_date_end);
        }
        if ($out_date_start && $out_date_end) {
            if ($out_date_start > $out_date_end) {
                return array('status' => -1, 'msg' => '请输入合理的搜索日期！');
            }
            $where['out_date'] = array(array('egt', $out_date_start), array('elt', $out_date_end), 'and');
        }
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        //查询当前用户是否有权限审核
        $approveOut = get_menu($this->MODULE, 'PurchaseCheck', 'approveOut');
        $total = $this->DB_get_count('purchases_out_warehouse', $where);
        $plans = $this->DB_get_all('purchases_out_warehouse', '*', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            //查询安装调试日期
            $bugInfo = $this->DB_get_one('purchases_assets_install_debug', '*', array('out_id' => $v['out_id']));
            $plans[$k]['debug_date'] = $bugInfo['debug_start_date'] . ' ~ ' . $bugInfo['debug_end_date'];
            $plans[$k]['debug_desc'] = $bugInfo['debug_desc'];
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['approve_status_name'] = $v['approve_status'] == 1 ? '已通过' : ($v['approve_status'] == 2 ? '不通过' : '未审核');
            $html = '<div class="layui-btn-group">';
            if ($approveOut && $v['approve_status'] == 0) {
                $html .= '<button  class="layui-btn layui-btn-xs" lay-event="approveOut" href="javascript:void(0)" data-url="' . $approveOut['actionurl'] . '">' . $approveOut['actionname'] . '</button>';
            } else {
                $html .= '<button  class="layui-btn layui-btn-xs layui-btn-primary" lay-event="showOut" href="javascript:void(0)" data-url="' . get_url() . '?action=showOut&id=' . $v['out_id'] . '">查看</button>';
            }
            $html .= '</div>';
            $plans[$k]['out_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取出库单明细
     */
    public function getOutWareInfo($out_id)
    {
        return $this->DB_get_one('purchases_out_warehouse', '*', array('out_id' => $out_id));
    }

    /**
     * Notes: 获取出库单设备列表
     * @param $out_id
     * @return mixed
     */
    public function getOutWareAssets($out_id)
    {
        return $this->DB_get_all('purchases_out_warehouse_assets', 'assets_id,assets_name,unit,count(*) as nums,buy_price,sum(buy_price) as total_price,is_import,buy_type', array('out_id' => $out_id), 'assets_id');
    }

    /**
     * Notes: 获取安装调试信息
     * @param $out_id
     */
    public function getDebugInfo($out_id)
    {
        return $this->DB_get_one('purchases_assets_install_debug', '*', array('out_id' => $out_id));
    }

    /**
     * Notes: 保存出库单审批结果
     */
    public function saveOutApprove()
    {
        $out_id = I('post.out_id');
        $data['approve_user'] = session('username');
        $data['approve_time'] = date('Y-m-d H:i:s');
        $data['approve_status'] = I('post.approve_status');
        $data['approve_desc'] = I('post.approve_desc');

        $debugInfo = $this->getDebugInfo($out_id);
        $outWareInfo = $this->getOutWareInfo($out_id);

        $res = $this->updateData('purchases_out_warehouse', $data, array('out_id' => $out_id));
        if ($res) {
            $settingData = $this->checkSmsIsOpen($this->MODULE);
            //审核通过后，修改设备为可用状态
            if ($data['approve_status'] == 1) {
                $this->updateData('purchases_out_warehouse_assets', array('can_use' => 1), array('out_id' => $out_id));
                //修改入库表中对应设备为已出库
                $assets_num = $this->DB_get_one('purchases_out_warehouse_assets', 'group_concat(assets_num) as assets_num', array('out_id' => $out_id));
                $this->updateData('purchases_in_warehouse_assets', array('is_out' => 2), array('assets_num' => array('in', $assets_num['assets_num'])));
                if ($settingData) {
                    //有开启短信 通知调试
                    $attendants_user=explode(',', $debugInfo['attendants_user']);
                    $attendants_user[]=$debugInfo['debug_user'];
                    $debugInfo['out_num']=$outWareInfo['out_num'];
                    $where = [];
                    $where['status'] = C('OPEN_STATUS');
                    $where['is_delete'] = C('NO_STATUS');
                    $where['username'] = ['IN',$attendants_user];
                    $UserData = $this->DB_get_all('user', 'telephone', $where);
                    if ($settingData['debugReport']['status'] == C('OPEN_STATUS') && $UserData) {
                        $sms = $this->formatSmsContent($settingData['debugReport']['content'], $debugInfo);
                        $phone=$this->formatPhone($UserData);
                        ToolController::sendingSMS($phone, $sms, $this->MODULE, $out_id);
                    }
                }
            } else {
                //出库审核不通过，修改入库单中对应出库状态为 0
                //修改入库表中对应设备为已出库
                $assets_num = $this->DB_get_one('purchases_out_warehouse_assets', 'group_concat(assets_num) as assets_num', array('out_id' => $out_id));
                $this->updateData('purchases_in_warehouse_assets', array('is_out' => 0), array('assets_num' => array('in', $assets_num['assets_num'])));
                if ($settingData) {
                    //有开启短信 通知被拒绝
                    $debugInfo['out_num']=$outWareInfo['out_num'];
                    $where = [];
                    $where['status'] = C('OPEN_STATUS');
                    $where['is_delete'] = C('NO_STATUS');
                    $where['username'] = ['EQ',$outWareInfo['out_user']];
                    $UserData = $this->DB_get_all('user', 'telephone', $where);
                    if ($settingData['notOutApproveOver']['status'] == C('OPEN_STATUS') && $UserData) {
                        $sms = $this->formatSmsContent($settingData['notOutApproveOver']['content'], $debugInfo);
                        $phone=$this->formatPhone($UserData);
                        ToolController::sendingSMS($phone, $sms, $this->MODULE, $out_id);
                    }
                }
            }
            return array('status' => 1, 'msg' => '审批成功！');
        } else {
            return array('status' => -1, 'msg' => '审批失败！');
        }
    }

    /**
     * Notes: 或安装调试设备列表
     */
    public function getDebugList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'ware_assets_id';
        $assets_name = I('post.assets_name');
        $departid = I('post.debug_departid');
        $debug_status = I('post.debug_status');
        //$where['A.in_assetsinfo'] = 0;//未入库
        $where['A.can_use'] = 1;//可用
        $where['A.hospital_id'] = session('current_hospitalid');
        if ($assets_name) {
            $where['A.assets_name'] = array('like', "%$assets_name%");
        }
        if ($departid) {
            $where['A.departid'] = $departid;
        }
        if ($debug_status != '') {
            $where['A.debug_status'] = $debug_status;
        }
        $join = "LEFT JOIN sb_purchases_assets_install_debug AS B ON A.out_id = B.out_id";
        $fields = "A.*,B.*";
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        //查询当前用户是否有权限审核
        $debugReport = get_menu($this->MODULE, 'PurchaseCheck', 'debugReport');
        $total = $this->DB_get_count_join('purchases_out_warehouse_assets', 'A', $join, $where);
        $plans = $this->DB_get_all_join('purchases_out_warehouse_assets', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['debug_date'] = $v['debug_start_date'] . ' ~ ' . $v['debug_end_date'];
            $plans[$k]['debug_status_name'] = $v['debug_status'] == 1 ? '调试中' : ($v['debug_status'] == 2 ? '已完成' : '未调试');
            $html = '<div class="layui-btn-group">';
            if ($debugReport && $v['debug_status'] != 2) {
                $html .= '<button  class="layui-btn layui-btn-xs" lay-event="debugReport" href="javascript:void(0)" data-url="' . $debugReport['actionurl'] . '">上传调试报告</button>';
            } else {
                $html .= '<button  class="layui-btn layui-btn-xs layui-btn-primary" lay-event="showDebug" href="javascript:void(0)" data-url="' . get_url() . '?action=showDebug&id=' . $v['ware_assets_id'] . '">查看</button>';
            }
            $html .= '</div>';
            $plans[$k]['debug_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取出库设备信息
     */
    public function outAssetsInfo($ware_assets_id)
    {
        return $this->DB_get_one('purchases_out_warehouse_assets', '*', array('ware_assets_id' => $ware_assets_id));
    }

    /**
     * Notes: 获取调试信息
     */
    public function debugInfo($out_id)
    {
        return $this->DB_get_one('purchases_assets_install_debug', '*', array('out_id' => $out_id));
    }

    /**
     * Notes: 获取报告信息
     * @param $ware_assets_id
     */
    public function outReportInfo($ware_assets_id)
    {
        return $this->DB_get_all('purchases_assets_install_debug_report', '*', array('ware_assets_id' => $ware_assets_id, 'is_delete' => 0));
    }

    public function uploadReport($dir)
    {
        $size = round($_FILES['file']['size'] / 1024 / 1024, 2);
        $Tool = new ToolController();
        $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx', 'zip');
        $dirName = $dir;
        $info = $Tool->upFile($style, $dirName);
        if ($info['status'] == C('YES_STATUS')) {
            $type = explode('.', $info['formerly']);
            $name = explode('/', $info['name']);
            $result['file_name'] = $info['formerly'];
            $result['save_name'] = $name[1];
            $result['file_type'] = $type[1];
            $result['file_size'] = $size . 'M';
            $result['file_url'] = $info['src'];
            $result['add_user'] = session('username');
            $result['add_time'] = date('Y-m-d H:i:s');
            $result['status'] = 1;
            $result['msg'] = '上传成功';
        } else {
            // 上传错误提示错误信息
            $result['status'] = -1;
            $result['msg'] = $info['msg'];
        }
        return $result;
    }

    /**
     * Notes: 获取可进行临床培训的设备列表
     */
    public function getTrainList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'ware_assets_id';
        $assets_name = I('post.assets_name');
        $departid = I('post.train_departid');
        $train_status = I('post.train_status');
        //$where['A.in_assetsinfo'] = 0;//未入库
        $where['A.can_use'] = 1;//可用
        $where['A.debug_status'] = 2;//已完成调试
        $where['A.hospital_id'] = session('current_hospitalid');
        if ($assets_name) {
            $where['A.assets_name'] = array('like', "%$assets_name%");
        }
        if ($departid) {
            $where['A.departid'] = $departid;
        }
        if ($train_status != '') {
            $where['A.train_status'] = $train_status;
        }
        $join = "LEFT JOIN sb_purchases_assets_train AS B ON A.train_id = B.train_id";
        $fields = "A.*,B.*,group_concat(A.ware_assets_id) as ware_ids";
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        //查询当前用户是否有权限制定培训计划
        $trainPlans = get_menu($this->MODULE, 'PurchaseCheck', 'trainPlans');
        $count = $this->DB_get_one_join('purchases_out_warehouse_assets', 'A', 'count(*) as total', $join, $where, 'A.assets_id,A.out_id,A.train_id');
        $plans = $this->DB_get_all_join('purchases_out_warehouse_assets', 'A', $fields, $join, $where, 'A.assets_id,A.out_id,A.train_id', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['train_date'] = $v['train_start_date'] . ' ~ ' . $v['train_end_date'];
            $plans[$k]['train_status_name'] = $v['train_status'] == 1 ? '培训中' : ($v['train_status'] == 2 ? '已完成' : '未培训');
            $html = '<div class="layui-btn-group">';
            if ($trainPlans && $v['plans_status'] == 0) {
                $html .= '<button  class="layui-btn layui-btn-xs" lay-event="trainPlans" href="javascript:void(0)" data-url="' . $trainPlans['actionurl'] . '">' . $trainPlans['actionname'] . '</button>';
            } else {
                if ($v['train_status'] != 2) {
                    $html .= '<button  class="layui-btn layui-btn-xs" lay-event="uploadReport" href="javascript:void(0)" data-url="' . get_url() . '?action=uploadReport&id=' . $v['ware_assets_id'] . '">上传培训报告</button>';
                } elseif ($v['train_status'] == 2) {
                    $html .= '<button  class="layui-btn layui-btn-xs layui-btn-primary" lay-event="showTrain" href="javascript:void(0)" data-url="' . get_url() . '?action=showTrain&id=' . $v['ware_assets_id'] . '">查看</button>';
                }
            }
            $html .= '</div>';
            $plans[$k]['train_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$count['total'];
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    public function saveTrainPlans()
    {
        $ware_ids = I('post.ware_ids');
        //记录培训信息
        $data['ware_ids'] = $ware_ids;
        $data['train_desc'] = I('post.train_desc');
        $data['train_user'] = I('post.train_user');
        $data['train_start_date'] = I('post.train_start_date');
        $data['train_end_date'] = I('post.train_end_date');
        $data['attendants_user'] = I('post.attendants_user');
        $data['train_area'] = I('post.train_area');
        $data['add_user'] = session('username');
        $data['add_time'] = date('Y-m-d H:i:s');
        $new_trainid = $this->insertData('purchases_assets_train', $data);
        if ($new_trainid) {
            $this->updateData('purchases_out_warehouse_assets', array('plans_status' => 1, 'train_id' => $new_trainid), array('ware_assets_id' => array('in', $ware_ids)));
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->MODULE);
            if ($settingData) {
                $data['train_assets']=trim(I('POST.assets_name'));
                //有开启短信 通知讲师进行培训
                $where = [];
                $where['status'] = C('OPEN_STATUS');
                $where['is_delete'] = C('NO_STATUS');
                $where['username'] = ['EQ', $data['train_user']];
                $UserData = $this->DB_get_all('user', 'telephone', $where);
                if ($settingData['doTrain']['status'] == C('OPEN_STATUS') && $UserData) {
                    $sms = $this->formatSmsContent($settingData['doTrain']['content'], $data);
                    $phone = $this->formatPhone($UserData);
                    ToolController::sendingSMS($phone, $sms, $this->MODULE, $new_trainid);
                }
                //有开启短信 通知人员进行培训
                $where = [];
                $where['status'] = C('OPEN_STATUS');
                $where['is_delete'] = C('NO_STATUS');
                $where['username'] = ['IN', $data['attendants_user']];
                $UserData = $this->DB_get_all('user', 'telephone', $where);
                if ($settingData['joinTrain']['status'] == C('OPEN_STATUS') && $UserData) {
                    $sms = $this->formatSmsContent($settingData['joinTrain']['content'], $data);
                    $phone = $this->formatPhone($UserData);
                    ToolController::sendingSMS($phone, $sms, $this->MODULE, $new_trainid);
                }
            }
            //==========================================短信 END============================================
            return array('status' => 1, 'msg' => '提交成功！');
        } else {
            return array('status' => -1, 'msg' => '提交失败！');
        }
    }

    /**
     * Notes: 获取培训计划信息
     * @param $train_id int 培训计划ID
     */
    public function getTrainInfo($train_id)
    {
        return $this->DB_get_one('purchases_assets_train', '*', array('train_id' => $train_id));
    }

    /**
     * Notes: 获取培训计划报告
     * @param $train_id int 培训计划ID
     */
    public function getTrainReports($train_id)
    {
        return $this->DB_get_all('purchases_assets_train_report', '*', array('train_id' => $train_id));
    }

    /**
     * Notes: 获取培训考核报告
     * @param $train_id int 培训计划ID
     */
    public function getTrainAssessReports($train_id)
    {
        return $this->DB_get_all('purchases_assets_train_assessment_report', '*', array('train_id' => $train_id));
    }

    /**
     * Notes: 获取测试运行报告
     * @param $out_id int 出库单ID
     */
    public function getTestReports($ware_assets_id, $out_id)
    {
        return $this->DB_get_all('purchases_assets_test_report', '*', array('ware_assets_id' => $ware_assets_id, 'out_id' => $out_id));
    }

    /**
     * Notes: 获取测试运行报告
     * @param $out_id int 出库单ID
     */
    public function getMeterReports($ware_assets_id, $out_id)
    {
        return $this->DB_get_all('purchases_assets_metering_report', '*', array('ware_assets_id' => $ware_assets_id, 'out_id' => $out_id));
    }

    /**
     * Notes: 获取首次计量报告
     * @param $out_id int 出库单ID
     */
    public function getMeteringReports($ware_assets_id, $out_id)
    {
        return $this->DB_get_all('purchases_assets_metering_report', '*', array('ware_assets_id' => $ware_assets_id, 'out_id' => $out_id));
    }

    /**
     * Notes: 获取临床培训考核列表
     */
    public function getTrainExamineList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'ware_assets_id';
        $assets_name = I('post.assets_name');
        $departid = I('post.assess_departid');
        //$where['A.in_assetsinfo'] = 0;//未入库
        $where['A.can_use'] = 1;//可用
        $where['A.train_status'] = 2;//已完成培训报告上传
        $where['A.hospital_id'] = session('current_hospitalid');
        if ($assets_name) {
            $where['A.assets_name'] = array('like', "%$assets_name%");
        }
        if ($departid) {
            $where['A.departid'] = $departid;
        }
        $join = "LEFT JOIN sb_purchases_assets_train AS B ON A.train_id = B.train_id";
        $fields = "A.*,B.*";
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        //查询当前用户是否有权限制定培训计划
        $assessReport = get_menu($this->MODULE, 'PurchaseCheck', 'assessReport');
        $count = $this->DB_get_one_join('purchases_out_warehouse_assets', 'A', 'count(*) as total', $join, $where, 'A.assets_id,A.out_id,A.train_id');
        $plans = $this->DB_get_all_join('purchases_out_warehouse_assets', 'A', $fields, $join, $where, 'A.assets_id,A.out_id,A.train_id', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['train_date'] = $v['train_start_date'] . ' ~ ' . $v['train_end_date'];
            $html = '<div class="layui-btn-group">';
            if ($assessReport && $v['assessment_status'] == 0) {
                $html .= '<button  class="layui-btn layui-btn-xs" lay-event="trainAssess" href="javascript:void(0)" data-url="' . $assessReport['actionurl'] . '">' . $assessReport['actionname'] . '</button>';
            } else {
                $html .= '<button  class="layui-btn layui-btn-xs layui-btn-primary" lay-event="showAssess" href="javascript:void(0)" data-url="' . get_url() . '?action=showAssess&id=' . $v['ware_assets_id'] . '">查看</button>';
            }
            $html .= '</div>';
            $plans[$k]['assess_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$count['total'];
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取测试运行列表
     */
    public function getTestReportList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'ware_assets_id';
        $assets_name = I('post.assets_name');
        $departid = I('post.test_departid');
        //$where['in_assetsinfo'] = 0;//未入库
        $where['can_use'] = 1;//可用
        $where['assessment_status'] = 1;//已完成考核报告上传
        $where['hospital_id'] = session('current_hospitalid');
        if ($assets_name) {
            $where['assets_name'] = array('like', "%$assets_name%");
        }
        if ($departid) {
            $where['departid'] = $departid;
        }
        $departname = $catname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        //查询当前用户是否有权上传测试运行报告
        $testReport = get_menu($this->MODULE, 'PurchaseCheck', 'testReport');
        $total = $this->DB_get_count('purchases_out_warehouse_assets', $where);
        $plans = $this->DB_get_all('purchases_out_warehouse_assets', '*', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['category'] = $catname[$v['catid']]['category'];
            $html = '<div class="layui-btn-group">';
            if ($testReport && $v['test_status'] == 0) {
                $html .= '<button  class="layui-btn layui-btn-xs" lay-event="testReport" href="javascript:void(0)" data-url="' . $testReport['actionurl'] . '">' . $testReport['actionname'] . '</button>';
            } else {
                $html .= '<button  class="layui-btn layui-btn-xs layui-btn-primary" lay-event="showTest" href="javascript:void(0)" data-url="' . get_url() . '?action=showTest&id=' . $v['ware_assets_id'] . '">查看</button>';
            }
            $html .= '</div>';
            $plans[$k]['test_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取首次计量列表
     */
    public function getFirstMeteringList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'ware_assets_id';
        $assets_name = I('post.assets_name');
        $departid = I('post.meter_departid');
        //$where['in_assetsinfo'] = 0;//未入库
        $where['can_use'] = 1;//可用
        $where['test_status'] = 1;//已完成测试运行报告上传
        $where['hospital_id'] = session('current_hospitalid');
        if ($assets_name) {
            $where['assets_name'] = array('like', "%$assets_name%");
        }
        if ($departid) {
            $where['departid'] = $departid;
        }
        $departname = $catname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        //设备类型为计量设备 才需要做首次计量，所以需要排除不是计量设备的数据
        $exe = $this->DB_get_all('purchases_out_warehouse_assets','distinct assets_id',$where);
        $assets_id = $real_id = [];
        foreach ($exe as $v){
            $assets_id[] = $v['assets_id'];
        }
        if($assets_id){
            $real = $this->DB_get_all('purchases_depart_apply_assets','assets_id',array('hospital_id'=>session('current_hospitalid'),'contract_id'=>array('gt',0),'is_metering'=>1,'assets_id'=>array('in',$assets_id)));
            foreach ($real as $v){
                $real_id[] = $v['assets_id'];
            }
        }
        if(!$real_id){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }else{
            $where['assets_id'] = array('in',$real_id);
        }
        //查询当前用户是否有权限上传首次计量报告
        $firstMetering = get_menu($this->MODULE, 'PurchaseCheck', 'firstMetering');
        $total = $this->DB_get_count('purchases_out_warehouse_assets', $where);
        $plans = $this->DB_get_all('purchases_out_warehouse_assets', '*', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['category'] = $catname[$v['catid']]['category'];
            $html = '<div class="layui-btn-group">';
            if ($firstMetering && $v['firstMetering_status'] == 0) {
                $html .= '<button  class="layui-btn layui-btn-xs" lay-event="firstMetering" href="javascript:void(0)" data-url="' . $firstMetering['actionurl'] . '">' . $firstMetering['actionname'] . '</button>';
            } else {
                $html .= '<button  class="layui-btn layui-btn-xs layui-btn-primary" lay-event="showMetering" href="javascript:void(0)" data-url="' . get_url() . '?action=showMetering&id=' . $v['ware_assets_id'] . '">查看</button>';
            }
            $html .= '</div>';
            $plans[$k]['first_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取质量验收列表
     */
    public function getQualityReportList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'ware_assets_id';
        $assets_name = I('post.assets_name');
        $departid = I('post.quality_departid');

        //找出所有已经可用，并且已完成测试运行的设备id
        $all_where['can_use'] = 1;//可用
        $all_where['test_status'] = 1;//已完成测试
        $all_where['hospital_id'] = session('current_hospitalid');
        $exe = $this->DB_get_all('purchases_out_warehouse_assets','distinct assets_id',$all_where);
        $assets_id = $noreal_id = $yesreal_id = $no_metering_id = $yes_metering_id = [];
        foreach ($exe as $v){
            $assets_id[] = $v['assets_id'];
        }

        //设备类型不是计量设备 不需要进行首次计量
        if($assets_id){
            $noreal = $this->DB_get_all('purchases_depart_apply_assets','assets_id',array('hospital_id'=>session('current_hospitalid'),'contract_id'=>array('gt',0),'is_metering'=>0,'assets_id'=>array('in',$assets_id)));
            foreach ($noreal as $v){
                $noreal_id[] = $v['assets_id'];
            }
        }
        if($noreal_id){
            //不是计量设备，但又已经完成了测试运行的，可以不进行首次计量，直接进行质量验收
            $no_me_where['can_use'] = 1;//可用
            $no_me_where['test_status'] = 1;//已完成测试
            $no_me_where['assets_id'] = array('in',$noreal_id);
            $no_me_where['hospital_id'] = session('current_hospitalid');
            $can = $this->DB_get_all('purchases_out_warehouse_assets','ware_assets_id',$no_me_where);
            foreach ($can as $v){
                $no_metering_id[] = $v['ware_assets_id'];//不是计量设备的id
            }
        }
        //设备类型是计量设备 需要进行首次计量，所以需要把是计量设备找出来
        if($assets_id){
            $yesreal = $this->DB_get_all('purchases_depart_apply_assets','assets_id',array('hospital_id'=>session('current_hospitalid'),'contract_id'=>array('gt',0),'is_metering'=>1,'assets_id'=>array('in',$assets_id)));
            foreach ($yesreal as $v){
                $yesreal_id[] = $v['assets_id'];
            }
        }
        if($yesreal_id){
            //是计量设备，需要进行首次计量
            $me_where['can_use'] = 1;//可用
            $me_where['firstMetering_status'] = 1;//已完成首次计量报告上传
            $me_where['assets_id'] = array('in',$yesreal_id);
            $me_where['hospital_id'] = session('current_hospitalid');
            $can = $this->DB_get_all('purchases_out_warehouse_assets','ware_assets_id',$me_where);
            foreach ($can as $v){
                $yes_metering_id[] = $v['ware_assets_id'];//不是计量设备的id
            }
        }
        $all_assets_id = array_merge($no_metering_id,$yes_metering_id);
        $where['ware_assets_id'] = ['in',$all_assets_id];
        if ($assets_name) {
            $where['assets_name'] = array('like', "%$assets_name%");
        }
        if ($departid) {
            $where['departid'] = $departid;
        }

        $departname = $catname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        //查询当前用户是否有权限上传质量验收报告
        $qualityReport = get_menu($this->MODULE, 'PurchaseCheck', 'qualityReport');
        $total = $this->DB_get_count('purchases_out_warehouse_assets', $where);
        $plans = $this->DB_get_all('purchases_out_warehouse_assets', '*', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['category'] = $catname[$v['catid']]['category'];
            $html = '<div class="layui-btn-group">';
            if ($qualityReport && $v['quality_status'] == 0) {
                $html .= '<button  class="layui-btn layui-btn-xs" lay-event="qualityReport" href="javascript:void(0)" data-url="' . $qualityReport['actionurl'] . '">' . $qualityReport['actionname'] . '</button>';
            } else {
                $html .= '<button  class="layui-btn layui-btn-xs layui-btn-primary" lay-event="showQuality" href="javascript:void(0)" data-url="' . get_url() . '?action=showQuality&id=' . $v['ware_assets_id'] . '">查看</button>';
            }
            $html .= '</div>';
            $plans[$k]['quality_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes:
     */
    public function getQualityReports($ware_assets_id, $outid)
    {
        return $this->DB_get_all('purchases_assets_quality_report', '*', array('ware_assets_id' => $ware_assets_id, 'out_id' => $outid, 'is_delete' => 0));
    }

    public function insertNewAssets($wareInfo)
    {
        $assInfo = $this->DB_get_one('purchases_depart_apply_assets', '*', array('assets_id' => $wareInfo['assets_id']));
        //生成设备编码
        $departnum = $this->DB_get_one('department', 'departnum', array('departid' => $assInfo['departid']));
        $catenum = $this->DB_get_one('category', 'catenum', array('catid' => $assInfo['catid']));
        $max = $this->DB_get_one('assets_info', 'max(assid) as assid', array('1'));
        $ass_data['hospital_id'] = $assInfo['hospital_id'];
        $ass_data['catid'] = $assInfo['catid'];
        $ass_data['assnum'] = $catenum['catenum'] . $departnum['departnum'] . ($max['assid'] + 1);
        $ass_data['assorignum'] = $departnum['departnum'] . ($max['assid'] + 1);
        $ass_data['barcore'] = $ass_data['assnum'];
        $ass_data['assets'] = $assInfo['assets_name'];
        $ass_data['common_name'] = $assInfo['alias_name'];
        $ass_data['helpcatid'] = $assInfo['helpcatid'];
        $ass_data['financeid'] = $assInfo['financeid'];
        $ass_data['brand'] = $assInfo['actually_brand'];
        $ass_data['model'] = $assInfo['model'];
        $ass_data['unit'] = $assInfo['unit'];
        $ass_data['serialnum'] = $wareInfo['serialnum'];
        $ass_data['assetsrespon'] = $assInfo['assetsrespon'];
        $ass_data['departid'] = $assInfo['departid'];
        $ass_data['address'] = $assInfo['address'];
        $ass_data['managedepart'] = $assInfo['managedepart'];
        $ass_data['factorynum'] = $wareInfo['factorynum'] ? $wareInfo['factorynum'] : '';
        $ass_data['factorydate'] = $assInfo['factorydate'];
        $ass_data['opendate'] = $assInfo['opendate'];
        $ass_data['storage_date'] = date('Y-m-d');
        $ass_data['capitalfrom'] = $assInfo['capitalfrom'];

        $ass_data['assfromid'] = $assInfo['assfromid'];
        $ass_data['invoicenum'] = $wareInfo['invoicenum'] ? $wareInfo['invoicenum'] : '';
        $ass_data['buy_price'] = $assInfo['buy_price'];
        $ass_data['expected_life'] = $assInfo['expected_life'];
        $ass_data['residual_value'] = 1;

        $ass_data['is_firstaid'] = $assInfo['is_firstaid'];
        $ass_data['is_special'] = $assInfo['is_special'];
        $ass_data['is_metering'] = $assInfo['is_metering'];
        $ass_data['is_qualityAssets'] = $assInfo['is_qualityAssets'];
        $ass_data['is_benefit'] = $assInfo['is_benefit'];
        $ass_data['is_lifesupport'] = $assInfo['is_lifesupport'];

        $ass_data['guarantee_date'] = $assInfo['guarantee_date'];
        $ass_data['depreciation_method'] = $assInfo['depreciation_method'];
        $ass_data['depreciable_lives'] = $assInfo['depreciable_lives'];
        $ass_data['adduser'] = session('username');
        $ass_data['adddate'] = time();
        $new_assid = $this->insertData('assets_info', $ass_data);
        $last_sql = M()->getLastSql();
        //查询厂商和供应商联系人和电话
        $supInfo = $this->DB_get_one('offline_suppliers', 'sup_name,artisan_name,artisan_phone', array('olsid' => $assInfo['supplier_id']));
        $facInfo = $this->DB_get_one('offline_suppliers', 'sup_name,artisan_name,artisan_phone', array('olsid' => $assInfo['factory_id']));
        $fac_data['assid'] = $new_assid;
        $fac_data['ols_facid'] = $assInfo['factory_id'];
        $fac_data['factory'] = $facInfo['sup_name'];
        $fac_data['factory_user'] = $facInfo['artisan_name'];
        $fac_data['factory_tel'] = $facInfo['artisan_phone'];
        $fac_data['ols_supid'] = $assInfo['supplier_id'];
        $fac_data['supplier'] = $supInfo['sup_name'];
        $fac_data['supp_user'] = $supInfo['artisan_name'];
        $fac_data['supp_tel'] = $supInfo['artisan_phone'];
        $fac_data['adddate'] = time();
        $afid = $this->insertData('assets_factory', $fac_data);
        $this->updateData('assets_info', array('afid' => $afid), array('assid' => $new_assid));
        //日志行为记录文字
        $log['assets'] = $assInfo['assets_name'];
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $log['department'] = $departname[$assInfo['departid']]['department'];
        $text = getLogText('wareOutAssets', $log);
        $this->addLog('assets_info', $last_sql, $text, $new_assid, '');
        //记录入库信息到状态变更表
        $this->updateAssetsStatus($new_assid, 0, '设备入库(仓库提取)');
    }

    /**
     * Notes: 获取设备采购进程数据
     */
    public function getLifeList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'assets_id';
        $departids = I('post.departids');
        $buy_type = I('post.buy_type');
        $assets_name = I('post.assets_name');

        $apply_date_start = I('post.apply_date_start');
        $apply_date_end = I('post.apply_date_end');
        $where['hospital_id'] = session('current_hospitalid');
        $where['is_delete'] = C('NO_STATUS');

        if ($departids) {
            //申请科室搜索
            $where['departid'] = array(array('in', $departids), array('in', session('departid')), 'and');
        } else {
            $where['departid'] = array('in', session('departid'));
        }
        if ($assets_name) {
            //设备名称搜索
            $where['assets_name'] = array('like', "%$assets_name%");
        }
        if ($buy_type != '') {
            //购置类型搜索
            $where['buy_type'] = $buy_type;
        }

        if ($apply_date_start && !$apply_date_end) {
            $where['apply_date'] = array('egt', $apply_date_start);
        }
        if ($apply_date_end && !$apply_date_start) {
            $where['apply_date'] = array('elt', $apply_date_end);
        }
        if ($apply_date_start && $apply_date_end) {
            if ($apply_date_start > $apply_date_end) {
                return array('status' => -1, 'msg' => '请输入合理的搜索日期！');
            }
            $where['apply_date'] = array(array('egt', $apply_date_start), array('elt', $apply_date_end), 'and');
        }

        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $total = $this->DB_get_count('purchases_depart_apply_assets', $where);
        $plans = $this->DB_get_all('purchases_depart_apply_assets', '*', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as $k => $v) {
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['buy_type_name'] = $v['buy_type'] == 1 ? '报废更新' : ($v['buy_type'] == 2 ? '添置' : '新增');
            $plans[$k]['is_import_name'] = $v['is_import'] == 1 ? '是' : '否';
            $html = '<div class="layui-btn-group">';
            $html .= $this->returnListLink('查看', get_url() . '?action=showLife&id=' . $v['assets_id'], 'showLife', C('BTN_CURRENCY') . ' layui-btn-primary');
            $html .= '</div>';
            $plans[$k]['life_operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $plans;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取设备明细
     * @param $assets_id
     * @return mixed
     */
    public function getAssetsInfo($assets_id)
    {
        return $this->DB_get_one('purchases_depart_apply_assets', '*', array('assets_id' => $assets_id));
    }

    /**
     * Notes: 获取设备招标明细
     * @param $assets_id
     */
    public function getTenderDetailByAssetsId($assets_id)
    {
        return $this->DB_get_all('purchases_tender_detail', '*', array('assets_id' => $assets_id, 'is_delete' => 0));
    }

    /**
     * Notes: 设备采购进程明细
     * @param $assets_id int 采购设备ID
     * @param $apply_id int 申请单ID
     * @param $contract_id int 合同ID
     * @param $is_check int 是否已验收
     */
    public function getPurchasesProgressDetail($assets_id, $apply_id, $contract_id, $contract_time, $is_check, $check_time)
    {
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $tips = [];
        $applyInfo = $this->DB_get_one('purchases_depart_apply', '*', array('apply_id' => $apply_id));
        //第一步，科室申请
        $tips[0]['text'] = '<span class="date">' . $applyInfo['apply_time'] . '</span>  【科室申请】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请。';
        $tips[0]['type_name'] = '科室申请';
        if ($applyInfo['approve_status'] == 0) {
            //申请后，如未审核。则显示正在审核，流程结束，提示最新
            $tips[1]['text'] = '<span class="date">' . $applyInfo['apply_time'] . '</span>  【采购审批】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，正在进行审批';
            $tips[1]['new'] = '<span class="newTips">最新</span>';
            $tips[1]['type_name'] = '采购审批';
            return $tips;
        } else {
            //如果已审核
            switch($applyInfo['approve_status']){
                case -1;
                    $pass_tips = "<span style='color:green;'>无需审核</span>";
                break;
                case 0;
                    $pass_tips = "<span style='color:red;'>未审核</span>";
                    break;
                case 1;
                    $pass_tips = "<span style='color:green;'>通过</span>";
                    break;
                case 2;
                    $pass_tips = "<span style='color:red;'>不通过</span>";
                    break;
            }
            $tips[1]['text'] = '<span class="date">' . $applyInfo['approve_time'] . '</span>  【采购审批】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，审批结果为：' . $pass_tips;
            $tips[1]['type_name'] = '采购审批';
            if ($applyInfo['approve_status'] == 2) {
                //但审核不通过，流程结束，并提示最新
                $tips[1]['new'] = '<span class="newTips">最新</span>';
                return $tips;
            }
        }
        //审核通过后
        //查询招标信息，如没有招标信息，则正在进行年度评审
        $tenderInfo = $this->DB_get_one('purchases_tender_record', '*', array('assets_id' => $assets_id));
        if (!$tenderInfo) {
            //招标记录未找到改设备招标信息，则设备正在进行年度评审，流程结束，并提示最新
            $tips[2]['text'] = '<span class="date">' . $applyInfo['approve_time'] . '</span>  【招标论证】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，正在进行招标论证。';
            $tips[2]['new'] = '<span class="newTips">最新</span>';
            $tips[2]['type_name'] = '招标论证';
            return $tips;
        }
        //如果找到招标记录，则判断招标记录来源
        if ($tenderInfo['record_from'] == 1) {
            //来源未招标论证，则该设备曾经过年度评审的
            $tips[2]['text'] = '<span class="date">' . $applyInfo['approve_time'] . '</span>  【招标论证】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，正在进行年度评审。';
            $tips[2]['type_name'] = '招标论证';
            $tips[3]['text'] = '<span class="date">' . $tenderInfo['add_time'] . '</span>  【招标记录】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，正在进行招标。';
            $tips[3]['type_name'] = '招标记录';
            if ($tenderInfo['have_final_supplier'] == 0) {
                //未确认供应商
                $tips[3]['new'] = '<span class="newTips">最新</span>';
                return $tips;
            } else {
                //已确认供应商
                $tips[4]['text'] = '<span class="date">' . $tenderInfo['handle_time'] . '</span>  【合同录入】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，正在进行合同录入。';
                $tips[4]['type_name'] = '合同录入';
                if ($contract_id) {
                    //有合同
                    if ($is_check) {
                        //已验收
                        $tips[5]['text'] = '<span class="date">' . $check_time . '</span>  【设备验收】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，设备已验收。';
                        $tips[5]['new'] = '<span class="newTips">最新</span>';
                        $tips[5]['type_name'] = '设备验收';
                        return $tips;
                    } else {
                        $tips[5]['text'] = '<span class="date">' . $contract_time . '</span>  【设备验收】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，正在进行验收。';
                        $tips[5]['new'] = '<span class="newTips">最新</span>';
                        $tips[5]['type_name'] = '设备验收';
                        return $tips;
                    }
                } else {
                    //没合同
                    $tips[4]['new'] = '<span class="newTips">最新</span>';
                    return $tips;
                }
            }
        } else {
            //招标记录不经过年度评审
            $tips[2]['text'] = '<span class="date">' . $tenderInfo['add_time'] . '</span>  【招标记录】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，正在进行招标。';
            $tips[2]['type_name'] = '招标记录';
            if ($tenderInfo['have_final_supplier'] == 0) {
                //未确认供应商
                $tips[2]['new'] = '<span class="newTips">最新</span>';
                return $tips;
            } else {
                //已确认供应商
                $tips[3]['text'] = '<span class="date">' . $tenderInfo['handle_time'] . '</span>  【合同录入】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，正在进行合同录入。';
                $tips[3]['type_name'] = '合同录入';
                if ($contract_id) {
                    //有合同
                    if ($is_check) {
                        //已验收
                        $tips[4]['text'] = '<span class="date">' . $check_time . '</span>  【设备验收】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，设备已验收。';
                        $tips[4]['new'] = '<span class="newTips">最新</span>';
                        $tips[4]['type_name'] = '设备验收';
                        return $tips;
                    } else {
                        $tips[4]['text'] = '<span class="date">' . $contract_time . '</span>  【设备验收】  ' . $departname[$applyInfo['apply_departid']]['department'] . '的' . $applyInfo['apply_user'] . ' 提出【' . $applyInfo['project_name'] . '】采购申请，正在进行验收。';
                        $tips[4]['new'] = '<span class="newTips">最新</span>';
                        $tips[4]['type_name'] = '设备验收';
                        return $tips;
                    }
                } else {
                    //没合同
                    $tips[4]['new'] = '<span class="newTips">最新</span>';
                    return $tips;
                }
            }
        }
    }

    //格式化短信内容
    public static function formatSmsContent($content, $data)
    {
        $content = str_replace("{project_name}", $data['project_name'], $content);
        $content = str_replace("{plans_num}", $data['plans_num'], $content);
        $content = str_replace("{department}", $data['department'], $content);
        $content = str_replace("{apply_num}", $data['apply_num'], $content);
        $content = str_replace("{approve_status}", $data['approve_status'], $content);
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{review_status}", $data['review_status'], $content);
        $content = str_replace("{installStartDate}", $data['debug_start_date'], $content);
        $content = str_replace("{installEendDate}", $data['debug_end_date'], $content);
        $content = str_replace("{debug_area}", $data['debug_area'], $content);
        $content = str_replace("{out_assets}", $data['out_assets'], $content);
        $content = str_replace("{out_num}", $data['out_num'], $content);
        $content = str_replace("{train_assets}", $data['train_assets'], $content);
        $content = str_replace("{trainStartDate}", $data['train_start_date'], $content);
        $content = str_replace("{trainEendDate}", $data['train_end_date'], $content);
        $content = str_replace("{train_area}", $data['train_area'], $content);

        return $content;
    }
}
