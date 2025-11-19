<?php

namespace Admin\Controller\BaseSetting;

use Admin\Controller\Login\CheckLoginController;

class InterfaceController extends CheckLoginController
{
    public function getInterfaceList()
    {
        if (IS_POST) {
            // 排序整理
            $limit  = I('post.limit') ? I('post.limit') : 100;
            $page   = I('post.page') ? I('post.page') : 1;
            $offset = ($page - 1) * $limit;
            $order  = I('post.order') ? I('post.order') : 'desc';
            $sort   = I('post.sort') ? I('post.sort') : 'id';
            // 整理查询
            $where             = [];
            $all_requests_data = I('post.');
            if (!empty($all_requests_data['system'])) {
                $where['system'] = ['like', "%$all_requests_data[system]%"];
            }
            if (!empty($all_requests_data['interface'])) {
                $where['interface'] = ['like', "%$all_requests_data[interface]%"];
            }
            if (!empty($all_requests_data['response'])) {
                $where['response'] = ['like', "%$all_requests_data[response]%"];
            }
            if (!empty($all_requests_data['status'])) {
                $where['status'] = ['like', "%$all_requests_data[status]%"];
            }
            // 一个拿总数
            $total = M('interface_log')->where($where)->order($sort . ' ' . $order)->limit($offset . ',' . $limit)->count();

            // 一个负责查数据
            $data = M('interface_log')->where($where)->order($sort . ' ' . $order)->limit($offset . ',' . $limit)->select();
            $res  = [
                'msg'   => $total == 0 ? '暂无数据' : '获取成功',
                'total' => $total,
                'code'  => $total == 0 ? 400 : 200,
                'rows'  => $data,
            ];
            $this->ajaxReturn($res);
        } else {
            $this->assign('getInterfaceList', get_url());
            $this->display();
        }
    }

}
