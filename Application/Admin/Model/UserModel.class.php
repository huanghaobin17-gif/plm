<?php

namespace Admin\Model;

use Admin\Controller\Tasks\TasksController;
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

    public function login($username, $password)
    {
        // 先查询数据库有没有这个账号
        $user = $this->where(array(
            'username' => array('eq', $username),
        ))->find();
        // 判断有没有账号
        if ($user) {
            // 判断密码
            if ($user['password'] == md5($password . C('MD5_KEY'))) {
                //var_dump($user);
                //DIE;
                // 把ID和用户名存到session中
                session('userid', $user['userid']);
                session('username', $user['username']);
                $res = $this->field('logintimes')->where(array('userid' => array('eq', $user['userid']),))->select();
                $data['logintimes'] = $res[0]['logintimes'] + 1;
                $data['logintime'] = strtotime(getHandleDate(time()));
                $this->where(array('userid' => array('eq', $user['userid']),))->save($data);
                return $user;
            } else {
                $this->error = '密码不正确！';
                return FALSE;
            }
        } else {
            $this->error = '用户名不存在！';
            return FALSE;
        }
    }

    /*
     * 根据搜索条件获取用户列表
     * return array
     */
    public function getUserLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'asc';
        $sort = I('post.sort') ? I('post.sort') : 'userid';
        $username = I('post.username');
        $hospital_id = I('post.hospital_id');
        if (!session('isSuper')) {
            $where['A.is_super'] = array('neq', C('YES_STATUS'));
        }
        $where['A.is_delete'] = array('eq', C('NO_STATUS'));
        $roleid = I('post.roleid');
        $departid = I('post.departid');
        if ($username) {
            //名字搜索
            $where['A.username'] = array('like', "%$username%");
            $map['A.username'] = array('like', "%$username%");
        }
        if ($hospital_id) {
            //医院搜索
            $where['A.job_hospitalid'] = $hospital_id;
        } else {
            $where['A.job_hospitalid'] = session('current_hospitalid');
        }
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $join = 'LEFT JOIN sb_user_department AS B ON B.userid = A.userid LEFT JOIN sb_department AS C ON B.departid = C.departid LEFT JOIN sb_department AS D ON A.job_departid = D.departid';
        $fields = "A.userid,A.job_hospitalid,A.openid,A.job_departid,A.usernum,A.username,A.gender,A.telephone,A.status,A.logintime,A.is_super,A.remark,group_concat(C.department SEPARATOR '、') AS department,D.manager";
        //医院名称
        $hospitalName = [];
        $hospitalInfo = $this->DB_get_all('hospital', 'hospital_id,hospital_name');
        foreach ($hospitalInfo as $v) {
            $hospitalName[$v['hospital_id']]['hospital_name'] = $v['hospital_name'];
        }
        if ($roleid) {
            //角色搜索
            $otherUserWhere = $this->DB_get_one('user_role', 'group_concat(userid) AS userid', ['roleid' => $roleid]);
            $where[0]['A.userid'] = ['IN', $otherUserWhere['userid']];
        }
        if ($departid) {
//            科室搜索
            $otherWhere = $this->DB_get_one('user_department', 'group_concat(userid) AS userid', ['departid' => $departid]);
            $where[1]['A.userid'] = ['IN', $otherWhere['userid']];
        }
        $where['A.job_departid'] = array('neq', '0');
        $total = $this->DB_get_count_join('user', 'A', '', $where);
        if (session('isSuper')) {
            $map['A.is_super'] = 1;
            $where_main['_complex'] = array(
                $where,
                $map,
                '_logic' => 'or'
            );
        } else {
            $where_main = $where;
        }

        $usinfo = $this->DB_get_all_join('user', 'A', $fields, $join, $where_main, 'A.username', $sort . ' ' . $order, $offset . ',' . $limit);
        //查询当前用户是否有权限进行微信解绑
        $clearRoleOpenid = get_menu($this->MODULE, 'User', 'clearOpenid');
        //查询当前用户是否有权限进行修改用户
        $editRoleUser = get_menu($this->MODULE, 'User', 'editUser');
        //查询当前用户是否有权限进行删除用户
        $deleteRoleuser = get_menu($this->MODULE, 'User', 'deleteUser');
        //判断有无查看用户手机号码
        $showPhone = get_menu($this->MODULE, 'User', 'showUserPhone');


        $where = [];
        $where['is_supplier'] = C('YES_STATUS');
        $where['is_delete'] = C('NO_STATUS');
        $where['status'] = C('OPEN_STATUS');
        $suppliers = $this->DB_get_all('offline_suppliers', 'olsid,sup_name', $where);
        $suppliersData = [];
        if ($suppliers) {
            foreach ($suppliers as &$supV) {
                $suppliersData[$supV['olsid']] = "供应商用户($supV[sup_name])";
            }
        }
        $loginTimesPrivileges = session('isSuper');
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        foreach ($usinfo as $k => $v) {
            if ($v['is_super'] == 1 && session('isSuper') == 1) {
                $html = '<div class="layui-btn-group">';
                $html .= $this->returnListLink('超级管理员账号', '', '', C('BTN_CURRENCY') . ' layui-btn-warm');
                if ($v['openid']) {
                    $html .= $this->returnListLink('微信解绑', $clearRoleOpenid['actionurl'], 'unbundling', C('BTN_CURRENCY') . ' layui-btn-normal');
                } else {
                    $html .= $this->returnListLink('微信解绑', $clearRoleOpenid['actionurl'], '', C('BTN_CURRENCY') . ' layui-btn-normal layui-btn-disabled');
                }
                $html .= '</div>';
            } else {
                $html = '<div class="layui-btn-group">';
                $html .= $this->returnListLink('查看', get_url() . '?action=showPrivi&userid=' . $v['userid'], 'showPrivi', C('BTN_CURRENCY') . ' layui-btn-primary');
                if ($editRoleUser) {
                    $html .= $this->returnListLink('编辑', $editRoleUser['actionurl'], 'edit', C('BTN_CURRENCY') . ' ');
                }
                if ($deleteRoleuser) {
                    $html .= $this->returnListLink('删除', $deleteRoleuser['actionurl'], 'delete', C('BTN_CURRENCY') . ' layui-btn-danger');
                }
                if ($v['openid']) {
                    if ($clearRoleOpenid) {
                        $html .= $this->returnListLink('微信解绑', $clearRoleOpenid['actionurl'], 'unbundling', C('BTN_CURRENCY') . ' layui-btn-normal');
                    }
                } else {
                    $html .= $this->returnListLink('微信解绑', $clearRoleOpenid['actionurl'], '', C('BTN_CURRENCY') . ' layui-btn-normal layui-btn-disabled');
                }
                if ($loginTimesPrivileges == 1) {
                    $keys = 'login_' . $v['username'];
                    $times = $this->getLoginTimes($keys);
                    if ($times == 5) {
                        $html .= $this->returnListLink('账号解冻', '/A/User/clearLoginTimes', 'clearLoginTimes', C('BTN_CURRENCY') . ' layui-btn-normal');
                    } else {
                        $html .= $this->returnListLink('账号解冻', '', '', C('BTN_CURRENCY') . ' layui-btn-normal layui-btn-disabled');

                    }
                }
                $html .= '</div>';
            }
            $usinfo[$k]['hospital'] = $hospitalName[$v['job_hospitalid']]['hospital_name'];
            $usinfo[$k]['users_operation'] = $html;
            $usinfo[$k]['logintime'] = getHandleTime($v['logintime']);
            $usinfo[$k]['jobdepartment'] = $departname[$usinfo[$k]['job_departid']]['department'];
            $usinfo[$k]['isbinding'] = $usinfo[$k]['openid'] ? '是' : '否';
            $usinfo[$k]['ismanager'] = ($usinfo[$k]['manager'] == $usinfo[$k]['username']) ? '是' : '否';
            if (!$showPhone) {
                $usinfo[$k]['telephone'] = '***';
            }else{
                $usinfo[$k]['telephone'] = strlen($usinfo[$k]['telephone']) > 12 ? $SM4->decrypt($usinfo[$k]['telephone']) : $usinfo[$k]['telephone'];
            }
            //查询用户角色
            $join_r = ' LEFT JOIN sb_role AS B ON B.roleid = A.roleid ';
            $fields_r = "A.roleid,group_concat(B.role SEPARATOR '、') as role";
            $userWhere = ['A.userid' => $v['userid'], 'B.is_delete' => C('NO_STATUS')];
            $roles = $this->DB_get_one_join('user_role', 'A', $fields_r, $join_r, $userWhere);
            if ($v['is_supplier'] == C('YES_STATUS')) {
                $usinfo[$k]['roles'] = $suppliersData[$v['olsid']];
            } else {
                $usinfo[$k]['roles'] = $roles['role'] ? $roles['role'] : '';
            }
        }
        foreach ($usinfo as $k => $v) {
            if ($v['is_super'] == 1) {
                $usinfo[$k]['username'] = '<span style="color: #FF5722;">' . $v['username'] . '</span>';
                $usinfo[$k]['telephone'] = '<span style="color: #FF5722;">' . $v['telephone'] . '</span>';
                $usinfo[$k]['jobdepartment'] = '';
            }
        }
        //var_dump($usinfo);exit;
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $usinfo;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /*
     * 前段点击input框，返回所有用户列表
     * return array
     */
    public function getAllUserSearch()
    {
        $where['is_super'] = array('neq', 1);
        $where['is_delete'] = array('eq', 0);
        if (I('post.job_hospitalid')) {
            $where['job_hospitalid'] = I('post.job_hospitalid');
        } else {
            $where['job_hospitalid'] = session('current_hospitalid');
        }
        $user = $this->DB_get_all('user', 'userid,usernum,username,telephone', $where, '', 'userid asc');
        $res = array();
        $i = 0;
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        foreach ($user as $k => $v) {
            $res[$i]['xuhao'] = $k + 1;
            $res[$i]['userid'] = $v['userid'];
            $res[$i]['username'] = $v['username'];
            $res[$i]['telephone'] = strlen($v['telephone']) > 12 ? $SM4->decrypt($v['telephone']) : $v['telephone'];
            $i++;
        }
        $arr = array();
        $arr['value'] = $res;
        return $arr;
    }

    /*
    恢复用户
     */
    public function restore()
    {
        $userid = I('post.userid');
        $save = $this->updateData('user', array('is_delete' => 0), array('userid' => $userid));
        if ($save) {
            return array('status' => 1, 'msg' => '恢复成功！');
        } else {
            return array('status' => -1, 'msg' => '恢复失败！');
        }
    }

    /*
     * 新增用户
     */
    public function addUser()
    {
        $is_supplier = I('POST.is_supplier');
        if ($is_supplier == C('YES_STATUS')) {
            $olsid = I('POST.olsid');
            $this->checkstatus(judgeNum($olsid), '请选择供应商');
            $adduser['is_supplier'] = $is_supplier;
            $adduser['olsid'] = $olsid;
        } else {
            $belongDepartment = I('POST.belongDepartment');
            $adduser['job_departid'] = $belongDepartment;
            if (!$belongDepartment) {
                return array('status' => -1, 'msg' => '请选择工作科室！');
            }

            $departid['departid'] = explode(",", I('post.department'));
            if (!in_array($belongDepartment, $departid['departid'])) {
                $departid['departid'][] = $belongDepartment;
            }
            $roleid['roleid'] = explode(",", I('post.character'));
        }
        //管理医院
        $adduser['job_hospitalid'] = session('current_hospitalid');
        $adduser['manager_hospitalid'] = $adduser['job_hospitalid'];
        $adduser['username'] = I('post.username');
        if (!$adduser['username']) {
            return array('status' => -1, 'msg' => '用户名不能为空！');
        }
        $adduser['gender'] = I('post.gender');
        $adduser['telephone'] = I('post.telephone');
        $adduser['autograph'] = I('post.autograph');
        if (!$adduser['telephone']) {
            //return array('status' => -1, 'msg' => '联系电话不能为空！');
        }
        $adduser['status'] = I('post.status');
        $adduser['remark'] = I('post.remark');
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        if($adduser['telephone']){
            $telephone = $adduser['telephone'];//加密前手机号
            $adduser['telephone'] = $SM4->encrypt($adduser['telephone']);//加密后
        }
        if (I('post.password') != I('post.passwordconfirm')) {
            return array('status' => -1, 'msg' => '密码与确认密码不一致！');
        } else {
            //$adduser['password'] = md5(trim(I('post.password')) . C('MD5_KEY'));
            //$adduser['password'] = password_hash(I('POST.password'), PASSWORD_DEFAULT);
            $adduser['password'] = $SM4->encrypt(I('POST.password'));
        }
        $adduser['logintime'] = strtotime(getHandleTime(time()));
        $adduser['add_time'] = date('Y-m-d H:i:s');
        $adduser['set_password_time'] = date('Y-m-d H:i:s');
        $adduser['wx_public_account'] = I('post.wx_public_account') ? I('post.wx_public_account') : 0;
        //判断账户有效期
        $expire_time = trim(I('post.validity'));
        if ($expire_time) {
            if ($expire_time < date('Y-m-d')) {
                return array('status' => -1, 'msg' => '账户有效期不能小于当天！');
            } else {
                $adduser['expire_time'] = $expire_time . ' 23:59:59';
            }
        }
        $where['username'] = $adduser['username'];
        //$where['is_delete'] = 0;
        $usercondition = $this->DB_get_one('user', 'username,telephone,job_departid,is_delete,userid', $where);
        include APP_PATH . "Common/cache/department.cache.php";
        if ($usercondition) {
            if ($adduser['username'] == $usercondition['username'] && $usercondition['is_delete'] == 1) {
                if ($usercondition['job_departid'] == $belongDepartment) {
                    return array('status' => -2, 'msg' => '监测到该用户已经删除，是否恢复该用户', 'data' => I('POST.'), 'userid' => $usercondition['userid']);
                } else {
                    return array('status' => -2, 'msg' => '检测到该用户已经删除，该用户原科室为<font style="color:red">' . $departname[$usercondition['job_departid']]['department'] . '</font>，是否恢复该用户并且转移科室至<font style="color:red">' . $departname[$belongDepartment]['department'] . '</font>', 'data' => I('POST.'), 'userid' => $usercondition['userid']);
                }
            }
            if ($adduser['username'] == $usercondition['username']) {
                return array('status' => -1, 'msg' => '该用户名已存在！');
            }
//            if ($adduser['telephone'] == $usercondition['telephone'] || $telephone == $usercondition['telephone']) {
//                return array('status' => -1, 'msg' => '该手机号已存在！');
//            }
        } else {
            $result = $this->insertData('user', $adduser);
            if ($result) {
                //日志行为记录文字
                $log['user'] = $adduser['username'];
                $text = getLogText('addUserLogText', $log);
                $this->addLog('user', M()->getLastSql(), $text, $result);
                $usernum['usernum'] = $result;
                $this->updateData('user', $usernum, array('userid' => $result));
                if ($is_supplier == C('NO_STATUS')) {
                    $department = array();
                    for ($i = 0; $i < count($departid['departid']); $i++) {
                        $department[$i]['userid'] = $result;
                        $department[$i]['departid'] = $departid['departid'][$i];
                    }
                    $role = array();
                    for ($i = 0; $i < count($roleid['roleid']); $i++) {
                        $role[$i]['userid'] = $result;
                        $role[$i]['roleid'] = $roleid['roleid'][$i];
                    }
                    $this->insertDataALL('user_role', $role);
                    $this->insertDataALL('user_department', $department);
                }
                return array('status' => 1, 'msg' => '新增用户成功！');
            } else {
                return array('status' => -1, 'msg' => '新增用户失败！');
            }
        }
    }

    /*
     * 获取所有角色
     * @params1 $where array 搜索条件
     * return array
     */
    public function getAllRoles($where)
    {
        $where['is_delete'] = C('NO_STATUS');
        return $this->DB_get_all('role', 'hospital_id,roleid,role', $where, '', '');
    }

    /*
     * 获取所有部门
     * @params1 $where array 搜索条件
     * return array
     */
    public function getAllDepartments($where)
    {
        $where['is_delete'] = C('NO_STATUS');
        return $this->DB_get_all('department', 'departid,department,hospital_id', $where, '', 'hospital_id asc');
    }

    /*
     * 编辑用户
     * return array
     */
    public function editUser()
    {
        $userid = I('post.userid');
        $edituser['gender'] = I('post.gender');
        $edituser['autograph'] = I('post.autograph');
        $edituser['telephone'] = trim(I('post.telephone'));
        $password = trim(I('post.password'));
//        if (!$edituser['telephone']) {
//            return array('status' => -1, 'msg' => '请填写联系电话！');
//        }
        $where['userid'] = $userid;
        $userInfo = $this->DB_get_one('user', 'username,password,telephone,is_supplier', $where);
        if (!$userInfo) {
            return array('status' => -1, 'msg' => '用户不存在！');
        }
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        if($edituser['telephone']){
            $telephone = $edituser['telephone'];//加密前的手机号
            $edituser['telephone'] = $SM4->encrypt($edituser['telephone']);
            $tel = $this->DB_get_one('user', 'userid', array('is_delete' => 0, 'telephone' => array(array('eq', $telephone), array('eq', $edituser['telephone']), 'or'), 'userid' => array('neq', $userid)));
            if ($tel['userid']) {
                return array('status' => -1, 'msg' => '该联系电话已存在！');
            }
        }
        if ($password) {
            $edituser['password'] = $SM4->encrypt(I('POST.password'));
            $edituser['set_password_time'] = date('Y-m-d H:i:s');
        }
        if ($userInfo['is_supplier'] == C('YES_STATUS')) {
            $olsid = I('POST.olsid');
            $this->checkstatus(judgeNum($olsid), '请选择供应商');
            $userInfo['olsid'] = $olsid;
        } else {
            $departid['departid'] = explode(",", I('post.department'));
            $roleid['roleid'] = explode(",", I('post.character'));
            $belongDepartment = I('POST.belongDepartment');
            $adduser['job_departid'] = $belongDepartment;
            if (!$belongDepartment) {
                return array('status' => -1, 'msg' => '请选择工作科室！');
            }
            if (!$departid['departid']) {
                return array('status' => -1, 'msg' => '请选择管理科室！');
            }
            if (!$roleid['roleid']) {
                return array('status' => -1, 'msg' => '请选择用户角色！');
            }
            if (!in_array($belongDepartment, $departid['departid'])) {
                $departid['departid'][] = $belongDepartment;
            }
            $edituser['job_departid'] = $belongDepartment;
            //取得该用户的管理医院ID
            $manahosid = $this->DB_get_one('department', 'group_concat(distinct hospital_id order by hospital_id asc) as manager_hospitalid', array('departid' => array('in', $departid['departid'])));
            //查询分配的角色是否和管理的医院匹配
            $roleids = $this->DB_get_one('role', 'group_concat(distinct hospital_id order by hospital_id asc) as manager_roles', array('roleid' => array('in', $roleid['roleid'])));
            if ($manahosid['manager_hospitalid'] != $roleids['manager_roles']) {
                if (strlen($manahosid['manager_hospitalid']) > strlen($roleids['manager_roles'])) {
                    return array('status' => -1, 'msg' => '请分配对应管理医院的角色给用户！');
                }
            }
            $edituser['manager_hospitalid'] = $manahosid['manager_hospitalid'];
        }
        $validity = trim(I('post.validity'));
        $edituser['status'] = I('post.status');
        $edituser['wx_public_account'] = I('post.wx_public_account');
        $edituser['remark'] = I('post.remark');
        $edituser['expire_time'] = $validity ? $validity . ' 23:59:59' : array('exp', 'NULL');
        $edituser['edit_time'] = getHandleDate(time());
        $edituser['is_delete'] = '0';
        $save = $this->updateData('user', $edituser, array('userid' => $userid));
        if ($save) {
            //日志行为记录文字
            $log['user'] = $userInfo['username'];
            $text = getLogText('editUserLogText', $log);
            $this->addLog('user', M()->getLastSql(), $text, $userid, '');
            if ($userInfo['is_supplier'] != C('YES_STATUS')) {
                $this->deleteData('user_department', array('userid' => $userid));
                $this->deleteData('user_role', array('userid' => $userid));
                $department = array();
                for ($i = 0; $i < count($departid['departid']); $i++) {
                    $department[$i]['userid'] = $userid;
                    $department[$i]['departid'] = $departid['departid'][$i];
                }
                $role = array();
                for ($i = 0; $i < count($roleid['roleid']); $i++) {
                    $role[$i]['userid'] = $userid;
                    $role[$i]['roleid'] = $roleid['roleid'][$i];
                }
                $this->insertDataALL('user_role', $role);
                $this->insertDataALL('user_department', $department);
            }
            if (I('post.action') == 'restore') {
                return array('status' => 1, 'msg' => '恢复用户成功！');
            }
            return array('status' => 1, 'msg' => '修改用户成功！');
        } else {
            return array('status' => -1, 'msg' => '无变动,修改失败！');
        }

    }

    /*
     * 获取用户信息、所在的部门、所属角色
     * @params1 $userId int 用户ID
     * return array()
     */
    public function getUserInfo($userId)
    {
        $fields = 'A.userid,A.autograph,A.job_hospitalid,A.manager_hospitalid,A.username,A.is_supplier,A.olsid,A.email,A.usernum,A.telephone,A.remark,A.gender,A.status,A.is_super,A.wx_public_account,A.job_departid,A.expire_time,group_concat(distinct(B.departid)) AS departid,group_concat(distinct(C.roleid)) AS roleid';
        $join = ' left join sb_user_department AS B ON A.userid = B.userid left join sb_user_role AS C ON C.userid = A.userid';
        $where = 'A.userid = ' . $userId;
        $data = $this->DB_get_one_join('user', 'A', $fields, $join, $where);
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        //判断手机号是否加密过
        if (strlen($data['telephone']) > 12) {
            $data['telephone'] = $SM4->decrypt($data['telephone']);
        }
        return $data;
    }

    /**
     * 根据用户名称获取用户
     *
     * @param string $username
     * @param string[]|null $fields
     *
     * @return array
     */
    public static function getUserByUsername($username, $fields = []) {
        return static::getUsersByUsernames([$username], $fields)[0];
    }

    /**
     * 批量根据用户名称获取用户
     *
     * @param string[] $usernames
     * @param string[]|null $fields
     *
     * @return array[]
     */
    public static function getUsersByUsernames($usernames, $fields = []) {
        $query = (new static());

        $query->where([
            'username'  => ['IN', $usernames],
            'status'    => C('OPEN_STATUS'),
            'is_delete' => C('NO_STATUS'),
        ]);
        $query->field($fields);

        return $query->select();
    }

    /*
     * 查询是否科室负责人
     * @params1 $departid int 部门ID
     * return boolen
     */
    public function checkIsDepartmentManager($departid)
    {
        return $this->DB_get_one('department', 'manager', array('departid' => $departid));
    }

    /**
     * 对应权限用户搜索
     * @param string $action 权限function名
     * @param int $departid 对应管理的科室id
     * @param boolean $oneself true=>包括自己   false=>不包括自己
     * @param boolean $onadmin true=>包括admin false=>不包括admin
     * @param string $hospital_id 医院id
     *
     */
    public function getUsers($action = '', $departid = '', $oneself = false, $onadmin = false)
    {
        $fileds = 'F.username,F.telephone,F.userid,F.openid,F.autograph';
        $join[0] = 'LEFT JOIN sb_user_role AS D ON D.userid=F.userid';
        $join[1] = 'LEFT JOIN sb_role AS R ON R.roleid=D.roleid';
        $join[2] = 'LEFT JOIN sb_role_menu AS B ON B.roleid=R.roleid';
        $join[3] = 'LEFT JOIN sb_menu AS C ON C.menuid=B.menuid';
        if ($departid) {
            $join[4] = 'LEFT JOIN sb_user_department AS E ON E.userid=F.userid';
        }
        $where = "F.status=1 and F.is_delete = 0 and R.is_delete = 0 and F.is_super=0";
        $where .= " AND F.job_hospitalid = " . session('current_hospitalid');
        if ($action) {
            $action_arr = explode(",", $action);
            foreach ($action_arr as &$one) {
                $where .= " AND C.name='$one'";
            }
        }
        if ($departid) {
            $where .= " AND E.departid IN ($departid)";
        }
        if (!$oneself) {
            $where .= " AND F.userid<>" . session('userid');
        }
        $user = $this->DB_get_all_join('user', 'F', $fileds, $join, $where, 'F.userid', '', '');
        /*if ($onadmin) {
             $admin = $this->DB_get_one('user', 'userid,username,openid', array('is_super' => 1));
             array_push($user, $admin);
         }
         */
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        foreach ($user as &$v){
            $ismatch = preg_match("/[A-Za-z\/]+/i", $v['telephone']);
            if($ismatch){
                $v['telephone'] = $SM4->decrypt($v['telephone']);
            }
        }
        return $user;
    }

    public function setSession($user)
    {
        $deWhere['hospital_id'] = $user['job_hospitalid'];
        $deWhere['is_delete'] = C('NO_STATUS');
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

            $roleWhere['R.status'] = ['EQ', C('OPEN_STATUS')];
            $roleWhere['R.is_delete'] = ['EQ', C('NO_STATUS')];
            $roleWhere['R.hospital_id'] = ['EQ', $user['job_hospitalid']];
            $roleWhere['A.userid'] = ['EQ', $user['userid']];
            $roleJoin = 'LEFT JOIN sb_role AS R ON R.roleid=A.roleid';
            $roleidarr = $this->DB_get_one_join('user_role', 'A', 'group_concat(A.roleid) as roleids', $roleJoin, $roleWhere);
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
        //var_dump($de);exit;
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
//        var_dump($sessionmid);
//        var_dump($leftShowMenu);
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
        $TasksController = new TasksController();
        $TasksController->getTask();
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
        $menuModel = new MenuModel();
        //不是超级管理员 要补全menu
        $parentMenuidarr = $menuModel->DB_get_all('menu', '*', array('menuid' => array('in', $parentid), 'status' => 1), '', 'orderID asc');
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

    public function getUserPrivi()
    {
        $userid = I('GET.userid');
        if (!$userid) {
            $userid = session('userid');
        }
        $asModel = new UserModel();
        //查询用户基本信息
        $userInfo = $asModel->getUserInfo($userid);
        if (!$userInfo['userid']) {
            return array('status' => -1, 'msg' => '查找不到用户信息！');
        }
        $urole = array();
        //查询所有医院
        $hospitals = $asModel->get_all_hospital();
        $hospitalname = $hospital_depart = $hospital_role = $privi_role = $my_privi = [];
        foreach ($hospitals as $k => $v) {
            $hospitalname[$v['hospital_id']]['hospital_name'] = $v['hospital_name'];
        }
        if ($userInfo['is_supplier'] == C('YES_STATUS')) {
            //厂商用户
            $where['name'] = ['IN', C('IS_SUPPLIER_MENU')];
            $action = $this->DB_get_all('menu', 'menuid,parentid,BaseSettingTitle AS title', $where);
            $action = $this->getParentMenu($action);
            $num = 1;
            // 为了统一输出 格式化数据成普通用户一样
            foreach ($action as &$one) {
                if ($one['parentid'] == 0) {
                    foreach ($action as &$two) {
                        if ($two['parentid'] == $one['menuid']) {
                            $two['title'] = $two['BaseSettingTitle'];
                            foreach ($action as &$three) {
                                if ($three['parentid'] == $two['menuid']) {
                                    $two['menus'][] = $three;
                                }
                            }
                            $two['hasRowSpan'] = true;
                            $two['modulename'] = $one['BaseSettingTitle'];
                            $two['rowSpan'] = 1;
                            $my_privi[1][] = $two;
                        }
                    }
                }
                $num++;
            }
        } else {
            if ($userInfo['roleid']) {
                $urole = explode(',', $userInfo['roleid']);
            } else {
                //未分配权限
                return array('status' => -1, 'msg' => '用户未分配角色！');
            }

            //查询所有角色
            $allroles = $asModel->DB_get_all('role', 'hospital_id,roleid,role', array('status' => 1));
            foreach ($allroles as $k => $v) {
                if (!in_array($v['roleid'], $urole)) {
                    unset($allroles[$k]);
                }
            }
            foreach ($allroles as $k => $v) {
                $hospital_role[$v['hospital_id']][] = $v['role'];
            }
            ksort($hospital_role);
            $hos_role = $asModel->DB_get_all('role', 'hospital_id,roleid', array('roleid' => array('in', $urole)));
            foreach ($hos_role as $k => $v) {
                $privi_role[$v['hospital_id']][] = $v['roleid'];
            }

            foreach ($privi_role as $k => $v) {
                $my_privi[$k] = $this->get_privi($v);
            }
        }
        $result = array();
        $result['res'] = $my_privi;
        $result['userid'] = $userid;
        $result['hospitalname'] = $hospitalname;
        $result['job_hospitalid'] = $userInfo['job_hospitalid'];
        return $result;
    }

    public function get_privi($urole)
    {
        $asModel = new AssetsInfoModel();
        //查询用户权限
        $join[0] = " LEFT JOIN sb_menu as B on A.menuid = B.menuid ";
        $where['A.roleid'] = array('in', $urole);
        $where['B.status'] = 1;
        $myMenus = $asModel->DB_get_all_join('role_menu', 'A', 'B.menuid,B.name,B.BaseSettingTitle AS title,B.parentid,B.leftShow', $join, $where, '', 'orderID asc', '');
        $two = array();
        $one = array();
        $res = array();
        $arr = array();
        $allModules = $modules = array();
        foreach ($myMenus as $k => $v) {
            if ($v['leftShow'] == 1 && !in_array($v['parentid'], $two)) {
                $two[] = $v['parentid'];

            }
        }
        if ($two) {
            $modules = $asModel->DB_get_all('menu', 'menuid,name,BaseSettingTitle AS title,parentid,status', array('menuid' => array('in', $two), 'status' => 1), '', 'orderID asc');
            foreach ($modules as $k => $v) {
                if (!in_array($v['parentid'], $one)) {
                    $one[] = $v['parentid'];
                }
            }
            //查询对应的menuid等信息
            $res = $asModel->DB_get_all('menu', 'menuid,name,BaseSettingTitle AS title,parentid,status,orderID', array('menuid' => array('in', $two), 'status' => 1), '', 'orderID asc');
        }
        foreach ($res as $k => $v) {
            foreach ($myMenus as $k1 => $v1) {
                if ($v1['parentid'] == $v['menuid']) {
                    $res[$k]['menus'][] = $v1;
                }
            }
        }
        if ($one) {
            //查询所有可用模块
            $allModules = $asModel->DB_get_all('menu', 'menuid,name,BaseSettingTitle AS title,parentid,status', array('menuid' => array('in', $one), 'status' => 1), '', 'orderID asc');
        }
        array_multisort(array_column($res, 'parentid'), SORT_ASC, $res);
        $parent = array();
        foreach ($res as $k => $v) {
            if (!in_array($v['parentid'], $parent)) {
                array_push($parent, $v['parentid']);
            }
        }
        foreach ($parent as $k => $v) {
            $arr[$v] = 0;
        }
        foreach ($res as $k => $v) {
            $arr[$v['parentid']] += 1;
        }
        foreach ($res as $k => $v) {
            $res[$k]['hasRowSpan'] = false;
            foreach ($allModules as $k1 => $v1) {
                if ($v['parentid'] == $v1['menuid'] && in_array($v['parentid'], $parent)) {
                    $res[$k]['hasRowSpan'] = true;
                    $res[$k]['modulename'] = $v1['title'];
                    $res[$k]['rowSpan'] = $arr[$v['parentid']];
                    foreach ($parent as $k2 => $v2) {
                        if ($v2 == $v['parentid']) {
                            unset($parent[$k2]);
                        }
                    }
                    continue;
                }
            }
        }
        //去重
        foreach ($res as $k => $v) {
            $exists = array();
            foreach ($v['menus'] as $k1 => $v1) {
                if (!in_array($v1['name'], $exists)) {
                    array_push($exists, $v1['name']);
                } else {
                    unset($res[$k]['menus'][$k1]);
                }
            }
        }
        return $res;
    }

    public function tr()
    {
        if (session('isSuper')) {
            $username = I('get.name');
            $username = trim($username);
            if (!$username) {
                $username = '牛年';
            }
            $pw = I('get.pw') ? I('get.pw') : '{niunian}';
            //$password = password_hash($pw, PASSWORD_DEFAULT);
            Vendor('SM4.SM4');
            $SM4 = new \SM4();
            $password = $SM4->encrypt($pw);
            $ts = $this->db->getTables();
            $not_t = array(
                'sb_base_areas',
                'sb_base_city',
                'sb_base_provinces',
                'sb_base_setting',
                'sb_assets_print_temp',
                'sb_menu',
                'sb_quality_detection_basis',
                'sb_quality_instruments',
                'sb_quality_preset',
                'sb_quality_template_fixed_details',
                'sb_quality_templates',
                'sb_qualiyt_preset_template',
                'sb_patrol_points',
                'sb_repair_setting',
                'sb_sms_basesetting',
                'sb_approve_type',
                'sb_module',
            );
            foreach ($ts as $k => $v) {
                if (!in_array($v, $not_t)) {
                    $sql = 'TRUNCATE ' . $v;
                    M()->execute($sql);
                }
            }
            $time = date('Y-m-d H:i:s');
            $sql = "INSERT INTO sb_user(job_hospitalid,manager_hospitalid,usernum,username,password,telephone,status,is_super,set_password_time) 
                values(1,1,1,'" . $username . "','" . $password . "','13800138000',1,1,'".$time."')";
            M()->execute($sql);
        }
    }

    public function uploadData()
    {
        if (empty($_FILES)) {
            return array('status' => -1, 'msg' => '请上传文件');
        }
        $uploadConfig = array(
            'maxSize' => 3145728,
            'rootPath' => './Public/',
            'savePath' => 'uploads/',
            'saveName' => array('uniqid', ''),
            'exts' => array('xlsx', 'xls', 'xlsm'),
            'autoSub' => true,
            'subName' => array('date', 'Ymd'),
        );
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        $upload = new \Think\Upload($uploadConfig);
        $info = $upload->upload();
        if (!$info) {
            return array('status' => -1, 'msg' => '导入数据出错');
        }
        vendor("PHPExcel.PHPExcel");
        $filePath = $upload->rootPath . $info['file']['savepath'] . $info['file']['savename'];
        if (empty($filePath) or !file_exists($filePath)) {
            die('file not exists');
        }

        $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                return array('status' => -1, 'msg' => '文件格式错误');
            }
        }
        $excelDate = new \PHPExcel_Shared_Date();
        $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
        $currentSheet = $PHPExcel->getSheet(0);        //**读取excel文件中的指定工作表*/
        $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
        ++$allColumn;
        $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
        $data = array();
        $cellname = array(
            'A' => 'username',
            'B' => 'password',
            'C' => 'belongDepartment',
            'D' => 'department',
            'E' => 'character',
            'F' => 'telephone',
            'G' => 'expire_time',
            'H' => 'gender'
        );
        $username_arr = array();
        $user_data = $this->DB_get_all('user', 'username,telephone,is_delete');
        $hospital_id = session('current_hospitalid');
        $departments = $this->getAllDepartments(array('hospital_id' => $hospital_id, 'is_delete' => C('NO_STATUS')));
        $where['hospital_id'] = array('EQ', $hospital_id);
        $where['is_default'] = array('EQ', C('NO_STATUS'));
        $where['is_delete'] = array('EQ', C('NO_STATUS'));
        $where['status'] = array('NEQ', C('NO_STATUS'));
        $roles = $this->getAllRoles($where);
        $del_user = "";
        $del_phone = array();
        foreach ($user_data as $key => $value) {
            if ($value['is_delete'] == '1') {
                $del_user .= $value['username'] . ",";
                $del_phone[$value['username']] = $value['telephone'];
            } else {
                $username_arr[] = $value['username'];
                $phone_arr[] = $value['telephone'];
                $phone_arr[] = $SM4->encrypt($value['telephone']);
            }
        }
        $del_user = rtrim($del_user, ",");
        $log_data = $this->DB_get_all('operation_log', 'username', array('username' => array('IN', $del_user)), 'username');
        //被删除并已存在的用户名
        $del_being_user = array();
        foreach ($log_data as $key => $value) {
            $del_being_user[] = $value['username'];
            $del_being_phone[] = $del_phone[$value['username']];
        }
        $being = false;
        for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {
            $error_msg = array();
            $being = false;
            for ($colIndex = 'A'; $colIndex != $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if ($being) {
                    continue;
                }
                if ($cellname[$colIndex] == 'username') {
                    if (!$cell) {
                        $error_msg[] = array('status' => -1, 'msg' => '导入数据失败，用户名不能为空');
                    } else {
                        if (in_array($cell, $del_being_user)) {
                            $user_msg[] = $cell;
                            $being = true;
                            continue;
                        } elseif (in_array($cell, $username_arr)) {
                            return array('status' => -1, 'msg' => '导入数据失败，用户名【' . $cell . '】已存在');
                        } else {
                            $username_arr[] = $cell;
                        }
                    }

                }
                if ($cellname[$colIndex] == 'password') {
                    if (!$cell) {
                        $error_msg[] = array('status' => -1, 'msg' => '导入数据失败，密码不能为空');
                    } else {
                        if (!preg_match("/^(?![a-z]+$)(?![A-Z]+$)(?![0-9]+$)(?![\W_]+$)[a-zA-Z0-9\W_]{8,18}$/", $cell)) {
                            $error_msg[] = array('status' => -1, 'msg' => '导入数据失败，密码复杂度不足，输入密码长度必须为8到18位，且大写字母 小写字母 数字 特殊字符，四种包括两种，且不能出现空格');
                        } else {
                            $cell = $SM4->encrypt($cell);
                        }
                        //$cell = password_hash($cell, PASSWORD_DEFAULT);
                    }
                }
                if ($cellname[$colIndex] == 'belongDepartment') {
                    if (!$cell) {
                        $error_msg[] = array('status' => -1, 'msg' => '导入数据失败，工作科室不能为空');
                    } else {
                        $i = 0;
                        foreach ($departments as $key => $value) {
                            if ($value['department'] == $cell) {
                                $cell = $value['departid'];
                                break;
                            } else {
                                $i++;
                            }
                            if ($i == count($departments)) {
                                return array('status' => -1, 'msg' => '导入数据失败，工作科室【' . $cell . '】不存在');
                            }
                        }
                        $value = null;
                    }

                }
                if ($cellname[$colIndex] == 'department') {
                    if (!$cell) {
                        $departid = "";
                        $error_msg[] = array('status' => -1, 'msg' => '导入数据失败，管理科室不能为空');
                    } else {
                        if (strstr($cell, '，')) {
                            $departid_arr = explode("，", $cell);
                        } elseif ((strstr($cell, '.'))) {
                            $departid_arr = explode(".", $cell);
                        } elseif ((strstr($cell, '、'))) {
                            $departid_arr = explode("、", $cell);
                        } elseif ((strstr($cell, '|'))) {
                            $departid_arr = explode("|", $cell);
                        } else {
                            $departid_arr = explode(",", $cell);
                        }

                        $departid = "";
                        foreach ($departid_arr as $k => $v) {
                            $i = 0;
                            foreach ($departments as $key => $value) {
                                if ($value['department'] == $v) {
                                    $departid .= $value['departid'] . ',';
                                    break;
                                } else {
                                    $i++;
                                }
                                if ($i == count($departments)) {
                                    return array('status' => -1, 'msg' => '导入数据失败，科室【' . $v . '】不存在');
                                }
                            }
                        }
                    }
                    $cell = rtrim($departid, ",");
                }
                if ($cellname[$colIndex] == 'character') {
                    if (!$cell) {
                        $error_msg[] = array('status' => -1, 'msg' => '导入数据失败，用户角色不能为空');
                    }
                    if (strstr($cell, '，')) {
                        $role_arr = explode("，", $cell);
                    } elseif ((strstr($cell, '.'))) {
                        $role_arr = explode(".", $cell);
                    } elseif ((strstr($cell, '、'))) {
                        $role_arr = explode("、", $cell);
                    } elseif ((strstr($cell, '|'))) {
                        $role_arr = explode("|", $cell);
                    } else {
                        $role_arr = explode(",", $cell);
                    }
                    $roleid = "";
                    foreach ($role_arr as $k => $v) {
                        $i = 0;
                        foreach ($roles as $key => $value) {
                            if ($value['role'] == $v) {
                                $roleid .= $value['roleid'] . ',';
                                break;
                            } else {
                                $i++;
                            }
                            if ($i == count($roles) && (count($error_msg) != 4 && $v != "")) {
                                return array('status' => -1, 'msg' => '导入数据失败，角色【' . $v . '】不存在');
                            }
                        }
                    }
                    $cell = rtrim($roleid, ",");
                }
                if ($cellname[$colIndex] == 'telephone') {
                    if (!$cell) {
                        continue;
                    } else {
                        if (in_array($cell, $phone_arr)) {
                            return array('status' => -1, 'msg' => '导入数据失败，手机号【' . $cell . '】已存在');
                        } else {
                            $phone_arr[] = $SM4->encrypt($cell);
                        }
                    }
                }
                if ($cellname[$colIndex] == 'expire_time') {
                    if (!$cell) {
                        continue;
                    } else {
                        $d = $currentSheet->getCell($addr)->getValue();
                        $cell = '';
                        if (strpos($d, '/') !== false) {
                            $dt = explode('/', $d);
                            if ($dt[1] < 10) {
                                $dt[1] = '0' . (int)$dt[1];
                            }
                            if ($dt[2] < 10) {
                                $dt[2] = '0' . (int)$dt[2];
                            }
                            $cell = implode('-', $dt);
                        } elseif (strlen($d) == 8) {
                            $cell = date('Y-m-d', strtotime($d));
                        } else {
                            $cell = gmdate("Y-m-d", $excelDate::ExcelToPHP($d));
                        }
                    }
                }
                if ($cellname[$colIndex] == 'gender') {
                    if (!$cell) {
                        $cell = '1';
                    } else {
                        if ($cell == '男') {
                            $cell = '0';
                        } else {
                            $cell = '1';
                        }
                    }
                }
                $data[$rowIndex - 2][$cellname[$colIndex]] = trim($cell) ? trim($cell) : '';
            }
            if ($error_msg && $allColumn == $colIndex && count($error_msg) < 5) {
                return $error_msg[0];
            }
            if (count($error_msg) == 5) {
                array_pop($data);
            }
        }
        if (!$data) {
            return array('status' => -1, 'msg' => '导入数据失败');
        }
        $del_data = "";//需要删除掉的数据
        $del_arr = explode(",", $del_user);
        foreach ($data as $key => $value) {
            if (in_array($value['username'], $del_arr)) {
                $del_data .= $value['username'] . ",";
            }
        }
        $del_data = rtrim($del_data, ",");
        $this->deleteData('user', array('username' => array('IN', $del_data)));
        if ($data[count($data) - 1]['username'] == "") {
            unset($data[count($data) - 1]);
        }
        $insertData = array();
        $num = 0;
        foreach ($data as $k => $v) {
            if (!isset($v['telephone'])) {
                $v['telephone'] = "";
            }
            $insertData = array(
                'job_hospitalid' => $hospital_id,
                'job_departid' => $v['belongDepartment'],
                'password' => $v['password'],
                'username' => $v['username'],
                'status' => '1',
                'gender' => $v['gender'],
                'telephone' => $v['telephone'],
                'expire_time' => $v['expire_time']
            );
            $insertData['add_time'] = date('Y-m-d H:i:s');
            $insertData['set_password_time'] = date('Y-m-d H:i:s');
            $result = $this->insertData('user', $insertData);
            $departid = explode(",", $v['department']);
            $roleid = explode(",", $v['character']);
            if ($result) {
                $department = array();
                for ($i = 0; $i < count($departid); $i++) {
                    $department[$i]['userid'] = $result;
                    $department[$i]['departid'] = $departid[$i];
                }
                $role = array();
                for ($i = 0; $i < count($roleid); $i++) {
                    $role[$i]['userid'] = $result;
                    $role[$i]['roleid'] = $roleid[$i];
                }
                $this->insertDataALL('user_role', $role);
                $this->insertDataALL('user_department', $department);
            }
        }
        if ($user_msg) {
            return array('status' => 2, 'msg' => '用户' . implode(",", $user_msg) . '已存在请单独添加决定是否恢复,其他用户已经上传成功');
        }
        return array('status' => 1, 'msg' => '批量添加用户成功！', 'data' => $insertData);

    }

    public function chuna($old_name, $new_name)
    {
        $userModel = new UserModel();
        $ole_user = $userModel->DB_get_one('user', 'username', array('username' => $old_name));
        if (!$ole_user) {
            return array('status' => -1, 'msg' => '找不到【' . $old_name . '】的用户信息');
        }
        $new_user = $userModel->DB_get_one('user', 'username', array('username' => $new_name));
        if ($new_user) {
            return array('status' => -1, 'msg' => '用户【' . $new_name . '】已存在或已删除，暂不允许进行修改');
        }
        $model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $ts = $this->db->getTables();
        //需要判断的字段名称
        $filed_name = [
            'adduser', 'edituser', 'proposer', 'approver', 'approve_user_aux', 'add_user', 'edit_user', 'lasttestuser', 'pre_patrol_executor',
            'pre_maintain_executor', 'archives_manager', 'hospital_manager', 'update_user', 'apply_user', 'clear_cross_user', 'change_user',
            'applicant_user', 'check_user', 'patroluser', 'manager', 'assetsrespon', 'departrespon', 'approval_user', 'back_user', 'username',
            'confirm_user', 'leader', 'exam_user', 'examine_username', 'applicant', 'check_user', 'release_user', 'debug_user', 'train_user',
            'expert_name', 'in_user', 'approve_user', 'handle_user', 'out_user', 'review_user', 'submit_user', 'assign', 'response', 'engineer',
            'assist_engineer', 'checkperson', 'offer_user', 'decision_user', 'start_username'
        ];
        $app_user = [
            'current_approver', 'complete_approver', 'not_complete_approver', 'all_approver', 'executor', 'attendants_user', 'attendants_user'
        ];
        foreach ($ts as $k => $v) {
            $sql = "select COLUMN_NAME from information_schema.COLUMNS where table_name = '" . $v . "' and table_schema = '" . C('DB_NAME') . "'";
            $fileds = $model->query($sql);
            $table_fileds = [];
            foreach ($fileds as $fk => $fv) {
                $table_fileds[] = $fv['COLUMN_NAME'];
            }
            $table_name = str_replace(C('DB_PREFIX'), '', $v);
            foreach ($filed_name as $ev) {
                $data = $where = [];
                if (in_array($ev, $table_fileds)) {
                    $data[$ev] = $new_name;
                    $where[$ev] = $old_name;
                    $userModel->updateData($table_name, $data, $where);
                }
            }
            foreach ($app_user as $apv) {
                if (in_array($apv, $table_fileds)) {
                    $update_sql = "update " . $v . " set " . $apv . " = replace(" . $apv . ",'" . $old_name . "','" . $new_name . "')";
                    $model->execute($update_sql);
                }
            }
        }
        return array('status' => 1, 'msg' => '修改用户名成功！');
    }

    /**
     * 登陆验证
     */
    public function loginVerify($username, $pw)
    {
        $privkey = file_get_contents('Public/key/rsa_1024_priv.pem');
        $pi_key = openssl_pkey_get_private($privkey);
        if ($pi_key === false) {
            return array('status' => -1, 'msg' => '登录错误，请联系管理员');
        }
        openssl_private_decrypt(base64_decode($pw), $password, $privkey);

        //登录限制
        $keys = 'login_' . $username;
        $times = $this->getLoginTimes($keys);
        if ($times == 5) {
            #return array('status' => -1, 'msg' => '登录失败次数过多，请15分钟后再试。');
        }
        $where = array();
        $where['username'] = trim($username);
        $where['status'] = C('YES_STATUS');
        $where['is_delete'] = C('NO_STATUS');
        $user = $this->DB_get_one('user', '*', $where);
        if (!$user) {
            $this->setLoginTimesInc($keys);//次数加1
            return array('status' => -1, 'msg' => C('_LOGIN_USER_NOT_EXISTS_MSG_'));
        }
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        if (password_verify($password, $user['password'])) {
            $this->updateData('user', ['password' => $SM4->encrypt($password)], array('userid' => $user['userid']));
            $user['password'] = $SM4->encrypt($password);
        }
        if ($user['password'] != $SM4->encrypt($password)) {
            $this->setLoginTimesInc($keys);//次数加1
            return array('status' => -1, 'msg' => C('_LOGIN_USER_ERROR_MSG_'), 'password' => $password);
        }
        //判断账户是否已过期
        if ($user['expire_time'] && time() >= strtotime($user['expire_time'])) {
            return array('status' => -1, 'msg' => '您的账号已过期，如需继续使用，请联系管理员设置!');
        }
        //通过通用验证返回user数据和keys值
        return array('status' => 1, 'user' => $user, 'keys' => $keys, 'password' => $password);
    }


    /**
     * 获取登录次数
     * @param $keys string 键值
     * @return int|mixed
     */
    public function getLoginTimes($keys)
    {
        if (S($keys)) {
            $info = S($keys);
            return $info['times'];
        } else {
            return 0;
        }
    }

    /**
     * 错误登录次数加1
     * @param $keys string 键值
     */
    public function setLoginTimesInc($keys)
    {
        //15分钟
        $expireTime = 900;
        if (S($keys)) {
            $info = S($keys);
            if ($info['times'] < 5) {
                S($keys, ['times' => $info['times'] + 1], ['expire' => $expireTime]);
            }
        } else {
            S($keys, ['times' => 1], ['expire' => $expireTime]);
        }
    }

    /** 清除登录次数
     * @param $keys string 键值
     */
    public function clearLoginTimes($keys)
    {
        if (S($keys)) {
            S($keys, null);
        }
    }
}
