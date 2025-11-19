<?php

namespace Admin\Controller\Qualities;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\NotCheckLogin\PublicController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\ModuleModel;
use Admin\Model\QualityModel;
use Admin\Model\UserModel;
use Common\Weixin\Weixin;

class QualityController extends CheckLoginController
{
    private $MODULE = 'Qualities';

    /**
     * 质控计划制定列表
     */
    public function getQualityList()
    {
        $qualityModel = new QualityModel();
        if (IS_POST) {
            $type = I('POST.type');
            if ($type == 'batchPrint') {
                $qsid = explode(',', trim(I('POST.qsid'), ','));
                $qualityModel = new QualityModel();
                $userModel = new UserModel();
                $html = '';
                foreach ($qsid as $key => $value) {
                    $qsInfo = $qualityModel->DB_get_one('quality_starts', '*', array('qsid' => $value));
                    //查询质控模板、检测仪器、检测依据
                    $template = $qualityModel->DB_get_one('quality_templates', 'name,template_name', array('qtemid' => $qsInfo['qtemid']));
                    $qsInfo['template'] = $template['name'];
                    $basis = $qualityModel->DB_get_one('quality_detection_basis', 'group_concat(basis separator "、") as basis', array('qdbid' => array('in', $qsInfo['qdbid'])));
                    $qsInfo['basis'] = $basis['basis'];
                    $instrument = $qualityModel->DB_get_one('quality_instruments', 'instrument,addtime', array('qiid' => $qsInfo['qiid']));
                    $qsInfo['instrument'] = $instrument['instrument'];
                    $setting = json_decode($qsInfo['start_preset'], true);
                    $values = array();
                    foreach ($setting as $k => $v) {
                        $preset = $qualityModel->DB_get_one('quality_preset', '*', array('detection_Ename' => $k));
                        $preset['set'] = $v;
                        $values[] = $preset;
                    }
                    //查询设备信息
                    $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,brand,serialnum,model,departid,catid', array('assid' => $qsInfo['assid']));
                    $departname = array();
                    $catname = array();
                    include APP_PATH . "Common/cache/category.cache.php";
                    include APP_PATH . "Common/cache/department.cache.php";
                    //设备科使用部门负责人签名
                    $asInfoautograph = $userModel->get_departid_autograph($asInfo['departid']);
                    $asInfo['department'] = $departname[$asInfo['departid']]['department'];
                    $asInfo['category'] = $catname[$asInfo['catid']]['category'];
                    //设备科签名
                    $departautograph = $userModel->get_assets_autograph();
                    //经办工程师签名
                    $qsInfoautograph = $userModel->get_autograph($qsInfo['username']);
                    /*
                    签名模板不显示
                    $this->assign('qsInfoautograph', $qsInfoautograph);
                    $this->assign('qsInfoautograph_time', substr($qsInfo['start_date'], 0, 10));
                    $this->assign('departautograph', $departautograph);
                    $this->assign('departautograph_time', substr($qsInfo['start_date'], 0, 10));
                    $this->assign('asInfoautograph', $asInfoautograph);
                    $this->assign('asInfoautograph_time', substr($qsInfo['start_date'], 0, 10));*/
                    $this->assign('qsInfo', $qsInfo);
                    $this->assign('asInfo', $asInfo);
                    $this->assign('values', $values);
                    $this->assign('date', getHandleTime(time()));
                    $html .= $this->display('Qualities/Quality/templates/' . $template['template_name']);
                }
                echo $html;
                exit;
            } else {
                $result = $qualityModel->getPlanList();
                $this->ajaxReturn($result);
            }
        } else {
            $departments = $this->getSelectDepartments();
            $basis = $qualityModel->DB_get_all('quality_detection_basis', '*', array('1'));
            foreach ($basis as $k => $v) {
                $basis[$k]['basis'] = $v['basis'] . '&#13;';
            }
            $this->assign('basis', $basis);
            $this->assign('department', $departments);
            $this->assign('getQualityList', get_url());
            $this->display();
        }
    }

    /**
     * 启用质控计划
     */
    public function startQualityPlan()
    {

        if (IS_POST) {
            $qualityModel = new QualityModel();
            $type = I('POST.type');
            if ($type == 'getInstrument') {
                //获取检测仪器参数
                $qiid = I('POST.qiid');
                $result = $qualityModel->DB_get_one('quality_instruments', 'qiid,productid,model,metering_num,metering_report', array('qiid' => $qiid));
                if ($result) {
                    $result['status'] = 1;
                    $result['msg'] = '获取仪器信息成功！';
                    $this->ajaxReturn($result);
                } else {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '获取不到仪器信息！'));
                }
            } elseif ($type == 'getTemplatesSetting') {
                //获取模板设定值
                $qtemid = I('POST.tid');
                $join = "LEFT JOIN sb_quality_preset AS B ON A.qprid = B.qprid";
                $fileds = "A.qtemid,B.*";
                $set = $qualityModel->DB_get_all_join('qualiyt_preset_template', 'A', $fileds, $join, array('A.qtemid' => $qtemid,'B.is_display'=>'0'), '', 'qprid asc','');
                foreach ($set as $k => $v) {
                    $set[$k]['setting'] = json_decode($v['value'], true);
                }
                include './tecev/src/views/Qualities/Quality/ajaxPreSet.html';
            } elseif ($type == 'save') {
                //启用计划
                $qsid = trim(I('POST.qsid'), ',');
                $result = $qualityModel->saveStartData();
                if ($result['status'] == 1) {
                    //记录日志
                    $qualityModel->addLog('quality_starts', M()->getLastSql(), session('username') . '启用了质控计划（计划ID为：' . $qsid . '）', $qsid, '');
                }
                $this->ajaxReturn($result);
            } elseif ($type == 'update') {
                //编辑计划
                $qsid = I('POST.qsid');
                $result = $qualityModel->updateStartData();
                if ($result['status'] == 1) {
                    //记录日志
                    $qualityModel->addLog('quality_starts', M()->getLastSql(), session('username') . '修改了一个质控计划（计划ID为：' . $qsid . ')', $qsid, '');
                }
                $this->ajaxReturn($result);
            } elseif ($type == 'stop') {
                //暂停计划
                $qsid = I('POST.qsid');
                if (!$qsid) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '参数非法！'));
                }
                $qsInfo = $qualityModel->DB_get_one('quality_starts', 'plan_identifier,plan_name,plan_num,is_start,start_username,assid,period,username,userid,start_date', array('qsid' => $qsid));
                if($qsInfo && $qsInfo['is_start'] == 2){
                    $this->ajaxReturn(array('status' => -1, 'msg' => '该质控计划已暂停，请勿重复操作！'));
                }
                $data['is_start'] = 0;
                $data['stop_date'] = getHandleTime(time());
                $data['stop_userid'] = session('userid');
                $data['stop_username'] = session('username');
                $res = $qualityModel->updateData('quality_starts', $data, array('qsid' => $qsid));
                if ($res) {
                    //记录日志
                    $qualityModel->addLog('quality_starts', M()->getLastSql(), session('username') . '暂停了一个质控计划（计划ID为：' . I('POST.qsid') . ')', I('POST.qsid'), '');
                    //==========================================短信 START==========================================
                    $settingData = $qualityModel->checkSmsIsOpen($this->MODULE);
                    if ($settingData) {
                        //有开启短信 通知执行人执行计划
                        $assets = $qualityModel->DB_get_one('assets_info', 'assets,departid', ['assid' => $qsInfo['assid']]);
                        $departname = [];
                        include APP_PATH . "Common/cache/department.cache.php";
                        $qsInfo['department'] = $departname[$assets['departid']]['department'];
                        $qsInfo['stop_username'] = session('username');
                        $where = [];
                        $where['status'] = C('OPEN_STATUS');
                        $where['is_delete'] = C('NO_STATUS');
                        $where['userid'] = $qsInfo['userid'];
                        $userData = $qualityModel->DB_get_one('user', 'telephone', $where);
                        if ($settingData['stopQualityPlan']['status'] == C('OPEN_STATUS') && $userData['telephone']) {
                            $sms = $qualityModel->formatSmsContent($settingData['stopQualityPlan']['content'], $qsInfo);
                            ToolController::sendingSMS($userData['telephone'], $sms, $this->MODULE, $qsid);
                        }
                    }
                    //==========================================短信 END==========================================

                    $where = [];
                    $where['status'] = C('OPEN_STATUS');
                    $where['is_delete'] = C('NO_STATUS');
                    $where['userid'] = $qsInfo['userid'];
                    $userData = $qualityModel->DB_get_one('user', 'telephone,openid', $where);
                    if(C('USE_FEISHU') === 1){
                        //==========================================飞书 START========================================
                        //要显示的字段区域
                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**计划名称：**'.$qsInfo['plan_name'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**计划类型：**质控计划';
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**计划状态：**暂停';
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '**执行人：**'.$qsInfo['username'];
                        $feishu_fields[] = $fd;

                        $fd['is_short'] = false;//是否并排布局
                        $fd['text']['tag'] = 'lark_md';
                        $fd['text']['content'] = '请暂时停止该质控计划的执行';
                        $feishu_fields[] = $fd;

                        $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                        $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                        $card_data['elements'][0]['tag'] = 'div';
                        $card_data['elements'][0]['fields'] = $feishu_fields;
                        $card_data['header']['template'] = 'red';
                        $card_data['header']['title']['content'] = '质控计划暂停提醒';
                        $card_data['header']['title']['tag'] = 'plain_text';

                        $qualityModel->send_feishu_card_msg($userData['openid'],$card_data);
                        //==========================================飞书 END==========================================
                    }else{
                        //==========================================微信 END==========================================
                        $moduleModel = new ModuleModel();
                        $wx_status = $moduleModel->decide_wx_login();
                        if ($wx_status) {
                            if ($userData['openid']) {
                                Weixin::instance()->sendMessage($userData['openid'], '工单处理提醒', [
                                    'thing10' => '质控计划',// 工单类型
                                    'thing9'  => $qsInfo['plan_name'],// 工单名称
                                    'time39'  => $qsInfo['start_date'],// 发起时间
                                    'thing7'  => $qsInfo['username'],// 发起人员
                                    'const56' => '已暂停',// 工单阶段
                                ]);
                            }
                        }
                        //==========================================微信 END==========================================
                    }
                    $this->ajaxReturn(array('status' => 1, 'msg' => '计划已暂停！'));
                } else {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '暂停失败！'));
                }
            }
        } else {
            $type = I('GET.type');
            switch ($type) {
                case 'showTemplate':
                    //查看模板页面
                    $this->showTemplate();
                    break;
                default:
                    //显示启用页面
                    $this->showStartQualityPlan();
                    break;
            }


        }
    }

    private function showStartQualityPlan()
    {
        $qualityModel = new QualityModel();
        $result = $qualityModel->getAssetsAndPresetInfo();
        if ($result['status'] == -1) {
            $this->error($result['msg']);
        } else {

            $this->assign('qsid', trim(I('GET.qsid'), ','));
            $this->assign('res', $result);
            $this->assign('date', getHandleTime(time()));
            $this->display();
        }
    }

    private function showTemplate()
    {
        $tpid = I('get.id');
        if (!$tpid) {
            exit('参数非法');
        }
        //实例化模型
        $qualityModel = new QualityModel();
        //获取当前模板名称信息
        $tpInfo = $qualityModel->DB_get_one('patrol_template', '', array('tpid' => $tpid));
        //根据arr_num_3获取类别和明细信息
        $arr_num_3 = json_decode($tpInfo['arr_num_3']);
        $order = 'FIELD(num,' . implode(',', $arr_num_3) . ')';
        $points = $qualityModel->DB_get_all('patrol_points', '', array('num' => array('IN', $arr_num_3)), '', $order, '');
        $parentid = array();
        foreach ($points as $k => $v) {
            if (!in_array($v['parentid'], $parentid)) {
                array_push($parentid, $v['parentid']);
            }
        }
        $pointCat = $qualityModel->DB_get_all('patrol_points', '', array('parentid' => 0, 'ppid' => array('IN', $parentid)));
        foreach ($pointCat as $k => $v) {
            foreach ($points as $k1 => $v1) {
                if ($v1['parentid'] == $v['ppid']) {
                    $pointCat[$k]['detail'][] = $v1;
                }
            }
        }
        $this->assign('name', $tpInfo['name']);
        $this->assign('level1', json_decode($tpInfo['arr_num_1']));
        $this->assign('level2', json_decode($tpInfo['arr_num_2']));
        $this->assign('data', $pointCat);
        $this->display('Patrol/PatrolSetting/showTemplate');
    }

    /**
     * 质控计划制定计划详情
     */
    public function showQualityPlan()
    {
        $qsid = I('GET.qsid');
        $qualityModel = new QualityModel();
        $qsInfo = $qualityModel->DB_get_one('quality_starts', '*', array('qsid' => $qsid));
        //查询质控模板、检测仪器、检测依据
        $template = $qualityModel->DB_get_one('quality_templates', 'name', array('qtemid' => $qsInfo['qtemid']));
        $qsInfo['template'] = $template['name'];
        $basis = array();
        if ($qsInfo['qdbid']) {
            $basis = $qualityModel->DB_get_one('quality_detection_basis', 'group_concat(basis separator "、") as basis', array('qdbid' => array('in', $qsInfo['qdbid'])));
        }
        //$basis = $qualityModel->DB_get_one('quality_detection_basis','group_concat(basis separator "、") as basis',array('qdbid'=>array('in',$qsInfo['qdbid'])));
        $qsInfo['basis'] = $basis['basis'];
        $instrument = $qualityModel->DB_get_one('quality_instruments', 'instrument', array('qiid' => $qsInfo['qiid']));
        $qsInfo['instrument'] = $instrument['instrument'];
        $setting = json_decode($qsInfo['start_preset'], true);
        $values = array();
        foreach ($setting as $k => $v) {
            $preset = $qualityModel->DB_get_one('quality_preset', '*', array('detection_Ename' => $k,'is_display'=>'0'));
            if ($preset) {
                $preset['set'] = implode('、', $v);
                $values[] = $preset;
            }
        }
        //查询设备信息
        $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,model,departid,catid,lasttesttime,lasttestuser,lasttestresult', array('assid' => $qsInfo['assid']));
        $departname = array();
        $catname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $asInfo['department'] = $departname[$asInfo['departid']]['department'];
        $asInfo['category'] = $catname[$asInfo['catid']]['category'];
        $this->assign('qsInfo', $qsInfo);
        $this->assign('asInfo', $asInfo);
        $this->assign('values', $values);
        $this->display();
    }

    /**
     * 质控计划模板预览
     */
    public function scanTemplate()
    {
        $qualityModel = new QualityModel();
        $userModel = new UserModel();
        if (IS_POST) {
            $cache = array();
            $cache['qtemid'] = $_POST['templates'];
            $cache['qiid'] = $_POST['instrument'];
            $cache['do_date'] = $_POST['do_date'];
            $cache['is_cycle'] = $_POST['is_cycle'] == 1 ? 1 : 0;
            $cache['cycle'] = $_POST['is_cycle'] == 1 ? $_POST['cycle'] : '';
            $cache['qdbid'] = implode(',', $_POST['basis']);
            if (!$cache['qdbid']) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '请选择检测仪器'));
            }
            unset($_POST['model']);
            unset($_POST['serialnum']);
            unset($_POST['num']);
            unset($_POST['templates']);
            unset($_POST['instrument']);
            unset($_POST['do_date']);
            unset($_POST['is_cycle']);
            unset($_POST['cycle']);
            unset($_POST['planName']);
            unset($_POST['basis']);
            unset($_POST['qsid']);
            $cache['start_preset'] = json_encode($_POST, JSON_UNESCAPED_UNICODE);
            F('planTempCache', $cache);
            $this->ajaxReturn(array('status' => 1, 'msg' => '缓存成功'));
        } else {//标记
            $qsid = trim(I('GET.qsid'), ',');
            $qsid = explode(',', $qsid);
            $qsid = $qsid[0];
            $qsInfo = $qualityModel->DB_get_one('quality_starts', '*', array('qsid' => $qsid));
            $cache = F('planTempCache');
            if ($cache) {
                //有缓存数据
                $qsInfo['qtemid'] = $cache['qtemid'];
                $qsInfo['qdbid'] = $cache['qdbid'];
                $qsInfo['qiid'] = $cache['qiid'];
                $qsInfo['do_date'] = $cache['do_date'];
                $qsInfo['is_cycle'] = $cache['is_cycle'];
                $qsInfo['cycle'] = $cache['cycle'];
                $qsInfo['start_preset'] = $cache['start_preset'];
            }
            //查询质控模板、检测仪器、检测依据
            $template = $qualityModel->DB_get_one('quality_templates', 'name,template_name', array('qtemid' => $qsInfo['qtemid']));
            $qsInfo['template'] = $template['name'];
            $basis = $qualityModel->DB_get_one('quality_detection_basis', 'group_concat(basis separator "、") as basis', array('qdbid' => array('in', $qsInfo['qdbid'])));
            $qsInfo['basis'] = $basis['basis'];
            $instrument = $qualityModel->DB_get_one('quality_instruments', 'instrument', array('qiid' => $qsInfo['qiid']));
            $qsInfo['instrument'] = $instrument['instrument'];
            $setting = json_decode($qsInfo['start_preset'], true);
            $values = $tolerance = [];
            foreach ($setting as $k => $v) {
                $preset = $qualityModel->DB_get_one('quality_preset', '*', array('detection_Ename' => $k));
                $preset['set'] = $v;
                $values[] = $preset;
            }
            foreach ($values as $k => $v) {
                $tolerance[$v['detection_name']] = $v['tolerance'];
            }
            //查询设备信息
            $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,brand,serialnum,model,departid,catid,opendate,lasttesttime', array('assid' => $qsInfo['assid']));
            $departname = array();
            $catname = array();
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            $asInfo['department'] = $departname[$asInfo['departid']]['department'];
            $asInfo['category'] = $catname[$asInfo['catid']]['category'];
            $asInfo['test_date'] = $asInfo['lasttesttime'] ? date('Y-m-d', strtotime($asInfo['lasttesttime'])) : '';
            if ($qsInfo['is_start']>2) {
                $detailInfo = $qualityModel->DB_get_one('quality_details', '*', array('qsid' => $qsid));
                $detailInfo['preset_detection'] = json_decode($detailInfo['preset_detection'], true);
                $detailInfo['fixed_detection'] = json_decode($detailInfo['fixed_detection'], true);
                //经办工程师签字
                $qsInfoautograph = $qualityModel->get_autograph($qsInfo['username']);
                //设备使用部门负责人签字
                $asInfoautograph = $qualityModel->get_departid_autograph($asInfo['departid']);
                //设备科签字
                $departautograph = $qualityModel->get_assets_autograph();
            $this->assign('qsInfoautograph', $qsInfoautograph);
            $this->assign('asInfoautograph', $asInfoautograph);
            $this->assign('departautograph', $departautograph);
            $this->assign('qsInfoautograph_time', date('Y-m-d', time()));
            $this->assign('asInfoautograph_time', date('Y-m-d', time()));
            $this->assign('departautograph_time', date('Y-m-d', time()));
            if ($detailInfo['preset_detection']['patient_normal_result']==1&&$detailInfo['preset_detection']['patient_abnormal_result']==1&&$detailInfo['preset_detection']['aid_normal_result']==1&&$detailInfo['preset_detection']['aid_abnormal_result']==1) {
                $detailInfo['preset_detection']['summary_result']=1;
            }else{
                $detailInfo['preset_detection']['summary_result']=2;
            }
            $this->assign('detailInfo', $detailInfo);

            }
            if ($qsInfo['qtemid']=='7') {
                $empty="<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
            }
            $this->assign('empty', $empty);
            $this->assign('qsInfo', $qsInfo);
            $this->assign('asInfo', $asInfo);
            $this->assign('values', $values);
            $this->assign('tolerance', $tolerance);
            $this->assign('date', getHandleTime(time()));
            //清除缓存
            F('planTempCache', NULL);
            $this->display('Qualities/Quality/printscan/' . $template['template_name']);
        }
    }

    /**
     * 新增质控计划
     */
    public function addQuality()
    {
        $qualityModel = new QualityModel();
        if (IS_POST) {
            $type = I('POST.type');
            if ($type == 'add') {
                $planName = trim($_POST['planName']);
                $result = $qualityModel->addPlan();
                if ($result['status'] == 1) {
                    //记录日志
                    $qualityModel->addLog('quality_starts', M()->getLastSql(), session('username') . '新增了一个质控计划（计划名称为：' . $planName . '）', M()->getLastInsID(), '');
                }
            } elseif ($type == 'getJoinAssets') {
                $result = $qualityModel->getAssetsListsByType();
            } else {
                $result = $qualityModel->getAssetsLists();
            }
            $this->ajaxReturn($result);
        } else {
            //所属科室
            $notCheck = new PublicController();
            $this->assign('departments', $notCheck->getAllDepartmentSearchSelect());
            //查询辅助分类
            $value = $qualityModel->DB_get_one('base_setting', 'value', array('module' => 'assets', 'set_item' => 'assets_helpcat'));
            $this->assign('helpcat', json_decode($value['value'], true));
            $this->assign('addQuality', get_url());
            $this->display();
        }
    }

    /**
     * 设备质控项目预设模板列表页
     */
    public function presetQualityItem()
    {
        $qualityModel = new QualityModel();
        if (IS_POST) {
            $type = I('POST.type');
            if ($type == 'batchPrint') {
                $assid = explode(',', trim(I('POST.assid'), ','));
                $temp = I('POST.temp');
                $qualityModel = new QualityModel();
                $html = '';
                foreach ($assid as $key => $value) {
                    //查询设备信息
                    $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,brand,serialnum,model,departid,catid,opendate,lasttesttime', array('assid' => $value));
                    $departname = array();
                    $catname = array();
                    include APP_PATH . "Common/cache/category.cache.php";
                    include APP_PATH . "Common/cache/department.cache.php";
                    $asInfo['department'] = $departname[$asInfo['departid']]['department'];
                    $asInfo['category'] = $catname[$asInfo['catid']]['category'];
                    $asInfo['test_date'] = $asInfo['lasttesttime'] ? date('Y-m-d', strtotime($asInfo['lasttesttime'])) : '';
                    $this->assign('asInfo', $asInfo);
                    $this->assign('qsInfo', array());
                    $this->assign('date', getHandleTime(time()));
                    $html .= $this->display('Qualities/Quality/templates/Assets_' . $temp);
                }
                echo $html;
                exit;
            }
        } else {
            $type = I('GET.type');
            if ($type) {
                $asInfo = $qsInfo = array();
                $asInfo['department'] = '由系统读取';
                $asInfo['assets'] = '由系统读取';
                $asInfo['brand'] = '由系统读取';
                $asInfo['model'] = '由系统读取';
                $asInfo['assnum'] = '由系统读取';
                $asInfo['serialnum'] = '由系统读取';
                $asInfo['test_date'] = '由系统读取';
                $qsInfo['plan_name'] = '由系统读取';
                $qsInfo['plan_name'] = '由系统读取';
                $qsInfo['basis'] = '由系统读取';
                $qsInfo['instrument'] = '由系统读取';
                $qsInfo['start_date'] = '由系统读取';
                $qsInfo['plan_num'] = '由系统读取';
                $this->assign('asInfo', $asInfo);
                $this->assign('qsInfo', $qsInfo);
                $this->assign('date', '由系统读取');
                $this->assign('type', $type);
                echo $this->display('Qualities/Quality/' . $type);
                exit;
            } else {
                $action = I('GET.action');
                if ($action == 'searchAssets') {
                    $this->assign('temp', I('GET.temp'));
                    $this->display('Qualities/Quality/searchAssets');
                } else {
                    //查询模板
                    $templates = $qualityModel->DB_get_all('quality_templates', '*', array('1'));
                    $asInfo = $qsInfo = array();
                    $asInfo['department'] = '由系统读取';
                    $asInfo['assets'] = '由系统读取';
                    $asInfo['brand'] = '由系统读取';
                    $asInfo['model'] = '由系统读取';
                    $asInfo['assnum'] = '由系统读取';
                    $asInfo['serialnum'] = '由系统读取';
                    $asInfo['test_date'] = '由系统读取';
                    $qsInfo['plan_name'] = '由系统读取';
                    $qsInfo['plan_name'] = '由系统读取';
                    $qsInfo['basis'] = '由系统读取';
                    $qsInfo['instrument'] = '由系统读取';
                    $qsInfo['start_date'] = '由系统读取';
                    $qsInfo['plan_num'] = '由系统读取';
                    $this->assign('asInfo', $asInfo);
                    $this->assign('qsInfo', $qsInfo);
                    $this->assign('date', '由系统读取');
                    $this->assign('type', 'Tem_JianHuYi');
                    $this->assign('templates', $templates);
                    $this->display();
                }
            }
        }
    }

    /**
     * 设备质控项目预设——新增设备质控项目模板
     */
    public function addPresetQI()
    {
        $qualityModel = new QualityModel();
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'addBasis':
                    $result = $qualityModel->addBasisData();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'editBasis':
                    $result = $qualityModel->editBasisData();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'deleteBasis':
                    $result = $qualityModel->deleteBasisData();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getBasisList':
                    $result = $qualityModel->getBasisListData();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $qualityModel->updatePreset();
                    $this->ajaxReturn(array('status' => 1, 'msg' => '设定成功'));
                    break;
            }
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'addBasis':
                    $this->assign('username', session('username'));
                    $this->assign('now', getHandleDate(time()));
                    $this->assign('url', get_url());
                    $this->display('addBasis');
                    break;
                case 'editBasis':
                    $qdbid = I('get.qdbid');
                    $basisInfo = $qualityModel->showBasisData();
                    $this->assign('url', get_url());
                    $this->assign('username', session('username'));
                    $this->assign('now', getHandleDate(time()));
                    $this->assign('basisInfo', $basisInfo);
                    $this->assign('qdbid', $qdbid);
                    $this->display('editBasis');
                    break;
                case 'showBasis':
                    $basisInfo = $qualityModel->showBasisData();
                    $this->assign('basisInfo', $basisInfo);
                    $this->display('showBasis');
                    break;
                default:
                    $setting = $qualityModel->DB_get_all('quality_preset','',array('is_dispaly'=>'0'));
                    foreach ($setting as $k => $v) {
                        $setting[$k]['value'] = json_decode($v['value']);
                        foreach ($setting[$k]['value'] as $k1 => $v1) {
                            $setting[$k]['value'][$k1] = $v1 . '&#13;';
                        }
                    }
                    $this->assign('setting', $setting);
                    $this->assign('addPresetQIUrl', get_url());
                    $this->display();
                    break;
            }
        }
    }

    /**
     * Notes: 编辑质控计划
     */
    public function editQualityPlan()
    {
        $qualityModel = new QualityModel();
        if (IS_GET) {
            //计划详情
            $qsid = I('GET.qsid');
            $qsInfo = $qualityModel->DB_get_one('quality_starts', '*', array('qsid' => $qsid));
            if ($qsInfo['is_start'] == 0) {
                $this->error('请先启用计划！');
            }
            //查询模板设置
            $join = "LEFT JOIN sb_qualiyt_preset_template AS B ON A.qprid = B.qprid";
            $preset = $qualityModel->DB_get_all_join('quality_preset', 'A', 'A.*', $join, array('B.qtemid' => $qsInfo['qtemid'],'A.is_display'=>'0'), '', 'A.qprid asc','');
            $oldset = json_decode($qsInfo['start_preset'], true);
            $set = array();
            foreach ($preset as $k => $v) {
                $value = json_decode($v['value'], true);
                foreach ($value as $kv => $vv) {
                    $set[$v['detection_Ename']][$kv]['value'] = $vv;
                    $set[$v['detection_Ename']][$kv]['checked'] = '';
                }
                foreach ($set as $k2 => $v2) {
                    foreach ($v2 as $k3 => $v3) {
                        if (in_array($v3['value'], $oldset[$k2])) {
                            $set[$k2][$k3]['checked'] = 'checked';
                        }
                    }
                }
            }
            $result = $qualityModel->getAssetsAndPresetInfo();
            $this->assign('qsInfo', $qsInfo);
            $this->assign('qsid', $qsid);
            $this->assign('res', $result);
            $this->assign('preset', $preset);
            $this->assign('set', $set);
            $this->assign('date', getHandleTime(time()));
            $this->display();
        }
    }

    /**
     * 质控明细录入列表
     */
    public function qualityDetailList()
    {
        $qualityModel = new QualityModel();
        if (IS_POST) {
            $type = I('POST.type');
            if ($type == 'getReportUrl') {
                $result = $qualityModel->getReportUrl();
                $this->ajaxReturn($result);
            } elseif ($type == 'batchPrint') {
                $this->batchPrint();
            } else {
                $result = $qualityModel->getCanExecuPlanLists();
                $this->ajaxReturn($result);
            }
        } else {
            $where['is_delete'] = C('NO_STATUS');
            $where['hospital_id'] = array('in', session('current_hospitalid'));
            $where['departid_id'] = session('departid');
            //获取所有部门
            $userModel = new UserModel();
            $departments = $userModel->getAllDepartments($where);
            $this->assign('department', $departments);
            $this->assign('qualityDetailList', get_url());
            $this->display();
        }
    }

    public function scanPic()
    {
        $qualityModel = new QualityModel();
        $qsid = I('GET.qsid');
        if($qsid){
            $reporturl = $qualityModel->DB_get_one('quality_details','report',array('qsid'=>$qsid));
            if(!$reporturl){
                exit('查找不到报告信息');
            }
            $src = $reporturl['report'];
        }else{
            $src = I('GET.url');
            $src = urldecode($src);
        }
        $t = explode('/', $src);
        $title = $t[count($t) - 1];//获取文件的名称，必须带后缀格式
        $type = substr(strrchr($title, '.'), 1);
        $this->assign('file_exists', file_exists('.' . $src));
        $this->assign('type', $type);
        $this->assign('src', $src);
        $this->display('./Public:showFile');
    }

    /**
     * 质控明细录入表单
     */
    public function setQualityDetail()
    {
        $qualityModel = new QualityModel();
        if (IS_POST) {
            $type = I('POST.action');
            switch ($type) {
                case 'upload':
                    if ($base64 = I('POST.base64')) {
                        $result = $qualityModel->uploadReportBase64($base64, C('UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME'));
                    } else {
                        $result = $qualityModel->uploadReport(C('UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME'));
                    }

                    if ($result['status'] == 1) {
                        $qsid = I('POST.qsid');
                        $data['report'] = $result['path'];
                        $data['edittime'] = getHandleDate(time());
                        $data['edituser'] = session('username');
                        $res = $qualityModel->updateData('quality_details', $data, array('qsid' => $qsid));
                        if ($res) {
                            $result['status'] = 1;
                            $result['msg'] = '上传报告成功！';
                        } else {
                            $result['status'] = -1;
                            $result['msg'] = '上传报告失败！';
                        }
                    }
                    break;
                case 'upload_pic':
                    if ($_FILES['file']) {
                        $Tool = new ToolController();
                        $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx');
                        $dirName = C('UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME');
                        $is_water = true;
                        $watermark = $qualityModel->DB_get_one('base_setting', 'value', array('module' => 'repair', 'set_item' => 'repair_print_watermark'));
                        $watermark = json_decode($watermark['value'], true);
                        $qsinfo = $qualityModel->DB_get_one('quality_starts', 'plan_num', array('qsid' => I('post.qsid')));
                        $water_text[0] = $watermark['watermark'];
                        $water_text[1] = $qsinfo['plan_num'];
                        $water_text[2] = date('Y-m-d H:i:s');
                        $info = $Tool->upFile($style, $dirName, $is_water, $water_text);
                        if ($info['status'] == C('YES_STATUS')) {
                            // 上传成功
                            $data['qsid'] = I('post.qsid');
                            $data['type'] = I('post.type');
                            $data['file_name'] = $info['formerly'];
                            $data['save_name'] = $info['title'];
                            $data['file_type'] = $info['ext'];
                            $data['file_size'] = round($info['size'] / 1000 / 1000, 2);
                            $data['file_url'] = $info['src'];
                            $data['add_user'] = session('username');
                            $data['add_time'] = date('Y-m-d H:i:s');
                            $res = $qualityModel->insertData('quality_details_file', $data);
                            if ($res) {
                                $result['status'] = 1;
                                $result['msg'] = '上传成功！';
                            }
                        } else {
                            // 上传错误提示错误信息
                            $result['status'] = -1;
                            $result['msg'] = '上传图片失败！';
                        }
                    }
                    break;
                case 'del_file':
                    $qsid = I('POST.qsid');
                    $data['report'] = '';
                    $res = $qualityModel->updateData('quality_details', $data, array('qsid' => $qsid));
                    if ($res) {
                        $result['status'] = 1;
                        $result['msg'] = '删除报告成功';
                    } else {
                        $result['status'] = -1;
                        $result['msg'] = '删除报告失败！';
                    }
                    break;
                case 'delpic':
                    $fileid = I('POST.id');
                    $res = $qualityModel->updateData('quality_details_file', array('is_delete' => 1), array('file_id' => $fileid));
                    if ($res) {
                        $result['status'] = 1;
                        $result['msg'] = '删除照片成功';
                    } else {
                        $result['status'] = -1;
                        $result['msg'] = '删除照片失败！';
                    }
                    break;
                case 'getpic':
                    $qsid = I('POST.id');
                    $res = $qualityModel->DB_get_all('quality_details_file', 'file_id,type,file_name,file_url', array('is_delete' => 0, 'qsid' => $qsid));
                    $file_data = [];
                    foreach ($res as &$v) {
                        $v['file_url'] = urlencode($v['file_url']);
                        $file_data[$v['type']][] = $v;
                    }
                    $result['status'] = 1;
                    $result['file_data'] = $file_data;
                    break;
                case 'keepquality':
                    //暂存数据
                    $result = $qualityModel->keepquality();
                    break;
                default:
                    //保存明细结果
                    $result = $qualityModel->saveDetail();

                    if ($result['status'] == 1) {
                        //记录日志
                        $qualityModel->addLog('quality_details', M()->getLastSql(), session('username') . '录入了一个设备质控计划明细（计划ID为：' . I('POST.qsid') . '）', I('POST.qsid'), '');
                    }
                    break;
            }
            $this->ajaxReturn($result);
        } else {
            if (I('GET.type') == 'uploadpic') {
                $qsid = I('GET.qsid');
                //生成二维码
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/uploadReport?id=' . $qsid . '&i=qsid&t=quality_details&username=' . session('username');
                $codeUrl = $qualityModel->createCodePic($url);
                $report = $qualityModel->DB_get_one('quality_details', 'report', array('qsid' => $qsid));
                $codeUrl = trim($codeUrl, '.');
                $this->assign('codeUrl', $codeUrl);
                $this->assign('qsid', $qsid);
                $this->assign('report', $report['report']);
                $this->display('uploadpic');
            } else {
                $qsid = I('GET.qsid');
                $qsInfo = $qualityModel->DB_get_one('quality_starts', '*', array('qsid' => $qsid));
                //查询质控模板、检测仪器、检测依据
                $template = $qualityModel->DB_get_one('quality_templates', 'name,template_name', array('qtemid' => $qsInfo['qtemid']));
                $qsInfo['template'] = $template['name'];
                $basis = $qualityModel->DB_get_one('quality_detection_basis', 'group_concat(basis separator "、") as basis', array('qdbid' => array('in', $qsInfo['qdbid'])));
                $qsInfo['basis'] = $basis['basis'];
                $instrument = $qualityModel->DB_get_one('quality_instruments', 'instrument', array('qiid' => $qsInfo['qiid']));
                $qsInfo['instrument'] = $instrument['instrument'];
                $setting = json_decode($qsInfo['start_preset'], true);
                $values = $type = $codeUrl = [];
                foreach ($setting as $k => $v) {
                    $type[] = $k;
                    $preset = $qualityModel->DB_get_one('quality_preset', '*', array('detection_Ename' => $k));
                    $preset['set'] = $v;
                    $values[] = $preset;
                }
                $type[] = 'nameplate';
                $type[] = 'instrument_view';
                foreach ($type as $k => $v) {
                    //生成二维码
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                    $url = "$protocol".C('HTTP_HOST'). C('ADMIN_NAME').'/Public/uploadReport?id=' . $qsid . '&i=qsid&t=quality_details_file&type=' . $v . '&username=' . session('username');
                    $tmp_url = $qualityModel->createCodePic($url, $v . '_' . $qsid . '_');
                    $codeUrl[$v] = trim($tmp_url, '.');
                }
                $this->assign('codeUrl', $codeUrl);
                //查询质控照片上传信息
                $files = $qualityModel->DB_get_all('quality_details_file', 'file_id,type,file_name,file_url', array('qsid' => $qsid, 'is_delete' => 0));
                $file_data = [];
                foreach ($files as $k => $v) {
                    $file_data[$v['type']][] = $v;
                }
                //查询明细信息
                $detail_data = $qualityModel->DB_get_one('quality_details', '*', array('qsid' => $qsid));
                if ($detail_data) {
                    $detail_data['preset_detection'] = json_decode($detail_data['preset_detection'], true);
                    $detail_data['fixed_detection'] = json_decode($detail_data['fixed_detection'], true);
                }
                //当无明细信息获取暂存信息
                if (!$detail_data&&$qsInfo['keepdata']) {
                    $keepdata = json_decode($qsInfo['keepdata'],true);
                    $detail_data['exterior'] = $keepdata['lookslike'];
                    $detail_data['exterior_explain'] = $keepdata['lookslike_desc'];
                    $detail_data['remark'] = $keepdata['total_desc'];
                    $detail_data['preset_detection'] = $keepdata;
                    $detail_data['fixed_detection'] = $keepdata;
                }
                //查询设备信息
                $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,brand,serialnum,model,departid,catid,opendate,lasttesttime,lasttestuser,lasttestresult', array('assid' => $qsInfo['assid']));
                $departname = array();
                $catname = array();
                include APP_PATH . "Common/cache/category.cache.php";
                include APP_PATH . "Common/cache/department.cache.php";

                $asInfo['department'] = $departname[$asInfo['departid']]['department'];
                $asInfo['opendate'] = HandleEmptyNull($asInfo['opendate']);
                $asInfo['category'] = $catname[$asInfo['catid']]['category'];
                $this->assign('qsInfo', $qsInfo);
                $this->assign('asInfo', $asInfo);
                $this->assign('values', $values);
                $this->assign('templatename', $template['name']);
                $this->assign('file_data', $file_data);
                $this->assign('detail_data', $detail_data);
                $this->assign('date', getHandleTime(time()));
                $this->display('Qualities/Quality/detail/' . $template['template_name']);
            }
        }
    }

    private function getPoints($nums)
    {
        $qualityModel = new QualityModel();
        $points = $qualityModel->DB_get_all('patrol_points', '*', array('num' => array('in', $nums)));
        $parentid = $data = [];
        foreach ($points as $k => $v) {
            $parentid[] = $v['parentid'];
        }
        $pars = $qualityModel->DB_get_all('patrol_points', '*', array('ppid' => array('in', $parentid)));
        foreach ($pars as $k => $v) {
            $data[$k]['ppid'] = $v['ppid'];
            $data[$k]['num'] = $v['num'];
            $data[$k]['name'] = $v['name'];
            $n = 0;
            foreach ($points as $k1 => $v1) {
                if ($v1['parentid'] == $v['ppid']) {
                    $data[$k]['detail'][$n]['ppid'] = $v1['ppid'];
                    $data[$k]['detail'][$n]['parentid'] = $v1['parentid'];
                    $data[$k]['detail'][$n]['num'] = $v1['num'];
                    $data[$k]['detail'][$n]['name'] = $v1['name'];
                    $data[$k]['detail'][$n]['result'] = $v1['result'];
                    $data[$k]['detail'][$n]['remark'] = $v1['remark'];
                    $n++;
                }
            }
        }
        return $data;
    }

    /**
     * Notes: 查看录入明细
     */
    public function showDetail()
    {
        $qualityModel = new QualityModel();
        $qsid = I('GET.qsid');
        //查询明细记录
        $detailInfo = $qualityModel->DB_get_one('quality_details', '*', array('qsid' => $qsid));
        if (!$detailInfo) {
            $this->error('请先录入明细！');
        }
        $detailInfo['preset_detection'] = json_decode($detailInfo['preset_detection'], true);
        $detailInfo['fixed_detection'] = json_decode($detailInfo['fixed_detection'], true);
        $qsInfo = $qualityModel->DB_get_one('quality_starts', '*', array('qsid' => $qsid));
        //查询质控模板、检测仪器、检测依据
        $template = $qualityModel->DB_get_one('quality_templates', 'name,template_name', array('qtemid' => $qsInfo['qtemid']));
        $qsInfo['template'] = $template['name'];
        $basis = $qualityModel->DB_get_one('quality_detection_basis', 'group_concat(basis separator "、") as basis', array('qdbid' => array('in', $qsInfo['qdbid'])));
        $qsInfo['basis'] = $basis['basis'];
        $instrument = $qualityModel->DB_get_one('quality_instruments', 'instrument', array('qiid' => $qsInfo['qiid']));
        $qsInfo['instrument'] = $instrument['instrument'];
        $setting = json_decode($qsInfo['start_preset'], true);
        $values = array();
        foreach ($setting as $k => $v) {
            $preset = $qualityModel->DB_get_one('quality_preset', '*', array('detection_Ename' => $k));
            $preset['set'] = $v;
            $values[] = $preset;
        }


        //查询铭牌照片信息
        $files = $qualityModel->DB_get_all('quality_details_file', '*', array('qsid' => $qsid, 'is_delete' => 0));
        //查询设备信息
        $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,brand,serialnum,model,departid,catid,opendate,lasttesttime,lasttestuser,lasttestresult', array('assid' => $qsInfo['assid']));
        $departname = $file_data = array();
        foreach ($files as &$v) {
            $v['file_url'] = urlencode($v['file_url']);
            $file_data[$v['type']][] = $v;
        }
        $catname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $asInfo['opendate'] = HandleEmptyNull($asInfo['opendate']);
        $asInfo['department'] = $departname[$asInfo['departid']]['department'];
        $asInfo['category'] = $catname[$asInfo['catid']]['category'];
        $this->assign('qsInfo', $qsInfo);
        $this->assign('asInfo', $asInfo);
        $this->assign('values', $values);
        $this->assign('file_data', $file_data);
        $this->assign('templatename', $template['name']);
        //通用电气应用总结
        if ($detailInfo['preset_detection']['patient_normal_result']==1&&$detailInfo['preset_detection']['patient_abnormal_result']==1&&$detailInfo['preset_detection']['aid_normal_result']==1&&$detailInfo['preset_detection']['aid_abnormal_result']==1) {
            $detailInfo['preset_detection']['summary_result']=1;
        }else{
            $detailInfo['preset_detection']['summary_result']=2;
        }
        $this->assign('detailInfo', $detailInfo);
        $this->assign('date', getHandleTime(time()));
        $this->display('Qualities/Quality/detail/Show_' . $template['template_name']);
    }

    /**
     * Notes: 修改明细
     */
    public function editQualityDetail()
    {
        $qualityModel = new QualityModel();
        if (IS_POST) {

        } else {
            $qsid = I('GET.qsid');
            $qsInfo = $qualityModel->DB_get_one('quality_starts', '*', array('qsid' => $qsid));
            //查询质控模板、检测仪器、检测依据
            $template = $qualityModel->DB_get_one('quality_templates', 'name,template_name', array('qtemid' => $qsInfo['qtemid']));
            $qsInfo['template'] = $template['name'];
            $basis = $qualityModel->DB_get_one('quality_detection_basis', 'group_concat(basis separator "、") as basis', array('qdbid' => array('in', $qsInfo['qdbid'])));
            $qsInfo['basis'] = $basis['basis'];
            $instrument = $qualityModel->DB_get_one('quality_instruments', 'instrument', array('qiid' => $qsInfo['qiid']));
            $qsInfo['instrument'] = $instrument['instrument'];
            $setting = json_decode($qsInfo['start_preset'], true);
            $values = $type = $codeUrl = [];
            foreach ($setting as $k => $v) {
                $type[] = $k;
                $preset = $qualityModel->DB_get_one('quality_preset', '*', array('detection_Ename' => $k));
                $preset['set'] = $v;
                $values[] = $preset;
            }
            $type[] = 'nameplate';
            $type[] = 'instrument_view';
            foreach ($type as $k => $v) {
                //生成二维码
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/uploadReport?id=' . $qsid . '&i=qsid&t=quality_details_file&type=' . $v . '&username=' . session('username');
                $tmp_url = $qualityModel->createCodePic($url, $v . '_' . $qsid . '_');
                $codeUrl[$v] = trim($tmp_url, '.');
            }
            $this->assign('codeUrl', $codeUrl);
            //查询质控照片上传信息
            $files = $qualityModel->DB_get_all('quality_details_file', 'file_id,type,file_name,file_url', array('qsid' => $qsid, 'is_delete' => 0));
            $file_data = [];
            foreach ($files as &$v) {
                $v['file_url'] = urlencode($v['file_url']);
                $file_data[$v['type']][] = $v;
            }
            //查询明细信息
            $detail_data = $qualityModel->DB_get_one('quality_details', '*', array('qsid' => $qsid));
            if ($detail_data) {
                $detail_data['preset_detection'] = json_decode($detail_data['preset_detection'], true);
                $detail_data['fixed_detection'] = json_decode($detail_data['fixed_detection'], true);
            }
            //查询设备信息
            $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,brand,serialnum,model,departid,catid,opendate,lasttesttime,lasttestuser,lasttestresult', array('assid' => $qsInfo['assid']));

            $departname = array();
            $catname = array();
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            $asInfo['department'] = $departname[$asInfo['departid']]['department'];
            $asInfo['opendate'] = HandleEmptyNull($asInfo['opendate']);
            $asInfo['category'] = $catname[$asInfo['catid']]['category'];
            $this->assign('qsInfo', $qsInfo);
            $this->assign('asInfo', $asInfo);
            $this->assign('values', $values);
            $this->assign('templatename', $template['name']);
            $this->assign('file_data', $file_data);
            $this->assign('detail_data', $detail_data);
            $this->assign('date', getHandleTime(time()));
            $this->display('Qualities/Quality/detail/' . $template['template_name']);
        }
    }

    /**
     * 质控明细计划详情
     */
    public function showQualityDetail()
    {
        $this->display();
    }

    /**
     * 检测仪器管理
     */
    public function getDetectingList()
    {
        $qualityModel = new QualityModel();
        if (IS_POST) {
            $type = I('POST.actionType');
            if ($type == 'add') {
                //新增仪器
                $result = $qualityModel->addInstruments();
            } elseif ($type == 'update') {
                //修改仪器
                $result = $qualityModel->updateInstruments();
            } elseif ($type == 'upload') {
                $result = $qualityModel->uploadReport(C('UPLOAD_DIR_QUALITY_REPORT_INSTRUMENTS_PIC_NAME'));
            } else {
                //删除仪器
                $result = $qualityModel->deleteInstruments();
            }
            $this->ajaxReturn($result);
        } else {
            $instruments = $qualityModel->DB_get_all('quality_instruments', '*', array('1'));
            foreach ($instruments as $k => $v) {
                $jpghtml = '<div class="layui-btn-group">';
                if ($v['metering_report']) {
                    $filenamearr = explode('/', $v['metering_report']);
                    $filename = $filenamearr[count($filenamearr) - 1];
                    $supplement = 'data-path="' . $v['metering_report'] . '" data-name="' . $filename . '"';
                    $jpghtml .= $qualityModel->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' showFile', '', $supplement);
                    $jpghtml .= $qualityModel->returnListLink('下载', '', '', C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                }
                $jpghtml .= '</div>';
                $instruments[$k]['html'] = $jpghtml;
            }
            $this->assign('data', $instruments);
            $this->assign('getDetectingList', get_url());
            $this->display();
        }
    }

    /**
     * Notes: 质控结果查询
     */
    public function qualityResult()
    {
        $qualityModel = new QualityModel();
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'getReportUrl':
                    $result = $qualityModel->getReportUrl();
                    $this->ajaxReturn($result);
                    break;
                case 'batchPrint':
                    $this->batchPrint();
                    break;
                case 'printResults':
                    $this->printResults();
                    break;
                case 'batchExport':
                    $result = $this->batchExport();
                    $this->ajaxReturn($result);
                    break;
                case 'get_pic_data':
                    $result = $qualityModel->get_pic_data();
                    $this->ajaxReturn($result);
                    break;
                case 'save_excel_pic':
                    $result = $qualityModel->save_excel_pic();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $result = $qualityModel->getAllPlanResult();
                    $this->ajaxReturn($result);
            }
        } else {
            $departments = $this->getSelectDepartments();
            //获取模板数据
            $templates = $qualityModel->DB_get_all('quality_templates', '*', array('1'));
            $this->assign('department', $departments);
            $this->assign('templates', $templates);
            $this->assign('qualityResult', get_url());
            $this->display();
        }
    }

    /**
     * Notes: 批量打印设备模板
     */
    public function batchPrint()
    {
        $qsid = explode(',', trim(I('post.qsid'), ','));
        $qualityModel = new QualityModel();
        $userModel = new UserModel();
        $html = '';
        foreach ($qsid as $key => $value) {
            $qsInfo = $qualityModel->DB_get_one('quality_starts', '*', array('qsid' => $value));
            //查询质控模板、检测仪器、检测依据
            $template = $qualityModel->DB_get_one('quality_templates', 'name,template_name', array('qtemid' => $qsInfo['qtemid']));
            $qsInfo['template'] = $template['name'];
            $basis = $qualityModel->DB_get_one('quality_detection_basis', 'group_concat(basis separator "、") as basis', array('qdbid' => array('in', $qsInfo['qdbid'])));
            $qsInfo['basis'] = $basis['basis'];
            $instrument = $qualityModel->DB_get_one('quality_instruments', 'instrument', array('qiid' => $qsInfo['qiid']));
            $qsInfo['instrument'] = $instrument['instrument'];
            $setting = json_decode($qsInfo['start_preset'], true);
            $values = $tolerance = [];
            foreach ($setting as $k => $v) {
                $preset = $qualityModel->DB_get_one('quality_preset', '*', array('detection_Ename' => $k));
                $preset['set'] = $v;
                $values[] = $preset;
            }
            foreach ($values as $k => $v) {
                $tolerance[$v['detection_name']] = $v['tolerance'];
            }
            //查询设备信息
            $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,brand,serialnum,model,departid,catid', array('assid' => $qsInfo['assid']));
            $departname = array();
            $catname = array();
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            $asInfo['department'] = $departname[$asInfo['departid']]['department'];
            $asInfo['category'] = $catname[$asInfo['catid']]['category'];
            //经办工程师签名
            $qsInfoautograph = $qualityModel->get_autograph($qsInfo['username']);
            //设备使用部门负责人签名
            $asInfoautograph = $qualityModel->get_departid_autograph($asInfo['departid']);
            //设备科签名
            $departautograph = $qualityModel->get_assets_autograph();
            /*
            隐藏模板签名
            $this->assign('qsInfoautograph', $qsInfoautograph);
            $this->assign('asInfoautograph', $asInfoautograph);
            $this->assign('departautograph', $departautograph);
            $this->assign('qsInfoautograph_time', $qsInfo['end_date']);
            $this->assign('asInfoautograph_time', date('Y-m-d', time()));
            $this->assign('departautograph_time', date('Y-m-d', time()));*/
            $this->assign('qsInfo', $qsInfo);
            $this->assign('asInfo', $asInfo);
            $this->assign('values', $values);
            $this->assign('tolerance', $tolerance);
            $this->assign('date', getHandleTime(time()));
            $html .= $this->display('Qualities/Quality/templates/' . $template['template_name']);
        }
        echo $html;
        exit;
    }

    /**
     * Notes: 批量打印质控结果
     */
    public function printResults()
    {
        $qsid = explode(',', trim(I('post.qsid'), ','));
        $qualityModel = new QualityModel();
        $userModel = new UserModel();
        $html = '';
        foreach ($qsid as $key => $value) {
            //查询明细记录
            $detailInfo = $qualityModel->DB_get_one('quality_details', '*', array('qsid' => $value));
            $detailInfo['preset_detection'] = json_decode($detailInfo['preset_detection'], true);
            $detailInfo['fixed_detection'] = json_decode($detailInfo['fixed_detection'], true);

            $qsInfo = $qualityModel->DB_get_one('quality_starts', '*', array('qsid' => $value));
            //查询质控模板、检测仪器、检测依据
            $template = $qualityModel->DB_get_one('quality_templates', 'name,template_name', array('qtemid' => $qsInfo['qtemid']));
            $qsInfo['template'] = $template['name'];
            $basis = $qualityModel->DB_get_one('quality_detection_basis', 'group_concat(basis separator "、") as basis', array('qdbid' => array('in', $qsInfo['qdbid'])));
            $qsInfo['basis'] = $basis['basis'];
            $instrument = $qualityModel->DB_get_one('quality_instruments', 'instrument', array('qiid' => $qsInfo['qiid']));
            $qsInfo['instrument'] = $instrument['instrument'];
            $setting = json_decode($qsInfo['start_preset'], true);
            $values = $tolerance = [];
            foreach ($setting as $k => $v) {
                $preset = $qualityModel->DB_get_one('quality_preset', '*', array('detection_Ename' => $k));
                $preset['set'] = $v;
                $values[] = $preset;
            }
            foreach ($values as $k => $v) {
                $tolerance[$v['detection_name']] = $v['tolerance'];
            }
            //查询设备信息
            $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,brand,serialnum,model,departid,catid', array('assid' => $qsInfo['assid']));
            $departname = array();
            $catname = array();
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            $asInfo['department'] = $departname[$asInfo['departid']]['department'];
            $asInfo['category'] = $catname[$asInfo['catid']]['category'];
            //经办工程师签名
            $qsInfoautograph = $qualityModel->get_autograph($qsInfo['username']);
            //设备使用部门负责人签名
            $asInfoautograph = $qualityModel->get_departid_autograph($asInfo['departid']);
            //设备科签名
            $departautograph = $qualityModel->get_assets_autograph();
            $this->assign('qsInfoautograph', $qsInfoautograph);
            $this->assign('asInfoautograph', $asInfoautograph);
            $this->assign('departautograph', $departautograph);
            $this->assign('qsInfoautograph_time', $qsInfo['end_date']);
            $this->assign('asInfoautograph_time', date('Y-m-d', time()));
            $this->assign('departautograph_time', date('Y-m-d', time()));
            $this->assign('qsInfo', $qsInfo);
            $this->assign('asInfo', $asInfo);
            $this->assign('values', $values);
            $this->assign('tolerance', $tolerance);
            if ($detailInfo['preset_detection']['patient_normal_result']==1&&$detailInfo['preset_detection']['patient_abnormal_result']==1&&$detailInfo['preset_detection']['aid_normal_result']==1&&$detailInfo['preset_detection']['aid_abnormal_result']==1) {
                $detailInfo['preset_detection']['summary_result']=1;
            }else{
                $detailInfo['preset_detection']['summary_result']=2;
            }
            $this->assign('detailInfo', $detailInfo);
            $this->assign('date', getHandleTime(time()));
            $html .= $this->display('Qualities/Quality/printresults/' . $template['template_name']);
        }
        echo $html;
        exit;
    }

    /**
     * Notes: 批量导出质控报表数据
     */
    public function batchExport()
    {
        $hospital_id = session('current_hospitalid');
        $qsid = explode(',', trim(I('post.qsid'), ','));
        //查询质控设备的启用值等信息
        $qualityModel = new QualityModel();
        $data = $qualityModel->get_complete_detail($qsid, $hospital_id);
        //查询模板
        $tempname = $qualityModel->DB_get_all('quality_templates', '*', array('1'));
        if (!$tempname) {
            exit('暂无质控模板！');
        }
        $jianhuyi = $shuye = $chuchanyi = $huxiji = $tongyongdianqi = [];
        if (!$data) {
            exit('暂无可导出的数据！');
        }
        $assids = [];
        foreach ($data as $k => $v) {
            $assids[] = $v['assid'];
        }
        $assInfo = $qualityModel->getAssetsInfo($assids);
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k => $v) {
            switch ($v['template_name']) {
                case 'Tem_JianHuYi':
                    $jianhuyi[] = $v;
                    break;
                case 'Tem_ShuYeZhuangZhi':
                    $shuye[] = $v;
                    break;
                case 'Tem_ChuChanYi':
                    $chuchanyi[] = $v;
                    break;
                case 'Tem_HuXiJi':
                    $huxiji[] = $v;
                    break;
                case 'Tem_TongYongDianQi':
                    $tongyongdianqi[] = $v;
                    break;
            }
        }
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $xlsName = "质控结果报表分析";
        $xlsTitle = iconv('utf-8', 'gb2312', $xlsName);//文件名称
        $fileName = $xlsName;//or $xlsTitle 文件名称可根据自己情况设定
        //生成工作表及写入表头数据
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF');
        foreach ($tempname as $k => $v) {
            $objPHPExcel->createSheet();
            $objPHPExcel->setactivesheetindex($k);
            $objPHPExcel->getActiveSheet()->setTitle($v['name'] . '质控模板');
            switch ($v['template_name']) {
                case 'Tem_JianHuYi':
                    $qualityModel->write_sheet_header_jianhuyi($cellName, $objPHPExcel);
                    break;
                case 'Tem_ShuYeZhuangZhi':
                    $objPHPExcel = $qualityModel->write_sheet_header_shuyezhuangzhi($cellName, $objPHPExcel);
                    break;
                case 'Tem_ChuChanYi':
                    //定位到对应活动表
                    $objPHPExcel->setactivesheetindex($k);
                    //写入表头数据
                    $objPHPExcel = $qualityModel->write_sheet_header_chuchanyi($cellName, $objPHPExcel);
                    break;
                case 'Tem_HuXiJi':
                    $qualityModel->write_sheet_header_huxiji($cellName, $objPHPExcel);
                    break;
                case 'Tem_TongYongDianQi':
                    $qualityModel->write_sheet_header_tongyongdianqi($cellName, $objPHPExcel);
                    break;
            }
        }
        foreach ($tempname as $k => $v) {
            //定位到对应活动表
            $objPHPExcel->setactivesheetindex($k);
            switch ($v['template_name']) {
                case 'Tem_JianHuYi':
                    //格式化数据
                    $jianhuyi_data = $qualityModel->format_data_jianhuyi($jianhuyi, $assInfo, $departname);
                    if ($jianhuyi_data) {
                        //写入数据
                        $objPHPExcel = $qualityModel->write_jianhuyi_data($cellName, $jianhuyi_data, $objPHPExcel);
                    }
                    break;
                case 'Tem_ShuYeZhuangZhi':
                    //格式化数据
                    $shuye_data = $qualityModel->format_data_shuye($shuye, $assInfo, $departname);
                    if ($shuye_data) {
                        //写入数据
                        $objPHPExcel = $qualityModel->write_shuye_data($cellName, $shuye_data, $objPHPExcel);
                    }
                    break;
                case 'Tem_ChuChanYi':
                    //格式化数据
                    $chuchanyi_data = $qualityModel->format_data_chuchanyi($chuchanyi, $assInfo, $departname);
                    if ($chuchanyi_data) {
                        //写入数据
                        $objPHPExcel = $qualityModel->write_chanchuyi_data($cellName, $chuchanyi_data, $objPHPExcel);
                    }
                    break;
                case 'Tem_HuXiJi':
                    //格式化数据
                    $huxiji_data = $qualityModel->format_data_huxiji($huxiji, $assInfo, $departname);
                    if ($huxiji_data) {
                        //写入数据
                        $objPHPExcel = $qualityModel->write_huxiji_data($cellName, $huxiji_data, $objPHPExcel);
                    }
                    break;
                case 'Tem_TongYongDianQi':
                    //格式化数据
                    $tongyongdianqi_data = $qualityModel->format_data_tongyongdianqi($tongyongdianqi, $assInfo, $departname);

                    if ($tongyongdianqi_data) {
                        //写入数据
                        $objPHPExcel = $qualityModel->write_tongyongdianqi_data($cellName, $tongyongdianqi_data, $objPHPExcel);
                    }
                    break;
            }
        }
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
