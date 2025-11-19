<?php

namespace Vue\Model;

use Admin\Controller\Tool\ToolController;
use Think\Model;
use Think\Model\RelationModel;

class RepairPartsModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'repair_parts';
    protected $MODULE = 'Repair';
    protected $Controller = 'RepairParts';

    public function get_parts_orders()
    {
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('get.order') ? I('get.order') : 'DESC';
        $sort = I('get.sort');
        $search = I('get.search');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
            $result['total'] = 0;
            return $result;
        }
        //查询配件表
        $rep_parts = $this->DB_get_all('repair_parts', 'repid,sum(part_num) as parts_num', array('status' => 0), 'repid');
        if (!$rep_parts) {
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
            $result['total'] = 0;
            return $result;
        }
        $numsinfo = $repidsarr = [];
        foreach ($rep_parts as $k => $v) {
            $repidsarr[] = $v['repid'];
            $numsinfo[$v['repid']] = $v['parts_num'];
        }
        $where['A.repid'] = array('in', $repidsarr);//待验收、转单确认
        $where['B.hospital_id'] = $hospital_id;
        $where['B.departid'] = array('IN', $departids);
        switch ($sort) {
            case 'part_num':
                $sort = 'A.part_num';
                break;
            case 'overhauldate':
                $sort = 'A.overhauldate';
                break;
            default:
                $sort = 'A.overhauldate';
                break;
        }
        if ($search) {
            $map['B.assets'] = array('like', '%' . $search . '%');
            $map['B.assnum'] = array('like', '%' . $search . '%');
            $map['B.model'] = array('like', '%' . $search . '%');
            $map['B.brand'] = array('like', '%' . $search . '%');
            $map['C.department'] = array('like', '%' . $search . '%');
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $where['B.is_delete'] = '0';
        $where['A.status'] = '3';
        $join[0] = ' LEFT JOIN __ASSETS_INFO__ AS B ON A.assid = B.assid';
        $join[1] = ' LEFT JOIN __DEPARTMENT__ AS C ON B.departid = C.departid';
        $total = $this->DB_get_count_join('repair', 'A', $join, $where);
        $fields = 'A.repid,A.assid,A.applicant as appUser,A.applicant_time as appTime,A.repnum,A.status,A.response,A.overhauldate,B.assets,B.assnum,C.department';
        $asArr = $this->DB_get_all_join('repair', 'A', $fields, $join, $where, '', $sort . ' ' . $order, $offset . "," . $limit);
        if (!$asArr) {
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
            $result['total'] = 0;
            return $result;
        }
        $menuData = get_menu($this->MODULE, $this->Controller, 'partsOutWare');
        //当前用户可验收科室
        foreach ($asArr as $k => $v) {
            //查询用户头像
            $uinfo = $this->DB_get_one('user', 'pic', array('username' => $v['response']));
            $asArr[$k]['parts_num'] = $numsinfo[$v['repid']];
            $asArr[$k]['parts_num_html'] = '配件数量 ' . $numsinfo[$v['repid']];
            if ($uinfo['pic'] == "" || $uinfo['pic'] == null) {
                $asArr[$k]['headimgurl'] = C('APP_NAME') . "/Public/mobile/images/user_logo.png";
            } else {
                $asArr[$k]['headimgurl'] = $uinfo['pic'];
            }
            $asArr[$k]['show_time'] = date('Y-m-d H:i', $v['overhauldate']);
            if ($menuData) {
                $asArr[$k]['Authority'] = 1;
            } else {
                $asArr[$k]['Authority'] = 0;
            }
        }
        if ($sort == 'parts_num') {
            //配件数量排序
            if ($order == 'DESC') {
                array_multisort(array_column($asArr, 'parts_num'), SORT_DESC, $asArr);
            } else {
                array_multisort(array_column($asArr, 'parts_num'), SORT_ASC, $asArr);
            }
        }
        $result['page'] = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result['rows'] = $asArr;
        $result['status'] = 1;
        return $result;
    }

    /**
     * Notes: 获取配件库存
     */
    public function get_parts_stock()
    {
        $hospital_id = session('current_hospitalid');
        $search = I('get.search');
        $where['hospital_id'] = $hospital_id;
        $where['is_use'] = ['EQ', C('NO_STATUS')];
        $where['status'] = ['EQ', C('NO_STATUS')];
        if ($search) {
            $map['parts'] = array('like', '%' . $search . '%');
            $map['parts_model'] = array('like', '%' . $search . '%');
            $map['supplier_name'] = array('like', '%' . $search . '%');
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }

        $group = 'parts,parts_model,repid,leader';
        $fields = 'parts,repid,parts_model,price,unit,COUNT(*) AS stock_nums,leader,supplier_name,supplier_id,brand,is_use,status';
        $data = $this->DB_get_all('parts_inware_record_detail', $fields, $where, $group);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
            $result['total'] = 0;
            return $result;
        }
        //查询维修配件清单
        $joinwhere['A.status'] = C('NO_STATUS');
        $joinwhere['C.hospital_id'] = $hospital_id;
        $joinwhere['C.is_delete'] = '0';
        $parts_str = $model_str = "";
        $unit_arr = array();
        $price_arr = array();
        $supplier_name_arr = array();
        $supplier_id_arr = array();
        foreach ($data as $key => $value) {
            $parts_str .= $value['parts'] . ',';
            $model_str .= $value['parts_model'] . ',';
            $unit_arr[$value['parts']][$value['parts_model']] = $value['unit'];
            $price_arr[$value['parts']][$value['parts_model']] = $value['supplier_id'];
            $supplier_id_arr[$value['parts']][$value['parts_model']] = $value['price'];
            $supplier_name_arr[$value['parts']][$value['parts_model']] = $value['supplier_name'];
        }
        $parts_str = rtrim($parts_str, ',');
        $model_str = rtrim($model_str, ',');
        if ($search) {
            $joinwhere['A.parts'] = array('IN', $parts_str);
            $joinwhere['A.part_model'] = array('IN', $model_str);
        }
        $joinwhere['B.status'] = array('IN', '3,7');
        $fields = "A.parts,A.part_model,A.repid,A.part_num,A.adduser,B.repnum,B.overhauldate,B.status";
        $join[0] = "LEFT JOIN sb_repair AS B ON A.repid = B.repid";
        $join[1] = "LEFT JOIN sb_assets_info AS C ON B.assid = C.assid";
        $partsOutWare = get_menu('Repair', 'RepairParts', 'partsOutWare');
        $repairparts = $this->DB_get_all_join('repair_parts', 'A', $fields, $join, $joinwhere, '', '', '');
        $parts = array();
        foreach ($data as $k => $v) {
            $data[$k]['cus_sort'] = 0;
            $data[$k]['repnum'] = '';
            $data[$k]['need_nums'] = '';
            $data[$k]['type'] = 3;
            $data[$k]['content_name'] = 'content';
        }
        foreach ($repairparts as $k => $v) {
            foreach ($data as $kk => $vv) {
                if ($v['parts'] == $vv['parts'] && $v['part_model'] == $vv['parts_model'] && $v['adduser'] == $vv['leader'] && $v['repid'] == $vv['repid']) {
                    //配件名称、型号、领用人匹配
                    if ($vv['stock_nums'] < $v['part_num']) {
                        $rurl = C('VUE_NAME') . '/RepairParts/partsInWare.html?action=partsInWareApply&repid=' . $vv['repid'] . '&sname=' . $vv['supplier_name'] . '&sid=' . $vv['supplier_id'] . '&stock=' . $vv['stock_nums'] . '&parts=' . $vv['parts'] . '&model=' . $vv['parts_model'] . '&price=' . $vv['price'];
                        $data[$kk]['cus_sort'] = 2;
                        $data[$kk]['repnum'] = '（' . $v['repnum'] . '）';
                        $data[$kk]['need_nums'] = '（需求量：' . $v['part_num'] . $vv["unit"] . '）';
                        $data[$kk]['type'] = 1;
                        $data[$kk]['content_name'] = 'content_red';
                    } else {
                        $rurl = C('VUE_NAME') . '/RepairParts/partsInWare.html?action=partsInWareApply&repid=' . $v['repid'];
                        $data[$kk]['cus_sort'] = 1;
                        $data[$kk]['repnum'] = '（' . $v['repnum'] . '）';
                        $data[$kk]['need_nums'] = '（需求量：' . $v['part_num'] . $vv["unit"] . '）';
                        $data[$kk]['type'] = 2;
                        $data[$kk]['content_name'] = 'content_blue';
                    }
                    unset($repairparts[$k]);
                }
            }
        }
        foreach ($repairparts as $k => $v) {
            $rurl = C('VUE_NAME') . '/RepairParts/partsInWare.html?action=partsInWareApply&repid=' . $v['repid'] . '&sname=' . $supplier_name_arr[$v['parts']][$v['part_model']] . '&sid=' . $supplier_id_arr[$v['parts']][$v['part_model']] . '&stock=0&parts=' . $v['parts'] . '&model=' . $v['part_model'] . '&price=' . $price_arr[$v['parts']][$v['part_model']];
            $v['cus_sort'] = 2;
            $v['repnum'] = '（' . $v['repnum'] . '）';
            $v['parts_model'] = $v['part_model'];
            $v['price'] = '/';
            $v['supplier_name'] = '/';
            $v['need_nums'] = $unit_arr[$v['parts']][$v['part_model']] . '（需求量：' . $v['part_num'] . $unit_arr[$v['parts']][$v['part_model']] . '）';
            $v['leader'] = $v['adduser'];
            $v['stock_nums'] = 0;
            $v['unit'] = '';
            $v['type'] = 1;
            $v['content_name'] = 'content_red';
            array_unshift($data, $v);
        }
        $order = I('get.order') ? I('get.order') : 'DESC';
        $sort = I('get.sort') ? I('get.sort') : 'leader';
        switch ($sort) {
            case 'price':
                //配件单价排序
                if ($order == 'DESC') {
                    array_multisort(array_column($data, 'price'), SORT_DESC, $data);
                } else {
                    array_multisort(array_column($data, 'price'), SORT_ASC, $data);
                }
                break;
            case 'parts':
                //配件名称排序
                if ($order == 'DESC') {
                    array_multisort(array_column($data, 'parts'), SORT_DESC, $data);
                } else {
                    array_multisort(array_column($data, 'parts'), SORT_ASC, $data);
                }
                break;
            case 'stock_nums':
                //配件库存排序
                if ($order == 'DESC') {
                    array_multisort(array_column($data, 'stock_nums'), SORT_DESC, $data);
                } else {
                    array_multisort(array_column($data, 'stock_nums'), SORT_ASC, $data);
                }
                break;
            default:
                array_multisort(array_column($data, 'cus_sort'), SORT_DESC, $data);
        }
        $total = count($data);
        //分页代码
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $page = $page ? $page : 1;
        $offset = ($page - 1) * $limit;
        $end = $limit * $page - 1;
        foreach ($data as $k => $v) {
            if ($k >= $offset && $k <= $end) {
                continue;
            } else {
                unset($data[$k]);
            }
        }
        $res = array();
        foreach ($data as $k => $v) {
            $res[] = $v;
        }
        $result['page'] = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result["status"] = 1;
        $result['rows'] = $res;
        return $result;
    }

    /**
     * Notes: 查询入库申请单信息
     * @param $repid int 维修单ID
     */
    public function get_inwareapply_info($repid)
    {
        return $this->DB_get_one('parts_inware_record', '*', array('repid' => $repid));
    }
}
