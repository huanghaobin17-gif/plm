<?php

namespace Admin\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\NotCheckLogin\PublicController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\AssetsScrapModel;
use Admin\Model\UserModel;

class ScrapController extends CheckLoginController
{

    private $MODULE = 'Assets';

    /**
     * 报废申请列表
     */
    public function getApplyList()
    {
        if (IS_POST) {
            $scrapModel = new AssetsScrapModel();
            $result = $scrapModel->getAssetsLists();
            $this->ajaxReturn($result);
        } else {
            $action = I('GET.action');
            if ($action == 'showScrap') {
                $this->getDetail();
                $this->display('showScrap');
            } else {
                //所属科室
                $notCheck = new PublicController();
                $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                $this->assign('getApplyList', get_url());
                $this->display();
            }
        }
    }

    /**
     * 报废申请
     */
    public function applyScrap()
    {
        if (IS_POST) {
            $scrapModel = new AssetsScrapModel();
            switch (I('post.type')) {
                case 'end':
                    $scrid = I('post.scrid');
                    $result = $scrapModel->endScrap($scrid);
                    $this->ajaxReturn($result);
                    break;
                case 'edit':
                    $result = $scrapModel->editScrap();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $result = $scrapModel->addScrap();
                    $this->ajaxReturn($result);
            }
        } else {
            $ScrapModel = new AssetsScrapModel();
            //判断是否是重审，如果是多一条审批信息
            if (I('get.scrid')) {
                $scrid = I('get.scrid');
                $scrap_data = $ScrapModel->DB_get_one('assets_scrap', 'assid,scrap_reason,retrial_status', array('scrid' => $scrid));
                $assid = $scrap_data['assid'];
                $this->assign('examine_is_display', 1);
                //审批进程记录
                $examine = $ScrapModel->DB_get_all('approve', 'scrapid,approver,is_adopt,approve_time,remark', array('scrapid' => $scrid), '', 'approve_time asc', '');
                foreach ($examine as $k => $v) {
                    $examine[$k]['approve_time'] = getHandleDate($v['approve_time']);
                    $examine[$k]['approve_name'] = '报废审批';
                }
                $this->assign('examine', $examine);
            } else {
                $assid = I('get.assid');
            }
            //设备基础信息
            $fields = 'A.assid,A.hospital_id,A.assnum,A.assets,A.catid,A.departid,A.model,A.opendate,A.storage_date,A.residual_value,A.guarantee_date,A.buy_price,B.factory';
            $join[0] = 'LEFT JOIN sb_assets_factory AS B ON A.afid = B.afid';
            $assetsinfo = $ScrapModel->DB_get_one_join('assets_info', 'A', $fields, $join, array('A.assid' => $assid));
            //判断有无查看原值的权限
            $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
            if (!$showPrice) {
                $assetsinfo['buy_price'] = '***';
            }
            $departname = array();
            $catname = array();
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            $assetsinfo['opendate'] = HandleEmptyNull($assetsinfo['opendate']);
            $assetsinfo['storage_date'] = HandleEmptyNull($assetsinfo['storage_date']);
            $assetsinfo['guarantee_date'] = HandleEmptyNull($assetsinfo['guarantee_date']);
            $assetsinfo['department'] = $departname[$assetsinfo['departid']]['department'];
            $assetsinfo['category'] = $catname[$assetsinfo['catid']]['category'];
            if (time() <= strtotime($assetsinfo['guarantee_date'])) {
                $assetsinfo['guaranteeStatus'] = '保修期内';
            } else {
                $assetsinfo['guaranteeStatus'] = '<span style="color: red;">已过保修期</span>';
            }

            $subsidiary = $ScrapModel->getAssetsSubsidiary($assid);

            //报废表单信息
            $scrapinfo['scrapnum'] = 'BF-' . $assetsinfo['assnum'];
            $scrapinfo['scrapdate'] = getHandleTime(time());
            $scrapinfo['username'] = session('username');
            //如果是重审申请从数据库中获取原因
            if ($scrid) {
                $scrapinfo['scrap_reason'] = $scrap_data['scrap_reason'];
                $scrapinfo['scrid'] = $scrid;
                $scrapinfo['type'] = 'edit';
            }
            $this->assign('assid', $assid);
            $this->assign('assetsinfo', $assetsinfo);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('scrapinfo', $scrapinfo);
            $this->display();
        }
    }

    /**
     * 报废审核列表
     */
    public function getExamineList()
    {
        if (IS_POST) {
            $scrapModel = new AssetsScrapModel();
            $result = $scrapModel->getApproveLists();
            $this->ajaxReturn($result);
        } else {
            $isopen = $this->checkApproveIsOpen(C('SCRAP_APPROVE'), session('current_hospitalid'));
            if (!$isopen) {
                $this->assign('errmsg', '报废审核未开启，如需开启，请联系管理员！');
                $this->display('Public/error');
            } else {
                //所属科室
                $notCheck = new PublicController();
                $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                $this->assign('isOpenApprove', $isopen['status']);
                $this->assign('getExamineList', get_url());
                $this->display();
            }
        }
    }

    /**
     * 报废审批
     */
    public function examine()
    {
        if (IS_POST) {
            $scrapModel = new AssetsScrapModel();
            $result = $scrapModel->saveExamine();
            $this->ajaxReturn($result);
        } else {

            $ScrapModel = new AssetsScrapModel();
            $assid = I('get.assid');
            $scrid = I('get.scrid');
            //设备基础信息
            $fields = 'A.assnum,A.assets,A.catid,A.departid,A.model,A.opendate,A.buy_price,B.factory,A.storage_date,A.residual_value,A.guarantee_date';
            $join[0] = 'LEFT JOIN sb_assets_factory AS B ON A.afid = B.afid';
            $join[1] = 'LEFT JOIN sb_assets_contract AS C ON A.acid = C.acid';
            $assetsinfo = $ScrapModel->DB_get_one_join('assets_info', 'A', $fields, $join, array('A.assid' => $assid));
            //判断有无查看原值的权限
            $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
            if (!$showPrice) {
                $assetsinfo['buy_price'] = '***';
            }
            $catname = [];
            $departname = [];
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            $assetsinfo['opendate'] = HandleEmptyNull($assetsinfo['opendate']);
            $assetsinfo['storage_date'] = HandleEmptyNull($assetsinfo['storage_date']);
            $assetsinfo['guarantee_date'] = HandleEmptyNull($assetsinfo['guarantee_date']);
            $assetsinfo['department'] = $departname[$assetsinfo['departid']]['department'];
            $assetsinfo['category'] = $catname[$assetsinfo['catid']]['category'];
            $subsidiary = $ScrapModel->getSubsidiaryBasic($scrid);
            if (time() <= strtotime($assetsinfo['guarantee_date'])) {
                $assetsinfo['guaranteeStatus'] = '保修期内';
            } else {
                $assetsinfo['guaranteeStatus'] = '<span style="color: red;">已过保修期</span>';
            }
            //报废表单信息
            $scrapinfo = $ScrapModel->DB_get_one('assets_scrap', 'scrid,scrapnum,scrapdate,apply_user,scrap_reason,current_approver,all_approver,approve_status', array('scrid' => $scrid));
            //审批进程记录 删除了的
            $old_apps = $ScrapModel->DB_get_all('approve', 'scrapid,approver,is_adopt,approve_time,remark', array('is_delete' => 1, 'scrapid' => $scrid), '', 'approve_time asc', '');
            foreach ($old_apps as $k => $v) {
                $old_apps[$k]['approve_time'] = getHandleDate($v['approve_time']);
                $old_apps[$k]['approve_name'] = '报废审批';
            }
            //审批进程记录
            $examine = $ScrapModel->DB_get_all('approve', 'scrapid,approver,is_adopt,approve_time,remark', array('is_delete' => 0, 'scrapid' => $scrid), '', 'approve_time asc', '');
            foreach ($examine as $k => $v) {
                $examine[$k]['approve_time'] = getHandleDate($v['approve_time']);
                $examine[$k]['approve_name'] = '报废审批';
            }
            $canApprove = false;
            if ($scrapinfo['approve_status'] == C('OUTSIDE_STATUS_APPROVE')) {
                if ($scrapinfo['current_approver']) {
                    $current_approver = explode(',', $scrapinfo['current_approver']);
                    $current_approver_arr = [];
                    foreach ($current_approver as &$current_approver_value) {
                        $current_approver_arr[$current_approver_value] = true;
                    }
                    if ($current_approver_arr[session('username')]) {
                        $canApprove = true;
                    }
                }
            }
            //******************************审批流程显示 start***************************//
            $scrapinfo = $ScrapModel->get_approves_progress($scrapinfo,'scrapid','scrid');
            //**************************************审批流程显示 end*****************************//
            $this->assign('canApprove', $canApprove);
            $this->assign('examine', $examine);
            $this->assign('old_apps', $old_apps);
            $this->assign('assid', $assid);
            $this->assign('scrid', $scrid);
            $this->assign('assetsinfo', $assetsinfo);
            $this->assign('subsidiary', $subsidiary);
            $this->assign('scrapinfo', $scrapinfo);
            $this->assign('username', session('username'));
            $this->assign('approve_time', getHandleTime(time()));
            $this->display();
        }
    }

    /**
     * 报废结果列表
     */
    public function getResultList()
    {
        $ScrapModel = new AssetsScrapModel();
        if (IS_POST) {
            $result = $ScrapModel->getResultLists();
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('GET.action');
            switch ($action){
                case 'showScrap':
                    $this->getDetail();
                    $this->display('showScrap');
                    break;
                case 'showResult':
                    $this->getDetail();
                    $this->display('showScrap');
                    break;
                case 'printReport':
                    //打印报告
                    $this->printReport();
                    break;
                default:
                    //所属科室
                    $notCheck = new PublicController();
                    $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                    $this->assign('getResultList', get_url());
                    $this->display();
                    break;
            }
        }
    }

    /**
     * 报废处置
     */
    public function result()
    {
        $ScrapModel = new AssetsScrapModel();
        switch (I('get.type')) {
            case '';
                if (IS_POST) {
                    $action = I('post.action');
                    if ($action == 'uploadReport') {
                        $result = $ScrapModel->uploadReport(C('UPLOAD_DIR_REPORT_SCRAP_NAME'));
                        if ($result['status'] == 1) {
                            //保存数据库
                            $data['scrid'] = I('post.scrid');
                            $data['file_name'] = $result['formerly'];
                            $data['save_name'] = $result['title'];
                            $data['file_type'] = $result['file_type'];
                            $data['file_size'] = $result['file_size'];
                            $data['file_url'] = $result['file_url'];
                            $data['add_user'] = session('username');
                            $data['add_time'] = date('Y-m-d H:i:s');
                            $ScrapModel->insertData('assets_scrap_report', $data);
                        }
                    } elseif ($action == 'del_file') {
                        $result = $ScrapModel->updateData('assets_scrap_report', array('is_delete' => 1), array('file_id' => I('post.file_id')));
                        if ($result) {
                            $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功！'));
                        } else {
                            $this->ajaxReturn(array('status' => -1, 'msg' => '删除失败！'));
                        }
                    } elseif ($action == 'batchPrint') {
                        $result = $this->batchPrintReport();
                    } else {
                        $result = $ScrapModel->saveResult();
                    }
                    $this->ajaxReturn($result);
                } else {
                    $action = I('get.action');
                    if ($action == 'uploadReport') {
                        //上传、查看报告
                        $this->uploadReport();
                    } else {
                        $assid = I('get.assid');
                        $scrid = I('get.scrid');
                        $catname = array();
                        $departname = array();
                        //设备基础信息
                        $fields = 'A.assnum,A.assets,A.catid,A.departid,A.model,A.opendate,A.buy_price,A.storage_date,A.residual_value,B.factory,A.guarantee_date';
                        $join[0] = 'LEFT JOIN sb_assets_factory AS B ON A.afid = B.afid';
                        $assetsinfo = $ScrapModel->DB_get_one_join('assets_info', 'A', $fields, $join, array('A.assid' => $assid));
                        //判断有无查看原值的权限
                        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
                        if (!$showPrice) {
                            $assetsinfo['buy_price'] = '***';
                        }
                        include APP_PATH . "Common/cache/category.cache.php";
                        include APP_PATH . "Common/cache/department.cache.php";
                        $assetsinfo['opendate'] = HandleEmptyNull($assetsinfo['opendate']);
                        $assetsinfo['storage_date'] = HandleEmptyNull($assetsinfo['storage_date']);
                        $assetsinfo['guarantee_date'] = HandleEmptyNull($assetsinfo['guarantee_date']);
                        $assetsinfo['department'] = $departname[$assetsinfo['departid']]['department'];
                        $assetsinfo['category'] = $catname[$assetsinfo['catid']]['category'];
                        if (time() <= strtotime($assetsinfo['guarantee_date'])) {
                            $assetsinfo['guaranteeStatus'] = '保修期内';
                        } else {
                            $assetsinfo['guaranteeStatus'] = '<span style="color: red;">已过保修期</span>';
                        }
                        //报废表单信息
                        $scrapinfo = $ScrapModel->DB_get_one('assets_scrap', 'scrapnum,scrapdate,apply_user,scrap_reason,cleardate,clear_cross_user,clear_company,clear_contacter,clear_telephone,clear_remark', array('scrid' => $scrid));
                        //审批进程记录
                        $examine = $ScrapModel->DB_get_all('approve', 'scrapid,approver,is_adopt,approve_time,remark', array('scrapid' => $scrid), '', '', '');
                        foreach ($examine as $k => $v) {
                            $examine[$k]['approve_time'] = getHandleDate($v['approve_time']);
                            $examine[$k]['approve_name'] = '报废审批';
                        }
                        //判断有无相关文件
                        $files = $ScrapModel->DB_get_one('scrap_file', 'file_url', array('scrid' => $scrid));
                        if ($files) {
                            $empty = 0;
                        } else {
                            $empty = 1;
                        }
                        $subsidiary = $ScrapModel->getSubsidiaryBasic($scrid);
                        $this->assign('empty', $empty);
                        $this->assign('examine', $examine);
                        $this->assign('clear_cross', session('username'));
                        $this->assign('assid', $assid);
                        $this->assign('scrid', $scrid);
                        $this->assign('assetsinfo', $assetsinfo);
                        $this->assign('subsidiary', $subsidiary);
                        $this->assign('scrapinfo', $scrapinfo);
                        $isOpenApprove = $this->checkApproveIsOpen(C('SCRAP_APPROVE'), session('current_hospitalid'));
                        $this->assign('isOpenApprove', $isOpenApprove);
                        $this->display();
                    }
                }
                break;
            case 'uploadFile';//上传文件到文件夹
                $Tool = new ToolController();
                //设置文件类型
                $type = array('jpg', 'pdf', 'png', 'bmp', 'jpeg', 'gif', 'doc', 'docx');
                //报废文件名目录设置
                $dirName = C('UPLOAD_DIR_SCRAP_NAME');
                //上传文件
                $upload = $Tool->upFile($type, $dirName);
                if ($upload['status'] == C('YES_STATUS')) {
                    // 上传成功 获取上传文件信息
                    $this->ajaxReturn(array('status' => 1, 'path' => $upload['src']));
                } else {
                    // 上传错误提示错误信息
                    $this->ajaxReturn(array('status' => -1, 'msg' => $upload['msg']));
                }
                break;
            case 'uploadScrap';//入库
                if (IS_POST) {
                    $scrid = I('post.scrid');
                    $ScrapModel->addPath($scrid);
                    // 上传成功 获取上传文件信息
                    $this->ajaxReturn(array('status' => 1));
                }
                break;
            case 'showFile';
                $files = $ScrapModel->getScrapFile();
                $this->assign('uploadinfo', $files);
                $this->display('showFile');
        }
    }

    /**
     * Notes: 打印报告
     */
    private function printReport()
    {
        $scrid = I('get.scrid');
        $where['A.scrid'] = $scrid;
        //查询报废设备及单号信息
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
        $fields = "A.*,B.assid,B.assets,B.assnum,B.model,B.departid,B.serialnum,B.brand,B.unit,B.catid,B.afid,B.guarantee_date,A.complete_approver";
        $assModel = new AssetsInfoModel();
        $scrapInfo = $assModel->DB_get_one_join('assets_scrap', 'A', $fields, $join, $where);
        $scrapInfo['guarantee_date'] = ($scrapInfo['guarantee_date'] == '0000-00-00') ? '/' : $scrapInfo['guarantee_date'];
        //查询报废科室负责人签名
        $scrautograph = $assModel->get_autograph($scrapInfo['apply_user']);
        //设备科签名
        $departautograph =$assModel->get_assets_autograph();
        //查询申请人电话
        $tel = $assModel->DB_get_one('user', 'telephone', array('username' => $scrapInfo['apply_user']));
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        $scrapInfo['telephone'] = $SM4->decrypt($tel['telephone']);
        //查询设备出厂厂家
        $fac = $assModel->DB_get_one('assets_factory', 'factory', array('afid' => $scrapInfo['afid']));
        $scrapInfo['factory'] = $fac['factory'];
        $departname = $catname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        $scrapInfo['department'] = $departname[$scrapInfo['departid']]['department'];
        $scrapInfo['category'] = $catname[$scrapInfo['catid']]['category'];
        if ($scrapInfo['approve_status'] == 1) {
            //查询审批信息
            $approves = $assModel->DB_get_all('approve', 'apprid,approver,approve_time,is_adopt,remark', array('scrapid' => $scrapInfo['scrid'], 'is_delete' => 0), '', 'apprid asc');
            $this->assign('approves', $approves);
        }
        //查询随报废附属设备
        $join_f = "LEFT JOIN sb_assets_info AS B ON A.subsidiary_assid = B.assid";
        $fields_f = "A.*,B.assets,B.assnum,B.model,B.brand,B.serialnum";
        $details = $assModel->DB_get_all_join('assets_scrap_detail', 'A', $fields_f, $join_f, array('A.scrid' => $scrid),'','','');
        $title = $assModel->getprinttitle('assets', 'scrap_template');
        $this->assign('title', $title);
        $this->assign('scrautograph', $scrautograph);
        $this->assign('departautograph', $departautograph);
        $this->assign('scrautograph_time', substr($scrapInfo['scrapdate'], 0, 10));
        $this->assign('departautograph_time',$scrapInfo['cleardate']);
        $this->assign('scrapInfo', $scrapInfo);
        $this->assign('details', $details);
        $this->assign('date', date('Y-m-d'));
        $baseSetting = array();
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
        $this->display('printReport');
    }

    /**
     * Notes: 打印报告
     */
    private function batchPrintReport()
    {
        $scrid = I('post.scrid');
        $scrid = trim($scrid, ',');
        $where['A.scrid'] = array('in', $scrid);
        //查询报废设备及单号信息
        $join = "LEFT JOIN sb_assets_info AS B ON A.assid = B.assid LEFT JOIN sb_user AS C ON C.username = A.apply_user";
        $fields = "A.*,B.assid,B.assets,B.assnum,B.model,B.departid,B.serialnum,B.brand,B.unit,B.catid,B.afid,B.guarantee_date,C.telephone";
        $assModel = new AssetsInfoModel();
        $userModel = new UserModel();
        $scrapInfo = $assModel->DB_get_all_join('assets_scrap', 'A', $fields, $join, $where,'','','');
        $departname = $catname = array();
        $html = '';
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/category.cache.php";
        Vendor('SM4.SM4');
        $SM4 = new \SM4();
        //查询设备出厂厂家
        foreach ($scrapInfo as $k => $v) {
            $scrapInfo[$k]['guarantee_date'] = ($scrapInfo[$k]['guarantee_date'] == '0000-00-00') ? '/' : $scrapInfo[$k]['guarantee_date'];
            $fac = $assModel->DB_get_one('assets_factory', 'factory', array('afid' => $v['afid']));
            $scrapInfo[$k]['factory'] = $fac['factory'];
            $scrapInfo[$k]['department'] = $departname[$v['departid']]['department'];
            $scrapInfo[$k]['category'] = $catname[$v['catid']]['category'];
            $scrapInfo[$k]['telephone'] = strlen($scrapInfo[$k]['telephone'])>12?$SM4->decrypt($scrapInfo[$k]['telephone']):$scrapInfo[$k]['telephone'];
            if ($v['approve_status'] == 1) {
                //查询审批信息
                $approves = $assModel->DB_get_all('approve', 'apprid,approver,approve_time,is_adopt,remark', array('scrapid' => $v['scrid'], 'is_delete' => 0), '', 'apprid asc');
                $this->assign('approves', $approves);
            }
            //查询报废科室负责人签名

            $scrautograph = $assModel->get_autograph($scrapInfo[$k]['apply_user']);
            //设备科签名
            $departautograph = $assModel->get_assets_autograph();
            $this->assign('scrautograph', $scrautograph);
            $this->assign('departautograph', $departautograph);
            $this->assign('scrautograph_time', substr($scrapInfo[$k]['scrapdate'], 0, 10));
            $this->assign('departautograph_time',$scrapInfo[$k]['cleardate']);

            //查询随报废附属设备
            $join_f = "LEFT JOIN sb_assets_info AS B ON A.subsidiary_assid = B.assid";
            $fields_f = "A.*,B.assets,B.assnum,B.model,B.brand,B.serialnum";
            $details = $assModel->DB_get_all_join('assets_scrap_detail', 'A', $fields_f, $join_f, array('A.scrid' => $v['scrid']),'','','');
            $this->assign('scrapInfo', $scrapInfo[$k]);
            $this->assign('details', $details);
            $this->assign('date', date('Y-m-d'));
            $marget_top = ($k + 2) % 2 == 0 ? 0 : 10;
            $this->assign('marget_top', $marget_top);
            $baseSetting = array();
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $this->assign('imageUrl', $baseSetting['all_module']['all_report_logo']['value']);
            $title = $assModel->getprinttitle('assets', 'scrap_template');
            $this->assign('title', $title);
            $html .= $this->display('batch_print_report');
        }
        echo $html;
        exit;
    }

    /**
     * Notes: 上传、查看报告
     */
    private function uploadReport()
    {
        $scrid = I('get.scrid');
        //查询对应的报废报告
        $scrapModel = new AssetsScrapModel();
        $files = $scrapModel->DB_get_all('assets_scrap_report', '*', array('scrid' => $scrid, 'is_delete' => 0));
        foreach ($files as $k => $v) {
            $files[$k]['suffix'] = substr(strrchr($v['file_url'], '.'), 1);
            switch ($files[$k]['suffix']) {
                //是图片类型
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'bmp':
                case 'gif':
                    $files[$k]['type'] = 1;
                    break;
                //pdf类型
                case 'pdf':
                    $files[$k]['type'] = 2;
                    break;
                //文档类型
                case 'doc':
                case 'docx':
                    $files[$k]['type'] = 3;
                    break;
            }
        }
        //生成二维码
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol".C('HTTP_HOST') . C('ADMIN_NAME').'/Public/uploadReport?id=' . $scrid . '&i=scrid&t=assets_scrap_report&username=' . session('username');
        $codeUrl = $scrapModel->createCodePic($url);
        $codeUrl = trim($codeUrl, '.');
        $this->assign('codeUrl', $codeUrl);
        $this->assign('uploadinfo', $files);
        $this->assign('scrid', $scrid);
        $this->display('uploadReport');
    }


    /**
     * 报废查询列表
     */
    public function getScrapList()
    {
        $ScrapModel = new AssetsScrapModel();
        if (IS_POST) {
            $result = $ScrapModel->getScrapLists();
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showScrap':
                    $this->getDetail();
                    $this->display('showScrap');
                    break;
                case 'showFile':
                    $files = $ScrapModel->getScrapFile();
                    $this->assign('uploadinfo', $files);
                    $this->display('showFile');
                    break;
                default:
                    //所属科室
                    $notCheck = new PublicController();
                    $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                    $this->assign('getScrapList', get_url());
                    $this->display();
                    break;
            }
        }
    }

    /**
     * 显示相关上传文件
     */
    public function showFile()
    {
        switch (I('get.type')) {
            case '';
                break;
            case 'downFile';//下载word文档
                $path = I('GET.path');//获取文件的路径
                $type = substr(strrchr($path, '.'), 1);
                $title = '附件.' . $type;//
                if ($path && $title) {
                    Header("Content-type:  application/octet-stream ");
                    Header("Accept-Ranges:  bytes ");
                    Header("Accept-Length: " . filesize($path));
                    header("Content-Disposition:  attachment;  filename= $title");//生成的文件名(带后缀的)
                    echo file_get_contents('http://' . C('HTTP_HOST') . $path);//用绝对路径
                    readfile($path);
                }
                break;
        }
    }

    private function getDetail()
    {
        $ScrapModel = new AssetsScrapModel();
        $assid = I('get.assid');
        //设备基础信息
        $fields = 'A.assnum,A.assets,A.catid,A.departid,A.model,A.opendate,A.residual_value,A.buy_price,A.guarantee_date,A.storage_date,B.factory';
        $join[0] = 'LEFT JOIN sb_assets_factory AS B ON A.afid = B.afid';
        $assetsinfo = $ScrapModel->DB_get_one_join('assets_info', 'A', $fields, $join, array('A.assid' => $assid));
        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assetsinfo['buy_price'] = '***';
        }
        $departname = array();
        $catname = array();
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        $assetsinfo['opendate'] = HandleEmptyNull($assetsinfo['opendate']);
        $assetsinfo['guarantee_date'] = HandleEmptyNull($assetsinfo['guarantee_date']);
        $assetsinfo['storage_date'] = HandleEmptyNull($assetsinfo['storage_date']);
        $assetsinfo['department'] = $departname[$assetsinfo['departid']]['department'];
        $assetsinfo['category'] = $catname[$assetsinfo['catid']]['category'];

        if (time() <= strtotime($assetsinfo['guarantee_date'])) {
            $assetsinfo['guaranteeStatus'] = '保修期内';
        } else {
            $assetsinfo['guaranteeStatus'] = '<span style="color: red;">已过保修期</span>';
        }
        //报废表单信息和处置明细信息
        $scrapinfo = $ScrapModel->DB_get_one('assets_scrap', '', array('assid' => $assid));
        if (!$scrapinfo) {
            $files = 'C.*';
            $join = 'LEFT JOIN sb_assets_scrap AS C ON C.scrid=D.scrid';
            $where['subsidiary_assid'] = ['EQ', $assid];
            $scrapinfo = $ScrapModel->DB_get_one_join('assets_scrap_detail', 'D', $files, $join, $where, '', 'D.id desc');
        } else {
            $subsidiary = $ScrapModel->getSubsidiaryBasic($scrapinfo['scrid']);
            $this->assign('subsidiary', $subsidiary);
        }
        //判断有无相关文件
        $files = $ScrapModel->DB_get_one('scrap_file', 'file_url', array('scrid' => $scrapinfo['scrid']));
        if ($files) {
            $empty = 0;
        } else {
            $empty = 1;
        }
        //审批进程记录
        $examine = $ScrapModel->DB_get_all('approve', 'scrapid,approver,is_adopt,approve_time,remark', array('scrapid' => $scrapinfo['scrid']), '', 'process_node_level,approve_time asc', '');
        foreach ($examine as $k => $v) {
            $examine[$k]['approve_time'] = getHandleDate($v['approve_time']);
            $examine[$k]['approve_name'] = '报废审批';
        }

        $this->assign('scrid', $scrapinfo['scrid']);
        $this->assign('examine', $examine);
        $this->assign('empty', $empty);
        $this->assign('assetsinfo', $assetsinfo);
        $this->assign('scrapinfo', $scrapinfo);

        $isOpenApprove = $this->checkApproveIsOpen(C('SCRAP_APPROVE'), session('current_hospitalid'));
        $this->assign('isOpenApprove', $isOpenApprove);
    }
}
