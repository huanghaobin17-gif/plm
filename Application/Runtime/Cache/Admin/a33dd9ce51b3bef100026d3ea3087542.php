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
        #LAY-Assets-Lookup-editAssets .layui-elem-quote {
            margin-top: 15px;
        }

        #LAY-Assets-Lookup-editAssets .layui-form-item .layui-inline {
            margin-top: 5px;
        }

        #LAY-Assets-Lookup-editAssets .subsidiaryAssets {
            display: none;
        }

        #LAY-Assets-Lookup-editAssets .layui-form-item {
            margin-bottom: 5px;
        }

        #LAY-Assets-Lookup-editAssets .form-control {
            padding: 5px 3px 6px 5px;
        }

        #LAY-Assets-Lookup-editAssets .layui-select-disabled .layui-disabled {
            background-color: #fbfbfb;
        }

        #LAY-Assets-Lookup-editAssets .addIcon {
            z-index: 99;
            position: absolute;
            right: -25px;
            top: 11px;
            cursor: pointer;
            color: #0a8ddf;
        }

        #LAY-Assets-Lookup-editAssets .txt {
            position: absolute;
            right: -15px;
            top: 11px;
            color: #999 !important;
            font-size: 12px !important;
            line-height: 20px;
        }

        #LAY-Assets-Lookup-editAssets .layui-form-label {
            width: 115px;
        }

        #LAY-Assets-Lookup-editAssets .layui-form-radio {
            margin: 6px 10px 0 5px;
        }

        #date4 {
            cursor: pointer;
        }

        .formatDate {
            cursor: pointer;
        }

        .suggest {
            color: #5fb878
        }

        body .demo-class .layui-layer-btn .layui-layer-btn0 {
            background-color: #c2c2c2;
            border: 1px solid #c2c2c2;
            color: #FFF;
        }

        body .demo-class .layui-layer-btn .layui-layer-btn1 {
            background-color: #1E9FFF;
            border: 1px solid #1E9FFF;
            color: #FFF;
        }
    </style>
</head>
<body>
<div class="layui-fluid" id="LAY-Assets-Lookup-editAssets">
    <div class="layui-row">
        <form class="layui-form" action="">
            <input type="hidden" name="assid" value="<?php echo ($asInfo["assid"]); ?>"/>
            <blockquote class="layui-elem-quote" style="padding: 5px 15px;margin-bottom: 10px;">设备基础信息
            </blockquote>
            <div class="layui-form-item">
                <div class="layui-form-item">
                    <?php if($showHospital == C('YES_STATUS')): ?><div class="layui-inline">
                            <label class="layui-form-label"><span class="rquireCoin"> * </span> 所属医院：</label>
                            <div class="layui-input-inline">
                                <input type="text" readonly autocomplete="off" placeholder="请输入设备名称"
                                       value="<?php echo ($hosInfo["hospital_name"]); ?>" class="layui-input">
                            </div>
                        </div><?php endif; ?>
                    <div class="layui-inline" style="margin-right: 10px;">
                        <label class="layui-form-label"><span class="rquireCoin">*</span> 附属设备：</label>
                        <div class="layui-input-block" style="width: 188px;position:static;margin-left:145px">
                            <input type="radio" lay-filter="is_subsidiary" name="is_subsidiary" value="1" disabled
                                   title="是"
                            <?php if($asInfo['is_subsidiary'] == C('YES_STATUS')): ?>checked<?php endif; ?>
                            >
                            <input type="radio" lay-filter="is_subsidiary" name="is_subsidiary" value="0" disabled
                                   title="否"
                            <?php if($asInfo['is_subsidiary'] == C('NO_STATUS')): ?>checked<?php endif; ?>
                            >
                        </div>
                    </div>

                    <div class="layui-inline subsidiaryAssets">
                        <label class="layui-form-label"><span class="rquireCoin">*</span> 所属设备：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="main_assets" readonly autocomplete="off"
                                   value="<?php echo ($asInfo["main_assets"]); ?>" class="layui-input">
                        </div>
                    </div>
                    <div style="display:none;" id="main_assets_div"></div>
                </div>

                <div class="layui-inline">
                    <label class="layui-form-label"><span class="rquireCoin">*</span> 设备名称：</label>
                    <div class="layui-input-inline">
                        <input type="hidden" name="assets" value="<?php echo ($asInfo["assets"]); ?>">
                        <select name="dic_assets_sel" lay-verify="dic_assets_sel" lay-filter="dic_assets_sel" lay-search="">
                            <?php if(is_array($dic_assets)): $i = 0; $__LIST__ = $dic_assets;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($asInfo['assets'] == $vo['assets']): ?><option value="<?php echo ($vo["assets"]); ?>" selected><?php echo ($vo["assets"]); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($vo["assets"]); ?>"><?php echo ($vo["assets"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                        <div class="addIcon">
                            <?php if($menuData = get_menu('BaseSetting','Dictionary','addAssetsDic')):?>
                            <i class="layui-icon" id="addAssetsDic"> &#xe654;</i>
                            <?php endif?>
                        </div>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">设备常用名：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="common_name" autocomplete="off" placeholder="请输入设备常用名"
                               value="<?php echo ($asInfo["common_name"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span> 规格/型号：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="model" lay-verify="model" autocomplete="off"
                               placeholder="请输入设备规格 / 型号" value="<?php echo ($asInfo["model"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span> 产品序列号：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="serialnum" lay-verify="serialnum" autocomplete="off"
                               placeholder="请输入产品序列号" value="<?php echo ($asInfo["serialnum"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">注册证编号：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="registration" placeholder="请输入注册证编号" autocomplete="off"
                               value="<?php echo ($asInfo["registration"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">设备编号：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="" disabled autocomplete="off" placeholder="此编号由系统自动生成"
                               value="<?php echo ($asInfo["assnum"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="rquireCoin">*</span> 设备分类：</label>
                    <div class="layui-input-inline">
                        <select name="catid" lay-verify="catid" lay-search id="childCategory" disabled>
                            <option value="">请选择设备分类</option>
                            <?php if(is_array($category)): $i = 0; $__LIST__ = $category;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['catid'] == $asInfo['catid']): ?><option value="<?php echo ($vo["catid"]); ?>" selected><?php echo ($vo["html"]); echo ($vo["category"]); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($vo["catid"]); ?>"><?php echo ($vo["html"]); echo ($vo["category"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">管理类别：</label>
                    <div class="layui-input-inline">
                        <select name="assets_level" lay-search>
                            <option value="">请选择管理类别</option>
                            <?php if(is_array($assetsLevel)): $i = 0; $__LIST__ = $assetsLevel;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; if($v['value'] == $asInfo['assets_level']): ?><option value="<?php echo ($v["value"]); ?>" selected><?php echo ($v["name"]); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($v["value"]); ?>"><?php echo ($v["name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline mainAssets">
                    <label class="layui-form-label">辅助分类：</label>
                    <div class="layui-input-inline">
                        <select name="helpcatid" class="layui-input" lay-search="">
                            <option value="">请选择辅助分类</option>
                            <?php if(is_array($assets_helpcat)): $i = 0; $__LIST__ = $assets_helpcat;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == $asInfo['helpcatid']): ?><option value="<?php echo ($key); ?>" selected><?php echo ($vo); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>

                <div class="layui-inline subsidiaryAssets">
                    <label class="layui-form-label">辅助分类：</label>
                    <div class="layui-input-inline">
                        <select name="subsidiary_helpcatid" class="layui-input" lay-search>
                            <option value="">请选择辅助分类</option>
                            <?php if(is_array($acin_category)): $i = 0; $__LIST__ = $acin_category;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == $asInfo['subsidiary_helpcatid']): ?><option value="<?php echo ($key); ?>" selected><?php echo ($vo); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span> 设备原值：</label>
                    <div class="layui-input-inline">
                        <input type="text" id="buy_price" name='<?php if($showPrice != 1): ?>no_<?php endif; ?>buy_price'
                               lay-verify="buy_price" placeholder="请输入设备购买价格（元）" autocomplete="off"
                               value="<?php echo ($asInfo["buy_price"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label" style="overflow: inherit"> 预计使用年限：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="expected_life" lay-verify="expected_life"
                               placeholder="请输入预计使用年限" autocomplete="off" value="<?php echo ($asInfo["expected_life"]); ?>"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label" style="overflow: inherit"><span class="suggest">*</span> 保修到期日期：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="guarantee_date" lay-verify="guarantee_date"
                               placeholder="请选择保修截止日期" autocomplete="off" value="<?php echo ($asInfo["guarantee_date"]); ?>"
                               class="layui-input formatDate">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest is_subsidiarySpan">*</span> 生产厂商：</label>
                    <div class="layui-input-inline">
                        <select name="ols_facid" class="layui-input" lay-search="" lay-verify="ols_facid">
                            <option value="">请选择生产厂商</option>
                            <?php if(is_array($factory)): $i = 0; $__LIST__ = $factory;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; if($facInfo['ols_facid'] == $v['olsid']): ?><option value="<?php echo ($v["olsid"]); ?>" selected><?php echo ($v["sup_name"]); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($v["olsid"]); ?>"><?php echo ($v["sup_name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                        <div class="addIcon">
                            <?php if($menuData = get_menu('OfflineSuppliers','OfflineSuppliers','addOfflineSupplier')):?>
                            <span class="factoryClick"></span>
                            <i class="layui-icon addSuppliers"> &#xe654;</i>
                            <?php endif?>
                        </div>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest is_subsidiarySpan">*</span> 供应商：</label>
                    <div class="layui-input-inline">
                        <select name="ols_supid" class="layui-input" lay-search="" lay-verify="ols_supid">
                            <option value="">请选择供应厂商</option>
                            <?php if(is_array($supplier)): $i = 0; $__LIST__ = $supplier;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; if($facInfo['ols_supid'] == $v['olsid']): ?><option value="<?php echo ($v["olsid"]); ?>" selected><?php echo ($v["sup_name"]); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($v["olsid"]); ?>"><?php echo ($v["sup_name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                        <div class="addIcon">
                            <?php if($menuData = get_menu('OfflineSuppliers','OfflineSuppliers','addOfflineSupplier')):?>
                            <span class="supplierClick"></span>
                            <i class="layui-icon addSuppliers"> &#xe654;</i>
                            <?php endif?>
                        </div>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest is_subsidiarySpan">*</span> 维修商：</label>
                    <div class="layui-input-inline">
                        <select name="ols_repid" class="layui-input" lay-search="" lay-verify="ols_repid">
                            <option value="">请选择维修厂商</option>
                            <?php if(is_array($repair)): $i = 0; $__LIST__ = $repair;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; if($facInfo['ols_repid'] == $v['olsid']): ?><option value="<?php echo ($v["olsid"]); ?>" selected><?php echo ($v["sup_name"]); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($v["olsid"]); ?>"><?php echo ($v["sup_name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                        <div class="addIcon">
                            <?php if($menuData = get_menu('OfflineSuppliers','OfflineSuppliers','addOfflineSupplier')):?>
                            <span class="repairClick"></span>
                            <i class="layui-icon addSuppliers"> &#xe654;</i>
                            <?php endif?>
                        </div>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">品牌：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="brand" placeholder="请输入设备品牌" autocomplete="off"
                               value="<?php echo ($asInfo["brand"]); ?>" class="layui-input">
                        <?php if($menuData = get_menu('BaseSetting','Dictionary','addBrandDic')):?>
                        <div class="addIcon" id="addBrandDic" data-url="<?php echo ($menuData['actionurl']); ?>">
                            <span class="factoryClick"></span>
                            <i class="layui-icon"> &#xe654;</i>
                        </div>
                        <?php endif?>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span>出厂日期：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="factorydate" placeholder="请选择出厂日期" autocomplete="off"
                               value="<?php echo ($asInfo["factorydate"]); ?>" class="layui-input formatDate">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">出厂编号：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="factorynum" placeholder="请输入出厂编号" autocomplete="off"
                               value="<?php echo ($asInfo["factorynum"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">单位：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="unit" placeholder="请输入单位" autocomplete="off"
                               value="<?php echo ($asInfo["unit"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">付款日期：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="paytime" placeholder="请选择付款日期" value="<?php echo ($asInfo["paytime"]); ?>"
                               autocomplete="off" class="layui-input formatDate">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">发票编号：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="invoicenum" placeholder="请输入发票编号" autocomplete="off"
                               value="<?php echo ($asInfo["invoicenum"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest is_subsidiarySpan">*</span> 设备原编码：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="assorignum" id="assorignum" lay-verify="assorignum" autocomplete="off"
                               placeholder="请输入设备原编码" value="<?php echo ($asInfo["assorignum"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"> 设备原码(备注)：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="assorignum_spare" id="assorignum_spare" lay-verify="assorignum_spare"
                               autocomplete="off" placeholder="请输入设备原编码" value="<?php echo ($asInfo["assorignum_spare"]); ?>"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label" style="overflow: inherit"> 残净值率(%)：</label>
                    <div class="layui-input-inline">
                        <input type="text" id="residual_value" name="residual_value" lay-verify="residual_value"
                               placeholder="请输入残净值率" autocomplete="off" value="<?php echo ($asInfo["residual_value"]); ?>"
                               class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item" pane="">
                <label class="layui-form-label">设备类型：</label>
                <div class="layui-input-block">
                    <?php if($asInfo["is_firstaid"] == 1): ?><input type="checkbox" name="is_firstaid" value="1" checked="checked" title="急救设备"
                               lay-skin="primary">
                        <?php else: ?>
                        <input type="checkbox" name="is_firstaid" value="1" title="急救设备" lay-skin="primary"><?php endif; ?>
                    <?php if($asInfo["is_special"] == 1): ?><input type="checkbox" name="is_special" value="1" checked="checked" title="特种设备"
                               lay-skin="primary">
                        <?php else: ?>
                        <input type="checkbox" name="is_special" value="1" title="特种设备" lay-skin="primary"><?php endif; ?>
                    <?php if($asInfo["is_metering"] == 1): ?><input type="checkbox" name="is_metering" value="1" checked="checked" title="计量设备"
                               lay-skin="primary">
                        <?php else: ?>
                        <input type="checkbox" name="is_metering" value="1" title="计量设备" lay-skin="primary"><?php endif; ?>
                    <?php if($asInfo["is_qualityAssets"] == 1): ?><input type="checkbox" name="is_qualityAssets" value="1" checked="checked" title="质控设备"
                               lay-skin="primary">
                        <?php else: ?>
                        <input type="checkbox" name="is_qualityAssets" value="1" title="质控设备" lay-skin="primary"><?php endif; ?>
                    <?php if($asInfo["is_patrol"] == 1): ?><input type="checkbox" name="is_patrol" value="1" title="保养设备" lay-skin="primary" checked>
                        <?php else: ?>
                        <input type="checkbox" name="is_patrol" value="1" title="保养设备" lay-skin="primary"><?php endif; ?>
                    <?php if($asInfo["is_benefit"] == 1): ?><input type="checkbox" name="is_benefit" value="1" title="效益分析" lay-skin="primary" checked>
                        <?php else: ?>
                        <input type="checkbox" name="is_benefit" value="1" title="效益分析" lay-skin="primary"><?php endif; ?>

                    <?php if($asInfo["is_lifesupport"] == 1): ?><input type="checkbox" name="is_lifesupport" value="1" title="生命支持类设备" lay-skin="primary"
                               checked>
                        <?php else: ?>
                        <input type="checkbox" name="is_lifesupport" value="1" title="生命支持类设备"
                               lay-skin="primary"><?php endif; ?>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">国产&进口：</label>
                    <div class="layui-input-inline" style="width: auto">
                        <?php switch($asInfo["is_domestic"]): case "1": ?><input type="radio" name="is_domestic" value="1" title="国产" checked>
                                <input type="radio" name="is_domestic" value="2" title="进口"><?php break;?>
                            <?php case "2": ?><input type="radio" name="is_domestic" value="1" title="国产">
                                <input type="radio" name="is_domestic" value="2" title="进口" checked><?php break;?>
                            <?php case "3": ?><input type="radio" name="is_domestic" value="1" title="国产">
                                <input type="radio" name="is_domestic" value="2" title="进口"><?php break; endswitch;?>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">是否付清：</label>
                    <div class="layui-input-inline" style="width: auto">
                        <?php switch($asInfo["pay_status"]): case "0": ?><input type="radio" name="pay_status" value="1" title="已付清">
                                <input type="radio" name="pay_status" value="0" title="未付清" checked><?php break;?>
                            <?php case "1": ?><input type="radio" name="pay_status" value="1" title="已付清" checked>
                                <input type="radio" name="pay_status" value="0" title="未付清"><?php break;?>
                            <?php case "3": ?><input type="radio" name="pay_status" value="1" title="已付清">
                                <input type="radio" name="pay_status" value="0" title="未付清"><?php break; endswitch;?>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label" style="overflow: inherit">标签ID</label>
                    <div class="layui-input-inline">
                        <input type="text" name="inventory_label_id" lay-verify="inventory_label_id"
                               placeholder="请输入标签ID" value="<?php echo ($asInfo["inventory_label_id"]); ?>" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span>巡查周期：</label>
                    <div class="layui-input-inline" style="width: auto">
                        <input type="text" name="patrol_xc_cycle" class="layui-input" lay-verify="num"
                               style="width: 5em;" value="<?php echo ($asInfo["patrol_xc_cycle"]); ?>">
                        <div class="txt">天</div>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span>保养周期：</label>
                    <div class="layui-input-inline" style="width: auto">
                        <input type="text" name="patrol_pm_cycle" class="layui-input" lay-verify="num"
                               style="width: 5em;" value="<?php echo ($asInfo["patrol_pm_cycle"]); ?>">
                        <div class="txt">天</div>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span>质控周期：</label>
                    <div class="layui-input-inline" style="width: auto">
                        <input type="text" name="quality_cycle" class="layui-input" lay-verify="num" style="width: 5em;"
                               value="<?php echo ($asInfo["quality_cycle"]); ?>">
                        <div class="txt">天</div>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span>计量周期：</label>
                    <div class="layui-input-inline" style="width: auto">
                        <input type="text" name="metering_cycle" class="layui-input" lay-verify="num"
                               style="width: 5em;" value="<?php echo ($asInfo["metering_cycle"]); ?>">
                        <div class="txt">天</div>
                    </div>
                </div>
            </div>
            <blockquote class="layui-elem-quote" style="padding: 5px 15px; /*margin-top: 35px;*/">设备入院信息
                <?php if($editdepartment['is_dispaly'] == 1): ?><font color="red">正在申请修改科室到
                    <?php echo ($editdepartment["managedepart"]); ?></font><?php endif; ?>
            </blockquote>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="rquireCoin">*</span> 所属科室：</label>
                    <div class="layui-input-inline">
                        <select name="departid" lay-search lay-verify="departid" lay-filter="departid">
                            <?php if(is_array($department)): $i = 0; $__LIST__ = $department;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["departid"]); ?>"
                                <?php if($vo["departid"] == $asInfo['departid']): ?>selected="selected"<?php endif; ?>
                                ><?php echo ($vo["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </empty>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"> 管理科室：</label>
                    <div class="layui-input-inline">
                        <select name="managedepart" lay-search>
                            <?php if(is_array($department)): $i = 0; $__LIST__ = $department;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["department"]); ?>"
                                <?php if($vo["department"] == $asInfo['managedepart']): ?>selected="selected"<?php endif; ?>
                                ><?php echo ($vo["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">设备使用位置：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="address" autocomplete="off" value="<?php echo ($asInfo["address"]); ?>"
                               class="layui-input" placeholder="请填写设备使用位置">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"> 资产负责人：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="assetsrespon" lay-verify="assetsrespon" placeholder="请填写资产负债人"
                               autocomplete="off" value="<?php echo ($asInfo["assetsrespon"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span> 财务分类：</label>
                    <div class="layui-input-inline">
                        <select name="financeid" lay-verify="financeid" class="layui-input" lay-search="">
                            <option value="">请选择财务分类</option>
                            <?php if(is_array($assets_finance)): $i = 0; $__LIST__ = $assets_finance;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if((string)$key === (string)$asInfo['financeid']): ?><option value="<?php echo ($key); ?>" selected><?php echo ($vo); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span> 设备来源：</label>
                    <div class="layui-input-inline">
                        <select name="assfromid" lay-verify="assfromid" class="layui-input" lay-search="">
                            <option value="">请选择设备来源</option>
                            <?php if(is_array($assets_assfrom)): $i = 0; $__LIST__ = $assets_assfrom;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if((string)$key === (string)$asInfo['assfromid']): ?><option value="<?php echo ($key); ?>" selected><?php echo ($vo); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span> 资金来源：</label>
                    <div class="layui-input-inline">
                        <select name="capitalfrom" lay-verify="capitalfrom" class="layui-input" lay-search="">
                            <option value="">请选择资金来源</option>
                            <?php if(is_array($assets_capitalfrom)): $i = 0; $__LIST__ = $assets_capitalfrom;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if((string)$key === (string)$asInfo['capitalfrom']): ?><option value="<?php echo ($key); ?>" selected><?php echo ($vo); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span> 入库日期：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="storage_date" lay-verify="storage_date" id="date4"
                               placeholder="请选择入库日期" autocomplete="off" value="<?php echo ($asInfo["storage_date"]); ?>"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="suggest">*</span> 启用日期：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="opendate" lay-verify="opendate" placeholder="请选择启用日期"
                               autocomplete="off" value="<?php echo ($asInfo["opendate"]); ?>" class="layui-input formatDate">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label"> 折旧方式：</label>
                    <div class="layui-input-inline">
                        <select name="depreciation_method" class="layui-input" lay-search=""
                                lay-verify="depreciation_method" lay-filter="depreciation_method">
                            <option value="">请选择折旧方式</option>
                            <?php if($asInfo['depreciation_method'] == 1): ?><option value="1" selected>平均折旧法</option>
                                <?php else: ?>
                                <option value="1">平均折旧法</option><?php endif; ?>
                            <!-- <?php if($asInfo['depreciation_method'] == 2): ?><option value="2" selected>工作量法</option>
                                <?php else: ?>
                                <option value="2">工作量法</option><?php endif; ?> -->
                            <?php if($asInfo['depreciation_method'] == 3): ?><option value="3" selected>加速折旧法(双倍余额递减法)</option>
                                <?php else: ?>
                                <option value="3">加速折旧法(双倍余额递减法)</option><?php endif; ?>
                            <?php if($asInfo['depreciation_method'] == 4): ?><option value="4" selected>加速折旧法(年数总额法)</option>
                                <?php else: ?>
                                <option value="4">加速折旧法(年数总额法)</option><?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">折旧年限：</label>
                    <div class="layui-input-inline">
                        <input type="text" id="depreciable_lives" name="depreciable_lives" placeholder="请填写折旧年限"
                               autocomplete="off" value="<?php echo ($asInfo["depreciable_lives"]); ?>" class="layui-input"
                               lay-verify="depreciable_lives">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">月折旧额：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="depreciable_quota_m" autocomplete="off" disabled="true"
                               value="<?php echo ($asInfo["depreciable_quota_m"]); ?>" class="layui-input"
                               lay-verify="depreciable_quota_m">
                    </div>
                </div>
                <!-- <div class="layui-inline">
                    <label class="layui-form-label">年折旧额：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="depreciable_quota_y"  autocomplete="off" disabled="true" value="<?php echo ($asInfo["depreciable_quota_y"]); ?>" class="layui-input" lay-verify="depreciable_quota_y">
                    </div>
                </div> -->
                <div class="layui-inline">
                    <label class="layui-form-label">累计折旧额：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="depreciable_quota_count" autocomplete="off" disabled="true"
                               value="<?php echo ($asInfo["depreciable_quota_count"]); ?>" class="layui-input"
                               lay-verify="depreciable_quota_count">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">资产净值：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="net_asset_value" autocomplete="off" disabled="true"
                               value="<?php echo ($asInfo["net_asset_value"]); ?>" class="layui-input" lay-verify="net_asset_value">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">减值准备：</label>
                    <div class="layui-input-inline">
                        <input type="text" id="impairment_provision" name="impairment_provision" autocomplete="off"
                               value="<?php echo ($asInfo["impairment_provision"]); ?>" class="layui-input"
                               lay-verify="impairment_provision">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">资产净额：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="net_assets" autocomplete="off" disabled="true"
                               value="<?php echo ($asInfo["net_assets"]); ?>" class="layui-input" lay-verify="net_assets">
                    </div>
                </div>
                <div class="layui-inline" style="display: none">
                    <label class="layui-form-label">登记日期：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="" autocomplete="off" value="<?php echo ($asInfo["assets"]); ?>" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline" style="display: none">
                    <label class="layui-form-label">登记人员：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="" autocomplete="off" value="<?php echo ($asInfo["assets"]); ?>" class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">备注：</label>
                <div class="layui-input-block">
                    <input type="text" name="remark" placeholder="请输入设备相关备注" autocomplete="off"
                           class="layui-input" value="<?php echo ($asInfo["remark"]); ?>" style="width: 92%;">
                </div>
            </div>
            <style>
                #multifile2 {
                    float: right;
                    font-size: 12px;
                    font-weight: normal;
                    color: #0a8ddf;
                    cursor: pointer;
                }

                .layui-layer-tips {
                    word-break: break-all;
                }
            </style>
            <div class="layui-form-item">
                <div class="margin-bottom-15">
                    <div class="layui-elem-quote">
                        设备物理档案
                        <span id="multifile2"
                              lay-tips="可一次选择多个文件上传，上传文件后缀名只能为.jpg/.jpeg/.png/.pdf/.xls/.xlsx/.doc/.docx/.ppt/.pptx"><i
                                class="iconfont icon-shangchuan" style="font-size: 14px;"></i> 本地上传</span>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">档案盒编号：</label>
                        <div class="layui-input-inline">
                            <div class="input-group">
                                <input type="text" class="form-control bsSuggest" id="box_num" value="<?php echo ($asInfo["box_num"]); ?>"
                                       placeholder="请选择档案编号" name="box_num">
                                <div class="input-group-btn">
                                    <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="layui-table mgt0" lay-size="sm">
                        <colgroup>
                            <col width="6%">
                            <col width="20%">
                            <col width="20%">
                            <col width="20%">
                            <col width="20%">
                            <col width="14%">
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
                                    <td class="td-align-center xuhao"><?php echo ($key+1); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["file_name"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["add_time"]); ?></td>
                                    <td class="td-align-center" style="padding: 0"><?php echo ($vo["archive_time"]); ?></td>
                                    <td class="td-align-center" style="padding: 0"><?php echo ($vo["expire_time"]); ?></td>
                                    <td class="td-align-center"><?php echo ($vo["html"]); ?></td>
                                    <input type="hidden" name="arc_id[]" value="<?php echo ($vo["arc_id"]); ?>"/>
                                </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </tbody>
                    </table>
                    <button style="display: none" type="button" id="testUpload"></button>
                </div>
            </div>
            <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                <button class="layui-btn" type="button" value="" lay-submit lay-filter="editAssets" id="uploadFileArc">
                    <i class="layui-icon">&#xe609;</i> 确认无误并提交
                </button>
                <button type="reset" class="layui-btn layui-btn-primary">
                    <i class="layui-icon">ဂ</i> 重置
                </button>
            </div>
        </form>
    </div>
</div>
<div id="addAssetsDicDiv" style="display: none">
    <div class="containDiv">
        <form class="layui-form">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="rquireCoin"> * </span> 设备名称：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="dic_assets" placeholder="请输入设备名称" required
                               lay-verify="required|assets" autocomplete="off" class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="rquireCoin">*</span> 设备分类：</label>
                    <div class="layui-input-inline">
                        <select name="dic_catid" lay-search lay-verify="required|catid" lay-filter="dic_catid">
                            <option value=""></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><span class="rquireCoin"> * </span> 设备类型：</label>
                    <div class="layui-input-inline">
                        <select name="dic_assets_category" xm-select="dic_assets_category" xm-select-search=""
                                lay-verify="dic_assets_category">
                            <option value="">请选择设备类型</option>
                            <option value="is_firstaid">急救设备</option>
                            <option value="is_special">特种设备</option>
                            <option value="is_metering">计量设备</option>
                            <option value="is_qualityAssets">质控设备</option>
                            <option value="is_benefit">效益分析</option>
                            <option value="is_lifesupport">生命支持</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">字典类别：</label>
                    <div class="layui-input-inline" id="dic_category_div">
                        <div class="input-group" id="bdepart">
                            <input type="text" class="form-control bsSuggest" name="dic_category" id="dic_category_add"
                                   placeholder="选择或填写新类别"/>
                            <div class="input-group-btn">
                                <ul id="showhos" class="dropdown-menu dropdown-menu-right ulwidth" role="menu">
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">单位：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="dic_unit" placeholder="请输入设备单位" required lay-verify="unit"
                               autocomplete="off" class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div style="text-align: center">
                    <button class="layui-btn" lay-submit lay-filter="addDic"><i class="layui-icon">&#xe609;</i> 保存
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary"><i class="layui-icon">ဂ</i> 重置</button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
<script>
    var old_is_subsidiary = parseInt('<?php echo ($asInfo["is_subsidiary"]); ?>');
    var main_assid = parseInt('<?php echo ($asInfo["main_assid"]); ?>');
    var main_assets = '<?php echo ($asInfo["main_assets"]); ?>';
    var this_catid = '<?php echo ($asInfo["catid"]); ?>';
    var this_assets = '<?php echo ($asInfo["assets"]); ?>';
    var old_dic_assets = '<?php echo ($asInfo["assets"]); ?>';
    var this_unit = '<?php echo ($asInfo["unit"]); ?>';
    var this_assets_category = '<?php echo ($asInfo["assets_category"]); ?>';
    var editAssetsUrl = '<?php echo ($editAssetsUrl); ?>';
    var now = "<?php echo ($now); ?>";
</script>

<script>
    layui.use('controller/assets/lookup/editassets', layui.factory('controller/assets/lookup/editassets'));
</script>