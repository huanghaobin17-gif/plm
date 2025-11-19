<?php
namespace Admin\Controller\Stock;
use Admin\Controller\Login\CheckLoginController;
use think\Controller;




class StockController extends CheckLoginController
{

    public function index(){
        $this->display("Stock/index");
    }

}