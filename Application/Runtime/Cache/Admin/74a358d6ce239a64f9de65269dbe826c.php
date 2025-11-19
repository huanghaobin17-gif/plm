<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo (C("APP_TITLE")); ?></title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<!--  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; child-src 'none'; object-src 'none';">-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="shortcut icon" type="image/x-icon" href="/Public/images/favicon.ico"/>
  <link rel="stylesheet" href="/tecev/src/style/login.css?v=<?php echo mt_rand(1,54561);?>" />
  <link rel="stylesheet" href="/tecev/start/layui/css/layui.css" media="all">
  <link rel="stylesheet" href="/Public/css/tecev.css" media="all">
  <link rel="stylesheet" href="/Public/css/iconfont.css">
  <link rel="stylesheet" href="/Public/tecev-icon/iconfont.css">
  <link rel="stylesheet" href="/Public/css/formselects.css?v=<?php echo mt_rand(1,54561);?>">
  <link rel="stylesheet" href="/tecev/start/layui/icon/icon.css" media="all">
  <script src="/Public/js/jquery-3.7.0.min.js?v=<?php echo mt_rand(1,54561);?>" type="text/javascript"></script>
  <script src="/Public/js/common.js?v=<?php echo mt_rand(1,54561);?>" type="text/javascript"></script>
  <script src="/Public/js/ajax.js?v=<?php echo mt_rand(1,54561);?>"></script>
  <script src="/Public/js/cookie.js"></script>
  <script src="/Public/js/newlocalstorage.js?v=<?php echo mt_rand(1,54561);?>"></script>
  <script src="/Public/js/placeholder.js?v=<?php echo mt_rand(1,54561);?>"></script>
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
  <script>
  /^http(s*):\/\//.test(location.href) || alert('请先部署到 localhost 下再访问');
  </script>
</head>
<body>
  <div id="LAY_app"></div>
  <script src="/dist/main.js"></script>
  <script>
    var version = Math.random().toFixed(4);
  layui.config({
    version:version,//js版本号
    base: '/tecev/src/' //指定 layuiAdmin 项目路径，本地开发用 src，线上用 dist
  }).extend({ //设定模块别名
    tipsType: '../../extend/tipsType',
    suggest: '../../extend/suggest',
    formSelects: '../../extend/formSelects',
    tablePlug: '../../extend/tablePlug/tablePlug',
    treeGrid: '../../extend/treeGrid',
    soulTable: '../../extend/soulTable/soulTable',
    tableFilter: '../../extend/soulTable/tableFilter',
    excel: '../../extend/soulTable/excel',
    tableChild: '../../extend/soulTable/tableChild',
    tableMerge: '../../extend/soulTable/tableMerge',
  }).use('index');
  </script>
</body>
</html>