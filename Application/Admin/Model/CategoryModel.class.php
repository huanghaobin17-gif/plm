<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/17
 * Time: 20:54
 */
namespace Admin\Model;
use Think\Model;
import('@.ORG.Util.TableTree'); //Thinkphp导入方法
class CategoryModel extends CommonModel
{
    protected $len = 500;
    protected $tablePrefix = 'sb_';
    protected $tableName = 'category';
    protected $MODULE = 'BaseSetting';

    /*
     * 列表搜索框中分类搜索功能，返回符合条件的分类ID
     * @params1 $catfields string 要搜索的字段
     * @params2 $catwhere array 要搜索的条件
     * return string
     */
    public function getCatidsBySearch($catwhere)
    {
        $res = $this->DB_get_all('category', 'catid', $catwhere, '', 'catid asc', '');
        if ($res) {
            $catids = '';
            foreach ($res as $k => $v) {
                $catids .= $v['catid'] . ',';
            }
            $catids = trim($catids, ',');
            $where='catid IN('.$catids.') OR parentid IN('.$catids.')';
            $data = $this->DB_get_all('category', 'catid', $where, '', 'catid asc', '');
            $catids = '';
            foreach ($data as $k => $v) {
                $catids .= $v['catid'] . ',';
            }
            $catids=trim($catids,',');
            return $catids;
        } else {
            return '-1';
        }
    }

    public function returnLimitDate($data)
    {
        F('categoryData', $data);
        $categoryData = F('categoryData');
        $arr = array();
        $i = 0;
        foreach($categoryData as $k=>$v){
            if($i < $this->len && $v){
                $arr[] = $v;
                $i++;
                unset($categoryData[$k]);
            }
        }
        F('categoryData', $categoryData);
        return $arr;
    }
    public function addCategory()
    {
        $tempids = explode(',',trim(I('POST.tempid'),','));
        if(!$tempids){
            return array('status'=>-1,'msg'=>'请选择要保存的数据');
        }
        //查询所有医院
        $hospitals = $this->DB_get_all('hospital','*',array('is_delete'=>0));
        $hosid = $hosidtocode = $allft = array();
        foreach ($hospitals as $k=>$v){
            $hosid[$v['hospital_code']] = $v['hospital_id'];
            $hosidtocode[$v['hospital_id']] = $v['hospital_code'];
        }
        $f = $this->DB_get_all('category_upload_temp', 'tempid,hospital_code,catenum,category,parentid,remark', array('parentid'=>0), '', '');
        $fp = array();
        foreach ($f as $k=>$v){
            $fp[$v['hospital_code']][$v['tempid']] = $v['catenum'];
        }

        $res = $this->DB_get_all('category_upload_temp', 'tempid,catenum,hospital_code,category,parentid,remark', array('tempid'=>array('in',$tempids)), '', '');
        //查询数据库已有父分类
        $allf = $this->DB_get_all('category','hospital_id,catenum',array('parentid'=>0,'is_delete'=>0));
        foreach ($allf as $k=>$v){
            $allft[$hosidtocode[$v['hospital_id']]][] = $v['catenum'];
        }
        //查询该医院代码是否存在或在该用户管理范围内
        //查询当前用户所在的医院代码
        $code = $this->DB_get_one('hospital','hospital_code',array('hospital_id'=>session('current_hospitalid'),'is_delete'=>0));
        $existsCode = explode(',',$code['hospital_code']);
        foreach ($res as $k=>$v){
            //判断分类编码规则是否符合系统设置要求
            $checknumres = $this->checkCateNum($v['catenum']);
            if($checknumres['status'] == -1){
                continue;
            }
            if(!in_array($v['hospital_code'],$existsCode)){
                continue;
            }
            if($v['parentid'] != 0){
                //该分类为临时表子类
                $fcatenum = $fp[$v['hospital_code']][$v['parentid']];
                if(!in_array($fcatenum,$allft[$v['hospital_code']])){
                    //系统不存在该父分类
                    //保存入库
                    $sf = $this->DB_get_one('category_upload_temp','catenum,hospital_code,category,parentid,remark',array('catenum'=>$fcatenum));
                    $indata['catenum']  = $sf['catenum'];
                    $indata['category'] = $sf['category'];
                    $indata['parentid'] = $sf['parentid'];
                    $indata['remark'] = $sf['remark'];
                    $indata['hospital_id'] = $hosid[$sf['hospital_code']];
                    //插入父类
                    $res = $this->insertData('category',$indata);
                    //增加到已有父类
                    $allft[$v['hospital_code']][] = $fcatenum;
                    //$this->updateData('category_upload_temp',array('is_save'=>1),array('catenum'=>$v['catenum']));
                    //插入子类
                }else{
                    //查询该子类是否已存在
                    $soncate = $this->DB_get_one('category','catid',array('is_delete'=>0,'catenum'=>$v['catenum'],'hospital_id'=>$hosid[$v['hospital_code']]));
                    if($soncate){
                        //已存在
                        $this->updateData('category_upload_temp',array('is_save'=>1),array('catenum'=>$v['catenum']));
                    }else{
                        //不存在，入库
                        //查询实际父类ID
                        $realFid = $this->DB_get_one('category','catid,hospital_id',array('is_delete'=>0,'catenum'=>$fcatenum,'hospital_id'=>$hosid[$v['hospital_code']]));
                        $indata['catenum']  = $v['catenum'];
                        $indata['category'] = $v['category'];
                        $indata['remark'] = $v['remark'];
                        $indata['parentid'] = $realFid['catid'];
                        $indata['hospital_id'] = $realFid['hospital_id'];
                        $res = $this->insertData('category',$indata);
                        $this->updateData('category_upload_temp',array('is_save'=>1),array('tempid'=>$v['tempid']));
                    }
                }
            }else{
                //该分类为临时表父类
                if(!in_array($v['catenum'],$allft[$v['hospital_code']])){
                    //系统不存在该父分类
                    //保存入库
                    $sf = $this->DB_get_one('category_upload_temp','catenum,hospital_code,category,parentid,remark',array('tempid'=>$v['tempid']));
                    $indata['catenum']  = $sf['catenum'];
                    $indata['category'] = $sf['category'];
                    $indata['parentid'] = $sf['parentid'];
                    $indata['remark'] = $sf['remark'];
                    $indata['hospital_id'] = $hosid[$sf['hospital_code']];
                    //插入父类
                    $res = $this->insertData('category',$indata);
                    //$this->updateData('category_upload_temp',array('is_save'=>1),array('catenum'=>$v['catenum']));
                    //增加到已有父类
                    $allft[$v['hospital_code']][] = $v['catenum'];
                }else{
                    //$this->updateData('category_upload_temp',array('is_save'=>1),array('catenum'=>$v['catenum']));
                }
            }
        }
        $msg = $res ? '保存数据成功！' : '暂无数据保存！';
        return array('status'=>1,'msg'=>$msg);
    }

    public function getSubCates()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        //关键字搜索
        $keyword = I('post.keyword');
        $catid = I('post.catid');
        if(!$catid){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $where['parentid'] = $catid;
        $where['is_delete'] = C('NO_STATUS');
        if ($keyword) {
            //分类名称搜索
            unset($where['parentid']);
            $whereOR['category'] = ['like',"%$keyword%"];
            $whereOR['remark'] = ['like',"%$keyword%"];
            $whereOR['_logic'] = 'or';
            $where['_complex'] = $whereOR;
            $where['hospital_id'] = ['in',session('current_hospitalid')];
        }
        $order = I('POST.order');
        $sort = I('POST.sort');
        if (!$sort) {
            $sort = 'catenum';
        }
        if (!$order) {
            $order = 'asc';
        }
        //查询当前用户是否有权限进行修改分类操作
        $editCate = get_menu($this->MODULE, 'IntegratedSetting', 'editCategory');
        //查询当前用户是否有权限进行删除明细
        $delCate = get_menu($this->MODULE, 'IntegratedSetting', 'deleteCategory');
        //查出分类
        if($keyword){
            $catidAll = [];
            $parentIdAll = [];
            $selectCatid = $this->DB_get_all('category','parentid,catid',$where);
            foreach($selectCatid as $k => $v){
                if($v['parentid'] == 0){
                    $catidAll[$k] = $v['catid'];
                }else{
                    $parentIdAll[$k] = $v['parentid'];
                }
            }
            //如果是关键字查询 另外组织数据
            if($selectCatid){
                //组织父分类
                $parentCategory = $this->DB_get_all('category', '',['catid'=>['in',array_merge($parentIdAll,$catidAll)]], '', $sort . ' ' . $order);
                //组织第一条父分类的子分类数据
                $firstparentID = $parentCategory[0]['catid'];
                $total = $this->DB_get_count('category', ['parentid'=>$firstparentID]);
                $cates = $this->DB_get_all('category', '',['parentid'=>$firstparentID], '', $sort . ' ' . $order,$offset . "," . $limit);
                $result['parentCategory'] = $parentCategory;
            }else{
                $result['parentCategory'] = '';
                $result['msg'] = '暂无相关数据';
                $result['code'] = 400;
                return $result;
            }
        }else{
            $total = $this->DB_get_count('category', $where);
            $cates = $this->DB_get_all('category', '', $where, '', $sort . ' ' . $order,$offset . "," . $limit);
        }
        foreach ($cates as $k => $v) {
            $html = '<div class="layui-btn-group">';
            if ($editCate) {
                $html .= $this->returnListLink('修改',$editCate['actionurl'],'editCate','layui-btn-xs layui-btn-warm','');
            }
            if ($delCate) {
                $html .= $this->returnListLink('删除',$delCate['actionurl'],'delCate','layui-btn-xs layui-btn-danger','');
            }
            $html .= '</div>';
            $cates[$k]['operation'] = $html;
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $cates;
        if(!$result['rows']){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 添加分类操作
     * @return array
     */
    public function saveCate()
    {
        $hospital_id = session('current_hospitalid');
        //获取post过来的parentid
        $addcategory['parentid'] = I('post.parentid');
        $addcategory['catenum'] = trim(I('post.catenum'));
        $addcategory['category'] = trim(I('post.category'));
        $addcategory['remark'] = trim(I('post.remark'));
        //判断分类编码规则是否符合系统设置要求
        $checknumres = $this->checkCateNum($addcategory['catenum']);
        if($checknumres['status'] == -1){
            return array('status'=>-1,'msg'=>$checknumres['msg']);
        }else{
            $addcategory['catenum'] = $checknumres['num'];
        }
        //利用parentid的判断去赋值给分类编号
        if ($addcategory['parentid'] == 0){
            //判断分类编码是否已存在
            $categoryname = $this->DB_get_one('category','catenum',array('hospital_id'=>$hospital_id,'catenum'=>$addcategory['catenum'],'is_delete'=>0));
            if ($addcategory['catenum'] == $categoryname['catenum']){
                return array('status'=>-1,'msg'=>$categoryname['catenum'].'分类编码已存在！');
            }
            $categoryname = $this->DB_get_one('category','category',array('hospital_id'=>$hospital_id,'category'=>$addcategory['category'],'is_delete'=>0));
            if ($addcategory['category'] == $categoryname['category']){
                return array('status'=>-1,'msg'=>$categoryname['catenum'].'分类名称已存在！');
            }
            $addcategory['hospital_id'] = session('current_hospitalid');
        }else{
            //获取分类编码
            $farther = $this->DB_get_one('category','catid,category,catenum,hospital_id',array('catid'=>$addcategory['parentid']));
            //判断子类编号是否合法
            $f = substr($addcategory['catenum'],0,strlen($farther['catenum']));
            if($f != $farther['catenum']){
                return array('status'=>-1,'msg'=>'子类编码须由'.$farther['catenum'].'开头！');
            }
            //查询该子类编码是否已存在
            $catenum = $this->DB_get_one('category','catid',array('parentid'=>$addcategory['parentid'],'catenum'=>$addcategory['catenum'],'is_delete'=>0));
            if ($catenum['catid']){
                return array('status'=>-1,'msg'=>'该子类编码已存在！');
            }
            //查询该子类名称是否已存在
            $catenum = $this->DB_get_one('category','category',array('category'=>$addcategory['category'],'is_delete'=>0));
            if ($catenum['category']){
                return array('status'=>-1,'msg'=>'该子类名称已存在！');
            }
            $addcategory['hospital_id'] = $farther['hospital_id'];
        }
        $newCatid = $this->insertData('category',$addcategory);
        if($newCatid){
            $log['category']=$addcategory['category'];
            $text=getLogText('addCategoryLogText',$log);
            $this->addLog('category',M()->getLastSql(),$text,$newCatid);
            return array('status'=>1,'msg'=>'添加分类成功！');
        }else{
            return array('status'=>-1,'msg'=>'添加分类失败！');
        }
    }

    /**
     * Notes: 修改分类操作
     * @return array
     */
    public function editCate()
    {
        $hospital_id = session('current_hospitalid');
        //获取post过来的parentid
        $catid = I('POST.catid');
        $data['parentid'] = I('post.parentid');
        $data['catenum'] = trim(I('post.catenum'));
        $data['category'] = trim(I('post.category'));
        $data['remark'] = trim(I('post.remark'));
        //判断分类编码规则是否符合系统设置要求
        $checknumres = $this->checkCateNum($data['catenum']);
        if($checknumres['status'] == -1){
            return array('status'=>-1,'msg'=>$checknumres['msg']);
        }else{
            $data['catenum'] = $checknumres['num'];
        }
        //查询原分类信息
        $oldCate = $this->DB_get_one('category','catid,catenum,category,parentid,hospital_id',array('catid'=>$catid));
        //利用parentid的判断去赋值给分类编号
        if ($data['parentid'] == 0){
            //判断分类编码是否已存在
            $categoryname = $this->DB_get_one('category','catenum',array('hospital_id'=>$hospital_id,'catenum'=>$data['catenum'],'catid'=>array('neq',$catid),'is_delete'=>0));
            if ($data['catenum'] == $categoryname['catenum']){
                return array('status'=>-1,'msg'=>$categoryname['catenum'].'分类编码已存在！');
            }
            $categoryname = $this->DB_get_one('category','category',array('hospital_id'=>$hospital_id,'category'=>$data['category'],'catid'=>array('neq',$catid),'is_delete'=>0));
            if ($data['category'] == $categoryname['category']){
                return array('status'=>-1,'msg'=>$categoryname['catenum'].'分类名称已存在！');
            }
        }else{
            //获取分类编码
            $farther = $this->DB_get_one('category','catid,category,catenum,hospital_id',array('catid'=>$data['parentid']));
            //判断子类编号是否合法
            $f = substr($data['catenum'],0,strlen($farther['catenum']));
            if($f != $farther['catenum']){
                return array('status'=>-1,'msg'=>'子类编码须由'.$farther['catenum'].'开头！');
            }
            //查询该子类编码是否已存在
            $catenum = $this->DB_get_one('category','catid',array('parentid'=>$data['parentid'],'catenum'=>$data['catenum'],'catid'=>array('neq',$catid),'is_delete'=>0));
            if ($catenum['catid']){
                return array('status'=>-1,'msg'=>'该子类编码已存在！');
            }
            //查询该子类名称是否已存在
            $catenum = $this->DB_get_one('category','category',array('category'=>$data['category'],'catid'=>array('neq',$catid),'is_delete'=>0,['hospital_id'=>session('current_hospitalid')]));
            if ($catenum['category']){
                return array('status'=>-1,'msg'=>'该子类名称已存在！');
            }
            $data['hospital_id'] = $farther['hospital_id'];
        }
        $res = $this->updateData('category',$data,array('catid'=>$catid));
        if($res){
            //如修改的是父分类编码，则要修改相应的子类编码
            if ($data['parentid'] == 0 && $oldCate['catenum'] != $data['catenum'] || $oldCate['hospital_id'] != $data['hospital_id']){
                $allsub = $this->DB_get_all('category','catid,catenum',array('parentid'=>$catid));
                foreach ($allsub as $k=>$v){
                    $newcatenum = str_replace($oldCate['catenum'],$data['catenum'],$v['catenum']);
                    $this->updateData('category',array('catenum'=>$newcatenum,'hospital_id'=>$data['hospital_id']),array('catid'=>$v['catid']));
                }
            }
            $log['category']=$data['category'];
            $text = getLogText('editCategoryLogText',$log);
            $this->addLog('category',M()->getLastSql(),$text,$catid);
        }
        return array('status'=>1,'msg'=>'修改分类成功！');
    }

    public function getTmpCates()
    {
        $where['is_save'] = 0;
        //查询当前用户所在的医院代码
        $code = $this->DB_get_one('hospital','hospital_code',array('hospital_id'=>session('current_hospitalid'),'is_delete'=>0));
        $where['hospital_code'] = $code['hospital_code'];
        $total = $this->DB_get_count('category_upload_temp',$where);
        $cates = $this->DB_get_all('category_upload_temp','',$where);
        $cates = getTree('parentid','tempid',$cates);
        $arr = array();
        foreach ($cates as $k=>$v){
            $arr[$k]['tempid'] = $v['tempid'];
            //判断分类编码规则是否符合系统设置要求
            $checknumres = $this->checkCateNum($v['catenum']);
            $falg = true;
            if($checknumres['status'] == -1){
                $falg = false;
            }
            if($v['parentid'] == 0){
                $arr[$k]['fcatenum'] = $falg ? $v['catenum'] : '<span style="color:red;">'.$v['catenum'].'</span>';
                $arr[$k]['hospital_code'] = $v['hospital_code'];
            }else{
                $arr[$k]['scatenum'] = $falg ? $v['catenum'] : '<span style="color:red;">'.$v['catenum'].'</span>';
            }
            $arr[$k]['category'] = $v['category'];
            $arr[$k]['remark'] = $v['remark'];
            $arr[$k]['operation'] = '<button class="layui-btn layui-btn-sm layui-btn-danger" lay-event="delCategory">删除</button>';
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
     * Notes: 更新分类临时表数据
     */
    public function updateTempData()
    {
        $tempid = I('POST.tempid');
        //查询原分类信息
        $oldCate = $this->DB_get_one('category_upload_temp','tempid,catenum,category,parentid',array('tempid'=>$tempid));
        $fcatenum = $_POST['fcatenum'];
        $scatenum = $_POST['scatenum'];
        $category = $_POST['category'];
        $hospital_code = $_POST['hospital_code'];
        if(isset($fcatenum)){
            if(!$fcatenum){
                return array('status'=>-1,'msg'=>'父类编号不能为空！');
            }else{
                //判断分类编码规则是否符合系统设置要求
                $checknumres = $this->checkCateNum($fcatenum);
                if($checknumres['status'] == -1){
                    return array('status'=>-1,'msg'=>$checknumres['msg']);
                }else{
                    $fcatenum = $checknumres['num'];
                }
                //判断分类编码是否已存在
                $categoryname = $this->DB_get_one('category_upload_temp','catenum',array('catenum'=>$fcatenum,'tempid'=>array('neq',$tempid)));
                if ($fcatenum == $categoryname['catenum']){
                    return array('status'=>-1,'msg'=>$categoryname['catenum'].'分类编码已存在！');
                }
                $res = $this->updateData('category_upload_temp',array('catenum'=>$fcatenum),array('tempid'=>$tempid));
                if($res){
                    //如修改的是父分类编码，则要修改相应的子类编码
                    if ($oldCate['parentid'] == 0 && $oldCate['catenum'] != $fcatenum){
                        $allsub = $this->DB_get_all('category_upload_temp','tempid,catenum',array('parentid'=>$tempid));
                        foreach ($allsub as $k=>$v){
                            $newcatenum = str_replace($oldCate['catenum'],$fcatenum,$v['catenum']);
                            $this->updateData('category_upload_temp',array('catenum'=>$newcatenum),array('tempid'=>$v['tempid']));
                        }
                    }
                }
                return array('status'=>1,'msg'=>'修改分类成功！');
            }
        }
        if(isset($scatenum)){
            if(!$scatenum){
                return array('status'=>-1,'msg'=>'子类编号不能为空！');
            }else{
                //判断分类编码规则是否符合系统设置要求
                $checknumres = $this->checkCateNum($scatenum);
                if($checknumres['status'] == -1){
                    return array('status'=>-1,'msg'=>$checknumres['msg']);
                }else{
                    $scatenum = $checknumres['num'];
                }
                //判断分类编码是否已存在
                $categoryname = $this->DB_get_one('category_upload_temp','catenum',array('catenum'=>$scatenum,'tempid'=>array('neq',$tempid)));
                if ($scatenum == $categoryname['catenum']){
                    return array('status'=>-1,'msg'=>$categoryname['catenum'].'分类编码已存在！');
                }
                $this->updateData('category_upload_temp',array('catenum'=>$scatenum),array('tempid'=>$tempid));
            }
        }
        if(isset($category)){
            if(!$category){
                return array('status'=>-1,'msg'=>'分类名称不能为空！');
            }else{
                $categoryname = $this->DB_get_one('category_upload_temp','category',array('category'=>$category,'tempid'=>array('neq',$tempid)));
                if ($category == $categoryname['category']){
                    return array('status'=>-1,'msg'=>$categoryname['catenum'].'分类名称已存在！');
                }
                $this->updateData('category_upload_temp',array('category'=>$category),array('tempid'=>$tempid));
            }
        }
        if(isset($hospital_code)){
            if(!$hospital_code){
                return array('status'=>-1,'msg'=>'父类编号不能为空！');
            }else{
                //判断医院代码是否存在
                //查询该医院代码是否存在或在该用户管理范围内
                //查询当前用户所在的医院代码
                $code = $this->DB_get_one('hospital','hospital_code',array('hospital_id'=>session('current_hospitalid'),'is_delete'=>0));
                $existsCode = explode(',',$code['hospital_code']);
                if(!in_array($hospital_code,$existsCode)){
                    return array('status'=>-1,'msg'=>'医院代码不存在！');
                }
                $res = $this->updateData('category_upload_temp',array('hospital_code'=>$hospital_code),array('tempid'=>$tempid));
                if($res){
                    //如修改的是父分类医院代码，则要修改相应的子类代码
                    if ($oldCate['parentid'] == 0 && $oldCate['hospital_code'] != $hospital_code){
                        $allsub = $this->DB_get_all('category_upload_temp','tempid,catenum',array('parentid'=>$tempid));
                        foreach ($allsub as $k=>$v){
                            $this->updateData('category_upload_temp',array('hospital_code'=>$hospital_code),array('tempid'=>$v['tempid']));
                        }
                    }
                }
                return array('status'=>1,'msg'=>'修改分类成功！');
            }
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
        $where['parentid'] = array('in',$tempArr);
        $where['_logic'] = 'or';
        $res = $this->deleteData('category_upload_temp',$where);
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
            'savePath' => 'uploads/batadd_tmp_files_can_delete/',
            'saveName' => array('uniqid', ''),
            'exts' => array('xlsx', 'xls', 'xlsm'),
            'autoSub' => true,
            'subName' => array('date', 'Ymd'),
        );
        $upload = new \Think\Upload($uploadConfig);
        $info = $upload->upload();
        if (!$info) {
            return array('status' => -1, 'msg' => '上传出错，请检查相关文件夹权限');
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
            'A' => 'hospital_code',
            'B' => 'catenum',
            'C' => 'category',
            'D' => 'remark'
        );
        for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for ($colIndex = 'A'; $colIndex != $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if ($cell instanceof \PHPExcel_RichText) { //富文本转换字符串
                    $cell = $cell->__toString();
                }
                if($cellname[$colIndex] == 'category'){
                    if(!$cell){
                        continue;
                    }
                }
                $data[$rowIndex - 2][$cellname[$colIndex]] = trim($cell) ? trim($cell) : '';
            }
        }
        if (!$data) {
            return array('status' => -1, 'msg' => '导入数据失败');
        }
        //查询该医院代码是否存在或在该用户管理范围内
        //查询当前用户所在的医院代码
        $code = $this->DB_get_one('hospital','hospital_code',array('hospital_id'=>session('current_hospitalid'),'is_delete'=>0));
        $existsCode = explode(',',$code['hospital_code']);
        //对数据进行重复性验证
        $delHosCate = array();
        foreach ($data as $k=>$v){
            foreach ($v as $k1=>$v1){
                if($k1 == 'hospital_code'){
                    if(trim($data[$k][$k1])){
                        if(!in_array($data[$k][$k1],$existsCode)){
                            $delHosCate[] = $data[$k]['catenum'];
                            unset($data[$k]);
                            break;
                        }
                    }
                }
                if($k1 == 'catenum'){
                    $res = $this->DB_get_one('category_upload_temp','tempid',array('catenum'=>$v1,'hospital_code'=>$v['hospital_code']));
                    if($res){
                        unset($data[$k]);
                        break;
                    }
                }
                if($k1 == 'category'){
                    $res = $this->DB_get_one('category_upload_temp','tempid',array('category'=>$v1,'hospital_code'=>$v['hospital_code']));
                    if($res){
                        unset($data[$k]);
                        break;
                    }
                }
            }
        }
        //排除掉不存在医院的分类
        foreach ($delHosCate as $k=>$v){
            foreach ($data as $k1=>$v1){
                $needdel = strstr($v1['catenum'],$v);
                if($needdel !== false){
                    unset($data[$k1]);
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
            if($num < $this->len){
                //$this->len条存一次数据到数据库
                $insertData[$num]['adduser'] = session('username');
                $insertData[$num]['adddate'] = getHandleDate(time());
                $insertData[$num]['is_save'] = 0;
                foreach ($v as $k1=>$v1){
                    $insertData[$num][$k1] = $v1;
                }
                $num++;
            }
            if($num == $this->len){
                //插入数据
                $this->insertDataALL('category_upload_temp',$insertData);
                //重置数据
                $num = 0;
                $insertData = array();
            }
        }
        if($insertData){
            $this->insertDataALL('category_upload_temp',$insertData);
        }
        //区分父类子类
//        $all = $this->DB_get_all('category_upload_temp','tempid,hospital_code,catenum,category,parentid',array('is_save'=>0));
//        foreach ($all as $k=>$v){
//            if(is_null($v['parentid'])){
//                $plen = strlen($v['catenum']);
//                foreach ($all as $k1=>$v1){
//                    if($v['catenum'] != $v1['catenum']){
//                        $a = substr($v1['catenum'],0,$plen);
//                        var_dump($v1['catenum']);
//                        var_dump($v['catenum']);
//                        var_dump($a);
//                        if($v['catenum'] == $a){
//                            //$v['catenum']为父类
//                            $this->updateData('category_upload_temp',array('parentid'=>0),array('tempid'=>$v['tempid']));
//                            $this->updateData('category_upload_temp',array('parentid'=>$v['tempid'],'hospital_code'=>$v['hospital_code']),array('tempid'=>$v1['tempid']));
//                        }
//                    }
//                }
//            }
//        }
        //获取所有parentid为空的数据
        $noparentid = $this->DB_get_all('category_upload_temp','tempid,catenum,category,parentid',array('is_save'=>0,'parentid'=>array('exp', 'is null')));
        foreach($noparentid as $k=>$v){
            $parentnum = substr($v['catenum'],0,4);
            $one = $this->DB_get_one('category_upload_temp','tempid',array('catenum'=>$parentnum));
            if($one['tempid']){
                $this->updateData('category_upload_temp',array('parentid'=>0),array('tempid'=>$v['tempid']));
                unset($noparentid[$k]);
            }
        }
        foreach ($noparentid as $k=>$v){
            $this->deleteData('category_upload_temp',array('tempid'=>$v['tempid']));
        }
        return array('status'=>1,'msg'=>'上传数据成功，请核准后再保存！');
    }

    /**
     * Notes: 获取医院分类
     * @param $hopital_id
     */
    public function getCatetypes($hopital_id)
    {
        return $this->DB_get_all('category','',array('parentid'=>0,'hospital_id'=>$hopital_id,'is_delete'=>C('NO_STATUS')));
    }
}