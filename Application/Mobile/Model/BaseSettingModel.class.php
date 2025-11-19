<?php

namespace Mobile\Model;

class BaseSettingModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    /*
     * 公告列表
     */
    public function get_notice_list()
    {
        $hospital_id = session('current_hospitalid');
        $join[0] = "LEFT JOIN sb_notice_file AS B ON A.notid = B.notid";
        $where['A.hospital_id'] = $hospital_id;
        $fields = 'B.file_type,A.title,A.adddate,count(A.notid) as num,A.notid,A.content';
        $data = $this->DB_get_all_join('notice', 'A', $fields, $join, $where, 'A.notid','','');
        foreach ($data as $key => $value) {
            $data[$key]['add_time'] = $value['adddate'];
            if ($value['num'] == 1 && ($value['file_type'] == 'word' || $value['file_type'] == 'pdf')) {
            } else if ($value['file_type'] == 'doc' || $value['file_type'] == 'docx') {
                $data[$key]['file_type'] = 'word';
            } else {
                $data[$key]['file_type'] = 'txt';
            }
        }
        return $data;
    }

    public function get_notice()
    {
        $id = I('get.id');
        $data = $this->DB_get_one('notice', 'adddate,content,title,send_user_id', ['notid'=>$id]);
        $data['content'] = htmlspecialchars_decode($data['content']);
        return $data;
    }

    public function get_file()
    {
        $id = I('get.id');
        $where['notid'] = $id;
        $where['is_delete'] = '0';
        $data = $this->DB_get_all('notice_file', '*', $where);
        foreach ($data as $key => $value) {
            $data[$key]['file_url'] = urlencode($data[$key]['file_url']);
            if ($value['file_type'] == 'pdf') {
            } else if ($value['file_type'] == 'doc' || $value['file_type'] == 'docx') {
                $data[$key]['file_type'] = 'word';
            } else {
                $data[$key]['file_type'] = 'txt';
            }
        }
        return $data;
    }
}
