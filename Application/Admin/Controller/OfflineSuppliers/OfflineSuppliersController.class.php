<?php

namespace Admin\Controller\OfflineSuppliers;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\CommonModel;
use Admin\Model\DictionaryModel;
use Admin\Model\OfflineSuppliersModel;
use Admin\Model\PartsModel;
use Admin\Model\UserModel;

class OfflineSuppliersController extends CheckLoginController
{
    //厂商列表
    public function offlineSuppliersList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取厂商列表数据1
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->offlineSuppliersList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showSuppliersDetails':
                    //显示厂商详情页
                    $this->showSuppliersDetails();
                    break;
                default:
                    //显示厂商列表页
                    $this->showOfflineSuppliersList();
                    break;
            }
        }
    }

    //厂商详情页
    private function showSuppliersDetails()
    {
        $olsid = I('GET.olsid');
        if ($olsid) {
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $suppliers = $OfflineSuppliersModel->getSuppliersBasic($olsid);
            $file = $OfflineSuppliersModel->getSuppliersFileList($olsid);
            $this->assign('suppliers', $suppliers);
            $this->assign('fileData', $file);
            $auth_file = $OfflineSuppliersModel->getAuthSuppliersFileList($olsid);
            $this->assign('auth_file', $auth_file);
            $this->display('showSuppliersDetails');
        } else {
            $this->error('非法操作');
        }
    }

    //厂商列表页
    private function showOfflineSuppliersList()
    {
        $this->assign('offlineSuppliersListUrl', get_url());
        $this->display();
    }

    //新增厂商
    public function addOfflineSupplier()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'getCity':
                    //获取城市
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getCity();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getAreas':
                    //获取区/城镇
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getAreas();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'upload':
                    //文件上传
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->uploadfile();
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //新增操作
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->addOfflineSupplier();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            //如果从其他页面进入该页面
            $otherPage = I('get.otherPage');
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $provinces = $OfflineSuppliersModel->getProvinces();
            $sup_num = $OfflineSuppliersModel->getSupNum();
            $this->assign('otherPage', $otherPage);
            $this->assign('provinces', $provinces);
            $this->assign('sup_num', $sup_num);
            $this->assign('addOfflineSupplierUrl', get_url());
            $this->display();
        }
    }

    //维护厂商信息
    public function editOfflineSupplier()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'getCity':
                    //获取城市
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getCity();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getAreas':
                    //获取区/城镇
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getAreas();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'upload':
                    //文件上传
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->uploadfile();
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'savetype':
                    //批量修改厂商类型
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->savetype();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'delete_auth':
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $fileid = I('post.fileid');
                    $OfflineSuppliersModel->deleteData('offline_suppliers_file',['fileid'=>$fileid]);
                    $this->ajaxReturn(['status'=>1], 'json');
                    break;
                default:
                    //修改操作
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->editOfflineSupplier();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $olsid = I('GET.olsid');
            if ($olsid) {
                $OfflineSuppliersModel = new OfflineSuppliersModel();
                $suppliers = $OfflineSuppliersModel->getSuppliersBasic($olsid);
                $provinces = $OfflineSuppliersModel->getProvinces();
                if ($suppliers['provinces']) {
                    $city = $OfflineSuppliersModel->getCity($suppliers['provinces']);
                    $this->assign('city', $city['result']);
                }
                if ($suppliers['city']) {
                    $area = $OfflineSuppliersModel->getAreas($suppliers['city']);
                    $this->assign('area', $area['result']);
                }
                $file = $OfflineSuppliersModel->getSuppliersFileList($olsid);

                $auth_file = $OfflineSuppliersModel->getAuthSuppliersFileList($olsid);
                $this->assign('suppliers', $suppliers);
                $this->assign('provinces', $provinces);
                $this->assign('fileData', $file);
                $this->assign('auth_file', $auth_file);
                $this->assign('editOfflineSupplierUrl', get_url());
                //如果是从其他页面进来
                $otherPage = I('get.otherPage');
                if ($otherPage) {
                    //组织数据
                    $typeArr = explode('、', $suppliers['suppliers_type']);
                    $typeValueArr = [];
                    foreach ($typeArr as $k => $v) {
                        switch ($v) {
                            case '供应商':
                                $typeValueArr[$k] = 1;
                                break;
                            case '生产商':
                                $typeValueArr[$k] = 2;
                                break;
                            case '维修商':
                                $typeValueArr[$k] = 3;
                                break;
                        }
                    }
                    $typeValue = implode(',', $typeValueArr);
                    $this->assign('typeValue', $typeValue);
                    $this->assign('otherPage', $otherPage);
                }

                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    /**
     * Notes: 删除厂商
     */
    public function delSupplier()
    {
        $olsid = I('post.olsid');
        if($olsid){
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $res = $OfflineSuppliersModel->updateData('offline_suppliers',array('is_delete'=>C('YES_STATUS'),'edituser'=>session('username'),'editdate'=>time()),array('olsid'=>$olsid));
            if($res){
                $this->ajaxReturn(array('status'=>1,'msg'=>'删除厂商成功！'));
            }else{
                $this->ajaxReturn(array('status'=>-1,'msg'=>'删除厂商失败！'));
            }
        }else{
            $this->ajaxReturn(array('status'=>-1,'msg'=>'无效参数！'));
        }
    }

    //合同管理
    public function olsContract()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取合同列表数据
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->olsContract();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showOLSContractDetails':
                    //显示合同详情页
                    $this->showOLSContractDetails();
                    break;
                default:
                    //显示合同列表页
                    $this->showOLSContractList();
                    break;
            }
        }
    }

    //合同列表页面
    private function showOLSContractList()
    {
        $this->assign('olsContractUrl', get_url());
        $this->display();
    }

    //合同详情页面
    public function showOLSContractDetails()
    {
        $contract_id = I('GET.contract_id');
        $contract_type = I('GET.contract_type');
        if ($contract_id && $contract_type) {
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $contract = $OfflineSuppliersModel->getContractBasic($contract_type, $contract_id);
            $contract_pay = $OfflineSuppliersModel->getContractPayBasic($contract_type, $contract_id);
            $file = $OfflineSuppliersModel->getContractFileList($contract_type, $contract_id);
            switch ($contract_type) {
                case C('CONTRACT_TYPE_SUPPLIER'):
                    //采购类型 获取采购明细
                    $apply_assets = $OfflineSuppliersModel->getContractPayAssetsBasic($contract['record_id']);
                    $this->assign('apply_assets', $apply_assets);
                    break;
                case C('CONTRACT_TYPE_REPAIR'):
                    //维修类型
                    break;
                case C('CONTRACT_TYPE_INSURANCE'):
                    //维保类型 todo
                    break;
                case C('CONTRACT_TYPE_RECORD_ASSETS'):
                    //补录合同
                    if (!$contract['assid']) {
                        $this->error('数据异常');
                    }
                    $assets_record = $OfflineSuppliersModel->getAssetsRecordList($contract['assid']);
                    $this->assign('assets_record', $assets_record);
                    break;
                case C('CONTRACT_TYPE_PARTS'):
                    $PartsModel = new PartsModel();
                    $inware_detail = $PartsModel->getInwareRecordDetail($contract['inwareid']);
                    $this->assign('inware_detail', $inware_detail);
                    //配件合同
                    break;
            }
            $this->assign('contract', $contract);
            $this->assign('contract_pay', $contract_pay);
            $this->assign('fileData', $file);
            $this->display('showOLSContractDetails');
        } else {
            $this->error('非法操作');
        }
    }

    //新增合同
    public function addOLSContract()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'upload':
                    //文件上传
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx');
                    $result = $OfflineSuppliersModel->uploadfile('OLSContract', $style);
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getSuppliers':
                    //获取对应的乙方单位
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getSuppliers();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getSalesman':
                    //获取乙方联系人信息
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getSalesman();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getCity':
                    //获取城市
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getCity();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getAreas':
                    //获取区/城镇
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getAreas();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addSuppliers':
                    //补充厂家
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->addOfflineSupplier();
                    if ($result['status'] == C('SUCCESS_STATUS')) {
                        $result['result']['sup_num'] = $OfflineSuppliersModel->getSupNum();
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addDic':
                    //补充字典
                    $dicModel = new DictionaryModel();
                    $result = $dicModel->addDic();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'joinedRepairList':
                    //已纳入的维修明细列表 todo 暂定第三方类型的维修单就可以补入
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->joinedRepairList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'canJoinRepairList':
                    //可纳入的维修明细列表 todo 暂定第三方类型的维修单就可以补入
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->canJoinRepairList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'joinedRecordAssetsList':
                    //已纳入的采购设备(补录)明细列表 todo 暂定已入库的设备可以录入(已外调，报废不需要)
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->joinedRecordAssetsList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'canJoinRecordAssetsList':
                    //可纳入的采购设备明细(补录)列表  todo 暂定已入库的设备可以录入(已外调，报废不需要)
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->canJoinRecordAssetsList();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //新增操作
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->addOLSContract();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $supplierResult = $OfflineSuppliersModel->getSuppliers(C('CONTRACT_TYPE_SUPPLIER'));
            $supplierList = $supplierResult['result'];
            $dic_assets = $OfflineSuppliersModel->DB_get_all('dic_assets', 'assets', array('status' => C('OPEN_STATUS')), 'assets');
            $userWhere['status'] = ['EQ', C('OPEN_STATUS')];
            $userWhere['is_delete'] = ['EQ', C('NO_STATUS')];
            $userWhere['job_hospitalid'] = ['IN', session('current_hospitalid')];
            $userWhere['is_super'] = ['NEQ', C('YES_STATUS')];
            $user = $OfflineSuppliersModel->DB_get_all('user', 'userid,username', $userWhere);
            $provinces = $OfflineSuppliersModel->getProvinces();
            $sup_num = $OfflineSuppliersModel->getSupNum();
            $this->assign('sup_num', $sup_num);
            $this->assign('provinces', $provinces);
            $this->assign('current_hosid', session('current_hospitalid'));
            $this->assign('addOLSContractUrl', get_url());
            $this->assign('supplierList', $supplierList);
            $this->assign('dic_assets', $dic_assets);
            $this->assign('user', $user);
            $this->display();
        }
    }

    //编辑合同
    public function editOLSContract()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'upload':
                    //文件上传
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx');
                    $result = $OfflineSuppliersModel->uploadfile('OLSContract', $style);
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getSuppliers':
                    //获取对应的乙方单位
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getSuppliers();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getSalesman':
                    //获取乙方联系人信息
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getSalesman();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getCity':
                    //获取城市
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getCity();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getAreas':
                    //获取区/城镇
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->getAreas();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addSuppliers':
                    //补充厂家
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->addOfflineSupplier();
                    if ($result['status'] == C('SUCCESS_STATUS')) {
                        $result['result']['sup_num'] = $OfflineSuppliersModel->getSupNum();
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addDic':
                    //补充字典
                    $dicModel = new DictionaryModel();
                    $result = $dicModel->addDic();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //新增操作
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->editOLSContract();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $this->display();
        }
    }

    //确认并录入合同信息
    public function confirmOLSContract()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'upload':
                    //文件上传
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx');
                    $result = $OfflineSuppliersModel->uploadfile('OLSContract', $style);
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->confirmOLSContract();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $contract_id = I('GET.contract_id');
            $contract_type = I('GET.type');
            if ($contract_id > 0 && $contract_type > 0) {
                $OfflineSuppliersModel = new OfflineSuppliersModel();
                $contract = $OfflineSuppliersModel->getContractBasic($contract_type, $contract_id);
                switch ($contract_type) {
                    case C('CONTRACT_TYPE_REPAIR'):
                        //todo 未确定需要什么类型
                        break;
                    case C('CONTRACT_TYPE_PARTS'):
                        //配件合同
                        $PartsModel = new PartsModel();
                        $inware_detail = $PartsModel->getInwareRecordDetail($contract['inwareid']);
                        $this->assign('inware_detail', $inware_detail);
                        break;
                    default :
                        $this->error('非法类型');
                        break;
                }
                $this->assign('confirmOLSContractUrl', get_url());
                $this->assign('contract', $contract);
                $this->display();

            } else {
                $this->error('非法操作');
            }
        }

    }


    //合同付款列表
    public function payOLSContractList()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    //获取借入验收列表数据
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result = $OfflineSuppliersModel->payOLSContractList();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $this->assign('payOLSContractListUrl',get_url());
            $this->display();
        }
    }

    //合同付款操作
    public function payOLSContract()
    {
        if (IS_POST) {
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $result = $OfflineSuppliersModel->payOLSContract();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->error('非法操作');
        }
    }


    //发票管理
    public function OLSInvoice()
    {
        $this->display();
    }

}