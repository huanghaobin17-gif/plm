<?php

namespace Admin\Controller\BaseSetting;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\MenuModel;
use Admin\Model\RoleModel;
use Admin\Model\StatisticsModel;
use Admin\Model\UserRoleModel;

class PrivilegeController extends CheckLoginController
{
    private $MODULE = 'BaseSetting';

    /*
     * 获取角色列表
     */
    public function getRoleList()
    {
        if (IS_POST) {
            //根据搜索条件获取用户列表
            $roleModel = new RoleModel();
            $result = $roleModel->getRoleLists();
            $this->ajaxReturn($result, 'json');
        } else {
            $type = I('GET.type');
            if ($type == 'getRole') {
                $roleModel = new RoleModel();
                $where['is_default'] = array('EQ', C('NO_STATUS'));
                $where['is_delete'] = array('EQ', C('NO_STATUS'));
                $where['hospital_id'] = session('current_hospitalid');
                $arr = $roleModel->getAllRoles($where);
                $this->ajaxReturn($arr, 'JSON');
            } else {
                $this->assign('getRoleList', get_url());
                $this->display();
            }
        }
    }

    /*
     * 添加角色
     */
    public function addRole()
    {
        if (IS_POST) {
            $is_default = I('POST.is_default') ? C('YES_STATUS') : C('NO_STATUS');
            $data['role'] = trim(I('POST.rolename'));
            $data['remark'] = trim(I('POST.remark'));
            $data['status'] = I('POST.status');
            $data['is_default'] = $is_default;
            $data['adduser'] = session('username');
            $data['addtime'] = time();
            $data['edituser'] = session('username');
            $data['edittime'] = time();
            $data['hospital_id'] = session('current_hospitalid');
            $roleModel = new RoleModel();
            //查询是否存在该角色
            $rolename = $roleModel->checkRoleNameIsExist($data['role'], $is_default);
            if ($rolename['role']) {
                $this->ajaxReturn(array('status' => -1, 'msg' => C('_ROLE_NAME_IS_EXIST_MSG_')), 'json');
            }
            $in = $roleModel->insertData('role', $data);
            //日志行为记录文字
            $log['user'] = $data['adduser'];
            $text = getLogText('addRoleLogText', $log);
            $roleModel->addLog('role', M()->getLastSql(), $text, $in, '');
            if ($in) {
                $this->ajaxReturn(array('status' => 1, 'msg' => C('_ADD_ROLE_SUCCESS_MSG_')), 'json');
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => C('_ADD_ROLE_FAIL_MSG_')), 'json');
            }
        } else {
            $is_default = I('GET.is_default');
            $this->assign('is_default', $is_default);
            $this->display();
        }
    }

    /*
     * 修改角色
     */
    public function editRole()
    {
        if (IS_POST) {
            $is_default = I('POST.is_default') ? C('YES_STATUS') : C('NO_STATUS');
            $data['role'] = trim(I('POST.rolename'));
            $data['remark'] = trim(I('POST.remark'));
            $data['status'] = I('POST.status');
            $data['edituser'] = session('username');
            $data['edittime'] = time();
            $where['roleid'] = I('POST.roleid');
            $roleModel = new RoleModel();
            //查询是否与数据库其他角色名称同名
            $checkWhere['role'] = array('EQ', $data['role']);
            $checkWhere['roleid'] = array('NEQ', $where['roleid']);
            $checkWhere['is_default'] = array('EQ', $is_default);
            $checkWhere['hospital_id'] = array('EQ', session('current_hospitalid'));
            $checkname = $roleModel->DB_get_one('role', 'roleid', $checkWhere);
            if ($checkname['roleid']) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '该角色名称已存在！'), 'json');
            }
            $in = $roleModel->updateData('role', $data, $where);
            //日志行为记录文字
            $log['user'] = $data['edituser'];
            $text = getLogText('editRoleLogText', $log);
            $roleModel->addLog('user', M()->getLastSql(), $text, $where['roleid'], '');
            if ($in) {
                $this->ajaxReturn(array('status' => 1, 'msg' => C('_EDIT_ROLE_SUCCESS_MSG_')), 'json');
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => C('_EDIT_ROLE_FAIL_MSG_')), 'json');
            }
        } else {
            $is_default = I('GET.is_default');
            $where['roleid'] = I('GET.roleid');
            $roleModel = new RoleModel();
            $returnArr = $roleModel->DB_get_one('role', 'roleid,role,remark,status', $where);
            $this->assign('data', $returnArr);
            $this->assign('url', ACTION_NAME);
            $this->assign('is_default', $is_default);
            $this->display();
        }
    }

    /*
     * 删除角色
     */
    public function deleteRole()
    {
        if (IS_POST) {
            $roleModel = new RoleModel();
            $where['roleid'] = I('POST.roleid');
            $username = $roleModel->DB_get_one('role', 'role', $where);
            //删除对应角色信息
            $in = $roleModel->updateData('role', array('is_delete' => 1, 'edittime' => time()), $where);
            //$in = $roleModel->deleteData('role', $where);
            //日志行为记录文字
            $log['role'] = $username['role'];
            $text = getLogText('delRoleLogText', $log);
            $roleModel->addLog('role', M()->getLastSql(), $text, $where['roleid'], '');
            $userRoleModel = new UserRoleModel();
            if ($in) {
                //删除角色对应的user
                //$userRoleModel->deleteData('user_role', $where);
                //删除角色对应的menu
                //$userRoleModel->deleteData('role_menu', $where);
                $this->ajaxReturn(array('status' => 1, 'msg' => C('_DELETE_ROLE_SUCCESS_MSG_')), 'json');
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => C('_DELETE_ROLE_FAIL_MSG_')), 'json');
            }
        }
    }

    /*
     * 角色用户维护
     */
    public function editRoleUser()
    {
        $User_roleModel = new UserRoleModel();
        if (IS_POST) {
            $roleid_edit = I('post.roleid_edit');
            $roleid_add = I('post.roleid_add');
            $roleid = I('post.roleid');
            if ($roleid_edit > 0) {
                $where = "userid in($roleid_edit) and roleid=$roleid";
                $edit = $User_roleModel->deleteData('user_role', $where);
            }
            if ($roleid_add > 0) {
                $arr = explode(',', $roleid_add);
                $addarr = [];
                foreach ($arr as $key => $one) {
                    $addarr[$key]['userid'] = $one;
                    $addarr[$key]['roleid'] = $roleid;
                }
                $add = $User_roleModel->insertDataALL('user_role', $addarr);
            }
            if ($edit || $add) {
                //日志行为记录文字
                $text = getLogText('editRoleUserLogText');
                $User_roleModel->addLog('user_role', '', $text, '', '');
                $this->ajaxReturn(array('status' => 1, 'msg' => '更新成功'), 'json');
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '没有修改'), 'json');
            }
        } else {
            $roleid = I('GET.roleid');
            $hosid = session('current_hospitalid');
            $join = "LEFT JOIN sb_user_role AS B ON B.userid=A.userid and B.roleid = $roleid";
            $where['A.status'] = C('YES_STATUS');
            $where['A.is_supplier'] = C('NO_STATUS');
            $where['A.is_delete'] = C('NO_STATUS');
            $where['A.is_super'] = array('neq', C('YES_STATUS'));
            $where['A.job_hospitalid'] = $hosid;
            $fields = 'A.username,A.userid,B.roleid';
            $list = $User_roleModel->DB_get_all_join('user', 'A', $fields, $join, $where, '', 'A.userid desc', '');
            $role = $User_roleModel->DB_get_one('role', 'role,roleid', array('roleid' => $roleid));
            $this->list = $list;
            $this->title = '用户维护';
            $this->rolename = $role['role'];
            $this->roleid = $role['roleid'];
            $this->display();
        }

    }

    /*
     * 角色权限维护
     */
    public function editRolePrivi()
    {    
      
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'getRoleMenu':
                    $menuModel = new MenuModel();
                    $result = $menuModel->getRoleMenu();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'target_setting':
                    $menuModel = new MenuModel();
                    $result = $menuModel->save_target_setting();
                    $this->ajaxReturn($result, 'json');
                    break;
                default :
                    $data['roleid'] = I('POST.roleid');
                    $data['menuid'] = explode(',', I('POST.menuid'));
                    $menuModel = new MenuModel();
                    $res = $menuModel->updateRoleMenu($data);
                    //日志行为记录文字
                    $text = getLogText('editRolePriviLogText');
                    $menuModel->addLog('user', '', $text, '', '');
                    if ($res) {
                        $this->ajaxReturn(array('status' => 1, 'msg' => C('_UPDATE_ROLE_MENU_SUCC_MSG_')), 'json');
                    } else {
                        $this->ajaxReturn(array('status' => -1, 'msg' => C('_UPDATE_ROLE_MENU_FAIL_MSG_')), 'json');
                    }
                    break;
            }
        } else {
            $is_default = I('GET.is_default');
            //查询所有menu
            $roleid = I('GET.roleid');
            $action = I('GET.action');
            if (!$roleid) {
                $this->error('异常参数');
            }
            $menuModel = new MenuModel();
            if ($action == 'target_setting') {
                //查询当前角色原有设置
                $chart_setting = $menuModel->get_all_target_setting($roleid);
                $staModel = new StatisticsModel();
                //查询系统设置的统计显示设置
                $sys_showids = $staModel->get_target_setting();
                $this->assign('role_id', $roleid);
                $this->assign('sys_showids', $sys_showids);
                $this->assign('show_ids', $chart_setting['detail']['chart_id']);
                $this->assign('show_survey_ids', $chart_setting['survey']['chart_id']);
                $this->display('target_setting');
            } else {
                //查询当前角色所拥有的权限
                $res = $menuModel->getAllMenu();
                if ($is_default != C('YES_STATUS')) {
                    $is_defaultRole = [];
                    $roleMenus = [];
                    include APP_PATH . "Common/Conf/role.php";
                    if ($roleMenus) {

                        $this->assign('is_defaultRole', $roleMenus);
                    }
                }
                $this->assign('roleid', $roleid);
                $this->assign('is_default', $is_default);
                $this->assign('data', $res);
                $this->display();

            }
        }
    }
}
