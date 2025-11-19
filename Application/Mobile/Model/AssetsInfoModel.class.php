<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2019/3/6
 * Time: 10:28
 */

namespace Mobile\Model;
use Admin\Controller\Tool\ToolController;

class AssetsInfoModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'assets_info';
    private $MODULE = 'Assets';

    /*
     * 获取分类数据
     */
    public function get_all_category($type = "")
    {
        switch ($type) {
            case 'transfer':
                $data = $this->DB_get_all('category','catid,parentid,category',array('is_delete'=>0,'hospital_id'=>session('current_hospitalid')));
                $as_data = $this->DB_get_all('assets_info','count(assid) as num,catid',array('is_delete'=>0,'hospital_id'=>session('current_hospitalid'),'status'=>array('IN',''.C('ASSETS_STATUS_USE').','.C('ASSETS_STATUS_TRANSFER_ON').''),'departid'=>array('IN',session('departid'))),'catid');
                break;
            case 'scrap':
                $data = $this->DB_get_all('category','catid,parentid,category',array('is_delete'=>0,'hospital_id'=>session('current_hospitalid')));
                $as_data = $this->DB_get_all('assets_info','count(assid) as num,catid',array('is_delete'=>0,'hospital_id'=>session('current_hospitalid'),'is_subsidiary'=>C('NO_STATUS'),'status'=>array('IN',''.C('ASSETS_STATUS_USE').','.C('ASSETS_STATUS_SCRAP_ON').''),'departid'=>array('IN',session('departid'))),'catid');
                break;
            case 'borrow':
                $data = $this->DB_get_all('category','catid,parentid,category',array('is_delete'=>0,'hospital_id'=>session('current_hospitalid')));
                $notwhere['status'] = array('not in',[3,4]);
                $notarr = $this->DB_get_all('assets_borrow','assid',$notwhere);
                $notid = "";
                foreach ($notarr as $k=>$v){
                        $notid .= $v['assid'].',';
                }
                $notid .= '0';
                $as_data = $this->DB_get_all('assets_info','count(assid) as num,catid',array('is_delete'=>0,'hospital_id'=>session('current_hospitalid'),'assid'=>array('NOT IN',$notid),'status'=>array('IN',''.C('ASSETS_STATUS_USE').''),'is_subsidiary'=>C('NO_STATUS'),'departid'=>array('IN',session('departid'))),'catid');
                break;
            case 'Print':
                $data = $this->DB_get_all('category','catid,parentid,category',array('is_delete'=>0,'hospital_id'=>session('current_hospitalid')));
                $as_data = $this->DB_get_all('assets_info','count(assid) as num,catid',array('is_delete'=>0,'print_status'=>0,'hospital_id'=>session('current_hospitalid'),'status'=>array('NOT IN',''.C('ASSETS_STATUS_SCRAP').''),'departid'=>array('IN',session('departid'))),'catid');
                break;
            default:
                $data = $this->DB_get_all('category','catid,parentid,category',array('is_delete'=>0,'hospital_id'=>session('current_hospitalid')));
                $as_data = $this->DB_get_all('assets_info','count(assid) as num,catid',array('is_delete'=>0,'hospital_id'=>session('current_hospitalid'),'status'=>array('NOT IN',''.C('ASSETS_STATUS_OUTSIDE').''),'departid'=>array('IN',session('departid'))),'catid');
                break;
        }
        $assetssum = [];
        foreach ($as_data as $key => $value) {
            $assetssum[$value['catid']] = $value['num'];
        }
        $parentsum = [];
        foreach ($data as $key => $value) {
            if ($value['parentid']=='0') {
                $parentsum[$value['catid']] = $key;
            }
        }
        foreach ($data as $key => $value) {
            $data[$key]['assetssum'] = $assetssum[$value['catid']]?$assetssum[$value['catid']]:0;
            if ($value['parentid']!='0'&&$data[$key]['assetssum']!='0') {
                $data[$parentsum[$value['parentid']]]['assetssum']+=$data[$key]['assetssum'];
            }
        }
        return $data;
    }

    public function get_all_department($departids = '',$status = '',$is_subsidiary = "",$print_status = "")
    {
        if($departids){
            $where['is_delete'] = 0;
            $where['hospital_id'] = session('current_hospitalid');
            $where['departid'] = array('in',$departids);
        }else{
            $where['is_delete'] = 0;
            $where['hospital_id'] = session('current_hospitalid');
        }
        $data = $this->DB_get_all('department','departid,department,parentid',$where);
        $where = [];
        if (is_int($is_subsidiary)) {
            $where['is_subsidiary'] = $is_subsidiary;
        }
        if (is_int($print_status)) {
            $where['print_status'] = $print_status;
        }
        $where['is_delete'] = 0;
        $where['hospital_id'] = session('current_hospitalid');
        $where['departid'] = array('IN',session('departid'));
        if ($status) {
            $where['status'] = array('IN',$status);
            $as_data = $this->DB_get_all('assets_info','count(assid) as num,departid',$where,'departid');
        }else{
            $where['status'] = array('NOT IN',''.C('ASSETS_STATUS_OUTSIDE').'');
            $as_data = $this->DB_get_all('assets_info','count(assid) as num,departid',$where,'departid');
        }
        $assetssum = [];
        foreach ($as_data as $key => $value) {
            $assetssum[$value['departid']] = $value['num'];
        }

        foreach ($data as $key => $value) {
            $data[$key]['assetssum'] = $assetssum[$value['departid']]?$assetssum[$value['departid']]:0;
        }
        return $data;
    }
    //获取需要核实的设备
    public function get_verify_lists()
    {
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $limit = I('post.limit') ? I('post.limit') : C('PAGE_NUMS');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $search = I('POST.search');
        $catid = I('POST.catid');
        $departid = I('POST.departid');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $where['A.departid'] = array('in', $departids);
        $where['A.status'][0] = 'NOT IN';
        $where['A.status'][1][] = C('ASSETS_STATUS_SCRAP');
//        $where['A.is_subsidiary'] = C('NO_STATUS');
        if ($hospital_id) {
            $where['A.hospital_id'] = $hospital_id;
        } else {
            $where['A.hospital_id'] = session('current_hospitalid');
        }
        if (!$sort) {
            $sort = 'assid';
        }
        if (!$order) {
            $order = 'DESC';
        }
        if($search){
            switch ($search){
                case '在用':
                    $where['A.status'] = 0;
                    break;
                case '维修':
                    $where['A.status'] = 1;
                    break;
                case '维修中':
                    $where['A.status'] = 1;
                    break;
                case '已报废':
                    $where['A.status'] = 2;
                    break;
                case '报废中':
                    $where['A.status'] = 5;
                    break;
                case '报废':
                    $where['A.status'] = array('in',[2,5]);
                    break;
                case '已外调':
                    $where['A.status'] = 3;
                    break;
                case '外调中':
                    $where['A.status'] = 4;
                    break;
                case '外调':
                    $where['A.status'] = array('in',[3,4]);
                    break;
                case '转科':
                    $where['A.status'] = 6;
                    break;
                case '转科中':
                    $where['A.status'] = 6;
                    break;
                default:
                    $map['A.assets'] = array('like','%' . $search. '%');
                    $map['A.assnum'] = array('like', '%' . $search . '%');
                    $map['A.model'] = array('like','%' . $search. '%');
                    $map['A.brand'] = array('like','%' . $search. '%');
                    $map['_logic'] = 'or';
                    $where['_complex'] = $map;
            }

        }
        if ($catid) {
            //查询是否父分类
            $parentcat = $this->DB_get_one('category','parentid,catid',array('catid'=>$catid));
            if($parentcat['parentid'] != 0){
                $where['A.catid'] = $catid;
            }else{
                //查询子类
                $allcatid = $this->DB_get_one('category','group_concat(catid) as catids',array('parentid'=>$parentcat['catid']));
                if($allcatid['catids']){
                    $allcatid['catids'].=','.$catid;
                    $where['A.catid'] = array('in',$allcatid['catids']);
                }else{
                    $where['A.catid'] = $catid;
                }
            }
        }
        if ($departid) {
            $where['A.departid'] = $departid;
        }
        $where['A.is_delete'] = '0';
        $where['A.print_status'] = '0';
        $fields = "A.assid,A.assets,A.assnum,A.catid,A.departid,A.model,A.status,A.brand,A.pic_url,B.department,B.assetssum";
        $join = "LEFT JOIN sb_department AS B ON A.departid = B.departid";
        $total = $this->DB_get_count_join('assets_info', 'A',$join,$where,'');
        $assets = $this->DB_get_all_join('assets_info','A',$fields,$join,$where,'',$sort . ' ' . $order, $offset . "," . $limit);
        if(!$assets){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        foreach ($assets as &$v){
            $v['operation'] = $this->returnMobileLink('核实', C('MOBILE_NAME').'/Print/verify?action=labelCheck&from=jumpButton&assid='.$v['assid'], ' layui-btn layui-btn-normal');
            switch ($v['status']) {
                case C('ASSETS_STATUS_USE'):
                    $v['status_name'] = C('ASSETS_STATUS_USE_NAME');
                    $v['status_name'] = '<span style="color:#009688;">'.$v['status_name'].'</span>';
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $v['status_name'] = C('ASSETS_STATUS_REPAIR_NAME');
                    $v['status_name'] = '<span style="color:red;">'.$v['status_name'].'</span>';
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $v['status_name'] = C('ASSETS_STATUS_SCRAP_NAME');
                    $v['status_name'] = '<span style="color:#FFB800;">'.$v['status_name'].'</span>';
                    break;
                case C('ASSETS_STATUS_OUTSIDE'):
                    $v['status_name'] = C('ASSETS_STATUS_OUTSIDE_NAME');
                    break;
                case C('ASSETS_STATUS_OUTSIDE_ON'):
                    $v['status_name'] = C('ASSETS_STATUS_OUTSIDE_ON_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP_ON'):
                    $v['status_name'] = C('ASSETS_STATUS_SCRAP_ON_NAME');
                    break;
                case C('ASSETS_STATUS_TRANSFER_ON'):
                    $v['status_name'] = C('ASSETS_STATUS_TRANSFER_ON_NAME');
                    $v['status_name'] = '<span style="color:#1E9FFF;">'.$v['status_name'].'</span>';
                    break;
                case 7:
                    $v['status_name'] = '质控中';
                    break;
                case 8:
                    $v['status_name'] = '巡查中';
                    break;
                default:
                    $v['status_name'] = '未知状态';
                    break;
            }
        }
        $result['page']  = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result['rows']  = $assets;
        $result['code']  = 200;
        return $result;
    }

        //从微信下载图片并存于本地
    public function uploadReport()
    {
        $wxModel = new WxAccessTokenModel();
        $access_token = $wxModel->getAccessToken();
        $assid = I('post.assid');
        $type = I('post.type');
        $mdi = I('post.mid');
        $Tool = new ToolController();
        $style = array('JPG', 'PNG', 'JPEG', 'PDF', 'BMP', 'DOC', 'DOCX', 'jpg', 'png', 'jpeg', 'pdf', 'bmp', 'doc', 'docx');
        $dirName = C('UPLOAD_DIR_ASSETS_NAME') . '/' . date('Ymd');
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=" . $access_token . "&media_id=" . $mdi;
        mkdir('./Public/uploads/' . $dirName, 0777);
        chmod('./Public/uploads/' . $dirName, 0777);
        $raw = file_get_contents($url);
        $file_path = './Public/uploads/' . $dirName . '/' . $mdi . '.jpg';
        file_put_contents($file_path, $raw);
        if (file_exists($file_path)) {
            $path = date('Ymd') . '/' . $mdi . '.jpg';
            $assets_data = $this->DB_get_one('assets_info','pic_url', array('assid' => $assid));
            if ($assets_data['pic_url']) {
                $data['pic_url'] = $assets_data['pic_url'].','.$path;
            }else{
                $data['pic_url'] = $path;
            }
            $data['print_status'] = $type;
            $res = $this->updateData('assets_info', $data, array('assid' => $assid));
             if ($res) {
                return array('status' => 1, 'msg' => '上传成功！', 'path' => '/Public/uploads/' . $dirName . '/' . $mdi . '.jpg');
            } else {
                return array('status' => -1, 'msg' => '上传失败！');

        }} else {
            return array('status' => -1, 'msg' => '上传失败！');
        }

    }
    public function get_assets_lists()
    {
        $departids = session('departid');
        $hospital_id = session('current_hospitalid');
        $limit = I('post.limit') ? I('post.limit') : C('PAGE_NUMS');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order');
        $sort = I('post.sort');
        $search = I('POST.search');
        $catid = I('POST.catid');
        $departid = I('POST.departid');
        if (!$departids) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $where['A.departid'] = array('in', $departids);
        $where['A.status'][0] = 'NOT IN';
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE');//已外调
        $where['A.status'][1][] = C('ASSETS_STATUS_OUTSIDE_ON');//外调中
//        $where['A.is_subsidiary'] = C('NO_STATUS');
        if ($hospital_id) {
            $where['A.hospital_id'] = $hospital_id;
        } else {
            $where['A.hospital_id'] = session('current_hospitalid');
        }
        if (!$sort) {
            $sort = 'assid';
        }
        if (!$order) {
            $order = 'DESC';
        }
        if($search){
            switch ($search){
                case '在用':
                    $where['A.status'] = 0;
                    break;
                case '维修':
                    $where['A.status'] = 1;
                    break;
                case '维修中':
                    $where['A.status'] = 1;
                    break;
                case '已报废':
                    $where['A.status'] = 2;
                    break;
                case '报废中':
                    $where['A.status'] = 5;
                    break;
                case '报废':
                    $where['A.status'] = array('in',[2,5]);
                    break;
                case '已外调':
                    $where['A.status'] = 3;
                    break;
                case '外调中':
                    $where['A.status'] = 4;
                    break;
                case '外调':
                    $where['A.status'] = array('in',[3,4]);
                    break;
                case '转科':
                    $where['A.status'] = 6;
                    break;
                case '转科中':
                    $where['A.status'] = 6;
                    break;
                default:
                    $map['A.assets'] = array('like','%' . $search. '%');
                    $map['A.assnum'] = array('like', '%' . $search . '%');
                    $map['A.model'] = array('like','%' . $search. '%');
                    $map['A.brand'] = array('like','%' . $search. '%');
                    $map['_logic'] = 'or';
                    $where['_complex'] = $map;
            }

        }
        if ($catid) {
            //查询是否父分类
            $parentcat = $this->DB_get_one('category','parentid,catid',array('catid'=>$catid));
            if($parentcat['parentid'] != 0){
                $where['A.catid'] = $catid;
            }else{
                //查询子类
                $allcatid = $this->DB_get_one('category','group_concat(catid) as catids',array('parentid'=>$parentcat['catid']));
                if($allcatid['catids']){
                    $allcatid['catids'].=','.$catid;
                    $where['A.catid'] = array('in',$allcatid['catids']);
                }else{
                    $where['A.catid'] = $catid;
                }
            }
        }
        if ($departid) {
            $where['A.departid'] = $departid;
        }
        $where['A.is_delete'] = '0';
        $fields = "A.assid,A.assets,A.assnum,A.catid,A.departid,A.model,A.status,A.brand,A.pic_url,B.department,B.assetssum";
        $join = "LEFT JOIN sb_department AS B ON A.departid = B.departid";
        $total = $this->DB_get_count_join('assets_info', 'A',$join,$where,'');
        $assets = $this->DB_get_all_join('assets_info','A',$fields,$join,$where,'',$sort . ' ' . $order, $offset . "," . $limit);
        if(!$assets){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            $result['total'] = 0;
            return $result;
        }
        $catname = [];
        include APP_PATH . "Common/cache/category.cache.php";
        foreach ($assets as &$v){
            if($v['pic_url']){
                $pic_url = explode(',',$v['pic_url']);
                $picarr = [];
                foreach ($pic_url as $k1=>$v1){
                    $v1 = str_replace('/Public/uploads/'.C('UPLOAD_DIR_ASSETS_NAME').'/','',$v1);
                    $picarr[] = '/Public/uploads/'.C('UPLOAD_DIR_ASSETS_NAME').'/'.$v1;
                }
                $v['pic_url'] = $picarr;
            }
            $v['category'] = $catname[$v['catid']]['category'];
            switch ($v['status']) {
                case C('ASSETS_STATUS_USE'):
                    $v['status_name'] = C('ASSETS_STATUS_USE_NAME');
                    $v['status_name'] = '<span style="color:#009688;">'.$v['status_name'].'</span>';
                    break;
                case C('ASSETS_STATUS_REPAIR'):
                    $v['status_name'] = C('ASSETS_STATUS_REPAIR_NAME');
                    $v['status_name'] = '<span style="color:red;">'.$v['status_name'].'</span>';
                    break;
                case C('ASSETS_STATUS_SCRAP'):
                    $v['status_name'] = C('ASSETS_STATUS_SCRAP_NAME');
                    $v['status_name'] = '<span style="color:#FFB800;">'.$v['status_name'].'</span>';
                    break;
                case C('ASSETS_STATUS_OUTSIDE'):
                    $v['status_name'] = C('ASSETS_STATUS_OUTSIDE_NAME');
                    break;
                case C('ASSETS_STATUS_OUTSIDE_ON'):
                    $v['status_name'] = C('ASSETS_STATUS_OUTSIDE_ON_NAME');
                    break;
                case C('ASSETS_STATUS_SCRAP_ON'):
                    $v['status_name'] = C('ASSETS_STATUS_SCRAP_ON_NAME');
                    break;
                case C('ASSETS_STATUS_TRANSFER_ON'):
                    $v['status_name'] = C('ASSETS_STATUS_TRANSFER_ON_NAME');
                    $v['status_name'] = '<span style="color:#1E9FFF;">'.$v['status_name'].'</span>';
                    break;
                case 7:
                    $v['status_name'] = '质控中';
                    break;
                case 8:
                    $v['status_name'] = '巡查中';
                    break;
                default:
                    $v['status_name'] = '未知状态';
                    break;
            }
        }
        $result['page']  = (int)$page;
        $result['pages'] = (int)ceil($total / C('PAGE_NUMS'));
        $result['total'] = $total;
        $result['rows']  = $assets;
        $result['code']  = 200;
        return $result;
    }
}
