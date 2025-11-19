<?php
namespace Admin\Controller\Metering;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\NotCheckLogin\PublicController;
use Admin\Model\MeteringModel;
use Admin\Model\UserModel;

class MeteringController extends CheckLoginController
{

    //计量计划制定管理列表
    public function getMeteringList()
    {
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'getMeteringList':
                    $MeteringModel = new MeteringModel();
                    $arr = $MeteringModel->getMeteringList();
                    $this->ajaxReturn($arr, 'JSON');
                    break;
            }
        } else {
            $type = I('GET.type');
            if ($type == 'showMetering') {
                $MeteringModel = new MeteringModel();
                $data = $MeteringModel->getMeteringData();
                $this->assign('data', $data);
                $this->display('showMetering');
            } else {
                $MeteringModel = new MeteringModel();
                $categorys = $MeteringModel->DB_get_all('metering_categorys', 'mcid,mcategory');
                $notCheck = new PublicController();
                $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                $this->assign('categorys', $categorys);
                $this->assign('getMeteringList', get_url());
                $this->display();
            }
        }
    }

    //删除计划
    public function delMetering()
    {
        $type = I('POST.type');
        if ($type == 'delBatchMetering') {
            $MeteringModel = new MeteringModel();
            $arr = $MeteringModel->delBatchMetering();
            $this->ajaxReturn($arr, 'JSON');
        } else {
            $MeteringModel = new MeteringModel();
            $arr = $MeteringModel->delMetering();
            $this->ajaxReturn($arr, 'JSON');
        }
    }

    //编辑计量计划
    public function saveMetering()
    {
        if (IS_POST) {
            $MeteringModel = new MeteringModel();
            $arr = $MeteringModel->saveMetering();
            $this->ajaxReturn($arr, 'JSON');
        } else {
            $userModel = new UserModel();
            $MeteringModel = new MeteringModel();
            $data = $MeteringModel->getMeteringData();
            $categorys = $userModel->DB_get_all('metering_categorys', 'mcid,mcategory');
            $this->assign('data', $data);
            $this->assign('categorys', $categorys);
            $this->display();
        }
    }

    //计量检定结果查询列表
    public function getMeteringResult()
    {
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'getMeteringResult':
                    $MeteringModel = new MeteringModel();
                    $arr = $MeteringModel->getMeteringResult();
                    $this->ajaxReturn($arr, 'JSON');
                    break;
            }
        } else {
            $MeteringModel = new MeteringModel();
            $categorys = $MeteringModel->DB_get_all('metering_categorys', 'mcid,mcategory');
            $notCheck = new PublicController();
            $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
            $this->assign('categorys', $categorys);
            $this->assign('getMeteringResult', get_url());
            $this->display();
        }
    }

    //新增计量计划
    public function addMetering()
    {
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'getSerialnum':
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->getSerialnum();
                    $this->ajaxReturn($result, 'JSON');
                    break;
                case 'addMeteringPlan':
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->addMeteringPlan();
                    $this->ajaxReturn($result, 'JSON');
                    break;
                case 'addCategorys':
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->addCategorys();
                    $this->ajaxReturn($result, 'JSON');
                    break;
                case 'getAssets':
                    $MeteringModel = new MeteringModel();
                    $arr = $MeteringModel->getAssetsData();
                    $this->ajaxReturn($arr, 'JSON');
                    break;
            }
        } else {
            $department = $this->getDepartname();
            $MeteringModel = new MeteringModel();
            $categorys = $MeteringModel->DB_get_all('metering_categorys', 'mcid,mcategory');
            $this->assign('department', $department);
            $this->assign('categorys', $categorys);
            $this->display();
        }
    }

    //计量检定结果录入
    public function setMeteringResult()
    {
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'upload':
                    //上传维保文件
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->uploadfile();
                    $result['adduser']=session('username');
                    $result['thisTime']=getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'setMeteringResult':
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->setMeteringResult();
                    $this->ajaxReturn($result, 'JSON');
                    break;
                case 'editMeteringResult':
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->editMeteringResult();
                    $this->ajaxReturn($result, 'JSON');
                    break;
            }
        } else {
            $type = I('GET.type');
            switch ($type) {
                case 'setMeteringResult':
                    $mrid = I('GET.mrid');
                    $MeteringModel = new MeteringModel();
                    $data = $MeteringModel->getMeteringData();
                    $resultData = $MeteringModel->DB_get_one('metering_result', 'mpid,mrid,this_date', array('mrid' => $mrid));
                    if($data['cycle']){
                        $resultData['next_date'] = date("Y-m-d", strtotime("+" . $data['cycle'] . " month", strtotime($resultData['this_date'])));
                    }
                    $this->assign('resultData', $resultData);
                    $this->assign('data', $data);
                    $this->display();
                    break;
                case 'showMeteringResult':
                    $mrid = I('GET.mrid');
                    $MeteringModel = new MeteringModel();
                    $data = $MeteringModel->getMeteringData();
                    $join='LEFT JOIN sb_user AS U ON U.userid=R.adduserid';
                    $field='R.this_date,R.report_num,R.result,R.company,R.money,R.test_person,R.auditor,R.remark,U.username AS adduser,R.adddate,R.status';
                    $resultData = $MeteringModel->DB_get_one_join('metering_result', 'R',$field,$join, array('mrid' => $mrid));
                    $resultData['result']=$resultData['result']==1?'合格':'不合格';
                    $resultData['status']=$resultData['status']==1?'已检测':'<span class="rquireCoin">待检测</span>';
                    $file=$MeteringModel->getFileList();
                    $this->assign('resultData', $resultData);
                    $this->assign('fileData', $file);
                    $this->assign('data', $data);
                    $this->display('showMeteringResult');
                    break;
                case 'editMeteringResult':
                    $mrid = I('GET.mrid');
                    $MeteringModel = new MeteringModel();
                    $data = $MeteringModel->getMeteringData();
                    $resultData = $MeteringModel->DB_get_one('metering_result', 'mpid,mrid,this_date,report_num,result,company,money,test_person,auditor,remark', array('mrid' => $mrid));
                    $resultFiles = $MeteringModel->DB_get_all('metering_result_reports', 'name,url,adddate,adduserid', array('mrid' => $mrid));
                    if ($resultFiles) {
                        foreach ($resultFiles as &$fileValue) {
                            $ui = $MeteringModel->DB_get_one('user','username',['userid'=>$fileValue['adduserid']]);
                            $fileValue['adduser'] = $ui['username'];
                            $suffix = substr(strrchr($fileValue['name'], '.'), 1);
                            $fileValue['operation'] = '<div class="layui-btn-group">';
                            $supplement = 'data-path="' . $fileValue['url'] . '" data-name="' . $fileValue['name'] . '"';
                            $fileValue['operation'] .= $this->returnListLink('删除', '', '', C('BTN_CURRENCY') . ' layui-bg-red delFile', '', $supplement);
                            if ($suffix != 'doc' && $suffix != 'docx') {
                                $fileValue['operation'] .= $this->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' layui-btn-normal showFile', '', $supplement);
                            }
                            $fileValue['operation'] .= '<input type="hidden" name="path" class="path" value="' . $fileValue['url'] .'">';
                            $fileValue['operation'] .= '</div>';
                        }
                    }

                    $this->assign('resultData', $resultData);
                    $this->assign('resultFiles', $resultFiles);
                    $this->assign('data', $data);
                    $this->display('editMeteringResult');
                    break;
            }
        }
    }

    //批量修改
    public function batchSaveMetering(){
        $MeteringModel = new MeteringModel();
        if(IS_POST){
            $type = I('POST.type');
            switch ($type){
                case 'batchEditGetData':
                    //获取初始化的批量修改列表
                    $result = $MeteringModel->batchEditGetData();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getFieldsData':
                    //获取对应下拉选择的字段表格数据
                    $result = $MeteringModel->getFieldsData();
                    $this->ajaxReturn($result);
                    break;
                case 'batchEditUpdateData':
                    //批量修改
                    $result = $MeteringModel->batchUpdateData();
                    $this->ajaxReturn($result);
                    break;
            }
        }else{
            $mpid = trim(I('GET.mpid'),',');
            if(!$mpid){
                $this->display('/Public/error');exit;
            }
            $fields = $MeteringModel->getDefaultShowFields();
            unset($fields['plan_num']);
            unset($fields['assets']);
            unset($fields['asset_count']);
            unset($fields['department']);
            unset($fields['productid']);
            $this->assign('mpid',$mpid);
            $this->assign('fields',$fields);
            $userModel=new UserModel();
            $respoUser = $userModel->getUsers('setMeteringResult', '', true);
            $categorys = $userModel->DB_get_all('metering_categorys', 'mcid,mcategory');
            $header = $MeteringModel->getEditHeader();
            $header = json_encode($header);
            $this->assign('header',$header);
            $this->assign('respoUser',$respoUser);
            $this->assign('categorys',$categorys);
            $this->display();
        }
    }

    //批量导入
    public function batchAddMetering()
    {
        if (IS_POST) {
            $type = I('POST.type');
            switch($type){
                case 'save':
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->batchAddMetering();
                    $this->ajaxReturn($result);
                    break;
                case 'getData':
                    //获取待入库设备明细信息
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->getWatingUploadMetering();
                    $this->ajaxReturn($result);
                    break;
                case 'updateData':
                    //更新临时表数据库
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->updateTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'delTmpMetering':
                    //删除临时表数据库
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->delTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'upload':
                    //接收上传文件数据
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->uploadData();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $this->ajaxReturn(array('status' => -1, 'msg' => '空操作！'));
                    break;
            }
        } else {
            $type = I('GET.type');
            if ($type == 'exploreMeteringModel') {
                $MeteringModel = new MeteringModel();
                $do=$MeteringModel->exploreMeteringModel();
                if($do==false){
                    $this->error('暂无符合添加的设备可导出至模板');
                }
            } else {
                $department = $this->getDepartname();
                $MeteringModel = new MeteringModel();
                $categorys = $MeteringModel->DB_get_all('metering_categorys', 'mcid,mcategory');
                $this->assign('department', $department);
                $this->assign('categorys', $categorys);
                $this->display();
            }
        }
    }

    public function testHtml()
    {
        $this->display();
    }

    //计量记录查询列表
    public function getMeteringHistory()
    {
        $MeteringModel = new MeteringModel();
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'getMeteringHistory':
                    $arr = $MeteringModel->getMeteringHistory();
                    $this->ajaxReturn($arr, 'JSON');
                    break;
            }
        } else {
            $type = I('get.type');
            if($type == 'showMeteringHistory'){
                $mpid = I('get.mpid');
                $MeteringModel = new MeteringModel();
                $data = $MeteringModel->getMeteringData();
                //查询记录历史
                $historyData = $MeteringModel->DB_get_all('metering_result', '*', array('mpid' => $mpid,'status'=>1));
                $this->assign('historyData', $historyData);
                $this->assign('data', $data);
                $this->display('showMeteringHistory');
            }else{
                $MeteringModel = new MeteringModel();
                $categorys = $MeteringModel->DB_get_all('metering_categorys', 'mcid,mcategory');
                $notCheck = new PublicController();
                $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                $this->assign('categorys', $categorys);
                $this->assign('getMeteringHistory', get_url());
                $this->display();
            }
        }
    }

    public function batchSetMeteringResult()
    {
        if(IS_POST){
            $type = I('POST.type');
            switch ($type){
                case 'save':
                    //保存数据
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->batchTempMeteringResult();
                    $this->ajaxReturn($result);
                    break;
                case 'getData':
                    //获取待入库设备明细信息
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->getWatingUploadResult();
                    $this->ajaxReturn($result);
                    break;
                case 'updateData':
                    //更新临时表数据库
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->updateResultTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'delResult':
                    //删除临时表数据库
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->delResult();
                    $this->ajaxReturn($result);
                    break;
                case 'uploadFiles':
                    //上传计量附件文件
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->uploadHistoryFile();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'matchFiles':
                    //匹配已上传的计量附件文件
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->matchFiles();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'upload':
                    //接收上传文件数据
                    $MeteringModel = new MeteringModel();
                    $result = $MeteringModel->uploadResultData();
                    $this->ajaxReturn($result);
                    break;
            }
        }else{
            $type = I('GET.type');
            if ($type == 'exploreMeteringResultModel') {
                $MeteringModel = new MeteringModel();
                $do=$MeteringModel->exploreMeteringResultModel();
                if($do==false){
                    $this->error('暂无符合添加的设备可导出至模板');
                }
            } else {
                $MeteringModel = new MeteringModel();
                $categorys = $MeteringModel->DB_get_all('metering_categorys', 'mcid,mcategory');
                $this->assign('categorys', $categorys);
                $this->display();
            }
        }
    }
}
