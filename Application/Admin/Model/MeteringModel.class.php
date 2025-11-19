<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;

class MeteringModel extends CommonModel
{
    protected $len = 100;
    protected $tablePrefix = 'sb_';
    protected $tableName = 'metering_plan';
    protected $MODULE = 'Metering';
    protected $Controller = 'Metering';

    //获取科室对应的设备
    public function getAssetsData()
    {
        $departid            = I('POST.departid');
        $where['departid']   = ['EQ', $departid];
        $where['main_assid'] = C('NO_STATUS');
        $where['is_delete']  = C('NO_STATUS');
        $field               = 'assets';
        $where['status']     = [
            'NOTIN',
            [
                C('ASSETS_STATUS_SCRAP'),
                C('ASSETS_STATUS_OUTSIDE'),
                C('ASSETS_STATUS_OUTSIDE_ON'),
                C('ASSETS_STATUS_SCRAP_ON'),
            ],
        ];//排除已报废，已外调，报废中，外调中的设备
        $Assets              = $this->DB_get_all('assets_info', $field, $where, 'assets', '', '');
        if ($Assets) {
            $result['status'] = 1;
            $result['msg']    = '获取成功';
            $result['result'] = $Assets;
        } else {
            $result['status'] = 2;
            $result['msg']    = '该科室未分配设备';
        }
        return $result;
    }

    //更新临时表数据
    public function updateTempData()
    {
        $tempid = I('POST.tempid');
        unset($_POST['tempid']);
        unset($_POST['type']);
        foreach ($_POST as $k => $v) {
            $data[$k] = htmlspecialchars(addslashes(trim($v)));
            if ($k == 'productid') {
                //验证序列号是否合法
                if ($data[$k] != '') {
                    $planWhere['status']    = ['NEQ', -1];
                    $planWhere['productid'] = ['EQ', $data[$k]];
                    $plan                   = $this->DB_get_one('metering_plan', 'productid', $planWhere);
                    $upload_temp_plan       = $this->DB_get_one('metering_plan_upload_temp', 'productid',
                        ['productid' => $data[$k]]);
                    if ($plan['productid'] or $upload_temp_plan['productid']) {
                        return ['status' => -2, 'msg' => '该序列号设备已存在，请检查避免重复'];
                    } else {
                        $assetsWhere['serialnum'] = ['EQ', $data[$k]];
                        $assetsWhere['status']    = ['NEQ', C('ASSETS_STATUS_SCRAP')];
                        $assets                   = $this->DB_get_one('assets_info', 'serialnum,assid', $assetsWhere);
                        if ($assets) {
                            $data['assid'] = $assets['assid'];
                        } else {
                            $data['assid'] = 0;
                        }
                    }
                } else {
                    $data['assid'] = 0;
                }
            } elseif ($k == 'mcid') {
                //验证计量分类是否合法
                $find = $this->DB_get_one('metering_categorys', 'mcid,mcategory', ['mcid' => $data[$k]]);
                if ($find['mcid']) {
                    $data[$k]          = $find['mcid'];
                    $data['mcategory'] = $find['mcategory'];
                } else {
                    return ['status' => -1, 'msg' => '查找不到 ' . $data[$k] . ' 的分类！！'];
                }
            } elseif ($k == 'remind_day') {
                if ($data[$k] <= 0) {
                    return ['status' => -1, 'msg' => '请输入合理提前提醒天数'];
                }
            } elseif ($k == 'next_date') {
                //验证下次待检时间是否合法
                if (!$data[$k] or time() > strtotime($data[$k])) {
                    return ['status' => -1, 'msg' => '请输入合理的下次待检时间'];
                }
            } elseif ($k == 'test_way') {
                if ($data[$k] != '院内' && $data[$k] != '院外') {
                    return ['status' => -1, 'msg' => '请输如正确的检定方式'];
                }
            } elseif ($k == 'status') {
                if ($data[$k] != '启用' && $data[$k] != '暂停') {
                    return ['status' => -1, 'msg' => '请输如正确的计划状态'];
                }
            }
        }
        $data['edituserid'] = session('userid');
        $data['editdate']   = time();
        $res                = $this->updateData('metering_plan_upload_temp', $data, ['tempid' => $tempid]);
        if ($res) {
            return ['status' => 1, 'msg' => '修改成功！'];
        } else {
            return ['status' => -1, 'msg' => '修改失败！'];
        }
    }

    //删除临时表数据
    public function delTempData()
    {
        $tempid  = trim(I('POST.tempid'), ',');
        $tempArr = explode(',', $tempid);
        $res     = $this->deleteData('metering_plan_upload_temp', ['tempid' => ['in', $tempArr]]);
        if ($res) {
            return ['status' => 1, 'msg' => '删除成功！'];
        } else {
            return ['status' => -1, 'msg' => '删除失败！'];
        }
    }

    //保存临时表数据
    public function batchAddMetering()
    {
        $tempid        = trim(I('POST.tempid'), ',');
        $tempArr       = explode(',', $tempid);
        $num           = 0;
        $saveTempidArr = [];
        foreach ($tempArr as $k => $v) {
            //按每次最多不超过$this->len条的数据获取临时表数据进行保存操作
            if ($num < $this->len) {
                $saveTempidArr[] = $v;
                $num++;
            }
            if ($num == $this->len) {
                //进行一次设备入库操作
                $this->benefitStorage($saveTempidArr);
                //重置
                $num           = 0;
                $saveTempidArr = [];
            }
        }
        if ($saveTempidArr) {
            $this->benefitStorage($saveTempidArr);
        }


        $this->updateMeteringPlanResult();


        return ['status' => 1, 'msg' => '保存数据成功！'];
    }


    //批量添加后更新检测表
    public function updateMeteringPlanResult()
    {
        $join                  = 'LEFT JOIN sb_metering_result AS R ON R.mpid=P.mpid';
        $fields                = 'P.mpid,P.next_date,R.mrid';
        $planWhere['P.status'] = ['NEQ', -1];
        $plan                  = $this->DB_get_all_join('metering_plan', 'P', $fields, $join, $planWhere);
        $key                   = 0;
        $addData               = [];
        foreach ($plan as &$one) {
            if (!$one['mrid']) {
                $addData[$key]['mpid']      = $one['mpid'];
                $addData[$key]['this_date'] = $one['next_date'];
                $key++;
            }
        }
        if ($addData) {
            $this->insertDataALL('metering_result', $addData);
        }


    }


    //批量修改
    public function batchUpdateData()
    {
        $mpid                   = trim(I('POST.mpid'), ',');
        $mpidArr                = explode(',', $mpid);
        $keyvalue['model']      = '规格 / 型号';
        $keyvalue['unit']       = '单位';
        $keyvalue['factory']    = '生产厂商';
        $keyvalue['mcategory']  = '计量分类';
        $keyvalue['cycle']      = '计量周期（月）';
        $keyvalue['test_way']   = '检定方式';
        $keyvalue['next_date']  = '下次待检日期';
        $keyvalue['respo_user'] = '计量负责人';
        $keyvalue['remark']     = '备注';
        $keyvalue['remind_day'] = '提前提醒天数';
        $keyvalue['status']     = '计划状态';
        $updata                 = [];
        $field                  = '';
        foreach (I('POST.field') as $k => $v) {
            $v = trim($v);
            if (!$keyvalue[$k]) {
                return ['status' => -1, 'msg' => '非法参数！'];
            }
            if ($k == 'mcategory') {
                if ($v <= 0) {
                    die(json_encode(['status' => -1, 'msg' => $keyvalue[$k] . '不能为空！']));
                }
            }
            if ($k == 'next_date') {
                if (!$v) {
                    die(json_encode(['status' => -1, 'msg' => $keyvalue[$k] . '不能为空！']));
                }
            }
            if ($k == 'remind_day') {
                if ($v <= 0) {
                    die(json_encode(['status' => -1, 'msg' => $keyvalue[$k] . '异常！']));
                }
            }
            if ($k == 'mcategory') {
                $field = 'mcid';
            } else {
                $field = $k;
            }
            $updata[$field] = $v;
        }

        //数据验证
        if ($field == 'next_date') {
            if (!$updata[$field] or time() > strtotime($updata[$field])) {
                return ['status' => -1, 'msg' => '请输入合理的' . $keyvalue[$field]];
            }
        }
        $where['mpid'] = ['IN', $mpidArr];
        $res           = $this->updateData('metering_plan', $updata, $where);
        if ($res) {
            if ($field == 'next_date') {
                $where['status']         = ['EQ', C('NOTHING_STATUS')];
                $resultData['this_date'] = $updata[$field];
                $this->updateData('metering_result', $resultData, $where);
            }
            return ['status' => 1, 'msg' => '批量维护成功！', 'field' => $field];
        } else {
            return ['status' => -1, 'msg' => '批量维护失败！'];
        }
    }

    //获取待入计划临时信息
    public function getWatingUploadMetering()
    {
        $limit             = I('post.limit') ? I('post.limit') : 10;
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $where['adduseid'] = session('userid');
        $where['is_save']  = C('NO_STATUS');//获取未上传的数据
        $total             = $this->DB_get_count('metering_plan_upload_temp', $where);
        //查询上次未完成保存的数据
        $data = $this->DB_get_all('metering_plan_upload_temp', '*', $where, '', 'departid asc,assets asc',
            $offset . ',' . $limit);
        if (!$data) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $checkProductid         = [];
        $planWhere['status']    = ['NEQ', -1];
        $planWhere['productid'] = ['NEQ', ''];
        $plan                   = $this->DB_get_all('metering_plan', 'productid', $planWhere);
        foreach ($plan as &$one) {
            $checkProductid[$one['productid']] = $one['productid'];
        }
        //判断待上传数据是否合法
        foreach ($data as $k => $v) {
            if ($checkProductid[$v['productid']]) {
                //是否已存在此序列号的计划
                $data[$k]['productid'] = '<span class="rquireCoin">' . $v['productid'] . '</span>';
            }
            if (!judgeNum($v['mcid']) or $v['mcid'] <= 0) {
                //计量分类
                $data[$k]['mcategory'] = '<span class="rquireCoin">' . $v['mcategory'] . '</span>';
            }
            if (!$v['next_date'] or time() > strtotime($v['next_date'])) {
                //下次待检日期不合法
                $data[$k]['next_date'] = '<span class="rquireCoin">' . $v['next_date'] . '</span>';
            }
            if ($v['test_way'] != '院内' && $v['test_way'] != '院外') {
                $data[$k]['test_way'] = '<span class="rquireCoin">' . $v['test_way'] . '</span>';
            }
            if ($v['status'] != '启用' && $v['status'] != '暂停') {
                $data[$k]['status'] = '<span class="rquireCoin">' . $v['status'] . '</span>';
            }
            if ($v['remind_day'] <= 0) {
                $data[$k]['remind_day'] = '<span class="rquireCoin">' . $v['remind_day'] . '</span>';

            }
            $data[$k]['operation'] = $this->returnListLink('删除',
                $this->full_open_url($this->MODULE, $this->Controller) . 'batchAddMetering', 'delTmpMetering',
                C('BTN_CURRENCY') . ' layui-btn-danger delTmpMetering');
        }
        $result['limit']  = (int)$limit;
        $result['offset'] = $offset;
        $result['total']  = (int)$total;
        $result['rows']   = $data;
        $result['code']   = 200;
        //var_dump($assets);exit;
        return $result;
    }

    //获取初始化的批量修改列表
    public function batchEditGetData()
    {
        $mpid              = trim(I('POST.mpid'), ',');
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $where['A.status'] = ['NEQ', -1];
        $where['A.mpid']   = ['IN', $mpid];
        $join[0]           = 'LEFT JOIN sb_metering_categorys AS C ON A.mcid=C.mcid';
        $fields            = 'mpid,plan_num,assid,assets,model,unit,factory,productid,departid,asset_count,mcategory,
        cycle,test_way,next_date,respo_user,remind_day,A.status,adduseid,adddate,A.remark';
        $total             = $this->DB_get_count_join('metering_plan', 'A', $join, $where);
        $list              = $this->DB_get_all_join('metering_plan', 'A', $fields, $join, $where, '', 'mpid asc',
            $offset . "," . $limit);
        if (!$list) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($list as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            if ($one['status'] == 1) {
                $one['status'] = '启用';
            } else {
                $one['status'] = '暂停';
            }
            if ($one['test_way'] == 1) {
                $one['test_way'] = '院内';
            } else {
                $one['test_way'] = '院外';
            }
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $list;
        return $result;
    }

    //获取对应下拉选择的字段表格数据
    public function getFieldsData()
    {
        $limit  = I('post.limit') ? I('post.limit') : 10;
        $page   = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        $mpid      = trim(I('POST.mpid'), ',');
        $mpid      = explode(',', $mpid);
        $selFields = I('POST.showFields');
        $title     = I('POST.title');
        $type2     = I('POST.type2');
        if ($type2 == 'getHeader') {
            $newField['field'] = $selFields;
            $newField['title'] = $title;
            $newField['align'] = 'center';
            $header            = $this->getEditHeader($newField, $width = ['10%', '20%', '25%', '22%', '25%']);
            return ['status' => 1, 'msg' => '成功', 'header' => $header];
        }
        $where['A.mpid'] = ['IN', $mpid];
        $join[0]         = 'LEFT JOIN sb_metering_categorys AS C ON A.mcid=C.mcid';
        $fields          = 'mpid,plan_num,assid,assets,model,unit,factory,productid,departid,asset_count,mcategory,
        cycle,test_way,next_date,respo_user,remind_day,A.status,adduseid,adddate,A.remark';
        $total           = $this->DB_get_count_join('metering_plan', 'A', $join, $where);
        $list            = $this->DB_get_all_join('metering_plan', 'A', $fields, $join, $where, '', 'mpid asc',
            $offset . "," . $limit);
        if (!$list) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($list as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            if ($one['status'] == 1) {
                $one['status'] = '启用';
            } else {
                $one['status'] = '暂停';
            }
            if ($one['test_way'] == 1) {
                $one['test_way'] = '院内';
            } else {
                $one['test_way'] = '院外';
            }
        }
        $result['total']  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result['rows']   = $list;
        if (!$result['rows']) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    //获取计量所有字段
    public function getDefaultShowFields()
    {
        $fields                = [];
        $fields['plan_num']    = '计划编号';
        $fields['assets']      = '设备名称';
        $fields['model']       = '规格 / 型号';
        $fields['asset_count'] = '设备数量';
        $fields['unit']        = '单位';
        $fields['factory']     = '生产厂商';
        $fields['productid']   = '产品序列号';
        $fields['department']  = '所属科室';
        $fields['mcategory']   = '计量分类';
        $fields['cycle']       = '计量周期（月）';
        $fields['test_way']    = '检定方式';
        $fields['next_date']   = '下次待检日期';
        $fields['respo_user']  = '计量负责人';
        $fields['remark']      = '备注';
        $fields['remind_day']  = '提前提醒天数';
        $fields['status']      = '计划状态';
        return $fields;
    }

    //获取所有列表头
    public function getTableHeader($showFields = [])
    {
        //查询配置文件默认列表显示选项
        $header = [];
        if (1) {
            $header[0]['type']  = 'checkbox';
            $header[0]['fixed'] = 'left';

            $header[1]['field']   = 'mpid';
            $header[1]['title']   = '序号';
            $header[1]['width']   = '50';
            $header[1]['fixed']   = 'left';
            $header[1]['align']   = 'center';
            $header[1]['templet'] = '#serialNumTpl';

            $header[2]['field']    = 'plan_num';
            $header[2]['title']    = '计划编号';
            $header[2]['fixed']    = 'left';
            $header[2]['minWidth'] = '150';
            $header[2]['align']    = 'center';

            $header[3]['field']    = 'assets';
            $header[3]['title']    = '设备名称';
            $header[3]['fixed']    = 'left';
            $header[3]['minWidth'] = '150';
            $header[3]['align']    = 'center';

            $header[4]['field']    = 'model';
            $header[4]['title']    = '规格 / 型号';
            $header[4]['minWidth'] = '120';
            $header[4]['align']    = 'center';

            $header[5]['field']    = 'asset_count';
            $header[5]['title']    = '设备数量';
            $header[5]['minWidth'] = '90';
            $header[5]['align']    = 'center';

            $header[6]['field']    = 'unit';
            $header[6]['title']    = '单位';
            $header[6]['minWidth'] = '80';
            $header[6]['align']    = 'center';

            $header[7]['field']    = 'factory';
            $header[7]['title']    = '生产厂商';
            $header[7]['minWidth'] = '160';
            $header[7]['align']    = 'center';

            $header[8]['field']    = 'productid';
            $header[8]['title']    = '产品序列号';
            $header[8]['minWidth'] = '160';
            $header[8]['align']    = 'center';

            $header[9]['field']    = 'department';
            $header[9]['title']    = '所属科室';
            $header[9]['minWidth'] = '140';
            $header[9]['align']    = 'center';

            $header[10]['field']    = 'mcategory';
            $header[10]['title']    = '计量分类';
            $header[10]['minWidth'] = '130';
            $header[10]['align']    = 'center';

            $header[11]['field']    = 'cycle';
            $header[11]['title']    = '计量周期（月）';
            $header[11]['minWidth'] = '150';
            $header[11]['align']    = 'center';

            $header[12]['field']    = 'test_way';
            $header[12]['title']    = '检定方式';
            $header[12]['minWidth'] = '120';
            $header[12]['align']    = 'center';

            $header[13]['field']    = 'next_date';
            $header[13]['title']    = '下次待检日期';
            $header[13]['minWidth'] = '140';
            $header[13]['align']    = 'center';

            $header[14]['field']    = 'respo_user';
            $header[14]['title']    = '计量负责人';
            $header[14]['minWidth'] = '100';
            $header[14]['align']    = 'center';

            $header[15]['field']    = 'remark';
            $header[15]['title']    = '备注';
            $header[15]['minWidth'] = '140';
            $header[15]['align']    = 'center';

            $header[16]['field']    = 'remind_day';
            $header[16]['title']    = '提前提醒天数';
            $header[16]['minWidth'] = '120';
            $header[16]['align']    = 'center';

            $header[17]['field']    = 'status';
            $header[17]['title']    = '计划状态';
            $header[17]['minWidth'] = '120';
            $header[17]['align']    = 'center';
            $header[44]['fixed']    = 'right';

            $header[18]['field']    = 'operation';
            $header[18]['title']    = '操作';
            $header[18]['minWidth'] = '280';
            $header[18]['align']    = 'center';
            $header[18]['fixed']    = 'right';
        }
        if ($showFields) {
            foreach ($header as $k => $v) {
                if ($v['fixed']) {
                    continue;
                }
                if (!in_array($v['field'], $showFields)) {
                    unset($header[$k]);
                }
            }
        }
        return $header;
    }

    //获取初始化的列表头
    public function getEditHeader($newField = [], $width = [])
    {
        $header[0]['field'] = 'mpid';
        $header[0]['title'] = '编号';
        $header[0]['width'] = '10%';
        $header[0]['align'] = 'center';

        $header[1]['field'] = 'plan_num';
        $header[1]['title'] = '计划编号';
        $header[1]['width'] = '26%';
        $header[1]['align'] = 'center';

        $header[2]['field'] = 'assets';
        $header[2]['title'] = '设备名称';
        $header[2]['width'] = '38%';
        $header[2]['align'] = 'center';

        $header[3]['field'] = 'productid';
        $header[3]['title'] = '设备序列号';
        $header[3]['width'] = '27%';
        $header[3]['align'] = 'center';

        if ($newField) {
            $len                   = count($header);
            $header[$len]['field'] = $newField['field'];
            $header[$len]['title'] = $newField['title'];
            $header[$len]['align'] = $newField['align'];
            foreach ($header as $k => $v) {
                $header[$k]['width'] = $width[$k];
            }
        }
        return $header;
    }

    //
    public function getMeteringData()
    {
        $mpid           = I('GET.mpid');
        $where['mpid']  = $mpid;
        $join[0]        = 'LEFT JOIN sb_metering_categorys AS C ON A.mcid=C.mcid';
        $join[1]        = 'LEFT JOIN sb_user AS U2 ON A.adduseid=U2.userid';
        $fields         = 'mpid,plan_num,assid,assets,model,unit,factory,productid,departid,asset_count,mcategory,A.mcid,
        cycle,test_way,next_date,respo_user,remind_day,A.status,adduseid,adddate,A.remark,U2.username AS adduser';
        $data           = $this->DB_get_one_join('metering_plan', 'A', $fields, $join, $where);
        $asInfo         = $this->DB_get_one('assets_info', 'assnum', ['assid' => $data['assid']]);
        $data['assnum'] = $asInfo['assnum'] ? $asInfo['assnum'] : '';
        $departname     = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $data['department'] = $departname[$data['departid']]['department'];
        if ($data['test_way'] == 1) {
            $data['test_way_name'] = '院内';
        } else {
            $data['test_way_name'] = '院外';
        }
        if ($data['status'] == 1) {
            $data['statusName'] = '启用';
        } else {
            $data['statusName'] = '停用';
        }
        return $data;
    }

    //上传文件
    public function uploadData()
    {
        if (empty($_FILES)) {
            return ['status' => -1, 'msg' => '请上传文件'];
        }
        $uploadConfig = [
            'maxSize'  => 3145728,
            'rootPath' => './Public/',
            'savePath' => 'uploads/',
            'saveName' => ['uniqid', ''],
            'exts'     => ['xlsx', 'xls', 'xlsm'],
            'autoSub'  => true,
            'subName'  => ['date', 'Ymd'],
        ];
        $upload       = new \Think\Upload($uploadConfig);
        $info         = $upload->upload();
        if (!$info) {
            return ['status' => -1, 'msg' => '导入数据出错'];
        }
        vendor("PHPExcel.PHPExcel");
        $filePath = $upload->rootPath . $info['file']['savepath'] . $info['file']['savename'];
        if (empty($filePath) or !file_exists($filePath)) {
            die('file not exists');
        }
        $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                return ['status' => -1, 'msg' => '文件格式错误'];
            }
        }
        $excelDate    = new \PHPExcel_Shared_Date();
        $PHPExcel     = $PHPReader->load($filePath);        //建立excel对象
        $currentSheet = $PHPExcel->getSheet(0);        //**读取excel文件中的指定工作表*/
        $allColumn    = $currentSheet->getHighestColumn();        //**取得最大的列号*/
        ++$allColumn;
        $allRow   = $currentSheet->getHighestRow();        //**取得一共有多少行*/
        $data     = [];
        $cellname = [
            'A' => 'department',
            'B' => 'assets',
            'C' => 'model',
            'D' => 'unit',
            'E' => 'factory',
            'F' => 'productid',
            'G' => 'mcategory',
            'H' => 'cycle',
            'I' => 'test_way',
            'J' => 'next_date',
            'K' => 'respo_user',
            'L' => 'remind_day',
            'M' => 'status',
            'N' => 'remark',
        ];
        //需要进行日期处理的保存在一个数组
        $toDate = [
            'next_date',
        ];

        for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for ($colIndex = 'A'; $colIndex != $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                if (in_array($cellname[$colIndex], $toDate)) {
                    if (!$currentSheet->getCell($addr)->getValue()) {
                        $cell = '';
                    } else {
                        $cell = gmdate("Y-m-d", $excelDate::ExcelToPHP($currentSheet->getCell($addr)->getValue()));
                    }
                } else {
                    $cell = $currentSheet->getCell($addr)->getValue();
                }
                if ($cell instanceof \PHPExcel_RichText) { //富文本转换字符串
                    $cell = $cell->__toString();
                }
                if ($cellname[$colIndex] == 'department') {
                    if (!$cell) {
                        break;
                    }
                }
                //  echo $cell."---1";
                if ($cellname[$colIndex] == 'assets') {
                    if (!$cell) {
                        break;
                    }
                }
                $data[$rowIndex - 2][$cellname[$colIndex]] = trim($cell) ? trim($cell) : '';
            }
        }
        if (!$data) {
            return ['status' => -1, 'msg' => '导入数据失败'];
        }


        $departname  = [];
        $departidArr = [];
        $mcidArr     = [];
        $assidArr    = [];
        include APP_PATH . "Common/cache/department.cache.php";
        //获取计量分类
        $mCategorys = $this->DB_get_all('metering_categorys', 'mcid,mcategory');
        //生成对应键值的分类数组
        foreach ($mCategorys as &$one) {
            $mcidArr[$one['mcategory']] = $one['mcid'];
        }

        //生成对应键值的科室数组
        foreach ($departname as $key => $value) {
            $departidArr[$value['department']] = $key;
        }

        //唯一序号数组
        $productidArr = [];
        foreach ($data as $key => $value) {
            //去除重复的设备
            if ($value['productid'] != '' && in_array($value['productid'], $productidArr)) {
                unset($data[$key]);
            } else {
                $productidArr[] = $value['productid'];

            }
        }


        if ($productidArr) {
            $assets = $this->DB_get_all('assets_info', 'assid,serialnum', ['serialnum' => ['IN', $productidArr]]);
            foreach ($assets as &$one) {
                $assidArr[$one['serialnum']] = $one['assid'];
            }
        }


        foreach ($data as &$one) {
            $one['departid'] = $departidArr[$one['department']];
            $one['mcid']     = $mcidArr[$one['mcategory']];
            $one['assid']    = $assidArr[$one['productid']];
            if (!$one['status']) {
                $one['status'] = '启用';
            }
            if (!$one['test_way']) {
                $one['test_way'] = '院内';
            }
        }


        //查询已存在并且需要是唯一的数据（存在序列号）
        $PlanWhere['status']    = ['NEQ', -1];
        $PlanWhere['productid'] = ['NEQ', ''];
        $PlanData               = $this->DB_get_all('metering_plan', '', $PlanWhere);
        $PlanRes                = [];
        $where                  = [];

        foreach ($PlanData as &$PlanValue) {
            //将已存在的明细记录组合成数组
            $PlanRes[$PlanValue['productid']] = true;
        }


        foreach ($data as $k => $v) {
            if ($v['departid'] <= 0 or !$v['assets']) {
                //将非法数据清空
                unset($data[$k]);
                break;
            }
            if ($PlanRes[$v['productid']]) {
                //将已存在计划表的数据删除.避免上传重复的数据
                unset($data[$k]);
                break;
            }
            //记录存在的序号
            if ($data[$k]['productid'] != '') {
                $where[] = [['productid' => $data[$k]['productid']]];
            }
        }


        if (!$data) {
            //上传的文件数据和临时表中已存在数据重复
            return ['status' => -1, 'msg' => '该文件数据已上传，请勿重复上传！'];
        }

        if ($where) {
            $where['_logic'] = 'or';
            //更新临时表数据-将未保存的旧数据删除
            $this->deleteData('metering_plan_upload_temp', $where);
        }
        //保存数据到临时表
        $insertData = [];
        $num        = 0;
        foreach ($data as $k => $v) {
            if (!$v['department'] || !isset($v['assets'])) {
                continue;
            }
            if ($num < $this->len) {
                //$this->len条存一次数据到数据库
                //如果编号存在就记录，避免记录错误的数据
                $tempid                       = getRandomId();
                $insertData[$num]['tempid']   = $tempid;
                $insertData[$num]['adduseid'] = session('userid');
                $insertData[$num]['adddate']  = time();
                $insertData[$num]['is_save']  = 0;
                foreach ($v as $k1 => $v1) {
                    $insertData[$num][$k1] = $v1;
                }
                //避免用户不小心修改设备信息，重新在设备表获取
                $num++;

            }
            if ($num == $this->len) {
                //插入数据
                $this->insertDataALL('metering_plan_upload_temp', $insertData);
                //重置数据
                $num        = 0;
                $insertData = [];
            }
        }
        if ($insertData) {
            $this->insertDataALL('metering_plan_upload_temp', $insertData);
        }
        return ['status' => 1, 'msg' => '上传数据成功，请核对后再保存！'];
    }

    //记录检测结果
    public function setMeteringResult()
    {
        $mpid        = I('POST.mpid');
        $mrid        = I('POST.mrid');
        $report_num  = I('POST.report_num');
        $result      = I('POST.result');
        $money       = I('POST.money');
        $test_person = I('POST.test_person');
        $auditor     = I('POST.auditor');
        $remark      = I('POST.remark');
        $next_data   = I('POST.next_date');
        $company     = I('POST.company');
        if (!$mpid && !$mrid) {
            die(json_encode(['status' => -999, 'msg' => '非法操作']));
        }
        $plan = $this->DB_get_one('metering_plan', 'next_date,cycle', ['mpid' => $mpid]);
        if (!$plan) {
            die(json_encode(['status' => -999, 'msg' => '非法操作']));
        }
        if (!$report_num) {
            die(json_encode(['status' => -1, 'msg' => '请输入证书编号']));
        }
        if (!$next_data or time() > strtotime($next_data)) {
            die(json_encode(['status' => -1, 'msg' => '请选择正确待检日期']));
        }
        //判断检修结果是否已录入
        $resultdata = $this->DB_get_one('metering_result', 'status', ['mrid' => $mrid]);
        if (!$resultdata) {
            die(json_encode(['status' => -999, 'msg' => '查找不到相关信息！']));
        }
        if ($resultdata['status'] == C('YES_STATUS')) {
            die(json_encode(['status' => -999, 'msg' => '请勿重复提交检测结果！']));
        }
        $setData['this_date']   = $plan['next_date'];
        $setData['report_num']  = $report_num;
        $setData['result']      = $result;
        $setData['money']       = $money;
        $setData['company']     = $company;
        $setData['test_person'] = $test_person;
        $setData['auditor']     = $auditor;
        $setData['remark']      = $remark;
        $setData['status']      = C('YES_STATUS');
        $setData['adddate']     = date('Y-m-d', time());
        $setData['adduserid']   = session('userid');
        $set                    = $this->updateData('metering_result', $setData, ['mrid' => $mrid]);
        if ($set) {
            $fileName = I('POST.fileName');
            if ($fileName) {
                $this->addMeteringeFile($mrid);
            }
            $planData['next_date'] = $next_data;
            $this->updateData('metering_plan', $planData, ['mpid' => $mpid]);
            //查询是否已生成下一个待检记录
            $exists = $this->DB_get_one('metering_result', 'mrid', ['mpid' => $mpid, 'status' => C('NO_STATUS')]);
            if (!$exists) {
                //不存在，新增一条记录
                $newData['this_date'] = $next_data;
                $newData['mpid']      = $mpid;
                $this->insertData('metering_result', $newData);
            }
            $data['status'] = 1;
            $data['msg']    = '录入成功';
        } else {
            $data['status'] = -1;
            $data['msg']    = '录入失败';
        }

        return $data;
    }

    //修改检测结果
    public function editMeteringResult()
    {
        $mpid        = I('POST.mpid');
        $mrid        = I('POST.mrid');
        $this_date   = I('POST.this_date');
        $report_num  = I('POST.report_num');
        $result      = I('POST.result');
        $money       = I('POST.money');
        $test_person = I('POST.test_person');
        $auditor     = I('POST.auditor');
        $remark      = I('POST.remark');
        $company     = I('POST.company');
        if (!$mpid && !$mrid) {
            die(json_encode(['status' => -999, 'msg' => '非法操作']));
        }
        if (!$report_num) {
            die(json_encode(['status' => -1, 'msg' => '请输入证书编号']));
        }
        $setData['this_date']   = $this_date;
        $setData['report_num']  = $report_num;
        $setData['result']      = $result;
        $setData['money']       = $money;
        $setData['company']     = $company;
        $setData['test_person'] = $test_person;
        $setData['auditor']     = $auditor;
        $setData['remark']      = $remark;
        $setData['edit_time']   = date('Y-m-d H:i:s');
        $setData['edit_uuid']   = session('userid');
        $set                    = $this->updateData('metering_result', $setData, ['mrid' => $mrid]);
        if ($set) {
            $fileName = I('POST.fileName');
            if ($fileName) {
                //删除原来的记录
                $this->deleteData('metering_result_reports', ['mrid' => $mrid]);
                $this->addMeteringeFile($mrid);
            }
            $data['status'] = 1;
            $data['msg']    = '修改成功';
        } else {
            $data['status'] = -1;
            $data['msg']    = '修改失败';
        }
        return $data;
    }

    //修改计划
    public function saveMetering()
    {
        $mpid = I('POST.mpid');
        if ($mpid > 0) {
            $mcid       = I('POST.categorys');
            $cycle      = I('POST.cycle');
            $factory    = I('POST.factory');
            $model      = I('POST.model');
            $next_date  = I('POST.next_date');
            $productid  = I('POST.productid');
            $remark     = I('POST.remark');
            $remind_day = I('POST.remind_day');
            $respo_user = I('POST.respo_user');
            $test_way   = I('POST.test_way');
            $unit       = I('POST.unit');

            $old_data = $this->DB_get_one('metering_plan', '', ['mpid' => $mpid]);
            if (!$old_data) {
                die(json_encode(['status' => -1, 'msg' => '非法参数']));
            }
            if ($mcid <= 0) {
                die(json_encode(['status' => -1, 'msg' => '请选择计量分类']));
            }
            if ((int)$remind_day <= 0) {
                die(json_encode(['status' => -1, 'msg' => '请补充正确的提前提醒天数']));
            }
            if (!$next_date) {
                die(json_encode(['status' => -1, 'msg' => '请补充下次待检日期']));
            }
            $data['mcid']       = $mcid;
            $data['cycle']      = $cycle;
            $data['factory']    = $factory;
            $data['model']      = $model;
            $data['remark']     = $remark;
            $data['remind_day'] = $remind_day;
            $data['respo_user'] = $respo_user;
            $data['test_way']   = $test_way;
            $data['unit']       = $unit;
            $data['next_date']  = $next_date;
            $data['status']     = I('post.status');
            if ($productid && $old_data['productid'] != $productid) {
                $assets                  = $this->DB_get_one('assets_info', 'serialnum,assid',
                    ['serialnum' => $productid]);
                $data['productid']       = $productid;
                $checkWhere['serialnum'] = ['EQ', $productid];
                $checkWhere['status']    = ['NEQ', -1];
                $check                   = $this->DB_get_one('metering_plan', 'mpid', $checkWhere);
                if ($check) {
                    die(json_encode(['status' => -1, 'msg' => '该设备已制定计划,请输入其他序号']));
                }
                if ($assets) {
                    $data['assid'] = $assets['assid'];
                }
            }
            $save = $this->updateData('metering_plan', $data, ['mpid' => $mpid]);
            if ($next_date != $old_data['next_date']) {
                $where['mpid']           = ['EQ', $mpid];
                $where['status']         = ['EQ', C('NOTHING_STATUS')];
                $resultData['this_date'] = $next_date;
                $this->updateData('metering_result', $resultData, $where);
            }
            return ['status' => 1, 'msg' => '修改成功'];
        } else {
            die(json_encode(['status' => -1, 'msg' => '非法操作']));
        }
    }

    //删除计划
    public function delMetering()
    {
        $mpid = I('POST.mpid');
        if ($mpid) {
            $resultWhere['status'] = C('YES_STATUS');
            $resultWhere['mpid']   = $mpid;
            $result                = $this->DB_get_one('metering_result', 'status', $resultWhere);
            $where['mpid']         = $mpid;
            if ($result) {
                $data['status']  = -1;
                $del             = $this->updateData('metering_plan', $data, $where);
                $where['status'] = C('NO_STATUS');
                $this->deleteData('metering_result', $where);
            } else {
                $del = $this->deleteData('metering_plan', $where);
                $this->deleteData('metering_result', $where);
            }
            if ($del) {
                $result['status'] = 1;
                $result['msg']    = '删除成功';
            } else {
                $result['status'] = -1;
                $result['msg']    = '删除成功';
            }
            return $result;
        } else {
            die(json_encode(['status' => -1, 'msg' => '非法操作']));
        }
    }

    //批量删除
    public function delBatchMetering()
    {
        $mpid                  = trim(I('POST.mpid'), ',');
        $tempArr               = explode(',', $mpid);
        $resultWhere['status'] = ['EQ', C('YES_STATUS')];
        $resultWhere['mpid']   = ['IN', $tempArr];
        $result                = $this->DB_get_all('metering_result', 'mpid,mrid', $resultWhere);
        $doData                = [];
        foreach ($result as &$value) {
            $doData[] = $value['mpid'];
        }
        $notDoData = array_diff($tempArr, $doData);
        if ($doData) {
            $doWhere['mpid']  = ['IN', $doData];
            $doSave['status'] = -1;
            $this->updateData('metering_plan', $doSave, $doWhere);
            $doWhere['status'] = ['EQ', C('NO_STATUS')];
            $this->deleteData('metering_result', $doWhere);
        }
        if ($notDoData) {
            $notDoWhere['mpid'] = ['IN', $notDoData];
            $this->deleteData('metering_plan', $notDoWhere);
            $this->deleteData('metering_result', $notDoWhere);
        }
        return ['status' => 1, 'msg' => '删除成功！'];
    }

    //检测列表
    public function getMeteringResult()
    {
        $is_metering       = I('POST.is_metering');
        $departid          = I('POST.departid');
        $assetsName        = I('POST.assetsName');
        $assnum            = I('POST.assnum');
        $productid         = I('POST.productid');
        $day_min           = I('POST.day_min');
        $day_max           = I('POST.day_max');
        $categorys         = I('POST.categorys');
        $startDate         = I('POST.startDate');
        $endDate           = I('POST.endDate');
        $hospital_id       = I('POST.hospital_id');
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $where['A.status'] = ['NEQ', -1];
        if ($is_metering or $hospital_id) {
            if ($is_metering) {
                $assetsDataWhere['is_metering'] = ['EQ', C('YES_STATUS')];
            }
            if ($hospital_id) {
                $assetsDataWhere['hospital_id'] = $hospital_id;
            } else {
                $assetsDataWhere['hospital_id'] = session('current_hospitalid');
            }
            $assetsData = $this->DB_get_all('assets_info', 'assid', $assetsDataWhere,'','assid desc');
	#var_dump($assetsData);exit;           
 if ($assetsData) {
                $assetsAssid = [];
                foreach ($assetsData as &$one) {
                    $assetsAssid[] = $one['assid'];
                }
                $where['A.assid'] = ['IN', $assetsAssid];
            } else {
                $result['msg']  = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        }
        if ($departid) {
            $where['A.departid'] = ['IN', $departid];
        } else {
            $where['A.departid'] = ['IN', session('departid')];
        }
        if ($assetsName) {
            $where['A.assets'] = ['LIKE', '%' . $assetsName . '%'];
        }
        if ($assnum) {
            $where['D.assnum'] = ['LIKE', '%' . $assnum . '%'];
        }
        if ($productid) {
            $where['A.productid'] = ['LIKE', '%' . $productid . '%'];
        }
        if ($day_min) {
            $where['A.remind_day'][] = ['EGT', $day_min];
        }
        if ($day_max) {
            $where['A.remind_day'][] = ['ELT', $day_max];
        }
        if ($categorys) {
            $where['A.mcid'][] = ['EQ', $categorys];
        }
        if ($startDate) {
            $where['R.this_date'][] = ['EGT', $startDate];
        }
        if ($endDate) {
            $where['R.this_date'][] = ['ELT', $endDate];
        }
        $join[0] = 'LEFT JOIN sb_metering_plan AS A ON A.mpid=R.mpid';
        $join[1] = 'LEFT JOIN sb_metering_categorys AS C ON A.mcid=C.mcid';
        $join[2] = 'LEFT JOIN sb_assets_info AS D ON A.assid=D.assid';
        $fields  = 'R.mrid,R.mpid,R.this_date,R.status,A.plan_num,A.assid,A.assets,A.model,A.unit,A.factory,A.productid,A.departid,
        A.asset_count,A.cycle,A.test_way,A.respo_user,A.remind_day,A.adduseid,A.adddate,A.status as mp_status,R.remark,mcategory,D.assnum';
        $total   = $this->DB_get_count_join('metering_result', 'R', $join, $where);
        $list    = $this->DB_get_all_join('metering_result', 'R', $fields, $join, $where, '', 'mpid desc',
            $offset . "," . $limit);
        if (!$list) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $menuData   = get_menu($this->MODULE, 'Metering', 'setMeteringResult');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($list as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            $one['operation']  = '';
            if ($one['test_way'] == 1) {
                $one['test_way'] = '院内';
            } else {
                $one['test_way'] = '院外';
            }
            if ($menuData) {
                if ($one['status'] == C('YES_STATUS')) {
                    $one['operation'] .= $this->returnListLink('详情', $menuData['actionurl'], 'showMeteringResult',
                        C('BTN_CURRENCY') . ' layui-btn-primary');
                } else {
                    if ($one['mp_status'] == 0) {
                        $one['operation'] .= $this->returnListLink('已暂停', $menuData['actionurl'],
                            'showMeteringResult', C('BTN_CURRENCY') . ' layui-btn-primary');
                    } else {
                        $one['operation'] .= $this->returnListLink('检测', $menuData['actionurl'], 'setMetering',
                            C('BTN_CURRENCY') . ' layui-btn-danger');
                    }
                }
            } else {
                $one['operation'] .= $this->returnListLink('详情', $menuData['actionurl'], 'showMeteringResult',
                    C('BTN_CURRENCY') . ' layui-btn-primary');
            }
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $list;
        return $result;
    }

    //设备记录历史
    public function getAssetsMeteringHistory()
    {

    }

    //计划列表
    public function getMeteringList()
    {
        $is_metering       = I('POST.is_metering');
        $departid          = I('POST.departid');
        $assetsName        = I('POST.assetsName');
        $productid         = I('POST.productid');
        $day_min           = I('POST.day_min');
        $day_max           = I('POST.day_max');
        $categorys         = I('POST.categorys');
        $startDate         = I('POST.startDate');
        $endDate           = I('POST.endDate');
        $hospital_id       = I('POST.hospital_id');
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $where['A.status'] = ['NEQ', -1];
        if ($is_metering or $hospital_id) {
            if ($is_metering) {
                $assetsDataWhere['is_metering'] = ['EQ', C('YES_STATUS')];
            }
            if ($hospital_id) {
                $assetsDataWhere['hospital_id'] = $hospital_id;
            } else {
                $assetsDataWhere['hospital_id'] = session('current_hospitalid');
            }
            $assetsData = $this->DB_get_all('assets_info', 'assid', $assetsDataWhere);
            if ($assetsData) {
                $assetsAssid = [];
                foreach ($assetsData as &$one) {
                    $assetsAssid[] = $one['assid'];
                }
                $where['A.assid'] = ['IN', $assetsAssid];
            } else {
                $result['msg']  = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        }

        if ($departid) {
            $where['A.departid'] = ['IN', $departid];
        } else {
            $where['A.departid'] = ['IN', session('departid')];
        }
        if ($assetsName) {
            $where['A.assets'] = ['LIKE', '%' . $assetsName . '%'];
        }
        if ($productid) {
            $where['A.productid'] = ['LIKE', '%' . $productid . '%'];
        }
        if ($day_min) {
            $where['A.remind_day'][] = ['EGT', $day_min];
        }
        if ($day_max) {
            $where['A.remind_day'][] = ['ELT', $day_max];
        }
        if ($categorys) {
            $where['A.mcid'][] = ['EQ', $categorys];
        }
        if ($startDate) {
            $where['A.next_date'][] = ['EGT', $startDate];
        }
        if ($endDate) {
            $where['A.next_date'][] = ['ELT', $endDate];
        }
        $join[0] = 'LEFT JOIN sb_metering_categorys AS C ON A.mcid=C.mcid LEFT JOIN sb_assets_info AS D ON A.assid = D.assid';
        $fields  = 'A.mpid,A.hospital_id,A.plan_num,A.assid,A.assets,A.model,A.unit,A.factory,A.productid,A.departid,A.asset_count,C.mcategory,
        A.cycle,A.test_way,A.next_date,A.respo_user,A.remind_day,A.status,A.adduseid,A.adddate,A.remark,D.assnum';
        $total   = $this->DB_get_count_join('metering_plan', 'A', $join, $where);
        $list    = $this->DB_get_all_join('metering_plan', 'A', $fields, $join, $where, '', 'mpid asc',
            $offset . "," . $limit);
        if (!$list) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $saveMenu   = get_menu($this->MODULE, 'Metering', 'saveMetering');
        $delMenu    = get_menu($this->MODULE, 'Metering', 'delMetering');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($list as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            if ($one['status'] == 1) {
                $one['status'] = '启用';
            } else {
                $one['status'] = '暂停';
            }
            if ($one['test_way'] == 1) {
                $one['test_way'] = '院内';
            } else {
                $one['test_way'] = '院外';
            }
            $one['operation'] = '<div class="layui-btn-group">';
            $one['operation'] .= $this->returnListLink('详情', C('ADMIN_NAME') . '/Metering/getMeteringList',
                'showMetering', C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($saveMenu) {
                if (strpos($one['plan_num'], 'IM-') !== false) {
                    //IM-开头的计划为历史记录导入，不允许修改
                    $one['operation'] .= $this->returnListLink($saveMenu['actionname'], '', '',
                        C('BTN_CURRENCY') . ' layui-btn-disabled');
                } else {
                    $one['operation'] .= $this->returnListLink($saveMenu['actionname'], $saveMenu['actionurl'],
                        'saveMetering', C('BTN_CURRENCY') . ' layui-btn-warm');
                }
            }
            if ($delMenu) {
                $one['operation'] .= $this->returnListLink($delMenu['actionname'], $delMenu['actionurl'], 'delMetering',
                    C('BTN_CURRENCY') . ' layui-btn-danger');
            }

            $one['operation'] .= '</div>';
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $list;
        return $result;
    }

    //获取上传文件
    public function getFileList()
    {
        $mrid = I('GET.mrid');
        $file = $this->DB_get_all('metering_result_reports', 'name,url,adddate', ['mrid' => $mrid]);
        if ($file) {
            foreach ($file as &$fileValue) {
                $suffix                 = substr(strrchr($fileValue['name'], '.'), 1);
                $fileValue['operation'] = '<div class="layui-btn-group">';
                $supplement             = 'data-path="' . $fileValue['url'] . '" data-name="' . $fileValue['name'] . '"';
                $fileValue['operation'] .= $this->returnListLink('下载', '', '', C('BTN_CURRENCY') . ' downFile', '',
                    $supplement);
                if ($suffix != 'doc' && $suffix != 'docx') {
                    $fileValue['operation'] .= $this->returnListLink('预览', '', '',
                        C('BTN_CURRENCY') . ' layui-btn-normal showFile', '', $supplement);
                }
                $fileValue['operation'] .= '</div>';
            }
        }
        return $file;
    }

    //获取对应设备序列号
    public function getSerialnum()
    {
        $assets              = I('POST.assets');
        $departid            = I('POST.departid');
        $where['departid']   = ['EQ', $departid];
        $where['main_assid'] = C('NO_STATUS');
        $where['is_delete']  = C('NO_STATUS');
        $where['assets']     = ['EQ', $assets];
        $where['status']     = [
            'NOTIN',
            [
                C('ASSETS_STATUS_SCRAP'),
                C('ASSETS_STATUS_OUTSIDE'),
                C('ASSETS_STATUS_OUTSIDE_ON'),
                C('ASSETS_STATUS_SCRAP_ON'),
            ],
        ];//排除已报废，已外调，报废中，外调中的设备
        //        $where['serialnum'] = array('NEQ', '');
        $join = 'LEFT JOIN sb_assets_factory AS F ON F.assid=A.assid';
        $data = $this->DB_get_all_join('assets_info', 'A', 'A.assid,A.assnum,F.factory,A.serialnum,A.model,A.unit',
            $join, $where);
        if ($data) {
            $assets = [];
            $assid  = [];
            foreach ($data as &$one) {
                if (!$one['serialnum']) {
                    $one['serialnum'] = $one['assnum'];
                }
                $assets[] = $one['serialnum'];
                $assid[]  = $one['assid'];
            }
            $planWhere['productid'] = ['IN', $assets];
            $planWhere['status']    = ['NEQ', -1];
            $plan                   = $this->DB_get_all('metering_plan', 'productid', $planWhere);
            if ($plan) {
                foreach ($plan as &$planValue) {
                    $result['result']['noselect'][] = $planValue['productid'];
                }
            } else {
                $result['result']['noselect'] = [];
            }
            $result['status']            = 1;
            $result['result']['assets']  = $data;
            $result['result']['model']   = $data[0]['model'];
            $result['result']['factory'] = $data[0]['factory'];
            $result['result']['unit']    = $data[0]['unit'];
        } else {
            $result['status'] = -200;
        }
        return $result;
    }

    //导出模板
    public function exploreMeteringModel()
    {

        $departid    = I('GET.departid');
        $is_metering = I('GET.is_metering');
        //导出模板
        $xlsName = "Metering";
        $xlsCell = [
            '所属科室',
            '设备名称',
            '规格/型号',
            '单位',
            '生产厂商',
            '产品序号',
            '计量分类',
            '计量周期',
            '检定方式(院内/院外)',
            '下次待检日期(例:0000/00/00)',
            '计量负责人',
            '提前提醒天数',
            '计划状态(启用/暂停)',
            '备注',
        ];
        //单元格宽度设置
        $width = [
            '所属科室'                    => 20,
            '设备名称'                    => 25,
            '规格/型号'                   => 15,
            '单位'                        => 10,
            '生产厂商'                    => 25,
            '产品序号'                    => 15,
            '计量分类'                    => 15,
            '计量周期'                    => 13,
            '检定方式(院内/院外)'         => 20,
            '下次待检日期(例:0000/00/00)' => 28,
            '计量负责人'                  => 13,
            '提前提醒天数'                => 13,
            '计划状态(启用/暂停)'         => 19,
            '备注'                        => 25,

        ];
        //单元格颜色设置（例如必填行单元格字体颜色为红色）
        $color  = [
            '所属科室'                    => 'FF0000',//颜色代码
            '设备名称'                    => 'FF0000',
            '计量分类'                    => 'FF0000',
            '下次待检日期(例:0000/00/00)' => 'FF0000',
            '提前提醒天数'                => 'FF0000',
        ];
        $join   = 'LEFT JOIN sb_assets_factory AS F ON F.assid=A.assid';
        $fields = 'A.departid as department,A.assets,A.model,A.unit,F.factory,serialnum';


        //筛选已经建立计划的设备
        $planWhere['status'] = ['NEQ', -1];
        $planWhere['assid']  = ['NEQ', null];
        $plan                = $this->DB_get_all('metering_plan', 'assid', $planWhere);

        $notassid = [];
        if ($plan) {
            foreach ($plan as &$one) {
                $notassid[] = $one['assid'];
            }
            $where['assid'] = ['NOTIN', $notassid];
        }
        $where['status'] = ['NEQ', C('ASSETS_STATUS_SCRAP')];
        if ($is_metering) {
            $where['is_metering'] = ['EQ', C('YES_STATUS')];
        }
        if ($departid) {
            $where['departid'] = ['EQ', $departid];
        }
        $departmentAssets = $this->DB_get_all_join('assets_info', 'A', $fields, $join, $where, '', 'departid');
        if (!$departmentAssets) {
            return false;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($departmentAssets as &$one) {
            $one['department'] = $departname[$one['department']]['department'];
        }
        $mCategorys                    = $this->DB_get_all('metering_categorys', 'mcid,mcategory');
        $newexport['departmentAssets'] = $departmentAssets;
        $newexport['mCategorys']       = $mCategorys;
        exportMeteringTemplate('计量计划导入模板', $xlsName, $xlsCell, $width, $color, $newexport);
    }

    //新增分类
    public function addCategorys()
    {
        $categorys = I('categorys');
        if (!$categorys) {
            die(json_encode(['status' => -1, 'msg' => '请输入分类名称']));
        }
        $categorysArr       = explode(',', ltrim($categorys, ","));
        $categorysArr       = array_unique($categorysArr);
        $where['mcategory'] = ['IN', $categorysArr];
        $data               = $this->DB_get_all('metering_categorys', 'mcid,mcategory', $where);
        $old_m_arr          = [];
        if ($data) {
            foreach ($data as &$mone) {
                $old_m_arr[] = $mone['mcategory'];
            }
        }
        $add = array_diff($categorysArr, $old_m_arr);
        if ($add) {
            $addAll = [];
            foreach ($add as $key => $valye) {
                $addAll[$key]['mcategory'] = $valye;
            }
            $add = $this->insertDataALL('metering_categorys', $addAll);
            if ($add) {
                $categorys        = $this->DB_get_all('metering_categorys');
                $result['status'] = 1;
                $result['msg']    = '添加成功';
                $result['result'] = $categorys;
            } else {
                $result['status'] = -1;
                $result['msg']    = '添加失败';
            }
            return $result;
        } else {
            die(json_encode(['status' => -1, 'msg' => '分类已存在,请勿重复添加']));
        }

    }

    //保存计划
    public function addMeteringPlan()
    {
        $assetsName = I('POST.assetsName');
        $categorys  = I('POST.categorys');
        $cycle      = I('POST.cycle');
        $departid   = I('POST.departid');
        $factory    = I('POST.factory');
        $model      = I('POST.model');
        $next_date  = I('POST.next_date');
        $remark     = I('POST.remark');
        $remind_day = I('POST.remind_day');
        $respo_user = I('POST.respo_user');
        $status     = I('POST.status');
        $test_way   = I('POST.test_way');
        $unit       = I('POST.unit');
        $addStyle   = I('POST.addStyle');
        $count      = I('POST.count');
        $adddate    = getHandleTime(time());
        if ($departid < 0) {
            die(json_encode(['status' => -1, 'msg' => '请选择科室']));
        }
        if (!$assetsName) {
            die(json_encode(['status' => -1, 'msg' => '请选择设备']));
        }
        if ($categorys <= 0) {
            die(json_encode(['status' => -1, 'msg' => '请选择计量分类']));
        }
        if ((int)$remind_day <= 0) {
            die(json_encode(['status' => -1, 'msg' => '请补充正确的提前提醒天数']));
        }
        if (!$next_date or time() > strtotime($next_date)) {
            die(json_encode(['status' => -1, 'msg' => '请补充正确的下次待检日期,需大于今日']));
        }
        //查询设备所在医院ID
        $hosid                  = $this->DB_get_one('department', 'departid,hospital_id', ['departid' => $departid]);
        $planWhere['adddate'][] = ['EQ', $adddate];
        $plan                   = $this->DB_get_one('metering_plan', 'plan_num', $planWhere, 'plan_num DESC');
        $start                  = 1;
        if ($plan) {
            $start = substr($plan['plan_num'], 11) + 1;
        }
        $plan_num = 'SW-' . date('Ymd', time()) . $start;
        $addAll   = [];

        if ($addStyle == 1) {
            //设备是已存info表的
            $selectPage             = I('POST.selectPage');
            $selectPageArr          = explode(',', $selectPage);
            $infoWhere['serialnum'] = ['IN', $selectPageArr];
            $Arr                    = $this->DB_get_all('assets_info', 'assid,assnum,hospital_id,serialnum',
                $infoWhere);
            if (!$Arr) {
                $Arr = $this->DB_get_all('assets_info', 'assid,assnum,hospital_id,serialnum',
                    ['assnum' => ['in', $selectPageArr]]);
            }
            $ArrCount = count($Arr);
            if ($ArrCount > $count) {
                die(json_encode(['status' => -1, 'msg' => '设备数量有误']));
            }
            $addAll = $this->SplicingAddAllPlanArr($ArrCount, $count, $Arr, $plan_num);
        } else {
            //无序列号
            for ($i = 0; $i < $count; $i++) {
                $addAll[$i]['plan_num']    = $plan_num;
                $addAll[$i]['assets']      = $assetsName;
                $addAll[$i]['assid']       = '';
                $addAll[$i]['hospital_id'] = $hosid['hospital_id'];
                $addAll[$i]['asset_count'] = 1;
                $addAll[$i]['model']       = $model;
                $addAll[$i]['unit']        = $unit;
                $addAll[$i]['factory']     = $factory;
                $addAll[$i]['productid']   = '';
                $addAll[$i]['departid']    = $departid;
                $addAll[$i]['mcid']        = $categorys;
                $addAll[$i]['cycle']       = $cycle;
                $addAll[$i]['test_way']    = $test_way;
                $addAll[$i]['next_date']   = $next_date;
                $addAll[$i]['respo_user']  = $respo_user;
                $addAll[$i]['remind_day']  = $remind_day;
                $addAll[$i]['status']      = $status;
                $addAll[$i]['remark']      = $remark;
                $addAll[$i]['adduseid']    = session('userid');
                $addAll[$i]['adddate']     = getHandleTime(time());
                $addAll[$i]['addtime']     = getHandleDate(time());
            }
        }
        $addAll = $this->insertDataALL('metering_plan', $addAll);
        $pmid   = '';
        if ($addAll) {
            $plan       = $this->DB_get_all('metering_plan', 'mpid', ['plan_num' => $plan_num]);
            $resultData = [];
            foreach ($plan as $key => $value) {
                $resultData[$key]['mpid']       = $value['mpid'];
                $resultData[$key]['report_num'] = '';
                $pmid                           .= ',' . $value['mpid'];
                $resultData[$key]['this_date']  = $next_date;
            }
            $this->insertDataALL('metering_result', $resultData);

            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($settingData) {
                //有开启短信
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $data['assets']     = $assetsName;
                $data['cycle']      = $cycle;
                $data['next_date']  = $next_date;
                $data['plan_num']   = $plan_num;
                $data['department'] = $departname[$departid]['department'];
                $ToolMod            = new ToolController();
                $UserData           = $ToolMod->getUser('setMeteringResult', $departid);
                //通知工程师实施
                if ($settingData['setMeteringResult']['status'] == C('OPEN_STATUS') && $UserData) {
                    //通知被借科室准备设备 开启
                    $phone = $this->formatPhone($UserData);
                    $sms   = $this->formatSmsContent($settingData['setMeteringResult']['content'], $data);
                    $ToolMod->sendingSMS($phone, $sms, $this->Controller, trim($pmid, ','));
                }
            }
            //==========================================短信 END============================================

            if (C('USE_FEISHU') === 1) {
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**计划编号：**' . $plan_num;
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**计划类型：**计量计划';
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '**计划状态：**已启用';
                $feishu_fields[]       = $fd;

                $fd['is_short']        = false;//是否并排布局
                $fd['text']['tag']     = 'lark_md';
                $fd['text']['content'] = '请及时执行';
                $feishu_fields[]       = $fd;

                $card_data['config']['enable_forward']   = false;//是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag']         = 'div';
                $card_data['elements'][0]['fields']      = $feishu_fields;
                $card_data['header']['template']         = 'red';
                $card_data['header']['title']['content'] = '计量计划启用提醒';
                $card_data['header']['title']['tag']     = 'plain_text';

                $toUser = $this->getToUser(0, $departid, 'Metering', 'Metering', 'setMeteringResult');
                foreach ($toUser as $k => $v) {
                    $this->send_feishu_card_msg($v['openid'], $card_data);
                }
                //==========================================飞书 END==========================================
            } else {
                //==========================================微信公众号 START==========================================
                $moduleModel = new ModuleModel();
                $wx_status   = $moduleModel->decide_wx_login();
                if ($wx_status) {
                    /** @var UserModel[] $users */
                    $users = $this->getToUser(0, $departid, 'Metering', 'Metering', 'setMeteringResult');
                    $openIds = array_column($users, 'openid');
                    $openIds = array_filter($openIds);
                    $openIds = array_unique($openIds);

                    $messageData = [
                        'thing10' => '计量计划',// 工单类型
                        'thing9'  => '新计量计划',// 工单名称
                        'time39'  => $adddate,// 发起时间
                        'thing7'  => session('username'),// 发起人员
                        'const56' => '已启用',// 工单阶段
                    ];

                    foreach ($openIds as $openId) {
                        Weixin::instance()->sendMessage($openId, '工单处理提醒', $messageData);
                    }
                }
                //==========================================微信公众号 END============================================
            }
            return ['status' => 1, 'msg' => '成功制定'];

        } else {
            return ['status' => -1, 'msg' => '制定失败'];
        }
    }

    //上传图片
    public function uploadfile()
    {
        if ($_FILES['file']) {
            $Tool    = new ToolController();
            $style   = [
                'JPG',
                'PNG',
                'JPEG',
                'PDF',
                'BMP',
                'DOC',
                'DOCX',
                'jpg',
                'png',
                'jpeg',
                'pdf',
                'bmp',
                'doc',
                'docx',
            ];
            $dirName = C('UPLOAD_DIR_METERING_NAME');
            $info    = $Tool->upFile($style, $dirName);
            if ($info['status'] == C('YES_STATUS')) {
                // 上传成功 获取上传文件信息
                $resule['status']   = 1;
                $resule['msg']      = '上传成功';
                $resule['path']     = $info['src'];
                $resule['name']     = $info['name'];
                $resule['formerly'] = $info['formerly'];
            } else {
                // 上传错误提示错误信息
                $resule['status'] = -1;
                $resule['msg']    = $info['msg'];
            }
            return $resule;
        }
    }

    /**
     * 记录上传计量文件信息
     *
     * @param $mrid int 维保ID
     */
    public function addMeteringeFile($mrid)
    {
        if (!$mrid) {
            die(json_encode(['status' => -999, 'msg' => '非法操作']));
        }
        $fileName = I('POST.fileName');
        $formerly = I('POST.formerly');
        if ($fileName) {
            $fileName = explode('|', rtrim($fileName, '|'));
            $formerly = explode('|', rtrim($formerly, '|'));
            $data     = [];
            foreach ($fileName as $key => $value) {
                $data[$key]['mrid']      = $mrid;
                $data[$key]['name']      = $formerly[$key];
                $data[$key]['url']       = $fileName[$key];
                $data[$key]['adduserid'] = session('userid');
                $data[$key]['adddate']   = getHandleTime(time());
            }
            $this->insertDataALL('metering_result_reports', $data);
        }
    }

    /**
     * 生成批量添加计划的数据
     *
     * @param $ArrCount int 序列号数量
     * @param $count    int 用户输入的设备数量
     * @param $arr      array 设备assid与序列号数组
     * @param $plan_num string 计划名称
     *
     * @return array
     * */
    public function SplicingAddAllPlanArr($ArrCount, $count, $arr, $plan_num)
    {
        $assetsName = I('POST.assetsName');
        $categorys  = I('POST.categorys');
        $cycle      = I('POST.cycle');
        $departid   = I('POST.departid');
        $factory    = I('POST.factory');
        $model      = I('POST.model');
        $next_date  = I('POST.next_date');
        $remark     = I('POST.remark');
        $remind_day = I('POST.remind_day');
        $respo_user = I('POST.respo_user');
        $status     = I('POST.status');
        $test_way   = I('POST.test_way');
        $unit       = I('POST.unit');
        $addAll     = [];
        $hosid      = 0;
        foreach ($arr as $key => $value) {
            $addAll[$key]['plan_num']    = $plan_num;
            $addAll[$key]['assets']      = $assetsName;
            $addAll[$key]['assid']       = $value['assid'];
            $hosid                       = $value['hospital_id'];
            $addAll[$key]['hospital_id'] = $hosid;
            $addAll[$key]['asset_count'] = 1;
            $addAll[$key]['model']       = $model;
            $addAll[$key]['unit']        = $unit;
            $addAll[$key]['factory']     = $factory;
            if (!$value['serialnum']) {
                $addAll[$key]['productid'] = $value['assnum'];
            } else {
                $addAll[$key]['productid'] = $value['serialnum'];
            }
            $addAll[$key]['departid']   = $departid;
            $addAll[$key]['mcid']       = $categorys;
            $addAll[$key]['cycle']      = $cycle;
            $addAll[$key]['test_way']   = $test_way;
            $addAll[$key]['next_date']  = $next_date;
            $addAll[$key]['respo_user'] = $respo_user;
            $addAll[$key]['remind_day'] = $remind_day;
            $addAll[$key]['status']     = $status;
            $addAll[$key]['remark']     = $remark;
            $addAll[$key]['adduseid']   = session('userid');
            $addAll[$key]['adddate']    = getHandleTime(time());
            $addAll[$key]['addtime']    = getHandleDate(time());
        }
        $asset_count = $count - $ArrCount;
        if ($asset_count > 0) {
            for ($i = $ArrCount; $i < $count; $i++) {
                $addAll[$i]['plan_num']    = $plan_num;
                $addAll[$i]['assets']      = $assetsName;
                $addAll[$i]['assid']       = '';
                $addAll[$i]['hospital_id'] = $hosid;
                $addAll[$i]['asset_count'] = 1;
                $addAll[$i]['model']       = $model;
                $addAll[$i]['unit']        = $unit;
                $addAll[$i]['factory']     = $factory;
                $addAll[$i]['productid']   = '';
                $addAll[$i]['departid']    = $departid;
                $addAll[$i]['mcid']        = $categorys;
                $addAll[$i]['cycle']       = $cycle;
                $addAll[$i]['test_way']    = $test_way;
                $addAll[$i]['next_date']   = $next_date;
                $addAll[$i]['respo_user']  = $respo_user;
                $addAll[$i]['remind_day']  = $remind_day;
                $addAll[$i]['status']      = $status;
                $addAll[$i]['remark']      = $remark;
                $addAll[$i]['adduseid']    = session('userid');
                $addAll[$i]['adddate']     = getHandleTime(time());
                $addAll[$i]['addtime']     = getHandleDate(time());
            }
        }
        return $addAll;
    }


    /**
     * Notes: 明细量入库方法
     *
     * @param $saveTempidArr array 要保存的临时表设备ID
     *
     * @return array
     */
    public function benefitStorage($saveTempidArr)
    {
        $where['tempid']  = ['IN', $saveTempidArr];
        $where['is_save'] = ['EQ', C('NO_STATUS')];
        $upload_temp_data = $this->DB_get_all('metering_plan_upload_temp', '', $where);
        if (!$upload_temp_data) {
            die(json_encode(['status' => -999, 'msg' => '数据异常']));
        }
        $newData        = [];
        $key            = 0;
        $checkMrid      = [];
        $checkProductid = [];
        //获取有检测权限的所有工程师
        //获取所以计量分类
        $metering_categorys = $this->DB_get_all('metering_categorys', 'mcid,mcategory');
        //获取所有未删除的计量计划
        $planWhere['status']    = ['NEQ', -1];
        $planWhere['productid'] = ['NEQ', ''];
        $plan                   = $this->DB_get_all('metering_plan', 'productid', $planWhere);

        foreach ($metering_categorys as &$mcone) {
            $checkMrid[$mcone['mcid']] = $mcone['mcategory'];
        }
        foreach ($plan as &$one) {
            $checkProductid[$one['productid']] = $one['productid'];
        }
        $adddate                     = date("Y-m-d", time());
        $planStartWhere['adddate'][] = ['EQ', $adddate];
        $planStart                   = $this->DB_get_one('metering_plan', 'plan_num', $planStartWhere, 'plan_num DESC');
        $start                       = 1;
        if ($planStart) {
            $start = substr($planStart['plan_num'], 11) + 1;
        }
        $plan_num_Arr = [];
        foreach ($upload_temp_data as &$data) {
            foreach ($data as $k => $v) {
                if ($k == 'productid') {
                    //验证序列号是否合法
                    if ($data[$k] != '') {
                        if ($checkProductid[$data[$k]]) {
                            die(json_encode([
                                'status' => -999,
                                'msg'    => $data['assets'] . ' 该序列号为 :' . $data[$k] . '的设备已存在',
                            ]));
                        }
                    }
                } elseif ($k == 'mcid') {
                    //验证计量分类是否合法\
                    if (!$checkMrid[$data[$k]]) {
                        die(json_encode([
                            'status' => -999,
                            'msg'    => $data['assets'] . ' 计量分类 :' . $data['mcategory'] . ' 不合法',
                        ]));
                    }
                } elseif ($k == 'next_date') {
                    //验证下次待检时间是否合法
                    if (!$data[$k] or time() > strtotime($data[$k])) {
                        die(json_encode([
                            'status' => -999,
                            'msg'    => $data['assets'] . ' 下次待检时间 :' . $data[$k] . ' 不合法',
                        ]));
                    }
                } elseif ($k == 'test_way') {
                    if ($data[$k] != '院内' && $data[$k] != '院外') {
                        die(json_encode([
                            'status' => -999,
                            'msg'    => $data['assets'] . ' 检定方式 :' . $data[$k] . ' 不合法',
                        ]));
                    }
                } elseif ($k == 'status') {
                    if ($data[$k] != '启用' && $data[$k] != '暂停') {
                        die(json_encode([
                            'status' => -999,
                            'msg'    => $data['assets'] . ' 计划状态 :' . $data[$k] . ' 不合法',
                        ]));
                    }
                }
            }
            if (!$plan_num_Arr) {
                $plan_num_Arr[$data['assets']] = 'SW-' . date('Ymd', time()) . $start;
                $start++;
            } else {
                if (!$plan_num_Arr[$data['assets']]) {
                    $plan_num_Arr[$data['assets']] = 'SW-' . date('Ymd', time()) . $start;
                    $start++;
                }
            }
        }
        foreach ($upload_temp_data as &$one) {
            $newData[$key]['plan_num']    = $plan_num_Arr[$one['assets']];
            $newData[$key]['assid']       = $one['assid'];
            $newData[$key]['assets']      = $one['assets'];
            $newData[$key]['model']       = $one['model'];
            $newData[$key]['unit']        = $one['unit'];
            $newData[$key]['factory']     = $one['factory'];
            $newData[$key]['productid']   = $one['productid'];
            $newData[$key]['departid']    = $one['departid'];
            $newData[$key]['asset_count'] = 1;
            $newData[$key]['mcid']        = $one['mcid'];
            $newData[$key]['cycle']       = $one['cycle'];
            $newData[$key]['test_way']    = $one['work_day'] == '院内' ? 1 : 0;
            $newData[$key]['next_date']   = $one['next_date'];
            $newData[$key]['respo_user']  = $one['respo_user'];
            $newData[$key]['remind_day']  = $one['remind_day'];
            $newData[$key]['status']      = $one['status'] == '启用' ? 1 : 0;
            $newData[$key]['remark']      = $one['remark'];
            $newData[$key]['adddate']     = getHandleTime(time());
            $newData[$key]['adduseid']    = session('userid');
            $key++;
        }
        $add = $this->insertDataALL('metering_plan', $newData);
        if ($add) {
            $saveTemp['is_save'] = C('YES_STATUS');
            $this->updateData('metering_plan_upload_temp', $saveTemp, $where);
        } else {
            die(json_encode(['status' => -999, 'msg' => '批量录入失败']));
        }
    }

    //格式化短信内容
    public function formatSmsContent($content, $data)
    {
        $content = str_replace("{plan_num}", $data['plan_num'], $content);
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{department}", $data['department'], $content);
        $content = str_replace("{next_date}", $data['next_date'], $content);
        $content = str_replace("{cycle}", $data['cycle'] . '天', $content);

        return $content;
    }

    //记录列表
    public function getMeteringHistory()
    {
        $departid          = I('POST.departid');
        $assetsName        = I('POST.assetsName');
        $assnum            = I('POST.assnum');
        $productid         = I('POST.productid');
        $categorys         = I('POST.categorys');
        $hospital_id       = I('POST.hospital_id');
        $limit             = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page              = I('post.page') ? I('post.page') : 1;
        $offset            = ($page - 1) * $limit;
        $where['A.status'] = ['NEQ', -1];
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($departid) {
            $where['A.departid'] = ['IN', $departid];
        } else {
            $where['A.departid'] = ['IN', session('departid')];
        }
        if ($assetsName) {
            $where['A.assets'] = ['LIKE', '%' . $assetsName . '%'];
        }
        if ($assnum) {
            $where['B.assnum'] = ['LIKE', '%' . $assnum . '%'];
        }
        if ($productid) {
            $where['A.productid'] = ['LIKE', '%' . $productid . '%'];
        }
        if ($categorys) {
            $where['A.mcid'][] = ['EQ', $categorys];
        }
        $join[0] = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $join[1] = 'LEFT JOIN sb_metering_categorys AS C ON A.mcid=C.mcid';
        $fields  = 'A.mpid,A.plan_num,A.assid,A.assets,A.model,A.productid,A.departid,A.cycle,A.mcid,A.test_way,A.status as mp_status,B.assnum,C.mcategory,(SELECT COUNT(*) FROM sb_metering_result AS B WHERE A.mpid = B.mpid AND B.status = 1) AS times';
        $total   = $this->DB_get_count_join('metering_plan', 'A', $join, $where);
        $list    = $this->DB_get_all_join('metering_plan', 'A', $fields, $join, $where, '', 'mpid asc',
            $offset . "," . $limit);
        if (!$list) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($list as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            $one['operation']  = '';
            if ($one['test_way'] == 1) {
                $one['test_way'] = '院内';
            } else {
                $one['test_way'] = '院外';
            }
            $one['operation'] .= $this->returnListLink('详情', '', 'showMeteringHistory',
                C('BTN_CURRENCY') . ' layui-btn-primary');
        }
        $result["total"]  = $total;
        $result["offset"] = $offset;
        $result["limit"]  = $limit;
        $result["code"]   = 200;
        $result["rows"]   = $list;
        return $result;
    }

    //获取待入记录临时信息
    public function getWatingUploadResult()
    {
        $limit               = I('post.limit') ? I('post.limit') : 10;
        $page                = I('post.page') ? I('post.page') : 1;
        $offset              = ($page - 1) * $limit;
        $where['add_userid'] = session('userid');
        $where['is_save']    = C('NO_STATUS');//获取未上传的数据
        $total               = $this->DB_get_count('metering_result_upload_temp', $where);
        //查询上次未完成保存的数据
        $data = $this->DB_get_all('metering_result_upload_temp', '*', $where, '', 'assets asc,this_date asc',
            $offset . ',' . $limit);
        if (!$data) {
            $result['msg']  = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $assetsWhere['is_delete']   = 0;
        $assetsWhere['hospital_id'] = session('current_hospitalid');
        $Assets                     = new AssetsInfoModel();
        $assnums                    = $Assets->where($assetsWhere)->getField('assid,assnum');
        //获取计量分类
        $mCategorys = $this->DB_get_all('metering_categorys', 'mcategory');
        $cates      = [];
        foreach ($mCategorys as $v) {
            $cates[] = $v['mcategory'];
        }
        //判断待上传数据是否合法
        foreach ($data as $k => $v) {
            $data[$k]['test_way'] = $v['test_way'] == 1 ? '院内' : ($v['test_way'] == 0 ? '院外' : '<span class="rquireCoin">未知</span>');
            $data[$k]['result']   = $v['result'] == 1 ? '合格' : ($v['result'] == 0 ? '不合格' : '<span class="rquireCoin">未知</span>');
            if (!in_array($v['assnum'], $assnums)) {
                $data[$k]['assnum'] = '<span class="rquireCoin">' . $v['assnum'] . '[不存在]</span>';
            }
            if (!in_array($v['cate'], $cates)) {
                $data[$k]['cate'] = '<span class="rquireCoin">' . $v['cate'] . '</span>';
            }
            $file_name = [];
            if ($v['file_name']) {
                $files = explode('|', $v['file_name']);
                foreach ($files as $fv) {
                    if (strpos($fv, '/Public/uploads/') !== false) {
                        $f           = explode('/', $fv);
                        $file_name[] = '<a class="titlecolor" href="javascript:void(0)" lay-event="showFile" data-path="' . $fv . '" data-name="' . $f[count($f) - 1] . '">' . $f[count($f) - 1] . '</a>';
                    } else {
                        $file_name[] = '<span class="rquireCoin">' . $fv . '</span>';
                    }
                }
            }
            $file_name             = implode($file_name, '，');
            $data[$k]['file_name'] = $file_name;
            $data[$k]['operation'] = $this->returnListLink('删除',
                $this->full_open_url($this->MODULE, $this->Controller) . 'batchSetMeteringResult', 'delResult',
                C('BTN_CURRENCY') . ' layui-btn-danger');
        }
        $result['limit']  = (int)$limit;
        $result['offset'] = $offset;
        $result['total']  = (int)$total;
        $result['rows']   = $data;
        $result['code']   = 200;
        return $result;
    }

    //导出记录模板
    public function exploreMeteringResultModel()
    {
        //导出模板
        $xlsName = "计量记录模板";
        $xlsCell = [
            '设备名称',
            '设备编码',
            '计量分类',
            '检定方式(院内/院外)',
            '检定日期(例:2021/2/18)',
            '检定结果(合格/不合格)',
            '证书编号',
            '检定机构',
            '计量费用',
            '检定人',
            '审核人',
            '检定备注',
            '计量附件名称，多个请用"|"隔开(例：abc.jpg|def.jpg)',
        ];
        //单元格宽度设置
        $width = [
            '设备名称'                                         => 20,
            '设备编码'                                         => 20,
            '计量分类'                                         => 25,
            '检定方式(院内/院外)'                              => 20,
            '检定日期(例:2021/2/18)'                           => 28,
            '检定结果(合格/不合格)'                            => 28,
            '证书编号'                                         => 20,
            '检定机构'                                         => 35,
            '计量费用'                                         => 15,
            '检定人'                                           => 15,
            '审核人'                                           => 15,
            '检定备注'                                         => 25,
            '计量附件名称，多个请用"|"隔开(例：abc.jpg|def.jpg)' => 55,

        ];
        //单元格颜色设置（例如必填行单元格字体颜色为红色）
        $color = [
            '设备编码'               => 'FF0000',//颜色代码
            '检定日期(例:2021/2/18)' => 'FF0000',//颜色代码
            '检定结果(合格/不合格)'  => 'FF0000',//颜色代码
            '证书编号'               => 'FF0000',//颜色代码
        ];
        Excel('计量记录模板', $xlsName, $xlsCell, $width, $color);
    }

    //上传计量记录文件
    public function uploadResultData()
    {
        if (empty($_FILES)) {
            return ['status' => -1, 'msg' => '请上传文件'];
        }
        $uploadConfig = [
            'maxSize'  => 3145728,
            'rootPath' => './Public/',
            'savePath' => 'uploads/',
            'saveName' => ['uniqid', ''],
            'exts'     => ['xlsx', 'xls', 'xlsm'],
            'autoSub'  => true,
            'subName'  => ['date', 'Ymd'],
        ];
        $upload       = new \Think\Upload($uploadConfig);
        $info         = $upload->upload();
        if (!$info) {
            return ['status' => -1, 'msg' => '导入数据出错'];
        }
        vendor("PHPExcel.PHPExcel");
        $filePath = $upload->rootPath . $info['file']['savepath'] . $info['file']['savename'];
        if (empty($filePath) or !file_exists($filePath)) {
            die('file not exists');
        }
        $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                return ['status' => -1, 'msg' => '文件格式错误'];
            }
        }
        $excelDate    = new \PHPExcel_Shared_Date();
        $PHPExcel     = $PHPReader->load($filePath);        //建立excel对象
        $currentSheet = $PHPExcel->getSheet(0);        //**读取excel文件中的指定工作表*/
        $allColumn    = $currentSheet->getHighestColumn();        //**取得最大的列号*/
        ++$allColumn;
        $allRow   = $currentSheet->getHighestRow();        //**取得一共有多少行*/
        $data     = [];
        $cellname = [
            'A' => 'assets',
            'B' => 'assnum',
            'C' => 'cate',
            'D' => 'test_way',
            'E' => 'this_date',
            'F' => 'result',
            'G' => 'report_num',
            'H' => 'company',
            'I' => 'money',
            'J' => 'test_person',
            'K' => 'auditor',
            'L' => 'remark',
            'M' => 'file_name',
        ];
        //需要进行日期处理的保存在一个数组
        $toDate = [
            'this_date',
        ];

        for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for ($colIndex = 'A'; $colIndex != $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                if (in_array($cellname[$colIndex], $toDate)) {
                    if (!$currentSheet->getCell($addr)->getValue()) {
                        $cell = '';
                    } else {
                        $cell = gmdate("Y-m-d", $excelDate::ExcelToPHP($currentSheet->getCell($addr)->getValue()));
                    }
                } else {
                    $cell = $currentSheet->getCell($addr)->getValue();
                }
                if ($cell instanceof \PHPExcel_RichText) { //富文本转换字符串
                    $cell = $cell->__toString();
                }
                if ($cellname[$colIndex] == 'assnum') {
                    if (!$cell) {
                        break;
                    }
                }
                if ($cellname[$colIndex] == 'this_date') {
                    if (!$cell) {
                        break;
                    }
                }
                if ($cellname[$colIndex] == 'report_num') {
                    if (!$cell) {
                        break;
                    }
                }
                if ($cellname[$colIndex] == 'result') {
                    if (!$cell) {
                        break;
                    }
                }
                if ($cellname[$colIndex] == 'test_way') {
                    switch ($cell) {
                        case '院内':
                            $cell = 1;
                            break;
                        case '院外':
                            $cell = 0;
                            break;
                        default:
                            $cell = 0;
                            break;
                    }
                }
                if ($cellname[$colIndex] == 'result') {
                    switch ($cell) {
                        case '合格':
                            $cell = 1;
                            break;
                        case '不合格':
                            $cell = 0;
                            break;
                        default:
                            $cell = 0;
                            break;
                    }
                }
                $data[$rowIndex - 2][$cellname[$colIndex]] = trim($cell) ? trim($cell) : '';
            }
        }
        if (!$data) {
            return ['status' => -1, 'msg' => '导入数据失败'];
        }
        $assnums = [];
        foreach ($data as $v) {
            $assnums[] = trim($v['assnum']);
        }
        $Assets     = new AssetsInfoModel();
        $assetsInfo = $Assets->alias('A')->join('LEFT JOIN sb_assets_factory as B ON A.afid = B.afid')->where([
            'A.assnum' => [
                'in',
                $assnums,
            ],
        ])->getField('A.assnum,A.hospital_id,A.departid,A.assid,A.model,A.serialnum,A.unit,B.factory');
        foreach ($data as &$one) {
            $one['assid']       = $assetsInfo[$one['assnum']] ? $assetsInfo[$one['assnum']]['assid'] : 0;
            $one['hospital_id'] = $assetsInfo[$one['assnum']] ? $assetsInfo[$one['assnum']]['hospital_id'] : 0;
            $one['departid']    = $assetsInfo[$one['assnum']] ? $assetsInfo[$one['assnum']]['departid'] : 0;
            $one['model']       = $assetsInfo[$one['assnum']] ? $assetsInfo[$one['assnum']]['model'] : '';
            $one['unit']        = $assetsInfo[$one['assnum']] ? $assetsInfo[$one['assnum']]['unit'] : '';
            $one['factory']     = $assetsInfo[$one['assnum']] ? $assetsInfo[$one['assnum']]['factory'] : '';
            $one['productid']   = $assetsInfo[$one['assnum']] ? $assetsInfo[$one['assnum']]['serialnum'] : '';
        }
        //查询是否重复上传
        $PlanWhere['assnum'] = ['in', $assnums];
        $PlanData            = $this->DB_get_all('metering_result_upload_temp', 'assnum,this_date', $PlanWhere);
        $PlanRes             = [];

        foreach ($PlanData as $PlanValue) {
            //将已存在的明细记录组合成数组
            $PlanRes[$PlanValue['assnum']][] = $PlanValue['this_date'];
        }
        foreach ($data as $k => $v) {
            if ($PlanRes[$v['assnum']] && in_array($v['this_date'], $PlanRes[$v['assnum']])) {
                //将已存在计划表的数据删除.避免上传重复的数据
                unset($data[$k]);
            }
        }
        if (!$data) {
            //上传的文件数据和临时表中已存在数据重复
            return ['status' => -1, 'msg' => '该文件数据已上传，请勿重复上传！'];
        }
        //保存数据到临时表
        $insertData = [];
        $num        = 0;
        foreach ($data as $k => $v) {
            if ($num < $this->len) {
                //$this->len条存一次数据到数据库
                //如果编号存在就记录，避免记录错误的数据
                $insertData[$num]['add_userid'] = session('userid');
                $insertData[$num]['add_time']   = date('Y-m-d H:i:s');
                $insertData[$num]['is_save']    = 0;
                foreach ($v as $k1 => $v1) {
                    $insertData[$num][$k1] = $v1;
                }
                //避免用户不小心修改设备信息，重新在设备表获取
                $num++;

            }
            if ($num == $this->len) {
                //插入数据
                $this->insertDataALL('metering_result_upload_temp', $insertData);
                //重置数据
                $num        = 0;
                $insertData = [];
            }
        }
        if ($insertData) {
            $this->insertDataALL('metering_result_upload_temp', $insertData);
        }
        return ['status' => 1, 'msg' => '上传数据成功，请核对后再保存！'];
    }

    //更新临时表数据
    public function updateResultTempData()
    {
        $temp_id = I('POST.temp_id');
        unset($_POST['temp_id']);
        unset($_POST['type']);
        foreach ($_POST as $k => $v) {
            $data[$k] = htmlspecialchars(addslashes(trim($v)));
            switch ($k) {
                case 'test_way':
                    if ($data[$k] != '院内' && $data[$k] != '院外') {
                        return ['status' => -1, 'msg' => '请输如正确的检定方式'];
                    } else {
                        $data[$k] = $data[$k] == '院内' ? 1 : 0;
                    }
                    break;
                case 'result':
                    if ($data[$k] != '合格' && $data[$k] != '不合格') {
                        return ['status' => -1, 'msg' => '请输如正确的检定结果'];
                    } else {
                        $data[$k] = $data[$k] == '合格' ? 1 : 0;
                    }
                    break;
                case 'mcid':
                    //验证计量分类是否合法
                    $find = $this->DB_get_one('metering_categorys', 'mcid,mcategory', ['mcid' => $data[$k]]);
                    if ($find['mcid']) {
                        $data[$k]     = $find['mcid'];
                        $data['cate'] = $find['mcategory'];
                    } else {
                        return ['status' => -1, 'msg' => '查找不到 ' . $data[$k] . ' 的分类！！'];
                    }
                    break;
            }
        }
        $data['edit_userid'] = session('userid');
        $data['edit_time']   = date('Y-m-d H:i:s');
        $res                 = $this->updateData('metering_result_upload_temp', $data, ['temp_id' => $temp_id]);
        if ($res) {
            return ['status' => 1, 'msg' => '修改成功！'];
        } else {
            return ['status' => -1, 'msg' => '修改失败！'];
        }
    }

    //删除临时表数据
    public function delResult()
    {
        $tempid  = trim(I('POST.temp_id'), ',');
        $tempArr = explode(',', $tempid);
        $res     = $this->deleteData('metering_result_upload_temp', ['temp_id' => ['in', $tempArr]]);
        if ($res) {
            return ['status' => 1, 'msg' => '删除成功！'];
        } else {
            return ['status' => -1, 'msg' => '删除失败！'];
        }
    }

    //保存计量记录临时表数据
    public function batchTempMeteringResult()
    {
        $temp_id       = trim(I('POST.temp_id'), ',');
        $tempArr       = explode(',', $temp_id);
        $num           = 0;
        $saveTempidArr = [];
        foreach ($tempArr as $k => $v) {
            //按每次最多不超过$this->len条的数据获取临时表数据进行保存操作
            if ($num < $this->len) {
                $saveTempidArr[] = $v;
                $num++;
            }
            if ($num == $this->len) {
                //进行一次入库操作
                $this->saveResultTemp($saveTempidArr);
                //重置
                $num           = 0;
                $saveTempidArr = [];
            }
        }
        if ($saveTempidArr) {
            $this->saveResultTemp($saveTempidArr);
        }
        return ['status' => 1, 'msg' => '保存数据成功！'];
    }

    /**
     * Notes: 保存计量记录明细到正式表
     *
     * @param $saveTempidArr array 要保存的临时表设备ID
     *
     * @return array
     */
    public function saveResultTemp($saveTempidArr)
    {
        $where['temp_id'] = ['IN', $saveTempidArr];
        $where['is_save'] = ['EQ', C('NO_STATUS')];
        $upload_temp_data = $this->DB_get_all('metering_result_upload_temp', '', $where, '', 'this_date asc');
        if (!$upload_temp_data) {
            die(json_encode(['status' => -999, 'msg' => '数据异常']));
        }
        //获取所以计量分类
        $metering_categorys = $this->DB_get_all('metering_categorys', 'mcid,mcategory');
        $cates              = [];
        foreach ($metering_categorys as &$mcone) {
            $cates[$mcone['mcategory']] = $mcone['mcid'];
        }
        //归类管理
        $data = $assids = [];
        foreach ($upload_temp_data as $v) {
            if (!in_array($v['assid'], $assids)) {
                $assids[]                  = $v['assid'];
                $key                       = count($assids) - 1;
                $data[$key]['assid']       = $v['assid'];
                $data[$key]['hospital_id'] = $v['hospital_id'];
                $data[$key]['assets']      = $v['assets'];
                $data[$key]['model']       = $v['model'];
                $data[$key]['unit']        = $v['unit'];
                $data[$key]['factory']     = $v['factory'];
                $data[$key]['productid']   = $v['productid'];
                $data[$key]['departid']    = $v['departid'];
                $data[$key]['mcid']        = $cates[$v['cate']];
                $data[$key]['test_way']    = $v['test_way'];
                $data[$key]['next_date']   = $v['this_date'];
            }
        }
        //开启事务
        M()->startTrans();
        foreach ($data as $v) {
            $w['status']   = ['neq', -1];
            $w['assid']    = $v['assid'];
            $w['plan_num'] = ['LIKE', '%IM-%'];
            $exists        = $this->DB_get_one('metering_plan', '*', $w);
            if (!$exists) {
                //不存在计划，新建一个计划
                //$adddate = date('Y-m-d',strtotime("-1 day",strtotime($v['next_date'])));
                $planWhere['adddate'][]  = ['EQ', date('Y-m-d')];
                $planWhere['plan_num'][] = ['LIKE', '%IM-%'];
                $plan                    = $this->DB_get_one('metering_plan', 'plan_num', $planWhere, 'plan_num DESC');
                $start                   = 1;
                if ($plan) {
                    $start = substr($plan['plan_num'], 11) + 1;
                }
                $plan_num = 'IM-' . date('Ymd', time()) . $start;

                $v['plan_num']    = $plan_num;
                $v['asset_count'] = 1;
                $v['status']      = 0;
                $v['remind_day']  = 1;
                $v['remark']      = '该计划为外部导入计量历史记录而自动生成的计划，计划编号为"IM-"开头，默认没有计量周期，计划状态为暂停且不可修改';
                $v['adduseid']    = session('userid');
                $v['adddate']     = date('Y-m-d');
                $v['addtime']     = date('Y-m-d H:i:s');
                $new_mpid         = $this->insertData('metering_plan', $v);
                if (!$new_mpid) {
                    M()->rollback();
                    die(json_encode(['status' => -999, 'msg' => '添加失败']));
                }
                foreach ($upload_temp_data as $uv) {
                    if ($v['assid'] == $uv['assid']) {
                        $result_data['mpid']        = $new_mpid;
                        $result_data['this_date']   = $uv['this_date'];
                        $result_data['report_num']  = $uv['report_num'];
                        $result_data['result']      = $uv['result'];
                        $result_data['company']     = $uv['company'];
                        $result_data['money']       = $uv['money'];
                        $result_data['test_person'] = $uv['test_person'];
                        $result_data['auditor']     = $uv['auditor'];
                        $result_data['remark']      = $uv['remark'];
                        $result_data['adduserid']   = session('userid');
                        $result_data['adddate']     = date('Y-m-d');
                        $result_data['status']      = 1;
                        $mrid                       = $this->insertData('metering_result', $result_data);
                        if (!$mrid) {
                            M()->rollback();
                        }
                        if ($uv['file_name']) {
                            //保存附件
                            $files = explode('|', $uv['file_name']);
                            foreach ($files as $fv) {
                                $f                   = explode('/', $fv);
                                $report['mrid']      = $mrid;
                                $report['name']      = $f[count($f) - 1];
                                $report['url']       = $fv;
                                $report['adduserid'] = session('userid');
                                $report['adddate']   = date('Y-m-d');
                                $a                   = $this->insertData('metering_result_reports', $report);
                                if (!$a) {
                                    M()->rollback();
                                }
                            }
                        }
                    }
                }
            } else {
                //如已存在，则对比next_date
                if ($exists['next_date'] > $v['next_date']) {
                    $u = $this->updateData('metering_plan', ['next_date' => $v['next_date']],
                        ['mpid' => $exists['mpid']]);
                    if (!$u) {
                        M()->rollback();
                    }
                }
                foreach ($upload_temp_data as $uv) {
                    if ($v['assid'] == $uv['assid']) {
                        $result_data['mpid']        = $exists['mpid'];
                        $result_data['this_date']   = $uv['this_date'];
                        $result_data['report_num']  = $uv['report_num'];
                        $result_data['result']      = $uv['result'];
                        $result_data['company']     = $uv['company'];
                        $result_data['money']       = $uv['money'];
                        $result_data['test_person'] = $uv['test_person'];
                        $result_data['auditor']     = $uv['auditor'];
                        $result_data['remark']      = $uv['remark'];
                        $result_data['adduserid']   = session('userid');
                        $result_data['adddate']     = date('Y-m-d');
                        $result_data['status']      = 1;
                        $mrid                       = $this->insertData('metering_result', $result_data);
                        if (!$mrid) {
                            M()->rollback();
                        }
                        if ($uv['file_name']) {
                            //保存附件
                            $files = explode('|', $uv['file_name']);
                            foreach ($files as $fv) {
                                $f                   = explode('/', $fv);
                                $report['mrid']      = $mrid;
                                $report['name']      = $f[count($f) - 1];
                                $report['url']       = $fv;
                                $report['adduserid'] = session('userid');
                                $report['adddate']   = date('Y-m-d');
                                $a                   = $this->insertData('metering_result_reports', $report);
                                if (!$a) {
                                    M()->rollback();
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach ($upload_temp_data as $v) {
            $this->deleteData('metering_result_upload_temp', ['temp_id' => $v['temp_id']]);
        }
        M()->commit();
    }

    //上传历史记录附件
    public function uploadHistoryFile()
    {
        if ($_FILES['file']) {
            $Tool    = new ToolController();
            $style   = [
                'JPG',
                'PNG',
                'JPEG',
                'PDF',
                'BMP',
                'DOC',
                'DOCX',
                'jpg',
                'png',
                'jpeg',
                'pdf',
                'bmp',
                'doc',
                'docx',
            ];
            $dirName = C('UPLOAD_DIR_METERING_NAME') . '/historyFiles';
            $info    = $Tool->upFile($style, $dirName, false, [], false, false, false);
            if ($info['status'] == C('YES_STATUS')) {
                // 上传成功 更新临时表文件名称
//                $where['file_name'][] = array('like',"%".$info['name']."%");
//                $tempInfo = $this->DB_get_all('metering_result_upload_temp','temp_id,file_name',$where);
//                foreach ($tempInfo as $v){
//                    $update['file_name'] = str_replace($info['name'],$info['src'],$v['file_name']);
//                    $this->updateData('metering_result_upload_temp',$update,['temp_id'=>$v['temp_id']]);
//                }
                $result['status'] = 1;
                $result['msg']    = '上传成功';
            } else {
                // 上传错误提示错误信息
                $result['status'] = -1;
                $result['msg']    = $info['msg'];
            }
            return $result;
        }
    }

    public function matchFiles()
    {
        $where['file_name'] = ['neq', ''];
        $where['is_save']   = 0;
        $data               = $this->DB_get_all('metering_result_upload_temp', 'temp_id,file_name', $where);
        $update_nums        = 0;
        foreach ($data as $k => $v) {
            $old_file_name = $v['file_name'];
            $files         = explode('|', $v['file_name']);
            foreach ($files as $fk => $fv) {
                if (strpos($fv, '/Public/uploads/') === false) {
                    //还没匹配上对应附件的，查询是否已上传该文件
                    if (file_exists('./Public/uploads/' . C('UPLOAD_DIR_METERING_NAME') . '/historyFiles/' . $fv)) {
                        //对应的文件已上传，更新文件名称
                        $files[$fk] = '/Public/uploads/' . C('UPLOAD_DIR_METERING_NAME') . '/historyFiles/' . $fv;
                    }
                }
            }
            $new_file_name = implode('|', $files);
            if ($old_file_name != $new_file_name) {
                $this->updateData('metering_result_upload_temp', ['file_name' => $new_file_name],
                    ['temp_id' => $v['temp_id']]);
                $update_nums++;
            }
        }
        return ['status' => 1, 'msg' => '操作成功，本次匹配成功个数为【' . $update_nums . '】条'];
    }
}
