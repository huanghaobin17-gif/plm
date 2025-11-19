<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;
use Think\Model;
use Think\Model\RelationModel;

class QualityModel extends CommonModel
{
    protected $len = 100;
    protected $tablePrefix = 'sb_';
    protected $tableName = 'quality_preset';
    private $MODULE = 'Qualities';

    /**
     * Notes: 获取质控设备列表
     */
    public function getAssetsLists()
    {
        $departids = session('departid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $assets = I('POST.assets');
        $department = I('POST.department');
        $catgory = I('POST.category');
        $quality = I('POST.quality');
        $hospital_id = I('POST.hospital_id');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if ($department) {
            $where['departid'] = array('in', $department);
        } else {
            $where['departid'] = array('in', $departids);
        }
        $where['status'][0] = 'NOTIN';
        $where['status'][1][] = C('ASSETS_STATUS_SCRAP');
        $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        $where['quality_in_plan'] = C('NO_STATUS');//不在质控计划中
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$sort) {
            $sort = 'assid ';
        }
        if (!$order) {
            $order = 'desc';
        }
        if ($_POST['hlepcatid'] !== NULL && $_POST['hlepcatid'] != '') {
            $hlepcatid = I('POST.hlepcatid');
            $where['helpcatid'] = $hlepcatid;
        }
        if ($assets) {
            //设备名称搜索
            $where['assets'] = array('LIKE', '%' . $assets . '%');
        }
        if ($catgory) {
            //分类搜索
            $catwhere['category'] = array('like', "%$catgory%");
            $res = $this->DB_get_one('category', 'group_concat(catid) as catid', $catwhere, 'catid asc');
            if ($res) {
                $where['catid'] = array('IN', $res['catid']);
            } else {
                $where['catid'] = array('IN', 0);
            }
        }
        if ($quality || $quality == '') {
            $where['is_qualityAssets'] = 1;
        }
        if (I('POST.assids')) {
            $assids = trim(I('POST.assids'), ',');
            $where['assid'] = array('NOT IN', $assids);
        }
        if ($hospital_id) {
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }
        $where['is_delete'] = '0';
        $total = $this->DB_get_count('assets_info', $where);
        $assets = $this->DB_get_all('assets_info', 'assid,hospital_id,assets,assnum,model,departid,catid,helpcatid,opendate,is_qualityAssets,lasttesttime,lasttestuser,lasttestresult', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        //查询当前用户是否有权限制定质控计划
        $addQuality = get_menu($this->MODULE, 'Quality', 'addQuality');
        $departname = array();
        $catname = array();
        $baseSetting = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $assids = array();
        foreach ($assets as $k => $v) {
            $html = '<div class="layui-btn-group">';
            $assids[] = $v['assid'];
            $assets[$k]['opendate'] = HandleEmptyNull($v['opendate']);
            $assets[$k]['department'] = $departname[$v['departid']]['department'];
            $assets[$k]['category'] = $catname[$v['catid']]['category'];
            $assets[$k]['helpcat'] = $v['helpcatid'] != null ? $baseSetting['assets']['assets_helpcat']['value'][$v['helpcatid']] : '--';
            if ($v['lasttestuser']) {
                $assets[$k]['test_user'] = $v['lasttestuser'];
                $assets[$k]['test_date'] = getHandleTime(strtotime($v['lasttesttime']));
                $assets[$k]['test_result'] = $v['lasttestresult'] == 1 ? '合格' : '不合格';
            } else {
                $assets[$k]['test_user'] = '';
                $assets[$k]['test_date'] = '';
                $assets[$k]['test_result'] = '';
            }
            if ($addQuality) {
                $html .= $this->returnButtonLink('纳入', $addQuality['actionurl'], 'layui-btn layui-btn-xs layui-btn-warm', '', 'lay-event = join');
            }
            $html .= $this->returnButtonLink('设备详情', C('ADMIN_NAME').'/Lookup/showAssets.html', 'layui-btn layui-btn-xs layui-btn-normal', '', 'lay-event = showAssets');
            $html .= '</div>';
            $assets[$k]['operation'] = $html;
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $assets;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取质控设备列表
     */
    public function getAssetsListsByType()
    {
        $departids = session('departid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $where = " departid in ($departids) and status != 2";
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$sort) {
            $sort = 'assid ';
        }
        if (!$order) {
            $order = 'desc';
        }
        if (I('POST.assids')) {
            $assids = trim(I('POST.assids'), ',');
            $where = "assid in($assids)";
        } else {
            $result['total'] = 0;
            $result["offset"] = $offset;
            $result["limit"] = $limit;
            $result["code"] = 200;
            $result['rows'] = array();
            if (!$result['rows']) {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
            }
            return $result;
        }
        $total = $this->DB_get_count('assets_info', $where);
        $assets = $this->DB_get_all('assets_info', 'assid,assets,assnum,model,departid,catid,helpcatid,opendate,is_qualityAssets,lasttesttime,lasttestuser,lasttestresult', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        //查询当前用户是否有权限制定质控计划
        $addQuality = get_menu($this->MODULE, 'Quality', 'addQuality');
        $departname = array();
        $catname = array();
        $baseSetting = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $assids = array();
        foreach ($assets as $k => $v) {
            $assids[] = $v['assid'];
            $assets[$k]['opendate'] = HandleEmptyNull($v['opendate']);
            $assets[$k]['department'] = $departname[$v['departid']]['department'];
            $assets[$k]['category'] = $catname[$v['catid']]['category'];
            $assets[$k]['helpcat'] = $v['helpcatid'] ? $baseSetting['assets']['assets_helpcat']['value'][$v['helpcatid']] : '--';
            $assets[$k]['test_user'] = $v['lasttestuser'];
            $assets[$k]['test_date'] = $v['lasttesttime'] ? date('Y-m-d', strtotime($v['lasttesttime'])) : '';
            $assets[$k]['test_result'] = $v['lasttestresult'] == 1 ? '符合' : ($v['lasttestresult'] == 2 ? '不符合' : '');
            $assets[$k]['executors'] = '';
            $assets[$k]['operation'] = '<div class="layui-btn-group"><button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="removeAssets">移除</button></div>';
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $assets;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes:新增质控计划
     */
    public function addPlan()
    {
        $planName = trim($_POST['planName']);
        $existsName = $this->DB_get_one('quality_starts', 'qsid', array('plan_name' => $planName));
        if ($existsName) {
            return array('status' => -1, 'msg' => '计划名称已存在！');
        }
        $planRemark = trim($_POST['planRemark']);
        $hospital_id = I('hospital_id');
        unset($_POST['type']);
        unset($_POST['planName']);
        unset($_POST['planRemark']);
        unset($_POST['hospital_id']);
        $user = $this->DB_get_all('user', 'userid,username', array('status' => 1, 'is_super' => 0, 'is_delete' => 0, 'job_hospitalid' => $hospital_id));
        $userKeyValue = array();
        foreach ($user as $k => $v) {
            $userKeyValue[$v['username']] = $v['userid'];
        }
        //生成一个质控计划标识符
        $plan_identifier = getRandomId();
        //查询现有的最大的计划数
        $maxplan = $this->DB_get_one('quality_starts', 'max(plans) as plans', array('1'));
        $plans = $maxplan['plans'] ? ($maxplan['plans'] + 1) : 1;
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $data = array();
        $n = 0;
        foreach ($_POST as $k => $v) {
            $assids = explode(',', trim($v, ','));
            //查询用户的管理科室
            $join = "LEFT JOIN sb_user AS B ON A.userid = B.userid";
            $joinwhere['B.username'] = $k;
            $udepartids = $this->DB_get_one_join('user_department', 'A', 'group_concat(departid) as departids', $join, $joinwhere);
            $udepartidsarr = explode(',', $udepartids['departids']);
            foreach ($assids as $k1 => $v1) {
                //查询设备编码
                $assnum = $this->DB_get_one('assets_info', 'assnum,departid', array('assid' => $v1));
                if (!in_array($assnum['departid'], $udepartidsarr)) {
                    return array('status' => -1, 'msg' => $k . ' 用户没有 ' . $departname[$assnum['departid']]['department'] . ' 科室设备的管理权限，请更改对应设备的检测人！');
                }
                $data[$n]['plan_identifier'] = $plan_identifier;
                $data[$n]['plans'] = $plans;
                $data[$n]['plan_num'] = 'QC' . date('Ymd') . '-' . $assnum['assnum'];
                $data[$n]['plan_name'] = $planName;
                $data[$n]['plan_remark'] = $planRemark;
                $data[$n]['assid'] = $v1;
                $data[$n]['userid'] = $userKeyValue[$k];
                $data[$n]['username'] = $k;
                $data[$n]['hospital_id'] = session('current_hospitalid');
                $data[$n]['addtime'] = getHandleDate(time());
                $data[$n]['adduser'] = session('username');
                $n++;
            }
        }
        if ($data) {
            $this->insertDataAll('quality_starts', $data);
            foreach ($data as $key => $val) {
                //修改设备表对应设备为质控计划中
                $this->updateData('assets_info', array('quality_in_plan' => C('YES_STATUS')), array('assid' => $val['assid']));
            }
        }
        return array('status' => 1, 'msg' => '新增质控计划成功！');
    }

    /**
     * Notes: 获取质控计划列表
     */
    public function getPlanList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $assets = trim(I('POST.assets'));
        $departid = trim(I('POST.departid'), ',');
        $cycle = I('POST.isSycle');
        $start = I('POST.status');
        $hospital_id = I('POST.hospital_id');
        $where = " 1 ";
//        if(session('isSuper')){
//            $where = " 1 ";
//        }else{
//            $where = " A.adduser = '".session('username')."'";
//        }
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$sort) {
            $sort = 'plans desc,assid';
        }
        if (!$order) {
            $order = 'desc';
        }
        if ($assets) {
            //设备名称搜索
            $where .= " and B.assets like '%" . $assets . "%'";
        }
        if ($departid) {
            $where .= " and B.departid in (" . $departid . ")";
        } else {
            $where .= " and B.departid in (" . session('departid') . ")";
        }
        if ($hospital_id) {
            $where .= " and B.hospital_id = " . $hospital_id;
        } else {
            $where .= " and B.hospital_id = " . session('current_hospitalid');
        }
        if ($cycle != '') {
            $where .= " and A.is_cycle = " . $cycle;
        }
        if ($start != '') {
            $where .= " and A.is_start = " . $start;
        }
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.*,B.assets,B.assnum,B.departid,B.catid,B.model,B.lasttesttime,B.lasttestuser,B.lasttestresult";
        $where.=" and B.is_delete = 0 ";
        $total = $this->DB_get_count_join('quality_starts', 'A', $join, $where);
        $plans = $this->DB_get_all_join('quality_starts', 'A', $fields, $join, $where, '', $sort . ' ' . $order . ',assid desc', $offset . "," . $limit);
        //搜索查询
        $assidFields = 'group_concat(A.assid) AS assid';
        $assidCount = $this->DB_get_one_join('quality_starts', 'A', $assidFields, $join, $where, '');
        //查询当前用户是否有权限制定质控计划
        $startQuality = get_menu($this->MODULE, 'Quality', 'startQualityPlan');
        //查询当前用户是否有权限修改质控计划
        $editPlan = get_menu($this->MODULE, 'Quality', 'editQualityPlan');
        //查询当前用户是否有权限执行质控计划
        $executePlan = get_menu($this->MODULE, 'Quality', 'setQualityDetail');
        $departname = array();
        $catname = array();
        $baseSetting = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $assids = array();
        foreach ($plans as $k => $v) {
            if (!isset($v['is_cycle'])) {
                $plans[$k]['is_cycle'] = 0;
            } else {
                if ($v['is_cycle'] == 0) {
                    $plans[$k]['is_cycle_name'] = '<span style="color:#FF5722;">否</span>';
                    $plans[$k]['cycle_name'] = '<span style="color:#FF5722;">无</span>';

                } else {
                    $plans[$k]['is_cycle_name'] = '是';
                    $plans[$k]['cycle_name'] = $v['cycle'];
                }
            }


            $html = '<div class="layui-btn-group">';
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['category'] = $catname[$v['catid']]['category'];
            $plans[$k]['helpcat'] = $v['helpcatid'] ? $baseSetting['assets']['assets_helpcat']['value'][$v['helpcatid']] : '--';
            if ($v['lasttestuser']) {
                $plans[$k]['test_user'] = $v['lasttestuser'];
                $plans[$k]['test_date'] = getHandleTime(strtotime($v['lasttesttime']));
                $plans[$k]['test_result'] = $v['lasttestresult'] == 1 ? '合格' : '不合格';
            } else {
                $plans[$k]['test_user'] = '';
                $plans[$k]['test_date'] = '';
                $plans[$k]['test_result'] = '';
            }

            switch ($v['is_start']) {
                case 0:
                    if ($startQuality) {
                        $html .= $this->returnButtonLink('启用', $startQuality['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'data-id= ' . $v['qsid'] . ' lay-event = start');
                    }
                    break;
                case 1:
                    if ($startQuality) {
                        $html .= $this->returnButtonLink('暂停', $startQuality['actionurl'], 'layui-btn layui-btn-xs layui-bg-green', '', 'data-id= ' . $v['qsid'] . ' lay-event = layup');
                    }
                    break;
                case 2:
                    if ($startQuality) {
                        $html .= $this->returnButtonLink('启用', $startQuality['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'data-id= ' . $v['qsid'] . ' lay-event = start');
                    }
                    break;
                case 3:
                    $html .= $this->returnButtonLink('完成', C('ADMIN_NAME').'/Quality/showDetail.html', 'layui-btn layui-btn-xs layui-bg-cyan', '', 'data-id= ' . $v['qsid'] . ' lay-event = showDetail');
                    break;
                case 4:
                    $html .= $this->returnButtonLink('结束', C('ADMIN_NAME').'/Quality/showDetail.html', 'layui-btn layui-btn-xs layui-bg-blue', '', 'data-id= ' . $v['qsid'] . ' lay-event = showDetail');
                    break;
            }
            $html .= $this->returnButtonLink('详情', C('ADMIN_NAME').'/Quality/showQualityPlan.html', 'layui-btn layui-btn-xs layui-btn-normal', '', 'data-id= ' . $v['qsid'] . ' lay-event = showPlan');
            if ($executePlan) {
                if ($v['is_start'] == 1) {
                    $html .= $this->returnButtonLink('执行', $executePlan['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'data-id= ' . $v['qsid'] . ' lay-event = execute');
                } else {
                    $html .= $this->returnButtonLink('执行', $executePlan['actionurl'], 'layui-btn layui-btn-xs layui-btn-disabled', '', 'data-id= ' . $v['qsid'] . ' lay-event = noexecute');
                }
            }
            if ($editPlan) {
                if ($v['is_start'] >= 3) {
                    $html .= $this->returnButtonLink('编辑', $editPlan['actionurl'], 'layui-btn layui-btn-xs layui-btn-warm layui-btn-disabled', '', 'data-id= ' . $v['qsid'] . ' lay-event = noedit');
                } else {
                    $html .= $this->returnButtonLink('编辑', $editPlan['actionurl'], 'layui-btn layui-btn-xs layui-btn-warm', '', 'data-id= ' . $v['qsid'] . ' lay-event = edit');
                }
            }

            $html .= '</div>';
            $plans[$k]['operation'] = $html;
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['assidCount'] = $assidCount['assid'];
        $result['rows'] = $plans;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes:启用计划--获取设备信息及预设项目
     */
    public function getAssetsAndPresetInfo()
    {
        $result = array();
        $qsid = trim(I('GET.qsid'), ',');
        $qsid = explode(',', $qsid);
        //计划信息
        $qsInfo = $this->DB_get_all('quality_starts', 'qsid,assid,username,plan_name,plan_num,is_cycle,cycle', array('qsid' => array('in', $qsid)));
        if (!$qsInfo) {
            $result['status'] = -1;
            $result['msg'] = '查找不到该计划';
            return $result;
        }
        foreach ($qsInfo as $k => $v) {
            if ($v['is_cycle'] == 1 && count($qsInfo) > 1) {
                $result['status'] = -1;
                $result['msg'] = '已设置周期执行的计划不支持多个计划一键启用';
                return $result;
            }
        }
        $assids = $tester = $realqs = $plan = array();
        foreach ($qsInfo as $k => $v) {
            $assids[] = $v['assid'];
            if (!in_array($v['username'], $tester)) {
                $tester[] = $v['username'];
            }
            if (!in_array($v['plan_name'], $plan)) {
                $plan[] = $v['plan_name'];
            }
        }
        $realqs['qsid'] = trim(I('GET.qsid'), ',');
        $realqs['username'] = implode('、', $tester);
        $realqs['plan_name'] = implode('、', $plan);
        $realqs['is_cycle'] = $qsInfo[0]['is_cycle'];
        $realqs['cycle'] = $qsInfo[0]['cycle'];
        $result['qsInfo'] = $realqs;
        //设备信息
        $asInfo = $this->DB_get_one('assets_info', 'group_concat(assid ) as assid,group_concat(assnum separator \'、\') as assnum,group_concat(assets separator \'、\') as assets,group_concat(departid) as departid,group_concat(model separator \'、\') as model', array('assid' => array('in', $assids)));
        $asInfo['department'] = '';
        $departname = array();
        $catname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        foreach (explode(',', $asInfo['departid']) as $k => $v) {
            $asInfo['department'] .= $departname[$v]['department'] . '、';
        }
        $asInfo['department'] = trim($asInfo['department'], '、');
        $result['assetsInfo'] = $asInfo;

        //获取模板
        $result['templates'] = $this->DB_get_all('quality_templates', '*', array('1'));
        //获取检查依据
        $result['basis'] = $this->DB_get_all('quality_detection_basis', '*', array('1'));
        //获取检测仪器
        $result['incres'] = $this->DB_get_all('quality_instruments', 'qiid,instrument', array('1'));
        return $result;

    }

    /**
     * Notes: 新增仪器
     */
    public function addInstruments()
    {
        $data['instrument'] = trim(I('POST.name'));
        $data['model'] = trim(I('POST.model'));
        $data['productid'] = trim(I('POST.serialnum'));
        $data['metering_date'] = trim(I('POST.date'));
        $data['metering_place'] = trim(I('POST.company'));
        $data['metering_num'] = trim(I('POST.num'));
        $data['metering_report'] = trim(I('POST.report'));
        if (!$data['instrument']) {
            return array('status' => -1, 'msg' => '仪器名称不能为空！');
        }
        if (!$data['model']) {
            return array('status' => -1, 'msg' => '仪器规格/型号不能为空！');
        }
        if (!$data['productid']) {
            return array('status' => -1, 'msg' => '仪器序列号不能为空！');
        }
        if (!$data['metering_date']) {
            return array('status' => -1, 'msg' => '仪器检定日期不能为空！');
        }
        if (!$data['metering_place']) {
            return array('status' => -1, 'msg' => '仪器检定单位不能为空！');
        }
        if (!$data['metering_num']) {
            return array('status' => -1, 'msg' => '仪器计量编号不能为空！');
        }
        if (!$data['metering_report']) {
            return array('status' => -1, 'msg' => '仪器计量报告不能为空！');
        }
        //查询仪器是否已存在()
        $exists = $this->DB_get_one('quality_instruments', 'qiid', array('productid' => $data['productid']));
        if ($exists) {
            return array('status' => -1, 'msg' => '该计量仪器已存在！');
        }
        $data['addtime'] = getHandleDate(time());
        $data['adduser'] = session('username');
        //入库
        $res = $this->insertData('quality_instruments', $data);
        if ($res) {
            return array('status' => 1, 'msg' => '添加仪器成功！', 'qiid' => $res);
        } else {
            return array('status' => -1, 'msg' => '添加仪器失败！');
        }
    }

    /**
     * Notes: 修改仪器
     */
    public function updateInstruments()
    {
        $qiid = I('POST.qiid');
        $data['instrument'] = trim(I('POST.name'));
        $data['model'] = trim(I('POST.model'));
        $data['productid'] = trim(I('POST.serialnum'));
        $data['metering_date'] = trim(I('POST.date'));
        $data['metering_place'] = trim(I('POST.company'));
        $data['metering_num'] = trim(I('POST.num'));
        $data['metering_report'] = trim(I('POST.report'));
        if (!$data['instrument']) {
            return array('status' => -1, 'msg' => '仪器名称不能为空！');
        }
        if (!$data['model']) {
            return array('status' => -1, 'msg' => '仪器规格/型号不能为空！');
        }
        if (!$data['productid']) {
            return array('status' => -1, 'msg' => '仪器序列号不能为空！');
        }
        if (!$data['metering_date']) {
            return array('status' => -1, 'msg' => '仪器检定日期不能为空！');
        }
        if (!$data['metering_place']) {
            return array('status' => -1, 'msg' => '仪器检定单位不能为空！');
        }
        if (!$data['metering_num']) {
            return array('status' => -1, 'msg' => '仪器计量编号不能为空！');
        }
        if (!$data['metering_report']) {
            return array('status' => -1, 'msg' => '仪器计量报告不能为空！');
        }
        //查询仪器是否已存在()
        $exists = $this->DB_get_one('quality_instruments', 'qiid', array('productid' => $data['productid'], 'qiid' => array('neq', $qiid)));
        if ($exists) {
            return array('status' => -1, 'msg' => '该计量仪器已存在！');
        }
        $data['edittime'] = getHandleDate(time());
        $data['edituser'] = session('username');
        //修改
        $res = $this->updateData('quality_instruments', $data, array('qiid' => $qiid));
        if ($res) {
            return array('status' => 1, 'msg' => '编辑仪器成功！', 'qiid' => $qiid);
        } else {
            return array('status' => -1, 'msg' => '编辑仪器失败！');
        }
    }


    public function deleteInstruments()
    {
        $qiid = I('POST.qiid');
        $res = $this->deleteData('quality_instruments', array('qiid' => $qiid));
        if ($res) {
            return array('status' => 1, 'msg' => '编辑仪器成功！', 'qiid' => $qiid);
        } else {
            return array('status' => -1, 'msg' => '编辑仪器失败！');
        }
    }

    //上传报告
    public function uploadReport($path)
    {
        if ($_FILES['file']) {
            $Tool = new ToolController();
            $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx');
            $dirName = $path;
            $info = $Tool->upFile($style, $dirName);
            if ($info['status'] == C('YES_STATUS')) {
                // 上传成功 获取上传文件信息
                $resule['status'] = 1;
                $resule['msg'] = '上传成功';
                $resule['path'] = $info['src'];
                $resule['name'] = $info['name'];
                $resule['formerly'] = $info['formerly'];
            } else {
                // 上传错误提示错误信息
                $resule['status'] = -1;
                $resule['msg'] = $info['msg'];
            }
            return $resule;
        }
    }

    public function uploadReportBase64($base64, $path)
    {
        $Tool = new ToolController();
        $info = $Tool->base64imgsave($base64, $path);
        if ($info['status'] == C('YES_STATUS')) {
            // 上传成功 获取上传文件信息
            $resule['status'] = 1;
            $resule['msg'] = '上传成功';
            $resule['path'] = $info['src'];
            $resule['name'] = $info['name'];
            $resule['formerly'] = $info['formerly'];
        } else {
            // 上传错误提示错误信息
            $resule['status'] = -1;
            $resule['msg'] = $info['msg'];
        }
        return $resule;
    }

    /**
     * Notes:质控项目预设
     */
    public function updatePreset()
    {
        //心率设定
        $editHeartRate['value'] = $this->formataddDataJson(I('post.heartRate'));
        //心率最大误差
        $editHeartRate['tolerance'] = I('post.heartRate_tolerance');
        $this->updateData('quality_preset', $editHeartRate, array('detection_Ename' => 'heartRate'));
        //呼吸率设定
        $editBreathRate['value'] = $this->formataddDataJson(I('POST.breathRate'));
        $editBreathRate['tolerance'] = I('post.breathRate_tolerance');
        $this->updateData('quality_preset', $editBreathRate, array('detection_Ename' => 'breathRate'));
        //无创血压设定值
        $editPressure['value'] = $this->formataddDataJson(I('POST.pressure'));
        $editPressure['tolerance'] = I('post.pressure_tolerance');
        $this->updateData('quality_preset', $editPressure, array('detection_Ename' => 'pressure'));
        //血氧饱和度设定值
        $editBOS['value'] = $this->formataddDataJson(I('POST.BOS'));
        $editBOS['tolerance'] = I('post.BOS_tolerance');
        $this->updateData('quality_preset', $editBOS, array('detection_Ename' => 'BOS'));
        //流程检测设定值
        $editFlow['value'] = $this->formataddDataJson(I('POST.flow'));
        //流程检测最大允差
        $editFlow['tolerance'] = I('post.flow_tolerance');
        $this->updateData('quality_preset', $editFlow, array('detection_Ename' => 'flow'));
        $editBlock['value'] = $this->formataddDataJson(I('POST.block'));
        $editBlock['tolerance'] = I('post.block_tolerance');
        $this->updateData('quality_preset', $editBlock, array('detection_Ename' => 'block'));
        //释放能量设定值
        $editEnergesis['value'] = $this->formataddDataJson(I('POST.energesis'));
        $editEnergesis['tolerance'] = I('post.energesis_tolerance');
        $this->updateData('quality_preset', $editEnergesis, array('detection_Ename' => 'energesis'));
        //充电时间设定值
        $editCharge['value'] = $this->formataddDataJson(I('POST.charge'));
        $editCharge['tolerance'] = I('post.charge_tolerance');
        $this->updateData('quality_preset', $editCharge, array('detection_Ename' => 'charge'));
        //潮气量
        $editHumidity['value'] = $this->formataddDataJson(I('POST.humidity'));
        $editHumidity['tolerance'] = I('post.humidity_tolerance');
        $this->updateData('quality_preset', $editHumidity, array('detection_Ename' => 'humidity'));
        //强制通气频率
        $editAeration['value'] = $this->formataddDataJson(I('POST.aeration'));
        $editAeration['tolerance'] = I('post.aeration_tolerance');
        $this->updateData('quality_preset', $editAeration, array('detection_Ename' => 'aeration'));
        //吸入氧浓度
        $editIOI['value'] = $this->formataddDataJson(I('POST.IOI'));
        $editIOI['tolerance'] = I('post.IOI_tolerance');
        $this->updateData('quality_preset', $editIOI, array('detection_Ename' => 'IOI'));
        //吸气压力水平
        $editIPAP['value'] = $this->formataddDataJson(I('POST.IPAP'));
        $editIPAP['tolerance'] = I('post.IPAP_tolerance');
        $this->updateData('quality_preset', $editIPAP, array('detection_Ename' => 'IPAP'));
        //呼气末正压
        $editPEEP['value'] = $this->formataddDataJson(I('POST.PEEP'));
        $editPEEP['tolerance'] = I('post.PEEP_tolerance');
        $this->updateData('quality_preset', $editPEEP, array('detection_Ename' => 'PEEP'));
        //单极电切
        $editUnipolar_cutting['value'] = $this->formataddDataJson(I('POST.Unipolar_cutting'));
        $editUnipolar_cutting['tolerance'] = I('post.Unipolar_cutting_tolerance');
        $this->updateData('quality_preset', $editUnipolar_cutting, array('detection_Ename' => 'Unipolar_cutting'));
        //单极电凝
        $editUnipolar_cutting['value'] = $this->formataddDataJson(I('POST.Unipolar_coagulation'));
        $editUnipolar_cutting['tolerance'] = I('post.Unipolar_coagulation_tolerance');
        $this->updateData('quality_preset', $editUnipolar_cutting, array('detection_Ename' => 'Unipolar_coagulation'));
        //双极电切
        $editUnipolar_cutting['value'] = $this->formataddDataJson(I('POST.Bipolar_resection'));
        $editUnipolar_cutting['tolerance'] = I('post.Bipolar_resection_tolerance');
        $this->updateData('quality_preset', $editUnipolar_cutting, array('detection_Ename' => 'Bipolar_resection'));
        //双极电凝
        $editUnipolar_cutting['value'] = $this->formataddDataJson(I('POST.Bipolar_coagulation'));
        $editUnipolar_cutting['tolerance'] = I('post.Bipolar_coagulation_tolerance');
        $this->updateData('quality_preset', $editUnipolar_cutting, array('detection_Ename' => 'Bipolar_coagulation'));

    }

    //格式化添加模板json格式
    private function formataddDataJson($str)
    {
        if ($str == '') {
            return '';
        }
        return json_encode(explode(',', implode(',', array_filter(explode(',', trim($str, ','))))), JSON_UNESCAPED_SLASHES);
    }

    /**
     * Notes: 保存启用数据
     * @return array
     */
    public function saveStartData()
    {
        $qsid = trim(I('POST.qsid'), ',');
        $data['do_date'] = I('POST.do_date');
        $data['qtemid'] = I('POST.templates');
        $data['qiid'] = I('POST.instrument');
        $data['qi_model'] = trim(I('POST.model'));
        $data['qi_productid'] = trim(I('POST.serialnum'));
        $data['qi_metering_num'] = trim(I('POST.num'));
        $data['qdbid'] = implode(',', I('POST.basis'));
        $data['is_start'] = 1;
        $data['is_cycle'] = isset($_POST['is_cycle']) ? 1 : 0;
        if (!$data['qdbid']) {
            return array('status' => -1, 'msg' => '请选择检测依据！');
        }
        $qsInfo = $this->DB_get_all('quality_starts', 'plan_identifier,plan_name,plan_num,start_username,assid,period,username,userid,qsid', array('qsid' => array('in', $qsid)));
        if ($data['is_cycle']) {
            if (!$_POST['cycle'] || $_POST['cycle'] <= 0) {
                return array('status' => -1, 'msg' => '请填写合理的周期！');
            }
            $data['cycle'] = $_POST['cycle'] ? $_POST['cycle'] : '';
            //周期执行时，计算出当期预计结束日期
            $cycle = $data['cycle'];
            $data['end_date'] = date('Y-m-d', strtotime("+$cycle month", strtotime($data['do_date'])));
            $data['period'] = $qsInfo[0]['period'] ? $qsInfo[0]['period'] : 1;
        }
        unset($_POST['qsid']);
        unset($_POST['do_date']);
        unset($_POST['templates']);
        unset($_POST['instrument']);
        unset($_POST['model']);
        unset($_POST['serialnum']);
        unset($_POST['num']);
        unset($_POST['is_cycle']);
        unset($_POST['cycle']);
        unset($_POST['basis']);
        unset($_POST['type']);
        unset($_POST['planName']);
        $data['start_preset'] = json_encode($_POST, JSON_UNESCAPED_UNICODE);
        $data['edittime'] = getHandleDate(time());
        $data['edituser'] = session('username');
        $data['start_date'] = getHandleTime(time());
        $data['start_userid'] = session('userid');
        $data['start_username'] = session('username');

        //各项目明细的最大允差值
        $join = "LEFT JOIN sb_quality_preset AS B ON A.qprid = B.qprid";
        $fileds = "B.detection_Ename,B.tolerance,B.value,A.qtemid";
        $temp = $this->DB_get_all_join('qualiyt_preset_template', 'A', $fileds, $join, array('A.qtemid' => $data['qtemid']),'','','');
        $tolerance = $start_preset = $Electrosurgical= $baby= [];
        foreach ($temp as &$V) {
            $tolerance[$V['detection_Ename']] = $V['tolerance'];
            $start_preset[$V['detection_Ename']] = json_decode($V['value']);
            if ($V['detection_Ename']=='Unipolar_mode'||$V['detection_Ename']=='Bipolar_mode') {
                $Electrosurgical[$V['detection_Ename']] = json_decode($V['value']);
            }
            if ($V['qtemid']=='7') {
                $baby[$V['detection_Ename']] = json_decode($V['value']);
            }
        }
        if ($data['qtemid']=='5') {
            $data['start_preset']=json_encode($start_preset, JSON_UNESCAPED_UNICODE);
        }else if ($data['qtemid']=='6'){
            $_POST['Unipolar_mode']=$Electrosurgical['Unipolar_mode'];
            $_POST['Bipolar_mode']=$Electrosurgical['Bipolar_mode'];
            $data['start_preset'] = json_encode($_POST, JSON_UNESCAPED_UNICODE);
        }else if ($data['qtemid']=='7') {
            $data['start_preset']=json_encode($baby, JSON_UNESCAPED_UNICODE);
        }
        $tolerance = json_encode($tolerance, JSON_UNESCAPED_UNICODE);
        $data['tolerance'] = $tolerance;

        $res = $this->updateData('quality_starts', $data, array('qsid' => array('in', $qsid)));
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        if ($res) {
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->MODULE);
            if ($settingData) {
                //有开启短信 通知执行人执行计划
                foreach ($qsInfo as &$qsV) {
                    $assets = $this->DB_get_one('assets_info', 'assets,departid', ['assid' => $qsV['assid']]);
                    $qsV['department'] = $departname[$assets['departid']]['department'];
                    $qsV['do_date'] = $data['do_date'];
                    $qsV['start_username'] = session('username');
                    $where = [];
                    $where['status'] = C('OPEN_STATUS');
                    $where['is_delete'] = C('NO_STATUS');
                    $where['userid'] = $qsV['userid'];
                    $userData = $this->DB_get_one('user', 'telephone', $where);
                    if ($settingData['startQualityPlan']['status'] == C('OPEN_STATUS') && $userData['telephone']) {
                        $sms = $this->formatSmsContent($settingData['startQualityPlan']['content'], $qsV);
                        ToolController::sendingSMS($userData['telephone'], $sms, $this->MODULE, $qsid);
                    }
                }
            }
            //==========================================短信 END==========================================

            //获取模板名称
            $tem_name = $this->DB_get_one('quality_templates','template_name',['qtemid'=>$data['qtemid']]);
            if(C('USE_FEISHU') === 1){
                foreach ($qsInfo as &$qsV) {
                    //==========================================飞书 START========================================
                    //要显示的字段区域
                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**计划名称：**'.$qsV['plan_name'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**计划类型：**质控计划';
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**计划状态：**已启用';
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**执行人：**'.$qsV['username'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '质控计划已启用，请及时实施';
                    $feishu_fields[] = $fd;

                    //按钮区域
                    $act['tag'] = 'button';
                    $act['type'] = 'primary';
                    $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME').'/#' . C('FS_NAME').'/Quality/setQualityDetail/'.$tem_name['template_name'].'?qsid='.$qsV['qsid'];
                    $act['text']['tag'] = 'plain_text';
                    $act['text']['content'] = '实施';
                    $feishu_actions[] = $act;

                    $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                    $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                    $card_data['elements'][0]['tag'] = 'div';
                    $card_data['elements'][0]['fields'] = $feishu_fields;
                    $card_data['elements'][1]['tag'] = 'hr';
                    $card_data['elements'][2]['actions'] = $feishu_actions;
                    $card_data['elements'][2]['layout'] = 'bisected';
                    $card_data['elements'][2]['tag'] = 'action';
                    $card_data['header']['template'] = 'blue';
                    $card_data['header']['title']['content'] = '质控计划启用提醒';
                    $card_data['header']['title']['tag'] = 'plain_text';

                    $where = [];
                    $where['status'] = C('OPEN_STATUS');
                    $where['is_delete'] = C('NO_STATUS');
                    $where['userid'] = $qsV['userid'];
                    $userData = $this->DB_get_one('user', 'telephone,openid', $where);
                    $this->send_feishu_card_msg($userData['openid'],$card_data);
                    //==========================================飞书 END==========================================
                }
            }else{
                //==========================================微信 START==========================================
                $moduleModel = new ModuleModel();
                $wx_status = $moduleModel->decide_wx_login();
                if ($wx_status) {
                    foreach ($qsInfo as &$qsV) {
                        $where = [];
                        $where['status'] = C('OPEN_STATUS');
                        $where['is_delete'] = C('NO_STATUS');
                        $where['userid'] = $qsV['userid'];
                        $userData = $this->DB_get_one('user', 'telephone,openid', $where);
                        if ($userData['openid']) {
                            if(C('USE_VUE_WECHAT_VERSION')){
                                $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME').'/#' . C('VUE_NAME').'/Quality/setQualityDetail/'.$tem_name['template_name'].'?qsid='.$qsV['qsid'];
                            }else{
                                $redecturl = C('HTTP_HOST') . C('MOBILE_NAME').'/Quality/setQualityDetail.html?qsid='.$qsV['qsid'];
                            }
                            Weixin::instance()->sendMessage($userData['openid'], '工单处理提醒', [
                                'thing10' => '质控计划',// 工单类型
                                'thing9'  => $qsV['plan_name'],// 工单名称
                                'time39'  => $data['start_date'],// 发起时间
                                'thing7'  => $qsV['username'],// 发起人员
                                'const56' => '已启用',// 工单阶段
                            ], $redecturl);
                        }
                    }
                }
                //==========================================微信 END==========================================
            }
            return array('status' => 1, 'msg' => '启用计划成功！');
        } else {
            return array('status' => -1, 'msg' => '启用计划失败！');
        }
    }

    /**
     * Notes: 修改启用数据
     * @return array
     */
    public function updateStartData()
    {
        $qsid = I('POST.qsid');
        $data['do_date'] = I('POST.do_date');
        $data['qtemid'] = I('POST.templates');
        $data['qiid'] = I('POST.instrument');
        $data['qi_model'] = trim(I('POST.model'));
        $data['qi_productid'] = trim(I('POST.serialnum'));
        $data['qi_metering_num'] = trim(I('POST.num'));
        $data['qdbid'] = implode(',', I('POST.basis'));
        $data['is_start'] = 1;
        $data['is_cycle'] = isset($_POST['is_cycle']) ? 1 : 0;
        if (!$data['qdbid']) {
            return array('status' => -1, 'msg' => '请选择检测依据！');
        }
        if ($data['is_cycle']) {
            if (!$_POST['cycle'] || $_POST['cycle'] <= 0) {
                return array('status' => -1, 'msg' => '请填写合理的周期！');
            }
            $data['cycle'] = $_POST['cycle'] ? $_POST['cycle'] : '';
        } else {
            $data['cycle'] = '';
        }
        unset($_POST['qsid']);
        unset($_POST['do_date']);
        unset($_POST['templates']);
        unset($_POST['instrument']);
        unset($_POST['model']);
        unset($_POST['serialnum']);
        unset($_POST['num']);
        unset($_POST['is_cycle']);
        unset($_POST['cycle']);
        unset($_POST['basis']);
        unset($_POST['type']);
        $join = "LEFT JOIN sb_quality_preset AS B ON A.qprid = B.qprid";
        $fileds = "B.detection_Ename,B.tolerance,B.value,A.qtemid";
        $temp = $this->DB_get_all_join('qualiyt_preset_template', 'A', $fileds, $join, array('A.qtemid' => $data['qtemid']),'','','');
        $start_preset = $Electrosurgical= $baby= [];
        foreach ($temp as &$V) {
            $start_preset[$V['detection_Ename']] = json_decode($V['value']);
            if ($V['detection_Ename']=='Unipolar_mode'||$V['detection_Ename']=='Bipolar_mode') {
                $Electrosurgical[$V['detection_Ename']] = json_decode($V['value']);
            }
            if ($V['qtemid']=='7') {
                $baby[$V['detection_Ename']] = json_decode($V['value']);
            }
        }
        $data['start_preset'] = json_encode($_POST, JSON_UNESCAPED_UNICODE);
        if ($data['qtemid']=='5') {
            $data['start_preset']=json_encode($start_preset, JSON_UNESCAPED_UNICODE);
        }else if ($data['qtemid']=='6'){
            $_POST['Unipolar_mode']=$Electrosurgical['Unipolar_mode'];
            $_POST['Bipolar_mode']=$Electrosurgical['Bipolar_mode'];
            $data['start_preset'] = json_encode($_POST, JSON_UNESCAPED_UNICODE);
        }else if ($data['qtemid']=='7') {
            $data['start_preset']=json_encode($baby, JSON_UNESCAPED_UNICODE);
        }
        $data['edittime'] = getHandleDate(time());
        $data['edituser'] = session('username');
        $data['start_date'] = getHandleTime(time());
        $data['start_userid'] = session('userid');
        $data['start_username'] = session('username');
        $res = $this->updateData('quality_starts', $data, array('qsid' => $qsid));
        if ($res) {
            return array('status' => 1, 'msg' => '修改计划成功！');
        } else {
            return array('status' => -1, 'msg' => '修改计划失败！');
        }
    }
    /*
    暂存数据
     */
    public function keepquality(){
        $qsid = I('POST.qsid');
        $action = I('POST.action');
        $data = I('POST.');
        $data['edit_time'] = time();
        unset($data['qsid']);
        unset($data['action']);
        $res = $this->updateData('quality_starts', array('keepdata'=>json_encode($data,JSON_UNESCAPED_UNICODE)), array('qsid' => $qsid));
        if ($res) {
            return array('status' => 1, 'msg' => '暂存计划成功！');
        } else {
            return array('status' => -1, 'msg' => '暂存计划失败！');
        }
    }

    /**
     * Notes: 保存明细结果
     */
    public function saveDetail()
    {
        $data['qsid'] = I('POST.qsid');
        $data['exterior'] = I('POST.lookslike') ? I('POST.lookslike') : 1;
        $data['exterior_explain'] = trim(I('POST.lookslike_desc'));
        if ($data['exterior'] == 2) {
            if (!$data['exterior_explain']) {
                return array('status' => -1, 'msg' => '外观功能不符合的请说明情况！');
            }
        }
        $data['score'] = I('POST.fen');
        $data['report'] = I('POST.report');
        $data['result'] = I('POST.total_result');
        $data['remark'] = I('POST.total_desc');
        $data['date'] = getHandleTime(time());
        $data['addtime'] = getHandleDate(time());
        $data['adduser'] = session('username');
        //查询该计划信息
        $qsInfo = $this->DB_get_one('quality_starts', '*', array('qsid' => $data['qsid']));
        if (!$qsInfo) {
            return array('status' => -1, 'msg' => '查找不到计划信息！');
        }
//        if ($qsInfo['is_start'] != 1) {
//            return array('status' => -1, 'msg' => '该计划不是执行中的计划！');
//        }
//        //查找是否已录入明细
//        $detailInfo = $this->DB_get_one('quality_details', 'qdid', array('qsid' => $data['qsid']));
//        if ($detailInfo['qdid']) {
//            return array('status' => -1, 'msg' => '请勿重复操作！');
//        }
        //var_dump($_POST);exit;
        //查询模板的预设值和固定非值
        $join = " LEFT JOIN sb_qualiyt_preset_template AS B ON A.qprid = B.qprid ";
        $preset = $this->DB_get_all_join('quality_preset', 'A', 'A.qprid,A.detection_name,A.detection_Ename,A.unit,B.qtemid', $join, array('B.qtemid' => $qsInfo['qtemid']),'','','');
        $fixed = $this->DB_get_all('quality_template_fixed_details', '*', array('qtemid' => $qsInfo['qtemid']));
        $old_preset = json_decode($qsInfo['start_preset'], true);
        $old_tolerance = json_decode($qsInfo['tolerance'], true);
        $preset_detection = $fixed_detection = $numvalue = array();
        foreach ($preset as $k => $v) {
            $numvalue[] = $v['detection_Ename'];
        }
        foreach ($preset as $k => $v) {
            //获取原来的启动设置记录及误差记录
            $preset[$k]['value'] = json_encode($old_preset[$v['detection_Ename']]);
            $preset[$k]['tolerance'] = $old_tolerance[$v['detection_Ename']];
            foreach ($_POST as $k1 => $v1) {
                if ($v['detection_Ename'] == $k1) {
                    //验证是否填写完整
                    foreach ($v1 as $real_k => $real_value) {
                        $v1[$real_k] = trim($real_value);
                        if ($v1[$real_k] == '') {
                            unset($v1[$real_k]);
                        }
                    }
                    if (count($v1) != count($_POST[$k1])) {
                        return array('status' => -1, 'msg' => '请填写完整的【' . $v['detection_name'] . '】测量值！');
                    }
                    //验证数据合法性
                    if ($k1 == 'pressure') {
                        foreach ($v1 as $real_k => $real_value) {
                            $v1[$real_k] = str_replace(' ', '', $v1[$real_k]);
                            $v1[$real_k] = str_replace('（', '(', $v1[$real_k]);
                            $v1[$real_k] = str_replace('）', ')', $v1[$real_k]);
                            if (strpos($v1[$real_k], '(') === false) {
                                return array('status' => -1, 'msg' => '请填写正确的【' . $v['detection_name'] . '】测量值！如:75/45(55)');
                            }
                            if (strpos($v1[$real_k], '/') === false) {
                                return array('status' => -1, 'msg' => '请填写正确的【' . $v['detection_name'] . '】测量值！如:75/45(55)');
                            }
                        }
                    } else {
                        foreach ($v1 as $real_k => $real_value) {
                            if (!is_numeric($real_value)) {
                                return array('status' => -1, 'msg' => '请填写正确的【' . $v['detection_name'] . '】测量值！');
                            }
                        }
                    }
                    $preset_detection[$v['detection_Ename']] = $v1;
//                    if ($_POST[$v['detection_Ename'] . '_result']) {
//                        $preset_detection[$v['detection_Ename'] . '_result'] = $_POST[$v['detection_Ename'] . '_result'];
//                    }
                    if ($_POST[$v['detection_Ename'] . '_tolerance']) {
                        $preset_detection[$v['detection_Ename'] . '_tolerance'] = $_POST[$v['detection_Ename'] . '_tolerance'];
                    }
                    if ($_POST[$v['detection_Ename'] . '_value']) {
                        $preset_detection[$v['detection_Ename'] . '_value'] = $_POST[$v['detection_Ename'] . '_value'];
                    }
                    if ($_POST[$v['detection_Ename'] . '_value_tolerance']) {
                        $preset_detection[$v['detection_Ename'] . '_value_tolerance'] = $_POST[$v['detection_Ename'] . '_value_tolerance'];
                    }
                    if ($_POST[$v['detection_Ename'] . '_setIE']) {
                        $preset_detection[$v['detection_Ename'] . '_setIE'] = $_POST[$v['detection_Ename'] . '_setIE'];
                    }
                    if ($_POST[$v['detection_Ename'] . '_max_output']) {
                        $preset_detection[$v['detection_Ename'] . '_max_output'] = $_POST[$v['detection_Ename'] . '_max_output'];
                    } else {
                        $preset_detection[$v['detection_Ename'] . '_max_output'] = 0;
                    }
                    if ($_POST[$v['detection_Ename'] . '_max_value']) {
                        $preset_detection[$v['detection_Ename'] . '_max_value'] = $_POST[$v['detection_Ename'] . '_max_value'];
                    } else {
                        $preset_detection[$v['detection_Ename'] . '_max_value'] = 0;
                    }
                }
            }
        }
        foreach ($fixed as $k => $v) {
            foreach ($_POST as $k1 => $v1) {
                if ($v['fixed_detection_Ename'] == $k1) {
                    $fixed_detection[$v['fixed_detection_Ename']] = $v1;
                }
            }
        }
        $data['preset_detection'] = json_encode($preset_detection, JSON_UNESCAPED_UNICODE);
        $data['fixed_detection'] = json_encode($fixed_detection, JSON_UNESCAPED_UNICODE);
        $patrol_data = [];
        $n = 0;
        foreach ($_POST['result'] as $k => $v) {
            $patrol_data[$n]['qsid'] = $qsInfo['qsid'];
            $patrol_data[$n]['ppid'] = $k;
            $patrol_data[$n]['result'] = $v;
            $patrol_data[$n]['abnormal_remark'] = $_POST['abnormal_remark'][$k];
            $patrol_data[$n]['add_time'] = date('Y-m-d H:i:s');
            $patrol_data[$n]['add_user'] = session('username');
            $n++;
        }
        $this->saveResultDetail($data, $preset, $qsInfo['assid']);

        //查询检测结果未填写的项
        $result_where['is_conformity'] = array(array('EXP', 'IS NULL'), 0, 'or');
        $result_where['qsid'] = $data['qsid'];
        $nullids = $this->DB_get_all('quality_result', 'resultid', $result_where);
        foreach ($nullids as $k => $v) {
            $find = $this->DB_get_one('quality_result_detail', 'id', array('resultid' => $v['resultid'], 'is_conformity' => 2));
            if ($find) {
                //找到有项目不符合的记录，修改原记录为不符合
                $this->updateData('quality_result', array('is_conformity' => 2), array('resultid' => $v['resultid']));
            } else {
                //没找到有项目不符合的记录，修改原记录为符合
                $this->updateData('quality_result', array('is_conformity' => 1), array('resultid' => $v['resultid']));
            }
        }
        //查找全部项目是否存在异常
        $have_error = $this->DB_get_one('quality_result', 'resultid', array('is_conformity' => 2, 'qsid' => $data['qsid']));
        if ($have_error['resultid']) {
            //有不合格项目，整个检测结果为不合格
            $data['result'] = 2;//不符合
        } else {
            $data['result'] = 1;//符合
        }
        foreach ($preset_detection as $k => $v) {
            $qr = $this->DB_get_one('quality_result', 'is_conformity', array('qsid' => $data['qsid'], 'detection_Ename' => $k));
            $preset_detection[$k . '_result'] = $qr['is_conformity'];
        }
        $data['preset_detection'] = json_encode($preset_detection, JSON_UNESCAPED_UNICODE);
        if ($_POST['save_edit'] == 'edit') {
            $update_detail['exterior'] = $data['exterior'];
            $update_detail['exterior_explain'] = $data['exterior_explain'];
            $update_detail['result'] = $data['result'];
            $update_detail['remark'] = $data['remark'];
            $update_detail['preset_detection'] = $data['preset_detection'];
            $update_detail['fixed_detection'] = $data['fixed_detection'];
            $update_detail['edituser'] = session('username');
            $update_detail['edittime'] = date('Y-m-d H:i:s');
            $res = $this->updateData('quality_details', $update_detail, array('qsid' => $data['qsid']));
            if (!$res) {
                return array('status' => -1, 'msg' => '修改明细失败！');
            }
            $this->deleteData('quality_details_patrol', array('qsid' => $data['qsid']));
            $this->insertDataALL('quality_details_patrol', $patrol_data);
            return array('status' => 1, 'msg' => '修改明细成功！');
        } else {
            $res = $this->insertData('quality_details', $data);
            if ($res) {
                $this->insertDataALL('quality_details_patrol', $patrol_data);
                //更改计划状态为已完成
                $this->updateData('quality_starts', array('is_start' => 3), array('qsid' => $data['qsid']));
                //判断该设备是否已完成整个质控计划
                if ($qsInfo['is_cycle'] == 0) {
                    //不是周期执行的计划，录入明细后，变更该设备质控计划状态为0
                    $up['quality_in_plan'] = C('NO_STATUS');
                    //修改计划状态为已结束
                    $this->updateData('quality_starts', array('is_start' => 4), array('plans' => $qsInfo['plans'], 'assid' => $qsInfo['assid']));
                } else {
                    //是周期执行的计划，判断是否是最后一期计划
                    if ($qsInfo['cycle'] == $qsInfo['period']) {
                        //统计计划数
                        $totalPlans = $this->DB_get_count('quality_starts', array('plans' => $qsInfo['plans']));
                        //统计已完成计划数
                        $completedPlans = $this->DB_get_count('quality_starts', array('plans' => $qsInfo['plans'], 'is_start' => 3));
                        if ($totalPlans == $completedPlans) {
                            //是最后一期，修改质控状态为0
                            $up['quality_in_plan'] = C('NO_STATUS');
                            //修改计划状态为已结束
                            $this->updateData('quality_starts', array('is_start' => 4), array('plans' => $qsInfo['plans'], 'assid' => $qsInfo['assid']));
                        }
                    }
                }
                //更改设备表最后检查日期检查人等信息
                $up['lasttesttime'] = getHandleDate(time());
                $up['lasttestuser'] = session('username');
                $up['lasttestresult'] = $data['result'];
                $this->updateData('assets_info', $up, array('assid' => $qsInfo['assid']));
                //记录设备变更信息
//            $this->updateAssetsStatus($qsInfo['assid'], C('ASSETS_STATUS_USE'), $remark = '完成质控计划');
                return array('status' => 1, 'msg' => '录入明细成功！');
            } else {
                return array('status' => -1, 'msg' => '录入明细失败！');
            }
        }
    }

    public function saveResultDetail($data, $preset, $assid)
    {
        //查询固定非值明细项
        $qualitydetail = M("quality_template_fixed_details"); // 实例化User对象
        // 获取ID为3的用户的昵称
        $fixed_detection_name = $qualitydetail->where('qtemid = ' . $preset[0]['qtemid'])->getField('fixed_detection_Ename,fixed_detection_name');
        $hospital_id = session('current_hospitalid');
        $insertData = $tolerance = $old_preset = [];
        $preset_result = json_decode($data['preset_detection'], true);
        $fixed_detection = json_decode($data['fixed_detection'], true);
        $fixed_detection_name['exterior'] = '外观功能';
        $fixed_detection['exterior'] = $_POST['lookslike'] ? $_POST['lookslike'] : 0;
        $save_edit = $_POST['save_edit'];
        foreach ($preset as $k => $v) {
            $insertData[$k]['qsid'] = $data['qsid'];
            $insertData[$k]['hospital_id'] = $hospital_id;
            $insertData[$k]['qtemid'] = $v['qtemid'];
            $insertData[$k]['assid'] = $assid;
            $insertData[$k]['add_date'] = date('Y-m-d');
            $insertData[$k]['detection_Ename'] = $v['detection_Ename'];
            $insertData[$k]['detection_name'] = $v['detection_name'];
            $insertData[$k]['unit'] = $v['unit'];
            if ($save_edit == 'edit') {
                $insertData[$k]['is_conformity'] = '';
            } else {
                $insertData[$k]['is_conformity'] = $preset_result[$v['detection_Ename'] . '_result'];
            }

            //提取启动时的设置值及误差值
            $tolerance[$v['detection_Ename']] = $v['tolerance'];
            $old_preset[$v['detection_Ename']] = json_decode($v['value'], true);
        }
        $len = count($insertData);
        foreach ($fixed_detection_name as $k => $v) {
            $insertData[$len]['qsid'] = $data['qsid'];
            $insertData[$len]['hospital_id'] = $hospital_id;
            $insertData[$len]['qtemid'] = $preset[0]['qtemid'];
            $insertData[$len]['assid'] = $assid;
            $insertData[$len]['add_date'] = date('Y-m-d');
            $insertData[$len]['detection_Ename'] = $k;
            $insertData[$len]['detection_name'] = $v;
            $insertData[$len]['unit'] = '';
            $insertData[$len]['is_conformity'] = $fixed_detection[$k] ? $fixed_detection[$k] : 0;
            $len++;
        }
        //去掉误差中的文字信息
        foreach ($tolerance as $k => $v) {
            preg_match_all("/[\x{4e00}-\x{9fa5}]+/u", $v, $match);
            foreach ($match as $k1 => $v1) {
                $tolerance[$k] = str_replace($match[$k1], '', $v);
                $tolerance[$k] = str_replace($match[1], '', $tolerance[$k]);
            }
        }
        unset($old_preset['charge']);
        if (!$insertData) {
            return array('status' => -1, 'msg' => '没有要记录的明细');
        }
        if ($save_edit == 'edit') {
            //更新修改值
            //查找原记录
            $detailInfo = $this->DB_get_one('quality_details', 'qdid,preset_detection,fixed_detection', array('qsid' => $data['qsid']));
            $this->updateData('quality_details', array('pre_preset_detection' => $detailInfo['preset_detection'], 'pre_fixed_detection' => $detailInfo['fixed_detection']), array('qdid' => $detailInfo['qdid']));
            //修改
            foreach ($insertData as $k => $v) {
                $this->updateData('quality_result', array('is_conformity' => $v['is_conformity'], 'edit_date' => date('Y-m-d')), array('qsid' => $v['qsid'], 'detection_Ename' => $v['detection_Ename']));
            }
        } else {
            $this->insertDataALL('quality_result', $insertData);
        }
        //根据类型选择限制值用于通用电气
        if ($fixed_detection['App_types']=='2') {
           unset($old_preset['patient_abnormal'][0]);
           unset($old_preset['patient_normal'][0]);
           unset($old_preset['aid_normal'][0]);
           unset($old_preset['aid_abnormal'][0]);
        }
        //删除多余的设置值
        foreach ($old_preset as $k => $v) {
            foreach ($preset_result as $k1 => $v1) {
                if ($k == $k1) {
                    $max_len_s = count($v1);
                    $max_len_e = count($v);
                    array_splice($old_preset[$k], $max_len_s, $max_len_e);
                }
            }
        }
        //查询记录的明细
        $results = $this->DB_get_all('quality_result', '*', array('qsid' => $data['qsid']));
        //组织明细数据
        foreach ($results as $k => $v) {
            $detailData = [];
            foreach ($old_preset[$v['detection_Ename']] as $k1 => $v1) {
                $detailData[$k1]['resultid'] = $v['resultid'];
                $detailData[$k1]['detail_name'] = $v['detection_name'];
                $detailData[$k1]['detail_Ename'] = $v['detection_Ename'];
                $detailData[$k1]['add_date'] = $v['add_date'];
                //设定值、误差、实测值
                $detailData[$k1]['scope_value'] = $v1;
                $detailData[$k1]['tolerance'] = $tolerance[$v['detection_Ename']];
                $detailData[$k1]['measured_value'] = $preset_result[$v['detection_Ename']][$k1];
                //根据设定值、误差、实测值计算是否符合
                switch ($v['detection_Ename']) {
                    case 'Humidity_detection':
                        $new_tolerance = str_replace('±', '', $tolerance[$v['detection_Ename']]);
                        if (strpos($new_tolerance, '%') !== false) {
                            $new_tolerance = str_replace('%', '', $new_tolerance);
                            $baifen = round($new_tolerance / 100, 2);
                            $min = $preset_result[$v['detection_Ename']][0] * (1 - $baifen);
                            $max = $preset_result[$v['detection_Ename']][0] * (1 + $baifen);
                        } else {
                            $min = $preset_result[$v['detection_Ename']][0] - $new_tolerance;
                            $max = $preset_result[$v['detection_Ename']][0] + $new_tolerance;
                        }
                        if ($preset_result[$v['detection_Ename']][1] >= $min && $preset_result[$v['detection_Ename']][1] <= $max) {
                            $detailData[$k1]['is_conformity'] = 1;//符合
                        } else {
                            $detailData[$k1]['is_conformity'] = 2;//不符合
                        }
                        break;
                    case 'insulation':
                    case 'Alarm_sound_level_test':
                        if ($preset_result[$v['detection_Ename']][$k1]>=$v1) {
                            $detailData[$k1]['is_conformity'] = 1;//符合
                        } else {
                            $detailData[$k1]['is_conformity'] = 2;//不符合
                        }
                        break;
                    case 'Bipolar_mode':
                    case 'Unipolar_mode':
                    case 'protection':
                    case 'earthleakagecurrent':
                    case 'Case_normal':
                    case 'Case_abnormal':
                    case 'Temperature_deviation':
                    case 'Temperature_uniformity':
                    case 'Volatility':
                    case 'Temperature_control_deviation':
                    case 'Normal_noise_detection_in_the_box':
                    case 'Alarm_noise_test_in_the_box':
                        if ($preset_result[$v['detection_Ename']][$k1]<=$v1) {
                            $detailData[$k1]['is_conformity'] = 1;//符合
                        } else {
                            $detailData[$k1]['is_conformity'] = 2;//不符合
                        }
                        break;
                    case 'patient_normal':
                    case 'patient_abnormal':
                    case 'aid_normal':
                    case 'aid_abnormal':
                        if ($preset_result[$v['detection_Ename']][$k1]<=$v1) {
                            $detailData[$k1]['is_conformity'] = 1;//符合
                        } else {
                            $detailData[$k1]['is_conformity'] = 2;//不符合
                        }
                        break;
                    case 'heartRate':
                        preg_match_all("/(?:\()(.*)(?:\))/i", $v1, $match_heartRate);
                        $min_max = explode('~', $match_heartRate[1][0]);
                        $min = $min_max[0];
                        $max = $min_max[1];
                        if ($preset_result[$v['detection_Ename']][$k1] >= $min && $preset_result[$v['detection_Ename']][$k1] <= $max) {
                            $detailData[$k1]['is_conformity'] = 1;//符合
                        } else {
                            $detailData[$k1]['is_conformity'] = 2;//不符合
                        }
                        break;
                    case 'pressure':
                        preg_match("/(?:\()(.*)(?:\))/i", $v1, $match_pressure);
                        $v1 = str_replace($match_pressure[0], '', $v1);
                        $min_max = explode('/', $v1);
                        $pre_res = substr($preset_result[$v['detection_Ename']][$k1], 0, strpos($preset_result[$v['detection_Ename']][$k1], '('));
                        $pre_res = explode('/', $pre_res);
                        $to_res = substr($tolerance[$v['detection_Ename']], strpos($tolerance[$v['detection_Ename']], '('));
                        $to_res = str_replace('(', '', $to_res);
                        $to_res = str_replace(')', '', $to_res);
                        $to_res = str_replace('±', '', $to_res);
                        $to_res = str_replace('mmHg', '', $to_res);
                        $detailData[$k1]['is_conformity'] = 1;//符合
                        if ($pre_res[0] > ($min_max[0] + $to_res) || $pre_res[0] < ($min_max[0] - $to_res)) {
                            $detailData[$k1]['is_conformity'] = 2;//符合
                        }
                        if ($pre_res[1] > ($min_max[1] + $to_res) || $pre_res[1] < ($min_max[1] - $to_res)) {
                            $detailData[$k1]['is_conformity'] = 2;//符合
                        }
                        break;
                    default:
                        $new_tolerance = str_replace('±', '', $tolerance[$v['detection_Ename']]);
                        if (strpos($new_tolerance, '+') !== false) {
                            $jia = explode('+', $new_tolerance);
                            $a = 0;
                            foreach ($jia as $jk => $jiaval) {
                                if (strpos($jiaval, '%') !== false) {
                                    $jiaval = str_replace('%', '', $jiaval);
                                    $a += $old_preset[$v['detection_Ename']][$k1] * $jiaval / 100;
                                    unset($jia[$jk]);
                                } else {
                                    $a += $jiaval;
                                }
                            }
                            $min = $old_preset[$v['detection_Ename']][$k1] - $a;
                            $max = $old_preset[$v['detection_Ename']][$k1] + $a;
                        } elseif (strpos($new_tolerance, '-') !== false) {
                            $jian = explode('-', $new_tolerance);
                            $a = 0;
                            foreach ($jian as $jk => $jiaval) {
                                if (strpos($jiaval, '%') !== false) {
                                    $jiaval = str_replace('%', '', $jiaval);
                                    $a += $old_preset[$v['detection_Ename']][$k1] * $jiaval / 100;
                                    unset($jian[$jk]);
                                } else {
                                    $a -= $jiaval;
                                }
                            }
                            if ($a < 0) {
                                $max = $old_preset[$v['detection_Ename']][$k1] - $a;
                                $min = $old_preset[$v['detection_Ename']][$k1] + $a;
                            } else {
                                $min = $old_preset[$v['detection_Ename']][$k1] - $a;
                                $max = $old_preset[$v['detection_Ename']][$k1] + $a;
                            }
                        } elseif (strpos($new_tolerance, '*') !== false) {
                            $cheng = explode('*', $new_tolerance);
                            $a = 0;
                            foreach ($cheng as $jk => $jiaval) {
                                if (strpos($jiaval, '%') !== false) {
                                    $jiaval = str_replace('%', '', $jiaval);
                                    $a += $old_preset[$v['detection_Ename']][$k1] * $jiaval / 100;
                                    unset($cheng[$jk]);
                                } else {
                                    $a = $a * $jiaval;
                                }
                            }
                            $min = $old_preset[$v['detection_Ename']][$k1] - $a;
                            $max = $old_preset[$v['detection_Ename']][$k1] + $a;
                        } elseif (strpos($new_tolerance, '/') !== false) {
                            $chu = explode('/', $new_tolerance);
                            $a = 0;
                            foreach ($chu as $jk => $jiaval) {
                                if (strpos($jiaval, '%') !== false) {
                                    $jiaval = str_replace('%', '', $jiaval);
                                    $a += $old_preset[$v['detection_Ename']][$k1] * $jiaval / 100;
                                    unset($chu[$jk]);
                                } else {
                                    $a = $a / $jiaval;
                                }
                            }
                            $min = $old_preset[$v['detection_Ename']][$k1] - $a;
                            $max = $old_preset[$v['detection_Ename']][$k1] + $a;
                        } else {
                            if (strpos($new_tolerance, '%') !== false) {
                                $new_tolerance = str_replace('%', '', $new_tolerance);
                                $baifen = round($new_tolerance / 100, 2);
                                $min = $old_preset[$v['detection_Ename']][$k1] * (1 - $baifen);
                                $max = $old_preset[$v['detection_Ename']][$k1] * (1 + $baifen);
                            } else {
                                $min = $old_preset[$v['detection_Ename']][$k1] - $new_tolerance;
                                $max = $old_preset[$v['detection_Ename']][$k1] + $new_tolerance;
                            }
                        }
                        if ($preset_result[$v['detection_Ename']][$k1] >= $min && $preset_result[$v['detection_Ename']][$k1] <= $max) {
                            $detailData[$k1]['is_conformity'] = 1;//符合
                        } else {
                            $detailData[$k1]['is_conformity'] = 2;//不符合
                        }
                        break;
                }
            }
            if ($detailData) {
                if ($save_edit == 'edit') {
                    foreach ($detailData as $nk => $nv) {
                        $this->updateData('quality_result_detail', array('measured_value' => $nv['measured_value'], 'is_conformity' => $nv['is_conformity'], 'edit_date' => date('Y-m-d')), array('resultid' => $nv['resultid'], 'detail_Ename' => $nv['detail_Ename'], 'scope_value' => $nv['scope_value']));
                    }
                } else {
                    $this->insertDataALL('quality_result_detail', $detailData);
                }
            }
        }
    }

    /**
     * Notes: 获取质控明细列表
     */
    public function getCanExecuPlanLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $assets = trim(I('POST.assets'));
        $departid = trim(I('POST.departid'), ',');
        $cycle = I('POST.isSycle');
        $start = I('POST.status');
        $assnum = I('POST.assnum');
        $hospital_id = I('POST.hospital_id');
        if (session('isSuper')) {
            $where['is_start'] = ['GT', 0];
        } elseif (session('is_supplier') == C('YES_STATUS')) {
            $where['is_start'] = ['GT', 0];
            if (session('olsid') > 0) {
                $assets_f = $this->DB_get_one('assets_factory', 'GROUP_CONCAT(assid) AS assid', ['ols_supid' => session('olsid')]);
                if ($assets_f['assid']) {
                    $where['A.assid'] = ['IN', $assets_f['assid']];
                } else {
                    $result['msg'] = '暂无相关数据';
                    $result['code'] = 400;
                    return $result;
                }
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        } else {
            $where['is_start'] = ['GT', 0];
            $where['A.userid'] = ['EQ', session('userid')];
        }
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$sort) {
            $sort = 'plans DESC,assid';
        }
        if (!$order) {
            $order = 'desc';
        }
        if ($assets) {
            //设备名称搜索
            $where['B.assets'] = ['LIKE', "%$assets%"];
        }
        if ($departid) {
            $where['B.departid'] = ['IN', $departid];
        } else {
            if (session('is_supplier') != C('YES_STATUS')) {
                $where['B.departid'] = ['IN', session('departid')];
            }
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = ['EQ', $hospital_id];
        } else {
            $where['B.hospital_id'] = ['EQ', session('current_hospitalid')];
        }
        if ($cycle != '') {
            $where['A.is_cycle'] = ['EQ', $cycle];
        }
        if ($start != '') {
            $where['A.is_start'] = ['EQ', $start];
        }
        if ($assnum) {
            $ass = $this->DB_get_one('assets_info', 'assid', array('assnum' => $assnum));
            $where['A.assid'] = ['EQ', $ass['assid']];
        }
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.*,B.assets,B.assnum,B.departid,B.catid,B.model";
        $total = $this->DB_get_count_join('quality_starts', 'A', $join, $where);
        $plans = $this->DB_get_all_join('quality_starts', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        //搜索查询
        $assidFields = 'group_concat(A.assid) AS assid';
        $assidCount = $this->DB_get_one_join('quality_starts', 'A', $assidFields, $join, $where, '');
        //查询当前用户是否有权限制定质控计划
        $startQuality = get_menu($this->MODULE, 'Quality', 'startQualityPlan');
        //查询当前用户是否有权限修改质控明细
        $editDetail = get_menu($this->MODULE, 'Quality', 'editQualityDetail');
        //查询当前用户是否有权限执行质控计划
        $executePlan = get_menu($this->MODULE, 'Quality', 'setQualityDetail');
        //查询当前用户是否有权限查看质控计划详情
        $showQualityPlan = get_menu($this->MODULE, 'Quality', 'showQualityPlan');
        $departname = array();
        $catname = array();
        $baseSetting = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $qsids = array();
        foreach ($plans as $k => $v) {
            $qsids[] = $v['qsid'];
        }
        //查询报告地址
        $reportqsid = array();
        if ($qsids) {
            $reports = $this->DB_get_all('quality_details', 'qsid,report', array('qsid' => array('in', $qsids)));
            foreach ($reports as $k => $v) {
                $reportqsid[$v['qsid']] = $v['report'];
            }
        }
        foreach ($plans as $k => $v) {
            $qsids[] = $v['qsid'];
            $html = '<div class="layui-btn-group">';
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['category'] = $catname[$v['catid']]['category'];
            $plans[$k]['helpcat'] = $v['helpcatid'] ? $baseSetting['assets']['assets_helpcat']['value'][$v['helpcatid']] : '--';
            $plans[$k]['test_user'] = '--';
            $plans[$k]['test_date'] = '--';
            $plans[$k]['test_result'] = '--';
            if ($showQualityPlan) {
                $html .= $this->returnButtonLink('详情', $showQualityPlan['actionurl'], 'layui-btn layui-btn-xs layui-btn-normal', '', 'data-id= ' . $v['qsid'] . ' lay-event = showPlan');
            }
            $html .= $this->returnButtonLink('打印', C('ADMIN_NAME').'/Quality/scanTemplate.html', 'layui-btn layui-btn-xs layui-bg-gray', '', 'data-id= ' . $v['qsid'] . ' lay-event = printTemp');
            if ($v['is_start'] == 1) {
                if ($executePlan) {
                    $html .= $this->returnButtonLink('待执行', $executePlan['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'data-id= ' . $v['qsid'] . ' lay-event = execute');
                }
            } elseif ($v['is_start'] == 2) {
                $html .= $this->returnButtonLink('已暂停', $startQuality['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'data-id= ' . $v['qsid'] . ' lay-event = showPlan');
            } elseif ($v['is_start'] == 3) {
                $html .= $this->returnButtonLink('已完成', C('ADMIN_NAME').'/Quality/showDetail.html', 'layui-btn layui-btn-xs layui-bg-cyan', '', 'data-id= ' . $v['qsid'] . ' lay-event = showDetail');
            } elseif ($v['is_start'] == 4) {
                $html .= $this->returnButtonLink('已结束', C('ADMIN_NAME').'/Quality/showDetail.html', 'layui-btn layui-btn-xs layui-btn-normal', '', 'data-id= ' . $v['qsid'] . ' lay-event = showDetail');
            }
            if ($v['is_start'] >= 2 && $editDetail) {
                $html .= $this->returnButtonLink('修改', $editDetail['actionurl'], 'layui-btn layui-btn-xs', '', 'data-id= ' . $v['qsid'] . ' lay-event = editDetail');
            } else {
                $html .= $this->returnButtonLink('修改', $editDetail['actionurl'], 'layui-btn layui-btn-xs layui-btn-disabled', '', 'data-id= ' . $v['qsid'] . '');
            }
            if ($reportqsid[$v['qsid']]) {
                //有报告地址
                $html .= $this->returnButtonLink('查看报告', $v['qsid'], 'layui-btn layui-btn-xs layui-btn-warm', '', 'data-id= ' . $v['qsid'] . ' lay-event = scanReport');
            } else {
                //没报告地址
                if ($executePlan) {
                    $html .= $this->returnButtonLink('上传报告', $executePlan['actionurl'], 'layui-btn layui-btn-xs layui-btn-warm', '', 'data-id= ' . $v['qsid'] . ' lay-event = uploadReport');
                }
            }
            $html .= '</div>';
            $plans[$k]['operation'] = $html;
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['assidCount'] = $assidCount['assid'];
        $result['rows'] = $plans;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取报告地址
     */
    public function getReportUrl()
    {
        $qsid = I('POST.qsid');
        $res = $this->DB_get_one('quality_details', 'qsid,report', array('qsid' => $qsid));
        if ($res['report']) {
            return array('status' => 1, 'msg' => '获取报告地址成功！', 'url' => $res['report']);
        } else {
            return array('status' => -1, 'msg' => '暂无报告！');
        }
    }

    /**
     * Notes: 获取质控结果查询列表
     */
    public function getAllPlanResult()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $assets = trim(I('POST.assets'));
        $departid = trim(I('POST.departid'), ',');
        $cycle = I('POST.isSycle');
        $start = I('POST.status');
        $qtemid = I('POST.qtemid');
        $assnum = I('POST.assnum');
        $hospital_id = I('POST.hospital_id');
        if (session('isSuper')) {
            $where = " 1 ";
        } else {
            $where = " A.adduser = '" . session('username') . "' OR A.userid = " . session('userid');
        }
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$sort) {
            $sort = 'plans desc,assid';
        }
        if (!$order) {
            $order = 'desc';
        }
        if ($assets) {
            //设备名称搜索
            $where .= " and B.assets like '%" . $assets . "%'";
        }
        if ($departid) {
            $where .= " and B.departid in (" . $departid . ")";
        } else {
            $where .= " and B.departid in (" . session('departid') . ")";
        }
        if ($hospital_id) {
            $where .= " and B.hospital_id = " . $hospital_id;
        } else {
            $where .= " and B.hospital_id = " . session('current_hospitalid');
        }
        if ($cycle != '') {
            $where .= " and A.is_cycle = " . $cycle;
        }
        if ($start != '') {
            $where .= " and A.is_start in(" . $start . ")";
        }
        if ($qtemid != '') {
            $where .= " and A.qtemid in(" . $qtemid . ")";
        }
        if ($assnum) {
            $ass = $this->DB_get_one('assets_info', 'assid', array('assnum' => $assnum));
            $where .= " and A.assid = '" . $ass['assid'] . "'";
        }
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.*,B.assets,B.assnum,B.departid,B.catid,B.model,B.lasttesttime,B.lasttestuser,B.lasttestresult";
        $total = $this->DB_get_count_join('quality_starts', 'A', $join, $where);
        $plans = $this->DB_get_all_join('quality_starts', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        //搜索查询
        $assidFields = 'group_concat(A.assid) AS assid';
        $assidCount = $this->DB_get_one_join('quality_starts', 'A', $assidFields, $join, $where, '');
        //查询当前用户是否有权限制定质控计划
        $startQuality = get_menu($this->MODULE, 'Quality', 'startQualityPlan');
        //查询当前用户是否有权限执行质控计划
        $executePlan = get_menu($this->MODULE, 'Quality', 'setQualityDetail');
        //查询当前用户是否有权限查看质控计划详情
        $showQualityPlan = get_menu($this->MODULE, 'Quality', 'showQualityPlan');
        $departname = array();
        $catname = array();
        $baseSetting = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $qsids = array();
        foreach ($plans as $k => $v) {
            $qsids[] = $v['qsid'];
        }
        //查询报告地址
        $reportqsid = array();
        if ($qsids) {
            $reports = $this->DB_get_all('quality_details', 'qsid,report', array('qsid' => array('in', $qsids)));
            foreach ($reports as $k => $v) {
                $reportqsid[$v['qsid']] = $v['report'];
            }
        }
        foreach ($plans as $k => $v) {
            $html = '<div class="layui-btn-group">';
            $plans[$k]['department'] = $departname[$v['departid']]['department'];
            $plans[$k]['category'] = $catname[$v['catid']]['category'];
            $plans[$k]['helpcat'] = $v['helpcatid'] ? $baseSetting['assets']['assets_helpcat']['value'][$v['helpcatid']] : '--';
            if ($showQualityPlan) {
                $html .= $this->returnButtonLink('详情', $showQualityPlan['actionurl'], 'layui-btn layui-btn-xs layui-btn-normal', '', 'data-id= ' . $v['qsid'] . ' lay-event = showPlan');
            }
            $html .= $this->returnButtonLink('打印', C('ADMIN_NAME').'/Quality/scanTemplate.html', 'layui-btn layui-btn-xs layui-bg-gray', '', 'data-id= ' . $v['qsid'] . ' lay-event = printTemp');
            if ($v['is_start'] == 0) {
                if ($startQuality) {
                    $html .= $this->returnButtonLink('待启动', $startQuality['actionurl'], 'layui-btn layui-btn-xs', '', 'data-id= ' . $v['qsid'] . ' lay-event = start');
                } else {
                    $html .= $this->returnButtonLink('待启动', $startQuality['actionurl'], 'layui-btn layui-btn-xs', '', 'data-id= ' . $v['qsid']);
                }
            } elseif ($v['is_start'] == 1) {
                if ($executePlan) {
                    $html .= $this->returnButtonLink('待执行', $executePlan['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'data-id= ' . $v['qsid'] . ' lay-event = execute');
                }
            } elseif ($v['is_start'] == 2) {
                if ($startQuality) {
                    $html .= $this->returnButtonLink('已暂停', $startQuality['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'data-id= ' . $v['qsid'] . ' lay-event = start');
                } else {
                    $html .= $this->returnButtonLink('已暂停', $startQuality['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'data-id= ' . $v['qsid'] . ' lay-event = showPlan');
                }
            } elseif ($v['is_start'] == 3) {
                $html .= $this->returnButtonLink('已完成', C('ADMIN_NAME').'/Quality/showDetail.html', 'layui-btn layui-btn-xs layui-bg-cyan', '', 'data-id= ' . $v['qsid'] . ' lay-event = showDetail');
            } elseif ($v['is_start'] == 4) {
                $html .= $this->returnButtonLink('已结束', C('ADMIN_NAME').'/Quality/showDetail.html', 'layui-btn layui-btn-xs layui-bg-blue', '', 'data-id= ' . $v['qsid'] . ' lay-event = showDetail');
            }
            if ($reportqsid[$v['qsid']]) {
                //有报告地址
                $html .= $this->returnButtonLink('查看报告', $v['qsid'], 'layui-btn layui-btn-xs layui-btn-warm', '', 'data-id= ' . $v['qsid'] . ' lay-event = scanReport');
            } else {
                //没报告地址
                if ($executePlan) {
                    $html .= $this->returnButtonLink('上传报告', $executePlan['actionurl'], 'layui-btn layui-btn-xs layui-btn-warm', '', 'data-id= ' . $v['qsid'] . ' lay-event = uploadReport');
                }
            }
            //$html .= $this->returnButtonLink('查看报告',$reportqsid[$v['qsid']],'layui-btn layui-btn-xs layui-btn-warm','','data-id= '.$v['qsid'].' lay-event = scanReport');
            $html .= '</div>';
            $plans[$k]['operation'] = $html;
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['assidCount'] = $assidCount['assid'];
        $result['rows'] = $plans;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    public function qualityDepartStatistics()
    {
        $result = [];
        $type = I('POST.type');
        switch ($type) {
            case 'qualitySurveyLists':
                //质控概况
                $result = $this->qualitySurveyLists();
                break;
            case 'getPlanResult':
                //质控结果不合格次数统计
                $result = $this->getPlanResult();
                break;
            case 'faiTypeTerm':
                //质控明细项统计
                $result = $this->faiTypeTerm();
                break;
        }
        return $result;
    }

    //质控概况
    public function qualitySurveyLists()
    {
        $templates = $this->DB_get_all('quality_templates', 'qtemid,name');
        if (!$templates) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $year = I('post.year') ? I('post.year') : date('Y');
        $departid = I('POST.departid') ? explode(',', I('POST.departid')) : $this->getShowDataDepart();
        $where['S.hospital_id'] = session('current_hospitalid');
        $where['A.departid'] = ['IN', $departid];
        $where['S.qtemid'] = ['GT', 0];
        $join = 'LEFT JOIN sb_assets_info as A ON A.assid=S.assid';
        $quality = $this->DB_get_all_join('quality_starts', 'S', 'qsid,qtemid,A.departid', $join, $where,'','','');

        if (!$quality) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }


        $qsid_arr = [];
        $qtemid_arr = [];
        $departid_psid_arr = [];
        foreach ($quality as &$qv) {
            $departid_psid_arr[$qv['qsid']] = $qv['departid'];
            $qsid_arr[] = $qv['qsid'];
            $qtemid_arr[$qv['departid']]['num'][$qv['qtemid']] = isset($qtemid_arr[$qv['departid']]['num'][$qv['qtemid']]) ? $qtemid_arr[$qv['departid']]['num'][$qv['qtemid']] + 1 : 1;
            if (!isset($qtemid_arr[$qv['departid']]['qtemidTotal'])) {
                $qtemid_arr[$qv['departid']]['qtemidTotal'] = 0;
            }
            $qtemid_arr[$qv['departid']]['qtemidTotal']++;

        }


        $where = [];
        $where['D.date'][] = ['EGT', $year . '-01-01'];
        $where['D.date'][] = ['ELT', $year . '-12-31'];
        $where['D.qsid'] = ['IN', $qsid_arr];
        $where[1][]['D.result'] = 1;
        $where[1][]['D.result'] = 2;
        $where[1]['_logic'] = 'OR';
        $fields = "D.result,Q.qtemid,Q.qsid";
        $join = 'LEFT JOIN sb_quality_starts as Q ON Q.qsid=D.qsid';
        $details = $this->DB_get_all_join('quality_details', 'D', $fields, $join, $where,'','','');
        if (!$details) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
//        var_dump($details);
        $details_arr = [];
        foreach ($details as &$detv) {
            $details_arr[$departid_psid_arr[$detv['qsid']]][$detv['qtemid']][$detv['result']] = isset($details_arr[$departid_psid_arr[$detv['qsid']]][$detv['qtemid']][$detv['result']]) ? $details_arr[$departid_psid_arr[$detv['qsid']]][$detv['qtemid']][$detv['result']] + 1 : 1;
        }


        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";


        $totalSuccessNums = 0;
        $templatesData = [];


        //组合图表数据
        //图表1 模板使用概况
        $tempSurveyData = [];
        //图表2 模板合格概况
        $tempQualifiedData = [];


        foreach ($templates as &$temV) {
            $tempSurveyData['legend'][] = $temV['name'];
        }

        $tempQualifiedData['legend'][] = '合格';
        $tempQualifiedData['legend'][] = '不合格';


        $tempValue_arr = [];

        $successData = [];
        $failData = [];
        foreach ($departid as &$depV) {
            $tempSurveyData['xAxis'][] = $departname[$depV]['department'];
            $tempQualifiedData['xAxis'][] = $departname[$depV]['department'];
            $successNums = 0;
            $failNums = 0;
            foreach ($templates as &$temV) {
                $temV['department'] = $departname[$depV]['department'];
                $temV['useNums'] = isset($qtemid_arr[$depV]['num'][$temV['qtemid']]) ? $qtemid_arr[$depV]['num'][$temV['qtemid']] : 0;
                if ($qtemid_arr[$depV]['qtemidTotal'] == 0) {
                    $temV['useRatio'] = '0%';
                } else {
                    $temV['useRatio'] = number_format($temV['useNums'] / $qtemid_arr[$depV]['qtemidTotal'] * 100, 2) . '%';
                }

                $temV['successNums'] = isset($details_arr[$depV][$temV['qtemid']][1]) ? $details_arr[$depV][$temV['qtemid']][1] : 0;
                $temV['failNums'] = isset($details_arr[$depV][$temV['qtemid']][2]) ? $details_arr[$depV][$temV['qtemid']][2] : 0;
                if ($temV['successNums'] + $temV['failNums'] == 0) {
                    $temV['successRatio'] = '0%';
                } else {
                    $temV['successRatio'] = number_format($temV['successNums'] / ($temV['successNums'] + $temV['failNums']) * 100, 2) . '%';
                }

                $totalSuccessNums += $temV['successNums'];
                $templatesData[] = $temV;
                $tempValue_arr[$temV['qtemid']][] = $temV['useNums'];
                $successNums += $temV['successNums'];
                $failNums += $temV['failNums'];
            }
            $successData[] = $successNums;
            $failData[] = $failNums;
        }


        foreach ($templates as &$temV) {
            $tempSurveyDataSeries['name'] = $temV['name'];
            $tempSurveyDataSeries['type'] = 'bar';
            $tempSurveyDataSeries['stack'] = '总量';
            $tempSurveyDataSeries['barMaxWidth'] = '50';
            $tempSurveyDataSeries['label']['normal']['show'] = true;
            $tempSurveyDataSeries['label']['normal']['position'] = 'insideRight';
            $tempSurveyDataSeries['data'] = $tempValue_arr[$temV['qtemid']];
            $tempSurveyData['series'][] = $tempSurveyDataSeries;
        }


        foreach ($tempQualifiedData['legend'] as &$QualLvalue) {
            $tempQualifiedDataSeries['name'] = $QualLvalue;
            $tempQualifiedDataSeries['type'] = 'bar';
            $tempQualifiedDataSeries['barMaxWidth'] = '50';
            $tempQualifiedDataSeries['stack'] = '总量';
            $tempQualifiedDataSeries['label']['normal']['show'] = true;
            $tempQualifiedDataSeries['label']['normal']['position'] = 'insideRight';
            if ($QualLvalue == '合格') {
                $tempQualifiedDataSeries['data'] = $successData;
            } else {
                $tempQualifiedDataSeries['data'] = $failData;
            }
            $tempQualifiedData['series'][] = $tempQualifiedDataSeries;
        }

//        var_dump(1);
//        var_dump($templatesData);


        $result["code"] = 200;
        $result['rows'] = $templatesData;
        $result['tempSurveyData'] = $tempSurveyData;
        $result['tempQualifiedData'] = $tempQualifiedData;
        return $result;

    }


    //质控结果不合格次数统计
    public function getPlanResult()
    {
        $year = I('post.year') ? I('post.year') : date('Y');
        $departid = I('POST.departid') ? I('POST.departid') : $this->getShowDataDepart();
        $where['S.hospital_id'] = session('current_hospitalid');
        $where['A.departid'] = ['IN', $departid];
        $join = 'LEFT JOIN sb_assets_info as A ON A.assid=S.assid';
        $quality = $this->DB_get_all_join('quality_starts', 'S', 'qsid', $join, $where,'','','');
        if (!$quality) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        $where = [];
        $qsid_arr = [];
        foreach ($quality as &$qv) {
            $qsid_arr[] = $qv['qsid'];
        }
        $where['date'][] = ['EGT', $year . '-01-01'];
        $where['date'][] = ['ELT', $year . '-12-31'];
        $where['qsid'] = ['IN', $qsid_arr];
        $where[1][]['result'] = 1;
        $where[1][]['result'] = 2;
        $where[1]['_logic'] = 'OR';
        $fields = "qsid,result";
        $details = $this->DB_get_all('quality_details', $fields, $where);
        if (!$details) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        $qsid_arr = [];
        $detailsData = [];
        foreach ($details as &$datav) {
            $qsid_arr[] = $datav['qsid'];
            $detailsData[$datav['qsid']] = $datav['result'];
        }
        $where = [];
        $where['S.hospital_id'] = session('current_hospitalid');
        $where['A.departid'] = ['IN', $departid];
        $where['S.qsid'] = ['IN', $qsid_arr];
        $join = 'LEFT JOIN sb_assets_info as A ON A.assid=S.assid';
        $fields = 'A.assets,A.assnum,A.assid,S.plan_name,S.qsid,S.plan_identifier,A.departid';
        $quality = $this->DB_get_all_join('quality_starts', 'S', $fields, $join, $where, '', 'S.plan_name','');


        $quResult = [];
        $departname = [];


        $qsid_arr = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($quality as &$quV) {
            $qsid_arr[] = $quV['qsid'];
            if (!isset($quResult[$quV['plan_identifier'] . $quV['assid']])) {
                $qualityData = $quV;
                if ($detailsData[$quV['qsid']] == 1) {
                    $qualityData['successNums'] = 1;
                    $qualityData['failNums'] = 0;
                } else {
                    $qualityData['successNums'] = 0;
                    $qualityData['failNums'] = 1;
                }
                $quResult[$quV['plan_identifier'] . $quV['assid']] = $qualityData;
            } else {
                if ($detailsData[$quV['qsid']] == 1) {
                    $quResult[$quV['plan_identifier'] . $quV['assid']]['successNums']++;
                } else {
                    $quResult[$quV['plan_identifier'] . $quV['assid']]['failNums']++;
                }
            }
            $quResult[$quV['plan_identifier'] . $quV['assid']]['department'] = $departname[$quV['departid']]['department'];
        }

        $quResult = $this->my_array_multisort($quResult, 'plan_identifier');

        //获取符合不符合项
        $fields = 'R.detection_name,R.is_conformity,A.plan_identifier,A.assid';
        $join = 'LEFT JOIN sb_quality_starts as A ON A.qsid=R.qsid';
        $resultQuality = $this->DB_get_all_join('quality_result', 'R', $fields, $join, ['R.qsid' => ['IN', $qsid_arr]],'','','');


        $resultQualityData = [];
        foreach ($resultQuality as &$one) {
            $key = $one['assid'] . $one['plan_identifier'];
            if ($one['is_conformity'] == 1) {
                $resultQualityData[$key]['accord'] = $resultQualityData[$key]['accord'] ? $resultQualityData[$key]['accord'] + 1 : 1;
            } else {
                $resultQualityData[$key]['n_accord'] = $resultQualityData[$key]['n_accord'] ? $resultQualityData[$key]['n_accord'] + 1 : 1;
                $resultQualityData[$key]['n_accord_name'][] = $one['detection_name'];
            }
        }

//        var_dump($resultQualityData);

        foreach ($quResult as &$one) {
            $one['successRatio'] = number_format($one['successNums'] / ($one['failNums'] + $one['successNums']) * 100, 2) . '%';
            $key = $one['assid'] . $one['plan_identifier'];
            $one['accord'] = $resultQualityData[$key]['accord'];
            if ($resultQualityData[$key]['n_accord']) {
                $n_accord_name = join(',', array_unique($resultQualityData[$key]['n_accord_name']));
                $one['n_accord'] = '<span class="rquireCoin ">' . $resultQualityData[$key]['n_accord'] . '</span>  (' . $n_accord_name . ')';
                $one['n_accord_ncolor'] = $resultQualityData[$key]['n_accord'] . '  (' . $n_accord_name . ')';
            } else {
                $one['n_accord'] = 0;
                $one['n_accord_ncolor'] = 0;
            }
        }


        //组织图表数据
        $chartData['legend'][] = '合格';
        $chartData['legend'][] = '不合格';
        $quResultNew = [];

        foreach ($quResult as &$value) {
            if (!isset($quResultNew[$value['plan_identifier']])) {
                $chartData['xAxis'][] = $value['plan_name'];
                $quResultNew[$value['plan_identifier']]['successNums'] = $value['successNums'];
                $quResultNew[$value['plan_identifier']]['failNums'] = $value['failNums'];
            } else {
                $quResultNew[$value['plan_identifier']]['successNums'] += $value['successNums'];
                $quResultNew[$value['plan_identifier']]['failNums'] += $value['failNums'];
            }
        }

        $successNums = [];
        $failNums = [];
        foreach ($quResultNew as &$value) {
            $successNums[] = $value['successNums'];
            $failNums[] = $value['failNums'];
        }
        foreach ($chartData['legend'] as &$QualLvalue) {
            $chartDataSeries['name'] = $QualLvalue;
            $chartDataSeries['type'] = 'bar';
            $chartDataSeries['barMaxWidth'] = '50';
            $chartDataSeries['stack'] = '总量';
            $chartDataSeries['label']['normal']['show'] = true;
            $chartDataSeries['label']['normal']['position'] = 'insideRight';
            if ($QualLvalue == '合格') {
                $chartDataSeries['data'] = $successNums;
            } else {
                $chartDataSeries['data'] = $failNums;
            }
            $chartData['series'][] = $chartDataSeries;
        }

        $result["code"] = 200;
        $result['rows'] = $quResult;
        $result['charData'] = $chartData;
        return $result;
    }

    //质控项不符合数量
    public function faiTypeTerm()
    {
        $year = I('post.year') ? I('post.year') : date('Y');
        $departid = I('POST.departid') ? explode(',', I('POST.departid')) : $this->getShowDataDepart();
        $qtemid = I('POST.templates');
        $where['A.hospital_id'] = session('current_hospitalid');
        $where['A.departid'] = ['IN', $departid];
        $where['S.add_date'][] = ['EGT', $year . '-01-01'];
        $where['S.add_date'][] = ['ELT', $year . '-12-31'];
        $where[1][]['S.is_conformity'] = 1;
        $where[1][]['S.is_conformity'] = 2;
        $where[1]['_logic'] = 'OR';
        $where['S.qtemid'] = ['EQ', $qtemid];
        $join = 'LEFT JOIN sb_assets_info as A ON A.assid=S.assid';
        $fields = 'GROUP_CONCAT(S.is_conformity) AS is_conformity,detection_name';
        $failResult = $this->DB_get_all_join('quality_result', 'S', $fields, $join, $where, 'detection_name','','');

        if (!$failResult) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        $totalCount = 0;
        $new = [];
        $notData = true;
        foreach ($failResult as $key => $v) {
            $new['lists'][$key]['title'] = $v['detection_name'];
            $is_conformity_arr = explode(',', $v['is_conformity']);
            $array_count = array_count_values($is_conformity_arr);
            $new['lists'][$key]['successNum'] = isset($array_count[1]) ? $array_count[1] : 0;
            $new['lists'][$key]['failNum'] = isset($array_count[2]) ? $array_count[2] : 0;

            $new['lists'][$key]['Ratio'] = number_format($new['lists'][$key]['successNum'] / count($is_conformity_arr) * 100, 2);
            $totalCount += $new['lists'][$key]['totalNum'];
            $new['legend'][$key] = $failResult[$key]['detection_name'];
            $new['series']['data'][$key]['name'] = $failResult[$key]['detection_name'];
            $new['series']['data'][$key]['value'] = $new['lists'][$key]['failNum'];
            if ($new['lists'][$key]['failNum'] == 0) {
                $new['selected'][$new['lists'][$key]['title']] = false;
            } else {
                $notData = false;
                $new['selected'][$new['lists'][$key]['title']] = true;
            }
            $new['series']['data'][$key]['precent'] = number_format($new['lists'][$key]['failNum'] / count($is_conformity_arr) * 100, 2) . '%';
        }
        $new['notData'] = $notData;
        $tipsData = $this->getReportTips();
        $new['reportTips'] = $tipsData['tips'];
        $new['tableTh'] = $tipsData['tableTh'];

        return $new;

    }


    /**
     * Notes:获取报表搜索条件范围
     * @param $startTime int 添加时间始
     * @param $endTime int 添加时间末
     * @param $priceMin float 总价区间始
     * @param $priceMax float 总价区间末
     * @return array
     */
    public function getReportTips()
    {
        $year = I('post.year') ? I('post.year') : date('Y');
        $departid = I('POST.departid') ? explode(',', I('POST.departid')) : $this->getShowDataDepart();
        $qtemid = I('POST.templates');
        //报表日期返回

        //获取模板
        $templatesArr = $this->DB_get_all('quality_templates');
        $templates = '';
        foreach ($templatesArr as $v) {
            if ($v['qtemid'] == $qtemid) {
                $templates = $v['name'];
            }
        }
        $tips = '模板：' . $templates . '';
        $tips .= '  年份：' . $year;
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $tips .= '  科室：';
        foreach ($departid as $v) {
            $tips .= $departname[$v]['department'] . ',';
        }
        $tips = trim($tips, ',');
        $data['tips'] = $tips;
        $data['tableTh'] = '模板：' . $templates;
        return $data;
    }

    //导出明细项不符合结果
    public function exportFaiTypeTerm()
    {
        //根据搜索条件获取数据
        $lists = $this->faiTypeTerm();
        $tips = $this->getReportTips();
        $reportTips = $tips['tips'];
        $i = 0;
        $successTotalNum = 0;
        $failTotalNum = 0;
        $sumRatio = 0;
        $showName = array('title' => '质控明细项', 'successNum' => '符合', 'failNum' => '不符合', 'Ratio' => '符合占比');
        foreach ($lists['lists'] as &$one) {
            $data[$i]['title'] = $one['title'];
            $data[$i]['successNum'] = $one['successNum'];
            $data[$i]['failNum'] = $one['failNum'];
            $data[$i]['Ratio'] = $one['Ratio'] . '%';
            $successTotalNum += $one['successNum'];
            $failTotalNum += $one['failNum'];
            $sumRatio += $one['Ratio'];
            $i++;
        }
        $data[$i]['title'] = '合计';
        $data[$i]['successNum'] = $successTotalNum;
        $data[$i]['failNum'] = $failTotalNum;
        $data[$i]['Ratio'] = number_format($successTotalNum / ($successTotalNum + $failTotalNum) * 100, 2) . '%';
        //接收base64图片编码并转化为图片保存
        $base64Data = I('POST.base64Data');
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Data, $result)) {
            //图片格式
            $type = $result[2];
            if (!in_array($type, array('png', 'jpeg'))) {
                return array('status' => -1, 'msg' => '图片格式错误！');
            }
            $filePath = "./Public/uploads/report/";
            if (!file_exists($filePath)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($filePath, 0777, true);
            }
            $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
            $file = $filePath . $fileName;
            if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $base64Data)))) {
                return array('status' => -1, 'msg' => '图表保存出错，请重试！');
            }
            $image = new \Think\Image();
            $image->open($file);
            $imageInfo['width'] = $image->width(); // 返回图片的宽度
            $imageInfo['height'] = $image->height(); // 返回图片的高度
            $imageInfo['type'] = $image->type(); // 返回图片的类型
            $imageInfo['url'] = $file; // 服务器图片地址
            $showLastTotalRow = true; //是否显示最后一行合计行

            $tableHeader = session('current_hospitalname') . '质控明细项统计';
            $otherInfo['titleFontSize'] = 28;
            $otherInfo['titleRowHeight'] = 60;
            $otherInfo['imagePosition'] = 'bottom';
            $otherInfo['imageWidth'] = $image->width();//图片缩放比例
            $otherInfo['imageHeight'] = $image->height();//图片缩放比例
            exportExcelStatistics($sheetTitle = array('质控明细项统计'), $tableHeader, $showName, $data, $imageInfo, $showLastTotalRow, $tableHeader, $reportTips, $otherInfo);
        } else {
            return array('status' => -1, 'msg' => '生成图片错误！');
        }
    }

    //导出 质控结果统计
    public function exportQualityDepart()
    {
        //根据搜索条件获取数据

        //模板概况表
        $qualitySurveyLists = $this->qualitySurveyLists();
        $qualitySurveyData = $qualitySurveyLists['rows'];
        //计划概况表
        $planResult = $this->getPlanResult();
        $planResultData = $planResult['rows'];

        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $xlsName = session('current_hospitalname') . "科室质控概况报表";
        $xlsTitle = iconv('utf-8', 'gb2312', $xlsName);//文件名称
        $fileName = $xlsName;//or $xlsTitle 文件名称可根据自己情况设定
        //生成工作表及写入表头数据
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF');


        $objPHPExcel->createSheet();
        $objPHPExcel->setactivesheetindex(1);
        $objPHPExcel->getActiveSheet()->setTitle('质控模板使用概况');
        $this->write_sheet_header_qualitySurvey($cellName, $objPHPExcel);


        $objPHPExcel->createSheet();
        $objPHPExcel->setactivesheetindex(2);
        $objPHPExcel->getActiveSheet()->setTitle('质控计划概况');
        $objPHPExcel = $this->write_sheet_header_planResult($cellName, $objPHPExcel);

        //定位到 质控模板使用概况
        $objPHPExcel->setactivesheetindex(1);
        foreach ($qualitySurveyData as $key => $val) {
            $qualitySurveyData[$key]['xuhao'] = $key + 1;
        }

        //分组操作 用于导出合并行
        $qualitySurveyData = $this->my_array_multisort($qualitySurveyData, 'department');
        $repeat = $this->returnRepeat($qualitySurveyData, 'department');
        foreach ($qualitySurveyData as &$listsV) {
            foreach ($repeat as &$repeatV) {
                if ($listsV['department'] == $repeatV['department']) {
                    $listsV['sum'] = $repeatV['sum'];
                    $repeatV['sum'] = 0;
                    break;
                }
            }
        }
        //写入数据 质控模板使用概况
        $resultData = $this->write_qualitySurvey_data($cellName, $qualitySurveyData, $objPHPExcel);
        $objPHPExcel = $resultData['objPHPExcel'];
        $qualitySurvey_img = $resultData['img'];


        //定位到 质控计划概况
        $objPHPExcel->setactivesheetindex(2);
        foreach ($planResultData as $key => $val) {
            $planResultData[$key]['xuhao'] = $key + 1;
        }
        //分组操作 用于导出合并行
        $planResultData = $this->my_array_multisort($planResultData, 'plan_name');
        $repeat = $this->returnRepeat($planResultData, 'plan_name');
        foreach ($planResultData as &$listsV) {
            foreach ($repeat as &$repeatV) {
                if ($listsV['plan_name'] == $repeatV['plan_name']) {
                    $listsV['sum'] = $repeatV['sum'];
                    $repeatV['sum'] = 0;
                    break;
                }
            }
        }
        //写入数据 质控计划概况
        $resultData = $this->write_planResult_data($cellName, $planResultData, $objPHPExcel);
        $objPHPExcel = $resultData['objPHPExcel'];
        $planResult_img = $resultData['img'];

        $objPHPExcel->setactivesheetindex(1);
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        foreach ($qualitySurvey_img as &$surveyV) {
            unlink($surveyV['url']);
        }

        unlink($planResult_img['url']);
        exit;
    }

    /**
     * Notes: 通过任务接口新增下一周期计划
     */
    public function addNewPlanForTask()
    {
        //查询所有已完成的周期计划
        $where['is_cycle'] = 1;
        $where['is_start'] = 3;
        $complete = $this->DB_get_all('quality_starts', '*', $where, '', '');
        if (!$complete) {
            return array('status' => 1, 'msg' => 'no plans are added');
        }
        foreach ($complete as $k => $v) {
            if (12 / $v['cycle'] > $v['period']) {
                //整个周期计划还未完成，需生成一个新的设备周期计划
                $data['hospital_id'] = $v['hospital_id'];
                $data['assid'] = $v['assid'];
                $data['plan_identifier'] = $v['plan_identifier'];
                $data['plans'] = $v['plans'];
                $data['userid'] = $v['userid'];
                $data['username'] = $v['username'];
                $data['plan_name'] = $v['plan_name'];
                $assnum = $this->DB_get_one('assets_info', 'assnum', array('assid' => $data['assid']));
                $data['plan_num'] = 'QC' . date('Ymd') . '-' . $assnum['assnum'];
                $data['plan_remark'] = $v['plan_remark'];
//                $data['do_date'] = date('Y-m-d',strtotime("+1 day",strtotime($v['do_date'])));
//                $cycle = $v['cycle'];
//                $data['end_date'] = date('Y-m-d',strtotime("+$cycle month",strtotime($data['do_date'])));
                $data['is_cycle'] = $v['is_cycle'];
                $data['cycle'] = $v['cycle'];
                $data['period'] = $v['period'] + 1;
                $data['addtime'] = getHandleDate(time());
                $data['adduser'] = 'system';
                //查询是否已生成计划，避免重复生成
                $selwhere['plan_identifier'] = $data['plan_identifier'];
                $selwhere['plans'] = $data['plans'];
                $selwhere['assid'] = $data['assid'];
                $selwhere['period'] = $data['period'];
                $isexists = $this->DB_get_one('quality_starts', 'qsid', $selwhere);
                if (!$isexists['qsid'] && $data['cycle'] >= $data['period']) {
                    $this->insertData('quality_starts', $data);
                }
            }
        }
        return array('status' => 1, 'msg' => 'add plans success');
    }

    public function getAllQualities()
    {
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-6 month"));
        $hospital_id = I('post.hospital_id');
        $startDate = I('post.startDate');
        $endDate = I('post.endDate');
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['A.addtime'] = array('egt', $startDate . ' 00:00:00');
        }
        if ($endDate && !$startDate) {
            $where['A.addtime'] = array('elt', $endDate . ' 23:59:59');
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return array('status' => -1, 'msg' => '请选择合理的日期区间！');
            }
            $where['A.addtime'] = array(array('egt', $startDate . ' 00:00:00'), array('elt', $endDate . ' 23:59:59'));
        }
        if (!$startDate && !$endDate) {
            $where['A.addtime'] = array(array('egt', $start_date . ' 00:00:00'), array('elt', $end_date . ' 23:59:59'));
        }
        $overwhere = $notoverwhere = $where;
        $overwhere['A.is_start'] = 4;
        $notoverwhere['A.is_start'] = array('neq', 4);
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        //已结束计划数量
        $over = $this->DB_get_all_join('quality_starts', 'A', 'A.plans', $join, $overwhere, 'A.plans','','');
        //未结束计划数量
        $notOver = $this->DB_get_all_join('quality_starts', 'A', 'A.plans', $join, $notoverwhere, 'A.plans','','');
        $over_num = $over ? count($over) : 0;
        $not_over_num = $notOver ? count($notOver) : 0;
        if ($over_num == 0 && $not_over_num == 0) {
            $result["total"] = 0;
            $result["code"] = 400;
            $result["rows"] = array();
            if (!$result['rows']) {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
            }
            return $result;
        }
        //组织数据
        $tmp = array();
        $tmp['over_data']['total'] = $over_num;
        $tmp['not_over_data']['total'] = $not_over_num;
        //已结束计划设备数量
        $over_assets = $this->DB_get_all_join('quality_starts', 'A', 'A.plans', $join, $overwhere, 'A.assid','','');
        //未结束计划设备数量
        $notOver_assets = $this->DB_get_all_join('quality_starts', 'A', 'A.plans', $join, $notoverwhere, 'A.assid','','');
        $over_assets_num = $over ? count($over_assets) : 0;
        $not_over_assets_num = $notOver ? count($notOver_assets) : 0;
        $tmp['over_data']['assets_num'] = $over_assets_num;
        $tmp['not_over_data']['assets_num'] = $not_over_assets_num;
        //已结束计划设备质控合格数
        $tmp['over_data']['pass_num'] = 0;
        $tmp['over_data']['not_pass_num'] = 0;
        $tmp['not_over_data']['pass_num'] = '--';
        $tmp['not_over_data']['not_pass_num'] = '--';
        //已结束计划
        $qsids = $this->DB_get_one_join('quality_starts', 'A', 'group_concat(qsid) as qsids', $join, $overwhere);
        if ($qsids['qsids']) {
            $results = $this->DB_get_count('quality_details', array('result' => 1, 'qsid' => array('in', $qsids['qsids'])));
            $tmp['over_data']['pass_num'] = $results;
            $tmp['over_data']['not_pass_num'] = count(explode(',', $qsids['qsids'])) - $results;
        }
        $num = 0;
        $res = array();
        foreach ($tmp as $k => $v) {
            if ($k == 'over_data') {
                $res[$num]['type'] = '已结束';
            } else {
                $res[$num]['type'] = '未结束';
            }
            $res[$num]['plans_num'] = $v['total'];
            $res[$num]['assets_num'] = $v['assets_num'];
            $res[$num]['pass_num'] = $v['pass_num'];
            $res[$num]['not_pass_num'] = $v['not_pass_num'];
            $num++;
        }
        $result["total"] = 2;
        $result["code"] = 200;
        $result["rows"] = $res;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 组织图表数据
     * @param $data array 要组织的数据
     * @param $type string 图表类型
     * @return array
     */
    public function formatChartData($data, $type)
    {
        $result = array();
        $result['title']['over_or_not'] = '计划是否结束概况';
        $result['title']['assets_over_or_not'] = '计划设备占比概况';
        $result['title']['pass_over_or_not'] = '已结束计划设备质控合计率概况';
        $result['color']['over_or_not'] = '#481350';
        $result['color']['assets_over_or_not'] = '#3F6F88';
        $result['color']['pass_over_or_not'] = '#778D2C';
        switch ($type) {
            case 'pie':
                $result = $this->getPieData($result, $data);
                break;
            default:
                $result = $this->getBarAndLineData($result, $data, $type);
                break;
        }
        return array('status' => 1, 'data' => $result);
    }

    /**
     * Notes: 饼图数据
     * @param $result
     * @param $data
     * @return mixed
     */
    private function getPieData($result, $data)
    {
        foreach ($data['rows'] as $k => $v) {
            if ($v['type'] == '已结束') {
                //计划数
                $result['over_or_not'][$k]['value'] = $v['plans_num'];
                $result['over_or_not'][$k]['name'] = $v['type'];
                $result['over_or_not'][$k]['itemStyle']['color'] = '#006262';

                //设备数
                $result['assets_over_or_not'][$k]['value'] = $v['assets_num'];
                $result['assets_over_or_not'][$k]['name'] = '已结束计划设备数';
                $result['assets_over_or_not'][$k]['itemStyle']['color'] = '#0DC622';

                //及格数
                $result['pass_over_or_not'][$k]['value'] = $v['pass_num'];
                $result['pass_over_or_not'][$k]['name'] = '合格';
                $result['pass_over_or_not'][$k]['itemStyle']['color'] = '#49DE5A';

                $result['pass_over_or_not'][$k + 1]['value'] = $v['not_pass_num'];
                $result['pass_over_or_not'][$k + 1]['name'] = '不合格';
                $result['pass_over_or_not'][$k + 1]['itemStyle']['color'] = '#006262';
            } else {
                //计划数
                $result['over_or_not'][$k]['value'] = $v['plans_num'];
                $result['over_or_not'][$k]['name'] = $v['type'];
                $result['over_or_not'][$k]['itemStyle']['color'] = '#400D64';

                //设备数
                $result['assets_over_or_not'][$k]['value'] = $v['assets_num'];
                $result['assets_over_or_not'][$k]['name'] = '未结束计划设备数';
                $result['assets_over_or_not'][$k]['itemStyle']['color'] = '#F7552E';
            }
        }
        return $result;
    }

    /**
     * Notes: 折线图和柱形图数据
     * @param $result
     * @param $data
     * @return mixed
     */
    private function getBarAndLineData($result, $data, $type)
    {
        foreach ($data['rows'] as $k => $v) {
            //计划数
            $result['over_or_not']['type'] = $type;
            $result['over_or_not']['xAxis_data'][] = $v['type'];
            $result['over_or_not']['series_data'][] = $v['plans_num'];
            if ($v['type'] == '已结束') {
                //及格数
                $result['pass_over_or_not']['type'] = $type;
                $result['pass_over_or_not']['xAxis_data'][] = '合格';
                $result['pass_over_or_not']['xAxis_data'][] = '不合格';
                $result['pass_over_or_not']['series_data'][] = (int)$data['rows'][$k]['pass_num'];
                $result['pass_over_or_not']['series_data'][] = $data['rows'][$k]['not_pass_num'];
            }
        }
        //设备数
        $result['assets_over_or_not']['type'] = $type;
        $result['assets_over_or_not']['xAxis_data'][] = '已结束计划设备数';
        $result['assets_over_or_not']['xAxis_data'][] = '未结束计划设备数';
        $result['assets_over_or_not']['series_data'][] = $data['rows'][0]['assets_num'];
        $result['assets_over_or_not']['series_data'][] = $data['rows'][1]['assets_num'];
        return $result;
    }

    //格式化短信内容
    public static function formatSmsContent($content, $data)
    {
        $content = str_replace("{plan_name}", $data['plan_name'], $content);
        $content = str_replace("{plan_num}", $data['plan_num'], $content);
        $content = str_replace("{do_date}", $data['do_date'], $content);
        $content = str_replace("{end_date}", $data['end_date'], $content);
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{start_username}", $data['start_username'], $content);
        $content = str_replace("{stop_username}", $data['stop_username'], $content);
        $content = str_replace("{completeNum}", $data['completeNum'], $content);
        $content = str_replace("{toBeDoneNum}", $data['toBeDoneNum'], $content);
        $content = str_replace("{hospital}", $data['hospital'], $content);
        return $content;
    }

    /**
     * Notes: 获取质控计划详情
     * @param $qsid array 要查询的质控ID
     * @param $hospital_id int 医院ID
     * @return mixed
     */
    public function get_complete_detail($qsid, $hospital_id)
    {
        $fields = "A.qsid,A.assid,A.qtemid,A.plan_name,A.plan_num,A.do_date,A.end_date,A.is_cycle,A.cycle,A.period,A.userid,A.username,A.start_preset,A.tolerance,B.*,C.name,C.template_name";
        $join[0] = "LEFT JOIN sb_quality_details AS B ON A.qsid = B.qsid";
        $join[1] = "LEFT JOIN sb_quality_templates AS C ON A.qtemid = C.qtemid";
        $where['A.qsid'] = array('in', $qsid);
        $where['A.hospital_id'] = $hospital_id;
        return $this->DB_get_all_join('quality_starts', 'A', $fields, $join, $where, '', '','');
    }

    public function getAssetsInfo($assids)
    {
        $assModel = M("assets_info");
        $where['assid'] = array('in', $assids);
        return $assModel->where($where)->getField('assid,assets,assnum,catid,departid,model,brand');
    }

    /**
     * Notes: 格式化除颤仪数据
     * @param $chuchanyi
     * @param $assInfo
     * @param $departname
     * @return mixed
     */
    public function format_data_chuchanyi($data, $assInfo, $departname)
    {
        foreach ($data as $k => $v) {
            $data[$k]['xuhao'] = $k + 1;
            $data[$k]['exterior'] = $v['exterior'] == 1 ? '符合' : '不符合(' . $v['exterior_explain'] . ')';
            $data[$k]['is_cycle'] = $v['is_cycle'] == 0 ? '/' : '是';
            $data[$k]['cycle'] = $v['cycle'] ? $v['cycle'] : '/';
            $data[$k]['period'] = $v['period'] ? '第 ' . $v['period'] . ' 期' : '/';
            $data[$k]['complete_date'] = $v['date'];
            $data[$k]['assets'] = $assInfo[$v['assid']]['assets'];
            $data[$k]['assnum'] = $assInfo[$v['assid']]['assnum'];
            $data[$k]['model'] = $assInfo[$v['assid']]['model'];
            $data[$k]['total_result'] = $v['result'] == 1 ? '合格' : ($v['result'] == 2 ? '不合格' : '未填写');
            $data[$k]['department'] = $departname[$assInfo[$v['assid']]['departid']]['department'];
            $start_preset = json_decode($v['start_preset'], true);
            $preset_detection = json_decode($v['preset_detection'], true);
            $tolerance = json_decode($v['tolerance'], true);
            $fixed_detection = json_decode($v['fixed_detection'], true);
            $charge_result = $preset_detection['charge_result'] == 1 ? '(正常)' : '(不正常)';

            $data[$k]['charge'] = $preset_detection['charge'] . $charge_result;
            $data[$k]['internal_discharge'] = $fixed_detection['internal_discharge'] == 1 ? '正常' : '不正常';

            $heartresult = ($preset_detection['heartRate_result'] == 1) ? '符合' : '不符合';
            $heartresult .= '(最大允差：' . $tolerance['heartRate'] . ')';
            $data[$k]['heartRate']['result'] = $heartresult;
            $data[$k]['heartRate']['setting'] = $start_preset['heartRate'];
            $data[$k]['heartRate']['testing'] = $preset_detection['heartRate'];
            array_unshift($data[$k]['heartRate']['setting'], '设定值:');
            array_unshift($data[$k]['heartRate']['testing'], '测量值:');

            $energesisresult = ($preset_detection['energesis_result'] == 1) ? '符合' : '不符合';
            $energesisresult .= '(最大允差：' . $tolerance['energesis'] . ')';
            $data[$k]['energesis']['result'] = $energesisresult;
            $data[$k]['energesis']['setting'] = $start_preset['energesis'];
            $data[$k]['energesis']['testing'] = $preset_detection['energesis'];
            array_unshift($data[$k]['energesis']['setting'], '设定值:');
            array_unshift($data[$k]['energesis']['testing'], '测量值:');
        }
        return $data;
    }

    /**
     * Notes: 写入除颤仪数据到工作表
     * @param $cellName
     * @param $chuchanyi_data
     * @param $objPHPExcel
     * @return mixed
     */
    public function write_chanchuyi_data($cellName, $chuchanyi_data, $objPHPExcel)
    {
        $xlsCell = array(
            '序号',
            '质控计划名称', '质控计划编号', '周期执行', '周期(月)', '期次', '预计执行日期', '实际完成日期', '计划执行人',
            '设备名称', '设备编码', '所属科室', '规格/型号',
            '外观功能', '心率(次/min)', '', '', '', '', '', '释放能量(J)', '', '', '', '', '', '充电时间(s)', '内部放电', '检测结果'
        );
        $showName = array(
            'xuhao' => '序号',

            'plan_name' => '质控计划名称',
            'plan_num' => '质控计划编号',
            'is_cycle' => '周期执行',
            'cycle' => '周期(月)',
            'period' => '期次',
            'do_date' => '预计执行日期',
            'complete_date' => '实际完成日期',
            'username' => '计划执行人',

            'assets' => '设备名称',
            'assnum' => '设备编码',
            'department' => '所属科室',
            'model' => '规格/型号',

            'exterior' => '外观功能',
            'heartRate' => '心率(次/min)',
            'energesis' => '释放能量(J)',
            'charge' => '充电时间(s)',
            'internal_discharge' => '内部放电',
            'total_result' => '检测结果'
        );
        $cellNum = count($xlsCell);
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        //$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        //获取表格表头数据
        $cell = [];
        foreach ($showName as $k => $v) {
            $cell[] = $k;
        }
        foreach ($chuchanyi_data as $k => $v) {
            $j = 0;
            foreach ($cell as $key => $val) {
                if ($val == 'heartRate') {
                    //心率记录
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2));
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 3 + 2))->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 3 + 3))->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 3 + 4))->setRowHeight(20);
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2 + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    //写入结果
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 2), $v[$val]['result']);
                    if (strpos($v[$val]['result'], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val]['result'], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    //写入设定值和测量值
                    foreach ($v[$val]['setting'] as $ks => $vs) {
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 1), $v[$val]['setting'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 2), $v[$val]['testing'][$ks]);
                    }
                    $j = $j + 5;
                } elseif ($val == 'energesis') {
                    //释放能量记录
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2));
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2 + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 2), $v[$val]['result']);
                    if (strpos($v[$val]['result'], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val]['result'], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    //写入设定值和测量值
                    foreach ($v[$val]['setting'] as $ks => $vs) {
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 1), $v[$val]['setting'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 2), $v[$val]['testing'][$ks]);
                    }
                    $j = $j + 5;
                } else {
                    // 合并
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j] . ($k * 3 + 4));
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 2), $v[$val]);
                    if (strpos($v[$val], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val], '不合格') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                }
                $j++;
            }
        }
        //exit;
        //设置单元格边框
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'allborders' => array(                               //allborders  表示全部线框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
                    'color' => array('argb' => '000000'),            //设置border颜色
                ),
            ),
        );
        $lrow = count($chuchanyi_data) * 3 + 1;
        $last = $cellName[$cellNum - 1] . $lrow;
        $objPHPExcel->getActiveSheet()->getStyle("A1:" . $last)->applyFromArray($styleThinBlackBorderOutline);

        $pic_name = ['chuchanyi_result', 'chuchanyi_abnormal_heartRate_3', 'chuchanyi_abnormal_energesis_3', 'chuchanyi_abnormal_other_3'];
        //设置图片路径,只能是本地图片
        foreach ($pic_name as $k => $v) {
            //导入图片到excel
            //实例化插入图片类
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setPath('./Public/uploads/qualities/excel_pic/' . session('userid') . $v . '.png');
            //设置图片要插入的单元格
            if (($k + 1) % 2 == 0) {
                $cn = 'I';
                if ($k - 3 < 0) {
                    $row = $lrow + 3;
                } elseif ($k - 3 == 0) {
                    $row = $lrow + 3 + ($k - 2) * 16;
                } else {
                    $row = $lrow + 3 + ($k - 3) * 16;
                }
            } else {
                $cn = 'B';
                if ($k - 2 < 0) {
                    $row = $lrow + 3;
                } elseif ($k - 2 == 0) {
                    $row = $lrow + 3 + ($k - 1) * 16;
                } else {
                    $row = $lrow + 3 + ($k - 2) * 16;
                }
            }
            $objDrawing->setCoordinates($cn . $row);
            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        }
        return $objPHPExcel;
    }

    /**
     * Notes: 格式化监护仪数据
     * @param $data
     * @param $assInfo
     * @param $departname
     * @return mixed
     */
    public function format_data_jianhuyi($data, $assInfo, $departname)
    {
        foreach ($data as $k => $v) {
            $data[$k]['xuhao'] = $k + 1;
            $data[$k]['exterior'] = $v['exterior'] == 1 ? '符合' : '不符合(' . $v['exterior_explain'] . ')';
            $data[$k]['is_cycle'] = $v['is_cycle'] == 0 ? '/' : '是';
            $data[$k]['cycle'] = $v['cycle'] ? $v['cycle'] : '/';
            $data[$k]['period'] = $v['period'] ? '第 ' . $v['period'] . ' 期' : '/';
            $data[$k]['complete_date'] = $v['date'];
            $data[$k]['assets'] = $assInfo[$v['assid']]['assets'];
            $data[$k]['assnum'] = $assInfo[$v['assid']]['assnum'];
            $data[$k]['model'] = $assInfo[$v['assid']]['model'];
            $data[$k]['total_result'] = $v['result'] == 1 ? '合格' : ($v['result'] == 2 ? '不合格' : '未填写');
            $data[$k]['department'] = $departname[$assInfo[$v['assid']]['departid']]['department'];
            $start_preset = json_decode($v['start_preset'], true);
            $preset_detection = json_decode($v['preset_detection'], true);
            $tolerance = json_decode($v['tolerance'], true);
            $fixed_detection = json_decode($v['fixed_detection'], true);

            $data[$k]['audible_and_visual_alarm'] = $fixed_detection['audible_and_visual_alarm'] == 1 ? '符合' : '不符合';
            $data[$k]['alarm_limit'] = $fixed_detection['alarm_limit'] == 1 ? '符合' : '不符合';
            $data[$k]['mute'] = $fixed_detection['mute'] == 1 ? '符合' : '不符合';

            $heartresult = ($preset_detection['heartRate_result'] == 1) ? '符合' : ($preset_detection['heartRate_result'] == 2 ? '不符合' : '不适用');
            $heartresult .= '(最大允差：' . $tolerance['heartRate'] . ')';
            $data[$k]['heartRate']['result'] = $heartresult;
            $data[$k]['heartRate']['setting'] = $start_preset['heartRate'];
            $data[$k]['heartRate']['testing'] = $preset_detection['heartRate'];
            array_unshift($data[$k]['heartRate']['setting'], '设定值:');
            array_unshift($data[$k]['heartRate']['testing'], '测量值:');

            $breathresult = ($preset_detection['breathRate_result'] == 1) ? '符合' : ($preset_detection['breathRate_result'] == 2 ? '不符合' : '不适用');
            $breathresult .= '(最大允差：' . $tolerance['breathRate'] . ')';
            $data[$k]['breathRate']['result'] = $breathresult;
            $data[$k]['breathRate']['setting'] = $start_preset['breathRate'];
            $data[$k]['breathRate']['testing'] = $preset_detection['breathRate'];
            array_unshift($data[$k]['breathRate']['setting'], '设定值:');
            array_unshift($data[$k]['breathRate']['testing'], '测量值:');

            $pressureresult = ($preset_detection['pressure_result'] == 1) ? '符合' : ($preset_detection['pressure_result'] == 2 ? '不符合' : '不适用');
            $pressureresult .= '(最大允差：' . $tolerance['pressure'] . ')';
            $data[$k]['pressure']['result'] = $pressureresult;
            $data[$k]['pressure']['setting'] = $start_preset['pressure'];
            $data[$k]['pressure']['testing'] = $preset_detection['pressure'];
            array_unshift($data[$k]['pressure']['setting'], '设定值:');
            array_unshift($data[$k]['pressure']['testing'], '测量值:');

            $BOSresult = ($preset_detection['BOS_result'] == 1) ? '符合' : ($preset_detection['BOS_result'] == 2 ? '不符合' : '不适用');
            $BOSresult .= '(最大允差：' . $tolerance['BOS'] . ')';
            $data[$k]['BOS']['result'] = $BOSresult;
            $data[$k]['BOS']['setting'] = $start_preset['BOS'];
            $data[$k]['BOS']['testing'] = $preset_detection['BOS'];
            array_unshift($data[$k]['BOS']['setting'], '设定值:');
            array_unshift($data[$k]['BOS']['testing'], '测量值:');
        }
        return $data;
    }

    /**
     * Notes: 写入监护仪数据到工作表
     * @param $cellName
     * @param $jianhuyi_data
     * @param $objPHPExcel
     * @return mixed
     */
    public function write_jianhuyi_data($cellName, $jianhuyi_data, $objPHPExcel)
    {
        $xlsCell = array(
            '序号',
            '质控计划名称', '质控计划编号', '周期执行', '周期(月)', '期次', '预计执行日期', '实际完成日期', '计划执行人',
            '设备名称', '设备编码', '所属科室', '规格/型号',
            '外观功能', '心率(次/min)', '', '', '', '', '', '呼吸率(次/min)', '', '', '', '', '', '无创血压(mmHg)', '', '', '', '', '', '血氧饱和度(%)', '', '', '', '', '', '声光报警', '报警限检查', '静音检查', '检测结果'
        );
        $showName = array(
            'xuhao' => '序号',

            'plan_name' => '质控计划名称',
            'plan_num' => '质控计划编号',
            'is_cycle' => '周期执行',
            'cycle' => '周期(月)',
            'period' => '期次',
            'do_date' => '预计执行日期',
            'complete_date' => '实际完成日期',
            'username' => '计划执行人',

            'assets' => '设备名称',
            'assnum' => '设备编码',
            'department' => '所属科室',
            'model' => '规格/型号',

            'exterior' => '外观功能',
            'heartRate' => '心率(次/min)',
            'breathRate' => '呼吸率(次/min)',
            'pressure' => '无创血压(mmHg)',
            'BOS' => '血氧饱和度(%)',
            'audible_and_visual_alarm' => '声光报警',
            'alarm_limit' => '报警限检查',
            'mute' => '静音检查',
            'total_result' => '检测结果'
        );
        $cellNum = count($xlsCell);
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        //$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        //获取表格表头数据
        $cell = [];
        foreach ($showName as $k => $v) {
            $cell[] = $k;
        }
        foreach ($jianhuyi_data as $k => $v) {
            $j = 0;
            foreach ($cell as $key => $val) {
                if ($val == 'heartRate') {
                    //心率记录
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2));
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 3 + 2))->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 3 + 3))->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 3 + 4))->setRowHeight(20);
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2 + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    //写入结果
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 2), $v[$val]['result']);
                    if (strpos($v[$val]['result'], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val]['result'], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    //写入设定值和测量值
                    foreach ($v[$val]['setting'] as $ks => $vs) {
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 1), $v[$val]['setting'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 2), $v[$val]['testing'][$ks]);
                    }
                    $j = $j + 5;
                } elseif ($val == 'breathRate') {
                    //呼吸率记录
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2));
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2 + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 2), $v[$val]['result']);
                    if (strpos($v[$val]['result'], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val]['result'], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    //写入设定值和测量值
                    foreach ($v[$val]['setting'] as $ks => $vs) {
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 1), $v[$val]['setting'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 2), $v[$val]['testing'][$ks]);
                    }
                    $j = $j + 5;
                } elseif ($val == 'pressure') {
                    //呼吸率记录
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2));
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2 + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 2), $v[$val]['result']);
                    if (strpos($v[$val]['result'], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val]['result'], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    //写入设定值和测量值
                    foreach ($v[$val]['setting'] as $ks => $vs) {
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 1), $v[$val]['setting'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 2), $v[$val]['testing'][$ks]);
                    }
                    $j = $j + 5;
                } elseif ($val == 'BOS') {
                    //呼吸率记录
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2));
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2 + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 2), $v[$val]['result']);
                    if (strpos($v[$val]['result'], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val]['result'], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j + 5] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    //写入设定值和测量值
                    foreach ($v[$val]['setting'] as $ks => $vs) {
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 1), $v[$val]['setting'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 2 + 2), $v[$val]['testing'][$ks]);
                    }
                    $j = $j + 5;
                } else {
                    // 合并
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 2) . ':' . $cellName[$j] . ($k * 3 + 4));
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    if ($val == 'assnum') {
                        //处理设备编码过长时变为科学计数法的问题
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($cellName[$j] . ($k * 3 + 2), $v[$val], \PHPExcel_Cell_DataType::TYPE_STRING);
                    } else {
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 2), $v[$val]);
                    }
                    if (strpos($v[$val], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val], '不合格') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                }
                $j++;
            }
        }
        //exit;
        //设置单元格边框
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'allborders' => array(                               //allborders  表示全部线框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
                    'color' => array('argb' => '000000'),            //设置border颜色
                ),
            ),
        );
        $lrow = count($jianhuyi_data) * 3 + 1;
        $last = $cellName[$cellNum - 1] . $lrow;
        $objPHPExcel->getActiveSheet()->getStyle("A1:" . $last)->applyFromArray($styleThinBlackBorderOutline);

        $pic_name = ['jianhuyi_result', 'jianhuyi_abnormal_heartRate_1', 'jianhuyi_abnormal_breathRate_1', 'jianhuyi_abnormal_pressure_1', 'jianhuyi_abnormal_BOS_1', 'jianhuyi_abnormal_other_1'];
        //设置图片路径,只能是本地图片
        foreach ($pic_name as $k => $v) {
            //导入图片到excel
            //实例化插入图片类
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setPath('./Public/uploads/qualities/excel_pic/' . session('userid') . $v . '.png');
            //设置图片要插入的单元格
            if (($k + 1) % 2 == 0) {
                $cn = 'I';
                if ($k - 3 < 0) {
                    $row = $lrow + 3;
                } elseif ($k - 3 == 0) {
                    $row = $lrow + 3 + ($k - 2) * 16;
                } else {
                    $row = $lrow + 3 + ($k - 3) * 16;
                }
            } else {
                $cn = 'B';
                if ($k - 2 < 0) {
                    $row = $lrow + 3;
                } elseif ($k - 2 == 0) {
                    $row = $lrow + 3 + ($k - 1) * 16;
                } else {
                    $row = $lrow + 3 + ($k - 2) * 16;
                }
            }
            $objDrawing->setCoordinates($cn . $row);
            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        }

        return $objPHPExcel;
    }

    /**
     * Notes: 格式化输液装置数据
     * @param $shuye
     * @param $assInfo
     * @param $departname
     * @return mixed
     */
    public function format_data_shuye($data, $assInfo, $departname)
    {
        foreach ($data as $k => $v) {
            $data[$k]['xuhao'] = $k + 1;
            $data[$k]['exterior'] = $v['exterior'] == 1 ? '符合' : '不符合(' . $v['exterior_explain'] . ')';
            $data[$k]['is_cycle'] = $v['is_cycle'] == 0 ? '/' : '是';
            $data[$k]['cycle'] = $v['cycle'] ? $v['cycle'] : '/';
            $data[$k]['period'] = $v['period'] ? '第 ' . $v['period'] . ' 期' : '/';
            $data[$k]['complete_date'] = $v['date'];
            $data[$k]['assets'] = $assInfo[$v['assid']]['assets'];
            $data[$k]['assnum'] = $assInfo[$v['assid']]['assnum'];
            $data[$k]['model'] = $assInfo[$v['assid']]['model'];
            $data[$k]['total_result'] = $v['result'] == 1 ? '合格' : ($v['result'] == 2 ? '不合格' : '未填写');
            $data[$k]['department'] = $departname[$assInfo[$v['assid']]['departid']]['department'];
            $start_preset = json_decode($v['start_preset'], true);
            $preset_detection = json_decode($v['preset_detection'], true);
            $tolerance = json_decode($v['tolerance'], true);
            $fixed_detection = json_decode($v['fixed_detection'], true);

            $data[$k]['blocking'] = $fixed_detection['blocking'] == 1 ? '符合' : ($fixed_detection['blocking'] == 2 ? '不符合' : '不适用');
            $data[$k]['forthcoming_empty_bottle'] = $fixed_detection['forthcoming_empty_bottle'] == 1 ? '符合' : ($fixed_detection['forthcoming_empty_bottle'] == 2 ? '不符合' : '不适用');
            $data[$k]['battery_low'] = $fixed_detection['battery_low'] == 1 ? '符合' : ($fixed_detection['battery_low'] == 2 ? '不符合' : '不适用');
            $data[$k]['flow_error'] = $fixed_detection['flow_error'] == 1 ? '符合' : ($fixed_detection['flow_error'] == 2 ? '不符合' : '不适用');
            $data[$k]['improper_installation'] = $fixed_detection['improper_installation'] == 1 ? '符合' : ($fixed_detection['improper_installation'] == 2 ? '不符合' : '不适用');
            $data[$k]['bubble_alarm'] = $fixed_detection['bubble_alarm'] == 1 ? '符合' : ($fixed_detection['bubble_alarm'] == 2 ? '不符合' : '不适用');
            $data[$k]['power_line_disconnect'] = $fixed_detection['power_line_disconnect'] == 1 ? '符合' : ($fixed_detection['power_line_disconnect'] == 2 ? '不符合' : '不适用');
            $data[$k]['open_door_alarm'] = $fixed_detection['open_door_alarm'] == 1 ? '符合' : ($fixed_detection['open_door_alarm'] == 2 ? '不符合' : '不适用');

            $heartresult = ($preset_detection['flow_result'] == 1) ? '符合' : '不符合';
            $heartresult .= '(最大允差：' . $tolerance['flow'] . ')';
            $data[$k]['flow']['result'] = $heartresult;
            $data[$k]['flow']['setting'] = $start_preset['flow'];
            $data[$k]['flow']['testing'] = $preset_detection['flow'];
            array_unshift($data[$k]['flow']['setting'], '设定值:');
            array_unshift($data[$k]['flow']['testing'], '测量值:');

            $energesisresult = ($preset_detection['block_result'] == 1) ? '符合' : '不符合';
            $energesisresult .= '(最大允差：' . $tolerance['block'] . ')';
            $data[$k]['block']['result'] = $energesisresult;
            $data[$k]['block']['setting'] = $start_preset['block'];
            $data[$k]['block']['testing'] = $preset_detection['block'];
            array_unshift($data[$k]['block']['setting'], '设定值:');
            array_unshift($data[$k]['block']['testing'], '测量值:');
        }
        return $data;
    }

    /**
     * Notes: 写入输液装置数据到工作表
     * @param $cellName
     * @param $chuchanyi_data
     * @param $objPHPExcel
     * @return mixed
     */
    public function write_shuye_data($cellName, $shuye_data, $objPHPExcel)
    {
        $xlsCell = array(
            '序号',
            '质控计划名称', '质控计划编号', '周期执行', '周期(月)', '期次', '预计执行日期', '实际完成日期', '计划执行人',
            '设备名称', '设备编码', '所属科室', '规格/型号',
            '外观功能', '流量检测', '', '', '阻塞报警检测', '', '', '报警系统检测', '堵塞', '即将空瓶', '电池电量不足', '流速错误', '输液管路安装不妥', '气泡报警', '电源线脱开', '开门报警', '检测结果'
        );
        $showName = array(
            'xuhao' => '序号',

            'plan_name' => '质控计划名称',
            'plan_num' => '质控计划编号',
            'is_cycle' => '周期执行',
            'cycle' => '周期(月)',
            'period' => '期次',
            'do_date' => '预计执行日期',
            'complete_date' => '实际完成日期',
            'username' => '计划执行人',

            'assets' => '设备名称',
            'assnum' => '设备编码',
            'department' => '所属科室',
            'model' => '规格/型号',

            'exterior' => '外观功能',
            'flow' => '流量检测',
            'block' => '阻塞报警检测',

            'blocking' => '堵塞',
            'forthcoming_empty_bottle' => '即将空瓶',
            'battery_low' => '电池电量不足',
            'flow_error' => '流速错误',
            'improper_installation' => '输液管路安装不妥',
            'bubble_alarm' => '气泡报警',
            'power_line_disconnect' => '电源线脱开',
            'open_door_alarm' => '开门报警',
            'total_result' => '检测结果'
        );
        $cellNum = count($xlsCell) - 1;
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        //$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        //获取表格表头数据
        $cell = [];
        foreach ($showName as $k => $v) {
            $cell[] = $k;
        }
        foreach ($shuye_data as $k => $v) {
            $j = 0;
            foreach ($cell as $key => $val) {
                if ($val == 'flow') {
                    //流量检测记录
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 3) . ':' . $cellName[$j + 2] . ($k * 3 + 3));
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 3 + 3))->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 3 + 4))->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 3 + 5))->setRowHeight(20);
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 3) . ':' . $cellName[$j + 2] . ($k * 3 + 3 + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    //写入结果
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 3), $v[$val]['result']);
                    if (strpos($v[$val]['result'], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 3) . ':' . $cellName[$j + 2] . ($k * 3 + 3))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val]['result'], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 3) . ':' . $cellName[$j + 2] . ($k * 3 + 3))->getFont()->getColor()->setARGB('FF0000');
                    }
                    //写入设定值和测量值
                    foreach ($v[$val]['setting'] as $ks => $vs) {
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 3 + 1), $v[$val]['setting'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 3 + 2), $v[$val]['testing'][$ks]);
                    }
                    $j = $j + 2;
                } elseif ($val == 'block') {
                    //阻塞报警检测记录
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 3) . ':' . $cellName[$j + 2] . ($k * 3 + 3));
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 3) . ':' . $cellName[$j + 2] . ($k * 3 + 3 + 2))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 3), $v[$val]['result']);
                    if (strpos($v[$val]['result'], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 3) . ':' . $cellName[$j + 2] . ($k * 3 + 3))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val]['result'], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 3) . ':' . $cellName[$j + 2] . ($k * 3 + 3))->getFont()->getColor()->setARGB('FF0000');
                    }
                    //写入设定值和测量值
                    foreach ($v[$val]['setting'] as $ks => $vs) {
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 3 + 1), $v[$val]['setting'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 3 + 3 + 2), $v[$val]['testing'][$ks]);
                    }
                    $j = $j + 2;
                } else {
                    // 合并
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 3 + 3) . ':' . $cellName[$j] . ($k * 3 + 5));
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 3))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 3 + 3), $v[$val]);
                    if (strpos($v[$val], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 3))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 3))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val], '不合格') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 3 + 3))->getFont()->getColor()->setARGB('FF0000');
                    }
                }
                $j++;
            }
        }
        //exit;
        //设置单元格边框
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'allborders' => array(                               //allborders  表示全部线框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
                    'color' => array('argb' => '000000'),            //设置border颜色
                ),
            ),
        );
        $lrow = count($shuye_data) * 3 + 2;
        $last = $cellName[$cellNum - 1] . $lrow;
        $objPHPExcel->getActiveSheet()->getStyle("A1:" . $last)->applyFromArray($styleThinBlackBorderOutline);

        $pic_name = ['shuye_result', 'shuye_abnormal_flow_2', 'shuye_abnormal_block_2', 'shuye_abnormal_other_2'];
        //设置图片路径,只能是本地图片
        foreach ($pic_name as $k => $v) {
            //导入图片到excel
            //实例化插入图片类
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setPath('./Public/uploads/qualities/excel_pic/' . session('userid') . $v . '.png');
            //设置图片要插入的单元格
            if (($k + 1) % 2 == 0) {
                $cn = 'I';
                if ($k - 3 < 0) {
                    $row = $lrow + 3;
                } elseif ($k - 3 == 0) {
                    $row = $lrow + 3 + ($k - 2) * 16;
                } else {
                    $row = $lrow + 3 + ($k - 3) * 16;
                }
            } else {
                $cn = 'B';
                if ($k - 2 < 0) {
                    $row = $lrow + 3;
                } elseif ($k - 2 == 0) {
                    $row = $lrow + 3 + ($k - 1) * 16;
                } else {
                    $row = $lrow + 3 + ($k - 2) * 16;
                }
            }
            $objDrawing->setCoordinates($cn . $row);
            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        }
        return $objPHPExcel;
    }

    /**
     * Notes: 格式化输液装置数据
     * @param $data
     * @param $assInfo
     * @param $departname
     * @return mixed
     */
    public function format_data_huxiji($data, $assInfo, $departname)
    {
        foreach ($data as $k => $v) {
            $data[$k]['xuhao'] = $k + 1;
            $data[$k]['exterior'] = $v['exterior'] == 1 ? '符合' : '不符合(' . $v['exterior_explain'] . ')';
            $data[$k]['is_cycle'] = $v['is_cycle'] == 0 ? '/' : '是';
            $data[$k]['cycle'] = $v['cycle'] ? $v['cycle'] : '/';
            $data[$k]['period'] = $v['period'] ? '第 ' . $v['period'] . ' 期' : '/';
            $data[$k]['complete_date'] = $v['date'];
            $data[$k]['assets'] = $assInfo[$v['assid']]['assets'];
            $data[$k]['assnum'] = $assInfo[$v['assid']]['assnum'];
            $data[$k]['model'] = $assInfo[$v['assid']]['model'];
            $data[$k]['total_result'] = $v['result'] == 1 ? '合格' : ($v['result'] == 2 ? '不合格' : '未填写');
            $data[$k]['department'] = $departname[$assInfo[$v['assid']]['departid']]['department'];
            $start_preset = json_decode($v['start_preset'], true);
            $preset_detection = json_decode($v['preset_detection'], true);
            $tolerance = json_decode($v['tolerance'], true);
            $fixed_detection = json_decode($v['fixed_detection'], true);

            $data[$k]['capacity_precut_mode'] = $fixed_detection['capacity_precut_mode'] == 1 ? '符合' : ($fixed_detection['capacity_precut_mode'] == 2 ? '不符合' : '不适用');
            $data[$k]['traffic_trigger'] = $fixed_detection['traffic_trigger'] == 1 ? '符合' : ($fixed_detection['traffic_trigger'] == 2 ? '不符合' : '不适用');
            $data[$k]['pressure_precut_mode'] = $fixed_detection['pressure_precut_mode'] == 1 ? '符合' : ($fixed_detection['pressure_precut_mode'] == 2 ? '不符合' : '不适用');
            $data[$k]['pressure_tigger'] = $fixed_detection['pressure_tigger'] == 1 ? '符合' : ($fixed_detection['pressure_tigger'] == 2 ? '不符合' : '不适用');

            $data[$k]['power_supply_alarm'] = $fixed_detection['power_supply_alarm'] == 1 ? '符合' : ($fixed_detection['power_supply_alarm'] == 2 ? '不符合' : '不适用');
            $data[$k]['oxygen_concentration_bound'] = $fixed_detection['oxygen_concentration_bound'] == 1 ? '符合' : ($fixed_detection['oxygen_concentration_bound'] == 2 ? '不符合' : '不适用');
            $data[$k]['gas_supply_alarm'] = $fixed_detection['gas_supply_alarm'] == 1 ? '符合' : ($fixed_detection['gas_supply_alarm'] == 2 ? '不符合' : '不适用');
            $data[$k]['apnea_alarm'] = $fixed_detection['apnea_alarm'] == 1 ? '符合' : ($fixed_detection['apnea_alarm'] == 2 ? '不符合' : '不适用');
            $data[$k]['AWP_alarm'] = $fixed_detection['AWP_alarm'] == 1 ? '符合' : ($fixed_detection['AWP_alarm'] == 2 ? '不符合' : '不适用');
            $data[$k]['loop_overvoltage_pretection'] = $fixed_detection['loop_overvoltage_pretection'] == 1 ? '符合' : ($fixed_detection['loop_overvoltage_pretection'] == 2 ? '不符合' : '不适用');
            $data[$k]['minute_ventilation_bound'] = $fixed_detection['minute_ventilation_bound'] == 1 ? '符合' : ($fixed_detection['minute_ventilation_bound'] == 2 ? '不符合' : '不适用');
            $data[$k]['press_key'] = $fixed_detection['press_key'] == 1 ? '符合' : ($fixed_detection['press_key'] == 2 ? '不符合' : '不适用');

            $humidityresult = ($preset_detection['humidity_result'] == 1) ? '符合' : '不符合';
            $humidityresult .= '(最大允差：' . $tolerance['humidity'] . ')';
            $data[$k]['humidity']['result'] = $humidityresult;
            $data[$k]['humidity']['setting'] = $start_preset['humidity'];
            $data[$k]['humidity']['testing'] = $preset_detection['humidity'];
            $data[$k]['humidity']['tolerance'] = $preset_detection['humidity_tolerance'];
            $data[$k]['humidity']['value'] = $preset_detection['humidity_value'];
            $data[$k]['humidity']['value_tolerance'] = $preset_detection['humidity_value_tolerance'];
            $data[$k]['humidity']['max_title'][] = '最大输出误差';
            $data[$k]['humidity']['max_title'][] = '最大示值误差';
            $data[$k]['humidity']['max_value'][] = $preset_detection['humidity_max_output'];
            $data[$k]['humidity']['max_value'][] = $preset_detection['humidity_max_value'];
            array_unshift($data[$k]['humidity']['setting'], '设定值:');
            array_unshift($data[$k]['humidity']['testing'], '测量值:');
            array_unshift($data[$k]['humidity']['tolerance'], '误差:');
            array_unshift($data[$k]['humidity']['value'], '示值:');
            array_unshift($data[$k]['humidity']['value_tolerance'], '示值误差:');


            $aerationresult = ($preset_detection['aeration_result'] == 1) ? '符合' : '不符合';
            $aerationresult .= '(最大允差：' . $tolerance['aeration'] . ')';
            $data[$k]['aeration']['result'] = $aerationresult;
            $data[$k]['aeration']['setting'] = $start_preset['aeration'];
            $data[$k]['aeration']['testing'] = $preset_detection['aeration'];
            $data[$k]['aeration']['tolerance'] = $preset_detection['aeration_tolerance'];
            $data[$k]['aeration']['value'] = $preset_detection['aeration_value'];
            $data[$k]['aeration']['value_tolerance'] = $preset_detection['aeration_value_tolerance'];
            $data[$k]['aeration']['max_title'][] = '最大输出误差';
            $data[$k]['aeration']['max_title'][] = '最大示值误差';
            $data[$k]['aeration']['max_value'][] = $preset_detection['aeration_max_output'];
            $data[$k]['aeration']['max_value'][] = $preset_detection['aeration_max_value'];
            array_unshift($data[$k]['aeration']['setting'], '设定值:');
            array_unshift($data[$k]['aeration']['testing'], '测量值:');
            array_unshift($data[$k]['aeration']['tolerance'], '误差:');
            array_unshift($data[$k]['aeration']['value'], '示值:');
            array_unshift($data[$k]['aeration']['value_tolerance'], '示值误差:');


            $IOIresult = ($preset_detection['IOI_result'] == 1) ? '符合' : '不符合';
            $IOIresult .= '(最大允差：' . $tolerance['IOI'] . ')';
            $data[$k]['IOI']['result'] = $IOIresult;
            $data[$k]['IOI']['setting'] = $start_preset['IOI'];
            $data[$k]['IOI']['testing'] = $preset_detection['IOI'];
            $data[$k]['IOI']['tolerance'] = $preset_detection['IOI_tolerance'];
            $data[$k]['IOI']['value'] = $preset_detection['IOI_value'];
            $data[$k]['IOI']['value_tolerance'] = $preset_detection['IOI_value_tolerance'];
            $data[$k]['IOI']['max_title'][] = '最大输出误差';
            $data[$k]['IOI']['max_title'][] = '最大示值误差';
            $data[$k]['IOI']['max_value'][] = $preset_detection['IOI_max_output'];
            $data[$k]['IOI']['max_value'][] = $preset_detection['IOI_max_value'];
            array_unshift($data[$k]['IOI']['setting'], '设定值:');
            array_unshift($data[$k]['IOI']['testing'], '测量值:');
            array_unshift($data[$k]['IOI']['tolerance'], '误差:');
            array_unshift($data[$k]['IOI']['value'], '示值:');
            array_unshift($data[$k]['IOI']['value_tolerance'], '示值误差:');

            $IPAPresult = ($preset_detection['IPAP_result'] == 1) ? '符合' : '不符合';
            $IPAPresult .= '(最大允差：' . $tolerance['IPAP'] . ')';
            $data[$k]['IPAP']['result'] = $IPAPresult;
            $data[$k]['IPAP']['setting'] = $start_preset['IPAP'];
            $data[$k]['IPAP']['testing'] = $preset_detection['IPAP'];
            $data[$k]['IPAP']['tolerance'] = $preset_detection['IPAP_tolerance'];
            $data[$k]['IPAP']['value'] = $preset_detection['IPAP_value'];
            $data[$k]['IPAP']['value_tolerance'] = $preset_detection['IPAP_value_tolerance'];
            $data[$k]['IPAP']['max_title'][] = '最大输出误差';
            $data[$k]['IPAP']['max_title'][] = '最大示值误差';
            $data[$k]['IPAP']['max_value'][] = $preset_detection['IPAP_max_output'];
            $data[$k]['IPAP']['max_value'][] = $preset_detection['IPAP_max_value'];
            array_unshift($data[$k]['IPAP']['setting'], '设定值:');
            array_unshift($data[$k]['IPAP']['testing'], '测量值:');
            array_unshift($data[$k]['IPAP']['tolerance'], '误差:');
            array_unshift($data[$k]['IPAP']['value'], '示值:');
            array_unshift($data[$k]['IPAP']['value_tolerance'], '示值误差:');

            $PEEPresult = ($preset_detection['PEEP_result'] == 1) ? '符合' : '不符合';
            $PEEPresult .= '(最大允差：' . $tolerance['PEEP'] . ')';
            $data[$k]['PEEP']['result'] = $PEEPresult;
            $data[$k]['PEEP']['setting'] = $start_preset['PEEP'];
            $data[$k]['PEEP']['testing'] = $preset_detection['PEEP'];
            $data[$k]['PEEP']['tolerance'] = $preset_detection['PEEP_tolerance'];
            $data[$k]['PEEP']['value'] = $preset_detection['PEEP_value'];
            $data[$k]['PEEP']['value_tolerance'] = $preset_detection['PEEP_value_tolerance'];
            $data[$k]['PEEP']['max_title'][] = '最大输出误差';
            $data[$k]['PEEP']['max_title'][] = '最大示值误差';
            $data[$k]['PEEP']['max_value'][] = $preset_detection['PEEP_max_output'];
            $data[$k]['PEEP']['max_value'][] = $preset_detection['PEEP_max_value'];
            array_unshift($data[$k]['PEEP']['setting'], '设定值:');
            array_unshift($data[$k]['PEEP']['testing'], '测量值:');
            array_unshift($data[$k]['PEEP']['tolerance'], '误差:');
            array_unshift($data[$k]['PEEP']['value'], '示值:');
            array_unshift($data[$k]['PEEP']['value_tolerance'], '示值误差:');
        }
        return $data;
    }
    /**
     * Notes: 格式化通用电气数据
     * @param $data
     * @param $assInfo
     * @param $departname
     * @return mixed
     */
    public function format_data_tongyongdianqi($data, $assInfo, $departname)
    {
        foreach ($data as $k => $v) {
            $data[$k]['xuhao'] = $k + 1;
            $data[$k]['exterior'] = $v['exterior'] == 1 ? '符合' : '不符合(' . $v['exterior_explain'] . ')';
            $data[$k]['is_cycle'] = $v['is_cycle'] == 0 ? '/' : '是';
            $data[$k]['cycle'] = $v['cycle'] ? $v['cycle'] : '/';
            $data[$k]['period'] = $v['period'] ? '第 ' . $v['period'] . ' 期' : '/';
            $data[$k]['complete_date'] = $v['date'];
            $data[$k]['assets'] = $assInfo[$v['assid']]['assets'];
            $data[$k]['assnum'] = $assInfo[$v['assid']]['assnum'];
            $data[$k]['model'] = $assInfo[$v['assid']]['model'];
            $data[$k]['department'] = $departname[$assInfo[$v['assid']]['departid']]['department'];

            $start_preset = json_decode($v['start_preset'], true);
            $preset_detection = json_decode($v['preset_detection'], true);
            $tolerance = json_decode($v['tolerance'], true);
            $fixed_detection = json_decode($v['fixed_detection'], true);

            $data[$k]['protection'] = $preset_detection['protection'][0];
            $data[$k]['insulation'] = $preset_detection['insulation'][0];
            $data[$k]['earthleakagecurrent'] = $preset_detection['earthleakagecurrent'][0];
            $data[$k]['Case_normal'] = $preset_detection['Case_normal'][0];
            $data[$k]['Case_abnormal'] = $preset_detection['Case_abnormal'][0];
            $data[$k]['patient_normal'] = $preset_detection['patient_normal'][0];
            $data[$k]['patient_abnormal'] = $preset_detection['patient_abnormal'][0];
            $data[$k]['aid_normal'] = $preset_detection['aid_normal'][0];
            $data[$k]['aid_abnormal'] = $preset_detection['aid_abnormal'][0];
            $data[$k]['App_types'] = $fixed_detection['App_types'] == 1 ? 'B型BF型' : ($fixed_detection['App_types'] == 2 ? 'CF型' : '不适用');
        }
        return $data;
    }

    /**
     * Notes: 写入呼吸机数据到工作表
     * @param $cellName
     * @param $huxiji_data
     * @param $objPHPExcel
     * @return mixed
     */
    public function write_huxiji_data($cellName, $huxiji_data, $objPHPExcel)
    {
        $xlsCell = array(
            '序号',
            '质控计划名称', '质控计划编号', '周期执行', '周期(月)', '期次', '预计执行日期', '实际完成日期', '计划执行人',

            '设备名称', '设备编码', '所属科室', '规格/型号',

            '外观功能',
            '潮气量(vcv模式)', '', '', '', '', '',
            '强制通气频率与呼吸比(vcv模式)', '', '', '', '', '',
            '吸入氧浓度FiO₂', '', '', '', '', '',
            '吸气压力水平 PCV或新生儿呼吸的PLV模式 f=20', '', '', '', '', '',
            '呼气末正压PEEP VCV模式 Vt=400ml f=20', '', '', '', '', '',

            '机械通气模式评价',
            '容量预制模式', '流量触发功能', '压力预制模式', '压力触发功能',

            '安全报警功能等检查',
            '电源报警', '氧浓度上/下限报警', '气源报警', '窒息报警', '气道压力上/下限报警', '病人回路过压保护功能', '分钟通气量上/下限报警', '按键功能检查(含键盘锁)', '检测结果'
        );
        $showName = array(
            'xuhao' => '序号',

            'plan_name' => '质控计划名称',
            'plan_num' => '质控计划编号',
            'is_cycle' => '周期执行',
            'cycle' => '周期(月)',
            'period' => '期次',
            'do_date' => '预计执行日期',
            'complete_date' => '实际完成日期',
            'username' => '计划执行人',

            'assets' => '设备名称',
            'assnum' => '设备编码',
            'department' => '所属科室',
            'model' => '规格/型号',

            'exterior' => '外观功能',
            'humidity' => '潮气量(vcv模式)',
            'aeration' => '强制通气频率与呼吸比(vcv模式)',
            'IOI' => '吸入氧浓度FiO₂',
            'IPAP' => '吸气压力水平 PCV或新生儿呼吸的PLV模式 f=20',
            'PEEP' => '呼气末正压PEEP VCV模式 Vt=400ml f=20',

            'capacity_precut_mode' => '容量预制模式',
            'traffic_trigger' => '流量触发功能',
            'pressure_precut_mode' => '压力预制模式',
            'pressure_tigger' => '压力触发功能',

            'power_supply_alarm' => '电源报警',
            'oxygen_concentration_bound' => '氧浓度上/下限报警',
            'gas_supply_alarm' => '气源报警',
            'apnea_alarm' => '窒息报警',
            'AWP_alarm' => '气道压力上/下限报警',
            'loop_overvoltage_pretection' => '病人回路过压保护功能',
            'minute_ventilation_bound' => '分钟通气量上/下限报警',
            'press_key' => '按键功能检查(含键盘锁)',
            'total_result' => '检测结果'
        );
        $cellNum = count($xlsCell) - 2;
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        //$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        //获取表格表头数据
        $cell = [];
        foreach ($showName as $k => $v) {
            $cell[] = $k;
        }
        foreach ($huxiji_data as $k => $v) {
            $j = 0;
            foreach ($cell as $key => $val) {
                if ($val == 'humidity' || $val == 'aeration' || $val == 'IOI' || $val == 'IPAP' || $val == 'PEEP') {
                    //潮气量记录
                    //$objPHPExcel->getActiveSheet()->mergeCells($cellName[$j].($k*5+3).':'.$cellName[$j+3].($k*5+3));
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 5 + 3))->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 5 + 4))->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 5 + 5))->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 5 + 6))->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k * 5 + 7))->setRowHeight(20);
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 5 + 3) . ':' . $cellName[$j + 5] . ($k * 5 + 3 + 4))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    //写入设定值和测量值等
                    foreach ($v[$val]['setting'] as $ks => $vs) {
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 5 + 3), $v[$val]['setting'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 5 + 3 + 1), $v[$val]['testing'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 5 + 3 + 2), $v[$val]['tolerance'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 5 + 3 + 3), $v[$val]['value'][$ks]);
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks] . ($k * 5 + 3 + 4), $v[$val]['value_tolerance'][$ks]);
                    }
                    foreach ($v[$val]['max_title'] as $ks => $vs) {
                        //最大输出误差 最大示值误差
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks + 4] . ($k * 5 + 3), $v[$val]['max_title'][$ks]);
                        //合并单元格
                        $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j + $ks + 4] . ($k * 5 + 3 + 1) . ':' . $cellName[$j + $ks + 4] . ($k * 5 + 3 + 4));
                        $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j + $ks + 4] . ($k * 5 + 3 + 1), $v[$val]['max_value'][$ks]);
                    }
                    $j = $j + 5;
                } else {
                    // 合并
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($k * 5 + 3) . ':' . $cellName[$j] . ($k * 5 + 7));
                    if ($k % 2 == 1) {
                        //设置背景颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 5 + 3))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DCE6F1');
                    }
                    $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j] . ($k * 5 + 3), $v[$val]);
                    if (strpos($v[$val], '不符合') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 5 + 3))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val], '不正常') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 5 + 3))->getFont()->getColor()->setARGB('FF0000');
                    }
                    if (strpos($v[$val], '不合格') !== false) {
                        //设置单元格内容字体颜色
                        $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] . ($k * 5 + 3))->getFont()->getColor()->setARGB('FF0000');
                    }
                }
                $j++;
            }
        }
        //exit;
        //设置单元格边框
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'allborders' => array(                               //allborders  表示全部线框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
                    'color' => array('argb' => '000000'),            //设置border颜色
                ),
            ),
        );
        $lrow = count($huxiji_data) * 5 + 2;
        $last = $cellName[$cellNum - 1] . $lrow;
        $objPHPExcel->getActiveSheet()->getStyle("A1:" . $last)->applyFromArray($styleThinBlackBorderOutline);

        $pic_name = ['huxiji_result', 'huxiji_abnormal_humidity_4', 'huxiji_abnormal_aeration_4', 'huxiji_abnormal_IOI_4', 'huxiji_abnormal_IPAP_4', 'huxiji_abnormal_PEEP_4', 'huxiji_abnormal_other_4'];
        //设置图片路径,只能是本地图片
        foreach ($pic_name as $k => $v) {
            //导入图片到excel
            //实例化插入图片类
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setPath('./Public/uploads/qualities/excel_pic/' . session('userid') . $v . '.png');
            //设置图片要插入的单元格
            if (($k + 1) % 2 == 0) {
                $cn = 'I';
                if ($k - 3 < 0) {
                    $row = $lrow + 3;
                } elseif ($k - 3 == 0) {
                    $row = $lrow + 3 + ($k - 2) * 16;
                } else {
                    $row = $lrow + 3 + ($k - 3) * 16;
                }
            } else {
                $cn = 'B';
                if ($k - 2 < 0) {
                    $row = $lrow + 3;
                } elseif ($k - 2 == 0) {
                    $row = $lrow + 3 + ($k - 1) * 16;
                } else {
                    $row = $lrow + 3 + ($k - 2) * 16;
                }
            }
            $objDrawing->setCoordinates($cn . $row);
            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        }
        return $objPHPExcel;
    }

    /**
     * Notes: 写入通用电气数据到工作表
     * @param $cellName
     * @param $huxiji_data
     * @param $objPHPExcel
     * @return mixed
     */
    public function write_tongyongdianqi_data($cellName, $tongyongdianqi_data, $objPHPExcel)
    {
        $xlsCell = array(
            '序号',
            '质控计划名称', '质控计划编号', '周期执行', '周期(月)', '期次', '预计执行日期', '实际完成日期', '计划执行人',

            '设备名称', '设备编码', '所属科室', '规格/型号',

            '外观功能','保护接地阻抗(mΩ)','绝缘阻抗(电源—外壳)(MΩ)','对地漏电流(正常状态)（μA）','外壳漏电流(正常状态)（μA）','外壳漏电流(地线断开)（μA）','设备类型','患者漏电流(正常状态)（μA）','患者漏电流(地线断开)（μA）','患者辅助漏电流(正常状态)（μA）','患者辅助漏电流(地线断开)（μA）'
        );
        $showName = array(
            'xuhao' => '序号',

            'plan_name' => '质控计划名称',
            'plan_num' => '质控计划编号',
            'is_cycle' => '周期执行',
            'cycle' => '周期(月)',
            'period' => '期次',
            'do_date' => '预计执行日期',
            'complete_date' => '实际完成日期',
            'username' => '计划执行人',

            'assets' => '设备名称',
            'assnum' => '设备编码',
            'department' => '所属科室',
            'model' => '规格/型号',

            'exterior' => '外观功能',
            'protection' => '保护接地阻抗(mΩ)',
            'insulation' => '绝缘阻抗(电源—外壳)(MΩ)',
            'earthleakagecurrent' => '对地漏电流(正常状态)（μA）',
            'Case_normal' => '外壳漏电流(正常状态)（μA）',
            'Case_abnormal' => '外壳漏电流(地线断开)（μA）',
            'App_types' => '设备类型',
            'patient_normal' => '患者漏电流(正常状态)（μA）',
            'patient_abnormal' => '患者漏电流(地线断开)（μA）',
            'aid_normal' => '患者辅助漏电流(正常状态)（μA）',
            'aid_abnormal' => '患者辅助漏电流(地线断开)（μA）',
        );
        $cellNum = count($xlsCell) - 2;
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        //$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        //获取表格表头数据
        $cell = [];
        foreach ($showName as $k => $v) {
            $cell[] = $k;
        }

        foreach ($tongyongdianqi_data as $k => $v) {
            $j = 0;
            foreach ($cell as $key => $val) {
                $objPHPExcel->getActiveSheet()->setCellvalue($cellName[$j].($k+2), $v[$val]);
                $j++;
            }
        }


        return $objPHPExcel;
    }
    /**
     * Notes: 生成监护仪表头
     * @param $cellName
     * @param $objPHPExcel
     */
    public function write_sheet_header_jianhuyi($cellName, $objPHPExcel)
    {
        $xlsCell = array(
            '序号',
            '质控计划名称', '质控计划编号', '周期执行', '周期(月)', '期次', '预计执行日期', '实际完成日期', '计划执行人',
            '设备名称', '设备编码', '所属科室', '规格/型号',
            '外观功能', '心率(次/min)', '', '', '', '', '', '呼吸率(次/min)', '', '', '', '', '', '无创血压(mmHg)', '', '', '', '', '', '血氧饱和度(%)', '', '', '', '', '', '声光报警', '报警限检查', '静音检查', '检测结果'
        );
        //单元格宽度设置
        $width = array(
            '序号' => '10',//字符数长度
            '质控计划名称' => '25',
            '质控计划编号' => '28',
            '周期执行' => '10',
            '期次' => '10',
            '预计执行日期' => '15',
            '实际完成日期' => '15',
            '计划执行人' => '15',

            '设备名称' => '20',
            '设备编码' => '20',
            '所属科室' => '20',
            '规格/型号' => '20',

            '外观功能' => '40',
            '声光报警' => '20',
            '报警限检查' => '20',
            '静音检查' => '20',
            '检测结果' => '20'
        );
        $cellNum = count($xlsCell);
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        for ($i = 0; $i < $cellNum; $i++) {
            //设置单元格行高
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);
            $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
            if ($width[$xlsCell[$i]]) {
                //设置单元格宽度
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$xlsCell[$i]]);
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(14);
            }
            //设置单元格第一行字体大小
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->setName('宋体')->setSize(12)->setBold(true);
            //设置单元格第一行背景颜色
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
            // 设置垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //左右居中对齐
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //合并
            $objPHPExcel->getActiveSheet()->mergeCells('O1:T1');
            $objPHPExcel->getActiveSheet()->mergeCells('U1:Z1');
            $objPHPExcel->getActiveSheet()->mergeCells('AA1:AF1');
            $objPHPExcel->getActiveSheet()->mergeCells('AG1:AL1');
            //写入表头数据
            $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
        }
        return $objPHPExcel;
    }

    /**
     * Notes: 生成输液装置表头
     * @param $cellName
     * @param $objPHPExcel
     */
    public function write_sheet_header_shuyezhuangzhi($cellName, $objPHPExcel)
    {
        $xlsCell = array(
            '序号',
            '质控计划名称', '质控计划编号', '周期执行', '周期(月)', '期次', '预计执行日期', '实际完成日期', '计划执行人',
            '设备名称', '设备编码', '所属科室', '规格/型号',
            '外观功能', '流量检测', '', '', '阻塞报警检测', '', '', '报警系统检测', '堵塞', '即将空瓶', '电池电量不足', '流速错误', '输液管路安装不妥', '气泡报警', '电源线脱开', '开门报警', '检测结果'
        );
        //单元格宽度设置
        $width = array(
            '序号' => '10',//字符数长度
            '质控计划名称' => '25',
            '质控计划编号' => '28',
            '周期执行' => '10',
            '期次' => '10',
            '预计执行日期' => '15',
            '实际完成日期' => '15',
            '计划执行人' => '15',

            '设备名称' => '20',
            '设备编码' => '20',
            '所属科室' => '20',
            '规格/型号' => '20',

            '外观功能' => '40',
            '检测结果' => '20'
        );
        $cellNum = count($xlsCell);
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        for ($i = 0; $i < $cellNum; $i++) {
            //设置单元格行高
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
            if ($width[$xlsCell[$i]]) {
                //设置单元格宽度
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$xlsCell[$i]]);
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(14);
            }
            //设置单元格第一行字体大小
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->setName('宋体')->setSize(12)->setBold(true);
            // 设置垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //左右居中对齐
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //合并
            if ($cellName[$i] == 'O') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i + 2] . '2');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1' . ':' . $cellName[$i + 2] . '2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 1])->setWidth(14);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 2])->setWidth(14);
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
                $i = $i + 2;
            } elseif ($cellName[$i] == 'R') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i + 2] . '2');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1' . ':' . $cellName[$i + 2] . '2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 1])->setWidth(14);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 2])->setWidth(14);
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
                $i = $i + 2;
            } elseif ($cellName[$i] == 'U') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i + 7] . '1');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1' . ':' . $cellName[$i + 7] . '2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
                for ($j = 0; $j < 8; $j++) {
                    $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i + $j] . '2', $xlsCell[$i + $j + 1]);
                    $objPHPExcel->getActiveSheet()->getStyle($cellName[$i + $j] . '2')->getFont()->setName('宋体')->setSize(12)->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + $j])->setWidth(18);
                }
                $i = $i + 8;
            } elseif ($cellName[$i] == 'AD') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i - 1] . '1' . ':' . $cellName[$i - 1] . '2');
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i - 1] . '1')->getFont()->setName('宋体')->setSize(12)->setBold(true);
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i - 1])->setWidth(14);
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i - 1] . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i - 1] . '1', $xlsCell[$i]);
            } else {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i] . '2');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
            }
        }
        return $objPHPExcel;
    }
    /**
     * Notes: 生成通用电气表头
     * @param $cellName
     * @param $objPHPExcel
     * @return mixed
     */
    public function write_sheet_header_tongyongdianqi($cellName, $objPHPExcel)
    {
        $xlsCell = array(
            '序号',
            '质控计划名称', '质控计划编号', '周期执行', '周期(月)', '期次', '预计执行日期', '实际完成日期', '计划执行人',
            '设备名称', '设备编码', '所属科室', '规格/型号',
            '外观功能','保护接地阻抗(mΩ)','绝缘阻抗(电源—外壳)(MΩ)','对地漏电流(正常状态)（μA）','外壳漏电流(正常状态)（μA）','外壳漏电流(地线断开)（μA）','设备类型','患者漏电流(正常状态)（μA）','患者漏电流(地线断开)（μA）','患者辅助漏电流(正常状态)（μA）','患者辅助漏电流(地线断开)（μA）'
        );
        //单元格宽度设置
        $width = array(
            '序号' => '10',//字符数长度
            '质控计划名称' => '25',
            '质控计划编号' => '28',
            '周期执行' => '10',
            '期次' => '10',
            '预计执行日期' => '15',
            '实际完成日期' => '15',
            '计划执行人' => '15',

            '设备名称' => '20',
            '设备编码' => '20',
            '所属科室' => '20',
            '规格/型号' => '20',

            '保护接地阻抗(mΩ)' => '40',
            '绝缘阻抗(电源—外壳)(MΩ)' => '40',
            '对地漏电流(正常状态)（μA）' => '40',
            '外壳漏电流(正常状态)（μA）' => '40',
            '外壳漏电流(地线断开)（μA）' => '40',
            '设备类型' => '40',
            '患者漏电流(正常状态)（μA）' => '40',
            '患者漏电流(地线断开)（μA）' => '40',
            '患者辅助漏电流(正常状态)（μA）' => '50',
            '患者辅助漏电流(地线断开)（μA）' => '50',
        );
        $cellNum = count($xlsCell);
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        for ($i = 0; $i < $cellNum; $i++) {
            //设置单元格行高
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);
            $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
            if ($width[$xlsCell[$i]]) {
                //设置单元格宽度
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$xlsCell[$i]]);
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(14);
            }
            //设置单元格第一行字体大小
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->setName('宋体')->setSize(12)->setBold(true);
            //设置单元格第一行背景颜色
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
            // 设置垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //左右居中对齐
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //写入表头数据
            $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
        }
        return $objPHPExcel;
    }

    /**
     * Notes: 生成除颤仪表头
     * @param $cellName
     * @param $objPHPExcel
     * @return mixed
     */
    public function write_sheet_header_chuchanyi($cellName, $objPHPExcel)
    {
        $xlsCell = array(
            '序号',
            '质控计划名称', '质控计划编号', '周期执行', '周期(月)', '期次', '预计执行日期', '实际完成日期', '计划执行人',
            '设备名称', '设备编码', '所属科室', '规格/型号',
            '外观功能', '心率(次/min)', '', '', '', '', '', '释放能量(J)', '', '', '', '', '', '充电时间(s)', '内部放电', '检测结果'
        );
        //单元格宽度设置
        $width = array(
            '序号' => '10',//字符数长度
            '质控计划名称' => '25',
            '质控计划编号' => '28',
            '周期执行' => '10',
            '期次' => '10',
            '预计执行日期' => '15',
            '实际完成日期' => '15',
            '计划执行人' => '15',

            '设备名称' => '20',
            '设备编码' => '20',
            '所属科室' => '20',
            '规格/型号' => '20',

            '外观功能' => '40',
            '充电时间(s)' => '20',
            '内部放电' => '20',
            '检测结果' => '20'
        );
        $cellNum = count($xlsCell);
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        for ($i = 0; $i < $cellNum; $i++) {
            //设置单元格行高
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);
            $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
            if ($width[$xlsCell[$i]]) {
                //设置单元格宽度
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$xlsCell[$i]]);
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(14);
            }
            //设置单元格第一行字体大小
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->setName('宋体')->setSize(12)->setBold(true);
            //设置单元格第一行背景颜色
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
            // 设置垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //左右居中对齐
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //合并
            $objPHPExcel->getActiveSheet()->mergeCells('O1:T1');
            $objPHPExcel->getActiveSheet()->mergeCells('U1:Z1');
            //写入表头数据
            $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
        }
        return $objPHPExcel;
    }

    /**
     * Notes: 生成呼吸机表头
     * @param $cellName
     * @param $objPHPExcel
     * @return mixed
     */
    public function write_sheet_header_huxiji($cellName, $objPHPExcel)
    {
        $xlsCell = array(
            '序号',
            '质控计划名称', '质控计划编号', '周期执行', '周期(月)', '期次', '预计执行日期', '实际完成日期', '计划执行人',

            '设备名称', '设备编码', '所属科室', '规格/型号',

            '外观功能',
            '潮气量(vcv模式)', '', '', '', '', '',
            '强制通气频率与呼吸比(vcv模式)', '', '', '', '', '',
            '吸入氧浓度FiO₂', '', '', '', '', '',
            '吸气压力水平 PCV或新生儿呼吸的PLV模式 f=20', '', '', '', '', '',
            '呼气末正压PEEP VCV模式 Vt=400ml f=20', '', '', '', '', '',
            '机械通气模式评价',
            '容量预制模式', '流量触发功能', '压力预制模式', '压力触发功能',
            '安全报警功能等检查',
            '电源报警', '氧浓度上/下限报警', '气源报警', '窒息报警', '气道压力上/下限报警', '病人回路过压保护功能', '分钟通气量上/下限报警', '按键功能检查(含键盘锁)', '检测结果'
        );
        //单元格宽度设置
        $width = array(
            '序号' => '10',//字符数长度
            '质控计划名称' => '25',
            '质控计划编号' => '28',
            '周期执行' => '10',
            '期次' => '10',
            '预计执行日期' => '15',
            '实际完成日期' => '15',
            '计划执行人' => '15',

            '设备名称' => '20',
            '设备编码' => '20',
            '所属科室' => '20',
            '规格/型号' => '20',

            '外观功能' => '40',
            '检测结果' => '20'
        );
        $cellNum = count($xlsCell) - 2;
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        for ($i = 0; $i < $cellNum; $i++) {
            //设置单元格行高
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
            if ($width[$xlsCell[$i]]) {
                //设置单元格宽度
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$xlsCell[$i]]);
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(15);
            }
            //设置单元格第一行字体大小
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->setName('宋体')->setSize(12)->setBold(true);
            // 设置垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //左右居中对齐
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //合并
            if ($cellName[$i] == 'O') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i + 5] . '2');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1' . ':' . $cellName[$i + 5] . '2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 1])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 2])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 3])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 4])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 5])->setWidth(15);
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
                $i = $i + 5;
            } elseif ($cellName[$i] == 'U') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i + 5] . '2');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1' . ':' . $cellName[$i + 5] . '2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 1])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 2])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 3])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 4])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 5])->setWidth(15);
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
                $i = $i + 5;
            } elseif ($cellName[$i] == 'AA') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i + 5] . '2');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1' . ':' . $cellName[$i + 5] . '2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 1])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 2])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 3])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 4])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 5])->setWidth(15);
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
                $i = $i + 5;
            } elseif ($cellName[$i] == 'AG') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i + 5] . '2');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1' . ':' . $cellName[$i + 5] . '2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 1])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 2])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 3])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 4])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 5])->setWidth(15);
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
                $i = $i + 5;
            } elseif ($cellName[$i] == 'AM') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i + 5] . '2');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1' . ':' . $cellName[$i + 5] . '2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 1])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 2])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 3])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 4])->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + 5])->setWidth(15);
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
                $i = $i + 5;
            } elseif ($cellName[$i] == 'AS') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i + 3] . '1');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1' . ':' . $cellName[$i + 3] . '2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
                for ($j = 0; $j < 4; $j++) {
                    $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i + $j] . '2', $xlsCell[$i + $j + 1]);
                    $objPHPExcel->getActiveSheet()->getStyle($cellName[$i + $j] . '2')->getFont()->setName('宋体')->setSize(12)->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + $j])->setWidth(18);
                }
                $i = $i + 3;
            } elseif ($cellName[$i] == 'AW') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i + 7] . '1');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1' . ':' . $cellName[$i + 7] . '2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i + 1]);
                for ($j = 0; $j < 8; $j++) {
                    $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i + $j] . '2', $xlsCell[$i + 1 + $j + 1]);
                    $objPHPExcel->getActiveSheet()->getStyle($cellName[$i + $j] . '2')->getFont()->setName('宋体')->setSize(12)->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i + $j])->setWidth(25);
                }
                $i = $i + 7;
            } elseif ($cellName[$i] == 'BE') {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i] . '2');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i + 2]);
            } else {
                $objPHPExcel->getActiveSheet()->mergeCells($cellName[$i] . '1' . ':' . $cellName[$i] . '2');
                //设置单元格第一行背景颜色
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
                //写入表头数据
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
            }
        }
        return $objPHPExcel;
    }

    /**
     * Notes: 获取质控结果分析数据
     * @param $type string 统计类型
     */
    public function getDataLists($type)
    {
        $result = [];
        switch ($type) {
            case 'template_nums':
                //模板使用次数统计
                $result = $this->get_template_nums();
                break;
            case 'department_result':
                //科室质控设备统计
                $result = $this->get_department_result();
                break;
            case 'abnormal_assets':
                //异常设备台次统计
                $result = $this->get_abnormal_assets();
                break;
        }
        return $result;
    }

    /**
     * Notes: 质控模板使用次数统计
     * @return mixed
     */
    public function get_template_nums()
    {
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-6 month"));
        $startDate = I('POST.startDate');
        $endDate = I('POST.endDate');
        //$hospital_id = I('POST.hospital_id');
        $hospital_id = session('current_hospitalid');
        if ($hospital_id) {
            $where['A.hospital_id'] = $hospital_id;
        } else {
            $where['A.hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['A.add_date'] = array('egt', $startDate);
        }
        if ($endDate && !$startDate) {
            $where['A.add_date'] = array('elt', $endDate);
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return array('status' => -1, 'msg' => '请选择合理的日期区间！');
            }
            $where['A.add_date'] = array(array('egt', $startDate), array('elt', $endDate));
        }
        if (!$startDate && !$endDate) {
            $where['A.add_date'] = array(array('egt', $start_date), array('elt', $end_date));
        }
        //设备名称
        $assets = I('post.assets');
        if ($assets) {
            $aswhere['assets'] = array('like', '%' . $assets . '%');
            $aswhere['hospital_id'] = $hospital_id;
            $res = $this->DB_get_all('assets_info', 'assid', $aswhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['A.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        //品牌
        $brands = I('post.brands');
        if ($brands) {
            $brwhere['brand'] = array('in', $brands);
            $brwhere['hospital_id'] = $hospital_id;
            $res = $this->DB_get_all('assets_info', 'assid', $brwhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['A.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        //科室
        $departids = I('post.departids');
        if ($departids) {
            $dewhere['departid'] = array('in', $departids);
            $dewhere['hospital_id'] = $hospital_id;
            $res = $this->DB_get_all('assets_info', 'assid', $dewhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['A.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        //分类
        $assetsCat = I('post.category');
        if ($assetsCat) {
            $catwhere['B.category'] = array('like', '%' . $assetsCat . '%');
            $catwhere['A.hospital_id'] = $hospital_id;
            $res = $this->DB_get_all_join('assets_info', 'A', 'A.assid', 'LEFT JOIN sb_category AS B ON A.catid = B.catid', $catwhere,'','','');
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['A.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        //供应商
        $suppname = I('post.suppname');
        if ($suppname) {
            $facwhere['supplier'] = array('like', '%' . $suppname . '%');
            $res = $this->DB_get_all('assets_factory', 'assid', $facwhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['A.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }

        $join = "LEFT JOIN sb_quality_details AS B ON A.qsid = B.qsid";
        $data = $this->DB_get_all_join('quality_result', 'A', 'A.qsid,A.qtemid,B.result', $join, $where, 'A.qsid','','');
        if (!$data) {
            $result['rows'] = [];
            $result['charData'] = [];
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //查询原有模板信息
        $templates = $this->DB_get_all('quality_templates', '*', array('1'));
        $tem_nums = [];
        foreach ($templates as $k => $v) {
            $tem_nums[$k]['qtemid'] = $v['qtemid'];
            $tem_nums[$k]['tem_name'] = $v['name'];
            $tem_nums[$k]['total_nums'] = 0;
            $tem_nums[$k]['pass_nums'] = 0;
            $tem_nums[$k]['notpass_nums'] = 0;
            $tem_nums[$k]['pass_rate'] = 0;
        }
        foreach ($tem_nums as $k => $v) {
            foreach ($data as $k1 => $v1) {
                if ($v['qtemid'] == $v1['qtemid']) {
                    $tem_nums[$k]['total_nums'] += 1;
                    if ($v1['result'] == 2) {
                        $tem_nums[$k]['notpass_nums'] += 1;
                    } else {
                        $tem_nums[$k]['pass_nums'] += 1;
                    }
                }
            }
        }
        foreach ($tem_nums as $k => $v) {
            if ($v['pass_nums'] + $v['notpass_nums'] == 0) {
                $tem_nums[$k]['pass_rate'] = '0%';
            } else {
                $tem_nums[$k]['pass_rate'] = round($v['pass_nums'] / ($v['pass_nums'] + $v['notpass_nums']) * 100, 2) . '%';
            }

        }
        //组织图表数据
        $chartData = [];
        $total_nums = 0;
        foreach ($tem_nums as $k => $v) {
            $total_nums += $v['total_nums'];
            $chartData['legend_data'][$k] = $v['tem_name'];
            $chartData['series_data'][$k]['value'] = $v['total_nums'];
            $chartData['series_data'][$k]['name'] = $v['tem_name'];
        }
        foreach ($tem_nums as $k => $v) {
            $chartData['series_data'][$k]['precent'] = round($v['total_nums'] / $total_nums * 100, 2) . '%';
            $chartData['series_data'][$k]['is_show'] = false;
        }
        $max = getArrayMax($chartData['series_data'], 'value');
        foreach ($chartData['series_data'] as $k => $v) {
            if ($v['value'] == $max) {
                $chartData['series_data'][$k]['is_show'] = true;
                break;
            }
        }
        $result["code"] = 200;
        $result['rows'] = $tem_nums;
        $result['charData'] = $chartData;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 科室质控设备统计
     * @return mixed
     */
    public function get_department_result()
    {
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-6 month"));

        $startDate = I('POST.startDate');
        $endDate = I('POST.endDate');
        $hospital_id = I('POST.hospital_id');
        if ($hospital_id) {
            $where['A.hospital_id'] = $hospital_id;
        } else {
            $where['A.hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['A.add_date'] = array('egt', $startDate);
        }
        if ($endDate && !$startDate) {
            $where['A.add_date'] = array('elt', $endDate);
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return array('status' => -1, 'msg' => '请选择合理的日期区间！');
            }
            $where['A.add_date'] = array(array('egt', $startDate), array('elt', $endDate));
        }
        if (!$startDate && !$endDate) {
            $where['A.add_date'] = array(array('egt', $start_date), array('elt', $end_date));
        }
        //设备名称
        $assets = I('post.assets');
        if ($assets) {
            $where['B.assets'] = array('like', $assets);
        }
        //品牌
        $brands = I('post.brands');
        if ($brands) {
            $where['B.brand'] = array('in', $brands);
        }
        //科室
        $departids = I('post.departids');
        if ($departids) {
            $where['B.departid'] = array('in', $departids);
        }
        //分类
        $assetsCat = I('post.category');
        if ($assetsCat) {
            $catwhere['category'] = array('like', '%' . $assetsCat . '%');
            $res = $this->DB_get_all('category', 'catid', $catwhere, '', 'catid asc', '');
            if ($res) {
                $catids = '';
                foreach ($res as $k => $v) {
                    $catids .= $v['catid'] . ',';
                }
                $catids = trim($catids, ',');
                $where['B.catid'] = array('in', $catids);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        //供应商
        $suppname = I('post.suppname');
        if ($suppname) {
            $facwhere['supplier'] = array('like', '%' . $suppname . '%');
            $res = $this->DB_get_all('assets_factory', 'assid', $facwhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['B.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        $fields = "A.qsid,A.qtemid,A.assid,B.departid,C.result";
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid LEFT JOIN sb_quality_details AS C ON A.qsid = C.qsid";
        $data = $this->DB_get_all_join('quality_result', 'A', $fields, $join, $where, 'A.qsid','','');
        $departname = $return_data = $departids = $assids = array();
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k => $v) {
            if (!in_array($v['departid'], $departids)) {
                $departids[] = $v['departid'];
                $assids[$v['departid']][] = $v['assid'];
            } else {
                if (!in_array($v['assid'], $assids[$v['departid']])) {
                    $assids[$v['departid']][] = $v['assid'];
                }
            }
        }
        $total_nums = $jige_num = $k = 0;
        foreach ($assids as $key => $val) {
            $return_data[$k]['departid'] = $key;
            $return_data[$k]['department'] = $departname[$key]['department'];
            $return_data[$k]['assets_nums'] = count($val);
            $return_data[$k]['assets_rate'] = 0;
            $return_data[$k]['tem_1'] = 0;
            $return_data[$k]['tem_2'] = 0;
            $return_data[$k]['tem_3'] = 0;
            $return_data[$k]['tem_4'] = 0;
            $return_data[$k]['res_1'] = 0;
            $return_data[$k]['res_2'] = 0;
            $return_data[$k]['res_3'] = 0;
            $return_data[$k]['res_rate'] = 0;
            $return_data[$k]['depart_res_rate'] = 0;
            $total_nums += count($val);
            $k++;
        }
        foreach ($return_data as $k => $v) {
            $return_data[$k]['assets_rate'] = round($v['assets_nums'] / $total_nums * 100, 2) . '%';
            foreach ($data as $k1 => $v1) {
                if ($v1['departid'] == $v['departid']) {
                    $return_data[$k]['tem_' . $v1['qtemid']] += 1;
                    if ($v1['result'] == 1) {
                        $return_data[$k]['res_1'] += 1;
                        $jige_num += 1;
                    } elseif ($v1['result'] == 2) {
                        $return_data[$k]['res_2'] += 1;
                    } else {
                        $return_data[$k]['res_3'] += 1;
                    }
                }
            }
        }
        foreach ($return_data as $k => $v) {
            $res_count = $v['res_1'] + $v['res_2'] + $v['res_3'];
            $return_data[$k]['res_rate'] = round($v['res_1'] / $res_count * 100, 2) . '%';
            $return_data[$k]['depart_res_rate'] = round($v['res_1'] / $jige_num * 100, 2) . '%';
        }
        //组织图表数据
        $chartData = [];
        foreach ($return_data as $k => $v) {
            $chartData['legend_data'][$k] = $v['department'];
            $chartData['series_data'][$k]['value'] = $v['assets_nums'];
            $chartData['series_data'][$k]['name'] = $v['department'];
            $chartData['series_data'][$k]['precent'] = $v['assets_rate'];
            $chartData['series_data'][$k]['is_show'] = false;
        }
        $max = getArrayMax($chartData['series_data'], 'value');
        foreach ($chartData['series_data'] as $k => $v) {
            if ($v['value'] == $max) {
                $chartData['series_data'][$k]['is_show'] = true;
                break;
            }
        }
        $result["code"] = 200;
        $result['rows'] = $return_data;
        $result['charData'] = $chartData;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    public function get_abnormal_assets()
    {
        $qtemid = I('POST.qtemid');
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-6 month"));

        $startDate = I('POST.startDate');
        $endDate = I('POST.endDate');
        //$hospital_id = I('POST.hospital_id');
        $hospital_id = session('current_hospitalid');
        if (!$qtemid) {
            return array('status' => -1, 'msg' => '参数错误！');
        }
        $where['A.qtemid'] = $qtemid;
        if ($hospital_id) {
            $where['A.hospital_id'] = $hospital_id;
        } else {
            $where['A.hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['A.add_date'] = array('egt', $startDate);
        }
        if ($endDate && !$startDate) {
            $where['A.add_date'] = array('elt', $endDate);
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return array('status' => -1, 'msg' => '请选择合理的日期区间！');
            }
            $where['A.add_date'] = array(array('egt', $startDate), array('elt', $endDate));
        }
        if (!$startDate && !$endDate) {
            $where['A.add_date'] = array(array('egt', $start_date), array('elt', $end_date));
        }
        //设备名称
        $assets = I('post.assets');
        if ($assets) {
            $aswhere['assets'] = array('like', '%' . $assets . '%');
            $aswhere['hospital_id'] = $hospital_id;
            $res = $this->DB_get_all('assets_info', 'assid', $aswhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['C.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        //品牌
        $brands = I('post.brands');
        if ($brands) {
            $brwhere['brand'] = array('in', $brands);
            $brwhere['hospital_id'] = $hospital_id;
            $res = $this->DB_get_all('assets_info', 'assid', $brwhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['C.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        //科室
        $departids = I('post.departids');
        if ($departids) {
            $dewhere['departid'] = array('in', $departids);
            $dewhere['hospital_id'] = $hospital_id;
            $res = $this->DB_get_all('assets_info', 'assid', $dewhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['C.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        //分类
        $assetsCat = I('post.category');
        if ($assetsCat) {
            $catwhere['B.category'] = array('like', '%' . $assetsCat . '%');
            $catwhere['A.hospital_id'] = $hospital_id;
            $res = $this->DB_get_all_join('assets_info', 'A', 'A.assid', 'LEFT JOIN sb_category AS B ON A.catid = B.catid', $catwhere,'','','');
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['C.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        //供应商
        $suppname = I('post.suppname');
        if ($suppname) {
            $facwhere['supplier'] = array('like', '%' . $suppname . '%');
            $res = $this->DB_get_all('assets_factory', 'assid', $facwhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['C.assid'] = array('in', $assid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                $result['charData'] = [];
                return $result;
            }
        }
        $join[0] = "LEFT JOIN sb_quality_details AS B ON A.qsid = B.qsid";
        $join[1] = "LEFT JOIN sb_assets_info AS C ON A.assid = C.assid";
        $join[2] = "LEFT JOIN sb_assets_factory AS D ON C.afid = D.afid";
        $data = $this->DB_get_all_join('quality_result', 'A', 'A.qsid,A.qtemid,A.assid,A.add_date,B.result,C.assets,C.assnum,C.catid,C.departid,C.model,C.afid,C.brand,D.ols_supid', $join, $where, 'A.qsid', 'C.departid asc','');
        if (!$data) {
            $result['rows'] = [];
            $result['charData'] = [];
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //查询厂商信息
        $facModel = M("assets_factory");
        $factorys = $facModel->where('ols_supid > 0')->getField('ols_supid,supplier');
        //查询计划信息
        $qsids = '';
        foreach ($data as $k => $v) {
            $qsids .= $v['qsid'] . ',';
        }
        $qsids = trim($qsids, ',');
        //查询固定非值明细项
        $quality_starts = M("quality_starts");
        $qsinfo = $quality_starts->where('qsid in (' . $qsids . ')')->getField('qsid,plan_name,start_date,is_cycle,cycle,period');
        foreach ($data as $k => $v) {
            $data[$k]['plan_name'] = $qsinfo[$v['qsid']]['plan_name'];
            $data[$k]['start_date'] = $qsinfo[$v['qsid']]['start_date'];
            $data[$k]['is_cycle'] = $qsinfo[$v['qsid']]['is_cycle'] == 1 ? '是' : '否';
            $data[$k]['cycle'] = $qsinfo[$v['qsid']]['cycle'] ? $qsinfo[$v['qsid']]['cycle'] : '/';
            $data[$k]['period'] = $qsinfo[$v['qsid']]['period'] ? '第 ' . $qsinfo[$v['qsid']]['period'] . ' 期' : '/';
        }
        //组织图表数据
        $chartData = $deprtids = [];
        $departname = $catname = $return_data = $departids = $assids = array();
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        foreach ($data as $k => $v) {
            $data[$k]['brand'] = $v['brand'] ? $v['brand'] : '/';
            $data[$k]['model'] = $v['model'] ? $v['model'] : '/';
            $data[$k]['supplier'] = $factorys[$v['ols_supid']] ? $factorys[$v['ols_supid']] : '/';
            $data[$k]['department'] = $departname[$v['departid']]['department'];
            $data[$k]['category'] = $catname[$v['catid']]['category'];
            $data[$k]['result'] = $v['result'] == 1 ? '合格' : ($v['result'] == 2 ? '不合格' : '未填写');
        }
        foreach ($data as $k => $v) {
            if ($v['result'] == '不合格') {
                $departids[$v['departid']][] = $v['assid'];
            }
        }
        $i = 0;
        $total_nums = 0;
        foreach ($departids as $k => $v) {
            $total_nums += count($v);
            $chartData['legend_data'][$i] = $departname[$k]['department'];
            $chartData['series_data'][$i]['value'] = count($v);
            $chartData['series_data'][$i]['name'] = $departname[$k]['department'];
            $i++;
        }

        foreach ($chartData['series_data'] as $k => $v) {
            $chartData['series_data'][$k]['precent'] = round($v['value'] / $total_nums * 100, 2) . '%';
            $chartData['series_data'][$k]['is_show'] = false;
        }
        $max = getArrayMax($chartData['series_data'], 'value');
        foreach ($chartData['series_data'] as $k => $v) {
            if ($v['value'] == $max) {
                $chartData['series_data'][$k]['is_show'] = true;
                break;
            }
        }
        $result["code"] = 200;
        $result['rows'] = $data;
        $result['charData'] = $chartData;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }


    /**
     * Notes: 获取一定时期内质控模板的明细异常项统计数据
     */
    public function get_abnormal_detail()
    {
        $qtemid = I('POST.qtemid');

        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-6 month"));

        $startDate = I('POST.startDate');
        $endDate = I('POST.endDate');
        //$hospital_id = I('POST.hospital_id');
        $hospital_id = session('current_hospitalid');
        if (!$qtemid) {
            return array('status' => -1, 'msg' => '参数错误！');
        }
        $where['A.qtemid'] = $qtemid;
        if ($hospital_id) {
            $where['A.hospital_id'] = $hospital_id;
        } else {
            $where['A.hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['A.add_date'] = array('egt', $startDate);
        }
        if ($endDate && !$startDate) {
            $where['A.add_date'] = array('elt', $endDate);
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return array('status' => -1, 'msg' => '请选择合理的日期区间！');
            }
            $where['A.add_date'] = array(array('egt', $startDate), array('elt', $endDate));
        }
        if (!$startDate && !$endDate) {
            $where['A.add_date'] = array(array('egt', $start_date), array('elt', $end_date));
        }
        //设备名称
        $assets = I('post.assets');
        if ($assets) {
            $aswhere['assets'] = array('like', '%' . $assets . '%');
            $aswhere['hospital_id'] = $hospital_id;
            $res = $this->DB_get_all('assets_info', 'assid', $aswhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['A.assid'] = array('in', $assid);
            } else {
                $result['resultChartData'] = [];
                $result['detailData'] = [];
                $result['msg'] = '暂无相关数据';
                $result['status'] = 1;
                $result["qtemid"] = $qtemid;
                return $result;
            }
        }
        //品牌
        $brands = I('post.brands');
        if ($brands) {
            $brwhere['brand'] = array('in', $brands);
            $brwhere['hospital_id'] = $hospital_id;
            $res = $this->DB_get_all('assets_info', 'assid', $brwhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['A.assid'] = array('in', $assid);
            } else {
                $result['resultChartData'] = [];
                $result['detailData'] = [];
                $result['msg'] = '暂无相关数据';
                $result['status'] = 1;
                $result["qtemid"] = $qtemid;
                return $result;
            }
        }
        //科室
        $departids = I('post.departids');
        if ($departids) {
            $dewhere['departid'] = array('in', $departids);
            $dewhere['hospital_id'] = $hospital_id;
            $res = $this->DB_get_all('assets_info', 'assid', $dewhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['A.assid'] = array('in', $assid);
            } else {
                $result['resultChartData'] = [];
                $result['detailData'] = [];
                $result['msg'] = '暂无相关数据';
                $result['status'] = 1;
                $result["qtemid"] = $qtemid;
                return $result;
            }
        }
        //分类
        $assetsCat = I('post.category');
        if ($assetsCat) {
            $catwhere['B.category'] = array('like', '%' . $assetsCat . '%');
            $catwhere['A.hospital_id'] = $hospital_id;
            $res = $this->DB_get_all_join('assets_info', 'A', 'A.assid', 'LEFT JOIN sb_category AS B ON A.catid = B.catid', $catwhere,'','','');
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['A.assid'] = array('in', $assid);
            } else {
                $result['resultChartData'] = [];
                $result['detailData'] = [];
                $result['msg'] = '暂无相关数据';
                $result['status'] = 1;
                $result["qtemid"] = $qtemid;
                return $result;
            }
        }
        //供应商
        $suppname = I('post.suppname');
        if ($suppname) {
            $facwhere['supplier'] = array('like', '%' . $suppname . '%');
            $res = $this->DB_get_all('assets_factory', 'assid', $facwhere);
            if ($res) {
                $assid = '';
                foreach ($res as $k => $v) {
                    $assid .= $v['assid'] . ',';
                }
                $assid = trim($assid, ',');
                $where['A.assid'] = array('in', $assid);
            } else {
                $result['resultChartData'] = [];
                $result['detailData'] = [];
                $result['msg'] = '暂无相关数据';
                $result['status'] = 1;
                $result["qtemid"] = $qtemid;
                return $result;
            }
        }

        //统计总结果及格率
        $join[0] = "LEFT JOIN sb_quality_details AS B ON A.qsid = B.qsid";
        $total_result = $this->DB_get_all_join('quality_result', 'A', 'A.qsid,A.qtemid,A.assid,A.add_date,B.result', $join, $where, 'A.qsid','','');
        if (!$total_result) {
            $result['resultChartData'] = [];
            $result['detailData'] = [];
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
            $result["qtemid"] = $qtemid;
            return $result;
        }
        //组织检测结果图表数据
        $resultChartData = $this->format_final_result_data($total_result);
        $join[0] = "LEFT JOIN sb_quality_result_detail AS B ON A.resultid = B.resultid";
        $detail_data = $this->DB_get_all_join('quality_result', 'A', 'A.resultid,A.qsid,A.qtemid,A.detection_Ename,A.detection_name,A.detection_name,A.unit,A.add_date,A.is_conformity AS res,B.id,B.scope_value,B.measured_value,B.tolerance,B.is_conformity', $join, $where,'','','');;
        //组织明细项图表数据
        $detailData['table_data'] = $this->format_table_data($detail_data, $qtemid);
        $detailData['chart_data'] = $this->format_chart_notpass_data($detailData['table_data']);
        $result["status"] = 1;
        $result["qtemid"] = $qtemid;
        $result['detailData'] = $detailData;
        $result['resultChartData'] = $resultChartData;
        return $result;
    }

    public function format_final_result_data($total_result)
    {
        $resultChartData = [];
        $total_result_data['pass'] = 0;
        $total_result_data['notpass'] = 0;
        foreach ($total_result as $k => $v) {
            if ($v['result'] == 2) {
                $total_result_data['notpass'] += 1;
            } else {
                $total_result_data['pass'] += 1;
            }
        }
        $total_nums = 0;
        foreach ($total_result_data as $k => $v) {
            $total_nums += $v;
            if ($k == 'pass') {
                $resultChartData['legend_data'][0] = '合格';
                $resultChartData['series_data'][0]['value'] = $v;
                $resultChartData['series_data'][0]['name'] = '合格';
            } else {
                $resultChartData['legend_data'][1] = '不合格';
                $resultChartData['series_data'][1]['value'] = $v;
                $resultChartData['series_data'][1]['name'] = '不合格';
            }
        }
        foreach ($resultChartData['series_data'] as $k => $v) {
            $resultChartData['series_data'][$k]['precent'] = round($v['value'] / $total_nums * 100, 2) . '%';
            $resultChartData['series_data'][$k]['is_show'] = false;
        }
        $max = getArrayMax($resultChartData['series_data'], 'value');
        foreach ($resultChartData['series_data'] as $k => $v) {
            if ($v['value'] == $max) {
                $resultChartData['series_data'][$k]['is_show'] = true;
                break;
            }
        }
        return $resultChartData;
    }

    public function format_table_data($detail_data, $qtemid)
    {
        $data = $detection_Ename = [];
        foreach ($detail_data as $k => $v) {
            if ($v['id'] && !in_array($v['detection_Ename'], $detection_Ename)) {
                $detection_Ename[] = $v['detection_Ename'];
            }
        }
        //查询其他固定非值
        $fixed_details = $this->DB_get_all('quality_template_fixed_details', 'fixed_detection_name,fixed_detection_Ename', array('qtemid' => $qtemid));
        foreach ($fixed_details as $k => $v) {
            $data['other'][$v['fixed_detection_name']]['pass'] = 0;
            $data['other'][$v['fixed_detection_name']]['not_pass'] = 0;
            $data['other'][$v['fixed_detection_name']]['not_use'] = 0;
            $data['other'][$v['fixed_detection_name']]['pass_rate'] = 0;
            $data['other'][$v['fixed_detection_name']]['not_pass_rate'] = 0;
        }
        $data['other']['外观功能']['pass'] = 0;
        $data['other']['外观功能']['not_pass'] = 0;
        $data['other']['外观功能']['not_use'] = 0;
        $data['other']['外观功能']['pass_rate'] = 0;
        $data['other']['外观功能']['not_pass_rate'] = 0;
        $setting = $this->DB_get_all('quality_preset', '*', array('detection_Ename' => array('in', $detection_Ename)));
        $sys_set = [];
        foreach ($setting as $k => $v) {
            foreach (json_decode($v['value'], true) as $k1 => $v1) {
                $sys_set[$v['detection_Ename']][] = $v1;
                $data[$v['detection_Ename']][$v1]['pass'] = 0;
                $data[$v['detection_Ename']][$v1]['not_pass'] = 0;
                $data[$v['detection_Ename']][$v1]['not_use'] = 0;
                $data[$v['detection_Ename']][$v1]['pass_rate'] = 0;
                $data[$v['detection_Ename']][$v1]['not_pass_rate'] = 0;
            }
        }
        foreach ($detail_data as $k => $v) {
            if ($v['id']) {
                if (in_array($v['scope_value'], $sys_set[$v['detection_Ename']])) {
                    switch ($v['is_conformity']) {
                        case 1:
                            $data[$v['detection_Ename']][$v['scope_value']]['pass'] += 1;
                            break;
                        case 2:
                            $data[$v['detection_Ename']][$v['scope_value']]['not_pass'] += 1;
                            break;
                        case 3:
                            $data[$v['detection_Ename']][$v['scope_value']]['not_use'] += 1;
                            break;
                        default:
                            $data[$v['detection_Ename']][$v['scope_value']]['pass'] += 1;
                            break;
                    }
                }
            } else {
                switch ($v['res']) {
                    case 1:
                        $data['other'][$v['detection_name']]['pass'] += 1;
                        break;
                    case 2:
                        $data['other'][$v['detection_name']]['not_pass'] += 1;
                        break;
                    case 3:
                        $data['other'][$v['detection_name']]['not_use'] += 1;
                        break;
                }
            }
        }
        foreach ($data as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $data[$k][$k1]['pass_rate'] = round($v1['pass'] / ($v1['pass'] + $v1['not_pass'] + $v1['not_use']) * 100, 2) . '%';
                $data[$k][$k1]['not_pass_rate'] = round($v1['not_pass'] / ($v1['pass'] + $v1['not_pass'] + $v1['not_use']) * 100, 2) . '%';
            }
        }
        return $data;
    }

    public function format_chart_data($table_data)
    {
        $chart_data = [];
        foreach ($table_data as $k => $v) {
            $i = 0;
            foreach ($v as $k1 => $v1) {
                $chart_data[$k][$i]['title'] = '合格率';
                $chart_data[$k][$i]['value'] = $v1['pass'];
                $chart_data[$k][$i]['precent'] = round($v1['pass'] / ($v1['pass'] + $v1['not_pass'] + $v1['not_use']) * 100, 2) . '%';
                $chart_data[$k][$i]['name'] = $k1;
                $i++;
            }
        }
        return $chart_data;
    }

    public function format_chart_notpass_data($table_data)
    {
        $chart_data = [];
        //var_dump($table_data);exit;
        foreach ($table_data as $k => $v) {
            $chart_data[$k] = [];
        }
        foreach ($table_data as $k => $v) {
            $i = 0;
            foreach ($v as $k1 => $v1) {
                if ($v1['not_pass'] > 0) {
                    $chart_data[$k][$i]['title'] = '不合格率';
                    $chart_data[$k][$i]['value'] = $v1['not_pass'];
                    $chart_data[$k][$i]['precent'] = round($v1['not_pass'] / ($v1['pass'] + $v1['not_pass'] + $v1['not_use']) * 100, 2) . '%';
                    $chart_data[$k][$i]['name'] = $k1;
                    $i++;
                }
            }
        }
        return $chart_data;
    }

    public function get_pic_data()
    {
        $qsid = I('POST.qsid');
        $qsid = trim($qsid, ',');
        $qsid = explode(',', $qsid);
        $where['A.qsid'] = array('in', $qsid);
        //查询要导出的数据是否已完成
        //$qsinfo = $this->DB_get_one('quality_details','group_concat(qsid) as qsids',array('qsid'=>array('in',$qsid)));

        //统计总结果及格率
        $join[0] = "LEFT JOIN sb_quality_details AS B ON A.qsid = B.qsid";
        $total_result = $this->DB_get_all_join('quality_result', 'A', 'A.qsid,A.qtemid,A.assid,A.add_date,B.result', $join, $where, 'A.qsid','','');
        if (!$total_result) {
            $result['msg'] = '查询不到相关数据，请稍后再试！';
            $result['code'] = -1;
            return $result;
        }
        if (count($total_result) != count($qsid)) {
            $result['msg'] = '请选择已完成或已结束的数据进行导出！';
            $result['status'] = -1;
            return $result;
        }
        $data = $resultChartData = $detail = $detailData = $qtemids = $title = [];
        foreach ($total_result as $k => $v) {
            switch ($v['qtemid']) {
                case 1:
                    $qtemids['jianhuyi'] = 1;
                    $data['jianhuyi'][] = $v;
                    break;
                case 2:
                    $qtemids['shuye'] = 2;
                    $data['shuye'][] = $v;
                    break;
                case 3:
                    $qtemids['chuchanyi'] = 3;
                    $data['chuchanyi'][] = $v;
                    break;
                case 4:
                    $qtemids['huxiji'] = 4;
                    $data['huxiji'][] = $v;
                    break;
            }
        }
        //组织检测结果图表数据
        foreach ($data as $k => $v) {
            $resultChartData[$k] = $this->format_final_result_data($v);
        }

        //组织明细项图表数据
        /***********************************/
        $join[0] = "LEFT JOIN sb_quality_result_detail AS B ON A.resultid = B.resultid";
        $detail_data = $this->DB_get_all_join('quality_result', 'A', 'A.resultid,A.qsid,A.qtemid,A.detection_Ename,A.detection_name,A.detection_name,A.unit,A.add_date,A.is_conformity AS res,B.id,B.scope_value,B.measured_value,B.tolerance,B.is_conformity', $join, $where,'','','');;
        foreach ($detail_data as $k => $v) {
            switch ($v['qtemid']) {
                case 1:
                    $detail['jianhuyi'][] = $v;
                    break;
                case 2:
                    $detail['shuye'][] = $v;
                    break;
                case 3:
                    $detail['chuchanyi'][] = $v;
                    break;
                case 4:
                    $detail['huxiji'][] = $v;
                    break;
            }
        }
        foreach ($detail as $k => $v) {
            //组织明细项图表数据
            $table_data = $this->format_table_data($v, $v[0]['qtemid']);
            $detailData[$k] = $this->format_chart_data($table_data);
        }
        //查询标题
        $preset = $this->DB_get_all('quality_preset', '*', array('1'));
        foreach ($preset as $k => $v) {
            if ($v['unit']) {
                $title[$v['detection_Ename']] = $v['detection_name'] . '(' . $v['unit'] . ')';
            } else {
                $title[$v['detection_Ename']] = $v['detection_name'];
            }
        }
        $title['other'] = '其他固定非值';
        $result["status"] = 1;
        $result['resultChartData'] = $resultChartData;
        $result['detailChartData'] = $detailData;
        $result['qtemids'] = $qtemids;
        $result['title'] = $title;
        return $result;
    }

    public function save_excel_pic()
    {
        unset($_POST['type']);
        foreach ($_POST as $k => $v) {
            //匹配出图片的格式
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $v, $result)) {
                //图片格式
                $type = $result[2];
                $filePath = "./Public/uploads/qualities/excel_pic/";
                if (!file_exists($filePath)) {
                    //检查是否有该文件夹，如果没有就创建，并给予最高权限
                    mkdir($filePath, 0777, true);
                }
                $fileName = session('userid') . $k . '.' . $type;
                $file = $filePath . $fileName;
                file_put_contents($file, base64_decode(str_replace($result[1], '', $v)));
            }
        }
        return array('status' => 1);
    }

    public function write_sheet_header_qualitySurvey($cellName, $objPHPExcel)
    {
        $xlsCell = array(
            '序号', '科室名称', '模板', '模板使用次数', '模板使用占比', '合格次数', '不合格次数', '合格率');
        //单元格宽度设置
        $width = array(
            '序号' => '10',//字符数长度
            '科室名称' => '20',
            '模板' => '20',
            '模板使用次数' => '20',
            '模板使用占比' => '20',
            '合格次数' => '15',
            '不合格次数' => '15',
            '合格率' => '15',
        );
        $cellNum = count($xlsCell);
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        for ($i = 0; $i < $cellNum; $i++) {
            //设置单元格行高
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);
            $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
            if ($width[$xlsCell[$i]]) {
                //设置单元格宽度
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$xlsCell[$i]]);
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(14);
            }
            //设置单元格第一行字体大小
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->setName('宋体')->setSize(12)->setBold(true);
            //设置单元格第一行背景颜色
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('F2F2F2');
            // 设置垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //左右居中对齐
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //合并
            $objPHPExcel->getActiveSheet()->mergeCells('O1:T1');
            $objPHPExcel->getActiveSheet()->mergeCells('U1:Z1');
            $objPHPExcel->getActiveSheet()->mergeCells('AA1:AF1');
            $objPHPExcel->getActiveSheet()->mergeCells('AG1:AL1');
            //写入表头数据
            $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
        }
        return $objPHPExcel;
    }

    public function write_qualitySurvey_data($cellName, $data, $objPHPExcel)
    {
        $xlsCell = array(
            '序号', '科室名称', '模板', '模板使用次数', '使用率', '合格次数', '不合格次数', '合格率');
        $showName = array(
            'xuhao' => '序号',
            'department' => '科室名称',
            'name' => '模板',
            'useNums' => '模板使用次数',
            'useRatio' => '使用率',
            'successNums' => '合格次数',
            'failNums' => '不合格次数',
            'successRatio' => '合格率',
        );
        $cellNum = count($xlsCell);

        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        $objActSheet = $objPHPExcel->getActiveSheet();
        //获取表格表头数据
        $cell = [];
        foreach ($showName as $k => $v) {
            $cell[] = $k;
        }
        $j = 2;
        foreach ($data as $k => $v) {
            foreach ($cell as $key => $val) {
                if ($val != 'sum') {
                    if ($key == 1) {
                        if ($data[$k]['sum'] != 0) {
                            $objActSheet->mergeCells($cellName[$key] . $j . ':' . $cellName[$key] . ($j + $v['sum'] - 1));
                        }
                        $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
                    } else {
                        $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
                    }


                }
            }
            $objActSheet->getRowDimension($j)->setRowHeight(18);
            $j++;
        }


        //使用次数图
        $surveyMain = I('POST.surveyMain');
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $surveyMain, $result)) {
            //图片格式
            $type = $result[2];
            if (!in_array($type, array('png', 'jpeg'))) {
                die(json_encode(array('status' => -1, 'msg' => '图片格式错误！')));
            }
            $filePath = "./Public/uploads/report/";
            if (!file_exists($filePath)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($filePath, 0777, true);
            }
            $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
            $file = $filePath . $fileName;
            if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $surveyMain)))) {
                die(json_encode(array('status' => -1, 'msg' => '图表保存出错，请重试！')));
            }
            $image = new \Think\Image();
            $image->open($file);
            $imageInfo[0]['width'] = $image->width(); // 返回图片的宽度
            $imageInfo[0]['height'] = $image->height(); // 返回图片的高度
            $imageInfo[0]['type'] = $image->type(); // 返回图片的类型
            $imageInfo[0]['url'] = $file; // 服务器图片地址
            $otherInfo['titleFontSize'] = 28;
            $otherInfo['titleRowHeight'] = 60;
            $otherInfo['imageWidth'] = $image->width();//图片缩放比例
            $otherInfo['imageHeight'] = $image->height();//图片缩放比例
        } else {
            die(json_encode(array('status' => -1, 'msg' => '图片格式错误！')));
        }

        $fiedMain = I('POST.fiedMain');
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $fiedMain, $result)) {
            //图片格式
            $type = $result[2];
            if (!in_array($type, array('png', 'jpeg'))) {
                die(json_encode(array('status' => -1, 'msg' => '图片格式错误！')));
            }
            $filePath = "./Public/uploads/report/";
            if (!file_exists($filePath)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($filePath, 0777, true);
            }
            $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
            $file = $filePath . $fileName;
            if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $fiedMain)))) {
                die(json_encode(array('status' => -1, 'msg' => '图表保存出错，请重试！')));
            }
            $image = new \Think\Image();
            $image->open($file);
            $imageInfo[1]['width'] = $image->width(); // 返回图片的宽度
            $imageInfo[1]['height'] = $image->height(); // 返回图片的高度
            $imageInfo[1]['type'] = $image->type(); // 返回图片的类型
            $imageInfo[1]['url'] = $file; // 服务器图片地址
            $otherInfo['titleFontSize'] = 28;
            $otherInfo['titleRowHeight'] = 60;
            $otherInfo['imageWidth'] = $image->width();//图片缩放比例
            $otherInfo['imageHeight'] = $image->height();//图片缩放比例
        } else {
            die(json_encode(array('status' => -1, 'msg' => '图片格式错误！')));
        }

        //设置图片路径,只能是本地图片
        $j += 3;
        foreach ($imageInfo as &$v) {
            //导入图片到excel
            //实例化插入图片类
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setPath($v['url']);
            //设置图片要插入的单元格

            $objDrawing->setCoordinates('A' . $j);
            //设置图片高度
            $objDrawing->setWidth($otherInfo['imageWidth']);
            $objDrawing->setHeight($otherInfo['imageHeight']);
            /*设置图片所在单元格的格式*/
            $objDrawing->setOffsetX(10);
            $objDrawing->setOffsetY(10);
            $objDrawing->setRotation(0);
            $objDrawing->getShadow()->setVisible(true);
            $objDrawing->getShadow()->setDirection(50);
            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
            $j += 17;
        }

        $resultData['objPHPExcel'] = $objPHPExcel;
        $resultData['img'] = $imageInfo;
//        unlink($imageInfo['url']);
        return $resultData;
    }

    public function write_planResult_data($cellName, $data, $objPHPExcel)
    {
        $xlsCell = array(
            '序号', '质控计划名称', '科室名称', '设备名称', '设备编号', '合格次数', '不合格次数', '合格率', '符合项', '不符合项');
        $showName = array(
            'xuhao' => '序号',
            'plan_name' => '质控计划名称',
            'department' => '科室名称',
            'assets' => '设备名称',
            'assnum' => '设备编号',
            'successNums' => '合格次数',
            'failNums' => '不合格次数',
            'successRatio' => '合格率',
            'accord' => '符合项',
            'n_accord_ncolor' => '不符合项',
        );

        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        $objActSheet = $objPHPExcel->getActiveSheet();
        //获取表格表头数据
        $cell = [];
        foreach ($showName as $k => $v) {
            $cell[] = $k;
        }
        $j = 2;
        foreach ($data as $k => $v) {
            foreach ($cell as $key => $val) {
                if ($val != 'sum') {
                    if ($key == 1) {
                        if ($data[$k]['sum'] != 0) {
                            $objActSheet->mergeCells($cellName[$key] . $j . ':' . $cellName[$key] . ($j + $v['sum'] - 1));
                        }
                        $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
                    } else {
                        if ($val == 'n_accord_ncolor') {
                            //左对齐
                            $objActSheet->getStyle($cellName[$key] . $j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
                            //自动换行
                            $objActSheet->getStyle($cellName[$key] . $j)->getAlignment()->setWrapText(true);
                        }
                        $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
                    }
                }
            }
            $objActSheet->getRowDimension($j)->setRowHeight(18);
            $j++;
        }


        //使用次数图
        $planMain = I('POST.planMain');
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $planMain, $result)) {
            //图片格式
            $type = $result[2];
            if (!in_array($type, array('png', 'jpeg'))) {
                die(json_encode(array('status' => -1, 'msg' => '图片格式错误！')));
            }
            $filePath = "./Public/uploads/report/";
            if (!file_exists($filePath)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($filePath, 0777, true);
            }
            $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
            $file = $filePath . $fileName;
            if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $planMain)))) {
                die(json_encode(array('status' => -1, 'msg' => '图表保存出错，请重试！')));
            }
            $image = new \Think\Image();
            $image->open($file);
            $imageInfo['width'] = $image->width(); // 返回图片的宽度
            $imageInfo['height'] = $image->height(); // 返回图片的高度
            $imageInfo['type'] = $image->type(); // 返回图片的类型
            $imageInfo['url'] = $file; // 服务器图片地址
            $otherInfo['titleFontSize'] = 28;
            $otherInfo['titleRowHeight'] = 60;
            $otherInfo['imageWidth'] = $image->width();//图片缩放比例
            $otherInfo['imageHeight'] = $image->height();//图片缩放比例
        } else {
            die(json_encode(array('status' => -1, 'msg' => '图片格式错误！')));
        }


        //导入图片到excel
        //实例化插入图片类
        $objDrawing = new \PHPExcel_Worksheet_Drawing();
        $objDrawing->setPath($imageInfo['url']);
        //设置图片要插入的单元格

        $objDrawing->setCoordinates('A' . ($j + 3));
        //设置图片高度
        $objDrawing->setWidth($otherInfo['imageWidth']);
        $objDrawing->setHeight($otherInfo['imageHeight']);
        /*设置图片所在单元格的格式*/
        $objDrawing->setOffsetX(10);
        $objDrawing->setOffsetY(10);
        $objDrawing->setRotation(0);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(50);
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());


        $resultData['objPHPExcel'] = $objPHPExcel;
        $resultData['img'] = $imageInfo;
//        unlink($imageInfo['url']);
        return $resultData;
    }

    public function write_sheet_header_planResult($cellName, $objPHPExcel)
    {
        $xlsCell = array(
            '序号', '质控计划名称', '科室名称', '设备名称', '设备编号', '合格次数', '不合格次数', '合格率', '符合项', '不符合项');
        //单元格宽度设置
        $width = array(
            '序号' => '10',//字符数长度
            '质控计划名称' => '25',
            '科室名称' => '20',
            '设备名称' => '20',
            '设备编号' => '18',
            '合格次数' => '15',
            '不合格次数' => '15',
            '合格率' => '15',
            '符合项' => '15',
            '不符合项' => '120',
        );
        $cellNum = count($xlsCell);
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        for ($i = 0; $i < $cellNum; $i++) {
            //设置单元格行高
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);
            $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
            if ($width[$xlsCell[$i]]) {
                //设置单元格宽度
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$xlsCell[$i]]);
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(14);
            }
            //设置单元格第一行字体大小
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->setName('宋体')->setSize(12)->setBold(true);
            //设置单元格第一行背景颜色
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('F2F2F2');
            // 设置垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //左右居中对齐
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //合并
            $objPHPExcel->getActiveSheet()->mergeCells('O1:T1');
            $objPHPExcel->getActiveSheet()->mergeCells('U1:Z1');
            $objPHPExcel->getActiveSheet()->mergeCells('AA1:AF1');
            $objPHPExcel->getActiveSheet()->mergeCells('AG1:AL1');
            //写入表头数据
            $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
        }
        return $objPHPExcel;
    }

    //获取默认有数据的科室 用于质控报表默认显示有数据的科室
    public function getShowDataDepart()
    {
        $where = [];
        $where['D.date'][] = ['EGT', date('Y', time()) . '-01-01'];
        $where['D.date'][] = ['ELT', date('Y', time()) . '-12-31'];
        $where[1][]['D.result'] = 1;
        $where[1][]['D.result'] = 2;
        $where[1]['_logic'] = 'OR';
        $fields = "Q.qsid";
        $join = 'LEFT JOIN sb_quality_starts as Q ON Q.qsid=D.qsid';
        $details = $this->DB_get_all_join('quality_details', 'D', $fields, $join, $where,'','','');
        $result = [];
        if ($details) {
            $qsid = [];
            foreach ($details as &$one) {
                $qsid[] = $one['qsid'];
            }
            $where = [];
            $where['S.qsid'] = ['IN', $qsid];
            $where['A.hospital_id'] = session('current_hospitalid');
            $where['A.departid'] = ['IN', session('departid')];
            $join = 'LEFT JOIN sb_assets_info as A ON A.assid=S.assid';
            $quality = $this->DB_get_all_join('quality_starts', 'S', 'A.departid', $join, $where, 'A.departid','','');
            if ($quality) {
                $result = [];
                foreach ($quality as &$one) {
                    $result[] = $one['departid'];
                }
            } else {
                $departid = explode(',', (session('departid')));
                $result[0] = $departid[0];
            }
        } else {
            $departid = explode(',', (session('departid')));
            $result[0] = $departid[0];
        }
        return $result;
    }

    public function feedbackQualityPlan()
    {
        $settingData = $this->checkSmsIsOpenTasks();
        /***************************短信 start***************************************/
        if ($settingData) {
            //有开启短信
            $ToolMod = new ToolController();
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $day = $baseSetting['qualities']['qualities_soon_expire_day']['value'];
            $qualities_soon_expire_day = 0;
            if ($day) {
                $qualities_soon_expire_day = getHandleTime(strtotime("+ $day day"));
            }
            //已开启 是周期类型的计划
            $where['is_start'] = ['EQ', C('OPEN_STATUS')];
            $where['is_cycle'] = ['EQ', C('YES_STATUS')];
            $quality = $this->DB_get_all('quality_starts', 'qsid,plan_name,plan_num,end_date,userid,is_cycle,hospital_id,start_date', $where);
            //反馈数据 初始化
            $feedback = [];
            if ($quality) {
                //开启短信
                foreach ($quality as &$one) {
                    if ($one['end_date'] <= $qualities_soon_expire_day && $one['is_cycle'] == C('YES_STATUS') && $one['end_date'] != null) {
                        $user = $this->DB_get_one('user', 'telephone', "userid='$one[userid]'");
                        if ($settingData['noticeDoQualityPlan']['status'] == C('OPEN_STATUS') && $user['telephone']) {
                            //质控提醒将要逾期
                            $sms = $this->formatSmsContent($settingData['noticeDoQualityPlan']['content'], $one);
                            $ToolMod->sendingSMS($user['telephone'], $sms, $this->MODULE, $one['qsid']);
                        }
                    }
                    //记录 待执行任务数量 按照医院分配
                    $feedback[$one['hospital_id']]['toBeDoneNum'] = isset($feedback[$one['hospital_id']]['toBeDoneNum']) ? $feedback[$one['hospital_id']]['toBeDoneNum'] + 1 : 1;
                }
            }

            $where = [];
            $where['date'] = ['EQ', getHandleTime(time())];
            $join = 'LEFT JOIN sb_quality_starts AS S ON S.qsid=D.qsid';
            $detailsData = $this->DB_get_all_join('quality_details', 'D', 'S.hospital_id', $join, $where,'','','');

            foreach ($detailsData as &$one) {
                //记录 今日完成任务数量 按照医院分配
                $feedback[$one['hospital_id']]['completeNum'] = isset($feedback[$one['hospital_id']]['completeNum']) ? $feedback[$one['hospital_id']]['completeNum'] + 1 : 1;
            }

            $hospital = $this->DB_get_all('hospital');
            $hospitalData = [];
            foreach ($hospital as &$hvalue) {
                $hospitalData[$hvalue['hospital_id']] = $hvalue['hospital_name'];
            }

            if ($feedback) {
                foreach ($feedback as $key => $value) {
                    $userData = $this->getUserTasks('feedbackQuality', $key);
                    if ($settingData['feedbackQuality']['status'] == C('OPEN_STATUS') && $userData) {
                        //质控提醒将要逾期
                        $data = [];
                        $data['completeNum'] = isset($value['completeNum']) ? $value['completeNum'] : 0;
                        $data['toBeDoneNum'] = isset($value['toBeDoneNum']) ? $value['toBeDoneNum'] : 0;
                        $data['hospital'] = $hospitalData[$key];
                        $sms = $this->formatSmsContent($settingData['feedbackQuality']['content'], $data);
                        $phone = $this->formatPhone($userData);
                        $ToolMod->sendingSMS($phone, $sms, $this->MODULE, 0);
                    }
                }
            }
        }
        /***************************短信 end***************************************/

        if(C('USE_FEISHU') === 1){
            $baseSetting = [];
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $day = $baseSetting['qualities']['qualities_soon_expire_day']['value'];
            $qualities_soon_expire_day = 0;
            if ($day) {
                $qualities_soon_expire_day = getHandleTime(strtotime("+ $day day"));
            }
            //已开启 是周期类型的计划
            $where['is_start'] = ['EQ', C('OPEN_STATUS')];
            $where['is_cycle'] = ['EQ', C('YES_STATUS')];
            $quality = $this->DB_get_all('quality_starts', 'qsid,plan_name,plan_num,end_date,userid,is_cycle,hospital_id,username', $where);
            //反馈数据 初始化
            $feedback = [];
            foreach ($quality as &$one) {
                if ($one['end_date'] <= $qualities_soon_expire_day && $one['is_cycle'] == C('YES_STATUS') && $one['end_date'] != null) {
                    $user = $this->DB_get_one('user', 'telephone,openid', "userid='$one[userid]'");
                    //==========================================飞书 START========================================
                    //要显示的字段区域
                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**计划名称：**'.$one['plan_name'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**计划类型：**质控计划';
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**计划状态：**即将逾期';
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**执行人：**'.$one['username'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false;//是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '计划即将逾期，请及时处理';
                    $feishu_fields[] = $fd;

                    $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                    $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                    $card_data['elements'][0]['tag'] = 'div';
                    $card_data['elements'][0]['fields'] = $feishu_fields;
                    $card_data['header']['template'] = 'red';
                    $card_data['header']['title']['content'] = '质控计划即将逾期提醒';
                    $card_data['header']['title']['tag'] = 'plain_text';

                    $this->send_feishu_card_msg($user['openid'],$card_data);
                    //==========================================飞书 END==========================================
                }
                //记录 待执行任务数量 按照医院分配
                $feedback[$one['hospital_id']]['toBeDoneNum'] = isset($feedback[$one['hospital_id']]['toBeDoneNum']) ? $feedback[$one['hospital_id']]['toBeDoneNum'] + 1 : 1;
            }
            $where = [];
            $where['date'] = ['EQ', getHandleTime(time())];
            $join = 'LEFT JOIN sb_quality_starts AS S ON S.qsid=D.qsid';
            $detailsData = $this->DB_get_all_join('quality_details', 'D', 'S.hospital_id', $join, $where,'','','');
            foreach ($detailsData as &$one) {
                //记录 今日完成任务数量 按照医院分配
                $feedback[$one['hospital_id']]['completeNum'] = isset($feedback[$one['hospital_id']]['completeNum']) ? $feedback[$one['hospital_id']]['completeNum'] + 1 : 1;
            }

            $hospital = $this->DB_get_all('hospital','',array('is_delete'=>0));
            $hospitalData = [];
            foreach ($hospital as &$hvalue) {
                $hospitalData[$hvalue['hospital_id']] = $hvalue['hospital_name'];
            }
            $already_send = [];
            foreach ($feedback as $key => $value) {
                $value['completeNum'] = $value['completeNum'] ? $value['completeNum'] : 0;
                $value['toBeDoneNum'] = $value['toBeDoneNum'] ? $value['toBeDoneNum'] : 0;
                $progress = '今日完成数量：'.$value['completeNum'].'，待执行数量：'.$value['toBeDoneNum'];
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**质控计划完成情况：**'.$progress;
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '请知悉并做好下一步工作安排';
                $feishu_fields[] = $fd;

                $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag'] = 'div';
                $card_data['elements'][0]['fields'] = $feishu_fields;
                $card_data['header']['template'] = 'red';
                $card_data['header']['title']['content'] = '质控计划每日反馈提醒';
                $card_data['header']['title']['tag'] = 'plain_text';

                $userData = $this->getUserTasks('feedbackQuality', $key);
                foreach ($userData as $k=>$v){
                    if($key == $v['job_hospitalid']){
                        if($v['openid'] && !in_array($v['openid'],$already_send)){
                            $this->send_feishu_card_msg($v['openid'],$card_data);
                            $already_send[] = $v['openid'];
                        }
                    }
                }
                //==========================================飞书 END==========================================
            }
        }else{
            /***************************微信 start***************************************/
            $moduleModel = new ModuleModel();
            $wx_status = $moduleModel->decide_wx_login();
            if ($wx_status) {
                $baseSetting = [];
                include APP_PATH . "Common/cache/basesetting.cache.php";
                $day = $baseSetting['qualities']['qualities_soon_expire_day']['value'];
                $qualities_soon_expire_day = 0;
                if ($day) {
                    $qualities_soon_expire_day = getHandleTime(strtotime("+ $day day"));
                }
                //已开启 是周期类型的计划
                $where['is_start'] = ['EQ', C('OPEN_STATUS')];
                $where['is_cycle'] = ['EQ', C('YES_STATUS')];
                $quality = $this->DB_get_all('quality_starts', 'qsid,plan_name,plan_num,end_date,userid,is_cycle,hospital_id,username', $where);
                //反馈数据 初始化
                $feedback = [];
                foreach ($quality as &$one) {
                    if ($one['end_date'] <= $qualities_soon_expire_day && $one['is_cycle'] == C('YES_STATUS') && $one['end_date'] != null) {
                        $user = $this->DB_get_one('user', 'telephone,openid', "userid='$one[userid]'");
                        if($user['openid']){
                            Weixin::instance()->sendMessage($user['openid'], '工单处理提醒', [
                                'thing10' => '质控计划',// 工单类型
                                'thing9'  => $one['plan_name'],// 工单名称
                                'time39'  => $one['start_date'],// 发起时间
                                'thing7'  => $one['username'],// 发起人员
                                'const56' => '',// 工单阶段
                            ]);
                        }
                    }
                    //记录 待执行任务数量 按照医院分配
                    $feedback[$one['hospital_id']]['toBeDoneNum'] = isset($feedback[$one['hospital_id']]['toBeDoneNum']) ? $feedback[$one['hospital_id']]['toBeDoneNum'] + 1 : 1;
                }
                $where = [];
                $where['date'] = ['EQ', getHandleTime(time())];
                $join = 'LEFT JOIN sb_quality_starts AS S ON S.qsid=D.qsid';
                $detailsData = $this->DB_get_all_join('quality_details', 'D', 'S.hospital_id', $join, $where,'','','');
                foreach ($detailsData as &$one) {
                    //记录 今日完成任务数量 按照医院分配
                    $feedback[$one['hospital_id']]['completeNum'] = isset($feedback[$one['hospital_id']]['completeNum']) ? $feedback[$one['hospital_id']]['completeNum'] + 1 : 1;
                }

                $hospital = $this->DB_get_all('hospital','',array('is_delete'=>0));
                $hospitalData = [];
                foreach ($hospital as &$hvalue) {
                    $hospitalData[$hvalue['hospital_id']] = $hvalue['hospital_name'];
                }
                $already_send = [];
                foreach ($feedback as $key => $value) {
                    $value['completeNum'] = $value['completeNum'] ? $value['completeNum'] : 0;
                    $value['toBeDoneNum'] = $value['toBeDoneNum'] ? $value['toBeDoneNum'] : 0;
                    $progress = '今日完成数量：'.$value['completeNum'].'，待执行数量：'.$value['toBeDoneNum'];
                    $templateId = C('WX_TEMPLATES')['GZJDTZ'];//计划进度通知
                    $redecturl = '';
                    $wxdata = array(
                        'first' => array('value' => urlencode("您好,收到一条质控计划每日反馈提醒！"), 'color' => "#FF0000"),
                        'keyword1' => array('value' => urlencode('每日质控计划完成情况反馈')),
                        'keyword2' => array('value' => urlencode($progress)),
                        'remark' => array('value' => urlencode('请知悉并做好下一步工作安排！')),
                    );
                    $userData = $this->getUserTasks('feedbackQuality', $key);
                    foreach ($userData as $k=>$v){
                        if($key == $v['job_hospitalid']){
                            if($v['openid'] && !in_array($v['openid'],$already_send)){
                                $this->sendMsgToOnUserByWechat($v['openid'], $templateId, $redecturl, $wxdata);
                                $already_send[] = $v['openid'];
                            }
                        }
                    }
                }
            }
            /***************************微信 end***************************************/
        }
    }

    //验证是否开启质控短信
    private function checkSmsIsOpenTasks()
    {
        $Data = $this->DB_get_all('sms_basesetting');
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
                if ($val['content'] == '' && $val['action'] == $this->MODULE) {
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
        return $settingData[$this->MODULE];
    }

    private function getUserTasks($action, $hospitalid)
    {
        $roleModel = new RoleModel();
        $where = [];
        $where['M.name'] = $action;
        $where['R.is_default'] = C('NO_STATUS');
        $where['R.status'] = C('OPEN_STATUS');
        $where['R.is_delete'] = C('NO_STATUS');
        $where['R.is_default'] = C('NO_STATUS');
        $where['R.hospital_id'] = $hospitalid;
        $join[0] = 'LEFT JOIN sb_role_menu AS RM ON RM.roleid=R.roleid';
        $join[1] = 'LEFT JOIN sb_menu AS M ON M.menuid=RM.menuid';
        $role = $roleModel->DB_get_one_join('role', 'R', 'GROUP_CONCAT(R.roleid)roleid', $join, $where);
        if ($role['roleid']) {
            $userWhere['UR.roleid'] = ['IN', $role['roleid']];
            $join = 'LEFT JOIN sb_user_role AS UR ON UR.userid=U.userid';
            $user = $roleModel->DB_get_all_join('user', 'U', 'U.userid,U.job_hospitalid,U.username,U.telephone,U.manager_hospitalid,U.openid', $join, $userWhere,'','','');
            $userData = [];
            foreach ($user as &$one) {
                $manager = explode(',', $one['manager_hospitalid']);
                if (in_array($hospitalid, $manager)) {
                    $userData[] = $one;
                }
            }
            return $userData;
        } else {
            return [];
        }
    }

    /**
     * @return mixed 质控项目设置获取检测依据
     */
    public function getBasisListData()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $total = $this->DB_get_count('quality_detection_basis');
        $basisInfo = $this->DB_get_all('quality_detection_basis', '', '', '', '', $offset . "," . $limit);
        $userInfo = $this->DB_get_all('user', 'userid,username', array('status' => 1, 'is_delete' => 0, 'job_hospitalid' => session('current_hospitalid')), '', 'userid asc', '');
        $userInfoArr = [];
        foreach ($userInfo as $v) {
            $userInfoArr[$v['userid']] = $v['username'];
        }
        foreach ($basisInfo as $k => $v) {
            $basisInfo[$k]['adduser'] = $userInfoArr[$v['adduserid']];
            $html = '<div class="layui-btn-group">';
            $html .= $this->returnButtonLink('查看', get_url(), 'layui-btn layui-btn-xs layui-btn-normal', '', 'lay-event = showBasis type="button"');
            $html .= $this->returnButtonLink('编辑', get_url(), 'layui-btn layui-btn-xs', '', 'lay-event = editBasis type="button"');
            $html .= $this->returnButtonLink('删除', get_url(), 'layui-btn layui-btn-xs layui-btn-danger', '', 'lay-event = deleteBasis type="button"');
            $html .= '</div>';
            $basisInfo[$k]['operation'] = $html;
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $basisInfo;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    public function showBasisData()
    {
        $qdbid = I('get.qdbid');
        $basisInfo = $this->DB_get_one('quality_detection_basis', '', ['qdbid' => $qdbid]);
        $userInfo = $this->DB_get_one('user', 'username', ['userid' => $basisInfo['adduserid']]);
        $basisInfo['content'] = htmlspecialchars_decode($basisInfo['content']);
        $basisInfo['adduser'] = $userInfo['username'];
        return $basisInfo;
    }

    public function addBasisData()
    {
        if (I('post.basis')) {
            $addData['basis'] = I('post.basis');
        } else {
            return ['status' => -1, 'msg' => '请输入检测依据标题'];
        }
        $addData['adddate'] = I('post.adddate');
        $addData['adduserid'] = session('userid');
        if (I('post.content')) {
            $addData['content'] = I('post.content');
        } else {
            return ['status' => -1, 'msg' => '请输入检测依据内容'];
        }
        $addId = $this->insertData('quality_detection_basis', $addData);
        //日志行为记录文字
        $log['basis'] = $addData['basis'];
        $text = getLogText('addBasis', $log);
        $this->addLog('notice', M()->getLastSql(), $text, $addId, '');
        if ($addId) {
            return ['status' => 1, 'msg' => '新增检测依据成功'];
        } else {
            return ['status' => -1, 'msg' => '新增检测依据失败'];
        }
    }

    public function editBasisData()
    {
        $qdbid = I('post.qdbid');
        if (I('post.basis')) {
            $editData['basis'] = I('post.basis');
        } else {
            return ['status' => -1, 'msg' => '请输入检测依据标题'];
        }
        $editData['adddate'] = I('post.adddate');
        $editData['adduserid'] = session('userid');
        if (I('post.content')) {
            $editData['content'] = I('post.content');
        } else {
            return ['status' => -1, 'msg' => '请输入检测依据内容'];
        }
        $this->updateData('quality_detection_basis', $editData, ['qdbid' => $qdbid]);
        //日志行为记录文字
        $log['basis'] = $editData['basis'];
        $text = getLogText('editBasis', $log);
        $this->addLog('notice', M()->getLastSql(), $text, $qdbid, '');
        return ['status' => 1, 'msg' => '编辑检测依据成功'];
    }

    public function deleteBasisData()
    {
        $qdbid = I('post.qdbid');
        $deleteData = $this->DB_get_one('quality_detection_basis', 'basis', ['qdbid' => $qdbid]);
        if ($deleteData['basis']) {
            //日志行为记录文字
            $this->deleteData('quality_detection_basis', ['qdbid' => $qdbid]);
            $log['basis'] = $deleteData['basis'];
            $text = getLogText('deleteBasis', $log);
            $this->addLog('notice', M()->getLastSql(), $text, $qdbid, '');
            return ['status' => 1, 'msg' => '删除检测依据成功'];
        } else {
            return ['status' => -1, 'msg' => '删除检测依据失败'];
        }
    }
}
