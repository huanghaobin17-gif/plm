<?php
namespace Admin\Model;
use Think\Model;
class ArchivesModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'archives_emergency_plan';
    private $MODULE = 'Archives';

    /**
     * Notes: 获取预案列表
     */
    public function get_emergency_lists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'arempid';
        $emergency = I('post.name');
        $keyword = I('post.keyword');
        $dic_category = I('post.category');
        $userid = I('post.userid');
        $where['hospital_id'] = session('current_hospitalid');
        $where['is_delete'] = ['EQ', C('NO_STATUS')];
        if ($emergency) {
            //关键字
            $where['emergency'] = array('like', "%$emergency%");
        }
        if ($keyword) {
            //关键字
            $where['content'] = array('like', "%$keyword%");
        }
        if ($dic_category) {
            //预案分类
            $where['category'] = array('in',$dic_category);
        }
        if ($userid) {
            //添加者
            $where['add_userid'] = array('in',$userid);
        }
        $total = $this->DB_get_count('archives_emergency_plan', $where);
        $data = $this->DB_get_all('archives_emergency_plan', '*', $where, '', $sort . ' ' . $order, $offset . ',' . $limit);
        //查询当前用户是否有权限修改预案
        $editEmer = get_menu($this->MODULE, 'Emergency', 'editEmergency');
        //查询当前用户是否有权限进行删除预案
        $delEmer = get_menu($this->MODULE, 'Emergency', 'delEmergency');
        //查询分类名称
        $cates = $this->DB_get_all('archives_emergency_category','*',array('1'));
        $catename = $username = [];
        foreach($cates as $k=>$v){
            $catename[$v['id']] = $v['name'];
        }
        //添加者
        //查询分类名称
        $users = $this->DB_get_all('user','userid,username',array('status'=>1,'is_delete'=>0,'hospital_id'=>session('current_hospitalid')));
        foreach($users as $k=>$v){
            $username[$v['userid']] = $v['username'];
        }
        $ids = [];
        foreach ($data as $k => $v) {
            $data[$k]['files'] = '';
            $ids[] = $v['arempid'];
            $html = '<div class="layui-btn-group">';
            $html .= $this->returnListLink('查看', get_url() . '?action=showEmer&arempid=' . $v['arempid'], 'showEmer', C('BTN_CURRENCY') . ' layui-btn-primary');
            if ($editEmer) {
                $html .= $this->returnListLink('编辑', $editEmer['actionurl'], 'edit', C('BTN_CURRENCY') . ' ');
            }
            if ($delEmer) {
                $html .= $this->returnListLink('删除', $delEmer['actionurl'], 'delete', C('BTN_CURRENCY') . ' layui-btn-danger');
            }
            $html .= '</div>';
            $data[$k]['category'] = $catename[$v['category']];
            $data[$k]['add_user'] = $username[$v['add_userid']];
            $data[$k]['operation'] = $html;
        }
        if($ids){
            //查询对应的文件
            $files = $this->DB_get_all('archives_emergency_plan_file','arempid,file_name,file_url,file_type',array('arempid'=>array('in',$ids),'is_delete'=>0));
            foreach ($files as $k=>$v){
                switch($v['file_type']){
                    case 'pdf':
                        $pic = '<img src="/Public/images/pdf.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                        break;
                    case 'doc':
                    case 'docx':
                        $pic = '<img src="/Public/images/word.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                        break;
                    case 'txt':
                        $pic = '<img src="/Public/images/text.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                        break;
                    default:
                        $pic = '<img src="/Public/images/word.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                        break;
                }
                foreach ($data as $kk => $vv) {
                    if($v['arempid'] == $vv['arempid']){
                        $data[$kk]['files'] .= $pic.'<span class="tdfile showFile" data-path="'.urlencode($v['file_url']).'" data-name="'.$v['file_name'].'">'.$v['file_name'].'</span>';
                    }
                }
            }
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
     * Notes: 添加预案分类
     */
    public function add_category()
    {
        $repeat = $this->DB_get_all('archives_emergency_category','name',array('1'));
        if(I('post.title')){
            $title = array_filter(explode(',',trim(I('post.title'))));
        }else{
            die(json_encode(array('status' => -1, 'msg' => '预案分类不能为空')));
        }
        //检查是否有重复的分类
        foreach($repeat as $k => $v){
            foreach($title as $k1 => $v1){
                if($v['name'] == $v1){
                    die(json_encode(array('status' => -1, 'msg' => '已存在相同名称('.$v['name'].')的预案分类')));
                }
            }
        }
        $addall = [];
        foreach($title as $k1 => $v1){
            $addall[$k1]['name'] = $v1;
        }
        $id = $this->insertDataALL('archives_emergency_category',$addall);
        if($id){
            return array('status'=>1,'msg'=>'添加预案分类成功！');
        }else{
            return array('status'=>-1,'msg'=>'添加预案分类失败！');
        }
    }
    /*
    获取预案分类以及已有数量
     */
    public function getcategory()
    {
        $limit = I('post.limit') ? I('post.limit') : 5;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $where['hospital_id'] = session('current_hospitalid');
        $where['is_delete'] = ['EQ', C('NO_STATUS')];
        $data = $this->DB_get_all('archives_emergency_category','','','','', $offset . ',' . $limit);
        $total = $this->DB_get_count('archives_emergency_category');
        if (!$data) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $categorystr="";
        foreach ($data as $key => $value) {
            $categorystr .= $value['id'].',';
            $data[$key]['num'] = 0;
        }
        $categorystr = rtrim($categorystr);
        $where['category'] =array('IN',$categorystr);
        $count = $this->DB_get_all('archives_emergency_plan','category,count(arempid) as num',$where,'category');
        foreach ($count as $key => $value) {
            foreach ($data as $k => $v) {
            if ($v['id']==$value['category']) {
                $data[$k]['num'] += $value['num'];
            }
         }
        }

        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $data;
        $result['code'] = 200;
        return $result;
    }
    /*
    修改分类名
     */
    public function savecategory()
    {
        $id = I('POST.id');
        $value = I('POST.value');
        $res = $this->updateData('archives_emergency_category',array('name'=>$value),array('id'=>$id));
        if ($res) {
            return array('status'=>1,'msg'=>'编辑成功！');
        }else{
            return array('status'=>-1,'msg'=>'编辑失败！');
        }
    }

    /**
     * Notes: 保存预案
     */
    public function save_emer()
    {
        $add = $file_add = [];
        $emergency = trim(I('post.emergency'));
        $category = trim(I('post.category'));
        $content = trim(I('post.content'));
        $this->checkstatus(judgeEmpty($emergency), '预案名称不能为空');
        $this->checkstatus(judgeEmpty($category), '预案分类不能为空');
        $this->checkstatus(judgeEmpty($content), '预案正文不能为空');
        $add['hospital_id'] = session('current_hospitalid');
        $add['emergency'] = $emergency;
        $add['category'] = $category;
        $add['content'] = $content;
        $add['add_date'] = I('post.add_date');
        $add['add_userid'] = I('post.add_userid');
        $add['add_time'] = date('Y-m-d H:i:s');
        $newid = $this->insertData('archives_emergency_plan',$add);
        if(!$newid){
            return array('status'=>-1,'msg'=>'添加预案失败！');
        }
        //保存文件
        $file_name = I('post.file_name');
        $save_name = I('post.save_name');
        $file_type = I('post.file_type');
        $file_size = I('post.file_size');
        $file_url = I('post.file_url');
        foreach ($file_name as $k=>$v){
            $file_add[$k]['arempid'] = $newid;
            $file_add[$k]['file_name'] = $v;
            $file_add[$k]['save_name'] = $save_name[$k];
            $file_add[$k]['file_type'] = $file_type[$k];
            $file_add[$k]['file_size'] = $file_size[$k];
            $file_add[$k]['file_url'] = $file_url[$k];
            $file_add[$k]['add_time'] = date('Y-m-d H:i:s');
            $file_add[$k]['add_user'] = session('username');
        }
        if($file_add){
            $this->insertDataALL('archives_emergency_plan_file',$file_add);
        }
        return array('status'=>1,'msg'=>'添加预案成功！');
    }

    /**
     * Notes: 获取应急预案详情
     * @param $arempid int 应急预案ID
     */
    public function get_emergency_info($arempid)
    {
        $join = "LEFT JOIN sb_archives_emergency_category AS B ON A.category = B.id";
        $data = $this->DB_get_one_join('archives_emergency_plan','A','A.*,B.name',$join,array('A.arempid'=>$arempid));
        $data['content'] = htmlspecialchars_decode($data['content']);
        $users = $this->DB_get_one('user','username',array('userid'=>$data['add_userid']));
        $data['username'] = $users['username'];
        return $data;
    }

    /**
     * Notes: 获取应急预案文件
     * @param $arempid int 应急预案ID
     */
    public function get_emergency_files($arempid)
    {
        //查找相应文件
        $files = $this->DB_get_all('archives_emergency_plan_file','*',array('arempid'=>$arempid,'is_delete'=>0));
        foreach ($files as $k=>$v){
            $files[$k]['file_url'] = urlencode($v['file_url']);
            switch($v['file_type']){
                case 'pdf':
                    $pic = '<img src="/Public/images/pdf.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                    break;
                case 'doc':
                case 'docx':
                    $pic = '<img src="/Public/images/word.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                    break;
                case 'txt':
                    $pic = '<img src="/Public/images/text.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                    break;
                default:
                    $pic = '<img src="/Public/images/word.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                    break;
            }
            $files[$k]['img'] = $pic;
        }
        return $files;
    }

    /**
     * Notes: 修改预案
     */
    public function edit_emer()
    {
        $add = $file_add = [];
        $arempid = trim(I('post.arempid'));
        $emergency = trim(I('post.emergency'));
        $category = trim(I('post.category'));
        $content = trim(I('post.content'));
        $this->checkstatus(judgeEmpty($emergency), '预案名称不能为空');
        $this->checkstatus(judgeEmpty($category), '预案分类不能为空');
        $this->checkstatus(judgeEmpty($content), '预案正文不能为空');
        $add['emergency'] = $emergency;
        $add['category'] = $category;
        $add['content'] = $content;
        $add['edit_userid'] = session('userid');
        $add['edit_time'] = date('Y-m-d H:i:s');
        $newid = $this->updateData('archives_emergency_plan',$add,array('arempid'=>$arempid));
        if(!$newid){
            return array('status'=>-1,'msg'=>'修改预案失败！');
        }
        //删除原来的文件
        $this->updateData('archives_emergency_plan_file',array('is_delete'=>1),array('arempid'=>$arempid));
        //保存文件
        $file_name = I('post.file_name');
        $save_name = I('post.save_name');
        $file_type = I('post.file_type');
        $file_size = I('post.file_size');
        $file_url = I('post.file_url');
        foreach ($file_name as $k=>$v){
            $file_add[$k]['arempid'] = $arempid;
            $file_add[$k]['file_name'] = $v;
            $file_add[$k]['save_name'] = $save_name[$k];
            $file_add[$k]['file_type'] = $file_type[$k];
            $file_add[$k]['file_size'] = $file_size[$k];
            $file_add[$k]['file_url'] = $file_url[$k];
            $file_add[$k]['add_time'] = date('Y-m-d H:i:s');
            $file_add[$k]['add_user'] = session('username');
        }
        if($file_add){
            $this->insertDataALL('archives_emergency_plan_file',$file_add);
        }
        return array('status'=>1,'msg'=>'修改预案成功！');
    }

    /**
     * Notes: 获取档案列表
     */
    public function get_box_lists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'desc';
        $sort = I('post.sort') ? I('post.sort') : 'arempid';
        $box_num = I('post.box_num');
        $assets = I('post.assets');
        $assnum = I('post.assnum');
        $assetsDep = I('post.department');
        $where['A.is_delete'] = C('NO_STATUS');
        $where['A.hospital_id'] = session('current_hospitalid');
        if ($box_num) {
            //档案盒编号
            $where['A.box_num'] = array('like', "%$box_num%");
        }
        if ($assets) {
            //设备名称
            $where['B.assets'] = array('like', "%$assets%");
        }
        if ($assnum) {
            //设备编号
            $where['B.assnum'] = array('like', "%$assnum%");
        }
        if ($assetsDep) {
            //部门搜索
            $where['B.departid'] = array('IN', $assetsDep);
        }
        $join = "LEFT JOIN sb_assets_info AS B ON A.box_num = B.box_num";
        $groupSql = M()->table('sb_archives_box AS A')->where($where)
            ->join($join)
            ->group('A.box_id')
            ->field('A.box_id')
            ->buildSql();
        $total = M()->table("{$groupSql} as A")->count();
        $data = $this->DB_get_all_join('archives_box','A', 'A.*',$join, $where, 'A.box_id', $sort . ' ' . $order, $offset . ',' . $limit);
        //即将逾期前提醒天数
        $expire_days = $this->getExpireDay();
        //是否有权编辑档案盒
        $editBox = get_menu($this->MODULE, 'Box', 'editBox');
        foreach ($data as &$v){
            $v['file_nums'] = 0;
            $v['toexpire_nums'] = 0;
            $v['date_span'] = '--';
            $v['assets_nums'] = 0;
            $v['depart_nums'] = 0;
            //统计数据
            $tmp_where['A.box_id'] = $v['box_id'];
            $join_assets = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
            $fields = 'A.arc_id,A.box_id,A.assid,A.archive_time,A.expire_time,B.departid';
            $tmpdata = $this->DB_get_all_join('assets_archives_file','A',$fields,$join_assets,$tmp_where);
            $v['file_nums'] = count($tmpdata);
            $v = $this->fortmatdata($tmpdata,$v,$expire_days);
            $html = '<div class="layui-btn-group">';
            $html .= '<button type="button" lay-event="show_box" class="layui-btn layui-btn-xs layui-btn-primary">查看</button>';
            if($editBox){
                $html .= '<button type="button" lay-event="editBox" class="layui-btn layui-btn-xs" data-url="'.$editBox['actionurl'].'?id='.$v['box_id'].'">编辑</button>';
            }
            $html .= '</div>';
            $v['operation'] = $html;
        }
        $result["code"] = 200;
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["rows"] = $data;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }
  

  /**
     * Notes: 获取档案列表
     */
    public function get_m_box_lists()
    {
        $limit = I('get.limit') ? I('get.limit') : 5;
        $page = I('get.page') ? I('get.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('get.order') ? I('get.order') : 'desc';
        $sort = I('get.sort') ? I('get.sort') : 'box_id';
        $search = I('get.search');
        $where['A.is_delete'] = C('NO_STATUS');
        $where['A.hospital_id'] = session('current_hospitalid');
        $map['A.box_num'] = ['like', "%$search%"];
        $map['B.assets'] = ['like', "%$search%"];
        $map['B.assnum'] = ['like', "%$search%"];
        $map['A.box_name'] = ['like', "%$search%"];
        $map['_logic'] = 'or';
        $where['_complex'] = $map;
        $join = "LEFT JOIN sb_assets_info AS B ON A.box_num = B.box_num";
        $groupSql = M()->table('sb_archives_box AS A')->where($where)
            ->join($join)
            ->group('A.box_id')
            ->field('A.box_id')
            ->buildSql();
        $total = M()->table("{$groupSql} as A")->count();
        $data = $this->DB_get_all_join('archives_box','A', 'A.*',$join, $where, 'A.box_id', $sort . ' ' . $order, $offset . ',' . $limit);
        //即将逾期前提醒天数
        $expire_days = $this->getExpireDay();
        //是否有权编辑档案盒
        $editBox = get_menu($this->MODULE, 'Box', 'editBox');
        foreach ($data as &$v){
            $v['file_nums'] = 0;
            $v['toexpire_nums'] = 0;
            $v['date_span'] = '--';
            $v['assets_nums'] = 0;
            $v['depart_nums'] = 0;
            //统计数据
            $tmp_where['A.box_id'] = $v['box_id'];
            $join_assets = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
            $fields = 'A.arc_id,A.box_id,A.assid,A.archive_time,A.expire_time,B.departid';
            $tmpdata = $this->DB_get_all_join('assets_archives_file','A',$fields,$join_assets,$tmp_where);
            $v['file_nums'] = count($tmpdata);
            $v = $this->fortmatdata($tmpdata,$v,$expire_days);
            $html = '<div class="layui-btn-group">';
            $html .= '<button type="button" lay-event="show_box" class="layui-btn layui-btn-xs layui-btn-primary">查看</button>';
            if($editBox){
                $html .= '<button type="button" lay-event="editBox" class="layui-btn layui-btn-xs" data-url="'.$editBox['actionurl'].'?id='.$v['box_id'].'">编辑</button>';
            }
            $html .= '</div>';
            $v['operation'] = $html;
        }
        $result["status"] = 1;
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["rows"] = $data;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['status'] = 1;
        }
        return $result;
    }
    //统计数据
    protected function fortmatdata($tmpdata,$v,$expire_days)
    {
        $depart_nums = $expire_nums = $archive_time = $expire_time = [];
        //统计包含设备数和科室数
        $info = $this->DB_get_all('assets_info','assid,box_num,departid',['box_num'=>$v['box_num']]);
        foreach ($info as $av){
            if(!in_array($av['departid'],$depart_nums)){
                $depart_nums[] = $av['departid'];
            }
        }
        $v['assets_nums'] = count($info);
        $v['depart_nums'] = count($depart_nums);
        foreach ($tmpdata as &$tmp){
            if($tmp['archive_time'] && !in_array($tmp['archive_time'],$archive_time)){
                $archive_time[] = $tmp['archive_time'];
            }
            if($tmp['expire_time'] && !in_array($tmp['expire_time'],$expire_time)){
                $expire_time[] = $tmp['expire_time'];
            }
        }
        if($archive_time){
            //使用给定的用户定义函数对数组排序
            usort($archive_time, "compareByTimeStamp");
            if(count($archive_time) == 1){
                $v['date_span'] = $archive_time[0].' 至 ';
            }else{
                $v['date_span'] = $archive_time[0].' 至 '.$archive_time[count($archive_time)-1];
            }
        }
        if($expire_time){
            $v['toexpire_nums'] = 0;
            //使用给定的用户定义函数对数组排序
            usort($expire_time, "compareByTimeStamp");
            $now = date('Y-m-d');
            $exday = date('Y-m-d',strtotime("+$expire_days day"));
            foreach ($expire_time as $t){
                if($t >= $now && $exday > $t){
                    $v['toexpire_nums'] += 1;
                }
            }
        }
        return $v;
    }

    /**
     * Notes: 获取档案盒详情
     * @param $box_id int 档案盒ID
     */
    public function get_box_info($box_id)
    {
        //即将逾期前提醒天数
        $expire_days = $this->getExpireDay();
        $where['box_id'] = $box_id;
        $boxInfo = $this->DB_get_one('archives_box','*',$where);
        $boxInfo['file_nums'] = 0;
        $boxInfo['toexpire_nums'] = 0;
        $boxInfo['date_span'] = '--';
        $boxInfo['assets_nums'] = 0;
        $boxInfo['depart_nums'] = 0;
        //统计数据
        $tmp_where['A.box_id'] = $boxInfo['box_id'];
        $join_assets = 'LEFT JOIN sb_assets_info AS B ON A.assid = B.assid';
        $fields = 'A.arc_id,A.box_id,A.assid,A.archive_time,A.expire_time,B.departid';
        $tmpdata = $this->DB_get_all_join('assets_archives_file','A',$fields,$join_assets,$tmp_where);
        $boxInfo['file_nums'] = count($tmpdata);
        $boxInfo = $this->fortmatdata($tmpdata,$boxInfo,$expire_days);
        return $boxInfo;
    }

    /**
     * Notes: 获取档案盒文件详情
     * @param $box_num string 档案盒编号
     */
    public function get_box_files($box_num)
    {
        $assets = $this->DB_get_all('assets_info','assid,assets,assnum,departid,box_num',['box_num'=>$box_num]);
        $departname = $catname = $baseSetting = array();
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($assets as &$v){
            $v['department'] = $departname[$v['departid']]['department'];
            $v['files'] = $this->DB_get_all('assets_archives_file','arc_id,file_name,file_url,file_type,add_user,add_time,archive_time,expire_time',['assid'=>$v['assid']]);
            if($v['expire_time'] == '0000-00-00' || !$v['expire_time']){
                $v['expire_time'] = '--';
            }
        }
        return $assets;
    }

    /**
     * Notes: 获取档案盒数据
     * @param $boxidArr array 档案盒ID
     */
    public function get_box_data($boxidArr)
    {
        $where['box_id'] = array('in',$boxidArr);
        return $this->DB_get_all('archives_box','box_id,box_num,code_url',$where);
    }

    /**
     * Notes: 根据departid获取对应的设备
     * @param $departid array 科室ID
     */
    public function getAssets($departid)
    {
        if(!$departid){
            return false;
        }
        $where['hospital_id'] = session('current_hospitalid');
        $where['is_delete'] = C('NO_STATUS');
        $where['departid'] = ['in',$departid];
        $where['status'] = ['neq',C('ASSETS_STATUS_SCRAP')];//已报废的设备排除
        $data = $this->DB_get_all('assets_info','assid,assnum,assets,departid,status,box_num',$where,'','departid asc,assid asc');
        $return_data = [];
        $departname = array();
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as $k=>$v){
            $return_data[$k]['name'] = $departname[$v['departid']]['department'].'：'.$v['assets'].'('.$v['assnum'].')';
            $return_data[$k]['value'] = $v['assid'];
            if($v['box_num']){
                $return_data[$k]['disabled'] = 'disabled';
            }
        }
        return $return_data;
    }

    /**
     * Notes: 新建档案盒
     */
    public function saveBox()
    {
        $data['box_num'] = trim(I('post.box_num'));
        $data['box_name'] = trim(I('post.box_name'));
        $data['remark'] = trim(I('post.remark'));
        $assid_str = trim(I('post.assid'));
        $assid_arr = explode(',',$assid_str);
        if(!$data['box_num'] || !$data['box_name']){
            return array('status'=>-1,'msg'=>'档案盒编号和名称不能为空！');
        }
        //查询档案编号是否已存在
        $boxInfo = $this->DB_get_one('archives_box','box_id',['box_num'=>$data['box_num'],'is_delete'=>C('NO_STATUS')]);
        if($boxInfo){
            return array('status'=>-1,'msg'=>'档案盒编号已存在！');
        }
        $data['hospital_id'] = session('current_hospitalid');
        $data['add_user'] = session('username');
        $data['add_time'] = date('Y-m-d H:i:s');
        $new_box_id = $this->insertData('archives_box',$data);
        if(!$new_box_id){
            return array('status'=>-1,'msg'=>'添加档案盒失败！');
        }
        //新建档案盒成功
        if($assid_arr){
            //档案盒编号绑定设备
            $this->updateData('assets_info',['box_num'=>$data['box_num']],['assid'=>array('in',$assid_arr)]);
            //设备的档案资料绑定box_id
            $this->updateData('assets_archives_file',['box_id'=>$new_box_id],['assid'=>array('in',$assid_arr)]);
        }
        return array('status'=>1,'msg'=>'添加档案盒成功！');
    }

    /**
     * Notes: 删除档案盒设备
     */
    public function delBoxAssets()
    {
        $assid = I('post.id');
        if(!$assid){
            return array('status'=>-1,'msg'=>'参数错误！');
        }
        $res = $this->updateData('assets_info',['box_num'=>''],['assid'=>$assid]);
        if(!$res){
            return array('status'=>-1,'msg'=>'移除失败！');
        }
        //删除设备档案资料的box_id
        $this->updateData('assets_archives_file',['box_id'=>''],['assid'=>$assid]);
        return array('status'=>1,'msg'=>'移除设备成功！');
    }

    public function getExpireDay()
    {
        //即将逾期前提醒天数
        $set = $this->DB_get_one('base_setting','value',['module'=>strtolower($this->MODULE),'set_item'=>'box_reminding_day']);
        return $set['value'] ? $set['value'] : 7;
    }

    /**
     * Notes: 获取需要编辑的档案盒信息
     * @param $box_id int 档案盒ID
     */
    public function edit_box_info($box_id)
    {
        return $this->DB_get_one('archives_box','box_id,box_num,box_name,remark',['box_id'=>$box_id,'is_delete'=>C('NO_STATUS')]);
    }

    /**
     * Notes: 获取需要编辑的档案盒设备
     * @param $box_id int 档案盒ID
     */
    public function edit_box_assets($box_id)
    {
        $boxInfo = $this->DB_get_one('archives_box','box_num',['box_id'=>$box_id]);
        if(!$boxInfo){
            return [];
        }
        $fields = 'B.box_id,A.assid,A.assets,A.assnum,A.departid';
        $join = 'LEFT JOIN sb_assets_archives_file AS B ON A.assid = B.assid';
        $where['A.box_num'] = $boxInfo['box_num'];
        $where['A.hospital_id'] = session('current_hospitalid');
        return $this->DB_get_all_join('assets_info','A',$fields,$join,$where,'assid');
    }

    /**
     * Notes: 保存编辑
     */
    public function saveEdit()
    {
        $assid = I('post.assid');
        $assid_arr = explode(',',$assid);
        $box_id = I('post.id');
        $boxInfo = $this->DB_get_one('archives_box','box_id,box_num',['box_id'=>$box_id,'is_delete'=>C('NO_STATUS')]);
        if(!$boxInfo){
            return array('status'=>-1,'msg'=>'查找不到档案盒信息！');
        }
        $data['box_name'] = I('post.box_name');
        $data['remark'] = trim(I('post.remark'));
        $data['edit_time'] = date('Y-m-d H:i:s');
        $data['edit_user'] = session('username');
        $res = $this->updateData('archives_box',$data,['box_id'=>$box_id]);
        if(!$res){
            return array('status'=>-1,'msg'=>'修改档案盒失败！');
        }
        //删除原来绑定的档案盒编号
        $this->updateData('assets_info',['box_num'=>''],['box_num'=>$boxInfo['box_num'],'hospital_id'=>session('current_hospitalid')]);
        //把档案盒编号绑定到新的设备上
        $this->updateData('assets_info',['box_num'=>$boxInfo['box_num']],['assid'=>array('in',$assid_arr)]);
        //删除原来绑定box_id的设备
        $this->updateData('assets_archives_file',['box_id'=>0],['box_id'=>$box_id]);
        //把box_id绑定到新的设备上
        $this->updateData('assets_archives_file',['box_id'=>$box_id],['assid'=>array('in',$assid_arr)]);
        return array('status'=>1,'msg'=>'修改档案盒成功！');
    }
}
