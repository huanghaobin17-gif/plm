<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/3/20
 * Time: 13:51
 */

namespace Mobile\Controller\Repair;

use Mobile\Controller\Login\IndexController;
use Mobile\Model\RepairModel;
use Mobile\Model\RepairPartsModel;

class RepairPartsController extends IndexController
{
    protected $index_url = 'Index/testindex.html';//首页地址
    /**
     * Notes: 配件出库
     */
    public function partsOutWareList()
    {
        if (IS_POST) {
            $partsModel = new RepairPartsModel();
            $result = $partsModel->get_parts_orders();
            $this->ajaxReturn($result);
        } else {
            $this->assign('url', get_url());
            $this->display();
        }
    }

    public function partsOutWare()
    {
        if (IS_POST) {
            $PartsModel = new \Admin\Model\PartsModel();
            $result = $PartsModel->partsOutWareApply();
            $this->ajaxReturn($result, 'json');
        } else {
            $repid = I('get.repid');
            //获取维修单信息
            $RepairModel = new \Admin\Model\RepairModel();
            //维修信息
            $repArr = $RepairModel->getRepairBasic($repid);

            $where['repid'] = ['EQ', $repid];
            $where['status'] = ['EQ', C('NO_STATUS')];
            $outware_record = $RepairModel->DB_get_one('parts_outware_record', '', $where);

            $PartsModel = new \Admin\Model\PartsModel();

            $apply = $PartsModel->getOutwareApply($outware_record['outwareid']);
            $outware_record['outdate'] = HandleEmptyNull($outware_record['outdate']);
            //设备信息
            $asArr = $RepairModel->getAssetsBasic($repArr['assid']);
            //获取故障问题
            $repArr['fault_problem'] = $RepairModel->getFaultProblem($repid);
            //配件/服务
            $parts = $RepairModel->getAllPartsBasic($repid);
            //查询对应配件库存
            $repairModel = new RepairModel();
            $parts = $repairModel->get_parts_stock($parts);
            if (!isset($repArr['assid'])) {
                $this->assign('tips', '参数非法，请稍联系管理员！');
                $this->assign('btn', '返回首页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            $parts_data = $repairModel->DB_get_one('repair_parts', 'partid', array('repid' => $repid, 'status' => '0'));
            if ($parts_data) {
                $this->assign('is_display', 1);
            } else {
                $this->assign('is_display', 0);
            }
            //******************************8审批流程显示 start***************************//
            $reModel = new RepairModel();
            $repArr = $reModel->get_approves_progress($repArr,'repid','repid');
            //**************************************审批流程显示 end*****************************//
            $this->assign('outware', $outware_record);
            $this->assign('repArr', $repArr);
            $this->assign('asArr', $asArr);
            $this->assign('parts', $parts);
            $this->assign('apply', $apply);
            $this->assign('partsOutWareUrl', get_url());
            $this->display();
        }
    }

    /**
     * Notes: 配件库存
     */
    public function partStockList()
    {
        if (IS_POST) {
            $partModel = new RepairPartsModel();
            $result = $partModel->get_parts_stock();
            $this->ajaxReturn($result);
        } else {
            $this->assign('url', get_url());
            $this->display();
        }
    }

    public function partsInWare()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'partsInWareApply':
                    //采购申请入库记录操作
                    $PartsModel = new \Admin\Model\PartsModel();
                    $result = $PartsModel->wx_partsInWareApply();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //新增操作
                    $PartsModel = new \Admin\Model\PartsModel();
                    $result = $PartsModel->partsInWare();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('get.action');
            if ($action == 'partsInWareApply') {
                $repid = I('get.repid');
                //查询申请单信息
                $partsModel = new RepairPartsModel();
                $inwareinfo = $partsModel->get_inwareapply_info($repid);
                if (!$inwareinfo['inwareid']) {
                    $this->assign('tips', '获取入库申请单信息失败！');
                    $this->assign('btn', '返回上一步');
                    $this->assign('url', C('MOBILE_NAME').'/'.'RepairParts/partStockList.html');
                    $this->display('Pub/Notin/fail');
                    exit;
                }
                //查询维修单信息
                $repinfo = $partsModel->DB_get_one('repair', 'response', array('repid' => $repid));
                if (!$repinfo['response']) {
                    $this->assign('tips', '获取维修单信息失败！');
                    $this->assign('btn', '返回上一步');
                    $this->assign('url', C('MOBILE_NAME').'/'.'RepairParts/partStockList.html');
                    $this->display('Pub/Notin/fail');
                    exit;
                }
                //查询供应商信息
                $sups = $partsModel->DB_get_all('offline_suppliers', 'olsid as value,sup_name as title', array('is_supplier' => 1, 'is_delete' => 0));
                $data = $partsModel->DB_get_one('repair_parts', 'part_num', array('parts' => I('get.parts'), 'part_model' => I('get.model'), 'repid' => $repid));
                $min_sum = $data['part_num'] - I('get.stock');
                $this->assign('sups', json_encode($sups));
                $this->assign('inwareid', $inwareinfo['inwareid']);
                $this->assign('leader', $repinfo['response']);
                $this->assign('supplier_id', I('get.sid'));
                $this->assign('supplier_name', I('get.sname'));
                $this->assign('parts', I('get.parts'));
                $this->assign('model', I('get.model'));
                $this->assign('price', I('get.price'));
                $this->assign('stock', I('get.stock'));
                $this->assign('date', date('Y-m-d'));
                $this->assign('repid', $repid);
                $this->assign('min_sum', $min_sum);
                $this->assign('username', session('username'));
                $this->assign('url', get_url());
                $this->display('partsInWareApply');
            }
        }
    }
}