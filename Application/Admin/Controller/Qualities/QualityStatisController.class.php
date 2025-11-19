<?php

namespace Admin\Controller\Qualities;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\QualityModel;
use Admin\Model\UserModel;

class QualityStatisController extends CheckLoginController
{
    private $MODULE = 'Qualities';


    //科室质控报表
    public function qualityDepartStatistics(){
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取列表数据
                    $QualityModel = new QualityModel();
                    $result = $QualityModel->qualityDepartStatistics();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
//            $action = I('GET.action');
            $this->showQualityDepartStatistics();
        }
    }


    private function showQualityDepartStatistics()
    {
        $QualityModel = new QualityModel();
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $departments = $this->getSelectDepartments();
        //获取模板
        $templates= $QualityModel->DB_get_all('quality_templates', '*', array('1'));
        $this->assign('thisdate',date('Y',time()));
        $this->assign('department', $departments);

        $departidResult=$QualityModel->getShowDataDepart();
        $thisdepartid=[];
        foreach ($departidResult as &$one){
            $thisdepartid[$one]=true;
        }

        $this->assign('thisdepartid',$thisdepartid);
        $thisdepartids=implode(",", $departidResult);
        $this->assign('thisdepartids',$thisdepartids);
        $this->assign('templates',$templates);
        $this->assign('thistemplates',$templates[0]['qtemid']);
        $this->assign('thistemplatesName',$templates[0]['name']);
        $this->assign('qualityDepartStatisticsUrl', get_url());
        $this->display();
    }


    //导出设备故障excel表格
    public function exportDepartStatistics()
    {
        if ($_POST) {
            $type=I('POST.type');
            switch ($type){
                case 'faiTypeTerm':
                    //导出 模板明细不符合项 excel
                    $QualityModel = new QualityModel();
                    $result = $QualityModel->exportFaiTypeTerm();
                    $this->ajaxReturn($result);
                    break;
                case 'exportQualityDepart':
                    //导出 质控结果统计 excel
                    $QualityModel = new QualityModel();
                    $result = $QualityModel->exportQualityDepart();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $this->ajaxReturn(array('status'=>'-1','mag'=>'非法操作'));
                    break;
            }
        }
    }
}