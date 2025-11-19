<?php

namespace Admin\Model;

use Think\Model;
use Think\Model\RelationModel;

class ModuleModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'base_setting';


    function getColumn($table_name)
    {
        $sql = "SHOW FULL COLUMNS FROM sb_" . $table_name;
        $rescolumns = M($table_name)->query($sql);
        $arr = array();
        foreach ($rescolumns as $k => $v) {
            $arr[$k]['comment'] = $v['Comment'];
            $arr[$k]['field'] = $v['Field'];
        }
        return $arr;
    }

    /**
     * Notes: 首页设置
     * @param $data
     */
    public function targetSetting($data)
    {
        $targetSetting = $_POST['target_setting'];
        foreach ($targetSetting as $k => $v) {
            //查询是否存在
            $target_set = $this->DB_get_one('base_setting', 'setid', array('module' => 'target_setting', 'set_item' => $k));
            if (!$target_set) {
                //不存在，新增
                $inser_data = $arr = [];
                $inser_data['module'] = 'target_setting';
                $inser_data['set_item'] = $k;
                foreach ($v as $k1 => $v1) {
                    $arr[$k1] = $v1;
                }
                $inser_data['value'] = json_encode($arr, JSON_UNESCAPED_UNICODE);
                $this->insertData('base_setting', $inser_data);
            } else {
                $arr = [];
                foreach ($v as $k1 => $v1) {
                    $arr[$k1] = $v1;
                }
                $data['target_setting'][$k] = json_encode($arr, JSON_UNESCAPED_UNICODE);
            }
        }
        return $data;
    }


    public function wxSetting($data)
    {
        $wxSetting = $_POST['wx_setting'];
        foreach ($wxSetting as $k => $v) {
            //查询是否存在
            $wx_set = $this->DB_get_one('base_setting', 'setid', array('module' => 'wx_setting', 'set_item' => $k));
            if (!$wx_set) {
                //不存在，新增
                $inser_data = $arr = [];
                $inser_data['module'] = 'wx_setting';
                $inser_data['set_item'] = $k;
                foreach ($v as $k1 => $v1) {
                    $arr[$k1] = $v1;
                }
                $inser_data['value'] = json_encode($arr, JSON_UNESCAPED_UNICODE);
                $this->insertData('base_setting', $inser_data);
            } else {
                $arr = [];
                foreach ($v as $k1 => $v1) {
                    $arr[$k1] = $v1;
                }
                $data['wx_setting'][$k] = json_encode($arr, JSON_UNESCAPED_UNICODE);
            }
        }
        return $data;
    }

    /**
     * Notes: 资产模块配置
     * @param1 array 要更新的数据
     * return array 要更新的数据
     */
    public function assetsSetting($data)
    {
        $assetsSetting = $_POST['assets'];
            //资产模块开启
            $assets_print = array();
            $assets_export = array();
            $assets_encoding_rules = array();
            foreach ($assetsSetting['assets_print'] as $k => $v) {
                $assets_print[] = $k;
            }
            foreach ($assetsSetting['assets_export'] as $k => $v) {
                $assets_export[] = $k;
            }
            foreach ($assetsSetting['assets_encoding_rules'] as $k => $v) {
                $assets_encoding_rules[] = $k;
            }

            if (!$assetsSetting['apply_borrow_back_time_startDate'] or !$assetsSetting['apply_borrow_back_time_endDate'] or $assetsSetting['apply_borrow_back_time_endDate'] <= $assetsSetting['apply_borrow_back_time_startDate']) {
                die(json_encode(array('status' => -1, 'msg' => '借调归还时间范围设置异常')));
            }
            $data['assets']['assets_helpcat'] = json_encode(explode('|', $assetsSetting['assets_helpcat']), JSON_UNESCAPED_UNICODE);
            $data['assets']['assets_capitalfrom'] = json_encode(explode('|', $assetsSetting['assets_capitalfrom']), JSON_UNESCAPED_UNICODE);
            $data['assets']['assets_assfrom'] = json_encode(explode('|', $assetsSetting['assets_assfrom']), JSON_UNESCAPED_UNICODE);
            $data['assets']['assets_finance'] = json_encode(explode('|', $assetsSetting['assets_finance']), JSON_UNESCAPED_UNICODE);
            $data['assets']['acin_category'] = json_encode(explode('|', $assetsSetting['acin_category']), JSON_UNESCAPED_UNICODE);
            $data['assets']['assets_encoding_rules'] = json_encode($assets_encoding_rules, JSON_UNESCAPED_UNICODE);
            $data['assets']['assets_add_remind_day'] = $assetsSetting['assets_add_remind_day'];
            $data['assets']['assets_scrap_overPrice'] = $assetsSetting['assets_scrap_overPrice'];
            $data['assets']['assets_scrap_licenseDay'] = $assetsSetting['assets_scrap_licenseDay'];
            $data['assets']['borrow_template'] = json_encode(array('title'=>$assetsSetting['borrow_template']['title']), JSON_UNESCAPED_UNICODE);
            $data['assets']['transfer_template'] = json_encode(array('title'=>$assetsSetting['transfer_template']['title']), JSON_UNESCAPED_UNICODE);
            $data['assets']['outside_template'] = json_encode(array('title'=>$assetsSetting['outside_template']['title']), JSON_UNESCAPED_UNICODE);
            $data['assets']['scrap_template'] = json_encode(array('title'=>$assetsSetting['scrap_template']['title']), JSON_UNESCAPED_UNICODE);
            $data['assets']['apply_borrow_back_time'] = json_encode(array($assetsSetting['apply_borrow_back_time_startDate'], $assetsSetting['apply_borrow_back_time_endDate']));

        return $data;
    }

    /**
     * Notes:维修模块配置
     * @param1 array 要更新的数据
     * return array 要更新的数据
     */
    public function repairSetting($data)
    {
        $repairSetting = $_POST['repair'];
        if ($repairSetting['repair_open']['is_open'] == 1) {
            //维修模块开启
            $repair_print = array();
            foreach ($repairSetting['repair_print'] as $k => $v) {
                $repair_print[] = $k;
            }
            //是否由系统生成字段若没有勾选默认0
            if (!$_POST['repair']['repair_system']['repair_date']) {
                $data['repair']['repair_system']['repair_date'] = '0';
            }
            if (!$_POST['repair']['repair_system']['repair_person']) {
                $data['repair']['repair_system']['repair_person'] = '0';
            }
            if (!$_POST['repair']['repair_system']['repair_phone']) {
                $data['repair']['repair_system']['repair_phone'] = '0';
            }
            if (!$_POST['repair']['repair_system']['service_date']) {
                $data['repair']['repair_system']['service_date'] = '0';
            }
            if (!$_POST['repair']['repair_system']['service_working']) {
                $data['repair']['repair_system']['service_working'] = '0';
            }
            if (!$_POST['repair']['repair_system']['repair_check']) {
                $data['repair']['repair_system']['repair_check'] = '0';
            }
            //表单必填项开关没有勾选默认0
            if (!$_POST['repair']['repair_required']['repair_date']) {
                $data['repair']['repair_required']['repair_date'] = '0';
            }
            if (!$_POST['repair']['repair_required']['repair_person']) {
                $data['repair']['repair_required']['repair_person'] = '0';
            }
            if (!$_POST['repair']['repair_required']['repair_phone']) {
                $data['repair']['repair_required']['repair_phone'] = '0';
            }
            if (!$_POST['repair']['repair_required']['service_date']) {
                $data['repair']['repair_required']['service_date'] = '0';
            }
            if (!$_POST['repair']['repair_required']['service_working']) {
                $data['repair']['repair_required']['service_working'] = '0';
            }
            if (!$_POST['repair']['repair_required']['repair_check']) {
                $data['repair']['repair_required']['repair_check'] = '0';
            }
            if (!$_POST['repair']['repair_required']['repair_detail']) {
                $data['repair']['repair_required']['repair_detail'] = '0';
            }
            if (!$_POST['repair']['open_sweepCode_overhaul']['open']) {
                $data['repair']['open_sweepCode_overhaul']['open'] = '0';
            }


            $data['repair']['repair_encoding_rules'] = json_encode($repairSetting['repair_encoding_rules'], JSON_UNESCAPED_UNICODE);
            $data['repair']['repair_print_watermark'] = json_encode($repairSetting['repair_print_watermark'], JSON_UNESCAPED_UNICODE);
            if (!$repairSetting['repair_template']['title']||$repairSetting['repair_template']['title']=="") {
                $repairSetting['repair_template']['title'] = '医疗设备';
            }
            $data['repair']['repair_template'] = json_encode($repairSetting['repair_template'], JSON_UNESCAPED_UNICODE);
            if ($repairSetting['repair_tmp']['style'] == 2) {
                if (!trim($repairSetting['repair_print_watermark']['watermark'])) {
                    return array('status' => -1, 'msg' => '请填写水印文字!');
                }
            }
            $data['repair']['repair_category'] = $repairSetting['repair_category'] ? json_encode(explode('|', $repairSetting['repair_category']), JSON_UNESCAPED_UNICODE) : '';
            $data['repair']['repair_tmp'] = $repairSetting['repair_tmp'] ? json_encode($repairSetting['repair_tmp'], JSON_UNESCAPED_UNICODE) : '';
            $data['repair']['repair_uptime'] = $repairSetting['repair_uptime'];
            $data['repair']['life_assets_remind'] = $repairSetting['life_assets_remind'];
            $data['repair']['normal_assets_remind'] = $repairSetting['normal_assets_remind'];
            $data['repair']['parts_warning'] = $repairSetting['parts_warning'];
            $data['repair']['repair_system'] = $repairSetting['repair_system'] ? json_encode($repairSetting['repair_system'], JSON_UNESCAPED_UNICODE) : '';
            $data['repair']['open_sweepCode_overhaul'] = $repairSetting['open_sweepCode_overhaul'] ? json_encode($repairSetting['open_sweepCode_overhaul'], JSON_UNESCAPED_UNICODE) : '';
            $data['repair']['repair_required'] = $repairSetting['repair_required'] ? json_encode($repairSetting['repair_required'], JSON_UNESCAPED_UNICODE) : '';
        }

        return $data;
    }

    /**
     * Notes:巡查保养模块配置
     * @param1 array 要更新的数据
     * return array 要更新的数据
     */
    public function patrolSetting($data)
    {
        $patrolSetting = $_POST['patrol'];
        //价格区间
        $data['patrol']['priceRange'] = I('priceRange') ? json_encode(explode(',', I('priceRange')), JSON_UNESCAPED_UNICODE) : '';
        //任务发布提醒范围
        $data['patrol']['patrol_reminding_day'] = $patrolSetting['patrol_reminding_day'];
        //任务将要到期提醒范围
        $data['patrol']['patrol_soon_expire_day'] = $patrolSetting['patrol_soon_expire_day'];
        //微信扫码保养功能
        $data['patrol']['patrol_wx_set_situation'] = $patrolSetting['patrol_wx_set_situation'];
        $data['patrol']['patrol_template'] = json_encode(array('title'=>$patrolSetting['patrol_template']['title']), JSON_UNESCAPED_UNICODE);
        return $data;
    }

    /*
     *
     * */
    public function qualitiesSetting($data)
    {
        $qualitiesSetting = $_POST['qualities'];
        if ($qualitiesSetting['qualities_open']['is_open'] == 1) {
            //任务将要到期提醒范围
            $data['qualities']['qualities_patrol'] = $qualitiesSetting['qualities_patrol'] ? json_encode($qualitiesSetting['qualities_patrol'], JSON_UNESCAPED_UNICODE) : '';
            $data['qualities']['qualities_soon_expire_day'] = $qualitiesSetting['qualities_soon_expire_day'];
        }
        return $data;
    }

    /**
     * Notes: 更新模块配置内容
     * @param $data array 要更新的配置
     */
    public function updateBaseSetting($data)
    {
        //更新所有配置内容
        $where = array();
        $updata = array();
        foreach ($data as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $where['module'] = $k;
                $where['set_item'] = $k1;
                $updata['value'] = $v1;
                //查询该项配置是否存在，不存在就新增,存在则修改
                $exist = $this->DB_get_one('base_setting', 'setid', $where, '');
                if (!$exist['setid']) {
                    $inserData['module'] = $k;
                    $inserData['set_item'] = $k1;
                    $inserData['value'] = $v1;
                    $res = $this->insertData('base_setting', $inserData);
                    if (!$res) {
                        return array('status' => -1, 'msg' => '更新失败！');
                    }
                } else {
                    $this->updateData('base_setting', $updata, $where);
                }
            }
        }
        //更新模块设置缓存
        $basearr = $this->DB_get_all('base_setting', '', '', '', '', '');
        $baseData = array();
        foreach ($basearr as $k => $v) {
            $baseData[$v['module']][$v['set_item']]['value'] = json_decode($v['value'], true);
        }
        $bedata['baseSetting'] = ArrayToString($baseData);
        made_web_array(APP_PATH . '/Common/cache/basesetting.cache.php', $bedata);
        //生成js文件
        $baseData['status'] = 1;
        $baseData['msg'] = 'ok';
        $jsdata = json_encode($baseData, JSON_UNESCAPED_UNICODE);
        made_web_js(APP_PUBLIC . '/js/cache/basesetting.js', $jsdata);
        return array('status' => 1, 'msg' => '更新成功！');
    }

    /**
     * Notes: 保存医院配置
     */
    public function saveHospital()
    {
        $hospitalName = I('post.hospital_name');
        $hospitalCode = I('post.hospital_code');
        $contacts = I('post.contacts');
        $phone = I('post.phone');
        $amount_limit = I('post.amount_limit');
        $address = I('post.address');
        $isOpen = I('post.is_general_hospital');
        $hospital_id = I('post.hospital_id');
        if (!trim($hospitalName[0])) {
            return array('status' => -1, 'msg' => '请填写医院名称！');
        }
        $data = array();
        foreach ($hospitalName as $k => $v) {
            if (!trim($v)) {
                return array('status' => -1, 'msg' => '请填写医院名称！');
            }
            if (!trim($hospitalCode[$k])) {
                return array('status' => -1, 'msg' => '请填写医院代码！');
            }
            if (!trim($contacts[$k])) {
                return array('status' => -1, 'msg' => '请填写医院联系人！');
            }
            if (!trim($phone[$k])) {
                return array('status' => -1, 'msg' => '请填写医院联系电话！');
            } else {
                //验证电话号码

            }
            if (!trim($amount_limit[$k])) {
                return array('status' => -1, 'msg' => '请填写医院采购年限下限！');
            }
            if (!trim($address[$k])) {
                return array('status' => -1, 'msg' => '请填写医院详细地址！');
            }
            $hid = $hospital_id[$k];
            $data['hospital_name'] = trim($v);
            $data['hospital_code'] = trim($hospitalCode[$k]);
            $data['contacts'] = trim($contacts[$k]);
            $data['phone'] = trim($phone[$k]);
            $data['amount_limit'] = trim($amount_limit[$k]);
            $data['address'] = trim($address[$k]);
            $data['is_general_hospital'] = trim($isOpen[$k]);
            //查询医院是否已存在
            $exists = $this->DB_get_one('hospital', 'hospital_id', array('hospital_id' => $hid));
            if ($exists) {
                //已存在，更新原医院信息
                $res = $this->check_hospital_name($hid, $data);
                if ($res['status'] == -1) {
                    return $res;
                    break;
                }
                $res = $this->check_hospital_code($hid, $data);
                if ($res['status'] == -1) {
                    return $res;
                    break;
                }
                $this->updateData('hospital', $data, array('hospital_id' => $hid));
            } else {
                //新增医院
                $res = $this->check_hospital_name(0, $data);
                if ($res['status'] == -1) {
                    return $res;
                    break;
                }
                $res = $this->check_hospital_code(0, $data);
                if ($res['status'] == -1) {
                    return $res;
                    break;
                }
                $this->insertData('hospital', $data);
                $newhosid = M()->getLastInsID();
                //更新超级管理员管理医院列表
                $hosinfo = $this->DB_get_one('hospital', 'group_concat(hospital_id) as hospitalids', array('is_delete' => 0));
                $this->updateData('user', array('manager_hospitalid' => $hosinfo['hospitalids']), array('is_super' => 1));
                //修改session
                session('manager_hospitalid', $hosinfo['hospitalids']);
                $hoswhere['is_delete'] = 0;
                $hoswhere['hospital_id'] = array('in', $hosinfo['hospitalids']);
                $hospitals = $this->DB_get_all('hospital', '*', $hoswhere);
                if (count($hospitals) >= 2) {
                    session('hospitals', $hospitals);
                }
                //增加医院对应的审批类型
                $gh = $this->DB_get_one('hospital', 'hospital_id', array('is_general_hospital' => 1));
                $types = $this->DB_get_all('approve_type', '*', array('hospital_id' => $gh['hospital_id']));
                $datatype = array();
                foreach ($types as $key => $val) {
                    $datatype['hospital_id'] = $newhosid;
                    $datatype['approve_type'] = $val['approve_type'];
                    $datatype['type_name'] = $val['type_name'];
                    $datatype['status'] = $val['status'];
                    $datatype['is_mandatory'] = $val['is_mandatory'];
                    $datatype['count'] = $val['count'];
                    //查询该流程是否已存在
                    $ty = $this->DB_get_one('approve_type', '*', array('hospital_id' => $newhosid, 'approve_type' => $val['approve_type']));
                    if (!$ty) {
                        //不存在，新增
                        $this->insertData('approve_type', $datatype);
                    }
                }
            }
        }
    }

    /**
     * Notes: 检测医院的名称是否存在重复值
     * @param $hid int 医院ID
     * @param $data array 要更新或新增的医院数据
     * @return array
     */
    public function check_hospital_name($hid, $data)
    {
        array('hospital_id' => array('neq', $hid), 'hospital_name' => $data['hospital_name']);
        if ($hid) {
            $where['hospital_id'] = array('neq', $hid);
            $where['hospital_name'] = $data['hospital_name'];
        } else {
            $where['hospital_name'] = $data['hospital_name'];
        }
        //查询医院名称是否已存在
        $nameexists = $this->DB_get_one('hospital', 'hospital_id', $where);
        if ($nameexists) {
            return array('status' => -1, 'msg' => '医院名称不能重复！');
        }
    }

    /**
     * Notes: 检测医院的码是否存在重复值
     * @param $hid int 医院ID
     * @param $data array 要更新或新增的医院数据
     * @return array
     */
    private function check_hospital_code($hid, $data)
    {
        array('hospital_id' => array('neq', $hid), 'hospital_name' => $data['hospital_name']);
        if ($hid) {
            $where['hospital_id'] = array('neq', $hid);
            $where['hospital_code'] = $data['hospital_code'];
        } else {
            $where['hospital_code'] = $data['hospital_code'];
        }
        //查询医院代码是否已存在
        $codeexists = $this->DB_get_one('hospital', 'hospital_id', $where);
        if ($codeexists) {
            return array('status' => -1, 'msg' => '医院代码不能重复！');
        }
    }

    /**
     * 检测登陆验证微信绑定是否开启
     */
    public function decide_wx_login()
    {
        $data = $this->DB_get_one('base_setting', 'value', array('set_item' => 'wx_setting_open'));
        if (!$data) {
            $datatype = array('module' => 'wx_setting', 'set_item' => 'wx_setting_open', 'value' => '{"open":"1"}');
            $this->insertData('base_setting', $datatype);
            $data = $this->DB_get_one('base_setting', 'value', array('set_item' => 'wx_setting_open'));
        }
        $status = json_decode($data['value'], true)['open'];
        if ($status == 1) {
            return true;
        }
        return false;
    }
    /*
    检测扫码检修是否开启
     */
    public function decide_sweepCode()
    {
        $data = $this->DB_get_one('base_setting','value',array('set_item'=>'open_sweepCode_overhaul'));
        if (!$data) {
            $datatype = array('repair' => 'wx_setting', 'set_item' => 'open_sweepCode_overhaul', 'value' => '{"open":"0"}');
            $this->insertData('base_setting', $datatype);
            $data = $this->DB_get_one('base_setting', 'value', array('set_item' => 'open_sweepCode_overhaul'));
        }
        $status = json_decode($data['value'], true)['open'];
        if ($status == 1) {
            return true;
        }
        return false;
    }
}
