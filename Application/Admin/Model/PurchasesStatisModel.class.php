<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2018/10/19
 * Time: 10:53
 */

namespace Admin\Model;


use Think\Model;

class PurchasesStatisModel extends CommonModel
{
    private $MODULE = 'Purchases';
    private $Controller = 'PurchasePlans';
    protected $tablePrefix = 'sb_';
    protected $tableName = 'purchases_depart_apply_assets';

    public function getAllContractedAssets()
    {
        return [
            'limit' => 0,
            'offset' => 0,
            'total' => 0,
            'rows' => [],
            'code' => 200,
            'msg' => '暂无相关数据'
        ];
    }

    /**
     * Notes: 获取采购费用分析数据
     * @param $type string 统计类型
     */
    public function getDataLists($type)
    {
        return [];
    }

    /**
     * Notes: 科室采购数据统计
     * @return mixed
     */
    public function getDepartmentAssetsNums()
    {
        return [
            'total' => 0,
            'offset' => 0,
            'limit' => 0,
            'code' => 200,
            'rows' => [],
            'charData' => [],
            'msg' => '暂无相关数据'
        ];
    }

    /**
     * Notes: 科室采购设备费用统计
     * @return mixed
     */
    public function getDepartmentAssetsFee()
    {
        return [
            'total' => 0,
            'offset' => 0,
            'limit' => 0,
            'code' => 200,
            'rows' => [],
            'charData' => [],
            'msg' => '暂无相关数据'
        ];
    }

    /**
     * Notes: 科室采购设购置类型统计
     * @return mixed
     */
    public function getAssetsBuyType()
    {
        return [
            'total' => 0,
            'offset' => 0,
            'limit' => 0,
            'code' => 200,
            'rows' => [],
            'charData' => [],
            'msg' => '暂无相关数据'
        ];
    }
}