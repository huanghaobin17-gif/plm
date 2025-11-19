<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/1/24
 * Time: 13:47
 */

namespace App\Controller\Login;

use App\Controller\AppController;
use App\Model\UserModel;
use App\Service\UserInfo\UserInfo;

class LoginController extends AppController
{
    public $UserModel;

    public function login()
    {
        $this->userInfoBegin();
        if (IS_POST) {
            $UserModel = new \Admin\Model\UserModel();
            $result    = $UserModel->loginVerify(I('POST.username'), I('POST.password'));
            if ($result['status'] == -1) {
                $this->ajaxReturn($result);
            }

            $user     = $result['user'];
            $keys     = $result['keys'];
            $password = $result['password'];

            //判断密码复杂度和有效期
            if (preg_match_all('/^(?![a-z]+$)(?![A-Z]+$)(?![0-9]+$)(?![\W_]+$)[a-zA-Z0-9\W_]{8,30}$/', $password)) {
                //符合标准，判断密码是否过期
                $is_overdue = $this->check_passwor_overdue($user);
                if (!$is_overdue) {
//                    session('password', true);
                    $this->ajaxReturn(['status' => -1, 'msg' => '密码已过期，请在电脑端重新修改密码！']);
                }
            } else {
//                session('password', true);
                $this->ajaxReturn(['status' => -1, 'msg' => '密码复杂度不够，请在电脑端重新修改密码！']);
            }

            $addLog['username']    = $user['username'];
            $module                = explode('/', CONTROLLER_NAME);
            $addLog['module']      = $module[0];
            $addLog['action']      = ACTION_NAME;
            $addLog['ip']          = get_ip();
            $addLog['remark']      = '登录系统';
            $addLog['action_time'] = getHandleDate(time());
            $UserModel->insertData('operation_log', $addLog);

            $this->UserModel = new UserModel();

            //微信登录处理
            if (UserInfo::getPlatform() == C('VUE_APP_WX')) {
                $this->loginWeixin($user);
                //（暂定）刷新用户数据
                $user = $this->UserModel->DB_get_one('user', '*', ['userid' => $user['userid']]);
            }

            $res = $this->UserModel->setSession($user);

            //清除错误登录次数  C('_LOGIN_UPDATA_PASSWORD_MSG_')
            $UserModel->clearLoginTimes($keys);

            $result           = [];
            $result['status'] = 1;
            $result['msg']    = '登录成功';
            if (UserInfo::getPlatform() == C('VUE_APP_APP')) {
                $result['token'] = $res['token'];
            }
            $this->ajaxReturn($result, 'JSON');
        } else {
            if (!UserInfo::getInstance()->get('userid')) {
                $result['status'] = 0;
            } else {
                $result['status'] = 999;
            }
            $this->ajaxReturn($result, 'json');
        }
    }


    /**
     * Notes: 微信公众号登录入口
     */
    public function getUserOpenId()
    {
        $this->userInfoBegin()->start();
        if (I('GET.code')) {
            $code = I('GET.code');
            //OAuth2.0鉴权
            //是否开启反向代理
            if (!C('OPEN_AGENT')) {
                $content = grant_authorization($code);
            } else {
                $content = dcurl(C('GET_GRANT_AUTHORIZATION_URL') . '?code=' . $code);
            }
            if ($content['openid'] != null) {
                //获取用户其他信息,判断用户是否已关注公众号，未关注要先关注
                $wxModel     = new \App\Model\WxAccessTokenModel();
                $accessToken = $wxModel->getAccessToken();
                if (!C('OPEN_AGENT')) {
                    $userBaseInfo = get_userinfo_by_unionID($content['openid'], $accessToken);
                } else {
                    $userBaseInfo = dcurl(C('GET_WX_USER_INFO_URL') . '?method=unionID=&openid=' . $content['openid'] . '&access_token=' . $accessToken);
                }
                if ($userBaseInfo['errcode']) {
                    $result['tips'] = '获取用户信息失败，errcode：' . $userBaseInfo['errcode'] . '，errmsg：' . $userBaseInfo['errmsg'];
                    $result['btn']  = '';
                    $result['url']  = '';
                    $result         = json_encode($result, JSON_UNESCAPED_UNICODE);
                    redirect(C('APP_FOLDER_NAME') . '/#/fail/' . $result);
                    exit;
                }
                if (!$userBaseInfo['subscribe']) {
                    $result['tips'] = '请先关注公众号';
                    $result['btn']  = '';
                    $result['url']  = '';
                    $result         = json_encode($result, JSON_UNESCAPED_UNICODE);
                    redirect(C('APP_FOLDER_NAME') . '/#/fail/' . $result);
                    exit;
                }
                //获取用户昵称、头像等信息，根据网页授权时返回的token和openid去获取
                if (!C('OPEN_AGENT')) {
                    $snsapi_userinfo = get_userinfo_by_OAuth($content);
                } else {
                    $snsapi_userinfo = dcurl(C('GET_WX_USER_INFO_URL') . '?method=OAuth&openid=' . $content['openid'] . '&access_token=' . $accessToken);
                }
                if ($snsapi_userinfo['errcode']) {
                    $result['tips'] = '获取用户账号信息失败，errcode：' . $snsapi_userinfo['errcode'] . '，errmsg：' . $snsapi_userinfo['errmsg'];
                    $result['btn']  = '';
                    $result['url']  = '';
                    $result         = json_encode($result, JSON_UNESCAPED_UNICODE);
                    redirect(C('APP_FOLDER_NAME') . '/#/fail/' . $result);
                    exit;
                }
                //登录页面过来的，跳回登录页面
//                $from_login = UserInfo::getInstance()->get('from_login');
//                if ($from_login) {
//                    UserInfo::getInstance()->set('openid', $content['openid']);
//                    UserInfo::getInstance()->set('nickname', $snsapi_userinfo['nickname']);
//                    UserInfo::getInstance()->set('headimgurl', $snsapi_userinfo['headimgurl']);
//                    UserInfo::getInstance()->set('from_login', null);
                //                redirect(C('APP_FOLDER_NAME') . '/#/login');
                //                exit;
//                }
                //根据微信openid查询用户信息
                UserInfo::getInstance()->set('weixin_platform', C('VUE_APP_WX'));

                $old_session_openid = UserInfo::getInstance()->get('openid');
                UserInfo::getInstance()->set('openid', $content['openid']);
                UserInfo::getInstance()->set('nickname', $snsapi_userinfo['nickname']);
                UserInfo::getInstance()->set('headimgurl', $snsapi_userinfo['headimgurl']);
                $userModel = new UserModel();
                $users     = $userModel->get_user_info($content['openid']);
                if (!$users) {
                    //查找不到该openid的用户
                    redirect(C('APP_FOLDER_NAME') . '/#/login');
                    exit;
                }
                if (count($users) == 1) {
                    //该openid只有一个账号在用，重置session
                    $this->setSession($users[0]['userid']);

                } else {
//                    //该openid绑定了多个账户
//                    if ($content['openid'] == $old_session_openid && UserInfo::getInstance()->get('userid')) {
//                        //直接进入首页
//                        redirect(C('APP_FOLDER_NAME') . '/#/');
//                        exit;
//                    } else {
                    redirect(C('APP_FOLDER_NAME') . '/#/chan_user');
                    exit;
//                    }
                }
                redirect(C('APP_FOLDER_NAME') . '/#/');
                exit;
            } else {
                UserInfo::getInstance()->logout();
                $result['tips'] = '获取用户信息失败，请尝试重新授权';
                $result['btn']  = '重新授权';
                $result['url']  = C('APP_MODULE') . '/Login/getUserOpenId';
                $result         = json_encode($result, JSON_UNESCAPED_UNICODE);
                redirect(C('APP_FOLDER_NAME') . '/#/fail/' . $result);
                exit;
            }
        } else {
            /*判断是否开启微信端*/
            $moduleModel = new \Admin\Model\ModuleModel();
            $wx_status   = $moduleModel->decide_wx_login();
            if (!$wx_status) {
                UserInfo::getInstance()->logout();
                $result['tips'] = '微信端使用已被关闭';
                $result['btn']  = '';
                $result['url']  = '';
                $result         = json_encode($result, JSON_UNESCAPED_UNICODE);
                redirect(C('APP_FOLDER_NAME') . '/#/fail/' . $result);
                exit;
            } else {
                //获取用户openid
                //是否开启反向代理
                if (!C('OPEN_AGENT')) {
                    $url = get_code('abc');
                } else {
                    $url = C('GET_CODE_URL') . '?state=abc';
                }
                header("Location: $url");
                exit;
            }
        }
    }

    //企业微信登录入口
    public function getQyUserOpenId()
    {
        if (I('GET.code')) {
            $this->userInfoBegin()->start();
            $code = I('GET.code');
            //OAuth2.0鉴权
            //获取用户其他信息,判断用户是否已关注公众号，未关注要先关注
            $wxModel      = new \App\Model\WxAccessTokenModel();
            $accessToken  = $wxModel->getQyAccessToken();
            $userBaseInfo = get_qyuserinfo_by_unionID($code, $accessToken);
            if ($userBaseInfo['errcode']) {
                $result['tips'] = '获取用户信息失败，errcode：' . $userBaseInfo['errcode'] . '，errmsg：' . $userBaseInfo['errmsg'];
                $result['btn']  = '';
                $result['url']  = '';
                $result         = json_encode($result, JSON_UNESCAPED_UNICODE);
                redirect(C('APP_FOLDER_NAME') . '/#/fail/' . $result);
                exit;
            }
            //获取用户昵称、头像等信息，根据网页授权时返回的token和openid去获取
            $snsapi_userinfo = get_qyuserinfo_by_OAuth($accessToken, $userBaseInfo['user_ticket']);
            if ($snsapi_userinfo['errcode']) {
                $result['tips'] = '获取用户敏感信息失败，errcode：' . $snsapi_userinfo['errcode'] . '，errmsg：' . $snsapi_userinfo['errmsg'];
                $result['btn']  = '';
                $result['url']  = '';
                $result         = json_encode($result, JSON_UNESCAPED_UNICODE);
                redirect(C('VUE_FOLDER_NAME') . '/#/fail/' . $result);
                exit;
            }
            UserInfo::getInstance()->set('weixin_platform', C('VUE_APP_QYWX'));
            UserInfo::getInstance()->set('qywx', 1);

            //根据微信userid查询用户信息
            $old_session_openid = UserInfo::getInstance()->get('qy_user_id');
            UserInfo::getInstance()->set('qy_user_id', $userBaseInfo['userid']);
            UserInfo::getInstance()->set('openid', $userBaseInfo['userid']);
            UserInfo::getInstance()->set('mobile', $snsapi_userinfo['mobile']);
            UserInfo::getInstance()->set('gender', $snsapi_userinfo['gender']);
            UserInfo::getInstance()->set('email', $snsapi_userinfo['email']);
            UserInfo::getInstance()->set('avatar', $snsapi_userinfo['avatar']);
            UserInfo::getInstance()->set('headimgurl', $snsapi_userinfo['avatar']);
            UserInfo::getInstance()->set('qr_code', $snsapi_userinfo['qr_code']);
            UserInfo::getInstance()->set('biz_mail', $snsapi_userinfo['biz_mail']);
            UserInfo::getInstance()->set('address', $snsapi_userinfo['address']);
            $userModel = new UserModel();
            $users     = $userModel->get_qy_user_info($userBaseInfo['userid']);
            if (!$users) {
                //查找不到该openid的用户
                redirect(C('APP_FOLDER_NAME') . '/#/login');
                exit;
            }

            if (count($users) == 1) {
                //该openid只有一个账号在用，重置session
                $this->setSession($users[0]['userid']);
            } else {
//                    //该openid绑定了多个账户
//                    if ($content['openid'] == $old_session_openid && UserInfo::getInstance()->get('userid')) {
//                        //直接进入首页
//                        redirect(C('APP_FOLDER_NAME') . '/#/');
//                        exit;
//                    } else {
                redirect(C('APP_FOLDER_NAME') . '/#/chan_user');
                exit;
//                    }
            }
        } else {
            /*判断是否开启微信端*/
            $moduleModel = new \Admin\Model\ModuleModel();
            $wx_status   = $moduleModel->decide_wx_login();
            if (!$wx_status) {
                session(null);
                $result['tips'] = '微信端使用已被关闭';
                $result['btn']  = '';
                $result['url']  = '';
                $result         = json_encode($result, JSON_UNESCAPED_UNICODE);
                redirect(C('APP_FOLDER_NAME') . '/#/fail/' . $result);
                exit;
            } else {
                //获取用户openid
                //是否开启反向代理
                if (!C('OPEN_AGENT')) {
                    $url = get_qy_code('abc');
                } else {
                    $url = C('GET_CODE_URL') . '?state=abc';
                }
                header("Location: $url");
                exit;
            }
        }
    }


    //微信登录逻辑
    public function loginWeixin($user)
    {
        //公众号
        if (UserInfo::getInstance()->get('weixin_platform') == C('VUE_APP_WX')) {
            if ($user['openid'] && UserInfo::getInstance()->get('openid') != $user['openid']) {
                //不符
                if ($user['wx_public_account'] == 1) {
                    //公共账号，可用多个微信共用
                    //更新新用户openid
                    $public_update['pic']      = UserInfo::getInstance()->get('headimgurl');
                    $public_update['nickname'] = UserInfo::getInstance()->get('nickname');
                    $public_update['openid']   = UserInfo::getInstance()->get('openid');
                    $this->UserModel->updateData('user', $public_update, ['userid' => $user['userid']]);

                    //记录新登录微信用户信息
                    $new_public_login['userid']     = $user['userid'];
                    $new_public_login['nickname']   = UserInfo::getInstance()->get('nickname');
                    $new_public_login['openid']     = UserInfo::getInstance()->get('openid');
                    $new_public_login['login_time'] = date('Y-m-d H:i:s');
                    $this->UserModel->insertData('wx_public_login', $new_public_login);
                } else {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该账号已绑定其他微信！']);
                }
            }
            if (!$user['openid']) {
                //保存用户
                $new_update['pic']      = UserInfo::getInstance()->get('headimgurl');
                $new_update['nickname'] = UserInfo::getInstance()->get('nickname');
                $new_update['openid']   = UserInfo::getInstance()->get('openid');
                $this->UserModel->updateData('user', $new_update, ['userid' => $user['userid']]);
            }

            return $user;
        }

        //企业微信
        if (UserInfo::getInstance()->get('weixin_platform') == C('VUE_APP_QYWX')) {
            if ($user['qy_user_id'] && UserInfo::getInstance()->get('qy_user_id') != $user['qy_user_id']) {
                //不符
                if ($user['wx_public_account'] == 1) {
                    //公共账号，可用多个微信共用
                    //更新新用户openid
                    $public_update['pic']        = UserInfo::getInstance()->get('avatar');
                    $public_update['nickname']   = UserInfo::getInstance()->get('nickname');
                    $public_update['qy_user_id'] = UserInfo::getInstance()->get('qyuserid');
                    $this->UserModel->updateData('user', $public_update, ['userid' => $user['userid']]);

                    //记录新登录微信用户信息
                    $new_public_login['userid']     = $user['userid'];
                    $new_public_login['nickname']   = UserInfo::getInstance()->get('nickname');
                    $new_public_login['qy_user_id'] = UserInfo::getInstance()->get('qy_user_id');
                    $new_public_login['login_time'] = date('Y-m-d H:i:s');
                    $this->UserModel->insertData('wx_public_login', $new_public_login);
                } else {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该账号已绑定其他微信！']);
                }
            }
            if (!$user['qy_user_id']) {
                //保存用户
                $new_update['pic']        = UserInfo::getInstance()->get('avatar');
                $new_update['nickname']   = UserInfo::getInstance()->get('nickname');
                $new_update['qy_user_id'] = UserInfo::getInstance()->get('qy_user_id');
                $this->UserModel->updateData('user', $new_update, ['userid' => $user['userid']]);
            }
        }

    }

    public function setSession($userid, $returnJosn = 0)
    {
        $this->userInfoBegin()->start();
        if (empty(UserInfo::getInstance()->get('openid'))) {
            $this->ajaxReturn(['status' => 999]);
        }
        $UserModel          = new UserModel();
        $where['userid']    = $userid;
        $where['is_delete'] = 0;
        $where['status']    = 1;
        $user               = $UserModel->DB_get_one('user', '*', $where);
        $res                = $UserModel->setSession($user);
        if ($res['status'] == -1) {
            $this->ajaxReturn($res);
        } else {
            //重置session成功，跳转到上一次访问的地址或首页
            $pre_url = UserInfo::getInstance()->get('pre_url');
            $pre_url = str_replace('/index.php/', '', $pre_url);
            $pre_url = str_replace('.html', '', $pre_url);
            $pre_url = trim($pre_url, '/');
            if ($pre_url) {
                UserInfo::getInstance()->set('pre_url', null);
                redirect(C('APP_FOLDER_NAME') . '/#/' . $pre_url);
            } else {
                if ($returnJosn) {
                    $this->ajaxReturn($res);
                } else {
                    //跳到首页
                    redirect(C('APP_FOLDER_NAME') . '/#/');
                }
            }
        }
//        $this->ajaxReturn($res);
    }


    public function logout()
    {
        if (IS_POST) {
            $this->userInfoBegin()->start();
            UserInfo::getInstance()->logout();
            $this->ajaxReturn(['status' => 1, 'msg' => '注销成功！']);
        }
    }

    /**
     * 判断密码是否过期
     */

    private function check_passwor_overdue($user)
    {
        //密码过期失效天数
        $password_overdue_days = C('password_overdue_days');
        if (!isset($user['set_password_time'])) {
            //没设置set_password_time，以add_time时间为准
            if (!isset($user['add_time'])) {
                //没设置add_time，直接需要重改密码
                return false;
            } else {
                //设置了add_time
                if (date('Y-m-d H:i:s',
                        strtotime($user['add_time']) + ($password_overdue_days * 24 * 3600)) < date('Y-m-d H:i:s')) {
                    return false;
                }
            }
        } else {
            if (date('Y-m-d H:i:s',
                    strtotime($user['set_password_time']) + ($password_overdue_days * 24 * 3600)) < date('Y-m-d H:i:s')) {
                return false;
            }
        }
        return true;
    }
}
