<?php

namespace Admin\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AssetsInfoModel;
use Think\Upload;


class StorageController extends CheckLoginController
{
    private $MODULE = 'Assets';

    public function json($tableName, $id)
    {
        $model    = M('assets_info');
        $model1   = M($tableName);
        $arr      = [];
        $array    = [];
        $id_array = $model->field($id)->select();
        foreach ($id_array as $k => $v) {
            $arr[$k] = $id_array[$k][$id];
        }
        $arr = array_merge(array_unique($arr));
        foreach ($arr as $k => $v) {
            $array[$k] = $model1->field($tableName)->where($id . '=' . "'" . $arr[$k] . "'")->select();
        }
        foreach ($array as $k => $v) {
            $array[$k] = $array[$k][0];
        }
        return json_encode($array, JSON_UNESCAPED_UNICODE);
    }

    function getAssign($data)
    {
        foreach ($data as $key => $value) {
            $this->assign($key, $value);
        }
    }

    function checked($assets)
    {
        $arr = [
            "is_attach",
            "is_firstaid",
            "is_special",
            "is_metering",
            "is_guarantee",
            "is_standard",
            "is_inspection",
            "is_benefit",
        ];
        foreach ($arr as $k => $v) {
            foreach ($assets as $key => $value) {
                if ($key == $v) $array[$k] = $k;
            }
        }
        foreach ($array as $k => $v) {
            unset($arr[$v]);
        }
        foreach ($arr as $k => $v) {
            $assets[$v] = 0;
        }
        return $assets;
    }

    function returnSetting($setting)
    {
        foreach ($setting as $k => $v) {
            $setting[$k][value] = json_decode($v[value], true);
        }
        return $setting;
    }

    function returnCheckvalue($data, $arr, $arrName)
    {
        $array = $data;
        if ($arr != null) {
            foreach ($data as $k => $v) {
                foreach ($arr as $key => $value) {
                    if ($v[$arrName] == $key + 1) $array[$k][$arrName] = $value;
                }
            }
        }
        return $array;
    }

    function returnColumn($table_name)
    {
        $sql        = "SHOW FULL COLUMNS FROM sb_" . $table_name;
        $rescolumns = M($table_name)->query($sql);
        foreach ($rescolumns as $k => $v) {
            $arr[$k] = $v[field];
        }
        return $arr;
    }

    function returnBatch($excel_array, $assets)
    {
        $arr = [];
        foreach ($excel_array as $k => $v) {
            foreach ($assets as $key => $value) {
                if ($v == $value) {
                    foreach (session($v) as $ke => $va) {
                        $arr[$ke][$value] = $va;
                    }
                }
            }
        }
        return $arr;
    }

    function returnDate($value)
    {
        return strtotime(date('Y-m-d', ($value - 25569) * 24 * 60 * 60));
    }

    public function asstestName()
    {
        $search_model = M('search');
        $assets       = $search_model->field('b.contract')
            ->join("a left join `__ASSETS_CONTRACT__` b on a.acid = b.acid")
            ->order('asscount desc')
            ->limit(5)
            ->select();

    }

    public function test()
    {
        $upload       = new Upload();
        $assets_model = M('assets_info');
        $assets       = $assets_model->field('assets')->select();

        $this->assign('assets', returnJson($assets, 'assets'));
//        $this -> assign('assets',$assets);
        $this->display();
    }

    public function getDepartment()
    {
        $asModel = new AssetsInfoModel();
        //查询数据库已有部门信息
        $departments = $asModel->DB_get_all('department', 'departnum,department', ['1'], '', '');
        $this->ajaxReturn(
            [
                'status'      => 1,
                'departments' => $departments,
            ], 'json'
        );
    }

    public function getCategory()
    {
        $asModel = new AssetsInfoModel();
        //查询数据库已有分类信息
        $categorys = $asModel->DB_get_all('category', 'catenum', ['1'], '', '');
        $this->ajaxReturn(
            [
                'status'    => 1,
                'categorys' => $categorys,
            ], 'json'
        );
    }

    public function getAssInfo()
    {
        $asModel = new AssetsInfoModel();
        //查询数据库已有设备编号、原编号
        $assInfo = $asModel->DB_get_all('assets_info', 'assnum,assorignum', ['1'], '', '');
        $this->ajaxReturn(
            [
                'status'  => 1,
                'assInfo' => $assInfo,
            ], 'json'
        );
    }

    public function getBasesetting()
    {
        //把资产模块属性设置返回
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $this->ajaxReturn(
            [
                'status'      => 1,
                'baseSetting' => $baseSetting['assets'],
            ], 'json'
        );
    }
}

