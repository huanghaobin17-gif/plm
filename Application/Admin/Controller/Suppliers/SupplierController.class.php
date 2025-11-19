<?php

/**
 * Created by PhpStorm.
 * User: tcdahe
 * Date: 2018/6/28
 * Time: 10:28
 */
namespace Admin\Controller\Suppliers;
use Admin\Controller\Login\CheckLoginController;
class SupplierController extends CheckLoginController
{
    /**
     * 注册供应商列表
     */
    public function getSuppliersList()
    {
        $this->display();
    }

    /**
     * 供应商详情
     */
    public function supplierDetailInfo()
    {
        $this->display();
    }
}