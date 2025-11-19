<?php


namespace Admin\Model;

use Think\Model;
use Think\Model\RelationModel;

class OperationLogModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'operation_log';

    public function operationListData()
    {
        $LogModel = new OperationLogModel();
        $hospital_id = I('post.hospital_id') ? I('post.hospital_id') : session('current_hospitalid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $login = I('post.login') ? I('post.login') : 2;
        //用户名
        $username = I('post.operationListUsername');
        //模块名称
        $module = I('post.operationListModule');
        //事件
        $action = I('post.operationListAction');
        //操作时间
        $startDate = I('post.operationListAction_timeStart');
        $endDate = I('post.operationListAction_timeEnd');
        //查询医院的用户
        //获取超级管理员信息，禁止用户查看超级管理员操作记录
        if (!session('isSuper')) {
            $unames = $LogModel->DB_get_one('user', 'group_concat(username) as usernames', array('job_hospitalid' => $hospital_id, 'status' => C('YES_STATUS'), 'is_delete' => C('NO_STATUS'), 'is_super' => 0));
        } else {
            $unames = $LogModel->DB_get_one('user', 'group_concat(username) as usernames', array('job_hospitalid' => $hospital_id, 'status' => C('YES_STATUS'), 'is_delete' => C('NO_STATUS')));
        }
        if (!$unames['usernames']) {
            //没有用户
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if ($login == 2) {
            $where['module'] = array('NEQ', 'Login');
        }
        $where['username'] = array('in', $unames['usernames']);
        if (!$sort) {
            $sort = 'logid ';
        }
        if (!$order) {
            $order = 'desc';
        }
        //用户名搜索
        if ($username) {
            $where['username'] = $username;
        }
        //模块名称搜索
        if ($module) {
            $where['module'] = $module;
        }
        //事件搜索
        if ($action) {
            $where['action'] = $action;
        }
        //操作时间搜索
        if ($startDate) {
            $where['action_time'][] = array('GT', getHandleDate(strtotime($startDate) - 1));
        }
        //操作时间搜索
        if ($endDate) {
            $where['action_time'][] = array('LT', getHandleDate(strtotime($endDate) + 24 * 3600));
        }

        $total = $LogModel->DB_get_count('operation_log', $where);
        $log = $LogModel->DB_get_all('operation_log', '', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        //获取模块中文名
        $moduleName = $LogModel->DB_get_all('menu', 'name,title', array('parentid' => 0), '', '', '');
        $actionName = $LogModel->DB_get_all('menu', 'name,title', array('parentid' => array('neq', 0), 'leftShow' => 0), '', '', '');
        foreach ($log as $k => $v) {
            $log[$k]['modulename'] = '无';
            $log[$k]['actionname'] = '无';
            foreach ($moduleName as $k1 => $v1) {
                if ($v['module'] == $v1['name']) {
                    $log[$k]['modulename'] = $v1['title'];
                }
            }
            foreach ($actionName as $k2 => $v2) {
                if ($v['action'] == $v2['name']) {
                    $log[$k]['actionname'] = $v2['title'];
                }
            }
            if (isset($v['ip'])) {
                if ($v['ip'] > 0) {
                    $log[$k]['ip'] = long2ip($v['ip']);
                } else {
                    $log[$k]['ip'] = '无';
                }
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $log;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }
}