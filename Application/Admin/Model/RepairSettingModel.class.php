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

class RepairSettingModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'repair_setting';


    //获取所有派工数据数据
    public function getAllAssign()
    {
        $hospital_id=session('current_hospitalid');
        //派工明细
        $datawhere['hospital_id']=['EQ',$hospital_id];
        $data = $this->DB_get_all('repair_assign', 'style,userid,valuedata,assignid',$datawhere);

        //获取有维修权限的所有工程师
        $userModel = new UserModel();
        $user = $userModel->getUsers('accept','','','');

        //获取全部辅助分类
        $auxiliaryWhere['module'] = array('EQ', 'assets');
        $auxiliaryWhere['set_item'] = array('EQ', 'assets_helpcat');
        $auxiliary = $this->DB_get_one('base_setting', 'value', $auxiliaryWhere);
        $auxiliary = json_decode($auxiliary['value']);

        //获取全部设备
        $assetsWhere['status'][0] = 'NOTIN';
        $assetsWhere['status'][1][] = C('ASSETS_STATUS_SCRAP');
        $assetsWhere['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
        $assetsWhere['hospital_id'] = array('EQ', $hospital_id);
        $assets = $this->DB_get_all('assets_info', 'assid AS id,assets AS title,departid', $assetsWhere);

        //获取全部科室
        $departnameWhere['is_delete'] = array('EQ', C('NO_STATUS'));
        $departnameWhere['hospital_id'] = array('EQ',$hospital_id);
        $departname = $this->DB_get_all('department', 'departid,department', $departnameWhere);

        $catWhere['is_delete'] = array('EQ', C('NO_STATUS'));
        $catWhere['hospital_id'] = array('EQ', $hospital_id);
        $catWhere['parentid'] = array('EQ', 0);
        $catname = $this->DB_get_all('category', 'catid,category', $catWhere);

        $result = [];
        $getUserDepartment = [];
        $keyUser = [];
        $keyCategory = [];
        $keyDepartment = [];
        $keyAuxiliary = [];
        $keyAssets = [];


        foreach ($user as &$userValue) {
            $keyUser[$userValue['userid']] = $userValue;
            $getUserDepartment[] = $userValue['userid'];
        }

        foreach ($catname as &$catValue) {
                $keyCategory[$catValue['catid']]['id'] = $catValue['catid'];
                $keyCategory[$catValue['catid']]['title'] = $catValue['category'];
        }

        foreach ($departname as $key => $value) {
            $keyDepartment[$value['departid']]['id'] = $value['departid'];
            $keyDepartment[$value['departid']]['title'] = $value['department'];
        }
        foreach ($auxiliary as $key => $value) {
            $keyAuxiliary[$key]['id'] = $key;
            $keyAuxiliary[$key]['title'] = $value;
        }
        foreach ($assets as &$assetsValue) {
            $keyAssets[$assetsValue['id']]['id'] = $assetsValue['id'];
            $keyAssets[$assetsValue['id']]['title'] = $assetsValue['title'];
            $keyAssets[$assetsValue['id']]['departid'] = $assetsValue['departid'];
        }

        //新增 分类的下拉项
        $result['categorySelect'] = $keyCategory;
        //新增 科室的下拉项
        $result['departmentSelect'] = $keyDepartment;
        //新增 辅助分类的下拉项
        $result['auxiliarySelect'] = $keyAuxiliary;
        //新增 设备的下拉项
        $result['assetsSelect'] = $keyAssets;


        //新增用户的下拉项
        $result['categoryUserSelect'] = $keyUser;
        $result['departmentUserSelect'] = $keyUser;
        $result['auxiliaryUserSelect'] = $keyUser;
        $result['assetsUserSelect'] = $keyUser;

        //已分配的全部分类
        $categoryAllValue = [];
        //已分配的全部科室
        $departmentAllValue = [];
        //已分配的全部辅助分类
        $auxiliaryAllValue = [];
        //已分配的全部设备
        $assetsAllValue = [];

        //分类 分配的全部维修工程师
        $categoryAllUser = [];
        //科室  分配的全部维修工程师
        $departmentAllUser = [];
        //辅助分类 分配的全部维修工程师
        $auxiliaryAllUser = [];
        //设备 分配的全部维修工程师
        $assetsAllUser = [];

        $UserDepartmentWhere = [];
        if ($getUserDepartment) {
            $UserDepartmentWhere['userid'] = array('IN', $getUserDepartment);
        }
        $userDepartment = $this->DB_get_all('user_department', 'GROUP_CONCAT(departid) AS departid,userid', $UserDepartmentWhere, 'userid');
        $keyUserDepartment = [];
        foreach ($userDepartment as &$userDepartmentValue) {
            $userDepartmentArr=explode(',',$userDepartmentValue['departid']);
            foreach ($userDepartmentArr as &$userDepartmentArrValue){
                $keyUserDepartment[$userDepartmentValue['userid']][$userDepartmentArrValue] = $userDepartmentArrValue;
            }
        }
        if ($data) {
            foreach ($data as &$datavalue) {
                $datavalue['username'] = $keyUser[$datavalue['userid']]['username'];
                $datavalue['dataArr'] = json_decode($datavalue['valuedata']);
                $datavalue['data_val'] = implode(',', $datavalue['dataArr']);
                switch ($datavalue['style']) {
                    case C('REPAIR_ASSIGN_STYLE_CATEGORY'):
                        //按设备分类
                        //如果用户已被分配 则新增用户的下拉项unset对用userid的用户
                        unset($result['categoryUserSelect'][$datavalue['userid']]);
                        $categoryAllValue = array_merge($categoryAllValue, $datavalue['dataArr']);
                        array_push($categoryAllUser, $datavalue['userid']);
                        break;
                    case C('REPAIR_ASSIGN_STYLE_DEPARTMENT'):
                        //按科室
                        unset($result['departmentUserSelect'][$datavalue['userid']]);
                        $departmentAllValue = array_merge($departmentAllValue, $datavalue['dataArr']);
                        $departmentAllUser = array_merge($departmentAllUser, $datavalue['userid']);
                        break;
                    case C('REPAIR_ASSIGN_STYLE_AUXILIARY'):
                        //按辅助分类
                        unset($result['auxiliaryUserSelect'][$datavalue['userid']]);
                        $auxiliaryAllValue = array_merge($auxiliaryAllValue, $datavalue['dataArr']);
                        $auxiliaryAllUser = array_merge($auxiliaryAllUser, $datavalue['userid']);
                        break;
                    case C('REPAIR_ASSIGN_STYLE_ASSETS'):
                        //按设备
                        unset($result['assetsUserSelect'][$datavalue['userid']]);
                        $assetsAllValue = array_merge($assetsAllValue, $datavalue['dataArr']);
                        $assetsAllUser = array_merge($assetsAllUser, $datavalue['userid']);
                }
            }
            foreach ($data as &$datavalue) {
                switch ($datavalue['style']) {
                    case C('REPAIR_ASSIGN_STYLE_CATEGORY'):
                        //按设备分类
                        $datavalue['categorySelect'] = $keyCategory;
                        $categoryDiff = array_diff($categoryAllValue, $datavalue['dataArr']);
                        foreach ($categoryDiff as &$categoryDiffValue) {
                            //去除其他工程师已分配的分类
                            unset($datavalue['categorySelect'][$categoryDiffValue]);
                        }
                        foreach ($datavalue['dataArr'] as &$dataArrValue) {
                            //选中项选中,新增 去除已分配的项
                            $datavalue['categorySelect'][$dataArrValue]['selected'] = 'selected';
                            unset($result['categorySelect'][$dataArrValue]);
                        }
                        $categoryUserDiff = array_diff($categoryAllUser, array($datavalue['userid']));
                        foreach ($categoryUserDiff as &$categoryUserDiffValue) {
                            //去除 已分配的工程师
                            unset($datavalue['userselect'][$categoryUserDiffValue]);
                        }
                        $result['category'][] = $datavalue;
                        break;
                    case C('REPAIR_ASSIGN_STYLE_DEPARTMENT'):
                        //按科室
                        $datavalue['departmentSelect'] = $keyDepartment;
                        $departmentDiff = array_diff($departmentAllValue, $datavalue['dataArr']);
                        foreach ($departmentDiff as &$departmentDiffValue) {
                            //去除其他工程师已分配的分类
                            unset($datavalue['departmentSelect'][$departmentDiffValue]);
                        }
                        foreach ($datavalue['departmentSelect'] as $departmentSelectKey=>$departmentSelectValue){
                            //去除当前用户没有权限的科室
                            if($keyUserDepartment[$datavalue['userid']][$departmentSelectValue['id']]==null){
                                unset($datavalue['departmentSelect'][$departmentSelectKey]);
                            }
                        }
                        foreach ($datavalue['dataArr'] as &$dataArrValue) {
                            //选中项选中,新增 去除已分配的项
                            $datavalue['departmentSelect'][$dataArrValue]['selected'] = 'selected';
                            unset($result['departmentSelect'][$dataArrValue]);
                        }
                        $departmentUserDiff = array_diff($departmentAllUser, array($datavalue['userid']));
                        foreach ($departmentUserDiff as &$departmentUserDiffValue) {
                            //去除 已分配的工程师
                            unset($datavalue['userselect'][$departmentUserDiffValue]);
                        }
                        $result['department'][] = $datavalue;
                        break;
                    case C('REPAIR_ASSIGN_STYLE_AUXILIARY'):
                        //按辅助分类
                        $datavalue['auxiliarySelect'] = $keyAuxiliary;
                        $auxiliaryDiff = array_diff($auxiliaryAllValue, $datavalue['dataArr']);
                        foreach ($auxiliaryDiff as &$auxiliaryDiffValue) {
                            //去除其他工程师已分配的分类
                            unset($datavalue['auxiliarySelect'][$auxiliaryDiffValue]);
                        }
                        foreach ($datavalue['dataArr'] as &$dataArrValue) {
                            //选中项选中,新增 去除已分配的项
                            $datavalue['auxiliarySelect'][$dataArrValue]['selected'] = 'selected';
                            unset($result['auxiliarySelect'][$dataArrValue]);
                        }
                        $auxiliaryUserDiff = array_diff($auxiliaryAllUser, array($datavalue['userid']));
                        foreach ($auxiliaryUserDiff as &$auxiliaryUserDiffValue) {
                            //去除 已分配的工程师
                            unset($datavalue['userselect'][$auxiliaryUserDiffValue]);
                        }
                        $result['auxiliary'][] = $datavalue;
                        break;
                    case C('REPAIR_ASSIGN_STYLE_ASSETS'):
                        //按设备
                        $datavalue['assetsSelect'] = $keyAssets;
                        $assetsDiff = array_diff($assetsAllValue, $datavalue['dataArr']);
                        foreach ($assetsDiff as &$assetsDiffValue) {
                            //去除其他工程师已分配的分类
                            unset($datavalue['assetsSelect'][$assetsDiffValue]);
                        }
                        foreach ($datavalue['assetsSelect'] as $assetsSelectKey=>$assetsSelectValue){
                            //去除当前用户没有权限的科室
                            if($keyUserDepartment[$datavalue['userid']][$assetsSelectValue['departid']]==null){
                                unset($datavalue['assetsSelect'][$assetsSelectKey]);
                            }
                        }
                        foreach ($datavalue['dataArr'] as &$dataArrValue) {
                            //选中项选中,新增 去除已分配的项
                            $datavalue['assetsSelect'][$dataArrValue]['selected'] = 'selected';
                            unset($result['assetsSelect'][$dataArrValue]);
                        }
                        $assetsUserDiff = array_diff($assetsAllUser, array($datavalue['userid']));
                        foreach ($assetsUserDiff as &$assetsUserDiffValue) {
                            //去除 已分配的工程师
                            unset($datavalue['userselect'][$assetsUserDiffValue]);
                        }
                        $result['assets'][] = $datavalue;
                        break;
                }

            }
        }

        return $result;
    }


    //获取可指派的工程师
    public function getAssignUser()
    {
        $assignStyle = I('POST.assignStyle');
        $userid = I('POST.userid');
        if ($userid) {
            $where['userid'] = array('NEQ', $userid);
        }
        $where['style'] = array('EQ', $assignStyle);
        $AllocatedUser = $this->DB_get_all('repair_assign', 'userid', $where);
        $fileds = 'F.username,F.userid';
        $join[0] = 'LEFT JOIN sb_user_role AS D ON D.userid=F.userid';
        $join[1] = 'LEFT JOIN sb_role AS R ON R.roleid=D.roleid';
        $join[2] = 'LEFT JOIN sb_role_menu AS B ON B.roleid=R.roleid';
        $join[3] = 'LEFT JOIN sb_menu AS C ON C.menuid=B.menuid';
        $userWhere['F.status'] = array('EQ', C('OPEN_STATUS'));
        $userWhere['R.hospital_id'] = array('EQ', session('current_hospitalid'));
        $userWhere['C.name'] = array('EQ', 'accept');
        if ($AllocatedUser) {
            $NotUser = [];
            foreach ($AllocatedUser as &$one) {
                $NotUser[] = $one['userid'];
            }
            $userWhere['F.userid'] = array('NOTIN', $NotUser);
        }
        $user = $this->DB_get_all_join('user', 'F', $fileds, $join, $userWhere, 'F.userid');
        if ($user) {
            $result['status'] = 1;
            $result['msg'] = '获取成功';
            $result['result'] = $user;
        } else {
            $result['status'] = -200;
            $result['msg'] = '暂无可分配的工程师';
        }
        return $result;
    }

    //获取可分配的分类
    public function getAssignCategory()
    {
        $value = I('POST.value');
        $where['style'] = array('EQ', C('REPAIR_ASSIGN_STYLE_CATEGORY'));
        $category = $this->DB_get_all('repair_assign', 'valuedata', $where);
        $categoryWhere['parentid'] = array('EQ', 0);
        if ($category) {
            $NotCategory = [];
            foreach ($category as &$one) {
                $NotCategory = array_merge($NotCategory, json_decode($one['valuedata']));
            }
            $categoryWhere['catid'][] = array('NOTIN', $NotCategory);
            if ($value) {
                $categoryWhere['catid'][] = array('IN', $value);
                $categoryWhere['catid'][] = 'OR';
            }
        }
        $data = $this->DB_get_all('category', 'catid AS id,category AS name', $categoryWhere);
        if ($data) {
            $result['status'] = 1;
            $result['msg'] = '获取成功';
            $result['result'] = $data;
        } else {
            $result['status'] = -200;
            $result['msg'] = '暂无可分配的分类';
        }
        return $result;
    }

    //获取可分配的科室
    public function getAssignDepartment()
    {
        $value = I('POST.value');
        $userid = I('POST.userid');
        if (!$userid) {
            die(json_encode(array('status' => -1, 'msg' => '请先指定维修工程师')));
        }
        $where['style'] = array('EQ', C('REPAIR_ASSIGN_STYLE_DEPARTMENT'));
        $department = $this->DB_get_all('repair_assign', 'valuedata', $where);
        $departmentWhere = [];
        if ($department) {
            $NotDepartment = [];
            foreach ($department as &$one) {
                $NotDepartment = array_merge($NotDepartment, json_decode($one['valuedata']));
            }
            $departmentWhere[1]['departid'][] = array('NOTIN', $NotDepartment);
            if ($value) {
                $departmentWhere[1]['departid'][] = array('IN', $value);
                $departmentWhere[1]['departid'][] = 'OR';
            }
        }
        $userDepartmentWhere['userid'] = $userid;
        $departmentid = $this->DB_get_all('user_department', 'GROUP_CONCAT(departid) AS departid', $userDepartmentWhere);
        if (!$departmentid) {
            die(json_encode(array('status' => -1, 'msg' => '暂时无此工程师可分配的设备')));
        }
        $departmentWhere[2]['departid'][] = array('IN', $departmentid[0]['departid']);
        $data = $this->DB_get_all('department', 'departid AS id,department AS name', $departmentWhere);
        if ($data) {
            $result['status'] = 1;
            $result['msg'] = '获取成功';
            $result['result'] = $data;
        } else {
            $result['status'] = -200;
            $result['msg'] = '暂无可分配的科室';
        }
        return $result;
    }

    //获取可分配的辅助分类
    public function getAssignAuxiliary()
    {
        $value = I('POST.value');
        $where['style'] = array('EQ', C('REPAIR_ASSIGN_STYLE_AUXILIARY'));
        $auxiliary = $this->DB_get_all('repair_assign', 'valuedata', $where);
        $Notauxiliary = [];
        if ($auxiliary) {
            foreach ($auxiliary as &$one) {
                $Notauxiliary = array_merge($Notauxiliary, json_decode($one['valuedata']));
            }
            if ($value) {
                $valueArr = explode(',', $value);
                $Notauxiliary = array_diff($Notauxiliary, $valueArr);
            }
        }
        $auxiliaryWhere['module'] = array('EQ', 'assets');
        $auxiliaryWhere['set_item'] = array('EQ', 'assets_helpcat');
        $auxiliary = $this->DB_get_one('base_setting', 'value', $auxiliaryWhere);
        $auxiliary = json_decode($auxiliary['value']);
        $auxiliarykey = array_diff(array_keys($auxiliary), $Notauxiliary);
        $data = [];
        $i = 0;
        foreach ($auxiliarykey as &$one) {
            $data[$i]['id'] = (string)$one;
            $data[$i]['name'] = $auxiliary[$one];
            $i++;
        }
        if ($data) {
            $result['status'] = 1;
            $result['msg'] = '获取成功';
            $result['result'] = $data;
        } else {
            $result['status'] = -200;
            $result['msg'] = '暂无可分配的辅助分类';
        }
        return $result;
    }

    //获取可分配的设备
    public function getAssignAssets()
    {
        $value = I('POST.value');
        $userid = I('POST.userid');
        if (!$userid) {
            die(json_encode(array('status' => -1, 'msg' => '请先指定维修工程师')));
        }
        $where['style'] = array('EQ', C('REPAIR_ASSIGN_STYLE_ASSETS'));
        $assets = $this->DB_get_all('repair_assign', 'valuedata', $where);
        $assetsWhere = [];
        if ($assets) {
            $NotAssets = [];
            foreach ($assets as &$one) {
                $NotAssets = array_merge($NotAssets, json_decode($one['valuedata']));
            }
            $assetsWhere['assid'][] = array('NOTIN', $NotAssets);
            if ($value) {
                $assetsWhere['assid'][] = array('IN', $value);
                $assetsWhere['assid'][] = 'OR';
            }
        }
        $departmentWhere['userid'] = $userid;
        $departmentid = $this->DB_get_all('user_department', 'GROUP_CONCAT(departid) AS departid', $departmentWhere);
        if (!$departmentid) {
            die(json_encode(array('status' => -1, 'msg' => '暂时无此工程师可分配的设备')));
        }
        $assetsWhere['departid'] = array('IN', $departmentid[0]['departid']);
        $data = $this->DB_get_all('assets_info', 'assid AS id,assets AS name', $assetsWhere);
        if ($data) {
            $result['status'] = 1;
            $result['msg'] = '获取成功';
            $result['result'] = $data;
        } else {
            $result['status'] = -200;
            $result['msg'] = '暂无可分配的设备';
        }
        return $result;
    }

    //保存自动派工记录
    public function addAssign()
    {
        $userid = I('POST.userid');
        $valuedata = I('POST.valuedata');
        $username = I('POST.username');
        $valuedataname = I('POST.valuedataname');
        $style = I('POST.currentType');
        $hospital_id = I('POST.hospital_id');
        if(!$hospital_id){
            die(json_encode(array('status' => -1, 'msg' => '医院编号缺失,请刷新')));
        }
        if ($userid && $valuedata != '' && $style) {
            $where['style'] = $style;
            $where['hospital_id'] = $hospital_id;
            $data = $this->DB_get_all('repair_assign', 'userid,valuedata', $where);
            if ($data) {
                $Notvalue = [];
                foreach ($data as &$one) {
                    if ($one['userid'] == $userid) {
                        die(json_encode(array('status' => -1, 'msg' => '该维修工程师已分配,请核对')));
                    }
                    $Notvalue = array_merge($Notvalue, json_decode($one['valuedata']));
                }
                $valueArr = explode(',', $valuedata);
                $Notauxiliary = array_intersect($Notvalue, $valueArr);
                if ($Notauxiliary) {
                    die(json_encode(array('status' => -1, 'msg' => '分配项重复,请核对')));
                }
            }
            $data['style'] = $style;
            $data['hospital_id'] = $hospital_id;
            $data['valuedata'] = json_encode(explode(',', $valuedata));
            $data['userid'] = $userid;
            $data['adddate'] = time();
            $data['adduser'] = session('username');
            $add = $this->insertData('repair_assign', $data);
            if ($add) {
                $sql = M()->getLastSql();
                switch ($style) {
                    case C('REPAIR_ASSIGN_STYLE_DEPARTMENT'):
                        $style = C('REPAIR_ASSIGN_STYLE_DEPARTMENT_NAME');
                        break;
                    case C('REPAIR_ASSIGN_STYLE_CATEGORY'):
                        $style = C('REPAIR_ASSIGN_STYLE_CATEGORY_NAME');
                        //按设备类型
                        break;
                    case C('REPAIR_ASSIGN_STYLE_AUXILIARY'):
                        $style = C('REPAIR_ASSIGN_STYLE_AUXILIARY_NAME');
                        //按辅助分类
                        break;
                    case C('REPAIR_ASSIGN_STYLE_ASSETS'):
                        $style = C('REPAIR_ASSIGN_STYLE_ASSETS_NAME');
                        //按设备
                        break;
                }
                $log['user']=$username;
                $log['value']=$valuedataname;
                $log['style']=$style;
                $text = getLogText('addRepairAssignLogText',$log);
                $this->addLog('repair_assign', $sql, $text, $add);
                $addData['assignid'] = $add;
                $addData['username'] = $username;
                $addData['data_name'] = $valuedataname;
                $addData['data_val'] = $valuedata;
                $addData['userid'] = $userid;
                $result['status'] = 1;
                $result['msg'] = '添加成功';
                $result['result'] = $addData;
            } else {
                $result['status'] = -99;
                $result['msg'] = '添加失败';
            }
            return $result;
        } else {
            die(json_encode(array('status' => -1, 'msg' => '异常操作')));
        }
    }

    //修改所分配的用户
    public function saveUser()
    {
        $assignid = I('POST.assignid');
        $userid = I('POST.applicant');
        $selectedName = I('POST.selectedName');
        if ($assignid > 0 && $userid > 0) {
            $where['assignid'] = $assignid;
            $data['userid'] = $userid;
            $join = 'LEFT JOIN sb_user AS B ON B.userid=A.userid';
            $old = $this->DB_get_one_join('repair_assign', 'A', 'B.username', $join, $where);
            $save = $this->updateData('repair_assign', $data, $where);
            if ($save) {
                $sql = M()->getLastSql();
                $log['user']=$selectedName;
                $log['old']=$old['username'];
                $text = getLogText('saveUserRepairAssignLogText', $log);
                $this->addLog('repair_assign', $sql, $text, $assignid);
                $result['status'] = 1;
                $result['msg'] = '修改成功';
            } else {
                $result['status'] = -99;
                $result['msg'] = '修改失败';
            }
            return $result;
        } else {
            die(json_encode(array('status' => -1, 'msg' => '异常操作')));
        }
    }

    //修改用户已分配项
    public function saveValueData()
    {
        $assignid = I('POST.assignid');
        $style = I('POST.style');
        $valuedata = I('POST.valuedata');
        $valuedataname = I('POST.valuedataname');
        if ($assignid > 0 && $valuedata != '') {
            $where['assignid'] = $assignid;
            $where['style'] = $style;
            $join = 'LEFT JOIN sb_user AS B ON B.userid=A.userid';
            $data = $this->DB_get_one_join('repair_assign', 'A', 'B.username', $join, $where);
            if (!$data) {
                die(json_encode(array('status' => -1, 'msg' => '非法参数')));
            }
            $saveData['valuedata'] = json_encode(explode(',', $valuedata));
            $save = $this->updateData('repair_assign', $saveData, $where);
            if ($save) {
                $sql = M()->getLastSql();
                $log['value']=$valuedataname;
                $log['username']=$data['username'];
                $text =getLogText('saveValueRepairAssignLogText',$log);
                $this->addLog('repair_assign', $sql, $text, $assignid);
                $result['status'] = 1;
                $result['msg'] = '修改成功';
            } else {
                $result['status'] = -99;
                $result['msg'] = '修改失败';
            }
            return $result;
        } else {
            die(json_encode(array('status' => -1, 'msg' => '异常操作')));
        }
    }

    //删除派工
    public function delAssign(){
        $assignid = I('POST.assignid');
        if ($assignid > 0 ) {
            $where['assignid'] = $assignid;
            $del = $this->deleteData('repair_assign', $where);
            if ($del) {
                $sql = M()->getLastSql();
                $log['id']=$assignid;
                $text = getLogText('delRepairAssignLogText',$log);
                $this->addLog('repair_assign', $sql, $text, $assignid);
                $result['status'] = 1;
                $result['msg'] = '删除成功';
            } else {
                $result['status'] = -99;
                $result['msg'] = '删除失败';
            }
            return $result;
        } else {
            die(json_encode(array('status' => -1, 'msg' => '异常操作')));
        }
    }

    /**
     * 获取故障类型
     */
    public function getType()
    {
        $type = $this->DB_get_all('repair_setting','id,title',array('parentid'=>0),'','id asc','');
        return $type;
    }

    /**
     * 获取故障问题
     */
    public function getProblem($parentid)
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $problemSearch = I('post.typeSettingProblem');
        $where['parentid'] = $parentid;
        if ($problemSearch) {
            //故障问题搜索
            $where['title'] = array('LIKE', '%' . $problemSearch . '%');
        }
        //查询当前用户是否有权限进行修改故障问题
        $editProblem = get_menu('Repair', 'RepairSetting', 'editProblem');
        //查询当前用户是否有权限进行删除故障问题
        $deleteProblem = get_menu('Repair', 'RepairSetting', 'deleteProblem');
        $total = $this->DB_get_count('repair_setting', $where);
        $problem = $this->DB_get_all('repair_setting','',$where,'','id asc',$offset.",".$limit);
        foreach ($problem as $k => $v) {
            $html = '<div class="layui-btn-group">';
            if($editProblem){
                $html .= $this->returnListLink('<i class="layui-icon"></i>', $editProblem['actionurl'],'editProblem',C('BTN_CURRENCY') . ' layui-btn-warm');
            }
            if($deleteProblem){
                $html .= $this->returnListLink('<i class="layui-icon">&#xe640;</i>', $deleteProblem['actionurl'],'deleteProblem',C('BTN_CURRENCY') . ' layui-btn-danger');
            }
            $html .= '</div>';
            $problem[$k]['operation'] = $html;
        }
        $result["code"] = 200;
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["rows"] = $problem;
        if(!$result['rows']){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * 添加故障类型(后台数据)
     */
    public function addTypeData()
    {
        if(I('post.type')){
            $addTypeData['title'] = I('post.type');
        }else{
            die(json_encode(array('status' => -1, 'msg' => '故障类型不能为空')));
        }
        $addTypeData['parentid'] = 0;
        $addTypeData['adduser'] = session('username');
        $addTypeData['addtime'] = time();
        $result = $this->insertData('repair_setting',$addTypeData);
        return $result;
    }

    /**
     * 添加故障问题(后台数据)
     */
    public function addProblemData($parentid)
    {
        $repeat = $this->DB_get_all('repair_setting','title',array('parentid'=>$parentid));
        if(I('post.title')){
            $title = array_filter(explode(',',trim(I('post.title'))));
        }else{
            die(json_encode(array('status' => -1, 'msg' => '故障问题不能为空')));
        }
        if(I('post.solve')){
            $solve = array_filter(explode(',',trim(I('post.solve'))));
        }else{
            die(json_encode(array('status' => -1, 'msg' => '解决办法不能为空')));
        }
//        检查是否有重复的故障问题
        foreach($repeat as $k => $v){
            foreach($title as $k1 => $v1){
                if($v['title'] == $v1){
                    die(json_encode(array('status' => -1, 'msg' => '已存在相同名称的故障问题')));
                }
            }
        }
        $remark = array_filter(explode(',',trim(I('post.remark'))));
        $addProblemData['parentid'] = $parentid;
        $addProblemData['addtime'] = time();
        $addProblemData['adduser'] = session('username');
        $addall = array();
        foreach($addProblemData as $k => $v){
            foreach($title as $k1 => $v1){
                $addall[$k1]['title'] = $v1;
                $addall[$k1]['parentid'] = $parentid;
                $addall[$k1]['addtime'] = time();
                $addall[$k1]['adduser'] = session('username');
                $addall[$k1]['status'] = I('post.status');
            }
            foreach($solve as $k2 => $v2){
                $addall[$k2]['solve'] = $v2;
            }
            foreach($remark as $k3 => $v3){
                $addall[$k3]['remark'] = $v3;
            }
        }
        $result = $this->insertDataALL('repair_setting',$addall);
        //日志行为记录文字
        $lastSql = M()->getLastSql();
        $log['type']=trim(I('post.title'));
        $text = getLogText('addRepairProblemText',$log);
        $this->addLog('repair_setting',$lastSql,$text);
        return $result;
    }

    /**
     * 修改故障问题
     */
    public function editProblemData($id)
    {
        $oldName = $this->DB_get_one('repair_setting','title',array('id'=>$id));
        if(I('post.title')){
            $editProblemData['title'] = trim(I('post.title'));
        }else{
            die(json_encode(array('status' => -1, 'msg' => '故障问题不能为空')));
        }
        if(I('post.solve')){
            $editProblemData['solve'] = trim(I('post.solve'));
        }else{
            die(json_encode(array('status' => -1, 'msg' => '解决办法不能为空')));
        }
        $editProblemData['remark'] = I('post.remark');
        $editProblemData['edituser'] = session('username');
        $editProblemData['edittime'] = time();
        $editProblemData['status'] = I('post.status');
        $result = $this->updateData('repair_setting',$editProblemData,array('id'=>$id));
        //日志行为记录文字
        $lastSql = M()->getLastSql();
        $log['problem']=$oldName['title'];
        $text = getLogText('editRepairProblemText',$log);
        $this->addLog('repair_setting',$lastSql,$text,$result,'editProblem');
        return $result;
    }
}