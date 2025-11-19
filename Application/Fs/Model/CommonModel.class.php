<?php

namespace Fs\Model;

use Think\Model;

class CommonModel extends Model
{
    Protected $autoCheckFields = false;

    protected $fail_url = 'Notin/fail.html';//失败跳转地址
    protected $succ_url = 'Notin/suc.html';//成功跳转地址
    protected $index_url = 'Index/testindex.html';//首页地址
    protected $addRepair_url = 'Repair/addRepair.html';//报修地址
    protected $ordersLists_url = 'Repair/ordersLists.html';//接单列表地址
    protected $overhaulLists_url = 'Repair/ordersLists.html?action=overhaulLists';//检修列表地址
    protected $accept_url = 'Repair/accept.html';//接单、检修操作地址
    protected $repair_detail_url = 'Repair/showRepairDetails.html';//维修单详情地址

    /*
     * 连表操作获取数据集合
     * @param1 $table string 查询表
     * @param2 $alias string 主表别名
     * @param4 $join  array 连表操作
     * @param5 $where array  查询条件
     */
    public function DB_get_count_join($table, $alias, $join, $where, $group = '')
    {
        $res = M($table, C('DB_PREFIX'))->alias($alias)->join($join)->where($where)->group($group)->count();
        //echo M($table,C('DB_PREFIX'))->getLastSql();exit;
        return $res;
    }


    /*
     * 连表操作获取数据集合
     * @param1 $table string 查询表
     * @param3 $alias string 主表别名
     * @param4 $fields string 查询的字段
     * @param5 $join  array 连表操作
     * @param6 $where array  查询条件
     * @param7 $group string 分组字段
     * @param8 $order string 排序字段
     * @param9 $limit string 分页
     */
    public function DB_get_all_join($table, $alias, $fields, $join, $where = '', $group, $order = '', $limit = '')
    {
        $res = M($table, C('DB_PREFIX'))->alias($alias)->field($fields)->join($join)->where($where)->group($group)->order($order)->limit($limit)->select();
        //echo M($table,C('DB_PREFIX'))->getLastSql();exit;
        return $res;
    }

    /*
       * 连表操作获取单条数据
       * @param1 $table string 查询表
       * @param2 $alias string 主表别名
       * @param3 $fields string 查询的字段
       * @param4 $join  array 连表操作
       * @param5 $where array  查询条件
       */
    public function DB_get_one_join($table, $alias, $fields, $join, $where = '', $group = '', $order = '')
    {
        $res = M($table, C('DB_PREFIX'))->alias($alias)->field($fields)->join($join)->where($where)->group($group)->order($order)->find();
        //echo M($table,C('DB_PREFIX'))->getLastSql();exit;
        return $res;
    }

    /*
    * 获取单条数据
    * @param1 $table1 string 查询表
    * @param2 $fields string 查询的字段
    * @param3 $where string  查询条件
    * @param4 $order string  顺序
    */
    public function DB_get_one($table, $fields = '', $where = '', $order = '', $group = '')
    {
        $res = M($table, C('DB_PREFIX'))->field($fields)->where($where)->group($group)->order($order)->find();
        //echo M($table,C('DB_PREFIX'))->getLastSql();exit;
        return $res;
    }

    /*
     * 获取数据集合
     * @param1 $table string 查询表
     * @param2 $fields string 查询的字段
     * @param3 $where string  查询条件
     * @param4 $group string 分组字段
     * @param5 $order string 排序字段
     * @param6 $order string 分页
     */
    public function DB_get_all($table, $fields = '', $where = '', $group = '', $order = '', $limit = '')
    {
        $res = M($table, C('DB_PREFIX'))->field($fields)->where($where)->group($group)->order($order)->limit($limit)->select();
        //echo M($table,C('DB_PREFIX'))->getLastSql();exit;
        return $res;
    }

    /*
    * 获取数据总数
    * @param1 $table string 查询表
    * @param2 $where string  查询条件
    */
    public function DB_get_count($table, $where = '', $group = '')
    {
        $res = M($table, C('DB_PREFIX'))->where($where)->group($group)->count();
        //echo M($table,C('DB_PREFIX'))->getLastSql();exit;
        return $res;
    }

    /*
     * 新增数据
     */
    public function insertData($table, $data)
    {
        $res = M($table, C('DB_PREFIX'))->add($data);
        return $res;
    }

    /*
    * 新增数据批量
    */
    public function insertDataALL($table, $data)
    {
        $res = M($table, C('DB_PREFIX'))->addAll($data);
        return $res;
    }

    /*
     * 修改数据
     */
    public function updateData($table, $data, $where)
    {
        $res = M($table, C('DB_PREFIX'))->where($where)->save($data);
        //echo M($table,C('DB_PREFIX'))->getLastSql();exit;
        return $res;
    }

    /*
     * 删除数据
     */
    public function deleteData($table, $where)
    {
        $res = M($table, C('DB_PREFIX'))->where($where)->delete();
        return $res;
    }


    /**
     * 列表页链接拼接
     * @param string $name 按钮内容
     * @param string $url 链接地址
     * @param string $layEvent 监听名称
     * @param string $class 类
     * @param string $style 样式
     * @param string $string 特殊参数等
     * @return string
     **/
    public function returnListLink($name, $url, $layEvent, $class, $style = '', $string = '')
    {
        return '<button class="layui-btn ' . $class . '" lay-event="' . $layEvent . '" style="' . $style . '"  data-url="' . $url . '" ' . $string . '>' . $name . '</button>';
    }


    /*列表页链接拼接按钮
     * $name 按钮名字
     * $url  按钮链接
     * $class 样式名称
     * $color  字体颜色
     * $type 可加参数
     * */
    public function returnButtonLink($name, $url, $class, $color = '', $type = '')
    {
        return '<button style="color:' . $color . ';" class=" ' . $class . '"  ' . $type . ' data-url="' . $url . '">' . $name . '</button>';
    }


    //任务提示链接拼接
    public function returnTaskALink($typeName, $name, $url, $class, $color = '', $string = '')
    {
        if (!$color) {
            $color = C('HTML_A_LINK_COLOR_BLUE');
        }
        return '<a class="titlecolor ' . $class . '" href="javascript:void(0)" ' . $string . ' lay-href="' . $url . '"><span style="color:' . $color . ';" >' . $typeName . '</span>' . $name . '</a>';
    }

    /**
     * 移动端按钮
     * @param string $name 按钮内容
     * @param string $url 链接地址
     * @param string $class 类
     * @param string $style 样式
     * @param string $string 特殊参数等
     * @return string
     **/
    public function returnMobileLink($name, $url, $class, $style = '', $string = '')
    {
        return '<a href="' . $url . '" class="layui-btn ' . $class . '" style="' . $style . '" ' . $string . ' >' . $name . '</a>';
    }


    /*增加日志记录
     * $table 操作了哪个表
     * $sql 记录关键的sql语句
     * $actionid 具体id
     * $text 行为记录语
     *  */
    public function addLog($tableName, $sql, $text, $actionid = '', $actionName = '')
    {
        if (C('IS_OPEN_LOG')) {
            //当前用户id
            $addData['username'] = session('username');
            if ($tableName) {
                //如果存在表就存表
                $addData['table'] = 'sb_' . $tableName;
            }

            if ($sql) {
                //如果存在sql语句就存sql
                $addData['sql'] = $sql;
            }

            //如果存在事件id就存事件id
            $addData['actionid'] = $actionid ? $actionid : '';
            //当前模块
            $newmodule = explode('/', CONTROLLER_NAME);
            $addData['module'] = $newmodule[0];
            //当前控制器
            $addData['controller'] = $newmodule[1];
            //当前方法
            $addData['action'] = $actionName ? $actionName : ACTION_NAME;
            //当前ip
            $addData['ip'] = get_ip();
            //当前事件记录时间
            $addData['action_time'] = getHandleDate(time());
            //提示语
            $addData['remark'] = $text;
            $this->insertData('operation_log', $addData);
        }
    }


    /*
    * 获取用户
    */
    public function getUser()
    {
//        //查询该医院代码是否存在或在该用户管理范围内
//        if (session('isSuper') && C('IS_OPEN_BRANCH')) {
//            //查询当前用户所在的医院代码
//            $existsid = explode(',', session('manager_hospitalid'));
//        } else {
//            $existsid = explode(',', session('job_hospitalid'));
//        }
        $user = $this->DB_get_all('user', 'userid,username', array('status' => 1, 'is_delete' => 0, 'job_hospitalid' => session('current_hospitalid')), '', 'userid asc', '');
        return $user;
    }

    public function checkstatus($value, $text)
    {
        if (!$value) {
            die(json_encode(array('status' => -1, 'msg' => $text)));
        }
    }

    //url 格式化弹窗链接
    public function full_open_url($MODULE, $CONTROLLER)
    {
        return $_SERVER['SCRIPT_NAME'] . '/admin/' . $MODULE . '/' . $CONTROLLER . '/';
    }

    //url 格式化
    public function full_url($CONTROLLER, $CATE)
    {
        return '/' . $CONTROLLER . '/' . $CATE . '/';
    }

    /**
     * Notes: 记录设备状态变更信息
     * @param $assid int 设备assid
     * @param $status int 设备状态码
     * @param $remark string 备注信息
     */
    public function updateAssetsStatus($assid, $status, $remark)
    {
        $this->insertData('assets_state_change', array('assid' => $assid, 'new_status' => $status, 'remark' => $remark, 'change_user' => $_SESSION['username'], 'change_time' => getHandleDate(time())));
    }


    /**
     * Notes: 记录设备状态变更信息 批量
     * @param $update_assid_arr array 设备assid
     * @param $status int 设备状态码
     * @param $remark string 备注信息
     */
    public function updateAllAssetsStatus($update_assid_arr, $status, $remark)
    {
        $addAllChange = [];
        foreach ($update_assid_arr as $key => $value) {
            $addAllChange[$key]['assid'] = $value;
            $addAllChange[$key]['new_status'] = $status;
            $addAllChange[$key]['remark'] = $remark;
            $addAllChange[$key]['change_user'] = session('username');
            $addAllChange[$key]['change_time'] = getHandleDate(time());
        }
        //记录设备状态变更信息
        $this->insertDataALL('assets_state_change', $addAllChange);
    }

    /**
     * Notes:查询审批是否已开启
     * @param string $type 类型
     * @param int $hospital_id 医院ID
     * @return bool
     */
    public function checkApproveIsOpen($type, $hospital_id)
    {
        $res = $this->DB_get_one('approve_type', '*', array('approve_type' => $type, 'hospital_id' => $hospital_id, 'status' => C('OPEN_STATUS')));
        if ($res) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Notes:查询短信发送是否已开启
     * @param string $model 模块
     * @return bool
     */
    public function checkSmsIsOpen($model)
    {
        S('sms_settingData', NULL);
        $settingData = S('sms_settingData');
        if (!$settingData) {
            S('sms_basesetting', NULL);
            $Data = S('sms_basesetting');
            if (!$Data) {
                $Data = M('sms_basesetting')->where(['hospital_id' => session('current_hospitalid')])->cache('sms_basesetting', 3600)->select();
            }
            if (!$Data) {
                return false;
            }
            $settingData = [];
            $parentData = [];
            foreach ($Data as $key => $val) {
                if ($val['parentid'] == 0) {
                    if ($val['status'] == C('SHUT_STATUS')) {
                        return false;
                    }
                    $settingData[$val['action']]['status'] = $val['status'];
                    unset($Data[$key]);
                } else {
                    if ($val['content'] == '' && $val['action'] == $model) {
                        if ($val['status'] == C('SHUT_STATUS')) {
                            return false;
                        }
                        $settingData[$val['action']]['status'] = $val['status'];
                        $parentData[$val['id']] = $val['action'];
                        unset($Data[$key]);
                    }
                }
            }
            foreach ($Data as &$one) {
                $settingData[$parentData[$one['parentid']]][$one['action']]['status'] = $one['status'];
                $settingData[$parentData[$one['parentid']]][$one['action']]['content'] = $one['content'];
            }
            S('sms_settingData', $settingData, 3600);
        }
        return $settingData[$model];
    }


    /**
     * Notes:查询审批是否已开启
     * @param string $type 类型
     * @param int $hospital_id 医院ID
     * @return bool
     */
    public function checkApproveIsSetProcess($type, $hospital_id)
    {

        $res = $this->DB_get_one('approve_type', '*', array('approve_type' => $type, 'hospital_id' => $hospital_id, 'status' => C('OPEN_STATUS')));
        if ($res) {
            $process = $this->DB_get_one('approve_process', '*', array('typeid' => $res['typeid']));
            if ($process) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Notes: 判断分类编码是否符合系统设置
     */
    public function checkCateNum($catenum)
    {
        if (C('CATE_PREFIX_NUM') == '') {
            return array('status' => 1, 'msg' => '系统未设置分类编码规则', 'num' => $catenum);
        }
        $prefix = substr($catenum, 0, strlen(C('CATE_PREFIX_NUM')));
        if ($prefix == C('CATE_PREFIX_NUM')) {
            return array('status' => 1, 'msg' => '分类编码规则符合系统设置', 'num' => $catenum);
        } else {
            return array('status' => -1, 'msg' => '分类编码规则不符合系统设置要求，请以 ' . C('CATE_PREFIX_NUM') . ' 为开头设置分类编码！');
        }
    }

    /**
     * Notes: 判断部门编码是否符合系统设置
     */
    public function checkDepartNum($departnum)
    {
        if (C('DEPART_PREFIX_NUM') == '') {
            return array('status' => 1, 'msg' => '系统未设置部门编码规则', 'num' => $departnum);
        }
        $prefix = substr($departnum, 0, strlen(C('DEPART_PREFIX_NUM')));
        if ($prefix == C('DEPART_PREFIX_NUM')) {
            return array('status' => 1, 'msg' => '部门编码规则符合系统设置要求', 'num' => $departnum);
        } else {
            return array('status' => -1, 'msg' => '部门编码规则不符合系统设置要求，请以 ' . C('DEPART_PREFIX_NUM') . ' 为开头设置部门编码！');
        }
    }

    public function get_all_hospital()
    {
        return $this->DB_get_all('hospital', '*', array('is_delete' => 0));
    }

    public function get_all_user_can_manager_hospital($userid)
    {
        $userinfo = $this->DB_get_one('user', 'manager_hospitalid', array('userid' => $userid));
        return $this->DB_get_all('hospital', '*', array('is_delete' => 0, 'hospital_id' => array('in', $userinfo['manager_hospitalid'])));
    }

    /**
     * Notes: 获取医院名称
     * @param $hospital_id
     * @return array
     */
    public function get_hospital_name($hospital_id)
    {
        $hosidarr = explode(',', $hospital_id);
        if (count($hosidarr) > 1) {
            $res = array('hospital_name' => '各医院');
        } else {
            $res = $this->DB_get_one('hospital', '*', array('hospital_id' => $hospital_id));
        }
        return $res;
    }

    /**
     * Notes: 验证流程是否有误,并且返回下次审批人
     * @param $assets array 设备基础信息
     * @param $approve_process_user array 审批流程
     * @param $level int 审批级别
     * @return array
     */
    public function check_approve_process($departid, $approve_process_user, $level)
    {
        $result = [];
        $all_approver = '';
        $current_approver = '';
        $this_current_approver = '';
        foreach ($approve_process_user as &$approveV) {
            if ($approveV['approve_user'] == '部门审批负责人') {
                $traninManager = $this->DB_get_one('department', 'manager', array('departid' => $departid));
                if (!$traninManager['manager']) {
                    $departname = [];
                    include APP_PATH . "Common/cache/department.cache.php";
                    die(json_encode(array('status' => -1, 'msg' => '该设备所属科室（' . $departname[$departid]['department'] . '）未设置审批负责人，请先设置')));
                } else {
                    $approveV['approve_user'] = $traninManager['manager'];
                }
            }
            if ($approveV['listorder'] == $level) {
                $current_approver = $approveV['approve_user'] . ',' . $approveV['approve_user_aux'];
                $this_current_approver = $approveV['approve_user'];
            }
            $all_approver .= ',/' . $approveV['approve_user'] . '/';
            if ($approveV['approve_user_aux']) {
                $all_approver .= ',/' . $approveV['approve_user_aux'] . '/';
            }
        }
        $result['all_approver'] = trim($all_approver, ',');
        $result['current_approver'] = trim($current_approver, ',');
        $result['this_current_approver'] = $this_current_approver;
        return $result;
    }

    /**
     * Notes: 获取审批流程
     * @param $assets array 设备基础信息
     * @param $approve_type string 审批类型
     * @return array
     */
    public function get_approve_process($price, $approve_type, $hospital_id)
    {
        //查询typeid
        $type = $this->DB_get_one('approve_type', 'typeid', array('approve_type' => $approve_type, 'hospital_id' => $hospital_id));
        $approve_process_where['typeid'] = array('EQ', $type['typeid']);
        $approve_process_where['start_price'] = array('ELT', $price);
        $approve_process_where['end_price'] = array('GT', $price);
        //查询是否有关联部门的审核流程
        $approve_process = $this->DB_get_one('approve_process', 'processid', $approve_process_where);
        if ($approve_process) {
            $approve_process_user = $this->DB_get_all('approve_process_user', 'approve_user,approve_user_aux,listorder', array('processid' => $approve_process['processid']), '', 'listorder ASC');
            if (!$approve_process_user) {
                die(json_encode(array('status' => -1, 'msg' => '未分配审批人员，请联系管理员')));
            } else {
                $usernameArr = [];
                $approve_process_user_data = [];
                foreach ($approve_process_user as &$one) {
                    $usernameArr[] = $one['approve_user'];
                    $approve_process_user_data[$one['approve_user']] = $one;
                }
                $user = $this->DB_get_all('user', 'status,is_delete,username', ['username' => ['IN', $usernameArr]]);
                foreach ($user as &$userV) {
                    if ($userV['status'] != C('OPEN_STATUS') or $userV['is_delete'] != C('NO_STATUS')) {
                        unset($approve_process_user_data[$userV['username']]);
                    }
                }
                if (!$approve_process_user_data) {
                    die(json_encode(array('status' => -1, 'msg' => '配置的审批人停用或已删除，请联系管理员重新配置')));
                }
                return $approve_process_user_data;
            }
        } else {
            return [];
        }
    }

    public function check_can_edit_approve($hospital_id, $approve_type)
    {
        $table = $where = array();
        switch ($approve_type) {
            case 'repair_approve':
                $table = 'repair';
                $where['A.approve_status'] = 0;
                $where['B.hospital_id'] = $hospital_id;
                break;
            case 'transfer_approve':
                $table = 'assets_transfer';
                $where['A.approve_status'] = 0;
                $where['B.hospital_id'] = $hospital_id;
                break;
            case 'scrap_approve':
                $table = 'assets_scrap';
                $where['A.approve_status'] = 0;
                $where['B.hospital_id'] = $hospital_id;
                break;
            case 'outside_approve':
                $table = 'assets_outside';
                $where['A.approve_status'] = 0;
                $where['B.hospital_id'] = $hospital_id;
                break;
            case 'purchases_plans_approve':
                $table = 'purchases_plans';
                $where['approve_status'] = 0;
                $where['hospital_id'] = $hospital_id;
                $res = $this->DB_get_one($table, '*', $where);
                if ($res) {
                    //存在，不能修改或关闭
                    return false;
                } else {
                    //不存在，可以修改或关闭
                    return true;
                }
                break;
            case 'depart_apply_approve':
                $table = 'purchases_depart_apply';
                $where['approve_status'] = 0;
                $where['hospital_id'] = $hospital_id;
                $res = $this->DB_get_one($table, '*', $where);
                if ($res) {
                    //存在，不能修改或关闭
                    return false;
                } else {
                    //不存在，可以修改或关闭
                    return true;
                }
                break;
        }
        $where['B.is_delete'] = '0';
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $res = $this->DB_get_one_join($table, 'A', 'A.*', $join, $where);
        if ($res) {
            //存在，不能修改或关闭
            return false;
        } else {
            //不存在，可以修改或关闭
            return true;
        }
    }

    public function get_complete_ornot_user($arr)
    {
        if ($arr['complete_approver']) {
            $complete = explode(',', $arr['complete_approver']);
        } else {
            $complete = array();
        }
        //已审批人
        $current = explode(',', $arr['current_approver']);
        foreach ($current as $k => $v) {
            array_push($complete, $v);
        }
        //未审批人
        $all = explode(',', str_replace('/', '', $arr['all_approver']));
        $len = count($complete);
        for ($i = 0; $i < $len; $i++) {
            unset($all[$i]);
        }
        $res['complete'] = implode(',', $complete);
        $res['notcomplete'] = implode(',', $all);
        return $res;
    }

    /**
     * Notes: 保存审批结果
     * @param $arr array 对应的审批申请信息
     * @param $data array 需要保存的审批数据
     * @param $price float 审批金额
     * @param $hospital_id int 设备所属医院ID
     * @param $departid int 设备所属部门ID
     * @param $approveType string 审批类型
     * @param $tableName string 表名
     * @param $idName string 字段名称
     * @return array
     */
    public function addApprove($arr, $data, $price, $hospital_id, $departid, $approveType, $tableName, $idName)
    {
        //获取审批流程
        $approve_process_user = $this->get_approve_process($price, $approveType, $hospital_id);
        //总审核流程数
        $lastProcess = count($approve_process_user);
        $approve = $this->insertData('approve', $data);
        $lastSql = M()->getLastSql();
        if (!$approve) {
            return array('status' => -1, 'msg' => '审批失败，请稍后再试！');
        }
        //更新已审批人和未审批人
        $completeornotuser = $this->get_complete_ornot_user($arr);
        //更新已审批人，未审批人
        $this->updateData($tableName, array('complete_approver' => $completeornotuser['complete'], 'not_complete_approver' => $completeornotuser['notcomplete']), array($idName => $arr[$idName]));
        //判断是否是最后一道审批或者审批不通过
        if ($data['process_node_level'] == $lastProcess || $data['is_adopt'] == C('STATUS_APPROE_FAIL')) {
            //更新表对应记录为最后审核状态
            $this->updateData($tableName, array('approve_status' => $data['is_adopt'], 'approve_time' => getHandleDate(time())), array($idName => $arr[$idName]));
            if ($data['is_adopt'] == C('STATUS_APPROE_SUCCESS')) {
                //本次为最后一道审批，且为同意
                if ($approveType == C('REPAIR_APPROVE')) {
                    //修改维修表中status为维修中
                    $this->updateData($tableName, array('status' => C('REPAIR_MAINTENANCE')), array($idName => $arr[$idName]));
                    //最后一次审批通过以后生成一条维修合同
                    if ($arr['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')) {
                        //第三方类型审批通过后 生成1条待确认的合同 记录基本信息
                        $companyInfo = $this->DB_get_one('repair_offer_company', 'offer_company_id,total_price,offer_company,offer_contacts,telphone', ['repid' => $data['repid'], 'last_decisioin' => 1]);
                        $repairContract['hospital_id'] = $hospital_id;
                        $repairContract['repid'] = $data['repid'];
                        $repairContract['supplier_id'] = $companyInfo['offer_company_id'];
                        $repairContract['supplier_name'] = $companyInfo['offer_company'];
                        $repairContract['supplier_contacts'] = $companyInfo['offer_contacts'];
                        $repairContract['supplier_phone'] = $companyInfo['telphone'];
                        $repairContract['contract_amount'] = $companyInfo['total_price'];
                        $repairContract['add_user'] = $companyInfo['adduser'];
                        $repairContract['add_time'] = getHandleDate(time());
                        $count = $this->DB_get_count('repair_contract');
                        $repairContract['contract_num'] = 'WXHT' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
                        $this->insertData('repair_contract', $repairContract);
                    }
                    if ($arr['repair_type'] == C('REPAIR_TYPE_IS_STUDY')) {
                        //自修配件审批通过
                        //获取此维修单待审批的配件
                        $inwareWhere['repid'] = ['EQ', $data['repid']];
                        $inwareWhere['status'] = ['EQ', C('YES_STATUS')];
                        $inwareData = $this->DB_get_all('parts_inware_record_detail', '', $inwareWhere);
                        if ($inwareData) {
                            $detailid = [];
                            foreach ($inwareData as &$inVal) {
                                $detailid[] = $inVal['detailid'];
                            }
                            // 修改库存明细状态
                            $inwareWhere['is_use'] = ['EQ', C('NO_STATUS')];
                            $this->updateData('parts_inware_record_detail', ['is_user' => C('YES_STATUS')], $inwareWhere);
                            // 修改出库明细单状态
                            $outWhere['inware_partsid'] = ['IN', $detailid];
                            $this->updateData('parts_inware_record_detail', ['status' => C('YES_STATUS')], $outWhere);
                        }
                    }
                    //通知审批结果
                    $this->repairResultSms($data, $arr, $departid);
                } elseif ($approveType == C('SCRAP_APPROVE')) {
                    //本次为最后一道审批，且为同意报废的
                    $this->approve_scrap_success_handle($arr);
                    $this->scrapResultSms($data, 1);
                } elseif ($approveType == C('OUTSIDE_APPROVE')) {
                    //本次为最后一道审批，且为同意外调的
                    $this->approve_outside_success_handle($arr);
                    //通知审批结果
                    $this->outsideResultSms($data, $arr, $departid);
                } elseif ($approveType == C('SUBSIDIARY_APPROVE')) {
                    //本次为最后一道审批，且为同意附属设备分配的
                    //修改 分配申请单状态
                    $changeData['approve_status'] = C('STATUS_APPROE_SUCCESS');
                    $changeData['status'] = C('SUBSIDIARY_STATUS_ACCEPTANCE_CHECK');
                    $this->updateData('subsidiary_allot', $changeData, ['allotid' => ['EQ', $arr['allotid']]]);
                    $this->subsidiaryResultSms($data, $arr, $departid);
                }
            }
            if ($data['is_adopt'] == C('STATUS_APPROE_FAIL')) {
                $change_assid = [];
                $change_assid[] = $arr['assid'];
                if ($approveType == C('OUTSIDE_APPROVE')) {
                    //本次为最后一道审批，且为不同意外调的
                    $this->updateData('assets_outside', array('status' => C('OUTSIDE_STATUS_FAIL')), array('outid' => $arr['outid']));
                    $detail = $this->DB_get_all('assets_outside_detail', 'subsidiary_assid', array('outid' => $arr['outid']));
                    if ($detail) {
                        foreach ($detail as &$detailV) {
                            $change_assid[] = $detailV['subsidiary_assid'];
                        }
                    }
                    //不同意，更新设备最新状态为在用
                    $this->updateData('assets_info', array('status' => C('ASSETS_STATUS_USE')), ['assid' => ['IN', $change_assid]]);
                    //通知审批结果
                    $this->outsideResultSms($data, $arr, $departid);
                } elseif ($approveType == C('PURCHASES_PLANS_APPROVE')) {
                    //本次为最后一道审批，且不同意科室上报采购计划
                    $this->purchasePlanApproveResultSms($data, $arr, $departid);
                } elseif ($approveType == C('SUBSIDIARY_APPROVE')) {
                    //本次为最后一道审批，且不同附属设备分配
                    $this->subsidiaryResultSms($data, $arr, $departid);
                } elseif ($approveType == C('PURCHASES_PLANS_APPROVE')) {
                    //本次为最后一道审批，且不同意科室采购申请
                    $this->purchaseApplyResultSms($data, $arr, $departid);
                } elseif ($approveType == C('SCRAP_APPROVE')) {
                    //本次为最后一道审批，且为不同意报废的
                    $this->updateData('assets_scrap', array('approve_status' => C('STATUS_APPROE_FAIL')), array('scrid' => $arr['scrid']));
                    $detail = $this->DB_get_all('assets_scrap_detail', 'subsidiary_assid', array('scrid' => $arr['scrid']));
                    if ($detail) {
                        foreach ($detail as &$detailV) {
                            $change_assid[] = $detailV['subsidiary_assid'];
                        }
                    }
                    //不同意，更新设备最新状态为在用
                    $this->updateData('assets_info', array('status' => C('ASSETS_STATUS_USE')), ['assid' => ['IN', $change_assid]]);
                    $this->scrapResultSms($data, 2);
                } elseif ($approveType == C('SUBSIDIARY_APPROVE')) {
                    //本次为最后一道审批，且为不同意分配申请
                    $changeData['approve_status'] = C('STATUS_APPROE_FAIL');
                    $changeData['status'] = C('SUBSIDIARY_STATUS_FAIL');
                    $this->updateData('subsidiary_allot', $changeData, array('allotid' => $arr['allotid']));
                } elseif ($approveType == C('REPAIR_APPROVE')) {
                    //维修类型的审批
                    if ($arr['repair_type'] == C('REPAIR_TYPE_IS_STUDY')) {
                        //自修配件审批 未通过
                        //获取此维修单待审批 未使用 的配件
                        $inwareWhere['repid'] = ['EQ', $data['repid']];
                        $inwareWhere['status'] = ['EQ', C('YES_STATUS')];
                        $inwareWhere['is_use'] = ['EQ', C('NO_STATUS')];
                        $inwareData = $this->DB_get_all('parts_inware_record_detail', 'detailid,inwareid', $inwareWhere);
                        if ($inwareData) {
                            $detailid = [];
                            $inwareid = [];
                            foreach ($inwareData as &$inVal) {
                                $detailid[] = $inVal['detailid'];
                                $inwareid[$inVal['inwareid']] = $inVal['inwareid'];
                            }
                            sort($inwareid);
                            //复位库存明细单数据
                            $inwareUpdate['repid'] = 0;
                            $inwareUpdate['leader'] = '';
                            $inwareUpdate['status'] = C('NO_STATUS');
                            $this->updateData('parts_inware_record_detail', $inwareUpdate, $inwareWhere);
                            //修改出库单状态
                            $outWhere['repid'] = ['EQ', $data['repid']];
                            $outWhere['status'] = ['EQ', C('YES_STATUS')];
                            $this->updateData('parts_outware_record', ['status' => C('STATUS_APPROE_FAIL')], $outWhere);
                            //修改出库单明细数据
                            $outDetailWhere['inware_partsid'] = ['IN', $detailid];
                            $this->updateData('parts_outware_record_detail', ['status' => C('STATUS_APPROE_FAIL')], $outDetailWhere);
                        }
                        //不同意，更新设备最新状态为在用
                        $this->updateData('assets_info', array('status' => C('ASSETS_STATUS_USE')), array('assid' => $arr['assid']));
                    }
                    //通知最后审批结果
                    $this->repairResultSms($data, $arr, $departid);
                } else {
                    //不同意，更新设备最新状态为在用
                    $this->updateData('assets_info', array('status' => C('ASSETS_STATUS_USE')), array('assid' => $arr['assid']));
                }
                //记录设备变更信息
                $this->updateAllAssetsStatus($change_assid, C('ASSETS_STATUS_USE'), '审批不通过！');
            }
        } else {
            //获取下次审批人  当前level+1
            $approve = $this->check_approve_process($departid, $approve_process_user, $data['process_node_level'] + 1);
            $saveData['current_approver'] = $approve['current_approver'];
            $this->updateData($tableName, $saveData, array($idName => $arr[$idName]));
            $approve_user = $approve['this_current_approver'];
            $this->noticeNextApprovalSms($approveType, $approve_user, $data, $arr, $departid);
        }
        return array('status' => 1, 'msg' => '审批成功！', 'lastSql' => $lastSql);
    }

    //报废审批通过
    public function approve_scrap_success_handle($arr)
    {
        //修改报废单状态
        $this->updateData('assets_scrap', array('approve_status' => C('STATUS_APPROE_SUCCESS')), array('scrid' => $arr['scrid']));
        $all_subsidiaryWhere['main_assid'] = ['EQ', $arr['assid']];
        $all_subsidiaryWhere['status'][0] = 'NOTIN';
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_SCRAP');//报废
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');//外调中
        //获取所有附属设备
        $all_subsidiaryWhere['is_delete'] = '0';
        $all_subsidiaryData = $this->DB_get_all('assets_info', 'assid', $all_subsidiaryWhere);
        $change_assid = [];
        $change_assid[] = $arr['assid'];
        if ($all_subsidiaryData) {
            $all_subsidiary_assid = [];
            foreach ($all_subsidiaryData as $all_sub) {
                $all_subsidiary_assid[] = $all_sub['assid'];
            }
            //获取选中报废的附属设备id
            $detail = $this->DB_get_all('assets_scrap_detail', 'subsidiary_assid', array('scrid' => $arr['scrid']));
            if ($detail) {
                $subsidiary_assid = [];
                foreach ($detail as &$detailV) {
                    $subsidiary_assid[] = $detailV['subsidiary_assid'];
                    $change_assid[] = $detailV['subsidiary_assid'];
                }
                $all_subsidiary_assid = array_diff($all_subsidiary_assid, $subsidiary_assid);
            }
            if ($all_subsidiary_assid) {
                //将未选中的附属设备变成无主
                $diffData['main_assid'] = 0;
                $diffData['main_assets'] = '';
                $diffWhere['assid'] = ['IN', $all_subsidiary_assid];
                $this->updateData('assets_info', $diffData, $diffWhere);
            }
        }
        //更新设备最新状态为已报废
        $this->updateData('assets_info', array('status' => C('ASSETS_STATUS_SCRAP')), ['assid' => ['IN', $change_assid]]);
        //记录设备变更信息
        $this->updateAllAssetsStatus($change_assid, C('ASSETS_STATUS_SCRAP'), '报废审批已通过,设备报废！');
    }

    //外调审批通过
    public function approve_outside_success_handle($arr)
    {
        //修改 外调单状态
        $this->updateData('assets_outside', array('status' => C('OUTSIDE_STATUS_ACCEPTANCE_CHECK')), array('outid' => $arr['outid']));
        $all_subsidiaryWhere['main_assid'] = ['EQ', $arr['assid']];
        $all_subsidiaryWhere['status'][0] = 'NOTIN';
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_SCRAP');//报废
        $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        //获取所有附属设备
        $all_subsidiaryWhere['is_delete'] = '0';
        $all_subsidiaryData = $this->DB_get_all('assets_info', 'assid', $all_subsidiaryWhere);
        $change_assid = [];
        $change_assid[] = $arr['assid'];
        if ($all_subsidiaryData) {
            $all_subsidiary_assid = [];
            foreach ($all_subsidiaryData as $all_sub) {
                $all_subsidiary_assid[] = $all_sub['assid'];
            }
            //获取选中外调的附属设备id
            $detail = $this->DB_get_all('assets_outside_detail', 'subsidiary_assid', array('outid' => $arr['outid']));
            if ($detail) {
                $subsidiary_assid = [];
                foreach ($detail as &$detailV) {
                    $subsidiary_assid[] = $detailV['subsidiary_assid'];
                    $change_assid[] = $detailV['subsidiary_assid'];
                }
                $all_subsidiary_assid = array_diff($all_subsidiary_assid, $subsidiary_assid);
            }
            if ($all_subsidiary_assid) {
                //将未选中的附属设备变成无主
                $diffData['main_assid'] = 0;
                $diffData['main_assets'] = '';
                $diffWhere['assid'] = ['IN', $all_subsidiary_assid];
                $this->updateData('assets_info', $diffData, $diffWhere);
            }
        }
        //更新设备最新状态为已外调
        $this->updateData('assets_info', array('status' => C('ASSETS_STATUS_OUTSIDE')), ['assid' => ['IN', $change_assid]]);
        //记录设备变更信息
        $this->updateAllAssetsStatus($change_assid, C('ASSETS_STATUS_OUTSIDE'), '外调审批已通过,设备外调！');
    }

    //公共更新统计部门和分类方法
    public function updateAssetsNumAndTotalPrice()
    {
        //更新部门表中每个部门的设备数量和总价
        $departCount = $this->DB_get_all('assets_info', 'departid,sum(buy_price) as totalPrice,count(departid) as assetsNum', '', 'departid', '');
        foreach ($departCount as $k => $v) {
            $departData['assetssum'] = $v['assetsNum'];
            $departData['assetsprice'] = $v['totalPrice'];
            $departWhere['departid'] = $v['departid'];
            $this->updateData('department', $departData, $departWhere);
        }
        //更新分类表中每个分类的设备数量和总价
        $cateCount = $this->DB_get_all('assets_info', 'catid,sum(buy_price) as totalPrice,count(catid) as assetsNum', '', 'catid', '');
        foreach ($cateCount as $k => $v) {
            $cateData['assetssum'] = $v['assetsNum'];
            $cateData['assetsprice'] = $v['totalPrice'];
            $cateWhere['catid'] = $v['catid'];
            $this->updateData('category', $cateData, $cateWhere);
        }
        //获取父分类信息
        $cateParent = $this->DB_get_one('category', 'group_concat(catid) as ids', array('parentid' => 0, 'is_delete' => 0));
        if ($cateParent['ids']) {
            $parentcount = $this->DB_get_all('category', 'parentid,group_concat(catid order by catid asc) as ids,count(catid) as childNum,sum(assetssum) as assetsNum,sum(assetsprice) as totalPrice', array('parentid' => array('IN', $cateParent['ids']), 'is_delete' => 0), 'parentid', '');
            foreach ($parentcount as $k => $v) {
                $parentData['assetssum'] = $v['assetsNum'];
                $parentData['assetsprice'] = $v['totalPrice'];
                $parentData['child'] = $v['childNum'];
                $parentData['arrchildid'] = json_encode(explode(',', $v['ids']));
                $parentWhere['catid'] = $v['parentid'];
                $this->updateData('category', $parentData, $parentWhere);
            }
        }
    }


    /**
     * 二维数组排序 按照某一键的值
     * @param $arr array 需要排序的数组
     * @param $keys string 键名
     * @param $type string 排序方式
     * @return array
     */
    function array_sort($arr, $keys, $type = 'ASC')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'ASC') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    /**
     * Notes: 根据医院ID获取对应的科室列表
     * @param $hospital_id int 医院ID
     */
    public function get_departments_by_hospital($hospital_id)
    {
        return $this->DB_get_all('department', 'departid,department', array('hospital_id' => $hospital_id, 'is_delete' => C('NO_STATUS')));
    }

    //获取全部省份
    public function getProvinces()
    {
        $provinces = $this->DB_get_all('base_provinces', 'provinceid,province');
        return $provinces;
    }


    //根据省份获取城市
    public function getCity($provinceid = '')
    {
        if (!$provinceid) {
            $provinceid = I('POST.provinceid');
            if (!$provinceid) {
                die(json_encode(array('status' => 1, 'msg' => '参数缺失')));
            }
        }
        $where['provinceid'] = ['EQ', $provinceid];
        $city = $this->DB_get_all('base_city', 'cityid,city', $where);
        return array('status' => 1, 'msg' => '获取成功', 'result' => $city);
    }

    //根据城市获取市区
    public function getAreas($cityid = '')
    {
        if (!$cityid) {
            $cityid = I('POST.cityid');
            if (!$cityid) {
                die(json_encode(array('status' => 1, 'msg' => '参数缺失')));
            }
        }
        $where['cityid'] = ['EQ', $cityid];
        $area = $this->DB_get_all('base_areas', 'areaid,area', $where);
        return array('status' => 1, 'msg' => '获取成功', 'result' => $area);
    }

    //组合号码
    public function formatPhone($userData)
    {
        $phone = '';
        foreach ($userData as &$one) {
            if ($userData['username'] != session('username')) {
                $phone .= ',' . $one['telephone'];
            }
        }
        $phone = ltrim($phone, ",");
        return $phone;
    }

    /**
     * Notes: 生成二维码图片
     * @return string
     */
    public function createCodePic($string)
    {
        Vendor('phpqrcode.phpqrcode');
        $QRcode = new \QRcode ();
        $value = $string;//二维码内容
        //二维码文件保存地址
        $savePath = './Public/uploads/qrcode/';
        if (!file_exists($savePath)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($savePath, 0777, true);
        }
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 5;//生成图片大小
        //文件名
        $filename = date('YmdHis') . '.png';
        //生成二维码,第二个参数为二维码保存路径
        $QRcode::png($value, $savePath . $filename, $errorCorrectionLevel, $matrixPointSize, 2, true);
        if (file_exists($savePath . $filename)) {
            return $savePath . $filename;
        } else {
            return false;
        }
    }

    //维修审批结果的短信
    public function repairResultSms($data, $repInfo, $departid)
    {
        $settingData = $this->checkSmsIsOpen('Repair');
        if ($settingData) {
            $repInfo['approve_status'] = $data['is_adopt'] == C('STATUS_APPROE_FAIL') ? '未通过' : '通过';
            //有开启短信
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $repInfo['department'] = $departname[$departid]['department'];
            //通知工程师审批结果
            $where = [];
            $where['status'] = C('OPEN_STATUS');
            $where['is_delete'] = C('NO_STATUS');
            $where['username'] = $data['proposer'];
            $userData = $this->DB_get_one('user', 'telephone', $where);
            if ($repInfo['part_total_price'] > 0 && $data['is_adopt'] == C('STATUS_APPROE_SUCCESS')) {
                //产生配件 并且通过审批  通知工程师领取配件
                if ($settingData['repairPartsOut']['status'] == C('OPEN_STATUS') && $userData['telephone']) {
                    $sms = RepairModel::formatSmsContent($settingData['repairPartsOut']['content'], $repInfo);
                    ToolController::sendingSMS($userData['telephone'], $sms, 'Repair', $repInfo['repid']);
                }
            } else {
                //通知工程师审批结果
                if ($settingData['repairApproveOver']['status'] == C('OPEN_STATUS') && $userData['telephone']) {
                    $sms = RepairModel::formatSmsContent($settingData['repairApproveOver']['content'], $repInfo);
                    ToolController::sendingSMS($userData['telephone'], $sms, 'Repair', $repInfo['repid']);
                }
            }
            if ($data['is_adopt'] == C('STATUS_APPROE_FAIL')) {
                //审批未通过 通知报修人
                $where = [];
                $where['status'] = C('OPEN_STATUS');
                $where['is_delete'] = C('NO_STATUS');
                $where['username'] = $repInfo['applicant'];
                $approve_user = $this->DB_get_one('user', 'telephone', $where);
                if ($settingData['repairApproveOverFAIL']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                    $sms = RepairModel::formatSmsContent($settingData['repairApproveOverFAIL']['content'], $repInfo);
                    ToolController::sendingSMS($approve_user['telephone'], $sms, 'Repair', $repInfo['repid']);
                }
            }
        }
    }

    //外调审批结果短信
    public function outsideResultSms($data, $outsideInfo, $departid)
    {
        $settingData = $this->checkSmsIsOpen('Outside');
        if ($settingData) {
            $outsideInfo['approve_status'] = $data['is_adopt'] == C('STATUS_APPROE_FAIL') ? '未通过' : '通过';
            //有开启短信
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $outsideInfo['department'] = $departname[$departid]['department'];
            //通知工程师审批结果
            $where = [];
            $where['status'] = C('OPEN_STATUS');
            $where['is_delete'] = C('NO_STATUS');
            $where['username'] = $data['proposer'];
            $approve_user = $this->DB_get_one('user', 'telephone', $where);
            if ($settingData['outsideApproveOver']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                $sms = AssetsOutsideModel::formatSmsContent($settingData['outsideApproveOver']['content'], $outsideInfo);
                ToolController::sendingSMS($approve_user['telephone'], $sms, 'Outside', $outsideInfo['outid']);
            }
        }
    }

    //附属设备审批结果短信
    public function subsidiaryResultSms($data, $assInfo, $departid)
    {
        $settingData = $this->checkSmsIsOpen('Subsidiary');
        if ($settingData) {
            $assInfo['approve_status'] = $data['is_adopt'] == C('STATUS_APPROE_FAIL') ? '未通过' : '通过';
            //有开启短信
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $assInfo['department'] = $departname[$departid]['department'];
            //通知申请人 审批结果
            if ($data['is_adopt'] == C('STATUS_APPROE_FAIL')) {
                $where = [];
                $where['status'] = C('OPEN_STATUS');
                $where['is_delete'] = C('NO_STATUS');
                $where['username'] = $data['proposer'];
                $approve_user = $this->DB_get_one('user', 'telephone', $where);
                if ($settingData['subsidiaryApproveOver']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                    $sms = SubsidiaryModel::formatSmsContent($settingData['subsidiaryApproveOver']['content'], $assInfo);
                    ToolController::sendingSMS($approve_user['telephone'], $sms, 'Subsidiary', $assInfo['allotid']);
                }
            } else {
                $dataUser = ToolController::getUser('subsidiaryCheck', $assInfo['main_departid']);
                if ($settingData['subsidiaryCheck']['status'] == C('OPEN_STATUS') && $dataUser) {
                    $phone = $this->formatPhone($dataUser);
                    $sms = SubsidiaryModel::formatSmsContent($settingData['subsidiaryCheck']['content'], $assInfo);
                    ToolController::sendingSMS($phone, $sms, 'Subsidiary', $assInfo['allotid']);
                }
            }

        }
    }

    //科室上报采购计划审批结果短信
    public function purchasePlanApproveResultSms($data, $plansInfo, $departid)
    {
        $settingData = $this->checkSmsIsOpen('Purchases');
        if ($settingData) {
            $plansInfo['approve_status'] = $data['is_adopt'] == C('STATUS_APPROE_FAIL') ? '未通过' : '通过';
            //有开启短信
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $plansInfo['department'] = $departname[$departid]['department'];
            //通知工程师审批结果
            $where = [];
            $where['status'] = C('OPEN_STATUS');
            $where['is_delete'] = C('NO_STATUS');
            $where['username'] = $plansInfo['apply_user'];
            $approve_user = $this->DB_get_one('user', 'telephone', $where);
            if ($settingData['purchasePlanApproveOver']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                $sms = PurchasesModel::formatSmsContent($settingData['purchasePlanApproveOver']['content'], $plansInfo);
                ToolController::sendingSMS($approve_user['telephone'], $sms, 'Purchases', $plansInfo['plans_id']);
            }
        }
    }

    //科室采购计划审批结果短信
    public function purchaseApplyResultSms($data, $plansInfo, $departid)
    {
        $settingData = $this->checkSmsIsOpen('Purchases');
        if ($settingData) {
            $plansInfo['approve_status'] = $data['is_adopt'] == C('STATUS_APPROE_FAIL') ? '未通过' : '通过';
            //有开启短信
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $plansInfo['department'] = $departname[$departid]['department'];
            //通知申请人审批结果
            $where = [];
            $where['status'] = C('OPEN_STATUS');
            $where['is_delete'] = C('NO_STATUS');
            $where['username'] = $data['proposer'];
            $approve_user = $this->DB_get_one('user', 'telephone', $where);
            if ($settingData['approveApplyOver']['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                $sms = PurchasesModel::formatSmsContent($settingData['approveApplyOver']['content'], $plansInfo);
                ToolController::sendingSMS($approve_user['telephone'], $sms, 'Purchases', $plansInfo['plans_id']);
            }
        }
    }


    //通知下次审批人短信
    public function noticeNextApprovalSms($approveType, $approve_user, $data, $arr, $departid)
    {
        $model = '';
        $thiModel = '';
        $id = 0;
        switch ($approveType) {
            case C('REPAIR_APPROVE'):
                //维修
                $model = 'Repair';
                $id = $arr['repid'];
                $thiModel = new RepairModel();
                break;
            case C('TRANSFER_APPROVE'):
                //转科
                $model = 'Transfer';
                $id = $arr['atid'];
                $thiModel = new AssetsTransferModel();
                break;
            case C('SCRAP_APPROVE'):
                //报废
                $model = 'scrap';
                $id = $arr['scrid'];
                $thiModel = new AssetsScrapModel();
                break;
            case C('OUTSIDE_APPROVE'):
                //外调
                $model = 'Outside';
                $id = $arr['outid'];
                $thiModel = new AssetsOutsideModel();
                break;
            case C('PURCHASES_PLANS_APPROVE'):
                //采购计划上报
                $model = 'Purchases';
                $id = $arr['plans_id'];
                $thiModel = new PurchasesModel();
                break;
            case C('DEPART_APPLY_APPROVE'):
                //科室申请
                $model = 'Purchases';
                $id = $arr['apply_id'];
                $thiModel = new PurchasesModel();
                break;
            case C('SUBSIDIARY_APPROVE'):
                //附属设备分配
                $model = 'Subsidiary';
                $id = $arr['allotid'];
                $thiModel = new SubsidiaryModel();
                break;
        }
        $settingData = $this->checkSmsIsOpen($model);
        if ($settingData && $thiModel) {
            $arr['approve_status'] = $data['is_adopt'] == C('STATUS_APPROE_FAIL') ? '未通过' : '通过';
            //有开启短信
            $departname = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $arr['department'] = $departname[$departid]['department'];
            $where = [];
            $where['status'] = C('OPEN_STATUS');
            $where['is_delete'] = C('NO_STATUS');
            $where['username'] = $approve_user;
            $approve_user = $this->DB_get_one('user', 'telephone', $where);
            switch ($approveType) {
                case C('PURCHASES_PLANS_APPROVE'):
                    //采购计划上报
                    $settingData = $settingData['purchasePlanApprove'];
                    break;
                case C('DEPART_APPLY_APPROVE'):
                    //科室采购申请
                    $settingData = $settingData['purchasePlanApprove'];
                    break;
                default:
                    $settingData = $settingData['doApprove'];
                    break;
            }
            if ($settingData['status'] == C('OPEN_STATUS') && $approve_user['telephone']) {
                $sms = $thiModel->formatSmsContent($settingData['content'], $arr);
                ToolController::sendingSMS($approve_user['telephone'], $sms, $model, $id);
            }
        }
    }

    /**报废审批结果的短信
     * @param $data
     * @param $status 1通过2不通过
     */
    public function scrapResultSms($data, $status)
    {
        //==========================================短信 START==========================================
        $scrapModel = new AssetsScrapModel();
        $settingData = $this->checkSmsIsOpen('Scrap');
        if ($settingData) {
            //有开启短信
            $scrapInfo = $this->DB_get_one('assets_scrap', 'scrapnum,assid,apply_user', ['scrid' => $data['scrapid']]);
            $asInfo = $this->DB_get_one('assets_info', 'assnum,assets', ['assid' => $scrapInfo['assid'], 'is_delete' => '0']);
            $smsData['assnum'] = $asInfo['assnum'];
            $smsData['assets'] = $asInfo['assets'];
            $smsData['scrap_num'] = $scrapInfo['scrapnum'];
            if ($status == 1) {
                $smsData['approve_status'] = '通过';
            } else {
                $smsData['approve_status'] = '不通过';
            }
            $ToolMod = new ToolController();
            $telephone = $this->DB_get_one('user', 'telephone', array('username' => $scrapInfo['apply_user']));
            $sms = $scrapModel->formatSmsContent($settingData['approveScrapStatus']['content'], $smsData);
            $ToolMod->sendingSMS($telephone['telephone'], $sms, 'Scrap', $data['scrapid']);
            //==========================================短信 END==========================================
        }
    }

    /**
     * 计算占比 避免出现总和加起来不等于100%的情况
     * @param array $array 数量的数组
     * @return array 占比的数组
     */
    public function calculationDutyRatio($array)
    {
//        $old_array = $array;
//        if (array_sum($array)) {
//            $total = array_sum($array);
//            array_walk($array, function (&$item, $key, $prefix) {
//                $item = round($item * 10000 / $prefix);
//            }, $total);
//            if ($d = (10000 - array_sum($array))) $array[rand(0, count($array) - 1)] += $d;
//        }
//        foreach ($array as &$one) {
//            if ($one < 0) {
//                //如果统计出来的占比<0 重新运行
//                $array = $this->calculationDutyRatio($old_array);
//            } else {
//                if ($one != 0) {
//                    $one = sprintf("%.2f", $one / 100);
//                }
//            }
//        }


        if (array_sum($array)) {
            $total = array_sum($array);
            array_walk($array, function (&$item, $key, $prefix) {
                $item = $item * 10000 / $prefix;
            }, $total);
            if ($d = (10000 - array_sum($array))) $array[rand(0, count($array) - 1)] += $d;
        }
        foreach ($array as &$one) {
            $one = sprintf("%.2f", $one / 100);
        }
        return $array;
    }

    /**
     * 分组操作
     * @param array $data 需要分组的数组
     * @param string $fieldName 已此字段作为分组条件
     * @param boolean $sort 是否需要排序
     * @param int $sort_order 规定排列顺序
     * @param int $sort_type 规定排序类型
     * @return array
     * */
    function my_array_multisort($data, $fieldName, $sort = false, $sort_order = SORT_DESC, $sort_type = SORT_NUMERIC)
    {
        $departmentData = $this->returnRepeat($data, $fieldName);
        foreach ($departmentData as $val) {
            $key_arrays[] = $val['sum'];
        }
        if ($sort) {
            array_multisort($key_arrays, $sort_order, $sort_type, $departmentData);
        }
        $list = [];
        foreach ($departmentData as &$value) {
            foreach ($data as &$value2) {
                if ($value[$fieldName] == $value2[$fieldName]) {
                    $list[] = $value2;
                }
            }
        }
        return $list;
    }


    /**
     * 统计指定字段重复数量
     * @param array $list 需要统计的数组
     * @param string $fieldName 需要统计的字段
     * @return array
     * */
    public function returnRepeat($list, $fieldName)
    {
        $newArr = [];
        foreach ($list as $key => $value) {
            if ($newArr) {
                foreach ($newArr as $key2 => $value2) {
                    if ($value[$fieldName] == $value2[$fieldName]) {
                        $newArr[$key2]['sum']++;
                    } else {
                        //不存在 但是避免直接在末尾重复插入，做多一次循环，验证是否已存在
                        $Repeat = false;
                        foreach ($newArr as $key3 => $value3) {
                            if ($value[$fieldName] == $value3[$fieldName]) {
                                $Repeat = true;
                                break;
                            }
                        }
                        if (!$Repeat) {
                            $newArr[$key][$fieldName] = $value[$fieldName];
                            $newArr[$key]['sum'] = 1;
                        }
                    }
                }
            } else {
                $newArr[$key][$fieldName] = $value[$fieldName];
                $newArr[$key]['sum'] = 1;
            }
        }
        return $newArr;
    }

    //获取要发送微信消息的用户openid
    public function getToUser($uid, $departid, $moduleName, $controllerName, $actionName)
    {
        //查询对应操作的menuid
        $menu_where['status'] = 1;
        $menu_where['name'] = array(array('eq', $moduleName), array('eq', $controllerName), array('eq', $actionName), 'or');
        $menus = $this->DB_get_all('menu', 'menuid,name,title,parentid', $menu_where, '', 'parentid asc');
        $menuid = 0;
        foreach ($menus as $k => $v) {
            if ($v['parentid'] == 0 && $v['name'] == $moduleName) {
                unset($menus[$k]);
                foreach ($menus as $k1 => $v1) {
                    if ($v1['parentid'] == $v['menuid'] && $v1['name'] == $controllerName) {
                        unset($menus[$k1]);
                        foreach ($menus as $k2 => $v2) {
                            if ($v2['parentid'] == $v1['menuid'] && $v2['name'] == $actionName) {
                                $menuid = $v2['menuid'];
                            }
                        }
                    }
                }
            }
        }
        if (!$menuid) {
            return array();
        }
        //查询对应menuid下的roleid集合
        $role_where['A.menuid'] = $menuid;
        $role_where['B.is_delete'] = 0;
        $role_where['B.is_default'] = 0;
        $role_where['B.hospital_id'] = session('current_hospitalid');
        $role = $this->DB_get_one_join('role_menu', 'A', 'group_concat(A.roleid) as roleids', 'LEFT JOIN sb_role AS B ON A.roleid = B.roleid', $role_where);
        //\Think\Log::write('sql======'.M()->getLastSql());
        if (!$role['roleids']) {
            return array();
        }
        //查询对应部门对应roleid下的用户
        $join[0] = " LEFT JOIN sb_user_department AS B ON A.userid = B.userid";
        $join[1] = " LEFT JOIN sb_user AS C ON A.userid = C.userid";
        $fields = "A.*,B.departid,C.username,C.openid";
        $where['A.roleid'] = array('in', $role['roleids']);
        $where['A.userid'] = array('neq', $uid);
        $where['B.departid'] = $departid;
        $res = $this->DB_get_all_join('user_role', 'A', $fields, $join, $where, '', '', '');
        return $res;
    }

    public function get_approves_progress($data, $app_id, $data_id)
    {
        if ($data['all_approver']) {
            //需要审批
            //查询审批记录
            $apps = $this->DB_get_all('approve', $app_id . ',group_concat(is_adopt ORDER BY apprid ASC) as is_adopt,group_concat(approve_time ORDER BY apprid ASC) as approve_time', array('is_delete' => 0, $app_id => $data[$data_id]), $app_id);
            //查询系统超级用户
            $super = $this->DB_get_all('user', 'username', array('is_super' => 1));
            $data['all_approver'] = str_replace('/', '', $data['all_approver']);
            foreach ($super as $user) {
                $search = ',' . $user['username'];
                $data['all_approver'] = str_replace($search, '', $data['all_approver']);
            }
            $yuan_icon = '&#xe63f;';//圆形
            $dui_icon = '&#xe605;';//通过
            $cuo_icon = '&#x1006;';//不通过

            //颜色
            $gray_color = 'unexecutedColor';//灰色
            $pass_color = 'executeddColor';//绿色
            $no_pass_color = 'endColor';//红色

            $all_approver = explode(',', $data['all_approver']);
            $data['all_approver'] =$all_approver;
            $html = '';
            $html = '<ul class="timeLineList">';
            foreach ($all_approver as $username) {
                $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $gray_color . '">' . $yuan_icon . '</i><div class="timeLine ' . $gray_color . 'Bg"></div><span class="timeLineTitle ' . $gray_color . '">' . $username . '</span><span class="timeLineDate ' . $gray_color . '">-</span></div></li>';
            }
            $html .= '</ul>';
            $data['app_user_status'] = $html;
            $app_user_num = -1;
            //审批状态
            foreach ($apps as $kp => $vp) {
                if ($vp[$app_id] == $data[$data_id]) {
                    $is_adopt = explode(',', $vp['is_adopt']);
                    $approve_time = explode(',', $vp['approve_time']);
                    $data['app_user_status'] = '';
                    $html = '';
                    $html = '<ul class="timeLineList">';
                    foreach ($all_approver as $uk => $username) {
                        if ($is_adopt[$uk]) {
                            if ($is_adopt[$uk] == 1) {
                                $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $pass_color . '">' . $dui_icon . '</i><div class="timeLine ' . $pass_color . 'Bg"></div><span class="timeLineTitle ' . $pass_color . '">' . $username . '</span><span class="timeLineDate ' . $pass_color . '">' . date('Y-m-d', $approve_time[$uk]) . '</span></div></li>';
                                $app_user_num =$app_user_num+1; 
                            } else {
                                $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $no_pass_color . '">' . $cuo_icon . '</i><div class="timeLine ' . $no_pass_color . 'Bg"></div><span class="timeLineTitle ' . $no_pass_color . '">' . $username . '</span><span class="timeLineDate ' . $no_pass_color . '">' . date('Y-m-d', $approve_time[$uk]) . '</span></div></li>';
                            }
                        } else {
                            $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $gray_color . '">' . $yuan_icon . '</i><div class="timeLine ' . $gray_color . 'Bg"></div><span class="timeLineTitle ' . $gray_color . '">' . $username . '</span><span class="timeLineDate ' . $gray_color . '">-</span></div></li>';
                        }
                    }
                    $html .= '</ul>';
                    $data['app_user_status'] = $html;
                }
            }
        } else {
            $data['app_user_status'] = '';
        }
        $data['app_user_num'] =$app_user_num;
        return $data;
    }

    /**
     * @return array 列表返回没有数据
     */
    protected static function noData()
    {
        return ['status' => 1, 'total' => 0];
    }

    /**
     * @param $url string 仅接受以逗号分隔的url地址 sb_assets_info表pic_url字段的数据
     * @return array 返回整理过图片地址的图片数组
     */
    protected static function getPicArr($url)
    {
        //域名前缀
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $pic_url = explode(',', $url);
        $picarr = [];
        foreach ($pic_url as $v) {
            $picarr[] = $protocol . C('HTTP_HOST') . '/Public/uploads/' . C('UPLOAD_DIR_ASSETS_NAME') . '/' . $v;
        }
        return $picarr;
    }

    /**
     * @param $openid string 要发送的用户openid
     * @param $card_data arr 要发送的卡片信息
     */
    public function send_feishu_card_msg($openid,$card_data)
    {
        $wxModel = new \Fs\Model\WxAccessTokenModel();
        $tenant_access_token = $wxModel->getAccessToken('tenant_access_token');
        $url = 'https://open.feishu.cn/open-apis/message/v4/send/';
        $header[] = 'Authorization:Bearer '.$tenant_access_token;
        $header[] = 'Content-Type:application/json; charset=utf-8';
        $par['open_id'] = $openid;
        $par['msg_type'] = 'interactive';
        $par['card'] = $card_data;
        $res = dcurl($url,json_encode($par),$header);
        $res = json_decode($res,true);
        $insert_data['openid'] = $openid;
        $insert_data['code'] = $res['code'];
        if($res['code'] != 0){
            $insert_data['msg'] = $res['msg'];
        }else{
            unset($res['code']);
            $insert_data['msg'] = json_encode($res);
        }
        $insert_data['send_time'] = date('Y-m-d H:i:s');
        //记录发送情况
        $this->insertData('feishu_msg_log',$insert_data);
    }
}
