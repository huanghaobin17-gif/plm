<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/3/27
 * Time: 16:19
 */

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Think\Model;
use Think\Model\RelationModel;

class PurchaseCheckModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'purchases_contract';
    private $MODULE = 'Purchases';
    private $Controller = 'PurchaseCheck';

    //合同列表
    public function checkAssetsLists()
    {
        $supplier_name = I('POST.supplier_name');
        $assets_name = I('POST.assets_name');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'record_id';
        if (!session('departid')) {
            $result['msg'] = '暂无科室信息';
            $result['code'] = 400;
            return $result;
        }
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $where['contract_id'] = ['GT', 0];
        $where['hospital_id'] = session('current_hospitalid');;
        if ($assets_name) {
            $where['assets_name'] = ['LIKE', "%$assets_name%"];
        }
        if ($supplier_name) {
            $where['supplier'] = ['LIKE', "%$supplier_name%"];
        }
        $fileds = '*';
        $total = $this->DB_get_count('purchases_depart_apply_assets', $where);
        $data = $this->DB_get_all('purchases_depart_apply_assets', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $checkMenu = get_menu($this->MODULE, $this->Controller, 'checkAssets');
        foreach ($data as &$value) {
            $value['real_total_price'] = round($value['buy_price']*$value['nums'],2);
            $value['guarantee_date'] = HandleEmptyNull($value['guarantee_date']);
            $detailsUrl = get_url() . '?action=showCheckAssetsDetails&assets_id=' . $value['assets_id'];
            if ($value['is_check'] == C('YES_STATUS')) {
                $value['operation'] .= $this->returnListLink('验收详情', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-primary');
            } else {
                if ($checkMenu) {
                    $value['operation'] .= $this->returnListLink('验收设备', $checkMenu['actionurl'], 'checkAssets', C('BTN_CURRENCY'));;
                } else {
                    $value['operation'] .= $this->returnListLink('待验收', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
                }
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //验收设备
    public function checkAssets()
    {
        $assets_id = trim(I('POST.assets_id'));
        $where['assets_id'] = ['EQ', $assets_id];
        $where['is_check'] = ['EQ', C('YES_STATUS')];
        $is_check = $this->DB_get_one('purchases_depart_apply_assets', 'assets_id', $where);
        if ($is_check) {
            die(json_encode(array('status' => -1, 'msg' => '已验收请勿重复验收')));
        }
        //验证信息
        $result = $this->checkAssetsData();
        if ($result['status'] != C('YES_STATUS')) {
            die(json_encode(array('status' => -1, 'msg' => $result['msg'])));
        }
        $data = $result['data'];
        $data['is_check'] = C('YES_STATUS');
        $data['check_time'] = date('Y-m-d H:i:s');
        $save = $this->updateData('purchases_depart_apply_assets', $data, array('assets_id' => $assets_id));
        if ($save) {
            $log['assets_name'] = $data['assets_name'];
            $log['unit'] = $data['unit'];
            $log['nums'] = I('POST.nums');
            $text = getLogText('purchaseCheckAssets', $log);
            $this->addLog('user', M()->getLastSql(), $text, $assets_id);
            return array('status' => 1, 'msg' => '验收成功');
        } else {
            return array('status' => -1, 'msg' => '验收失败');
        }
    }


    //移除验收附件
    public function deleteFile()
    {
        $file_id = I('POST.file_id');
        if ($file_id) {
            $where['file_id'] = $file_id;
            $data = $this->DB_get_one('purchases_depart_apply_checkassets_file', 'is_delete', $where);
            if ($data) {
                if ($data['is_delete'] != C('YES_STATUS')) {
                    $data['is_delete'] = C('YES_STATUS');
                    $save = $this->updateData('purchases_depart_apply_checkassets_file', $data, $where);
                    if ($save) {
                        return array('status' => 1, 'msg' => '移除成功');
                    } else {
                        return array('status' => -1, 'msg' => '移除失败');
                    }
                } else {
                    die(json_encode(array('status' => -1, 'msg' => '数据已删除,请勿重复操作')));
                }
            } else {
                die(json_encode(array('status' => -1, 'msg' => '数据不存在,请刷新页面重新操作')));
            }
        } else {
            die(json_encode(array('status' => -1, 'msg' => '参数缺少,请按正常流程操作')));
        }
    }

    /**
     * 验证验收信息
     * */
    public function checkAssetsData()
    {
        $this->checkstatus(judgeEmpty(trim(I('POST.assets_id'))), '非法操作');

        $this->checkstatus(judgeEmpty(trim(I('POST.catid'))), '请选择设备分类');
        $data['catid'] = trim(I('POST.catid'));

        $data['assfromid'] = trim(I('POST.assfromid'));
        $this->checkstatus(is_numeric(trim(I('POST.assfromid'))), '请选择设备来源');

        $this->checkstatus(judgeNum(trim(I('POST.expected_life'))), '请补充预计使用年限');
        $data['expected_life'] = trim(I('POST.expected_life'));

        $this->checkstatus(judgeNum(trim(I('POST.depreciable_lives'))), '请补充折旧年限');
        $data['depreciable_lives'] = trim(I('POST.depreciable_lives'));

        $this->checkstatus(judgeEmpty(trim(I('POST.assetsrespon'))), '请补充设备负责人');
        $data['assetsrespon'] = trim(I('POST.assetsrespon'));


        $this->checkstatus(judgeEmpty(trim(I('POST.serialnum'))), '请补充产品序列号');
        $nums = I('POST.nums');
        $serialnum = explode(';', I('POST.serialnum'));
        if (count($serialnum) != $nums) {
            die(json_encode(array('status' => -1, 'msg' => '请补充已验收的合同上' . $nums . '台设备的产品序列号')));
        }
        foreach ($serialnum as &$serV) {
            if (!$serV) {
                die(json_encode(array('status' => -1, 'msg' => '序列号不能为空')));
            }
        }
        $data['serialnum'] = trim(I('POST.serialnum'));

        $this->checkstatus(judgeEmpty(trim(I('POST.model'))), '请补充设备 规格/型号');
        $data['model'] = trim(I('POST.model'));


        $this->checkstatus(judgeEmpty(trim(I('POST.factorydate'))), '请补充出厂日期');
        $data['factorydate'] = trim(I('POST.factorydate'));

        $this->checkstatus(judgeEmpty(trim(I('POST.opendate'))), '请补充启用日期');
        $data['opendate'] = trim(I('POST.opendate'));

        $this->checkstatus(judgeEmpty(trim(I('POST.guarantee_date'))), '保修截止日期');
        $data['guarantee_date'] = trim(I('POST.guarantee_date'));

        $this->checkstatus(judgeNum(trim(I('POST.financeid'))), '请选择财务分类');
        $data['financeid'] = trim(I('POST.financeid'));


        $this->checkstatus(judgeNum(trim(I('POST.capitalfrom'))), '请选择资金来源');
        $data['capitalfrom'] = trim(I('POST.capitalfrom'));

        $this->checkstatus(judgeNum(trim(I('POST.depreciation_method'))), '请选择折旧方式');
        $data['depreciation_method'] = trim(I('POST.depreciation_method'));

        $this->checkstatus(judgeEmpty(trim(I('POST.arrival_date'))), '请选择到货日期');
        $data['arrival_date'] = trim(I('POST.arrival_date'));

        $data['alias_name'] = trim(I('POST.alias_name'));
        $data['helpcatid'] = trim(I('POST.helpcatid'));
        $data['departid'] = trim(I('POST.departid'));
        $data['managedepart'] = trim(I('POST.managedepart'));
        $data['address'] = trim(I('POST.address'));
        $data['unit'] = trim(I('POST.unit'));
        $data['brand'] = trim(I('POST.brand'));
        $data['repair'] = trim(I('POST.repair'));
        $data['factorynum'] = trim(I('POST.factorynum'));
        $data['invoicenum'] = trim(I('POST.invoicenum'));
        $data['is_firstaid'] = trim(I('POST.is_firstaid'));
        $data['is_special'] = trim(I('POST.is_special'));
        $data['is_metering'] = trim(I('POST.is_metering'));
        $data['is_qualityAssets'] = trim(I('POST.is_qualityAssets'));
        //判断保养设备
        if (!I('post.is_patrol')) {
            $data['is_patrol'] = 0;
        } else {
            $data['is_patrol'] = I('post.is_patrol');
        }
        //付款日期
        if (I('post.paytime') != '') {
            $data['paytime'] = I('post.paytime');
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
        //医疗器械类别
        $data['assets_level'] = I('post.assets_level');
        $data['is_benefit'] = trim(I('POST.is_benefit'));
        $data['is_lifesupport'] = trim(I('POST.is_lifesupport'));
        $data['check_desc'] = trim(I('POST.remark'));
        $data['instructions'] = trim(I('POST.instructions'));
        $data['certificate'] = trim(I('POST.certificate'));
        $data['repair_card'] = trim(I('POST.repair_card'));
        $data['inspection_report'] = trim(I('POST.inspection_report'));
        $data['customs_declaration'] = trim(I('POST.customs_declaration'));
        $data['check_user'] = session('username');
        $data['check_date'] = I('post.check_date');

        $result['status'] = C('YES_STATUS');
        $result['data'] = $data;
        return $result;
    }


    /**
     * 获取采购计划设备明细详细信息
     * @param $assets_id int 采购计划设备明细id
     * @return array
     * */
    public function getCheckassetsBasic($assets_id)
    {
        $where['contract_id'] = ['GT', 0];
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $where['assets_id'] = ['EQ', $assets_id];
        $assetsinfo = $this->DB_get_one('purchases_depart_apply_assets', '', $where);
        $assetsinfo['guarantee_date'] = HandleEmptyNull($assetsinfo['guarantee_date']);
        $assetsinfo['factorydate'] = HandleEmptyNull($assetsinfo['factorydate']);
        $assetsinfo['opendate'] = HandleEmptyNull($assetsinfo['opendate']);
        switch ($assetsinfo['buy_type']) {
            case C('APPLY_ASSETS_SCRAP_UPDATE'):
                $assetsinfo['buy_type_name'] = C('APPLY_ASSETS_SCRAP_UPDATE_NAME');
                break;
            case C('APPLY_ASSETS_ADD_TO_IT'):
                $assetsinfo['buy_type_name'] = C('APPLY_ASSETS_ADD_TO_IT_NAME');
                break;
            case C('APPLY_ASSETS_NEWLY_ADDED'):
                $assetsinfo['buy_type_name'] = C('APPLY_ASSETS_NEWLY_ADDED_NAME');
                break;
            default :
                $assetsinfo['buy_type_name'] = '未知参数';
                break;
        }

        if($assetsinfo['is_check']==C('YES_STATUS')){
            $departname = array();
            $catname = array();
            $baseSetting = array();
            $depreciation_method = array('平均折旧法', '工作量法', '加速折旧法');
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $assetsinfo['arrival_date'] = HandleEmptyNull($assetsinfo['arrival_date']);
            $assetsinfo['check_date'] = HandleEmptyNull($assetsinfo['check_date']);
            $assetsinfo['department'] = $departname[$assetsinfo['departid']]['department'];
            $assetsinfo['category'] = $catname[$assetsinfo['catid']]['category'];
            $assetsinfo['helpcatid'] = $baseSetting['assets']['assets_helpcat']['value'][$assetsinfo['helpcatid']];
            $assetsinfo['financeid'] = $baseSetting['assets']['assets_finance']['value'][$assetsinfo['financeid']];
            $assetsinfo['capitalfrom'] = $baseSetting['assets']['assets_capitalfrom']['value'][$assetsinfo['capitalfrom']];
            $assetsinfo['assfromid'] = $baseSetting['assets']['assets_assfrom']['value'][$assetsinfo['assfromid']];
            $assetsinfo['depreciation_method'] = $depreciation_method[$assetsinfo['depreciation_method'] - 1];
            $assetsinfo['type']='';
            if ($assetsinfo['is_firstaid'] == C('YES_STATUS')) {
                $assetsinfo['type'] .= C('ASSETS_FIRST_CODE_YES_NAME');
            }
            if ($assetsinfo['is_special'] == C('YES_STATUS')) {
                $assetsinfo['type'] .= '、' . C('ASSETS_SPEC_CODE_YES_NAME');
            }
            if ($assetsinfo['is_metering'] == C('YES_STATUS')) {
                $assetsinfo['type'] .= '、' . C('ASSETS_METER_CODE_YES_NAME');
            }
            if ($assetsinfo['is_qualityAssets'] == C('YES_STATUS')) {
                $assetsinfo['type'] .= '、' . C('ASSETS_QUALITY_CODE_YES_NAME');
            }
            if ($assetsinfo['is_patrol'] == C('YES_STATUS')) {
                $assetsinfo['type'] .= '、' . C('ASSETS_PATROL_CODE_YES_NAME');
            }
            if ($assetsinfo['is_benefit'] == C('YES_STATUS')) {
                $assetsinfo['type'] .= '、' . C('ASSETS_BENEFIT_CODE_YES_NAME');
            }
            if ($assetsinfo['is_lifesupport'] == C('YES_STATUS')) {
                $assetsinfo['type'] .= '、' . C('ASSETS_LIFE_SUPPORT_NAME');
            }
            $assetsinfo['type']=trim($assetsinfo['type'],'、');
            switch ($assetsinfo['pay_status']) {
                case 0:
                    $assetsinfo['pay_statusName'] = '未付清';
                    break;
                case 1:
                    $assetsinfo['pay_statusName'] = '已付清';
                    break;
                case 3:
                    $assetsinfo['pay_statusName'] = '';
                    break;
                default:
                    $assetsinfo['pay_statusName'] = '';
                    break;
            }
            switch ($assetsinfo['is_domestic']) {
                case 1:
                    $assetsinfo['is_domesticName'] = '国产';
                    break;
                case 2:
                    $assetsinfo['is_domesticName'] = '进口';
                    break;
                case 3:
                    $assetsinfo['is_domesticName'] = '';
                    break;
                default:
                    $assetsinfo['is_domesticName'] = '';
                    break;
            }
            switch ($assetsinfo['assets_level']) {
                case 1:
                    $assetsinfo['assets_level_name'] = 'Ⅰ类';
                    break;
                case 2:
                    $assetsinfo['assets_level_name'] = 'Ⅱ类';
                    break;
                case 3:
                    $assetsinfo['assets_level_name'] = 'Ⅲ类';
                    break;
                default:
                    $assetsinfo['assets_level_name'] = '';
                    break;
            }
        }
        return $assetsinfo;
    }


    /**
     * 获取设备申购信息
     * @param $apply_id int 申购id
     * @return array
     * */
    public function getAssetsApplyBasic($apply_id)
    {
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $where['apply_id'] = ['EQ', $apply_id];
        $data = $this->DB_get_one('purchases_depart_apply', 'apply_reason,apply_time,apply_user,apply_departid,apply_num,project_name', $where);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $data['department'] = $departname[$data['apply_departid']]['department'];
        return $data;
    }

    /**
     * 获取对应合同的信息
     * @param $contract_id int 合同id
     * @return array
     * */
    public function getAssetsContractBasic($contract_id)
    {
        $where['contract_id'] = ['EQ', $contract_id];
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $data = $this->DB_get_one('purchases_contract', '', $where);
        $data['guarantee_date'] = HandleEmptyNull($data['guarantee_date']);
        $data['end_date'] = HandleEmptyNull($data['end_date']);
        $data['sign_date'] = HandleEmptyNull($data['sign_date']);
        switch ($data['contract_type']) {
            case C('CONTENT_TYPE_SUPPLIER'):
                $data['contract_type_name'] = C('CONTENT_TYPE_SUPPLIER_NAME');
                break;
            case C('CONTENT_TYPE_REPAIR'):
                $data['contract_type_name'] = C('CONTENT_TYPE_REPAIR_NAME');
                break;
            default:
                $data['contract_type_name'] = '未知类型';
                break;

        }
        return $data;
    }

    /**
     * 获取合同付款信息
     * @param $contract_id int 合同id
     * @return array
     * */
    public function getAssetsContractPay($contract_id)
    {
        $where['contract_id'] = ['EQ', $contract_id];
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $data = $this->DB_get_all('purchases_contract_pay', '', $where);
        foreach ($data as &$one) {
            $one['real_pay_date'] = HandleEmptyNull($one['real_pay_date']);
            $one['estimate_pay_date'] = HandleEmptyNull($one['estimate_pay_date']);

        }
        return $data;
    }

    /**
     * 获取采购设备对应的字典信息
     * @param $assets int 采购计划设备名称
     * @param $hospital_id int 医院id
     * @return array
     * */
    public function getAssetsDicBasic($assets, $hospital_id)
    {
        $where['hospital_id'] = ['EQ', $hospital_id];
        $where['assets'] = ['EQ', $assets];
        $where['status'] = ['EQ', C('YES_STATUS')];
        $data = $this->DB_get_one('dic_assets', 'assets,assets_category,catid,unit', $where);
        if ($data['assets_category']) {
            $assets_category = explode(',', $data['assets_category']);
            foreach ($assets_category as &$value) {
                if ($value == 'is_firstaid') {
                    $data['is_firstaid'] = C('YES_STATUS');
                }
                if ($value == 'is_special') {
                    $data['is_special'] = C('YES_STATUS');
                }
                if ($value == 'is_metering') {
                    $data['is_metering'] = C('YES_STATUS');
                }
                if ($value == 'is_qualityAssets') {
                    $data['is_qualityAssets'] = C('YES_STATUS');
                }
                if ($value == 'is_benefit') {
                    $data['is_benefit'] = C('YES_STATUS');
                }
                if ($value == 'is_lifesupport') {
                    $data['is_lifesupport'] = C('YES_STATUS');
                }
            }
        }
        return $data;
    }


    /**
     *  获取已上传的验收资料文件
     * @param $assets_id int 采购计划设备明细id
     * @return array
     * */

    public function getCheckAssetsFile($assets_id)
    {
        $where['assets_id'] = ['EQ', $assets_id];
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $data = $this->DB_get_all('purchases_depart_apply_checkassets_file', '', $where);
        $file['file'] = [];
        $file['enclosure'] = [];
        if ($data) {
            foreach ($data as &$dataV) {
                $dataV['file_size'] = round($dataV['file_size'] / 1024 / 1024, 2) . 'M';
                if (judgeEmpty($dataV['style'])) {
                    //验收资料
                    $file['file'][$dataV['style']] = $dataV;
                } else {
                    //验收附件
                    $dataV['operation'] = '<div class="layui-btn-group">';
                    $dataV['operation'] .= '<input type="hidden" name="file_url" value="' . $dataV['file_url'] . '">';
                    $dataV['operation'] .= '<input type="hidden" name="file_id" value="' . $dataV['file_id'] . '">';
                    $dataV['operation'] .= '<input type="hidden" name="file_name" value="' . $dataV['file_name'] . '">';
                    $dataV['operation'] .= '<input type="hidden" name="file_type" value="' . $dataV['file_type'] . '">';
                    $dataV['operation'] .= $this->returnListLink('下载', '', '', C('BTN_CURRENCY') . ' downFile', '');
                    if ($dataV['file_type'] != 'doc' && $dataV['file_type'] != 'docx') {
                        $dataV['operation'] .= $this->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' layui-btn-normal showFile', '');
                    }
                    $dataV['operation'] .= '<button class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</button>';
                    $dataV['operation'] .= '</div>';
                    $file['enclosure'][] = $dataV;
                }
            }
        }
        return $file;
    }

    /**
     * 上传文件
     * @param $dir string 保存的文件名
     * @param $style array 允许上传的格式
     * @return array
     * */
    public function uploadfile($dir = '', $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx'))
    {
        if ($_FILES['file']) {
            $Tool = new ToolController();
            if ($dir) {
                $dirName = $dir;
            } else {
                $dirName = $this->Controller;
            }
            $info = $Tool->upFile($style, $dirName);
            if ($info['status']==C('YES_STATUS')) {
                // 上传成功 获取上传文件信息
                $resule['status'] = 1;
                $resule['msg'] = '上传成功';
                $resule['file_url'] = $info['src'];
                $resule['title'] = $info['title'];
                $resule['formerly'] = $info['formerly'];
                $resule['file_type'] = $info['ext'];
                $resule['file_size'] = $info['size'];
            } else {
                // 上传错误提示错误信息
                $resule['status'] = -1;
                $resule['msg'] = $info['msg'];
            }
        } else {
            // 上传错误提示错误信息
            $resule['status'] = -1;
            $resule['msg'] = '未接收到文件';
        }
        return $resule;
    }

    /**
     * 保存验收文件
     * @param $file array 保存在服务器文件信息
     * @return array
     * */
    public function addCheckFile($file)
    {
        $file_id = I('POST.file_id');
        $style = I('POST.style');
        $file_name = I('POST.file_name');
        $assets_id = I('POST.assets_id');
        if (!$assets_id) {
            die(json_encode(array('status' => -1, 'msg' => '参数缺少,请按正常流程操作')));
        }
        if (!$file_name) {
            $file_name = $file['formerly'];
        }

        $add['file_name'] = $file_name;
        $add['assets_id'] = $assets_id;
        $add['style'] = $style;
        $add['save_name'] = $file['title'];
        $add['file_type'] = $file['file_type'];
        $add['file_size'] = $file['file_size'];
        $add['file_url'] = $file['file_url'];
        $add['add_user'] = session('username');
        $add['add_time'] = getHandleDate(time());
        if ($file_id > 0) {
            $this->updateData('purchases_depart_apply_checkassets_file', $add, array('file_id' => $file));
        } else {
            $file_id = $this->insertData('purchases_depart_apply_checkassets_file', $add);
        }
        $file['file_size'] = round($file['file_size'] / 1024 / 1024, 2) . 'M';
        $file['file_id'] = $file_id;
        return $file;
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

}
