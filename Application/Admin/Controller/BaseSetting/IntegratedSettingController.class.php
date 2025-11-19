<?php

namespace Admin\Controller\BaseSetting;

use Admin\Controller\NotCheckLogin\PublicController;
use Admin\Controller\Tool\ToolController;
use Admin\Controller\Login\CheckLoginController;
use Admin\Model\DepartmentModel;
use Admin\Model\IntegratedSettingModel;
use Admin\Model\CategoryModel;
use Admin\Model\UserModel;
use Admin\Model\OperationLogModel;
use Admin\Model\ModuleModel;
use Common\Weixin\Weixin;
use EasyWeChat\Core\Exceptions\HttpException;

class IntegratedSettingController extends CheckLoginController
{

    public function index()
    {
        $this->display();
    }

    //主设备分类设置
    public function category()
    {
        $cateModel = new CategoryModel();
        if (IS_POST) {
            $action = I('post.action');
            if ($action) {
                $hospital_id = session('current_hospitalid');
                $result = $cateModel->getCatetypes($hospital_id);
                $this->ajaxReturn($result);
            } else {
                $result = $cateModel->getSubCates();
                $this->ajaxReturn($result);
            }
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'getParentCategory':
                    //查数据库所有内容
                    $data = $cateModel->DB_get_all('category', '', array('parentid' => 0, 'is_delete' => C('NO_STATUS'), 'hospital_id' => session('current_hospitalid')), '', 'catid asc', '');
                    $this->ajaxReturn($data, 'json');
                    break;
                default:
                    //查数据库所有内容
                    $data = $cateModel->DB_get_all('category', '', array('parentid' => 0, 'is_delete' => C('NO_STATUS'), 'hospital_id' => session('current_hospitalid')), '', 'catid asc', '');
                    //转化json格式
                    $this->assign('cates', $data);
                    $this->assign('category', get_url());
                    $this->display();
                    break;
            }
        }
    }

    //添加主设备分类
    public function addCategory()
    {
        $cateModel = new CategoryModel();
        if (IS_POST) {
            $result = $cateModel->saveCate();
            $this->ajaxReturn($result);
        } else {
            $catid = I('GET.catid');
            $categoryinfo = $maxCatenum = $exitsCatenum = array();
            if ($catid) {
                //添加子类操作
                //获取父类信息
                $categoryinfo = $cateModel->DB_get_one('category', 'catid,catenum,category', array('catid' => $catid));
                //查询同一父分类下已有最大分类编号
                $maxCatenum = $cateModel->DB_get_one('category', 'max(catenum) as catenum', array('parentid' => $catid, 'hospital_id' => session('current_hospitalid'), 'is_delete' => 0));
                $maxCatenum['catenum'] = $maxCatenum['catenum'] ? $maxCatenum['catenum'] : $categoryinfo['catenum'] . '00';
                //查询已有分类编号
                $exitsCatenum = $cateModel->DB_get_one('category', 'group_concat(catenum ORDER BY catenum asc SEPARATOR "、") as catenum', array('is_delete' => 0, 'parentid' => $catid, 'hospital_id' => session('current_hospitalid')));
                $this->assign('parentid', $categoryinfo['catid']);
                $this->assign('fcatename', $categoryinfo['category']);
                $this->assign('fcatenum', $categoryinfo['catenum'] ? $categoryinfo['catenum'] : '');
                $this->assign('tips', '子');
                $this->assign('catenum', $maxCatenum['catenum'] + 1);
                $this->assign('exitsCatenum', $exitsCatenum['catenum'] ? $exitsCatenum['catenum'] : '无');
                $this->assign('catename', $categoryinfo['category']);
            } else {
                //查询同一父分类最大分类编号
                $maxCatenum = $cateModel->DB_get_one('category', 'max(catenum) as catenum', array('parentid' => 0, 'hospital_id' => session('current_hospitalid'), 'is_delete' => 0));
                //查询已有分类编号
                $exitsCatenum = $cateModel->DB_get_one('category', 'group_concat(catenum ORDER BY catenum asc SEPARATOR "、") as catenum', array('parentid' => 0, 'hospital_id' => session('current_hospitalid')));
                $this->assign('parentid', 0);
                $this->assign('fcatename', '无');
                $this->assign('fcatenum', '无');
                $this->assign('tips', '父');
                $this->assign('catenum', $maxCatenum['catenum'] + 1);
                $this->assign('exitsCatenum', $exitsCatenum['catenum'] ? $exitsCatenum['catenum'] : '无');
                $this->assign('catename', $categoryinfo['category']);
            }
            $this->display();
        }
    }

    //修改主分类
    public function editCategory()
    {
        //实例化模型
        $cateModel = new CategoryModel();
        if (IS_POST) {
            $result = $cateModel->editCate();
            $this->ajaxReturn($result);
        } else {
            $catid = I('GET.catid');
            $categoryinfo = $maxCatenum = $exitsCatenum = $fcategory = array();
            if ($catid) {
                //修改分类操作
                $categoryinfo = $cateModel->DB_get_one('category', 'catid,catenum,category,parentid,hospital_id,remark', array('catid' => $catid));
                if ($categoryinfo['parentid'] != 0) {
                    $fcategory = $cateModel->DB_get_one('category', 'catid,catenum,category,parentid', array('catid' => $categoryinfo['parentid'], 'hospital_id' => session('current_hospitalid')));
                }
                //查询已有分类编号
                $exitsCatenum = $cateModel->DB_get_one('category', 'group_concat(catenum ORDER BY catenum asc SEPARATOR "、") as catenum', array('parentid' => $categoryinfo['parentid'], 'is_delete' => 0, 'hospital_id' => session('current_hospitalid')));
                $this->assign('parentid', $categoryinfo['parentid']);
                $this->assign('fcatename', $fcategory['category'] ? $fcategory['category'] : '无');
                $this->assign('fcatenum', $fcategory['catenum'] ? $fcategory['catenum'] : '无');
                $this->assign('tips', $categoryinfo['parentid'] == 0 ? '父' : '子');
                $this->assign('catenum', $categoryinfo['catenum']);
                $this->assign('exitsCatenum', $exitsCatenum['catenum'] ? $exitsCatenum['catenum'] : '无');
                $this->assign('catename', $categoryinfo['category']);
                $this->assign('remark', $categoryinfo['remark']);
            } else {
                $this->error('参数错误！');
            }
            $this->assign('catid', $catid);
            $this->display();
        }
    }

    //删除主分类
    public function deleteCategory()
    {
        //实例化模型
        $categoryModel = new IntegratedSettingModel();
        //获取主分类的id
        $catid = I('POST.catid');
        if (!$catid) {
            $this->ajaxReturn(array('status' => -1, 'msg' => '参数错误！'));
        }
        $category = $categoryModel->DB_get_one('category', 'category', array('catid' => $catid));
        $res = $categoryModel->updateData('category', array('is_delete' => C('YES_STATUS')), 'catid=' . $catid . ' or ' . 'parentid=' . $catid);
        //日志行为记录文字
        $log['category'] = $category['category'];
        $text = getLogText('deleteCategoryLogText', $log);
        $categoryModel->addLog('category', M()->getLastSql(), $text, $catid, '');
        if ($res) {
            //更新分类缓存
            $categoryModel->updateCategory();
            //更新部门表和分类表中设备数量、总价等信息
            $this->updateAssetsNumAndTotalPrice();
            $this->ajaxReturn(array('status' => 1, 'msg' => '删除分类成功！'));
        } else {
            $this->ajaxReturn(array('status' => -1, 'msg' => '删除分类失败！'));
        }
    }

    /*
     * 获取科室列表
     */
    public function department()
    {
        $departmentModel = new DepartmentModel();
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'setApproveUser':
                    $departid = I('POST.departid');
                    $userid = I('POST.manager');
                    //查询该用户的管理科室
                    $udepart = $departmentModel->DB_get_one('user_department', 'group_concat(departid) as departids', array('userid' => $userid));
                    $managerDepartArr = explode(',', $udepart['departids']);
                    if (!in_array($departid, $managerDepartArr)) {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '该用户没有该科室的管理权限！'));
                    }
                    $name = $departmentModel->DB_get_one('user', 'username', array('userid' => $userid));
                    $manager = $name['username'];
                    //设置科室审批负责人
                    $departmentModel->updateData('department', array('manager' => $manager), array('departid' => $departid));
                    $this->ajaxReturn(array('status' => 1, 'msg' => '设置成功！'));
                    break;
                case 'getType':
                    $hospitalID = I('post.hospital_id');
                    $departName = $departmentModel->DB_get_all('department', '', array('parentid' => 0, 'is_delete' => C('NO_STATUS'), 'hospital_id' => array('in', $hospitalID)), '', 'departnum asc', '');
                    $this->ajaxReturn($departName, 'json');
                    break;
                default:
                    //获取部门数据
                    $result = $departmentModel->getDepartmentLists();

                    //var_dump($result);die;
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'setApproveUser':
                    $departid = I('GET.departid');
                    $departmanager = $departmentModel->DB_get_one('department', 'departid,hospital_id,department,manager', array('departid' => $departid));
                    //获取有科室管理权限的用户ID
                    $uids = $departmentModel->DB_get_one('user_department', 'group_concat(userid) as userids', array('departid' => $departid));
                    $users = array();
                    if ($uids['userids']) {
                        //获取系统该医院用户
                        $uwhere['status'] = C('YES_STATUS');
                        $uwhere['is_super'] = C('NO_STATUS');
                        $uwhere['is_delete'] = C('NO_STATUS');
                        $uwhere['job_hospitalid'] = $departmanager['hospital_id'];
                        $uwhere['userid'] = array('in', $uids['userids']);
                        $users = $departmentModel->DB_get_all('user', 'userid,username', $uwhere);
                    }
                    if (!$users) {
                        $this->assign('error_msg', '该科室暂未有管理人员，请先设置管理人员！');
                    }
                    $this->assign('users', $users);
                    $this->assign('departmanager', $departmanager);
                    $this->display('setApproveUser');
                    break;
                case 'changeSearch'://实时搜索功能
                    $department = trim(I('get.department'));
                    $where['parentid'] = 0;
                    $where['is_delete'] = C('NO_STATUS');
                    $where['hospital_id'] = session('current_hospitalid');
                    $where['department'] = ['like', '%' . $department . '%'];
                    $departName = $departmentModel->DB_get_all('department', 'departid,departnum,department', $where, '', 'departnum asc', '');
                    $this->ajaxReturn($departName, 'json');
                    break;
                default:
                    //查数据库所有内容
                    $where['is_delete'] = C('NO_STATUS');
                    $where['hospital_id'] = session('current_hospitalid');
                    $departName = $departmentModel->DB_get_all('department', 'departid,departnum,department', $where, '', 'departnum asc', '');

                    $this->assign('departName', $departName);
                    $this->assign('department', get_url());
                    $notCheck = new PublicController();
                    $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                    $this->display();
                    break;
            }
        }
    }

    /*
    * 添加科室
    */
    public function addDepartment()
    {
        if (IS_POST) {
            $action = I('post.action');
            if ($action == 'getUser') {
                $userModel = new UserModel();
                $userModel->getUser();
            } else {
                //实例化模型
                $departmentModel = new DepartmentModel();
                $parentid = I('post.parentid');
                //对接收的数据进行规则判断
                $this->checkstatus(judgeEmpty(I('post.address')), '所在位置不能为空');
                $add['address'] = I('post.address');
                $add['assetsrespon'] = I('post.assetsrespon');
                $this->checkstatus(judgeEmpty(I('post.department')), '科室名称不能为空');
                $add['department'] = I('post.department');
                $this->checkstatus(judgeNum(I('post.departnum')), '科室编号必须为数字');
                $add['departnum'] = I('post.departnum');
                $add['departrespon'] = I('post.departrespon');
                $this->checkstatus(judgeEmpty(I('post.departtel')), '科室电话不能为空');
                $add['departtel'] = I('post.departtel');
                //父ID
                $add['parentid'] = $parentid;
                //判断部门编码规则是否符合系统设置要求
                $checknumres = $departmentModel->checkDepartNum($add['departnum']);
                if ($checknumres['status'] == -1) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => $checknumres['msg']));
                }
                $add['hospital_id'] = session('current_hospitalid');
                //检查数据库是否有重复的科室名称
                $departnamecondition = $departmentModel->DB_get_one('department', 'department', ['department' => $add['department'], 'is_delete' => C('NO_STATUS'), 'hospital_id' => $add['hospital_id']]);
                //检查数据库是否有重复的科室编号
                $departnumcondition = $departmentModel->DB_get_one('department', 'departnum', ['departnum' => $add['departnum'], 'is_delete' => C('NO_STATUS'), 'hospital_id' => $add['hospital_id']]);
                if ($add['department'] == $departnamecondition['department']) {
                    $this->ajaxReturn(array('status' => 2, 'msg' => '已存在相同的科室名称'));
                } elseif ($add['departnum'] == $departnumcondition['departnum']) {
                    $this->ajaxReturn(array('status' => 2, 'msg' => '已存在相同的科室编号'));
                } else {
                    $result = $departmentModel->insertData('department', $add);
                    //日志行为记录文字
                    $log['department'] = $add['department'];
                    $text = getLogText('addDepartmentLogText', $log);
                    $departmentModel->addLog('department', M()->getLastSql(), $text, $result, '');
                    //入库判断
                    if ($result) {
                        //更新部门缓存
                        $interModel = new IntegratedSettingModel();
                        $interModel->updateDepartment();
                        $this->ajaxReturn(array('status' => 1, 'msg' => '添加部门成功！'));
                    } else {
                        $this->ajaxReturn(array('status' => 2, 'msg' => '添加部门失败！'));
                    }
                }
            }
        } else {
            //实例化模型
            $departmentModel = new DepartmentModel();
            $parentid = I('get.parentid');
            $action = I('get.action');
            switch ($action) {
                case 'getParentNum':
                    $hospitalID = session('current_hospitalid');
                    //查对应医院存在的科室编号
                    $existNum = $departmentModel->DB_get_one('department', 'group_concat(departnum SEPARATOR "、") AS departnum', ['parentid' => 0, 'hospital_id' => $hospitalID, 'is_delete' => C('NO_STATUS')]);
                    //查对应医院科室最大科室编号
                    $maxdepartNum = $departmentModel->DB_get_one('department', 'max(departnum) as departnum', ['hospital_id' => $hospitalID, 'parentid' => $parentid, 'is_delete' => C('NO_STATUS')]);
                    $maxNum = $maxdepartNum['departnum'] ? $maxdepartNum['departnum'] + 1 : C('DEPART_PREFIX_NUM');
                    $this->ajaxReturn(['existNum' => $existNum['departnum'], 'maxNum' => $maxNum], 'json');
                    break;
                default:
                    $where['status'] = C('YES_STATUS');
                    $where['is_delete'] = C('NO_STATUS');
                    $where['is_super'] = 0;
                    $where['job_hospitalid'] = session('current_hospitalid');
                    $user = $departmentModel->DB_get_all('user', 'username', $where, '', 'userid asc', '');
                    $this->assign('user', $user);
                    $this->assign('job_hospitalid', session('job_hospitalid'));
                    //查科室编号
                    $departNum = $departmentModel->DB_get_one('department', 'group_concat(departnum ORDER BY departnum asc SEPARATOR "、") as departnum', ['parentid' => $parentid, 'is_delete' => C('NO_STATUS'), 'hospital_id' => session('current_hospitalid')]);
                    //查最大科室编号
                    $tips = '主';
                    $maxdepartNum = [];
                    if ($parentid != 0) {
                        //查主科室编号
                        $parentDepartment = $departmentModel->DB_get_one('department', 'departnum,department,hospital_id', ['departid' => $parentid, 'hospital_id' => session('current_hospitalid'), 'is_delete' => C('NO_STATUS')]);
                        //最大一条的编号
                        $maxdepartNum = $departmentModel->DB_get_one('department', 'max(departnum) as departnum', ['parentid' => $parentid, 'is_delete' => C('NO_STATUS'), 'hospital_id' => session('current_hospitalid')]);
                        $this->assign('parentDepartment', $parentDepartment);
                        $maxdepartNum = $maxdepartNum['departnum'] ? $maxdepartNum['departnum'] + 1 : $parentDepartment['departnum'] . '00';
                        $hospitalName = $departmentModel->DB_get_one('hospital', 'hospital_name', ['hospital_id' => $parentDepartment['hospital_id']]);
                        $this->assign('hospitalName', $hospitalName['hospital_name']);
                        $tips = '子';
                    }
                    $this->assign('tips', $tips);
                    $this->assign('maxdepartNum', $maxdepartNum);
                    $this->assign('departNum', $departNum['departnum'] ? $departNum['departnum'] : '暂无');
                    $this->assign('parentid', $parentid);
                    $this->display();
                    break;
            }
        }
    }

    /*
    * 修改科室
    */
    public function editDepartment()
    {
        if (IS_POST) {
            //实例化模型
            $departmentModel = new DepartmentModel();
            //对接收的数据进行规则判断
            $departid = I('post.departid');
            $this->checkstatus(judgeEmpty(I('post.address')), '所在位置不能为空');
            $edit['address'] = I('post.address');
            $edit['assetsrespon'] = I('post.assetsrespon');
            $this->checkstatus(judgeEmpty(I('post.department')), '科室名称不能为空');
            $edit['department'] = I('post.department');
            $this->checkstatus(judgeNum(I('post.departnum')), '科室编号必须为数字');
            $edit['departnum'] = I('post.departnum');
            $edit['departrespon'] = I('post.departrespon');
            $this->checkstatus(judgeEmpty(I('post.departtel')), '科室电话不能为空');
            $edit['departtel'] = I('post.departtel');
            $edit['edittime'] = time();

            //判断部门编码规则是否符合系统设置要求
            $checknumres = $departmentModel->checkDepartNum($edit['departnum']);
            if ($checknumres['status'] == -1) {
                $this->ajaxReturn(array('status' => -1, 'msg' => $checknumres['msg']));
            }
            //查询是否有主设备数据，有则不能修改科室编号
            $ass = $departmentModel->DB_get_one('assets_info', 'assid', array('is_delete' => 0, 'hospital_id' => session('current_hospitalid')));
            //当前科室医院ID
            $editnameHospitalId = $departmentModel->DB_get_one('department', 'hospital_id,departnum', ['departid' => $departid]);
            if ($ass) {
                //有设备数据，不允许更改编号
                if ($edit['departnum'] != $editnameHospitalId['departnum']) {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '系统已有设备数据，不允许更科室编号！'));
                }
            }
            //检查数据库是否有重复的科室名称
            $editnamecondition = $departmentModel->DB_get_one('department', 'departid', array('is_delete' => 0, 'department' => $edit['department'], 'departid' => array('neq', $departid), 'hospital_id' => $editnameHospitalId['hospital_id']));
            //检查数据库是否有重复的科室编号
            $editnumcondition = $departmentModel->DB_get_one('department', 'department,departid', array('is_delete' => 0, 'departnum' => $edit['departnum'], 'departid' => array('neq', $departid), 'hospital_id' => $editnameHospitalId['hospital_id']));
            if ($editnamecondition['departid']) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '已存在该科室名称！'));
            } elseif ($editnumcondition['departid']) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '已存在该科室编号！'));
            } else {
                $name = $departmentModel->DB_get_one('department', 'department,hospital_id', array('departid' => $departid));
                $departmentModel->updateData('department', $edit, array('departid' => $departid));
                //日志行为记录文字
                $log['department'] = $name['department'];
                $text = getLogText('editDepartmentLogText', $log);
                $departmentModel->addLog('department', M()->getLastSql(), $text, $departid, '');
                //更新部门缓存
                $interModel = new IntegratedSettingModel();
                $interModel->updateDepartment();
                $this->ajaxReturn(array('status' => 1, 'msg' => '修改成功'));
            }
        } else {
            $departid = I('get.departid');
            //实例化模型
            $departmentModel = new DepartmentModel();
            //查询不是post方式时候对应的数据
            $departmentinfo = $departmentModel->DB_get_one('department', '', array('departid' => $departid));
            $where['status'] = C('YES_STATUS');
            $where['is_delete'] = C('NO_STATUS');
            $where['is_super'] = 0;
            $where['job_hospitalid'] = $departmentinfo['hospital_id'];
            //获取有该科室管理权限的用户ID
            $uds = $departmentModel->DB_get_one('user_department', 'group_concat(userid) as userids', array('departid' => $departid));
            if (!$uds['userids']) {
                $user = [];
            } else {
                $where['userid'] = array('in', $uds['userids']);
                $user = $departmentModel->DB_get_all('user', 'username', $where, '', 'userid asc', '');
            }
            $this->assign('user', $user);
            $this->assign('departmentinfo', $departmentinfo);
            //查是否管理员 以及是否开启分院功能
            if (session('isSuper') == 1 and C('IS_OPEN_BRANCH') == true) {
                //显示分院
                $showHospital = 1;
                $hosInfo = $departmentModel->DB_get_one('hospital', 'hospital_id,hospital_name', array('hospital_id' => $departmentinfo['hospital_id'], 'is_delete' => C('NO_STATUS')));
                $this->assign('showHospital', $showHospital);
                $this->assign('hosInfo', $hosInfo);
            }
            //查科室编号
            $departNum = $departmentModel->DB_get_one('department', 'group_concat(departnum ORDER BY departnum asc SEPARATOR "、") as departnum', ['parentid' => $departmentinfo['parentid'], 'is_delete' => C('NO_STATUS'), 'hospital_id' => $departmentinfo['hospital_id']]);
            if ($departmentinfo['parentid'] == 0) {
                $tips = '主';
            } else {
                //查主科室编号
                $parentDepartment = $departmentModel->DB_get_one('department', 'departnum,department', ['departid' => $departmentinfo['parentid'], 'is_delete' => C('NO_STATUS')]);
                $this->assign('parentDepartment', $parentDepartment);
                $tips = '子';
            }
            //查询是否有主设备数据，有则不能修改科室编号
            $ass = $departmentModel->DB_get_one('assets_info', 'assid', array('is_delete' => 0, 'hospital_id' => session('current_hospitalid')));
            if ($ass) {
                $this->assign('canEdit', 0);
            } else {
                $this->assign('canEdit', 1);
            }
            $this->assign('tips', $tips);
            $this->assign('tips', $tips);
            $this->assign('parentid', $departmentinfo['parentid']);
            $this->assign('departNum', $departNum['departnum']);
            $this->display();
        }
    }

    /*
    * 删除科室
    */
    public function deleteDepartment()
    {
        $departmentModel = new DepartmentModel();
        if (IS_POST) {
            $departid = I('POST.departid');
            //实例化模型
            $all = I('post.all');
            if ($all) {
                //如果是删除主科室 子科室一起删
                $res = $departmentModel->updateData('department', array('is_delete' => C('YES_STATUS')), array('departid' => $departid));
                //删其下子科室
                $departmentModel->updateData('department', array('is_delete' => C('YES_STATUS')), array('parentid' => $departid));
            } else {
                //只删除子科室
                $res = $departmentModel->updateData('department', array('is_delete' => C('YES_STATUS')), array('departid' => $departid));
            }
            $department = $departmentModel->DB_get_one('department', 'department', array('departid' => $departid));
            //日志行为记录文字
            $log['department'] = $department['department'];
            $text = getLogText('deleteDepartmentLogText', $log);
            $departmentModel->addLog('department', M()->getLastSql(), $text, $departid, '');
            if ($res) {
                //更新部门缓存
                $interModel = new IntegratedSettingModel();
                $interModel->updateDepartment();
                //更新部门表和分类表中设备数量、总价等信息
                $this->updateAssetsNumAndTotalPrice();
                $this->ajaxReturn(array('status' => 1, 'msg' => '删除部门成功！'));
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '删除部门失败！'));
            }
        }
    }

    /**
     * 功能：系统基础参数设置
     * 作用：修改参数设置，等同修改Home的config配置
     * 时间：2017-5-16
     */
    public function system()
    {
        //$path = 'Application/Home/Conf/system.config.php';//保存文件的路径
        $path = 'Application/Common/Conf/wechat.php';//保存文件的路径
        $moduleModel = new ModuleModel();
        $wx_status = $moduleModel->decide_wx_login();
        if (!$wx_status) {
            if (IS_POST) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '微信端已停用，暂不能对微信配置进行修改！'));
            } else {
                $this->assign('errmsg', '微信端已停用，请在模块配置中开启后再设置！');
                $this->display('Public/error');
            }
            return;
        }
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'code_pic':
                    //上传设备图片
                    $moduleModel = new ModuleModel();
                    $wx_status = $moduleModel->decide_wx_login();
                    if (!$wx_status) {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '微信登陆已经关闭，请先开启微信登陆'));
                    }
                    $Tool = new ToolController();
                    //设置文件类型
                    $type = array('jpg', 'png', 'bmp', 'jpeg', 'gif');
                    //微信头像目录设置
                    $dirName = 'WeChat';
                    //上传文件
                    $upload = $Tool->upFile($type, $dirName);
                    $this->ajaxReturn(array('status' => 1, 'msg' => '图片上传成功', 'data' => $upload));
                    break;

                case 'get_templates':
                    // 获取模板
                    $data = [
                        'WX_APPID'         => trim(I('post.WX_APPID')),
                        'WX_SECRET'        => trim(I('post.WX_SECRET')),
                    ];

                    try {
                        $templates = Weixin::instance([
                            'app_id' => $data['WX_APPID'],
                            'secret' => $data['WX_SECRET'],
                        ])->getTemplates();

                        $this->ajaxReturn(['status' => 1, 'data' => $templates]);

                    } catch (HttpException $e) {
                        $this->ajaxReturn(['status' => -1, 'msg' => $e->getMessage()]);
                    }
                    break;

                default:
                    $data = [
                        'WX_APPID'         => trim(I('post.WX_APPID')),
                        'WX_SECRET'        => trim(I('post.WX_SECRET')),
                        'WX_WORK_APPID'    => trim(I('post.WX_WORK_APPID')),
                        'WX_WORK_SECRET'   => trim(I('post.WX_WORK_SECRET')),
                        'WX_WORK_AGENT_ID' => trim(I('post.WX_WORK_AGENT_ID')),
                        'WX_LOGO'          => trim(I('post.WX_LOGO')),
                    ];

                    $templatesText = I('post.WX_TEMPLATES');
                    $templatesText = str_replace('：', ':', $templatesText);
                    $templatesText = str_replace("\r\n", "\n", $templatesText);

                    $templates = [];

                    foreach (explode("\n", $templatesText) as $templateText) {
                        if (empty($templateText)) {
                            continue;
                        }

                        list($name, $id) = explode(':', $templateText);

                        $name = trim($name);
                        $id = trim($id);

                        if ($name) {
                            $templates[$name] = $id;
                        }
                    }

                    $data['WX_TEMPLATES'] = $templates;

                    //获取上一个头像地址并删除
                    $configOld = include $path;

                    if ($configOld && $configOld['WX_LOGO'] != $data['WX_LOGO']) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . $configOld['WX_LOGO']);
                    }

                    $configCode = var_export($data, true);
                    $content = <<<EOF
<?php
/**
 * 本文件由脚本生成，请不要手动修改
 *
 * @see \Admin\Controller\BaseSetting\IntegratedSettingController::system
 */

return {$configCode};

EOF;

                    if (file_put_contents($path, $content)) {
                        $this->success(C('SYSTEM_SUCCESS'));
                    } else {
                        $this->error(C('SYSTEM_ERROR'));
                    }
                    break;
            }
        } else {

            $config = include $path;

            if ($config && $config['WX_TEMPLATES']) {
                $config['WX_TEMPLATES_TEXT'] = '';

                foreach ($config['WX_TEMPLATES'] as $name => $id) {
                    $config['WX_TEMPLATES_TEXT'] .= "{$name}：{$id}\n";
                }
            }

            $this->assign('system', $config);

            $this->display();
        }
    }

    /*
     * 批量添加科室
     */
    public function batchAddDepartment()
    {
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'save':
                    $departModel = new DepartmentModel();
                    $result = $departModel->batchAddDeparts();
                    //更新部门缓存
                    $interModel = new IntegratedSettingModel();
                    $interModel->updateDepartment();
                    //更新部门表和分类表中设备数量、总价等信息
                    $this->updateAssetsNumAndTotalPrice();

                    //日志行为记录文字
                    $text = getLogText('batchAddDepartmentLogText');
                    $departModel->addLog('department', '', $text, '', '');
                    $this->ajaxReturn($result);
                    break;
                case 'getData':
                    $departModel = new DepartmentModel();
                    //获取待入库设备
                    $result = $departModel->getWatingUploadDeparts();
                    $this->ajaxReturn($result);
                    break;
                case 'updateData':
                    //更新临时表数据库
                    $departModel = new DepartmentModel();
                    $result = $departModel->updateTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'delTmpDeparts':
                    //删除临时表数据库
                    $departModel = new DepartmentModel();
                    $result = $departModel->delTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'upload':
                    //接收上传文件数据
                    $departModel = new DepartmentModel();
                    $result = $departModel->uploadData();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $this->ajaxReturn(array('status' => -1, 'msg' => '空操作！'));
                    break;
            }
        } else {
            $departModel = new DepartmentModel();
            //查询医院配置
            $hoinfo = $departModel->DB_get_one('hospital', 'hospital_id', array('hospital_id' => session('current_hospitalid'), 'is_delete' => 0));
            if (!$hoinfo) {
                $this->assign('jumpUrl', '');
                $this->assign('errmsg', '请先配置医院信息！');
                $this->display('Public/error');
                exit;
            }
            $type = I('GET.type');
            if ($type == 'exploreDepartModel') {
                //导出模板
                $xlsName = "department";
                $xlsCell = array('医院代码', '科室编号', '所属上级科室编号(没有则不填写)', '科室名称','所在位置', '科室负责人', '设备负责人', '科室电话');
                //单元格宽度设置
                $width = array(
                    '医院代码' => '15',//字符数长度
                    '科室编号' => '15',//字符数长度
                    '所属上级科室编号(没有则不填写)' => '35',
                    '科室名称' => '25',
                    '所在位置' => '30',
                    '科室负责人' => '25',
                    '设备负责人' => '25',
                    '科室电话' => '25'
                );
                //单元格颜色设置（例如必填行单元格字体颜色为红色）
                $color = array(
                    '医院代码' => 'FF0000',//颜色代码
                    '科室编号' => 'FF0000',//颜色代码
                    '科室名称' => 'FF0000',
                    '所在位置' => 'FF0000',
//                    '科室负责人' => 'FF0000',
//                    '设备负责人' => 'FF0000',
//                    '科室电话' => 'FF0000'
                );
                Excel('科室导入模板', $xlsName, $xlsCell, $width, $color);
            }
            //查询数据库已有部门信息
            $departments = $departModel->DB_get_all('department', 'departnum,department', '', '', '');
            $this->assign('departments', json_encode($departments, JSON_UNESCAPED_UNICODE));
            $this->assign('batchAddDepartment', get_url());
            $this->display();
        }
    }

    /*
     * 批量添加分类
     */
    public function batchAddCategory()
    {
        if (IS_POST) {
            $categoryModel = new CategoryModel();
            $type = I('POST.type');
            switch ($type) {
                case 'save':
                    $result = $categoryModel->addCategory();
                    if ($result['status'] == 1) {
                        $interModel = new IntegratedSettingModel();
                        //更新分类缓存
                        $interModel->updateCategory();
                        //更新部门表和分类表中设备数量、总价等信息
                        $this->updateAssetsNumAndTotalPrice();
                        //日志行为记录文字
                        $text = getLogText('batchAddCategoryLogText');
                        $categoryModel->addLog('category', '', $text, '', '');
                    }
                    $this->ajaxReturn($result);
                    break;
                case 'getData':
                    $result = $categoryModel->getTmpCates();
                    $this->ajaxReturn($result);
                    break;
                case 'updateData':
                    //更新临时表数据库
                    $result = $categoryModel->updateTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'delTmpData':
                    //删除临时表数据库
                    $result = $categoryModel->delTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'upload':
                    //接收上传文件数据
                    $result = $categoryModel->uploadData();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $this->ajaxReturn(array('status' => -1, 'msg' => '空操作！'));
                    break;
            }
        } else {
            $categoryModel = new CategoryModel();
            //查询医院配置
            $hoinfo = $categoryModel->DB_get_one('hospital', 'hospital_id', array('hospital_id' => session('current_hospitalid'), 'is_delete' => 0));
            if (!$hoinfo) {
                $this->assign('jumpUrl', '');
                $this->assign('errmsg', '请先配置医院信息！');
                $this->display('Public/error');
                exit;
            }
            $type = I('GET.type');
            if ($type == 'exploreCatesModel') {
                //导出模板
                $xlsName = "category";
                $xlsCell = array('医院代码', '分类编号', '分类名称', '品名举例');
                //单元格宽度设置
                $width = array(
                    '医院代码' => '20',//字符数长度
                    '分类编号' => '20',//字符数长度
                    '分类名称' => '20',//字符数长度
                    '品名举例' => '100'//字符数长度
                );
                //单元格颜色设置（例如必填行单元格字体颜色为红色）
                $color = array(
                    '医院代码' => 'FF0000',//颜色代码
                    '分类编号' => 'FF0000',//颜色代码
                    '分类名称' => 'FF0000',//颜色代码
                );
                Excel('分类导入模板', $xlsName, $xlsCell, $width, $color);
            }
            //查询数据库已有分类信息

            $categorys = $categoryModel->DB_get_all('category', 'category', array('parentid' => 0), '', '');
            $this->assign('categorys', json_encode($categorys, JSON_UNESCAPED_UNICODE));
            $this->assign('batchAddCategory', get_url());
            $this->display();
        }
    }

    /*
    * 系统操作日志
    */
    public function operationLog()
    {
        $LogModel = new OperationLogModel();
        if (IS_POST) {
            $action = I('post.action');
            if ($action == 'getActionName') {
                //联动获取的事件select框
                $moduleName = I('post.module');
                if (!$moduleName) {
                    $this->ajaxReturn(array());
                }
                $menuID = $LogModel->DB_get_one('menu', 'menuid', array('name' => $moduleName));
                $parentID = $LogModel->DB_get_one('menu', 'group_concat(menuid) AS parentid', array('parentid' => $menuID['menuid']), '');
                $action = $LogModel->DB_get_all('menu', 'name,title', array('parentid' => array('IN', $parentID['parentid']), 'leftShow' => 0), '', '', '');
                $this->ajaxReturn($action);
            } else {
                $result = $LogModel->operationListData();
                $this->ajaxReturn($result);
            }
        } else {
            $hospital_id = session('current_hospitalid');
            //select框的用户名
            $username = $LogModel->DB_get_all('user', 'userid,username', array('job_hospitalid' => $hospital_id, 'is_delete' => C('NO_STATUS'), 'is_super' => 0));
            $module = $LogModel->DB_get_all('menu', 'menuid,name,title', array('parentid' => 0, 'status' => 1), '', 'menuid asc', '');
            $this->assign('username', $username);
            $this->assign('module', $module);
            $this->assign('operationLog', get_url());
            $this->display();
        }
    }

    /*
     * 获取科室标签列表
     */
    public function departLabel()
    {
        $departmentModel = new DepartmentModel();
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'batchPrint':
                    $departid = I('post.departid');
                    $departid = trim($departid, ',');

                    $departInfo = $departmentModel->DB_get_all('department','departid,department,departnum', array('departid' => array('in', $departid)));
                    if(C('USE_FEISHU')){
                        $code_str = C('APP_NAME').C('FS_NAME').'/Notin/depart_sign';
                    }else{
                        if(C('USE_VUE_WECHAT_VERSION')){
                            $code_str = C('APP_NAME').C('VUE_NAME').'/Notin/depart_sign';
                        }else{
                            $code_str = C('APP_NAME').C('MOBILE_NAME').'/Notin/depart_sign';
                        }
                    }
                    foreach ($departInfo as $k => $v) {
                        $labels = $departmentModel->DB_get_all('department_label','*',array('depart_id'=>$v['departid']));
                        if(!$labels){
                            //不存在，生成4个标签
                            for($i=0;$i<4;$i++){
                                $file_name = $v['departnum'].'_00'.($i+1);
                                $code_str .= '?id='.$v['departid'].'&name='.$file_name;
                                $codeUrl = $departmentModel->createLabelCode($file_name,$code_str);
                                $indata['depart_id'] = $v['departid'];
                                $indata['label_name'] = $file_name;
                                $indata['label_url'] = $codeUrl;
                                $indata['add_userid'] = session('userid');
                                $indata['add_time'] = date('Y-m-d H:i:s');
                                $departmentModel->insertData('department_label',$indata);
                                $labels[$i] = $indata;
                            }
                        }else{
                            foreach ($labels as $lak=>$lav){
                                $fileExists = file_exists('.' . $lav['label_url']);
                                if (!$fileExists) {
                                    //文件已不存在，重新生成二维码文件
                                    $file_name = $v['departnum'].'_00'.($lak+1);
                                    $code_str .= '?id='.$v['departid'].'&name='.$file_name;;
                                    $codeUrl = $departmentModel->createLabelCode($file_name,$code_str);
                                    if ($codeUrl) {
                                        //保存二维码图片地址到数据库
                                        $codeUrl = trim($codeUrl, '.');
                                        $departmentModel->updateData('department_label', array('label_url' => $codeUrl,'label_name'=>$file_name), array('label_id' => $lav['label_id']));
                                        $labels[$lak] = $codeUrl;
                                    }
                                }
                            }
                        }
                        foreach ($labels as &$dl){
                            $la = explode('/',$dl['label_url']);
                            $las = $la[count($la)-1];
                            $ex = substr($las,0,strpos($las, '.'));
                            if($v['departid'] == $dl['depart_id']){
                                $dl['department'] = $v['department'].'('.$ex.')';
                            }
                        }
                        $departInfo[$k]['label'] = $labels;
                    }
                    $html = '';
                    foreach ($departInfo as $k => $v) {
                        $this->assign('departInfo', $departInfo[$k]);
                        $html .= $this->display('print_department_label');
                    }
                    break;
                default:
                    //获取部门数据
                    $result = $departmentModel->getDepartmentLists();
                    $departLabel = get_menu('BaseSetting', 'IntegratedSetting', 'print');
                    foreach ($result['rows'] as &$v) {
                        unset($v['depart_operation']);
                        $v['is_save'] = 0;
                        $v['label_1'] = $v['departnum'].'_001';
                        $v['label_2'] = $v['departnum'].'_002';
                        $v['label_3'] = $v['departnum'].'_003';
                        $v['label_4'] = $v['departnum'].'_004';
                        $dlabel = $departmentModel->DB_get_all('department_label', '', ['depart_id' => $v['departid']]);
                        if($dlabel){
                            $v['is_save'] = 1;
                            $html = '<div class="layui-btn-group">';
                            $html .= $this->returnButtonLink('重新打印',$departLabel['actionurl'],'layui-btn layui-btn-xs layui-btn-warm','','lay-event = print');
                            $html .= '</div>';
                        }else{
                            $html = '<div class="layui-btn-group">';
                            $html .= $this->returnButtonLink('打印',$departLabel['actionurl'],'layui-btn layui-btn-xs layui-btn-normal','','lay-event = print');
                            $html .= '</div>';
                        }
                        $v['depart_operation'] = $html;
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            //查数据库所有内容
            $where['is_delete'] = C('NO_STATUS');
            $where['hospital_id'] = session('current_hospitalid');
            $departName = $departmentModel->DB_get_all('department', 'departid,departnum,department', $where, '', 'departnum asc', '');

            $this->assign('departName', $departName);
            $this->assign('departLabel', get_url());
            $notCheck = new PublicController();
            $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
            $this->display();
        }
    }
}
