<?php

namespace Admin\Model;

use Think\Model;

class PointModel extends CommonModel
{
    protected $tableName = 'patrol_points';
    protected $tableFields = 'ppid,num,name,parentid,remark,result,require';

    //补充明细
    public function addSetPoints(){
        $typeName = trim(I('POST.typeName'));
        $name = I('POST.name');
        $require = I('POST.require');
        $tpid = I('POST.tpid');
        $level = I('POST.level');
        $RC = I('POST.RC');
        $XC = I('POST.XC');
        $PM = I('POST.PM');
        $this->checkstatus(judgeEmpty($typeName), '请输入类型名称');
        $this->checkstatus(judgeEmpty($name), '请输入明细名称');
        $parentid = $this->addPointsType($typeName);
        $dataArr['name'] = $name;
        $dataArr['require'] = $require;
        $levelData['level'] = $level;
        $levelData['RC'] = $RC;
        $levelData['XC'] = $XC;
        $levelData['PM'] = $PM;
        $save = $this->addOrSavePoints($parentid, $dataArr, $tpid, $levelData);
        if ($save) {
            $partolMod = new PointModel();
            $result = $partolMod->getPointsOne("A.num IN ($save)");
            return array('status' => 1, 'msg' => '修改成功', 'result' => $result);
        } else {
            return array('status' => -99, 'msg' => '已存在');
        }
    }

  /*
   * 获取明细数据
   * @params $num string 明细num
   * return array
   */
    public function getPointslist($num)
    {
        $where = "A.num IN ($num)";
        $fields = 'A.ppid,A.num,A.name,A.parentid,A.remark,A.result,A.require,B.name AS parentName';
        $join = 'LEFT JOIN sb_patrol_points AS B ON B.ppid=A.parentid';
        return $this->DB_get_all_join($this->tableName, 'A', $fields, $join, $where,'','','');
    }
   /*
    * 获取明细项数量
    * @params $where string 条件
    * return int
    */
    public function getPointslistCount($where)
    {
        return $this->DB_get_count($this->tableName, $where);
    }

   /*
    * 获取明细单条数据
    * @params $where string 条件
    * return array
    */
    public function getPointsOne($where){
        $fields = 'A.ppid,A.num,A.name,A.parentid,A.remark,A.result,A.require,B.name AS parentName';
        $join = 'LEFT JOIN sb_patrol_points AS B ON B.ppid=A.parentid';
        return $this->DB_get_one_join($this->tableName, 'A', $fields, $join, $where);
    }

   /*
    * 添加单条明细
    * @params $typeName string 明细名称
    * return ID or false
    */
    public function addPointsType($typeName)
    {
        $data = $this->DB_get_one('patrol_points', 'ppid', "name='$typeName'");
        if ($data) {
            return $data['ppid'];
        } else {
            $data['name'] = $typeName;
            return $this->insertData('patrol_points', $data);
        }
    }

   /*
    * 添加或修改明细
    * @params1 $parentid int 类型ID
    * @params2 $dataArr array 明细名称,明细默认项
    * @params3 $tpid int 模板ID
    * @params4 $levelData array 同步的模板
    * return ID or false
    */
    public function addOrSavePoints($parentid, $dataArr, $tpid, $levelData)
    {
        $name = trim($dataArr['name']);
        $require = trim($dataArr['require']);
        $points = $this->DB_get_one('patrol_points', 'num', "name='$name' and parentid!=0");
        if ($points) {
            //修改模板操作
            $save = $this->updataTemplateArr($points['num'], $tpid, $levelData);
            if($save){
                return $points['num'];
            }else{
                return false;
            }
        } else {
            //添加明细 修改模板操作
            $num = $this->DB_get_one('patrol_points', 'MAX(num) AS num', "parentid=$parentid");
            if ($num['num']) {
                $data['num'] = $num['num'] + 1;
            } else {
                $data['num'] = $parentid . sprintf("%03d", 1);
            }
            $data['name'] = $name;
            $data['require'] =$require;
            $data['result'] = '合格';
            $data['parentid'] = $parentid;
            $add = $this->insertData('patrol_points',$data);
            if($add){
                $save = $this->updataTemplateArr($data['num'], $tpid, $levelData);
                if($save){
                    return $data['num'];
                }else{
                    return false;
                }
            }else{
                die(json_encode(array('status' => -98, 'msg' => '添加失败')));
            }
        }
    }


   /*
    * 修改模板明细
    * @params1 $num string 明细编号
    * @params2 $tpid int 模板ID
    * @params3 $levelData array 同步的模板
    * return 1 or false
    */
    public function updataTemplateArr($num, $tpid, $levelData)
    {
        $RC = $levelData['RC'];
        $XC = $levelData['XC'];
        $PM = $levelData['PM'];

        $num=(string)$num;
        $template = $this->DB_get_one('patrol_template', 'tpid,name,arr_num_1,arr_num_2,arr_num_3', "tpid=$tpid");
        if ($RC == true) {
            $template['arr_num_1'] = json_encode($this->arrplusNum(json_decode($template['arr_num_1']), $num));
        }
        if ($XC == true) {
            $template['arr_num_2'] = json_encode($this->arrplusNum(json_decode($template['arr_num_2']), $num));
        }
        if ($PM == true) {
            $template['arr_num_3'] = json_encode($this->arrplusNum(json_decode($template['arr_num_3']), $num));
        }
        return $this->updateData('patrol_template', $template,array('tpid'=>$tpid));
    }


    public function getTemplate(){
        //排序加分页加搜索
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('POST.order');
        $sort = I('POST.sort');
        $name = I('POST.name');
        $where = 1;
        if (!$sort) {
            $sort = 'tpid ';
        }
        if (!$order) {
            $order = 'asc';
        }
        if ($name) {
            //模板名称搜索
            $where .= " and name like '%" . $name . "%'";
        }
        //获取总条数
        $total = $this->DB_get_count('patrol_template',$where);
        //获取数据
        $template = $this->DB_get_all('patrol_template', 'tpid,name,remark', $where, 'name', $sort . ' ' . $order, $offset . "," . $limit);
        if(!$template){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $result['code'] = 200;
        $result["total"] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["rows"] = $template;
        return $result;

    }


  /*
   * 将新明细编号加入模板
   * @params1 $arr array  模板已有的数组
   * @params2 $num string 明细编号
   * return ID or false
   */
    public function arrplusNum($arr, $num)
    {
        if (!in_array($num, $arr)) {
            Array_push($arr, $num);
        }
        return $arr;
    }

}