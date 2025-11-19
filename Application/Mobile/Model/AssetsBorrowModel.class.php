<?php

namespace Mobile\Model;

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
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $limit = I('post.limit') ? I('post.limit') : C('PAGE_NUMS');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'DESC';
        $sort = I('post.sort');
        $search = I('POST.search');
        $catid = I('POST.catid');
        $departid = I('POST.departid');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $where['A.hospital_id'] = $hospital_id;
        if (session('isSuper') != C('YES_STATUS')) {
            //筛选科室 获取除用户本身工作以外的科室
            $where['A.departid'][] = array('neq', session('job_departid'));
        }
        $where['A.status'][0] = 'NOTIN';
        $where['A.status'][1][] = C('ASSETS_STATUS_SCRAP');
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE');

        $where['A.is_subsidiary'] = C('NO_STATUS');//只借调主设备
        $where['A.quality_in_plan'] = C('NO_STATUS');//排除质控中
        $where['A.patrol_in_plan'] = C('NO_STATUS');//排除巡查中
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
        if($search){
            $map['A.assets'] = array('like','%' . $search. '%');
            $map['A.assnum'] = array('like', '%' . $search . '%');
            $map['A.model'] = array('like','%' . $search. '%');
            $map['A.brand'] = array('like','%' . $search. '%');
            $map['B.department'] = array('like','%' . $search. '%');
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        if ($catid) {
            //查询是否父分类
            $parentcat = $this->DB_get_one('category','parentid,catid',array('catid'=>$catid));
            if($parentcat['parentid'] != 0){
                $where['A.catid'] = $catid;
            }else{
                //查询子类
                $allcatid = $this->DB_get_one('category','group_concat(catid) as catids',array('parentid'=>$parentcat['catid']));
                if($allcatid['catids']){
                    $allcatid['catids'].=','.$catid;
                    $where['A.catid'] = array('in',$allcatid['catids']);
                }else{
                    $where['A.catid'] = $catid;
                }
            }
        }
        if ($departid) {
            $where['A.departid'] = $departid;
        }
        //获取借调中的设备
        $notwhere['status'] = array('not in',[3,4]);
        $notarr = $this->DB_get_all('assets_borrow','assid',$notwhere);
        $notid = [];
        foreach ($notarr as $k=>$v){
            $notid[] = $v['assid'];
        }
        if($notid){
            $where['A.assid'] = array('not in',$notid);
        }
        $where['A.is_delete'] = '0';
        $fields = "A.assid,A.assets,A.assnum,A.catid,A.departid,A.model,A.status,A.brand,A.pic_url,B.department,B.assetssum";
        $join = "LEFT JOIN sb_department AS B ON A.departid = B.departid";
        $total = $this->DB_get_count_join('assets_info', 'A',$join,$where,'');
        $assets = $this->DB_get_all_join('assets_info','A',$fields,$join,$where,'',$sort . ' ' . $order, $offset . "," . $limit);
        if(!$assets){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        //查询是否有借调权限
        $menu = get_menu($this->MODULE,$this->Controller,'applyBorrow');
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        foreach ($assets as &$v){
            if($v['pic_url']){
                $pic_url = explode(',',$v['pic_url']);
                $picarr = [];
                foreach ($pic_url as $k1=>$v1){
                    $v1 = str_replace('/Public/uploads/'.C('UPLOAD_DIR_ASSETS_NAME').'/','',$v1);
                    $picarr[] = '/Public/uploads/'.C('UPLOAD_DIR_ASSETS_NAME').'/'.$v1;
                }
                $v['pic_url'] = $picarr;
            }
            $v['category'] = $catname[$v['catid']]['category'];
            if($menu){
                $v['url'] = $this->returnMobileLink('申请借调',$menu['actionurl'].'?assid='.$v['assid'],' layui-btn-normal layui-btn-sm');
            }else{
                $v['url'] = $this->returnMobileLink('申请借调','javascript:void(0);',' layui-btn-disabled layui-btn-sm');
            }
            switch ($v['status']) {
                case C('ASSETS_STATUS_USE'):
                    $v['status_name'] = C('ASSETS_STATUS_USE_NAME');
                    $v['status_name'] = '<span style="color:#009688;">'.$v['status_name'].'</span>';
                    break;
            }
        }
        $result['page']  = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result['rows']  = $assets;
        $result['code']  = 200;
        return $result;
    }

    //借调审批列表数据
    public function get_borrow_examine()
    {
        //查询是否有审批权限
        $menu = get_menu('Assets','Borrow','approveBorrowList');
        if(!$menu){
            $result['msg']   = '对不起，您没有获取借调审批列表的权限！';
            $result['rows']  = [];
            $result['total'] = 0;
            $result['code']  = 400;
            return $result;
        }
        $order = I('post.order') ? I('post.order') : 'DESC';
        $sort = I('post.sort');
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
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $where['A.examine_status'] = array('EQ', C('APPROVE_STATUS'));//未审核状态
        //借出部门审批
        $departApproveBorrowMenu = get_menu($this->MODULE, $this->Controller, 'departApproveBorrow');
        //设备科审批
        $assetsApproveBorrowMenu = get_menu($this->MODULE, $this->Controller, 'assetsApproveBorrow');
        //有审批权限的设备
        $backAssid = [];
        //负责人的可审批设备
        $managerApproveAssid = [];
        //设备科的可审批设备
        $assetsApproveAssid = [];

        if ($departApproveBorrowMenu) {
            //有借出部门审批权限
            $managerWhere['departid'] = array('in', $departids);
            $managerWhere['manager'] = array('EQ', session('username'));
            $managerWhere['hospital_id'] = session('current_hospitalid');
            $manager = $this->DB_get_all('department', 'departid,manager', $managerWhere);
            if ($manager) {
                //负责的科室
                $managerDepairtid = [];
                foreach ($manager as $managerV) {
                    $managerDepairtid[] = $managerV['departid'];
                }
                $assetsDepartWhere['departid'] = array('IN', $managerDepairtid);
                $assetsDepartWhere['is_delete'] = '0';
                $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
                if ($assetsDepart) {
                    foreach ($assetsDepart as &$assetsDepartV) {
                        $backAssid[] = $assetsDepartV['assid'];
                        $managerApproveAssid[$assetsDepartV['assid']] = true;
                    }
                }
            }
        }

        if ($assetsApproveBorrowMenu) {
            //有设备科审批权限
            $assetsDepartWhere['departid'] = array('IN', $departids);
            $assetsDepartWhere['is_delete'] = '0';
            $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
            if ($assetsDepart) {
                foreach ($assetsDepart as &$assetsDepartV) {
                    $backAssid[] = $assetsDepartV['assid'];
                    $assetsApproveAssid[$assetsDepartV['assid']] = true;
                }
            }
        }

        if (!$backAssid) {
            $result['msg'] = '暂无需处理的借调审批流程';
            $result['code'] = 400;
            return $result;
        }
        $backAssid = array_unique($backAssid);
        $assetsWhere['assid'][] = array('IN', $backAssid);

        if ($hospital_id) {
            $assetsWhere['hospital_id'] = $hospital_id;
        } else {
            //管理员默认情况下的话只能看到自己工作的医院下的设备
            $assetsWhere['hospital_id'] = session('current_hospitalid');
        }
        $assetsWhere['is_delete'] = '0';
        $assets = $this->DB_get_all('assets_info', 'assid', $assetsWhere);
        if ($assets) {
            $assetsAssid = [];
            foreach ($assets as &$assetsAssidV) {
                $assetsAssid[] = $assetsAssidV['assid'];
            }
            $where['A.assid'][] = array('IN', $assetsAssid);
        } else {
            $result['msg'] = '暂无相关数据';
            $result['rows']  = [];
            $result['total'] = 0;
            $result['code'] = 400;
            return $result;
        }

        //获取审批列表信息
        $join = "LEFT JOIN sb_department AS B ON A.apply_departid = B.departid";
        $fileds = 'A.borid,A.assid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,A.borrow_reason,A.estimate_back,A.status,A.examine_status';
        $total = $this->DB_get_count_join('assets_borrow', 'A',$join,$where);
        $data = $this->DB_get_all_join('assets_borrow', 'A',$fileds,$join, $where, '', $sort . ' ' . $order,'');
        if(!$data){
            $result['msg'] = '暂无相关数据';
            $result['rows']  = [];
            $result['total'] = 0;
            $result['code'] = 400;
            return $result;
        }
        //获取设备基本信息
        $assid = [];
        $userid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
            $userid[] = $dataV['apply_userid'];
        }
        $assetsWhere = [];
        $assetsWhere['assid'] = array('IN', $assid);
        $assetsWhere['is_delete'] = '0';
        $fileds = 'departid,assets,assnum,brand,model,status,assid';
        $assets = $this->DB_get_all('assets_info', $fileds, $assetsWhere);
        $assetsData = [];
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assets as &$assetsV) {
            $assetsData[$assetsV['assid']]['department'] = $departname[$assetsV['departid']]['department'];
            $assetsData[$assetsV['assid']]['assets'] = $assetsV['assets'];
            $assetsData[$assetsV['assid']]['assnum'] = $assetsV['assnum'];
            $assetsData[$assetsV['assid']]['brand'] = $assetsV['brand'];
            $assetsData[$assetsV['assid']]['model'] = $assetsV['model'];
        }
        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        $user = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }
        foreach ($data as &$dataV) {
            $dataV['department'] = $assetsData[$dataV['assid']]['department'];
            $dataV['assets'] = $assetsData[$dataV['assid']]['assets'];
            $dataV['assnum'] = $assetsData[$dataV['assid']]['assnum'];
            $dataV['brand'] = $assetsData[$dataV['assid']]['brand'];
            $dataV['model'] = $assetsData[$dataV['assid']]['model'];
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back'] = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time'] = date('Y-m-d H:i',$dataV['apply_time']);
            $dataV['apply_user'] = $userData[$dataV['apply_userid']]['username'];
            //查询审批历史
            $apps = $this->DB_get_all('assets_borrow_approve', '', array('borid' => $dataV['borid']), '', 'level,approve_time asc');
            if ((!$apps && $managerApproveAssid[$dataV['assid']]) or session('isSuper') == C('YES_STATUS')) {
                //未审批,是第一个审批人
                $dataV['Sort'] = 1;
                $dataV['url'] = $this->returnMobileLink('审批', $departApproveBorrowMenu['actionurl'].'?assid='.$dataV['assid'].'&borid='.$dataV['borid'], ' layui-btn-danger');;
                continue;

            }
            if (!$apps && !$managerApproveAssid[$dataV['assid']]) {
                //未审批,不是第一个审批人
                $total--;
                $dataV['Sort'] = 2;
                $dataV['url'] = $this->returnMobileLink('待审批', 'javascript:void(0);', ' layui-btn-disabled');
                continue;
            }else if($apps && $assetsApproveAssid[$dataV['assid']]){
                $dataV['Sort'] = 1;
                $dataV['url'] = $this->returnMobileLink('审批', $departApproveBorrowMenu['actionurl'].'?assid='.$dataV['assid'].'&borid='.$dataV['borid'], ' layui-btn-danger');;
                continue;
            }else{
                $total--;
                $dataV['Sort'] = 2;
                $dataV['url'] = $this->returnMobileLink('已审批', 'javascript:void(0);', 'layui-btn-primary');
            }
        }
        $cmf_arr = array_column($data,'Sort');
        array_multisort($cmf_arr, SORT_ASC, $data);
        $result["total"] = $total;
        $result["code"] = 200;
        $result["rows"] = $data;
        return $result;
    }


    //借入验收列表
    public function borrowInCheckList()
    {
        $page = I('POST.page') ? I('POST.page') : 1;
        $offset = ($page - 1) * C('PAGE_NUMS');
        $order = I('POST.order');
        $sort = I('POST.sort');
        $search = trim(I('POST.search'));
        if ($search) {
            $where[1]['B.assnum'] = ['LIKE', "%$search%"];
            $where[1]['B.assets'] = ['LIKE', "%$search%"];
            $where[1]['A.borrow_num'] = ['LIKE', "%$search%"];
            $where[1]['_logic'] = 'OR';
        }
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = C('PAGE_NUMS');
        }
        if (!$sort) {
            $sort = 'A.borid';
        } else {
            $sort = 'A.' . $sort;
        }
        if (!$order) {
            $order = 'DESC';
        }


        $where['A.status'] = array('EQ', C('BORROW_STATUS_BORROW_IN'));
        if (session('isSuper') != C('YES_STATUS')) {
            if (!session('job_departid')) {
                $result['msg'] = '该用户未分配工作科室';
                $result['code'] = 400;
                return $result;
            }
            $where['A.apply_departid'] = array('IN', session('job_departid'));
        }

        $where['B.is_delete'] = '0';
        $where['B.hospital_id'] = session('current_hospitalid');
        //获取审批列表信息
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fileds = 'A.borid,A.assid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,A.borrow_reason,A.estimate_back,A.status,A.examine_status,A.borrow_in_time,B.departid,B.assets,B.assnum,B.brand,B.model,B.status AS a_status';
        $total = $this->DB_get_count_join('assets_borrow', 'A', $join, $where);
        $data = $this->DB_get_all_join('assets_borrow', 'A', $fileds, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);

        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //获取设备基本信息
        $assid = [];
        $userid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $borid[] = $dataV['borid'];
            $assid[] = $dataV['assid'];
            $userid[] = $dataV['apply_userid'];
        }
        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        $user = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }
        $borrowInCheckMenu = get_menu($this->MODULE, $this->Controller, 'borrowInCheck');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$dataV) {
            $dataV['department'] = $departname[$dataV['departid']]['department'];
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back'] = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time'] = getHandleMinute($dataV['apply_time']);
            $dataV['apply_user'] = $userData[$dataV['apply_userid']]['username'];
            if ($borrowInCheckMenu) {
                $dataV['operation'] = $this->returnMobileLink($borrowInCheckMenu['actionname'], $borrowInCheckMenu['actionurl'] . '?borid=' . $dataV['borid'], ' layui-btn-normal layui-btn-sm accept');
            }
        }
        $result['pages'] = ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result["code"] = 200;
        $result['rows'] = $data;
        $result['page'] = $page;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    //归还验收列表
    public function giveBackCheckList()
    {
        $page = I('POST.page') ? I('POST.page') : 1;
        $offset = ($page - 1) * C('PAGE_NUMS');
        $order = I('POST.order');
        $sort = I('POST.sort');
        $search = trim(I('POST.search'));
        if ($search) {
            $depairWhere['department'] = ['LIKE', "%$search%"];
            $depairWhere['hospital_id'] = ['EQ', session('current_hospitalid')];
            $department = $this->DB_get_all('department', 'departid', $depairWhere);
            if ($department) {
                $departidArr = [];
                foreach ($department as &$one) {
                    $departidArr[] = $one['departid'];
                }
                $where[1]['B.departid'] = ['IN', $departidArr];
            }
            $where[1]['B.assnum'] = ['LIKE', "%$search%"];
            $where[1]['B.assets'] = ['LIKE', "%$search%"];
            $where[1]['A.borrow_num'] = ['LIKE', "%$search%"];
            $where[1]['_logic'] = 'OR';
        }
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = C('PAGE_NUMS');
        }
        if (!$sort) {
            $sort = 'A.borid';
        } else {
            $sort = 'A.' . $sort;
        }
        if (!$order) {
            $order = 'DESC';
        }

        $where['A.status'] = array('EQ', C('BORROW_STATUS_GIVE_BACK'));
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $assetsDepartWhere['departid'] = array('IN', session('departid'));
        $assetsDepartWhere['hospital_id'] = session('current_hospitalid');
        $assetsDepartWhere['is_delete'] = '0';
        $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
        if (!$assetsDepart) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $backAssid = [];
        foreach ($assetsDepart as &$assetsDepartV) {
            $backAssid[] = $assetsDepartV['assid'];
        }
        $where['A.assid'] = array('IN', $backAssid);
        $where['B.is_delete'] = '0';
        //获取审批列表信息
        $fileds = 'B.assnum,B.assid,B.assets,A.borid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,
        A.borrow_reason,A.estimate_back,A.status,A.examine_status,A.borrow_in_time,A.supplement';
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $total = $this->DB_get_count_join('assets_borrow','A',$join, $where);
        $data = $this->DB_get_all_join('assets_borrow','A', $fileds, $join,$where, '', $sort . ' ' . $order, $offset . "," . $limit);

        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        //获取设备基本信息
        $userid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $userid[] = $dataV['apply_userid'];
            $borid[] = $dataV['borid'];
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";

        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        $user = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }

        //获取附属设备明细
        $subsidiaryWhere['borid'] = ['IN', $borid];
        $subsidiaryWhere['is_delete'] = '0';
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price,D.borid';
        $subsidiary = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join, $subsidiaryWhere,'','','');
        $subsidiaryData = [];
        if ($subsidiary) {
            foreach ($subsidiary as &$subV) {
                $subsidiaryData[$subV['borid']][] = $subV;
            }
        }
        $giveBackCheckMenu = get_menu($this->MODULE, $this->Controller, 'giveBackCheck');
        foreach ($data as &$dataV) {
            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back'] = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time'] = getHandleMinute($dataV['apply_time']);
            $dataV['borrow_in_time'] = getHandleMinute($dataV['borrow_in_time']);
            $dataV['apply_user'] = $userData[$dataV['apply_userid']]['username'];
            if ($giveBackCheckMenu) {
                $dataV['operation'] = $this->returnMobileLink($giveBackCheckMenu['actionname'], $giveBackCheckMenu['actionurl'] . '?borid=' . $dataV['borid'], ' layui-btn-normal layui-btn-sm accept');
            }
        }
        $result['pages'] = ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result["code"] = 200;
        $result['rows'] = $data;
        $result['page'] = $page;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
        }
        return $result;
    }

    public function getReminderList(){
        $borid = I('post.borid');
        if ($borid) {
            $where['A.borid'] = $borid;
        }
        $where['A.status'] = array('EQ', C('BORROW_STATUS_GIVE_BACK'));
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $assetsDepartWhere['is_delete'] = '0';
        $assetsDepartWhere['departid'] = array('IN', session('departid'));
        $assetsDepartWhere['hospital_id'] = session('current_hospitalid');
        $assetsDepart = $this->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
        if (!$assetsDepart) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $backAssid = [];
        foreach ($assetsDepart as &$assetsDepartV) {
            $backAssid[] = $assetsDepartV['assid'];
        }
        $where['A.assid'] = array('IN', $backAssid);
        $where['B.is_delete'] = '0';
        $where['estimate_back']=['LT',time()];
        //获取审批列表信息
        $fileds = 'B.assnum,B.assid,B.assets,A.borid,A.borrow_num,A.apply_userid,A.apply_departid,A.apply_time,
        A.borrow_reason,A.estimate_back,A.status,A.examine_status,A.borrow_in_time,A.supplement';
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $data = $this->DB_get_all_join('assets_borrow','A', $fileds, $join,$where,'','','');
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //获取设备基本信息
        $userid = [];
        $borid = [];
        foreach ($data as &$dataV) {
            $userid[] = $dataV['apply_userid'];
            $borid[] = $dataV['borid'];
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";

        //获取对应的申请人名称
        $userWhere['userid'] = array('IN', $userid);
        $user = $this->DB_get_all('user', 'userid,username', $userWhere);
        $userData = [];
        foreach ($user as &$userV) {
            $userData[$userV['userid']]['username'] = $userV['username'];
        }

        //获取附属设备明细
        $subsidiaryWhere['A.is_delete'] = '0';
        $subsidiaryWhere['borid'] = ['IN', $borid];
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price,D.borid';
        $subsidiary = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join, $subsidiaryWhere,'','','');
        $subsidiaryData = [];
        if ($subsidiary) {
            foreach ($subsidiary as &$subV) {
                $subsidiaryData[$subV['borid']][] = $subV;
            }
        }
        foreach ($data as &$dataV) {

            if($dataV['estimate_back']<time()){
                $dataV['overdue']=timediff($dataV['estimate_back'],time());
                if($dataV['overdue']>24){
                    $dataV['overdue']=(sprintf('%.2f',$dataV['overdue']/24)).'天';
                }else{
                    $dataV['overdue'].='小时';
                }
            }

            $dataV['apply_department'] = $departname[$dataV['apply_departid']]['department'];
            $dataV['estimate_back'] = getHandleMinute($dataV['estimate_back']);
            $dataV['apply_time'] = getHandleMinute($dataV['apply_time']);
            $dataV['borrow_in_time'] = getHandleMinute($dataV['borrow_in_time']);
            $dataV['apply_user'] = $userData[$dataV['apply_userid']]['username'];
        }
        $result["code"] = 200;
        $result['rows'] = $data;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * 获取设备基本信息
     * @param $assid int 设备id
     * @return array
     */
    public function getAssetsBasic($assid)
    {
        $where['assid'] = array('EQ', $assid);
        $where['is_delete'] = '0';
        $files = 'assid,catid,assnum,assets,helpcatid,status,brand,model,unit,serialnum,assetsrespon,departid,address,buy_price';
        $assets = $this->DB_get_one('assets_info', $files, $where);
        $files = 'afid,factory,factory_user,factory_tel,supplier,supp_user,supp_tel,repair,repa_user,repa_tel';
        $factory = $this->DB_get_one('assets_factory', $files, $where);
        $departname = [];
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
     * @param $borid int 借调id
     * @return array
     * */
    public function getBorrowBasic($borid)
    {
        $where['borid'] = array('EQ', $borid);
        $files = 'assid,borid,borrow_num,apply_userid,apply_departid,borrow_reason,estimate_back,apply_time,status,examine_status,
        not_apply_time,end_reason,borrow_in_time,borrow_in_userid,give_back_time,give_back_userid,end_reason,score_value,
        score_remark,examine_status,status,examine_time,supplement';
        $borrow = $this->DB_get_one('assets_borrow', $files, $where);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";


        if($borrow['estimate_back']<time()){
            $borrow['overdue']=timediff($borrow['estimate_back'],time());
            if($borrow['overdue']>24){
                $borrow['overdue']=(sprintf('%.2f',$borrow['overdue']/24)).'天';
            }else{
                $borrow['overdue'].='小时';
            }
        }


        $borrow['department'] = $departname[$borrow['apply_departid']]['department'];
        $borrow['estimate_back'] = getHandleMinute($borrow['estimate_back']);
        $borrow['apply_time'] = getHandleMinute($borrow['apply_time']);
        $borrow['not_apply_time'] = getHandleMinute($borrow['not_apply_time']);
        $borrow['borrow_in_time'] = getHandleMinute($borrow['borrow_in_time']);
        $borrow['give_back_time'] = getHandleMinute($borrow['give_back_time']);
        $borrow['examine_time'] = getHandleMinute($borrow['examine_time']);
        $userId[] = $borrow['apply_userid'];

        if ($borrow['borrow_in_userid']) {
            $userId[] = $borrow['borrow_in_userid'];
        }

        if ($borrow['give_back_userid']) {
            $userId[] = $borrow['give_back_userid'];
        }


        $user = $this->DB_get_All('user', 'userid,username', array('userid' => array('IN', $userId)));

        $subsidiary=$this->DB_get_count('assets_borrow_detail',$where);
        $borrow['subsidiary_num']=$subsidiary;

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

    /**
     * 获取借调审核基本信息
     * @param $borid int 借调id
     * @return array
     * */
    public function getBorrowApprovBasic($borid)
    {
        $where['borid'] = array('EQ', $borid);
        $approve = $this->DB_get_all('assets_borrow_approve', '', $where, '', 'level,approve_time asc');
        if ($approve) {
            $userId = [];
            foreach ($approve as &$approveV) {
                $userId[] = $approveV['approve_userid'];
                $approveV['approve_time'] = getHandleMinute($approveV['approve_time']);
                switch ($approveV['approve_status']) {
                    case C('STATUS_APPROE_SUCCESS'):
                        $approveV['is_adoptName'] = '<span style = "color:green" >通过</span >';
                        $approveV['is_adoptNameNoColor'] = '通过</span > ';
                        break;
                    case C('STATUS_APPROE_FAIL'):
                        $approveV['is_adoptName'] = '<span class="rquireCoin" >不通过</span >';
                        $approveV['is_adoptNameNoColor'] = '不通过';
                        break;
                    default :
                        $approveV['is_adoptName'] = '未审核';
                }
            }

            $user = $this->DB_get_All('user', 'userid,username', array('userid' => array('IN', $userId)));
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
     * @param $borid int 借调记录id
     * @return array
     * */
    public function getSubsidiaryBasic($borid)
    {
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price';
        $data = $this->DB_get_all_join('assets_borrow_detail', 'D', $fields, $join, "borid=$borid and A.is_delete='0'",'','','');
        return $data;
    }
}
