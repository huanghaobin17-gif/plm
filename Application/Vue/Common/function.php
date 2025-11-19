<?php
/*获取code跳转*/
function get_code($state){
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $REDIRECT_URI = "$protocol".C('HTTP_HOST').C('VUE_NAME')."/Notin/getUserOpenId";
    $param['appid'] = C('WX_APPID'); //AppID
    $param['redirect_uri'] = urlencode($REDIRECT_URI); //获取code后的跳转地址
    $param['response_type'] = 'code'; //不用修改
    //snsapi_base为 scope 发起的网页授权，是用来获取进入页面的用户的 openid 的，并且是静默授权并自动跳转到回调页的
    //以snsapi_userinfo为 scope 发起的网页授权，是用来获取用户的基本信息的。但这种授权需要用户手动同意，并且由于用户同意过，所以无须关注，就可在授权后获取该用户的基本信息.
    //对于已关注公众号的用户，如果用户从公众号的会话或者自定义菜单进入本公众号的网页授权页，即使是 scope 为snsapi_userinfo，也是静默授权，用户无感知。
    $param['scope'] = 'snsapi_userinfo'; //不用修改
    $param['state'] = $state; //可在行定义该参数
	$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$param['appid']."&redirect_uri=".$param['redirect_uri']."&response_type=". $param['response_type'] ."&scope=".$param['scope']."&state=".$param['state']."#wechat_redirect";
	return  $url;
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

//获取用户基本信息(UnionID机制) http请求方式: GET,判断用户是否已关注公众号
function get_userinfo_by_unionID($openid,$accessToken){
    $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$accessToken.'&openid='.$openid.'&lang=zh_CN';
    $user = file_get_contents($url);
    $user = json_decode($user, true);
    return $user;
}

//获取用户昵称、头像等信息，根据网页授权时返回的token和openid去获取
function get_userinfo_by_OAuth($content){
    //http：GET（请使用 https 协议）：
    //https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
    $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$content['access_token'].'&openid='.$content['openid'].'&lang=zh_CN';
    $user = file_get_contents($url);
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
