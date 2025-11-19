<?php

namespace Vue\Model;

class ArchivesModel extends CommonModel
{
    private $MODULE = 'Archives';
    private $Controller = 'Emergency';
    protected $tablePrefix = 'sb_';
    protected $tableName = 'archives_emergency_plan';

    /*
     * 借调申请列表 仅显示可以借调的设备
     */
    public function get_emergency_list()
    {
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $hospital_id = session('current_hospitalid');
        $join[0] = "LEFT JOIN sb_archives_emergency_plan_file AS B ON A.arempid = B.arempid";
        $join[1] = "LEFT JOIN sb_archives_emergency_category AS C ON C.id = A.category";
        $where['A.is_delete'] = '0';
        $where['B.is_delete'] = '0';
        $where['A.hospital_id'] = $hospital_id;
        $fields = 'C.name,B.file_type,A.emergency,A.add_time,count(aremfid) as num,A.arempid';
        $total = $this->DB_get_all_join('archives_emergency_plan', 'A','B.arempid', $join, $where, 'B.arempid');
        $data = $this->DB_get_all_join('archives_emergency_plan', 'A', $fields, $join, $where, 'B.arempid','', $offset . "," . $limit);
        if (!$data) {
           $result['status'] = 1;
           $result['total'] = 0;
           return $result;
        }
        foreach ($data as $key => $value) {
            $data[$key]['add_time'] = date('Y-m-d H:i',strtotime($value['add_time']));
            if ($value['num'] == 1 && ($value['file_type'] == 'word' || $value['file_type'] == 'pdf')) {
                $data[$key]['file_type'] = C('APP_NAME') . '/Public/mobile/images/icon/'.$value['file_type'].'.png';
            } else if ($value['file_type'] == 'doc' || $value['file_type'] == 'docx') {
                $data[$key]['file_type'] = C('APP_NAME') . '/Public/mobile/images/icon/word.png';
            } else {
                $data[$key]['file_type'] = C('APP_NAME') . '/Public/mobile/images/icon/txt.png';
            }
        }
        $result['total'] = count($total);
        $result['row'] = $data;
        $result['status'] = 1;
        return $result;
    }

    public function get_emergency()
    {
        $id = I('get.id');
        $join = "LEFT JOIN sb_archives_emergency_category AS C ON C.id = A.category";
        $data = $this->DB_get_one_join('archives_emergency_plan', 'A', 'A.add_time,A.content,A.emergency,C.name as type', $join, array('A.arempid' => $id));
        $data['content'] = htmlspecialchars_decode($data['content']);
        return $data;
    }

    public function get_file()
    {
        $id = I('get.id');
        $where['arempid'] = $id;
        $where['is_delete'] = '0';
        $data = $this->DB_get_all('archives_emergency_plan_file', '*', $where);
        foreach ($data as $key => $value) {
            if ($value['file_type'] == 'pdf') {
                $data[$key]['type_img'] = C('APP_NAME') . '/Public/mobile/images/icon/'.$value['file_type'].'.png';
                //$data[$key]['file_type'] = C('APP_NAME') . '/Public/mobile/images/icon/'.$value['file_type'].'.png';
            } else if ($value['file_type'] == 'doc' || $value['file_type'] == 'docx') {
                $data[$key]['type_img'] = C('APP_NAME') . '/Public/mobile/images/icon/word.png';
                //$data[$key]['file_type'] = C('APP_NAME') . '/Public/mobile/images/icon/word.png';
            } else {
                $data[$key]['type_img'] = C('APP_NAME') . '/Public/mobile/images/icon/txt.png';
                //$data[$key]['file_type'] = C('APP_NAME') . '/Public/mobile/images/icon/txt.png';
            }
        }
        return $data;
    }
}
