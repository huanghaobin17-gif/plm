<?php

namespace Admin\Controller\BaseSetting;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\ModuleModel;
use Admin\Model\NoticeModel;
use Common\Weixin\Weixin;
use Mobile\Model\WxAccessTokenModel;

class NoticeController extends CheckLoginController
{
    /**
     * 系统公告栏列表
     */
    public function getNoticeList()
    {
        if (IS_POST) {
            $noticeModel = new NoticeModel();
            $result = $noticeModel->getNoticeData();
            $this->ajaxReturn($result, 'json');
        } else {
            switch (I('get.type')) {
                case '';
                    $this->assign('getNoticeList', get_url());
                    $this->display();
                    break;
                case 'showNotice';
                    $noticeModel = new NoticeModel();
                    $notid = I('get.notid');
                    $noticeInfo = $noticeModel->DB_get_one('notice', '', array('notid' => $notid), '');
                    $noticeInfo['content'] = htmlspecialchars_decode($noticeInfo['content']);
                    $this->assign('noticeInfo', $noticeInfo);
                    if ($noticeInfo['send_user_id'] != '') {
                        $judgeArr = explode(',', $noticeInfo['send_user_id']);
                        $where['job_hospitalid'] = session('current_hospitalid');
                        $where['status'] = 1;
                        $where['is_delete'] = 0;
                        $where['userid'] = ['IN', $judgeArr];
                        $where['is_super'] = 0;
                        $username = $noticeModel->DB_get_one('user', 'group_concat(username) AS username', $where);
                        $this->assign('is_look_username', $username);
                        $userid = session('userid');
                        if (in_array($userid, $judgeArr) || session('isSuper') == 1) {
                            $files = $noticeModel->getNoticeFile($notid);
                            $this->assign('files', $files);
                        }
                    } else {
                        $files = $noticeModel->getNoticeFile($notid);
                        $this->assign('files', $files);
                    }
                    $this->display('showNotice');
                    break;
            }
        }

    }

    /**
     * 发布公告
     */
    public function addNotice()
    {
        $noticeModel = new NoticeModel();
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'uploadFile':
                    //上传设备图片
                    $Tool = new ToolController();
                    //设置文件类型
                    $type = array('pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'csv');
                    //维修文件名目录设置
                    $dirName = C('UPLOAD_DIR_NOTICE_NAME');
                    //上传文件
                    $upload = $Tool->upFile($type, $dirName);
                    $this->ajaxReturn($upload);
                    break;
                default:
                    if (I('post.title')) {
                        $addData['title'] = I('post.title');
                    } else {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '请输入标题'));
                    }
                    $addData['top'] = I('post.top');
                    $addData['adduser'] = I('post.adduser');
                    $addData['adduserid'] = I('post.adduserid');
                    $addData['adddate'] = I('post.adddate');
                    if (I('post.content')) {
                        $addData['content'] = I('post.content');
                    } else {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '请输入公告正文'));
                    }
                    $addData['hospital_id'] = session('current_hospitalid');
                    //新增发送用户记录id
                    if (I('post.sendUserId')) {
                        $addData['send_user_id'] = I('post.sendUserId') . ',' . session('userid');
                    } else {
                        $addData['send_user_id'] = session('userid');
                    }
                    $addid = $noticeModel->insertData('notice', $addData);
                    //日志行为记录文字
                    $log['title'] = $addData['title'];
                    $text = getLogText('addNoticeLogText', $log);
                    $noticeModel->addLog('notice', M()->getLastSql(), $text, $addid, '');
                    if ($addid) {
                        //保存文件
                        $file_name = I('post.file_name');
                        $save_name = I('post.save_name');
                        $file_type = I('post.file_type');
                        $file_size = I('post.file_size');
                        $file_url = I('post.file_url');
                        foreach ($file_name as $k => $v) {
                            $file_add[$k]['notid'] = $addid;
                            $file_add[$k]['file_name'] = $v;
                            $file_add[$k]['save_name'] = $save_name[$k];
                            $file_add[$k]['file_type'] = $file_type[$k];
                            $file_add[$k]['file_size'] = $file_size[$k];
                            $file_add[$k]['file_url'] = $file_url[$k];
                            $file_add[$k]['add_time'] = date('Y-m-d H:i:s');
                            $file_add[$k]['add_user'] = session('username');
                        }
                        if ($file_add) {
                            $noticeModel->insertDataALL('notice_file', $file_add);
                        }
                        if(C('USE_FEISHU') === 1){
                            //==========================================飞书 START========================================
                            //要显示的字段区域
                            $fd['is_short'] = false;//是否并排布局
                            $fd['text']['tag'] = 'lark_md';
                            $fd['text']['content'] = '**公告标题：**'.$addData['title'];
                            $feishu_fields[] = $fd;

                            $fd['is_short'] = false;//是否并排布局
                            $fd['text']['tag'] = 'lark_md';
                            $fd['text']['content'] = '**发布时间：**'.date('Y-m-d H:i:s');
                            $feishu_fields[] = $fd;

                            //按钮区域
                            $act['tag'] = 'button';
                            $act['type'] = 'primary';
                            $act['url'] = C('APP_NAME') . C('FS_FOLDER_NAME').'/#' . C('FS_NAME') . '/Notice/getNoticeList?id=' . $addid . '&action=showNotice';
                            $act['text']['tag'] = 'plain_text';
                            $act['text']['content'] = '查看详情';
                            $feishu_actions[] = $act;

                            $card_data['config']['enable_forward'] = false;//是否允许卡片被转发
                            $card_data['config']['wide_screen_mode'] = true;//是否根据屏幕宽度动态调整消息卡片宽度
                            $card_data['elements'][0]['tag'] = 'div';
                            $card_data['elements'][0]['fields'] = $feishu_fields;
                            $card_data['elements'][1]['tag'] = 'hr';
                            $card_data['elements'][2]['actions'] = $feishu_actions;
                            $card_data['elements'][2]['layout'] = 'bisected';
                            $card_data['elements'][2]['tag'] = 'action';
                            $card_data['header']['template'] = 'red';
                            $card_data['header']['title']['content'] = '系统公告';
                            $card_data['header']['title']['tag'] = 'plain_text';

                            $where['is_delete'] = 0;
                            $where['status'] = 1;
                            $where['job_hospitalid'] = session('current_hospitalid');
                            if (I('post.sendUserId')) {
                                $where['userid'] = ['IN', I('post.sendUserId') . ',' . session('userid')];
                            }
                            $toUser = $noticeModel->DB_get_all('user', 'openid', $where);
                            $alerady_send = [];
                            foreach ($toUser as $v) {
                                if ($v['openid'] && !in_array($v['openid'], $alerady_send)) {
                                    $noticeModel->send_feishu_card_msg($v['openid'],$card_data);
                                    $alerady_send[] = $v['openid'];
                                }
                            }
                            //==========================================飞书 END==========================================
                        }else{
                            //发布成功微信通知相关人员
                            //==========================================微信 START==========================================
                            $moduleModel = new ModuleModel();
                            $wx_status = $moduleModel->decide_wx_login();
                            if ($wx_status) {
                                if(C('USE_VUE_WECHAT_VERSION')){
                                    $redecturl = C('APP_NAME') . C('VUE_FOLDER_NAME').'/#' . C('VUE_NAME') . '/Notice/getNoticeList?id=' . $addid . '&action=showNotice';
                                }else{
                                    $redecturl = C('HTTP_HOST') . C('MOBILE_NAME') . '/Notice/getNoticeList.html?id=' . $addid . '&action=showNotice';
                                }
                                $where['is_delete'] = 0;
                                $where['status'] = 1;
                                $where['job_hospitalid'] = session('current_hospitalid');
                                if (I('post.sendUserId')) {
                                    $where['userid'] = ['IN', I('post.sendUserId') . ',' . session('userid')];
                                }
                                $toUser = $noticeModel->DB_get_all('user', 'openid', $where);
                                $openIds = array_column($toUser, 'openid');
                                $openIds = array_filter($openIds);
                                $openIds = array_unique($openIds);

                                $messageData = [
                                    'thing10' => '系统公告',// 工单类型
                                    'thing9'  => $addData['title'],// 工单名称
                                    'time39'  => getHandleMinute(strtotime($addData['adddate'])),// 发起时间
                                    'thing7'  => session('username'),// 发起人员
                                    'const56' => '',// 工单阶段
                                ];

                                foreach ($openIds as $openId) {
                                    Weixin::instance()->sendMessage($openId, '工单处理提醒', $messageData, $redecturl);
                                }
                            }
                            //==========================================微信 END==========================================
                        }
                        $this->ajaxReturn(array('status' => 1, 'msg' => '发布成功'));
                    } else {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '发布失败'));
                    }
                    break;
            }
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'getRoleUser':
                    $roleid = I('get.roleid');
                    $where['A.job_hospitalid'] = session('current_hospitalid');
                    $where['A.status'] = 1;
                    $where['A.is_delete'] = 0;
                    $where['B.roleid'] = ['IN', $roleid];
                    $userInfo = $noticeModel->DB_get_all_join('user', 'A', 'A.userid,A.username', 'sb_user_role AS B ON A.userid = B.userid', $where);
                    foreach ($userInfo as $k => $v) {
                        $type[$k]['name'] = $v['username'];
                        $type[$k]['value'] = $v['userid'];
                        $type[$k]['selected'] = 'selected';
                    }
                    $this->ajaxReturn($type, 'JSON');
                    break;
                default:
                    $now = getHandleDate(time());
                    $userInfo = [];
                    $userInfo['username'] = session('username');
                    $userInfo['userid'] = session('userid');
                    $this->assign('now', $now);
                    $this->assign('userInfo', $userInfo);
                    //获取用户组 以及用户
                    $where['hospital_id'] = session('current_hospitalid');
                    $where['status'] = 1;
                    $where['is_delete'] = 0;
                    $group = $noticeModel->DB_get_all('role', 'roleid,role', $where);
                    //排查无权限公告角色id
                    $otherId = $noticeModel->DB_get_all('role_menu', 'distinct(roleid) AS roleid', ['menuid' => 230]);
                    foreach ($group as $k => $v) {
                        foreach ($otherId as $k1 => $v1) {
                            if ($v['roleid'] == $v1['roleid']) {
                                $user_group[$k]['roleid'] = $v['roleid'];
                                $user_group[$k]['role'] = $v['role'];
                            }
                        }
                    }
                    $this->assign('user_group', $user_group);
                    $this->display();
                    break;
            }
        }
    }

    /**
     * 编辑公告
     */
    public function editNotice()
    {
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'uploadFile':
                    //上传设备图片
                    $Tool = new ToolController();
                    //设置文件类型
                    $type = array('pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'csv');
                    //维修文件名目录设置
                    $dirName = C('UPLOAD_DIR_NOTICE_NAME');
                    //上传文件
                    $upload = $Tool->upFile($type, $dirName);
                    $this->ajaxReturn($upload);
                    break;
                default:
                    $noticeModel = new NoticeModel();
                    $notid = I('post.notid');
                    $oldNoticeTitle = $noticeModel->DB_get_one('notice', 'title', array('notid' => $notid));
                    if ($notid) {
                        if (I('post.title')) {
                            $editData['title'] = I('post.title');
                        } else {
                            $this->ajaxReturn(array('status' => -1, 'msg' => '请输入标题'));
                        }
                        $editData['top'] = I('post.top');
                        $editData['adduser'] = I('post.adduser');
                        $editData['adduserid'] = I('post.adduserid');
                        $editData['adddate'] = I('post.adddate');
                        if (I('post.content')) {
                            $editData['content'] = I('post.content');
                        } else {
                            $this->ajaxReturn(array('status' => -1, 'msg' => '请输入公告正文'));
                        }
                        //改变查看文件用户记录id
                        if (I('post.sendUserId')) {
                            $editData['send_user_id'] = I('post.sendUserId') . ',' . session('userid');
                        } else {
                            $editData['send_user_id'] = session('userid');
                        }
                        $noticeModel->updateData('notice', $editData, array('notid' => $notid));
                        //日志行为记录文字
                        $log['title'] = $oldNoticeTitle['title'];
                        $text = getLogText('editNoticeLogText', $log);
                        $noticeModel->addLog('notice', M()->getLastSql(), $text, $notid, '');
                        //删除原来的文件
                        $noticeModel->updateData('notice_file', array('is_delete' => 1), array('notid' => $notid));
                        //保存文件
                        $file_name = I('post.file_name');
                        $save_name = I('post.save_name');
                        $file_type = I('post.file_type');
                        $file_size = I('post.file_size');
                        $file_url = I('post.file_url');
                        foreach ($file_name as $k => $v) {
                            $file_add[$k]['notid'] = $notid;
                            $file_add[$k]['file_name'] = $v;
                            $file_add[$k]['save_name'] = $save_name[$k];
                            $file_add[$k]['file_type'] = $file_type[$k];
                            $file_add[$k]['file_size'] = $file_size[$k];
                            $file_add[$k]['file_url'] = $file_url[$k];
                            $file_add[$k]['add_time'] = date('Y-m-d H:i:s');
                            $file_add[$k]['add_user'] = session('username');
                        }
                        if ($file_add) {
                            $noticeModel->insertDataALL('notice_file', $file_add);
                        }
                        $this->ajaxReturn(array('status' => 1, 'msg' => '编辑成功'));
                    } else {
                        $this->ajaxReturn(array('status' => -1, 'msg' => '参数错误'));
                    }
                    break;
            }
        } else {
            $noticeModel = new NoticeModel();
            $action = I('get.action');
            switch ($action) {
                case 'getRoleUser':
                    $roleid = I('get.roleid');
                    $where['A.job_hospitalid'] = session('current_hospitalid');
                    $where['A.status'] = 1;
                    $where['A.is_delete'] = 0;
                    $where['B.roleid'] = ['IN', $roleid];
                    $userInfo = $noticeModel->DB_get_all_join('user', 'A', 'A.userid,A.username', 'sb_user_role AS B ON A.userid = B.userid', $where);
                    foreach ($userInfo as $k => $v) {
                        $type[$k]['name'] = $v['username'];
                        $type[$k]['value'] = $v['userid'];
                        $type[$k]['selected'] = 'selected';
                    }
                    $this->ajaxReturn($type, 'JSON');
                    break;
                default:
                    $notid = I('get.notid');
                    $noticeInfo = $noticeModel->DB_get_one('notice', '', array('notid' => $notid), '');
                    $noticeInfo['content'] = htmlspecialchars_decode($noticeInfo['content']);
//            当前时间
                    $now = getHandleDate(time());
                    $userInfo = [];
                    $userInfo['username'] = session('username');
                    $userInfo['userid'] = session('userid');
                    $this->assign('noticeInfo', $noticeInfo);
                    $this->assign('notid', $notid);
                    $this->assign('now', $now);
                    $this->assign('userInfo', $userInfo);
                    //获取用户组
                    $where['hospital_id'] = session('current_hospitalid');
                    $where['status'] = 1;
                    $where['is_delete'] = 0;
                    $group = $noticeModel->DB_get_all('role', 'roleid,role', $where);
                    //排查无权限公告角色id
                    $otherId = $noticeModel->DB_get_all('role_menu', 'distinct(roleid) AS roleid', ['menuid' => 230]);
                    foreach ($group as $k => $v) {
                        foreach ($otherId as $k1 => $v1) {
                            if ($v['roleid'] == $v1['roleid']) {
                                $user_group[$k]['roleid'] = $v['roleid'];
                                $user_group[$k]['role'] = $v['role'];
                            }
                        }
                    }
                    if ($noticeInfo['send_user_id'] != '') {
                        $judgeArr = explode(',', $noticeInfo['send_user_id']);
                        //增加勾选可查看角色
                        $inRoleIdArr = $noticeModel->DB_get_all('user_role', 'distinct(roleid) AS roleid', ['userid' => ['IN', $judgeArr]]);
                        if ($inRoleIdArr) {
                            foreach ($user_group as $k => $v) {
                                foreach ($inRoleIdArr as $k1 => $v1) {
                                    if ($v['roleid'] == $v1['roleid']) {
                                        $user_group[$k]['selected'] = 'selected';
                                        $whereRoleId[] = $v1['roleid'];
                                    }
                                }
                            }
                            //获取勾选用户
                            $inRoleUserIdArr = $noticeModel->DB_get_one('user_role', 'group_concat(userid) AS userid', ['roleid' => ['IN', $whereRoleId]]);
                            $whereUser['job_hospitalid'] = session('current_hospitalid');
                            $whereUser['status'] = 1;
                            $whereUser['is_delete'] = 0;
                            $whereUser['userid'] = ['IN', $inRoleUserIdArr['userid']];
                            $whereUser['is_super'] = 0;
                            $inUser = $noticeModel->DB_get_all('user', 'userid,username', $whereUser);
                            foreach ($inUser as $k => $v) {
                                foreach ($judgeArr as $k1 => $v1) {
                                    if ($v['userid'] == $v1) {
                                        $inUser[$k]['selected'] = 'selected';
                                    }
                                }
                            }
                            $this->assign('inUser', $inUser);
                        }
                        $this->assign('user_group', $user_group);
                        $userid = session('userid');
                        if (in_array($userid, $judgeArr) || session('isSuper') == 1) {
                            $files = $noticeModel->getNoticeFile($notid);
                            $this->assign('files', $files);
                        }
                    } else {
                        $this->assign('user_group', $user_group);
                        $files = $noticeModel->getNoticeFile($notid);
                        $this->assign('files', $files);
                    }
                    $this->display();
                    break;
            }
        }
    }

    /**
     * 删除公告
     */
    public
    function deleteNotice()
    {
        if (IS_POST) {
            $noticeModel = new NoticeModel();
            $notid = I('post.notid');
            $oldNoticeTitle = $noticeModel->DB_get_one('notice', 'title', array('notid' => $notid));
            if ($notid) {
                $noticeModel->deleteData('notice', array('notid' => $notid));
                $noticeModel->deleteData('notice_file', array('notid' => $notid));
                //日志行为记录文字
                $log['title'] = $oldNoticeTitle['title'];
                $text = getLogText('deleteNoticeLogText', $log);
                $noticeModel->addLog('notice', M()->getLastSql(), $text, $notid, '');
                $this->ajaxReturn(array('status' => 1, 'msg' => '删除公告成功'));
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '删除公告失败'));
            }
        }
    }

}
