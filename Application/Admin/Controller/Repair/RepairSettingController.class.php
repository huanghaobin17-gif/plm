<?php

namespace Admin\Controller\Repair;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use think\Controller;
use Admin\Model\RepairSettingModel;
use Admin\Model\ModuleSettingModel;
use Admin\Model\ModuleModel;
use Admin\Model\BaseSettingModel;

class RepairSettingController extends CheckLoginController
{
    public function repairModuleSetting()
    {
        $moduleModel = new ModuleModel();
        if (IS_POST) {
            unset($_POST['action']);
            //先进行模块开关配置
            foreach ($_POST as $k => $v) {
                $openwhere['module'] = $k;
                $openwhere['set_item'] = $k . '_open';
                $opendata['value'] = json_encode($_POST[$k][$k . '_open'], JSON_UNESCAPED_UNICODE);
                //更新配置值
                $moduleModel->updateData('base_setting', $opendata, $openwhere);
                $moduleStatus = $_POST[$k][$k . '_open']['is_open'];
                //修改menu表中对应模块状态
                $moduleModel->updateData('menu', array('status' => $moduleStatus), array('name' => ucfirst($k), 'parentid' => 0));
            }
            $data = array();
            //维修模块设置
            $data = $moduleModel->repairSetting($data);
            if ($data['status'] == -1) {
                $this->ajaxReturn($data);
            }
            $result = $moduleModel->updateBaseSetting($data);
            $this->ajaxReturn($result);
        } else {
            //G('begin');
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
            $repairPrint = $moduleSettingModel->getColumn('repair');
            $this->assign('settings', $settings);
            $this->assign('repairPrint', $repairPrint);
            $this->assign('url', get_url());
            $this->display();
        }
    }

    public function repairAssign()
    {
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'getUser':
                    $RepairSettingModel = new RepairSettingModel();
                    $result = $RepairSettingModel->getAssignUser();
                    $this->ajaxReturn($result);
                    break;
                case 'getCategory':
                    $RepairSettingModel = new RepairSettingModel();
                    $result = $RepairSettingModel->getAssignCategory();
                    $this->ajaxReturn($result);
                    break;
                case 'getDepartment':
                    $RepairSettingModel = new RepairSettingModel();
                    $result = $RepairSettingModel->getAssignDepartment();
                    $this->ajaxReturn($result);
                    break;
                case 'getAuxiliary':
                    $RepairSettingModel = new RepairSettingModel();
                    $result = $RepairSettingModel->getAssignAuxiliary();
                    $this->ajaxReturn($result);
                    break;
                case 'getAssets':
                    $RepairSettingModel = new RepairSettingModel();
                    $result = $RepairSettingModel->getAssignAssets();
                    $this->ajaxReturn($result);
                    break;
                case 'addAssign':
                    $RepairSettingModel = new RepairSettingModel();
                    $result = $RepairSettingModel->addAssign();
                    $this->ajaxReturn($result);
                    break;
                case 'saveUser':
                    $RepairSettingModel = new RepairSettingModel();
                    $result = $RepairSettingModel->saveUser();
                    $this->ajaxReturn($result);
                    break;
                case 'saveValueData':
                    $RepairSettingModel = new RepairSettingModel();
                    $result = $RepairSettingModel->saveValueData();
                    $this->ajaxReturn($result);
                    break;
                case 'delAssign':
                    $RepairSettingModel = new RepairSettingModel();
                    $result = $RepairSettingModel->delAssign();
                    $this->ajaxReturn($result);
                    break;
            }

        } else {
            $hospital_id=session('current_hospitalid');
            $RepairSettingModel = new RepairSettingModel();
            $result = $RepairSettingModel->getAllAssign();
            $this->assign('assign', $result);
            $this->assign('repairAssignUrl', get_url());
            $this->assign('hospital_id', $hospital_id);
            if (I('GET.action') == 'getType') {
                echo $this->display('hospital_repairAssign');
            } else {
                $this->display();
            }
        }
    }

    /**
     * 故障类型
     */
    public function typeSetting()
    {
        $repairmodel = new RepairSettingModel();
        if (IS_POST) {
            $parentid = I('post.parentid');
            $problem = $repairmodel->getProblem($parentid);
            $this->ajaxReturn($problem, 'json');
        } else {
            //获取故障类型
            $type = $repairmodel->getType();
            //第一次进入页面显示的故障类型
            $firstnName = $repairmodel->DB_get_one('repair_setting', 'id,title', array('parentid' => 0));
            $this->assign('type', $type);
            $this->assign('first', $firstnName);
            $this->assign('typeSetting', get_url());
            $this->display();
        }
    }

    /**
     * 故障类型添加
     */
    public function addType()
    {
        $repairmodel = new RepairSettingModel();
        switch (I('get.type')) {
            case '';
                if (IS_POST) {
                    $result = $repairmodel->addTypeData();
                    $lastSql = M()->getLastSql();
                    if ($result) {
                        $newType = $repairmodel->DB_get_one('repair_setting', 'title', array('id' => $result));
                        //日志行为记录文字
                        $log['type'] = $newType['title'];
                        $text = getLogText('addRepairTypeText', $log);
                        $repairmodel->addLog('repair_setting', $lastSql, $text, $result, ACTION_NAME);
                        $this->ajaxReturn(array('status' => 1, 'msg' => '新增故障类型成功'));
                    } else {
                        $this->ajaxReturn(array('status' => 0, 'msg' => '新增故障类型失败'));
                    }
                } else {
                    $this->assign('actionname', ACTION_NAME);
                    $this->display();
                }
                break;
            case 'addProblem';//添加故障问题
                if (IS_POST) {
                    $parentid = I('post.id');
                    $result = $repairmodel->addProblemData($parentid);
                    if ($result) {
                        $this->ajaxReturn(array('status' => 1, 'msg' => '新增故障问题成功'));
                    } else {
                        $this->ajaxReturn(array('status' => 0, 'msg' => '新增故障问题失败'));
                    }
                } else {
                    //父id
                    $id = I('get.id');
                    $type = $repairmodel->DB_get_one('repair_setting', 'id,title', array('id' => $id));
                    $this->assign('id', $id);
                    $this->assign('type', $type['title']);
                    $this->assign('actionname', ACTION_NAME);
                    $this->display('addProblem');
                }
                break;
        }

    }

    /**
     * 故障问题修改
     */
    public function editProblem()
    {
        $repairmodel = new RepairSettingModel();
        if (IS_POST) {
            $id = I('post.id');
            $result = $repairmodel->editProblemData($id);
            if ($result) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '修改故障问题成功'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => '修改故障问题失败'));
            }
        } else {
            //故障问题id
            $id = I('get.id');
            //故障问题信息
            $problem = $repairmodel->DB_get_one('repair_setting', '', array('id' => $id));
            //故障类型名称
            $type = $repairmodel->DB_get_one('repair_setting', 'title', array('id' => $problem['parentid']));
            $this->assign('id', $id);
            $this->assign('type', $type['title']);
            $this->assign('problem', $problem);
            $this->assign('actionname', ACTION_NAME);
            $this->display('editProblem');
        }
    }

    /**
     * 故障问题删除
     */
    public function deleteProblem()
    {
        $repairmodel = new RepairSettingModel();
        $id = I('post.id');
        $deleteName = $repairmodel->DB_get_one('repair_setting', 'title', array('id' => $id));
        $del = $repairmodel->deleteData('repair_setting', array('id' => $id));
        //日志行为记录文字
        $lastSql = M()->getLastSql();

        $log['problem'] = $deleteName['title'];
        $text = getLogText('deleteRepairProblemText', $log);
        $repairmodel->addLog('repair_setting', $lastSql, $text, $id, ACTION_NAME);
        if ($del) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '删除该故障问题成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '删除该故障问题失败'));
        }
    }
}