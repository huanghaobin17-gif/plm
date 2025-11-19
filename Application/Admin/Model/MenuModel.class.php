<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/17
 * Time: 20:54
 */
namespace Admin\Model;
use Think\Model;
use Think\Model\RelationModel;
use Admin\Model\ModuleModel;
import('@.ORG.Util.TableTree'); //Thinkphp导入方法
class MenuModel extends CommonModel
{

    /*
     * 更新角色权限
     * @parma data 要更新的数据
     */
    public function updateRoleMenu($data)
    {
        if(!$data){
            return false;
        }
        $values = array();
        $i = 0;

        $where['roleid'] = array('EQ', $data['roleid']);
        $where['menuid']=['NEQ',0];
        //获取被编辑的角色的详细权限
        $roleData=$this->DB_get_all('role_menu','menuid',$where);

        $editUser_role_all=[];
        foreach ($roleData as &$roleV){
            $editUser_role_all[]=$roleV['menuid'];
        }

        //获取差异(当前用户没权限编辑的部分)
        $diff=array_diff($editUser_role_all,session('sessionmid'));
        $data['menuid']=array_merge($diff,$data['menuid']);

        foreach($data['menuid'] as $k=>$v){
            $values[$i]['roleid'] = $data['roleid'];
            $values[$i]['menuid'] = $v;
            $i++;
        }
        //删除原有的menuid
        // 自动启动事务支持
        $r = M('role_menu');
        $r->startTrans();
        try{
            $result   =  $r->where(array('roleid'=>$data['roleid']))->delete();
            if(false === $result) {
                // 发生错误自动回滚事务
                $this->rollback();
                return false;
            }
            $in = $this->insertDataALL('role_menu',$values);
            // 提交事务
            $this->commit();
            return $in;
        } catch (ThinkException $e) {
            $this->rollback();
            throw new throw_exception($e->getMessage());
        }
    }

    /**
     * 格式化菜单返回
     */
    public function formatMenu($menus)
    {
        $moduleModel = new ModuleModel();
        $wx_status=$moduleModel->decide_wx_login();
        //不显示的二级menu
        $notShow=[];
        foreach ($menus as &$one){
            if($one['leftShow']==C('NO_STATUS')){
                $notShow[$one['menuid']]=$one;
            }
        }
        foreach ($menus as &$one){
            //如果二级需要不显示 三级需要显示的数据，将第二级的parentid赋值给第三级 让第三级作为第二级直接显示
            if($one['leftShow']==C('YES_STATUS') && $notShow[$one['parentid']]){
                $one['parentid']=$notShow[$one['parentid']]["parentid"];
            }
        }
        //分组并排序显示
        $modules = [];
        $i = 0;
        foreach ($menus as $k=>$v){
            if($v['parentid'] == 0 && $v['leftShow']==C('YES_STATUS')){
                $modules[$i]['menuid'] = $v['menuid'];
                $modules[$i]['name'] = $v['name'];
                $modules[$i]['title'] = $v['title'];
                $modules[$i]['icon'] = $v['icon'];
                $modules[$i]['jump'] = $v['jump'];
                $modules[$i]['orderID'] = $v['orderID'];
                $i++;
                unset($menus[$k]);
            }
        }
        array_multisort(array_column($modules,'orderID'),SORT_ASC,$modules);
        foreach ($modules as $k=>$v) {
            $modules[$k]['include'] = [];
            foreach ($menus as $k1 => $v1) {
                if ($v1['parentid'] == $v['menuid']){
                    $modules[$k]['include'][] = $v1['name'];
                }
            }
        }
        foreach ($modules as $k=>$v){
            $controllers = [];
            $j = 0;
            foreach ($menus as $k1=>$v1){
                if($v1['parentid'] == $v['menuid'] && $v1['leftShow']==C('YES_STATUS')){
                    $controllers[$j]['menuid'] = $v1['menuid'];
                    $controllers[$j]['name'] = $v1['name'];
                    $controllers[$j]['title'] = $v1['title'];
                    $controllers[$j]['icon'] = $v1['icon'];
                    $controllers[$j]['jump'] = $v1['jump'];
                    if($v1['jump']){
                        $pre = explode('/',$v1['jump']);
                        $controllers[$j]['pre'] = $pre[1];
                    }
                    $controllers[$j]['orderID'] = $v1['orderID'];
                    $j++;
                    if($v1['name']=='getAssetsList'){
                        //特殊处理生命支持类
                        $controllers[$j]['menuid'] = $v1['menuid'];
                        $controllers[$j]['name'] = $v1['name'].'?action=lifeAssetsList';
                        $controllers[$j]['title'] = '生命支持类设备列表';
                        $controllers[$j]['icon'] = $v1['icon'];
                        $controllers[$j]['jump'] = $v1['jump'].'?action=lifeAssetsList';
                        if($v1['jump']){
                            $pre = explode('/',$v1['jump']);
                            $controllers[$j]['pre'] = $pre[1];
                        }
                        $controllers[$j]['orderID'] = $v1['orderID']+1;
                        $j++;
                    }
                    unset($menus[$k1]);
                }
            }
            array_multisort(array_column($controllers,'orderID'),SORT_ASC,$controllers);
            $modules[$k]['list'] = $controllers;
            foreach($modules[$k]['list'] as $k2=>$v2){
                $actions = $include2 = [];
                $n = 0;
                foreach ($menus as $k3=>$v3){
                    //判断微信登录是否开启，没开启显示微信参数设置
                    if (!$wx_status&&$v3['name']=='system') {
                        $v3['leftShow']=C('NO_STATUS');
                    }
                    if($v3['parentid'] == $v2['menuid'] && $v3['leftShow']==C('YES_STATUS')){
                        $include2[] = $v3['name'];
                        $actions[$n]['menuid'] = $v3['menuid'];
                        $actions[$n]['name'] = $v3['name'];
                        $actions[$n]['title'] = $v3['title'];
                        $actions[$n]['orderID'] = $v3['orderID'];
                        $n++;
                    }
                }
                array_multisort(array_column($actions,'orderID'),SORT_ASC,$actions);
                $modules[$k]['list'][$k2]['list'] = $actions;
                $modules[$k]['list'][$k2]['include'] = $include2;
            }
        }

        foreach ($modules as &$v1){
            foreach ($v1['list'] as &$vv2){
                if($vv2['jump'] != '/' && $vv2['jump']){
                    $tmparr = [];
                    $tmparr = explode('/',$vv2['jump']);
                    unset($tmparr[0]);
                    $str = implode('/',$tmparr);
                    $vv2['jump'] = $str;
                }
            }
        }
        return $modules;
    }

    /**
     * Notes:获取侧边菜单
     */
    public function getMenuLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $where['status'] = 1;
        $menus = $this->DB_get_all('menu', '*', array('1'), '', 'parentid,orderID asc', $offset . ',' . $limit);
        $formatMenus = getTree('parentid','menuid',$menus);
        $res = array();
        foreach ($formatMenus as $k=>$v){
            if($v['level'] <=1){
                $res[] = $v;
            }
        }
        foreach ($res as $k=>$v){
            if($v['level'] ==1){
                $res[$k]['operation'] = '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="detail" data-id="'.$v['menuid'].'" href="javascript:void(0)" data-url="/index.php/Admin/BaseSetting/Menu/getDetail.html">明细管理</a>';
            }
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = count($res);
        $result['rows'] = $res;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    public function getMenuLists1()
    {
        $data = $this->DB_get_all('menu','menuid,parentid,name,title,status,leftShow,orderID,op_parentid',array('op_parentid'=>array('exp','is null')),'','parentid,orderID asc');
        $data = getTree('parentid','menuid',$data,0,0,'');
        foreach ($data as $k=>$v){
            $html = '<div class="layui-btn-group">';
            if (session('isSuper') && $v['status'] == 1 && $v['parentid'] != 0) {
                $html .= $this->returnListLink('停用', get_url(), 'stop', C('BTN_CURRENCY') . ' layui-btn-danger');
            }
            if (session('isSuper') && $v['status'] == 0 && $v['parentid'] != 0) {
                $html .= $this->returnListLink('启用', get_url(), 'start', C('BTN_CURRENCY') . ' layui-btn-normal');
            }
            if($v['level'] == 2){
                $html .= $this->returnListLink('管理明细', get_url(), 'getDetail', C('BTN_CURRENCY') . ' layui-btn-normal');
            }
            $html .= '</div>';
            $data[$k]['menu_operation'] = $html;
        }
        $result['total'] = 924;
        $result['is'] = true;
        $result['tips'] = '操作成功';
        $result['code'] = 200;
        $result['msg'] = '';
        $result['rows'] = $data;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes:获取明细菜单
     */
    public function getDetail()
    {
        $menuid = I('POST.menuid');
        if(!$menuid){
            $result['rows'] = array();
            $result['code'] = 200;
            if (!$result['rows']) {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
            }
            return $result;
        }
        $where['op_parentid'] = $menuid;
        $data = $this->DB_get_all('menu', '*', $where, '', 'leftShow desc,orderID asc');
        foreach ($data as $k=>$v){
            $html = '<div class="layui-btn-group">';
            if (session('isSuper') && $v['status'] == 1) {
                $html .= $this->returnListLink('停用', get_url(), 'stop', C('BTN_CURRENCY') . ' layui-btn-danger');
            }
            if (session('isSuper') && $v['status'] == 0) {
                $html .= $this->returnListLink('启用', get_url(), 'start', C('BTN_CURRENCY') . ' layui-btn-normal');
            }
            if($v['level'] == 2){
                $html .= $this->returnListLink('管理明细', get_url(), 'getDetail', C('BTN_CURRENCY') . ' layui-btn-normal');
            }
            $html .= '</div>';
            $data[$k]['son_operation'] = $html;
        }
        $result['rows'] = $data;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    //获取menu所有权限
    public function getAllMenu(){
        $roleid = I('GET.roleid');
        //查找当前用户拥有的权限
        if(session('isSuper')){
            $mymid = $this->DB_get_all('menu','menuid',array('status'=>C('OPEN_STATUS')));
            foreach ($mymid as $v){
                $mid[] = $v['menuid'];
            }
        }else{
            //非超级管理员
            $join = "LEFT JOIN sb_role_menu AS B ON B.roleid = A.roleid";
            $rowhere['A.userid'] = session('userid');
            $mymid = $this->DB_get_all_join('user_role','A','B.menuid',$join,$rowhere,'','','');
            foreach ($mymid as $v){
                $myidArr[] = $v['menuid'];
            }
            $mid = $this->get_moduleid($myidArr);
        }
        // var_dump($mymid);die;
        //查询当前用户所拥有的所有权限
        $where['status'] = C('OPEN_STATUS');
        $where['menuid'] = array('in',$mid);
        if(session('isSuper')==C('YES_STATUS')){
            $menuArr = $this->DB_get_all('menu', 'menuid,name,BaseSettingTitle AS title,parentid,orderID,leftShow,op_parentid,tips', $where, '', '', '');
        }else{
            $menuArr=session('leftShowMenu');
        }

        $moduleModel = new ModuleModel();
        $wx_status=$moduleModel->decide_wx_login();
        $tmp = $modules = $res = $parents = array();
        foreach ($menuArr as $k => $v) {
            if ($v['name']=='system'&&!$wx_status) {
                unset($menuArr[$k]);
            }
            if($v['BaseSettingTitle']){
                $menuArr[$k]['title']=$v['BaseSettingTitle'];
            }
            if ($v['parentid'] == 0) {
                $modules[] = $v;
                unset($menuArr[$k]);
            }
        }
        array_multisort(array_column($modules, 'orderID'), SORT_ASC, $modules);
        foreach ($modules as $k => $v) {
            $parents[] = (int)$v['menuid'];
            $tmp[$v['menuid']] = 0;
            foreach ($menuArr as $k1 => $v1) {
                if ($v1['parentid'] == $v['menuid']) {
                    $res[] = $v1;
                    $tmp[$v['menuid']] += 1;
                    unset($menuArr[$k1]);
                }
            }
        }
        foreach ($res as $k => $v) {
            $res[$k]['hasRowSpan'] = false;
            $res[$k]['rowSpan'] = 0;
            if (!in_array($v['parentid'], $parents)) {
                continue;
            }
            foreach ($tmp as $k1 => $v1) {
                if ($v['parentid'] == $k1) {
                    $res[$k]['hasRowSpan'] = true;
                    $res[$k]['rowSpan'] = $v1;
                    foreach ($modules as $k2 => $v2) {
                        if ($v2['menuid'] == $v['parentid']) {
                            $res[$k]['modulename'] = $v2['title'];
                            $res[$k]['modulecode'] = $v2['name'];
                        }
                    }
                    foreach ($parents as $k3 => $v3) {
                        if ($v3 == $v['parentid']) {
                            unset($parents[$k3]);
                        }
                    }
                }
            }
        }
        $roleMenus = $this->DB_get_one('role_menu', 'group_concat(menuid) as menuids', array('roleid' => $roleid));
        $roleMenus = explode(',', $roleMenus['menuids']);
        foreach ($res as $k => $v) {
            foreach ($menuArr as $k1 => $v1) {
                if ($v1['parentid'] == $v['menuid']) {
                    $v1['Selected'] = false;
                    if (in_array($v1['menuid'], $roleMenus)) {
                        $v1['Selected'] = true;
                    }
                    $res[$k]['menus'][] = $v1;
                }
            }
        }
        return $res;
    }

    //获取对应角色的权限
    public function getRoleMenu(){
        $roleid=I('POST.roleid');
        if($roleid===""){
            die(json_encode(array('status' => -999, 'msg' => '参数异常')));
        }
        $roleMenus = [];
        include APP_PATH . "Common/Conf/role.php";

        if($roleMenus[$roleid]){
            $result = [];
            $one = "";
            $two = "";
            $three = "";

            foreach ($roleMenus[$roleid]['role'] as $key => $value) {
                $one .= "'$key',";
                foreach ($value as $k => $v) {
                    $two .= "'$k',";
                    foreach ($v as $ks => $vs) {
                        $three .= "'$vs',";
                    }
                }
            }
            foreach ($roleMenus[$roleid]['role'] as $key => $value) {
                $one_sql = " and parentid in(SELECT menuid FROM sb_menu WHERE name = '$key' and parentid='0'))  union all  ";
                foreach ($value as $k => $v) {
                    $two_sql = " and parentid in(SELECT menuid FROM sb_menu WHERE name = '$k'".$one_sql;
                    foreach ($v as $ks => $vs) {
                        $three_sql = "SELECT menuid FROM sb_menu WHERE name='$vs'".$two_sql;
                        $sql .= $three_sql;
                    }
                }
            }
            $sql = rtrim($sql, 'union all');
            $menu_arr = $this->query($sql);
            foreach ($menu_arr as &$one){
                $result[$one['menuid']]=true;
            }
            return array('status' => 1, 'msg' => '获取成功！','result'=>$result);
        }else{
            return array('status' => -999, 'msg' => '未配置！');
        }
    }


    /**
     * Notes: 返回用户的模块ID
     * @param $left array 模块
     * @param $myidstr string 用户权限
     * @return array
     */
    public function get_moduleid($myidArr)
    {
        $where['status'] = 1;
        $menuArr = $this->DB_get_all('menu', 'menuid,name,BaseSettingTitle AS title,parentid,orderID,leftShow,op_parentid,tips', $where, '', '', '');
        $fmenu = getLeftMenu($menuArr,0);
        $one = $two = array();
        foreach ($fmenu as $k=>$v){
            foreach ($v['list'] as $k1=>$v1){
                foreach ($v1['list'] as $k2=>$v2){
                    $one[$v['menuid']][] = $v2['menuid'];
                    $two[$v1['menuid']][] = $v2['menuid'];
                }
            }
        }
        foreach ($myidArr as $k=>$v){
            foreach ($one as $k1=>$v1){
                if(in_array($v,$v1)){
                    if(!in_array($k1,$myidArr)){
                        $myidArr[] = $k1;
                    }
                }
            }
        }
        foreach ($myidArr as $k=>$v){
            foreach ($two as $k1=>$v1){
                if(in_array($v,$v1)){
                    if(!in_array($k1,$myidArr)){
                        $myidArr[] = $k1;
                    }
                }
            }
        }
        return $myidArr;
    }

    /**
     * Notes: 获取角色首页设置
     * @param $roleid
     */
    public function get_all_target_setting($roleid)
    {
        $data = $this->DB_get_all('role_target_setting','role_id,set_type,group_concat(chart_id) as chart_id',array('role_id'=>$roleid),'set_type');
        $res = [];
        foreach($data as $k=>$v){
            $res[$v['set_type']]['chart_id'] = explode(',',$v['chart_id']);
        }
        return $res;
    }

    /**
     * Notes: 保存角色首页统计图表显示设置
     */
    public function save_target_setting()
    {
        $role_id = I('post.role_id');
        unset($_POST['type']);
        unset($_POST['role_id']);
        $data = [];
        $i = 0;
        foreach ($_POST as $k=>$v){
            foreach ($v as $k1=>$v1){
                $data[$i]['role_id'] = $role_id;
                $data[$i]['set_type'] = $k;
                $data[$i]['chart_id'] = $k1;
                $i++;
            }
        }
        if($data){
            //删除原有设置
            $this->deleteData('role_target_setting',array('role_id'=>$role_id));
        }
        $res = $this->insertDataALL('role_target_setting',$data);
        if($res){
            return array('status'=>1,'msg'=>'设置成功！');
        }else{
            return array('status'=>-1,'msg'=>'设置失败！');
        }
    }

}
