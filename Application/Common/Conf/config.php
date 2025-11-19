<?php
return [
    //开启日志
    'LOG_RECORD'             => true,
    'LOG_LEVEL'              => 'EMERG,ALERT,CRIT,ERR,notice,info,debug', // 记录所有级别日志信息
    'password_overdue_days'  => 180,//密码过期失效时间（天）

    //允许选择供应商用户 0禁止 1开启 注:用于供应商制定所供应的设备质控计划
    'OPEN_SUPPLIER_USER'     => 1,
    //允许供应商用户使用的功能/权限
    'IS_SUPPLIER_MENU'       => ['qualityDetailList', 'setQualityDetail'],
    'LOAD_EXT_CONFIG'        => 'database,message,repair,assets,patrol,offlineSuppliers,purchases,wechat,app',
    //开启扫码登录模式 1开启 0关闭
    'OPEN_SCAN_LOGIN'        => 1,
    'ADMIN_NAME'             => '/A',
    'MOBILE_NAME'            => '/M',

    //使用微信or飞书
    'USE_FEISHU'             => 0,//使用微信或飞书 0微信，1飞书

    //是否启用vue微信版本
    'USE_VUE_WECHAT_VERSION' => 1, // 0使用老版本微信 1使用vue版本微信

    'VUE_NAME'        => '/P',
    'VUE_FOLDER_NAME' => '/wx',

    'FS_NAME'        => '/F',
    'FS_FOLDER_NAME' => '/fs',

    'APP_NAME'             => 'http://tecev-dev.com/',
    'APP_TITLE'            => '医疗设备管理系统',//全站标题
    'APP_LOGO'             => 'logo-new.png',//Public/images/下面的logo文件名称
    'APP_MIN_LOGO'         => 'logo.png',//Public/images/下面的logo文件名称  用于大屏等底部小图标
    'COMPANY_NAME'         => '',//公司版权名称
    'COMPANY_JC'           => '',//公司简称
    'WATER_NAME'           => '',//打印报告时，如模块设置中未设置水印文字时，则用此默认水印文字

    //列表页分页数量
    'PAGE_NUMS'            => 5,
    'HTTP_HOST'            => $_SERVER['HTTP_HOST'],
    'HOST_IP'              => '39.108.99.232',
    /*URL设置 S  默认false 表示URL区分大小写 true则表示不区分大小写*/
    'URL_CASE_INSENSITIVE' => false,
    // URL访问模式,可选参数0、1、2、3,代表以下四种模式：// 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
    'URL_MODEL'            => 2,
    // PATHINFO模式下，各参数之间的分割符号
    'URL_PATHINFO_DEPR'    => '/',
    //控制器层级
    'CONTROLLER_LEVEL'     => 2,
    /*URL设置 E*/

    'TMPL_EXCEPTION_FILE'     => './404.tpl',

    //定义模板中指定的字符串代替代替指定字符串
    'TMPL_PARSE_STRING'       => [
        '__JS__'     => '/Public/js',
        '__CSS__'    => '/Public/css',
        '__IMAGES__' => '/Public/images',
    ],
    //是否开启记录日志 0关闭 1开启
    'IS_OPEN_LOG'             => 1,
    //是否开启分院功能。true为开启，false为关闭
    'IS_OPEN_BRANCH'          => true,
    //是否可以管理分院（即可以对分院进行具体的操作），true为可以，false为不可以，注：只有开启分院功能时候，该项设置才生效，否则不生效
    'CAN_MANAGER_BANCH'       => false,
    //审批设置，请勿修改，如需修改，请同步修改数据库
    'REPAIR_APPROVE'          => 'repair_approve',
    'TRANSFER_APPROVE'        => 'transfer_approve',
    'SCRAP_APPROVE'           => 'scrap_approve',
    'OUTSIDE_APPROVE'         => 'outside_approve',
    'PURCHASES_PLANS_APPROVE' => 'purchases_plans_approve',
    'DEPART_APPLY_APPROVE'    => 'depart_apply_approve',
    'SUBSIDIARY_APPROVE'      => 'subsidiary_approve',
    'PATROL_APPROVE'          => 'patrol_approve',
    'INVENTORY_PLAN_APPROVE'  => 'inventory_plan_approve',
];
