<?php

namespace Admin\Controller\Tool;

use Admin\Controller\BaseSetting\BaseSettingController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\BaseSettingModel;
use Admin\Model\DepartmentModel;
use Admin\Model\MeteringModel;
use Admin\Model\NoticeModel;
use Admin\Model\PatrolPlanModel;
use Admin\Model\QualityModel;
use Admin\Model\RepairModel;
use Admin\Model\UserModel;
use Admin\Model\RoleModel;
use Think\Controller;
use Think\Exception;

class ToolController extends Controller
{

    public function repairMegery()
    {
        $repairModel = new RepairModel();
//        $owhere['assid'] = array('exp','is null');
//        $olds = $repairModel->DB_get_all('repair_old_data','*',$owhere,'','id asc');
//        session('current_hospitalid','1');
//        foreach ($olds as $k=>$v){
//            $where['assnum'] = $v['assnum'];
//            $assetsInfo = $repairModel->DB_get_one('assets_info','assid,departid',$where,'','');
//            if($assetsInfo){
//                $update = [];
//                //查询该科室的护士作为报修人
//                $app_users = ToolController::getUser('addRepair', $assetsInfo['departid'], false);
//                if($app_users){
//                    $to = count($app_users);
//                    $update['applicant'] = $app_users[$to-1]['username'];
//                    //报修时间
//                    $h = rand(8, 13);
//                    $m = rand(1, 59);
//                    //报修人报修时间
//                    $applicant_data = date('Y-m-d H:i:s',strtotime($v['repair_date']) + $h*3600 + $m*60);
//                    $update['applicant_time'] = $applicant_data;
//                    $update['applicant_tel'] = $app_users[$to-1]['telephone'];
//
//
//                    //验收人验收时间
//                    $h = rand(16, 19);
//                    $m = rand(1, 59);
//                    $check_data = date('Y-m-d H:i:s',strtotime($v['over_repair_date']) + $h*3600 + $m*60);
//                    $update['checkdate'] = $check_data;
//                    $update['checkperson'] = $app_users[$to-1]['username'];
//                }
//                $update['assid'] = $assetsInfo['assid'];
//                $update['departid'] = $assetsInfo['departid'];
//
//                //接单人接单时间
//                $update['expect_arrive'] = 5;//预计到场时间
//                $f = rand(3, 10);
//                $respond_date = date('Y-m-d H:i:s',strtotime($update['applicant_time']) + $f*60);
//                $update['response'] = $v['engineer'];
//                $update['respond_date'] = $respond_date;
//
//                //检修时间，维修开始时间
//                $j = rand(5, 10);
//                $overhaul_date = date('Y-m-d H:i:s',strtotime($respond_date) + $j*60);
//                $update['engineer_time'] = $overhaul_date;
//                $update['overhauldate'] = $overhaul_date;
//
//                //结束维修时间
//                $s = rand(13, 16);
//                $ss = rand(1, 59);
//                $overhaul_date = date('Y-m-d H:i:s',strtotime($v['over_repair_date']) + $s*3600 + $ss*60);
//                $update['over_repair_date'] = $overhaul_date;
//                $repairModel->updateData('repair_old_data',$update,['id'=>$v['id']]);
//            }
//        }
//        var_dump('ok');
//        exit;



//        $owhere['departid'] = array('gt',0);
//        $olds = $repairModel->DB_get_all('repair_old_data','*',$owhere,'','id asc');
//        $insert = [];
//        $baseSetting = [];
//        include APP_PATH . "Common/cache/basesetting.cache.php";
//        $wx = $baseSetting['repair']['repair_encoding_rules']['value'];
//        foreach ($olds as $k=>$v){
//            if ($wx['prefix']) {
//                $insert['repnum'] = $wx['prefix'] . $insert['repnum'];
//            }
//            //暂时未需要配置默认下划线 如果需要配置 这里读配置 todo
//            $cut = '_';
//            if ($cut) {
//                $insert['repnum'] .= $cut;
//            }
//            $insert['repnum'] = $repairModel->getOrgNumber('repair', 'repnum', $wx['prefix'], $cut,strtotime($v['repair_date']));
//            $insert['assid'] = $v['assid'];
//            $partsArray = [];
//            if($v['parts_name']){
//                $partsArray = explode('|',$v['parts_name']);
//            }
//            $pn = count($partsArray);
//            if($pn > 1){
//                //多个配件
//                $partsarr = preg_split('//', $v['parts_num'], -1, PREG_SPLIT_NO_EMPTY);
//                $t = 0;
//                foreach ($partsarr as $p){
//                    $t += (int)$p;
//                }
//                $insert['part_num'] = $t;
//            }else{
//                $insert['part_num'] = $v['parts_num'];
//            }
//            $insert['assnum'] = $v['assnum'];
//            $insert['assets'] = $v['assets_name'];
//            $insert['status'] = 8;
//            $insert['repair_type'] = $v['repair_type'] == '是' ? 0 : 1;
//            $insert['model'] = $v['model'];
//            $insert['departid'] = $v['departid'];
//            $insert['department'] = $v['department'];
//            $insert['applicant'] = $v['applicant'];
//            $insert['applicant_time'] = strtotime($v['applicant_time']);
//            $insert['applicant_tel'] = $v['applicant_tel'];
//            $insert['breakdown'] = $v['fault_details'];
//            $insert['response'] = $v['engineer'];
//            $insert['response_date'] = strtotime($v['applicant_time']) + $v['respond_date']*60;
//            $insert['fault_type'] = 1;
//            $insert['fault_problem'] = 1;
//            $insert['expect_arrive'] = $v['expect_arrive'];
//            $insert['expect_time'] = strtotime($v['expect_time']);
//            $insert['engineer'] = $v['engineer'];
//            $insert['engineer_time'] = strtotime($v['engineer_time']);
//            $insert['working_hours'] = $v['working_hours'];
//            $insert['dispose_detail'] = $v['processing_details'];
//            $insert['approve_status'] = -1;
//            $insert['overdate'] = strtotime($v['over_repair_date']);
//            $insert['over_status'] = 1;
//            $insert['service_attitude'] = 0;
//            $insert['technical_level'] = 0;
//            $insert['response_efficiency'] = 0;
//            $insert['checkperson'] = $v['checkperson'];
//            $insert['checkdate'] = strtotime($v['checkdate']);
//            $insert['overhauldate'] = strtotime($v['overhauldate']);
//            $res = $repairModel->insertData('repair',$insert);
//            //新增维修单成功
//            if($partsArray){
//                //有配件的
//                $parts_unit = explode('|',$v['parts_unit']);
//                $partsnum = preg_split('//', $v['parts_num'], -1, PREG_SPLIT_NO_EMPTY);
//                $partInfo = [];
//                foreach ($partsArray as $pk=>$pv){
//                    $partInfo[$pk]['repid'] = $res;
//                    $partInfo[$pk]['parts'] = $pv;
//                    $partInfo[$pk]['part_num'] = $partsnum[$pk] ? $partsnum[$pk] : 1;
//                    $partInfo[$pk]['status'] = 1;
//                }
//                $repairModel->insertDataALL('repair_parts',$partInfo);
//            }
//
//            //故障类型 故障问题
//            $fs = $repairModel->DB_get_one('repair_setting','group_concat(id) as ids',['parentid'=>0]);
//            $idsarr = explode(',',$fs['ids']);
//            $cf = count($idsarr);
//            $cc = rand(0, $cf-1);
//            $pp = $repairModel->DB_get_one('repair_setting','id',['parentid'=>$idsarr[$cc]],'id asc');
//            $faultInsert['repid'] = $res;
//            $faultInsert['fault_type_id'] = $idsarr[$cc];
//            $faultInsert['fault_problem_id'] = $pp['id'];
//            $repairModel->insertData('repair_fault',$faultInsert);
//        }
//        var_dump('ok');
//        exit;

        $all = $repairModel->DB_get_all('repair','*','','','repid asc');
        $faults = $repairModel->DB_get_all('repair_setting','*',['parentid'=>10],'','id asc');
        foreach ($all as $k=>$v){
            foreach ($faults as $fk=>$fv){
                if(strpos($fv['title'],$v['breakdown']) !== false){
                    $faultInsert['repid'] = $v['repid'];
                    $faultInsert['fault_type_id'] = 10;
                    $faultInsert['fault_problem_id'] = $fv['id'];
                    $repairModel->insertData('repair_fault',$faultInsert);
                    break;
                }
            }
        }
        exit;
    }
    public function movePatrol()
    {
        $page = I('get.page');
        if(!$page){
            exit('输入页码');
        }
        $limit = 50;
        $page = $page ? $page : 1;
        $offset = ($page - 1) * $limit;
        $patrolPlanModel = new PatrolPlanModel();
        $plans = $patrolPlanModel->DB_get_all('patrol_plan','*','','','patrid asc',$offset . ',' . $limit);
        if(!$plans){
            exit('当前页码没数据');
        }
        $patrolPlanModel->startTrans();
        $add_plans_data = [];
        foreach ($plans as $k=>$v){
            $add_plans_data[$k]['patrid'] = $v['patrid'];
            $add_plans_data[$k]['hospital_id'] = $v['hospital_id'];
            $depart = $patrolPlanModel->DB_get_one('assets_info','group_concat(distinct departid) as departid',['assnum'=>['in',$v['patrol_assnums']]]);
            $add_plans_data[$k]['assets_departid'] = $depart['departid'];
            $add_plans_data[$k]['patrol_level'] = $v['patrol_level'];
            $add_plans_data[$k]['patrol_name'] = $v['patrolname'];
            $add_plans_data[$k]['patrol_start_date'] = $v['executedate'];
            $add_plans_data[$k]['patrol_end_date'] = $v['expect_complete_date'];
            $add_plans_data[$k]['is_cycle'] = 0;
            $add_plans_data[$k]['cycle_unit'] = '';
            $add_plans_data[$k]['cycle_setting'] = '';
            $add_plans_data[$k]['total_cycle'] = 1;
            $add_plans_data[$k]['current_cycle'] = 1;
            switch ($v['patrol_status']){
                case '1':
                    $add_plans_data[$k]['patrol_status'] = 1;//待审核
                    break;
                case '3':
                    $add_plans_data[$k]['patrol_status'] = 2;//待发布
                    break;
                case '4':
                case '5':
                    $add_plans_data[$k]['patrol_status'] = 3;//待实施
                    break;
                case '2':
                case '6':
                case '7':
                    $add_plans_data[$k]['patrol_status'] = 4;//已结束
                    break;
            }
            $add_plans_data[$k]['remark'] = $v['remark'];
            $add_plans_data[$k]['add_user'] = $v['add_user'];
            $add_plans_data[$k]['add_time'] = $v['add_time'];
            $add_plans_data[$k]['edit_user'] = $v['edit_user'];
            $add_plans_data[$k]['edit_time'] = $v['edit_time'];

            $add_plans_data[$k]['approve_status'] = $v['approve_status'];
            $add_plans_data[$k]['approve_time'] = $v['approve_time'];
            $add_plans_data[$k]['retrial_status'] = $v['retrial_status'];
            $add_plans_data[$k]['current_approver'] = $v['current_approver'];
            $add_plans_data[$k]['complete_approver'] = $v['complete_approver'];
            $add_plans_data[$k]['not_complete_approver'] = $v['not_complete_approver'];
            $add_plans_data[$k]['all_approver'] = $v['all_approver'];
            $add_plans_data[$k]['is_release'] = $v['is_release'];
            $add_plans_data[$k]['release_user'] = $v['release_user'];
            $add_plans_data[$k]['release_time'] = $v['release_time'];
            $add_plans_data[$k]['release_remark'] = $v['release_remark'];
            $add_plans_data[$k]['is_delete'] = $v['is_delete'];
        }
        $res = $patrolPlanModel->insertDataALL('patrol_plans',$add_plans_data);
        if(!$res){
            $patrolPlanModel->rollback();
        }
        foreach ($plans as $k=>$v){
            $where_1['patrid'] = $v['patrid'];
            $where_1['patrol_level'] = $v['patrol_level'];
            $cycleInfo = $patrolPlanModel->DB_get_all('patrol_plan_cycle','*',$where_1,'','cycid asc');
            $implement_sum = 0;
            $abnormal_sum = 0;
            $abnormal_assnum = [];
            $repair_assnum = [];
            $scrap_assnum = [];
            $not_operation_assnum = [];
            $sign_info = [];
            $assnum_tpid = [];
            foreach ($cycleInfo as $ck=>$cv){
                $implement_sum += $cv['implement_sum'];
                $abnormal_sum += $cv['abnormal_sum'];

                $tmp_abnormal_assnum = json_decode($cv['abnormal_assnum']);
                foreach ($tmp_abnormal_assnum as $abv){
                    $abnormal_assnum[] = $abv;
                }

                $tmp_repair_assnum = json_decode($cv['repair_assnum']);
                foreach ($tmp_repair_assnum as $rpv){
                    $repair_assnum[] = $rpv;
                }

                $tmp_scrap_assnum = json_decode($cv['scrap_assnum']);
                foreach ($tmp_scrap_assnum as $scv){
                    $scrap_assnum[] = $scv;
                }

                $tmp_not_operation_assnum = json_decode($cv['not_operation_assnum']);
                foreach ($tmp_not_operation_assnum as $nov){
                    $not_operation_assnum[] = $nov;
                }

                $tmp_sign_info = json_decode($cv['sign_info']);
                foreach ($tmp_sign_info as $sv){
                    $sign_info[] = $sv;
                }

                $a = (array)json_decode($cv['assnum_tpid']);
                foreach ($a as $tpk=>$tpv){
                    $assnum_tpid[$tpk] = $tpv;
                }
            }
            $c['cycid'] = $cycleInfo[0]['cycid'];
            $c['patrid'] = $cycleInfo[0]['patrid'];
            $c['period'] = 1;
            $c['patrol_num'] = $v['patrolnum'];
            $c['assets_nums'] = count(explode(',',$v['patrol_assnums']));
            $c['implement_sum'] = $implement_sum;

            $assets_departid = $patrolPlanModel->DB_get_one('patrol_plans','assets_departid',['patrid'=>$v['patrid']]);
            $c['assets_departid'] = $assets_departid['assets_departid'];

            $c['abnormal_sum'] = $abnormal_sum;
            $c['abnormal_assnum'] = $abnormal_assnum ? json_encode($abnormal_assnum) : '';
            $c['plan_assnum'] = json_encode(explode(',',$v['patrol_assnums']));
            $c['repair_assnum'] = $repair_assnum ? json_encode($repair_assnum) : '';
            $c['scrap_assnum'] = $scrap_assnum ? json_encode($scrap_assnum) : '';
            $c['not_operation_assnum'] = $not_operation_assnum ? json_encode($not_operation_assnum) : '';

            $c['cycle_start_date'] = $v['executedate'];
            $c['cycle_end_date'] = $v['expect_complete_date'];

            $min_status = $patrolPlanModel->DB_get_one('patrol_plan_cycle','min(status) as status',$where_1);
            switch ($min_status['status']){
                case '0':
                    $c['cycle_status'] = 0;//待执行
                    break;
                case '1':
                    $c['cycle_status'] = 1;//执行中
                    break;
                case '2':
                case '3':
                    $c['cycle_status'] = 2;//已完成
                    break;
            }
            $c['check_status'] = $min_status['status'] == 3 ? 1 : 0;
            $c['create_time'] = $v['release_time'];
            $c['sign_info'] = $sign_info ? json_encode($sign_info) : '';
            $complete_time = $patrolPlanModel->DB_get_one('patrol_plan_cycle','max(complete_time) as complete_time',$where_1);
            $c['complete_time'] = $complete_time ? $complete_time['complete_time'] : '';
            $res1 = $patrolPlanModel->insertData('patrol_plans_cycle',$c);
            if(!$res1){
                $patrolPlanModel->rollback();
            }
            $assnums = explode(',',$v['patrol_assnums']);
            $as_where['assnum'] = ['in',$v['patrol_assnums']];
            $asModel = M('assets_info');
            $assInfo = $asModel->where($as_where)->getField('assnum,assid');
            $ps = [];
            foreach ($assnums as $ak=>$av){
                $ps[$ak]['patrid'] = $v['patrid'];
                $ps[$ak]['assid'] = $assInfo[$av];
                $ps[$ak]['assnum'] = $av;
                $ps[$ak]['assnum_tpid'] = $assnum_tpid[$av];
                $ps[$ak]['enable_status'] = 1;
                $ps[$ak]['add_user'] = $v['add_user'];
                $ps[$ak]['add_time'] = $v['add_time'];
            }
            $ps_res = $patrolPlanModel->insertDataALL('patrol_plans_assets',$ps);
            if(!$ps_res){
                $patrolPlanModel->rollback();
            }
            //修改执行信息
            $cycids = [];
            foreach ($cycleInfo as $ck=>$cv){
                $cycids[] = $cv['cycid'];
                $patrolPlanModel->updateData('patrol_execute',['execute_user'=>$cv['executor']],['cycid'=>$cv['cycid']]);

                //修改保养图片
                $files = $patrolPlanModel->DB_get_all('patrol_plan_cycle_file','*',['cycid'=>$cv['cycid']]);
                foreach ($files as $fk=>$fv){
                    $f = [];
                    $f['file_id'] = $fv['file_id'];
                    $f['cycid'] = $cycleInfo[0]['cycid'];
                    $f['assnum'] = $fv['assnum'];
                    $f['file_name'] = $fv['file_name'];
                    $f['save_name'] = $fv['save_name'];
                    $f['file_type'] = $fv['file_type'];
                    $f['file_size'] = $fv['file_size'];
                    $f['file_url'] = $fv['file_url'];
                    $f['add_user'] = $fv['add_user'];
                    $f['add_time'] = $fv['add_time'];
                    $f['is_delete'] = $fv['is_delete'];
                    $patrolPlanModel->insertData('patrol_plans_cycle_file',$f);
                }
            }
            //把所有cycid更改为第一个cycid
            $patrolPlanModel->updateData('patrol_execute',['cycid'=>$cycleInfo[0]['cycid']],['cycid'=>['in',implode(',',$cycids)]]);
        }
        $patrolPlanModel->commit();
        var_dump(66);
        exit;
    }

    //layer 编辑器图片上传
    public function addLayerImg()
    {
        $style = array('jpg', 'gif', 'png', 'jpeg');
        $dirName = C('UPLOAD_DIR_LAYEDIT_NAME');
        $name = $this->upFile($style, $dirName);
        if ($name['status'] == C('YES_STATUS')) {
            $code = 0;
            $this->ajaxReturn(array('code' => $code, 'msg' => '', 'data' => $name), 'json');
        } else {
            $code = -1;
            $this->ajaxReturn(array('code' => $code, 'msg' => $name['msg'], 'data' => ''), 'json');
        }
    }

    /** 文件上传
     *
     * @param1 $type array 设置允许上传的格式
     * @param2 $dirName string 文件夹名称
     * @return  array
     *
     */
    public function upFile($type, $dirName, $is_water = false, $water_text = [], $is_compression = false,$saveName=true,$autoSub=true)
    {
        //实例化上传类
        $upload = new \Think\Upload();
        //设置上传大小
        $upload->maxSize = 31457280000;
        //设置上传类型
        $upload->exts = $type;
        // 设置上传目录
        $path = './Public/uploads/' . $dirName . '/';
        if (!file_exists($path)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            $dirarr = explode('/', $dirName);
            $tmpdir = './Public/uploads/';
            foreach ($dirarr as $v) {
                $tmpdir .= $v . '/';
                mkdir($tmpdir, 0777);
                chmod($tmpdir, 0777);
            }
        }
        $upload->rootPath = $path;
        $upload->savePath = '';
        //设置保存的文件名
        $upload->saveName = $saveName ? array('uniqid', '') : '';
        //上传文件
        $upload->autoSub = $autoSub;
        $upload->subName = array('date', 'Ymd');
        $info = $upload->upload($_FILES);
        if (!$info) {
            $result['status'] = -999;
            $result['msg'] = $upload->getError();
            $this->error();
        } else {
            //生成缩略图
//            $image = new \Think\Image();
//            $image->open($upload->rootPath.$info['image']['savepath'].$info['image']['savename']);
//            $image->thumb(250,250)->save($upload->rootPath.$info['image']['savepath'].'l_'.$info['image']['savename']);
//            $image->thumb(50, 50)->save($upload->rootPath.$info['image']['savepath'].'m_'.$info['image']['savename']);

            if ($is_water) {
                //添加水印
                $image = new \Think\Image();
                $image->open('./Public/uploads/' . $dirName . '/' . $info['file']['savepath'] . $info['file']['savename']);
                //$file_arr = read_exif_data('./Public/uploads/'.$dirName.'/'.$info['file']['savepath'].$info['file']['savename']);
                $font_size = ceil($image->width() / 20);
                $font_size2 = ceil($image->width() / 24);
                $offset = ceil($image->width() / 5.5);
                $offset1 = ceil($offset * 1.3);
                $offset3 = ceil($offset / 1.4);

                $image->text($water_text[0], './Public/font/simkai.ttf', $font_size, '#B80D02', \Think\Image::IMAGE_WATER_SOUTHEAST, -$offset1);
                $image->text($water_text[1], './Public/font/simkai.ttf', $font_size2, '#B80D02', \Think\Image::IMAGE_WATER_SOUTHEAST, -$offset);
                $image->text($water_text[2], './Public/font/simkai.ttf', $font_size2, '#B80D02', \Think\Image::IMAGE_WATER_SOUTHEAST, -$offset3);
                $image->save('./Public/uploads/' . $dirName . '/' . $info['file']['savepath'] . $info['file']['savename']);
            }
            if (!$info['file']) {
                //layer 特殊
                $info['file'] = $info['file_url'];
            }
            if ($is_compression) {
                $image = new \Think\Image();
                $thumb_file = $upload->rootPath . $info['file']['savepath'] . $info['file']['savename'];
                $image->open($thumb_file);
                $image->thumb(900, 1300)->save($upload->rootPath . $info['file']['savepath'] . 'l_' . $info['file']['savename']);
                $img['src'] = '/Public/uploads/' . $dirName . '/' . $info['file']['savepath'] . 'l_' . $info['file']['savename'];
                $img['title'] = 'l_' . $info['file']['savename'];
                $img['name'] = $info['file']['savepath'] . 'l_' . $info['file']['savename'];
                $img['formerly'] = $info['file']['name'];
                $img['ext'] = $info['file']['ext'];
                $img['size'] = $info['file']['size'];
                $img['status'] = C('YES_STATUS');
                //@unlink($thumb_file);
                return $img;
            }
            //地址
            $img['src'] = '/Public/uploads/' . $dirName . '/' . $info['file']['savepath'] . $info['file']['savename'];
            //生成文件名
            $img['title'] = $info['file']['savename'];
            //存入sql的名字
            $img['name'] = $info['file']['savepath'] . $info['file']['savename'];
            //原文件名
            $img['formerly'] = $info['file']['name'];
            //后缀名
            $img['ext'] = $info['file']['ext'];
            //文件大小
            $img['size'] = $info['file']['size'];
            $img['status'] = C('YES_STATUS');
            return $img;
        }
    }


    public function base64imgsave($img, $dirName, $is_water = false, $water_text = [])
    {
        //图片路径地址
        $basedir = '/Public/uploads/' . $dirName . '/' . date('Ymd') . '/';
        $fullpath = '.' . $basedir;
        if (!file_exists($fullpath)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            $dirarr = explode('/', $dirName. '/' . date('Ymd'));
            $tmpdir = './Public/uploads/';
            foreach ($dirarr as $v) {
                $tmpdir .= $v . '/';
                mkdir($tmpdir, 0777);
                chmod($tmpdir, 0777);
            }
        }
        $types = empty($types) ? array('jpg', 'gif', 'png', 'jpeg') : $types;
        $img = str_replace(array('_', '-'), array('/', '+'), $img);
        $b64img = substr($img, 0, 100);
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $b64img, $matches)) {
            $type = $matches[2];
            if (!in_array($type, $types)) {
                return array('status' => 0, 'info' => '图片格式不正确，只支持 jpg、png、jpeg！', 'url' => '');
            }
            $img = str_replace($matches[1], '', $img);
            $img = base64_decode($img);
            $photo = md5(date('YmdHis') . rand(1000, 9999)) . '.' . $type;
            file_put_contents($fullpath . $photo, $img);
            if ($is_water) {
                //添加水印
                $image = new \Think\Image();
                $image->open($fullpath . $photo);
                //$file_arr = read_exif_data('./Public/uploads/'.$dirName.'/'.$info['file']['savepath'].$info['file']['savename']);
                $font_size = ceil($image->width() / 20);
                $font_size2 = ceil($image->width() / 24);
                $offset = ceil($image->width() / 5.5);
                $offset1 = ceil($offset * 1.3);
                $offset3 = ceil($offset / 1.4);
                $image->text($water_text[0], './Public/font/simkai.ttf', $font_size, '#B80D02', \Think\Image::IMAGE_WATER_SOUTHEAST, -$offset1);
                $image->text($water_text[1], './Public/font/simkai.ttf', $font_size2, '#B80D02', \Think\Image::IMAGE_WATER_SOUTHEAST, -$offset);
                $image->text($water_text[2], './Public/font/simkai.ttf', $font_size2, '#B80D02', \Think\Image::IMAGE_WATER_SOUTHEAST, -$offset3);
                $image->save($fullpath . $photo);
            }
            //地址
            $result['src'] = $basedir . $photo;
            //生成文件名
            $result['title'] = $photo;
            //存入sql的名字
            $result['name'] = $photo;
            //原文件名
            $fileName = trim(I('POST.fileName'));
            $result['formerly'] = $fileName ? $fileName : $photo;
            //后缀名
            $result['ext'] = $type;
            //文件大小
            $result['size'] = filesize($fullpath . $photo);
            $result['status'] = C('YES_STATUS');
            return $result;
        } else {
            $result['status'] = 0;
            return $result;
        }
    }

    /**
     * 发送短信
     * 短信内容超过70字会分开成2条，内容请尽量不要超过70字!
     *
     * @param1 $time string 发送短信的时间
     * @param2 $module string 模块名称
     * @param3 $actionid string 事件ID
     * @param4 $msg string 短信内容1
     * @param5 $condition string 权限方法名/电话号码
     * @param6 $type string 1=>通过方法名找到号码;0=>直接使用号码
     * @param7 $departid string 1=>通过方法名找到号码;2=>直接使用号码
     * @return array
     */
    function pushSms($module, $actionid, $msg, $condition, $type = 0, $departid = null)
    {
        if ($type == 1) {
            $user = $this->getUser($condition, $departid);
            $phone = '';
            foreach ($user as &$one) {
                $phone .= ',' . $one['telephone'];
            }
            $phone = ltrim($phone, ",");
            return $this->sendingSMS($phone, $msg, $module, $actionid);
        } else {
            return $this->sendingSMS($condition, $msg, $module, $actionid);
        }
    }


    /**
     * 发送短信
     * 短信内容超过70字会分开成2条，内容请尽量不要超过70字!
     * @param1 $phone string 电话号码
     * @param2 $msg string 短信内容
     * @param3 $module string 模块名称
     * @param4 $actionid string 事件ID
     * */
    public static function sendingSMS($phone, $msg, $module, $actionid)
    {

        if (!$phone) {
            return true;
        }
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        $sha = '【天成资产系统】';
        $clapi = new \Admin\Common\ChuanglanSmsApi();
        $msg = $msg . $sha;
        //测试阶段 全部成功
        $smsData['module'] = $module;
        $smsData['actionid'] = $actionid;
        $smsData['phone'] = strlen($phone) > 12 ? $SM4->decrypt($phone) : $phone;
        $smsData['send_time'] = date('Y-m-d H:i:s');
        $smsData['content'] = $msg;
        $smsData['send_status'] = 1;
        M('sms', C('DB_PREFIX'))->add($smsData);
        return $data = array('status' => 1, 'msg' => "短信发送成功");
        //end
        $result = $clapi->sendSMS($phone, $msg);
        $result = $clapi->execResult($result);
        $smsData['module'] = $module;
        $smsData['actionid'] = $actionid;
        $smsData['phone'] = $phone;
        $smsData['send_time'] = date('Y-m-d H:i:s');
        $smsData['content'] = $msg;
        if (isset($result[1]) && $result[1] == 0) {
            $smsData['send_status'] = 1;
            M('sms', C('DB_PREFIX'))->add($smsData);
            return $data = array('status' => 1, 'msg' => "短信发送成功");
        } else {
            $smsData['send_status'] = 0;
            M('sms', C('DB_PREFIX'))->add($smsData);
            return $data = array('status' => -1, 'msg' => "短信发送失败：{$result[1]}");
        }
    }

    /**
     * 下载文件
     * @param1 $filename string 保存的文件名称
     * @param2 $title string 下载后的文件名(带后缀)
     */
    function downFile()
    {
        $filename = $_REQUEST['path'];//获取文件的相对路径
        $filename = urldecode($filename);
        $title = $_REQUEST['filename'];//获取文件的名称，带后缀格式
        if (file_exists('.' . $filename) == false) {
            echo('文件丢失,请联系管理人员');
        } else {
            if (!$title) {
                if (strstr($filename, '/')) {
                    $title = substr($filename, strrpos($filename, '/') + 1);
                } else {
                    $title = $filename;
                }
            }
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            $exc = $http_type . $_SERVER['HTTP_HOST'];
//            var_dump($title);exit;
            Header("Content-type:  application/octet-stream ");
            Header("Accept-Ranges:  bytes ");
            Header("Accept-Length: " . filesize($filename));
            header("Content-Disposition:  attachment;  filename= $title");//生成的文件名(带后缀的)

            echo file_get_contents($exc . $filename);//用绝对路径
            readfile($filename);
        }
    }

    /**
     * 获取有对应权限的用户
     * @param1 $action string 权限的方法
     * @param2 $departid string 所属科室
     * @param2 $onadmin Boolean 是否包括超级管理员 默认不包括
     * @return array
     */
    static function getUser($action, $departid = null, $onadmin = false)
    {
        $roleModel = new RoleModel();
        if ($departid) {
            $where['A.departid'] = array('in', $departid);
            $where['B.is_delete'] = C('NO_STATUS');
            $where['B.status'] = C('OPEN_STATUS');
            $ujoin = " LEFT JOIN sb_user AS B ON A.userid = B.userid";
            $userAll = $roleModel->DB_get_one_join('user_department', 'A', 'GROUP_CONCAT(A.userid) AS userid', $ujoin, $where);
            if ($userAll['userid']) {
                $userWhere['U.userid'] = ['IN', $userAll['userid']];
            }
        }
        $where = [];
        $where['M.name'] = $action;
        $where['R.is_default'] = C('NO_STATUS');
        $where['R.status'] = C('OPEN_STATUS');
        $where['R.is_delete'] = C('NO_STATUS');
        $where['R.is_default'] = C('NO_STATUS');
        $where['R.hospital_id'] = session('current_hospitalid');
        $join[0] = 'LEFT JOIN sb_role_menu AS RM ON RM.roleid=R.roleid';
        $join[1] = 'LEFT JOIN sb_menu AS M ON M.menuid=RM.menuid';
        $role = $roleModel->DB_get_one_join('role', 'R', 'GROUP_CONCAT(R.roleid)roleid', $join, $where);
        if ($role['roleid']) {
            $userWhere['UR.roleid'] = ['IN', $role['roleid']];
            if ($onadmin == false) {
                $userWhere['U.is_super'] = ['EQ', C('NO_STATUS')];
            }
            $join = 'LEFT JOIN sb_user_role AS UR ON UR.userid=U.userid';
            $user = $roleModel->DB_get_all_join('user', 'U', 'U.userid,U.username,U.telephone,U.manager_hospitalid,U.openid', $join, $userWhere, 'U.userid', '', '');
            $userData = [];
            foreach ($user as &$one) {
                $manager = explode(',', $one['manager_hospitalid']);
                if (in_array(session('current_hospitalid'), $manager)) {
                    $userData[] = $one;
                }
            }
            return $userData;
        } else {
            return [];
        }
    }

    //预览文件
    function showFile()
    {
        $path = I('GET.path');//获取文件的相对路径
        $path = urldecode($path);
        $title = $_GET['filename'];//获取文件的名称，必须带后缀格式
        if (!$title) {
            $path_arr = explode('/', $path);
            $title = $path_arr[count($path_arr) - 1];
        }
        $type = substr(strrchr($title, '.'), 1);
        $this->assign('type', $type);
        $path = str_replace(C('APP_NAME'), '', $path);
        $this->assign('file_exists', file_exists('.' . $path));
        $this->assign('src', $path);
        $this->assign('title', $title);
        $this->display('./Public:showFile');
    }

    /**
     * Notes: 大屏显示
     */
    public function screen()
    {
        if (IS_POST) {
            //上传背景图片
            //设置文件类型
            $type = array('jpg', 'png', 'bmp', 'jpeg');
            //报告保存地址
            $dirName = C('UPLOAD_DIR_SCREEN');
            //上传文件
            $upload = $this->upFile($type, $dirName);
            if ($upload['status'] == C('YES_STATUS')) {
                // 上传成功 获取上传文件信息
                $this->ajaxReturn(array('status' => 1, 'msg' => '上传文件成功！', 'path' => $upload['src']));
            } else {
                // 上传错误提示错误信息
                $this->ajaxReturn(array('status' => -1, 'msg' => '上传文件失败！'));
            }
        } else {
            if (!session('userid')) {
                redirect(U("A/Login/login"));
                exit;
            }
            $hospital_id = session('current_hospitalid');
            $where['is_delete'] = 0;
            if ($hospital_id) {
                $where['hospital_id'] = $hospital_id;
            }
            $repModel = new RepairModel();
            //查询所有医院
            $hosinfo = $repModel->DB_get_all('hospital', 'hospital_id,hospital_code,hospital_name', $where);
            if (!$hosinfo) {
                exit('没有查询到任何医院信息');
            }
            //var_dump($_GET);exit;
            //背景设置
            $bj = I('get.bj');
            if ($bj == 'img') {
                $this->assign('bj_img', 1);
                $this->assign('img_url', I('get.iurl'));
            } else {
                $this->assign('bj_ys', 1);
                $background = trim(I('get.bd'));//背景颜色
                $background = $background ? '#' . $background : '#000';
                $this->assign('background', $background);
            }
            $title = trim(I('get.t'));
            $biankuang = trim(I('get.bg'));//边框颜色
            $biankuang = $biankuang ? '#' . $biankuang : '#ff0000';
            $this->assign('biankuang', $biankuang);

            $btitle = trim(I('get.bt'));//大标题颜色
            $btitle = $btitle ? '#' . $btitle : '#FFC000';
            $this->assign('btitle', $btitle);

            $xtitle = trim(I('get.xt'));//小标题颜色
            $xtitle = $xtitle ? '#' . $xtitle : '#FFFF00';
            $this->assign('xtitle', $xtitle);

            $zcon = trim(I('get.zc'));//主体内容颜色
            $zcon = $zcon ? '#' . $zcon : '#FF0000';
            $this->assign('zcon', $zcon);

            $page = (int)trim(I('get.p'));//条数
            if ($page < 10) {
                $page = 0;
            }
            if ($page > 50) {
                $page = 50;
            }
            $sec = (int)trim(I('get.sec'));
            if ($sec < 5) {
                $sec = 5;
            }
            if ($sec > 50) {
                $sec = 50;
            }
            $this->assign('page', $page);
            $this->assign('sec', $sec);
            $this->assign('http_host', C('HTTP_HOST'));
            $dbname = C('DB_NAME');
            $dbname = str_replace('tecev_item_', '', $dbname);
            $dbname = str_replace('_data', '', $dbname);
            $dbname = MD5($dbname);
            $this->assign('uid', getRandomId() . '@' . $dbname);
            //汤圆酱 start
            $statusColor = json_decode(htmlspecialchars_decode(I('get.status_color')), true);//状态颜色
            $statusArr = $statusColor ? $statusColor : [
                ["value" => C('REPAIR_HAVE_REPAIRED'), "name" => C('REPAIR_HAVE_REPAIRED_NAME'), "color" => "#ff0000"],
                ["value" => C('REPAIR_RECEIPT'), 'name' => C('REPAIR_RECEIPT_NAME'), "color" => "#ff0000"],
                ["value" => C('REPAIR_HAVE_OVERHAULED'), "name" => C('REPAIR_HAVE_OVERHAULED_NAME'), "color" => "#ff0000"],
                ["value" => C('REPAIR_AUDIT'), 'name' => C('REPAIR_AUDIT_NAME'), "color" => "#ff0000"],
                ["value" => C('REPAIR_MAINTENANCE'), "name" => C('REPAIR_MAINTENANCE_NAME'), "color" => "#ff0000"],
                ["value" => C('REPAIR_MAINTENANCE_COMPLETION'), "name" => C('REPAIR_MAINTENANCE_COMPLETION_NAME'), "color" => "#ff0000"]
            ];
            $this->assign('status_color', json_encode($statusArr));
            //汤圆酱 end
            $hosidarr = [];
            if (count($hosinfo) > 1) {
                //存在多个医院
                $hos_code = trim(I('get.code'));
                if (!$hos_code) {
                    //没有医院代码参数，返回页面提示选择一个医院显示
                    $this->assign('show_sel', 1);
                    $this->assign('hosinfo', $hosinfo);
                    $this->assign('alldata', json_encode(array()));
                    $this->display();
                } else {
                    //有医院代码参数，选择相应的医院数据显示
                    if ($hos_code == 'all') {
                        //显示全部医院信息
                        foreach ($hosinfo as $v) {
                            $hosidarr[] = (int)$v['hospital_id'];
                        }
                        $title = $title ? $title : $hosinfo[0]['hospital_name'];
                        $this->assign('title', $title);
                    } else {
                        //只显示单个医院信息
                        foreach ($hosinfo as $v) {
                            if ($v['hospital_code'] == $hos_code) {
                                $hosidarr[] = (int)$v['hospital_id'];
                                $title = $title ? $title : $v['hospital_name'];
                                $this->assign('title', $title);
                                break;
                            }
                        }
                    }
                    if (!$hosidarr) {
                        exit('查询不到医院代码为【' . $hos_code . '】的信息！');
                    } else {
                        //获取相应医院的信息
                        $hos_data = $repModel->getHosScreenInfo($hosidarr);
                        $this->assign('show_sel', 0);
                        $this->assign('data', $hos_data);
                        $this->assign('alldata', json_encode($hos_data));
                        $this->display();
                    }
                }
            } else {
                //只有一个医院，选择相应的医院数据显示
                $hosidarr[] = (int)$hosinfo[0]['hospital_id'];
                $hos_data = $repModel->getHosScreenInfo($hosidarr);
                $this->assign('show_sel', 0);
                $this->assign('data', $hos_data);
                $this->assign('alldata', json_encode($hos_data));
                $title = $title ? $title : $hosinfo[0]['hospital_name'];
                $this->assign('title', $title);
                $this->display();
            }
        }
    }

    public function testPush()
    {
        if (!session('userid')) {
            redirect(U("Admin/Login/Login/login"));
            exit;
        }
        $data[0] = [
            'type_action' => 'add',
            'type_name' => '设备维修',
            'assets' => '----测试推送消息---',
            'assnum' => '66778899022',
            'department' => '新生儿科',
            'remark' => '如推送成功则可以正常运行',
            'status' => '1',
            'status_name' => '已报修',
            'time' => '2019-12-03 09:52',
            'username' => '牛年(13800138000)',
        ];
        $result = push_messages($data);
        $result = trim($result);
        if ($result == 'ok') {
            echo('test ok');
        } else {
            echo('test fail');
        }
    }

    /**
     * 将pdf文件转化为多张png图片
     * @param string $pdf pdf所在路径 （/www/pdf/abc.pdf pdf所在的绝对路径）
     * @param string $path 新生成图片所在路径 (/www/pngs/)
     *
     * @return array|bool
     */
    public function pdf2png()
    {
        $pdf = '/var/www/html/tecev-new/1.pdf';
        $path = './Public/pdf';
        if (!extension_loaded('imagick')) {
            exit('imagick模块未安装');
        }
        if (!file_exists($pdf)) {
            exit('文件不存在');
        }
        $fileone = realpath($pdf);
        if (!is_readable($fileone)) {
            exit('file not readable');
        }

        $im = new \Imagick();
//        $im->setResolution(120, 120); //设置分辨率 值越大分辨率越高
//        $im->setCompressionQuality(100);
        $im->readImage($pdf);
        $return = [];
        foreach ($im as $k => $v) {
            $v->setImageFormat('png');
            $fileName = $path . md5($k . time()) . '.png';
            if ($v->writeImage($fileName) == true) {
                $return[] = $fileName;
            }
        }
        return $return;
    }

    /**
     * 将pdf转化为单一png图片
     * @param string $pdf pdf所在路径 （/www/pdf/abc.pdf pdf所在的绝对路径）
     * @param string $path 新生成图片所在路径 (/www/pngs/)
     *
     * @throws Exception
     */
    public function pdf2png2($pdf, $path)
    {
        try {
            $im = new \Imagick();
            $im->setCompressionQuality(100);
            $im->setResolution(120, 120);//设置分辨率 值越大分辨率越高
            $im->readImage($pdf);

            $canvas = new \Imagick();
            $imgNum = $im->getNumberImages();
            //$canvas->setResolution(120, 120);
            foreach ($im as $k => $sub) {
                $sub->setImageFormat('png');
                //$sub->setResolution(120, 120);
                $sub->stripImage();
                $sub->trimImage(0);
                $width = $sub->getImageWidth() + 10;
                $height = $sub->getImageHeight() + 10;
                if ($k + 1 == $imgNum) {
                    $height += 10;
                } //最后添加10的height
                $canvas->newImage($width, $height, new \ImagickPixel('white'));
                $canvas->compositeImage($sub, Imagick::COMPOSITE_DEFAULT, 5, 5);
            }

            $canvas->resetIterator();
            $canvas->appendImages(true)->writeImage($path . microtime(true) . '.png');
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function scr()
    {
        if (IS_POST) {
            $pw = I('post.pw');
            $model = new UserModel();
            $result = $model->loginVerify('牛年', $pw);
            if ($result['status'] == -1) {
                $this->ajaxReturn($result);
            } else {
                session('scr_pw', 1);
                $this->ajaxReturn(array('status' => 1, 'msg' => 'success'));
            }
        } else {
            $userid = session('userid');
            $pw = session('scr_pw');
            if ($userid || $pw) {
                $this->assign('pw', 1);
                $hospital_id = 1;
                $baseModel = new BaseSettingModel();
                $hos_name = $baseModel->DB_get_one('hospital', 'hospital_name', ['hospital_id' => $hospital_id, 'is_delete' => 0]);
                $hospital_name = $hos_name['hospital_name'] ? $hos_name['hospital_name'] : '查找不到相关医院信息';
                //$this->assign('http_host', C('HOST_IP'));//特殊情况下使用IP
                $this->assign('http_host', $_SERVER['HTTP_HOST']);
                $dbname = C('DB_NAME');
                $dbname = str_replace('tecev_item_', '', $dbname);
                $dbname = str_replace('_data', '', $dbname);
                $dbname = MD5($dbname);
                $this->assign('uid', getRandomId() . '@' . $dbname);
                $this->assign('url', get_url());
                $this->assign('month', (int)date('m'));
                $weekarray = array("日", "一", "二", "三", "四", "五", "六");
                $now = date('Y-m-d') . ' 星期' . $weekarray[date('w')];
                $this->assign('today', $now);
                $this->assign('now', date('H:i:s'));
                $this->assign('hospital_name', $hospital_name);
            } else {
                //$this->assign('http_host', C('HOST_IP'));//特殊情况下使用IP
                $this->assign('http_host', $_SERVER['HTTP_HOST']);
                $dbname = C('DB_NAME');
                $dbname = str_replace('tecev_item_', '', $dbname);
                $dbname = str_replace('_data', '', $dbname);
                $dbname = MD5($dbname);
                $uid = getRandomId() . '@' . $dbname;
                $this->assign('uid', $uid);
                //生成二维码图片
                $repModel = new RepairModel();
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $url = "$protocol" . C('HTTP_HOST') . C('MOBILE_NAME') . '/Tasks/setPw?uid=' . $uid;
                $codeUrl = $repModel->createCodePic($url);
                $codeUrl = trim($codeUrl, '.');
                $this->assign('codeUrl', $codeUrl);
                $this->assign('pw', 0);
            }
            $this->display();
        }
    }

    public function upScr()
    {
        sleep(3);
        //当月份维修单汇总
        $this->monthRepairOrder();
        //当月份科室故障率
        $this->monthRepairDepart();
        //当年每月份维修单汇总
        $this->eachMonthRepairNum();
        //当月份保养计划情况
        $this->assetsPatrol();
        //当前维修数据实时状况
        $this->repairStatus();
        //设备运行状况
        $this->assetsOperate();
        //当月份科室报修top10
        $this->monthTopRepair();
        //全院资产概况
        $this->assetsStatusNums();
        //最新公告
        $this->getNewNotice();
        $this->ajaxReturn(array('status' => 1, 'msg' => 'success'));
    }

    /**
     * Notes: 当月份维修单汇总
     */
    public function monthRepairOrder()
    {
        $hospital_id = 1;
        $day = date('t');
        $start_date = date('Y-m') . '-01 00:00:01';
        $end_date = date('Y-m') . '-' . $day . ' 23:59:59';
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        $where['A.applicant_time'] = array(array('egt', $start_time), array('elt', $end_time));
        $where['A.status'] = array('egt', 0);//撤单-1 的不统计
        $where['B.hospital_id'] = $hospital_id;
        $repModel = new RepairModel();
        $fields = 'A.repid,A.assid,A.departid,A.status';
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $data = $repModel->DB_get_all_join('repair', 'A', $fields, $join, $where);
        $res['djd'] = 0;
        $res['ljbx'] = count($data);
        $res['ljwc'] = 0;
        $res['ljwwc'] = 0;
        foreach ($data as $v) {
            if ($v['status'] == C('REPAIR_HAVE_REPAIRED')) {
                //待接单
                $res['djd'] += 1;
            } elseif ($v['status'] == C('REPAIR_ALREADY_ACCEPTED')) {
                //已验收
                $res['ljwc'] += 1;
            }
        }
        $res['ljwwc'] = $res['ljbx'] - $res['ljwc'];
        $push_data['target'] = 'month_repair_order';
        $push_data['month'] = (int)date('m');
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    /**
     * Notes: 当月份科室故障率
     */
    public function monthRepairDepart()
    {
        $hospital_id = 1;
        $day = date('t');
        $start_date = date('Y-m') . '-01 00:00:01';
        $end_date = date('Y-m') . '-' . $day . ' 23:59:59';
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        $where['A.applicant_time'] = array(array('egt', $start_time), array('elt', $end_time));
        $where['A.status'] = array('egt', 0);//撤单-1 的不统计
        $where['B.hospital_id'] = $hospital_id;
        $repModel = new RepairModel();
        //获取所有科室数据
        $res = $repModel->DB_get_all('department', 'departid,department,assetssum', ['hospital_id' => $hospital_id, 'is_delete' => 0]);
        $fields = 'A.repid,A.assid,A.departid,A.status';
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $data = $repModel->DB_get_all_join('repair', 'A', $fields, $join, $where);
        foreach ($res as &$v) {
            $v['repair_num'] = 0;
            foreach ($data as $kv) {
                if ($kv['departid'] == $v['departid']) {
                    $v['repair_num'] += 1;
                }
            }
            $rate = round($v['repair_num'] / $v['assetssum'] * 100, 2);
            $rate = $rate < 50 ? $rate . '%' : '<span style="color: red;">' . $rate . '%</span>';
            $v['repair_rate'] = $rate;
        }
        //报修多的科室排前面
        array_multisort(array_column($res, 'repair_num'), SORT_DESC, $res);
        $push_data['target'] = 'month_repair_depart';
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    /**
     * Notes: 当年每月份维修单汇总
     */
    public function eachMonthRepairNum()
    {
        $hospital_id = 1;
        $year = date('Y');
        $start_date = $year . '-01-01 00:00:01';
        $end_date = $year . '-12-31 23:59:59';
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        $where['A.applicant_time'] = array(array('egt', $start_time), array('elt', $end_time));
        $where['A.status'] = array('egt', 0);//撤单-1 的不统计
        $where['B.hospital_id'] = $hospital_id;
        $repModel = new RepairModel();
        $fields = 'count(*) as nums,FROM_UNIXTIME(A.applicant_time,\'%m\') as months';
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $data = $repModel->DB_get_all_join('repair', 'A', $fields, $join, $where, 'months');
        $res = [];
        for ($i = 0; $i < 12; $i++) {
            $res[$i] = 0;
        }
        foreach ($data as $v) {
            $mon = (int)$v['months'];
            $res[$mon - 1] = (int)$v['nums'];
        }
        $push_data['target'] = 'month_repair_num';
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    /**
     * Notes: 当月份保养计划情况（只统计已发布的）
     */
    public function assetsPatrol()
    {
        $hospital_id = 1;
        $day = date('t');
//        $start_date = date('Y-m') . '-01 00:00:01';
        $start_date = date('Y-m') . '-01';
//        $end_date = date('Y-m') . '-' . $day . ' 23:59:59';
        $end_date = date('Y-m') . '-' . $day;
        $where['A.cycle_start_date'] = array(array('egt', $start_date), array('elt', $end_date),'and');
        $where['B.hospital_id'] = $hospital_id;
        $where['B.is_release'] = 1;//已发布的计划
        $join = ' LEFT JOIN sb_patrol_plans AS B ON A.patrid = B.patrid ';
        $partolModel = new PatrolPlanModel();
        $plans = $partolModel->DB_get_all_join('patrol_plans_cycle','A', 'A.assets_departid,A.assets_nums,A.implement_sum',$join, $where);
        $depart_ids = $res = [];
        $plans_num = $implement_sum = $depart_nums = 0;
        foreach ($plans as $k => $v) {
            $depart_ids[] = $v['assets_departid'];
            $plans_num += $v['assets_nums'];
            $implement_sum += $v['implement_sum'];
        }
        $depart_ids = array_unique($depart_ids);
        $depart_ids = array_values($depart_ids);

        $res['plans_num'] = $plans_num;
        $res['implement_sum'] = $implement_sum;
        $res['departs_num'] = count($depart_ids);
        $res['not_complete'] = $plans_num - $implement_sum;
        $push_data['target'] = 'assets_patrol';
        $push_data['month'] = (int)date('m');
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    /**
     * Notes: 当前维修数据实时状况
     */
    public function repairStatus()
    {
        $page = 10;//每页条数
        $hospital_id = 1;
        $where['A.status'] = array(array('neq', C('REPAIR_ALREADY_ACCEPTED')),array('egt', 0),'and');
        $where['B.is_delete'] = 0;
        $where['B.hospital_id'] = $hospital_id;
        $repModel = new RepairModel();
        $fields = 'A.repid,A.assid,A.applicant_time,A.response,A.status,B.assets,B.model,B.departid';
        $join = ' LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $data = $repModel->DB_get_all_join('repair', 'A', $fields, $join, $where);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $tips['r_djd'] = $tips['r_jxz'] = $tips['r_wxclz'] = $tips['r_dys'] = $tips['r_jrxf'] = 0;
        foreach ($data as &$v) {
            $v['response'] = $v['response'] ? $v['response'] : '';
            $v['department'] = $departname[$v['departid']]['department'];
            $v['applicant_date'] = date('Y-m-d H:i', $v['applicant_time']);
            switch ($v['status']) {
                case C('REPAIR_HAVE_REPAIRED')://待接单
                    $v['status_name'] = '<span style="color: #DAFF0D;">待接单</span>';
                    $tips['r_djd'] += 1;
                    break;
                case C('REPAIR_RECEIPT_NAME')://已接单待检修
                    $v['status_name'] = '<span style="color: #FF3300;">检修中</span>';
                    $tips['r_jxz'] += 1;
                    break;
                case C('REPAIR_MAINTENANCE_COMPLETION')://待验收
                    $v['status_name'] = '<span style="color: #00FF00;">待验收</span>';
                    $tips['r_dys'] += 1;
                    break;
                default:
                    $v['status_name'] = '<span style="color: #0099FF;">维修处理中</span>';
                    $tips['r_wxclz'] += 1;
                    break;
            }
        }
        //最新报修的排前面
        array_multisort(array_column($data, 'applicant_date'), SORT_DESC, $data);
        $total = count($data);
        //需要补充$n条空数据
        $n = $page - ($total % $page);
        for ($i = 0; $i < $n; $i++) {
            $data[$total + $i]['department'] = '';
            $data[$total + $i]['applicant_date'] = '';
            $data[$total + $i]['assets'] = '';
            $data[$total + $i]['model'] = '';
            $data[$total + $i]['response'] = '';
            $data[$total + $i]['status_name'] = '';
        }
        //统计今天修复的数量
        $start_date = date('Y-m-d') . ' 00:00:01';
        $end_date = date('Y-m-d') . ' 23:59:59';
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        $to_where['A.checkdate'] = array(array('egt', $start_time), array('elt', $end_time));;
        $to_where['A.status'] = array('eq', C('REPAIR_ALREADY_ACCEPTED'));
        $to_where['B.is_delete'] = 0;
        $to_where['B.hospital_id'] = $hospital_id;
        $today_num = $repModel->DB_get_count_join('repair', 'A', $join, $to_where);
        $tips['r_jrxf'] = (int)$today_num;
        $push_data['target'] = 'repair_status';
        $push_data['tips'] = $tips;
        $push_data['data'] = $data;
        push_messages_to_new_screen($push_data);
    }

    /**
     * Notes: 设备运行状况
     */
    public function assetsOperate()
    {
        $hospital_id = 1;
        $n = 30;//最近30天的数据（含今天）
        for ($i = 1; $i <= $n; $i++) {
            $res['days'][] = $i;
            $res['rep_num'][] = 0;
            $res['kjl'][] = 1;
        }
        for ($i = 1; $i <= 30; $i++) {
            $d = $n - $i;
            $res['dates'][] = date("Y-m-d", strtotime("-$d day"));
        }
        $asModel = new AssetsInfoModel();
        //统计设备总数
        $as_where['is_delete'] = C('NO_STATUS');
        $as_where['hospital_id'] = $hospital_id;
        $as_where['status'][0] = 'NOT IN';
        $as_where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        $as_where['status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');//外调中
        $total = $asModel->DB_get_count('assets_info', $as_where);
        //开机率（设备总数-故障设备总数）/设备总数=开机率
        //统计最近30天维修的设备数量
        $start_time = strtotime($res['dates'][0] . ' 00:00:01');
        $end_time = strtotime($res['dates'][$n - 1] . ' 23:59:59');
        $where['A.applicant_time'] = array(array('egt', $start_time), array('elt', $end_time));
        $where['A.status'] = array('egt', 0);//撤单-1 的不统计
        $where['B.hospital_id'] = $hospital_id;

        $fields = 'count(*) as rep_num,FROM_UNIXTIME(A.applicant_time,\'%Y-%m-%d\') as dates';
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $data = $asModel->DB_get_all_join('repair', 'A', $fields, $join, $where, 'dates');
        foreach ($res['dates'] as $k => $v) {
            foreach ($data as $dk => $dv) {
                if ($v == $dv['dates']) {
                    $res['rep_num'][$k] = (int)$dv['rep_num'];
                    $res['kjl'][$k] = round(($total - (int)$dv['rep_num']) / $total, 2);
                }
            }
        }
        $push_data['target'] = 'assets_operate';
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    /**
     * Notes: 当月份科室报修top10
     */
    public function monthTopRepair()
    {
        $hospital_id = 1;
        $day = date('t');
        $start_date = date('Y-m') . '-01 00:00:01';
        $end_date = date('Y-m') . '-' . $day . ' 23:59:59';
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        $where['A.applicant_time'] = array(array('egt', $start_time), array('elt', $end_time));
        $where['A.status'] = array('egt', 0);//撤单-1 的不统计
        $where['B.hospital_id'] = $hospital_id;
        $repModel = new RepairModel();
        $fields = 'count(*) as total_nums,A.departid';
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $data = $repModel->DB_get_all_join('repair', 'A', $fields, $join, $where, 'A.departid', 'total_nums desc', '');
        $tmp = $res = $depart_name = [];
        $others_nums = 0;
        foreach ($data as $k => $v) {
            if ($k < 10) {
                $tmp[] = $v;
                unset($data[$k]);
            }
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($tmp as $k => $v) {
            $res[$k]['value'] = $v['total_nums'];
            $depart_name[] = $departname[$v['departid']]['department'];
            $res[$k]['name'] = $departname[$v['departid']]['department'];
        }
        foreach ($data as $k => $v) {
            $others_nums += $v['total_nums'];
        }
        if ($res) {
            $count = count($res);
            $res[$count]['value'] = $others_nums;
            $res[$count]['name'] = '其他科室总和';
            $depart_name[] = '其他科室总和';
        }
        $push_data['target'] = 'top_repair';
        $push_data['depart_name'] = $depart_name;
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    /**
     * Notes: 全院资产概况
     */
    public function assetsStatusNums()
    {
        $hospital_id = 1;
        $as_where['hospital_id'] = $hospital_id;
        $as_where['is_delete'] = C('NO_STATUS');
        $as_where['status'] = array('in', [C('ASSETS_STATUS_USE'), C('ASSETS_STATUS_REPAIR')]);//统计在用、维修中
        //排除已删除科室
        $del_departids = $this->get_delete_departids($hospital_id);
        if ($del_departids) {
            $as_where['departid'] = array('not in', $del_departids);
        }
        $asModel = new AssetsInfoModel();
        $use_repair_data = $asModel->DB_get_all('assets_info', 'count(*) as num,status', $as_where, 'status');
        $res = $data1 = $data2 = [];
        foreach ($use_repair_data as $k => $v) {
            if ($v['status'] == C('ASSETS_STATUS_USE')) {
                //在用设备
                $data1[$k]['name'] = C('ASSETS_STATUS_USE_NAME') . '设备';
                $data1[$k]['value'] = (int)$v['num'];
            } else {
                //维修中
                $data1[$k]['name'] = C('ASSETS_STATUS_REPAIR_NAME');
                $data1[$k]['value'] = (int)$v['num'];
            }
        }
        //统计设备类型
        $n = 0;
        $status_where['status'][0] = 'NOT IN';
        $status_where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        $status_where['status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');//外调中

        //统计急救设备
        $firstaid_assets_where = $status_where;
        $firstaid_assets_where['hospital_id'] = $hospital_id;
        $firstaid_assets_where['is_firstaid'] = C('YES_STATUS');
        $firstaid_assets_where['is_delete'] = C('NO_STATUS');
        $is_firstaid_num = $asModel->DB_get_count('assets_info', $firstaid_assets_where);
        $data2[$n]['name'] = '急救设备';
        $data2[$n]['value'] = (int)$is_firstaid_num;
        $n++;

        //统计质控设备
        $quality_assets_where = $status_where;
        $quality_assets_where['hospital_id'] = $hospital_id;
        $quality_assets_where['is_qualityAssets'] = C('YES_STATUS');
        $quality_assets_where['is_delete'] = C('NO_STATUS');
        $is_qualityAssets_num = $asModel->DB_get_count('assets_info', $quality_assets_where);
        $data2[$n]['name'] = '质控设备';
        $data2[$n]['value'] = (int)$is_qualityAssets_num;
        $n++;

        //统计生命支持设备
        $is_lifesupport_num = $asModel->DB_get_count('assets_info', array('hospital_id' => $hospital_id, 'is_delete' => C('NO_STATUS'), 'is_lifesupport' => C('YES_STATUS')));
        $data2[$n]['name'] = '生命支持类';
        $data2[$n]['value'] = (int)$is_lifesupport_num;
        $n++;


        //统计特种设备
        $is_special_num = $asModel->DB_get_count('assets_info', array('hospital_id' => $hospital_id, 'is_delete' => C('NO_STATUS'), 'is_special' => C('YES_STATUS')));
        $data2[$n]['name'] = '特种设备';
        $data2[$n]['value'] = (int)$is_special_num;
        $n++;

        //统计计量设备
        $metering_assets_where = $status_where;
        $metering_assets_where['hospital_id'] = $hospital_id;
        $metering_assets_where['is_metering'] = C('YES_STATUS');
        $metering_assets_where['is_delete'] = C('NO_STATUS');
        $is_metering_assets_num = $asModel->DB_get_count('assets_info', $metering_assets_where);
        $data2[$n]['name'] = '计量设备';
        $data2[$n]['value'] = (int)$is_metering_assets_num;
        $n++;

        //统计在保设备
        $guarantee_num = $asModel->DB_get_count('assets_info', array('hospital_id' => $hospital_id, 'is_delete' => C('NO_STATUS'), 'guarantee_date' => array('EGT', date('Y-m-d'))));
        $data2[$n]['name'] = '在保设备';
        $data2[$n]['value'] = (int)$guarantee_num;
        $n++;

        $res[] = $data1;
        $res[] = $data2;
        $push_data['target'] = 'assets_status_nums';
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    private function get_delete_departids($hospital_id)
    {
        $deModel = new DepartmentModel();
        $ids = $deModel->DB_get_one('department', 'group_concat(departid) as ids', array('is_delete' => 1, 'hospital_id' => $hospital_id));
        return $ids['ids'];
    }

    //获取最新公告标题
    public function getNewNotice()
    {
        $hospital_id = 1;
        $notModel = new NoticeModel();
        $notice = $notModel->DB_get_one('notice', 'title', array('hospital_id' => $hospital_id), 'notid desc');
        $push_data['target'] = 'notice_title';
        $push_data['data'] = $notice;
        push_messages_to_new_screen($push_data);
    }

    //新版大屏
    public function newScr()
    {
        if (IS_POST) {
            sleep(3);
            //维修实时数据
            $this->real_time_repair();

            //维修中、自修率、开机率、及时率数据统计
            $this->other_repair();

            //当月份科室报修top10
            $this->month_top_repair_fee();

            //当月份保养计划情况（第二级巡查保养计划）
            $this->current_month_partol_plan($level = 2,'rc_patrol');

            //当月份预防性维护计划情况（第三级计划）
            $this->current_month_partol_plan($level = 3,'pm_patrol');

            //当月份质控计划情况
            $this->current_month_quality_plan();

            //当月份计量计划情况
            $this->current_month_metering_plan();

            //当月报修总数
            $this->current_month_repair();

            //当月工程师工作进度
            $this->current_month_progress();

            $this->ajaxReturn(array('status' => 1, 'msg' => 'success'));
        } else {
            $dbname = C('DB_NAME');
            $dbname = str_replace('tecev_item_', '', $dbname);
            $dbname = str_replace('_data', '', $dbname);
            $dbname = MD5($dbname);
            $uid = getRandomId() . '@' . $dbname;
            $this->assign('uid', $uid);
            $this->assign('http_host', $_SERVER['HTTP_HOST']);
            $this->display();
        }
    }

    //维修实时数据
    public function real_time_repair()
    {
        $page = 8;//每页条数
        $hospital_id = 1;
        $where['A.status'] = array(array('neq', C('REPAIR_ALREADY_ACCEPTED')),array('egt', 0),'and');//已验收 已撤单的除外
        $where['B.is_delete'] = 0;
        $where['B.hospital_id'] = $hospital_id;
        $repModel = new RepairModel();
        $fields = 'A.repid,A.assid,A.applicant_time,A.response,A.status,A.overhauldate,B.assets,B.assnum,B.model,B.departid';
        $join = ' LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $data = $repModel->DB_get_all_join('repair', 'A', $fields, $join, $where);
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $overhaul_timeout = $repModel->DB_get_one('base_setting', 'value', array('set_item' => 'repair_overhaul_timeout'));
        $overhaul_timeout = (int)$overhaul_timeout['value']*60;
        foreach ($data as &$v) {
            $v['response'] = $v['response'] ? $v['response'] : '--';
            $v['department'] = $departname[$v['departid']]['department'];
            $v['applicant_date'] = date('Y-m-d H:i', $v['applicant_time']);
            $status_name = '';
            if($v['overhauldate']){
                if($v['overhauldate'] - $v['applicant_time'] > $overhaul_timeout){
                    $status_name = '<span style="color:red">检修(超时)</span>';
                }
            }else{
                if(time() - $v['applicant_time'] > $overhaul_timeout){
                    $status_name = '<span style="color:red">检修(超时)</span>';
                }
            }
            switch ($v['status']) {
                case C('REPAIR_HAVE_REPAIRED')://1待接单
                    if($status_name){
                        $v['status_name'] = $status_name;
                    }else{
                        $v['status_name'] = '<span style="color:#01AAED">待接单</span>';
                    }
                    $v['progress'] = '<div class="layui-row line-height-row">
                                    <div class="layui-col-md9">
                                        <div class="layui-progress">
                                            <div class="layui-progress-bar layui-bg-blue" lay-percent="0%"></div>
                                        </div>
                                    </div>
                                    <div class="layui-col-md3">0%</div>
                                </div>';
                    break;
                case C('REPAIR_RECEIPT')://2已接单待检修
                    if($status_name){
                        $v['status_name'] = $status_name;
                    }else{
                        $v['status_name'] = '已接单';
                    }
                    $v['progress'] = '<div class="layui-row line-height-row">
                                    <div class="layui-col-md9">
                                        <div class="layui-progress">
                                            <div class="layui-progress-bar layui-bg-blue" lay-percent="20%"></div>
                                        </div>
                                    </div>
                                    <div class="layui-col-md3">20%</div>
                                </div>';
                    break;
                case C('REPAIR_HAVE_OVERHAULED')://3已检修
                    if($status_name){
                        $v['status_name'] = $status_name;
                    }else{
                        $v['status_name'] = '已检修';
                    }
                    $v['progress'] = '<div class="layui-row line-height-row">
                                    <div class="layui-col-md9">
                                        <div class="layui-progress">
                                            <div class="layui-progress-bar layui-bg-blue" lay-percent="40%"></div>
                                        </div>
                                    </div>
                                    <div class="layui-col-md3">40%</div>
                                </div>';
                    break;
                case C('REPAIR_AUDIT')://5审核中
                    if($status_name){
                        $v['status_name'] = $status_name;
                    }else{
                        $v['status_name'] = '待审核';
                    }
                    $v['progress'] = '<div class="layui-row line-height-row">
                                    <div class="layui-col-md9">
                                        <div class="layui-progress">
                                            <div class="layui-progress-bar layui-bg-blue" lay-percent="60%"></div>
                                        </div>
                                    </div>
                                    <div class="layui-col-md3">60%</div>
                                </div>';
                    break;
                case C('REPAIR_MAINTENANCE')://6维修中
                    if($status_name){
                        $v['status_name'] = $status_name;
                    }else{
                        $v['status_name'] = '维修中';
                    }
                    $v['progress'] = '<div class="layui-row line-height-row">
                                    <div class="layui-col-md9">
                                        <div class="layui-progress">
                                            <div class="layui-progress-bar layui-bg-blue" lay-percent="80%"></div>
                                        </div>
                                    </div>
                                    <div class="layui-col-md3">80%</div>
                                </div>';
                    break;
                case C('REPAIR_MAINTENANCE_COMPLETION')://待验收
                    if($status_name){
                        $v['status_name'] = $status_name;
                    }else{
                        $v['status_name'] = '<span style="color: green;">修复</span>';
                    }
                    $v['progress'] = '<div class="layui-row line-height-row">
                                    <div class="layui-col-md9">
                                        <div class="layui-progress">
                                            <div class="layui-progress-bar layui-bg-blue" lay-percent="100%"></div>
                                        </div>
                                    </div>
                                    <div class="layui-col-md3"><i class="layui-icon layui-icon-ok-circle" style="font-size: 12px; color: green;"></i></div>
                                </div>';
                    break;
                default:
                    if($status_name){
                        $v['status_name'] = $status_name;
                    }else{
                        $v['status_name'] = '维修中';
                    }
                    $v['progress'] = '';
                    $v['percent'] = '80%';
                    break;
            }
        }
        //需要补充$n条空数据
        $total = count($data);
        $n = $page - ($total % $page);
        for ($i = 0; $i < $n; $i++) {
            $data[$total + $i]['department'] = '';
            $data[$total + $i]['applicant_date'] = '';
            $data[$total + $i]['assets'] = '';
            $data[$total + $i]['assnum'] = '';
            $data[$total + $i]['model'] = '';
            $data[$total + $i]['response'] = '';
            $data[$total + $i]['status_name'] = '';
            $data[$total + $i]['progress'] = '';
        }
        $push_data['target'] = 'repair_status';
        $push_data['data'] = $data;
        push_messages_to_new_screen($push_data);
    }

    //维修中、自修率、开机率、及时率数据统计
    public function other_repair()
    {
        $hospital_id = 1;
        $where['B.hospital_id'] = $hospital_id;
        $where['A.status'] = array('egt', 0);//撤单-1 的不统计
        $repModel = new RepairModel();
        $fields = 'A.repid,A.assid,A.departid,A.status,A.repair_type,A.overhauldate,A.applicant_time';
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $data = $repModel->DB_get_all_join('repair', 'A', $fields, $join, $where);
        $res['wxz'] = 0;//维修中
        $res['zx'] = 0;//自修数
        $res['zxl'] = '0%';//自修率
        $res['kjl'] = '0%';//开机率
        $res['zy'] = 0;//在用
        $res['ontime'] = 0;//及时检修数
        $res['jsl'] = '0%';//及时率
        foreach ($data as $v) {
            if ($v['status'] != C('REPAIR_ALREADY_ACCEPTED')) {
                //排除未验收的
                $res['wxz'] += 1;
            }
            if ($v['repair_type'] != C('REPAIR_TYPE_IS_STUDY')) {
                //自修数
                $res['zx'] += 1;
            }
        }
        $res['zxl'] = round($res['zx'] / count($data) * 100, 2) . '%';
        //总设备数
        $as_where['is_delete'] = 0;
        $as_where['hospital_id'] = $hospital_id;
        $as_where['status'][0] = 'NOT IN';
        $as_where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        $as_where['status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');
        $total_assets_nums = $repModel->DB_get_count('assets_info', $as_where);
        $use_where['is_delete'] = 0;
        $use_where['hospital_id'] = $hospital_id;
        $use_where['status'] = 0;
        $use_assets_nums = $repModel->DB_get_count('assets_info', $use_where);
        $res['zy'] = (int)$use_assets_nums;
        $res['kjl'] = round($use_assets_nums / $total_assets_nums * 100, 2) . '%';
        $y = date("Y");
        $m = date("n");
        $d = date("j");
        $res['jz'] = $y . '年' . $m . '月' . $d . '日';

        //统计本月及时率
        $day = date('t');
        $start_date = date('Y-m') . '-01 00:00:01';
        $end_date = date('Y-m') . '-' . $day . ' 23:59:59';
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        $where['A.applicant_time'] = array(array('egt', $start_time), array('elt', $end_time));
        $mon_data = $repModel->DB_get_all_join('repair', 'A', $fields, $join, $where);
        $overhaul_timeout = $repModel->DB_get_one('base_setting', 'value', array('set_item' => 'repair_overhaul_timeout'));
        $overhaul_timeout = (int)$overhaul_timeout['value']*60;
        foreach ($mon_data as $v) {
            //及时数 及时率
            if($v['overhauldate']){
                if($v['overhauldate'] - $v['applicant_time'] <= $overhaul_timeout){
                    $res['ontime'] += 1;
                }
            }
        }
        //及时率
        $res['jsl'] = round($res['ontime'] / count($mon_data) * 100, 2) . '%';

        $push_data['target'] = 'other_repair';
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    //当月份科室报修top10
    public function month_top_repair_fee()
    {
        $hospital_id = 1;
        $day = date('t');
        $start_date = date('Y-m') . '-01 00:00:01';
        $end_date = date('Y-m') . '-' . $day . ' 23:59:59';
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        $where['A.applicant_time'] = array(array('egt', $start_time), array('elt', $end_time));
        $where['A.status'] = array('egt', 0);//撤单-1 的不统计
        $where['B.hospital_id'] = $hospital_id;
        $repModel = new RepairModel();
        $fields = 'count(*) as total_nums,A.departid,GROUP_CONCAT(A.repid) AS repid';
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $data = $repModel->DB_get_all_join('repair', 'A', $fields, $join, $where, 'A.departid', 'total_nums desc', '');
        $tmp = $res = $depart_name = [];
        foreach ($data as $k => $v) {
            if ($k < 10) {
                $tmp[] = $v;
                unset($data[$k]);
            }
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($tmp as $k => $v) {
            $res['total_nums'][$k] = (int)$v['total_nums'];
            $depart_name[] = $departname[$v['departid']]['department'];
            //统计维修费用
            $price = $repModel->DB_get_one('repair', 'IFNULL(SUM(part_total_price), 0)  AS part_total_price', ['repid' => array('in', $v['repid']), 'status' => array('egt', 6)]);
            $res['total_price'][$k] = $price['part_total_price'] / 1000;
        }
        $push_data['target'] = 'top_repair';
        $push_data['depart_name'] = $depart_name;
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    //当月份保养计划情况
    public function current_month_partol_plan($level,$target)
    {
        $hospital_id = session('current_hospitalid') ? session('current_hospitalid') : 1;
        $start_date = date('Y-m-01', strtotime(date("Y-m-d")));
        $end_date = date('Y-m-d', strtotime("$start_date +1 month -1 day"));
        $where['A.cycle_start_date'] = array(array('egt', $start_date), array('elt', $end_date));
        $where['B.hospital_id'] = $hospital_id;
        $where['B.patrol_level'] = $level;
        $fields = 'A.patrid,A.assets_nums,A.implement_sum';
        $join = 'LEFT JOIN sb_patrol_plans AS B ON A.patrid = B.patrid';
        $partolModel = new PatrolPlanModel();
        $plans = $partolModel->DB_get_all_join('patrol_plans_cycle', 'A', $fields, $join, $where);
        $plans_nums = $implement_sum = 0;
        foreach ($plans as $v) {
            $plans_nums += $v['assets_nums'];
            $implement_sum += $v['implement_sum'];
        }
        $res['plans_num'] = $plans_nums;
        $res['implement_sum'] = $implement_sum;
        $res['not_complete'] = $plans_nums - $implement_sum;
        $res['complete'] = round($implement_sum / $plans_nums * 100, 2) . '%';
        $push_data['target'] = $target;
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    //当月份质控计划情况
    public function current_month_quality_plan()
    {
        $hospital_id = 1;
        $start_date = date('Y-m-01', strtotime(date("Y-m-d")));
        $end_date = date('Y-m-d', strtotime("$start_date +1 month -1 day"));
        $where['do_date'] = array(array('egt', $start_date), array('elt', $end_date));
        $where['hospital_id'] = $hospital_id;
        $where['is_start'][0] = 'NOT IN';
        $where['is_start'][1][] = 0;
        $where['is_start'][1][] = 2;
        $fields = 'qsid,is_start,do_date';
        $qualityModel = new QualityModel();
        $plans = $qualityModel->DB_get_all('quality_starts', $fields, $where);
        $complete_nums = 0;
        foreach ($plans as $v) {
            if ($v['is_start'] == 4) {
                $complete_nums += 1;
            }
        }
        $res['plans_num'] = count($plans);
        $res['complete_nums'] = $complete_nums;
        $res['not_complete'] = count($plans) - $complete_nums;
        $res['complete'] = round($complete_nums / count($plans) * 100, 2) . '%';
        $push_data['target'] = 'assets_quality';
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    //当月份计量计划情况
    public function current_month_metering_plan()
    {
        $hospital_id = 1;
        $start_date = date('Y-m-01', strtotime(date("Y-m-d")));
        $end_date = date('Y-m-d', strtotime("$start_date +1 month -1 day"));
        $where['A.this_date'] = array(array('egt', $start_date), array('elt', $end_date));
        $where['B.hospital_id'] = $hospital_id;
        $where['B.status'] = 1;
        $fields = 'A.mpid,A.this_date,A.status';
        $join = 'LEFT JOIN sb_metering_plan AS B ON A.mpid = B.mpid';
        $meterModel = new MeteringModel();
        $plans = $meterModel->DB_get_all_join('metering_result', 'A', $fields, $join, $where);
        $complete_nums = 0;
        foreach ($plans as $v) {
            if ($v['status'] == 1) {
                $complete_nums += 1;
            }
        }
        $res['plans_num'] = count($plans);
        $res['complete_nums'] = $complete_nums;
        $res['not_complete'] = count($plans) - $complete_nums;
        $res['complete'] = round($complete_nums / count($plans) * 100, 2) . '%';
        $push_data['target'] = 'assets_metering';
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    //本月报修数和全院设备数
    public function current_month_repair()
    {
        $hospital_id = 1;
        $start_date = date('Y-m-01', strtotime(date("Y-m-d")));
        $end_date = date('Y-m-d', strtotime("$start_date +1 month -1 day"));
        $start_date = $start_date . ' 00:00:01';
        $end_date = $end_date . ' 23:59:59';
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        $where['A.applicant_time'] = array(array('egt', $start_time), array('elt', $end_time));
        $where['A.status'] = array('egt', 0);//撤单-1 的不统计
        $where['B.hospital_id'] = $hospital_id;
        $repModel = new RepairModel();
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $total = $repModel->DB_get_count_join('repair', 'A', $join, $where);
        $res['current_month'] = (int)$total;

        //统计上一个月报修量
        $pre_start_date = date('Y-m-01', strtotime("-1 month"));
        $pre_end_date = date('Y-m-d', strtotime("$pre_start_date +1 month -1 day"));
        $pre_start_date = $pre_start_date . ' 00:00:01';
        $pre_end_date = $pre_end_date . ' 23:59:59';
        $pre_start_time = strtotime($pre_start_date);
        $pre_end_time = strtotime($pre_end_date);
        $pre_where['A.applicant_time'] = array(array('egt', $pre_start_time), array('elt', $pre_end_time));
        $pre_where['B.hospital_id'] = $hospital_id;
        $pre_total = $repModel->DB_get_count_join('repair', 'A', $join, $pre_where);

        //统计全院设备台账
        $as_where['hospital_id'] = $hospital_id;
        $as_where['status'][0] = 'NOT IN';
        $as_where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        $as_where['status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');
        $as_where['is_delete'] = '0';
        $assets_nums = $repModel->DB_get_count('assets_info', $as_where);

        $res['assets_nums'] = (int)$assets_nums;
        $res['current_month'] = (int)$total;
        $res['pre_month'] = (int)$pre_total;
        $res['huanbi'] = round(($res['current_month'] - $res['pre_month']) / $res['pre_month'] * 100, 2) . '%';
        $res['icon'] = ($res['current_month'] - $res['pre_month']) < 0 ? '<i class="tecevicon tecev-sanjiaoxing-xia" style="font-size: 14px; color: red;"></i>' : '<i class="tecevicon tecev-sanjiaoxing" style="font-size: 14px; color: red;"></i>';

        $push_data['target'] = 'current_repair';
        $push_data['data'] = $res;
        push_messages_to_new_screen($push_data);
    }

    //当月工程师工作进度
    public function current_month_progress()
    {
        $page = 8;//每页条数
        //获取所有工程师
        $hospital_id = session('current_hospitalid') ? session('current_hospitalid') : 1;
        $userModel = new UserModel();
        $engineers = $userModel->getUsers('accept', '', true, false, $hospital_id);

        //获取所有当月报修数据
        $start_date = date('Y-m-01', strtotime(date("Y-m-d")));
        $end_date = date('Y-m-d', strtotime("$start_date +1 month -1 day"));
        $start_date = $start_date . ' 00:00:01';
        $end_date = $end_date . ' 23:59:59';
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        $where['A.applicant_time'] = array(array('egt', $start_time), array('elt', $end_time));
        $where['A.status'] = array('egt', 0);//撤单-1 的不统计
        $where['B.hospital_id'] = $hospital_id;
        $fields = 'A.repid,A.assid,A.status,A.response';
        $repModel = new RepairModel();
        $join = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $repairs = $repModel->DB_get_all_join('repair', 'A', $fields, $join, $where);

        //统计每个工程师未完成维修数
        foreach ($engineers as $k => $v) {
            $engineers[$k]['not_complete_repair'] = 0;
            foreach ($repairs as $rk => $rv) {
                if($v['username'] == $rv['response']){
                    if($rv['status'] != C('REPAIR_ALREADY_ACCEPTED')){
                        $engineers[$k]['not_complete_repair'] += 1;
                    }
                }
            }
        }

        //获取当月所有巡查计划数
        $start_date = date('Y-m-01', strtotime(date("Y-m-d")));
        $end_date = date('Y-m-d', strtotime("$start_date +1 month -1 day"));
        $patrol_where['A.cycle_start_date'] = array(array('egt', $start_date), array('elt', $end_date));
        $patrol_where['B.hospital_id'] = $hospital_id;
        $patrol_where['B.patrol_level'] = array('neq',1);
        $fields = 'A.patrid,A.cycid,A.assets_nums,A.implement_sum';
        $join = 'LEFT JOIN sb_patrol_plans AS B ON A.patrid = B.patrid';
        $partolModel = new PatrolPlanModel();
        $patrol_plans = $partolModel->DB_get_all_join('patrol_plans_cycle', 'A', $fields, $join, $patrol_where);
        $cycids = [];
        foreach ($patrol_plans as $v){
            $cycids[] = (int)$v['cycid'];
        }
        $complete = $user_complete = [];
        if($cycids){
            $complete = $partolModel->DB_get_all('patrol_execute', 'execute_user,COUNT(*) AS complete_nums', ['cycid'=>array('in',$cycids),'status'=>2],'execute_user');
        }
        foreach ($complete as $uv){
            $user_complete[$uv['execute_user']] = $uv['complete_nums'];
        }
        //统计每个工程师完成情况
        foreach ($engineers as $k => $v) {
            $engineers[$k]['patrol_show'] = isset($user_complete[$v['username']]) ? $user_complete[$v['username']] : 0;
        }
        //获取当月所有质控计划数
        $start_date = date('Y-m-01', strtotime(date("Y-m-d")));
        $end_date = date('Y-m-d', strtotime("$start_date +1 month -1 day"));
        $quality_where['do_date'] = array(array('egt', $start_date), array('elt', $end_date));
        $quality_where['hospital_id'] = $hospital_id;
        $quality_where['is_start'][0] = 'NOT IN';
        $quality_where['is_start'][1][] = 0;
        $quality_where['is_start'][1][] = 2;
        $fields = 'qsid,is_start,do_date,username';
        $qualityModel = new QualityModel();
        $quality_plans = $qualityModel->DB_get_all('quality_starts', $fields, $quality_where);
        //统计每个工程师完成情况
        foreach ($engineers as $k => $v) {
            $engineers[$k]['quality_assets_num'] = 0;
            $engineers[$k]['quality_complete_sum'] = 0;
            $engineers[$k]['quality_complete'] = '0%';
            foreach ($quality_plans as $rk => $rv) {
                if($v['username'] == $rv['username']){
                    $engineers[$k]['quality_assets_num'] += 1;
                    if($rv['is_start'] == 4){
                        $engineers[$k]['quality_complete_sum'] += 1;
                    }
                }
            }
            $engineers[$k]['quality_complete'] = round($engineers[$k]['quality_complete_sum'] / $engineers[$k]['quality_assets_num'] * 100, 2) . '%';
            $engineers[$k]['quality_show'] = '<span>'.$engineers[$k]['quality_complete_sum']. ' / ' .$engineers[$k]['quality_assets_num']. '</span><span class="tdb">' .$engineers[$k]['quality_complete']. '</span>';
        }

        //需要补充$n条空数据
        $total = count($engineers);
        $n = $page - ($total % $page);
        for ($i = 0; $i < $n; $i++) {
            $engineers[$total + $i]['username'] = '-';
            $engineers[$total + $i]['not_complete_repair'] = '';
            $engineers[$total + $i]['patrol_show'] = '';
            $engineers[$total + $i]['quality_show'] = '';
        }
        $push_data['target'] = 'engineers_progress';
        $push_data['data'] = $engineers;
        push_messages_to_new_screen($push_data);
    }

    public function yy()
    {
        if (IS_POST) {

        } else {
            $this->display();
        }
    }
}
