<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/25
 * Time: 9:44
 */

namespace Admin\Model;

use Think\Model;
use Think\Model\RelationModel;

class BenefitModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $MODULE = 'Benefit';
    protected $CONTROLLER = 'Benefit';
    protected $len = 100;
    protected $tableName = 'assets_info';

    // 设备收支录入
    public function assetsBenefitList()
    {
        $result = [];
        return $result;
    }

    // 单机效益分析
    public function singleBenefitList()
    {
        $result = [];
        return $result;
    }

    // 科室效益分析
    public function departmentBenefitList()
    {
        $result = [];
        return $result;
    }

    //单机效益分析 饼图
    public function assetsBenefitDataGetPie()
    {
        $result = [];
        return $result;
    }

    //单机效益分析 混合
    public function assetsBenefitDataGetFix()
    {
        $result = [];
        return $result;
    }

    public function format_year_fixdata($data, $year)
    {
        $result = [];
        return $result;
    }

    public function format_all_fixdata($data)
    {
        $result = [];
        return $result;
    }

    /**
     * 获取效益明细记录
     * @return mixed
     */
    public function getBenfitDetail()
    {
        $result = [];
        return $result;
    }

    //单机效益分析 线图
    public function assetsBenefitDataGetLine()
    {
        $result = [];
        return $result;
    }

    //科室效益分析 线图
    public function departmentBenefitDataGetLine()
    {
        $result = [];
        return $result;
    }

    /**
     * 科室效益设备分析
     */
    public function departmentBenefitData()
    {
        $result = [];
        return $result;
    }

    //获取科室所有效益设备的收入
    public function getBenefitAssetsIncome()
    {
        $result = [];
        return $result;
    }

    //获取科室支出前五设备
    public function getTopFiveCostAssets($data)
    {
        $result = [];
        return $result;
    }

    //获取回本设备
    public function getHuiBenAssets()
    {
        $result = [];
        return $result;
    }

    /**
     * 获取科室效益设备
     */
    public function getBenfitAssets()
    {
        $result = [];
        return $result;
    }

    //获取待入库设备明细信息
    public function getWatingUploadBenefit()
    {
        $result = [];
        return $result;
    }

    //删除明细
    public function delTempData()
    {
        $result = [];
        return $result;
    }

    //上传文件
    public function uploadData()
    {
        $result = [];
        return $result;
    }

    //修改临时数据
    public function updateTempData()
    {
        $result = [];
        return $result;
    }

    //保存数据
    public function batchAddBenefit()
    {
        $result = [];
        return $result;
    }

    //修改临时数据
    public function updateBenefitData()
    {
        $result = [];
        return $result;
    }

    //导出模板
    public function exploreBenefitModel()
    {
        $result = [];
        return $result;
    }

    //导出设备收支明细数据
    public function exportBenefit()
    {
        $result = [];
        return $result;
    }

    /**
     * Notes: 计算月份差
     * @param $date1 string 开始月份
     * @param $date2 string 结束月份
     * @param $tags string 日期分隔符
     * @return array
     */
    function getMonthNum($date1, $date2, $tags = '-')
    {
        $result = [];
        return $result;
    }

    /**
     * Notes: 明细量入库方法
     * @param $saveTempidArr array 要保存的临时表设备ID
     * @return array
     */
    public function benefitStorage($saveTempidArr)
    {
        $result = [];
        return $result;
    }
}