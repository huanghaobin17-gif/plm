<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/17
 * Time: 20:54
 */
namespace Admin\Model;
use Think\Model;
use Think\Model\RelationModel;
import('@.ORG.Util.TableTree'); //Thinkphp导入方法
class BusinessControlModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'business_control';
    private $MODULE = 'Repair';

    /**
     * Notes: 新增业务
     *
     */
    public function addBusiness()
    {
        $addData['project'] = C('PROJECT_CODE');
        $addData['process_num'] = trim(I('POST.process_num'));
        $addData['repair_num'] = trim(I('POST.repair_num'));
        $addData['type'] = I('POST.type');
        if($addData['type'] == 2){
            $addData['customer_name'] = trim(I('POST.customer_name'));
            $addData['customer_contacts'] = trim(I('POST.customer_contacts'));
            $addData['customer_tel'] = trim(I('POST.customer_tel'));
            if(!judgeEmpty($addData['customer_name'])){
                return array('status'=>-1,'msg'=>'客户名称不能为空！');
            }
            if(!judgeEmpty($addData['customer_contacts'])){
                return array('status'=>-1,'msg'=>'客户联系人不能为空！');
            }
            if(!judgeEmpty($addData['customer_tel'])){
                return array('status'=>-1,'msg'=>'供应商联系电话不能为空！');
            }
        }
        $addData['desc'] = trim(I('POST.desc'));
        $addData['supplier'] = trim(I('POST.supplier'));
        $addData['supplier_contacts'] = trim(I('POST.supplier_contacts'));
        $addData['supplier_tel'] = trim(I('POST.supplier_tel'));
        $addData['supplier_account'] = trim(I('POST.supplier_account'));
        $addData['supplier_bank'] = trim(I('POST.supplier_bank'));
        $addData['supplier_bank_num'] = trim(I('POST.supplier_bank_num'));
        $addData['create_user'] = session('username');
        $addData['create_time'] = strtotime(I('POST.createdate'));
        if(!judgeEmpty($addData['desc'])){
            return array('status'=>-1,'msg'=>'业务描述不能为空！');
        }
        if(!judgeEmpty($addData['create_time'])){
            return array('status'=>-1,'msg'=>'申请日期不能为空！');
        }
        if(!judgeEmpty($addData['supplier'])){
            return array('status'=>-1,'msg'=>'供应商名称不能为空！');
        }
        if(!judgeEmpty($addData['supplier_contacts'])){
            return array('status'=>-1,'msg'=>'供应商联系人不能为空！');
        }
        if(!judgeEmpty($addData['supplier_tel'])){
            return array('status'=>-1,'msg'=>'供应商联系电话不能为空！');
        }
        if(!judgeEmpty($addData['supplier_account'])){
            return array('status'=>-1,'msg'=>'账户名称不能为空！');
        }
        if(!judgeEmpty($addData['supplier_bank'])){
            return array('status'=>-1,'msg'=>'开户银行不能为空！');
        }
        if(!judgeEmpty($addData['supplier_bank_num'])){
            return array('status'=>-1,'msg'=>'供应商账号不能为空！');
        }
        $title = I('POST.title');
        $is_depot = I('POST.is_depot');
        $depot = array();
        foreach($is_depot as $k => $v){
            if($v == '否'){
                $depot[$k] = 0;
            }elseif($v == '是'){
                $depot[$k] = 1;
            }

        }
        $factory = I('POST.factory');
        $model = I('POST.model');
        $num = I('POST.num');
        $price = I('POST.price');
        $unit = I('POST.unit');
        $gdate = I('POST.gdate');
        $invoice_type = I('POST.invoice_type');
        $invoice_rate = I('POST.invoice_rate');
        $invoice_content = I('POST.invoice_content');
        foreach($title as $val){
            if(!trim($val)){
                return array('status'=>-1,'msg'=>'物料/服务名称不能为空！');
            }
        }
        foreach($num as $val){
            if(!trim($val)){
                return array('status'=>-1,'msg'=>'物料/服务数量不能为空！');
            }
        }
        foreach($price as $val){
            if(!trim($val)){
                return array('status'=>-1,'msg'=>'物料/服务单价不能为空！');
            }
        }
        //新增业务数据
        $contid = $this->insertData('business_control',$addData);
        //日志行为记录文字
        $text = getLogText('addBusinessLogText');
        $this->addLog('business_control',M()->getLastSql(),$text,$contid,'');
        if(!$contid){
            return array('status'=>-1,'msg'=>'新增业务失败，请稍后再试！');
        }
        //组织数据
        $addMateriel = [];
        $allMateriel = [];
        foreach ($title as $k=>$v){
            $addMateriel['title'] = $v;
            $addMateriel['is_depot'] = $depot[$k];
            $addMateriel['contid'] = $contid;
            $addMateriel['project_matid'] = 0;
            $addMateriel['model'] = $model[$k];
            $addMateriel['num'] = (int)$num[$k];
            $addMateriel['price'] = (float)$price[$k];
            $addMateriel['total_price'] = $addMateriel['num']*$addMateriel['price'];
            $addMateriel['unit'] = $unit[$k];
            $addMateriel['factory'] = $factory[$k];
            $addMateriel['guarantee_date'] = $gdate[$k];
            $addMateriel['is_invoice'] = 0;
            if($invoice_type[$k] == '无发票'){
                $addMateriel['is_invoice'] = 0;
            }else{
                $addMateriel['is_invoice'] = 1;
            }
            $addMateriel['invoice_type'] = $invoice_type[$k];
            $addMateriel['invoice_rate'] = $invoice_rate[$k];
            //开票内容
            $addMateriel['invoice_content'] = $invoice_content[$k];
            //新增物料数据
            $materid = $this->insertData('business_materiel',$addMateriel);
            if(!$materid){
                //删除业务数据
                $this->deleteData('business_control',array('contid'=>$contid));
                return array('status'=>-1,'msg'=>'新增业务失败，请稍后再试！');
            }
            $addMateriel['project_matid'] = $materid;
            $allMateriel[] = $addMateriel;
        }
        if(1){
            $addData['project_contid'] = $contid;
            $data['business'] = $addData;
            $data['materiel'] = $allMateriel;
            Vendor('TecevApi.TecevApi');
            $api = new \TecevApi();
            $res = $api->syncBusinessData('createBusiness',C('PROJECT_CODE'),$data);
            //对返回数据签名进行验证
            $signed = $api->getSign('createBusiness',$res['requestTime'],C('PROJECT_CODE'));
            if($res['signed'] == $signed){
                //验证通过
                if($res['resultCode'] == 200){
                    //总部已同步成功,记录同步成功信息
                    $this->updateData('business_control',array('synchro_add'=>1),array('contid'=>$contid));
                }
            }
            return array('status'=>1,'msg'=>'新增业务成功！');
        }
    }

    public function followBusiness()
    {
        //业务跟进
        $contid = I('POST.contid');
        $updateData['sale_price'] = I('POST.salePrice');
        $updateData['total_sale'] = I('POST.totalSale');
        $updateData['allowance']  = I('POST.allowance');
        $updateData['follow_time']  = time();
        $res = $this->updateData('business_control',$updateData,array('contid'=>$contid));
        //日志行为记录文字
        $text = getLogText('followBusinessLogText');
        $this->addLog('business_control',M()->getLastSql(),$text,$contid,'');
        if($res){
            $data['where']['project_contid'] = $contid;
            $data['where']['project'] = C('PROJECT_CODE');
            $data['business']['sale_price'] = $updateData['sale_price'];
            $data['business']['total_sale'] = $updateData['total_sale'];
            $data['business']['allowance'] = $updateData['allowance'];
            $data['business']['follow_time'] = $updateData['follow_time'];
            Vendor('TecevApi.TecevApi');
            $api = new \TecevApi();
            $res = $api->syncBusinessData('followBusiness',C('PROJECT_CODE'),$data);
            //对返回数据签名进行验证
            $signed = $api->getSign('followBusiness',$res['requestTime'],C('PROJECT_CODE'));
            if($res['signed'] == $signed){
                //验证通过
                if($res['resultCode'] == 200){
                    //总部已同步跟进,记录同步跟进成功信息
                    $this->updateData('business_control',array('follow'=>1),array('contid'=>$contid));
                }
            }
            return array('status'=>1,'msg'=>'业务跟进成功！');
        }else{
            return array('status'=>-1,'msg'=>'业务跟进失败，请稍后再试！');
        }
    }

    /**
     * Notes: 获取业务列表
     */
    public function getBusinessLists()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $offset = ($page - 1) * $limit;
        $order = I('post.order') ? I('post.order') : 'asc';
        $sort = I('post.sort') ? I('post.sort') : 'contid';
        $project = I('POST.project');//项目搜索
        $pnum = (I('POST.pnum') == '') ? 1 : I('POST.pnum');//OA流程编号
        $type = I('POST.type');//业务类型
        $create_user = I('POST.createUser');//采购申请人
        $customer_name = I('POST.cusName');//客户名称
        $pay_user = I('POST.payUser');//付款方
        $income_date = (I('POST.incomeDate') == '') ? 0 : I('POST.incomeDate');//进票项票日期
        $income_num = (I('POST.incomeNum') == '') ? 0 : I('POST.incomeNum');//进票项票编号
        $output_date = I('POST.outputDate');//销项票项日期
        $output_num = I('POST.outputNum');//进票项日期
        $payment_date = I('POST.paymentDate');//回款日期
        $settlement_date = I('POST.settleDate');//结算日期
        $allover_date = I('POST.alloverDate');//结单日期
        $invoice_type = I('POST.invoiceType');//发票类型
        $where = array('1');
        //分院查询自己提交的业务
        if(!C('HEADQUARTERS')){
            //如果是项目经理（有权限跟进业务的），有权限查看所有业务
            $follow = get_menu('Repair','Repair','followBusinessControl');
            if(!$follow){
                $where['create_user'] = session('username');
            }
            $where['project_contid'] = array('eq',0);
        }else{
            $where['project_contid'] = array('neq',0);
        }
        if($project){
            $where['project'] = $project;
        }
        if($pnum){
            $where['process_num'] = array('EXP','IS NOT NULL');
        }else{
            $where['process_num'] = array(array('EXP','IS NULL'),array('eq',''), 'or');
        }
        if($type){
            $where['type'] = $type;
        }
        if($create_user){
            $where['create_user'] = array('like',$create_user);
        }
        if($customer_name){
            $where['customer_name'] = array('like',$customer_name);
        }
        if($pay_user){
            $where['pay_user'] = array('like',$pay_user);
        }
        if($income_date == 1){
            $where['income_date'] = array('GT','0000-00-00');
        }else{
            $where['income_date'] = array(array('EQ','0000-00-00'),array('EXP','IS NULL'), 'or');
        }
        if($income_num == 1){
            $where['income_num'] = array('EXP','IS NOT NULL');
        }else{
            $where['income_num'] = array(array('EXP','IS NULL'),array('eq',''), 'or');
        }
        if($output_date){
            $where['output_date'] = $output_date;
        }
        if($output_num){
            $where['output_num'] = array('like',$output_num);
        }
        if($payment_date){
            $where['payment_date'] = $payment_date;
        }
        if($settlement_date){
            $where['settlement_date'] = $settlement_date;
        }
        if($allover_date){
            $where['allover_date'] = $allover_date;
        }
        if($invoice_type){
            //查询物料表中对应的发票类型所属的contid集合
            if($invoice_type != '-1'){
                $contidArr = $this->DB_get_all('business_materiel','contid',array('is_invoice'=>1,'invoice_type'=>$invoice_type),'contid');
            }else{
                $contidArr = $this->DB_get_all('business_materiel','contid',array('is_invoice'=>0),'contid');
            }
            $ids = [];
            foreach ($contidArr as $k=>$v){
                $ids[] = $v['contid'];
            }
            if($ids){
                $where['contid'] = array('in',$ids);
            }
        }
        $total = $this->DB_get_count('business_control',$where);
        $res = $this->DB_get_all('business_control','*',$where, '', $sort.' '.$order,$offset.','.$limit);
        //查询对应物料信息
        foreach ($res as $k=>$v){
            $res[$k]['materiel'] = $this->DB_get_all('business_materiel','*',array('contid'=>$v['contid']));
            foreach($res[$k]['materiel'] as $k1 => $v1){
                if($v1['is_depot'] == 0){
                    $res[$k]['materiel'][$k1]['depot'] = '否';
                }elseif($v1['is_depot'] == 1){
                    $res[$k]['materiel'][$k1]['depot'] = '是';
                }
            }
        }
        //查询当前用户是否有权限进行跟进业务
        $follow = get_menu($this->MODULE, 'Repair', 'followBusinessControl');
        //查询当前用户是否有权限进行业务修改
        $editBusiness = get_menu($this->MODULE, 'Repair', 'editBusinessControl');
        $buy = $financial = array();
        if(C('HEADQUARTERS')){
            //查询当前用户是否有权限进行采购对接
            $buy = get_menu($this->MODULE, 'Repair', 'buyMateriel');
            //查询当前用户是否有权限进行财务对接
            $financial = get_menu($this->MODULE, 'Repair', 'finanDocking');
        }
        foreach($res as $k=>$v){
            if(C('HEADQUARTERS')){
                $res[$k]['project'] = C('PROJECT_CODE_TO_NAME')[$v['project']];
            }else{
                $res[$k]['project'] = C('PROJECT_NAME');
            }
            $res[$k]['create_time'] = getHandleTime($v['create_time']);
            $res[$k]['buy_accept_date'] = ($v['buy_accept_date'] == '0000-00-00') ? '' : $v['buy_accept_date'];
            $res[$k]['income_date'] = ($v['income_date'] == '0000-00-00') ? '' : $v['income_date'];
            $res[$k]['pay_date'] = ($v['pay_date'] == '0000-00-00') ? '' : $v['pay_date'];
            $res[$k]['pay_to_capital'] = ($v['pay_to_capital'] == '0000-00-00') ? '' : $v['pay_to_capital'];
            $res[$k]['actual_pay_date'] = ($v['actual_pay_date'] == '0000-00-00') ? '' : $v['actual_pay_date'];
            $res[$k]['output_date'] = ($v['output_date'] == '0000-00-00') ? '' : $v['output_date'];
            $res[$k]['payment_date'] = ($v['payment_date'] == '0000-00-00') ? '' : $v['payment_date'];
            $res[$k]['settlement_date'] = ($v['settlement_date'] == '0000-00-00') ? '' : $v['settlement_date'];
            $res[$k]['allover_date'] = ($v['allover_date'] == '0000-00-00') ? '' : $v['allover_date'];
            $html = '<div class="layui-btn-group">';
            if ($follow) {
                $html .= '<a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="follow" id="follow" href="javascript:void(0)" data-url="' . $follow['actionurl'] . '">' . $follow['actionname'] . '</a>';
            }
            if ($editBusiness) {
                $html .= '<a class="layui-btn layui-btn-xs" lay-event="editBusiness" id="editBusiness" href="javascript:void(0)" data-url="' . $editBusiness['actionurl'] . '">' . $editBusiness['actionname'] . '</a>';
            }
            if ($buy) {
                $html .= '<a class="layui-btn layui-btn-xs" lay-event="buyMateriel" id="buyMateriel" href="javascript:void(0)" data-url="' . $buy['actionurl'] . '">' . $buy['actionname'] . '</a>';
            }
            if ($financial) {
                if($v['all_complete'] == 1){
                    $html .= '<a class="layui-btn layui-btn-xs" lay-event="financial" id="financial" href="javascript:void(0)" data-url="' . $financial['actionurl'] . '">已结束&查看</a>';
                }else{
                    $html .= '<a class="layui-btn layui-btn-xs" lay-event="financial" id="financial" href="javascript:void(0)" data-url="' . $financial['actionurl'] . '">' . $financial['actionname'] . '</a>';
                }
            }
            $html .= '</div>';
            $res[$k]['operation'] = $html;
        }
        $result['limit'] = (int)$limit;
        $result['offset'] = $offset;
        $result['total'] = (int)$total;
        $result['rows'] = $res;
        $result['code'] = 200;
        if(!$result['rows']){
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    /**
     * Notes: 获取业务列表
     */
    public function getExportBusinessLists()
    {
//        $limit = I('post.limit') ? I('post.limit') : 10;
//        $page = I('post.page') ? I('post.page') : 1;
//        $offset = ($page - 1) * $limit;
//        $order = I('post.order') ? I('post.order') : 'asc';
//        $sort = I('post.sort') ? I('post.sort') : 'contid';
//        $project = I('POST.project');//项目搜索
//        $pnum = (I('POST.pnum') == '') ? 1 : I('POST.pnum');//OA流程编号
//        $type = I('POST.type');//业务类型
//        $create_user = I('POST.createUser');//采购申请人
//        $customer_name = I('POST.cusName');//客户名称
//        $pay_user = I('POST.payUser');//付款方
//        $income_date = (I('POST.incomeDate') == '') ? 0 : I('POST.incomeDate');//进票项票日期
//        $income_num = (I('POST.incomeNum') == '') ? 0 : I('POST.incomeNum');//进票项票编号
//        $output_date = I('POST.outputDate');//销项票项日期
//        $output_num = I('POST.outputNum');//进票项日期
//        $payment_date = I('POST.paymentDate');//回款日期
//        $settlement_date = I('POST.settleDate');//结算日期
//        $allover_date = I('POST.alloverDate');//结单日期
//        $invoice_type = I('POST.invoiceType');//发票类型
//        $where = array('1');
//        //分院查询自己提交的业务
//        if(!C('HEADQUARTERS')){
//            //如果是项目经理（有权限跟进业务的），有权限查看所有业务
//            $follow = get_menu('Repair','Repair','followBusinessControl');
//            if(!$follow){
//                $where['create_user'] = session('username');
//            }
//            $where['project_contid'] = array('eq',0);
//        }else{
//            $where['project_contid'] = array('neq',0);
//        }
//        if($project){
//            $where['project'] = $project;
//        }
//        if($pnum){
//            $where['process_num'] = array('EXP','IS NOT NULL');
//        }else{
//            $where['process_num'] = array('EXP','IS NULL');
//        }
//        if($type){
//            $where['type'] = $type;
//        }
//        if($create_user){
//            $where['create_user'] = array('like',$create_user);
//        }
//        if($customer_name){
//            $where['customer_name'] = array('like',$customer_name);
//        }
//        if($pay_user){
//            $where['pay_user'] = array('like',$pay_user);
//        }
//        if($income_date == 1){
//            $where['income_date'] = array('GT','0000-00-00');
//        }else{
//            $where['income_date'] = array(array('EQ','0000-00-00'),array('EXP','IS NULL'), 'or');
//        }
//        if($income_num == 1){
//            $where['income_num'] = array('EXP','IS NOT NULL');
//        }else{
//            $where['income_num'] = array(array('EXP','IS NULL'),array('eq',''), 'or');
//        }
//        if($output_date){
//            $where['output_date'] = $output_date;
//        }
//        if($output_num){
//            $where['output_num'] = array('like',$output_num);
//        }
//        if($payment_date){
//            $where['payment_date'] = $payment_date;
//        }
//        if($settlement_date){
//            $where['settlement_date'] = $settlement_date;
//        }
//        if($allover_date){
//            $where['allover_date'] = $allover_date;
//        }
//        if($invoice_type){
//            //查询物料表中对应的发票类型所属的contid集合
//            if($invoice_type != '-1'){
//                $contidArr = $this->DB_get_all('business_materiel','contid',array('is_invoice'=>1,'invoice_type'=>$invoice_type),'contid');
//            }else{
//                $contidArr = $this->DB_get_all('business_materiel','contid',array('is_invoice'=>0,'invoice_type'=>$invoice_type),'contid');
//            }
//            $ids = [];
//            foreach ($contidArr as $k=>$v){
//                $ids[] = $v['contid'];
//            }
//            if($ids){
//                $where['contid'] = array('in',$ids);
//            }
//        }
        $contid = trim(I('get.contid'),',');
        $where = array('contid'=>array('IN',$contid));
        $res = $this->DB_get_all('business_control','',$where, '','','');
        foreach($res as $k=>$v){
            if(C('HEADQUARTERS')){
                $res[$k]['project'] = C('PROJECT_CODE_TO_NAME')[$v['project']];
            }else{
                $res[$k]['project'] = C('PROJECT_NAME');
            }
            $res[$k]['type'] = $v['type'] == 1 ? '项目内' : '项目外';
            $res[$k]['create_time'] = getHandleTime($v['create_time']);
            $res[$k]['buy_accept_date'] = ($v['buy_accept_date'] == '0000-00-00') ? '' : $v['buy_accept_date'];
            $res[$k]['income_date'] = ($v['income_date'] == '0000-00-00') ? '' : $v['income_date'];
            $res[$k]['pay_date'] = ($v['pay_date'] == '0000-00-00') ? '' : $v['pay_date'];
            $res[$k]['pay_to_capital'] = ($v['pay_to_capital'] == '0000-00-00') ? '' : $v['pay_to_capital'];
            $res[$k]['actual_pay_date'] = ($v['actual_pay_date'] == '0000-00-00') ? '' : $v['actual_pay_date'];
            $res[$k]['output_date'] = ($v['output_date'] == '0000-00-00') ? '' : $v['output_date'];
            $res[$k]['payment_date'] = ($v['payment_date'] == '0000-00-00') ? '' : $v['payment_date'];
            $res[$k]['settlement_date'] = ($v['settlement_date'] == '0000-00-00') ? '' : $v['settlement_date'];
            $res[$k]['allover_date'] = ($v['allover_date'] == '0000-00-00') ? '' : $v['allover_date'];
        }
        foreach ($res as $k=>$v){
            $materiel = $this->DB_get_all('business_materiel','',array('contid'=>$v['contid']));
            $res[$k]['length'] = count($materiel);
            $res[$k]['materiel'] = $materiel;
        }
        return $res;
    }

    /**
     * Notes: 查询业务单信息
     * @param $contid int 业务单ID
     * @return array
     */
    public function getBusinessInfo($contid)
    {
        return $this->DB_get_one('business_control','*',array('contid'=>$contid));
    }

    /**
     * Notes: 查询业务单物料信息
     * @param $contid int 业务单ID
     * @return array
     */
    public function getMaterielInfo($contid)
    {
        $MaterielInfo = $this->DB_get_all('business_materiel','*',array('contid'=>$contid));
        foreach($MaterielInfo as $k => $v){
            if($v['is_depot'] == 0){
                $MaterielInfo[$k]['depot'] = '否';
            }elseif($v['is_depot'] == 1){
                $MaterielInfo[$k]['depot'] = '是';
            }
        }
        return $MaterielInfo;
    }

    /**
     * Notes: 保存采购对接信息
     */
    public function saveBuyInfo()
    {
        $contid = I('POST.contid');
        //查询该业务单信息
        $businessInfo = $this->DB_get_one('business_control','project_contid,project',array('contid'=>$contid));
        if(!$businessInfo){
            return array('status'=>-1,'msg'=>'查找不到该业务单信息！');
        }
        $updateData['buy_user'] = session('username');//对接人
        $updateData['buy_accept_date'] = I('POST.acceptDate');//受理日期
        $incomeDate = I('POST.incomeDate');//进项票日期
        if($incomeDate != ''){
            $updateData['income_date'] = $incomeDate;
        }else{
            $updateData['income_date'] = '0000-00-00';
        }
        $updateData['income_num'] = trim(I('POST.incomeNum'));//进项票编号
        $updateData['goods_arrive'] = trim(I('POST.goodsArrive'));//货物到货情况
        $updateData['logistics_num'] = trim(I('POST.logisticsNum'));//货物物流单号
        $updateData['remark'] = trim(I('POST.remark'));//其他备注
        $pay_date = I('POST.payDate');//付款申请日期
        if($pay_date != ''){
            $updateData['pay_date'] = $pay_date;
        }else{
            $updateData['pay_date'] = '0000-00-00';
        }
        $pay_to_capital = I('POST.toCapital');//提交资金部日期
        if($pay_to_capital != ''){
            $updateData['pay_to_capital'] = $pay_to_capital;
        }else{
            $updateData['pay_to_capital'] = '0000-00-00';
        }
        $actual_pay_date = I('POST.actualPdate');//实际付款时间
        if($actual_pay_date != ''){
            $updateData['actual_pay_date'] = $actual_pay_date;
        }else{
            $updateData['actual_pay_date'] = '0000-00-00';
        }
        $updateData['pay_user'] = trim(I('POST.payUser'));//付款方
        $updateData['pay_money'] = trim(I('POST.payMoney'));//付款金额
        $updateData['pay_remark'] = trim(I('POST.payRemark'));//付款备注
        $pay_for_spare = I('POST.pay_for_spare');//备用金支付
        if($pay_for_spare != ''){
            $updateData['pay_for_spare'] = $pay_for_spare;
        }
        $in_kingdee = I('POST.in_kingdee');//金蝶入库
        if($in_kingdee != ''){
            $updateData['in_kingdee'] = $in_kingdee;
        }
        $out_kingdee = I('POST.out_kingdee');//金蝶出库
        if($out_kingdee != ''){
            $updateData['out_kingdee'] = $out_kingdee;
        }
        $first_operation = I('POST.first_operation');//是否首营
        if($first_operation != ''){
            $updateData['first_operation'] = $first_operation;
        }
        if(!$updateData['pay_user']){
            return array('status'=>-1,'msg'=>'请填写付款方！');
        }
        if(!$updateData['pay_money']){
            return array('status'=>-1,'msg'=>'请填写付款金额！');
        }else{
            if(!checkPrice($updateData['pay_money'])){
                return array('status'=>-1,'msg'=>'付款金额格式不正确！');
            }
        }
        //是否转至财务
        if(I('POST.buy_complete')){
            $updateData['buy_complete'] = 1;
            $text = '采购对接一条信息并转至财务';
        }else{
            $text = '暂存一条采购信息';
        }
        $updateData['purchase_time']  = time();//对接时间
        $res = $this->updateData('business_control',$updateData,array('contid'=>$contid));
        $this->addLog('business_control',M()->getLastSql(),$text,$contid,'');
        if($res){
            $data['where']['contid'] = $businessInfo['project_contid'];
            $data['where']['project'] = $businessInfo['project'];
            $data['business'] = $updateData;
            Vendor('TecevApi.TecevApi');
            $api = new \TecevApi();
            $res = $api->syncBuyMaterielData(C('PROJECT_URL')[$businessInfo['project']],'procureInfo',$businessInfo['project'],C('PROJECT_APP_KEY')[$businessInfo['project']],$data);
            //对返回数据签名进行验证
            $signed = $api->getSign('procureInfo',$res['requestTime'],$businessInfo['project']);
            if($res['signed'] == $signed){
                //验证通过
                if($res['resultCode'] == 200){
                    //项目组已同步采购信息,记录同步成功信息
                    $this->updateData('business_control',array('purchase'=>1),array('contid'=>$contid));
                }else{
                    $this->updateData('business_control',array('purchase'=>0),array('contid'=>$contid));
                }
            }
            return array('status'=>1,'msg'=>'采购对接成功！');
        }else{
            return array('status'=>-1,'msg'=>'采购对接失败，请稍后再试！');
        }
    }

    /**
     * Notes: 保存财务对接信息
     */
    public function saveFinanInfo()
    {
        $contid = I('POST.contid');
        //查询该业务单信息
        $businessInfo = $this->DB_get_one('business_control','project_contid,project',array('contid'=>$contid));
        if(!$businessInfo){
            return array('status'=>-1,'msg'=>'查找不到该业务单信息！');
        }
        $output_date = I('POST.outputDate');//销项票日期
        $updateData['output_type'] = I('POST.output_type');//发票类型
        $updateData['output_rate'] = I('POST.output_rate');//销项票税率
        $updateData['output_content'] = I('POST.output_content');//销项票内容
        $updateData['tax_amount'] = I('POST.tax_amount');//含税金额
        $updateData['non_tax_amount'] = I('POST.non_tax_amount');//不含税金额
        $updateData['cancellation_amount'] = I('POST.cancellation_amount');//核销金额
        $updateData['non_repayment_amount'] = I('POST.non_repayment_amount');//未回款金额
        if($output_date != ''){
            $updateData['output_date'] = $output_date;
        }else{
            $updateData['output_date'] = '0000-00-00';
        }
        $updateData['output_num'] = trim(I('POST.outputNum'));//销项票编号
        $updateData['output_info'] = trim(I('POST.outputInfo'));//销项票物流信息
        $payment_date = I('POST.paymentDate');//回款日期
        if($payment_date != ''){
            $updateData['payment_date'] = $payment_date;
        }else{
            $updateData['payment_date'] = '0000-00-00';
        }
        $estimate_profit = trim(I('POST.estimateProfit'));//预估毛利
        if($estimate_profit != ''){
            if(!checkPrice($estimate_profit)){
                return array('status'=>-1,'msg'=>'预估毛利金额格式不正确！');
            }
            $updateData['estimate_profit'] = $estimate_profit;
        }else{
            $updateData['estimate_profit'] = $estimate_profit;
        }
        $settlement_date = I('POST.settlementDate');//结算日期
        if($settlement_date != ''){
            $updateData['settlement_date'] = $settlement_date;
        }else{
            $updateData['settlement_date'] = '0000-00-00';
        }
        $settlement_amount = trim(I('POST.settlementAmount'));//结算额度
        if($settlement_amount != ''){
            if(!checkPrice($settlement_amount)){
                return array('status'=>-1,'msg'=>'结算额度金额格式不正确！');
            }
            $updateData['settlement_amount'] = $settlement_amount;
        }
        $allover_date = I('POST.alloverDate');//结单日期
        if($allover_date != ''){
            $updateData['allover_date'] = $allover_date;
        }else{
            $updateData['allover_date'] = '0000-00-00';
        }
        //是否结束业务单
        if(I('POST.all_complete')){
            $updateData['all_complete'] = 1;
        }
        $updateData['finance_time']  = time();//对接时间
        $res = $this->updateData('business_control',$updateData,array('contid'=>$contid));
        //日志行为记录文字
        $text = getLogText('saveFinalInfoLogText');
        $this->addLog('business_control',M()->getLastSql(),$text,$contid,'');
        if($res){
            $data['where']['contid'] = $businessInfo['project_contid'];
            $data['where']['project'] = $businessInfo['project'];
            $data['business'] = $updateData;
            Vendor('TecevApi.TecevApi');
            $api = new \TecevApi();
            $res = $api->syncBuyMaterielData(C('PROJECT_URL')[$businessInfo['project']],'procureInfo',$businessInfo['project'],C('PROJECT_APP_KEY')[$businessInfo['project']],$data);
            //对返回数据签名进行验证
            $signed = $api->getSign('procureInfo',$res['requestTime'],$businessInfo['project']);
            if($res['signed'] == $signed){
                //验证通过
                if($res['resultCode'] == 200){
                    //项目组已同步财务信息,记录同步成功信息
                    $this->updateData('business_control',array('purchase'=>1),array('contid'=>$contid));
                }else{
                    $this->updateData('business_control',array('purchase'=>0),array('contid'=>$contid));
                }
            }
            return array('status'=>1,'msg'=>'财务对接成功！');
        }else{
            return array('status'=>-1,'msg'=>'财务对接失败，请稍后再试！');
        }
    }

    /**
     * Notes: 修改业务
     *
     */
    public function editBusiness()
    {
        $contid = I('POST.contid');
        //查询该业务单是否存在
        $businessInfo = $this->DB_get_one('business_control','contid,project_contid,project',array('contid'=>$contid));
        if(!$businessInfo){
            return array('status'=>-1,'msg'=>'查询不到该业务单信息！');
        }
        //查询该业务单是否允许维修
        $canEdit = 1;
        if(!$canEdit){
            return array('status'=>-1,'msg'=>'该业务单暂不允许修改！');
        }
        $editData['process_num'] = trim(I('POST.process_num'));
        $editData['repair_num'] = trim(I('POST.repair_num'));
        $editData['type'] = I('POST.type');
        if($editData['type'] == 2){
            $editData['customer_name'] = trim(I('POST.customer_name'));
            $editData['customer_contacts'] = trim(I('POST.customer_contacts'));
            $editData['customer_tel'] = trim(I('POST.customer_tel'));
            if(!judgeEmpty($editData['customer_name'])){
                return array('status'=>-1,'msg'=>'客户名称不能为空！');
            }
            if(!judgeEmpty($editData['customer_contacts'])){
                return array('status'=>-1,'msg'=>'客户联系人不能为空！');
            }
            if(!judgeEmpty($editData['customer_tel'])){
                return array('status'=>-1,'msg'=>'客户联系电话不能为空！');
            }
        }
        $editData['desc'] = trim(I('POST.desc'));
        $editData['supplier'] = trim(I('POST.supplier'));
        $editData['supplier_contacts'] = trim(I('POST.supplier_contacts'));
        $editData['supplier_tel'] = trim(I('POST.supplier_tel'));
        $editData['supplier_account'] = trim(I('POST.supplier_account'));
        $editData['supplier_bank'] = trim(I('POST.supplier_bank'));
        $editData['supplier_bank_num'] = trim(I('POST.supplier_bank_num'));
        $editData['edit_user'] = session('username');
        $editData['create_time'] = strtotime(I('POST.createdate'));
        $editData['edit_time'] = time();
        if(!judgeEmpty($editData['desc'])){
            return array('status'=>-1,'msg'=>'业务描述不能为空！');
        }
        if(!judgeEmpty($editData['create_time'])){
            return array('status'=>-1,'msg'=>'申请日期不能为空！');
        }
        if(!judgeEmpty($editData['supplier'])){
            return array('status'=>-1,'msg'=>'供应商名称不能为空！');
        }
        if(!judgeEmpty($editData['supplier_contacts'])){
            return array('status'=>-1,'msg'=>'供应商联系人不能为空！');
        }
        if(!judgeEmpty($editData['supplier_tel'])){
            return array('status'=>-1,'msg'=>'供应商联系电话不能为空！');
        }
        if(!judgeEmpty($editData['supplier_account'])){
            return array('status'=>-1,'msg'=>'账户名称不能为空！');
        }
        if(!judgeEmpty($editData['supplier_bank'])){
            return array('status'=>-1,'msg'=>'开户银行不能为空！');
        }
        if(!judgeEmpty($editData['supplier_bank_num'])){
            return array('status'=>-1,'msg'=>'供应商账号不能为空！');
        }
        //查询当前用户是否有权限进行跟进业务
        $follow = get_menu($this->MODULE, 'Repair', 'followBusinessControl');
        if($follow){
            $editData['sale_price'] = trim(I('POST.salePrice'));
            $editData['total_sale'] = trim(I('POST.totalSale'));
            $editData['allowance'] = trim(I('POST.allowance'));
            if(!judgeEmpty($editData['sale_price'])){
                return array('status'=>-1,'msg'=>'经销价不能为空！');
            }
            if(!judgeEmpty($editData['total_sale'])){
                return array('status'=>-1,'msg'=>'销售总额不能为空！');
            }
            if(!judgeEmpty($editData['allowance'])){
                return array('status'=>-1,'msg'=>'计提额度不能为空！');
            }
        }
        $matid = I('POST.matid');
        $title = I('POST.title');
        $is_depot = I('POST.is_depot');
        $depot = array();
        foreach($is_depot as $k => $v){
            if($v == '否'){
                $depot[$k] = 0;
            }elseif($v == '是'){
                $depot[$k] = 1;
            }

        }
        $factory = I('POST.factory');
        $model = I('POST.model');
        $num = I('POST.num');
        $price = I('POST.price');
        $unit = I('POST.unit');
        $gdate = I('POST.gdate');
        $invoice_type = I('POST.invoice_type');
        $invoice_rate = I('POST.invoice_rate');
        $invoice_content = I('POST.invoice_content');
        foreach($title as $val){
            if(!trim($val)){
                return array('status'=>-1,'msg'=>'物料/服务名称不能为空！');
            }
        }
        foreach($num as $val){
            if(!trim($val)){
                return array('status'=>-1,'msg'=>'物料/服务数量不能为空！');
            }
        }
        foreach($price as $val){
            if(!trim($val)){
                return array('status'=>-1,'msg'=>'物料/服务单价不能为空！');
            }
        }
        //修改业务数据
        $uid = $this->updateData('business_control',$editData,array('contid'=>$contid));
        //日志行为记录文字
        $text = getLogText('editBusinessLogText');
        $this->addLog('business_control',M()->getLastSql(),$text,$contid,'');
        if(!$uid){
            return array('status'=>-1,'msg'=>'修改业务失败，请稍后再试！');
        }
        //查询原有物料数据，与新数据做对比
        $oldMatids = $this->DB_get_one('business_materiel','group_concat(matid) as matid',array('contid'=>$contid));
        $oldMatidsArr = explode(',',$oldMatids['matid']);
        $delArr = array_diff($oldMatidsArr, $matid);
        if($delArr){
            //删除对应的matid物料
            $this->deleteData('business_materiel',array('matid'=>array('in',$delArr)));
        }
        //组织数据
        $editMateriel = [];
        $allMateriel = [];
        foreach ($title as $k=>$v){
            $editMateriel['title'] = $v;
            $editMateriel['contid'] = $contid;
            $editMateriel['is_depot'] = $depot[$k];
            $editMateriel['project_matid'] = 0;
            $editMateriel['model'] = $model[$k];
            $editMateriel['num'] = (int)$num[$k];
            $editMateriel['price'] = (float)$price[$k];
            $editMateriel['total_price'] = $editMateriel['num']*$editMateriel['price'];
            $editMateriel['unit'] = $unit[$k];
            $editMateriel['factory'] = $factory[$k];
            $editMateriel['guarantee_date'] = $gdate[$k];
            $editMateriel['is_invoice'] = 0;
            if($invoice_type[$k] == '无发票'){
                $addMateriel['is_invoice'] = 0;
            }else{
                $addMateriel['is_invoice'] = 1;
            }
            $editMateriel['invoice_type'] = $invoice_type[$k];
            $editMateriel['invoice_rate'] = $invoice_rate[$k];
            $editMateriel['invoice_content'] = $invoice_content[$k];
            if($matid[$k]){
                //对物料进行修改
                $this->updateData('business_materiel',$editMateriel,array('matid'=>$matid[$k]));
                $editMateriel['project_matid'] = $matid[$k];
            }else{
                //新增物料
                $id = $this->insertData('business_materiel',$editMateriel);
                $editMateriel['project_matid'] = $id;
            }
            $allMateriel[] = $editMateriel;
        }
        if(C('HEADQUARTERS') && $businessInfo['project_contid'] && $businessInfo['project']){
            //总部对业务模块进行修改，把修改数据同步到分院
            //总部  --> 分院
            $data['where']['contid'] = $businessInfo['project_contid'];
            $data['where']['project'] = $businessInfo['project'];
            $data['business'] = $editData;
            $data['materiel'] = $allMateriel;
            Vendor('TecevApi.TecevApi');
            $api = new \TecevApi();
            $res = $api->syncBuyMaterielData(C('PROJECT_URL')[$businessInfo['project']],'editBusiness',$businessInfo['project'],C('PROJECT_APP_KEY')[$businessInfo['project']],$data);
            //对返回数据签名进行验证
            $signed = $api->getSign('procureInfo',$res['requestTime'],$businessInfo['project']);
            if($res['signed'] == $signed){
                //验证通过
                if($res['resultCode'] == 200){
                    //项目组已同步财务信息,记录同步成功信息
                    $this->updateData('business_control',array('purchase'=>1),array('contid'=>$contid));
                }else{
                    $this->updateData('business_control',array('purchase'=>0),array('contid'=>$contid));
                }
            }
        }else{
            //分院修改业务数据，把数据同步到总部
            //分院  --> 总部
            $where['project_contid'] = $contid;
            $where['project'] = C('PROJECT_CODE');
            $data['where'] = $where;
            $data['business'] = $editData;
            $data['materiel'] = $allMateriel;
            Vendor('TecevApi.TecevApi');
            $api = new \TecevApi();
            $res = $api->syncBusinessData('editBusiness',C('PROJECT_CODE'),$data);
            //对返回数据签名进行验证
            $signed = $api->getSign('editBusiness',$res['requestTime'],C('PROJECT_CODE'));
            if($res['signed'] == $signed){
                //验证通过
                if($res['resultCode'] == 200){
                    //总部已同步修改成功,记录同步修改成功信息
                    $this->updateData('business_control',array('synchro_edit'=>1),array('contid'=>$contid));
                }
            }
        }
        return array('status'=>1,'msg'=>'修改业务成功！');
    }

    /**
     * notes: 获取所有OA编码
     */
    public function getAllOaNum()
    {
        $oa = $this->DB_get_all('business_control','process_num','','process_num','contid desc');
        $res = array();
        $i = 0;
        foreach ($oa as $k => $v) {
            $res[$i]['xh'] = $k+1;
            $res[$i]['process_num'] = $v['process_num'];
            $i++;
        }
        $arr = array();
        $arr['value'] = $res;
        return $arr;
    }

    /**
     * notes: 获取所有采购申请人
     */
    public function getBuyUser()
    {
        //查询有权限申报的menuid
        $menu = $this->DB_get_one('menu','menuid',array('name'=>'addBusinessControl'));
        $join[0] = " LEFT JOIN __USER_ROLE__ ON A.roleid = __USER_ROLE__.roleid";
        $join[1] = " LEFT JOIN __USER__ ON __USER_ROLE__.userid = __USER__.userid";
        $user = $this->DB_get_all_join('role_menu','A','sb_user.username as create_user',$join,array('A.menuid'=>$menu['menuid']),'','','');
        $oa = $this->DB_get_all('business_control','create_user','','create_user','contid desc');
        $all = array_merge($oa,$user);
        $key = 'create_user';//去重条件
        $tmp_arr = array();//声明数组
        foreach($all as $k => $v){
            if(in_array($v[$key], $tmp_arr)){
                unset($all[$k]);
            }else {
                $tmp_arr[] = $v[$key];
            }
        }
        $res = array();
        $i = 0;
        foreach ($all as $k => $v) {
            $res[$i]['xh'] = $k+1;
            $res[$i]['create_user'] = $v['create_user'];
            $i++;
        }
        $arr = array();
        $arr['value'] = $res;
        return $arr;
    }

    /**
     * notes: 获取所有采购申请人
     */
    public function getCustomer()
    {
        $oa = $this->DB_get_all('business_control','customer_name','','customer_name','contid desc');
        $res = array();
        $i = 0;
        foreach ($oa as $k => $v) {
            $res[$i]['xh'] = $k+1;
            $res[$i]['customer_name'] = $v['customer_name'];
            $i++;
        }
        $arr = array();
        $arr['value'] = $res;
        return $arr;
    }

    /**
     * notes: 获取所有采购申请人
     */
    public function getPayUser()
    {
        $oa = $this->DB_get_all('business_control','pay_user','','pay_user','contid desc');
        $res = array();
        $i = 0;
        foreach ($oa as $k => $v) {
            if($v['pay_user']){
                $res[$i]['xh'] = $k+1;
                $res[$i]['pay_user'] = $v['pay_user'];
                $i++;
            }
        }
        $arr = array();
        $arr['value'] = $res;
        return $arr;
    }

    /**
     * notes: 获取销项票编号
     */
    public function getOutputNum()
    {
        $oa = $this->DB_get_all('business_control','output_num','','output_num','contid desc');
        $res = array();
        $i = 0;
        foreach ($oa as $k => $v) {
            if($v['output_num']){
                $res[$i]['xh'] = $k+1;
                $res[$i]['output_num'] = $v['output_num'];
                $i++;
            }
        }
        $arr = array();
        $arr['value'] = $res;
        return $arr;
    }

    /**
     * Notes: 获取表格字段名的备注名
     * @param $table_name
     * @return mixed
     */
    public function getCloums($table_name,$showName = array(),$unShowKey = array())
    {
        $sql = "SHOW FULL COLUMNS FROM sb_" . $table_name;
        $rescolumns = M($table_name)->query($sql);
        foreach ($rescolumns as $k => $v) {
            if(in_array($v['Field'],$unShowKey)){
                continue;
            }
            if(mb_strpos($v['Comment'], '【') !== false){
                $commemt = mb_substr($v['Comment'],0,mb_strpos($v['Comment'], '【'));
                $showName[$v['Field']] = $commemt;
            }elseif(mb_strpos($v['Comment'], '[') !== false){
                $commemt = mb_substr($v['Comment'],0,mb_strpos($v['Comment'], '['));
                $showName[$v['Field']] = $commemt;
            }else{
                $showName[$v['Field']] = $v['Comment'];
            }
        }
        return $showName;
    }
}