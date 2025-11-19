<?php

namespace Admin\Model;

use Common\Weixin\Weixin;
use Think\Model;
use Think\Model\RelationModel;
use Admin\Model\ModuleModel;

class AdverseModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'adverse_info';
    private $MODULE = 'Adverse';

    public function getAssetsData()
    {
        $hospital_id = session('current_hospitalid');
        if (!session('isSuper')) {
            $departid = session('departid');
            $where = array('departid' => array('IN', $departid));
            $where['A.hospital_id'] = $hospital_id;
            $field = 'A.assid,A.assets,A.assnum,A.serialnum,A.model,B.ols_facid';
            $join = 'LEFT JOIN sb_assets_factory AS B ON A.assid = B.assid';
            $Assets = $this->DB_get_all_join('assets_info', 'A', $field, $join, $where, '', '', '');
        } else {
            $where['A.hospital_id'] = $hospital_id;
            $field = 'A.assid,A.assets,A.assnum,A.serialnum,A.model,B.factory';
            $join = 'INNER JOIN sb_assets_factory AS B ON A.assid = B.assid';
            $Assets = $this->DB_get_all_join('assets_info', 'A', $field, $join, $where, '', '', '');
        }
        $res = array();
        $i = 0;
        foreach ($Assets as $k => $v) {
            //查询生产企业信息
            $facInfo = [];
            if ($v['ols_facid']) {
                $facInfo = $this->DB_get_one('offline_suppliers', 'sup_name,salesman_phone,address', array('olsid' => $v['ols_facid']));
            }
            $res[$i]['assid'] = $v['assid'];
            $res[$i]['assnum'] = $v['assnum'];
            $res[$i]['assets'] = $v['assets'];
            $res[$i]['serialnum'] = $v['serialnum'];
            $res[$i]['model'] = $v['model'];
            $res[$i]['factory'] = $facInfo['sup_name'];
            $res[$i]['factory_tel'] = $facInfo['salesman_phone'];
            $res[$i]['address'] = $facInfo['address'];
            $i++;
        }
        $arr = array();
        $arr['value'] = $res;
        return $arr;
    }

    public function addAdverse()
    {
        $result = array();
//        是暂存状态还是结束状态
        $addData['status'] = I('post.status');
//        assid
        $addData['assid'] = I('post.assid');
//        报告日期
        $addData['report_date'] = I('post.report_date');
//        报告来源
        $addData['report_from'] = I('post.report_from');
//        联系地址
        $addData['address'] = I('post.address');
//        编码
        $addData['code'] = I('post.code');
//        单位
        $addData['unit'] = I('post.unit');
//        邮编
        $addData['post_code'] = I('post.post_code');
//        联系电话
        $addData['telphone'] = I('post.telphone');
//        患者姓名
        $addData['name'] = I('post.name');
//        年龄
        if (I('post.age')) {
            $addData['age'] = I('post.age');
        }
//        性别
        if (I('post.sex') != '') {
            $addData['sex'] = I('post.sex');
        }
//        预期治疗疾病或者作用
        $addData['expected'] = I('post.expected');
//        事件主要表现
        $addData['express'] = I('post.express');
//        事件发生时间
        $addData['express_date'] = I('post.express_date') ?: null;
//        发现或知悉事件
        $addData['discovery'] = I('post.discovery');
//        医疗器械实际使用场所：
        if (!I('post.place')) {
            $addData['place'] = '-1';
        } else {
            $addData['place'] = I('post.place');
        }
//        事件后果
        $addData['consequence'] = strtotime(I('post.consequence')) > 0 ? '死亡（日期：' . I('POST.consequence') . '）' : I('POST.consequence');
        if (!$addData['consequence']) {
            $addData['consequence'] = '-1';
        }
//        报告人
        $addData['reporter'] = I('post.reporter');
//        报告人签字
        $addData['sign'] = I('post.sign');
//        产品名称
        if (I('post.assets')) {
            $addData['assets'] = I('post.assets');
        } else {
            $result['msg'] = '请填写产品名称';
            $result['status'] = -1;
        }
//        商品名称
        $addData['commodity'] = I('post.commodity');
//        注册证号
        $addData['register_num'] = I('post.register_num');
//        生产企业
        $addData['company'] = I('post.company');
//        生产企业地址
        $addData['company_address'] = I('post.company_address');
//        企业联系地址
        $addData['company_contract'] = I('post.company_contract');
//        产品型号
        $addData['model'] = I('post.model');
//        产品编号
        $addData['assnum'] = I('post.assnum');
//        产品批号
        $addData['assets_batch_num'] = I('post.assets_batch_num');
//        操作人
        if (I('post.operator')) {
            $addData['operator'] = I('post.operator');
        } else {
            $addData['operator'] = '-1';
        }
//        有效期至
        $addData['validity_date'] = I('post.validity_date') ?: null;
//        生产日期
        $addData['manufacture_date'] = I('post.manufacture_date') ?: null;
//        停用日期
        $addData['discontinuation_date'] = I('post.discontinuation_date') ?: null;
//        植入日期（若植入）
        $addData['implantation_date'] = I('post.implantation_date') ?: null;
//        事件发生初步原因分析
        $addData['cause'] = I('post.cause');
//        事件初步处理情况
        $addData['situation'] = I('post.situation');
//        事件报告状态
        $addData['report_status'] = trim(I('post.report_status'), ',');
//        省级监测技术机构评价意见（可另附附页）
        $addData['provincial_monitoring'] = I('post.provincial_monitoring');
//        国家监测技术机构评价意见（可另附附页）
        $addData['state_monitoring'] = I('post.state_monitoring');
//        文件上传地址
        $addData['report'] = I('post.file_url');
        //        原文件名
        $addData['Filename'] = I('post.Filename');
//        添加人
        $addData['adduser'] = session('username');
//        添加时间
        $addData['addtime'] = getHandleDate(time());
        $addData['hospital_id'] = session('current_hospitalid');
        $add = $this->insertData('adverse_info', $addData);
        //日志行为记录文字
        $log['assnum'] = $addData['assnum'];
        $text = getLogText('addAdverseLogText', $log);
        $this->addLog('adverse_info', M()->getLastSql(), $text, $add, '');
        if ($add) {
            $result['msg'] = '新增成功';
            $result['status'] = 1;
            $this->send_wechat_adverse_news($addData);
        } else {
            $result['msg'] = '新增失败';
            $result['status'] = -1;
        }
        return $result;
    }

    public function send_wechat_adverse_news($addData)
    {
        //给设备科发送消息
        $moduleModel = new ModuleModel();
        $wx_status = $moduleModel->decide_wx_login();
        if (I('post.status') == 1) {
            if(C('USE_FEISHU') === 1){
                //==========================================飞书 START========================================
                //获取设备信息
                $assInfo = [];
                if($addData['assid']){
                    $assInfo = $this->DB_get_one('assets_info','assets,assnum,model,departid',array('assid'=>$addData['assid']));
                    $departname = [];
                    include APP_PATH . "Common/cache/department.cache.php";
                    $assInfo['department'] = $departname[$assInfo['departid']]['department'];
                }
                //要显示的字段区域
                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**上报人员：**'.session('username');
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**设备名称：**'.$assInfo['assets'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**设备编码：**'.$assInfo['assnum'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false;//是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**使用科室：**'.$assInfo['department'];
                $feishu_fields[] = $fd;

                //按钮区域
                $act['tag'] = 'button';
                $act['type'] = 'primary';
                $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME').'/#' . C('FS_NAME').'/Lookup/showAssets?assid=' . $addData['assid'];
                $act['text']['tag'] = 'plain_text';
                $act['text']['content'] = '查看详情';
                $feishu_actions[] = $act;

                $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag'] = 'div';
                $card_data['elements'][0]['fields'] = $feishu_fields;
                $card_data['elements'][1]['tag'] = 'hr';
                $card_data['elements'][2]['actions'] = $feishu_actions;
                $card_data['elements'][2]['layout'] = 'bisected';
                $card_data['elements'][2]['tag'] = 'action';
                $card_data['header']['template'] = 'red';
                $card_data['header']['title']['content'] = '设备不良事件报告提醒';
                $card_data['header']['title']['tag'] = 'plain_text';

                $userModel = new UserModel();
                $assetsUser = $userModel->getUsers('assetsApproveBorrow', "", false);
                $assetsUser = array_column($assetsUser, 'openid');
                $assetsUser = array_unique($assetsUser);
                foreach ($assetsUser as $key => $value) {
                    $this->send_feishu_card_msg($value,$card_data);
                }
                //==========================================飞书 END==========================================
            }else{
                if($wx_status){
                    //获取设备信息
                    $assInfo = [];
                    $url = '';
                    if($addData['assid']){
                        $assInfo = $this->DB_get_one('assets_info','assets,assnum,model,departid',array('assid'=>$addData['assid']));
                        $departname = [];
                        include APP_PATH . "Common/cache/department.cache.php";
                        $assInfo['department'] = $departname[$assInfo['departid']]['department'];
                        if(C('USE_VUE_WECHAT_VERSION')){
                            $url = C('APP_NAME') . C('VUE_FOLDER_NAME').'/#' . C('VUE_NAME').'/Lookup/showAssets?assid=' . $addData['assid'];
                        }else{
                            $url = C('HTTP_HOST') . C('MOBILE_NAME').'/Lookup/showAssets.html?assid=' . $addData['assid'];
                        }
                    }
                    $userModel = new UserModel();
                    $assetsUser = $userModel->getUsers('assetsApproveBorrow', "", false);

                    $openIds = array_column($assetsUser, 'openid');
                    $openIds = array_filter($openIds);
                    $openIds = array_unique($openIds);

                    $messageData = [
                        'thing5'            => $assInfo['department'],// 使用科室
                        'thing4'            => $assInfo['assets'],// 设备名称
                        'character_string6' => $assInfo['assnum'],// 设备编号
                        'thing7'            => session('username'),// 上报人员
                    ];

                    foreach ($openIds as $openId) {
                        Weixin::instance()->sendMessage($openId, '医疗器械不良事件上报通知', $messageData, $url);
                    }
                }
            }
        }
    }

    public function getAdverseData()
    {
        $departids = session('departid');
        $departids_arr = explode(',',$departids);
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order');
        //设备名称
        $assets = I('post.getAdverseListsAssets');
        //科室
        $department = I('post.department');
        //报告来源
        $report_from = I('post.getAdverseListReport_from');
        //时间
        $startDate = I('post.getAdverseListStartDate');
        $endDate = I('post.getAdverseListEndDate');
        $hospital_id = I('post.hospital_id');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        if (session('isSuper') != C('YES_STATUS')) {
            //$where['B.departid'] = array('in', $departids);
        }
        /*if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }*/
        //如果是生命线历程页面点进来的 获取到的assid
        if (I('post.assid')) {
            $where['A.assid'] = I('post.assid');
        }
        //设备名称搜索
        if ($assets) {
            $where['A.assets'] = array('like', '%' . $assets . '%');
        }
        //报告来源搜索
        if ($report_from) {
            $where['A.report_from'] = $report_from;
        }
        //科室名称搜索
        if ($department) {
            $where['B.departid'] = array('IN', $department);
        }
        //操作时间搜索
        if ($startDate) {
            $where['A.report_date'][] = array('GT', getHandleTime(strtotime($startDate) - 1));
        }
        //操作时间搜索
        if ($endDate) {
            $where['A.report_date'][] = array('LT', getHandleTime(strtotime($endDate) + 24 * 3600));
        }
        //查询当前用户是否有权限进行编辑不良报告
        $editAdverse = get_menu($this->MODULE, 'Adverse', 'editAdverse');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $fields = 'A.id,A.assnum,A.assets,A.model,A.status AS adverseStatus,B.departid,B.status,A.reporter,A.report_from,A.sign,A.report_date,A.express_date,A.report_status,A.cause,A.situation,A.consequence,A.express,A.report,A.Filename';
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $total = $this->DB_get_count_join('adverse_info', 'A', $join, $where, '');
        $adverseInfo = $this->DB_get_all_join('adverse_info', 'A', $fields, $join, $where, '', '', $offset . "," . $limit);
        foreach ($adverseInfo as $k => $v) {
            $newReportStatus = '';
            $html = '';
            $adverseInfo[$k]['department'] = $departname[$v['departid']]['department'];
            if (($editAdverse && in_array($v['departid'],$departids_arr))||($v['sign']==session('username'))) {
                $html .= $this->returnListLink('编辑', $editAdverse['actionurl'], 'editAdverse', C('BTN_CURRENCY'));
            }else{
                $html .= $this->returnListLink('编辑', '', '', C('BTN_CURRENCY').' layui-btn-disabled');
            }
            if ($v['adverseStatus'] == 1) {
                $html .= $this->returnListLink('查看', C('ADMIN_NAME').'/Adverse/getAdverseList', 'showAdverse', C('BTN_CURRENCY') . ' layui-btn-normal');
            }
            switch ($v['status']) {
                case 0:
                    $adverseInfo[$k]['status'] = '在用';
                    break;
                case 1:
                    $adverseInfo[$k]['status'] = '维修中';
                    break;
                case 2:
                    $adverseInfo[$k]['status'] = '已报废';
                    break;
                case 3:
                    $adverseInfo[$k]['status'] = '已外调';
                    break;
                case 4:
                    $adverseInfo[$k]['status'] = '外调中';
                    break;
                case 5:
                    $adverseInfo[$k]['status'] = '报废中';
                    break;
                case 6:
                    $adverseInfo[$k]['status'] = '转科中';
                    break;
            }
            $reportStatus = explode(',', $v['report_status']);
            foreach ($reportStatus as $k1 => $v1) {
                switch ($v1) {
                    case 1:
                        $newReportStatus .= '已通知使用单位,';
                        break;
                    case 2:
                        $newReportStatus .= '已通知生产企业,';
                        break;
                    case 3:
                        $newReportStatus .= '已通知经营企业,';
                        break;
                    case 4:
                        $newReportStatus .= '已通知药监部门,';
                        break;
                }
            }
            $adverseInfo[$k]['report_date'] = HandleEmptyNull($v['report_date']);
            $adverseInfo[$k]['express_date'] = HandleEmptyNull($v['express_date']);
            $adverseInfo[$k]['report_status'] = trim($newReportStatus, ',');
            $adverseInfo[$k]['getAdverseListsOperation'] = $html;
            if ($adverseInfo[$k]['report']) {
                $jpghtml = '';
                $supplement = 'data-path="' . $adverseInfo[$k]['report'] . '" data-name="' . $adverseInfo[$k]['Filename'] . '"';
                $jpghtml .= $this->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' showFile', '', $supplement);
                $jpghtml .= $this->returnListLink('下载', '', '', C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                $adverseInfo[$k]['report'] = $jpghtml;
            }
        }
        $result["code"] = 200;
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["rows"] = $adverseInfo;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    public function editAdverse()
    {
        $result = array();
//        是暂存状态还是结束状态
        $editData['status'] = I('post.status');
        $id = I('post.id');
//        assid
        $editData['assid'] = I('post.assid');
        if (I('post.report_date')) {
//            报告日期
            $editData['report_date'] = I('post.report_date');
        }
        if (I('post.report_from')) {
//            报告来源
            $editData['report_from'] = I('post.report_from');
        }
        if (I('post.address')) {
//            联系地址
            $editData['address'] = I('post.address');
        }
        if (I('post.code')) {
//            编码
            $editData['code'] = I('post.code');
        }
        if (I('post.unit')) {
//            单位
            $editData['unit'] = I('post.unit');
        }
        if (I('post.post_code')) {
//            邮编
            $editData['post_code'] = I('post.post_code');
        }
        if (I('post.telphone')) {
//            联系电话
            $editData['telphone'] = I('post.telphone');
        }
        if (I('post.name')) {
//            患者姓名
            $editData['name'] = I('post.name');
        }
        if (I('post.age')) {
//            年龄
            $editData['age'] = I('post.age');
        }
        if (I('post.sex')) {
//            性别
            $editData['sex'] = I('post.sex');
        }
        if (I('post.expected')) {
//            预期治疗疾病或者作用
            $editData['expected'] = I('post.expected');
        }
        if (I('post.express')) {
//            事件主要表现
            $editData['express'] = I('post.express');
        }
        if (I('post.express_date')) {
//            事件发生时间
            $editData['express_date'] = I('post.express_date');
        }
        if (I('post.discovery')) {
//            发现或知悉事件
            $editData['discovery'] = I('post.discovery');
        }
        if (I('post.place')) {
//            医疗器械实际使用场所：
            $editData['place'] = I('post.place');
        }
        if (I('post.consequence')) {
//            事件后果
            $editData['consequence'] = I('post.consequence');
        }
        if (I('post.reporter')) {
//            报告人
            $editData['reporter'] = I('post.reporter');
        }
        if (I('post.sign')) {
//            报告人签字
            $editData['sign'] = I('post.sign');
        }
        if (I('post.assets')) {
//            产品名称
            $editData['assets'] = I('post.assets');
        } else {
            $result['msg'] = '请填写产品名称';
            $result['status'] = -1;
        }
        if (I('post.commodity')) {
//            商品名称
            $editData['commodity'] = I('post.commodity');
        }
        if (I('post.register_num')) {
//            注册证号
            $editData['register_num'] = I('post.register_num');
        }
        if (I('post.company')) {
//            生产企业
            $editData['company'] = I('post.company');
        }
        if (I('post.company_address')) {
//            生产企业地址
            $editData['company_address'] = I('post.company_address');
        }
        if (I('post.company_contract')) {
//            企业联系地址
            $editData['company_contract'] = I('post.company_contract');
        }
        if (I('post.model')) {
//            产品型号
            $editData['model'] = I('post.model');
        }
        if (I('post.assnum')) {
//            产品编号
            $editData['assnum'] = I('post.assnum');
        }
        if (I('post.assets_batch_num')) {
//            产品批号
            $editData['assets_batch_num'] = I('post.assets_batch_num');
        }
        if (I('post.operator')) {
//            操作人
            $editData['operator'] = I('post.operator');
        }
        if (I('post.validity_date')) {
//            有效期至
            $editData['validity_date'] = I('post.validity_date');
        }
        if (I('post.manufacture_date')) {
//            生产日期
            $editData['manufacture_date'] = I('post.manufacture_date');
        }
        if (I('post.discontinuation_date')) {
//            停用日期
            $editData['discontinuation_date'] = I('post.discontinuation_date');
        }
        if (I('post.implantation_date')) {
//            植入日期（若植入）
            $editData['implantation_date'] = I('post.implantation_date');
        }
        if (I('post.cause')) {
//            事件发生初步原因分析
            $editData['cause'] = I('post.cause');
        }
        if (I('post.situation')) {
//            事件初步处理情况
            $editData['situation'] = I('post.situation');
        }
        if (I('post.report_status')) {
//            事件报告状态
            $editData['report_status'] = trim(I('post.report_status'), ',');
        }
        if (I('post.provincial_monitoring')) {
//            省级监测技术机构评价意见（可另附附页）
            $editData['provincial_monitoring'] = I('post.provincial_monitoring');
        }
        if (I('post.state_monitoring')) {
//            国家监测技术机构评价意见（可另附附页）
            $editData['state_monitoring'] = I('post.state_monitoring');
        }
        if (I('post.file_url')) {
//            文件上传地址
            $editData['report'] = I('post.file_url');
        }
        if (I('post.Filename')) {
            //        原文件名
            $editData['Filename'] = I('post.Filename');
        }
//        修改人
        $editData['edituser'] = session('username');
//        修改时间
        $editData['edittime'] = getHandleDate(time());
        if ($id) {
            $this->updateData('adverse_info', $editData, array('id' => $id));
            $this->send_wechat_adverse_news($editData);
            //日志行为记录文字
            $log['assnum'] = $editData['assnum'];
            $text = getLogText('editAdverseLogText', $log);
            $this->addLog('adverse_info', M()->getLastSql(), $text, $id, '');
            $result['msg'] = '编辑成功';
            $result['status'] = 1;
        } else {
            $result['msg'] = '编辑失败';
            $result['status'] = -1;
        }
        return $result;
    }

    /*
     * 显示不良报告获取的后台数据
     * */
    public function showAdverseData($id)
    {
        $adverseInfo = $this->DB_get_one('adverse_info', '', array('id' => $id), '');
        if ($adverseInfo['sex'] == null) {
            $adverseInfo['sex'] = 3;
        }
//        文件判断
        $adverseInfo['type'] = substr(strrchr($adverseInfo['report'], '.'), 1);
        $adverseInfo['report_date'] = HandleEmptyNull($adverseInfo['report_date']);
        $adverseInfo['express_date'] = HandleEmptyNull($adverseInfo['express_date']);
        $adverseInfo['validity_date'] = HandleEmptyNull($adverseInfo['validity_date']);
        $adverseInfo['manufacture_date'] = HandleEmptyNull($adverseInfo['manufacture_date']);
        $adverseInfo['discontinuation_date'] = HandleEmptyNull($adverseInfo['discontinuation_date']);
        $adverseInfo['implantation_date'] = HandleEmptyNull($adverseInfo['implantation_date']);
        switch ($adverseInfo['place']) {
            case '医疗机构':
                $adverseInfo['placeStatus'] = 0;
                break;
            case '家庭':
                $adverseInfo['placeStatus'] = 0;
                break;
            case '0':
                $adverseInfo['placeStatus'] = 0;
                break;
            case '-1':
                $adverseInfo['placeStatus'] = -1;
                break;
            default:
                $adverseInfo['placeStatus'] = 1;
                break;
        }
        if (isdate($adverseInfo['consequence'])) {
            $adverseInfo['consequenceStatus'] = 1;
        } else {
            switch ($adverseInfo['consequence']) {
                case '危及生命':
                    $adverseInfo['consequenceStatus'] = 0;
                    break;
                case '机体功能结构永久性损伤':
                    $adverseInfo['consequenceStatus'] = 0;
                    break;
                case '可能导致机体功能结构永久性损伤':
                    $adverseInfo['consequenceStatus'] = 0;
                    break;
                case '-1':
                    $adverseInfo['consequenceStatus'] = -1;
                    break;
                case '需要内、外科治疗避免上述永久损伤':
                    $adverseInfo['consequenceStatus'] = 0;
                    break;
                default:
                    $adverseInfo['consequenceStatus'] = 2;
                    break;
            }
        }
        switch ($adverseInfo['operator']) {
            case '专业人员':
                $adverseInfo['operatorStatus'] = 0;
                break;
            case '非专业人员':
                $adverseInfo['operatorStatus'] = 0;
                break;
            case '-1':
                $adverseInfo['operatorStatus'] = -1;
                break;
            case '患者':
                $adverseInfo['operatorStatus'] = 0;
                break;
            default:
                $adverseInfo['operatorStatus'] = 1;
                break;
        }
        $adverseInfo['report_status'] = explode(',', $adverseInfo['report_status']);
        return $adverseInfo;
    }

    /**
     * Notes: 获取不良数据
     */
    public function getAllAdverses()
    {
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-6 month"));
        $hospital_id = I('post.hospital_id');
        $startDate = I('post.startDate');
        $endDate = I('post.endDate');
        $where['status'] = 1;
        if ($hospital_id) {
            $where['hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }
        if ($startDate && !$endDate) {
            $where['report_date'] = array('egt', $startDate);
        }
        if ($endDate && !$startDate) {
            $where['report_date'] = array('elt', $endDate);
        }
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                return array('status' => -1, 'msg' => '请选择合理的日期区间！');
            }
            $where['report_date'] = array(array('egt', $startDate), array('elt', $endDate));
        }
        if (!$startDate && !$endDate) {
            $where['report_date'] = array(array('egt', $start_date), array('elt', $end_date));
        }
        $total = $this->DB_get_count('adverse_info', $where);
        $data = $this->DB_get_all('adverse_info', 'id,hospital_id,assid,report_date,report_from,age,sex,consequence,place', $where);
        //组织数据
        $tmp = array();
        foreach ($data as $k => $v) {
            if ($v['assid']) {
                //院内设备不良数据
                $tmp['innerData']['total'] += 1;
                $tmp['innerData']['lists'][] = $v;
            } else {
                $tmp['outsideData']['total'] += 1;
                $tmp['outsideData']['lists'][] = $v;
            }

        }
        $num = 0;
        $res = array();
        foreach ($tmp as $k => $v) {
            if ($k == 'outsideData') {
                $res[$num]['type'] = '院外设备';
                $res = $this->getDetailData($res, $v, $num);
            } else {
                $res[$num]['type'] = '院内设备';
                $res = $this->getDetailData($res, $v, $num);
            }
            $num++;
        }
        $result["total"] = $total;
        $result["code"] = 200;
        $result["rows"] = $res;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    private function getDetailData($res, $v, $num)
    {
        $res[$num]['report_num'] = $v['total'];
        $res[$num]['report_from_factory'] = $res[$num]['report_from_bussniess'] = $res[$num]['report_from_user'] = $res[$num]['report_from_none'] = 0;
        $res[$num]['male'] = $res[$num]['female'] = $res[$num]['none_sex'] = 0;
        $res[$num]['die'] = $res[$num]['jeolife'] = $res[$num]['res3'] = $res[$num]['resother'] = 0;
        $res[$num]['place1'] = $res[$num]['place2'] = $res[$num]['place3'] = $res[$num]['place4'] = 0;
        $res[$num]['age1'] = $res[$num]['age2'] = $res[$num]['age3'] = $res[$num]['age4'] = $res[$num]['age5'] = 0;
        foreach ($v['lists'] as $key => $val) {
            switch ($val['report_from']) {
                case '使用单位':
                    $res[$num]['report_from_user'] += 1;
                    break;
                case '经营企业':
                    $res[$num]['report_from_bussniess'] += 1;
                    break;
                case '生产企业':
                    $res[$num]['report_from_factory'] += 1;
                    break;
                case '':
                    $res[$num]['report_from_none'] += 1;
                    break;
            }
            switch ($val['sex']) {
                case '1':
                    $res[$num]['male'] += 1;
                    break;
                case '0':
                    $res[$num]['female'] += 1;
                    break;
                case '':
                    $res[$num]['none_sex'] += 1;
                    break;
            }
            switch ($val['place']) {
                case '医疗机构':
                    $res[$num]['place1'] += 1;
                    break;
                case '家庭':
                    $res[$num]['place2'] += 1;
                    break;
                case '-1':
                    $res[$num]['place3'] += 1;
                    break;
                default:
                    $res[$num]['place4'] += 1;
                    break;
            }
            if (mb_strpos($val['consequence'], '死亡') !== false) {
                $res[$num]['die'] += 1;
            } elseif ($val['consequence'] == '危及生命') {
                $res[$num]['jeolife'] += 1;
            } elseif ($val['consequence'] == '机体功能结构永久性损伤') {
                $res[$num]['res3'] += 1;
            } else {
                $res[$num]['resother'] += 1;
            }
            if ($val['age']) {
                if ($val['age'] >= 0 && $val['age'] < 20) {
                    $res[$num]['age1'] += 1;
                }
                if ($val['age'] >= 20 && $val['age'] < 40) {
                    $res[$num]['age2'] += 1;
                }
                if ($val['age'] >= 40 && $val['age'] < 60) {
                    $res[$num]['age3'] += 1;
                }
                if ($val['age'] >= 60) {
                    $res[$num]['age4'] += 1;
                }
            } else {
                $res[$num]['age5'] += 1;
            }
        }
        return $res;
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
        $result['title']['inner_out'] = '不良事件设备所属分布';
        $result['title']['report_from'] = '不良事件报告来源分布';
        $result['title']['patient_sex'] = '患者性别分布';
        $result['title']['patient_age'] = '患者年龄分布';
        $result['title']['patient_place'] = '医疗器械使用场所分布';
        $result['title']['event_result'] = '不良事件导致后果分布';
        $result['color']['inner_out'] = '#481350';
        $result['color']['report_from'] = '#3F6F88';
        $result['color']['patient_sex'] = '#778D2C';
        $result['color']['patient_age'] = '#B58240';
        $result['color']['patient_place'] = '#EE744F';
        $result['color']['event_result'] = '#414487';
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
            if ($v['type'] == '院外设备') {
                $result['inner_out'][$k]['value'] = $v['report_num'];
                $result['inner_out'][$k]['name'] = $v['type'];
                $result['inner_out'][$k]['itemStyle']['color'] = '#006262';
            } else {
                $result['inner_out'][$k]['value'] = $v['report_num'];
                $result['inner_out'][$k]['name'] = $v['type'];
                $result['inner_out'][$k]['itemStyle']['color'] = '#400D64';
            }
        }
        $from_user = $from_bussniess = $from_factory = $from_none = $malenum = $femalenum = $none_sex_num = 0;
        $age1_num = $age2_num = $age3_num = $age4_num = $age5_num = 0;
        $die_num = $jeolife_num = $res3_num = $resother_num = 0;
        $place1_num = $place2_num = $place3_num = $place4_num = 0;
        foreach ($data['rows'] as $k => $v) {
            $from_user += $v['report_from_user'];
            $from_bussniess += $v['report_from_bussniess'];
            $from_factory += $v['report_from_factory'];
            $from_none += $v['report_from_none'];
            $malenum += $v['male'];
            $femalenum += $v['female'];
            $none_sex_num += $v['none_sex'];
            $age1_num += $v['age1'];
            $age2_num += $v['age2'];
            $age3_num += $v['age3'];
            $age4_num += $v['age4'];
            $age5_num += $v['age5'];
            $place1_num += $v['place1'];
            $place2_num += $v['place2'];
            $place3_num += $v['place3'];
            $place4_num += $v['place4'];
            $die_num += $v['die'];
            $jeolife_num += $v['jeolife'];
            $res3_num += $v['res3'];
            $resother_num += $v['resother'];
        }
        //报告来源
        $result['report_from'][0]['value'] = $from_user;
        $result['report_from'][0]['name'] = '使用单位';
        $result['report_from'][0]['itemStyle']['color'] = '#671002';

        $result['report_from'][1]['value'] = $from_bussniess;
        $result['report_from'][1]['name'] = '经营企业';
        $result['report_from'][1]['itemStyle']['color'] = '#26093B';

        $result['report_from'][2]['value'] = $from_factory;
        $result['report_from'][2]['name'] = '生产企业';
        $result['report_from'][2]['itemStyle']['color'] = '#B975F7';

        $result['report_from'][3]['value'] = $from_none;
        $result['report_from'][3]['name'] = '未填写';
        $result['report_from'][3]['itemStyle']['color'] = '#9EC4E5';

        //性别分布
        $result['patient_sex'][0]['value'] = $malenum;
        $result['patient_sex'][0]['name'] = '男';
        $result['patient_sex'][0]['itemStyle']['color'] = '#00A2EA';

        $result['patient_sex'][1]['value'] = $femalenum;
        $result['patient_sex'][1]['name'] = '女';
        $result['patient_sex'][1]['itemStyle']['color'] = '#E40380';

        $result['patient_sex'][2]['value'] = $none_sex_num;
        $result['patient_sex'][2]['name'] = '未填写';

        //年龄分布
        $result['patient_age'][0]['value'] = $age1_num;
        $result['patient_age'][0]['name'] = '0~20岁';
        $result['patient_age'][0]['itemStyle']['color'] = '#0AA024';

        $result['patient_age'][1]['value'] = $age2_num;
        $result['patient_age'][1]['name'] = '20~40岁';
        $result['patient_age'][1]['itemStyle']['color'] = '#0A8DA0';

        $result['patient_age'][2]['value'] = $age3_num;
        $result['patient_age'][2]['name'] = '40~60岁';
        $result['patient_age'][2]['itemStyle']['color'] = '#9EC4E5';

        $result['patient_age'][3]['value'] = $age4_num;
        $result['patient_age'][3]['name'] = '60岁以上';
        $result['patient_age'][3]['itemStyle']['color'] = '#062E34';

        $result['patient_age'][4]['value'] = $age5_num;
        $result['patient_age'][4]['name'] = '未填写';

        //场所分布
        $result['patient_place'][0]['value'] = $place1_num;
        $result['patient_place'][0]['name'] = '医疗机构';
        $result['patient_place'][0]['itemStyle']['color'] = '#4D67BC';

        $result['patient_place'][1]['value'] = $place2_num;
        $result['patient_place'][1]['name'] = '家庭';
        $result['patient_place'][1]['itemStyle']['color'] = '#0A8DA0';

        $result['patient_place'][2]['value'] = $place3_num;
        $result['patient_place'][2]['name'] = '其他';
        $result['patient_place'][2]['itemStyle']['color'] = '#9EC4E5';

        $result['patient_place'][3]['value'] = $place4_num;
        $result['patient_place'][3]['name'] = '未填写';
        $result['patient_place'][3]['itemStyle']['color'] = '#062E34';

        //事件后果
        $result['event_result'][0]['value'] = $die_num;
        $result['event_result'][0]['name'] = '死亡';

        $result['event_result'][1]['value'] = $jeolife_num;
        $result['event_result'][1]['name'] = '危及生命';

        $result['event_result'][2]['value'] = $res3_num;
        $result['event_result'][2]['name'] = '机体功能结构永久性损伤';

        $result['event_result'][3]['value'] = $resother_num;
        $result['event_result'][3]['name'] = '其他后果';
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
        //设备所属
        foreach ($data['rows'] as $k => $v) {
            $result['inner_out']['type'] = $type;
            $result['inner_out']['xAxis_data'][] = $v['type'];
            $result['inner_out']['series_data'][] = $v['report_num'];
        }
        $from_user = $from_bussniess = $from_factory = $from_none = $malenum = $femalenum = $none_sex_num = 0;
        $age1_num = $age2_num = $age3_num = $age4_num = $age5_num = 0;
        $die_num = $jeolife_num = $res3_num = $resother_num = 0;
        $place1_num = $place2_num = $place3_num = $place4_num = 0;
        foreach ($data['rows'] as $k => $v) {
            $from_user += $v['report_from_user'];
            $from_bussniess += $v['report_from_bussniess'];
            $from_factory += $v['report_from_factory'];
            $from_none += $v['report_from_none'];
            $malenum += $v['male'];
            $femalenum += $v['female'];
            $none_sex_num += $v['none_sex'];
            $age1_num += $v['age1'];
            $age2_num += $v['age2'];
            $age3_num += $v['age3'];
            $age4_num += $v['age4'];
            $age5_num += $v['age5'];
            $place1_num += $v['place1'];
            $place2_num += $v['place2'];
            $place3_num += $v['place3'];
            $place4_num += $v['place4'];
            $die_num += $v['die'];
            $jeolife_num += $v['jeolife'];
            $res3_num += $v['res3'];
            $resother_num += $v['resother'];
        }
        //报告来源
        $result['report_from']['type'] = $type;
        $result['report_from']['xAxis_data'][] = '使用单位';
        $result['report_from']['xAxis_data'][] = '经营企业';
        $result['report_from']['xAxis_data'][] = '生产企业';
        $result['report_from']['xAxis_data'][] = '未填写';
        $result['report_from']['series_data'][] = $from_user;
        $result['report_from']['series_data'][] = $from_bussniess;
        $result['report_from']['series_data'][] = $from_factory;
        $result['report_from']['series_data'][] = $from_none;

        //性别分布
        $result['patient_sex']['type'] = $type;
        $result['patient_sex']['xAxis_data'][] = '男';
        $result['patient_sex']['xAxis_data'][] = '女';
        $result['patient_sex']['xAxis_data'][] = '未填写';
        $result['patient_sex']['series_data'][] = $malenum;
        $result['patient_sex']['series_data'][] = $femalenum;
        $result['patient_sex']['series_data'][] = $none_sex_num;

        //年龄分布
        $result['patient_age']['type'] = $type;
        $result['patient_age']['xAxis_data'][] = '0~20岁';
        $result['patient_age']['xAxis_data'][] = '20~40岁';
        $result['patient_age']['xAxis_data'][] = '40~60岁';
        $result['patient_age']['xAxis_data'][] = '60岁以上';
        $result['patient_age']['xAxis_data'][] = '未填写';
        $result['patient_age']['series_data'][] = $age1_num;
        $result['patient_age']['series_data'][] = $age2_num;
        $result['patient_age']['series_data'][] = $age3_num;
        $result['patient_age']['series_data'][] = $age4_num;
        $result['patient_age']['series_data'][] = $age5_num;

        //场所分布
        $result['patient_place']['type'] = $type;
        $result['patient_place']['xAxis_data'][] = '医疗机构';
        $result['patient_place']['xAxis_data'][] = '家庭';
        $result['patient_place']['xAxis_data'][] = '其他';
        $result['patient_place']['xAxis_data'][] = '未填写';
        $result['patient_place']['series_data'][] = $place1_num;
        $result['patient_place']['series_data'][] = $place2_num;
        $result['patient_place']['series_data'][] = $place3_num;
        $result['patient_place']['series_data'][] = $place4_num;

        //事件后果
        $result['event_result']['type'] = $type;
        $result['event_result']['xAxis_data'][] = '死亡';
        $result['event_result']['xAxis_data'][] = '危及生命';
        $result['event_result']['xAxis_data'][] = '机体功能结构永久性损伤';
        $result['event_result']['xAxis_data'][] = '其他后果';
        $result['event_result']['series_data'][] = $die_num;
        $result['event_result']['series_data'][] = $jeolife_num;
        $result['event_result']['series_data'][] = $res3_num;
        $result['event_result']['series_data'][] = $resother_num;

        return $result;
    }
}
