<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/22
 * Time: 22:47
 */

namespace Fs\Controller\Pub;

use Admin\Model\AssetsInfoModel;
use Admin\Model\ModuleModel;
use Fs\Model\AssetsBorrowModel;
use Fs\Model\AssetsTransferModel;
use Fs\Model\QualityModel;
use Fs\Model\RepairModel;
use Fs\Model\AssetsScrapModel;
use Fs\Model\UserModel;
use Fs\Model\WxAccessTokenModel;
use Admin\Controller\Tool\ToolController;
use Think\Controller;
use Think\Log;

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
            //获取登录用户身份
            $wxModel =  new WxAccessTokenModel();
            $app_access_token = $wxModel->getAccessToken('app_access_token');
            $userInfo = get_auth_user_info($code,$app_access_token);
            if(!$userInfo){
                $result['tips'] = '获取用户信息失败';
                $result['btn'] = '';
                $result['url'] = '';
                $result = json_encode($result, JSON_UNESCAPED_UNICODE);
                redirect(C('FS_FOLDER_NAME') . '/#/fail/' . $result);
                exit;
            }
            session('user_access_token',$userInfo['access_token']);
            session('feishu_name',$userInfo['name']);
            session('feishu_avatar',$userInfo['avatar_middle']);
            //根据open_id查询用户信息
            $userModel = new UserModel();
            $users = $userModel->get_user_info($userInfo['open_id']);
            if (!$users) {
                //查找不到该openid的用户
                redirect(C('FS_FOLDER_NAME') . '/#/login');
                exit;
            }
            if (count($users) == 1) {
                //该openid只有一个账号在用，重置session
                $this->setSession($users[0]['userid']);
            } else {
                redirect(C('FS_FOLDER_NAME') . '/#/chan_user');
                exit;
            }
        } else {
            //获取用户openid
            $url = get_code('abc');
            header('Content-type: application/json;charset=utf-8');
            header("Location: $url");
            exit;
        }
    }

    /**
     * Notes: 获取jsdk签名rKo6RGZitR6HLnFgsQAV0EVy4lWXXMLOD3h-e1Iph7g ww3622251fb26ae071  1000026
     */
    public function getSignature()
    {
        $jssdk = new WxAccessTokenModel();
        $signPackage = $jssdk->GetSignPackage();
        $result['status'] = 1;
        $result['signPackage'] = $signPackage;
        $this->ajaxReturn($result, 'json');
    }

    /**
     * Notes: 微信端设置session
     */
    public function setSession($userid, $returnJosn = 0)
    {
        $UserModel = new UserModel();
        $where['userid'] = $userid;
        $where['is_delete'] = 0;
        $where['status'] = 1;
        $user = $UserModel->DB_get_one('user', '*', $where);
        $res = $UserModel->setSession($user);
        if ($res['status'] == -1) {
            $this->ajaxReturn($res);
        } else {
            //重置session成功，跳转到上一次访问的地址或首页
            $pre_url = session('pre_url');
            $pre_url = str_replace('/index.php/', '', $pre_url);
            $pre_url = str_replace('.html', '', $pre_url);
            $pre_url = trim($pre_url,'/');
            if ($pre_url) {
                session('pre_url', null);
                redirect(C('FS_FOLDER_NAME') . '/#/'.$pre_url);
            } else {
                if ($returnJosn) {
                    $this->ajaxReturn($res);
                } else {
                    //跳到首页
                    redirect(C('FS_FOLDER_NAME') . '/#/');
                }
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
        $this->assign('url', C('FS_NAME') . '/' . $redirectUrl);
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
            $menu = get_menu('Assets', 'Scrap', 'examine');
            if (!$menu) {
                $show_scrap = 0;
            }
            $res['status'] = 1;
            $res['data'] = array('show_repair' => $show_repair, 'show_transfer' => $show_transfer, 'show_borrow' => $show_borrow, 'show_scrap' => $show_scrap);
            $this->ajaxReturn($res);
        }
    }

    /*
     * 维修流程 、 转科流程 、借调流程
     */
    public function progress()
    {
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'transfer':
                    $transferModel = new AssetsTransferModel();
                    $result = $transferModel->get_transfer_progress();
                    $this->ajaxReturn($result);
                    break;
                case 'repair':
                    $repairModel = new RepairModel();
                    $result = $repairModel->progress();
                    $this->ajaxReturn($result);
                    break;
                case 'borrow':
                    $borrowModel = new AssetsBorrowModel();
                    $result = $borrowModel->borrowLife();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
            $show_transfer = $show_repair = $show_borrow = 1;
            //查询是否有审批权限
            $menu = get_menu('Assets', 'Transfer', 'progress');
            if (!$menu) {
                $show_transfer = 0;
            }
            $menu = get_menu('Repair', 'Repair', 'progress');
            if (!$menu) {
                $show_repair = 0;
            }
            $menu = get_menu('Assets', 'Borrow', 'borrowLife');
            if (!$menu) {
                $show_borrow = 0;
            }
            $res['status'] = 1;
            $res['data'] = array('show_repair' => $show_repair, 'show_transfer' => $show_transfer, 'show_borrow' => $show_borrow);
            $this->ajaxReturn($res);
        }
    }

    public function changuser()
    {
        $openid = session('openid');
        $userModel = new UserModel();
        $users = $userModel->get_user_info($openid);
        $res['status'] = 1;
        $res['data'] = $users;
        $this->ajaxReturn($res);
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
        if (!$_SESSION['userid']) {
            redirect(C('FS_NAME')."/Notin/getUserOpenId");
            exit;
        }
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
        //快捷按钮
        $kBtn = $this->get_kuaijie();
        $this->ajaxReturn(['status' => 1, 'menu' => $menus, 'kBtn' => $kBtn]);
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
        $is_qrjr = get_menu('Assets', 'Borrow', 'borrowInCheck');
        $qrjr = get_menu('Assets', 'Borrow', 'borrowInCheckList');
        if ($is_qrjr) {
            if ($j % $num == 0) {
                $i++;
            }
            $j++;
            $qrjr['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/qrjr.png';
            $assets['swipe'][$i]['menu'][] = $qrjr;
        }
        //归还验收
        $is_ghys = get_menu('Assets', 'Borrow', 'giveBackCheck');
        $ghys = get_menu('Assets', 'Borrow', 'giveBackCheckList');
        if ($is_ghys) {
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
            $jdyq['actionurl'] = C('FS_NAME').'/Borrow/giveBackCheckList/showReminderList';
            $jdyq['actionname'] = '借调逾期';
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
            $smjx['actionurl'] = C('FS_NAME').'/Repair/overhaulLists';
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
    private function get_patrol_menus($num)
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

    //首页快捷按钮
    private function get_kuaijie()
    {
        $menus = [];
        //审批
        $sp = get_menu('Repair', 'Repair', 'addApprove');
        $trans = get_menu('Assets', 'Transfer', 'approval');
        $scrap = get_menu('Assets', 'Scrap', 'examine');
        $borrow = get_menu('Assets', 'Borrow', 'approveBorrow');
        if ($sp || $trans || $scrap || $borrow) {
            $sp['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/approve.png';
            $sp['actionname'] = '审批';
            $sp['actionurl'] = C('FS_NAME').'/Notin/approve';
            //统计待审批数量
            $sp['nums'] = $this->get_approves_nums();
            $menus[] = $sp;
        }

        //验收
        $ys_Repair = get_menu('Repair', 'Repair', 'checkRepair');
        $ys_Transfer = get_menu('Assets', 'Transfer', 'checkLists');
        $ys_inBorrow = get_menu('Assets', 'Borrow', 'borrowInCheckList');
        $ys_giveBorrow = get_menu('Assets', 'Borrow', 'giveBackCheckList');
        if ($ys_Repair || $ys_Transfer || $ys_inBorrow || $ys_giveBorrow) {
            $ys = array();
            $ys['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/check.png';
            $ys['actionname'] = '验收';
            $ys['actionurl'] = C('FS_NAME').'/Notin/check';
            //统计待验收数量
            $ys['nums'] = $this->get_check_nums();
            $menus[] = $ys;
        }

        //扫码报修
        $sabx = get_menu('Repair', 'Repair', 'addRepair');
        if ($sabx) {
            $sabx['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/sys.png';
            $sabx['actionname'] = '扫码报修';
            $menus[] = $sabx;
        }

        //进程
        $progress_Repair = get_menu('Repair', 'Repair', 'progress');
        $progress_Transfer = get_menu('Assets', 'Transfer', 'progress');
        $progress_Borrow = get_menu('Assets', 'Borrow', 'borrowLife');
        if ($progress_Repair || $progress_Transfer || $progress_Borrow) {
            $progress = array();
            $progress['actionname'] = '进程';
            $progress['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/progress.png';
            $progress['actionurl'] = C('FS_NAME').'/Notin/progress';
            //统计待验收数量
            $progress['nums'] = $this->get_progress_nums();
            $menus[] = $progress;
        }

        //到期
        $daoqi = [];
        if (1) {
            $daoqi['actionname'] = '到期';
            $daoqi['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/expire.png';
            $daoqi['actionurl'] = '';
            $menus[] = $daoqi;
        }

        //扫码检修
        $smjx = get_menu('Repair', 'Repair', 'accept');
        if ($smjx) {
            $smjx['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/smjx.png';
            $smjx['actionname'] = '扫码检修';
            $smjx['actionurl'] = C('FS_NAME').'/Repair/overhaulLists';
            $menus[] = $smjx;
        }

        //保养实施
        $patrol = [];
        if (1) {
            $patrol['actionname'] = '保养实施';
            $patrol['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/patrol.png';
            $patrol['actionurl'] = C('FS_NAME').'/Patrol/patrolList';
            $menus[] = $patrol;
        }
        //公告
        $gglb = get_menu('BaseSetting', 'Notice', 'getNoticeList');
        if ($gglb) {
            $gglb['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/notice.png';
            $gglb['actionname'] = '公告';
            $menus[] = $gglb;
        }

        //应急预案
        $yjya = get_menu('Archives', 'Emergency', 'emergencyPlanList');
        if ($yjya) {
            $yjya['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/emergency.png';
            $yjya['actionname'] = '应急预案';
            $menus[] = $yjya;
        }

        if (count($menus) >= 9) {
            //更多
            $more = [];
            if (1) {
                $more['actionname'] = '更多';
                $more['icon'] = C('APP_NAME') . '/Public/mobile/images/icon/more.png';
                $more['actionurl'] = '';
                $menus[] = $more;
            }
        }
        return $menus;
    }

    //获取待审批的数量
    public function get_approves_nums()
    {
        $nums = 0;
        $hospital_id = session('current_hospitalid');
        $departids = session('departid');
        //查询当前用户是否有维修审批权限
        $repair_menu = get_menu('Repair', 'Repair', 'addApprove');
        $repModel = new RepairModel();
        if ($repair_menu) {
            $repair_where['B.departid'] = array('IN', $departids);
            $repair_where['A.all_approver'] = array('LIKE', '%/' . session('username') . '/%');
            $repair_where['B.hospital_id'] = $hospital_id;
            $repair_where['A.approve_status'] = array('EQ', C('REPAIR_IS_NOTCHECK'));
            $repair_where['B.is_delete'] = '0';
            $repair_where['A.status'] = C('REPAIR_AUDIT');
            $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
            $repair_total_nums = $repModel->DB_get_count_join('repair', 'A', $join, $repair_where, '');
            $nums += $repair_total_nums;
        }

        //报废审核
        $Scrapmenu = get_menu('Assets', 'Scrap', 'examine');
        if ($Scrapmenu) {
            if ($departids) {
                $Scrap_where['B.departid'] = array('IN', $departids);
            }
            if ($hospital_id) {
                $Scrap_where['B.hospital_id'] = $hospital_id;
            }
            $Scrap_where['B.status'] = C('ASSETS_STATUS_SCRAP_ON');//报废中
            $Scrap_where['B.is_delete'] = C('NO_STATUS');
            $Scrap_where['A.all_approver'] = array('LIKE', '%/' . session('username') . '/%');
            $Scrap_where['A.approve_status'] = 0;//审批中

            $Scrap_join[0] = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
            $scrap_total_nums = $repModel->DB_get_count_join('assets_scrap', 'A', $Scrap_join, $Scrap_where);
            $nums += $scrap_total_nums;
        }

        //查询当前用户是否有转科审批权限
        $transfer_menu = get_menu('Assets', 'Transfer', 'approval');
        if ($transfer_menu) {
            $transfer_where['A.approve_status'] = 0;
            $transfer_where['B.hospital_id'] = $hospital_id;
            $transfer_where['A.all_approver'] = array('like', '%/' . session('username') . '/%');
            $map['A.tranout_departid'] = array('in', $departids);
            $map['A.tranin_departid'] = array('in', $departids);
            $map['_logic'] = 'or';
            $transfer_where['_complex'] = $map;
            $transfer_where['B.is_delete'] = C('NO_STATUS');
            //根据条件统计符合要求的数量
            $join[0] = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
            $total_trans_nums = $repModel->DB_get_count_join('assets_transfer', 'A', $join, $transfer_where, '');
            $nums += $total_trans_nums;
        }

        //借出部门审批
        $departApproveBorrowMenu = get_menu('Assets', 'Borrow', 'departApproveBorrow');
        //设备科审批
        $assetsApproveBorrowMenu = get_menu('Assets', 'Borrow', 'assetsApproveBorrow');
        if ($departApproveBorrowMenu || $assetsApproveBorrowMenu) {
            $borrow_where['A.examine_status'] = array('EQ', C('APPROVE_STATUS'));//未审核状态

            //有审批权限的设备
            $backAssid = [];
            //负责人的可审批设备
            $managerApproveAssid = [];
            //设备科的可审批设备
            $assetsApproveAssid = [];

            if ($departApproveBorrowMenu) {
                //有借出部门审批权限
                $managerWhere['departid'] = array('in', $departids);
                $managerWhere['manager'] = array('EQ', session('username'));
                $managerWhere['hospital_id'] = $hospital_id;
                $manager = $repModel->DB_get_all('department', 'departid,manager', $managerWhere);
                if ($manager) {
                    //负责的科室
                    $managerDepairtid = [];
                    foreach ($manager as $managerV) {
                        $managerDepairtid[] = $managerV['departid'];
                    }
                    $assetsDepartWhere['departid'] = array('IN', $managerDepairtid);
                    $assetsDepartWhere['is_delete'] = '0';
                    $assetsDepart = $repModel->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
                    if ($assetsDepart) {
                        foreach ($assetsDepart as &$assetsDepartV) {
                            $backAssid[] = $assetsDepartV['assid'];
                            $managerApproveAssid[$assetsDepartV['assid']] = true;
                        }
                    }
                }
            }

            if ($assetsApproveBorrowMenu) {
                //有设备科审批权限
                $assetsDepartWhere['departid'] = array('IN', $departids);
                $assetsDepartWhere['is_delete'] = '0';
                $assetsDepart = $repModel->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
                if ($assetsDepart) {
                    foreach ($assetsDepart as &$assetsDepartV) {
                        $backAssid[] = $assetsDepartV['assid'];
                        $assetsApproveAssid[$assetsDepartV['assid']] = true;
                    }
                }
            }

            if ($backAssid) {
                $backAssid = array_unique($backAssid);
                $assetsWhere['assid'][] = array('IN', $backAssid);
                //管理员默认情况下的话只能看到自己工作的医院下的设备
                $assetsWhere['hospital_id'] = $hospital_id;
                $assetsWhere['is_delete'] = C('NO_STATUS');
                $assets = $repModel->DB_get_all('assets_info', 'assid', $assetsWhere);
                if ($assets) {
                    $assetsAssid = [];
                    foreach ($assets as &$assetsAssidV) {
                        $assetsAssid[] = $assetsAssidV['assid'];
                    }
                    $borrow_where['A.assid'][] = array('IN', $assetsAssid);
                    //获取审批列表信息
                    $join = "LEFT JOIN sb_department AS B ON A.apply_departid = B.departid";
                    $total_borrow_nums = $repModel->DB_get_count_join('assets_borrow', 'A', $join, $borrow_where, '');
                    $nums += $total_borrow_nums;
                }
            }
        }
        return $nums < 100 ? $nums : '99+';
    }

    //获取待验收数量
    public function get_check_nums()
    {
        $nums = 0;
        $hospital_id = session('current_hospitalid');
        $departids = session('departid');
        //查询当前用户是否有维修验收权限
        $repair_menu = get_menu('Repair', 'Repair', 'checkRepair');
        $repModel = new RepairModel();
        if ($repair_menu) {
            $repair_where['B.departid'] = array('IN', $departids);
            $repair_where['B.hospital_id'] = $hospital_id;
            $repair_where['B.is_delete'] = C('NO_STATUS');
            $repair_where['A.status'] = array('in', [C('REPAIR_MAINTENANCE_COMPLETION')]);//待验收、转单确认
            $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
            $repair_total_nums = $repModel->DB_get_count_join('repair', 'A', $join, $repair_where, '');
            $nums += $repair_total_nums;
        }

        //查询当前用户是否有转科验收权限
        $transfer_menu = get_menu('Assets', 'Transfer', 'check');
        $repModel = new RepairModel();
        if ($transfer_menu) {
            $trans_where['B.departid'] = array('IN', $departids);
            $trans_where['B.hospital_id'] = $hospital_id;
            $trans_where['B.is_delete'] = C('NO_STATUS');
            if (!session('isSuper')) {
                $trans_where['B.status'] = ['NEQ', C('ASSETS_STATUS_SCRAP')];
                $trans_where['A.tranin_departid'] = array('in', $departids);
            } else {
                $trans_where['B.status'] = ['NEQ', C('ASSETS_STATUS_SCRAP')];
            }
            $trans_where['A.approve_status'][0] = 'IN';
            $trans_where['A.approve_status'][1][] = C('STATUS_APPROE_UNWANTED');//不需审批
            $trans_where['A.approve_status'][1][] = C('STATUS_APPROE_SUCCESS');//审批通过
            $trans_where['A.is_check'] = C('TRANSFER_IS_NOTCHECK');//未验收

            $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
            $trans_total_nums = $repModel->DB_get_count_join('assets_transfer', 'A', $join, $trans_where, '');
            $nums += $trans_total_nums;
        }

        //借入验收
        $borrow_menu = get_menu('Assets', 'Borrow', 'borrowInCheck');
        $repModel = new RepairModel();
        if ($borrow_menu) {
            $borrow_where['B.hospital_id'] = $hospital_id;
            $borrow_where['B.is_delete'] = C('NO_STATUS');
            $borrow_where['A.status'] = ['EQ', C('BORROW_STATUS_BORROW_IN')];
            if (session('isSuper') != C('YES_STATUS')) {
                $borrow_where['A.apply_departid'] = ['IN', session('job_departid')];
            }
            $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
            $borrow_total_nums = $repModel->DB_get_count_join('assets_borrow', 'A', $join, $borrow_where, '');
            $nums += $borrow_total_nums;
        }

        //归还验收
        $back_menu = get_menu('Assets', 'Borrow', 'giveBackCheck');
        $repModel = new RepairModel();
        if ($back_menu) {
            $assetsDepartWhere['departid'] = array('IN', session('departid'));
            $assetsDepartWhere['hospital_id'] = session('current_hospitalid');
            $assetsDepartWhere['is_delete'] = '0';
            $assetsDepart = $repModel->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
            $backAssid = [];
            foreach ($assetsDepart as &$assetsDepartV) {
                $backAssid[] = $assetsDepartV['assid'];
            }
            if ($backAssid) {
                $back_where['B.is_delete'] = C('NO_STATUS');
                $back_where['A.status'] = ['EQ', C('BORROW_STATUS_GIVE_BACK')];
                $back_where['A.assid'] = ['IN', $backAssid];
                $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
                $back_total_nums = $repModel->DB_get_count_join('assets_borrow', 'A', $join, $back_where, '');
                $nums += $back_total_nums;
            }
        }
        return $nums < 100 ? $nums : '99+';
    }

    //获取进程数量
    public function get_progress_nums()
    {
        $nums = 0;
        $hospital_id = session('current_hospitalid');
        $departids = session('departid');
        //查询当前用户是否有维修进程权限
        $repair_menu = get_menu('Repair', 'Repair', 'progress');
        $repModel = new RepairModel();
        if ($repair_menu) {
            $repair_where['A.status'][0] = 'IN';
            $repair_where['A.status'][1][] = C('REPAIR_HAVE_REPAIRED');//已报修待接单
            $repair_where['A.status'][1][] = C('REPAIR_RECEIPT');//已接单待检修的设备
            $repair_where['A.status'][1][] = C('REPAIR_HAVE_OVERHAULED');//已检修/配件待出库
            $repair_where['A.status'][1][] = C('REPAIR_AUDIT');//审核中
            $repair_where['A.status'][1][] = C('REPAIR_MAINTENANCE');//维修中
            $repair_where['A.status'][1][] = C('REPAIR_MAINTENANCE_COMPLETION');//待验收
            $repair_where['B.hospital_id'] = $hospital_id;
            $repair_where['B.departid'] = array('IN', $departids);
            $repair_where['B.is_delete'] = C('NO_STATUS');
            $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
            $repair_total_nums = $repModel->DB_get_count_join('repair', 'A', $join, $repair_where, '');
            $nums += $repair_total_nums;
        }

        //查询当前用户是否有转科进程权限
        $transfer_menu = get_menu('Assets', 'Transfer', 'progress');
        if ($transfer_menu) {
            $transfer_where['B.hospital_id'] = $hospital_id;
            $transfer_where['B.status'] = 6;
            $transfer_where['B.departid'] = array('IN', $departids);
            $join[0] = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
            $transfer_total_nums = $repModel->DB_get_count_join('assets_transfer', 'A', $join, $transfer_where, '');
            $nums += $transfer_total_nums;
        }

        //查询当前用户是否有借调进程权限
        $borrow_menu = get_menu('Assets', 'Borrow', 'borrowLife');
        if ($borrow_menu) {
            $assetsDepartWhere['hospital_id'] = $hospital_id;
            $assetsDepart = $repModel->DB_get_all('assets_info', 'assid', $assetsDepartWhere);
            if ($assetsDepart) {
                $backAssid = [];
                foreach ($assetsDepart as &$assetsDepartV) {
                    $backAssid[] = $assetsDepartV['assid'];
                }
                $borrow_where['assid'] = array('IN', $backAssid);
                $startTime = strtotime(date("Ymd"));
                $endTime = $startTime + 86399;
                $showStatus[] = C('BORROW_STATUS_APPROVE');
                $showStatus[] = C('BORROW_STATUS_BORROW_IN');
                $showStatus[] = C('BORROW_STATUS_GIVE_BACK');
                //正常流程的设备
                $borrow_where[1][1]['status'] = array('IN', $showStatus);

                //或者当天结束的设备
                //1.完成验收
                $borrow_where[1][2][1]['status'] = array('EQ', C('BORROW_STATUS_COMPLETE'));
                $borrow_where[1][2][1][]['give_back_time'] = array('EGT', $startTime);
                $borrow_where[1][2][1][]['give_back_time'] = array('ELT', $endTime);

                //2.不借调
                $borrow_where[1][2][2]['status'] = array('EQ', C('BORROW_STATUS_NOT_APPLY'));
                $borrow_where[1][2][2][]['not_apply_time'] = array('EGT', $startTime);
                $borrow_where[1][2][2][]['not_apply_time'] = array('ELT', $endTime);

                //3.审批不通过
                $borrow_where[1][2][3]['status'] = array('EQ', C('BORROW_STATUS_FAIL'));
                $borrow_where[1][2][3][]['examine_time'] = array('EGT', $startTime);
                $borrow_where[1][2][3][]['examine_time'] = array('ELT', $endTime);

                $borrow_where[1][2]['_logic'] = 'or';
                $borrow_where[1]['_logic'] = 'or';

                $borrow_total_nums = $repModel->DB_get_count('assets_borrow', $borrow_where, '');
                $nums += $borrow_total_nums;
            }
        }
        return $nums < 100 ? $nums : '99+';
    }

    public function check()
    {
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'transfer':
                    $transferModel = new AssetsTransferModel();
                    $result = $transferModel->get_transfer_checklist();
                    $this->ajaxReturn($result);
                    break;
                case 'repair':
                    $repairModel = new RepairModel();
                    $result = $repairModel->examine();
                    $this->ajaxReturn($result);
                    break;
                case 'give_borrow':
                    $scrapModel = new AssetsBorrowModel();
                    $result = $scrapModel->giveBackCheckList();
                    $this->ajaxReturn($result);
                    break;
                case 'in_borrow':
                    $borrowModel = $repairModel = new AssetsBorrowModel();
                    $result = $borrowModel->borrowInCheckList();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
            $show_transfer = $show_give_borrow = $show_repair = $show_in_borrow = 1;
            //查询是否有审批权限
            $menu = get_menu('Repair', 'Repair', 'checkRepair');
            if (!$menu) {
                $show_repair = 0;
            }
            $menu = get_menu('Assets', 'Transfer', 'check');
            if (!$menu) {
                $show_transfer = 0;
            }
            $menu = get_menu('Assets', 'Borrow', 'borrowInCheck');
            if (!$menu) {
                $show_in_borrow = 0;
            }
            $menu = get_menu('Assets', 'Borrow', 'giveBackCheck');
            if (!$menu) {
                $show_give_borrow = 0;
            }
            $res['status'] = 1;
            $res['data'] = array('show_repair' => $show_repair, 'show_transfer' => $show_transfer, 'show_in_borrow' => $show_in_borrow, 'show_give_borrow' => $show_give_borrow);
            $this->ajaxReturn($res);
        }
    }

    public function testAbc()
    {
        $result['okk'] = '1';
        return $result;
    }

    public function uploadReport()
    {
        $action = I('POST.action');
        if ($action == "upload") {
            # code...
            $table_name = I('post.t');
            //上传设备图片
            $Tool = new ToolController();
            //设置文件类型
            $type = array('jpg', 'png', 'bmp', 'jpeg', 'gif');
            //报告保存地址
            $dirName = '';
            $is_water = false;
            $is_compression = false;
            $water_text = [];
            switch ($table_name) {
                case 'assets_scrap':
                    $dirName = C('UPLOAD_DIR_REPORT_SCRAP_NAME');
                    break;
                case 'assets_transfer':
                    $dirName = C('UPLOAD_DIR_REPORT_TRANSFER_NAME');
                    break;
                case 'quality_details':
                    $dirName = C('UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME');
                    break;
                case 'assets_info':
                    $dirName = C('UPLOAD_DIR_ASSETS_NAME');
                    $is_compression = true;
                    break;
                case 'assets_outside_file':
                    $dirName = C('UPLOAD_DIR_OUTSIDE_NAME');
                    break;
                case 'quality_details_file':
                    $dirName = C('UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME');
                    $is_water = true;
                    $quaModel = new QualityModel();
                    $watermark = $quaModel->DB_get_one('base_setting', 'value', array('module' => 'repair', 'set_item' => 'repair_print_watermark'));
                    $watermark = json_decode($watermark['value'], true);
                    $qsinfo = $quaModel->DB_get_one('quality_starts', 'plan_num', array('qsid' => I('post.id')));
                    $water_text[0] = $watermark['watermark'];
                    $water_text[1] = $qsinfo['plan_num'];
                    $water_text[2] = date('Y-m-d H:i:s');
                    break;
            }
            //上传文件
            $base64 = I('POST.base64');
            if ($base64) {
                $upload = $Tool->base64imgsave($base64, $dirName);
            } else {
                $upload = $Tool->upFile($type, $dirName, $is_water, $water_text, $is_compression);
            }
            $asmodel = new \Admin\Model\AssetsInfoModel();
            $assid = I('POST.assid');
            $save = $asmodel->uploadAssetsPic($assid, date('Ymd') . '/' . $upload['name']);
            $type = I('POST.type');
            if ($type) {
                $asmodel->updateData('assets_info', array('print_status' => $type), ['assid' => $assid]);
            }
            $result['status'] = 1;
            $result['url'] = $upload['src'];
            $result['msg'] = '上传成功';

            $this->ajaxReturn($result);
        } else {
            $asModel = new \Admin\Model\AssetsInfoModel();
            $result = $asModel->deleteAssetsPic();
            $this->ajaxReturn($result);
        }
    }

    public function getKey()
    {
        $pubkey = file_get_contents('Public/key/rsa_1024_pub.pem');
        $this->ajaxReturn(['status' => 1, 'data' => $pubkey]);
    }

    /**
     * Notes: 首页扫一扫
     */
    public function todo()
    {
        $departid = session('departid');
        $assnum = I('get.assnum');
        $asModel = new \Fs\Model\AssetsInfoModel();
        $filds = 'assid,assnum,assets,model,assorignum,serialnum,catid,departid,status';
        $asInfo = $asModel->DB_get_one('assets_info', $filds, array('assnum' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
        if (!$asInfo) {
            $asInfo = $asModel->DB_get_one('assets_info', $filds, array('assorignum' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
        }
        if (!$asInfo) {
            $asInfo = $asModel->DB_get_one('assets_info', $filds, array('assorignum_spare' => $assnum, 'hospital_id' => session('current_hospitalid'), 'is_delete' => '0'));
        }
        if (!$asInfo) {
            $result['status'] = 302;
            $msg['tips'] = '查找不到设备信息';
            $msg['url'] = '';
            $msg['btn'] = '';
            $result['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
        }
        if ($asInfo['status'] != C('ASSETS_STATUS_TRANSFER_ON')) {
            //设备状态不是转科中
            if (!in_array($asInfo['departid'], explode(',', $departid))) {
                $result['status'] = 302;
                $msg['tips'] = '您无权操作该科室设备';
                $msg['url'] = '';
                $msg['btn'] = '';
                $result['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result);
            }
        }
        $catname = array();
        $departname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $asInfo['department'] = $departname[$asInfo['departid']]['department'];
        $asInfo['cate_name'] = $catname[$asInfo['catid']]['category'];
        $asInfo['cate_name'] = $catname[$asInfo['catid']]['category'];
        $asInfo['status_name'] = $asModel->getStatus($asInfo['status']);
        $res['menus'] = [];
        $i = 0;
        switch ($asInfo['status']) {
            case C('ASSETS_STATUS_USE')://0在用
                //报修
                $menu = get_menu('Repair', 'Repair', 'addRepair');
                if ($menu) {
                    //有权限报修
                    $res['menus'][$i]['name'] = $menu['actionname'];
                    $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?assnum=' . $asInfo['assnum'];
                    $i++;
                }
                //转科
                $menu = get_menu('Assets', 'Transfer', 'add');
                if ($menu) {
                    //有权限转科
                    $res['menus'][$i]['name'] = $menu['actionname'];
                    $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?assnum=' . $asInfo['assnum'];
                    $i++;
                }
                //报废
                $menu = get_menu('Assets', 'Scrap', 'applyScrap');
                if ($menu) {
                    //有权限报废
                    $res['menus'][$i]['name'] = $menu['actionname'];
                    $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?assnum=' . $asInfo['assnum'];
                    $i++;
                }
                //借调
                $menu = get_menu('Assets', 'Borrow', 'applyBorrow');
                if ($menu) {
                    //有权限借调
                    $res['menus'][$i]['name'] = $menu['actionname'];
                    $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?assid=' . $asInfo['assid'];
                    $i++;
                }
                break;
            case C('ASSETS_STATUS_REPAIR')://1维修中
                //查询设备维修信息
                $repInfo = $asModel->DB_get_one('repair', 'repid,status,response,current_approver', ['assid' => $asInfo['assid'], 'status' => array('neq', C('REPAIR_ALREADY_ACCEPTED'))]);
                switch ($repInfo['status']) {
                    case C('REPAIR_HAVE_REPAIRED')://1待接单
                        //接单
                        $menu = get_menu('Repair', 'Repair', 'accept');
                        if ($menu) {
                            //有权限接单
                            $res['menus'][$i]['name'] = $menu['actionname'];
                            $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?repid=' . $repInfo['repid'];
                            $i++;
                        }
                        break;
                    case C('REPAIR_RECEIPT')://2已接单待检修
                        //检修
                        $menu = get_menu('Repair', 'Repair', 'accept');
                        if ($menu) {
                            //有权限检修
                            $res['menus'][$i]['name'] = $menu['actionname'];
                            $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?repid=' . $repInfo['repid'] . '&action=overhaul';
                            $i++;
                        }
                        break;
                    case C('REPAIR_HAVE_OVERHAULED')://3已检修/配件待出库
                        //出库
                        $menu = get_menu('RepairParts', 'RepairParts', 'partsOutWare');
                        if ($menu) {
                            //有权限出库
                            $res['menus'][$i]['name'] = $menu['actionname'];
                            $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?repid=' . $repInfo['repid'];
                            $i++;
                        }
                        break;
                    case C('REPAIR_AUDIT')://5审核中
                        //审核
                        $menu = get_menu('Repair', 'Repair', 'addApprove');
                        if ($menu) {
                            //有权限审核
                            $cuer = explode(',', $repInfo['current_approver']);
                            if (in_array(session('username'), $cuer)) {
                                $res['menus'][$i]['name'] = $menu['actionname'];
                                $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?repid=' . $repInfo['repid'];
                                $i++;
                            }
                        }
                        break;
                    case C('REPAIR_MAINTENANCE')://6继续维修
                        //维修
                        $menu = get_menu('Repair', 'Repair', 'startRepair');
                        if ($menu) {
                            //有权限维修
                            if (session('isSuper') || session('username') == $repInfo['response']) {
                                $res['menus'][$i]['name'] = $menu['actionname'];
                                $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?repid=' . $repInfo['repid'];
                                $i++;
                            }
                        }
                        break;
                    case C('REPAIR_MAINTENANCE_COMPLETION')://7待验收
                        //验收
                        $menu = get_menu('Repair', 'Repair', 'checkRepair');
                        if ($menu) {
                            //有权限验收
                            $res['menus'][$i]['name'] = $menu['actionname'];
                            $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?repid=' . $repInfo['repid'];
                            $i++;
                        }
                        break;
                }
                break;
            case C('ASSETS_STATUS_SCRAP')://3已报废
                //报废处置手机端不能操作
                break;
            case C('ASSETS_STATUS_SCRAP_ON')://5报废中
                //查询报废信息
                $scrInfo = $asModel->DB_get_one('assets_scrap', 'scrid,retrial_status,approve_status,current_approver', ['assid' => $asInfo['assid']]);
                if ($scrInfo['approve_status'] == 0) {
                    //审核中
                    $menu = get_menu('Assets', 'Scrap', 'examine');
                    if ($menu) {
                        //有权限审核
                        $cuer = explode(',', $scrInfo['current_approver']);
                        if (in_array(session('username'), $cuer)) {
                            $res['menus'][$i]['name'] = $menu['actionname'];
                            $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?scrid=' . $scrInfo['scrid'];
                            $i++;
                        }
                    }
                }
                if ($scrInfo['approve_status'] == 2 && $scrInfo['retrial_status'] == 1) {
                    //审核不通过，可以申请重审、或直接结束进程
                    $asInfo['status_name'] .= '（审批不通过）';
                    $menu = get_menu('Assets', 'Scrap', 'applyScrap');
                    if ($menu) {
                        //有权限申请报废
                        $res['menus'][$i]['name'] = '申请重审 / 结束进程';
                        $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?scrid=' . $scrInfo['scrid'];
                        $i++;
                    }
                }
                break;
            case C('ASSETS_STATUS_TRANSFER_ON')://6转科中
                //查询转科信息
                $transInfo = $asModel->DB_get_one('assets_transfer', 'atid,retrial_status,approve_status,current_approver,is_check,tranout_departid,tranin_departid', ['assid' => $asInfo['assid']]);
                if (!in_array($transInfo['tranout_departid'], explode(',', $departid)) || !in_array($transInfo['tranin_departid'], explode(',', $departid))) {
                    //转出科室或转入科室都不在自己管理科室范围内
                    $result['status'] = 302;
                    $msg['tips'] = '您无权操作该科室设备';
                    $msg['url'] = '';
                    $msg['btn'] = '';
                    $result['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
                    $this->ajaxReturn($result);
                }
                if ($transInfo['approve_status'] == 0) {
                    //审核中
                    $menu = get_menu('Assets', 'Transfer', 'approval');
                    if ($menu) {
                        //有权限审核
                        $cuer = explode(',', $transInfo['current_approver']);
                        if (in_array(session('username'), $cuer)) {
                            $res['menus'][$i]['name'] = $menu['actionname'];
                            $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?atid=' . $transInfo['atid'];
                            $i++;
                        }
                    }
                }
                if ($transInfo['approve_status'] == 2 && $transInfo['retrial_status'] == 1) {
                    //审核不通过，可以申请重审、或直接结束进程
                    $asInfo['status_name'] .= '（审批不通过）';
                    $menu = get_menu('Assets', 'Transfer', 'add');
                    if ($menu) {
                        //有权限申请转科
                        $res['menus'][$i]['name'] = '申请重审 / 结束进程';
                        $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?atid=' . $transInfo['atid'];
                        $i++;
                    }
                }
                if ($transInfo['approve_status'] == 1 && $transInfo['is_check'] == 0) {
                    //审批通过，未验收
                    if (in_array($transInfo['tranin_departid'], explode(',', $departid))) {
                        //转入科室在自己管理科室范围内
                        //验收
                        $menu = get_menu('Assets', 'Transfer', 'check');
                        if ($menu) {
                            //有权限验收
                            $res['menus'][$i]['name'] = $menu['actionname'];
                            $res['menus'][$i]['to_url'] = $menu['actionurl'] . '?atid=' . $transInfo['atid'];
                            $i++;
                        }
                    }
                }
                break;
        }
        $res['status'] = 1;
        $res['asInfo'] = $asInfo;
        $this->ajaxReturn($res, 'json');
    }

    /**
     * Notes: 下载微信语音到本地服务器
     */
    public function wxRecordDown()
    {
        $mdi = I('post.mid');
        //\Think\Log::write('media_id------='.$mdi);
        $repairModel = new \Admin\Model\RepairModel();
        $wxModel = new WxAccessTokenModel();
        $access_token = $wxModel->getAccessToken();

        $dirName = C('UPLOAD_DIR_RECORD_REPAIR_NAME') . '/' . date('Ymd');
        //\Think\Log::write('dirName='.$dirName);
        $dirarr = explode('/', $dirName);
        $tmpdir = './Public/uploads/';
        foreach ($dirarr as $v) {
            $tmpdir .= $v . '/';
            mkdir($tmpdir, 0777);
            chmod($tmpdir, 0777);
        }
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=" . $access_token . "&media_id=" . $mdi;

        \Think\Log::write('record_url=' . $url);

        $raw = file_get_contents($url);
        sleep(3);
        $res = json_decode($raw, true);
        if ($res['errcode'] != 0) {
            $this->ajaxReturn(array('status' => -1, 'msg' => '下载语音失败！' . $res['errmsg']));
        }
        $file_path = './Public/uploads/' . $dirName . '/' . $mdi . '.amr';
        file_put_contents($file_path, $raw);
        if (file_exists($file_path)) {
            //转换为mp3
            $amr = './Public/uploads/' . $dirName . '/' . $mdi;
            $mp3 = $amr . '.mp3';
            $mediaInfo = $this->amrToMp3($amr, $mp3);
            // 下载成功
            $data['seconds'] = $mediaInfo['seconds'];
            $data['record_url'] = '/Public/uploads/' . $dirName . '/' . $mdi . '.mp3';
            $data['add_user'] = session('username');
            $data['add_time'] = date('Y-m-d H:i:s');
            $res = $repairModel->insertData('repair_record', $data);
            $return_data['record_url'] = '/Public/uploads/' . $dirName . '/' . $mdi . '.mp3';
            $return_data['seconds'] = ceil($mediaInfo['seconds']);
            $this->ajaxReturn(array('status' => 1, 'msg' => '成功！', 'info' => $return_data));
        } else {
            $this->ajaxReturn(array('status' => -1, 'msg' => '失败！'));
        }
    }

    /**
     * Notes: amr 转码为 mp3
     * @param $amr
     * @param $mp3
     * @return array
     */
    public function amrToMp3($amr, $mp3)
    {
        $amr = $amr . '.amr';
        if (file_exists($amr)) {
            shell_exec("ffmpeg -i $amr $mp3");
            //删除原文件
            shell_exec("rm -rf " . $amr);
        }
        $info = $this->getMedioInfo($mp3);
        return $info;
    }

    /**
     * Notes: 获取多媒体信息
     * @param $file
     * @return array
     */
    public function getMedioInfo($file)
    {
        $command = sprintf('ffmpeg -i "%s" 2>&1', $file);//你的安装路径

        ob_start();
        passthru($command);
        $info = ob_get_contents();
        ob_end_clean();

        $data = array();
        if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
            $data['duration'] = $match[1]; //播放时间
            $arr_duration = explode(':', $match[1]);
            $data['seconds'] = $arr_duration[0] * 3600 + $arr_duration[1] * 60 + $arr_duration[2]; //转换播放时间为秒数
            $data['start'] = $match[2]; //开始时间
            $data['bitrate'] = $match[3]; //码率(kb)
        }
        if (preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $info, $match)) {
            $data['vcodec'] = $match[1]; //视频编码格式
            $data['vformat'] = $match[2]; //视频格式
            $data['resolution'] = $match[3]; //视频分辨率
            $arr_resolution = explode('x', $match[3]);
            $data['width'] = $arr_resolution[0];
            $data['height'] = $arr_resolution[1];
        }
        if (preg_match("/Audio: (\w*), (\d*) Hz/", $info, $match)) {
            $data['acodec'] = $match[1]; //音频编码
            $data['asamplerate'] = $match[2]; //音频采样频率
        }
        if (isset($data['seconds']) && isset($data['start'])) {
            $data['play_time'] = $data['seconds'] + $data['start']; //实际播放时间
        }
        $data['size'] = filesize($file); //文件大小
        return $data;
    }

    //首页搜索框
    public function search()
    {
        $key_word = trim(I('get.keyword'));
        $type = trim(I('get.type'));
        $limit = I('get.limit') ? I('get.limit') : C('PAGE_NUMS');
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        if (!$key_word) {
            $result['status'] = 302;
            $msg['tips'] = '参数错误';
            $msg['url'] = '';
            $msg['btn'] = '';
            $result['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
            $this->ajaxReturn($result);
        }
        $departids = session('departid');
        $where['A.departid'] = ['in', $departids];
        $hospital_id = session('current_hospitalid');
        $where['A.is_delete'] = 0;
        $where['A.status'][0] = 'NOT IN';
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');//外调中
        $where['A.hospital_id'] = $hospital_id;
        if (!$type) {
            if ($key_word) {
                $map['A.assets'] = ['like', "%$key_word%"];
                $map['A.assnum'] = ['like', "%$key_word%"];
                $map['B.department'] = ['like', "%$key_word%"];
                $map['_logic'] = 'or';
                $where['_complex'] = $map;
            } else {
                $result['status'] = 302;
                $msg['tips'] = '请输入搜索条件';
                $msg['url'] = '';
                $msg['btn'] = '';
                $result['msg'] = json_encode($msg, JSON_UNESCAPED_UNICODE);
                $this->ajaxReturn($result);
            }
        } else {
            switch ($type) {
                case 'assets':
                    $where['A.assets'] = ['like', "%$key_word%"];
                    break;
                case 'assnum':
                    $where['A.assnum'] = ['like', "%$key_word%"];
                    break;
                case 'department':
                    $where['B.department'] = ['like', "%$key_word%"];
                    break;
            }
        }
        $fields = "A.assid,A.assets,A.assnum,A.departid,A.model,A.status,A.pic_url,B.department";
        $join = "LEFT JOIN sb_department AS B ON A.departid = B.departid";
        $asModel = new \Fs\Model\AssetsInfoModel();
        $total = $asModel->DB_get_count_join('assets_info', 'A', $join, $where, '');
        $assets = $asModel->DB_get_all_join('assets_info', 'A', $fields, $join, $where, '', '', $offset . "," . $limit);
        if (!$assets) {
            $result['status'] = 1;
            $result['total'] = 0;
            $this->ajaxReturn($result);
        }
        $result['total'] = (int)$total;
        $result['page'] = (int)$page;
        $result['pages'] = (int)ceil($total / $limit);
        $result['status'] = 1;
        $result['rows'] = $assets;
        $this->ajaxReturn($result);
    }

    /** 文件上传
     *
     * @param1 $type array 设置允许上传的格式
     * @param2 $dirName string 文件夹名称
     * @return  array
     *
     */
    public function upFile()
    {
        \Think\Log::write('file======');
        \Think\Log::write('file======'.json_encode($_POST));
        //实例化上传类
        $upload = new \Think\Upload();
        //设置上传大小
        $upload->maxSize = 31457280000;
        // 设置上传目录
        $dirName = 'feishu';
        $path = './Public/uploads/' . $dirName . '/';
        if (!file_exists($path)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            $dirarr = explode('/', $dirName);
            $tmpdir = './Public/uploads/';
            foreach ($dirarr as $v) {
                $tmpdir .= $v . '/';
                mkdir($tmpdir, 0777);
                chmod($tmpdir, 0777);
            }
        }
        $upload->rootPath = $path;
        $upload->savePath = '';
        $upload->autoSub = false;
        //设置保存的文件名
        $upload->saveName = array('uniqid', '');
        //上传文件
        $upload->autoSub = true;
        $upload->subName = array('date', 'Ymd');
        $info = $upload->upload($_FILES);
        \Think\Log::write('file======-------------'.json_encode($_FILES));
        \Think\Log::write('file======-------------'.json_encode($info));
        if (!$info) {
            $result['status'] = -999;
            $result['msg'] = $upload->getError();
            $this->error();
        } else {
            if (!$info['file']) {
                //layer 特殊
                $info['file'] = $info['file_url'];
            }
            //地址
            $img['src'] = '/Public/uploads/' . $dirName . '/' . $info['file']['savepath'] . $info['file']['savename'];
            //生成文件名
            $img['title'] = $info['file']['savename'];
            //存入sql的名字
            $img['name'] = $info['file']['savepath'] . $info['file']['savename'];
            //原文件名
            $img['formerly'] = $info['file']['name'];
            //后缀名
            $img['ext'] = $info['file']['ext'];
            //文件大小
            $img['size'] = $info['file']['size'];
            $img['status'] = C('YES_STATUS');
            return $img;
        }
    }

    /**
     * 科室扫码签到
     */
    public function depart_sign()
    {
        $depart_id = I('get.id');
        $label_name = I('get.name');
        if (!$_SESSION['userid']) {
            session('pre_url', $_SERVER['REQUEST_URI']);
            $url = C('APP_NAME').C('FS_NAME').'/Notin/getUserOpenId';
            header("Location: $url");
            exit;
        }
        $pre_url = session('pre_url');
        $pre_url = str_replace('/index.php/', '', $pre_url);
        $pre_url = str_replace('.html', '', $pre_url);
        $pre_url = trim($pre_url,'/');
        if ($pre_url) {
            session('pre_url', null);
            redirect(U($pre_url));
            exit;
        }
        $userModel =  new UserModel();
        $indata['depart_id'] = $depart_id;
        $indata['label_name'] = $label_name;
        $indata['scan_userid'] = $_SESSION['userid'];
        $indata['scan_date'] = date('Y-m-d');;
        $indata['scan_time'] = date('Y-m-d H:i:s');
        $res = $userModel->insertData('user_trajectory',$indata);
        if($res){
            exit('签到成功！');
        }else{
            exit('签到失败！');
        }
    }
}
