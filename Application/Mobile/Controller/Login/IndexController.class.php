<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/1/24
 * Time: 13:47
 */

namespace Mobile\Controller\Login;

use Admin\Model\ApproveProcessModel;
use Admin\Model\MenuModel;
use Mobile\Model\WxAccessTokenModel;
use Mobile\Model\UserModel;
use Think\Controller;
use Admin\Model\ModuleModel;
class IndexController extends Controller
{
    protected $login_url = 'Login/login.html';//登录地址
    protected $index_url = 'Index/testindex.html';//首页地址

    public function __construct()
    {
        //调用父类构造
        parent::__construct();
            /*判断是否开启微信端*/
            $moduleModel = new ModuleModel();
            $wx_status=$moduleModel->decide_wx_login();
            if (!$wx_status) {
                session(null);
                $this->assign('tips', '微信端使用已被关闭');
                $this->assign('btn', '');
                $this->assign('url', '');
                $this->display('Pub/Notin/fail');
                exit;
            }
            $token = $_SERVER['HTTP_AUTHORIZATION'];
            if ($token) {
                $UserModel = new UserModel();
                $user=$UserModel->DB_get_one('user', '*', array('userid' => $token));
                $res = $UserModel->setSession($user);
            }
        if (!$_SESSION['userid']) {
            session('pre_url',$_SERVER['REQUEST_URI']);
            redirect(U("M/Notin/getUserOpenId"));
        } else {
            //G('begin');
            $userid = $_SESSION['userid'];
            $isSuper = $_SESSION['isSuper'];
            $mc = explode('/', CONTROLLER_NAME);
            $self = explode('/', trim(get_url(), '/'));
            $loginUrl = '/' . $self[0] . '/Login/login';
            $model = new UserModel();
            //判断账户是否已过期
            $expire_time = $model->DB_get_one('user', 'expire_time', array('userid' => $userid));
            if ($expire_time['expire_time'] && time() >= strtotime($expire_time['expire_time'])) {
                session(null);
                $this->assign('tips', '您的账户已过期，如需继续使用，请联系管理员设置！');
                $this->assign('btn', '返回登录页面');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->login_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            if (!in_array($mc[0], array('Login'))) {
                //查询模块是否已关闭
                $module_status = $model->DB_get_one('menu', 'menuid,status', $where = array('name' => $mc[0]));
                if ($module_status['status'] != 1) {
                    session(null);
                    $this->assign('tips', '该模块已关闭，请重新登录系统！');
                    $this->assign('btn', '返回登录页面');
                    $this->assign('url', C('MOBILE_NAME').'/'.$this->login_url);
                    $this->display('Pub/Notin/fail');
                    exit;
                }
            }
            if ($isSuper) {
                //超级管理员，不做权限判断
                return true;
            }
            //不做权限验证的方法列表
            $noCheck = array(
                'Login' => array(
                    'Index' => array(
                        'index',//框架加载js css文件
                        'testindex',//框架加载js css文件
                        'setSession',//框架加载js css文件
                    ),
                ),
                'Qualities' => array(
                    'Quality' => array(
                        'showDetail',
                    )
                ),
                'Assets' => array(
                    'Assets' => array(
                        'index'//尚不明确
                    ),
                    'Transfer' => array(
                        'prin'//尚不明确
                    ),
                    'Lookup' => array(
                        'index',
                        'showAssets',//显示设备详情
                        'assetsLifeList',//获取菜单
                        'cate',//获取菜单
                        'departs',//获取菜单
                    )
                ),
                'Repair' => array(
                    'Repair' => array(
                        'index',//尚不明确
                        'uploadFile',//上传维修文件
                        'showRepairDetails'//上传维修文件
                    ),
                    'RepairSearch' => array(
                        'getRepairSearchList',//维修记录列表
                        'showUpload',//显示上传文件
                        'showRepair',//维修单详情
                    )
                ),
                'Patrol' => array(
                    'Patrol' => array(
                        'index',//尚不明确
                        'allocationPlan',//确认计划
                        'allNext',//确认整个计划
                    )
                ),
                'Archives' => array(
                    'Emergency' => array(
                        'showEmergencyPlan',//应急档案列表
                        'showFile',//预览
                    ),
                    'Box' => array(
                        'showFile',//预览
                    )
                ),
                'BaseSetting' => array(
                    'Notice' => array(
                        'showFile',//预览
                    )
                ),

            );
            if (in_array(ACTION_NAME, $noCheck[$mc[0]][$mc[1]])) {
                return true;
            }
             
            //获取Controller id
            $controllerId = $model->DB_get_one('menu', 'GROUP_CONCAT(menuid) AS menuid', $where = array('name' => $mc[1], 'status' => 1));
            $menuWhere['name'] = array('EQ', ACTION_NAME);
            $menuWhere['status'] = array('EQ', 1);
            $menuWhere['parentid'] = array('IN', $controllerId['menuid']);
            //获取当前要访问的url的menuid
            $menuid = $model->DB_get_one('menu', 'menuid', $menuWhere);
            if (!$menuid['menuid']) {
                $this->assign('tips', '该方法不存在！');
                $this->assign('btn', '返回首页');
                $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                $this->display('Pub/Notin/fail');
                exit;
            }
            if (session('is_supplier') == C('YES_STATUS')) {
                //厂商用户 验证是否在在允许操作的方法
                if (in_array(ACTION_NAME, C('IS_SUPPLIER_MENU'))) {
                    return true;
                } else {
                    session(null);
                    $this->assign('tips', '您没有权限访问此页面！');
                    $this->assign('btn', '返回首页');
                    $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                    $this->display('Pub/Notin/fail');
                    exit;
                }
            } else {
                //获取用户所有可以访问的menuid
                $join[0] = ' LEFT JOIN __USER_ROLE__ ON __ROLE_MENU__.roleid = __USER_ROLE__.roleid';
                $join[1] = ' LEFT JOIN __MENU__ ON __ROLE_MENU__.menuid = __MENU__.menuid';
                $fields = 'sb_role_menu.menuid';
                $rolewhere['sb_user_role.userid'] = $userid;
                $rolewhere['sb_menu.status'] = C('YES_STATUS');
                if (C('IS_OPEN_BRANCH') && !C('CAN_MANAGER_BANCH')) {
                    //开启了分院功能，且不可以对分院进行具体管理操作，只能查看
                    if (session('current_hospitalid') != session('job_hospitalid')) {
                        //当前医院ID不等于自己所在工作医院ID，即现在切换到了用户管理的分院，那么所有具体操作权限都不能操作
                        $rolewhere['sb_menu.leftShow'] = C('YES_STATUS');
                    }
                }
                $menuArr = $model->DB_get_all_join('role_menu', 'sb_role_menu', $fields, $join, $rolewhere, 'sb_role_menu.menuid', 'sb_role_menu.menuid asc','');
                $arr = array();
                foreach ($menuArr as $v) {
                    $arr[] = (int)$v['menuid'];
                }
                //G('end');
                //echo G('begin','end').'s---';
                //判断要访问的menuid是否在$arr中
                if (in_array($menuid['menuid'], $arr)) {
                    return true;
                } else {
                    session(null);
                    $this->assign('tips', '您没有权限访问此页面！');
                    $this->assign('btn', '返回首页');
                    $this->assign('url', C('MOBILE_NAME').'/'.$this->index_url);
                    $this->display('Pub/Notin/fail');
                    exit;
                }
            }
        }
    }

    /**
     * Notes:查询审批是否已开启
     * @param string $type 类型
     * @return bool
     */
    public function checkApproveIsOpen($type, $hospital_id)
    {
        $apModel = new ApproveProcessModel();
        $where['approve_type'] = array('EQ', $type);
        $where['hospital_id'] = array('EQ', $hospital_id);
        $where['status'] = array('EQ', C('OPEN_STATUS'));
        $res = $apModel->DB_get_one('approve_type', '*', $where);
        return $res;
    }

    public function index()
    {
        //判断是否开启微信端
        $moduleModel = new ModuleModel();
        $wx_status=$moduleModel->decide_wx_login();
        if (!$wx_status) {
            $this->assign('tips', '微信端已经关闭，请先开启微信端');
            $this->assign('btn', '返回登录页面');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->login_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        $jssdk = new WxAccessTokenModel();
        $signPackage = $jssdk->GetSignPackage();
        $this->signPackage = $signPackage;
        $this->display();
    }

    /**
     * Notes:微信首页
     */
    public function testindex()
    {
        //判断是否开启微信端
        $moduleModel = new ModuleModel();
        $wx_status=$moduleModel->decide_wx_login();
        if (!$wx_status) {
            $this->assign('tips', '微信端已经关闭，请先开启微信端');
            $this->assign('btn', '返回登录页面');
            $this->assign('url', C('MOBILE_NAME').'/'.$this->login_url);
            $this->display('Pub/Notin/fail');
            exit;
        }
        $this->title = '医疗设备管理系统';
        $jssdk = new WxAccessTokenModel();
        $signPackage = $jssdk->GetSignPackage();
        $this->signPackage = $signPackage;
        $this->display();
    }
}
