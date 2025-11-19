<?php
/**
 * Created by PhpStorm.
 * User: jinglong
 * Date: 2017/2/12
 * Time: 15:24
 */

namespace Admin\Model;

use Think\Model;

class BaseSettingModel extends CommonModel
{

    protected $tablePrefix = 'sb_';
    protected $tableName = 'sms_basesetting';
    protected $len=50;


    /*
     * 查询某个设置选项是否存在
     * param @module string module
     * param @setItem string set_item
     * return array
     */
    public function checkOption($module, $setItem)
    {
        $condition['module'] = $module;
        $condition['set_item'] = $setItem;
        $data = $this->where($condition)->find();
        return $data;
    }

    /*
     * 修改或新增某个选项的配置值
     * param @module string module
     * param @setItem string set_item
     * param @type string 操作类型 新增或修改
     * param @data array 要新增或修改的数据值
     */
    public function addOrEditOption($module, $setItem, $type, $arr = array())
    {
        if ($type == 'add') {
            try {
                $BaseSetting = M('BaseSetting');
                $values['module'] = $module;
                $values['set_item'] = $setItem;
                $values['value'] = json_encode($arr);
                $BaseSetting->create($values);
                $BaseSetting->add();
            } catch (Exception $e) {
                $e->getMessage();
            }
        } else {
            try {
                $BaseSetting = M('BaseSetting');
                if ($arr) {
                    $data['value'] = json_encode($arr);
                } else {
                    $data['value'] = $arr;
                }
                $condition['module'] = $module;
                $condition['set_item'] = $setItem;
                $BaseSetting->where($condition)->data($data)->save();
            } catch (Exception $e) {
                $e->getMessage();
            }
        }
    }

    /*
     * 获取某个模块的全部设置
     * @param string $statistics 模块名字
     * return array
     */
    public function getSettingByModule($statistics)
    {
        $BaseSetting = M('BaseSetting');
        $condition['module'] = $statistics;
        $data = $BaseSetting->where($condition)->select();
        return $data;
    }


    //获取短信配置
    public function getSmsSetting()
    {
        $where['hospital_id'] = session('current_hospitalid');
        $data = $this->DB_get_all('sms_basesetting', '', $where);
        if ($data) {
            //组合数据
            $settingData=[];
            $parentData=[];
            foreach ($data as $key=>$val){
                if($val['parentid']==0){
                    $settingData[$val['action']]['status']=$val['status'];
                    unset($data[$key]);
                }else{
                    if($val['content']==''){
                        $settingData[$val['action']]['status']=$val['status'];
                        $parentData[$val['id']]=$val['action'];
                        unset($data[$key]);
                    }
                }
            }
            foreach ($data as &$one){
                $settingData[$parentData[$one['parentid']]][$one['action']]['status']=$one['status'];
                $settingData[$parentData[$one['parentid']]][$one['action']]['content']=$one['content'];

            }
            return $settingData;
        }else{
            return false;
        }

    }

    //保存短信配置
    public function setSmsSetting()
    {
        $setting = $this->DB_get_all('sms_basesetting', '', ['hospital_id' => session('current_hospitalid')]);
        $settingData = [];
        if ($setting) {
            foreach ($setting as $settV) {
                $settingData[$settV['action']]['id'] = $settV['id'];
                $settingData[$settV['action']]['content'] = $settV['content'];
                $settingData[$settV['action']]['status'] = $settV['status'];
            }
        }
        $settingParentid=0;
        $addAll=[];
        foreach ($_POST as $k => $v) {
            switch ($k) {
                case 'setting_open':
                    if ($settingData[$k]['id'] > 0) {
                        if ($settingData[$k]['status'] != $v['status']) {
                            //修改总配置
                            $this->updateData('sms_basesetting', ['status' => $v['status']], ['id' => $settingData[$k]['id']]);
                            $settingParentid = $settingData[$k]['id'];
                        }
                    } else {
                        //新增总配置
                        $addData['hospital_id'] = session('current_hospitalid');
                        $addData['action'] = 'setting_open';
                        $addData['status'] = $v;
                        $settingParentid = $this->insertData('sms_basesetting', $addData);
                    }
                    break;
                default:
                    //模块短信配置
                    if($settingData[$k]['id']>0){
                        $parentid= $settingData[$k]['id'];
                        if($settingData[$k]['status']!=$v['status']){
                            //修改配置
                            $this->updateData('sms_basesetting',['status'=>$v['status']],['id'=>$settingData[$k]['id']]);
                        }
                    }else{
                        //新增配置
                        $addData=[];
                        $addData['hospital_id']=session('current_hospitalid');
                        $addData['action']=$k;
                        $addData['parentid']=$settingParentid;
                        $addData['status']=$v['status'];
                        $parentid=$this->insertData('sms_basesetting',$addData);
                    }
                    unset($v['status']);
                    foreach ($v as $actionKey=>$actionValue){
                        if($settingData[$actionKey]['id']>0){
                            if($settingData[$actionKey]['status']!=$actionValue['status'] or $settingData[$actionKey]['content']!=$actionValue['content']){
                                //修改配置
                                $saveData['status']=$actionValue['status'];
                                $saveData['content']=$actionValue['content'];
                                $this->updateData('sms_basesetting',$saveData,['id'=>$settingData[$actionKey]['id']]);
                            }
                        }else{
                            //新增配置
                            $addData['hospital_id']=session('current_hospitalid');
                            $addData['action']=$actionKey;
                            $addData['parentid']=$parentid;
                            $addData['content']=$actionValue['content'];
                            $addData['status']=$actionValue['status'];
                            if (count($addAll) < $this->len) {
                                $addAll[]=$addData;
                            }
                            if (count($addAll) == $this->len) {
                                //进行一次设备入库操作
                                $this->insertDataALL('sms_basesetting',$addAll);
                                //重置
                                $addAll = [];
                            }
                        }
                    }
            }
        }
        if($addAll){
            $this->insertDataALL('sms_basesetting',$addAll);
        }
        return array('status' => 1, 'msg' => '保存成功！');
    }

}