<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
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
        #departData tr td{padding:5px 8px;}
        .styled-select select {
            background: transparent;
            width: 130px;
            padding: 2px;
            font-size: 14px;
            border: 1px solid #ccc;
            height: 25px;
            -webkit-appearance: none; /*for chrome*/
        }
        .cusquote1 {
            padding: 5px !important;
            width: 100%;
            margin: 0 auto;
            font-size: 12px;
            color: red;
        }
        .tdcenter{
            text-align: center;
        }
    </style>
</head>
<script>
    var batchAddAssets = "<?php echo ($batchAddAssets); ?>";
</script>
<body>
<div class="containDiv">
    <div style="margin: 20px 0;">
        <div class="layui-inline">
            <div class="toolbar-move">
                <button class="layui-btn" id="exploreAssetsModel"><i class="layui-icon">&#xe61e;</i> 导出设备模板</button>
            </div>
            <?php if($menuData = get_menu('Assets','Lookup','batchAddAssets')):?>
            <div class="toolbar-move">
                <button class="layui-btn" id="uploadAssetsFile" data-url="<?php echo ($menuData['actionurl']); ?>" lay-data="{accept: 'file'}"><i class="layui-icon layui-icon-uploadfile"></i>上传文件</button>
            </div>
            <?php endif?>
        </div>
    </div>
        <blockquote class="layui-elem-quote cusquote1" style="width: auto;">
            <span style="color: #000;">待入库设备信息</span>（列表中数据尚未入库，红色字体数据为不合法数据，可直接点击进行修改操作！）（未修改正确直接进行保存操作的，系统会自动忽略此设备数据，不进行入库操作）
        </blockquote>
        <div class="layui-card-body">
            <div id="batchAssetsLists" lay-filter="batchAssetsData"></div>
        </div>

    <div class="layui-form-item">
        <div style="text-align: center">
            <button class="layui-btn layui-btn-danger" id="batchDel" lay-submit lay-filter="batchDel"><i class="layui-icon" >&#xe640;</i> 删除选中数据</button>
            <button class="layui-btn layui-btn-normal" id="uploadSel" lay-submit lay-filter="uploadSel"><i class="layui-icon" >&#xe609;</i> 保存选中数据</button>
            <button class="layui-btn" id="uploadAll" lay-submit lay-filter="uploadAll"><i class="layui-icon" >&#xe609;</i> 保存当页数据</button>
        </div>
    </div>
</div>
</body>
<div id="cateList" style="display: none;">
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form layui-form-pane" action="">
                <blockquote class="layui-elem-quote" style="padding: 5px 15px;margin: 15px 0;">选择设备分类</blockquote>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 110px;">设备分类 <span class="rquireCoin">*</span></label>
                        <div class="layui-input-block">
                            <select name="catid" lay-search>
                                <option value="">请选择设备分类</option>
                                <?php if(is_array($category)): $i = 0; $__LIST__ = $category;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["catid"]); ?>"><?php echo ($vo["html"]); echo ($vo["category"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                    <button class="layui-btn" type="button" value="" lay-submit lay-filter="saveCate">
                        <i class="layui-icon" >&#xe609;</i> 确认
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary">
                        <i class="layui-icon">ဂ</i> 重置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="assets_level_name" style="display: none;">
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form layui-form-pane" action="">
                <blockquote class="layui-elem-quote" style="padding: 5px 15px;margin: 15px 0;">选择管理类别</blockquote>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 130px;">管理类别：</label>
                        <div class="layui-input-inline">
                            <select name="assets_level" lay-search>
                                <option value="">请选择管理类别</option>
                                <?php if(is_array($assetsLevel)): $i = 0; $__LIST__ = $assetsLevel;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["value"]); ?>"><?php echo ($v["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                    <button class="layui-btn" type="button" value="" lay-submit lay-filter="saveAssetsLevel">
                        <i class="layui-icon" >&#xe609;</i> 确认
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary">
                        <i class="layui-icon">ဂ</i> 重置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="departList" style="display: none;">
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form layui-form-pane" action="">
                <blockquote class="layui-elem-quote" style="padding: 5px 15px;margin: 15px 0;">选择设备使用科室</blockquote>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 110px;">使用科室：<span class="rquireCoin">*</span></label>
                        <div class="layui-input-block">
                            <select name="department" lay-search>
                                <option value="">请选择设备使用科室</option>
                                <?php if(is_array($department)): $i = 0; $__LIST__ = $department;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["departid"]); ?>"><?php echo ($vo["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                    <button class="layui-btn" type="button" value="" lay-submit lay-filter="saveDepart">
                        <i class="layui-icon" >&#xe609;</i> 确认
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary">
                        <i class="layui-icon">ဂ</i> 重置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="helpcatList" style="display: none;">
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form layui-form-pane" action="">
                <blockquote class="layui-elem-quote" style="padding: 5px 15px;margin: 15px 0;">选择设备辅助分类</blockquote>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 110px;">辅助分类：<span class="rquireCoin">*</span></label>
                        <div class="layui-input-block">
                            <select name="helpcat" lay-search>
                                <option value="">请选择辅助分类</option>
                                <?php if(is_array($helpcat)): $i = 0; $__LIST__ = $helpcat;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                    <button class="layui-btn" type="button" value="" lay-submit lay-filter="saveHelpcat">
                        <i class="layui-icon" >&#xe609;</i> 确认
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary">
                        <i class="layui-icon">ဂ</i> 重置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="financeList" style="display: none;">
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form layui-form-pane" action="">
                <blockquote class="layui-elem-quote" style="padding: 5px 15px;margin: 15px 0;">选择设备财务分类</blockquote>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 110px;">财务分类：<span class="rquireCoin">*</span></label>
                        <div class="layui-input-block">
                            <select name="finance" lay-search>
                                <option value="">请选择财务分类</option>
                                <?php if(is_array($finance)): $i = 0; $__LIST__ = $finance;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                    <button class="layui-btn" type="button" value="" lay-submit lay-filter="saveFinance">
                        <i class="layui-icon" >&#xe609;</i> 确认
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary">
                        <i class="layui-icon">ဂ</i> 重置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="capitalfromList" style="display: none;">
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form layui-form-pane" action="">
                <blockquote class="layui-elem-quote" style="padding: 5px 15px;margin: 15px 0;">选择资金来源</blockquote>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 110px;">资金来源：<span class="rquireCoin">*</span></label>
                        <div class="layui-input-block">
                            <select name="capitalfrom" lay-search>
                                <option value="">请选择资金来源</option>
                                <?php if(is_array($capitalfrom)): $i = 0; $__LIST__ = $capitalfrom;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                    <button class="layui-btn" type="button" value="" lay-submit lay-filter="saveCapitalfrom">
                        <i class="layui-icon" >&#xe609;</i> 确认
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary">
                        <i class="layui-icon">ဂ</i> 重置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="assfromList" style="display: none;">
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form layui-form-pane" action="">
                <blockquote class="layui-elem-quote" style="padding: 5px 15px;margin: 15px 0;">选择设备来源</blockquote>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 110px;">设备来源：<span class="rquireCoin">*</span></label>
                        <div class="layui-input-block">
                            <select name="assfrom" lay-search>
                                <option value="">请选择设备来源</option>
                                <?php if(is_array($assfrom)): $i = 0; $__LIST__ = $assfrom;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                    <button class="layui-btn" type="button" value="" lay-submit lay-filter="saveAssfrom">
                        <i class="layui-icon" >&#xe609;</i> 确认
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary">
                        <i class="layui-icon">ဂ</i> 重置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="methodList" style="display: none;">
    <div class="layui-fluid">
        <div class="layui-row">
            <form class="layui-form layui-form-pane" action="">
                <blockquote class="layui-elem-quote" style="padding: 5px 15px;margin: 15px 0;">选择折旧方法</blockquote>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 110px;">折旧方法：<span class="rquireCoin">*</span></label>
                        <div class="layui-input-block">
                            <select name="depreciation_method" lay-search>
                                <option value="">请选择折旧方法</option>
                                <?php if(is_array($depreciation_method)): $i = 0; $__LIST__ = $depreciation_method;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                    <button class="layui-btn" type="button" value="" lay-submit lay-filter="saveMethod">
                        <i class="layui-icon" >&#xe609;</i> 确认
                    </button>
                    <button type="reset" class="layui-btn layui-btn-primary">
                        <i class="layui-icon">ဂ</i> 重置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</html>
<script>
    layui.use('controller/assets/lookup/batchaddassets', layui.factory('controller/assets/lookup/batchaddassets'));
</script>