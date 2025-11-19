<?php

namespace Admin\Model;

use Admin\Controller\Tasks\TasksController;
use Admin\Controller\Tool\ToolController;
use DateTime;
use Think\Db;

class AssetsInfoModel extends CommonModel
{
    protected $len = 30;
    protected $tablePrefix = 'sb_';
    protected $tableName = 'assets_info';
    protected $MODULE = 'Assets';
    protected $Controller = 'Lookup';

    //获取主设备信息
    public function getMainAssetsBasic()
    {
        $this->checkstatus(judgeNum(trim(I('post.main_assid'))), '非法操作');
        $join = 'LEFT JOIN sb_assets_factory AS F ON F.assid=A.assid';
        $fields = 'F.factory,F.ols_facid,F.supplier,F.ols_supid,F.ols_repid,F.repair,A.assetsrespon,A.departid,A.address,A.managedepart';
        $data = $this->DB_get_one_join('assets_info', 'A', $fields, $join,
            ['A.assid' => ['EQ', trim(I('post.main_assid'))]]);
        return ['status' => 1, 'msg' => '获取成功', 'result' => $data];
    }

    /**
     * Notes: 获取设备表所有字段
     *
     * @return array
     */
    public function getDefaultShowFields()
    {
        $fields = [];
        $fields['assnum'] = '设备编码';//不允许批量修改的字段
        $fields['assets'] = '设备名称';
        $fields['assorignum'] = '设备原编码';//不允许批量修改的字段
        $fields['assorignum_spare'] = '设备原编码(备用)';//不允许批量修改的字段
        $fields['catid'] = '设备分类';
        $fields['departid'] = '使用科室';//不允许批量修改的字段
        $fields['assets_level'] = '管理类别';
        $fields['assetsrespon'] = '科室负责人';
        $fields['address'] = '存放地点';
        $fields['managedepart'] = '管理科室';
        $fields['model'] = '规格型号';
        $fields['patrol_xc_cycle'] = '巡查周期(天)';
        $fields['patrol_pm_cycle'] = '保养周期(天)';
        $fields['quality_cycle'] = '质控周期(天)';
        $fields['metering_cycle'] = '计量周期(天)';
        $fields['brand'] = '品牌';
        $fields['is_domestic'] = '是否国产设备';
        $fields['unit'] = '单位';
        $fields['status'] = '设备当前状态';
        $fields['serialnum'] = '序列号';
        $fields['registration'] = '注册证编号';
        $fields['assetsrespon'] = '设备负责人';
        $fields['factorynum'] = '出厂编号';
        $fields['factorydate'] = '出厂日期';
        $fields['opendate'] = '开机日期';
        $fields['storage_date'] = '入库日期';
        $fields['helpcatid'] = '辅助分类';
        $fields['financeid'] = '财务分类';
        $fields['capitalfrom'] = '资金来源';
        $fields['assfromid'] = '设备来源';
        $fields['invoicenum'] = '发票编号';
        $fields['buy_price'] = '设备原值';
        $fields['paytime'] = '付款日期';
        $fields['code_status'] = '打印状态';
        $fields['print_status'] = '标签状态';
        //$fields['pay_statusName'] = '是否付清';
        $fields['pay_status'] = '付款情况';
        $fields['expected_life'] = '预计使用年限';
        $fields['residual_value'] = '残净值率';
        $fields['is_firstaid'] = '急救设备';
        $fields['is_special'] = '特种设备';
        $fields['is_metering'] = '计量设备';
        $fields['is_qualityAssets'] = '质控设备';
        $fields['is_patrol'] = '保养设备';
        $fields['is_benefit'] = '效益分析设备';
        $fields['is_lifesupport'] = '生命支持类设备';
        $fields['guarantee_date'] = '保修截止日期';
        $fields['depreciation_method'] = '折旧方式';
        $fields['depreciable_lives'] = '折旧年限';
        $fields['factory'] = '生产厂商';
        $fields['factory_user'] = '生产厂商联系人';
        $fields['factory_tel'] = '生产厂商联系电话';
        $fields['supplier'] = '供应商';
        $fields['supp_user'] = '供应商联系人';
        $fields['supp_tel'] = '供应商联系电话';
        $fields['repair'] = '维修公司';
        $fields['repa_user'] = '维修公司联系人';
        $fields['repa_tel'] = '维修公司联系电话';
        $fields['remark'] = '设备备注';
        $fields['inventory_label_id'] = '标签ID';
        return $fields;
    }

    /**
     * Notes: 返回选定的表头
     *
     * @param $showFields array 要返回的字段
     *
     * @return array
     */
    public function getTableHeader($showFields = [])
    {
        //查询配置文件默认列表显示选项
        $header = [];
        if (1) {
            $header[0]['type'] = 'checkbox';
            $header[0]['fixed'] = 'left';

            $header[1]['field'] = 'assid';
            $header[1]['title'] = '序号';
            $header[1]['width'] = '60';
            $header[1]['fixed'] = 'left';
            $header[1]['align'] = 'center';
            $header[1]['templet'] = '#serialNumTpl';

            $header[2]['field'] = 'assnum';
            $header[2]['title'] = '设备编号';
            $header[2]['fixed'] = 'left';
            $header[2]['minWidth'] = '180';
            $header[2]['align'] = 'center';

            $header[3]['field'] = 'assets';
            $header[3]['title'] = '设备名称';
            $header[3]['fixed'] = 'left';
            $header[3]['minWidth'] = '180';
            $header[3]['align'] = 'center';

            $header[4]['field'] = 'assorignum';
            $header[4]['title'] = '设备原编码';
            $header[4]['minWidth'] = '140';
            $header[4]['align'] = 'center';

            $header[5]['field'] = 'catid';
            $header[5]['title'] = '设备分类';
            $header[5]['minWidth'] = '160';
            $header[5]['align'] = 'center';

            $header[6]['field'] = 'departid';
            $header[6]['title'] = '使用科室';
            $header[6]['minWidth'] = '180';
            $header[6]['align'] = 'center';

            $header[7]['field'] = 'departperson';
            $header[7]['title'] = '科室负责人';
            $header[7]['minWidth'] = '100';
            $header[7]['align'] = 'center';

            $header[8]['field'] = 'address';
            $header[8]['title'] = '存放地点';
            $header[8]['minWidth'] = '180';
            $header[8]['align'] = 'center';

            $header[9]['field'] = 'managedepart';
            $header[9]['title'] = '管理科室';
            $header[9]['minWidth'] = '180';
            $header[9]['align'] = 'center';

            $header[10]['field'] = 'model';
            $header[10]['title'] = '规格型号';
            $header[10]['minWidth'] = '140';
            $header[10]['align'] = 'center';

            $header[11]['field'] = 'brand';
            $header[11]['title'] = '品牌';
            $header[11]['minWidth'] = '140';
            $header[11]['align'] = 'center';

            $header[12]['field'] = 'unit';
            $header[12]['title'] = '单位';
            $header[12]['minWidth'] = '80';
            $header[12]['align'] = 'center';

            $header[13]['field'] = 'status';
            $header[13]['title'] = '设备当前状态';
            $header[13]['minWidth'] = '120';
            $header[13]['align'] = 'center';
            $header[13]['templet'] = '#statusFormat';

            $header[14]['field'] = 'serialnum';
            $header[14]['title'] = '设备序列号';
            $header[14]['minWidth'] = '140';
            $header[14]['align'] = 'center';

            $header[15]['field'] = 'assetsrespon';
            $header[15]['title'] = '设备负责人';
            $header[15]['minWidth'] = '100';
            $header[15]['align'] = 'center';

            $header[16]['field'] = 'factorynum';
            $header[16]['title'] = '出厂编号';
            $header[16]['minWidth'] = '140';
            $header[16]['align'] = 'center';

            $header[17]['field'] = 'factorydate';
            $header[17]['title'] = '出厂日期';
            $header[17]['minWidth'] = '120';
            $header[17]['sort'] = true;
            $header[17]['align'] = 'center';

            $header[18]['field'] = 'opendate';
            $header[18]['title'] = '开机日期';
            $header[18]['minWidth'] = '120';
            $header[18]['sort'] = true;
            $header[18]['align'] = 'center';

            $header[19]['field'] = 'storage_date';
            $header[19]['title'] = '入库日期';
            $header[19]['minWidth'] = '120';
            $header[19]['sort'] = true;
            $header[19]['align'] = 'center';

            $header[20]['field'] = 'helpcatid';
            $header[20]['title'] = '辅助分类';
            $header[20]['minWidth'] = '120';
            $header[20]['align'] = 'center';

            $header[21]['field'] = 'financeid';
            $header[21]['title'] = '财务分类';
            $header[21]['minWidth'] = '90';
            $header[21]['align'] = 'center';

            $header[22]['field'] = 'capitalfrom';
            $header[22]['title'] = '资金来源';
            $header[22]['minWidth'] = '90';
            $header[22]['align'] = 'center';

            $header[23]['field'] = 'assfromid';
            $header[23]['title'] = '设备来源';
            $header[23]['minWidth'] = '90';
            $header[23]['align'] = 'center';

            $header[24]['field'] = 'invoicenum';
            $header[24]['title'] = '发票编号';
            $header[24]['minWidth'] = '140';
            $header[24]['align'] = 'center';

            $header[25]['field'] = 'buy_price';
            $header[25]['title'] = '设备原值';
            $header[25]['minWidth'] = '120';
            $header[25]['sort'] = true;
            $header[25]['align'] = 'center';

            $header[26]['field'] = 'expected_life';
            $header[26]['title'] = '预计使用年限';
            $header[26]['minWidth'] = '120';
            $header[26]['sort'] = true;
            $header[26]['align'] = 'center';

            $header[27]['field'] = 'residual_value';
            $header[27]['title'] = '残净值率';
            $header[27]['minWidth'] = '120';
            $header[27]['sort'] = true;
            $header[27]['align'] = 'center';

            $header[28]['field'] = 'is_firstaid';
            $header[28]['title'] = '急救设备';
            $header[28]['minWidth'] = '90';
            $header[28]['align'] = 'center';
            $header[28]['templet'] = '#firTpl';

            $header[29]['field'] = 'is_special';
            $header[29]['title'] = '特种设备';
            $header[29]['minWidth'] = '90';
            $header[29]['align'] = 'center';
            $header[29]['templet'] = '#specTpl';

            $header[30]['field'] = 'is_metering';
            $header[30]['title'] = '计量设备';
            $header[30]['minWidth'] = '90';
            $header[30]['align'] = 'center';
            $header[30]['templet'] = '#meteTpl';

            $header[31]['field'] = 'is_qualityAssets';
            $header[31]['title'] = '质控设备';
            $header[31]['minWidth'] = '90';
            $header[31]['align'] = 'center';
            $header[31]['templet'] = '#quaTpl';

            $header[32]['field'] = 'is_benefit';
            $header[32]['title'] = '效益分析设备';
            $header[32]['minWidth'] = '120';
            $header[32]['align'] = 'center';
            $header[32]['templet'] = '#benTpl';

            $header[33]['field'] = 'is_lifesupport';
            $header[33]['title'] = '生命支持类设备';
            $header[33]['minWidth'] = '120';
            $header[33]['align'] = 'center';
            $header[33]['templet'] = '#benTpl';

            $header[34]['field'] = 'guarantee_date';
            $header[34]['title'] = '保修截止日期';
            $header[34]['minWidth'] = '130';
            $header[34]['sort'] = true;
            $header[34]['align'] = 'center';

            $header[35]['field'] = 'depreciation_method';
            $header[35]['title'] = '折旧方式';
            $header[35]['minWidth'] = '120';
            $header[35]['align'] = 'center';

            $header[36]['field'] = 'depreciable_lives';
            $header[36]['title'] = '折旧年限';
            $header[36]['minWidth'] = '100';
            $header[36]['sort'] = true;
            $header[36]['align'] = 'center';

            $header[37]['field'] = 'factory';
            $header[37]['title'] = '生产厂商';
            $header[37]['minWidth'] = '230';
            $header[37]['align'] = 'center';

            $header[38]['field'] = 'factory_user';
            $header[38]['title'] = '厂商联系人';
            $header[38]['minWidth'] = '100';
            $header[38]['align'] = 'center';

            $header[39]['field'] = 'factory_tel';
            $header[39]['title'] = '厂商联系电话';
            $header[39]['minWidth'] = '120';
            $header[39]['align'] = 'center';

            $header[40]['field'] = 'supplier';
            $header[40]['title'] = '供应商';
            $header[40]['minWidth'] = '230';
            $header[40]['align'] = 'center';

            $header[41]['field'] = 'supp_user';
            $header[41]['title'] = '供应商联系人';
            $header[41]['minWidth'] = '120';
            $header[41]['align'] = 'center';

            $header[42]['field'] = 'supp_tel';
            $header[42]['title'] = '供应商联系电话';
            $header[42]['minWidth'] = '130';
            $header[42]['align'] = 'center';

            $header[43]['field'] = 'repair';
            $header[43]['title'] = '维修公司';
            $header[43]['minWidth'] = '230';
            $header[43]['align'] = 'center';

            $header[44]['field'] = 'repa_user';
            $header[44]['title'] = '维修联系人';
            $header[44]['minWidth'] = '110';
            $header[44]['align'] = 'center';

            $header[45]['field'] = 'repa_tel';
            $header[45]['title'] = '维修联系电话';
            $header[45]['minWidth'] = '120';
            $header[45]['align'] = 'center';

            $header[46]['field'] = 'operation';
            $header[46]['title'] = '操作';
            $header[46]['minWidth'] = '285';
            $header[46]['align'] = 'center';
            $header[46]['fixed'] = 'right';

            $header[47]['field'] = 'is_patrol';
            $header[47]['title'] = '保养设备';
            $header[47]['minWidth'] = '120';
            $header[47]['align'] = 'center';
            $header[47]['templet'] = '#patTpl';

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

    public function getEditHeader($newField = [], $width = [])
    {
        $header[0]['field'] = 'assid';
        $header[0]['title'] = '序号';
        $header[0]['width'] = '10%';
        $header[0]['align'] = 'center';
        $header[0]['templet'] = '#serialNumTpl';

        $header[1]['field'] = 'assnum';
        $header[1]['title'] = '设备编号';
        $header[1]['width'] = '30%';
        $header[1]['align'] = 'center';

        $header[2]['field'] = 'assets';
        $header[2]['title'] = '设备名称';
        $header[2]['width'] = '30%';
        $header[2]['align'] = 'center';

        $header[3]['field'] = 'assorignum';
        $header[3]['title'] = '设备原编码';
        $header[3]['width'] = '30%';
        $header[3]['align'] = 'center';
        if ($newField) {
            if ($newField['field'] == 'assets_level') {
                $newField['field'] = 'assets_level_name';
            }
            $len = count($header);
            $header[$len]['field'] = $newField['field'];
            $header[$len]['title'] = $newField['title'];
            $header[$len]['align'] = $newField['align'];
            foreach ($header as $k => $v) {
                $header[$k]['width'] = $width[$k];
            }
        }
        return $header;
    }

    public function batchUpdateData()
    {
        $assid = trim(I('post.assid'), ',');
        $assidArr = explode(',', $assid);
        $keyvalue['assets'] = '设备名称';
        $keyvalue['assorignum'] = '设备原编码';
        $keyvalue['serialnum'] = '设备序列号';
        $keyvalue['cate'] = '设备分类';
        $keyvalue['department'] = '使用科室';
        $keyvalue['finance'] = '财务分类';
        $keyvalue['model'] = '规格/型号';
        $keyvalue['storage_date'] = '入库日期';
        $keyvalue['opendate'] = '开机日期';
        $keyvalue['capitalfrom'] = '资金来源';
        $keyvalue['assfrom'] = '设备来源';
        $keyvalue['assetsrespon'] = '设备负责人';
        $keyvalue['buy_price'] = '设备原值(元)';
        $keyvalue['expected_life'] = '预计使用年限';
        $keyvalue['residual_value'] = '残净值率(%)';
        $keyvalue['guarantee_date'] = '保修截止日期';
        $keyvalue['depreciable_lives'] = '折旧年限';
        $keyvalue['depreciation_method'] = '折旧方式';
        $keyvalue['factory'] = '生产厂家';
        $keyvalue['supplier'] = '供应商';
        $updata = [];
        $field = '';
        foreach (I('post.field') as $k => $v) {
            $v = trim($v);
            if ($keyvalue[$k] && !isset($v)) {
                return ['status' => -1, 'msg' => $keyvalue[$k] . '不能为空！'];
            } else {
                if ($k != 'remark') {
                    if ($v == '') {
                        return ['status' => -1, 'msg' => $keyvalue[$k] . '不能为空！'];
                    }
                }
                $field = $k;
                $updata[$field] = $v;
                $updata['editdate'] = time();
            }
        }

        //数据验证
        if ($field == 'buy_price') {
            if (!is_numeric($updata[$field]) || $updata[$field] < 0) {
                return ['status' => -1, 'msg' => '请输入合理的' . $keyvalue[$field]];
            }
        }
        if ($field == 'expected_life' || $field == 'depreciable_lives') {
            if (!is_numeric($updata[$field]) || $updata[$field] <= 0) {
                return ['status' => -1, 'msg' => '请输入合理的' . $keyvalue[$field]];
            }
        }
        if ($field == 'departid') {
            //要更改的是科室，则要判断是否允许修改
            foreach ($assidArr as $k => $v) {
                $asInfo = $this->DB_get_one('assets_info',
                    'departid,assid,assnum,assets,status,quality_in_plan,patrol_in_plan', ['assid' => $v]);
                if ($_POST['field']['departid'] != $asInfo['departid']) {
                    switch ($asInfo['status']) {
                        case C('ASSETS_STATUS_REPAIR'):
                            //设备维修中
                            return [
                                'status' => -1,
                                'msg' => $asInfo['assnum'] . ' 设备' . C('ASSETS_STATUS_REPAIR_NAME') . '，暂不允许对科室进行修改！',
                            ];
                            break;
                        case C('ASSETS_STATUS_SCRAP'):
                            //设备已报废
                            return [
                                'status' => -1,
                                'msg' => $asInfo['assnum'] . ' 设备' . C('ASSETS_STATUS_SCRAP_NAME') . '，暂不允许对科室进行修改！',
                            ];
                            break;
                        case C('ASSETS_STATUS_OUTSIDE'):
                            //设备已外调
                            return [
                                'status' => -1,
                                'msg' => $asInfo['assnum'] . '设备' . C('ASSETS_STATUS_OUTSIDE_NAME') . '，暂不允许对科室进行修改！',
                            ];
                            break;
                        case C('ASSETS_STATUS_OUTSIDE_ON'):
                            //设备外调中
                            return [
                                'status' => -1,
                                'msg' => $asInfo['assnum'] . ' 设备' . C('ASSETS_STATUS_OUTSIDE_ON_NAME') . '，暂不允许对科室进行修改！',
                            ];
                            break;
                        case C('ASSETS_STATUS_SCRAP_ON'):
                            //设备报废中
                            return [
                                'status' => -1,
                                'msg' => $asInfo['assnum'] . ' 设备' . C('ASSETS_STATUS_SCRAP_ON_NAME') . '，暂不允许对科室进行修改！',
                            ];
                            break;
                        case  C('ASSETS_STATUS_TRANSFER_ON'):
                            //设备转科中
                            return [
                                'status' => -1,
                                'msg' => $asInfo['assnum'] . ' 设备' . C('ASSETS_STATUS_TRANSFER_ON_NAME') . '，暂不允许对科室进行修改！',
                            ];
                            break;
                        case '':
                            break;
                    }
                    if ($asInfo['quality_in_plan'] == C('YES_STATUS')) {
                        return ['status' => -1, 'msg' => $asInfo['assnum'] . ' 设备质控计划中，暂不允许对科室进行修改！'];
                    }
                    if ($asInfo['patrol_in_plan'] == C('YES_STATUS')) {
                        return ['status' => -1, 'msg' => $asInfo['assnum'] . ' 设备巡查计划中，暂不允许对科室进行修改！'];
                    }
                }
            }
        }
        $factoryField = [
            'factory',
            'factory_user',
            'factory_tel',
            'supplier',
            'supp_user',
            'supp_tel',
            'repair',
            'repa_user',
            'repa_tel',
        ];
        $updatTable = 'assets_info';
        if (in_array($field, $factoryField)) {
            $updatTable = 'assets_factory';
        } else {
            $updata['edituser'] = session('username');
        }
        $res = $this->updateData($updatTable, $updata, ['assid' => ['in', $assidArr]]);
        if ($res) {
            if (isset($updata['assets']) || isset($updata['model']) || isset($updata['factorynum']) || isset($updata['opendate']) || isset($updata['factory']) || isset($updata['buy_price'])) {
                //如果是修改这些字段，则对应修改repair表的相应字段
                if (isset($updata['buy_price'])) {
                    $this->updateData('repair', ['assprice' => $updata['buy_price']], ['assid' => ['in', $assidArr]]);
                } else {
                    $this->updateData('repair', $updata, ['assid' => ['in', $assidArr]]);
                }
            }
            if (isset($updata['assets']) || isset($updata['model']) || isset($updata['unit']) || isset($updata['factory'])) {
                //如果是修改这些字段，则对应修改metering_plan表的相应字段
                $this->updateData('metering_plan', $updata, ['assid' => ['in', $assidArr]]);
            }
            return ['status' => 1, 'msg' => '批量维护成功！', 'field' => $field];
        } else {
            return ['status' => -1, 'msg' => '批量维护失败！'];
        }
    }

    /**
     * Notes: 验证数据合法性
     *
     * @return array|mixed
     */
    public function checkData()
    {
        $data = [];
        //判断设备名称
        $this->checkstatus(judgeEmpty(I('post.dic_assets_sel')), '设备名称不能为空');
        $data['assets'] = I('post.dic_assets_sel');
        $data['model'] = I('post.model');
        //资产序列号
        $this->checkstatus(judgeEmpty(I('post.serialnum')), '序列号不能为空');
        $data['serialnum'] = trim(I('post.serialnum'));
        if (I('post.assid')) {
            $swhere['serialnum'] = $data['serialnum'];
            $swhere['assid'] = ['neq', I('post.assid')];
        } else {
            $swhere['serialnum'] = trim(I('post.serialnum'));
        }
        //判断序列号是否已存在
        if (I('post.serialnum') != '/') {
            $swhere['is_delete'] = '0';
            $serialnum = $this->DB_get_one('assets_info', 'assid', $swhere);
            if ($serialnum) {
                return ['status' => -1, 'msg' => '序列号已存在！'];
            }
        }
        //判断设备分类
        $this->checkstatus(judgeEmpty(I('post.catid')), '请选择设备分类');
        $data['catid'] = I('post.catid');

        if (I('post.is_subsidiary') == C('YES_STATUS')) {
            //是附属设备
            if (!I('post.main_assid')) {
                return ['status' => -1, 'msg' => '请选择所属设备！'];
            }
            if (I('post.subsidiary_helpcatid') != '') {
                $data['subsidiary_helpcatid'] = I('post.subsidiary_helpcatid');
            }
            $data['is_subsidiary'] = C('YES_STATUS');
            $data['main_assid'] = I('post.main_assid');
            $data['main_assets'] = I('post.main_assets');

        } else {
            $data['is_subsidiary'] = C('NO_STATUS');
            //判断辅助分类
            if (I('post.helpcatid') != '') {
                $data['helpcatid'] = I('post.helpcatid');
            }
            //设备原编码
            $this->checkstatus(judgeEmpty(I('post.assorignum')), '设备原编码不能为空');


        }
        $data['assorignum'] = I('post.assorignum');
        if ($data['assorignum']) {
            if (I('post.assid')) {
                $map['assorignum_spare'] = $data['assorignum'];
                $map['assorignum'] = $data['assorignum'];
                $map['_logic'] = 'OR';
                $asswhere['_complex'] = $map;
                $asswhere['assid'] = ['neq', I('post.assid')];
            } else {
                $map['assorignum_spare'] = $data['assorignum'];
                $map['assorignum'] = $data['assorignum'];
                $map['_logic'] = 'OR';
                $asswhere['_complex'] = $map;
            }
            //判断原编码是否已存在
            if ($data['assorignum'] != '/') {
                $asswhere['is_delete'] = '0';
                $assorignum = $this->DB_get_one('assets_info', 'assid', $asswhere);
                if ($assorignum) {
                    return ['status' => -1, 'msg' => '原编码或原编码备用已存在！'];
                }
            }
        }
        $data['assorignum_spare'] = I('post.assorignum_spare');
        if ($data['assorignum_spare']) {
            if (I('post.assid')) {
                $map['assorignum_spare'] = $data['assorignum_spare'];
                $map['assorignum'] = $data['assorignum_spare'];
                $map['_logic'] = 'OR';
                $ass_sparewhere['_complex'] = $map;
                $ass_sparewhere['assid'] = ['neq', I('post.assid')];
            } else {
                $map['assorignum_spare'] = $data['assorignum_spare'];
                $map['assorignum'] = $data['assorignum_spare'];
                $map['_logic'] = 'OR';
                $ass_sparewhere['_complex'] = $map;

            }
            //判断原编码(备用)是否已存在
            if ($data['assorignum_spare'] != '/') {
                $ass_sparewhere['is_delete'] = '0';
                $assorignum = $this->DB_get_one('assets_info', 'assid', $ass_sparewhere);
                if ($assorignum) {
                    return ['status' => -1, 'msg' => '原编码或原编码备用已存在！'];
                }
            }
        }

        //判断设备原值
        if (I('post.buy_price') != '') {
            $this->checkstatus(judgeNum(I('post.buy_price')), '发票金额只能为数字');
            $data['buy_price'] = I('post.buy_price');
        }
        //预计使用年限
        if (I('post.expected_life') != '') {
            $data['expected_life'] = I('post.expected_life');
        }

        //残净值率
        if (I('post.residual_value') != '') {
            $data['residual_value'] = I('post.residual_value');
        }
        //保修到期日期
        if (I('post.guarantee_date') == '') {
            $data['guarantee_date'] = '0000-00-00';
        } else {
            $data['guarantee_date'] = I('post.guarantee_date');
        }
        //判断单位
        if (I('post.unit') != '') {
            $data['unit'] = I('post.unit');
        }
        //判断单位
        if (I('post.address') != '') {
            $data['address'] = I('post.address');
        }
        //判断品牌
        if (I('post.brand') != '') {
            $data['brand'] = I('post.brand');
        }
        //注册证编号
        if (I('post.registration') != '') {
            $data['registration'] = I('post.registration');
        }
        //出厂编号
        if (I('post.factorynum') != '') {
            $data['factorynum'] = I('post.factorynum');
        }

        //判断出厂编号是否已存在
        if (I('post.factorynum') != '/' && I('post.factorynum') != '') {
            $factorynum = $this->DB_get_one('assets_info', 'factorynum',
                ['factorynum' => I('post.factorynum'), 'is_delete' => '0', 'assid' => ['neq', I('post.assid')]]);
            if ($factorynum) {
                return ['status' => -1, 'msg' => '出厂编号已存在！'];
            }
        }
        //出厂日期
        if (I('post.factorydate') == '') {
            $data['factorydate'] = '0000-00-00';
        } else {
            $data['factorydate'] = I('post.factorydate');
        }
        //付款日期
        if (I('post.paytime') == '') {
            $data['paytime'] = '0000-00-00';
        } else {
            $data['paytime'] = I('post.paytime');
        }
        //发票编号
        if (I('post.invoicenum') != '') {
            $data['invoicenum'] = I('post.invoicenum');
        }
        if (I('post.invoicenum') != '/' && I('post.invoicenum') != '') {
            $invoicenum = $this->DB_get_one('assets_info', 'invoicenum',
                ['invoicenum' => I('post.invoicenum'), 'is_delete' => '0', 'assid' => ['neq', I('post.assid')]]);
            if ($invoicenum) {
                return ['status' => -1, 'msg' => '发票编号已存在！'];
            }
        }
        //判断急救资产
        if (!I('post.is_firstaid')) {
            $data['is_firstaid'] = 0;
        } else {
            $data['is_firstaid'] = I('post.is_firstaid');
        }
        //判断特种资产
        if (!I('post.is_special')) {
            $data['is_special'] = 0;
        } else {
            $data['is_special'] = I('post.is_special');
        }
        //判断计量资产
        if (!I('post.is_metering')) {
            $data['is_metering'] = 0;
        } else {
            $data['is_metering'] = I('post.is_metering');
        }
        //判断质控设备
        if (!I('post.is_qualityAssets')) {
            $data['is_qualityAssets'] = 0;
        } else {
            $data['is_qualityAssets'] = I('post.is_qualityAssets');
        }
        //判断保养设备
        if (!I('post.is_patrol')) {
            $data['is_patrol'] = 0;
        } else {
            $data['is_patrol'] = I('post.is_patrol');
        }
        //判断质控设备
        if (!I('post.is_benefit')) {
            $data['is_benefit'] = 0;
        } else {
            $data['is_benefit'] = I('post.is_benefit');
        }
        //判断生命支持类设备
        if (!I('post.is_lifesupport')) {
            $data['is_lifesupport'] = 0;
        } else {
            $data['is_lifesupport'] = I('post.is_lifesupport');
        }
        //国产进口
        if (I('post.is_domestic') == '') {
            $data['is_domestic'] = 3;
        } else {
            $data['is_domestic'] = I('post.is_domestic');
        }
        //是否付清
        if (I('post.pay_status') == '') {
            $data['pay_status'] = 3;
        } else {
            $data['pay_status'] = I('post.pay_status');
        }
        //判断巡查周期
        $data['patrol_xc_cycle'] = I('post.patrol_xc_cycle');
        if ($data['patrol_xc_cycle'] != "") {
            if (!is_numeric($data['patrol_xc_cycle']) || $data['patrol_xc_cycle'] < 1) {
                return ['status' => -1, 'msg' => '巡查周期请输入正整数'];
            }
            $data['patrol_xc_cycle'] = intval($data['patrol_xc_cycle']);
        }
        //判断保养周期
        $data['patrol_pm_cycle'] = I('post.patrol_pm_cycle');
        if ($data['patrol_pm_cycle'] != "") {
            if (!is_numeric($data['patrol_pm_cycle']) || $data['patrol_pm_cycle'] < 1) {
                return ['status' => -1, 'msg' => '保养周期请输入正整数'];
            }
            $data['patrol_pm_cycle'] = intval($data['patrol_pm_cycle']);
        }
        //判断质控周期
        $data['quality_cycle'] = I('post.quality_cycle');
        if ($data['quality_cycle'] != "") {
            if (!is_numeric($data['quality_cycle']) || $data['quality_cycle'] < 1) {
                return ['status' => -1, 'msg' => '质控周期请输入正整数'];
            }
            $data['quality_cycle'] = intval($data['quality_cycle']);
        }
        //判断计量周期
        $data['metering_cycle'] = I('post.metering_cycle');
        if ($data['metering_cycle'] != "") {
            if (!is_numeric($data['metering_cycle']) || $data['metering_cycle'] < 1) {
                return ['status' => -1, 'msg' => '计量周期请输入正整数'];
            }
            $data['metering_cycle'] = intval($data['metering_cycle']);
        }
        //判断使用科室
        $this->checkstatus(judgeEmpty(I('post.departid')), '使用科室不能为空');
        $data['departid'] = I('post.departid');
        //判断资产负责人
        if (I('post.assetsrespon') != '') {
            $data['assetsrespon'] = I('post.assetsrespon');
        }
        //判断管理科室
        if (I('post.managedepart') != '') {
            $data['managedepart'] = I('post.managedepart');
        }

        //判断财务分类
        if (I('post.financeid') != '') {
            $data['financeid'] = I('post.financeid');
        }
        //判断资产来源
        if (I('post.assfromid') != '') {
            $data['assfromid'] = I('post.assfromid');
        }
        //判断资金来源
        if (I('post.capitalfrom') != '') {
            $data['capitalfrom'] = I('post.capitalfrom');
        }
        //入库日期
        if (I('post.storage_date') == '') {
            $data['storage_date'] = '0000-00-00';
        } else {
            $data['storage_date'] = I('post.storage_date');
        }
        //启用日期
        if (I('post.opendate') == '') {
            $data['opendate'] = '0000-00-00';
        } else {
            $data['opendate'] = I('post.opendate');
        }

        //折旧方式
        $data['depreciation_method'] = I('post.depreciation_method');
        //折旧年限
        $data['depreciable_lives'] = I('post.depreciable_lives');
        //月折旧额
        $data['depreciable_quota_m'] = (float) I('post.depreciable_quota_m');
        //累计折旧额
        $data['depreciable_quota_count'] = (float) I('post.depreciable_quota_count');
        //资产净值
        $data['net_asset_value'] = (float) I('post.net_asset_value');
        //减值准备
        $data['impairment_provision'] = (float) I('post.impairment_provision');
        //资产净额
        $data['net_assets'] = (float) I('post.net_assets');
        $data['box_num'] = trim(I('post.box_num'));
        //医疗器械类别
        $data['assets_level'] = I('post.assets_level');
        //设备备注
        $data['remark'] = I('post.remark');
        return $data;
    }

    /**
     * Notes: 获取设备详情
     *
     * @param $assid int 设备ID
     *
     * @return mixed
     */
    public function getAssetsInfo($assid)
    {
        //加载缓存
        $catname = [];
        $departname = [];
        $baseSetting = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $assets = $this->DB_get_one('assets_info', '', ['assid' => $assid]);
        $where['assid'] = ['EQ', $assid];
        $files = 'afid,factory,factory_user,factory_tel,supplier,supp_user,supp_tel,repair,repa_user,repa_tel';
        $factory = $this->DB_get_one('assets_factory', $files, $where);
        //第一分类信息
        //$this->DB_get_one('assets_factory','factory');
        $assets['departmentAddress'] = $departname[$assets['departid']]['address'];
        $assets['cate_name'] = $catname[$assets['catid']]['category'];
        if ($assets['is_subsidiary'] == C('YES_STATUS')) {
            //附属设备 获取附属设备辅助分类
            $assets['helpcat'] = $baseSetting['assets']['acin_category']['value'][$assets['subsidiary_helpcatid']];
        } else {
            //主设备   获取主设备辅助分类
            $assets['helpcat'] = $baseSetting['assets']['assets_helpcat']['value'][$assets['helpcatid']];
        }

        if (getHandleTime(time()) < $assets['guarantee_date']) {
            $assets['guaranteeStatus'] = '<span class="green">保修期内</span>';
            $assets['guaranteeStatusNoColor'] = '保修期内';
            $assets['is_guarantee'] = C('YES_STATUS');
        } else {
            if ($assets['guarantee_status'] == C('INSURANCE_STATUS_USE')) {
                $assets['guaranteeStatus'] = '<span class="green">参保期内</span>';
                $assets['guaranteeStatusNoColor'] = '参保期内';
                $assets['is_guarantee'] = C('YES_STATUS');
            } else {
                $assets['guaranteeStatus'] = '<span style="color: red;">脱保</span>';
                $assets['guaranteeStatusNoColor'] = '脱保';
            }
        }

        // 年限到期日期
        if ($assets['factorydate'] && $assets['factorydate'] !== '0000-00-00' && (int) $assets['expected_life']) {
            $assets['life_expiration_date'] = (new DateTime($assets['factorydate']))
                ->modify("+{$assets['expected_life']} years")
                ->format('Y-m-d')
            ;
        } else {
            $assets['life_expiration_date'] = HandleEmptyNull('');
        }

        if ($assets['expected_life'] > 0) {
            // 计算剩余有效年限
            $storage_date = strtotime($assets['storage_date']); 
            $current_date = time();
            
            // 计算已使用月数(向上取整)
            $used_months = ceil(($current_date - $storage_date) / (30 * 24 * 60 * 60));
            
            // 计算预计使用总月数
            $total_months = $assets['expected_life'] * 12;
            
            // 计算剩余月数
            $remaining_months = $total_months - $used_months;
            
            // 添加到返回数据中
            $assets['remaining_mounths'] = $remaining_months;
        } else {
            $assets['remaining_mounths'] = "/";
        }

        if ($assets['status'] == C('ASSETS_STATUS_USE')) {
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
        $assets['paytime'] = HandleEmptyNull($assets['paytime']);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assets['buy_price'] = '***';
        }
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
        if ($assets['is_patrol'] == C('YES_STATUS')) {
            $assets['type'] .= '、' . C('ASSETS_PATROL_CODE_YES_NAME');
        }
        if ($assets['is_benefit'] == C('YES_STATUS')) {
            $assets['type'] .= '、' . C('ASSETS_BENEFIT_CODE_YES_NAME');
        }
        if ($assets['is_lifesupport'] == C('YES_STATUS')) {
            $assets['type'] .= '、' . C('ASSETS_LIFE_SUPPORT_NAME');
        }
        switch ($assets['depreciation_method']) {
            case 1:
                $assets['depreciation_method_name'] = '平均折旧法';
                break;
            case 2:
                $assets['depreciation_method_name'] = '工作量法';
                break;
            case 3:
                $assets['depreciation_method_name'] = '加速折旧法';
                break;
            default:
                $assets['depreciation_method_name'] = '';
                break;
        }
        switch ($assets['pay_status']) {
            case 0:
                $assets['pay_statusName'] = '未付清';
                break;
            case 1:
                $assets['pay_statusName'] = '已付清';
                break;
            default:
                $assets['pay_statusName'] = '';
                break;
        }
        switch ($assets['is_domestic']) {
            case 1:
                $assets['is_domesticName'] = '国产';
                break;
            case 2:
                $assets['is_domesticName'] = '进口';
                break;
            case 3:
                $assets['is_domesticName'] = '';
                break;
            default:
                $assets['is_domesticName'] = '';
                break;
        }
        switch ($assets['assets_level']) {
            case 1:
                $assets['assets_level_name'] = 'Ⅰ类';
                break;
            case 2:
                $assets['assets_level_name'] = 'Ⅱ类';
                break;
            case 3:
                $assets['assets_level_name'] = 'Ⅲ类';
                break;
            default:
                $assets['assets_level_name'] = '';
                break;
        }
        $assets['type'] = trim($assets['type'], '、');
        $assets['expected_life'] = $assets['expected_life'] > 0 ? $assets['expected_life'] : '';
        if (isset($factory)) {
            return array_merge($assets, $factory);
        } else {
            return $assets;
        }
    }

    /**
     * Notes: 获取设备附属设备列表
     *
     * @param $assid int 主设备ID
     *
     * @return mixed
     */
    public function getSubsidiaryList($assid)
    {
        $files = 'main_assid,assets,assid,hospital_id,assnum,brand,model,unit,departid,buy_price,catid,model';
        $where['main_assid'] = ['EQ', $assid];
        $where['is_subsidiary'] = ['EQ', C('YES_STATUS')];
        $where['status'] = ['NOTIN', [C('ASSETS_STATUS_SCRAP'), C('ASSETS_STATUS_OUTSIDE')]];
        $subsidiary = $this->DB_get_all('assets_info', $files, $where);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if ($subsidiary) {
            $catname = [];
            $departname = [];
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            foreach ($subsidiary as &$subsidiary_v) {
                if (!$showPrice) {
                    $subsidiary_v['buy_price'] = '***';
                }
                $subsidiary_v['department'] = $departname[$subsidiary_v['departid']]['department'];
                $subsidiary_v['category'] = $catname[$subsidiary_v['catid']]['category'];
                $subsidiary_v['operation'] = $this->returnListLink('详情',
                    C('SHOWASSETS_ACTION_URL') . '?assid=' . $subsidiary_v['assid'], 'showAssets',
                    C('BTN_CURRENCY') . ' layui-btn-primary');
            }
        }
        return $subsidiary;
    }


    /**
     * Notes: 获取生命历程
     *
     * @param $assid int 设备ID
     *
     * @return mixed
     */
    public function getLifeInfo($assid)
    {
        //加载缓存
//        $catname = array();
        $departname = [];
//        $baseSetting = array();
//        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
//        include APP_PATH . "Common/cache/basesetting.cache.php";
        $life = [];
        //转科
        $transferFields = 'atid,transfernum,applicant_user,applicant_time,transfer_date,tranout_departid,tranin_departid,tranout_departrespon,tranin_departrespon,is_check,check_user,check_time';
        $transferInfo = $this->DB_get_all('assets_transfer', $transferFields, ['assid' => $assid], '', 'atid asc',
            '');
        if (!$transferInfo) {
            $join = 'LEFT JOIN sb_assets_transfer_detail AS B ON A.atid = B.atid';
            $transferInfo = $this->DB_get_all_join('assets_transfer', 'A', '', $join, ['subsidiary_assid' => $assid],
                '', '', '');
        }
        foreach ($transferInfo as $k => $v) {
            $transferInfo[$k]['tranout_depart_name'] = $departname[$v['tranout_departid']]['department'];
            $transferInfo[$k]['tranin_depart_name'] = $departname[$v['tranin_departid']]['department'];
            $transferInfo[$k]['sort_date'] = getHandleTime(strtotime($v['check_time']));
            $transferInfo[$k]['sort_time'] = $v['check_time'];
        }
        //维修
        $repairInfo = $this->DB_get_all('repair',
            'repid,repnum,applicant,applicant_time,status,overdate,response,response_date,checkperson,checkdate,breakdown',
            ['assid' => $assid], '', '', '');
        foreach ($repairInfo as $k => $v) {
            $repairInfo[$k]['applicant_time'] = date('Y-m-d H:i:s', $v['applicant_time']);
            $repairInfo[$k]['overdate'] = $v['overdate'] ? date('Y-m-d H:i:s', $v['overdate']) : '';
            $repairInfo[$k]['response_date'] = $v['response_date'] ? date('Y-m-d H:i:s', $v['response_date']) : '';
            $repairInfo[$k]['checkdate'] = $v['checkdate'] ? date('Y-m-d H:i:s', $v['checkdate']) : '';
            $repairInfo[$k]['sort_date'] = date('Y-m-d', $v['applicant_time']);
            $repairInfo[$k]['sort_time'] = date('Y-m-d H:i:s', $v['applicant_time']);
            switch ($v['status']) {
                case C('REPAIR_HAVE_REPAIRED'):
                    $repairInfo[$k]['statusName'] = C('REPAIR_HAVE_REPAIRED_NAME');
                    break;
                case C('REPAIR_RECEIPT'):
                    $repairInfo[$k]['statusName'] = C('REPAIR_RECEIPT_NAME');
                    break;
                case C('REPAIR_HAVE_OVERHAULED'):
                    $repairInfo[$k]['statusName'] = C('REPAIR_HAVE_OVERHAULED_NAME');
                    break;
                case C('REPAIR_QUOTATION'):
                    $repairInfo[$k]['statusName'] = C('REPAIR_QUOTATION_NAME');
                    break;
                case C('REPAIR_AUDIT'):
                    $repairInfo[$k]['statusName'] = C('REPAIR_AUDIT_NAME');
                    break;
                case C('REPAIR_MAINTENANCE'):
                    $repairInfo[$k]['statusName'] = C('REPAIR_MAINTENANCE_NAME');
                    break;
                case C('REPAIR_MAINTENANCE_COMPLETION'):
                    $repairInfo[$k]['statusName'] = C('REPAIR_MAINTENANCE_COMPLETION_NAME');
                    break;
                default:
                    $repairInfo[$k]['statusName'] = '已完成';
                    break;
            }
        }
        //查询设备所属医院
        $info = $this->DB_get_one('assets_info', 'hospital_id', ['assid' => $assid]);
        //报废
        //查询是否开启了报废审核，用做区分具体报废时间
        $isopenscrap = $this->checkApproveIsOpen(C('SCRAP_APPROVE'), $info['hospital_id']);
        if ($isopenscrap) {
            //开启了报废审核，最后报废时间为分界点
            $scrapWhere['assid'] = $assid;
            $scrapWhere['approve_status'] = ['neq', 0];//不等于0的为已审核
        } else {
            //未开启报废审核，以添加时间为报废点
            $scrapWhere['assid'] = $assid;
        }
        $scrapInfo = $this->DB_get_all('assets_scrap', '', $scrapWhere, '', 'add_time asc', '');
        if (!$scrapInfo) {
            //可能是附属设备
            $join = 'LEFT JOIN sb_assets_scrap_detail AS B ON A.scrid = B.scrid';
            if ($isopenscrap) {
                //开启了报废审核，最后报废时间为分界点
                $scrapWhere['subsidiary_assid'] = $assid;
                $scrapWhere['approve_status'] = ['neq', 0];//不等于0的为已审核
            } else {
                //未开启报废审核，以添加时间为报废点
                $scrapWhere['subsidiary_assid'] = $assid;
            }
            $scrapInfo = $this->DB_get_all_join('assets_scrap', 'A', '', $join, $scrapWhere);
        }
        foreach ($scrapInfo as $k => $v) {
            if ($v['approve_status'] != '-1') {
                //开启报废审核
                $scrapInfo[$k]['sort_date'] = date('Y-m-d', strtotime($v['approve_time']));
                $scrapInfo[$k]['sort_time'] = $v['approve_time'];
            } else {
                $scrapInfo[$k]['sort_date'] = date('Y-m-d', strtotime($v['add_time']));
                $scrapInfo[$k]['sort_time'] = $v['add_time'];
            }
        }
        //取出设备编号
        $asinfo = $this->DB_get_one('assets_info', 'assnum', ['assid' => $assid], '');
        $assnum = $asinfo['assnum'];
        //巡查保养信息
        $patrids = $this->DB_get_all('patrol_plans_assets', 'patrid', ['assnum' => $assnum], 'patrid', 'patrid asc');
        $patr_ids = [];
        foreach ($patrids as $v) {
            $patr_ids[] = $v['patrid'];
        }
        $patrolInfo = [];
        if ($patr_ids) {
            $patrolfields = 'patrid,patrol_name,patrol_level,is_cycle,cycle_unit,cycle_setting,total_cycle,current_cycle,patrol_start_date,patrol_end_date,patrol_status,release_time,release_user';
            $patrolInfo = $this->DB_get_all('patrol_plans', $patrolfields,
                ['is_release' => 1, 'patrid' => ['in', implode(',', $patr_ids)]], '', 'patrol_status asc');
            foreach ($patrolInfo as $k => $v) {
                $patrolInfo[$k]['cycle_name'] = $v['is_cycle'] ? '是' : '否';
                if ($v['is_cycle']) {
                    switch ($v['cycle_unit']) {
                        case 'day':
                            $patrolInfo[$k]['cycle_setting_name'] = '每' . $v['cycle_setting'] . '天';
                            break;
                        case 'week':
                            $patrolInfo[$k]['cycle_setting_name'] = '每' . $v['cycle_setting'] . '周';
                            break;
                        case 'month':
                            $patrolInfo[$k]['cycle_setting_name'] = '每' . $v['cycle_setting'] . '月';
                            break;
                        case 'quarter':
                            $patrolInfo[$k]['cycle_setting_name'] = '每' . $v['cycle_setting'] . '季度';
                            break;
                        case 'year':
                            $patrolInfo[$k]['cycle_setting_name'] = '每' . $v['cycle_setting'] . '年';
                            break;
                    }
                    $patrolInfo[$k]['total_current_period'] = $v['current_cycle'] . ' / ' . $v['total_cycle'];
                }
                switch ($v['patrol_level']) {
                    case C('PATROL_LEVEL_DC'):
                        $patrolInfo[$k]['patrol_level_name'] = C('PATROL_LEVEL_NAME_DC');
                        break;
                    case C('PATROL_LEVEL_RC'):
                        $patrolInfo[$k]['patrol_level_name'] = C('PATROL_LEVEL_NAME_RC');
                        break;
                    default:
                        $patrolInfo[$k]['patrol_level_name'] = C('PATROL_LEVEL_NAME_PM');
                        break;
                }
                switch ($v['patrol_status']) {
                    case 1:
                        $patrolInfo[$k]['statusName'] = '待审核';
                        break;
                    case 2:
                        $patrolInfo[$k]['statusName'] = '待发布';
                        break;
                    case 3:
                        $patrolInfo[$k]['statusName'] = '实施中';
                        break;
                    case 4:
                        $patrolInfo[$k]['statusName'] = '已结束';
                        break;
                }
                $patrolInfo[$k]['sort_date'] = HandleEmptyNull(getHandleTime(strtotime($v['release_time'])));
                $patrolInfo[$k]['sort_time'] = HandleEmptyNull(getHandleDate(strtotime($v['release_time'])));
                $patrolInfo[$k]['release_time'] = HandleEmptyNull(getHandleTime(strtotime($v['release_time'])));
            }
            //统计异设备点数
            foreach ($patrolInfo as $k => $v) {
                $cycidArr = $this->DB_get_one('patrol_plans_cycle', 'group_concat(cycid) AS cycid',
                    ['patrid' => $v['patrid']]);
                if ($cycidArr['cycid']) {
                    $join = "LEFT JOIN sb_patrol_execute_abnormal AS B ON A.execid = B.execid";
                    $abnormrl = $this->DB_get_count_join('patrol_execute', 'A', $join, [
                        'A.cycid' => ['IN', $cycidArr['cycid']],
                        'A.assetnum' => $assnum,
                        'B.result' => ['neq', '合格'],
                    ]);
                    $patrolInfo[$k]['is_normal'] = $abnormrl > 0 ? '是' : '否';
                    $patrolInfo[$k]['abnormal'] = $abnormrl;
                }
            }
        }
        //不良事件
        $adverseInfo = $this->DB_get_all('adverse_info',
            'report_date,report_from,express,consequence,reporter,sign,cause,situation,expected,addtime',
            ['assid' => $assid], '', 'addtime asc', '');
        foreach ($adverseInfo as $k => $v) {
            $adverseInfo[$k]['sort_date'] = date('Y-m-d', strtotime($v['addtime']));
            $adverseInfo[$k]['sort_time'] = $v['addtime'];
        }
        //质控计划
        $fields = "A.qsid,A.assid,A.plan_num,A.do_date,A.end_date,A.is_cycle,A.cycle,A.period,A.qtemid,A.username,A.plan_name,A.start_date,A.is_start,A.addtime,B.addtime as overtime,B.result,B.report,B.date";
        $join = "LEFT JOIN sb_quality_details as B on A.qsid = B.qsid";
        $where['A.assid'] = $assid;
        $records = $this->DB_get_all_join('quality_starts', 'A', $fields, $join, $where, '', 'A.addtime asc',
            '');
        foreach ($records as $k => $v) {
            if (is_null($v['is_cycle'])) {
                $records[$k]['is_cycle'] = '';
            } else {
                if ($v['is_cycle'] == 1) {
                    $records[$k]['is_cycle'] = '是';
                    $records[$k]['period'] = '第 ' . $v['period'] . ' 期';
                } else {
                    $records[$k]['end_date'] = $v['date'];
                    $records[$k]['is_cycle'] = '否';
                }
            }
            $records[$k]['is_start'] = $v['is_start'] == 0 ? '未启用' : ($v['is_start'] == 1 ? '执行中' : ($v['is_start'] == 2 ? '已暂停' : ($v['is_start'] == 3 ? '已完成' : '已结束')));
            $records[$k]['sort_date'] = date('Y-m-d', strtotime($v['addtime']));
            $records[$k]['sort_time'] = $v['addtime'];
            if ($v['result']) {
                $records[$k]['result'] = '合格';
            } else {
                $records[$k]['result'] = '不合格';
            }
        }
        //计量计划
        $fields = "A.mpid,A.assid,A.plan_num,A.mcid,A.cycle,A.test_way,A.respo_user,A.status as plan_status,A.addtime,B.this_date,B.status as result_status,B.company,B.money,B.test_person,B.result";
        $join = "LEFT JOIN sb_metering_result as B on A.mpid = B.mpid";
        $where['A.assid'] = $assid;
        $meterings = $this->DB_get_all_join('metering_plan', 'A', $fields, $join, $where, '', 'A.addtime asc',
            '');
        foreach ($meterings as $k => $v) {
            //查询计量分类
            $mcategory = $this->DB_get_one('metering_categorys', 'mcategory',
                ['mcid' => $v['mcid']]);
            $meterings[$k]['mcategory'] = $mcategory['mcategory'];
            $meterings[$k]['test_way'] = $v['test_way'] == 1 ? '院内' : '院外';
            $meterings[$k]['plan_status_name'] = $v['plan_status'] == 0 ? '暂停' : ($v['plan_status'] == 1 ? '启用' : '已结束');
            $meterings[$k]['result_status_name'] = $v['result_status'] == 0 ? '未执行' : ($v['result_status'] == 1 ? '执行中' : '已结束');
            $meterings[$k]['result'] = ($v['result_status'] == 1 ? ($v['result'] == 0 ? '不合格' : ($v['result'] == 1 ? '合格' : '')) : '');
            $meterings[$k]['sort_date'] = date('Y-m-d', strtotime($v['addtime']));
            $meterings[$k]['sort_time'] = $v['addtime'];
        }
        //借调记录
        $user = $this->getUser();
        $borrowInfo = $this->DB_get_all('assets_borrow', '', ['assid' => $assid]);
        if (!$borrowInfo) {
            $join = 'LEFT JOIN sb_assets_borrow_detail AS B ON A.borid = B.borid';
            $borrowInfo = $this->DB_get_all_join('assets_borrow', 'A', '', $join, ['subsidiary_assid' => $assid]);
        }
        foreach ($borrowInfo as $k => $v) {
            switch ($v['status']) {
                case C('BORROW_STATUS_FAIL'):
                    //                不通过
                    $borrowInfo[$k]['sort_date'] = getHandleTime($v['examine_time']);
                    $borrowInfo[$k]['sort_time'] = getHandleTime($v['examine_time']);
                    $borrowInfo[$k]['over_date'] = getHandleDate($v['examine_time']);
                    $borrowInfo[$k]['statuName'] = '不通过';
                    break;
                case C('BORROW_STATUS_COMPLETE'):
                    //                完成
                    $borrowInfo[$k]['sort_date'] = getHandleTime($v['apply_time']);
                    $borrowInfo[$k]['sort_time'] = getHandleTime($v['apply_time']);
                    $borrowInfo[$k]['over_date'] = getHandleDate($v['give_back_time']);
                    $borrowInfo[$k]['statuName'] = '完成';
                    break;
                case C('BORROW_STATUS_NOT_APPLY'):
                    //                不借入
                    $borrowInfo[$k]['sort_date'] = getHandleTime($v['not_apply_time']);
                    $borrowInfo[$k]['sort_time'] = getHandleTime($v['not_apply_time']);
                    $borrowInfo[$k]['over_date'] = getHandleDate($v['examine_time']);
                    $borrowInfo[$k]['statuName'] = '不借入';
                    break;
                case 0:
                    $borrowInfo[$k]['statuName'] = '待审核';
                    break;
                default:
                    $borrowInfo[$k]['sort_date'] = '-';
                    break;
            }
            $borrowInfo[$k]['apply_department'] = $departname[$v['apply_departid']]['department'];
            $borrowInfo[$k]['apply_time'] = getHandleDate($v['apply_time']);
            foreach ($user as $k1 => $v1) {
                if ($v['apply_userid'] == $v1['userid']) {
                    $borrowInfo[$k]['apply_username'] = $v1['username'];
                }
            }
        }
        //外调记录
        $outsideInfo = $this->DB_get_all('assets_outside', '', ['assid' => $assid]);
        if (!$outsideInfo) {
            $join = 'LEFT JOIN sb_assets_outside_detail AS B ON A.outid = B.outid';
            $outsideInfo = $this->DB_get_all_join('assets_outside', 'A', '', $join, ['subsidiary_assid' => $assid]);
        }
        foreach ($outsideInfo as $k => $v) {
            $outsideInfo[$k]['sort_date'] = getHandleTime($v['apply_time']);
            $outsideInfo[$k]['sort_time'] = getHandleTime($v['apply_time']);
            $outsideInfo[$k]['over_date'] = getHandleDate($v['check_date']);
            $outsideInfo[$k]['apply_time'] = getHandleDate($v['apply_time']);
            $outsideInfo[$k]['check_date'] = getHandleDate($v['check_date']);
            switch ($v['apply_type']) {
                case C('OUTSIDE_CALL_OUT_TYPE'):
                    $outsideInfo[$k]['apply_typeName'] = C('OUTSIDE_CALL_OUT_TYPE_NAME');
                    break;
                case C('OUTSIDE_DONATION_TYPE'):
                    $outsideInfo[$k]['apply_typeName'] = C('OUTSIDE_DONATION_TYPE_NAME');
                    break;
                case C('OUTSIDE_OUTSIDE_SALE_TYPE'):
                    $outsideInfo[$k]['apply_typeName'] = C('OUTSIDE_OUTSIDE_SALE_TYPE_NAME');
                    break;
            }
            if ($v['approve_status'] == C('OUTSIDE_STATUS_ACCEPTANCE_CHECK')) {
                $outsideInfo[$k]['examine_statusName'] = '是';
            } elseif ($v['approve_status'] == C('OUTSIDE_STATUS_FAIL')) {
                $outsideInfo[$k]['examine_statusName'] = '否';
            }
            foreach ($user as $k1 => $v1) {
                if ($v['apply_userid'] == $v1['userid']) {
                    $outsideInfo[$k]['apply_username'] = $v1['username'];
                }
            }
        }

        //附属设备分配
        $subsidiaryInfo = $this->DB_get_all('subsidiary_allot', '', [
            'assid' => $assid,
            'status' => C('SUBSIDIARY_STATUS_COMPLETE'),
            'check_status' => C('SUBSIDIARY_STATUS_ACCEPTANCE_CHECK'),
        ]);
        foreach ($subsidiaryInfo as $k => $v) {
            $subsidiaryInfo[$k]['sort_date'] = $v['check_time'];
            $subsidiaryInfo[$k]['sort_time'] = $v['check_time'];
            $subsidiaryInfo[$k]['department'] = $departname[$v['main_departid']]['department'];
        }
        //合并数组
        $allData = array_merge($transferInfo, $repairInfo, $scrapInfo, $patrolInfo, $adverseInfo, $records, $meterings,
            $borrowInfo, $outsideInfo, $subsidiaryInfo);
        foreach ($allData as $k => $v) {
            $life[$k] = $this->formatLife_data($allData[$k]);
        }
        array_multisort(array_column($life, 'sort_time'), SORT_ASC, $life);
        return $life;
    }

    public function formatLife_data($data)
    {
        $life = [];
        foreach ($data as $k => $v) {
            switch ($k) {
                case 'atid':
                    $life[$k] = $v;
                    $life['title'] = '转科记录';
                    $life['type'] = '1';
                    break;
                case 'repid':
                    $life[$k] = $v;
                    $life['title'] = '维修记录';
                    $life['type'] = '2';
                    break;
                case 'scrid':
                    $life[$k] = $v;
                    $life['title'] = '报废记录';
                    $life['type'] = '3';
                    break;
                case 'patrid':
                    $life[$k] = $v;
                    $life['title'] = '巡查保养';
                    $life['type'] = '4';
                    break;
                case 'report_date':
                    $life[$k] = $v;
                    $life['title'] = '不良事件记录';
                    $life['type'] = '5';
                    break;
                case 'qsid':
                    $life[$k] = $v;
                    $life['title'] = '质控计划';
                    $life['type'] = '6';
                    break;
                case 'mpid':
                    $life[$k] = $v;
                    $life['title'] = '计量计划';
                    $life['type'] = '7';
                    break;
                case 'borid':
                    $life[$k] = $v;
                    $life['title'] = '借调记录';
                    $life['type'] = '8';
                    break;
                case 'outid':
                    $life[$k] = $v;
                    $life['title'] = '外调记录';
                    $life['type'] = '9';
                    break;
                case 'allotid':
                    $life[$k] = $v;
                    $life['title'] = '附属设备分配';
                    $life['type'] = '10';
                    break;
                default :
                    $life[$k] = $v;
                    break;
            }
        }
        return $life;
    }

    /**
     * Notes:获取设备状态变更记录
     */
    public function getStateRecord()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $where['assid'] = I('post.assid');
        $total = $this->DB_get_count('assets_state_change', $where);
        $fields = 'id,assid,remark,change_time as changeTime';
        $states = $this->DB_get_all('assets_state_change', $fields, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $states;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /*
     * 设置单元格有效性(下拉列表)
     */
    public function setExcelDataListFormat($FormatListColumn)
    {
        $arr = [];
        foreach ($FormatListColumn as $k => $v) {
            if ($v == '辅助分类' || $v == '财务分类' || $v == '资金来源' || $v == '资产来源') {
                switch ($v) {
                    case '辅助分类':
                        $item = 'assets_helpcat';
                        break;
                    case '财务分类':
                        $item = 'assets_finance';
                        break;
                    case '资金来源':
                        $item = 'assets_capitalfrom';
                        break;
                    case '资产来源':
                        $item = 'assets_assfrom';
                        break;
                    default:
                        $item = 'assets_helpcat';
                        break;
                }
                $baseSetting = [];
                include APP_PATH . "Common/cache/basesetting.cache.php";
                $assets_helpcat = $baseSetting['assets'][$item]['value'];
                $arr[$v] = implode(',', $assets_helpcat);
            }
            if ($v == '是否附有资产标志' || $v == '是否急救资产' || $v == '是否特种资产' || $v == '是否计量资产' || $v == '是否有合格证' || $v == '是否有检验报告书' || $v == '是否参与效益分析' || $v == '是否附带说明书') {
                $arr[$v] = '否,是';
            }
        }
        return $arr;
    }

    /*
     * 设置单元格有效性(日期)
     */
    public function setExcelDataDateFormat($FormatDateColumn)
    {
        $arr = [];
        foreach ($FormatDateColumn as $k => $v) {
            if ($v == '出厂日期') {
                $arr['出厂日期'][0] = '1970/01/01';
                $arr['出厂日期'][1] = '2030/12/30';
            }
            if ($v == '开机日期') {
                $arr['开机日期'][0] = '1970/01/01';
                $arr['开机日期'][1] = '2030/12/30';
            }
            if ($v == '签订日期') {
                $arr['签订日期'][0] = '1970/01/01';
                $arr['签订日期'][1] = '2030/12/30';
            }
            if ($v == '购入日期') {
                $arr['购入日期'][0] = '1970/01/01';
                $arr['购入日期'][1] = '2030/12/30';
            }
            if ($v == '验收合格日期') {
                $arr['验收合格日期'][0] = '1970/01/01';
                $arr['验收合格日期'][1] = '2030/12/30';
            }
            if ($v == '保修到期日期') {
                $arr['保修到期日期'][0] = '1970/01/01';
                $arr['保修到期日期'][1] = '2230/12/30';
            }
        }
        return $arr;
    }

    private function formatCategoryValue($catesTree)
    {
        $str = '';
        foreach ($catesTree as $k => $v) {
            if ($v['level'] == 1) {
                $str .= $v['catenum'] . ':' . $v['category'] . ',';
            } else {
                $str .= '  ' . $v['catenum'] . ':' . $v['category'] . ',';
            }
        }
        $str = trim($str, ',');
        return $str;
    }

    private function formatDepartmentValue($departs)
    {
        $str = '';
        foreach ($departs as $k => $v) {
            $str .= $v['departnum'] . ':' . $v['department'] . ',';
        }
        $str = trim($str, ',');
        return $str;
    }

    public function getAllCategotyAndDepartment()
    {
        //查询该医院代码是否存在或在该用户管理范围内
        if (session('isSuper') && C('IS_OPEN_BRANCH')) {
            //查询当前用户所在的医院代码
            $code = $this->DB_get_one('hospital', 'group_concat(hospital_id) as hospital_id',
                ['hospital_id' => ['in', session('manager_hospitalid')], 'is_delete' => 0]);
            $existsid = explode(',', $code['hospital_id']);
        } else {
            //查询当前用户所在的医院代码
            $code = $this->DB_get_one('hospital', 'hospital_id',
                ['hospital_id' => session('job_hospitalid'), 'is_delete' => 0]);
            $existsid = explode(',', $code['hospital_id']);
        }
        $where['hospital_id'] = ['in', $existsid];
        $where['is_delete'] = C('NO_STATUS');
        $categories = $this->DB_get_all('category', 'catid,hospital_id,catenum,category,parentid', $where, '',
            'catid asc', '');
        $categories = $this->noLimitCategory('catid', 'parentid', $categories, $pid = 0, $level = 0);
        $departments = $this->DB_get_all('department', 'departid,hospital_id,departnum,department', $where, '',
            'departid asc');
        $arr['category'] = $categories;
        $arr['department'] = $departments;
        //查询所有医院信息
        $hosInfo = $this->DB_get_all('hospital', 'hospital_id,hospital_code', ['is_delete' => 0]);
        $hoscode = [];
        foreach ($hosInfo as $k => $v) {
            $hoscode[$v['hospital_id']] = $v['hospital_code'];
        }
        $arr['hospitals'] = $hoscode;
        return $arr;
    }

    public function noLimitCategory($idName, $parentidName, $categories, $parent_id = 0, $level = 0, $stop_id = 0)
    {
        //定义数组保存结果
        static $lists = [];
        //遍历数组
        foreach ($categories as $k => $cat) {
            //先判断当前商品分类的id是否等于不需要的那个商品分类的id
            if ($cat[$idName] != $stop_id) {
                //判断当前的商品分类是否属于要找
                if ($cat[$parentidName] == $parent_id) {
                    //将当前层级添加到$cat数组中
                    $cat['level'] = $level;

                    $lists[] = $cat;
                    //当前分类有可能有子分类
                    //递归点
                    $this->noLimitCategory($idName, $parentidName, $categories, $cat[$idName], $level + 1, $stop_id);
                }
            }
        }
        //递归出口：数组遍历结束

        //返回当前结果
        return $lists;
    }

    public function returnLimitDate($data)
    {
        F('assetsData', $data);
        $assetsData = F('assetsData');
        $arr = [];
        $i = 0;
        foreach ($assetsData as $k => $v) {
            if ($i < $this->len && $v) {
                $arr[] = $v;
                $i++;
                unset($assetsData[$k]);
            }
        }
        F('assetsData', $assetsData);
        return $arr;
    }

    public function returnLimitEditData($data)
    {
        F('assetsEditData', $data);
        $assetsData = F('assetsEditData');
        $arr = [];
        $i = 0;
        foreach ($assetsData as $k => $v) {
            if ($i < $this->len && $v) {
                $arr[] = $v;
                $i++;
                unset($assetsData[$k]);
            }
        }
        F('assetsEditData', $assetsData);
        return $arr;
    }

    /*
     * 批量新增设备
     * return array
     */
    public function batchAddAssets()
    {
        Db::execute("SET sql_mode = 'ALLOW_INVALID_DATES'");
        $tempid = trim(I('post.tempid'), ',');
        $tempArr = explode(',', $tempid);
        $hospital_id = session('current_hospitalid');
        //查询当前用户所在的医院代码
        $code = $this->DB_get_one('hospital', 'hospital_code',
            ['hospital_id' => $hospital_id, 'is_delete' => 0]);
        $hospital_code = $code['hospital_code'];
        $cdwhere['is_delete'] = C('NO_STATUS');
        $cdwhere['hospital_id'] = $hospital_id;
        //查询所有现有的设备原编码
        $assorignum = $this->DB_get_all('assets_info', 'assorignum,serialnum,assorignum_spare,inventory_label_id',
            $cdwhere);
        //查设备分类
        $category = $this->DB_get_all('category', 'catid,hospital_id,catenum,category', $cdwhere, '', 'catid asc', '');
        //查询所有科室
        $department = $this->DB_get_all('department', 'departid,hospital_id,departnum,department', $cdwhere, '',
            'departid asc', '');
        //把资产模块属性设置返回
        $basesetting = $this->DB_get_all('base_setting', '*', ['module' => 'assets'], '', '');
        //折旧方式
        $depreciation_method = ['平均折旧法', '工作量法', '双倍余额递减法', '年数总额法'];
        $helpcat = $finance = $capitalfrom = $assfrom = [];
        foreach ($basesetting as $k => $v) {
            if ($v['set_item'] == 'assets_helpcat') {
                //辅助分类
                $helpcat = json_decode($v['value'], true);
            }
            if ($v['set_item'] == 'assets_finance') {
                //财务分类
                $finance = json_decode($v['value'], true);
            }
            if ($v['set_item'] == 'assets_capitalfrom') {
                //资金来源
                $capitalfrom = json_decode($v['value'], true);
            }
            if ($v['set_item'] == 'assets_assfrom') {
                //设备来源
                $assfrom = json_decode($v['value'], true);
            }
        }
        $oldInventoryLabelIds = $oldserialnum = $oldassorignum = $oldassorignum_spare = $catenum = $catename = $departnum = $departname = $helpcatname = $financename = $capitalfromname = $assfromname = $methodname = [];
        foreach ($assorignum as $k => $v) {
            if ($v['serialnum']) {
                $oldserialnum[] = $v['serialnum'];
            }
            if ($v['assorignum']) {
                $oldassorignum[] = $v['assorignum'];
            }
            if ($v['assorignum_spare']) {
                $oldassorignum_spare[] = $v['assorignum_spare'];
            }
            if ($v['inventory_label_id']) {
                $oldInventoryLabelIds[] = $v['inventory_label_id'];
            }
        }

        foreach ($category as $k => $v) {
            $catenum[$v['hospital_id']][$v['catenum']] = $v['catid'];
            $catename[$v['hospital_id']][$v['category']] = $v['catid'];
        }
        foreach ($department as $k => $v) {
            $departnum[$v['hospital_id']][$v['departnum']] = $v['departid'];
            $departname[$v['hospital_id']][$v['department']] = $v['departid'];
        }
        foreach ($helpcat as $k => $v) {
            $helpcatname[$v] = $k;
        }
        foreach ($finance as $k => $v) {
            $financename[$v] = $k;
        }
        foreach ($capitalfrom as $k => $v) {
            $capitalfromname[$v] = $k;
        }
        foreach ($assfrom as $k => $v) {
            $assfromname[$v] = $k;
        }
        foreach ($depreciation_method as $k => $v) {
            $methodname[$v] = $k + 1;
        }

        //查询医院下的科室和分类
        $hosvsdepart = $this->DB_get_all('department', 'hospital_id,departid',
            ['hospital_id' => $hospital_id, 'is_delete' => 0]);
        $hosvscate = $this->DB_get_all('category', 'hospital_id,catid',
            ['hospital_id' => $hospital_id, 'is_delete' => 0]);
        $hosvsdepartid = $hosvscatid = [];
        foreach ($hosvsdepart as $k => $v) {
            $hosvsdepartid[$v['hospital_id']][] = $v['departid'];
        }
        foreach ($hosvscate as $k => $v) {
            $hosvscatid[$v['hospital_id']][] = $v['catid'];
        }
        $num = 0;
        $saveTempidArr = [];
        foreach ($tempArr as $k => $v) {
            //按每次最多不超过$this->len条的数据获取临时表数据进行保存操作
            if ($num < $this->len) {
                $saveTempidArr[] = $v;
                $num++;
            }
            if ($num == $this->len) {
                //进行一次设备入库操作
                $res[] = $this->assetsStorage($saveTempidArr, $hospital_code, $hospital_id, $hosvsdepartid, $hosvscatid,
                    $oldserialnum, $oldassorignum, $catenum, $catename, $departnum, $departname, $helpcatname,
                    $financename, $capitalfromname, $assfromname, $methodname, $oldassorignum_spare,
                    $oldInventoryLabelIds);
                //重置
                $num = 0;
                $saveTempidArr = [];
            }
        }
        if ($saveTempidArr) {
            $res[] = $this->assetsStorage($saveTempidArr, $hospital_code, $hospital_id, $hosvsdepartid, $hosvscatid,
                $oldserialnum, $oldassorignum, $catenum, $catename, $departnum, $departname, $helpcatname, $financename,
                $capitalfromname, $assfromname, $methodname, $oldassorignum_spare, $oldInventoryLabelIds);

        }

        $TasksController = new TasksController();
        $TasksController->set_offline_suppliers();
        $msg = $res[0] ? '保存数据成功！' : '暂无数据保存！';
        return ['status' => 1, 'msg' => $msg];
    }

    /**
     * Notes: 设备批量入库方法
     *
     * @param $saveTempidArr   array 要保存的临时表设备ID
     * @param $oldassorignum   array 已有设备的assorignum
     * @param $catenum         array 设备分类编码与catid对应的数组
     * @param $catename        array 设备分类名称与catid对应的数组
     * @param $departnum       array 科室编码与departid对应的数组
     * @param $departname      array 科室名称与departid对应的数组
     * @param $helpcatname     array 辅助分类与key对应的数组
     * @param $financename     array 财务分类与key对应的数组
     * @param $capitalfromname array 资金来源与key对应的数组
     * @param $assfromname     array 资产来源与key对应的数组
     * @param $methodname      array 折旧方法与key对应的数组
     *
     * @return array
     */
    public function assetsStorage(
        $saveTempidArr,
        $hospital_code,
        $hospital_id,
        $hosvsdepartid,
        $hosvscatid,
        $oldserialnum,
        $oldassorignum,
        $catenum,
        $catename,
        $departnum,
        $dname,
        $helpcatname,
        $financename,
        $capitalfromname,
        $assfromname,
        $methodname,
        $oldassorignum_spare,
        $oldInventoryLabelIds
    )
    {
        $assetsLists = $this->DB_get_all('assets_info_upload_temp', '*',
            ['tempid' => ['in', $saveTempidArr], 'is_save' => 0]);
        //过滤掉必填字段为空的数据
        $need = ['cate', 'assets', 'finance', 'department', 'capitalfrom', 'assfrom', 'buy_price'];
        foreach ($assetsLists as $k => $v) {
            foreach ($v as $k1 => $v1) {
                if (in_array($k1, $need) && is_null($v1)) {
                    //过滤掉必填字段没填写的数据
                    unset($assetsLists[$k]);
                }
            }
        }
        foreach ($assetsLists as $k => $v) {
            if ($v['hospital_code'] != $hospital_code) {
                //过滤掉医院代码不同的数据
                unset($assetsLists[$k]);
            }
        }
        foreach ($assetsLists as $k => $v) {
            if ($v['serialnum'] and $v['serialnum'] != '' and $v['serialnum'] != '/' and $v['serialnum'] != '\\') {
                if (in_array($v['serialnum'], $oldserialnum, true)) {
                    //过滤掉与系统设备序列号重复的数据
                    unset($assetsLists[$k]);
                }
            } else {
                $assetsLists[$k]['serialnum'] = '';
            }

            if ($v['inventory_label_id'] && $v['inventory_label_id'] != '') {
                if (in_array($v['inventory_label_id'], $oldInventoryLabelIds, true)) {
                    //过滤掉与系统标签ID重复的数据
                    unset($assetsLists[$k]);
                }
            }
        }
        foreach ($assetsLists as $k => $v) {
            if ($v['assorignum'] and $v['assorignum'] != '/' and $v['assorignum'] != '' and $v['assorignum'] != '\\') {
                if (in_array($v['assorignum'], $oldassorignum, true) || in_array($v['assorignum'], $oldassorignum_spare,
                        true)) {
                    //过滤掉与系统设备原编码或原编码(备用)重复的数据
                    unset($assetsLists[$k]);
                    continue;
                }
            } else {
                $assetsLists[$k]['assorignum'] = '';
            }
            if ($v['assorignum_spare'] and $v['assorignum_spare'] != '/' and $v['assorignum_spare'] != '' and $v['assorignum_spare'] != '\\') {
                if (in_array($v['assorignum_spare'], $oldassorignum, true) || in_array($v['assorignum_spare'],
                        $oldassorignum_spare, true)) {
                    //过滤掉与系统设备原编码或原编码(备用)重复的数据
                    unset($assetsLists[$k]);
                    continue;
                }
            } else {
                $assetsLists[$k]['assorignum_spare'] = '';
            }
        }
        foreach ($assetsLists as $k => $v) {
            if (!$catenum[$hospital_id][$v['cate']] && !$catename[$hospital_id][$v['cate']]) {
                //过滤掉不存在系统设备分类的数据
                unset($assetsLists[$k]);
            }
        }
        foreach ($assetsLists as $k => $v) {
            if (!$departnum[$hospital_id][$v['department']] && !$dname[$hospital_id][$v['department']]) {
                //过滤掉不存在科室的数据
                unset($assetsLists[$k]);
            }
        }
        foreach ($assetsLists as $k => $v) {
            if ($v['helpcat'] != '') {
                if (is_null($helpcatname[$v['helpcat']])) {
                    //过滤掉不存在辅助分类的数据
                    unset($assetsLists[$k]);
                }
            }
        }
        foreach ($assetsLists as $k => $v) {
            if ($v['depreciation_method']) {
                if (is_null($methodname[$v['depreciation_method']])) {
                    //过滤掉不存在折旧方式的数据
                    unset($assetsLists[$k]);
                }
            }
        }
        //分类ID不在对应医院ID的要排除掉
        foreach ($assetsLists as $k => $v) {
            $catid = $catename[$hospital_id][$v['cate']] ? $catename[$hospital_id][$v['cate']] : $catenum[$hospital_id][$v['cate']];
            if (!in_array($catid, $hosvscatid[$hospital_id])) {
                unset($assetsLists[$k]);
            }
        }
        //科室ID不在对应医院ID的要排除掉
        foreach ($assetsLists as $k => $v) {
            $departid = $dname[$hospital_id][$v['department']] ? $dname[$hospital_id][$v['department']] : $departnum[$hospital_id][$v['department']];
            if (!in_array($departid, $hosvsdepartid[$hospital_id])) {
                unset($assetsLists[$k]);
            }
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $insertData = [];
        //组织数据插入正式表
        foreach ($assetsLists as $k => $v) {
            if ($v['serialnum'] and $v['serialnum'] != '' and $v['serialnum'] != '/') {
                if (in_array($v['serialnum'], $oldserialnum, true)) {
                    //过滤掉与系统设备序列号重复的数据
                    unset($assetsLists[$k]);
                    continue;
                }
            }
            if ($v['assorignum'] and $v['assorignum'] != '' and $v['assorignum'] != '/') {
                if (in_array($v['assorignum'], $oldassorignum, true)) {
                    unset($assetsLists[$k]);
                    continue;
                }
            }
            $insertData['hospital_id'] = $hospital_id;
            $insertData['catid'] = $catename[$hospital_id][$v['cate']] ? $catename[$hospital_id][$v['cate']] : $catenum[$hospital_id][$v['cate']];
            $insertData['assorignum'] = $v['assorignum'];
            $insertData['assets'] = $v['assets'];
            $insertData['assets_level'] = $v['assets_level'];
            $insertData['helpcatid'] = $helpcatname[$v['helpcat']];
            if ($financename[$v['finance']]) {
                $insertData['financeid'] = $financename[$v['finance']];
            }
            $insertData['brand'] = $v['brand'];
            $insertData['model'] = $v['model'];
            $insertData['assorignum_spare'] = $v['assorignum_spare'];
            $insertData['patrol_xc_cycle'] = $v['patrol_xc_cycle'];
            $insertData['patrol_pm_cycle'] = $v['patrol_pm_cycle'];
            $insertData['quality_cycle'] = $v['quality_cycle'];
            $insertData['metering_cycle'] = $v['metering_cycle'];
            $insertData['unit'] = $v['unit'];
            $insertData['serialnum'] = $v['serialnum'];
            $insertData['assetsrespon'] = $v['assetsrespon'];
            $insertData['departid'] = $dname[$hospital_id][$v['department']] ? $dname[$hospital_id][$v['department']] : $departnum[$hospital_id][$v['department']];
            $insertData['address'] = $departname[$insertData['departid']]['address'] ? $departname[$insertData['departid']]['address'] : '';
            $insertData['managedepart'] = $departname[$insertData['departid']]['department'] ? $departname[$insertData['departid']]['department'] : '';
            $insertData['factorynum'] = $v['factorynum'];
            $insertData['factorydate'] = $v['factorydate'];
            $insertData['opendate'] = $v['opendate'];
            $insertData['storage_date'] = $v['storage_date'];
            if ($capitalfromname[$v['capitalfrom']]) {
                $insertData['capitalfrom'] = $capitalfromname[$v['capitalfrom']];
            }
            if ($assfromname[$v['assfrom']]) {
                $insertData['assfromid'] = $assfromname[$v['assfrom']];
            }
            $insertData['invoicenum'] = $v['invoicenum'];
            $insertData['registration'] = $v['registration'];
            $insertData['buy_price'] = $v['buy_price'];
            $insertData['expected_life'] = $v['expected_life'];
            $insertData['residual_value'] = $v['residual_value'];
            $insertData['is_firstaid'] = $v['is_firstaid'];
            $insertData['is_special'] = $v['is_special'];
            $insertData['is_metering'] = $v['is_metering'];
            $insertData['is_qualityAssets'] = $v['is_qualityAssets'];
            $insertData['is_patrol'] = $v['is_patrol'];
            $insertData['is_benefit'] = $v['is_benefit'];
            $insertData['is_lifesupport'] = $v['is_lifesupport'];
            $insertData['guarantee_date'] = $v['guarantee_date'];
            $insertData['depreciation_method'] = $methodname[$v['depreciation_method']];
            $insertData['depreciable_lives'] = $v['depreciable_lives'];
            $insertData['remark'] = $v['remark'];
            $insertData['adduser'] = session('username');
            $insertData['adddate'] = time();
            $insertData['inventory_label_id'] = $v['inventory_label_id'];
            //新增付款情况 日期 和国产&进口
            $insertData['paytime'] = $v['paytime'];
            $insertData['pay_status'] = $v['pay_status'];
            $insertData['is_domestic'] = $v['is_domestic'];
            // 折旧额计算
            $depreciation = $this->depreciation([
                'storage_date' => $v['storage_date'],
                'buy_price' => $v['buy_price'],
                'residual_value' => $v['residual_value'],
                'depreciation_method' => $v['depreciation_method'],
                'depreciable_lives' => $v['depreciable_lives'],
            ]);
            if ($depreciation) {
                $insertData['depreciable_quota_m'] = $depreciation['depreciable_quota_m'];
                $insertData['depreciable_quota_count'] = $depreciation['depreciable_quota_count'];
                $insertData['net_asset_value'] = $depreciation['net_asset_value'];
            }
            $assid = $this->insertData('assets_info', $insertData);
            if ($assid) {
                //记录入库信息到状态变更表
                $this->updateAssetsStatus($assid, $status = '0', $remark = '设备入库');
                if ($v['assorignum']) {
                    $oldassorignum[] = $v['assorignum'];
                }
                if ($v['serialnum']) {
                    $oldserialnum[] = $v['serialnum'];
                }
                //生产厂家信息入库
                $factoryData['assid'] = $assid;
                $factoryData['factory'] = $v['factory'];
                $factoryData['factory_user'] = $v['factory_user'];
                $factoryData['factory_tel'] = $v['factory_tel'];
                $factoryData['supplier'] = $v['supplier'];
                $factoryData['supp_user'] = $v['supp_user'];
                $factoryData['supp_tel'] = $v['supp_tel'];
                $factoryData['repair'] = $v['repair'];
                $factoryData['repa_user'] = $v['repa_user'];
                $factoryData['repa_tel'] = $v['repa_tel'];
                $factoryData['adddate'] = time();
                $newafid = $this->insertData('assets_factory', $factoryData);
                //生成设备编码
                $newcat = $this->DB_get_one('category', 'catenum', ['catid' => $insertData['catid']]);
                $newdepart = $this->DB_get_one('department', 'departnum',
                    ['departid' => $insertData['departid']]);
                $newnum['afid'] = $newafid;
                $newnum['assnum'] = $newcat['catenum'] . $newdepart['departnum'] . $assid;
                $newnum['barcore'] = $newnum['assnum'];
                $this->updateData('assets_info', $newnum, ['assid' => $assid]);
                //修改临时表状态未已上传
                $this->updateData('assets_info_upload_temp',
                    ['is_save' => 1, 'edituser' => session('username'), 'editdate' => date('Y-m-d H:i:s', time())],
                    ['tempid' => $v['tempid']]);
            }
        }
        return $newafid;
    }

    /*
     * 每月更新设备折旧额
     * return array
     */
    public function renew_depreciation()
    {
        $data = $this->DB_get_all('assets_info',
            'depreciation_method,depreciable_lives,buy_price,storage_date,residual_value,assid',
            ['buy_price' => ['GT', 0]]);
        foreach ($data as $value) {
            $depreciation = $this->depreciation($value);
            if ($depreciation) {
                $this->updateData('assets_info', $depreciation, ['assid' => $value['assid']]);
            }
        }
    }

    /*
     * 计算设备折旧额
     * return array
     */
    public function depreciation($data = [])
    {
        $depreciation_method = $data['depreciation_method'];
        $buy_price = $data['buy_price'];
        $depreciable_lives = $data['depreciable_lives'];
        if (!$depreciable_lives) {
            // 未设置折旧年限
            return false;
        }
        if (!$buy_price) {
            // 未设置设备原值或设备原值为0
            return false;
        }
        if (!$depreciation_method) {
            // 未选中折旧方式
            return false;
        }
        $residual_value = $data['residual_value'];
        if (!$residual_value) {
            // 未设置残净值率则默认为0
            $residual_value = 0;
        }
        $storage_date = $data['storage_date'];
        if (!$storage_date) {
            // 未设置入库日期
            return false;
        } else {
            $storage_date = strtotime($storage_date);
        }
        $new_date = time();
        switch ($depreciation_method) {
            case '1':
            case 1:
            case '平均折旧法':
                $depreciable_quota_m = $buy_price * (100 - $residual_value) / 100 / $depreciable_lives / 12;
                $depreciable_quota_y = $depreciable_quota_m * 12;
                $y = date('Y', $new_date) - date('Y', $storage_date);
                $m = date('m', $new_date) - date('m', $storage_date);
                $depreciable_quota_count = $depreciable_quota_m * ($y * 12 + $m);
                $net_asset_value = $buy_price - $depreciable_quota_count;
                $depreciable_quota_m = round($depreciable_quota_m, 2);
                break;
            case '3':
            case 3:
            case '双倍余额递减法':
            case '加速折旧法(双倍余额递减法)':
                $depreciable_quota_m = '';
                $depreciable_quota_y = '';
                $depreciable_quota = 0;//双倍余额递减法折旧额
                $depreciable_quota_count = 0;//已提折旧额
                $y = date('Y', $new_date) - date('Y', $storage_date) - 1;
                $m = date('m', $new_date) - date('m', $storage_date);
                if ($m > 0) {
                    $y = $y + 1;
                }
                $m = abs($m);
                for ($i = 0; i < $depreciable_lives - 2; $i++) {
                    if ($y > $i) {
                        $depreciable_quota_y = ($buy_price - $depreciable_quota) * 2 / $depreciable_lives;
                        $depreciable_quota_m = $depreciable_quota_y / 12;
                        $depreciable_quota_count = $depreciable_quota = $depreciable_quota + $depreciable_quota_y;
                    } else {
                        break;
                    }
                }
                for ($j = $i; $depreciable_lives - 3 < $j && $j < $depreciable_lives; $j++) {
                    if ($i != $y) {
                        $depreciable_quota_y = ($buy_price - $depreciable_quota - $buy_price * $residual_value / 100) / 2;
                        $depreciable_quota_m = $depreciable_quota_y / 12;
                        $depreciable_quota_count = $depreciable_quota_count + $depreciable_quota_y;
                    } else {
                        break;
                    }
                }
                $depreciable_quota_count = $depreciable_quota_count + $depreciable_quota_m * $m;
                if ($depreciable_quota_y != '') {
                    $depreciable_quota_y = round($depreciable_quota_y, 2);
                }
                if ($depreciable_quota_m != '') {
                    $depreciable_quota_m = round($depreciable_quota_m, 2);
                }
                $net_asset_value = $buy_price - $depreciable_quota_count;
                break;
            case '4':
            case 4:
            case '年数总额法':
            case '加速折旧法(年数总额法)':
                $depreciable_quota_m = '';
                $depreciable_quota_y = '';
                $depreciable_quota = 0;//折旧额
                $y = date('Y', $new_date) - date('Y', $storage_date);
                $m = date('m', $new_date) - date('m', $storage_date);
                if ($m > 0) {
                    $y = $y + 1;
                }
                for ($i = 0; $i < $y; $i++) {
                    $depreciable_quota_y = ($buy_price - $buy_price * $residual_value / 100) * ($depreciable_lives - $i) / ($depreciable_lives * (1 + $depreciable_lives) / 2);
                    $depreciable_quota_m = $depreciable_quota_y / 12;
                    $depreciable_quota = $depreciable_quota + $depreciable_quota_y;
                }
                $depreciable_quota_count = $depreciable_quota = $depreciable_quota + $depreciable_quota_m * $m;
                if ($depreciable_quota_y != '') {
                    $depreciable_quota_y = round($depreciable_quota_y, 2);
                }
                if ($depreciable_quota_m != '') {
                    $depreciable_quota_m = round($depreciable_quota_m, 2);
                }
                $net_asset_value = ($buy_price - $buy_price * $residual_value / 100) - $depreciable_quota;
                break;
        }
        if ($y > $depreciable_lives) {
            //超过折旧率
            $depreciable_quota_y = 0;
            $depreciable_quota_m = 0;
            $net_asset_value = $buy_price * $residual_value / 100;
        }
        return [
            'depreciable_quota_m' => $depreciable_quota_m,
            'depreciable_quota_count' => round($depreciable_quota_count, 2),
            'net_asset_value' => $net_asset_value,
        ];
    }

    /*
     * 批量编辑设备
     * return array
     */
    public function batchEditAssets()
    {
        $assnum = I('post.assnum');
        $assets = I('post.assets');
        $byname = I('post.byname');
        $assorignum = I('post.assorignum');
        $catid = I('post.catid');
        $brand = I('POST.brand');
        $plaofpro = I('POST.plaofpro');
        $country = I('POST.country');
        $model = I('POST.model');
        $unit = I('POST.unit');

        $department = I('POST.department');
        $managedepart = I('POST.managedepart');
        $assetsrespon = I('POST.assetsrespon');
        $serialnum = I('POST.serialnum');
        $helpcatid = I('POST.helpcatid');
        $type = I('POST.assetstype');
        $financeid = I('POST.financeid');
        $factorynum = I('POST.factorynum');
        $factorydate = I('POST.factorydate');

        $registnum = I('POST.registnum');
        $opendate = I('POST.opendate');
        $capitalfrom = I('POST.capitalfrom');
        $assfromid = I('POST.assfromid');
        $invoicenum = I('POST.invoicenum');
        $invoiceprice = I('POST.invoiceprice');
        $is_attach = I('POST.is_attach');
        $is_firstaid = I('POST.is_firstaid');
        $is_special = I('POST.is_special');
        $is_metering = I('POST.is_metering');

        $is_standard = I('POST.is_standard');
        $is_inspection = I('POST.is_inspection');
        $is_benefit = I('POST.is_benefit');
        $is_lifesupport = I('POST.is_lifesupport');
        $contract = I('POST.contract');
        $buy_price = I('POST.buy_price');
        $con_date = I('POST.con_date');
        $buy_date = I('POST.buy_date');
        $standard_date = I('POST.standard_date');
        $guarantee_date = I('POST.guarantee_date');

        $factory = I('POST.factory');
        $factory_tel = I('POST.factory_tel');
        $supplier = I('POST.supplier');
        $supp_user = I('POST.supp_user');
        $supp_tel = I('POST.supp_tel');
        $repair = I('POST.repair');
        $repa_user = I('POST.repa_user');
        $repa_tel = I('POST.repa_tel');
        if (!$assnum) {
            return ['status' => -1, 'msg' => '设备编码不能为空！'];
        }
        $assnum = explode('|', trim($assnum, '|'));
        if ($assets) {
            $assets = explode('|', trim($assets, '|'));
        }
        if ($byname) {
            $byname = explode('|', trim($byname, '|'));
        }
        if ($assorignum) {
            $assorignum = explode('|', trim($assorignum, '|'));
        }
        if ($catid) {
            $catid = explode('|', trim($catid, '|'));
        }
        if ($brand) {
            $brand = explode('|', trim($brand, '|'));
        }
        if ($plaofpro) {
            $plaofpro = explode('|', trim($plaofpro, '|'));
        }
        if ($country) {
            $country = explode('|', trim($country, '|'));
        }
        if ($model) {
            $model = explode('|', trim($model, '|'));
        }
        if ($unit) {
            $unit = explode('|', trim($unit, '|'));
        }
        if ($department) {
            $department = explode('|', trim($department, '|'));
        }
        if ($managedepart) {
            $managedepart = explode('|', trim($managedepart, '|'));
        }
        if ($assetsrespon) {
            $assetsrespon = explode('|', trim($assetsrespon, '|'));
        }
        if ($serialnum) {
            $serialnum = explode('|', trim($serialnum, '|'));
        }
        if ($helpcatid) {
            $helpcatid = explode('|', trim($helpcatid, '|'));
        }
        if ($type) {
            $type = explode('|', trim($type, '|'));
        }
        if ($financeid) {
            $financeid = explode('|', trim($financeid, '|'));
        }
        if ($factorynum) {
            $factorynum = explode('|', trim($factorynum, '|'));
        }
        if ($factorydate) {
            $factorydate = explode('|', trim($factorydate, '|'));
        }
        if ($registnum) {
            $registnum = explode('|', trim($registnum, '|'));
        }
        if ($opendate) {
            $opendate = explode('|', trim($opendate, '|'));
        }
        if ($capitalfrom) {
            $capitalfrom = explode('|', trim($capitalfrom, '|'));
        }
        if ($assfromid) {
            $assfromid = explode('|', trim($assfromid, '|'));
        }
        if ($invoicenum) {
            $invoicenum = explode('|', trim($invoicenum, '|'));
        }
        if ($invoiceprice) {
            $invoiceprice = explode('|', trim($invoiceprice, '|'));
        }
        if ($is_attach) {
            $is_attach = explode('|', trim($is_attach, '|'));
        }
        if ($is_firstaid) {
            $is_firstaid = explode('|', trim($is_firstaid, '|'));
        }
        if ($is_special) {
            $is_special = explode('|', trim($is_special, '|'));
        }
        if ($is_metering) {
            $is_metering = explode('|', trim($is_metering, '|'));
        }
        if ($is_standard) {
            $is_standard = explode('|', trim($is_standard, '|'));
        }
        if ($is_inspection) {
            $is_inspection = explode('|', trim($is_inspection, '|'));
        }
        if ($is_benefit) {
            $is_benefit = explode('|', trim($is_benefit, '|'));
        }
        if ($is_lifesupport) {
            $is_lifesupport = explode('|', trim($is_lifesupport, '|'));
        }
        if ($contract) {
            $contract = explode('|', trim($contract, '|'));
        }
        if ($buy_price) {
            $buy_price = explode('|', trim($buy_price, '|'));
        }
        if ($con_date) {
            $con_date = explode('|', trim($con_date, '|'));
        }
        if ($buy_date) {
            $buy_date = explode('|', trim($buy_date, '|'));
        }
        if ($standard_date) {
            $standard_date = explode('|', trim($standard_date, '|'));
        }
        if ($guarantee_date) {
            $guarantee_date = explode('|', trim($guarantee_date, '|'));
        }
        if ($factory) {
            $factory = explode('|', trim($factory, '|'));
        }
        if ($factory_tel) {
            $factory_tel = explode('|', trim($factory_tel, '|'));
        }
        if ($supplier) {
            $supplier = explode('|', trim($supplier, '|'));
        }
        if ($supp_user) {
            $supp_user = explode('|', trim($supp_user, '|'));
        }
        if ($supp_tel) {
            $supp_tel = explode('|', trim($supp_tel, '|'));
        }
        if ($repair) {
            $repair = explode('|', trim($repair, '|'));
        }
        if ($repa_user) {
            $repa_user = explode('|', trim($repa_user, '|'));
        }
        if ($repa_tel) {
            $repa_tel = explode('|', trim($repa_tel, '|'));
        }
        //获取所有分类编号
        $cate = $this->DB_get_all('category', 'catenum,catid', ['1'], '', '', '');
        $cateArr = $catIdArr = [];
        foreach ($cate as $k => $v) {
            $cateArr[] = $v['catenum'];
            $catIdArr[$v['catenum']] = $v['catid'];
        }
        //获取所有部门编号
        $depart = $this->DB_get_all('department', 'departid,departnum,department,address', ['1'], '', '', '');
        $departArr = $departIdArr = [];
        foreach ($depart as $k => $v) {
            $departArr[$v['department']]['departid'] = $v['departid'];
            $departArr[$v['department']]['address'] = $v['address'];
            $departArr[$v['department']]['departnum'] = $v['departnum'];
        }
        foreach ($assnum as $k => $v) {
            if (!$v) {
                return ['status' => -1, 'msg' => '资产编码不能为空！'];
                break;
            }
            if ($assets) {
                if ($assets[$k] == '' || $assets[$k] == '--') {
                    return ['status' => -1, 'msg' => '资产名称不能为空！'];
                    break;
                }
            }
            if ($catid) {
                if ($catid[$k] == '--') {
                    return ['status' => -1, 'msg' => '68分类编码不能为空！'];
                    break;
                }
            }
            if ($model) {
                if ($model[$k] == '--') {
                    return ['status' => -1, 'msg' => '规格/型号不能为空！'];
                    break;
                }
            }
            if ($department) {
                if ($department[$k] == '--') {
                    return ['status' => -1, 'msg' => '使用科室不能为空！'];
                    break;
                }
            }
            if ($managedepart) {
                if ($managedepart[$k] == '--') {
                    return ['status' => -1, 'msg' => '管理科室不能为空！'];
                    break;
                }
            }
            if ($assetsrespon) {
                if ($assetsrespon[$k] == '--') {
                    return ['status' => -1, 'msg' => '资产负责人不能为空！'];
                    break;
                }
            }
            if ($contract) {
                if ($contract[$k] == '--') {
                    return ['status' => -1, 'msg' => '合同名称不能为空！'];
                    break;
                }
            }
            if ($buy_price) {
                if ($buy_price[$k] == '--') {
                    return ['status' => -1, 'msg' => '合同价格不能为空！'];
                    break;
                } else {
                    if (!judgeNum($buy_price[$k])) {
                        return ['status' => -1, 'msg' => '合同价格只能为数字！'];
                        break;
                    }
                }
            }
            if ($con_date) {
                if ($con_date[$k] == '--') {
                    return ['status' => -1, 'msg' => '签订日期不能为空！'];
                    break;
                } else {
                    //判断签订日期
                    if ($con_date[$k] > date('Y-m-d')) {
                        return ['status' => -1, 'msg' => '签订日期不能大于今天！'];
                        break;
                    }
                }
            }
            if ($catid) {
                if (!in_array($catid[$k], $cateArr)) {
                    return ['status' => -1, 'msg' => '68分类编码（' . $catid[$k] . '）不存在'];
                    break;
                }
            }
        }
        include APP_PATH . "Common/cache/basesetting.cache.php";
        foreach ($assnum as $k => $v) {
            //查询对应设备的assid
            $assid = $this->DB_get_one('assets_info', 'assid,acid,afid', ['assnum' => $v], '');
            if (!$assid) {
                ['status' => -1, 'msg' => '系统不存在编码为' . $v . '的设备，请修改正确后再做上传保存操作！'];
            }
            $infoData = [];
            $conData = [];
            $facData = [];
            if ($assets) {
                $assets[$k] = ($assets[$k] == '--') ? '' : $assets[$k];
                $infoData['assets'] = $assets[$k];
            }
            if ($assorignum) {
                $assorignum[$k] = ($assorignum[$k] == '--') ? '' : $assorignum[$k];
                $infoData['assorignum'] = $assorignum[$k];
            }
            if ($byname) {
                $byname[$k] = ($byname[$k] == '--') ? '' : $byname[$k];
                $infoData['byname'] = $byname[$k];
            }
            if ($brand) {
                $brand[$k] = ($brand[$k] == '--') ? '' : $brand[$k];
                $infoData['brand'] = $brand[$k];
            }
            if ($plaofpro) {
                $plaofpro[$k] = ($plaofpro[$k] == '--') ? '' : $plaofpro[$k];
                $infoData['plaofpro'] = $plaofpro[$k];
            }
            if ($country) {
                $country[$k] = ($country[$k] == '--') ? '' : $country[$k];
                $infoData['country'] = $country[$k];
            }
            if ($unit) {
                $unit[$k] = ($unit[$k] == '--') ? '' : $unit[$k];
                $infoData['unit'] = $unit[$k];
            }
            if ($serialnum) {
                $serialnum[$k] = ($serialnum[$k] == '--') ? '' : $serialnum[$k];
                $infoData['serialnum'] = $serialnum[$k];
            }
            if ($type) {
                $type[$k] = ($type[$k] == '--') ? '' : $type[$k];
                $infoData['type'] = $type[$k];
            }
            if ($factorynum) {
                $factorynum[$k] = ($factorynum[$k] == '--') ? '' : $factorynum[$k];
                $infoData['factorynum'] = $factorynum[$k];
            }
            if ($factorydate) {
                $factorydate[$k] = ($factorydate[$k] == '--') ? '0' : $factorydate[$k];
                $infoData['factorydate'] = strtotime($factorydate[$k]);
            }
            if ($opendate) {
                $opendate[$k] = ($opendate[$k] == '--') ? '0' : $opendate[$k];
                $infoData['opendate'] = strtotime($opendate[$k]);
            }
            if ($invoicenum) {
                $invoicenum[$k] = ($invoicenum[$k] == '--') ? '' : $invoicenum[$k];
                $infoData['invoicenum'] = $invoicenum[$k];
            }
            if ($invoiceprice) {
                $invoiceprice[$k] = ($invoiceprice[$k] == '--') ? '' : $invoiceprice[$k];
                $infoData['invoiceprice'] = $invoiceprice[$k];
            }
            if ($registnum) {
                $registnum[$k] = ($registnum[$k] == '--') ? '' : $registnum[$k];
                $infoData['registnum'] = $registnum[$k];
            }
            if ($buy_price) {
                $infoData['buy_price'] = $buy_price[$k];
            }
            if ($is_attach) {
                $infoData['is_attach'] = $is_attach[$k];
            }
            if ($is_firstaid) {
                $infoData['is_firstaid'] = $is_firstaid[$k];
            }
            if ($is_special) {
                $infoData['is_special'] = $is_special[$k];
            }
            if ($is_metering) {
                $infoData['is_metering'] = $is_metering[$k];
            }
            if ($is_standard) {
                $infoData['is_standard'] = $is_standard[$k];
            }
            if ($is_inspection) {
                $infoData['is_inspection'] = $is_inspection[$k];
            }
            if ($is_benefit) {
                $infoData['is_benefit'] = $is_benefit[$k];
            }
            if ($is_lifesupport) {
                $infoData['is_lifesupport'] = $is_lifesupport[$k];
            }
            if ($assetsrespon) {
                $infoData['assetsrespon'] = $assetsrespon[$k];
            }
            if ($helpcatid) {
                $infoData['helpcatid'] = $helpcatid[$k];
            }
            if ($financeid) {
                $infoData['financeid'] = $financeid[$k];
            }
            if ($model) {
                $infoData['model'] = $model[$k];
            }
            if ($managedepart) {
                $infoData['managedepart'] = $managedepart[$k];
            }
            if ($capitalfrom) {
                $infoData['capitalfrom'] = $capitalfrom[$k];
            }
            if ($assfromid) {
                $infoData['assfromid'] = $assfromid[$k];
            }
            if ($catid) {
                $infoData['catid'] = $catIdArr[$catid[$k]];
            }
            if ($department) {
                $infoData['departid'] = $departArr[$department[$k]]['departid'];
                $infoData['address'] = $departArr[$department[$k]]['address'];
            }
            //修改厂家信息
            if ($factory) {
                $factory[$k] = ($factory[$k] == '--') ? '' : $factory[$k];
                $facData['factory'] = $factory[$k];
            }
            if ($factory_tel) {
                $factory_tel[$k] = ($factory_tel[$k] == '--') ? '' : $factory_tel[$k];
                $facData['factory_tel'] = $factory_tel[$k];
            }
            if ($supplier) {
                $supplier[$k] = ($supplier[$k] == '--') ? '' : $supplier[$k];
                $facData['supplier'] = $supplier[$k];
            }
            if ($supp_tel) {
                $supp_tel[$k] = ($supp_tel[$k] == '--') ? '' : $supp_tel[$k];
                $facData['supp_tel'] = $supp_tel[$k];
            }
            if ($supp_user) {
                $supp_user[$k] = ($supp_user[$k] == '--') ? '' : $supp_user[$k];
                $facData['supp_user'] = $supp_user[$k];
            }
            if ($repa_user) {
                $repa_user[$k] = ($repa_user[$k] == '--') ? '' : $repa_user[$k];
                $facData['repa_user'] = $repa_user[$k];
            }
            if ($repa_tel) {
                $repa_tel[$k] = ($repa_tel[$k] == '--') ? '' : $repa_tel[$k];
                $facData['repa_tel'] = $repa_tel[$k];
            }
            if ($repair) {
                $repair[$k] = ($repair[$k] == '--') ? '' : $repair[$k];
                $facData['repair'] = $repair[$k];
            }
            if ($repa_user) {
                $repa_user[$k] = ($repa_user[$k] == '--') ? '' : $repa_user[$k];
                $facData['repa_user'] = $repa_user[$k];
            }
            //修改合同信息
            if ($contract) {
                $conData['contract'] = $contract[$k];
            }
            if ($con_date) {
                $conData['con_date'] = strtotime($con_date[$k]);
            }
            if ($buy_price) {
                $conData['price'] = $buy_price[$k];
            }
            if ($buy_date) {
                $buy_date[$k] = ($buy_date[$k] == '--') ? '' : $buy_date[$k];
                $conData['buy_date'] = strtotime($buy_date[$k]);
            }
            if ($standard_date) {
                $standard_date[$k] = ($standard_date[$k] == '--') ? '' : $standard_date[$k];
                $conData['standard_date'] = strtotime($standard_date[$k]);
            }
            if ($guarantee_date) {
                $guarantee_date[$k] = ($guarantee_date[$k] == '--') ? '' : $guarantee_date[$k];
                $conData['guarantee_date'] = strtotime($guarantee_date[$k]);
            }
            if ($infoData) {
                $this->updateData('assets_info', $infoData, ['assid' => $assid['assid']]);
            }
            if ($conData) {
                $this->updateData('assets_contract', $conData, ['acid' => $assid['acid']]);
            }
            if ($facData) {
                $this->updateData('assets_factory', $facData, ['afid' => $assid['afid']]);
            }
        }
        //继续返回下一批数据
        $assetsEditData = F('assetsEditData');
        $arr = [];
        $i = 0;
        foreach ($assetsEditData as $k => $v) {
            if ($i < $this->len && $v) {
                $arr[] = $v;
                $i++;
                unset($assetsEditData[$k]);
            }
        }
        F('assetsEditData', $assetsEditData);
        return ['status' => 1, 'msg' => '保存成功', 'data' => $arr];
    }


    /**
     * Notes: 返回表头(生命历程页面)
     *
     * @param $showFields array 要返回的字段
     *
     * @return array
     */
    public function getAssetsLifeHeader($showFields = [])
    {
        //查询配置文件默认列表显示选项
        $header = [];
        $userid = session('userid');
        if (1) {
            $header[1]['field'] = 'assid';
            $header[1]['title'] = '序号';
            $header[1]['width'] = '65';
            $header[1]['fixed'] = 'left';
            $header[1]['align'] = 'center';
            $header[1]['templet'] = '#serialNumTpl';

            $header[2]['field'] = 'assnum';
            $header[2]['title'] = '设备编号';
            $header[2]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/assnum"] == 'false' ? true : false;
            $header[2]['fixed'] = 'left';
            $header[2]['minWidth'] = '160';
            $header[2]['align'] = 'center';

            $header[3]['field'] = 'assets';
            $header[3]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/assets"] == 'false' ? true : false;
            $header[3]['title'] = '设备名称';
            $header[3]['fixed'] = 'left';
            $header[3]['minWidth'] = '160';
            $header[3]['align'] = 'center';

            $header[4]['field'] = 'assorignum';
            $header[4]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/assorignum"] == 'false' ? true : false;
            $header[4]['title'] = '设备原编码';
            $header[4]['minWidth'] = '140';
            $header[4]['align'] = 'center';

            $header[5]['field'] = 'catid';
            $header[5]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/catid"] == 'false' ? true : false;
            $header[5]['title'] = '设备分类';
            $header[5]['minWidth'] = '160';
            $header[5]['align'] = 'center';

            $header[6]['field'] = 'departid';
            $header[6]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/departid"] == 'false' ? true : false;
            $header[6]['title'] = '使用科室';
            $header[6]['minWidth'] = '130';
            $header[6]['align'] = 'center';

            $header[7]['field'] = 'address';
            $header[7]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/address"] == 'false' ? true : false;
            $header[7]['title'] = '存放地点';
            $header[7]['minWidth'] = '160';
            $header[7]['align'] = 'center';

            $header[8]['field'] = 'managedepart';
            $header[8]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/managedepart"] == 'false' ? true : false;
            $header[8]['title'] = '管理科室';
            $header[8]['minWidth'] = '160';
            $header[8]['align'] = 'center';

            $header[9]['field'] = 'model';
            $header[9]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/model"] == 'false' ? true : false;
            $header[9]['title'] = '规格型号';
            $header[9]['minWidth'] = '140';
            $header[9]['align'] = 'center';

            $header[10]['field'] = 'brand';
            $header[10]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/brand"] == 'false' ? true : false;
            $header[10]['title'] = '品牌';
            $header[10]['minWidth'] = '140';
            $header[10]['align'] = 'center';

            $header[11]['field'] = 'unit';
            $header[11]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/unit"] == 'false' ? true : false;
            $header[11]['title'] = '单位';
            $header[11]['minWidth'] = '80';
            $header[11]['align'] = 'center';

            $header[12]['field'] = 'status';
            $header[12]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/status"] == 'false' ? true : false;
            $header[12]['title'] = '设备当前状态';
            $header[12]['minWidth'] = '120';
            $header[12]['align'] = 'center';

            $header[13]['field'] = 'serialnum';
            $header[13]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/serialnum"] == 'false' ? true : false;
            $header[13]['title'] = '设备序列号';
            $header[13]['minWidth'] = '140';
            $header[13]['align'] = 'center';

            $header[14]['field'] = 'assetsrespon';
            $header[14]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/assetsrespon"] == 'false' ? true : false;
            $header[14]['title'] = '设备负责人';
            $header[14]['minWidth'] = '100';
            $header[14]['align'] = 'center';

            $header[15]['field'] = 'factorynum';
            $header[15]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/factorynum"] == 'false' ? true : false;
            $header[15]['title'] = '出厂编号';
            $header[15]['minWidth'] = '140';
            $header[15]['align'] = 'center';

            $header[16]['field'] = 'factorydate';
            $header[16]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/factorydate"] == 'false' ? true : false;
            $header[16]['title'] = '出厂日期';
            $header[16]['minWidth'] = '120';
            $header[16]['sort'] = true;
            $header[16]['align'] = 'center';

            $header[17]['field'] = 'opendate';
            $header[17]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/opendate"] == 'false' ? true : false;
            $header[17]['title'] = '开机日期';
            $header[17]['minWidth'] = '120';
            $header[17]['sort'] = true;
            $header[17]['align'] = 'center';

            $header[18]['field'] = 'storage_date';
            $header[18]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/storage_date"] == 'false' ? true : false;
            $header[18]['title'] = '入库日期';
            $header[18]['minWidth'] = '120';
            $header[18]['sort'] = true;
            $header[18]['align'] = 'center';

            $header[19]['field'] = 'helpcatid';
            $header[19]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/helpcatid"] == 'false' ? true : false;
            $header[19]['title'] = '辅助分类';
            $header[19]['minWidth'] = '120';
            $header[19]['align'] = 'center';

            $header[20]['field'] = 'financeid';
            $header[20]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/financeid"] == 'false' ? true : false;
            $header[20]['title'] = '财务分类';
            $header[20]['minWidth'] = '90';
            $header[20]['align'] = 'center';

            $header[21]['field'] = 'capitalfrom';
            $header[21]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/capitalfrom"] == 'false' ? true : false;
            $header[21]['title'] = '资金来源';
            $header[21]['minWidth'] = '90';
            $header[21]['align'] = 'center';

            $header[22]['field'] = 'assfromid';
            $header[22]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/assfromid"] == 'false' ? true : false;
            $header[22]['title'] = '设备来源';
            $header[22]['minWidth'] = '90';
            $header[22]['align'] = 'center';

            $header[23]['field'] = 'invoicenum';
            $header[23]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/invoicenum"] == 'false' ? true : false;
            $header[23]['title'] = '发票编号';
            $header[23]['minWidth'] = '140';
            $header[23]['align'] = 'center';

            $header[24]['field'] = 'buy_price';
            $header[24]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/buy_price"] == 'false' ? true : false;
            $header[24]['title'] = '设备原值';
            $header[24]['minWidth'] = '120';
            $header[24]['sort'] = true;
            $header[24]['align'] = 'center';

            $header[25]['field'] = 'expected_life';
            $header[25]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/expected_life"] == 'false' ? true : false;
            $header[25]['title'] = '预计使用年限';
            $header[25]['minWidth'] = '120';
            $header[25]['sort'] = true;
            $header[25]['align'] = 'center';

            $header[26]['field'] = 'residual_value';
            $header[26]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/residual_value"] == 'false' ? true : false;
            $header[26]['title'] = '残净值率';
            $header[26]['minWidth'] = '120';
            $header[26]['sort'] = true;
            $header[26]['align'] = 'center';

            $header[27]['field'] = 'is_firstaid';
            $header[27]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/is_firstaid"] == 'false' ? true : false;
            $header[27]['title'] = '急救设备';
            $header[27]['minWidth'] = '90';
            $header[27]['align'] = 'center';
            $header[27]['templet'] = '#firTpl';

            $header[28]['field'] = 'is_special';
            $header[28]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/is_special"] == 'false' ? true : false;
            $header[28]['title'] = '特种设备';
            $header[28]['minWidth'] = '90';
            $header[28]['align'] = 'center';
            $header[28]['templet'] = '#specTpl';

            $header[29]['field'] = 'is_metering';
            $header[29]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/is_metering"] == 'false' ? true : false;
            $header[29]['title'] = '计量设备';
            $header[29]['minWidth'] = '90';
            $header[29]['align'] = 'center';
            $header[29]['templet'] = '#meteTpl';

            $header[30]['field'] = 'is_qualityAssets';
            $header[30]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/is_qualityAssets"] == 'false' ? true : false;
            $header[30]['title'] = '质控设备';
            $header[30]['minWidth'] = '90';
            $header[30]['align'] = 'center';
            $header[30]['templet'] = '#quaTpl';

            $header[31]['field'] = 'is_benefit';
            $header[31]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/is_benefit"] == 'false' ? true : false;
            $header[31]['title'] = '效益分析设备';
            $header[31]['minWidth'] = '90';
            $header[31]['align'] = 'center';
            $header[31]['templet'] = '#benefitTpl';

            $header[32]['field'] = 'is_lifesupport';
            $header[32]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/is_lifesupport"] == 'false' ? true : false;
            $header[32]['title'] = '生命支持类设备';
            $header[32]['minWidth'] = '120';
            $header[32]['align'] = 'center';
            $header[32]['templet'] = '#lifeTpl';


            $header[33]['field'] = 'guarantee_date';
            $header[33]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/guarantee_date"] == 'false' ? true : false;
            $header[33]['title'] = '保修截止日期';
            $header[33]['minWidth'] = '130';
            $header[33]['sort'] = true;
            $header[33]['align'] = 'center';

            $header[34]['field'] = 'depreciation_method';
            $header[34]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/depreciation_method"] == 'false' ? true : false;
            $header[34]['title'] = '折旧方式';
            $header[34]['minWidth'] = '120';
            $header[34]['align'] = 'center';

            $header[35]['field'] = 'depreciable_lives';
            $header[35]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/depreciable_lives"] == 'false' ? true : false;
            $header[35]['title'] = '折旧年限';
            $header[35]['minWidth'] = '100';
            $header[35]['sort'] = true;
            $header[35]['align'] = 'center';

            $header[36]['field'] = 'factory';
            $header[36]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/factory"] == 'false' ? true : false;
            $header[36]['title'] = '生产厂商';
            $header[36]['minWidth'] = '230';
            $header[36]['align'] = 'center';

            $header[37]['field'] = 'factory_user';
            $header[37]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/factory_user"] == 'false' ? true : false;
            $header[37]['title'] = '厂商联系人';
            $header[37]['minWidth'] = '100';
            $header[37]['align'] = 'center';

            $header[38]['field'] = 'factory_tel';
            $header[38]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/factory_tel"] == 'false' ? true : false;
            $header[38]['title'] = '厂商联系电话';
            $header[38]['minWidth'] = '120';
            $header[38]['align'] = 'center';

            $header[39]['field'] = 'supplier';
            $header[39]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/supplier"] == 'false' ? true : false;
            $header[39]['title'] = '供应商';
            $header[39]['minWidth'] = '230';
            $header[39]['align'] = 'center';

            $header[40]['field'] = 'supp_user';
            $header[40]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/supp_user"] == 'false' ? true : false;
            $header[40]['title'] = '供应商联系人';
            $header[40]['minWidth'] = '120';
            $header[40]['align'] = 'center';

            $header[41]['field'] = 'supp_tel';
            $header[41]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/supp_tel"] == 'false' ? true : false;
            $header[41]['title'] = '供应商联系电话';
            $header[41]['minWidth'] = '130';
            $header[41]['align'] = 'center';

            $header[42]['field'] = 'repair';
            $header[42]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/repair"] == 'false' ? true : false;
            $header[42]['title'] = '维修公司';
            $header[42]['minWidth'] = '230';
            $header[42]['align'] = 'center';

            $header[43]['field'] = 'repa_user';
            $header[43]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/repa_user"] == 'false' ? true : false;
            $header[43]['title'] = '维修联系人';
            $header[43]['minWidth'] = '110';
            $header[43]['align'] = 'center';

            $header[44]['field'] = 'repa_tel';
            $header[44]['hide'] = $_COOKIE[$userid . "#/Lookup/assetsLifeList/repa_tel"] == 'false' ? true : false;
            $header[44]['title'] = '维修联系电话';
            $header[44]['minWidth'] = '120';
            $header[44]['align'] = 'center';

            $header[45]['field'] = 'operation';
            $header[45]['title'] = '操作';
            $header[45]['minWidth'] = '100';
            $header[45]['align'] = 'center';
            $header[45]['fixed'] = 'right';
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


    //生命历程列表
    public function getAssetsLifeList()
    {
        $departids = session('departid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $assets = I('POST.assetsLifeListAssets');
        $assetsNum = I('POST.assetsLifeListAssnum');
        $assetsOrnum = I('POST.assetsLifeListAssorignum');
        $assetsCat = I('POST.assetsLifeListCategory');
        $assetsDep = I('POST.department');
        $assetsDate = I('POST.assetsLifeListAddDate');
        $assetsUser = I('POST.assetsLifeListAdduser');
        $hospital_id = I('POST.hospital_id');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where = " departid in ($departids) ";
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
            $order = 'asc';
        }
        if ($hospital_id) {
            $where .= " and hospital_id = " . $hospital_id;
        } else {
            $where .= " and hospital_id = " . session('current_hospitalid');
        }
        if ($assets) {
            //设备名称搜索
            $where .= " and assets like '%" . $assets . "%'";
        }
        if ($assetsNum) {
            //资产编码搜索
            $where .= " and assnum like '%" . $assetsNum . "%'";
        }
        if ($assetsOrnum) {
            //资产原编码搜索
            $where .= " and assorignum like '%" . $assetsOrnum . "%'";
        }
        if ($assetsCat) {
            //分类搜索
            $catwhere['category'] = ['like', "%$assetsCat%"];
            $res = $this->DB_get_all('category', 'catid', $catwhere, '', 'catid asc', '');
            if ($res) {
                $catids = '';
                foreach ($res as $k => $v) {
                    $catids .= $v['catid'] . ',';
                }
                $catids = trim($catids, ',');
                $where .= " and catid in (" . $catids . ")";
            } else {
                $where .= " and catid in (-1)";
            }
        }
        if ($assetsDep) {
            //部门搜索
            $where .= " and departid in (" . $assetsDep . ")";
        }
        if ($assetsDate) {
            //录入时间搜索
            $pretime = strtotime($assetsDate) - 1;
            $nexttime = strtotime($assetsDate) + 24 * 3600;
            $where .= " and adddate >" . $pretime . ' and adddate <' . $nexttime;
        }
        if ($assetsUser != null) {
            //录入人员搜索
            $where .= " and adduser =" . "'" . $assetsUser . "'";
        }
        $fields = '*';
        $where .= " and is_delete = " . C('NO_STATUS');
        $total = $this->DB_get_count('assets_info', $where);
        $asinfo = $this->DB_get_all('assets_info', $fields, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asinfo) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        $inAfid = [];
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($asinfo as &$one) {
            if (!$showPrice) {
                $one['buy_price'] = '***';
            }
            $one['factorydate'] = HandleEmptyNull($one['factorydate']);
            $one['opendate'] = HandleEmptyNull($one['opendate']);
            $one['storage_date'] = HandleEmptyNull($one['storage_date']);
            $one['guarantee_date'] = HandleEmptyNull($one['guarantee_date']);
            $inAfid[] = $one['afid'];
        }
        $factorWhere['afid'] = ['IN', $inAfid];
        $factor = $this->DB_get_all('assets_factory', 'factory,assid,supplier,repair', $factorWhere);

        $newfactor = [];
        foreach ($factor as &$factorV) {
            $newfactor[$factorV['assid']]['factory'] = $factorV['factory'];
            $newfactor[$factorV['assid']]['supplier'] = $factorV['supplier'];
            $newfactor[$factorV['assid']]['repair'] = $factorV['repair'];
        }
        foreach ($asinfo as &$one) {
            if ($newfactor[$one['assid']]) {
                $one['factory'] = $newfactor[$one['assid']]['factory'];
                $one['supplier'] = $newfactor[$one['assid']]['supplier'];
                $one['repair'] = $newfactor[$one['assid']]['repair'];
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $asinfo;
        return $result;
    }


    //或者配件或增值设备信息
    public function getAssetsInfoList($highSearch = '')
    {
        $departids = session('departid');
        if ($highSearch == 1) {
            $limit = I('post.limit') ? I('post.limit') : 10;
            $page = I('post.page') ? I('post.page') : 1;
            $offset = ($page - 1) * $limit;
            $order = I('post.order');
            $sort = I('post.sort');
            $assets = I('post.assets');
            $assetsNum = I('post.assnum');
            $assetsOrnum = I('post.assorignum');
            $model = I('post.model');
            $serialnum = I('post.serialnum');
            $registration = I('post.registration');
            $factorynum = I('post.factorynum');
            $remark = I('post.remark');
            $assorignum_spare = I('post.assorignum_spare');
            $patrol_xc_cycle = I('post.patrol_xc_cycle');
            $patrol_pm_cycle = I('post.patrol_pm_cycle');
            $quality_cycle = I('post.quality_cycle');
            $metering_cycle = I('post.metering_cycle');
            $assetsCat = I('post.category');
            $assetsDep = I('post.department');
            $assetsUser = I('post.adduser');
            $hospital_id = I('post.hospital_id');
            $assetsStatus = I('post.status');
            $helpcatid = I('post.assets_helpcat');
            $financeid = I('post.assets_finance');
            $capitalfrom = I('post.assets_capitalfrom');

            $assetsrespon = I('post.assetsrespon');
            $brand = I('post.brand');
            //设备类型
            $assetsType = trim(I('post.assetsType'));
            //供应商
            $supplier = I('post.supplier');
            //生产商
            $factory = I('post.factory');
            //维修商
            $repair = I('post.repair');
            if (!$departids) {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
            $where['departid'] = ['in', $departids];
            $where['status'][0] = 'NOT IN';
            $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
            $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');
            $where['is_delete'] = '0';

            if (I('GET.action') == 'lifeAssetsList') {
                //生命支持类列表
                $where['is_lifesupport'] = C('YES_STATUS');
            }

            if ($hospital_id) {
                $where['hospital_id'] = $hospital_id;
            } else {
                $where['hospital_id'] = session('current_hospitalid');
            }
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
                $order = 'asc';
            }
            if ($helpcatid != '') {
                //辅助分类
                $where['helpcatid'] = $helpcatid;
            }
            if ($financeid != '') {
                //财务分类
                $where['financeid'] = $financeid;
            }
            if ($capitalfrom != '') {
                //资金来源
                $where['capitalfrom'] = $capitalfrom;
            }


            if ($patrol_xc_cycle) {
                //巡查周期
                $where['patrol_xc_cycle'] = $patrol_xc_cycle;
            }
            if ($patrol_pm_cycle) {
                //保养周期
                $where['patrol_pm_cycle'] = $patrol_pm_cycle;
            }
            if ($quality_cycle) {
                //质控周期
                $where['quality_cycle'] = $quality_cycle;
            }
            if ($metering_cycle) {
                //计量周期
                $where['metering_cycle'] = $metering_cycle;
            }
            if ($assets) {
                //设备名称搜索
                $where['assets'] = ['like', '%' . $assets . '%'];
            }
            if ($assetsNum) {
                //资产编码搜索
                $where['assnum'] = ['like', '%' . $assetsNum . '%'];
            }
            if (I('post.file_number') != '') {
                //档案盒编号
                $where['file_number'] = ['like', '%' . I('post.file_number') . '%'];
            }
            if ($assetsOrnum) {
                //资产原编码搜索
                $where['assorignum'] = ['like', '%' . $assetsOrnum . '%'];
            }
            if ($assorignum_spare) {
                //资产原编码搜索
                $where['assorignum_spare'] = ['like', '%' . $assorignum_spare . '%'];
            }
            if ($model) {
                //规格型号搜索
                $where['model'] = ['like', '%' . $model . '%'];
            }
            if ($serialnum) {
                //资产序列号
                $where['serialnum'] = ['like', '%' . $serialnum . '%'];
            }
            if ($registration) {
                //注册证编号
                $where['registration'] = ['like', '%' . $registration . '%'];
            }
            if ($factorynum) {
                //出厂编号
                $where['factorynum'] = ['like', '%' . $factorynum . '%'];
            }
            if ($remark) {
                //设备备注搜索
                $where['remark'] = ['like', '%' . $remark . '%'];
            }
            if ($brand) {
                //品牌搜索
                $where['brand'] = ['like', '%' . $brand . '%'];
            }
            if ($assetsCat) {
                //分类搜索
                $catwhere['category'] = ['like', '%' . $assetsCat . '%'];
                $res = $this->DB_get_all('category', 'catid', $catwhere, '', 'catid asc', '');
                if ($res) {
                    $catids = '';
                    foreach ($res as $k => $v) {
                        $catids .= $v['catid'] . ',';
                    }
                    $catids = trim($catids, ',');
                    $where['catid'] = ['in', $catids];
                } else {
                    $result['msg'] = '暂无相关数据';
                    $result['code'] = 400;
                    return $result;
                }
            }
            if ($assetsDep) {
                //部门搜索
                $where['departid'] = ['IN', $assetsDep];
            }
            //录入日期--开始
            if (I('post.addDateStartDate')) {
                $where['adddate'][] = ['GT', strtotime(I('post.addDateStartDate')) - 1];
            }
            //录入日期--结束
            if (I('post.addDateEndDate')) {
                $where['adddate'][] = ['LT', strtotime(I('post.addDateEndDate')) + 24 * 3600];
            }
            //截保日期--开始
            if (I('post.guaranteeDateStartDate')) {
                $where['guarantee_date'][] = ['GT', getHandleTime(strtotime(I('post.guaranteeDateStartDate')) - 1)];
            }
            //截保日期--结束
            if (I('post.guaranteeDateEndDate')) {
                $where['guarantee_date'][] = [
                    'LT',
                    getHandleTime(strtotime(I('post.guaranteeDateEndDate')) + 24 * 3600),
                ];
            }
            //出厂日期--开始
            if (I('post.factoryDateStartDate')) {
                $where['factorydate'][] = ['GT', getHandleTime(strtotime(I('post.factoryDateStartDate')) - 1)];
            }
            //出厂日期--结束
            if (I('post.factoryDateEndDate')) {
                $where['factorydate'][] = ['LT', getHandleTime(strtotime(I('post.factoryDateEndDate')) + 24 * 3600)];
            }
            //入库日期--开始
            if (I('post.storageDateStartDate')) {
                $where['storage_date'][] = ['GT', getHandleTime(strtotime(I('post.storageDateStartDate')) - 1)];
            }
            //入库日期--结束
            if (I('post.storageDateEndDate')) {
                $where['storage_date'][] = ['LT', getHandleTime(strtotime(I('post.storageDateEndDate')) + 24 * 3600)];
            }
            //启用日期--开始
            if (I('post.openDateStartDate')) {
                $where['opendate'][] = ['GT', getHandleTime(strtotime(I('post.openDateStartDate')) - 1)];
            }
            //启用日期--结束
            if (I('post.openDateEndDate')) {
                $where['opendate'][] = ['LT', getHandleTime(strtotime(I('post.openDateEndDate')) + 24 * 3600)];
            }
            //付款日期--开始
            if (I('post.paytimeStartDate')) {
                $where['paytime'][] = ['GT', getHandleTime(strtotime(I('post.paytimeStartDate')) - 1)];
            }
            //付款日期--结束
            if (I('post.paytimeEndDate')) {
                $where['paytime'][] = ['LT', getHandleTime(strtotime(I('post.paytimeEndDate')) + 24 * 3600)];
            }
            //设备原值区间（小）
            if (I('post.buy_priceMin')) {
                $where['buy_price'][] = ['EGT', I('post.buy_priceMin')];
            }
            //设备原值区间（大）
            if (I('post.buy_priceMax')) {
                $where['buy_price'][] = ['ELT', I('post.buy_priceMax')];
            }
            //是否国产
            if (I('post.is_domestic') == 1) {
                $where['is_domestic'] = 1;
            } elseif (I('post.is_domestic') == 2) {
                $where['is_domestic'] = 2;
            }
            //付款情况
            if (I('post.pay_status') == 1) {
                $where['pay_status'] = 1;
            } elseif (I('post.pay_status') == 2) {
                $where['pay_status'] = 0;
            }
            //附属设备
            if (I('post.is_subsidiary')) {
                $where['is_subsidiary'] = I('post.is_subsidiary');
            }
            //医疗器械类别
            if (I('post.assets_level')) {
                $where['assets_level'] = I('post.assets_level');
            }
            if ($assetsUser != null) {
                //录入人员搜索
                $where['adduser'] = $assetsUser;
            }
            if ($assetsrespon != null) {
                //设备负责人搜索
                $where['assetsrespon'] = $assetsrespon;
            }
            if ($assetsStatus != '') {
                $status = explode(',', $assetsStatus);
                $whereOR = [];
                if ($status) {
                    foreach ($status as $k => $v) {
                        if ($v == 7) {
                            $whereOR['quality_in_plan'] = C('YES_STATUS');
                        } elseif ($v == 8) {
                            $whereOR['patrol_in_plan'] = C('YES_STATUS');
                        } else {
                            $whereOR[$k]['status'] = $v;
                        }
                    }
                }
                $whereOR['_logic'] = 'or';
                $where['_complex'][0] = $whereOR;
            }
            if ($assetsType != '') {
                $type = array_filter(explode(',', $assetsType));
                $whereOrType = [];
                if ($type) {
                    foreach ($type as $k => $v) {
                        $whereOrType[$v] = 1;
                    }
                }
                $whereOrType['_logic'] = 'or';
                $where['_complex'][1] = $whereOrType;
            }
            //供应商
            if ($supplier) {
                $supplierAssid = $this->DB_get_one('assets_factory', 'group_concat(assid) as assid',
                    ['ols_supid' => ['in', $supplier]]);
                if ($supplierAssid['assid']) {
                    $whereOrSupplierAssid['assid'] = ['IN', $supplierAssid['assid']];
                } else {
                    $whereOrSupplierAssid['assid'] = '-1';
                }
                $whereOrSupplierAssid['_logic'] = 'or';
                $where['_complex'][2] = $whereOrSupplierAssid;
            }
            // 生产商
            if ($factory) {
                $factoryAssid = $this->DB_get_one('assets_factory', 'group_concat(assid) as assid',
                    ['ols_facid' => ['in', $factory]]);
                if ($factoryAssid['assid']) {
                    $whereOrFactoryAssid['assid'] = ['IN', $factoryAssid['assid']];
                } else {
                    $whereOrFactoryAssid['assid'] = '-1';
                }
                $whereOrFactoryAssid['_logic'] = 'or';
                $where['_complex'][3] = $whereOrFactoryAssid;
            }
            // 维修商
            if ($repair) {
                $repairAssid = $this->DB_get_one('assets_factory', 'group_concat(assid) as assid',
                    ['ols_repid' => ['in', $repair]]);
                if ($repairAssid['assid']) {
                    $whereOrRepairAssid['assid'] = ['IN', $repairAssid['assid']];
                } else {
                    $whereOrRepairAssid['assid'] = '-1';
                }
                $whereOrRepairAssid['_logic'] = 'or';
                $where['_complex'][4] = $whereOrRepairAssid;
            }
        } else {
            $limit = I('post.limit') ? I('post.limit') : 10;
            $page = I('post.page') ? I('post.page') : 1;
            $offset = ($page - 1) * $limit;
            $order = I('post.order');
            $sort = I('post.sort');
            $expect_assidStr = I('post.expect_assidStr');
            $assets = I('POST.assets');
            $assetsNum = I('POST.assnum');
            $serialnum = I('post.serialnum');
            $assetsOrnum = I('POST.assorignum');
            $assetsCat = I('POST.category');
            $addUser = I('POST.add_user');
            $where = [];
            if ($expect_assidStr) {
                $where['assid'] = ['IN', $expect_assidStr];
            }
            //如果是任务项闹钟点进来
            $assidArr = I('post.assidStr');
            if ($assidArr != '/' and $assidArr != null) {
                $where['assid'] = ['IN', $assidArr];
            }
            if ($addUser != null) {
                //录入人员搜索
                $where['adduser'] = $addUser;
            }
            $assetsDep = I('POST.department');
            $hospital_id = I('POST.hospital_id');
            $assetsStatus = I('POST.status');
            $asStatusAr = [];
            $asStatusAr = explode(",", $assetsStatus);


            if (!$departids) {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
            $where['departid'] = ['in', $departids];
            $where['status'][0] = 'NOT IN';
            $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
            $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');//外调中

            if (I('GET.action') == 'lifeAssetsList') {
                //生命支持类列表
                $where['is_lifesupport'] = C('YES_STATUS');
            }
            if ($hospital_id) {
                $where['hospital_id'] = $hospital_id;
            } else {
                $where['hospital_id'] = session('current_hospitalid');
            }
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
                $order = 'asc';
            }
            if ($assets) {
                //设备名称搜索
                $where['assets'] = ['like', '%' . $assets . '%'];
            }
            if ($assetsNum) {
                //资产编码搜索
                $where['assnum'] = ['like', '%' . $assetsNum . '%'];
            }
            if (I('post.file_number') != '') {
                //档案盒编号
                $where['file_number'] = ['like', '%' . I('post.file_number') . '%'];
            }
            if ($assetsOrnum) {
                //资产原编码搜索
                $where['assorignum'] = ['like', '%' . $assetsOrnum . '%'];
            }
            if ($serialnum) {
                //出厂编号搜索
                $where['serialnum'] = ['like', '%' . $serialnum . '%'];
            }
            if ($assetsCat) {
                //分类搜索
                $catwhere['category'] = ['like', '%' . $assetsCat . '%'];
                $res = $this->DB_get_all('category', 'catid', $catwhere, '', 'catid asc', '');
                if ($res) {
                    $catids = '';
                    foreach ($res as $k => $v) {
                        $catids .= $v['catid'] . ',';
                    }
                    $catids = trim($catids, ',');
                    $where['catid'] = ['in', $catids];
                } else {
                    $result['msg'] = '暂无相关数据';
                    $result['code'] = 400;
                    return $result;
                }
            }
            if ($assetsDep) {
                //部门搜索
                $where['departid'] = ['IN', $assetsDep];
            }
            //设备原值区间（小）
            if (I('post.buy_priceMin')) {
                $where['buy_price'][] = ['EGT', I('post.buy_priceMin')];
            }
            //设备原值区间（大）
            if (I('post.buy_priceMax')) {
                $where['buy_price'][] = ['ELT', I('post.buy_priceMax')];
            }
            //启用日期--开始
            if (I('post.openDateStartDate')) {
                $where['opendate'][] = ['GT', getHandleTime(strtotime(I('post.openDateStartDate')) - 1)];
            }
            //启用日期--结束
            if (I('post.openDateEndDate')) {
                $where['opendate'][] = ['LT', getHandleTime(strtotime(I('post.openDateEndDate')) + 24 * 3600)];
            }
            //付款日期--开始
            if (I('post.paytimeStartDate')) {
                $where['paytime'][] = ['GT', getHandleTime(strtotime(I('post.paytimeStartDate')) - 1)];
            }
            //付款日期--结束
            if (I('post.paytimeEndDate')) {
                $where['paytime'][] = ['LT', getHandleTime(strtotime(I('post.paytimeEndDate')) + 24 * 3600)];
            }
            //是否国产
            if (I('post.is_domestic') == 1) {
                $where['is_domestic'] = 1;
            } elseif (I('post.is_domestic') == 2) {
                $where['is_domestic'] = 2;
            }
            //付款情况
            if (I('post.pay_status') == 1) {
                $where['pay_status'] = 1;
            } elseif (I('post.pay_status') == 2) {
                $where['pay_status'] = 0;
            }


            $statusWhere = [];
            if ($assetsStatus != '') {
                if (in_array(7, $asStatusAr)) {
                    $statusWhere['quality_in_plan'] = C('YES_STATUS');

                }
                if (in_array(8, $asStatusAr)) {
                    $statusWhere['patrol_in_plan'] = C('YES_STATUS');
                }

                foreach ($asStatusAr as $k => $v) {
                    if ($v != 7 && $v != 8) {
                        $nasStatusAr[] = $v;
                    }
                }
                if (!empty($nasStatusAr)) {
                    $statusWhere['status'] = ['IN', $nasStatusAr];

                }

                $statusWhere['_logic'] = 'OR';
                $where['_complex'][1] = $statusWhere;

            }

        }


        $subsidiaryWhere[0]['is_subsidiary'] = ['EQ', C('NO_STATUS')];
        $subsidiaryWhere[0][1]['is_subsidiary'] = ['EQ', C('YES_STATUS')];
        $subsidiaryWhere[0][1]['main_assid'] = ['GT', 0];
        $subsidiaryWhere[0]['_logic'] = 'OR';
        $where['_complex'][5] = $subsidiaryWhere;

        $where['is_delete'] = '0';
        $total = $this->DB_get_count('assets_info', $where);
        $all_selects = $this->DB_get_all('assets_info', 'assid', $where, '', $sort . ' ' . $order);
        $all_assids = array_column($all_selects, 'assid');
        $asinfo = $this->DB_get_all('assets_info', '', $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$asinfo) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $inAfid = [];
        foreach ($asinfo as &$one) {
            // 年限到期日期
            if ($one['factorydate'] && $one['factorydate'] !== '0000-00-00' && (int) $one['expected_life']) {
                $one['life_expiration_date'] = (new DateTime($one['factorydate']))
                    ->modify("+{$one['expected_life']} years")
                    ->format('Y-m-d')
                ;
            } else {
                $one['life_expiration_date'] = HandleEmptyNull('');
            }

            if ($one['expected_life'] > 0) {
                // 计算剩余有效年限
                $storage_date = strtotime($one['storage_date']); 
                $current_date = time();
                
                // 计算已使用月数(向上取整)
                $used_months = ceil(($current_date - $storage_date) / (30 * 24 * 60 * 60));
                
                // 计算预计使用总月数
                $total_months = $one['expected_life'] * 12;
                
                // 计算剩余月数
                $remaining_months = $total_months - $used_months;
                
                // 添加到返回数据中
                $one['remaining_mounths'] = $remaining_months;
            } else {
                $one['remaining_mounths'] = "/";
            }

            if (!$showPrice) {
                $one['buy_price'] = '***';
            }
            $one['factorydate'] = HandleEmptyNull($one['factorydate']);
            $one['opendate'] = HandleEmptyNull($one['opendate']);
            $one['storage_date'] = HandleEmptyNull($one['storage_date']);
            $one['guarantee_date'] = HandleEmptyNull($one['guarantee_date']);
            $inAfid[] = $one['afid'];
        }
        $factorWhere['afid'] = ['IN', $inAfid];
        $factor = $this->DB_get_all('assets_factory', 'factory,assid,supplier,repair', $factorWhere);

        $newfactor = [];
        foreach ($factor as &$factorV) {
            $newfactor[$factorV['assid']]['factory'] = $factorV['factory'];
            $newfactor[$factorV['assid']]['supplier'] = $factorV['supplier'];
            $newfactor[$factorV['assid']]['repair'] = $factorV['repair'];
        }
        $del_display = [];
        foreach ($asinfo as &$one) {
            $del_display[] = $one['assid'];
            if ($newfactor[$one['assid']]) {
                $one['factory'] = $newfactor[$one['assid']]['factory'];
                $one['supplier'] = $newfactor[$one['assid']]['supplier'];
                $one['repair'] = $newfactor[$one['assid']]['repair'];
            }
        }
        $result['total'] = (int)$total;
        $result["offset"] = $offset;
        $result["limit"] = (int)$limit;
        $result["code"] = 200;
        $result['rows'] = $asinfo;
        $result['del_display'] = $del_display;
        $result['all_assids'] = $all_assids;
        return $result;
    }

    public function getSelData()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$sort) {
            $sort = 'A.adddate';
        }
        if (!$order) {
            $order = 'desc';
        }
        $assid = trim(I('POST.assid'), ',');
        $assid = explode(',', $assid);
        $selFields = I('POST.showFields');
        $title = I('POST.title');
        $type2 = I('POST.type2');
        if ($type2 == 'getHeader') {
            $newField['field'] = $selFields;
            $newField['title'] = $title;
            $newField['align'] = 'center';
            $header = $this->getEditHeader($newField, $width = ['10%', '20%', '25%', '25%', '20%']);
            return ['status' => 1, 'msg' => '成功', 'header' => $header];
        }
        $factoryField = [
            'factory',
            'factory_user',
            'factory_tel',
            'supplier',
            'supp_user',
            'supp_tel',
            'repair',
            'repa_user',
            'repa_tel',
        ];
        $fields = "A.assnum,A.assets,A.assorignum";
        if ($selFields) {
            if (in_array($selFields, $factoryField)) {
                $fields .= ',B.' . $selFields;
            } else {
                $fields .= ',A.' . $selFields;
            }
        }
        $join = " LEFT JOIN sb_assets_factory as B ON A.assid = B.assid";
        $total = $this->DB_get_count('assets_info', ['assid' => ['in', $assid]]);
        $asinfo = $this->DB_get_all_join('assets_info', 'A', $fields, $join, ['A.assid' => ['in', $assid]],
            '', $sort . ' ' . $order, $offset . "," . $limit);
        $depreciation_method = ['平均折旧法', '工作量法', '加速折旧法'];
        $departname = $catname = $baseSetting = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        foreach ($asinfo as $k => $v) {


            $asinfo[$k]['opendate'] = HandleEmptyNull($v['opendate']);
            $asinfo[$k]['factorydate'] = HandleEmptyNull($v['factorydate']);
            $asinfo[$k]['storage_date'] = HandleEmptyNull($v['storage_date']);
            $asinfo[$k]['guarantee_date'] = HandleEmptyNull($v['guarantee_date']);

            $asinfo[$k]['departid'] = $departname[$v['departid']]['department'];
            $asinfo[$k]['catid'] = $catname[$v['catid']]['category'];
            switch ($v['status']) {
                case C('ASSETS_STATUS_USE'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_REPAIR_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_USE'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_SCRAP_NAME');
                    break;
                default:
                    $asinfo[$k]['status'] = '未知状态';
                    break;
            }
            $asinfo[$k]['helpcatid'] = $baseSetting['assets']['assets_helpcat']['value'][$v['helpcatid']];
            $asinfo[$k]['financeid'] = $baseSetting['assets']['assets_finance']['value'][$v['financeid']];
            $asinfo[$k]['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$v['capitalfrom']];
            $asinfo[$k]['assfromid'] = $baseSetting['assets']['assets_assfrom']['value'][$v['assfromid']];
            $asinfo[$k]['depreciation_method'] = $depreciation_method[$v['depreciation_method'] - 1];
        }
        //格式化设备类型
        $asinfo = $this->formatAssetsType($asinfo);
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $asinfo;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    //添加设备操作
    public function addAssets()
    {
        //查询档案盒编号是否存在
        $box_num = trim(I('post.box_num'));
        if ($box_num) {
            $box_info = $this->DB_get_one('archives_box', 'box_id,box_num', ['box_num' => $box_num]);
            if (!$box_info) {
                return ['status' => -1, 'msg' => '档案盒编号不存在！'];
            } else {
                $box_id = $box_info['box_id'];
            }
        } else {
            $box_id = '';
        }
        //验证数据
        $data = $this->checkData();
        if ($data['status'] == -1) {
            return $data;
        }
        $data['adduser'] = session('username');
        $data['adddate'] = time();
        $data['common_name'] = I('post.common_name');
        $data['hospital_id'] = session('current_hospitalid');
        //print_r($data);die;
        //最新assid
        $assid = $this->insertData('assets_info', $data);
        if (!$assid) {
            return ['status' => -1, 'msg' => '新增设备失败！'];
        }
        $identifier = I('post.identifier');
        //更新archives表
        $new_arc = $this->DB_get_all('assets_archives_file', 'arc_id,assid,archive_time,expire_time',
            ['identifier' => $identifier], '', 'arc_id asc');
        foreach ($new_arc as $k => $v) {
            $this->updateData('assets_archives_file', [
                'archive_time' => I('post.archives_time')[$k],
                'expire_time' => I('post.expire_time')[$k],
                'assid' => $assid,
                'box_id' => $box_id,
            ], ['arc_id' => $v['arc_id']]);
        }
        //日志行为记录文字
        $log['assets'] = $data['assets'];
        $text = getLogText('addAssetsLogText', $log);
        $this->addLog('assets_info', M()->getLastSql(), $text, $assid, '');
        //记录入库信息到状态变更表
        $this->updateAssetsStatus($assid, 0, '设备入库');
        $factory['assid'] = $assid;
        $factory['adddate'] = time();
        $factory['ols_facid'] = trim(I('post.ols_facid'));
        $factory['ols_supid'] = trim(I('post.ols_supid'));
        $factory['ols_repid'] = trim(I('post.ols_repid'));
        $factory['factory'] = trim(I('post.factory'));
        $factory['supplier'] = trim(I('post.supplier'));
        $factory['repair'] = trim(I('post.repair'));
        //入库和获取最新afid
        $newafid = $this->insertData('assets_factory', $factory);
        //修改最新一条数据(assid和acid和afid)和生成最新资产编号 资产条码号
        $newcat = $this->DB_get_one('category', 'catenum', ['catid' => I('catid')]);
        $newdepart = $this->DB_get_one('department', 'departnum', ['departid' => I('departid')]);
        $newnum['afid'] = $newafid;
        if (I('POST.is_subsidiary') == C('YES_STATUS')) {
            $mainAssetsWhere['assid'] = ['EQ', $data['main_assid']];
            $mainAssetsWhere['main_assid'] = ['EQ', $data['main_assid']];
            $mainAssetsWhere['_logic'] = 'OR';
            $mainAssets = $this->DB_get_all('assets_info', 'assid,assnum', $mainAssetsWhere);
            //-1 减去当前这一条设备入库的数量
            $count = count($mainAssets) - 1;
            $main_assnum = '';
            foreach ($mainAssets as &$mainV) {
                if ($mainV['assid'] == $data['main_assid']) {
                    $main_assnum = $mainV['assnum'];
                }
            }
            $newnum['assnum'] = $this->getSubsidiaryAssetsNum($main_assnum, $count);
        } else {
            $newnum['assnum'] = $newcat['catenum'] . $newdepart['departnum'] . $assid;
        }
        $newnum['barcore'] = $newnum['assnum'];
        $record = $this->DB_get_one('assets_info', 'assnum', ['assnum' => $newnum['assnum']]);
        if ($record) {
            // 拼上随机数
            $newnum['assnum'] = $newnum['assnum'] . rand(1000, 9999);
        }
        $this->updateData('assets_info', $newnum, ['assid' => $assid]);
        //更新部门表和分类表中设备数量、总价等信息
        $this->updateAssetsNumAndTotalPrice();
        // 盘点审核需要这assets_data字段
        $newnum['assid'] = $assid;
        $newnum['status'] = 0;
        return ['status' => 1, 'msg' => '添加设备成功！', 'assets_data' => $newnum];
    }


    //编辑设备操作
    public function editAssets()
    {
        $assid = I('POST.assid');
        //查询设备是否存在
        $asInfo = $this->DB_get_one('assets_info', 'assid,afid,departid,status,quality_in_plan,patrol_in_plan',
            ['assid' => $assid]);
        if (!$asInfo) {
            return ['status' => -1, 'msg' => '查找不到设备信息！'];
        }
        //查询档案盒编号是否存在
        $box_num = trim(I('post.box_num'));
        if ($box_num) {
            $box_info = $this->DB_get_one('archives_box', 'box_id,box_num', ['box_num' => $box_num]);
            if (!$box_info) {
                return ['status' => -1, 'msg' => '档案盒编号不存在！'];
            } else {
                $box_id = $box_info['box_id'];
            }
        } else {
            $box_id = '';
        }
        //验证数据
        $data = $this->checkData();
        if ($data['status'] == -1) {
            return $data;
        }
        //录入员工
        $data['edituser'] = session('username');
        //录入时间
        $data['editdate'] = time();
        //常用名
        $data['common_name'] = trim(I('post.common_name'));
        //防止科室信息被修改
        unset($data['departid']);
//        unset($data['managedepart']);
        //修改assets
        $res = $this->updateData('assets_info', $data, ['assid' => $assid]);
        //更新archives表
        $new_arc = $this->DB_get_all('assets_archives_file', 'arc_id,archive_time,expire_time', ['assid' => $assid], '',
            'arc_id asc');
        foreach ($new_arc as $k => $v) {
            $this->updateData('assets_archives_file', [
                'archive_time' => I('post.archives_time')[$k],
                'expire_time' => I('post.expire_time')[$k],
                'box_id' => $box_id,
            ], ['arc_id' => $v['arc_id']]);
        }
        //日志行为记录文字
        $log['assets'] = $data['assets'];
        $text = getLogText('editAssetsLogText', $log);
        $this->addLog('assets_info', M()->getLastSql(), $text, $assid, '');
        if (!$res) {
            return ['status' => -1, 'msg' => '更新设备失败！'];
        }
        //更新厂商信息
        $factory['editdate'] = time();
        $factory['ols_facid'] = trim(I('post.ols_facid'));
        $factory['ols_supid'] = trim(I('post.ols_supid'));
        $factory['ols_repid'] = trim(I('post.ols_repid'));
        $factory['factory'] = trim(I('post.factory'));
        $factory['supplier'] = trim(I('post.supplier'));
        $factory['repair'] = trim(I('post.repair'));
        if ($asInfo['afid'] != '0'){
            $factoryInfo = $this->DB_get_one('assets_factory', 'factory', ['afid' => $asInfo['afid']]);
            if ($factoryInfo) {
                $res = $this->updateData('assets_factory', $factory, ['afid' => $asInfo['afid']]);
            }else{
                $new['afid'] = $this->insertData('assets_factory', $factory);
                $res = $this->updateData('assets_info', ['afid' => $new['afid']], ['assid' => $assid]);
            }
            if ($res) {
                return ['status' => 1, 'msg' => '更新设备成功！'];
            } else {
                return ['status' => -1, 'msg' => '更新设备厂商信息失败！'];
            }
        }else{
            return ['status' => 1, 'msg' => '更新设备成功！'];
        }
    }

    /*
     * 批量编辑设备时，获取设备所有信息
     * return array
     */
    public function getAssetsDetailInfoList()
    {
        $limit = I('post.limit');
        $offset = I('post.offset');
        $order = I('post.order');
        $sort = I('post.sort');
        $assets = I('post.assetsName');
        $assetsNum = I('post.assetsNum');
        $assetsCat = I('post.assetsCat');
        $assetsDep = I('post.assetsDep');
        $assetsDate = I('post.assetsDate');
        $assetsUser = I('post.assetsUser');
        $where['A.status'] = ['neq', '2'];
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$sort) {
            $sort = 'A.assid ';
        } else {
            $sort = 'A.' . $sort;
        }
        if (!$order) {
            $order = 'asc';
        }
        if ($sort == 'price') {
            $sort = 'A.buy_price';
        }
        if ($assets) {
            //设备名称搜索
            $where['A.assets'] = ['like', '%' . $assets . '%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['A.assnum'] = ['like',];
        }
        if ($assetsCat) {
            //分类搜索
            $catwhere['category'] = ['like', '%' . $assetsCat . '%'];
            $res = $this->DB_get_all('category', 'catid', $catwhere, '', 'catid asc', '');
            if ($res) {
                $catids = '';
                foreach ($res as $k => $v) {
                    $catids .= $v['catid'] . ',';
                }
                $catids = trim($catids, ',');
                $where['A.catid'] = ['in', $catids];
            } else {
                $where['A.catid'] = ['in', '-1'];
            }
        }
        if ($assetsDep) {
            //部门搜索
            $dewhere['department'] = ['like', '%' . $assetsDep . '%'];
            $res = $this->DB_get_all('department', 'departid', $dewhere, '', 'departid asc', '');
            if ($res) {
                $departids = '';
                foreach ($res as $k => $v) {
                    $departids .= $v['departid'] . ',';
                }
                $departids = trim($departids, ',');
                $where['A.departid'] = ['in', $departids];
            } else {
                $where['A.departid'] = ['in', '-1'];
            }
        }
        if ($assetsDate) {
            //录入时间搜索
            $pretime = strtotime($assetsDate) - 1;
            $nexttime = strtotime($assetsDate) + 24 * 3600;
            $where['A.adddate'][] = ['gt', $pretime];
            $where['A.adddate'][] = ['lt', $nexttime];
        }
        if ($assetsUser != null) {
            //录入人员搜索
            $where['A.adduser'] = $assetsUser;
        }
        $join = " LEFT JOIN sb_assets_factory AS B ON B.afid=A.afid LEFT JOIN sb_assets_contract AS C ON C.acid=A.acid ";
        $total = $this->DB_get_count_join('assets_info', 'A', $join, $where);
        $fields = 'A.*,B.*,C.*';
        $asinfo = $this->DB_get_all_join('assets_info', 'A', $fields, $join, $where, '', $sort . ' ' . $order,
            $offset . "," . $limit);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        $catname = [];
        $departname = [];
        $baseSetting = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        foreach ($asinfo as $k => $v) {
            if (!$showPrice) {
                $asinfo[$k]['buy_price'] = '***';
            }
            $asinfo[$k]['department'] = $departname[$v['departid']]['department'];
            $asinfo[$k]['category'] = $catname[$v['catid']]['catenum'];
            $asinfo[$k]['helpcatid'] = $baseSetting['assets']['assets_helpcat']['value'][$asinfo[$k]['helpcatid']];
            $asinfo[$k]['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$asinfo[$k]['capitalfrom']];
            $asinfo[$k]['assfromid'] = $baseSetting['assets']['assets_assfrom']['value'][$asinfo[$k]['assfromid']];
            $asinfo[$k]['financeid'] = $baseSetting['assets']['assets_finance']['value'][$asinfo[$k]['financeid']];
            $asinfo[$k]['con_date'] = ($asinfo[$k]['con_date'] == 0) ? '' : date('Y-m-d',
                $asinfo[$k]['con_date']);
            $asinfo[$k]['buy_date'] = ($asinfo[$k]['buy_date'] == 0) ? '' : date('Y-m-d',
                $asinfo[$k]['buy_date']);
            $asinfo[$k]['standard_date'] = ($asinfo[$k]['standard_date'] == 0) ? '' : date('Y-m-d',
                $asinfo[$k]['standard_date']);
            $asinfo[$k]['guarantee_date'] = ($asinfo[$k]['guarantee_date'] == 0) ? '' : date('Y-m-d',
                $asinfo[$k]['guarantee_date']);
            $asinfo[$k]['is_firstaid'] = ($asinfo[$k]['is_firstaid'] == 0) ? '否' : '是';
            $asinfo[$k]['is_special'] = ($asinfo[$k]['is_special'] == 0) ? '否' : '是';
            $asinfo[$k]['is_metering'] = ($asinfo[$k]['is_metering'] == 0) ? '否' : '是';
            $asinfo[$k]['is_patrol'] = ($asinfo[$k]['is_patrol'] == 0) ? '否' : '是';
            $asinfo[$k]['is_standard'] = ($asinfo[$k]['is_standard'] == 0) ? '否' : '是';
            $asinfo[$k]['is_inspection'] = ($asinfo[$k]['is_inspection'] == 0) ? '否' : '是';
            $asinfo[$k]['is_benefit'] = ($asinfo[$k]['is_benefit'] == 0) ? '否' : '是';
            $asinfo[$k]['is_lifesupport'] = ($asinfo[$k]['is_lifesupport'] == 0) ? '否' : '是';
            $asinfo[$k]['is_description'] = ($asinfo[$k]['is_description'] == 0) ? '否' : '是';
            $asinfo[$k]['is_attach'] = ($asinfo[$k]['is_attach'] == 0) ? '否' : '是';
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["rows"] = $asinfo;
        return $result;
    }


    /*
    * 查询设备的附件和增值数量(生命历程页面)
    * @params $asinfo array 设备信息
    * return array
    */
    public function getLifeIncrementAndAccessory($asinfo)
    {
        $depreciation_method = ['平均折旧法', '工作量法', '加速折旧法'];
        $departname = [];
        $catname = [];
        $baseSetting = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        //查询当前用户是否有权限进行查询设备生命历程
        $assetsLife = get_menu('Assets', 'Lookup', 'assetsLifeList');
        foreach ($asinfo as $k => $v) {
            $html = '<div class="layui-btn-group">';
            $asinfo[$k]['address'] = $departname[$v['departid']]['address'];
            $asinfo[$k]['departid'] = $departname[$v['departid']]['department'];
            $asinfo[$k]['jbuy_price'] = $asinfo[$k]['buy_price'];
            $asinfo[$k]['catid'] = $catname[$v['catid']]['category'];
            switch ($v['status']) {
                case C('ASSETS_STATUS_USE'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_REPAIR_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_SCRAP_NAME');
                    break;
                case C('ASSETS_STATUS_OUTSIDE'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_OUTSIDE_NAME');
                    break;
                case C('ASSETS_STATUS_OUTSIDE_ON'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_OUTSIDE_ON_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP_ON'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_SCRAP_ON_NAME');
                    break;
                case C('ASSETS_STATUS_TRANSFER_ON'):
                    $asinfo[$k]['status'] = C('ASSETS_STATUS_TRANSFER_ON_NAME');
                    break;
                case 7:
                    $asinfo[$k]['status'] = '质控中';
                    break;
                case 8:
                    $asinfo[$k]['status'] = '巡查中';
                    break;
                default:
                    $asinfo[$k]['status'] = '未知状态';
                    break;
            }
            $asinfo[$k]['helpcatid'] = $baseSetting['assets']['assets_helpcat']['value'][$v['helpcatid']];
            $asinfo[$k]['financeid'] = $baseSetting['assets']['assets_finance']['value'][$v['financeid']];
            $asinfo[$k]['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$v['capitalfrom']];
            $asinfo[$k]['assfromid'] = $baseSetting['assets']['assets_assfrom']['value'][$v['assfromid']];
            $asinfo[$k]['depreciation_method'] = $depreciation_method[$v['depreciation_method'] - 1];
            if ($assetsLife) {
                $html .= $this->returnButtonLink('显示详情', $assetsLife['actionurl'],
                    'layui-btn layui-btn-xs layui-btn-normal', '', 'lay-event = showLife');
            }
            $html .= '</div>';
            $asinfo[$k]['operation'] = $html;
        }
        //格式化设备类型
        $asinfo = $this->formatAssetsType($asinfo);
        return $asinfo;
    }

    /*
     * 查询设备的附件和增值数量
     * @params $asinfo array 设备信息
     * return array
     */
    public function getIncrementAndAccessory($asinfo, $del_display = "0")
    {
        //查询当前用户是否有权限进行修改主设备
        $editAssets = get_menu('Assets', 'Lookup', 'editAssets');
        //查询当前用户是否有权限进行删除主设备
        $deleteAssets = get_menu('Assets', 'Lookup', 'deleteAssets');
        //查询当前用户是否有权限进行添加主设备
        $addAssets = get_menu('Assets', 'Lookup', 'addAssets');
        //查询当前用户是否有权限查询设备列表
        $getAssetsList = get_menu('Assets', 'Lookup', 'getAssetsList');
        $depreciation_method = ['平均折旧法', '工作量法', '加速折旧法'];
        $departname = [];
        $catname = [];
        $baseSetting = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $assids = [];

        $where['is_subsidiary'] = ['EQ', C('YES_STATUS')];
        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        $where['is_delete'] = '0';
        $assets = $this->DB_get_all('assets_info', 'main_assid', $where, 'main_assid');
        //获取不能被删除的设备id开始
        if (!$del_display) {
            $del_display = '0';
            $assid_str = '0';
        } else {
            $assid_str = implode(',', $del_display);
        }
        $del_display_data = M('assets_borrow')->field('assid,"borrow" as type')->where([
            'assid' => [
                'IN',
                $del_display,
            ],
        ])->union('SELECT assid,"transfer" as type FROM sb_assets_transfer where assid in (' . $assid_str . ')',
            true)->union('SELECT assid,"outside" as type FROM sb_assets_outside where assid in (' . $assid_str . ')',
            true)->union('SELECT assid,"scrap" as type FROM sb_assets_scrap where assid in (' . $assid_str . ')',
            true)->union('SELECT assid,"repair" as type FROM sb_repair where assid in (' . $assid_str . ')',
            true)->union('SELECT assid,"adverse" as type FROM sb_adverse_info where assid in (' . $assid_str . ')',
            true)->union('SELECT assid,"quality_starts" as type FROM sb_quality_starts where assid in (' . $assid_str . ')',
            true)->union('SELECT assid,"quality_result" as type FROM sb_quality_result where assid in (' . $assid_str . ')',
            true)->union('SELECT assid,"template" as type FROM sb_patrol_assets_template where assid in (' . $assid_str . ')',
            true)->union('SELECT assid,"metering" as type FROM sb_metering_plan where assid in (' . $assid_str . ')',
            true)->select();
        $edit_data = $this->DB_get_all('edit', 'update_where',
            ['is_approval' => '0', 'operation_type' => 'delete']);
        foreach ($edit_data as $e_k => $e_v) {
            $u_arr = [];
            $u_arr = json_decode($e_v['update_where'], true);
            $u_arr['type'] = 'delete';
            $del_display_data[] = $u_arr;
        }
        array_unique($edit_data, SORT_REGULAR);
        //结束
        $assetsData = [];
        foreach ($assets as &$val) {
            $assetsData[$val['main_assid']] = true;
        }

        foreach ($asinfo as $k => $v) {
            $html = '<div class="layui-btn-group">';
            $assids[] = $v['assid'];
            //$asinfo[$k]['address'] = $departname[$v['departid']]['address'];
            $asinfo[$k]['department'] = $departname[$v['departid']]['department'];
            $asinfo[$k]['departperson'] = $departname[$v['departid']]['departrespon'];
            $asinfo[$k]['jbuy_price'] = $asinfo[$k]['buy_price'];
            $asinfo[$k]['category'] = $catname[$v['catid']]['category'];
            $asinfo[$k]['status'] = (int)$v['status'];
            switch ($v['status']) {
                case C('ASSETS_STATUS_USE'):
                    $asinfo[$k]['status_name'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $asinfo[$k]['status_name'] = C('ASSETS_STATUS_REPAIR_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $asinfo[$k]['status_name'] = C('ASSETS_STATUS_SCRAP_NAME');
                    break;
                case C('ASSETS_STATUS_OUTSIDE'):
                    $asinfo[$k]['status_name'] = C('ASSETS_STATUS_OUTSIDE_NAME');
                    break;
                case C('ASSETS_STATUS_OUTSIDE_ON'):
                    $asinfo[$k]['status_name'] = C('ASSETS_STATUS_OUTSIDE_ON_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP_ON'):
                    $asinfo[$k]['status_name'] = C('ASSETS_STATUS_SCRAP_ON_NAME');
                    break;
                case C('ASSETS_STATUS_TRANSFER_ON'):
                    $asinfo[$k]['status_name'] = C('ASSETS_STATUS_TRANSFER_ON_NAME');
                    break;
                case 7:
                    $asinfo[$k]['status_name'] = '质控中';
                    break;
                case 8:
                    $asinfo[$k]['status_name'] = '巡查中';
                    break;
                default:
                    $asinfo[$k]['status_name'] = '未知状态';
                    break;
            }
            $asinfo[$k]['helpcatid'] = $baseSetting['assets']['assets_helpcat']['value'][$v['helpcatid']];
            $asinfo[$k]['financeid'] = $baseSetting['assets']['assets_finance']['value'][$v['financeid']];
            $asinfo[$k]['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$v['capitalfrom']];
            $asinfo[$k]['assfromid'] = $baseSetting['assets']['assets_assfrom']['value'][$v['assfromid']];
            $asinfo[$k]['depreciation_method'] = $depreciation_method[$v['depreciation_method'] - 1];
            $html .= $this->returnListLink('详情', C('SHOWASSETS_ACTION_URL'),
                'showAssets', C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($editAssets) {
                if ($v['status'] != C('ASSETS_STATUS_SCRAP')) {
                    $html .= $this->returnListLink('编辑', $editAssets['actionurl'], 'editAssets',
                        C('BTN_CURRENCY') . ' layui-btn-warm');
                } else {
                    $html .= $this->returnListLink('编辑', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                }
            }

            if ($v['is_subsidiary'] == C('YES_STATUS')) {
                $html .= $this->returnListLink('所属设备', C('SHOWASSETS_ACTION_URL'), 'mainAssets',
                    C('BTN_CURRENCY') . ' layui-bg-cyan');
            } else {
                if ($assetsData[$v['assid']]) {
                    $html .= $this->returnListLink('附属设备', C('SHOWASSETS_ACTION_URL'), 'increment',
                        C('BTN_CURRENCY') . ' layui-btn-warm');
                } else {
                    $html .= $this->returnListLink('附属设备', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                }
            }
            $del_display = false;
            $title = "";
            foreach ($del_display_data as $key => $value) {
                if ($value['assid'] == $v['assid']) {
                    $del_display = true;
                    switch ($value['type']) {
                        case 'borrow':
                            $title = "该设备还存在借调业务";
                            break;
                        case 'transfer':
                            $title = "该设备还存在转科业务";
                            break;
                        case 'outside':
                            $title = "该设备还存在外调业务";
                            break;
                        case 'scrap':
                            $title = "该设备还存在报废业务";
                            break;
                        case 'repair':
                            $title = "该设备还存在维修业务";
                            break;
                        case 'adverse':
                            $title = "该设备还存在不良事件记录";
                            break;
                        case 'quality_starts':
                            $title = "该设备还存在质控业务";
                            break;
                        case 'quality_result':
                            $title = "该设备还存在质控项目执行记录";
                            break;
                        case 'metering':
                            $title = "该设备还存在计量计划业务";
                            break;
                        case 'delete':
                            $title = "已经申请删除请耐心等待";
                            break;
                        case 'template':
                            $title = "该设备还存在初始化模板";
                            break;
                        default:
                            $title = "该设备还存在其他业务";
                            break;
                    }
                    break;
                }
            }
            if ($deleteAssets && ($v['quality_in_plan'] || $del_display || $v['patrol_in_plan'])) {
                if ($title == "") {
                    $title = "该设备还存在巡查保养业务";
                }
                $html .= $this->returnListLink('删除', "", '', C('BTN_CURRENCY') . ' layui-btn-disabled', '',
                    "title='$title'");
            } elseif ($deleteAssets) {
                $html .= $this->returnListLink('删除', $deleteAssets['actionurl'] . '?assid=' . $v['assid'],
                    'deleteAssets', C('BTN_CURRENCY') . ' layui-btn-danger');
            }
            $html .= '</div>';
            $asinfo[$k]['operation'] = $html;
            switch ($v['pay_status']) {
                case 0:
                    $asinfo[$k]['pay_statusName'] = '未付清';
                    break;
                case 1:
                    $asinfo[$k]['pay_statusName'] = '已付清';
                    break;
                case 3:
                    $asinfo[$k]['pay_statusName'] = '';
                    break;
                default:
                    $asinfo[$k]['pay_statusName'] = '';
                    break;
            }
            switch ($v['is_domestic']) {
                case 1:
                    $asinfo[$k]['is_domesticName'] = '国产';
                    break;
                case 2:
                    $asinfo[$k]['is_domesticName'] = '进口';
                    break;
                case 3:
                    $asinfo[$k]['is_domesticName'] = '';
                    break;
                default:
                    $asinfo[$k]['is_domesticName'] = '';
                    break;
            }
            switch ($v['assets_level']) {
                case 1:
                    $asinfo[$k]['assets_level_name'] = 'Ⅰ类';
                    break;
                case 2:
                    $asinfo[$k]['assets_level_name'] = 'Ⅱ类';
                    break;
                case 3:
                    $asinfo[$k]['assets_level_name'] = 'Ⅲ类';
                    break;
                default:
                    $asinfo[$k]['assets_level_name'] = '';
                    break;
            }
        }
        //格式化设备类型
        $asinfo = $this->formatAssetsType($asinfo);
        $unarr = [
            'assid',
            'status',
            'main_assid',
            'hospital_id',
            'code',
            'acid',
            'afid',
            'catid',
            'common_name',
            'main_assets',
            'subsidiary_helpcatid',
            'departid',
            'pic_url',
            'code_url',
            'operation',
        ];
        foreach ($asinfo as $k => $v) {
            if ($v['status'] == C('ASSETS_STATUS_SCRAP')) {
                $asinfo[$k]['assnum'] = '<span style="color: #FF5722;">' . $v['assnum'] . '</span>';
                $asinfo[$k]['assets'] = '<span style="color: #FF5722;">' . $v['assets'] . '</span>';
                $asinfo[$k]['assorignum'] = '<span style="color: #FF5722;">' . $v['assorignum'] . '</span>';
                $asinfo[$k]['category'] = '<span style="color: #FF5722;">' . $v['category'] . '</span>';
                $asinfo[$k]['department'] = '<span style="color: #FF5722;">' . $v['department'] . '</span>';
                $asinfo[$k]['departperson'] = '<span style="color: #FF5722;">' . $v['departperson'] . '</span>';
                $asinfo[$k]['address'] = '<span style="color: #FF5722;">' . $v['address'] . '</span>';
                $asinfo[$k]['managedepart'] = '<span style="color: #FF5722;">' . $v['managedepart'] . '</span>';
                $asinfo[$k]['model'] = '<span style="color: #FF5722;">' . $v['model'] . '</span>';
                $asinfo[$k]['brand'] = '<span style="color: #FF5722;">' . $v['brand'] . '</span>';

                $asinfo[$k]['serialnum'] = '<span style="color: #FF5722;">' . $v['serialnum'] . '</span>';
                $asinfo[$k]['assetsrespon'] = '<span style="color: #FF5722;">' . $v['assetsrespon'] . '</span>';
                $asinfo[$k]['factorynum'] = '<span style="color: #FF5722;">' . $v['factorynum'] . '</span>';
                $asinfo[$k]['factorydate'] = '<span style="color: #FF5722;">' . $v['factorydate'] . '</span>';
                $asinfo[$k]['opendate'] = '<span style="color: #FF5722;">' . $v['opendate'] . '</span>';
                $asinfo[$k]['storage_date'] = '<span style="color: #FF5722;">' . $v['storage_date'] . '</span>';
                $asinfo[$k]['helpcatid'] = '<span style="color: #FF5722;">' . $v['helpcatid'] . '</span>';
                $asinfo[$k]['financeid'] = '<span style="color: #FF5722;">' . $v['financeid'] . '</span>';
                $asinfo[$k]['capitalfrom'] = '<span style="color: #FF5722;">' . $v['capitalfrom'] . '</span>';
                $asinfo[$k]['assfromid'] = '<span style="color: #FF5722;">' . $v['assfromid'] . '</span>';
                $asinfo[$k]['invoicenum'] = '<span style="color: #FF5722;">' . $v['invoicenum'] . '</span>';
                $asinfo[$k]['buy_price'] = '<span style="color: #FF5722;">' . $v['buy_price'] . '</span>';
                $asinfo[$k]['expected_life'] = '<span style="color: #FF5722;">' . $v['expected_life'] . '</span>';
                $asinfo[$k]['residual_value'] = '<span style="color: #FF5722;">' . $v['residual_value'] . '</span>';

                $asinfo[$k]['guarantee_date'] = '<span style="color: #FF5722;">' . $v['guarantee_date'] . '</span>';
                $asinfo[$k]['depreciation_method'] = '<span style="color: #FF5722;">' . $v['depreciation_method'] . '</span>';
                $asinfo[$k]['depreciable_lives'] = '<span style="color: #FF5722;">' . $v['depreciable_lives'] . '</span>';
                $asinfo[$k]['factory'] = '<span style="color: #FF5722;">' . $v['factory'] . '</span>';
                $asinfo[$k]['factory_user'] = '<span style="color: #FF5722;">' . $v['factory_user'] . '</span>';
                $asinfo[$k]['factory_tel'] = '<span style="color: #FF5722;">' . $v['factory_tel'] . '</span>';
                $asinfo[$k]['supplier'] = '<span style="color: #FF5722;">' . $v['supplier'] . '</span>';
                $asinfo[$k]['supp_user'] = '<span style="color: #FF5722;">' . $v['supp_user'] . '</span>';
                $asinfo[$k]['supp_tel'] = '<span style="color: #FF5722;">' . $v['supp_tel'] . '</span>';
                $asinfo[$k]['repair'] = '<span style="color: #FF5722;">' . $v['repair'] . '</span>';
                $asinfo[$k]['repa_user'] = '<span style="color: #FF5722;">' . $v['repa_user'] . '</span>';
                $asinfo[$k]['repa_tel'] = '<span style="color: #FF5722;">' . $v['repa_tel'] . '</span>';
            }
        }
        if (!$assids) {
            return $asinfo;
        }
        return $asinfo;
    }

    /*
     * 处理上传的主设备编辑文件
     * return array
     */
    public function uploadEditAssetsFiles()
    {
        if (!empty($_FILES)) {
            $uploadConfig = [
                'maxSize' => 3145728,
                'rootPath' => './Public/',
                'savePath' => 'uploads/',
                'saveName' => ['uniqid', ''],
                'exts' => ['xlsx', 'xls', 'xlsm'],
                'autoSub' => true,
                'subName' => ['date', 'Ymd'],
            ];
            $upload = new \Think\Upload($uploadConfig);
            $info = $upload->upload();
            if (!$info) {
                return ['status' => -1, 'msg' => '导入数据出错'];
            }
            vendor("PHPExcel.PHPExcel");
            $filePath = $upload->rootPath . $info['file']['savepath'] . $info['file']['savename'];
            if (empty($filePath) or !file_exists($filePath)) {
                return ['status' => -1, 'msg' => '文件不存在！'];
            }

            $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
            if (!$PHPReader->canRead($filePath)) {
                $PHPReader = new \PHPExcel_Reader_Excel5();
                if (!$PHPReader->canRead($filePath)) {
                    return ['status' => -1, 'msg' => '文件格式错误'];
                }
            }
            $excelDate = new \PHPExcel_Shared_Date();
            $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
            $currentSheet = $PHPExcel->getSheet(0);        //**读取excel文件中的指定工作表*/
            $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
            ++$allColumn;
            $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
            $data = [];
            //需要进行日期处理的保存在一个数组
            $toDate = [
                'factorydate',
                'opendate',
                'con_date',
                'buy_date',
                'standard_date',
                'guarantee_date',
            ];
            $table = [];
            $realcellname = [];
            for ($rowIndex = 1; $rowIndex <= 1; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for ($colIndex = 'A'; $colIndex != $allColumn; $colIndex++) {
                    $addr = $colIndex . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell) {
                        $table[] = $cell;
                    }
                }
            }
            $table = $this->getFieldName($table);
            $i = 65;
            foreach ($table as $k => $v) {
                if ($i <= 90) {
                    $realcellname[chr($i)] = $k;
                } elseif ($i > 90 && $i <= 115) {
                    $realcellname['A' . chr($i - 26)] = $k;
                }
                $i++;
            }
            for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for ($colIndex = 'A'; $colIndex != $allColumn; $colIndex++) {
                    $addr = $colIndex . $rowIndex;
                    if (in_array($realcellname[$colIndex], $toDate)) {
                        if (!$currentSheet->getCell($addr)->getValue()) {
                            $cell = '';
                        } else {
                            $d = $currentSheet->getCell($addr)->getValue();
                            $cell = '';
                            if (strpos($d, '/') !== false) {
                                $dt = explode('/', $d);
                                if ($dt[1] < 10) {
                                    $dt[1] = '0' . (int)$dt[1];
                                }
                                if ($dt[2] < 10) {
                                    $dt[2] = '0' . (int)$dt[2];
                                }
                                $cell = implode('-', $dt);
                            } elseif (strlen($d) == 8) {
                                $cell = date('Y-m-d', strtotime($d));
                            } else {
                                $cell = gmdate("Y-m-d", $excelDate::ExcelToPHP($d));
                            }
                        }
                    } else {
                        $cell = $currentSheet->getCell($addr)->getValue();
                    }
                    if ($cell instanceof \PHPExcel_RichText) { //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    if ($realcellname[$colIndex] == 'assets') {
                        if (!$cell) {
                            continue;
                        }
                    }
                    $data[$rowIndex - 2][$realcellname[$colIndex]] = $cell ? $cell : '';
                }
            }
            if (!$data) {
                return ['status' => -1, 'msg' => '导入数据失败！'];
            }
            //只返回特定条数数据
            F('assetsEditData', []);
            $returnLimitData = $this->returnLimitEditData($data);
            unlink($filePath);
            $return['status'] = "1";
            $return['msg'] = '数据上传成功，请编辑正确后再做保存操作！';
            $return['nowDay'] = date('Y-m-d');
            $return['data'] = $returnLimitData;
            $return['tableData'] = $table;
            $return = json_encode($return, JSON_UNESCAPED_UNICODE);
            header('Content-Length: ' . strlen($return));
            header('Content-Type: application/json; charset=utf-8');
            echo $return;
            exit;
        } else {
            return ['status' => -1, 'msg' => '请上传文件！'];
        }
    }

    public function getFieldName($table)
    {
        $sql = "SHOW FULL COLUMNS FROM sb_" . $this->tableName;
        $res = M($this->tableName)->query($sql);
        $arr = [];
        $returnAarr = [];
        foreach ($res as $k => $v) {
            $arr[$v['Field']] = $v['Comment'];
        }
        $sql = "SHOW FULL COLUMNS FROM sb_assets_contract";
        $res = M('assets_contract')->query($sql);
        foreach ($res as $k => $v) {
            if ($v['Field'] != 'price') {
                $arr[$v['Field']] = $v['Comment'];
            }
        }
        $sql = "SHOW FULL COLUMNS FROM sb_assets_factory";
        $res = M('assets_contract')->query($sql);
        foreach ($res as $k => $v) {
            $arr[$v['Field']] = $v['Comment'];
        }
        foreach ($table as $k => $v) {
            foreach ($arr as $k1 => $v1) {
                if (trim($v1) == '68分类ID') {
                    $tmpname = '分类编码';
                } else {
                    $tmpname = trim($v1);
                }
                if (trim($v) == $tmpname) {
                    $returnAarr[$k1] = $tmpname;
                }
            }
        }
        return $returnAarr;
    }

    /**
     * Notes: 编辑厂商资质
     */
    public function updateFactory()
    {
        $afid = I('POST.afid');
        $update['factory'] = trim(I('POST.factory'));
        $update['factory_user'] = trim(I('POST.factory_user'));
        $update['factory_tel'] = trim(I('POST.factory_tel'));
        $update['supplier'] = trim(I('POST.supplier'));
        $update['supp_user'] = trim(I('POST.supp_user'));
        $update['supp_tel'] = trim(I('POST.supp_tel'));
        $update['repair'] = trim(I('POST.repair'));
        $update['repa_user'] = trim(I('POST.repa_user'));
        $update['repa_tel'] = trim(I('POST.repa_tel'));
        $update['editdate'] = time();
        if (!$update['factory']) {
            return ['status' => -1, 'msg' => '生产厂商不能为空！'];
        }
        if (!$update['supplier']) {
            return ['status' => -1, 'msg' => '供应商不能为空！'];
        }
        $res = $this->updateData('assets_factory', $update, ['afid' => $afid]);
        if ($res) {
            return ['status' => 1, 'msg' => '厂商资质维护成功！'];
        } else {
            return ['status' => -1, 'msg' => '厂商资质维护失败！'];
        }
    }

    /**
     * Notes: 保存附属设备数据
     */
    public function saveIncreMent()
    {
        $actionType = I('POST.actionType');
        if ($actionType == 'del') {
            //删除附属设备
            $aiid = I('POST.aiid');
            $del = $this->deleteData('assets_increment', ['aiid' => $aiid]);
            if ($del) {
                return ['status' => 1, 'msg' => '删除附属设备成功！', 'aiid' => $aiid];
            } else {
                return ['status' => -1, 'msg' => '删除附属设备失败！'];
            }
        }
        $data['increname'] = trim(I('POST.increname'));
        $data['brand'] = trim(I('POST.brand'));
        $data['model'] = trim(I('POST.model'));
        $data['incre_num'] = trim(I('POST.incre_num'));
        $data['increprice'] = trim(I('POST.increprice'));
        $incre_catid = I('POST.incre_catid');
        $data['remark'] = trim(I('POST.remark'));
        if (!$data['increname']) {
            return ['status' => -1, 'msg' => '附属设备名称不能为空！'];
        }
        if (!$data['brand']) {
            return ['status' => -1, 'msg' => '附属设备品牌不能为空！'];
        }
        if (!$data['model']) {
            return ['status' => -1, 'msg' => '附属设备规格/型号不能为空！'];
        }
        if (!$data['incre_num']) {
            return ['status' => -1, 'msg' => '附属设备数量不能为空！'];
        }
        if ($incre_catid == '') {
            return ['status' => -1, 'msg' => '请选择附属设备分类！'];
        }
        //查询对应的附属设备ID
        $baseSetting = $this->DB_get_one('base_setting', 'set_item,value',
            ['module' => 'assets', 'set_item' => 'acin_category'], '');
        $cat = json_decode($baseSetting['value'], true);
        $cat = array_flip($cat);
        $data['incre_catid'] = $cat[$incre_catid];
        if ($actionType == 'add') {
            //新增附属设备
            $data['assid'] = I('POST.assid');
            $data['addtime'] = time();
            $data['adduser'] = session('username');
            $aiid = $this->insertData('assets_increment', $data);
            if ($aiid) {
                return ['status' => 1, 'msg' => '新增附属设备成功！', 'aiid' => $aiid];
            } else {
                return ['status' => -1, 'msg' => '新增附属设备失败！'];
            }
        } elseif ($actionType == 'update') {
            //更新附属设备信息
            $aiid = I('POST.aiid');
            $data['edittime'] = time();
            $data['edituser'] = session('username');
            $update = $this->updateData('assets_increment', $data, ['aiid' => $aiid]);
            if ($update) {
                return ['status' => 1, 'msg' => '修改附属设备成功！', 'aiid' => $aiid];
            } else {
                return ['status' => -1, 'msg' => '修改附属设备失败！'];
            }
        }
    }

    /**
     * Notes: 生成二维码图片
     *
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
        $filename = $string . '.png';
        //生成二维码,第二个参数为二维码保存路径
        $QRcode::png($value, $savePath . $filename, $errorCorrectionLevel, $matrixPointSize, 2, true);
        if (file_exists($savePath . $filename)) {
            return $savePath . $filename;
        } else {
            return false;
        }
    }


    /**
     * Notes: 获取待入库临时表设备
     *
     * @return mixed
     */
    public function getWatingUploadAssets()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'asc';
        $sort = I('post.sort') ? I('post.sort') : 'hospital_code desc,adddate desc';
        //查询该医院代码是否存在或在该用户管理范围内
        $hospital_id = session('current_hospitalid');
        //查询当前用户所在的医院代码
        $code = $this->DB_get_one('hospital', 'hospital_code',
            ['hospital_id' => $hospital_id, 'is_delete' => 0]);
        $hospital_code = $code['hospital_code'];
        $where['hospital_code'] = $hospital_code;
        $where['adduser'] = session('username');
        $where['is_save'] = 0;//获取未上传的数据
        $total = $this->DB_get_count('assets_info_upload_temp', $where);
        //查询上次未完成保存的数据
        $assets = $this->DB_get_all('assets_info_upload_temp', '*', $where, '', $sort . ' ' . $order,
            $offset . ',' . $limit);
        $error_tempid = [];
        if ($assets) {
            $cdwhere['is_delete'] = C('NO_STATUS');
            $cdwhere['hospital_id'] = $hospital_id;
            //查询数据库已有部门信息
            $alldepar = $this->DB_get_all('department', 'departnum,department', $cdwhere, '', '');
            //查询数据库已有分类信息
            $allcate = $this->DB_get_all('category', 'catenum,category,parentid', $cdwhere, '', '');
            //查询数据库已有设备编号、原编号
            $allassorig = $this->DB_get_all('assets_info',
                'assorignum,serialnum,assorignum_spare,factorynum,inventory_label_id', $cdwhere,
                '', '');
            //把资产模块属性设置返回
            $basesetting = $this->DB_get_all('base_setting', '*', ['module' => 'assets'], '', '');
            $dnum = $dment = $catenum = $catename = $anum_spare = $anum = $serinum = $factorynum = [];
            //科室
            foreach ($alldepar as $k => $v) {
                $dnum[] = $v['departnum'];
                $dment[] = $v['department'];
            }
            //分类
            foreach ($allcate as $k => $v) {
                $catenum[] = $v['catenum'];
                $catename[] = $v['category'];
            }
            //原编码
            foreach ($allassorig as $k => $v) {
                if ($v['assorignum']) {
                    $anum[] = htmlspecialchars($v['assorignum']);
                }
                if ($v['assorignum_spare']) {
                    $anum_spare[] = htmlspecialchars($v['assorignum_spare']);
                }
                if ($v['serialnum']) {
                    $serinum[] = htmlspecialchars($v['serialnum']);
                }
                if ($v['factorynum']) {
                    $factorynum[] = htmlspecialchars($v['factorynum']);
                }
                if ($v['inventory_label_id']) {
                    $inventoryLabelIds[] = htmlspecialchars($v['inventory_label_id']);
                }
            }
            $helpcat = $finance = $capitalfrom = $assfrom = [];
            foreach ($basesetting as $k => $v) {
                if ($v['set_item'] == 'assets_helpcat') {
                    //辅助分类
                    $helpcat = json_decode($v['value'], true);
                }
                if ($v['set_item'] == 'assets_finance') {
                    //财务分类
                    $finance = json_decode($v['value'], true);
                }
                if ($v['set_item'] == 'assets_capitalfrom') {
                    //资金来源
                    $capitalfrom = json_decode($v['value'], true);
                }
                if ($v['set_item'] == 'assets_assfrom') {
                    //设备来源
                    $assfrom = json_decode($v['value'], true);
                }
            }
            //折旧方式
            $depreciation_method = ['平均折旧法', '工作量法', '加速折旧法'];
            //判断待上传数据是否合法
            //var_dump($catenum);
            //var_dump($catename);
            foreach ($assets as $k => $v) {
                if ($v['hospital_code'] != $hospital_code) {
                    //医院代码不存在或没权限添加改医院设备
                    $assets[$k]['hospital_code'] = '<span style="color:red;">' . $v['hospital_code'] . '</span>';
                }
                if (!in_array($v['cate'], $catenum, true) && !in_array($v['cate'], $catename, true)) {
                    //分类不存在
                    $assets[$k]['cate'] = '<span style="color:red;">分类不能为空</span>';
                    if (!in_array($v['tempid'], $error_tempid, true)) {
                        $error_tempid[] = $v['tempid'];
                    }
                }
                if (!in_array($v['department'], $dnum, true) && !in_array($v['department'], $dment, true)) {
                    //科室不存在
                    $assets[$k]['department'] = '<span style="color:red;">' . $v['department'] . '</span>';
                    if (!in_array($v['tempid'], $error_tempid, true)) {
                        $error_tempid[] = $v['tempid'];
                    }
                }
                if ($v['assorignum'] && $v['assorignum'] != '' && $v['assorignum'] != '/' && $v['assorignum'] != '\\') {
                    if (in_array($v['assorignum'], $anum, true) || in_array($v['assorignum'], $anum_spare, true)) {
                        //原编码已存在
                        $assets[$k]['assorignum'] = '<span style="color:red;">' . $v['assorignum'] . '</span>';
                        if (!in_array($v['tempid'], $error_tempid, true)) {
                            $error_tempid[] = $v['tempid'];
                        }
                    }
                }
                if ($v['inventory_label_id'] && $v['inventory_label_id'] != '') {
                    if (in_array($v['inventory_label_id'], $inventoryLabelIds,
                            true) || in_array($v['inventory_label_id'], $anum_spare, true)) {
                        //标签ID已存在
                        $assets[$k]['inventory_label_id'] = '<span style="color:red;">' . $v['inventory_label_id'] . '</span>';
                        if (!in_array($v['tempid'], $error_tempid, true)) {
                            $error_tempid[] = $v['tempid'];
                        }
                    }
                }
                // if ($v['factorynum'] && $v['factorynum'] != '' && $v['factorynum'] != '/' && $v['factorynum'] != '\\') {
                //     if (in_array($v['factorynum'], $factorynum, true)) {
                //         //出厂编号已存在
                //         $assets[$k]['factorynum'] = '<span style="color:red;">' . $v['factorynum'] . '</span>';
                //         if (!in_array($v['tempid'], $error_tempid, true)) {
                //             $error_tempid[] = $v['tempid'];
                //         }
                //     }
                // }
                if ($v['assorignum_spare'] && $v['assorignum_spare'] != '' && $v['assorignum_spare'] != '/' && $v['assorignum_spare'] != '\\') {
                    if (in_array($v['assorignum_spare'], $anum, true) || in_array($v['assorignum_spare'], $anum_spare,
                            true)) {
                        //原编码(备用)已存在
                        $assets[$k]['assorignum_spare'] = '<span style="color:red;">' . $v['assorignum_spare'] . '</span>';
                        if (!in_array($v['tempid'], $error_tempid, true)) {
                            $error_tempid[] = $v['tempid'];
                        }
                    }
                }
                if ($v['serialnum'] && $v['serialnum'] != '' && $v['serialnum'] != '/' && $v['serialnum'] != '\\') {
                    if (in_array($v['serialnum'], $serinum, true)) {
                        //原序列号已存在
                        $assets[$k]['serialnum'] = '<span style="color:red;">' . $v['serialnum'] . '</span>';
                        if (!in_array($v['tempid'], $error_tempid, true)) {
                            $error_tempid[] = $v['tempid'];
                        }
                    }
                }
                if ($v['helpcat']) {
                    if (!in_array($v['helpcat'], $helpcat)) {
                        //辅助分类不存在
                        $assets[$k]['helpcat'] = '<span style="color:red;">' . $v['helpcat'] . '</span>';
                        if (!in_array($v['tempid'], $error_tempid)) {
                            $error_tempid[] = $v['tempid'];
                        }
                    }
                }
                if (!in_array($v['finance'], $finance) && $v['finance']) {
                    //财务分类不存在
                    $assets[$k]['finance'] = '<span style="color:red;">' . $v['finance'] . '</span>';
                    if (!in_array($v['tempid'], $error_tempid)) {
                        $error_tempid[] = $v['tempid'];
                    }
                }
                if (!in_array($v['capitalfrom'], $capitalfrom) && $v['capitalfrom']) {
                    //资金来源不存在
                    $assets[$k]['capitalfrom'] = '<span style="color:red;">' . $v['capitalfrom'] . '</span>';
                    if (!in_array($v['tempid'], $error_tempid)) {
                        $error_tempid[] = $v['tempid'];
                    }
                }
                if (!in_array($v['assfrom'], $assfrom) && $v['assfrom']) {
                    //设备来源不存在
                    $assets[$k]['assfrom'] = '<span style="color:red;">' . $v['assfrom'] . '</span>';
                    if (!in_array($v['tempid'], $error_tempid)) {
                        $error_tempid[] = $v['tempid'];
                    }
                }
                if ($v['depreciation_method']) {
                    if (!in_array($v['depreciation_method'], $depreciation_method)) {
                        //折旧方式不存在
                        $assets[$k]['depreciation_method'] = '<span style="color:red;">' . $v['depreciation_method'] . '</span>';
                        if (!in_array($v['tempid'], $error_tempid)) {
                            $error_tempid[] = $v['tempid'];
                        }
                    }
                }
            }
            foreach ($assets as $k => $v) {
                $assets[$k]['is_firstaid'] = $v['is_firstaid'] == C('YES_STATUS') ? '是' : '否';
                $assets[$k]['is_special'] = $v['is_special'] == C('YES_STATUS') ? '是' : '否';
                $assets[$k]['is_metering'] = $v['is_metering'] == C('YES_STATUS') ? '是' : '否';
                $assets[$k]['is_qualityAssets'] = $v['is_qualityAssets'] == C('YES_STATUS') ? '是' : '否';
                $assets[$k]['is_patrol'] = $v['is_patrol'] == C('YES_STATUS') ? '是' : '否';
                $assets[$k]['is_benefit'] = $v['is_benefit'] == C('YES_STATUS') ? '是' : '否';
                $assets[$k]['is_lifesupport'] = $v['is_lifesupport'] == C('YES_STATUS') ? '是' : '否';
                $assets[$k]['operation'] = '<button style="color:;" class="layui-btn layui-btn-xs layui-btn-danger delTmpAssets" lay-event="delTmpAssets" data-url="/index.php/Admin/Assets/Lookup/batchAddAssets.html" data-id="' . $v['tempid'] . '" data-type="delTmpAssets">删除</button>';
                switch ($v['pay_status']) {
                    case '0':
                        $assets[$k]['pay_statusName'] = '未付清';
                        break;
                    case '1':
                        $assets[$k]['pay_statusName'] = '已付清';
                        break;
                    default:
                        $assets[$k]['pay_statusName'] = '';
                        break;
                }
                switch ($v['is_domestic']) {
                    case '1':
                        $assets[$k]['is_domesticName'] = '国产';
                        break;
                    case '2':
                        $assets[$k]['is_domesticName'] = '进口';
                        break;
                    default:
                        $assets[$k]['is_domesticName'] = '';
                        break;
                }
                switch ($v['assets_level']) {
                    case 1:
                        $assets[$k]['assets_level_name'] = 'Ⅰ类';
                        break;
                    case 2:
                        $assets[$k]['assets_level_name'] = 'Ⅱ类';
                        break;
                    case 3:
                        $assets[$k]['assets_level_name'] = 'Ⅲ类';
                        break;
                    default:
                        $assets[$k]['assets_level_name'] = '';
                        break;
                }
            }
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $assets;
        $result['error_tempid'] = $error_tempid;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 更新设备临时表数据
     */
    public function updateTempData()
    {
        $tempid = I('POST.tempid');
        $inventoryLabelId = I('POST.inventory_label_id');
        unset($_POST['tempid']);
        unset($_POST['type']);
        $newdata = '';
        foreach ($_POST as $k => $v) {
            $data[$k] = addslashes(trim($v));
            if ($k == 'assorignum') {
                $find = [];
                //查询是否已存在重复的设备原编码
                if ($data[$k] != '/') {
                    $find = $this->DB_get_one('assets_info', 'assorignum,assorignum_spare',
                        ['assorignum' => $data[$k], 'assorignum_spare' => $data[$k], '_logic' => 'OR']);
                }
                if ($find['assorignum_spare'] || $find['assorignum']) {
                    return ['status' => -1, 'msg' => '系统中已存在原编码或原编码(备用)为 ' . $data[$k] . ' 的设备！'];
                }

                $newdata = $data[$k];
            } elseif ($k == 'assorignum_spare') {
                $find = [];
                //查询是否已存在重复的设备原编码(备用)
                if ($data[$k] != '/') {
                    $find = $this->DB_get_one('assets_info', 'assorignum,assorignum_spare',
                        ['assorignum' => $data[$k], 'assorignum_spare' => $data[$k], '_logic' => 'OR']);
                }
                if ($find['assorignum_spare'] || $find['assorignum']) {
                    return ['status' => -1, 'msg' => '系统中已存在原编码或原编码备用为 ' . $data[$k] . ' 的设备！'];
                }
                $newdata = $data[$k];
            } elseif ($k == 'factorynum') {
                $find = [];
                if ($data[$k] != '/') {
                    $find = $this->DB_get_one('assets_info', 'factorynum', ['factorynum' => $data[$k]]);
                }
                if ($find['factorynum']) {
                    return ['status' => -1, 'msg' => '系统中已存在出厂编码为 ' . $data[$k] . ' 的设备！'];
                }
                $newdata = $data[$k];
            } elseif ($k == 'cate') {
                //查询对应cate的分类名称
                $find = $this->DB_get_one('category', 'catid,catenum,category', ['catid' => $data[$k]]);
                if ($find['category']) {
                    $data[$k] = $find['category'];
                    $newdata = $data[$k];
                } else {
                    return ['status' => -1, 'msg' => '查找不到 ' . $data[$k] . ' 的分类！！'];
                }
            } elseif ($k == 'department') {
                //查询对应department的科室名称
                $find = $this->DB_get_one('department', 'departid,departnum,department', ['departid' => $data[$k]]);
                if ($find['department']) {
                    $data[$k] = $find['department'];
                    $newdata = $data[$k];
                } else {
                    return ['status' => -1, 'msg' => '查找不到 ' . $data[$k] . ' 的科室！！'];
                }
            } else {
                $newdata = $data[$k];
            }
        }
        if ($_POST['is_patrol']) {
            if ($_POST['is_patrol'] == '是') {
                $data['is_patrol'] = 1;
            } else {
                $data['is_patrol'] = 0;
            }
        }
        if ($_POST['pay_statusName']) {
            switch ($_POST['pay_statusName']) {
                case '已付清':
                    $data['pay_status'] = 1;
                    break;
                case '未付清':
                    $data['pay_status'] = 0;
                    break;
                default:
                    $data['pay_status'] = 3;
                    break;
            }
        }
        if ($_POST['is_domesticName']) {
            switch ($_POST['is_domesticName']) {
                case '国产':
                    $data['is_domestic'] = 1;
                    break;
                case '进口':
                    $data['is_domestic'] = 2;
                    break;
                default:
                    $data['is_domestic'] = 3;
                    break;
            }
        }
        $data['edituser'] = session('username');
        $data['editdate'] = date('Y-m-d H:i:s');
        $res = $this->updateData('assets_info_upload_temp', $data, ['tempid' => $tempid]);
        if ($res) {
            return ['status' => 1, 'msg' => '修改成功！', 'newdata' => $newdata];
        } else {
            return ['status' => -1, 'msg' => '修改失败！'];
        }
    }

    /**
     * Notes: 更新设备主表数据
     */
    public function updateAssetsData()
    {
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $assid = I('POST.assid');
        $inventoryLabelId = I('POST.inventory_label_id');
        unset($_POST['assid']);
        unset($_POST['type']);
        $newdata = '';
        $factoryField = [
            'factory',
            'factory_user',
            'factory_tel',
            'supplier',
            'supp_user',
            'supp_tel',
            'repair',
            'repa_user',
            'repa_tel',
        ];
        $updatTable = 'assets_info';
        foreach ($_POST as $k => $v) {
            $data[$k] = trim($v);
            if (in_array($k, $factoryField)) {
                $updatTable = 'assets_factory';
            }
            if ($k == 'assorignum') {
                //查询是否已存在重复的设备原编码
                $map['assorignum_spare'] = $data['assorignum'];
                $map['assorignum'] = $data['assorignum'];
                $map['_logic'] = 'OR';
                $asswhere['_complex'] = $map;
                $asswhere['assid'] = ['neq', $assid];
                $asswhere['is_delete'] = '0';
                $find = $this->DB_get_one('assets_info', 'assorignum', $asswhere);
                if ($find['assorignum']) {
                    return ['status' => -1, 'msg' => '系统中已存在原编码为 ' . $data[$k] . ' 的设备！'];
                }
                $newdata = $data[$k];
            } elseif ($k == 'inventory_label_id') {
                //查询是否已存在重复的盘点标签id
                $find = $this->DB_get_one('assets_info', 'inventory_label_id,assid',
                    ['inventory_label_id' => $inventoryLabelId]);
                if ($find['inventory_label_id'] && $assid != $find['assid']) {
                    return ['status' => -1, 'msg' => '系统中已存在标签ID为 ' . $data[$k] . ' 的设备！'];
                }
            } elseif ($k == 'catid') {
                //查询对应cate的分类名称
                $find = $this->DB_get_one('category', 'catid,catenum,category', ['catid' => $data[$k]]);
                if ($find['category']) {
                    $newdata = $find['category'];
                } else {
                    return ['status' => -1, 'msg' => '查找不到 ' . $data[$k] . ' 的分类！！'];
                }
            } elseif ($k == 'departid') {
                //查询对应department的科室名称
                $find = $this->DB_get_one('department', 'departid,departnum,department', ['departid' => $data[$k]]);
                if ($find['department']) {
                    $newdata = $find['department'];
                    unset($data[$k]);
                    $this->editdepartment($assid);
                } else {
                    return ['status' => -1, 'msg' => '查找不到 ' . $data[$k] . ' 的科室！！'];
                }
            } elseif ($k == 'is_firstaid') {
                $newdata = $data[$k];
                $data[$k] = $data[$k] == '是' ? 1 : 0;
            } elseif ($k == 'is_special') {
                $newdata = $data[$k];
                $data[$k] = $data[$k] == '是' ? 1 : 0;
            } elseif ($k == 'is_metering') {
                $newdata = $data[$k];
                $data[$k] = $data[$k] == '是' ? 1 : 0;
            } elseif ($k == 'is_qualityAssets') {
                $newdata = $data[$k];
                $data[$k] = $data[$k] == '是' ? 1 : 0;
            } elseif ($k == 'is_patrol') {
                $newdata = $data[$k];
                $data[$k] = $data[$k] == '是' ? 1 : 0;
            } elseif ($k == 'is_benefit') {
                $newdata = $data[$k];
                $data[$k] = $data[$k] == '是' ? 1 : 0;
            } elseif ($k == 'is_lifesupport') {
                $newdata = $data[$k];
                $data[$k] = $data[$k] == '是' ? 1 : 0;
            } elseif ($k == 'is_patrol') {
                $newdata = $data[$k];
                $data[$k] = $data[$k] == '是' ? 1 : 0;
            } elseif ($k == 'helpcatid') {
                $newdata = $baseSetting['assets']['assets_helpcat']['value'][$data[$k]];
            } elseif ($k == 'financeid') {
                $newdata = $baseSetting['assets']['assets_finance']['value'][$data[$k]];
            } elseif ($k == 'capitalfrom') {
                $newdata = $baseSetting['assets']['assets_capitalfrom']['value'][$data[$k]];
            } elseif ($k == 'assfromid') {
                $newdata = $baseSetting['assets']['assets_assfrom']['value'][$data[$k]];
            } else {
                $newdata = $data[$k];
            }
        }
        $data['edituser'] = session('username');
        $data['editdate'] = time();
        $res = $this->updateData($updatTable, $data, ['assid' => $assid]);
        if ($res) {
            if (isset($data['assets']) || isset($data['model']) || isset($data['factorynum']) || isset($data['opendate']) || isset($data['factory']) || isset($data['buy_price'])) {
                //如果是修改这些字段，则对应修改repair表的相应字段
                if (isset($updata['buy_price'])) {
                    $this->updateData('repair', ['assprice' => $updata['buy_price']], ['assid' => ['in', $assidArr]]);
                } else {
                    $this->updateData('repair', $data, ['assid' => $assid]);
                }
            }
            if (isset($data['assets']) || isset($data['model']) || isset($data['unit']) || isset($data['factory'])) {
                //如果是修改这些字段，则对应修改metering_plan表的相应字段
                $this->updateData('metering_plan', $data, ['assid' => $assid]);
            }
            return ['status' => 1, 'msg' => '修改成功！', 'newdata' => $newdata];
        } else {
            return ['status' => -1, 'msg' => '修改失败！'];
        }
    }

    /**
     * Notes: 删除设备临时表数据
     */
    public function delTempData()
    {
        $tempid = trim(I('POST.tempid'), ',');
        $tempArr = explode(',', $tempid);
        $res = $this->deleteData('assets_info_upload_temp', ['tempid' => ['in', $tempArr]]);
        if ($res) {
            return ['status' => 1, 'msg' => '删除成功！'];
        } else {
            return ['status' => -1, 'msg' => '删除失败！'];
        }
    }

    /**
     * Notes: 接收上传的excel数据
     */
    public function uploadData()
    {
        if (empty($_FILES)) {
            return ['status' => -1, 'msg' => '请上传文件'];
        }
        $uploadConfig = [
            'maxSize' => 3145728,
            'rootPath' => './Public/',
            'savePath' => 'uploads/',
            'saveName' => ['uniqid', ''],
            'exts' => ['xlsx', 'xls', 'xlsm'],
            'autoSub' => true,
            'subName' => ['date', 'Ymd'],
        ];
        $upload = new \Think\Upload($uploadConfig);
        $info = $upload->upload();
        if (!$info) {
            return ['status' => -1, 'msg' => '上传出错，请检查相关文件夹权限'];
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
        $excelDate = new \PHPExcel_Shared_Date();
        $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
        $currentSheet = $PHPExcel->getSheet(0);        //**读取excel文件中的指定工作表*/
        $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
        ++$allColumn;
        $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
        $data = [];
        $cellname = [
            'A' => 'hospital_code',
            'B' => 'assets',
            'C' => 'assorignum',
            'D' => 'assorignum_spare',
            'E' => 'serialnum',
            'F' => 'registration',
            'G' => 'model',
            'H' => 'cate',
            'I' => 'assets_level',
            'J' => 'buy_price',
            'K' => 'paytime',
            'L' => 'pay_status',
            'M' => 'expected_life',
            'N' => 'residual_value',
            'O' => 'guarantee_date',
            'P' => 'helpcat',
            'Q' => 'brand',
            'R' => 'patrol_xc_cycle',
            'S' => 'patrol_pm_cycle',
            'T' => 'quality_cycle',
            'U' => 'metering_cycle',
            'V' => 'unit',
            'W' => 'factorynum',
            'X' => 'factorydate',
            'Y' => 'invoicenum',
            'Z' => 'is_firstaid',
            'AA' => 'is_special',
            'AB' => 'is_metering',
            'AC' => 'is_qualityAssets',
            'AD' => 'is_patrol',
            'AE' => 'is_benefit',
            'AF' => 'is_lifesupport',
            'AG' => 'is_domestic',
            'AH' => 'department',
            'AI' => 'assetsrespon',
            'AJ' => 'finance',
            'AK' => 'assfrom',
            'AL' => 'capitalfrom',
            'AM' => 'storage_date',
            'AN' => 'opendate',
            'AO' => 'depreciation_method',
            'AP' => 'depreciable_lives',
            'AQ' => 'factory',
            'AR' => 'factory_user',
            'AS' => 'factory_tel',
            'AT' => 'supplier',
            'AU' => 'supp_user',
            'AV' => 'supp_tel',
            'AW' => 'repair',
            'AX' => 'repa_user',
            'AY' => 'repa_tel',
            'AZ' => 'remark',
            'BA' => 'inventory_label_id',
        ];
        //需要进行日期处理的保存在一个数组
        $toDate = [
            'guarantee_date',
            'factorydate',
            'storage_date',
            'opendate',
            'paytime',
        ];
        //设备类型转换
        $assetstype = [
            'is_firstaid',
            'is_special',
            'is_metering',
            'is_qualityAssets',
            'is_patrol',
            'is_benefit',
            'is_lifesupport',
        ];
        for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for ($colIndex = 'A'; $colIndex != $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                if (in_array($cellname[$colIndex], $toDate)) {
                    if (!$currentSheet->getCell($addr)->getValue()) {
                        $cell = '0000-00-00';
                    } else {
                        $d = $currentSheet->getCell($addr)->getValue();
                        $cell = '';
                        if (strpos($d, '/') !== false) {
                            $dt = explode('/', $d);
                            if ($dt[1] < 10) {
                                $dt[1] = '0' . (int)$dt[1];
                            }
                            if ($dt[2] < 10) {
                                $dt[2] = '0' . (int)$dt[2];
                            }
                            $cell = implode('-', $dt);

                        } elseif (strpos($d, '-') == true) {
                            $cell = $d;
                        } elseif (strlen($d) == 8) {
                            $cell = date('Y-m-d', strtotime($d));
                        } else {
                            $cell = gmdate("Y-m-d", $excelDate::ExcelToPHP($d));
                        }
                    }
                } else {
                    $cell = $currentSheet->getCell($addr)->getValue();
                }
                if ($cell instanceof \PHPExcel_RichText) { //富文本转换字符串
                    $cell = $cell->__toString();
                }
                if ($cellname[$colIndex] == 'assets') {
                    if (!$cell) {
                        continue;
                    }
                }
                if ($cellname[$colIndex] == 'buy_price') {
                    $cell = str_replace(',', '', $cell);
                    $cell = str_replace('，', '', $cell);
                }
                if (in_array($cellname[$colIndex], $assetstype)) {
                    if ($cell == '是') {
                        $cell = 1;
                    } else {
                        $cell = 0;
                    }
                }
                if ($cellname[$colIndex] == 'pay_status') {
                    switch ($cell) {
                        case '已付清':
                            $cell = 1;
                            break;
                        case '未付清':
                            $cell = 0;
                            break;
                        default:
                            $cell = 3;
                            break;
                    }
                }
                if ($cellname[$colIndex] == 'is_domestic') {
                    switch ($cell) {
                        case '国产':
                            $cell = 1;
                            break;
                        case '进口':
                            $cell = 2;
                            break;
                        default:
                            $cell = 3;
                            break;
                    }
                }
                $data[$rowIndex - 2][$cellname[$colIndex]] = trim($cell) ? trim($cell) : '';
            }
        }
        if (!$data) {
            //上传的文件数据和临时表中已存在数据重复
            return ['status' => -1, 'msg' => '没有新数据被上传！请检查文件数据是否已上传过，或是否符合要求！'];
        }
        //过滤空设备名称数据
        foreach ($data as $k => $v) {
            if (!isset($data[$k]['assets'])) {
                unset($data[$k]);
            }
        }
        //保存数据到临时表
        $insertData = [];
        $num = 0;
        //判断医院代码
        $hosid = session('current_hospitalid');
        $assModel = new AssetsInfoModel();
        $hoscode = $assModel->DB_get_one('hospital', 'hospital_code', ['hospital_id' => $hosid, 'is_delete' => 0]);
        if (!$hoscode) {
            return ['status' => -1, 'msg' => '当前医院【代码：' . $hoscode['hospital_code'] . '】不存在或已被删除！'];
        }
        if ($data[0]['hospital_code'] && $data[0]['hospital_code'] != $hoscode['hospital_code']) {
            return [
                'status' => -1,
                'msg' => '当前医院【代码：' . $hoscode['hospital_code'] . '】与上传的医院数据【代码：' . $data[0]['hospital_code'] . '】不匹配！',
            ];
        }
        Db::execute("SET sql_mode = 'ALLOW_INVALID_DATES'");
        foreach ($data as $k => $v) {
            if ($num < $this->len) {
                //$this->len条存一次数据到数据库
                $tempid = getRandomId();
                $insertData[$num]['tempid'] = $tempid;
                $insertData[$num]['adduser'] = session('username');
                $insertData[$num]['adddate'] = date('Y-m-d H:i:s');
                $insertData[$num]['is_save'] = 0;
                if ($v['address'] == '') {
                    $insertData[$num]['address'] = '/';
                }
                if ($v['managedepart'] == '') {
                    $insertData[$num]['managedepart'] = '/';
                }
                foreach ($v as $k1 => $v1) {
                    if ($k1 == 'buy_price') {
                        $insertData[$num][$k1] = sprintf("%.2f", $v1);
//                        $insertData[$num][$k1] = str_replace(',', '', $v1);
                    } else {
                        $insertData[$num][$k1] = $v1;
                    }
                }
                $num++;
            }
            if ($num == $this->len) {
                //插入数据
                $this->insertDataALL('assets_info_upload_temp', $insertData);
                //重置数据
                $num = 0;
                $insertData = [];
            }
        }
        if ($insertData) {
            foreach ($insertData as $k => $v) {
                if ($v['hospital_code'] == '') {
                    unset($insertData[$k]);
                }
            }
            $this->insertDataALL('assets_info_upload_temp', $insertData);
        }
        return ['status' => 1, 'msg' => '上传数据成功，请核准后再保存！'];
    }

    function excelTime($date, $time = false)
    {
        if (function_exists('GregorianToJD')) {
            if (is_numeric($date)) {
                $jd = GregorianToJD(1, 1, 1970);
                $gregorian = JDToGregorian($jd + intval($date) - 25569);
                $date = explode('/', $gregorian);
                $date_str = str_pad($date [2], 4, '0', STR_PAD_LEFT)
                    . "-" . str_pad($date [0], 2, '0', STR_PAD_LEFT)
                    . "-" . str_pad($date [1], 2, '0', STR_PAD_LEFT)
                    . ($time ? " 00:00:00" : '');
                return $date_str;
            }
        } else {
            $date = $date > 25568 ? $date + 1 : 25569;
            /*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/
            $ofs = (70 * 365 + 17 + 2) * 86400;
            $date = date("Y-m-d", ($date * 86400) - $ofs) . ($time ? " 00:00:00" : '');
        }
        return $date;
    }

    /**
     * Notes: 格式化设备类型
     *
     * @param $assets array 设备集合
     *
     * @return mixed
     */
    public function formatAssetsType($assets)
    {
        foreach ($assets as $k => $v) {
            $assets[$k]['is_firstaid'] = $v['is_firstaid'] == C('YES_STATUS') ? '是' : '否';
            $assets[$k]['is_special'] = $v['is_special'] == C('YES_STATUS') ? '是' : '否';
            $assets[$k]['is_metering'] = $v['is_metering'] == C('YES_STATUS') ? '是' : '否';
            $assets[$k]['is_qualityAssets'] = $v['is_qualityAssets'] == C('YES_STATUS') ? '是' : '否';
            $assets[$k]['is_benefit'] = $v['is_benefit'] == C('YES_STATUS') ? '是' : '否';
            $assets[$k]['is_patrol'] = $v['is_patrol'] == C('YES_STATUS') ? '是' : '否';
            $assets[$k]['is_lifesupport'] = $v['is_lifesupport'] == C('YES_STATUS') ? '是' : '否';
            switch ($v['pay_status']) {
                case '0':
                    $assets[$k]['pay_status'] = '未付清';
                    break;
                case '1':
                    $assets[$k]['pay_status'] = '已付清';
                    break;
                default:
                    $assets[$k]['pay_status'] = '';
                    break;
            }
            switch ($v['is_domestic']) {
                case '1':
                    $assets[$k]['is_domestic'] = '国产';
                    break;
                case '2':
                    $assets[$k]['is_domestic'] = '进口';
                    break;
                default:
                    $assets[$k]['is_domestic'] = '';
                    break;
            }
            switch ($v['assets_level']) {
                case 1:
                    $assets[$k]['assets_level_name'] = 'Ⅰ类';
                    break;
                case 2:
                    $assets[$k]['assets_level_name'] = 'Ⅱ类';
                    break;
                case 3:
                    $assets[$k]['assets_level_name'] = 'Ⅲ类';
                    break;
                default:
                    $assets[$k]['assets_level_name'] = '';
                    break;
            }
        }
        return $assets;
    }

    /**
     * Notes: 返回要查询的字段
     *
     * @param $table  string 要获取字段的表名称
     * @param $fields array 全部要查字段
     * @param $alias  string 虚拟表名
     *
     * @return string
     */
    public function getFields($table, $fields, $alias)
    {
        $tableName = $this->tablePrefix . $table;
        $sql = "SHOW FULL COLUMNS FROM " . $tableName;
        $res = M($this->tableName)->query($sql);
        $fieldStr = '';
        foreach ($res as $k => $v) {
            if (in_array($v['Field'], $fields)) {
                $fieldStr .= $alias . '.' . $v['Field'] . ',';
            }
        }
        $fieldStr = trim($fieldStr, ',');
        return $fieldStr;
    }

    public function formatData($data)
    {
        $depreciation_method = ['平均折旧法', '工作量法', '加速折旧法'];
        $departname = [];
        $catname = [];
        $baseSetting = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($data as $k => $v) {
            if (!$showPrice) {
                $data[$k]['buy_price'] = '***';
            }
            $data[$k]['xuhao'] = $k + 1;
            $data[$k]['departperson'] = $departname[$v['departid']]['departrespon'];
            $data[$k]['departid'] = $departname[$v['departid']]['department'];
            $data[$k]['catid'] = $catname[$v['catid']]['category'];
            if ($data[$k]['factorydate'] != '/') {
                $data[$k]['factorydate'] = $v['factorydate'] != '0000-00-00' ? (strtotime($v['factorydate']) ? date('Y/n/j',
                    strtotime($v['factorydate'])) : '/') : '/';
            }
            if ($data[$k]['opendate'] != '/') {
                $data[$k]['opendate'] = $v['opendate'] != '0000-00-00' ? (strtotime($v['opendate']) ? date('Y/n/j',
                    strtotime($v['opendate'])) : '/') : '/';
            }
            if ($data[$k]['storage_date'] != '/') {
                $data[$k]['storage_date'] = $v['storage_date'] != '0000-00-00' ? (strtotime($v['storage_date']) ? date('Y/n/j',
                    strtotime($v['storage_date'])) : '/') : '/';
            }
            if ($data[$k]['guarantee_date'] != '/') {
                $data[$k]['guarantee_date'] = $v['guarantee_date'] != '0000-00-00' ? (strtotime($v['guarantee_date']) ? date('Y/n/j',
                    strtotime($v['guarantee_date'])) : '/') : '/';
            }
            switch ($v['status']) {
                case C('ASSETS_STATUS_USE'):
                    $data[$k]['status'] = C('ASSETS_STATUS_USE_NAME');
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $data[$k]['status'] = C('ASSETS_STATUS_REPAIR_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $data[$k]['status'] = C('ASSETS_STATUS_SCRAP_NAME');
                    break;
                case C('ASSETS_STATUS_TRANSFER_ON'):
                    $data[$k]['status'] = C('ASSETS_STATUS_TRANSFER_ON_NAME');
                    break;
                default:
                    $data[$k]['status'] = '未知状态';
                    break;
            }
            $data[$k]['helpcatid'] = $baseSetting['assets']['assets_helpcat']['value'][$v['helpcatid']];
            $data[$k]['financeid'] = $baseSetting['assets']['assets_finance']['value'][$v['financeid']];
            $data[$k]['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$v['capitalfrom']];
            $data[$k]['assfromid'] = $baseSetting['assets']['assets_assfrom']['value'][$v['assfromid']];
            $data[$k]['depreciation_method'] = $depreciation_method[$v['depreciation_method'] - 1];
            $data[$k]['is_firstaid'] = $v['is_firstaid'] == 1 ? '是' : '否';
            if ($v['is_domestic'] == 1) {
                $data[$k]['is_domestic'] = '国产';
            } elseif ($v['is_domestic'] == 2) {
                $data[$k]['is_domestic'] = '进口';
            } else {
                $data[$k]['is_domestic'] = '';
            }
            if ($v['pay_status'] == 1) {
                $data[$k]['pay_status'] = '已付清';
            } elseif ($v['pay_status'] == 2) {
                $data[$k]['pay_status'] = '未付清';
            } else {
                $data[$k]['pay_status'] = '';
            }
            switch ($v['print_status']) {
                case '0':
                    $data[$k]['print_status'] = '初始状态';
                    break;
                case '1':
                    $data[$k]['print_status'] = '已核实';
                    break;
                case '2':
                    $data[$k]['print_status'] = '已核实(无法贴标)';
                    break;
            }
            if ($v['code_url']) {
                $fileExists = file_exists('.' . $v['code_url']);
                if (!$fileExists) {
                    //文件已不存在
                    $data[$k]['code_status'] = '未打印';
                } else {
                    $data[$k]['code_status'] = '已打印';
                }
            } else {
                $data[$k]['code_status'] = '未打印';
            }
            $data[$k]['is_special'] = $v['is_special'] == 1 ? '是' : '否';
            $data[$k]['is_patrol'] = $v['is_patrol'] == 1 ? '是' : '否';
            $data[$k]['is_metering'] = $v['is_metering'] == 1 ? '是' : '否';
            $data[$k]['is_qualityAssets'] = $v['is_qualityAssets'] == 1 ? '是' : '否';
            $data[$k]['is_benefit'] = $v['is_benefit'] == 1 ? '是' : '否';
            $data[$k]['is_lifesupport'] = $v['is_lifesupport'] == 1 ? '是' : '否';
        }
        return $data;
    }


    /**
     * Notes: 获取设备质控记录
     *
     * @return array
     */
    public function getAssetsQaulityRecord()
    {
        $assid = I('POST.assid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        if (!$sort) {
            $sort = 'A.addtime';
        }
        if (!$order) {
            $order = 'asc';
        }
        $fields = "A.qsid,A.assid,A.do_date,A.end_date,A.is_cycle,A.cycle,A.period,A.qtemid,A.username,A.plan_name,A.start_date,A.is_start,B.addtime,B.result,B.report";
        $join = "LEFT JOIN sb_quality_details as B on A.qsid = B.qsid";
        $where['A.assid'] = $assid;
        $total = $this->DB_get_count_join('quality_starts', 'A', $join, $where);
        $records = $this->DB_get_all_join('quality_starts', 'A', $fields, $join, $where, '',
            $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($records as $k => $v) {
            if (is_null($v['is_cycle'])) {
                $records[$k]['is_cycle'] = '';
            } else {
                if ($v['is_cycle'] == 1) {
                    $records[$k]['is_cycle'] = '是';
                    $records[$k]['period'] = '第 ' . $v['period'] . ' 期';
                } else {
                    $records[$k]['is_cycle'] = '否';
                }
            }
            $jpghtml = '';
            if ($v['report']) {
                //有报告地址
                $supplement = 'data-path="' . $v['report'] . '" data-name="' . $v['Filename'] . '"';
                $jpghtml .= $this->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' showFile', '',
                    $supplement);
                $jpghtml .= $this->returnListLink('下载', '', '',
                    C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                $records[$k]['report'] = $jpghtml;
            }
            $records[$k]['report'] = $jpghtml;
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $records;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取设备转科记录
     *
     * @return mixed
     */
    public function getAssetsTransferRecord()
    {
        $page = I('post.page');
        $limit = I('post.limit');
        $assid = I('post.assid');
        $offset = ($page - 1) * $limit;
        //加载部门缓存
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        //设备转科记录
        $transferTotal = $this->DB_get_count('assets_transfer', ['assid' => $assid], '');
        $transfer = $this->DB_get_all('assets_transfer', '', ['assid' => $assid], '', 'atid asc',
            $offset . ',' . $limit);
        if (!$transfer) {
            $join = 'LEFT JOIN sb_assets_transfer_detail AS B ON A.atid = B.atid';
            $transfer = $this->DB_get_all_join('assets_transfer', 'A', '', $join, ['subsidiary_assid' => $assid], '',
                'A.atid asc', $offset . ',' . $limit);
        }
        foreach ($transfer as $k => $v) {
            $transfer[$k]['tranout_department'] = $departname[$v['tranout_departid']]['department'];
            $transfer[$k]['tranin_department'] = $departname[$v['tranin_departid']]['department'];
            //$transfer[$k]['approve_status'] = (int)$v['approve_status'];
            if ($v['is_check'] == 0) {
                $transfer[$k]['status'] = '未验收';
            } elseif ($v['is_check'] == 1) {
                $transfer[$k]['status'] = '通过';
            } else {
                $transfer[$k]['status'] = '不通过';
            }
        }
        $result['code'] = 200;
        $result['limit'] = $limit;
        $result['total'] = $transferTotal;
        $result["rows"] = $transfer;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取设备维修记录
     *
     * @return mixed
     */
    public function getAssetsRepairRecord()
    {
        $departids = session('departid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $assets = I('post.getRepairSearchListAssets');
        $model = I('post.getRepairSearchListModel');
        $assetsNum = I('post.getRepairSearchListAssetsNum');
        $factory = I('post.getRepairSearchListFactory');
        $department = I('post.department');
        $opendate = I('post.getRepairSearchListOpendate');
        $repnum = I('post.getRepairSearchListRepnum');
        $repairStatus = I('post.getRepairSearchListRepairStatus');
        $applicant = I('post.getRepairSearchListApplicant');
        $startDate = I('post.getRepairSearchListStartDate');
        $endDate = I('post.getRepairSearchListEndDate');
        $examineStatus = I('post.getRepairSearchListExamineStatus');
        $repairType = I('post.getRepairSearchListRepairType');
        $engineerStartDate = I('post.getRepairSearchListEngineerStartDate');
        $engineerEndDate = I('post.getRepairSearchListEngineerEndDate');
        $meetStatus = I('post.getRepairSearchListMeetStatus');
        $isnum = I('post.getRepairSearchListIsnum');
        $guarantee = I('post.getRepairSearchListGuarantee');
        $engineer = I('post.getRepairSearchListEngineer');
        $repair_category = I('post.repair_category');
        $hospital_id = I('post.hospital_id');
        $where = [];
        if ($department) {
            $where['A.departid'] = ['in', $department];
        } else {
            $where['A.departid'] = ['in', $departids];
        }
        //如果是生命线历程页面点进来的
        if (I('post.assid')) {
            $where['A.assid'] = I('post.assid');
        }
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        if (!$sort) {
            $sort = 'repid ';
        }
        if (!$order) {
            $order = 'asc';
        }
        if ($assets) {
            //设备名称搜索
            $where['A.assets'] = ['like', '%' . $assets . '%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['A.assnum'] = ['like', '%' . $assetsNum . '%'];
        }
        if ($model) {
            //规格型号搜索
            $where['A.model'] = ['like', '%' . $model . '%'];
        }
        if ($repnum) {
            //维修单编号搜索
            $where['A.repnum'] = ['like', '%' . $repnum . '%'];
        }
        if ($opendate) {
            //开机日期搜索
            $openPretime = strtotime($opendate) - 1;
            $openNexttime = strtotime($opendate) + 24 * 3600;
            $where['A.opendate'][] = ['gt', $openPretime];
            $where['A.opendate'][] = ['lt', $openNexttime];
        }
        if ($factory) {
            //生产厂商搜索
            $where['A.factory'] = ['like', '%' . $factory . '%'];
        }
        if ($applicant) {
            //报修人搜索
            $where['A.applicant'] = $applicant;
        }
        if ($engineer) {
            //维修工程师搜索
            $where['A.engineer'] = $engineer;
        }
        if ($examineStatus) {
            //验收状态
            $where['A.status'] = $examineStatus;
        }
        if ($repairType != '') {
            //维修性质搜索
            $where['A.repair_type'] = $repairType;
        }
        if ($repairStatus) {
            //维修状态搜索
            switch ($repairStatus) {
                case 1:
                    $where['A.status'] = C('REPAIR_HAVE_REPAIRED');
                    $where['is_assign'] = C('YES_STATUS');
                    break;
                case 2:
                    $whereOR[0]['A.status'] = C('REPAIR_RECEIPT');
                    $whereOR[1]['A.status'] = C('REPAIR_QUOTATION');
                    $whereOR['_logic'] = 'or';
                    $where['_complex'] = $whereOR;
                    break;
                case 4:
                    $where['A.status'] = C('REPAIR_AUDIT');
                    break;
                case 5:
                    $where['A.status'] = C('REPAIR_MAINTENANCE');
                    break;
                case 6:
                    $where['A.status'] = C('REPAIR_MAINTENANCE_COMPLETION');
                    break;
                case 7:
                    $where['A.status'] = C('REPAIR_ALREADY_ACCEPTED');
                    break;
                case  9:
                    $where['A.status'] = C('REPAIR_HAVE_REPAIRED');
                    $where['is_assign'] = C('NO_STATUS');
                    break;
            }
        }
        if ($isnum) {
            //是否产生配件
            if ($isnum == 1) {
                $where['A.part_num'] = ['gt', 0];
            } elseif ($isnum == 2) {
                $where['A.part_num'] = 0;
            }
        }
        if ($meetStatus) {
            //接单
            $where['A.status'] = $meetStatus;
        }
        if ($guarantee != '' && $guarantee != '-1') {
            //保修
            $where['A.is_guarantee'] = $guarantee;
        }
        if ($startDate) {
            //报修时间--开始
            $where['A.applicant_time'][] = ['gt', (strtotime($startDate) - 1)];
        }
        if ($endDate) {
            //报修时间--结束
            $where['A.applicant_time'][] = ['lt', (strtotime($endDate) + 24 * 3600)];
        }
        if ($engineerStartDate) {//维修时间--开始
            $where['A.engineer_time'][] = ['gt', (strtotime($engineerStartDate) - 1)];
        }
        if ($engineerEndDate) {//维修时间--结束
            $where['A.engineer_time'][] = ['lt', (strtotime($engineerEndDate) + 24 * 3600)];
        }
        if ($repair_category) {
            $where['A.repair_category'] = $repair_category;
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }


        $fields = 'A.repid,A.repnum,A.assnum,A.assets,A.model,A.departid,
        A.factory,A.opendate,A.status,A.is_assign,A.applicant_time,A.applicant,
        A.repair_type,A.engineer_time,A.is_guarantee,A.engineer,A.part_num,A.over_status,B.assid,
        B.afid,B.acid,A.breakdown,A.fault_problem,A.repair_remark,A.dispose_detail,A.repair_type,A.actual_price,A.working_hours';
        $join[0] = 'LEFT JOIN sb_assets_info AS B ON B.assid = A.assid';
        $total = $this->DB_get_count_join('repair', 'A', $join, $where);
        $repairinfo = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', 'A.' . $sort . ' ' . $order,
            $offset . "," . $limit);
        if (!$repairinfo) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        //判断有无撤单的权限
        $cancelRepair = get_menu('Repair', 'RepairSearch', 'cancelRepair');
        $repidArr = [];
        $RepMod = new RepairModel();
        foreach ($repairinfo as &$value) {
            $repidArr[] = $value['repid'];
            $value['fault_problem'] = $RepMod->getFaultProblem($value['repid']);
            if ($value['actual_price'] == 0) {
                $value['actual_price'] = $value['expect_price'];
            }
            //维修性质
            if ($value['repair_type'] == C('REPAIR_TYPE_IS_STUDY')) {
                $value['repairTypeName'] = '自修';
            } elseif ($value['repair_type'] == C('REPAIR_TYPE_IS_GUARANTEE')) {
                $value['repairTypeName'] = '维保厂家';
            } elseif ($value['repair_type'] == C('REPAIR_TYPE_THIRD_PARTY')) {
                $value['repairTypeName'] = '第三方维修';
            } elseif ($value['repair_type'] == C('REPAIR_TYPE_IS_SCENE')) {
                $value['repairTypeName'] = '现场解决';
            } else {
                $value['repairTypeName'] = '';
            }
        }


        $fileWhere['repid'] = ['IN', $repidArr];
        $fileWhere['is_delete'] = ['EQ', C('NO_STATUS')];
        $file = $this->DB_get_all('repair_file', 'repid', $fileWhere, 'repid');
        $fileData = [];
        if ($file) {
            foreach ($file as &$filev) {
                $fileData[$filev['repid']] = true;
            }
        }


        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";


        foreach ($repairinfo as $k => $v) {
            $repairinfo[$k]['over_status'] = (int)$v['over_status'];
            $html = '<div class="layui-btn-group">';
            $repairinfo[$k]['applicant_time'] = getHandleDate($v['applicant_time']);
            $repairinfo[$k]['department'] = $departname[$v['departid']]['department'];
            if (!I('post.assid')) {
                //如果不是生命历程页面点进来获取的 就可以显示设备详情按钮
                $html .= $this->returnButtonLink('设备详情', C('ADMIN_NAME') . '/Lookup/showAssets.html',
                    'layui-btn layui-btn-xs layui-btn-primary', '', 'lay-event = showAssets');
            }
            $html .= $this->returnButtonLink('维修详情', C('ADMIN_NAME') . '/RepairSearch/showRepair.html',
                'layui-btn layui-btn-xs layui-btn-normal', '', 'lay-event = showRepair');
            if ($fileData[$v['repid']]) {
                $html .= $this->returnButtonLink('相关文件', C('ADMIN_NAME') . '/RepairSearch/showUpload.html',
                    'layui-btn layui-btn-xs layui-btn-warm', '', 'lay-event = showUpload');
            } else {
                $html .= $this->returnButtonLink('相关文件', '',
                    'layui-btn layui-btn-xs layui-btn-normal layui-btn-disabled', '', '');
            }
            if ($v['status'] == -1) {
                $html .= $this->returnButtonLink('已撤单', '',
                    'layui-btn layui-btn-xs layui-btn-normal layui-btn-disabled', '', '');
            } else {
                if ($v['status'] < 7 && $cancelRepair) {
                    $html .= $this->returnButtonLink('撤单', C('ADMIN_NAME') . '/RepairSearch/cancelRepair.html',
                        'layui-btn layui-btn-xs layui-btn-danger', '', 'lay-event = cancelRepair');
                } else {
                    $html .= $this->returnButtonLink('撤单', '',
                        'layui-btn layui-btn-xs layui-btn-normal layui-btn-disabled', '', '');
                }
            }
            $html .= '</div>';
            $repairinfo[$k]['operation'] = $html;
            switch ($v['status']) {
                case C('REPAIR_HAVE_REPAIRED'):
                    $repairinfo[$k]['statusName'] = C('REPAIR_HAVE_REPAIRED_NAME');
                    break;
                case C('REPAIR_RECEIPT'):
                    $repairinfo[$k]['statusName'] = C('REPAIR_RECEIPT_NAME');
                    break;
                case C('REPAIR_HAVE_OVERHAULED'):
                    $repairinfo[$k]['statusName'] = C('REPAIR_HAVE_OVERHAULED_NAME');
                    break;
                case C('REPAIR_QUOTATION'):
                    $repairinfo[$k]['statusName'] = C('REPAIR_QUOTATION_NAME');
                    break;
                case C('REPAIR_AUDIT'):
                    $repairinfo[$k]['statusName'] = C('REPAIR_AUDIT_NAME');
                    break;
                case C('REPAIR_MAINTENANCE'):
                    $repairinfo[$k]['statusName'] = C('REPAIR_MAINTENANCE_NAME');
                    break;
                case C('REPAIR_MAINTENANCE_COMPLETION'):
                    $repairinfo[$k]['statusName'] = C('REPAIR_MAINTENANCE_COMPLETION_NAME');
                    break;
                default:
                    $repairinfo[$k]['statusName'] = '已完成';
                    break;
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $repairinfo;
        return $result;
    }

    /**
     * Notes: 上传设备技术资料、设备档案等文件接口
     *
     * @return array
     */
    public function uploadAssetsFile()
    {
        $Tool = new ToolController();
        //设置文件类型
        $type = ['jpg', 'jpeg', 'png', 'pdf', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'rar', 'zip'];
        //文件名目录设置
        switch ($_POST['type']) {
            case 'technical':
                $dirName = C('UPLOAD_DIR_ASSETS_TECH_PIC_NAME');
                break;
            case 'quali':
                $dirName = C('UPLOAD_DIR_ASSETS_QUALI_PIC_NAME');
                break;
            case 'archives':
                $dirName = C('UPLOAD_DIR_ASSETS_ARCHIVES_PIC_NAME');
                break;
            default:
                $dirName = 'uploads';
                break;
        }
        //上传文件
        $upload = $Tool->upFile($type, $dirName);
        if ($upload['status'] == C('YES_STATUS')) {
            // 上传成功 获取上传文件信息
            //pic地址保存到数据库
            $oldFileName = $_FILES['file']['name'];
            $start = strripos($oldFileName, ".");
            if (I('post.assid') != '') {
                $data['assid'] = I('post.assid');
            } else {
                $data['assid'] = 0;
            }
            $data['file_type'] = substr($oldFileName, $start + 1);
            $data['file_name'] = $oldFileName;
            $data['file_url'] = $upload['src'];
            $data['add_user'] = session('username');
            $data['add_time'] = getHandleDate(time());
            $res = 0;
            switch ($_POST['type']) {
                case 'technical':
                    $res = $this->insertData('assets_technical_file', $data);
                    break;
                case 'quali':
                    $res = $this->insertData('assets_factory_qualification_file', $data);
                    break;
                case 'archives':
                    if (I('post.identifier')) {
                        $data['identifier'] = I('post.identifier');
                    }
                    \Think\Log::write('------------------------------assets_archives_file--------------------------------');
                    $res = $this->insertData('assets_archives_file', $data);
                    break;
            }
            if ($res) {
                $supplement = 'data-path="' . $data['file_url'] . '" data-name="' . $data['file_name'] . '.' . $data['file_type'] . '"';
                $html = '<div class="layui-btn-group">';
                $html .= $this->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' showFile', '', $supplement);
                $html .= $this->returnListLink('下载', '', '', C('BTN_CURRENCY') . ' layui-btn-normal downFile',
                    '', $supplement);
                $html .= $this->returnListLink('删除', C('ADMIN_NAME') . '/Lookup/addAssets.html', '',
                    C('BTN_CURRENCY') . ' layui-btn-danger delFile', '',
                    'data-id="' . $res . '" data-type="' . $_POST['type'] . '"');
                $html .= '</div>';
                if ($_POST['type'] == 'technical') {
                    return [
                        'status' => 1,
                        'msg' => '上传文件成功！',
                        'path' => $upload['src'],
                        'html' => $html,
                        'tech_id' => $res,
                    ];
                } else {
                    return [
                        'status' => 1,
                        'msg' => '上传文件成功！',
                        'path' => $upload['src'],
                        'html' => $html,
                        'id' => $res,
                    ];
                }
            } else {
                return ['status' => -1, 'msg' => '上传文件失败！', 'path' => $upload['src']];
            }
        } else {
            // 上传错误提示错误信息
            return ['status' => -1, 'msg' => '上传文件失败！'];
        }
    }

    /**
     * Notes: 单独上传非计划文件
     *
     * @return array
     */
    public function uploadAssetsUnplanFile()
    {
        $Tool = new ToolController();
        //设置文件类型
        $type = ['jpg', 'jpeg', 'png', 'pdf', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx'];
        //文件名目录设置
        $dirName = C('UPLOAD_DIR_ASSETS_ARCHIVES_PIC_NAME');
        //上传文件
        $upload = $Tool->upFile($type, $dirName);
        if ($upload['status'] == C('YES_STATUS')) {
            // 上传成功 获取上传文件信息
            //pic地址保存到数据库
            $oldFileName = $_FILES['file']['name'];
            $start = strripos($oldFileName, ".");
            $data['assid'] = I('post.assid');
            $data['unplan_class'] = I('post.unplanType');
            $data['file_type'] = substr($oldFileName, $start + 1);
            $data['file_name'] = $oldFileName;
            $data['file_url'] = $upload['src'];
            $data['add_user'] = session('username');
            $data['add_time'] = getHandleDate(time());
            if (I('post.identifier')) {
                $data['identifier'] = I('post.identifier');
            }
            $res = $this->insertData('assets_archives_file', $data);
            if ($res) {
                return ['status' => 1, 'msg' => '上传文件成功！'];
            } else {
                return ['status' => -1, 'msg' => '上传文件失败！'];
            }
        } else {
            return ['status' => -1, 'msg' => '上传文件失败！'];
        }
    }

    public function bindSameAssetFileData()
    {
        $insertId = '';
        $techId = I('post.tech_id');
        $bindAssetsAssid = I('post.bindAssetsAssid');
        if ($techId && $bindAssetsAssid) {
            $addData = $this->DB_get_one('assets_technical_file', 'file_name,file_url,file_type,add_user,add_time',
                ['tech_id' => $techId]);
            if ($addData['file_name']) {
                for ($i = 0; $i < count($bindAssetsAssid); $i++) {
                    $addData['assid'] = $bindAssetsAssid[$i];
                    $insertId = $this->insertData('assets_technical_file', $addData);
                }
            }
        }
        if ($insertId) {
            return ['status' => 1];
        } else {
            return ['status' => -1];
        }
    }

    /**
     * Notes: 上传设备图片
     *
     * @return array
     */
//    public function uploadAssetsPic()
//    {
//        //上传图片到服务器并保存图片地址到数据库
//        //接收base64图片编码并转化为图片保存
//        $base64Data = I('POST.base64Data');
//        //匹配出图片的格式
//        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Data, $result)) {
//            //图片格式
//            $type = $result[2];
//            if (!in_array($type, array('png', 'jpeg'))) {
//                return array('status' => -1, 'msg' => '图片格式错误！');
//            }
//            $filePath = "./Public/uploads/assets/";
//            if (!file_exists($filePath)) {
//                //检查是否有该文件夹，如果没有就创建，并给予最高权限
//                mkdir($filePath, 0777, true);
//            }
//            $fileName = date('YmdHis') . rand(1000, 9999) . '.' . $type;
//            $file = $filePath . $fileName;
//            if (!file_put_contents($file, base64_decode(str_replace($result[1], '', $base64Data)))) {
//                return array('status' => -1, 'msg' => '图片保存出错，请重试！');
//            }
//            $image = new \Think\Image();
//            $image->open($file);
//            //更新设备图片地址为最新地址
//            $file = trim($file, '.');
//            $this->updateData('assets_info', array('pic_url' => $file), array('assid' => I('POST.assid')));
//            return array('status' => 1, 'msg' => '上传设备图片成功！', 'pic_url' => $file);
//        } else {
//            return array('status' => -1, 'msg' => '上传设备图片失败！');
//        }
//    }

    /**
     * Notes: 获取设备计量记录
     *
     * @return array
     */
    public function getMeteringRecord()
    {
        $assid = I('post.assid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        if (!$sort) {
            $sort = 'B.mrid';
        }
        if (!$order) {
            $order = 'asc';
        }
        $fields = "A.mpid,A.assid,A.plan_num,A.mcid,A.cycle,A.test_way,A.respo_user,B.mrid,B.status,B.company,B.money,B.test_person,B.this_date,B.result,D.mcategory";
        $join = "LEFT JOIN sb_metering_result as B on A.mpid = B.mpid LEFT JOIN sb_metering_categorys as D ON A.mcid = D.mcid";
        $where['A.assid'] = $assid;
        $total = $this->DB_get_count_join('metering_plan', 'A', $join, $where);
        $records = $this->DB_get_all_join('metering_plan', 'A', $fields, $join, $where, 'B.mrid',
            $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($records as $k => $v) {
            $file = $this->DB_get_all('metering_result_reports', 'name,url', ['mrid' => $v['mrid']]);
            $file_data = '';
            foreach ($file as &$fileValue) {
                $one['file_url'] = $fileValue['url'];
                $suffix = substr(strrchr($fileValue['name'], '.'), 1);
                $supplement = 'data-path="' . $fileValue['url'] . '" data-name="' . $fileValue['name'] . '"';
                $string = '';
                if ($suffix == 'doc' or $suffix == 'docx') {
                    $string .= ' data-showFile=false';
                } else {
                    $string .= ' data-showFile=true';
                }
                $file_data .= $this->returnListLink($fileValue['name'], '', '',
                    C('BTN_CURRENCY') . ' layui-btn-warm operationFile', '', $supplement . $string);
            }
            $records[$k]['operation'] = trim($file_data, ',');
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $records;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
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
        $where['assid'] = ['EQ', $assid];
        $files = 'assid,catid,assnum,assets,helpcatid,status,brand,model,unit,serialnum,assetsrespon,departid,address,buy_price';
        $assets = $this->DB_get_one('assets_info', $files, $where);
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assets['buy_price'] = '***';
        }
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


    //获取设备字典详情
    public function getDicAssetsDetail()
    {
        $assets = I('post.assets');
        if (!$assets) {
            die(json_encode(['status' => -999, 'msg' => '非法操作']));
        }
        $data = $this->DB_get_one('dic_assets', 'assets,catid,assets_category,unit',
            ['assets' => $assets, 'hospital_id' => session('current_hospitalid')]);
        if ($data) {
            return ['status' => 1, 'msg' => '获取成功！', 'result' => $data];
        } else {
            die(json_encode(['status' => -999, 'msg' => '参数丢失']));
        }
    }

    //获取医院对应的设备字典
    public function getDicAssets()
    {
        $data = $this->DB_get_all('dic_assets', 'dic_assid,assets', ['hospital_id' => session('current_hospitalid')]);
        if ($data) {
            return ['status' => 1, 'result' => $data];
        } else {
            die(json_encode(['status' => -999, 'msg' => '该医院未配置设备名称字典，请先配置！']));
        }
    }

    //获取医院对应的科室列数据
    public function getDepartmentList()
    {
        $hospital_id = I('post.hospital_id');
        if (!$hospital_id) {
            die(json_encode(['status' => -999, 'msg' => '非法操作']));
        }
        $data = $this->DB_get_all('department', 'departid,department', ['hospital_id' => $hospital_id]);
        if ($data) {
            return ['status' => 1, 'result' => $data];
        } else {
            die(json_encode(['status' => -999, 'msg' => '该医院未配置科室请先配置！']));
        }
    }

    //判断数据是否合理
    public function getjudgement()
    {
        $field = I('post.field');
        $value = I('post.value');
        if ($field == "assorignum" || $field == "assorignum_spare") {
            # code...
            if ($value) {
                if (I('post.assid')) {
                    $map['assorignum_spare'] = $value;
                    $map['assorignum'] = $value;
                    $map['_logic'] = 'OR';
                    $asswhere['_complex'] = $map;
                    $asswhere['assid'] = ['neq', I('post.assid')];
                } else {
                    $map['assorignum_spare'] = $value;
                    $map['assorignum'] = $value;
                    $map['_logic'] = 'OR';
                    $asswhere['_complex'] = $map;
                }
                //判断原编码是否已存在
                if ($value != '/') {
                    $asswhere['is_delete'] = '0';
                    $assorignum = $this->DB_get_one('assets_info', 'assid', $asswhere);
                    if ($assorignum) {
                        return ['status' => -1, 'msg' => '原编码或原编码备用已存在！'];
                    }
                }
            }
        }
        return ['status' => 1];
    }

    //获取科室对应的信息
    public function getdepartDetail()
    {
        $departid = I('post.departid');
        if (!$departid) {
            die(json_encode(['status' => -999, 'msg' => '非法操作']));
        }
        $data = $this->DB_get_one('department', 'departid,department,address,assetsrespon', ['departid' => $departid]);
        if ($data) {
            return ['status' => 1, 'msg' => '获取成功！', 'result' => $data];
        } else {
            die(json_encode(['status' => -999, 'msg' => '参数丢失']));
        }
    }

    /**设备详情页面 上传设备图片 最新
     *
     * @return array
     */
    public function uploadAssetsPic($assid, $url)
    {
        $oldUrl = $this->DB_get_one('assets_info', 'pic_url', ['assid' => $assid]);
        if ($oldUrl['pic_url']) {
            $newUrl = $oldUrl['pic_url'] . ',' . $url;
            $result = $this->updateData('assets_info', ['pic_url' => $newUrl], ['assid' => $assid]);
        } else {
            $result = $this->updateData('assets_info', ['pic_url' => $url], ['assid' => $assid]);
        }
        return $result;
    }

    /** 获取生产 供应 维修商
     *
     * @param $type string 获取的类型
     */
    public function getSuppliers($type = '')
    {
        $fields = 'olsid,sup_name,salesman_name,salesman_phone';
        switch ($type) {
            case 'factory':
                $result = $this->DB_get_all('offline_suppliers', $fields,
                    ['is_manufacturer' => 1, 'is_delete' => C('NO_STATUS')]);
                break;
            case 'supplier':
                $result = $this->DB_get_all('offline_suppliers', $fields,
                    ['is_supplier' => 1, 'is_delete' => C('NO_STATUS')]);
                break;
            case 'repair':
                $result = $this->DB_get_all('offline_suppliers', $fields,
                    ['is_repair' => 1, 'is_delete' => C('NO_STATUS')]);
                break;
            default:
                $defaultFields = 'is_manufacturer,is_supplier,is_repair,olsid,sup_name,salesman_name,salesman_phone';
                $result = $this->DB_get_all('offline_suppliers', $defaultFields,
                    ['is_delete' => C('NO_STATUS')]);
                break;

        }
        return $result;
    }

    //生成附属设备编号
    public function getSubsidiaryAssetsNum($main_assnum, $count)
    {
        $assnum = 'AE' . $main_assnum . '-' . $count;
        $check = $this->DB_get_one('assets_info', 'assid', ['assnum' => ['EQ', $assnum]]);
        if ($check) {
            return $this->getSubsidiaryAssetsNum($main_assnum, $count + 1);
        } else {
            return $assnum;
        }
    }

    public function getPrintAssets()
    {
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $assets = I('post.assets');
        $assetsNum = I('post.assnum');
        $assetsOrnum = I('post.assorignum');
        $assetsCat = I('post.category');
        $assetsDep = I('post.department');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['is_delete'] = 0;
        $where['departid'] = ['in', $departids];
        $where['hospital_id'] = $hospital_id;
        $where['status'] = ['neq', C('ASSETS_STATUS_SCRAP')];

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
        if ($assets) {
            //设备名称搜索
            $where['assets'] = ['like', '%' . $assets . '%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['assnum'] = ['like', '%' . $assetsNum . '%'];
        }
        if ($assetsOrnum) {
            //资产原编码搜索
            $where['assorignum'] = ['like', '%' . $assetsOrnum . '%'];
        }

        if ($assetsCat) {
            //分类搜索
            $catwhere['category'] = ['like', '%' . $assetsCat . '%'];
            $res = $this->DB_get_all('category', 'catid', $catwhere, '', 'catid asc', '');
            if ($res) {
                $catids = '';
                foreach ($res as $k => $v) {
                    $catids .= $v['catid'] . ',';
                }
                $catids = trim($catids, ',');
                $where['catid'] = ['in', $catids];
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        }
        if ($assetsDep) {
            //部门搜索
            $where['departid'] = ['IN', $assetsDep];
        }
        $total = $this->DB_get_count('assets_info', $where);
        $fields = "assid,assnum,assets,catid,departid,opendate,serialnum,assorignum,model,factorynum,code_url,storage_date,assetsrespon,remark";
        $asinfo = $this->DB_get_all('assets_info', $fields, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asinfo) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($asinfo as &$one) {
            $one['storage_date'] = HandleEmptyNull($one['storage_date']);
            $one['opendate'] = HandleEmptyNull($one['opendate']);
            $one['department'] = $departname[$one['departid']]['department'];
            $one['category'] = $catname[$one['catid']]['category'];
            //判断二维码图片
            if ($one['code_url']) {
                $fileExists = file_exists('.' . $one['code_url']);
                if (!$fileExists) {
                    //文件已不存在
                    $one['print_status'] = '未打印';
                } else {
                    $one['print_status'] = '已打印';
                }
            } else {
                $one['print_status'] = '未打印';
            }
        }
        $result['total'] = (int)$total;
        $result["offset"] = $offset;
        $result["limit"] = (int)$limit;
        $result["code"] = 200;
        $result['rows'] = $asinfo;
        return $result;
    }

    public function getverify()
    {
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $assets = I('post.assets');
        $assetsNum = I('post.assnum');
        $assetsDep = I('post.department');
        $print_status = I('post.print_status');
        $code_status = I('post.code_status');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['is_delete'] = 0;
        $where['departid'] = ['in', $departids];
        $where['hospital_id'] = $hospital_id;
        $where['status'] = ['neq', C('ASSETS_STATUS_SCRAP')];

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
        if ($assets) {
            //设备名称搜索
            $where['assets'] = ['like', '%' . $assets . '%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['assnum'] = ['like', '%' . $assetsNum . '%'];
        }
        if ($assetsDep) {
            //部门搜索
            $where['departid'] = ['IN', $assetsDep];
        }
        if (strlen($print_status)) {
            //部门搜索
            $where['print_status'] = ['eq', $print_status];
        }
        switch ($code_status) {
            //是否打印搜索
            case '1':
                $where['code_url'] = ['EXP', 'IS NULL'];
                break;
            case '2':
                $where['code_url'] = ['EXP', 'IS NOT NULL'];
                break;
            default:
                # code...
                break;
        }
        $total = $this->DB_get_count('assets_info', $where);
        $fields = "assid,assnum,assets,departid,opendate,serialnum,model,factorynum,code_url,storage_date,print_status,assorignum";
        $asinfo = $this->DB_get_all('assets_info', $fields, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asinfo) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($asinfo as &$one) {
            $one['storage_date'] = HandleEmptyNull($one['storage_date']);
            $one['opendate'] = HandleEmptyNull($one['opendate']);
            $one['department'] = $departname[$one['departid']]['department'];
            //判断二维码图片
            if ($one['code_url']) {
                $fileExists = file_exists('.' . $one['code_url']);
                if (!$fileExists) {
                    //文件已不存在
                    $one['code_status'] = '未打印';
                } else {
                    $one['code_status'] = '已打印';
                }
            } else {
                $one['code_status'] = '未打印';
            }
            if ($one['print_status'] == 1 || $one['print_status'] == 2) {
                $one['operation'] = $this->returnListLink('查看', '', 'showFile',
                    C('BTN_CURRENCY') . ' layui-btn-normal ', '', 'data-id=' . $one['assid']);
            } else {
                $one['operation'] = $this->returnListLink('查看', '', '',
                    C('BTN_CURRENCY') . ' layui-btn-normal layui-btn-disabled', 'cursor: not-allowed');
            }
            switch ($one['print_status']) {
                case '0':
                    $one['print_status'] = '初始状态';
                    break;
                case '1':
                    $one['print_status'] = '已核实';
                    break;
                case '2':
                    $one['print_status'] = '已核实(无法贴标)';
                    break;
                default:
                    $one['print_status'] = '初始状态';
                    break;
            }
        }
        $result['total'] = (int)$total;
        $result["offset"] = $offset;
        $result["limit"] = (int)$limit;
        $result["code"] = 200;
        $result['rows'] = $asinfo;
        return $result;
    }

    //获取设备的标签图片
    public function getprintFile()
    {
        $assid = I('get.assid');
        $data = $this->DB_get_one('assets_info', 'pic_url', ['assid' => $assid]);
        $files = explode(",", $data['pic_url']);
        return $files;
    }

    public function getPrintLabelData($assid)
    {
        return $this->DB_get_all('assets_info',
            'assid,assnum,assets,catid,departid,opendate,serialnum,assorignum,model,factorynum,code_url,storage_date,remark,assetsrespon',
            ['assid' => ['in', $assid]]);
    }

    //删除主设备信息
    public function deleteAssets($assid)
    {
        $assets_data = $this->DB_get_one('assets_info', 'hospital_id,assets,assnum', ['assid' => $assid]);
        $hospital_id = $assets_data['hospital_id'];
        $desc = '申请删除主设备' . $assets_data['assets'] . '(' . $assets_data['assnum'] . ')';
        $update_where_arr = ['assid' => $assid];
        $update_where = json_encode($update_where_arr);
        $delete_data = [
            'operation_type' => 'delete',
            'table' => 'assets_info',
            'hospital_id' => $hospital_id,
            'desc' => $desc,
            'update_where' => $update_where,
            'applicant_user' => session('username'),
            'applicant_time' => date('Y-m-d H:i:s', time()),
        ];
        $deleteassets_data = $this->DB_get_one('edit', 'id',
            ['update_where' => $update_where, 'operation_type' => 'delete', 'is_approval' => '0']);
        if ($deleteassets_data) {
            $result = $this->updateData('edit', $delete_data, ['id' => $deleteassets_data['id']]);
            if ($result) {
                return 1;
            } else {
                return 0;
            }
        }
        $result = $this->insertData('edit', $delete_data);
        $sql = M()->getLastSql();
        //日志行为记录文字
        $log['str'] = $desc;
        $text = getLogText('adddeleteAssetsLogText', $log);
        $this->addLog('edit', $sql, $text, $result, 'deleteAssets');
        if ($result) {
            return 1;
        } else {
            return 0;
        }
    }

    //存储科室修改相关信息
    public function editdepartment($assid = null)
    {
        $assid = $assid ? $assid : I('POST.assid');
        $departid = I('POST.departid');
        $managedepart = I('POST.managedepart');
        $address = I('POST.address');
        $assetsrespon = I('POST.assetsrespon');
        //获取设备信息判断用户是否修改了这四个信息
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assets_data = $this->DB_get_one('assets_info',
            'assid,assnum,assets,departid,managedepart,address,assetsrespon,hospital_id', ['assid' => $assid]);
        if ($departid != $assets_data['departid']) {
            $hospital_id = $assets_data['hospital_id'];
            $desc = $assets_data['assets'] . '(' . $assets_data['assnum'] . ')申请修改科室从' . $departname[$assets_data['departid']]['department'] . '修改为' . $departname[$departid]['department'] . ' ';
            $old_data = json_encode($assets_data, JSON_UNESCAPED_UNICODE);
            $update_data_arr = [
                'departid' => $departid,
                'managedepart' => $managedepart,
                'address' => $address,
                'assetsrespon' => $assetsrespon,
            ];
            $update_data = json_encode($update_data_arr, JSON_UNESCAPED_UNICODE);
            $update_where_arr = ['assid' => $assid];
            $update_where = json_encode($update_where_arr);
            $edit_data = [
                'operation_type' => 'edit',
                'hospital_id' => $hospital_id,
                'table' => 'assets_info',
                'desc' => $desc,
                'old_data' => $old_data,
                'update_data' => $update_data,
                'update_where' => $update_where,
                'applicant_user' => session('username'),
                'applicant_time' => date('Y-m-d H:i:s', time()),
            ];
            $editassets_data = $this->DB_get_one('edit', 'id',
                ['update_where' => $update_where, 'operation_type' => 'edit', 'is_approval' => '0']);
            if ($editassets_data) {
                $result = $this->updateData('edit', $edit_data, ['id' => $editassets_data['id']]);
                if ($result) {
                    return 1;
                } else {
                    return 0;
                }
            }
            $result = $this->insertData('edit', $edit_data);
            if ($result) {
                return 1;
            } else {
                return 0;
            }
        }
        return 1;//1代表一切正常，当相关没有被修改时，直接返回1
    }

    /**医疗器械类别
     *
     * @return array
     */
    public function getAssetsLevel()
    {
        $result = [
            ['value' => 1, 'name' => 'Ⅰ类'],
            ['value' => 2, 'name' => 'Ⅱ类'],
            ['value' => 3, 'name' => 'Ⅲ类'],
        ];
        return $result;
    }


    public function getBaseSettingAssets($keyname)
    {
        $baseSetting = [];
        $result = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        foreach ($baseSetting['assets'] as $k => $v) {
            if ($k == $keyname) {
                $result = $v['value'];
            }
        }
        return $result;
    }

    /**
     * @return mixed 设备详情技术资料页面获取同一批文档设备
     */
    public function getSameAssetsListData()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $assets = I('post.assets');
        $model = I('post.model');
        $brand = I('post.brand');
        $assid = I('post.assid');
        if ($assets || $model || $brand) {
            $assid = '';
        }

        $where = [];
        if ($assid != '') {
            $thisAssetsInfo = $this->DB_get_one('assets_info', 'assets,brand,model', ['assid' => $assid]);
            $where = [
                'assets' => $thisAssetsInfo['assets'],
                'brand' => $thisAssetsInfo['brand'],
                'model' => $thisAssetsInfo['model'],
                'assid' => ['neq', $assid],
            ];
        } else {
            if ($assets) {
                $where['assets'] = ['like', '%' . $assets . '%'];
            }
            if ($model) {
                $where['model'] = ['like', '%' . $model . '%'];
            }
            if ($brand) {
                $where['brand'] = ['like', '%' . $brand . '%'];
            }
            $where['assid'] = ['neq', I('post.assid')];
        }
        $total = $this->DB_get_count('assets_info', $where);
        $sameAssetsInfo = $this->DB_get_all('assets_info', 'assid,assets,brand,model', $where, '', '',
            $offset . ',' . $limit);
        foreach ($sameAssetsInfo as $k => $v) {
            $html = $this->returnButtonLink('绑定', get_url(), 'layui-btn layui-btn-xs', '',
                'lay-event = bind type="button"');
            $sameAssetsInfo[$k]['operation'] = $html;
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $sameAssetsInfo;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * 删除设备照片
     */
    public function deleteAssetsPic()
    {
        $assid = I('post.assid');
        $src = I('post.src');
        if ($assid) {
            $asInfo = $this->DB_get_one('assets_info', 'pic_url', ['assid' => $assid]);
            $thisPicUrl = explode(',', $asInfo['pic_url']);
            $newSrc = [];
            foreach ($thisPicUrl as $k => $v) {
                if ('/Public/uploads/assets/' . $v != $src) {
                    $newSrc[] = $v;
                }
            }
            $newSrc = implode(',', $newSrc);
            $update['pic_url'] = $newSrc;
            $result = $this->updateData('assets_info', $update, ['assid' => $assid]);
            if ($result) {
                return ['status' => 1, 'msg' => '删除成功'];
            } else {
                return ['status' => -1, 'msg' => '删除失败'];
            }
        } else {
            return ['status' => -1, 'msg' => '非法操作'];
        }
    }

    /**
     * Notes: 获取待审批
     *
     * @return array
     */
    public function get_user_approves($hospital_id)
    {
        //查询超级管理员账号名称
        $super = $this->DB_get_one('user', 'username', ['is_super' => 1, 'is_delete' => 0]);
        $super_name = $super['username'];
        //外调待审批数量
        $outside_where['A.approve_status'] = 0;
        $outside_where['B.hospital_id'] = $hospital_id;
        $outside_where['B.is_delete'] = 0;
        $outside_fields = "A.outid,A.current_approver";
        $join = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $outside_data = $this->DB_get_all_join('assets_outside', 'A', $outside_fields, $join,
            $outside_where);
        $outside_approve = [];
        foreach ($outside_data as $k => $v) {
            $tmp_name = str_replace(',' . $super_name, '', $v['current_approver']);
            if (!isset($outside_approve[$tmp_name])) {
                $outside_approve[$tmp_name] = 1;
            } else {
                $outside_approve[$tmp_name] += 1;
            }
        }
        //报废待审批数量
        $scrap_where['A.approve_status'] = 0;
        $scrap_where['B.hospital_id'] = $hospital_id;
        $scrap_where['B.is_delete'] = 0;
        $scrap_fields = "A.scrid,A.current_approver";
        $join = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $scrap_data = $this->DB_get_all_join('assets_scrap', 'A', $scrap_fields, $join,
            $scrap_where);
        $scrap_approve = [];
        foreach ($scrap_data as $k => $v) {
            $tmp_name = str_replace(',' . $super_name, '', $v['current_approver']);
            if (!isset($scrap_approve[$tmp_name])) {
                $scrap_approve[$tmp_name] = 1;
            } else {
                $scrap_approve[$tmp_name] += 1;
            }
        }
        //转科待审批数量
        $trans_where['A.approve_status'] = 0;
        $trans_where['B.hospital_id'] = $hospital_id;
        $trans_where['B.is_delete'] = 0;
        $trans_fields = "A.atid,A.current_approver";
        $join = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $trans_data = $this->DB_get_all_join('assets_transfer', 'A', $trans_fields, $join,
            $trans_where);
        $trans_approve = [];
        foreach ($trans_data as $k => $v) {
            $tmp_name = str_replace(',' . $super_name, '', $v['current_approver']);
            if (!isset($trans_approve[$tmp_name])) {
                $trans_approve[$tmp_name] = 1;
            } else {
                $trans_approve[$tmp_name] += 1;
            }
        }
        //巡查待审批数量
        $patrol_where['approve_status'] = 0;
        $patrol_where['hospital_id'] = $hospital_id;
        $patrol_where['is_delete'] = 0;
        $patrol_data = $this->DB_get_all('patrol_plan', 'patrid,current_approver', $patrol_where);
        $patrol_approve = [];
        foreach ($patrol_data as $k => $v) {
            $tmp_name = str_replace(',' . $super_name, '', $v['current_approver']);
            if (!isset($patrol_approve[$tmp_name])) {
                $patrol_approve[$tmp_name] = 1;
            } else {
                $patrol_approve[$tmp_name] += 1;
            }
        }
        //采购计划待审批数量
        $purch_where['approve_status'] = 0;
        $purch_where['hospital_id'] = $hospital_id;
        $purch_where['is_delete'] = 0;
        $purch_data = $this->DB_get_all('purchases_plans', 'plans_id,current_approver',
            $purch_where);
        $purch_approve = [];
        foreach ($purch_data as $k => $v) {
            $tmp_name = str_replace(',' . $super_name, '', $v['current_approver']);
            if (!isset($purch_approve[$tmp_name])) {
                $purch_approve[$tmp_name] = 1;
            } else {
                $purch_approve[$tmp_name] += 1;
            }
        }
        //维修待审批数量
        $repair_where['A.approve_status'] = 0;
        $repair_where['A.status'] = C('REPAIR_AUDIT');
        $repair_where['B.hospital_id'] = $hospital_id;
        $repair_where['B.is_delete'] = 0;
        $repair_fields = "A.repid,A.current_approver";
        $join = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $repair_data = $this->DB_get_all_join('repair', 'A', $repair_fields, $join, $repair_where);
        $repair_approve = [];
        foreach ($repair_data as $k => $v) {
            $tmp_name = str_replace(',' . $super_name, '', $v['current_approver']);
            if (!isset($repair_approve[$tmp_name])) {
                $repair_approve[$tmp_name] = 1;
            } else {
                $repair_approve[$tmp_name] += 1;
            }
        }
        //组织数据
        $user = [];
        foreach ($outside_approve as $k => $v) {
            $user[$k]['outside_approve_nums'] = $v;
        }
        foreach ($scrap_approve as $k => $v) {
            $user[$k]['scrap_approve_nums'] = $v;
        }
        foreach ($trans_approve as $k => $v) {
            $user[$k]['transfer_approve_nums'] = $v;
        }
        foreach ($patrol_approve as $k => $v) {
            $user[$k]['patrol_approve_nums'] = $v;
        }
        foreach ($purch_approve as $k => $v) {
            $user[$k]['purch_approve_nums'] = $v;
        }
        foreach ($repair_approve as $k => $v) {
            $user[$k]['repair_approve_nums'] = $v;
        }
        return $user;
    }

    /**
     * Notes: 获取待验收
     *
     * @return array
     */
    public function get_user_check($user, $hospital_id)
    {
        //查询超级管理员账号名称
        $super = $this->DB_get_one('user', 'username', ['is_super' => 1, 'is_delete' => 0]);
        $super_name = $super['username'];
        //维修待验收数量
        $repair_where['A.status'] = C('REPAIR_MAINTENANCE_COMPLETION');
        $repair_where['B.hospital_id'] = $hospital_id;
        $repair_where['B.is_delete'] = 0;
        $repair_fields = "A.repid,A.applicant";
        $join = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $repair_data = $this->DB_get_all_join('repair', 'A', $repair_fields, $join, $repair_where);
        $repair_check = [];
        foreach ($repair_data as $k => $v) {
            if (!isset($repair_check[$v['applicant']])) {
                $repair_check[$v['applicant']] = 1;
            } else {
                $repair_check[$v['applicant']] += 1;
            }
        }
        //组织数据
        foreach ($repair_check as $k => $v) {
            $user[$k]['repair_check_nums'] = $v;
        }
        return $user;
    }

    public function get_use_num()
    {
        $where = ['status' => 0, 'is_delete' => 0];
        $total = $this->DB_get_count('assets_info', $where);
        return $total;

    }
}
