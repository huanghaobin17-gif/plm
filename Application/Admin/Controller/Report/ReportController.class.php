<?php
namespace Admin\Controller\Report;
use Admin\Controller\Login\CheckLoginController;
use think\Controller;





class ReportController extends CheckLoginController
{
    public function index(){
        $this->display("Report/index");
    }

}