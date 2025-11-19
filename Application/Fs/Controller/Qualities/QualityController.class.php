<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/1/24
 * Time: 13:48
 */

namespace Fs\Controller\Qualities;

use Admin\Controller\Tool\ToolController;
use Fs\Controller\Login\IndexController;
use Fs\Model\QualityModel;
use Fs\Model\WxAccessTokenModel;
use Think\Controller;

class QualityController extends IndexController
{
    protected $fail_url = 'Notin/fail.html';//失败跳转地址
    protected $succ_url = 'Notin/suc.html';//成功跳转地址
    protected $index_url = 'Index/testindex.html';//首页地址

    /**
     * Notes: 质控检测（明细录入列表）
     */
    public function qualityDetailList()
    {
        $qualityModel = new QualityModel();
        $result = $qualityModel->get_quality_detail_lists();
        $this->ajaxReturn($result);
    }

    /**
     * Notes: 明细录入
     */
    public function setQualityDetail()
    {
        $qualityModel = new QualityModel();
        if (IS_POST) {
            $type = I('POST.action');
            switch ($type){
                case 'upload':
                    $result = $qualityModel->uploadReport();
                    break;
                case 'upload_pic':
                    $Tool = new ToolController();
                    $dirName = C('UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME');
                    $is_water = true;
                    $watermark = $qualityModel->DB_get_one('base_setting','value',array('module'=>'repair','set_item'=>'repair_print_watermark'));
                    $watermark = json_decode($watermark['value'],true);
                    $qsinfo = $qualityModel->DB_get_one('quality_starts','plan_num',array('qsid'=>I('post.qsid')));
                    $water_text[0] = $watermark['watermark'];
                    $water_text[1] = $qsinfo['plan_num'];
                    $water_text[2] = date('Y-m-d H:i:s');
                    $base64=I('POST.base64Data');
                    $info=$Tool->base64imgsave($base64,$dirName,$is_water,$water_text);
                    if ($info['status'] == C('YES_STATUS')) {
                        // 上传成功
                        $data['qsid'] = I('post.qsid');
                        $data['type'] = I('post.type');
                        $data['file_name'] = $info['formerly'];
                        $data['save_name'] = $info['title'];
                        $data['file_type'] = $info['ext'];
                        $data['file_size'] = round($info['size']/1000/1000,2);
                        $data['file_url'] = $info['src'];
                        $data['add_user'] = session('username');
                        $data['add_time'] = date('Y-m-d H:i:s');
                        $res = $qualityModel->insertData('quality_details_file',$data);
                        if($res){
                            $result['status'] = 1;
                            $result['msg'] = '上传成功！';
                            $result['pic_url'] = $data['file_url'];
                            $result['file_id'] = $res;
                        }else{
                            $result['status'] = -1;
                            $result['msg'] = '上传失败！';
                        }
                    } else {
                        // 上传错误提示错误信息
                        $result['status'] = -1;
                        $result['msg'] = '上传图片失败！';
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
                    $res = $qualityModel->updateData('quality_details_file', array('is_delete'=>1), array('file_id' => $fileid));
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
                    $res = $qualityModel->DB_get_all('quality_details_file', 'file_id,type,file_name,file_url',array('is_delete'=>0,'qsid' => $qsid));
                    $file_data = [];
                    foreach($res as $k=>$v){
                        $file_data[$v['type']][] = $v;
                    }
                    $result['status'] = 1;
                    $result['file_data'] = $file_data;
                    break;
                case 'keepquality':
                    $qualityModel = new \Admin\Model\QualityModel();
                    $result = $qualityModel->keepquality();
                    break;
                default:
                    //保存明细结果
                    $qualityModel = new \Admin\Model\QualityModel();
                    $result = $qualityModel->saveDetail();
                    if ($result['status'] == 1) {
                        //记录日志
                        $qualityModel->addLog('quality_details', M()->getLastSql(), session('username') . '录入了一个设备质控计划明细（计划ID为：' . I('POST.qsid') . '）', I('POST.qsid'), '');
                    }
                    break;
            }
            $this->ajaxReturn($result);
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
            $tolerance = json_decode($qsInfo['tolerance'], true);
            //查询质控照片上传信息
            $files = $qualityModel->DB_get_all('quality_details_file','file_id,type,file_name,file_url',array('qsid'=>$qsid,'is_delete'=>0));
            $file_data = [];
            foreach($files as $k=>$v){
                $file_data[$v['type']][] = $v;
            }
            //查询明细信息
            $detail_data = $qualityModel->DB_get_one('quality_details','*',array('qsid'=>$qsid));
            if($detail_data){
                $detail_data['preset_detection'] = json_decode($detail_data['preset_detection'],true);
                $detail_data['fixed_detection'] = json_decode($detail_data['fixed_detection'],true);
            }
            //当无明细信息获取暂存信息
                if (!$detail_data&&$qsInfo['keepdata']) {
                    $keepdata = json_decode($qsInfo['keepdata'],true);
                    $detail_data['exterior'] = $keepdata['lookslike'];
                    $detail_data['exterior_explain'] = $keepdata['lookslike_desc'];
                    $detail_data['remark'] = $keepdata['total_desc'];
                    $detail_data['preset_detection'] = $keepdata;
                    $detail_data['fixed_detection'] = $keepdata;
                    $save_edit = 'keep';
                }
                $save_edit = $save_edit?$save_edit:'edit';
            //查询设备信息
            $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,brand,serialnum,model,departid,catid,opendate,lasttesttime,lasttestuser,lasttestresult', array('assid' => $qsInfo['assid'],'is_delete'=>'0'));
            $departname = array();
            $catname = array();
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";

            $asInfo['department'] = $departname[$asInfo['departid']]['department'];
            $asInfo['opendate'] = HandleEmptyNull($asInfo['opendate']);
            $asInfo['category'] = $catname[$asInfo['catid']]['category'];

            $result['tolerance'] = $tolerance;
            $result['qsInfo'] = $qsInfo;
            $result['asInfo'] = $asInfo;
            $result['setting'] = $values;
            $result['file_data'] = $file_data;
            $result['detail_data'] = $detail_data;
            $result['templatename'] = $template['name'];
            $result['status'] = 1;
            $this->ajaxReturn($result);
            $this->assign('qsInfo', $qsInfo);
            $this->assign('asInfo', $asInfo);
            $this->assign('values', $values);
            $this->assign('templatename', $template['name']);
            $this->assign('file_data', $file_data);
            $this->assign('detail_data', $detail_data);

        }
    }

    private function getPoints($nums)
    {
        $qualityModel = new QualityModel();
        $points = $qualityModel->DB_get_all('patrol_points','*',array('num'=>array('in',$nums)));
        $parentid = $data = [];
        foreach($points as $k=>$v){
            $parentid[] = $v['parentid'];
        }
        $pars = $qualityModel->DB_get_all('patrol_points','*',array('ppid'=>array('in',$parentid)));
        foreach ($pars as $k=>$v){
            $data[$k]['ppid'] = $v['ppid'];
            $data[$k]['num'] = $v['num'];
            $data[$k]['name'] = $v['name'];
            $n = 0;
            foreach ($points as $k1=>$v1){
                if($v1['parentid'] == $v['ppid']){
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
     * Notes: 质控结果查询
     */
    public function qualityResult()
    {
        $qualityModel = new QualityModel();
        $result = $qualityModel->get_quality_result();
        $this->ajaxReturn($result);
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
            $result['msg'] = '查找不到该质控计划信息！';
            $result['status'] = -1;
            $this->ajaxReturn($result);
        }
        $detailInfo['addtime'] = getHandleMinute(strtotime($detailInfo['addtime']));
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
        //检测设置值
        $tolerance = json_decode($qsInfo['tolerance'], true);
        $setting = json_decode($qsInfo['start_preset'], true);
        //检测测量值
        $detail_result = [];
        //查询质控结果
        $res = $qualityModel->DB_get_all('quality_result','detection_Ename,is_conformity',array('qsid'=>$qsid));
        foreach ($res as $k=>$v){
            $detail_result[$v['detection_Ename']] = $v['is_conformity'];
        }
        //查询铭牌照片信息
        $files = $qualityModel->DB_get_all('quality_details_file', '*', array('qsid' => $qsid, 'is_delete' => 0));
        //查询设备信息
        $asInfo = $qualityModel->DB_get_one('assets_info', 'assid,assets,assnum,brand,serialnum,model,departid,catid,opendate,lasttesttime,lasttestuser,lasttestresult', array('assid' => $qsInfo['assid']));
        $departname = $file_data = array();
        foreach ($files as $k => $v) {
            $file_data[$v['type']][] = $v;
        }
        $catname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $asInfo['opendate'] = HandleEmptyNull($asInfo['opendate']);
        $asInfo['lasttesttime'] = getHandleMinute(strtotime($asInfo['lasttesttime']));
        $asInfo['department'] = $departname[$asInfo['departid']]['department'];
        $asInfo['category'] = $catname[$asInfo['catid']]['category'];

        $result['qsInfo'] = $qsInfo;
        $result['asInfo'] = $asInfo;
        $result['detailInfo'] = $detailInfo;
        $result['tolerance'] = $tolerance;
        foreach ($setting as $key => $value) {
            $setting[$key] = array_values($value);
        }
        $result['setting'] = $setting;
        $result['detail_result'] = $detail_result;
        $result['file_data'] = $file_data;
        $result['templatename'] = $template['name'];
        $jssdk = new WxAccessTokenModel();
        $signPackage = $jssdk->GetSignPackage();
        $this->signPackage = $signPackage;
        $this->assign('date', getHandleTime(time()));
        $result['status'] = 1;
        $this->ajaxReturn($result);
    }

}
