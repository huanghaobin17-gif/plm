<?php

namespace Admin\Controller\NotCheckLogin;

use Admin\Controller\Tool\ToolController;
use Admin\Model\ArchivesModel;
use Admin\Model\AssetsInfoModel;
use Admin\Model\DepartmentModel;
use Admin\Model\DictionaryModel;
use Admin\Model\OfflineSuppliersModel;
use Admin\Model\PatrolModel;
use Admin\Model\PatrolPlanModel;
use Admin\Model\PeopleModel;
use Admin\Model\PointModel;
use Admin\Model\PurchaseCheckModel;
use Admin\Model\PurchasesModel;
use Admin\Model\QualityModel;
use Admin\Model\RepairModel;
use Admin\Model\RoleModel;
use Admin\Model\UserModel;
use Mobile\Model\WxAccessTokenModel;
use think\Controller;

class PublicController extends Controller
{
    public function testRpc()
    {
        $this->ajaxReturn(['msg' => 'ok', 'status' => 1], 'JSON');
    }

    /**
     * Notes: 导出pdf
     */
    public function getPdfTmp()
    {
        $repId = I('GET.repid');
        //获取维修单信息
        $reModel    = new RepairModel();
        $fields     = "A.*,sb_assets_info.departid,sb_assets_info.catid";
        $join[0]    = " LEFT JOIN __ASSETS_INFO__ ON __ASSETS_INFO__.assid = A.assid";
        $repInfo    = $reModel->DB_get_one_join('repair', 'A', $fields, $join, $where = ['repid' => $repId]);
        $catname    = [];
        $departname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        include APP_PATH . "Common/cache/department.cache.php";
        include APP_PATH . "Common/cache/basesetting.cache.php";
        $repInfo['catName']        = $catname[$repInfo['catid']]['category'];
        $repInfo['departmentName'] = $departname[$repInfo['departid']]['department'];
        //获取配件信息
        $parts = $reModel->DB_get_all('repair_parts', '', ['repid' => $repId], '', 'partid asc', '');
        $this->assign('repInfo', $repInfo);
        $this->assign('parts', $parts);
        $this->assign('username', session('username'));
        $this->assign('printDate', getHandleTime(time()));
        $this->display();
    }

    public function repairToPdf()
    {
        $repId   = I('GET.repid');
        $reModel = new RepairModel();
        $repInfo = $reModel->DB_get_one('repair', 'repnum', $where = ['repid' => $repId]);
        Vendor('mpdf.mpdf');
        //设置中文编码
        $mpdf        = new \mPDF('zh-cn');
        $baseSetting = [];
        include APP_PATH . "Common/cache/basesetting.cache.php";
        if ($baseSetting['repair']['repair_tmp']['value']['style'] == 2) {
            //生成水印
            $water = C('WATER_NAME');
            if ($baseSetting['repair']['repair_print_watermark']['value']['watermark']) {
                $water = $baseSetting['repair']['repair_print_watermark']['value']['watermark'];
            }
            $mpdf->SetWatermarkText($water, 0.08);
        }
        //设置字体，解决中文乱码
        $mpdf->useAdobeCJK      = true;
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        //设置pdf显示方式
        $mpdf->SetDisplayMode('fullpage');
        //$strContent = '我是带水印的PDF文件';
        $mpdf->showWatermarkText = true;
        $mpdf->SetHTMLHeader('');
        $mpdf->SetHTMLFooter('');
        $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url        = "$protocol" . C('HTTP_HOST') . C('ADMIN_NAME') . '/Public/getPdfTmp.html?repid=' . $repId;
        $stylesheet = curl($url);
        $mpdf->WriteHTML($stylesheet);
        $mpdf->Output($repInfo['repnum'] . '.pdf', true);
    }

    public function generate_pdf()
    {
        $planModel   = new PatrolPlanModel();
        $assnum      = I('get.assnum');
        $result      = $planModel->getRecordData($assnum);
        $PatrolModel = new PatrolModel();
        $apps        = $PatrolModel->get_approve_info($result['plan_data']['patrid']);
        $this->assign('result', $result);
        $this->assign('data', $result['data']);
        $this->assign('apps', $apps);
        $this->assign('plan_data', $result['plan_data']);
        $this->assign('time', date('Y-m-d', time()));
        $this->display('Patrol/ReportTemplate/patroltemp');
    }

    //只查一个计划
    public function generates_pdf()
    {
        $planModel   = new PatrolPlanModel();
        $assnum      = I('get.assnum');
        $cycid       = I('get.cycid');
        $result      = $planModel->getRecordDatas($assnum, $cycid);
        $PatrolModel = new PatrolModel();
        $apps        = $PatrolModel->get_approve_info($result['plan_data']['patrid']);
        $this->assign('result', $result);
        $this->assign('data', $result['data']);
        $this->assign('apps', $apps);
        $this->assign('plan_data', $result['plan_data']);
        $this->assign('time', date('Y-m-d', time()));
        $this->display('Patrol/ReportTemplate/patroltemp');
    }

    public function errorPage()
    {
        $this->display('/Public/404');
        exit;
    }

    /**
     * 其他访问控制器错误等跳转的404
     */
    public function otherError()
    {
        $this->display('/Public/otherError404');
        exit;
    }

    //搜索所有巡查计划制定人
    public function getAllPlansMaker()
    {
        $UserModel    = new UserModel();
        $res          = $UserModel->getUsers('addPatrol', '', true, true);
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /*
     * 巡查保养建议搜索计划名称
     */
    public function getAllPlans()
    {
        $type         = I('GET.data');
        $patrolModel  = new PatrolModel();
        $res          = $patrolModel->getAllPlans($type);
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /*
    * 搜索所有执行人
    */
    public function getAllExecute()
    {
        $UserModel    = new UserModel();
        $res          = $UserModel->getUsers('doTask', '', true, true);
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /*
    * 巡查保养获取模板信息
    */
    public function getAllTemplate()
    {
        //实例化模型
        $pointsModel = new PointModel();
        $template    = $pointsModel->DB_get_all('patrol_template', 'tpid,name', '', 'name', 'tpid asc');
        $res         = [];
        $i           = 0;
        foreach ($template as $k => $v) {
            $res[$i]['tpid'] = $v['tpid'];
            $res[$i]['name'] = $v['name'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        //$arr = json_encode($arr);
        $this->ajaxReturn($arr, 'JSON');
    }

    /*
     * 获取周期设备名称
    */
    public function getExamineAsset()
    {
        //实例化模型
        $exallid = I('GET.exallid');
        $where   = "exallid = $exallid";
        $data    = M('patrol_examine_one')->where($where)->field('GROUP_CONCAT(assnum) AS assnum')->group('exallid')->find();
        $assnum  = "'" . str_replace(",", "','", $data['assnum']) . "'";

        $where = "assnum IN ($assnum)";


        $assArr = M('assets_info')->where($where)->field('assnum,assets')->group('assets')->select();
        $res    = [];
        $i      = 0;
        foreach ($assArr as $k => $v) {
            $res[$i]['assnum'] = $v['assnum'];
            $res[$i]['assets'] = $v['assets'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /*
   * 获取周期设备名称
   */
    public function getCycleAsset()
    {
        //实例化模型
        $assnum = I('GET.assnum');
        $where  = "assnum IN ($assnum)";
        $assArr = M('assets_info')->where($where)->field('assets')->group('assets')->select();
        $res    = [];
        $i      = 0;
        foreach ($assArr as $k => $v) {
            $res[$i]['assets'] = $v['assets'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /**
     * 搜索所有设备信息
     */
    public function getAllAssetsSearch()
    {
        $asModel = new AssetsInfoModel();
        $type    = I('GET.type');
        switch ($type) {
            case 'transfer':
                $join[0]                = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
                $where['B.hospital_id'] = session('current_hospitalid');
                $where['B.is_delete']   = C('NO_STATUS');
                if (!session('isSuper')) {
                    $departid            = session('departid');
                    $where['B.departid'] = ['in', $departid];
                    $Assets              = $asModel->DB_get_all_join('assets_transfer', 'A',
                        'B.assid,B.assets,B.assnum,B.assorignum', $join, $where, '', 'A.assid asc', '');
                } else {
                    $Assets = $asModel->DB_get_all_join('assets_transfer', 'A',
                        'B.assid,B.assets,B.assnum,B.assorignum', $join, $where, '', 'A.assid asc', '');
                }
                break;
            case 'scrap':
                $join[0]                = " LEFT JOIN sb_assets_info AS B ON A.assid = B.assid";
                $where['B.hospital_id'] = session('current_hospitalid');
                $where['B.is_delete']   = C('NO_STATUS');
                if (!session('isSuper')) {
                    $departid            = session('departid');
                    $where['B.departid'] = ['in', $departid];
                    $Assets              = $asModel->DB_get_all_join('assets_scrap', 'A',
                        'B.assid,B.assets,B.assnum,B.assorignum', $join, $where, '', 'A.assid asc', '');
                } else {
                    $Assets = $asModel->DB_get_all_join('assets_scrap', 'A', 'B.assid,B.assets,B.assnum,B.assorignum',
                        $join, $where, '', 'A.assid asc', '');
                }
                break;
            case 'job':
                $where['hospital_id'] = session('current_hospitalid');
                $where['is_delete']   = C('NO_STATUS');
                if (!session('isSuper')) {
                    $departid          = session('job_departid');
                    $where['departid'] = ['in', $departid];
                    $Assets            = $asModel->DB_get_all('assets_info', 'assid,assets,assnum,assorignum', $where,
                        '', 'assid asc', '');
                } else {
                    $Assets = $asModel->DB_get_all('assets_info', 'assid,assets,assnum,assorignum', $where, '',
                        'assid asc', '');
                }
                break;
            case 'subsidiary':
                $departid               = I('GET.departid');
                $where['is_delete']     = C('NO_STATUS');
                $where['status'][0]     = 'NOTIN';
                $where['status'][1][]   = C('ASSETS_STATUS_SCRAP');
                $where['status'][1][]   = C('ASSETS_STATUS_OUTSIDE');
                $where['hospital_id']   = session('current_hospitalid');
                $where['is_subsidiary'] = '0';
                if (I('GET.not_get_assid')) {
                    $where['assid'] = ['NEQ', I('GET.not_get_assid')];
                }
                if (judgeNum($departid)) {
                    $where['departid'][] = ['EQ', $departid];
                }
                if (I('GET.inAssid')) {
                    $where['assid'] = ['in', I('GET.inAssid')];
                }
                $where['departid'][] = ['IN', session('departid')];
                $Assets              = $asModel->DB_get_all('assets_info', 'assid,assets,assnum,assorignum', $where, '',
                    'assid desc', '');
                break;
            case 'repairForm':
                $departid             = I('GET.departid');
                $where['is_delete']   = C('NO_STATUS');
                $where['status'][0]   = 'NOTIN';
                $where['status'][1][] = C('ASSETS_STATUS_SCRAP');
                $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
                $where['hospital_id'] = session('current_hospitalid');
                if (I('GET.not_get_assid')) {
                    $where['assid'] = ['NEQ', I('GET.not_get_assid')];
                }
                if (judgeNum($departid)) {
                    $where['departid'][] = ['EQ', $departid];
                }
                if (I('GET.inAssid')) {
                    $where['assid'] = ['in', I('GET.inAssid')];
                }
                $where['departid'][] = ['IN', session('departid')];
                $Assets              = $asModel->DB_get_all('assets_info',
                    'assid,assets,assnum,assorignum,model,serialnum,address', $where, '', 'assid desc', '');
                $res                 = [];
                $i                   = 0;
                foreach ($Assets as $k => $v) {
                    $res[$i]['assid']      = $v['assid'];
                    $res[$i]['assnum']     = $v['assnum'];
                    $res[$i]['assets']     = $v['assets'];
                    $res[$i]['assorignum'] = $v['assorignum'];
                    $res[$i]['model']      = $v['model'];
                    $res[$i]['serialnum']  = $v['serialnum'];
                    $res[$i]['address']    = $v['address'];
                    $i++;
                }
                $arr          = [];
                $arr['value'] = $res;
                $this->ajaxReturn($arr, 'JSON');
                break;
            default:

                $departid             = I('GET.departid');
                $where['is_delete']   = C('NO_STATUS');
                $where['status'][0]   = 'NOTIN';
                $where['status'][1][] = C('ASSETS_STATUS_SCRAP');
                $where['status'][1][] = C('ASSETS_STATUS_OUTSIDE');
                $where['hospital_id'] = session('current_hospitalid');
                if (I('GET.not_get_assid')) {
                    $where['assid'] = ['NEQ', I('GET.not_get_assid')];
                }
                if (judgeNum($departid)) {
                    $where['departid'][] = ['EQ', $departid];
                }
                if (I('GET.inAssid')) {
                    $where['assid'] = ['in', I('GET.inAssid')];
                }
                $where['departid'][] = ['IN', session('departid')];
                $Assets              = $asModel->DB_get_all('assets_info', 'assid,assets,assnum,assorignum', $where, '',
                    'assid desc', '');
                break;
        }
        $res = [];
        $i   = 0;
        foreach ($Assets as $k => $v) {
            $res[$i]['assid']      = $v['assid'];
            $res[$i]['assnum']     = $v['assnum'];
            $res[$i]['assets']     = $v['assets'];
            $res[$i]['assorignum'] = $v['assorignum'];
            $res[$i]['pinyin']     = strtoupper(pinyin($v['assets'], 'first'));
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /**
     * 搜索所有分类信息
     */
    public function getAllCategorySearch()
    {
        $where['is_delete']   = C('NO_STATUS');
        $where['hospital_id'] = session('current_hospitalid');
        $asModel              = new AssetsInfoModel();
        $category             = $asModel->DB_get_all('category', 'catid,hospital_id,catenum,category,parentid', $where,
            '', 'catid asc', '');
        $res                  = [];
        $i                    = 0;
        foreach ($category as $k => $v) {
            $res[$i]['xuhao']    = $k + 1;
            $res[$i]['catid']    = $v['catid'];
            $res[$i]['catenum']  = $v['catenum'];
            $res[$i]['category'] = $v['category'];
            $res[$i]['parentid'] = $v['parentid'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /**
     * 搜索所有部门信息
     */
    public function getAllDepartmentSearch()
    {
        $asModel              = new AssetsInfoModel();
        $where['is_delete']   = C('NO_STATUS');
        $type                 = $_GET['type'];
        $where['hospital_id'] = session('current_hospitalid');
        if ($type != 'all') {
            $where['departid'] = ['IN', session('departid')];
        }
        if ($_GET['departid']) {
            $where['departid'] = ['NOT IN', $_GET['departid']];
        }
        $department = $asModel->DB_get_all('department',
            'departid,department,departnum,address,departrespon,assetsrespon', $where, '', 'departid asc', '');
        $res        = [];
        foreach ($department as $k => $v) {
            $res[$k]['xuhao']        = $k + 1;
            $res[$k]['departid']     = $v['departid'];
            $res[$k]['departnum']    = $v['departnum'];
            $res[$k]['department']   = $v['department'];
            $res[$k]['departrespon'] = $v['departrespon'];
            $res[$k]['address']      = $v['address'];
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /**
     * 搜索所有部门信息(列表用多选框)
     */
    public function getAllDepartmentSearchSelect()
    {
        $asModel              = new AssetsInfoModel();
        $where['is_delete']   = C('NO_STATUS');
        $type                 = I('get.type');
        $where['hospital_id'] = session('current_hospitalid');
        if ($type != 'all') {
            if (session('isSuper') != 1) {
                $where['departid'] = ['IN', session('departid')];
            }
        }
        return $asModel->DB_get_all('department', 'departid,department', $where, '', 'departid asc', '');
    }

    /*
    * 搜索所有用户信息
    */
    public function getAllUserSearch()
    {
        $userModel = new UserModel();
        //获取所有用户
        $arr = $userModel->getAllUserSearch();
        $this->ajaxReturn($arr, 'JSON');
    }


    /**
     * 获取所有部门信息
     */
    public function getAllDepartmentRespon()
    {
        $departModel          = new DepartmentModel();
        $where['is_delete']   = C('NO_STATUS');
        $where['hospital_id'] = session('current_hospitalid');
        $departments          = $departModel->getDepartmentsRespon($where);
        $res                  = [];
        $i                    = 0;
        foreach ($departments as $k => $v) {
            $res[$i]['departid']     = $v['departid'];
            $res[$i]['departnum']    = $v['departnum'];
            $res[$i]['department']   = $v['department'];
            $res[$i]['departrespon'] = $v['departrespon'];
            $res[$i]['address']      = $v['address'];
            $res[$i]['assetsrespon'] = $v['assetsrespon'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        //$arr = json_encode($arr);
        $this->ajaxReturn($arr, 'JSON');
    }

    /*
    * 搜索所有角色信息
    */
    public function getAllRoles()
    {
        $roleModel            = new RoleModel();
        $where['status']      = ['EQ', C('OPEN_STATUS')];
        $where['is_delete']   = ['EQ', C('NO_STATUS')];
        $where['hospital_id'] = ['EQ', session('current_hospitalid')];
        $role                 = $roleModel->DB_get_all('role', 'roleid,role', $where, '', 'roleid asc', '');
        $res                  = [];
        $i                    = 0;
        foreach ($role as $k => $v) {
            $res[$i]['num']      = $k + 1;
            $res[$i]['rolename'] = $v['role'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        //$arr = json_encode($arr);
        $this->ajaxReturn($arr, 'JSON');
    }


    //获取模板类型信息
    public function getAllTemplateType()
    {
        //实例化模型
        $pointsModel = new PointModel();
        $type        = $pointsModel->DB_get_all('patrol_points', 'name,ppid', "parentid=0", 'name', 'ppid asc');
        $res         = [];
        $i           = 0;
        foreach ($type as $k => $v) {
            $res[$i]['ppid'] = $v['ppid'];
            $res[$i]['name'] = $v['name'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        //$arr = json_encode($arr);
        $this->ajaxReturn($arr, 'JSON');
    }

    //获取厂商名称数据
    public function getOfflineSuppliersName()
    {
        $OfflineSuppliersModel = new OfflineSuppliersModel();
        $where['is_delete']    = C('NO_STATUS');
        $type                  = $OfflineSuppliersModel->DB_get_all('offline_suppliers', 'olsid,sup_name', $where);
        $res                   = [];
        $i                     = 0;
        foreach ($type as $k => $v) {
            $res[$i]['sup_name'] = $v['sup_name'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        //$arr = json_encode($arr);
        $this->ajaxReturn($arr, 'JSON');
    }

    //获取乙方名称
    public function getOLSContractContractName()
    {
        $where['is_delete']  = ['NEQ', C('YES_STATUS')];
        $where['is_confirm'] = ['EQ', C('YES_STATUS')];
        $fileds              = 'contract_name';
        $sql                 = M('purchases_contract', C('DB_PREFIX'))
            ->field($fileds)
            ->union(['field' => $fileds, 'table' => 'sb_assets_record_contract', 'where' => $where], true)
            ->union(['field' => $fileds, 'table' => 'sb_assets_insurance_contract', 'where' => $where], true)
            ->union(['field' => $fileds, 'table' => 'sb_parts_contract', 'where' => $where], true)
            ->union(['field' => $fileds, 'table' => 'sb_repair_contract', 'where' => $where], true)
            ->where($where)
            ->fetchSql(true)
            ->select();
        $sql                 = 'SELECT * FROM(' . $sql . ') AS a';
        $type                = M()->query($sql);

        $res = [];
        $i   = 0;
        foreach ($type as $k => $v) {
            $res[$i]['contract_name'] = $v['contract_name'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    //获取模板类型信息
    public function getTemplatePoints()
    {
        //实例化模型
        $tpid        = I('GET.tpid');
        $level       = I('GET.level');
        $ppid        = I('GET.ppid');
        $pointsModel = new PointModel();
        $fileds      = 'arr_num_3';
        if ($level == C('PATROL_LEVEL_RC')) {
            $fileds .= ',arr_num_1 AS arr_num';
        }
        if ($level == C('PATROL_LEVEL_XC')) {
            $fileds .= ',arr_num_2 AS arr_num';
        }
        if ($level == C('PATROL_LEVEL_PM')) {
            $fileds .= ',arr_num_3 AS arr_num';
        }
        $template   = $pointsModel->DB_get_one('patrol_template', $fileds, 'tpid=' . $tpid);
        $difference = array_diff(json_decode($template['arr_num_3']), json_decode($template['arr_num']));
        $difference = join(',', $difference);
        $res        = [];
        if ($ppid) {
            if ($difference) {
                $where = "num IN($difference)";
                if ($ppid) {
                    $where .= ' AND parentid=' . $ppid;
                }
                $points = $pointsModel->DB_get_all('patrol_points', 'name', $where, 'name', 'ppid asc');
                $res    = [];
                $i      = 0;
                foreach ($points as $k => $v) {
                    $res[$i]['name'] = $v['name'];
                    $i++;
                }
            }
        }
        $arr          = [];
        $arr['value'] = $res;
        //$arr = json_encode($arr);
        $this->ajaxReturn($arr, 'JSON');
    }

    //建议搜索 设备规格型号
    public function getAssetsModelSearch()
    {
        $AssetsInfoModel      = new AssetsInfoModel();
        $where['is_delete']   = C('NO_STATUS');
        $where['model']       = ['NEQ', ''];
        $where['hospital_id'] = session('current_hospitalid');
        $data                 = $AssetsInfoModel->DB_get_all('assets_info', 'model', $where, 'model', 'assid asc');
        $res                  = [];
        $i                    = 0;
        foreach ($data as $k => $v) {
            $res[$i]['num']   = $k + 1;
            $res[$i]['model'] = $v['model'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    public function getQualitesExecutor()
    {
        $UserModel    = new UserModel();
        $res          = $UserModel->getUsers('setQualityDetail', session('departid'), true, false);
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /**
     * Notes: 获取所有设备字典名称
     */
    public function getAllAssetsDic()
    {
        $dicModel             = new DictionaryModel();
        $where['status']      = C('YES_STATUS');
        $where['hospital_id'] = session('current_hospitalid');
        $data                 = $dicModel->DB_get_all('dic_assets',
            'dic_assid,assets,catid,dic_category,assets_category,unit', $where, '', 'dic_assid desc');
        $res                  = [];
        $i                    = 0;
        $catname              = [];
        include APP_PATH . "Common/cache/category.cache.php";
        foreach ($data as $k => $v) {
            $res[$i]['assets_category']      = $v['assets_category'];
            $v['assets_category']            = str_replace('is_firstaid', '急救设备', $v['assets_category']);
            $v['assets_category']            = str_replace('is_special', '特种设备', $v['assets_category']);
            $v['assets_category']            = str_replace('is_metering', '计量设备', $v['assets_category']);
            $v['assets_category']            = str_replace('is_qualityAssets', '质控设备', $v['assets_category']);
            $v['assets_category']            = str_replace('is_benefit', '效益分析', $v['assets_category']);
            $v['assets_category']            = str_replace('is_lifesupport', '生命支持', $v['assets_category']);
            $res[$i]['xuhao']                = $k + 1;
            $res[$i]['assets']               = $v['assets'];
            $res[$i]['catid']                = $v['catid'];
            $res[$i]['category']             = $catname[$v['catid']]['category'];
            $res[$i]['dic_category']         = $v['dic_category'];
            $res[$i]['assets_category_name'] = $v['assets_category'];
            $res[$i]['unit']                 = $v['unit'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /**
     * Notes: 获取所有测试字典名称
     */
    public function getAllTestDic()
    {
        $dicModel = new PeopleModel();
        // $where['status'] = C('YES_STATUS');
        // $where['hospital_id'] = session('current_hospitalid');
        $where = '';
        $data  = $dicModel->DB_get_all('people', 'id,tname,like', $where, '', 'id desc');
        $res   = [];
        $i     = 0;
        // $catname = [];
        // include APP_PATH . "Common/cache/category.cache.php";
        foreach ($data as $k => $v) {
            $res[$i]['like'] = $v['like'];
            $v['like']       = str_replace('eat', '吃饭', $v['like']);
            $v['like']       = str_replace('sleep', '睡觉', $v['like']);

            $res[$i]['xuhao'] = $k + 1;
            $res[$i]['tname'] = $v['tname'];

            $res[$i]['like'] = $v['like'];

            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }


    /**
     * Notes: 获取所有设备字典名称
     */
    public function getAllPartsDic()
    {
        $dicModel             = new DictionaryModel();
        $where['hospital_id'] = session('current_hospitalid');
        $where['is_delete']   = C('NO_STATUS');
        $data                 = $dicModel->DB_get_all('dic_parts', 'parts,parts_model,dic_category', $where);
        $res                  = [];
        $i                    = 0;
        foreach ($data as $k => $v) {
            $res[$i]['xuhao']        = $k + 1;
            $res[$i]['parts']        = $v['parts'];
            $res[$i]['parts_model']  = $v['parts_model'];
            $res[$i]['dic_category'] = $v['dic_category'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }


    /**
     * Notes: 获取所有设备字典名称
     */
    public function getAllAssetsDicCategory()
    {
        $dicModel              = new DictionaryModel();
        $type                  = $_GET['type'];
        $where['hospital_id']  = session('current_hospitalid');
        $where['dic_category'] = ['NEQ', ''];
        switch ($type) {
            case 'parts';
                $table              = 'dic_parts';
                $where['is_delete'] = ['NEQ', C('YES_STATUS')];
                break;
            default :
                $table = 'dic_assets';
                break;

        }
        $data = $dicModel->DB_get_all($table, 'dic_category', $where, 'dic_category');
        $res  = [];
        $i    = 0;
        include APP_PATH . "Common/cache/category.cache.php";
        foreach ($data as $k => $v) {
            $res[$i]['xuhao']        = $k;
            $res[$i]['dic_category'] = $v['dic_category'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    /**
     * 高级查询
     */
    public function getAssetsListHighSearch()
    {
        $asModel = new AssetsInfoModel();
        //人员名称
        $users = $asModel->getUser();
        $this->assign('users', $users);
        //供应 生产 维修商
        $factory  = $asModel->getSuppliers('factory');
        $supplier = $asModel->getSuppliers('supplier');
        $repair   = $asModel->getSuppliers('repair');
        //辅助分类
        $assets_helpcat = $asModel->getBaseSettingAssets('assets_helpcat');
        //财务分类
        $assets_finance = $asModel->getBaseSettingAssets('assets_finance');
        //资金来源
        $assets_capitalfrom = $asModel->getBaseSettingAssets('assets_capitalfrom');

        //科室
//        $department = $asModel->DB_get_all('department', 'departid,parentid,department', ['is_delete' => C('NO_STATUS'), 'hospital_id' => session('job_hospitalid')]);
//        $departmentData = [];
//        foreach ($department as $k => $v) {
//            if ($v['parentid'] == 0) {
//                $departmentData[$k]['name'] = $v['department'];
//                $departmentData[$k]['value'] = $v['departid'];
//            }
//        }
//        foreach ($departmentData as $k => $v) {
//            $a = 0;
//            foreach ($department as $k1 => $v1) {
//                if ($v1['parentid'] == $v['value']) {
//                    $departmentData[$k]['children'][$a]['name'] = $v1['department'];
//                    $departmentData[$k]['children'][$a]['value'] = $v1['departid'];
//                    $a++;
//                }
//            }
//        }

        $assetsLevel = $asModel->getAssetsLevel();
        $showPrice   = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $this->assign('is_showPrice', 0);
        } else {
            $this->assign('is_showPrice', 1);
        }
        $this->assign('assets_helpcat', $assets_helpcat);
        $this->assign('assets_finance', $assets_finance);
        $this->assign('assets_capitalfrom', $assets_capitalfrom);
        $this->assign('assetsLevel', $assetsLevel);
        $this->assign('factory', $factory);
        $this->assign('departmentInfo', $this->getAllDepartmentSearchSelect());
        $this->assign('supplier', $supplier);
        $this->assign('repair', $repair);
        $this->display('/Assets/Lookup/getAssetsLishHighSearch');
    }

    /**
     * Notes: 获取审批通过的科室采购计划
     *
     * @param1 getApplyDepartment int 医院ID
     */
    public function getApplyDepartment()
    {
        $apply_type  = I('get.apply_type');//1计划内 2计划外
        $hospital_id = session('current_hospitalid');
        $purModel    = new PurchasesModel();
        if ($apply_type == 1) {
            $where['hospital_id']    = $hospital_id;
            $where['approve_status'] = ['in', [-1, 1]];
            $where['plans_status']   = 1;
            $where['is_delete']      = C('NO_STATUS');
            $where['can_apply_nums'] = ['gt', 0];
            $where['departid']       = ['in', session('departid')];
            $departments             = $purModel->DB_get_all('purchases_plans', 'plans_id,departid,project_name',
                $where);
            $departname              = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $i = 0;
            foreach ($departments as $k => $v) {
                $departments[$k]['xuhao']      = $k + 1;
                $departments[$k]['department'] = $departname[$v['departid']]['department'];
            }
            $arr          = [];
            $arr['value'] = $departments;
            $this->ajaxReturn($arr);
        } else {
            $where_1['departid']    = ['in', session('departid')];
            $where_1['hospital_id'] = $hospital_id;
            $where_1['is_delete']   = C('NO_STATUS');
//            $notdeparts = $purModel->DB_get_one('purchases_plans', 'group_concat(departid) as departids', $where);
//            if ($notdeparts['departids']) {
//                $where_1['departid'] = array('not in', $notdeparts['departids']);
//            }
            $departments = $purModel->DB_get_all('department', 'departid,department', $where_1);
            $departname  = [];
            include APP_PATH . "Common/cache/department.cache.php";
            $i = 0;
            foreach ($departments as $k => $v) {
                $departments[$k]['xuhao']      = $k + 1;
                $departments[$k]['department'] = $departname[$v['departid']]['department'];
            }
            $arr          = [];
            $arr['value'] = $departments;
            $this->ajaxReturn($arr);
        }
    }

    /**
     * Notes: 获取采购计划中的部门名称
     */
    public function getPurPlansProjects()
    {
        $purModel = new PurchasesModel();
        $projects = $purModel->getPurPlansProjects();
        $res      = [];
        foreach ($projects as $k => $v) {
            $res[$k]['xuhao']        = $k + 1;
            $res[$k]['project_name'] = $v['project_name'];
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr);
    }

    /**
     * Notes: 获取科室申请的项目名称
     */
    public function getAllDepartProjects()
    {
        $purModel = new PurchasesModel();
        $projects = $purModel->getAllDepartProjects();
        $res      = [];
        foreach ($projects as $k => $v) {
            $res[$k]['xuhao']        = $k + 1;
            $res[$k]['project_name'] = $v['project_name'];
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr);
    }

    //获取供应商、厂商或维修商名称数据
    public function getAllSupplierFactoryOrRepair()
    {
        $type                  = I('get.type');
        $OfflineSuppliersModel = new OfflineSuppliersModel();
        $where['is_delete']    = C('NO_STATUS');
        switch ($type) {
            case 'supplier':
                $where['is_supplier'] = '1';
                break;
            case 'factory':
                $where['is_manufacturer'] = '1';
                break;
            case 'repair':
                $where['is_repair'] = '1';
                break;
        }

        $type = $OfflineSuppliersModel->DB_get_all('offline_suppliers',
            'olsid,sup_name,sup_num,sup_abbr,salesman_name,salesman_phone,address', $where);
        $res  = [];
        $i    = 0;
        foreach ($type as $k => $v) {
            $res[$i]['olsid']          = $v['olsid'];
            $res[$i]['sup_name']       = $v['sup_name'];
            $res[$i]['salesman_name']  = $v['salesman_name'];
            $res[$i]['salesman_phone'] = $v['salesman_phone'];
            $res[$i]['address']        = $v['address'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        //$arr = json_encode($arr);
        $this->ajaxReturn($arr, 'JSON');
    }

    /**
     * Notes: 获取品牌字典
     */
    public function getAllBrandDic()
    {
        $dicModel = new DictionaryModel();
        $brands   = $dicModel->DB_get_all('dic_brand', '*', ['is_delete' => 0]);
        $res      = [];
        $i        = 0;
        foreach ($brands as $k => $v) {
            $res[$i]['xuhao']      = $k + 1;
            $res[$i]['brand_id']   = $v['brand_id'];
            $res[$i]['brand_name'] = $v['brand_name'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr);
    }


    //获取入库单号
    public function getInwareNum()
    {
        $dicModel = new DictionaryModel();
        $brands   = $dicModel->DB_get_all('parts_inware_record', 'inware_num', ['is_delete' => 0]);
        $res      = [];
        $i        = 0;
        foreach ($brands as $k => $v) {
            $res[$i]['xuhao']      = $k + 1;
            $res[$i]['inware_num'] = $v['inware_num'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr);
    }

    //获取出库单号
    public function getOutwareNum()
    {
        $dicModel = new DictionaryModel();
        $brands   = $dicModel->DB_get_all('parts_outware_record', 'outware_num', ['is_delete' => 0]);
        $res      = [];
        $i        = 0;
        foreach ($brands as $k => $v) {
            $res[$i]['xuhao']       = $k + 1;
            $res[$i]['outware_num'] = $v['outware_num'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr);
    }

    //获取档案盒编号
    public function getBoxNum()
    {
        $archModel = new ArchivesModel();
        $data      = $archModel->DB_get_all('archives_box', 'box_num', ['is_delete' => 0]);
        $res       = [];
        $i         = 0;
        foreach ($data as $k => $v) {
            $res[$i]['xuhao']   = $k + 1;
            $res[$i]['box_num'] = $v['box_num'];
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr);
    }

    /**
     * Notes: 获取配件库配件信息
     */
    public function getAllPartsInfo()
    {
        $repairModel = new RepairModel();
        //查询未出库配件
        $where['status']    = ['EQ', C('OPEN_STATUS')];
        $where['is_delete'] = ['NEQ', C('YES_STATUS')];
        $data               = $repairModel->DB_get_all('dic_parts', 'parts,parts_model', ['status' => 1],
            'parts,parts_model');
        $res                = [];
        $i                  = 0;
        foreach ($data as $k => $v) {
            $res[$i]['xuhao']       = $k + 1;
            $res[$i]['parts']       = $v['parts'];
            $res[$i]['parts_model'] = $v['parts_model'];
            $res[$i]['status']      = 1;
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr, 'JSON');
    }

    //
    public function getSubsidiaryAllotAssets()
    {
        $dicModel               = new DictionaryModel();
        $where['hospital_id']   = ['EQ', session('current_hospitalid')];
        $where['main_assid']    = ['EQ', 0];
        $where['is_subsidiary'] = ['EQ', C('YES_STATUS')];
        $where['departid']      = ['IN', session('departid')];
        $Assets                 = $dicModel->DB_get_all('assets_info', 'assid,assets,assnum,assorignum', $where);
        $res                    = [];
        $i                      = 0;
        foreach ($Assets as $k => $v) {
            $res[$i]['assid']      = $v['assid'];
            $res[$i]['assnum']     = $v['assnum'];
            $res[$i]['assets']     = $v['assets'];
            $res[$i]['assorignum'] = $v['assorignum'];
            $res[$i]['pinyin']     = strtoupper(pinyin($v['assets'], 'first'));
            $i++;
        }
        $arr          = [];
        $arr['value'] = $res;
        $this->ajaxReturn($arr);
    }

    public function uploadPic()
    {
        if (IS_POST) {
            $action = I('post.action');
            if ($action == 'upload') {
                if (I('post.zm' == 'canvas')) {
                    $fin                    = I('post.filename');
                    $_FILES['file']['name'] = $fin;
                    $ty                     = explode('.', $fin);
                    $_FILES['file']['ext']  = $ty[1];
                }
                //上传设备图片
                $Tool = new ToolController();
                //设置文件类型
                $type = ['jpg', 'png', 'bmp', 'jpeg', 'gif'];
                //维修文件名目录设置
                $dirName = C('UPLOAD_DIR_PURCHASES_CHECK_ASSETS_FILE_NAME');
                //上传文件
                $base64 = I('POST.base64');
                if ($base64) {
                    $upload = $Tool->base64imgsave($base64, $dirName);
                } else {
                    $upload = $Tool->upFile($type, $dirName);
                }
                if ($upload['status'] == C('YES_STATUS')) {
                    $result['status']    = 1;
                    $result['file_url']  = $upload['src'];
                    $result['file_name'] = $upload['formerly'];
                    $result['file_type'] = $upload['ext'];
                    $result['save_name'] = $upload['title'];
                    $result['file_size'] = $upload['size'];
                    $result['msg']       = '成功上传';
                } else {
                    $result['status'] = -1;
                    $result['msg']    = '上传失败';
                }
                $this->ajaxReturn($result);
            } elseif ($action == 'save') {
                $assets_id = I('POST.assets_id');
                $username  = I('POST.username');
                $file_url  = I('POST.file_url');
                $file_size = I('POST.file_size');
                $save_name = I('POST.save_name');
                $file_name = I('POST.file_name');
                $file_type = I('POST.file_type');
                if (!$assets_id) {
                    die(json_encode(['status' => -1, 'msg' => '参数缺少,请按正常流程操作']));
                }
                $add = [];
                $i   = 0;
                foreach ($file_url as $k => $v) {
                    $add[$i]['assets_id'] = $assets_id;
                    $add[$i]['file_name'] = $file_name[$i];
                    $add[$i]['save_name'] = $save_name[$i];
                    $add[$i]['file_type'] = $file_type[$i];
                    $add[$i]['file_size'] = $file_size[$i];
                    $add[$i]['file_url']  = $file_url[$i];
                    $add[$i]['add_user']  = $username;
                    $add[$i]['add_time']  = date('Y-m-d H:i:s');
                    $i++;
                }
                if ($add) {
                    $PurchaseCheckModel = new PurchaseCheckModel();
                    $PurchaseCheckModel->insertDataAll('purchases_depart_apply_checkassets_file', $add);
                    $this->ajaxReturn(['status' => 1, 'msg' => '上传成功！']);
                } else {
                    $this->ajaxReturn(['status' => -1, 'msg' => '请先添加文件再上传！']);
                }
            }
        } else {
            $assets_id = I('get.id');
            $username  = I('get.username');
            $this->assign('assets_id', $assets_id);
            $this->assign('username', $username);
            $this->assign('url', C('ADMIN_NAME') . '/Public/uploadPic');
            $this->display();
        }
    }

    /**
     * Notes: 扫码上传报告
     */
    public function uploadReport()
    {
        if (IS_POST) {
            $action     = I('post.action');
            $table_name = I('post.t');
            if ($action == 'upload') {
                if (I('post.zm' == 'canvas')) {
                    $fin                    = I('post.filename');
                    $_FILES['file']['name'] = $fin;
                    $ty                     = explode('.', $fin);
                    $_FILES['file']['ext']  = $ty[1];
                }
                //上传设备图片
                $Tool = new ToolController();
                //设置文件类型
                $type = ['jpg', 'png', 'bmp', 'jpeg', 'gif'];
                //报告保存地址
                $dirName        = '';
                $is_water       = false;
                $is_compression = false;
                $water_text     = [];
                switch ($table_name) {
                    case 'assets_scrap':
                        $dirName = C('UPLOAD_DIR_REPORT_SCRAP_NAME');
                        break;
                    case 'assets_transfer':
                        $dirName = C('UPLOAD_DIR_REPORT_TRANSFER_NAME');
                        break;
                    case 'quality_details':
                        $dirName = C('UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME');
                        break;
                    case 'assets_info':
                        $dirName        = C('UPLOAD_DIR_ASSETS_NAME');
                        $is_compression = true;
                        break;
                    case 'assets_outside_file':
                        $dirName = C('UPLOAD_DIR_OUTSIDE_NAME');
                        break;
                    case 'quality_details_file':
                        $dirName       = C('UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME');
                        $is_water      = true;
                        $quaModel      = new QualityModel();
                        $watermark     = $quaModel->DB_get_one('base_setting', 'value',
                            ['module' => 'repair', 'set_item' => 'repair_print_watermark']);
                        $watermark     = json_decode($watermark['value'], true);
                        $qsinfo        = $quaModel->DB_get_one('quality_starts', 'plan_num', ['qsid' => I('post.id')]);
                        $water_text[0] = $watermark['watermark'];
                        $water_text[1] = $qsinfo['plan_num'];
                        $water_text[2] = date('Y-m-d H:i:s');
                        break;
                }
                //上传文件
                $base64 = I('POST.base64');
                if ($base64) {
                    $upload = $Tool->base64imgsave($base64, $dirName);
                } else {
                    $upload = $Tool->upFile($type, $dirName, $is_water, $water_text, $is_compression);
                }
                if ($upload['status'] == C('YES_STATUS')) {
                    $result['status']    = 1;
                    $result['file_url']  = $upload['src'];
                    $result['file_name'] = $upload['formerly'];
                    $result['file_type'] = $upload['ext'];
                    $result['save_name'] = $upload['title'];
                    $result['msg']       = '上传成功';
                    $size                = round($upload['size'] / 1024 / 1024, 2);
                    $result['file_size'] = $size;
                } else {
                    $result['status'] = -1;
                    $result['msg']    = '上传失败';
                }
                $this->ajaxReturn($result);
            } elseif ($action == 'save') {
                $id         = I('POST.id');
                $id2        = I('POST.id2');
                $id_name    = I('POST.i');
                $id_name2   = I('POST.i2');
                $table_name = I('POST.t');
                $type       = I('POST.type');
                $username   = I('POST.username');
                $file_url   = I('POST.file_url');
                $file_size  = I('POST.file_size');
                $save_name  = I('POST.save_name');
                $file_name  = I('POST.file_name');
                $file_type  = I('POST.file_type');
                if (!$id) {
                    die(json_encode(['status' => -1, 'msg' => '参数缺少,请按正常流程操作']));
                }
                switch ($table_name) {
                    case 'quality_details':
                        $data['report']   = $file_url[0];
                        $data['edittime'] = getHandleDate(time());
                        $data['edituser'] = $username;
                        $qualityModel     = new QualityModel();
                        $res              = $qualityModel->updateData($table_name, $data, [$id_name => $id]);
                        if ($res) {
                            $result['status'] = 1;
                            $result['msg']    = '上传成功！';
                        } else {
                            $result['status'] = -1;
                            $result['msg']    = '上传失败！';
                        }
                        $this->ajaxReturn($result);
                        break;
                    case 'assets_info':
                        $url = '';
                        foreach ($file_url as $k => $v) {
                            $url .= ',' . str_replace('/Public/uploads/assets/', '', $file_url[$k]);
                        }
                        $url = trim($url, ',');
                        if ($url) {
                            $asModel = new AssetsInfoModel();
                            $asModel->uploadAssetsPic($id, $url);
                            $this->ajaxReturn(['status' => 1, 'msg' => '上传成功！']);
                        } else {
                            $this->ajaxReturn(['status' => -1, 'msg' => '请先添加文件再上传！']);
                        }
                        break;
                    default:
                        $add = [];
                        $i   = 0;
                        foreach ($file_url as $k => $v) {
                            $add[$i][$id_name] = $id;
                            if ($id_name2) {
                                $add[$i][$id_name2] = $id2;
                            }
                            if ($type) {
                                $add[$i]['type'] = $type;
                            }
                            $add[$i]['file_name'] = $file_name[$i];
                            $add[$i]['save_name'] = $save_name[$i];
                            $add[$i]['file_type'] = $file_type[$i];
                            $add[$i]['file_size'] = $file_size[$i];
                            $add[$i]['file_url']  = $file_url[$i];
                            $add[$i]['add_user']  = $username;
                            $add[$i]['add_time']  = date('Y-m-d H:i:s');
                            $i++;
                        }
                        if ($add) {
                            $asModel = new AssetsInfoModel();
                            $asModel->insertDataAll($table_name, $add);
                            if ($table_name == 'purchases_assets_install_debug_report') {
                                $asModel->updateData('purchases_out_warehouse_assets', ['debug_status' => 1],
                                    ['ware_assets_id' => $id2]);
                            }
                            if ($table_name == 'purchases_assets_train_report') {
                                $asModel->updateData('purchases_out_warehouse_assets', ['train_status' => 1],
                                    ['train_id' => $id]);
                            }
                            $this->ajaxReturn(['status' => 1, 'msg' => '上传成功！']);
                        } else {
                            $this->ajaxReturn(['status' => -1, 'msg' => '请先添加文件再上传！']);
                        }
                        break;
                }
            }
        } else {
            $id         = I('get.id');
            $id2        = I('get.id2');
            $id_name    = I('get.i');
            $id_name2   = I('get.i2');
            $table_name = I('get.t');
            $username   = I('get.username');
            $type       = I('get.type');
            $this->assign('id', $id);
            $this->assign('id2', $id2);
            $this->assign('i', $id_name);
            $this->assign('i2', $id_name2);
            $this->assign('t', $table_name);
            $this->assign('username', $username);
            $this->assign('type', $type);
            $this->assign('url', C('ADMIN_NAME') . '/Public/uploadReport');
            $this->display();
        }
    }

    /**
     * Notes: 下载微信图片到本地服务器
     */
    public function wxImgDown()
    {
        $qualityModel = new QualityModel();
        $wxModel      = new WxAccessTokenModel();
        $access_token = $wxModel->getAccessToken();
        $qsdi         = I('post.qsid');
        $mdi          = I('post.mid');
        $type         = I('post.type');
        $Tool         = new ToolController();
        $style        = [
            'JPG',
            'PNG',
            'JPEG',
            'PDF',
            'BMP',
            'DOC',
            'DOCX',
            'jpg',
            'png',
            'jpeg',
            'pdf',
            'bmp',
            'doc',
            'docx',
        ];
        $dirName      = C('UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME') . '/' . date('Ymd');
        mkdir('./Public/uploads/' . $dirName, 0777);
        chmod('./Public/uploads/' . $dirName, 0777);
        //是否开启反向代理
        if (!C('OPEN_AGENT')) {
            $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=" . $access_token . "&media_id=" . $mdi;
            $raw = file_get_contents($url);
        } else {
            $raw = dcurl(C('GET_WX_FILES_URL') . '?access_token=' . $access_token . '&media_id=' . $mdi);
        }
        sleep(2);
        $file_path = './Public/uploads/' . $dirName . '/' . $mdi . '.jpg';
        file_put_contents($file_path, $raw);
        if (file_exists($file_path)) {
            $is_water      = true;
            $watermark     = $qualityModel->DB_get_one('base_setting', 'value',
                ['module' => 'repair', 'set_item' => 'repair_print_watermark']);
            $watermark     = json_decode($watermark['value'], true);
            $qsinfo        = $qualityModel->DB_get_one('quality_starts', 'plan_num', ['qsid' => $qsdi]);
            $water_text[0] = $watermark['watermark'];
            $water_text[1] = $qsinfo['plan_num'];
            $water_text[2] = date('Y-m-d H:i:s');
            $image         = new \Think\Image();
            $info          = $image->open($file_path);
            if ($is_water) {
                //添加水印
                $font_size  = ceil($image->width() / 20);
                $font_size2 = ceil($image->width() / 24);
                $offset     = ceil($image->width() / 5.5);
                $offset1    = ceil($offset * 1.3);
                $offset3    = ceil($offset / 1.4);

                $image->text($water_text[0], './Public/font/simkai.ttf', $font_size, '#B80D02',
                    \Think\Image::IMAGE_WATER_SOUTHEAST, -$offset1);
                $image->text($water_text[1], './Public/font/simkai.ttf', $font_size2, '#B80D02',
                    \Think\Image::IMAGE_WATER_SOUTHEAST, -$offset);
                $image->text($water_text[2], './Public/font/simkai.ttf', $font_size2, '#B80D02',
                    \Think\Image::IMAGE_WATER_SOUTHEAST, -$offset3);
                $image->save($file_path);
            }
            $pic_info['type'] = $image->type(); // 返回图片的类型
            $pic_info['size'] = $image->size();
            // 上传成功
            $data['qsid']      = $qsdi;
            $data['type']      = $type;
            $data['file_name'] = $mdi . '.jpg';
            $data['save_name'] = $mdi . '.jpg';
            $data['file_type'] = $pic_info['type'];
            $data['file_size'] = round($pic_info['size'][0] * $pic_info['size'][1] / 1000 / 1000, 2);
            $data['file_url']  = '/Public/uploads/' . $dirName . '/' . $mdi . '.jpg';
            $data['add_user']  = session('username');
            $data['add_time']  = date('Y-m-d H:i:s');
            $res               = $qualityModel->insertData('quality_details_file', $data);
            $this->ajaxReturn(['status' => 1, 'msg' => '成功！', 'info' => $data, 'file_id' => $res]);
        } else {
            $this->ajaxReturn(['status' => -1, 'msg' => '失败！']);
        }
    }

    /**
     * Notes: 下载微信语音到本地服务器
     */
    public function wxRecordDown()
    {
        $mdi          = I('post.mid');
        $repairModel  = new RepairModel();
        $wxModel      = new WxAccessTokenModel();
        $access_token = $wxModel->getAccessToken();

        $dirName = C('UPLOAD_DIR_RECORD_REPAIR_NAME') . '/' . date('Ymd');
        //\Think\Log::write('dirName='.$dirName);
        $dirarr = explode('/', $dirName);
        $tmpdir = './Public/uploads/';
        foreach ($dirarr as $v) {
            $tmpdir .= $v . '/';
            mkdir($tmpdir, 0777);
            chmod($tmpdir, 0777);
        }
        //是否开启反向代理
        if (!C('OPEN_AGENT')) {
            $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=" . $access_token . "&media_id=" . $mdi;
            $raw = file_get_contents($url);
        } else {
            //\Think\Log::write('wxRecordDown---url======='.C('GET_WX_FILES_URL').'?access_token='.$access_token.'&media_id='.$mdi);
            $raw = dcurl(C('GET_WX_FILES_URL') . '?access_token=' . $access_token . '&media_id=' . $mdi);
        }
        sleep(2);
        $file_path = './Public/uploads/' . $dirName . '/' . $mdi . '.amr';
        file_put_contents($file_path, $raw);
        if (file_exists($file_path)) {
            //转换为mp3
            $amr       = './Public/uploads/' . $dirName . '/' . $mdi;
            $mp3       = $amr . '.mp3';
            $mediaInfo = $this->amrToMp3($amr, $mp3);
            // 下载成功
            $data['seconds']           = $mediaInfo['seconds'];
            $data['record_url']        = '/Public/uploads/' . $dirName . '/' . $mdi . '.mp3';
            $data['add_user']          = session('username');
            $data['add_time']          = date('Y-m-d H:i:s');
            $res                       = $repairModel->insertData('repair_record', $data);
            $return_data['record_url'] = '/Public/uploads/' . $dirName . '/' . $mdi . '.mp3';
            $return_data['seconds']    = ceil($mediaInfo['seconds']);
            $this->ajaxReturn(['status' => 1, 'msg' => '成功！', 'info' => $return_data]);
        } else {
            $this->ajaxReturn(['status' => -1, 'msg' => '失败！']);
        }
    }

    /**
     * Notes: amr 转码为 mp3
     *
     * @param $amr
     * @param $mp3
     *
     * @return array
     */
    public function amrToMp3($amr, $mp3)
    {
        $amr = $amr . '.amr';
        if (file_exists($amr)) {
            shell_exec("ffmpeg -i $amr $mp3");
            //删除原文件
            shell_exec("rm -rf " . $amr);
        }
        $info = $this->getMedioInfo($mp3);
        return $info;
    }

    /**
     * Notes: 获取多媒体信息
     *
     * @param $file
     *
     * @return array
     */
    public function getMedioInfo($file)
    {
        $command = sprintf('ffmpeg -i "%s" 2>&1', $file);//你的安装路径

        ob_start();
        passthru($command);
        $info = ob_get_contents();
        ob_end_clean();

        $data = [];
        if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
            $data['duration'] = $match[1]; //播放时间
            $arr_duration     = explode(':', $match[1]);
            $data['seconds']  = $arr_duration[0] * 3600 + $arr_duration[1] * 60 + $arr_duration[2]; //转换播放时间为秒数
            $data['start']    = $match[2]; //开始时间
            $data['bitrate']  = $match[3]; //码率(kb)
        }
        if (preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $info, $match)) {
            $data['vcodec']     = $match[1]; //视频编码格式
            $data['vformat']    = $match[2]; //视频格式
            $data['resolution'] = $match[3]; //视频分辨率
            $arr_resolution     = explode('x', $match[3]);
            $data['width']      = $arr_resolution[0];
            $data['height']     = $arr_resolution[1];
        }
        if (preg_match("/Audio: (\w*), (\d*) Hz/", $info, $match)) {
            $data['acodec']      = $match[1]; //音频编码
            $data['asamplerate'] = $match[2]; //音频采样频率
        }
        if (isset($data['seconds']) && isset($data['start'])) {
            $data['play_time'] = $data['seconds'] + $data['start']; //实际播放时间
        }
        $data['size'] = filesize($file); //文件大小
        return $data;
    }

    /**
     * Notes: 该方法为测试定时任务接口用，可删除
     *
     * @return mixed
     */
    public function test_crontab()
    {
        $remodel        = new RepairModel();
        $data['userid'] = '1';
        $data['openid'] = 'sdfasd123';
        $res            = $remodel->insertData('wx', $data);
        return $res;
    }

    /**
     * 采集数据列表 （仅test.tj.tecev能访问）
     */
    public function cjsj()
    {
        if ($_SERVER['HTTP_HOST'] !== 'test.tj.tecev.com') {
            echo '禁止访问';
            exit;
        }
        if (IS_POST) {
            $cid    = I('post.cate_id');
            $saveId = I('post.checked_id');
            if (M('cjsj_tianyancha', 'zz_')->where(['id' => ['in', $saveId]])->save(['c_id' => $cid]) !== false) {
                $this->ajaxReturn(['msg' => '设置成功']);
            } else {
                $this->ajaxReturn(['msg' => '后台出错']);
            }
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'detail':
                    $id = I('get.id');
                    $this->assign('data', M('cjsj_tianyancha', 'zz_')->where(['id' => $id])->find());
                    $this->display('Public/cjsj-detail');
                    break;
                case 'getList':
                    $limit  = 10;
                    $page   = I('get.page') ? I('get.page') : 1;
                    $offset = ($page - 1) * $limit;
                    $where  = [];
                    if (I('get.title')) {
                        $where['A.title'] = ['like', '%' . I('get.title') . '%'];
                    }
                    if (I('get.content')) {
                        $where['A.content'] = ['like', '%' . I('get.content') . '%'];
                    }
                    if (I('get.date')) {
                        $where['A.zb_addtime'] = I('get.date');
                    }
                    if (I('get.cate')) {
                        $where['B.cate'] = ['like', '%' . I('get.cate') . '%'];
                    }
                    $data = M('cjsj_tianyancha',
                        'zz_')->alias('A')->field('A.id,A.title,A.url,A.zb_url,A.zb_addtime,B.cate AS cate')->join('zz_cjsj_cate AS B ON A.c_id = B.id')->where($where)->limit($offset . ',' . $limit)->select();
                    foreach ($data as &$v) {
                        $v['href'] = "cjsj?action=detail&id=$v[id]";
                    }
                    $total           = M('cjsj_tianyancha',
                        'zz_')->alias('A')->field('A.id,A.title,A.url,A.zb_url,A.zb_addtime,B.cate AS cate')->join('zz_cjsj_cate AS B ON A.c_id = B.id')->where($where)->count();
                    $result['total'] = $total;
                    $result['data']  = $data;
                    $result['pages'] = (int)ceil($total / $limit);
                    $this->ajaxReturn($result);
                    break;
                default:
                    $this->assign('cate', M('cjsj_cate', 'zz_')->select());
                    $this->display('Public/cjsj');
                    break;
            }
        }

    }

    public function get_gps()//gpg 转百度坐标
    {
        $lat    = I('post.latitude');
        $lng    = I('post.longitude');
        $gps    = true;
        $google = false;
        if ($gps) {
            $c = file_get_contents("http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x=$lng&y=$lat");
        } elseif ($google) {
            $c = file_get_contents("http://api.map.baidu.com/ag/coord/convert?from=2&to=4&x=$lng&y=$lat");
        } else {
            return [$lat, $lng];
        }
        $arr = (array)json_decode($c);
        if (!$arr['error']) {
            $res['latitude']  = base64_decode($arr['y']);
            $res['longitude'] = base64_decode($arr['x']);
            $this->ajaxReturn(['status' => 1, 'msg' => 'success！', 'info' => $res]);
        } else {
            $this->ajaxReturn(array('status' => -1, 'msg' => 'fail！'));
        }

    }
}
