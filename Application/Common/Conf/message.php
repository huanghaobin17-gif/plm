<?php
/**
 * 验证提示信息
 * 避免命名重复，统一命名：方法名_接受的变量名
 */
return array(
  //系统基础参数设置 Admin/Controller/SetupController/system方法
    'SYSTEM_APPID'              => 'APPID不能为空',
    'SYSTEM_SECRET'             => 'SECRET不能为空',
    'SYSTEM_REDITECT_URL'       => 'REDITECT_URL不能为空',
    'SYSTEM_EXPIRETIME'         => 'token过期时间不能为空且必须整数',
    'SYSTEM_WXNURES'            => '保修页面列表显示数据条数不能为空且必须整数',
    'SYSTEM_WXENGINEER'         => '维修页面列表显示数据条数不能为空且必须整数',
    'SYSTEM_WXAPPROVE'          => '审批页面列表显示数据条数不能为空且必须整数',
    'SYSTEM_REPAIR_STATUS'      => '保修人员保修列表状态不能为空',
    'SYSTEM_ENGINEER_STATUS'    => '维修工程师维修列表状态不能为空',
    'SYSTEM_YSTZ'               => '验收通知不能为空',
    'SYSTEM_YSJGTX'             => '验收结果通知不能为空',
    'SYSTEM_BZTZ'               => '保障通知不能为空',
    'SYSTEM_YWSLTZ'             =>  '业务受理通知不能为空',
    'SYSTEM_WXCLTZ'             => '维修处理通知不能为空',
    'SYSTEM_WTJZTZ'             => '处理进度通知不能为空',
    'SYSTEM_SPJGTZ'             => '审批结果通知不能为空',
    'SYSTEM_PDTZ'               => '派单通知不能为空',
    'SYSTEM_YQTZ'               => '设备归还通知不能为空',
    'SYSTEM_CSTZ'               => '借调超时通知不能为空',
    'SYSTEM_JHJZTX'             => '计划进展提醒不能为空',
    'SYSTEM_GZJDTZ'             => '工作进度通知不能为空',
    'SYSTEM_XTYXJB'             => '系统运行简报不能为空',
    'SYSTEM_GDCLTZ'             => '工单处理通知不能为空',
    'SYSTEM_SUCCESS'            => '修改配置成功',
    'SYSTEM_ERROR'              => '修改配置失败，请检查相关文件权限',

    //用户登录模块 admin/controller/Login/LoginController
    '_LOGIN_USER_NOT_EXISTS_MSG_' => '用户不存在或未启用',
    '_LOGIN_USER_NOT_ROLE_MSG_'   => '该用户未分配角色',
    '_LOGIN_USER_NOT_MENU_MSG_'   => '该角色未分配权限',
    '_LOGIN_USER_ERROR_MSG_'      => '用户名或密码错误',
    '_LOGIN_SUCCESS_MSG_'         => '登录成功',
    '_LOGIN_UPDATA_PASSWORD_MSG_' => '系统更新要求更复杂的密码',
    '_PASSWORD_OVERDUE_MSG_'      => '密码过期失效，请重新设置密码',
    '_LOGOUT_SUCCESS_MSG_'        => '成功退出',

    //用户、角色、权限
    '_ADD_ROLE_SUCCESS_MSG_'         => '添加角色成功',
    '_EDIT_ROLE_SUCCESS_MSG_'        => '修改角色成功',
    '_ADD_ROLE_FAIL_MSG_'            => '添加角色失败',
    '_EDIT_ROLE_FAIL_MSG_'           => '修改角色失败',
    '_UPDATE_ROLE_MENU_SUCC_MSG_'    => '修改角色权限成功',
    '_UPDATE_ROLE_MENU_FAIL_MSG_'    => '修改角色权限失败',
    '_DELETE_USER_SUCCESS_SUCC_MSG_' => '删除用户成功',
    '_DELETE_USER_SUCCESS_FAIL_MSG_' => '删除用户失败',
    '_ROLE_NAME_IS_EXIST_MSG_'       => '该角色已存在',
    '_DELETE_ROLE_SUCCESS_MSG_'      => '删除角色成功',
    '_DELETE_ROLE_FAIL_MSG_'         => '删除角色失败',

    //成功
    'SUCCESS_STATUS'                 =>1,
    //开启
    'OPEN_STATUS'                    =>1,
    //关闭
    'SHUT_STATUS'                    =>0,
    //是
    'YES_STATUS'                     =>1,
    //否
    'NO_STATUS'                      =>0,
    //可操作
    'DO_STATUS'                      =>1,
    //不可操作
    'NOT_DO_STATUS'                  =>0,
    //有
    'HAVE_STATUS'                    =>1,
    //无
    'NOTHING_STATUS'                 =>0,
    //删除
    'DELETE_STATUS'                  =>-1,


    'STATUS_APPROE_UNWANTED'   =>-1,//不需要审批
    'APPROVE_STATUS'           =>0,//未审批/审批中
    'STATUS_APPROE_SUCCESS'    =>1,//审批通过
    'STATUS_APPROE_FAIL'       =>2,//审批不通过

    /*upload 上传文件名配置 S*/
    //维修文件地址
    'UPLOAD_DIR_ASSETS_NAME'     =>'assets',
    'UPLOAD_DIR_REPAIR_NAME'     =>'repair',
    //维修文件地址
    'UPLOAD_DIR_INSURANCE_NAME'  =>'insurance',
    //维保文件地址
    'UPLOAD_DIR_METERING_NAME'   =>'metering',
    //计量文件地址
    'UPLOAD_DIR_LAYEDIT_NAME'                           =>'layedit',
    'UPLOAD_DIR_SCRAP_NAME'                             =>'scrap',
    'UPLOAD_DIR_ADVERSE_NAME'                           =>'adverse',
    'UPLOAD_DIR_ASSETS_TECH_PIC_NAME'                   =>'assets/technical',
    'UPLOAD_DIR_ASSETS_QUALI_PIC_NAME'                  =>'assets/qualification',
    'UPLOAD_DIR_ASSETS_ARCHIVES_PIC_NAME'               =>'assets/archives',
    'UPLOAD_DIR_QUALITY_REPORT_INSTRUMENTS_PIC_NAME'    =>'qualities/report/instruments',
    'UPLOAD_DIR_QUALITY_REPORT_DETAIL_PIC_NAME'         =>'qualities/report/detail',
    'UPLOAD_DIR_PATROL_DOTASK_SETSITUATION_PIC_NAME'    =>'patrol/doTask/setSituation',
    'UPLOAD_DIR_PURCHASES_APPLY_ASSETS_FILE_NAME'       =>'purchases/apply/assets',
    'UPLOAD_DIR_PURCHASES_TENDER_SUPPLIER_FILE_NAME'    =>'purchases/tender/supplier',
    'UPLOAD_DIR_PURCHASES_CHECK_ASSETS_FILE_NAME'       =>'purchases/check/assets',
    'UPLOAD_DIR_DEBUG_FILE_NAME'                        =>'purchases/report/debug',
    'UPLOAD_DIR_TRAIN_FILE_NAME'                        =>'purchases/report/train',
    'UPLOAD_DIR_ASSESS_FILE_NAME'                       =>'purchases/report/assess',
    'UPLOAD_DIR_TEST_FILE_NAME'                         =>'purchases/report/test',
    'UPLOAD_DIR_METERING_FILE_NAME'                     =>'purchases/report/metering',
    'UPLOAD_DIR_QUALITY_FILE_NAME'                      =>'purchases/report/quality',
    'UPLOAD_DIR_REPORT_TRANSFER_NAME'                   =>'report/transfer',
    'UPLOAD_DIR_REPORT_SCRAP_NAME'                      =>'report/scrap',
    'UPLOAD_DIR_OUTSIDE_NAME'                           =>'Outside',
    'UPLOAD_DIR_RECORD_REPAIR_NAME'                     =>'record/repair',
    'UPLOAD_DIR_ARCHIVES_EMERGENCY_NAME'                =>'archives/emergency',
    'UPLOAD_DIR_NOTICE_NAME'                            =>'noticeFile',
    'UPLOAD_DIR_SCREEN'                                 =>'screen',

    //分页及其他配置
    'DEFAULT_LIMIT'                  =>10,//分页默认 10条数据
    'DEFAULT_ORDER'                  =>'desc',//默认降序
    'BTN_CURRENCY'                   =>'layui-btn-xs',
    'HTML_A_LINK_COLOR_BLUE'         =>'#428bca',//默认连接颜色
    'HTML_A_LINK_COLOR_GREEN'        =>'#009688',//默认连接颜色
    'HTML_A_LINK_COLOR_ORANGE'       =>'#FF5722',//默认连接颜色
    'TASK_REPAIR_COLOR'              =>'#CA8888',
    'TASK_PATROL_COLOR'              =>'#009688',
    'TASK_ASSETS_COLOR'              =>'#428bca',


);
