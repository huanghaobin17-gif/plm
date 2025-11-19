<?php

namespace Admin\Model;

use Think\Model;

class PatrolAssetsPackModel extends CommonModel
{
    protected $tableName = 'patrol_plan_assets_pack';


    //创建计划
    public function addPatrol()
    {
        $this->checkstatus(I('POST.name'), '请输入:计划设备列表（包）统一名称');
        $this->checkstatus(judgeEmpty(I('POST.removedata')), '设备包不能为空');
        $addData['arr_assnum'] = $_POST['removedata'];
        $addData['desc'] = I('POST.remark');
        $addData['packname'] = I('POST.name');
        $addData['hospital_id'] = I('POST.hospital_id');
        $addData['level'] = I('POST.level');
        return array('status' => 1, 'msg' => '创建设备包成功', 'result' => $addData);
    }

    //移除设备包设备
    public function removeAssets()
    {
        $assnum = I('POST.assnum');
        $packid = (int)I('POST.packid');
        //删除设备包对应编码的设备
        $patrolModel = new PatrolAssetsPackModel();
        $packInfo = $patrolModel->getPackInfo($packid);
        if ($packInfo['arr_assnum']) {
            $arr_assnum = json_decode($packInfo['arr_assnum']);
            $arr_assnum = array_diff($arr_assnum, [$assnum]);
            $arr_assnum = array_values($arr_assnum);
            if (count($arr_assnum) == 0) {
                return array('status' => -1, 'msg' => '不能把设备包所有设备移除，至少要存在一个设备');
            }
            //把被移除的设备巡查状态变更为0
            $this->updateData('assets_info', array('patrol_in_plan' => C('NO_STATUS')), array('assnum' => array('in', $assnum)));
            $arr_assnum = json_encode($arr_assnum, true);
            //更新设备包设备组
            $update = $patrolModel->updateData('patrol_plan_assets_pack', array('arr_assnum' => $arr_assnum), array('packid' => $packid));
            if ($update) {
                //更新成功，同步更新各个周期计划包含此设备编码的周期计划
                return $patrolModel->updateCyclePlan($packid, $assnum);
            } else {
                return array('status' => -1, 'msg' => '更新失败');
            }
        } else {
            return array('status' => -1, 'msg' => '数据异常');
        }
    }

    /*
     * 获取历史计划记录
     */
    public function getHistoryPlan($packid)
    {
        $fields = "A.packid,A.remark,A.patrolnum,A.patrolname,sb_patrol_plan_cycle.cycid,sb_patrol_plan_cycle.patrid,sb_patrol_plan_cycle.patrol_level,A.expect_complete_date,
        sb_patrol_plan_cycle.executor,sb_patrol_plan_cycle.assnum_tpid,sb_patrol_plan_cycle.plan_assnum";
        $join[0] = " LEFT JOIN __PATROL_PLAN_CYCLE__ ON A.patrid = sb_patrol_plan_cycle.patrid";
        $res = $this->DB_get_all_join('patrol_plan', 'A', $fields, $join, array('A.packid' => $packid), 'patrid,executor', '', '');
        foreach ($res as &$value) {
            $value['tpidArr'] = json_decode($value['assnum_tpid'], true);
            $value['assnumArr'] = json_decode($value['plan_assnum']);
        }
        return $res;
    }

    /*
     * 判断模板明细
     */
    public function formatDetail($assInfo, $tpInfo)
    {
        foreach ($assInfo as $k => $v) {
            $assInfo[$k]['level_detail_1'] = false;
            $assInfo[$k]['level_detail_2'] = false;
            $assInfo[$k]['level_detail_3'] = false;
            foreach ($tpInfo as $k1 => $v1) {
                if ($v['assid'] == $v1['assid']) {
                    $arr_num_1 = json_decode($v1['arr_num_1']);
                    $arr_num_2 = json_decode($v1['arr_num_2']);
                    $arr_num_3 = json_decode($v1['arr_num_3']);
                    if (count($arr_num_1) > 0 && $arr_num_1[0] != '') {
                        $assInfo[$k]['level_detail_1'] = true;
                    }
                    if (count($arr_num_1) > 0 && $arr_num_2[0] != '') {
                        $assInfo[$k]['level_detail_2'] = true;
                    }
                    if (count($arr_num_1) > 0 && $arr_num_3[0] != '') {
                        $assInfo[$k]['level_detail_3'] = true;
                    }
                }
            }
        }
        return $assInfo;
    }

    /*
     * 格式化各级别执行人数据
     */
    public function formatLevlExector($assInfo, $history)
    {
        foreach ($assInfo as $k => $v) {
            $assInfo[$k]['level_executor_1'] = '';
            $assInfo[$k]['level_executor_2'] = '';
            $assInfo[$k]['level_executor_3'] = '';
            foreach ($history as $k1 => $v1) {
                if ($v1['patrol_level'] == 1) {
                    if (in_array($v['assnum'], $v1['assnumArr'])) {
                        $assInfo[$k]['level_executor_1'] = $v1['executor'];
                    }
                }
                if ($v1['patrol_level'] == 2) {
                    if (in_array($v['assnum'], $v1['assnumArr'])) {
                        $assInfo[$k]['level_executor_2'] = $v1['executor'];
                    }
                }
                if ($v1['patrol_level'] == 3) {
                    if (in_array($v['assnum'], $v1['assnumArr'])) {
                        $assInfo[$k]['level_executor_3'] = $v1['executor'];
                    }
                }
            }
        }
        return $assInfo;
    }

    /*
     * 获取设备包的计划和周期计划
     * @param $packid int 设备包ID
     * return $res array
     */
    public function getAllPlanAndDetail($packid)
    {
        $fields = "A.patrid,A.packid,A.patrol_level,A.patrolnum,A.patrolname,A.adduser,A.addtime,A.remark,sb_patrol_plan_cycle.*";
        $join[0] = " LEFT JOIN __PATROL_PLAN_CYCLE__ ON A.patrid = sb_patrol_plan_cycle.patrid";
        $plans = $this->DB_get_all_join('patrol_plan', 'A', $fields, $join, array('A.packid' => $packid), '', 'A.patrol_level,sb_patrol_plan_cycle.period asc', '');
        $res = array();
        foreach ($plans as $k => $v) {
            switch ($v['patrol_level']) {
                case 1:
                    $res['rc'][] = $v;
                    break;
                case 2:
                    $res['xc'][] = $v;
                    break;
                case 3:
                    $res['pm'][] = $v;
                    break;
            }
        }
        return $res;
    }

    /*
     * 获取模板名称
     * @params1 array 设备组信息
     * @params2 array 设备id组
     * return array()
     */
    public function getTemplateName($assInfo, $assidArr)
    {
        //查询模板信息
        $pt = M("patrol_template");
        $tps_info = $pt->where(array('is_delete' => 0))->getField('tpid,name');
        $assids = [];
        foreach ($assInfo as $k => $v) {
            $assids[] = $v['assid'];
        }
        $astpm = M("patrol_assets_template");
        $astps = $astpm->where(array('assid' => array('in', $assids)))->getField('assid,tpid,default_tpid');
        foreach ($assInfo as $k => $v) {
            $assInfo[$k]['tpName'] = '';
            $assInfo[$k]['tpid'] = 0;
            $assInfo[$k]['mb'] = [];
            if ($astps[$v['assid']]) {
                $ass_tmps = explode(',', $astps[$v['assid']]['tpid']);
                $default_tpid = $astps[$v['assid']]['default_tpid'];
                //$tmp_name = '<form class="layui-form" action=""><div class="layui-form-item"><div class="layui-input-inline">';
                $tmp_name = '';
                foreach ($ass_tmps as $kk => $vv) {
                    $assInfo[$k]['mb'][$kk]['tpName'] = $tps_info[$vv];
                    $assInfo[$k]['mb'][$kk]['tpid'] = (int)$vv;
                    $assInfo[$k]['mb'][$kk]['cell_name'] = $v['input_name'];
                    if ($default_tpid == $vv) {
                        $assInfo[$k]['mb'][$kk]['default'] = true;
                    } else {
                        $assInfo[$k]['mb'][$kk]['default'] = false;
                    }
                    if ($default_tpid == $vv) {
                        $assInfo[$k]['tpid'] = $vv;
                        $tmp_name .= '<label><input type="radio" name="' . $assInfo[$k]['assnum'] . '" value="' . $vv . '" data-id="' . $v['assnum'] . '" checked="checked"></label><a class="thisTemplate" style="color:#01AAED;cursor:pointer;" lay-event="thisTemplate"  data-id="' . $vv . '"> ' . $tps_info[$vv] . '</a>';
                    } else {
                        $tmp_name .= '<label><input type="radio" name="' . $assInfo[$k]['assnum'] . '" value="' . $vv . '" data-id="' . $v['assnum'] . '"></label><a class="thisTemplate" style="color:#01AAED;cursor:pointer;"  lay-event="thisTemplate"  data-id="' . $vv . '"> ' . $tps_info[$vv] . '</a>';
                    }
                }
                $tmp_name .= '</div></div></form>';
                $assInfo[$k]['tpName'] = $tmp_name;
            }
        }
        $settingTemplate = get_menu('Patrol', 'PatrolSetting', 'batchSettingTemplate');
        $html = '';
        foreach ($assInfo as $k => $v) {
            if (!$assInfo[$k]['tpName']) {
                if ($settingTemplate) {
                    $html = '<a class="settingTemplate" style="color: red;" title="设定模板" lay-event="settingTemplate" href="javascript:void(0)" data-assnum="' . $v['assnum'] . '" data-id="' . $v['assid'] . '" data-url="' . $settingTemplate['actionurl'] . '">' . $settingTemplate['actionname'] . '</a>';
                } else {
                    $html = '<a class="notSettingTemplate">您没有权限设定模板</a>';
                }
                $assInfo[$k]['tpName'] = $html;
            }
            $assInfo[$k]['operation'] = $html;
        }
        return $assInfo;
    }

    /*
     * 获取模板名称
     * @params1 array 设备组信息
     * @params2 array 设备id组
     * return array()
     */
    public function getTemplateNameEdit($assInfo, $tpidArr)
    {
        //查询模板信息
        $pt = M("patrol_template");
        $tps_info = $pt->where(array('is_delete' => 0))->getField('tpid,name');
        $assids = $assnums = [];
        foreach ($assInfo as $k => $v) {
            $assids[] = $v['assid'];
            $assnums[] = $v['assnum'];
        }
        $astpm = M("patrol_assets_template");
        $astps = $astpm->where(array('assid' => array('in', $assids)))->getField('assid,assnum,tpid,default_tpid');
        foreach ($assInfo as $k => $v) {
            $assInfo[$k]['tpName'] = '';
            $assInfo[$k]['tpid'] = 0;
            if ($astps[$v['assid']]) {
                $ass_tmps = explode(',', $astps[$v['assid']]['tpid']);
                if ($tpidArr) {
                    $selected_tpid = $tpidArr[$v['assnum']];
                } else {
                    $selected_tpid = $astps[$v['assid']]['default_tpid'];;
                }
                //$tmp_name = '<form class="layui-form" action=""><div class="layui-form-item"><div class="layui-input-inline">';
                $tmp_name = '';
                foreach ($ass_tmps as $kk => $vv) {
                    if ($selected_tpid == $vv) {
                        $assInfo[$k]['tpid'] = $vv;
                        $tmp_name .= '<label><input type="radio" name="' . $v['input_name'] . '" value="' . $vv . '" title="' . $tps_info[$vv] . '" checked="checked"></label><a class="thisTemplate" style="color:#01AAED;cursor:pointer;"> ' . $tps_info[$vv] . '</a></br>';
                    } else {
                        $tmp_name .= '<label><input type="radio" name="' . $v['input_name'] . '" value="' . $vv . '" title="' . $tps_info[$vv] . '"></label><a class="thisTemplate" style="color:#01AAED;cursor:pointer;"> ' . $tps_info[$vv] . '</a></br>';
                    }
                }
                //$tmp_name .= '</div></div></form>';
                $assInfo[$k]['tpName'] = $tmp_name;
            }
        }
        $settingTemplate = get_menu('Patrol', 'PatrolSetting', 'batchSettingTemplate');
        $html = '';
        foreach ($assInfo as $k => $v) {
            if (!$assInfo[$k]['tpName']) {
                if ($settingTemplate) {
                    $html = '<a class="settingTemplate" style="color: red;" title="设定模板" href="javascript:void(0)" data-assnum="' . $v['assnum'] . '" data-id="' . $v['assid'] . '" data-url="' . $settingTemplate['actionurl'] . '">' . $settingTemplate['actionname'] . '</a>';
                } else {
                    $html = '<a class="notSettingTemplate">您没有权限设定模板</a>';
                }
                $assInfo[$k]['tpName'] = $html;
            }
            $assInfo[$k]['operation'] = $html;
        }
        return $assInfo;
    }

    /*
     * 获取设备包详情
     * @parmas1 $packid int 设备包ID
     * retutn array()
     */
    public function getPackInfo($packid)
    {
        return $this->DB_get_one('patrol_plan_assets_pack', '*', array('packid' => $packid));
    }

    /*
    判断用户是否是开启审核但审核没有设置审核人
     */
    public function is_Approval()
    {
        $data = $this->DB_get_one('approve_type', 'status,typeid', array('approve_type' => 'patrol_approve'));
        if ($data['status'] == 0) {
            return array('status' => 1);
        }
        $app_data = $this->DB_get_one('approve_process', 'processid', array('typeid' => $data['typeid']));
        if ($app_data) {
            return array('status' => 1);
        } else {
            return array('status' => -1, 'msg' => '请前往先前往多级审批-巡查保养审批设置审批人或关闭审批');
        }
    }

    /*
     * 更新周期计划
     * @parmas1 int $packid 设备包ID
     * @params2 string $assnum 设备编码
     * return array
     */
    public function updateCyclePlan($packid, $assnum)
    {
        $planInfo = $this->DB_get_one('patrol_plan', 'group_concat(patrid) as patrid', array('packid' => $packid));
        if ($planInfo['patrid']) {
            $cyces = $this->DB_get_all('patrol_plan_cycle', 'cycid,patrid,patrol_level,executor,plan_assnum', array('patrid' => array('IN', $planInfo['patrid']), 'is_release' => 0));
            foreach ($cyces as $k => $v) {
                $plan_assnum = json_decode($v['plan_assnum']);
                if (in_array($assnum, $plan_assnum)) {
                    $plan_assnum = array_diff($plan_assnum, [$assnum]);
                    $plan_assnum = array_values($plan_assnum);
                    if (count($plan_assnum) == 0) {
                        //删除对应的执行人周期计划
                        $this->deleteData('patrol_plan_cycle', array('patrid' => $v['patrid'], 'patrol_level' => $v['patrol_level'], 'executor' => $v['executor'], 'is_release' => 0));
                    } else {
                        $plan_assnum = json_encode($plan_assnum);
                        $this->updateData('patrol_plan_cycle', array('plan_assnum' => $plan_assnum), array('patrid' => $v['patrid'], 'patrol_level' => $v['patrol_level'], 'executor' => $v['executor'], 'is_release' => 0));
                    }
                }
            }
        }
        return array('status' => 1, 'msg' => '设备移除成功！');
    }
}