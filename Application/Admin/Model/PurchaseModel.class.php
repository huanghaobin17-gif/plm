<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/3/27
 * Time: 16:19
 */

namespace Admin\Model;

use Think\Model;
use Think\Model\RelationModel;

class PurchaseModel extends CommonModel
{
    private $MODULE = 'Purchases';
    private $Controller = 'Purchases';
    protected $tablePrefix = 'sb_';
    protected $tableName = 'purchase';


    //采购申请列表
    public function purchaseApplyList(){
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $data=array(
            array('purchase_id'=>1,'projectName'=>'普通内科新增设备','departid'=>'1','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>5,'price'=>12000.00,'status'=>'1'),
            array('purchase_id'=>2,'projectName'=>'血透室报废更新','departid'=>'2','ApplyDate'=>'2018-5-4','level'=>'报废更新','number'=>1,'price'=>8000.00,'status'=>'0'),
            array('purchase_id'=>3,'projectName'=>'肺功能室新增设备','departid'=>'3','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>2,'price'=>67000.00,'status'=>'1'),
            array('purchase_id'=>4,'projectName'=>'肿瘤内科新增设备','departid'=>'4','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>1,'price'=>10000.00,'status'=>'1'),
            array('purchase_id'=>5,'projectName'=>'超声波科添置设备','departid'=>'5','ApplyDate'=>'2018-5-4','level'=>'添置','number'=>1,'price'=>20000.00,'status'=>'1'),

        );
        if(!$data){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $editMenu=get_menu('Purchases','Purchases','editApply');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$one){
            $one['status']=$one['status']==1?'已审批':'未审批';
            $one['department'] = $departname[$one['departid']]['department'];
            $one['operation'] = '<div class="layui-btn-group">';
            $one['operation'].=$this->returnListLink('详情',get_url().'?action=showApplyDetails&purchase_id='.$one['purchase_id'],'showDetails',C('BTN_CURRENCY') . ' layui-btn-primary');
            if($editMenu){
                $one['operation'].=$this->returnListLink('编辑',$editMenu['actionurl'],'editApply',C('BTN_CURRENCY'));
            }
            $one['operation'].='</div>';

        }
        $result['total'] = 5;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //提交采购信息
    public function purchaseApply(){
        return array('status'=>1,'msg'=>'提交成功');
    }

    //编辑采购信息
    public function editApply(){
        return array('status'=>1,'msg'=>'编辑成功');
    }

    //采购审批列表
    public function purchaseApproveList(){
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $data=array(
            array('purchase_id'=>1,'projectName'=>'普通内科新增设备','departid'=>'1','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>5,'price'=>12000.00,'status'=>'1'),
            array('purchase_id'=>2,'projectName'=>'血透室报废更新','departid'=>'2','ApplyDate'=>'2018-5-4','level'=>'报废更新','number'=>1,'price'=>8000.00,'status'=>'0'),
            array('purchase_id'=>3,'projectName'=>'肺功能室新增设备','departid'=>'3','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>2,'price'=>67000.00,'status'=>'1'),
            array('purchase_id'=>4,'projectName'=>'肿瘤内科新增设备','departid'=>'4','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>1,'price'=>10000.00,'status'=>'1'),
            array('purchase_id'=>5,'projectName'=>'超声波科添置设备','departid'=>'5','ApplyDate'=>'2018-5-4','level'=>'添置','number'=>1,'price'=>20000.00,'status'=>'1'),
        );
        if(!$data){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $ApproveMenu=get_menu('Purchases','Purchases','purchaseApprove');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$one){
            $one['status']=$one['status']==1?'已审批':'未审批';
            $one['department'] = $departname[$one['departid']]['department'];
            $one['operation'] = '<div class="layui-btn-group">';
            $one['operation'].=$this->returnListLink('详情',get_url().'?action=showApproveDetails&purchase_id='.$one['purchase_id'],'showDetails',C('BTN_CURRENCY') . ' layui-btn-primary');
            if($ApproveMenu){
                $one['operation'].=$this->returnListLink('审批',$ApproveMenu['actionurl'],'purchaseApprove',C('BTN_CURRENCY'));
            }
            $one['operation'].='</div>';
        }
        $result['total'] = 5;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //审批操作
    public function purchaseApprove(){
        return array('status'=>1,'msg'=>'提交成功');
    }

    //招标评审列表
    public function reviewTenderingList(){
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $data=array(
            array('purchase_id'=>1,'projectName'=>'普通内科新增设备','departid'=>'1','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>5,'price'=>12000.00,'status'=>'1','commentaryNumber'=>2,'notCommentaryNumber'=>1,'fraction'=>95),
            array('purchase_id'=>2,'projectName'=>'血透室报废更新','departid'=>'2','ApplyDate'=>'2018-5-4','level'=>'报废更新','number'=>1,'price'=>8000.00,'status'=>'0','commentaryNumber'=>0,'notCommentaryNumber'=>2,'fraction'=>0),
            array('purchase_id'=>3,'projectName'=>'肺功能室新增设备','departid'=>'3','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>2,'price'=>67000.00,'status'=>'2','commentaryNumber'=>2,'notCommentaryNumber'=>0,'fraction'=>100),
            array('purchase_id'=>4,'projectName'=>'肿瘤内科新增设备','departid'=>'4','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>1,'price'=>10000.00,'status'=>'2','commentaryNumber'=>3,'notCommentaryNumber'=>0,'fraction'=>100),
            array('purchase_id'=>5,'projectName'=>'超声波科添置设备','departid'=>'5','ApplyDate'=>'2018-5-4','level'=>'添置','number'=>1,'price'=>20000.00,'status'=>'2','commentaryNumber'=>5,'notCommentaryNumber'=>0,'fraction'=>100),

        );
        if(!$data){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $reviewTenderingMenu=get_menu('Purchases','Tendering','reviewTendering');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$one){
            switch ($one['status']){
                case '0':
                    $one['statusName']='未评审';
                    break;
                case '1':
                    $one['statusName']='评审中';
                    break;
                case '2':
                    $one['statusName']='已评审';
                    break;
            }
            $one['department'] = $departname[$one['departid']]['department'];
            $one['operation'] = '<div class="layui-btn-group">';
            $one['operation'].=$this->returnListLink('详情',get_url().'?action=showReviewTenderingDetails&purchase_id='.$one['purchase_id'],'showDetails',C('BTN_CURRENCY') . ' layui-btn-primary');
            if($reviewTenderingMenu && $one['status']<2){
                $one['operation'].=$this->returnListLink('评审',$reviewTenderingMenu['actionurl'],'reviewTendering',C('BTN_CURRENCY'));
            }
            $one['operation'].='</div>';

        }
        $result['total'] = 5;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //招标评审
    public function reviewTendering(){
        return array('status'=>1,'msg'=>'提交成功');
    }

    //项目通过列表
    public function projectThroughList(){
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $data=array(
            array('purchase_id'=>1,'projectName'=>'普通内科新增设备','departid'=>'1','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>5,'price'=>12000.00,'status'=>'1','fraction'=>55),
            array('purchase_id'=>2,'projectName'=>'血透室报废更新','departid'=>'2','ApplyDate'=>'2018-5-4','level'=>'报废更新','number'=>1,'price'=>8000.00,'status'=>'2','fraction'=>70),
            array('purchase_id'=>3,'projectName'=>'肺功能室新增设备','departid'=>'3','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>2,'price'=>67000.00,'status'=>'0','fraction'=>95),
            array('purchase_id'=>4,'projectName'=>'肿瘤内科新增设备','departid'=>'4','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>1,'price'=>10000.00,'status'=>'0','fraction'=>90),
            array('purchase_id'=>5,'projectName'=>'超声波科添置设备','departid'=>'5','ApplyDate'=>'2018-5-4','level'=>'添置','number'=>1,'price'=>20000.00,'status'=>'0','fraction'=>100),
        );
        if(!$data){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $projectThroughMenu=get_menu('Purchases','Tendering','projectThrough');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$one){
            switch ($one['status']){
                case '0':
                    $one['statusName']='待通过';
                    break;
                case '1':
                    $one['statusName']='已驳回';
                    break;
                case '2':
                    $one['statusName']='已通过';
                    break;
            }
            $one['department'] = $departname[$one['departid']]['department'];
            $one['operation'] = '<div class="layui-btn-group">';
            $one['operation'].=$this->returnListLink('详情',get_url().'?action=showProjectThroughDetails&purchase_id='.$one['purchase_id'],'showDetails',C('BTN_CURRENCY') . ' layui-btn-primary');
            if($projectThroughMenu && $one['status']<1){
                $one['operation'].=$this->returnListLink('通过',$projectThroughMenu['actionurl'],'operation',C('BTN_CURRENCY'),'','data-status=2');
                $one['operation'].=$this->returnListLink('驳回',$projectThroughMenu['actionurl'],'operation',C('BTN_CURRENCY').' layui-btn-danger','','data-status=1');
            }
            $one['operation'].='</div>';

        }
        $result['total'] = 5;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //项目通过/驳回 操作
    public function projectThrough(){
        return array('status'=>1,'msg'=>'提交成功');
    }
    //项目批量 通过/驳回 操作
    public function batchProjectThrough(){
        return array('status'=>1,'msg'=>'提交成功');
    }

    //标书列表
    public function setDocumentList(){
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $data=array(
            array('purchase_id'=>1,'projectName'=>'普通内科新增设备','departid'=>'1','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>5,'price'=>12000.00,'status'=>'2'),
            array('purchase_id'=>2,'projectName'=>'血透室报废更新','departid'=>'2','ApplyDate'=>'2018-5-4','level'=>'报废更新','number'=>1,'price'=>8000.00,'status'=>'2'),
            array('purchase_id'=>3,'projectName'=>'肺功能室新增设备','departid'=>'3','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>2,'price'=>67000.00,'status'=>'2'),
            array('purchase_id'=>4,'projectName'=>'肿瘤内科新增设备','departid'=>'4','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>1,'price'=>10000.00,'status'=>'2'),
            array('purchase_id'=>5,'projectName'=>'超声波科添置设备','departid'=>'5','ApplyDate'=>'2018-5-4','level'=>'添置','number'=>1,'price'=>20000.00,'status'=>'2'),
        );
        if(!$data){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $setDocumentMenu=get_menu('Purchases','Tendering','setDocument');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$one){
            switch ($one['status']){
                case '0':
                    $one['statusName']='待通过';
                    break;
                case '1':
                    $one['statusName']='已驳回';
                    break;
                case '2':
                    $one['statusName']='已通过';
                    break;
            }
            $one['department'] = $departname[$one['departid']]['department'];
            $one['operation'] = '<div class="layui-btn-group">';
            $one['operation'].=$this->returnListLink('详情',get_url().'?action=showSetDocumentDetails&purchase_id='.$one['purchase_id'],'showDetails',C('BTN_CURRENCY') . ' layui-btn-primary');
            if($setDocumentMenu){
                $one['operation'].=$this->returnListLink('制定',$setDocumentMenu['actionurl'],'setDocument',C('BTN_CURRENCY'));
            }
            $one['operation'].='</div>';

        }
        $result['total'] = 5;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //制定标书操作
    public function setDocument(){
        return array('status'=>1,'msg'=>'制定成功');
    }

    //标书评审列表
    public function reviewDocumentList(){
        $limit = I('post.limit') ? I('post.limit') : C('DEFAULT_LIMIT');
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $data=array(
            array('purchase_id'=>1,'projectName'=>'普通内科新增设备','departid'=>'1','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>5,'price'=>12000.00,'status'=>'1'),
            array('purchase_id'=>2,'projectName'=>'血透室报废更新','departid'=>'2','ApplyDate'=>'2018-5-4','level'=>'报废更新','number'=>1,'price'=>8000.00,'status'=>'0'),
            array('purchase_id'=>3,'projectName'=>'肺功能室新增设备','departid'=>'3','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>2,'price'=>67000.00,'status'=>'1'),
            array('purchase_id'=>4,'projectName'=>'肿瘤内科新增设备','departid'=>'4','ApplyDate'=>'2018-5-4','level'=>'新增','number'=>1,'price'=>10000.00,'status'=>'1'),
            array('purchase_id'=>5,'projectName'=>'超声波科添置设备','departid'=>'5','ApplyDate'=>'2018-5-4','level'=>'添置','number'=>1,'price'=>20000.00,'status'=>'1'),
        );
        if(!$data){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
            return $result;
        }
        $reviewDocumentMenu=get_menu('Purchases','Tendering','reviewDocument');
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";
        foreach ($data as &$one){
            $one['status']=$one['status']==1?'已审批':'未审批';
            $one['department'] = $departname[$one['departid']]['department'];
            $one['operation'] = '<div class="layui-btn-group">';
            $one['operation'].=$this->returnListLink('详情',get_url().'?action=showReviewDocumentDetails&purchase_id='.$one['purchase_id'],'showDetails',C('BTN_CURRENCY') . ' layui-btn-primary');
            if($reviewDocumentMenu){
                $one['operation'].=$this->returnListLink('审批',$reviewDocumentMenu['actionurl'],'reviewDocument',C('BTN_CURRENCY'));
            }
            $one['operation'].='</div>';
        }
        $result['total'] = 5;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $data;
        return $result;
    }

    //标书评审操作
    public function reviewDocument(){
        return array('status'=>1,'msg'=>'评审成功');
    }

}
