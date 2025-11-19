<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/17
 * Time: 10:38
 */

namespace Admin\Controller\BaseSetting;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\UserModel;

class UserController extends CheckLoginController
{
    private $MODULE = 'BaseSetting';


    /*
     * 或取用户列表getList
     */
    public function getUserList()
    {
        $userModel = new UserModel();
        if (IS_POST) {
            $action = I('POST.action');
            if ($action == 'managerhos') {
                //分配用户能管理的分院
                $update['manager_hospitalid'] = I('post.managerhospital');
                $update['edit_time']          = date('Y-m-d H:i:s');
                $where['userid']              = I('post.userid');
                $res                          = $userModel->updateData('user', $update, $where);
                if ($res) {
                    $this->ajaxReturn(['status' => 1, 'msg' => '修改管理医院成功！']);
                } else {
                    $this->ajaxReturn(['status' => -1, 'msg' => '修改管理医院失败！']);
                }
            } else {
                //根据搜索条件获取用户列表
                $result = $userModel->getUserLists();
                echo json_encode($result, JSON_PARTIAL_OUTPUT_ON_ERROR);
            }
        } else {
            $action = I('GET.action');
            if ($action == 'showPrivi') {
                $this->showPrivi();
            } else {
                $where['is_delete']   = C('NO_STATUS');
                $where['hospital_id'] = session('current_hospitalid');
                $where['departid']    = ['IN', session('departid')];
                $department           = $userModel->DB_get_all('department', 'departid,department', $where, '',
                    'departid asc', '');
                $this->assign('department', $department);
                $roleInfo = $userModel->DB_get_all('role', 'roleid,role', ['is_delete' => 0]);
                $this->assign('roleInfo', $roleInfo);
                $this->assign('getUserList', get_url());
                $this->assign('is_super', session('isSuper'));
                $this->display();
            }
        }
    }

    /*
     * 新增用户
     */
    public function addUser()
    {
        if (IS_POST) {
            $userModel = new UserModel();
            $action    = I('POST.action');
            if ($action == 'restore') {
                $res = $userModel->editUser();
            } else {
                $res = $userModel->addUser();
            }

            $this->ajaxReturn($res);
        } else {
            $userModel = new UserModel();
            switch (I('get.type')) {
                case 'getManager':
                    $departid = I('get.departid');
                    //获取工作科室负责人 用于联动
                    $manager = $userModel->DB_get_one('department', 'manager', ['departid' => $departid]);
                    $this->ajaxReturn($manager['manager'], 'json');
                    break;
                case 'getHospitalDepartment':
                    $hospitalId = I('get.hospital_id');
                    //获取工作科室负责人 用于联动
                    $departments = $userModel->getAllDepartments(['hospital_id' => $hospitalId]);
                    foreach ($departments as $k => $v) {
                        $departments[$k]['name']  = $v['department'];
                        $departments[$k]['value'] = $v['departid'];
                        unset($departments[$k]['departid']);
                        unset($departments[$k]['department']);
                    }
                    $result['code'] = 0;
                    $result['msg']  = 'success';
                    $result['data'] = $departments;
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $hospital_id = session('current_hospitalid');
                    //获取所有角色
                    $where['hospital_id'] = ['EQ', $hospital_id];
                    $where['is_default']  = ['EQ', C('NO_STATUS')];
                    $where['is_delete']   = ['EQ', C('NO_STATUS')];
                    $where['status']      = ['NEQ', C('NO_STATUS')];
                    $roles                = $userModel->getAllRoles($where);
                    //获取所有部门
                    $departments = $userModel->getAllDepartments([
                        'hospital_id' => $hospital_id,
                        'is_delete'   => C('NO_STATUS'),
                    ]);
                    $this->assign('is_super', session('isSuper'));
                    $this->assign('today', date('Y-m-d'));
                    $this->assign('character', $roles);
                    $this->assign('department', $departments);
                    if (C('OPEN_SUPPLIER_USER')) {
                        //开启 厂商用户
                        $where['is_supplier'] = C('YES_STATUS');
                        $where['is_delete']   = C('NO_STATUS');
                        $where['status']      = C('OPEN_STATUS');
                        $suppliers            = $userModel->DB_get_all('offline_suppliers', 'olsid,sup_name', $where);
                        $this->assign('suppliers', $suppliers);
                    }
                    $this->display();
            }
        }
    }

    /*
     * 修改用户
     */
    public function editUser()
    {
        if (IS_POST) {
            $userModel = new UserModel();
            //修改用户信息
            $res = $userModel->editUser();
            $this->ajaxReturn($res);
        } else {
            $userModel = new UserModel();
            switch (I('get.type')) {
                case 'getManager':
                    $departid = I('get.departid');
                    //获取工作科室负责人 用于联动
                    $manager = $userModel->DB_get_one('department', 'manager', ['departid' => $departid]);
                    $this->ajaxReturn($manager['manager'], 'json');
                    break;
                default:
                    $userId = I('get.userid');
                    //获取用户信息
                    $userInfo             = $userModel->getUserInfo($userId);
                    $userInfo['departid'] = explode(',', $userInfo['departid']);
                    $userInfo['roleid']   = explode(',', $userInfo['roleid']);
                    $userInfo['validity'] = $userInfo['expire_time'] ? date('Y-m-d',
                        strtotime($userInfo['expire_time'])) : '';
                    $managerDepartments   = $managerRoles = $hospitalNameArr = $hos_id = [];
                    if (session('isSuper') && C('IS_OPEN_BRANCH')) {
                        //如果是超级管理员,获取所有医院的名称
                        $hospitalName = $userModel->DB_get_all('hospital', 'hospital_id,hospital_name',
                            ['is_delete' => 0]);
                        foreach ($hospitalName as $v) {
                            $hospitalNameArr[$v['hospital_id']] = $v['hospital_name'];
                            $hos_id[]                           = $v['hospital_id'];
                        }
                        $this->assign('show_role_tips', 1);
                    } else {
                        $hospitalName = $userModel->DB_get_all('hospital', 'hospital_id,hospital_name',
                            ['is_delete' => 0, 'hospital_id' => session('current_hospitalid')]);
                        foreach ($hospitalName as $v) {
                            $hospitalNameArr[$v['hospital_id']] = $v['hospital_name'];
                            $hos_id[]                           = $v['hospital_id'];
                        }
                    }
                    if ($userInfo['is_supplier'] == C('YES_STATUS')) {
                        //厂商用户
                        $where['is_supplier'] = C('YES_STATUS');
                        $where['is_delete']   = C('NO_STATUS');
                        $where['status']      = C('OPEN_STATUS');
                        $suppliers            = $userModel->DB_get_all('offline_suppliers', 'olsid,sup_name', $where);
                        $this->assign('suppliers', $suppliers);
                        $userInfo['user_type'] = '厂商用户';
                    } else {
                        //普通用户
                        //获取所有角色
                        $where['hospital_id'] = ['in', $hos_id];
                        $where['is_default']  = ['EQ', C('NO_STATUS')];
                        $where['is_delete']   = ['EQ', C('NO_STATUS')];
                        $where['status']      = ['NEQ', C('NO_STATUS')];
                        $roles                = $userModel->getAllRoles($where);
                        //获取用户所在医院的部门
                        $jobDeparts = $userModel->getAllDepartments(['hospital_id' => session('current_hospitalid')]);
                        //获取用户可管理医院的部门
                        $managerDeparts = $userModel->getAllDepartments(['hospital_id' => ['in', $hos_id]]);
                        foreach ($managerDeparts as $v) {
                            $managerDepartments[$v['hospital_id']]['hospital_name'] = $hospitalNameArr[$v['hospital_id']];
                            $managerDepartments[$v['hospital_id']]['list'][]        = $v;
                        }
                        foreach ($roles as $k => $v) {
                            $managerRoles[$v['hospital_id']]['hospital_name'] = $hospitalNameArr[$v['hospital_id']];
                            $managerRoles[$v['hospital_id']]['list'][]        = $v;
                        }
                        $departid = $userInfo['job_departid'];
                        //获取当前工作科室负责人
                        $manager = $userModel->DB_get_one('department', 'manager', ['departid' => $departid]);
                        $this->assign('manager', $manager['manager']);
                        $this->assign('role', $managerRoles);
                        $this->assign('jobDeparts', $jobDeparts);
                        $this->assign('managerDeparts', $managerDepartments);
                        $userInfo['user_type'] = '医院用户';
                    }
                    $this->assign('is_super', session('isSuper'));
                    $this->assign('today', date('Y-m-d'));
                    $this->assign('userInfo', $userInfo);
                    $this->display();
            }
        }
    }

    /*
     * 删除用户
     */
    public function deleteUser()
    {
        if (IS_POST) {
            $usModel  = new UserModel();
            $id       = I('POST.userid');
            $userInfo = $usModel->DB_get_one('user', 'username', ['userid' => $id]);
            if (!$userInfo) {
                $this->ajaxReturn(['status' => 1, 'msg' => C('_DELETE_USER_SUCCESS_SUCC_MSG_')], 'json');
            }
            //$result = $usModel->deleteData('user',array('userid'=>$id));
            $log = $usModel->DB_get_one('operation_log', 'username', ['username' => $userInfo['username']]);
            if ($log) {
                $result = $usModel->updateData('user', ['is_delete' => 1, 'edit_time' => date('Y-m-d H:i:s')],
                    ['userid' => $id]);
            } else {
                $result = $usModel->deleteData('user', ['userid' => $id]);
            }
            //日志行为记录文字
            $log['user'] = $userInfo['username'];
            $text        = getLogText('deleteUserLogText', $log);
            $usModel->addLog('user', M()->getLastSql(), $text, $id, '');
            //删除该用户的系统科室负责人设置
            $usModel->updateData('department', ['manager' => ''], ['manager' => $userInfo['username']]);
            if ($result !== false) {
                $this->ajaxReturn(['status' => 1, 'msg' => C('_DELETE_USER_SUCCESS_SUCC_MSG_')], 'json');
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => C('_DELETE_USER_SUCCESS_FAIL_MSG_')], 'json');
            }
        }
    }

    /*
     * 批量删除用户
     */
    public function batchDeleteUser()
    {
        if (IS_POST) {
            $usModel = new UserModel();
            $id      = explode(',', I('POST.userid'));
            if (!$id) {
                $this->ajaxReturn(['status' => 1, 'msg' => C('_DELETE_USER_SUCCESS_SUCC_MSG_')], 'json');
            }
            //查询用户名
            $userInfo = $usModel->DB_get_all('user', 'userid,username,is_super', ['userid' => ['in', $id]]);
            if (!$userInfo) {
                $this->ajaxReturn(['status' => 1, 'msg' => C('_DELETE_USER_SUCCESS_SUCC_MSG_')], 'json');
            }
            foreach ($userInfo as $k => $v) {
                if ($v['is_super'] == 1) {
                    unset($userInfo[$k]);
                    foreach ($id as $kk => $vv) {
                        if ($vv == $v['userid']) {
                            unset($id[$kk]);
                        }
                    }
                }
            }
            if (!$id) {
                $this->ajaxReturn(['status' => -1, 'msg' => '超级管理员不允许删除'], 'json');
            }
            $usernameArr = [];
            foreach ($userInfo as $k => $v) {
                $usernameArr[] = $v['username'];
            }
            $deleteUsername = $usModel->DB_get_one('user', 'group_concat(username) AS username',
                ['userid' => ['in', $id]]);
            $result         = $usModel->updateData('user', ['is_delete' => 1, 'edit_time' => date('Y-m-d H:i:s')],
                ['userid' => ['in', $id]]);
            //$result = $usModel->deleteData('user',array('userid'=>array('in',$id)));
            //日志行为记录文字
            $log['user'] = $deleteUsername['username'];
            $text        = getLogText('batchDeleteUserLogText', $log);
            $usModel->addLog('user', M()->getLastSql(), $text, '', '');
            //$result = $usModel->deleteData('user_role',array('userid'=>array('in',$id)));
            //$result = $usModel->deleteData('user_department',array('userid'=>array('in',$id)));
            //删除该用户的系统科室负责人设置
            $usModel->updateData('department', ['manager' => ''], ['manager' => ['in', $usernameArr]]);
            if ($result !== false) {
                $this->ajaxReturn(['status' => 1, 'msg' => C('_DELETE_USER_SUCCESS_SUCC_MSG_')], 'json');
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => C('_DELETE_USER_SUCCESS_FAIL_MSG_')], 'json');
            }
        }
    }

    /*
    批量添加用户
     */
    public function batchAddUser()
    {
        $usModel = new UserModel();
        $result  = $usModel->uploadData();
        $this->ajaxReturn($result);
        return;
    }

    /*
     * 清除用户微信绑定
     */
    public function clearOpenid()
    {
        $usModel            = new UserModel();
        $userid             = I('post.userid');
        $userInfo           = $usModel->DB_get_one('user', 'username', ['userid' => $userid]);
        $data['openid']     = '';
        $data['qy_user_id'] = '';
        $data['nickname']   = '';
        $User               = $usModel->updateData('user', $data, ['userid' => $userid]);
        //日志行为记录文字
        $log['user'] = $userInfo['username'];
        $text        = getLogText('clearOpenIDLogText', $log);
        $usModel->addLog('user', M()->getLastSql(), $text, $userid, '');
        if ($User) {
            $this->ajaxReturn(['status' => 1, 'msg' => '微信与企业微信解绑成功！'], 'json');
        } else {
            $this->ajaxReturn(['status' => -1, 'msg' => '微信与企业微信解绑失败！'], 'json');
        }
    }

    public function clearLoginTimes()
    {
        $userModel = new UserModel();
        $username  = I('post.username');
        $keys      = 'login_' . $username;
        $userModel->clearLoginTimes($keys);
        $this->ajaxReturn(['status' => 1, 'msg' => '解冻成功！'], 'json');
    }

    private function showPrivi()
    {
        $userModel = new UserModel();
        $result    = $userModel->getUserPrivi();
        if ($result['status'] == -1) {
            $this->assign('errmsg', $result['msg']);
            $this->display('/Public/error');
            exit;
        } else {
            $this->assign('hospitalname', $result['hospitalname']);
            $this->assign('result', $result);
            $this->display('showPrivi');
        }
    }

    /*
    跳转到截图页面
     */
    public function uploadautograph()
    {
        $this->display();
    }

    /**
     * 用户资料
     */
    public function userInfo()
    {
        if (IS_POST) {
            $userModel = new UserModel();
            $userid    = session('userid');
            $saveType  = I('POST.saveType');
            Vendor('SM4.SM4');
            $SM4 = new \SM4();
            if ($saveType == 'userinfo') {
                $data['gender']    = I('POST.gender');
                $data['telephone'] = $SM4->encrypt(trim(I('POST.telephone')));
                $telephone         = trim(I('POST.telephone'));
                //查询该手机号码是否已存在
                $tel = $userModel->DB_get_one('user', 'telephone', [
                    'telephone' => [['eq', $data['telephone']], ['eq', $telephone] . 'or'],
                    'userid'    => ['neq', $userid],
                ]);
                if ($tel) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该手机号码已存在！']);
                }
                $data['email']  = trim(I('POST.email'));
                $data['remark'] = trim(I('POST.remark'));
            } elseif ($saveType == 'password') {
                $oldpassword = trim(I('POST.oldpassword'));
                //验证密码是否正确
                $upw = $userModel->DB_get_one('user', 'password', ['userid' => $userid]);
                if ($upw['password'] != $SM4->encrypt($oldpassword)) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '密码错误！']);
                }
                $newpassword  = trim(I('POST.newpassword'));
                $newpassword2 = trim(I('POST.newpassword2'));
                if ($newpassword != $newpassword2) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '密码密码和确认密码不一致！']);
                }
                $data['password']          = $SM4->encrypt($newpassword);
                $data['set_password_time'] = date('Y-m-d H:i:s');
                //$data['password'] = password_hash($newpassword, PASSWORD_DEFAULT);
            } else {
                $data = [];
            }
            $data['edit_time'] = getHandleDate(time());
            $res               = $userModel->updateData('user', $data, ['userid' => $userid]);
            if ($res) {
                session('password', null);
                $this->ajaxReturn(['status' => 1, 'msg' => '修改成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '修改失败']);
            }
        } else {
            $userid = I('GET.userid');
            if (!$userid) {
                $userid = session('userid');
            }
            $asModel = new UserModel();
            //查询用户基本信息
            $userInfo = $asModel->getUserInfo($userid);
            if (!$userInfo['userid']) {
                $this->assign('errmsg', '查找不到用户信息！');
                $this->display('/Public/error');
                exit;
            }
            $isSuper = session('isSuper');
            $urole   = [];
            if (!$isSuper) {
                $udepart = explode(',', $userInfo['departid']);
                $urole   = explode(',', $userInfo['roleid']);
            } else {
                //超级管理员
                $udepart = explode(',', session('departid'));
                $roles   = $asModel->DB_get_all('role', '', ['status' => 1]);
                if (!$roles) {
                    $this->assign('errmsg', '请先设置角色！');
                    $this->display('/Public/error');
                    exit;
                }
                foreach ($roles as $k => $v) {
                    $urole[] = $v['roleid'];
                }
            }
            //查询所有科室
            $alldeparts                 = $asModel->DB_get_all('department', 'hospital_id,departid,department',
                ['is_delete' => 0]);
            $userInfo['job_department'] = '';
            foreach ($alldeparts as $k => $v) {
                if ($v['departid'] == $userInfo['job_departid']) {
                    $userInfo['job_department'] = $v['department'];
                }
                if (!in_array($v['departid'], $udepart)) {
                    unset($alldeparts[$k]);
                }
            }
            //查询所有医院
            $hospitals    = $asModel->get_all_hospital();
            $hospitalname = $hospital_depart = $hospital_role = $privi_role = $my_privi = [];
            foreach ($hospitals as $k => $v) {
                $hospitalname[$v['hospital_id']]['hospital_name'] = $v['hospital_name'];
            }
            foreach ($alldeparts as $k => $v) {
                $hospital_depart[$v['hospital_id']][] = $v['department'];
            }
            ksort($hospital_depart);
            //查询所有角色
            $allroles = $asModel->DB_get_all('role', 'hospital_id,roleid,role', ['status' => 1]);
            foreach ($allroles as $k => $v) {
                if (!in_array($v['roleid'], $urole)) {
                    unset($allroles[$k]);
                }
            }
            foreach ($allroles as $k => $v) {
                $hospital_role[$v['hospital_id']][] = $v['role'];
            }
            ksort($hospital_role);
            $hos_role = $asModel->DB_get_all('role', 'hospital_id,roleid', ['roleid' => ['in', $urole]]);
            foreach ($hos_role as $k => $v) {
                $privi_role[$v['hospital_id']][] = $v['roleid'];
            }
            foreach ($privi_role as $k => $v) {
                $my_privi[$k] = $asModel->get_privi($v);
            }
            $this->assign('alldeparts', $hospital_depart);
            $this->assign('hospitalname', $hospitalname);
            $this->assign('allroles', $hospital_role);
            $this->assign('res', $my_privi);
            $this->assign('isSuper', $isSuper);
            $this->assign('userInfo', $userInfo);
            $this->assign('password', session('password'));
            $this->assign('userInfoUrl', get_url());
            $this->display();
        }
    }

    /**
     * Notes: 修改用户名称
     */
    public function chuna()
    {
        $old_name = trim(I('post.old_name'));
        $new_name = trim(I('post.new_name'));
        if (!$old_name || !$new_name) {
            $this->ajaxReturn(['status' => -1, 'msg' => '用户名不能为空！']);
        } elseif ($old_name == $new_name) {
            $this->ajaxReturn(['status' => -1, 'msg' => '请输入新的用户名！']);
        } else {
            $userModel = new UserModel();
            $result    = $userModel->chuna($old_name, $new_name);
            $this->ajaxReturn($result);
        }
    }

    /*
     * 或取用户轨迹getList
     */
    public function userTra()
    {
        $userModel = new UserModel();
        if (IS_POST) {
            $limit     = I('post.limit') ? I('post.limit') : 10;
            $page      = I('post.page') ? I('post.page') : 1;
            $offset    = ($page - 1) * $limit;
            $username  = I('post.username');
            $scan_date = I('post.scan_date');
            $where     = '';
            if ($username) {
                //名字搜索
                $where .= " where B.username = '" . $username . "'";
            }
            if ($scan_date) {
                $where .= " where A.scan_date = '" . $scan_date . "'";
            }
            //$join = 'LEFT JOIN sb_user AS B ON B.userid = A.scan_userid LEFT JOIN sb_department AS C ON A.depart_id = C.departid';
            //$total = $userModel->DB_get_count_join('user_trajectory', 'A', $join, $where);
            $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
            $sql1  = "SELECT * FROM (SELECT A.tra_id,A.scan_userid,A.scan_date,B.username,GROUP_CONCAT(A.depart_id) AS depart_id,GROUP_CONCAT(A.scan_time) AS scan_time,GROUP_CONCAT(C.department) AS department FROM sb_user_trajectory AS A LEFT JOIN sb_user AS B ON A.scan_userid = B.userid LEFT JOIN sb_department AS C ON A.depart_id = C.departid " . $where . " GROUP BY B.username,A.scan_date) AS aa ORDER BY aa.tra_id ASC";
            $sql2  = "SELECT * FROM (SELECT A.tra_id,A.scan_userid,A.scan_date,B.username,GROUP_CONCAT(A.depart_id) AS depart_id,GROUP_CONCAT(A.scan_time) AS scan_time,GROUP_CONCAT(C.department) AS department FROM sb_user_trajectory AS A LEFT JOIN sb_user AS B ON A.scan_userid = B.userid LEFT JOIN sb_department AS C ON A.depart_id = C.departid " . $where . " GROUP BY B.username,A.scan_date) AS aa ORDER BY aa.tra_id ASC limit " . $limit . " offset " . $offset;
            $total = $Model->query($sql1);
            $data  = $Model->query($sql2);
            foreach ($data as &$v) {
                $departs  = explode(',', $v['department']);
                $scantime = explode(',', $v['scan_time']);
                foreach ($scantime as &$dv) {
                    $dv = date('H:i', strtotime($dv));
                }
                $v['departs']    = $departs;
                $v['scantime']   = $scantime;
                $v['trajectory'] = '12';
            }
            $result['limit']  = (int)$limit;
            $result['offset'] = $offset;
            $result['total']  = count($total);
            $result['rows']   = $data;
            $result['code']   = 200;
            if (!$result['rows']) {
                $result['msg']  = '暂无相关数据';
                $result['code'] = 400;
            }
            $this->ajaxReturn($result, 'json');
        } else {
            $where['is_delete']   = C('NO_STATUS');
            $where['hospital_id'] = session('current_hospitalid');
            $where['departid']    = ['IN', session('departid')];
            $department           = $userModel->DB_get_all('department', 'departid,department', $where, '',
                'departid asc', '');
            $this->assign('department', $department);
            $this->assign('userTra', get_url());
            $this->display();
        }
    }
}
