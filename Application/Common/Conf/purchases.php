<?php
//采购模块配置
return [
     /*上传证件类型 purchases_depart_apply_checkassets_file  字段：'style' S*/
    //到货通知单
    'ARRIVAL_NOTICE_FILE_STYLE'            =>1,
    //设备技术标准确认表
    'CONFIRM_FILE_STYLE'                   =>2,
    //设备配置清单
    'CONFIGURATION_LIST_FILE_STYLE'        =>3,
    //设备技术参数表
    'TECHNICAL_PARAMETER_LIST_FILE_STYLE'  =>4,
    //设备技术服务条款
    'SERVICE_CLAUSE_FILE_STYLE'            =>5,
    //中华人民共和国医疗器械注册证
    'REGISTRATION_CERTIFICATE_FILE_STYLE'  =>6,
    /*上传证件类型 purchases_depart_apply_checkassets_file  字段：'style' E*/
    /*购置类型 sb_purchases_depart_apply_assets  字段'buy_type' S*/
    //报废更新
    'APPLY_ASSETS_SCRAP_UPDATE'            =>1,
    //添置
    'APPLY_ASSETS_ADD_TO_IT'               =>2,
    //新增
    'APPLY_ASSETS_NEWLY_ADDED'             =>3,
    'APPLY_ASSETS_SCRAP_UPDATE_NAME'       =>'报废更新',
    'APPLY_ASSETS_ADD_TO_IT_NAME'          =>'添置',
    'APPLY_ASSETS_NEWLY_ADDED_NAME'        =>'新增',
    /*购置类型 sb_purchases_depart_apply_assets  字段'buy_type' E*/
];