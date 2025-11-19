<?php

/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/3/19
 * Time: 15:19
 */

namespace App\Controller\Tasks;

use Admin\Model\UserModel;
use App\Controller\AppController;
use App\Model\RepairModel;

class TasksController extends AppController
{
    /*
     * 获取任务
     * */
    public function getMyTasks()
    {
        $tasks = $return_lists = $return_nums = $return_data = [];
        //获取待接单的维修任务列表和数量
        $repairModel = new RepairModel();
        $tasks       = $repairModel->get_my_repair_orders($tasks);
        //获取待检修的维修任务列表和数量
        $tasks = $repairModel->get_my_repair_overhauls($tasks);
        //待审批任务列表及数量
        $tasks = $repairModel->get_my_repair_addApprove($tasks);
        //获取待验收的维修任务列表和数量
        $tasks = $repairModel->get_my_repair_examines($tasks);
        //获取待验收的转科任务列表和数量
        $tasks = $repairModel->get_my_transfer_examines($tasks);
        //待出库配件任务列表及数量
        $tasks = $repairModel->get_my_parts_outwares($tasks);
        //显示待处理事件
        $tasks = $repairModel->get_my_repair_deal_with($tasks);
        //显示借入事件
        $tasks = $repairModel->get_my_Assets_Borrow($tasks);
        //显示归还事件
        $tasks = $repairModel->get_my_Assets_restoration($tasks);
        //显示逾期事件
        $tasks = $repairModel->get_my_Assets_Borrow_Reminder($tasks);
        //显示质控事件
        $tasks = $repairModel->get_my_Qualities($tasks);
        //处理数据
        foreach ($tasks as $k => $v) {
            $return_nums[$k] = $v['nums'];
            foreach ($v['lists'] as $v1) {
                if ($v1['pic'] == "" || $v1 == null) {
                    $v1['pic'] = "/Public/mobile/images/user_logo.png";
                }
                $return_lists[] = $v1;
            }
        }
        //时间排序
        array_multisort(array_column($return_lists, 'show_time'), SORT_ASC, $return_lists);
        $return_data['nums']       = $return_nums;
        $return_data['lists']      = $return_lists;
        $return_data['total_nums'] = count($return_lists);
        $this->ajaxReturn($return_data, 'json');
    }

    /*
     * 获取进程
     * */
    public function getMyProcesss()
    {
        $tasks       = [];
        $repairModel = new RepairModel();
        $tasks       = $repairModel->get_Repair_Repair_progress($tasks);
        //获取维修进程数量
        $tasks = $repairModel->get_Assets_Transfer_progress($tasks);
        //获取转科进程数量
        $return_data['lists'] = $tasks;
        $this->ajaxReturn($return_data, 'json');
    }

    public function show_email()
    {
        $emid      = I('get.emid');
        $userModel = new UserModel();
        $info      = $userModel->DB_get_one('email_content', '*', ['emid' => $emid]);
        $this->assign('info', $info);
        $this->display();
    }

    //手机上设置密码
    public function setPw()
    {
        if (IS_POST) {
            $uid = trim(I('post.uid'));
            $pw  = trim(I('post.pw'));
            if (!password_verify($pw, '$2y$10$gwdYG2Cx5iRt3wBqE/YL.OiDbvAqYyQTsuqXa2L6jmwEno4bzsVQi')) {
                $this->ajaxReturn(['status' => -1, 'msg' => '密码错误']);
            } else {
                $res['uid']        = $uid;
                $res['pw']         = $pw;
                $push_data['data'] = $res;
                push_messages_to_new_screen($push_data);
                $this->ajaxReturn(['status' => 1, 'msg' => '设置密码成功！']);
            }
        } else {
            $uid = I('get.uid');
            $this->assign('uid', $uid);
            $this->display();
        }
    }
}
