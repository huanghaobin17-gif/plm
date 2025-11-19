<?php
/*获取code跳转*/
function get_code($state){
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $REDIRECT_URI = "$protocol".C('HTTP_HOST').C('FS_NAME')."/Notin/getUserOpenId";
    $param['appid'] = C('WX_APPID'); //AppID
    $param['redirect_uri'] = urlencode($REDIRECT_URI); //获取code后的跳转地址
    $param['state'] = $state; //可在行定义该参数
    $url = "https://open.feishu.cn/open-apis/authen/v1/index?redirect_uri=".$param['redirect_uri']."&app_id=".$param['appid']."&state=".$param['state'];
	return  $url;
}

////获取 app_access_token（企业自建应用）
//function get_app_access_token(){
//    $param['app_id'] = C('WX_APPID'); //AppID
//    $param['app_secret'] = C('WX_SECRET'); //AppSecret
//    $url = "https://open.feishu.cn/open-apis/auth/v3/app_access_token/internal";
//    $header[] = 'Content-Type:application/json; charset=utf-8';
//    $res = dcurl($url,json_encode($param),$header);
//    $res = json_decode($res,true);
//    if ($res['code'] != 0) {
//        return false;
//    }
//    return $res['app_access_token'];
//}

function get_auth_user_info($code,$app_access_token){
    $param['grant_type'] = 'authorization_code';
    $param['code'] = $code;
    $header[] = 'Authorization:Bearer '.$app_access_token;
    $header[] = 'Content-Type:application/json; charset=utf-8';
    $url = "https://open.feishu.cn/open-apis/authen/v1/access_token";
    $res = dcurl($url,json_encode($param),$header);
    $res = json_decode($res,true);
    if ($res['code'] != 0) {
        return false;
    }
    return $res['data'];
}



//获取网页授权access_token，此access_token非普通的access_token，详情请看微信公众号开发者文档
function grant_authorization($code){
	$param ['appid'] = C('WX_APPID'); //AppID
	$param ['secret'] = C('WX_SECRET'); //AppSecret
	$param ['code'] = $code;
	$param ['grant_type'] = 'authorization_code';

	$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?'.http_build_query($param);
	//$content = file_get_contents($url);
	$content = curl($url);
	$content = json_decode ( $content, true );
	if (! empty ( $content ['errmsg'] )) {
		return false;
	}

	return $content;
}

//通过授权获取用户信息, $content 是数组类型
function get_userinfo_by_auth($content){
    $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$content['access_token'].'&openid='.$content['openid'].'&lang=zh_CN';
    $user = curl($url);;
    $user = json_decode($user, true);

	return $user;
}
//获取code，test在该链接自定义的参数
 function auth(){
	$state = 'test';//该字符串是获取code时自定义的参数。
	if(I('get.state') != $state){//获取code
		get_code($state); //调用function.php中定义的get_code函数，$state是链接自带参数的

	}else{ //获取code之后

		//获取access_token;
		$content = get_access_token();

		//获取用户信息
		$user = get_userinfo_by_auth($content); //$user是保存用户信息的一位数组

		return $user;

	}

}

function downAndSaveFile($url,$savePath){
    ob_start();
    readfile($url);
    $img  = ob_get_contents();
    ob_end_clean();
    $size = strlen($img);
    $fp = fopen($savePath, 'a');
    fwrite($fp, $img);
    fclose($fp);
}

//获取网页授权access_token，此access_token非普通的access_token，详情请看微信公众号开发者文档
function get_access_token($code){
    $param ['appid'] = C('WX_APPID'); //AppID
    $param ['secret'] = C('WX_SECRET'); //AppSecret
    $param ['code'] = $code;
    $param ['grant_type'] = 'authorization_code';

    $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?'.http_build_query($param);
    //$content = file_get_contents($url);
    $content = curl($url);
    $content = json_decode ( $content, true );
    if (! empty ( $content ['errmsg'] )) {
        return false;
    }

    return $content;
}
