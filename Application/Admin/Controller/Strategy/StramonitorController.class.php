<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2020/1/7
 * Time: 9:55
 */
namespace Admin\Controller\Strategy;


use Admin\Controller\Login\CheckLoginController;

class StramonitorController extends CheckLoginController
{
    public function monitorsur()
    {
        if(IS_POST){

        }else{
            $this->display();
        }
    }
}