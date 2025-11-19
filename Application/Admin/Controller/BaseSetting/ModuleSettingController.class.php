<?php

namespace Admin\Controller\BaseSetting;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\ModuleModel;
use Admin\Model\ModuleSettingModel;

class ModuleSettingController extends CheckLoginController
{
    /*
     * 模块总设置
     */
    public function module()
    {
        if (IS_POST) {
            $moduleModel = new ModuleModel();
            //保存医院配置
            $saveres = $moduleModel->saveHospital();
            if ($saveres['status'] == -1) {
                $this->ajaxReturn($saveres);
            }

            //删除医院参数
            unset($_POST['action']);
            unset($_POST['hospital_id']);
            unset($_POST['is_general_hospital']);
            unset($_POST['hospital_name']);
            unset($_POST['hospital_code']);
            unset($_POST['contacts']);
            unset($_POST['phone']);
            unset($_POST['amount_limit']);
            unset($_POST['address']);
            //设置设备管理配置永远开启
            $_POST['assets']['assets_open']['is_open']="1";
            $data = array();
            //先进行模块开关配置
            foreach ($_POST as $k => $v) {
                $openwhere['module'] = $k;
                $openwhere['set_item'] = $k . '_open';
                $opendata['value'] = json_encode($_POST[$k][$k . '_open'], JSON_UNESCAPED_UNICODE);
                //查询模块配置是否存在
                $exists = $moduleModel->DB_get_one('base_setting', 'setid', $openwhere);
                if (!$exists) {
                    //不存在，新增
                    $moduleModel->insertData('base_setting', array('module' => $k, 'set_item' => $k . '_open', 'value' => $opendata['value']));
                } else {
                    //更新配置值
                    $moduleModel->updateData('base_setting', $opendata, $openwhere);
                }
                $moduleStatus = $_POST[$k][$k . '_open']['is_open'];
                //修改menu表中对应模块状态
                $moduleModel->updateData('menu', array('status' => $moduleStatus), array('name' => ucfirst($k), 'parentid' => 0));
            }
            //首页设置
            $data = $moduleModel->targetSetting($data);

            //微信设置
            $data = $moduleModel->wxSetting($data);

            //资产模块设置
            $data = $moduleModel->assetsSetting($data);
            if ($data['status'] == -1) {
                $this->ajaxReturn($data);
            }
            //维修模块设置
            $data = $moduleModel->repairSetting($data);
            if ($data['status'] == -1) {
                $this->ajaxReturn($data);
            }
            //巡查模块配置
            $data = $moduleModel->patrolSetting($data);
            if ($data['status'] == -1) {
                $this->ajaxReturn($data);
            }
            //质控模块配置
            $data = $moduleModel->qualitiesSetting($data);
            if ($data['status'] == -1) {
                $this->ajaxReturn($data);
            }
            //更新配置内容
           
            $result = $moduleModel->updateBaseSetting($data);

            $this->ajaxReturn($result);
        } else {
            $moduleSettingModel = new ModuleSettingModel();
            //读取医院信息
            if (session('isSuper') && C('IS_OPEN_BRANCH')) {
                $this->assign('canAddHospital', 1);
                $hoswhere['is_delete'] = C('NO_STATUS');
            } else {
                //未开启分院
                $this->assign('canAddHospital', 0);
                $hoswhere['is_general_hospital'] = C('YES_STATUS');
            }
            $hospitals = $moduleSettingModel->DB_get_all('hospital', '*', $hoswhere);
            $base = $moduleSettingModel->DB_get_all('base_setting', 'module,set_item,value', '', '', 'setid asc', '');
            $module = array();
            $settings = array();
            foreach ($base as $k => $v) {
                if (!in_array($v['module'], $module)) {
                    $module[] = $v['module'];
                    $settings[$v['module']][$v['set_item']] = json_decode($v['value'], true);
                } else {
                    $settings[$v['module']][$v['set_item']] = json_decode($v['value'], true);
                }
            }
            $assetsExport = $moduleSettingModel->getColumn('assets_info');
            $assets_encoding_rules = [];
            foreach ($settings['assets']['assets_encoding_rules'] as $key => $valeu) {
                if ($valeu == 'hospitalCode') {
                    $assets_encoding_rules['hospitalCode'] = 'checked';
                }
                if ($valeu == 'categoryCode') {
                    $assets_encoding_rules['categoryCode'] = 'checked';
                }
                if ($valeu == 'departmentCode') {
                    $assets_encoding_rules['departmentCode'] = 'checked';
                }
            }
            $repairPrint = $moduleSettingModel->getColumn('repair');
            $priceRange = '';
            foreach ($settings['patrol']['priceRange'] as &$one) {
                $priceRange .= $one . "\n";
            }
            $priceRange = trim($priceRange, "\n");
            $canEdit = [];
            foreach ($hospitals as $k => $v) {
                $ass = $moduleSettingModel->DB_get_one('assets_info', 'assid', array('is_delete' => 0, 'hospital_id' => $v['hospital_id']));
                if ($ass) {
                    $canEdit[$v['hospital_id']] = 0;
                } else {
                    $canEdit[$v['hospital_id']] = 1;
                }
            }
            $this->assign('settings', $settings);
            $this->assign('assetsExport', $assetsExport);
            $this->assign('assets_encoding_rules', $assets_encoding_rules);
            $this->assign('repairPrint', $repairPrint);
            $this->assign('priceRange', $priceRange);
            $this->assign('hospitals', $hospitals);
            $this->assign('canEdit', $canEdit);
            $this->assign('url', C('ADMIN_NAME').'/ModuleSetting/' . ACTION_NAME);
            //判断用户是否是超级管理员
            $isSuper = session('isSuper');
            $settings_display = array(
                'purchaseSetting' => '1',
                'assetsSetting' => '1',
                'repairSetting' => '1',
                'patrolSetting' => '1',
                'qualitiesSetting' => '1',
                'meteringSetting' => '1',
                'adverseSetting' => '1',
                'benefitSetting' => '1',
                'statisticsSetting' => '1',
                'strategySetting' => '1',
                'monitorSetting' => '1',
                'archivesSetting' => '1',
                'trainSetting' => '1',
                'inventorySetting' => '1',
                'suppliersSetting' => '1');
            if ($isSuper != '1') {
                if ($settings['purchases']['purchases_open']['is_open'] != 1) {
                    $settings_display['purchaseSetting'] = '0';
                }
                if ($settings['assets']['assets_open']['is_open'] != 1) {
                    $settings_display['assetsSetting'] = '0';
                }
                if ($settings['repair']['repair_open']['is_open'] != 1) {
                    $settings_display['repairSetting'] = '0';
                }
                if ($settings['patrol']['patrol_open']['is_open'] != 1) {
                    $settings_display['patrolSetting'] = '0';
                }
                if ($settings['qualities']['qualities_open']['is_open'] != 1) {
                    $settings_display['qualitiesSetting'] = '0';
                }
                if ($settings['metering']['metering_open']['is_open'] != 1) {
                    $settings_display['meteringSetting'] = '0';
                }
                if ($settings['adverse']['adverse_open']['is_open'] != 1) {
                    $settings_display['adverseSetting'] = '0';
                }
                if ($settings['benefit']['benefit_open']['is_open'] != 1) {
                    $settings_display['benefitSetting'] = '0';
                }
                if ($settings['statistics']['statistics_open']['is_open'] != 1) {
                    $settings_display['statisticsSetting'] = '0';
                }
                if ($settings['strategy']['strategy_open']['is_open'] != 1) {
                    $settings_display['strategySetting'] = '0';
                }
                if ($settings['monitor']['monitor_open']['is_open'] != 1) {
                    $settings_display['monitorSetting'] = '0';
                }
                if ($settings['purchases']['archives_open']['is_open'] != 1) {
                    $settings_display['archivesSetting'] = '0';
                }
                if ($settings['purchases']['train_open']['is_open'] != 1) {
                    $settings_display['trainSetting'] = '0';
                }
                if ($settings['inventory']['inventory_open']['is_open'] != 1) {
                    $settings_display['inventorySetting'] = '0';
                }
                if ($settings['purchases']['suppliers_open']['is_open'] != 1) {
                    $settings_display['suppliersSetting'] = '0';
                }
            }
            $this->assign('settings_display', $settings_display);
            $this->display();
        }
    }

    function returnTextarea($str)
    {
        $arr = explode("\n", $str);
        $data = array();
        foreach ($arr as $k => $v) {
            if ($v) {
                $data[$k] = explode('|', $v);
            }
        }
        return $data;
    }

    public function totalSetting()
    {
        $this->display('BaseSetting/Module/totalSetting');
    }


}

?>
