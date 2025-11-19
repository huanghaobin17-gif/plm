<?php

namespace Admin\Controller\Patrol;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\ModuleModel;
use Admin\Model\ModuleSettingModel;
use Admin\Model\PatrolModel;
use Admin\Model\PatrolPlanCycleModel;
use Admin\Model\PointModel;
use Admin\Model\CategoryModel;
use Admin\Model\DepartmentModel;

class PatrolSettingController extends CheckLoginController
{
    private $MODULE = 'Patrol';

    /*
    * 保养项目类别&明细列表
     */
    public function points()
    {
        switch(I('get.type')){
            case '';
                //实例化模型
                $pointsModel = new PointModel();
//        点击类别时
                $ppid = I('get.ppid');
//        返回备注
                if($ppid){
                    $remark = $pointsModel->DB_get_one('patrol_points', 'remark', array('ppid'=>$ppid));
                    $this->ajaxReturn(array('status'=>1,'remark'=>$remark['remark']),'json');
                }
                //第一次的进页面的数据
                $points = $pointsModel->DB_get_all('patrol_points','ppid,name,remark', array('parentid' => 0),'','','');
                $firstname = $pointsModel->DB_get_one('patrol_points', 'ppid,name,remark', array('parentid' => 0));
                $this->assign('firstname', $firstname);
                $this->assign('points', $points);
                $this->display();
                break;
            case 'getDetail';
                if (IS_POST) {
                    //实例化模型
                    $pointsModel = new PointModel();
                    $limit = I('post.limit') ? I('post.limit') : 10;
                    $page = I('post.page') ? I('post.page') : 1;
                    $offset = ($page - 1) * $limit;
                    //搜索名称
                    $name = I('post.pointsDetail');
                    //如没有默认id，则为数据库第一个id
                    $ppid = I('post.ppid');
                    if ($ppid == NULL) {
                        $firstid = $pointsModel->DB_get_one('patrol_points', 'ppid', array('parentid'=>0));
                        $ppid = $firstid['ppid'] ? $firstid['ppid'] : 0;
                    } else {
                        $ppid = I('post.ppid');
                    }
                    $where = array('parentid'=>$ppid);
                    if ($name) {
                        //明细名称搜索
                        $where['name'] = array('LIKE','%' . $name . '%');
                    }
                    $order = I('POST.order');
                    $sort = I('POST.sort');
                    if (!$sort) {
                        $sort = 'ppid ';
                    }
                    if (!$order) {
                        $order = 'asc';
                    }
                    //查询当前用户是否有权限进行修改明细
                    $editDetail = get_menu($this->MODULE, 'PatrolSetting', 'editDetail');
                    //查询当前用户是否有权限进行删除明细
                    $deleteDetail = get_menu($this->MODULE, 'PatrolSetting', 'deleteDetail');
                    $total = $pointsModel->DB_get_count('patrol_points', $where);
                    //查出子表
                    $Detail = $pointsModel->DB_get_all('patrol_points', 'ppid,num,name,result,require', $where, '', $sort . ' ' . $order,$offset . "," . $limit);
                    foreach ($Detail as $k => $v) {
                        $html = '<div class="layui-btn-group">';
                        if ($editDetail) {
                            $html .= $this->returnButtonLink('<i class="layui-icon"></i>',$editDetail['actionurl'],'layui-btn layui-btn-xs layui-btn-warm','','lay-event = editDetail',$editDetail['actionname']);
                        }
                        if ($deleteDetail) {
                            $html .= $this->returnButtonLink('<i class="layui-icon">&#xe640;</i>',$deleteDetail['actionurl'],'layui-btn layui-btn-xs layui-btn-danger','','lay-event = deleteDetail',$deleteDetail['actionname']);
                        }
                        $html .= '</div>';
                        $Detail[$k]['operation'] = $html;
                    }
                    $result['total'] = $total;
                    $result["offset"] = $offset;
                    $result["limit"] = $limit;
                    $result["code"] = 200;
                    $result['rows'] = $Detail;
                    if(!$result['rows']){
                        $result['msg'] = '暂无相关数据';
                        $result['code'] = 400;
                    }
                    $this->ajaxReturn($result, 'json');
                }
        }

    }

    /*
     * 增加类型
     */
    public function addPoints()
    {
        switch(I('get.type')){
            case '':
                if (IS_POST) {
                    //实例化模型
                    $pointsModel = new PointModel();
                    //判断类别名称
                    $this->checkstatus(judgeEmpty(I('post.name')), '类别名称不能为空');
                    $add['name'] = I('post.name');
                    //备注
                    $add['remark'] = I('post.remark');
                    //类别 父id默认0
                    $add['parentid'] = 0;
                    //检查数据库有无重复
                    $addcondition = $pointsModel->DB_get_one('patrol_points', 'name', array('name' => $add['name'], 'parentid' => 0));
                    if ($addcondition['name']) {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '已存在该类别名称'));
                    } else {
                        //入库
                        $newID = $pointsModel->insertData('patrol_points', $add);
                        //日志行为记录文字
                        $log['name']=$add['name'];
                        $text = getLogText('addPointsLogText',$log);
                        $pointsModel->addLog('patrol_points',M()->getLastSql(),$text,$newID,'');
                        $this->ajaxReturn(array('status' => 1, 'msg' => '添加成功'));
                    }
                } else {
                    $this->display();
                }
            break;
            case 'addDetail'://添加明细
                if (IS_POST) {
                    //实例化模型
                    $pointsModel = new PointModel();
                    //获取父id
                    $parentid = I('post.typeid');
                    //判断名称
                    $this->checkstatus(judgeEmpty(I('post.name')), '明细名称不能为空');
                    $add['name'] = explode(',', trim(I('post.name'), ','));
                    //结果
                    $add['result'] = I('post.result');
                    //需求
                    $add['require'] = explode(',', trim(I('post.require'), ','));
                    //编号
                    $num = $pointsModel->DB_get_one('patrol_points', 'MAX(num) AS num', array('parentid' => $parentid));
                    $add['parentid'] = $parentid;
                    $result = $add['result'];
                    //组织数据
                    $addall = array();
                    $a = 1;
                    for ($i = 0; $i < count($add['name']); $i++) {
                        if ($num['num']) {
                            $addall[$i]['num'] = $num['num'] + $a;
                            $a++;
                        } else {
                            $addall[$i]['num'] = $parentid . sprintf("%03d", $a);
                            $a++;
                        }
                        $addall[$i]['name'] = $add['name'][$i];
                        $addall[$i]['require'] = $add['require'][$i];
                        $addall[$i]['result'] = $result;
                        $addall[$i]['parentid'] = $parentid;
                    }
                    //检查有无重复
                    $addcondition = $pointsModel->DB_get_all('patrol_points', 'name', 'parentid != 0');
                    $condition = array();
                    foreach ($addcondition as $k => $v) {
                        $condition[] = $v['name'];
                    }
                    if (count($add['name']) != count(array_unique($add['name']))) {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '有重复明细名称'));
                    } else {
                        if (array_intersect($add['name'], $condition)) {
                            $text = implode(',', array_intersect($add['name'], $condition));
                            $this->ajaxReturn(array('status' => -1, 'msg' => '已存在该明细名称  ' . $text));
                        } else {
                            $sum = count($addall);
                            //入库
                            $pointsModel->insertDataALL('patrol_points', $addall);
                            //日志行为记录文字
                            $log['sum']=$sum;
                            $text = getLogText('addDetailLogText',$log);
                            $pointsModel->addLog('patrol_points',M()->getLastSql(),$text,'','');
                            $this->ajaxReturn(array('status' => 1, 'msg' => '添加成功'));
                        }
                    }
                } else {
                    //实例化模型
                    $pointsModel = new PointModel();
                    $typeid = I('get.typeid');
                    $parentname = $pointsModel->DB_get_one('patrol_points', 'name', array('ppid' => $typeid));
                    $this->assign('parentname', $parentname);
                    $this->assign('typeid', $typeid);
                    $this->display('addDetail');
                }
            break;
        }

    }


    /*
    * 修改明细
    */
    public function editDetail()
    {
        if (IS_POST) {
            //实例化模型
            $pointsModel = new PointModel();
            $ppid = I('post.ppid');
            //判断名称
            $this->checkstatus(judgeEmpty(I('post.name')), '明细名称不能为空');
            $edit['name'] = I('post.name');
            //结果
            $edit['result'] = I('post.result');
            //需求
            $edit['require'] = I('post.require');
            //判断数据库有无重复
            $editcondition = $pointsModel->DB_get_one('patrol_points', 'ppid', array('name' => $edit['name'], 'ppid' => array('neq', $ppid)));
            if ($editcondition['ppid']) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '已存在该明细名称'));
            } else {
                $name = $pointsModel->DB_get_one('patrol_points', 'name', array('ppid' => $ppid));
                //入库
                $pointsModel->updateData('patrol_points', $edit, array('ppid' => $ppid));
                //日志行为记录文字
                $log['name']=$name['name'];
                $text = getLogText('editDetailLogTex',$log);
                $pointsModel->addLog('patrol_points',M()->getLastSql(),$text,$ppid,'');
                $this->ajaxReturn(array('status' => 1, 'msg' => '修改成功'));
            }
        } else {
            //实例化模型
            $pointsModel = new PointModel();
            //获取当前数据信息
            $ppid = I('get.ppid');
            $detailinfo = $pointsModel->DB_get_one('patrol_points', 'ppid,name,result,require', array('ppid' => $ppid));
            $this->assign('detailinfo', $detailinfo);
            $this->display();
        }
    }

    /*
     * 删除明细
    */
    public function deleteDetail()
    {
        //实例化模型
        $pointsModel = new PointModel();
        //当前明细id
        $ppid = I('POST.ppid');
        if ($ppid) {
            $name = $pointsModel->DB_get_one('patrol_points', 'name', array('ppid' => $ppid));
            //删除数据
            $pointsModel->deleteData('patrol_points', array('ppid' => $ppid));
            //日志行为记录文字\
            $log['name']=$name['name'];
            $text = getLogText('deleteDetailLogText',$log);
            $pointsModel->addLog('patrol_points',M()->getLastSql(),$text,$ppid,'');
            $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('status' => -1, 'msg' => '参数错误'));
        }
    }

    /*
    * 模板维护
    */
    public function template()
    {
        switch(I('get.type')){
            case '';
                if (IS_POST) {
                    //实例化模型
                    $pointsModel = new PointModel();
                    //排序加分页加搜索
                    $limit = I('post.limit') ? I('post.limit') : 10;
                    $page = I('post.page') ? I('post.page') : 1;
                    $offset = ($page - 1) * $limit;
                    $order = I('POST.order');
                    $sort = I('POST.sort');
                    $name = I('POST.templateName');
                    $where = array('1');
                    if (!$sort) {
                        $sort = 'ppid ';
                    }
                    if (!$order) {
                        $order = 'asc';
                    }
                    if ($name) {
                        //模板名称搜索
                        $where['name'] = array('LIKE','%' . $name . '%');
                    }
                    //统计总条数
                    $total = $pointsModel->DB_get_count('patrol_template', $where);
                    //获取数据
                    $template = $pointsModel->DB_get_all('patrol_template', 'tpid,name,remark', $where, '', $sort . ' ' . $order, $offset . "," . $limit);
                    //查询当前用户是否有权限进行拷贝新模板
                    $copyAddTemplate = get_menu($this->MODULE, 'PatrolSetting', 'addTemplate');
                    //查询当前用户是否有权限进行修改模板
                    $editTemplate = get_menu($this->MODULE, 'PatrolSetting', 'editTemplate');
                    //查询当前用户是否有权限进行删除模板
                    $deleteTemplate = get_menu($this->MODULE, 'PatrolSetting', 'deleteTemplate');
                    foreach ($template as $k => $v) {
                        $html = '<div class="layui-btn-group">';
                        $html .= $this->returnButtonLink('模板预览',C('ADMIN_NAME').'/PatrolSetting/template','layui-btn layui-btn-xs layui-btn-normal','','lay-event = showTemplate');
                        if ($copyAddTemplate) {
                            $html .= $this->returnButtonLink('拷贝为新模板',$copyAddTemplate['actionurl'],'layui-btn layui-btn-xs','','lay-event = copyAddTemplate');
                        }
                        if ($editTemplate) {
                            $html .= $this->returnButtonLink($editTemplate['actionname'],$editTemplate['actionurl'],'layui-btn layui-btn-xs layui-btn-warm','','lay-event = editTemplate');
                        }
                        if ($deleteTemplate) {
                            $html .= $this->returnButtonLink($deleteTemplate['actionname'],$deleteTemplate['actionurl'],'layui-btn layui-btn-xs layui-btn-danger','','lay-event = deleteTemplate');
                        }
                        $html .= '</div>';
                        $template[$k]['operation'] = $html;
                    }
                    $result['total'] = $total;
                    $result["offset"] = $offset;
                    $result["limit"] = $limit;
                    $result["code"] = 200;
                    $result['rows'] = $template;
                    if(!$result['rows']){
                        $result['msg'] = '暂无相关数据';
                        $result['code'] = 400;
                    }
                    $this->ajaxReturn($result, 'json');
                } else {
                    $this->assign('template',get_url());
                    $this->display();
                }
                break;
            case 'showTemplate';//显示模板
                $tpid = I('get.id');
                if (!$tpid) {
                    exit('参数非法');
                }
                //实例化模型
                $patrolModel = new PatrolModel();
//                //获取当前模板名称信息
                $tpInfo = $patrolModel->DB_get_one('patrol_template', 'name,remark,points_num', array('tpid' => $tpid));
                $points_num = json_decode($tpInfo['points_num']);
                if($points_num){
                    $points = $patrolModel->DB_get_all('patrol_points', '', array('num' => array('IN', $points_num)), '', '', '');
                }else{
                    exit('该模板存在错误');
                }

                $parentid = array();
                foreach ($points as $k => $v) {
                    if (!in_array($v['parentid'], $parentid)) {
                        array_push($parentid, $v['parentid']);
                    }
                }
                $pointCat = $patrolModel->DB_get_all('patrol_points', '', array('parentid' => 0, 'ppid' => array('IN', $parentid)));
                foreach ($pointCat as $k => $v) {
                    foreach ($points as $k1 => $v1) {
                        if ($v1['parentid'] == $v['ppid']) {
                            $pointCat[$k]['detail'][] = $v1;
                        }
                    }
                }
                $this->assign('tpInfo',$tpInfo);
                $this->assign('points_num', $points_num);
                $this->assign('data', $pointCat);
                $this->display('showTemplate');
        }
    }

    /*
    * 模板维护
    */
    public function addTemplate()
    {
        if(IS_POST){
            $patrolModel = new PatrolModel();
            $result = $patrolModel->addTemplateData();
            if ($result) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '添加模板成功'));
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '添加模板失败'));
            }
        }else{
            switch(I('get.type')){
                case 'copyAddTemplate':
                    $tpid = I('get.id');
                    //实例化模型
                    $patrolModel = new PatrolModel();
                    //获取当前选项
                    $tpInfo = $patrolModel->DB_get_one('patrol_template', '', array('tpid' => $tpid));
                    $points_num = json_decode($tpInfo['points_num']);
                    $order = 'FIELD(num,' . implode(',', $points_num) . ')';
                    //获取所有类别明细
                    $pointCat = $patrolModel->DB_get_all('patrol_points','','','',$order);
                    $cate = array();
                    foreach ($pointCat as $k => $v) {
                        if ($v['parentid'] == 0) {
                            $cate[] = $v;
                        }
                    }
                    foreach ($cate as $k => $v) {
                        $cate[$k]['selectedNum'] = 0;
                        foreach ($pointCat as $k1 => $v1) {
                            if ($v1['parentid'] == $v['ppid']) {
                                $cate[$k]['detail'][] = $v1;
                                $cate[$k]['sum'] = count($cate[$k]['detail']);
                                if (in_array($v1['num'], $points_num)) {
                                    $cate[$k]['selectedNum'] += 1;
                                }
                            }
                        }
                    }
                    $this->assign('tpInfo',$tpInfo);
                    $this->assign('points_num', $points_num);
                    $this->assign('data', $cate);
                    //获取模板
                    $template = $patrolModel->DB_get_all('patrol_template', 'name', '', 'name', 'tpid asc');
                    $tp = json_encode($template);
                    $this->assign('tp', $tp);
                    $this->display('copyAddTemplate');
                    break;
                default:
                    //实例化模型
                    $pointsModel = new PointModel();
                    //获取类别
                    $points = $pointsModel->DB_get_all('patrol_points', 'ppid,name,parentid,num,require', '', '', 'ppid asc');
                    $data = array();
                    foreach ($points as $k => $v) {
                        if ($v['parentid'] == 0) {
                            $data[$k]['type'] = $v['name'];
                            $data[$k]['typeid'] = $v['ppid'];
                        }
                    }
                    foreach ($data as $k => $v) {
                        $a = 0;
                        foreach ($points as $k1 => $v1) {
                            if ($v1['parentid'] == $v['typeid']) {
                                $data[$k]['detail'][$a]['name'] = $v1['name'];
                                $data[$k]['detail'][$a]['num'] = $v1['num'];
                                $data[$k]['detail'][$a]['numID'] = $v1['num'];
                                $data[$k]['detail'][$a]['require'] = $v1['require'];
                                $data[$k]['sum'] = count($data[$k]['detail']);
                                $a++;
                            }
                        }
                    }
                    //获取模板
                    $template = $pointsModel->DB_get_all('patrol_template', 'name', '', 'name', 'tpid asc');
                    $tp = json_encode($template);
                    $this->assign('close', 1);
                    $this->assign('tp', $tp);
                    $this->assign('data', $data);
                    $this->display();
                    break;
            }
        }
    }

    /*
    * 修改模板
    */
    public function editTemplate()
    {
        if(IS_POST){
            $patrolModel = new PatrolModel();
            $result = $patrolModel->editTemplateData();
            if ($result) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '修改模板成功'));
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '修改模板失败'));
            }
        }else{
            switch(I('get.type')){
                case '';
                    //实例化模型
                    $pointsModel = new PointModel();
                    $tpid = I('get.id');
                    if (!$tpid) {
                        exit('参数非法');
                    }
                    //实例化模型
                    $patrolModel = new PatrolModel();
                    //获取当前模板名称信息
                    $tpInfo = $patrolModel->DB_get_one('patrol_template', '', array('tpid' => $tpid));
                    $points_num = json_decode($tpInfo['points_num']);
                    $order = 'FIELD(num,' . implode(',', $points_num) . ')';
                    //获取所有类别明细
                    $pointCat = $patrolModel->DB_get_all('patrol_points','','','',$order);
                    $cate = array();
                    foreach ($pointCat as $k => $v) {
                        if ($v['parentid'] == 0) {
                            $cate[] = $v;
                        }
                    }
                    foreach ($cate as $k => $v) {
                        $cate[$k]['selectedNum'] = 0;
                        foreach ($pointCat as $k1 => $v1) {
                            if ($v1['parentid'] == $v['ppid']) {
                                $cate[$k]['detail'][] = $v1;
                                $cate[$k]['sum'] = count($cate[$k]['detail']);
                                if (in_array($v1['num'], $points_num)) {
                                    $cate[$k]['selectedNum'] += 1;
                                }
                            }
                        }
                    }
                    $this->assign('tpInfo', $tpInfo);
                    $this->assign('pointNum', json_decode($tpInfo['points_num']));
                    $this->assign('data', $cate);
                    //获取模板
                    $template = $pointsModel->DB_get_all('patrol_template', 'name', array('tpid' => array('neq', $tpid)), 'name', 'tpid asc');
                    $tp = json_encode($template);
                    $this->assign('tp', $tp);
                    $this->display();
                    break;
            }
        }


    }




    /*
    * 删除模板
    */
    public function deleteTemplate()
    {
        $tpid = I('get.id');
        $pointsModel = new PointModel();
        if ($tpid) {
            //查询该模板是否有绑定了巡查任务
            $cycleModel = new PatrolPlanCycleModel();
            $where['assnum_tpid'] = $tpid;
            $cycinfo = $cycleModel->DB_get_one('patrol_plans_assets','assnum_tpid',$where);
            if($cycinfo){
                //已经有巡查任务绑定该模板
                $this->ajaxReturn(array('status' => -1, 'msg' => '该模板已绑定有的巡查任务，暂不允许删除'), 'json');
            }
            $name = $pointsModel->DB_get_one('patrol_template','name',array('tpid'=>$tpid),'');
            $pointsModel->deleteData('patrol_template', array('tpid' => $tpid));
            //日志行为记录文字
            $log['name']=$name['name'];
            $text = getLogText('deleteTemplateLogText',$log);
            $pointsModel->addLog('patrol_template',M()->getLastSql(),$text,$tpid,'');
            $pointsModel->deleteData('patrol_assets_template', array('tpid' => $tpid));
            $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功'), 'json');
        } else {
            $this->ajaxReturn(array('status' => -1, 'msg' => '参数错误'), 'json');
        }
    }

    /*
    * 设备初始化模板列表
    */
    public function initialization()
    {
        if (IS_POST) {
            //实例化模型
            $pointsModel = new PointModel();
            //排序加分页加搜索
            $limit = I('post.limit') ? I('post.limit') : 10;
            $page = I('post.page') ? I('post.page') : 1;
            $offset = ($page - 1) * $limit;
            $order = I('POST.order');
            $sort = I('POST.sort');
            $assets = I('POST.initializationAssets');
            $assetsCat = I('POST.initializationCategory');
            $assetsDep = I('POST.initializationDepartment');
            $default = I('POST.initializationIsDefault');
            $price = I('POST.initializationPrice');
            $status = I('POST.initializationStatus');
            $assnum = I('POST.assnum');
            $hospital_id = I('POST.hospital_id');
            $type = I('POST.type');
            $where = [];
            if (!$sort) {
                $sort = 'assid ';
            }
            if (!$order) {
                $order = 'asc';
            }
            if ($assets) {
                //设备名称搜索
                $where['assets'] = array('like','%'.$assets.'%');
            }

            $typ=explode(',', $type);
            foreach ($typ as &$typeValue){
                if ($typeValue == 'is_firstaid') {
                    $where['is_firstaid'] = C('ASSETS_FIRST_CODE_YES');
                }
                if ($typeValue== 'is_special') {
                    $where['is_special'] = C('ASSETS_SPEC_CODE_YES');
                }
                if ($typeValue== 'is_metering') {
                    $where['is_metering'] = C('ASSETS_METER_CODE_YES');
                }
                if ($typeValue== 'is_qualityAssets') {
                    $where['is_qualityAssets'] = C('ASSETS_QUALITY_CODE_YES');
                }
                if ($typeValue== 'is_patrol') {
                    $where['is_patrol'] = C('ASSETS_PATROL_CODE_YES');
                }
                if ($typeValue== 'is_benefit') {
                    $where['is_benefit'] = C('ASSETS_BENEFIT_CODE_YES');
                }
                if ($typeValue== 'is_lifesupport') {
                    $where['is_lifesupport'] = C('ASSETS_LIFE_SUPPORT_CODE_YES');
                }
            }
            //维保状态搜索
            if ($status == 1) {
                $now = getHandleTime(time());
                $where['guarantee_date'] = array('lt',$now);
            } elseif ($status == 2) {
                $now = getHandleTime(time());
                $where['guarantee_date'] = array('gt',$now);
            }
            //设定与否搜索
            if ($default == 1) {
                $where['B.tpid'] = array('neq','');
            } elseif ($default == 2) {
                $where['B.tpid'] = array('exp', 'IS NULL');
            }
            if ($assets) {
                //设备名称搜索
                $where['assets'] = array('like','%'.$assets.'%');
            }
            if ($assetsCat) {
                //分类搜索
                $caModel = new CategoryModel();
                $catwhere['category'] = array('like','%'.$assetsCat.'%');
                $catids = $caModel->getCatidsBySearch($catwhere);
                $where['A.catid'] = array('in',$catids);
            }
            if ($assetsDep) {
                //部门搜索
                $deModel = new DepartmentModel();
                $dewhere['department'] = array('like','%'.$assetsDep.'%');
                $res = $deModel->DB_get_all('department', 'departid', $dewhere, '', 'departid asc', '');
                if ($res) {
                    $departids = '';
                    foreach ($res as $k => $v) {
                        $departids .= $v['departid'] . ',';
                    }
                    $departids = trim($departids, ',');
                    $where['A.departid'] = array('in',$departids);
                } else {
                    $result['msg'] = '暂无相关数据';
                    $result['code'] = 400;
                    $this->ajaxReturn($result, 'json');
                }
            }
            //价格区间搜索
            if($price){
                $price = explode('|',$price);
                if($price[1] == NULL){
                    $where['A.buy_price'] = array('gt',$price[0]);
                }else{
                    $where['A.buy_price'] = array('gt',$price[0]);
                    $where['A.buy_price'] = array('lt',$price[1]);
                }
            }
            if($hospital_id){
                $where['A.hospital_id'] = $hospital_id;
            }else{
                $where['A.hospital_id'] = session('current_hospitalid');
            }
            if($assnum){
                $where['A.assnum'] = $assnum;
            }
            //获取数据
            //获取总条数
            $fields = 'A.assid,A.assnum,A.assets,A.model,A.departid,A.catid,A.buy_price,A.is_firstaid,A.is_special,
            A.is_metering,A.is_qualityAssets,A.guarantee_date,B.tpid,B.default_tpid';
            $join[0] = 'LEFT JOIN sb_patrol_assets_template AS B on A.assid = B.assid';
            $total = $pointsModel->DB_get_count_join('assets_info', 'A', $join, $where);
            $asinfo = $pointsModel->DB_get_all_join('assets_info', 'A', $fields, $join, $where, 'A.assid', 'A.' . $sort . ' ' . $order, $offset . "," . $limit);
            //查询当前用户是否有权限进行设定模板
            $settingTemplate = get_menu($this->MODULE, 'PatrolSetting', 'batchSettingTemplate');
            $departname = array();
            $catname = array();
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            //判断有无查看原值的权限
            $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
            foreach ($asinfo as $k => $v) {
                $html = '';
                if (!$showPrice) {
                    $asinfo[$k]['buy_price'] = '***';
                }
                $asinfo[$k]['guarantee_date']=HandleEmptyNull($v['guarantee_date']);
                $asinfo[$k]['department'] = $departname[$v['departid']]['department'];
                $asinfo[$k]['category'] = $catname[$v['catid']]['category'];
                if ($v['is_firstaid'] == C('ASSETS_FIRST_CODE_YES')) {
                    $asinfo[$k]['assetstype'] = C('ASSETS_FIRST_CODE_YES_NAME');
                }
                if ($v['is_special'] == C('ASSETS_SPEC_CODE_YES')) {
                    $asinfo[$k]['assetstype'] .= ','.C('ASSETS_SPEC_CODE_YES_NAME');
                }
                if ($v['is_metering'] == C('ASSETS_METER_CODE_YES')) {
                    $asinfo[$k]['assetstype'] = C('ASSETS_METER_CODE_YES_NAME');
                }
                if ($v['is_qualityAssets'] == C('ASSETS_QUALITY_CODE_YES')) {
                    $asinfo[$k]['assetstype'] .= ','.C('ASSETS_QUALITY_CODE_YES_NAME');
                }
                $asinfo[$k]['assetstype'] = ltrim($asinfo[$k]['assetstype'], ",");
                if ($v['guarantee_date'] <= getHandleTime(time())) {
                    $asinfo[$k]['maintenance'] = '保外';
                } else {
                    $asinfo[$k]['maintenance'] = '保内';
                }
                $asinfo[$k]['is_default'] = explode(',', $v['is_default']);
                if ($settingTemplate) {
                    if($v['tpid']){
                        $html .= $this->returnButtonLink('修改',$settingTemplate['actionurl'],'layui-btn layui-btn-warm layui-btn-xs','','lay-event = settingTemplate');
                    }else{
                        $html .= $this->returnButtonLink('设定',$settingTemplate['actionurl'],'layui-btn layui-btn-xs','','lay-event = settingTemplate');
                    }
                }
                if($v['tpid']){
                    $tpWhere['tpid'] = ['IN',$v['tpid']];
                    $template = $pointsModel->DB_get_one('patrol_template','group_concat(name) AS name',$tpWhere);
                    $asinfo[$k]['name'] = $template['name'];
                }else{
                    $asinfo[$k]['name'] = '';
                }
                if($v['default_tpid']){
                    $template = $pointsModel->DB_get_one('patrol_template','name',['tpid'=>$v['default_tpid']]);
                    $asinfo[$k]['default_name'] = $template['name'];
                }else{
                    $asinfo[$k]['default_name'] = '';
                }
                $asinfo[$k]['operation'] = $html;
            }
            $result['total'] = $total;
            $result["offset"] = $offset;
            $result["limit"] = $limit;
            $result["code"] = 200;
            $result['rows'] = $asinfo;
            if(!$result['rows']){
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
            }
            $this->ajaxReturn($result, 'json');
        } else {
            //实例化模型
            $pointsModel = new PointModel();
            //价格区间
            $price = $pointsModel->DB_get_one('base_setting','value',array('module'=>'patrol','set_item'=>'priceRange'));
            $this->assign('price',json_decode($price['value']));
            $this->display();
        }
    }

    /*
    * 删除模板(初始化页)
    */
    public function deleteTp()
    {
        //实例化模型
        $pointsModel = new PointModel();
        //当前patid
        $patid = I('post.patid');
        $assid = I('post.assid');
        $tpid = $pointsModel->DB_get_one('patrol_assets_template', 'group_concat(tpid) AS tpid', array('assid' => $assid, 'patid' => array('neq', $patid)));
        if ($patid) {
            $pointsModel->deleteData('patrol_assets_template', array('patid' => $patid));
            $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功', 'tpid' => $tpid['tpid']));
        } else {
            $this->ajaxReturn(array('status' => -1, 'msg' => '删除失败'));
        }

    }

    /*
    * 设定模板
    */
    public function batchSettingTemplate()
    {
        switch(I('get.type')){
            case '';
                if (IS_POST) {
                    //实例化模型
                    $patrolModel = new PatrolModel();
                    $result = $patrolModel->batchSettingTemplateData();
                    if ($result) {
                        $this->ajaxReturn(array('status' => 1, 'msg' => '初始化成功'));

                    } else {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '初始化失败'));
                    }
                } else {
                    //实例化模型
                    $patrolModel = new PatrolModel();
                    $assid = trim(I('get.assid'), ',');
                    $assnum = trim(I('get.assnum'), ',');
                    $join = 'LEFT JOIN sb_assets_insurance as B ON A.assid=B.assid AND B.status=' . C('INSURANCE_STATUS_USE');
                    $fileds = 'A.assid,A.assnum,A.assets,A.catid,A.model,A.departid,A.status,A.is_firstaid,A.is_special,
                    A.is_metering,A.is_qualityAssets,A.buy_price,A.guarantee_date,B.status AS guarantee_status';
                    $assInfo = $patrolModel->DB_get_all_join('assets_info', 'A', $fileds, $join, array('A.assid' => array('IN', $assid)), '', 'A.assid DESC','');
                    //判断有无查看原值的权限
                    $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
                    $departname = [];
                    $catname = [];
                    include APP_PATH . "Common/cache/category.cache.php";
                    include APP_PATH . "Common/cache/department.cache.php";
                    foreach ($assInfo as &$one) {
                        $one['guarantee_date']=HandleEmptyNull($one['guarantee_date']);
                        $one['department'] = $departname[$one['departid']]['department'];
                        $one['category'] = $catname[$one['catid']]['category'];
                        if (!$showPrice) {
                            $one['buy_price'] = '***';
                        }
                        if ($one['is_firstaid'] == C('ASSETS_FIRST_CODE_YES')) {
                            $one['type_name'] = C('ASSETS_FIRST_CODE_YES_NAME');
                        }
                        if ($one['is_special'] == C('ASSETS_SPEC_CODE_YES')) {
                            $one['type_name'] .= ','.C('ASSETS_SPEC_CODE_YES_NAME');
                        }
                        if ($one['is_metering'] == C('ASSETS_METER_CODE_YES')) {
                            $one['type_name'] = C('ASSETS_METER_CODE_YES_NAME');
                        }
                        if ($one['is_qualityAssets'] == C('ASSETS_QUALITY_CODE_YES')) {
                            $one['type_name'] .= ','.C('ASSETS_QUALITY_CODE_YES_NAME');
                        }
                        $one['type_name'] = ltrim($one['type_name'], ",");


                        if (getHandleTime(time()) < $one['guarantee_date'] or $one['guarantee_status'] == C('INSURANCE_STATUS_USE')) {
                            $one['guarantee_status'] = '保内';
                        } else {
                            $one['guarantee_status'] = '保外';
                        }
                    }
//                    if ($assInfo) {
//                        $join='LEFT JOIN sb_patrol_template AS T ON T.tpid=A.tpid';
//                        $where['assid']=array('IN',$assid);
//                        $fileds='A.assid,A.tpid,T.name';
//                        $acInfo = $patrolModel->DB_get_all_join('patrol_assets_template', 'A', $fileds, $join,$where,'','','');
//                        $acRes=[];
//                        foreach ($acInfo as &$acValue) {
//                            $acRes[$acValue['assid']]['name'] = $acValue['name'];
//                            $acRes[$acValue['assid']]['tpid'] = $acValue['tpid'];
//                        }
//                        foreach ($assInfo as &$two) {
//                            $two['tpid'] = $acRes[$two['assid']]['tpid'];
//                            $two['name'] = $acRes[$two['assid']]['name'];
//                        }
//                    }
                    if(substr_count($assid,',')==0){
                        $existTemplate = $patrolModel->DB_get_one('patrol_assets_template','tpid,default_tpid',['assid' => $assid]);
                        if($existTemplate['tpid']){
                            $this->assign('tpid', implode(',',explode(',',$existTemplate['tpid'])));
                            $this->assign('default_tpid', $existTemplate['default_tpid']);
                        }
                    }

                    $this->assign('assnum', $assnum);
                    $this->assign('assid', $assid);
                    $this->assign('assInfo', $assInfo);
                    $this->display();
                }
                break;
            case 'tp';//设定模板的列表
                //实例化模型
                $pointsModel = new PointModel();
                $result=$pointsModel->getTemplate();
                $this->ajaxReturn($result, 'json');
        }

    }

    //巡查模块配置
    public function patrolModuleSetting()
    {
        if(IS_POST){
            $moduleModel = new ModuleModel();
            unset($_POST['action']);
            $data = array();
            //先进行模块开关配置
            foreach ($_POST as $k=>$v){
                $openwhere['module']  = $k;
                $openwhere['set_item'] = $k.'_open';
                $opendata['value'] = json_encode($_POST[$k][$k.'_open'],JSON_UNESCAPED_UNICODE);
                //更新配置值
                $moduleModel->updateData('base_setting',$opendata,$openwhere);
                $moduleStatus = $_POST[$k][$k.'_open']['is_open'];
                //修改menu表中对应模块状态
                $moduleModel->updateData('menu',array('status'=>$moduleStatus),array('name'=>ucfirst($k),'parentid'=>0));
            }
            //巡查模块配置
            $data = $moduleModel->patrolSetting($data);
            if($data['status'] == -1){
                $this->ajaxReturn($data);
            }
            //更新配置内容
            $result = $moduleModel->updateBaseSetting($data);
            $this->ajaxReturn($result);
        }else{
            $moduleSettingModel = new ModuleSettingModel();
            $base = $moduleSettingModel->DB_get_all('base_setting','module,set_item,value','','','setid asc','');
            $module = array();
            $settings = array();
            foreach ($base as $k=>$v){
                if(!in_array($v['module'],$module)){
                    $module[] = $v['module'];
                    $settings[$v['module']][$v['set_item']] = json_decode($v['value'],true);
                }else{
                    $settings[$v['module']][$v['set_item']] = json_decode($v['value'],true);
                }
            }
            $priceRange = '';
            foreach($settings['patrol']['priceRange'] as &$one){
                $priceRange .= $one."\n";
            }
            $priceRange = trim($priceRange,"\n");
            $this->assign('settings',$settings);
            $this->assign('priceRange',$priceRange);
            $this->assign('url',C('ADMIN_NAME').'/PatrolSetting/'.ACTION_NAME);
            $this->display();
        }
    }

}