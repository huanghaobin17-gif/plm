<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/17
 * Time: 10:38
 */

namespace Admin\Controller\BaseSetting;

use Admin\Controller\Login\CheckLoginController;
use Admin\Model\AssetsInfoModel;
use Admin\Model\BaseSettingModel;
use Admin\Model\UserModel;

class SmsModuleController extends CheckLoginController
{
    private $MODULE = 'BaseSetting';

    public function smsSetting()
    {
        if (IS_POST) {
            $action = I('POST.action');
            switch ($action) {
                default:
                    $BaseSettingModel = new BaseSettingModel();
                    $result = $BaseSettingModel->setSmsSetting();
                    $this->ajaxReturn($result, 'json');
                    break;
            }
        } else {
            $BaseSettingModel = new BaseSettingModel();
            $settings = $BaseSettingModel->getSmsSetting();
            if (!$settings) {
                //未配置过 获取默认配置
                $settings = $this->getSmsBaseSetting();
            } else {
                $checkSetting=$this->getSmsBaseSetting();
                foreach ($checkSetting as $checkKey=>$checkValue){
                    if($checkKey!='setting_open'){
                        foreach ($checkValue as $key=>$value){
                            if($key!='status'){
                                if(!$settings[$checkKey][$key]){
                                    $settings[$checkKey][$key]=$value;
                                }
                            }
                        }
                    }
                }
            }
            $this->assign('settings', $settings);
            $this->assign('smsSettingUrl', get_url());
            $this->display();
        }
    }


    //获取默认的短信配置
    private function getSmsBaseSetting()
    {
        //默认总配置未开启 需要用户有保存过后才记录 注：补充新的短信配置 请在对应的view也进行补充
        $settings['setting_open'] = C('SHUT_STATUS');

        //==============================维修配置 START==============================
        $settings['Repair']['status'] = C('OPEN_STATUS');

        //通知工程师接单
        $settings['Repair']['applyRepair']['content'] = '{department}科室,编号为{assnum}的设备申请报修，请处理';
        $settings['Repair']['applyRepair']['status'] = C('OPEN_STATUS');

        //通知派工员分配维修单
        $settings['Repair']['assigned']['content'] = '{department}科室,编号为{assnum}的设备申请报修，请及时分配工程师接单处理';
        $settings['Repair']['assigned']['status'] = C('OPEN_STATUS');

        //通知已有工程师接单
        $settings['Repair']['acceptOrder']['content'] = '您报修设备编号为{assnum}的设备已有工程师接单处理';
        $settings['Repair']['acceptOrder']['status'] = C('OPEN_STATUS');

        //通知库管处理出库申请
        $settings['Repair']['repairPartsOutApply']['content'] = '维修单{repnum}有配件出库申请，请及时处理';
        $settings['Repair']['repairPartsOutApply']['status'] = C('OPEN_STATUS');

        //通知工程师配件已出库
        $settings['Repair']['repairPartsOut']['content'] = '仓库已同意维修单{repnum}的配件出库申请，请及时领取';
        $settings['Repair']['repairPartsOut']['status'] = C('OPEN_STATUS');

        //通知审批用户审批
        $settings['Repair']['doApprove']['content'] = '{department}科室,维修单{repnum}需审批，请处理';
        $settings['Repair']['doApprove']['status'] = C('OPEN_STATUS');

        //通知已审批
        $settings['Repair']['repairApproveOver']['content'] = '维修单{repnum}已审批，审批结果：{approve_status}';
        $settings['Repair']['repairApproveOver']['status'] = C('OPEN_STATUS');

        //通知用户审批未通过
        $settings['Repair']['repairApproveOverFAIL']['content'] = '维修单{repnum}审批结果：{approve_status},请重新申请';
        $settings['Repair']['repairApproveOverFAIL']['status'] = C('OPEN_STATUS');

        //通知选择最终厂商
        $settings['Repair']['repairOffer']['content'] = '{department}科室,维修单{repnum}需报价，请处理';
        $settings['Repair']['repairOffer']['status'] = C('OPEN_STATUS');

        //通知已选最终厂商
        $settings['Repair']['repairOfferOver']['content'] = '维修单{repnum}已确认最终厂商，请继续进行维修处理';
        $settings['Repair']['repairOfferOver']['status'] = C('OPEN_STATUS');

        //通知报修用户验收
        $settings['Repair']['checkRepair']['content'] = '报修设备编号为{assnum}的设备工程师已维修处理结束，请及时验收';
        $settings['Repair']['checkRepair']['status'] = C('OPEN_STATUS');

        //通知工程师验收结果
        $settings['Repair']['checkRepairStatus']['content'] = '您维修编号为{assnum}的设备已被验收，验收结果：{over_status}';
        $settings['Repair']['checkRepairStatus']['status'] = C('OPEN_STATUS');
        //==============================维修配置 END================================


        //==============================巡查配置 START==============================
        $settings['Patrol']['status'] = C('OPEN_STATUS');

        //通知实施巡查计划
        $settings['Patrol']['doPatrolTask']['content'] = '计划{patrolname}已发布，开始时间{startdate},请及时处理';
        $settings['Patrol']['doPatrolTask']['status'] = C('OPEN_STATUS');
        //通知处理巡查计划审批
        $settings['Patrol']['doApprove']['content'] = '用户{applicant}申请巡查计划{patrolname}，请处理审批';
        $settings['Patrol']['doApprove']['status'] = C('OPEN_STATUS');
        //通知审批结果
        $settings['Patrol']['borrowrApproveOver']['content'] = '巡查计划{patrolname}，审批结果：{examine_status}';
        $settings['Patrol']['borrowrApproveOver']['status'] = C('OPEN_STATUS');
        
        //通知验收巡查计划
        $settings['Patrol']['checkPatrolTask']['content'] = '巡查任务{cyclenum}已实施完成，请及时验收';
        $settings['Patrol']['checkPatrolTask']['status'] = C('OPEN_STATUS');
        //通知确认异常设备报修
        $settings['Patrol']['confirmRepair']['content'] = '{department}科室设备{assets}巡查结果：需要报修，请登录确认转至维修';
        $settings['Patrol']['confirmRepair']['status'] = C('OPEN_STATUS');
        //==============================巡查配置 END================================


        //==============================借调配置 START==============================
        $settings['Borrow']['status'] = C('OPEN_STATUS');

        //通知处理借调审批
        $settings['Borrow']['doApprove']['content'] = '{apply_department}科室向{department}科室申请借调{assets}，请处理审批';
        $settings['Borrow']['doApprove']['status'] = C('OPEN_STATUS');

        //通知审批结果
        $settings['Borrow']['borrowrApproveOver']['content'] = '借调单{borrow_num}，审批结果：{examine_status}';
        $settings['Borrow']['borrowrApproveOver']['status'] = C('OPEN_STATUS');
//
//        //通知被借科室准备设备
//        $settings['Borrow']['borrowReadyAssets']['content'] = '科室{apply_department}借调设备{assets},请准备好设备';
//        $settings['Borrow']['borrowReadyAssets']['status'] = C('OPEN_STATUS');

        //通知被借调科室取消借调
        $settings['Borrow']['borrowNotApply']['content'] = '科室{apply_department}借调设备{assets}的申请已取消';
        $settings['Borrow']['borrowNotApply']['status'] = C('OPEN_STATUS');

        //通知被借科室借入验收情况
        $settings['Borrow']['borrowInCheck']['content'] = '科室{apply_department}已确认设备{assets}完好无损并且借入，预计在{estimate_back}归还';
        $settings['Borrow']['borrowInCheck']['status'] = C('OPEN_STATUS');

        //通知申请人科室已验收
        $settings['Borrow']['borrowGiveBack']['content'] = '科室{department}已确认设备{assets}完好无损并结束流程';
        $settings['Borrow']['borrowGiveBack']['status'] = C('OPEN_STATUS');
        //==============================借调配置 END================================


        //==============================外调配置 START==============================
        $settings['Outside']['status'] = C('OPEN_STATUS');

        //通知处理外调审批
        $settings['Outside']['doApprove']['content'] = '{department}科室申请外调设备{assets}，请处理审批';
        $settings['Outside']['doApprove']['status'] = C('OPEN_STATUS');

        //通知审批结果
        $settings['Outside']['outsideApproveOver']['content'] = '科室{department}外调设备{assets}，审批结果：{examine_status}';
        $settings['Outside']['outsideApproveOver']['status'] = C('OPEN_STATUS');
        //==============================外调配置 END================================

        //==============================计量配置 START==============================
        $settings['Metering']['status'] = C('OPEN_STATUS');

        //通知执行计量任务
        $settings['Metering']['setMeteringResult']['content'] = '科室{department}设备{assets}制定了计量计划，编号{plan_num}，下次待检日期{next_date}';
        $settings['Metering']['setMeteringResult']['status'] = C('OPEN_STATUS');
        //==============================计量配置 END================================

        //==============================采购配置 START==============================
        $settings['Purchases']['status'] = C('OPEN_STATUS');

        //通知科室上报审批
        $settings['Purchases']['purchasePlanApprove']['content'] = '科室{department}上报了采购计划{project_name}，请审批';
        $settings['Purchases']['purchasePlanApprove']['status'] = C('OPEN_STATUS');

        //通知上报审批结果
        $settings['Purchases']['purchasePlanApproveOver']['content'] = '上报计划{project_name}，审批结果：{approve_status}';
        $settings['Purchases']['purchasePlanApproveOver']['status'] = C('OPEN_STATUS');

        //通知采购申请审批
        $settings['Purchases']['approveApply']['content'] = '科室{department}申请采购计划{project_name}，请审批';
        $settings['Purchases']['approveApply']['status'] = C('OPEN_STATUS');

        //通知采购审批结果
        $settings['Purchases']['approveApplyOver']['content'] = '采购计划{project_name}，审批结果：{approve_status}';
        $settings['Purchases']['approveApplyOver']['status'] = C('OPEN_STATUS');

        //通知专家评审
        $settings['Purchases']['expertReview']['content'] = '采购申请{project_name}需评审，请及时处理';
        $settings['Purchases']['expertReview']['status'] = C('OPEN_STATUS');

        //通知进行标书评审
        $settings['Purchases']['tbApprove']['content'] = '采购申请{project_name}购买设备{assets}的标书需评审，请及时处理';
        $settings['Purchases']['tbApprove']['status'] = C('OPEN_STATUS');

        //通知进行标书已驳回
        $settings['Purchases']['tbApproveOver']['content'] = '采购申请{project_name}购买设备{assets}的标书需评审结果：{review_status}，请重新制定标书';
        $settings['Purchases']['tbApproveOver']['status'] = C('OPEN_STATUS');

        //通知提交标书
        $settings['Purchases']['tbSubmit']['content'] = '采购申请{project_name}购买设备{assets}的标书需评审结果：{review_status},请提交标书';
        $settings['Purchases']['tbSubmit']['status'] = C('OPEN_STATUS');

        //通知出库审批未通过
        $settings['Purchases']['notOutApproveOver']['content'] = '出库单{out_num}出库设备申请已被拒绝,请及时跟进';
        $settings['Purchases']['notOutApproveOver']['status'] = C('OPEN_STATUS');

        //通知参加安装调试
        $settings['Purchases']['debugReport']['content'] = '请于{installStartDate}至{installEendDate}到达{debug_area}参与调试';
        $settings['Purchases']['debugReport']['status'] = C('OPEN_STATUS');

        //通知讲师进行培训
        $settings['Purchases']['doTrain']['content'] = '请于{trainStartDate}至{trainEendDate}到达{train_area}进行{train_assets}设备的培训';
        $settings['Purchases']['doTrain']['status'] = C('OPEN_STATUS');

        //通知人员参加培训
        $settings['Purchases']['joinTrain']['content'] = '请于{trainStartDate}至{trainEendDate}到达{train_area}参加{train_assets}设备的培训';
        $settings['Purchases']['joinTrain']['status'] = C('OPEN_STATUS');
        //==============================采购配置 END================================


        //==============================质控配置 START==============================
        $settings['Qualities']['status'] = C('OPEN_STATUS');

        //通知执行质控
        $settings['Qualities']['startQualityPlan']['content'] = '{plan_name}预计{do_date}执行{assets}的质控计划,请按时处理';
        $settings['Qualities']['startQualityPlan']['status'] = C('OPEN_STATUS');

        //通知质控计划暂停
        $settings['Qualities']['stopQualityPlan']['content'] = '{plan_name}已被{stop_username}暂停';
        $settings['Qualities']['stopQualityPlan']['status'] = C('OPEN_STATUS');

        //通知计划将要逾期
        $settings['Qualities']['noticeDoQualityPlan']['content'] = '计划{plan_name}将要逾期,截止日期{end_date}，请及时录入';
        $settings['Qualities']['noticeDoQualityPlan']['status'] = C('OPEN_STATUS');

        //当日质控信息反馈
        $settings['Qualities']['feedbackQuality']['content'] = '{hospital}今日完成录入的计划数量{completeNum},待录入数量{toBeDoneNum}';
        $settings['Qualities']['feedbackQuality']['status'] = C('OPEN_STATUS');
        //==============================质控配置 END================================

        //==============================附属设备分配配置 START=======================
        $settings['Subsidiary']['status'] = C('OPEN_STATUS');

        //通知处理外调审批
        $settings['Subsidiary']['doApprove']['content'] = '{department}科室申请分配附属设备{assets}，请处理审批';
        $settings['Subsidiary']['doApprove']['status'] = C('OPEN_STATUS');

        //通知审批结果
        $settings['Subsidiary']['subsidiaryApproveOver']['content'] = '科室{department}分配附属设备{assets}，审批结果：{approve_status}';
        $settings['Subsidiary']['subsidiaryApproveOver']['status'] = C('OPEN_STATUS');

        //通知验收附属设备
        $settings['Subsidiary']['subsidiaryCheck']['content'] = '分配附属设备{assets}审批已通过，请验收';
        $settings['Subsidiary']['subsidiaryCheck']['status'] = C('OPEN_STATUS');
        //==============================附属设备分配配置 END=========================

        //==============================转科配置 START==============================
        $settings['Transfer']['status'] = C('OPEN_STATUS');

        //通知审批人审批
        $settings['Transfer']['approveTransfer']['content'] = '科室{tranout_department}，编号{assnum}的设备申请转入科室{tranin_department}，请您进行审批';
        $settings['Transfer']['approveTransfer']['status'] = C('OPEN_STATUS');

        //通知审批结果
        $settings['Transfer']['approveTransferStatus']['content'] = '转科单号{transfer_num}的审批结果为{approve_status}';
        $settings['Transfer']['approveTransferStatus']['status'] = C('OPEN_STATUS');

        //通知转入科室人验收
        $settings['Transfer']['checkTransfer']['content'] = '科室{tranout_department}，编号为assnum}的设备申请转入科室{tranin_department}，请您进行验收';
        $settings['Transfer']['checkTransfer']['status'] = C('OPEN_STATUS');

        //通知验收结果
        $settings['Transfer']['checkTransferStatus']['content'] = '转科单号{transfer_num}的验收结果为{check_status}';
        $settings['Transfer']['checkTransferStatus']['status'] = C('OPEN_STATUS');
        //==============================转科配置 END==============================

        //==============================报废配置 START==============================
        $settings['Scrap']['status'] = C('OPEN_STATUS');

        //通知审批人审批
        $settings['Scrap']['approveScrap']['content'] = '编号为{assnum}的设备{assets}申请报废，请您进行审批';
        $settings['Scrap']['approveScrap']['status'] = C('OPEN_STATUS');

        //通知申请人审批结果
        $settings['Scrap']['approveScrapStatus']['content'] = '报废编号为{scrap_num}设备的审批结果为{approve_status}';
        $settings['Scrap']['approveScrapStatus']['status'] = C('OPEN_STATUS');
        //==============================报废配置 END==============================
        return $settings;
    }

}