<?php

namespace Admin\Model;

use Admin\Controller\Tasks\TasksController;
use Think\Model;
use Think\Model\RelationModel;

class PeopleModel extends CommonModel
{
    protected $len = 100;
    private $MODULE = 'BaseSetting';
    private $Controller = 'Dictionary';

    protected $tablePrefix = 'sb_';
    protected $tableName = 'people';

    //获取所有设备字典
    public function getAllDic()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'ASC';
        $sort = I('post.sort') ? I('post.sort') : 'id';
        $tname = I('post.tname');
        $where = '';
        // $dic_category = I('post.dic_category');
        // $hospital_id = I('post.hospital_id');
        if ($tname) {
            //设备名称搜索
            $where['tname'] = array('like', "%$tname%");
        }
        // if ($dic_category) {
        //     //字典分类
        //     $where['dic_category'] = array('like', "%$dic_category%");
        // }
    
        // $where['status'] = ['EQ', C('OPEN_STATUS')];
        //echo $tname;die;
        $total = $this->DB_get_count('people');
        
        //echo $total;die;
        $dicassets = $this->DB_get_all('people', '*', $where, '', $sort . ' ' . $order, $offset . ',' . $limit);
        //var_dump($dicassets);die;
        //查询当前用户是否有权限进行删除字典操作
        $deleDic = get_menu($this->MODULE, 'Dictionary', 'delTestDic');
        //查询当前用户是否有权限进行修改字典操作
        $editDic = get_menu($this->MODULE, 'Dictionary', 'editTestDic');
        $catname=[];
       // include APP_PATH . "Common/cache/category.cache.php";
        foreach ($dicassets as $k => $v) {
            $html = '<div class="layui-btn-group">';
            if ($editDic) {
                $html .= '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit" id="edit" href="javascript:void(0)" data-url="' . $editDic['actionurl'] . '">修改</a>';
            }
            if ($deleDic) {
                $html .= '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete" id="delete" href="javascript:void(0)" data-url="' . $deleDic['actionurl'] . '">删除</a>';
            }
            $html .= '</div>';
            $v['like'] = str_replace('eat', '吃饭', $v['like']);
            $v['like'] = str_replace('sleep', '睡觉', $v['like']);
            $dicassets[$k]['like'] = $v['like'];
            $dicassets[$k]['operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $dicassets;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    //新增设备字典
    public function addDic()
    {
        $menuData = get_menu($this->MODULE, $this->Controller, 'addTestDic');
        if (!$menuData) {
            return array('status' => -1, 'msg' => '无权限新增字典');
        }
        $data['tname'] = trim(I('post.tname'));
        $data['like'] = trim(I('post.like'));
        $data['remark'] = trim(I('post.remark'));
        $data['addtime'] = date('Y-m-d H:i:s');
        if (empty($data['tname'])) {
            return array('status' => -1, 'msg' => '必填项不能为空！');
        }
      
        $newid = $this->insertData('people', $data);
        if ($newid) {
            return array('status' => 1, 'msg' => '添加字典成功！');
        } else {
            return array('status' => -1, 'msg' => '添加字典失败！');
        }
    }

    //修改设备字典
    public function editDic()
    {
        $id = I('post.id');
        $data['tname'] = trim(I('post.tname'));
        $data['catid'] = I('post.catid');
        $data['like'] = I('post.like');
    
        $data['remark'] = trim(I('post.remark'));

        $data['edittime'] = date('Y-m-d H:i:s');
        if (empty($data['tname'])) {
            return array('status' => -1, 'msg' => '必填项不能为空！');
        }
        // //查询是否已存在该设备名称
        // $is_exists = $this->DB_get_one('dic_assets','dic_assid',array('hospital_id'=>session('current_hospitalid'),'assets'=>$data['assets'],'dic_assid'=>array('neq',$dic_assid)));
        // if($is_exists){
        //     return array('status' => -1, 'msg' => '该设备名称已存在！');
        // }
        $res = $this->updateData('people', $data, array('id' => $id));
        if ($res) {
            return array('status' => 1, 'msg' => '修改设备字典成功！');
        } else {
            return array('status' => -1, 'msg' => '修改设备字典失败！');
        }
    }

    /**
     * Notes: 品牌字典
     */
    public function getAllBrandDic()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'brand_id';
        $brand_name = I('post.brand_name');
        $where['is_delete'] = 0;
        if ($brand_name) {
            //设备名称搜索
            $where['brand_name'] = array('like', "%$brand_name%");
        }
        $total = $this->DB_get_count('dic_brand', $where);
        $data = $this->DB_get_all('dic_brand', '*', $where, '', $sort . ' ' . $order, $offset . ',' . $limit);
        //查询当前用户是否有权限进行删除字典操作
        $deleDic = get_menu($this->MODULE, 'Dictionary', 'delBrandDic');
        $editDic = get_menu($this->MODULE, 'Dictionary', 'editBrandDic');
        include APP_PATH . "Common/cache/category.cache.php";
        foreach ($data as $k => $v) {
            $html = '<div class="layui-btn-group">';
            if ($editDic) {
                $html .= '<button class="layui-btn layui-btn-xs layui-btn-normal" lay-event="edit" id="edit" href="javascript:void(0)" data-url="' . $editDic['actionurl'] . '">修改</button>';
            }
            if ($deleDic) {
                $html .= '<button class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete" id="delete" href="javascript:void(0)" data-url="' . $deleDic['actionurl'] . '">删除</button>';
            }
            $html .= '</div>';
            $data[$k]['operation'] = $html;
        }

        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $data;
        $result['code'] = 200;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 新增品牌字典
     */
    public function addBrandDic()
    {
        $data['brand_name'] = trim(I('post.brand_name'));
        $data['brand_desc'] = trim(I('post.brand_desc'));
        if(!$data['brand_name']){
            return array('status'=>-1,'msg'=>'品牌名称不能为空！');
        }
        //查询是否已存在该品牌名称
        $is_exists = $this->DB_get_one('dic_brand','brand_id',array('brand_name'=>$data['brand_name']));
        if($is_exists){
            return array('status' => -1, 'msg' => '该品牌名称已存在！');
        }
        $res = $this->insertData('dic_brand',$data);
        if($res){
            return array('status'=>1,'msg'=>'新增品牌字典成功！');
        }else{
            return array('status'=>-1,'msg'=>'新增品牌字典失败！');
        }
    }

    /**
     * Notes: 修改品牌字典
     */
    public function editBrandDic()
    {
        $data['brand_name'] = trim(I('post.brand_name'));
        $data['brand_desc'] = trim(I('post.brand_desc'));
        if(!$data['brand_name']){
            return array('status'=>-1,'msg'=>'品牌名称不能为空！');
        }
        //查询是否已存在该品牌名称
        $is_exists = $this->DB_get_one('dic_brand','brand_id',array('brand_name'=>$data['brand_name'],'brand_id'=>array('neq',I('post.brand_id'))));
        if($is_exists){
            return array('status' => -1, 'msg' => '该品牌名称已存在！');
        }
        $res = $this->updateData('dic_brand',$data,array('brand_id'=>I('post.brand_id')));
        if($res){
            return array('status'=>1,'msg'=>'修改品牌字典成功！');
        }else{
            return array('status'=>-1,'msg'=>'修改品牌字典失败！');
        }
    }

    //配件字典列表
    public function partsDic(){
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'ASC';
        $sort = I('post.sort') ? I('post.sort') : 'dic_partid';
        $parts = I('post.parts');
        $dic_category = I('post.dic_category');
        $hospital_id = I('post.hospital_id');
        if ($parts) {
            //配件名称搜索
            $where['parts'] = array('like',"%$parts%");
        }
        if ($dic_category) {
            //字典分类
            $where['dic_category'] = array('like',"%$dic_category%");
        }
        if ($hospital_id) {
            //医院搜索
            $where['hospital_id'] = $hospital_id;
        }else{
            $where['hospital_id'] = session('current_hospitalid');
        }
        $where['is_delete']=['NEQ',C('YES_STATUS')];
        $total = $this->DB_get_count('dic_parts', $where);
        $data = $this->DB_get_all('dic_parts', '*', $where, '', $sort . ' ' . $order, $offset . ',' . $limit);
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $deleDic = get_menu($this->MODULE, $this->Controller, 'delPartsDic');
        $editDic = get_menu($this->MODULE, $this->Controller, 'editPartsDic');
        foreach ($data as &$value) {
            $value['operation'] = '<div class="layui-btn-group">';
            if ($editDic) {
                $value['operation'] .= $this->returnListLink('修改', $editDic['actionurl'], 'edit', C('BTN_CURRENCY').' layui-btn-normal');;
            }
            if ($deleDic) {
                $value['operation'] .= $this->returnListLink('删除', $deleDic['actionurl'], 'delete', C('BTN_CURRENCY').' layui-btn-danger');;
            }
            $value['operation']  .= '</div>';
        }

        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $data;
        $result['code'] = 200;

        return $result;
    }

    //新增配件字典
    public function addPartsDic(){
        $menuData = get_menu($this->MODULE, $this->Controller, 'addPartsDic');
        if (!$menuData) {
            return array('status' => -1, 'msg' => '无权限新增字典');
        }
        $data['hospital_id'] = session('current_hospitalid');
        $this->checkstatus(judgeEmpty(trim(I('POST.parts'))), '请补充配件字典名称');
        $data['parts'] = trim(I('post.parts'));

        $data['supplier_name']=trim(I('POST.supplier_name'));
        $data['supplier_id']=trim(I('POST.supplier_id'));

        $data['unit']=trim(I('POST.unit'));
        $data['brand']=trim(I('POST.brand'));
        $price=trim(I('POST.price'));
        if($price){
            $this->checkstatus(judgeEmpty($price), '请输入正确的金额');
        }
        $data['price']=$price;
        $data['parts_model'] = trim(I('post.parts_model'));
        $data['dic_category'] = trim(I('post.dic_category'));
        $data['remark'] = trim(I('post.remark'));
        $data['adduser'] = session('username');
        $data['addtime'] = date('Y-m-d H:i:s');
        $add = $this->insertData('dic_parts', $data);
        if ($add) {
            $log['parts'] = $data['parts'];
            $log['parts_model'] = $data['parts_model'];
            $text = getLogText('addPartsDic', $log);
            $this->addLog('dic_parts', M()->getLastSql(), $text, $add);
            return array('status' => 1, 'msg' => '添加配件字典成功！');
        } else {
            return array('status' => -1, 'msg' => '添加配件字典失败！');
        }
    }

    //修改配件字典
    public function editPartsDic()
    {
        $data['dic_partsid']= trim(I('post.dic_partsid'));
        $data['parts'] = trim(I('post.parts'));
        $data['parts_model'] = trim(I('post.parts_model'));

        if(!$data['parts']){
            return array('status'=>-1,'msg'=>'配件名称不能为空！');
        }

        $price=trim(I('POST.price'));
        if($price){
            $this->checkstatus(judgeEmpty($price), '请输入正确的金额');
        }
        $data['price']=$price;

        $data['supplier_name']=trim(I('POST.supplier_name'));
        $data['supplier_id']=trim(I('POST.supplier_id'));
//
//        $where['status']=['NEQ',C('DELETE_STATUS')];
//        $where['dic_partsid']=['NEQ',$data['dic_partsid']];
//        $where['parts']=['EQ',$data['parts']];
//        $where['parts_model']=['EQ',$data['parts_model']];
//        $where['price']=['EQ',$data['price']];
//        $where['supplier_id']=['EQ',$data['supplier_id']];
//        $where['brand']=['EQ',$data['brand']];
//
//        $check=$this->DB_get_one('dic_parts','',$where);
//        if($check){
//            return array('status' => -1, 'msg' => '已存在 配件名称 '.$data['parts'].' 配件型号'.$data['parts_model'].' 的配件');
//        }
        $data['unit']=trim(I('POST.unit'));
        $data['brand']=trim(I('POST.brand'));
        $data['remark'] = trim(I('post.remark'));
        $data['dic_category'] = trim(I('post.dic_category'));
        $data['edituser'] = session('username');
        $data['edittime'] = date('Y-m-d H:i:s');
        $data['status'] = trim(I('post.status'));
        $save = $this->updateData('dic_parts',$data,array('dic_partsid'=>$data['dic_partsid']));
        if($save){
            $log['dic_partsid'] = $data['dic_partsid'];
            $text = getLogText('savePartsDic', $log);
            $this->addLog('dic_parts', M()->getLastSql(), $text, $data['dic_partsid']);
            return array('status'=>1,'msg'=>'修改配件字典成功！');
        }else{
            return array('status'=>-1,'msg'=>'修改配件字典失败！');
        }
    }

    //删除配件字典
    public function delPartsDic(){
        $dic_partsid = I('post.dic_partsid');
        //查当前信息所对应的基本信息
        $parts = $this->DB_get_one('dic_parts', '', array('dic_partsid' => $dic_partsid));
        if (!$parts) {
            return array('status'=>-1,'msg'=>'该配件字典不存在！');
        }
        if($parts['is_delete']==C('YES_STATUS')){
            return array('status'=>-1,'msg'=>'该配件字典已删除！');
        }
        $data['edittime'] = date('Y-m-d H:i:s');
        $data['is_delete'] = C('YES_STATUS');
        $del=$this->deleteData('dic_parts',array('dic_partsid' => $dic_partsid));
        if($del){
            $log['parts'] = $parts['parts'];
            $text = getLogText('delPartsDic', $log);
            $this->addLog('dic_parts', M()->getLastSql(), $text, $data['dic_partsid']);
            return array('status'=>1,'msg'=>'删除配件字典成功！');
        }else{
            return array('status'=>-99,'msg'=>'删除配件字典失败！');
        }
    }

    public function getTmpDic()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'asc';
        $sort = I('post.sort') ? I('post.sort') : 'addtime';
        $hospital_id = session('current_hospitalid');
        $where['is_save'] = 0;
        //查询原有设备字典
        $old_data = $this->DB_get_all('dic_assets','assets',array('hospital_id'=>$hospital_id,'status'=>1));
        $old_assets = [];
        foreach($old_data as $v){
            $old_assets[] = $v['assets'];
        }
        //查询设备分类
        $cates = $this->DB_get_all('category','category,catenum',array('hospital_id'=>$hospital_id,'is_delete'=>0));
        $category = $catenum = array();
        foreach($cates as $k=>$v){
            $category[] = $v['category'];
            $catenum[] = $v['catenum'];
        }
        $where['hospital_id'] = $hospital_id;
        $total = $this->DB_get_count('dic_assets_upload_temp',$where);
        $dics = $this->DB_get_all('dic_assets_upload_temp','*',$where,'',$sort . ' ' . $order, $offset . ',' . $limit);
        $arr = array();
        foreach ($dics as $k=>$v){
            $arr[$k]['tempid'] = $v['tempid'];
            $arr[$k]['assets'] = $v['assets'];
            $arr[$k]['category'] = $v['category'];
            $arr[$k]['dic_category'] = $v['dic_category'];
            $arr[$k]['assets_category'] = $v['assets_category'];
            $arr[$k]['unit'] = $v['unit'];
            //判断字典名称是否存在
            if($v['assets']){
                if(in_array($v['assets'],$old_assets)){
                    $arr[$k]['assets'] = '<span style="color:red;">'.$v['assets'].'</span>';
                }
            }else{
                $arr[$k]['assets'] = '<span style="color:red;">设备名称不能为空</span>';
            }
            if($v['category']){
                //判断分类名称是否存在
                if(!in_array($v['category'],$category)){
                    $arr[$k]['category'] = '<span style="color:red;">'.$v['category'].'</span>';
                }
            }else{
                $arr[$k]['category'] = '<span style="color:red;">设备分类不能为空</span>';
            }
            $arr[$k]['operation'] = '<button class="layui-btn layui-btn-sm layui-btn-danger" lay-event="delDic">删除</button>';
        }
        $result['total'] = $total;
        $result["offset"] = 0;
        $result["limit"] = 500;
        $result["code"] = 200;
        $result['rows'] = $arr;
        if(!$result['rows']){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 更新临时表数据
     */
    public function updateTempData()
    {
        $tempid = I('POST.tempid');
        $assets = $_POST['assets'];
        $category = $_POST['category'];
        $dic_category = $_POST['dic_category'];
        $assets_category = $_POST['assets_category'];
        $unit = $_POST['unit'];
        if(isset($assets)){
            if(!$assets){
                return array('status'=>-1,'msg'=>'设备名称不能为空！');
            }else{
                $assets_name = $this->DB_get_one('dic_assets_upload_temp','assets',array('assets'=>$assets,'tempid'=>array('neq',$tempid)));
                if ($assets == $assets_name['assets']){
                    return array('status'=>-1,'msg'=>$assets_name['assets'].'设备名称已存在！');
                }
                $this->updateData('dic_assets_upload_temp',array('assets'=>$assets),array('tempid'=>$tempid));
            }
        }
        if(isset($category)){
            $this->updateData('dic_assets_upload_temp',array('category'=>$category),array('tempid'=>$tempid));
            return array('status'=>1,'msg'=>'修改成功！','newdata'=>$category);
        }
        if(isset($dic_category)){
            $this->updateData('dic_assets_upload_temp',array('dic_category'=>$dic_category),array('tempid'=>$tempid));
        }
        if(isset($unit)){
            $this->updateData('dic_assets_upload_temp',array('unit'=>$unit),array('tempid'=>$tempid));
        }
        if(isset($assets_category)){
            $this->updateData('dic_assets_upload_temp',array('assets_category'=>$assets_category),array('tempid'=>$tempid));
        }
        return array('status'=>1,'msg'=>'修改成功！');
    }

    /**
     * Notes: 删除设备临时表数据
     */
    public function delTempData()
    {
        $tempid = trim(I('POST.tempid'),',');
        $tempArr = explode(',',$tempid);
        $where['tempid'] = array('in',$tempArr);
        $res = $this->deleteData('dic_assets_upload_temp',$where);
        if($res){
            return array('status'=>1,'msg'=>'删除成功！');
        }else{
            return array('status'=>-1,'msg'=>'删除失败！');
        }
    }

    /**
     * Notes: 接收上传的excel数据
     */
    public function uploadData()
    {
        if (empty($_FILES)) {
            return array('status' => -1, 'msg' => '请上传文件');
        }
        $uploadConfig = array(
            'maxSize' => 3145728,
            'rootPath' => './Public/',
            'savePath' => 'uploads/',
            'saveName' => array('uniqid', ''),
            'exts' => array('xlsx', 'xls', 'xlsm'),
            'autoSub' => true,
            'subName' => array('date', 'Ymd'),
        );
        $upload = new \Think\Upload($uploadConfig);
        $info = $upload->upload();
        if (!$info) {
            return array('status' => -1, 'msg' => '导入数据出错');
        }
        vendor("PHPExcel.PHPExcel");
        $filePath = $upload->rootPath . $info['file']['savepath'] . $info['file']['savename'];
        if (empty($filePath) or !file_exists($filePath)) {
            die('file not exists');
        }
        $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                return array('status' => -1, 'msg' => '文件格式错误');
            }
        }
        $excelDate = new \PHPExcel_Shared_Date();
        $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
        $currentSheet = $PHPExcel->getSheet(0);        //**读取excel文件中的指定工作表*/
        $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
        ++$allColumn;
        $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
        $data = array();
        $cellname = array(
            'A' => 'assets',
            'B' => 'category',
            'C' => 'dic_category',
            'D' => 'unit',
            'E' => 'assets_category'
        );
        for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for ($colIndex = 'A'; $colIndex != $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if ($cell instanceof \PHPExcel_RichText) { //富文本转换字符串
                    $cell = $cell->__toString();
                }
//                if($cellname[$colIndex] == 'assets'){
//                    if(!$cell){
//                        continue;
//                    }
//                }
//                if($cellname[$colIndex] == 'category'){
//                    if(!$cell){
//                        continue;
//                    }
//                }
                $data[$rowIndex - 2][$cellname[$colIndex]] = trim($cell) ? trim($cell) : '';
            }
        }
        if (!$data) {
            return array('status' => -1, 'msg' => '导入数据失败');
        }
        $hospital_id = session('current_hospitalid');
        $assets = $this->DB_get_all('dic_assets_upload_temp','assets',array('hospital_id'=>$hospital_id));
        $old_assets = [];
        foreach ($assets as $k=>$v){
            $old_assets[] = $v['assets'];
        }
        //对数据进行重复性验证
        foreach ($data as $k=>$v){
            foreach ($v as $k1=>$v1){
                if($k1 == 'assets'){
                    if(in_array($v1,$old_assets)){
                        unset($data[$k]);
                        break;
                    }
                }
            }
        }
        if(!$data){
            //上传的文件数据和临时表中已存在数据重复
            return array('status'=>-1,'msg'=>'没有新数据被上传！请检查文件数据是否已上传过，或是否符合要求!');
        }
        //保存数据到临时表
        $insertData = array();
        $num = 0;
        foreach ($data as $k=>$v){
            //if($v['assets'] && $v['category']){
                if($num < $this->len){
                    //$this->len条存一次数据到数据库
                    $tempid = getRandomId();
                    $insertData[$num]['tempid'] = $tempid;
                    $insertData[$num]['hospital_id'] = $hospital_id;
                    $insertData[$num]['adduser'] = session('username');
                    $insertData[$num]['addtime'] = date('Y-m-d H:i:s');
                    $insertData[$num]['is_save'] = 0;
                    foreach ($v as $k1=>$v1){
                        $insertData[$num][$k1] = $v1;
                    }
                    $num++;
                }

                if($num == $this->len){
                    //插入数据
                    $this->insertDataALL('dic_assets_upload_temp',$insertData);
                    //重置数据
                    $num = 0;
                    $insertData = array();
                }
            //}
        }
        if($insertData){
            $this->insertDataALL('dic_assets_upload_temp',$insertData);
        }
        return array('status'=>1,'msg'=>'上传数据成功，请核准后再保存！');
    }

    /**
     * Notes: 批量添加设备字典
     * @return array
     */
    public function batchAddDic()
    {
        $hospital_id = session('current_hospitalid');
        $tempid = trim(I('POST.tempid'),',');
        $tempArr = explode(',',$tempid);
        //查询原有设备字典
        $old_data = $this->DB_get_all('dic_assets','assets',array('hospital_id'=>$hospital_id,'status'=>1));
        $old_assets = [];
        foreach($old_data as $v){
            $old_assets[] = $v['assets'];
        }
        //获取临时数据
        $tempdata = $this->DB_get_all('dic_assets_upload_temp','*',array('tempid'=>array('in',$tempid)));
        //查询所有分类
        $cates = $this->DB_get_all('category','catid,category',array('hospital_id'=>$hospital_id,'is_delete'=>0));
        $category = $catids = array();
        foreach($cates as $k=>$v){
            $category[] = $v['category'];
            $catids[$v['category']] = $v['catid'];
        }
        $as_cate = array(
            '急救设备'=>'is_firstaid',
            '特种设备'=>'is_special',
            '计量设备'=>'is_metering',
            '质控设备'=>'is_qualityAssets',
            '效益分析'=>'is_benefit',
            '生命支持'=>'is_lifesupport',
        );
        $num = 0;
        $saveTempidArr = $savetempid = $not_save = array();
        foreach($tempdata as $k=>$v){
            if(!$v['assets']){
                continue;
            }
            if(!$v['category']){
                continue;
            }
            if(!in_array($v['category'],$category)){
                continue;
            }
            if(in_array($v['assets'],$old_assets)){
                $not_save[] = $v['assets'];
            }else{
                //按每次最多不超过$this->len条的数据获取临时表数据进行保存操作
                if($num < $this->len){
                    $savetempid[] = $v['tempid'];
                    $saveTempidArr[$num]['hospital_id'] = $hospital_id;
                    $saveTempidArr[$num]['assets'] = $v['assets'];
                    $old_assets[] = $v['assets'];
                    $saveTempidArr[$num]['catid'] = $catids[$v['category']];
                    $saveTempidArr[$num]['dic_category'] = $v['dic_category'];
                    $assets_category = explode('|',$v['assets_category']);
                    $real_as_cat = array();
                    foreach($assets_category as $k1=>$v1){
                        if($as_cate[$v1]){
                            $real_as_cat[] = $as_cate[$v1];
                        }
                    }
                    $saveTempidArr[$num]['assets_category'] = implode(',',$real_as_cat);
                    $saveTempidArr[$num]['unit'] = $v['unit'];
                    $saveTempidArr[$num]['adduser'] = session('username');
                    $saveTempidArr[$num]['addtime'] = date('Y-m-d H:i:s');
                    $num++;
                }
            }
            if($num == $this->len){
                //进行一次设备入库操作
                $res = $this->insertDataALL('dic_assets',$saveTempidArr);
                $this->updateData('dic_assets_upload_temp',array('is_save'=>1),array('tempid'=>array('in',$savetempid)));
                //重置
                $num = 0;
                $saveTempidArr = array();
            }
        }
        if($saveTempidArr){
            $res = $this->insertDataALL('dic_assets',$saveTempidArr);
            $this->updateData('dic_assets_upload_temp',array('is_save'=>1),array('tempid'=>array('in',$savetempid)));
        }
        $not_save = implode(',',$not_save);
        $msg = $res ? '保存数据成功！' : '暂无数据保存！';
        $msg = $not_save ? $msg.'不能重复添加系统已存在的设备字典（'.$not_save.'）！' : $msg;
        return array('status'=>1,'msg'=>$msg);
    }
}