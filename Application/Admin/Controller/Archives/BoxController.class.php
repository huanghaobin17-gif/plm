<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2020/03/11
 * Time: 16:30
 */

namespace Admin\Controller\Archives;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\NotCheckLogin\PublicController;
use Admin\Model\ArchivesModel;

class BoxController extends CheckLoginController
{
    private $MODULE = 'Archives';
    private $Controller = 'Box';

    /*
     * 档案盒管理
     */
    public function boxList()
    {
        $archivesModel = new ArchivesModel();
        if (IS_POST) {
            $result = $archivesModel->get_box_lists();
            $this->ajaxReturn($result);
        } else {
            $action = I('get.action');
            $expire_days = $archivesModel->getExpireDay();
            switch ($action) {
                case 'show_box':
                    $box_id = I('get.box_id');
                    $boxInfo = $archivesModel->get_box_info($box_id);
                    $filesInfo = $archivesModel->get_box_files($boxInfo['box_num']);
                    $this->assign('expire_days', $expire_days);
                    $this->assign('boxInfo', $boxInfo);
                    $this->assign('filesInfo', $filesInfo);
                    $this->assign('empty','<tr><td colspan="7" style="text-align: center;">暂无相关数据</td></tr>');
                    $this->display('showBox');
                    break;
                default:
                    //所属科室
                    $notCheck = new PublicController();
                    $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                    $this->assign('expire_days', $expire_days);
                    $this->assign('boxList', get_url());
                    $this->display();
            }
        }
    }

    /**
     * Notes: 添加档案盒
     */
    public function addBox()
    {
        $archivesModel = new ArchivesModel();
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                default:
                    $result = $archivesModel->saveBox();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'getAssets':
                    $departid = I('get.did');
                    $arr_departid = explode(',', $departid);
                    $assets = $archivesModel->getAssets($arr_departid);
                    $this->ajaxReturn($assets);
                    break;
                default:
                    //所属科室
                    $notCheck = new PublicController();
                    $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                    $this->display();
                    break;
            }
        }
    }

    /**
     * Notes: 编辑档案盒
     */
    public function editBox()
    {
        $archivesModel = new ArchivesModel();
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'delBoxAssets':
                    $result = $archivesModel->delBoxAssets();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $result = $archivesModel->saveEdit();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'getAssets':
                    $departid = I('get.did');
                    $box_id = I('get.id');
                    $assets = $archivesModel->getAssets($departid);
                    $boxAssets = $archivesModel->edit_box_assets($box_id);
                    foreach ($boxAssets as $k => $v) {
                        foreach ($assets as &$dv) {
                            if ($v['assid'] == $dv['value']) {
                                $dv['selected'] = 'selected';
                                $dv['disabled'] = '';
                            }
                        }
                    }
                    $this->ajaxReturn($assets);
                    break;
                default:
                    $box_id = I('get.id');
                    //查询档案盒信息
                    $boxInfo = $archivesModel->edit_box_info($box_id);
                    $boxAssets = $archivesModel->edit_box_assets($box_id);
                    //所属科室
                    $notCheck = new PublicController();
                    $departs = $notCheck->getAllDepartmentSearchSelect();
                    $exists_departid = [];
                    foreach ($boxAssets as $k => $v) {
                        if (!in_array($v['departid'], $exists_departid)) {
                            $exists_departid[] = $v['departid'];
                        }
                        foreach ($departs as &$dv) {
                            if ($v['departid'] == $dv['departid']) {
                                $dv['selected'] = 'selected';
                            }
                        }
                    }
                    //查询科室设备
                    $assets = $archivesModel->getAssets($exists_departid);
                    foreach ($boxAssets as $k => $v) {
                        foreach ($assets as &$dv) {
                            if ($v['assid'] == $dv['value']) {
                                $dv['selected'] = 'selected';
                                $dv['disabled'] = '';
                            }
                        }
                    }
                    $this->assign('departmentInfo', $departs);
                    $this->assign('boxInfo', $boxInfo);
                    $this->assign('assets', $assets);
                    $this->display();
                    break;
            }
        }
    }
}