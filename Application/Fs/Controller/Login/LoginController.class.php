<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/1/24
 * Time: 13:47
 */

namespace Fs\Controller\Login;

use Fs\Controller\Pub\NotinController;
use Fs\Model\QualityModel;
use Admin\Model\UserModel;
use Fs\Model\WxAccessTokenModel;
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
            if(!session('user_access_token')){
                //不存在user_access_token
                $this->ajaxReturn(array('status' => -1, 'msg' => '请先授权！'));
            }
            $wxuserinfo = $tokenModel->get_feishu_userinfo(session('user_access_token'));
            if(!$user['openid']){
                //保存用户
                $UserModel->updateData('user',array('openid'=>$wxuserinfo['open_id']),array('userid'=>$user['userid']));
            }
            if($user['openid'] && $user['openid'] != $wxuserinfo['open_id']){
                //不符
                if($user['wx_public_account'] == 1){
                    //公共账号，可用多个微信共用
                    //更新新用户openid
                    $UserModel->updateData('user',array('openid'=>$wxuserinfo['open_id']),array('userid'=>$user['userid']));
                }else{
                    $this->ajaxReturn(array('status' => -1, 'msg' => '该账号已绑定其他飞书用户！'));
                }
            }
            //保存头像
            $UserModel->updateData('user',array('pic'=>$wxuserinfo['avatar_middle'],'nickname'=>$wxuserinfo['name']),array('userid'=>$user['userid']));

            NotinController::setSession($user['userid'],1);

            $result = array();
            $result['status'] = 1;
            $result['msg'] = '登录成功';
            $result['openid'] = $wxuserinfo['open_id'];
            $result['userid'] = $user['userid'];
            $result['nickname'] = $wxuserinfo['name'];
            $result['headimgurl'] = $wxuserinfo['avatar_middle'];
            //清除错误登录次数  C('_LOGIN_UPDATA_PASSWORD_MSG_')
            $UserModel->clearLoginTimes($keys);
            $this->ajaxReturn($result, 'JSON');
        } else {
            if(!session('openid')){
                session('from_login',C('FS_FOLDER_NAME').'/#/login');
                $result['status'] = 1;
                $result['openid'] = '';
                $this->ajaxReturn($result, 'json');
            }else{
                $result['status'] = 1;
                $result['openid'] = session('openid');
                $this->ajaxReturn($result, 'json');
            }
        }
    }

    public function logout()
    {
        if(IS_POST){
            session(null);
            $this->ajaxReturn(array('status'=>1,'msg'=>'注销成功！'));
        }
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
