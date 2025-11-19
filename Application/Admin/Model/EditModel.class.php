<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/5/10
 * Time: 16:49
 */

namespace Admin\Model;


class EditModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'edit';
    private $MODULE = 'BaseSetting';

    /**
     * Notes: 获取待审查批准数据
     */
    public function get_exam_app()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'asc';
        $sort = I('post.sort') ? I('post.sort') : 'userid';
        $type = I('post.type');
        $app = I('post.app');
        $back = I('post.back');
        $exam_time = I('post.exam_time');
        $where['hospital_id'] = session('current_hospitalid');
        if ($type) {
            $where['operation_type'] = $type;
        }
        if ($app != '') {
            $where['is_approval'] = $app;
        }
        if ($back) {
            $where['is_back'] = $back;
        }
        if ($exam_time) {
            $first_data = getCurMonthFirstDay($exam_time);
            $last_data = getCurMonthLastDay($exam_time);
            $where['approval_time'] = array(array('EGT', $first_data . ' 00:00:01'), array('ELT', $last_data . ' 23:59:59'), 'and');
        }
        $passno = get_menu($this->MODULE, 'ExamApp', 'passno');
        $total = $this->DB_get_count('edit', $where);
        $data = $this->DB_get_all('edit', '', $where, '', $sort . ' ' . $order, $offset . ',' . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $assets_info_where = [];
        foreach ($data as $k => $v) {
            $data[$k]['type'] = $v['operation_type'] == 'edit' ? '<span style="color:#009688;">申请修改</span>' : '<span style="color:red;">申请删除</span>';
            $data[$k]['back_status'] = $v['is_back'] == '0' ? '未处理' : '已回退';
            $data[$k]['approval_status'] = $v['is_approval'] == '0' ? '未处理' : ($v['is_approval'] == '1' ? '<span style="color:#009688;">已核准</span>' : '<span style="color:red;">已驳回</span>');
            $html = '<div class="layui-btn-group">';
            if ($v['is_approval'] == '0') {
                if ($passno) {
                    $html .= $this->returnListLink('同意', $passno['actionurl'], 'pass', C('BTN_CURRENCY') . ' ');
                    $html .= $this->returnListLink('驳回', $passno['actionurl'], 'not_pass', C('BTN_CURRENCY') . ' layui-btn-danger');
                }
            } elseif ($v['is_approval'] == '1') {
                if ($passno) {
                    $html .= $this->returnListLink('已核准', $passno['actionurl'], 'back_data', C('BTN_CURRENCY') . ' layui-btn-disabled');
                }
            } else {
                $html .= $this->returnListLink('已驳回', $passno['actionurl'], '', C('BTN_CURRENCY') . ' layui-btn-disabled');
            }
            $html .= '</div>';
            $data[$k]['operation'] = $html;
            $assets_info_where[$k] = json_decode($data[$k]['update_where'], true)['assid'];
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assets_info_data = $this->DB_get_all('assets_info', 'assid,model,departid', array('assid' => array('in', $assets_info_where)));
        foreach ($assets_info_data as $key => $value) {
            foreach ($data as $data_key => $data_value) {
                if ($value['assid'] == json_decode($data_value['update_where'], true)['assid']) {
                    $data[$data_key]['model'] = $value['model'];
                    $data[$data_key]['department'] = $departname[$value['departid']]['department'];
                }
            }
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $data;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 同意或驳回申请
     */
    public function do_approval()
    {
        $id = I('post.id');
        $type = I('post.event');
        $data = $this->DB_get_one('edit', '*', array('id' => $id));
        if (!$data) {
            return array('status' => -1, 'msg' => '查找不到该申请单信息！');
        }
        $old_data = json_decode($data['old_data'], true);
        $updata_data = json_decode($data['update_data'], true);
        $where = json_decode($data['update_where'], true);
        if ($type == 'pass' && $data['operation_type'] == 'edit') {
            //同意
            $this->updateData($data['table'], $updata_data, $where);
            $laset_sql = M()->getLastSql();
            $app_update['is_approval'] = 1;
            $app_update['approval_user'] = session('username');
            $app_update['approval_time'] = date('Y-m-d H:i:s');
            $res = $this->updateData('edit', $app_update, array('id' => $id));
            if ($res) {
                if ($old_data['departid'] && $updata_data['departid']) {
                    $str = '所属科室由 ' . $old_data['managedepart'] . ' 修改为 ' . $updata_data['managedepart'];
                    $this->addLog('assets_info', $laset_sql, $str, $where['assid']);
                    $this->updateAllAssetsStatus($update_assid_arr, $status, $str);
                }
                return array('status' => 1, 'msg' => '同意申请操作成功！');
            } else {
                return array('status' => -1, 'msg' => '同意申请操作失败！');
            }
        } else if ($type == 'pass' && $data['operation_type'] == 'delete') {
            $delete_data = array('is_delete' => '1');
            $this->updateData($data['table'], $delete_data, $where);
            $this->updateData($data['table'], array('main_assid' => 0), array('main_assid' => $where['assid']));
            $laset_sql = M()->getLastSql();
            $app_update['is_approval'] = 1;
            $app_update['approval_user'] = session('username');
            $app_update['approval_time'] = date('Y-m-d H:i:s');
            $res = $this->updateData('edit', $app_update, array('id' => $id));
            if ($res) {
                $str = "同意" . $data['desc'];
                $this->addLog('assets_info', $laset_sql, $str, $where['assid']);
                return array('status' => 1, 'msg' => '同意申请操作成功！');
            } else {
                return array('status' => -1, 'msg' => '同意申请操作失败！');
            }
        } else {
            //驳回
            $app_update['is_approval'] = 2;
            $app_update['approval_user'] = session('username');
            $app_update['approval_time'] = date('Y-m-d H:i:s');
            $res = $this->updateData('edit', $app_update, array('id' => $id));
            if ($res) {
                return array('status' => 1, 'msg' => '驳回申请操作成功！');
            } else {
                return array('status' => -1, 'msg' => '驳回申请操作失败！');
            }
        }
    }

    /*
    获取该设备的审批记录
     */
    public function geteditRecordList()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'asc';
        $sort = I('post.sort') ? I('post.sort') : 'applicant_time';
        $Assid = I('post.assid');
        $where['update_where'] = '{"assid":"' . $Assid . '"}';
        $total = $this->DB_get_count('edit', $where);
        $data = $this->DB_get_all('edit', '', $where, '', $sort . ' ' . $order, $offset . ',' . $limit);
        foreach ($data as $k => $v) {
            if ($v['is_approval'] == 0) {
                $data[$k]['approval'] = '未核准';
            } elseif ($v['is_approval'] == 1) {
                $data[$k]['approval'] = '已核准';
            } elseif ($v['is_approval'] == 2) {
                $data[$k]['approval'] = '驳回申请';
            }
            if ($v['operation_type'] == 'edit') {
                $data[$k]['operation_type'] = '申请修改科室';
            }
            if ($v['operation_type'] == 'delete') {
                $data[$k]['operation_type'] = '申请删除设备';
            }
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $data;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;

    }
}
