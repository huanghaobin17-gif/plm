<?php

namespace Vue\Model;

class BaseSettingModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    /*
     * 公告列表
     */
    public function get_notice_list()
    {
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $hospital_id = session('current_hospitalid');
        $where['hospital_id'] = $hospital_id;
        $total = $this->DB_get_count('notice', $where);
        $data = $this->DB_get_all('notice', '', $where, '','top desc,adddate desc', $offset . "," . $limit);
        if (!$data) {
           $result['status'] = 1;
           $result['total'] = 0;
           return $result;
        }
        foreach ($data as $key => $value) {
            $data[$key]['top'] = (int)$value['top'];
            $data[$key]['content'] = htmlspecialchars_decode($value['content']);
            $data[$key]['add_time'] = date('Y-m-d H:i',strtotime($value['adddate']));
        }
        $result['total'] = (int)$total;
        $result['page'] = (int)$page;
        $result['pages'] = (int)ceil($total / $limit);
        $result['row'] = $data;
        $result['status'] = 1;
        return $result;
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
            if ($value['file_type'] == 'pdf') {
                $data[$key]['type_img'] = C('APP_NAME') . '/Public/mobile/images/icon/'.$value['file_type'].'.png';
            } else if ($value['file_type'] == 'doc' || $value['file_type'] == 'docx') {
                $data[$key]['type_img'] = C('APP_NAME') . '/Public/mobile/images/icon/word.png';
            } else {
                $data[$key]['type_img'] = C('APP_NAME') . '/Public/mobile/images/icon/txt.png';
            }
        }
        return $data;
    }
}
