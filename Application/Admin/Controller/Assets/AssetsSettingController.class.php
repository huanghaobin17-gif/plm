<?php

namespace Admin\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\ModuleSettingModel;
use Admin\Model\ModuleModel;
use Admin\Model\BaseSettingModel;
use think\Controller;

class AssetsSettingController extends CheckLoginController
{
    protected function __initialize()
    { 
        parent::_initialize();
    }

    private $MODULE = 'Assets';

    public function assetsModuleSetting()
    {
        if (IS_POST) {
            $moduleModel = new ModuleModel();
            if (I('post.otherAction')) {
                //上传维修报告logo
                $Tool = new ToolController();
                //设置文件类型
                $type = array('jpg', 'png', 'bmp', 'jpeg');
                //文件名目录设置
                $dirName = 'allModuleLogo';
                //上传文件
                $upload = $Tool->upFile($type, $dirName);
                if ($upload['status'] == C('YES_STATUS')) {
                    $result['status'] = 1;
                    $result['msg'] = $upload['上传成功'];
                    $result['src'] = $upload['src'];
                } else {
                    $result['status'] = -1;
                    $result['msg'] = $upload['上传失败'];
                }
                $this->ajaxReturn($result);
            } else {
                unset($_POST['action']);
                $data = array();
                //先进行模块开关配置
                foreach ($_POST as $k => $v) {
                    $openwhere['module'] = $k;
                    $openwhere['set_item'] = $k . '_open';
                    $opendata['value'] = '{"is_open":"1"}';
                    //更新配置值
                    $moduleModel->updateData('base_setting', $opendata, $openwhere);
                    $moduleStatus = 1;//设置默认设备管理开启
                    //修改menu表中对应模块状态
                    $moduleModel->updateData('menu', array('status' => $moduleStatus), array('name' => ucfirst($k), 'parentid' => 0));
                }
                //更新logo地址
                if (I('post.src')) {
                    $data['all_module']['all_report_logo'] = json_encode(I('post.src'), JSON_UNESCAPED_UNICODE);
                } else {
                    //资产模块设置
                    $data = $moduleModel->assetsSetting($data);
                    if ($data['status'] == -1) {
                        $this->ajaxReturn($data);
                    }
                }
                //更新配置内容
                $result = $moduleModel->updateBaseSetting($data);
                $this->ajaxReturn($result);
            }
        } else {
            $moduleSettingModel = new ModuleSettingModel();
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
                if ($valeu == 'Code') {
                    $assets_encoding_rules['departmentCode'] = 'checked';
                }
            }
            $this->assign('settings', $settings);
            $this->assign('assetsExport', $assetsExport);
            $this->assign('assets_encoding_rules', $assets_encoding_rules);
            $this->assign('url', C('ADMIN_NAME').'/AssetsSetting/' . ACTION_NAME);
            $this->display();
        }
    }
}