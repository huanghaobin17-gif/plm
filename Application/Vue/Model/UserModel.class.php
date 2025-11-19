<?php

namespace Vue\Model;

use Think\Model;
use Think\Model\RelationModel;

class UserModel extends CommonModel
{
    private $MODULE = 'BaseSetting';

    protected $_validate = array(
        array('username', 'require', '用户名不能为空'),
        array('password', '/^[a-zA-Z0-9]{4,15}/', '用户密码只能为数字和字母且必须在4-18位之间'),
        array('checkpassword', 'password', '用户确认密码不正确', 1, 'confirm'),
        array('telephone', '/^((0\d{2,3}-\d{7,8})|(1\d{10}))$/', '请填入正确的电话号码'),
        array('roleid', 'require', '请选择角色'),
    );

    public function setSession($user)
    {
        $deWhere['hospital_id'] = $user['job_hospitalid'];
        $de = [];
        if ($user['is_super'] == C('YES_STATUS')) {
            //超级管理员
            $menuidarr = $this->DB_get_all('menu', '*', array('status' => 1));
            //获取部门信息
            $de = $this->DB_get_one('department', 'group_concat(departid) as departids', $deWhere);
        } elseif ($user['is_supplier'] == C('YES_STATUS')) {
            $menuWhere['name'] = ['IN', C('IS_SUPPLIER_MENU')];
            //厂商用户
            $menuidarr = $this->DB_get_all('menu', '*', $menuWhere, 'menuid');
            if (!$menuidarr) {
                //没有分配权限
                return array('status' => -1, 'msg' => C('_LOGIN_USER_NOT_MENU_MSG_'));
            }
        } else {
            //获取用户roleid和menu
            $roleidarr = $this->DB_get_one('user_role', 'group_concat(roleid) as roleids', array('userid' => $user['userid']));
            if (!$roleidarr['roleids']) {
                //没有分配角色
                return array('status' => -1, 'msg' => C('_LOGIN_USER_NOT_ROLE_MSG_'));
            }
            $menuidarr = $this->DB_get_all('role_menu', 'menuid', array('roleid' => array('in', $roleidarr['roleids'])), 'menuid');
            if (!$menuidarr) {
                //没有分配权限
                return array('status' => -1, 'msg' => C('_LOGIN_USER_NOT_MENU_MSG_'));
            }
            $menuidstr = [];
            foreach ($menuidarr as $k => $v) {
                $menuidstr[] = $v['menuid'];
            }
            if ($menuidstr) {
                $menuidarr = $this->DB_get_all('menu', '*', array('menuid' => array('in', $menuidstr), 'status' => 1), '', 'orderID asc');
            }
            //获取部门信息
            $join = "LEFT JOIN sb_department AS B ON A.departid = B.departid";
            $deWhere['userid'] = array('EQ', $user['userid']);
            $de = $this->DB_get_one_join('user_department', 'A', 'group_concat(A.departid) as departids', $join, $deWhere);
        }
        $leftShowMenu = array();
        $sessionmid = array();
        foreach ($menuidarr as $k => $v) {
            $sessionmid[] = $v['menuid'];
            $leftShowMenu[] = $v;
        }
        if ($user['is_super'] != C('YES_STATUS')) {
            //不是超级管理员要补全menu
            $leftShowMenu = $this->getParentMenu($leftShowMenu);
        }
        //查询工作医院名称
        $jobhosname = $this->DB_get_one('hospital', 'hospital_name', array('hospital_id' => $user['job_hospitalid']));
        session('leftShowMenu', $leftShowMenu);
        session('sessionmid', $sessionmid);
        if ($de['departids']) {
            session('departid', $de['departids']);
        } else {
            session('departid', '-1');
        }

        session('openid', $user['openid']);
        session('is_supplier', $user['is_supplier']);
        session('olsid', $user['olsid']);
        session('userid', $user['userid']);
        session('job_departid', $user['job_departid']);
        session('isSuper', $user['is_super']);
        session('username', $user['username']);
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        $user['telephone'] = strlen($user['telephone']) > 12 ? $SM4->decrypt($user['telephone']) : $user['telephone'];
        session('telephone', $user['telephone']);
        session('job_hospitalid', $user['job_hospitalid']);
        session('manager_hospitalid', $user['manager_hospitalid']);
        session('current_hospitalid', $user['job_hospitalid']);
        session('current_hospitalname', $jobhosname['hospital_name']);
//        $TasksController = new TasksController();
//        $TasksController->getTask();
        return array('status' => 1, 'msg' => '成功登陆');
    }

    //补全menu
    public function getParentMenu($leftShowMenu)
    {
        $parentid = array();
        foreach ($leftShowMenu as &$one) {
            $parentid[] = $one['parentid'];
        }
        $parentid = array_unique($parentid);
        //不是超级管理员 要补全menu
        $parentMenuidarr = $this->DB_get_all('menu', '*', array('menuid' => array('in', $parentid), 'status' => 1), '', 'orderID asc');
        $newParentid = false;
        foreach ($parentMenuidarr as &$one) {
            if ($one['parentid'] != 0) {
                $newParentid = true;
                break;
            }
        }
        if ($newParentid) {
            $parentMenuidarr = $this->getParentMenu($parentMenuidarr);
        }
        return array_merge($parentMenuidarr, $leftShowMenu);
    }

    /*
     * 获取对应openid的用户
     */
    public function get_user_info($openid)
    {
        return $this->DB_get_all('user','userid,username,openid',array('openid'=>$openid,'is_delete'=>0,'status'=>1));
    }

}
