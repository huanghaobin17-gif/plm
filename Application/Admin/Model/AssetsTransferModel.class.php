<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/6/12
 * Time: 16:19.
 */

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Common\Weixin\Weixin;

class AssetsTransferModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'assets_transfer';
    private $MODULE = 'Assets';
    private $Controller = 'Transfer';

    public function getAssBreakDown($repid)
    {
        return $this->DB_get_one('repair', 'assid,breakdown', ['repid' => $repid], '');
    }

    //转科申请列表
    public function getAssetsLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order');
        $sort = I('POST.sort');
        $assets = I('POST.getListAssets');
        $assetsNum = I('POST.getListAssetsNum');
        $assetsOrnum = I('POST.getListAssetsOrnum');
        $assetsCat = I('POST.getListCategory');
        $assetsDep = I('POST.getListDepartment');
//        $assetsDate = I('POST.getListAddDate');
//        $assetsUser = I('POST.getListAddUser');
        $where['status'][0] = 'IN';
        $where['status'][1][] = C('ASSETS_STATUS_USE');
        $where['status'][1][] = C('ASSETS_STATUS_TRANSFER_ON');
        $where['is_subsidiary'] = C('NO_STATUS');

        if (!session('isSuper')) {
            $departids = session('departid');
            if (!$departids) {
                $result['msg'] = '暂无科室信息';
                $result['code'] = 400;

                return $result;
            }
            $where['departid'][] = ['IN', $departids];
        }
        $where['hospital_id'] = session('current_hospitalid');
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
            $where['assets'] = ['LIKE', '%'.$assets.'%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['assnum'] = ['LIKE', '%'.$assetsNum.'%'];
        }
        if ($assetsOrnum) {
            //资产原编码搜索
            $where['assorignum'] = ['LIKE', '%'.$assetsOrnum.'%'];
        }
        if ($assetsCat) {
            //分类搜索
            $caModel = new CategoryModel();
            $catwhere['category'] = ['LIKE', '%'.$assetsCat.'%'];
            $catids = $caModel->getCatidsBySearch($catwhere);
            $where['catid'] = $catids;
        }
        if ($assetsDep) {
            //部门搜索
            $deModel = new DepartmentModel();
            $dewhere['department'] = ['LIKE', '%'.$assetsDep.'%'];
            $res = $deModel->DB_get_all('department', 'departid', $dewhere, '', 'departid asc', '');
            if ($res) {
                $departids = '';
                foreach ($res as $k => $v) {
                    $departids .= $v['departid'].',';
                }
                $departids = trim($departids, ',');
                $where['departid'][] = ['IN', $departids];
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;

                return $result;
            }
        }
//        if ($assetsDate) {
//            //录入时间搜索
//            $pretime = strtotime($assetsDate) - 1;
//            $nexttime = strtotime($assetsDate) + 24 * 3600;
//            $where['adddate'][] = array('gt', $pretime);
//            $where['adddate'][] = array('lt', $nexttime);
//        }
//        if ($assetsUser != NULL) {
//            //录入人员搜索
//            $where['adduser'] = $assetsUser;
//        }
        $asModel = new AssetsInfoModel();
        $where['is_delete'] = C('NO_STATUS');
        $total = $asModel->DB_get_count('assets_info', $where);
        $fileds = 'assid,serialnum,acid,assets,catid,assnum,assorignum,status,model,departid,storage_date,factorydate,opendate,buy_price,adduser,adddate,quality_in_plan,patrol_in_plan';
        $asArr = $asModel->DB_get_all('assets_info', $fileds, $where, '', $sort.' '.$order, $offset.','.$limit);
        if (!$asArr) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;

            return $result;
        }
        $assid = [];
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($asArr as &$value) {
            $assid[] = $value['assid'];
        }
        //查询转科表中等待重审或重审中的数据
        $trans = $this->DB_get_all('assets_transfer', 'atid,assid,retrial_status', ['retrial_status' => ['in', '1,2']]);
        $transids = $retrial_status = $retrial_atid = [];
        foreach ($trans as $k => $v) {
            $transids[] = $v['assid'];
            $retrial_status[$v['assid']] = $v['retrial_status'];
            $retrial_atid[$v['assid']] = $v['atid'];
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

        //var_dump($borrow);die;
        $departname = [];
        $catname = [];
        include APP_PATH.'Common/cache/category.cache.php';
        include APP_PATH.'Common/cache/department.cache.php';
        //查询当前用户是否有权限申请转科
        $menuData = get_menu($this->MODULE, $this->Controller, 'add');
        foreach ($asArr as $k => $v) {
            $asArr[$k]['opendate'] = HandleEmptyNull($v['opendate']);
            $asArr[$k]['factorydate'] = HandleEmptyNull($v['factorydate']);
            $asArr[$k]['storage_date'] = HandleEmptyNull($v['storage_date']);
            $asArr[$k]['department_name'] = $departname[$v['departid']]['department'];
            $asArr[$k]['cat_name'] = $catname[$v['catid']]['category'];
            $asArr[$k]['buy_price'] = (float) $v['buy_price'];
            if (!$showPrice) {
                $asArr[$k]['buy_price'] = '***';
            }
            if ($v['status'] == C('ASSETS_STATUS_USE')) {
                //在用
                if (in_array($v['assid'], $meterassids)) {
                    //计量计划中
                    $asArr[$k]['operation'] = $this->returnListLink('计量计划中', '', '', C('BTN_CURRENCY').' layui-btn-disabled');
                } elseif ($borrowAssid[$v['assid']]) {
                    $asArr[$k]['as_status'] = '借调中';
                    $asArr[$k]['operation'] = $this->returnListLink('借调中', '', '', C('BTN_CURRENCY').' layui-btn-disabled');
                } else {
                    $asArr[$k]['as_status'] = C('ASSETS_STATUS_USE_NAME');
                    if ($menuData) {
                        $asArr[$k]['operation'] = $this->returnListLink('转科', $menuData['actionurl'], 'add', C('BTN_CURRENCY'));
                    } else {
                        $asArr[$k]['operation'] = $this->returnListLink('转科', '', '', C('BTN_CURRENCY').' layui-btn-disabled');
                    }
                }
            } elseif ($v['status'] == C('ASSETS_STATUS_TRANSFER_ON')) {
                $asArr[$k]['as_status'] = C('ASSETS_STATUS_TRANSFER_ON_NAME');
                if (in_array($v['assid'], $transids)) {
                    //审批不通过，判断重审状态
                    if ($retrial_status[$v['assid']] == 1) {
                        $asArr[$k]['operation'] .= '<div class="layui-btn-group">';
                        $asArr[$k]['operation'] .= $this->returnListLink('申请重审', $menuData['actionurl'].'?atid='.$retrial_atid[$v['assid']], 'add', C('BTN_CURRENCY').' layui-btn-normal');
                        $asArr[$k]['operation'] .= $this->returnListLink('结束进程', $menuData['actionurl'], 'over', C('BTN_CURRENCY').' layui-btn-danger', '', 'data-id='.$retrial_atid[$v['assid']]);
                        $asArr[$k]['operation'] .= '</div>';
                    } else {
                        $asArr[$k]['operation'] = $this->returnListLink('重审中', '', '', C('BTN_CURRENCY').' layui-btn-disabled');
                    }
                } else {
                    $asArr[$k]['operation'] = $this->returnListLink('转科中', '', '', C('BTN_CURRENCY').' layui-btn-disabled');
                }
            }
            if ($v['quality_in_plan'] == C('YES_STATUS')) {
                $asArr[$k]['as_status'] = '质控计划中';
                $asArr[$k]['operation'] = $this->returnListLink('质控中', '', '', C('BTN_CURRENCY').' layui-btn-disabled');
            }
            if ($v['patrol_in_plan'] == C('YES_STATUS')) {
                $asArr[$k]['as_status'] = '巡查计划中';
                $asArr[$k]['operation'] = $this->returnListLink('巡查计划中', '', '', C('BTN_CURRENCY').' layui-btn-disabled');
            }
        }
        $result['total'] = $total;
        $result['offset'] = $offset;
        $result['limit'] = $limit;
        $result['code'] = 200;
        $result['rows'] = $asArr;

        return $result;
    }

    //转科申请操作
    public function addTransfer()
    {
        //var_dump($_POST);die;
        $assids = I('POST.assids');
        $subsidiary_assid = trim(I('POST.subsidiary_assid'));

        if (!$assids) {
            return ['status' => -1, 'msg' => '参数非法!'];
        }
        $assids = explode(',', $assids);

        $asInfo_where['assid'] = ['IN', $assids];
        $asInfo = $this->DB_get_all('assets_info', 'assid,hospital_id,assnum,assets,status,departid,quality_in_plan,patrol_in_plan,buy_price', $asInfo_where, '', 'assid desc');
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        $hospital_id = session('current_hospitalid');
        //筛选计量计划中
        $meteringWhere['status'] = ['EQ', C('YES_STATUS')];

        $where['status'][0] = 'IN';
        $where['status'][1][] = C('ASSETS_STATUS_USE');
        $where['status'][1][] = C('ASSETS_STATUS_TRANSFER_ON');
        $meteringWhere[1][]['assid'][] = ['IN', $assids];
        if ($subsidiary_assid) {
            $meteringWhere[1][]['assid'][] = ['IN', $subsidiary_assid];
            $meteringWhere[1]['_logic'] = 'OR';
        }
        $metering = $this->DB_get_one('metering_plan', 'assid', $meteringWhere);
        if ($metering) {
            die(json_encode(['status' => -1, 'msg' => '转科的设备有启用中的计量计划,请停用或删除后再进行转科']));
        }
        $departname = [];
        include APP_PATH.'Common/cache/department.cache.php';
        foreach ($asInfo as &$assets_value) {
            if (!$showPrice) {
                $assets_value['buy_price'] = '***';
            }
            $assets_value['department'] = $departname[$assets_value['departid']]['department'];
            $hospital_id = $assets_value['hospital_id'];
            switch ($assets_value['status']) {
                case C('ASSETS_STATUS_REPAIR'):
                    die(json_encode(['status' => -1, 'msg' => $assets_value['assets'].' '.C('ASSETS_STATUS_REPAIR_NAME').' 暂不能申请转科!']));
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    die(json_encode(['status' => -1, 'msg' => $assets_value['assets'].' '.C('ASSETS_STATUS_SCRAP_NAME').' 暂不能申请转科!']));
                    break;
                case C('ASSETS_STATUS_OUTSIDE'):
                    die(json_encode(['status' => -1, 'msg' => $assets_value['assets'].' '.C('ASSETS_STATUS_OUTSIDE_NAME').' 暂不能申请转科!']));
                    break;
                case C('ASSETS_STATUS_OUTSIDE_ON'):
                    die(json_encode(['status' => -1, 'msg' => $assets_value['assets'].' '.C('ASSETS_STATUS_OUTSIDE_ON_NAME').' 暂不能申请转科!']));
                    break;
                case C('ASSETS_STATUS_SCRAP_ON'):
                    die(json_encode(['status' => -1, 'msg' => $assets_value['assets'].' '.C('ASSETS_STATUS_SCRAP_ON_NAME').' 暂不能申请转科!']));
                    break;
                case C('ASSETS_STATUS_TRANSFER_ON'):
                    die(json_encode(['status' => -1, 'msg' => $assets_value['assets'].' '.C('ASSETS_STATUS_TRANSFER_ON_NAME').' 暂不能申请转科!']));
                    break;
            }
            if ($assets_value['patrol_in_plan'] == C('YES_STATUS')) {
                die(json_encode(['status' => -1, 'msg' => $assets_value['assets'].' 在巡查计划中 暂不能申请转科!']));
            }
            if ($assets_value['quality_in_plan'] == C('YES_STATUS')) {
                die(json_encode(['status' => -1, 'msg' => $assets_value['assets'].' 在质控计划中 暂不能申请转科!']));
            }
        }

        $addSubsidiaryData = [];
        $subsidiaryData = [];
        if ($subsidiary_assid) {
            $subsidiary_assid = explode(',', $subsidiary_assid);
            $main_assid = explode(',', str_replace('&quot;', '', trim(I('POST.main_assid'))));

            foreach ($subsidiary_assid as $key => $value) {
                //将勾选的附属设备分配至对应的主设备
                $addSubsidiaryData[$main_assid[$key]][] = $value;
            }

            $all_subsidiaryWhere['main_assid'] = ['IN', $assids];
            $all_subsidiaryWhere['status'][0] = 'NOTIN';
            $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_SCRAP'); //报废
            $all_subsidiaryWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE'); //已外调
            $subsidiary_assets = $this->DB_get_all('assets_info', 'buy_price,assid,main_assid', $all_subsidiaryWhere);
            foreach ($subsidiary_assets as &$assetV) {
                if (!$showPrice) {
                    $assetV['buy_price'] = '***';
                }
                $subsidiaryData[$assetV['main_assid']][] = $assetV;
            }
        }

        $departname = [];
        $smsApprover = [];
        include APP_PATH.'Common/cache/department.cache.php';
        $data = [];
        $data['tranout_departid'] = I('POST.traOutId');
        $data['tranout_departrespon'] = $departname[$data['tranout_departid']]['departrespon'];
        $data['tranin_departid'] = I('POST.departid');
        $data['tranin_departrespon'] = $departname[$data['tranin_departid']]['departrespon'];
        $data['transfer_date'] = I('POST.transferdate');
        $data['tran_docnum'] = I('POST.docnum');
        $data['address'] = I('POST.address');
        $data['tran_reason'] = I('POST.tranreason');
        $data['applicant_time'] = date('Y-m-d H:i:s');
        $data['applicant_user'] = session('username');
        $data['transfernum'] = 'zk'.date('YmdHis', time());
        $data['is_check'] = 0; //默认未验收
        if (!$data['tranin_departid']) {
            return ['status' => -1, 'msg' => '请选择转入科室'];
        }
        if ($data['tranout_departid'] == $data['tranin_departid']) {
            return ['status' => -1, 'msg' => '转入科室不能与转出科室相同'];
        }
        if ($data['transfer_date'] < date('Y-m-d')) {
            return ['status' => -1, 'msg' => '转科日期不能小于当前日期!'];
        }
        //判断是否开启微信端
        $moduleModel = new ModuleModel();
        $wx_status = $moduleModel->decide_wx_login();
        $departname = [];
        include APP_PATH.'Common/cache/department.cache.php';
        $settingData = $this->checkSmsIsOpen($this->Controller);
        //查询是否已开启转科审批
        $isOpenApprove = $this->checkApproveIsOpen(C('TRANSFER_APPROVE'), $hospital_id);
        $isSetProcess = $this->checkApproveIsSetProcess(C('TRANSFER_APPROVE'), $hospital_id);
        if (!$isSetProcess && $isOpenApprove) {
            die(json_encode(['status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！']));
        }
        $ToolMod = new ToolController();
        foreach ($asInfo as &$assets_value) {
            $assets_value['tranin_departid'] = $data['tranin_departid'];
            $assets_value['tranout_departid'] = $data['tranout_departid'];
            $all_subsidiary_assid = [];
            $data['order_price'] = $assets_value['buy_price'];
            if ($isOpenApprove) {
                //已开启转科审批
                if ($addSubsidiaryData[$assets_value['assid']]) {
                    foreach ($subsidiaryData[$assets_value['assid']] as &$sub_asstes) {
                        $all_subsidiary_assid[] = $sub_asstes['assid'];
                        if (in_array($sub_asstes['assid'], $addSubsidiaryData[$assets_value['assid']])) {
                            $data['order_price'] += $sub_asstes['buy_price'];
                        }
                    }
                }
                $approve_process_user = $this->get_approve_process($data['order_price'], C('TRANSFER_APPROVE'), $assets_value['hospital_id']);
                //并且获取下次审批人
                $approve = $this->check_approve_process_transfer($assets_value, $approve_process_user, 1);
                $smsApprover['username'] = $approve['this_current_approver'];
                if ($approve['all_approver'] == '') {
                    //不在审核范围内 不需要审批
                    $data['approve_status'] = C('STATUS_APPROE_UNWANTED');
                } else {
                    //默认为未审核
                    $data['current_approver'] = $approve['current_approver'];
                    $data['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                    $data['all_approver'] = $approve['all_approver'];
                    $data['approve_status'] = C('APPROVE_STATUS');
                }
            } else {
                $data['approve_status'] = C('STATUS_APPROE_UNWANTED');
            }
            $data['assid'] = $assets_value['assid'];
            $insertId = $this->insertData('assets_transfer', $data);
            if ($insertId) {
                //记录辅助设备
                $update_assid_arr = [];
                if ($addSubsidiaryData[$assets_value['assid']]) {
                    $addData = [];
                    for ($i = 0; $i < count($addSubsidiaryData[$assets_value['assid']]); ++$i) {
                        $update_assid_arr[] = $addSubsidiaryData[$assets_value['assid']][$i];
                        $addData[$i]['atid'] = $insertId;
                        $addData[$i]['subsidiary_assid'] = $addSubsidiaryData[$assets_value['assid']][$i];
                    }
                    $this->insertDataALL('assets_transfer_detail', $addData);
                }
                //设备状态变更为转科中
                $update_assid_arr[] = $assets_value['assid'];
                $assetaData['status'] = C('ASSETS_STATUS_TRANSFER_ON');
                $this->updateData('assets_info', $assetaData, ['assid' => ['IN', $update_assid_arr]]);
                $this->updateAllAssetsStatus($update_assid_arr, $assetaData['status'], '设备转科申请');

                $log['assnum'] = $assets_value['assnum'];
                $text = getLogText('addTransferLogText', $log);

                $this->addLog('assets_transfer', M()->getLastSql(), $text, $insertId);
                //==========================================短信 START==========================================

                $tmp_Info = $this->DB_get_one('assets_info', 'assnum,assets', ['assid' => $data['assid']]);
                $smsData['tranout_department'] = $departname[$data['tranout_departid']]['department'];
                $smsData['tranin_department'] = $departname[$data['tranin_departid']]['department'];

                $telephone = $UserData = [];
                if ($isOpenApprove) {
                    //审批人
                    $telephone = $this->DB_get_one('user', 'telephone,openid', ['username' => $smsApprover['username']]);
                } else {
                    //验收人
                    $UserData = $ToolMod->getUser('check', $data['tranin_departid']);
                }
                if ($settingData) {
                    //有开启短信
                    $smsData['assnum'] = $tmp_Info['assnum'];
                    $smsData['assets'] = $tmp_Info['assets'];
                    $smsData['transfer_num'] = $data['transfernum'];
                    if ($isOpenApprove) {
                        if ($settingData['approveTransfer']['status'] == C('OPEN_STATUS') && $telephone) {
                            //开启了审批 通知审批人
                            $sms = $this->formatSmsContent($settingData['approveTransfer']['content'], $smsData);
                            $ToolMod->sendingSMS($telephone['telephone'], $sms, $this->Controller, $insertId);
                        }
                    } else {
                        //没有开启审批 通知验收人
                        if ($settingData['checkTransfer']['status'] == C('OPEN_STATUS') && $UserData) {
                            //通知转入科室验收 开启
                            $phone = $this->formatPhone($UserData);
                            $sms = $this->formatSmsContent($settingData['checkTransfer']['content'], $data);
                            $ToolMod->sendingSMS($phone, $sms, $this->Controller, $insertId);
                        }
                    }
                }
                //==========================================短信 END==========================================

                if (C('USE_FEISHU') === 1) {
                    if ($isOpenApprove) {
                        //开启了审批 通知审批人
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**'.$assets_value['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**'.$assets_value['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转出科室：**'.$assets_value['backDepart'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转入科室：**'.$smsData['tranin_department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转科原因：**'.$data['tran_reason'];
                        $feishu_fields[] = $fd;

                        //按钮区域
                        $act['tag'] = 'button';
                        $act['type'] = 'primary';
                        $act['url'] = C('APP_NAME').C('FS_FOLDER_NAME').'/#'.C('FS_NAME').'/Transfer/approval?atid='.$insertId;
                        $act['text']['tag'] = 'plain_text';
                        $act['text']['content'] = '审批';
                        $feishu_actions[] = $act;

                        $card_data['config']['enable_forward'] = false; //是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true; //是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['elements'][1]['tag'] = 'hr';
                        $card_data['elements'][2]['actions'] = $feishu_actions;
                        $card_data['elements'][2]['layout'] = 'bisected';
                        $card_data['elements'][2]['tag'] = 'action';
                        $card_data['header']['template'] = 'blue';
                        $card_data['header']['title']['content'] = '设备转科审批提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        $this->send_feishu_card_msg($telephone['openid'], $card_data);
                    //==========================================飞书 END==========================================
                    } else {
                        //没有开启审批 通知转入科室验收
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**'.$assets_value['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**'.$assets_value['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转出科室：**'.$smsData['tranout_department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转入科室：**'.$smsData['tranin_department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转科原因：**'.$data['tran_reason'];
                        $feishu_fields[] = $fd;

                        //按钮区域
                        $act['tag'] = 'button';
                        $act['type'] = 'primary';
                        $act['url'] = C('APP_NAME').C('FS_FOLDER_NAME').'/#'.C('FS_NAME').'/Transfer/check?atid='.$insertId;
                        $act['text']['tag'] = 'plain_text';
                        $act['text']['content'] = '验收';
                        $feishu_actions[] = $act;

                        $card_data['config']['enable_forward'] = false; //是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true; //是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['elements'][1]['tag'] = 'hr';
                        $card_data['elements'][2]['actions'] = $feishu_actions;
                        $card_data['elements'][2]['layout'] = 'bisected';
                        $card_data['elements'][2]['tag'] = 'action';
                        $card_data['header']['template'] = 'blue';
                        $card_data['header']['title']['content'] = '设备转科验收提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        $toUser = $this->getToUser(session('userid'), $data['tranin_departid'], 'Assets', 'Transfer', 'check');
                        foreach ($toUser as $k => $v) {
                            $this->send_feishu_card_msg($v['openid'], $card_data);
                        }
                        //==========================================飞书 END==========================================
                    }
                } else {
                    //==========================================微信通知 START=====================================
                    if ($wx_status) {
                        if ($isOpenApprove) {
                            //开启了审批 通知审批人
                            if ($telephone['openid']) {
                                if (C('USE_VUE_WECHAT_VERSION')) {
                                    $redecturl = C('APP_NAME').C('VUE_FOLDER_NAME').'/#'.C('VUE_NAME').'/Transfer/approval?atid='.$insertId;
                                } else {
                                    $redecturl = C('HTTP_HOST').C('MOBILE_NAME').'/Transfer/approval.html?atid='.$insertId;
                                }

                                Weixin::instance()->sendMessage($telephone['openid'], '设备转科待审批提醒', [
                                    'thing1' => $smsData['tranout_department'], // 转出科室
                                    'thing2' => $smsData['tranin_department'], // 转入科室
                                    'thing3' => $assets_value['assets'], // 设备名称
                                    'character_string4' => $assets_value['assnum'], // 设备编码
                                    'character_string5' => $data['transfernum'], // 转科单号
                                ], $redecturl);
                            }
                        } else {
                            //没有开启审批 通知转入科室验收
                            if (C('USE_VUE_WECHAT_VERSION')) {
                                $redecturl = C('APP_NAME').C('VUE_FOLDER_NAME').'/#'.C('VUE_NAME').'/Transfer/check?atid='.$insertId;
                            } else {
                                $redecturl = C('HTTP_HOST').C('MOBILE_NAME').'/Transfer/check.html?atid='.$insertId;
                            }

                            /** @var UserModel[] $users */
                            $users = $this->getToUser(session('userid'), $data['tranin_departid'], 'Assets', 'Transfer', 'check');
                            $openIds = array_column($users, 'openid');
                            $openIds = array_filter($openIds);
                            $openIds = array_unique($openIds);

                            $messageData = [
                                'thing3' => $smsData['tranout_department'], // 所属科室
                                'thing1' => $assets_value['assets'], // 设备名称
                                'const13' => '转科', // 设备来源
                                'character_string11' => $data['transfernum'], // 订单编号
                                'const7' => '', // 处理结果
                            ];

                            foreach ($openIds as $openId) {
                                Weixin::instance()->sendMessage($openId, '设备验收通知', $messageData, $redecturl);
                            }
                        }
                    }
                    //==========================================微信通知 END=======================================
                }
            }
        }

        return ['status' => 1, 'msg' => '提交申请成功!'];
    }

    //提交重审
    public function updateTransfer()
    {
        $data['tran_docnum'] = I('post.docnum');
        $data['tran_reason'] = I('post.tranreason');
        $data['retrial_status'] = 2; //重审中
        $data['update_user'] = session('username');
        $data['update_time'] = date('Y-m-d H:i:s');
        $atid = I('POST.atid');
        $transInfo = $this->DB_get_one('assets_transfer', 'atid,assid,order_price,transfernum,tranin_departid,tranout_departid', ['atid' => $atid]);
        $hospital_id = session('current_hospitalid');
        //查询是否已开启转科审批
        $isOpenApprove = $this->checkApproveIsOpen(C('TRANSFER_APPROVE'), $hospital_id);
        $isSetProcess = $this->checkApproveIsSetProcess(C('TRANSFER_APPROVE'), $hospital_id);
        if (!$isSetProcess) {
            die(json_encode(['status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！']));
        }
        if ($isOpenApprove) {
            //已开启转科审批
            $approve_process_user = $this->get_approve_process($transInfo['order_price'], C('TRANSFER_APPROVE'), $hospital_id);
            //并且获取下次审批人
            $approve = $this->check_approve_process_transfer($transInfo, $approve_process_user, 1);
            $smsApprover['username'] = $approve['this_current_approver'];
            if ($approve['all_approver'] == '') {
                //不在审核范围内 不需要审批
                $data['approve_status'] = C('STATUS_APPROE_UNWANTED');
            } else {
                //默认为未审核
                $data['current_approver'] = $approve['current_approver'];
                $data['not_complete_approver'] = str_replace('/', '', $approve['all_approver']);
                $data['all_approver'] = $approve['all_approver'];
                $data['approve_status'] = C('APPROVE_STATUS');
            }
        } else {
            $data['approve_status'] = C('STATUS_APPROE_UNWANTED');
        }
        $data['complete_approver'] = '';
        //修改转科单信息
        $res = $this->updateData('assets_transfer', $data, ['atid' => $atid]);
        if (!$res) {
            return ['status' => -1, 'msg' => '提交重审申请失败!'];
        }
        //删除审批记录
        $this->updateData('approve', ['is_delete' => 1], ['atid' => $atid]);
        //判断是否开启微信端
        $moduleModel = new ModuleModel();
        $wx_status = $moduleModel->decide_wx_login();
        //==========================================短信 START==========================================
        $settingData = $this->checkSmsIsOpen($this->Controller);
        $departname = [];
        include APP_PATH.'Common/cache/department.cache.php';
        $asInfo = $this->DB_get_one('assets_info', 'assnum,assets', ['assid' => $transInfo['assid']]);

        $data['assets'] = $asInfo['assets'];
        $data['assnum'] = $asInfo['assnum'];
        $data['tranout_department'] = $departname[$transInfo['tranout_department']]['department'];
        $data['tranin_department'] = $departname[$transInfo['tranin_department']]['department'];
        $data['transfer_num'] = $transInfo['transfernum'];
        $data['check_status'] = 0;
        $smsData['tranout_department'] = $departname[$transInfo['tranout_departid']]['department'];
        $smsData['tranin_department'] = $departname[$transInfo['tranin_departid']]['department'];
        $ToolMod = new ToolController();
        $telephone = $UserData = [];
        if ($isOpenApprove) {
            //审批人
            $telephone = $this->DB_get_one('user', 'telephone,openid', ['username' => $smsApprover['username']]);
        } else {
            //验收人
            $UserData = $ToolMod->getUser('check', $transInfo['tranin_departid']);
        }
        if ($settingData) {
            //有开启短信
            $smsData['assnum'] = $asInfo['assnum'];
            $smsData['assets'] = $asInfo['assets'];
            $smsData['transfer_num'] = $transInfo['transfernum'];
            if ($isOpenApprove) {
                if ($settingData['approveTransfer']['status'] == C('OPEN_STATUS') && $telephone) {
                    //开启了审批 通知审批人
                    $sms = $this->formatSmsContent($settingData['approveTransfer']['content'], $smsData);
                    $ToolMod->sendingSMS($telephone['telephone'], $sms, $this->Controller, $transInfo['atid']);
                }
            } else {
                //没有开启审批 通知验收人
                if ($settingData['checkTransfer']['status'] == C('OPEN_STATUS') && $UserData) {
                    //通知转入科室验收 开启
                    $phone = $this->formatPhone($UserData);
                    $sms = $this->formatSmsContent($settingData['checkTransfer']['content'], $data);
                    $ToolMod->sendingSMS($phone, $sms, $this->Controller, $transInfo['atid']);
                }
            }
        }
        //==========================================短信 END==========================================

        if (C('USE_FEISHU') === 1) {
            if ($isOpenApprove) {
                //开启了审批 通知审批人
                if ($telephone['openid']) {
                    //==========================================飞书 START========================================
                    //要显示的字段区域
                    $fd['is_short'] = false; //是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备名称：**'.$asInfo['assets'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false; //是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备编码：**'.$asInfo['assnum'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false; //是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**转出科室：**'.$smsData['tranout_department'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false; //是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**转入科室：**'.$smsData['tranin_department'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false; //是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**转科原因：**'.$data['tran_reason'];
                    $feishu_fields[] = $fd;

                    //按钮区域
                    $act['tag'] = 'button';
                    $act['type'] = 'primary';
                    $act['url'] = C('APP_NAME').C('FS_FOLDER_NAME').'/#'.C('FS_NAME').'/Transfer/approval?atid='.$transInfo['atid'];
                    $act['text']['tag'] = 'plain_text';
                    $act['text']['content'] = '审批';
                    $feishu_actions[] = $act;

                    $card_data['config']['enable_forward'] = false; //是否允许卡片被转发
                    $card_data['config']['wide_screen_mode'] = true; //是否根据屏幕宽度动态调整消息卡片宽度
                    $card_data['elements'][0]['tag'] = 'div';
                    $card_data['elements'][0]['fields'] = $feishu_fields;
                    $card_data['elements'][1]['tag'] = 'hr';
                    $card_data['elements'][2]['actions'] = $feishu_actions;
                    $card_data['elements'][2]['layout'] = 'bisected';
                    $card_data['elements'][2]['tag'] = 'action';
                    $card_data['header']['template'] = 'blue';
                    $card_data['header']['title']['content'] = '设备转科审批提醒(重审申请)';
                    $card_data['header']['title']['tag'] = 'plain_text';

                    $this->send_feishu_card_msg($telephone['openid'], $card_data);
                    //==========================================飞书 END==========================================
                }
            } else {
                //没有开启审批 通知转入科室验收
                //==========================================飞书 START========================================
                //要显示的字段区域
                $fd['is_short'] = false; //是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**设备名称：**'.$asInfo['assets'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false; //是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**设备编码：**'.$asInfo['assnum'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false; //是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**转出科室：**'.$smsData['tranout_department'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false; //是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**转入科室：**'.$smsData['tranin_department'];
                $feishu_fields[] = $fd;

                $fd['is_short'] = false; //是否并排布局
                $fd['text']['tag'] = 'lark_md';
                $fd['text']['content'] = '**转科原因：**'.$data['tran_reason'];
                $feishu_fields[] = $fd;

                //按钮区域
                $act['tag'] = 'button';
                $act['type'] = 'primary';
                $act['url'] = C('APP_NAME').C('FS_FOLDER_NAME').'/#'.C('FS_NAME').'/Transfer/check?atid='.$transInfo['atid'];
                $act['text']['tag'] = 'plain_text';
                $act['text']['content'] = '验收';
                $feishu_actions[] = $act;

                $card_data['config']['enable_forward'] = false; //是否允许卡片被转发
                $card_data['config']['wide_screen_mode'] = true; //是否根据屏幕宽度动态调整消息卡片宽度
                $card_data['elements'][0]['tag'] = 'div';
                $card_data['elements'][0]['fields'] = $feishu_fields;
                $card_data['elements'][1]['tag'] = 'hr';
                $card_data['elements'][2]['actions'] = $feishu_actions;
                $card_data['elements'][2]['layout'] = 'bisected';
                $card_data['elements'][2]['tag'] = 'action';
                $card_data['header']['template'] = 'blue';
                $card_data['header']['title']['content'] = '设备转科验收提醒';
                $card_data['header']['title']['tag'] = 'plain_text';

                $toUser = $this->getToUser(session('userid'), $data['tranin_departid'], 'Assets', 'Transfer', 'check');
                foreach ($toUser as $k => $v) {
                    $this->send_feishu_card_msg($v['openid'], $card_data);
                }
                //==========================================飞书 END==========================================
            }
        } else {
            //==========================================微信通知 START=====================================
            if ($isOpenApprove) {
                //开启了审批 通知审批人
                if ($wx_status && $telephone['openid']) {
                    if (C('USE_VUE_WECHAT_VERSION')) {
                        $redecturl = C('APP_NAME').C('VUE_FOLDER_NAME').'/#'.C('VUE_NAME').'/Transfer/approval?atid='.$transInfo['atid'];
                    } else {
                        $redecturl = C('HTTP_HOST').C('MOBILE_NAME').'/Transfer/approval.html?atid='.$transInfo['atid'];
                    }

                    Weixin::instance()->sendMessage($telephone['openid'], '设备验收通知', [
                        'thing3' => $smsData['tranout_department'], // 所属科室
                        'thing1' => $asInfo['assets'], // 设备名称
                        'const13' => '转科', // 设备来源
                        'character_string11' => $transInfo['transfernum'], // 订单编号
                        'const7' => '', // 处理结果
                    ], $redecturl);
                }
            } else {
                //没有开启审批 通知转入科室验收
                if (C('USE_VUE_WECHAT_VERSION')) {
                    $redecturl = C('APP_NAME').C('VUE_FOLDER_NAME').'/#'.C('VUE_NAME').'/Transfer/check?atid='.$transInfo['atid'];
                } else {
                    $redecturl = C('HTTP_HOST').C('MOBILE_NAME').'/Transfer/check.html?atid='.$transInfo['atid'];
                }

                /** @var UserModel[] $users */
                $users = $this->getToUser(session('userid'), $data['tranin_departid'], 'Assets', 'Transfer', 'check');
                $openIds = array_column($users, 'openid');
                $openIds = array_filter($openIds);
                $openIds = array_unique($openIds);

                $messageData = [
                    'thing3' => $smsData['tranout_department'], // 所属科室
                    'thing1' => $asInfo['assets'], // 设备名称
                    'const13' => '转科', // 设备来源
                    'character_string11' => $transInfo['transfernum'], // 订单编号
                    'const7' => '', // 处理结果
                ];

                foreach ($openIds as $openId) {
                    Weixin::instance()->sendMessage($openId, '设备验收通知', $messageData, $redecturl);
                }
            }
            //==========================================微信通知 END=======================================
        }

        return ['status' => 1, 'msg' => '提交重审成功!'];
    }

    //结束进程
    public function endTransfer()
    {
        $atid = I('post.atid');
        $transInfo = $this->DB_get_one('assets_transfer', 'atid,assid', ['atid' => $atid]);
        if (!$transInfo) {
            return ['status' => -1, 'msg' => '查找不到该转科单！'];
        }
        $this->updateData('assets_transfer', ['retrial_status' => 3], ['atid' => $atid]);
        $this->updateData('assets_info', ['status' => 0], ['assid' => $transInfo['assid']]);

        return ['status' => 1, 'msg' => '操作成功！'];
    }

    //获取审批列表
    public function getExamines()
    {
        $departid = session('departid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'atid';
        $assets = I('POST.examineAssets');
        $assetsNum = I('POST.examineAssetsNum');
        $assetsDepIn = I('POST.examineAssetsDepartmentIn');
        $assetsDepOut = I('POST.examineAssetsDepartmentOut');
        $transferdate = I('POST.examineTransferdate');
        $applicantdate = I('POST.examineApplicantdate');
        $assetsUser = I('POST.examineAssetsUser');
        $hospital_id = session('job_hospitalid');
        $examineStatus = I('POST.examineStatus');
        $checkStatus = I('POST.checkStatus');
        $where = ' B.hospital_id = '.$hospital_id;
        if (!session('isSuper')) {
            if (!$departid) {
                $result['msg'] = '无分配科室请联系管理员';
                $result['code'] = 400;

                return $result;
            } else {
                $where = ' B.status != 2 ';
            }
        }
        if ($examineStatus != null) {
            //审核状态搜索
            $where .= " and A.approve_status = '".$examineStatus."'";
        } else {
            $where .= ' AND A.approve_status <> '.C('STATUS_APPROE_UNWANTED');
        }
        if ($checkStatus != null) {
            //验收状态搜索
            $where .= " and A.is_check = '".$checkStatus."'";
        }
        if ($assets) {
            //设备名称搜索
            $where .= " and B.assets like '%".$assets."%'";
        }
        if ($assetsNum) {
            //资产编码搜索
            $where .= " and B.assnum like '%".$assetsNum."%'";
        }
        if ($assetsDepIn) {
            //转入部门搜索
            $deinwhere['department'] = ['like', "%$assetsDepIn%"];
            $res = $this->DB_get_one('department', 'group_concat(departid) as departid', $deinwhere);
            if ($res['departid']) {
                $where .= ' and A.tranin_departid in ('.$res['departid'].')';
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;

                return $result;
            }
        }
        if ($assetsDepOut) {
            //转出部门搜索
            $deoutwhere['department'] = ['like', "%$assetsDepOut%"];
            $res = $this->DB_get_one('department', 'group_concat(departid) as departid', $deoutwhere);
            if ($res['departid']) {
                $where .= ' and A.tranout_departid in ('.$res['departid'].')';
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;

                return $result;
            }
        }
        if ($transferdate) {
            //转科时间搜索
            $where .= " and A.transfer_date = '".$transferdate."'";
        }
        if ($applicantdate) {
            //申请时间搜索
            $pretime = getHandleDate(strtotime($applicantdate) - 1);
            $nexttime = getHandleDate(strtotime($applicantdate) + 24 * 3600);
            $where .= " and A.applicant_time > '".$pretime."' and A.applicant_time < '".$nexttime." ' ";
        }
        if ($assetsUser != null) {
            //申请人员搜索
            $where .= " and A.applicant_user = '".$assetsUser."'";
        }
        $where .= " AND A.all_approver LIKE '%/".session('username')."/%'";

        //根据条件统计符合要求的数量
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $total = $this->DB_get_count_join('assets_transfer', 'A', $join, $where);
        $fields = 'A.*,B.assnum,B.assorignum,B.assets,B.model,B.catid,B.buy_price';
        $asArr = $this->DB_get_all_join('assets_transfer', 'A', $fields, $join, $where, '', $sort.' '.$order, $offset.','.$limit);
        //查询当前用户是否有权审批
        $canApproval = get_menu('Assets', 'Transfer', 'approval');
        //查询当前用户是否有权验收
        $canAcceptance = get_menu('Assets', 'Transfer', 'check');
        $departname = [];
        include APP_PATH.'Common/cache/department.cache.php';
        foreach ($asArr as $k => $v) {
            $asArr[$k]['tranout_depart_name'] = $departname[$v['tranout_departid']]['department'];
            $asArr[$k]['tranin_depart_name'] = $departname[$v['tranin_departid']]['department'];
            $asArr[$k]['applicant_time'] = getHandleTime(strtotime($v['applicant_time']));
            $asArr[$k]['approve_status'] = (int) $v['approve_status'];
            $asArr[$k]['checkStatus'] = (int) $v['is_check'];
        }
        foreach ($asArr as $k => $v) {
            $html = '<div class="layui-btn-group">';
            $html .= $this->returnListLink('设备详情', C('SHOWASSETS_ACTION_URL'), 'showAssets', C('BTN_CURRENCY').' layui-btn-primary');
            $detailsUrl = get_url().'?action=showDetails&transNum='.$v['transfernum'].'&assid='.$v['assid'];
            if ($v['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                //审核通过
                if ($v['is_check'] == C('TRANSFER_IS_NOTCHECK')) {
                    //未验收
                    if ($canAcceptance) {
                        $html .= $this->returnListLink($canAcceptance['actionname'], $canAcceptance['actionurl'], 'acceptance', C('BTN_CURRENCY').' layui-btn-normal');
                    } else {
                        $html .= $this->returnListLink('查看', $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-normal');
                    }
                } elseif ($v['is_check'] == C('TRANSFER_IS_CHECK_ADOPT') or $v['is_check'] == C('TRANSFER_IS_CHECK_NOT_THROUGH')) {
                    //已验收通过//不通过
                    $html .= $this->returnListLink('查看', $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-normal');
                }
            } elseif ($v['approve_status'] == C('STATUS_APPROE_FAIL')) {
                $html .= $this->returnListLink('不通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-danger');
            } else {
                if ($v['current_approver']) {
                    $current_approver = explode(',', $v['current_approver']);
                    $current_approver_arr = [];
                    foreach ($current_approver as &$current_approver_value) {
                        $current_approver_arr[$current_approver_value] = true;
                    }
                    if ($current_approver_arr[session('username')]) {
                        if ($canApproval) {
                            $html .= $this->returnListLink('审核', $canApproval['actionurl'], 'approval', C('BTN_CURRENCY'));
                        } else {
                            $html .= $this->returnListLink('待审批', $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-warm');
                        }
                    } else {
                        $complete = explode(',', $v['complete_approver']);
                        $notcomplete = explode(',', $v['not_complete_approver']);
                        if (!in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                            //完全未审
                            $html .= $this->returnListLink('待审核', $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && in_array(session('username'), $notcomplete)) {
                            //有已审，有未审
                            $html .= $this->returnListLink('待审核', $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-warm');
                        } elseif (in_array(session('username'), $complete) && !in_array(session('username'), $notcomplete)) {
                            //全部已审
                            $html .= $this->returnListLink('已审核', $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-primary');
                        } else {
                            $html .= '';
                        }
                    }
                }
            }
            $html .= '</div>';
            $asArr[$k]['operation'] = $html;
        }
        $result['total'] = $total;
        $result['offset'] = $offset;
        $result['limit'] = $limit;
        $result['code'] = 200;
        $result['rows'] = $asArr;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }

        return $result;
    }

    //保存审核结果
    public function saveApprove()
    {
        $transfernum = I('POST.transnum');
        $assid = I('POST.assid');
        $asModel = new AssetsInfoModel();
        $transfer = $asModel->DB_get_one('assets_transfer', '', ['transfernum' => $transfernum, 'assid' => $assid]);
        if (!$transfer) {
            return ['status' => -1, 'msg' => '查找不到转科设备信息'];
        } else {
            if ($transfer['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                return ['status' => -1, 'msg' => '审批已通过，请勿重复提交！'];
            } elseif ($transfer['approve_status'] == C('STATUS_APPROE_FAIL')) {
                return ['status' => -1, 'msg' => '审批已否决，请勿重复提交！'];
            }
        }
        //判断是否是当前审批人
        if ($transfer['current_approver']) {
            $current_approver = explode(',', $transfer['current_approver']);
            $current_approver_arr = [];
            foreach ($current_approver as &$current_approver_value) {
                $current_approver_arr[$current_approver_value] = true;
            }
            if ($current_approver_arr[session('username')]) {
                $processWhere['atid'] = ['EQ', $transfer['atid']];
                $processWhere['is_delete'] = ['NEQ', C('YES_STATUS')];
                $process = $this->DB_get_count('approve', $processWhere);
                $level = $process + 1;

                return $this->addApprove_transfer($level, $transfer);
            } else {
                return ['status' => -1, 'msg' => '请等待审批！'];
            }
        } else {
            return ['status' => -1, 'msg' => '审核已结束！'];
        }
    }

    //获取验收列表
    public function getCheckLists()
    {
        $departid = session('departid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'atid';
        $assets = I('POST.examineAssets');
        $assetsNum = I('POST.examineAssetsNum');
        $assetsDepIn = I('POST.examineAssetsDepartmentIn');
        $assetsDepOut = I('POST.examineAssetsDepartmentOut');
        $transferdate = I('POST.examineTransferdate');
        $applicantdate = I('POST.examineApplicantdate');
        $assetsUser = I('POST.examineAssetsUser');
        $checkStatus = I('POST.checkStatus');
        $hospital_id = session('job_hospitalid');
        if (!session('isSuper')) {
            if (!$departid) {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;

                return $result;
            } else {
                $where['B.status'] = ['NEQ', C('ASSETS_STATUS_SCRAP')];
                $where['A.tranin_departid'] = ['in', $departid];
            }
        } else {
            $where['B.status'] = ['NEQ', C('ASSETS_STATUS_SCRAP')];
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = ['EQ', $hospital_id];
        } else {
            $where['B.hospital_id'] = session('current_hospitalid');
        }
        if ($assets) {
            //设备名称搜索
            $where['B.assets'] = ['LIKE', '%'.$assets.'%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['B.assnum'] = ['LIKE', '%'.$assetsNum.'%'];
        }
        if ($assetsDepIn) {
            //转入部门搜索
            $deinwhere['department'] = ['like', "%$assetsDepIn%"];
            $res = $this->DB_get_one('department', 'group_concat(departid) as departid', $deinwhere);
            if ($res['departid']) {
                $where['A.tranin_departid'] = ['IN', $res['departid']];
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;

                return $result;
            }
        }
        if ($assetsDepOut) {
            //转出部门搜索
            $deoutwhere['department'] = ['like', "%$assetsDepOut%"];
            $res = $this->DB_get_one('department', 'group_concat(departid) as departid', $deoutwhere);
            if ($res['departid']) {
                $where['A.tranout_departid'] = ['IN', $res['departid']];
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;

                return $result;
            }
        }
        if ($transferdate) {
            //转科时间搜索
            $where['A.transfer_date'] = ['EQ', $transferdate];
        }
        if ($applicantdate) {
            //申请时间搜索
            $pretime = getHandleDate(strtotime($applicantdate) - 1);
            $nexttime = getHandleDate(strtotime($applicantdate) + 24 * 3600);
            $where['A.applicant_time'] = [['GT', $pretime], ['LT', $nexttime], 'and'];
        }
        if ($assetsUser != null) {
            //申请人员搜索
            $where['A.applicant_user'] = ['EQ', $assetsUser];
        }
        if ($checkStatus != null) {
            //审核状态搜索
            $where['A.is_check'] = ['EQ', $checkStatus];
        }
        $where['A.approve_status'][0] = 'IN';
        $where['A.approve_status'][1][] = C('STATUS_APPROE_UNWANTED');
        $where['A.approve_status'][1][] = C('STATUS_APPROE_SUCCESS');
        $where['A.approve_status'][1][] = C('STATUS_APPROE_FAIL');
        //根据条件统计符合要求的数量
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $total = $this->DB_get_count_join('assets_transfer', 'A', $join, $where);
        $fields = 'A.*,B.assnum,B.assorignum,B.assets,B.model,B.catid,B.buy_price';
        $asArr = $this->DB_get_all_join('assets_transfer', 'A', $fields, $join, $where, '', $sort.' '.$order, $limit);
        if (!$asArr) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;

            return $result;
        }
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        //查询当前用户是否有权验收
        $canCheck = get_menu('Assets', 'Transfer', 'check');
        //查询当前用户是否有权查看验收列表
        $checkList = get_menu('Assets', 'Transfer', 'checkLists');
        $departname = [];
        include APP_PATH.'Common/cache/department.cache.php';
        $bot_span = '<span style="color:#FFB800;">';
        $success_span = '<span style="color:green;">';
        $fail_span = '<span style="color:red;">';
        //查询是否开启了转科审批
        foreach ($asArr as $k => $v) {
            if (!$showPrice) {
                $asArr[$k]['buy_price'] = '***';
            }
            $asArr[$k]['tranout_depart_name'] = $departname[$v['tranout_departid']]['department'];
            $asArr[$k]['tranin_depart_name'] = $departname[$v['tranin_departid']]['department'];
            $asArr[$k]['applicant_time'] = getHandleTime(strtotime($v['applicant_time']));
            if ($v['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                //审批通过
                $asArr[$k]['approve_status_name'] = $success_span.'通过</span>';
            } elseif ($v['approve_status'] == C('APPROVE_STATUS')) {
                //审批中
                if ($v['complete_approver']) {
                    $asArr[$k]['approve_status_name'] = $bot_span.'审批中</span>';
                } else {
                    $asArr[$k]['approve_status_name'] = $bot_span.'待审批</span>';
                }
            } elseif ($v['approve_status'] == C('STATUS_APPROE_FAIL')) {
                //审批不通过
                $asArr[$k]['approve_status_name'] = $fail_span.'不通过</span>';
            } elseif ($v['approve_status'] == C('STATUS_APPROE_UNWANTED')) {
                //不需要审批
                $asArr[$k]['approve_status_name'] = '不需审批';
            }
            if ($v['is_check'] == C('TRANSFER_IS_CHECK_ADOPT')) {
                //已验收
                $asArr[$k]['is_check_name'] = $success_span.C('TRANSFER_IS_CHECK_ADOPT_NAME').'</span>';
            } elseif ($v['is_check'] == C('TRANSFER_IS_NOTCHECK')) {
                //待验收
                $asArr[$k]['is_check_name'] = $bot_span.C('TRANSFER_IS_NOTCHECK_NAME').'</span>';
            } elseif ($v['is_check'] == C('TRANSFER_IS_CHECK_NOT_THROUGH')) {
                //验收失败
                $asArr[$k]['is_check_name'] = $fail_span.C('TRANSFER_IS_CHECK_NOT_THROUGH_NAME').'</span>';
            }

            $detailsUrl = get_url().'?action=showDetails&transNum='.$v['transfernum'].'&assid='.$v['assid'];
            $printReportUrl = get_url().'?action=printReport&atid='.$v['atid'];
            $uploadReportUrl = get_url().'?action=uploadReport&atid='.$v['atid'];
            $html = '<div class="layui-btn-group">';
            $html .= $this->returnListLink('设备详情', C('SHOWASSETS_ACTION_URL'), 'showAssets', C('BTN_CURRENCY').' layui-btn-primary');
            if ($v['approve_status'] == C('STATUS_APPROE_SUCCESS') or $v['approve_status'] == C('STATUS_APPROE_UNWANTED')) {
                //审批通过 / 不需要审批
                if ($v['is_check'] == C('TRANSFER_IS_NOTCHECK')) {
                    if ($canCheck) {
                        $html .= $this->returnListLink($canCheck['actionname'], $canCheck['actionurl'], 'check', C('BTN_CURRENCY'));
                    } else {
                        $html .= $this->returnListLink(C('TRANSFER_IS_NOTCHECK_NAME'), $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-warm');
                    }
                } elseif ($v['is_check'] == C('TRANSFER_IS_CHECK_NOT_THROUGH')) {
                    //验收不通过
                    $html .= $this->returnListLink(C('TRANSFER_IS_CHECK_NOT_THROUGH_NAME'), $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-danger');
                } elseif ($v['is_check'] == C('TRANSFER_IS_CHECK_ADOPT')) {
                    //验收通过
                    $html .= $this->returnListLink(C('TRANSFER_IS_CHECK_ADOPT_NAME'), $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-normal');
                    $html .= $this->returnListLink('打印审批单', $printReportUrl, 'printReport', C('BTN_CURRENCY'));
                    $html .= $this->returnListLink('上传/查看审批单', $uploadReportUrl, 'uploadReport', C('BTN_CURRENCY').' layui-btn-warm');
                }
            } elseif ($v['approve_status'] == C('STATUS_APPROE_FAIL')) {
                //审批不通过
                $html .= $this->returnListLink('不通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-danger');
            }
            $html .= '</div>';
            $asArr[$k]['operation'] = $html;
        }

        $result['total'] = $total;
        $result['offset'] = $offset;
        $result['limit'] = $limit;
        $result['code'] = 200;
        $result['rows'] = $asArr;

        return $result;
    }

    //获取转科流程记录
    public function getProgress()
    {
        $departid = session('departid');
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order');
        $sort = I('POST.sort');
        $assets = I('POST.progressAssets');
        $assetsNum = I('POST.progressAssetsNum');
        $assetsOrnum = I('POST.progressAssetsOrnum');
        $assetsCat = I('POST.progressCategory');
        $assetsDepIn = I('POST.progressAssetsDepartmentIn');
        $assetsDepOut = I('POST.progressAssetsDepartmentOut');
        $transferdate = I('POST.progressTransferdate');
        $applicantdate = I('POST.progressApplicantdate');
        $assetsUser = I('POST.progressAssetsUser');
        $examineStatus = I('POST.progressExamineStatus');
        $checkStatus = I('POST.progressCheckStatus');
        $hospital_id = I('POST.hospital_id');
        if (!session('isSuper')) {
            if (!$departid) {
                $where['B.departid'] = 0;
            } else {
                $where['B.status'] = ['neq', 2];
            }
        } else {
            $where['B.status'] = ['neq', 2];
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = $hospital_id;
        } else {
            $where['hospital_id'] = session('current_hospitalid');
        }
        $where['_string'] = "A.tranin_departid IN ($departid) or A.tranout_departid IN ($departid) ";
        $asModel = new AssetsInfoModel();
        if ($assets) {
            //设备名称搜索
            $where['B.assets'] = ['like', '%'.$assets.'%'];
        }
        if ($assetsNum) {
            //资产编码搜索
            $where['B.assnum'] = ['like', '%'.$assetsNum.'%'];
        }
        if ($assetsOrnum) {
            //资产原编码搜索
            $where['B.assorignum'] = ['like', '%'.$assetsOrnum.'%'];
        }
        if ($assetsCat) {
            //分类搜索
            $caModel = new CategoryModel();
            $catwhere['category'] = ['like', '%'.$assetsCat.'%'];
            $catids = $caModel->getCatidsBySearch($catwhere);
            $where['B.catid'] = ['in', $catids];
        }
        if ($assetsDepIn) {
            //转入部门搜索
            $deModel = new DepartmentModel();
            $deinwhere['department'] = ['like', '%'.$assetsDepIn.'%'];
            $res = $deModel->DB_get_one('department', 'group_concat(departid) as departid', $deinwhere);
            if ($res['departid']) {
                $where['A.tranin_departid'] = ['in', $res['departid']];
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;

                return $result;
            }
        }
        if ($assetsDepOut) {
            //转出部门搜索
            $deModel = new DepartmentModel();
            $deoutwhere['department'] = ['like', '%'.$assetsDepOut.'%'];
            $res = $deModel->DB_get_one('department', 'group_concat(departid) as departid', $deoutwhere);
            if ($res['departid']) {
                $where['A.tranout_departid'] = ['in', $res['departid']];
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;

                return $result;
            }
        }
        if ($transferdate) {
            //转科时间搜索
            $where['A.transfer_date'] = $transferdate;
        }
        if ($applicantdate) {
            //申请时间搜索
            $pretime = getHandleDate(strtotime($applicantdate) - 1);
            $nexttime = getHandleDate(strtotime($applicantdate) + 24 * 3600);
            $where['A.applicant_time'] = [['GT', $pretime], ['LT', $nexttime], 'and'];
        }
        if ($assetsUser != null) {
            //申请人员搜索
            $where['A.applicant_user'] = $assetsUser;
        }
        if ($examineStatus != null) {
            //审核状态搜索
            $where['A.approve_status'] = $examineStatus;
        }
        if ($checkStatus != null) {
            //验收状态搜索
            $where['A.is_check'] = $checkStatus;
        }
        //根据条件统计符合要求的数量
        $join = ' LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $total = $asModel->DB_get_count_join('assets_transfer', 'A', $join, $where);
        $fields = 'A.*,B.assnum,B.assorignum,B.assets,B.model,B.catid';
        $asArr = $asModel->DB_get_all_join('assets_transfer', 'A', $fields, $join, $where, '', $sort.' '.$order, $limit);
        $catname = [];
        $departname = [];
        include APP_PATH.'Common/cache/category.cache.php';
        include APP_PATH.'Common/cache/department.cache.php';

        $bot_span = '<span style="color:#FFB800;">';
        $success_span = '<span style="color:green;">';
        $fail_span = '<span style="color:red;">';
        foreach ($asArr as $k => $v) {
            $asArr[$k]['category'] = $catname[$v['catid']]['category'];
            $asArr[$k]['tranout_depart_name'] = $departname[$v['tranout_departid']]['department'];
            $asArr[$k]['tranin_depart_name'] = $departname[$v['tranin_departid']]['department'];
            if ($v['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                //审批通过
                $asArr[$k]['approve_status_name'] = $success_span.'通过</span>';
            } elseif ($v['approve_status'] == C('APPROVE_STATUS')) {
                //审批中
                if ($v['complete_approver']) {
                    $asArr[$k]['approve_status_name'] = $bot_span.'审批中</span>';
                } else {
                    $asArr[$k]['approve_status_name'] = $bot_span.'待审批</span>';
                }
            } elseif ($v['approve_status'] == C('STATUS_APPROE_FAIL')) {
                //审批不通过
                $asArr[$k]['approve_status_name'] = $fail_span.'不通过</span>';
            } elseif ($v['approve_status'] == C('STATUS_APPROE_UNWANTED')) {
                //不需要审批
                $asArr[$k]['approve_status_name'] = '不需审批';
            }
            if ($v['is_check'] == C('TRANSFER_IS_CHECK_ADOPT')) {
                //已验收
                $asArr[$k]['is_check_name'] = $success_span.C('TRANSFER_IS_CHECK_ADOPT_NAME').'</span>';
            } elseif ($v['is_check'] == C('TRANSFER_IS_NOTCHECK')) {
                //待验收
                $asArr[$k]['is_check_name'] = $bot_span.C('TRANSFER_IS_NOTCHECK_NAME').'</span>';
            } elseif ($v['is_check'] == C('TRANSFER_IS_CHECK_NOT_THROUGH')) {
                //验收失败
                $asArr[$k]['is_check_name'] = $fail_span.C('TRANSFER_IS_CHECK_NOT_THROUGH_NAME').'</span>';
            }
        }
        $result['total'] = $total;
        $result['offset'] = $offset;
        $result['limit'] = $limit;
        $result['code'] = 200;
        $result['rows'] = $asArr;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }

        return $result;
    }

    /**
     * Notes: 保存审核结果.
     *
     * @param $level int 当前次序
     * @param $arr array 转科信息
     *
     * @return array
     */
    public function addApprove_transfer($level, $arr)
    {
        $data['atid'] = $arr['atid'];
        $data['approve_class'] = 'transfer';
        $data['process_node_level'] = $level;
        $data['process_node'] = C('TRANSFER_APPROVE');
        $data['proposer'] = $arr['applicant_user'];
        $data['proposer_time'] = strtotime($arr['applicant_time']);
        $data['approver'] = session('username');
        $data['approve_time'] = time();
        $data['is_adopt'] = I('POST.res');
        $data['remark'] = trim(I('POST.remark'));
        $where['assid'] = ['EQ', $arr['assid']];
        $files = 'assid,hospital_id,assnum,assets,status,departid,quality_in_plan,patrol_in_plan,buy_price';
        $assets = $this->DB_get_one('assets_info', $files, $where);
        //获取审批流程
        $approve_process_user = $this->get_approve_process($assets['buy_price'], C('TRANSFER_APPROVE'), $assets['hospital_id']);
        $count = 0;
        foreach ($approve_process_user as &$process_value) {
            if ($process_value['approve_user'] == '部门审批负责人') {
                //有部门审批 流程总数+1
                $count = 1;
            }
        }
        //总审核流程数
        $lastProcess = count($approve_process_user) + $count;
        $approve = $this->insertData('approve', $data);
        if ($approve) {
            //更新已审批人和未审批人
            $completeornotuser = $this->get_complete_ornot_user($arr);
            //更新已审批人，未审批人
            $this->updateData('assets_transfer', ['complete_approver' => $completeornotuser['complete'], 'not_complete_approver' => $completeornotuser['notcomplete']], ['atid' => $arr['atid']]);
            //添加日志
            $log['is_adopt'] = $data['is_adopt'] == 1 ? '同意' : '不同意';
            $log['transfernum'] = $arr['transfernum'];
            $text = getLogText('approverTransferLogText', $log);
            $this->addLog('assets_transfer', M()->getLastSql(), $text, $arr['atid']);
            //短信开启
            $settingData = $this->checkSmsIsOpen($this->Controller);
            $ToolMod = new ToolController();
            $departname = [];
            include APP_PATH.'Common/cache/department.cache.php';
            $transferInfo = $this->DB_get_one('assets_transfer', 'assid,transfer_date,transfernum,tranout_departid,tranin_departid,tranout_departrespon,tranin_departrespon,applicant_user,applicant_time,tran_reason', ['atid' => $arr['atid']]);
            $asInfo = $this->DB_get_one('assets_info', 'assnum,assets', ['assid' => $transferInfo['assid']]);
            $smsData['tranout_department'] = $departname[$transferInfo['tranout_departid']]['department'];
            $smsData['tranin_department'] = $departname[$transferInfo['tranin_departid']]['department'];
            $smsData['assnum'] = $asInfo['assnum'];
            $smsData['assets'] = $asInfo['assets'];
            $smsData['transfer_num'] = $transferInfo['transfernum'];
            //判断是否开启微信端
            $moduleModel = new ModuleModel();
            $wx_status = $moduleModel->decide_wx_login();
            //判断是否是最后一道审批或者审批不通过
            if ($level == $lastProcess || $data['is_adopt'] == C('STATUS_APPROE_FAIL')) {
                //更新转科表对应记录为最后审核状态
                $change_assid = [];
                $change_assid[] = $arr['assid'];
                $this->updateData('assets_transfer', ['approve_status' => $data['is_adopt'], 'approve_time' => getHandleDate(time())], ['atid' => $arr['atid']]);
                if ($data['is_adopt'] == C('STATUS_APPROE_FAIL')) {
                    //审批不通过，修改retrial_status状态为 1 等待重审操作
                    $this->updateData('assets_transfer', ['retrial_status' => 1], ['atid' => $arr['atid']]);
                    //==========================================短信 START==========================================
                    //最后不通过审批 短信通知申请人
                    if ($settingData) {
                        //有开启短信
                        $telephone = $this->DB_get_one('user', 'telephone', ['username' => $transferInfo['tranout_departrespon']]);
                        $smsData['approve_status'] = '不通过';
                        $sms = $this->formatSmsContent($settingData['approveTransferStatus']['content'], $smsData);
                        $ToolMod->sendingSMS($telephone['telephone'], $sms, $this->Controller, $arr['atid']);
                    }
                    //==========================================短信 END==========================================

                    $telephone = $this->DB_get_one('user', 'openid', ['username' => $transferInfo['applicant_user']]);
                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**'.$asInfo['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**'.$asInfo['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转出科室：**'.$smsData['tranout_department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转入科室：**'.$smsData['tranin_department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**审批意见：**不通过';
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**审批备注：**'.$data['remark'];
                        $feishu_fields[] = $fd;

                        //按钮区域
                        $act['tag'] = 'button';
                        $act['type'] = 'primary';
                        $act['url'] = C('APP_NAME').C('FS_FOLDER_NAME').'/#'.C('FS_NAME').'/Transfer/add?atid='.$arr['atid'];
                        $act['text']['tag'] = 'plain_text';
                        $act['text']['content'] = '申请重审或结束进程';
                        $feishu_actions[] = $act;

                        $card_data['config']['enable_forward'] = false; //是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true; //是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['elements'][1]['tag'] = 'hr';
                        $card_data['elements'][2]['actions'] = $feishu_actions;
                        $card_data['elements'][2]['layout'] = 'bisected';
                        $card_data['elements'][2]['tag'] = 'action';
                        $card_data['header']['template'] = 'red';
                        $card_data['header']['title']['content'] = '设备转科审批结果通知';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        if ($telephone['openid']) {
                            $this->send_feishu_card_msg($telephone['openid'], $card_data);
                        }
                        //==========================================飞书 END==========================================
                    } else {
                        //==================================微信通知审批未通过 END====================================
                        if ($wx_status && $telephone['openid']) {
                            if (C('USE_VUE_WECHAT_VERSION')) {
                                $redecturl = C('APP_NAME').C('VUE_FOLDER_NAME').'/#'.C('VUE_NAME').'/Transfer/add?atid='.$arr['atid'];
                            } else {
                                $redecturl = C('HTTP_HOST').C('MOBILE_NAME').'/Transfer/add.html?atid='.$arr['atid'];
                            }

                            Weixin::instance()->sendMessage($telephone['openid'], '设备转科审批结果通知', [
                                'thing1' => $smsData['tranout_department'], // 转出科室
                                'thing2' => $smsData['tranin_department'], // 转入科室
                                'thing3' => $asInfo['assets'], // 设备名称
                                'character_string5' => $transferInfo['transfernum'], // 转科单号
                                'const6' => '未通过', // 审批结果
                            ], $redecturl);
                        }
                        //==================================微信通知审批未通过 END====================================
                    }
                } else {
                    //如果最后通过了审批 短信通知验收
                    if ($settingData) {
                        //有开启短信
                        //==========================================短信 START==========================================
                        $smsData['approve_status'] = '通过';
                        $UserData = $ToolMod->getUser('check', $transferInfo['tranin_departid']);
                        if ($settingData['checkTransfer']['status'] == C('OPEN_STATUS') && $UserData) {
                            //通知转入科室验收 开启
                            $phone = $this->formatPhone($UserData);
                            $sms = $this->formatSmsContent($settingData['approveTransferStatus']['content'], $smsData);
                            $ToolMod->sendingSMS($phone, $sms, $this->Controller, $arr['atid']);
                        }
                        //==========================================短信 END==========================================
                    }

                    if (C('USE_FEISHU') === 1) {
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转科单号：**'.$transferInfo['transfernum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备名称：**'.$asInfo['assets'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**设备编码：**'.$asInfo['assnum'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转出科室：**'.$smsData['tranout_department'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false; //是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**转入科室：**'.$smsData['tranin_department'];
                        $feishu_fields[] = $fd;

                        //按钮区域
                        $act['tag'] = 'button';
                        $act['type'] = 'primary';
                        $act['url'] = C('APP_NAME').C('FS_FOLDER_NAME').'/#'.C('FS_NAME').'/Transfer/check?atid='.$arr['atid'];
                        $act['text']['tag'] = 'plain_text';
                        $act['text']['content'] = '验收';
                        $feishu_actions[] = $act;

                        $card_data['config']['enable_forward'] = false; //是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true; //是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['elements'][1]['tag'] = 'hr';
                        $card_data['elements'][2]['actions'] = $feishu_actions;
                        $card_data['elements'][2]['layout'] = 'bisected';
                        $card_data['elements'][2]['tag'] = 'action';
                        $card_data['header']['template'] = 'carmine';
                        $card_data['header']['title']['content'] = '设备转科验收提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        $toUser = $this->getToUser(session('userid'), $transferInfo['tranin_departid'], 'Assets', 'Transfer', 'check');
                        foreach ($toUser as $k => $v) {
                            $this->send_feishu_card_msg($v['openid'], $card_data);
                        }
                        //==========================================飞书 END==========================================
                    } else {
                        //==================================微信通知转入科室验收 START==================================
                        if (C('USE_VUE_WECHAT_VERSION')) {
                            $redecturl = C('APP_NAME').C('VUE_FOLDER_NAME').'/#'.C('VUE_NAME').'/Transfer/check?atid='.$arr['atid'];
                        } else {
                            $redecturl = C('HTTP_HOST').C('MOBILE_NAME').'/Transfer/check.html?atid='.$arr['atid'];
                        }

                        /** @var UserModel[] $users */
                        $users = $this->getToUser(session('userid'), $transferInfo['tranin_departid'], 'Assets', 'Transfer', 'check');
                        $openIds = array_column($users, 'openid');
                        $openIds = array_filter($openIds);
                        $openIds = array_unique($openIds);

                        $messageData = [
                            'thing3' => $smsData['tranout_department'], // 所属科室
                            'thing1' => $asInfo['assets'], // 设备名称
                            'const13' => '转科', // 设备来源
                            'character_string11' => $transferInfo['transfernum'], // 订单编号
                            'const7' => '', // 处理结果
                        ];

                        foreach ($openIds as $openId) {
                            Weixin::instance()->sendMessage($openId, '设备验收通知', $messageData, $redecturl);
                        }
                        //==================================微信通知转入科室验收 END====================================
                    }
                }
            } else {
                //获取下次审批人  当前level+1
                $arr['complete_approver'] = $completeornotuser['complete'];
                $approve = $this->check_approve_process_transfer($arr, $approve_process_user, $level + 1);
                $saveData['current_approver'] = $approve['current_approver'];
                $this->updateData('assets_transfer', $saveData, ['atid' => $arr['atid']]);

                $telephone = $this->DB_get_one('user', 'telephone,openid', ['username' => $approve['this_current_approver']]);
                //短信通知下一个人
                //==========================================短信 START==========================================
                if ($settingData) {
                    $sms = $this->formatSmsContent($settingData['approveTransfer']['content'], $smsData);
                    $ToolMod->sendingSMS($telephone['telephone'], $sms, $this->Controller, $arr['atid']);
                }
                //==========================================短信 END==========================================

                if (C('USE_FEISHU') === 1) {
                    //==========================================飞书 START========================================
                    //要显示的字段区域
                    $fd['is_short'] = false; //是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备名称：**'.$asInfo['assets'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false; //是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**设备编码：**'.$asInfo['assnum'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false; //是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**转出科室：**'.$smsData['tranout_department'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false; //是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**转入科室：**'.$smsData['tranin_department'];
                    $feishu_fields[] = $fd;

                    $fd['is_short'] = false; //是否并排布局
                    $fd['text']['tag'] = 'lark_md';
                    $fd['text']['content'] = '**转科原因：**'.$transferInfo['tran_reason'];
                    $feishu_fields[] = $fd;

                    //按钮区域
                    $act['tag'] = 'button';
                    $act['type'] = 'primary';
                    $act['url'] = C('APP_NAME').C('FS_FOLDER_NAME').'/#'.C('FS_NAME').'/Transfer/approval?atid='.$arr['atid'];
                    $act['text']['tag'] = 'plain_text';
                    $act['text']['content'] = '审批';
                    $feishu_actions[] = $act;

                    $card_data['config']['enable_forward'] = false; //是否允许卡片被转发
                    $card_data['config']['wide_screen_mode'] = true; //是否根据屏幕宽度动态调整消息卡片宽度
                    $card_data['elements'][0]['tag'] = 'div';
                    $card_data['elements'][0]['fields'] = $feishu_fields;
                    $card_data['elements'][1]['tag'] = 'hr';
                    $card_data['elements'][2]['actions'] = $feishu_actions;
                    $card_data['elements'][2]['layout'] = 'bisected';
                    $card_data['elements'][2]['tag'] = 'action';
                    $card_data['header']['template'] = 'blue';
                    $card_data['header']['title']['content'] = '设备转科审批提醒';
                    $card_data['header']['title']['tag'] = 'plain_text';

                    $this->send_feishu_card_msg($telephone['openid'], $card_data);
                //==========================================飞书 END==========================================
                } else {
                    //==================================微信短信通知下一个人 END====================================
                    if ($telephone['openid']) {
                        if (C('USE_VUE_WECHAT_VERSION')) {
                            $redecturl = C('APP_NAME').C('VUE_FOLDER_NAME').'/#'.C('VUE_NAME').'/Transfer/approval?atid='.$arr['atid'];
                        } else {
                            $redecturl = C('HTTP_HOST').C('MOBILE_NAME').'/Transfer/approval.html?atid='.$arr['atid'];
                        }

                        Weixin::instance()->sendMessage($telephone['openid'], '设备转科待审批提醒', [
                            'thing1' => $smsData['tranout_department'], // 转出科室
                            'thing2' => $smsData['tranin_department'], // 转入科室
                            'thing3' => $asInfo['assets'], // 设备名称
                            'character_string4' => $asInfo['assnum'], // 设备编码
                            'character_string5' => $transferInfo['transfernum'], // 转科单号
                        ], $redecturl);
                    }
                    //==================================微信短信通知下一个人 END====================================
                }
            }

            return ['status' => 1, 'msg' => '审批成功！'];
        } else {
            return ['status' => -1, 'msg' => '审批失败，请稍后再试！'];
        }
    }

    /**
     * Notes: 验证转科流程是否有误,并且返回下次审批人.
     *
     * @param $assets array 设备基础信息
     * @param $approve_process_user array 审批流程
     * @param $level int 审批级别
     *
     * @return array
     */
    public function check_approve_process_transfer($assets, $approve_process_user, $level)
    {
        $result = [];
        $all_approver = '';
        $current_approver = '';
        $this_current_approver = '';
        $departname = [];
        $push = [];
        include APP_PATH.'Common/cache/department.cache.php';
        foreach ($approve_process_user as &$approveV) {
            if ($approveV['approve_user'] == '部门审批负责人') {
                //转出科室负责人
                $manager_where['departid'] = ['IN', [$assets['tranout_departid'], $assets['tranin_departid']]];
                $manager = $this->DB_get_all('department', 'manager,departid', $manager_where);
                if (!$manager) {
                    die(json_encode(['status' => -1, 'msg' => '该设备所属转出科室（'.$departname[$assets['tranout_departid']]['department'].'）与 设备转入科室 （'.$departname[$assets['tranin_departid']]['department'].'） 未设置审批负责人，请先设置']));
                }
                $managerValueArr = [];
                foreach ($manager as &$managerValue) {
                    $managerValueArr[$managerValue['departid']] = $managerValue['manager'];
                }
                if (is_null($managerValueArr[$assets['tranout_departid']])) {
                    die(json_encode(['status' => -1, 'msg' => '该设备所属转出科室（'.$departname[$assets['tranout_departid']]['department'].'） 未设置审批负责人，请先设置']));
                }
                if (is_null($managerValueArr[$assets['tranin_departid']])) {
                    die(json_encode(['status' => -1, 'msg' => '该设备转入科室（'.$departname[$assets['tranin_departid']]['department'].'） 未设置审批负责人，请先设置']));
                }
                $approveV['approve_user'] = $managerValueArr[$assets['tranout_departid']];
                $push['approve_user'] = $managerValueArr[$assets['tranin_departid']];
                $push['approve_user_aux'] = $approveV['approve_user_aux'];
                $push['listorder'] = $approveV['listorder'] + 1;
                --$approveV['listorder'];
            }
            if ($push) {
                ++$approveV['listorder'];
            }
        }

        if ($push) {
            array_push($approve_process_user, $push);
        }
        $approve_process_user = $this->array_sort($approve_process_user, 'listorder');
        foreach ($approve_process_user as &$approveV) {
            if ($approveV['listorder'] == $level) {
                $current_approver = $approveV['approve_user'].','.$approveV['approve_user_aux'];
                $this_current_approver = $approveV['approve_user'];
            }
            $all_approver .= ',/'.$approveV['approve_user'].'/';
            if ($approveV['approve_user_aux']) {
                $all_approver .= ',/'.$approveV['approve_user_aux'].'/';
            }
        }
        $result['all_approver'] = trim($all_approver, ',');
        $current_approver = trim($current_approver, ',');
        $result['current_approver'] = str_replace('/,/', ',', $current_approver);
        $result['this_current_approver'] = $this_current_approver;

        return $result;
    }

    /**
     * 获取附属设备信息.
     *
     * @param $atid int 转科记录id
     *
     * @return array
     * */
    public function getSubsidiaryBasic($atid)
    {
        $join = 'LEFT JOIN sb_assets_info AS A ON A.assid=D.subsidiary_assid';
        $fields = 'A.assid,A.assets,A.assnum,A.model,A.unit,A.buy_price';
        $data = $this->DB_get_all_join('assets_transfer_detail', 'D', $fields, $join, "atid=$atid");

        return $data;
    }

    /**
     * 获取对应的附属设备.
     *
     * @return array
     * */
    public function getAssetsSubsidiary($assids_arr)
    {
        //筛选借调中
        $borrowWhere['status'] = ['IN', [C('BORROW_STATUS_APPROVE'), C('BORROW_STATUS_BORROW_IN'), C('BORROW_STATUS_GIVE_BACK')]];
        $borrow = $this->DB_get_one('assets_borrow', 'borid,assid', $borrowWhere);
        $not_assid = [];
        if ($borrow) {
            $borid = [];
            foreach ($borrow as &$bor) {
                $borid[] = $bor['borid'];
                $not_assid[] = $bor['assid'];
            }
            $borrow_detail = $this->DB_get_one('assets_borrow_detail', 'subsidiary_assid', ['assid' => ['IN', $borid]]);
            if ($borrow_detail) {
                foreach ($borrow_detail as &$bor_detail) {
                    $not_assid[] = $bor_detail['subsidiary_assid'];
                }
            }
        }
        //筛选计量计划中
        $meteringWhere['status'] = ['EQ', C('YES_STATUS')];
        $meteringWhere['assid'] = ['IN', $assids_arr];
        $metering = $this->DB_get_one('metering_plan', 'assid', $meteringWhere);
        if ($metering) {
            foreach ($metering as &$met) {
                $not_assid[] = $met['assid'];
            }
        }
        if ($not_assid != []) {
            $where['assid'] = ['NOTIN', $not_assid];
        }
        $where['main_assid'] = ['IN', $assids_arr];
        $where['quality_in_plan'] = ['EQ', C('NO_STATUS')];
        $where['patrol_in_plan'] = ['EQ', C('NO_STATUS')];
        $where['status'] = ['EQ', C('ASSETS_STATUS_USE')];
        $data = $this->DB_get_all('assets_info', 'assid,assets,assnum,model,unit,buy_price,main_assid,main_assets', $where);
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        foreach ($data as $key => $value) {
            if (!$showPrice) {
                $data[$key]['buy_price'] = '***';
            }
        }

        return $data;
    }

    /** 格式化短信内容
     * @param $content
     * @param $data
     *
     * @return mixed
     */
    public function formatSmsContent($content, $data)
    {
        $content = str_replace('{assets}', $data['assets'], $content);
        $content = str_replace('{assnum}', $data['assnum'], $content);
        $content = str_replace('{tranout_department}', $data['tranout_department'], $content);
        $content = str_replace('{tranin_department}', $data['tranin_department'], $content);
        $content = str_replace('{transfer_num}', $data['transfer_num'], $content);
        $content = str_replace('{approve_status}', $data['approve_status'], $content);
        $content = str_replace('{check_status}', $data['check_status'], $content);

        return $content;
    }
}
