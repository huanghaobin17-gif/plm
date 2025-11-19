<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2020/11/13
 * Time: 13:40
 */
$agent_url = 'http://intranet.tecev.com';//反向代理服务器地址
$folder_name = 'gzpz';//文件夹名称，多项目部署时候，每个文件夹对应一个项目
return [
    'OPEN_AGENT' => false,//是否开启反向代理
    'AGENT_URL' => $agent_url,
    'GET_CODE_URL' => $agent_url . '/' . $folder_name . '/get_code.php',
    'GET_GRANT_AUTHORIZATION_URL' => $agent_url . '/' . $folder_name . '/grant_authorization.php',
    'GET_JS_TICKET_URL' => $agent_url . '/' . $folder_name . '/get_js_ticket.php',
    'GET_ASSESS_TOKEN_URL' => $agent_url . '/' . $folder_name . '/get_access_token.php',
    'GET_WX_USER_INFO_URL' => $agent_url . '/' . $folder_name . '/get_wxuser_info.php',
    'SEND_MSG_URL' => $agent_url . '/' . $folder_name . '/send_msg.php',
    'GET_WX_FILES_URL' => $agent_url . '/' . $folder_name . '/get_wx_files.php',
];
