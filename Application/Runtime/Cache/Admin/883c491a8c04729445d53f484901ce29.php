<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        .layui-layer-tips .layui-layer-content {
            white-space: normal;
            width: auto;
            text-align: left;
            word-wrap: break-word;
            word-break: break-all;
        }

        #getAssetsListAssets {
            background-color: #fff !important;
        }

        .sameAssetsListCss .layui-table-cell, .sameAssetsListCss .layui-table-tool-panel li {
            overflow: inherit;
        }

        .layui-table-view .layui-table {
            width: 100%;
        }

        .layui-table, .layui-table-view {
            margin: 0;
        }

        .no-padding-td {
            padding: 0 !important;
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
            min-height: 300px;
        }

        .system-img {
            padding-top: 105px;
            padding-bottom: 105px;
            text-align: center;
        }

        .layui-textarea {
            width: 80%;
        }

        .layui-form-label {
            width: 115px;
        }

        .layui-layer-tips {
            width: 220px !important;
        }

        .uploadTips {
            margin-left: 70px;
            width: 210px;
            font-size: 12px;
            color: #999999;
            height: 30px;
            float: left;
            line-height: 30px;
        }

        #LAY-Assets-Lookup-doRenewal-showAssets .addIcon {
            position: absolute;
            right: -8px;
            top: 9px;
            cursor: pointer;
            color: #0a8ddf;
        }

        #LAY-Assets-Lookup-doRenewal-showAssets .layui-form-item .layui-input-inline {
            margin-right: 17px;
        }

        #LAY-Assets-Lookup-doRenewal-showAssets .layui-form-item .layui-inline {
            margin-left: 15px;
        }

        #addSuppliersDiv .layui-form-label {
            width: 115px !important;
        }

        .editFactory {
            float: right;
            color: #01AAED;
            cursor: pointer;
            font-weight: normal;
        }

        .editFactory:hover {
            color: #01AAED;
        }

        #multifile1, #multifile2, #unplan-upload0, #unplan-upload1, #unplan-upload2, #unplan-upload3 {
            float: right;
            font-size: 12px;
            font-weight: normal;
            color: #0a8ddf;
            cursor: pointer;
        }

        .photo {
            border-right: 1px solid #e6e6e6;
            border-bottom: 1px solid #e6e6e6;
            width: 304px;
            height: 420px;
        }

        .qrcode {
            display: none;
            width: 190px;
            height: 255px;
            padding: 20px 14px;
            box-sizing: border-box;
            position: absolute;
            background: url('/Public/images/qrcode1.png') no-repeat;
            background-size: 188px 220px;
            right: 85px;
            top: 335px;
            z-index: 30;
        }

        .qrcode img {
            width: 100%;
        }

        .qrcode .tip {
            width: 170px;
            text-align: center;
            vertical-align: top;
            display: block;
            font-size: 14px;
            color: #333;
        }

        #deleteAssetsPicBtn {
            position: absolute;
            top: 10px;
            right: 0;
        }

        .thc {
            text-align: center !important;
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
    <div class="layui-tab layui-tab-brief" lay-filter="change">
        <ul class="layui-tab-title">
            <li class="layui-this">基础信息</li>
            <li lay-id="2">资质信息</li>
            <?php if($showTechnicalInformation == 1): ?><li lay-id="3">技术资料</li><?php endif; ?>
            <?php if($showAssetsfile == 1): ?><li lay-id="4">设备档案</li><?php endif; ?>
            <?php if($assets['is_subsidiary'] == C('YES_STATUS')): ?><li lay-id="5">所属设备</li>
                <?php else: ?>
                <?php if(!empty($subsidiary)): ?><li lay-id="5">附属设备</li><?php endif; endif; ?>
            <?php if($showAssetsInsurance == 1): ?><li lay-id="6">设备参保</li><?php endif; ?>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">设备名称：<?php echo ($assets["assets"]); ?></div>
                    <div style="width: 708px;float: left;">
                        <table class="layui-table read-table" lay-size="sm" lay-even>
                            <colgroup>
                                <col width="195">
                                <col>
                                <col width="195">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr>
                                <th>档案盒编号：</th>
                                <td colspan="3"><?php echo ($assets["box_num"]); ?></td>
                            </tr>
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
                                <th>设备常用名：</th>
                                <td><?php echo ($assets["common_name"]); ?></td>
                                <th>设备分类：</th>
                                <td><?php echo ($assets["cate_name"]); ?></td>
                            </tr>
                            <tr>
                                <th>管理类别：</th>
                                <td><?php echo ($assets["assets_level_name"]); ?></td>
                                <th>系统辅助分类：</th>
                                <td><?php echo ($assets["helpcat"]); ?></td>
                            </tr>
                            <tr>
                                <th>设备原值(元)：</th>
                                <td><?php echo ($assets["buy_price"]); ?></td>
                                <th>预计使用年限：</th>
                                <td><?php echo ($assets["expected_life"]); ?></td>
                            </tr>
                            <tr>
                                <th>注册证编号：</th>
                                <td><?php echo ($assets["registration"]); ?></td>
                                <th>生产厂商：</th>
                                <td><?php echo ($factoryInfo["sup_name"]); ?></td>
                            </tr>
                            <tr>
                                <th>供应商：</th>
                                <td><?php echo ($supplierInfo["sup_name"]); ?></td>
                                <th>维修商：</th>
                                <td><?php echo ($repairInfo["sup_name"]); ?></td>
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
                                <th>设备类型：</th>
                                <td colspan="3"><?php echo ($assets["type"]); ?></td>
                            </tr>
                            <tr>
                                <th>出厂编号：</th>
                                <td><?php echo ($assets["factorynum"]); ?></td>
                                <th>发票编号：</th>
                                <td><?php echo ($assets["invoicenum"]); ?></td>
                            </tr>
                            <tr>
                                <th>所属科室：</th>
                                <td><?php echo ($assets["department"]); ?></td>
                                <th>管理科室：</th>
                                <td><?php echo ($assets["managedepart"]); ?></td>
                            </tr>
                            <tr>
                                <th>科室所在位置：</th>
                                <td><?php echo ($assets["departmentAddress"]); ?></td>
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
                                <td><?php echo ($assets["opendate"]); ?></td>
                                <th>付款日期：</th>
                                <td><?php echo ($assets["paytime"]); ?></td>
                            </tr>
                            <tr>
                                <th>剩余时间（月）：</th>
                                <td><?php echo ($assets["remaining_mounths"]); ?></td>
                                <th></th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>年限到期日期：</th>
                                <td><?php echo ($assets["life_expiration_date"]); ?></td>
                                <th></th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>是否付清：</th>
                                <td><?php echo ($assets["pay_statusName"]); ?></td>
                                <th>国产&进口：</th>
                                <td><?php echo ($assets["is_domesticName"]); ?></td>
                            </tr>
                            <tr>
                                <th>折旧方式：</th>
                                <td><?php echo ($assets["depreciation_method_name"]); ?></td>
                                <th>折旧年限：</th>
                                <td><?php echo ($assets["depreciable_lives"]); ?></td>
                            </tr>
                            <tr>
                                <th>设备原编码(备用)：</th>
                                <td><?php echo ($assets["assorignum_spare"]); ?></td>
                                <th>残净值率(%)：</th>
                                <td><?php echo ($assets["residual_value"]); ?></td>
                            </tr>
                            <tr>
                                <th>月折旧额：</th>
                                <td><?php echo ($assets["depreciable_quota_m"]); ?></td>
                                <th>累计折旧额：</th>
                                <td><?php echo ($assets["depreciable_quota_count"]); ?></td>
                            </tr>
                            <tr>
                                <th>资产净值：</th>
                                <td><?php echo ($assets["net_asset_value"]); ?></td>
                                <th>资产净额：</th>
                                <td><?php echo ($assets["net_assets"]); ?></td>
                            </tr>
                            <tr>
                                <th>巡查周期(天)：</th>
                                <td><?php echo ($assets["patrol_xc_cycle"]); ?></td>
                                <th>保养周期(天)：</th>
                                <td><?php echo ($assets["patrol_pm_cycle"]); ?></td>
                            </tr>
                            <tr>
                                <th>质控周期(天)：</th>
                                <td><?php echo ($assets["quality_cycle"]); ?></td>
                                <th>计量周期(天)：</th>
                                <td><?php echo ($assets["metering_cycle"]); ?></td>
                            </tr>
                            <tr>
                                <th>设备所在位置：</th>
                                <td colspan="3"><?php echo ($assets["address"]); ?></td>
                            </tr>
                            <tr>
                                <th>设备备注：</th>
                                <td colspan="3"><?php echo ($assets["remark"]); ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="photo fl">
                        <?php if(empty($assets["pic_url"])): ?><div class="system-img">
                                <i class="layui-icon" style="font-size: 120px;">&#xe60d;</i><br>暂无图片<br>
                            </div>
                            <?php else: ?>
                            <div class="layui-carousel" style="padding-top: 5px;padding-left: 7px;" id="carouselAssetsPic">
                                <div carousel-item="">
                                    <?php if(is_array($assetsPic)): $i = 0; $__LIST__ = $assetsPic;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><img src="<?php echo ($v); ?>" style="cursor: pointer;"/><?php endforeach; endif; else: echo "" ;endif; ?>
                                </div>
                                <?php if($menuData = get_menu('Assets','Lookup','addAssets')):?>
                                <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" id="deleteAssetsPicBtn">删除</button>
                                <?php endif?>
                            </div><?php endif; ?>
                    </div>
                    <?php if($menuData = get_menu('Assets','Lookup','addAssets')):?>
                    <div class="uploadTips">(上传图片建议像素为：290x430)</div>
                    <div class="layui-btn-group" style="margin-left: 70px;">
                        <button type="button" class="layui-btn layui-btn-sm upload" id="uploadAssetsPic" lay-submit lay-filter="upload" data-url="<?php echo ($menuData['actionurl']); ?>" style="margin: 0 0 0 60px;">
                            <i class="layui-icon">&#xe67c;</i>本地上传
                        </button>
                        <span id="paizhao">
                        <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" style="margin: 0 0 0 60px;">
                            <i class="layui-icon layui-icon-zqrcode-l" style="font-size: 13px;"></i>扫码上传
                        </button>
                        <div class="qrcode">
                            <img src="<?php echo ($codeUrl); ?>" alt="扫码上传">
                            <span class="tip">扫一扫，上传图片</span>
                        </div>
                        </span>
                    </div>
                    <?php endif?>
                </div>
                <div class="clear"></div>
                <div class="margin-bottom-15">
                    <div class="layui-tab layui-tab-brief" lay-filter="tableChange">
                        <ul class="layui-tab-title">
                            <li class="layui-this">状态变更记录</li>
                            <li>设备审查批准记录</li>
                            <li>设备转科记录</li>
                            <li>设备维修记录</li>
                            <!--功能开发中。。。后续开启-->
                            <li>设备质控记录</li>
                            <li>设备计量记录</li>
                            <li>设备不良事件记录</li>
                            <li>借调记录</li>
                            <li>设备折旧记录</li>
                        </ul>
                        <div class="layui-tab-content" style="padding: 0px">
                            <div class="layui-tab-item layui-show"><!--状态变更记录-->
                                <table id="Status" lay-filter="statusData"></table>
                            </div>
                            <div class="layui-tab-item"><!--设备审批记录-->
                                <div id="getAssetsedit" lay-filter="geteditData"></div>
                            </div>
                            <div class="layui-tab-item"><!--设备转科记录-->
                                <table id="transfer" lay-filter="transfer"></table>
                            </div>
                            <div class="layui-tab-item"><!--设备维修记录-->
                                <div id="RepairSearchList" lay-filter="RepairSearchList"></div>
                                <div class="layui-elem-quote unplan" style="margin-top: 20px;">
                                    非计划类文档
                                    <?php if($menuData = get_menu('Assets','Lookup','addAssets')):?>
                                    <span id="unplan-upload0" lay-tips="可一次选择多个文件上传，上传文件后缀名只能为.jpg/.jpeg/.png/.pdf/.xls/.xlsx/.doc/.docx/.ppt/.pptx">
                                                                            <i class="iconfont icon-shangchuan" style="font-size: 14px;"></i> 本地上传
                                                                        </span>
                                    <?php endif;?>
                                </div>
                                <table id="unplanDataList" lay-filter="unplanDataList"></table>
                            </div>
                            <div class="layui-tab-item"><!--设备质控记录-->
                                <table id="quality" lay-filter="qualityData"></table>
                                <div class="layui-elem-quote unplan" style="margin-top: 20px;">
                                    非计划类文档
                                    <?php if($menuData = get_menu('Assets','Lookup','addAssets')):?>
                                    <span id="unplan-upload1" lay-tips="可一次选择多个文件上传，上传文件后缀名只能为.jpg/.jpeg/.png/.pdf/.xls/.xlsx/.doc/.docx/.ppt/.pptx">
                                                                            <i class="iconfont icon-shangchuan" style="font-size: 14px;"></i> 本地上传
                                                                        </span>
                                    <?php endif;?>
                                </div>
                            </div>
                            <div class="layui-tab-item"><!--设备计量记录-->
                                <div id="meteringLists" lay-filter="meteringData"></div>
                                <div class="layui-elem-quote unplan" style="margin-top: 20px;">
                                    非计划类文档
                                    <?php if($menuData = get_menu('Assets','Lookup','addAssets')):?>
                                    <span id="unplan-upload2" lay-tips="可一次选择多个文件上传，上传文件后缀名只能为.jpg/.jpeg/.png/.pdf/.xls/.xlsx/.doc/.docx/.ppt/.pptx">
                                                                            <i class="iconfont icon-shangchuan" style="font-size: 14px;"></i> 本地上传
                                                                        </span>
                                    <?php endif;?>
                                </div>
                            </div>
                            <div class="layui-tab-item"><!--设备不良事件记录-->
                                <div id="getAdverseLists" lay-filter="getAdverseData"></div>
                                <div class="layui-elem-quote unplan" style="margin-top: 20px;">
                                    非计划类文档
                                    <?php if($menuData = get_menu('Assets','Lookup','addAssets')):?>
                                    <span id="unplan-upload3" lay-tips="可一次选择多个文件上传，上传文件后缀名只能为.jpg/.jpeg/.png/.pdf/.xls/.xlsx/.doc/.docx/.ppt/.pptx">
                                                                            <i class="iconfont icon-shangchuan" style="font-size: 14px;"></i> 本地上传
                                                                        </span>
                                    <?php endif;?>
                                </div>
                            </div>
                            <div class="layui-tab-item"><!--设备借调记录-->
                                <div id="borrowRecordList" lay-filter="borrowRecordData"></div>
                            </div>
                            <div class="layui-tab-item"><!--设备折旧记录-->
                                开发中...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-tab-item">
                <?php if($factoryInfo || $supplierInfo || $repairInfo): ?><!--生产厂商-->
                    <?php if(!empty($factoryInfo)): ?><div class="layui-elem-quote">
                            生产厂商资质信息
                            <?php if($menuData = get_menu('OfflineSuppliers','OfflineSuppliers','editOfflineSupplier')):?>
                            <a class="editFactory" data-id="<?php echo ($factoryInfo["olsid"]); ?>" data-url="<?php echo ($menuData['actionurl']); ?>">
                                <i class="layui-icon">&#xe642;</i>维护资质信息
                            </a>
                            <?php endif?>
                        </div>
                        <div class="margin-bottom-15">
                            <table class="layui-table read-table" lay-even="" lay-size="sm">
                                <tbody>
                                <tr>
                                    <th>生产厂商名称：</th>
                                    <td colspan="3"><?php echo ($factoryInfo["sup_name"]); ?></td>
                                </tr>
                                <tr>
                                    <th>业务员：</th>
                                    <td><?php echo ($factoryInfo["salesman_name"]); ?></td>
                                    <th>业务员联系电话：</th>
                                    <td><?php echo ($factoryInfo["salesman_phone"]); ?></td>
                                </tr>
                                <tr>
                                    <th>技术人员：</th>
                                    <td><?php echo ($factoryInfo["artisan_name"]); ?></td>
                                    <th>技术人员联系电话：</th>
                                    <td><?php echo ($factoryInfo["artisan_phone"]); ?></td>
                                </tr>
                                </tbody>
                            </table>

                        </div>
                        <div class="margin-bottom-15">
                            <blockquote class="layui-elem-quote">生产厂商证照信息</blockquote>
                            <table class="layui-table" lay-size="sm" lay-even="" style="margin-top: 0!important;">
                                <colgroup>
                                    <col width="8%">
                                    <col width="35%">
                                    <col width="15%">
                                    <col width="15%">
                                    <col width="15%">
                                    <col>
                                </colgroup>
                                <tbody>
                                <tr>
                                    <th class="thc">序号</th>
                                    <th class="thc">文件名称</th>
                                    <td class="thc">上传时间</td>
                                    <td class="thc">发证(备案)日期</td>
                                    <td class="thc">有效期限</td>
                                    <th class="thc">操作</th>
                                </tr>
                                <?php if(empty($factoryData)): ?><tr>
                                        <td class="td-align-center" colspan="6">暂无文件</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php if(is_array($factoryData)): $i = 0; $__LIST__ = $factoryData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                            <td class="thc"><?php echo ($key+1); ?></td>
                                            <td class="thc"><?php echo ($vo["name"]); ?></td>
                                            <td class="thc"><?php echo ($vo["adddate"]); ?></td>
                                            <td class="thc"><?php echo ($vo["record_date"]); ?></td>
                                            <td class="thc"><?php echo ($vo["term_date"]); ?></td>
                                            <td class="thc"><?php echo ($vo["operation"]); ?></td>
                                        </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                                </tbody>
                            </table>
                        </div><?php endif; ?>
                    <!--供应商-->
                    <?php if(!empty($supplierInfo)): ?><div class="layui-elem-quote">
                            供应商资质信息
                            <?php if($menuData = get_menu('OfflineSuppliers','OfflineSuppliers','editOfflineSupplier')):?>
                            <a class="editFactory" data-id="<?php echo ($supplierInfo["olsid"]); ?>" data-url="<?php echo ($menuData['actionurl']); ?>">
                                <i class="layui-icon">&#xe642;</i>维护资质信息
                            </a>
                            <?php endif?>
                        </div>
                        <div class="margin-bottom-15">
                            <table class="layui-table read-table" lay-even="" lay-size="sm">
                                <tbody>
                                <tr>
                                    <th>供应商名称：</th>
                                    <td colspan="3"><?php echo ($supplierInfo["sup_name"]); ?></td>
                                </tr>
                                <tr>
                                    <th>业务员：</th>
                                    <td><?php echo ($supplierInfo["salesman_name"]); ?></td>
                                    <th>业务员联系电话：</th>
                                    <td><?php echo ($supplierInfo["salesman_phone"]); ?></td>
                                </tr>
                                <tr>
                                    <th>技术人员：</th>
                                    <td><?php echo ($supplierInfo["artisan_name"]); ?></td>
                                    <th>技术人员联系电话：</th>
                                    <td><?php echo ($supplierInfo["artisan_phone"]); ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="margin-bottom-15">
                            <blockquote class="layui-elem-quote">供应商证照信息</blockquote>
                            <table class="layui-table" lay-size="sm" lay-even="" style="margin-top: 0!important;">
                                <colgroup>
                                    <col width="8%">
                                    <col width="35%">
                                    <col width="15%">
                                    <col width="15%">
                                    <col width="15%">
                                    <col>
                                </colgroup>
                                <tbody>
                                <tr>
                                    <th class="thc">序号</th>
                                    <th class="thc">文件名称</th>
                                    <td class="thc">上传时间</td>
                                    <td class="thc">发证(备案)日期</td>
                                    <td class="thc">有效期限</td>
                                    <th class="thc">操作</th>
                                </tr>
                                <?php if(empty($supplierData)): ?><tr>
                                        <td class="td-align-center" colspan="6">暂无文件</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php if(is_array($supplierData)): $i = 0; $__LIST__ = $supplierData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                            <td class="thc"><?php echo ($key+1); ?></td>
                                            <td class="thc"><?php echo ($vo["name"]); ?></td>
                                            <td class="thc"><?php echo ($vo["adddate"]); ?></td>
                                            <td class="thc"><?php echo ($vo["record_date"]); ?></td>
                                            <td class="thc"><?php echo ($vo["term_date"]); ?></td>
                                            <td class="thc"><?php echo ($vo["operation"]); ?></td>
                                        </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                                </tbody>
                            </table>
                        </div><?php endif; ?>
                    <!--维修商-->
                    <?php if(!empty($repairInfo)): ?><div class="layui-elem-quote">
                            维修商资质信息
                            <?php if($menuData = get_menu('OfflineSuppliers','OfflineSuppliers','editOfflineSupplier')):?>
                            <a class="editFactory" data-id="<?php echo ($repairInfo["olsid"]); ?>" data-url="<?php echo ($menuData['actionurl']); ?>">
                                <i class="layui-icon">&#xe642;</i>维护资质信息
                            </a>
                            <?php endif?>
                        </div>
                        <div class="margin-bottom-15">
                            <table class="layui-table read-table" lay-even="" lay-size="sm">
                                <tbody>
                                <tr>
                                    <th>维修商名称：</th>
                                    <td colspan="3"><?php echo ($repairInfo["sup_name"]); ?></td>
                                </tr>
                                <tr>
                                    <th>业务员：</th>
                                    <td><?php echo ($repairInfo["salesman_name"]); ?></td>
                                    <th>业务员联系电话：</th>
                                    <td><?php echo ($repairInfo["salesman_phone"]); ?></td>
                                </tr>
                                <tr>
                                    <th>技术人员：</th>
                                    <td><?php echo ($repairInfo["artisan_name"]); ?></td>
                                    <th>技术人员联系电话：</th>
                                    <td><?php echo ($repairInfo["artisan_phone"]); ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="margin-bottom-15">
                            <blockquote class="layui-elem-quote">维修商证照信息</blockquote>
                            <table class="layui-table" lay-size="sm" lay-even="" style="margin-top: 0!important;">
                                <colgroup>
                                    <col width="8%">
                                    <col width="35%">
                                    <col width="15%">
                                    <col width="15%">
                                    <col width="15%">
                                    <col>
                                </colgroup>
                                <tbody>
                                <tr>
                                    <th class="thc">序号</th>
                                    <th class="thc">文件名称</th>
                                    <td class="thc">上传时间</td>
                                    <td class="thc">发证(备案)日期</td>
                                    <td class="thc">有效期限</td>
                                    <th class="thc">操作</th>
                                </tr>
                                <?php if(empty($repairData)): ?><tr>
                                        <td class="td-align-center" colspan="6">暂无文件</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php if(is_array($repairData)): $i = 0; $__LIST__ = $repairData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                            <td class="thc"><?php echo ($key+1); ?></td>
                                            <td class="thc"><?php echo ($vo["name"]); ?></td>
                                            <td class="thc"><?php echo ($vo["adddate"]); ?></td>
                                            <td class="thc"><?php echo ($vo["record_date"]); ?></td>
                                            <td class="thc"><?php echo ($vo["term_date"]); ?></td>
                                            <td class="thc"><?php echo ($vo["operation"]); ?></td>
                                        </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                                </tbody>
                            </table>
                        </div><?php endif; ?>
                    <?php else: ?>
                    该设备暂无资质信息<?php endif; ?>

            </div>
            <?php if($showTechnicalInformation == 1): ?><div class="layui-tab-item">
                    <div class="margin-bottom-15">
                        <div class="layui-elem-quote">
                            技术资料
                            <span id="multifile1"
                                  lay-tips="可一次选择多个文件上传，上传文件后缀名只能为.jpg/.jpeg/.png/.pdf/.xls/.xlsx/.doc/.docx/.ppt/.pptx"><i
                                    class="iconfont icon-shangchuan" style="font-size: 14px;"></i> 本地上传</span>
                        </div>
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
                                    <td colspan="4" style="text-align: center;">暂无相关数据</td>
                                </tr>
                                <?php else: ?>
                                <?php if(is_array($techni_files)): $i = 0; $__LIST__ = $techni_files;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                        <td class="xuhao td-align-center"><?php echo ($key+1); ?></td>
                                        <td class="td-align-center"><?php echo ($vo["file_name"]); ?></td>
                                        <td class="td-align-center"><?php echo ($vo["add_time"]); ?></td>
                                        <td class="td-align-center"><?php echo ($vo["html"]); ?></td>
                                    </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                            </tbody>
                        </table>

                    </div>
                    <div class="margin-bottom-15">
                        <form class="layui-form">
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label" style="width: 80px;">设备名称：</label>
                                    <div class="layui-input-inline" style="margin-right: 0;">
                                        <div class="input-group">
                                            <input type="text" class="form-control bsSuggest" id="getAssetsListAssets"
                                                   placeholder="请输入设备名称" name="assets">
                                            <div class="input-group-btn">
                                                <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label" style="width: 80px;">规格/型号：</label>
                                    <div class="layui-input-inline" style="margin-right: 0;">
                                        <input type="text" name="model" placeholder="请输入规格/型号" autocomplete="off"
                                               class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label" style="width: 50px;">品牌：</label>
                                    <div class="layui-input-inline" style="margin-right: 0;">
                                        <input type="text" name="brand" placeholder="请输入品牌" autocomplete="off"
                                               class="layui-input">
                                    </div>
                                    <button class="layui-btn" type="button" lay-submit lay-filter="sameAssetsSearch"
                                            style="margin-left: 20px;">
                                        <i class="layui-icon">&#xe615;</i> 搜 索
                                    </button>
                                </div>
                            </div>
                            <div class="layui-card-header">
                                <div class="fl">
                                    <i class="layui-icon"></i> 使用同一个文档设备列表
                                    <button class="layui-btn layui-btn-sm" type="button" id="batchSameAssetsBind"
                                            style="margin-left: 20px;">选中批量绑定
                                    </button>
                                </div>
                            </div>
                            <div class="contain sameAssetsListCss">
                                <table id="sameAssetsList" lay-filter="sameAssetsListData"></table>
                            </div>
                            <div class="layui-form-item">
                                <div style="text-align: center;margin-top: 20px;">
                                    <?php if($menuData = get_menu('Assets','Lookup','addAssets')):?>
                                    <div class="layui-form-item" style="margin-top: 15px;">
                                        <div class="layui-inline">
                                            <button type="button" class="layui-btn" id="uploadFileQuali">
                                                <i class="layui-icon">&#xe67c;</i>确定上传
                                            </button>
                                        </div>
                                    </div>
                                    <?php endif;?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div><?php endif; ?>
            <?php if($showAssetsfile == 1): ?><div class="layui-tab-item">
                    <div class="layui-form">
                        <div class="margin-bottom-15">
                            <p style="margin: 15px 0;">档案盒编号：<?php echo ($assets["box_num"]); ?></p>
                            <div class="layui-elem-quote">
                                设备档案
                                <?php if($menuData = get_menu('Assets','Lookup','addAssets')):?><span id="multifile2"
                                                                                                      lay-tips="可一次选择多个文件上传，上传文件后缀名只能为.jpg/.jpeg/.png/.pdf/.xls/.xlsx/.doc/.docx/.ppt/.pptx/.rar/.zip"><i
                                    class="iconfont icon-shangchuan" style="font-size: 14px;"></i> 本地上传</span><?php endif;?>
                            </div>
                            <table class="layui-table mgt0" lay-size="sm">
                                <colgroup>
                                    <col width="80">
                                    <col>
                                    <col width="160">
                                    <col width="160">
                                    <col width="160">
                                    <col width="160">
                                </colgroup>
                                <thead>
                                <tr>
                                    <td class="td-align-center">序号</td>
                                    <td class="td-align-center">文件名称</td>
                                    <td class="td-align-center">上传时间</td>
                                    <td class="td-align-center">档案日期</td>
                                    <td class="td-align-center">过期日期</td>
                                    <td class="td-align-center">操作</td>
                                </tr>
                                </thead>
                                <tbody id="arcfileList">
                                <?php if(empty($archives_files)): ?><tr class="arc-empty">
                                        <td colspan="6" style="text-align: center;">暂无相关数据</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php if(is_array($archives_files)): $i = 0; $__LIST__ = $archives_files;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                            <td class="xuhao td-align-center"><?php echo ($key+1); ?></td>
                                            <td class="td-align-center"><?php echo ($vo["file_name"]); ?></td>
                                            <td class="td-align-center"><?php echo ($vo["add_time"]); ?></td>
                                            <td class="td-align-center" style="padding: 0;"><?php echo ($vo["archive_time"]); ?></td>
                                            <td class="td-align-center" style="padding: 0;"><?php echo ($vo["expire_time"]); ?></td>
                                            <td class="td-align-center"><?php echo ($vo["html"]); ?></td>
                                        </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                                </tbody>
                            </table>
                            <button style="display: none" type="button" id="testUpload"></button>
                            <?php if($menuData = get_menu('Assets','Lookup','addAssets')):?>
                            <input type="hidden" name="box_id" value="<?php echo ($box_id); ?>"/>
                            <div class="layui-form-item" style="margin-top: 15px;">
                                <div class="layui-inline">
                                    <button type="button" class="layui-btn" lay-submit lay-filter="uploadFileArcSubmit" id="uploadFileArc">
                                        <i class="layui-icon">&#xe67c;</i>确定上传
                                    </button>
                                </div>
                            </div>
                            <?php endif;?>
                        </div>
                    </div>
                </div><?php endif; ?>
            <?php if($assets['is_subsidiary'] == C('YES_STATUS')): ?><!--显示所属主设备信息-->
                <div class="layui-tab-item">
                    <div class="margin-bottom-15">
                        <div class="layui-elem-quote">所属设备名称：<?php echo ($mainAssets["assets"]); ?></div>
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
                                    <td><?php echo ($mainAssets["assnum"]); ?></td>
                                    <th>设备原编号：</th>
                                    <td><?php echo ($mainAssets["assorignum"]); ?></td>
                                </tr>
                                <tr>
                                    <th>规格 / 型号：</th>
                                    <td><?php echo ($mainAssets["model"]); ?></td>
                                    <th>产品序列号：</th>
                                    <td><?php echo ($mainAssets["serialnum"]); ?></td>
                                </tr>
                                <tr>
                                    <th>设备分类：</th>
                                    <td colspan="3"><?php echo ($mainAssets["cate_name"]); ?></td>
                                </tr>
                                <tr>
                                    <th>系统辅助分类：</th>
                                    <td><?php echo ($mainAssets["helpcat"]); ?></td>
                                    <th>设备原值(元)：</th>
                                    <td><?php echo ($mainAssets["buy_price"]); ?></td>
                                </tr>
                                <tr>
                                    <th>预计使用年限：</th>
                                    <td><?php echo ($mainAssets["expected_life"]); ?></td>
                                    <th>残净值率(%)：</th>
                                    <td><?php echo ($mainAssets["residual_value"]); ?></td>
                                </tr>
                                <tr>
                                    <th>生产厂商：</th>
                                    <td><?php echo ($mainAssets_factory["factory"]); ?></td>
                                    <th>供应商：</th>
                                    <td><?php echo ($mainAssets_factory["supplier"]); ?></td>
                                </tr>
                                <tr>
                                    <th>出厂日期：</th>
                                    <td><?php echo ($mainAssets["factorydate"]); ?></td>
                                    <th>保修到期日期：</th>
                                    <td><?php echo ($mainAssets["guarantee_date"]); ?></td>
                                </tr>
                                <tr>
                                    <th>单位：</th>
                                    <td><?php echo ($mainAssets["unit"]); ?></td>
                                    <th>品牌：</th>
                                    <td><?php echo ($mainAssets["brand"]); ?></td>
                                </tr>
                                <tr>
                                    <th>出厂编号：</th>
                                    <td><?php echo ($mainAssets["factorynum"]); ?></td>
                                    <th>发票编号：</th>
                                    <td><?php echo ($mainAssets["invoicenum"]); ?></td>
                                </tr>
                                <tr>
                                    <th>设备类型：</th>
                                    <td colspan="3"><?php echo ($mainAssets["type"]); ?></td>
                                </tr>
                                <tr>
                                    <th>所属科室：</th>
                                    <td><?php echo ($mainAssets["department"]); ?></td>
                                    <th>管理科室：</th>
                                    <td><?php echo ($mainAssets["managedepart"]); ?></td>
                                </tr>
                                <tr>
                                    <th>所在位置：</th>
                                    <td><?php echo ($mainAssets["address"]); ?></td>
                                    <th>资产负责人：</th>
                                    <td><?php echo ($mainAssets["assetsrespon"]); ?></td>
                                </tr>
                                <tr>
                                    <th>财务分类：</th>
                                    <td><?php echo ($mainAssets["finance"]); ?></td>
                                    <th>设备来源：</th>
                                    <td><?php echo ($mainAssets["assfrom"]); ?></td>
                                </tr>
                                <tr>
                                    <th>资金来源：</th>
                                    <td><?php echo ($mainAssets["capitalfrom"]); ?></td>
                                    <th>入库日期：</th>
                                    <td><?php echo ($mainAssets["storage_date"]); ?></td>
                                </tr>
                                <tr>
                                    <th>启用日期：</th>
                                    <td colspan="3"><?php echo ($mainAssets["opendate"]); ?></td>
                                </tr>
                                <tr>
                                    <th>折旧方式：</th>
                                    <td><?php echo ($mainAssets["depreciation_method_name"]); ?></td>
                                    <th>折旧年限：</th>
                                    <td><?php echo ($mainAssets["depreciable_lives"]); ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="photo fl" style="width: 24%; text-align: center;margin-left: 3%;">
                            <div class="img-content">
                                <?php if($mainAssets["pic_url"] == ''): ?><div class="system-img">
                                        <i class="layui-icon" style="font-size: 120px;">&#xe60d;</i><br>暂无图片<br>
                                    </div>
                                    <?php else: ?>
                                    <img src="<?php echo ($mainAssets["pic_url"]); ?>" alt=""/><?php endif; ?>
                            </div>
                            <?php if($menuData = get_menu('Assets','Lookup','addAssets')):?>
                            <button type="button" class="layui-btn upload" data-id="<?php echo ($mainAssets["assid"]); ?>" lay-submit lay-filter="upload" data-url="<?php echo ($menuData['actionurl']); ?>" style="margin-top: 10px;">
                                <i class="layui-icon">&#xe67c;</i>上传图片
                            </button>
                            <?php endif?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!--显示附属设备列表-->
                <?php if(!empty($subsidiary)): ?><div class="layui-tab-item">
                        <div class="margin-bottom-15">
                            <div class="layui-elem-quote">附属设备</div>
                            <table lay-filter="subsidiaryList">
                                <thead>
                                <tr>
                                    <th lay-data="{field:'assets', width:180, align:'center'}">设备名称</th>
                                    <th lay-data="{field:'assnum', width:180, align:'center'}">设备编号</th>
                                    <th lay-data="{field:'model', width:140, align:'center'}">规格/型号</th>
                                    <th lay-data="{field:'category', width:160, align:'center'}">设备分类</th>
                                    <th lay-data="{field:'department', width:180, align:'center'}">所属科室</th>
                                    <th lay-data="{field:'brand', width:140, align:'center'}">品牌</th>
                                    <th lay-data="{field:'unit', width:100, align:'center'}">单位</th>
                                    <th lay-data="{field:'buy_price', width:120, align:'center'}">设备原值(元)</th>
                                    <th lay-data="{field:'operation', width:120, align:'center'}">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if(is_array($subsidiary)): $i = 0; $__LIST__ = $subsidiary;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>

                                        <td><?php echo ($vo["assets"]); ?></td>
                                        <td><?php echo ($vo["assnum"]); ?></td>
                                        <td><?php echo ($vo["model"]); ?></td>
                                        <td><?php echo ($vo["category"]); ?></td>
                                        <td><?php echo ($vo["department"]); ?></td>
                                        <td><?php echo ($vo["brand"]); ?></td>
                                        <td><?php echo ($vo["unit"]); ?></td>
                                        <td><?php echo ($vo["buy_price"]); ?></td>
                                        <td><?php echo ($vo["operation"]); ?></td>

                                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div><?php endif; endif; ?>
            <?php if($showAssetsInsurance == 1): ?><div class="layui-tab-item">
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
                    <?php if(!empty($doRenewalMenu)): ?><fieldset style="margin-top: 15px;" id="LAY-Assets-Lookup-doRenewal-showAssets">
                            <legend style="color: #00a6c8;">新增维保信息</legend>
                            <form class="layui-form" action="doRenewal" id="addInsurance">
                                <input type="hidden" name="action" value="<?php echo ($url); ?>">
                                <div class="layui-form-item">
                                    <div class="layui-inline">
                                        <label class="layui-form-label"><span class="rquireCoin"> * </span>维保购入日期：</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" readonly placeholder="请选择维保购入日期" style="cursor: pointer;" name="buydate" id="doRenewalBuydate">
                                        </div>
                                    </div>

                                    <div class="layui-inline">
                                        <label class="layui-form-label"><span class="rquireCoin"> * </span>维保性质：</label>
                                        <input type="hidden" name="ols_facid" value="<?php echo ($ols_facid); ?>">
                                        <div class="layui-input-inline">
                                            <select id="nature" name="nature" lay-verify="required" lay-filter="nature">
                                                <option value="">请选择</option>
                                                <?php if(!empty($usecompany)): ?><option value="0">原厂续保</option><?php endif; ?>
                                                <option value="1">第三方</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <div class="layui-inline">
                                        <div class="layui-form-label"><span class="rquireCoin"> * </span>维保公司：</div>
                                        <div class="layui-input-inline">
                                            <select name="company_id" class="input_select" lay-verify="required" lay-search="" lay-filter="company_id">
                                                <option value="-1">请先选择维保性质</option>
                                            </select>
                                        </div>
                                        <div class="addIcon">
                                            <?php if($menuData = get_menu('OfflineSuppliers','OfflineSuppliers','addOfflineSupplier')):?>
                                            <i class="layui-icon" id="addSupplier"> &#xe654;</i>
                                            <?php endif?>
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">维保费用：</label>
                                        <div class="layui-input-inline">
                                            <input type="text" autocomplete="off" class="layui-input" name="cost" lay-verify="cost" placeholder="请输入维保费用">
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <div class="layui-inline">
                                        <label class="layui-form-label"><span class="rquireCoin"> * </span>维保开始日期：</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" readonly placeholder="请选择选维保开始日期" style="cursor: pointer;" name="startdate" id="doRenewalStartdate">
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label"><span class="rquireCoin"> * </span>维保结束日期：</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" readonly placeholder="请选择选维保结束日期" style="cursor: pointer;" name="overdate" id="doRenewalOverdate">

                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <div class="layui-inline">
                                        <label class="layui-form-label"><span class="rquireCoin"> * </span>联系人：</label>
                                        <div class="layui-input-inline">
                                            <input type="text" value="" name="contacts" class="layui-input" lay-verify="required" placeholder="请输入联系人">
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label"><span class="rquireCoin"> * </span>联系电话：</label>
                                        <div class="layui-input-inline">
                                            <input type="text" value="" name="telephone" class="layui-input" lay-verify="phone" placeholder="请输入联系电话">
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item layui-form-text" style="margin-left: 15px;">
                                    <label class="layui-form-label"><span class="rquireCoin"> * </span>维保内容：</label>
                                    <div class="layui-input-block">
                                        <textarea placeholder="请输入维保内容" class="layui-textarea" name="content" lay-verify="required"></textarea>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <div class="layui-inline">
                                        <input type="hidden" name="uploadAction" value="<?php echo ($uploadAction); ?>">
                                        <label class="layui-form-label"><span class="rquireCoin"></span> 相关文件上传：</label>
                                        <div class="layui-input-inline" style="width: 500px;">
                                            <button type="button" class="layui-btn" id="file_url" name="file_url" lay-tips="只可以上传word文档、PDF文件、或扫描图片上传">
                                                <i class="layui-icon">&#xe67c;</i>上传文件
                                            </button>
                                            <span class="file_url" style="margin-left: 15px;"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item layui-form-text" style="margin-left: 15px;">
                                    <label class="layui-form-label">备注：</label>
                                    <div class="layui-input-block">
                                        <textarea class="layui-textarea" name="remark"></textarea>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <div style="text-align: center">
                                        <button class="layui-btn" lay-submit lay-filter="addDoRenewal"><i class="layui-icon">&#xe609;</i> 新增参保信息</button>
                                        <button type="reset" class="layui-btn layui-btn-primary"><i class="layui-icon">ဂ</i>重置</button>
                                    </div>
                                </div>
                            </form>
                        </fieldset><?php endif; ?>
                </div><?php endif; ?>
        </div>
    </div>
</div>
<div id="addSuppliersDiv" style="display: none">
    <div class="containDiv">
        <div class="layui-row">
            <form class="layui-form" action="">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label"><span class="rquireCoin">*</span> 厂商编号：</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" name="sup_num" readonly value="<?php echo ($sup_num); ?>">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label"><span class="rquireCoin">*</span> 厂商名称：</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" name="sup_name" placeholder="请输入厂商名称" lay-verify="required">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label"><span class="rquireCoin">*</span> 厂商类型：</label>
                        <div class="layui-input-inline">
                            <select name="suppliers_type" xm-select="suppliers_type" xm-select-search="" lay-verify="required">
                                <option value="1">供应商</option>
                                <option value="2">生产商</option>
                                <option value="3">维修商</option>
                                <option value="4">维保商</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">业务联系人：</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" name="salesman_name" placeholder="请输入业务联系人">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">业务联系人电话：</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" name="salesman_phone" placeholder="请输入业务联系人电话" lay-verify="tel">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">省份：</label>
                        <div class="layui-input-inline">
                            <select name="provinces" lay-filter="provinces" lay-search>
                                <option value="">请选择</option>
                                <?php if(is_array($provinces)): $i = 0; $__LIST__ = $provinces;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["provinceid"]); ?>"><?php echo ($vo["province"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">城市：</label>
                        <div class="layui-input-inline">
                            <select name="city" lay-filter="city" lay-search>
                                <option value="">请选择省份</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">区/城镇：</label>
                        <div class="layui-input-inline">
                            <select name="areas" lay-search>
                                <option value="">请选择城市</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">通讯地址：</label>
                    <div class="layui-input-block" style="width: 540px; margin-left: 145px;">
                        <input type="text" class="layui-input" name="address" placeholder="请输入通讯地址">
                    </div>
                </div>
                <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                    <button class="layui-btn" type="button" value="" lay-submit lay-filter="addSuppliers">
                        <i class="layui-icon">&#xe609;</i> 添加
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary">
                        <i class="layui-icon">ဂ</i> 重置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
<script>
    var today = "<?php echo ($today); ?>";
    var usecompany = '<?php echo($usecompany);?>';
    if (usecompany != '') {
        usecompany = JSON.parse(usecompany);
    }
</script>
<script>
    layui.use('controller/assets/lookup/showAssets', layui.factory('controller/assets/lookup/showAssets'));
</script>