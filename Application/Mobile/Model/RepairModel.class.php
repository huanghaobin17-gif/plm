<?php

namespace Mobile\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;
use Think\Model;
use Think\Model\RelationModel;
use Admin\Model\ModuleModel;

class RepairModel extends CommonModel
{
    protected $len = 100;
    protected $tablePrefix = 'sb_';
    protected $tableName = 'repair';
    protected $MODULE = 'Repair';
    protected $Controller = 'Repair';

    //接单列表数据
    public function ordersLists()
    {
        $page = I('POST.page') ? I('POST.page') : 1;
        $offset = ($page - 1) * C('PAGE_NUMS');
        $order = I('POST.order');
        $sort = I('POST.sort');
        $hospital_id = I('POST.hospital_id');
        $search = trim(I('POST.search'));
        if ($search) {
            //科室
            $depairWhere['department'] = ['LIKE', "%$search%"];
            $depairWhere['hospital_id'] = ['EQ', session('current_hospitalid')];
            $department = $this->DB_get_all('department', 'departid', $depairWhere);
            if ($department) {
                $departidArr = [];
                foreach ($department as &$one) {
                    $departidArr[] = $one['departid'];
                }
                $where[1]['A.departid'] = ['IN', $departidArr];
            }
            $where[1]['A.assnum'] = ['LIKE', "%$search%"];
            $where[1]['A.assets'] = ['LIKE', "%$search%"];
            $where[1]['_logic'] = 'OR';
        }


        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = C('PAGE_NUMS');
        }
        if (!$sort) {
            $sort = 'R.applicant_time';
        } else {
            $sort = 'R.' . $sort;
        }
        if (!$order) {
            $order = 'DESC';
        }

        if ($hospital_id) {
            $where['A.hospital_id'] = $hospital_id;
        } else {
            $where['A.hospital_id'] = session('current_hospitalid');
        }
        $where['A.departid'] = ['IN', session('departid')];

        $where['R.status'] = ['EQ', C('REPAIR_HAVE_REPAIRED')];


        //如果不是超级管理员 只显示自己接的单和未接单的和指派了自己的
        $username = session('username');
        if (!session('isSuper')) {
            $where['_string'] .= "(is_assign=" . C('NOTHING_STATUS') . " OR ISNULL(assign_engineer) OR assign_engineer='$username') AND (ISNULL(response) OR response='$username')";
        }
        $where['A.is_delete'] = '0';
        $fields = "A.departid,A.assid,A.assets,A.assnum,R.applicant,R.applicant_time,A.model,A.pic_url,R.repid";
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=R.assid';
        $total = $this->DB_get_count_join('repair', 'R', $join, $where);
        $data = $this->DB_get_all_join('repair', 'R', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        //\Think\Log::write('sql===='.M()->getLastSql());
        //搜索查询
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $acceptMenu = get_menu($this->MODULE, $this->Controller, 'accept');
        foreach ($data as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            $one['applicant_time'] = getHandleMinute($one['applicant_time']);
            $one['operation'] = $this->returnMobileLink($acceptMenu['actionname'], $acceptMenu['actionurl'] . '?repid=' . $one['repid'], ' layui-btn-normal accept');
            if ($one['pic_url']) {
                $one['pic_url'] = explode(',', $one['pic_url']);
                foreach ($one['pic_url'] as &$pic) {
                    $pic = '/Public/uploads/assets/' . $pic;
                }
            }
        }
        $result['pages'] = ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result["code"] = 200;
        $result['rows'] = $data;
        $result['page'] = $page;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    //接单操作
    public function accept()
    {
        $repid = I('POST.repid');
        $datafield = 'assid,repnum,applicant,response,response_date,status,assign,department,assnum,response_date,departid,
        applicant_tel,applicant,approve_status,assign_tel';
        $data = $this->DB_get_one('repair', $datafield, array('repid' => $repid));
        if (!$data) {
            $result['status'] = -1;
            $result['msg'] = '维修单不存在';
            return $result;
        }
        if ($data['status'] == C('REPAIR_RECEIPT')) {
            $result['status'] = -1;
            $result['msg'] = '已被接单';
            return $result;
        }
        //查询设备所属医院
        $assInfo = $this->DB_get_one('assets_info', 'assets,assnum,model,departid,hospital_id', array('assid' => $data['assid'], 'is_delete' => '0'));
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assInfo['department'] = $departname[$assInfo['departid']]['department'];
        $data['hospital_id'] = $assInfo['hospital_id'];
        $expect_arrive = I('POST.expect_arrive');
        $this->checkstatus(judgeNum($expect_arrive) && $expect_arrive > 0, '到场时间必须为大于0');
        $data['response'] = session('username');
        $data['response_tel'] = session('telephone');
        $data['response'] = session('username');
        $data['response_tel'] = session('telephone');
        $data['response_date'] = time();
        $data['notice_time'] = time();
        $data['editdate'] = time();
        $data['expect_arrive'] = $expect_arrive;
        $data['status'] = C('REPAIR_RECEIPT');
        $data['reponse_remark'] = I('post.reponse_remark');
        $save = $this->updateData('repair', $data, array('repid' => $repid));
        if ($save) {
            $this->addLog('repair', M()->getLastSql(), '接单一台维修编号为' . $data['repnum'] . '的设备', $repid);
            $result['status'] = 1;
            $result['msg'] = '接单成功';

            //判断微信是否启用
            $moduleModel = new ModuleModel();
            $wx_status = $moduleModel->decide_wx_login();
            if ($wx_status) {
                //发送微信消息给报修人（维修处理通知）
                //查询报修人员openid
                $applicant = $this->DB_get_one('user', 'telephone,openid', array('username' => $data['applicant']));

                if ($applicant['openid']) {
                    Weixin::instance()->sendMessage($applicant['openid'], '设备维修通知', [
                        'thing3'             => $assInfo['department'],// 科室
                        'thing6'             => $assInfo['assets'],// 设备名称
                        'character_string12' => $assInfo['assnum'],// 设备编码
                        'character_string35' => $data['repnum'],// 维修单号
                        'const17'            => '已接单',// 工单状态
                    ]);
                }
            }

            //推送一条推送消息到大屏幕
            $push_messages[] = ['type_action' => 'edit', 'type_name' => C('SCREEN_REPAIR'), 'assets' => $assInfo['assets'], 'assnum' => $assInfo['assnum'], 'department' => $assInfo['department'], 'remark' => $data['reponse_remark'], 'status' => $data['status'], 'status_name' => '已接单', 'time' => date('Y-m-d H:i'), 'username' => session('username') . '(' . session('telephone') . ')'];
            push_messages($push_messages);
        } else {
            $result['status'] = -1;
            $result['msg'] = '接单失败!';
        }
        return $result;
    }

    //检修操作
    public function overhaul()
    {
        $repid = I('post.repid');
        $datafield = 'assid,repnum,applicant,applicant_time,response,response_date,status,assign,department,assets,assnum,response_date,departid,
        applicant_tel,applicant,approve_status,assign_tel';
        $data = $this->DB_get_one('repair', $datafield, array('repid' => $repid));
        if (!$data) {
            $result['status'] = -2;
            $result['msg'] = '维修单不存在';
            return $result;
        }
        if ($data['status'] >= C('REPAIR_HAVE_OVERHAULED')) {
            $result['status'] = -2;
            $result['msg'] = '请勿重复操作';
            return $result;
        }
        //查询设备所属医院
        $assInfo = $this->DB_get_one('assets_info', 'assets,assnum,model,departid,hospital_id', array('assid' => $data['assid'], 'is_delete' => '0'));
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assInfo['department'] = $departname[$assInfo['departid']]['department'];
        $data['hospital_id'] = $assInfo['hospital_id'];
        $RepairModel = new \Admin\Model\RepairModel();
        //检修操作
        $moduleModel = new ModuleModel();
        $wx_status = $moduleModel->decide_wx_login();
        $code_status = $moduleModel->decide_sweepCode();
        if ($wx_status && !$code_status) {
            $log = $this->DB_get_one('log', 'logid', array('module' => 'repair', 'action' => 'sign', 'actionid' => $repid, 'username' => session('username')));
            if (!$log) {
                $result['status'] = -8;
                $result['msg'] = '未签到';
                return $result;
            }
        }

        $is_scene = I('POST.is_scene');
        if ($is_scene == C('YES_STATUS')) {
            //现场解决
            $result = $RepairModel->sceneEnd($data, $repid);
        } else {
            //现场不能解决
            $result = $RepairModel->notSceneEnd($data, $repid);
        }
        return $result;
    }

    /*
     * 维修进程
     */
    public function progress()
    {
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $limit = I('post.limit') ? I('post.limit') : C('PAGE_NUMS');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'DESC';
        $sort = I('post.sort');
        $search = I('POST.search');
        $repairStatus = I('POST.status');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $where['A.status'][0] = 'IN';
        $where['A.status'][1][] = C('REPAIR_HAVE_REPAIRED');//已报修待接单
        $where['A.status'][1][] = C('REPAIR_RECEIPT');//已接单待检修的设备
        $where['A.status'][1][] = C('REPAIR_HAVE_OVERHAULED');//已检修/配件待出库
        $where['A.status'][1][] = C('REPAIR_AUDIT');//审核中
        $where['A.status'][1][] = C('REPAIR_MAINTENANCE');//维修中
        $where['A.status'][1][] = C('REPAIR_MAINTENANCE_COMPLETION');//待验收
        $where['B.hospital_id'] = $hospital_id;
        $where['B.departid'] = array('IN', $departids);
        switch ($sort) {
            case 'applicant_time':
                $sort = 'A.applicant_time';
                break;
            case 'status':
                $sort = 'A.status';
                break;
            default:
                $sort = 'A.applicant_time';
                break;
        }
        $whereIdx = 0;
        if ($search) {
            $map['B.assets'] = array('like', '%' . $search . '%');
            $map['B.assnum'] = array('like', '%' . $search . '%');
            $map['B.model'] = array('like', '%' . $search . '%');
            $map['B.brand'] = array('like', '%' . $search . '%');
            $map['C.department'] = array('like', '%' . $search . '%');
            $map['_logic'] = 'or';
            $where['_complex'][$whereIdx++] = $map;
        }
        if ($repairStatus) {
            switch ($repairStatus) {
                case 1:
                    if (session('isSuper')) {
                        $where['A.status'] = 1;
                        $where['is_assign'] = 1;
                        $whereOR['A.status'] = 1;
                        $whereOR['_logic'] = 'or';
                        $where['_complex'][$whereIdx++] = $whereOR;
                    } else {
                        $where['A.status'] = 1;
                        $where['is_assign'] = 1;
                        $where['assign_engineer'] = session('username');
                        $whereOR['A.status'] = 1;
                        $whereOR['_logic'] = 'or';
                        $where['_complex'][$whereIdx++] = $whereOR;
                    }
                    break;
                case 2:
                    $whereOR[0]['A.status'] = 2;
                    $whereOR[1]['A.status'] = 4;
                    $whereOR['_logic'] = 'or';
                    $where['_complex'][$whereIdx++] = $whereOR;
                    break;
                case 3:
                    $where['A.status'] = 3;
                    break;
                case 4:
                    $where['A.status'] = 5;
                    break;
                case 5:
                    $where['A.status'] = 5;
                    break;
                case 6:
                    $where['A.status'] = 6;
                    break;
                case 7:
                    $where['A.status'] = 7;
                    break;
                case 8:
                    $where['A.status'] = 8;
                    break;
                case  9:
                    $where['A.status'] = 1;
                    $where['is_assign'] = 0;
                    break;
            }
        }
        $where['B.is_delete'] = '0';
        $join[0] = ' LEFT JOIN __ASSETS_INFO__ AS B ON A.assid = B.assid';
        $join[1] = ' LEFT JOIN __DEPARTMENT__ AS C ON B.departid = C.departid';
        $total = $this->DB_get_count_join('repair', 'A', $join, $where);
        $fields = 'A.repid,A.assid,A.applicant as appUser,A.applicant_time,A.repnum,A.status,A.overdate,B.assets,B.assnum,C.department';
        $asArr = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asArr) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        foreach ($asArr as &$one) {
            $one['url'] = $this->returnMobileLink('查看', get_url() . '?repid=' . $one['repid'] . '&action=showRepairDetails', ' layui-btn layui-btn-normal');;
            $one['applicant_time'] = getHandleMinute($one['applicant_time']);
            switch ($one['status']) {
                case 1:
                    $one['status_name'] = '待接单';
                    $one['status_class_name'] = 'layui-bg-green layui-btn layui-btn-xs layui-btn-radius';
                    break;
                case 2:
                    $one['status_name'] = '待检修';
                    $one['status_class_name'] = 'layui-bg-cyan layui-btn layui-btn-xs layui-btn-radius';
                    break;
                case 3:
                    $one['status_name'] = '待出库';
                    $one['status_class_name'] = 'layui-bg-red layui-btn layui-btn-xs layui-btn-radius';
                    break;
                case 5:
                    $one['status_name'] = '待审核';
                    $one['status_class_name'] = 'layui-bg-red layui-btn layui-btn-xs layui-btn-radius';
                    break;
                case 6:
                    $one['status_name'] = '继续维修';
                    $one['status_class_name'] = 'layui-bg-orange layui-btn layui-btn-xs layui-btn-radius';
                    break;
                case 7:
                    $one['status_name'] = '待验收';
                    $one['status_class_name'] = 'layui-bg-green layui-btn layui-btn-xs layui-btn-radius';
                    break;
                case 8:
                    $one['status_name'] = '已结束';
                    $one['status_class_name'] = 'layui-btn layui-btn-xs layui-btn-radius layui-btn-primary';
                    break;
            }
        }
        $result['page'] = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result['rows'] = $asArr;
        $result['code'] = 200;
        return $result;
    }

    //检修列表数据
    public function overhaulLists()
    {
        $page = I('POST.page') ? I('POST.page') : 1;
        $offset = ($page - 1) * C('PAGE_NUMS');
        $order = I('POST.order');
        $sort = I('POST.sort');
        $hospital_id = I('POST.hospital_id');
        $search = trim(I('POST.search'));
        if ($search) {
            //科室
            $depairWhere['department'] = ['LIKE', "%$search%"];
            $depairWhere['hospital_id'] = ['EQ', session('current_hospitalid')];
            $department = $this->DB_get_all('department', 'departid', $depairWhere);
            if ($department) {
                $departidArr = [];
                foreach ($department as &$one) {
                    $departidArr[] = $one['departid'];
                }
                $where[1]['A.departid'] = ['IN', $departidArr];
            }
            $where[1]['A.assnum'] = ['LIKE', "%$search%"];
            $where[1]['A.assets'] = ['LIKE', "%$search%"];
            $where[1]['R.repnum'] = ['LIKE', "%$search%"];
            $where[1]['_logic'] = 'OR';
        }

        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = C('PAGE_NUMS');
        }
        if (!$sort) {
            $sort = 'R.applicant_time';
        } else {
            $sort = 'R.' . $sort;
        }
        if (!$order) {
            $order = 'DESC';
        }

        $where['A.departid'] = ['IN', session('departid')];

        if ($hospital_id) {
            $where['A.hospital_id'] = $hospital_id;
        } else {
            $where['A.hospital_id'] = session('current_hospitalid');
        }
        $where['R.status'] = ['EQ', C('REPAIR_RECEIPT')];
        if (!session('isSuper')) {
            $where['response'] = ['EQ', session('username')];
        }
        $where['A.is_delete'] = '0';
        $fields = "A.departid,A.assid,A.assets,A.assnum,R.applicant,R.response_date,A.model,R.repnum,R.repid";
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=R.assid';
        $total = $this->DB_get_count_join('repair', 'R', $join, $where);
        $data = $this->DB_get_all_join('repair', 'R', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);


        //搜索查询
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";

        $overhaulMenu = get_menu($this->MODULE, $this->Controller, 'accept');
        foreach ($data as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            $one['response_date'] = getHandleMinute($one['response_date']);

            if ($baseSetting['repair']['open_sweepCode_overhaul']['value']['open'] == C('OPEN_STATUS')) {
                $one['operation'] = $this->returnListLink('扫码检修', $overhaulMenu['actionurl'], 'overhaul', ' layui-btn-warm');
            } else {
                $one['operation'] = $this->returnMobileLink('检修', $overhaulMenu['actionurl'] . '?repid=' . $one['repid'] . '&action=overhaul', ' layui-btn-warm overhaul');
            }
            $one['operation'] = $this->returnMobileLink('检修', $overhaulMenu['actionurl'] . '?repid=' . $one['repid'] . '&action=overhaul', ' layui-btn-warm overhaul');
        }
        $result['pages'] = ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result["code"] = 200;
        $result['rows'] = $data;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }


    public function repairApproveLists()
    {
        $page = I('POST.page') ? I('POST.page') : 1;
        $offset = ($page - 1) * C('PAGE_NUMS');
        $order = I('POST.order');
        $sort = I('POST.sort');
        $hospital_id = I('POST.hospital_id');

        $search = trim(I('POST.search'));
        if ($search) {
            //科室
            $depairWhere['department'] = ['LIKE', "%$search%"];
            $depairWhere['hospital_id'] = ['EQ', session('current_hospitalid')];
            $department = $this->DB_get_all('department', 'departid', $depairWhere);
            if ($department) {
                $departidArr = [];
                foreach ($department as &$one) {
                    $departidArr[] = $one['departid'];
                }
                $where[1]['A.departid'] = ['IN', $departidArr];
            }
            $where[1]['A.assnum'] = ['LIKE', "%$search%"];
            $where[1]['A.assets'] = ['LIKE', "%$search%"];
            $where[1]['R.repnum'] = ['LIKE', "%$search%"];
            $where[1]['_logic'] = 'OR';
        }


        $hospital_id = session('current_hospitalid');
        $where['A.departid'] = array('IN', session('departid'));
        $where['R.all_approver'] = array('LIKE', '%/' . session('username') . '/%');
        $where['A.hospital_id'] = $hospital_id;
        $where['R.approve_status'] = array('EQ', C('REPAIR_IS_NOTCHECK'));

        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = C('PAGE_NUMS');
        }
        if (!$sort) {
            $sort = 'R.applicant_time';
        } else {
            $sort = 'R.' . $sort;
        }
        if (!$order) {
            $order = 'DESC';
        }
        if ($hospital_id) {
            $where['A.hospital_id'] = $hospital_id;
        } else {
            $where['A.hospital_id'] = session('current_hospitalid');
        }
        $where['R.status'] = ['EQ', C('REPAIR_AUDIT')];
        $where['A.is_delete'] = '0';
        $fields = "A.departid,A.assid,A.assets,A.assnum,R.repnum,R.applicant,R.applicant_time,A.model,R.repid";
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=R.assid';
        $total = $this->DB_get_count_join('repair', 'R', $join, $where);
        $data = $this->DB_get_all_join('repair', 'R', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        //搜索查询
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $addApproveMenu = get_menu($this->MODULE, $this->Controller, 'addApprove');
        foreach ($data as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            $one['applicant_time'] = getHandleDate($one['applicant_time']);

            $one['operation'] = $this->returnMobileLink($addApproveMenu['actionname'], $addApproveMenu['actionurl'] . '?repid=' . $one['repid'], ' layui-btn-danger');


        }
        $result['pages'] = ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result["code"] = 200;
        $result['rows'] = $data;
        $result['page'] = $page;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;

    }

    /*
     * 检测是否可以报修设备
     */
    public function scanQRcode_baoxiu()
    {
        $departid = session('departid');
        $assnum = I('post.assnum');
        $url = $this->index_url;
        if (!$assnum) {
            return array(
                'status' => -1,
                'msg' => '扫码错误！',
                'url' => $this->fail_url . '?url=' . $url . '&tips=扫码错误，请重新扫码&btn=返回首页'
            );
        }
        //微信扫码进入，查询assid
        $exists = $this->DB_get_one('assets_info', 'assid,status,assnum,departid', array('assnum' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
        if (!$exists) {
            $exists = $this->DB_get_one('assets_info', 'assid,status,assnum,departid', array('assorignum' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
        }
        if (!$exists) {
            $exists = $this->DB_get_one('assets_info', 'assid,status,assnum,departid', array('assorignum_spare' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
        }
        if (!$exists) {
            return array(
                'status' => -1,
                'msg' => '查找不到编码为 ' . $assnum . ' 的设备信息',
                'url' => $this->fail_url . '?url=' . $url . '&tips=查找不到编码为 ' . $assnum . ' 的设备信息&btn=返回首页'
            );
        }
        if (!in_array($exists['departid'], explode(',', $departid))) {
            return array(
                'status' => -1,
                'msg' => '您无权操作该部门设备!',
                'url' => $this->fail_url . '?url=' . $url . '&tips=对不起，'.$assnum.'设备不在您管理的科室范围内！&btn=返回首页'
            );
        }
        //判断该设备是否已报修
        if ($exists['status'] == C('ASSETS_STATUS_REPAIR')) {
            //状态 维修中
            $repWhere['assid'] = array('EQ', $exists['assid']);
            $repWhere['status'] = array('NEQ', C('REPAIR_ALREADY_ACCEPTED'));
            $repinfo = $this->DB_get_one('repair', 'repid', $repWhere, 'repid desc');
            return array(
                'status' => -1,
                'msg' => '该设备正在维修中...',
                'url' => $this->fail_url . '?url=' . $this->repair_detail_url . '?repid=' . $repinfo['repid'] . '&tips=该设备正在维修中&btn=查看维修单详情'
            );
        }
        return array(
            'status' => 1,
            'msg' => 'success',
            'url' => $this->addRepair_url . '?assnum=' . $exists['assnum']
        );
    }

    /*
     * 检查是否可以检修设备
     */
    public function scanQRCode_jianxiu()
    {
        $departid = session('departid');
        $assnum = I('post.assnum');
        $url = $this->overhaulLists_url;
        if (!$assnum) {
            return array(
                'status' => -1,
                'msg' => '扫码错误！',
                'url' => $this->fail_url . '?url=' . $url . '&tips=扫码错误，请重新扫码&btn=检修列表'
            );
        }
        //微信扫码检修进入，查询repid
        $exists = $this->DB_get_one('assets_info', 'assid,assnum,departid', array('assnum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        if (!$exists) {//先判断原设备码
            $exists = $this->DB_get_one('assets_info', 'assid,assnum,departid', array('assorignum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        }
        if (!$exists) {
            //设备原编码备用
            $exists = $this->DB_get_one('assets_info', 'assid,assnum,departid', array('assorignum_spare' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        }
        if (!$exists) {
            return array(
                'status' => -1,
                'msg' => '查找不到编码为 ' . $assnum . ' 的设备信息',
                'url' => $this->fail_url . '?url=' . $url . '&tips=查找不到编码为 ' . $assnum . ' 的设备信息&btn=检修列表'
            );
        }
        if (!in_array($exists['departid'], explode(',', $departid))) {
            return array(
                'status' => -1,
                'msg' => '您无权操作该部门设备!',
                'url' => $this->fail_url . '?url=' . $url . '&tips=对不起，您无权操作该部门设备&btn=检修列表'
            );
        }
        //查询该设备维修ID
        $repinfo = $this->DB_get_one('repair', 'repid', array('assid' => $exists['assid'], 'status' => 2));
        if (!$repinfo['repid']) {
            return array(
                'status' => -1,
                'msg' => '查找不到设备编码为 ' . $assnum . ' 的检修单信息！',
                'url' => $this->fail_url . '?url=' . $url . '&tips=查找不到设备编码为 ' . $assnum . ' 的检修信息，请确认该设备是否已报修和接单&btn=检修列表'
            );
        }
        return array(
            'status' => 1,
            'msg' => 'success',
            'url' => $this->accept_url . '?repid=' . $repinfo['repid'] . '&action=overhaul'
        );
    }

    /**
     * Notes: 扫码签到
     * @return array
     */
    public function scanQRCode_signin()
    {
        $departid = session('departid');
        $assnum = I('post.assnum');
        $url = $this->overhaulLists_url;
        if (!$assnum) {
            return array(
                'status' => -1,
                'msg' => '扫码错误！',
                'url' => $this->fail_url . '?url=' . $url . '&tips=扫码错误，请重新扫码&btn=检修列表'
            );
        }
        //微信扫码签到进入，查询repid
        $exists = $this->DB_get_one('assets_info', 'assid,assnum,departid', array('assnum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        if (!$exists) {//先判断原设备码
            $exists = $this->DB_get_one('assets_info', 'assid,assnum,departid', array('assorignum' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        }
        if (!$exists) {
            //设备原编码备用
            $exists = $this->DB_get_one('assets_info', 'assid,assnum,departid', array('assorignum_spare' => $assnum, 'is_delete' => '0', 'hospital_id' => session('current_hospitalid')));
        }
        if (!$exists) {
            return array(
                'status' => -1,
                'msg' => '查找不到编码为 ' . $assnum . ' 的设备信息',
                'url' => $this->fail_url . '?url=' . $url . '&tips=查找不到编码为 ' . $assnum . ' 的设备信息&btn=检修列表'
            );
        }
        if (!in_array($exists['departid'], explode(',', $departid))) {
            return array(
                'status' => -1,
                'msg' => '您无权操作该部门设备!',
                'url' => $this->fail_url . '?url=' . $url . '&tips=对不起，您无权操作该部门设备&btn=检修列表'
            );
        }
        //查询该设备维修ID
        $repinfo = $this->DB_get_one('repair', 'repid', array('assid' => $exists['assid'], 'status' => 2));
        if (!$repinfo['repid']) {
            return array(
                'status' => -1,
                'msg' => '查找不到设备编码为 ' . $assnum . ' 的检修单信息！',
                'url' => $this->fail_url . '?url=' . $url . '&tips=查找不到设备编码为 ' . $assnum . ' 的检修信息，请确认该设备是否已报修和接单&btn=检修列表'
            );
        }
        //保存签到数据
        $updata['sign_in_time'] = date('Y-m-d H:i:s');
        $updata['latitude'] = I('post.latitude');
        $updata['longitude'] = I('post.longitude');
        $res = $this->updateData('repair',$updata,['repid'=>$repinfo['repid']]);
        \Think\Log::write('---------------------------------------------');
        \Think\Log::write($updata['sign_in_time']);
        \Think\Log::write($updata['latitude']);
        \Think\Log::write($updata['longitude']);
        \Think\Log::write(M()->getLastSql());
        \Think\Log::write('---------------------------------------------');
        if($res){
            return array(
                'status' => 1,
                'msg' => '微信签到成功！'
            );
        }else{
            return array(
                'status' => -1,
                'msg' => '保存签到信息失败'.$res
            );
        }
    }

    /*
     *
     */
    public function examine()
    {
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $limit = I('post.limit') ? I('post.limit') : C('PAGE_NUMS');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'DESC';
        $sort = I('post.sort');
        $search = I('POST.search');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $where['A.status'] = array('in', [C('REPAIR_MAINTENANCE_COMPLETION')]);//待验收、转单确认
        $where['B.hospital_id'] = $hospital_id;
        $where['B.departid'] = array('IN', $departids);
        switch ($sort) {
            case 'applicant_time':
                $sort = 'A.applicant_time';
                break;
            case 'overdate':
                $sort = 'A.overdate';
                break;
            default:
                $sort = 'A.applicant_time';
                break;
        }
        if ($search) {
            $map['B.assets'] = array('like', '%' . $search . '%');
            $map['B.assnum'] = array('like', '%' . $search . '%');
            $map['B.model'] = array('like', '%' . $search . '%');
            $map['B.brand'] = array('like', '%' . $search . '%');
            $map['C.department'] = array('like', '%' . $search . '%');
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $where['B.is_delete'] = '0';
        $join[0] = ' LEFT JOIN __ASSETS_INFO__ AS B ON A.assid = B.assid';
        $join[1] = ' LEFT JOIN __DEPARTMENT__ AS C ON B.departid = C.departid';
        $total = $this->DB_get_count_join('repair', 'A', $join, $where);
        $fields = 'A.repid,A.assid,A.applicant as appUser,A.applicant_time as appTime,A.repnum,A.status,A.overdate,B.assets,B.assnum,C.department';
        $asArr = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asArr) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $menuData = get_menu($this->MODULE, 'Repair', 'checkRepair');
        //当前用户可验收科室
        foreach ($asArr as $k => $v) {
            //查询用户头像
            $uinfo = $this->DB_get_one('user', 'pic', array('username' => $v['appUser']));
            $asArr[$k]['appTime'] = getHandleMinute($v['appTime']);
            $asArr[$k]['overdate'] = getHandleDate($v['overdate']);
            if ($uinfo['pic']==""||$uinfo['pic']==null) {
                    $asArr[$k]['headimgurl']="/Public/mobile/images/user_logo.png";
                }else{
                    $asArr[$k]['headimgurl'] = $uinfo['pic'];
                }
            if ($asArr[$k]['status'] == C('REPAIR_MAINTENANCE_COMPLETION')) {
                if ($menuData) {
                    $asArr[$k]['checkRepair'] = '<div class="jumpButton">';
                    $asArr[$k]['checkRepair'] .= $this->returnMobileLink('验收', $menuData['actionurl'] . '?repid=' . $v['repid'], ' accept');
                    $asArr[$k]['checkRepair'] .= '</div>';
                    //$asArr[$k]['checkRepair'] = $menuData['actionurl'] . '?repid=' . $v['repid'];
                } else {
                    //$asArr[$k]['checkRepair'] = 'javascript:;';
                    $asArr[$k]['checkRepair'] = '<div class="jumpButton">';
                    $asArr[$k]['checkRepair'] .= $this->returnMobileLink('验收', 'javascript:;', 'layui-btn-disabled');
                    $asArr[$k]['checkRepair'] .= '</div>';
                    //$asArr[$k]['checkRepair'] = $this->returnMobileLink('验收', $menuData['actionurl'] . '?repid=' . $v['repid'], '');
                }
            } else {

            }
        }
        $result['page'] = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result['rows'] = $asArr;
        $result['code'] = 200;
        return $result;
    }

    /*
     * 维修处理页面
     */
    public function get_repair_lists()
    {
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $limit = I('post.limit') ? I('post.limit') : C('PAGE_NUMS');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'DESC';
        $sort = I('post.sort');
        $search = I('POST.search');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $where['A.status'][0] = 'IN';
        $where['A.status'][1][] = C('REPAIR_HAVE_OVERHAULED');//已检修/配件待出库
        $where['A.status'][1][] = C('REPAIR_AUDIT');//审核中
        $where['A.status'][1][] = C('REPAIR_MAINTENANCE');//维修中
        $where['B.hospital_id'] = $hospital_id;
        $where['B.departid'] = array('IN', $departids);
        if (!session('isSuper')) {
            $where['A.response'] = session('username');
        }
        switch ($sort) {
            case 'applicant_time':
                $sort = 'A.applicant_time';
                break;
            case 'status':
                $sort = 'A.status';
                break;
            default:
                $sort = 'A.applicant_time';
                break;
        }
        if ($search) {
            $map['B.assets'] = array('like', '%' . $search . '%');
            $map['B.assnum'] = array('like', '%' . $search . '%');
            $map['B.model'] = array('like', '%' . $search . '%');
            $map['B.brand'] = array('like', '%' . $search . '%');
            $map['C.department'] = array('like', '%' . $search . '%');
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $where['B.is_delete'] = '0';
        $join[0] = ' LEFT JOIN __ASSETS_INFO__ AS B ON A.assid = B.assid';
        $join[1] = ' LEFT JOIN __DEPARTMENT__ AS C ON B.departid = C.departid';
        $total = $this->DB_get_count_join('repair', 'A', $join, $where);
        $fields = 'A.repid,A.assid,A.applicant as appUser,A.applicant_time as appTime,A.repnum,A.status,A.overdate,A.response,A.response_date,A.approve_status,A.repair_type,B.assets,B.assnum,B.model,C.department';
        $asArr = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asArr) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $acceptMenu = get_menu($this->MODULE, 'Repair', 'accept');//检修
        $startMenu = get_menu($this->MODULE, 'Repair', 'startRepair');//继续维修
        //当前用户可验收科室
        $app_where = $part_where = $rep_where = $where;
        $app_where['A.status'] = C('REPAIR_AUDIT');
        $part_where['A.status'] = C('REPAIR_HAVE_OVERHAULED');
        $app_nums = $this->DB_get_count_join('repair', 'A', $join, $app_where);
        $part_nums = $this->DB_get_count_join('repair', 'A', $join, $part_where);
        $rep_nums = $total-$app_nums-$part_nums;
        foreach ($asArr as $k => $v) {
            $asArr[$k]['appTime'] = getHandleMinute($v['appTime']);
            $asArr[$k]['response_date'] = getHandleMinute($v['response_date']);
            $asArr[$k]['overdate'] = getHandleMinute($v['overdate']);
            switch ($v['repair_type']) {
                case C('REPAIR_TYPE_IS_STUDY'):
                    $asArr[$k]['repair_type_name'] = '<span style="color:#5FB878;">'.C('REPAIR_TYPE_IS_STUDY_NAME').'</span>';
                    break;
                case C('REPAIR_TYPE_IS_GUARANTEE'):
                    $asArr[$k]['repair_type_name'] = '<span style="color:#5FB878;">'.C('REPAIR_TYPE_IS_GUARANTEE_NAME').'</span>';
                    break;
                case C('REPAIR_TYPE_THIRD_PARTY'):
                    $asArr[$k]['repair_type_name'] = '<span style="color:#FFB800;">'.C('REPAIR_TYPE_THIRD_PARTY_NAME').'</span>';
                    break;
                case C('REPAIR_TYPE_IS_SCENE'):
                    $asArr[$k]['repair_type_name'] = '<span style="color:#1E9FFF;">'.C('REPAIR_TYPE_IS_SCENE_NAME').'</span>';
                    break;
                default:
                    $asArr[$k]['repair_type_name'] = '未知';
                    break;
            }
            switch ($v['status']) {
                case C('REPAIR_RECEIPT'):
                    //已接单待检修
                    if ($acceptMenu) {
                        $asArr[$k]['operation'] = $this->returnMobileLink('检修', $acceptMenu['actionurl'] . '?repid=' . $v['repid'] . '&action=overhaul', ' layui-btn accept');
                    } else {
                        $asArr[$k]['operation'] = $this->returnMobileLink('检修', '', 'layui-btn layui-btn-primary nourl');
                    }
                    break;
                case C('REPAIR_HAVE_OVERHAULED'):
                    //已检修/配件待出库
                    $asArr[$k]['operation'] = $this->returnMobileLink('配件出库中', 'javascript:;', 'layui-btn layui-btn-primary nourl');
                    break;
                case C('REPAIR_AUDIT'):
                    //审核中
                    $asArr[$k]['operation'] = $this->returnMobileLink('审核中', 'javascript:;', 'layui-btn layui-btn-primary nourl');
                    break;
                case C('REPAIR_MAINTENANCE'):
                    //继续维修
                    if ($startMenu) {
                        $asArr[$k]['operation'] = $this->returnMobileLink('处理', $startMenu['actionurl'] . '?repid=' . $v['repid'], ' layui-btn-normal startRepair');
                    } else {
                        $asArr[$k]['operation'] = $this->returnMobileLink('处理', '', '');
                    }
                    break;
            }
        }
        $tips = '<img src="/Public/mobile/images/icon/tips.png" style="margin-bottom: 4px;width: 17px;margin-right: 5px;"></img>审批中 ' . $app_nums . ' 台；待处理 <span style="color:red;">' . $rep_nums . '</span> 台；待配件出库 ' . $part_nums . ' 台';
        $result['page'] = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result['rows'] = $asArr;
        $result['code'] = 200;
        $result['tips'] = $tips;
        return $result;
    }

    /*
   * 上传文件
   * */
    public function uploadfile()
    {
        if (I('post.zm' == 'canvas')) {
            $fin = I('post.filename');
            $_FILES['file']['name'] = $fin;
            $ty = explode('.', $fin);
            $_FILES['file']['ext'] = $ty[1];
        }
        //上传设备图片
        $Tool = new ToolController();
        //设置文件类型
        $type = array('jpg', 'png', 'bmp', 'jpeg', 'gif');
        //报告保存地址
        $dirName = C('UPLOAD_DIR_REPAIR_NAME');
        //上传文件
        $base64 = I('POST.base64');
        if ($base64) {
            $upload = $Tool->base64imgsave($base64, $dirName);
        } else {
            $upload = $Tool->upFile($type, $dirName);
        }
        if ($upload['status'] == C('YES_STATUS')) {
            $result['status'] = 1;
            $result['file_url'] = $upload['src'];
            $result['file_name'] = $upload['formerly'];
            $result['file_type'] = $upload['ext'];
            $result['save_name'] = $upload['title'];
            $result['thisDateTime'] = date('Ymd', time());
            $result['msg'] = '上传成功';
            $size = round($upload['size'] / 1024 / 1024, 2);
            $result['file_size'] = $size;
        } else {
            $result['status'] = -1;
            $result['msg'] = '上传失败';
        }
        return $result;
    }

    /**
     * Notes:获取我的待接单任务
     */
    public function get_my_repair_orders($tasks)
    {
        //查询当前用户是否有验收权限
        $menu = get_menu('Repair', 'Repair', 'accept');
        $tasks['Repair_Repair_ordersLists']['nums'] = 0;
        $tasks['Repair_Repair_ordersLists']['lists'] = [];
        if (!$menu) {
            return $tasks;
        }
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $username = session('username');
        $is_super = session('isSuper');
        $where['B.is_delete'] = '0';
        $where['B.hospital_id'] = $hospital_id;
        $where['B.departid'] = array('in', $departids);
        $where['A.status'] = C('REPAIR_HAVE_REPAIRED');// 1 已报修待接单
        $fields = "A.repid,A.repnum,A.applicant,A.applicant_time,A.is_assign,A.assign_engineer,A.status,A.response,A.response_date,B.assets,B.assnum,B.departid,C.pic";
        $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $join[1] = "LEFT JOIN sb_user AS C ON A.applicant = C.username";
        $data = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', 'A.applicant_time DESC','');
//        var_dump(M()->getLastSql());
        if (!$data) {
            return $tasks;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k => $v) {
            if ($is_super != 1) {
                if ($v['is_assign'] == 1) {
                    if ($v['assign_engineer'] != $username) {
                        //不是指派给自己的
                        unset($data[$k]);
                        continue;
                    }
                }
            }
            $data[$k]['show_time'] = date('Y-m-d H:i', $v['applicant_time']);
            $data[$k]['department'] = $departname[$v['departid']]['department'];
            $data[$k]['tips_name'] = '';
            $data[$k]['url'] = $this->returnMobileLink('接单', $menu['actionurl'] . '?repid=' . $v['repid'], 'taskButtom layui-btn layui-btn-xs layui-bg-blue');
            $tasks['Repair_Repair_ordersLists']['nums'] += 1;
            $tasks['Repair_Repair_ordersLists']['lists'][] = $data[$k];
        }
        return $tasks;
    }

    /**
     * Notes:获取我的待检修任务
     */
    public function get_my_repair_overhauls($tasks)
    {
        //查询当前用户是否有验收权限
        $menu = get_menu('Repair', 'Repair', 'accept');
        $tasks['Repair_Repair_overhaul']['nums'] = 0;
        $tasks['Repair_Repair_overhaul']['lists'] = [];
        if (!$menu) {
            return $tasks;
        }
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $username = session('username');
        $is_super = session('isSuper');
        $where['B.hospital_id'] = $hospital_id;
        $where['B.departid'] = array('in', $departids);
        $where['A.status'] = C('REPAIR_RECEIPT');// 1 已接单待检修
        $where['B.is_delete'] = '0';
        $fields = "A.repid,A.repnum,A.applicant,A.applicant_time,A.is_assign,A.assign_engineer,A.status,A.response,A.response_date,B.assets,B.assnum,B.departid,C.pic";
        $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $join[1] = "LEFT JOIN sb_user AS C ON A.applicant = C.username";
        $data = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', 'A.applicant_time DESC','');
        if (!$data) {
            return $tasks;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k => $v) {
            if ($is_super != 1) {
                if ($v['response'] != $username) {
                    //不是自己接的单
                    unset($data[$k]);
                    continue;
                }
            }
            $data[$k]['show_time'] = date('Y-m-d H:i', $v['applicant_time']);
            $data[$k]['department'] = $departname[$v['departid']]['department'];
            $data[$k]['tips_name'] = '';
            $data[$k]['url'] = $this->returnMobileLink('检修', $menu['actionurl'] . '?repid=' . $v['repid'] . '&action=overhaul', 'taskButtom layui-btn layui-btn-xs layui-bg-cyan');
            $tasks['Repair_Repair_overhaul']['nums'] += 1;
            $tasks['Repair_Repair_overhaul']['lists'][] = $data[$k];
        }
        return $tasks;
    }

    //
    public function get_my_repair_addApprove($tasks)
    {
        $hospital_id = session('current_hospitalid');
        $departids = session('departid');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        //查询当前用户是否有维修审批权限
        $repair_menu = get_menu('Repair', 'Repair', 'addApprove');
        $tasks['Pub_Public_approve']['nums'] = 0;
        $tasks['Pub_Public_approve']['lists'] = [];
        if ($repair_menu) {
            $repair_where['B.departid'] = array('IN', $departids);
            $repair_where['A.all_approver'] = array('LIKE', '%/' . session('username') . '/%');
            $repair_where['B.hospital_id'] = $hospital_id;
            $repair_where['A.approve_status'] = array('EQ', C('REPAIR_IS_NOTCHECK'));
            $repair_where['B.is_delete'] = '0';
            $repair_where['A.status'] = C('REPAIR_AUDIT');
            $fields = "A.repid,A.repnum,A.applicant,A.applicant_time,A.is_assign,A.assign_engineer,A.status,A.response,A.response_date,A.approve_status,A.current_approver,A.not_complete_approver,B.assets,B.assnum,B.departid,C.pic";
            $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
            $join[1] = "LEFT JOIN sb_user AS C ON A.applicant = C.username";
            $data = $this->DB_get_all_join('repair', 'A', $fields, $join, $repair_where, '', 'A.applicant_time DESC','');
            foreach ($data as $k => $v) {
                $data[$k]['show_time'] = date('Y-m-d H:i', $v['applicant_time']);
                $data[$k]['tips_name'] = '维修申请';
                $data[$k]['department'] = $departname[$v['departid']]['department'];
                $current_approver = explode(',', $v['current_approver']);
                //$not_complete_approver = explode(',', $v['not_complete_approver']);
                if ($v['approve_status'] == 0 && in_array(session('username'), $current_approver)) {
                    $data[$k]['url'] = $this->returnMobileLink('审批', $repair_menu['actionurl'] . '?repid=' . $v['repid'], 'taskButtom layui-btn layui-btn-xs layui-btn-warm');
                    $tasks['Pub_Public_approve']['nums'] += 1;
                    $tasks['Pub_Public_approve']['lists'][] = $data[$k];
                }
            }
        }
        //报废审核
        $Scrapmenu = get_menu('Assets', 'Scrap', 'examine');
        if ($Scrapmenu) {
            if ($departids) {
                $Scrap_where['B.departid'] = array('IN', $departids);
            }
            if ($hospital_id) {
                $Scrap_where['B.hospital_id'] = $hospital_id;
            }
            $Scrap_where['B.status'] = 5;
            $Scrap_where['B.is_delete'] = '0';
            $Scrap_fields = 'A.scrid,A.scrapnum,A.scrapdate,A.apply_user,A.scrap_reason,A.approve_status,A.current_approver,A.complete_approver,A.not_complete_approver,A.all_approver,B.assets,B.catid,B.assid,B.assnum,B.departid,B.opendate,B.buy_price,B.storage_date,B.expected_life,A.add_time,A.add_user,C.pic';
            $Scrap_join[0] = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
            $Scrap_join[1] = 'LEFT JOIN sb_user AS C ON A.add_user = C.username';
            $total = $this->DB_get_count_join('assets_scrap', 'A', $Scrap_join, $Scrap_where);
            $examine = $this->DB_get_all_join('assets_scrap', 'A', $Scrap_fields, $Scrap_join, $Scrap_where,'','','');
            if ($examine) {
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                foreach ($examine as $key => &$value) {
                    $examine[$key]['department'] = $departname[$value['departid']]['department'];
                    $examine[$key]['show_time'] = $value['add_time'];
                    $examine[$key]['applicant'] = $value['add_user'];
                    $examine[$key]['tips_name'] = '报废申请';
                    $current_approver = explode(',', $value['current_approver']);
                    //$not_complete_approver = explode(',', $value['not_complete_approver']);
                    if ($value['approve_status'] == 0 && in_array(session('username'), $current_approver)) {
                        $examine[$key]['url'] = $this->returnMobileLink('审批', $Scrapmenu['actionurl'] . '?scrid=' . $value['scrid'], ' taskButtom layui-btn layui-btn-xs layui-btn-warm');
                        $tasks['Pub_Public_approve']['nums'] += 1;
                        $tasks['Pub_Public_approve']['lists'][] = $value;
                    }

                }
            }
        }
        //查询当前用户是否有转科审批权限
        $transfer_menu = get_menu('Assets', 'Transfer', 'approval');
        if ($transfer_menu) {
            $transfer_where['A.approve_status'] = 0;
            $transfer_where['B.hospital_id'] = $hospital_id;
            $transfer_where['A.all_approver'] = array('like', '%/' . session('username') . '/%');
            $map['A.tranout_departid'] = array('in', $departids);
            $map['A.tranin_departid'] = array('in', $departids);
            $map['_logic'] = 'or';
            $transfer_where['_complex'] = $map;
            $transfer_where['B.is_delete'] = '0';
            //根据条件统计符合要求的数量
            $join[0] = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
            $join[1] = 'LEFT JOIN sb_user AS C ON A.applicant_user = C.username';
            $fields = 'A.*,B.assnum,B.assets,B.model,C.pic';
            $asArr = $this->DB_get_all_join('assets_transfer', 'A', $fields, $join, $transfer_where, '','','');
            foreach ($asArr as $k => $v) {
                $asArr[$k]['show_time'] = date('Y-m-d H:i', strtotime($v['applicant_time']));
                $asArr[$k]['department'] = $departname[$v['tranout_departid']]['department'];
                $asArr[$k]['applicant'] = $v['applicant_user'];
                $asArr[$k]['tips_name'] = '转科申请';
                $current_approver = explode(',', $v['current_approver']);
                //$not_complete_approver = explode(',', $v['not_complete_approver']);
                if ($v['approve_status'] == 0 && in_array(session('username'), $current_approver)) {
                    $asArr[$k]['url'] = $this->returnMobileLink('审批', $transfer_menu['actionurl'] . '?atid=' . $v['atid'], ' taskButtom layui-btn-xs layui-btn-warm');
                    $tasks['Pub_Public_approve']['nums'] += 1;
                    $tasks['Pub_Public_approve']['lists'][] = $asArr[$k];
                }
            }
        }
        //借出部门审批
        $departApproveBorrowMenu = get_menu('Assets', 'Borrow', 'departApproveBorrow');
        //设备科审批
        $assetsApproveBorrowMenu = get_menu('Assets', 'Borrow', 'assetsApproveBorrow');
        if ($departApproveBorrowMenu || $assetsApproveBorrowMenu) {
            $borrow_where['A.examine_status'] = array('EQ', C('APPROVE_STATUS'));//未审核状态

            //有审批权限的设备
            $backAssid = [];
            //负责人的可审批设备
            $managerApproveAssid = [];
            //设备科的可审批设备
            $assetsApproveAssid = [];

            if ($departApproveBorrowMenu) {
                //有借出部门审批权限
                $managerWhere['departid'] = array('in', $departids);
                $managerWhere['manager'] = array('EQ', session('username'));
                $managerWhere['hospital_id'] = $hospital_id;
                $manager = $this->DB_get_all('department', 'departid,manager', $managerWhere);
                if ($manager) {
                    //负责的科室
                    $managerDepairtid = [];
                    foreach ($manager as $managerV) {
                        $managerDepairtid[] = $managerV['departid'];
                    }
                    $assetsDepartWhere['departid'] = array('IN', $managerDepairtid);
                    $assetsDepartWhere['is_delete'] = '0';
                    $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
                    if ($assetsDepart) {
                        foreach ($assetsDepart as &$assetsDepartV) {
                            $backAssid[] = $assetsDepartV['assid'];
                            $managerApproveAssid[$assetsDepartV['assid']] = true;
                        }
                    }
                }
            }

            if ($assetsApproveBorrowMenu) {
                //有设备科审批权限
                $assetsDepartWhere['departid'] = array('IN', $departids);
                $assetsDepartWhere['is_delete'] = '0';
                $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
                if ($assetsDepart) {
                    foreach ($assetsDepart as &$assetsDepartV) {
                        $backAssid[] = $assetsDepartV['assid'];
                        $assetsApproveAssid[$assetsDepartV['assid']] = true;
                    }
                }
            }

            if ($backAssid) {
                $backAssid = array_unique($backAssid);
                $assetsWhere['assid'][] = array('IN', $backAssid);
                //管理员默认情况下的话只能看到自己工作的医院下的设备
                $assetsWhere['hospital_id'] = $hospital_id;
                $assetsWhere['is_delete'] = '0';
                $assets = $this->DB_get_all('assets_info', 'assid', $assetsWhere);
                if ($assets) {
                    $assetsAssid = [];
                    foreach ($assets as &$assetsAssidV) {
                        $assetsAssid[] = $assetsAssidV['assid'];
                    }
                    $borrow_where['A.assid'][] = array('IN', $assetsAssid);
                    //获取审批列表信息
                    $join = "LEFT JOIN sb_department AS B ON A.apply_departid = B.departid";
                    $fileds = 'A.borid,A.assid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,A.borrow_reason,A.estimate_back,A.status,A.examine_status';
                    $data = $this->DB_get_all_join('assets_borrow', 'A', $fileds, $join, $borrow_where, '','','');
                    if ($data) {
                        //获取设备基本信息
                        $assid = [];
                        $userid = [];
                        foreach ($data as &$dataV) {
                            $assid[] = $dataV['assid'];
                            $userid[] = $dataV['apply_userid'];
                        }
                        $assetsWhere = [];
                        $assetsWhere['assid'] = array('IN', $assid);
                        $fileds = 'departid,assets,assnum,brand,model,status,assid';
                        $assetsWhere['is_delete'] = '0';
                        $assets = $this->DB_get_all('assets_info', $fileds, $assetsWhere);
                        $assetsData = [];
                        foreach ($assets as &$assetsV) {
                            $assetsData[$assetsV['assid']]['department'] = $departname[$assetsV['departid']]['department'];
                            $assetsData[$assetsV['assid']]['assets'] = $assetsV['assets'];
                        }
                        //获取对应的申请人名称
                        $userWhere['userid'] = array('IN', $userid);
                        $user = $this->DB_get_all('user', 'userid,username,pic', $userWhere);
                        $userData = [];
                        foreach ($user as &$userV) {
                            $userData[$userV['userid']]['username'] = $userV['username'];
                            $userData[$userV['userid']]['pic'] = $userV['pic'];
                        }
                        foreach ($data as &$dataV) {
                            $dataV['department'] = $assetsData[$dataV['assid']]['department'];
                            $dataV['assets'] = $assetsData[$dataV['assid']]['assets'];
                            $dataV['applicant'] = $userData[$dataV['apply_userid']]['username'];
                            $dataV['pic'] = $userData[$dataV['apply_userid']]['pic'];
                            $dataV['tips_name'] = '借调申请';
                            $dataV['show_time'] = date('Y-m-d H:i', $dataV['apply_time']);
                            //查询审批历史
                            $apps = $this->DB_get_all('assets_borrow_approve', '', array('borid' => $dataV['borid']), '', 'level,approve_time asc');
                            if ((!$apps && $managerApproveAssid[$dataV['assid']]) or session('isSuper') == C('YES_STATUS')) {
                                //未审批,是第一个审批人
                                $dataV['url'] = $this->returnMobileLink('审批', $departApproveBorrowMenu['actionurl'] . '?assid=' . $dataV['assid'] . '&borid=' . $dataV['borid'], ' taskButtom layui-btn-xs layui-btn-warm');
                                $tasks['Pub_Public_approve']['nums'] += 1;
                                $tasks['Pub_Public_approve']['lists'][] = $dataV;
                            } else if ($apps && $assetsApproveAssid[$dataV['assid']]) {
                                //已审批一次，是设备科审批人
                                $dataV['url'] = $this->returnMobileLink('审批', $departApproveBorrowMenu['actionurl'] . '?assid=' . $dataV['assid'] . '&borid=' . $dataV['borid'], ' taskButtom layui-btn-xs layui-btn-warm');
                                $tasks['Pub_Public_approve']['nums'] += 1;
                                $tasks['Pub_Public_approve']['lists'][] = $dataV;
                            }
                        }
                    }
                }
            }
        }
        return $tasks;
    }

    /**
     * Notes: 获取待验收的维修任务列表和数量
     */
    public function get_my_repair_examines($tasks)
    {
        //查询当前用户是否有验收权限
        $menu = get_menu('Repair', 'Repair', 'checkRepair');
        $tasks['Repair_Repair_examine']['nums'] = 0;
        $tasks['Repair_Repair_examine']['lists'] = [];
        if (!$menu) {
            return $tasks;
        }
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $where['B.hospital_id'] = $hospital_id;
        $where['B.departid'] = array('in', $departids);
        $where['A.status'] = C('REPAIR_MAINTENANCE_COMPLETION');// 1 已完成待验收
        $where['B.is_delete'] = '0';
        $fields = "A.repid,A.repnum,A.applicant,A.applicant_time,A.is_assign,A.assign_engineer,A.status,A.response,A.response_date,B.assets,B.assnum,B.departid,C.pic";
        $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $join[1] = "LEFT JOIN sb_user AS C ON A.applicant = C.username";
        $data = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', 'A.applicant_time DESC','');
        if (!$data) {
            return $tasks;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k => $v) {
            $data[$k]['show_time'] = date('Y-m-d H:i', $v['applicant_time']);
            $data[$k]['department'] = $departname[$v['departid']]['department'];
            $data[$k]['tips_name'] = '维修验收';
            $data[$k]['url'] = $this->returnMobileLink('验收', $menu['actionurl'] . '?repid=' . $v['repid'], 'taskButtom layui-btn layui-btn-xs');
            $tasks['Repair_Repair_examine']['nums'] += 1;
            $tasks['Repair_Repair_examine']['lists'][] = $data[$k];
        }
        return $tasks;
    }

    /**
     * Notes: 获取待验收的转科任务列表和数量
     */
    public function get_my_transfer_examines($tasks)
    {
        //查询当前用户是否有验收权限
        $menu = get_menu('Assets', 'Transfer', 'check');
        $tasks['Assets_Transfer_checkLists']['nums'] = 0;
        $tasks['Assets_Transfer_checkLists']['lists'] = [];
        if (!$menu) {
            return $tasks;
        }
        $hospital_id = session('current_hospitalid');
        $where['B.hospital_id'] = $hospital_id;
        $departids = session('departid');
        if (!$departids) {
            return $tasks;
        }
        $where['A.tranin_departid'] = array('in', $departids);
        $where['A.approve_status'][0] = 'IN';
        $where['A.approve_status'][1][] = C('STATUS_APPROE_UNWANTED');//不需审批
        $where['B.is_delete'] = '0';
        $where['A.approve_status'][1][] = C('STATUS_APPROE_SUCCESS');//审批通过
        $where['A.is_check'] = C('TRANSFER_IS_NOTCHECK');//未验收
        //根据条件统计符合要求的数量
        $join[0] = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $join[1] = 'LEFT JOIN sb_department AS C ON A.tranout_departid = C.departid';
        $join[2] = 'LEFT JOIN sb_user AS D ON A.applicant_user = D.username';
        $total = $this->DB_get_count_join('assets_transfer', 'A', $join, $where);
        $fields = 'A.*,B.assnum,B.assorignum,B.assets,B.model,B.catid,B.buy_price,B.pic_url,D.pic';
        $asArr = $this->DB_get_all_join('assets_transfer', 'A', $fields, $join, $where,'','','');
        if (!$asArr) {
            return $tasks;
        }

        //查询当前用户是否有权验收
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        //查询是否开启了转科审批
        foreach ($asArr as $k => $v) {
            $asArr[$k]['department'] = $departname[$v['tranin_departid']]['department'];
            $asArr[$k]['tips_name'] = '转科验收';
            $asArr[$k]['show_time'] = date('Y-m-d H:i', strtotime($v['applicant_time']));
            $asArr[$k]['applicant'] = $v['applicant_user'];
            $asArr[$k]['url'] = $this->returnMobileLink('验收', $menu['actionurl'] . '?atid=' . $v['atid'], 'taskButtom layui-btn layui-btn-xs');
            $tasks['Assets_Transfer_checkLists']['nums'] += 1;
            $tasks['Assets_Transfer_checkLists']['lists'][] = $asArr[$k];
        }
        return $tasks;
    }

    /**
     * Notes: 获取维修待处理的维修任务列表和数量
     */
    public function get_my_repair_deal_with($tasks)
    {
        //查询当前用户是否有验收权限
        $menu = get_menu('Repair', 'Repair', 'getRepairLists');
        $tasks['repair_deal_with']['nums'] = 0;
        $tasks['repair_deal_with']['lists'] = [];
        if (!$menu) {
            return $tasks;
        }
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $where['A.status'][0] = 'IN';
        $where['A.status'][1][] = C('REPAIR_HAVE_OVERHAULED');//已检修/配件待出库
        $where['A.status'][1][] = C('REPAIR_AUDIT');//审核中
        $where['A.status'][1][] = C('REPAIR_MAINTENANCE');//维修中
        $where['B.hospital_id'] = $hospital_id;
        $where['B.is_delete'] = '0';
        $where['B.departid'] = array('IN', $departids);
        if (!session('isSuper')) {
            $where['A.response'] = session('username');
        }
        $join[0] = ' LEFT JOIN __ASSETS_INFO__ AS B ON A.assid = B.assid';
        $join[1] = ' LEFT JOIN __DEPARTMENT__ AS C ON B.departid = C.departid';
        $join[2] = ' LEFT JOIN __USER__ AS D ON D.username = A.applicant';
        $total = $this->DB_get_count_join('repair', 'A', $join, $where);
        $fields = 'A.repid,A.assid,A.applicant as appUser,A.applicant_time as appTime,A.repnum,A.status,A.overdate,A.response,A.response_date,A.approve_status,B.assets,B.assnum,B.model,C.department,D.pic as pic';
        $asArr = $this->DB_get_all_join('repair', 'A', $fields, $join, $where,'','','');
        if (!$asArr) {
            return $tasks;
        }
        $startMenu = get_menu($this->MODULE, 'Repair', 'startRepair');//继续维修
        //当前用户可验收科室
        foreach ($asArr as $k => $v) {
            $asArr[$k]['show_time'] = date('Y-m-d H:i', $v['appTime']);
            $asArr[$k]['department'] = $v['department'];
            $asArr[$k]['tips_name'] = '';
            //$asArr[$k]['assets'] = $assetsData[$v['assid']]['assets'];
            $asArr[$k]['applicant'] = $v['appUser'];
            $asArr[$k]['pic'] = $v['pic'];
            switch ($v['status']) {
                case C('REPAIR_AUDIT'):
                    //审核中layui-btn layui-btn-primary nourl
                    $asArr[$k]['url'] = $this->returnMobileLink('审核中', 'javascript:;', 'taskButtom layui-btn layui-btn-xs layui-btn-primary');
                    break;
                case C('REPAIR_MAINTENANCE'):
                    //继续维修
                    if ($startMenu) {
                        $asArr[$k]['url'] = $this->returnMobileLink('处理', $startMenu['actionurl'] . '?repid=' . $v['repid'], ' taskButtom layui-btn layui-btn-xs layui-bg-blue');
                    } else {
                        $asArr[$k]['url'] = $this->returnMobileLink('处理', '', 'taskButtom layui-btn layui-btn-xs layui-bg-blue');
                    }
                    break;
            }
            if ($v['status'] == C('REPAIR_MAINTENANCE')) {
                $tasks['repair_deal_with']['nums'] += 1;
                $tasks['repair_deal_with']['lists'][] = $asArr[$k];
            } else {
                //$tasks['repair_deal_with']['nums'] += 1;
            }
        }
        return $tasks;
    }

    /**
     * Notes: 获取借入任务列表和数量
     */
    public function get_my_Assets_Borrow($tasks)
    {
        $menu = get_menu('Assets', 'Borrow', 'borrowInCheckList');
        $tasks['Assets_Borrow']['nums'] = 0;
        $tasks['Assets_Borrow']['lists'] = [];
        if (!$menu) {
            return $tasks;
        }
        $where['A.status'] = array('EQ', C('BORROW_STATUS_BORROW_IN'));
        if (session('isSuper') != C('YES_STATUS')) {
            $where['A.apply_departid'] = array('IN', session('job_departid'));
        }
        $where['B.hospital_id'] = session('current_hospitalid');
        $where['B.is_delete'] = '0';
        //获取审批列表信息
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fileds = 'A.borid,A.assid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,A.borrow_reason,A.estimate_back,A.status,A.examine_status,A.borrow_in_time,B.departid,B.assets,B.assnum,B.brand,B.model,B.status AS a_status';
        $data = $this->DB_get_all_join('assets_borrow', 'A', $fileds, $join, $where,'','','');
        if (!$data) {
            return $tasks;
        }
        //获取设备基本信息
        $assid = [];
        $userid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $borid[] = $dataV['borid'];
            $assid[] = $dataV['assid'];
            $userid[] = $dataV['apply_userid'];
        }
        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        $user = $this->DB_get_all('user', 'userid,username,pic', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
            $userData[$userV['userid']]['pic'] = $userV['pic'];
        }
        $borrowInCheckMenu = get_menu('Assets', 'Borrow', 'borrowInCheck');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$dataV) {
            $dataV['pic'] = $userData[$dataV['apply_userid']]['pic'];
            $dataV['department'] = $departname[$dataV['departid']]['department'];
            $dataV['tips_name'] = '';
            $dataV['estimate_back'] = getHandleMinute($dataV['estimate_back']);
            $dataV['show_time'] = getHandleMinute($dataV['apply_time']);
            $dataV['applicant'] = $userData[$dataV['apply_userid']]['username'];
            if ($borrowInCheckMenu) {
                $dataV['url'] = $this->returnMobileLink('借入', $borrowInCheckMenu['actionurl'] . '?borid=' . $dataV['borid'], ' taskButtom layui-btn layui-btn-xs layui-bg-blue');
            }
            $tasks['Assets_Borrow']['nums'] += 1;
            $tasks['Assets_Borrow']['lists'][] = $dataV;
        }
        return $tasks;
    }

    /*
    获取逾期列表和数量
     */
    public function get_my_Assets_Borrow_Reminder($tasks)
    {
        $menu = get_menu('Assets', 'Borrow', 'giveBackCheckList');
        $tasks['Assets_Expired']['nums'] = 0;
        $tasks['Assets_Expired']['lists'] = [];
        if (!$menu) {
            return $tasks;
        }
        $where['A.status'] = array('EQ', C('BORROW_STATUS_GIVE_BACK'));
        if (!session('departid')) {
            return $tasks;
        }
        $assetsDepartWhere['departid'] = array('IN', session('departid'));
        $assetsDepartWhere['hospital_id'] = session('current_hospitalid');
        $assetsDepartWhere['is_delete'] = '0';
        $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
        if (!$assetsDepart) {
            return $tasks;
        }
        $backAssid = [];
        foreach ($assetsDepart as &$assetsDepartV) {
            $backAssid[] = $assetsDepartV['assid'];
        }
        $where['A.assid'] = array('IN', $backAssid);

        $where['estimate_back'] = ['LT', time()];
        //获取审批列表信息
        $fileds = 'B.assnum,B.assid,B.assets,A.borid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,
        A.borrow_reason,A.estimate_back,A.status,A.examine_status,A.borrow_in_time,A.supplement';
        $where['B.is_delete'] = '0';
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $data = $this->DB_get_all_join('assets_borrow', 'A', $fileds, $join, $where,'','','');
        if (!$data) {
            return $tasks;
        }
        //获取设备基本信息
        $userid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $userid[] = $dataV['apply_userid'];
            $borid[] = $dataV['borid'];
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";

        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        $user = $this->DB_get_all('user', 'userid,username,pic', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
            $userData[$userV['userid']]['pic'] = $userV['pic'];
        }

        //获取附属设备明细
        $subsidiaryWhere['borid'] = ['IN', $borid];
        $subsidiaryWhere['A.is_delete'] = '0';
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price,D.borid';
        $subsidiary = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join, $subsidiaryWhere,'','','');
        $subsidiaryData = [];
        if ($subsidiary) {
            foreach ($subsidiary as &$subV) {
                $subsidiaryData[$subV['borid']][] = $subV;
            }
        }
        foreach ($data as &$dataV) {
            $dataV['department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['show_time'] = getHandleMinute($dataV['estimate_back']);
            $dataV['tips_name'] = '';
            $dataV['applicant'] = $userData[$dataV['apply_userid']]['username'];
            $dataV['pic'] = $userData[$dataV['apply_userid']]['pic'];
            $dataV['url'] = $this->returnMobileLink('催还', $menu['actionurl'] . '?action=showReminderList&borid=' . $dataV['borid'], ' taskButtom layui-btn layui-btn-xs layui-bg-blue');
            $tasks['Assets_Expired']['nums'] += 1;
            $tasks['Assets_Expired']['lists'][] = $dataV;
        }
        return $tasks;
    }

    /**
     * Notes: 获取归还任务列表和数量
     */
    public function get_my_Assets_restoration($tasks)
    {
        $menu = get_menu('Assets', 'Borrow', 'giveBackCheckList');
        $tasks['Assets_restoration']['nums'] = 0;
        $tasks['Assets_restoration']['lists'] = [];
        if (!$menu) {
            return $tasks;
        }
        $where['A.status'] = array('EQ', C('BORROW_STATUS_GIVE_BACK'));
        if (!session('departid')) {
            return $tasks;
        }
        $assetsDepartWhere['departid'] = array('IN', session('departid'));
        $assetsDepartWhere['hospital_id'] = session('current_hospitalid');
        $assetsDepartWhere['is_delete'] = '0';
        $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
        if (!$assetsDepart) {
            return $tasks;
        }
        $backAssid = [];
        foreach ($assetsDepart as &$assetsDepartV) {
            $backAssid[] = $assetsDepartV['assid'];
        }
        $where['A.assid'] = array('IN', $backAssid);
        //获取审批列表信息
        $fileds = 'B.assnum,B.assid,B.assets,A.borid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,
        A.borrow_reason,A.estimate_back,A.status,A.examine_status,A.borrow_in_time,A.supplement';
        $where['B.is_delete'] = '0';
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $data = $this->DB_get_all_join('assets_borrow', 'A', $fileds, $join, $where,'','','');

        if (!$data) {
            return $tasks;
        }
        //获取设备基本信息
        $userid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $userid[] = $dataV['apply_userid'];
            $borid[] = $dataV['borid'];
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";

        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        $user = $this->DB_get_all('user', 'userid,username,pic', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
            $userData[$userV['userid']]['pic'] = $userV['pic'];
        }

        //获取附属设备明细
        $subsidiaryWhere['borid'] = ['IN', $borid];
        $subsidiaryWhere['A.is_delete'] = '0';
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price,D.borid';
        $subsidiary = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join, $subsidiaryWhere,'','','');
        $subsidiaryData = [];
        if ($subsidiary) {
            foreach ($subsidiary as &$subV) {
                $subsidiaryData[$subV['borid']][] = $subV;
            }
        }
        $giveBackCheckMenu = get_menu('Assets', 'Borrow', 'giveBackCheck');
        foreach ($data as &$dataV) {
            $dataV['department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['show_time'] = getHandleMinute($dataV['borrow_in_time']);
            $dataV['tips_name'] = '';
            $dataV['applicant'] = $userData[$dataV['apply_userid']]['username'];
            $dataV['pic'] = $userData[$dataV['apply_userid']]['pic'];
            if ($giveBackCheckMenu) {
                $dataV['url'] = $this->returnMobileLink('归还', $giveBackCheckMenu['actionurl'] . '?borid=' . $dataV['borid'], ' taskButtom layui-btn layui-btn-xs layui-bg-blue');
            }
            $tasks['Assets_restoration']['nums'] += 1;
            $tasks['Assets_restoration']['lists'][] = $dataV;
        }
        return $tasks;
    }

    /*
    获取待处理质控事件列表和数量
     */
    public function get_my_Qualities($tasks)
    {
        $menu = get_menu('Qualities', 'Quality', 'setQualityDetail');
        $tasks['Qualities']['nums'] = 0;
        $tasks['Qualities']['lists'] = [];
        if (!$menu) {
            return $tasks;
        }
        $hospital_id = session('current_hospitalid');
        if (session('isSuper')) {
            $where['is_start'] = 1;
        } elseif (session('is_supplier') == C('YES_STATUS')) {
            $where['is_start'] = 1;
            if (session('olsid') > 0) {
                $assets_f = $this->DB_get_one('assets_factory', 'GROUP_CONCAT(assid) AS assid', ['ols_supid' => session('olsid')]);
                if ($assets_f['assid']) {
                    $where['A.assid'] = ['IN', $assets_f['assid']];
                } else {
                    return $tasks;
                }
            } else {
               return $tasks;
            }
        } else {
            $where['is_start'] = 1;
            $where['A.userid'] = ['EQ', session('userid')];
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = ['EQ', $hospital_id];
        } else {
            $where['B.hospital_id'] = ['EQ', session('current_hospitalid')];
        }
        $where['B.is_delete'] = '0';
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid LEFT JOIN sb_user AS C ON A.username = C.username";
        $fields = "A.qsid,A.plan_name,A.username,A.is_cycle,A.period,A.is_start,A.do_date,A.addtime,A.start_date,B.assets,B.assnum,B.departid,B.model,A.keepdata,C.pic";
        $data = $this->DB_get_all_join('quality_starts', 'A', $fields, $join, $where,'','','');
        if (!$data) {
            return $tasks;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k => $v) {
            $data[$k]['show_time'] = $v['addtime'];
            $data[$k]['pic'] = $v['pic'];
            $data[$k]['applicant'] = $v['username'];
            $data[$k]['department'] = $departname[$v['departid']]['department'];
            $data[$k]['tips_name'] = '';
            $data[$k]['url'] = $this->returnMobileLink('检测', $menu['actionurl'] . '?qsid=' . $v['qsid'], 'taskButtom layui-btn layui-btn-xs  layui-bg-blue');
            $tasks['Qualities']['nums'] += 1;
            $tasks['Qualities']['lists'][] = $data[$k];
        }
        return $tasks;
    }
    /**
     * Notes: 获取待出库配件任务列表和数量
     */
    public function get_my_parts_outwares($tasks)
    {
        //查询当前用户是否有出库权限
        $menu = get_menu('Repair', 'RepairParts', 'partsOutWare');
        $tasks['Repair_RepairParts_partsOutWare']['nums'] = 0;
        $tasks['Repair_RepairParts_partsOutWare']['lists'] = [];
        if (!$menu) {
            return $tasks;
        }
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $whereP['hospital_id'] = $hospital_id;
        $whereP['status'] = 0;
        $reps = $this->DB_get_one('parts_outware_record', 'group_concat(repid) as repids,group_concat(outwareid) as outwareid', $whereP);
        if ($reps['repids']) {
            $respsidarr = explode(',', $reps['repids']);
            $outwareidarr = explode(',', $reps['outwareid']);
            $ids = [];
            foreach ($respsidarr as $k => $v) {
                $ids[$v] = $outwareidarr[$k];
            }
            $where['A.repid'] = array('in', $reps['repids']);
            $where['B.departid'] = array('in', $departids);
            $where['B.is_delete'] = '0';
            $where['A.status'] = '3';
            $fields = "A.repid,A.repnum,A.applicant,A.applicant_time,A.is_assign,A.assign_engineer,A.status,A.response,A.response_date,B.assets,B.assnum,B.departid,C.pic";
            $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
            $join[1] = "LEFT JOIN sb_user AS C ON A.applicant = C.username";
            $data = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', 'A.applicant_time DESC','');
            if (!$data) {
                return $tasks;
            }
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            foreach ($data as $k => $v) {
                $data[$k]['show_time'] = date('Y-m-d H:i', $v['applicant_time']);
                $data[$k]['department'] = $departname[$v['departid']]['department'];
                $data[$k]['tips_name'] = '';
                $data[$k]['url'] = $this->returnMobileLink('出库', $menu['actionurl'] . '?repid=' . $v['repid'], 'taskButtom layui-btn layui-btn-xs layui-bg-orange');
                $tasks['Repair_RepairParts_partsOutWare']['nums'] += 1;
                $tasks['Repair_RepairParts_partsOutWare']['lists'][] = $data[$k];
            }
        }
        return $tasks;
    }

    public function get_repair_info($repid)
    {
        if (!$repid) {
            return [];
        }
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $repinfo = $this->DB_get_one_join('repair', 'A', 'A.*,B.assets,B.assnum,B.departid,B.catid,B.model,B.brand', $join, array('A.repid' => $repid, 'B.is_delete' => '0'));
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $repinfo['department'] = $departname[$repinfo['departid']]['department'];
        return $repinfo;
    }

    /**
     * Notes: 获取配件库存
     */
    public function get_parts_stock($parts)
    {
        $hospital_id = session('current_hospitalid');
        $username = session('username');
        foreach ($parts as $k => $v) {
            $repid = $v['repid'];
            $where['hospital_id'] = $hospital_id;//所在医院
            $where['status'] = 0;//未出库
            $where['parts'] = $v['parts'];//名称
            if ($v['part_model']) {
                $where['parts_model'] = $v['part_model'];//型号
            }
            $where['repid'] = array(array('eq', 0), array('eq', $repid), 'or');//未出库
            $where['leader'] = array(array('eq', ''), array('eq', $username), 'or');//领用人
            $count = $this->DB_get_count('parts_inware_record_detail', $where);
            $parts[$k]['stock_num'] = $count;
            $parts[$k]['stock_not_enough'] = 0;
            if ($parts[$k]['stock_num'] < $parts[$k]['part_num']) {
                //库存不足
                $parts[$k]['stock_not_enough'] = 1;
            }
        }
        return $parts;
    }

    /*
     * 获取维修审批列表数据
     */
    public function get_repair_examine()
    {
        //查询是否有审批权限
        $menu = get_menu('Repair', 'Repair', 'repairApproveLists');
        if (!$menu) {
            $result['msg'] = '对不起，您没有获取维修审批列表的权限！';
            $result['rows'] = [];
            $result['total'] = 0;
            $result['code'] = 400;
            return $result;
        }
        $departids = session('departid');
        $order = I('POST.order') ? I('POST.order') : 'DESC';
        $sort = I('POST.sort');
        $hospital_id = session('current_hospitalid');

        $where['A.status'] = C('REPAIR_AUDIT');
        $where['A.approve_status'] = C('REPAIR_IS_NOTCHECK');
        $where['A.all_approver'] = array('LIKE', '%/' . session('username') . '/%');
        $where['B.hospital_id'] = $hospital_id;
        $where['B.departid'] = array('IN', $departids);
        switch ($sort) {
            case 'applicant_time':
                $sort = 'A.applicant_time';
                break;
            case 'department':
                $sort = 'C.department';
                break;
            default:
                $sort = 'A.applicant_time';
                break;
        }
        $where['B.is_delete'] = '0';
        $fields = "A.repid,A.repnum,A.applicant,A.applicant_time,A.current_approver,A.not_complete_approver,A.complete_approver,B.assid,B.assets,B.assnum,B.model,B.departid";
        $join[0] = 'LEFT JOIN sb_assets_info AS B ON A.assid=B.assid';
        $join[1] = 'LEFT JOIN sb_department AS C ON B.departid=C.departid';
        $total = $this->DB_get_count_join('repair', 'A', $join, $where);
        $data = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order,'');
        if (!$data) {
            $result['msg'] = '暂无需处理的维修审批流程';
            $result['rows'] = [];
            $result['total'] = 0;
            $result['code'] = 400;
            return $result;
        }
        //搜索查询
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $canApproval = get_menu('Repair', 'Repair', 'addApprove');
        $addApproveMenu = get_menu($this->MODULE, $this->Controller, 'addApprove');
        foreach ($data as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            $one['applicant_time'] = date('Y-m-d H:i', $one['applicant_time']);
            if ($one['current_approver']) {
                $current_approver = explode(',', $one['current_approver']);
                $current_approver_arr = [];
                foreach ($current_approver as &$current_approver_value) {
                    $current_approver_arr[$current_approver_value] = true;
                }
                if ($current_approver_arr[session('username')]) {
                    $one['Sort'] = 1;
                    if ($canApproval) {
                        $one['url'] = $this->returnMobileLink('审批', $addApproveMenu['actionurl'] . '?repid=' . $one['repid'], ' layui-btn-danger');
                    }else{
                        $one['url'] = $this->returnMobileLink('审批', 'javascript:void(0);', ' layui-btn-disabled');
                    }
                } else {
                    $total--;
                    $one['Sort'] = 2;
                    $complete = explode(',', $one['complete_approver']);
                    $notcomplete = explode(',', $one['not_complete_approver']);
                    if (!in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                        //完全未审
                        $one['url'] = $this->returnMobileLink('待审批', 'javascript:void(0);', 'layui-btn-disabled');
                    } elseif (in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                        //有已审，有未审
                        $one['url'] = $this->returnMobileLink('待审批', 'javascript:void(0);',' layui-btn-disabled');
                    } elseif (in_array(session('username'), $complete) && !in_array(session('username'), $notcomplete)) {
                        //全部已审
                        $one['url'] = $this->returnMobileLink('已审核', 'javascript:void(0);', C('BTN_CURRENCY') . ' layui-btn-primary');
                    } else {
                        $one['url']= $this->returnMobileLink('待处理', 'javascript:void(0);', C('BTN_CURRENCY') . ' layui-btn-primary');
                    }
                }
            }
        }
        $cmf_arr = array_column($data,'Sort');
        array_multisort($cmf_arr, SORT_ASC, $data);
        $result["total"] = $total;
        $result["code"] = 200;
        $result["rows"] = $data;
        return $result;

    }
    /*
    获取维修进程数量
     */
    public function get_Repair_Repair_progress($tasks)
    {
        $menu = get_menu('Repair', 'Repair', 'progress');
        $tasks['Repair_Repair_progress']['nums'] = 0;
        if (!$menu) {
            return $tasks;
        }
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $where['A.status'][0] = 'IN';
        $where['A.status'][1][] = C('REPAIR_HAVE_REPAIRED');//已报修待接单
        $where['A.status'][1][] = C('REPAIR_RECEIPT');//已接单待检修的设备
        $where['A.status'][1][] = C('REPAIR_HAVE_OVERHAULED');//已检修/配件待出库
        $where['A.status'][1][] = C('REPAIR_AUDIT');//审核中
        $where['A.status'][1][] = C('REPAIR_MAINTENANCE');//维修中
        $where['A.status'][1][] = C('REPAIR_MAINTENANCE_COMPLETION');//待验收
        $where['B.hospital_id'] = $hospital_id;
        $where['B.departid'] = array('IN', $departids);
        $where['B.is_delete'] = '0';
        $join[0] = ' LEFT JOIN __ASSETS_INFO__ AS B ON A.assid = B.assid';
        $join[1] = ' LEFT JOIN __DEPARTMENT__ AS C ON B.departid = C.departid';
        $total = $this->DB_get_count_join('repair', 'A', $join, $where);
        $tasks['Repair_Repair_progress']['nums'] = $total;
        return $tasks;
    }
    /*
    获取转科进程数量
     */
    public function get_Assets_Transfer_progress($tasks)
    {
        $menu = get_menu('Assets', 'Transfer', 'progress');
        $tasks['Assets_Transfer_progress']['nums'] = 0;
        if (!$menu) {
            return $tasks;
        }
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $where['B.hospital_id'] = $hospital_id;
        $where['B.status'] = array('eq','6');
        $asModel = new AssetsInfoModel();
        $where['B.is_delete'] = '0';
        //根据条件统计符合要求的数量
        $join[0] = ' LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $join[1] = ' LEFT JOIN sb_department AS C ON A.tranout_departid = C.departid';
        $total = $asModel->DB_get_count_join('assets_transfer', 'A', $join, $where);
        $tasks['Assets_Transfer_progress']['nums'] = $total;
        return $tasks;
    }

}
