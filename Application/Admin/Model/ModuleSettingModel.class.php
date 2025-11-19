<?php
namespace Admin\Model;


class ModuleSettingModel extends CommonModel{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'base_setting';

    function getColumn($table_name)
    {
        $sql = "SHOW FULL COLUMNS FROM sb_" . $table_name;
        $rescolumns = M($table_name)->query($sql);
        $arr = array();
        foreach ($rescolumns as $k => $v) {
            $arr[$k]['comment'] = $v['Comment'];
            $arr[$k]['field'] = $v['Field'];
        }
        return $arr;
    }
}