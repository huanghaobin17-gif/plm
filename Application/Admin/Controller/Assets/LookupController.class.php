<?php

namespace Admin\Controller\Assets;

use Admin\Common\CiAnTongAPI;
use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\NotCheckLogin\PublicController;
use Admin\Controller\Tool\ToolController;
use Admin\Model\AdverseModel;
use Admin\Model\AssetsBorrowModel;
use Admin\Model\AssetsInfoModel;
use Admin\Model\AssetsInsuranceModel;
use Admin\Model\AssetsOutsideModel;
use Admin\Model\AssetsTransferModel;
use Admin\Model\CategoryModel;
use Admin\Model\DictionaryModel;
use Admin\Model\EditModel;
use Think\Db;
use Admin\Model\OfflineSuppliersModel;

class LookupController extends CheckLoginController
{
    private $MODULE = 'Assets';
    private $Controller = 'Lookup';

    protected function __initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $this->display();
    }

    //设备生命历程
    public function assetsLifeList()
    {
        //print_r($_GET);die;
        $asModel = new AssetsInfoModel();
        if (IS_POST) {
            $result = $asModel->getAssetsLifeList();
            //获取对应设备的附件和增值信息
            $result['rows'] = $asModel->getLifeIncrementAndAccessory($result['rows']);
            $this->ajaxReturn($result, 'json');
        } else {
            $action = I('GET.action');
            //判断有无查看设备档案的权限
            $showAssetsfile = get_menu('Assets', 'Lookup', 'showAssetsfile');
            //判断有无查看技术资料的权限
            $showTechnicalInformation = get_menu('Assets', 'Lookup', 'showTechnicalInformation');
            //判断有无查看设备参保的权限
            $showAssetsInsurance = get_menu('Assets', 'Lookup', 'showAssetsInsurance');
            if ($action == 'showLife') {
                $assid     = I('GET.assid');
                $changeTab = I('GET.changeTab');
                //组织表单第一部分数据设备基础信息
                $assets = $asModel->getAssetsInfo($assid);
                //组织表单第三部分厂商信息
                $join         = 'LEFT JOIN sb_offline_suppliers AS O ON O.olsid=F.ols_facid';
                $fields       = 'F.ols_facid,O.sup_name AS factory,O.salesman_name AS factory_user,O.salesman_phone AS factory_tel';
                $ols_factory  = $asModel->DB_get_one_join('assets_factory', 'F', $fields, $join,
                    ['F.assid' => ['EQ', $assets['assid']]]);
                $join         = 'LEFT JOIN sb_offline_suppliers AS O ON O.olsid=F.ols_supid';
                $fields       = 'F.ols_supid,O.sup_name AS supplier,O.salesman_name AS supp_user,O.salesman_phone AS supp_tel';
                $ols_supplier = $asModel->DB_get_one_join('assets_factory', 'F', $fields, $join,
                    ['F.assid' => ['EQ', $assets['assid']]]);
                $join         = 'LEFT JOIN sb_offline_suppliers AS O ON O.olsid=F.ols_repid';
                $fields       = 'F.ols_repid,O.sup_name AS repair,O.salesman_name AS repa_user,O.salesman_phone AS repa_tel';
                $ols_repair   = $asModel->DB_get_one_join('assets_factory', 'F', $fields, $join,
                    ['F.assid' => ['EQ', $assets['assid']]]);
                //查baseSetting表
                $baseSetting = $asModel->DB_get_all('base_setting', 'set_item,value', '', '', 'setid asc', '');
                //组织数据
                $base = [];
                foreach ($baseSetting as $k => $v) {
                    $base[$k]['set_item'] = $v['set_item'];
                    $base[$k]['value']    = json_decode($v['value'], 'true');
                }
                //辅助分类
                $acin_category = [];
                foreach ($base as $k => $v) {
                    if ($v['set_item'] == 'acin_category') {
                        $acin_category = $v['value'];
                    }
                }
                //获取生产商资质文件信息
                $fac_files = $asModel->DB_get_all('offline_suppliers_file', '*',
                    ['olsid' => $ols_factory['ols_facid']]);
                foreach ($fac_files as $k => $v) {
                    $fac_files[$k]['adddate']     = date("Y-m-d", $v['adddate']);
                    $fac_files[$k]['record_date'] = date("Y-m-d", $v['record_date']);
                    $fac_files[$k]['term_date']   = date("Y-m-d", $v['term_date']);
                    $jpghtml                      = '';
                    $supplement                   = 'data-path="' . $v['url'] . '" data-name="' . $v['name'] . '.' . $v['ext'] . '"' . 'data-id="' . $v['fileid'] . '" data-type="quali"';
                    $jpghtml                      .= $asModel->returnListLink('预览', '', '',
                        C('BTN_CURRENCY') . ' showFile', '', $supplement);
                    $jpghtml                      .= $asModel->returnListLink('下载', '', '',
                        C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                    $fac_files[$k]['html']        = $jpghtml;
                }
                //获取供应商资质文件信息
                $sup_files = $asModel->DB_get_all('offline_suppliers_file', '*',
                    ['olsid' => $ols_supplier['ols_supid']]);
                foreach ($sup_files as $k => $v) {
                    $sup_files[$k]['adddate']     = date("Y-m-d", $v['adddate']);
                    $sup_files[$k]['record_date'] = date("Y-m-d", $v['record_date']);
                    $sup_files[$k]['term_date']   = date("Y-m-d", $v['term_date']);
                    $jpghtml                      = '';
                    $supplement                   = 'data-path="' . $v['url'] . '" data-name="' . $v['name'] . '.' . $v['ext'] . '"' . 'data-id="' . $v['fileid'] . '" data-type="quali"';
                    $jpghtml                      .= $asModel->returnListLink('预览', '', '',
                        C('BTN_CURRENCY') . ' showFile', '', $supplement);
                    $jpghtml                      .= $asModel->returnListLink('下载', '', '',
                        C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                    $sup_files[$k]['html']        = $jpghtml;
                }
                //获取生产商资质文件信息
                $rep_files = $asModel->DB_get_all('offline_suppliers_file', '*', ['olsid' => $ols_repair['ols_repid']]);
                foreach ($rep_files as $k => $v) {
                    $rep_files[$k]['adddate']     = date("Y-m-d", $v['adddate']);
                    $rep_files[$k]['record_date'] = date("Y-m-d", $v['record_date']);
                    $rep_files[$k]['term_date']   = date("Y-m-d", $v['term_date']);
                    $jpghtml                      = '';
                    $supplement                   = 'data-path="' . $v['url'] . '" data-name="' . $v['name'] . '.' . $v['ext'] . '"' . 'data-id="' . $v['fileid'] . '" data-type="quali"';
                    $jpghtml                      .= $asModel->returnListLink('预览', '', '',
                        C('BTN_CURRENCY') . ' showFile', '', $supplement);
                    $jpghtml                      .= $asModel->returnListLink('下载', '', '',
                        C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                    $rep_files[$k]['html']        = $jpghtml;
                }
                if ($showTechnicalInformation) {
                    //获取技术资料文件信息
                    $techni_files = $asModel->DB_get_all('assets_technical_file', '*', ['assid' => $assid]);
                    foreach ($techni_files as $k => $v) {
                        $jpghtml                  = '';
                        $supplement               = 'data-path="' . $v['file_url'] . '" data-name="' . $v['file_name'] . '.' . $v['file_type'] . '"' . 'data-id="' . $v['tech_id'] . '" data-type="technical"';
                        $jpghtml                  .= $asModel->returnListLink('预览', '', '',
                            C('BTN_CURRENCY') . ' showFile', '', $supplement);
                        $jpghtml                  .= $asModel->returnListLink('下载', '', '',
                            C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                        $techni_files[$k]['html'] = $jpghtml;
                    }
                    $this->assign('techni_files', $techni_files);
                    $this->assign('showTechnicalInformation', 1);
                } else {
                    $this->assign('showTechnicalInformation', 0);
                }
                if ($showAssetsfile) {
                    //获取档案资料文件信息
                    $archives_files = $asModel->DB_get_all('assets_archives_file', '*', ['assid' => $assid]);
                    foreach ($archives_files as $k => $v) {
                        $jpghtml                    = '';
                        $supplement                 = 'data-path="' . $v['file_url'] . '" data-name="' . $v['file_name'] . '.' . $v['file_type'] . '"' . 'data-id="' . $v['arc_id'] . '" data-type="archives"';
                        $jpghtml                    .= $asModel->returnListLink('预览', '', '',
                            C('BTN_CURRENCY') . ' showFile', '', $supplement);
                        $jpghtml                    .= $asModel->returnListLink('下载', '', '',
                            C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                        $archives_files[$k]['html'] = $jpghtml;
                    }
                    $this->assign('archives_files', $archives_files);
                    $this->assign('showAssetsfile', 1);
                } else {
                    $this->assign('showAssetsfile', 0);
                }
                //获取附属设备信息
                $increment = $asModel->DB_get_all('assets_increment', '*', ['assid' => $assid]);
                foreach ($increment as $k => $v) {
                    $increment[$k]['catname'] = $acin_category[$v['incre_catid']];
                }
                if ($showAssetsInsurance) {
                    //获取保修厂家数据
                    $usecompany = $asModel->DB_get_one('assets_factory', 'repair,repa_user,repa_tel',
                        'afid=' . $assets['afid']);
                    $this->assign('usecompany', json_encode($usecompany));
                    $this->assign('showAssetsInsurance', 1);
                } else {
                    $this->assign('usecompany', '');
                    $this->assign('showAssetsInsurance', 0);
                }
                //生命历程
                $life               = $asModel->getLifeInfo($assid);
                $AssetsOutsideModel = new AssetsOutsideModel();
                $outsideAssets      = $AssetsOutsideModel->getAssetsBasic($assid);
                $outside            = $AssetsOutsideModel->getOutsideBasic(null, $assid);
                $approve            = $AssetsOutsideModel->getOutsideApprovBasic($outside['outid']);
                $applyFile          = $AssetsOutsideModel->getFileList(C('OUTSIDE_FILE_TYPE_APPLY'), $outside['outid']);
                $checkFile          = $AssetsOutsideModel->getFileList(C('OUTSIDE_FILE_TYPE_CHECK'), $outside['outid']);
                $assets             = array_merge($assets, $outsideAssets);

                $assetsPic = explode(',', $assets['pic_url']);
                foreach ($assetsPic as $k => $v) {
                    $assetsPic[$k] = '/Public/uploads/assets/' . $v;
                }
                $this->assign('assetsPic', $assetsPic);
                $this->assign('life', $life);
                $this->assign('changeTab', $changeTab);
                $this->assign('uploadAction', 'uploadInsurance');
                $this->assign('ols_factory', $ols_factory);
                $this->assign('ols_supplier', $ols_supplier);
                $this->assign('ols_repair', $ols_repair);
                $this->assign('acin_category', $acin_category);
                $this->assign('fac_files', $fac_files);
                $this->assign('sup_files', $sup_files);
                $this->assign('rep_files', $rep_files);
                $this->assign('increment', $increment);
                $this->assign('assets', $assets);
                $this->assign('assid', $assid);
                $this->assign('approve', $approve);
                $this->assign('outside', $outside);
                $this->assign('applyFile', $applyFile);
                $this->assign('checkFile', $checkFile);
                $this->assign('showAssets', C('ADMIN_NAME') . '/Lookup/showAssets');
                $this->display('showLife');
            } else {
                $users = $asModel->getUser();
                $this->assign('users', $users);
                //读取设备信息所有字段
                $header = $asModel->getAssetsLifeHeader();
                $header = json_encode($header);
                $this->assign('header', $header);
                $this->assign('assetsLifeList', get_url());
                //所属科室
                $notCheck = new PublicController();
                $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                $this->display();
            }
        }
    }

    //主设备列表
    public function getAssetsList()
    {
        $asModel = new AssetsInfoModel();
        if (IS_POST) {
            switch (I('POST.type')) {
                case 'changeFields':
                    $showFields = I('POST.showFields');
                    $header     = $asModel->getTableHeader($showFields);
                    $this->ajaxReturn(['status' => 1, 'msg' => 'ok', 'header' => $header], 'json');
                    break;
                case 'highSearch':
                    $result = $asModel->getAssetsInfoList(1);
                    //获取对应设备的附件和增值信息
                    $result['rows'] = $asModel->getIncrementAndAccessory($result['rows']);
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    $result = $asModel->getAssetsInfoList();

                    //获取对应设备的附件和增值信息
                    $result['rows'] = $asModel->getIncrementAndAccessory($result['rows'], $result['del_display']);
                    // var_dump($result);exit;
                    $this->ajaxReturn($result, 'json');

                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showAssetLabel':
                    //显示二维码
                    $this->showAssetLabel();
                    break;
                case 'lifeAssetsList':
                    //生命支持列表
                    $this->showLifeAssetsList();
                    break;
                default:
                    //设备列表
                    $this->showgetAssetsList();
                    break;
            }
        }
    }

    //显示二维码
    private function showAssetLabel()
    {
        $assid   = I('GET.assid');
        $asModel = new AssetsInfoModel();
        $asInfo  = $asModel->DB_get_one('assets_info',
            'assid,assets,assnum,assorignum,serialnum,brand,model,departid,opendate,code_url', ['assid' => $assid]);
        if (!$asInfo) {
            $this->error('查找不到该设备信息！');
        }
        //查询二维码是否已经生成
        if (!$asInfo['code_url']) {
            //未生成二维码，调用接口生成二维码
            $codeUrl = $asModel->createCodePic($asInfo['assnum']);
            if ($codeUrl) {
                //保存二维码图片地址到数据库
                $codeUrl = trim($codeUrl, '.');
                $asModel->updateData('assets_info', ['code_url' => $codeUrl], ['assid' => $assid]);
                $asInfo['code_url'] = $codeUrl;
            }
        } else {
            //存在二维码地址，查询文件是否存在
            $fileExists = file_exists('.' . $asInfo['code_url']);
            if (!$fileExists) {
                //文件已不存在，重新生成二维码文件
                $codeUrl = $asModel->createCodePic($asInfo['assnum']);
                if ($codeUrl) {
                    //保存二维码图片地址到数据库
                    $codeUrl = trim($codeUrl, '.');
                    $asModel->updateData('assets_info', ['code_url' => $codeUrl], ['assid' => $assid]);
                    $asInfo['code_url'] = $codeUrl;
                }
            }
        }
        $asInfo['opendate']   = HandleEmptyNull($asInfo['opendate']);
        $asInfo['brand']      = ($asInfo['brand'] == '') ? '无' : $asInfo['brand'];
        $department           = $asModel->DB_get_one('department', 'department', ['departid' => $asInfo['departid']]);
        $asInfo['department'] = $department['department'];
        $this->assign('asInfo', $asInfo);
        $this->assign('hospitalName', session('current_hospitalname'));
        $this->display('showAssetLabel');
    }

    //设备列表
    private function showgetAssetsList()
    {
        $assidArr = I('get.assid');
        if ($assidArr) {
            $this->assign('assidArr', $assidArr);
        }
        $asModel = new AssetsInfoModel();
        $users   = $asModel->getUser();
        $this->assign('users', $users);
        $this->assign('header', '');
        $this->assign('getAssetsList', get_url());
        //所属科室
        $notCheck  = new PublicController();
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $this->assign('is_showPrice', 0);
        } else {
            $this->assign('is_showPrice', 1);
        }
        $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
        if (I('get.type') == 'expect_assets') {
            //查询过期设备列表
            $this->assign('expect_assid', implode(',', session('expect_assid')));
        }
        $this->display();
    }

    //生命支持列表
    private function showLifeAssetsList()
    {
        $asModel = new AssetsInfoModel();
        //print_r($_POST);die;
        // echo get_url();die;
        $users = $asModel->getUser();
        $this->assign('users', $users);
        $this->assign('actionName', '生命支持设备');
        $this->assign('lifeAssetsList', get_url() . '?action=lifeAssetsList');
        //所属科室
        $notCheck = new PublicController();
        $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
        $this->display('lifeAssetsList');
    }

    //添加主设备
    public function addAssets()
    {

        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'editFactory':
                    //编辑厂商资质信息
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->updateFactory();
                    $this->ajaxReturn($result);
                    break;
                case 'uploadFile':
                    //上传技术资料、设备档案文件
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->uploadAssetsFile();
                    $this->ajaxReturn($result);
                    break;
                case 'bindSameAssetsFile':
                    //补充绑定相同文档的设备群
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->bindSameAssetFileData();
                    $this->ajaxReturn($result);
                    break;
                case 'uploadPic':
                    $assid = I('post.assid');
                    //总数
                    $count = I('post.count');
                    if ($count) {
                        $asModel       = new AssetsInfoModel();
                        $assnum        = $asModel->DB_get_one('assets_info', 'assnum', ['assid' => $assid]);
                        $log['assnum'] = $assnum['assnum'];
                        $log['count']  = $count;
                        $text          = getLogText('addAssetsPic', $log);
                        $asModel->addLog('assets_info', '', $text, '', '');
                    } else {
                        //上传设备图片
                        $Tool = new ToolController();
                        //设置文件类型
                        $type = ['jpg', 'png', 'bmp', 'jpeg', 'gif'];
                        //维修文件名目录设置
                        $dirName = C('UPLOAD_DIR_ASSETS_NAME');
                        //上传文件
                        $upload = $Tool->upFile($type, $dirName, false, [], true);
                        if ($upload['status'] == C('YES_STATUS')) {
                            $asModel = new AssetsInfoModel();
                            $result  = $asModel->uploadAssetsPic($assid, $upload['name']);
                        } else {
                            $result['status'] = -1;
                            $result['msg']    = $upload['msg'];
                        }
                        $this->ajaxReturn($result);
                    }
                    break;
                case 'addMateriel':
                    //保存附属设备数据
                    $tabName = I('POST.tabName');
                    if ($tabName == 'increment') {
                        //保存附属设备数据
                        $asModel = new AssetsInfoModel();
                        $res     = $asModel->saveIncreMent();
                        $this->ajaxReturn($res);
                    }
                    break;
                case 'getDicAssetsDetail':
                    //获取 对应字典数据详情
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->getDicAssetsDetail();
                    $this->ajaxReturn($result);
                    break;
                case 'getAssetsDic':
                    //获取医院对应的设备字典
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->getDicAssets();
                    $this->ajaxReturn($result);
                    break;
                case 'getDepartmentList':
                    //获取医院对应的科室列数据
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->getDepartmentList();
                    $this->ajaxReturn($result);
                    break;
                case 'getdepartDetail':
                    //获取科室对应的信息
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->getdepartDetail();
                    $this->ajaxReturn($result);
                    break;
                case 'addDic':
                    //补充字典
                    $dicModel = new DictionaryModel();
                    $result   = $dicModel->addDic();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getMainAssetsBasic':
                    //获取主设备的信息
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->getMainAssetsBasic();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'updateUpload':
                    $asModel = new AssetsInfoModel();
                    $new     = $asModel->DB_get_all('assets_info', 'assid', '', '', 'assid desc', 1);

                    break;
                case 'getjudgement':
                    //判断数据是否合法
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->getjudgement();
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //添加设备操作
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->addAssets();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'editFactory':
                    //编辑厂商信息
                    $afid    = I('GET.afid');
                    $asModel = new AssetsInfoModel();
                    $facInfo = $asModel->DB_get_one('assets_factory', '', ['afid' => $afid]);
                    $this->assign('facInfo', $facInfo);
                    $this->display('editFactory');
                    break;
                case 'delFile':
                    //删除资质文件、档案材料
                    $asModel = new AssetsInfoModel();
                    $id      = I('GET.id');
                    $dtype   = I('GET.dtype');
                    $where   = [];
                    $res     = '';
                    switch ($dtype) {
                        case 'technical':
                            $tableName        = 'assets_technical_file';
                            $where['tech_id'] = $id;
                            if (I('get.allDelete') == 1) {
                                $thisAssetsInfo     = $asModel->DB_get_one('assets_technical_file',
                                    'file_name,file_url,file_type', $where);
                                $deleteId           = $asModel->DB_get_one('assets_technical_file',
                                    'group_concat(tech_id) AS techId', [
                                        'file_name' => $thisAssetsInfo['file_name'],
                                        'file_url'  => $thisAssetsInfo['file_url'],
                                        'file_type' => $thisAssetsInfo['file_type'],
                                    ]);
                                $deleteId['techId'] .= ',' . $id;
                                $res                = $asModel->deleteData($tableName,
                                    ['tech_id' => ['IN', $deleteId['techId']]]);
                            } else {
                                $res = $asModel->deleteData($tableName, $where);
                            }
                            break;
                        case 'archives':
                            $tableName       = 'assets_archives_file';
                            $where['arc_id'] = $id;
                            $res             = $asModel->deleteData($tableName, $where);
                            break;
                        case 'quali':
                            $tableName         = 'assets_factory_qualification_file';
                            $where['quali_id'] = $id;
                            $res               = $asModel->deleteData($tableName, $where);
                            break;

                    }
                    if ($res) {
                        $this->ajaxReturn(['status' => 1, 'msg' => '删除文件成功！']);
                    } else {
                        $this->ajaxReturn(['status' => -1, 'msg' => '删除文件失败！']);
                    }
                    break;
                case 'checkRepeatFile':
                    //检测是否有公用一份文件的
                    $asModel          = new AssetsInfoModel();
                    $where['tech_id'] = I('GET.id');
                    $thisAssetsInfo   = $asModel->DB_get_one('assets_technical_file', 'file_name,file_url,file_type',
                        $where);
                    $repeatInfo       = $asModel->DB_get_all('assets_technical_file', '', [
                        'file_name' => $thisAssetsInfo['file_name'],
                        'file_url'  => $thisAssetsInfo['file_url'],
                        'file_type' => $thisAssetsInfo['file_type'],
                    ]);
                    if (count($repeatInfo) > 0) {
                        $num = count($repeatInfo);
                        $this->ajaxReturn([
                            'status' => 1,
                            'msg'    => '该文件还有另外' . ($num - 1) . '台设备正在使用，是否一并删除？',
                        ]);
                    } else {
                        $this->ajaxReturn(['status' => -1]);
                    }
                    break;
                case 'getNewSuppliers':
                    //补充厂商信息
                    $type    = I('get.type');
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->getSuppliers($type);
                    $this->ajaxReturn($result, 'json');
                    break;
                default:
                    //添加设备页面
                    $this->showAddAssets();
                    break;
            }
        }
    }

    //添加设备页面
    private function showAddAssets()
    {
        $asModel = new AssetsInfoModel();
        //查资产负责人
        $user = $asModel->DB_get_all('user', 'username', ['is_super' => ['NEQ', C('YES_STATUS')]], '', 'userid ASC');
        //辅助分类
        $assets_helpcat = $asModel->getBaseSettingAssets('assets_helpcat');
        //财务分类
        $assets_finance = $asModel->getBaseSettingAssets('assets_finance');
        //附属设备分类
        $acin_category = $asModel->getBaseSettingAssets('acin_category');
        //资金来源
        $assets_capitalfrom = $asModel->getBaseSettingAssets('assets_capitalfrom');
        //资产来源
        $assets_assfrom = $asModel->getBaseSettingAssets('assets_assfrom');
        //设备状态
        $assets_status                            = [];
        $assets_status[C('ASSETS_STATUS_USE')]    = C('ASSETS_STATUS_USE_NAME');
        $assets_status[C('ASSETS_STATUS_REPAIR')] = C('ASSETS_STATUS_REPAIR_NAME');
        $assets_status[C('ASSETS_STATUS_SCRAP')]  = C('ASSETS_STATUS_SCRAP_NAME');
        //查当前录入员工
        $adduser = $asModel->DB_get_one('user', 'userid,username', ['userid' => session('userid')]);
        //当前时间
        $this->assign('assets_finance', $assets_finance);
        $this->assign('acin_category', $acin_category);
        $this->assign('assets_helpcat', $assets_helpcat);
        $this->assign('assets_status', $assets_status);
        $this->assign('assets_capitalfrom', $assets_capitalfrom);
        $this->assign('assets_assfrom', $assets_assfrom);

        $this->assign('adduser', $adduser);
        $this->assign('now', getHandleTime(time()));
        $this->assign('user', $user);
        $hospital_id = session('current_hospitalid');
        if ($hospital_id) {
            //获取设备字典
            $dic_where['hospital_id'] = ['EQ', $hospital_id];
            $dic_where['status']      = ['EQ', C('OPEN_STATUS')];
            $dic_assets               = $asModel->DB_get_all('dic_assets', 'assets', $dic_where);
            $this->assign('dic_assets', $dic_assets);
        }
        //获取科室
        $departmentWhere['hospital_id'] = $hospital_id;
        $department                     = $this->getSelectDepartments($departmentWhere);
        $this->assign('department', $department);
        //获取生产 供应 维修商
        $allsuppliers = $asModel->getSuppliers();
        $factory      = [];
        $supplier     = [];
        $repair       = [];
        foreach ($allsuppliers as $k => $v) {
            if ($v['is_manufacturer'] == 1) {
                $factory[] = $v;
            }
            if ($v['is_supplier'] == 1) {
                $supplier[] = $v;
            }
            if ($v['is_repair'] == 1) {
                $repair[] = $v;
            }
        }
        $assetsLevel = $asModel->getAssetsLevel();
        $this->assign('assetsLevel', $assetsLevel);
        $this->assign('factory', $factory);
        $this->assign('supplier', $supplier);
        $this->assign('repair', $repair);
        //设备档案资料上传标识符
        $this->assign('identifier', get_random_str(6));
        $this->assign('addAssetsUrl', ACTION_NAME);
        $this->display();
    }

    //编辑设备
    public function editAssets()
    {
        if (IS_POST) {
            $action  = I('POST.action');
            $asModel = new AssetsInfoModel();
            switch ($action) {
                case 'getDicAssetsDetail':
                    //获取 对应字典数据详情
                    $result = $asModel->getDicAssetsDetail();
                    $this->ajaxReturn($result);
                    break;
                case 'getdepartDetail':
                    //获取科室对应的信息
                    $result = $asModel->getdepartDetail();
                    $this->ajaxReturn($result);
                    break;
                case 'getjudgement':
                    //判断数据是否合法
                    $result = $asModel->getjudgement();
                    $this->ajaxReturn($result);
                    break;
                default:
                    //存储编辑科室信息等待审核
                    $result = $asModel->editdepartment();
                    if ($result == 0) {
                        $this->ajaxReturn(['status' => -1, 'msg' => '编辑出现错误,请重新编辑']);
                    }
                    //编辑设备操作
                    $result = $asModel->editAssets();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                default:
                    //编辑设备详情页面
                    $this->showEditAssets();
                    break;
            }
        }
    }

    //编辑设备页面
    private function showEditAssets()
    {
        $assid = I('GET.assid');
        //实例化模型
        $asModel = new AssetsInfoModel();
        //查当前信息所对应的基本信息
        $assetsinfo = $asModel->DB_get_one('assets_info', '', ['assid' => $assid]);
        if ($assetsinfo['hospital_id'] != session('current_hospitalid')) {
            $this->error('当前操作医院无此设备！');
        }
        $assetsinfo['factorydate']    = HandleEmptyNull($assetsinfo['factorydate'], '');
        $assetsinfo['opendate']       = HandleEmptyNull($assetsinfo['opendate'], '');
        $assetsinfo['storage_date']   = HandleEmptyNull($assetsinfo['storage_date'], '');
        $assetsinfo['guarantee_date'] = HandleEmptyNull($assetsinfo['guarantee_date'], '');

        //判断有无查看原值的权限
        $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
        if (!$showPrice) {
            $assetsinfo['buy_price'] = '***';
        } else {
            $this->assign('showPrice', 1);
        }
        if (!$assetsinfo) {
            $this->error('该设备不存在！');
        }
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        $assetsinfo['department'] = $departname[$assetsinfo['departid']]['department'];
        //查当前信息所对应的厂商信息
        $factoryinfo = $asModel->DB_get_one('assets_factory', '', ['afid' => $assetsinfo['afid']]);
        //辅助分类
        $assets_helpcat = $asModel->getBaseSettingAssets('assets_helpcat');
        //财务分类
        $assets_finance = $asModel->getBaseSettingAssets('assets_finance');
        //附属设备分类
        $acin_category = $asModel->getBaseSettingAssets('acin_category');
        //资金来源
        $assets_capitalfrom = $asModel->getBaseSettingAssets('assets_capitalfrom');
        //资产来源
        $assets_assfrom  = $asModel->getBaseSettingAssets('assets_assfrom');
        $assets_category = '';
        if ($assetsinfo['is_firstaid'] == C('YES_STATUS')) {
            $assets_category .= ',is_firstaid';
        }
        if ($assetsinfo['is_special'] == C('YES_STATUS')) {
            $assets_category .= ',is_special';
        }
        if ($assetsinfo['is_metering'] == C('YES_STATUS')) {
            $assets_category .= ',is_metering';
        }
        if ($assetsinfo['is_qualityAssets'] == C('YES_STATUS')) {
            $assets_category .= ',is_qualityAssets';
        }
        if ($assetsinfo['is_benefit'] == C('YES_STATUS')) {
            $assets_category .= ',is_benefit';
        }
        if ($assetsinfo['is_lifesupport'] == C('YES_STATUS')) {
            $assets_category .= ',is_lifesupport';
        }
        $assetsinfo['assets_category'] = trim($assets_category, ',');
        if ($assetsinfo['paytime'] < '1900-00-00') {
            # 付款日期一旦是低于十九世纪 一律为空
            $assetsinfo['paytime'] = '';
        }
        //查设备分类
        $category = $asModel->DB_get_all('category', 'catid,catenum,category,parentid',
            ['status' => 1, 'is_delete' => 0, 'hospital_id' => $assetsinfo['hospital_id']], '', 'catid asc', '');
        $category = getTree('parentid', 'catid', $category, 0, 0, ' ➣ ');
        $this->assign('asInfo', $assetsinfo);
        $this->assign('facInfo', $factoryinfo);
        $this->assign('assets_finance', $assets_finance);
        $this->assign('assets_helpcat', $assets_helpcat);
        $this->assign('assets_capitalfrom', $assets_capitalfrom);
        $this->assign('acin_category', $acin_category);
        $this->assign('category', $category);
        $this->assign('assets_assfrom', $assets_assfrom);
        $this->assign('now', getHandleTime(time()));
        //获取设备字典
        $dic_where['hospital_id'] = ['EQ', $assetsinfo['hospital_id']];
        $dic_where['status']      = ['EQ', C('OPEN_STATUS')];
        $dic_assets               = $asModel->DB_get_all('dic_assets', 'assets', $dic_where);
        $assets_true              = false;
        foreach ($dic_assets as $dic_assets_value) {
            if ($dic_assets_value['assets'] == $assetsinfo['assets']) {
                $assets_true = true;
            }
        }
        if ($assets_true == false) {
            $this_assets  = ['assets' => $assetsinfo['assets']];
            $dic_assets[] = $this_assets;
        }
        $this->assign('dic_assets', $dic_assets);

        //获取科室
        $departmentWhere['hospital_id'] = $assetsinfo['hospital_id'];
        $department                     = $this->getSelectDepartments($departmentWhere);
        $this->assign('department', $department);
        //判断该设备是否正在修改科室，如果是传递相关信息
        $editdepartment = $asModel->DB_get_one('edit', 'update_data',
            ['update_where' => '{"assid":"' . $assid . '"}', 'operation_type' => 'edit', 'is_approval' => 0]);
        $edit_dispaly   = '0';
        if ($editdepartment) {
            $editdepartment_arr['managedepart'] = json_decode($editdepartment['update_data'], true)['managedepart'];
            $edit_dispaly                       = '1';
        }
        $editdepartment_arr['is_dispaly'] = $edit_dispaly;
        $this->assign('editdepartment', $editdepartment_arr);
        //获取生产 供应 维修商
        $allsuppliers = $asModel->getSuppliers();
        $factory      = [];
        $supplier     = [];
        $repair       = [];
        foreach ($allsuppliers as $k => $v) {
            if ($v['is_manufacturer'] == 1) {
                $factory[] = $v;
            }
            if ($v['is_supplier'] == 1) {
                $supplier[] = $v;
            }
            if ($v['is_repair'] == 1) {
                $repair[] = $v;
            }
        }
        //查询当前用户是否有权限进行添加主设备
        $addAssets = get_menu('Assets', 'Lookup', 'addAssets');
        //获取档案资料文件信息
        $archives_files = $asModel->DB_get_all('assets_archives_file', '*', ['assid' => $assid], '', 'arc_id asc');
        foreach ($archives_files as $k => $v) {
            $html       = '';
            $supplement = 'data-path="' . $v['file_url'] . '" data-name="' . $v['file_name'] . '.' . $v['file_type'] . '"';
            if ($v['file_type'] == 'jpg' || $v['file_type'] == 'jpeg' || $v['file_type'] == 'png' || $v['file_type'] == 'pdf') {
                $html .= $asModel->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' showFile', '', $supplement);
            }
            if ($v['archive_time'] == '0000-00-00' || !$v['archive_time']) {
                $archives_files[$k]['archive_time'] = '<input type="text" name="archives_time[]" value="" readonly placeholder="点击选择日期" class="layui-input archives-time" style="cursor: pointer;border: none;height: 33px;">';
            } else {
                $archives_files[$k]['archive_time'] = '<input type="text" name="archives_time[]" value="' . $v['archive_time'] . '" placeholder="点击选择日期" class="layui-input archives-time" style="cursor: pointer;border: none;height: 33px;">';
            }
            if ($v['expire_time'] == '0000-00-00' || !$v['expire_time']) {
                $archives_files[$k]['expire_time'] = '<input type="text" name="expire_time[]" value="" readonly placeholder="点击选择日期" class="layui-input expire-time" style="cursor: pointer;border: none;height: 33px;">';
            } else {
                $archives_files[$k]['expire_time'] = '<input type="text" name="expire_time[]" value="' . $v['expire_time'] . '" placeholder="点击选择日期" class="layui-input expire-time" style="cursor: pointer;border: none;height: 33px;">';
            }
            if ($addAssets) {
                $html .= $asModel->returnListLink('删除', $addAssets['actionurl'], '',
                    C('BTN_CURRENCY') . ' layui-btn-danger delFile', '',
                    'data-id="' . $v['arc_id'] . '" data-type="archives"');
            }
            $archives_files[$k]['html'] = $html;
        }
        $this->assign('archives_files', $archives_files);
        $assetsLevel = $asModel->getAssetsLevel();
        $this->assign('assetsLevel', $assetsLevel);
        $this->assign('factory', $factory);
        $this->assign('supplier', $supplier);
        $this->assign('repair', $repair);
        $this->assign('editAssetsUrl', ACTION_NAME);
        $this->display();
    }

    /**
     * 批量添加设备
     */
    public function batchAddAssets()
    {
        //print_r($_POST);die;
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'save':
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->batchAddAssets();
                    //日志行为记录文字
                    $text = getLogText('batchAddAssetsLogText');
                    $asModel->addLog('assets_info', '', $text, '', '');
                    $this->ajaxReturn($result);
                    break;
                case 'getData':
                    $asModel = new AssetsInfoModel();
                    //获取待入库设备
                    $result = $asModel->getWatingUploadAssets();
                    $this->ajaxReturn($result);
                    break;
                case 'updateData':
                    //更新临时表数据库
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->updateTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'delTmpAssets':
                    //删除临时表数据库
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->delTempData();
                    $this->ajaxReturn($result);
                    break;
                case 'upload':
                    //接收上传文件数据
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->uploadData();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $this->ajaxReturn(['status' => -1, 'msg' => '空操作！']);
                    break;
            }
        } else {
            $asModel = new AssetsInfoModel();
            //查询医院配置
            $hoinfo = $asModel->DB_get_one('hospital', 'hospital_id',
                ['hospital_id' => session('current_hospitalid'), 'is_delete' => 0]);
            if (!$hoinfo) {
                $this->assign('jumpUrl', '');
                $this->assign('errmsg', '请先配置医院信息！');
                $this->display('Public/error');
                exit;
            }
            $type = I('GET.type');
            if ($type == 'exploreAssetsModel') {
                //导出模板
                $xlsName = "assets";
                $xlsCell = [
                    '医院代码',
                    '设备名称',
                    '设备原编号',
                    '设备原编码(备用)',
                    '资产序列号',
                    '注册证编号',
                    '规格/型号',
                    '设备分类',
                    '医疗器械类别',
                    '设备原值',
                    '付款日期',
                    '已付清&未付清',
                    '预计使用年限',
                    '残净值率(%)',
                    '保修到期日期',
                    '辅助分类',
                    '品牌',
                    '巡查周期(天)',
                    '保养周期(天)',
                    '质控周期(天)',
                    '计量周期(天)',
                    '单位',
                    '出厂编号',
                    '出厂日期',
                    '发票编号',
                    '是否急救设备',
                    '是否特种设备',
                    '是否计量设备',
                    '是否质控设备',
                    '是否保养设备',
                    '是否效益分析设备',
                    '是否生命支持类设备',
                    '国产&进口',
                    '所属科室',
                    '资产负责人',
                    '财务分类',
                    '设备来源',
                    '资金来源',
                    '入库日期',
                    '启用日期',
                    '折旧方式',
                    '折旧年限',
                    '生产厂商',
                    '生产厂商联系人',
                    '生产厂商联系电话',
                    '供应商',
                    '供应商联系人',
                    '供应商联系电话',
                    '维修公司',
                    '维修公司联系人',
                    '维修公司联系电话',
                    '设备备注',
                    '标签ID',
                ];
                //单元格宽度设置
                $width = [
                    '医院代码'           => '20',//字符数长度
                    '设备名称'           => '20',//字符数长度
                    '设备原编号'         => '20',
                    '设备原编码(备用)'   => '20',
                    '资产序列号'         => '20',
                    '注册证编号'         => '20',
                    '设备分类'           => '20',
                    '医疗器械类别'       => '25',
                    '设备原值'           => '15',
                    '付款日期'           => '20',
                    '已付清&未付清'      => '26',
                    '预计使用年限'       => '15',
                    '残净值率(%)'        => '15',
                    '保修到期日期'       => '20',
                    '辅助分类'           => '20',
                    '巡查周期(天)'       => '20',
                    '保养周期(天)'       => '20',
                    '质控周期(天)'       => '20',
                    '计量周期(天)'       => '20',
                    '品牌'               => '20',
                    '出厂编号'           => '30',
                    '规格/型号'          => '15',
                    '单位'               => '10',
                    '出厂日期'           => '15',
                    '发票编号'           => '30',
                    '是否急救设备'       => '15',
                    '是否特种设备'       => '15',
                    '是否计量设备'       => '15',
                    '是否质控设备'       => '15',
                    '是否保养设备'       => '15',
                    '是否效益分析设备'   => '20',
                    '是否生命支持类设备' => '20',
                    '国产&进口'          => '20',
                    '所属科室'           => '20',
                    '资产负责人'         => '15',
                    '财务分类'           => '15',
                    '入库日期'           => '15',
                    '启用日期'           => '15',
                    '资金来源'           => '15',
                    '设备来源'           => '15',
                    '折旧方式'           => '20',
                    '折旧年限'           => '10',
                    '生产厂商'           => '30',
                    '生产厂商联系人'     => '15',
                    '生产厂商联系电话'   => '20',
                    '供应商'             => '30',
                    '供应商联系人'       => '15',
                    '供应商联系电话'     => '15',
                    '维修公司'           => '30',
                    '维修公司联系人'     => '15',
                    '维修公司联系电话'   => '20',
                    '设备备注'           => '100',
                    '标签ID'             => '50',
                ];
                //单元格颜色设置（例如必填行单元格字体颜色为红色）
                $color = [
                    '医院代码' => 'FF0000',//颜色代码
                    '设备名称' => 'FF0000',//颜色代码
                    '设备分类' => 'FF0000',
                    //'设备原编号' => 'FF0000',
//                    '资产序列号' => 'FF0000',
//                    '规格/型号' => 'FF0000',
                    //'资产负责人' => 'FF0000',
                    '所属科室' => 'FF0000',
                    '设备原值' => 'FF0000',
                    //'预计使用年限' => 'FF0000',
                    //'残净值率(%)' => 'FF0000',
//                    '保修到期日期' => 'FF0000',
                    //'生产厂商' => 'FF0000',
                    //'供应商' => 'FF0000',
                    //'财务分类' => 'FF0000',
                    //'资金来源' => 'FF0000',
                    //'设备来源' => 'FF0000',
                    //'折旧方式' => 'FF0000',
                    //'折旧年限' => 'FF0000',
//                    '启用日期' => 'FF0000',
//                    '入库日期' => 'FF0000',
                ];

                //查询所有分类和部门作为附表说明
                $descSheet = $asModel->getAllCategotyAndDepartment();
                exportTemplate('设备导入模板', $xlsName, $xlsCell, $width, $color, $descSheet);
            } else {
                $hospital_id = session('current_hospitalid');
                $asModel     = new AssetsInfoModel();
                //查设备分类
                $category = $asModel->DB_get_all('category', 'catid,parentid,catenum,category',
                    ['hospital_id' => $hospital_id, 'is_delete' => 0], '', 'catid asc', '');
                $category = getTree('parentid', 'catid', $category, 0, 0, '&nbsp;&nbsp;&nbsp;&nbsp;');
                //查询所有科室
                $department = $asModel->DB_get_all('department', 'departid,departnum,department',
                    ['hospital_id' => $hospital_id, 'is_delete' => 0], '', 'departid asc', '');
                //把资产模块属性设置返回
                $basesetting = $asModel->DB_get_all('base_setting', '*', ['module' => 'assets'], '', '');
                //折旧方式
                $depreciation_method = ['平均折旧法', '工作量法', '加速折旧法'];
                $helpcat             = $finance = $capitalfrom = $assfrom = [];
                foreach ($basesetting as $k => $v) {
                    if ($v['set_item'] == 'assets_helpcat') {
                        //辅助分类
                        $helpcat = json_decode($v['value'], true);
                    }
                    if ($v['set_item'] == 'assets_finance') {
                        //财务分类
                        $finance = json_decode($v['value'], true);
                    }
                    if ($v['set_item'] == 'assets_capitalfrom') {
                        //资金来源
                        $capitalfrom = json_decode($v['value'], true);
                    }
                    if ($v['set_item'] == 'assets_assfrom') {
                        //设备来源
                        $assfrom = json_decode($v['value'], true);
                    }
                }
                $assetsLevel = $asModel->getAssetsLevel();
                $this->assign('assetsLevel', $assetsLevel);
                $this->assign('category', $category);
                $this->assign('department', $department);
                $this->assign('depreciation_method', $depreciation_method);
                $this->assign('helpcat', $helpcat);
                $this->assign('finance', $finance);
                $this->assign('capitalfrom', $capitalfrom);
                $this->assign('assfrom', $assfrom);
                $this->assign('batchAddAssets', get_url());
                $this->display();
            }
        }
    }

    /**
     * 批量修改主设备
     */
    public function batchEditAssets()
    {
        $asModel = new AssetsInfoModel();
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'getData':
                    $result = $asModel->getAssetsInfoList(1);
                    //获取对应设备的附件和增值信息
                    $result['rows'] = $asModel->getIncrementAndAccessory($result['rows']);
                    //日志行为记录文字
                    $text = getLogText('batchEditAssetsLogText');
                    $asModel->addLog('assets_info', '', $text);
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'updateData':
                    //更新表数据库
                    $result = $asModel->updateAssetsData();
                    $this->ajaxReturn($result);
                    break;
                case 'batchEditGetData':
                    //获取选中数据
                    $result = $asModel->getSelData();
                    $this->ajaxReturn($result);
                    break;
                case 'batchEditUpdateData':
                    //更新数据
                    $result = $asModel->batchUpdateData();
                    $this->ajaxReturn($result);
                    break;
                default:
                    $this->ajaxReturn(['status' => -1, 'msg' => '空操作！']);
                    break;
            }
        } else {
            $type        = I('GET.type');
            $hospital_id = session('current_hospitalid');
            //查设备分类
            $category = $asModel->DB_get_all('category', 'catid,parentid,catenum,category',
                ['is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id], '', 'catid asc', '');
            $category = getTree('parentid', 'catid', $category, 0, 0, '----');
            //查询所有科室
            $department = $asModel->DB_get_all('department', 'departid,departnum,department',
                ['is_delete' => C('NO_STATUS'), 'hospital_id' => $hospital_id], '', 'departid asc', '');
            //把资产模块属性设置返回
            $basesetting = $asModel->DB_get_all('base_setting', '*', ['module' => 'assets'], '', '');
            //折旧方式
            $depreciation_method = ['平均折旧法', '工作量法', '加速折旧法'];
            $helpcat             = $finance = $capitalfrom = $assfrom = [];
            foreach ($basesetting as $k => $v) {
                if ($v['set_item'] == 'assets_helpcat') {
                    //辅助分类
                    $helpcat = json_decode($v['value'], true);
                }
                if ($v['set_item'] == 'assets_finance') {
                    //财务分类
                    $finance = json_decode($v['value'], true);
                }
                if ($v['set_item'] == 'assets_capitalfrom') {
                    //资金来源
                    $capitalfrom = json_decode($v['value'], true);
                }
                if ($v['set_item'] == 'assets_assfrom') {
                    //设备来源
                    $assfrom = json_decode($v['value'], true);
                }
            }
            $assetsLevel = $asModel->getAssetsLevel();
            $this->assign('assetsLevel', $assetsLevel);
            $this->assign('category', $category);
            $this->assign('department', $department);
            $this->assign('depreciation_method', $depreciation_method);
            $this->assign('helpcat', $helpcat);
            $this->assign('finance', $finance);
            $this->assign('capitalfrom', $capitalfrom);
            $this->assign('assfrom', $assfrom);
            if ($type == 'batchEdit') {
                $assid = trim(I('GET.assid'), ',');
                if (!$assid) {
                    $this->display('/Public/error');
                    exit;
                }
                $fields = $asModel->getDefaultShowFields();
                unset($fields['assorignum']);
                unset($fields['status']);
                $this->assign('assid', $assid);
                $this->assign('fields', $fields);
                $header = $asModel->getEditHeader();
                $header = json_encode($header);
                $this->assign('header', $header);
                $assetsLevel = $asModel->getAssetsLevel();
                $this->assign('assetsLevel', $assetsLevel);
                $this->display('Assets/Lookup/batchEdit');
            } else {
                //所属科室
                $notCheck = new PublicController();
                $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                $this->assign('batchEditAssets', get_url());
                $this->display();
            }
        }
    }

    /**
     * 批量删除主设备
     */
    public function batchDeleteAssets()
    {
        if (IS_POST) {
            $assid = I('post.assid');
            if (!$assid) {
                $this->ajaxReturn(
                    [
                        'status' => -1,
                        'msg'    => '请选择需要删除的设备',
                    ]
                );
            }
            $asModel = new AssetsInfoModel();
            $assInfo = $asModel->DB_get_one('assets_info',
                'group_concat(acid) AS acid,group_concat(afid) AS afid,group_concat(assnum) AS assnum',
                ['assid' => ['IN', $assid]]);
            //删除主设备
            $asModel->deleteData('assets_info', ['assid' => ['IN', $assid]]);
            //日志行为记录文字
            $log['assnum'] = $assInfo['assnum'];
            $text          = getLogText('batchDeleteAssetsLogText', $log);
            $asModel->addLog('assets_info', M()->getLastSql(), $text, '', '');
            //删除合同信息
            $asModel->deleteData('assets_contract', ['acid' => ['IN', $assInfo['acid']]]);
            //更新厂家信息
            $facInfo = $asModel->DB_get_all('assets_factory', 'afid,assid', ['afid' => ['IN', $assInfo['afid']]], '',
                '', '');
            foreach ($facInfo as $k => $v) {
                $aid = json_decode($v['assid'], true);
                foreach ($aid as $k1 => $v1) {
                    if (in_array($v1, explode(',', $assid))) {
                        unset($aid[$k1]);
                    }
                }
                $asModel->updateData('assets_factory', ['assid' => json_encode($aid)], ['afid' => $v['afid']]);
            }
            //删除维修信息
            $asModel->deleteData('repair', ['assid' => ['IN', $assid]]);
            //更新部门表和分类表中设备数量、总价等信息
            $this->updateAssetsNumAndTotalPrice();
            $this->ajaxReturn(['status' => 1, 'msg' => '删除设备成功'], 'json');
        }
    }


    /**
     * 设备信息预览
     */
    public function showAssets()
    {
        //实例化模型
        $asModel = new AssetsInfoModel();
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'getQuality':
                    //获取设备质控记录
                    $result = $asModel->getAssetsQaulityRecord();
                    $this->ajaxReturn($result);
                    break;
                case 'getTransfer':
                    //获取转科记录
                    $result = $asModel->getAssetsTransferRecord();
                    $this->ajaxReturn($result);
                    break;
                case 'deleteAssetsPic':
                    //删除设备照片
                    $result = $asModel->deleteAssetsPic();
                    $this->ajaxReturn($result);
                    break;
                case 'getSameAssetsList':
                    //获取技术资料相同设备
                    $result = $asModel->getSameAssetsListData();
                    $this->ajaxReturn($result);
                    break;
                case 'getStatusChange':
                    //获取状态变更记录
                    $result = $asModel->getStateRecord();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getRepairRecord':
                    //获取维修记录
                    $result = $asModel->getAssetsRepairRecord();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getAdverseRecord':
                    //获取不良事件记录
                    $adverseModel = new AdverseModel();
                    $result       = $adverseModel->getAdverseData();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getMeteringRecord':
                    //获取设备计量记录
                    $result = $asModel->getMeteringRecord();
                    $this->ajaxReturn($result);
                    break;
                case 'doRenewal':
                    //获取参保记录
                    $AssetsInsuranceModel = new AssetsInsuranceModel();
                    $result               = $AssetsInsuranceModel->getAssetsInsuranceList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'borrowRecordList':
                    //获取借调记录
                    $AssetsBorrowModel = new AssetsBorrowModel();
                    $result            = $AssetsBorrowModel->borrowRecordList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getRepairOffList':
                    //获取第三方公司列表
                    $InsuranceModel = new AssetsInsuranceModel();
                    $result         = $InsuranceModel->getRepairOffList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'geteditData':
                    $EditModel = new EditModel();
                    $result    = $EditModel->geteditRecordList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getCity':
                    //获取城市
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result                = $OfflineSuppliersModel->getCity();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getAreas':
                    //获取区/城镇
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result                = $OfflineSuppliersModel->getAreas();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addSuppliers':
                    //补充厂家
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result                = $OfflineSuppliersModel->addOfflineSupplier();
                    if ($result['status'] == C('SUCCESS_STATUS')) {
                        $result['result']['sup_num'] = $OfflineSuppliersModel->getSupNum();
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'uploadUnplanFile':
                    //上传技术资料、设备档案文件
                    $asModel = new AssetsInfoModel();
                    $result  = $asModel->uploadAssetsUnplanFile();
                    $this->ajaxReturn($result);
                    break;
                case 'updateUnplanFileDate':
                    //上传技术资料、设备档案文件
                    $asModel      = new AssetsInfoModel();
                    $arc_id       = I('post.arc_id');
                    $archive_time = I('post.archive_time');
                    $asModel->updateData('assets_archives_file', ['archive_time' => $archive_time],
                        ['arc_id' => $arc_id]);
                    $this->ajaxReturn(['status' => 1]);
                    break;
                case 'uploadUpdate':
                    //更新archives表
                    $new_arc       = $asModel->DB_get_all('assets_archives_file', 'arc_id,archive_time,expire_time',
                        ['assid' => I('post.assid')], '', 'arc_id asc');
                    $archives_time = I('post.archives_time');
                    $expire_time   = I('post.expire_time');
                    $box_id        = I('post.box_id') ? I('post.box_id') : 0;
                    foreach ($new_arc as $k => $v) {
                        $asModel->updateData('assets_archives_file', [
                            'archive_time' => $archives_time[$k],
                            'expire_time'  => $expire_time[$k],
                            'box_id'       => $box_id,
                        ], ['arc_id' => $v['arc_id']]);
                    }
                    $this->ajaxReturn(['status' => 1], 'json');
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'getUnplanData':
                    $assid          = I('get.assid');
                    $type           = I('get.type');
                    $total          = $asModel->DB_get_count('assets_archives_file',
                        ['assid' => $assid, 'unplan_class' => $type]);
                    $archives_files = $asModel->DB_get_all('assets_archives_file', '*',
                        ['assid' => $assid, 'unplan_class' => $type], '', 'arc_id asc');
                    foreach ($archives_files as $k => $v) {
                        $html       = '<div class="layui-btn-group">';
                        $supplement = 'data-path="' . $v['file_url'] . '" data-name="' . $v['file_name'] . '"';
                        if ($v['file_type'] == 'jpg' || $v['file_type'] == 'jpeg' || $v['file_type'] == 'png' || $v['file_type'] == 'pdf') {
                            $html .= $asModel->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' showFile', '',
                                $supplement);
                        }
                        if (empty($v['archive_time'])) {
                            $archives_files[$k]['archive_time'] = getHandleTime(time());
                        }
                        $html .= $asModel->returnListLink('下载', '', '',
                            C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
//                        if ($addAssets) {
                        $html .= $asModel->returnListLink('删除', '/A/Lookup/addAssets', '',
                            C('BTN_CURRENCY') . ' layui-btn-danger delFile', '',
                            'data-id="' . $v['arc_id'] . '" data-type="archives" data-unplan="1"');
//                        }
                        $html                            .= '</div>';
                        $archives_files[$k]['operation'] = $html;
                    }
                    $result['total'] = $total;
                    $result['rows']  = $archives_files;
                    $result['code']  = 200;
                    $this->ajaxReturn($result);
                    break;
                default:
                    $assid = I('GET.assid');
                    //如果是附属设备点击进来的话就切换tab选项卡
                    $changeTab = I('GET.change');
                    //组织表单第一部分数据设备基础信息
                    $assets = $asModel->getAssetsInfo($assid);
                    //判断有无查看原值的权限
                    $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');
                    //判断有无查看设备档案的权限
                    $showAssetsfile = get_menu('Assets', 'Lookup', 'showAssetsfile');
                    //判断有无查看技术资料的权限
                    $showTechnicalInformation = get_menu('Assets', 'Lookup', 'showTechnicalInformation');
                    //判断有无查看设备参保的权限
                    $showAssetsInsurance = get_menu('Assets', 'Lookup', 'showAssetsInsurance');
                    if (!$showPrice) {
                        $assets['buy_price']               = '***';
                        $assets['depreciable_quota_m']     = '***';
                        $assets['depreciable_quota_count'] = '***';
                        $assets['net_asset_value']         = '***';
                        $assets['net_assets']              = '***';
                    }
                    //组织表单第三部分厂商信息
                    $OfflineSuppliersModel  = new OfflineSuppliersModel();
                    $offlineSuppliers       = $asModel->DB_get_one('assets_factory', 'ols_facid,ols_supid,ols_repid',
                        ['assid' => $assets['assid']]);
                    $factoryInfo            = [];
                    $supplierInfo           = [];
                    $repairInfo             = [];
                    $offlineSuppliersFields = 'olsid,sup_name,salesman_name,salesman_phone,artisan_name,artisan_phone';
                    if ($offlineSuppliers['ols_facid']) {
                        $factoryInfo = $asModel->DB_get_one('offline_suppliers', $offlineSuppliersFields,
                            ['olsid' => $offlineSuppliers['ols_facid']]);
                        $factoryFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_facid']);
                        $this->assign('factoryData', $factoryFile);
                    }
                    if ($offlineSuppliers['ols_supid']) {
                        $supplierInfo = $asModel->DB_get_one('offline_suppliers', $offlineSuppliersFields,
                            ['olsid' => $offlineSuppliers['ols_supid']]);
                        $supplierFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_supid']);
                        $this->assign('supplierData', $supplierFile);
                    }
                    if ($offlineSuppliers['ols_repid']) {
                        $repairInfo = $asModel->DB_get_one('offline_suppliers', $offlineSuppliersFields,
                            ['olsid' => $offlineSuppliers['ols_repid']]);
                        $repairFile = $OfflineSuppliersModel->getSuppliersFileList($offlineSuppliers['ols_repid']);
                        $this->assign('repairData', $repairFile);
                    }
                    $this->assign('factoryInfo', $factoryInfo);
                    $this->assign('supplierInfo', $supplierInfo);
                    $this->assign('repairInfo', $repairInfo);
                    //查询当前用户是否有权限进行添加主设备
                    $addAssets = get_menu('Assets', 'Lookup', 'addAssets');
                    //获取资质文件信息
                    $quali_files = $asModel->DB_get_all('assets_factory_qualification_file', '*', ['assid' => $assid]);
                    foreach ($quali_files as $k => $v) {
                        $html       = '';
                        $supplement = 'data-path="' . $v['file_url'] . '" data-name="' . $v['file_name'] . '"';
                        $html       .= $asModel->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' showFile', '',
                            $supplement);
                        $html       .= $asModel->returnListLink('下载', '', '',
                            C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                        if ($addAssets) {
                            $html .= $asModel->returnListLink('删除', $addAssets['actionurl'], '',
                                C('BTN_CURRENCY') . ' layui-btn-danger delFile', '',
                                'data-id="' . $v['quali_id'] . '" data-type="quali"');
                        }
                        $quali_files[$k]['html'] = $html;
                    }
                    if ($showTechnicalInformation) {
                        //获取技术资料文件信息
                        $techni_files = $asModel->DB_get_all('assets_technical_file', '*', ['assid' => $assid]);
                        foreach ($techni_files as $k => $v) {
                            $html       = '';
                            $supplement = 'data-path="' . $v['file_url'] . '" data-name="' . $v['file_name'] . '"';
                            if ($v['file_type'] == 'jpg' || $v['file_type'] == 'jpeg' || $v['file_type'] == 'png' || $v['file_type'] == 'pdf') {
                                $html .= $asModel->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' showFile', '',
                                    $supplement);
                            }
                            $html .= $asModel->returnListLink('下载', '', '',
                                C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                            if ($addAssets) {
                                $html .= $asModel->returnListLink('删除', $addAssets['actionurl'], '',
                                    C('BTN_CURRENCY') . ' layui-btn-danger delFile', '',
                                    'data-id="' . $v['tech_id'] . '" data-type="technical"');
                            }
                            $techni_files[$k]['html'] = $html;
                        }
                        $this->assign('showTechnicalInformation', 1);
                    } else {
                        $this->assign('showTechnicalInformation', 0);
                    }
                    //获取档案资料文件信息
                    if ($showAssetsfile) {
                        if ($assets['box_num']) {
                            $boxInfo = $asModel->DB_get_one('archives_box', 'box_id',
                                ['box_num' => $assets['box_num']]);
                            $this->assign('box_id', $boxInfo['box_id']);
                        }
                        $today          = date('Y-m-d');
                        $archives_files = $asModel->DB_get_all('assets_archives_file', '*', ['assid' => $assid], '',
                            'arc_id asc');
                        foreach ($archives_files as $k => $v) {
                            $html       = '<div class="layui-btn-group">';
                            $supplement = 'data-path="' . $v['file_url'] . '" data-name="' . $v['file_name'] . '"';
                            if ($v['file_type'] == 'jpg' || $v['file_type'] == 'jpeg' || $v['file_type'] == 'png' || $v['file_type'] == 'pdf') {
                                $html .= $asModel->returnListLink('预览', '', '', C('BTN_CURRENCY') . ' showFile', '',
                                    $supplement);
                            }
                            if ($v['archive_time'] == '0000-00-00' || !$v['archive_time'] == '0000-00-00') {
                                $archives_files[$k]['archive_time'] = '<input type="text" name="archives_time[]" value="' . $today . '" readonly placeholder="点击选择日期" class="layui-input archives-time" style="cursor: pointer;border: none;height: 33px;">';
                            } else {
                                $archives_files[$k]['archive_time'] = '<input type="text" name="archives_time[]" value="' . $v['archive_time'] . '" readonly placeholder="点击选择日期" class="layui-input archives-time" style="cursor: pointer;border: none;height: 33px;">';
                            }
                            if ($v['expire_time'] == '0000-00-00' || !$v['archive_time'] == '0000-00-00') {
                                $archives_files[$k]['expire_time'] = '<input type="text" name="expire_time[]" value="" placeholder="点击选择日期" readonly class="layui-input expire-time" style="cursor: pointer;border: none;height: 33px;">';
                            } else {
                                $archives_files[$k]['expire_time'] = '<input type="text" name="expire_time[]" value="' . $v['expire_time'] . '" readonly placeholder="点击选择日期" class="layui-input expire-time" style="cursor: pointer;border: none;height: 33px;">';
                            }
                            $html .= $asModel->returnListLink('下载', '', '',
                                C('BTN_CURRENCY') . ' layui-btn-normal downFile', '', $supplement);
                            if ($addAssets) {
                                $html .= $asModel->returnListLink('删除', $addAssets['actionurl'], '',
                                    C('BTN_CURRENCY') . ' layui-btn-danger delFile', '',
                                    'data-id="' . $v['arc_id'] . '" data-type="archives"');
                            }
                            $html                       .= '</div>';
                            $archives_files[$k]['html'] = $html;
                        }
                        $this->assign('showAssetsfile', 1);
                    } else {
                        $this->assign('showAssetsfile', 0);
                    }
                    if ($showAssetsInsurance) {
                        //获取保修厂家数据
                        $join       = 'LEFT JOIN sb_offline_suppliers AS O ON O.olsid=F.ols_facid';
                        $fields     = 'F.ols_facid,O.sup_name AS factory,O.salesman_name AS factory_user,O.salesman_phone AS factory_tel';
                        $usecompany = $asModel->DB_get_one_join('assets_factory', 'F', $fields, $join,
                            ['F.afid' => ['EQ', $assets['afid']]]);
                        if ($usecompany['factory'] != null) {
                            $this->assign('ols_facid', $usecompany['ols_facid']);
                            $this->assign('usecompany', json_encode($usecompany));
                        } else {
                            $this->assign('usecompany', null);
                        }
                        $this->assign('showAssetsInsurance', 1);
                    } else {
                        $this->assign('showAssetsInsurance', 0);
                    }
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $provinces             = $OfflineSuppliersModel->getProvinces();
                    $sup_num               = $OfflineSuppliersModel->getSupNum();
                    $this->assign('sup_num', $sup_num);
                    $this->assign('provinces', $provinces);
                    if ($assets['status'] != C('ASSETS_STATUS_SCRAP')) {
                        $doRenewalMenu = get_menu($this->MODULE, $this->Controller, 'doRenewal');
                        $this->assign('doRenewalMenu', $doRenewalMenu);
                    }
                    if ($assets['is_subsidiary'] == C('YES_STATUS')) {
                        //获取所属主设备信息
                        $mainAssets = $asModel->getAssetsInfo($assets['main_assid']);
                        if (!$showPrice) {
                            $mainAssets['buy_price']               = '***';
                            $mainAssets['depreciable_quota_m']     = '***';
                            $mainAssets['depreciable_quota_count'] = '***';
                            $mainAssets['net_asset_value']         = '***';
                            $mainAssets['net_assets']              = '***';
                        }
                        $this->assign('mainAssets', $mainAssets);
                        //组织表单第三部分厂商信息
                        $mainAssets_factory = $asModel->DB_get_one('assets_factory', '',
                            ['assid' => $mainAssets['assid']]);
                        $this->assign('mainAssets_factory', $mainAssets_factory);
                    } else {
                        //获取附属设备 信息
                        $subsidiary = $asModel->getSubsidiaryList($assid);
                        $this->assign('subsidiary', $subsidiary);
                    }
                    $assetsPic = explode(',', $assets['pic_url']);
                    foreach ($assetsPic as $k => $v) {
                        $assetsPic[$k] = '/Public/uploads/assets/' . $v;
                    }
                    //生成二维码
                    $scrapModel = new AssetsTransferModel();
                    $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                    $url        = "$protocol" . C('HTTP_HOST') . C('ADMIN_NAME') . '/Public/uploadReport?id=' . $assid . '&i=assid&t=assets_info&username=' . session('username');
                    $codeUrl    = $scrapModel->createCodePic($url);
                    $codeUrl    = trim($codeUrl, '.');
                    $this->assign('codeUrl', $codeUrl);
                    $this->assign('assetsPic', $assetsPic);
                    $this->assign('uploadAction', 'uploadInsurance');
                    $this->assign('quali_files', $quali_files);
                    $this->assign('techni_files', $techni_files);
                    $this->assign('archives_files', $archives_files);
                    $this->assign('assets', $assets);
                    $this->assign('assid', $assid);
                    $this->assign('changeTab', $changeTab);
                    $this->assign('showAssets', get_url());
                    $this->assign('today', $today);
                    $this->display();
                    break;
            }
        }
    }

    /**
     * 获取维修状态(主设备详情页面)
     */

    private function getRepairStatus($status)
    {
        switch ($status) {
            case 1:
                return '已报修';
                break;
            case 2:
                return '已接单';
                break;
            case 3:
                return '已检修';
                break;
            case 4:
                return '报价中';
                break;
            case 5:
                return '审核中';
                break;
            case 6:
                return '维修中]';
                break;
            case 7:
                return '维修完成';
                break;
            case 8:
                return '已验收';
                break;
        }
    }

    /**
     * 主设备删除
     */
    public function deleteAssets()
    {

        $asModel = new AssetsInfoModel();
        $assid   = I('get.assid');
        if (!$assid) {
            $this->ajaxReturn(['status' => -1, 'msg' => '参数错误']);
        } else {
            $type = I('post.type');
            if (!$assid) {
                $this->ajaxReturn(['status' => -1, 'msg' => '参数错误']);
            } else {
                //查出当前的assid对应的其他id
                $asinfo = $asModel->DB_get_one('assets_info', 'assnum,assets,assid,catid,buy_price,acid,afid',
                    ['assid' => $assid, 'is_delete' => '0']);
                if (!$asinfo) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备不存在！']);
                }
                $edit_data = $asModel->DB_get_one('edit', 'id', [
                    'operation_type' => 'delete',
                    'update_where'   => '{"assid":"' . $assid . '"}',
                    'is_approval'    => '0',
                ]);
                if ($edit_data && $type == "is_del") {
                    $this->ajaxReturn(['status' => -1, 'msg' => '已经申请删除请耐心等待']);
                }
                //查看该设备是否存在业务，如果存在提示用户删除业务后再重新删除主设备
                //判断是否有借调业务
                $borid = $asModel->DB_get_one('assets_borrow', 'borid', ['assid' => $assid, 'is_delete' => '0']);
                if ($borid) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备还存在借调业务，请先删除相关业务再申请删除']);
                }
                //判断是否有转科业务
                $atid = $asModel->DB_get_one('assets_transfer', 'atid', ['assid' => $assid, 'is_delete' => '0']);
                if ($atid) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备还存在转科业务，请先删除相关业务再申请删除']);
                }
                //判断是否有外调业务
                $outid = $asModel->DB_get_one('assets_outside', 'outid', ['assid' => $assid, 'is_delete' => '0']);
                if ($outid) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备还存在外调业务，请先删除相关业务再申请删除']);
                }
                //判断是否有报废业务
                $scrid = $asModel->DB_get_one('assets_scrap', 'scrid', ['assid' => $assid, 'is_delete' => '0']);
                if ($scrid) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备还存在报废业务，请先删除相关业务再申请删除']);
                }
                //判断是否有维修业务
                $repid = $asModel->DB_get_one('repair', 'repid', ['assid' => $assid]);
                if ($repid) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备还存在维修业务，请先删除相关业务再申请删除']);
                }
                //判断是否有巡查保养业务
                $packid = $asModel->DB_get_one('patrol_plans_assets', 'plan_asid', ['assid' => $asinfo['assid']]);
                if ($packid) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备还存在巡查保养业务，请先删除相关业务再申请删除']);
                }
                //判断是否有初始化模板
                $patid = $asModel->DB_get_one('patrol_assets_template', 'patid', ['assid' => $assid]);
                if ($patid) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备还存在初始化模板，请先删除相关业务再申请删除']);
                }

                //判断是否有质控业务
                $qsid = $asModel->DB_get_one('quality_starts', 'qsid', ['assid' => $assid]);
                if ($qsid) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备还存在质控业务，请先删除相关业务再申请删除']);
                }
                //判断是否有质控项目执行记录
                $resultid = $asModel->DB_get_one('quality_result', 'resultid', ['assid' => $assid]);
                if ($resultid) {
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg'    => '该设备还存在质控项目执行记录，请先删除相关业务再申请删除',
                    ]);
                }
                //
                //判断是否有计量计划
                $mpid = $asModel->DB_get_one('metering_plan', 'mpid', ['assid' => $assid]);
                if ($mpid) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备还存在计量计划业务，请先删除相关业务再申请删除']);
                }
                //判断是否有不良事件管理
                $ai_Id = $asModel->DB_get_one('adverse_info', 'Id', ['assid' => $assid]);
                if ($ai_Id) {
                    $this->ajaxReturn(['status' => -1, 'msg' => '该设备还存在不良事件记录，请先删除相关业务再申请删除']);
                }
                //将该申请提交
                if ($type == 'is_del') {
                    $this->ajaxReturn(['status' => 1, 'msg' => '允许删除']);
                }
                $result = $asModel->deleteAssets($assid);
                if ($result) {
                    $this->ajaxReturn(['status' => 1, 'msg' => '申请删除设备成功，请等待审批']);
                } else {
                    $this->ajaxReturn(['status' => -1, 'msg' => '申请删除设备失败，请重新申请']);
                }
            }
        }
    }

    /**
     * 维保资产列表
     */
    public function getInsuranceList()
    {

        //var_dump($_POST);exit;
        if (IS_POST) {
            $InsuranceModel = new AssetsInsuranceModel();
            $result         = $InsuranceModel->getInsuranceList();
            $this->ajaxReturn($result, 'json');
        } else {

            $departids   = explode(',', session('departid'));
            $departments = $this->getDepartname($departids);
            $this->assign('departments', $departments);
            $this->assign('getInsuranceList', get_url());
            //所属科室
            $notCheck = new PublicController();
            $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
            $this->display();
        }
    }

    //续保页面
    public function doRenewal()
    {
        //var_dump($_POST);exit;
        if (IS_POST) {
            $type = I('POST.type');
            switch ($type) {
                case 'getList':
                    //获取明细列表
                    $AssetsInsuranceModel = new AssetsInsuranceModel();
                    $result               = $AssetsInsuranceModel->getAssetsInsuranceList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addInsurance':
                    //新增维保明细
                    $InsuranceModel = new AssetsInsuranceModel();
                    $result         = $InsuranceModel->addInsurance();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'upload':
                    //上传维保文件
                    $InsuranceModel = new AssetsInsuranceModel();
                    $result         = $InsuranceModel->uploadInsurance();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'saveInsurance':
                    //编辑明细
                    $InsuranceModel = new AssetsInsuranceModel();
                    $result         = $InsuranceModel->saveInsurance();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getRepairOffList':
                    //获取第三方公司列表
                    $InsuranceModel = new AssetsInsuranceModel();
                    $result         = $InsuranceModel->getRepairOffList();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getCity':
                    //获取城市
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result                = $OfflineSuppliersModel->getCity();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'getAreas':
                    //获取区/城镇
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result                = $OfflineSuppliersModel->getAreas();
                    $this->ajaxReturn($result, 'json');
                    break;
                case 'addSuppliers':
                    //补充厂家
                    $OfflineSuppliersModel = new OfflineSuppliersModel();
                    $result                = $OfflineSuppliersModel->addOfflineSupplier();
                    if ($result['status'] == C('SUCCESS_STATUS')) {
                        $result['result']['sup_num'] = $OfflineSuppliersModel->getSupNum();
                    }
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $assid          = I('GET.assid');
            $InsuranceModel = new AssetsInsuranceModel();
            $asArr          = $InsuranceModel->getAssets($assid);
            $join           = 'LEFT JOIN sb_offline_suppliers AS O ON O.olsid=F.ols_facid';
            $fields         = 'F.ols_facid,O.sup_name AS factory,O.salesman_name AS factory_user,O.salesman_phone AS factory_tel';
            $usecompany     = $InsuranceModel->DB_get_one_join('assets_factory', 'F', $fields, $join,
                ['F.afid' => ['EQ', $asArr['afid']]]);
            if ($usecompany['factory'] != null) {
                $this->assign('ols_facid', $usecompany['ols_facid']);
                $this->assign('usecompany', json_encode($usecompany));
            } else {
                $this->assign('usecompany', null);
            }
            $OfflineSuppliersModel = new OfflineSuppliersModel();
            $provinces             = $OfflineSuppliersModel->getProvinces();
            $sup_num               = $OfflineSuppliersModel->getSupNum();
            $this->assign('sup_num', $sup_num);
            $this->assign('provinces', $provinces);
            $this->assign('asArr', $asArr);
            $this->assign('url', ACTION_NAME);
            $this->display();
        }
    }

    /**
     * 资产综合查询
     */
    public function getAssetsSearchList()
    {
        if (IS_POST) {
            $asModel             = new AssetsInfoModel();
            $limit               = I('post.limit') ? I('post.limit') : 10;
            $page                = I('post.page') ? I('post.page') : 1;
            $offset              = ($page - 1) * $limit;
            $order               = I('post.order');
            $sort                = I('post.sort');
            $assets              = I('post.getAssetsSearchListAssets');
            $model               = I('post.getAssetsSearchListModel');
            $unit                = I('post.getAssetsSearchListUnit');
            $barcore             = I('post.getAssetsSearchListBarcore');
            $financenum          = I('post.getAssetsSearchListFinancenum');
            $assetsNum           = I('post.getAssetsSearchListAssetsNum');
            $assetsOrnum         = I('post.getAssetsSearchListAssetsOrnum');
            $archnum             = I('post.getAssetsSearchListArchnum');
            $supplier            = I('post.getAssetsSearchListSupplier');
            $factory             = I('post.getAssetsSearchListFactory');
            $assetsCat           = I('post.getAssetsSearchListCategory');
            $assetsDep           = I('post.getAssetsSearchListDepartment');
            $managedepart        = I('post.getAssetsSearchListManagedepartment');
            $assetsDate          = I('post.getAssetsSearchListAssetsDate');
            $assetsUser          = I('post.getAssetsSearchListAssetsUser');
            $capitalfrom         = I('post.getAssetsSearchListCapitalfrom');
            $financeid           = I('post.getAssetsSearchListFinanceid');
            $assfromid           = I('post.getAssetsSearchListAssfromid');
            $assetsrespon        = I('post.getAssetsSearchListAssetsrespon');
            $buydate             = I('post.getAssetsSearchListBuydate');
            $status              = I('post.getAssetsSearchListStatus');
            $invoicenum          = I('post.getAssetsSearchListInvoicenum');
            $plaofpro            = I('post.getAssetsSearchListPlaofpro');
            $brand               = I('post.getAssetsSearchListBrand');
            $country             = I('post.getAssetsSearchListCountry');
            $contract            = I('post.getAssetsSearchListContract');
            $repair              = I('post.getAssetsSearchListRepair');
            $isfirstaid          = I('post.checkFirstaid');
            $isspecial           = I('post.checkSpecial');
            $isassets            = I('post.checkAssets');
            $ismetering          = I('post.checkMetering');
            $condate             = I('post.getAssetsSearchListCondate');
            $residual_value      = I('post.getAssetsSearchListResidual_value');
            $factorynum          = I('post.getAssetsSearchListFactorynum');
            $depreciation_method = I('post.getAssetsSearchListDepreciation_method');
            $expected_life       = I('post.getAssetsSearchListExpected_life');
            $factorydate         = I('post.getAssetsSearchListFactorydate');
            $depreciable_lives   = I('post.getAssetsSearchListDepreciable_lives');
            $opendate            = I('post.getAssetsSearchListOpendate');
            $where               = " 1 ";
            if (!isset($offset)) {
                $offset = 0;
            }
            if (!isset($limit)) {
                $limit = 10;
            }
            if (!$sort) {
                $sort = 'assid ';
            }
            if (!$order) {
                $order = 'asc';
            }
            if ($assets) {
                //设备名称搜索
                $where .= " and sb_assets_info.assets like '%" . $assets . "%'";
            }
            if ($depreciation_method) {
                //折旧状态搜索
                $where .= " and sb_assets_info.depreciation_method = " . $depreciation_method;
            }
            if ($expected_life) {
                //预计使用年限搜索
                $where .= " and sb_assets_info.expected_life = " . $expected_life;
            }
            if ($depreciable_lives) {
                //折旧年限搜索
                $where .= " and sb_assets_info.depreciable_lives = " . $depreciable_lives;
            }
            if ($factorynum) {
                //出厂编号搜索
                $where .= " and sb_assets_info.factorynum like '%" . $factorynum . "%'";
            }
            if ($isfirstaid) {
                //急救资产搜索
                $where .= " and sb_assets_info.is_firstaid = " . $isfirstaid;
            }
            if ($isspecial) {
                //特种资产搜索
                $where .= " and sb_assets_info.is_special = " . $isspecial;
            }
            if ($isassets) {
                //是否资产搜索
                $where .= " and sb_assets_info.is_assets = " . $isassets;
            }
            if ($ismetering) {
                //计量资产搜索
                $where .= " and sb_assets_info.is_metering = " . $ismetering;
            }
            if ($invoicenum) {
                //发票编号搜索
                $where .= " and sb_assets_info.invoicenum like '%" . $invoicenum . "%'";
            }
            if ($brand) {
                //品牌搜索
                $where .= " and sb_assets_info.brand like '%" . $brand . "%'";
            }
            if ($residual_value) {
                //品牌搜索
                $where .= " and sb_assets_info.residual_value like '%" . $residual_value . "%'";
            }
            if ($capitalfrom) {
                //资金来源搜索
                $where .= " and sb_assets_info.capitalfrom = " . $capitalfrom;
            }
            if ($financeid) {
                //财务分类搜索
                $where .= " and sb_assets_info.financeid = " . $financeid;
            }
            if ($assetsrespon) {
                //资产负责人搜索
                $where .= " and sb_assets_info.assetsrespon = '" . $assetsrespon . "'";
            }
            if ($assfromid) {
                //资产来源搜索
                $where .= " and sb_assets_info.assfromid = " . $assfromid;
            }
            if ($status) {
                //资产状态搜索
                $where .= " and sb_assets_info.status = " . $status;
            }
            if ($managedepart) {
                //管理科室搜索
                $where .= " and sb_assets_info.managedepart = '" . $managedepart . "'";
            }
            if ($assetsNum) {
                //资产编码搜索
                $where .= " and sb_assets_info.assnum like '%" . $assetsNum . "%'";
            }
            if ($model) {
                //规格型号搜索
                $where .= " and sb_assets_info.model like '%" . $model . "%'";
            }
            if ($unit) {
                //单位搜索
                $where .= " and sb_assets_info.unit like '%" . $unit . "%'";
            }
            if ($barcore) {
                //条形码搜索
                $where .= " and sb_assets_info.barcore like '%" . $barcore . "%'";
            }
            if ($financenum) {
                //财务编号搜索
                $where .= " and sb_assets_info.financenum like '%" . $financenum . "%'";
            }
            if ($assetsOrnum) {
                //资产原编码搜索
                $where .= " and sb_assets_info.assorignum like '%" . $assetsOrnum . "%'";
            }
            if ($assetsCat) {
                //分类搜索
                $caModel              = new CategoryModel();
                $catwhere['category'] = ['like', "%$assetsCat%"];
                $catids               = $caModel->getCatidsBySearch($catwhere);
                $where                .= " and sb_assets_info.catid in (" . $catids . ")";
            }
            if ($contract) {
                //合同名称搜索
                $contractwhere = ['contract' => ['LIKE', '%' . $contract . '%']];
                $res           = $asModel->DB_get_one('assets_contract', 'group_concat(acid) AS acid', $contractwhere);
                if ($res['acid']) {
                    $where .= " and sb_assets_info.acid in (" . $res['acid'] . ")";
                } else {
                    $where .= " and sb_assets_info.acid in (-1)";
                }
            }
            if ($condate) {
                //签订日期搜索
                $conPretime   = strtotime($condate) - 1;
                $conNexttime  = strtotime($condate) + 24 * 3600;
                $condatewhere = "con_date >" . $conPretime . ' and con_date <' . $conNexttime;
                $res          = $asModel->DB_get_one('assets_contract', 'group_concat(acid) AS acid', $condatewhere);
                if ($res['acid']) {
                    $where .= " and sb_assets_info.acid in (" . $res['acid'] . ")";
                } else {
                    $where .= " and sb_assets_info.acid in (-1)";
                }
            }
            if ($buydate) {
                //购入日期搜索
                $buyPretime  = strtotime($buydate) - 1;
                $buyNexttime = strtotime($buydate) + 24 * 3600;
                $buywhere    = "buy_date >" . $buyPretime . ' and buy_date <' . $buyNexttime;
                $res         = $asModel->DB_get_one('assets_contract', 'group_concat(acid) AS acid', $buywhere);
                if ($res['acid']) {
                    $where .= " and sb_assets_info.acid in (" . $res['acid'] . ")";
                } else {
                    $where .= " and sb_assets_info.acid in (-1)";
                }
            }
            if ($supplier) {
                //供应商搜索
                $supplierwhere = ['supplier' => ['LIKE', '%' . $supplier . '%']];
                $res           = $asModel->DB_get_one('assets_factory', 'group_concat(afid) AS afid', $supplierwhere);
                if ($res['afid']) {
                    $where .= " and sb_assets_info.afid in (" . $res['afid'] . ")";
                } else {
                    $where .= " and sb_assets_info.afid in (-1)";
                }
            }
            if ($repair) {
                //维修公司搜索
                $repairwhere = ['repair' => ['LIKE', '%' . $repair . '%']];
                $res         = $asModel->DB_get_one('assets_factory', 'group_concat(afid) AS afid', $repairwhere);
                if ($res['afid']) {
                    $where .= " and sb_assets_info.afid in (" . $res['afid'] . ")";
                } else {
                    $where .= " and sb_assets_info.afid in (-1)";
                }
            }
            if ($factory) {
                //生产厂商搜索
                $factorywhere = ['factory' => ['LIKE', '%' . $factory . '%']];
                $res          = $asModel->DB_get_one('assets_factory', 'group_concat(afid) AS afid', $factorywhere);
                if ($res['afid']) {
                    $where .= " and sb_assets_info.afid in (" . $res['afid'] . ")";
                } else {
                    $where .= " and sb_assets_info.afid in (-1)";
                }
            }
            if ($assetsDep) {
                //部门搜索
                $dewhere['department'] = ['like', '%' . $assetsDep . '%'];
                $res                   = $asModel->DB_get_all('department', 'departid', $dewhere, '', 'departid asc',
                    '');
                if ($res) {
                    $departids = '';
                    foreach ($res as $k => $v) {
                        $departids .= $v['departid'] . ',';
                    }
                    $departids = trim($departids, ',');
                    $where     .= " and sb_assets_info.departid in (" . $departids . ")";
                } else {
                    $where .= " and sb_assets_info.departid in (-1)";
                }
            }
            if ($assetsDate) {
                //入库时间搜索
                $pretime  = strtotime($assetsDate) - 1;
                $nexttime = strtotime($assetsDate) + 24 * 3600;
                $where    .= " and sb_assets_info.adddate >" . $pretime . ' and sb_assets_info.adddate <' . $nexttime;
            }
            if ($opendate) {
                //启用时间搜索
                $where .= " and sb_assets_info.opendate = " . $opendate;
            }
            if ($factorydate) {
                //出厂时间搜索
                $where .= " and sb_assets_info.factorydate = " . $factorydate;
            }
            if ($assetsUser != null) {
                //录入人员搜索
                $where .= " and sb_assets_info.adduser =" . "'" . $assetsUser . "'";
            } else {
                $where .= '';
            }
            $catname     = [];
            $departname  = [];
            $baseSetting = [];
            include APP_PATH . "Common/cache/category.cache.php";
            include APP_PATH . "Common/cache/department.cache.php";
            include APP_PATH . "Common/cache/basesetting.cache.php";
            $fields  = 'sb_assets_info.*,sb_assets_contract.contract,sb_assets_contract.con_date,sb_assets_contract.price,sb_assets_contract.buy_date,sb_assets_contract.standard_date,sb_assets_contract.guarantee_date,sb_assets_factory.factory,sb_assets_factory.supplier,sb_assets_factory.repair';
            $join[0] = 'LEFT JOIN sb_assets_factory ON sb_assets_info.afid = sb_assets_factory.afid';
            $join[1] = 'LEFT JOIN sb_assets_contract ON sb_assets_info.acid = sb_assets_contract.acid';
            $total   = $asModel->DB_get_count_join('assets_info', '', $join, $where);
            $asinfo  = $asModel->DB_get_all_join('assets_info', '', $fields, $join, $where, '',
                'sb_assets_info.' . $sort . ' ' . $order, $offset . "," . $limit);
            //判断有无查看原值的权限
            $showPrice = get_menu('Assets', 'Lookup', 'showAssetsPrice');

            foreach ($asinfo as $k => $v) {
                $html = '';
                if (!$showPrice) {
                    $asinfo[$k]['buy_price'] = '***';
                }
                $asinfo[$k]['address']    = $departname[$v['departid']]['address'];
                $asinfo[$k]['department'] = $departname[$v['departid']]['department'];
                $asinfo[$k]['jbuy_price'] = $asinfo[$k]['buy_price'];
                $asinfo[$k]['category']   = $catname[$v['catid']]['category'];
                $asinfo[$k]['as_status']  = $baseSetting['assets']['assets_status']['value'][$v['status']];
                $html                     .= $this->returnButtonLink('设备详情',
                    C('ADMIN_NAME') . '/Lookup/showAssets.html', 'layui-btn layui-btn-xs layui-btn-normal', '',
                    'lay-event = showAssets');
                $asinfo[$k]['operation']  = $html;
            }
            $result['total']  = $total;
            $result["offset"] = $offset;
            $result["limit"]  = $limit;
            $result["code"]   = 200;
            $result['rows']   = $asinfo;
            if (!$result['rows']) {
                $result['msg']  = '暂无相关数据';
                $result['code'] = 400;
            }
            $this->ajaxReturn($result, 'json');
        } else {
            $asModel = new AssetsInfoModel();
            //录入人员
            $users = $asModel->getUser();
            //资产来源
            $assfrom = $asModel->DB_get_one('base_setting', 'value', ['set_item' => 'assets_assfrom']);
            $assfrom = json_decode($assfrom['value']);
            //资金来源
            $capitalfrom = $asModel->DB_get_one('base_setting', 'value', ['set_item' => 'assets_capitalfrom']);
            $capitalfrom = json_decode($capitalfrom['value']);
            //资产状态
            $assets_status                            = [];
            $assets_status[C('ASSETS_STATUS_USE')]    = C('ASSETS_STATUS_USE_NAME');
            $assets_status[C('ASSETS_STATUS_REPAIR')] = C('ASSETS_STATUS_REPAIR_NAME');
            $assets_status[C('ASSETS_STATUS_SCRAP')]  = C('ASSETS_STATUS_SCRAP_NAME');
            //财务分类
            $finance = $asModel->DB_get_one('base_setting', 'value', ['set_item' => 'assets_finance']);
            $finance = json_decode($finance['value']);
            $this->assign('assfrom', $assfrom);
            $this->assign('capitalfrom', $capitalfrom);
            $this->assign('finance', $finance);
            $this->assign('status', $assets_status);
            $this->assign('users', $users);
            $this->display();
        }
    }

    /**
     * Notes:导出设备
     */
    public function exportAssets()
    {
        $asModel    = new AssetsInfoModel();
        $orderField = I('post.orderField');
        $orderType  = I('post.orderType');
        if ($orderField && $orderType) {
            $order = 'A.' . $orderField . ' ' . $orderType;
        } else {
            $order = 'A.adddate desc';
        }
        $assid  = I('POST.assid');
        $assid  = trim($assid, ',');
        $assid  = explode(',', $assid);
        $fields = I('POST.fields');
        $fields = trim($fields, ',');
        $fields = explode(',', $fields);
        if (!$assid || !$fields) {
            $this->error('参数错误！');
            exit;
        }
        //获取要导出的数据
        //读取assets、factory数据库字段
        foreach ($fields as $key => $value) {
            if ($value == 'department') {
                $fields[$key] = 'departid';
            }
            if ($value == 'category') {
                $fields[$key] = 'catid';
            }
            if ($value == 'assets_level_name') {
                $fields[$key] = 'assets_level';
            }
            if ($value == 'is_domesticName') {
                $fields[$key] = 'is_domestic';
            }
            if ($value == 'status_name') {
                $fields[$key] = 'status';
            }
            if ($value == 'pay_statusName') {
                $fields[$key] = 'pay_status';
            }
        }
        $fields_1 = $asModel->getFields('assets_info', $fields, 'A');
        $fields_2 = $asModel->getFields('assets_factory', $fields, 'B');
        if ($fields_1 && $fields_2) {
            $selFields = $fields_1 . ',' . $fields_2;
        } elseif ($fields_1) {
            $selFields = $fields_1;
        } else {
            $selFields = $fields_2;
        }
        $join = " LEFT JOIN sb_assets_factory as B on A.assid = B.assid ";
        $data = $asModel->DB_get_all_join('assets_info', 'A', $selFields, $join, ['A.assid' => ['in', $assid]], '',
            $order);
        //格式化数据
        $data = $asModel->formatData($data);
        foreach ($data as &$value) {
            $value['assnum'] = $value['assnum'] . ' ';
        }
        $showName = ['xuhao' => '序号'];
        $keyValue = $asModel->getDefaultShowFields();
        foreach ($keyValue as $k => $v) {
            if (in_array($k, $fields)) {
                $showName[$k] = $v;
            }
        }
        exportAssets(['设备列表'], '设备列表', $showName, $data);
    }

    // 推送数据
    public function pushData()
    {
        $api = new CiAnTongAPI();
        $assids = I('post.assids');
        // 组织数据
        $data = Db::query("SELECT assid AS assetId,assets AS name,serialnum AS label,assnum AS code,department AS tenantName,departnum AS tenantCode,category AS categoryName,brand as brandName,model as modelName,buy_price AS price,UNIX_TIMESTAMP(factorydate) AS factoryDate,UNIX_TIMESTAMP(storage_date) AS purchaseDate,depreciable_lives AS yearLimit,A.remark FROM sb_assets_info AS A LEFT JOIN sb_department AS B ON A.departid = B.departid LEFT JOIN sb_category AS C ON A.catid = C.catid WHERE assid in (" . $assids . ")");
        foreach ($data as $k => $v) {
            $data[$k]['purchaseDate'] = $data[$k]['purchaseDate'] * 1000;
            $data[$k]['factoryDate'] = $data[$k]['factoryDate'] * 1000;
        }
        // 开始对接
        foreach ($data as $k => $v) {
            // 对接接口
            // 插监控表 没有才插
            $record = M('monitor')->field('id')->where(['assid' => $v['assetId']])->find();
            if (!$record) {
//                var_dump($v['assetId']);
                $add_record_data = [
                    'assid' => $v['assetId'],
                    'create_at' => getHandleDate(time())
                ];
                M('monitor')->add($add_record_data);
            }
        }
//                var_dump($assets);exit;
//                $data = array(
//                    'assetId' => "",//必选 String 资产唯一标识
//                    'name' => "",//必选 String 资产设备名
//                    'label' => "",//必选 String 资产标签码(sn码)
//                    'code' => "",//必选 String 资产编号
//                    'tenantName' => "",//必选 String 使用科室名称
//                    'tenantCode' => "",//必选 String 使用科室编码
//                    'categoryName' => "",//必选 String 分类名称
//                    'brandName' => "",//非必选 String 品牌名称
//                    'modelName' => "",//非必选 String 型号名称
//                    'price' => "",//非必选 Double 原值
//                    'purchaseDate' => "",//非必选 Long 购买日期的毫秒时间戳
//                    'factoryDate' => "",//非必选 Long 出厂日期的毫秒时间戳
//                    'yearLimit' => "",//非必选 Integer 折旧年限
//                    'industryName' => "",//非必选 String 行业分类名称
//                    'remark' => "",//非必选 String 备注
//                );
        $endpoint = "/api/asset/add";
        $post_data = array(
            'key' => $api->key,
            'json' => json_encode($data, JSON_UNESCAPED_UNICODE)
        );
        $response = $api->sendRequest($endpoint, $post_data);
        // 插日志记录
        $add_log_data = [
            'system' => '中科物联网',
            'interface' => '资产推送',
            'status' => json_decode($response, true)['code'],
            'response' => $response,
            'create_at' => getHandleDate(time())
        ];
        M('interface_log')->add($add_log_data);
        // 返回数据出去
//        $res = [
//            'code' => 200,
//            'data' => count($data)
//        ];
//        $this->ajaxReturn($res);
    }

}
