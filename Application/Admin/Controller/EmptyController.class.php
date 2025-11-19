<?php
/**
 * Created by PhpStorm.
 * User: liuwl
 * Date: 2019/4/19 0019
 * Time: 下午 2:31
 */
namespace Admin\Controller;
use Think\Controller;

class EmptyController extends Controller
{
    public function index() {
        if(!showError){
            header("Location: /A/Public/otherError");
            exit;
        }
    }
}