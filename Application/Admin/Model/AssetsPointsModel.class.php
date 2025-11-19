<?php
/**
 * Created by PhpStorm.
 * User: jinlong
 * Date: 2017/3/6
 * Time: 11:55
 */
namespace Admin\Model;
use Think\Model;
use Think\Model\RelationModel;

class AssetsPointsModel extends RelationModel
{

    protected $tablePrefix = 'sb_';
    protected $tableName = 'assets_points';
    protected $_link = array(
        //关联设备表
        'AssetsInfo' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'AssetsInfo',
            'mapping_name' => 'AssetsInfo',
            'foreign_key' => 'assid',
            'as_fields' => 'catid,assets,model,brand,departid'
        ),
        //关联部门表
        'Department' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'Department',
            'mapping_name' => 'department',
            'foreign_key' => 'departid',
            'as_fields' => 'department'
        ),
        //关联分类表
        'Category' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'Category',
            'mapping_name' => 'category',
            'foreign_key' => 'catid',
            'as_fields' => 'category'
        ),
    );
    /*
     * 获取数据集合
     * @param1 $table1 string 查询表1
     * @param2 $fields string 查询的字段
     * @param3 $where string  查询条件
     * @param4 $group string 分组字段
     * @param5 $order string 排序字段
     */
    public function DB_get_all($table,$where=1,$group,$order,$fields,$relation=false)
    {
        if($relation){
            $model = D($table);
            $data   = $model
                ->field($fields)
                ->where($where)
                ->group($group)
                ->order($order)
                ->relation($relation)
                //->fetchSql(true)
                ->select();
        }else{
            $model = M($table);
            $data   = $model
                ->field($fields)
                ->where($where)
                ->group($group)
                ->order($order)
                //->fetchSql(true)
                ->select();
        }
        return $data;
    }

    /*
     * 获取单条数据
     * @param1 $table1 string 查询表1
     * @param2 $fields string 查询的字段
     * @param3 $where string  查询条件
     * @param4 $group string 分组字段
     * @param5 $order string 排序字段
     */
    public function DB_get_one($table,$where=1,$group,$order,$fields,$relation=false)
    {
        if($relation){
            $model = D($table);
            $data   = $model
                ->field($fields)
                ->where($where)
                ->group($group)
                ->order($order)
                ->relation($relation)
                //->fetchSql(true)
                ->find();
        }else{
            $model = M($table);
            $data   = $model
                ->field($fields)
                ->where($where)
                ->group($group)
                ->order($order)
                //->fetchSql(true)
                ->find();
        }
        return $data;
    }

    public function insertData($data)
    {
        $asPointModel = new AssetsPointsModel();
        // 自动启动事务支持
        $this->startTrans();
        try{
            $result   =  $asPointModel->add($data);
            if(false === $result) {
                // 发生错误自动回滚事务
                $this->rollback();
                return false;
            }
            // 提交事务
            $this->commit();
            return $result;
        } catch (ThinkException $e) {
            $this->rollback();
            throw new throw_exception($e->getMessage());
        }
    }
}