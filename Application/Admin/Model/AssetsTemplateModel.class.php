<?php

namespace Admin\Model;

use Think\Model;

class AssetsTemplateModel extends CommonModel
{
    protected $tableName = 'patrol_assets_template  ';
    protected $tableFields = 'patid,assid,tpid';

    public function getAsArr($assnum, $cycId)
    {
        $cycInfo = $this->DB_get_one('patrol_plans_cycle','*',array('cycid'=>$cycId));

        $patrol_model = M('patrol_template');
        $all_tps = $patrol_model->where(array('is_delete'=>0))->getField('tpid,points_num,name');
        //获取模板信息
        $patrol_assets = M("patrol_plans_assets");
        $assnum_tpid = $patrol_assets->where(['patrid'=>$cycInfo['patrid']])->getField('assnum,assnum_tpid');
        //$assnum_tpid = json_decode($cycInfo['assnum_tpid'],true);
        $where['assnum'] = $assnum;
        $asArr = $this->DB_get_one('assets_info','assets,assnum,model,departid,catid,serialnum,assorignum,brand,status,is_firstaid,is_metering,is_qualityAssets,is_special',$where);
        $asArr['arr_num'] = $all_tps[$assnum_tpid[$assnum]]['points_num'];
        $asArr['name'] = $all_tps[$assnum_tpid[$assnum]]['name'];
        $departname = array();
        $catname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $asArr['department_name'] = $departname[$asArr['departid']]['department'];
        $asArr['cat_name'] = $catname[$asArr['catid']]['category'];
        switch ($asArr['status']) {
            case
            $asArr['status_name'] = C('ASSETS_STATUS_USE_NAME');
                break;
            case C('ASSETS_STATUS_REPAIR'):
                $asArr['status_name'] = C('ASSETS_STATUS_REPAIR_NAME');
                break;
            case C('ASSETS_STATUS_SCRAP'):
                $asArr['status_name'] = C('ASSETS_STATUS_SCRAP_NAME');
                break;
        }
        if ($asArr['is_firstaid'] == C('ASSETS_FIRST_CODE_YES')) {
            $asArr['type_name'] = C('ASSETS_FIRST_CODE_YES_NAME');
        }
        if ($asArr['is_special'] == C('ASSETS_SPEC_CODE_YES')) {
            $asArr['type_name'] .= ',' . C('ASSETS_SPEC_CODE_YES_NAME');
            $asArr['type_name'] = ltrim($asArr['type_name'], ",");
        }
        if(!$asArr['type_name']){
            $asArr['type_name']='-';
        }
        $template_array = json_decode($asArr['arr_num']);
        $asArr['count'] = count($template_array);
        $asArr['arr_num'] = implode(',', $template_array);
        return $asArr;
    }

    public function getFormatCate($data,$arr_num,$type=0){
        $cate = array();
        foreach($data as $k=>$v){
            if($v['parentid'] == 0){
                $cate[] = $v;
            }
        }
        foreach($cate as $k=>$v){
            $cate[$k]['selectedNum'] = 0;
            foreach($data as $k1=>$v1){
                if($v1['parentid'] == $v['ppid']){
                    $cate[$k]['detail'][] = $v1;
                    if($type!=1){
                        if(in_array($v1['num'],$arr_num)){
                            $cate[$k]['selectedNum'] += 1;
                        }
                    }
                }
            }
        }
        return $cate;
    }

    //返回初始化明细
    public function getIniPoints($arr_num,$arr=array(),$type=0){
        if($type){
            $where = "ppid IN (SELECT parentid FROM sb_patrol_points WHERE ppid IN ($arr_num)) or ppid IN ($arr_num)";
            $order = 'FIELD(ppid,'.$arr_num.')';
        }else{
            $where = "ppid IN (SELECT parentid FROM sb_patrol_points WHERE num IN ($arr_num)) or num IN ($arr_num)";
            $order = 'FIELD(num,'.$arr_num.')';
        }
        $pointCat = $this->DB_get_all('patrol_points', 'ppid,num,name,parentid,result,require', $where,'',$order);
        if($arr){
            foreach ($pointCat as &$one){
                foreach ($arr as &$two){
                    if($one['ppid']==$two['ppid']){
                        $one['result']=$two['result'];
                        $one['remark']=$two['abnormal_remark'];
                    }
                }
            }
        }
        return $this->getFormatCate($pointCat, $arr_num,$type);
    }
}
