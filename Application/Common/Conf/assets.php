<?php
/**
 * 关于资产模块用到的状态码
 */
return[
    //微信端 设备列表 每页显示数量
    'PAGE_NUM'                          => 5,
    /*资产状态 S*/
    //在用
    'ASSETS_STATUS_USE'                 => 0,
    //维修中
    'ASSETS_STATUS_REPAIR'              => 1,
    //已报废
    'ASSETS_STATUS_SCRAP'               => 2,
    //已外调
    'ASSETS_STATUS_OUTSIDE'             => 3,
    //外调中
    'ASSETS_STATUS_OUTSIDE_ON'          => 4,
    //报废中
    'ASSETS_STATUS_SCRAP_ON'            => 5,
    //转科中
    'ASSETS_STATUS_TRANSFER_ON'         => 6,
    /*资产状态 E*/
    'ASSETS_STATUS_USE_NAME'            => '在用',
    'ASSETS_STATUS_REPAIR_NAME'         => '维修中',
    'ASSETS_STATUS_SCRAP_NAME'          => '已报废',
    'ASSETS_STATUS_OUTSIDE_NAME'        => '已外调',
    'ASSETS_STATUS_OUTSIDE_ON_NAME'     => '外调中',
    'ASSETS_STATUS_SCRAP_ON_NAME'       => '报废中',
    'ASSETS_STATUS_TRANSFER_ON_NAME'    => '转科中',

    'ASSETS_FIRST_CODE_NO'              => 0,
    'ASSETS_FIRST_CODE_YES'             => 1,
    'ASSETS_FIRST_CODE_NO_NAME'         => '非急救设备',
    'ASSETS_FIRST_CODE_YES_NAME'        => '急救设备',

    'ASSETS_SPEC_CODE_NO'               => 0,
    'ASSETS_SPEC_CODE_YES'              => 1,
    'ASSETS_SPEC_CODE_NO_NAME'          => '非特种设备',
    'ASSETS_SPEC_CODE_YES_NAME'         => '特种设备',

    'ASSETS_METER_CODE_NO'              => 0,
    'ASSETS_METER_CODE_YES'             => 1,
    'ASSETS_METER_CODE_NO_NAME'         => '非计量设备',
    'ASSETS_METER_CODE_YES_NAME'        => '计量设备',

    'ASSETS_QUALITY_CODE_NO'            => 0,
    'ASSETS_QUALITY_CODE_YES'           => 1,
    'ASSETS_QUALITY_CODE_NO_NAME'       => '非质控设备',
    'ASSETS_QUALITY_CODE_YES_NAME'      => '质控设备',

    'ASSETS_PATROL_CODE_NO'             => 0,
    'ASSETS_PATROL_CODE_YES'            => 1,
    'ASSETS_PATROL_CODE_NO_NAME'        => '非保养设备',
    'ASSETS_PATROL_CODE_YES_NAME'       => '保养设备',

    'ASSETS_BENEFIT_CODE_NO'            => 0,
    'ASSETS_BENEFIT_CODE_YES'           => 1,
    'ASSETS_BENEFIT_CODE_NO_NAME'       => '非效益分析设备',
    'ASSETS_BENEFIT_CODE_YES_NAME'      => '效益分析设备',

    'ASSETS_LIFE_SUPPORT_CODE_NO'            => 0,
    'ASSETS_LIFE_SUPPORT_CODE_YES'           => 1,
    'ASSETS_LIFE_SUPPORT_CODE_NO_NAME'       => '非生命支持类设备',
    'ASSETS_LIFE_SUPPORT_CODE_YES_NAME'      => '生命支持类设备',

    //设备现状 sb_patrol_execute
    'ASSETS_STATUS_NORMAL'           =>1,
    'ASSETS_STATUS_SMALL_PROBLEM'    =>2,
    'ASSETS_STATUS_FAULT'            =>3,
    'ASSETS_STATUS_ABNORMAL'         =>4,
    'ASSETS_STATUS_IN_MAINTENANCE'   =>5,
    'ASSETS_STATUS_SCRAPPED'         =>6,
    'ASSETS_STATUS_NOT_OPERATION'    =>7,

    'ASSETS_STATUS_NORMAL_NAME'           =>'工作正常',
    'ASSETS_STATUS_SMALL_PROBLEM_NAME'    =>'有小问题，但不影响使用',
    'ASSETS_STATUS_FAULT_NAME'            =>'有故障，需要进一步维修',
    'ASSETS_STATUS_ABNORMAL_NAME'         =>'无法正常使用',
    'ASSETS_STATUS_IN_MAINTENANCE_NAME'   =>'该设备正在维修',
    'ASSETS_STATUS_SCRAPPED_NAME'         =>'该设备已报废',
    'ASSETS_STATUS_NOT_OPERATION_NAME'    =>'该设备不做保养',
    'ASSETS_STATUS_IN_MAINTENANCE_SNAME'  =>'正在维修',
    'ASSETS_STATUS_SCRAPPED_SNAME'        =>'已报废',


    //添加主设备的附件 admin/controller/AssetsController/addAccessory
    'ADDACC_INCRENAME'              => '附件名称不能为空',
    'ADDACC_INC_NUM'                => '附件数量不能为空且必须为正整数',
    'ADDACC_INCRE_CATID'            => '附件分类不能为空',
    'ADDACC_SUCCESS'                => '添加附件成功',
    'ADDACC_ERROR'                  => '添加附件失败',

    //编辑附件  admin/controller/AssetsController/editAccessory
    'EDITACC_SUCCESS'               => '修改附件成功',
    'EDITACC_ERROR'                 => '修改附件失败',

    //删除附件  admin/controller/AssetsController/deleteAccessory
    'DELETEACC_SUCCESS'             => '删除附件成功',
    'DELETEACC_ERROR'               => '删除附件失败',

    //添加主设备的增值 admin/controller/AssetsController/addAppreciation
    'ADDAPP_INCRENAME'              => '增值名称不能为空',
    'ADDAPP_INC_NUM'                => '增值数量不能为空且必须为正整数',
    'ADDAPP_INCRE_CATID'            => '增值分类不能为空',
    'ADDAPP_INCREPRICE'             => '增值单价不能为空且必须正整数',
    'ADDAPP_SUCCESS'                => '添加增值成功',
    'ADDAPP_ERROR'                  => '添加增值失败',

    //转科验收
    'TRANSFER_IS_NOTCHECK'                   =>0,
    'TRANSFER_IS_CHECK_ADOPT'                =>1,
    'TRANSFER_IS_CHECK_NOT_THROUGH'          =>2,
    'TRANSFER_IS_NOTCHECK_NAME'              =>'待验收',
    'TRANSFER_IS_CHECK_ADOPT_NAME'           =>'已通过',
    'TRANSFER_IS_CHECK_NOT_THROUGH_NAME'     =>'未通过',


    //报废审核状态
    'SCRAP_IS_CHECK_ADOPT'                =>1,
    'SCRAP_IS_CHECK_NOT_THROUGH'          =>2,
    'SCRAP_IS_NOTCHECK'                   =>0,
    'SCRAP_IS_CHECK_ADOPT_NAME'           =>'已通过',
    'SCRAP_IS_CHECK_NOT_THROUGH_NAME'     =>'未通过',
    'SCRAP_IS_NOTCHECK_NAME'              =>'未审核',

    //维保性质 sb_assets_insurance
    'INSURANCE_IS_GUARANTEE'      =>0,
    'INSURANCE_THIRD_PARTY'       =>1,
    'INSURANCE_IS_GUARANTEE_NAME' =>'原厂续保',
    'INSURANCE_THIRD_PARTY_NAME'  =>'第三方',
    'FACTORY_WARRANTY_NAME'       =>'原厂保修',


    //维保使用状态 sb_assets_insurance
    'INSURANCE_STATUS_USE'                =>1,
    'INSURANCE_STATUS_DE_PAUL'            =>2,
    'INSURANCE_STATUS_NOT_RIGHT_NOW'      =>3,

    'INSURANCE_STATUS_USE_NAME'           =>'在用',
    'INSURANCE_STATUS_DE_PAUL_NAME'       =>'脱保',
    'INSURANCE_STATUS_NOT_RIGHT_NOW_NAME' =>'未到维保开始时间',

    /*借调状态 sb_assets_borrow S*/
    // 申请/待审核
    'BORROW_STATUS_APPROVE'               =>0,
    // 审核通过/待借入验收
    'BORROW_STATUS_BORROW_IN'             =>1,
    // 借入验收完成/待归还
    'BORROW_STATUS_GIVE_BACK'             =>2,
    // 完成借调/归还验收完成
    'BORROW_STATUS_COMPLETE'              =>3,
    // 不借入
    'BORROW_STATUS_NOT_APPLY'             =>4,
    // 审批不通过
    'BORROW_STATUS_FAIL'                  =>-1,
    /*借调状态 sb_assets_borrow E*/
    /*外调状态 sb_assets_outside S*/
    // 申请/待审核
    'OUTSIDE_STATUS_APPROVE'              =>0,
    // 审核通过/待验收单录入/流程结束
    'OUTSIDE_STATUS_ACCEPTANCE_CHECK'     =>1,
    // 待验收单录入完成
    'OUTSIDE_STATUS_COMPLETE'             =>2,
    // 审批不通过
    'OUTSIDE_STATUS_FAIL'                 =>-1,
    /*外调状态 sb_assets_outside E*/
    /*附属设备分配状态 sb_subsidiary_allot S*/
    // 申请/待审核
    'SUBSIDIARY_STATUS_APPROVE'           =>0,
    // 审核通过/待验收单录入/流程结束
    'SUBSIDIARY_STATUS_ACCEPTANCE_CHECK'  =>1,
    // 验收结束
    'SUBSIDIARY_STATUS_COMPLETE'          =>2,
    // 审批不通过
    'SUBSIDIARY_STATUS_FAIL'              =>-1,
    /*附属设备分配状态 sb_subsidiary_allot E*/
    /*外调附件种类 sb_assets_outside_file S*/
    //外调申请附件
    'OUTSIDE_FILE_TYPE_APPLY'             =>0,
    //外调验收单附件
    'OUTSIDE_FILE_TYPE_CHECK'             =>1,
    //外调报告附件
    'OUTSIDE_FILE_TYPE_REPORT'            =>2,
    /*外调附件种类 sb_assets_outside_file E*/
    'OUTSIDE_CALL_OUT_TYPE'               =>1,
    'OUTSIDE_DONATION_TYPE'               =>2,
    'OUTSIDE_OUTSIDE_SALE_TYPE'           =>3,
    'OUTSIDE_CALL_OUT_TYPE_NAME'          =>'调出',
    'OUTSIDE_DONATION_TYPE_NAME'          =>'捐赠',
    'OUTSIDE_OUTSIDE_SALE_TYPE_NAME'      =>'外售',

    //设备详情页地址配置
    'SHOWASSETS_ACTION_URL'      =>  '/index.php/Admin/Assets/Lookup/showAssets.html'
];