<?php

namespace Fs\Model;

use Admin\Controller\Tool\ToolController;
use Think\Model;
use Think\Model\RelationModel;
use Fs\Model\WxAccessTokenModel;

class QualityModel extends CommonModel
{
    protected $len = 100;
    protected $tablePrefix = 'sb_';
    protected $tableName = 'quality_preset';
    private $MODULE = 'Qualities';
    protected $Controller = 'Quality';

    /**
     * Notes: 质控检测列表
     * @return mixed
     */
    public function get_quality_detail_lists()
    {
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('get.order');
        $sort = I('get.sort');
        $search = I('get.search');
        $hospital_id = I('get.hospital_id');
        if (session('isSuper')) {
            $where['is_start'] = 1;
        } elseif (session('is_supplier') == C('YES_STATUS')) {
            $where['is_start'] = 1;
            if (session('olsid') > 0) {
                $assets_f = $this->DB_get_one('assets_factory', 'GROUP_CONCAT(assid) AS assid', ['ols_supid' => session('olsid')]);
                if ($assets_f['assid']) {
                    $where['A.assid'] = ['IN', $assets_f['assid']];
                } else {
                    $result['msg'] = '暂无相关数据';
                    $result['status'] = 1;
                    $result['total'] = 0;
                    return $result;
                }
            } else {
                $result['msg'] = '暂无相关数据';
                $result['status'] = 1;
                $result['total'] = 0;
                return $result;
            }
        } else {
            $where['is_start'] = 1;
            $where['A.userid'] = ['EQ', session('userid')];
        }
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        switch ($sort) {
            case 'addtime':
                $sort = 'A.addtime';
                break;
            case 'start_date':
                $sort = 'A.start_date';
                break;
            default:
                $sort = 'A.qsid';
                break;
        }
        if (!$order) {
            $order = 'DESC';
        }
        if ($search) {
            $map['A.plan_name'] = array('like', '%' . $search . '%');
            $map['B.assets'] = array('like', '%' . $search . '%');
            $map['B.assnum'] = array('like', '%' . $search . '%');
            $map['B.model'] = array('like', '%' . $search . '%');
            $map['B.brand'] = array('like', '%' . $search . '%');
            $map['C.department'] = array('like', '%' . $search . '%');
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = ['EQ', $hospital_id];
        } else {
            $where['B.hospital_id'] = ['EQ', session('current_hospitalid')];
        }
        $template = $this->DB_get_all('quality_templates', 'qtemid,template_name');
        $temp_arr = array();
        foreach ($template as $key => $value) {
            $temp_arr[$value['qtemid']] = $value['template_name'];
        }
        $where['B.is_delete'] = '0';
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid LEFT JOIN sb_department AS C ON B.departid = C.departid";
        $fields = "A.qsid,A.plan_name,A.username,A.is_cycle,A.period,A.is_start,A.do_date,A.addtime,A.start_date,B.assets,B.assnum,B.departid,B.model,C.department,A.keepdata,A.qtemid";
        $total = $this->DB_get_count_join('quality_starts', 'A', $join, $where,'');
        $plans = $this->DB_get_all_join('quality_starts', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        $setQualityDetail = get_menu($this->MODULE, $this->Controller, 'setQualityDetail');
        foreach ($plans as &$value){
            $value['template_name'] = $temp_arr[$value['qtemid']];
            if ($value['keepdata']) {
                $value['btn'] = 1;
            }else{
                $value['btn'] = 2;
            }
            if($value['is_cycle'] == 1){
                $value['cycle_status'] = '<span style="color:#009688;">是（第 '.$value['period'].' 期）</span>';
            }else{
                $value['cycle_status'] = '<span style="color: red;">否</span>';
            }
        }
        $result['page'] = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result["status"] = 1;
        $result['rows'] = $plans;
        $result['total'] = $total;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
            $result['total'] = 0;
        }
        return $result;
    }

    /**
     * Notes: 质控结果列表
     * @return mixed
     */
    public function get_quality_result()
    {
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('get.order');
        $sort = I('get.sort');
        $search = I('get.search');
        $hospital_id = I('get.hospital_id');
        if (session('isSuper')) {
            $where['is_start'] = array('in',[3,4]);
        } elseif (session('is_supplier') == C('YES_STATUS')) {
            $where['is_start'] = array('in',[3,4]);;
            if (session('olsid') > 0) {
                $assets_f = $this->DB_get_one('assets_factory', 'GROUP_CONCAT(assid) AS assid', ['ols_supid' => session('olsid')]);
                if ($assets_f['assid']) {
                    $where['A.assid'] = ['IN', $assets_f['assid']];
                } else {
                    $result['msg'] = '暂无相关数据';
                    $result['status'] = 1;
                    $result['total'] = 0;
                    return $result;
                }
            } else {
                $result['msg'] = '暂无相关数据';
                $result['status'] = 1;
                $result['total'] = 0;
                return $result;
            }
        } else {
            $where['is_start'] = array('in',[3,4]);;
            $where['A.userid'] = ['EQ', session('userid')];
        }
        if (!isset($offset)) {
            $offset = 0;
        }
        if (!isset($limit)) {
            $limit = 10;
        }
        switch ($sort) {
            case 'start_date':
                $sort = 'A.start_date';
                break;
            default:
                $sort = 'A.qsid';
                break;
        }
        if (!$order) {
            $order = 'DESC';
        }
        if ($search) {
            $map['A.plan_name'] = array('like', '%' . $search . '%');
            $map['B.assets'] = array('like', '%' . $search . '%');
            $map['B.assnum'] = array('like', '%' . $search . '%');
            $map['B.model'] = array('like', '%' . $search . '%');
            $map['B.brand'] = array('like', '%' . $search . '%');
            $map['C.department'] = array('like', '%' . $search . '%');
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        if ($hospital_id) {
            $where['B.hospital_id'] = ['EQ', $hospital_id];
        } else {
            $where['B.hospital_id'] = ['EQ', session('current_hospitalid')];
        }
        $template = $this->DB_get_all('quality_templates', 'qtemid,template_name');
        $temp_arr = array();
        foreach ($template as $key => $value) {
            $temp_arr[$value['qtemid']] = $value['template_name'];
        }
        $where['B.is_delete'] = '0';
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid LEFT JOIN sb_department AS C ON B.departid = C.departid";
        $fields = "A.qsid,A.plan_name,A.username,A.is_cycle,A.period,A.is_start,A.do_date,A.addtime,A.start_date,B.assets,B.assnum,B.departid,B.model,C.department,A.qtemid";
        $total = $this->DB_get_count_join('quality_starts', 'A', $join, $where,'');
        $plans = $this->DB_get_all_join('quality_starts', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        foreach ($plans as &$value){
            $value['operation'] = $this->returnMobileLink('查看', C('FS_NAME').'/Quality/showDetail?qsid=' . $value['qsid'] , ' layui-btn-normal showDetail');
            $value['template_name'] = $temp_arr[$value['qtemid']];
            if($value['is_cycle'] == 1){
                $value['cycle_status'] = '<span style="color:#009688;">是（第 '.$value['period'].' 期）</span>';
            }else{
                $value['cycle_status'] = '<span style="color: red;">否</span>';
            }
            if($value['is_start'] == 3){
                $value['status_name'] = '<span style="color:#009688;">已完成</span>';
            }else{
                $value['status_name'] = '<span style="color: red;">已结束</span>';
            }
        }
        $result['page'] = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result["status"] = 1;
        $result['rows'] = $plans;
        $result['total'] = $total;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
            $result['total'] = 1;
        }
        return $result;
    }


    /**
     * 获取质控计划信息
     * @param $plans int 计划id
     * @return array
     * */
    public function getPlanBasic($plans)
    {
        $fields = 'plans,plan_name,plan_num,plan_remark,adduser,addtime';
        $where['plans'] = ['EQ', $plans];
        $data = $this->DB_get_one('quality_starts', $fields, $where);
        return $data;
    }


    /**
     * 获取质控计划设备质控任务列表
     * @param $plans int 计划id
     * @return array
     * */
    public function getPlanDetailList($plans)
    {
        //查询计划下的设备信息及完成情况
        $where['B.is_delete'] = '0';
        $fields = "A.qsid,A.assid,B.assets,B.assnum,B.departid,B.catid,B.model,A.do_date,A.is_cycle,A.cycle,A.period,A.is_start";
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $where['A.plans'] = ['EQ', $plans];
        if (session('isSuper')) {
            $where['is_start'] = ['GT', 0];
        } elseif (session('is_supplier') == C('YES_STATUS')) {
            $where['is_start'] = ['GT', 0];
            if (session('olsid') > 0) {
                $assets_f = $this->DB_get_one('assets_factory', 'GROUP_CONCAT(assid) AS assid', ['ols_supid' => session('olsid')]);
                if ($assets_f['assid']) {
                    $where['A.assid'] = ['IN', $assets_f['assid']];
                } else {
                    return [];
                }
            } else {
                return [];
            }
        } else {
            $where['is_start'] = ['GT', 0];
            $where['A.userid'] = ['EQ', session('userid')];
        }


        $data = $this->DB_get_all_join('quality_starts', 'A', $fields, $join, $where,'','','');

        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$one) {
            $one['department'] = $departname[$one['departid']]['department'];
        }

        return $data;

    }
    //上传质控报告
    public function uploadReport()
    {
       if (I('post.zm' == 'canvas')) {
            $fin = I('post.filename');
            $_FILES['file']['name'] = $fin;
            $ty = explode('.', $fin);
            $_FILES['file']['ext'] = $ty[1];
        }
        //上传设备图片
        $Tool = new ToolController();
        //设置文件类型
        $type = array('jpg', 'png', 'bmp', 'jpeg', 'gif');
        //报告保存地址
        $dirName = C('UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME');
        //上传文件
        $base64 = I('POST.base64');
        if ($base64) {
            $upload = $Tool->base64imgsave($base64, $dirName);
        } else {
            $upload = $Tool->upFile($type, $dirName);
        }


        $qsid = I('post.qsid');
        $path = $upload['src'];
        $data['report'] = $path;
        $data['edittime'] = getHandleDate(time());
        $data['edituser'] = session('username');
        $res = $this->updateData('quality_details', $data, array('qsid' => $qsid));
         if ($res) {
                return array('status' => 1, 'msg' => '上传报告成功！', 'path' => $path);
            } else {
                return array('status' => -1, 'msg' => '上传报告失败！');

        }

    }


    //组合质控计划设备质控任务同周期设备
    public function groupCycle($data){

        $newData=[];
        $setQualityDetailMenu = get_menu($this->MODULE, 'Quality', 'setQualityDetail');
        $qualityDetailMenu = get_menu($this->MODULE, 'Quality', 'qualityDetailList');

        foreach ($data as &$one){
            $showQuality = $qualityDetailMenu['actionurl'] . '?action=showQuality&qsid=' . $one['qsid'];
            if ($one['is_start'] == 1) {
                if ($setQualityDetailMenu) {
                    $href= $this->returnMobileLink('检测', $setQualityDetailMenu['actionurl'].'?qsid='.$one['qsid'], '');
                } else {
                    $href = $this->returnMobileLink('待检测', $showQuality, 'layui-btn-warm');
                }
            } else {
                $href = $this->returnMobileLink('预览', $showQuality, 'layui-btn-warm');
            }

            if($newData[$one['assnum']]){
                $resultData['href']=$href;
                $resultData['period']=$one['period'];
                array_push($newData[$one['assnum']]['resultData'],$resultData);
            }else{
                $newData[$one['assnum']]=$one;
                if($one['is_cycle']==C('YES_STATUS')){
                    $resultData['period']=$one['period'];
                }
                $resultData['href']=$href;
                $newData[$one['assnum']]['resultData'][]=$resultData;
            }
        }


        return $newData;
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
        foreach($preset as $k=>$v){
            $numvalue[] = $v['detection_Ename'];
        }
        foreach ($preset as $k => $v) {
            //获取原来的启动设置记录及误差记录
            $preset[$k]['value'] = json_encode($old_preset[$v['detection_Ename']]);
            $preset[$k]['tolerance'] = $old_tolerance[$v['detection_Ename']];
            foreach ($_POST as $k1 => $v1) {
                if ($v['detection_Ename'] == $k1) {
                    //验证是否填写完整
                    foreach ($v1 as $real_k=>$real_value){
                        $v1[$real_k] = trim($real_value);
                        if($v1[$real_k] == ''){
                            unset($v1[$real_k]);
                        }
                    }
                    if(count($v1) != count($_POST[$k1])){
                        return array('status' => -1, 'msg' => '请填写完整的 '.$v['detection_name'].' 测量值！');
                    }
                    //验证数据合法性
                    if($k1 == 'pressure'){
                        foreach ($v1 as $real_k=>$real_value){
                            $v1[$real_k] = str_replace(' ','',$v1[$real_k]);
                            $v1[$real_k] = str_replace('（','(',$v1[$real_k]);
                            $v1[$real_k] = str_replace('）',')',$v1[$real_k]);
                            if(strpos($v1[$real_k],'(') === false){
                                return array('status' => -1, 'msg' => '请填写正确的 '.$v['detection_name'].' 测量值！如:75/45(55)');
                            }
                            if(strpos($v1[$real_k],'/') === false){
                                return array('status' => -1, 'msg' => '请填写正确的 '.$v['detection_name'].' 测量值！如:75/45(55)');
                            }
                        }
                    }else{
                        foreach ($v1 as $real_k=>$real_value){
                            if(!is_numeric($real_value)){
                                return array('status' => -1, 'msg' => '请填写正确的 '.$v['detection_name'].' 测量值！');
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
                    }
                    if ($_POST[$v['detection_Ename'] . '_max_value']) {
                        $preset_detection[$v['detection_Ename'] . '_max_value'] = $_POST[$v['detection_Ename'] . '_max_value'];
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
        foreach ($_POST['result'] as $k=>$v){
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
        $result_where['is_conformity'] = array(array('EXP', 'IS NULL'),0,'or');
        $result_where['qsid']  = $data['qsid'];
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
        $have_error = $this->DB_get_one('quality_result','resultid',array('is_conformity'=>2,'qsid'=>$data['qsid']));
        if($have_error['resultid']){
            //有不合格项目，整个检测结果为不合格
            $data['result'] = 2;//不符合
        }else{
            $data['result'] = 1;//符合
        }
        foreach ($preset_detection as $k=>$v){
            $qr = $this->DB_get_one('quality_result','is_conformity',array('qsid'=>$data['qsid'],'detection_Ename'=>$k));
            $preset_detection[$k.'_result'] = $qr['is_conformity'];
        }
        $data['preset_detection'] = json_encode($preset_detection, JSON_UNESCAPED_UNICODE);
        if($_POST['save_edit'] == 'edit'){
            $update_detail['exterior'] = $data['exterior'];
            $update_detail['exterior_explain'] = $data['exterior_explain'];
            $update_detail['result'] = $data['result'];
            $update_detail['remark'] = $data['remark'];
            $update_detail['preset_detection'] = $data['preset_detection'];
            $update_detail['fixed_detection'] = $data['fixed_detection'];
            $update_detail['edituser'] = session('username');
            $update_detail['edittime'] = date('Y-m-d H:i:s');
            $res = $this->updateData('quality_details',$update_detail,array('qsid'=>$data['qsid']));
            if(!$res){
                return array('status' => -1, 'msg' => '修改明细失败！');
            }
            $this->deleteData('quality_details_patrol',array('qsid'=>$data['qsid']));
            $this->insertDataALL('quality_details_patrol',$patrol_data);
            return array('status' => 1, 'msg' => '修改明细成功！');
        }else{
            $res = $this->insertData('quality_details', $data);
            if ($res) {
                $this->insertDataALL('quality_details_patrol',$patrol_data);
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
}
