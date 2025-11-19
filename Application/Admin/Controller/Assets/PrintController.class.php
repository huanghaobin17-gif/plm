<?php

namespace Admin\Controller\Assets;

use Admin\Controller\Login\CheckLoginController;
use Admin\Controller\NotCheckLogin\PublicController;
use Admin\Model\ArchivesModel;
use Admin\Model\AssetsInfoModel;

class PrintController extends CheckLoginController
{
    private $MODULE = 'Assets';

    public function design()
    {
        $hospital_id   = session('current_hospitalid');
        $hospital_name = session('current_hospitalname');
        $assModel      = new AssetsInfoModel();
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'set_default':
                    $temp_name = I('post.temp_name');
                    $assModel->updateData('assets_print_temp', ['is_select' => 0], ['1']);
                    $res = $assModel->updateData('assets_print_temp', ['is_select' => 1],
                        ['hospital_id' => $hospital_id, 'temp_name' => $temp_name]);
                    if ($res) {
                        $names  = $assModel->DB_get_all('assets_print_temp', 'temp_name',
                            ['hospital_id' => $hospital_id, 'temp_name' => ['neq', $temp_name]]);
                        $others = [];
                        foreach ($names as $k => $v) {
                            $others[] = $v['temp_name'];
                        }
                        $this->ajaxReturn([
                            'status'    => 1,
                            'msg'       => '设置成功！',
                            'temp_name' => $temp_name,
                            'others'    => $others,
                        ]);
                    } else {
                        $this->ajaxReturn(['status' => -1, 'msg' => '设置失败！']);
                    }
                    break;
                case 'get_content':
                    $assInfo[0]  = [
                        'assets'       => '内热式软管自动罐装封口机',
                        'assnum'       => '68010149901001',
                        'assorignum'   => '545050292',
                        'category'     => '医用X射线设备',
                        'department'   => '麻醉手术科',
                        'factorynum'   => '8050232',
                        'model'        => '松下MDF-382E(CN)',
                        'opendate'     => '2018-12-01',
                        'serialnum'    => 'SN:BI3A1209322',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '张三',
                    ];
                    $assInfo[1]  = [
                        'assets'       => '婴儿辐射保暖台',
                        'assnum'       => '68010354505028',
                        'assorignum'   => '10509',
                        'category'     => '婴儿保育设备',
                        'department'   => '新生儿科',
                        'factorynum'   => 'SCG11179416WA',
                        'model'        => 'WG-OT300B',
                        'opendate'     => '2018-12-02',
                        'serialnum'    => 'HBVA00001',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '和二',
                    ];
                    $assInfo[2]  = [
                        'assets'       => '2M高分辨率专业显示器显示卡',
                        'assnum'       => '6856021193120',
                        'assorignum'   => 'HBVA00001',
                        'category'     => '无创监护仪器',
                        'department'   => '中西医结合皮肤科',
                        'factorynum'   => 'S1305014',
                        'model'        => 'PM-9000EXPRESS',
                        'opendate'     => '2018-12-03',
                        'serialnum'    => 'ERW2233',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '王八',
                    ];
                    $assInfo[3]  = [
                        'assets'       => '妇科门诊检查模型显示器',
                        'assnum'       => '6856021061364',
                        'assorignum'   => '6976',
                        'category'     => '电子压力测定装置',
                        'department'   => '血液净化中心',
                        'factorynum'   => 'OT300Ba151001005',
                        'model'        => 'WATOEX-35',
                        'opendate'     => '2018-12-04',
                        'serialnum'    => 'hx123004',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '田七',
                    ];
                    $assInfo[4]  = [
                        'assets'       => '高级婴儿复苏模型',
                        'assnum'       => '6823021011517',
                        'assorignum'   => '54697692',
                        'category'     => '超声辅助材料',
                        'department'   => '门诊外科',
                        'factorynum'   => 'FF-66022287',
                        'model'        => 'DW-25L262',
                        'opendate'     => '2018-12-01',
                        'serialnum'    => 'D0458',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '赵六',
                    ];
                    $assInfo[5]  = [
                        'assets'       => '全自动血球仪',
                        'assnum'       => '6856021315908',
                        'assorignum'   => '545056976',
                        'category'     => 'X射线诊断设备及高压发生装置',
                        'department'   => '康复医学部',
                        'factorynum'   => 'E150230',
                        'model'        => '天田 TT/DTYX-80T',
                        'opendate'     => '2018-12-05',
                        'serialnum'    => 'XUSDHJ12',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '王五',
                    ];
                    $assInfo[6]  = [
                        'assets'       => '空气波压力治疗仪',
                        'assnum'       => '687810214',
                        'assorignum'   => '54505634976',
                        'category'     => '理疗仪器',
                        'department'   => '康复医学部',
                        'factorynum'   => 'E150230',
                        'model'        => '天田 TT/DTYX-80T',
                        'opendate'     => '2018-12-06',
                        'serialnum'    => 'XUSDHJ12',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '李四',
                    ];
                    $assInfo[7]  = [
                        'assets'       => '空气波压力治疗仪',
                        'assnum'       => '687810214',
                        'assorignum'   => '54505634976',
                        'category'     => '理疗仪器',
                        'department'   => '康复医学部',
                        'factorynum'   => 'E150230',
                        'model'        => '天田 TT/DTYX-80T',
                        'opendate'     => '2018-12-07',
                        'serialnum'    => 'XUSDHJ12',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '李四',
                    ];
                    $assInfo[8]  = [
                        'assets'       => '空气波压力治疗仪',
                        'assnum'       => '687810214',
                        'assorignum'   => '54505634976',
                        'category'     => '理疗仪器',
                        'department'   => '康复医学部',
                        'factorynum'   => 'E150230',
                        'model'        => '天田 TT/DTYX-80T',
                        'opendate'     => '2018-12-07',
                        'serialnum'    => 'XUSDHJ12',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '李四',
                    ];
                    $assInfo[9]  = [
                        'assets'       => '空气波压力治疗仪',
                        'assnum'       => '687810214',
                        'assorignum'   => '54505634976',
                        'category'     => '理疗仪器',
                        'department'   => '康复医学部',
                        'factorynum'   => 'E150230',
                        'model'        => '天田 TT/DTYX-80T',
                        'opendate'     => '2018-12-07',
                        'serialnum'    => 'XUSDHJ12',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '李四',
                    ];
                    $assInfo[10] = [
                        'assets'       => '空气波压力治疗仪',
                        'assnum'       => '687810214',
                        'assorignum'   => '54505634976',
                        'category'     => '理疗仪器',
                        'department'   => '康复医学部',
                        'factorynum'   => 'E150230',
                        'model'        => '天田 TT/DTYX-80T',
                        'opendate'     => '2018-12-07',
                        'serialnum'    => 'XUSDHJ12',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '李四',
                    ];
                    $assInfo[11] = [
                        'assets'       => '空气波压力治疗仪',
                        'assnum'       => '687810214',
                        'assorignum'   => '54505634976',
                        'category'     => '理疗仪器',
                        'department'   => '康复医学部',
                        'factorynum'   => 'E150230',
                        'model'        => '天田 TT/DTYX-80T',
                        'opendate'     => '2018-12-07',
                        'serialnum'    => 'XUSDHJ12',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '李四',
                    ];
                    $assInfo[12] = [
                        'assets'       => '空气波压力治疗仪',
                        'assnum'       => '687810214',
                        'assorignum'   => '54505634976',
                        'category'     => '理疗仪器',
                        'department'   => '康复医学部',
                        'factorynum'   => 'E150230',
                        'model'        => '天田 TT/DTYX-80T',
                        'opendate'     => '2018-12-07',
                        'serialnum'    => 'XUSDHJ12',
                        'storage_date' => '2019-01-01',
                        'remark'       => '这是设备的备注信息',
                        'assetsrespon' => '李四',
                    ];
                    $temps       = $assModel->DB_get_all('assets_print_temp', 'temp_name,show_fields,is_select',
                        ['hospital_id' => $hospital_id], '', 'temp_id asc');
                    $is_select   = '';
                    foreach ($temps as &$value) {
                        if ($value['is_select'] == 1) {
                            $is_select = $value['temp_name'];
                        }
                        $value['show_fields'] = json_decode($value['show_fields'], true);
                    }
                    $this->ajaxReturn([
                            'status'        => 1,
                            'data'          => $temps,
                            'assInfo'       => $assInfo,
                            'is_select'     => $is_select,
                            'hospital_name' => session('current_hospitalname'),
                            'msg'           => '成功',
                        ]
                    );
                    break;
                case 'design_label':
                    $zidingyi_value         = $_POST['zidingyi_value'];
                    $data['add_user']       = session('username');
                    $data['add_time']       = date('Y-m-d H:i:s');
                    $data['is_select']      = 0;
                    $data['system_default'] = 0;
                    $data['printer_type']   = $_POST['printer_type'];
                    $data['pic_width']      = $_POST['pic_width'];
                    $data['font_size']      = $_POST['font_size'];
                    $data['hospital_id']    = $hospital_id;
                    $data['temp_name']      = $_POST['temp_name'];
                    $data['temp_content']   = $_POST['temp_content'];
                    unset($_POST['action']);
                    unset($_POST['temp_name']);
                    unset($_POST['printer_type']);
                    unset($_POST['pic_width']);
                    unset($_POST['font_size']);
                    unset($_POST['temp_content']);
                    unset($_POST['zidingyi_value']);
                    $option      = [
                        'assets'       => '设备名称',
                        'assnum'       => '设备编号',
                        'assorignum'   => '设备编号',
                        'category'     => '设备分类',
                        'department'   => '使用科室',
                        'factorynum'   => '出厂编号',
                        'model'        => '规格型号',
                        'opendate'     => '启用日期',
                        'serialnum'    => '序 列 号',
                        'storage_date' => '入库日期',
                        'remark'       => '设备备注',
                        'assetsrespon' => '负 责 人',
                    ];
                    $show_fields = [];
                    foreach ($_POST as $k => $v) {
                        switch ($v) {
                            case 'zidingyi':
                                $show_fields[$v] = $zidingyi_value;
                                break;
                            case 'hos_name':
                                $show_fields[$v] = $hospital_name;
                                break;
                            default:
                                $show_fields[$v] = $option[$v];
                                break;
                        }
                    }
                    if (count($show_fields) == 0) {
                        $this->ajaxReturn(['status' => -1, 'msg' => '请选择要显示的内容！']);
                    }
                    $data['show_fields'] = json_encode($show_fields, JSON_UNESCAPED_UNICODE);
                    //查询是否已存在
                    $exists = $assModel->DB_get_one('assets_print_temp', 'temp_name',
                        ['hospital_id' => $hospital_id, 'temp_name' => $data['temp_name']]);
                    if ($exists['temp_name']) {
                        //存在，更新旧数据
                        $updateData                 = [];
                        $updateData['pic_width']    = $data['pic_width'];
                        $updateData['font_size']    = $data['font_size'];
                        $updateData['show_fields']  = $data['show_fields'];
                        $updateData['temp_content'] = $data['temp_content'];
                        $updateData['edit_user']    = session('username');
                        $updateData['edit_time']    = date('Y-m-d H:i:s');
                        $res                        = $assModel->updateData('assets_print_temp', $updateData,
                            ['hospital_id' => $hospital_id, 'temp_name' => $data['temp_name']]);
                        if ($res) {
                            $this->ajaxReturn(['status' => 1, 'msg' => '修改成功！']);
                        } else {
                            $this->ajaxReturn(['status' => -1, 'msg' => '修改失败！']);
                        }
                    } else {
                        $res = $assModel->insertData('assets_print_temp', $data);
                        if ($res) {
                            $this->ajaxReturn(['status' => 1, 'msg' => '设置成功！']);
                        } else {
                            $this->ajaxReturn(['status' => -1, 'msg' => '设置失败！']);
                        }
                    }
                    break;
                case 'delete_design':
                    $temp_name = I('post.temp_name');
                    $oldsel    = $assModel->DB_get_one('assets_print_temp', 'temp_id',
                        ['hospital_id' => $hospital_id, 'temp_name' => $temp_name]);
                    if ($oldsel['temp_id']) {
                        $res = $assModel->deleteData('assets_print_temp',
                            ['hospital_id' => $hospital_id, 'temp_name' => $temp_name]);
                    }
                    if ($res) {
                        $this->ajaxReturn(['status' => 1, 'msg' => '删除成功！']);
                    } else {
                        $this->ajaxReturn(['status' => -1, 'msg' => '删除失败！']);
                    }
                    break;
                default:
                    break;
            }
        } else {
            $action = I('get.action');
            switch ($action) {
                case 'design_label':
                    $option               = [
                        'assets'       => '设备名称',
                        'assnum'       => '设备编号',
                        'assorignum'   => '原编号(设备编号)',
                        'category'     => '设备分类',
                        'department'   => '使用科室',
                        'factorynum'   => '出厂编号',
                        'model'        => '规格型号',
                        'opendate'     => '启用日期',
                        'serialnum'    => '序 列 号',
                        'storage_date' => '入库日期',
                        'remark'       => '设备备注',
                        'assetsrespon' => '(设备)负责人',
                    ];
                    $show                 = [];
                    $show['assets']       = '设备名称：内热式软管自动罐装封口机';
                    $show['assnum']       = '设备编号：68010149901001';
                    $show['assorignum']   = '设备编号：545050292';
                    $show['category']     = '设备分类：医用X射线设备';
                    $show['department']   = '使用科室：中西医结合皮肤科';
                    $show['factorynum']   = '出厂编号：SCG11179416WA';
                    $show['model']        = '规格型号：PM-9000EXPRESS';
                    $show['opendate']     = '启用日期：2018-12-01';
                    $show['serialnum']    = '序 列 号：SN:BI3A1209322';
                    $show['storage_date'] = '入库日期：2019-01-01';
                    $show['remark']       = '设备备注：这是设备的备注信息';
                    $show['assetsrespon'] = '负 责 人：张三';
                    $show['zidingyi']     = '固定资产管理卡';
                    $show['hos_name']     = $hospital_name;
                    //查询用户自定义的设计
                    $user_designs = $assModel->DB_get_all('assets_print_temp', '*', ['system_default' => 0]);
                    $fields       = $data = $font_size = [];
                    foreach ($user_designs as $k => $v) {
                        foreach ($v as $k1 => $v1) {
                            if ($k1 == 'temp_name') {
                                $font_size[$v1] = $user_designs[$k]['font_size'];
                            }
                        }
                    }

                    foreach ($user_designs as $k => $v) {
                        $show_fields = json_decode($v['show_fields'], true);
                        $n           = 1;
                        foreach ($show_fields as $k1 => $v1) {
                            $data[$v['temp_name']][]                 = $show[$k1];
                            $fields[$v['temp_name']]['fields_' . $n] = $k1;
                            $n++;
                        }
                        $fields[$v['temp_name']]['pic_width'] = $v['pic_width'];
                        $fields[$v['temp_name']]['font_size'] = $v['font_size'];
                    }
                    $this->assign('hospital_name', $hospital_name);
                    $this->assign('option', $option);
                    $this->assign('fields', $fields);
                    $this->assign('font_size', $font_size);
                    $this->assign('data', $data);
                    $this->assign('design', get_url());
                    $this->display('design_label');
                    break;
                default:
                    //查询标签模板
                    $temps        = $assModel->DB_get_all('assets_print_temp', '*', ['hospital_id' => $hospital_id], '',
                        'temp_id asc');
                    $printer_type = $return_tmp = [];
                    foreach ($temps as $k => $v) {
                        if (!in_array($v['printer_type'], $printer_type)) {
                            $printer_type[] = $v['printer_type'];
                        }
                    }
                    foreach ($printer_type as $k => $v) {
                        foreach ($temps as $km => $vm) {
                            if ($vm['printer_type'] == $v) {
                                $return_tmp[$v][] = $vm;
                            }
                        }
                    }
                    $this->assign('hospital_name', $hospital_name);
                    $this->assign('temps', $return_tmp);
                    $font_size = [];
                    foreach ($temps as $k => $v) {
                        foreach ($v as $k1 => $v1) {
                            if ($k1 == 'temp_name') {
                                $font_size[$v1] = $temps[$k]['font_size'];
                            }
                        }
                    }
                    $this->assign('font_size', $font_size);
                    $this->assign('design', get_url());
                    $this->display();
                    break;
            }
        }
    }

    public function verify()
    {
        $assModel = new AssetsInfoModel();
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                case 'exportAssets':
                    $this->exportAssets();
                    break;

                default:
                    $result = $assModel->getverify();
                    $this->ajaxReturn($result);
                    break;
            }
        } else {
            $action = I('GET.action');
            switch ($action) {
                case 'showFile':
                    $files = $assModel->getprintFile();
                    $this->assign('uploadinfo', $files);
                    $this->display('showFile');
                    break;
                default:

                    $notCheck = new PublicController();
                    $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
                    $this->assign('verify', get_url());
                    $this->display();
            }
        }
    }

    /**
     * Notes:导出设备
     */
    public function exportAssets()
    {
        $asModel = new AssetsInfoModel();
        $assid   = I('POST.assid');
        $assid   = trim($assid, ',');
        $assid   = explode(',', $assid);
        $fields  = I('POST.fields');
        $fields  = trim($fields, ',');
        $fields  = explode(',', $fields);
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
            if ($value == 'code_status') {
                $fields[$key] = 'code_url';
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
            'A.adddate desc', '');
        //格式化数据
        $data     = $asModel->formatData($data);
        $showName = ['xuhao' => '序号'];
        $keyValue = $asModel->getDefaultShowFields();
        foreach ($keyValue as $k => $v) {
            if (in_array($k, $fields)) {
                $showName[$k] = $v;
            }
        }
        exportAssets(['设备列表'], '设备列表', $showName, $data);
    }

    public function printAssets()
    {
        $assModel      = new AssetsInfoModel();
        $hospital_name = session('current_hospitalname');
        if (IS_POST) {
            $action = I('post.action');
            switch ($action) {
                case 'get_temp':
                    $tem = $assModel->DB_get_one('assets_print_temp', 'temp_content', ['is_select' => 1]);
                    $this->ajaxReturn(['status' => 1, 'temp_conctent' => $tem['temp_content']]);
                    break;
                case 'batchPrint':
                    $assid = I('post.assid');
                    $assid = trim($assid, ',');
                    //查询打印模板
                    $tem = $assModel->DB_get_one('assets_print_temp',
                        'printer_type,temp_name,show_fields,pic_width,font_size', ['is_select' => 1]);
                    if ($tem['printer_type'] == 'zebra') {
                        $margin_top = 9;
                    } elseif ($tem['printer_type'] == 'brother') {
                        $margin_top = 13;
                    } else {
                        $margin_top = 0;
                    }
                    $font_size[$tem['temp_name']]['font_size'] = $tem['font_size'];
                    $font_size[$tem['temp_name']]['pic_width'] = $tem['pic_width'];
                    $this->assign('margin_top', $margin_top);
                    $this->assign('font_size', $font_size);
                    $this->assign('temp_name', $tem['temp_name']);
                    $this->assign('show_fields', json_decode($tem['show_fields'], true));
                    $this->assign('hospital_name', $hospital_name);
                    //筛选出要查询的字段
                    //查询数据
                    $assInfo    = $assModel->getPrintLabelData($assid);
                    $departname = [];
                    $catname    = [];
                    include APP_PATH . "Common/cache/category.cache.php";
                    include APP_PATH . "Common/cache/department.cache.php";
                    foreach ($assInfo as $k => $v) {
                        //判断二维码图片
                        if ($v['code_url']) {
                            $fileExists = file_exists('.' . $v['code_url']);
                            if (!$fileExists) {
                                //文件已不存在，重新生成二维码文件
                                $codeUrl = $assModel->createCodePic($v['assnum']);
                                if ($codeUrl) {
                                    //保存二维码图片地址到数据库
                                    $codeUrl = trim($codeUrl, '.');
                                    $assModel->updateData('assets_info', ['code_url' => $codeUrl],
                                        ['assid' => $v['assid']]);
                                    $assInfo[$k]['code_url'] = $codeUrl;
                                }
                            }
                        } else {
                            $codeUrl = $assModel->createCodePic($v['assnum']);
                            if ($codeUrl) {
                                //保存二维码图片地址到数据库
                                $codeUrl = trim($codeUrl, '.');
                                $assModel->updateData('assets_info', ['code_url' => $codeUrl],
                                    ['assid' => $v['assid']]);
                                $assInfo[$k]['code_url'] = $codeUrl;
                            }
                        }
                        $assInfo[$k]['opendate']   = HandleEmptyNull($v['opendate']);
                        $assInfo[$k]['department'] = $departname[$v['departid']]['department'];
                        $assInfo[$k]['category']   = $catname[$v['catid']]['category'];
                    }
                    $show_fields = json_decode($tem['show_fields'], true);
                    $html        = '';
                    $assetNumber = -1;
                    foreach ($assInfo as $k => $v) {
                        $data      = [];
                        $i         = 0;
                        $marginTop = -110;
                        $scale     = 90;
                        $length    = 13;
                        foreach ($show_fields as $k1 => $v1) {
                            if ($k1 == 'hos_name') {
                                $data[$i]['title'] = $hospital_name;
                            } else {
                                $data[$i]['title'] = $v1;
                            }
                            $data[$i]['content'] = $assInfo[$k][$k1];
                            $i++;
                        }
                        $length                 = strlen($v['assnum']) - $length;
                        $data[$i]['margin_top'] = $marginTop - $length * 10;
                        $data[$i]['scale']      = round(($scale - $length * 5) / 100, 2);

                        $assetNumber++;
                        $this->assign('assInfo', $assInfo[$k]);
                        $this->assign('data', $data);
                        $this->assign('number', $assetNumber);
                        $html .= $this->display('batch_print_label');
                    }
                    break;
                default:
                    $result = $assModel->getPrintAssets();
                    $this->ajaxReturn($result);
            }
        } else {
            //所属科室
            $notCheck = new PublicController();
            $this->assign('departmentInfo', $notCheck->getAllDepartmentSearchSelect());
            $this->assign('printAssets', get_url());
            $this->display();
        }
    }

    /*
     * 档案盒标签打印
     */
    public function printBox()
    {
        $archivesModel = new ArchivesModel();
        if (IS_POST) {
            $assModel = new AssetsInfoModel();
            $action   = I('post.action');
            switch ($action) {
                case 'batchPrint':
                    $box_id   = I('post.box_id');
                    $box_id   = trim($box_id, ',');
                    $boxidArr = explode(',', $box_id);
                    //筛选出要查询的字段
                    //查询数据
                    $boxInfo = $archivesModel->get_box_data($boxidArr);
                    foreach ($boxInfo as $k => $v) {
                        //判断二维码图片
                        if ($v['code_url']) {
                            $fileExists = file_exists('.' . $v['code_url']);
                            if (!$fileExists) {
                                //文件已不存在，重新生成二维码文件
                                $codeUrl = $assModel->createCodePic($v['box_num']);
                                if ($codeUrl) {
                                    //保存二维码图片地址到数据库
                                    $codeUrl = trim($codeUrl, '.');
                                    $assModel->updateData('archives_box', ['code_url' => $codeUrl],
                                        ['box_id' => $v['box_id']]);
                                    $boxInfo[$k]['code_url'] = $codeUrl;
                                }
                            }
                        } else {
                            $codeUrl = $assModel->createCodePic($v['box_num']);
                            if ($codeUrl) {
                                //保存二维码图片地址到数据库
                                $codeUrl = trim($codeUrl, '.');
                                $assModel->updateData('archives_box', ['code_url' => $codeUrl],
                                    ['box_id' => $v['box_id']]);
                                $boxInfo[$k]['code_url'] = $codeUrl;
                            }
                        }
                    }
                    $html = '';
                    foreach ($boxInfo as $k => $v) {
                        $this->assign('boxInfo', $boxInfo[$k]);
                        $html .= $this->display('batch_print_box');
                    }
                    break;
                default:
                    $result = $archivesModel->get_box_lists();
                    $this->ajaxReturn($result);
            }
        } else {
            $this->assign('printBox', get_url());
            $this->display();
        }
    }
}
