<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/3
 * Time: 14:59
 */

namespace Admin\Controller\BaseSetting;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\ApproveProcessModel;
use Admin\Model\RoleModel;

class ApproveSettingController extends CheckLoginController
{
    private $MODULE = 'BaseSetting';

    public function approveLists()
    {
        if(IS_GET){
            $approveModel = new ApproveProcessModel();
            $hospital_id = session('current_hospitalid');
            $types = $approveModel->getProcess($hospital_id);
            if(!$types){
                //未设置审批类型，插入审批类型
                $typeData[0]['hospital_id'] = $hospital_id;
                $typeData[0]['approve_type'] = C('REPAIR_APPROVE');
                $typeData[0]['type_name'] = '维修审批';
                $typeData[0]['status'] = '1';

                $typeData[1]['hospital_id'] = $hospital_id;
                $typeData[1]['approve_type'] = C('TRANSFER_APPROVE');
                $typeData[1]['type_name'] = '转科审批';
                $typeData[1]['status'] = '1';

                $typeData[2]['hospital_id'] = $hospital_id;
                $typeData[2]['approve_type'] = C('SCRAP_APPROVE');
                $typeData[2]['type_name'] = '报废审批';
                $typeData[2]['status'] = '1';

                $typeData[3]['hospital_id'] = $hospital_id;
                $typeData[3]['approve_type'] = C('OUTSIDE_APPROVE');
                $typeData[3]['type_name'] = '外调审批';
                $typeData[3]['status'] = '1';

                $typeData[4]['hospital_id'] = $hospital_id;
                $typeData[4]['approve_type'] = C('PURCHASES_PLANS_APPROVE');
                $typeData[4]['type_name'] = '采购计划审批';
                $typeData[4]['status'] = '1';

                $typeData[5]['hospital_id'] = $hospital_id;
                $typeData[5]['approve_type'] = C('DEPART_APPLY_APPROVE');
                $typeData[5]['type_name'] = '科室计划审批';
                $typeData[5]['status'] = '1';

                $typeData[6]['hospital_id'] = $hospital_id;
                $typeData[6]['approve_type'] = C('SUBSIDIARY_APPROVE');
                $typeData[6]['type_name'] = '附属设备分配审批';
                $typeData[6]['status'] = '1';

                $typeData[7]['hospital_id'] = $hospital_id;
                $typeData[7]['approve_type'] = C('PATROL_APPROVE');
                $typeData[7]['type_name'] = '巡查保养审批';
                $typeData[7]['status'] = '1';

                $typeData[8]['hospital_id'] = $hospital_id;
                $typeData[8]['approve_type'] = C('INVENTORY_PLAN_APPROVE');
                $typeData[8]['type_name'] = '盘点审批';
                $typeData[8]['status'] = '1';

                $approveModel->insertDataALL('approve_type',$typeData);
                $types = $approveModel->getProcess($hospital_id);
            }
            //获取所有角色
            $roles = $approveModel->DB_get_all('role','roleid,role',array('hospital_id'=>$hospital_id,'is_default'=>0,'is_delete'=>0));
            //获取所有角色用户
            $users = array();
            foreach ($roles as $k=>$v){
                $users[$k]['rolename'] = $v['role'];
                $join = "LEFT JOIN sb_user_role AS B ON A.userid = B.userid";
                $where['B.roleid'] = $v['roleid'];
                $where['A.is_delete'] = C('NO_STATUS');
                $where['A.status'] = C('YES_STATUS');
                $where['A.job_hospitalid'] = $hospital_id;
                $roleuser = $approveModel->DB_get_all_join('user','A','A.userid,A.username,B.roleid',$join,$where,'','','');
                $users[$k]['users'] = $roleuser;
            }
            $this->assign('types',$types);
            $this->assign('roles',$roles);
            $this->assign('role_users',$users);
            $this->assign('hospital_id',$hospital_id);
            $this->assign('approveLists',get_url());
            if(I('get.action') == 'getType'){
                echo $this->display('hospital_approve');
            }else{
                $this->display();
            }
        }
    }

    /*
     * 添加流程
     */
    public function addProcess()
    {
        if(IS_POST){
            $apModel = new ApproveProcessModel();
            $action = I('POST.action');
            if($action == 'offon'){
                //开启、关闭审核功能
                $result = $apModel->updateApproveStatus();
                $this->ajaxReturn($result);
            }else{
                //新增、修改流程
                $result = $apModel->addProcess();
                $this->ajaxReturn($result);
            }
        }
    }
}
