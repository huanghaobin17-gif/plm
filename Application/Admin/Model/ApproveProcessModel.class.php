<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/8/03
 * Time: 16:44
 */

namespace Admin\Model;

use Think\Model;
use Think\Model\RelationModel;

class ApproveProcessModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'approve_process';

    public function addProcess()
    {
        $action = I('post.action');
        $tags = $_POST['tags'];
        $typeid = I('post.typeid');
        $names = I('post.approvesName');
        $remark = I('post.remark');
        $egt = I('post.egt');
        $lt = I('post.lt');
        $users = $_POST[I('post.approve_type') . '_users'];
        $between = I('post.between');
        if (in_array(I('post.approve_type'),[C('PATROL_APPROVE'),C('INVENTORY_PLAN_APPROVE')])) {
            $tags[0] = 'egt';
            $egt[0] = 0;
        }
        $names = array_values($names);
        $users = array_values($users);
        $tags = array_values($tags);
        $remark = array_values($remark);
        $between = array_values($between);
        foreach ($names as $k => $v) {
            if (!trim($v)) {
                return array('status' => -1, 'msg' => '请填写审批名称！');
            }
            if (!$tags[$k]) {
                return array('status' => -1, 'msg' => '请选择审批金额！');
            }
            if (!$users[$k]) {
                return array('status' => -1, 'msg' => '请选择审批人！');
            }
        }
        $tagsnum = array_count_values($tags);
        if ($tagsnum['egt'] > 1) {
            return array('status' => -1, 'msg' => '只能设置一个大于等于的流程！');
        }
        if ($tagsnum['lt'] > 1) {
            return array('status' => -1, 'msg' => '只能设置一个小于的流程！');
        }
        if ($tagsnum['egt'] == 1) {
            //一个 >=
            if (trim($egt[0]) == '') {
                return array('status' => -1, 'msg' => '请设置大于等于的金额！');
            }
            if (!is_numeric($egt[0]) && $egt[0] < 0) {
                return array('status' => -1, 'msg' => '请设置大于等于的金额！');
            }
        }
        if ($tagsnum['lt'] == 1) {
            //一个 >=
            if (trim($lt[0]) == '') {
                return array('status' => -1, 'msg' => '请设置小于的金额！');
            }
            if (!is_numeric($lt[0]) && $lt[0] < 0) {
                return array('status' => -1, 'msg' => '请设置小于的金额！');
            }
            if ($egt[0] != '') {
                //如果设置了大于等于的条件，则小于的价格不能大于该设置值
                if ($lt[0] > $egt[0]) {
                    return array('status' => -1, 'msg' => '请设置合理的小于金额！');
                }
            }
        }
        $num = 1;
        foreach ($tags as $k => $v) {
            if ($v == 'between') {
                if (trim($between[$num * 2 - 2]) == '' || trim($between[$num * 2 - 1]) == '') {
                    return array('status' => -1, 'msg' => '请设置区间金额！');
                }
                if ($between[$num * 2 - 2] >= $between[$num * 2 - 1]) {
                    return array('status' => -1, 'msg' => '请设置合理的金额区间！');
                }
                if ($num >= 2) {
                    //设置了两个价格区间，则两个价格区间的值不能重合,且价格由低到高
                    if ($between[$num * 2 - 2] <= $between[$num * 2 - 4]) {
                        return array('status' => -1, 'msg' => '设置多个金额区间的请由低到高续级设置，如：100-200，200-300');
                    }
                    if ($between[$num * 2 - 2] < $between[$num * 2 - 3]) {
                        return array('status' => -1, 'msg' => '多个金额区间的值不能重合！');
                    }
                }
                if ($egt[0] != '') {
                    //如果设置了大于等于的条件，则最大价格区间的值不能大于该设置值
                    if ($between[$num * 2 - 1] > $egt[0]) {
                        return array('status' => -1, 'msg' => '区间最大金额不能与大于等于条件的金额重合！');
                    }
                }
                if ($lt[0] != '') {
                    //如果设置了小于的条件，则最小价格区间的值不能小于该设置值
                    if ($between[$num * 2 - 2] < $lt[0]) {
                        return array('status' => -1, 'msg' => '区间最小金额不能与小于条件的金额重合！');
                    }
                }
                $num++;
            }
        }
        //数据验证通过
        $addProcess = $addUser = array();
        $t = 1;
        //查询所有超级管理员
        $supper = $this->DB_get_one('user', 'group_concat(username) as supper', array('is_super' => 1,'is_delete'=>0));
        if ($action == 'add') {
            //从无到有新增流程
            foreach ($names as $k => $v) {
                $addProcess['typeid'] = $typeid;
                $addProcess['approve_name'] = $v;
                $addProcess['condition_type'] = $tags[$k];
                $addProcess['remark'] = $remark[$k];
                switch ($tags[$k]) {
                    case 'egt':
                        $addProcess['start_price'] = $egt[0];
                        $addProcess['end_price'] = '999999999999';
                        break;
                    case 'lt':
                        $addProcess['start_price'] = 0;
                        $addProcess['end_price'] = $lt[0];
                        break;
                    default:
                        $addProcess['start_price'] = $between[$t * 2 - 2];
                        $addProcess['end_price'] = $between[$t * 2 - 1];
                        $t++;
                        break;
                }
                $addProcess['adduser'] = session('username');
                $addProcess['addtime'] = date('Y-m-d H:i:s');
                //新增一条流程数据
                $this->insertData('approve_process', $addProcess);
                $processid = M()->getLastInsID();
                //新增审批用户
                $auser = explode(',', $users[$k]);
                foreach ($auser as $k1 => $v1) {
                    $addUser['processid'] = $processid;
                    $addUser['listorder'] = $k1 + 1;
                    $addUser['approve_user'] = $v1;
                    $addUser['approve_user_aux'] = $supper['supper'];
                    $this->insertData('approve_process_user', $addUser);
                }
            }
            return array('status' => 1, 'msg' => '设置成功！');
        } else {
            //修改流程
            //查询是否审批数据未审核的，有则不允许修改
            $typeinfo = $this->DB_get_one('approve_type', 'hospital_id,approve_type', array('typeid' => $typeid));
            if (!$typeinfo) {
                return array('status' => -1, 'msg' => '查找不到审批类型信息！');
            }
            $canEdit = $this->check_can_edit_approve($typeinfo['hospital_id'], $typeinfo['approve_type']);
            if (!$canEdit) {
                return array('status' => -1, 'msg' => '有审批申请正在等待审批，暂不能修改！');
            }
            $processids = I('post.processid');
            $processids = array_values($processids);
            //查询原有的流程
            $oldproids = $this->DB_get_one('approve_process', 'group_concat(processid) as proids', array('typeid' => $typeid));
            $oldproids = explode(',', $oldproids['proids']);
            $delProid = array_merge(array_diff($processids, $oldproids), array_diff($oldproids, $processids));
            $delProid = array_filter($delProid);
            if ($delProid) {
                //删除已删除的流程
                $this->deleteData('approve_process', array('processid' => array('in', $delProid)));
                $this->deleteData('approve_process_user', array('processid' => array('in', $delProid)));
            }
            foreach ($names as $k => $v) {
                $addProcess['typeid'] = $typeid;
                $addProcess['approve_name'] = $v;
                $addProcess['condition_type'] = $tags[$k];
                $addProcess['remark'] = $remark[$k];
                if ($tags[$k] == 'egt') {
                    $addProcess['start_price'] = $egt[0];
                    $addProcess['end_price'] = '999999999999';
                } elseif ($tags[$k] == 'lt') {
                    $addProcess['start_price'] = 0;
                    $addProcess['end_price'] = $lt[0];
                } else {
                    $addProcess['start_price'] = $between[$t * 2 - 2];
                    $addProcess['end_price'] = $between[$t * 2 - 1];
                    $t++;
                }
                if ($processids[$k]) {
                    //查询该流程是否已存在
                    $processexists = $this->DB_get_one('approve_process', 'processid', array('processid' => $processids[$k]));
                    if ($processexists) {
                        //存在，修改原流程
                        $addProcess['edituser'] = session('username');
                        $addProcess['edittime'] = date('Y-m-d H:i:s');
                        $this->updateData('approve_process', $addProcess, array('processid' => $processids[$k]));
                        //修改审批用户
                        $auser = explode(',', $users[$k]);
                        //查询原有的流程审批人
                        $oldpuids = $this->DB_get_one('approve_process_user', 'group_concat(puid) as puids', array('processid' => $processids[$k]));
                        $oldpuids = explode(',', $oldpuids['puids']);
                        $newlen = count($auser);
                        $oldlen = count($oldpuids);
                        if ($oldlen > $newlen) {
                            //新设置的审批人比原审批人少，删除多余的审批用户
                            $this->deleteData('approve_process_user', array('processid' => $processids[$k], 'listorder' => array('gt', $newlen)));
                        }
                        $delProid = array_merge(array_diff($processids, $oldproids), array_diff($oldproids, $processids));
                        $delProid = array_filter($delProid);
                        if ($delProid) {
                            //删除已删除的流程
                            $this->deleteData('approve_process', array('processid' => array('in', $delProid)));
                            $this->deleteData('approve_process_user', array('processid' => array('in', $delProid)));
                        }
                        foreach ($auser as $k1 => $v1) {
                            //查询原审批用户是否存在
                            $pu = $this->DB_get_one('approve_process_user', 'puid', array('processid' => $processids[$k], 'listorder' => $k1 + 1));
                            if ($pu) {
                                //存在，修改
                                $this->updateData('approve_process_user', array('approve_user' => $v1, 'approve_user_aux' => $supper['supper']), array('puid' => $pu['puid']));
                            } else {
                                //不存在，新增
                                $addUser['processid'] = $processids[$k];
                                $addUser['listorder'] = $k1 + 1;
                                $addUser['approve_user'] = $v1;
                                $addUser['approve_user_aux'] = $supper['supper'];
                                $this->insertData('approve_process_user', $addUser);
                            }
                        }
                    }
                } else {
                    //不存在，新增流程
                    $addProcess['adduser'] = session('username');
                    $addProcess['addtime'] = date('Y-m-d H:i:s');
                    //新增一条流程数据
                    $this->insertData('approve_process', $addProcess);
                    $processid = M()->getLastInsID();
                    //新增审批用户
                    $auser = explode(',', $users[$k]);
                    foreach ($auser as $k1 => $v1) {
                        $addUser['processid'] = $processid;
                        $addUser['listorder'] = $k1 + 1;
                        $addUser['approve_user'] = $v1;
                        $addUser['approve_user_aux'] = $supper['supper'];
                        $this->insertData('approve_process_user', $addUser);
                    }
                }
            }
            return array('status' => 1, 'msg' => '设置成功！');
        }
    }

    /**
     * Notes: 开启、关闭审批功能
     * @return array
     */
    public function updateApproveStatus()
    {
        $typeid = I('POST.typeid');
        $typeStatus = I('POST.typestatus');
        //查询审批信息
        $appInfo = $this->DB_get_one('approve_type', '*', array('typeid' => $typeid));
        if ($appInfo['status'] != $typeStatus) {
            return array('status' => -1, 'msg' => '无需修改状态');
        }
        if ($typeStatus == 1) {
            //关闭审批操作，查询对应的表是否还有需要审批的数据
            $canEdit = $this->check_can_edit_approve($appInfo['hospital_id'], $appInfo['approve_type']);
            if (!$canEdit) {
                return array('status' => -1, 'msg' => '有审批申请正在等待审批，暂不能关闭！');
            } else {
                return $this->closeApprove($appInfo['approve_type'], $appInfo['hospital_id']);
            }
        } else {
            //开启审批
            switch ($appInfo['approve_type']) {
                case 'repair_approve';
                    return $this->openApprove($appInfo['approve_type'], $appInfo['hospital_id']);
                    break;
                case 'transfer_approve';
                    return $this->openApprove($appInfo['approve_type'], $appInfo['hospital_id']);
                    break;
                case 'scrap_approve';
                    return $this->openApprove($appInfo['approve_type'], $appInfo['hospital_id']);
                    break;
                case 'outside_approve';
                    return $this->openApprove($appInfo['approve_type'], $appInfo['hospital_id']);
                    break;
                case 'purchases_plans_approve';
                    return $this->openApprove($appInfo['approve_type'], $appInfo['hospital_id']);
                    break;
                case 'depart_apply_approve';
                    return $this->openApprove($appInfo['approve_type'], $appInfo['hospital_id']);
                    break;
                case 'patrol_approve';
                    return $this->openApprove($appInfo['approve_type'], $appInfo['hospital_id']);
                    break;
                case 'inventory_plan_approve';
                    return $this->openApprove($appInfo['approve_type'], $appInfo['hospital_id']);
                    break;
                case 'subsidiary_approve';
                    return $this->openApprove($appInfo['approve_type'], $appInfo['hospital_id']);
                    break;
            }
        }
    }

    /**
     * Notes:关闭审批
     * @params1 $approveType string 审批类型
     * @params1 $hospital_id int 所在医院ID
     */
    private function closeApprove($approveType, $hospital_id)
    {
        $res = $this->updateData('approve_type', array('status' => 0), array('approve_type' => $approveType, 'hospital_id' => $hospital_id));
        if (!$res) {
            return array('status' => -1, 'msg' => '关闭审批失败！');
        }
        return array('status' => 1, 'msg' => '关闭审批成功！');
    }

    /**
     * Notes:开启审批
     * @params1 $approveType string 审批类型
     * @params1 $hospital_id int 医院ID
     */
    private function openApprove($approveType, $hospital_id)
    {
        $res = $this->updateData('approve_type', array('status' => 1), array('approve_type' => $approveType, 'hospital_id' => $hospital_id));
        if (!$res) {
            return array('status' => -1, 'msg' => '开启审批失败！');
        }
        return array('status' => 1, 'msg' => '开启审批成功！');
    }

    /**
     * Notes: 获取对应医院的审批流程
     * @param $hospital_id int 医院ID
     * @return mixed
     */
    public function getProcess($hospital_id)
    {
        //查询所有审批类型
        $types = $this->DB_get_all('approve_type', '', array('hospital_id' => $hospital_id), '', 'typeid asc');
        //查询各审批流程和审批人
        $Satisfy = 0;
        foreach ($types as $k => $v) {
            $process = $this->DB_get_all('approve_process', 'processid,typeid,condition_type,approve_name,start_price,end_price,remark', array('typeid' => $v['typeid']));
            $types[$k]['process'] = $process;
            //查询审批用户
            foreach ($process as $k1 => $v1) {
                $prousers = $this->DB_get_all('approve_process_user', '*', array('processid' => $v1['processid']), '', 'listorder asc');
                $types[$k]['process'][$k1]['users'] = $prousers;
            }
            if ($v['approve_type'] == C('PATROL_APPROVE')) {
                $Satisfy = 1;
            }
        }
        if ($types && $Satisfy == 0) {
            $typeData['hospital_id'] = $hospital_id;
            $typeData['approve_type'] = C('PATROL_APPROVE');
            $typeData['type_name'] = '巡查保养审批';
            $typeData['status'] = '1';
            $this->insertData('approve_type', $typeData);
            $types = $this->getProcess($hospital_id);
        }
        return $types;
    }
}