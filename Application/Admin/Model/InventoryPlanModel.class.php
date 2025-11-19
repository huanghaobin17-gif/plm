<?php

namespace Admin\Model;

use App\Service\UserInfo\UserInfo;

class InventoryPlanModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'inventory_plan';
    protected $MODULE = 'Inventory';
    protected $Controller = 'inventoryPlan';

    public function getList()
    {
        $limit            = I('post.limit') ? I('post.limit') : 10;
        $page             = I('post.page') ? I('post.page') : 1;
        $offset           = ($page - 1) * $limit;
        $order            = I('post.order') ? I('post.order') : 'desc';
        $sort             = I('post.sort') ? I('post.sort') : 'inventory_plan_id';
        $sort             = I('post.sort') ? I('post.sort') : 'inventory_plan_id';
        $hospital_id      = session('current_hospitalid');
        $current_username = session('username');
        $isSuper          = session('isSuper');

        $inventory_plan_ids    = I('post.inventory_plan_ids');
        $inventory_plan_no     = I('post.inventory_plan_no');
        $inventory_plan_name   = I('post.inventory_plan_name');
        $inventory_plan_status = I('post.inventory_plan_status');
        $is_push               = I('post.is_push');

        $asset_num = I('post.assetnum');

        $where['hospital_id'] = $hospital_id;
        $where['is_delete']   = 0;


        if (!$isSuper) {
            $map[]             = sprintf('json_contains(inventory_users,\'"%s"\')', $current_username);
            $map['add_user']   = $current_username;
            $map['_logic']     = 'or';
            $where['_complex'] = $map;
        }

        if ($inventory_plan_no) {
            $where['inventory_plan_no'] = ['like', '%' . $inventory_plan_no . '%'];
        }
        if ($inventory_plan_name) {
            $where['inventory_plan_name'] = ['like', '%' . $inventory_plan_name . '%'];
        }

        if (strlen($inventory_plan_status) > 0 || is_array($inventory_plan_status)) {
            $where['inventory_plan_status'] = ['in', $inventory_plan_status];
        }

        if (isset($is_push) && strlen($is_push) > 0) {
            $where['is_push'] = $is_push;
        }

        if ($asset_num) {
            $inventoryPlanIds           = $this->DB_get_all('inventory_plan_assets', 'inventory_plan_id',
                ['assetnum' => $asset_num]);
            $where['inventory_plan_id'] = ['in', array_column($inventoryPlanIds, 'inventory_plan_id')];
        }
        if ($inventory_plan_ids) {
            $where['inventory_plan_id'] = ['in', $inventory_plan_ids];
        }

        $fields          = '
            inventory_plan_id,
            hospital_id,
            inventory_plan_no,
            inventory_plan_name,
            inventory_plan_start_time,
            inventory_plan_end_time,
            inventory_plan_status,
            inventory_users,
            is_push,
            push_system_name,
            push_status,
            push_time,
            receive_status,
            receive_time,
            error_msg,
            add_user,
            add_time,
            remark
        ';
        $total           = $this->DB_get_count($this->tableName, $where);
        $inventory_plans = $this->DB_get_all($this->tableName, $fields, $where, '',
            $sort . ' ' . $order, $offset . "," . $limit);
        if (!$inventory_plans) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        $inventory_plan_ids    = array_column($inventory_plans, 'inventory_plan_id');
        $inventory_plan_assets = $this->DB_get_all('inventory_plan_assets',
            'inventory_plan_id,inventory_status', ['inventory_plan_id' => ['in', $inventory_plan_ids]]);


        foreach ($inventory_plans as &$inventory_plan) {
            $inventory_plan['inventory_users'] = json_decode($inventory_plan['inventory_users'], true);

            $inventory_plan['push_error']   = $inventory_plan['receive_error'] = null;
            $inventory_plan['is_push_name'] = $inventory_plan['is_push'] ? '自动' : '手动';
            if ($inventory_plan['is_push']) {
                $inventory_plan['push_status_name']    = $inventory_plan['push_status'] ? '成功' : '失败';
                $inventory_plan['receive_status_name'] = $inventory_plan['receive_status'] ? '成功' : '失败';
            }

            // 错误信息
            if ($inventory_plan['error_msg']) {
                if (isset($inventory_plan['error_msg']['push_error'])) {
                    $inventory_plan['push_error'] = $inventory_plan['error_msg']['push_error'];
                }
                if (isset($inventory_plan['error_msg']['receive_error'])) {
                    $inventory_plan['receive_error'] = $inventory_plan['error_msg']['receive_error'];
                }
            }

            // 操作
            $html                      = '<div class="layui-btn-group">';
            $showInventoryPlanUrl      = C('ADMIN_NAME') . '/InventoryPlan/showInventoryPlan?inventory_plan_id=' . $inventory_plan['inventory_plan_id'];
            $editInventoryPlanUrl      = C('ADMIN_NAME') . '/InventoryPlan/editInventoryPlan?inventory_plan_id=' . $inventory_plan['inventory_plan_id'];
            $saveOrEndInventoryPlanUrl = C('ADMIN_NAME') . '/InventoryPlan/saveOrEndInventoryPlan?inventory_plan_id=' . $inventory_plan['inventory_plan_id'];


            if ($inventory_plan['inventory_plan_status'] == 0) {
                $html .= $this->returnListLink('编辑', $editInventoryPlanUrl, 'editInventoryPlan',
                    C('BTN_CURRENCY'));
            } elseif ($inventory_plan['inventory_plan_status'] == 1) {
                $html .= $this->returnListLink('盘点', $saveOrEndInventoryPlanUrl, 'saveOrEndInventoryPlan',
                    C('BTN_CURRENCY'));
            } elseif ($inventory_plan['inventory_plan_status'] == 2) {
                $html .= $this->returnListLink('继续盘点', $saveOrEndInventoryPlanUrl, 'saveOrEndInventoryPlan',
                    C('BTN_CURRENCY'));
            } else {
                $html .= $this->returnListLink('查看', $showInventoryPlanUrl, 'showInventoryPlan',
                    C('BTN_CURRENCY') . ' layui-btn-primary');
            }
            $html                        .= '</div>';
            $inventory_plan['operation'] = $html;

            // 盘点状态【0-待发布，1-待盘点，2-正在(暂存)盘点，3-审核中，4-已结束】
            switch ($inventory_plan['inventory_plan_status']) {
                case  0 :
                    $inventory_plan['inventory_plan_status_name'] = '待发布';
                    break;
                case 1:
                    $inventory_plan['inventory_plan_status_name'] = '待盘点';
                    break;
                case 2:
                    $inventory_plan['inventory_plan_status_name'] = '正在盘点';
                    break;
                case 3:
                    $inventory_plan['inventory_plan_status_name'] = '审核中';
                    break;
                case 4:
                    $inventory_plan['inventory_plan_status_name'] = '已结束';
                    break;
            }

            $inventory_plan['inventory_plan_asset_count']                 = 0;
            $inventory_plan['inventory_plan_asset_status_not_count']      = 0;
            $inventory_plan['inventory_plan_asset_status_normal_count']   = 0;
            $inventory_plan['inventory_plan_asset_status_abnormal_count'] = 0;
            foreach ($inventory_plan_assets as $inventory_plan_asset) {
                if ($inventory_plan_asset['inventory_plan_id'] === $inventory_plan['inventory_plan_id']) {
                    $inventory_plan['inventory_plan_asset_count']++;
                    switch ($inventory_plan_asset['inventory_status']) {
                        case 0:
                            $inventory_plan['inventory_plan_asset_status_not_count']++;
                            break;
                        case 1:
                            $inventory_plan['inventory_plan_asset_status_normal_count']++;
                            break;
                        case 2:
                            $inventory_plan['inventory_plan_asset_status_abnormal_count']++;
                            break;
                    }
                }
            }


        }

        $result['total']  = (int)$total;
        $result["offset"] = $offset;
        $result["limit"]  = (int)$limit;
        $result["code"]   = 200;
        $result['rows']   = $inventory_plans;
        return $result;
    }

    /**
     * 新增设备列表数据
     */
    public function addAssetsList()
    {
        $limit       = I('post.limit') ? I('post.limit') : 10;
        $page        = I('post.page') ? I('post.page') : 1;
        $offset      = ($page - 1) * $limit;
        $hospital_id = session('current_hospitalid');
        $departid    = session('departid');

        $assName                    = I('POST.assName');
        $assNum                     = I('POST.assNum');
        $assetsCat                  = I('POST.assCat');
        $dids                       = I('POST.departids');
        $financeid                  = I('post.financeid');
        $is_bind_inventory_label_id = I('POST.is_bind_inventory_label_id');
        $removedata                 = I('POST.removedata');


        $where['departid'][]  = ['in', $departid];
        $where['hospital_id'] = $hospital_id;
        $where['status']      = C('ASSETS_STATUS_USE');
        $where['is_delete']   = '0';

        if ($removedata) {
            $where['assnum'][] = ['NOT IN', $removedata];
        }
        //设备编号搜索
        if ($assNum) {
            $where['assnum'][] = ['eq', $assNum];
        }

        // 设备名称
        if ($assName) {
            $where['assets'] = ['LIKE', '%' . $assName . '%'];
        }
        //财务分类
        if (isset($financeid) && strlen($financeid) > 0) {
            $where['financeid'] = $financeid;
        }
        //分类搜索
        if ($assetsCat) {
            $caModel              = new CategoryModel();
            $catwhere['category'] = ['like', "%$assetsCat%"];
            $catids               = $caModel->getCatidsBySearch($catwhere);
            $where['catid']       = ['IN', $catids];
        }
        //科室搜索
        if ($dids != '') {
            $where['departid'][] = ['in', $dids];
        }

        if ($is_bind_inventory_label_id) {
            $where['inventory_label_id'] = ['exp', 'is not null'];
        }

        $fileds = '
        assid,
        hospital_id,
        assnum,
        assets,
        departid,
        catid,
        address,
        financeid,
        model,
        buy_price,
        inventory_label_id,
        is_firstaid,
        is_special,
        is_metering,
        is_qualityAssets,
        is_benefit,
        is_patrol,
        status
        ';

        $total     = $this->DB_get_count('assets_info', $where);
        $assets    = $this->DB_get_all('assets_info', $fileds, $where, '', 'assid DESC', $offset . "," . $limit);
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$assets) {
            $result["total"] = 0;
            $result['msg']   = '暂无相关数据';
            $result['code']  = 400;
            return $result;
        }
        $departname = [];
        $catname    = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";

        $asModel        = new AssetsInfoModel();
        $assets_finance = $asModel->getBaseSettingAssets('assets_finance');

        foreach ($assets as &$asset) {
            $asset['is_bind_inventory_label_id'] = empty($asset['inventory_label_id']) ? "未绑定" : "已绑定";
            if (!$showPrice) {
                $asset['buy_price'] = '***';
            }
            $asset['department'] = $departname[$asset['departid']]['department'];
            $asset['category']   = $catname[$asset['catid']]['category'];

            switch ($asset['status']) {
                case C('ASSETS_STATUS_USE'):
                    $asset['status_name'] = '<span style="color: #5FB878;">在用</span>';
                    break;
            }
            $asset['financeid'] = $assets_finance[$asset['financeid']];

            if ($asset['is_firstaid'] == C('ASSETS_FIRST_CODE_YES')) {
                $asset['type_name'] = C('ASSETS_FIRST_CODE_YES_NAME');
            }
            if ($asset['is_special'] == C('ASSETS_SPEC_CODE_YES')) {
                $asset['type_name'] .= ',' . C('ASSETS_SPEC_CODE_YES_NAME');
            }
            if ($asset['is_metering'] == C('ASSETS_METER_CODE_YES')) {
                $asset['type_name'] = C('ASSETS_METER_CODE_YES_NAME');
            }
            if ($asset['is_qualityAssets'] == C('ASSETS_QUALITY_CODE_YES')) {
                $asset['type_name'] .= ',' . C('ASSETS_QUALITY_CODE_YES_NAME');
            }
            if ($asset['is_benefit'] == C('ASSETS_BENEFIT_CODE_YES')) {
                $asset['type_name'] .= ',' . C('ASSETS_BENEFIT_CODE_YES_NAME');
            }
            if ($asset['is_patrol'] == C('ASSETS_PATROL_CODE_YES')) {
                $asset['type_name'] .= ',' . C('ASSETS_PATROL_CODE_YES_NAME');
            }
            $asset['type_name'] = ltrim($asset['type_name'], ",");
            $asset['operation'] = $this->returnListLink('纳入', '', 'add', C('BTN_CURRENCY'));

        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $assets;
        return $result;
    }

    /**
     * 新增设备列表数据
     */
    public function delAssetsList()
    {
        $limit       = I('post.limit') ? I('post.limit') : 10;
        $page        = I('post.page') ? I('post.page') : 1;
        $offset      = ($page - 1) * $limit;
        $hospital_id = session('current_hospitalid');
        $departid    = session('departid');

        $assName                    = I('POST.assName');
        $assNum                     = I('POST.assNum');
        $assetsCat                  = I('POST.aslsCat');
        $dids                       = I('POST.departids');
        $financeid                  = I('post.assets_finance');
        $is_bind_inventory_label_id = I('POST.is_bind_inventory_label_id');
        $removedata                 = I('POST.removedata');


        $where['departid'][]  = ['in', $departid];
        $where['hospital_id'] = $hospital_id;
        $where['status']      = C('ASSETS_STATUS_USE');
        $where['is_delete']   = '0';

        if ($removedata) {
            $where['assnum'] = ['IN', $removedata];
        } elseif (session('notAssnums')) {
            $where['assnum'] = ['IN', session('notAssnums')];
        } else {
            $result["total"] = 0;
            $result['msg']   = '暂无相关数据';
            $result['code']  = 400;
            return $result;
        }


        $fileds = '
        assid,
        hospital_id,
        assnum,
        assets,
        departid,
        catid,
        address,
        financeid,
        model,
        buy_price,
        inventory_label_id,
        is_firstaid,
        is_special,
        is_metering,
        is_qualityAssets,
        is_benefit,
        is_patrol,
        status
        ';

        $total     = $this->DB_get_count('assets_info', $where);
        $assets    = $this->DB_get_all('assets_info', $fileds, $where, '', 'assid DESC', $offset . "," . $limit);
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$assets) {
            $result["total"] = 0;
            $result['msg']   = '暂无相关数据';
            $result['code']  = 400;
            return $result;
        }
        $departname = [];
        $catname    = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";


        $asModel        = new AssetsInfoModel();
        $assets_finance = $asModel->getBaseSettingAssets('assets_finance');

        foreach ($assets as &$asset) {
            $asset['is_bind_inventory_label_id'] = empty($asset['inventory_label_id']) ? "未绑定" : "已绑定";
            if (!$showPrice) {
                $asset['buy_price'] = '***';
            }
            $asset['department'] = $departname[$asset['departid']]['department'];
            $asset['category']   = $catname[$asset['catid']]['category'];

            $asset['financeid'] = $assets_finance[$asset['financeid']];

            switch ($asset['status']) {
                case C('ASSETS_STATUS_USE'):
                    $asset['status_name'] = '<span style="color: #5FB878;">在用</span>';
                    break;
            }

            if ($asset['is_firstaid'] == C('ASSETS_FIRST_CODE_YES')) {
                $asset['type_name'] = C('ASSETS_FIRST_CODE_YES_NAME');
            }
            if ($asset['is_special'] == C('ASSETS_SPEC_CODE_YES')) {
                $asset['type_name'] .= ',' . C('ASSETS_SPEC_CODE_YES_NAME');
            }
            if ($asset['is_metering'] == C('ASSETS_METER_CODE_YES')) {
                $asset['type_name'] = C('ASSETS_METER_CODE_YES_NAME');
            }
            if ($asset['is_qualityAssets'] == C('ASSETS_QUALITY_CODE_YES')) {
                $asset['type_name'] .= ',' . C('ASSETS_QUALITY_CODE_YES_NAME');
            }
            if ($asset['is_benefit'] == C('ASSETS_BENEFIT_CODE_YES')) {
                $asset['type_name'] .= ',' . C('ASSETS_BENEFIT_CODE_YES_NAME');
            }
            if ($asset['is_patrol'] == C('ASSETS_PATROL_CODE_YES')) {
                $asset['type_name'] .= ',' . C('ASSETS_PATROL_CODE_YES_NAME');
            }
            $asset['type_name'] = ltrim($asset['type_name'], ",");
            $asset['operation'] = $this->returnListLink('移除', '', 'del', C('BTN_CURRENCY') . ' layui-btn-danger');
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $assets;
        return $result;
    }

    /**
     *  获取所有设备
     */
    public function addAssetAll()
    {
        $removedata           = I('get.removedata');
        $where['is_delete']   = C('NO_STATUS');
        $where['status']      = C('ASSETS_STATUS_USE');
        $where['hospital_id'] = session('current_hospitalid');
        if ($removedata) {
            $where['assnum'] = ['not IN', $removedata];
        }
        $where['departid'] = ['IN', session('departid')];
        $assets            = $this->DB_get_all('assets_info', 'assid,assets,assnum,departid,address,financeid', $where,
            '',
            'assid desc', '');
        $res               = [];
        $i                 = 0;
        foreach ($assets as $k => $v) {
            $res[$i]['assid']     = $v['assid'];
            $res[$i]['assets']    = $v['assets'];
            $res[$i]['assnum']    = $v['assnum'];
            $res[$i]['departid']  = $v['departid'];
            $res[$i]['address']   = $v['address'];
            $res[$i]['financeid'] = $v['financeid'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        return $arr;
    }

    /**
     * 详情
     *
     * @return array
     */
    public function show()
    {
        $inventory_plan_id = I('request.inventory_plan_id/d');
        $inventory_plan    = $this->detail($inventory_plan_id);

        if (!$inventory_plan) {
            return ['status' => -1, 'msg' => '数据不存在！'];
        }
        $inventory_plan_assets = $this->DB_get_all('inventory_plan_assets',
            '', ['inventory_plan_id' => $inventory_plan['inventory_plan_id']], '', 'inventory_status desc');


        $inventory_plan['is_push_name'] = $inventory_plan['is_push'] ? '是' : '否';

        // 盘点状态【0-待发布，1-待盘点，2-正在(暂存)盘点，3-审核中，4-已结束】
        switch ($inventory_plan['inventory_plan_status']) {
            case  0 :
                $inventory_plan['inventory_plan_status_name'] = '待发布';
                break;
            case 1:
                $inventory_plan['inventory_plan_status_name'] = '待盘点';
                break;
            case 2:
                $inventory_plan['inventory_plan_status_name'] = '正在盘点';
                break;
            case 3:
                $inventory_plan['inventory_plan_status_name'] = '审核中';
                break;
            case 4:
                $inventory_plan['inventory_plan_status_name'] = '已结束';
                break;
        }

        $data['inventory_plan'] = $inventory_plan;

        //加载缓存
        $catname     = [];
        $departname  = [];
        $baseSetting = [];
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";

        foreach ($inventory_plan_assets as &$inventory_plan_asset) {
            // 如果有设备编号，取设备编号的值
            if ($inventory_plan_asset['assetnum']) {
                $asset_info                         = $this->DB_get_one('assets_info',
                    'assets,departid,address,financeid,assorignum,assorignum_spare',
                    ['assnum' => $inventory_plan_asset['assetnum']]);
                $inventory_plan_asset['assets']     = $asset_info['assets'];
                $inventory_plan_asset['assorignum_spare']     = $asset_info['assorignum_spare'];
                $inventory_plan_asset['assorignum']     = $asset_info['assorignum'];
                $inventory_plan_asset['departid']   = $asset_info['departid'];
                $inventory_plan_asset['department'] = $departname[$asset_info['departid']]['department'];
                $inventory_plan_asset['address']    = $asset_info['address'];
                $inventory_plan_asset['financeid']  = $asset_info['financeid'];
                $inventory_plan_asset['finance']    = $baseSetting['assets']['assets_finance']['value'][$asset_info['financeid']];
            }
        }

        $data['inventory_plan_assets'] = $inventory_plan_assets;

        //查询审批信息
        $data['apps'] = $this->getApproveInfo($inventory_plan['inventory_plan_id']);
        if ($inventory_plan['inventory_plan_status'] == 1 && (empty($inventory_plan['inventory_plan_start_time'])
                || $inventory_plan['inventory_plan_start_time'] === '0000-00-00 00:00:00')) {
            $updateData = [
                'inventory_plan_start_time' => date('Y-m-d H:i:s'),
            ];
            $this->updateData($this->tableName, $updateData, ['inventory_plan_id' => $inventory_plan_id]);
        }
        return $data;
    }


    /**
     * 批量删除
     *
     * @param  $inventory_plan_ids
     *
     * @return array
     */
    public function batchDel($inventory_plan_ids)
    {
        $where['hospital_id']       = session('current_hospitalid');
        $where['is_delete']         = 0;
        $where['inventory_plan_id'] = ['in', $inventory_plan_ids];
        $inventory_plans            = $this->DB_get_all($this->tableName, '', $where);

        if (!$inventory_plans) {
            return ['status' => -1, 'msg' => '数据不存在！'];
        }

        $this->startTrans();
        foreach ($inventory_plans as $inventory_plan) {
            $result = $this->del($inventory_plan);
            if ($result['status'] === -1) {
                $this->rollback();
                return ['status' => -1, 'msg' => '只能删除待发布的盘点数据，请重新选择！'];
            }
        }
        $this->commit();
        return ['status' => 1, 'msg' => '批量删除盘点计划成功'];
    }

    /**
     * 删除
     *
     * @param $inventory_plan
     *
     * @return array
     */
    public function del($inventory_plan)
    {
        if ($inventory_plan['inventory_plan_status'] != 0) {
            return ['status' => -1, 'msg' => '只能删除待发布的盘点数据'];
        }
        $this->where(['inventory_plan_id' => $inventory_plan['inventory_plan_id']])->delete();
        $this->deleteData('inventory_plan_assets', ['inventory_plan_id' => $inventory_plan['inventory_plan_id']]);
        return ['status' => 1, 'msg' => '删除盘点计划成功'];
    }


    /**
     * 新增
     *
     * @return array
     */
    public function addPlan()
    {
        $data = $this->processAttributes();
        if ($data['status'] === -1) {
            return $data;
        }
        $data['add_user']        = session('username');
        $data['add_time']        = date('Y-m-d H:i:s');
        $data['inventory_users'] = str_json_encode($data['inventory_users']);

        $assets = array_pop_key($data, 'assets');

        $this->startTrans();
        $inventory_plan_id = $this->insertData($this->tableName, $data);
        if (!$inventory_plan_id) {
            $this->rollback();
            return ['status' => -1, 'msg' => '保存失败'];
        }

        foreach ($assets as $asset) {
            $assetsData[] = [
                'assetnum'          => $asset['assnum'],
                'inventory_plan_id' => $inventory_plan_id,
                'inventory_status'  => 0,
                'is_plan'           => 1,
                'add_time'          => date('Y-m-d H:i:s'),
            ];
        }
        $result = $this->insertDataALL('inventory_plan_assets', $assetsData);
        if (!$result) {
            $this->rollback();
            return ['status' => -1, 'msg' => '保存失败'];
        }
        $this->commit();

        return ['status' => 1, 'msg' => '保存成功'];
    }

    /**
     * 编辑
     *
     * @return array
     */
    public function editPlan()
    {
        $inventory_plan_id = I('post.inventory_plan_id/d');
        $inventory_plan    = $this->detail($inventory_plan_id);

        if (!$inventory_plan) {
            return ['status' => -1, 'msg' => '计划不存在！'];
        }
        if ($inventory_plan['inventory_plan_status'] != 0) {
            return ['status' => -1, 'msg' => '只有盘点状态为未发布才可以编辑'];
        }
        $data = $this->processAttributes($inventory_plan);
        if ($data['status'] == -1) {
            return $data;
        }
        $data['edit_user']       = session('username');
        $data['edit_time']       = date('Y-m-d H:i:s');
        $data['inventory_users'] = str_json_encode($data['inventory_users']);
        $assets                  = array_pop_key($data, 'assets');

        $this->startTrans();
        $result = $this->updateData($this->tableName, $data, ['inventory_plan_id' => $inventory_plan_id]);
        if (!$result) {
            $this->rollback();
            return ['status' => -1, 'msg' => '编辑失败'];
        }

        $this->deleteData('inventory_plan_assets', ['inventory_plan_id' => $inventory_plan_id]);
        foreach ($assets as $asset) {
            $assetsData[] = [
                'assetnum'          => $asset['assnum'],
                'inventory_plan_id' => $inventory_plan_id,
                'inventory_status'  => 0,
                'is_plan'           => 1,
                'add_time'          => date('Y-m-d H:i:s'),
            ];
        }
        $result = $this->insertDataALL('inventory_plan_assets', $assetsData);
        if (!$result) {
            $this->rollback();
            return ['status' => -1, 'msg' => '编辑失败'];
        }


        $this->commit();

        return ['status' => 1, 'msg' => '编辑成功'];
    }

    /**
     * 批量发布
     *
     * @param  $inventory_plan_ids
     *
     * @return array
     */
    public function batchRelease($inventory_plan_ids)
    {
        $where['hospital_id']       = session('current_hospitalid');
        $where['is_delete']         = 0;
        $where['inventory_plan_id'] = ['in', $inventory_plan_ids];
        $inventory_plans            = $this->DB_get_all($this->tableName, '', $where);

        if (!$inventory_plans) {
            return ['status' => -1, 'msg' => '数据不存在！'];
        }

        $this->startTrans();
        foreach ($inventory_plans as $inventory_plan) {
            $result = $this->release($inventory_plan);
            if ($result['status'] === -1) {
                $this->rollback();
                return ['status' => -1, 'msg' => '只能发布状态为 待发布 的盘点数据，请重新选择！'];
            }
        }
        $this->commit();
        return ['status' => 1, 'msg' => '批量发布盘点计划成功'];
    }

    /**
     * 发布
     */
    public function release($inventory_plan)
    {
        if ($inventory_plan['inventory_plan_status'] != 0) {
            return ['status' => -1, 'msg' => '只能发布状态为 待发布 的盘点数据'];
        }

        $this->updateData($this->tableName, ['inventory_plan_status' => 1],
            ['inventory_plan_id' => $inventory_plan['inventory_plan_id']]);
        $result = ['status' => 1, 'msg' => '发布成功'];

        // 如果是推送，则推送到下游系统
        if ($inventory_plan['is_push']) {
            $result = $this->pushSystem($inventory_plan);
        }

        return $result;
    }


    /**
     * 暂存或结束盘点
     *
     * @return array
     */
    public function saveOrEnd()
    {
        $inventory_plan_id     = I('post.inventory_plan_id/d');
        $operate               = I('post.operate/s'); // save-暂时保存 end-结束盘点
        $inventory_plan_assets = I('post.inventory_plan_assets');
        $date                  = date('Y-m-d H:i:s');
        $inventory_plan        = $this->detail($inventory_plan_id);
        $approve_data          = null; // 审核数据
        if (!in_array($inventory_plan['inventory_plan_status'], [1, 2])) {
            return ['status' => -1, 'msg' => '只有盘点状态为 未盘点 或 正在盘点 时才可以进行盘点'];
        }

        if ($inventory_plan['is_push'] == 1) {
            return ['status' => -1, 'msg' => '盘点计划为自动，不可手动盘点！'];
        }

        foreach ($inventory_plan_assets as $inventory_plan_asset) {
            if (!$inventory_plan_asset['assetnum']) {
                return ['status' => -1, 'msg' => '设备编号不能为空！'];
            }
        }

        // 结束盘点需要校验
        if ($operate == 'end') {
            foreach ($inventory_plan_assets as $inventory_plan_asset) {
                // 校验实盘状态
                $is_abnormal = $inventory_plan_asset['inventory_status'] == 2;
                if ($inventory_plan_asset['inventory_status'] == 0) {
                    return ['status' => -1, 'msg' => '存在未盘点的设备，请先盘点！'];
                }

                if ($is_abnormal && empty($inventory_plan_asset['reason'])) {
                    return ['status' => -1, 'msg' => '当实盘状态为异常时，原因必填！'];
                }
                if (!$inventory_plan_asset['inventory_user']) {
                    return ['status' => -1, 'msg' => '盘点员不能为空'];
                }

                if (!in_array($inventory_plan_asset['inventory_user'], $inventory_plan['inventory_users'])) {
                    return ['status' => -1, 'msg' => '盘点员不在计划内'];
                }

                if ($approve_data == null && ($inventory_plan_asset['inventory_status'] == 2 || !$inventory_plan_asset['inventory_plan_assets_id'])) {
                    $approve_data = $this->approve($inventory_plan);
                    if (!$approve_data) {
                        return ['status' => -1, 'msg' => '未设置审批流程，请先设置审批流程'];
                    }
                }
            }
            if ($approve_data) {
                $update_data = $approve_data;
            } else {
                $update_data = [
                    'inventory_plan_status'   => 4,
                    'inventory_plan_end_time' => $date,
                ];
            }
        } else {
            $update_data = ['inventory_plan_status' => 2];
        }

        // 更新 盘点、审核状态
        $this->updateData($this->tableName,
            $update_data,
            ['inventory_plan_id' => $inventory_plan_id]);


        // 获取关联数据
        $inventory_plan_assets_ids = array_filter(array_column($inventory_plan_assets, 'inventory_plan_assets_id'));

        // 删除无关联数据
        $this->deleteData('inventory_plan_assets',
            [
                'inventory_plan_id'        => $inventory_plan['inventory_plan_id'],
                'is_plan'                  => 0,
                'inventory_plan_assets_id' => ['not in', $inventory_plan_assets_ids],
            ]);

        $inventory_plan_assets_list = [];
        foreach ($this->DB_get_all('inventory_plan_assets', '',
            ['inventory_plan_assets_id' => ['in', $inventory_plan_assets_ids]]) as $v) {
            $inventory_plan_assets_list[$v['inventory_plan_assets_id']] = $v;
        }

        // 过滤参数
        foreach ($inventory_plan_assets as &$inventory_plan_asset) {
            if ($inventory_plan_asset['inventory_plan_assets_id']) {
                $inventory_plan_asset_info = $inventory_plan_assets_list[$inventory_plan_asset['inventory_plan_assets_id']];
                $inventory_plan_asset      = array_merge($inventory_plan_asset_info,
                    array_keep_keys($inventory_plan_asset,
                        ['inventory_plan_assets_id', 'assetnum', 'inventory_status', 'reason', 'inventory_user']));
            } else {
                $inventory_plan_asset = array_keep_keys($inventory_plan_asset,
                    [
                        'assetnum',
                        'reason',
                        'assets',
                        'departid',
                        'address',
                        'financeid',
                        'inventory_user',
                    ]);
            }
        }

        // 更新数据
        unset($inventory_plan_asset);
        foreach ($inventory_plan_assets as $inventory_plan_asset) {

            if ($inventory_plan_asset['inventory_status'] == 2) {
                $inventory_plan_asset['result'] = '报废';
            }
            if ($inventory_plan_asset['inventory_plan_assets_id']) {
                $this->updateData('inventory_plan_assets', $inventory_plan_asset,
                    ['inventory_plan_assets_id' => $inventory_plan_asset['inventory_plan_assets_id']]);
            } else {
                $this->insertData('inventory_plan_assets',
                    [
                        'inventory_plan_id' => $inventory_plan['inventory_plan_id'],
                        'assetnum'          => $inventory_plan_asset['assetnum'],
                        'is_plan'           => 0,
                        'inventory_status'  => $inventory_plan_asset['inventory_status'],
                        'assets'            => $inventory_plan_asset['assets'],
                        'reason'            => $inventory_plan_asset['reason'],
                        'inventory_user'    => $inventory_plan_asset['inventory_user'],
                        'result'            => $inventory_plan_asset['result'],
                        'departid'          => $inventory_plan_asset['departid'],
                        'address'           => $inventory_plan_asset['address'],
                        'catid'             => $inventory_plan_asset['catid'],
                        'add_time'          => $date,
                    ]);
            }

        }
        return ['status' => 1, 'msg' => '操作成功'];
    }


    public function AppSaveOrEnd()
    {
        $inventory_plan_id     = I('post.inventory_plan_id/d');
        $operate               = I('post.operate/s'); // save-一键盘点 end-结束盘点
        $inventory_plan_assets = I('post.inventory_plan_assets', []);
        $date                  = date('Y-m-d H:i:s');
        $inventory_plan        = $this->detail($inventory_plan_id);
        $approve_data          = null; // 审核数据
        if (!in_array($inventory_plan['inventory_plan_status'], [1, 2])) {
            return ['status' => -1, 'msg' => '只有盘点状态为 未盘点 或 正在盘点 时才可以进行盘点'];
        }

        if ($inventory_plan['is_push'] == 1) {
            return ['status' => -1, 'msg' => '盘点计划为自动，不可手动盘点！'];
        }

        if ($operate === 'save') {
            foreach ($inventory_plan_assets as $k => $v) {
                $update = [
                    'inventory_status' => 1,
                    'inventory_user'   => UserInfo::getInstance()->get('username'),
                ];
                $this->updateData('inventory_plan_assets', $update,
                    ['inventory_plan_assets_id' => $v['inventory_plan_assets_id']]);
            }

            $this->updateData('inventory_plan', ['inventory_plan_status' => 2],
                ['inventory_plan_id' => $inventory_plan_id, 'inventory_plan_status' => 1]);
        }

        if ($operate === 'end') {
            $data    = $this->DB_get_all('inventory_plan_assets', '*', ['inventory_plan_id' => $inventory_plan_id]);
            $approve = false;
            $stop    = false;
            foreach ($data as $k => $v) {
                if ($v['inventory_status'] == 0) {
                    $stop = true;
                    break;
                }
                if ($v['inventory_status'] == 2) {
                    $approve = true;
                }
            }
            if ($stop == true) {
                return ['status' => -1, 'msg' => '存在未盘点的设备，请先盘点！'];
            }

            if ($approve == true) {
                $update = $this->approve($inventory_plan);
            } else {
                $update = [
                    'inventory_plan_status'   => 4,
                    'inventory_plan_end_time' => $date,
                ];
            }

            $res = $this->updateData('inventory_plan', $update, ['inventory_plan_id' => $inventory_plan_id]);
            if ($res) {
                return ['status' => 1, 'msg' => '盘点结束成功'];
            } else {
                return ['status' => -1, 'msg' => '盘点结束失败'];
            }
        }

        return ['status' => 1, 'msg' => '盘点成功'];


    }


    public function getApproveList()
    {
        $limit            = I('post . limit') ? I('post . limit') : 10;
        $page             = I('post . page') ? I('post . page') : 1;
        $offset           = ($page - 1) * $limit;
        $order            = I('post . order') ? I('post . order') : 'desc';
        $sort             = I('post . sort') ? I('post . sort') : 'inventory_plan_id';
        $hospital_id      = session('current_hospitalid');
        $current_username = session('username');
        $isSuper          = session('isSuper');


        $inventory_plan_no   = I('post . inventory_plan_no');
        $inventory_plan_name = I('post . inventory_plan_name');

        $where['hospital_id']           = $hospital_id;
        $where['is_delete']             = 0;
        $where['approve_status']        = ['egt', 0];
        $where['inventory_plan_status'] = ['egt', 3];

        if (!$isSuper) {
            $map[]             = sprintf('json_contains(inventory_users, \'"%s"\')', $current_username);
            $map['add_user']   = $current_username;
            $map['_logic']     = 'or';
            $where['_complex'] = $map;
        }

        if ($inventory_plan_no) {
            $where['inventory_plan_no'] = ['like', '%' . $inventory_plan_no . '%'];
        }
        if ($inventory_plan_name) {
            $where['inventory_plan_name'] = ['like', '%' . $inventory_plan_name . '%'];
        }

        $fields          = '
            inventory_plan_id,
            hospital_id,
            inventory_plan_no,
            inventory_plan_name,
            inventory_plan_start_time,
            inventory_plan_end_time,
            inventory_plan_status,
            approve_status,
            approve_time,
            current_approver,
            complete_approver,
            not_complete_approver,
            all_approver,
            add_time,
            remark
        ';
        $total           = $this->DB_get_count($this->tableName, $where);
        $inventory_plans = $this->DB_get_all($this->tableName, $fields, $where, '',
            $sort . ' ' . $order, $offset . "," . $limit);
        if (!$inventory_plans) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        $super = $this->DB_get_all('user', 'username', ['is_super' => 1]);
        //查询计划执行人
        $ids_arr = [];
        foreach ($inventory_plans as $k => $v) {
            $ids_arr[]         = $v['inventory_plan_id'];
            $v['all_approver'] = str_replace('/', '', $v['all_approver']);
            foreach ($super as $user) {
                $search                              = ',' . $user['username'];
                $inventory_plans[$k]['all_approver'] = str_replace($search, '', $v['all_approver']);
            }
        }

        //查询审批记录
        $apps = $this->DB_get_all('approve',
            'inventory_plan_id,group_concat(is_adopt ORDER BY apprid ASC) as is_adopt,group_concat(approve_time ORDER BY apprid ASC) as approve_time',
            ['is_delete' => 0, 'inventory_plan_id' => ['in', $ids_arr]], 'inventory_plan_id');
        //处理数据
        //icon
        $yuan_icon = '&#xe63f;';//圆形
        $dui_icon  = '&#xe605;';//通过
        $cuo_icon  = '&#x1006;';//不通过

        //颜色
        $gray_color    = 'unexecutedColor';//灰色
        $pass_color    = 'executeddColor';//绿色
        $no_pass_color = 'endColor';//红色

        foreach ($inventory_plans as &$inventory_plan) {
            $all_approver = explode(',', $inventory_plan['all_approver']);
            $html         = '<ul class="timeLineList">';
            foreach ($all_approver as $username) {
                $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $gray_color . '">' . $yuan_icon . '</i><div class="timeLine ' . $gray_color . 'Bg"></div><span class="timeLineTitle ' . $gray_color . '">' . $username . '</span><span class="timeLineDate ' . $gray_color . '">-</span></div></li>';
            }
            $html                              .= '</ul>';
            $inventory_plan['app_user_status'] = $html;
            //审批状态
            foreach ($apps as $vp) {
                if ($vp['inventory_plan_id'] == $inventory_plan['inventory_plan_id']) {
                    $is_adopt     = explode(',', $vp['is_adopt']);
                    $approve_time = explode(',', $vp['approve_time']);
                    $html         = '<ul class="timeLineList">';
                    foreach ($all_approver as $uk => $username) {
                        if ($is_adopt[$uk]) {
                            if ($is_adopt[$uk] == 1) {
                                $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $pass_color . '">' . $dui_icon . '</i><div class="timeLine ' . $pass_color . 'Bg"></div><span class="timeLineTitle ' . $pass_color . '">' . $username . '</span><span class="timeLineDate ' . $pass_color . '">' . date('Y-m-d H:i',
                                        $approve_time[$uk]) . '</span></div></li>';
                            } else {
                                $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $no_pass_color . '">' . $cuo_icon . '</i><div class="timeLine ' . $no_pass_color . 'Bg"></div><span class="timeLineTitle ' . $no_pass_color . '">' . $username . '</span><span class="timeLineDate ' . $no_pass_color . '">' . date('Y-m-d H:i',
                                        $approve_time[$uk]) . '</span></div></li>';
                            }
                        } else {
                            $html .= '<li><div class="timeLineBox"><i class="layui-icon layui-timeline-axis ' . $gray_color . '">' . $yuan_icon . '</i><div class="timeLine ' . $gray_color . 'Bg"></div><span class="timeLineTitle ' . $gray_color . '">' . $username . '</span><span class="timeLineDate ' . $gray_color . '">-</span></div></li>';
                        }
                    }
                    $html                              .= '</ul>';
                    $inventory_plan['app_user_status'] = $html;
                }
            }

            // 操作
            $html                         = '<div class="layui-btn-group">';
            $showInventoryPlanUrl         = C('ADMIN_NAME') . '/InventoryPlan/showInventoryPlan?inventory_plan_id=' . $inventory_plan['inventory_plan_id'];
            $auditInventoryPlanApproveUrl = C('ADMIN_NAME') . '/InventoryPlan/auditInventoryPlanApprove?inventory_plan_id=' . $inventory_plan['inventory_plan_id'];

            $html .= $this->returnListLink('查看', $showInventoryPlanUrl, 'showInventoryPlan',
                C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($inventory_plan['approve_status'] == 0) {
                $html .= $this->returnListLink('未审核', $auditInventoryPlanApproveUrl, 'auditInventoryPlanApprove',
                    C('BTN_CURRENCY'));
            }
            $html                        .= '</div>';
            $inventory_plan['operation'] = $html;
        }


        $result['total']  = (int)$total;
        $result["offset"] = $offset;
        $result["limit"]  = (int)$limit;
        $result["code"]   = 200;
        $result['rows']   = $inventory_plans;
        return $result;
    }

    /**
     * 审核计划
     *
     * @return array
     */
    public
    function audit()
    {
        $inventory_plan_id = I('POST.inventory_plan_id');
        $inventory_plan    = $this->detail($inventory_plan_id);
        if (!$inventory_plan) {
            return ['status' => -1, 'msg' => '查找不到盘点计划信息'];
        } else {
            if ($inventory_plan['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                return ['status' => -1, 'msg' => '审批已通过，请勿重复提交！'];
            } elseif ($inventory_plan['approve_status'] == C('STATUS_APPROE_FAIL')) {
                return ['status' => -1, 'msg' => '审批已否决，请勿重复提交！'];
            }
        }
        $data['inventory_plan_id'] = $inventory_plan['inventory_plan_id'];
        $data['is_adopt']          = I('POST.is_adopt');
        $data['remark']            = trim(I('POST.remark'));
        $data['proposer']          = $inventory_plan['add_user'];
        $data['proposer_time']     = strtotime($inventory_plan['add_time']);
        $data['approver']          = session('username');
        $data['approve_time']      = time();
        $data['approve_class']     = 'inventory_plan';
        $data['process_node']      = C('INVENTORY_PLAN_APPROVE');
        //判断是否是当前审批人
        if ($inventory_plan['current_approver']) {
            $current_approver     = explode(',', $inventory_plan['current_approver']);
            $current_approver_arr = [];
            foreach ($current_approver as &$current_approver_value) {
                $current_approver_arr[$current_approver_value] = true;
            }
            if ($current_approver_arr[session('username')]) {
                $processWhere['inventory_plan_id'] = ['EQ', $inventory_plan['inventory_plan_id']];
                $processWhere['is_delete']         = ['NEQ', C('YES_STATUS')];
                $process                           = $this->DB_get_count('approve', $processWhere);
                $level                             = $process + 1;
                $data['process_node_level']        = $level;
                $res                               = $this->addApprove($inventory_plan, $data, 0,
                    $inventory_plan['hospital_id'], 0,
                    C('INVENTORY_PLAN_APPROVE'), 'inventory_plan', 'inventory_plan_id');
                if ($res['status'] == 1) {
                    $is_adopt = I('POST.is_adopt') == 1 ? '已通过' : '已驳回';
                    $text     = getLogText('approveInventoryPlanLogText',
                        ['inventory_plan_name' => $inventory_plan['inventory_plan_name'], 'is_adopt' => $is_adopt]);
                    $this->addLog('inventory_plan', $res['lastSql'], $text, $inventory_plan['inventory_plan_id'],
                        '');
                }
                // todo 短信模块未开发，后续如果需要再加

                return $res;
            } else {
                return ['status' => -1, 'msg' => '请等待审批！'];
            }
        } else {
            return ['status' => -1, 'msg' => '审核已结束！'];
        }
    }

    /**
     * 处理属性
     *
     * @return array
     */
    protected
    function processAttributes(
        $inventory_plan = null
    ) {
        $inventory_plan_assets_nums = rtrim(I('post.inventory_plan_assets_nums'), ',');
        $this->checkstatus(judgeEmpty($inventory_plan_assets_nums), '请选择需要纳入的设备');
        $this->checkstatus(judgeEmpty(I('post.inventory_plan_name')), '计划名称不能为空');
        $this->checkstatus(judgeEmpty(I('post.inventory_users')), '盘点员不能为空');

        $data['inventory_plan_name']       = I('post.inventory_plan_name/s');
        $data['inventory_users']           = I('post.inventory_users/a');
        $data['inventory_plan_start_time'] = I('post.inventory_plan_start_time/s');
        $data['inventory_plan_end_time']   = I('post.inventory_plan_end_time/s');
        $data['is_push']                   = I('post.is_push/d');
        $data['remark']                    = I('post.remark/s');

        if (!$inventory_plan) {
            $data['hospital_id']       = session('current_hospitalid');
            $data['inventory_plan_no'] = $this->getOrgNumber($this->tableName, 'inventory_plan_no', "PD", '_');
            $data['approve_status']    = -1;
        }

        // todo 判断盘点员是否存在,后续看需不需要根据科室判断
        if (!$this->DB_get_one('user', 'userid', ['username' => ['in', $data['inventory_users']]])) {
            return ['status' => -1, 'msg' => '盘点员不存在！'];
        }

        // 如果 是否自动为是 校验设备是否绑定的rfid
        if ($data['is_push']) {
            $data['assets'] = $this->DB_get_all('assets_info', 'assnum',
                ['assnum' => ['in', $inventory_plan_assets_nums], 'inventory_label_id' => ['exp', 'is not null']]);
            if (explode(',', $inventory_plan_assets_nums) != count($data['assets'])) {
                return ['status' => -1, 'msg' => '该计划为推送到RFID的盘点计划，请选择已绑定标签ID的设备'];
            }
        } else {
            $data['assets'] = $this->DB_get_all('assets_info', 'assnum',
                ['assnum' => ['in', $inventory_plan_assets_nums]]);
        }


        return $data;
    }

    protected
    function detail(
        $inventory_plan_id
    ) {
        $where['inventory_plan_id'] = $inventory_plan_id;
        $where['is_delete']         = 0;
        $where['hospital_id']       = session('current_hospitalid');
        $data                       = $this->DB_get_one($this->tableName, '', $where);
        if ($data) {
            $data['inventory_users'] = json_decode($data['inventory_users'], true);
            array_unshift($data['inventory_users'], $data['add_user']);
        }
        return $data;
    }

    /**
     * 获取审批的相关数据
     *
     * @param $data
     *
     * @return null
     */
    protected
    function approve(
        $data
    ) {
        $where['hospital_id']  = $data['hospital_id'];
        $where['approve_type'] = 'inventory_plan_approve';//巡查审批类型
        $fields                = "A.status as status,C.approve_user,C.listorder,C.approve_user_aux";
        $join[0]               = "LEFT JOIN sb_approve_process as B ON A.typeid = B.typeid";
        $join[1]               = "LEFT JOIN sb_approve_process_user as C ON B.processid = C.processid";
        $approve               = $this->DB_get_all_join('approve_type', 'A', $fields, $join, $where, '',
            'C.listorder asc');
        foreach ($approve as $key => $value) {
            if ($value['status'] == 0 || $value['approve_user'] == "") {
                return null;
            }

            if ($value['listorder'] == 1) {
                $data['current_approver'] = $value['approve_user'] . ',' . $value['approve_user_aux'];
            }
            $data['approve_status']        = '0';
            $data['inventory_plan_status'] = '3';
            $data['not_complete_approver'] .= $value['approve_user'] . ',' . $value['approve_user_aux'] . ',';
            $data['all_approver']          .= '/' . $value['approve_user'] . '/,/' . $value['approve_user_aux'] . '/,';

        }
        $data['not_complete_approver'] = substr($data['not_complete_approver'], 0, -1);
        $data['all_approver']          = substr($data['all_approver'], 0, -1);
        return $data;
    }

    /**
     * 获取计划审批信息
     *
     * @param $patrid int 计划ID
     */
    protected
    function getApproveInfo(
        $inventory_plan_id
    ) {
        $where['is_delete']         = C('NO_STATUS');
        $where['approve_class']     = 'inventory_plan';
        $where['process_node']      = C('INVENTORY_PLAN_APPROVE');
        $where['inventory_plan_id'] = $inventory_plan_id;
        $apps                       = $this->DB_get_all('approve',
            'apprid,inventory_plan_id,approver,approve_time,is_adopt,remark', $where, '',
            'process_node_level,apprid asc');
        foreach ($apps as $k => $v) {
            $apps[$k]['approve_time'] = date('Y-m-d H:i', $v['approve_time']);
            if ($v['is_adopt'] == 1) {
                $apps[$k]['approve_status'] = '通过';
            } else {
                $apps[$k]['approve_status'] = '驳回';
            }
        }
        return $apps;
    }

    public function updatePlanAssetsStatus()
    {
        $inventory_plan_assets_id = I('post.inventory_plan_assets_id');
        $assetnum                 = I('post.assetnum');
        if (empty($inventory_plan_assets_id) && empty($assetnum)) {
            return ['status' => 302, 'msg' => '请选择设备'];
        }
        if (!empty($inventory_plan_assets_id)) {
            $update                     = [];
            $update['inventory_status'] = I('post.status');
            $update['reason']           = I('post.reason');
            $update['result']           = I('post.result');
            $update['inventory_user']   = I('post.inventory_user');

            $res = $this->updateData('inventory_plan_assets', $update,
                ['inventory_plan_assets_id' => $inventory_plan_assets_id]);

            if ($res !== false) {
                $response = ['status' => 1, 'msg' => '盘点成功'];
            } else {
                $response = ['status' => -1, 'msg' => '盘点失败'];
            }

        } else {
            $asset = $this->DB_get_one('assets_info', '*', ['assetnum' => $assetnum]);
            if (empty($asset)) {
                return ['status' => -1, 'msg' => '设备不存在'];
            }
            $planAsset = $this->DB_get_one('inventory_plan_assets', '*',
                ['assetnum' => $assetnum, 'inventory_plan_id' => I('post.inventory_plan_id', 0)]);
            if (!empty($planAsset)) {
                return ['status' => -1, 'msg' => '该设备已存在与当前盘点计划'];
            }

            $res = $this->insertData('inventory_plan_assets',
                [
                    'inventory_plan_id' => I('post.inventory_plan_id'),
                    'assetnum'          => $assetnum,
                    'is_plan'           => 0,
                    'inventory_status'  => 1,
                    'assets'            => $asset['assets'],
                    'inventory_user'    => I('post.inventory_user'),
                    'add_time'          => date('Y-m-d H:i:s'),
                ]);
            if ($res) {
                $response = ['status' => 1, 'msg' => '盘点成功'];
            } else {
                $response = ['status' => -1, 'msg' => '盘点失败'];
            }
        }

        return $response;
    }

    public function getAssetInfo()
    {
        $assnum = I('get.assnum');
        $field = "assets,departid,assnum";
        $departname  = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $asset = $this->DB_get_one('assets_info', $field, ['assnum' => $assnum,    'is_delete' => '0', 'hospital_id' => UserInfo::getInstance()->get('current_hospitalid')]);
        if (!$asset) {
            $asset = $this->DB_get_one('assets_info', $field, ['assorignum' => $assnum,    'is_delete' => '0', 'hospital_id' => UserInfo::getInstance()->get('current_hospitalid')]);
            if (!$asset) {
                $asset = $this->DB_get_one('assets_info', $field, ['assorignum_spare' => $assnum,    'is_delete' => '0', 'hospital_id' => UserInfo::getInstance()->get('current_hospitalid')]);
                if (!$asset){
                    return ['status' => -1, 'msg' => '设备不存在', 'data' => null];
                }
            }
        }
        $asset['department'] = $departname[$asset['departid']]['department'];
        return ['status' => 1, 'msg' => '获取成功', 'data' => $asset];

    }

}