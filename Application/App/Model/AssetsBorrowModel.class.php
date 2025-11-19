<?php

namespace App\Model;

use App\Service\UserInfo\UserInfo;

class AssetsBorrowModel extends CommonModel
{
    private $MODULE = 'Assets';
    private $Controller = 'Borrow';
    protected $tablePrefix = 'sb_';
    protected $tableName = 'assets_borrow';

    /*
     * 借调申请列表 仅显示可以借调的设备
     */
    public function get_borrow_assets_list()
    {
        $departids   = UserInfo::getInstance()->get('departid');
        $hospital_id = UserInfo::getInstance()->get('current_hospitalid');
        $limit       = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page        = I('get.page') ? I('get.page') : 1;
        $offset      = ($page - 1) * $limit;
        $order       = I('get.order') ? I('get.order') : 'DESC';
        $sort        = I('get.sort');
        $search      = I('get.search');
        $catid       = I('get.catid');
        $departid    = I('get.departid');
        if (!$departids) {
            return parent::noData();
        }
        $where['A.hospital_id'] = $hospital_id;
        if (UserInfo::getInstance()->get('isSuper') != C('YES_STATUS')) {
            //筛选科室 获取除用户本身工作以外的科室
            $where['A.departid'][] = ['neq', UserInfo::getInstance()->get('job_departid')];
        }
        //$where['A.status'] = C('ASSETS_STATUS_USE');//只能借调在用的设备
        $where['A.status'][0]   = 'NOTIN';
        $where['A.status'][1][] = C('ASSETS_STATUS_SCRAP');
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE');


        $where['A.is_subsidiary']   = C('NO_STATUS');//只借调主设备
        $where['A.quality_in_plan'] = C('NO_STATUS');//排除质控中
        $where['A.patrol_in_plan']  = C('NO_STATUS');//排除巡查中
        switch ($sort) {
            case 'department':
                $sort = 'B.department';
                break;
            case 'opendate':
                $sort = 'A.opendate';
                break;
            default:
                $sort = 'A.assid';
                break;
        }
        if ($search) {
            $map['A.assets']     = ['like', "%$search%"];
            $map['A.assnum']     = ['like', "%$search%"];
            $map['A.model']      = ['like', "%$search%"];
            $map['A.brand']      = ['like', "%$search%"];
            $map['B.department'] = ['like', "%$search%"];
            $map['_logic']       = 'or';
            $where['_complex']   = $map;
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
                    $allcatid[]       = $catid;
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
        $fields               = "A.assid,A.assets,A.assnum,A.catid,A.departid,A.model,A.status,A.brand,A.pic_url,B.department,B.assetssum";
        $join[0]              = "LEFT JOIN sb_department AS B ON A.departid = B.departid";
        $total                = $this->DB_get_count_join('assets_info', 'A', $join, $where, '');
        $assets               = $this->DB_get_all_join('assets_info', 'A', $fields, $join, $where, '',
            $sort . ' ' . $order, $offset . "," . $limit);
        if (!$assets) {
            return parent::noData();
        }

        // 查询借调申请
        $query = (new AssetsBorrowModel());
        $query->where('assid', ['in', array_column($assets, 'assids')]);
        $query->field([
            'borid',
            'assid',
            'retrial_status',
            'status',
        ]);
        $borrowList = $query->select();
        // 单个设备有多个借调时，这里只拿最后一个
        $borrowMap = array_flip(array_column($borrowList, 'assid'));

        //查询是否有借调权限
        $menu = get_menu_app($this->MODULE, $this->Controller, 'applyBorrow');
        foreach ($assets as &$v) {
            if ($v['pic_url']) {
                $picArr       = parent::getPicArr($v['pic_url']);
                $v['pic_url'] = $picArr[0];
            }
            $v['category'] = $catname[$v['catid']]['category'];
            if ($menu) {
                $v['urlName'] = '申请借调';
                $v['url']     = $menu['actionurl'];
            }
            if ($v['retrial_status'] == 1) {
                $v['urlName']     = '重新申请';
                $v['status_name'] = '<span style="color:red;">借调审核失败</span>';
            } else {
                $v['status_name'] = '<span style="color:#009688;">' . $v['status_name'] . '</span>';
            }

            $borrow = $borrowList[$borrowMap[$v['assid']]];

            if ($borrow) {
                $v['borid'] = $borrow['borid'];
                $v['retrial_status'] = $borrow['retrial_status'];
                $v['is_borrowing'] = in_array($borrow['status'], [
                    C('BORROW_STATUS_APPROVE'),
                    C('BORROW_STATUS_BORROW_IN'),
                    C('BORROW_STATUS_GIVE_BACK'),
                ]);
            }
        }
        return [
            'page'   => (int)$page,
            'pages'  => (int)ceil($total / $limit),
            'total'  => $total,
            'rows'   => $assets,
            'status' => 1,
        ];
    }

    //借调审批列表数据
    public function get_borrow_examine()
    {
        //查询是否有审批权限
        $menu = get_menu_app('Assets', 'Borrow', 'approveBorrowList');
        if (!$menu) {
            $result['msg']    = '对不起，您没有获取借调审批列表的权限！';
            $result['rows']   = [];
            $result['total']  = 0;
            $result['status'] = -1;
            return $result;
        }
        $order = I('post.order') ? I('post.order') : 'DESC';
        $sort  = I('post.sort');
        switch ($sort) {
            case 'apply_time':
                $sort = 'A.apply_time';
                break;
            case 'department':
                $sort = 'B.department';
                break;
            default:
                $sort = 'A.apply_time';
                break;
        }
        $departids                 = UserInfo::getInstance()->get('departid');
        $hospital_id               = UserInfo::getInstance()->get('current_hospitalid');
        $where['A.examine_status'] = ['EQ', C('APPROVE_STATUS')];//未审核状态
        //借出部门审批
        $departApproveBorrowMenu = get_menu_app($this->MODULE, $this->Controller, 'departApproveBorrow');
        //设备科审批
        $assetsApproveBorrowMenu = get_menu_app($this->MODULE, $this->Controller, 'assetsApproveBorrow');
        //有审批权限的设备
        $backAssid = [];
        //负责人的可审批设备
        $managerApproveAssid = [];
        //设备科的可审批设备
        $assetsApproveAssid = [];

        if ($departApproveBorrowMenu) {
            //有借出部门审批权限
            $managerWhere['departid']    = ['in', $departids];
            $managerWhere['manager']     = ['EQ', UserInfo::getInstance()->get('username')];
            $managerWhere['hospital_id'] = UserInfo::getInstance()->get('current_hospitalid');
            $manager                     = $this->DB_get_all('department', 'departid,manager', $managerWhere);
            if ($manager) {
                //负责的科室
                $managerDepairtid = [];
                foreach ($manager as $managerV) {
                    $managerDepairtid[] = $managerV['departid'];
                }
                $assetsDepartWhere['departid']  = ['IN', $managerDepairtid];
                $assetsDepartWhere['is_delete'] = '0';
                $assetsDepart                   = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
                if ($assetsDepart) {
                    foreach ($assetsDepart as &$assetsDepartV) {
                        $backAssid[]                                  = $assetsDepartV['assid'];
                        $managerApproveAssid[$assetsDepartV['assid']] = true;
                    }
                }
            }
        }

        if ($assetsApproveBorrowMenu) {
            //有设备科审批权限
            $assetsDepartWhere['departid']  = ['IN', $departids];
            $assetsDepartWhere['is_delete'] = '0';
            $assetsDepart                   = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
            if ($assetsDepart) {
                foreach ($assetsDepart as &$assetsDepartV) {
                    $backAssid[]                                 = $assetsDepartV['assid'];
                    $assetsApproveAssid[$assetsDepartV['assid']] = true;
                }
            }
        }

        if (!$backAssid) {
            $result['msg']    = '暂无需处理的借调审批流程';
            $result['status'] = 1;
            $result['total']  = 0;
            return $result;
        }
        $backAssid              = array_unique($backAssid);
        $assetsWhere['assid'][] = ['IN', $backAssid];

        if ($hospital_id) {
            $assetsWhere['hospital_id'] = $hospital_id;
        } else {
            //管理员默认情况下的话只能看到自己工作的医院下的设备
            $assetsWhere['hospital_id'] = UserInfo::getInstance()->get('current_hospitalid');
        }
        $assetsWhere['is_delete'] = '0';
        $assets                   = $this->DB_get_all('assets_info', 'assid', $assetsWhere);
        if ($assets) {
            $assetsAssid = [];
            foreach ($assets as &$assetsAssidV) {
                $assetsAssid[] = $assetsAssidV['assid'];
            }
            $where['A.assid'][] = ['IN', $assetsAssid];
        } else {
            $result['msg']    = '暂无相关数据';
            $result['rows']   = [];
            $result['total']  = 0;
            $result['status'] = 1;
            return $result;
        }

        //获取审批列表信息
        $join   = "LEFT JOIN sb_department AS B ON A.apply_departid = B.departid";
        $fileds = 'A.borid,A.assid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,A.borrow_reason,A.estimate_back,A.status,A.examine_status';
        $total  = $this->DB_get_count_join('assets_borrow', 'A', $join, $where);
        $data   = $this->DB_get_all_join('assets_borrow', 'A', $fileds, $join, $where, '', $sort . ' ' . $order, '');
        if (!$data) {
            $result['msg']    = '暂无相关数据';
            $result['rows']   = [];
            $result['total']  = 0;
            $result['status'] = 1;
            return $result;
        }
        //获取设备基本信息
        $assid  = [];
        $userid = [];
        foreach ($data as &$dataV) {
            $assid[]  = $dataV['assid'];
            $userid[] = $dataV['apply_userid'];
        }
        $assetsWhere              = [];
        $assetsWhere['assid']     = ['IN', $assid];
        $assetsWhere['is_delete'] = '0';
        $fileds                   = 'departid,assets,assnum,brand,model,status,assid';
        $assets                   = $this->DB_get_all('assets_info', $fileds, $assetsWhere);
        $assetsData               = [];
        $departname               = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assets as &$assetsV) {
            $assetsData[$assetsV['assid']]['department'] = $departname[$assetsV['departid']]['department'];
            $assetsData[$assetsV['assid']]['assets']     = $assetsV['assets'];
            $assetsData[$assetsV['assid']]['assnum']     = $assetsV['assnum'];
            $assetsData[$assetsV['assid']]['brand']      = $assetsV['brand'];
            $assetsData[$assetsV['assid']]['model']      = $assetsV['model'];
        }
        //获取对应的申请人名称
        $userWhere['userid'] = ['IN', $userid];
        $user                = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData            = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }
        foreach ($data as &$dataV) {
            $dataV['department']       = $assetsData[$dataV['assid']]['department'];
            $dataV['assets']           = $assetsData[$dataV['assid']]['assets'];
            $dataV['assnum']           = $assetsData[$dataV['assid']]['assnum'];
            $dataV['brand']            = $assetsData[$dataV['assid']]['brand'];
            $dataV['model']            = $assetsData[$dataV['assid']]['model'];
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back']    = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time']       = date('Y-m-d H:i', $dataV['apply_time']);
            $dataV['apply_user']       = $userData[$dataV['apply_userid']]['username'];
            //查询审批历史
            $apps = $this->DB_get_all('assets_borrow_approve', '', ['borid' => $dataV['borid']], '',
                'level,approve_time asc');
            if ((!$apps && $managerApproveAssid[$dataV['assid']]) or UserInfo::getInstance()->get('isSuper') == C('YES_STATUS')) {
                //未审批,是第一个审批人
                $dataV['Sort'] = 1;
                $dataV['btn']  = "审批";
                continue;

            }
            if (!$apps && !$managerApproveAssid[$dataV['assid']]) {
                //未审批,不是第一个审批人
                $total--;
                $dataV['Sort'] = 2;
                $dataV['btn']  = "待审批";
                continue;
            } elseif ($apps && $assetsApproveAssid[$dataV['assid']]) {
                $dataV['Sort'] = 1;
                $dataV['btn']  = "审批";
                continue;
            } else {
                $total--;
                $dataV['Sort'] = 2;
                $dataV['btn']  = "已审批";
            }
        }
        if (!I('POST.sort')) {
            $cmf_arr = array_column($data, 'Sort');
            array_multisort($cmf_arr, SORT_ASC, $data);
        }
        $result["total"]  = $total;
        $result["status"] = 1;
        $result["rows"]   = $data;
        return $result;
    }


    //借入验收列表
    public function borrowInCheckList()
    {
        $limit  = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page   = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * C('PAGE_NUMS');
        $order  = I('get.order') ? I('get.order') : 'desc';
        $sort   = I('get.sort');
        $search = trim(I('get.search'));
        if ($search) {
            $where[1]['B.assnum']     = ['LIKE', "%$search%"];
            $where[1]['B.assets']     = ['LIKE', "%$search%"];
            $where[1]['A.borrow_num'] = ['LIKE', "%$search%"];
            $where[1]['_logic']       = 'OR';
        }
        if (!$sort) {
            $sort = 'A.borid';
        } else {
            $sort = 'A.' . $sort;
        }
        $where['A.status'] = ['EQ', C('BORROW_STATUS_BORROW_IN')];
        if (UserInfo::getInstance()->get('isSuper') != C('YES_STATUS')) {
            if (!UserInfo::getInstance()->get('job_departid')) {
                $result['msg']    = '该用户未分配工作科室';
                $result['status'] = -1;
                return $result;
            }
            $where['A.apply_departid'] = ['IN', UserInfo::getInstance()->get('job_departid')];
        }

        $where['B.is_delete']   = 0;
        $where['B.hospital_id'] = UserInfo::getInstance()->get('current_hospitalid');
        //获取审批列表信息
        $join   = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fileds = 'A.borid,A.assid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,A.borrow_reason,A.estimate_back,A.status,A.examine_status,A.borrow_in_time,B.departid,B.assets,B.assnum,B.brand,B.model,B.status AS a_status';
        $total  = $this->DB_get_count_join('assets_borrow', 'A', $join, $where);
        $data   = $this->DB_get_all_join('assets_borrow', 'A', $fileds, $join, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        if (!$data) {
            return parent::noData();
        }
        //获取设备基本信息
        $assid  = [];
        $userid = [];
        $borid  = [];
        foreach ($data as &$dataV) {
            $borid[]  = $dataV['borid'];
            $assid[]  = $dataV['assid'];
            $userid[] = $dataV['apply_userid'];
        }
        //获取对应的申请人名称
        $userWhere['userid'] = ['IN', $userid];
        $user                = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData            = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }
        $borrowInCheckMenu = get_menu_app($this->MODULE, $this->Controller, 'borrowInCheck');
        $departname        = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$dataV) {
            $dataV['department']       = $departname[$dataV['departid']]['department'];
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back']    = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time']       = getHandleMinute($dataV['apply_time']);
            $dataV['apply_user']       = $userData[$dataV['apply_userid']]['username'];
            if ($borrowInCheckMenu) {
                $dataV['operation'] = $this->returnMobileLink($borrowInCheckMenu['actionname'],
                    $borrowInCheckMenu['actionurl'] . '?borid=' . $dataV['borid'],
                    ' layui-btn-normal layui-btn-sm accept');
            }
        }
        if (!$data) {
            return parent::noData();
        }
        return [
            'page'   => (int)$page,
            'pages'  => (int)ceil($total / $limit),
            'total'  => $total,
            'rows'   => $data,
            'status' => 1,
        ];
    }

    //归还验收列表
    public function giveBackCheckList()
    {
        $limit  = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page   = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * C('PAGE_NUMS');
        $order  = I('get.order') ? I('get.order') : 'desc';
        $sort   = I('get.sort');
        $search = trim(I('get.search'));
        if ($search) {
            $depairWhere['department']  = ['LIKE', "%$search%"];
            $depairWhere['hospital_id'] = ['EQ', UserInfo::getInstance()->get('current_hospitalid')];
            $department                 = $this->DB_get_all('department', 'departid', $depairWhere);
            if ($department) {
                $departidArr = [];
                foreach ($department as &$one) {
                    $departidArr[] = $one['departid'];
                }
                $where[1]['B.departid'] = ['IN', $departidArr];
            }
            $where[1]['B.assnum']     = ['LIKE', "%$search%"];
            $where[1]['B.assets']     = ['LIKE', "%$search%"];
            $where[1]['A.borrow_num'] = ['LIKE', "%$search%"];
            $where[1]['_logic']       = 'OR';
        }
        if (!$sort) {
            $sort = 'A.borid';
        } else {
            $sort = 'A.' . $sort;
        }
        $where['A.status'] = ['EQ', C('BORROW_STATUS_GIVE_BACK')];
        if (!UserInfo::getInstance()->get('departid')) {
            return parent::noData();
        }
        $assetsDepartWhere['departid']    = ['IN', UserInfo::getInstance()->get('departid')];
        $assetsDepartWhere['hospital_id'] = UserInfo::getInstance()->get('current_hospitalid');
        $assetsDepartWhere['is_delete']   = '0';
        $assetsDepart                     = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
        if (!$assetsDepart) {
            return parent::noData();
        }
        $backAssid = [];
        foreach ($assetsDepart as &$assetsDepartV) {
            $backAssid[] = $assetsDepartV['assid'];
        }
        $where['A.assid']     = ['IN', $backAssid];
        $where['B.is_delete'] = '0';
        //获取审批列表信息
        $fileds = 'B.assnum,B.assid,B.assets,A.borid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,
        A.borrow_reason,A.estimate_back,A.status,A.examine_status,A.borrow_in_time,A.supplement';
        $join   = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $total  = $this->DB_get_count_join('assets_borrow', 'A', $join, $where);
        $data   = $this->DB_get_all_join('assets_borrow', 'A', $fileds, $join, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);

        if (!$data) {
            return parent::noData();
        }
        //获取设备基本信息
        $userid = [];
        $borid  = [];
        foreach ($data as &$dataV) {
            $userid[] = $dataV['apply_userid'];
            $borid[]  = $dataV['borid'];
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";

        //获取对应的申请人名称
        $userWhere['userid'] = ['IN', $userid];
        $user                = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData            = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }

        //获取附属设备明细
        $subsidiaryWhere['borid']     = ['IN', $borid];
        $subsidiaryWhere['is_delete'] = '0';
        $join                         = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields                       = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price,D.borid';
        $subsidiary                   = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join,
            $subsidiaryWhere, '', '', '');
        $subsidiaryData               = [];
        if ($subsidiary) {
            foreach ($subsidiary as &$subV) {
                $subsidiaryData[$subV['borid']][] = $subV;
            }
        }
        $giveBackCheckMenu = get_menu_app($this->MODULE, $this->Controller, 'giveBackCheck');
        foreach ($data as &$dataV) {
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back']    = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time']       = getHandleMinute($dataV['apply_time']);
            $dataV['borrow_in_time']   = getHandleMinute($dataV['borrow_in_time']);
            $dataV['apply_user']       = $userData[$dataV['apply_userid']]['username'];
            if ($giveBackCheckMenu) {
                $dataV['operation'] = $this->returnMobileLink($giveBackCheckMenu['actionname'],
                    $giveBackCheckMenu['actionurl'] . '?borid=' . $dataV['borid'],
                    ' layui-btn-normal layui-btn-sm accept');
            }
        }
        if (!$data) {
            return parent::noData();
        }
        return [
            'page'   => (int)$page,
            'pages'  => (int)ceil($total / $limit),
            'total'  => $total,
            'rows'   => $data,
            'status' => 1,
        ];
    }

    public function getReminderList()
    {
        $borid = I('get.borid');
        if ($borid) {
            $where['A.borid'] = $borid;
        }
        $where['A.status'] = ['EQ', C('BORROW_STATUS_GIVE_BACK')];
        if (!UserInfo::getInstance()->get('departid')) {
            $result['msg']    = '暂无科室信息';
            $result['status'] = -1;
            return $result;
        }
        $assetsDepartWhere['is_delete']   = '0';
        $assetsDepartWhere['departid']    = ['IN', UserInfo::getInstance()->get('departid')];
        $assetsDepartWhere['hospital_id'] = UserInfo::getInstance()->get('current_hospitalid');
        $assetsDepart                     = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
        if (!$assetsDepart) {
            $result['msg']    = '暂无相关数据';
            $result['status'] = 1;
            $result['total']  = 0;
            return $result;
        }
        $backAssid = [];
        foreach ($assetsDepart as &$assetsDepartV) {
            $backAssid[] = $assetsDepartV['assid'];
        }
        $where['A.assid']       = ['IN', $backAssid];
        $where['B.is_delete']   = '0';
        $where['estimate_back'] = ['LT', time()];
        //获取审批列表信息
        $fileds = 'B.assnum,B.assid,B.assets,A.borid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,
        A.borrow_reason,A.estimate_back,A.status,A.examine_status,A.borrow_in_time,A.supplement';
        $join   = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $data   = $this->DB_get_all_join('assets_borrow', 'A', $fileds, $join, $where, '', '', '');
        if (!$data) {
            $result['msg']    = '暂无相关数据';
            $result['status'] = 1;
            $result['total']  = 0;
            return $result;
        }
        //获取设备基本信息
        $userid = [];
        $borid  = [];
        foreach ($data as &$dataV) {
            $userid[] = $dataV['apply_userid'];
            $borid[]  = $dataV['borid'];
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";

        //获取对应的申请人名称
        $userWhere['userid'] = ['IN', $userid];
        $user                = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData            = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }

        //获取附属设备明细
        $subsidiaryWhere['A.is_delete'] = '0';
        $subsidiaryWhere['borid']       = ['IN', $borid];
        $join                           = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields                         = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price,D.borid';
        $subsidiary                     = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join,
            $subsidiaryWhere, '', '', '');
        $subsidiaryData                 = [];
        if ($subsidiary) {
            foreach ($subsidiary as &$subV) {
                $subsidiaryData[$subV['borid']][] = $subV;
            }
        }
        foreach ($data as &$dataV) {

            if ($dataV['estimate_back'] < time()) {
                $dataV['overdue'] = timediff($dataV['estimate_back'], time());
                if ($dataV['overdue'] > 24) {
                    $dataV['overdue'] = (sprintf('%.2f', $dataV['overdue'] / 24)) . '天';
                } else {
                    $dataV['overdue'] .= '小时';
                }
            }

            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back']    = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time']       = getHandleMinute($dataV['apply_time']);
            $dataV['borrow_in_time']   = getHandleMinute($dataV['borrow_in_time']);
            $dataV['apply_user']       = $userData[$dataV['apply_userid']]['username'];
        }
        $result["status"] = 1;
        $result['total']  = count($data);
        $result['rows']   = $data;
        if (!$result['rows']) {
            $result['msg']    = '暂无相关数据';
            $result['status'] = 1;
            $result['total']  = 0;
        }
        return $result;
    }

    /**
     * 获取设备基本信息
     *
     * @param $assid int 设备id
     *
     * @return array
     */
    public function getAssetsBasic($assid)
    {
        $where['assid']     = ['EQ', $assid];
        $where['is_delete'] = '0';
        $files              = 'assid,catid,assnum,assets,helpcatid,status,brand,model,unit,serialnum,assetsrespon,departid,address,buy_price';
        $assets             = $this->DB_get_one('assets_info', $files, $where);
        $files              = 'afid,factory,factory_user,factory_tel,supplier,supp_user,supp_tel,repair,repa_user,repa_tel';
        $factory            = $this->DB_get_one('assets_factory', $files, $where);
        $departname         = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assets['department'] = $departname[$assets['departid']]['department'];
        switch ($assets['status']) {
            case C('ASSETS_STATUS_USE'):
                $assets['statusName'] = C('ASSETS_STATUS_USE_NAME');
                break;
            case C('ASSETS_STATUS_REPAIR'):
                $assets['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                break;
        }
        if (empty($factory)){
            return $assets;
        }else{
            return array_merge($assets, $factory);
        }
    }

    /**
     * 获取借调基本信息
     *
     * @param $borid int 借调id
     *
     * @return array
     * */
    public function getBorrowBasic($borid)
    {
        $where['borid'] = ['EQ', $borid];
        $files          = 'assid,borid,borrow_num,apply_userid,apply_departid,borrow_reason,estimate_back,apply_time,status,examine_status,
        not_apply_time,end_reason,borrow_in_time,borrow_in_userid,give_back_time,give_back_userid,end_reason,score_value,
        score_remark,examine_status,status,examine_time,supplement';
        $borrow         = $this->DB_get_one('assets_borrow', $files, $where);
        $departname     = [];
        include APP_PATH . "Common/cache/department.cache.php";


        if ($borrow['estimate_back'] < time()) {
            $borrow['overdue'] = timediff($borrow['estimate_back'], time());
            if ($borrow['overdue'] > 24) {
                $borrow['overdue'] = (sprintf('%.2f', $borrow['overdue'] / 24)) . '天';
            } else {
                $borrow['overdue'] .= '小时';
            }
        }


        $borrow['department']     = $departname[$borrow['apply_departid']]['department'];
        $borrow['estimate_back']  = getHandleMinute($borrow['estimate_back']);
        $borrow['apply_time']     = getHandleMinute($borrow['apply_time']);
        $borrow['not_apply_time'] = getHandleMinute($borrow['not_apply_time']);
        $borrow['borrow_in_time'] = getHandleMinute($borrow['borrow_in_time']);
        $borrow['give_back_time'] = getHandleMinute($borrow['give_back_time']);
        $borrow['examine_time']   = getHandleMinute($borrow['examine_time']);
        $userId[]                 = $borrow['apply_userid'];

        if ($borrow['borrow_in_userid']) {
            $userId[] = $borrow['borrow_in_userid'];
        }

        if ($borrow['give_back_userid']) {
            $userId[] = $borrow['give_back_userid'];
        }


        $user = $this->DB_get_All('user', 'userid,username', ['userid' => ['IN', $userId]]);

        $subsidiary               = $this->DB_get_count('assets_borrow_detail', $where);
        $borrow['subsidiary_num'] = $subsidiary;

        foreach ($user as &$userV) {
            if ($userV['userid'] == $borrow['apply_userid']) {
                $borrow['apply_username'] = $userV['username'];
            }
            if ($userV['userid'] == $borrow['borrow_in_userid']) {
                $borrow['borrow_in_username'] = $userV['username'];
            }

            if ($userV['userid'] == $borrow['give_back_userid']) {
                $borrow['give_back_username'] = $userV['username'];
            }
        }

        return $borrow;
    }

    /*
     *  借调详细流程
     */
    public function get_progress_detail($borrowInfo, $approves)
    {
        $line = $applicant = $approve = $check = [];

        //转科申请
        $applicant['statusName'] = '借调申请';
        $applicant['date']       = $borrowInfo['applicant_time'];
        $applicant['user']       = $borrowInfo['apply_username'];
        $applicant['text']       = '【' . $applicant['user'] . '】提交了设备借调申请';
        $line[]                  = $applicant;

        if ($borrowInfo['approve_status'] != -1) {
            //借调审批
            foreach ($approves as &$one) {
                $approve['statusName'] = '借调审批';
                $approve['date']       = $one['approve_time'];
                $approve['user']       = $one['approver'];
                $one['is_adopt']       = $one['is_adopt'] == C('REPAIR_IS_CHECK_ADOPT') ? '<span style="color:green;">通过</span>' : '<span style="color:red;">未通过</span>';
                $approve['text']       = '【' . $approve['user'] . '】对借调设备进行审核，审批结果：' . $one['is_adopt'];
                $line[]                = $approve;
            }
        }
        //转科验收
        if ($borrowInfo['status'] == 4) {
            $check['statusName'] = '借入验收';
            $check['date']       = $borrowInfo['borrow_in_time'];
            $check['user']       = $borrowInfo['borrow_in_username'];
            $check['text']       = '【' . $check['user'] . '】对借调设备进行借入验收，验收结果：不借入';
            $line[]              = $check;
        } elseif ($borrowInfo['status'] > 1) {
            $check['statusName'] = '借入验收';
            $check['date']       = $borrowInfo['borrow_in_time'];
            $check['user']       = $borrowInfo['borrow_in_username'];
            $check['text']       = '【' . $check['user'] . '】对借调设备进行借入验收，验收结果：借入';
            $line[]              = $check;
        }

        if ($borrowInfo['give_back_username']) {
            $check['statusName'] = '归还验收';
            $check['date']       = $borrowInfo['give_back_time'];
            $check['user']       = $borrowInfo['give_back_username'];
            $check['text']       = '【' . $check['user'] . '】对借调设备进行归还验收，验收结果：归还成功';
            $line[]              = $check;
        }
        $user = [];
        foreach ($line as $k => $v) {
            $user[] = $v['user'];
        }
        $userModel         = M('user');
        $where['username'] = ['in', $user];
        $user_phone        = $userModel->where($where)->getField('username,telephone');
        foreach ($line as $k => $v) {
            $line[$k]['telephone'] = $user_phone[$v['user']];
        }
        return $line;
    }

    /**
     * 获取借调审核基本信息
     *
     * @param $borid int 借调id
     *
     * @return array
     * */
    public function getBorrowApprovBasic($borid)
    {
        $where['borid'] = ['EQ', $borid];
        $approve        = $this->DB_get_all('assets_borrow_approve', '', $where, '', 'level,approve_time asc');
        if ($approve) {
            $userId = [];
            foreach ($approve as &$approveV) {
                $userId[]                 = $approveV['approve_userid'];
                $approveV['approve_time'] = getHandleMinute($approveV['approve_time']);
                switch ($approveV['approve_status']) {
                    case C('STATUS_APPROE_SUCCESS'):
                        $approveV['opinion']             = '<span style = "color:green" >通过</span >';
                        $approveV['is_adoptNameNoColor'] = '通过</span > ';
                        break;
                    case C('STATUS_APPROE_FAIL'):
                        $approveV['opinion']             = '<span style = "color:red" >不通过</span >';
                        $approveV['is_adoptNameNoColor'] = '不通过';
                        break;
                    default :
                        $approveV['opinion'] = '未审核';
                }
            }

            $user     = $this->DB_get_All('user', 'userid,username', ['userid' => ['IN', $userId]]);
            $userData = [];
            foreach ($user as &$userV) {
                $userData[$userV['userid']] = $userV['username'];
            }
            foreach ($approve as &$approveV) {
                $approveV['approver'] = $userData[$approveV['approve_userid']];
            }
        }
        return $approve;
    }


    /**
     * 获取附属设备信息
     *
     * @param $borid int 借调记录id
     *
     * @return array
     * */
    public function getSubsidiaryBasic($borid)
    {
        $join   = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price';
        $data   = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join,
            "borid=$borid and A.is_delete='0'", '', '', '');
        return $data;
    }

    //借调进程列表
    public function borrowLife()
    {
        $limit     = C('PAGE_NUMS');
        $page      = I('get.page') ? I('get.page') : 1;
        $offset    = ($page - 1) * C('PAGE_NUMS');
        $order     = I('get.order') ? I('get.order') : 'DESC';
        $sort      = I('get.sort') ? I('get.sort') : 'borid';
        $startTime = strtotime(date("Ymd"));
        $endTime   = $startTime + 86399;
        //获取所管理科室下面的设备
        if (!UserInfo::getInstance()->get('departid')) {
            $result['msg']    = '暂无科室信息';
            $result["status"] = 1;
            $result['total']  = 0;
            return $result;
        }
        $assetsDepartWhere['hospital_id'] = UserInfo::getInstance()->get('current_hospitalid');
        $assetsDepart                     = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
        if (!$assetsDepart) {
            $result['msg']    = '暂无相关数据';
            $result["status"] = 1;
            $result['total']  = 0;
            return $result;
        }
        $backAssid = [];
        foreach ($assetsDepart as &$assetsDepartV) {
            $backAssid[] = $assetsDepartV['assid'];
        }
        $where['assid'] = ['IN', $backAssid];

        $showStatus[] = C('BORROW_STATUS_APPROVE');
        $showStatus[] = C('BORROW_STATUS_BORROW_IN');
        $showStatus[] = C('BORROW_STATUS_GIVE_BACK');
//        //正常流程的设备
        $where[1][1]['status'] = ['IN', $showStatus];

        //或者当天结束的设备
        //1.完成验收
        $where[1][2][1]['status']           = ['EQ', C('BORROW_STATUS_COMPLETE')];
        $where[1][2][1][]['give_back_time'] = ['EGT', $startTime];
        $where[1][2][1][]['give_back_time'] = ['ELT', $endTime];

        //2.不借调
        $where[1][2][2]['status']           = ['EQ', C('BORROW_STATUS_NOT_APPLY')];
        $where[1][2][2][]['not_apply_time'] = ['EGT', $startTime];
        $where[1][2][2][]['not_apply_time'] = ['ELT', $endTime];

        //3.审批不通过
        $where[1][2][3]['status']         = ['EQ', C('BORROW_STATUS_FAIL')];
        $where[1][2][3][]['examine_time'] = ['EGT', $startTime];
        $where[1][2][3][]['examine_time'] = ['ELT', $endTime];

        $where[1][2]['_logic'] = 'or';
        $where[1]['_logic']    = 'or';
        //获取审批列表信息
        $fileds = 'borid,assid,borrow_num,apply_departid,estimate_back,apply_time,not_apply_time,borrow_in_time,
        borrow_in_userid,give_back_time,give_back_userid,examine_status,status,examine_time';
        $total  = $this->DB_get_count('assets_borrow', $where);
        $data   = $this->DB_get_all('assets_borrow', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg']    = '暂无相关数据';
            $result["status"] = 1;
            $result['total']  = 0;
            return $result;
        }
        //获取设备基本信息
        $assid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
            $borid[] = $dataV['borid'];
        }
        $assetsWhere['assid'] = ['IN', $assid];
        $fileds               = 'departid,assets,assnum,assid';
        $assets               = $this->DB_get_all('assets_info', $fileds, $assetsWhere);
        $assetsData           = [];
        $departname           = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assets as &$assetsV) {
            $assetsData[$assetsV['assid']]['department'] = $departname[$assetsV['departid']]['department'];
            $assetsData[$assetsV['assid']]['assets']     = $assetsV['assets'];
            $assetsData[$assetsV['assid']]['assnum']     = $assetsV['assnum'];
            $assetsData[$assetsV['assid']]['brand']      = $assetsV['brand'];
            $assetsData[$assetsV['assid']]['model']      = $assetsV['model'];
            switch ($assetsV['status']) {
                case C('ASSETS_STATUS_USE'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $assetsData[$assetsV['assid']]['statusName'] = C('ASSETS_STATUS_SCRAP_NAME');
                    break;
            }
        }

        $approveWhere['borid'] = ['IN', $borid];
        $approve               = $this->DB_get_all('assets_borrow_approve', 'approve_time,level,borid', $approveWhere,
            '', 'borid asc,level asc');

        $approveData = [];
        foreach ($approve as &$approveV) {
            $approveData[$approveV['borid']][$approveV['level']]['approve_time'] = getHandleMinute($approveV['approve_time']);
        }

        foreach ($data as &$dataV) {
            $dataV['department']       = $assetsData[$dataV['assid']]['department'];
            $dataV['assets']           = $assetsData[$dataV['assid']]['assets'];
            $dataV['assnum']           = $assetsData[$dataV['assid']]['assnum'];
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back']    = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time']       = getHandleMinute($dataV['apply_time']);
            $dataV['borrow_in_time']   = getHandleMinute($dataV['borrow_in_time']);
            $dataV['give_back_time']   = getHandleMinute($dataV['give_back_time']);
            $dataV['not_apply_time']   = getHandleMinute($dataV['not_apply_time']);
            $dataV['approve']          = $approveData[$dataV['borid']];
            if ($dataV['status'] == C('BORROW_STATUS_BORROW_IN')) {
                //验收不通过
                $dataV['show_status_name'] = '借入检查';
            } elseif ($dataV['status'] == C('BORROW_STATUS_GIVE_BACK')) {
                //验收不通过
                $dataV['show_status_name'] = '归还验收';
            } elseif ($dataV['status'] == C('BORROW_STATUS_COMPLETE')) {
                //验收不通过
                $dataV['show_status_name'] = '归还验收完成';
            } elseif ($dataV['status'] == C('BORROW_STATUS_NOT_APPLY')) {
                //验收不通过
                $dataV['show_status_name'] = '不借入';
            } elseif ($dataV['status'] == C('BORROW_STATUS_APPROVE')) {
                $dataV['show_status_name'] = '审批中';
                $dataV['type']             = 'warning';
            } elseif ($dataV['status'] == C('BORROW_STATUS_FAIL')) {
                $dataV['show_status_name'] = '审批不通过';
                $dataV['type']             = 'danger';
            }
        }
        $result['total']  = $total;
        $result["offset"] = $offset;
        $result["page"]   = $page;
        $result["status"] = 1;
        $result['rows']   = $data;
        return $result;
    }
}
