<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
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
        .layui-table-view .layui-table {
            width: 100%
        }

        .layui-table, .layui-table-view {
            margin-top: 0;
        }

        .no-padding-td {
            padding: 0px !important;
        }

        .no-padding-td .layui-input {
            border: none;
            height: 48px;
            line-height: 48px;
        }

        .cus-width {
            width: 250px !important;
        }

        .total {
            text-align: left;
            font-weight: bold;
            color: #FF5722;
        }

        .img-content {
            width: 95%;
            margin-top: 10px;
            margin-bottom: 10px;
            margin-left: 2%;
            text-align: center;
            border: 1px solid #E6E9EB;
        }

        .img-content img {
            width: 100%;
            max-height: 410px;
        }

        .system-img {
            padding-top: 105px;
            padding-bottom: 105px;
        }

        .width140 {
            width: 140px !important;
        }

        /*时间线*/
        .timeLineList {
            height: auto;
            width: 75%;
            float: left;
            margin: 0 0 20px 100px;
        }

        .timeLineList li {
            width: 80px;
            float: left;
            height: 80px;
        }

        .layui-timeline-axis {
            position: static;
        }

        .timeLineBox {
            margin-top: 30px;
            position: relative;
        }

        .timeLine {
            width: 63px;
            height: 3px;
            position: absolute;
            top: 7px;
            left: -62px;
            margin-top: 0;
        }

        .timeLineAddAssets {
            width: 70px;
            height: 3px;
            background-color: #009688;
            position: absolute;
            top: 7px;
            left: -69px;
        }

        .timeLineAddAssetsTitle {
            position: absolute;
            top: -25px;
            left: -15px;
            font-size: 12px;
            color: #009688
        }

        .timeLineTitle {
            position: absolute;
            top: -25px;
            font-size: 12px;
            left: -5px;
        }

        .timeLineAddAssetsDate {
            position: absolute;
            top: 25px;
            left: -20px;
            font-size: 12px;
            color: #009688
        }

        .timeLineDate {
            position: absolute;
            top: 25px;
            left: -20px;
            font-size: 12px;
        }

        .timeLineDetail {
            width: 300px;
            position: absolute;
            left: -80px;
            border-radius: 5px;
            background-color: #fff;
            color: #333;
            font-size: 12px;
            z-index: 100;
            display: none;
        }

        .timeLineDetail tr th {
            text-align: right;
        }

        .transferbgcolor {
            background-color: #FFB800;
        }

        .transfercolor {
            color: #FFB800;
        }

        .repairbgcolor {
            background-color: #FF5722;
        }

        .repaircolor {
            color: #FF5722;
        }

        .scrapbgcolor {
            background-color: #C9C9C9;
        }

        .scrapcolor {
            color: #C9C9C9;
        }

        .patrolbgcolor {
            background-color: #1E9FFF;
        }

        .patrolcolor {
            color: #1E9FFF;
        }

        .adversebgcolor {
            background-color: #f06165;
        }

        .adversecolor {
            color: #f06165;
        }

        .qualitybgcolor {
            background-color: #F88C51;
        }

        .qualitycolor {
            color: #F88C51;
        }

        .meteringbgcolor {
            background-color: #AE7C37;
        }

        .meteringcolor {
            color: #AE7C37;
        }

        .borrowbgcolor {
            background-color: #DA70D6;
        }

        .borrowcolor {
            color: #DA70D6;
        }

        .outsidebgcolor {
            background-color: #8B4513;
        }

        .outsidecolor {
            color: #8B4513;
        }
        .subsidiarybgcolor {
            background-color: #2F4056;
        }

        .subsidiarycolor {
            color: #2F4056;
        }

        .baseInfo {
            margin-left: 30px;
            font-size: 12px;
        }

        .baseInfo .layui-inline {
            width: 200px;
        }
    </style>
    <script>
        var showAssets = "<?php echo ($showAssets); ?>";
    </script>
</head>
<body>
<div class="containDiv" style="width: 100%;">
    <input type="hidden" name="assid" value="<?php echo ($assid); ?>"/>
    <input type="hidden" name="changeTab" value="<?php echo ($changeTab); ?>"/>
    <div class="margin-bottom-15">
        <ul class="timeLineList">
            <li>
                <div class="timeLineBox">
                    <i class="layui-icon layui-timeline-axis"></i>
                    <div class="timeLineAddAssets"></div>
                    <span class="timeLineAddAssetsTitle">设备入库</span>
                    <span class="timeLineAddAssetsDate"><?php echo ($assets["storage_date"]); ?></span>
                </div>
            </li>
            <?php if(is_array($life)): $i = 0; $__LIST__ = $life;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; switch($v["type"]): case "1": ?><!--转科生命线-->
                        <?php if($v['sort_date'] != '-'): ?>
                        <li>
                            <div class="timeLineBox">
                                <i class="layui-icon layui-timeline-axis"></i>
                                <div class="timeLine transferbgcolor"></div>
                                <span class="timeLineTitle transfercolor"><?php echo ($v["title"]); ?></span>
                                <span class="timeLineDate transfercolor"><?php echo ($v["sort_date"]); ?></span>
                                <div class="timeLineDetail">
                                    <table class="layui-table" lay-size="sm" style="margin: 0;">
                                        <colgroup>
                                            <col width="40%">
                                            <col width="60%">
                                        </colgroup>
                                        <tbody>
                                        <tr>
                                            <th>申请人：</th>
                                            <td><?php echo ($v["applicant_user"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>申请时间：</th>
                                            <td><?php echo ($v["applicant_time"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>转出科室：</th>
                                            <td><?php echo ($v["tranout_depart_name"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>转出科室负责人：</th>
                                            <td><?php echo ($v["tranout_departrespon"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>转入科室：</th>
                                            <td><?php echo ($v["tranin_depart_name"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>转入科室负责人：</th>
                                            <td><?php echo ($v["tranin_departrespon"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>转科日期：</th>
                                            <td><?php echo ($v["transfer_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>验收人：</th>
                                            <td><?php echo ($v["check_user"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>验收时间：</th>
                                            <td><?php echo ($v["check_time"]); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php endif; break;?>
                    <?php case "2": ?><!--维修生命线-->
                        <?php if($v['sort_date'] != '-'): ?>
                        <li>
                            <div class="timeLineBox">
                                <i class="layui-icon layui-timeline-axis"></i>
                                <div class="timeLine repairbgcolor"></div>
                                <span class="timeLineTitle repaircolor"><?php echo ($v["title"]); ?></span>
                                <span class="timeLineDate repaircolor"><?php echo ($v["sort_date"]); ?></span>
                                <div class="timeLineDetail">
                                    <table class="layui-table" lay-size="sm" style="margin: 0;">
                                        <colgroup>
                                            <col width="40%">
                                            <col width="60%">
                                        </colgroup>
                                        <tbody>
                                        <tr>
                                            <th>报修人：</th>
                                            <td><?php echo ($v["applicant"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>报修时间：</th>
                                            <td><?php echo ($v["applicant_time"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>报修原因：</th>
                                            <td><?php echo ($v["breakdown"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>维修状态：</th>
                                            <td><?php echo ($v["statusName"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>接单人：</th>
                                            <td><?php echo ($v["response"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>接单时间：</th>
                                            <td><?php echo ($v["response_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>维修结束时间：</th>
                                            <td><?php echo ($v["overdate"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>验收人：</th>
                                            <td><?php echo ($v["checkperson"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>验收时间：</th>
                                            <td><?php echo ($v["checkdate"]); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php endif; break;?>
                    <?php case "3": ?><!--报废生命线-->
                        <?php if($v['sort_date'] != '-'): ?>
                        <li>
                            <div class="timeLineBox">
                                <i class="layui-icon layui-timeline-axis"></i>
                                <div class="timeLine scrapbgcolor"></div>
                                <span class="timeLineTitle scrapcolor"><?php echo ($v["title"]); ?></span>
                                <span class="timeLineDate scrapcolor"><?php echo ($v["sort_date"]); ?></span>
                                <div class="timeLineDetail">
                                    <table class="layui-table" lay-size="sm" style="margin: 0;">
                                        <colgroup>
                                            <col width="40%">
                                            <col width="60%">
                                        </colgroup>
                                        <tbody>
                                        <tr>
                                            <th>申请人：</th>
                                            <td><?php echo ($v["apply_user"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>申请时间：</th>
                                            <td><?php echo ($v["add_time"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>报废原因：</th>
                                            <td><?php echo ($v["scrap_reason"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>处置经手人：</th>
                                            <td><?php echo ($v["clear_cross_user"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>清理公司：</th>
                                            <td><?php echo ($v["clear_company"]); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php endif; break;?>
                    <?php case "4": ?><!--巡查保养生命线-->
                        <?php if($v['sort_date'] != '-'): ?>
                        <li>
                            <div class="timeLineBox">
                                <i class="layui-icon layui-timeline-axis"></i>
                                <div class="timeLine patrolbgcolor"></div>
                                <span class="timeLineTitle patrolcolor" style="left: -15px;"><?php echo ($v["title"]); ?></span>
                                <span class="timeLineDate patrolcolor"><?php echo ($v["sort_date"]); ?></span>
                                <div class="timeLineDetail">
                                    <table class="layui-table" lay-size="sm" style="margin: 0;">
                                        <colgroup>
                                            <col width="40%">
                                            <col width="60%">
                                        </colgroup>
                                        <tbody>
                                        <tr>
                                            <th>计划名称：</th>
                                            <td><?php echo ($v["patrol_name"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>计划级别：</th>
                                            <td><?php echo ($v["patrol_level_name"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>周期计划：</th>
                                            <td><?php echo ($v["cycle_name"]); ?></td>
                                        </tr>
                                        <?php if($v["is_cycle"] == 1): ?><tr>
                                                <th>周期设置：</th>
                                                <td><?php echo ($v["cycle_setting_name"]); ?></td>
                                            </tr>
                                            <tr>
                                                <th>当前/总期次：</th>
                                                <td><?php echo ($v["total_current_period"]); ?></td>
                                            </tr><?php endif; ?>
                                        <tr>
                                            <th>计划开始日期：</th>
                                            <td><?php echo ($v["patrol_start_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>计划结束日期：</th>
                                            <td><?php echo ($v["patrol_end_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>计划执行状态：</th>
                                            <td><?php echo ($v["statusName"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>是否异常：</th>
                                            <td><?php echo ($v["is_normal"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>异常项总数：</th>
                                            <td><?php echo ($v["abnormal"]); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php endif; break;?>
                    <?php case "5": ?><!--不良事件生命线-->
                        <?php if($v['sort_date'] != '-'): ?>
                        <li>
                            <div class="timeLineBox">
                                <i class="layui-icon layui-timeline-axis"></i>
                                <div class="timeLine adversebgcolor"></div>
                                <span class="timeLineTitle adversecolor" style="left: -15px;"><?php echo ($v["title"]); ?></span>
                                <span class="timeLineDate adversecolor"><?php echo ($v["sort_date"]); ?></span>
                                <div class="timeLineDetail">
                                    <table class="layui-table" lay-size="sm" style="margin: 0;">
                                        <colgroup>
                                            <col width="40%">
                                            <col width="60%">
                                        </colgroup>
                                        <tbody>
                                        <tr>
                                            <th>报告人：</th>
                                            <td><?php echo ($v["sign"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>报告人职称：</th>
                                            <td><?php echo ($v["reporter"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>事件记录时间：</th>
                                            <td><?php echo ($v["addtime"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>报告日期：</th>
                                            <td><?php echo ($v["report_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>报告来源：</th>
                                            <td><?php echo ($v["report_from"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>预期治疗治病：</th>
                                            <td><?php echo ($v["expected"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>事件后果：</th>
                                            <td><?php echo ($v["consequence"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>事件主要表现：</th>
                                            <td><?php echo ($v["express"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>初步原因分析：</th>
                                            <td><?php echo ($v["cause"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>初步处理情况：</th>
                                            <td><?php echo ($v["situation"]); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php endif; break;?>
                    <?php case "6": ?><!--质控计划生命线-->
                        <?php if($v['sort_date'] != '-'): ?>
                        <li>
                            <div class="timeLineBox">
                                <i class="layui-icon layui-timeline-axis"></i>
                                <div class="timeLine qualitybgcolor"></div>
                                <span class="timeLineTitle qualitycolor" style="left: -15px;"><?php echo ($v["title"]); ?></span>
                                <span class="timeLineDate qualitycolor"><?php echo ($v["sort_date"]); ?></span>
                                <div class="timeLineDetail">
                                    <table class="layui-table" lay-size="sm" style="margin: 0;">
                                        <colgroup>
                                            <col width="40%">
                                            <col width="60%">
                                        </colgroup>
                                        <tbody>
                                        <tr>
                                            <th>计划名称：</th>
                                            <td><?php echo ($v["plan_name"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>计划编号：</th>
                                            <td><?php echo ($v["plan_num"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>是否周期执行：</th>
                                            <td><?php echo ($v["is_cycle"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>周期(月)：</th>
                                            <td><?php echo ($v["cycle"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>期次：</th>
                                            <td><?php echo ($v["period"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>检测人：</th>
                                            <td><?php echo ($v["username"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>预计执行日期：</th>
                                            <td><?php echo ($v["do_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>预计结束日期：</th>
                                            <td><?php echo ($v["end_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>启用日期：</th>
                                            <td><?php echo ($v["start_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>计划执行状态：</th>
                                            <td><?php echo ($v["is_start"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>检测结果：</th>
                                            <td><?php echo ($v["result"]); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php endif; break;?>
                    <?php case "7": ?><!--计量计划生命线-->
                        <?php if($v['sort_date'] != '-'): ?>
                        <li>
                            <div class="timeLineBox">
                                <i class="layui-icon layui-timeline-axis"></i>
                                <div class="timeLine meteringbgcolor"></div>
                                <span class="timeLineTitle meteringcolor" style="left: -15px;"><?php echo ($v["title"]); ?></span>
                                <span class="timeLineDate meteringcolor"><?php echo ($v["sort_date"]); ?></span>
                                <div class="timeLineDetail">
                                    <table class="layui-table" lay-size="sm" style="margin: 0;">
                                        <colgroup>
                                            <col width="40%">
                                            <col width="60%">
                                        </colgroup>
                                        <tbody>
                                        <tr>
                                            <th>计划编号：</th>
                                            <td><?php echo ($v["plan_num"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>计量分类：</th>
                                            <td><?php echo ($v["mcategory"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>周期(月)：</th>
                                            <td><?php echo ($v["cycle"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>计量负责人：</th>
                                            <td><?php echo ($v["respo_user"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>启用状态：</th>
                                            <td><?php echo ($v["plan_status_name"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>本次检定日期：</th>
                                            <td><?php echo ($v["this_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>检定机构：</th>
                                            <td><?php echo ($v["company"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>计量费用：</th>
                                            <td><?php echo ($v["money"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>检定人：</th>
                                            <td><?php echo ($v["test_person"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>检查状态：</th>
                                            <td><?php echo ($v["result_status_name"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>检定结果：</th>
                                            <td><?php echo ($v["result"]); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php endif; break;?>
                    <?php case "8": ?><!--借调生命线-->
                        <?php if($v['sort_date'] != '-'): ?>
                        <li>
                            <div class="timeLineBox">
                                <i class="layui-icon layui-timeline-axis"></i>
                                <div class="timeLine borrowbgcolor"></div>
                                <span class="timeLineTitle borrowcolor" style="left: -15px;"><?php echo ($v["title"]); ?></span>
                                <span class="timeLineDate borrowcolor"><?php echo ($v["sort_date"]); ?></span>
                                <div class="timeLineDetail">
                                    <table class="layui-table" lay-size="sm" style="margin: 0;">
                                        <colgroup>
                                            <col width="40%">
                                            <col width="60%">
                                        </colgroup>
                                        <tbody>
                                        <tr>
                                            <th>申请科室：</th>
                                            <td><?php echo ($v["apply_department"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>申请人：</th>
                                            <td><?php echo ($v["apply_username"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>申请时间：</th>
                                            <td><?php echo ($v["apply_time"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>借用原因：</th>
                                            <td><?php echo ($v["borrow_reason"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>借调状态：</th>
                                            <td><?php echo ($v["statuName"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>结束时间：</th>
                                            <td><?php echo ($v["over_date"]); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php endif; break;?>
                    <?php case "9": ?><!--外调生命线-->
                        <?php if($v['sort_date'] != '-'): ?>
                        <li>
                            <div class="timeLineBox">
                                <i class="layui-icon layui-timeline-axis"></i>
                                <div class="timeLine outsidebgcolor"></div>
                                <span class="timeLineTitle outsidecolor" style="left: -15px;"><?php echo ($v["title"]); ?></span>
                                <span class="timeLineDate outsidecolor"><?php echo ($v["sort_date"]); ?></span>
                                <div class="timeLineDetail">
                                    <table class="layui-table" lay-size="sm" style="margin: 0;">
                                        <colgroup>
                                            <col width="40%">
                                            <col width="60%">
                                        </colgroup>
                                        <tbody>
                                        <tr>
                                            <th>申请人：</th>
                                            <td><?php echo ($v["apply_username"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>申请时间：</th>
                                            <td><?php echo ($v["apply_time"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>申请类型：</th>
                                            <td><?php echo ($v["apply_typeName"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>联系人：</th>
                                            <td><?php echo ($v["person"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>联系电话：</th>
                                            <td><?php echo ($v["phone"]); ?></td>
                                        </tr>
                                        <?php if(!empty($v["price"])): ?><tr>
                                                <th>金额：</th>
                                                <td><?php echo ($v["price"]); ?></td>
                                            </tr><?php endif; ?>
                                        <tr>
                                            <th>外调目的地：</th>
                                            <td><?php echo ($v["accept"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>验收人：</th>
                                            <td><?php echo ($v["check_person"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>验收人联系电话：</th>
                                            <td><?php echo ($v["check_phone"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>验收日期：</th>
                                            <td><?php echo ($v["check_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>是否通过审核：</th>
                                            <td><?php echo ($v["examine_statusName"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>结束时间：</th>
                                            <td><?php echo ($v["over_date"]); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php endif; break;?>
                    <?php case "10": ?><!--附属设备分配生命线-->
                        <?php if($v['sort_date'] != '-'): ?>
                        <li>
                            <div class="timeLineBox">
                                <i class="layui-icon layui-timeline-axis"></i>
                                <div class="timeLine subsidiarybgcolor"></div>
                                <span class="timeLineTitle subsidiarycolor" style="left: -30px;"><?php echo ($v["title"]); ?></span>
                                <span class="timeLineDate subsidiarycolor"><?php echo ($v["sort_date"]); ?></span>
                                <div class="timeLineDetail">
                                    <table class="layui-table" lay-size="sm" style="margin: 0;">
                                        <colgroup>
                                            <col width="40%">
                                            <col width="60%">
                                        </colgroup>
                                        <tbody>
                                        <tr>
                                            <th>申请人：</th>
                                            <td><?php echo ($v["apply_user"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>申请日期：</th>
                                            <td><?php echo ($v["apply_date"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>设备名称：</th>
                                            <td><?php echo ($v["main_assets"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>设备科室：</th>
                                            <td><?php echo ($v["department"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>管理科室：</th>
                                            <td><?php echo ($v["main_managedepart"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>使用位置：</th>
                                            <td><?php echo ($v["main_address"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>负责人：</th>
                                            <td><?php echo ($v["main_assetsrespon"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>验收人：</th>
                                            <td><?php echo ($v["check_user"]); ?></td>
                                        </tr>
                                        <tr>
                                            <th>验收日期：</th>
                                            <td><?php echo ($v["check_time"]); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php endif; break; endswitch; endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
    <div class="clear"></div>
    <div class="margin-bottom-15 baseInfo">
        <div class="layui-form-item">
            <div class="layui-inline">设备编号：<?php echo ($assets["assnum"]); ?></div>
            <div class="layui-inline">所属科室：<?php echo ($assets["department"]); ?></div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">设备名称：<?php echo ($assets["assets"]); ?></div>
            <div class="layui-inline">设备型号：<?php echo ($assets["model"]); ?></div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">入库日期：<?php echo ($assets["storage_date"]); ?></div>
            <div class="layui-inline">保修到期：<?php echo ($assets["guarantee_date"]); ?></div>
        </div>
    </div>
    <div class="layui-tab layui-tab-brief" lay-filter="change">
        <ul class="layui-tab-title">
            <li class="layui-this">基础信息</li>
            <li>资质信息</li>
            <?php if($showTechnicalInformation == 1): ?><li>技术资料</li><?php endif; ?>
            <?php if($showAssetsfile == 1): ?><li>设备档案</li><?php endif; ?>
            <?php if($assets['is_subsidiary'] == 0): ?><li>附属设备</li><?php endif; ?>
            <?php if($showAssetsInsurance == 1): ?><li>设备参保</li><?php endif; ?>
            <li>状态变更记录</li>
            <li>转科记录</li>
            <li>维修记录</li>
            <li>质控记录</li>
            <li>计量记录</li>
            <!--<li>折旧记录</li>-->
            <li>不良记录</li>

            <li>借调记录</li>
            <li lay-id="14">外调记录</li>

            <!--功能开发中。。。后续开启-->
            <!--<li>采购信息</li>-->
            <!--<li>效益分析</li>-->
            <!--<li>保养信息</li>-->

        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show"><!--基础信息-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备名称：<?php echo ($assets["assets"]); ?></div>
                    <div style="width: 70%;float: left;">
                        <table class="layui-table read-table" lay-size="sm" lay-even>
                            <colgroup>
                                <col width="195">
                                <col>
                                <col width="195">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr>
                                <th>设备编号：</th>
                                <td><?php echo ($assets["assnum"]); ?></td>
                                <th>设备原编号：</th>
                                <td><?php echo ($assets["assorignum"]); ?></td>
                            </tr>
                            <tr>
                                <th>规格 / 型号：</th>
                                <td><?php echo ($assets["model"]); ?></td>
                                <th>产品序列号：</th>
                                <td><?php echo ($assets["serialnum"]); ?></td>
                            </tr>
                            <tr>
                                <th>设备分类：</th>
                                <td colspan="3"><?php echo ($assets["cate_name"]); ?></td>
                            </tr>
                            <tr>
                                <th>系统辅助分类：</th>
                                <td><?php echo ($assets["helpcat"]); ?></td>
                                <th>设备原值(元)：</th>
                                <td><?php echo ($assets["buy_price"]); ?></td>
                            </tr>
                            <tr>
                                <th>预计使用年限：</th>
                                <td><?php echo ($assets["expected_life"]); ?></td>
                                <th>残净值率(%)：</th>
                                <td><?php echo ($assets["residual_value"]); ?></td>
                            </tr>
                            <tr>
                                <th>生产厂商：</th>
                                <td><?php echo ($ols_factory["factory"]); ?></td>
                                <th>供应商：</th>
                                <td><?php echo ($ols_supplier["supplier"]); ?></td>
                            </tr>
                            <tr>
                                <th>出厂日期：</th>
                                <td><?php echo ($assets["factorydate"]); ?></td>
                                <th>保修到期日期：</th>
                                <td><?php echo ($assets["guarantee_date"]); ?></td>
                            </tr>
                            <tr>
                                <th>单位：</th>
                                <td><?php echo ($assets["unit"]); ?></td>
                                <th>品牌：</th>
                                <td><?php echo ($assets["brand"]); ?></td>
                            </tr>
                            <tr>
                                <th>出厂编号：</th>
                                <td><?php echo ($assets["factorynum"]); ?></td>
                                <th>发票编号：</th>
                                <td><?php echo ($assets["invoicenum"]); ?></td>
                            </tr>
                            <tr>
                                <th>设备类型：</th>
                                <td colspan="3"><?php echo ($assets["type"]); ?></td>
                            </tr>
                            <tr>
                                <th>所属科室：</th>
                                <td><?php echo ($assets["department"]); ?></td>
                                <th>管理科室：</th>
                                <td><?php echo ($assets["managedepart"]); ?></td>
                            </tr>
                            <tr>
                                <th>所在位置：</th>
                                <td><?php echo ($assets["address"]); ?></td>
                                <th>资产负责人：</th>
                                <td><?php echo ($assets["assetsrespon"]); ?></td>
                            </tr>
                            <tr>
                                <th>财务分类：</th>
                                <td><?php echo ($assets["finance"]); ?></td>
                                <th>设备来源：</th>
                                <td><?php echo ($assets["assfrom"]); ?></td>
                            </tr>
                            <tr>
                                <th>资金来源：</th>
                                <td><?php echo ($assets["capitalfrom"]); ?></td>
                                <th>入库日期：</th>
                                <td><?php echo ($assets["storage_date"]); ?></td>
                            </tr>
                            <tr>
                                <th>启用日期：</th>
                                <td colspan="3"><?php echo ($assets["opendate"]); ?></td>
                            </tr>
                            <tr>
                                <th>折旧方式：</th>
                                <td><?php echo ($assets["depreciation_method_name"]); ?></td>
                                <th>折旧年限：</th>
                                <td><?php echo ($assets["depreciable_lives"]); ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="photo fl" style="width: 30%; text-align: center;">
                        <?php if(empty($assets["pic_url"])): ?><div class="system-img">
                                <i class="layui-icon" style="font-size: 120px;">&#xe60d;</i><br>暂无图片<br/>
                                <br>（如需添加请前往设备详情页面添加）<br/>
                            </div>
                            <?php else: ?>
                            <div class="layui-carousel" id="carouselAssetsPic">
                                <div carousel-item="">
                                    <?php if(is_array($assetsPic)): $i = 0; $__LIST__ = $assetsPic;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><img src="<?php echo ($v); ?>" style="cursor: pointer;"/><?php endforeach; endif; else: echo "" ;endif; ?>
                                </div>
                            </div><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="layui-tab-item"><!--资质信息-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">资质信息</div>
                    <table class="layui-table read-table" lay-even="" lay-size="sm">
                        <colgroup>
                            <col>
                        </colgroup>
                        <tbody>
                        <tr>
                            <th>生产厂商：</th>
                            <td colspan="3"><?php echo ($ols_factory["factory"]); ?></td>
                        </tr>
                        <tr>
                            <th>联系人：</th>
                            <td><?php echo ($ols_factory["factory_user"]); ?></td>
                            <th>联系电话：</th>
                            <td><?php echo ($ols_factory["factory_tel"]); ?></td>
                        </tr>
                        <tr>
                            <th>供应商：</th>
                            <td colspan="3"><?php echo ($ols_supplier["supplier"]); ?></td>
                        </tr>
                        <tr>
                            <th>联系人：</th>
                            <td><?php echo ($ols_supplier["supp_user"]); ?></td>
                            <th>联系电话：</th>
                            <td><?php echo ($ols_supplier["supp_tel"]); ?></td>
                        </tr>
                        <tr>
                            <th>维修公司：</th>
                            <td colspan="3"><?php echo ($ols_repair["repair"]); ?></td>
                        </tr>
                        <tr>
                            <th>联系人：</th>
                            <td><?php echo ($ols_repair["repa_user"]); ?></td>
                            <th>联系电话：</th>
                            <td><?php echo ($ols_repair["repa_tel"]); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">生产商资质文件</div>
                    <table class="layui-table mgt0" lay-size="sm">
                        <colgroup>
                            <col width="80">
                            <col>
                            <col>
                            <col>
                            <col>
                            <col width="160">
                        </colgroup>
                        <thead>
                        <tr>
                            <td class="td-align-center">序号</td>
                            <td class="td-align-center">文件名称</td>
                            <td class="td-align-center">上传时间</td>
                            <td class="td-align-center">发证(备案)日期</td>
                            <td class="td-align-center">有效期限</td>
                            <td class="td-align-center">操作</td>
                        </tr>
                        </thead>
                        <tbody id="fileList">
                        <?php if(empty($fac_files)): ?><tr class="tech-empty">
                                <td colspan="6" class="td-align-center">暂无相关数据</td>
                            </tr>
                            <?php else: ?>
                            <?php if(is_array($fac_files)): $i = 0; $__LIST__ = $fac_files;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                    <td class="td-align-center"><?php echo ($key+1); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["name"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["adddate"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["record_date"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["term_date"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["html"]); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">供应商资质文件</div>
                    <table class="layui-table mgt0" lay-size="sm">
                        <colgroup>
                            <col width="80">
                            <col>
                            <col>
                            <col>
                            <col>
                            <col width="160">
                        </colgroup>
                        <thead>
                        <tr>
                            <td class="td-align-center">序号</td>
                            <td class="td-align-center">文件名称</td>
                            <td class="td-align-center">上传时间</td>
                            <td class="td-align-center">发证(备案)日期</td>
                            <td class="td-align-center">有效期限</td>
                            <td class="td-align-center">操作</td>
                        </tr>
                        </thead>
                        <tbody id="fileList">
                        <?php if(empty($sup_files)): ?><tr class="tech-empty">
                                <td colspan="6" class="td-align-center">暂无相关数据</td>
                            </tr>
                            <?php else: ?>
                            <?php if(is_array($sup_files)): $i = 0; $__LIST__ = $sup_files;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                    <td class="td-align-center"><?php echo ($key+1); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["name"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["adddate"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["record_date"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["term_date"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["html"]); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">维修商资质文件</div>
                    <table class="layui-table mgt0" lay-size="sm">
                        <colgroup>
                            <col width="80">
                            <col>
                            <col>
                            <col>
                            <col>
                            <col width="160">
                        </colgroup>
                        <thead>
                        <tr>
                            <td class="td-align-center">序号</td>
                            <td class="td-align-center">文件名称</td>
                            <td class="td-align-center">上传时间</td>
                            <td class="td-align-center">发证(备案)日期</td>
                            <td class="td-align-center">有效期限</td>
                            <td class="td-align-center">操作</td>
                        </tr>
                        </thead>
                        <tbody id="fileList">
                        <?php if(empty($rep_files)): ?><tr class="tech-empty">
                                <td colspan="6" class="td-align-center">暂无相关数据</td>
                            </tr>
                            <?php else: ?>
                            <?php if(is_array($rep_files)): $i = 0; $__LIST__ = $rep_files;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                    <td class="td-align-center"><?php echo ($key+1); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["name"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["adddate"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["record_date"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["term_date"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["html"]); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </tbody>
                    </table>
                </div></div>
        <?php if($showTechnicalInformation == 1): ?><div class="layui-tab-item"><!--技术资料-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">技术资料</div>
                    <table class="layui-table mgt0" lay-size="sm">
                        <colgroup>
                            <col width="80">
                            <col>
                            <col>
                            <col width="160">
                        </colgroup>
                        <thead>
                        <tr>
                            <td class="td-align-center">序号</td>
                            <td class="td-align-center">文件名称</td>
                            <td class="td-align-center">上传时间</td>
                            <td class="td-align-center">操作</td>
                        </tr>
                        </thead>
                        <tbody id="qualifileList">
                        <?php if(empty($techni_files)): ?><tr class="tech-empty">
                                <td colspan="4" class="td-align-center">暂无相关数据</td>
                            </tr>
                            <?php else: ?>
                            <?php if(is_array($techni_files)): $i = 0; $__LIST__ = $techni_files;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                    <td class="td-align-center"><?php echo ($key+1); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["file_name"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["add_time"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["html"]); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div><?php endif; ?>
        <?php if($showAssetsfile == 1): ?><div class="layui-tab-item"><!--设备档案-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备档案</div>
                    <table class="layui-table mgt0" lay-size="sm">
                        <colgroup>
                            <col width="80">
                            <col>
                            <col>
                            <col width="160">
                        </colgroup>
                        <thead>
                        <tr>
                            <td class="td-align-center">序号</td>
                            <td class="td-align-center">文件名称</td>
                            <td class="td-align-center">上传时间</td>
                            <td class="td-align-center">操作</td>
                        </tr>
                        </thead>
                        <tbody id="arcfileList">
                        <?php if(empty($archives_files)): ?><tr class="arc-empty">
                                <td colspan="4" class="td-align-center">暂无相关数据</td>
                            </tr>
                            <?php else: ?>
                            <?php if(is_array($archives_files)): $i = 0; $__LIST__ = $archives_files;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                    <td class="td-align-center"><?php echo ($key+1); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["file_name"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["add_time"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["html"]); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div><?php endif; ?>
            <?php if($assets['is_subsidiary'] == 0): ?><div class="layui-tab-item"><!--附属设备-->
                    <div class="margin-bottom-15">
                        <div class="layui-elem-quote">附属设备</div>
                        <table class="layui-table mgt0" lay-size="sm">
                            <colgroup>
                                <col width="60">
                                <col width="">
                                <col width="120">
                                <col width="120">
                                <col width="80">
                                <col width="90">
                                <col width="">
                                <col width="150">
                                <col>
                            </colgroup>
                            <thead>
                            <tr>
                                <th class="th-align-center">序号</th>
                                <th class="th-align-center"><span class="rquireCoin">*</span> 附属设备名称</th>
                                <th class="th-align-center"><span class="rquireCoin">*</span> 品牌</th>
                                <th class="th-align-center"><span class="rquireCoin">*</span> 规格 / 型号</th>
                                <th class="th-align-center"><span class="rquireCoin">*</span> 数量</th>
                                <th class="th-align-center">单价（元）</th>
                                <th class="th-align-center"><span class="rquireCoin">*</span> 分类</th>
                                <th class="th-align-center">备注</th>
                            </tr>
                            </thead>
                            <tbody class="materiel-list">
                            <?php if(empty($increment)): ?><tr class="arc-empty" id="arc-empty-materiel">
                                    <td colspan="9" style="text-align: center;">暂无相关数据</td>
                                </tr>
                                <?php else: ?>
                                <?php if(is_array($increment)): $i = 0; $__LIST__ = $increment;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id="row_<?php echo ($key+1); ?>">
                                        <td class="td-align-center xuhao"><?php echo ($key+1); ?></td>
                                        <td class="td-align-center increname"><?php echo ($vo["increname"]); ?></td>
                                        <td class="td-align-center brand"><?php echo ($vo["brand"]); ?></td>
                                        <td class="td-align-center model"><?php echo ($vo["model"]); ?></td>
                                        <td class="td-align-center incre_num"><?php echo ($vo["incre_num"]); ?></td>
                                        <td class="td-align-center increprice"><?php echo ($vo["increprice"]); ?></td>
                                        <td class="td-align-center incre_catid"><?php echo ($vo["catname"]); ?></td>
                                        <td class="td-align-center remark"><?php echo ($vo["remark"]); ?></td>
                                    </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div><?php endif; ?>
        <?php if($showAssetsInsurance == 1): ?><div class="layui-tab-item"><!--参保信息-->
                <div class="margin-bottom-15">
                    <div class="layui-row">
                        <div class="layui-col-md12">
                            <div class="layui-card">
                                <div class="layui-card-header" style="padding: 0">
                                    <div class="layui-elem-quote">参保信息</div>
                                </div>
                                <div class="layui-card-body" style="padding:0 0 0 1px;">
                                    <table id="doRenewal" lay-filter="doRenewalData" lay-size="sm"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><?php endif; ?>
            <div class="layui-tab-item"><!--状态变更记录-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备状态变更记录</div>
                    <table id="Status" lay-filter="statusData"></table>
                </div>
            </div>
            <div class="layui-tab-item"><!--设备转科记录-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备转科记录</div>
                    <table id="transfer" lay-filter="transfer"></table>
                </div>
            </div>
            <div class="layui-tab-item"><!--设备维修记录-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备维修记录</div>
                    <div id="RepairSearchList" lay-filter="RepairSearchList"></div>
                </div>
            </div>
            <div class="layui-tab-item"><!--设备质控记录-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备质控记录</div>
                    <div id="quality" lay-filter="qualityData"></div>
                </div>
            </div>
            <div class="layui-tab-item"><!--设备计量记录-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备计量记录</div>
                    <div id="meteringLists" lay-filter="meteringListsData"></div>
                </div>
            </div>
            <!--<div class="layui-tab-item">&lt;!&ndash;设备折旧记录&ndash;&gt;-->
            <!--<div class="margin-bottom-15">-->
            <!--<div class="layui-elem-quote">设备折旧记录</div>-->
            <!--开发中。。。-->
            <!--</div>-->
            <!--</div>-->
            <div class="layui-tab-item"><!--不良记录-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备不良记录</div>
                    <div id="getAdverseLists" lay-filter="getAdverseData"></div>
                </div>
            </div>

            <div class="layui-tab-item"><!--借调记录-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备借调记录</div>
                    <div id="borrowRecordList" lay-filter="borrowRecordData"></div>
                </div>
            </div>
            <div class="layui-tab-item"><!--外调记录-->
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备外调申请记录</div>
                    <table class="layui-table" lay-even="" lay-size="sm">
                        <colgroup>
                            <col width="120">
                            <col>
                            <col width="120">
                            <col>
                        </colgroup>
                        <tbody>
                        <tr>
                            <td class="text-right">申请类型：</td>
                            <td><?php echo ($outside["apply_type_name"]); ?></td>
                            <?php if($outside['apply_type'] == C('OUTSIDE_OUTSIDE_SALE_TYPE')): ?><td class="text-right">外售金额：</td>
                                <td><?php echo ($outside["price"]); ?>元</td>
                                <?php else: ?>
                                <td class="text-right">外调目的地：</td>
                                <td><?php echo ($outside["accept"]); ?></td><?php endif; ?>
                        </tr>
                        <tr>
                            <?php if($outside['apply_type'] == C('OUTSIDE_OUTSIDE_SALE_TYPE')): ?><td class="text-right">外调目的地：</td>
                                <td><?php echo ($outside["accept"]); ?></td>
                                <td class="text-right">外调原因：</td>
                                <td><?php echo ($outside["reason"]); ?></td>
                                <?php else: ?>
                                <td class="text-right">外调原因：</td>
                                <td colspan="3"><?php echo ($outside["reason"]); ?></td><?php endif; ?>
                        </tr>
                        <tr>
                            <td class="text-right">联系人：</td>
                            <td><?php echo ($outside["person"]); ?></td>
                            <td class="text-right">联系电话：</td>
                            <td><?php echo ($outside["phone"]); ?></td>
                        </tr>
                        <tr>
                            <td class="text-right">申请人：</td>
                            <td><?php echo ($outside["apply_username"]); ?></td>
                            <td class="text-right">申请时间：</td>
                            <td><?php echo ($outside["apply_time"]); ?></td>
                        </tr>
                        <tr>
                            <td class="text-right">预计调出日期：</td>
                            <td colspan="3"><?php echo ($outside["outside_date"]); ?></td>
                        </tr>

                        </tbody>
                    </table>

                    <?php if(!empty($approve)): ?><blockquote class="layui-elem-quote">审批信息</blockquote>
                        <table class="layui-table" lay-even="" lay-size="sm">
                            <colgroup>
                                <col width="100">
                                <col width="100">
                                <col width="100">
                                <col width="150">
                                <col width="100">
                                <col width="100">
                                <col width="100">
                                <col width="250">
                            </colgroup>
                            <tbody>
                            <?php if(is_array($approve)): $i = 0; $__LIST__ = $approve;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                    <th style="width: 10%;">审批人：</th>
                                    <td><?php echo ($vo["approver"]); ?></td>
                                    <th style="width: 10%;">审批时间：</th>
                                    <td><?php echo ($vo["approve_time"]); ?></td>
                                    <th style="width: 10%;">审批状态：</th>
                                    <td><?php echo ($vo["is_adoptName"]); ?></td>
                                    <th style="width: 10%;">审批意见：</th>
                                    <td><?php echo ($vo["remark"]); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                        </table><?php endif; ?>

                    <?php if(!empty($applyFile)): ?><blockquote class="layui-elem-quote">外调申请附件</blockquote>
                        <table class="layui-table" lay-size="sm" lay-even="" style="margin-top: 0!important;">
                            <colgroup>
                                <col width="15%">
                                <col width="40%">
                                <col width="30%">
                                <col width="15%">
                            </colgroup>
                            <tbody>
                            <tr>
                                <th>序号：</th>
                                <th>文件名称：</th>
                                <td>上传时间</td>
                                <th>操作：</th>
                            </tr>
                            <?php if(is_array($applyFile)): $i = 0; $__LIST__ = $applyFile;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                    <td><?php echo ($key+1); ?></td>
                                    <td><?php echo ($vo["file_name"]); ?></td>
                                    <td><?php echo ($vo["add_time"]); ?></td>
                                    <td><?php echo ($vo["operation"]); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                        </table><?php endif; ?>

                    <?php if($outside[status] == C('OUTSIDE_STATUS_COMPLETE')): ?><blockquote class="layui-elem-quote">外调验收单</blockquote>
                        <table class="layui-table" lay-size="sm" lay-even="" style="margin-top: 0!important;margin-bottom: 0!important;border-bottom: none">
                            <colgroup>
                                <col width="15%">
                                <col width="40%">
                                <col width="30%">
                                <col width="15%">
                            </colgroup>
                            <tbody>
                            <tr>
                                <th>验收人：</th>
                                <td><?php echo ($outside["check_person"]); ?></td>
                                <th>联系电话：</th>
                                <td><?php echo ($outside["check_phone"]); ?></td>
                            </tr>
                            <tr>
                                <th>验收时间：</th>
                                <td><?php echo ($outside["check_date"]); ?></td>
                                <td>备注：</td>
                                <td><?php echo ($outside["check_remark"]); ?></td>
                            </tr>
                            </tbody>
                        </table>
                        <?php if(!empty($checkFile)): ?><blockquote class="layui-elem-quote" style="padding: 5px 15px;border-left: 1px solid #e6e6e6;">外调验收单附件</blockquote>
                            <table class="layui-table" lay-size="sm" lay-even="" style="margin-top: 0!important;">
                                <colgroup>
                                    <col width="15%">
                                    <col width="40%">
                                    <col width="30%">
                                    <col width="15%">
                                </colgroup>
                                <tbody>
                                <tr>
                                    <th>序号：</th>
                                    <th>文件名称：</th>
                                    <th>上传时间</th>
                                    <th>操作：</th>
                                </tr>
                                <?php if(is_array($checkFile)): $i = 0; $__LIST__ = $checkFile;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                        <td><?php echo ($key+1); ?></td>
                                        <td><?php echo ($vo["file_name"]); ?></td>
                                        <td><?php echo ($vo["add_time"]); ?></td>
                                        <td><?php echo ($vo["operation"]); ?></td>
                                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                            </table><?php endif; endif; ?>
                </div>
            </div>

            <!--<div class="layui-tab-item">&lt;!&ndash;采购信息&ndash;&gt;-->
            <!--开发中...-->
            <!--</div>-->
            <!--<div class="layui-tab-item">&lt;!&ndash;效益分析&ndash;&gt;-->
            <!--开发中...-->
            <!--</div>-->
            <!--<div class="layui-tab-item">&lt;!&ndash;保养信息&ndash;&gt;-->
            <!--开发中...-->
            <!--</div>-->

            <!--<div class="layui-tab-item">&lt;!&ndash;设备折旧记录&ndash;&gt;-->
            <!--开发中...-->
            <!--</div>-->
        </div>
    </div>
    <div class="clear"></div>
</div>
</body>
</html>
<script>
    var usecompany ='<?php echo ($usecompany); ?>';
    layui.use('controller/assets/lookup/showLife', layui.factory('controller/assets/lookup/showLife'));
</script>