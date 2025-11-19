<?php
/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/8/1
 * Time: 16:58
 */

namespace Admin\Controller\Purchases;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\OfflineSuppliersModel;
use Admin\Model\PurchasesContractModel;

/**
 * 采购合同管理
 * Class PurchaseContractController
 * @package Admin\Controller\Purchases
 */
class ContractController extends CheckLoginController
{
    private $MODULE = 'Purchases';

    //合同列表
    public function contractList()
    {
        if (IS_POST) {
            //获取合同列表数据
            $PurchasesContractModel = new PurchasesContractModel();
            $result = $PurchasesContractModel->contractList();
            $this->ajaxReturn($result, 'json');
        } else {
            $this->assign('contractListUrl', get_url());
            $this->display();
        }
    }

    //生成合同
    public function addContract()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'upload':
                    //文件上传
                    $PurchasesContractModel = new PurchasesContractModel();
                    $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx');
                    $result = $PurchasesContractModel->uploadfile('addContract', $style);
                    $result['adduser'] = session('username');
                    $result['thisTime'] = getHandleTime(time());
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //新增操作
                    $PurchasesContractModel = new PurchasesContractModel();
                    $result = $PurchasesContractModel->addContract();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $record_id = I('GET.record_id');
            if ($record_id) {
                $recordArr = explode(',', $record_id);
                $PurchasesContractModel = new PurchasesContractModel();
                //获取招标信息供应商信息
                $record = $PurchasesContractModel->getRecordBasic($recordArr[0]);
                $userWhere['status'] = ['EQ', C('OPEN_STATUS')];
                $userWhere['is_delete'] = ['EQ', C('NO_STATUS')];
                $userWhere['job_hospitalid'] = ['EQ', $record['hospital_id']];
                $userWhere['is_super'] = ['NEQ', C('YES_STATUS')];
                //院方联系人
                $user = $PurchasesContractModel->DB_get_all('user', 'userid,username', $userWhere);
                //采购设备明细
                $assets = $PurchasesContractModel->getRecordAssetsBasic($recordArr);
                $contract_amount = 0;
                foreach ($assets as &$assetV) {
                    $contract_amount += $assetV['sum'];
                }
                $htbh = 'CG'.date('Ymd').$record['olsid'].date('s').rand(1000, 9999);
                $dabh = 'DN'.date('Ymd').$record['olsid'].rand(100000, 999999);
                //生成默认的合同编号和档案编号
                $this->assign('htbh', $htbh);
                $this->assign('dabh', $dabh);
                $this->assign('assets', $assets);
                $this->assign('contract_amount', $contract_amount);
                $this->assign('user', $user);
                $this->assign('add_user', session('username'));
                $this->assign('record', $record);
                $this->assign('addContractUrl', get_url());
                $this->assign('record_id', $record_id);
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }



    /**
     * Notes: 查看合同详情
     */
    public function showContract()
    {
        $this->display();
    }

    /**
     * Notes: 付款管理
     */
    public function purchasePaymentList()
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
            $this->display('OfflineSuppliers/OfflineSuppliers/payOLSContractList');
        }
    }

}