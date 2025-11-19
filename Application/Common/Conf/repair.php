<?php
/**
 * 关于维修模块用到的状态码
 */
return [
    //大屏显示中操作类型名称
    'SCREEN_REPAIR'                        => '设备维修',
    //维修 确认维修状态设置 sb_confirm_add_repair
    'SWITCH_REPAIR_UNCONFIRMED'            => 0,
    'SWITCH_REPAIR_CONFIRM'                => 1,
    'SWITCH_REPAIR_UNCONFIRMED_NAME'       => '待确认',
    'SWITCH_REPAIR_CONFIRM_NAME'           => '已确认',
    /*维修状态 sb_repair S*/
    //已报修
    'REPAIR_HAVE_REPAIRED'                 =>1,
    //已接单
    'REPAIR_RECEIPT'                       =>2,
    //已检修/配件待出库
    'REPAIR_HAVE_OVERHAULED'               =>3,
    //报价中
    'REPAIR_QUOTATION'                     =>4,
    //审核中
    'REPAIR_AUDIT'                         =>5,
    //维修中
    'REPAIR_MAINTENANCE'                   =>6,
    //待验收
    'REPAIR_MAINTENANCE_COMPLETION'        =>7,
    //已验收
    'REPAIR_ALREADY_ACCEPTED'              =>8,
    /*维修状态 sb_repair E*/
    'REPAIR_HAVE_REPAIRED_NAME'            =>'已报修',
    'REPAIR_RECEIPT_NAME'                  =>'已接单',
    //配件待出库
    'REPAIR_HAVE_OVERHAULED_NAME'          =>'已检修',
    'REPAIR_QUOTATION_NAME'                =>'报价中',
    'REPAIR_AUDIT_NAME'                    =>'审核中',
    'REPAIR_MAINTENANCE_NAME'              =>'维修中',
    'REPAIR_MAINTENANCE_COMPLETION_NAME'   =>'待验收',
    'REPAIR_ALREADY_ACCEPTED_NAME'         =>'已验收',
    //维修类型 sb_repair
    'REPAIR_TYPE_IS_STUDY'                 =>0,
    'REPAIR_TYPE_IS_GUARANTEE'             =>1,
    'REPAIR_TYPE_THIRD_PARTY'              =>2,
    'REPAIR_TYPE_IS_SCENE'                 =>3,
    //报价关闭 无权限（可填价格 数量 建议厂家）
    'SHUT_STATUS_DO'                       =>2,
    'REPAIR_TYPE_IS_STUDY_NAME'            =>'自修',
    'REPAIR_TYPE_IS_GUARANTEE_NAME'        =>'维保厂家',
    'REPAIR_TYPE_THIRD_PARTY_NAME'         =>'第三方',
    'REPAIR_TYPE_IS_SCENE_NAME'            =>'现场解决',
    //修复状态 成功
    'REPAIR_OVER_STATUS_SUCCESSFUL'        =>1,
    //修复状态 失败
    'REPAIR_OVER_STATUS_FAIL '             =>0,
    //维修过程成未产生配件
    'REPAIR_NOT_ADD_NEW_PARTS'             =>0,
    //维修过程中产生配件待报价
    'REPAIR_ADD_NEW_PARTS'                 =>1,
    //维修过程中产生配件已报价
    'REPAIR_QUOTED_PRICE_PARTS'            =>2,
    //维修审核状态
    'REPAIR_IS_CHECK_ADOPT'                =>1,
    'REPAIR_IS_CHECK_NOT_THROUGH'          =>2,
    'REPAIR_IS_NOTCHECK'                   =>0,
    'REPAIR_IS_CHECK_ADOPT_NAME'           =>'已通过',
    'REPAIR_IS_CHECK_NOT_THROUGH_NAME'     =>'未通过',
    'REPAIR_IS_NOTCHECK_NAME'              =>'未审核',
    //自动派工状态 ab_repair_assign
    'REPAIR_ASSIGN_STYLE_CATEGORY'         =>1,
    'REPAIR_ASSIGN_STYLE_DEPARTMENT'       =>2,
    'REPAIR_ASSIGN_STYLE_AUXILIARY'        =>3,
    'REPAIR_ASSIGN_STYLE_ASSETS'           =>4,
    'REPAIR_ASSIGN_STYLE_CATEGORY_NAME'    =>'按分类',
    'REPAIR_ASSIGN_STYLE_DEPARTMENT_NAME'  =>'按科室',
    'REPAIR_ASSIGN_STYLE_AUXILIARY_NAME'   =>'按辅助分类',
    'REPAIR_ASSIGN_STYLE_ASSETS_NAME'      =>'按设备',
    'REPAIR_STATUS' =>
        array (
            1 => '维修申请',
            2 => '维修接单',
            3 => '维修审批',
            4 => '维修处理',
            6 => '待验收',
            7 => '科室验收',
        ),
    'SHOW_REPAIR_STATUS' =>
        array (
            1 => '维修申请',
            2 => '维修接单',
            3 => '维修审批',
            4 => '维修处理',
            7 => '科室验收',
        ),
    //判断是否开启发送短信 1开启 0关闭
    'IS_SENDOUT_SMS' => 0,
    //判断是否开启了统一报价 1开启 0关闭
    'IS_OPEN_OFFER'  => 1
];