<?php

/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/6/6
 * Time: 11:15
 */
namespace Admin\Controller\Remind;
use Admin\Controller\Login\CheckLoginController;

class RemindController extends CheckLoginController
{
    /**
     * 历史提醒列表
     */
    public function historyRemindList()
    {
        $this->display();
    }

    /**
     * LED实时消息屏
     */
    public function ledRemind()
    {
        $this->display();
    }

    /**
     * 模块配置
     */
    public function remindSetting()
    {
        $this->display();
    }
}