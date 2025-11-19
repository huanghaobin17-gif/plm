<?php

namespace Admin\Controller\Patrol;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\PatrolPlanModel;
use Admin\Model\UserModel;
use Admin\Model\PatrolModel;
class PatroRecordSearchController extends CheckLoginController
{

    public function getRecordSearchList()
    {
        $planModel = new PatrolPlanModel();
        if(IS_POST){
            $result = $planModel->getRecordSearchListData();
            $this->ajaxReturn($result, 'json');
        }else{
            $action = I('get.action');
            switch($action){
                case 'showPatrolRecord':
                    $result = $planModel->showPatrolRecordData();
                    $this->assign('result', $result);
                    $this->display('showPatrolRecord');
                    break;
                case 'test':
                    $assnum=I('get.assnum');
                    $result = $planModel->getRecordData($assnum);
                    $PatrolModel = new PatrolModel();
                    $apps = $PatrolModel->get_approve_info($result['plan_data']['patrid']);
                    $this->assign('result', $result);
                    $this->assign('data', $result['data']);
                    $this->assign('apps', $apps);
                    $this->assign('plan_data', $result['plan_data']);
                    $this->assign('time',date('Y-m-d',time()));
                    $baseSetting = array();
                    include APP_PATH . "Common/cache/basesetting.cache.php";
                    $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
                    $this->display('Patrol/ReportTemplate/patrolReport');
                    break;
                default:

                    $UserModel = new UserModel();
                    //获取用户
                    $userInfo = $UserModel->getUsers('doTask', '', true, true);
                    //获取所有部门
                    $department = $UserModel->getAllDepartments(['hospital_id' => session('current_hospitalid'),'is_delete'=>C('NO_STATUS')]);
                    $this->assign('getRecordSearchList', get_url());
                    $this->assign('departmentInfo',$department);
                    $this->assign('userInfo',$userInfo);
                    $this->display();
                    break;
            }
        }
    }

    /**
     * 批量打印保养报告
     */
   /*
    批量打印报告
     */
    public function  batchPrintReport(){
                    $printStyle=I('POST.printStyle');
                    if ($printStyle=='1') {
                        $this->onebatchPrintReport();
                    }elseif($printStyle=='2'){
                        $this->allbatchPrintReport();
                    }else{
                        $this->assnumbatchPrintReport();
                    }
    }
    /*
     下载一个pdf文件
     */
    public function downpdf(){
        $assnum=I('POST.assnum');
        $cycids=I('POST.cycids');
        Vendor('mpdf.mpdf');
        //设置中文编码
        $mpdf = new \mPDF('zh-cn');
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        if ($baseSetting['repair']['repair_tmp']['value']['style'] == 2) {
            //生成水印
            $water = C('WATER_NAME');
            if ($baseSetting['repair']['repair_print_watermark']['value']['watermark']) {
                $water = $baseSetting['repair']['repair_print_watermark']['value']['watermark'];
            }
            $mpdf->SetWatermarkText($water, 0.08);
        }
        //设置字体，解决中文乱码
        $mpdf->useAdobeCJK = TRUE;
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        //设置pdf显示方式
        $mpdf->SetDisplayMode('fullpage');
        //$strContent = '我是带水印的PDF文件';
        $mpdf->showWatermarkText = true;
        $mpdf->SetHTMLHeader('');
        $mpdf->SetHTMLFooter('');
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/generates_pdf?assnum='.$assnum.'&cycids='.$cycids;
        $stylesheet = file_get_contents($url);
        $mpdf->WriteHTML($stylesheet);
        $mpdf->Output('设备号'.$assnum.'的保养报告.pdf','D');
    }
    /*
    打印一台设备的对应报告
     */
    public function assnumbatchPrintReport(){
                    $assnum=I('POST.assnums');
                    $cycids=I('POST.cycids');
                    $cycids = trim($cycids,',');
                    $cycidArr = explode(',',$cycids);
                    $html="";
                    $planModel = new PatrolPlanModel();
                    $PatrolModel = new PatrolModel();
                    foreach ($cycidArr as $key => $value) {


                    $result = $planModel->getRecordDatas($assnum,$value);
                    $apps = $PatrolModel->get_approve_info($result['plan_data']['patrid']);
                    $this->assign('result', $result);
                    $this->assign('data', $result['data']);
                    $this->assign('apps', $apps);
                    $this->assign('plan_data', $result['plan_data']);
                    $this->assign('time',date('Y-m-d',time()));
                    $marget_top = ($key + 2) % 2 == 0 ? 0 : 10;
                    $this->assign('marget_top', $marget_top);
                    if ($result['plan_data']) {
                        $html.=$this->display('Patrol/ReportTemplate/reportremp');
                    }
                    }
                    echo $html;
                    exit;
    }
    /*
    批量打印最近一次报告
     */
    public function  onebatchPrintReport(){
                    $assnum=I('POST.assnums');
                    $assnum = trim($assnum,',');
                    $assnumArr = explode(',',$assnum);
                    $html="";
                    $planModel = new PatrolPlanModel();
                    $PatrolModel = new PatrolModel();
                    foreach ($assnumArr as $key => $value) {
                        # code...
                    $result = $planModel->getRecordData($value);
                    $apps = $PatrolModel->get_approve_info($result['plan_data']['patrid']);
                    $this->assign('result', $result);
                    $this->assign('data', $result['data']);
                    $this->assign('apps', $apps);
                    $this->assign('plan_data', $result['plan_data']);
                    $this->assign('time',date('Y-m-d',time()));
                    $marget_top = ($key + 2) % 2 == 0 ? 0 : 10;
                    $this->assign('marget_top', $marget_top);
                    $html.=$this->display('Patrol/ReportTemplate/reportremp');
                    }
                    echo $html;
                    exit;
    }
    public function generate_pdf($assnum){
        Vendor('mpdf.mpdf');
        //设置中文编码
        $mpdf = new \mPDF('zh-cn');
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        if ($baseSetting['repair']['repair_tmp']['value']['style'] == 2) {
            //生成水印
            $water = C('WATER_NAME');
            if ($baseSetting['repair']['repair_print_watermark']['value']['watermark']) {
                $water = $baseSetting['repair']['repair_print_watermark']['value']['watermark'];
            }
            $mpdf->SetWatermarkText($water, 0.08);
        }
        //设置字体，解决中文乱码
        $mpdf->useAdobeCJK = TRUE;
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        //设置pdf显示方式
        $mpdf->SetDisplayMode('fullpage');
        //$strContent = '我是带水印的PDF文件';
        $mpdf->showWatermarkText = true;
        $mpdf->SetHTMLHeader('');
        $mpdf->SetHTMLFooter('');
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/generate_pdf?assnum='.$assnum;
        $stylesheet = file_get_contents($url);
        $mpdf->WriteHTML($stylesheet);
        $dir_path='./Public/uploads/patrol/zip/'.date('Ymd').session('userid');
        is_directory($dir_path);
        $mpdf->Output($dir_path.'/DC'.$assnum.'.pdf', 'f');
        return $dir_path;
    }
   /*
    批量打印所有报告
     */
    public function  allbatchPrintReport(){
                    $assnum=I('POST.assnums');
                    $assnum = trim($assnum,',');
                    $assnumArr = explode(',',$assnum);
                    $html="";
                    $planModel = new PatrolPlanModel();
                    $PatrolModel = new PatrolModel();
                    foreach ($assnumArr as $key => $value) {
                        $cycids="";
                    do{
                    $result = $planModel->getRecordData($value,$cycids);
                    $apps = $PatrolModel->get_approve_info($result['plan_data']['patrid']);
                    $this->assign('result', $result);
                    $this->assign('data', $result['data']);
                    $this->assign('apps', $apps);
                    $this->assign('plan_data', $result['plan_data']);
                    $this->assign('time',date('Y-m-d',time()));
                    $marget_top = ($key + 2) % 2 == 0 ? 0 : 10;
                    $this->assign('marget_top', $marget_top);
                    if ($result['plan_data']) {
                        $html.=$this->display('Patrol/ReportTemplate/reportremp');
                    }
                    $cycids=$cycids.$result['plan_data']['cycid'].',';
                    }while($result['report_num']!="");
                    }
                    echo $html;
                    exit;
    }
    /**
     * 导出excel
     */
    public function exportReport()
    {
        $planModel = new PatrolPlanModel();
        $assid = explode(',', trim(I('POST.assid'), ','));
        if (!$assid) {
            $this->error('参数错误！');
            exit;
        }
        //获取要导出的数据
        //读取assets、factory数据库字段
        $field = 'assets,assnum,assorignum,model,departid,serialnum,patrol_xc_cycle,patrol_pm_cycle,patrol_nums,maintain_nums,patrol_dates,maintain_dates,pre_patrol_executor,pre_patrol_result,pre_maintain_result,pre_maintain_date';
        $data = $planModel->DB_get_all('assets_info', $field, ['assid' => ['IN', $assid]], '', 'adddate desc');
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        $assnums="";
        foreach ($data as $k => $v) {
            $data[$k]['xuhao'] = $k + 1;
            $data[$k]['department'] = $departname[$v['departid']]['department'];
            $assnums=$assnums.$v['assnum'].',';
        }
        $assnums=substr($assnums, 0, -1);
        $execute_data=$planModel->DB_get_all_join('patrol_execute', 'A', 'A.report_num,A.assetnum,A.cycid,A.finish_time,B.executor,A.asset_status,C.executedate,B.complete_time,C.expect_complete_date',"LEFT JOIN sb_patrol_plan_cycle as B ON A.cycid=B.cycid LEFT JOIN sb_patrol_plan as C ON C.patrid=B.patrid", ['assetnum' => ['IN', $assnums]]);
        $exec_data=array();
        foreach ($execute_data as $k => $v) {
            $exec_data[$v['assetnum']]=$v;
        }
        foreach ($data as $key => $value) {
            $data[$key]['report_num']=$exec_data[$value['assnum']]['report_num'];
            $data[$key]['executor']=$exec_data[$value['assnum']]['executor'];
            $data[$key]['asset_status']=$exec_data[$value['assnum']]['asset_status'];
            $data[$key]['complete_time']=$exec_data[$value['assnum']]['complete_time'];
            $data[$key]['executedate']=$exec_data[$value['assnum']]['executedate'];
            if (strtotime($exec_data[$value['assnum']]['complete_time'])-strtotime($exec_data[$value['assnum']]['expect_complete_date'])>24*60*60) {
                $data[$key]['is_overdue']='逾期';
            }else{
                $data[$key]['is_overdue']='未逾期';
            }
            if (!isset($data[$key]['pre_maintain_date'])) {
                $data[$key]['next_maintain_date']=$data[$key]['executedate'];
            }else{
                $data[$key]['next_maintain_date']=date("Y-m-d",strtotime($data[$key]['pre_maintain_date'])+$data[$key]['patrol_pm_cycle']*24*60*60);
            }
            $dir_path=$this->generate_pdf($value['assnum']);
        }
        //下次保养日期=上次保养日期+周期(天)
        //格式化数据
        $showName = [
            'xuhao' => '序号',
            'report_num' => '设备保养报告编号',
            'executor' => '经办工程师',
            'department' => '使用科室',
            'assets' => '设备名称',
            'assnum' => '设备编号',
            'assorignum' => '设备原编码',
            'model' => '规格/型号',
            'serialnum' => '设备序列号',
            'patrol_pm_cycle' => '保养周期（天）',
            'next_maintain_date' => '下次保养日期',
            'executedate' => '计划保养日期',
            'complete_time' => '实际保养时间',
            'is_overdue' => '是否逾期（天）',
            'asset_status' => '保养结果',
        ];
        exportPatrolReport([date('Y', time()) . '设备预防性维护管控表'], date('Y', time()) . '设备预防性维护管控表', $showName, $data,$dir_path);
    }
}