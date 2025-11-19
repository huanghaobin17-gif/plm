<?php

namespace Admin\Controller\Patrol;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\ModuleModel;
use Admin\Model\ModuleSettingModel;
use Admin\Model\PatrolModel;
use Admin\Model\PointModel;
use Admin\Model\CategoryModel;
use Admin\Model\DepartmentModel;

class PatModSettingController extends CheckLoginController
{
    private $MODULE = 'Patrol';

    //巡查模块配置
    public function patrolModuleSetting()
    {
        if(IS_POST){
            $moduleModel = new ModuleModel();
            unset($_POST['action']);
            $data = array();
            //先进行模块开关配置
            foreach ($_POST as $k=>$v){
                $openwhere['module']  = $k;
                $openwhere['set_item'] = $k.'_open';
                $opendata['value'] = json_encode($_POST[$k][$k.'_open'],JSON_UNESCAPED_UNICODE);
                //更新配置值
                $moduleModel->updateData('base_setting',$opendata,$openwhere);
                $moduleStatus = $_POST[$k][$k.'_open']['is_open'];
                //修改menu表中对应模块状态
                $moduleModel->updateData('menu',array('status'=>$moduleStatus),array('name'=>ucfirst($k),'parentid'=>0));
            }
            //巡查模块配置
            $data = $moduleModel->patrolSetting($data);
            if($data['status'] == -1){
                $this->ajaxReturn($data);
            }
            //更新配置内容
            $result = $moduleModel->updateBaseSetting($data);
            $this->ajaxReturn($result);
        }else{
            $moduleSettingModel = new ModuleSettingModel();
            $base = $moduleSettingModel->DB_get_all('base_setting','module,set_item,value','','','setid asc','');
            $module = array();
            $settings = array();
            foreach ($base as $k=>$v){
                if(!in_array($v['module'],$module)){
                    $module[] = $v['module'];
                    $settings[$v['module']][$v['set_item']] = json_decode($v['value'],true);
                }else{
                    $settings[$v['module']][$v['set_item']] = json_decode($v['value'],true);
                }
            }
            $priceRange = '';
            foreach($settings['patrol']['priceRange'] as &$one){
                $priceRange .= $one."\n";
            }
            $priceRange = trim($priceRange,"\n");
            $this->assign('settings',$settings);
            $this->assign('priceRange',$priceRange);
            $this->assign('url',C('ADMIN_NAME').'/PatrolSetting/'.ACTION_NAME);
            $this->display();
        }
    }

}