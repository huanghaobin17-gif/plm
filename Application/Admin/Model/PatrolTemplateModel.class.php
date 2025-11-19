<?php

namespace Admin\Model;

use Think\Model;

class PatrolTemplateModel extends CommonModel
{
    protected $tableName = 'patrol_template';
    protected $tableFields = 'tpid,name,arr_num_1,arr_num_2,arr_num_3';


    //获取一条模板信息
    public function getOneTemplate($tpid)
    {
        $data = $this->DB_get_one($this->tableName, $this->tableFields, "tpid=$tpid");
        return $data;
    }

    //修改模板内容
    public function updateTemplate($data, $tpid)
    {
        return $this->updateData($this->tableName, $data,array('tpid'=>$tpid));
    }





}