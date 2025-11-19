<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/3/20
 * Time: 13:51
 */

namespace App\Controller\Repair;

use App\Controller\Login\IndexController;
use App\Model\RepairModel;
use App\Model\RepairPartsModel;

class RepairPartsController extends IndexController
{
    protected $index_url = 'Index/testindex.html';//首页地址
    /**
     * Notes: 配件出库
     */
    public function partsOutWareList()
    {
            $partsModel = new RepairPartsModel();
            $result = $partsModel->get_parts_orders();
            $this->ajaxReturn($result);
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
            $repArr['pic_url'] = json_decode($repArr['pic_url']);

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
                $result['status'] = -1;
                $result['msg'] = '参数错误';
                $this->ajaxReturn($result, 'json');
                exit;
            }
            $parts_data = $repairModel->DB_get_one('repair_parts', 'partid', array('repid' => $repid, 'status' => '0'));
            $is_display = 0;
            if ($parts_data) {
                $is_display = 1;
            } else {
                $is_display = 0;
            }
            //******************************8审批流程显示 start***************************//
            $reModel = new RepairModel();
            $repArr = $reModel->get_approves_progress($repArr,'repid','repid');
            //**************************************审批流程显示 end*****************************//
            $result['apply'] = $apply;
            $result['repArr'] = array_merge($asArr,$repArr);
            $result['parts'] = $parts;
            $can_submit = 1;
            foreach ($parts as $key => $value) {
                if ($value['stock_not_enough']==1) {
                    $can_submit = 0;
                }
            }
            $result['is_display'] = $is_display;
            $result['can_submit'] = $can_submit;
            $result['outware'] = $outware_record;
            $result['status'] = 1;
            $this->ajaxReturn($result, 'json');
        }
    }

    /**
     * Notes: 配件库存
     */
    public function partStockList()
    {
            $partModel = new RepairPartsModel();
            $result = $partModel->get_parts_stock();
            $this->ajaxReturn($result);
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
            
                $repid = I('get.repid');
                //查询申请单信息
                $partsModel = new RepairPartsModel();
                $inwareinfo = $partsModel->get_inwareapply_info($repid);
                if (!$inwareinfo['inwareid']) {
                    $result['status'] = -1;
                    $result['msg'] = '获取入库申请单信息失败！';
                    $this->ajaxReturn($result, 'json');
                    exit;
                }
                //查询维修单信息
                $repinfo = $partsModel->DB_get_one('repair', 'response', array('repid' => $repid));
                if (!$repinfo['response']) {
                    $result['status'] = -1;
                    $result['msg'] = '获取维修单信息失败！';
                    $this->ajaxReturn($result, 'json');
                    exit;
                }
                //查询供应商信息
                $sups = $partsModel->DB_get_all('offline_suppliers', 'olsid as value,sup_name as title', array('is_supplier' => 1, 'is_delete' => 0));
                $data = $partsModel->DB_get_one('repair_parts', 'part_num', array('parts' => I('get.parts'), 'part_model' => I('get.model'), 'repid' => $repid));
                $min_sum = $data['part_num'] - I('get.stock');
                $result['status'] = 1;
                $result['leader'] = $repinfo['response'];
                $result['addtime'] = date('Y-m-d');
                $result['sups'] = $sups;
                $result['min_sum'] = $min_sum;
                $result['inwareid'] = $inwareinfo['inwareid'];
                $result['supsColumns'] = array_column($sups,'title');
                $this->ajaxReturn($result, 'json');
            
        }
    }
}