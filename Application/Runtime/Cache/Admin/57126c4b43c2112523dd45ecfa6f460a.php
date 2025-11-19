<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/tecev/start/layui/css/layui.css">
    <link rel="stylesheet" href="/Public/css/tecev.css">
    <link rel="stylesheet" href="/Public/css/common.css">
    <link rel="stylesheet" href="/Public/css/formselects.css">
    <link rel="stylesheet" href="/Public/css/iconfont.css">
    <link rel="stylesheet" href="/Public/tecev-icon/iconfont.css">
    <link rel="stylesheet" href="/tecev/start/layui/icon/icon.css" media="all">
    <script src="/Public/js/jquery-3.7.0.min.js"></script>
    <script src="/tecev/start/layui/layui.js"></script>
    <script src="/Public/js/common.js?v=<?php echo mt_rand(1,54561);?>" type="text/javascript"></script>
    <script src="/Public/js/ajax.js?v=<?php echo mt_rand(1,54561);?>"></script>
    <script src="/Public/js/placeholder.js"></script>
    <script type="text/javascript" language="JavaScript">
    //资产状态
    var ASSETS_STATUS_USE='<?php echo(C("ASSETS_STATUS_REPAIR")); ?>';
    var ASSETS_STATUS_REPAIR='<?php echo(C("ASSETS_STATUS_REPAIR")); ?>';
    var ASSETS_STATUS_SCRAP='<?php echo(C("ASSETS_STATUS_SCRAP")); ?>';
    var ASSETS_STATUS_USE_NAME='<?php echo(C("ASSETS_STATUS_USE_NAME")); ?>';
    var ASSETS_STATUS_REPAIR_NAME='<?php echo(C("ASSETS_STATUS_REPAIR_NAME")); ?>';
    var ASSETS_STATUS_SCRAP_NAME='<?php echo(C("ASSETS_STATUS_SCRAP_NAME")); ?>';

    var ASSETS_FIRST_CODE_NO='<?php echo(C("ASSETS_FIRST_CODE_NO")); ?>';
    var ASSETS_FIRST_CODE_YES='<?php echo(C("ASSETS_FIRST_CODE_YES")); ?>';
    var ASSETS_FIRST_CODE_NO_NAME='<?php echo(C("ASSETS_FIRST_CODE_NO_NAME")); ?>';
    var ASSETS_FIRST_CODE_YES_NAME='<?php echo(C("ASSETS_FIRST_CODE_YES_NAME")); ?>';

    var ASSETS_SPEC_CODE_NO='<?php echo(C("ASSETS_SPEC_CODE_NO")); ?>';
    var ASSETS_SPEC_CODE_YES='<?php echo(C("ASSETS_SPEC_CODE_YES")); ?>';
    var ASSETS_SPEC_CODE_NO_NAME='<?php echo(C("ASSETS_SPEC_CODE_NO_NAME")); ?>';
    var ASSETS_SPEC_CODE_YES_NAME='<?php echo(C("ASSETS_SPEC_CODE_YES_NAME")); ?>';

    //设备现状 sb_patrol_execute
    var ASSETS_STATUS_NORMAL='<?php echo(C("ASSETS_STATUS_NORMAL")); ?>';
    var ASSETS_STATUS_SMALL_PROBLEM='<?php echo(C("ASSETS_STATUS_SMALL_PROBLEM")); ?>';
    var ASSETS_STATUS_NORMAL_NAME='<?php echo(C("ASSETS_STATUS_NORMAL_NAME")); ?>';
    var ASSETS_STATUS_SMALL_PROBLEM_NAME='<?php echo(C("ASSETS_STATUS_SMALL_PROBLEM_NAME")); ?>';
    var ASSETS_STATUS_FAULT_NAME='<?php echo(C("ASSETS_STATUS_FAULT_NAME")); ?>';
    var ASSETS_STATUS_ABNORMAL_NAME='<?php echo(C("ASSETS_STATUS_ABNORMAL_NAME")); ?>';
    var ASSETS_STATUS_IN_MAINTENANCE_NAME='<?php echo(C("ASSETS_STATUS_IN_MAINTENANCE_NAME")); ?>';
    var ASSETS_STATUS_SCRAPPED_NAME='<?php echo(C("ASSETS_STATUS_SCRAPPED_NAME")); ?>';
    var ASSETS_STATUS_NOT_OPERATION_NAME='<?php echo(C("ASSETS_STATUS_NOT_OPERATION_NAME")); ?>';

    //维修 确认维修状态设置 sb_confirm_add_repair
    var ASSETS_STATUS_IN_MAINTENANCE_SNAME='<?php echo(C("ASSETS_STATUS_IN_MAINTENANCE_SNAME")); ?>';
    var SWITCH_REPAIR_UNCONFIRMED='<?php echo(C("SWITCH_REPAIR_UNCONFIRMED")); ?>';
    var SWITCH_REPAIR_CONFIRM='<?php echo(C("SWITCH_REPAIR_CONFIRM")); ?>';
    var SWITCH_REPAIR_UNCONFIRMED_NAME='<?php echo(C("SWITCH_REPAIR_UNCONFIRMED_NAME")); ?>';
    var ASSETS_STATUS_SCRAPPED_SNAME='<?php echo(C("ASSETS_STATUS_SCRAPPED_SNAME")); ?>';
    var SWITCH_REPAIR_CONFIRM_NAME='<?php echo(C("SWITCH_REPAIR_CONFIRM_NAME")); ?>';

    //维修状态 sb_repair
    var REPAIR_HAVE_REPAIRED='<?php echo(C("REPAIR_HAVE_REPAIRED")); ?>';
    var REPAIR_RECEIPT='<?php echo(C("REPAIR_RECEIPT")); ?>';
    var REPAIR_HAVE_OVERHAULED='<?php echo(C("REPAIR_HAVE_OVERHAULED")); ?>';
    var REPAIR_QUOTATION='<?php echo(C("REPAIR_QUOTATION")); ?>';
    var REPAIR_AUDIT='<?php echo(C("REPAIR_AUDIT")); ?>';
    var REPAIR_MAINTENANCE='<?php echo(C("REPAIR_MAINTENANCE")); ?>';
    var REPAIR_MAINTENANCE_COMPLETION='<?php echo(C("REPAIR_MAINTENANCE_COMPLETION")); ?>';
    var REPAIR_ALREADY_ACCEPTED='<?php echo(C("REPAIR_ALREADY_ACCEPTED")); ?>';
    var REPAIR_HAVE_REPAIRED_NAME='<?php echo(C("REPAIR_HAVE_REPAIRED_NAME")); ?>';
    var REPAIR_RECEIPT_NAME='<?php echo(C("REPAIR_RECEIPT_NAME")); ?>';
    var REPAIR_HAVE_OVERHAULED_NAME='<?php echo(C("REPAIR_HAVE_OVERHAULED_NAME")); ?>';
    var REPAIR_QUOTATION_NAME='<?php echo(C("REPAIR_QUOTATION_NAME")); ?>';
    var REPAIR_AUDIT_NAME='<?php echo(C("REPAIR_AUDIT_NAME")); ?>';
    var REPAIR_MAINTENANCE_NAME='<?php echo(C("REPAIR_MAINTENANCE_NAME")); ?>';
    var REPAIR_MAINTENANCE_COMPLETION_NAME='<?php echo(C("REPAIR_MAINTENANCE_COMPLETION_NAME")); ?>';
    var REPAIR_ALREADY_ACCEPTED_NAME='<?php echo(C("REPAIR_ALREADY_ACCEPTED_NAME")); ?>';

    //维修类型 sb_repair
    var REPAIR_TYPE_IS_STUDY='<?php echo(C("REPAIR_TYPE_IS_STUDY")); ?>';
    var REPAIR_TYPE_IS_GUARANTEE='<?php echo(C("REPAIR_TYPE_IS_GUARANTEE")); ?>';
    var REPAIR_TYPE_THIRD_PARTY='<?php echo(C("REPAIR_TYPE_THIRD_PARTY")); ?>';
    var REPAIR_TYPE_IS_SCENE='<?php echo(C("REPAIR_TYPE_IS_SCENE")); ?>';
    var REPAIR_TYPE_IS_STUDY_NAME='<?php echo(C("REPAIR_TYPE_IS_STUDY_NAME")); ?>';
    var REPAIR_TYPE_IS_GUARANTEE_NAME='<?php echo(C("REPAIR_TYPE_IS_GUARANTEE_NAME")); ?>';
    var REPAIR_TYPE_THIRD_PARTY_NAME='<?php echo(C("REPAIR_TYPE_THIRD_PARTY_NAME")); ?>';
    var REPAIR_TYPE_IS_SCENE_NAME='<?php echo(C("REPAIR_TYPE_IS_SCENE_NAME")); ?>';
    var REPAIR_OVER_STATUS_SUCCESSFUL='<?php echo(C("REPAIR_OVER_STATUS_SUCCESSFUL")); ?>';
    var REPAIR_OVER_STATUS_FAIL='<?php echo(C("REPAIR_OVER_STATUS_FAIL")); ?>';
    var REPAIR_NOT_ADD_NEW_PARTS='<?php echo(C("REPAIR_NOT_ADD_NEW_PARTS")); ?>';
    var REPAIR_ADD_NEW_PARTS='<?php echo(C("REPAIR_ADD_NEW_PARTS")); ?>';
    var REPAIR_QUOTED_PRICE_PARTS='<?php echo(C("REPAIR_QUOTED_PRICE_PARTS")); ?>';

    //维修审核状态
    var REPAIR_IS_CHECK_ADOPT='<?php echo(C("REPAIR_IS_CHECK_ADOPT")); ?>';
    var REPAIR_IS_CHECK_NOT_THROUGH='<?php echo(C("REPAIR_IS_CHECK_NOT_THROUGH")); ?>';
    var REPAIR_IS_NOTCHECK='<?php echo(C("REPAIR_IS_NOTCHECK")); ?>';
    var REPAIR_IS_CHECK_ADOPT_NAME='<?php echo(C("REPAIR_IS_CHECK_ADOPT_NAME")); ?>';
    var REPAIR_IS_CHECK_NOT_THROUGH_NAME='<?php echo(C("REPAIR_IS_CHECK_NOT_THROUGH_NAME")); ?>';
    var REPAIR_IS_NOTCHECK_NAME='<?php echo(C("REPAIR_IS_NOTCHECK_NAME")); ?>';

    //专科验收
    var TRANSFER_IS_CHECK_ADOPT='<?php echo(C("TRANSFER_IS_CHECK_ADOPT")); ?>';
    var TRANSFER_IS_CHECK_NOT_THROUGH='<?php echo(C("TRANSFER_IS_CHECK_NOT_THROUGH")); ?>';
    var TRANSFER_IS_NOTCHECK='<?php echo(C("TRANSFER_IS_NOTCHECK")); ?>';
    var TRANSFER_IS_CHECK_ADOPT_NAME='<?php echo(C("TRANSFER_IS_CHECK_ADOPT_NAME")); ?>';
    var TRANSFER_IS_CHECK_NOT_THROUGH_NAME='<?php echo(C("TRANSFER_IS_CHECK_NOT_THROUGH_NAME")); ?>';
    var TRANSFER_IS_NOTCHECK_NAME='<?php echo(C("TRANSFER_IS_NOTCHECK_NAME")); ?>';

    //报废审核状态
    var SCRAP_IS_CHECK_ADOPT='<?php echo(C("SCRAP_IS_CHECK_ADOPT")); ?>';
    var SCRAP_IS_CHECK_NOT_THROUGH='<?php echo(C("SCRAP_IS_CHECK_NOT_THROUGH")); ?>';
    var SCRAP_IS_NOTCHECK='<?php echo(C("SCRAP_IS_NOTCHECK")); ?>';
    var SCRAP_IS_CHECK_ADOPT_NAME='<?php echo(C("SCRAP_IS_CHECK_ADOPT_NAME")); ?>';
    var SCRAP_IS_CHECK_NOT_THROUGH_NAME='<?php echo(C("SCRAP_IS_CHECK_NOT_THROUGH_NAME")); ?>';
    var SCRAP_IS_NOTCHECK_NAME='<?php echo(C("SCRAP_IS_NOTCHECK_NAME")); ?>';

    //巡查保养级别设置
    var PATROL_LEVEL_DC='<?php echo(C("PATROL_LEVEL_DC")); ?>';
    var PATROL_LEVEL_RC='<?php echo(C("PATROL_LEVEL_RC")); ?>';
    var PATROL_LEVEL_PM='<?php echo(C("PATROL_LEVEL_PM")); ?>';
    var PATROL_LEVEL_RC_ALIAS_NAME='<?php echo(C("PATROL_LEVEL_RC_ALIAS_NAME")); ?>';
    var PATROL_LEVEL_DC_ALIAS_NAME='<?php echo(C("PATROL_LEVEL_DC_ALIAS_NAME")); ?>';
    var PATROL_LEVEL_PM_ALIAS_NAME='<?php echo(C("PATROL_LEVEL_PM_ALIAS_NAME")); ?>';
    var PATROL_LEVEL_NAME_RC='<?php echo(C("PATROL_LEVEL_NAME_RC")); ?>';
    var PATROL_LEVEL_NAME_DC='<?php echo(C("PATROL_LEVEL_NAME_DC")); ?>';
    var PATROL_LEVEL_NAME_PM='<?php echo(C("PATROL_LEVEL_NAME_PM")); ?>';

    //周期计划状态 sb_patrol_plan_cycle
    var PLAN_CYCLE_STANDBY='<?php echo(C("PLAN_CYCLE_STANDBY")); ?>';
    var PLAN_CYCLE_EXECUTION='<?php echo(C("PLAN_CYCLE_EXECUTION")); ?>';
    var PLAN_CYCLE_COMPLETE='<?php echo(C("PLAN_CYCLE_COMPLETE")); ?>';
    var PLAN_CYCLE_CHECK='<?php echo(C("PLAN_CYCLE_CHECK")); ?>';
    var PLAN_CYCLE_STANDBY_NAME='<?php echo(C("PLAN_CYCLE_STANDBY_NAME")); ?>';
    var PLAN_CYCLE_EXECUTION_NAME='<?php echo(C("PLAN_CYCLE_EXECUTION_NAME")); ?>';
    var PLAN_CYCLE_COMPLETE_NAME='<?php echo(C("PLAN_CYCLE_COMPLETE_NAME")); ?>';
    var PLAN_CYCLE_CHECK_ACCEPTANCE_NAME='<?php echo(C("PLAN_CYCLE_CHECK_ACCEPTANCE_NAME")); ?>';
    var PLAN_CYCLE_CHECK_NAME='<?php echo(C("PLAN_CYCLE_CHECK_NAME")); ?>';
    var PLAN_CYCLE_END_NAME='<?php echo(C("PLAN_CYCLE_END_NAME")); ?>';
    var PLAN_CYCLE_OVERDUE_NAME='<?php echo(C("PLAN_CYCLE_OVERDUE_NAME")); ?>';

    //周期计划发布状态 sb_patrol_plan_cycle
    var PLAN_NOT_RELEASE='<?php echo(C("PLAN_NOT_RELEASE")); ?>';
    var PLAN_IS_RELEASE='<?php echo(C("PLAN_IS_RELEASE")); ?>';
    var PLAN_NOT_RELEASE_NAME='<?php echo(C("PLAN_NOT_RELEASE_NAME")); ?>';
    var PLAN_IS_RELEASE_NAME='<?php echo(C("PLAN_IS_RELEASE_NAME")); ?>';

    //设备是否转至报修 sb_patrol_execute
    var ASSETS_TO_REPAIR='<?php echo(C("ASSETS_TO_REPAIR")); ?>';
    var ASSETS_NOT_REPAIR='<?php echo(C("ASSETS_NOT_REPAIR")); ?>';
    var ASSETS_TO_REPAIR_NAME='<?php echo(C("ASSETS_TO_REPAIR_NAME")); ?>';
    var ASSETS_NOT_REPAIR_NAME='<?php echo(C("ASSETS_NOT_REPAIR_NAME")); ?>';

    //巡查保养状态 sb_patrol_execute
    var MAINTAIN_EXECUTION='<?php echo(C("MAINTAIN_EXECUTION")); ?>';
    var MAINTAIN_EXECUTION_NAME='<?php echo(C("MAINTAIN_EXECUTION_NAME")); ?>';
    var MAINTAIN_PATROL_NAME='<?php echo(C("MAINTAIN_PATROL_NAME")); ?>';
    var MAINTAIN_COMPLETE_NAME='<?php echo(C("MAINTAIN_COMPLETE_NAME")); ?>';

    //巡查保养检查状态patrol_examine_all AND patrol_examine_one
    var CYCLE_STANDBY='<?php echo(C("CYCLE_STANDBY")); ?>';
    var CYCLE_COMPLETE='<?php echo(C("CYCLE_COMPLETE")); ?>';
    var CYCLE_STANDBY_NAME='<?php echo(C("CYCLE_STANDBY_NAME")); ?>';
    var CYCLE_EXECUTION_NAME='<?php echo(C("CYCLE_EXECUTION_NAME")); ?>';
    var CYCLE_COMPLETE_NAME='<?php echo(C("CYCLE_COMPLETE_NAME")); ?>';



    var OPEN_STATUS='<?php echo(C("OPEN_STATUS")); ?>';
    var SHUT_STATUS='<?php echo(C("SHUT_STATUS")); ?>';
    var YES_STATUS='<?php echo(C("YES_STATUS")); ?>';
    var NO_STATUS='<?php echo(C("NO_STATUS")); ?>';
    var DO_STATUS='<?php echo(C("DO_STATUS")); ?>';
    var NOT_DO_STATUS='<?php echo(C("NOT_DO_STATUS")); ?>';
    var NOTHING_STATUS='<?php echo(C("NOTHING_STATUS")); ?>';

    var SHUT_STATUS_DO='<?php echo(C("SHUT_STATUS_DO")); ?>';

    //维保性质 sb_assets_insurance
    var INSURANCE_IS_GUARANTEE='<?php echo(C("INSURANCE_IS_GUARANTEE")); ?>';
    var INSURANCE_THIRD_PARTY='<?php echo(C("INSURANCE_THIRD_PARTY")); ?>';
    var INSURANCE_IS_GUARANTEE_NAME='<?php echo(C("INSURANCE_IS_GUARANTEE_NAME")); ?>';
    var INSURANCE_THIRD_PARTY_NAME='<?php echo(C("INSURANCE_THIRD_PARTY_NAME")); ?>';
    var FACTORY_WARRANTY_NAME='<?php echo(C("FACTORY_WARRANTY_NAME")); ?>';

    //维保使用状态 sb_assets_insurance
    var INSURANCE_STATUS_USE='<?php echo(C("INSURANCE_STATUS_USE")); ?>';
    var INSURANCE_STATUS_DE_PAUL='<?php echo(C("INSURANCE_STATUS_DE_PAUL")); ?>';
    var INSURANCE_STATUS_NOT_RIGHT_NOW='<?php echo(C("INSURANCE_STATUS_NOT_RIGHT_NOW")); ?>';
    var INSURANCE_STATUS_USE_NAME='<?php echo(C("INSURANCE_STATUS_USE_NAME")); ?>';
    var INSURANCE_STATUS_DE_PAUL_NAME='<?php echo(C("INSURANCE_STATUS_DE_PAUL_NAME")); ?>';
    var INSURANCE_STATUS_NOT_RIGHT_NOW_NAME='<?php echo(C("INSURANCE_STATUS_NOT_RIGHT_NOW_NAME")); ?>';

    //借调状态
    var BORROW_STATUS_NOT_APPLY='<?php echo(C("BORROW_STATUS_NOT_APPLY")); ?>';
    var BORROW_STATUS_GIVE_BACK='<?php echo(C("BORROW_STATUS_GIVE_BACK")); ?>';

    //上面部分需要找时间去除部分 很多事不需要的 需要在JS或HTML追加状态的补充在下面

    var CONTRACT_TYPE_SUPPLIER='<?php echo(C("CONTRACT_TYPE_SUPPLIER")); ?>';
    var CONTRACT_TYPE_REPAIR='<?php echo(C("CONTRACT_TYPE_REPAIR")); ?>';
    var CONTRACT_TYPE_INSURANCE='<?php echo(C("CONTRACT_TYPE_INSURANCE")); ?>';
    var CONTRACT_TYPE_RECORD_ASSETS='<?php echo(C("CONTRACT_TYPE_RECORD_ASSETS")); ?>';

    var OUTSIDE_STATUS_COMPLETE='<?php echo(C("OUTSIDE_STATUS_COMPLETE")); ?>';
    var BORROW_STATUS_COMPLETE='<?php echo(C("BORROW_STATUS_COMPLETE")); ?>';


    var ASSETS_STATUS_FAULT='<?php echo(C("ASSETS_STATUS_FAULT")); ?>';
    var ASSETS_STATUS_ABNORMAL='<?php echo(C("ASSETS_STATUS_ABNORMAL")); ?>';
    var ASSETS_STATUS_NOT_OPERATION='<?php echo(C("ASSETS_STATUS_NOT_OPERATION")); ?>';
    var ASSETS_STATUS_IN_MAINTENANCE='<?php echo(C("ASSETS_STATUS_IN_MAINTENANCE")); ?>';
    var ASSETS_STATUS_SCRAPPED='<?php echo(C("ASSETS_STATUS_SCRAPPED")); ?>';
    var MAINTAIN_COMPLETE='<?php echo(C("MAINTAIN_COMPLETE")); ?>';
    var MAINTAIN_PATROL='<?php echo(C("MAINTAIN_PATROL")); ?>';





</script>
</head>
<script>
    var version = Math.random().toFixed(4);
    layui.config({
        version:version,//js版本号
        base: '/tecev/src/' //拓展模块的根目录
    }).extend({ //设定模块别名
        tipsType: '../extend/tipsType',
        suggest: '../extend/suggest',
        formSelects: '../extend/formSelects',
        tablePlug: '../extend/tablePlug/tablePlug',
        asyncFalseUpload: '../extend/asyncFalseUpload'
    });
</script>

<style>
    #profitMainText,
    #positiveMainText,
    #ReturnMainText,
    #UseMainText {
        width: 270px;
        margin-top: -45px;
        float: left;
        padding-left: 10px;
        text-align: center;
    }

    #profitMainText {
        margin-left: 0;
    }

    #UseMainText {
        margin-left: 280px;
    }

    #ReturnMainText {
        margin-left: 560px;
    }

    #positiveMainText {
        margin-left: 840px;
    }

    #LAY-Benefit-Benefit-assetsBenefitData .conData td,
    #LAY-Benefit-Benefit-assetsBenefitData .conData th {
        text-align: center;
    }

    .ass_pic {
        height: 100px;
        width: 88%;
        margin-top: 10px;
        margin-left: 10px;
        margin-right: 10px;
    }

    .ass_left,
    .ass_right {
        float: left;
    }

    .ass_left {
        width: 30%;
    }

    .ass_right {
        margin-top: 10px;
        font-size: 13px;
    }

    .ass_right_bt {
        margin-bottom: 4px;
    }

    .ass_tj {
        width: 18%;
        float: left;
        margin-top: 40px;
        padding-left: 12px;
    }

    .ass_sy_l {
        float: left;
        width: 50%;
    }

    .ass_sy_r {
        float: right;
        text-align: right;
        width: 50%;
        font-size: 14px;
    }

    .ass_sy_r_unit {
        font-size: 12px;
        color: #666;
    }

    .ass_sy {
        height: 33px;
        font-size: 14px;
    }

    .ass_use_l,
    .ass_use_r {
        float: left;
        width: 50%;
    }

    .ass_use_bt {
        padding-left: 18px;
        margin-bottom: 10px;
        margin-top: -2px;
    }

    .ass_use {
        font-size: 14px;
    }

    .total_l,
    .total_r {
        float: left;
    }

    .total_l {
        width: 40%;
    }
</style>
<script>
    var getBenefitLists = "<?php echo ($getBenefitLists); ?>";
</script>

<body style="background: #F2F2F2;">
    <div class="containDiv" style="margin-bottom: 40px;">
        <div class="layui-row">
            <div class="margin-bottom-15">
                <div class="layui-row layui-col-space10" style="margin-top: 10px;">
                    <div class="layui-col-md5">
                        <div class="layui-panel" style="background: #fff;">
                            <div style="height: 120px;">
                                <?php if(empty($pic)): ?><div class="ass_left">
                                        <div class="system-img"
                                            style="margin-top: -12px;height: 120px;overflow: hidden">
                                            <i class="layui-icon" style="font-size: 152px;">&#xe60d;</i>
                                        </div>
                                    </div>
                                    <div class="ass_right">
                                        <?php if($asArr.is_lifesupport == 1):?>
                                        <div class="ass_right_bt">设备编号：<a
                                                href="/A/Lookup/assetsLifeList?action=showLife&assid=<?php echo ($asArr["assid"]); ?>"
                                                class="layui-btn layui-btn-xs"><?php echo ($asArr["assnum"]); ?></a>【查看生命支持设备】</div>
                                        <?php else:?>
                                        <div class="ass_right_bt">设备编号：<?php echo ($asArr["assnum"]); ?></div>
                                        <?php endif?>
                                        <div class="ass_right_bt">设备名称：<?php echo ($asArr["assets"]); ?></div>
                                        <div class="ass_right_bt">所属科室：<a
                                                href="/Admin/Benefit/Benefit/departmentBenefitData?departid=<?php echo ($asArr["departid"]); ?>"
                                                class="layui-btn layui-btn-xs"><?php echo ($asArr["department"]); ?></a>【查看科室效益分析】
                                        </div>
                                        <div class="ass_right_bt">设备型号：<?php echo ($asArr["model"]); ?></div>
                                        <div>设备品牌：<?php echo ($asArr["brand"]); ?></div>
                                    </div>
                                    <?php else: ?>
                                    <div class="ass_left">
                                        <img src="<?php echo ($pic[0]); ?>" class="ass_pic">
                                    </div>
                                    <div class="ass_right">
                                        <div class="ass_right_bt">设备编号：<?php echo ($asArr["assnum"]); ?></div>
                                        <div class="ass_right_bt">设备名称：<?php echo ($asArr["assets"]); ?></div>
                                        <div class="ass_right_bt">所属科室：<?php echo ($asArr["department"]); ?></div>
                                        <div class="ass_right_bt">设备型号：<?php echo ($asArr["model"]); ?></div>
                                        <div>设备品牌：<?php echo ($asArr["brand"]); ?></div>
                                    </div><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="layui-col-md7">
                        <div class="layui-panel" style="background: #fff;">
                            <div style="height: 120px;">
                                <div class="ass_tj">
                                    <i class="layui-icon layui-icon-rmb" style="color: #FFB902"></i> 设备价格
                                    <div style="padding-left: 20px;padding-top: 5px;"><?php echo ($asArr["buy_price"]); ?></div>
                                </div>
                                <div class="ass_tj">
                                    <i class="layui-icon layui-icon-date" style="color: #01AAED;"></i> 启用日期
                                    <div style="padding-left: 20px;padding-top: 5px;"><?php echo ($asArr["opendate"]); ?></div>
                                </div>
                                <div class="ass_tj">
                                    <i class="tecevicon tecev-touyunnianxian" style="color: blue;"></i> 预计使用年限
                                    <div style="padding-left: 20px;padding-top: 5px;"><?php echo ($asArr["expected_life"]); ?>年</div>
                                </div>
                                <div class="ass_tj">
                                    <i class="tecevicon tecev-icon03" style="color: red;"></i> 回本情况
                                    <div style="padding-left: 20px;padding-top: 5px;"><?php echo ($asArr["is_huiben"]); ?></div>
                                </div>
                                <div class="ass_tj">
                                    <i class="tecevicon tecev-jurassic_cost" style="color: green;"></i> 收回成本日期
                                    <div style="padding-left: 20px;padding-top: 5px;"><?php echo ($asArr["huiben_date"]); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="margin-bottom-15">
                <form class="layui-form layui-form-pane" action="">
                    <input type="hidden" value="<?php echo ($asArr["assnum"]); ?>" name="assnum">
                    <div class="layui-black" style="margin: 20px 0;">
                        <label class="layui-form-label" style="width: 140px;">统计特定年份：</label>
                        <div class="layui-input-inline" style="width:110px;">
                            <input class="layui-input" placeholder="选择统计年份" readonly style="cursor: pointer;"
                                name="year" id="assetsBenefitDataYearDate">
                        </div>
                        <div class="layui-inline" style="margin-left: 10px;">
                            <button class="layui-btn" type="button" lay-submit="" lay-filter="assetsBenefitDataSearch"
                                id="assetsBenefitDataSearch">
                                <i class="layui-icon">&#xe615;</i> 确 定
                            </button>
                        </div>
                    </div>
                    <div class="layui-row layui-col-space10">
                        <div class="layui-col-md5">
                            <div class="layui-row layui-col-space10">
                                <div class="layui-col-md6">
                                    <div class="layui-card">
                                        <div class="layui-card-header">收益情况</div>
                                        <div class="layui-card-body" style="height: 180px;">
                                            <div class="ass_sy">
                                                <div class="ass_sy_l">
                                                    <i class="tecevicon tecev-zongshouru3"
                                                        style="color: green;font-size: 14px;"></i>
                                                    累计总收入
                                                </div>
                                                <div class="ass_sy_r">
                                                    <span id="total_income">0</span>
                                                    <span class="ass_sy_r_unit"> 万元</span>
                                                </div>
                                            </div>
                                            <div class="ass_sy">
                                                <div class="ass_sy_l">
                                                    <i class="tecevicon tecev-zongzhichu1"
                                                        style="color: red;font-size: 14px;"></i>
                                                    累计总支出
                                                </div>
                                                <div class="ass_sy_r">
                                                    <span id="total_cost">0</span>
                                                    <span class="ass_sy_r_unit"> 万元</span>
                                                </div>
                                            </div>
                                            <div class="ass_sy">
                                                <div class="ass_sy_l">
                                                    <i class="tecevicon tecev-leijishouyi"
                                                        style="color: blue;font-size: 14px;"></i>
                                                    累计净收益
                                                </div>
                                                <div class="ass_sy_r">
                                                    <span id="total_profit">0</span>
                                                    <span class="ass_sy_r_unit"> 万元</span>
                                                </div>
                                            </div>
                                            <div class="ass_sy">
                                                <div class="ass_sy_l">
                                                    <i class="tecevicon tecev-zhenliaojilu"
                                                        style="color: #01AAED;font-size: 14px;"></i>
                                                    人次/诊疗
                                                </div>
                                                <div class="ass_sy_r">
                                                    <span id="work_number">0</span>
                                                    <span class="ass_sy_r_unit"> 次</span>
                                                </div>
                                            </div>
                                            <div class="ass_sy">
                                                <div class="ass_sy_l">
                                                    <i class="tecevicon tecev-shouyishuai"
                                                        style="color: #FFB800;font-size: 14px;"></i>
                                                    利润率
                                                </div>
                                                <div class="ass_sy_r">
                                                    <span id="total_rate_profit">0%</span>
                                                    <span class="ass_sy_r_unit"> %</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-col-md6">
                                    <div class="layui-card">
                                        <div class="layui-card-header">使用情况</div>
                                        <div class="layui-card-body" style="height: 180px;">
                                            <div class="ass_use">
                                                <div class="ass_use_l">
                                                    <i class="tecevicon tecev-tianshu"
                                                        style="color: green;font-size: 14px;"></i>
                                                    使用天数
                                                    <div class="ass_use_bt">
                                                        <span id="work_days">0</span>
                                                        天
                                                    </div>
                                                </div>
                                                <div class="ass_use_r">
                                                    <i class="tecevicon tecev-tianshu"
                                                        style="color: red;font-size: 14px;"></i>
                                                    停机天数
                                                    <div class="ass_use_bt">
                                                        <span id="stop_days">0</span>
                                                        天
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ass_use">
                                                <div class="ass_use_l">
                                                    <i class="tecevicon tecev-shouyishuai"
                                                        style="color: #FFB800;font-size: 14px;"></i>
                                                    开机率
                                                    <div class="ass_use_bt">
                                                        <span id="start_rate">0</span>
                                                        %
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="layui-col-md7">
                            <div class="layui-card">
                                <div class="layui-card-header">累计收益情况（万元）</div>
                                <div class="hidden-contant">暂无相关数据</div>
                                <div class="layui-card-body" style="height: 180px;" id="income"></div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-row layui-col-space10">
                        <div class="layui-col-md5">
                            <div class="layui-card">
                                <div class="layui-card-header">支出费用统计（万元）</div>
                                <div class="hidden-contant">暂无相关数据</div>
                                <div class="layui-card-body" style="height: 300px;" id="cost"></div>
                            </div>
                        </div>
                        <div class="layui-col-md7">
                            <div class="layui-card">
                                <div class="layui-card-header">收支结余统计（万元）</div>
                                <div class="hidden-contant">暂无相关数据</div>
                                <div class="layui-card-body" style="height: 300px;" id="income_cost"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="layui-row table-list">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">
                        <div class="fl">
                            <i class="layui-icon">&#xe62d;</i> 收支明细记录
                        </div>
                    </div>
                    <div class="layui-card-body">
                        <div id="benefitLists" lay-filter="benefitLists"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<script src="/Public/js/echarts.min.js"></script>
<script>
    layui.use('controller/benefit/benefit/assetsBenefitData', layui.factory('controller/benefit/benefit/assetsBenefitData'));
</script>
<script type="text/html" id="LAY-Benfit-detail-getListToolbar">
    <div class="layui-btn-container">

    </div>
</script>