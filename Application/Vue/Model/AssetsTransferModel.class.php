<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/3/14
 * Time: 11:00
 */

namespace Vue\Model;

class AssetsTransferModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'assets_transfer';
    private $MODULE = 'Assets';
    private $Controller = 'Transfer';

    public function get_transfer_lists()
    {
        $departids = session('departid');
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('get.order') ? I('get.order') : 'desc';
        $sort = I('get.sort') ? I('get.sort') : 'status';
        $search = I('get.search');
        $catid = I('get.catid');
        $departid = I('get.departid');
        if (!$departids) {
            return parent::noData();
        }
        $where['A.departid'] = ['in', $departids];
        $where['A.status'][0] = 'IN';
        $where['A.status'][1][] = C('ASSETS_STATUS_USE');
        $where['A.status'][1][] = C('ASSETS_STATUS_TRANSFER_ON');
        $where['A.is_subsidiary'] = C('NO_STATUS');
        $where['A.hospital_id'] = session('current_hospitalid');
        if ($search) {
            switch ($search) {
                case '在用':
                    $where['A.status'] = 0;
                    break;
                case '转科中':
                    $where['A.status'] = 6;
                    break;
                default:
                    $map['A.assets'] = array('like', '%' . $search . '%');
                    $map['A.assnum'] = array('like', '%' . $search . '%');
                    $map['A.model'] = array('like', '%' . $search . '%');
                    $map['A.brand'] = array('like', '%' . $search . '%');
                    $map['_logic'] = 'or';
                    $where['_complex'] = $map;
            }

        }
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        if ($catid) {
            //查询是否父分类
            $parentId = $catname[$catid]['parentid'];
            if ($parentId == 0) {
                //是父类 去查询是否有子类
                $allcatid = array_keys(array_filter($catname, function ($v) use ($catid) {
                    return $v['parentid'] == $catid;
                }));
                if (empty($allcatid)) {
                    $where['A.catid'] = $catid;
                } else {
                    $allcatid[] = $catid;
                    $where['A.catid'] = ['in', $allcatid];
                }
            } else {
                $where['A.catid'] = $catid;
            }
        }
        if ($departid) {
            $where['A.departid'] = $departid;
        }
        $where['A.is_delete'] = 0;
        $fields = "A.assid,A.assets,A.assnum,A.catid,A.departid,A.model,A.status,A.brand,A.pic_url,B.department,B.assetssum,C.retrial_status";
        $join[0] = "LEFT JOIN sb_department AS B ON A.departid = B.departid";
        $join[1] = "LEFT JOIN sb_assets_transfer AS C ON A.assid = C.assid";
        $total = $this->DB_get_count_join('assets_info', 'A', $join, $where, '');
        $assets = $this->DB_get_all_join('assets_info', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$assets) {
            return parent::noData();
        }
        $assid = [];
        foreach ($assets as &$value) {
            $assid[] = $value['assid'];
        }
        //筛选计量计划中的设备
        $meteringWhere['status'] = ['EQ', C('YES_STATUS')];
        $meteringWhere['hospital_id'] = session('current_hospitalid');
        $metering = $this->DB_get_all('metering_plan', 'assid', $meteringWhere);
        $meterassids = [];
        foreach ($metering as $k => $v) {
            $meterassids[] = $v['assid'];
        }
        //筛选借调中的设备
        $borrowWhere['status'] = ['IN', [C('BORROW_STATUS_APPROVE'), C('BORROW_STATUS_BORROW_IN'), C('BORROW_STATUS_GIVE_BACK')]];
        $borrowWhere['assid'] = ['IN', $assid];
        $borrow = $this->DB_get_all('assets_borrow', 'assid,borid', $borrowWhere);
        $borrowAssid = [];
        if ($borrow) {
            $borid = [];
            foreach ($borrow as &$borrowV) {
                $borid[] = $borrowV['borid'];
                $borrowAssid[$borrowV['assid']] = true;
            }
        }
        //查询当前用户是否有权限申请转科
        $menuData = get_menu($this->MODULE, $this->Controller, 'add');
        foreach ($assets as &$v) {
            if ($v['pic_url']) {
                $picArr = parent::getPicArr($v['pic_url']);
                $v['pic_url'] = $picArr[0];
            }
            $v['category'] = $catname[$v['catid']]['category'];
            switch ($v['status']) {
                case C('ASSETS_STATUS_USE'):
                    //在用
                    $v['status_name'] = '<span style="color:#009688;">' . C('ASSETS_STATUS_USE_NAME') . '</span>';
                    if (in_array($v['assid'], $meterassids)) {
                        //计量计划中
                        $v['urlName'] = '计量计划中';
                    } elseif ($borrowAssid[$v['assid']]) {
                        $v['urlName'] = '借调中';
                    } else {
                        if ($menuData) {
                            $v['url'] = $menuData['actionurl'];
                            $v['urlName'] = '申请转科';
                        }
                    }
                    break;
                case C('ASSETS_STATUS_TRANSFER_ON'):
                    if ($v['retrial_status']==1) {
                        $v['status_name'] = '<span style="color:#red;">审批不通过</span>';
                        $v['urlName'] = '重新申请';
                        $v['url'] = $menuData['actionurl'];
                    }else{
                    $v['status_name'] = '<span style="color:#1E9FFF;">' . C('ASSETS_STATUS_TRANSFER_ON_NAME') . '</span>';
                    $v['urlName'] = '转科中';
                    }
                    break;
                default:
                    $v['status_name'] = '未知状态';
                    break;
            }
        }
        return [
            'page' => (int)$page,
            'pages' => (int)ceil($total / $limit),
            'total' => $total,
            'rows' => $assets,
            'status' => 1
        ];
    }

    /**
     * Notes:查询设备信息
     * @param $assnum string 设备编码
     */
    public function get_assets_info($assnum)
    {
        //加载缓存
        $catname = array();
        $departname = array();
        $baseSetting = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $assets = $this->DB_get_one('assets_info', '', array('assnum' => $assnum, 'is_delete' => '0'));
        if (!$assets) {
            $assets = $this->DB_get_one('assets_info', '', array('assorignum' => $assnum, 'is_delete' => '0'));
        }
        if (!$assets) {
            $assets = $this->DB_get_one('assets_info', '', array('assorignum_spare' => $assnum, 'is_delete' => '0'));
        }
        //第一分类信息
        //$this->DB_get_one('assets_factory','factory');
        $assets['cate_name'] = $catname[$assets['catid']]['category'];
        if ($assets['is_subsidiary'] == C('YES_STATUS')) {
            //附属设备 获取附属设备辅助分类
            $assets['helpcat'] = $baseSetting['assets']['acin_category']['value'][$assets['subsidiary_helpcatid']];
        } else {
            //主设备   获取主设备辅助分类
            $assets['helpcat'] = $baseSetting['assets']['assets_helpcat']['value'][$assets['helpcatid']];
        }

        if ($assets['status'] == C('ASSETS_STATUS_USE')) {
            $assets['statusName'] = C('ASSETS_STATUS_USE_NAME');
        } elseif ($assets['status'] == C('ASSETS_STATUS_USE')) {
            $assets['statusName'] = C('ASSETS_STATUS_USE_NAME');
        } elseif ($assets['status'] == C('ASSETS_STATUS_USE')) {
            $assets['statusName'] = C('ASSETS_STATUS_USE_NAME');
        } else {
            $assets['statusName'] = '无';
        }
        $assets['finance'] = $baseSetting['assets']['assets_finance']['value'][$assets['financeid']];
        $assets['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$assets['capitalfrom']];
        $assets['assfrom'] = $baseSetting['assets']['assets_assfrom']['value'][$assets['assfromid']];
        $assets['department'] = $departname[$assets['departid']]['department'];

        $assets['factorydate'] = HandleEmptyNull($assets['factorydate']);
        $assets['opendate'] = HandleEmptyNull($assets['opendate']);
        $assets['storage_date'] = HandleEmptyNull($assets['storage_date']);
        $assets['guarantee_date'] = HandleEmptyNull($assets['guarantee_date']);

        if ($assets['adddate'] == 0) {
            $assets['adddate'] = '';
        } else {
            $assets['adddate'] = date('Y-m-d', $assets['adddate']);
        }
        if ($assets['editdate'] == 0) {
            $assets['editdate'] = '';
        } else {
            $assets['editdate'] = date('Y-m-d', $assets['editdate']);
        }
        if ($assets['is_firstaid'] == C('YES_STATUS')) {
            $assets['type'] = C('ASSETS_FIRST_CODE_YES_NAME');
        }
        if ($assets['is_special'] == C('YES_STATUS')) {
            $assets['type'] .= '、' . C('ASSETS_SPEC_CODE_YES_NAME');
        }
        if ($assets['is_metering'] == C('YES_STATUS')) {
            $assets['type'] .= '、' . C('ASSETS_METER_CODE_YES_NAME');
        }
        if ($assets['is_qualityAssets'] == C('YES_STATUS')) {
            $assets['type'] .= '、' . C('ASSETS_QUALITY_CODE_YES_NAME');
        }
        if ($assets['is_benefit'] == C('YES_STATUS')) {
            $assets['type'] .= '、' . C('ASSETS_BENEFIT_CODE_YES_NAME');
        }
        if ($assets['is_lifesupport'] == C('YES_STATUS')) {
            $assets['type'] .= '、' . C('ASSETS_LIFE_SUPPORT_NAME');
        }
        if ($assets['depreciation_method'] == 1) {
            $assets['depreciation_method_name'] = '平均折旧法';
        } elseif ($assets['depreciation_method'] == 2) {
            $assets['depreciation_method_name'] = '工作量法';
        } elseif ($assets['depreciation_method'] == 3) {
            $assets['depreciation_method_name'] = '加速折旧法';
        } else {
            $assets['depreciation_method_name'] = '';
        }
        $assets['type'] = trim($assets['type'], '、');
        $assets['expected_life'] = $assets['expected_life'] > 0 ? $assets['expected_life'] : '';
        return $assets;
    }

    //获取转科流程记录
    public function get_transfer_progress()
    {
        $departids = session('departid');
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('get.order') ? I('get.order') : 'desc';
        $sort = I('get.sort');
        $search = I('get.search');
        $hospital_id = session('current_hospitalid');
        if (!$departids) {
            return parent::noData();
        }
        switch ($sort) {
            case 'check_time':
                $sort = 'A.check_time';
                break;
            case 'department':
                $sort = 'C.department';
                break;
            default:
                $sort = 'A.applicant_time';
                break;
        }
        $where['B.departid'] = array('IN', $departids);
        $where['B.hospital_id'] = $hospital_id;
        $where['B.status'] = 6;
        if ($search) {
            $map['B.assets'] = ['like', "%$search%"];
            $map['B.assnum'] = ['like', "%$search%"];
            $map['B.model'] = ['like', "%$search%"];
            $map['B.brand'] = ['like', "%$search%"];
            $map['C.department'] = ['like', "%$search%"];
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $asModel = new AssetsInfoModel();
        $where['B.is_delete'] = 0;
        //根据条件统计符合要求的数量
        $join[0] = ' LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $join[1] = ' LEFT JOIN sb_department AS C ON A.tranout_departid = C.departid';
        $total = $asModel->DB_get_count_join('assets_transfer', 'A', $join, $where);
        $fields = 'A.*,B.assnum,B.assorignum,B.assets,B.model,B.catid';
        $asArr = $asModel->DB_get_all_join('assets_transfer', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asArr) {
            return parent::noData();
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($asArr as $k => $v) {
            $asArr[$k]['applicant_time'] = date('Y/m/d H:i',strtotime($v['applicant_time']));
            $asArr[$k]['tranout_depart_name'] = $departname[$v['tranout_departid']]['department'];
            $asArr[$k]['tranin_depart_name'] = $departname[$v['tranin_departid']]['department'];
            if ($v['is_check'] == C('TRANSFER_IS_CHECK_ADOPT')) {
                //已验收
                $asArr[$k]['show_status_name'] = '验收通过';
                $asArr[$k]['type'] = 'success';
            } elseif ($v['is_check'] == C('TRANSFER_IS_CHECK_NOT_THROUGH')) {
                //验收不通过
                $asArr[$k]['show_status_name'] = '验收不通过';
                $asArr[$k]['type'] = '';
            } elseif ($v['is_check'] == C('TRANSFER_IS_NOTCHECK')) {
                //待验收转态，判断审批情况
                if ($v['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                    //审批通过
                    $asArr[$k]['show_status_name'] = '待验收';
                    $asArr[$k]['type'] = 'primary';
                } elseif ($v['approve_status'] == C('STATUS_APPROE_FAIL')) {
                    //审批不通过
                    $asArr[$k]['show_status_name'] = '审批不通过';
                    $asArr[$k]['type'] = 'danger';
                } elseif ($v['approve_status'] == C('APPROVE_STATUS')) {
                    //审批中
                    $asArr[$k]['show_status_name'] = '待审批';
                    $asArr[$k]['type'] = 'warning';
                }
            }
        }
        return [
            'page' => (int)$page,
            'pages' => (int)ceil($total / $limit),
            'total' => $total,
            'rows' => $asArr,
            'status' => 1
        ];
    }

    /*
     *  转科详细流程
     */
    public function get_progress_detail($transferInfo, $approves)
    {
        $line = $applicant = $approve = $check = [];

        //转科申请
        $applicant['statusName'] = '转科申请';
        $applicant['date'] = $transferInfo['applicant_time'];
        $applicant['user'] = $transferInfo['applicant_user'];
        $applicant['text'] = '【'.$applicant['user'].'】提交了设备转科申请';
        $line[] = $applicant;

        if ($transferInfo['approve_status'] != -1) {
            //转科审批
            foreach ($approves as &$one) {
                $approve['statusName'] = '转科审批';
                $approve['date'] = getHandleDate($one['approve_time']);
                $approve['user'] = $one['approver'];
                $one['is_adopt'] = $one['is_adopt'] == C('REPAIR_IS_CHECK_ADOPT') ? '<span style="color:green;">通过</span>' : '<span style="color:red;">未通过</span>';
                $approve['text'] = '【'.$approve['user'].'】对转科设备进行审核，审批结果：' . $one['is_adopt'];
                $line[] = $approve;
            }
        }
        //转科验收
        if ($transferInfo['is_check'] != 0) {
            $check['statusName'] = '转科验收';
            $check['date'] = $transferInfo['check_time'];
            $check['user'] = $transferInfo['check_user'];
            $res = $transferInfo['is_check'] == 1 ? '<span style="color:green;">通过</span>' : '<span style="color:red;">未通过</span>';
            $check['text'] = '【'.$check['user'].'】对转科设备进行验收，验收结果：' . $res;
            $line[] = $check;
        }
        $user = [];
        foreach ($line as $k => $v) {
            $user[] = $v['user'];
        }
        $userModel = M('user');
        $where['username'] = array('in', $user);
        $user_phone = $userModel->where($where)->getField('username,telephone');
        foreach ($line as $k => $v) {
            $line[$k]['telephone'] = $user_phone[$v['user']];
        }
        return $line;
    }

    /*
     * 获取转科审批列表
     */
    public function get_transfer_examine()
    {
        //查询是否有审批权限
        $menu = get_menu('Assets', 'Transfer', 'examine');
        if (!$menu) {
            $result['msg'] = '对不起，您没有获取转科审批列表的权限！';
            $result['rows'] = [];
            $result['total'] = 0;
            $result['status'] = -1;
            return $result;
        }
        $order = I('post.order') ? I('post.order') : 'DESC';
        $sort = I('post.sort');
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $where['A.approve_status'] = 0;
        $where['B.hospital_id'] = $hospital_id;
        if (!$departids) {
            $result['msg'] = '暂未无分配管理科室，请联系管理员设置！';
            $result['rows'] = [];
            $result['total'] = 0;
            $result['status'] = -1;
        }
        $where['A.all_approver'] = array('like', '%/' . session('username') . '/%');
        $map['A.tranout_departid'] = array('in', $departids);
        $map['A.tranin_departid'] = array('in', $departids);
        $map['_logic'] = 'or';
        $where['_complex'] = $map;
        switch ($sort) {
            case 'applicant_time':
                $sort = 'A.applicant_time';
                break;
            case 'department':
                $sort = 'C.department';
                break;
            default:
                $sort = 'A.applicant_time';
                break;
        }
        //根据条件统计符合要求的数量
        $where['B.is_delete'] = '0';
        $join[0] = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $join[1] = 'LEFT JOIN sb_department AS C ON B.departid = C.departid';
        $total = $this->DB_get_count_join('assets_transfer', 'A', $join, $where);
        $fields = 'A.*,B.assnum,B.assets,B.model';
        $asArr = $this->DB_get_all_join('assets_transfer', 'A', $fields, $join, $where, '', $sort . ' ' . $order, '');
        if (!$asArr) {
            $result['msg'] = '暂无需处理的转科审批流程';
            $result['rows'] = [];
            $result['total'] = 0;
            $result['status'] = 1;
            return $result;
        }
        //查询当前用户是否有权审批
        $canApproval = get_menu('Assets', 'Transfer', 'approval');

        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($asArr as $k => $v) {
            $asArr[$k]['tranout_depart_name'] = $departname[$v['tranout_departid']]['department'];
            $asArr[$k]['tranin_depart_name'] = $departname[$v['tranin_departid']]['department'];
            $asArr[$k]['applicant_time'] = date('Y-m-d H:i', strtotime($v['applicant_time']));
            $asArr[$k]['approve_status'] = (int)$v['approve_status'];
            if ($v['current_approver']) {
                $current_approver = explode(',', $v['current_approver']);
                $current_approver_arr = [];
                foreach ($current_approver as &$current_approver_value) {
                    $current_approver_arr[$current_approver_value] = true;
                }
                if ($current_approver_arr[session('username')]) {
                    $asArr[$k]['Sort'] = 1;
                    if ($canApproval) {
                        $asArr[$k]['btn'] = "审批";
                    } else {
                        $asArr[$k]['btn'] = "待审批";
                    }
                } else {
                    $total--;
                    $asArr[$k]['Sort'] = 2;
                    $complete = explode(',', $v['complete_approver']);
                    $notcomplete = explode(',', $v['not_complete_approver']);
                    if (!in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                        //完全未审
                        $asArr[$k]['btn'] = "待审批";
                    } elseif (in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                        //有已审，有未审
                        $asArr[$k]['btn'] = "待审批";
                    } elseif (in_array(session('username'), $complete) && !in_array(session('username'), $notcomplete)) {
                        //全部已审
                        $asArr[$k]['btn'] = "已审核";
                    } else {
                        $asArr[$k]['url'] = '';
                    }
                }
            }
        }
        $cmf_arr = array_column($asArr, 'Sort');
        array_multisort($cmf_arr, SORT_ASC, $asArr);
        $result["total"] = $total;
        $result["status"] = 1;
        $result["rows"] = $asArr;
        return $result;
    }

    //获取验收列表
    public function get_transfer_checklist()
    {
        $departids = session('departid');
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('get.order') ? I('get.order') : 'DESC';
        $sort = I('get.sort');
        $search = I('get.search');
        $hospital_id = session('current_hospitalid');
        $where['B.hospital_id'] = $hospital_id;
        if (!$departids) {
            return parent::noData();
        }
        if (!session('isSuper')) {
            $where['B.status'] = ['NEQ', C('ASSETS_STATUS_SCRAP')];
            $where['A.tranin_departid'] = array('in', $departids);
        } else {
            $where['B.status'] = ['NEQ', C('ASSETS_STATUS_SCRAP')];
        }
        switch ($sort) {
            case 'department':
                $sort = 'C.department';
                break;
            default:
                $sort = 'A.applicant_time';
                break;
        }
        if ($search) {
            $map['B.assets'] = ['like', "%$search%"];
            $map['B.assnum'] = ['like', "%$search%"];
            $map['B.model'] = ['like', "%$search%"];
            $map['B.brand'] = ['like', "%$search%"];
            $map['C.department'] = ['like', "%$search%"];
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $where['A.approve_status'][0] = 'IN';
        $where['A.approve_status'][1][] = C('STATUS_APPROE_UNWANTED');//不需审批
        $where['A.approve_status'][1][] = C('STATUS_APPROE_SUCCESS');//审批通过
        $where['A.is_check'] = C('TRANSFER_IS_NOTCHECK');//未验收
        //根据条件统计符合要求的数量
        $where['B.is_delete'] = 0;
        $join[0] = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $join[1] = 'LEFT JOIN sb_department AS C ON A.tranout_departid = C.departid';
        $total = $this->DB_get_count_join('assets_transfer', 'A', $join, $where);
        $fields = 'A.*,B.assnum,B.assorignum,B.assets,B.model,B.catid,B.buy_price,B.pic_url';
        $asArr = $this->DB_get_all_join('assets_transfer', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asArr) {
           return parent::noData();
        }

        //查询当前用户是否有权验收
        $canCheck = get_menu('Assets', 'Transfer', 'check');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        //查询是否开启了转科审批
        foreach ($asArr as $k => $v) {
            //查询用户头像
            $uinfo = $this->DB_get_one('user', 'pic', array('username' => $v['applicant_user']));
            if ($uinfo['pic'] == "" || $uinfo['pic'] == null) {
                $asArr[$k]['headimgurl'] = "/Public/mobile/images/user_logo.png";
            } else {
                $asArr[$k]['headimgurl'] = $uinfo['pic'];
            }

            $asArr[$k]['tranout_depart_name'] = $departname[$v['tranout_departid']]['department'];
            $asArr[$k]['tranin_depart_name'] = $departname[$v['tranin_departid']]['department'];
            $asArr[$k]['applicant_time'] = date('Y-m-d H:i', strtotime($v['applicant_time']));
            $asArr[$k]['tranout_depart_name'] = $departname[$v['tranout_departid']]['department'];
            $asArr[$k]['tranin_depart_name'] = $departname[$v['tranin_departid']]['department'];
            if ($canCheck) {
                $asArr[$k]['url'] = $this->returnMobileLink('验收', $canCheck['actionurl'] . '?atid=' . $v['atid'], '');
            } else {
                $asArr[$k]['url'] = $this->returnMobileLink('验收', 'javascript:void(0);', ' layui-btn-disabled');
            }
        }
        return [
            'page' => (int)$page,
            'pages' => (int)ceil($total / $limit),
            'total' => $total,
            'rows' => $asArr,
            'status' => 1
        ];
    }

    /** 格式化短信内容
     * @param $content
     * @param $data
     * @return mixed
     */
    public function formatSmsContent($content, $data)
    {
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{assnum}", $data['assnum'], $content);
        $content = str_replace("{tranout_department}", $data['tranout_department'], $content);
        $content = str_replace("{tranin_department}", $data['tranin_department'], $content);
        $content = str_replace("{transfer_num}", $data['transfer_num'], $content);
        $content = str_replace("{approve_status}", $data['approve_status'], $content);
        $content = str_replace("{check_status}", $data['check_status'], $content);
        return $content;
    }

}
