<?php
namespace Admin\Model;
use Think\Model;
use Think\Model\RelationModel;
class IntegratedSettingModel extends CommonModel{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'category';

    /*
     * 更新分类缓存
     */
    public function updateCategory()
    {
        //更新分类缓存
        $catModel = new CategoryModel();
        $catarr = $catModel->DB_get_all('category', '', array('is_delete'=>0), '', '','');
        $catname = array();
        if (is_array($catarr) && $catarr) {
            foreach ($catarr as $v) {
                $catname[$v['catid']]['hospital_id'] = $v['hospital_id'];
                $catname[$v['catid']]['category'] = $v['category'];
                $catname[$v['catid']]['catenum']  = $v['catenum'];
                $catname[$v['catid']]['parentid'] = $v['parentid'];
            }
        }
        $catdata['catname'] = ArrayToString($catname);
        made_web_array(APP_PATH . '/Common/cache/category.cache.php', $catdata);
    }

    /*
    * 更新部门缓存
    */
    public function updateDepartment()
    {
        //更新部门缓存信息
        $departModel = new DepartmentModel();
        $departarr = $departModel->DB_get_all('department','*',array('is_delete'=>0), '', '','');
        $departname = array();
        if (is_array($departarr) && $departarr) {
            foreach ($departarr as $v) {
                $departname[$v['departid']]['hospital_id'] = $v['hospital_id'];
                $departname[$v['departid']]['department'] = $v['department'];
                $departname[$v['departid']]['address'] = $v['address'];
                $departname[$v['departid']]['departrespon'] = $v['departrespon'];
                $departname[$v['departid']]['assetsrespon'] = $v['assetsrespon'];
                $departname[$v['departid']]['departtel'] = $v['departtel'];
                $departname[$v['departid']]['assetsprice'] = $v['assetsprice'];
            }
        }
        $dedata['departname'] = ArrayToString($departname);
        made_web_array(APP_PATH . '/Common/cache/department.cache.php', $dedata);
    }

}