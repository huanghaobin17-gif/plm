<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/1/24
 * Time: 13:47
 */

namespace Mobile\Controller\Login;

use Mobile\Controller\Pub\NotinController;
use Mobile\Model\QualityModel;
use Admin\Model\UserModel;
use Mobile\Model\WxAccessTokenModel;
use Think\Controller;
use Admin\Model\ModuleModel;
class LoginController extends Controller
{
    public function login()
    {
        if (IS_POST) {
            $UserModel = new UserModel();
            $result = $UserModel->loginVerify(I('POST.username'),I('POST.password'));
            if ($result['status'] == -1) {
                $this->ajaxReturn($result);
            }
            $user = $result['user'];
            $keys = $result['keys'];
            $password = $result['password'];
            //判断是否开启微信端
            $moduleModel = new ModuleModel();
            $wx_status=$moduleModel->decide_wx_login();
            if (!$wx_status) {
                $this->ajaxReturn(array('status'=>-1,'msg'=>'微信端已经关闭，请先开启微信端'));
            }
            //登录成功，判断用户openid是否与登录用户openid一致
            //测试代码
            //session('openid','oSYrdsu68E4GetWsrQ76dmuGo2M0');
            if($user['openid'] && session('openid') != $user['openid']){
                //不符
                $this->ajaxReturn(array('status' => -1, 'msg' => '该账号已绑定其他微信！'));
            }
            if(!$user['openid']){
                //保存用户
                $UserModel->updateData('user',array('openid'=>session('openid')),array('userid'=>$user['userid']));
            }
            //判断密码复杂度和有效期
            if (preg_match_all('/^(?![a-z]+$)(?![A-Z]+$)(?![0-9]+$)(?![\W_]+$)[a-zA-Z0-9\W_]{8,30}$/',$password)) {
                //符合标准，判断密码是否过期
                $is_overdue = $this->check_passwor_overdue($user);
                if(!$is_overdue){
                    session('password',true);
                    $this->ajaxReturn(array('status' => -1, 'msg' => '密码已过期，请在电脑端重新修改密码！'));
                }
            } else {
                session('password',true);
                $this->ajaxReturn(array('status' => -1, 'msg' => '密码复杂度不够，请在电脑端重新修改密码！'));
            }

            $addLog['username'] = $user['username'];
            $module = explode('/', CONTROLLER_NAME);
            $addLog['module'] = $module[0];
            $addLog['action'] = ACTION_NAME;
            $addLog['ip'] = get_ip();
            $addLog['remark'] = '登录系统';
            $addLog['action_time'] = getHandleDate(time());
            $UserModel->insertData('operation_log', $addLog);

            //获取用户其他信息
            $tokenModel = new WxAccessTokenModel();
            $wxuserinfo = $tokenModel->get_wxuser_info(session('openid'));
            //保存头像
            $UserModel->updateData('user',array('pic'=>$wxuserinfo['headimgurl'],'nickname'=>$wxuserinfo['nickname']),array('userid'=>$user['userid']));
            NotinController::setSession($user['userid']);

            $result = array();
            $result['status'] = 1;
            $result['msg'] = '登录成功';
            $result['openid'] = session('openid');
            $result['userid'] = $user['userid'];
            $result['nickname'] = $wxuserinfo['nickname'];
            $result['sex'] = $wxuserinfo['sex'];
            $result['country'] = $wxuserinfo['country'];
            $result['province'] = $wxuserinfo['province'];
            $result['city'] = $wxuserinfo['city'];
            $result['headimgurl'] = $wxuserinfo['headimgurl'];
            //清除错误登录次数  C('_LOGIN_UPDATA_PASSWORD_MSG_')
            $UserModel->clearLoginTimes($keys);
            $this->ajaxReturn($result, 'JSON');
        } else {
//            if(!session('openid')){
//                session('from_login',$_SERVER['REQUEST_URI']);
//                redirect(U('M/Notin/getUserOpenId'));
//                exit;
//            }
//            $this->assign('loginUrl', get_url());
            $this->display();
        }
    }

    public function logout()
    {
        session(null);
        $this->assign('url',C('MOBILE_NAME').'/Notin/getUserOpenId.html');
        $this->assign('tips','已成功退出！');
        $this->assign('btn','重新授权登陆！');
        $this->display();
    }

    /**
     * 判断密码是否过期
     */

    private function check_passwor_overdue($user)
    {
        //密码过期失效天数
        $password_overdue_days = C('password_overdue_days');
        if(!isset($user['set_password_time'])){
            //没设置set_password_time，以add_time时间为准
            if(!isset($user['add_time'])){
                //没设置add_time，直接需要重改密码
                return false;
            }else{
                //设置了add_time
                if(date('Y-m-d H:i:s',strtotime($user['add_time'])+($password_overdue_days*24*3600)) < date('Y-m-d H:i:s')){
                    return false;
                }
            }
        }else{
            if(date('Y-m-d H:i:s',strtotime($user['set_password_time'])+($password_overdue_days*24*3600)) < date('Y-m-d H:i:s')){
                return false;
            }
        }
        return true;
    }
}
