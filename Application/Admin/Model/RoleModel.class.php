<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/3/27
 * Time: 16:19
 */

namespace Admin\Model;

use Think\Model;
use Think\Model\RelationModel;

class RoleModel extends CommonModel
{
    private $MODULE = 'BaseSetting';
    protected $tablePrefix = 'sb_';
    protected $tableName = 'role';


    public function getAssBreakDown($repid)
    {
        return $this->DB_get_one('repair', 'assid,breakdown', array('repid' => $repid));
    }

    public function checkRoleNameIsExist($rolename, $is_default)
    {
        $where['is_default'] = array('EQ', $is_default);
        $where['is_delete'] = array('EQ', 0);
        $where['role'] = array('EQ', $rolename);
        $where['hospital_id'] = array('EQ', session('current_hospitalid'));
        return $this->DB_get_one('role', 'role', $where);

    }

    //获取角色列表
    public function getRoleLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'ASC';
        $sort = I('post.sort') ? I('post.sort') : 'roleid';
        $role = I('POST.roleName');
        $hospital_id = session('current_hospitalid');
        $where['A.is_default'] = 0;
        $where['A.is_delete'] = 0;
        $whereNoJoin['is_default'] = 0;
        $whereNoJoin['is_delete'] = 0;
        $whereNoJoin['hospital_id'] = $hospital_id;
        if ($role) {
            //角色名称搜索
            $where['A.role'] = array('like',"%".$role."%");
            $whereNoJoin['role'] = array('like',"%".$role."%");
        }
        $total = $this->DB_get_count('role', $whereNoJoin);
        $data = $this->DB_get_all('role','*',$whereNoJoin,'',$sort . ' ' . $order, $offset . ',' . $limit);
        //查询各角色下的用户
        foreach ($data as $k=>$v){
            $join = "LEFT JOIN sb_user AS B ON A.userid = B.userid";
            $fields = "group_concat(B.username separator '、') as username";
            $role_users = $this->DB_get_one_join('user_role', 'A', $fields, $join, array('A.roleid' => $v['roleid'], 'B.job_hospitalid' => $hospital_id, 'B.is_delete' => 0));
            $data[$k]['users'] = $role_users['username'];
            $menus = $this->DB_get_one('role_menu','group_concat(menuid) as menuids',array('roleid'=>$v['roleid']));
            if($menus['menuids']){
                $menu_1 = $this->DB_get_one('menu','group_concat(distinct parentid) as parentids',array('menuid'=>array('in',$menus['menuids'])));
                $menu_2 = $this->DB_get_one('menu','group_concat(distinct parentid) as parentids',array('menuid'=>array('in',$menu_1['parentids'])));
                $module_name = $this->DB_get_one('menu',"group_concat(title separator '、') as module_name",array('menuid'=>array('in',$menu_2['parentids'])));
                $data[$k]['module_name'] = $module_name['module_name'];
            }else{
                $data[$k]['module_name'] = '';
            }
        }
        //查询当前用户是否有权限进行用户维护
        $editRoleUser = get_menu($this->MODULE, 'Privilege', 'editRoleUser');
        //查询当前用户是否有权限进行权限维护
        $editRolePrivi = get_menu($this->MODULE, 'Privilege', 'editRolePrivi');
        //查询当前用户是否有权限编辑角色
        $editRole = get_menu($this->MODULE, 'Privilege', 'editRole');
        //查询当前用户是否有权限删除角色
        $deleteRole = get_menu($this->MODULE, 'Privilege', 'deleteRole');
        foreach ($data as $k => $v) {
            $html = '<div class="layui-btn-group">';
            if ($editRoleUser) {
                $html .= '<button class="layui-btn layui-btn-xs" lay-event="editUser" id="editUser" href="javascript:void(0)" data-url="' . $editRoleUser['actionurl'] . '">成员</button>';
            }
            if ($editRolePrivi) {
                $html .= '<button class="layui-btn layui-btn-cus layui-btn-xs" lay-event="editTarget" id="editPri" href="javascript:void(0)" data-url="' . $editRolePrivi['actionurl'] . '">首页统计</button>';
                $html .= '<button class="layui-btn layui-btn-normal layui-btn-xs" lay-event="editPri" id="editPri" href="javascript:void(0)" data-url="' . $editRolePrivi['actionurl'] . '">权限</button>';
            }
            if ($editRole) {
                $html .= '<button class="layui-btn layui-btn-warm layui-btn-xs" lay-event="editRole" id="editRole" href="javascript:void(0)" data-url="' . $editRole['actionurl'] . '">修改</button>';
            }
            if ($deleteRole) {
                $html .= '<button class="layui-btn layui-btn-danger layui-btn-xs" lay-event="deleteRole" id="deleteRole" href="javascript:void(0)" data-url="' . $deleteRole['actionurl'] . '">删除</button>';
            }
            $html .= '</div>';
            $data[$k]['operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $data;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }


    public function getDefaultRoleList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'asc';
        $sort = I('post.sort') ? I('post.sort') : 'roleid';
        $role = I('POST.roleName');
        $where['is_default'] = array('EQ',C('YES_STATUS'));
        if ($role) {
            //角色名称搜索
            $where['role']=array('LIKE','%'.$role.'%');

        }
        $join[0] = ' LEFT JOIN __USER_ROLE__ ON A.roleid = __USER_ROLE__.roleid';
        $join[1] = ' LEFT JOIN __USER__ ON __USER_ROLE__.userid = __USER__.userid AND __USER__.status = 1';
        $join[2] = ' LEFT JOIN __ROLE_MENU__ ON A.roleid = __ROLE_MENU__.roleid';
        $total = $this->DB_get_count('role', $where);
        $fields = 'A.roleid,A.role as roleName,A.status,A.remark,A.adduser,A.edituser,group_concat(distinct sb_user.username separator "、") as users,group_concat(distinct sb_role_menu.menuid) as menuid';
        $returnArr = $this->DB_get_all_join('role', 'A', $fields, $join, $where, 'A.roleid', $sort . ' ' . $order, $offset . "," . $limit);
        //获取所有menu数据
        $menus = $this->DB_get_all('menu', 'menuid,name,title,parentid', array('status' => 1));
        $menus = getLeftMenu($menus, 0);
        //组织数据
        $arr = array();
        foreach ($menus as $k => $v) {
            foreach ($v['list'] as $k1 => $v1) {
                foreach ($v1['list'] as $k2 => $v2) {
                    $arr[$v['title']][] = $v2['menuid'];
                }
            }
        }
        foreach ($returnArr as $k => $v) {
            $returnArr[$k]['modulename'] = '';
            if (!$v['menuid']) {
                continue;
            }
            foreach ($arr as $k1 => $v1) {
                $m = explode(',', $v['menuid']);
                if (array_intersect($m, $v1)) {
                    $returnArr[$k]['modulename'] .= $k1 . ',';
                }
            }
            $returnArr[$k]['modulename'] = trim($returnArr[$k]['modulename'], ',');
        }
        //查询当前用户是否有权限进行权限维护
        $editRolePrivi = get_menu($this->MODULE, 'Privilege', 'editRolePrivi');
        //查询当前用户是否有权限编辑角色
        $editRole = get_menu($this->MODULE, 'Privilege', 'editRole');
        //查询当前用户是否有权限删除角色
        $deleteRole = get_menu($this->MODULE, 'Privilege', 'deleteRole');
        foreach ($returnArr as $k => $v) {
            $html = '<div class="layui-btn-group">';
            if ($editRolePrivi) {
                $html .= '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="editPri" id="editPri" href="javascript:void(0)" data-url="' . $editRolePrivi['actionurl'] . '">' . $editRolePrivi['actionname'] . '</a>';
            }
            if ($editRole) {
                $html .= '<a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="editRole" id="editRole" href="javascript:void(0)" data-url="' . $editRole['actionurl'] . '">' . $editRole['actionname'] . '</a>';
            }
            if ($deleteRole) {
                $html .= '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="deleteRole" id="deleteRole" href="javascript:void(0)" data-url="' . $deleteRole['actionurl'] . '">' . $deleteRole['actionname'] . '</a>';
            }
            $html .= '</div>';
            $returnArr[$k]['operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $returnArr;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取所有角色
     * @param $where
     * @return array
     */
    public function getAllRoles($where)
    {
        $role = $this->DB_get_all('role', 'roleid,role', $where, '', 'roleid asc', '');
        $res = array();
        $i = 0;
        foreach ($role as $k => $v) {
            $res[$i]['num'] = $k + 1;
            $res[$i]['rolename'] = $v['role'];
            $i++;
        }
        $arr = array();
        $arr['value'] = $res;
        return $arr;
        //$arr = json_encode($arr);
    }
}
