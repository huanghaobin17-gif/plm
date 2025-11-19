<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/5/10
 * Time: 16:44
 */

namespace Admin\Controller\BaseSetting;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\EditModel;

class ExamAppController extends CheckLoginController
{
    private $MODULE = 'BaseSetting';

    public function getExamLists()
    {
        if(IS_POST){
            $editModel = new EditModel();
            $result = $editModel->get_exam_app();
            $this->ajaxReturn($result);
        }else{
            $this->assign('getExamLists',get_url());
            $this->display();
        }
    }

    /**
     * Notes: 同意/驳回申请
     */
    public function passno()
    {
        if(IS_POST){
            $editModel = new EditModel();
            $result = $editModel->do_approval();
            $this->ajaxReturn($result);
        }
    }
}