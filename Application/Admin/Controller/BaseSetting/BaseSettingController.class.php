<?php
namespace Admin\Controller\BaseSetting;
use Admin\Controller\Login\CheckLoginController;
use think\Controller;

class BaseSettingController extends CheckLoginController
{
    protected function __initialize(){
        parent::_initialize();
    }
    private $MODULE = 'BaseSetting';

    public function index()
    {
        $this->display();
    }
}