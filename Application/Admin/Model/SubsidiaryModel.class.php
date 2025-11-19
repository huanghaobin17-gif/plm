<?php

namespace Admin\Model;

use Admin\Controller\Tool\ToolController;
use Think\Model;
use Think\Model\RelationModel;

class SubsidiaryModel extends CommonModel
{
    protected $len = 30;
    protected $tablePrefix = 'sb_';
    protected $tableName = 'subsidiary_allot';
    protected $MODULE = 'Assets';
    protected $Controller = 'Subsidiary';

    //附属设备分配列表
    public function subsidiaryAllotList(){
        $departid = I('POST.departid');
        $model = I('POST.assetsModel');
        $assetsName = I('POST.assetsName');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order') ? I('POST.order') : 'desc';
        $sort = I('POST.sort') ? I('POST.sort') : 'assid';
        if(session('isSuper')!=C('YES_STATUS')){
            //筛选科室 获取除用户本身工作的科室
            if (!session('job_departid')) {
                $result['msg'] = '该用户未分配工作科室';
                $result['code'] = 400;
                return $result;
            }
            $where['departid'][] = array('IN', session('job_departid'));
        }

        $where['status'][0] = 'NOTIN';
        $where['status'][1][] = C('ASSETS_STATUS_SCRAP');
        $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        //借调主设备
        $where['is_subsidiary']=C('YES_STATUS');
        $where['main_assid']=0;

        $where['hospital_id'] = session('current_hospitalid');

        if ($departid) {
            $where['departid'][] = array('IN', $departid);
        }

        if ($model) {
            $where['model'] = array('LIKE', '%' . $model . '%');
        }

        if ($assetsName) {
            $where['assets'] = array('LIKE', '%' . $assetsName . '%');
        }
        $fileds = 'departid,assets,assnum,assets,brand,model,status,assid,buy_price';
        $total = $this->DB_get_count('assets_info', $where);
        $data = $this->DB_get_all('assets_info', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $assid = [];
        foreach ($data as &$dataV) {
            $assid[] = $dataV['assid'];
        }
        $allotWhere['assid']=['IN',$assid];
        $allotWhere['status']=['NOTIN',[C('SUBSIDIARY_STATUS_FAIL'),C('SUBSIDIARY_STATUS_COMPLETE')]];
        $allot=$this->DB_get_all('subsidiary_allot','assid',$allotWhere);
        $allotData=[];
        if($allot){
            foreach ($allot as &$alloutV){
                $allotData[$alloutV['assid']]=true;
            }
        }
        $allotMenu = get_menu($this->MODULE, $this->Controller, 'subsidiaryAllot');
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $disabled = $this->returnListLink('分配申请', '', '', C('BTN_CURRENCY') . ' layui-btn-disabled');
        foreach ($data as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
            if (!$showPrice) {
                $one['buy_price'] = '***';
            }
            if ($allotMenu ){
                if($allotData[$one['assid']]){
                    $one['operation'] =$disabled;
                }else{
                    $one['operation'] = $this->returnListLink($allotMenu['actionname'], $allotMenu['actionurl'], 'subsidiaryAllot', C('BTN_CURRENCY'));
                }
            } else {
                $one['operation'] =$disabled;
            }
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //附属设备归属分配操作
    public function subsidiaryAllot(){
        $assid=trim(I('POST.assid'));
        $departid=trim(I('POST.departid'));
        $main_assid=trim(I('POST.main_assid'));
        $main_assets=trim(I('POST.main_assets'));
        $assetsrespon=trim(I('POST.assetsrespon'));
        $address=trim(I('POST.address'));
        $managedepart=trim(I('POST.managedepart'));
        $remark=trim(I('POST.remark'));
        $this->checkstatus(judgeNum($assid), '非法操作！');
        $this->checkstatus(judgeNum($departid), '请选择附属设备分配至的科室！');
        $this->checkstatus(judgeNum($main_assid), '请选择主设备！');
        $this->checkstatus(judgeEmpty($main_assets), '数据异常！');
        $this->checkstatus(judgeEmpty($managedepart), '管理科室获取异常,请重新选择！');
        $this->checkstatus(judgeEmpty($assetsrespon), '请补充资产负责人！');
        $files = 'assid,hospital_id,departid,assets,status,quality_in_plan,patrol_in_plan,buy_price';
        $where['assid'] = array('EQ', $assid);
        $where['main_assid'] = array('EQ', 0);
        $assets = $this->DB_get_one('assets_info', $files, $where);
        if(!$assets){
            die(json_encode(array('status' => -1, 'msg' => '已分配，此设备不需要申请！')));
        }
        $subWhere['status']=['NOTIN',[C('SUBSIDIARY_STATUS_COMPLETE'),C('SUBSIDIARY_STATUS_FAIL')]];
        $subWhere['assid']=['EQ',$assid];
        $subData=$this->DB_get_one('subsidiary_allot','assid',$subWhere);
        if($subData){
            die(json_encode(array('status' => -1, 'msg' => '已申请请勿重复操作')));
        }
        //查询是否已开启附属设备分配审批
        $isOpenApprove = $this->checkApproveIsOpen(C('SUBSIDIARY_APPROVE'), $assets['hospital_id']);
        $approve=[];
        if ($isOpenApprove) {
            //查询是否已设置审批流程
            $isSetProcess = $this->checkApproveIsSetProcess(C('SUBSIDIARY_APPROVE'), $assets['hospital_id']);
            if(!$isSetProcess){
                die(json_encode(array('status' => -1, 'msg' => '未设置审批流程，请联系管理员设置！')));
            }
            $data['status'] = C('SUBSIDIARY_STATUS_APPROVE');//默认为未审核
            //获取审批流程

            $approve_process_user=$this->get_approve_process($assets['buy_price'],C('SUBSIDIARY_APPROVE'),$assets['hospital_id']);
            //并且获取下次审批人
            $approve = $this->check_approve_process($assets['departid'],$approve_process_user,1);
            if ($approve['all_approver'] == '') {
                //不在审核范围内 不需要审批
                $data['approve_status'] = C('STATUS_APPROE_UNWANTED');
            } else {
                //默认为未审核
                $data['current_approver']=$approve['current_approver'];
                $data['not_complete_approver']=str_replace('/','',$approve['all_approver']);
                $data['all_approver']=$approve['all_approver'];
                $data['approve_status'] = C('APPROVE_STATUS');
            }
        } else {
            //跳过审核直接到验收
            $data['status'] = C('SUBSIDIARY_STATUS_ACCEPTANCE_CHECK');
            $data['approve_status'] = C('STATUS_APPROE_UNWANTED');//不需审核
        }

        $data['assid']=$assid;
        $data['assets']=$assets['assets'];
        $data['hospital_id']=$assets['hospital_id'];
        $data['main_assid']=$main_assid;
        $data['main_assets']=$main_assets;
        $data['main_departid']=$departid;
        $data['main_managedepart']=$managedepart;
        $data['main_address']=$address;
        $data['main_assetsrespon']=$assetsrespon;
        $data['remark']=$remark;
        $data['apply_user']=session('username');
        $data['apply_date']=getHandleTime(time());
        $add = $this->insertData('subsidiary_allot', $data);
        if($add){
            //日志行为记录文字
            $log['assets'] = $assets['assets'];
            $log['main_assets'] = $assets['main_assets'];
            $text = getLogText('applySubsidiaryLogText', $log);
            $this->addLog('subsidiary_allot', M()->getLastSql(), $text, $add);
            //==========================================短信 START==========================================
            $settingData = $this->checkSmsIsOpen($this->Controller);
            if ($settingData && $approve['this_current_approver']) {
                //有开启短信 并且需要通知审批人
                $departname = [];
                include APP_PATH . "Common/cache/department.cache.php";
                $data['department'] = $departname[$assets['departid']]['department'];
                $data['allot_department'] = $departname[$data['main_departid']]['department'];
                $data['assets']=$assets['assets'];
                $data['allot_assets']=$data['main_assets'];
                //通知审批人审批
                $where=[];
                $where['status']=C('OPEN_STATUS');
                $where['is_delete']=C('NO_STATUS');
                $where['username']=$approve['this_current_approver'];
                $approve_user=$this->DB_get_one('user','telephone',$where);
                if($settingData['doApprove']['status'] == C('OPEN_STATUS') && $approve_user['telephone']){
                    $sms = $this->formatSmsContent($settingData['doApprove']['content'], $data);
                    ToolController::sendingSMS($approve_user['telephone'], $sms, $this->Controller, $add);
                }
            }
            //==========================================短信 END==========================================
            return array('status' => 1, 'msg' => '提交成功');
        }else{
            return array('status' => 1, 'msg' => '提交失败');
        }
    }

    //附属设备分配审批列表
    public function subsidiaryApproveList(){
        $departid = I('POST.departid');
        $assetsName = I('POST.assetsName');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order')?I('POST.order'):'DESC';
        $sort = I('POST.sort')? I('POST.sort'):'allotid';
        $where['approve_status'] = array('NEQ', C('STATUS_APPROE_UNWANTED'));
        $where['all_approver']=array('LIKE','%/'.session('username').'/%');
        if ($departid) {
            $where['main_departid'] = array('EQ', $departid);
        }
        if ($assetsName) {
            $where['assets'] = array('LIKE', '%' . $assetsName . '%');
        }
        $where['hospital_id'] = session('current_hospitalid');
        //获取审批列表信息
        $fileds = 'allotid,assid,assets,main_assid,main_assets,main_departid,remark,apply_user,apply_date,status,approve_status,
        current_approver,complete_approver,not_complete_approver,all_approver';
        $total = $this->DB_get_count('subsidiary_allot', $where);
        $data = $this->DB_get_all('subsidiary_allot', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        $Apply = get_menu($this->MODULE, $this->Controller, 'subsidiaryApprove');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$dataV) {
            //详情url
            $dataV['department'] = $departname[$dataV['main_departid']]['department'];
            $detailsUrl = get_url() . '?action=showAllotDetails&allotid=' . $dataV['allotid'] . '&assid=' . $dataV['assid'];
            if ($dataV['approve_status'] == C('STATUS_APPROE_SUCCESS')) {
                //审批完成
                $dataV['operation'] = $this->returnListLink('已通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY').' layui-btn-normal');
            } elseif ($dataV['approve_status'] == C('STATUS_APPROE_FAIL')) {
                //审批失败
                $dataV['operation'] = $this->returnListLink('不通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-danger');
            } else {
                if ($Apply && $dataV['current_approver']) {
                    $current_approver=explode(',',$dataV['current_approver']);
                    $current_approver_arr=[];
                    foreach($current_approver as &$current_approver_value){
                        $current_approver_arr[$current_approver_value]=true;
                    }
                    if($current_approver_arr[session('username')]){
                        $dataV['operation']= $this->returnListLink('审批', $Apply['actionurl'], 'subsidiaryApprove', C('BTN_CURRENCY'));
                    }else{
                        $complete = explode(',',$dataV['complete_approver']);
                        $notcomplete = explode(',',$dataV['not_complete_approver']);
                        if(!in_array(session('username'),$complete) && in_array(session('username'),$notcomplete)){
                            //完全未审
                            $dataV['operation']= $this->returnListLink('待审批', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-warm');
                        }elseif(in_array(session('username'),$complete) && in_array(session('username'),$notcomplete)){
                            //有已审，有未审
                            $dataV['operation']= $this->returnListLink('待审批', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-warm');
                        }elseif(in_array(session('username'),$complete) && !in_array(session('username'),$notcomplete)){
                            //全部已审
                            $dataV['operation']= $this->returnListLink('已审批', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-primary');
                        }else{
                            $dataV['operation'] = '';
                        }
                    }
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

    //附属设备分配审批操作
    public function subsidiaryApprove(){
        $allotid = I('POST.allotid');
        $this->checkstatus(judgeNum($allotid), '非法操作');
        //检查是否存在此条记录
        $where['allotid'] = array('EQ', $allotid);
        $allot = $this->DB_get_one('subsidiary_allot', 'allotid,assid,apply_user,apply_date,approve_status,current_approver,complete_approver,all_approver', $where);
        if (!$allot) {
            die(json_encode(array('status' => -1, 'msg' => '无外调信息')));
        }else{
            switch ($allot['approve_status']){
                case C('STATUS_APPROE_UNWANTED'):
                    die(json_encode(array('status' => -1, 'msg' => '不需审批,请按正常流程操作')));
                    break;
                case C('STATUS_APPROE_SUCCESS'):
                    die(json_encode(array('status' => -1, 'msg' => '审批已通过,请勿重复操作')));
                    break;
                case C('STATUS_APPROE_FAIL'):
                    die(json_encode(array('status' => -1, 'msg' => '申请已驳回,请勿重复操作')));
                    break;
            }
        }
        //查询设备信息
        $assInfo = $this->DB_get_one('assets_info','assid,hospital_id,assnum,assets,status,departid,quality_in_plan,patrol_in_plan,buy_price',array('assid'=>$allot['assid']));
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assInfo['buy_price'] = '***';
        }
        $data['allotid']    = $allotid;
        $data['is_adopt'] = I('POST.is_adopt');
        $data['remark']   = trim(I('POST.remark'));
        $data['proposer'] = $allot['apply_user'];
        $data['proposer_time'] = strtotime($allot['apply_date']);
        $data['approver'] = session('username');
        $data['approve_time'] = time();
        $data['approve_class'] = 'subsidiary';
        $data['process_node'] = C('SUBSIDIARY_APPROVE');
        //判断是否是当前审批人
        if ($allot['current_approver']) {
            $current_approver=explode(',',$allot['current_approver']);
            $current_approver_arr=[];
            foreach($current_approver as &$current_approver_value){
                $current_approver_arr[$current_approver_value]=true;
            }
            if($current_approver_arr[session('username')]){
                $processWhere['allotid'] = array('EQ',$allot['allotid']);
                $processWhere['is_delete'] = array('NEQ', C('YES_STATUS'));
                $process=$this->DB_get_count('approve',$processWhere);
                $level=$process+1;
                $data['process_node_level'] = $level;
                $res = $this->addApprove($allot,$data,$assInfo['buy_price'], $assInfo['hospital_id'],$assInfo['departid'],C('SUBSIDIARY_APPROVE'),'subsidiary_allot','allotid');
                if($res['status'] == 1){
                    $text = getLogText('subsidiaryApproveLogText',array('assets'=>$assInfo['assets'],'is_adopt'=>I('POST.is_adopt')));
                    $this->addLog('subsidiary_allot',$res['lastSql'],$text,$allot['allotid'],'');
                }
                return $res;
            }else{
                return array('status' => -1, 'msg' => '请等待审批！');
            }
        }else{
            return array('status' => -1, 'msg' => '审核已结束！');
        }

    }

    //附属设备分配验收列表
    public function subsidiaryCheckList(){

        $departid = I('POST.departid');
        $assetsName = I('POST.assetsName');
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order')?I('POST.order'):'DESC';
        $sort = I('POST.sort')? I('POST.sort'):'allotid';

        $where['status'] = array('IN', [C('SUBSIDIARY_STATUS_ACCEPTANCE_CHECK'),C('SUBSIDIARY_STATUS_COMPLETE')]);
        if ($departid or $assetsName) {
            $assetsWhere=[];
            if ($departid) {
                $assetsWhere['main_departid'] = array('EQ', $departid);
            }
            if ($assetsName) {
                $assetsWhere['assets'] = array('LIKE', '%' . $assetsName . '%');
            }
            $assets = $this->DB_get_all('assets_info', 'assid', $assetsWhere);
            $assetsAssid = [];
            if ($assets) {
                foreach ($assets as &$assetsValue) {
                    $assetsAssid[] = $assetsValue['assid'];
                }
                $where['assid'][] = array('IN', $assetsAssid);
            } else {
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        }
        $where['hospital_id'] = session('current_hospitalid');
        $fileds = 'allotid,assid,assets,main_assid,main_assets,main_departid,apply_user,apply_date,status,check_status,remark';
        $total = $this->DB_get_count('subsidiary_allot', $where);
        $data = $this->DB_get_all('subsidiary_allot', $fileds, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }

        $check = get_menu($this->MODULE, $this->Controller, 'subsidiaryCheck');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$dataV) {
            //详情url
            $dataV['department'] = $departname[$dataV['main_departid']]['department'];
            $detailsUrl = get_url() . '?action=showAllotDetails&allotid=' . $dataV['allotid'] . '&assid=' . $dataV['assid'];
            if ($dataV['status'] == C('SUBSIDIARY_STATUS_COMPLETE')) {
                if($dataV['check_status']==C('YES_STATUS')){
                    $dataV['operation'] = $this->returnListLink('验收通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-normal');
                }else{
                    $dataV['operation'] = $this->returnListLink('验收未通过', $detailsUrl, 'showDetails', C('BTN_CURRENCY') . ' layui-btn-danger');
                }
            } else {
                if ($check) {
                    $dataV['operation'] = $this->returnListLink($check['actionname'], $check['actionurl'], 'subsidiaryCheck', C('BTN_CURRENCY'));
                }else{
                    $dataV['operation'] = $this->returnListLink('待验收', $detailsUrl, 'showDetails', C('BTN_CURRENCY'));
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

    //附属设备分配验操作
    public function subsidiaryCheck(){
        $allotid=trim(I('POST.allotid'));
        $is_adopt=trim(I('POST.is_adopt'));
        $remark=I('POST.remark');
        $check_time=trim(I('POST.check_time'));
        $this->checkstatus(judgeNum($allotid), '非法操作！');
        $allot=$this->DB_get_one('subsidiary_allot','',['allotid'=>['EQ',$allotid]]);
        if(!$allot){
            die(json_encode(array('status' => -1, 'msg' => '数据不存在！')));
        }
        if($allot['status']==C('SUBSIDIARY_STATUS_COMPLETE')){
            die(json_encode(array('status' => -1, 'msg' => '已验收,请勿重复操作！')));
        }
        $this->checkstatus(judgeNum($is_adopt), '非法操作！');
        $this->checkstatus(judgeEmpty($check_time), '请选择验收日期！');
        $data['check_status']=$is_adopt;
        $data['check_user']=session('username');
        $data['check_time']=$check_time;
        $data['check_remrk']=$remark;
        $data['status']=C('SUBSIDIARY_STATUS_COMPLETE');
        $check=$this->updateData('subsidiary_allot',$data,['allotid'=>['EQ',$allotid]]);
        if($check){
            $log['assets'] = $allot['assets'];
            $log['main_assets'] = $allot['main_assets'];
            $log['main_assets'] = $allot['main_assets'];
            $log['is_adopt'] = $is_adopt==C('SUCCESS_STATUS')?'通过':'未通过';
            $text = getLogText('checkSubsidiaryLogText', $log);
            $this->addLog('subsidiary_allot', M()->getLastSql(), $text, $allot['allotid']);
            if($is_adopt==C('SUCCESS_STATUS')){
                //验收通过,修改设备信息
                $assetsData['main_assid']=$allot['main_assid'];
                $assetsData['main_assets']=$allot['main_assets'];
                $assetsData['departid']=$allot['main_departid'];
                $assetsData['managedepart']=$allot['main_managedepart'];
                $assetsData['address']=$allot['main_address'];
                $assetsData['assetsrespon']=$allot['main_assetsrespon'];
                $this->updateData('assets_info',$assetsData,['assid'=>['EQ',$allot['assid']]]);
            }
            return array('status' => 1, 'msg' => '验收成功');
        }else{
            return array('status' => -1, 'msg' => '验收失败！');
        }
    }




    //获取主设备科室的信息
    public function getAssetsDetail(){
        $assid=trim(I('POST.assid'));
        $this->checkstatus(judgeNum($assid), '请选择主设备');
        $where['status'][0] = 'NOTIN';
        $where['status'][1][] = C('ASSETS_STATUS_SCRAP');
        $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        $where['is_subsidiary']=['EQ',C('NO_STATUS')];
        $where['assid']=['EQ',$assid];
        $data=$this->DB_get_one('assets_info','assid,assets,managedepart,address,assetsrespon',$where);
        if($data){
            return array('status' => 1, 'msg' => '获取成功','result'=>$data);
        }else{
            return array('status' => -1, 'msg' => '此设备不符合被分配的条件');
        }
    }

    /**
     * 获取设备基本信息
     * @param $assid int 设备id
     * @return array
     */
    public function getAssetsBasic($assid)
    {
        $where['assid'] = array('EQ', $assid);
        $files = 'assid,catid,assnum,assets,helpcatid,status,brand,model,unit,serialnum,assetsrespon,departid,address,buy_price,
        opendate,guarantee_date,insuredsum,is_metering,quality_in_plan,patrol_in_plan';
        $assets = $this->DB_get_one('assets_info', $files, $where);
        $files = 'afid,factory,factory_user,factory_tel,supplier,supp_user,supp_tel,repair,repa_user,repa_tel';
        $factory = $this->DB_get_one('assets_factory', $files, $where);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assets['department'] = $departname[$assets['departid']]['department'];
        $assets['opendate'] = HandleEmptyNull($assets['opendate']);
        switch ($assets['status']) {
            case C('ASSETS_STATUS_USE'):
                $assets['statusName'] = C('ASSETS_STATUS_USE_NAME');
                break;
            case C('ASSETS_STATUS_REPAIR'):
                $assets['statusName'] = C('ASSETS_STATUS_REPAIR_NAME');
                break;
            case C('ASSETS_STATUS_OUTSIDE'):
                $assets['statusName'] = C('ASSETS_STATUS_OUTSIDE_NAME');
                break;
        }
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
                $assets['buy_price'] = '***';
        }
        //查询维保状态
        $insuranceWhere['assid'] = array('EQ', $assid);
        $insuranceWhere['status'] = array('EQ', C('INSURANCE_STATUS_USE'));
        $insurance = $this->DB_get_one('assets_insurance', 'status,assid', $insuranceWhere);
        $insuranceData = [];
        foreach ($insurance as &$insuranceV) {
            $insuranceData[$insuranceV['assid']] = $insuranceV['status'];
        }
        if (getHandleTime(time()) < $assets['guarantee_date']) {
            $assets['guarantee_status'] = '保修期内';
        } else {
            if ($insuranceData[$assets['assid']] == C('INSURANCE_STATUS_USE')) {
                $assets['guarantee_status'] = '参保期内';
            } else {
                $assets['guarantee_status'] = '脱保';
            }
        }

        if($assets['patrol_in_plan']==C('YES_STATUS')){
            $assets['patrol_status'] = '计划中';
        }else{
            $assets['patrol_status'] = '不在计划中';
        }

        //计量状态 默认不在计划中
        if ($assets['is_metering'] == C('YES_STATUS')) {
            $assets['meterin_status'] = '不在计划中';
        } else {
            $assets['meterin_status'] = '非计量设备';
        }

        $meterinWhere['status'] = array('EQ', C('OPEN_STATUS'));
        $meterinWhere['assid'] = array('EQ', $assid);
        $meterin = $this->DB_get_one('metering_plan', 'mpid', $meterinWhere);
        if ($meterin) {
            $assets['meterin_status'] = '计划中';
        }
        //维修次数
        $repairWhere['assid'] = $assid;
        $repairNum = $this->DB_get_count('repair', $repairWhere);
        $assets['repairNum'] = $repairNum;
        if (empty($factory)){
            return $assets;
        }else{
            return array_merge($assets, $factory);
        }
    }

    /**
     * 获取申请单信息
     * @param $allotid int 申请单id
     * @return array
     * */
    public function getAllotBasic($allotid){
        $where['allotid']=['EQ',$allotid];
        $data=$this->DB_get_one('subsidiary_allot','',$where);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $data['main_department'] = $departname[$data['main_departid']]['department'];
        return $data;
    }

    /**
     * 获取附属设备分配审核基本信息
     * @param $allotid int 申请单id
     * @return array
     * */
    public function getSubsidiaryApprovBasic($allotid)
    {
        $where['allotid'] = array('EQ', $allotid);
        $where['approve_class'] = array('EQ', 'subsidiary');
        $where['process_node'] = array('EQ', C('SUBSIDIARY_APPROVE'));
        $approve = $this->DB_get_all('approve', '', $where, '', 'process_node_level,approve_time asc');
        foreach ($approve as &$approveV) {
            $approveV['approve_time'] = getHandleMinute($approveV['approve_time']);
            switch ($approveV['is_adopt']) {
                case C('STATUS_APPROE_SUCCESS'):
                    $approveV['is_adoptName'] = '<span style = "color:green" > 通过</span > ';
                    break;
                case C('STATUS_APPROE_FAIL'):
                    $approveV['is_adoptName'] = '<span class="rquireCoin" > 不通过</span > ';
                    break;
                default :
                    $approveV['is_adoptName'] = '未审核';
            }
        }
        return $approve;
    }

    //格式化短信内容
    public static function formatSmsContent($content,$data){
        $content = str_replace("{assets}", $data['assets'], $content);
        $content = str_replace("{department}", $data['department'], $content);
        $content = str_replace("{allot_assets}", $data['allot_assets'], $content);
        $content = str_replace("{allot_department}", $data['allot_department'], $content);
        $content = str_replace("{approve_status}", $data['approve_status'], $content);
        return $content;
    }
}