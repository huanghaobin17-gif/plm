<?php
namespace Admin\Controller\Assets;
use Admin\Controller\Login\CheckLoginController;

class AssetsController extends CheckLoginController
{
    protected function __initialize(){
        parent::_initialize();
    }
    private $MODULE = 'Assets';

    public function index()
    {
        $this->display();
    }
}