<?php
//厂商模块配置
return [
     /*上传证件类型 sb_offline_suppliers_file  字段：'type' S*/
    //营业执照
    'BUSINESS_LICENSE_FILE_TYPE'    =>0,
    //医疗器械经营许可证
    'MANAGEMENT_FILE_TYPE'          =>1,
    //第二类医疗器械经营备案凭证
    'KEEP_ON_RECORD_FILE_TYPE'      =>2,
    //医疗器械生产许可证
    'GENERATE_LICENSE_FILE_TYPE'    =>3,
    //第一类医疗器械生产备案凭证
    'PRODUCTION_RECORD_FILE_TYPE'   =>4,
    //医疗器械注册证
    'MEDICAL_INSTRUMENT_REGISTRATION_FILE_TYPE'    =>5,
    //其他
    'OTHERS_FILE_TYPE'   =>6,

    /*上传证件类型 sb_offline_suppliers_file  字段：'type' E*/
    /*合同类型 sb_purchases_contract S*/
    //采购合同
    'CONTRACT_TYPE_SUPPLIER'           =>1,
    //维修合同
    'CONTRACT_TYPE_REPAIR'             =>2,
    //维保合同
    'CONTRACT_TYPE_INSURANCE'          =>3,
    //采购合同(补录)
    'CONTRACT_TYPE_RECORD_ASSETS'      =>4,
    //配件合同
    'CONTRACT_TYPE_PARTS'              =>5,
    'CONTRACT_TYPE_SUPPLIER_NAME'      =>'采购合同',
    'CONTRACT_TYPE_REPAIR_NAME'        =>'维修合同',
    'CONTRACT_TYPE_INSURANCE_NAME'     =>'维保合同',
    'CONTRACT_TYPE_RECORD_ASSETS_NAME' =>'采购合同(补录)',
    'CONTRACT_TYPE_PARTS_NAME'         =>'配件合同'
    /*合同类型 sb_purchases_contract E*/
];