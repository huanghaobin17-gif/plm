<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/3/27
 * Time: 16:19
 */

namespace Admin\Model;

use Think\Model;
use Think\Model\RelationModel;
use Admin\Model\PatrolPlanModel;
use Admin\Model\PatrolModel;

class StatisticsModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'repair';

    /**
     * Notes: 获取首页设备概况数据
     */
    public function get_assets_data()
    {
        $ids = $_POST['params'];

        //查询显示类型
        $chart_type = $this->DB_get_one('target_statistic_setting','chart_type',array('user_id'=>session('userid'),'set_hospital_id'=>session('current_hospitalid'),'set_type'=>'survey','is_show'=>1));
        $show_type = $chart_type['chart_type'] ? $chart_type['chart_type'] : 'annular';
        //统计在用设备、维修中、已报废设备数量
        $hospital_id = session('current_hospitalid');
        $as_where['hospital_id'] = $hospital_id;
        $as_where['is_delete'] = C('NO_STATUS');
        // $as_where['status'][0] = 'NOT IN';
        // $as_where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        // $as_where['status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');//外调中
        //排除已删除科室
        $del_departids = $this->get_delete_departids();
        if($del_departids){
            $as_where['departid'] = array('not in',$del_departids);
        }
        $use_repair_data = $this->DB_get_all('assets_info', 'count(*) as num,status',  $as_where, 'status');
  
        //echo M()->getLastSql();die;
        $result = $tmparr = $label = array();
        $result['title']['repair_scrap_assets'] = '维修报废设备';
        $result['title']['firstaid_assets'] = '急救设备';
        $result['title']['quality_assets'] = '质控设备';
        $result['title']['metering_assets'] = '计量设备';
        $result['title']['special_assets'] = '特种设备';
        $result['title']['Inspection_assets'] = '巡检设备';
        $result['title']['maintain_assets'] = '保养设备';
        $result['title']['lifesupport_assets'] = '生命支持类设备';
        $result['title']['big_assets'] = '大型设备(10万及以上)';
        $result['title']['insurance_assets'] = '在保买保设备';
        $all_assets_num = 0;
        $tmparr['repair_scrap_assets']['ASSETS_STATUS_REPAIR']['num'] = 0;
        $tmparr['repair_scrap_assets']['ASSETS_STATUS_SCRAP']['num'] = 0;
        $tmparr['repair_scrap_assets']['ASSETS_STATUS_USE']['num'] = 0;
        $tmparr['repair_scrap_assets']['ASSETS_STATUS_OUTSIDE']['num'] = 0;
        foreach ($use_repair_data as $k => $v) {
            if($v['status']!=2){
                $all_assets_num += $v['num'];//筛选掉报废的设备
            }
            
            if ($v['status'] == C('ASSETS_STATUS_REPAIR')) {
                //维修中
                $tmparr['repair_scrap_assets']['ASSETS_STATUS_REPAIR']['num'] = (int)$v['num'];
            } elseif ($v['status'] == C('ASSETS_STATUS_SCRAP')) {
                //已报废
                $tmparr['repair_scrap_assets']['ASSETS_STATUS_SCRAP']['num'] = (int)$v['num'];
                $scrapnum = $tmparr['repair_scrap_assets']['ASSETS_STATUS_SCRAP']['num']; //报废数量
            } elseif ($v['status'] == C('ASSETS_STATUS_USE')) {
                //在用
                $tmparr['repair_scrap_assets']['ASSETS_STATUS_USE']['num'] = (int)$v['num'];
                $result['repair_scrap_assets'][C('ASSETS_STATUS_USE')]['itemStyle']['color'] = '#5FB878';
            } else {
                $tmparr['repair_scrap_assets']['ASSETS_STATUS_OUTSIDE']['num'] += (int)$v['num'];
            }    
        }
        //echo $all_assets_num;die;
       
        foreach ($tmparr['repair_scrap_assets'] as $k => $v) {
            $result['repair_scrap_assets'][C($k)]['value'] = (int)$v['num'];
            $result['repair_scrap_assets'][C($k)]['zhanbi'] = '占比';
            $result['repair_scrap_assets'][C($k)]['ratio'] = number_format($v['num'] / $all_assets_num * 100, 2) . '%';
            if($k == 'ASSETS_STATUS_OUTSIDE'){
                $result['repair_scrap_assets'][C($k)]['title'] = '其他';
                $result['repair_scrap_assets'][C($k)]['name'] = '其他'.' '.$v['num'] . '（' . number_format($v['num'] / $all_assets_num * 100, 2) . '%）';
            }else{
                $result['repair_scrap_assets'][C($k)]['title'] = C($k . '_NAME');
                $result['repair_scrap_assets'][C($k)]['name'] = C($k . '_NAME').' '.$v['num'] . '（' . number_format($v['num'] / $all_assets_num * 100, 2) . '%）';
            }
        }
        $result['repair_scrap_assets'] = array_values($result['repair_scrap_assets']);
        $result['max']['repair_scrap_assets'] = getArrayMax($result['repair_scrap_assets'],'value');
  
        //统计急救设备
        if(in_array('firstaid_assets',$ids)){
            $firstaid_assets_where = $as_where;
            $firstaid_assets_where['is_firstaid'] = C('YES_STATUS');
            $firstaid_assets_where['is_delete'] = C('NO_STATUS');
            $firstaid_assets_where['status'] = array("NOT IN",[2]);
            $is_firstaid_num = $this->DB_get_count('assets_info', $firstaid_assets_where);
            $result['firstaid_assets'][0]['value'] = (int)$is_firstaid_num;
            $result['firstaid_assets'][0]['title'] = '急救';
            $result['firstaid_assets'][0]['zhanbi'] = '占比';
            $result['firstaid_assets'][0]['ratio'] = number_format($is_firstaid_num / $all_assets_num * 100, 2) . '%';

            $result['firstaid_assets'][0]['name'] = '急救 '.$is_firstaid_num .'（'. number_format($is_firstaid_num / $all_assets_num * 100, 2) . '%）';
            $result['firstaid_assets'][0]['itemStyle']['color'] = '#F91608';

            $result['firstaid_assets'][1]['value'] = (int)$all_assets_num - $is_firstaid_num;
            $result['firstaid_assets'][1]['title'] = '其他';
            $result['firstaid_assets'][1]['zhanbi'] = '占比';
            $result['firstaid_assets'][1]['ratio'] = number_format(($all_assets_num - $is_firstaid_num) / $all_assets_num * 100, 2) . '%';
            $result['firstaid_assets'][1]['name'] = '其他 '.($all_assets_num - $is_firstaid_num).'（'. number_format(($all_assets_num - $is_firstaid_num) / $all_assets_num * 100, 2) . '%）';
            $result['firstaid_assets'][1]['itemStyle']['color'] = '#4AB3A9';

            $result['max']['firstaid_assets'] = (int)$is_firstaid_num;
        }

        //统计质控设备
        if (in_array('quality_assets', $ids)) {
            $quality_assets_where = $as_where;
            $quality_assets_where['is_qualityAssets'] = C('YES_STATUS');
            $quality_assets_where['is_delete'] = C('NO_STATUS');
            $quality_assets_where['status'] = array("NOT IN",[2]);
            $is_qualityAssets_num = $this->DB_get_count('assets_info', $quality_assets_where);
         
            $result['quality_assets'][0]['value'] = (int)$is_qualityAssets_num;
            $result['quality_assets'][0]['title'] = '质控';
            $result['quality_assets'][0]['zhanbi'] = '占比';
            $result['quality_assets'][0]['ratio'] = number_format($is_qualityAssets_num / $all_assets_num * 100, 2) . '%';
            $result['quality_assets'][0]['name'] = '质控 ' . $is_qualityAssets_num . '（' . number_format($is_qualityAssets_num / $all_assets_num * 100, 2) . '%）';
            $result['quality_assets'][0]['itemStyle']['color'] = '#5FB878';

            $result['quality_assets'][1]['value'] = (int)$all_assets_num - $is_qualityAssets_num;
            $result['quality_assets'][1]['title'] = '其他';
            $result['quality_assets'][1]['zhanbi'] = '占比';
            $result['quality_assets'][1]['ratio'] = number_format(($all_assets_num - $is_qualityAssets_num) / $all_assets_num * 100, 2) . '%';
            $result['quality_assets'][1]['name'] = '其他 ' . ($all_assets_num - $is_qualityAssets_num) . '（' . number_format(($all_assets_num - $is_qualityAssets_num) / $all_assets_num * 100, 2) . '%）';
            $result['quality_assets'][1]['itemStyle']['color'] = '#CB651D';

            $result['max']['quality_assets'] = (int)$is_qualityAssets_num;
        }

        //统计计量设备
        if (in_array('metering_assets', $ids)) {
            $metering_assets_where = $as_where;
            $metering_assets_where['is_metering'] = C('YES_STATUS');
            $metering_assets_where['is_delete'] = C('NO_STATUS');
            $metering_assets_where['status'] = array("NOT IN",[2]);
            $is_metering_assets_num = $this->DB_get_count('assets_info', $metering_assets_where);
    
            $result['metering_assets'][0]['value'] = (int)$is_metering_assets_num;
            $result['metering_assets'][0]['title'] = '计量';
            $result['metering_assets'][0]['zhanbi'] = '占比';
            $result['metering_assets'][0]['ratio'] = number_format($is_metering_assets_num / $all_assets_num * 100, 2) . '%';
            $result['metering_assets'][0]['name'] = '计量 ' . $is_metering_assets_num . '（' . number_format($is_metering_assets_num / $all_assets_num * 100, 2) . '%）';
            $result['metering_assets'][0]['itemStyle']['color'] = '#643B1B';

            $result['metering_assets'][1]['value'] = (int)$all_assets_num - $is_metering_assets_num;
            $result['metering_assets'][1]['title'] = '其他';
            $result['metering_assets'][1]['zhanbi'] = '占比';
            $result['metering_assets'][1]['ratio'] = number_format(($all_assets_num - $is_metering_assets_num) / $all_assets_num * 100, 2) . '%';
            $result['metering_assets'][1]['name'] = '其他 ' . ($all_assets_num - $is_metering_assets_num) . '（' . number_format(($all_assets_num - $is_metering_assets_num) / $all_assets_num * 100, 2) . '%）';
            $result['metering_assets'][1]['itemStyle']['color'] = '#01AAED';

            $result['max']['metering_assets'] = (int)$is_metering_assets_num;
        }

        //统计特种设备
        if(in_array('special_assets',$ids)){
            $is_special_num = $this->DB_get_count('assets_info', array('hospital_id' => $hospital_id, 'is_delete' => C('NO_STATUS'), 'is_special' => C('YES_STATUS'),'status'=>array('not in',[2])));
           
            $result['special_assets'][0]['value'] = (int)$is_special_num;
            $result['special_assets'][0]['title'] = '特种';
            $result['special_assets'][0]['zhanbi'] = '占比';
            $result['special_assets'][0]['ratio'] = number_format($is_special_num / $all_assets_num * 100, 2) . '%';
            $result['special_assets'][0]['name'] = '特种 '.$is_special_num.'（'. number_format($is_special_num / $all_assets_num * 100, 2) . '%）';
            $result['special_assets'][0]['itemStyle']['color'] = '#92DF82';

            $result['special_assets'][1]['value'] = (int)($all_assets_num - $is_special_num);
            $result['special_assets'][1]['title'] = '其他';
            $result['special_assets'][1]['zhanbi'] = '占比';
            $result['special_assets'][1]['ratio'] = number_format(($all_assets_num - $is_special_num) / $all_assets_num * 100, 2) . '%';
            $result['special_assets'][1]['name'] = '其他 '.($all_assets_num - $is_special_num).'（'. number_format(($all_assets_num - $is_special_num) / $all_assets_num * 100, 2) . '%）';
            $result['special_assets'][1]['itemStyle']['color'] = '#EE7621';

            $result['max']['special_assets'] = (int)$is_special_num;
        }

        //统计生命支持类设备
        if(in_array('lifesupport_assets',$ids)){
            $is_lifesupport_num = $this->DB_get_count('assets_info', array('hospital_id' => $hospital_id, 'is_delete' => C('NO_STATUS'), 'is_lifesupport' => C('YES_STATUS'),'status'=>array('not in',[2])));
         
            $result['lifesupport_assets'][0]['value'] = (int)$is_lifesupport_num;
            $result['lifesupport_assets'][0]['title'] = '生命支持类';
            $result['lifesupport_assets'][0]['zhanbi'] = '占比';
            $result['lifesupport_assets'][0]['ratio'] = number_format($is_lifesupport_num / $all_assets_num * 100, 2) . '%';
            $result['lifesupport_assets'][0]['name'] = '生命支持类 '.$is_lifesupport_num.'（'. number_format($is_lifesupport_num / $all_assets_num * 100, 2) . '%）';
            $result['lifesupport_assets'][0]['itemStyle']['color'] = '#18316D';


            $result['lifesupport_assets'][1]['value'] = (int)($all_assets_num - $is_lifesupport_num);
            $result['lifesupport_assets'][1]['title'] = '其他';
            $result['lifesupport_assets'][1]['zhanbi'] = '占比';
            $result['lifesupport_assets'][1]['ratio'] = number_format(($all_assets_num - $is_lifesupport_num) / $all_assets_num * 100, 2) . '%';
            $result['lifesupport_assets'][1]['name'] = '其他 '.($all_assets_num - $is_lifesupport_num).'（'. number_format(($all_assets_num - $is_lifesupport_num) / $all_assets_num * 100, 2) . '%）';
            $result['lifesupport_assets'][1]['itemStyle']['color'] = '#2BA5E5';

            $result['max']['lifesupport_assets'] = (int)$is_lifesupport_num;
        }

        //大型设备
        if(in_array('big_assets',$ids)){
            $big_assets_num = $this->DB_get_count('assets_info', array('hospital_id' => $hospital_id, 'is_delete' => C('NO_STATUS'), 'buy_price' => array('egt', 100000),'status'=>array('not in',[2])));
       
            $result['big_assets'][0]['value'] = (int)$big_assets_num;
            $result['big_assets'][0]['title'] = '大型';
            $result['big_assets'][0]['zhanbi'] = '占比';
            $result['big_assets'][0]['ratio'] = number_format($big_assets_num / $all_assets_num * 100, 2) . '%';
            $result['big_assets'][0]['name'] = '大型 '.$big_assets_num.'（'. number_format($big_assets_num / $all_assets_num * 100, 2) . '%）';
            $result['big_assets'][0]['itemStyle']['color'] = '#2E0249';

            $result['big_assets'][1]['value'] = (int)($all_assets_num - $big_assets_num);
            $result['big_assets'][1]['title'] = '其他';
            $result['big_assets'][1]['zhanbi'] = '占比';
            $result['big_assets'][1]['ratio'] = number_format(($all_assets_num - $big_assets_num) / $all_assets_num * 100, 2) . '%';
            $result['big_assets'][1]['name'] = '其他 '.($all_assets_num - $big_assets_num).'（'. number_format(($all_assets_num - $big_assets_num) / $all_assets_num * 100, 2) . '%）';
            $result['big_assets'][1]['itemStyle']['color'] = '#AD19DE';

            $result['max']['big_assets'] = (int)$big_assets_num;
        }

        //统计在保设备、买保设备数量
        if(in_array('insurance_assets',$ids)){
            $guarantee_num = $this->DB_get_count('assets_info', array('hospital_id' => $hospital_id, 'is_delete' => C('NO_STATUS'), 'guarantee_date' => array('EGT',date('Y-m-d')),'status'=>array('not in',[2])));
   
            //统计买保设备
            $buy_join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
            $buy_num = $this->DB_get_count_join('assets_insurance','A',$buy_join, array('B.hospital_id' => $hospital_id, 'A.status' => array('NEQ',2)));

            $result['insurance_assets'][0]['value'] = (int)$guarantee_num;
            $result['insurance_assets'][0]['title'] = '在保';
            $result['insurance_assets'][0]['zhanbi'] = '占比';
            $result['insurance_assets'][0]['ratio'] = number_format($guarantee_num / ($guarantee_num+$buy_num) * 100, 2) . '%';
            $result['insurance_assets'][0]['name'] = '在保设备 '.$guarantee_num.'（'. number_format($guarantee_num / (($guarantee_num+$buy_num)) * 100, 2) . '%）';
            $result['insurance_assets'][0]['itemStyle']['color'] = '#127828';

            $result['insurance_assets'][1]['value'] = (int)$buy_num;
            $result['insurance_assets'][1]['title'] = '买保';
            $result['insurance_assets'][1]['zhanbi'] = '占比';
            $result['insurance_assets'][1]['ratio'] = number_format($buy_num / ($guarantee_num+$buy_num) * 100, 2) . '%';
            $result['insurance_assets'][1]['name'] = '买保设备 '.($buy_num).'（'. number_format($buy_num / ($guarantee_num+$buy_num) * 100, 2) . '%）';
            $result['insurance_assets'][1]['itemStyle']['color'] = '#F5C23F';

            $result['max']['insurance_assets'] = $guarantee_num > $buy_num ? (int)$guarantee_num : (int)$buy_num;
        }

        //统计巡检设备数量
        if(in_array('Inspection_assets',$ids)){
            //统计巡检设备数量
            $patrol_xc_where = $as_where;
            $patrol_xc_where['patrol_xc_cycle'] = array('gt','0');
            $patrol_xc_where['status'] = array("NOT IN",[2]);
            $guarantee_num = $this->DB_get_count('assets_info',$patrol_xc_where);
           
            $patrol_xc_where['patrol_xc_cycle'] = array('elt','0');
            $patrol_xc_where['status'] = array("NOT IN",[2]);
            $buy_num = $this->DB_get_count('assets_info',$patrol_xc_where);
           
            $result['Inspection_assets'][0]['value'] = (int)$guarantee_num;
            $result['Inspection_assets'][0]['title'] = '巡检';
            $result['Inspection_assets'][0]['zhanbi'] = '占比';
            $result['Inspection_assets'][0]['ratio'] = number_format($guarantee_num / ($guarantee_num+$buy_num) * 100, 2) . '%';
            $result['Inspection_assets'][0]['name'] = '巡检设备 '.$guarantee_num.'（'. number_format($guarantee_num / (($guarantee_num+$buy_num)) * 100, 2) . '%）';
            $result['Inspection_assets'][0]['itemStyle']['color'] = '#127828';

            $result['Inspection_assets'][1]['value'] = (int)$buy_num;
            $result['Inspection_assets'][1]['title'] = '非巡检';
            $result['Inspection_assets'][1]['zhanbi'] = '占比';
            $result['Inspection_assets'][1]['ratio'] = number_format($buy_num / ($guarantee_num+$buy_num) * 100, 2) . '%';
            $result['Inspection_assets'][1]['name'] = '非巡检设备 '.($buy_num).'（'. number_format($buy_num / ($guarantee_num+$buy_num) * 100, 2) . '%）';
            $result['Inspection_assets'][1]['itemStyle']['color'] = '#F5C23F';

            $result['max']['Inspection_assets'] = $guarantee_num > $buy_num ? (int)$guarantee_num : (int)$buy_num;
        }

        if(in_array('maintain_assets',$ids)){
            //统计保养设备数量
            $patrol_pm_where = $as_where;
            $patrol_pm_where['patrol_pm_cycle'] = array('gt','0');
            $patrol_pm_where['status'] = array("NOT IN",[2]);
            $guarantee_num = $this->DB_get_count('assets_info',$patrol_pm_where);
            
            //统计非保养设备
            $patrol_pm_where['patrol_pm_cycle'] = array('elt','0');
            $patrol_pm_where['status'] = array("NOT IN",[2]);
            $buy_num = $this->DB_get_count('assets_info',$patrol_pm_where);

            $result['maintain_assets'][0]['value'] = (int)$guarantee_num;
            $result['maintain_assets'][0]['title'] = '保养';
            $result['maintain_assets'][0]['zhanbi'] = '占比';
            $result['maintain_assets'][0]['ratio'] = number_format($guarantee_num / ($guarantee_num+$buy_num) * 100, 2) . '%';
            $result['maintain_assets'][0]['name'] = '保养设备 '.$guarantee_num.'（'. number_format($guarantee_num / (($guarantee_num+$buy_num)) * 100, 2) . '%）';
            $result['maintain_assets'][0]['itemStyle']['color'] = '#127828';

            $result['maintain_assets'][1]['value'] = (int)$buy_num;
            $result['maintain_assets'][1]['title'] = '非保养';
            $result['maintain_assets'][1]['zhanbi'] = '占比';
            $result['maintain_assets'][1]['ratio'] = number_format($buy_num / ($guarantee_num+$buy_num) * 100, 2) . '%';
            $result['maintain_assets'][1]['name'] = '非保养设备 '.($buy_num).'（'. number_format($buy_num / ($guarantee_num+$buy_num) * 100, 2) . '%）';
            $result['maintain_assets'][1]['itemStyle']['color'] = '#F5C23F';

            $result['max']['maintain_assets'] = $guarantee_num > $buy_num ? (int)$guarantee_num : (int)$buy_num;
        }
        $label['normal']['show'] = true;
        $label['normal']['position'] = 'center';
        $label['normal']['textStyle']['fontSize'] = '12';
        $label['emphasis']['show'] = true;
        $label['emphasis']['textStyle']['fontSize'] = '16';
        $label['emphasis']['textStyle']['fontWeight'] = 'bold';
        return array('status' => 1, 'data' => $result, 'label' => $label,'show_type'=>$show_type);
    }
    //本月设备已修复率
    public function get_repaired_assets($hospital_id,$start,$now){ 
        $totalwhere['A.applicant_time'] = array(array('egt', $start), array('elt', $now), 'and');
        $totalwhere['B.hospital_id'] = $hospital_id;
        $totalwhere['B.is_delete'] = 0;  
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $apply = $this->DB_get_count_join('repair','A',$join,$totalwhere);
        $finishwhere['applicant_time'] = array(array('egt', $start), array('elt', $now), 'and');
        $finishwhere['checkdate'] = array(array('egt', $start), array('elt', $now), 'and');
        $finishwhere['status'] = 8;
        $finish = $this->DB_get_count('repair', $finishwhere);
        $rate = round(($finish/$apply)*100,2);
        $data['data'][0]['itemStyle']['color'] = '#5FB878';
        $data['data'][0]['ratio'] = $rate."%";        
        $data['data'][0]['name'] = '已修复';
        $data['data'][0]['title'] = '已修复';
        $data['data'][0]['value'] = $rate;
        $data['data'][1]['itemStyle']['color'] = '#D3D3D3';
        $data['data'][1]['ratio'] = （100-$rate）."%";        
        $data['data'][1]['name'] = '未完成率';
        $data['data'][1]['title'] = '未完成率';
        $data['data'][1]['value'] = 100-$rate;
        $data['title']['left'] = '57';
        $data['title']['text'] = '已修复';
        return $data;
    }
    //本月设备保养覆盖率
    public function get_patrol_plan_assets($hospital_id,$monthStart,$now){ 
        $planModel = new PatrolPlanModel();
        $planNum = $planModel->getPlanNum($hospital_id,$monthStart,$now);
        $where['is_delete'] = 0;
        $where['is_patrol'] = 1;
        $total = $this->DB_get_count('assets_info', $where);
        $rate = round(($planNum/$total)*100);
        $data['data'][0]['itemStyle']['color'] = '#CE0000';
        $data['data'][0]['ratio'] = $rate."%";
        $data['data'][0]['name'] = '保养覆盖率';       
        $data['data'][0]['title'] = '保养覆盖率';
        $data['data'][0]['left'] = '50';
        $data['data'][0]['value'] = $rate;
        $data['data'][1]['itemStyle']['color'] = '#D3D3D3';
        $data['data'][1]['ratio'] = （100-$rate）."%";
        $data['data'][1]['name'] = '未完成率';        
        $data['data'][1]['title'] = '未完成率';
        $data['data'][1]['value'] = 100-$rate;
        $data['title']['left'] = '48';
        $data['title']['text'] = '保养覆盖率';
        return $data;
    }
    //本月设备已保养率
    public function get_patroled_assets($hospital_id,$monthStart,$now,$planNum){ 
        $patrolModel = new PatrolModel();
        $finishPassNum = $patrolModel->getPatroled($hospital_id,$monthStart,$now);
        $passNum = $finishPassNum['passNum'];
        $where['is_delete'] = 0;
        $where['is_patrol'] = 1;
        $rate = round(($passNum/$planNum)*100);
        $data['data'][0]['itemStyle']['color'] = '#5FB878';
        $data['data'][0]['ratio'] = $rate."%";
        $data['data'][0]['name'] = '已保养';
        $data['data'][0]['title'] = '已保养';
        $data['data'][0]['value'] = $rate;
        $data['data'][1]['itemStyle']['color'] = '#D3D3D3';
        $data['data'][1]['ratio'] = （100-$rate）."%";
        $data['data'][1]['name'] = '未完成率';
        $data['data'][1]['title'] = '未完成率';
        $data['data'][1]['value'] = 100-$rate;
        $data['title']['left'] = '57';
        $data['title']['text'] = '已保养';
        return $data;
    }

    public function get_assets_repair_data($year,$count_type,$hospital_id,$departids)
    {
        $total_months = (int)($year * 12);
        $start_month = date('Y-m',strtotime((-$total_months+1)." month"));
        $end_month = date('Y-m');
        $start_time = strtotime($start_month."-01 00:00:01");
        $end_time = time();
        $where['B.hospital_id'] = $hospital_id;
        $where['A.status'] = ['egt',7];
        if($departids){
            $where['A.departid'] = ['in',$departids];
        }
        $where['A.overdate'] = array(array('egt',$start_time),array('elt',$end_time),'and');
        $where['B.is_delete'] = C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "B.hospital_id,A.departid,group_concat(FROM_UNIXTIME(overdate, '%m')) as months,group_concat(actual_price) as actual_price,
group_concat(working_hours) as total_hours";
        $data = $this->DB_get_all_join('repair','A',$fields,$join,$where,'A.departid');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k=>$v){
            $data[$k]['department'] = $departname[$v['departid']]['department'];
        }
        $s_month_arr = explode('-',$start_month);
        $e_month_arr = explode('-',$end_month);
        $s_month = (int)$s_month_arr[1];
        $e_month = (int)$e_month_arr[1];
        foreach ($data as $k=>$v){
            if($s_month > $e_month){
                for ($i = $s_month;$i <= 12;$i++){
                    $data[$k]['repair_num_'.$i] = 0;
                    $data[$k]['repair_hours_'.$i] = 0;
                    $data[$k]['repair_price_'.$i] = 0;
                }
                for ($i = 1;$i <= $e_month;$i++){
                    $data[$k]['repair_num_'.$i] = 0;
                    $data[$k]['repair_hours_'.$i] = 0;
                    $data[$k]['repair_price_'.$i] = 0;
                }
            }else{
                for ($i = $s_month;$i <= $e_month;$i++){
                    $data[$k]['repair_num_'.$i] = 0;
                    $data[$k]['repair_hours_'.$i] = 0;
                    $data[$k]['repair_price_'.$i] = 0;
                }
            }
        }
        foreach ($data as $k=>$v){
            $months = explode(',',$v['months']);
            $actual_price = explode(',',$v['actual_price']);
            $total_hours = explode(',',$v['total_hours']);
            foreach ($months as $k1=>$v1){
                $data[$k]['repair_num_'.(int)$v1] += 1;
                $data[$k]['repair_hours_'.(int)$v1] += $total_hours[$k1];
                $data[$k]['repair_price_'.(int)$v1] += $actual_price[$k1];
            }
        }
        $option = [];
        $option['tooltip']['axisPointer']['type'] = 'line';
        if($s_month > $e_month){
            $i = 0;
            for ($k = $s_month;$k <= 12;$k++){
                $option['xAxis']['data'][$i] = ($s_month+$i).' 月';
                $i++;
            }
            for ($k = 1;$k <= $e_month;$k++){
                $option['xAxis']['data'][$i] = $k.' 月';
                $i++;
            }
        }else{
            $i = 0;
            for ($k = $s_month;$k <= $e_month;$k++){
                $option['xAxis']['data'][$i] = $k.' 月';
                $i++;
            }
        }
        $len = count($option['xAxis']['data']);
        foreach ($option['xAxis']['data'] as $k=>$v){
            for($i = 0;$i < $len;$i++){
                $option['series']['data'][$i] = 0;
            }
        }
        switch ($count_type){
            case 'times':
                $option['yAxis']['name'] = '单位：次';
                $option['series']['smooth'] = true;//圆滑曲线
                $option['series']['type'] = 'line';
                $option['series']['name'] = '维修次数';
                $option['legend'][] = '维修次数';
                for ($i = 0;$i < $total_months;$i++){
                    foreach ($data as $k=>$value){
                        if($s_month > $e_month){
                            if(($s_month+$i) <= 12){
                                $option['series']['data'][$i] += $value['repair_num_'.($s_month+$i)];
                            }else{
                                $option['series']['data'][$i] += $value['repair_num_'.($s_month+$i-12)];
                            }
                        }else{
                            $option['series']['data'][$i] += $value['repair_num_'.($s_month+$i)];
                        }
                    }
                }
                break;
            case 'hours':
                $option['yAxis']['name'] = '单位：时';
                $option['series']['smooth'] = true;//圆滑曲线
                $option['series']['type'] = 'line';
                $option['series']['name'] = '维修工时';
                $option['legend'][] = '维修工时';
                for ($i = 0;$i < $total_months;$i++){
                    foreach ($data as $k=>$value){
                        if($s_month > $e_month){
                            if(($s_month+$i) <= 12){
                                $option['series']['data'][$i] += $value['repair_hours_'.($s_month+$i)];
                            }else{
                                $option['series']['data'][$i] += $value['repair_hours_'.($s_month+$i-12)];
                            }
                        }else{
                            $option['series']['data'][$i] += $value['repair_hours_'.($s_month+$i)];
                        }
                    }
                }
                break;
            case 'free':
                $option['yAxis']['name'] = '单位：元';
                $option['series']['smooth'] = true;//圆滑曲线
                $option['series']['type'] = 'line';
                $option['series']['name'] = '维修费用';
                $option['legend'][] = '维修费用';
                for ($i = 0;$i < $total_months;$i++){
                    foreach ($data as $k=>$value){
                        if($s_month > $e_month){
                            if(($s_month+$i) <= 12){
                                $option['series']['data'][$i] += $value['repair_price_'.($s_month+$i)];
                            }else{
                                $option['series']['data'][$i] += $value['repair_price_'.($s_month+$i-12)];
                            }
                        }else{
                            $option['series']['data'][$i] += $value['repair_price_'.($s_month+$i)];
                        }
                    }
                }
                break;
        }
        return $option;
    }

    /**
     * Notes: 获取年度每月各科室维修数据
     * @param $hospital_id int 医院ID
     * @param $year int year 要获取的年份
     * @param $departids string 要搜索的科室ID
     */
    public function getDepartFreeEachMonths($hospital_id,$year,$departids)
    {
        $year = $year ? $year : date('Y');
        $time_start = strtotime($year.'-01-01 00:00:01');
        $time_end = strtotime($year.'-12-31 23:59:59');
        $where['B.hospital_id'] = $hospital_id;
        $where['A.status'] = ['egt',7];
        if($departids){
            $where['A.departid'] = ['in',$departids];
        }
        $where['A.overdate'] = array(array('egt',$time_start),array('elt',$time_end),'and');
        $where['B.is_delete'] = C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "B.hospital_id,A.departid,group_concat(FROM_UNIXTIME(overdate, '%m')) as months,group_concat(actual_price) as actual_price,
group_concat(working_hours) as total_hours";
        $data = $this->DB_get_all_join('repair','A',$fields,$join,$where,'A.departid');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k=>$v){
            $data[$k]['department'] = $departname[$v['departid']]['department'];
            $data[$k]['year'] = $year;
            for($i = 1;$i <= 12;$i++){
                $data[$k]['repair_num_'.$i] = 0;
                $data[$k]['repair_hours_'.$i] = 0;
                $data[$k]['repair_price_'.$i] = 0;
            }
        }
        foreach ($data as $k=>$v){
            $months = explode(',',$v['months']);
            $actual_price = explode(',',$v['actual_price']);
            $total_hours = explode(',',$v['total_hours']);
            foreach ($months as $k1=>$v1){
                $data[$k]['repair_num_'.(int)$v1] += 1;
                $data[$k]['repair_hours_'.(int)$v1] += $total_hours[$k1];
                $data[$k]['repair_price_'.(int)$v1] += $actual_price[$k1];
            }
        }
        foreach ($data as $k=>$v){
            $total_num = $total_hours = $total_price = 0;
            for($i = 1;$i <= 12;$i++){
                //$data[$k]['repair_price_'.$i] = $data[$k]['repair_price_'.$i];
                $total_num += $data[$k]['repair_num_'.$i];
                $total_hours += $data[$k]['repair_hours_'.$i];
                $total_price += $data[$k]['repair_price_'.$i];
            }
            $data[$k]['year_total_num'] = $total_num;
            $data[$k]['year_total_hours'] = $total_hours;
            $data[$k]['year_total_price'] = $total_price;
        }
        return $data;
    }

    /**
     * Notes: 获取科室维修费用
     * @param $hospital_id int 医院ID
     * @param $year int year 要获取的年份
     * @param $departids string 要搜索的科室ID
     */
    public function get_department_repair_free($hospital_id,$departids,$year)
    {
        $current_year = date('Y');
        if($year == 3){
            $start_time = strtotime(($current_year-2).'-01-01 00:00:01');
            $end_time = strtotime($current_year.'-12-31 23:59:59');
        }else{
            $total_months = (int)($year * 12);
            $start_month = date('Y-m',strtotime((-$total_months+1)." month"));
            $start_time = strtotime($start_month."-01 00:00:01");
            $end_time = time();
        }
        $where['B.hospital_id'] = $hospital_id;
        $where['A.status'] = ['egt',7];
        if($departids){
            $where['A.departid'] = ['in',$departids];
        }
        $where['A.overdate'] = array(array('egt',$start_time),array('elt',$end_time),'and');
        $where['B.is_delete'] =  C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.departid,SUM(A.actual_price) AS total_price";
        $data = $this->DB_get_all_join('repair','A',$fields,$join,$where,'A.departid');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k=>$v){
            $data[$k]['department'] = $departname[$v['departid']]['department'];
        }
        return $data;
    }

    public function format_department_repair_free($data)
    {
        $option = [];
        //由高-》排序
        array_multisort(array_column($data,'total_price'),SORT_DESC,$data);
        //默认显示前15个维修费用最高的部门
        foreach ($data as $k=>$v){
            $option['legend']['data'][] = $v['department'];
            $option['series']['data'][$k]['name'] = $v['department'];
            $option['series']['data'][$k]['value'] = $v['total_price'];
            if($k > 14){
                $option['legend']['selected'][$v['department']] = false;
            }
        }
        return $option;
    }

    /**
     * Notes: 设备增加情况
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     */
    public function get_assets_add($hospital_id,$year,$departids)
    {
        if($departids){
            $where['departid'] = array('in',$departids);
        }
        $where['hospital_id'] = $hospital_id;
        $where['storage_date'] = array(array('egt',$year.'-01-01'),array('elt',$year.'-12-31'),'and');
        $where['is_delete'] =  C('NO_STATUS');
        $fields = "assid,departid,DATE_FORMAT(storage_date,'%m') as months";
        $data = $this->DB_get_all('assets_info',$fields,$where);
        return $data;
    }

    /**
     * Notes: 科室设备增加情况
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     */
    public function get_assets_department_add($hospital_id,$year,$departids)
    {
        if($departids){
            $where['departid'] = array('in',$departids);
        }
        $where['hospital_id'] = $hospital_id;
        $where['storage_date'] = array(array('egt',$year.'-01-01'),array('elt',$year.'-12-31'),'and');
        $fields = "count(assid) AS total_num,departid";
        $where['is_delete'] =  C('NO_STATUS');
        $data = $this->DB_get_all('assets_info',$fields,$where,'departid');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k=>$v){
            if($v['departid']){
                $data[$k]['department'] = $departname[$v['departid']]['department'];
            }else{
                $data[$k]['department'] = '未知科室(院外设备)';
            }
        }
        return $data;
    }

    /**
     * Notes: 全院设备报废情况
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     */
    public function get_assets_scrap($hospital_id,$year,$departids)
    {
        if($departids){
            $where['B.departid'] = array('in',$departids);
        }
        $where['B.hospital_id'] = $hospital_id;
        $where['A.approve_status'] = array('in','-1,1');
        $where['A.scrapdate'] = array(array('egt',$year.'-01-01'),array('elt',$year.'-12-31'),'and');
        $where['B.is_delete'] =  C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.assid,B.departid,DATE_FORMAT(A.scrapdate,'%m') as months";
        $data = $this->DB_get_all_join('assets_scrap','A',$fields,$join,$where);
        return $data;
    }

    /**
     * Notes: 全院设备报废情况
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     */
    public function get_assets_department_scrap($hospital_id,$year,$departids)
    {
        if($departids){
            $where['B.departid'] = array('in',$departids);
        }
        $where['B.hospital_id'] = $hospital_id;
        $where['A.approve_status'] = array('in','-1,1');
        $where['A.scrapdate'] = array(array('egt',$year.'-01-01'),array('elt',$year.'-12-31'),'and');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "count(*) AS total_num,B.departid";
        $data = $this->DB_get_all_join('assets_scrap','A',$fields,$join,$where,'B.departid');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k=>$v){
            if($v['departid']){
                $data[$k]['department'] = $departname[$v['departid']]['department'];
            }else{
                $data[$k]['department'] = '未知科室(院外设备)';
            }
        }
        return $data;
    }

    public function format_assets_data_months_trend($data)
    {
        $option = [];
        for($i=1;$i<=12;$i++){
            $option['xAxis']['data'][] = $i.' 月';
            $option['series']['data'][] = 0;
        }
        foreach ($data as $k=>$v){
            $month = (int)$v['months'];
            $month -= 1;
            $option['series']['data'][$month] += 1;
        }
        return $option;
    }

    public function format_assets_data_department_analysis($data)
    {
        $option = [];
        //由高-》排序
        array_multisort(array_column($data,'total_num'),SORT_DESC,$data);
        foreach ($data as $k=>$v){
            $option['legend']['data'][] = $v['department'];
            $option['series']['data'][$k]['name'] = $v['department'];
            $option['series']['data'][$k]['value'] = $v['total_num'];
            if($k > 14){
                //默认显示前15个科室
                $option['legend']['selected'][$v['department']] = false;
            }
        }
        return $option;
    }

    /**
     * Notes: 设备不良事件情况
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     */
    public function get_assets_adverse($hospital_id,$year,$departids)
    {
        if($departids){
            $where['B.departid'] = array('in',$departids);
        }
        $where['B.hospital_id'] = $hospital_id;
        $where['A.report_date'] = array(array('egt',$year.'-01-01'),array('elt',$year.'-12-31'),'and');
        $where['B.is_delete'] =  C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.assid,DATE_FORMAT(A.report_date,'%m') as months";
        $data = $this->DB_get_all_join('adverse_info','A',$fields,$join,$where);
        return $data;
    }

    /**
     * Notes: 部门设备不良事件情况
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     */
    public function get_assets_department_adverse($hospital_id,$year,$departids)
    {
        if($departids){
            $where['B.departid'] = array('in',$departids);
        }
        $where['A.hospital_id'] = $hospital_id;
        $where['A.report_date'] = array(array('egt',$year.'-01-01'),array('elt',$year.'-12-31'),'and');
        $fields = "count(*) as total_num,B.departid";
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $data = $this->DB_get_all_join('adverse_info','A',$fields,$join,$where,'B.departid');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k=>$v){
            if($v['departid']){
                $data[$k]['department'] = $departname[$v['departid']]['department'];
            }else{
                $data[$k]['department'] = '未知科室(院外设备)';
            }
        }
        return $data;
    }


    /**
     * Notes: 设备采购费用情况
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     */
    public function get_assets_purchases($hospital_id,$year,$departids)
    {
        if($departids){
            $where['departid'] = array('in',$departids);
        }
        $where['hospital_id'] = $hospital_id;
        $where['contract_id'] = array('exp','is not null');
        $where['apply_date'] = array(array('egt',$year.'-01-01'),array('elt',$year.'-12-31'),'and');
        $fields = "DATE_FORMAT(apply_date,'%m') as months,SUM(nums*buy_price) as total_price";
        $data = $this->DB_get_all('purchases_depart_apply_assets',$fields,$where,'months');
        return $data;
    }

    /**
     * Notes: 科室设备采购费用情况
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     */
    public function get_assets_department_free_purchases($hospital_id,$year,$departids)
    {
        if($departids){
            $where['departid'] = array('in',$departids);
        }
        $where['hospital_id'] = $hospital_id;
        $where['contract_id'] = array('exp','is not null');
        $where['apply_date'] = array(array('egt',$year.'-01-01'),array('elt',$year.'-12-31'),'and');
        $fields = "departid,SUM(nums*buy_price) as total_num";
        $data = $this->DB_get_all('purchases_depart_apply_assets',$fields,$where,'departid');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k=>$v){
            $data[$k]['department'] = $departname[$v['departid']]['department'];
        }
        return $data;
    }

    /**
     * Notes: 科室设备采购费用情况
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     */
    public function get_assets_department_nums_purchases($hospital_id,$year,$departids)
    {
        if($departids){
            $where['departid'] = array('in',$departids);
        }
        $where['hospital_id'] = $hospital_id;
        $where['contract_id'] = array('exp','is not null');
        $where['apply_date'] = array(array('egt',$year.'-01-01'),array('elt',$year.'-12-31'),'and');
        $fields = "departid,SUM(nums) as total_num";
        $data = $this->DB_get_all('purchases_depart_apply_assets',$fields,$where,'departid');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k=>$v){
            $data[$k]['department'] = $departname[$v['departid']]['department'];
        }
        return $data;
    }


    public function format_assets_purchases($data)
    {
        $option = [];
        for($i=1;$i<=12;$i++){
            $option['xAxis']['data'][] = $i.' 月';
            $option['series']['data'][] = 0;
        }
        foreach ($data as $k=>$v){
            $month = (int)$v['months'];
            $month -= 1;
            $option['series']['data'][$month] += $v['total_price'];
        }
        return $option;
    }

    /**
     * Notes: 设备效益分析 线图
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     * @param $departids string 要查的科室
     * @return mixed
     */
    public function get_assets_benefit($hospital_id,$year,$departids)
    {
        if($departids){
            $where['A.departid'] = array('in',$departids);
        }
        $startDate = $year . '-01';
        $endDate = $year . '-12';
        $where['A.entryDate'] = array(array('egt',$startDate),array('elt',$endDate),'and');
        $where['B.hospital_id'] = $hospital_id;
        $where['B.is_delete'] =  C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assnum = B.assnum";
        $fields = "A.entryDate,SUM(A.income) AS total_income,SUM(A.depreciation_cost) AS total_zhejiu,SUM(A.material_cost) AS total_cailiao,SUM(A.maintenance_cost) AS total_weibao,
        SUM(A.management_cost) AS total_guanli,SUM(A.comprehensive_cost) AS total_zonghe,SUM(A.interest_cost) AS total_lixi";

        $data = $this->DB_get_all_join('assets_benefit','A', $fields,$join, $where, 'A.entryDate', 'entryDate');
        return $data;
    }

    public function format_assets_benefit($data)
    {
        foreach ($data as &$one){
            $year_month = explode('-',$one['entryDate']);
            $one['months'] = $year_month[1];
            //统计总费用
            //当月总费用
            $one['total_cost'] += $one['total_zhejiu'] + $one['total_cailiao'] + $one['total_weibao'] + $one['total_guanli'] + $one['total_zonghe'] + $one['total_lixi'];
           // $one['total_cost'] += $one['total_cost'];
            //总利润
            $one['total_profit'] = $one['total_income'] - $one['total_cost'];
        }
        $option = [];
        $option['yAxis']['name'] = '单位：万元';
        $option['legend']['data'] = ['总收入','总支出','总利润'];
        $option['tooltip']['axisPointer']['type'] = 'line';

        $option['series']['total_income']['name'] = '总收入';
        $option['series']['total_income']['type'] = 'line';
        $option['series']['total_income']['smooth'] = true;
        $option['series']['total_income']['itemStyle']['normal']['color'] = '#0A9912';
        $option['series']['total_income']['itemStyle']['normal']['lineStyle']['color'] = '#0A9912';

        $option['series']['total_cost']['name'] = '总支出';
        $option['series']['total_cost']['type'] = 'line';
        $option['series']['total_cost']['smooth'] = true;
        $option['series']['total_cost']['itemStyle']['normal']['color'] = '#F90909';
        $option['series']['total_cost']['itemStyle']['normal']['lineStyle']['color'] = '#F90909';

        $option['series']['total_profit']['name'] = '总利润';
        $option['series']['total_profit']['type'] = 'line';
        $option['series']['total_profit']['smooth'] = true;
        $option['series']['total_profit']['itemStyle']['normal']['color'] = '#1E9FFF';
        $option['series']['total_profit']['itemStyle']['normal']['lineStyle']['color'] = '#1E9FFF';

        for($i=1;$i<=12;$i++){
            $option['xAxis']['data'][] = $i.' 月';
            $option['series']['total_income']['data'][] = 0;
            $option['series']['total_cost']['data'][] = 0;
            $option['series']['total_profit']['data'][] = 0;
        }
        foreach ($data as $k=>$v){
            $month = (int)$v['months'];
            $month -= 1;
            //总收入数据
            $option['series']['total_income']['data'][$month] += $v['total_income'];

            //总支出数据
            $option['series']['total_cost']['data'][$month] += $v['total_cost'];

            //总利润数据
            $option['series']['total_profit']['data'][$month] += $v['total_profit'];
        }
        foreach ($option['series']['total_income']['data'] as $k=>$v){
            //总收入数据
            $option['series']['total_income']['data'][$k] = round($v / 10000, 2);
            //总支出数据
            $option['series']['total_cost']['data'][$k] = round($option['series']['total_cost']['data'][$k] / 10000, 2);
            //总利润数据
            $option['series']['total_profit']['data'][$k] = round($option['series']['total_profit']['data'][$k] / 10000, 2);
        }
        return $option;
    }

    /**
     * Notes: 科室设备效益分析 饼图
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     * @param $departids string 要查的科室
     * @return mixed
     */
    public function get_assets_depart_benefit($hospital_id,$year,$departids)
    {
        if($departids){
            $where['A.departid'] = array('in',$departids);
        }
        $startDate = $year . '-01';
        $endDate = $year . '-12';
        $where['A.entryDate'] = array(array('egt',$startDate),array('elt',$endDate),'and');
        $where['B.hospital_id'] = $hospital_id;
        $where['B.is_delete'] =  C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assnum = B.assnum";
        $fields = "A.departid,SUM(A.income) AS total_income";

        $data = $this->DB_get_all_join('assets_benefit','A', $fields,$join, $where, 'A.departid', 'entryDate');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k=>$v){
            $data[$k]['department'] = $departname[$v['departid']]['department'];
        }
        return $data;
    }

    public function format_assets_depart_benefit($data)
    {
        $option = [];
        //由高-》排序
        array_multisort(array_column($data,'total_price'),SORT_DESC,$data);
        //默认显示前15个收入最高的部门
        foreach ($data as $k=>$v){
            $option['legend']['data'][] = $v['department'];
            $option['series']['data'][$k]['name'] = $v['department'];
            $option['series']['data'][$k]['value'] = round($v['total_income'] / 10000, 2);
            if($k > 14){
                $option['legend']['selected'][$v['department']] = false;
            }
        }
        return $option;
    }

    /**
     * Notes: 设备效益支出分析 饼图
     * @param $hospital_id int 医院ID
     * @param $year int 要查的年份
     * @param $departids string 要查的科室
     * @return mixed
     */
    public function get_assets_benefit_expenditure($hospital_id,$year,$departids)
    {
        if($departids){
            $where['A.departid'] = array('in',$departids);
        }
        $startDate = $year . '-01';
        $endDate = $year . '-12';
        $where['A.entryDate'] = array(array('egt',$startDate),array('elt',$endDate),'and');
        $where['B.hospital_id'] = $hospital_id;
        $where['B.is_delete'] =  C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assnum = B.assnum";
        $fields = "SUM(A.depreciation_cost) AS total_zhejiu,SUM(A.material_cost) AS total_cailiao,SUM(A.maintenance_cost) AS total_weibao,
        SUM(A.management_cost) AS total_guanli,SUM(A.comprehensive_cost) AS total_zonghe,SUM(A.interest_cost) AS total_lixi";

        $data = $this->DB_get_one_join('assets_benefit','A', $fields,$join, $where, '', 'entryDate');

        return $data;
    }

    public function format_assets_benefit_expenditure($data)
    {
        $data['total_zhejiu'] = $data['total_zhejiu'] ? $data['total_zhejiu'] : 0;
        $data['total_cailiao'] = $data['total_cailiao'] ? $data['total_cailiao'] : 0;
        $data['total_weibao'] = $data['total_weibao'] ? $data['total_weibao'] : 0;
        $data['total_guanli'] = $data['total_guanli'] ? $data['total_guanli'] : 0;
        $data['total_zonghe'] = $data['total_zonghe'] ? $data['total_zonghe'] : 0;
        $data['total_lixi'] = $data['total_lixi'] ? $data['total_lixi'] : 0;
        if($data['total_zhejiu'] == 0 && $data['total_cailiao'] == 0 && $data['total_weibao'] == 0 && $data['total_guanli'] == 0 && $data['total_zonghe'] == 0 && $data['total_lixi'] == 0){
            return array();
        }
        $option = [];
        $option['legend']['data'] = ['折旧费用','材料费用','维保费用','管理费用','综合费用','利息支出'];
        foreach ($option['legend']['data'] as $k=>$v){
            $option['series']['data'][$k]['value'] = 0;
            $option['series']['data'][$k]['name'] = $v;
        }
        foreach ($option['series']['data'] as &$value){
            switch($value['name']){
                case '折旧费用':
                    $value['value'] = round($data['total_zhejiu'] / 10000, 2);
                    break;
                case '材料费用':
                    $value['value'] = round($data['total_cailiao'] / 10000, 2);
                    break;
                case '维保费用':
                    $value['value'] = round($data['total_weibao'] / 10000, 2);
                    break;
                case '管理费用':
                    $value['value'] = round($data['total_guanli'] / 10000, 2);
                    break;
                case '综合费用':
                    $value['value'] = round($data['total_zonghe'] / 10000, 2);
                    break;
                case '利息支出':
                    $value['value'] = round($data['total_lixi'] / 10000, 2);
                    break;
            }
        }
        return $option;
    }

    /**
     * Notes: 设备转移情况--转科
     * @param $hospital_id int 医院ID
     * @param $year int 统计年份
     * @param $departids string 统计科室
     */
    public function get_assets_transfer($hospital_id,$year,$departids)
    {

        if($departids){
            $where['A.tranout_departid'] = array('in',$departids);
        }
        $where['B.hospital_id'] = $hospital_id;
        $where['A.approve_status'] = array('in','-1,1');
        $where['A.applicant_time'] = array(array('egt',$year.'-01-01 00:00:01'),array('elt',$year.'-12-31 23:59:59'),'and');
        $where['B.is_delete'] =  C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "count(*) as total_num,DATE_FORMAT(A.applicant_time,'%m') as months";
        $res = $this->DB_get_all_join('assets_transfer','A',$fields,$join,$where,'months');
        $data = $result = [];
        for($i = 1;$i <= 12;$i++){
            $data[$i-1]['total_num'] = 0;
            $data[$i-1]['months'] = $i;
        }
        foreach ($res as $k=>$v){
            $month = (int)$v['months'];
            $data[$month-1]['total_num'] = (int)$v['total_num'];
        }
        foreach ($data as $k=>$v){
            $result[$k] = $v['total_num'];
        }
        return $result;
    }

    /**
     * Notes: 设备转移情况--外调
     * @param $hospital_id int 医院ID
     * @param $year int 统计年份
     * @param $departids string 统计科室
     */
    public function get_assets_outside($hospital_id,$year,$departids)
    {

        if($departids){
            $where['B.departid'] = array('in',$departids);
        }
        $where['B.hospital_id'] = $hospital_id;
        $where['A.approve_status'] = array('in','-1,1');
        $start_time = strtotime($year.'-01-01 00:00:01');
        $end_time = strtotime($year.'-12-31 23:59:59');
        $where['A.apply_time'] = array(array('egt',$start_time),array('elt',$end_time),'and');
        $where['B.is_delete'] =  C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "count(*) as total_num,FROM_UNIXTIME(A.apply_time,'%m') as months";
        $res = $this->DB_get_all_join('assets_outside','A',$fields,$join,$where,'months');
        $data = $result = [];
        for($i = 1;$i <= 12;$i++){
            $data[$i-1]['total_num'] = 0;
            $data[$i-1]['months'] = $i;
        }
        foreach ($res as $k=>$v){
            $month = (int)$v['months'];
            $data[$month-1]['total_num'] = (int)$v['total_num'];
        }
        foreach ($data as $k=>$v){
            $result[$k] = $v['total_num'];
        }
        return $result;
    }

    /**
     * Notes: 设备转移情况--借入
     * @param $hospital_id int 医院ID
     * @param $year int 统计年份
     * @param $departids string 统计科室
     */
    public function get_assets_borrow_in($hospital_id,$year,$departids)
    {

        if($departids){
            $where['A.apply_departid'] = array('in',$departids);
        }
        $where['B.hospital_id'] = $hospital_id;
        $where['A.examine_status'] = array('in','-1,1');
        $start_time = strtotime($year.'-01-01 00:00:01');
        $end_time = strtotime($year.'-12-31 23:59:59');
        $where['A.apply_time'] = array(array('egt',$start_time),array('elt',$end_time),'and');
        $where['B.is_delete'] =  C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "count(*) as total_num,FROM_UNIXTIME(A.apply_time,'%m') as months";
        $res = $this->DB_get_all_join('assets_borrow','A',$fields,$join,$where,'months');
        $data = $result = [];
        for($i = 1;$i <= 12;$i++){
            $data[$i-1]['total_num'] = 0;
            $data[$i-1]['months'] = $i;
        }
        foreach ($res as $k=>$v){
            $month = (int)$v['months'];
            $data[$month-1]['total_num'] = (int)$v['total_num'];
        }
        foreach ($data as $k=>$v){
            $result[$k] = $v['total_num'];
        }
        return $result;
    }

    /**
     * Notes: 设备转移情况--借出
     * @param $hospital_id int 医院ID
     * @param $year int 统计年份
     * @param $departids string 统计科室
     */
    public function get_assets_borrow_out($hospital_id,$year,$departids)
    {

        if($departids){
            $where['B.departid'] = array('in',$departids);
        }
        $where['B.hospital_id'] = $hospital_id;
        $where['A.examine_status'] = array('in','-1,1');
        $start_time = strtotime($year.'-01-01 00:00:01');
        $end_time = strtotime($year.'-12-31 23:59:59');
        $where['A.apply_time'] = array(array('egt',$start_time),array('elt',$end_time),'and');
        $where['B.is_delete'] =  C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "count(*) as total_num,FROM_UNIXTIME(A.apply_time,'%m') as months";
        $res = $this->DB_get_all_join('assets_borrow','A',$fields,$join,$where,'months');
        $data = $result = [];
        for($i = 1;$i <= 12;$i++){
            $data[$i-1]['total_num'] = 0;
            $data[$i-1]['months'] = $i;
        }
        foreach ($res as $k=>$v){
            $month = (int)$v['months'];
            $data[$month-1]['total_num'] = (int)$v['total_num'];
        }
        foreach ($data as $k=>$v){
            $result[$k] = $v['total_num'];
        }
        return $result;
    }

    /**
     * Notes: 获取用户的显示设置
     * @param $user_id int 用户ID
     * @param $hospital_id int 医院ID
     */
    public function get_show_setting($user_id,$hospital_id,$set_type)
    {
        return $this->DB_get_all('target_statistic_setting','chart_id,chart_type',array('user_id'=>$user_id,'set_hospital_id'=>$hospital_id,'is_show'=>1,'set_type'=>$set_type));
    }

    /**
     * Notes: 保存用户首页显示图标设置
     * @param $ids
     * @param $hospital_id
     * @param $set_type
     * @return array
     */
    public function save_show_setting($ids,$hospital_id,$set_type)
    {
        $user_id = session('userid');
        //全部改为不显示
        $this->updateData('target_statistic_setting',array('is_show'=>0),array('user_id'=>$user_id,'set_hospital_id'=>$hospital_id,'set_type'=>$set_type));
        foreach ($ids as $k=>$v){
            //查询是否已存在该设置
            $chart_id = $this->DB_get_one('target_statistic_setting','id,is_show',array('user_id'=>$user_id,'set_hospital_id'=>$hospital_id,'chart_id'=>$v,'set_type'=>$set_type));
            if(!$chart_id){
                $data['user_id'] = $user_id;
                $data['set_hospital_id'] = $hospital_id;
                $data['set_type'] = $set_type;
                $data['chart_id'] = $v;
                $data['is_show'] = 1;
                $data['add_time'] = date('Y-m-d H:i:s');
                $data['add_user'] = session('username');
                $this->insertData('target_statistic_setting',$data);
            }else{
                //改为显示
                $up_data['is_show'] = 1;
                $up_data['update_time'] = date('Y-m-d H:i:s');
                $up_data['update_user'] = session('username');
                $this->updateData('target_statistic_setting',$up_data,array('user_id'=>$user_id,'set_hospital_id'=>$hospital_id,'chart_id'=>$v,'set_type'=>$set_type));
            }
        }
        return array('status'=>1,'msg'=>'设置成功！','new_show_ids'=>$ids);
    }

    public function get_target_setting()
    {
        $settings = $this->DB_get_all('base_setting','*',array('module'=>'target_setting'));
        $showids = [];
        foreach($settings as $k=>$v){
            $value = json_decode($v['value'],true);
            if($value['is_open']){
                $showids[] = $v['set_item'];
            }
        }
        return $showids;
    }

    public function get_role_target_setting()
    {
        if(!session('isSuper')){
            //不是超级管理员，查询当前用户所在医院的roleid
            $join = "LEFT JOIN sb_role AS B ON A.roleid = B.roleid";
            $roleids = $this->DB_get_one_join('user_role','A','group_concat(A.roleid) as roleids',$join,array('A.userid'=>session('userid'),'B.hospital_id'=>session('current_hospitalid')));
            if($roleids['roleids']){
                $role_set =  $this->DB_get_all('role_target_setting','set_type,group_concat(chart_id) as chart_ids',array('role_id'=>array('in',$roleids['roleids'])),'set_type');
                $res = [];
                foreach($role_set as $k=>$v){
                    $res[$v['set_type']] = explode(',',$v['chart_ids']);
                }
                return $res;
            }else{
                return array();
            }
        }else{
            return array();
        }
    }

    /**
     * Notes: 获取保养次数
     */
    public function get_assets_patrol_times($year)
    {
        $hospital_id = session('current_hospitalid');
        $start_time = $year.'-01-01 00:00:01';
        $end_time = $year.'-12-31 23:59:59';
        $where['A.status'] = 2;
        $where['B.hospital_id'] = $hospital_id;
        $where['A.finish_time'] = array(array('egt',$start_time),array('elt',$end_time),'and');
        $where['B.is_delete'] = C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assetnum = B.assnum";
        $fields = "A.cycid,DATE_FORMAT(A.finish_time,'%m') as months";
        $data = $this->DB_get_all_join('patrol_execute','A',$fields,$join,$where,'A.cycid');
        return $data;
    }

    /**
     * Notes: 保养统计
     * @param $year int 统计年份
     */
    public function get_assets_patrol_abnormal($year)
    {
        $series['normal'] = $this->get_assets_status($year,'lt');
        $series['not_normal'] = $this->get_assets_status($year,'egt');
        return $series;
    }

    private function get_assets_status($year,$flag)
    {
        $hospital_id = session('current_hospitalid');
        $start_time = $year.'-01-01 00:00:01';
        $end_time = $year.'-12-31 23:59:59';
        $where['A.status'] = 2;
        $where['A.asset_status_num'] = array($flag,3);
        $where['B.hospital_id'] = $hospital_id;
        $where['A.finish_time'] = array(array('egt',$start_time),array('elt',$end_time),'and');
        $where['B.is_delete'] = C('NO_STATUS');
        $join = "LEFT JOIN sb_assets_info AS B ON A.assetnum = B.assnum";
        $fields = "count(*) as total_num,DATE_FORMAT(finish_time,'%m') as months";
        $data = $this->DB_get_all_join('patrol_execute','A',$fields,$join,$where,'months');
        $result = [];
        for($i = 0;$i < 12;$i++){
            $result[] = 0;
        }
        foreach ($result as $k=>$v){
            foreach ($data as $k1=>$v1){
                $monsth = (int)$v1['months'];
                $result[$monsth-1] = (int)$v1['total_num'];
            }
        }
        return $result;
    }

}
