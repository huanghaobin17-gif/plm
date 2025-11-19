<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/22
 * Time: 22:47
 */

namespace Mobile\Controller\Pub;

use Admin\Model\ModuleModel;
use Mobile\Model\AssetsBorrowModel;
use Mobile\Model\AssetsTransferModel;
use Mobile\Model\RepairModel;
use Mobile\Model\AssetsScrapModel;
use Mobile\Model\UserModel;
use Mobile\Model\WxAccessTokenModel;
use Think\Controller;

class NotinController extends Controller
{
    protected $login_url = 'Login/login.html';//登录地址
    protected $fail_url = 'Notin/fail.html';//失败跳转地址
    protected $succ_url = 'Notin/suc.html';//成功跳转地址
    protected $index_url = 'Index/testindex.html';//首页地址
    protected $addRepair_url = 'Repair/addRepair.html';//报修地址
    protected $ordersLists_url = 'Repair/ordersLists.html';//接单列表地址
    protected $overhaulLists_url = 'Repair/ordersLists.html?action=overhaulLists';//检修列表地址
    protected $accept_url = 'Repair/accept.html';//接单、检修操作地址
    protected $repair_detail_url = 'Repair/showRepairDetails.html';//维修单详情地址

    /**
     * Notes: 获取用户openid
     */
    public function getUserOpenId()
    {
        if (I('GET.code')) {
            $code = I('GET.code');
            //OAuth2.0鉴权
            if(!C('OPEN_AGENT')){
                $content = grant_authorization($code);
            }else{
                $content = dcurl(C('GET_GRANT_AUTHORIZATION_URL').'?code='.$code);
            }
            if ($content['openid'] != null) {
                //获取用户其他信息,判断用户是否已关注公众号，未关注要先关注
                $wxModel = new \Mobile\Model\WxAccessTokenModel();
                $accessToken = $wxModel->getAccessToken();
                if(!C('OPEN_AGENT')){
                    $userBaseInfo = get_userinfo_by_unionID($content['openid'],$accessToken);
                }else{
                    $userBaseInfo = dcurl(C('GET_WX_USER_INFO_URL').'?method=unionID=&openid='.$content['openid'].'&access_token='.$accessToken);
                }
                if ($userBaseInfo['errcode']) {
                    $this->assign('tips', '获取用户信息失败，errcode：'.$userBaseInfo['errcode'].'，errmsg：'.$userBaseInfo['errmsg']);
                    $this->assign('url', '');
                    $this->display('Pub/Notin/fail');
                    exit;
                }
                if (!$userBaseInfo['subscribe']) {
                    $this->assign('tips', '请先关注公众号！');
                    $this->assign('url', '');
                    $this->display('Pub/Notin/fail');
                    exit;
                }

                //获取用户昵称、头像等信息，根据网页授权时返回的token和openid去获取
                if(!C('OPEN_AGENT')){
                    $snsapi_userinfo = get_userinfo_by_OAuth($content);
                }else{
                    $snsapi_userinfo = dcurl(C('GET_WX_USER_INFO_URL').'?method=OAuth&openid='.$content['openid'].'&access_token='.$accessToken);
                }
                if ($snsapi_userinfo['errcode']) {
                    $this->assign('tips', '获取用户信息失败，errcode：'.$snsapi_userinfo['errcode'].'，errmsg：'.$snsapi_userinfo['errmsg']);
                    $this->assign('url', '');
                    $this->display('Pub/Notin/fail');
                    exit;
                }

                //登录页面过来的，跳回登录页面
                $from_login = session('from_login');
                $from_login = str_replace('/index.php/', '', $from_login);
                $from_login = str_replace('.html', '', $from_login);
                if ($from_login) {
                    session('openid', $content['openid']);
                    session('nickname', $snsapi_userinfo['nickname']);
                    session('headimgurl', $snsapi_userinfo['headimgurl']);
                    session('from_login', null);
                    redirect(U($from_login));
                    exit;
                }
                //根据微信openid查询用户信息
                $old_session_openid = session('openid');
                session('openid', $content['openid']);
                session('nickname', $snsapi_userinfo['nickname']);
                session('headimgurl', $snsapi_userinfo['headimgurl']);
                $userModel = new UserModel();
                $users = $userModel->get_user_info($content['openid']);
                if (!$users) {
                    //查找不到该openid的用户
                    redirect(U('M/Login/login'));
                    exit;
                }
                if (count($users) == 1) {
                    //该openid只有一个账号在用，重置session
                    $this->setSession($users[0]['userid']);
                } else {
                    //该openid绑定了多个账户
                    if ($content['openid'] == $old_session_openid && session('userid')) {
                        redirect(U('M/Index/testindex'));
                        exit;
                    } else {
                        redirect(U('M/Notin/changuser'));
                        exit;
                    }
                }
            } else {
                session(null);
                $this->assign('tips', '获取用户信息失败，请尝试重新授权！');
                $this->assign('btn', '重新授权');
                $this->assign('url', C('MOBILE_NAME') . '/Notin/getUserOpenId');
                $this->display('Pub/Notin/fail');
                exit;
            }
        } else {
            /*判断是否开启微信端*/
            $moduleModel = new ModuleModel();
            $wx_status = $moduleModel->decide_wx_login();
            if (!$wx_status) {
                session(null);
                $this->assign('tips', '微信端使用已被关闭');
                $this->assign('btn', '');
                $this->assign('url', '');
                $this->display('Pub/Notin/fail');
                exit;
            } else {
                //获取用户openid
                if(!C('OPEN_AGENT')){
                    $url = get_code('abc');
                }else{
                    $url = C('GET_CODE_URL').'?state=abc';
                }
                header("Location: $url");
                exit;
            }
        }
    }

    /**
     * Notes: 微信端设置session
     */
    public function setSession($userid)
    {
        $UserModel = new UserModel();
        $where['userid'] = $userid;
        $where['is_delete'] = 0;
        $where['status'] = 1;
        $user = $UserModel->DB_get_one('user', '*', $where);
        $res = $UserModel->setSession($user);
        if ($res['status'] == -1) {
            $this->assign('tips', $res['msg']);
            $this->assign('btn', '重新登录');
            $this->assign('url', C('MOBILE_NAME') . '/' . $this->login_url);
            $this->display('Pub/Notin/fail');
            exit;
        } else {
            //重置session成功，跳转到上一次访问的地址或首页
            $pre_url = session('pre_url');
            $pre_url = str_replace('/index.php/', '', $pre_url);
            $pre_url = str_replace('.html', '', $pre_url);
            $pre_url = trim($pre_url,'/');
            if ($pre_url) {
                session('pre_url', null);
                redirect(U($pre_url));
            } else {
                redirect(U('M/Index/testindex'));
            }
        }
    }

    public function suc()
    {
        $redirectUrl = I('GET.url');
        $tips = I('GET.tips');
        $btn = I('GET.btn');
        $this->assign('url', $redirectUrl);
        $this->assign('tips', $tips);
        $this->assign('btn', $btn);
        $this->display();
    }

    public function fail()
    {
        $redirectUrl = I('GET.url');
        $tips = I('GET.tips');
        $btn = I('GET.btn');
        $this->assign('url', C('MOBILE_NAME') . '/' . $redirectUrl);
        $this->assign('tips', $tips);
        $this->assign('btn', $btn);
        $this->display();
    }

    /*
     * 维修审批 、 转科审批 、借调审批
     */
    public function approve()
    {
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'transfer':
                    $transferModel = new AssetsTransferModel();
                    $result = $transferModel->get_transfer_examine();
                    $this->ajaxReturn($result);
                    break;
                case 'repair':
                    $repairModel = new RepairModel();
                    $result = $repairModel->get_repair_examine();
                    $this->ajaxReturn($result);
                    break;
                case 'scrap':
                    $scrapModel = new AssetsScrapModel();
                    $result = $scrapModel->get_scrap_examine();
                    $this->ajaxReturn($result);
                    break;
                case 'borrow':
                    $borrowModel = $repairModel = new AssetsBorrowModel();
                    $result = $borrowModel->get_borrow_examine();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
            $show_transfer = $show_scrap = $show_repair = $show_borrow = 1;
            //查询是否有审批权限
            $menu = get_menu('Assets', 'Transfer', 'examine');
            if (!$menu) {
                $show_transfer = 0;
            }
            $menu = get_menu('Repair', 'Repair', 'repairApproveLists');
            if (!$menu) {
                $show_repair = 0;
            }
            $menu = get_menu('Assets', 'Borrow', 'approveBorrowList');
            if (!$menu) {
                $show_borrow = 0;
            }
            $menu = get_menu('Assets', 'Scrap', 'getExamineList');
            if (!$menu) {
                $show_scrap = 0;
            }
            $this->assign('show_repair', $show_repair);
            $this->assign('show_transfer', $show_transfer);
            $this->assign('show_borrow', $show_borrow);
            $this->assign('show_scrap', $show_scrap);
            $this->display();
        }
    }

    public function changuser()
    {
        $openid = session('openid');
        $userModel = new UserModel();
        $users = $userModel->get_user_info($openid);
        $this->assign('users', $users);
        $this->assign('tips', '该微信已绑定多个账户，请选择其中一个登录');
        $this->display('Pub/Notin/changuser');
        exit;
    }

    /*
    下载页面
     */
    public function download()
    {
        $path = I('get.path');
        $this->assign('path', $path);
        $name = I('get.name');
        $this->assign('name', $name);
        $size = I('get.size');
        $this->assign('size', $size);
        $this->display();
    }

    public function downloads()
    {
        $path = I('get.path');
        $name = I('get.name');
        $size = I('get.size');
        Header("Content-type:  application/octet-stream ");
        Header("Accept-Ranges:  bytes ");
        Header("Accept-Length: " . $size);
        header("Content-Disposition:  attachment;  filename= $name");//生成的文件名(带后缀的)
        echo file_get_contents('http://' . C('HTTP_HOST') . $path);//用绝对路径
        readfile($path);

    }

    public function code()
    {
        $userModel = new UserModel();
        if (IS_POST) {
            $is_agree = I('POST.is_agree');
            $openid = I('POST.openid');
            $state = I('POST.state');
            sleep(1);
            if ($is_agree) {
                //同意授权登录
                $userModel->updateData('user', array('authorization' => 2), array('openid' => $openid, 'state' => $state));
            } else {
                //不同意授权登录
                $userModel->updateData('user', array('authorization' => 3), array('openid' => $openid, 'state' => $state));
            }
            $this->ajaxReturn(array('status' => 1, 'msg' => 'success'));
        } else {
            $code = I('get.code');
            $state = I('get.state');
            $content = grant_authorization($code);
            if ($content['openid'] != null) {
                //根据微信openid查询用户信息
                $users = $userModel->get_user_info($content['openid']);
                if (!$users) {
                    //查找不到该openid的用户
                    redirect(U('M/Login/login'));
                    exit;
                }
                $wxModel = new WxAccessTokenModel();
                $access_token = $wxModel->getAccessToken();
                //获取用户信息
                $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $content['openid'] . '&lang=zh_CN';
                $userinfo = curl($url);
                $userinfo = json_decode($userinfo, true);
                //不论该微信绑定多少个账户，都默认用第一个账户授权
                $userModel->updateData('user', array('state' => $state, 'authorization' => 1), array('userid' => $users[0]['userid']));
                //弹出确认授权页面
                $this->assign('openid', $content['openid']);
                $this->assign('state', $state);
                $this->assign('userinfo', $userinfo);
                $this->display('confirm');
                exit;
            } else {
                session(null);
                $this->assign('tips', '获取用户信息失败，请尝试刷新PC端页面后重新扫码！');
                $this->assign('btn', '');
                $this->display('Pub/Notin/fail');
                exit;
            }
        }
    }

    public function getMenus()
    {
        $UserModel = new UserModel();
        $where = array();
        //测试用
        $where['username'] = '牛年';
        $where['status'] = C('YES_STATUS');
        $where['is_delete'] = C('NO_STATUS');

        $user = $UserModel->DB_get_one('user', '*', $where);
        $UserModel->setSession($user);
        $num = 8;//轮播每页8个
        //设备管理
        $assets = $this->get_assets_menus($num);
        //维修管理
        $repair = $this->get_repair_menus($num);
        //巡查保养
        $patrol = $this->get_patrol_menus($num);
        //质控管理
        $quality = $this->get_quality_menus($num);
        $menus = [];
        if ($assets['swipe']) {
            $menus[] = $assets;
        }
        if ($repair['swipe']) {
            $menus[] = $repair;
        }
        if ($patrol['swipe']) {
            $menus[] = $patrol;
        }
        if ($quality['swipe']) {
            $menus[] = $quality;
        }
        $this->ajaxReturn(['status' => 1, 'menu' => $menus]);
    }

    public function getSystem()
    {
        $System['title'] = '医疗设备管理系统';
        $System['icon'] = C('APP_NAME') . '/Public/images/logo-new.png';
        $this->ajaxReturn($System);
    }

    //设备管理菜单
    private function get_assets_menus($num)
    {
        $assets = [];
        $assets['title'] = '设备管理';
        $i = -1;
        $j = 0;
        //设备查询
        $sbcx = get_menu('Assets', 'Lookup', 'getAssetsList');
        if ($sbcx) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $sbcx['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/sbcx.png';
            $assets['swipe'][$i]['menu'][] = $sbcx;
        }
        //借调申请
        $jdsq = get_menu('Assets', 'Borrow', 'borrowAssetsList');
        if ($jdsq) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $jdsq['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/jdsq.png';
            $assets['swipe'][$i]['menu'][] = $jdsq;
        }
        //确认借入
        $qrjr = get_menu('Assets', 'Borrow', 'borrowInCheckList');
        if ($qrjr) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $qrjr['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/qrjr.png';
            $assets['swipe'][$i]['menu'][] = $qrjr;
        }
        //归还验收
        $ghys = get_menu('Assets', 'Borrow', 'giveBackCheckList');
        if ($qrjr) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $ghys['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/ghys.png';
            $assets['swipe'][$i]['menu'][] = $ghys;
        }
        //借调逾期
        $jdyq = get_menu('Assets', 'Borrow', 'giveBackCheckList');
        if ($jdyq) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $jdyq['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/jdyq.png';
            $assets['swipe'][$i]['menu'][] = $jdyq;
        }
        //转科申请
        $zksq = get_menu('Assets', 'Transfer', 'getList');
        if ($zksq) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $zksq['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/zksq.png';
            $assets['swipe'][$i]['menu'][] = $zksq;
        }
        //转科进程
        $zkjc = get_menu('Assets', 'Transfer', 'progress');
        if ($zkjc) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $zkjc['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/zkjc.png';
            $assets['swipe'][$i]['menu'][] = $zkjc;
        }
        //转科验收
        $zkys = get_menu('Assets', 'Transfer', 'checkLists');
        if ($zkys) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $zkys['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/zkys.png';
            $assets['swipe'][$i]['menu'][] = $zkys;
        }
        //报废申请
        $bfsq = get_menu('Assets', 'Scrap', 'getApplyList');
        if ($bfsq) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $bfsq['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/bfsq.png';
            $assets['swipe'][$i]['menu'][] = $bfsq;
        }
        //报废查询
        $bfcx = get_menu('Assets', 'Scrap', 'getScrapList');
        if ($bfcx) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $bfcx['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/bfcx.png';
            $assets['swipe'][$i]['menu'][] = $bfcx;
        }
        //查阅档案
        $cyda = get_menu('Archives', 'Box', 'boxList');
        if ($cyda) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $cyda['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/smda.png';
            $assets['swipe'][$i]['menu'][] = $cyda;
        }
        //标签核实
        $bqhs = get_menu('Assets', 'Print', 'verify');
        if ($bqhs) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $bqhs['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/byys.png';
            $assets['swipe'][$i]['menu'][] = $bqhs;
        }
        return $assets;
    }

    //维修管理菜单
    private function get_repair_menus($num)
    {
        $repair = [];
        $repair['title'] = '维修管理';
        $i = -1;
        $j = 0;
        //扫码报修
        $sabx = get_menu('Repair', 'Repair', 'addRepair');
        if ($sabx) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $sabx['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/sys.png';
            $sabx['actionname'] = '扫码报修';
            $repair['swipe'][$i]['menu'][] = $sabx;
        }
        //维修接单
        $wxjd = get_menu('Repair', 'Repair', 'ordersLists');
        if ($wxjd) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $wxjd['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/wxjd.png';
            $wxjd['actionname'] = '维修接单';
            $repair['swipe'][$i]['menu'][] = $wxjd;
        }
        //扫码检修
        $smjx = get_menu('Repair', 'Repair', 'accept');
        if ($smjx) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $smjx['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/smjx.png';
            $smjx['actionname'] = '扫码检修';
            $smjx['actionurl'] = '/M/Repair/ordersLists.html?action=overhaulLists';
            $repair['swipe'][$i]['menu'][] = $smjx;
        }
        //维修处理
        $wxcl = get_menu('Repair', 'Repair', 'getRepairLists');
        if ($wxcl) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $wxcl['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/wxcl.png';
            $repair['swipe'][$i]['menu'][] = $wxcl;
        }
        //维修进程
        $wxjc = get_menu('Repair', 'Repair', 'progress');
        if ($wxjc) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $wxjc['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/wxjc.png';
            $repair['swipe'][$i]['menu'][] = $wxjc;
        }
        //维修验收
        $wxys = get_menu('Repair', 'Repair', 'examine');
        if ($wxys) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $wxys['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/wxys.png';
            $repair['swipe'][$i]['menu'][] = $wxys;
        }
        //配件库存
        $pjkc = get_menu('Repair', 'RepairParts', 'partStockList');
        if ($pjkc) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $pjkc['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/pjck.png';
            $repair['swipe'][$i]['menu'][] = $pjkc;
        }
        //配件出库
        $pjck = get_menu('Repair', 'RepairParts', 'partsOutWareList');
        if ($pjck) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $pjck['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/pjck.png';
            $repair['swipe'][$i]['menu'][] = $pjck;
        }
        return $repair;
    }

    //巡查保养菜单
    private function get_patrol_menus()
    {
        $data = [];
        $data['title'] = '巡查保养';
        $data['swipe'] = [];
        //保养实施
        //保养进程
        //保养验收
        return $data;
    }

    //质控管理菜单
    private function get_quality_menus($num)
    {
        $quality = [];
        $quality['title'] = '质控管理';
        $i = -1;
        $j = 0;
        //质控检测
        $zkjc = get_menu('Qualities', 'Quality', 'qualityDetailList');
        if ($zkjc) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $zkjc['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/zkjc1.png';
            $zkjc['actionname'] = '质控检测';
            $quality['swipe'][$i]['menu'][] = $zkjc;
        }
        //质控结果
        $zkjg = get_menu('Qualities', 'Quality', 'qualityResult');
        if ($zkjg) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $zkjg['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/zkjg.png';
            $zkjg['actionname'] = '质控结果';
            $quality['swipe'][$i]['menu'][] = $zkjg;
        }

        return $quality;
    }

    public function testAbc()
    {
        $result['okk'] = '1';
        return $result;
    }

    public function login()
    {
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body, true);
        $token = $data['token'];
        $privkey = file_get_contents('Public/key/rsa_1024_priv.pem');
        $pi_key = openssl_pkey_get_private($privkey);
        if ($pi_key === false) {
            $this->ajaxReturn(array('status' => -1, 'msg' => '登录错误，请联系管理员'));
        }
        $UserModel = new UserModel();
        $where = array();
        $where['username'] = trim($data['username']);
        $where['status'] = C('YES_STATUS');
        $where['is_delete'] = C('NO_STATUS');

        $user = $UserModel->DB_get_one('user', 'username,openid,password', $where);

        if (!$user) {
            $this->ajaxReturn(array('status' => -1, 'msg' => C('_LOGIN_USER_NOT_EXISTS_MSG_')));
        }
        if (!password_verify($data['password'], $user['password'])) {
            $this->ajaxReturn(array('status' => -1, 'msg' => C('_LOGIN_USER_ERROR_MSG_'), 'data' => $password));
        }
        //判断账户是否已过期
        if ($user['expire_time'] && time() >= strtotime($user['expire_time'])) {
            $this->ajaxReturn(array('status' => -1, 'msg' => '您的账号已过期，如需继续使用，请联系管理员设置!'));
        }
        //判断是否开启微信端
        $moduleModel = new ModuleModel();
        $wx_status = $moduleModel->decide_wx_login();
        if (!$wx_status) {
            $this->ajaxReturn(array('status' => -1, 'msg' => '微信端已经关闭，请先开启微信端'));
        }
        //登录成功，判断用户openid是否与登录用户openid一致
        if ($user['openid'] && $token != '123') {
            //不符
            $this->ajaxReturn(array('status' => -1, 'msg' => '该账号已绑定其他微信！', 'data' => $token));
        }
        if (!$user['openid']) {
            //保存用户
            $UserModel->updateData('user', array('openid' => session('openid')), array('userid' => $user['userid']));
        }
        $addLog['username'] = $data['username'];
        $module = explode('/', CONTROLLER_NAME);
        $addLog['module'] = $module[0];
        $addLog['action'] = ACTION_NAME;
        $addLog['ip'] = get_ip();
        $addLog['remark'] = '登录系统';
        $addLog['action_time'] = getHandleDate(time());
        $UserModel->insertData('operation_log', $addLog);

        //获取用户其他信息
        $tokenModel = new WxAccessTokenModel();
        $wxuserinfo = $tokenModel->get_wxuser_info($user['openid']);
        //保存头像
        $UserModel->updateData('user', array('pic' => $wxuserinfo['headimgurl'], 'nickname' => $wxuserinfo['nickname']), array('userid' => $user['userid']));

        $result['status'] = 1;
        $result['msg'] = '登录成功';
        unset($user['password']);
        $result['token'] = '123';
        /*$result['openid'] = $user['openid'];
        $result['userid'] = $user['userid'];
        $result['nickname'] = $wxuserinfo['nickname'];
        $result['sex'] = $wxuserinfo['sex'];
        $result['token'] = '123';
        $result['country'] = $wxuserinfo['country'];
        $result['province'] = $wxuserinfo['province'];
        $result['city'] = $wxuserinfo['city'];
        $result['headimgurl'] = $wxuserinfo['headimgurl'];*/
        $this->ajaxReturn($result, 'JSON');
    }
}
