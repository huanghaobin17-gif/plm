<?php

namespace App\Model;

use App\Service\UserInfo\UserInfo;

class UserModel extends CommonModel
{
    private $MODULE = 'BaseSetting';

    protected $_validate = [
        ['username', 'require', '用户名不能为空'],
        ['password', '/^[a-zA-Z0-9]{4,15}/', '用户密码只能为数字和字母且必须在4-18位之间'],
        ['checkpassword', 'password', '用户确认密码不正确', 1, 'confirm'],
        ['telephone', '/^((0\d{2,3}-\d{7,8})|(1\d{10}))$/', '请填入正确的电话号码'],
        ['roleid', 'require', '请选择角色'],
    ];

    public function setSession($user)
    {
        $deWhere['hospital_id'] = $user['job_hospitalid'];
        $de                     = [];
        if ($user['is_super'] == C('YES_STATUS')) {
            //超级管理员
            $menuidarr = $this->DB_get_all('menu', '*', ['status' => 1]);
            //获取部门信息
            $de = $this->DB_get_one('department', 'group_concat(departid) as departids', $deWhere);
        } elseif ($user['is_supplier'] == C('YES_STATUS')) {
            $menuWhere['name'] = ['IN', C('IS_SUPPLIER_MENU')];
            //厂商用户
            $menuidarr = $this->DB_get_all('menu', '*', $menuWhere, 'menuid');
            if (!$menuidarr) {
                //没有分配权限
                return ['status' => -1, 'msg' => C('_LOGIN_USER_NOT_MENU_MSG_')];
            }
        } else {
            //获取用户roleid和menu
            $roleidarr = $this->DB_get_one('user_role', 'group_concat(roleid) as roleids',
                ['userid' => $user['userid']]);
            if (!$roleidarr['roleids']) {
                //没有分配角色
                return ['status' => -1, 'msg' => C('_LOGIN_USER_NOT_ROLE_MSG_')];
            }
            $menuidarr = $this->DB_get_all('role_menu', 'menuid', ['roleid' => ['in', $roleidarr['roleids']]],
                'menuid');
            if (!$menuidarr) {
                //没有分配权限
                return ['status' => -1, 'msg' => C('_LOGIN_USER_NOT_MENU_MSG_')];
            }
            $menuidstr = [];
            foreach ($menuidarr as $k => $v) {
                $menuidstr[] = $v['menuid'];
            }
            if ($menuidstr) {
                $menuidarr = $this->DB_get_all('menu', '*', ['menuid' => ['in', $menuidstr], 'status' => 1], '',
                    'orderID asc');
            }
            //获取部门信息
            $join              = "LEFT JOIN sb_department AS B ON A.departid = B.departid";
            $deWhere['userid'] = ['EQ', $user['userid']];
            $de                = $this->DB_get_one_join('user_department', 'A', 'group_concat(A.departid) as departids',
                $join, $deWhere);
        }
        $leftShowMenu = [];
        $sessionmid   = [];
        foreach ($menuidarr as $k => $v) {
            $sessionmid[]   = $v['menuid'];
            $leftShowMenu[] = $v;
        }
        if ($user['is_super'] != C('YES_STATUS')) {
            //不是超级管理员要补全menu
            $leftShowMenu = $this->getParentMenu($leftShowMenu);
        }
        //查询工作医院名称
        $jobhosname = $this->DB_get_one('hospital', 'hospital_name', ['hospital_id' => $user['job_hospitalid']]);

        if (UserInfo::getPlatform() != C('VUE_APP_APP')) {
            UserInfo::getInstance()->set('leftShowMenu', $leftShowMenu);
            UserInfo::getInstance()->set('sessionmid', $sessionmid);
            if ($de['departids']) {
                UserInfo::getInstance()->set('departid', $de['departids']);
            } else {
                UserInfo::getInstance()->set('departid', '-1');
            }

            UserInfo::getInstance()->set('openid', $user['openid']);
            UserInfo::getInstance()->set('is_supplier', $user['is_supplier']);
            UserInfo::getInstance()->set('olsid', $user['olsid']);
            UserInfo::getInstance()->set('userid', $user['userid']);
            UserInfo::getInstance()->set('job_departid', $user['job_departid']);
            UserInfo::getInstance()->set('isSuper', $user['is_super']);
            UserInfo::getInstance()->set('username', $user['username']);
            Vendor('SM4.SM4');
            $SM4               = new \SM4();
            $user['telephone'] = strlen($user['telephone']) > 12 ? $SM4->decrypt($user['telephone']) : $user['telephone'];
            UserInfo::getInstance()->set('telephone', $user['telephone']);
            UserInfo::getInstance()->set('job_hospitalid', $user['job_hospitalid']);
            UserInfo::getInstance()->set('manager_hospitalid', $user['manager_hospitalid']);
            UserInfo::getInstance()->set('current_hospitalid', $user['job_hospitalid']);
            UserInfo::getInstance()->set('current_hospitalname', $jobhosname['hospital_name']);
//            header("Auth:" . session_id());
//            $cookie = session_name() . '=' . session_id() . ';SameSite=None;secure;';
//            header('Set-Cookie: ' . $cookie);
        } else {
            $session  = [
                'leftShowMenu'         => $leftShowMenu,
                'sessionmid'           => $sessionmid,
                'departid'             => !empty($de['departids']) ? $de['departids'] : '-1',
                'openid'               => $user['openid'],
                'is_supplier'          => $user['is_supplier'],
                'olsid'                => $user['olsid'],
                'userid'               => $user['userid'],
                'job_departid'         => $user['job_departid'],
                'isSuper'              => $user['is_super'],
                'username'             => $user['username'],
                'telephone'            => $user['telephone'],
                'job_hospitalid'       => $user['job_hospitalid'],
                'manager_hospitalid'   => $user['manager_hospitalid'],
                'current_hospitalid'   => $user['job_hospitalid'],
                'current_hospitalname' => $jobhosname['hospital_name'],
            ];
            $data     = [
                'userid'     => $user['userid'],
                'token'      => session_id(),
                'session'    => json_encode($session, JSON_UNESCAPED_UNICODE),
                'status'     => 1,
                'login_time' => date('Y-m-d H:i:s'),
            ];
            $getToken = $this->DB_get_one('user_app_token', 'id', ['userid' => $user['userid']]);
            if (empty($getToken)) {
                $this->insertData('user_app_token', $data);
            } else {
                $this->updateData('user_app_token', $data, ['id' => $getToken['id']]);
            }
            foreach ($session as $k => $v) {
                UserInfo::getInstance()->set($k, $v);
            }
        }
//        $TasksController = new TasksController();
//        $TasksController->getTask();
        return ['status' => 1, 'msg' => '成功登陆', 'token' => !empty($data['token']) ? $data['token'] : null];
    }

    //补全menu
    public function getParentMenu($leftShowMenu)
    {
        $parentid = [];
        foreach ($leftShowMenu as &$one) {
            $parentid[] = $one['parentid'];
        }
        $parentid = array_unique($parentid);
        //不是超级管理员 要补全menu
        $parentMenuidarr = $this->DB_get_all('menu', '*', ['menuid' => ['in', $parentid], 'status' => 1], '',
            'orderID asc');
        $newParentid     = false;
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
        return $this->DB_get_all('user', 'userid,username,openid',
            ['openid' => $openid, 'is_delete' => 0, 'status' => 1]);
    }


    public function get_qy_user_info($userid)
    {
        return $this->DB_get_all('user', '*', ['qy_user_id' => $userid, 'is_delete' => 0, 'status' => 1]);
    }
}
